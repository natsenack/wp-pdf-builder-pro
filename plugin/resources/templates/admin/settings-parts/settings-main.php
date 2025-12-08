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

    <!-- Navigation par onglets -->
    <h2 class="nav-tab-wrapper" id="pdf-builder-tabs">
        <a href="#general" class="nav-tab nav-tab-active" data-tab="general"><?php _e('Général', 'pdf-builder-pro'); ?></a>
        <a href="#licence" class="nav-tab" data-tab="licence"><?php _e('Licence', 'pdf-builder-pro'); ?></a>
        <a href="#systeme" class="nav-tab" data-tab="systeme"><?php _e('Système', 'pdf-builder-pro'); ?></a>
        <a href="#securite" class="nav-tab" data-tab="securite"><?php _e('Sécurité', 'pdf-builder-pro'); ?></a>
        <a href="#pdf" class="nav-tab" data-tab="pdf"><?php _e('Configuration PDF', 'pdf-builder-pro'); ?></a>
        <a href="#contenu" class="nav-tab" data-tab="contenu"><?php _e('Canvas & Design', 'pdf-builder-pro'); ?></a>
        <a href="#templates" class="nav-tab" data-tab="templates"><?php _e('Templates', 'pdf-builder-pro'); ?></a>
        <a href="#developpeur" class="nav-tab" data-tab="developpeur"><?php _e('Développeur', 'pdf-builder-pro'); ?></a>
    </h2>

    <form method="post" action="options.php">
        <?php settings_fields('pdf_builder_settings_group'); ?>

        <!-- Section Général -->
        <div id="general" class="tab-content tab-content-active">
            <h3><?php _e('Général', 'pdf-builder-pro'); ?></h3>
            <?php
            $general_file = __DIR__ . '/settings-general.php';
            if (file_exists($general_file)) {
                require_once $general_file;
            } else {
                echo '<p>' . __('Fichier de paramètres général manquant.', 'pdf-builder-pro') . '</p>';
            }
            ?>
        </div>

        <!-- Section Licence -->
        <div id="licence" class="tab-content">
            <h3><?php _e('Licence', 'pdf-builder-pro'); ?></h3>
            <?php
            $licence_file = __DIR__ . '/settings-licence.php';
            if (file_exists($licence_file)) {
                require_once $licence_file;
            } else {
                echo '<p>' . __('Fichier de paramètres licence manquant.', 'pdf-builder-pro') . '</p>';
            }
            ?>
        </div>

        <!-- Section Système -->
        <div id="systeme" class="tab-content">
            <h3><?php _e('Système', 'pdf-builder-pro'); ?></h3>
            <?php
            $systeme_file = __DIR__ . '/settings-systeme.php';
            if (file_exists($systeme_file)) {
                require_once $systeme_file;
            } else {
                echo '<p>' . __('Fichier de paramètres système manquant.', 'pdf-builder-pro') . '</p>';
            }
            ?>
        </div>

        <!-- Section Sécurité -->
        <div id="securite" class="tab-content">
            <h3><?php _e('Sécurité', 'pdf-builder-pro'); ?></h3>
            <?php
            $securite_file = __DIR__ . '/settings-securite.php';
            if (file_exists($securite_file)) {
                require_once $securite_file;
            } else {
                echo '<p>' . __('Fichier de paramètres sécurité manquant.', 'pdf-builder-pro') . '</p>';
            }
            ?>
        </div>

        <!-- Section Configuration PDF -->
        <div id="pdf" class="tab-content">
            <h3><?php _e('Configuration PDF', 'pdf-builder-pro'); ?></h3>
            <?php
            $pdf_file = __DIR__ . '/settings-pdf.php';
            if (file_exists($pdf_file)) {
                require_once $pdf_file;
            } else {
                echo '<p>' . __('Fichier de paramètres PDF manquant.', 'pdf-builder-pro') . '</p>';
            }
            ?>
        </div>

        <!-- Section Canvas & Design -->
        <div id="contenu" class="tab-content">
            <h3><?php _e('Canvas & Design', 'pdf-builder-pro'); ?></h3>
            <?php
            $contenu_file = __DIR__ . '/settings-contenu.php';
            if (file_exists($contenu_file)) {
                require_once $contenu_file;
            } else {
                echo '<p>' . __('Fichier de paramètres canvas manquant.', 'pdf-builder-pro') . '</p>';
            }
            ?>
        </div>

        <!-- Section Templates -->
        <div id="templates" class="tab-content">
            <h3><?php _e('Templates', 'pdf-builder-pro'); ?></h3>
            <?php
            $templates_file = __DIR__ . '/settings-templates.php';
            if (file_exists($templates_file)) {
                require_once $templates_file;
            } else {
                echo '<p>' . __('Fichier de paramètres templates manquant.', 'pdf-builder-pro') . '</p>';
            }
            ?>
        </div>

        <!-- Section Développeur -->
        <div id="developpeur" class="tab-content">
            <h3><?php _e('Développeur', 'pdf-builder-pro'); ?></h3>
            <?php
            $developpeur_file = __DIR__ . '/settings-developpeur.php';
            if (file_exists($developpeur_file)) {
                require_once $developpeur_file;
            } else {
                echo '<p>' . __('Fichier de paramètres développeur manquant.', 'pdf-builder-pro') . '</p>';
            }
            ?>
        </div>

        <?php submit_button(); ?>
    </form>
</div>

<style>
.nav-tab-wrapper {
    border-bottom: 1px solid #ccc;
    margin-bottom: 20px;
}

.nav-tab {
    display: inline-block;
    padding: 8px 16px;
    margin-right: 4px;
    border: 1px solid #ccc;
    border-bottom: none;
    background: #f1f1f1;
    color: #666;
    text-decoration: none;
    border-radius: 4px 4px 0 0;
}

.nav-tab-active,
.nav-tab:hover {
    background: #fff;
    color: #000;
    border-bottom: 1px solid #fff;
}

.tab-content {
    display: none;
}

.tab-content-active {
    display: block;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();

            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('nav-tab-active'));
            // Add active class to clicked tab
            this.classList.add('nav-tab-active');

            // Hide all content
            contents.forEach(c => c.classList.remove('tab-content-active'));
            // Show corresponding content
            const target = this.getAttribute('data-tab');
            document.getElementById(target).classList.add('tab-content-active');
        });
    });
});
</script>