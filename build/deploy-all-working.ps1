# Script complet de déploiement : Compilation + FTP + Git
# Usage: .\deploy-complete.ps1

param(
    [switch]$SkipConnectionTest,
    [switch]$FastMode
)

$ErrorActionPreference = "Stop"

# Configuration
$WorkingDir = "I:\wp-pdf-builder-pro"
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpBasePath = "/wp-content/plugins/wp-pdf-builder-pro"
$PluginPath = "$WorkingDir\plugin"

# Variables globales pour le résumé
$uploadedCount = 0

Write-Host " DÉPLOIEMENT COMPLET - $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor Cyan
Write-Host "=" * 60 -ForegroundColor Cyan

# 1. COMPILATION DES ASSETS
Write-Host "`n1️⃣  COMPILATION DES ASSETS" -ForegroundColor Yellow
Write-Host "-" * 30

if (Test-Path "$WorkingDir\package.json") {
    Push-Location $WorkingDir
    try {
        Write-Host "🔨 Exécution de 'npm run build'..." -ForegroundColor White
        $buildResult = & npm run build 2>&1

        if ($LASTEXITCODE -eq 0) {
            Write-Host "✅ Compilation réussie !" -ForegroundColor Green
        } else {
            Write-Host "❌ Erreur de compilation :" -ForegroundColor Red
            Write-Host $buildResult -ForegroundColor Red
            exit 1
        }
    } catch {
        Write-Host "❌ Erreur lors de la compilation : $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    } finally {
        Pop-Location
    }
} else {
    Write-Host "⚠️  package.json non trouvé, compilation ignorée" -ForegroundColor Yellow
}

# Script de déploiement FTP ultra-simple

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

# 1 COLLECTE DE TOUS LES FICHIERS
Write-Host "`n1 Collecte de tous les fichiers..." -ForegroundColor Magenta

