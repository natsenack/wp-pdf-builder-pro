# Script PowerShell pour exécuter le script de vérification de table sur le serveur FTP
# Via SSH vers le serveur

param(
    [string]$SshHost = "threeaxe.fr",
    [string]$SshUser = "root",
    [string]$SshPort = "22",
    [string]$WordpressPath = "/var/www/nats/data/www/threeaxe.fr"
)

Write-Host "=== Vérification de la table wp_pdf_builder_settings ===" -ForegroundColor Cyan

# Construire la commande SSH
$command = @"
cd $WordpressPath && php -r "
require_once 'wp-load.php';
require_once 'wp-content/plugins/wp-pdf-builder-pro/check-and-fix-table.php';
"
"@

Write-Host "Exécution de la commande SSH..." -ForegroundColor Yellow
Write-Host "Hôte: $SshHost:$SshPort" -ForegroundColor Gray
Write-Host "Utilisateur: $SshUser" -ForegroundColor Gray

try {
    # Exécuter la commande via SSH
    # Note: Nécessite une connexion SSH configurée
    ssh -p $SshPort "${SshUser}@${SshHost}" $command
    
    Write-Host "✅ Script de vérification exécuté avec succès" -ForegroundColor Green
}
catch {
    Write-Host "❌ Erreur lors de l'exécution du script SSH:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
}
