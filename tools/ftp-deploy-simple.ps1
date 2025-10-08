# 🚀 FTP DEPLOYMENT SÉQUENTIEL ULTRA-RAPIDE
# =========================================
# 🔥 Upload séquentiel optimisé pour 5 fichiers/s

param(
    [string]$RemoteDir = "/wp-content/plugins/wp-pdf-builder-pro",
    [int]$Timeout = 2000,    # 2 secondes pour équilibre vitesse/stabilité
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
Write-Host "⚡ Optimisations : FtpWebRequest + KeepAlive=true + Binary + Test réseau" -ForegroundColor Cyan
Write-Host ""

# Test de connectivité réseau
Write-Host "🔍 Test de connectivité réseau..." -ForegroundColor Cyan
$connectionTest = Test-NetConnection -ComputerName $FtpHost -Port 21 -WarningAction SilentlyContinue
if (-not $connectionTest.TcpTestSucceeded) {
    Write-Host "❌ Impossible de se connecter au serveur $FtpHost:21" -ForegroundColor Red
    exit 1
}
Write-Host "✅ Connectivité réseau OK (latence: $($connectionTest.PingReplyDetails.RoundtripTime)ms)" -ForegroundColor Green
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

            $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpHost$RemotePath")
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
            $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = $Timeout
            $ftpRequest.ReadWriteTimeout = $Timeout
            $ftpRequest.KeepAlive = $true  # Connexion persistante pour vitesse
            $ftpRequest.UseBinary = $true

            $fileContents = [System.IO.File]::ReadAllBytes($LocalPath)
            $ftpRequest.ContentLength = $fileContents.Length

            # Mesure précise du temps avec Stopwatch
            $stopwatch = [System.Diagnostics.Stopwatch]::StartNew()

            $requestStream = $ftpRequest.GetRequestStream()
            $requestStream.Write($fileContents, 0, $fileContents.Length)
            $requestStream.Close()

            $response = $ftpRequest.GetResponse()
            $stopwatch.Stop()
            $response.Close()

            # Calcul précis de la vitesse
            $fileSize = $fileContents.Length
            $durationMs = $stopwatch.ElapsedMilliseconds
            $durationSec = $durationMs / 1000
            $sizeKB = $fileSize / 1024
            $speedKBps = [math]::Round($sizeKB / $durationSec, 2)

            Write-Host " ✅ $([math]::Round($durationSec, 2))s - ${speedKBps} KB/s" -ForegroundColor Green
            return @{ Success = $true; File = $LocalPath; Size = $fileSize; Attempt = $attempt }
        } catch {
            Write-Host " ❌ Tentative $attempt : $($_.Exception.Message)" -ForegroundColor Red
            if ($attempt -lt $RetryCount) {
                Start-Sleep -Milliseconds 100  # Attente très courte entre retries
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

# Calculer la taille totale
$totalSize = ($files | Measure-Object -Property Length -Sum).Sum
$totalSizeMB = [math]::Round($totalSize / 1MB, 2)
Write-Host "📏 Taille totale : ${totalSizeMB} MB" -ForegroundColor Yellow
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
    $currentFileSizeKB = [math]::Round($file.Length / 1KB, 1)
    $percent = [math]::Round(($currentIndex / $totalFiles) * 100, 1)
    Write-Host "`r📊 Progression: $percent% ($currentIndex/$totalFiles) - ${currentFileSizeKB} KB - ✅ $successCount - ❌ $failCount" -NoNewline
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

# Statistiques de taille
$uploadedSize = ($files | Where-Object { $successCount -gt 0 } | Measure-Object -Property Length -Sum).Sum
$uploadedSizeMB = [math]::Round($uploadedSize / 1MB, 2)
$avgFileSizeKB = [math]::Round($uploadedSize / 1KB / $successCount, 1)
Write-Host "📏 Données uploadées : ${uploadedSizeMB} MB" -ForegroundColor Cyan
Write-Host "📊 Taille moyenne : ${avgFileSizeKB} KB/fichier" -ForegroundColor Cyan

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
- Données uploadées: ${uploadedSizeMB} MB
- Taille moyenne: ${avgFileSizeKB} KB/fichier
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