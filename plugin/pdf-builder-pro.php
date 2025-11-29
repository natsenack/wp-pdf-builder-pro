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

// VERSION ULTRA-SIMPLE - ne charger que l'essentiel
if (function_exists('add_action')) {
    add_action('plugins_loaded', function() {
        // Charger seulement le bootstrap minimal
        $bootstrap = PDF_BUILDER_PLUGIN_DIR . 'bootstrap.php';
        if (file_exists($bootstrap)) {
            require_once $bootstrap;
        }
    }, 1);
}

/**
 * Fonction d'activation
 */
function pdf_builder_activate()
{

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

    // Créer une table de templates si nécessaire
    $table_templates = $wpdb->prefix . 'pdf_builder_templates';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_templates'") != $table_templates) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_templates (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            template_data longtext NOT NULL,
            user_id bigint(20) unsigned NOT NULL DEFAULT 0,
            is_default tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY name (name),
            KEY user_id (user_id),
            KEY is_default (is_default)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    update_option('pdf_builder_version', '1.1.0');

    // Vérifier et créer les tables manquantes pour les mises à jour
    pdf_builder_check_tables();
}

/**
 * Vérifier et créer les tables manquantes
 */
function pdf_builder_check_tables() {
    global $wpdb;

    // Créer la table de templates si elle n'existe pas
    $table_templates = $wpdb->prefix . 'pdf_builder_templates';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_templates'") != $table_templates) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_templates (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            template_data longtext NOT NULL,
            user_id bigint(20) unsigned NOT NULL DEFAULT 0,
            is_default tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY name (name),
            KEY user_id (user_id),
            KEY is_default (is_default)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    } else {
        // Vérifier et ajouter les colonnes manquantes pour les mises à jour
        pdf_builder_update_table_schema();
    }
}

/**
 * Mettre à jour le schéma des tables existantes
 */
function pdf_builder_update_table_schema() {
    global $wpdb;
    
    $table_templates = $wpdb->prefix . 'pdf_builder_templates';
    
    // Vérifier et ajouter la colonne user_id
    $user_id_exists = $wpdb->get_results("SHOW COLUMNS FROM `$table_templates` LIKE 'user_id'");
    if (empty($user_id_exists)) {
        $wpdb->query("ALTER TABLE `$table_templates` ADD COLUMN user_id bigint(20) unsigned NOT NULL DEFAULT 0");
        $wpdb->query("ALTER TABLE `$table_templates` ADD KEY user_id (user_id)");
    }
    
    // Vérifier et ajouter la colonne is_default
    $is_default_exists = $wpdb->get_results("SHOW COLUMNS FROM `$table_templates` LIKE 'is_default'");
    if (empty($is_default_exists)) {
        $wpdb->query("ALTER TABLE `$table_templates` ADD COLUMN is_default tinyint(1) NOT NULL DEFAULT 0");
        $wpdb->query("ALTER TABLE `$table_templates` ADD KEY is_default (is_default)");
    }
    
    // Vérifier et ajouter la colonne is_premium
    $is_premium_exists = $wpdb->get_results("SHOW COLUMNS FROM `$table_templates` LIKE 'is_premium'");
    if (empty($is_premium_exists)) {
        $wpdb->query("ALTER TABLE `$table_templates` ADD COLUMN is_premium tinyint(1) NOT NULL DEFAULT 0");
    }
    
    // Vérifier et ajouter la colonne metadata
    $metadata_exists = $wpdb->get_results("SHOW COLUMNS FROM `$table_templates` LIKE 'metadata'");
    if (empty($metadata_exists)) {
        $wpdb->query("ALTER TABLE `$table_templates` ADD COLUMN metadata longtext");
    }
    
    // Mettre à jour les templates existants pour leur assigner un user_id par défaut
    // Pour les templates sans user_id ou avec user_id = 0, on les assigne à l'utilisateur actuel
    // (en production, il faudrait une logique plus sophistiquée)
    $current_user_id = get_current_user_id();
    if ($current_user_id > 0) {
        $wpdb->query($wpdb->prepare(
            "UPDATE `$table_templates` SET user_id = %d WHERE user_id IS NULL OR user_id = 0",
            $current_user_id
        ));
    }
}

/**
 * Vérifier les mises à jour de base de données
 */
function pdf_builder_check_database_updates() {
    // Vérifier la version actuelle
    $current_version = get_option('pdf_builder_version', '1.0.0');
    
    // Si la version est inférieure à 1.1.0, mettre à jour le schéma
    if (version_compare($current_version, '1.1.0', '<')) {
        pdf_builder_update_table_schema();
        update_option('pdf_builder_version', '1.1.0');
    }
}

/**
 * Fonction de désactivation
 */
function pdf_builder_deactivate()
{

    delete_option('pdf_builder_activated');
// Clear scheduled expiration check
    if (class_exists('\PDFBuilderPro\License\License_Expiration_Handler')) {
        \PDFBuilderPro\License\License_Expiration_Handler::clear_scheduled_expiration_check();
    }
}

// Charger le plugin de manière standard
if (function_exists('add_action')) {
    add_action('plugins_loaded', 'pdf_builder_init', 1); // Priorité 1 pour charger l'autoloader en premier
    add_action('plugins_loaded', 'pdf_builder_load_textdomain', 1);
}

/**
 * Enregistrer les handlers AJAX
 */
function pdf_builder_register_ajax_handlers() {
    // Le nouveau système AJAX gère maintenant tous les handlers
    // Les anciens handlers sont conservés pour la compatibilité mais redirigés vers le nouveau système

    // Handlers principaux gérés par PDF_Builder_Ajax_Handler
    add_action('wp_ajax_nopriv_wp_pdf_preview_image', 'pdf_builder_handle_preview_ajax');
    add_action('wp_ajax_wp_pdf_preview_image', 'pdf_builder_handle_preview_ajax');

    // Handlers de paramètres - maintenant gérés par le nouveau système AJAX
    add_action('wp_ajax_pdf_builder_save_settings', 'pdf_builder_save_settings_ajax');
    add_action('wp_ajax_pdf_builder_save_all_settings', 'pdf_builder_ajax_handler_dispatch');
    add_action('wp_ajax_pdf_builder_get_fresh_nonce', 'pdf_builder_get_fresh_nonce_ajax');

    // Handlers de cache - maintenant gérés par le système de cache intelligent
    add_action('wp_ajax_pdf_builder_get_cache_status', 'pdf_builder_get_cache_status_ajax');
    add_action('wp_ajax_pdf_builder_test_cache', 'pdf_builder_test_cache_ajax');
    add_action('wp_ajax_pdf_builder_test_cache_integration', 'pdf_builder_test_cache_ajax');
    add_action('wp_ajax_pdf_builder_clear_all_cache', 'pdf_builder_clear_cache_ajax');
    add_action('wp_ajax_pdf_builder_get_cache_metrics', 'pdf_builder_ajax_handler_dispatch');
    add_action('wp_ajax_pdf_builder_update_cache_metrics', 'pdf_builder_ajax_handler_dispatch');

    // Handlers de maintenance - maintenant gérés par les systèmes appropriés
    add_action('wp_ajax_pdf_builder_optimize_database', 'pdf_builder_ajax_handler_dispatch');
    add_action('wp_ajax_pdf_builder_repair_templates', 'pdf_builder_ajax_handler_dispatch');
    add_action('wp_ajax_pdf_builder_remove_temp_files', 'pdf_builder_ajax_handler_dispatch');

    // Handlers de sauvegarde - maintenant gérés par le système de sauvegarde
    add_action('wp_ajax_pdf_builder_create_backup', 'pdf_builder_create_backup_ajax');
    add_action('wp_ajax_pdf_builder_list_backups', 'pdf_builder_list_backups_ajax');
    add_action('wp_ajax_pdf_builder_restore_backup', 'pdf_builder_restore_backup_ajax');
    add_action('wp_ajax_pdf_builder_delete_backup', 'pdf_builder_ajax_handler_dispatch');

    // Handlers de licence - maintenant gérés par le gestionnaire de licences
    add_action('wp_ajax_pdf_builder_test_license', 'pdf_builder_ajax_handler_dispatch');

    // Handlers de routes - maintenant gérés par le système de diagnostic
    add_action('wp_ajax_pdf_builder_test_routes', 'pdf_builder_ajax_handler_dispatch');

    // Handlers de diagnostic - maintenant gérés par l'outil de diagnostic
    add_action('wp_ajax_pdf_builder_export_diagnostic', 'pdf_builder_ajax_handler_dispatch');
    add_action('wp_ajax_pdf_builder_view_logs', 'pdf_builder_ajax_handler_dispatch');

    // Handlers de templates - maintenant gérés par le système de gestion de templates
    add_action('wp_ajax_pdf_builder_save_template', 'pdf_builder_save_template_handler');
    add_action('wp_ajax_pdf_builder_load_template', 'pdf_builder_load_template_handler');
    add_action('wp_ajax_pdf_builder_auto_save_template', 'pdf_builder_auto_save_template_handler');
    add_action('wp_ajax_pdf_builder_load_template_settings', 'pdf_builder_load_template_settings_handler');
    add_action('wp_ajax_pdf_builder_save_template_settings', 'pdf_builder_save_template_settings_handler');
    add_action('wp_ajax_pdf_builder_delete_template', 'pdf_builder_delete_template_handler');
    add_action('wp_ajax_pdf_builder_set_default_template', 'pdf_builder_set_default_template_handler');
    add_action('wp_ajax_pdf_builder_duplicate_template', 'pdf_builder_duplicate_template_handler');
    add_action('wp_ajax_pdf_builder_load_predefined_into_editor', 'pdf_builder_load_predefined_into_editor_handler');
    add_action('wp_ajax_pdf_builder_check_template_limit', 'pdf_builder_check_template_limit_handler');

    // Test AJAX handler
    add_action('wp_ajax_test_ajax', 'pdf_builder_test_ajax_handler');
    add_action('wp_ajax_pdf_builder_test_ajax', 'pdf_builder_test_ajax_handler');

    // Actions programmées
    add_action('pdf_builder_daily_backup', 'pdf_builder_execute_daily_backup');
    add_action('pdf_builder_cleanup_old_backups', 'pdf_builder_cleanup_old_backups');
    add_action('pdf_builder_weekly_maintenance', 'pdf_builder_execute_weekly_maintenance');
    add_action('admin_action_pdf_builder_download_backup', 'pdf_builder_download_backup');
}

