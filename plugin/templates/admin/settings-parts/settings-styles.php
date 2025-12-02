<?php
/**
 * Styles CSS pour la page de paramètres
 * Charge le fichier settings.css LOCAL depuis le dossier settings-parts/
 */

// Charger le CSS local depuis settings-parts/settings.css
add_action('admin_head', function() {
    wp_enqueue_style(
        'pdf-builder-settings-css',
        plugins_url('settings.css', __FILE__),
        array(),
        filemtime(__FILE__)
    );
});

// FALLBACK: Charger directement aussi au cas où le hook ne se déclenche pas
wp_enqueue_style(
    'pdf-builder-settings-css',
    plugins_url('settings.css', __FILE__),
    array(),
    filemtime(__FILE__)
);
?>
