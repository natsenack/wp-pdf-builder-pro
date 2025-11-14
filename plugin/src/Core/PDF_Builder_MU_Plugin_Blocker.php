<?php
/**
 * PDF Builder MU-Plugin Blocker
 * 
 * Supprime automatiquement les fichiers MU-plugins non autorisés
 * pour éviter les interférences avec le fonctionnement du plugin
 */

namespace PDF_Builder_Pro\Core;

if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

class PDFBuilderMuPluginBlocker {
    
    /**
     * Liste blanche des MU-plugins autorisés
     */
    private static $whitelist = [];
    
    /**
     * Initialiser le bloqueur
     */
    public static function init() {
        // Bloquer les MU-plugins non autorisés
        add_action('init', [self::class, 'block_unauthorized_mu_plugins'], 1);
        
        // Aussi au chargement du plugin
        add_action('plugins_loaded', [self::class, 'block_unauthorized_mu_plugins'], 1);
        
        // Et lors de l'accès à l'admin
        add_action('admin_init', [self::class, 'block_unauthorized_mu_plugins'], 1);
    }
    
    /**
     * Bloquer les MU-plugins non autorisés
     */
    public static function block_unauthorized_mu_plugins() {
        $mu_plugins_dir = WP_CONTENT_DIR . '/mu-plugins/';
        
        // Fichiers MU-plugin à bloquer
        $blocked_files = [
            'pdf-builder-validator-mu.php',
            'pdf-builder-admin-validator.php',
            'validator-mu.php'
        ];
        
        foreach ($blocked_files as $file) {
            $file_path = $mu_plugins_dir . $file;
            
            if (file_exists($file_path)) {
                // Essayer de supprimer le fichier
                @unlink($file_path);
                
                // Log l'action
                error_log('[PDF Builder Pro] Suppression de MU-plugin non autorisé: ' . $file_path);
            }
        }
    }
}

// Initialiser automatiquement
PDFBuilderMuPluginBlocker::init();
