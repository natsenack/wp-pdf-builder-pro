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
    // DEBUG: Fonction appelée
    error_log('PDF Builder - pdf_builder_load_settings_assets appelée pour hook: ' . $hook);

    // DEBUG: Log du hook actuel
    error_log('PDF Builder - Hook actuel: ' . $hook);

    // TEMPORAIREMENT : Charger sur TOUTES les pages admin pour debug
    // if ($hook !== 'pdf-builder-pro_page_pdf-builder-settings') {
    //     return;
    // }

    // TEMPORAIREMENT DÉSACTIVÉ : Assets personnalisés causent des conflits avec WordPress
    // Ces styles personnalisés interfèrent avec la structure WordPress et causent le chevauchement du footer

    /*
    // Charger les styles CSS
    wp_enqueue_style(
        'pdf-builder-settings',
        PDF_BUILDER_PLUGIN_URL . 'resources/assets/css/settings.css',
        array(),
        PDF_BUILDER_VERSION . '-' . time(),
        'all'
    );

    // Charger les styles pour les onglets (nav-tab-wrapper)
    wp_enqueue_style(
        'pdf-builder-tabs',
        PDF_BUILDER_PLUGIN_URL . 'resources/assets/css/settings-tabs.css',
        array(),
        PDF_BUILDER_VERSION . '-' . time(),
        'all'
    );

    // Charger les styles pour l'onglet développeur
    wp_enqueue_style(
        'pdf-builder-developer-settings',
        PDF_BUILDER_PLUGIN_URL . 'resources/assets/css/developer-settings.css',
        array(),
        PDF_BUILDER_VERSION . '-' . time(),
        'all'
    );

    // Charger les styles pour l'onglet système
    wp_enqueue_style(
        'pdf-builder-system-settings',
        PDF_BUILDER_PLUGIN_URL . 'resources/assets/css/system-settings.css',
        array(),
        PDF_BUILDER_VERSION . '-' . time(),
        'all'
    );
    */

    // DEBUG: Avant enqueue du script
    error_log('PDF Builder - Avant wp_enqueue_script');

    // Charger le JavaScript pour la navigation par onglets
    wp_enqueue_script(
        'pdf-builder-settings-tabs',
        PDF_BUILDER_PLUGIN_URL . 'resources/assets/js/settings-tabs.js',
        array('jquery'),
        PDF_BUILDER_VERSION . '-' . time() . '-' . rand(1000, 9999), // Cache busting très agressif
        false // Chargé dans le header pour une exécution précoce
    );

    // DEBUG: Après enqueue du script
    error_log('PDF Builder - Après wp_enqueue_script');

    // Localiser le script avec les données AJAX
    wp_localize_script('pdf-builder-settings-tabs', 'pdfBuilderAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('pdf_builder_ajax')
    ));

    // DEBUG: Après localization
    error_log('PDF Builder - Après wp_localize_script');
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
        PDF_BUILDER_PLUGIN_URL . 'resources/assets/css/admin-global.css',
        array(),
        PDF_BUILDER_VERSION . '-' . time(),
        'all'
    );
}

// Enregistrer le hook pour les assets globaux
add_action('admin_enqueue_scripts', 'pdf_builder_load_global_admin_assets');
