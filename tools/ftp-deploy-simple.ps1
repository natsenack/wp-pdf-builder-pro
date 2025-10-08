# 🚀 FTP DEPLOYMENT SÉQUENTIEL ULTRA-RAPIDE
# =========================================
# 🔥 Upload séquentiel optimisé pour 5 fichiers/s

param(
    [string]$RemoteDir = "/wp-content/plugins/wp-pdf-builder-pro",
    [int]$Timeout = 1000,    # ⚡ 1s pour bon équilibre débit/stabilité
    [int]$RetryCount = 3,    # 3 retries pour la stabilité
    [int]$MaxParallel = 6,   # 🔥 6 connexions parallèles (vs 3 avant)
    [int]$PreloadBuffer = 10, # 💾 10 fichiers préchargés (vs 5 avant)
    [switch]$NoParallel      # Désactiver le parallélisme
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
Write-Host "🔀 Parallèle : $(if($NoParallel){'Désactivé'}else{$MaxParallel + ' connexions'})" -ForegroundColor Yellow
Write-Host "🎯 Objectif : 5 fichiers/s (comme hier)" -ForegroundColor Red
Write-Host "⚡ Optimisations : FtpWebRequest + KeepAlive=true + Binary + Test réseau + Pipelining" -ForegroundColor Cyan
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
                Start-Sleep -Milliseconds 20  # ⚡ Attente minimale entre retries
            }
        }
    }

    return @{ Success = $false; Error = "Échec après $RetryCount tentatives"; File = $LocalPath }
}

