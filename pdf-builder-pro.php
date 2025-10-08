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

// Sécurité : empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

// Définir le répertoire du plugin
if (!defined('PDF_BUILDER_PLUGIN_DIR')) {
    define('PDF_BUILDER_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// Charger le système de debug helper tôt
require_once PDF_BUILDER_PLUGIN_DIR . 'includes/utilities/PDF_Builder_Debug_Helper.php';

// 

// Définir l'URL des assets
if (!defined('PDF_BUILDER_PRO_ASSETS_URL')) {
    define('PDF_BUILDER_PRO_ASSETS_URL', plugin_dir_url(__FILE__) . 'assets/');
}

// Définir le chemin des assets
if (!defined('PDF_BUILDER_PRO_ASSETS_PATH')) {
    define('PDF_BUILDER_PRO_ASSETS_PATH', PDF_BUILDER_PLUGIN_DIR . 'assets/');
}

// Définir la version du plugin
if (!defined('PDF_BUILDER_PRO_VERSION')) {
    define('PDF_BUILDER_PRO_VERSION', '1.0.0');
}

// Définir la version du plugin (alias pour compatibilité)
if (!defined('PDF_BUILDER_VERSION')) {
    define('PDF_BUILDER_VERSION', '1.0.0');
}

// Définir le chemin des includes
if (!defined('PDF_BUILDER_PRO_INCLUDES_PATH')) {
    define('PDF_BUILDER_PRO_INCLUDES_PATH', PDF_BUILDER_PLUGIN_DIR . 'includes/');
}

// Charger les utilitaires de traduction
require_once PDF_BUILDER_PLUGIN_DIR . 'includes/managers/PDF_Builder_Translation_Utils.php';

// Inclure TCPDF automatiquement si pas déjà présent
if (!class_exists('TCPDF')) {
    $tcpdf_path = PDF_BUILDER_PLUGIN_DIR . 'lib/tcpdf/tcpdf.php';
    if (file_exists($tcpdf_path)) {
        require_once $tcpdf_path;
    }
}

// Inclusion différée de la classe principale pour éviter les timeouts
function pdf_builder_load_core() {
    static $loaded = false;
    if (!$loaded) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'includes/classes/PDF_Builder_Core.php';

        // Charger les managers essentiels en premier pour éviter les dépendances circulaires
        require_once PDF_BUILDER_PLUGIN_DIR . 'includes/managers/PDF_Builder_Cache_Manager.php';
        require_once PDF_BUILDER_PLUGIN_DIR . 'includes/utilities/PDF_Builder_Logger.php';

        // Charger les managers canvas (récupérés depuis l'archive)
        require_once PDF_BUILDER_PLUGIN_DIR . 'includes/managers/PDF_Builder_Canvas_Elements_Manager.php';
        require_once PDF_BUILDER_PLUGIN_DIR . 'includes/managers/PDF_Builder_Canvas_Interactions_Manager.php';
        require_once PDF_BUILDER_PLUGIN_DIR . 'includes/managers/PDF_Builder_Drag_Drop_Manager.php';
        require_once PDF_BUILDER_PLUGIN_DIR . 'includes/managers/PDF_Builder_Resize_Manager.php';

        $loaded = true;
    }
}

// Hook d'activation - CRÉATION DES TABLES DE BASE DE DONNÉES
function pdf_builder_activate_plugin() {
    // Désactiver complètement l'affichage des erreurs PHP pour éviter tout output
    $display_errors = ini_get('display_errors');
    $error_reporting = error_reporting();
    ini_set('display_errors', '0');
    error_reporting(0);

    // Supprimer tout output potentiel pour éviter les erreurs "headers already sent"
    if (ob_get_level()) {
        ob_end_clean();
    }
    ob_start();

    try {
        // Marquer comme activé
        if (!get_option('pdf_builder_activated')) {
            update_option('pdf_builder_activated', true);
            update_option('pdf_builder_activation_time', time());
        }

        // CRÉER LES TABLES DE BASE DE DONNÉES
        pdf_builder_create_database_tables();

        // INSTALLER TCPDF AUTOMATIQUEMENT
        pdf_builder_install_tcpdf();

        // Supprimer tout output capturé
        ob_end_clean();

    } catch (Exception $e) {
        // En cas d'erreur, nettoyer et logger
        if (ob_get_level()) {
            ob_end_clean();
        }
        error_log('PDF Builder Pro activation error: ' . $e->getMessage());
    } finally {
        // Restaurer l'affichage des erreurs
        ini_set('display_errors', $display_errors);
        error_reporting($error_reporting);
    }
}

// Fonction pour installer TCPDF automatiquement
function pdf_builder_install_tcpdf() {
    $vendor_dir = PDF_BUILDER_PLUGIN_DIR . 'vendor';
    $tcpdf_dir = $vendor_dir . '/tecnickcom/tcpdf';
    $tcpdf_file = $tcpdf_dir . '/tcpdf.php';

    // Si TCPDF est déjà installé, rien à faire
    if (file_exists($tcpdf_file) && class_exists('TCPDF')) {
        return true;
    }

    // Créer les dossiers nécessaires
    if (!is_dir($vendor_dir)) {
        mkdir($vendor_dir, 0755, true);
    }
    if (!is_dir($tcpdf_dir)) {
        mkdir($tcpdf_dir, 0755, true);
    }

    // Télécharger TCPDF
    $tcpdf_url = 'https://github.com/tecnickcom/TCPDF/archive/refs/tags/6.6.2.zip';
    $zip_file = PDF_BUILDER_PLUGIN_DIR . 'tcpdf_temp.zip';

    $zip_content = @file_get_contents($tcpdf_url);
    if ($zip_content === false) {
        error_log('PDF Builder Pro: Impossible de télécharger TCPDF');
        return false;
    }

    file_put_contents($zip_file, $zip_content);

    // Extraire le ZIP
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($zip_file) === TRUE) {
            $zip->extractTo($tcpdf_dir);
            $zip->close();

            // Déplacer les fichiers du sous-dossier
            $extracted_dir = $tcpdf_dir . '/TCPDF-6.6.2';
            if (is_dir($extracted_dir)) {
                $files = scandir($extracted_dir);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        rename($extracted_dir . '/' . $file, $tcpdf_dir . '/' . $file);
                    }
                }
                rmdir($extracted_dir);
            }

            // Nettoyer
            if (file_exists($zip_file)) {
                unlink($zip_file);
            }

            // Vérifier l'installation
            if (file_exists($tcpdf_file)) {
                return true;
            }
        }
    }

    // Nettoyer en cas d'erreur
    if (file_exists($zip_file)) {
        unlink($zip_file);
    }

    error_log('PDF Builder Pro: Échec de l\'installation automatique de TCPDF');
    return false;
}