/**
 * Dispatcher pour les nouveaux handlers AJAX
 */
function pdf_builder_ajax_handler_dispatch() {
    try {
        $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';

        if (empty($action)) {
            wp_send_json_error('Action manquante');
            return;
        }

        // Dispatcher vers le handler approprié selon l'action
        switch ($action) {
            case 'pdf_builder_save_settings':
                pdf_builder_save_settings_ajax();
                break;
            case 'pdf_builder_save_all_settings':
                pdf_builder_save_all_settings_handler();
                break;
            case 'pdf_builder_get_fresh_nonce':
                pdf_builder_get_fresh_nonce_ajax();
                break;
            case 'pdf_builder_get_cache_status':
                pdf_builder_get_cache_status_ajax();
                break;
            case 'pdf_builder_test_cache':
                pdf_builder_test_cache_ajax();
                break;
            case 'pdf_builder_test_cache_integration':
                pdf_builder_test_cache_ajax();
                break;
            case 'pdf_builder_clear_all_cache':
                pdf_builder_clear_cache_handler();
                break;
            case 'pdf_builder_get_cache_metrics':
                pdf_builder_get_cache_metrics_handler();
                break;
            case 'pdf_builder_update_cache_metrics':
                wp_send_json_error('Handler not implemented - use settings-ajax.php');
                break;
            case 'pdf_builder_optimize_database':
                // This function doesn't exist, let's create a simple fallback
                wp_send_json_error('Handler not implemented');
                break;
            case 'pdf_builder_repair_templates':
                // This function doesn't exist, let's create a simple fallback
                wp_send_json_error('Handler not implemented');
                break;
            case 'pdf_builder_remove_temp_files':
                // This function doesn't exist, let's create a simple fallback
                wp_send_json_error('Handler not implemented');
                break;
            case 'pdf_builder_create_backup':
                pdf_builder_create_backup_ajax();
                break;
            case 'pdf_builder_list_backups':
                pdf_builder_list_backups_ajax();
                break;
            case 'pdf_builder_restore_backup':
                pdf_builder_restore_backup_ajax();
                break;
            case 'pdf_builder_delete_backup':
                // This function doesn't exist, let's create a simple fallback
                wp_send_json_error('Handler not implemented');
                break;
            case 'pdf_builder_test_license':
                wp_send_json_error('Handler not implemented - use settings-ajax.php');
                break;
            case 'pdf_builder_test_routes':
                wp_send_json_error('Handler not implemented - use settings-ajax.php');
                break;
            case 'pdf_builder_export_diagnostic':
                pdf_builder_export_diagnostic_handler();
                break;
            case 'pdf_builder_view_logs':
                pdf_builder_view_logs_handler();
                break;
            case 'pdf_builder_save_template':
                pdf_builder_save_template_handler();
                break;
            case 'pdf_builder_load_template':
                pdf_builder_load_template_handler();
                break;
            case 'pdf_builder_auto_save_template':
                pdf_builder_auto_save_template_handler();
                break;
            case 'pdf_builder_load_template_settings':
                pdf_builder_load_template_settings_handler();
                break;
            case 'pdf_builder_save_template_settings':
                pdf_builder_save_template_settings_handler();
                break;
            case 'pdf_builder_delete_template':
                pdf_builder_delete_template_handler();
                break;
            case 'pdf_builder_set_default_template':
                pdf_builder_set_default_template_handler();
                break;
            case 'pdf_builder_duplicate_template':
                pdf_builder_duplicate_template_handler();
                break;
            case 'pdf_builder_load_predefined_into_editor':
                pdf_builder_load_predefined_into_editor_handler();
                break;
            case 'pdf_builder_check_template_limit':
                pdf_builder_check_template_limit_handler();
                break;
            default:
                wp_send_json_error('Action non reconnue: ' . $action);
                break;
        }

    } catch (Exception $e) {
        error_log('PDF Builder AJAX Error: ' . $e->getMessage());
        wp_send_json_error('Erreur interne du serveur');
    }
}

/**
 * Handler pour admin_post AJAX (fallback)
 */
function pdf_builder_handle_admin_post_ajax() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_die('Accès refusé');
    }

    $action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '';
    $data = isset($_POST['data']) ? $_POST['data'] : array();
    $response = array('success' => false);

    if ($action === 'pdf_builder_test_ajax') {
        $response = array('success' => true, 'message' => 'admin_post AJAX works!');
    }

    wp_send_json($response);
}

/**
 * Handler AJAX pour test de connectivité
 */
function pdf_builder_test_ajax_handler() {
    // Vérifier le nonce si fourni
    if (isset($_POST['nonce']) && !wp_verify_nonce($_POST['nonce'], 'pdf_builder_wizard')) {
        wp_send_json_error('Nonce invalide');
        return;
    }

    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    wp_send_json_success(array(
        'message' => 'AJAX connection successful',
        'timestamp' => current_time('mysql'),
        'user_id' => get_current_user_id()
    ));
}



/**
 * Initialiser le plugin
 */
function pdf_builder_init()
{

    // Vérifier que WordPress est prêt
    if (!function_exists('get_option') || !defined('ABSPATH')) {
        return;
    }

    // Charger l'autoloader Composer
    $autoload_path = plugin_dir_path(__FILE__) . 'vendor/autoload.php';
    if (file_exists($autoload_path)) {
        require_once $autoload_path;
    }

    // Initialiser notre autoloader personnalisé
    require_once plugin_dir_path(__FILE__) . 'core/autoloader.php';
    if (class_exists('PDF_Builder\Core\PdfBuilderAutoloader')) {
        \PDF_Builder\Core\PdfBuilderAutoloader::init(plugin_dir_path(__FILE__));
    }

    // Vérifier et créer les tables manquantes
    pdf_builder_check_tables();

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

    // Enregistrer les handlers AJAX au hook init
    add_action('init', 'pdf_builder_register_ajax_handlers');

    // Initialiser les sauvegardes automatiques
    add_action('init', 'pdf_builder_init_auto_backup');

    // Vérifier les mises à jour de schéma de base de données
    add_action('admin_init', 'pdf_builder_check_database_updates');

    // Les nouveaux systèmes avancés sont maintenant initialisés dans le hook plugins_loaded
    // ci-dessus, donc nous n'avons plus besoin de charger individuellement le moniteur de performance

    // Tools for development/tests removed from production bootstrap

    // Charger le moniteur de performance (maintenant géré par le système avancé)
    $performance_monitor_path = plugin_dir_path(__FILE__) . 'src/Managers/PDF_Builder_Performance_Monitor.php';
    if (file_exists($performance_monitor_path)) {
        require_once $performance_monitor_path;
        // Le moniteur de performance est maintenant initialisé via PDF_Builder_Health_Monitor
    }
}

/**
 * Ajouter les headers de cache pour les assets du plugin
 */
