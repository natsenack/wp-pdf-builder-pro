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

// Définir les constantes du plugin
define('PDF_BUILDER_PLUGIN_FILE', __FILE__);
define('PDF_BUILDER_PLUGIN_DIR', dirname(__FILE__) . '/');
// PDF_BUILDER_PLUGIN_URL sera défini dans constants.php avec plugins_url()
define('PDF_BUILDER_VERSION', '1.1.0');

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
    // Créer une table de logs si nécessaire
    global $wpdb;
    $table_name = $wpdb->prefix . 'pdf_builder_logs';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            log_message text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    update_option('pdf_builder_version', '1.1.0');
}

/**
 * Fonction de désactivation
 */
function pdf_builder_deactivate() {
    delete_option('pdf_builder_activated');
}

// Charger le plugin de manière standard
add_action('plugins_loaded', 'pdf_builder_init');
add_action('plugins_loaded', 'pdf_builder_load_textdomain', 1);

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

    // Charger le bootstrap (version complète pour la production)
    $bootstrap_path = plugin_dir_path(__FILE__) . 'bootstrap.php';
    if (file_exists($bootstrap_path)) {
        require_once $bootstrap_path;

        // Démarrer le plugin
        if (function_exists('pdf_builder_load_bootstrap')) {
            pdf_builder_load_bootstrap();
        } else {
            // Log si bootstrap n'existe pas

        }
    }

    // Tools for development/tests removed from production bootstrap

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

// Charger le plugin pour les requêtes AJAX
add_action('admin_init', 'pdf_builder_load_for_ajax');
add_action('wp_ajax_nopriv_wp_pdf_preview_image', 'pdf_builder_handle_preview_ajax');
add_action('wp_ajax_wp_pdf_preview_image', 'pdf_builder_handle_preview_ajax');

// Actions AJAX pour la sauvegarde des paramètres
add_action('wp_ajax_pdf_builder_save_settings', 'pdf_builder_ajax_save_settings');

/**
 * Charger le plugin pour les requêtes AJAX
 */
function pdf_builder_load_for_ajax() {
    // Charger le bootstrap pour les requêtes AJAX
    $bootstrap_path = plugin_dir_path(__FILE__) . 'bootstrap.php';
    if (file_exists($bootstrap_path)) {
        require_once $bootstrap_path;
        if (function_exists('pdf_builder_load_bootstrap')) {
            pdf_builder_load_bootstrap();
        }
    }
}

/**
 * Gestionnaire AJAX pour les aperçus PDF
 * Cette fonction DOIT être un callback AJAX véritable qui produit une réponse
 */
function pdf_builder_handle_preview_ajax() {
    // Charger le bootstrap
    pdf_builder_load_for_ajax();
    
    // Le bootstrap a instancié PreviewImageAPI qui a re-enregistré les actions AJAX.
    // Maintenant, appelons directement la méthode generate_preview si PreviewImageAPI existe
    if (class_exists('WP_PDF_Builder_Pro\Api\PreviewImageAPI')) {
        // Créer une nouvelle instance et appeler generate_preview directement
        $api = new \WP_PDF_Builder_Pro\Api\PreviewImageAPI();
        $api->generate_preview();
    } else {
        // Fallback: envoyer une erreur JSON
        header('Content-Type: application/json; charset=UTF-8', true);
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'PreviewImageAPI not found - plugin not properly initialized'
        ]);
        exit;
    }
}

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

/**
 * Gérer la sauvegarde AJAX des paramètres
 */
function pdf_builder_ajax_save_settings() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_die(json_encode([
            'success' => false,
            'message' => 'Permissions insuffisantes'
        ]));
    }

    // Vérifier le nonce
    if (!isset($_POST['pdf_builder_settings_nonce']) || 
        !wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
        wp_die(json_encode([
            'success' => false,
            'message' => 'Nonce invalide'
        ]));
    }

    $notices = [];
    $settings = get_option('pdf_builder_settings', []);

    // Déterminer l'onglet actif
    $current_tab = sanitize_text_field($_POST['current_tab'] ?? 'general');

    // Traiter selon l'onglet actif
    switch ($current_tab) {
        case 'general':
            $general_settings = [
                'cache_enabled' => isset($_POST['cache_enabled']),
                'cache_ttl' => intval($_POST['cache_ttl'] ?? 3600),
                'auto_save_enabled' => isset($_POST['auto_save_enabled']),
                'auto_save_interval' => intval($_POST['auto_save_interval'] ?? 30),
                'compress_images' => isset($_POST['compress_images']),
                'image_quality' => intval($_POST['image_quality'] ?? 85),
                'optimize_for_web' => isset($_POST['optimize_for_web']),
                'enable_hardware_acceleration' => isset($_POST['enable_hardware_acceleration']),
                'limit_fps' => isset($_POST['limit_fps']),
                'max_fps' => intval($_POST['max_fps'] ?? 60),
            ];
            update_option('pdf_builder_settings', array_merge($settings, $general_settings));
            $notices[] = 'Paramètres généraux enregistrés avec succès';
            break;

        case 'developpeur':
            $dev_settings = [
                'developer_enabled' => isset($_POST['developer_enabled']),
                'developer_password' => sanitize_text_field($_POST['developer_password'] ?? ''),
                'debug_php_errors' => isset($_POST['debug_php_errors']),
                'debug_javascript' => isset($_POST['debug_javascript']),
                'debug_ajax' => isset($_POST['debug_ajax']),
                'debug_performance' => isset($_POST['debug_performance']),
                'debug_database' => isset($_POST['debug_database']),
                'log_level' => sanitize_text_field($_POST['log_level'] ?? 'info'),
                'log_file_size' => intval($_POST['log_file_size'] ?? 10),
                'log_retention' => intval($_POST['log_retention'] ?? 30),
                'disable_hooks' => sanitize_text_field($_POST['disable_hooks'] ?? ''),
                'enable_profiling' => isset($_POST['enable_profiling']),
                'force_https' => isset($_POST['force_https']),
            ];
            update_option('pdf_builder_settings', array_merge($settings, $dev_settings));
            $notices[] = 'Paramètres développeur enregistrés avec succès';
            break;

        default:
            $notices[] = 'Onglet non reconnu';
            break;
    }

    wp_die(json_encode([
        'success' => !empty($notices),
        'message' => !empty($notices) ? implode(', ', $notices) : 'Aucune modification sauvegardée',
        'notices' => $notices
    ]));
}
