# Script de déploiement FTP simplifié
# Version épurée pour déploiement propre

param(
    [string]$RemoteDir = "/wp-content/plugins/wp-pdf-builder-pro",
    [int]$MaxConcurrent = 50,  # Augmenté pour plus de parallélisation
    [int]$ChunkSize = 1048576  # 1MB chunks pour les gros fichiers
)

Write-Host "🚀 DÉPLOIEMENT FTP ULTRA-PARALLÈLE" -ForegroundColor Green
Write-Host "=================================" -ForegroundColor Green

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
Write-Host "🔥 Connexions simultanées : $MaxConcurrent (ULTRA-PARALLÈLE)" -ForegroundColor Red
Write-Host "📦 Taille des chunks : $([math]::Round($ChunkSize/1MB, 1))MB" -ForegroundColor Yellow

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

# Fonction upload ultra-optimisée avec chunks
$uploadScript = {
    param($localFile, $remoteFile, $ftpHost, $ftpUser, $ftpPassword, $chunkSize)

    try {
        $fileInfo = Get-Item $localFile
        $fileSize = $fileInfo.Length

        # Pour les petits fichiers (< 1MB), upload direct
        if ($fileSize -le 1048576) {
            $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost$remoteFile")
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
            $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = 30000
            $ftpRequest.ReadWriteTimeout = 30000
            $ftpRequest.UseBinary = $true
            $ftpRequest.KeepAlive = $false

            $fileContents = [System.IO.File]::ReadAllBytes($localFile)
            $ftpRequest.ContentLength = $fileContents.Length

            $requestStream = $ftpRequest.GetRequestStream()
            $requestStream.Write($fileContents, 0, $fileContents.Length)
            $requestStream.Close()

            $response = $ftpRequest.GetResponse()
            $response.Close()

            return @{ Success = $true; File = $localFile; Size = $fileSize; Method = "Direct" }
        }
        # Pour les gros fichiers, upload par chunks
        else {
            $bytesUploaded = 0
            $buffer = New-Object byte[] $chunkSize
            $fileStream = $null

            try {
                $fileStream = [System.IO.File]::OpenRead($localFile)

                while ($bytesUploaded -lt $fileSize) {
                    $bytesToRead = [Math]::Min($chunkSize, $fileSize - $bytesUploaded)

                    $bytesRead = $fileStream.Read($buffer, 0, $bytesToRead)
                    if ($bytesRead -eq 0) { break }

                    # Créer une requête FTP pour ce chunk
                    $chunkRemotePath = if ($bytesUploaded -eq 0) {
                        $remoteFile  # Premier chunk : créer le fichier
                    } else {
                        "$remoteFile;offset=$bytesUploaded"  # Chunks suivants : append
                    }

                    $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost$chunkRemotePath")
                    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
                    $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
                    $ftpRequest.UsePassive = $true
                    $ftpRequest.Timeout = 30000
                    $ftpRequest.ReadWriteTimeout = 30000
                    $ftpRequest.UseBinary = $true
                    $ftpRequest.KeepAlive = $false

                    $requestStream = $ftpRequest.GetRequestStream()
                    $requestStream.Write($buffer, 0, $bytesRead)
                    $requestStream.Close()

                    $response = $ftpRequest.GetResponse()
                    $response.Close()

                    $bytesUploaded += $bytesRead
                }
            } finally {
                if ($fileStream) {
                    $fileStream.Close()
                    $fileStream.Dispose()
                }
            }

            return @{ Success = $true; File = $localFile; Size = $fileSize; Method = "Chunked" }
        }
    } catch {
        return @{ Success = $false; Error = $_.Exception.Message; File = $localFile }
    }
}

$successCount = 0
$failCount = 0
$totalFiles = $files.Count
$currentIndex = 0
$startTime = Get-Date

# Créer le pool de runspaces pour parallélisation maximale
$runspacePool = [runspacefactory]::CreateRunspacePool(1, $MaxConcurrent)
$runspacePool.Open()

