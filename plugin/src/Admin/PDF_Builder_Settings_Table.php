<?php
/**
 * PDF Builder Pro - Création de la table des paramètres
 * Crée la table wp_pdf_builder_settings pour stocker tous les paramètres du plugin
 */

namespace PDF_Builder\Admin;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe pour gérer la table des paramètres PDF Builder
 */
class PDF_Builder_Settings_Table {

    /**
     * Nom de la table
     */
    private static $table_name = 'pdf_builder_settings';

    /**
     * Obtenir le nom complet de la table avec préfixe
     */
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . self::$table_name;
    }

    /**
     * Créer la table des paramètres (structure compatible wp_options)
     */
    public static function create_table() {
        global $wpdb;

        $table_name = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            option_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            option_name varchar(191) NOT NULL,
            option_value longtext NOT NULL,
            autoload varchar(20) NOT NULL DEFAULT 'yes',
            PRIMARY KEY (option_id),
            UNIQUE KEY option_name (option_name),
            KEY autoload (autoload)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Log de création
        error_log("[PDF Builder] Table des paramètres créée: $table_name");
    }

    /**
     * Vérifier si la table existe
     */
    public static function table_exists() {
        global $wpdb;
        $table_name = self::get_table_name();
        return $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
    }

    /**
     * Sauvegarder un paramètre (compatible wp_options)
     */
    public static function set_setting($key, $value, $autoload = 'yes') {
        global $wpdb;
        $table_name = self::get_table_name();

        // Sérialiser la valeur si nécessaire (comme WordPress)
        $serialized_value = self::serialize_value($value);

        // Utiliser INSERT ... ON DUPLICATE KEY UPDATE pour gérer les inserts/updates
        $result = $wpdb->query($wpdb->prepare(
            "INSERT INTO $table_name (option_name, option_value, autoload)
             VALUES (%s, %s, %s)
             ON DUPLICATE KEY UPDATE
             option_value = VALUES(option_value),
             autoload = VALUES(autoload)",
            $key,
            $serialized_value,
            $autoload
        ));

        if ($result === false) {
            error_log("[PDF Builder] Erreur sauvegarde paramètre $key: " . $wpdb->last_error);
            return false;
        }

        return true;
    }

    /**
     * Récupérer un paramètre (compatible wp_options)
     */
    public static function get_setting($key, $default = null) {
        global $wpdb;
        $table_name = self::get_table_name();

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT option_value FROM $table_name WHERE option_name = %s",
            $key
        ));

        if (!$row) {
            return $default;
        }

        return self::deserialize_value($row->option_value);
    }

    /**
     * Supprimer un paramètre
     */
    public static function delete_setting($key) {
        global $wpdb;
        $table_name = self::get_table_name();

        return $wpdb->delete($table_name, ['option_name' => $key], ['%s']);
    }

    /**
     * Migrer les paramètres depuis wp_options vers la table personnalisée
     */
    public static function migrate_from_options() {
        $existing_settings = get_option('pdf_builder_settings', []);

        if (!empty($existing_settings)) {
            error_log("[PDF Builder] Migration de " . count($existing_settings) . " paramètres depuis wp_options");

            foreach ($existing_settings as $key => $value) {
                self::set_setting($key, $value);
            }

            // Sauvegarder une copie de backup dans wp_options avec un nom différent
            update_option('pdf_builder_settings_backup_' . time(), $existing_settings);

            // Supprimer l'ancienne option
            delete_option('pdf_builder_settings');

            error_log("[PDF Builder] Migration terminée");
        }
    }

    /**
     * Récupérer tous les paramètres sous forme de tableau associatif
     * Compatible avec l'ancien format get_option('pdf_builder_settings')
     */
    public static function get_all_settings() {
        global $wpdb;
        $table_name = self::get_table_name();

        // Vérifier et créer la table si elle n'existe pas
        if (!self::table_exists()) {
            self::create_table();
        }

        $results = $wpdb->get_results(
            "SELECT option_name, option_value FROM $table_name",
            ARRAY_A
        );

        $settings = [];
        foreach ($results as $row) {
            $settings[$row['option_name']] = self::deserialize_value($row['option_value']);
        }

        return $settings;
    }

    /**
     * Vérifier et réparer la structure de la table
     */
    public static function check_and_repair() {
        global $wpdb;
        $table_name = self::get_table_name();

        // Si la table n'existe pas, la créer
        if (!self::table_exists()) {
            self::create_table();
            return array('status' => 'created', 'message' => 'Table créée');
        }

        // Vérifier les colonnes requises
        $required_columns = array(
            'option_id' => 'bigint',
            'option_name' => 'varchar',
            'option_value' => 'longtext',
            'autoload' => 'varchar'
        );

        $existing_columns = $wpdb->get_results("DESCRIBE $table_name");
        $column_names = wp_list_pluck($existing_columns, 'Field');

        $missing_columns = array_diff(array_keys($required_columns), $column_names);

        if (!empty($missing_columns)) {
            // Ajouter les colonnes manquantes
            foreach ($missing_columns as $col) {
                if ($col === 'option_id') {
                    // Ne pas ajouter option_id car c'est la clé primaire
                    continue;
                } elseif ($col === 'option_name') {
                    $wpdb->query("ALTER TABLE $table_name ADD COLUMN option_name varchar(191) NOT NULL");
                } elseif ($col === 'option_value') {
                    $wpdb->query("ALTER TABLE $table_name ADD COLUMN option_value longtext NOT NULL");
                } elseif ($col === 'autoload') {
                    $wpdb->query("ALTER TABLE $table_name ADD COLUMN autoload varchar(20) NOT NULL DEFAULT 'yes'");
                }
            }
            
            // Ajouter la clé unique si elle n'existe pas
            $keys = $wpdb->get_results("SHOW KEYS FROM $table_name WHERE Key_name = 'option_name'");
            if (empty($keys) && in_array('option_name', $column_names)) {
                $wpdb->query("ALTER TABLE $table_name ADD UNIQUE KEY option_name (option_name)");
            }

            return array('status' => 'repaired', 'message' => 'Colonnes manquantes ajoutées et réparées');
        }

        return array('status' => 'ok', 'message' => 'Table OK');
    }
}