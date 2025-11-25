<?php
// Script de migration pour ajouter la colonne thumbnail_url
define('WP_USE_THEMES', false);
require_once '../../../wp-load.php';

// Maintenant $wpdb devrait être disponible
global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

// Vérifier si la colonne existe déjà
$columns = $wpdb->get_results("DESCRIBE $table_templates");
$column_exists = false;
if ($columns) {
    foreach ($columns as $column) {
        if ($column->Field === 'thumbnail_url') {
            $column_exists = true;
            break;
        }
    }
}

if (!$column_exists) {
    $sql = "ALTER TABLE $table_templates ADD COLUMN thumbnail_url VARCHAR(500) DEFAULT '' AFTER template_data";
    $result = $wpdb->query($sql);
    if ($result !== false) {
        echo "Colonne thumbnail_url ajoutée avec succès\n";
    } else {
        echo "Erreur lors de l'ajout de la colonne: " . $wpdb->last_error . "\n";
    }
} else {
    echo "La colonne thumbnail_url existe déjà\n";
}
?>