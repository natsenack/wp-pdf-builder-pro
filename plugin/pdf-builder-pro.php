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
    add_action('wp_ajax_pdf_builder_test_cache', 'pdf_builder_test_cache_ajax');
    add_action('wp_ajax_pdf_builder_clear_cache', 'pdf_builder_clear_cache_ajax');
    add_action('wp_ajax_pdf_builder_generate_test_license_key', 'pdf_builder_generate_test_license_key_ajax');
    add_action('wp_ajax_pdf_builder_delete_test_license_key', 'pdf_builder_delete_test_license_key_ajax');
    add_action('wp_ajax_pdf_builder_get_consent_status', 'pdf_builder_get_consent_status_ajax');
    add_action('wp_ajax_pdf_builder_export_user_data', 'pdf_builder_export_user_data_ajax');
    add_action('wp_ajax_pdf_builder_delete_user_data', 'pdf_builder_delete_user_data_ajax');
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

    $current_tab = sanitize_text_field($_POST['current_tab']);
    $saved_count = 0;

    // Traiter selon l'onglet
    switch ($current_tab) {
        case 'general':
            if (isset($_POST['pdf_builder_settings_nonce']) &&
                wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
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
            }
            break;

        case 'performance':
            if (isset($_POST['pdf_builder_performance_nonce']) &&
                wp_verify_nonce($_POST['pdf_builder_performance_nonce'], 'pdf_builder_performance')) {
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
            }
            break;

        case 'maintenance':
            if (isset($_POST['pdf_builder_maintenance_nonce']) &&
                wp_verify_nonce($_POST['pdf_builder_maintenance_nonce'], 'pdf_builder_maintenance')) {
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
            }
            break;

        case 'sauvegarde':
            if (isset($_POST['pdf_builder_backup_nonce']) &&
                wp_verify_nonce($_POST['pdf_builder_backup_nonce'], 'pdf_builder_backup')) {
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
            }
            break;

        case 'acces':
            if (isset($_POST['pdf_builder_roles_nonce']) &&
                wp_verify_nonce($_POST['pdf_builder_roles_nonce'], 'pdf_builder_roles')) {
                // Sauvegarder les rôles
                $allowed_roles = isset($_POST['pdf_builder_allowed_roles']) ? $_POST['pdf_builder_allowed_roles'] : array();
                update_option('pdf_builder_allowed_roles', $allowed_roles);
                $saved_count++;
            }
            break;

        case 'securite':
            if (isset($_POST['pdf_builder_securite_nonce']) &&
                wp_verify_nonce($_POST['pdf_builder_securite_nonce'], 'pdf_builder_securite')) {
                // Sauvegarder les paramètres sécurité
                $settings = array(
                    'ip_filtering' => isset($_POST['ip_filtering']) ? '1' : '0',
                    'rate_limiting' => isset($_POST['rate_limiting']) ? '1' : '0',
                    'encryption' => isset($_POST['encryption']) ? '1' : '0',
                    'audit_log' => isset($_POST['audit_log']) ? '1' : '0',
                );

                foreach ($settings as $key => $value) {
                    update_option('pdf_builder_' . $key, $value);
                }
                $saved_count++;
            }

            if (isset($_POST['pdf_builder_rgpd_nonce']) &&
                wp_verify_nonce($_POST['pdf_builder_rgpd_nonce'], 'pdf_builder_rgpd')) {
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
            }
            break;

        case 'pdf':
            if (isset($_POST['pdf_builder_pdf_nonce']) &&
                wp_verify_nonce($_POST['pdf_builder_pdf_nonce'], 'pdf_builder_pdf')) {
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
            }
            break;

        case 'contenu':
            if (isset($_POST['pdf_builder_canvas_nonce']) &&
                wp_verify_nonce($_POST['pdf_builder_canvas_nonce'], 'pdf_builder_canvas')) {
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
            }

            if (isset($_POST['pdf_builder_templates_nonce']) &&
                wp_verify_nonce($_POST['pdf_builder_templates_nonce'], 'pdf_builder_templates')) {
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
            }
            break;

        case 'developpeur':
            if (isset($_POST['pdf_builder_developer_nonce']) &&
                wp_verify_nonce($_POST['pdf_builder_developer_nonce'], 'pdf_builder_developer')) {
                // Sauvegarder les paramètres développeur
                $settings = array(
                    'debug_mode' => isset($_POST['debug_mode']) ? '1' : '0',
                    'dev_tools' => isset($_POST['dev_tools']) ? '1' : '0',
                    'api_logging' => isset($_POST['api_logging']) ? '1' : '0',
                    'performance_monitoring' => isset($_POST['performance_monitoring']) ? '1' : '0',
                );

                foreach ($settings as $key => $value) {
                    update_option('pdf_builder_' . $key, $value);
                }
                $saved_count++;
            }
            break;
    }

    if ($saved_count > 0) {
        wp_send_json_success('Paramètres sauvegardés avec succès');
    } else {
        wp_send_json_error('Aucun paramètre sauvegardé');
    }
}

