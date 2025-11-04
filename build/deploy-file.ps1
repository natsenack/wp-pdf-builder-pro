# Script de déploiement rapide pour un fichier spécifique
param(
    [string]$FilePath
)

Write-Host "🚀 DÉPLOIEMENT RAPIDE - Fichier spécifique" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan

# Configuration FTP
$FtpServer = "65.108.242.181"
$FtpUsername = "nats"
$FtpPassword = "iZ6vU3zV2y"
$RemotePath = "/wp-content/plugins/wp-pdf-builder-pro"

Write-Host "📁 Fichier à déployer : $FilePath" -ForegroundColor Yellow

# Vérifier que le fichier existe
if (!(Test-Path $FilePath)) {
    Write-Host "❌ Fichier non trouvé : $FilePath" -ForegroundColor Red
    exit 1
}

# Test FTP
Write-Host "🔗 Test connexion FTP..." -ForegroundColor Gray
try {
    $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpServer$RemotePath")
    $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUsername, $FtpPassword)
    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
    $ftpRequest.Timeout = 10000
    $response = $ftpRequest.GetResponse()
    $response.Close()
    Write-Host "✅ Connexion FTP OK" -ForegroundColor Green
} catch {
    Write-Host "❌ Erreur FTP: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Calculer le chemin relatif pour le serveur
$RelativePath = $FilePath.Replace("D:\wp-pdf-builder-pro\plugin\", "").Replace("\", "/")
$RemoteFilePath = "ftp://$FtpServer$RemotePath/$RelativePath"

# Créer les répertoires nécessaires
$RemoteDir = [System.IO.Path]::GetDirectoryName("$RemotePath/$RelativePath")
Write-Host "📁 Création des répertoires : $RemoteDir" -ForegroundColor Gray

try {
    # Créer le répertoire templates
    $templatesDir = "ftp://$FtpServer$RemotePath/templates"
    $mkdirRequest = [System.Net.FtpWebRequest]::Create($templatesDir)
    $mkdirRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUsername, $FtpPassword)
    $mkdirRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
    $mkdirRequest.Timeout = 10000
    try { $response = $mkdirRequest.GetResponse(); $response.Close() } catch { }

    # Créer le répertoire admin
    $adminDir = "ftp://$FtpServer$RemotePath/templates/admin"
    $mkdirRequest = [System.Net.FtpWebRequest]::Create($adminDir)
    $mkdirRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUsername, $FtpPassword)
    $mkdirRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
    $mkdirRequest.Timeout = 10000
    try { $response = $mkdirRequest.GetResponse(); $response.Close() } catch { }

    Write-Host "✅ Répertoires créés" -ForegroundColor Green
} catch {
    Write-Host "⚠️ Impossible de créer les répertoires (peut-être existent déjà)" -ForegroundColor Yellow
}

Write-Host "📤 Upload vers : $RemoteFilePath" -ForegroundColor Gray

# Upload du fichier
try {
    $uploadRequest = [System.Net.FtpWebRequest]::Create($RemoteFilePath)
    $uploadRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUsername, $FtpPassword)
    $uploadRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $uploadRequest.UseBinary = $true
    $uploadRequest.Timeout = 30000

    $fileContents = [System.IO.File]::ReadAllBytes($FilePath)
    $uploadRequest.ContentLength = $fileContents.Length

    $requestStream = $uploadRequest.GetRequestStream()
    $requestStream.Write($fileContents, 0, $fileContents.Length)
    $requestStream.Close()

    $response = $uploadRequest.GetResponse()
    $response.Close()

    Write-Host "✅ Fichier déployé avec succès !" -ForegroundColor Green

} catch {
    Write-Host "❌ Erreur upload: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}