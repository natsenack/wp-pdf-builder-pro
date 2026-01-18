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
        // Page principale
        add_menu_page(
            __('PDF Builder Pro', 'pdf-builder-pro'),
            __('PDF Builder', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder',
            function() {
                include dirname(__DIR__) . '/pages/welcome.php';
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
    }
}

// Auto-register
AdminPages::register();

