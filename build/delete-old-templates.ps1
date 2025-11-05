# Script pour supprimer les anciens templates du serveur
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro/templates/builtin"

$filesToDelete = @(
    "classic.json",
    "corporate.json",
    "minimal.json",
    "modern.json"
)

Write-Host "Suppression des anciens templates..." -ForegroundColor Yellow

foreach ($file in $filesToDelete) {
    $ftpUri = "ftp://$FtpHost$FtpPath/$file"
    Write-Host "Suppression: $ftpUri" -ForegroundColor Gray

    try {
        # Créer la requête FTP
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)

        # Exécuter la requête
        $response = $ftpRequest.GetResponse()
        $response.Close()

        Write-Host "  ✓ Supprimé: $file" -ForegroundColor Green
    } catch {
        Write-Host "  ✗ Erreur suppression $file : $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "Suppression terminée!" -ForegroundColor Green