# Script pour exécuter le diagnostic sur le serveur via SSH
# Teste que l'interface DataProviderInterface fonctionne correctement

$sshHost = "65.108.242.181"
$sshUser = "nats"
$sshKeyPath = "$env:USERPROFILE\.ssh\nats_key"  # Chemin vers la clé SSH

Write-Host "🔍 Exécution du diagnostic sur le serveur..." -ForegroundColor Cyan

# Commande SSH pour exécuter le diagnostic
$sshCommand = @"
cd /var/www/nats/data/www/threeaxe.fr/wp-content/plugins/wp-pdf-builder-pro && php diagnostic.php
"@

try {
    Write-Host "🔗 Connexion SSH et exécution du diagnostic..." -ForegroundColor Yellow

    # Utiliser ssh avec la clé
    $result = & ssh -i $sshKeyPath -o StrictHostKeyChecking=no $sshUser@$sshHost $sshCommand

    Write-Host "📋 Résultat du diagnostic:" -ForegroundColor Green
    Write-Host "----------------------------------------" -ForegroundColor Gray
    Write-Host $result -ForegroundColor White
    Write-Host "----------------------------------------" -ForegroundColor Gray

    # Analyser le résultat
    if ($result -match "TOUS LES TESTS RÉUSSIS") {
        Write-Host "✅ SUCCÈS: Le système fonctionne parfaitement !" -ForegroundColor Green
    } elseif ($result -match "ERREUR") {
        Write-Host "❌ ÉCHEC: Il y a encore des erreurs à corriger" -ForegroundColor Red
    } else {
        Write-Host "⚠️ Résultat ambigu - vérification manuelle recommandée" -ForegroundColor Yellow
    }

} catch {
    Write-Host "❌ Erreur SSH: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "💡 Vérifiez que la clé SSH existe et que la connexion fonctionne" -ForegroundColor Cyan
}