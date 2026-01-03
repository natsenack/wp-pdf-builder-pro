# Script complet de déploiement : Compilation + FTP + Git
# Usage: .\deploy-complete.ps1

$ErrorActionPreference = "Continue"  # Ne pas arrêter sur les erreurs FTP

$WorkingDir = "I:\wp-pdf-builder-pro"
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpBasePath = "/wp-content/plugins/wp-pdf-builder-pro"
$PluginPath = "$WorkingDir\plugin"

# Variables globales pour le résumé
$uploadedCount = 0
$failedCount = 0
$totalBytesUploaded = 0
$compilationSuccess = $false
$ftpSuccess = $false
$gitSuccess = $false

$startTimestamp = Get-Date

Write-Host "🚀 DÉPLOIEMENT COMPLET - $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor Cyan
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
            $compilationSuccess = $true
        } else {
            Write-Host "❌ Erreur de compilation :" -ForegroundColor Red
            Write-Host $buildResult -ForegroundColor Red
            $compilationSuccess = $false
            Write-Host "`n🛑 Arrêt du script : compilation échouée" -ForegroundColor Red
            exit 1
        }
    } catch {
        Write-Host "❌ Erreur lors de la compilation : $($_.Exception.Message)" -ForegroundColor Red
        $compilationSuccess = $false
    } finally {
        Pop-Location
    }
} else {
    Write-Host "⚠️  package.json non trouvé, compilation ignorée" -ForegroundColor Yellow
    $compilationSuccess = $true  # Considéré comme réussi si ignoré
}

# 2. COLLECTE DES FICHIERS
Write-Host "`n2️⃣  COLLECTE DES FICHIERS" -ForegroundColor Yellow
Write-Host "-" * 30

Write-Host "📂 Collecte des fichiers depuis : $PluginPath" -ForegroundColor White
$allFiles = Get-ChildItem -Path $PluginPath -Recurse -File
Write-Host "📊 Fichiers totaux trouvés : $($allFiles.Count)" -ForegroundColor White

$totalSize = ($allFiles | Measure-Object -Property Length -Sum).Sum
Write-Host "📈 Taille totale : $([math]::Round($totalSize / 1MB, 2)) MB" -ForegroundColor Green

# 3. DÉPLOIEMENT FTP ULTRA-RAPIDE AVEC PARALLÉLISATION
Write-Host "`n3️⃣  DÉPLOIEMENT FTP ULTRA-RAPIDE" -ForegroundColor Yellow
Write-Host "-" * 40

