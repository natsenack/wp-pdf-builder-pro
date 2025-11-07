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

    // Déterminer l'onglet actif pour vérifier le bon nonce
    $current_tab = sanitize_text_field($_POST['current_tab'] ?? 'general');

    // Vérifier le nonce selon l'onglet
    $nonce_valid = false;
    switch ($current_tab) {
        case 'pdf':
            if (isset($_POST['pdf_builder_pdf_nonce']) && 
                wp_verify_nonce($_POST['pdf_builder_pdf_nonce'], 'pdf_builder_pdf_settings')) {
                $nonce_valid = true;
            }
            break;
        case 'performance':
            if (isset($_POST['pdf_builder_performance_nonce']) && 
                wp_verify_nonce($_POST['pdf_builder_performance_nonce'], 'pdf_builder_performance_settings')) {
                $nonce_valid = true;
            }
            break;
        default:
            if (isset($_POST['pdf_builder_settings_nonce']) && 
                wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
                $nonce_valid = true;
            }
            break;
    }

    if (!$nonce_valid) {
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
                'cache_enabled' => !empty($_POST['cache_enabled']) && $_POST['cache_enabled'] === '1',
                'cache_ttl' => intval($_POST['cache_ttl'] ?? 3600),
                // Paramètres PDF dans l'onglet général
                'pdf_quality' => sanitize_text_field($_POST['pdf_quality'] ?? 'high'),
                'default_format' => sanitize_text_field($_POST['default_format'] ?? 'A4'),
                'default_orientation' => sanitize_text_field($_POST['default_orientation'] ?? 'portrait'),
            ];
            update_option('pdf_builder_settings', array_merge($settings, $general_settings));
            $notices[] = 'Paramètres généraux enregistrés avec succès';
            break;

        case 'developpeur':
            $dev_settings = [
                'developer_enabled' => !empty($_POST['developer_enabled']) && $_POST['developer_enabled'] === '1',
                'developer_password' => sanitize_text_field($_POST['developer_password'] ?? ''),
                'debug_php_errors' => !empty($_POST['debug_php_errors']) && $_POST['debug_php_errors'] === '1',
                'debug_javascript' => !empty($_POST['debug_javascript']) && $_POST['debug_javascript'] === '1',
                'debug_ajax' => !empty($_POST['debug_ajax']) && $_POST['debug_ajax'] === '1',
                'debug_performance' => !empty($_POST['debug_performance']) && $_POST['debug_performance'] === '1',
                'debug_database' => !empty($_POST['debug_database']) && $_POST['debug_database'] === '1',
                'log_level' => sanitize_text_field($_POST['log_level'] ?? 'info'),
                'log_file_size' => intval($_POST['log_file_size'] ?? 10),
                'log_retention' => intval($_POST['log_retention'] ?? 30),
                'disable_hooks' => sanitize_text_field($_POST['disable_hooks'] ?? ''),
                'enable_profiling' => !empty($_POST['enable_profiling']) && $_POST['enable_profiling'] === '1',
                'force_https' => !empty($_POST['force_https']) && $_POST['force_https'] === '1',
            ];
            
            $new_settings = array_merge($settings, $dev_settings);
            
            // Log des paramètres avant sauvegarde
            error_log('DEBUG AJAX: About to save developer settings: ' . print_r($dev_settings, true));
            error_log('DEBUG AJAX: Final merged settings: ' . print_r($new_settings, true));
            
            // Si update_option échoue, supprimer et recréer l'option
            $result = update_option('pdf_builder_settings', $new_settings);
            error_log('DEBUG AJAX: update_option result: ' . ($result ? 'SUCCESS' : 'FAILED'));
            
            if (!$result) {
                delete_option('pdf_builder_settings');
                $result = add_option('pdf_builder_settings', $new_settings);
                error_log('DEBUG AJAX: add_option result: ' . ($result ? 'SUCCESS' : 'FAILED'));
            }
            
            // Vérifier immédiatement après sauvegarde
            $verify_settings = get_option('pdf_builder_settings', []);
            error_log('DEBUG AJAX: Settings immediately after save: ' . print_r($verify_settings, true));
            error_log('DEBUG AJAX: debug_php_errors value: ' . (isset($verify_settings['debug_php_errors']) ? ($verify_settings['debug_php_errors'] ? 'true' : 'false') : 'NOT SET'));
            
            $notices[] = 'Paramètres développeur enregistrés avec succès';
            break;

        case 'licence':
            $licence_settings = [
                'license_key' => sanitize_text_field($_POST['license_key'] ?? ''),
            ];
            update_option('pdf_builder_settings', array_merge($settings, $licence_settings));
            $notices[] = 'Paramètres de licence enregistrés avec succès';
            break;

        case 'performance':
            $performance_settings = [
                'auto_save_enabled' => !empty($_POST['auto_save_enabled']) && $_POST['auto_save_enabled'] === '1',
                'auto_save_interval' => intval($_POST['auto_save_interval'] ?? 30),
                'compress_images' => !empty($_POST['compress_images']) && $_POST['compress_images'] === '1',
                'image_quality' => intval($_POST['image_quality'] ?? 85),
                'optimize_for_web' => !empty($_POST['optimize_for_web']) && $_POST['optimize_for_web'] === '1',
                'enable_hardware_acceleration' => !empty($_POST['enable_hardware_acceleration']) && $_POST['enable_hardware_acceleration'] === '1',
                'limit_fps' => !empty($_POST['limit_fps']) && $_POST['limit_fps'] === '1',
                'max_fps' => intval($_POST['max_fps'] ?? 60),
            ];
            update_option('pdf_builder_settings', array_merge($settings, $performance_settings));
            $notices[] = 'Paramètres de performance enregistrés avec succès';
            break;

        case 'pdf':
            $pdf_settings = [
                'include_metadata' => !empty($_POST['include_metadata']) && $_POST['include_metadata'] === '1',
                'embed_fonts' => !empty($_POST['embed_fonts']) && $_POST['embed_fonts'] === '1',
                'auto_crop' => !empty($_POST['auto_crop']) && $_POST['auto_crop'] === '1',
                'export_quality' => sanitize_text_field($_POST['export_quality'] ?? 'print'),
                'export_format' => sanitize_text_field($_POST['export_format'] ?? 'pdf'),
                'pdf_author' => sanitize_text_field($_POST['pdf_author'] ?? ''),
                'pdf_subject' => sanitize_text_field($_POST['pdf_subject'] ?? ''),
                'max_image_size' => intval($_POST['max_image_size'] ?? 2048),
            ];
            update_option('pdf_builder_settings', array_merge($settings, $pdf_settings));
            $notices[] = 'Paramètres PDF enregistrés avec succès';
            break;

        case 'securite':
            $securite_settings = [
                'max_template_size' => intval($_POST['max_template_size'] ?? 52428800),
                'max_execution_time' => intval($_POST['max_execution_time'] ?? 300),
                'memory_limit' => sanitize_text_field($_POST['memory_limit'] ?? '256M'),
            ];
            update_option('pdf_builder_settings', array_merge($settings, $securite_settings));
            $notices[] = 'Paramètres de sécurité enregistrés avec succès';
            break;

        case 'roles':
            $roles_settings = [
                'admin_role_access' => !empty($_POST['admin_role_access']) && $_POST['admin_role_access'] === '1',
                'editor_role_access' => !empty($_POST['editor_role_access']) && $_POST['editor_role_access'] === '1',
                'author_role_access' => !empty($_POST['author_role_access']) && $_POST['author_role_access'] === '1',
                'contributor_role_access' => !empty($_POST['contributor_role_access']) && $_POST['contributor_role_access'] === '1',
            ];
            update_option('pdf_builder_settings', array_merge($settings, $roles_settings));
            $notices[] = 'Paramètres des rôles enregistrés avec succès';
            break;

        case 'notifications':
            $notifications_settings = [
                'enable_email_notifications' => !empty($_POST['enable_email_notifications']) && $_POST['enable_email_notifications'] === '1',
                'notification_email' => sanitize_email($_POST['notification_email'] ?? ''),
                'notify_on_errors' => !empty($_POST['notify_on_errors']) && $_POST['notify_on_errors'] === '1',
                'notify_on_success' => !empty($_POST['notify_on_success']) && $_POST['notify_on_success'] === '1',
            ];
            update_option('pdf_builder_settings', array_merge($settings, $notifications_settings));
            $notices[] = 'Paramètres de notifications enregistrés avec succès';
            break;

        case 'canvas':
            $canvas_settings = [
                'canvas_width' => intval($_POST['canvas_width'] ?? 800),
                'canvas_height' => intval($_POST['canvas_height'] ?? 600),
                'canvas_background_color' => sanitize_text_field($_POST['canvas_background_color'] ?? '#ffffff'),
                'enable_grid' => !empty($_POST['enable_grid']) && $_POST['enable_grid'] === '1',
                'grid_size' => intval($_POST['grid_size'] ?? 20),
                'snap_to_grid' => !empty($_POST['snap_to_grid']) && $_POST['snap_to_grid'] === '1',
            ];
            update_option('pdf_builder_settings', array_merge($settings, $canvas_settings));
            $notices[] = 'Paramètres Canvas enregistrés avec succès';
            break;

        case 'templates':
            $templates_settings = [
                'default_template' => sanitize_text_field($_POST['default_template'] ?? ''),
                'enable_template_cache' => !empty($_POST['enable_template_cache']) && $_POST['enable_template_cache'] === '1',
                'template_cache_ttl' => intval($_POST['template_cache_ttl'] ?? 3600),
            ];
            update_option('pdf_builder_settings', array_merge($settings, $templates_settings));
            $notices[] = 'Paramètres des templates enregistrés avec succès';
            break;

        case 'maintenance':
            $maintenance_settings = [
                'enable_maintenance_mode' => !empty($_POST['enable_maintenance_mode']) && $_POST['enable_maintenance_mode'] === '1',
                'maintenance_message' => sanitize_text_field($_POST['maintenance_message'] ?? ''),
                'maintenance_end_date' => sanitize_text_field($_POST['maintenance_end_date'] ?? ''),
                'allow_admin_access' => !empty($_POST['allow_admin_access']) && $_POST['allow_admin_access'] === '1',
            ];
            update_option('pdf_builder_settings', array_merge($settings, $maintenance_settings));
            $notices[] = 'Paramètres de maintenance enregistrés avec succès';
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
