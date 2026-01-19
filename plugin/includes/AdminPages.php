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
        // Page principale - Redirection directe vers l'éditeur
        add_menu_page(
            __('PDF Builder Pro', 'pdf-builder-pro'),
            __('PDF Builder', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder',
            function() {
                wp_redirect(admin_url('admin.php?page=pdf-builder-react-editor'));
                exit;
            },
            'dashicons-pdf',
            76
        );
        
        // Sous-page: Éditeur
        add_submenu_page(
            'pdf-builder',
            __('Éditeur PDF', 'pdf-builder-pro'),
            __('Éditeur', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-react-editor',
            function() {
                include dirname(__DIR__) . '/pages/admin-editor.php';
            }
        );
        
        // Sous-page: Paramètres
        add_submenu_page(
            'pdf-builder',
            __('Paramètres PDF Builder', 'pdf-builder-pro'),
            __('Paramètres', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-settings',
            function() {
                include dirname(__DIR__) . '/templates/admin/settings-page.php';
            }
        );

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

