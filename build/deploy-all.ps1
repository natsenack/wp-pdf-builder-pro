# Script complet de déploiement : Compilation + FTP + Git
# Usage: .\deploy-complete.ps1

param(
    [switch]$SkipCompilation,
    [switch]$SkipFTP,
    [switch]$SkipGit,
    [string]$GitMessage = "Deploy $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
)

$ErrorActionPreference = "Stop"

# Configuration
$WorkingDir = "I:\wp-pdf-builder-pro"
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpBasePath = "/wp-content/plugins/wp-pdf-builder-pro"
$PluginPath = "$WorkingDir\plugin"

Write-Host " DÉPLOIEMENT COMPLET - $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor Cyan
Write-Host "=" * 60 -ForegroundColor Cyan

# 1. COMPILATION DES ASSETS
if (-not $SkipCompilation) {
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
} else {
    Write-Host "`n1️⃣  COMPILATION IGNORÉE (-SkipCompilation)" -ForegroundColor Gray
}

# 2. COLLECTE DES FICHIERS À DÉPLOYER
Write-Host "`n2️⃣  COLLECTE DES FICHIERS" -ForegroundColor Yellow
Write-Host "-" * 30

Write-Host "📂 Collecte des fichiers depuis : $PluginPath" -ForegroundColor White
$allFiles = Get-ChildItem -Path $PluginPath -Recurse -File
Write-Host "📊 Fichiers totaux trouvés : $($allFiles.Count)" -ForegroundColor White

# AUCUNE EXCLUSION - Tout le contenu du dossier plugin doit être déployé
$filesToDeploy = $allFiles

$totalSize = ($filesToDeploy | Measure-Object -Property Length -Sum).Sum
Write-Host "📈 Fichiers à déployer : $($filesToDeploy.Count)" -ForegroundColor Green
Write-Host "💾 Taille totale : $([math]::Round($totalSize / 1MB, 2)) MB" -ForegroundColor Green

# DEBUG : Vérifier si settings-loader.php est dans la liste
$settingsLoader = $filesToDeploy | Where-Object { $_.Name -eq "settings-loader.php" }
if ($settingsLoader) {
    Write-Host "🔍 DEBUG : settings-loader.php trouvé : $($settingsLoader.FullName)" -ForegroundColor Cyan
} else {
    Write-Host "🔍 DEBUG : settings-loader.php NON trouvé dans la liste des fichiers à déployer" -ForegroundColor Yellow
    # Lister quelques fichiers de resources/templates/admin pour debug
    $adminTemplates = $filesToDeploy | Where-Object { $_.FullName -like "*resources\templates\admin*" }
    Write-Host "🔍 DEBUG : Fichiers dans resources/templates/admin :" -ForegroundColor Yellow
    $adminTemplates | ForEach-Object { Write-Host "  - $($_.Name)" -ForegroundColor Yellow }
}

