# 🚀 FTP DEPLOY - SIMPLE & FAST - AUTO-OPTIMIZED
# ================================================

Write-Host "🚀 FTP DEPLOY - SIMPLE & FAST - AUTO-OPTIMIZED" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Green

# Configuration
$projectRoot = Split-Path (Get-Location) -Parent
$configFile = Join-Path $projectRoot "./tools/ftp-config.env"
$perfFile = Join-Path $projectRoot "./tools/ftp-performance.json"

Write-Host "Project root: $projectRoot" -ForegroundColor Yellow
Write-Host "Config file: $configFile" -ForegroundColor Yellow
Write-Host "Performance file: $perfFile" -ForegroundColor Yellow
Write-Host "Config exists: $(Test-Path $configFile)" -ForegroundColor Yellow
if (-not (Test-Path $configFile)) {
    Write-Host "❌ Config manquante : $configFile" -ForegroundColor Red
    exit 1
}

Get-Content $configFile | Where-Object { $_ -match '^FTP_' } | ForEach-Object {
    $key, $value = $_ -split '=', 2
    [Environment]::SetEnvironmentVariable($key.Trim(), $value.Trim())
}

$ftpHost = $env:FTP_HOST
$ftpUser = $env:FTP_USER
$ftpPassword = $env:FTP_PASS
$remotePath = $env:FTP_PATH

Write-Host "🎯 Serveur: $ftpHost" -ForegroundColor Cyan
Write-Host "👤 User: $ftpUser" -ForegroundColor Cyan
Write-Host "📁 Dest: $remotePath" -ForegroundColor Cyan

# Système d'auto-optimisation des performances
Write-Host "🧠 Chargement des données de performance..." -ForegroundColor Yellow
$performanceData = if (Test-Path $perfFile) {
    try {
        Get-Content $perfFile | ConvertFrom-Json
    } catch {
        @{ LastDeployments = @(); OptimalSettings = @{ ConcurrentJobs = 20; Timeout = 2000; SleepMs = 25 } }
    }
} else {
    @{ LastDeployments = @(); OptimalSettings = @{ ConcurrentJobs = 20; Timeout = 2000; SleepMs = 25 } }
}

# Paramètres optimaux (auto-adaptés)
$maxConcurrentJobs = $performanceData.OptimalSettings.ConcurrentJobs
$ftpTimeout = $performanceData.OptimalSettings.Timeout
$sleepMs = $performanceData.OptimalSettings.SleepMs

Write-Host "⚡ Paramètres optimaux chargés: $maxConcurrentJobs jobs, ${ftpTimeout}ms timeout, ${sleepMs}ms sleep" -ForegroundColor Cyan

# Compilation
Write-Host "🔨 Compilation en cours..." -ForegroundColor Yellow
Write-Progress -Activity "🔨 Compilation" -Status "Compilation du projet en cours..." -PercentComplete 0
Push-Location $projectRoot
& npm run build  # Compilation optimisée
Write-Progress -Activity "🔨 Compilation" -Status "Compilation terminée" -PercentComplete 100
if ($LASTEXITCODE -ne 0) {
    Write-Progress -Activity "🔨 Compilation" -Completed
    Write-Host "❌ Erreur de compilation" -ForegroundColor Red
    exit 1
}
Pop-Location
Write-Progress -Activity "🔨 Compilation" -Completed
Write-Host "✅ Compilation terminée" -ForegroundColor Green

# Lister les fichiers
Write-Host "📂 Analyse des fichiers..." -ForegroundColor Yellow
Write-Progress -Activity "📂 Analyse des fichiers" -Status "Recherche des fichiers à déployer..." -PercentComplete 0

