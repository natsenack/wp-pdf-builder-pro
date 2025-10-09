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
$files = Get-ChildItem -Path $projectRoot -Recurse -File | Where-Object {
    $relPath = $_.FullName.Substring($projectRoot.Length + 1).Replace('\', '/')
    -not ($relPath -match '^(archive|\.git|\.vscode|node_modules|src|tools|docs|build-tools|dev-tools|vendor|dist|package\.json|package-lock\.json|webpack\.config\.js|tsconfig\.json)/')
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

# Fonction pour uploader un fichier
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
        $webClient.UploadFile($uri, $localFile)
        return @{ Success = $true; RemoteFile = $remoteFile; FileName = [System.IO.Path]::GetFileName($remoteFile) }
    }
    catch {
        return @{ Success = $false; RemoteFile = $remoteFile; Error = $_.Exception.Message; FileName = [System.IO.Path]::GetFileName($remoteFile) }
    }
    finally {
        $webClient.Dispose()
    }
}

# Upload des fichiers en parallèle
$maxConcurrentJobs = 5  # Nombre maximum de jobs simultanés
$runningJobs = @()
$completedJobs = @()
$uploaded = 0
$total = $files.Count
$startTime = Get-Date
$uploadedBytes = 0

Write-Host "📤 Upload en parallèle ($maxConcurrentJobs jobs max)..." -ForegroundColor Yellow

foreach ($file in $files) {
    $relPath = $file.FullName.Substring($projectRoot.Length + 1).Replace('\', '/')
    $remoteFile = "$remotePath/$relPath"
    $fileSize = $file.Length

    # Démarrer un nouveau job si on n'a pas atteint la limite
    if ($runningJobs.Count -lt $maxConcurrentJobs) {
        $job = Start-Job -ScriptBlock ${function:Send-File} -ArgumentList $file.FullName, $remoteFile, $ftpHost, $ftpUser, $ftpPassword
        $runningJobs += @{ Job = $job; FileSize = $fileSize; RemoteFile = $remoteFile; FileName = [System.IO.Path]::GetFileName($relPath) }
    } else {
        # Attendre qu'un job se termine
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
        }

        # Redémarrer le job pour le fichier actuel
        $job = Start-Job -ScriptBlock ${function:Send-File} -ArgumentList $file.FullName, $remoteFile, $ftpHost, $ftpUser, $ftpPassword
        $runningJobs += @{ Job = $job; FileSize = $fileSize; RemoteFile = $remoteFile; FileName = [System.IO.Path]::GetFileName($relPath) }
    }

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
            Write-Host "✅ $($result.FileName)" -ForegroundColor Green
            $uploaded++
            $uploadedBytes += $completed.FileSize
        } else {
            Write-Host "❌ Erreur upload $($result.FileName): $($result.Error)" -ForegroundColor Red
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
