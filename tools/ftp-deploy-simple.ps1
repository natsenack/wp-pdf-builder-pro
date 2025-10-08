# 🚀 FTP DEPLOYMENT SÉQUENTIEL ULTRA-RAPIDE
# =========================================
# 🔥 Upload séquentiel optimisé pour 5 fichiers/s

param(
    [string]$RemoteDir = "/wp-content/plugins/wp-pdf-builder-pro",
    [int]$Timeout = 8000,    # 8 secondes pour vitesse
    [int]$RetryCount = 3     # 3 retries rapides
)

Write-Host "🐌 DÉPLOIEMENT FTP SÉQUENTIEL ULTRA-RAPIDE" -ForegroundColor Green
Write-Host "=========================================" -ForegroundColor Green

# Configuration
$configFile = ".\ftp-config.env"
if (-not (Test-Path $configFile)) {
    Write-Host "❌ Config manquante : $configFile" -ForegroundColor Red
    exit 1
}

# Charger config
Get-Content $configFile | Where-Object { $_ -match '^FTP_' } | ForEach-Object {
    $key, $value = $_ -split '=', 2
    [Environment]::SetEnvironmentVariable($key.Trim(), $value.Trim())
}

$FtpHost = $env:FTP_HOST
$FtpUser = $env:FTP_USER
$FtpPassword = $env:FTP_PASSWORD

if (-not $FtpHost -or -not $FtpUser -or -not $FtpPassword) {
    Write-Host "❌ Config FTP incomplète" -ForegroundColor Red
    exit 1
}

Write-Host "🎯 Serveur : $FtpHost" -ForegroundColor Cyan
Write-Host "📁 Destination : $RemoteDir" -ForegroundColor Cyan
Write-Host "⏱️ Timeout : ${Timeout}ms" -ForegroundColor Yellow
Write-Host "🔄 Retries : $RetryCount" -ForegroundColor Yellow
Write-Host "🎯 Objectif : 5 fichiers/s (comme hier)" -ForegroundColor Red
Write-Host ""

# Fonction pour créer un répertoire FTP
function New-FtpDirectory {
    param([string]$Directory)

    $parts = $Directory -split '/' | Where-Object { $_ -ne '' }
    $currentPath = ""

    foreach ($part in $parts) {
        $currentPath += "/$part"
        try {
            $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpHost$currentPath")
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = $Timeout

            $response = $ftpRequest.GetResponse()
            $response.Close()
        } catch {
            # Ignore if directory exists
        }
    }
}

# Fonction upload séquentiel ultra-rapide
function Send-FtpFile {
    param([string]$LocalPath, [string]$RemotePath)

    $fileName = Split-Path $LocalPath -Leaf

    for ($attempt = 1; $attempt -le $RetryCount; $attempt++) {
        try {
            Write-Host "📤 [$attempt/$RetryCount] $fileName..." -NoNewline

            $webClient = New-Object System.Net.WebClient
            $webClient.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
            $webClient.Proxy = $null
            $webClient.Encoding = [System.Text.Encoding]::UTF8
            $webClient.Headers.Add("User-Agent", "Mozilla/5.0")

            $ftpUri = "ftp://$FtpHost$RemotePath"
            $startTime = Get-Date

            $webClient.UploadFile($ftpUri, $LocalPath)

            $duration = (Get-Date) - $startTime
            $fileSize = (Get-Item $LocalPath).Length
            $speedKBps = [math]::Round($fileSize / 1024 / $duration.TotalSeconds, 1)

            $webClient.Dispose()

            Write-Host " ✅ $([math]::Round($duration.TotalSeconds, 2))s - ${speedKBps} KB/s" -ForegroundColor Green
            return @{ Success = $true; File = $LocalPath; Size = $fileSize; Attempt = $attempt }
        } catch {
            Write-Host " ❌ Tentative $attempt : $($_.Exception.Message)" -ForegroundColor Red
            if ($attempt -lt $RetryCount) {
                Start-Sleep -Milliseconds 200  # Attente courte entre retries
            }
        }
    }

    return @{ Success = $false; Error = "Échec après $RetryCount tentatives"; File = $LocalPath }
}