function pdf_builder_add_asset_cache_headers()
{

    // Vérifier si le cache est activé dans les paramètres
    $settings = get_option('pdf_builder_settings', []);
    $cache_enabled = $settings['cache_enabled'] ?? false;
// Si le cache est désactivé, ne pas ajouter de headers de cache
    if (!$cache_enabled) {
// Headers pour désactiver complètement le cache
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        return;
    }

    // Headers de cache pour les assets du plugin (1 semaine)
    $cache_time = 604800; // 7 jours en secondes

    // Pour les assets JavaScript
    if (
        isset($_SERVER['REQUEST_URI']) &&
        (strpos($_SERVER['REQUEST_URI'], '/wp-content/plugins/wp-pdf-builder-pro/assets/js/') !== false ||
         strpos($_SERVER['REQUEST_URI'], '/wp-content/plugins/wp-pdf-builder-pro/assets/css/') !== false)
    ) {
// Headers de cache
        header('Cache-Control: public, max-age=' . $cache_time);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cache_time) . ' GMT');
        header('ETag: "' . md5($_SERVER['REQUEST_URI'] . filemtime($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI'])) . '"');

        // Compression si supportée
        if (
            isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
            strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false &&
            strpos($_SERVER['REQUEST_URI'], '.gz') !== false
        ) {
            header('Content-Encoding: gzip');
        }
    }
}

// Gérer les téléchargements PDF en frontend
if (function_exists('add_action')) {
    add_action('init', 'pdf_builder_handle_pdf_downloads');
}
// AJAX handlers supprimés - maintenant gérés dans pdf_builder_register_ajax_handlers()

/**
 * Charger le plugin pour les requêtes AJAX
 */
function pdf_builder_load_for_ajax()
{

    // Vérifier que WordPress est prêt
    if (!function_exists('get_option') || !defined('ABSPATH')) {
        return;
    }

    // Charger le bootstrap pour les requêtes AJAX
    $bootstrap_path = plugin_dir_path(__FILE__) . 'bootstrap.php';
    if (file_exists($bootstrap_path)) {
        require_once $bootstrap_path;
    // Pour les requêtes AJAX, nous chargeons juste le bootstrap
        // sans initialiser complètement le plugin
        if (function_exists('pdf_builder_load_bootstrap')) {
            pdf_builder_load_bootstrap();
        }
    }
}
function pdf_builder_handle_preview_ajax()
{
    // Charger le bootstrap
    pdf_builder_load_for_ajax();
    // Le bootstrap a instancié PreviewImageAPI qui a re-enregistré les actions AJAX.
    // Maintenant, appelons directement la méthode generatePreview si PreviewImageAPI existe
    if (class_exists('PDF_Builder\\Api\\PreviewImageAPI')) {
        // Créer une nouvelle instance et appeler generatePreview directement
        $api = new \PDF_Builder\Api\PreviewImageAPI();
        $api->generatePreview();
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
 * Charger le domaine de traduction
 */
function pdf_builder_load_textdomain()
{

    load_plugin_textdomain('pdf-builder-pro', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

/**
 * Gérer les téléchargements PDF
 */
function pdf_builder_handle_pdf_downloads()
{

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
 * Handler AJAX pour sauvegarder les paramètres
 */
function pdf_builder_save_settings_ajax() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permissions insuffisantes'));
        return;
    }

    // Vérifier le nonce (depuis l'en-tête HTTP ou POST)
    $nonce_valid = false;
    if (isset($_SERVER['HTTP_X_WP_NONCE'])) {
        $nonce_valid = wp_verify_nonce($_SERVER['HTTP_X_WP_NONCE'], 'pdf_builder_ajax');
    } elseif (isset($_POST['nonce'])) {
        $nonce_valid = wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax');
    }

    if (!$nonce_valid) {
        wp_send_json_error('Nonce invalide');
        return;
    }

    $current_tab = sanitize_text_field($_POST['tab'] ?? 'all');
    $saved_count = 0;

    // Si current_tab est 'all', sauvegarder tous les paramètres
    if ($current_tab === 'all') {
        // Collecter tous les paramètres possibles
        $all_settings = array(
            // Général
            'company_phone_manual' => sanitize_text_field($_POST['company_phone_manual'] ?? ''),
            'company_siret' => sanitize_text_field($_POST['company_siret'] ?? ''),
            'company_vat' => sanitize_text_field($_POST['company_vat'] ?? ''),
            'company_rcs' => sanitize_text_field($_POST['company_rcs'] ?? ''),
            'company_capital' => sanitize_text_field($_POST['company_capital'] ?? ''),

            // Licence
            'license_test_mode' => isset($_POST['license_test_mode']) ? '1' : '0',

            // Système - Cache
            'cache_enabled' => !empty($_POST['cache_enabled']) ? '1' : '0',
            'cache_ttl' => intval($_POST['cache_ttl'] ?? 3600),
            'cache_compression' => !empty($_POST['cache_compression']) ? '1' : '0',
            'cache_auto_cleanup' => !empty($_POST['cache_auto_cleanup']) ? '1' : '0',
            'cache_max_size' => intval($_POST['cache_max_size'] ?? 100),

            // Système - Maintenance
            'auto_maintenance' => !empty($_POST['systeme_auto_maintenance']) ? '1' : '0',

            // Système - Sauvegarde
            'auto_backup' => !empty($_POST['systeme_auto_backup']) ? '1' : '0',
            'auto_backup_frequency' => sanitize_text_field($_POST['systeme_auto_backup_frequency'] ?? 'daily'),
            'backup_retention' => intval($_POST['systeme_backup_retention'] ?? 30),

            // Accès - Rôles autorisés
            'allowed_roles' => isset($_POST['pdf_builder_allowed_roles']) ? array_map('sanitize_text_field', (array) $_POST['pdf_builder_allowed_roles']) : ['administrator'],

            // Sécurité
            'security_level' => sanitize_text_field($_POST['security_level'] ?? 'medium'),
            'enable_logging' => !empty($_POST['enable_logging']) ? '1' : '0',

            // RGPD
            'gdpr_enabled' => !empty($_POST['gdpr_enabled']) ? '1' : '0',
            'gdpr_consent_required' => !empty($_POST['gdpr_consent_required']) ? '1' : '0',
            'gdpr_data_retention' => intval($_POST['gdpr_data_retention'] ?? 2555),
            'gdpr_audit_enabled' => !empty($_POST['gdpr_audit_enabled']) ? '1' : '0',
            'gdpr_encryption_enabled' => !empty($_POST['gdpr_encryption_enabled']) ? '1' : '0',
            'gdpr_consent_analytics' => !empty($_POST['gdpr_consent_analytics']) ? '1' : '0',
            'gdpr_consent_templates' => !empty($_POST['gdpr_consent_templates']) ? '1' : '0',
            'gdpr_consent_marketing' => !empty($_POST['gdpr_consent_marketing']) ? '1' : '0',
        );

        foreach ($all_settings as $key => $value) {
            if ($key === 'allowed_roles') {
                // Sauvegarde spéciale pour les rôles
                update_option('pdf_builder_allowed_roles', $value);
            } else {
                update_option('pdf_builder_' . $key, $value);
            }
        }
        $saved_count = count($all_settings);

    } else {
        // Traiter selon l'onglet (code existant)
        switch ($current_tab) {
        case 'general':
            // Pour l'AJAX, on utilise le nonce principal pdf_builder_ajax
            // Sauvegarder les paramètres généraux
            $settings = array(
                'cache_enabled' => isset($_POST['cache_enabled']) ? '1' : '0',
                'cache_ttl' => intval($_POST['cache_ttl']),
                'cache_compression' => isset($_POST['cache_compression']) ? '1' : '0',
                'cache_auto_cleanup' => isset($_POST['cache_auto_cleanup']) ? '1' : '0',
                'cache_max_size' => intval($_POST['cache_max_size'] ?? 100),
                'company_phone_manual' => sanitize_text_field($_POST['company_phone_manual'] ?? ''),
                'company_siret' => sanitize_text_field($_POST['company_siret'] ?? ''),
                'company_vat' => sanitize_text_field($_POST['company_vat'] ?? ''),
                'company_rcs' => sanitize_text_field($_POST['company_rcs'] ?? ''),
                'company_capital' => sanitize_text_field($_POST['company_capital'] ?? ''),
                'pdf_quality' => sanitize_text_field($_POST['pdf_quality'] ?? 'high'),
                'default_format' => sanitize_text_field($_POST['default_format'] ?? 'A4'),
                'default_orientation' => sanitize_text_field($_POST['default_orientation'] ?? 'portrait'),
            );

            foreach ($settings as $key => $value) {
                update_option('pdf_builder_' . $key, $value);
            }
            $saved_count++;
            break;

        case 'performance':
            // Sauvegarder les paramètres performance
            $settings = array(
                'cache_enabled' => isset($_POST['cache_enabled']) ? '1' : '0',
                'cache_expiry' => intval($_POST['cache_expiry']),
                'compression_enabled' => isset($_POST['compression_enabled']) ? '1' : '0',
                'lazy_loading' => isset($_POST['lazy_loading']) ? '1' : '0',
                'preload_resources' => isset($_POST['preload_resources']) ? '1' : '0',
            );

            foreach ($settings as $key => $value) {
                update_option('pdf_builder_' . $key, $value);
            }
            $saved_count++;
            break;

        case 'maintenance':
            // Sauvegarder les paramètres maintenance
            $settings = array(
                'auto_cleanup' => isset($_POST['auto_cleanup']) ? '1' : '0',
                'cleanup_interval' => sanitize_text_field($_POST['cleanup_interval']),
                'log_retention' => intval($_POST['log_retention']),
                'backup_enabled' => isset($_POST['backup_enabled']) ? '1' : '0',
            );

            foreach ($settings as $key => $value) {
                update_option('pdf_builder_' . $key, $value);
            }
            $saved_count++;
            break;

        case 'sauvegarde':
            // Sauvegarder les paramètres sauvegarde
            $settings = array(
                'auto_backup' => isset($_POST['auto_backup']) ? '1' : '0',
                'backup_frequency' => sanitize_text_field($_POST['backup_frequency']),
                'backup_retention' => intval($_POST['backup_retention']),
                'cloud_backup' => isset($_POST['cloud_backup']) ? '1' : '0',
            );

            foreach ($settings as $key => $value) {
                update_option('pdf_builder_' . $key, $value);
            }
            $saved_count++;
            break;

        case 'acces':
            // Sauvegarder les rôles
            $allowed_roles = isset($_POST['pdf_builder_allowed_roles']) ? $_POST['pdf_builder_allowed_roles'] : array();
            update_option('pdf_builder_allowed_roles', $allowed_roles);
            $saved_count++;
            break;

        case 'securite':
            // Sauvegarder les paramètres sécurité
            $settings = array(
                'security_level' => sanitize_text_field($_POST['security_level'] ?? 'medium'),
                'enable_logging' => isset($_POST['enable_logging']) ? '1' : '0',
                'ip_filtering' => isset($_POST['ip_filtering']) ? '1' : '0',
                'rate_limiting' => isset($_POST['rate_limiting']) ? '1' : '0',
                'encryption' => isset($_POST['encryption']) ? '1' : '0',
                'audit_log' => isset($_POST['audit_log']) ? '1' : '0',
            );

            foreach ($settings as $key => $value) {
                update_option('pdf_builder_' . $key, $value);
            }
            $saved_count++;

            // Sauvegarder les paramètres RGPD
            $settings = array(
                'gdpr_enabled' => isset($_POST['gdpr_enabled']) ? '1' : '0',
                'gdpr_consent_required' => isset($_POST['gdpr_consent_required']) ? '1' : '0',
                'gdpr_data_retention' => intval($_POST['gdpr_data_retention']),
                'gdpr_audit_enabled' => isset($_POST['gdpr_audit_enabled']) ? '1' : '0',
                'gdpr_encryption_enabled' => isset($_POST['gdpr_encryption_enabled']) ? '1' : '0',
                'gdpr_consent_analytics' => isset($_POST['gdpr_consent_analytics']) ? '1' : '0',
                'gdpr_consent_templates' => isset($_POST['gdpr_consent_templates']) ? '1' : '0',
                'gdpr_consent_marketing' => isset($_POST['gdpr_consent_marketing']) ? '1' : '0',
            );

            foreach ($settings as $key => $value) {
                update_option('pdf_builder_' . $key, $value);
            }
            $saved_count++;
            break;

        case 'pdf':
            // Sauvegarder les paramètres PDF
            $settings = array(
                'pdf_quality' => sanitize_text_field($_POST['pdf_quality'] ?? 'high'),
                'pdf_page_size' => sanitize_text_field($_POST['pdf_page_size'] ?? 'A4'),
                'pdf_orientation' => sanitize_text_field($_POST['pdf_orientation'] ?? 'portrait'),
                'pdf_cache_enabled' => isset($_POST['pdf_cache_enabled']) ? '1' : '0',
                'pdf_compression' => sanitize_text_field($_POST['pdf_compression'] ?? 'medium'),
                'pdf_metadata_enabled' => isset($_POST['pdf_metadata_enabled']) ? '1' : '0',
                'pdf_print_optimized' => isset($_POST['pdf_print_optimized']) ? '1' : '0',
            );

            foreach ($settings as $key => $value) {
                update_option('pdf_builder_' . $key, $value);
            }
            $saved_count++;
            break;

        case 'contenu':
            // Sauvegarder les paramètres canvas
            $settings = array(
                'canvas_max_size' => intval($_POST['canvas_max_size']),
                'canvas_dpi' => intval($_POST['canvas_dpi']),
                'canvas_format' => sanitize_text_field($_POST['canvas_format']),
                'canvas_quality' => intval($_POST['canvas_quality']),
            );

            foreach ($settings as $key => $value) {
                update_option('pdf_builder_' . $key, $value);
            }
            $saved_count++;

            // Sauvegarder les paramètres templates
            $settings = array(
                'template_library_enabled' => isset($_POST['template_library_enabled']) ? '1' : '0',
                'default_template' => sanitize_text_field($_POST['default_template'] ?? 'blank'),
            );

            foreach ($settings as $key => $value) {
                update_option('pdf_builder_' . $key, $value);
            }
            $saved_count++;
            break;

        case 'developpeur':
            // Sauvegarder les paramètres développeur
            $settings = array(
                'developer_enabled' => $_POST['developer_enabled'] ?? '0',
                'developer_password' => sanitize_text_field($_POST['developer_password'] ?? ''),
                'debug_php_errors' => isset($_POST['debug_php_errors']) ? '1' : '0',
                'debug_javascript' => isset($_POST['debug_javascript']) ? '1' : '0',
                'debug_javascript_verbose' => isset($_POST['debug_javascript_verbose']) ? '1' : '0',
                'debug_ajax' => isset($_POST['debug_ajax']) ? '1' : '0',
                'debug_performance' => isset($_POST['debug_performance']) ? '1' : '0',
                'debug_database' => isset($_POST['debug_database']) ? '1' : '0',
                'log_level' => intval($_POST['log_level'] ?? 3),
                'log_file_size' => intval($_POST['log_file_size'] ?? 10),
                'log_retention' => intval($_POST['log_retention'] ?? 30),
                'license_test_mode' => isset($_POST['license_test_mode']) ? '1' : '0',
                'force_https' => isset($_POST['force_https']) ? '1' : '0',
            );

            foreach ($settings as $key => $value) {
                update_option('pdf_builder_' . $key, $value);
            }
            $saved_count++;
            break;

        case 'systeme':
            // Sauvegarder les paramètres système (performance + maintenance + sauvegarde)
            $settings = array(
                'cache_enabled' => $_POST['cache_enabled'] ?? '0',
                'cache_compression' => $_POST['cache_compression'] ?? '0',
                'cache_auto_cleanup' => $_POST['cache_auto_cleanup'] ?? '0',
                'cache_max_size' => intval($_POST['cache_max_size'] ?? 100),
                'cache_ttl' => intval($_POST['cache_ttl'] ?? 3600),
                'performance_auto_optimization' => isset($_POST['performance_auto_optimization']) ? '1' : '0',
                'auto_maintenance' => $_POST['systeme_auto_maintenance'] ?? '0',
                'auto_backup' => $_POST['systeme_auto_backup'] ?? '0',
                'auto_backup_frequency' => sanitize_text_field($_POST['systeme_auto_backup_frequency'] ?? $_POST['systeme_auto_backup_frequency_hidden'] ?? 'daily'),
                'backup_retention' => intval($_POST['systeme_backup_retention'] ?? 30),
            );

            foreach ($settings as $key => $value) {
                update_option('pdf_builder_' . $key, $value);
            }
            $saved_count++;
            break;

        case 'licence':
            // Sauvegarder les paramètres licence
            $settings = array(
                'license_enable_notifications' => isset($_POST['enable_expiration_notifications']) ? '1' : '0',
            );

            foreach ($settings as $key => $value) {
                update_option('pdf_builder_' . $key, $value);
            }
            $saved_count++;
            break;
    }
    } // End of else block

    if ($saved_count > 0) {
        wp_send_json_success(array(
            'message' => 'Paramètres sauvegardés avec succès',
            'new_nonce' => wp_create_nonce('pdf_builder_ajax')
        ));
    } else {
        wp_send_json_error(array('message' => 'Aucun paramètre sauvegardé'));
    }
}

/**
 * AJAX handler pour récupérer l'état du cache
 */
function pdf_builder_get_cache_status_ajax() {
    // Vérifier le nonce
    if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
        wp_send_json_error('Nonce invalide');
        return;
    }

    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    $cache_enabled = get_option('pdf_builder_cache_enabled', '0');

    wp_send_json_success(array(
        'cache_enabled' => $cache_enabled
    ));
}

/**
 * AJAX handler pour tester le système de cache
 */
function pdf_builder_test_cache_ajax() {
    // Vérifier le nonce
    if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
        wp_send_json_error('Nonce invalide');
        return;
    }

    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    $test_key = sanitize_text_field($_POST['test_key']);
    $test_value = sanitize_text_field($_POST['test_value']);

    $results = array(
        'cache_available' => false,
        'transient_test' => false,
        'cache_status' => 'Cache non testé'
    );

    // Test 1: Vérifier la disponibilité des fonctions de cache
    if (function_exists('wp_cache_flush')) {
        $results['cache_available'] = true;
    }

    // Test 2: Tester les transients WordPress
    $transient_test_key = 'pdf_builder_test_' . time();
    $transient_test_value = 'test_value_' . mt_rand(1000, 9999);

    // Définir un transient
    $set_result = set_transient($transient_test_key, $transient_test_value, 300); // 5 minutes

    if ($set_result) {
        // Récupérer le transient
        $get_result = get_transient($transient_test_key);

        if ($get_result === $transient_test_value) {
            $results['transient_test'] = true;
            $results['cache_status'] = 'Transients WordPress opérationnels';

            // Nettoyer le test
            delete_transient($transient_test_key);
        } else {
            $results['cache_status'] = 'Erreur lors de la récupération du transient';
        }
    } else {
        $results['cache_status'] = 'Impossible de définir un transient';
    }

    // Test 3: Vérifier les options de cache du plugin
    $cache_enabled = get_option('pdf_builder_cache_enabled', false);
    if ($cache_enabled) {
        $results['cache_status'] .= ' | Cache du plugin activé';
    } else {
        $results['cache_status'] .= ' | Cache du plugin désactivé';
    }

    wp_send_json_success(array(
        'message' => 'Test du cache terminé',
        'cache_status' => $results['cache_status'],
        'transient_working' => $results['transient_test'],
        'cache_available' => $results['cache_available']
    ));
}

/**
 * AJAX handler pour obtenir un nouveau nonce frais
 */
function pdf_builder_get_fresh_nonce_ajax() {
    // Vérifier les permissions (pas besoin de nonce ici car on en génère un nouveau)
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Générer un nouveau nonce
    $fresh_nonce = wp_create_nonce('pdf_builder_ajax');

    error_log('PDF Builder AJAX: Generated fresh nonce: ' . substr($fresh_nonce, 0, 10) . '..., User ID: ' . get_current_user_id());

    wp_send_json_success(array(
        'nonce' => $fresh_nonce,
        'generated_at' => current_time('timestamp')
    ));
}

/**
 * AJAX handler pour vider le cache
 */
function pdf_builder_clear_cache_ajax() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Vider le cache WordPress
    wp_cache_flush();

    // Supprimer les transients liés au plugin
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_pdf_builder_%'");

    wp_send_json_success(array(
        'message' => 'Cache vidé avec succès'
    ));
}

/**
 * Handler pour télécharger une sauvegarde
 */
function pdf_builder_download_backup() {
    // Vérifier le nonce
    if (!wp_verify_nonce($_GET['nonce'], 'pdf_builder_ajax')) {
        wp_die('Nonce invalide');
    }

    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_die('Permissions insuffisantes');
    }

    $filename = sanitize_file_name($_GET['filename']);

    // Décoder l'URI encoding (JavaScript fait encodeURIComponent)
    $filename = urldecode($filename);

    // Plus besoin de décodage base64 puisque nous envoyons le filename brut maintenant
    if (empty($filename)) {
        wp_die('Nom de fichier manquant');
    }

    try {
        $backup_dir = WP_CONTENT_DIR . '/pdf-builder-backups/';
        $filepath = $backup_dir . $filename;

        if (!file_exists($filepath)) {
            wp_die('Fichier de sauvegarde introuvable: ' . $filepath);
        }

        if (!is_readable($filepath)) {
            wp_die('Fichier non lisible: ' . $filepath);
        }

        // Nettoyer toute sortie précédente
        if (ob_get_level()) {
            ob_clean();
        }

        // Déterminer le type de fichier et le Content-Type approprié
        $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $content_type = 'application/octet-stream'; // Type par défaut

        if ($file_extension === 'zip') {
            $content_type = 'application/zip';
        } elseif ($file_extension === 'json') {
            $content_type = 'application/json';
        }

        // Définir les headers pour le téléchargement
        header('Content-Type: ' . $content_type);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Désactiver la compression zlib si activée
        if (ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }

        // Lire et envoyer le fichier
        readfile($filepath);
        exit;

    } catch (Exception $e) {
        wp_die('Erreur lors du téléchargement: ' . $e->getMessage());
    }
}

