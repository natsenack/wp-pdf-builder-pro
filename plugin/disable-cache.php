<?php
/**
 * Script pour désactiver complètement le cache du plugin PDF Builder Pro
 * À exécuter une fois pour désactiver le cache
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// Désactiver le cache du plugin
$settings = get_option('pdf_builder_settings', []);
$settings['cache_enabled'] = false;
$settings['cache_ttl'] = 0;

update_option('pdf_builder_settings', $settings);

// Supprimer tous les transients liés au cache
delete_transient('pdf_builder_cache');
delete_transient('pdf_builder_templates');
delete_transient('pdf_builder_elements');

// Vider le cache WordPress si disponible
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
}

// Nettoyer le cache des assets
if (function_exists('clean_post_cache')) {
    clean_post_cache(0); // Nettoie le cache des posts
}

echo "✅ Cache du plugin PDF Builder Pro désactivé avec succès\n";
echo "✅ Tous les transients supprimés\n";
echo "✅ Cache WordPress vidé\n";