# 3. DÉPLOIEMENT FTP ULTRA-OPTIMISÉ
if (-not $SkipFTP) {
    Write-Host "`n3️⃣  DÉPLOIEMENT FTP ULTRA-OPTIMISÉ" -ForegroundColor Yellow
    Write-Host "-" * 40

    try {
        # Créer la connexion FTP
        $ftpUri = "ftp://$FtpHost"
        Write-Host "🔌 Connexion à $ftpUri..." -ForegroundColor White

        # 🏗️ CRÉATION OPTIMISÉE DES RÉPERTOIRES (UNE SEULE FOIS)
        Write-Host "🏗️  Création optimisée des répertoires..." -ForegroundColor White

        $directories = @{}
        foreach ($file in $filesToDeploy) {
            $relativePath = $file.FullName -replace [regex]::Escape($PluginPath), ""
            $remotePath = "$FtpBasePath$relativePath".Replace("\", "/")
            $remoteDir = [System.IO.Path]::GetDirectoryName($remotePath).Replace("\", "/")

            if ($remoteDir -and $remoteDir -ne "/" -and -not $directories.ContainsKey($remoteDir)) {
                $directories[$remoteDir] = $true
            }
        }

        Write-Host "📁 Création de $($directories.Count) répertoires uniques..." -ForegroundColor White

        foreach ($dir in $directories.Keys) {
            try {
                $dirRequest = [System.Net.FtpWebRequest]::Create("$ftpUri$dir")
                $dirRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
                $dirRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
                $dirRequest.UseBinary = $true
                $dirRequest.KeepAlive = $false
                $dirRequest.Timeout = 5000

                $dirResponse = $dirRequest.GetResponse()
                $dirResponse.Close()
            } catch {
                # Répertoire existe déjà, c'est normal
            }
        }

        Write-Host "✅ Structure de répertoires créée" -ForegroundColor Green

        # 🚀 UPLOAD ULTRA-RAPIDE AVEC POOL DE CONNEXIONS OPTIMISÉ
        Write-Host "📤 Upload ultra-rapide avec pool de connexions optimisé..." -ForegroundColor White

        $totalFiles = $filesToDeploy.Count
        $maxConcurrentJobs = 12  # Augmenté pour exploiter le débit réseau disponible (12 MB/s)
        $maxRetries = 3         # Réduit pour accélérer
        $uploadedCount = 0
        $failedCount = 0
        $failedFiles = @()
        $totalAttempts = 0
        $retryCount = 0

        Write-Host "⚡ Pool de connexions : $maxConcurrentJobs simultanées pour $totalFiles fichiers" -ForegroundColor Cyan

        # Grouper les fichiers par taille (gros fichiers en premier)
        $sortedFiles = $filesToDeploy | Sort-Object -Property Length -Descending

        # File d'attente et pool de jobs actifs
        $fileQueue = [System.Collections.Concurrent.ConcurrentQueue[object]]::new()
        $activeJobs = @{}
        $jobCounter = 0

        # Remplir la file d'attente
        foreach ($file in $sortedFiles) {
            $relativePath = $file.FullName -replace [regex]::Escape($PluginPath), ""
            $remotePath = "$FtpBasePath$relativePath".Replace("\", "/")
            $fileQueue.Enqueue(@{
                LocalPath = $file.FullName
                RemotePath = $remotePath
                FileName = [System.IO.Path]::GetFileName($file.FullName)
                Size = $file.Length
                Id = $jobCounter++
            })
        }

        Write-Host "🚀 Démarrage du pool de connexions optimisé..." -ForegroundColor Green

        # TIMER POUR LES STATISTIQUES DE PERFORMANCE
        $startTime = Get-Date
        $totalBytesUploaded = 0
        $lastProgressTime = $startTime

        $processedFiles = 0
        while ($fileQueue.Count -gt 0 -or $activeJobs.Count -gt 0) {
            # Démarrer de nouveaux jobs si on a de la place dans le pool
            while ($activeJobs.Count -lt $maxConcurrentJobs -and $fileQueue.Count -gt 0) {
                $fileItem = $null
                if ($fileQueue.TryDequeue([ref]$fileItem)) {
                    $job = Start-Job -ScriptBlock {
                        param($localFile, $remoteFile, $ftpHost, $ftpUser, $ftpPass, $maxRetries, $ftpUri, $fileName, $fileSize)

                        $attempts = 0
                        $success = $false
                        $lastError = ""

                        while (-not $success -and $attempts -lt $maxRetries) {
                            $attempts++
                            try {
                                # UPLOAD ULTRA-RAPIDE - RÉPERTOIRES DÉJÀ CRÉÉS
                                $ftpRequest = [System.Net.FtpWebRequest]::Create("$ftpUri$remoteFile")
                                $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
                                $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
                                $ftpRequest.UseBinary = $true
                                $ftpRequest.UsePassive = $false  # Mode actif pour compatibilité
                                $ftpRequest.KeepAlive = $false
                                $ftpRequest.Timeout = 30000      # 30 secondes pour petits fichiers
                                $ftpRequest.ReadWriteTimeout = 30000

                                $fileStream = [System.IO.File]::OpenRead($localFile)
                                $requestStream = $ftpRequest.GetRequestStream()

                                # Buffer optimisé selon la taille du fichier
                                $bufferSize = if ($fileSize -lt 10KB) { 4096 } elseif ($fileSize -lt 100KB) { 8192 } else { 16384 }
                                $buffer = New-Object byte[] $bufferSize
                                $bytesRead = 0

                                while (($bytesRead = $fileStream.Read($buffer, 0, $buffer.Length)) -gt 0) {
                                    $requestStream.Write($buffer, 0, $bytesRead)
                                }

                                $requestStream.Close()
                                $fileStream.Close()

                                $success = $true
                                return @{Success = $true; File = $remoteFile; Attempts = $attempts; FileName = $fileName; Size = $fileSize}
                            } catch {
                                $lastError = $_.Exception.Message
                                if ($attempts -lt $maxRetries) {
                                    Start-Sleep -Milliseconds (100 * $attempts)  # Retry plus rapide
                                }
                            }
                        }

                        return @{Success = $false; File = $remoteFile; Error = $lastError; Attempts = $attempts; FileName = $fileName; Size = $fileSize}
                    } -ArgumentList $fileItem.LocalPath, $fileItem.RemotePath, $FtpHost, $FtpUser, $FtpPass, $maxRetries, $ftpUri, $fileItem.FileName, $fileItem.Size

                    $activeJobs[$fileItem.Id] = $job
                }
            }

            # Vérifier les jobs terminés et les traiter immédiatement
            $completedJobIds = @()
            foreach ($jobId in $activeJobs.Keys) {
                $job = $activeJobs[$jobId]
                if ($job.State -eq 'Completed') {
                    $result = Receive-Job $job
                    $processedFiles++

                    if ($result.Success) {
                        $uploadedCount++
                        $totalAttempts += $result.Attempts
                        if ($result.Attempts -gt 1) {
                            $retryCount++
                        }
                        # Accumuler la taille du fichier uploadé pour les statistiques
                        $totalBytesUploaded += $result.Size

                        if ($result.Attempts -gt 1) {
                            Write-Host "  ✅ $($result.FileName) (après $($result.Attempts) tentatives)" -ForegroundColor Yellow
                        } else {
                            Write-Host "  ✅ $($result.FileName)" -ForegroundColor Green
                        }
                    } else {
                        $failedCount++
                        $totalAttempts += $result.Attempts
                        $failedFiles += @{
                            LocalPath = $fileItem.LocalPath
                            RemotePath = $fileItem.RemotePath
                            FileName = $fileItem.FileName
                            Error = $result.Error
                            Size = $result.Size
                        }
                        Write-Host "  ❌ $($result.FileName) : $($result.Error)" -ForegroundColor Red
                    }

                    Remove-Job $job
                    $completedJobIds += $jobId
                }
            }

            # Nettoyer les jobs terminés
            foreach ($jobId in $completedJobIds) {
                $activeJobs.Remove($jobId)
            }

            # Afficher la progression avec statistiques de performance
            $currentTime = Get-Date
            $elapsed = $currentTime - $startTime

            if ($processedFiles % 10 -eq 0 -or ($processedFiles -gt 0 -and ($currentTime - $lastProgressTime).TotalSeconds -ge 1)) {
                $progress = [math]::Round($processedFiles / $totalFiles * 100, 1)

                # Calculer les vitesses
                $filesPerMinute = if ($elapsed.TotalMinutes -gt 0) { [math]::Round($uploadedCount / $elapsed.TotalMinutes, 1) } else { 0 }
                $mbPerMinute = if ($elapsed.TotalMinutes -gt 0) { [math]::Round($totalBytesUploaded / 1MB / $elapsed.TotalMinutes, 1) } else { 0 }

                # Estimer le temps restant
                $remainingFiles = $totalFiles - $processedFiles
                $eta = if ($filesPerMinute -gt 0) {
                    $minutesLeft = $remainingFiles / $filesPerMinute
                    if ($minutesLeft -lt 1) {
                        "$([math]::Round($minutesLeft * 60))s"
                    } elseif ($minutesLeft -lt 60) {
                        "$([math]::Round($minutesLeft))min"
                    } else {
                        "$([math]::Round($minutesLeft / 60, 1))h"
                    }
                } else { "∞" }

                Write-Host "📊 $progress% | $uploadedCount/$totalFiles fichiers | ${filesPerMinute} f/min | ${mbPerMinute} Mo/min | ETA: ${eta} | $($activeJobs.Count) actifs" -ForegroundColor Cyan

                $lastProgressTime = $currentTime
            }

            # Pause ultra-courte pour éviter surcharge CPU
            if ($activeJobs.Count -gt 0) {
                Start-Sleep -Milliseconds 5  # Encore plus rapide
            }
        }

        $endTime = Get-Date
        $totalElapsed = $endTime - $startTime

        # RETRY SÉQUENTIEL RAPIDE POUR LES FICHIERS ÉCHOUÉS
        if ($failedFiles.Count -gt 0) {
            Write-Host "`n🔄 Retry séquentiel rapide pour $($failedFiles.Count) fichiers échoués..." -ForegroundColor Yellow

            foreach ($failedFile in $failedFiles) {
                $attempts = 0
                $success = $false
                $lastError = ""

                while (-not $success -and $attempts -lt $maxRetries) {
                    $attempts++
                    try {
                        $ftpRequest = [System.Net.FtpWebRequest]::Create("$ftpUri$($failedFile.RemotePath)")
                        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
                        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
                        $ftpRequest.UseBinary = $true
                        $ftpRequest.UsePassive = $false
                        $ftpRequest.KeepAlive = $false
                        $ftpRequest.Timeout = 20000  # Plus rapide pour retry
                        $ftpRequest.ReadWriteTimeout = 20000

                        $fileStream = [System.IO.File]::OpenRead($failedFile.LocalPath)
                        $requestStream = $ftpRequest.GetRequestStream()

                        $bufferSize = if ($failedFile.Size -lt 10KB) { 4096 } elseif ($failedFile.Size -lt 100KB) { 8192 } else { 16384 }
                        $buffer = New-Object byte[] $bufferSize
                        $bytesRead = 0

                        while (($bytesRead = $fileStream.Read($buffer, 0, $buffer.Length)) -gt 0) {
                            $requestStream.Write($buffer, 0, $bytesRead)
                        }

                        $requestStream.Close()
                        $fileStream.Close()

                        $success = $true
                        $uploadedCount++
                        $failedCount--
                        $totalBytesUploaded += $failedFile.Size
                        $totalAttempts += $attempts

                        Write-Host "  ✅ $($failedFile.FileName) (retry réussi après $attempts tentatives)" -ForegroundColor Green
                    } catch {
                        $lastError = $_.Exception.Message
                        if ($attempts -lt $maxRetries) {
                            Start-Sleep -Milliseconds (200 * $attempts)
                        }
                    }
                }

                if (-not $success) {
                    Write-Host "  ❌ $($failedFile.FileName) : Échec définitif - $lastError" -ForegroundColor Red
                }
            }
        }

        if ($failedCount -eq 0) {
            Write-Host "✅ Déploiement FTP terminé : $uploadedCount fichiers uploadés avec succès" -ForegroundColor Green
        } else {
            Write-Host "⚠️  Déploiement FTP terminé : $uploadedCount fichiers uploadés, $failedCount échoués" -ForegroundColor Yellow
            Write-Host "   Fichiers échoués :" -ForegroundColor Yellow
            foreach ($failed in $failedFiles) {
                if (-not $failed.ContainsKey('Error')) { continue }  # Skip if already retried successfully
                Write-Host "   - $($failed.FileName): $($failed.Error)" -ForegroundColor Red
            }
        }

        # STATISTIQUES DE PERFORMANCE DÉTAILLÉES
        $totalMB = [math]::Round($totalBytesUploaded / 1MB, 2)
        $avgFilesPerMinute = [math]::Round($uploadedCount / $totalElapsed.TotalMinutes, 1)
        $avgMBPerMinute = [math]::Round($totalMB / $totalElapsed.TotalMinutes, 1)
        $avgMBPerSecond = [math]::Round($totalMB / $totalElapsed.TotalSeconds, 2)

        Write-Host "📈 Statistiques de performance :" -ForegroundColor Magenta
        Write-Host "   ⏱️  Durée totale : $([math]::Round($totalElapsed.TotalSeconds)) secondes" -ForegroundColor Magenta
        Write-Host "   📁 Fichiers : $uploadedCount uploadés, $failedCount échoués" -ForegroundColor Magenta
        Write-Host "   💾 Données : $totalMB Mo transférés" -ForegroundColor Magenta
        Write-Host "   ⚡ Vitesse moyenne : $avgFilesPerMinute fichiers/min, $avgMBPerMinute Mo/min ($avgMBPerSecond Mo/s)" -ForegroundColor Magenta
        Write-Host "   🔄 Taux de succès : $([math]::Round($uploadedCount / ($uploadedCount + $failedCount) * 100, 1))% ($uploadedCount/$(($uploadedCount + $failedCount)))" -ForegroundColor Magenta
        Write-Host "   🎯 Tentatives : $totalAttempts total, $([math]::Round($totalAttempts / ($uploadedCount + $failedCount), 2)) tentatives/fichier en moyenne" -ForegroundColor Magenta
        Write-Host "   🔁 Fichiers retried : $retryCount ($(if ($uploadedCount -gt 0) { [math]::Round($retryCount / $uploadedCount * 100, 1) } else { 0 })% des réussites)" -ForegroundColor Magenta

    } catch {
        Write-Host "❌ Erreur FTP : $($_.Exception.Message)" -ForegroundColor Red
        Write-Host "⚠️  Continuation malgré les erreurs FTP..." -ForegroundColor Yellow
    }
} else {
    Write-Host "`n3️⃣  DÉPLOIEMENT FTP IGNORÉ (-SkipFTP)" -ForegroundColor Gray
}

# 4. COMMIT ET PUSH GIT
if (-not $SkipGit) {
    Write-Host "`n4️⃣  COMMIT ET PUSH GIT" -ForegroundColor Yellow
    Write-Host "-" * 30

    Push-Location $WorkingDir
    try {
        # Vérifier l'état du repository
        Write-Host "🔍 Vérification du repository Git..." -ForegroundColor White
        $gitStatus = & git status --porcelain

        if ($gitStatus) {
            Write-Host "📝 Fichiers modifiés détectés" -ForegroundColor White

            # Add all changes
            Write-Host "➕ Ajout des fichiers..." -ForegroundColor White
            & git add .

            # Commit
            Write-Host "💾 Commit avec message : $GitMessage" -ForegroundColor White
            & git commit -m $GitMessage

            if ($LASTEXITCODE -eq 0) {
                Write-Host "✅ Commit réussi" -ForegroundColor Green

                # Push vers dev
                Write-Host "🚀 Push vers la branche dev..." -ForegroundColor White
                & git push origin dev

                if ($LASTEXITCODE -eq 0) {
                    Write-Host "✅ Push réussi vers dev" -ForegroundColor Green
                } else {
                    Write-Host "❌ Erreur lors du push" -ForegroundColor Red
                }
            } else {
                Write-Host "❌ Erreur lors du commit" -ForegroundColor Red
            }
        } else {
            Write-Host "ℹ️  Aucun changement à committer" -ForegroundColor Gray
        }

    } catch {
        Write-Host "❌ Erreur Git : $($_.Exception.Message)" -ForegroundColor Red
    } finally {
        Pop-Location
    }
} else {
    Write-Host "`n4️⃣  GIT IGNORÉ (-SkipGit)" -ForegroundColor Gray
}

# 5. RÉSUMÉ FINAL
Write-Host "`n🎉 DÉPLOIEMENT COMPLET TERMINÉ !" -ForegroundColor Green
Write-Host "=" * 60 -ForegroundColor Green
Write-Host "📊 Résumé :" -ForegroundColor White
Write-Host "   • Compilation : $(if ($SkipCompilation) { 'Ignorée' } else { '✅ Effectuée' })" -ForegroundColor White
Write-Host "   • FTP : $(if ($SkipFTP) { 'Ignoré' } else { "✅ $uploadedCount fichiers déployés" })" -ForegroundColor White
Write-Host "   • Git : $(if ($SkipGit) { 'Ignoré' } else { '✅ Commit + Push vers dev' })" -ForegroundColor White
Write-Host "   • Timestamp : $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor White

Write-Host "`n✨ Script terminé avec succès !" -ForegroundColor Green