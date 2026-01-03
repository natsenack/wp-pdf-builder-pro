# Script de déploiement complet : Compilation + FTP asynchrone + Git
# Envoie TOUS les fichiers du dossier plugin/ vers le serveur distant
# Usage: .\deployall.ps1 [-Mode <plugin|full>] [-SkipConnectionTest] [-FastMode] [-DryRun]

param(
    [ValidateSet("plugin", "full")]
    [string]$Mode = "plugin",
    [switch]$SkipConnectionTest,
    [switch]$FastMode,
    [switch]$DryRun
)

$ErrorActionPreference = "Stop"
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

# Configuration
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"
$WorkingDir = "I:\wp-pdf-builder-pro"
$PluginPath = Join-Path $WorkingDir "plugin"

# Variables globales
$uploadedCount = 0
$failedCount = 0
$totalBytesUploaded = 0
$compilationSuccess = $false
$ftpSuccess = $false
$gitSuccess = $false
$startTime = Get-Date

Write-Host "🚀 DEPLOIEMENT COMPLET - $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor Cyan
Write-Host ("=" * 60) -ForegroundColor Cyan

if ($DryRun) {
    Write-Host "🔍 MODE SIMULATION (pas d'upload reel)" -ForegroundColor Yellow
    Write-Host ""
}

# Fonction pour afficher la barre de progression
function Show-ProgressBar {
    param(
        [int]$Current,
        [int]$Total,
        [string]$Activity = "Progression",
        [double]$Speed = 0,
        [string]$ETA = ""
    )

    if ($Total -eq 0) { return }
    
    $percent = [math]::Round(($Current / $Total) * 100, 1)
    $progressBar = ("█" * [math]::Floor($percent / 2)) + ("░" * (50 - [math]::Floor($percent / 2)))

    $speedText = if ($Speed -gt 0) { " | ${Speed} MB/s" } else { "" }
    $etaText = if ($ETA) { " | ETA: $ETA" } else { "" }

    Write-Host "📊 [$progressBar] ${percent}% | $Current/$Total $Activity$speedText$etaText" -ForegroundColor Cyan
}

# 1. COMPILATION
Write-Host "`n1️⃣  COMPILATION DES ASSETS" -ForegroundColor Yellow
Write-Host ("-" * 30) -ForegroundColor Yellow

try {
    Push-Location $WorkingDir
    Write-Host "🔨 Lancement de npm run build..." -ForegroundColor White

    $ErrorActionPreference = "Continue"
    $buildResult = cmd /c "cd /d $WorkingDir && npm run build" 2>&1
    $ErrorActionPreference = "Stop"

    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Compilation reussie" -ForegroundColor Green
        $compilationSuccess = $true
    } else {
        Write-Host "❌ Compilation echouee: $($buildResult -join ' ')" -ForegroundColor Red
        Write-Host "⚠️ Continuation du deploiement malgre l'erreur" -ForegroundColor Yellow
        $compilationSuccess = $false
    }

    Pop-Location
} catch {
    Write-Host "❌ Erreur compilation: $($_.Exception.Message)" -ForegroundColor Red
    $compilationSuccess = $false
}

# 2. GESTION GIT AVANT DEPLOIEMENT
Write-Host "`n2️⃣  GESTION GIT" -ForegroundColor Yellow
Write-Host ("-" * 30) -ForegroundColor Yellow

