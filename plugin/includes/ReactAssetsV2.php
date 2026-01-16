<?php
/**
 * PDF Builder Pro V2 - Enregistrement des assets React
 * 
 * Ce fichier enregistre les scripts et styles React V2
 * pour être chargés dans WordPress admin
 */

namespace PDFBuilderPro\V2;

class ReactAssets {
    
    public static function register() {
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_scripts'], 1);
    }
    
    /**
     * Enregistre les scripts et styles React V2
     */
    public static function enqueue_scripts($page) {
        // Assurer que wp-util est chargé sur toutes les pages admin pour éviter les erreurs "wp is not defined"
        wp_enqueue_script('wp-util');
        
        // Définir wp global tôt pour éviter les erreurs de plugins tiers
        wp_add_inline_script('jquery-core', 'window.wp = window.wp || {};', 'before');
        
        // Charger seulement sur la page du PDF Builder
        if ($page !== 'admin.php?page=pdf-builder-react-editor') {
            return;
        }
        
        $plugin_url = plugin_dir_url(__FILE__);
        $version = '2.0.0';
        
        // CSS
        wp_enqueue_style(
            'pdf-builder-react-v2',
            $plugin_url . 'assets/css/pdf-builder-react.min.css',
            [],
            $version
        );
        
        // Vendors (React, ReactDOM)
        wp_enqueue_script(
            'pdf-builder-react-vendors-v2',
            $plugin_url . 'assets/js/vendors.min.js',
            ['wp-util'],
            $version,
            true
        );
        
        // App principal
        wp_enqueue_script(
            'pdf-builder-react-app-v2',
            $plugin_url . 'assets/js/pdf-builder-react.min.js',
            ['pdf-builder-react-vendors-v2', 'wp-util'],
            $version,
            true
        );
        
        // Wrapper d'initialisation
        wp_enqueue_script(
            'pdf-builder-react-wrapper-v2',
            $plugin_url . 'assets/js/pdf-builder-react-wrapper.js',
            ['pdf-builder-react-app-v2', 'wp-util'],
            $version,
            true
        );
    }
}

// Auto-enregister
ReactAssets::register();
