<?php
/**
 * PDF Builder Pro - Settings Loader
 * Charge les styles et scripts pour la page de paramètres
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

/**
 * Charger les assets pour la page de paramètres
 */
function pdf_builder_load_settings_assets($hook) {
    // Charger seulement sur la page de paramètres PDF Builder
    if ($hook !== 'toplevel_page_pdf-builder-settings') {
        return;
    }

    // Charger les styles CSS
    wp_enqueue_style(
        'pdf-builder-settings',
        PDF_BUILDER_PLUGIN_URL . 'assets/css/settings.css',
        array(),
        PDF_BUILDER_VERSION . '-' . time(),
        'all'
    );

    // Charger les styles pour les onglets (nav-tab-wrapper)
    wp_enqueue_style(
        'pdf-builder-tabs',
        PDF_BUILDER_PLUGIN_URL . 'assets/css/settings-tabs.css',
        array(),
        PDF_BUILDER_VERSION . '-' . time(),
        'all'
    );

    // Charger le JavaScript pour la navigation par onglets
    wp_enqueue_script(
        'pdf-builder-settings-tabs',
        PDF_BUILDER_PLUGIN_URL . 'assets/js/settings-tabs.js',
        array('jquery'),
        PDF_BUILDER_VERSION . '-' . time(),
        true
    );

    // Localiser les variables JavaScript
    wp_localize_script('pdf-builder-settings-tabs', 'pdfBuilderSettings', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('pdf_builder_settings'),
        'strings' => array(
            'loading' => __('Chargement...', 'pdf-builder-pro'),
            'error' => __('Erreur', 'pdf-builder-pro'),
            'success' => __('Succès', 'pdf-builder-pro'),
            'saving' => __('Sauvegarde en cours...', 'pdf-builder-pro'),
            'saved' => __('Paramètres sauvegardés', 'pdf-builder-pro'),
        ),
        'debug' => defined('WP_DEBUG') && WP_DEBUG
    ));
}

// Enregistrer le hook pour charger les assets
add_action('admin_enqueue_scripts', 'pdf_builder_load_settings_assets');

/**
 * Charger les assets pour toutes les pages admin (styles globaux)
 */
function pdf_builder_load_global_admin_assets($hook) {
    // Styles globaux pour l'admin
    wp_enqueue_style(
        'pdf-builder-admin-global',
        PDF_BUILDER_PLUGIN_URL . 'assets/css/admin-global.css',
        array(),
        PDF_BUILDER_VERSION . '-' . time(),
        'all'
    );
}

// Enregistrer le hook pour les assets globaux
add_action('admin_enqueue_scripts', 'pdf_builder_load_global_admin_assets', 5);