# Test rapide des statistiques FTP
param(
    [int]$MaxFiles = 20
)

$ErrorActionPreference = "Stop"

# Configuration
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpBasePath = "/wp-content/plugins/wp-pdf-builder-pro"
$PluginPath = "I:\wp-pdf-builder-pro\plugin"

Write-Host "🧪 TEST STATISTIQUES FTP - $MaxFiles fichiers" -ForegroundColor Cyan
Write-Host "=" * 50 -ForegroundColor Cyan

# Collecter quelques fichiers
$allFiles = Get-ChildItem -Path $PluginPath -Recurse -File | Select-Object -First $MaxFiles
Write-Host "📁 Test avec $($allFiles.Count) fichiers" -ForegroundColor White

$ftpUri = "ftp://$FtpHost"

$totalAttempts = 0
$uploadedCount = 0
$failedCount = 0
$retryCount = 0
$startTime = Get-Date

foreach ($file in $allFiles) {
    $relativePath = $file.FullName -replace [regex]::Escape($PluginPath), ""
    $remotePath = "$FtpBasePath$relativePath".Replace("\", "/")

    $attempts = 0
    $success = $false
    $maxRetries = 3

    while (-not $success -and $attempts -lt $maxRetries) {
        $attempts++
        try {
            # Créer répertoire
            $remoteDir = [System.IO.Path]::GetDirectoryName($remotePath).Replace("\", "/")
            if ($remoteDir -and $remoteDir -ne "/") {
                $dirParts = $remoteDir -split '/' | Where-Object { $_ }
                $currentDir = ""
                foreach ($part in $dirParts) {
                    $currentDir += "/$part"
                    try {
                        $dirRequest = [System.Net.FtpWebRequest]::Create("$ftpUri$currentDir")
                        $dirRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
                        $dirRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
                        $dirRequest.UseBinary = $true
                        $dirRequest.UsePassive = $false
                        $dirRequest.KeepAlive = $false
                        $dirRequest.Timeout = 10000
                        $dirResponse = $dirRequest.GetResponse()
                        $dirResponse.Close()
                    } catch { }
                }
            }

            # Upload
            $ftpRequest = [System.Net.FtpWebRequest]::Create("$ftpUri$remotePath")
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
            $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
            $ftpRequest.UseBinary = $true
            $ftpRequest.UsePassive = $false
            $ftpRequest.KeepAlive = $false
            $ftpRequest.Timeout = 30000
            $ftpRequest.ReadWriteTimeout = 30000

            $fileStream = [System.IO.File]::OpenRead($file.FullName)
            $requestStream = $ftpRequest.GetRequestStream()
            $buffer = New-Object byte[] 65536
            $bytesRead = 0
            while (($bytesRead = $fileStream.Read($buffer, 0, $buffer.Length)) -gt 0) {
                $requestStream.Write($buffer, 0, $bytesRead)
            }
            $requestStream.Close()
            $fileStream.Close()

            $success = $true
            $uploadedCount++
            $totalAttempts += $attempts
            if ($attempts -gt 1) { $retryCount++ }

            if ($attempts -gt 1) {
                Write-Host "  ✅ $($file.Name) (après $attempts tentatives)" -ForegroundColor Yellow
            } else {
                Write-Host "  ✅ $($file.Name)" -ForegroundColor Green
            }
        } catch {
            if ($attempts -lt $maxRetries) {
                Start-Sleep -Milliseconds (200 * $attempts)
            } else {
                $failedCount++
                $totalAttempts += $attempts
                Write-Host "  ❌ $($file.Name) : $($_.Exception.Message)" -ForegroundColor Red
            }
        }
    }
}

$endTime = Get-Date
$elapsed = $endTime - $startTime
$totalFiles = $uploadedCount + $failedCount

Write-Host "`n📊 RÉSULTATS DU TEST :" -ForegroundColor Magenta
Write-Host "   📁 Fichiers traités : $totalFiles" -ForegroundColor Magenta
Write-Host "   ✅ Réussis : $uploadedCount" -ForegroundColor Green
Write-Host "   ❌ Échoués : $failedCount" -ForegroundColor Red
Write-Host "   🔄 Taux de succès : $([math]::Round($uploadedCount / $totalFiles * 100, 1))%" -ForegroundColor Magenta
Write-Host "   🎯 Tentatives totales : $totalAttempts" -ForegroundColor Magenta
Write-Host "   📈 Moyenne tentatives/fichier : $([math]::Round($totalAttempts / $totalFiles, 2))" -ForegroundColor Magenta
Write-Host "   🔁 Fichiers avec retry : $retryCount ($(if ($uploadedCount -gt 0) { [math]::Round($retryCount / $uploadedCount * 100, 1) } else { 0 })%)" -ForegroundColor Magenta
Write-Host "   ⏱️  Durée : $([math]::Round($elapsed.TotalSeconds, 1))s" -ForegroundColor Magenta
Write-Host "   ⚡ Vitesse : $([math]::Round($uploadedCount / $elapsed.TotalMinutes, 1)) f/min" -ForegroundColor Magenta