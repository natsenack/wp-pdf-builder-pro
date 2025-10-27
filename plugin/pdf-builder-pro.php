<?php
/**
 * Plugin Name: PDF Builder Pro
 * Plugin URI: https://github.com/natsenack/wp-pdf-builder-pro
 * Description: Constructeur de PDF professionnel ultra-performant avec architecture modulaire avancée
 * Version: 1.1.0
 * Author: Natsenack
 * Author URI: https://github.com/natsenack
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

// Charger le logger pour les hooks d'activation/désactivation
if (file_exists(plugin_dir_path(__FILE__) . 'src/Managers/PDF_Builder_Logger.php')) {
    require_once plugin_dir_path(__FILE__) . 'src/Managers/PDF_Builder_Logger.php';
}

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

    // Ajouter les headers de cache pour les assets
    add_action('wp_enqueue_scripts', 'pdf_builder_add_asset_cache_headers', 1);
    add_action('admin_enqueue_scripts', 'pdf_builder_add_asset_cache_headers', 1);

    // Charger le bootstrap (à la racine du plugin)
    $bootstrap_path = plugin_dir_path(__FILE__) . 'bootstrap.php';
    if (file_exists($bootstrap_path)) {
        require_once $bootstrap_path;

        // Démarrer le plugin
        if (function_exists('pdf_builder_load_bootstrap')) {
            pdf_builder_load_bootstrap();
        }
    } else {
        // Log si bootstrap n'existe pas
    }

    // Charger les outils de test en mode développement
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $test_tools_path = plugin_dir_path(__FILE__) . 'tools/security-tests-phase5.php';
        if (file_exists($test_tools_path)) {
            require_once $test_tools_path;
        }
    }

    // Charger le moniteur de performance
    $performance_monitor_path = plugin_dir_path(__FILE__) . 'src/Managers/PDF_Builder_Performance_Monitor.php';
    if (file_exists($performance_monitor_path)) {
        require_once $performance_monitor_path;
    }
}

/**
 * Ajouter les headers de cache pour les assets du plugin
 */
function pdf_builder_add_asset_cache_headers() {
    // Headers de cache pour les assets du plugin (1 semaine)
    $cache_time = 604800; // 7 jours en secondes

    // Pour les assets JavaScript
    if (isset($_SERVER['REQUEST_URI']) &&
        (strpos($_SERVER['REQUEST_URI'], '/wp-content/plugins/wp-pdf-builder-pro/assets/js/') !== false ||
         strpos($_SERVER['REQUEST_URI'], '/wp-content/plugins/wp-pdf-builder-pro/assets/css/') !== false)) {

        // Headers de cache
        header('Cache-Control: public, max-age=' . $cache_time);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cache_time) . ' GMT');
        header('ETag: "' . md5($_SERVER['REQUEST_URI'] . filemtime($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI'])) . '"');

        // Compression si supportée
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
            strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false &&
            strpos($_SERVER['REQUEST_URI'], '.gz') !== false) {
            header('Content-Encoding: gzip');
        }
    }
}

// Gérer les téléchargements PDF en frontend
add_action('init', 'pdf_builder_handle_pdf_downloads');

/**
 * Gérer les téléchargements PDF
 */
function pdf_builder_handle_pdf_downloads() {
    if (isset($_GET['pdf_download'])) {
        // Charger le bootstrap pour gérer le téléchargement
        $bootstrap_path = plugin_dir_path(__FILE__) . 'bootstrap.php';
        if (file_exists($bootstrap_path)) {
            require_once $bootstrap_path;
            if (function_exists('pdf_builder_load_bootstrap')) {
                pdf_builder_load_bootstrap();
            }
        }
    }
}