/**
 * Calcule le prochain timestamp pour la sauvegarde automatique selon la fréquence
 */
function pdf_builder_calculate_next_backup_time($frequency) {
    $current_time = current_time('timestamp');

    switch ($frequency) {
        case 'daily':
            // Prochaine exécution demain à 02:00
            return strtotime('tomorrow 02:00:00');

        case 'weekly':
            // Prochaine exécution dimanche à 02:00
            $days_until_sunday = (7 - date('w', $current_time)) % 7;
            if ($days_until_sunday == 0 && date('H', $current_time) >= 2) {
                // Si c'est dimanche et qu'il est déjà 02:00 ou plus, programmer pour dimanche prochain
                $days_until_sunday = 7;
            } elseif ($days_until_sunday == 0) {
                // Si c'est dimanche avant 02:00, programmer pour aujourd'hui
                $days_until_sunday = 0;
            }
            return strtotime('+' . $days_until_sunday . ' days 02:00:00');

        case 'monthly':
            // Prochaine exécution le 1er du mois à 02:00
            $current_month = date('m', $current_time);
            $current_year = date('Y', $current_time);
            $current_day = date('d', $current_time);
            $current_hour = date('H', $current_time);

            // Si c'est le 1er du mois et qu'il est avant 02:00, programmer pour aujourd'hui
            if ($current_day == 1 && $current_hour < 2) {
                return strtotime($current_year . '-' . $current_month . '-01 02:00:00');
            }

            // Sinon, programmer pour le 1er du mois prochain
            $next_month = $current_month + 1;
            $next_year = $current_year;
            if ($next_month > 12) {
                $next_month = 1;
                $next_year++;
            }
            return strtotime($next_year . '-' . str_pad($next_month, 2, '0', STR_PAD_LEFT) . '-01 02:00:00');

        default:
            // Par défaut, quotidien
            return strtotime('tomorrow 02:00:00');
    }
}

