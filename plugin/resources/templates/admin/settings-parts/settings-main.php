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
    <div class="nav-tab-wrapper">
        <input type="radio" id="tab-general" name="tabs" checked>
        <label for="tab-general" class="nav-tab"><?php _e('Général', 'pdf-builder-pro'); ?></label>

        <input type="radio" id="tab-licence" name="tabs">
        <label for="tab-licence" class="nav-tab"><?php _e('Licence', 'pdf-builder-pro'); ?></label>

        <input type="radio" id="tab-systeme" name="tabs">
        <label for="tab-systeme" class="nav-tab"><?php _e('Système', 'pdf-builder-pro'); ?></label>

        <input type="radio" id="tab-securite" name="tabs">
        <label for="tab-securite" class="nav-tab"><?php _e('Sécurité', 'pdf-builder-pro'); ?></label>

        <input type="radio" id="tab-pdf" name="tabs">
        <label for="tab-pdf" class="nav-tab"><?php _e('Configuration PDF', 'pdf-builder-pro'); ?></label>

        <input type="radio" id="tab-contenu" name="tabs">
        <label for="tab-contenu" class="nav-tab"><?php _e('Canvas & Design', 'pdf-builder-pro'); ?></label>

        <input type="radio" id="tab-templates" name="tabs">
        <label for="tab-templates" class="nav-tab"><?php _e('Templates', 'pdf-builder-pro'); ?></label>

        <input type="radio" id="tab-developpeur" name="tabs">
        <label for="tab-developpeur" class="nav-tab"><?php _e('Développeur', 'pdf-builder-pro'); ?></label>
    </div>

    <form method="post" action="options.php">
        <?php settings_fields('pdf_builder_settings_group'); ?>

        <!-- Section Général -->
        <div class="tab-content">
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
        <div class="tab-content">
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
        <div class="tab-content">
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
        <div class="tab-content">
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
        <div class="tab-content">
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
        <div class="tab-content">
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
        <div class="tab-content">
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
        <div class="tab-content">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.nav-tab');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();

            // Retirer la classe active de tous les onglets
            tabs.forEach(t => t.classList.remove('active'));
            // Ajouter la classe active à l'onglet cliqué
            this.classList.add('active');

            // Masquer tous les contenus
            contents.forEach(c => c.classList.remove('active'));
            // Afficher le contenu correspondant
            const targetId = this.getAttribute('href').substring(1);
            document.getElementById(targetId).classList.add('active');
        });
    });
});
</script>

<style>
    /* Masquer les boutons radio */
    .nav-tab-wrapper input[type="radio"] {
        display: none;
    }

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
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .nav-tab:hover {
        background: #e9e9e9;
        color: #333;
    }

    /* Style de l'onglet actif basé sur le bouton radio coché */
    #tab-general:checked ~ .nav-tab-wrapper .nav-tab[for="tab-general"],
    #tab-licence:checked ~ .nav-tab-wrapper .nav-tab[for="tab-licence"],
    #tab-systeme:checked ~ .nav-tab-wrapper .nav-tab[for="tab-systeme"],
    #tab-securite:checked ~ .nav-tab-wrapper .nav-tab[for="tab-securite"],
    #tab-pdf:checked ~ .nav-tab-wrapper .nav-tab[for="tab-pdf"],
    #tab-contenu:checked ~ .nav-tab-wrapper .nav-tab[for="tab-contenu"],
    #tab-templates:checked ~ .nav-tab-wrapper .nav-tab[for="tab-templates"],
    #tab-developpeur:checked ~ .nav-tab-wrapper .nav-tab[for="tab-developpeur"] {
        background: #fff;
        color: #000;
        border-bottom: 1px solid #fff;
        margin-bottom: -1px;
        font-weight: 600;
    }

    /* Masquer toutes les sections par défaut */
    .tab-content {
        display: none;
        background: #fff;
        border: 1px solid #ddd;
        border-top: none;
        padding: 20px;
        border-radius: 0 0 4px 4px;
    }

    /* Afficher la section active basée sur le bouton radio coché */
    #tab-general:checked ~ form .tab-content:nth-of-type(1),
    #tab-licence:checked ~ form .tab-content:nth-of-type(2),
    #tab-systeme:checked ~ form .tab-content:nth-of-type(3),
    #tab-securite:checked ~ form .tab-content:nth-of-type(4),
    #tab-pdf:checked ~ form .tab-content:nth-of-type(5),
    #tab-contenu:checked ~ form .tab-content:nth-of-type(6),
    #tab-templates:checked ~ form .tab-content:nth-of-type(7),
    #tab-developpeur:checked ~ form .tab-content:nth-of-type(8) {
        display: block;
    }

    .tab-content h3 {
        margin-top: 0;
        color: #23282d;
        font-size: 18px;
        font-weight: 600;
    }
</style>

</div>