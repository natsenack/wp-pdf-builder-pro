# Script pour supprimer le fichier dupliqué DataProviderInterface.php du serveur
# À exécuter sur le serveur ou via FTP

# Connexion FTP (remplacer les valeurs)
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

# Créer une session FTP
$ftpUri = "ftp://$FtpHost$FtpPath/src/Interfaces/DataProviderInterface.php"

try {
    Write-Host "🗑️ Suppression du fichier dupliqué DataProviderInterface.php..." -ForegroundColor Yellow

    # Créer la requête FTP DELETE
    $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
    $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)

    # Exécuter la requête
    $response = $ftpRequest.GetResponse()
    $response.Close()

    Write-Host "✅ Fichier dupliqué supprimé avec succès !" -ForegroundColor Green

} catch {
    Write-Host "❌ Erreur lors de la suppression: $($_.Exception.Message)" -ForegroundColor Red
}

# Vérifier que le fichier n'existe plus
try {
    Write-Host "🔍 Vérification que le fichier a été supprimé..." -ForegroundColor Cyan

    $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::GetFileSize
    $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)

    $response = $ftpRequest.GetResponse()
    Write-Host "⚠️ Le fichier existe encore !" -ForegroundColor Yellow

} catch {
    Write-Host "✅ Confirmation: le fichier dupliqué n'existe plus" -ForegroundColor Green
}