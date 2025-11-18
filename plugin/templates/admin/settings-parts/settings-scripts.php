<?php
/**
 * Scripts JavaScript pour la page de paramètres
 * Charge les fichiers JavaScript externes
 * Updated: 2025-11-18 20:20:00
 */

// Inclure d'abord les paramètres canvas
require_once 'settings-canvas-params.php';

// Enregistrer et charger le script JavaScript principal
wp_enqueue_script(
    'pdf-builder-settings-js',
    plugins_url('settings-parts/settings.js', dirname(__FILE__)),
    array('jquery'),
    PDF_BUILDER_VERSION,
    true
);

// Localiser le script pour AJAX
wp_localize_script('pdf-builder-settings-js', 'pdf_builder_ajax', array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('pdf_builder_settings_nonce')
));
?>