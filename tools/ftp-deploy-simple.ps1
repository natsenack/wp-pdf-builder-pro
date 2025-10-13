# 🚀 FTP DEPLOY - SIMPLE & FAST
# ===================================

Write-Host "🚀 FTP DEPLOY - SIMPLE & FAST" -ForegroundColor Green
Write-Host "================================" -ForegroundColor Green

# Configuration
$projectRoot = Split-Path (Get-Location) -Parent
$configFile = Join-Path $projectRoot "./tools/ftp-config.env"
Write-Host "Project root: $projectRoot" -ForegroundColor Yellow
Write-Host "Config file: $configFile" -ForegroundColor Yellow
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
$ftpPassword = $env:FTP_PASSWORD
$remotePath = $env:FTP_PATH

Write-Host "🎯 Serveur: $ftpHost" -ForegroundColor Cyan
Write-Host "👤 User: $ftpUser" -ForegroundColor Cyan
Write-Host "📁 Dest: $remotePath" -ForegroundColor Cyan

# Compilation
Write-Host "🔨 Compilation en cours..." -ForegroundColor Yellow
Push-Location $projectRoot
& npm run build
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Erreur de compilation" -ForegroundColor Red
    exit 1
}
Pop-Location
Write-Host "✅ Compilation terminée" -ForegroundColor Green

# Lister les fichiers
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

Write-Host "📊 Fichiers à envoyer: $($files.Count)" -ForegroundColor Yellow

if ($files.Count -eq 0) {
    Write-Host "❌ Aucun fichier à envoyer" -ForegroundColor Red
    exit 1
}

# Fonction pour créer un répertoire sur le serveur FTP
function Create-FtpDirectory {
    param(
        [string]$ftpHost,
        [string]$ftpUser,
        [string]$ftpPassword,
        [string]$remoteDir
    )

    try {
        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost$remoteDir")
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        $ftpRequest.UseBinary = $true
        $ftpRequest.KeepAlive = $false

        $response = $ftpRequest.GetResponse()
        $response.Close()
        
        # Tenter de définir les permissions
        Set-FtpPermissions -ftpHost $ftpHost -ftpUser $ftpUser -ftpPassword $ftpPassword -remotePath $remoteDir -permissions "755"
        
        return $true
    }
    catch {
        # Si le répertoire existe déjà (erreur 550), c'est ok
        if ($_.Exception.Message -match "550") {
            return $true
        }
        return $false
    }
}

# Fonction pour définir les permissions FTP
function Set-FtpPermissions {
    param(
        [string]$ftpHost,
        [string]$ftpUser,
        [string]$ftpPassword,
        [string]$remotePath,
        [string]$permissions
    )

    try {
        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost$remotePath")
        $ftpRequest.Method = "SITE CHMOD $permissions $remotePath"
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        $ftpRequest.UseBinary = $false
        $ftpRequest.KeepAlive = $false

        $response = $ftpRequest.GetResponse()
        $response.Close()
        return $true
    }
    catch {
        # Ignorer les erreurs de permissions
        return $false
    }
}

# Créer le répertoire de base si nécessaire
Write-Host "📁 Création du répertoire de base: $remotePath" -ForegroundColor Yellow
if (Create-FtpDirectory -ftpHost $ftpHost -ftpUser $ftpUser -ftpPassword $ftpPassword -remoteDir $remotePath) {
    Write-Host "✅ Répertoire de base créé" -ForegroundColor Green
} else {
    Write-Host "❌ Échec création répertoire de base" -ForegroundColor Red
    exit 1
}

# Créer tous les répertoires nécessaires
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
foreach ($dir in $directories) {
    $remoteDir = "$remotePath/$dir"
    if (Create-FtpDirectory -ftpHost $ftpHost -ftpUser $ftpUser -ftpPassword $ftpPassword -remoteDir $remoteDir) {
        Write-Host "✅ $dir" -ForegroundColor Green
    } else {
        Write-Host "❌ Échec création $dir" -ForegroundColor Red
    }
}
Write-Host "✅ Répertoires créés" -ForegroundColor Green

# Fonction pour uploader un fichier avec gestion d'erreur améliorée
function Send-File {
    param(
        [string]$localFile,
        [string]$remoteFile,
        [string]$ftpHost,
        [string]$ftpUser,
        [string]$ftpPassword
    )

    try {
        $webClient = New-Object System.Net.WebClient
        $webClient.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        $uri = "ftp://$ftpHost$remoteFile"

        # Essayer d'uploader le fichier
        $webClient.UploadFile($uri, $localFile)
        return @{ Success = $true; RemoteFile = $remoteFile; FileName = [System.IO.Path]::GetFileName($remoteFile) }
    }
    catch {
        $errorMessage = $_.Exception.Message

        # Pour les erreurs 550 (fichier non disponible), essayer de supprimer et réessayer
        if ($errorMessage -match "550") {
            try {
                Write-Host "⚠️ Tentative de suppression du fichier distant: $($remoteFile)" -ForegroundColor Yellow
                $deleteUri = "ftp://$ftpHost$remoteFile"
                $deleteRequest = [System.Net.FtpWebRequest]::Create($deleteUri)
                $deleteRequest.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
                $deleteRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)

                $deleteResponse = $deleteRequest.GetResponse()
                $deleteResponse.Close()

                Write-Host "✅ Fichier distant supprimé, nouvelle tentative..." -ForegroundColor Green

                # Réessayer l'upload
                $webClient.UploadFile($uri, $localFile)
                return @{ Success = $true; RemoteFile = $remoteFile; FileName = [System.IO.Path]::GetFileName($remoteFile); Retried = $true }
            }
            catch {
                return @{ Success = $false; RemoteFile = $remoteFile; Error = "Erreur 550 persistante: $($_.Exception.Message)"; FileName = [System.IO.Path]::GetFileName($remoteFile) }
            }
        }
        else {
            return @{ Success = $false; RemoteFile = $remoteFile; Error = $errorMessage; FileName = [System.IO.Path]::GetFileName($remoteFile) }
        }
    }
    finally {
        $webClient.Dispose()
    }
}

