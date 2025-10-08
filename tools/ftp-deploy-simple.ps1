# Script de déploiement FTP simplifié
# Version épurée pour déploiement propre

param(
    [string]$RemoteDir = "/wp-content/plugins/wp-pdf-builder-pro",
    [int]$MaxConcurrent = 20
)

Write-Host "🚀 DÉPLOIEMENT FTP SIMPLIFIÉ" -ForegroundColor Green
Write-Host "===========================" -ForegroundColor Green

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
Write-Host "🔥 Connexions simultanées : $MaxConcurrent (ULTRA-RAPIDE)" -ForegroundColor Yellow

# Fonction pour créer un répertoire FTP (optimisée)
function New-FtpDirectory {
    param([string]$Directory)

    # Créer récursivement tous les répertoires parents
    $parts = $Directory -split '/' | Where-Object { $_ -ne '' }
    $currentPath = ""

    foreach ($part in $parts) {
        $currentPath += "/$part"
        try {
            $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpHost$currentPath")
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = 5000  # Timeout réduit

            $response = $ftpRequest.GetResponse()
            $response.Close()
        } catch {
            # Ignorer les erreurs (répertoire existe déjà)
        }
    }
}

# Fonction upload simple
function Send-FtpFile {
    param([string]$LocalPath, [string]$RemotePath)

    try {
        $ftpUri = "ftp://$FtpHost$RemotePath"
        $webClient = New-Object System.Net.WebClient
        $webClient.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
        $webClient.UploadFile($ftpUri, $LocalPath)
        return $true
    } catch {
        Write-Host "❌ Échec : $LocalPath → $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

# Lister les fichiers de production
$projectRoot = Split-Path (Get-Location) -Parent
$files = Get-ChildItem -Path $projectRoot -Recurse -File | Where-Object {
    $relPath = $_.FullName.Substring($projectRoot.Length + 1).Replace('\', '/')

    # EXCLURE les dossiers et fichiers de développement
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

    # INCLURE seulement les fichiers essentiels du plugin
    ($relPath -match '^(assets|includes|languages|uploads)/') -or
    ($relPath -eq '.htaccess') -or
    ($relPath -eq 'bootstrap.php') -or
    ($relPath -eq 'pdf-builder-pro.php') -or
    ($relPath -eq 'README.md')
}

Write-Host "📊 Fichiers à déployer : $($files.Count)" -ForegroundColor Yellow

# Fonction d'upload pour les jobs (ULTRA-optimisée)
$uploadScript = {
    param($localFile, $remoteFile, $ftpHost, $ftpUser, $ftpPassword)

    try {
        # Utiliser FtpWebRequest pour de meilleures performances
        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost$remoteFile")
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 10000  # Réduit pour rapidité
        $ftpRequest.ReadWriteTimeout = 10000
        $ftpRequest.UseBinary = $true
        $ftpRequest.KeepAlive = $true  # Pas de connexion persistante pour parallélisme

        # Lecture optimisée du fichier
        $fileContents = [System.IO.File]::ReadAllBytes($localFile)
        $ftpRequest.ContentLength = $fileContents.Length

        # Stream optimisé
        $requestStream = $ftpRequest.GetRequestStream()
        $requestStream.Write($fileContents, 0, $fileContents.Length)
        $requestStream.Close()

        $response = $ftpRequest.GetResponse()
        $response.Close()

        return @{ Success = $true; File = $localFile; Size = $fileContents.Length }
    } catch {
        return @{ Success = $false; Error = $_.Exception.Message; File = $localFile }
    }
}

$successCount = 0
$failCount = 0
$runningJobs = [System.Collections.ArrayList]::new()
$totalFiles = $files.Count
$currentIndex = 0
$startTime = Get-Date

foreach ($file in $files) {
    $currentIndex++
    $relPath = $file.FullName.Substring($projectRoot.Length + 1).Replace('\', '/')
    $remotePath = "$RemoteDir/$relPath"

    # Créer tous les répertoires parents nécessaires
    $remoteDirPath = [System.IO.Path]::GetDirectoryName($remotePath).Replace('\', '/')
    if ($remoteDirPath -ne $RemoteDir.TrimEnd('/') -and $remoteDirPath -ne "") {
        New-FtpDirectory -Directory $remoteDirPath | Out-Null
    }

    # Lancer l'upload en job
    $job = Start-Job -ScriptBlock $uploadScript -ArgumentList $file.FullName, $remotePath, $FtpHost, $FtpUser, $FtpPassword
    $runningJobs.Add($job) | Out-Null

    $percent = [math]::Round(($currentIndex / $totalFiles) * 100, 1)
    Write-Host "`r📤 [$percent%] $currentIndex/$totalFiles fichiers" -ForegroundColor Gray -NoNewline

    # Attendre si on atteint la limite de jobs simultanés
    while ($runningJobs.Count -ge $MaxConcurrent) {
        $completedJobs = $runningJobs | Where-Object { $_.State -ne 'Running' }
        if ($completedJobs) {
            foreach ($job in $completedJobs) {
                $result = Receive-Job -Job $job -ErrorAction SilentlyContinue
                if ($result.Success) {
                    $successCount++
                } else {
                    $failCount++
                }
                Remove-Job -Job $job -ErrorAction SilentlyContinue | Out-Null
                $runningJobs.Remove($job) | Out-Null
            }
        }

        if ($runningJobs.Count -ge $MaxConcurrent) {
            Start-Sleep -Milliseconds 25  # Réduit pour rapidité
        }
    }
}

# Attendre que tous les jobs se terminent
while ($runningJobs.Count -gt 0) {
    $completedJobs = $runningJobs | Where-Object { $_.State -ne 'Running' }
    if ($completedJobs) {
        foreach ($job in $completedJobs) {
            $result = Receive-Job -Job $job -ErrorAction SilentlyContinue
            if ($result.Success) {
                $successCount++
            } else {
                $failCount++
            }
            Remove-Job -Job $job -ErrorAction SilentlyContinue | Out-Null
            $runningJobs.Remove($job) | Out-Null
        }
    }

    if ($runningJobs.Count -gt 0) {
        Start-Sleep -Milliseconds 25
    }
}

Write-Host ""
Write-Host "✅ TERMINÉ" -ForegroundColor Green
Write-Host "==========" -ForegroundColor Green

$endTime = Get-Date
$duration = $endTime - $startTime
$totalSeconds = $duration.TotalSeconds
$filesPerSecond = [math]::Round($successCount / $totalSeconds, 1)

Write-Host "📊 Réussis : $successCount" -ForegroundColor Green
Write-Host "❌ Échecs : $failCount" -ForegroundColor Red
Write-Host "⏱️ Durée : $([math]::Round($totalSeconds, 1))s" -ForegroundColor Cyan
Write-Host "🚀 Vitesse : $filesPerSecond fichiers/s" -ForegroundColor Cyan
Write-Host ""

# Git commit et push automatique après déploiement réussi
if ($failCount -eq 0 -and $successCount -gt 0) {
    Write-Host "🔄 VERSIONNAGE AUTOMATIQUE" -ForegroundColor Magenta
    Write-Host "=========================" -ForegroundColor Magenta

    try {
        # Aller à la racine du projet
        Push-Location $projectRoot

        # Vérifier l'état Git
        $gitStatus = & git status --porcelain
        if ($gitStatus) {
            Write-Host "📝 Fichiers modifiés détectés, création du commit..." -ForegroundColor Yellow

            # Ajouter tous les fichiers modifiés
            & git add .

            # Créer un message de commit détaillé
            $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
            $commitMessage = @"
deploy: déploiement FTP réussi vers $FtpHost

- Déploiement automatique via script ftp-deploy-simple.ps1
- $successCount fichiers déployés avec succès
- Durée du déploiement: $([math]::Round($totalSeconds, 1))s
- Vitesse: $filesPerSecond fichiers/s
- Date: $timestamp

Type: deploy (déploiement)
Impact: Production mise à jour
Environnement: $FtpHost$RemoteDir
"@

            # Commit
            $commitResult = & git commit -m $commitMessage 2>&1
            if ($LASTEXITCODE -eq 0) {
                Write-Host "✅ Commit créé avec succès" -ForegroundColor Green

                # Push
                $pushResult = & git push origin main 2>&1
                if ($LASTEXITCODE -eq 0) {
                    Write-Host "✅ Push vers GitHub réussi" -ForegroundColor Green
                    Write-Host "🔗 Dépôt: https://github.com/natsenack/wp-pdf-builder-pro.git" -ForegroundColor Cyan
                } else {
                    Write-Host "❌ Échec du push Git:" -ForegroundColor Red
                    Write-Host $pushResult -ForegroundColor Red
                }
            } else {
                Write-Host "❌ Échec du commit Git:" -ForegroundColor Red
                Write-Host $commitResult -ForegroundColor Red
            }
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