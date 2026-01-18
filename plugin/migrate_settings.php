<?php
/**
 * Migration script pour transférer les paramètres de wp_options vers wp_pdf_builder_settings
 * À exécuter une seule fois après l'implémentation de la table personnalisée
 */

// Inclure WordPress pour avoir accès aux fonctions
define('WP_USE_THEMES', false);
require_once dirname(__FILE__) . '/../../../wp-load.php';

require_once plugin_dir_path(__FILE__) . 'src/Admin/PDF_Builder_Settings_Table.php';

function migrate_pdf_builder_settings_to_custom_table() {
    // Vérifier si la migration a déjà été faite
    $migration_done = get_option('pdf_builder_migration_done', false);
    if ($migration_done) {
        error_log('[PDF Builder] Migration already completed');
        return true;
    }

    // Créer la table si elle n'existe pas
    PDF_Builder_Settings_Table::create_table();

    // Récupérer les paramètres existants
    $existing_settings = get_option('pdf_builder_settings', array());

    if (empty($existing_settings)) {
        error_log('[PDF Builder] No existing settings to migrate');
        update_option('pdf_builder_migration_done', true);
        return true;
    }

    error_log('[PDF Builder] Starting migration of ' . count($existing_settings) . ' settings');

    $migrated_count = 0;
    foreach ($existing_settings as $key => $value) {
        if (PDF_Builder_Settings_Table::set_setting($key, $value)) {
            $migrated_count++;
            error_log("[PDF Builder] Migrated setting: $key");
        } else {
            error_log("[PDF Builder] Failed to migrate setting: $key");
        }
    }

    // Marquer la migration comme terminée
    update_option('pdf_builder_migration_done', true);

    error_log("[PDF Builder] Migration completed: $migrated_count settings migrated");

    return $migrated_count > 0;
}

// Exécuter la migration si appelée directement
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    migrate_pdf_builder_settings_to_custom_table();
    echo "Migration completed. Check error logs for details.\n";
}