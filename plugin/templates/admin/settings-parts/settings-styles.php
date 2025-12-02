<?php
/**
 * Styles CSS pour la page de paramètres
 * Charge le fichier settings.css externe depuis assets/css/
 * Updated: 2025-12-02 18:15:00
 */

// Enregistrer et charger la feuille de style CSS depuis assets/css/
wp_enqueue_style(
    'pdf-builder-settings-css',
    plugins_url('../../../assets/css/settings.css', __FILE__),
    array(),
    PDF_BUILDER_VERSION . '-maintenance-buttons-' . time()
);
?>