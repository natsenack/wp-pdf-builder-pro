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
    error_log('PDF Builder - pdf_builder_load_settings_assets appelée pour hook: ' . $hook . ' - DÉBUT FONCTION');

    // DEBUG: Log du hook actuel
    error_log('PDF Builder - Hook actuel: ' . $hook);

    // TEMPORAIREMENT : Charger sur TOUTES les pages admin pour debug
    // if ($hook !== 'pdf-builder-pro_page_pdf-builder-settings') {
    //     return;
    // }

    // ACTIVATION DES STYLES CSS PERSONNALISES
    // Styles pour les paramètres du plugin PDF Builder Pro

    // Charger les styles CSS généraux
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

    // Charger les styles pour l'onglet général
    wp_enqueue_style(
        'pdf-builder-general-settings',
        PDF_BUILDER_PLUGIN_URL . 'resources/assets/css/general-settings.css',
        array(),
        PDF_BUILDER_VERSION . '-' . time(),
        'all'
    );

    // Charger les styles pour l'onglet licence
    wp_enqueue_style(
        'pdf-builder-licence-settings',
        PDF_BUILDER_PLUGIN_URL . 'resources/assets/css/licence-settings.css',
        array(),
        PDF_BUILDER_VERSION . '-' . time(),
        'all'
    );

    // Charger les styles pour l'onglet PDF
    wp_enqueue_style(
        'pdf-builder-pdf-settings',
        PDF_BUILDER_PLUGIN_URL . 'resources/assets/css/pdf-settings.css',
        array(),
        PDF_BUILDER_VERSION . '-' . time(),
        'all'
    );

    // Charger les styles pour l'onglet templates
    wp_enqueue_style(
        'pdf-builder-templates-settings',
        PDF_BUILDER_PLUGIN_URL . 'resources/assets/css/templates-settings.css',
        array(),
        PDF_BUILDER_VERSION . '-' . time(),
        'all'
    );

    // Charger les styles pour l'onglet sécurité
    wp_enqueue_style(
        'pdf-builder-securite-settings',
        PDF_BUILDER_PLUGIN_URL . 'resources/assets/css/securite-settings.css',
        array(),
        PDF_BUILDER_VERSION . '-' . time(),
        'all'
    );

    // Charger les styles pour l'onglet cron
    wp_enqueue_style(
        'pdf-builder-cron-settings',
        PDF_BUILDER_PLUGIN_URL . 'resources/assets/css/cron-settings.css',
        array(),
        PDF_BUILDER_VERSION . '-' . time(),
        'all'
    );

    // Charger les styles pour l'onglet contenu
    wp_enqueue_style(
        'pdf-builder-contenu-settings',
        PDF_BUILDER_PLUGIN_URL . 'resources/assets/css/contenu-settings.css',
        array(),
        PDF_BUILDER_VERSION . '-' . time(),
        'all'
    );

    // Charger les styles des modals de l'onglet contenu
    wp_enqueue_style(
        'pdf-builder-modals-contenu',
        PDF_BUILDER_PLUGIN_URL . 'resources/assets/css/modals-contenu.css',
        array(),
        PDF_BUILDER_VERSION . '-' . time() . '-' . uniqid() . '-' . rand(1000, 9999),
        'all'
    );

    // Script pour forcer le rechargement CSS (en cas de cache persistant)
    wp_enqueue_script(
        'pdf-builder-force-css-reload',
        PDF_BUILDER_PLUGIN_URL . 'resources/assets/js/force-css-reload.js',
        array('jquery'),
        PDF_BUILDER_VERSION . '-' . time(),
        true
    );

    // Script pour vérifier et forcer le rechargement COMPLET des CSS
    wp_enqueue_script(
        'pdf-builder-force-complete-reload',
        PDF_BUILDER_PLUGIN_URL . 'resources/assets/js/force-complete-reload.js',
        array('jquery'),
        PDF_BUILDER_VERSION . '-' . time() . '-' . uniqid() . '-' . rand(100000, 999999),
        true
    );

    // Localiser le script force-complete-reload avec l'URL du plugin
    wp_localize_script('pdf-builder-force-complete-reload', 'pdfBuilderForceReload', array(
        'pluginUrl' => PDF_BUILDER_PLUGIN_URL
    ));

    // Charger les styles pour l'onglet principal
    wp_enqueue_style(
        'pdf-builder-main-settings',
        PDF_BUILDER_PLUGIN_URL . 'resources/assets/css/main-settings.css',
        array(),
        PDF_BUILDER_VERSION . '-' . time(),
        'all'
    );

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

    // Charger le système de notifications
    if (class_exists('PDF_Builder_Notification_Manager')) {
        $notification_manager = PDF_Builder_Notification_Manager::get_instance();
        $notification_manager->enqueue_scripts();
        error_log('PDF Builder - Système de notifications chargé');
    } else {
        error_log('PDF Builder - ERREUR: Classe PDF_Builder_Notification_Manager non trouvée');
    }

    // Charger le script de vérification de syntaxe JavaScript
    wp_enqueue_script(
        'pdf-builder-js-syntax-check',
        PDF_BUILDER_PLUGIN_URL . 'resources/assets/js/js-syntax-check.js',
        array('jquery'),
        PDF_BUILDER_VERSION . '-' . time(),
        true // Chargé dans le footer
    );

    error_log('PDF Builder - pdf_builder_load_settings_assets TERMINÉE pour hook: ' . $hook);
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
