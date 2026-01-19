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
$RelativePath = $FilePath.Replace("I:\wp-pdf-builder-pro\", "").Replace("D:\wp-pdf-builder-pro\", "").Replace("\", "/")
# Supprimer le préfixe 'plugin/' si présent pour correspondre à la structure du serveur
$RelativePath = $RelativePath -replace "^plugin/", ""
$RemoteFilePath = "ftp://$FtpServer$RemotePath/$RelativePath"

# Créer les répertoires nécessaires récursivement
$RemoteDir = [System.IO.Path]::GetDirectoryName("$RemotePath/$RelativePath").Replace("\", "/")
Write-Host "📁 Création des répertoires : $RemoteDir" -ForegroundColor Gray

try {
    # Fonction pour créer les répertoires récursivement
    function New-FtpDirectory {
        param([string]$ftpPath)
        try {
            $mkdirRequest = [System.Net.FtpWebRequest]::Create($ftpPath)
            $mkdirRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUsername, $FtpPassword)
            $mkdirRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $mkdirRequest.Timeout = 10000
            $response = $mkdirRequest.GetResponse()
            $response.Close()
            Write-Host "  ✅ Créé: $ftpPath" -ForegroundColor Gray
        } catch {
            # Le répertoire existe probablement déjà, c'est normal
        }
    }

    # Diviser le chemin et créer chaque niveau
    $pathParts = $RemoteDir -split "/" | Where-Object { $_ -ne "" }
    $currentPath = "ftp://$FtpServer"

    foreach ($part in $pathParts) {
        $currentPath += "/$part"
        New-FtpDirectory $currentPath
    }

    Write-Host "✅ Répertoires créés" -ForegroundColor Green
} catch {
    Write-Host "⚠️ Erreur lors de la création des répertoires: $($_.Exception.Message)" -ForegroundColor Yellow
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