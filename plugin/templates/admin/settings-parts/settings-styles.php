<?php
/**
 * Styles CSS pour la page de paramètres
 * Charge le fichier settings.css externe depuis assets/css/
 * Updated: 2025-12-02 18:22:00
 */

// Vérifier que la constante PDF_BUILDER_PRO_ASSETS_URL est définie
if (!defined('PDF_BUILDER_PRO_ASSETS_URL')) {
    // Définition de secours si la constante n'est pas disponible
    define('PDF_BUILDER_PRO_ASSETS_URL', plugins_url('assets/', dirname(dirname(dirname(dirname(__FILE__))))));
}

// Enregistrer et charger la feuille de style CSS depuis assets/css/
wp_enqueue_style(
    'pdf-builder-settings-css',
    PDF_BUILDER_PRO_ASSETS_URL . 'css/settings.css',
    array(),
    PDF_BUILDER_VERSION . '-maintenance-buttons-' . time()
);
?>