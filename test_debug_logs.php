<?php
/**
 * Test des logs de débogage PDF Builder
 */

echo "=== Test des logs de débogage PDF Builder ===\n";
echo "Les logs de débogage ont été déployés.\n\n";

echo "Pour voir les logs :\n";
echo "1. Ouvrez une commande WooCommerce\n";
echo "2. Cliquez sur 'Aperçu PDF'\n";
echo "3. Vérifiez les logs d'erreur PHP :\n";
echo "   - Apache/XAMPP: tail -f /chemin/vers/apache/logs/error.log | grep \"PDF BUILDER\"\n";
echo "   - WordPress: tail -f /chemin/vers/wordpress/wp-content/debug.log | grep \"PDF BUILDER\"\n\n";

echo "Les logs suivants seront affichés :\n";
echo "- Données de la commande (nom client, total, statut)\n";
echo "- Nombre d'éléments traités dans le template\n";
echo "- Chaque élément traité avec son type et contenu généré\n\n";

echo "Si les informations de commande ne s'affichent pas, les logs nous diront pourquoi.\n";