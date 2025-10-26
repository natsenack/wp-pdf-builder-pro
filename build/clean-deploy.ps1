# Script de nettoyage complet du serveur avant redéploiement

param(
    [string]$FtpHost = "65.108.242.181",
    [string]$FtpUser = "ftp",
    [string]$FtpPass = "t-3=,DGq%Z8(",
    [string]$PluginPath = "/wp-content/plugins/wp-pdf-builder-pro"
)

Write-Host "🧹 NETTOYAGE COMPLET DU PLUGIN SUR LE SERVEUR" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

# Créer la session FTP
$ftpUri = "ftp://${FtpHost}${PluginPath}/"
$credential = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)

try {
    Write-Host "📁 Suppression de : $PluginPath" -ForegroundColor Yellow
    Write-Host ""
    
    # Utiliser FTP pour supprimer récursivement
    $request = [System.Net.FtpWebRequest]::Create($ftpUri)
    $request.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
    $request.Credentials = $credential
    
    # Pour FTP, il faut supprimer les fichiers d'abord, puis les dossiers
    # C'est plus facile via PowerShell avec WinSCP ou via SSH
    
    Write-Host "⚠️  Impossible de supprimer via FTP standard." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "💡 Solution alternative : Utiliser le SSH/SFTP ou l'admin FTP du serveur" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Ou exécuter sur le serveur :" -ForegroundColor Green
    Write-Host "rm -rf /wp-content/plugins/wp-pdf-builder-pro" -ForegroundColor Green
    Write-Host ""
    
} catch {
    Write-Host "❌ Erreur : $_" -ForegroundColor Red
}

Write-Host ""
Write-Host "✅ Pour continuer le déploiement propre :" -ForegroundColor Green
Write-Host "   1. Supprimez manuellement le dossier wp-pdf-builder-pro via FTP/SSH" -ForegroundColor Green
Write-Host "   2. Exécutez : .\build\deploy.ps1 -Mode plugin -Force" -ForegroundColor Green
Write-Host ""