// Fonction pour créer les tables de base de données
function pdf_builder_create_database_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_prefix = $wpdb->prefix . 'pdf_builder_';

    // Capturer tout output potentiel pour éviter les erreurs "headers already sent"
    ob_start();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    // Créer la table templates
    $table_name = $table_prefix . 'templates';
    $sql = "CREATE TABLE $table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        description text,
        type varchar(50) NOT NULL DEFAULT 'pdf',
        content longtext,
        settings longtext,
        status varchar(20) NOT NULL DEFAULT 'active',
        category_id bigint(20) unsigned DEFAULT NULL,
        author_id bigint(20) unsigned NOT NULL,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY type_status (type, status),
        KEY author_id (author_id),
        KEY category_id (category_id)
    ) $charset_collate;";
    dbDelta($sql);

    // Créer la table documents
    $table_name = $table_prefix . 'documents';
    $sql = "CREATE TABLE $table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        template_id bigint(20) unsigned NOT NULL,
        title varchar(255) NOT NULL,
        data longtext,
        file_path varchar(500),
        file_size bigint(20) unsigned DEFAULT NULL,
        status varchar(20) NOT NULL DEFAULT 'pending',
        workflow_status varchar(20) NOT NULL DEFAULT 'draft',
        author_id bigint(20) unsigned NOT NULL,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY template_id (template_id),
        KEY status (status),
        KEY workflow_status (workflow_status),
        KEY author_id (author_id)
    ) $charset_collate;";
    dbDelta($sql);

    // Créer la table categories
    $table_name = $table_prefix . 'categories';
    $sql = "CREATE TABLE $table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        description text,
        parent_id bigint(20) unsigned DEFAULT NULL,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY parent_id (parent_id)
    ) $charset_collate;";
    dbDelta($sql);

    // Créer la table logs
    $table_name = $table_prefix . 'logs';
    $sql = "CREATE TABLE $table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        level varchar(20) NOT NULL DEFAULT 'info',
        message text NOT NULL,
        context longtext,
        user_id bigint(20) unsigned DEFAULT NULL,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY level (level),
        KEY user_id (user_id),
        KEY created_at (created_at)
    ) $charset_collate;";
    dbDelta($sql);

    // Créer la table cache
    $table_name = $table_prefix . 'cache';
    $sql = "CREATE TABLE $table_name (
        cache_key varchar(255) NOT NULL,
        cache_value longtext,
        expiration datetime DEFAULT NULL,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (cache_key),
        KEY expiration (expiration)
    ) $charset_collate;";
    dbDelta($sql);

    // Créer la table settings
    $table_name = $table_prefix . 'settings';
    $sql = "CREATE TABLE $table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        setting_key varchar(255) NOT NULL,
        setting_value longtext,
        setting_type varchar(50) DEFAULT 'string',
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY setting_key (setting_key)
    ) $charset_collate;";
    dbDelta($sql);

    // Créer la table template_versions
    $table_name = $table_prefix . 'template_versions';
    $sql = "CREATE TABLE $table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        template_id bigint(20) unsigned NOT NULL,
        version_number int(11) NOT NULL,
        content longtext,
        settings longtext,
        author_id bigint(20) unsigned NOT NULL,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY template_id (template_id),
        KEY version_number (version_number),
        KEY author_id (author_id)
    ) $charset_collate;";
    dbDelta($sql);

    update_option('pdf_builder_db_version', '1.0.0');

    // Supprimer tout output capturé pour éviter les erreurs "headers already sent"
    ob_end_clean();
}