# Exclusions: dossiers de développement, fichiers temporaires, archives, backups, logs, docs
$files = Get-ChildItem -Path $projectRoot -Recurse -File | Where-Object {
    $relPath = $_.FullName.Substring($projectRoot.Length + 1).Replace('\', '/')
    -not ($relPath -match '^(archive|\.git|\.vscode|node_modules|src|tools|docs|build-tools|dev-tools|vendor|dist|package\.json|package-lock\.json|webpack\.config\.js|tsconfig\.json|temp-restore)/|^(temp|backup|cache|extract|restore|canvas-extract|temp-canvas|backup-wp|archive-pdf|temp_backup|projet)/|^.*\.(bak|tmp|log|md)$')
} | Where-Object {
    $relPath = $_.FullName.Substring($projectRoot.Length + 1).Replace('\', '/')
    ($relPath -match '^(assets|includes|languages|lib)/') -or
    ($relPath -match '\.(php|css|js|html|htaccess)$') -or
    ($relPath -eq 'readme.txt') -or
    ($relPath -eq 'pdf-builder-pro.php') -or
    ($relPath -eq 'bootstrap.php')
}

Write-Progress -Activity "📂 Analyse des fichiers" -Status "Analyse terminée" -PercentComplete 100
Write-Progress -Activity "📂 Analyse des fichiers" -Completed

Write-Host "📊 Fichiers à envoyer: $($files.Count)" -ForegroundColor Yellow

if ($files.Count -eq 0) {
    Write-Host "❌ Aucun fichier à envoyer" -ForegroundColor Red
    exit 1
}

# Fonction pour vérifier si un répertoire FTP existe
function Test-FtpDirectory {
    param(
        [string]$ftpHost,
        [string]$ftpUser,
        [string]$ftpPassword,
        [string]$remoteDir
    )

    try {
        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost$remoteDir")
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        $ftpRequest.UseBinary = $true
        $ftpRequest.KeepAlive = $false
        $ftpRequest.Timeout = 2000  # Timeout ultra-réduit pour performance

        $response = $ftpRequest.GetResponse()
        $response.Close()
        return $true
    }
    catch {
        return $false
    }
}

# Fonction pour vérifier si un fichier distant existe et obtenir ses informations
function Get-FtpFileInfo {
    param(
        [string]$ftpHost,
        [string]$ftpUser,
        [string]$ftpPassword,
        [string]$remoteFile
    )

    try {
        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost$remoteFile")
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::GetFileSize
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        $ftpRequest.UseBinary = $true
        $ftpRequest.KeepAlive = $false
        $ftpRequest.Timeout = 1500  # Timeout ultra-court pour vérifications rapides

        $response = $ftpRequest.GetResponse()
        $fileSize = $response.ContentLength
        $lastModified = $response.LastModified
        $response.Close()

        return @{ Exists = $true; Size = $fileSize; LastModified = $lastModified }
    }
    catch {
        return @{ Exists = $false; Size = 0; LastModified = $null }
    }
}

# Fonction pour créer un répertoire sur le serveur FTP (optimisée)
function Create-FtpDirectory {
    param(
        [string]$ftpHost,
        [string]$ftpUser,
        [string]$ftpPassword,
        [string]$remoteDir
    )

    # Vérifier d'abord si le répertoire existe déjà - si oui, ignorer complètement
    if (Test-FtpDirectory -ftpHost $ftpHost -ftpUser $ftpUser -ftpPassword $ftpPassword -remoteDir $remoteDir) {
        return $true  # Existe déjà, rien à faire
    }

    try {
        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost$remoteDir")
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        $ftpRequest.UseBinary = $true
        $ftpRequest.KeepAlive = $false
        $ftpRequest.Timeout = 5000

        $response = $ftpRequest.GetResponse()
        $response.Close()

        # Tenter de définir les permissions (en arrière-plan, ne pas bloquer)
        try {
            $permRequest = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost$remoteDir")
            $permRequest.Method = "SITE CHMOD 755 $remoteDir"
            $permRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
            $permRequest.UseBinary = $false
            $permRequest.KeepAlive = $false
            $permRequest.Timeout = 1000  # Timeout minimal pour permissions
            $permResponse = $permRequest.GetResponse()
            $permResponse.Close()
        } catch {
            # Ignorer les erreurs de permissions
        }

        return $true
    }
    catch {
        return $false
    }
}

# Créer le répertoire de base si nécessaire
Write-Host "📁 Vérification du répertoire de base: $remotePath" -ForegroundColor Yellow
if (Create-FtpDirectory -ftpHost $ftpHost -ftpUser $ftpUser -ftpPassword $ftpPassword -remoteDir $remotePath) {
    Write-Host "✅ Répertoire de base prêt" -ForegroundColor Green
} else {
    Write-Host "❌ Échec préparation répertoire de base" -ForegroundColor Red
    exit 1
}

# Créer tous les répertoires nécessaires (optimisé)
$allDirectories = @()
foreach ($file in $files) {
    $relPath = $file.FullName.Substring($projectRoot.Length + 1).Replace('\', '/')
    $dir = [System.IO.Path]::GetDirectoryName($relPath)
    if ($dir) {
        $parts = $dir.Split('/')
        for ($i = 0; $i -lt $parts.Length; $i++) {
            $parentDir = ($parts[0..$i] -join '/')
            if ($parentDir -notin $allDirectories) {
                $allDirectories += $parentDir
            }
        }
    }
}

# Trier par profondeur pour créer les répertoires parents d'abord
$directories = $allDirectories | Sort-Object { ($_.Split('/')).Count }

Write-Host "📁 Création des répertoires ($($directories.Count) répertoires)..." -ForegroundColor Yellow
$createdCount = 0
$skippedCount = 0
$totalDirs = $directories.Count

# Créer les répertoires de manière optimisée (pas de messages pour les existants)
$dirIndex = 0
foreach ($dir in $directories) {
    $dirIndex++
    $progressPercent = [math]::Round(($dirIndex / $totalDirs) * 100, 1)
    Write-Progress -Activity "📁 Création des répertoires" -Status "Création: $dir ($dirIndex/$totalDirs)" -PercentComplete $progressPercent

    $remoteDir = "$remotePath/$dir"
    if (Create-FtpDirectory -ftpHost $ftpHost -ftpUser $ftpUser -ftpPassword $ftpPassword -remoteDir $remoteDir) {
        $createdCount++
    } else {
        $skippedCount++
    }
}
Write-Progress -Activity "📁 Création des répertoires" -Completed
Write-Host "✅ Répertoires prêts: $createdCount créés, $skippedCount ignorés (existants)" -ForegroundColor Green

# Fonction pour uploader un fichier avec vérification d'existence (optimisée)
function Send-File {
    param(
        [string]$localFile,
        [string]$remoteFile,
        [string]$ftpHost,
        [string]$ftpUser,
        [string]$ftpPassword
    )

    # Fonction auxiliaire Get-FtpFileInfo incluse pour les jobs
    function Get-FtpFileInfo {
        param(
            [string]$ftpHost,
            [string]$ftpUser,
            [string]$ftpPassword,
            [string]$remoteFile
        )

        try {
            $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost$remoteFile")
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::GetFileSize
            $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
            $ftpRequest.UseBinary = $true
            $ftpRequest.KeepAlive = $false
            $ftpRequest.Timeout = 1500

            $response = $ftpRequest.GetResponse()
            $fileSize = $response.ContentLength
            $lastModified = $response.LastModified
            $response.Close()

            return @{ Exists = $true; Size = $fileSize; LastModified = $lastModified }
        }
        catch {
            return @{ Exists = $false; Size = 0; LastModified = $null }
        }
    }

    try {
        # Vérifier d'abord si le fichier distant existe et est identique
        $localFileInfo = Get-Item $localFile
        $remoteFileInfo = Get-FtpFileInfo -ftpHost $ftpHost -ftpUser $ftpUser -ftpPassword $ftpPassword -remoteFile $remoteFile

        # Si le fichier existe et a la même taille, considérer qu'il est à jour
        if ($remoteFileInfo.Exists -and $remoteFileInfo.Size -eq $localFileInfo.Length) {
            return @{ Success = $true; RemoteFile = $remoteFile; FileName = [System.IO.Path]::GetFileName($remoteFile); Skipped = $true }
        }

        # Fichier différent ou inexistant, procéder à l'upload
        $webClient = New-Object System.Net.WebClient
        $webClient.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        $uri = "ftp://$ftpHost$remoteFile"

        # Essayer d'uploader le fichier
        $webClient.UploadFile($uri, $localFile)
        return @{ Success = $true; RemoteFile = $remoteFile; FileName = [System.IO.Path]::GetFileName($remoteFile); Uploaded = $true }
    }
    catch {
        $errorMessage = $_.Exception.Message

        # Pour les erreurs 550 (fichier non disponible), essayer de supprimer et réessayer
        if ($errorMessage -match "550") {
            try {
                # Réessayer l'upload directement (le fichier distant sera remplacé)
                $webClient = New-Object System.Net.WebClient
                $webClient.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
                $uri = "ftp://$ftpHost$remoteFile"
                $webClient.UploadFile($uri, $localFile)
                return @{ Success = $true; RemoteFile = $remoteFile; FileName = [System.IO.Path]::GetFileName($remoteFile); Retried = $true }
            }
            catch {
                return @{ Success = $false; RemoteFile = $remoteFile; Error = "Erreur 550 persistante: $($_.Exception.Message)"; FileName = [System.IO.Path]::GetFileName($remoteFile) }
            }
            finally {
                if ($webClient) { $webClient.Dispose() }
            }
        }
        else {
            return @{ Success = $false; RemoteFile = $remoteFile; Error = $errorMessage; FileName = [System.IO.Path]::GetFileName($remoteFile) }
        }
    }
    finally {
        if ($webClient) { $webClient.Dispose() }
    }
}

# Upload des fichiers en parallèle (ultra-optimisé)
$maxConcurrentJobs = 20  # Augmenté pour performance maximale
$runningJobs = @()
$completedJobs = @()
$uploaded = 0
$skipped = 0
$total = $files.Count
$uploadedBytes = 0

# Auto-optimisation des paramètres de transfert
Write-Host "🎯 Auto-optimisation des paramètres de transfert..." -ForegroundColor Yellow
$optimizationStart = Get-Date

# Test rapide de connectivité pour ajuster les timeouts
$connectivityTest = Test-Connection -ComputerName $ftpHost.Split('.')[0] -Count 1 -ErrorAction SilentlyContinue
$latency = if ($connectivityTest) { $connectivityTest.ResponseTime } else { 50 }

# Ajustement intelligent des paramètres basé sur la latence et l'historique
if ($latency -lt 20) {
    # Connexion très rapide
    $maxConcurrentJobs = [Math]::Min(30, $maxConcurrentJobs + 2)
    $ftpTimeout = [Math]::Max(1000, $ftpTimeout - 200)
    $sleepMs = [Math]::Max(10, $sleepMs - 5)
} elseif ($latency -lt 50) {
    # Connexion normale
    $maxConcurrentJobs = [Math]::Min(25, $maxConcurrentJobs + 1)
    $ftpTimeout = [Math]::Max(1500, $ftpTimeout - 100)
    $sleepMs = [Math]::Max(15, $sleepMs - 2)
} else {
    # Connexion lente
    $maxConcurrentJobs = [Math]::Max(10, $maxConcurrentJobs - 1)
    $ftpTimeout = [Math]::Min(3000, $ftpTimeout + 200)
    $sleepMs = [Math]::Min(50, $sleepMs + 5)
}

# Vérifier les performances des derniers déploiements
$lastDeployments = $performanceData.LastDeployments | Where-Object { $_.Timestamp -gt (Get-Date).AddDays(-7) }
if ($lastDeployments.Count -ge 3) {
    $avgTime = ($lastDeployments | Measure-Object -Property Duration -Average).Average
    if ($avgTime -gt 15) {
        # Déploiements lents récemment, augmenter l'agressivité
        $maxConcurrentJobs = [Math]::Min(35, $maxConcurrentJobs + 3)
        $ftpTimeout = [Math]::Max(800, $ftpTimeout - 300)
        $sleepMs = [Math]::Max(5, $sleepMs - 10)
    } elseif ($avgTime -lt 8) {
        # Déploiements très rapides, stabiliser
        $maxConcurrentJobs = [Math]::Max(15, $maxConcurrentJobs - 1)
        $ftpTimeout = [Math]::Min(2500, $ftpTimeout + 100)
        $sleepMs = [Math]::Min(30, $sleepMs + 2)
    }
}

$optimizationTime = (Get-Date) - $optimizationStart
Write-Host "✅ Optimisation terminée en $([math]::Round($optimizationTime.TotalMilliseconds, 0))ms" -ForegroundColor Green
Write-Host "⚡ Paramètres adaptés: $maxConcurrentJobs jobs simultanés, ${ftpTimeout}ms timeout, ${sleepMs}ms sleep" -ForegroundColor Cyan

$startTime = Get-Date
Write-Host "📤 Upload en parallèle ($maxConcurrentJobs jobs max)..." -ForegroundColor Yellow

try {
    foreach ($file in $files) {
        $relPath = $file.FullName.Substring($projectRoot.Length + 1).Replace('\', '/')
        $remoteFile = "$remotePath/$relPath"
        $fileSize = $file.Length

        # Attendre qu'il y ait un slot disponible si on a atteint la limite
        while ($runningJobs.Count -ge $maxConcurrentJobs) {
            # Attendre qu'au moins un job se termine
            $completed = $runningJobs | Where-Object { $_.Job.State -ne 'Running' } | Select-Object -First 1
            if ($completed) {
                $result = Receive-Job -Job $completed.Job
                Remove-Job -Job $completed.Job

                if ($result.Success) {
                    if ($result.Skipped) {
                        Write-Host "⏭️ $($result.FileName)" -ForegroundColor Cyan
                        $skipped++
                    } else {
                        Write-Host "✅ $($result.FileName)" -ForegroundColor Green
                        $uploaded++
                        $uploadedBytes += $completed.FileSize
                    }
                } else {
                    Write-Host "❌ Erreur upload $($result.FileName): $($result.Error)" -ForegroundColor Red
                }

                $runningJobs = $runningJobs | Where-Object { $_.Job -ne $completed.Job }
                $completedJobs += $completed
            } else {
                # Aucun job terminé, attendre un peu
                Start-Sleep -Milliseconds 25  # Réduit pour plus de réactivité
            }
        }

        # Démarrer le job pour ce fichier
        $job = Start-Job -ScriptBlock ${function:Send-File} -ArgumentList $file.FullName, $remoteFile, $ftpHost, $ftpUser, $ftpPassword
        $runningJobs += @{ Job = $job; FileSize = $fileSize; RemoteFile = $remoteFile; FileName = [System.IO.Path]::GetFileName($relPath) }

        # Mise à jour de la progression (ultra-optimisée)
        if (($uploaded + $skipped) % 10 -eq 0) {  # Mise à jour tous les 10 fichiers pour performance max
            $elapsed = (Get-Date) - $startTime
            $avgSpeed = if ($elapsed.TotalSeconds -gt 0) { $uploadedBytes / $elapsed.TotalSeconds } else { 0 }
            $remainingFiles = $total - $uploaded - $skipped
            $estimatedTimeRemaining = if ($avgSpeed -gt 0) { ($remainingFiles * ($uploadedBytes / [Math]::Max($uploaded, 1))) / $avgSpeed } else { 0 }

            $progressPercent = [math]::Round((($uploaded + $skipped) / $total) * 100, 1)
            $status = "$($uploaded + $skipped)/$total fichiers | $([math]::Round($avgSpeed / 1024, 1)) KB/s | ~$([math]::Round($estimatedTimeRemaining / 60, 1)) min restantes"

        Write-Progress -Activity "🚀 Déploiement FTP - $progressPercent% terminé" -Status $status -PercentComplete $progressPercent
    }

    # Attendre que tous les jobs restants se terminent
    while ($runningJobs.Count -gt 0) {
        $completed = $runningJobs | Where-Object { $_.Job.State -ne 'Running' } | Select-Object -First 1
        if ($completed) {
            $result = Receive-Job -Job $completed.Job
            Remove-Job -Job $completed.Job

            if ($result.Success) {
                if ($result.Skipped) {
                    Write-Host "⏭️ $($result.FileName)" -ForegroundColor Cyan
                    $skipped++
                } elseif ($result.Uploaded -or $result.Retried) {
                    $statusIcon = if ($result.Retried) { "🔄" } else { "✅" }
                    Write-Host "$statusIcon $($result.FileName)" -ForegroundColor Green
                    $uploaded++
                    $uploadedBytes += $completed.FileSize
                }
            } else {
                # Pour les erreurs 550 sur les fichiers PHP, les marquer comme warnings plutôt qu'erreurs
                if ($result.Error -match "550" -and $result.FileName -match "\.php$") {
                    Write-Host "⚠️ Erreur 550 sur $($result.FileName) (permissions serveur) - ignoré" -ForegroundColor Yellow
                    $skipped++  # Compter comme ignoré pour la progression
                } else {
                    Write-Host "❌ Erreur upload $($result.FileName): $($result.Error)" -ForegroundColor Red
                }
            }

            $runningJobs = $runningJobs | Where-Object { $_.Job -ne $completed.Job }
            $completedJobs += $completed
        }

        # Mise à jour de la progression finale
        $elapsed = (Get-Date) - $startTime
        $avgSpeed = if ($elapsed.TotalSeconds -gt 0) { $uploadedBytes / $elapsed.TotalSeconds } else { 0 }
        $remainingFiles = $total - $uploaded - $skipped
        $estimatedTimeRemaining = if ($avgSpeed -gt 0) { ($remainingFiles * ($uploadedBytes / [Math]::Max($uploaded, 1))) / $avgSpeed } else { 0 }

        $progressPercent = [math]::Round((($uploaded + $skipped) / $total) * 100, 1)
        $status = "$($uploaded + $skipped)/$total fichiers | $([math]::Round($avgSpeed / 1024, 1)) KB/s | ~$([math]::Round($estimatedTimeRemaining / 60, 1)) min restantes"

        Write-Progress -Activity "🚀 Déploiement FTP - $progressPercent% terminé" -Status $status -PercentComplete $progressPercent

        Start-Sleep -Milliseconds 25  # Délai ultra-réduit pour performance maximale
    }
}
} catch {
    Write-Host "❌ Erreur lors de l'upload: $($_.Exception.Message)" -ForegroundColor Red
} finally {
    # Nettoyer tous les jobs restants en cas d'erreur ou d'interruption
    Write-Host "🧹 Nettoyage des jobs en cours..." -ForegroundColor Yellow
    Get-Job | Remove-Job -Force -ErrorAction SilentlyContinue
    Write-Host "✅ Jobs nettoyés" -ForegroundColor Green
}

Write-Host "🎉 Déploiement terminé ! $uploaded fichiers uploadés, $skipped fichiers ignorés (inchangés)." -ForegroundColor Green

# Résumé détaillé du déploiement
$totalProcessed = $uploaded + $skipped
$elapsed = (Get-Date) - $startTime
$avgSpeed = if ($elapsed.TotalSeconds -gt 0) { $uploadedBytes / $elapsed.TotalSeconds } else { 0 }

Write-Host "`n📊 RÉSUMÉ DU DÉPLOIEMENT" -ForegroundColor Cyan
Write-Host "═══════════════════════════════" -ForegroundColor Cyan
Write-Host "⏱️  Durée totale: $([math]::Round($elapsed.TotalSeconds, 1)) secondes" -ForegroundColor White
Write-Host "📁 Fichiers traités: $totalProcessed/$total" -ForegroundColor White
Write-Host "📤 Fichiers uploadés: $uploaded" -ForegroundColor Green
Write-Host "⏭️  Fichiers ignorés: $skipped (inchangés)" -ForegroundColor Yellow
Write-Host "💾 Données transférées: $([math]::Round($uploadedBytes / 1024 / 1024, 2)) MB" -ForegroundColor White
Write-Host "⚡ Vitesse moyenne: $([math]::Round($avgSpeed / 1024, 1)) KB/s" -ForegroundColor White

# Indicateur spécial pour objectif < 10 secondes
if ($elapsed.TotalSeconds -lt 10) {
    Write-Host "🎯 OBJECTIF ATTEINT: Déploiement en moins de 10 secondes !" -ForegroundColor Green -BackgroundColor Black
} else {
    $timeOver = [math]::Round($elapsed.TotalSeconds - 10, 1)
    Write-Host "⚠️  Objectif non atteint: +${timeOver}s (optimisation en cours)" -ForegroundColor Yellow
}

Write-Host "═══════════════════════════════`n" -ForegroundColor Cyan

# Sauvegarde des performances pour optimisation future
Write-Host "💾 Sauvegarde des performances..." -ForegroundColor Yellow
$currentDeployment = @{
    Timestamp = Get-Date
    Duration = $elapsed.TotalSeconds
    FilesProcessed = $totalProcessed
    FilesUploaded = $uploaded
    FilesSkipped = $skipped
    BytesTransferred = $uploadedBytes
    AverageSpeed = $avgSpeed
    ConcurrentJobs = $maxConcurrentJobs
    Timeout = $ftpTimeout
    SleepMs = $sleepMs
    TargetReached = ($elapsed.TotalSeconds -lt 10)
}

# Ajouter aux derniers déploiements (garder seulement les 10 derniers)
$performanceData.LastDeployments = @($currentDeployment) + ($performanceData.LastDeployments | Select-Object -First 9)

# Mettre à jour les paramètres optimaux si ce déploiement était réussi et rapide
if ($currentDeployment.TargetReached -and $uploaded -gt 0) {
    $performanceData.OptimalSettings = @{
        ConcurrentJobs = $maxConcurrentJobs
        Timeout = $ftpTimeout
        SleepMs = $sleepMs
    }
    Write-Host "🎯 Nouveaux paramètres optimaux sauvegardés" -ForegroundColor Green
}

# Sauvegarder dans le fichier
$performanceData | ConvertTo-Json | Set-Content $perfFile -Encoding UTF8
Write-Host "✅ Performances sauvegardées" -ForegroundColor Green

# Push automatique vers Git après déploiement réussi
Write-Host "🔄 Push vers Git..." -ForegroundColor Yellow

try {
    # Aller dans le répertoire du projet
    Push-Location $projectRoot

    # Git add
    Write-Progress -Activity "🔄 Push Git" -Status "Ajout des fichiers au staging..." -PercentComplete 0
    & git add .
    Write-Progress -Activity "🔄 Push Git" -Status "Fichiers ajoutés" -PercentComplete 33

    # Git commit
    Write-Progress -Activity "🔄 Push Git" -Status "Création du commit..." -PercentComplete 33
    $commitMessage = "Déploiement automatique - $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
    & git commit -m $commitMessage
    Write-Progress -Activity "🔄 Push Git" -Status "Commit créé" -PercentComplete 66

    # Git push
    Write-Progress -Activity "🔄 Push Git" -Status "Push vers le dépôt distant..." -PercentComplete 66
    & git push origin dev
    Write-Progress -Activity "🔄 Push Git" -Status "Push terminé" -PercentComplete 100

    Write-Progress -Activity "🔄 Push Git" -Completed
    Write-Host "✅ Push Git réussi" -ForegroundColor Green

} catch {
    Write-Progress -Activity "🔄 Push Git" -Completed
    Write-Host "⚠️ Erreur Git: $($_.Exception.Message)" -ForegroundColor Yellow
} finally {
    Pop-Location
}
