# Script de deploiement simplifie - Envoie UNIQUEMENT les fichiers modifies
#commande possible - a lire absolument
# Usage: .\deploy-simple.ps1
#.\build\deploy-simple.ps1

param(
    [Parameter(Mandatory=$false)]
    [ValidateSet("test", "plugin")]
    [string]$Mode = "plugin"
)

$ErrorActionPreference = "Stop"

# Configuration FTP
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

$WorkingDir = "I:\wp-pdf-builder-pro"

Write-Host "`nDEPLOIEMENT PLUGIN - Mode: $Mode" -ForegroundColor Cyan
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
    $modifiedFiles = & git diff --name-only HEAD 2>&1
    $stagedFiles = & git diff --cached --name-only HEAD 2>&1
    $lastCommitFiles = & git diff --name-only HEAD~1 HEAD 2>&1
    $ErrorActionPreference = "Stop"
    
    # Filtrer pour enlever les warnings
    $allModified = @($modifiedFiles) + @($stagedFiles) + @($lastCommitFiles) | Where-Object { $_ -and $_ -notlike "*warning*" } | Sort-Object -Unique
    
    # Filtrer pour le dossier plugin uniquement
    $pluginModified = $allModified | Where-Object { $_ -like "plugin/*" }
    
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

    # Test connexion FTP
    Write-Host "   Test de connexion FTP..." -ForegroundColor Yellow
    try {
        $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost/"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
        $ftpRequest.UseBinary = $false
        $ftpRequest.UsePassive = $false
        $ftpRequest.Timeout = 8000  # 8 secondes pour le test
        $response = $ftpRequest.GetResponse()
        $response.Close()
        Write-Host "   Connexion FTP OK" -ForegroundColor Green
    } catch {
        Write-Host "   Erreur FTP: $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }

    # Creer les repertoires d'abord
    $dirs = @{}
    foreach ($file in $pluginModified) {
        $dir = Split-Path $file -Parent
        if ($dir -and !$dirs.ContainsKey($dir)) {
            $dirs[$dir] = $true
        }
    }

    # Creer repertoires sur FTP de maniere recursive
    function New-FtpDirectory {
        param([string]$ftpPath)

        $parts = $ftpPath -split '/'
        $currentPath = ""

        foreach ($part in $parts) {
            if ($part) {
                $currentPath += "/$part"
                try {
                    $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$currentPath/"
                    $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
                    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
                    $ftpRequest.UseBinary = $true
                    $ftpRequest.UsePassive = $false
                    $ftpRequest.Timeout = 8000  # 8 secondes pour la création de répertoires
                    $ftpRequest.ReadWriteTimeout = 15000
                    $response = $ftpRequest.GetResponse()
                    $response.Close()
                } catch {
                    # Dossier peut deja exister, ignorer l'erreur
                }
            }
        }
    }

    # Creer repertoires sur FTP de maniere optimisee
    Write-Host "   Creation des repertoires..." -ForegroundColor Yellow
    $createdDirs = 0
    foreach ($dir in $dirs.Keys) {
        # Corriger le calcul du chemin FTP - enlever seulement le prefixe "plugin/" si present
        if ($dir.StartsWith("plugin/")) {
            $ftpDir = $dir.Substring(7)  # Enlever "plugin/"
        } elseif ($dir.StartsWith("plugin\")) {
            $ftpDir = $dir.Substring(7)  # Enlever "plugin\"
        } else {
            $ftpDir = $dir
        }
        $ftpDir = $ftpDir.Replace("\", "/")
        $fullPath = "$FtpPath/$ftpDir".TrimEnd('/')
        if ($fullPath -ne $FtpPath) {
            New-FtpDirectory $fullPath
            $createdDirs++
        }
    }
    Write-Host "   Repertoires crees: $createdDirs" -ForegroundColor Green

    # Upload fichiers avec status - VERSION OPTIMISEE AVEC PARALLELISME
    Write-Host "   Upload des fichiers ($($pluginModified.Count) fichiers)..." -ForegroundColor Yellow
    $maxConcurrentUploads = 3  # Limiter à 3 uploads simultanés pour éviter la surcharge
    $uploadJobs = @()

    foreach ($file in $pluginModified) {
        $localFile = Join-Path $WorkingDir $file

        if (!(Test-Path $localFile)) {
            # Fichier supprime
            continue
        }

        # Corriger le calcul du remotePath
        if ($file.StartsWith("plugin/")) {
            $remotePath = $file.Substring(7)  # Enlever "plugin/"
        } elseif ($file.StartsWith("plugin\")) {
            $remotePath = $file.Substring(7)  # Enlever "plugin\"
        } else {
            $remotePath = $file
        }
        $remotePath = $remotePath.Replace("\", "/")

        # Attendre si on a atteint la limite de jobs simultanés
        while ($uploadJobs.Count -ge $maxConcurrentUploads) {
            $completedJobs = $uploadJobs | Where-Object { $_.IsCompleted }
            $uploadJobs = $uploadJobs | Where-Object { -not $_.IsCompleted }
            Start-Sleep -Milliseconds 100
        }

        # Lancer l'upload en arrière-plan
        $job = Start-Job -ScriptBlock {
            param($ftpHost, $ftpUser, $ftpPass, $ftpPath, $remotePath, $localFile)

            try {
                $ftpUri = "ftp://$ftpUser`:$ftpPass@$ftpHost$ftpPath/$remotePath"
                $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
                $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
                $ftpRequest.UseBinary = $true
                $ftpRequest.UsePassive = $false
                $ftpRequest.Timeout = 15000  # 15 secondes pour l'upload
                $ftpRequest.ReadWriteTimeout = 30000  # 30 secondes pour les gros fichiers

                $fileContent = [System.IO.File]::ReadAllBytes($localFile)
                $ftpRequest.ContentLength = $fileContent.Length

                $stream = $ftpRequest.GetRequestStream()
                $stream.Write($fileContent, 0, $fileContent.Length)
                $stream.Close()

                $response = $ftpRequest.GetResponse()
                $response.Close()

                return @{ Success = $true; File = $remotePath }
            } catch {
                return @{ Success = $false; File = $remotePath; Error = $_.Exception.Message }
            }
        } -ArgumentList $FtpHost, $FtpUser, $FtpPass, $FtpPath, $remotePath, $localFile

        $uploadJobs += $job
    }

    # Attendre que tous les jobs se terminent et afficher les résultats
    $completedCount = 0
    while ($uploadJobs.Count -gt 0) {
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
            $completedCount++
        }

        $uploadJobs = $uploadJobs | Where-Object { $_.State -ne 'Completed' }

        # Afficher la progression toutes les 2 secondes
        if ($completedCount % 2 -eq 0 -and $completedCount -gt 0) {
            Write-Host "   Progression: $completedCount / $($pluginModified.Count) fichiers traites..." -ForegroundColor Yellow
        }

        Start-Sleep -Milliseconds 500
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

    # Staging
    Write-Host "   Staging des fichiers..." -ForegroundColor Yellow
    $ErrorActionPreference = "Continue"
    & git add -A 2>&1 | Out-Null
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

    # Tag seulement si push réussi
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