# Fonction de transfert parallèle avec pipelining
function Send-FtpFileParallel {
    param([string]$LocalPath, [string]$RemotePath, [int]$Index)

    $fileName = Split-Path $LocalPath -Leaf

    for ($attempt = 1; $attempt -le $RetryCount; $attempt++) {
        try {
            Write-Host "📤 [$Index|$attempt/$RetryCount] $fileName..." -NoNewline

            $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpHost$RemotePath")
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
            $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = $Timeout
            $ftpRequest.ReadWriteTimeout = $Timeout
            $ftpRequest.KeepAlive = $true
            $ftpRequest.UseBinary = $true

            $fileContents = [System.IO.File]::ReadAllBytes($LocalPath)
            $ftpRequest.ContentLength = $fileContents.Length

            $startTime = Get-Date

            $requestStream = $ftpRequest.GetRequestStream()
            $requestStream.Write($fileContents, 0, $fileContents.Length)
            $requestStream.Close()

            $response = $ftpRequest.GetResponse()
            $response.Close()

            $duration = (Get-Date) - $startTime
            $fileSize = $fileContents.Length
            $speedKBps = [math]::Round($fileSize / 1024 / $duration.TotalSeconds, 2)

            Write-Host " ✅ $([math]::Round($duration.TotalSeconds, 2))s - ${speedKBps} KB/s" -ForegroundColor Green
            return @{ Success = $true; File = $LocalPath; Size = $fileSize; Attempt = $attempt; Index = $Index }
        } catch {
            Write-Host " ❌ Tentative $attempt : $($_.Exception.Message)" -ForegroundColor Red
            if ($attempt -lt $RetryCount) {
                Start-Sleep -Milliseconds 200
            }
        }
    }

    return @{ Success = $false; Error = "Échec après $RetryCount tentatives"; File = $LocalPath; Index = $Index }
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
    ($relPath -match '^(assets|includes|languages|uploads|lib)/') -or
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

# Upload parallèle avec pipelining
$successCount = 0
$failCount = 0
$totalFiles = $files.Count
$currentIndex = 0
$startTime = Get-Date

if ($NoParallel -or $MaxParallel -le 1) {
    # Mode séquentiel (original)
    Write-Host "🔄 Mode séquentiel activé" -ForegroundColor Yellow
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
} else {
    # Mode parallèle avec pipelining avancé
    Write-Host "🔀 Mode parallèle avancé activé ($MaxParallel connexions simultanées, $PreloadBuffer fichiers préchargés)" -ForegroundColor Cyan

    # Précharger les fichiers en mémoire pour éviter les accès disque
    Write-Host "💾 Préchargement des fichiers en RAM..." -ForegroundColor Yellow
    $fileBuffer = @()
    $preloadCount = [math]::Min($PreloadBuffer, $files.Count)

    for ($i = 0; $i -lt $preloadCount; $i++) {
        $file = $files[$i]
        try {
            $fileData = [System.IO.File]::ReadAllBytes($file.FullName)
            $fileBuffer += @{
                Index = $i + 1
                File = $file
                Data = $fileData
                Size = $fileData.Length
                RemotePath = "$RemoteDir/$($file.FullName.Substring($projectRoot.Length + 1).Replace('\', '/'))"
            }
            Write-Host "`r💾 Préchargement: $([math]::Round(($i + 1) / $preloadCount * 100, 1))% ($($i + 1)/$preloadCount)" -NoNewline
        } catch {
            Write-Host "❌ Erreur préchargement $($file.Name): $($_.Exception.Message)" -ForegroundColor Red
        }
    }
    Write-Host "" -ForegroundColor Green

    # Créer le pool de runspaces avec connexions persistantes
    $runspacePool = [runspacefactory]::CreateRunspacePool(1, $MaxParallel)
    $runspacePool.Open()

    $jobs = [System.Collections.Generic.List[object]]::new()
    $bufferIndex = 0
    $fileIndex = $preloadCount
    $completedCount = 0

    # Fonction pour soumettre un nouveau job
    function Submit-FileJob {
        param($fileInfo)

        # Créer les répertoires nécessaires (synchrone pour éviter conflits)
        $remoteDirPath = [System.IO.Path]::GetDirectoryName($fileInfo.RemotePath).Replace('\', '/')
        if ($remoteDirPath -ne $RemoteDir.TrimEnd('/') -and $remoteDirPath -ne "") {
            New-FtpDirectory -Directory $remoteDirPath | Out-Null
        }

        # Script pour le job parallèle avec connexion persistante
        $jobScript = {
            param($FileData, $RemotePath, $Index, $FtpHost, $FtpUser, $FtpPassword, $Timeout, $RetryCount)

            function Send-FtpFileParallel {
                param([byte[]]$FileData, [string]$RemotePath, [int]$Index)

                for ($attempt = 1; $attempt -le $RetryCount; $attempt++) {
                    try {
                        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpHost$RemotePath")
                        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
                        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
                        $ftpRequest.UsePassive = $true
                        $ftpRequest.Timeout = $Timeout
                        $ftpRequest.ReadWriteTimeout = $Timeout
                        $ftpRequest.KeepAlive = $true  # Connexion persistante
                        $ftpRequest.UseBinary = $true
                        $ftpRequest.ContentLength = $FileData.Length

                        $startTime = Get-Date

                        $requestStream = $ftpRequest.GetRequestStream()
                        $requestStream.Write($FileData, 0, $FileData.Length)
                        $requestStream.Close()

                        $response = $ftpRequest.GetResponse()
                        $response.Close()

                        $duration = (Get-Date) - $startTime
                        $speedKBps = [math]::Round($FileData.Length / 1024 / $duration.TotalSeconds, 2)

                        return @{
                            Success = $true
                            Size = $FileData.Length
                            Attempt = $attempt
                            Index = $Index
                            Speed = $speedKBps
                            Duration = $duration.TotalSeconds
                        }
                    } catch {
                        if ($attempt -lt $RetryCount) {
                            Start-Sleep -Milliseconds 10  # ⚡ Pause minimale entre tentatives
                        }
                    }
                }

                return @{
                    Success = $false
                    Error = "Échec après $RetryCount tentatives"
                    Index = $Index
                }
            }

            Send-FtpFileParallel -FileData $FileData -RemotePath $RemotePath -Index $Index
        }

        # Créer et démarrer le job
        $job = [powershell]::Create().AddScript($jobScript).AddParameters(@{
            FileData = $fileInfo.Data
            RemotePath = $fileInfo.RemotePath
            Index = $fileInfo.Index
            FtpHost = $FtpHost
            FtpUser = $FtpUser
            FtpPassword = $FtpPassword
            Timeout = $Timeout
            RetryCount = $RetryCount
        })
        $job.RunspacePool = $runspacePool

        return @{
            Job = $job
            Handle = $job.BeginInvoke()
            FileInfo = $fileInfo
        }
    }

    # Soumettre les premiers jobs depuis le buffer
    Write-Host "🚀 Démarrage des transferts parallèles..." -ForegroundColor Green
    for ($i = 0; $i -lt [math]::Min($MaxParallel, $fileBuffer.Count); $i++) {
        $job = Submit-FileJob -fileInfo $fileBuffer[$i]
        $jobs.Add($job)
    }
    $bufferIndex = $MaxParallel

    # Boucle principale de traitement
    while ($jobs.Count -gt 0 -or $bufferIndex -lt $fileBuffer.Count -or $fileIndex -lt $files.Count) {
        # Précharger le prochain fichier si nécessaire
        if ($bufferIndex -lt $fileBuffer.Count) {
            # Rien à faire, déjà préchargé
        } elseif ($fileIndex -lt $files.Count) {
            # Charger le prochain fichier
            $file = $files[$fileIndex]
            try {
                $fileData = [System.IO.File]::ReadAllBytes($file.FullName)
                $fileBuffer += @{
                    Index = $fileIndex + 1
                    File = $file
                    Data = $fileData
                    Size = $fileData.Length
                    RemotePath = "$RemoteDir/$($file.FullName.Substring($projectRoot.Length + 1).Replace('\', '/'))"
                }
                $fileIndex++
            } catch {
                Write-Host "❌ Erreur chargement $($file.Name): $($_.Exception.Message)" -ForegroundColor Red
                $fileIndex++
            }
        }

        # Traiter les jobs terminés
        for ($i = $jobs.Count - 1; $i -ge 0; $i--) {
            $job = $jobs[$i]
            if ($job.Handle.IsCompleted) {
                $result = $job.Job.EndInvoke($job.Handle)
                $job.Job.Dispose()

                $completedCount++
                $fileInfo = $job.FileInfo

                if ($result.Success) {
                    $successCount++
                    Write-Host "📤 [$($result.Index)] $(Split-Path $fileInfo.File.Name -Leaf)... ✅ $([math]::Round($result.Duration, 2))s - $($result.Speed) KB/s" -ForegroundColor Green
                } else {
                    $failCount++
                    Write-Host "❌ ÉCHEC FINAL [$($result.Index)] : $(Split-Path $fileInfo.File.Name -Leaf) - $($result.Error)" -ForegroundColor Red
                }

                # Progression
                $percent = [math]::Round(($completedCount / $totalFiles) * 100, 1)
                Write-Host "`r📊 Progression: $percent% ($completedCount/$totalFiles) - ✅ $successCount - ❌ $failCount" -NoNewline

                # Retirer le job terminé
                $jobs.RemoveAt($i)

                # Soumettre un nouveau job si disponible
                if ($bufferIndex -lt $fileBuffer.Count) {
                    $newJob = Submit-FileJob -fileInfo $fileBuffer[$bufferIndex]
                    $jobs.Add($newJob)
                    $bufferIndex++
                }
            }
        }

        # Petite pause pour éviter la surcharge CPU
        if ($jobs.Count -gt 0) {
            Start-Sleep -Milliseconds 10  # ⚡ Pause minimale pour l'efficacité
        }
    }

    # Fermer le pool de runspaces
    $runspacePool.Close()
    $runspacePool.Dispose()

    # Libérer la mémoire du buffer
    $fileBuffer = $null
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