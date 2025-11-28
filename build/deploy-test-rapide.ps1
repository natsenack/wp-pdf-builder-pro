# Script pour déployer test-rapide.php à la racine du site

param(
    [switch]$SkipConnectionTest
)

$ErrorActionPreference = "Stop"

# Configuration FTP
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/"  # Racine du site web

$WorkingDir = "I:\wp-pdf-builder-pro"

# Fonction de log
function Write-Log {
    param([string]$Message, [string]$Level = "INFO")
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Write-Host "[$timestamp] [$Level] $Message"
}

Write-Log "Déploiement de test-rapide.php à la racine du site"

# Test de connexion FTP
if (!$SkipConnectionTest) {
    Write-Log "Test de connexion FTP..."
    try {
        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpHost$FtpPath")
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
        $ftpRequest.UseBinary = $true
        $ftpRequest.KeepAlive = $false
        $ftpRequest.Timeout = 30000

        $response = $ftpRequest.GetResponse()
        $response.Close()
        Write-Log "Connexion FTP OK"
    } catch {
        Write-Log "Erreur de connexion FTP: $($_.Exception.Message)" "ERROR"
        exit 1
    }
}

# Upload du fichier
$localFile = "$WorkingDir\build\test-rapide.php"
$remoteFile = "test-rapide.php"

if (!(Test-Path $localFile)) {
    Write-Log "Fichier local non trouvé: $localFile" "ERROR"
    exit 1
}

Write-Log "Upload de $localFile vers $remoteFile"

try {
    $ftpUri = "ftp://$FtpHost$FtpPath$remoteFile"
    $webClient = New-Object System.Net.WebClient
    $webClient.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
    $webClient.UploadFile($ftpUri, $localFile)
    $webClient.Dispose()

    Write-Log "Upload réussi !" "SUCCESS"
    Write-Log "URL: https://threeaxe.fr/test-rapide.php"

} catch {
    Write-Log "Erreur lors de l'upload: $($_.Exception.Message)" "ERROR"
    exit 1
}

Write-Log "Déploiement terminé avec succès"