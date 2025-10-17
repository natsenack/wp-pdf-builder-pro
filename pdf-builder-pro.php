<?php
/**
 * Plugin Name: PDF Builder Pro
 * Plugin URI: https://github.com/your-repo/pdf-builder-pro
 * Description: Constructeur de PDF professionnel ultra-performant avec architecture modulaire avancée
 * Version: 1.1.0-beta
 * Author: Natsenack
 * Author URI: https://github.com/your-profile
 * License: GPL v2 or later
 * Text Domain: pdf-builder-pro
 * Domain Path: /languages
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// Désactiver les avertissements de dépréciation pour la compatibilité PHP 8.1+
error_reporting(error_reporting() & ~E_DEPRECATED);

// Hook d'activation
register_activation_hook(__FILE__, 'pdf_builder_activate');

// Hook de désactivation
register_deactivation_hook(__FILE__, 'pdf_builder_deactivate');

/**
 * Fonction d'activation
 */
function pdf_builder_activate() {
    update_option('pdf_builder_activated', true);
}

/**
 * Fonction de désactivation
 */
function pdf_builder_deactivate() {
    delete_option('pdf_builder_activated');
}

// Charger le plugin de manière standard
add_action('plugins_loaded', 'pdf_builder_init');

// Charger les traductions
add_action('init', 'pdf_builder_load_textdomain');

/**
 * Charger le domaine de traduction
 */
function pdf_builder_load_textdomain() {
    load_plugin_textdomain('pdf-builder-pro', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

/**
 * Initialiser le plugin
 */
function pdf_builder_init() {
    // Vérifier que WordPress est prêt
    if (!function_exists('get_option') || !defined('ABSPATH')) {
        return;
    }

    // Charger le fichier de test admin
    $test_path = plugin_dir_path(__FILE__) . 'admin-properties-test.php';
    if (file_exists($test_path)) {
        require_once $test_path;
    }

    // Charger le fichier de diagnostic JSON
    $diagnostic_path = plugin_dir_path(__FILE__) . 'diagnostic-json.php';
    if (file_exists($diagnostic_path)) {
        require_once $diagnostic_path;
    }

    // Charger le bootstrap
    $bootstrap_path = plugin_dir_path(__FILE__) . 'bootstrap.php';
    if (file_exists($bootstrap_path)) {
        require_once $bootstrap_path;
        
        // Démarrer le plugin
        if (function_exists('pdf_builder_load_bootstrap')) {
            pdf_builder_load_bootstrap();
        }
    }

    // Charger le fichier de vérification des assets APRÈS le bootstrap
    $assets_check_path = plugin_dir_path(__FILE__) . 'assets-check.php';
    if (file_exists($assets_check_path)) {
        require_once $assets_check_path;
    }
}

// Gérer les téléchargements PDF en frontend
add_action('init', 'pdf_builder_handle_pdf_downloads');

/**
 * Gérer les téléchargements PDF
 */
function pdf_builder_handle_pdf_downloads() {
    if (isset($_GET['pdf_download'])) {
        // Charger le plugin pour gérer le téléchargement
        $bootstrap_path = plugin_dir_path(__FILE__) . 'bootstrap.php';
        if (file_exists($bootstrap_path)) {
            require_once $bootstrap_path;
            if (function_exists('pdf_builder_load_bootstrap')) {
                pdf_builder_load_bootstrap();
                
                // Traiter le téléchargement
                if (class_exists('PDF_Builder_Core')) {
                    try {
                        $core = PDF_Builder_Core::getInstance();
                        $woocommerce_integration = $core->get_woocommerce_integration();
                        if ($woocommerce_integration && method_exists($woocommerce_integration, 'handle_pdf_download')) {
                            $woocommerce_integration->handle_pdf_download();
                        }
                    } catch (Exception $e) {
                        // Gérer l'erreur silencieusement
                    }
                }
            }
        }
    }
}