/**
 * Réinitialise les sauvegardes automatiques (désactive et reprogramme avec nouvelle fréquence)
 */
function pdf_builder_reinit_auto_backup() {
    // Désactiver l'ancien cron s'il existe
    $timestamp = wp_next_scheduled('pdf_builder_daily_backup');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'pdf_builder_daily_backup');
    }

    // Réinitialiser avec la nouvelle configuration
    pdf_builder_init_auto_backup();
}

/**
 * Initialiser les sauvegardes automatiques
 */
function pdf_builder_init_auto_backup() {
    // Vérifier si les sauvegardes automatiques sont activées
    $auto_backup_enabled = get_option('pdf_builder_auto_backup', '0');
    $auto_backup_frequency = get_option('pdf_builder_auto_backup_frequency', 'daily');

    // Mapping des fréquences vers les intervalles WordPress
    $frequency_mapping = array(
        'daily' => 'daily',
        'weekly' => 'weekly',
        'monthly' => 'monthly'
    );

    $wp_schedule = isset($frequency_mapping[$auto_backup_frequency]) ? $frequency_mapping[$auto_backup_frequency] : 'daily';

    if ($auto_backup_enabled === '1') {
        // Programmer la sauvegarde automatique selon la fréquence choisie
        if (!wp_next_scheduled('pdf_builder_daily_backup')) {
            // Calculer le prochain timestamp selon la fréquence
            $next_timestamp = pdf_builder_calculate_next_backup_time($auto_backup_frequency);
            wp_schedule_event($next_timestamp, $wp_schedule, 'pdf_builder_daily_backup');
        }
    } else {
        // Désactiver la sauvegarde automatique si elle était programmée
        $timestamp = wp_next_scheduled('pdf_builder_daily_backup');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'pdf_builder_daily_backup');
        }
    }

    // Programmer le nettoyage automatique des anciennes sauvegardes
    if (!wp_next_scheduled('pdf_builder_cleanup_old_backups')) {
        wp_schedule_event(strtotime('tomorrow 03:00:00'), 'daily', 'pdf_builder_cleanup_old_backups');
    }

    // Programmer la maintenance automatique hebdomadaire
    $auto_maintenance_enabled = get_option('pdf_builder_auto_maintenance', '0');
    if ($auto_maintenance_enabled === '1') {
        if (!wp_next_scheduled('pdf_builder_weekly_maintenance')) {
            // Programmer pour le prochain dimanche à 02:00
            $next_sunday = strtotime('next Sunday 02:00:00');
            if ($next_sunday < current_time('timestamp')) {
                $next_sunday = strtotime('next Sunday 02:00:00', strtotime('+1 week'));
            }
            wp_schedule_event($next_sunday, 'weekly', 'pdf_builder_weekly_maintenance');
        }
    } else {
        // Désactiver la maintenance automatique si elle était programmée
        $timestamp = wp_next_scheduled('pdf_builder_weekly_maintenance');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'pdf_builder_weekly_maintenance');
        }
    }
}

