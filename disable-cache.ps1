# Script PowerShell pour désactiver le cache du plugin PDF Builder Pro
Write-Host "🔧 Désactivation du cache du plugin PDF Builder Pro..." -ForegroundColor Yellow

# Vérifier si PHP est disponible
$phpPath = Get-Command php -ErrorAction SilentlyContinue
if (-not $phpPath) {
    Write-Host "❌ PHP n'est pas installé ou n'est pas dans le PATH" -ForegroundColor Red
    exit 1
}

# Exécuter le script PHP
Write-Host "📝 Exécution du script de désactivation..." -ForegroundColor Cyan
try {
    $output = & php -r "
    require_once 'disable-cache.php';
    "
    Write-Host "✅ Cache désactivé avec succès !" -ForegroundColor Green
    Write-Host $output -ForegroundColor White
} catch {
    Write-Host "❌ Erreur lors de la désactivation du cache:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
}

Write-Host "`n🎯 Résumé des actions effectuées:" -ForegroundColor Cyan
Write-Host "  ✅ Option cache_enabled définie à false" -ForegroundColor Green
Write-Host "  ✅ TTL du cache défini à 0" -ForegroundColor Green
Write-Host "  ✅ Transients supprimés (pdf_builder_cache, templates, elements)" -ForegroundColor Green
Write-Host "  ✅ Cache WordPress vidé" -ForegroundColor Green
Write-Host "  ✅ Headers de cache modifiés pour forcer no-cache" -ForegroundColor Green

Write-Host "`n📋 Prochaines étapes:" -ForegroundColor Yellow
Write-Host "  1. Vider le cache de votre navigateur (Ctrl+F5)" -ForegroundColor White
Write-Host "  2. Tester les modifications dans l'éditeur PDF" -ForegroundColor White
Write-Host "  3. Vérifier que les assets se rechargent à chaque modification" -ForegroundColor White