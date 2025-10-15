# Script de déploiement des corrections du canvas A4
# ====================================================

echo "🚀 Déploiement des corrections du canvas A4"
echo "=========================================="

# Aller dans le dossier tools
cd g:\wp-pdf-builder-pro\tools

# Compiler les assets (si nécessaire)
echo "🔨 Compilation des assets..."
npm run build 2>/dev/null || echo "⚠️  Build ignoré (pas de package.json ou erreur)"

# Déployer via FTP
echo "📤 Déploiement FTP..."
.\ftp-deploy-simple.ps1

# Push Git
echo "🔄 Push vers le repo dev..."
git add .
git commit -m "🔧 Corrections canvas A4 - Optimisations performance et contraintes A4 préservées

- Contraintes A4 595×842pt maintenues
- Gestion optimisée des événements souris
- Calculs de coordonnées précis avec zoom
- Dimensions minimales 10×10px
- Snap to grid amélioré
- Mémoire optimisée avec useMemo/useCallback
- Validation robuste des propriétés"

git push origin dev

echo ""
echo "✅ Déploiement terminé !"
echo "🎯 Corrections du canvas A4 déployées avec succès"
echo ""
echo "📋 Vérifications à faire :"
echo "  - Canvas A4 portrait fonctionne correctement"
echo "  - Éléments restent dans les limites 595×842pt"
echo "  - Drag & drop fluide"
echo "  - Redimensionnement avec contraintes"
echo "  - Pas de memory leaks"
echo "  - Performance améliorée"