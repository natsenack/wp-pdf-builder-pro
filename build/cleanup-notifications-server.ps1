# Script de suppression des fichiers de notification sur le serveur distant
# Supprime tous les fichiers restants du système de notification

param(
    [switch]$TestMode
)

$ErrorActionPreference = "Stop"

# Configuration FTP
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

Write-Host "SUPPRESSION DES FICHIERS DE NOTIFICATION SUR SERVEUR DISTANT" -ForegroundColor Cyan
Write-Host ("=" * 70) -ForegroundColor White

if ($TestMode) {
    Write-Host "MODE TEST - Simulation uniquement" -ForegroundColor Yellow
}

# Liste des fichiers à supprimer
$filesToDelete = @(
    # Classe principale
    "core/PDF_Builder_Notification_Manager.php",

    # Assets JavaScript et CSS
    "assets/js/notifications.js",
    "assets/css/notifications.css",

    # Templates de notification
    "templates/notifications/toast-container.php",
    "templates/notifications/toast-script.php",
    "templates/notifications/admin-notice.php",

    # Fichier de test
    "notifications-test.php"
)

Write-Host "`nFichiers à supprimer:" -ForegroundColor Magenta
$filesToDelete | ForEach-Object {
    Write-Host "   - $_" -ForegroundColor White
}

$deletedCount = 0
$errorCount = 0

foreach ($file in $filesToDelete) {
    $remotePath = "$FtpPath/$file"

    if ($TestMode) {
        Write-Host "   [TEST] Suppression simulée: $remotePath" -ForegroundColor Gray
        $deletedCount++
        continue
    }

    try {
        Write-Host "   Suppression: $file" -ForegroundColor Yellow

        # Créer la requête FTP DELETE
        $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$remotePath"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
        $ftpRequest.UseBinary = $false
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 10000
        $ftpRequest.KeepAlive = $false

        # Exécuter la requête
        $response = $ftpRequest.GetResponse()
        $response.Close()

        Write-Host "   ✅ Supprimé: $file" -ForegroundColor Green
        $deletedCount++

    } catch {
        $errorCount++
        Write-Host "   ❌ Erreur ou fichier inexistant: $file" -ForegroundColor Red
        Write-Host "      $($_.Exception.Message)" -ForegroundColor Gray
    }
}

# Suppression du dossier notifications/ s'il existe
$notificationsDir = "$FtpPath/templates/notifications"

if ($TestMode) {
    Write-Host "   [TEST] Suppression simulée du dossier: templates/notifications/" -ForegroundColor Gray
} else {
    try {
        Write-Host "`nSuppression du dossier: templates/notifications/" -ForegroundColor Yellow

        # Tenter de supprimer le dossier (FTP ne supporte pas toujours la suppression récursive)
        $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$notificationsDir/"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::RemoveDirectory
        $ftpRequest.UseBinary = $false
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 10000
        $ftpRequest.KeepAlive = $false

        $response = $ftpRequest.GetResponse()
        $response.Close()

        Write-Host "   ✅ Dossier supprimé: templates/notifications/" -ForegroundColor Green

    } catch {
        Write-Host "   ❌ Erreur suppression dossier ou dossier inexistant: templates/notifications/" -ForegroundColor Red
        Write-Host "      $($_.Exception.Message)" -ForegroundColor Gray
    }
}

Write-Host "`nSUPPRESSION TERMINÉE" -ForegroundColor White
Write-Host ("=" * 70) -ForegroundColor White
Write-Host "Résumé:" -ForegroundColor Cyan
Write-Host "   Fichiers supprimés: $deletedCount" -ForegroundColor Green
Write-Host "   Erreurs: $errorCount" -ForegroundColor $(if ($errorCount -gt 0) { "Red" } else { "Green" })

if ($TestMode) {
    Write-Host "`nPour exécuter réellement la suppression, relancer sans -TestMode" -ForegroundColor Yellow
} else {
    Write-Host "`nSystème de notification supprimé du serveur distant ✅" -ForegroundColor Green
}