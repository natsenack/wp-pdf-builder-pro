# Test de débit FTP complet
param(
    [int]$TestFileSizeMB = 5,
    [int]$ConcurrentConnections = 5,
    [switch]$UseWebClient
)

$ErrorActionPreference = "Stop"

# Configuration
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpBasePath = "/wp-content/plugins/wp-pdf-builder-pro"

Write-Host "🧪 TEST DE DÉBIT FTP COMPLET - $TestFileSizeMB MB" -ForegroundColor Cyan
Write-Host "=" * 60 -ForegroundColor Cyan

$ftpUri = "ftp://$FtpHost"

# 1. TEST DE LATENCE
Write-Host "`n1️⃣  TEST DE LATENCE" -ForegroundColor Yellow
$latencyTests = 5
$totalLatency = 0

for ($i = 1; $i -le $latencyTests; $i++) {
    $start = Get-Date
    try {
        $request = [System.Net.FtpWebRequest]::Create("$ftpUri$FtpBasePath/")
        $request.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
        $request.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
        $request.Timeout = 10000
        $response = $request.GetResponse()
        $response.Close()
        $latency = (Get-Date) - $start
        $totalLatency += $latency.TotalMilliseconds
        Write-Host "  Test $i : $([math]::Round($latency.TotalMilliseconds, 1))ms" -ForegroundColor Green
    } catch {
        Write-Host "  Test $i : ÉCHEC - $($_.Exception.Message)" -ForegroundColor Red
    }
}

$avgLatency = $totalLatency / $latencyTests
Write-Host "📊 Latence moyenne : $([math]::Round($avgLatency, 1))ms" -ForegroundColor Magenta

# 2. CRÉATION D'UN FICHIER TEST
Write-Host "`n2️⃣  CRÉATION DU FICHIER TEST" -ForegroundColor Yellow
$testFile = "$env:TEMP\ftp_speed_test_$TestFileSizeMB`MB.dat"
$testFileSize = $TestFileSizeMB * 1MB

