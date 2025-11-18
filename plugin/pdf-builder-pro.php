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

// DEBUG: Log si le plugin se charge pendant AJAX
if (defined('DOING_AJAX') && DOING_AJAX) {
}

// Définir les constantes du plugin
define('PDF_BUILDER_PLUGIN_FILE', __FILE__);
define('PDF_BUILDER_PLUGIN_DIR', dirname(__FILE__) . '/');
// PDF_BUILDER_PLUGIN_URL sera défini dans constants.php avec plugins_url()
// PDF_BUILDER_VERSION sera défini dans constants.php
// Désactiver les avertissements de dépréciation pour la compatibilité PHP 8.1+
error_reporting(error_reporting() & ~E_DEPRECATED);
// Hook d'activation
register_activation_hook(__FILE__, 'pdf_builder_activate');
// Hook de désactivation
register_deactivation_hook(__FILE__, 'pdf_builder_deactivate');

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
add_action('plugins_loaded', 'pdf_builder_init');
add_action('plugins_loaded', 'pdf_builder_load_textdomain', 1);

/**
 * Enregistrer les handlers AJAX
 */
function pdf_builder_register_ajax_handlers() {
    // Preview images
    add_action('wp_ajax_nopriv_wp_pdf_preview_image', 'pdf_builder_handle_preview_ajax');
    add_action('wp_ajax_wp_pdf_preview_image', 'pdf_builder_handle_preview_ajax');

    // Settings save
    add_action('wp_ajax_pdf_builder_save_settings', 'pdf_builder_save_settings_ajax');
    add_action('wp_ajax_pdf_builder_get_cache_status', 'pdf_builder_get_cache_status_ajax');
    add_action('wp_ajax_pdf_builder_test_cache', 'pdf_builder_test_cache_ajax');
    add_action('wp_ajax_pdf_builder_clear_cache', 'pdf_builder_clear_cache_ajax');
    // Nouveaux handlers pour les fonctionnalités de cache avancées
    add_action('wp_ajax_pdf_builder_test_cache_integration', 'pdf_builder_test_cache_integration_ajax');
    add_action('wp_ajax_pdf_builder_clear_all_cache', 'pdf_builder_clear_all_cache_ajax');
    add_action('wp_ajax_pdf_builder_get_cache_metrics', 'pdf_builder_get_cache_metrics_ajax');
    // add_action('wp_ajax_pdf_builder_create_backup', 'pdf_builder_create_backup_ajax'); // Désactivé - conflit avec le manager
    // add_action('wp_ajax_pdf_builder_list_backups', 'pdf_builder_list_backups_ajax'); // Désactivé - conflit avec le manager
    // add_action('wp_ajax_pdf_builder_restore_backup', 'pdf_builder_restore_backup_ajax'); // Désactivé - conflit avec le manager
    // add_action('wp_ajax_pdf_builder_delete_backup', 'pdf_builder_delete_backup_ajax'); // Désactivé - conflit avec le manager
    add_action('pdf_builder_daily_backup', 'pdf_builder_execute_daily_backup');
    add_action('pdf_builder_cleanup_old_backups', 'pdf_builder_cleanup_old_backups');
    add_action('pdf_builder_weekly_maintenance', 'pdf_builder_execute_weekly_maintenance');
    add_action('admin_action_pdf_builder_download_backup', 'pdf_builder_download_backup');
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
add_action('init', 'pdf_builder_handle_pdf_downloads');
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
    if (class_exists('WP_PDF_Builder_Pro\Api\PreviewImageAPI')) {
        // Créer une nouvelle instance et appeler generatePreview directement
        $api = new \WP_PDF_Builder_Pro\Api\PreviewImageAPI();
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
    // Vérifier le nonce
    if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_save_settings')) {
        wp_send_json_error('Nonce invalide');
        return;
    }

    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    $current_tab = sanitize_text_field($_POST['current_tab'] ?? 'all');
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

            // Système
            'cache_enabled' => isset($_POST['cache_enabled']) ? '1' : '0',
            'cache_ttl' => intval($_POST['cache_ttl'] ?? 3600),
            'cache_compression' => isset($_POST['cache_compression']) ? '1' : '0',
            'cache_auto_cleanup' => isset($_POST['cache_auto_cleanup']) ? '1' : '0',
            'cache_max_size' => intval($_POST['cache_max_size'] ?? 100),

            // PDF
            'pdf_quality' => sanitize_text_field($_POST['pdf_quality'] ?? 'high'),
            'pdf_page_size' => sanitize_text_field($_POST['pdf_page_size'] ?? 'A4'),
            'default_template' => sanitize_text_field($_POST['default_template'] ?? 'blank'),

            // Contenu
            'template_library_enabled' => isset($_POST['template_library_enabled']) ? '1' : '0',

            // Développeur
            'developer_enabled' => isset($_POST['developer_enabled']) ? '1' : '0',
            'developer_password' => sanitize_text_field($_POST['developer_password'] ?? ''),
            'debug_php_errors' => isset($_POST['debug_php_errors']) ? '1' : '0',
            'debug_javascript' => isset($_POST['debug_javascript']) ? '1' : '0',
            'debug_javascript_verbose' => isset($_POST['debug_javascript_verbose']) ? '1' : '0',
            'debug_ajax' => isset($_POST['debug_ajax']) ? '1' : '0',
            'debug_performance' => isset($_POST['debug_performance']) ? '1' : '0',
            'debug_database' => isset($_POST['debug_database']) ? '1' : '0',
            'log_level' => intval($_POST['log_level'] ?? 0),
            'log_file_size' => intval($_POST['log_file_size'] ?? 10),
            'log_retention' => intval($_POST['log_retention'] ?? 0),
            'force_https' => isset($_POST['force_https']) ? '1' : '0',
        );

        foreach ($all_settings as $key => $value) {
            update_option('pdf_builder_' . $key, $value);
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
                'pdf_quality' => sanitize_text_field($_POST['pdf_quality']),
                'pdf_format' => sanitize_text_field($_POST['pdf_format']),
                'pdf_compression' => isset($_POST['pdf_compression']) ? '1' : '0',
                'pdf_metadata' => isset($_POST['pdf_metadata']) ? '1' : '0',
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
                'template_library' => isset($_POST['template_library']) ? '1' : '0',
                'custom_templates' => isset($_POST['custom_templates']) ? '1' : '0',
                'template_sharing' => isset($_POST['template_sharing']) ? '1' : '0',
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
    }
    } // End of else block

    if ($saved_count > 0) {
        wp_send_json_success('Paramètres sauvegardés avec succès');
    } else {
        wp_send_json_error('Aucun paramètre sauvegardé');
    }
}

