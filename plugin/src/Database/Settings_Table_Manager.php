<?php
/**
 * PDF Builder Pro - Database Table Management
 * Gère la table wp_pdf_builder_settings personnalisée
 */

namespace PDF_Builder\Database;

class Settings_Table_Manager {
    
    const TABLE_NAME = 'wp_pdf_builder_settings';
    const LEGACY_OPTION_KEY = 'pdf_builder_settings';
    const ARRAY_A = 2; // WordPress constant for associative array results
    
    /**
     * Créer la table lors de l'activation
     */
    public static function create_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'pdf_builder_settings';
        $charset_collate = $wpdb->get_charset_collate();
        
        // Vérifier si la table existe déjà via information_schema (plus fiable)
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
            DB_NAME,
            $table_name
        ));
        
        if ($table_exists) {
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
        
        // Vérifier que la table a bien été créée
        $table_created = $wpdb->get_var($wpdb->prepare(
            "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
            DB_NAME,
            $table_name
        ));
        
        if ($table_created) {
            if (class_exists('PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Table wp_pdf_builder_settings créée avec succès');
            return true;
        } else {
            error_log('[PDF Builder] ERREUR: Table wp_pdf_builder_settings NOT créée');
            return false;
        }
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
            self::ARRAY_A
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
        
        if (class_exists('PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Table wp_pdf_builder_settings vidée');
        
        return $result !== false;
    }
}



