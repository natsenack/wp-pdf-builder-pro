<?php
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.SchemaChange
/**
 * PDF Builder Pro - Database Initialization
 * Initialiser les tables de base de données lors de l'activation
 */

namespace PDF_Builder\Database;

if ( ! defined( 'ABSPATH' ) ) exit;

class Database_Initializer {
    
    /**
     * Initialiser les tables lors de l'activation du plugin
     */
    public static function initialize_database() {
        global $wpdb;
        
        // Créer la table wp_pdf_builder_settings
        self::create_settings_table();
        
        // Créer la table wp_pdf_builder_templates
        self::create_templates_table();
    }
    
    /**
     * Créer la table wp_pdf_builder_settings
     */
    private static function create_settings_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'pdf_builder_settings';
        $charset_collate = $wpdb->get_charset_collate();
        
        // Vérifier si la table existe déjà
        $existing_table = $wpdb->get_var($wpdb->prepare(
            "SELECT 1 FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
            DB_NAME,
            $table_name
        ));
        
        if ($existing_table) {
            return; // Table existe déjà
        }
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            option_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            option_name varchar(191) NOT NULL DEFAULT '',
            option_value longtext NOT NULL,
            autoload varchar(20) NOT NULL DEFAULT 'yes',
            PRIMARY KEY (option_id),
            UNIQUE KEY option_name (option_name)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Créer la table wp_pdf_builder_templates
     */
    private static function create_templates_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'pdf_builder_templates';
        $charset_collate = $wpdb->get_charset_collate();
        
        // Vérifier si la table existe déjà
        $existing_table = $wpdb->get_var($wpdb->prepare(
            "SELECT 1 FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
            DB_NAME,
            $table_name
        ));
        
        if ($existing_table) {
            return; // Table existe déjà
        }
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            template_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            template_name varchar(255) NOT NULL DEFAULT '',
            template_type varchar(100) NOT NULL DEFAULT 'custom',
            template_data longtext NOT NULL,
            template_preview longtext,
            template_created datetime DEFAULT CURRENT_TIMESTAMP,
            template_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_default tinyint(1) NOT NULL DEFAULT 0,
            is_public tinyint(1) NOT NULL DEFAULT 0,
            thumbnail_url varchar(500) DEFAULT '',
            PRIMARY KEY (template_id),
            KEY template_type (template_type),
            KEY is_default (is_default)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