try {
    Push-Location $WorkingDir

    # Vérifier les changements
    $statusOutput = cmd /c "cd /d $WorkingDir && git status --porcelain" 2>&1
    $hasChanges = $statusOutput -and $statusOutput.Trim() -ne ""

    if ($hasChanges) {
        Write-Host "📝 Changements detectes, ajout au staging..." -ForegroundColor White
        $ErrorActionPreference = "Continue"
        $addResult = cmd /c "cd /d $WorkingDir && git add -A" 2>&1
        $ErrorActionPreference = "Stop"

        # Commit
        $commitMessage = "deploy prep: $(Get-Date -Format 'dd/MM/yyyy HH:mm') - compilation + deploiement"
        Write-Host "💾 Commit: $commitMessage" -ForegroundColor White
        $ErrorActionPreference = "Continue"
        $commitResult = cmd /c "cd /d $WorkingDir && git commit -m `"$commitMessage`" --allow-empty" 2>&1
        $ErrorActionPreference = "Stop"

        if ($LASTEXITCODE -eq 0) {
            Write-Host "✅ Commit cree" -ForegroundColor Green

            # Push
            Write-Host "📤 Push vers remote..." -ForegroundColor White
            $ErrorActionPreference = "Continue"
            $pushResult = cmd /c "cd /d $WorkingDir && git push origin dev" 2>&1
            $ErrorActionPreference = "Stop"

            if ($LASTEXITCODE -eq 0) {
                Write-Host "✅ Push reussi" -ForegroundColor Green
                $gitSuccess = $true
            } else {
                Write-Host "⚠️ Push echoue: $($pushResult -join ' ')" -ForegroundColor Yellow
            }
        } else {
            Write-Host "⚠️ Commit echoue: $($commitResult -join ' ')" -ForegroundColor Yellow
        }
    } else {
        Write-Host "ℹ️ Aucun changement a committer" -ForegroundColor Gray
        $gitSuccess = $true
    }

    Pop-Location
} catch {
    Write-Host "❌ Erreur git: $($_.Exception.Message)" -ForegroundColor Red
}

# 3. COLLECTE DES FICHIERS
Write-Host "`n3️⃣  COLLECTE DES FICHIERS" -ForegroundColor Yellow
Write-Host ("-" * 30) -ForegroundColor Yellow

$allFiles = @()
$excludedPatterns = @(
    "node_modules",
    ".git",
    "build",
    "logs",
    "*.log",
    "*.tmp",
    "*.bak",
    ".DS_Store",
    "Thumbs.db",
    "*.swp",
    "*.swo"
)

Write-Host "📂 Collecte des fichiers depuis: $PluginPath" -ForegroundColor White

$rawFiles = Get-ChildItem -Path $PluginPath -Recurse -File -ErrorAction SilentlyContinue
$totalFiles = 0

foreach ($file in $rawFiles) {
    $relativePath = $file.FullName.Replace("$PluginPath\", "").Replace("$PluginPath/", "")
    $shouldExclude = $false

    foreach ($pattern in $excludedPatterns) {
        if ($relativePath -like "*$pattern*" -or $file.Name -like $pattern) {
            $shouldExclude = $true
            break
        }
    }

    if (-not $shouldExclude) {
        $allFiles += $file
        $totalFiles++
    }
}

$totalSize = ($allFiles | Measure-Object -Property Length -Sum).Sum
Write-Host "📊 Fichiers a deployer: $totalFiles ($( [math]::Round($totalSize / 1MB, 2) ) MB)" -ForegroundColor Green

# 4. DEPLOIEMENT FTP ASYNCHRONE
Write-Host "`n4️⃣  DEPLOIEMENT FTP ASYNCHRONE" -ForegroundColor Yellow
Write-Host ("-" * 40) -ForegroundColor Yellow

if ($DryRun) {
    Write-Host "🔍 Mode simulation - pas de test FTP reel" -ForegroundColor Yellow
} elseif (!$SkipConnectionTest) {
    Write-Host "🔌 Test connexion FTP..." -ForegroundColor White
    try {
        $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost/"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 5000
        $ftpRequest.KeepAlive = $false
        $response = $ftpRequest.GetResponse()
        $response.Close()
        Write-Host "✅ Connexion FTP OK" -ForegroundColor Green
    } catch {
        Write-Host "❌ Erreur FTP: $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }
}

# Collecter les repertoires
$directories = [System.Collections.Generic.List[string]]::new()
foreach ($file in $allFiles) {
    $relativePath = $file.FullName.Replace("$PluginPath\", "").Replace("$PluginPath/", "")
    $remoteDir = [System.IO.Path]::GetDirectoryName("$FtpPath/$relativePath").Replace("\", "/")

    if ($remoteDir -and $remoteDir -ne $FtpPath -and -not $directories.Contains($remoteDir)) {
        $directories.Add($remoteDir)
    }
}

# Creation des repertoires (sequentiellement pour eviter les timeouts)
if (-not $DryRun -and $directories.Count -gt 0) {
    Write-Host "🏗️ Creation des repertoires ($($directories.Count))..." -ForegroundColor White

    $createdDirs = 0
    foreach ($dir in $directories) {
        try {
            $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$dir/"
            $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $ftpRequest.UseBinary = $true
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = 5000
            $ftpRequest.KeepAlive = $false
            
            $response = $ftpRequest.GetResponse()
            $response.Close()
            $createdDirs++
        } catch {
            # Le repertoire existe probablement deja
        }
    }
    Write-Host "✅ Repertoires crees/verifies: $createdDirs" -ForegroundColor Green
} elseif ($DryRun) {
    Write-Host "✅ Repertoires crees/verifies: $($directories.Count) (simulation)" -ForegroundColor Green
}

# Upload parallele des fichiers
$maxConcurrentUploads = $(if ($FastMode) { 10 } else { 6 })
$uploadJobs = [System.Collections.Generic.List[object]]::new()

if ($DryRun) {
    Write-Host "🔍 Mode simulation - pas d'upload reel" -ForegroundColor Yellow
    $uploadedCount = $totalFiles
    $totalBytesUploaded = $totalSize
    $ftpSuccess = $true
    Write-Host "✅ Upload FTP termine (simulation): $uploadedCount/$totalFiles fichiers" -ForegroundColor Green
    Write-Host "⚡ Donnees: $([math]::Round($totalSize / 1024 / 1024, 2)) MB" -ForegroundColor Cyan
} else {
    Write-Host "🚀 Upload parallele des fichiers ($totalFiles fichiers)..." -ForegroundColor Cyan

    $processedCount = 0
    $lastProgressTime = Get-Date
    $uploadStartTime = Get-Date

    foreach ($file in $allFiles) {
        $relativePath = $file.FullName.Replace("$PluginPath\", "").Replace("$PluginPath/", "")
        $remotePath = "$FtpPath/$relativePath".Replace("\", "/")

        # Gestion des jobs simultanes
        while ($uploadJobs.Count -ge $maxConcurrentUploads) {
            $completedJobs = $uploadJobs | Where-Object { $_.State -eq 'Completed' }
            foreach ($job in $completedJobs) {
                $result = Receive-Job $job -ErrorAction SilentlyContinue
                $processedCount++
                if ($result -and $result.Success) {
                    $uploadedCount++
                    $totalBytesUploaded += $result.Size
                    Write-Host "  ✅ $($result.File)" -ForegroundColor Green
                } elseif ($result) {
                    $failedCount++
                    Write-Host "  ❌ $($result.File) - Tentative $($result.Attempts)/3" -ForegroundColor Red
                }
                Remove-Job $job -Force -ErrorAction SilentlyContinue
                $uploadJobs.Remove($job) | Out-Null
            }

            # Barre de progression
            $currentTime = Get-Date
            if (($currentTime - $lastProgressTime).TotalSeconds -ge 1) {
                $elapsed = $currentTime - $uploadStartTime
                $speedMBps = if ($elapsed.TotalSeconds -gt 0) { [math]::Round($totalBytesUploaded / $elapsed.TotalSeconds / 1024 / 1024, 2) } else { 0 }
                Show-ProgressBar -Current $processedCount -Total $totalFiles -Activity "fichiers" -Speed $speedMBps
                $lastProgressTime = $currentTime
            }

            Start-Sleep -Milliseconds 50
        }

        # Job d'upload
        $job = Start-Job -ScriptBlock {
            param($ftpHost, $ftpUser, $ftpPass, $remotePath, $localFile, $fileName, $fileSize)

            $maxRetries = 3
            for ($attempt = 1; $attempt -le $maxRetries; $attempt++) {
                try {
                    $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$ftpUser`:$ftpPass@$ftpHost$remotePath")
                    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
                    
                    # Utiliser le mode texte pour PHP/HTML/JSON, binaire pour les autres
                    $useBinary = -not ($fileName -like "*.php" -or $fileName -like "*.html" -or $fileName -like "*.json" -or $fileName -like "*.txt" -or $fileName -like "*.md")
                    $ftpRequest.UseBinary = $useBinary
                    $ftpRequest.UsePassive = $true
                    $ftpRequest.Timeout = 15000
                    $ftpRequest.ReadWriteTimeout = 30000
                    $ftpRequest.KeepAlive = $false

                    $fileContent = [System.IO.File]::ReadAllBytes($localFile)
                    $ftpRequest.ContentLength = $fileContent.Length

                    $requestStream = $ftpRequest.GetRequestStream()
                    $requestStream.Write($fileContent, 0, $fileContent.Length)
                    $requestStream.Close()

                    $response = $ftpRequest.GetResponse()
                    $response.Close()

                    return @{ Success = $true; File = $fileName; Size = $fileSize; Attempts = $attempt }
                } catch {
                    if ($attempt -ge $maxRetries) {
                        return @{ Success = $false; File = $fileName; Size = $fileSize; Error = $_.Exception.Message; Attempts = $attempt }
                    }
                    Start-Sleep -Seconds 1
                }
            }
        } -ArgumentList $FtpHost, $FtpUser, $FtpPass, $remotePath, $file.FullName, $file.Name, $file.Length

        $uploadJobs.Add($job) | Out-Null
    }

    # Attendre la fin de tous les uploads
    $globalTimeout = $(if ($FastMode) { 300 } else { 600 })
    $globalStartTime = Get-Date

    while ($uploadJobs.Count -gt 0 -and ((Get-Date) - $globalStartTime).TotalSeconds -lt $globalTimeout) {
        $completedJobs = $uploadJobs | Where-Object { $_.State -eq 'Completed' }
        foreach ($job in $completedJobs) {
            $result = Receive-Job $job -ErrorAction SilentlyContinue
            $processedCount++
            if ($result -and $result.Success) {
                $uploadedCount++
                $totalBytesUploaded += $result.Size
                Write-Host "  ✅ $($result.File)" -ForegroundColor Green
            } elseif ($result) {
                $failedCount++
                Write-Host "  ❌ $($result.File) - Tentative $($result.Attempts)/3" -ForegroundColor Red
            }
            Remove-Job $job -Force -ErrorAction SilentlyContinue
            $uploadJobs.Remove($job) | Out-Null
        }

        # Barre de progression finale
        $currentTime = Get-Date
        if (($currentTime - $lastProgressTime).TotalSeconds -ge 1) {
            $elapsed = $currentTime - $uploadStartTime
            $speedMBps = if ($elapsed.TotalSeconds -gt 0) { [math]::Round($totalBytesUploaded / $elapsed.TotalSeconds / 1024 / 1024, 2) } else { 0 }
            Show-ProgressBar -Current $processedCount -Total $totalFiles -Activity "fichiers" -Speed $speedMBps
            $lastProgressTime = $currentTime
        }

        Start-Sleep -Milliseconds 100
    }

    # Nettoyer jobs timeout
    foreach ($job in $uploadJobs) {
        if ($job.State -ne 'Completed') {
            $failedCount++
            Write-Host "  ⏰ TIMEOUT: $($job.Name)" -ForegroundColor Red
            Stop-Job $job -ErrorAction SilentlyContinue
            Remove-Job $job -Force -ErrorAction SilentlyContinue
        }
    }

    $uploadDuration = (Get-Date) - $uploadStartTime
    $avgSpeedMBps = if ($uploadDuration.TotalSeconds -gt 0) { [math]::Round($totalBytesUploaded / $uploadDuration.TotalSeconds / 1024 / 1024, 2) } else { 0 }

    Write-Host "✅ Upload FTP termine: $uploadedCount reussis, $failedCount echoues" -ForegroundColor Green
    Write-Host "⚡ Vitesse moyenne: $avgSpeedMBps MB/s | Duree: $([math]::Round($uploadDuration.TotalSeconds))s | Donnees: $([math]::Round($totalBytesUploaded / 1024 / 1024, 2)) MB" -ForegroundColor Cyan

    $ftpSuccess = $uploadedCount -gt 0
}

# 5. GESTION GIT APRES DEPLOIEMENT
Write-Host "`n5️⃣  FINALISATION GIT" -ForegroundColor Yellow
Write-Host ("-" * 30) -ForegroundColor Yellow

try {
    Push-Location $WorkingDir

    # Commit du deploiement
    $deployMessage = "deploy: $(Get-Date -Format 'dd/MM/yyyy HH:mm') - $uploadedCount fichiers deployes"
    Write-Host "💾 Commit deploiement: $deployMessage" -ForegroundColor White

    $ErrorActionPreference = "Continue"
    $commitResult = cmd /c "cd /d $WorkingDir && git add -A && git commit -m `"$deployMessage`" --allow-empty" 2>&1
    $ErrorActionPreference = "Stop"

    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Commit deploiement cree" -ForegroundColor Green

        # Push final
        Write-Host "📤 Push final vers remote..." -ForegroundColor White
        $ErrorActionPreference = "Continue"
        $pushResult = cmd /c "cd /d $WorkingDir && git push origin dev" 2>&1
        $ErrorActionPreference = "Stop"

        if ($LASTEXITCODE -eq 0) {
            Write-Host "✅ Push final reussi" -ForegroundColor Green
        } else {
            Write-Host "⚠️ Push final echoue: $($pushResult -join ' ')" -ForegroundColor Yellow
        }
    } else {
        Write-Host "⚠️ Commit deploiement echoue: $($commitResult -join ' ')" -ForegroundColor Yellow
    }

    Pop-Location
} catch {
    Write-Host "❌ Erreur git final: $($_.Exception.Message)" -ForegroundColor Red
}

# 6. RESUME FINAL
Write-Host "`n🎉 DEPLOIEMENT TERMINE !" -ForegroundColor Green
Write-Host ("=" * 60) -ForegroundColor Green

$totalDuration = (Get-Date) - $startTime

Write-Host "📊 RESUME DETAILLE:" -ForegroundColor White
Write-Host "   • Compilation: $(if ($compilationSuccess) { "✅ Reussie" } else { "⚠️ Echouee" })" -ForegroundColor $(if ($compilationSuccess) { "Green" } else { "Yellow" })
Write-Host "   • Collecte fichiers: ✅ $totalFiles fichiers ($( [math]::Round($totalSize / 1MB, 2) ) MB)" -ForegroundColor Green
Write-Host "   • FTP Upload: $(if ($ftpSuccess) { "✅ $uploadedCount/$(($uploadedCount + $failedCount)) fichiers" } else { "❌ Echoue" }) ($( [math]::Round($totalBytesUploaded / 1024 / 1024, 2) ) MB)" -ForegroundColor $(if ($ftpSuccess) { "Green" } else { "Red" })
Write-Host "   • Git: $(if ($gitSuccess) { "✅ OK" } else { "⚠️ Partiel" })" -ForegroundColor $(if ($gitSuccess) { "Green" } else { "Yellow" })
Write-Host "   • Duree totale: $([math]::Round($totalDuration.TotalSeconds)) secondes" -ForegroundColor Cyan
Write-Host "   • Timestamp: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor White

if ($DryRun) {
    Write-Host "`n ℹ️  Mode simulation termine" -ForegroundColor Cyan
} elseif ($ftpSuccess) {
    Write-Host "`n✨ DEPLOIEMENT REUSSI !" -ForegroundColor Green
} else {
    Write-Host "`n❌ DEPLOIEMENT ECHOUÉ" -ForegroundColor Red
    exit 1
}