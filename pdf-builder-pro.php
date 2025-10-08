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

    try {
        // Marquer comme activé
        update_option('pdf_builder_activated', true);
        update_option('pdf_builder_activation_time', time());

        // NE PAS créer les tables automatiquement lors de l'activation
        // Cela sera fait plus tard via un hook admin_init sécurisé

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

// Fonction pour créer les tables de base de données
function pdf_builder_create_database_tables() {
    // Vérifications de sécurité avant toute opération de base de données
    if (!defined('ABSPATH') || !function_exists('dbDelta')) {
        error_log('PDF Builder Pro: Impossible de créer les tables - WordPress pas complètement chargé');
        return false;
    }

    global $wpdb;
    if (!isset($wpdb)) {
        error_log('PDF Builder Pro: Objet $wpdb non disponible');
        return false;
    }

    $charset_collate = $wpdb->get_charset_collate();
    $table_prefix = $wpdb->prefix . 'pdf_builder_';

    // Capturer tout output potentiel pour éviter les erreurs "headers already sent"
    ob_start();

    // Le require_once est maintenant protégé par la vérification function_exists('dbDelta') ci-dessus
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    // Vérifier que dbDelta est maintenant disponible
    if (!function_exists('dbDelta')) {
        ob_end_clean();
        error_log('PDF Builder Pro: Fonction dbDelta non disponible après inclusion');
        return false;
    }

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
        KEY author_id (author_id),
        KEY status (status),
        KEY type (type)
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

    // Créer la table user_settings
    $table_name = $table_prefix . 'user_settings';
    $sql = "CREATE TABLE $table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        user_id bigint(20) unsigned NOT NULL,
        setting_key varchar(255) NOT NULL,
        setting_value longtext,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_setting (user_id, setting_key),
        KEY user_id (user_id)
    ) $charset_collate;";

    dbDelta($sql);

    // Nettoyer l'output capturé
    ob_end_clean();

    return true;
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

    if (file_put_contents($zip_file, $zip_content) === false) {
        error_log('PDF Builder Pro: Impossible de sauvegarder TCPDF');
        return false;
    }

    // Extraire le ZIP
    if (!class_exists('ZipArchive')) {
        unlink($zip_file);
        error_log('PDF Builder Pro: ZipArchive non disponible');
        return false;
    }

    $zip = new ZipArchive();
    if ($zip->open($zip_file) !== true) {
        unlink($zip_file);
        error_log('PDF Builder Pro: Impossible d\'ouvrir le ZIP TCPDF');
        return false;
    }

    $zip->extractTo($vendor_dir);
    $zip->close();
    unlink($zip_file);

    // Déplacer le contenu extrait vers le bon endroit
    $extracted_dir = $vendor_dir . '/TCPDF-6.6.2';
    if (is_dir($extracted_dir)) {
        rename($extracted_dir, $tcpdf_dir);
    }

    // Vérifier que TCPDF est maintenant disponible
    if (file_exists($tcpdf_file)) {
        require_once($tcpdf_file);
        return class_exists('TCPDF');
    }

    error_log('PDF Builder Pro: Échec de l\'installation automatique de TCPDF');
    return false;
}

// Fonction de désactivation
function pdf_builder_deactivate_plugin() {
    // Désactiver seulement si nécessaire, sans charger le Core
    delete_option('pdf_builder_activated');
    delete_option('pdf_builder_activation_time');
}

// Hook d'activation sécurisé - NE FAIT QUE MARQUER LE PLUGIN COMME ACTIVÉ
register_activation_hook(__FILE__, 'pdf_builder_activate_plugin');
register_deactivation_hook(__FILE__, 'pdf_builder_deactivate_plugin');

// CHARGEMENT ULTRA-PARESSEUX - RIEN ne se passe ici
// Le plugin ne fait ABSOLUMENT rien tant qu'on n'accède pas à ses pages/fonctionnalités

// Hook unique pour déclencher le chargement quand nécessaire
add_action('plugins_loaded', 'pdf_builder_lazy_load', 9999);

// Fonction de création différée des tables (plus sécurisée)
add_action('admin_init', 'pdf_builder_delayed_table_creation', 1);

// Fonction de création différée des tables (plus sécurisée)
function pdf_builder_delayed_table_creation() {
    // Ne créer les tables que si :
    // 1. Le plugin est activé
    // 2. Nous sommes dans l'admin
    // 3. Les tables n'existent pas déjà
    // 4. WordPress est complètement chargé

    if (!get_option('pdf_builder_activated', false)) {
        return;
    }

    if (!is_admin() || !current_user_can('activate_plugins')) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'pdf_builder_templates';

    // Vérifier si la table existe déjà
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        return; // Table déjà créée
    }

    // Créer les tables maintenant que tout est sécurisé
    pdf_builder_create_database_tables();
}

