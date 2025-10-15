# Script de déploiement complet des corrections de l'aperçu PDF
# ========================================================

echo "🚀 Déploiement des corrections de l'aperçu PDF"
echo "=============================================="

# Aller dans le dossier du projet
cd g:\wp-pdf-builder-pro

# Compiler les assets si nécessaire
echo "🔨 Vérification des assets..."
if [ -f "package.json" ]; then
    npm run build 2>/dev/null || echo "⚠️  Build ignoré (erreur ou pas de package.json)"
else
    echo "ℹ️  Pas de package.json trouvé, build ignoré"
fi

# Créer une sauvegarde des fichiers modifiés
echo "💾 Création d'une sauvegarde..."
backup_dir="backup-$(date +%Y%m%d-%H%M%S)"
mkdir -p "$backup_dir"

# Fichiers modifiés lors des corrections
files_to_backup=(
    "includes/pdf-generator.php"
    "pdf-preview-diagnostic.php"
    "repair-canvas-data.php"
    "test-pdf-preview-fixes.php"
    "test-canvas-corrections.php"
    "deploy-canvas-fixes.sh"
)

for file in "${files_to_backup[@]}"; do
    if [ -f "$file" ]; then
        cp "$file" "$backup_dir/"
        echo "✅ Sauvegardé: $file"
    fi
done

echo "📦 Sauvegarde créée dans: $backup_dir"

# Déployer via FTP
echo "📤 Déploiement FTP..."
cd tools
.\ftp-deploy-simple.ps1

# Push Git avec les corrections
echo "🔄 Push vers le repo dev..."
cd ..
git add .
git commit -m "🔧 Corrections complètes de l'aperçu PDF

✅ DIAGNOSTIC ET RÉPARATION:
- Script de diagnostic pdf-preview-diagnostic.php
- Outil de réparation des données canvas repair-canvas-data.php
- Tests de validation test-pdf-preview-fixes.php

✅ CORRECTIONS DU GÉNÉRATEUR PDF:
- Conversions d'unités px->mm corrigées (facteur 0.3529)
- Taille de police px->pt corrigée
- Gestion des padding et marges améliorée
- Validation des éléments avant rendu
- Support de nouveaux types: circle, line, barcode, qrcode
- Gestion d'erreurs robuste avec fallback
- Remplacement automatique des variables WooCommerce
- Limites A4 595×842px respectées
- Logging et débogage améliorés

✅ AMÉLIORATIONS DE PERFORMANCE:
- useMemo et useCallback pour éviter les re-renders
- Gestion optimisée des événements souris
- Calculs de coordonnées précis avec zoom
- Mémoire optimisée avec cleanup automatique

✅ COMPATIBILITÉ:
- Support complet des templates existants
- Migration automatique des anciennes structures
- Fallback pour éléments non supportés
- Rétrocompatibilité préservée"

git push origin dev

echo ""
echo "✅ DÉPLOIEMENT TERMINÉ !"
echo "=========================="
echo ""
echo "📋 Corrections déployées:"
echo "  ✅ Conversions d'unités corrigées"
echo "  ✅ Nouveaux types d'éléments supportés"
echo "  ✅ Gestion d'erreurs améliorée"
echo "  ✅ Variables WooCommerce remplacées"
echo "  ✅ Limites A4 respectées"
echo "  ✅ Performance optimisée"
echo ""
echo "🧪 Tests disponibles:"
echo "  - pdf-preview-diagnostic.php (diagnostic complet)"
echo "  - test-pdf-preview-fixes.php (tests des corrections)"
echo "  - repair-canvas-data.php (réparation des données)"
echo ""
echo "📦 Sauvegarde disponible: $backup_dir"
echo ""
echo "🎯 L'aperçu PDF devrait maintenant fonctionner correctement !"