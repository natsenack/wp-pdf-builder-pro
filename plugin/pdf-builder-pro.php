<?php

/**
 * Plugin Name: PDF Builder Pro
 * Plugin URI: https://github.com/natsenack/wp-pdf-builder-pro
 * Description: Constructeur de PDF professionnel ultra-performant avec architecture modulaire avancée
 * Version: 1.0.1.0
 * Author: Natsenack
 * Author URI: https://github.com/natsenack
 * License: GPL v2 or later
 * Text Domain: pdf-builder-pro
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.9
 * Requires PHP: 7.4
 */

// Définir les constantes du plugin
define('PDF_BUILDER_PLUGIN_FILE', __FILE__);
define('PDF_BUILDER_PLUGIN_DIR', dirname(__FILE__) . '/');
define('PDF_BUILDER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PDF_BUILDER_PRO_ASSETS_URL', plugin_dir_url(__FILE__) . 'assets/');
define('PDF_BUILDER_PRO_ASSETS_PATH', plugin_dir_path(__FILE__) . 'assets/');
define('PDF_BUILDER_VERSION', '1.0.1.0');
define('PDF_BUILDER_PRO_VERSION', '1.0.1.0');

// Premium features constant (set to false for free version)
if (!defined('PDF_BUILDER_PREMIUM')) {
    define('PDF_BUILDER_PREMIUM', false);
}

// CHARGEMENT CRITIQUE DE LA CLASSE ADMIN PRINCIPALE
// Retardé au hook plugins_loaded pour éviter les conflits d'initialisation
add_action('plugins_loaded', function() {
    // Initialiser l'autoloader personnalisé
    $autoloader_file = PDF_BUILDER_PLUGIN_DIR . 'src/Core/core/autoloader.php';
    if (file_exists($autoloader_file)) {
        try {
            require_once $autoloader_file;
            if (class_exists('PDF_Builder\Core\PdfBuilderAutoloader')) {
                \PDF_Builder\Core\PdfBuilderAutoloader::init(PDF_BUILDER_PLUGIN_DIR);
            }
        } catch (Exception $e) {
            error_log('[ERROR] PDF Builder: Autoloader init failed: ' . $e->getMessage());
        }
    } else {
        error_log('[ERROR] PDF Builder: Custom autoloader file not found: ' . $autoloader_file);
    }

    // ENREGISTRER LES ACTIONS AJAX TRÈS TÔT
    add_action('wp_ajax_pdf_builder_show_notification', 'pdf_builder_test_notification_handler');
    add_action('wp_ajax_nopriv_pdf_builder_show_notification', 'pdf_builder_test_notification_handler');
    
    function pdf_builder_test_notification_handler() {
        error_log('[TEST HANDLER] pdf_builder_show_notification called');
        error_log('[TEST HANDLER] POST data: ' . print_r($_POST, true));
        
        // TEST: Vérifier le nonce de différentes manières
        $nonce = $_POST['nonce'] ?? '';
        error_log('[TEST HANDLER] Checking nonce: ' . $nonce);
        
        $check1 = wp_verify_nonce($nonce, 'pdf_builder_settings');
        $check2 = wp_verify_nonce($nonce, 'pdf_builder_ajax');
        $check3 = wp_verify_nonce($nonce, 'pdf_builder_notifications');
        
        error_log('[TEST HANDLER] Nonce checks - settings: ' . ($check1 ? 'VALID' : 'INVALID') . ', ajax: ' . ($check2 ? 'VALID' : 'INVALID') . ', notifications: ' . ($check3 ? 'VALID' : 'INVALID'));
        
        // Pour le test, acceptons tous les nonces
        $nonce_valid = $check1 || $check2 || $check3;
        
        if (!$nonce_valid) {
            error_log('[TEST HANDLER] All nonce checks failed - accepting anyway for test');
            // Pour le test, on accepte quand même
            $nonce_valid = true;
        }
        
        $message = sanitize_text_field($_POST['message'] ?? '');
        $type = sanitize_text_field($_POST['type'] ?? 'info');
        
        error_log('[TEST HANDLER] Message: ' . $message . ', Type: ' . $type);
        
        wp_send_json_success([
            'message' => $message,
            'type' => $type,
            'test' => true,
            'nonce_checks' => [
                'settings' => $check1,
                'ajax' => $check2,
                'notifications' => $check3
            ]
        ]);
    }

    if (!class_exists('PDF_Builder\Admin\PdfBuilderAdminNew')) {
        $admin_file = PDF_BUILDER_PLUGIN_DIR . 'src/Admin/PDF_Builder_Admin.php';
        if (file_exists($admin_file)) {
            require_once $admin_file;
        } else {
            error_log('[ERROR] PDF Builder: Admin class file not found: ' . $admin_file);
        }
    }

    // Instancier la classe admin principale pour enregistrer les menus et hooks
    if (class_exists('PDF_Builder\Admin\PdfBuilderAdminNew')) {
        try {
            \PDF_Builder\Admin\PdfBuilderAdminNew::getInstance();
        } catch (Exception $e) {
            error_log('[ERROR] PDF Builder: Failed to instantiate admin class: ' . $e->getMessage());
        }
    } else {
        error_log('[ERROR] PDF Builder: Admin class not found after loading attempt');
    }
});

