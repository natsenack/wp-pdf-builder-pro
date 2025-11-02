<?php
/**
 * PDF Builder Pro - Must-Use Plugin pour Admin Validator
 * Force le chargement de l'interface admin du validateur
 *
 * Ce fichier doit être placé dans wp-content/mu-plugins/
 */

// Sécurité
if (!defined('ABSPATH')) {
    exit;
}

// Inclure l'admin validator si le plugin principal est activé
add_action('plugins_loaded', function() {
    $plugin_path = WP_PLUGIN_DIR . '/wp-pdf-builder-pro/admin-validator.php';
    if (file_exists($plugin_path)) {
        require_once $plugin_path;
    }
});

// Ajouter un lien direct dans la barre d'admin
add_action('admin_bar_menu', function($wp_admin_bar) {
    if (current_user_can('manage_options')) {
        $wp_admin_bar->add_node([
            'id'    => 'pdf-builder-validator',
            'title' => '🧪 PDF Builder Validator',
            'href'  => admin_url('tools.php?page=pdf-builder-validator'),
            'meta'  => ['class' => 'pdf-builder-validator-link']
        ]);
    }
}, 999);

// Ajouter un style pour le lien
add_action('admin_head', function() {
    echo '<style>
        #wp-admin-bar-pdf-builder-validator a {
            background: linear-gradient(45deg, #3498db, #2980b9) !important;
            color: white !important;
        }
        #wp-admin-bar-pdf-builder-validator a:hover {
            background: linear-gradient(45deg, #2980b9, #21618c) !important;
        }
    </style>';
});