# Lister les fichiers
$projectRoot = Split-Path (Get-Location) -Parent
$files = Get-ChildItem -Path $projectRoot -Recurse -File | Where-Object {
    $relPath = $_.FullName.Substring($projectRoot.Length + 1).Replace('\', '/')

    # EXCLURE les dossiers de développement (selon README.md)
    -not ($relPath -match '^(\.git|\.vscode|node_modules|src|tools|docs|build-tools|dev-tools|vendor|archive|dist)/') -and
    -not ($relPath -match '\.(log|tmp|bak|md~)$') -and
    -not ($relPath -match '^composer\.(json|lock)$') -and
    -not ($relPath -match '^package\.json$') -and
    -not ($relPath -match '^tsconfig\.json$') -and
    -not ($relPath -match '\.ts$') -and
    -not ($relPath -match '\.tsx$') -and
    -not ($relPath -match '\.map$')
} | Where-Object {
    $relPath = $_.FullName.Substring($projectRoot.Length + 1).Replace('\', '/')

    # INCLURE seulement les fichiers de PRODUCTION selon README.md
    ($relPath -match '^(assets|includes|languages|uploads)/') -or
    ($relPath -eq '.htaccess') -or
    ($relPath -eq 'bootstrap.php') -or
    ($relPath -eq 'pdf-builder-pro.php') -or
    ($relPath -eq 'README.md') -or
    ($relPath -eq 'settings-page.php') -or
    ($relPath -eq 'template-editor.php') -or
    ($relPath -eq 'woocommerce-elements.css')
}

Write-Host "📊 Fichiers à déployer : $($files.Count)" -ForegroundColor Yellow
Write-Host ""

# Upload séquentiel rapide (comme hier)
$successCount = 0
$failCount = 0
$totalFiles = $files.Count
$currentIndex = 0
$startTime = Get-Date

foreach ($file in $files) {
    $currentIndex++
    $relPath = $file.FullName.Substring($projectRoot.Length + 1).Replace('\', '/')
    $remotePath = "$RemoteDir/$relPath"

    # Créer les répertoires nécessaires
    $remoteDirPath = [System.IO.Path]::GetDirectoryName($remotePath).Replace('\', '/')
    if ($remoteDirPath -ne $RemoteDir.TrimEnd('/') -and $remoteDirPath -ne "") {
        New-FtpDirectory -Directory $remoteDirPath | Out-Null
    }

    # Upload avec retry rapide
    $result = Send-FtpFile -LocalPath $file.FullName -RemotePath $remotePath

    if ($result.Success) {
        $successCount++
    } else {
        $failCount++
        Write-Host "❌ ÉCHEC FINAL : $(Split-Path $result.File -Leaf) - $($result.Error)" -ForegroundColor Red
    }

    # Progression
    $percent = [math]::Round(($currentIndex / $totalFiles) * 100, 1)
    Write-Host "`r📊 Progression: $percent% ($currentIndex/$totalFiles) - ✅ $successCount - ❌ $failCount" -NoNewline
}

Write-Host ""
Write-Host ""

# Résultats
Write-Host "✅ TERMINÉ" -ForegroundColor Green
Write-Host "==========" -ForegroundColor Green

$endTime = Get-Date
$duration = $endTime - $startTime
$totalSeconds = $duration.TotalSeconds
$filesPerSecond = [math]::Round($successCount / $totalSeconds, 2)

Write-Host "📊 Réussis : $successCount" -ForegroundColor Green
Write-Host "❌ Échecs : $failCount" -ForegroundColor Red
Write-Host "⏱️ Durée : $([math]::Round($totalSeconds, 1))s" -ForegroundColor Cyan
Write-Host "🚀 Vitesse : $filesPerSecond fichiers/s" -ForegroundColor Cyan

if ($filesPerSecond -ge 4.5) {
    Write-Host "🎯 OBJECTIF ATTEINT : $filesPerSecond fichiers/s (comme hier !)" -ForegroundColor Green
} elseif ($filesPerSecond -ge 3) {
    Write-Host "⚠️ PRESQUE : $filesPerSecond fichiers/s (proche de l'objectif)" -ForegroundColor Yellow
} else {
    Write-Host "❌ TROP LENT : $filesPerSecond fichiers/s (revoir les paramètres)" -ForegroundColor Red
}

Write-Host ""

# Git commit automatique
if ($failCount -eq 0 -and $successCount -gt 0) {
    Write-Host "🔄 VERSIONNAGE AUTOMATIQUE" -ForegroundColor Magenta
    Write-Host "=========================" -ForegroundColor Magenta

    try {
        Push-Location $projectRoot

        $gitStatus = & git status --porcelain
        if ($gitStatus) {
            & git add .

            $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
            $commitMessage = @"
deploy: déploiement FTP séquentiel rapide vers $FtpHost

- Déploiement automatique via script ftp-deploy-simple.ps1
- Mode séquentiel optimisé pour vitesse (comme hier)
- $successCount fichiers déployés avec succès
- Durée du déploiement: $([math]::Round($totalSeconds, 1))s
- Vitesse: $filesPerSecond fichiers/s
- Timeout: ${Timeout}ms, Retries: $RetryCount
- Date: $timestamp

Type: deploy (séquentiel rapide)
Impact: Production mise à jour
Environnement: $FtpHost$RemoteDir
"@

            & git commit -m $commitMessage 2>&1 | Out-Null
            & git push origin main 2>&1 | Out-Null

            Write-Host "✅ Commit et push automatiques réussis" -ForegroundColor Green
        } else {
            Write-Host "ℹ️ Aucun changement détecté dans Git" -ForegroundColor Cyan
        }

        Pop-Location
    } catch {
        Write-Host "❌ Erreur lors du versionnage Git:" -ForegroundColor Red
        Write-Host $_.Exception.Message -ForegroundColor Red
    }

    Write-Host ""
}

Write-Host "⚠️ Videz le cache WordPress après déploiement" -ForegroundColor Yellow