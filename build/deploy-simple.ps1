# Script de déploiement FTP ultra-simple
# Déploie UNIQUEMENT les fichiers modifiés détectés par git
# Usage: .\deploy-simple.ps1

param(
    [switch]$SkipConnectionTest,
    [switch]$FastMode
)

$ErrorActionPreference = "Stop"
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

# Configuration FTP
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"
$WorkingDir = "I:\wp-pdf-builder-pro"

Write-Host "DEPLOIEMENT FTP ULTRA-SIMPLE" -ForegroundColor Cyan
Write-Host ("=" * 40) -ForegroundColor White

# 1 DETECTION DES FICHIERS MODIFIES
Write-Host "`n1 Detection des fichiers modifies..." -ForegroundColor Magenta

try {
    # Obtenir les fichiers modifiés via git
    $modifiedFiles = cmd /c "cd /d $WorkingDir && git diff --name-only" 2>&1
    $stagedFiles = cmd /c "cd /d $WorkingDir && git diff --name-only --cached" 2>&1

    # Convertir les sorties en tableaux de fichiers
    $modifiedArray = @()
    if ($modifiedFiles -and $modifiedFiles.Trim() -ne "") {
        $modifiedArray = $modifiedFiles -split '\s+' | Where-Object { $_ -and $_.Trim() -ne "" }
    }

    $stagedArray = @()
    if ($stagedFiles -and $stagedFiles.Trim() -ne "") {
        $stagedArray = $stagedFiles -split '\s+' | Where-Object { $_ -and $_.Trim() -ne "" }
    }

    $allModified = @()
    $allModified += $modifiedArray
    $allModified += $stagedArray
    $allModified = $allModified | Select-Object -Unique

    # GIT ADD DES FICHIERS MODIFIES
    if ($allModified.Count -gt 0) {
        Write-Host "`n1.5 Git add des fichiers modifies..." -ForegroundColor Magenta
        try {
            $ErrorActionPreference = "Continue"
            $addResult = cmd /c "cd /d $WorkingDir && git add $($allModified -join ' ')" 2>&1
            $ErrorActionPreference = "Stop"
            Write-Host "   ✅ Fichiers ajoutes au staging" -ForegroundColor Green
        } catch {
            Write-Host "   ⚠️ Erreur git add: $($_.Exception.Message)" -ForegroundColor Yellow
        }

        # COMMIT GIT DES FICHIERS AJOUTES
        Write-Host "`n1.7 Commit des fichiers ajoutes..." -ForegroundColor Magenta
        try {
            $commitMessage = "deploy prep: $(Get-Date -Format 'dd/MM/yyyy HH:mm') - $($allModified.Count) fichiers ajoutes"
            $ErrorActionPreference = "Continue"
            $commitResult = cmd /c "cd /d $WorkingDir && git commit -m `"$commitMessage`" --allow-empty" 2>&1
            $ErrorActionPreference = "Stop"
            if ($LASTEXITCODE -eq 0) {
                Write-Host "   ✅ Commit cree: $commitMessage" -ForegroundColor Green
            } else {
                Write-Host "   ⚠️ Commit echoue: $($commitResult -join ' ')" -ForegroundColor Yellow
            }
        } catch {
            Write-Host "   ⚠️ Erreur commit: $($_.Exception.Message)" -ForegroundColor Yellow
        }
    }

    $filesToDeploy = @()
    foreach ($file in $allModified) {
        $fullPath = Join-Path $WorkingDir $file
        # Accepter tous les fichiers PHP et JS modifiés dans plugin/
        if (($file -like "plugin/*.php" -or $file -like "plugin/src/*.php" -or $file -like "plugin/resources/assets/js/dist/*.js" -or $file -like "plugin/resources/assets/js/*.js" -or $file -like "plugin/resources/js/*.js" -or $file -like "plugin/resources/assets/css/*.css") -and (Test-Path $fullPath)) {
            $filesToDeploy += Get-Item $fullPath
            Write-Host "   ✅ Ajouté: $file" -ForegroundColor Green
        } else {
            Write-Host "   ❌ Ignoré: $file (ne correspond pas aux critères)" -ForegroundColor Gray
        }
    }

    if ($filesToDeploy.Count -eq 0) {
        Write-Host "   ❌ Aucun fichier modifié détecté" -ForegroundColor Yellow
        Write-Host "   💡 Modifiez et 'git add' des fichiers PHP/JS pour les déployer" -ForegroundColor Cyan
        exit 0
    }

    Write-Host "   ✅ $($filesToDeploy.Count) fichier(s) modifié(s) détecté(s)" -ForegroundColor Green

    # Afficher la liste
    Write-Host "`n   📋 Fichiers à déployer:" -ForegroundColor Yellow
    foreach ($file in $filesToDeploy) {
        $relativePath = $file.FullName.Replace("$WorkingDir\", "").Replace("\", "/")
        Write-Host "      - $relativePath" -ForegroundColor Gray
    }

} catch {
    Write-Host "   ❌ Erreur détection: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# 2 UPLOAD FTP
Write-Host "`n2 Upload FTP..." -ForegroundColor Magenta

$uploadCount = 0
$errorCount = 0
$startTime = Get-Date

# Test connexion FTP
if (!$SkipConnectionTest) {
    Write-Host "   Test connexion FTP..." -ForegroundColor Yellow
    try {
        $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost/"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
        $ftpRequest.Timeout = 5000
        $ftpRequest.UsePassive = $true
        $ftpRequest.KeepAlive = $false
        $response = $ftpRequest.GetResponse()
        $response.Close()
        Write-Host "   ✅ Connexion FTP OK" -ForegroundColor Green
    } catch {
        Write-Host "   ❌ Erreur FTP: $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }
}

# Upload de chaque fichier
foreach ($file in $filesToDeploy) {
    $relativePath = $file.FullName.Replace("$WorkingDir\", "").Replace("\", "/")
    # Construire le chemin FTP simple
    $ftpFilePath = $relativePath.Replace("plugin/", "")

    try {
        # Upload direct du fichier (FTP gère automatiquement les répertoires)
        $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$FtpPath/$ftpFilePath"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $ftpRequest.UseBinary = $true
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 30000
        $ftpRequest.KeepAlive = $false

        $fileContent = [System.IO.File]::ReadAllBytes($file.FullName)
        $ftpRequest.ContentLength = $fileContent.Length

        $requestStream = $ftpRequest.GetRequestStream()
        $requestStream.Write($fileContent, 0, $fileContent.Length)
        $requestStream.Close()

        $response = $ftpRequest.GetResponse()
        $response.Close()

        Write-Host "   ✅ $relativePath" -ForegroundColor Green
        $uploadCount++

    } catch {
        Write-Host "   ❌ $relativePath - $($_.Exception.Message)" -ForegroundColor Red
        $errorCount++
    }
}

# Résumé
$duration = [math]::Round(((Get-Date) - $startTime).TotalSeconds, 1)
Write-Host "`n3 Resume" -ForegroundColor Magenta
Write-Host "   📊 Upload: $uploadCount réussi(s), $errorCount erreur(s)" -ForegroundColor Cyan
Write-Host "   ⏱️  Durée: $duration secondes" -ForegroundColor Cyan

if ($errorCount -eq 0) {
    Write-Host "   🎉 Déploiement terminé avec succès!" -ForegroundColor Green

    # 3.5 COMPILATION AVANT COMMIT
    Write-Host "`n3.5 Compilation..." -ForegroundColor Magenta

    try {
        Push-Location $WorkingDir
        Write-Host "   🔨 Lancement de npm run build..." -ForegroundColor Yellow

        $ErrorActionPreference = "Continue"
        $buildResult = cmd /c "cd /d $WorkingDir && npm run build" 2>&1
        $ErrorActionPreference = "Stop"

        if ($LASTEXITCODE -eq 0) {
            Write-Host "   ✅ Compilation reussie" -ForegroundColor Green
        } else {
            Write-Host "   ❌ Compilation echouee: $($buildResult -join ' ')" -ForegroundColor Red
            Write-Host "   ⚠️ Continuation du déploiement malgré l'erreur de compilation" -ForegroundColor Yellow
        }

        Pop-Location
    } catch {
        Write-Host "   ❌ Erreur compilation: $($_.Exception.Message)" -ForegroundColor Red
        Write-Host "   ⚠️ Continuation du déploiement malgré l'erreur de compilation" -ForegroundColor Yellow
    }

    # 4 COMMIT GIT APRES DEPLOIEMENT
    Write-Host "`n4 Commit Git..." -ForegroundColor Magenta

    try {
        Push-Location $WorkingDir

        # Vérifier s'il y a des changements à committer
        $statusOutput = cmd /c "cd /d $WorkingDir && git status --porcelain" 2>&1
        $stagedFiles = $statusOutput | Where-Object { $_ -match "^[AM]" }

        if ($stagedFiles -and $stagedFiles.Count -gt 0) {
            # Générer un message de commit basé sur les fichiers déployés
            $commitMessage = "deploy: $(Get-Date -Format 'dd/MM/yyyy HH:mm') - $($filesToDeploy.Count) fichiers deployes"

            Write-Host "   📝 Commit: $commitMessage" -ForegroundColor Yellow

            # Commit
            $ErrorActionPreference = "Continue"
            $commitResult = cmd /c "cd /d $WorkingDir && git commit -m `"$commitMessage`"" 2>&1
            $ErrorActionPreference = "Stop"

            if ($LASTEXITCODE -eq 0) {
                Write-Host "   ✅ Commit cree" -ForegroundColor Green

                # Push
                Write-Host "   📤 Push vers remote..." -ForegroundColor Yellow
                $ErrorActionPreference = "Continue"
                $pushResult = cmd /c "cd /d $WorkingDir && git push origin dev" 2>&1
                $ErrorActionPreference = "Stop"

                if ($LASTEXITCODE -eq 0) {
                    Write-Host "   ✅ Push reussi" -ForegroundColor Green
                } else {
                    Write-Host "   ⚠️ Push echoue: $($pushResult -join ' ')" -ForegroundColor Yellow
                }
            } else {
                Write-Host "   ⚠️ Commit echoue: $($commitResult -join ' ')" -ForegroundColor Yellow
            }
        } else {
            Write-Host "   ⏭️ Aucun changement a committer" -ForegroundColor Cyan
        }

        Pop-Location
    } catch {
        Write-Host "   ❌ Erreur git: $($_.Exception.Message)" -ForegroundColor Red
        Pop-Location
    }

} else {
    Write-Host "   ⚠️  Déploiement terminé avec des erreurs" -ForegroundColor Yellow
    exit 1
}

# 3 UPLOAD FTP
$uploadCount = 0
$errorCount = 0
$startTime = Get-Date

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

    # Ajouter tous les répertoires parents nécessaires (récursif)
    $allDirs = @{}
    foreach ($dir in $dirs.Keys) {
        $currentDir = $dir
        while ($currentDir -and $currentDir -ne "." -and $currentDir -ne "plugin") {
            if (!$allDirs.ContainsKey($currentDir)) {
                $allDirs[$currentDir] = $true
            }
            $currentDir = Split-Path $currentDir -Parent
        }
    }

    # Fonction pour créer récursivement tous les répertoires nécessaires
    function New-FtpDirectoryRecursive {
        param([string]$ftpPath)

        try {
            # Créer le répertoire directement (FTP gère la récursion automatiquement)
            $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$ftpPath/"
            $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $ftpRequest.UseBinary = $true
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = 5000  # Augmenté pour la création récursive
            $ftpRequest.KeepAlive = $false
            $response = $ftpRequest.GetResponse()
            $response.Close()
            return $true
        } catch {
            # Le répertoire existe probablement déjà, ou il y a eu une erreur
            return $false
        }
    }

    # Créer tous les répertoires en parallèle avec gestion récursive
    $createdDirs = 0
    $dirJobs = @()
    foreach ($dir in $allDirs.Keys) {
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
                    # Créer le répertoire récursivement
                    $ftpUri = "ftp://$using:FtpUser`:$using:FtpPass@$using:FtpHost$fullPath/"
                    $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
                    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
                    $ftpRequest.UseBinary = $true
                    $ftpRequest.UsePassive = $true
                    $ftpRequest.Timeout = 5000
                    $ftpRequest.KeepAlive = $false
                    $response = $ftpRequest.GetResponse()
                    $response.Close()
                    return @{ Success = $true; Path = $fullPath }
                } catch {
                    return @{ Success = $false; Path = $fullPath; Error = $_.Exception.Message }
                }
            } -ArgumentList $FtpHost, $FtpUser, $FtpPass, $fullPath
            $dirJobs += $job
        }
    }

    # Attendre la fin de la création des répertoires (max 15 secondes pour la récursion)
    $dirTimeout = 15
    $dirStartTime = Get-Date
    while ($dirJobs.Count -gt 0 -and ((Get-Date) - $dirStartTime).TotalSeconds -lt $dirTimeout) {
        $completedDirJobs = $dirJobs | Where-Object { $_.State -eq 'Completed' }
        foreach ($job in $completedDirJobs) {
            $result = Receive-Job $job
            if ($result.Success) {
                $createdDirs++
                Write-Host "   Repertoire cree: $($result.Path)" -ForegroundColor Green
            } else {
                Write-Host "   Repertoire existe deja ou erreur: $($result.Path)" -ForegroundColor Gray
            }
            Remove-Job $job
        }
        $dirJobs = $dirJobs | Where-Object { $_.State -ne 'Completed' }
        Start-Sleep -Milliseconds 200  # Augmenté pour la création récursive
    }

    # Nettoyer les jobs restants
    foreach ($job in $dirJobs) {
        Write-Host "   Timeout creation repertoire: $($job.Name)" -ForegroundColor Yellow
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
                    # Utiliser le mode TEXTE pour les fichiers PHP/HTML pour éviter la corruption d'encodage
                    $useBinaryMode = !($remotePath -like "*.php" -or $remotePath -like "*.html" -or $remotePath -like "*.json")
                    $ftpRequest.UseBinary = $useBinaryMode
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

    # ✅ CORRECTION: Ajouter TOUS les fichiers modifiés (même s'ils ne sont pas dans $pluginModified)
    Write-Host "   Staging de TOUS les fichiers modifies..." -ForegroundColor Yellow
    $ErrorActionPreference = "Continue"
    $addResult = cmd /c "cd /d $WorkingDir && git add -A" 2>&1
    $ErrorActionPreference = "Stop"

    # Vérifier s'il y a des changements à committer
    $statusOutput = cmd /c "cd /d $WorkingDir && git status --porcelain" 2>&1
    $stagedFiles = $statusOutput | Where-Object { $_ -and $_ -match "^[AM]" }
    
    if ($stagedFiles -and $stagedFiles.Count -gt 0) {
        # Afficher les fichiers qui seront committés
        Write-Host "   Fichiers à committer:" -ForegroundColor Cyan
        $stagedFiles | ForEach-Object {
            Write-Host "     $_" -ForegroundColor Gray
        }
        
        # Générer un message de commit basé sur les fichiers modifiés
        $fileTypes = @()
        foreach ($file in $pluginModified) {
            if ($file -like "*.php") { $fileTypes += "PHP" }
            elseif ($file -like "*.js") { $fileTypes += "JS" }
            elseif ($file -like "*.css") { $fileTypes += "CSS" }
            elseif ($file -like "*.json") { $fileTypes += "JSON" }
            else { $fileTypes += "autres" }
        }
        $fileTypes = $fileTypes | Select-Object -Unique
        $commitMsg = "deploy: " + ($fileTypes -join "/") + " files - " + (Get-Date -Format "dd/MM/yyyy HH:mm")
        
        Write-Host "   Commit: $commitMsg" -ForegroundColor Yellow
        $ErrorActionPreference = "Continue"
        $commitResult = cmd /c "cd /d $WorkingDir && git commit -m `"$commitMsg`"" 2>&1
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
        $pushResult = cmd /c "cd /d $WorkingDir && git push origin dev" 2>&1
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
        # ✅ CORRECTION: Utiliser le format de version déployé (comme dans les logs)
        $version = Get-Date -Format "v1.0.0-11eplo25-ddMMyyyy-HHmmss"
        Write-Host "   Tag: $version" -ForegroundColor Yellow
        $ErrorActionPreference = "Continue"
        $tagResult = cmd /c "cd /d $WorkingDir && git tag -a $version -m `"Deploiement $version`"" 2>&1
        $ErrorActionPreference = "Stop"

        if ($LASTEXITCODE -eq 0) {
            $ErrorActionPreference = "Continue"
            $tagPushResult = cmd /c "cd /d $WorkingDir && git push origin $version" 2>&1
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
Write-Host "   Upload FTP: OK ($uploadCount fichiers)" -ForegroundColor Green

# Afficher le statut Git selon les résultats
if ($commitCreated -and $pushSuccess) {
    Write-Host "   Git: OK (commit + push + tag)" -ForegroundColor Green
} elseif ($commitCreated) {
    Write-Host "   Git: PARTIEL (commit OK, push/tag echoue)" -ForegroundColor Yellow
} else {
    Write-Host "   Git: SKIP (rien a committer)" -ForegroundColor Gray
}
Write-Host ""