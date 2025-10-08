<?php
/**
 * Plugin Name: PDF Builder Pro - VERSION ULTRA-MINIMALISTE
 * Plugin URI: https://github.com/your-repo/pdf-builder-pro
 * Description: Version ultra-minimaliste - chargement différé maximal
 * Version: 1.0.0
 * Author: Natsenack
 * Author URI: https://github.com/your-profile
 * License: GPL v2 or later
 * Text Domain: pdf-builder-pro
 * Domain Path: /languages
 */

// SÉCURITÉ ABSOLUE : empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

// VERSION ULTRA-MINIMALISTE : AUCUN HOOK AU CHARGEMENT
// Le plugin ne fait ABSOLUMENT rien tant que WordPress n'est pas complètement chargé

// Hook d'activation ultra-sécurisé
register_activation_hook(__FILE__, 'pdf_builder_ultra_minimal_activate');

// Hook de désactivation ultra-sécurisé
register_deactivation_hook(__FILE__, 'pdf_builder_ultra_minimal_deactivate');

// Fonction d'activation qui ne fait RIEN
function pdf_builder_ultra_minimal_activate() {
    // RIEN - juste marquer comme activé de façon ultra-sécurisée
    if (function_exists('update_option')) {
        update_option('pdf_builder_ultra_minimal_activated', true);
    }
}

// Fonction de désactivation qui ne fait RIEN
function pdf_builder_ultra_minimal_deactivate() {
    // RIEN - juste nettoyer
    if (function_exists('delete_option')) {
        delete_option('pdf_builder_ultra_minimal_activated');
    }
}

// AUCUN AUTRE HOOK AU CHARGEMENT DU PLUGIN
// TOUT EST DIFFÉRÉ À PLUS TARD

// Chargement différé maximal - seulement quand WordPress est complètement prêt
add_action('wp_loaded', 'pdf_builder_ultra_minimal_init', 9999);

function pdf_builder_ultra_minimal_init() {
    // Vérifications de sécurité maximales
    if (!function_exists('get_option') || !function_exists('is_admin') || !defined('ABSPATH')) {
        return; // WordPress pas prêt
    }

    // Vérifier si le plugin est activé
    $is_activated = get_option('pdf_builder_ultra_minimal_activated', false);
    if (!$is_activated) {
        return; // Plugin pas activé
    }

    // Charger seulement si nous sommes dans l'admin ET que c'est nécessaire
    if (is_admin() && isset($_GET['page']) && strpos($_GET['page'], 'pdf-builder') !== false) {
        // Charger le bootstrap seulement quand on accède aux pages du plugin
        $bootstrap_path = plugin_dir_path(__FILE__) . 'bootstrap.php';
        if (file_exists($bootstrap_path)) {
            require_once $bootstrap_path;
            if (function_exists('pdf_builder_load_bootstrap')) {
                pdf_builder_load_bootstrap();
            }
        }
    }

    // Rien d'autre - plugin complètement dormant
}

// FIN DE LA VERSION ULTRA-MINIMALISTE