/**
 * Exécuter la sauvegarde automatique quotidienne
 */
function pdf_builder_execute_daily_backup() {
    try {
        // Créer le dossier de sauvegarde s'il n'existe pas
        $backup_dir = WP_CONTENT_DIR . '/pdf-builder-backups';
        if (!file_exists($backup_dir)) {
            if (!wp_mkdir_p($backup_dir)) {
                return;
            }
        }

        // Récupérer toutes les options du plugin
        global $wpdb;
        $options = $wpdb->get_results(
            $wpdb->prepare("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s", 'pdf_builder_%'),
            ARRAY_A
        );

        // Créer le nom du fichier de sauvegarde avec timezone
        $timestamp = current_time('timestamp');
        $filename = 'pdf_builder_auto_backup_' . wp_date('Y-m-d_H-i-s', $timestamp) . '.json';
        $filepath = $backup_dir . '/' . $filename;

        // Préparer les données de sauvegarde
        $backup_data = array(
            'version' => '1.0',
            'timestamp' => $timestamp,
            'date' => wp_date('Y-m-d H:i:s', $timestamp),
            'timezone' => wp_timezone_string(),
            'type' => 'automatic',
            'options' => array()
        );

        foreach ($options as $option) {
            $backup_data['options'][$option['option_name']] = maybe_unserialize($option['option_value']);
        }

        // Écrire le fichier de sauvegarde
        if (file_put_contents($filepath, json_encode($backup_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        } else {
        }

    } catch (Exception $e) {
    }
}

/**
 * Nettoyer les anciennes sauvegardes automatiquement
 */
function pdf_builder_cleanup_old_backups() {
    try {
        $backup_dir = WP_CONTENT_DIR . '/pdf-builder-backups';
        $retention_days = intval(get_option('pdf_builder_backup_retention', 30));

        if (!file_exists($backup_dir) || !is_dir($backup_dir)) {
            return;
        }

        $files = glob($backup_dir . '/pdf_builder_backup_*.json');
        $now = current_time('timestamp');
        $deleted_count = 0;

        foreach ($files as $file) {
            $file_timestamp = filemtime($file);
            $age_days = ($now - $file_timestamp) / (60 * 60 * 24);

            if ($age_days > $retention_days) {
                if (unlink($file)) {
                    $deleted_count++;
                }
            }
        }

    } catch (Exception $e) {
    }
}

/**
 * Exécuter la maintenance automatique hebdomadaire
 */
function pdf_builder_execute_weekly_maintenance() {
    // Vérifier si la maintenance automatique est activée
    $auto_maintenance_enabled = get_option('pdf_builder_auto_maintenance', '0');
    $performance_optimization_enabled = get_option('pdf_builder_performance_auto_optimization', '0');

    if ($auto_maintenance_enabled !== '1' && $performance_optimization_enabled !== '1') {
        return; // Aucune maintenance automatique activée
    }

    try {
        // Log de début de maintenance
        

        // 1. Optimiser la base de données (si optimisation performance activée)
        if ($performance_optimization_enabled === '1') {
            pdf_builder_auto_optimize_db();
        }

        // 2. Réparer les templates (si optimisation performance activée)
        if ($performance_optimization_enabled === '1') {
            pdf_builder_auto_repair_templates();
        }

        // 3. Supprimer les fichiers temporaires (maintenance générale)
        if ($auto_maintenance_enabled === '1') {
            pdf_builder_auto_remove_temp_files();
        }

        // 4. Nettoyer le cache (maintenance générale)
        if ($auto_maintenance_enabled === '1') {
            pdf_builder_auto_clear_cache();
        }

        // Log de fin de maintenance
        

    } catch (Exception $e) {
        
    }
}

/**
 * Optimisation automatique de la base de données
 */
function pdf_builder_auto_optimize_db() {
    try {
        global $wpdb;
        $tables = [
            $wpdb->posts,
            $wpdb->postmeta,
            $wpdb->prefix . 'woocommerce_order_items',
            $wpdb->prefix . 'woocommerce_order_itemmeta'
        ];

        foreach ($tables as $table) {
            $wpdb->query("OPTIMIZE TABLE {$table}");
            $wpdb->query("REPAIR TABLE {$table}");
        }

        
    } catch (Exception $e) {
        
    }
}

/**
 * Réparation automatique des templates
 */
function pdf_builder_auto_repair_templates() {
    try {
        global $wpdb;

        // Vérifier et réparer les tables des templates
        $tables = ['pdf_builder_templates', 'pdf_builder_pdfs'];
        foreach ($tables as $table) {
            $result = $wpdb->get_row("CHECK TABLE {$wpdb->prefix}{$table}");
            if ($result && $result->Msg_text !== 'OK') {
                $wpdb->query("REPAIR TABLE {$wpdb->prefix}{$table}");
            }
        }

        // Vérifier l'accès aux options
        $settings = get_option('pdf_builder_settings', []);
        if (!is_array($settings)) {
            update_option('pdf_builder_settings', []);
        }

        
    } catch (Exception $e) {
        
    }
}

/**
 * Suppression automatique des fichiers temporaires
 */
function pdf_builder_auto_remove_temp_files() {
    try {
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/pdf-builder-temp';

        if (file_exists($temp_dir) && is_dir($temp_dir)) {
            // Supprimer les fichiers temporaires de plus de 24h
            $files = glob($temp_dir . '/*');
            $now = current_time('timestamp');
            $deleted_count = 0;

            foreach ($files as $file) {
                if (is_file($file)) {
                    $file_age = $now - filemtime($file);
                    if ($file_age > 86400) { // 24 heures
                        if (unlink($file)) {
                            $deleted_count++;
                        }
                    }
                }
            }

            
        }
    } catch (Exception $e) {
        
    }
}

/**
 * Nettoyage automatique du cache
 */
function pdf_builder_auto_clear_cache() {
    try {
        // Nettoyer le cache principal
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }

        // Nettoyer les caches spécifiques du plugin
        $cache_dirs = [
            WP_CONTENT_DIR . '/cache/pdf-builder',
            WP_CONTENT_DIR . '/cache/pdf-builder-preview'
        ];

        foreach ($cache_dirs as $cache_dir) {
            if (file_exists($cache_dir) && is_dir($cache_dir)) {
                $files = glob($cache_dir . '/*');
                $deleted_count = 0;

                foreach ($files as $file) {
                    if (is_file($file)) {
                        $file_age = current_time('timestamp') - filemtime($file);
                        if ($file_age > 604800) { // 7 jours
                            if (unlink($file)) {
                                $deleted_count++;
                            }
                        }
                    }
                }

                
            }
        }

        
    } catch (Exception $e) {
        
    }
}

/**
 * AJAX handler for saving templates (React frontend)
 */
function pdf_builder_save_template_handler() {
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        $template_id = intval($_POST['template_id'] ?? 0);
        $template_data = isset($_POST['template_data']) ? $_POST['template_data'] : '';

        if (!$template_id || empty($template_data)) {
            wp_send_json_error('Données manquantes');
            return;
        }

        // Decode and validate JSON
        $decoded_data = json_decode($template_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('Données JSON invalides');
            return;
        }

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // Check if template exists
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_templates WHERE id = %d", $template_id));
        if (!$existing) {
            wp_send_json_error('Template non trouvé');
            return;
        }

        // Update template
        $result = $wpdb->update(
            $table_templates,
            [
                'template_data' => $template_data,
                'updated_at' => current_time('mysql')
            ],
            ['id' => $template_id],
            ['%s', '%s'],
            ['%d']
        );

        if ($result === false) {
            wp_send_json_error('Erreur lors de la sauvegarde');
            return;
        }

        wp_send_json_success([
            'message' => 'Template sauvegardé avec succès',
            'template_id' => $template_id
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la sauvegarde: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for loading templates (React frontend)
 */
function pdf_builder_load_template_handler() {
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        $template_id = intval($_POST['template_id'] ?? 0);

        if (!$template_id) {
            wp_send_json_error('ID du template manquant');
            return;
        }

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
            ARRAY_A
        );

        if (!$template) {
            wp_send_json_error('Template non trouvé');
            return;
        }

        $template_data = json_decode($template['template_data'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('Erreur de décodage JSON');
            return;
        }

        wp_send_json_success([
            'template' => $template_data,
            'id' => $template['id'],
            'name' => $template['name']
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors du chargement: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for auto-saving templates (React frontend)
 */
function pdf_builder_auto_save_template_handler() {
    // Same logic as save_template_handler but for auto-save
    pdf_builder_save_template_handler();
}

/**
 * AJAX handler for loading template settings (templates page)
 */
function pdf_builder_load_template_settings_handler() {
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        $template_id = intval($_POST['template_id'] ?? 0);

        if (!$template_id) {
            wp_send_json_error('ID du template manquant');
            return;
        }

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
            ARRAY_A
        );

        if (!$template) {
            wp_send_json_error('Template non trouvé');
            return;
        }

        // Decode template data to get settings
        $template_data = json_decode($template['template_data'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $template_data = [];
        }

        wp_send_json_success([
            'name' => $template['name'],
            'description' => $template_data['description'] ?? '',
            'category' => $template_data['category'] ?? 'autre',
            'is_public' => $template_data['is_public'] ?? false,
            'paper_size' => $template_data['paper_size'] ?? 'A4',
            'orientation' => $template_data['orientation'] ?? 'portrait'
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors du chargement: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for saving template settings (templates page)
 */
function pdf_builder_save_template_settings_handler() {
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        $template_id = intval($_POST['template_id'] ?? 0);
        $name = sanitize_text_field($_POST['name'] ?? '');
        $description = sanitize_text_field($_POST['description'] ?? '');
        $category = sanitize_text_field($_POST['category'] ?? 'autre');
        $is_public = intval($_POST['is_public'] ?? 0);
        $paper_size = sanitize_text_field($_POST['paper_size'] ?? 'A4');
        $orientation = sanitize_text_field($_POST['orientation'] ?? 'portrait');

        if (!$template_id || empty($name)) {
            wp_send_json_error('Données manquantes');
            return;
        }

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // Check if template exists
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_templates WHERE id = %d", $template_id));
        if (!$existing) {
            wp_send_json_error('Template non trouvé');
            return;
        }

        // Get current template data
        $current_template = $wpdb->get_row(
            $wpdb->prepare("SELECT template_data FROM $table_templates WHERE id = %d", $template_id),
            ARRAY_A
        );

        $template_data = json_decode($current_template['template_data'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $template_data = [];
        }

        // Update settings in template data
        $template_data['description'] = $description;
        $template_data['category'] = $category;
        $template_data['is_public'] = $is_public;
        $template_data['paper_size'] = $paper_size;
        $template_data['orientation'] = $orientation;

        // Update template
        $result = $wpdb->update(
            $table_templates,
            [
                'name' => $name,
                'template_data' => json_encode($template_data),
                'updated_at' => current_time('mysql')
            ],
            ['id' => $template_id],
            ['%s', '%s', '%s'],
            ['%d']
        );

        if ($result === false) {
            wp_send_json_error('Erreur lors de la sauvegarde');
            return;
        }

        wp_send_json_success([
            'message' => 'Paramètres du template sauvegardés avec succès'
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la sauvegarde: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for deleting templates (templates page)
 */
function pdf_builder_delete_template_handler() {
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        $template_id = intval($_POST['template_id'] ?? 0);

        if (!$template_id) {
            wp_send_json_error('ID du template manquant');
            return;
        }

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // Check if template exists
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_templates WHERE id = %d", $template_id));
        if (!$existing) {
            wp_send_json_error('Template non trouvé');
            return;
        }

        // Delete template
        $result = $wpdb->delete(
            $table_templates,
            ['id' => $template_id],
            ['%d']
        );

        if ($result === false) {
            wp_send_json_error('Erreur lors de la suppression');
            return;
        }

        wp_send_json_success([
            'message' => 'Template supprimé avec succès'
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la suppression: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for setting default template (templates page)
 */
function pdf_builder_set_default_template_handler() {
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        $template_id = intval($_POST['template_id'] ?? 0);
        $is_default = intval($_POST['is_default'] ?? 0);

        if (!$template_id) {
            wp_send_json_error('ID du template manquant');
            return;
        }

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // First, remove default status from all templates
        $wpdb->update(
            $table_templates,
            ['is_default' => 0],
            ['is_default' => 1],
            ['%d'],
            ['%d']
        );

        // Set new default if requested
        if ($is_default) {
            $result = $wpdb->update(
                $table_templates,
                ['is_default' => 1],
                ['id' => $template_id],
                ['%d'],
                ['%d']
            );

            if ($result === false) {
                wp_send_json_error('Erreur lors de la mise à jour du statut par défaut');
                return;
            }
        }

        wp_send_json_success([
            'message' => $is_default ? 'Template défini comme par défaut' : 'Statut par défaut retiré'
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la modification: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for duplicating templates (templates page)
 */
function pdf_builder_duplicate_template_handler() {
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        $template_id = intval($_POST['template_id'] ?? 0);
        $template_name = sanitize_text_field($_POST['template_name'] ?? '');

        if (!$template_id || empty($template_name)) {
            wp_send_json_error('Données manquantes');
            return;
        }

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // Get original template
        $original = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
            ARRAY_A
        );

        if (!$original) {
            wp_send_json_error('Template original non trouvé');
            return;
        }

        // Insert duplicate
        $result = $wpdb->insert(
            $table_templates,
            [
                'name' => $template_name,
                'template_data' => $original['template_data'],
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
                'is_default' => 0
            ],
            ['%s', '%s', '%s', '%s', '%d']
        );

        if ($result === false) {
            wp_send_json_error('Erreur lors de la duplication');
            return;
        }

        wp_send_json_success([
            'message' => 'Template dupliqué avec succès',
            'new_template_id' => $wpdb->insert_id
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la duplication: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for loading predefined template into editor (templates page)
 */
function pdf_builder_load_predefined_into_editor_handler() {
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        $template_slug = sanitize_text_field($_POST['template_slug'] ?? '');

        if (empty($template_slug)) {
            wp_send_json_error('Slug du modèle manquant');
            return;
        }

        // Load predefined template data
        if (!class_exists('PDF_Builder\TemplateDefaults')) {
            wp_send_json_error('Classe TemplateDefaults non trouvée');
            return;
        }

        $template_data = \PDF_Builder\TemplateDefaults::get_template_by_slug($template_slug);

        if (!$template_data) {
            wp_send_json_error('Modèle prédéfini non trouvé');
            return;
        }

        // For now, we'll assume template ID 1 (default template)
        // In a real implementation, you might want to create a new template or update an existing one
        $template_id = 1;

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // Update template with predefined data
        $result = $wpdb->update(
            $table_templates,
            [
                'template_data' => json_encode($template_data),
                'updated_at' => current_time('mysql')
            ],
            ['id' => $template_id],
            ['%s', '%s'],
            ['%d']
        );

        if ($result === false) {
            wp_send_json_error('Erreur lors du chargement du modèle');
            return;
        }

        wp_send_json_success([
            'message' => 'Modèle chargé avec succès',
            'redirect_url' => admin_url('admin.php?page=pdf-builder-react-editor&template_id=' . $template_id)
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors du chargement: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for checking template creation limit (templates page)
 */
function pdf_builder_check_template_limit_handler() {
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        // Check if premium features are enabled
        $is_premium = defined('PDF_BUILDER_PREMIUM') && PDF_BUILDER_PREMIUM;

        if ($is_premium) {
            wp_send_json_success(['can_create' => true]);
            return;
        }

        // For free version, check template count
        $user_id = get_current_user_id();
        $template_count = \PDF_Builder\Admin\PdfBuilderAdmin::count_user_templates($user_id);

        $can_create = $template_count < 1; // Limit to 1 template for free version

        wp_send_json_success([
            'can_create' => $can_create,
            'current_count' => $template_count,
            'limit' => 1
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la vérification: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for creating backups
 */
function pdf_builder_create_backup_ajax() {
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        // Create backup directory if it doesn't exist
        $backup_dir = WP_CONTENT_DIR . '/pdf-builder-backups';
        if (!file_exists($backup_dir)) {
            if (!wp_mkdir_p($backup_dir)) {
                wp_send_json_error('Impossible de créer le dossier de sauvegarde');
                return;
            }
        }

        // Get all plugin options
        global $wpdb;
        $options = $wpdb->get_results(
            $wpdb->prepare("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s", 'pdf_builder_%'),
            ARRAY_A
        );

        // Create backup filename with timestamp
        $timestamp = current_time('timestamp');
        $filename = 'pdf_builder_backup_' . wp_date('Y-m-d_H-i-s', $timestamp) . '.json';
        $filepath = $backup_dir . '/' . $filename;

        // Prepare backup data
        $backup_data = array(
            'version' => '1.0',
            'timestamp' => $timestamp,
            'date' => wp_date('Y-m-d H:i:s', $timestamp),
            'timezone' => wp_timezone_string(),
            'type' => 'manual',
            'options' => array()
        );

        foreach ($options as $option) {
            $backup_data['options'][$option['option_name']] = maybe_unserialize($option['option_value']);
        }

        // Write backup file
        if (file_put_contents($filepath, json_encode($backup_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            wp_send_json_success(array(
                'message' => 'Sauvegarde créée avec succès',
                'filename' => $filename,
                'size' => size_format(filesize($filepath))
            ));
        } else {
            wp_send_json_error('Erreur lors de l\'écriture du fichier de sauvegarde');
        }

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la création de la sauvegarde: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for listing backups
 */
function pdf_builder_list_backups_ajax() {
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        $backup_dir = WP_CONTENT_DIR . '/pdf-builder-backups';

        if (!file_exists($backup_dir) || !is_dir($backup_dir)) {
            wp_send_json_success(array('backups' => array()));
            return;
        }

        $files = glob($backup_dir . '/pdf_builder_backup_*.json');
        $backups = array();

        foreach ($files as $file) {
            $filename = basename($file);
            $file_path = $backup_dir . '/' . $filename;

            if (is_file($file_path) && is_readable($file_path)) {
                $file_size = filesize($file_path);
                $file_modified = filemtime($file_path);

                // Parse filename to extract date
                $date_match = array();
                if (preg_match('/pdf_builder_backup_(\d{4}-\d{2}-\d{2})_(\d{2}-\d{2}-\d{2})\.json/', $filename, $date_match)) {
                    $date_str = $date_match[1] . ' ' . str_replace('-', ':', $date_match[2]);
                    $backup_date = strtotime($date_str);
                } else {
                    $backup_date = $file_modified;
                }

                $backups[] = array(
                    'filename' => $filename,
                    'size' => $file_size,
                    'size_human' => size_format($file_size),
                    'modified' => $file_modified,
                    'modified_human' => wp_date(get_option('date_format') . ' ' . get_option('time_format'), $file_modified),
                    'type' => strpos($filename, 'auto_backup') !== false ? 'automatic' : 'manual'
                );
            }
        }

        // Sort by modification date (newest first)
        usort($backups, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });

        wp_send_json_success(array('backups' => $backups));

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la récupération des sauvegardes: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for restoring backups
 */
function pdf_builder_restore_backup_ajax() {
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        $filename = sanitize_file_name($_POST['filename'] ?? '');

        if (empty($filename)) {
            wp_send_json_error('Nom de fichier manquant');
            return;
        }

        $backup_dir = WP_CONTENT_DIR . '/pdf-builder-backups';
        $filepath = $backup_dir . '/' . $filename;

        if (!file_exists($filepath) || !is_readable($filepath)) {
            wp_send_json_error('Fichier de sauvegarde introuvable ou illisible');
            return;
        }

        // Read and decode backup file
        $backup_content = file_get_contents($filepath);
        $backup_data = json_decode($backup_content, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($backup_data['options'])) {
            wp_send_json_error('Fichier de sauvegarde corrompu');
            return;
        }

        // Restore options
        $restored_count = 0;
        foreach ($backup_data['options'] as $option_name => $option_value) {
            update_option($option_name, $option_value);
            $restored_count++;
        }

        wp_send_json_success(array(
            'message' => 'Sauvegarde restaurée avec succès',
            'restored_options' => $restored_count
        ));

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la restauration: ' . $e->getMessage());
    }
}









/**
 * Vérifier l'état des systèmes avancés
 */
function pdf_builder_check_advanced_systems_status() {
    $systems_status = array(
        'intelligent_loader' => class_exists('PDF_Builder_Intelligent_Loader') && PDF_Builder_Intelligent_Loader::get_instance() !== null,
        'config_manager' => class_exists('PDF_Builder_Config_Manager') && PDF_Builder_Config_Manager::get_instance() !== null,
        'smart_cache' => class_exists('PDF_Builder_Smart_Cache') && PDF_Builder_Smart_Cache::get_instance() !== null,
        'advanced_logger' => class_exists('PDF_Builder_Advanced_Logger') && PDF_Builder_Advanced_Logger::get_instance() !== null,
        'security_validator' => class_exists('PDF_Builder_Security_Validator') && PDF_Builder_Security_Validator::get_instance() !== null,
        'error_handler' => class_exists('PDF_Builder_Error_Handler') && PDF_Builder_Error_Handler::get_instance() !== null,
        'task_scheduler' => class_exists('PDF_Builder_Task_Scheduler') && PDF_Builder_Task_Scheduler::get_instance() !== null,
        'notification_manager' => class_exists('PDF_Builder_Notification_Manager') && PDF_Builder_Notification_Manager::get_instance() !== null,
        'diagnostic_tool' => class_exists('PDF_Builder_Diagnostic_Tool') && PDF_Builder_Diagnostic_Tool::get_instance() !== null,
        'analytics_manager' => class_exists('PDF_Builder_Analytics_Manager') && PDF_Builder_Analytics_Manager::get_instance() !== null,
        'backup_recovery' => class_exists('PDF_Builder_Backup_Recovery') && PDF_Builder_Backup_Recovery::get_instance() !== null,
        'security_monitor' => class_exists('PDF_Builder_Security_Monitor') && PDF_Builder_Security_Monitor::get_instance() !== null,
        'update_manager' => class_exists('PDF_Builder_Update_Manager') && PDF_Builder_Update_Manager::get_instance() !== null,
        'reporting_system' => class_exists('PDF_Builder_Reporting_System') && PDF_Builder_Reporting_System::get_instance() !== null,
        'test_suite' => class_exists('PDF_Builder_Test_Suite') && PDF_Builder_Test_Suite::get_instance() !== null,
        'continuous_deployment' => class_exists('PDF_Builder_Continuous_Deployment') && PDF_Builder_Continuous_Deployment::get_instance() !== null,
        'health_monitor' => class_exists('PDF_Builder_Health_Monitor') && PDF_Builder_Health_Monitor::get_instance() !== null,
        'api_manager' => class_exists('PDF_Builder_API_Manager') && PDF_Builder_API_Manager::get_instance() !== null,
        'metrics_analytics' => class_exists('PDF_Builder_Metrics_Analytics') && PDF_Builder_Metrics_Analytics::get_instance() !== null,
        'database_updater' => class_exists('PDF_Builder_Database_Updater') && PDF_Builder_Database_Updater::get_instance() !== null,
        'localization' => class_exists('PDF_Builder_Localization') && PDF_Builder_Localization::get_instance() !== null,
        'theme_customizer' => class_exists('PDF_Builder_Theme_Customizer') && PDF_Builder_Theme_Customizer::get_instance() !== null,
        'user_manager' => class_exists('PDF_Builder_User_Manager') && PDF_Builder_User_Manager::get_instance() !== null,
        'license_manager' => class_exists('PDF_Builder_License_Manager') && PDF_Builder_License_Manager::get_instance() !== null,
        'integration_manager' => class_exists('PDF_Builder_Integration_Manager') && PDF_Builder_Integration_Manager::get_instance() !== null,
        'auto_update_manager' => class_exists('PDF_Builder_Auto_Update_Manager') && PDF_Builder_Auto_Update_Manager::get_instance() !== null,
        'advanced_reporting' => class_exists('PDF_Builder_Advanced_Reporting') && PDF_Builder_Advanced_Reporting::get_instance() !== null,
        'ajax_handler' => class_exists('PDF_Builder_Ajax_Handler') && PDF_Builder_Ajax_Handler::get_instance() !== null,
    );

    $all_systems_loaded = !in_array(false, $systems_status, true);
    $loaded_count = count(array_filter($systems_status));
    $total_count = count($systems_status);

    if (!$all_systems_loaded) {
        $failed_systems = array_keys(array_filter($systems_status, function($status) { return !$status; }));
        error_log('PDF Builder: Certains systèmes avancés n\'ont pas été chargés: ' . implode(', ', $failed_systems));
    }

    return array(
        'all_loaded' => $all_systems_loaded,
        'loaded_count' => $loaded_count,
        'total_count' => $total_count,
        'systems_status' => $systems_status
    );
}

/**
 * Hook d'administration pour vérifier l'état des systèmes
 */
add_action('admin_init', function() {
    // Vérifier l'état des systèmes une fois par jour
    $last_check = get_option('pdf_builder_systems_check_timestamp', 0);
    $current_time = current_time('timestamp');

    if (($current_time - $last_check) > 86400) { // 24 heures
        $status = pdf_builder_check_advanced_systems_status();
        update_option('pdf_builder_systems_check_timestamp', $current_time);

        if (!$status['all_loaded']) {
            // Notifier l'administrateur si des systèmes ne sont pas chargés
            if (current_user_can('manage_options')) {
                add_action('admin_notices', function() use ($status) {
                    echo '<div class="notice notice-warning is-dismissible">';
                    echo '<p><strong>PDF Builder Pro:</strong> ' . ($status['total_count'] - $status['loaded_count']) . ' système(s) avancé(s) n\'ont pas été chargés correctement. Vérifiez les logs pour plus de détails.</p>';
                    echo '</div>';
                });
            }
        }
    }
});