// Chargement immédiat du menu admin pour éviter les problèmes de timing
// add_action('admin_menu', 'pdf_builder_register_admin_menu_immediate', 10);

// Hook pour gérer les téléchargements PDF WooCommerce
add_action('init', 'pdf_builder_handle_pdf_download', 1);

// Fonction pour gérer les téléchargements PDF WooCommerce
function pdf_builder_handle_pdf_download() {
    // Vérifications de sécurité avant de charger quoi que ce soit
    if (!isset($_GET['pdf_builder_action']) || $_GET['pdf_builder_action'] !== 'download_order_pdf') {
        return;
    }

    // Vérifier que WordPress est suffisamment chargé
    if (!function_exists('get_option') || !function_exists('is_admin') || !defined('ABSPATH')) {
        return;
    }

    // Vérifier que le plugin est activé
    $is_activated = get_option('pdf_builder_activated', false);
    if (!$is_activated) {
        return;
    }

    // Charger le core seulement si nécessaire et sécurisé
    if (!function_exists('pdf_builder_load_core')) {
        $bootstrap_path = PDF_BUILDER_PLUGIN_DIR . 'bootstrap.php';
        if (file_exists($bootstrap_path)) {
            require_once $bootstrap_path;
            if (function_exists('pdf_builder_load_bootstrap')) {
                pdf_builder_load_bootstrap();
            } else {
                return; // Bootstrap non disponible
            }
        } else {
            return; // Fichier bootstrap manquant
        }
    }

    // Vérifier que la classe Core est disponible
    if (!class_exists('PDF_Builder_Core')) {
        return;
    }

    try {
        $core = PDF_Builder_Core::getInstance();
        $woocommerce_integration = $core->get_woocommerce_integration();

        if ($woocommerce_integration && method_exists($woocommerce_integration, 'handle_pdf_download')) {
            $woocommerce_integration->handle_pdf_download();
        }
    } catch (Exception $e) {
        // Log l'erreur silencieusement sans casser WordPress
        if (function_exists('error_log')) {
            error_log('PDF Builder: Erreur dans handle_pdf_download - ' . $e->getMessage());
        }
    }
}

function pdf_builder_lazy_load() {
    // Protection contre les erreurs fatales pendant l'activation
    if (!function_exists('get_option') || !function_exists('is_admin')) {
        return; // WordPress pas encore complètement chargé
    }

    // Vérifier si le plugin est activé via option
    $is_enabled = get_option('pdf_builder_enabled', true); // Par défaut activé
    if (!$is_enabled) {
        return; // Plugin désactivé
    }

    // Charger le système de debug helper
    $debug_helper_path = PDF_BUILDER_PLUGIN_DIR . 'includes/utilities/PDF_Builder_Debug_Helper.php';
    if (file_exists($debug_helper_path)) {
        require_once $debug_helper_path;
    }

    // Charger les utilitaires de traduction
    $translation_utils_path = PDF_BUILDER_PLUGIN_DIR . 'includes/managers/PDF_Builder_Translation_Utils.php';
    if (file_exists($translation_utils_path)) {
        require_once $translation_utils_path;
    }

    // Charger config.php en premier (contient pdf_builder_should_load)
    $config_path = PDF_BUILDER_PLUGIN_DIR . 'includes/config.php';
    if (file_exists($config_path)) {
        require_once $config_path;
    } else {
        return; // Config manquante, ne pas continuer
    }

    // Vérifier que la fonction pdf_builder_should_load existe
    if (!function_exists('pdf_builder_should_load')) {
        return; // Fonction critique manquante
    }

    // Inclure TCPDF automatiquement si pas déjà présent
    if (!class_exists('TCPDF')) {
        $tcpdf_path = PDF_BUILDER_PLUGIN_DIR . 'lib/tcpdf/tcpdf.php';
        if (file_exists($tcpdf_path)) {
            require_once $tcpdf_path;
        }
    }

    // Charger l'internationalisation du frontend
    $frontend_i18n_path = PDF_BUILDER_PLUGIN_DIR . 'includes/class-pdf-builder-frontend-i18n.php';
    if (file_exists($frontend_i18n_path)) {
        require_once $frontend_i18n_path;
    }

    // Utiliser la fonction optimisée de détection
    if (pdf_builder_should_load()) {
        // Charger le bootstrap seulement quand nécessaire
        $bootstrap_path = PDF_BUILDER_PLUGIN_DIR . 'bootstrap.php';
        if (file_exists($bootstrap_path)) {
            require_once $bootstrap_path;
            if (function_exists('pdf_builder_load_bootstrap')) {
                pdf_builder_load_bootstrap();
            }
        }
    }
    // Sinon: RIEN - plugin totalement dormant
}