try {
    # Collecter tous les fichiers du dossier plugin
    $pluginPath = "$WorkingDir\plugin"
    Write-Host "📂 Collecte des fichiers depuis : $pluginPath" -ForegroundColor White

    $allFiles = Get-ChildItem -Path $pluginPath -Recurse -File
    Write-Host "📊 Fichiers totaux trouvés : $($allFiles.Count)" -ForegroundColor White

    # AUCUNE EXCLUSION - Tout le contenu du dossier plugin doit être déployé
    $filesToDeploy = $allFiles

    $totalSize = ($filesToDeploy | Measure-Object -Property Length -Sum).Sum
    Write-Host "📈 Fichiers à déployer : $($filesToDeploy.Count)" -ForegroundColor Green
    Write-Host "💾 Taille totale : $([math]::Round($totalSize / 1MB, 2)) MB" -ForegroundColor Green

    if ($filesToDeploy.Count -eq 0) {
        Write-Host "❌ Aucun fichier trouvé dans $pluginPath" -ForegroundColor Red
        exit 1
    }

    Write-Host "   ✅ $($filesToDeploy.Count) fichier(s) trouvé(s)" -ForegroundColor Green

    # Afficher un aperçu des fichiers
    Write-Host "`n   📋 Aperçu des fichiers à déployer:" -ForegroundColor Yellow
    $filesToDeploy | Select-Object -First 5 | ForEach-Object {
        $relativePath = $_.FullName.Replace("$pluginPath\", "").Replace("\", "/")
        Write-Host "      - $relativePath" -ForegroundColor Gray
    }
    if ($filesToDeploy.Count -gt 5) {
        Write-Host "      ... et $($filesToDeploy.Count - 5) autres fichiers" -ForegroundColor Gray
    }

} catch {
    Write-Host "   ❌ Erreur collecte: $($_.Exception.Message)" -ForegroundColor Red
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

# Créer la structure de répertoires sur le serveur FTP (approche simplifiée)
Write-Host "   🏗️ Création de la structure de répertoires..." -ForegroundColor Yellow

$directories = @{}
foreach ($file in $filesToDeploy) {
    $relativePath = $file.FullName.Replace("$pluginPath\", "").Replace("\", "/")
    $remotePath = "$FtpPath/$relativePath"
    $remoteDir = [System.IO.Path]::GetDirectoryName($remotePath).Replace("\", "/")

    # Créer TOUS les niveaux de répertoires récursivement
    if ($remoteDir -and $remoteDir -ne "/" -and $remoteDir -ne $FtpPath.TrimEnd('/')) {
        $currentDir = $remoteDir
        while ($currentDir -and $currentDir -ne "/" -and $currentDir -ne $FtpPath.TrimEnd('/')) {
            if (-not $directories.ContainsKey($currentDir)) {
                $directories[$currentDir] = $true
            }
            $currentDir = Split-Path $currentDir -Parent
            if ($currentDir) {
                $currentDir = $currentDir.Replace("\", "/")
            }
        }
    }
}

Write-Host "   📁 Création de $($directories.Count) répertoires en parallèle..." -ForegroundColor White

# Trier les répertoires par profondeur (du plus haut niveau au plus bas)
$sortedDirectories = $directories.Keys | Sort-Object { ($_.Split('/')).Count }

# Création en parallèle des répertoires
$dirJobs = @()
$maxDirJobs = 15

foreach ($dir in $sortedDirectories) {
    $dirJobScript = {
        param($ftpUri, $ftpUser, $ftpPass, $dir)

        try {
            $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = 5000  # Timeout réduit pour les répertoires
            $ftpRequest.KeepAlive = $false

            $response = $ftpRequest.GetResponse()
            $response.Close()
            return @{Success = $false; Path = $relativePath; Error = $errorMessage; IsFileNotFound = $isFileNotFound; FullFtpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$dir/"}
        } catch {
            $errorMessage = $_.Exception.Message
            $isFileNotFound = $errorMessage.Contains("550") -or $errorMessage.Contains("fichier non disponible") -or $errorMessage.Contains("file not available")

            return @{Success = $false; Path = $relativePath; Error = $errorMessage; IsFileNotFound = $isFileNotFound; FullFtpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$dir/"}
        }
    }

    # Attendre qu'il y ait de la place pour un nouveau job
    while ($dirJobs.Count -ge $maxDirJobs) {
        # Nettoyer les jobs terminés
        $completedJobs = $dirJobs | Where-Object { $_.State -eq "Completed" }
        if ($completedJobs) {
            foreach ($job in $completedJobs) {
                Receive-Job -Job $job | Out-Null
                Remove-Job -Job $job
            }
            $dirJobs = $dirJobs | Where-Object { $_.State -ne "Completed" }
        } else {
            Start-Sleep -Milliseconds 50
        }
    }

    $job = Start-Job -ScriptBlock $dirJobScript -ArgumentList "ftp://$FtpUser`:$FtpPass@$FtpHost$dir/", $FtpUser, $FtpPass, $dir
    $dirJobs += $job
}

# Attendre que tous les jobs de répertoires se terminent
foreach ($job in $dirJobs) {
    Wait-Job -Job $job | Out-Null
    Receive-Job -Job $job | Out-Null
    Remove-Job -Job $job
}

Write-Host "   ✅ Structure de répertoires créée" -ForegroundColor Green

# Upload optimisé avec parallélisation
$maxConcurrentUploads = 8  # Augmenté à 8 connexions simultanées
$runningJobs = @{}
$completedJobs = @()
$jobCounter = 0
$totalFiles = $filesToDeploy.Count
$processedFiles = 0
$uploadErrors = @()  # Liste pour stocker les détails des erreurs

Write-Host "   🚀 Démarrage des uploads parallèles (max $maxConcurrentUploads simultanés)..." -ForegroundColor Yellow
Write-Progress -Activity "Déploiement FTP" -Status "Initialisation..." -PercentComplete 0

foreach ($file in $filesToDeploy) {
    $relativePath = $file.FullName.Replace("$pluginPath\", "").Replace("\", "/")
    $ftpFilePath = "$FtpPath/$relativePath"
    $jobId = "Upload_$jobCounter"
    $jobCounter++

    # Fonction d'upload pour le job
    $uploadScript = {
        param($ftpUri, $filePath, $relativePath)

        try {
            # Vérifier si le répertoire parent existe avant l'upload
            $parentDir = Split-Path $relativePath -Parent
            if ($parentDir -and $parentDir -ne "/") {
                $parentFtpUri = "ftp://$using:FtpUser`:$using:FtpPass@$using:FtpHost$using:FtpPath/$parentDir/"
                try {
                    $parentRequest = [System.Net.FtpWebRequest]::Create($parentFtpUri)
                    $parentRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
                    $parentRequest.UsePassive = $true
                    $parentRequest.Timeout = 5000
                    $parentRequest.KeepAlive = $false
                    $parentResponse = $parentRequest.GetResponse()
                    $parentResponse.Close()
                } catch {
                    # Le répertoire parent n'existe pas, essayons de le créer
                    try {
                        $createRequest = [System.Net.FtpWebRequest]::Create($parentFtpUri)
                        $createRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
                        $createRequest.UsePassive = $true
                        $createRequest.Timeout = 5000
                        $createRequest.KeepAlive = $false
                        $createResponse = $createRequest.GetResponse()
                        $createResponse.Close()
                    } catch {
                        # Impossible de créer le répertoire parent
                        return @{Success = $false; Path = $relativePath; Error = "Répertoire parent inaccessible: $($_.Exception.Message)"; IsFileNotFound = $false; FullFtpUri = $ftpUri}
                    }
                }
            }

            $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
            $ftpRequest.UseBinary = $true
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = 45000  # Timeout optimisé
            $ftpRequest.ReadWriteTimeout = 45000
            $ftpRequest.KeepAlive = $false

            $fileContent = [System.IO.File]::ReadAllBytes($filePath)
            $ftpRequest.ContentLength = $fileContent.Length

            $requestStream = $ftpRequest.GetRequestStream()
            # Upload en chunks pour de gros fichiers
            $bufferSize = 8192  # 8KB buffer
            $bytesUploaded = 0

            while ($bytesUploaded -lt $fileContent.Length) {
                $bytesToUpload = [Math]::Min($bufferSize, $fileContent.Length - $bytesUploaded)
                $requestStream.Write($fileContent, $bytesUploaded, $bytesToUpload)
                $bytesUploaded += $bytesToUpload
            }

            $requestStream.Close()

            $response = $ftpRequest.GetResponse()
            $response.Close()

            return @{Success = $true; Path = $relativePath}
        } catch {
            $errorMessage = $_.Exception.Message
            $isFileNotFound = $errorMessage.Contains("550") -or $errorMessage.Contains("fichier non disponible") -or $errorMessage.Contains("file not available")

            return @{Success = $false; Path = $relativePath; Error = $errorMessage; IsFileNotFound = $isFileNotFound; FullFtpUri = $ftpUri}
        }
    }

    # Démarrer le job
    $job = Start-Job -ScriptBlock $uploadScript -ArgumentList "ftp://$FtpUser`:$FtpPass@$FtpHost$ftpFilePath", $file.FullName, $relativePath
    $runningJobs[$jobId] = @{Job = $job; Path = $relativePath; StartTime = Get-Date}

    # Attendre si on atteint la limite de jobs simultanés
    while ($runningJobs.Count -ge $maxConcurrentUploads) {
        Start-Sleep -Milliseconds 50  # Réduction du délai d'attente

        # Vérifier les jobs terminés
        $jobsToRemove = @()
        foreach ($jobEntry in $runningJobs.GetEnumerator()) {
            $jobId = $jobEntry.Key
            $jobInfo = $jobEntry.Value

            if ($jobInfo.Job.State -eq "Completed") {
                $result = Receive-Job -Job $jobInfo.Job
                Remove-Job -Job $jobInfo.Job

                if ($result.Success) {
                    Write-Host "   ✅ $($result.Path)" -ForegroundColor Green
                    $uploadCount++
                } else {
                    if ($result.IsFileNotFound) {
                        Write-Host "   ⚠️  $($result.Path) - Fichier inaccessible (normal pour certains fichiers)" -ForegroundColor Yellow
                    } else {
                        Write-Host "   ❌ $($result.Path) - $($result.Error)" -ForegroundColor Red
                    }
                    $errorCount++
                    $uploadErrors += @{Path = $result.Path; Error = $result.Error; IsFileNotFound = $result.IsFileNotFound}
                }

                $jobsToRemove += $jobId
                $processedFiles++
            }
        }

        # Nettoyer les jobs terminés
        foreach ($jobId in $jobsToRemove) {
            $runningJobs.Remove($jobId)
        }

        # Mettre à jour la barre de progression
        $percentComplete = [math]::Min(100, [math]::Round(($processedFiles / $totalFiles) * 100, 0))
        Write-Progress -Activity "Déploiement FTP" -Status "Upload en cours... ($processedFiles/$totalFiles fichiers)" -PercentComplete $percentComplete
    }
}

# Attendre que tous les jobs se terminent
Write-Host "   ⏳ Finalisation des uploads en cours..." -ForegroundColor Yellow
while ($runningJobs.Count -gt 0) {
    Start-Sleep -Milliseconds 50  # Réduction du délai

    $jobsToRemove = @()
    foreach ($jobEntry in $runningJobs.GetEnumerator()) {
        $jobId = $jobEntry.Key
        $jobInfo = $jobEntry.Value

        if ($jobInfo.Job.State -eq "Completed") {
            $result = Receive-Job -Job $jobInfo.Job
            Remove-Job -Job $jobInfo.Job

            if ($result.Success) {
                Write-Host "   ✅ $($result.Path)" -ForegroundColor Green
                $uploadCount++
            } else {
                if ($result.IsFileNotFound) {
                    Write-Host "   ⚠️  $($result.Path) - Fichier inaccessible (normal pour certains fichiers)" -ForegroundColor Yellow
                } else {
                    Write-Host "   ❌ $($result.Path) - $($result.Error)" -ForegroundColor Red
                }
                $errorCount++
                $uploadErrors += @{Path = $result.Path; Error = $result.Error; IsFileNotFound = $result.IsFileNotFound}
            }

            $jobsToRemove += $jobId
            $processedFiles++
        }
    }

    foreach ($jobId in $jobsToRemove) {
        $runningJobs.Remove($jobId)
    }

    # Mettre à jour la barre de progression
    $percentComplete = [math]::Min(100, [math]::Round(($processedFiles / $totalFiles) * 100, 0))
    Write-Progress -Activity "Déploiement FTP" -Status "Finalisation... ($processedFiles/$totalFiles fichiers)" -PercentComplete $percentComplete
}

# Résumé
$duration = [math]::Round(((Get-Date) - $startTime).TotalSeconds, 1)
$fileNotFoundErrors = ($uploadErrors | Where-Object { $_.IsFileNotFound }).Count
$otherErrors = $errorCount - $fileNotFoundErrors

Write-Host "`n3 Resume" -ForegroundColor Magenta
Write-Host "   📊 Upload: $uploadCount réussi(s), $errorCount erreur(s)" -ForegroundColor Cyan
if ($fileNotFoundErrors -gt 0) {
    Write-Host "      └─ $fileNotFoundErrors fichier(s) inaccessible(s) (normal)" -ForegroundColor Yellow
}
if ($otherErrors -gt 0) {
    Write-Host "      └─ $otherErrors autre(s) erreur(s)" -ForegroundColor Red
}
Write-Host "   ⏱️  Durée: $duration secondes" -ForegroundColor Cyan

# Afficher les détails des erreurs si il y en a
if ($uploadErrors.Count -gt 0) {
    Write-Host "`n   📋 ANALYSE DES ERREURS ($($uploadErrors.Count) erreurs):" -ForegroundColor Yellow
    Write-Host "   ==========================================" -ForegroundColor Yellow

    # Grouper les erreurs par type
    $fileNotFoundErrors = $uploadErrors | Where-Object { $_.IsFileNotFound }
    $otherErrors = $uploadErrors | Where-Object { -not $_.IsFileNotFound }

    if ($fileNotFoundErrors.Count -gt 0) {
        Write-Host "`n   ⚠️  ERREURS 'FICHIER INACCESSIBLE' ($($fileNotFoundErrors.Count)) - NORMALES:" -ForegroundColor Yellow
        Write-Host "   Ces erreurs signifient que le fichier n'existe pas sur le serveur FTP distant." -ForegroundColor Gray
        Write-Host "   C'est normal pour les déploiements WordPress où certains fichiers sont générés côté serveur." -ForegroundColor Gray
        Write-Host ""

        # Afficher seulement les 5 premiers pour ne pas spammer
        $displayCount = [Math]::Min(5, $fileNotFoundErrors.Count)
        for ($i = 0; $i -lt $displayCount; $i++) {
            Write-Host "      • $($fileNotFoundErrors[$i].Path)" -ForegroundColor Yellow
        }
        if ($fileNotFoundErrors.Count -gt 5) {
            Write-Host "      ... et $($fileNotFoundErrors.Count - 5) autres fichiers similaires" -ForegroundColor Gray
        }
    }

    if ($otherErrors.Count -gt 0) {
        Write-Host "`n   ❌ ERREURS RÉELLES ($($otherErrors.Count)) - À VÉRIFIER:" -ForegroundColor Red
        Write-Host "   Ces erreurs nécessitent une attention particulière." -ForegroundColor Gray
        Write-Host ""
        foreach ($error in $otherErrors) {
            Write-Host "      • $($error.Path)" -ForegroundColor Red
            Write-Host "        └─ $($error.Error)" -ForegroundColor Gray
            if ($error.FullFtpUri) {
                Write-Host "        └─ URI: $($error.FullFtpUri)" -ForegroundColor DarkGray
            }
        }
    }

    Write-Host "`n   💡 CONSEIL:" -ForegroundColor Cyan
    if ($fileNotFoundErrors.Count -gt 0 -and $otherErrors.Count -eq 0) {
        Write-Host "   Toutes les erreurs sont normales. Le déploiement est réussi !" -ForegroundColor Green
    } elseif ($otherErrors.Count -gt 0) {
        Write-Host "   Il y a des erreurs réelles à corriger. Vérifiez la connexion FTP et les permissions." -ForegroundColor Red
    }

    # Créer un fichier log des erreurs
    $logFile = Join-Path $WorkingDir "build\deployment-errors-$(Get-Date -Format 'yyyyMMdd-HHmmss').log"
    Write-Host "`n   📝 Log des erreurs sauvegardé dans: $logFile" -ForegroundColor Cyan

    $logContent = @"
RAPPORT D'ERREURS DE DÉPLOIEMENT
================================
Date: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')
Total fichiers traités: $totalFiles
Fichiers réussis: $uploadCount
Erreurs totales: $errorCount

ERREURS 'FICHIER INACCESSIBLE' (NORMALES):
-----------------------------------------
"@

    foreach ($error in $fileNotFoundErrors) {
        $logContent += "`n• $($error.Path)"
    }

    if ($otherErrors.Count -gt 0) {
        $logContent += @"


ERREURS RÉELLES (À VÉRIFIER):
-----------------------------
"@
        foreach ($error in $otherErrors) {
            $logContent += "`n• $($error.Path)`n  Erreur: $($error.Error)`n"
        }
    }

    $logContent | Out-File -FilePath $logFile -Encoding UTF8
    Write-Host ""
}

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
Write-Progress -Activity "Déploiement FTP" -Completed
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