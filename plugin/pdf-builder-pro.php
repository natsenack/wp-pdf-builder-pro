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
    error_log('PDF Builder Pro: Plugin loading during AJAX request');
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
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY name (name)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    update_option('pdf_builder_version', pdf_builder_get_version());

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
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY name (name)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
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
    error_log('PDF Builder Pro: Registering AJAX handlers on init hook');

    // Wizard supprimé - handlers désactivés
    // // Test AJAX
    // add_action('wp_ajax_test_ajax', function() {
    //     error_log('PDF Builder Pro: test_ajax handler called');
    //     wp_send_json(['success' => true, 'message' => 'AJAX works']);
    // });

    // // Wizard steps
    // add_action('wp_ajax_pdf_builder_wizard_step', function() {
    //     error_log('PDF Builder Pro: wizard_step handler called');
    //     pdf_builder_handle_admin_post_ajax();
    // });

    // Preview images
    add_action('wp_ajax_nopriv_wp_pdf_preview_image', 'pdf_builder_handle_preview_ajax');
    add_action('wp_ajax_wp_pdf_preview_image', 'pdf_builder_handle_preview_ajax');

    error_log('PDF Builder Pro: AJAX handlers registered successfully');
}

/**
 * Handler pour admin_post AJAX (fallback)
 */
function pdf_builder_handle_admin_post_ajax() {
    error_log('PDF Builder Pro: admin_post AJAX handler called');

    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        error_log('PDF Builder Pro: Access denied');
        wp_die('Accès refusé');
    }

    $action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '';
    $step = isset($_POST['step']) ? sanitize_text_field($_POST['step']) : '';
    $data = isset($_POST['data']) ? $_POST['data'] : array();

    error_log('PDF Builder Pro: admin_post AJAX - action: ' . $action . ', step: ' . $step);

    $response = array('success' => false);

    if ($action === 'pdf_builder_wizard_step') {
        switch ($step) {
            case 'save_company':
                $response = pdf_builder_ajax_save_company_data($data);
                break;

            case 'create_template':
                $response = pdf_builder_ajax_create_template();
                break;

            case 'complete':
                update_option('pdf_builder_installed', true);
                $response = array('success' => true, 'message' => 'Installation terminée');
                break;

            default:
                $response = array('success' => false, 'message' => 'Étape inconnue: ' . $step);
        }
    } elseif ($action === 'pdf_builder_test_ajax') {
        $response = array('success' => true, 'message' => 'admin_post AJAX works!');
    }

    error_log('PDF Builder Pro: admin_post response: ' . print_r($response, true));

    wp_send_json($response);
}

/**
 * Sauvegarder les données entreprise via AJAX
 */
function pdf_builder_ajax_save_company_data($data) {
    error_log('PDF Builder Pro: Saving company data: ' . print_r($data, true));

    try {
        // Décoder les données JSON si nécessaire
        if (is_string($data)) {
            $data = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return array('success' => false, 'message' => 'Données JSON invalides');
            }
        }

        // Validation des données
        $required_fields = array('company_name', 'company_address', 'company_phone', 'company_email');
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return array('success' => false, 'message' => 'Champ requis manquant: ' . $field);
            }
        }

        // Sauvegarder les options
        update_option('pdf_builder_company_name', sanitize_text_field($data['company_name']));
        update_option('pdf_builder_company_address', sanitize_textarea_field($data['company_address']));
        update_option('pdf_builder_company_phone', sanitize_text_field($data['company_phone']));
        update_option('pdf_builder_company_email', sanitize_email($data['company_email']));

        if (!empty($data['company_logo'])) {
            update_option('pdf_builder_company_logo', esc_url_raw($data['company_logo']));
        }

        error_log('PDF Builder Pro: Company data saved successfully');

        return array('success' => true, 'message' => 'Données entreprise sauvegardées');

    } catch (Exception $e) {
        error_log('PDF Builder Pro: Error saving company data: ' . $e->getMessage());
        return array('success' => false, 'message' => 'Erreur lors de la sauvegarde: ' . $e->getMessage());
    }
}

/**
 * Créer un template par défaut via AJAX
 */
function pdf_builder_ajax_create_template() {
    error_log('PDF Builder Pro: Creating default template');

    try {
        // Créer un template par défaut simple
        $template_data = array(
            'name' => 'Template par défaut',
            'elements' => array(
                array(
                    'type' => 'text',
                    'content' => 'FACTURE',
                    'x' => 50,
                    'y' => 50,
                    'font_size' => 24,
                    'font_weight' => 'bold'
                )
            )
        );

        // Sauvegarder le template
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $wpdb->insert(
            $table_templates,
            array(
                'name' => $template_data['name'],
                'template_data' => json_encode($template_data)
            ),
            array('%s', '%s')
        );

        if ($wpdb->last_error) {
            error_log('PDF Builder Pro: Database error: ' . $wpdb->last_error);
            return array('success' => false, 'message' => 'Erreur base de données: ' . $wpdb->last_error);
        }

        error_log('PDF Builder Pro: Default template created successfully');

        return array('success' => true, 'message' => 'Template par défaut créé');

    } catch (Exception $e) {
        error_log('PDF Builder Pro: Error creating template: ' . $e->getMessage());
        return array('success' => false, 'message' => 'Erreur lors de la création du template: ' . $e->getMessage());
    }
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

    // Wizard supprimé - trop de problèmes non résolvables
    // if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
    //     $wizard_path = plugin_dir_path(__FILE__) . 'wizard.php';
    //     if (file_exists($wizard_path)) {
    //         require_once $wizard_path;
    //     }
    // }

    // Enregistrer les handlers AJAX au hook init
    add_action('init', 'pdf_builder_register_ajax_handlers');

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
