<?php
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.SchemaChange
/**
 * PDF Builder Pro - Database Table Management
 * Gère la table {prefix}pdf_builder_settings personnalisée
 */

namespace PDF_Builder\Database;

class Settings_Table_Manager {

    /**
     * Nom de la table SANS préfixe (le préfixe est toujours ajouté via $wpdb->prefix).
     * Ancienne constante gardée pour compatibilité ascendante — ne jamais l'utiliser dans des queries SQL.
     */
    const TABLE_SUFFIX = 'pdf_builder_settings';
    const TABLE_NAME   = 'pdf_builder_settings'; // sans préfixe — ex: {prefix}pdf_builder_settings

    /**
     * Nom de l'ancienne table hardcodée avec préfixe "wp_" (migration).
     * Certains sites avaient cette table créée avec le préfixe wp_ littéral
     * au lieu du préfixe WordPress réel ($wpdb->prefix).
     */
    const LEGACY_TABLE_NAME = 'wp_pdf_builder_settings';

    const LEGACY_OPTION_KEY = 'pdf_builder_settings';
    const ARRAY_A = 2; // WordPress constant for associative array results

    /**
     * Retourne le nom complet de la table avec le bon préfixe WordPress.
     * Le préfixe est lu directement depuis $table_prefix (défini dans wp-config.php).
     */
    public static function get_table_name(): string {
        global $table_prefix;
        return $table_prefix . 'pdf_builder_settings';
    }

    /**
     * Créer la table lors de l'activation.
     */
    public static function create_table() {
        global $wpdb;

        $table_name      = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        // Vérifier si la table existe déjà
        $table_exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
            DB_NAME,
            $table_name
        ) );

        if ( $table_exists ) {
            return true;
        }

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            option_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            option_name varchar(191) NOT NULL DEFAULT '',
            option_value longtext NOT NULL,
            autoload varchar(20) NOT NULL DEFAULT 'yes',
            PRIMARY KEY (option_id),
            UNIQUE KEY option_name (option_name)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        $table_exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
            DB_NAME,
            $table_name
        ) );

        if ( $table_exists ) {
            error_log( "[PDF Builder] Table {$table_name} créée avec succès" );
            return true;
        } else {
            error_log( "[PDF Builder] ERREUR: Table {$table_name} NOT créée" );
            return false;
        }
    }

    /**
     * Récupérer une option depuis la table {prefix}pdf_builder_settings.
     */
    public static function get_option($option_name, $default = false) {
        global $wpdb;

        $table_name = self::get_table_name();

        $option_value = $wpdb->get_var(
            $wpdb->prepare( "SELECT option_value FROM $table_name WHERE option_name = %s", $option_name )
        );

        if ( $option_value === null ) {
            return $default;
        }

        return maybe_unserialize( $option_value );
    }
    
    /**
     * Mettre à jour une option dans la table personnalisée
     */
    public static function update_option($option_name, $option_value, $autoload = 'yes') {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
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
        
        $table_name = self::get_table_name();
        
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
        
        $table_name = self::get_table_name();
        
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
        
        $table_name = self::get_table_name();
        
        $result = $wpdb->query("TRUNCATE TABLE $table_name");

        error_log("[PDF Builder] Table {$table_name} vidée");

        return $result !== false;
    }
}



