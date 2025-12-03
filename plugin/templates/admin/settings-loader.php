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
    if ($hook !== 'pdf-builder_page_pdf-builder-settings') {
        error_log('⚠️ PDF BUILDER ASSETS: Hook ignoré (pas la bonne page): ' . $hook . ' (attendu: pdf-builder_page_pdf-builder-settings)');
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
    
    // LOG AVANT L'ENREGISTREMENT
    error_log('🔧 PDF BUILDER ASSETS: Tentative d\'enregistrement du script: ' . $script_url);
    
    wp_enqueue_script(
        'pdf-builder-settings-tabs',
        $script_url,
        array('jquery'),
        PDF_BUILDER_VERSION . '-' . time(),
        true
    );

    // LOG APRÈS L'ENREGISTREMENT
    error_log('✅ PDF BUILDER ASSETS: Script enregistré avec succès: pdf-builder-settings-tabs');
    
    // AJOUTER UN SCRIPT INLINE POUR VÉRIFIER LE CHARGEMENT
    wp_add_inline_script('pdf-builder-settings-tabs', '
        console.log("🔥 PDF BUILDER INLINE: Script settings-tabs.js chargé via wp_enqueue_script");
        console.log("📍 Script URL:", "' . $script_url . '");
        console.log("📅 Version:", "' . PDF_BUILDER_VERSION . '-' . time() . '");
    ');
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