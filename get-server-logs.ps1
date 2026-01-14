# Script pour récupérer les logs PHP du serveur
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content"

# Fonction pour récupérer un fichier via FTP
function Get-FtpFile {
    param([string]$remoteFile, [string]$localFile)

    try {
        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpHost$FtpPath/$remoteFile")
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)

        $ftpResponse = $ftpRequest.GetResponse()
        $responseStream = $ftpResponse.GetResponseStream()
        $reader = New-Object System.IO.StreamReader($responseStream)

        $content = $reader.ReadToEnd()

        $reader.Close()
        $responseStream.Close()
        $ftpResponse.Close()

        # Sauvegarder localement
        $content | Out-File -FilePath $localFile -Encoding UTF8
        Write-Host "✅ Fichier récupéré: $remoteFile -> $localFile"

        return $content
    }
    catch {
        Write-Host "❌ Erreur lors de la récupération de $remoteFile : $($_.Exception.Message)"
        return $null
    }
}

Write-Host "🔍 Récupération des logs PHP du serveur..."
Write-Host "========================================"

# Essayer différents emplacements de logs
$logFiles = @(
    "debug.log",
    "../logs/error_log",
    "uploads/logs/php_error.log"
)

foreach ($logFile in $logFiles) {
    Write-Host "Tentative de récupération: $logFile"
    $content = Get-FtpFile -remoteFile $logFile -localFile "server_$($logFile -replace '/', '_')"

    if ($content) {
        # Afficher les dernières lignes contenant "PDF Builder"
        $pdfBuilderLogs = $content -split "`n" | Where-Object { $_ -like "*PDF Builder*" } | Select-Object -Last 10
        if ($pdfBuilderLogs) {
            Write-Host "📋 Logs PDF Builder trouvés dans $logFile :"
            $pdfBuilderLogs | ForEach-Object { Write-Host "  $_" }
        } else {
            Write-Host "ℹ️ Aucun log PDF Builder trouvé dans $logFile"
        }
    }
}

Write-Host "========================================"
Write-Host "✅ Récupération terminée"