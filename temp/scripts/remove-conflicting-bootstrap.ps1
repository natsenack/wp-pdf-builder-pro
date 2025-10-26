# 🧹 NETTOYAGE URGENT - SUPPRESSION core/bootstrap.php CONFLICTUEL
# =================================================================
# Script pour supprimer le fichier core/bootstrap.php qui cause un conflit
# de redéclaration de fonction pdf_builder_load_core()

Write-Host "NETTOYAGE URGENT - SUPPRESSION core/bootstrap.php" -ForegroundColor Red
Write-Host "================================================" -ForegroundColor Red

# Configuration
$configPath = Join-Path $PSScriptRoot "ftp-config.env"
$config = @{}
Get-Content $configPath | ForEach-Object {
    if ($_ -match "^([^=]+)=(.*)$") {
        $config[$matches[1].Trim()] = $matches[2].Trim()
    }
}

$ftpServer = $config["FTP_HOST"]
$ftpUser = $config["FTP_USER"]
$ftpPass = $config["FTP_PASS"]
$ftpPath = $config["FTP_PATH"]

Write-Host "Configuration chargée" -ForegroundColor Green
Write-Host "Serveur: $ftpServer" -ForegroundColor Gray
Write-Host "Chemin: $ftpPath" -ForegroundColor Gray

# Fichier à supprimer
$fileToDelete = "/wp-content/plugins/wp-pdf-builder-pro/core/bootstrap.php"

Write-Host "Suppression du fichier conflictuel: $fileToDelete" -ForegroundColor Yellow

try {
    # Créer la requête FTP
    $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$ftpServer$fileToDelete")
    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
    $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)

    # Exécuter la requête
    $response = $ftpRequest.GetResponse()
    $response.Close()

    Write-Host "✅ Fichier supprimé avec succès: $fileToDelete" -ForegroundColor Green

} catch {
    Write-Host "❌ Erreur lors de la suppression: $($_.Exception.Message)" -ForegroundColor Red

    # Vérifier si le fichier existe vraiment
    try {
        $checkRequest = [System.Net.FtpWebRequest]::Create("ftp://$ftpServer$fileToDelete")
        $checkRequest.Method = [System.Net.WebRequestMethods+Ftp]::GetFileSize
        $checkRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)

        $checkResponse = $checkRequest.GetResponse()
        $checkResponse.Close()

        Write-Host "ℹ️ Le fichier existe toujours sur le serveur" -ForegroundColor Yellow

    } catch {
        Write-Host "ℹ️ Le fichier n'existe pas ou a déjà été supprimé" -ForegroundColor Blue
    }
}

Write-Host "Nettoyage terminé." -ForegroundColor Cyan