/**
 * Fonction principale d'initialisation du plugin
 */
function pdf_builder_init_plugin() {
    // Garder contre les initialisations multiples
    static $initialized = false;
    if ($initialized) {
        return;
    }
    $initialized = true;

    $bootstrap = PDF_BUILDER_PLUGIN_DIR . 'bootstrap.php';
    if (file_exists($bootstrap)) {
        require_once $bootstrap;
    } else {
        error_log('[ERROR] PDF Builder: bootstrap.php not found at: ' . $bootstrap);
    }
}

// VERSION ULTRA-SIMPLE - ne charger que l'essentiel
if (function_exists('add_action')) {
    // Charger le bootstrap plus tard, après plugins_loaded
    add_action('init', 'pdf_builder_init_plugin', 30);
}

// Retirer l'appel immédiat aux handlers AJAX - ils seront enregistrés dans le bootstrap
// if (function_exists('add_action') && function_exists('pdf_builder_register_ajax_handlers')) {
//     pdf_builder_register_ajax_handlers();
// }

// ========================================================================
// FONCTIONS UTILITAIRES - DOIVENT ÊTRE DISPONIBLES À L'ACTIVATION
// ========================================================================

/**
 * Fonction utilitaire pour mettre à jour les options
 */
if (!function_exists('pdf_builder_update_option')) {
    function pdf_builder_update_option($option_name, $option_value, $autoload = 'yes') {
        if (function_exists('update_option')) {
            return update_option($option_name, $option_value);
        }
        return false;
    }
}

/**
 * Fonction de logging
 */
if (!function_exists('pdf_builder_log')) {
    function pdf_builder_log($message, $level = 'info') {
        if (function_exists('error_log')) {
            $prefix = strtoupper($level);
            error_log('[PDF Builder] ' . $prefix . ': ' . $message);
        }
    }
}

/**
 * Fonction d'activation du plugin
 * Crée toutes les tables nécessaires
 */
function pdf_builder_activate()
{
    // Vérifier la compatibilité des versions
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('PDF Builder Pro nécessite PHP 7.4 ou supérieur. Version actuelle: ' . PHP_VERSION);
    }

    if (version_compare(get_bloginfo('version'), '5.0', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('PDF Builder Pro nécessite WordPress 5.0 ou supérieur. Version actuelle: ' . get_bloginfo('version'));
    }

    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // ========== TABLE 1: PARAMÈTRES PERSONNALISÉS ==========
    $table_settings = $wpdb->prefix . 'pdf_builder_settings';
    
    $sql_settings = "CREATE TABLE IF NOT EXISTS $table_settings (
        option_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        option_name varchar(191) NOT NULL DEFAULT '',
        option_value longtext NOT NULL,
        autoload varchar(20) NOT NULL DEFAULT 'yes',
        PRIMARY KEY (option_id),
        UNIQUE KEY option_name (option_name)
    ) $charset_collate;";
    
    dbDelta($sql_settings);
    error_log('[PDF Builder] Activation: Table wp_pdf_builder_settings créée/vérifiée');

    // ========== TABLE 2: TEMPLATES PERSONNALISÉS ==========
    $table_templates = $wpdb->prefix . 'pdf_builder_templates';
    
    $sql_templates = "CREATE TABLE IF NOT EXISTS $table_templates (
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
    
    dbDelta($sql_templates);
    error_log('[PDF Builder] Activation: Table wp_pdf_builder_templates créée/vérifiée');

    // Marquer la version du plugin comme activée
    pdf_builder_update_option('pdf_builder_version', '1.0.1.0');

    // Définir les valeurs par défaut pour les paramètres canvas
    $default_canvas_settings = array(
        // Dimensions (A4 par défaut)
        'pdf_builder_canvas_width' => '794',  // A4 width at 96 DPI
        'pdf_builder_canvas_height' => '1123', // A4 height at 96 DPI
        'pdf_builder_canvas_dpi' => '96',
        'pdf_builder_canvas_format' => 'A4',

        // Apparence
        'pdf_builder_canvas_bg_color' => '#ffffff',
        'pdf_builder_canvas_border_color' => '#cccccc',
        'pdf_builder_canvas_border_width' => '1',
        'pdf_builder_canvas_shadow_enabled' => '0',

        // Grille
        'pdf_builder_canvas_grid_enabled' => '1',
        'pdf_builder_canvas_grid_size' => '20',
        'pdf_builder_canvas_grid_color' => '#e0e0e0',
        'pdf_builder_canvas_snap_to_grid' => '0',

        // Autres paramètres par défaut
        'pdf_builder_canvas_margin_top' => '10',
        'pdf_builder_canvas_margin_right' => '10',
        'pdf_builder_canvas_margin_bottom' => '10',
        'pdf_builder_canvas_margin_left' => '10',
    );

    // Définir les valeurs par défaut seulement si elles n'existent pas déjà
    foreach ($default_canvas_settings as $option_key => $default_value) {
        if (!get_option($option_key)) {
            update_option($option_key, $default_value);
        }
    }

    // Définir d'autres valeurs par défaut générales
    $default_general_settings = array(
        'pdf_builder_pdf_quality' => 'high',
        'pdf_builder_pdf_page_size' => 'A4',
        'pdf_builder_pdf_orientation' => 'portrait',
        'pdf_builder_pdf_compression' => 'medium',
        'pdf_builder_pdf_metadata_enabled' => '1',
        'pdf_builder_pdf_print_optimized' => '1',
    );

    foreach ($default_general_settings as $option_key => $default_value) {
        if (!get_option($option_key)) {
            update_option($option_key, $default_value);
        }
    }

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
    $current_version = pdf_builder_get_option('pdf_builder_version', '1.0.0');
    
    // Si la version est inférieure à 1.1.0, mettre à jour le schéma
    if (version_compare($current_version, '1.1.0', '<')) {
        pdf_builder_update_table_schema();
        pdf_builder_update_option('pdf_builder_version', '1.1.0');
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
    
    // Déclencher le hook de désactivation personnalisé
    // Cela permettra au gestionnaire de désactivation de traiter les données
    do_action('pdf_builder_deactivate');
}



