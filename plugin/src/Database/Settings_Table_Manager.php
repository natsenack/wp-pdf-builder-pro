<?php
/**
 * PDF Builder Pro - Database Table Management
 * Gère la table wp_pdf_builder_settings personnalisée
 */

namespace PDF_Builder\Database;

class Settings_Table_Manager {
    
    const TABLE_NAME = 'wp_pdf_builder_settings';
    const LEGACY_OPTION_KEY = 'pdf_builder_settings';
    
    /**
     * Créer la table lors de l'activation
     */
    public static function create_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'pdf_builder_settings';
        $charset_collate = $wpdb->get_charset_collate();
        
        // Vérifier si la table existe déjà
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
            return true; // Table existe déjà
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
        
        error_log('[PDF Builder] Table wp_pdf_builder_settings créée avec succès');
        return true;
    }
    
    /**
     * Migrer les données de wp_options vers wp_pdf_builder_settings
     */
    public static function migrate_data() {
        global $wpdb;
        
        $source_table = $wpdb->options;
        $dest_table = $wpdb->prefix . 'pdf_builder_settings';
        
        // Récupérer tous les paramètres pdf_builder_*
        $options = $wpdb->get_results(
            "SELECT option_name, option_value, autoload 
             FROM $source_table 
             WHERE option_name LIKE 'pdf_builder_%' 
             OR option_name = 'pdf_builder_settings'",
            ARRAY_A
        );
        
        if (empty($options)) {
            error_log('[PDF Builder] Aucune donnée à migrer depuis wp_options');
            return 0;
        }
        
        $count = 0;
        
        foreach ($options as $option) {
            $inserted = $wpdb->replace(
                $dest_table,
                [
                    'option_name' => $option['option_name'],
                    'option_value' => $option['option_value'],
                    'autoload' => $option['autoload']
                ]
            );
            
            if ($inserted) {
                $count++;
            }
        }
        
        error_log("[PDF Builder] Migration: $count paramètres migrés avec succès");
        return $count;
    }
    
    /**
     * Récupérer une option depuis la table personnalisée
     */
    public static function get_option($option_name, $default = false) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'pdf_builder_settings';
        
        $option_value = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT option_value FROM $table_name WHERE option_name = %s",
                $option_name
            )
        );
        
        if ($option_value === null) {
            return $default;
        }
        
        // Déserialiser si nécessaire
        return maybe_unserialize($option_value);
    }
    
    /**
     * Mettre à jour une option dans la table personnalisée
     */
    public static function update_option($option_name, $option_value, $autoload = 'yes') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'pdf_builder_settings';
        
        // Sérialiser si nécessaire
        $serialized_value = maybe_serialize($option_value);
        
        $result = $wpdb->replace(
            $table_name,
            [
                'option_name' => $option_name,
                'option_value' => $serialized_value,
                'autoload' => $autoload
            ],
            ['%s', '%s', '%s']
        );
        
        return $result !== false;
    }
    
    /**
     * Supprimer une option depuis la table personnalisée
     */
    public static function delete_option($option_name) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'pdf_builder_settings';
        
        $result = $wpdb->delete(
            $table_name,
            ['option_name' => $option_name],
            ['%s']
        );
        
        return $result !== false;
    }
    
    /**
     * Récupérer tous les options PDF Builder
     */
    public static function get_all_options() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'pdf_builder_settings';
        
        $options = $wpdb->get_results(
            "SELECT option_name, option_value FROM $table_name",
            ARRAY_A
        );
        
        if (empty($options)) {
            return [];
        }
        
        $result = [];
        foreach ($options as $option) {
            $result[$option['option_name']] = maybe_unserialize($option['option_value']);
        }
        
        return $result;
    }
    
    /**
     * Nettoyer la table (supprimer toutes les options)
     */
    public static function clear_all_options() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'pdf_builder_settings';
        
        $result = $wpdb->query("TRUNCATE TABLE $table_name");
        
        error_log('[PDF Builder] Table wp_pdf_builder_settings vidée');
        
        return $result !== false;
    }
    
    /**
     * Vérifier si la migration est complète
     */
    public static function is_migrated() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'pdf_builder_settings';
        
        // Vérifier que la table existe
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            return false;
        }
        
        // Vérifier qu'il y a des données
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        return $count > 0;
    }
}
