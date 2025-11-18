<?php
/**
 * Styles CSS pour la page de paramètres
 * Charge le fichier settings.css externe
 * Updated: 2025-11-18 20:20:00
 */

// Enregistrer et charger la feuille de style CSS
wp_enqueue_style(
    'pdf-builder-settings-css',
    plugins_url('settings-parts/settings.css', dirname(__FILE__)),
    array(),
    PDF_BUILDER_VERSION
);
?>