/**
 * AJAX handler pour récupérer l'état du cache
 */
function pdf_builder_get_cache_status_ajax() {
    // Vérifier le nonce
    if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_save_settings')) {
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
    if (!wp_verify_nonce($_POST['security'], 'pdf_builder_save_settings')) {
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
    $transient_test_value = 'test_value_' . rand(1000, 9999);

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
 * AJAX handler pour vider le cache
 */
function pdf_builder_clear_cache_ajax() {
    // Vérifier le nonce
    if (!wp_verify_nonce($_POST['security'], 'pdf_builder_save_settings')) {
        wp_send_json_error('Nonce invalide');
        return;
    }

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
    if (!wp_verify_nonce($_GET['nonce'], 'pdf_builder_save_settings')) {
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
    if ($auto_maintenance_enabled !== '1') {
        return; // Maintenance automatique désactivée
    }

    try {
        // Log de début de maintenance
        error_log('[PDF Builder] Démarrage de la maintenance automatique hebdomadaire');

        // 1. Optimiser la base de données
        pdf_builder_auto_optimize_db();

        // 2. Réparer les templates
        pdf_builder_auto_repair_templates();

        // 3. Supprimer les fichiers temporaires
        pdf_builder_auto_remove_temp_files();

        // 4. Nettoyer le cache
        pdf_builder_auto_clear_cache();

        // Log de fin de maintenance
        error_log('[PDF Builder] Maintenance automatique hebdomadaire terminée avec succès');

    } catch (Exception $e) {
        error_log('[PDF Builder] Erreur lors de la maintenance automatique: ' . $e->getMessage());
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

        error_log('[PDF Builder] Base de données optimisée automatiquement');
    } catch (Exception $e) {
        error_log('[PDF Builder] Erreur lors de l\'optimisation automatique de la DB: ' . $e->getMessage());
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

        error_log('[PDF Builder] Templates réparés automatiquement');
    } catch (Exception $e) {
        error_log('[PDF Builder] Erreur lors de la réparation automatique des templates: ' . $e->getMessage());
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

            error_log('[PDF Builder] ' . $deleted_count . ' fichiers temporaires supprimés automatiquement');
        }
    } catch (Exception $e) {
        error_log('[PDF Builder] Erreur lors de la suppression automatique des fichiers temp: ' . $e->getMessage());
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

                error_log('[PDF Builder] ' . $deleted_count . ' fichiers cache supprimés automatiquement');
            }
        }

        error_log('[PDF Builder] Cache nettoyé automatiquement');
    } catch (Exception $e) {
        error_log('[PDF Builder] Erreur lors du nettoyage automatique du cache: ' . $e->getMessage());
    }
}