/**
 * AJAX handler pour tester le système de cache
 */
function pdf_builder_test_cache_ajax() {
    // Vérifier le nonce
    if (!wp_verify_nonce($_POST['security'], 'pdf_builder_test_cache')) {
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
 * AJAX handler pour générer une clé de licence de test
 */
function pdf_builder_generate_test_license_key_ajax() {
    // Vérifier le nonce
    if (!wp_verify_nonce($_POST['security'], 'pdf_builder_generate_license_key')) {
        wp_send_json_error('Nonce invalide');
        return;
    }

    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Générer une clé de test
    $test_key = 'TEST-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 16));
    $expires = strtotime('+30 days'); // Expire dans 30 jours

    // Sauvegarder la clé de test
    update_option('pdf_builder_license_test_key', $test_key);
    update_option('pdf_builder_license_test_key_expires', $expires);
    update_option('pdf_builder_license_test_mode_enabled', true);

    wp_send_json_success(array(
        'message' => 'Clé de licence de test générée avec succès',
        'test_key' => $test_key,
        'expires' => date('Y-m-d H:i:s', $expires)
    ));
}

/**
 * AJAX handler pour supprimer une clé de licence de test
 */
function pdf_builder_delete_test_license_key_ajax() {
    // Vérifier le nonce
    if (!wp_verify_nonce($_POST['security'], 'pdf_builder_delete_test_license_key')) {
        wp_send_json_error('Nonce invalide');
        return;
    }

    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Supprimer la clé de test
    delete_option('pdf_builder_license_test_key');
    delete_option('pdf_builder_license_test_key_expires');
    update_option('pdf_builder_license_test_mode_enabled', false);

    wp_send_json_success(array(
        'message' => 'Clé de licence de test supprimée avec succès'
    ));
}

/**
 * AJAX handler pour obtenir le statut du consentement
 */
function pdf_builder_get_consent_status_ajax() {
    // Vérifier le nonce
    if (!wp_verify_nonce($_POST['security'], 'pdf_builder_get_consent_status')) {
        wp_send_json_error('Nonce invalide');
        return;
    }

    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Obtenir le statut du consentement
    $consent_status = get_option('pdf_builder_consent_status', 'not_set');

    wp_send_json_success(array(
        'consent_status' => $consent_status
    ));
}

/**
 * AJAX handler pour exporter les données utilisateur
 */
function pdf_builder_export_user_data_ajax() {
    // Vérifier le nonce
    if (!wp_verify_nonce($_POST['security'], 'pdf_builder_export_user_data')) {
        wp_send_json_error('Nonce invalide');
        return;
    }

    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Collecter les données utilisateur
    $user_data = array(
        'settings' => get_option('pdf_builder_settings', array()),
        'license_key' => get_option('pdf_builder_license_key', ''),
        'test_license_key' => get_option('pdf_builder_license_test_key', ''),
        'consent_status' => get_option('pdf_builder_consent_status', 'not_set'),
        'export_date' => current_time('mysql')
    );

    wp_send_json_success(array(
        'message' => 'Données utilisateur exportées avec succès',
        'data' => $user_data
    ));
}

/**
 * AJAX handler pour supprimer les données utilisateur
 */
function pdf_builder_delete_user_data_ajax() {
    // Vérifier le nonce
    if (!wp_verify_nonce($_POST['security'], 'pdf_builder_delete_user_data')) {
        wp_send_json_error('Nonce invalide');
        return;
    }

    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Supprimer toutes les données utilisateur
    delete_option('pdf_builder_settings');
    delete_option('pdf_builder_license_key');
    delete_option('pdf_builder_license_test_key');
    delete_option('pdf_builder_license_test_key_expires');
    delete_option('pdf_builder_license_test_mode_enabled');
    delete_option('pdf_builder_consent_status');

    wp_send_json_success(array(
        'message' => 'Toutes les données utilisateur ont été supprimées avec succès'
    ));
}

/**
 * AJAX handler pour vider le cache
 */
function pdf_builder_clear_cache_ajax() {
    // Vérifier le nonce
    if (!wp_verify_nonce($_POST['security'], 'pdf_builder_clear_cache')) {
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

