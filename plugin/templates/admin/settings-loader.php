<?php
/**
 * PDF Builder Settings Page Loader
 * Gère le chargement des styles et scripts pour la page de paramètres
 * 
 * Ce fichier est inclus avant la page de paramètres pour enregistrer les hooks
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

/**
 * Hook pour charger les styles de la page de paramètres
 * Se déclenche à admin_print_styles - le bon moment pour enqueuer les styles
 */
add_action('admin_print_styles', function() {
    // Vérifier qu'on est sur la bonne page
    global $pagenow;
    if ($pagenow !== 'admin.php' || !isset($_GET['page']) || $_GET['page'] !== 'pdf-builder-settings') {
        return;
    }

    // Charger le CSS de settings
    wp_enqueue_style(
        'pdf-builder-settings-css',
        plugins_url('settings-parts/settings.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'settings-parts/settings.css')
    );
}, 9); // Priorité 9 pour charger avant d'autres styles
?>
