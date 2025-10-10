<?php
/**
 * Plugin Name: PDF Builder Pro
 * Plugin URI: https://github.com/your-repo/pdf-builder-pro
 * Description: Constructeur de PDF professionnel ultra-performant avec architecture modulaire avancée
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

// VERSION FINALE : CHARGEMENT ULTRA-DIFFÉRÉ
// Le plugin ne fait RIEN tant que WordPress n'est pas complètement chargé

// Hook d'activation ultra-sécurisé
register_activation_hook(__FILE__, 'pdf_builder_final_activate');

// Hook de désactivation ultra-sécurisé
register_deactivation_hook(__FILE__, 'pdf_builder_final_deactivate');

// Fonction d'activation qui ne fait RIEN
function pdf_builder_final_activate() {
    // RIEN - juste marquer comme activé de façon ultra-sécurisée
    if (function_exists('update_option')) {
        update_option('pdf_builder_final_activated', true);
    }
}

// Fonction de désactivation qui ne fait RIEN
function pdf_builder_final_deactivate() {
    // RIEN - juste nettoyer
    if (function_exists('delete_option')) {
        delete_option('pdf_builder_final_activated');
    }
}

// AUCUN AUTRE HOOK AU CHARGEMENT DU PLUGIN
// TOUT EST DIFFÉRÉ À wp_loaded

// Chargement différé maximal - seulement quand WordPress est complètement prêt
add_action('wp_loaded', 'pdf_builder_final_init', 9999);

// Enregistrer le menu admin dès que possible dans l'admin
add_action('admin_menu', 'pdf_builder_register_admin_menu_early', 5);

// Charger le bootstrap dès que les plugins sont chargés (plus tôt que admin_menu)
add_action('plugins_loaded', 'pdf_builder_load_bootstrap_early', 5);

function pdf_builder_load_bootstrap_early() {
    // Charger le bootstrap immédiatement après le chargement des plugins
    $bootstrap_path = plugin_dir_path(__FILE__) . 'bootstrap.php';
    if (file_exists($bootstrap_path)) {
        require_once $bootstrap_path;
        if (function_exists('pdf_builder_load_bootstrap')) {
            pdf_builder_load_bootstrap();
        }
    }
}

function pdf_builder_register_admin_menu_early() {
    // Vérifications de sécurité minimales
    if (!defined('ABSPATH')) {
        return;
    }

    // Charger le bootstrap immédiatement quand on est dans l'admin
    // Le bootstrap gérera lui-même les vérifications d'activation
    $bootstrap_path = plugin_dir_path(__FILE__) . 'bootstrap.php';
    if (file_exists($bootstrap_path)) {
        require_once $bootstrap_path;
        if (function_exists('pdf_builder_load_bootstrap')) {
            pdf_builder_load_bootstrap();
        }
    }
}

// Fonction de test pour le menu - SUPPRIMÉE car le vrai menu est géré par PDF_Builder_Admin
// function pdf_builder_test_page() {
//     echo '<div class="wrap">';
//     echo '<h1>PDF Builder Pro - Test Menu</h1>';
//     echo '<p>Le menu fonctionne ! Le plugin est chargé.</p>';
//     echo '<p><a href="' . admin_url('plugins.php') . '">Retour aux plugins</a></p>';
//     echo '</div>';
// }

function pdf_builder_final_init() {
    // Vérifications de sécurité maximales
    if (!function_exists('get_option') || !function_exists('is_admin') || !defined('ABSPATH')) {
        return; // WordPress pas prêt
    }

    // Vérifier si le plugin est activé
    $is_activated = get_option('pdf_builder_final_activated', false);
    if (!$is_activated) {
        return; // Plugin pas activé
    }

    // Le bootstrap est déjà chargé dans l'admin via admin_menu
    // Ici on gère seulement les autres cas (frontend, API, etc.)

    // Hook pour les téléchargements PDF - seulement si demandé
    if (isset($_GET['pdf_builder_action']) && $_GET['pdf_builder_action'] === 'download_order_pdf') {
        add_action('init', 'pdf_builder_handle_pdf_download_final', 1);
    }
}

// Fonction pour gérer les téléchargements PDF WooCommerce - ultra-sécurisée
function pdf_builder_handle_pdf_download_final() {
    // Vérifications de sécurité avant de charger quoi que ce soit
    if (!isset($_GET['pdf_builder_action']) || $_GET['pdf_builder_action'] !== 'download_order_pdf') {
        return;
    }

    // Vérifier que WordPress est suffisamment chargé
    if (!function_exists('get_option') || !function_exists('is_admin') || !defined('ABSPATH')) {
        return;
    }

    // Vérifier que le plugin est activé
    $is_activated = get_option('pdf_builder_final_activated', false);
    if (!$is_activated) {
        return;
    }

    // Charger le bootstrap seulement maintenant
    $bootstrap_path = plugin_dir_path(__FILE__) . 'bootstrap.php';
    if (file_exists($bootstrap_path)) {
        require_once $bootstrap_path;
        if (function_exists('pdf_builder_load_bootstrap')) {
            pdf_builder_load_bootstrap();
        } else {
            return; // Bootstrap non disponible
        }
    } else {
        return; // Fichier bootstrap manquant
    }

    // Vérifier que la classe Core est disponible
    if (!class_exists('PDF_Builder_Core')) {
        return;
    }

    try {
        $core = PDF_Builder_Core::getInstance();
        $woocommerce_integration = $core->get_woocommerce_integration();

        if ($woocommerce_integration && method_exists($woocommerce_integration, 'handle_pdf_download')) {
            $woocommerce_integration->handle_pdf_download();
        }
    } catch (Exception $e) {
        // Log l'erreur silencieusement sans casser WordPress
        if (function_exists('error_log')) {
            error_log('PDF Builder: Erreur dans handle_pdf_download - ' . $e->getMessage());
        }
    }
}

// FIN DE LA VERSION FINALE - CHARGEMENT ULTRA-DIFFÉRÉ

