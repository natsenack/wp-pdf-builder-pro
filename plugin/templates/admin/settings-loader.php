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
    // AFFICHAGE DIRECT SUR LA PAGE POUR DIAGNOSTIC
    echo '<div style="background: #ffeaa7; border: 2px solid #d63031; padding: 10px; margin: 10px 0; font-family: monospace; font-size: 12px;">';
    echo '<strong>🔍 PDF BUILDER DEBUG:</strong><br>';
    echo 'Hook détecté: <strong>' . $hook . '</strong><br>';
    echo 'PDF_BUILDER_PLUGIN_URL: <strong>' . (defined('PDF_BUILDER_PLUGIN_URL') ? PDF_BUILDER_PLUGIN_URL : '<span style="color: red;">NON DÉFINI</span>') . '</strong><br>';
    echo 'PDF_BUILDER_VERSION: <strong>' . (defined('PDF_BUILDER_VERSION') ? PDF_BUILDER_VERSION : '<span style="color: red;">NON DÉFINI</span>') . '</strong><br>';
    echo 'Page attendue: <strong>pdf-builder_page_pdf-builder-settings</strong><br>';
    echo 'Hook correspond: <strong>' . (($hook === 'pdf-builder_page_pdf-builder-settings') ? '<span style="color: green;">OUI</span>' : '<span style="color: red;">NON</span>') . '</strong>';
    echo '</div>';

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

    // TEST DE CHARGEMENT DU FICHIER JS
    $js_file_path = plugin_dir_path(dirname(dirname(__FILE__))) . 'assets/js/settings-tabs.js';
    echo 'Fichier JS existe: <strong>' . (file_exists($js_file_path) ? '<span style="color: green;">OUI</span>' : '<span style="color: red;">NON</span>') . '</strong><br>';
    echo 'Chemin du fichier: <strong>' . $js_file_path . '</strong><br>';

    // Charger le JavaScript pour la navigation par onglets
    $script_url = PDF_BUILDER_PLUGIN_URL . 'assets/js/settings-tabs.js';
    echo 'URL générée: <strong>' . $script_url . '</strong><br>';

    // TEST DE RÉCUPÉRATION HTTP
    $response = wp_remote_get($script_url);
    echo 'Test HTTP: <strong>' . (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200 ? '<span style="color: green;">OK (200)</span>' : '<span style="color: red;">ERREUR</span>') . '</strong><br>';

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