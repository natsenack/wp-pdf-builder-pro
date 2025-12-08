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
    <h1><?php _e('Paramètres PDF Builder Pro', 'pdf-builder-pro'); ?></h1>
    <p><?php _e('Configurez les paramètres de génération de vos documents PDF.', 'pdf-builder-pro'); ?></p>

    <form method="post" action="options.php">
        <?php settings_fields('pdf_builder_settings_group'); ?>

        <!-- Section Général -->
        <h2><?php _e('Général', 'pdf-builder-pro'); ?></h2>
        <?php
        $general_file = __DIR__ . '/settings-general.php';
        if (file_exists($general_file)) {
            require_once $general_file;
        } else {
            echo '<p>' . __('Fichier de paramètres général manquant.', 'pdf-builder-pro') . '</p>';
        }
        ?>

        <!-- Section Licence -->
        <h2><?php _e('Licence', 'pdf-builder-pro'); ?></h2>
        <?php
        $licence_file = __DIR__ . '/settings-licence.php';
        if (file_exists($licence_file)) {
            require_once $licence_file;
        } else {
            echo '<p>' . __('Fichier de paramètres licence manquant.', 'pdf-builder-pro') . '</p>';
        }
        ?>

        <!-- Section Système -->
        <h2><?php _e('Système', 'pdf-builder-pro'); ?></h2>
        <?php
        $systeme_file = __DIR__ . '/settings-systeme.php';
        if (file_exists($systeme_file)) {
            require_once $systeme_file;
        } else {
            echo '<p>' . __('Fichier de paramètres système manquant.', 'pdf-builder-pro') . '</p>';
        }
        ?>

        <!-- Section Sécurité -->
        <h2><?php _e('Sécurité', 'pdf-builder-pro'); ?></h2>
        <?php
        $securite_file = __DIR__ . '/settings-securite.php';
        if (file_exists($securite_file)) {
            require_once $securite_file;
        } else {
            echo '<p>' . __('Fichier de paramètres sécurité manquant.', 'pdf-builder-pro') . '</p>';
        }
        ?>

        <!-- Section Configuration PDF -->
        <h2><?php _e('Configuration PDF', 'pdf-builder-pro'); ?></h2>
        <?php
        $pdf_file = __DIR__ . '/settings-pdf.php';
        if (file_exists($pdf_file)) {
            require_once $pdf_file;
        } else {
            echo '<p>' . __('Fichier de paramètres PDF manquant.', 'pdf-builder-pro') . '</p>';
        }
        ?>

        <!-- Section Canvas & Design -->
        <h2><?php _e('Canvas & Design', 'pdf-builder-pro'); ?></h2>
        <?php
        $contenu_file = __DIR__ . '/settings-contenu.php';
        if (file_exists($contenu_file)) {
            require_once $contenu_file;
        } else {
            echo '<p>' . __('Fichier de paramètres canvas manquant.', 'pdf-builder-pro') . '</p>';
        }
        ?>

        <!-- Section Templates -->
        <h2><?php _e('Templates', 'pdf-builder-pro'); ?></h2>
        <?php
        $templates_file = __DIR__ . '/settings-templates.php';
        if (file_exists($templates_file)) {
            require_once $templates_file;
        } else {
            echo '<p>' . __('Fichier de paramètres templates manquant.', 'pdf-builder-pro') . '</p>';
        }
        ?>

        <!-- Section Développeur -->
        <h2><?php _e('Développeur', 'pdf-builder-pro'); ?></h2>
        <?php
        $developpeur_file = __DIR__ . '/settings-developpeur.php';
        if (file_exists($developpeur_file)) {
            require_once $developpeur_file;
        } else {
            echo '<p>' . __('Fichier de paramètres développeur manquant.', 'pdf-builder-pro') . '</p>';
        }
        ?>

        <?php submit_button(); ?>
    </form>
</div>