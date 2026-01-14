<?php
/**
 * Script pour vider le cache OPcache
 * À exécuter sur le serveur pour forcer le rechargement des fichiers PHP
 */

// Vider OPcache si disponible
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache vidé avec succès\n";
} else {
    echo "⚠️ OPcache n'est pas disponible ou activé\n";
}

// Vider le cache objet de WordPress si disponible
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "✅ Cache WordPress vidé\n";
}

echo "📝 Script exécuté le " . date('Y-m-d H:i:s') . "\n";
echo "🔄 Rechargez maintenant la page de l'éditeur PDF Builder\n";
?>