<?php
/**
 * PDF Builder Pro V2 - Enregistrement des pages d'administration
 */

namespace PDFBuilderPro\V2;

class AdminPages {
    
    public static function register() {
        add_action('admin_menu', [self::class, 'add_menu_pages']);
    }
    
    /**
     * Ajoute les pages d'admin dans le menu WordPress
     */
    public static function add_menu_pages() {
        // NE PAS CRÉER DE MENU PRINCIPAL - IL EXISTE DÉJÀ AILLEURS
        // Uniquement ajouter la sous-page de migration
        
        // Sous-page: Migration Canvas
        add_submenu_page(
            'pdf-builder',
            __('Migration Paramètres Canvas', 'pdf-builder-pro'),
            __('Migration Canvas', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-migration',
            function() {
                include dirname(__DIR__) . '/migration_admin_page.php';
            }
        );
    }
}

// Auto-register
AdminPages::register();