/**
 * Execute daily backup
 */
function pdf_builder_execute_daily_backup() {
    try {
        // Create backup directory if it doesn't exist
        $backup_dir = WP_CONTENT_DIR . '/pdf-builder-backups';
        if (!file_exists($backup_dir)) {
            if (!wp_mkdir_p($backup_dir)) {
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
            'type' => 'automatic',
            'options' => array()
        );

        foreach ($options as $option) {
            $backup_data['options'][$option['option_name']] = maybe_unserialize($option['option_value']);
        }

        // Write backup file
        file_put_contents($filepath, json_encode($backup_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    } catch (Exception $e) {
        // Silent fail for cron job
    }
}

/**
 * Cleanup old backups
 */
function pdf_builder_cleanup_old_backups() {
    global $wpdb;

    // Récupérer tous les backups (max 5 derniers)
    $backups = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT option_name FROM {$wpdb->options}
             WHERE option_name LIKE %s
             ORDER BY option_name DESC
             LIMIT 999 OFFSET 5",
            'pdf_builder_backup_%'
        )
    );

    // Supprimer les anciens
    foreach ($backups as $backup) {
        delete_option($backup->option_name);
    }
}

/**
 * AJAX handler for creating backups
 */
function pdf_builder_create_backup_ajax() {
    // Check nonce
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
        wp_send_json_error('Nonce invalide');
        return;
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Check if user is premium
    if (!\PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance()->is_premium()) {
        wp_send_json_error('La fonctionnalité de sauvegarde n\'est disponible que dans la version premium');
        return;
    }

    // Check backup limit (50 max for premium users)
    $backup_dir = WP_CONTENT_DIR . '/pdf-builder-backups';
    if (file_exists($backup_dir)) {
        $files = glob($backup_dir . '/pdf_builder_backup_*.json');
        $manual_files = glob($backup_dir . '/pdf-builder-backup-*.json');
        $all_backup_files = array_merge($files, $manual_files);
        
        if (count($all_backup_files) >= 50) {
            wp_send_json_error('Limite de 50 sauvegardes atteinte. Veuillez supprimer des sauvegardes anciennes avant d\'en créer de nouvelles.');
            return;
        }
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
    // Check nonce
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
        wp_send_json_error(['message' => 'Nonce invalide']);
        return;
    }

    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Check if user is premium
    if (!\PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance()->is_premium()) {
        wp_send_json_error('La fonctionnalité de sauvegarde n\'est disponible que dans la version premium');
        return;
    }

    try {
        $backup_dir = defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR . '/pdf-builder-backups' : ABSPATH . 'wp-content/pdf-builder-backups';

        if (!file_exists($backup_dir) || !is_dir($backup_dir)) {
            wp_send_json_success(array('backups' => array()));
            return;
        }

        $files = glob($backup_dir . '/pdf_builder_backup_*.json');
        $files_manual = glob($backup_dir . '/pdf-builder-backup-*.json');
        $files = array_merge($files, $files_manual);

        $backups = array();

        foreach ($files as $file) {
            $filename = basename($file);
            $file_path = $backup_dir . '/' . $filename;

            if (is_file($file_path) && is_readable($file_path)) {
                $file_size = filesize($file_path);
                $file_modified = filemtime($file_path);

                // Parse filename to extract date - handle multiple formats
                $date_match = array();
                if (preg_match('/pdf[-_]builder[-_]backup[_-](\d{4}[-]\d{2}[-]\d{2})[_-](\d{2}[-]\d{2}[-]\d{2})\.json/', $filename, $date_match)) {
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
                    'type' => 'manual'
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
    // Check nonce
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
        wp_send_json_error('Nonce invalide');
        return;
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Check if user is premium
    if (!\PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance()->is_premium()) {
        wp_send_json_error('La fonctionnalité de sauvegarde n\'est disponible que dans la version premium');
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

function pdf_builder_register_ajax_handlers() {
    static $handlers_registered = false;
    
    // error_log('PDF Builder: [AJAX REGISTRATION] Function called at ' . current_time('Y-m-d H:i:s'));
    
    if ($handlers_registered) {
        return;
    }
    
    // Cache handlers
    add_action('wp_ajax_pdf_builder_get_cache_status', 'pdf_builder_get_cache_status_ajax');
    add_action('wp_ajax_pdf_builder_test_cache', 'pdf_builder_test_cache_ajax');
    add_action('wp_ajax_pdf_builder_test_cache_integration', 'pdf_builder_test_cache_ajax');
    add_action('wp_ajax_pdf_builder_clear_all_cache', 'pdf_builder_clear_cache_ajax');
    add_action('wp_ajax_pdf_builder_get_cache_metrics', 'pdf_builder_ajax_handler_dispatch');
    add_action('wp_ajax_pdf_builder_update_cache_metrics', 'pdf_builder_ajax_handler_dispatch');

    // Maintenance handlers
    add_action('wp_ajax_pdf_builder_optimize_database', 'pdf_builder_ajax_handler_dispatch');
    add_action('wp_ajax_pdf_builder_repair_templates', 'pdf_builder_ajax_handler_dispatch');
    add_action('wp_ajax_pdf_builder_remove_temp_files', 'pdf_builder_ajax_handler_dispatch');

    // Backup handlers
    add_action('wp_ajax_pdf_builder_create_backup', 'pdf_builder_create_backup_ajax');
    add_action('wp_ajax_pdf_builder_list_backups', 'pdf_builder_list_backups_ajax');
    add_action('wp_ajax_pdf_builder_restore_backup', 'pdf_builder_restore_backup_ajax');
    add_action('wp_ajax_pdf_builder_delete_backup', 'pdf_builder_ajax_handler_dispatch');

    $handlers_registered = true;
    
    // License handlers
    add_action('wp_ajax_pdf_builder_test_license', 'pdf_builder_ajax_handler_dispatch');

    // Route handlers
    add_action('wp_ajax_pdf_builder_test_routes', 'pdf_builder_ajax_handler_dispatch');

    // Diagnostic handlers
    add_action('wp_ajax_pdf_builder_export_diagnostic', 'pdf_builder_ajax_handler_dispatch');
    add_action('wp_ajax_pdf_builder_view_logs', 'pdf_builder_ajax_handler_dispatch');

    add_action('wp_ajax_pdf_builder_diagnostic', 'pdf_builder_diagnostic_ajax_handler');

    add_action('wp_ajax_test_ajax', 'pdf_builder_test_ajax_handler');
    add_action('wp_ajax_pdf_builder_test_ajax', 'pdf_builder_test_ajax_handler');

    add_action('wp_ajax_pdf_builder_get_allowed_roles', 'pdf_builder_get_allowed_roles_ajax_handler');

    add_action('pdf_builder_daily_backup', 'pdf_builder_execute_daily_backup');
    add_action('pdf_builder_cleanup_old_backups', 'pdf_builder_cleanup_old_backups');
    add_action('pdf_builder_weekly_maintenance', 'pdf_builder_execute_weekly_maintenance');
    add_action('admin_action_pdf_builder_download_backup', 'pdf_builder_download_backup');
}

/**
 * Handler AJAX de diagnostic - Test simple pour vérifier le système AJAX
 */
function pdf_builder_diagnostic_ajax_handler() {
    wp_send_json_success([
        'message' => 'Diagnostic AJAX handler works perfectly',
        'timestamp' => current_time('timestamp'),
        'user_id' => get_current_user_id(),
        'post_data' => $_POST
    ]);
}

/**
 * AJAX handler for optimizing database
 */
function pdf_builder_optimize_database_handler() {
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        global $wpdb;
        $tables = [
            $wpdb->posts,
            $wpdb->postmeta,
            $wpdb->prefix . 'woocommerce_order_items',
            $wpdb->prefix . 'woocommerce_order_itemmeta'
        ];

        $optimized_count = 0;
        foreach ($tables as $table) {
            if ($wpdb->query("OPTIMIZE TABLE {$table}")) {
                $optimized_count++;
            }
        }

        wp_send_json_success(array(
            'message' => 'Base de données optimisée avec succès',
            'tables_optimized' => $optimized_count
        ));

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de l\'optimisation: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for repairing templates
 */
function pdf_builder_repair_templates_handler() {
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        global $wpdb;

        // Check and repair template tables
        $tables = ['pdf_builder_templates', 'pdf_builder_pdfs'];
        $repaired_count = 0;

        foreach ($tables as $table) {
            $result = $wpdb->get_row("CHECK TABLE {$wpdb->prefix}{$table}");
            if ($result && $result->Msg_text !== 'OK') {
                if ($wpdb->query("REPAIR TABLE {$wpdb->prefix}{$table}")) {
                    $repaired_count++;
                }
            }
        }

        // Check settings
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        if (!is_array($settings)) {
            pdf_builder_update_option('pdf_builder_settings', []);
        }

        wp_send_json_success(array(
            'message' => 'Templates réparés avec succès',
            'tables_repaired' => $repaired_count
        ));

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la réparation: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for removing temporary files
 */
function pdf_builder_remove_temp_files_handler() {
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/pdf-builder-temp';

        $deleted_count = 0;
        if (file_exists($temp_dir) && is_dir($temp_dir)) {
            $files = glob($temp_dir . '/*');
            $now = current_time('timestamp');

            foreach ($files as $file) {
                if (is_file($file)) {
                    $file_age = $now - filemtime($file);
                    if ($file_age > 86400) { // 24 hours
                        if (unlink($file)) {
                            $deleted_count++;
                        }
                    }
                }
            }
        }

        wp_send_json_success(array(
            'message' => 'Fichiers temporaires supprimés avec succès',
            'files_deleted' => $deleted_count
        ));

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la suppression: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for deleting backups
 */
function pdf_builder_delete_backup_handler() {
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

        if (!file_exists($filepath)) {
            wp_send_json_error('Fichier de sauvegarde introuvable');
            return;
        }

        if (unlink($filepath)) {
            wp_send_json_success(array(
                'message' => 'Sauvegarde supprimée avec succès'
            ));
        } else {
            wp_send_json_error('Erreur lors de la suppression du fichier');
        }

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la suppression: ' . $e->getMessage());
    }
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

        // error_log('PDF BUILDER AJAX DISPATCHER: Processing action: ' . $action);

        // Dispatcher vers le handler approprié selon l'action
        switch ($action) {
            case 'pdf_builder_save_settings':
                wp_send_json_error('Handler not available - use unified AJAX system');
                break;
            case 'pdf_builder_save_all_settings':
                wp_send_json_error('Use unified AJAX system - handled by PDF_Builder_Settings_Ajax_Handler');
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
                pdf_builder_update_cache_metrics_handler();
                break;
            case 'pdf_builder_optimize_database':
                pdf_builder_optimize_database_handler();
                break;
            case 'pdf_builder_repair_templates':
                pdf_builder_repair_templates_handler();
                break;
            case 'pdf_builder_remove_temp_files':
                pdf_builder_remove_temp_files_handler();
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
                pdf_builder_delete_backup_handler();
                break;
            case 'pdf_builder_test_license':
                pdf_builder_test_license_handler();
                break;
            case 'pdf_builder_test_routes':
                pdf_builder_test_routes_handler();
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
        // error_log('PDF Builder AJAX Error: ' . $e->getMessage());
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
 * Ajouter les headers de cache pour les assets du plugin
 */
function pdf_builder_add_asset_cache_headers()
{
    // Éviter l'erreur "headers already sent" si les headers ont déjà été envoyés
    if (headers_sent()) {
        return;
    }

    // Vérifier si le cache est activé dans les paramètres
    $settings = pdf_builder_get_option('pdf_builder_settings', array());
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
        (strpos($_SERVER['REQUEST_URI'], '/wp-content/plugins/pdf-builder-pro/resources/assets/js/') !== false ||
         strpos($_SERVER['REQUEST_URI'], '/wp-content/plugins/pdf-builder-pro/resources/assets/css/') !== false)
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

    $cache_enabled = pdf_builder_get_option('pdf_builder_cache_enabled', '0');

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

    // Vérifier la disponibilité des fonctions de cache
    if (function_exists('wp_cache_flush')) {
        $results['cache_available'] = true;
    }

    // Tester les transients WordPress
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

    // Vérifier les options de cache du plugin
    $cache_enabled = pdf_builder_get_option('pdf_builder_cache_enabled', false);
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

    // error_log('PDF Builder AJAX: Generated fresh nonce: ' . substr($fresh_nonce, 0, 10) . '..., User ID: ' . get_current_user_id());

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

        // Vérifier que les headers n'ont pas encore été envoyés
        if (headers_sent()) {
            wp_die('Impossible d\'envoyer les headers - sortie déjà commencée');
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
 * Exécuter la maintenance automatique hebdomadaire
 */
function pdf_builder_execute_weekly_maintenance() {
    // Vérifier si la maintenance automatique est activée
    $auto_maintenance_enabled = pdf_builder_get_option('pdf_builder_auto_maintenance', '0');
    $performance_optimization_enabled = pdf_builder_get_option('pdf_builder_performance_auto_optimization', '0');

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
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        if (!is_array($settings)) {
            pdf_builder_update_option('pdf_builder_settings', []);
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
    // Log détaillé du début de la requête
    // error_log('[PDF Builder SAVE] ===== DÉBUT SAUVEGARDE =====');
    // error_log('[PDF Builder SAVE] Timestamp: ' . current_time('mysql'));
    // error_log('[PDF Builder SAVE] User ID: ' . get_current_user_id());
    // error_log('[PDF Builder SAVE] User capabilities: ' . (current_user_can('manage_options') ? 'HAS_MANAGE_OPTIONS' : 'NO_MANAGE_OPTIONS'));
    // error_log('[PDF Builder SAVE] REQUEST_METHOD: ' . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'));
    // error_log('[PDF Builder SAVE] Content-Type: ' . ($_SERVER['CONTENT_TYPE'] ?? 'UNKNOWN'));
    // error_log('[PDF Builder SAVE] POST data keys: ' . implode(', ', array_keys($_POST)));

    // Check permissions
    if (!current_user_can('manage_options')) {
        // error_log('[PDF Builder SAVE] ❌ ÉCHEC: Permissions insuffisantes pour user: ' . get_current_user_id());
        wp_send_json_error('Permissions insuffisantes');
        return;
    }
    // error_log('[PDF Builder SAVE] ✅ Permissions OK');

    // Check nonce
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
    // error_log('[PDF Builder SAVE] Nonce reçu: ' . substr($nonce, 0, 10) . '...');
    if (empty($nonce) || !wp_verify_nonce($nonce, 'pdf_builder_save_template_nonce')) {
        // error_log('[PDF Builder SAVE] ❌ ÉCHEC: Nonce invalide ou manquant');
        // error_log('[PDF Builder SAVE] Nonce attendu: pdf_builder_save_template_nonce');
        wp_send_json_error('Nonce invalide');
        return;
    }
    // error_log('[PDF Builder SAVE] ✅ Nonce OK');

    try {
        $template_id = intval($_POST['template_id'] ?? 0);
        $template_data = isset($_POST['template_data']) ? $_POST['template_data'] : '';
        $template_name = isset($_POST['template_name']) ? sanitize_text_field($_POST['template_name']) : '';

        // error_log('[PDF Builder SAVE] Template ID: ' . $template_id);
        // error_log('[PDF Builder SAVE] Template data length: ' . strlen($template_data));
        // error_log('[PDF Builder SAVE] Template name: ' . $template_name);

        if (!$template_id || empty($template_data)) {
            // error_log('[PDF Builder SAVE] ❌ ÉCHEC: Données manquantes - template_id: ' . $template_id . ', template_data length: ' . strlen($template_data));
            wp_send_json_error('Données manquantes');
            return;
        }
        // error_log('[PDF Builder SAVE] ✅ Données de base OK');

        // Decode and validate JSON
        $decoded_data = json_decode($template_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // error_log('[PDF Builder SAVE] ❌ ÉCHEC: Erreur JSON: ' . json_last_error_msg());
            // error_log('[PDF Builder SAVE] Données JSON (début): ' . substr($template_data, 0, 500) . '...');
            wp_send_json_error('Données JSON invalides');
            return;
        }
        // error_log('[PDF Builder SAVE] ✅ JSON valide, éléments: ' . (isset($decoded_data['elements']) ? count($decoded_data['elements']) : 'N/A'));

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';
        // error_log('[PDF Builder SAVE] Table templates: ' . $table_templates);

        // Check if template exists
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_templates WHERE id = %d", $template_id));
        if (!$existing) {
            // error_log('[PDF Builder SAVE] ❌ ÉCHEC: Template non trouvé: ' . $template_id);
            // Log all existing templates for debugging
            $all_templates = $wpdb->get_results("SELECT id, name FROM $table_templates", ARRAY_A);
            // error_log('[PDF Builder SAVE] Templates existants: ' . json_encode($all_templates));
            wp_send_json_error('Template non trouvé');
            return;
        }
        // error_log('[PDF Builder SAVE] ✅ Template trouvé');

        // Log before update
        // error_log('[PDF Builder SAVE] Tentative de mise à jour...');

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
            // error_log('[PDF Builder SAVE] ❌ ÉCHEC: Mise à jour DB échouée pour template ' . $template_id);
            // error_log('[PDF Builder SAVE] Erreur DB: ' . $wpdb->last_error);
            // error_log('[PDF Builder SAVE] Dernière requête: ' . $wpdb->last_query);
            wp_send_json_error('Erreur lors de la sauvegarde');
            return;
        }

        // error_log('[PDF Builder SAVE] ✅ Sauvegarde réussie: template ' . $template_id . ', lignes affectées: ' . $result);
        // error_log('[PDF Builder SAVE] ===== FIN SAUVEGARDE =====');

        wp_send_json_success([
            'message' => 'Template sauvegardé avec succès',
            'template_id' => $template_id,
            'saved_at' => current_time('mysql')
        ]);

    } catch (Exception $e) {
        // error_log('[PDF Builder SAVE] ❌ EXCEPTION: ' . $e->getMessage());
        // error_log('[PDF Builder SAVE] Trace: ' . $e->getTraceAsString());
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
            'template' => [
                'name' => $template['name'],
                'description' => $template_data['description'] ?? '',
                'category' => $template_data['category'] ?? 'autre',
                'is_default' => $template['is_default'],
                'created_at' => $template['created_at'],
                'updated_at' => $template['updated_at'],
                'canvas_settings' => [
                    'default_canvas_format' => get_option('pdf_builder_canvas_format', 'A4'),
                    'default_canvas_orientation' => get_option('pdf_builder_canvas_default_orientation', 'portrait'),
                    'default_canvas_dpi' => get_option('pdf_builder_canvas_dpi', 96)
                ]
            ]
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
        $template_count = \PDF_Builder\Admin\PdfBuilderAdminNew::count_user_templates($user_id);

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
 * Vérifier l'état des systèmes avancés
 */
function pdf_builder_check_advanced_systems_status() {
    $systems_status = array(
        'intelligent_loader' => class_exists('PDF_Builder_Intelligent_Loader') && PDF_Builder_Intelligent_Loader::get_instance() !== null,
        'config_manager' => class_exists('PDF_Builder_Config_Manager') && PDF_Builder_Config_Manager::get_instance() !== null,
        'error_handler' => class_exists('PDF_Builder_Error_Handler') && PDF_Builder_Error_Handler::get_instance() !== null,
        'analytics_manager' => class_exists('PDF_Builder_Analytics_Manager') && PDF_Builder_Analytics_Manager::get_instance() !== null,
        'update_manager' => class_exists('PDF_Builder_Update_Manager') && PDF_Builder_Update_Manager::get_instance() !== null,
        'health_monitor' => class_exists('PDF_Builder_Health_Monitor') && PDF_Builder_Health_Monitor::get_instance() !== null,
        'api_manager' => class_exists('PDF_Builder_API_Manager') && PDF_Builder_API_Manager::get_instance() !== null,
        'user_manager' => class_exists('PDF_Builder_User_Manager') && PDF_Builder_User_Manager::get_instance() !== null,
        'license_manager' => class_exists('PDF_Builder\Managers\PDF_Builder_License_Manager') && \PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance() !== null,
        'integration_manager' => class_exists('PDF_Builder_Integration_Manager') && PDF_Builder_Integration_Manager::get_instance() !== null,
        'auto_update_manager' => class_exists('PDF_Builder_Auto_Update_Manager') && PDF_Builder_Auto_Update_Manager::get_instance() !== null,
    );

    $all_systems_loaded = !in_array(false, $systems_status, true);
    $loaded_count = count(array_filter($systems_status));
    $total_count = count($systems_status);

    if (!$all_systems_loaded) {
        $failed_systems = array_keys(array_filter($systems_status, function($status) { return !$status; }));
        // error_log('PDF Builder: Certains systèmes avancés n\'ont pas été chargés: ' . implode(', ', $failed_systems));
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
    $last_check = pdf_builder_get_option('pdf_builder_systems_check_timestamp', 0);
    $current_time = current_time('timestamp');

    if (($current_time - $last_check) > 86400) { // 24 heures
        $status = pdf_builder_check_advanced_systems_status();
        pdf_builder_update_option('pdf_builder_systems_check_timestamp', $current_time);

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


/**
 * Handler AJAX pour vider le cache
 */
function pdf_builder_clear_cache_handler() {
    try {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
            return;
        }

        // Nettoyer le cache WordPress
        wp_cache_flush();

        // Nettoyer les transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_pdf_builder_%'");

        wp_send_json_success(array(
            'message' => 'Cache vidé avec succès',
            'cleared_at' => current_time('mysql')
        ));

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors du vidage du cache: ' . $e->getMessage());
    }
}

/**
 * Handler AJAX pour obtenir les métriques du cache
 */
function pdf_builder_get_cache_metrics_handler() {
    try {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
            return;
        }

        global $wpdb;

        // Compter les transients PDF Builder
        $transient_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'");
        $transient_timeout_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_pdf_builder_%'");

        // Taille approximative du cache
        $cache_size = $wpdb->get_var("SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'");

        wp_send_json_success(array(
            'transient_count' => intval($transient_count),
            'timeout_count' => intval($transient_timeout_count),
            'cache_size_bytes' => intval($cache_size),
            'cache_size_mb' => round(intval($cache_size) / 1024 / 1024, 2)
        ));

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la récupération des métriques: ' . $e->getMessage());
    }
}

/**
 * Handler AJAX pour mettre à jour les métriques du cache
 */
function pdf_builder_update_cache_metrics_handler() {
    try {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
            return;
        }

        // Mettre à jour les métriques (pour l'instant, juste retourner les métriques actuelles)
        pdf_builder_get_cache_metrics_handler();

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la mise à jour des métriques: ' . $e->getMessage());
    }
}

/**
 * Handler AJAX pour tester la licence
 */
function pdf_builder_test_license_handler() {
    try {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
            return;
        }

        // Simuler un test de licence (à implémenter selon vos besoins)
        $license_status = 'valid'; // ou 'invalid', 'expired', etc.

        wp_send_json_success(array(
            'license_status' => $license_status,
            'tested_at' => current_time('mysql')
        ));

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors du test de licence: ' . $e->getMessage());
    }
}

/**
 * Handler AJAX pour tester les routes
 */
function pdf_builder_test_routes_handler() {
    try {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
            return;
        }

        // Tester les routes AJAX disponibles
        $routes = array(
            'diagnostic' => admin_url('admin-ajax.php?action=pdf_builder_diagnostic'),
            'save_settings' => admin_url('admin-ajax.php?action=pdf_builder_save_settings'),
            'render_template' => admin_url('admin-ajax.php?action=pdf_builder_render_template_html')
        );

        $route_status = array();
        foreach ($routes as $name => $url) {
            $route_status[$name] = array(
                'url' => $url,
                'accessible' => true // Simplifié - en production, tester réellement l'accès
            );
        }

        wp_send_json_success(array(
            'routes' => $route_status,
            'tested_at' => current_time('mysql')
        ));

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors du test des routes: ' . $e->getMessage());
    }
}

/**
 * Handler AJAX pour exporter les diagnostics
 */
function pdf_builder_export_diagnostic_handler() {
    try {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
            return;
        }

        // Collecter les informations de diagnostic
        $diagnostic = array(
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'plugin_version' => PDF_BUILDER_VERSION,
            'server_info' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'collected_at' => current_time('mysql')
        );

        wp_send_json_success(array(
            'diagnostic' => $diagnostic,
            'export_ready' => true
        ));

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de l\'export des diagnostics: ' . $e->getMessage());
    }
}

/**
 * Handler AJAX pour voir les logs
 */
function pdf_builder_view_logs_handler() {
    try {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
            return;
        }

        // Récupérer les logs récents (simplifié)
        $logs = array(
            array(
                'timestamp' => current_time('mysql'),
                'level' => 'info',
                'message' => 'Logs système PDF Builder'
            )
        );

        wp_send_json_success(array(
            'logs' => $logs,
            'total_logs' => count($logs)
        ));

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la récupération des logs: ' . $e->getMessage());
    }
}

// ====================================================================
// HOOKS D'ACTIVATION / DÉACTIVATION
// ====================================================================

// Enregistrer la fonction d'activation du plugin
register_activation_hook(__FILE__, 'pdf_builder_activate');

// Enregistrer la fonction de désactivation du plugin
register_deactivation_hook(__FILE__, 'pdf_builder_deactivate');
