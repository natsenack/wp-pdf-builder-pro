<?php
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
     */
    public static function get_table_name(): string {
        global $wpdb;
        return $wpdb->prefix . 'pdf_builder_settings';
    }

    /**
     * Créer la table lors de l'activation + migration depuis l'ancienne table wp_ hardcodée.
     */
    public static function create_table() {
        global $wpdb;

        $table_name    = $wpdb->prefix . 'pdf_builder_settings';
        $legacy_table  = self::LEGACY_TABLE_NAME; // 'wp_pdf_builder_settings'
        $charset_collate = $wpdb->get_charset_collate();

        // Vérifier si la table existe déjà via information_schema (plus fiable)
        $table_exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
            DB_NAME,
            $table_name
        ) );

        if ( ! $table_exists ) {
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
            } else {
                error_log( "[PDF Builder] ERREUR: Table {$table_name} NOT créée" );
                return false;
            }
        }

        // ── Migration depuis l'ancienne table hardcodée 'wp_pdf_builder_settings' ──
        // Si le préfixe WP n'est pas 'wp_', l'ancienne table peut contenir des données
        // que les queries courantes ($wpdb->prefix) ne trouvent pas.
        if ( $table_name !== $legacy_table ) {
            $legacy_exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
                DB_NAME,
                $legacy_table
            ) );

            if ( $legacy_exists ) {
                // Compter les lignes dans la table cible
                $target_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );

                if ( $target_count === 0 ) {
                    // Table cible vide → copier toutes les lignes depuis l'ancienne table
                    $rows = $wpdb->get_results( "SELECT option_name, option_value, autoload FROM $legacy_table", ARRAY_A );
                    $migrated = 0;
                    foreach ( (array) $rows as $row ) {
                        $wpdb->replace( $table_name, $row, [ '%s', '%s', '%s' ] );
                        $migrated++;
                    }
                    error_log( "[PDF Builder] Migration: {$migrated} options copiées de '{$legacy_table}' vers '{$table_name}'" );
                } else {
                    error_log( "[PDF Builder] Migration ignorée: '{$table_name}' contient déjà {$target_count} ligne(s)" );
                }
            }
        }

        return true;
    }

    /**
     * Récupérer une option depuis la table personnalisée.
     * Fallback automatique vers l'ancienne table 'wp_pdf_builder_settings' si la table préfixée
     * n'existe pas encore ou ne contient pas l'option (ex : migration non encore effectuée).
     */
    public static function get_option($option_name, $default = false) {
        global $wpdb;

        $table_name   = $wpdb->prefix . 'pdf_builder_settings';
        $legacy_table = self::LEGACY_TABLE_NAME;

        $option_value = $wpdb->get_var(
            $wpdb->prepare( "SELECT option_value FROM $table_name WHERE option_name = %s", $option_name )
        );

        // Fallback : chercher dans l'ancienne table hardcodée si la table préfixée ne contient rien
        if ( $option_value === null && $table_name !== $legacy_table ) {
            $legacy_exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
                DB_NAME,
                $legacy_table
            ) );
            if ( $legacy_exists ) {
                $option_value = $wpdb->get_var(
                    $wpdb->prepare( "SELECT option_value FROM $legacy_table WHERE option_name = %s", $option_name )
                );
                if ( $option_value !== null ) {
                    error_log( "[PDF Builder] get_option('{$option_name}') — lu depuis table legacy '{$legacy_table}' (migration en attente)" );
                }
            }
        }

        if ( $option_value === null ) {
            return $default;
        }

        // Déserialiser si nécessaire
        return maybe_unserialize( $option_value );
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

        error_log('[PDF Builder] Table wp_pdf_builder_settings vidée');

        return $result !== false;
    }
}