Write-Host "📁 Création d'un fichier de test de $TestFileSizeMB MB..." -ForegroundColor White
try {
    $buffer = New-Object byte[] 65536  # 64KB buffer
    $fs = [System.IO.File]::Create($testFile)

    $bytesWritten = 0
    while ($bytesWritten -lt $testFileSize) {
        $remaining = $testFileSize - $bytesWritten
        $toWrite = [math]::Min($buffer.Length, $remaining)
        $fs.Write($buffer, 0, $toWrite)
        $bytesWritten += $toWrite
    }
    $fs.Close()

    $actualSize = (Get-Item $testFile).Length
    Write-Host "✅ Fichier créé : $([math]::Round($actualSize / 1MB, 2)) MB" -ForegroundColor Green
} catch {
    Write-Host "❌ Erreur création fichier : $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# 3. TEST DE DÉBIT UNIQUE
Write-Host "`n3️⃣  TEST DE DÉBIT UNIQUE" -ForegroundColor Yellow

$remoteTestFile = "$FtpBasePath/speed_test_upload.dat"
$startTime = Get-Date

try {
    if ($UseWebClient) {
        Write-Host "📤 Upload avec WebClient..." -ForegroundColor White
        $webClient = New-Object System.Net.WebClient
        $webClient.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
        $webClient.UploadFile("$ftpUri$remoteTestFile", $testFile) | Out-Null
        $webClient.Dispose()
    } else {
        Write-Host "📤 Upload avec FtpWebRequest..." -ForegroundColor White
        $ftpRequest = [System.Net.FtpWebRequest]::Create("$ftpUri$remoteTestFile")
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
        $ftpRequest.UseBinary = $true
        $ftpRequest.UsePassive = $false
        $ftpRequest.KeepAlive = $false
        $ftpRequest.Timeout = 300000  # 5 minutes
        $ftpRequest.ReadWriteTimeout = 300000

        $fileStream = [System.IO.File]::OpenRead($testFile)
        $requestStream = $ftpRequest.GetRequestStream()

        $buffer = New-Object byte[] 131072  # 128KB buffer pour test
        $bytesRead = 0
        $totalUploaded = 0

        while (($bytesRead = $fileStream.Read($buffer, 0, $buffer.Length)) -gt 0) {
            $requestStream.Write($buffer, 0, $bytesRead)
            $totalUploaded += $bytesRead
        }

        $requestStream.Close()
        $fileStream.Close()
    }

    $endTime = Get-Date
    $duration = $endTime - $startTime
    $speedMbps = ($actualSize * 8) / (1000000 * $duration.TotalSeconds)
    $speedMBps = $actualSize / (1000000 * $duration.TotalSeconds)
    $speedMBmin = ($actualSize / 1000000) / $duration.TotalMinutes

    Write-Host "✅ Upload réussi en $([math]::Round($duration.TotalSeconds, 1))s" -ForegroundColor Green
    Write-Host "📊 Débit : $([math]::Round($speedMbps, 2)) Mbps | $([math]::Round($speedMBps, 2)) MB/s | $([math]::Round($speedMBmin, 2)) MB/min" -ForegroundColor Magenta

} catch {
    Write-Host "❌ Erreur upload : $($_.Exception.Message)" -ForegroundColor Red
}

# 4. TEST DE DÉBIT SIMULTANÉ
Write-Host "`n4️⃣  TEST DE DÉBIT SIMULTANÉ ($ConcurrentConnections connexions)" -ForegroundColor Yellow

$jobs = @()
$startTime = Get-Date

for ($i = 1; $i -le $ConcurrentConnections; $i++) {
    $remoteFile = "$FtpBasePath/speed_test_concurrent_$i.dat"
    $job = Start-Job -ScriptBlock {
        param($ftpUri, $remoteFile, $ftpUser, $ftpPass, $testFile)

        try {
            $ftpRequest = [System.Net.FtpWebRequest]::Create("$ftpUri$remoteFile")
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
            $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
            $ftpRequest.UseBinary = $true
            $ftpRequest.UsePassive = $false
            $ftpRequest.KeepAlive = $false
            $ftpRequest.Timeout = 300000
            $ftpRequest.ReadWriteTimeout = 300000

            $fileStream = [System.IO.File]::OpenRead($testFile)
            $requestStream = $ftpRequest.GetRequestStream()

            $buffer = New-Object byte[] 131072
            $bytesRead = 0

            while (($bytesRead = $fileStream.Read($buffer, 0, $buffer.Length)) -gt 0) {
                $requestStream.Write($buffer, 0, $bytesRead)
            }

            $requestStream.Close()
            $fileStream.Close()

            return @{Success = $true; File = $remoteFile}
        } catch {
            return @{Success = $false; Error = $_.Exception.Message; File = $remoteFile}
        }
    } -ArgumentList $ftpUri, $remoteFile, $FtpUser, $FtpPass, $testFile

    $jobs += $job
}

# Attendre que tous les jobs soient terminés
$completed = 0
$totalConcurrent = $ConcurrentConnections * $actualSize
$concurrentStart = Get-Date

while ($completed -lt $ConcurrentConnections) {
    Start-Sleep -Milliseconds 100
    $completed = ($jobs | Where-Object { $_.State -eq 'Completed' }).Count
}

$concurrentEnd = Get-Date
$concurrentDuration = $concurrentEnd - $concurrentStart
$concurrentSpeedMbps = ($totalConcurrent * 8) / (1000000 * $concurrentDuration.TotalSeconds)
$concurrentSpeedMBps = $totalConcurrent / (1000000 * $concurrentDuration.TotalSeconds)
$concurrentSpeedMBmin = ($totalConcurrent / 1000000) / $concurrentDuration.TotalMinutes

# Récupérer les résultats
$successCount = 0
foreach ($job in $jobs) {
    $result = Receive-Job $job
    if ($result.Success) {
        $successCount++
    } else {
        Write-Host "❌ Échec concurrent : $($result.Error)" -ForegroundColor Red
    }
    Remove-Job $job
}

Write-Host "✅ $successCount/$ConcurrentConnections uploads simultanés réussis" -ForegroundColor Green
Write-Host "📊 Débit simultané : $([math]::Round($concurrentSpeedMbps, 2)) Mbps | $([math]::Round($concurrentSpeedMBps, 2)) MB/s | $([math]::Round($concurrentSpeedMBmin, 2)) MB/min" -ForegroundColor Magenta

# 5. NETTOYAGE
Write-Host "`n5️⃣  NETTOYAGE" -ForegroundColor Yellow
Write-Host "🗑️  Suppression du fichier test local..." -ForegroundColor White
Remove-Item $testFile -Force -ErrorAction SilentlyContinue

Write-Host "🗑️  Suppression des fichiers test distants..." -ForegroundColor White
for ($i = 0; $i -le $ConcurrentConnections; $i++) {
    try {
        $fileToDelete = if ($i -eq 0) { "speed_test_upload.dat" } else { "speed_test_concurrent_$i.dat" }
        $deleteRequest = [System.Net.FtpWebRequest]::Create("$ftpUri$FtpBasePath/$fileToDelete")
        $deleteRequest.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
        $deleteRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
        $deleteResponse = $deleteRequest.GetResponse()
        $deleteResponse.Close()
    } catch {
        # Ignore les erreurs de suppression
    }
}

# 6. ANALYSE ET RECOMMANDATIONS
Write-Host "`n6️⃣  ANALYSE ET RECOMMANDATIONS" -ForegroundColor Yellow
Write-Host "📊 Résumé des tests :" -ForegroundColor White
Write-Host "   🔗 Latence : $([math]::Round($avgLatency, 1))ms" -ForegroundColor White
Write-Host "   📤 Débit unique : $([math]::Round($speedMBps, 2)) MB/s" -ForegroundColor White
Write-Host "   📤 Débit simultané : $([math]::Round($concurrentSpeedMBps, 2)) MB/s ($ConcurrentConnections connexions)" -ForegroundColor White

if ($speedMBps -lt 1) {
    Write-Host "`n⚠️  DÉBIT FAIBLE DÉTECTÉ !" -ForegroundColor Red
    Write-Host "   Causes possibles :" -ForegroundColor Yellow
    Write-Host "   • Connexion réseau lente" -ForegroundColor Yellow
    Write-Host "   • Serveur FTP surchargé" -ForegroundColor Yellow
    Write-Host "   • Limitations du serveur" -ForegroundColor Yellow
    Write-Host "   • Problème de configuration FTP" -ForegroundColor Yellow
} elseif ($speedMBps -lt 5) {
    Write-Host "`n⚠️  DÉBIT MOYEN" -ForegroundColor Yellow
    Write-Host "   Le débit est acceptable mais peut être amélioré" -ForegroundColor Yellow
} else {
    Write-Host "`n✅ DÉBIT EXCELLENT !" -ForegroundColor Green
    Write-Host "   La connexion réseau est performante" -ForegroundColor Green
}

Write-Host "`n💡 Recommandations :" -ForegroundColor Cyan
Write-Host "   • Test avec différentes tailles de buffer (64KB, 128KB, 256KB)" -ForegroundColor Cyan
Write-Host "   • Ajuster le nombre de connexions simultanées" -ForegroundColor Cyan
Write-Host "   • Vérifier la configuration du serveur FTP" -ForegroundColor Cyan
Write-Host "   • Considérer la compression des fichiers avant upload" -ForegroundColor Cyan