// Hook de désactivation ULTRA-LÉGER
function pdf_builder_deactivate_plugin() {
    // Désactiver seulement si nécessaire, sans charger le Core
    delete_option('pdf_builder_activated');
    delete_option('pdf_builder_activation_time');
}

register_activation_hook(__FILE__, 'pdf_builder_activate_plugin');
register_deactivation_hook(__FILE__, 'pdf_builder_deactivate_plugin');

// CHARGEMENT ULTRA-PARESSEUX - RIEN ne se passe ici
// Le plugin ne fait ABSOLUMENT rien tant qu'on n'accède pas à ses pages/fonctionnalités

// Hook unique pour déclencher le chargement quand nécessaire
add_action('plugins_loaded', 'pdf_builder_lazy_load', 9999);

// Hook pour gérer les téléchargements PDF WooCommerce
add_action('init', 'pdf_builder_handle_pdf_download', 1);

// Chargement immédiat du menu admin pour éviter les problèmes de timing
// add_action('admin_menu', 'pdf_builder_register_admin_menu_immediate', 10);

// Fonction pour gérer les téléchargements PDF WooCommerce
function pdf_builder_handle_pdf_download() {
    if (!isset($_GET['pdf_builder_action']) || $_GET['pdf_builder_action'] !== 'download_order_pdf') {
        return;
    }

    // Charger le core si nécessaire
    if (!function_exists('pdf_builder_load_core')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'bootstrap.php';
        pdf_builder_load_bootstrap();
    }

    $core = PDF_Builder_Core::getInstance();
    $woocommerce_integration = $core->get_woocommerce_integration();

    if ($woocommerce_integration) {
        $woocommerce_integration->handle_pdf_download();
    }
}

function pdf_builder_lazy_load() {
    // Vérifier si le plugin est activé via option
    $is_enabled = get_option('pdf_builder_enabled', true); // Par défaut activé
    if (!$is_enabled) {
        return; // Plugin désactivé
    }

    // Charger config.php en premier (contient pdf_builder_should_load)
    require_once PDF_BUILDER_PLUGIN_DIR . 'includes/config.php';

    // Charger l'internationalisation du frontend
    require_once PDF_BUILDER_PLUGIN_DIR . 'includes/class-pdf-builder-frontend-i18n.php';

    // Utiliser la fonction optimisée de détection
    if (pdf_builder_should_load()) {
        // Charger le bootstrap seulement quand nécessaire
        require_once PDF_BUILDER_PLUGIN_DIR . 'bootstrap.php';
        pdf_builder_load_bootstrap();
    }
    // Sinon: RIEN - plugin totalement dormant
}