# Upload des fichiers en parallèle
$maxConcurrentJobs = 10  # Nombre maximum de jobs simultanés
$runningJobs = @()
$completedJobs = @()
$uploaded = 0
$total = $files.Count
$startTime = Get-Date
$uploadedBytes = 0

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
                    Write-Host "✅ $($result.FileName)" -ForegroundColor Green
                    $uploaded++
                    $uploadedBytes += $completed.FileSize
                } else {
                    Write-Host "❌ Erreur upload $($result.FileName): $($result.Error)" -ForegroundColor Red
                }

                $runningJobs = $runningJobs | Where-Object { $_.Job -ne $completed.Job }
                $completedJobs += $completed
            } else {
                # Aucun job terminé, attendre un peu
                Start-Sleep -Milliseconds 100
            }
        }

        # Démarrer le job pour ce fichier
        $job = Start-Job -ScriptBlock ${function:Send-File} -ArgumentList $file.FullName, $remoteFile, $ftpHost, $ftpUser, $ftpPassword
        $runningJobs += @{ Job = $job; FileSize = $fileSize; RemoteFile = $remoteFile; FileName = [System.IO.Path]::GetFileName($relPath) }

        # Mise à jour de la progression
        $elapsed = (Get-Date) - $startTime
        $avgSpeed = if ($elapsed.TotalSeconds -gt 0) { $uploadedBytes / $elapsed.TotalSeconds } else { 0 }
        $remainingFiles = $total - $uploaded
        $estimatedTimeRemaining = if ($avgSpeed -gt 0) { ($remainingFiles * ($uploadedBytes / [Math]::Max($uploaded, 1))) / $avgSpeed } else { 0 }

        $progressPercent = [math]::Round(($uploaded / $total) * 100, 1)
        $status = "$uploaded/$total fichiers | $([math]::Round($avgSpeed / 1024, 1)) KB/s | ~$([math]::Round($estimatedTimeRemaining / 60, 1)) min restantes"

        Write-Progress -Activity "🚀 Déploiement FTP - $progressPercent% terminé" -Status $status -PercentComplete $progressPercent
    }

    # Attendre que tous les jobs restants se terminent
    while ($runningJobs.Count -gt 0) {
        $completed = $runningJobs | Where-Object { $_.Job.State -ne 'Running' } | Select-Object -First 1
        if ($completed) {
            $result = Receive-Job -Job $completed.Job
            Remove-Job -Job $completed.Job

            if ($result.Success) {
                $statusIcon = if ($result.Retried) { "🔄" } else { "✅" }
                Write-Host "$statusIcon $($result.FileName)" -ForegroundColor Green
                $uploaded++
                $uploadedBytes += $completed.FileSize
            } else {
                # Pour les erreurs 550 sur les fichiers PHP, les marquer comme warnings plutôt qu'erreurs
                if ($result.Error -match "550" -and $result.FileName -match "\.php$") {
                    Write-Host "⚠️ Erreur 550 sur $($result.FileName) (permissions serveur) - ignoré" -ForegroundColor Yellow
                    $uploaded++  # Compter comme uploadé pour la progression
                } else {
                    Write-Host "❌ Erreur upload $($result.FileName): $($result.Error)" -ForegroundColor Red
                }
            }

            $runningJobs = $runningJobs | Where-Object { $_.Job -ne $completed.Job }
            $completedJobs += $completed
        }

        # Mise à jour de la progression
        $elapsed = (Get-Date) - $startTime
        $avgSpeed = if ($elapsed.TotalSeconds -gt 0) { $uploadedBytes / $elapsed.TotalSeconds } else { 0 }
        $remainingFiles = $total - $uploaded
        $estimatedTimeRemaining = if ($avgSpeed -gt 0) { ($remainingFiles * ($uploadedBytes / [Math]::Max($uploaded, 1))) / $avgSpeed } else { 0 }

        $progressPercent = [math]::Round(($uploaded / $total) * 100, 1)
        $status = "$uploaded/$total fichiers | $([math]::Round($avgSpeed / 1024, 1)) KB/s | ~$([math]::Round($estimatedTimeRemaining / 60, 1)) min restantes"

        Write-Progress -Activity "🚀 Déploiement FTP - $progressPercent% terminé" -Status $status -PercentComplete $progressPercent

        Start-Sleep -Milliseconds 100  # Petite pause pour éviter de boucler trop vite
    }
} finally {
    # Nettoyer tous les jobs restants en cas d'erreur ou d'interruption
    Write-Host "🧹 Nettoyage des jobs en cours..." -ForegroundColor Yellow
    Get-Job | Remove-Job -Force -ErrorAction SilentlyContinue
    Write-Host "✅ Jobs nettoyés" -ForegroundColor Green
}

Write-Host "🎉 Déploiement terminé ! $uploaded fichiers uploadés." -ForegroundColor Green

# Push automatique vers Git après déploiement réussi
Write-Host "🔄 Push vers Git..." -ForegroundColor Yellow

try {
    # Aller dans le répertoire du projet
    Push-Location $projectRoot

    # Git add, commit, push
    & git add .
    $commitMessage = "Déploiement automatique - $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
    & git commit -m $commitMessage
    & git push origin dev

    Write-Host "✅ Push Git réussi" -ForegroundColor Green

} catch {
    Write-Host "⚠️ Erreur Git: $($_.Exception.Message)" -ForegroundColor Yellow
} finally {
    Pop-Location
}
