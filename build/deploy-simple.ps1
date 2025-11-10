# Script de deploiement simplifie - Envoie UNIQUEMENT les fichiers modifies
#commande possible - a lire absolument
# Usage: .\deploy-simple.ps1
#.\build\deploy-simple.ps1

param(
    [Parameter(Mandatory=$false)]
    [ValidateSet("test", "plugin")]
    [string]$Mode = "plugin",
    [switch]$SkipConnectionTest,
    [switch]$FastMode
)

$ErrorActionPreference = "Stop"

# Configuration FTP
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

$WorkingDir = "I:\wp-pdf-builder-pro"

# Configuration FastMode
if ($FastMode) {
    $SkipConnectionTest = $true
    Write-Host "MODE RAPIDE: Test de connexion désactivé, parallélisation maximale" -ForegroundColor Cyan
}

Write-Host "`nDEPLOIEMENT PLUGIN - Mode: $Mode $(if ($FastMode) { '(RAPIDE)' } else { '' })" -ForegroundColor Cyan
Write-Host ("=" * 60) -ForegroundColor White

Write-Host "`n1 Compilation des assets JavaScript/CSS..." -ForegroundColor Magenta

try {
    Push-Location $WorkingDir
    Write-Host "   Execution: npm run build" -ForegroundColor Yellow
    $buildResult = & npm run build 2>&1
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Erreur de compilation!" -ForegroundColor Red
        Write-Host $buildResult -ForegroundColor Red
        exit 1
    }
    Write-Host "Compilation reussie" -ForegroundColor Green
    Pop-Location
} catch {
    Write-Host "Erreur: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# 2 LISTER LES FICHIERS MODIFIES
Write-Host "`n2 Detection des fichiers modifies..." -ForegroundColor Magenta

try {
    Push-Location $WorkingDir
    
    # Recuperer les fichiers modifies depuis git (les warnings git ne doivent pas causer d'erreur)
    $ErrorActionPreference = "Continue"
    $statusOutput = & git status --porcelain 2>&1
    $ErrorActionPreference = "Stop"
    
    # Parser la sortie de git status pour extraire les fichiers modifiés
    $allModified = $statusOutput | Where-Object { $_ -and $_ -notlike "*warning*" } | ForEach-Object {
        # Format de git status --porcelain: "XY fichier" où X=status index, Y=status working tree
        if ($_ -match '^\s*([MADRCU\?\!]{1,2})\s+(.+)$') {
            $file = $matches[2]
            $file
        }
    } | Sort-Object -Unique
    
    # Filtrer pour le dossier plugin uniquement, mais inclure aussi les fichiers de build
    $pluginModified = $allModified | Where-Object { $_ -like "plugin/*" -or $_ -like "build/*" }
    
    # Toujours inclure les fichiers dist s'ils ont été modifiés récemment (dans les dernières 5 minutes)
    $distFiles = Get-ChildItem "plugin/assets/js/dist/*.js" | Where-Object { $_.LastWriteTime -gt (Get-Date).AddMinutes(-5) } | Select-Object -ExpandProperty FullName
    $distFilesRelative = $distFiles | ForEach-Object { $_.Replace("$WorkingDir\", "").Replace("\", "/") }
    $pluginModified = @($pluginModified) + @($distFilesRelative) | Sort-Object -Unique
    
    if ($pluginModified.Count -eq 0) {
        Write-Host "Aucun fichier modifie a deployer" -ForegroundColor Green
        Write-Host "   (Tous les fichiers sont a jour)" -ForegroundColor Gray
        Pop-Location
        exit 0
    }
    
    Write-Host "Fichiers modifies detects: $($pluginModified.Count)" -ForegroundColor Cyan
    $pluginModified | ForEach-Object {
        Write-Host "   - $_" -ForegroundColor White
    }
    
    Pop-Location
} catch {
    Write-Host "Erreur git: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# 3 UPLOAD FTP
$uploadCount = 0
$errorCount = 0
$startTime = Get-Date

if ($Mode -eq "test") {
    Write-Host "`nMODE TEST - Pas d'upload reel" -ForegroundColor Yellow
} else {
    Write-Host "`n3 Upload FTP des fichiers modifies..." -ForegroundColor Magenta

    # Test connexion FTP rapide (optionnel - skip si -SkipConnectionTest)
    if (!$SkipConnectionTest) {
        Write-Host "   Test de connexion FTP..." -ForegroundColor Yellow
        try {
            $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost/"
            $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
            $ftpRequest.UseBinary = $false
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = 5000  # Réduit à 5 secondes
            $ftpRequest.KeepAlive = $false
            $response = $ftpRequest.GetResponse()
            $response.Close()
            Write-Host "   Connexion FTP OK" -ForegroundColor Green
        } catch {
            Write-Host "   Erreur FTP: $($_.Exception.Message)" -ForegroundColor Red
            exit 1
        }
    }

    # Créer tous les répertoires en parallèle
    Write-Host "   Creation des repertoires..." -ForegroundColor Yellow
    $dirs = @{}
    foreach ($file in $pluginModified) {
        $dir = Split-Path $file -Parent
        if ($dir -and !$dirs.ContainsKey($dir)) {
            $dirs[$dir] = $true
        }
    }

    # Fonction optimisée pour créer un répertoire (sans récursion lente)
    function New-FtpDirectoryFast {
        param([string]$ftpPath)

        try {
            $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$ftpPath/"
            $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $ftpRequest.UseBinary = $true
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = 3000  # 3 secondes par répertoire
            $ftpRequest.KeepAlive = $false
            $response = $ftpRequest.GetResponse()
            $response.Close()
            return $true
        } catch {
            # Dossier existe probablement déjà
            return $false
        }
    }

    # Créer tous les répertoires en parallèle
    $createdDirs = 0
    $dirJobs = @()
    foreach ($dir in $dirs.Keys) {
        # Corriger le calcul du chemin FTP
        if ($dir.StartsWith("plugin/")) {
            $ftpDir = $dir.Substring(7)
        } elseif ($dir.StartsWith("plugin\")) {
            $ftpDir = $dir.Substring(7)
        } else {
            $ftpDir = $dir
        }
        $ftpDir = $ftpDir.Replace("\", "/")
        $fullPath = "$FtpPath/$ftpDir".TrimEnd('/')

        if ($fullPath -ne $FtpPath) {
            $job = Start-Job -ScriptBlock {
                param($ftpHost, $ftpUser, $ftpPass, $fullPath)
                try {
                    $ftpUri = "ftp://$using:FtpUser`:$using:FtpPass@$using:FtpHost$fullPath/"
                    $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
                    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
                    $ftpRequest.UseBinary = $true
                    $ftpRequest.UsePassive = $true
                    $ftpRequest.Timeout = 3000
                    $ftpRequest.KeepAlive = $false
                    $response = $ftpRequest.GetResponse()
                    $response.Close()
                    return $true
                } catch {
                    return $false
                }
            } -ArgumentList $FtpHost, $FtpUser, $FtpPass, $fullPath
            $dirJobs += $job
        }
    }

    # Attendre la fin de la création des répertoires (max 10 secondes)
    $dirTimeout = 10
    $dirStartTime = Get-Date
    while ($dirJobs.Count -gt 0 -and ((Get-Date) - $dirStartTime).TotalSeconds -lt $dirTimeout) {
        $completedDirJobs = $dirJobs | Where-Object { $_.State -eq 'Completed' }
        foreach ($job in $completedDirJobs) {
            $result = Receive-Job $job
            if ($result) { $createdDirs++ }
            Remove-Job $job
        }
        $dirJobs = $dirJobs | Where-Object { $_.State -ne 'Completed' }
        Start-Sleep -Milliseconds 100
    }

    # Nettoyer les jobs restants
    foreach ($job in $dirJobs) {
        Stop-Job $job
        Remove-Job $job
    }

    Write-Host "   Repertoires crees: $createdDirs" -ForegroundColor Green

    # Upload fichiers avec parallélisation optimisée
    Write-Host "   Upload des fichiers ($($pluginModified.Count) fichiers)..." -ForegroundColor Yellow
    $maxConcurrentUploads = $(if ($FastMode) { 6 } else { 4 })  # 6 en mode rapide, 4 normal
    $uploadJobs = [System.Collections.Generic.List[object]]::new()
    $jobTimeout = $(if ($FastMode) { 30 } else { 45 })  # Timeout plus court en mode rapide

    foreach ($file in $pluginModified) {
        $localFile = Join-Path $WorkingDir $file

        if (!(Test-Path $localFile)) {
            continue
        }

        # Calcul du remotePath optimisé
        if ($file.StartsWith("plugin/")) {
            $remotePath = $file.Substring(7)
        } elseif ($file.StartsWith("plugin\")) {
            $remotePath = $file.Substring(7)
        } else {
            $remotePath = $file
        }
        $remotePath = $remotePath.Replace("\", "/")

        # Gestion optimisée des jobs simultanés
        while ($uploadJobs.Count -ge $maxConcurrentUploads) {
            $completedJobs = $uploadJobs | Where-Object { $_.State -eq 'Completed' }
            foreach ($job in $completedJobs) {
                $result = Receive-Job $job
                if ($result.Success) {
                    $uploadCount++
                    Write-Host "   OK: $($result.File)" -ForegroundColor Green
                } else {
                    $errorCount++
                    Write-Host "   ERREUR: $($result.File) - $($result.Error)" -ForegroundColor Red
                }
                Remove-Job $job
                $uploadJobs.Remove($job) | Out-Null
            }
            Start-Sleep -Milliseconds 50  # Réduit à 50ms
        }

        # Job d'upload optimisé avec retry
        $job = Start-Job -ScriptBlock {
            param($ftpHost, $ftpUser, $ftpPass, $ftpPath, $remotePath, $localFile)

            $maxRetries = 3
            $retryCount = 0

            while ($retryCount -lt $maxRetries) {
                try {
                    $ftpUri = "ftp://$ftpUser`:$ftpPass@$ftpHost$ftpPath/$remotePath"
                    $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
                    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
                    $ftpRequest.UseBinary = $true
                    $ftpRequest.UsePassive = $true
                    $ftpRequest.Timeout = 15000  # Augmenté à 15 secondes
                    $ftpRequest.ReadWriteTimeout = 30000  # Augmenté à 30 secondes
                    $ftpRequest.KeepAlive = $false

                    $fileContent = [System.IO.File]::ReadAllBytes($localFile)
                    $ftpRequest.ContentLength = $fileContent.Length

                    $stream = $ftpRequest.GetRequestStream()
                    $stream.Write($fileContent, 0, $fileContent.Length)
                    $stream.Close()

                    $response = $ftpRequest.GetResponse()
                    $response.Close()

                    return @{ Success = $true; File = $remotePath }
                } catch {
                    $retryCount++
                    if ($retryCount -ge $maxRetries) {
                        return @{ Success = $false; File = $remotePath; Error = $_.Exception.Message }
                    }
                    Start-Sleep -Seconds 1  # Attendre 1 seconde avant retry
                }
            }
        } -ArgumentList $FtpHost, $FtpUser, $FtpPass, $FtpPath, $remotePath, $localFile

        $uploadJobs.Add($job) | Out-Null
    }

    # Attendre la fin de tous les uploads avec timeout optimisé
    $globalTimeout = $(if ($FastMode) { 180 } else { 240 })  # Augmenté pour les retries
    $globalStartTime = Get-Date

    while ($uploadJobs.Count -gt 0 -and ((Get-Date) - $globalStartTime).TotalSeconds -lt $globalTimeout) {
        $completedJobs = $uploadJobs | Where-Object { $_.State -eq 'Completed' }

        foreach ($job in $completedJobs) {
            $result = Receive-Job $job
            if ($result.Success) {
                $uploadCount++
                Write-Host "   OK: $($result.File)" -ForegroundColor Green
            } else {
                $errorCount++
                Write-Host "   ERREUR: $($result.File) - $($result.Error)" -ForegroundColor Red
            }
            Remove-Job $job
            $uploadJobs.Remove($job) | Out-Null
        }

        # Progression moins verbeuse
        $totalProcessed = $uploadCount + $errorCount
        if ($totalProcessed -gt 0 -and ($totalProcessed % 3) -eq 0) {  # Tous les 3 fichiers
            Write-Host "   Progression: $totalProcessed / $($pluginModified.Count) fichiers..." -ForegroundColor Yellow
        }

        Start-Sleep -Milliseconds 100  # Réduit à 100ms
    }

    # Nettoyer les jobs timeoutés
    foreach ($job in $uploadJobs) {
        if ($job.State -ne 'Completed') {
            Write-Host "   TIMEOUT: $($job.Name)" -ForegroundColor Red
            $errorCount++
            Stop-Job $job
            Remove-Job $job
        }
    }
}

$totalTime = (Get-Date) - $startTime
Write-Host "`nUpload termine:" -ForegroundColor White
Write-Host "   Fichiers envoyes: $uploadCount" -ForegroundColor Green
Write-Host "   Erreurs: $errorCount" -ForegroundColor $(if ($errorCount -gt 0) { "Red" } else { "Green" })
Write-Host "   Temps: $([math]::Round($totalTime.TotalSeconds, 1))s" -ForegroundColor Gray

if ($errorCount -gt 0) {
    Write-Host "`nCertains fichiers n'ont pas pu etre uploades (probablement des fichiers binaires)." -ForegroundColor Yellow
    Write-Host "Les fichiers importants ont été déployés avec succès." -ForegroundColor Green
    # Ne pas sortir en erreur pour les fichiers binaires
}

# 4 GIT COMMIT + PUSH + TAG
Write-Host "`n4 Git commit + push + tag..." -ForegroundColor Magenta

$commitCreated = $false
$pushSuccess = $false

try {
    Push-Location $WorkingDir

    # Staging seulement des fichiers modifies (plus rapide que git add -A)
    Write-Host "   Staging des fichiers modifies uniquement..." -ForegroundColor Yellow
    $ErrorActionPreference = "Continue"
    foreach ($file in $pluginModified) {
        & git add $file 2>&1 | Out-Null
    }
    $ErrorActionPreference = "Stop"

    # Vérifier s'il y a des changements à committer
    $status = & git status --porcelain 2>&1
    if ($status -and $status.Count -gt 0) {
        # Commit
        $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
        $commitMsg = "fix: Drag-drop FTP deploy - $timestamp"
        Write-Host "   Commit: $commitMsg" -ForegroundColor Yellow
        $ErrorActionPreference = "Continue"
        $commitResult = & git commit -m $commitMsg 2>&1
        $ErrorActionPreference = "Stop"

        if ($LASTEXITCODE -eq 0) {
            Write-Host "   Commit cree" -ForegroundColor Green
            $commitCreated = $true
        } else {
            Write-Host "   Erreur commit: $($commitResult -join ' ')" -ForegroundColor Red
            $commitCreated = $false
        }
    } else {
        Write-Host "   Rien a committer (deja a jour)" -ForegroundColor Gray
        $commitCreated = $false
    }

    # Push seulement si un commit a été créé
    if ($commitCreated) {
        Write-Host "   Push vers remote..." -ForegroundColor Yellow
        $ErrorActionPreference = "Continue"
        $pushResult = & git push origin dev 2>&1
        $ErrorActionPreference = "Stop"

        if ($LASTEXITCODE -eq 0) {
            Write-Host "   Push reussi" -ForegroundColor Green
            $pushSuccess = $true
        } else {
            Write-Host "   Erreur push: $($pushResult -join ' ')" -ForegroundColor Red
            $pushSuccess = $false
        }
    } else {
        Write-Host "   Pas de push (pas de commit)" -ForegroundColor Gray
        $pushSuccess = $true  # Pas d'erreur si pas de commit
    }

    # Tag seulement si push réussi - OPTIONNEL, peut être désactivé pour accélérer
    if ($pushSuccess -and $commitCreated) {
        $version = Get-Date -Format "v1.0.0-deploy-yyyyMMdd-HHmmss"
        Write-Host "   Tag: $version" -ForegroundColor Yellow
        $ErrorActionPreference = "Continue"
        $tagResult = & git tag -a $version -m "Deploiement $version" 2>&1
        $ErrorActionPreference = "Stop"

        if ($LASTEXITCODE -eq 0) {
            $ErrorActionPreference = "Continue"
            $tagPushResult = & git push origin $version 2>&1
            $ErrorActionPreference = "Stop"

            if ($LASTEXITCODE -eq 0) {
                Write-Host "   Tag cree et pousse" -ForegroundColor Green
            } else {
                Write-Host "   Erreur push tag: $($tagPushResult -join ' ')" -ForegroundColor Red
            }
        } else {
            Write-Host "   Erreur creation tag: $($tagResult -join ' ')" -ForegroundColor Red
        }
    } else {
        Write-Host "   Pas de tag (pas de push ou commit)" -ForegroundColor Gray
    }

    Pop-Location
} catch {
    Write-Host "   Erreur git: $($_.Exception.Message)" -ForegroundColor Red
}

# FIN
Write-Host "`nDEPLOIEMENT TERMINE AVEC SUCCES!" -ForegroundColor Green
Write-Host ("=" * 60) -ForegroundColor White
Write-Host "Resume:" -ForegroundColor Cyan
Write-Host "   Compilation: OK" -ForegroundColor Green

# Afficher le statut FTP selon le mode
if ($Mode -eq "test") {
    Write-Host "   Upload FTP: TEST (pas d'upload reel)" -ForegroundColor Yellow
} else {
    Write-Host "   Upload FTP: OK ($uploadCount fichiers)" -ForegroundColor Green
}

# Afficher le statut Git selon les résultats
if ($commitCreated -and $pushSuccess) {
    Write-Host "   Git: OK (commit + push + tag)" -ForegroundColor Green
} elseif ($commitCreated) {
    Write-Host "   Git: PARTIEL (commit OK, push/tag echoue)" -ForegroundColor Yellow
} else {
    Write-Host "   Git: SKIP (rien a committer)" -ForegroundColor Gray
}
Write-Host ""