$runspaces = [System.Collections.ArrayList]::new()

Write-Host "📊 Déploiement de $totalFiles fichiers avec $MaxConcurrent connexions simultanées..." -ForegroundColor Cyan

foreach ($file in $files) {
    $currentIndex++
    $relPath = $file.FullName.Substring($projectRoot.Length + 1).Replace('\', '/')
    $remotePath = "$RemoteDir/$relPath"

    # Créer tous les répertoires parents nécessaires
    $remoteDirPath = [System.IO.Path]::GetDirectoryName($remotePath).Replace('\', '/')
    if ($remoteDirPath -ne $RemoteDir.TrimEnd('/') -and $remoteDirPath -ne "") {
        New-FtpDirectory -Directory $remoteDirPath | Out-Null
    }

    # Lancer l'upload en runspace (beaucoup plus rapide que les jobs)
    $powershell = [powershell]::Create().AddScript($uploadScript).AddArgument($file.FullName).AddArgument($remotePath).AddArgument($FtpHost).AddArgument($FtpUser).AddArgument($FtpPassword).AddArgument($ChunkSize)
    $powershell.RunspacePool = $runspacePool

    $runspaceData = @{
        PowerShell = $powershell
        Handle = $powershell.BeginInvoke()
        File = $file.FullName
        Index = $currentIndex
    }
    $runspaces.Add($runspaceData) | Out-Null

    # Afficher progression
    $percent = [math]::Round(($currentIndex / $totalFiles) * 100, 1)
    Write-Host "`r📤 [$percent%] $currentIndex/$totalFiles fichiers - Runspaces actifs: $($runspaces.Count)" -NoNewline

    # Attendre si on atteint la limite de runspaces simultanés
    while ($runspaces.Count -ge $MaxConcurrent) {
        $completedRunspaces = $runspaces | Where-Object { $_.Handle.IsCompleted }

        if ($completedRunspaces) {
            foreach ($rs in $completedRunspaces) {
                $result = $rs.PowerShell.EndInvoke($rs.Handle)
                $rs.PowerShell.Dispose()

                if ($result.Success) {
                    $successCount++
                    $method = if ($result.Method) { " ($($result.Method))" } else { "" }
                    Write-Host "`r✅ Upload réussi: $(Split-Path $result.File -Leaf)$method" -ForegroundColor Green
                } else {
                    $failCount++
                    Write-Host "`r❌ Échec: $(Split-Path $result.File -Leaf) - $($result.Error)" -ForegroundColor Red
                }

                $runspaces.Remove($rs)
            }
        } else {
            Start-Sleep -Milliseconds 10  # Très courte pause pour éviter la surcharge CPU
        }
    }
}

Write-Host ""

# Attendre que tous les runspaces se terminent
Write-Host "🔄 Finalisation des derniers transferts..." -ForegroundColor Yellow

while ($runspaces.Count -gt 0) {
    $completedRunspaces = $runspaces | Where-Object { $_.Handle.IsCompleted }

    if ($completedRunspaces) {
        foreach ($rs in $completedRunspaces) {
            $result = $rs.PowerShell.EndInvoke($rs.Handle)
            $rs.PowerShell.Dispose()

            if ($result.Success) {
                $successCount++
                $method = if ($result.Method) { " ($($result.Method))" } else { "" }
                Write-Host "✅ Upload réussi: $(Split-Path $result.File -Leaf)$method" -ForegroundColor Green
            } else {
                $failCount++
                Write-Host "❌ Échec: $(Split-Path $result.File -Leaf) - $($result.Error)" -ForegroundColor Red
            }

            $runspaces.Remove($rs)
        }
    }

    if ($runspaces.Count -gt 0) {
        Start-Sleep -Milliseconds 10
    }
}

# Nettoyer le pool de runspaces
$runspacePool.Close()
$runspacePool.Dispose()

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