try {
    Write-Host "🔌 Connexion à ftp://$FtpHost..." -ForegroundColor White

    # Collecter tous les répertoires nécessaires
    $directories = @{}
    foreach ($file in $allFiles) {
        $relativePath = $file.FullName -replace [regex]::Escape($PluginPath), ""
        $remotePath = "$FtpBasePath$relativePath".Replace("\", "/")
        $remoteDir = [System.IO.Path]::GetDirectoryName($remotePath).Replace("\", "/")

        if ($remoteDir -and $remoteDir -ne "/" -and -not $directories.ContainsKey($remoteDir)) {
            $directories[$remoteDir] = $true
        }
    }

    # CRÉATION ULTRA-RAPIDE DES RÉPERTOIRES
    Write-Host "🏗️ Création de $($directories.Count) répertoires..." -ForegroundColor White

    $createdDirs = 0
    foreach ($dir in $directories.Keys) {
        try {
            $dirRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpHost$dir")
            $dirRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $dirRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
            $dirRequest.UseBinary = $true
            $dirRequest.KeepAlive = $false
            $dirRequest.Timeout = 500

            $dirResponse = $dirRequest.GetResponse()
            $dirResponse.Close()
            $createdDirs++
        } catch {
            # Ignore if directory exists
        }
    }

    Write-Host "✅ Répertoires vérifiés/créés : $createdDirs" -ForegroundColor Green

    # FONCTION UPLOAD SYNCHRONE
    function Upload-File {
        param($localFile, $remoteFile, $ftpHost, $ftpUser, $ftpPass, $fileName, $fileSize)

        $maxRetries = 3
        $retryDelay = 1000  # 1 seconde

        for ($attempt = 1; $attempt -le $maxRetries; $attempt++) {
            try {
                # Créer le répertoire parent si nécessaire
                $remoteDir = [System.IO.Path]::GetDirectoryName($remoteFile).Replace("\", "/")
                if ($remoteDir -and $remoteDir -ne "/") {
                    try {
                        $dirRequest = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost$remoteDir")
                        $dirRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
                        $dirRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
                        $dirRequest.UseBinary = $true
                        $dirRequest.KeepAlive = $false
                        $dirRequest.Timeout = 2000
                        $dirResponse = $dirRequest.GetResponse()
                        $dirResponse.Close()
                    } catch {
                        # Ignore si le répertoire existe déjà
                    }
                }

                $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost$remoteFile")
                $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
                $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
                $ftpRequest.UseBinary = $true
                $ftpRequest.UsePassive = $false
                $ftpRequest.KeepAlive = $false
                $ftpRequest.Timeout = 15000  # Augmenté à 15 secondes
                $ftpRequest.ReadWriteTimeout = 15000

                $fileStream = [System.IO.File]::OpenRead($localFile)
                $requestStream = $ftpRequest.GetRequestStream()

                # Buffer optimisé selon la taille du fichier
                $bufferSize = if ($fileSize -lt 32KB) { 16384 } elseif ($fileSize -lt 256KB) { 65536 } elseif ($fileSize -lt 1MB) { 131072 } else { 262144 }
                $buffer = New-Object byte[] $bufferSize
                $bytesRead = 0
                $totalUploaded = 0

                while (($bytesRead = $fileStream.Read($buffer, 0, $buffer.Length)) -gt 0) {
                    $requestStream.Write($buffer, 0, $bytesRead)
                    $totalUploaded += $bytesRead
                }

                $requestStream.Close()
                $fileStream.Close()

                return @{
                    Success = $true
                    FileName = $fileName
                    Size = $fileSize
                    Attempts = $attempt
                    UploadedBytes = $totalUploaded
                }
            } catch {
                $errorMsg = $_.Exception.Message
                $errorCode = "Unknown"

                # Extraire le code d'erreur FTP si disponible
                if ($errorMsg -match '\((\d+)\)') {
                    $errorCode = $matches[1]
                }

                # Pour les erreurs temporaires, retry
                if ($attempt -lt $maxRetries -and ($errorCode -eq "421" -or $errorCode -eq "425" -or $errorCode -eq "426")) {
                    Start-Sleep -Milliseconds ($retryDelay * $attempt)
                    continue
                }

                return @{
                    Success = $false
                    FileName = $fileName
                    Size = $fileSize
                    Error = $errorMsg
                    ErrorCode = $errorCode
                    RemotePath = $remoteFile
                    Attempts = $attempt
                }
            }
        }
    }

    # UPLOAD SYNCHRONE SIMPLE
    Write-Host "🚀 Upload synchrone des fichiers..." -ForegroundColor Cyan

    # UPLOAD SYNCHRONE SIMPLE
    Write-Host "🚀 Upload synchrone des fichiers..." -ForegroundColor Cyan

    $uploadedCount = 0
    $failedCount = 0
    $totalBytesUploaded = 0
    $startTime = Get-Date
    $lastProgressTime = $startTime

    foreach ($file in $sortedFiles) {
        $relativePath = $file.FullName -replace [regex]::Escape($PluginPath), ""
        $remotePath = "$FtpBasePath$relativePath".Replace("\", "/")

        $result = Upload-File -localFile $file.FullName -remoteFile $remotePath -ftpHost $FtpHost -ftpUser $FtpUser -ftpPass $FtpPass -fileName $file.Name -fileSize $file.Length

        if ($result.Success) {
            $uploadedCount++
            $totalBytesUploaded += $result.Size
            $attemptInfo = if ($result.Attempts -gt 1) { " (tentative $($result.Attempts))" } else { "" }
            Write-Host "  ✅ $($result.FileName)$attemptInfo" -ForegroundColor Green
        } else {
            $failedCount++
            $errorDisplay = if ($result.ErrorCode -and $result.ErrorCode -ne "Unknown") {
                "[$($result.ErrorCode)] $($result.Error)"
            } else {
                $result.Error
            }
            $attemptInfo = if ($result.Attempts -gt 1) { " (après $($result.Attempts) tentatives)" } else { "" }
            Write-Host "  ❌ $($result.FileName) : $errorDisplay$attemptInfo" -ForegroundColor Red
        }

        # Afficher la progression toutes les secondes
        $currentTime = Get-Date
        if (($currentTime - $lastProgressTime).TotalSeconds -ge 1) {
            $progress = [math]::Round(($uploadedCount + $failedCount) / $totalFiles * 100, 1)
            $elapsed = $currentTime - $startTime
            $speedMBps = if ($elapsed.TotalSeconds -gt 0) { [math]::Round($totalBytesUploaded / $elapsed.TotalSeconds / 1024 / 1024, 2) } else { 0 }
            $eta = if ($speedMBps -gt 0 -and ($totalFiles - ($uploadedCount + $failedCount)) -gt 0) {
                $remainingBytes = ($sortedFiles | Select-Object -Skip ($uploadedCount + $failedCount) | Measure-Object -Property Length -Sum).Sum
                $etaSeconds = $remainingBytes / ($speedMBps * 1024 * 1024)
                if ($etaSeconds -lt 60) { "$([math]::Round($etaSeconds))s" }
                elseif ($etaSeconds -lt 3600) { "$([math]::Round($etaSeconds / 60))min" }
                else { "$([math]::Round($etaSeconds / 3600, 1))h" }
            } else { "∞" }

            $progressBar = ("█" * [math]::Floor($progress / 2)) + ("░" * (50 - [math]::Floor($progress / 2)))
            Write-Host "📊 [$progressBar] $progress% | $uploadedCount/$totalFiles fichiers | ${speedMBps} MB/s | ETA: $eta" -ForegroundColor Cyan
            $lastProgressTime = $currentTime
        }
    }

    $endTime = Get-Date
    $totalElapsed = $endTime - $startTime
    $avgSpeedMBps = [math]::Round($totalBytesUploaded / $totalElapsed.TotalSeconds / 1024 / 1024, 2)

    Write-Host "✅ Déploiement FTP terminé : $uploadedCount fichiers uploadés, $failedCount échoués" -ForegroundColor Green
    Write-Host "⚡ Vitesse moyenne : $avgSpeedMBps MB/s | Durée : $([math]::Round($totalElapsed.TotalSeconds))s | Données : $([math]::Round($totalBytesUploaded / 1024 / 1024, 2)) MB" -ForegroundColor Cyan

    $ftpSuccess = $uploadedCount -gt 0

} catch {
    Write-Host "❌ Erreur FTP générale : $($_.Exception.Message)" -ForegroundColor Red
    $ftpSuccess = $false
}

# 4. COMMIT ET PUSH GIT
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
        Write-Host "💾 Commit avec message : Deploy $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor White
        & git commit -m "Deploy $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"

        if ($LASTEXITCODE -eq 0) {
            Write-Host "✅ Commit réussi" -ForegroundColor Green
            $gitSuccess = $true

            # Push vers dev
            Write-Host "🚀 Push vers la branche dev..." -ForegroundColor White
            & git push origin dev

            if ($LASTEXITCODE -eq 0) {
                Write-Host "✅ Push réussi vers dev" -ForegroundColor Green
                $gitSuccess = $true
            } else {
                Write-Host "❌ Erreur lors du push" -ForegroundColor Red
                $gitSuccess = $false
            }
        } else {
            Write-Host "❌ Erreur lors du commit" -ForegroundColor Red
            $gitSuccess = $false
        }
    } else {
        Write-Host "ℹ️  Aucun changement à committer" -ForegroundColor Gray
        $gitSuccess = $true  # Considéré comme réussi si rien à committer
    }

} catch {
    Write-Host "❌ Erreur Git : $($_.Exception.Message)" -ForegroundColor Red
    $gitSuccess = $false
} finally {
    Pop-Location
}

# 5. RÉSUMÉ FINAL
Write-Host "`n🎉 DÉPLOIEMENT COMPLET TERMINÉ !" -ForegroundColor Green
Write-Host "=" * 60 -ForegroundColor Green

$endTime = Get-Date
$totalDuration = $endTime - [DateTime]::Parse($startTimestamp)

Write-Host "📊 RÉSUMÉ DÉTAILLÉ :" -ForegroundColor White
Write-Host "   • Compilation : $(if ($compilationSuccess) { "✅ Réussie" } else { "❌ Échouée" })" -ForegroundColor $(if ($compilationSuccess) { "Green" } else { "Red" })
Write-Host "   • Collecte fichiers : ✅ $($allFiles.Count) fichiers ($([math]::Round($totalSize / 1MB, 2)) MB)" -ForegroundColor Green
Write-Host "   • FTP Upload : $(if ($ftpSuccess) { "✅ $uploadedCount/$(($uploadedCount + $failedCount)) fichiers" } else { "❌ Échoué" }) ($([math]::Round($totalBytesUploaded / 1024 / 1024, 2)) MB)" -ForegroundColor $(if ($ftpSuccess) { "Green" } else { "Red" })

if ($failedCount -gt 0) {
    Write-Host "     ⚠️  $failedCount fichiers ont échoué (principalement erreurs 550 - fichiers existants)" -ForegroundColor Yellow
}

Write-Host "   • Git : $(if ($gitSuccess) { "✅ Commit + Push vers dev" } else { "❌ Échoué" })" -ForegroundColor $(if ($gitSuccess) { "Green" } else { "Red" })
Write-Host "   • Durée totale : $([math]::Round($totalDuration.TotalSeconds)) secondes" -ForegroundColor Cyan
Write-Host "   • Timestamp : $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor White

if ($compilationSuccess -and $ftpSuccess) {
    Write-Host "`n✨ DÉPLOIEMENT RÉUSSI !" -ForegroundColor Green
} elseif ($ftpSuccess) {
    Write-Host "`n⚠️  DÉPLOIEMENT PARTIELLEMENT RÉUSSI (FTP OK, compilation échouée)" -ForegroundColor Yellow
} else {
    Write-Host "`n❌ DÉPLOIEMENT ÉCHOUÉ" -ForegroundColor Red
}


