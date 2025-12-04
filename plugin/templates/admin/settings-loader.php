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
    if ($hook !== 'pdf-builder_page_pdf-builder-settings') {
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
        PDF_BUILDER_VERSION . '-' . time() . '-' . rand(1000, 9999), // Cache busting très agressif
        false // Chargé dans le header pour une exécution précoce
    );

    // Passer les paramètres de debug au JavaScript
    $settings = get_option('pdf_builder_settings', []);
    $debug_config = array(
        'debug' => isset($settings['pdf_builder_debug_javascript']) && $settings['pdf_builder_debug_javascript'],
        'verbose' => isset($settings['pdf_builder_debug_javascript_verbose']) && $settings['pdf_builder_debug_javascript_verbose'],
        'ajax_debug' => isset($settings['pdf_builder_debug_ajax']) && $settings['pdf_builder_debug_ajax'],
        'developer_mode' => isset($settings['pdf_builder_developer_enabled']) && $settings['pdf_builder_developer_enabled']
    );
    
    wp_localize_script('pdf-builder-settings-tabs', 'PDF_BUILDER_CONFIG', $debug_config);
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
add_action('admin_enqueue_scripts', 'pdf_builder_load_global_admin_assets');