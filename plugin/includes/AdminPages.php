<?php
/**
 * PDF Builder Pro - Enregistrement des pages d'administration
 */

// Fonction pour ajouter les pages admin
function pdf_builder_add_migration_page() {
    add_submenu_page(
        'pdf-builder',
        __('Migration Paramètres Canvas', 'pdf-builder-pro'),
        __('Migration Canvas', 'pdf-builder-pro'),
        'manage_options',
        'pdf-builder-migration',
        'pdf_builder_render_migration_page'
    );
}

// Fonction pour rendre la page de migration
function pdf_builder_render_migration_page() {
    include plugin_dir_path(__FILE__) . '../migration_admin_page.php';
}

// Enregistrer le hook admin_menu
add_action('admin_menu', 'pdf_builder_add_migration_page');

