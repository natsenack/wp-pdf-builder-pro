<?php
/**
 * Test rapide de l'action AJAX PDF Builder
 */

// Simuler un environnement WordPress minimal
define('ABSPATH', __DIR__ . '/');
define('WPINC', 'wp-includes');

// Charger le plugin
require_once 'pdf-builder-pro.php';

// Simuler WordPress functions basiques
if (!function_exists('has_action')) {
    function has_action($tag) {
        global $wp_actions;
        return isset($wp_actions[$tag]);
    }
}

if (!function_exists('add_action')) {
    function add_action($tag, $callback) {
        global $wp_actions;
        $wp_actions[$tag] = $callback;
    }
}

global $wp_actions;
$wp_actions = [];

// Charger le plugin
if (function_exists('pdf_builder_bootstrap')) {
    pdf_builder_bootstrap();
    echo "✅ Plugin chargé avec succès\n";

    // Vérifier si l'action AJAX est enregistrée
    if (has_action('wp_ajax_pdf_builder_unified_preview')) {
        echo "✅ Action AJAX pdf_builder_unified_preview enregistrée\n";
    } else {
        echo "❌ Action AJAX non trouvée\n";
    }
} else {
    echo "❌ Fonction pdf_builder_bootstrap non trouvée\n";
}
?>