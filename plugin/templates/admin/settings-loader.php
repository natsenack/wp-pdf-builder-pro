<?php
/**
 * PDF Builder Pro - Settings Loader
 * Charge les styles et scripts pour la page de paramètres
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

// LOG DE BASE - LE FICHIER SE CHARGE-T-IL ?
add_action('admin_init', function() {
    echo "<script>console.log('🔥 SETTINGS-LOADER.PHP CHARGÉ - " . time() . "');</script>";
});

/**
 * Charger les assets pour la page de paramètres
 */
function pdf_builder_load_settings_assets($hook) {
    // LOG DU HOOK ACTUEL
    echo "<script>console.log('🔥 HOOK ACTUEL: {$hook}');</script>";

    // Charger seulement sur la page de paramètres PDF Builder
    if ($hook !== 'pdf-builder_page_pdf-builder-settings') {
        echo "<script>console.log('❌ HOOK IGNORÉ: {$hook} (attendu: pdf-builder_page_pdf-builder-settings)');</script>";
        return;
    }

    echo "<script>console.log('✅ BONNE PAGE DÉTECTÉE: {$hook}');</script>";

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

    wp_enqueue_script(
        'pdf-builder-settings-tabs',
        $script_url,
        array('jquery'),
        PDF_BUILDER_VERSION . '-' . time(),
        true
    );

    // AJOUTER LES LOGS JAVASCRIPT DIRECTEMENT
    wp_add_inline_script('pdf-builder-settings-tabs', '
        console.log("🔥 PDF BUILDER DEBUG: Hook détecté:", "' . $hook . '");
        console.log("📋 PDF BUILDER DEBUG: PDF_BUILDER_PLUGIN_URL =", "' . (defined('PDF_BUILDER_PLUGIN_URL') ? PDF_BUILDER_PLUGIN_URL : 'NON DÉFINI') . '");
        console.log("📋 PDF BUILDER DEBUG: PDF_BUILDER_VERSION =", "' . (defined('PDF_BUILDER_VERSION') ? PDF_BUILDER_VERSION : 'NON DÉFINI') . '");
        console.log("🎯 PDF BUILDER DEBUG: Chargement des assets pour la page:", "' . $hook . '");
        console.log("🔧 PDF BUILDER DEBUG: Script URL:", "' . $script_url . '");
        console.log("📅 PDF BUILDER DEBUG: Version avec cache buster:", "' . PDF_BUILDER_VERSION . '-' . time() . '");
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