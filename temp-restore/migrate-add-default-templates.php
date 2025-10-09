<?php
/**
 * Migration pour ajouter le champ is_default aux templates
 * À exécuter après la création des tables
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

global $wpdb;
$table_name = $wpdb->prefix . 'pdf_builder_templates';

// Vérifier si la colonne is_default existe déjà
$column_exists = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'is_default'");

if (empty($column_exists)) {
    // Ajouter la colonne is_default
    $wpdb->query("ALTER TABLE $table_name ADD COLUMN is_default tinyint(1) NOT NULL DEFAULT 0 AFTER status");

    // Ajouter l'index pour les performances
    $wpdb->query("ALTER TABLE $table_name ADD KEY type_default (type, is_default)");

    echo "✅ Migration réussie : champ is_default ajouté à la table templates\n";

    // Définir un template par défaut pour chaque type existant (le plus ancien actif)
    $types = array('pdf', 'invoice', 'quote');
    foreach ($types as $type) {
        $existing_templates = $wpdb->get_results($wpdb->prepare(
            "SELECT id FROM $table_name WHERE type = %s AND status = 'active' ORDER BY created_at ASC LIMIT 1",
            $type
        ));

        if (!empty($existing_templates)) {
            $wpdb->update(
                $table_name,
                array('is_default' => 1),
                array('id' => $existing_templates[0]->id),
                array('%d'),
                array('%d')
            );
            echo "✅ Template par défaut défini pour le type $type (ID: {$existing_templates[0]->id})\n";
        }
    }
} else {
    echo "ℹ️ La colonne is_default existe déjà\n";
}

echo "Migration terminée.\n";