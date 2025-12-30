# Script de déploiement FTP ultra-simple pour PDF Builder Pro
# Déploie tout le dossier plugin/ vers le serveur FTP

param(
    [switch]$SkipConnectionTest,
    [switch]$FastMode,
    [switch]$TestMode
)

$ErrorActionPreference = "Stop"
$OutputEncoding = [System.Text.Encoding]::UTF8
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
chcp 65001 | Out-Null

# Configuration FTP
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

$WorkingDir = "I:\wp-pdf-builder-pro"
$PluginDir = Join-Path $WorkingDir "plugin"

Write-Host "DEPLOIEMENT FTP SIMPLE - PDF Builder Pro" -ForegroundColor Cyan
Write-Host ("=" * 50) -ForegroundColor White

# Test connexion FTP
if (!$SkipConnectionTest) {
    Write-Host "`n1. Test de connexion FTP..." -ForegroundColor Magenta
    try {
        $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost/"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
        $ftpRequest.UseBinary = $false
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 5000
        $ftpRequest.KeepAlive = $false
        $response = $ftpRequest.GetResponse()
        $response.Close()
        Write-Host "   Connexion FTP OK" -ForegroundColor Green
    } catch {
        Write-Host "   Erreur FTP: $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }
}

# Collecter tous les fichiers du plugin
Write-Host "`n2. Collecte des fichiers..." -ForegroundColor Magenta

$allFiles = @()
Get-ChildItem -Path $PluginDir -Recurse -File | ForEach-Object {
    $relativePath = $_.FullName.Replace($PluginDir, "").TrimStart("\")
    $allFiles += $relativePath
}

Write-Host "   Fichiers trouvés: $($allFiles.Count)" -ForegroundColor Cyan

if ($TestMode) {
    Write-Host "`nMODE TEST - Liste des fichiers qui seraient déployés:" -ForegroundColor Yellow
    $allFiles | ForEach-Object { Write-Host "   - $_" -ForegroundColor Gray }
    Write-Host "`nTest terminé. Utilisez sans -TestMode pour déployer réellement." -ForegroundColor Green
    exit 0
}

# Upload FTP
$uploadCount = 0
$errorCount = 0
$startTime = Get-Date

Write-Host "`n3. Upload FTP..." -ForegroundColor Magenta

$maxConcurrentUploads = if ($FastMode) { 8 } else { 4 }
$uploadJobs = [System.Collections.Generic.List[object]]::new()

foreach ($file in $allFiles) {
    $localFile = Join-Path $PluginDir $file
    $remotePath = $file.Replace("\", "/")

    if (!(Test-Path $localFile)) {
        Write-Host "   Fichier introuvable: $localFile" -ForegroundColor Yellow
        continue
    }

    # Attendre si trop de jobs en cours
    while ($uploadJobs.Count -ge $maxConcurrentUploads) {
        $completedJobs = $uploadJobs | Where-Object { $_.State -eq 'Completed' }
        foreach ($job in $completedJobs) {
            $result = Receive-Job $job
            if ($result.Success) {
                $uploadCount++
                Write-Host "   OK: $($result.File)" -ForegroundColor Green
            } else {
                $errorCount++
                Write-Host "   ERREUR: $($result.File) - $($result.Error)" -ForegroundColor Red
            }
            Remove-Job $job
            $uploadJobs.Remove($job) | Out-Null
        }
        Start-Sleep -Milliseconds 50
    }

    # Job d'upload
    $job = Start-Job -ScriptBlock {
        param($ftpHost, $ftpUser, $ftpPass, $ftpPath, $remotePath, $localFile)

        try {
            $ftpUri = "ftp://$ftpUser`:$ftpPass@$ftpHost$ftpPath/$remotePath"
            $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
            $ftpRequest.UseBinary = !($remotePath -like "*.php" -or $remotePath -like "*.html" -or $remotePath -like "*.json")
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = 15000
            $ftpRequest.ReadWriteTimeout = 30000
            $ftpRequest.KeepAlive = $false

            $fileContent = [System.IO.File]::ReadAllBytes($localFile)
            $ftpRequest.ContentLength = $fileContent.Length

            $stream = $ftpRequest.GetRequestStream()
            $stream.Write($fileContent, 0, $fileContent.Length)
            $stream.Close()

            $response = $ftpRequest.GetResponse()
            $response.Close()

            return @{ Success = $true; File = $remotePath }
        } catch {
            return @{ Success = $false; File = $remotePath; Error = $_.Exception.Message }
        }
    } -ArgumentList $FtpHost, $FtpUser, $FtpPass, $FtpPath, $remotePath, $localFile

    $uploadJobs.Add($job) | Out-Null
}

# Attendre la fin de tous les uploads
$globalTimeout = if ($FastMode) { 300 } else { 600 }
$globalStartTime = Get-Date

while ($uploadJobs.Count -gt 0 -and ((Get-Date) - $globalStartTime).TotalSeconds -lt $globalTimeout) {
    $completedJobs = $uploadJobs | Where-Object { $_.State -eq 'Completed' }

    foreach ($job in $completedJobs) {
        $result = Receive-Job $job
        if ($result.Success) {
            $uploadCount++
            Write-Host "   OK: $($result.File)" -ForegroundColor Green
        } else {
            $errorCount++
            Write-Host "   ERREUR: $($result.File) - $($result.Error)" -ForegroundColor Red
        }
        Remove-Job $job
        $uploadJobs.Remove($job) | Out-Null
    }

    $totalProcessed = $uploadCount + $errorCount
    if ($totalProcessed -gt 0 -and ($totalProcessed % 5) -eq 0) {
        Write-Host "   Progression: $totalProcessed / $($allFiles.Count) fichiers..." -ForegroundColor Yellow
    }

    Start-Sleep -Milliseconds 100
}

# Nettoyer les jobs timeoutés
foreach ($job in $uploadJobs) {
    if ($job.State -ne 'Completed') {
        Write-Host "   TIMEOUT: $($job.Name)" -ForegroundColor Red
        $errorCount++
        Stop-Job $job
        Remove-Job $job
    }
}

$totalTime = (Get-Date) - $startTime

Write-Host "`nDEPLOIEMENT TERMINE!" -ForegroundColor Green
Write-Host ("=" * 50) -ForegroundColor White
Write-Host "Résumé:" -ForegroundColor Cyan
Write-Host "   Fichiers uploadés: $uploadCount" -ForegroundColor Green
Write-Host "   Erreurs: $errorCount" -ForegroundColor $(if ($errorCount -gt 0) { "Red" } else { "Green" })
Write-Host "   Temps: $([math]::Round($totalTime.TotalSeconds, 1))s" -ForegroundColor Gray

if ($errorCount -gt 0) {
    Write-Host "`nCertains fichiers n'ont pas pu être uploadés." -ForegroundColor Yellow
    exit 1
} else {
    Write-Host "`nDéploiement réussi! ✅" -ForegroundColor Green
}
