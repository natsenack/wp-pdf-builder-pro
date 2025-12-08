<?php
/**
 * Page principale des paramètres PDF Builder Pro
 *
 * Interface d'administration principale avec système d'onglets
 * pour la configuration complète du générateur de PDF.
 *
 * @version 2.1.0
 * @since 2025-12-08
 */

// Sécurité WordPress
if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die(__('Accès refusé. Vous devez être administrateur pour accéder à cette page.', 'pdf-builder-pro'));
}

// Récupération des paramètres généraux
$settings = get_option('pdf_builder_settings', array());
$current_user = wp_get_current_user();

// Informations de diagnostic pour le débogage (uniquement en mode debug)
$debug_info = defined('WP_DEBUG') && WP_DEBUG ? [
    'version' => PDF_BUILDER_PRO_VERSION ?? 'unknown',
    'php' => PHP_VERSION,
    'wordpress' => get_bloginfo('version'),
    'user' => $current_user->display_name,
    'time' => current_time('mysql')
] : null;

?>
<div class="wrap">
ceci est un test

</div>
