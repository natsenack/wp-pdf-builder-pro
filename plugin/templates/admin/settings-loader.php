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
    // LOG GÉNÉRAL POUR TOUS LES HOOKS
    error_log('🔍 PDF BUILDER ASSETS: Hook détecté: ' . $hook);

    // VÉRIFIER LES CONSTANTES
    error_log('📋 PDF BUILDER ASSETS: PDF_BUILDER_PLUGIN_URL = ' . (defined('PDF_BUILDER_PLUGIN_URL') ? PDF_BUILDER_PLUGIN_URL : 'NON DÉFINI'));
    error_log('📋 PDF BUILDER ASSETS: PDF_BUILDER_VERSION = ' . (defined('PDF_BUILDER_VERSION') ? PDF_BUILDER_VERSION : 'NON DÉFINI'));

    // Charger seulement sur la page de paramètres PDF Builder
    if ($hook !== 'toplevel_page_pdf-builder-pro') {
        error_log('⚠️ PDF BUILDER ASSETS: Hook ignoré (pas la bonne page): ' . $hook);
        return;
    }

    // LOG POUR CONFIRMER LE CHARGEMENT DES ASSETS
    error_log('🎯 PDF BUILDER ASSETS: Chargement des assets pour la page ' . $hook);

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
    $script_url = PDF_BUILDER_PLUGIN_URL . 'assets/js/settings-tabs.js';
    error_log('📜 PDF BUILDER ASSETS: URL du script générée: ' . $script_url);

    wp_enqueue_script(
        'pdf-builder-settings-tabs',
        $script_url,
        array('jquery'),
        PDF_BUILDER_VERSION . '-' . time(),
        true
    );

    // LOG POUR CONFIRMER L'ENREGISTREMENT DU SCRIPT
    error_log('📜 PDF BUILDER ASSETS: Script settings-tabs.js enregistré pour ' . $hook);

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