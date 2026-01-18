<?php
/**
 * Script de vérification et correction de la table wp_pdf_builder_settings
 * Ce script doit être exécuté directement après le déploiement
 */

// Charger WordPress
require_once dirname(dirname(dirname(__FILE__))) . '/wp-load.php';

if (!defined('ABSPATH')) {
    die('WordPress not loaded');
}

global $wpdb;

// Nom de la table
$table_name = $wpdb->prefix . 'pdf_builder_settings';

echo "=== Vérification de la table wp_pdf_builder_settings ===\n\n";

// Vérifier si la table existe
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;

if (!$table_exists) {
    echo "❌ Table n'existe pas. Création en cours...\n";
    
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
    
    echo "✅ Table créée avec succès\n\n";
} else {
    echo "✅ Table existe déjà\n\n";
    
    // Vérifier les colonnes
    echo "Vérification des colonnes:\n";
    
    $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
    
    $required_columns = array(
        'option_id' => 'bigint(20) unsigned',
        'option_name' => 'varchar(191)',
        'option_value' => 'longtext',
        'autoload' => "varchar(20)"
    );
    
    $existing_columns = array();
    foreach ($columns as $col) {
        $existing_columns[$col->Field] = $col->Type;
        echo "  - {$col->Field}: {$col->Type}\n";
    }
    
    // Vérifier les colonnes manquantes
    $missing = array_diff_key($required_columns, $existing_columns);
    
    if (!empty($missing)) {
        echo "\n❌ Colonnes manquantes détectées:\n";
        foreach ($missing as $col_name => $col_type) {
            echo "  - $col_name ($col_type)\n";
        }
        echo "\nRéparation en cours...\n";
        
        // Ajouter les colonnes manquantes
        foreach ($missing as $col_name => $col_type) {
            if ($col_name === 'option_id') {
                // Ne pas ajouter option_id car c'est la clé primaire
                continue;
            }
            
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN $col_name $col_type");
            echo "✅ Colonne $col_name ajoutée\n";
        }
        
        // Définir la clé primaire si elle n'existe pas
        $primary_key = $wpdb->get_results("SHOW KEYS FROM $table_name WHERE Key_name = 'PRIMARY'");
        if (empty($primary_key) && isset($existing_columns['option_id'])) {
            $wpdb->query("ALTER TABLE $table_name ADD PRIMARY KEY (option_id)");
            echo "✅ Clé primaire définie\n";
        }
        
        // Définir la clé unique sur option_name
        $unique_key = $wpdb->get_results("SHOW KEYS FROM $table_name WHERE Key_name = 'option_name'");
        if (empty($unique_key) && isset($existing_columns['option_name'])) {
            $wpdb->query("ALTER TABLE $table_name ADD UNIQUE KEY option_name (option_name)");
            echo "✅ Clé unique sur option_name définie\n";
        }
    } else {
        echo "\n✅ Toutes les colonnes requises sont présentes\n";
    }
}

// Vérifier que la table n'est pas vide de structure
$table_structure = $wpdb->get_results("DESCRIBE $table_name");
if (empty($table_structure)) {
    echo "\n❌ ERREUR: La table est vide (pas de colonnes)\n";
} else {
    echo "\n✅ Structure de table OK\n";
    echo "   Nombre de colonnes: " . count($table_structure) . "\n";
}

echo "\n=== Vérification terminée ===\n";
?>
