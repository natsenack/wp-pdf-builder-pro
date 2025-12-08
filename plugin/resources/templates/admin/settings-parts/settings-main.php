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

    <!-- Boutons radio pour les onglets (invisibles) -->
    <input type="radio" id="tab-general" name="tabs" checked>
    <input type="radio" id="tab-licence" name="tabs">
    <input type="radio" id="tab-systeme" name="tabs">
    <input type="radio" id="tab-securite" name="tabs">
    <input type="radio" id="tab-pdf" name="tabs">
    <input type="radio" id="tab-contenu" name="tabs">
    <input type="radio" id="tab-templates" name="tabs">
    <input type="radio" id="tab-developpeur" name="tabs">

    <!-- Navigation par onglets moderne -->
    <nav class="tabs-navigation">
        <div class="tabs-container">
            <label for="tab-general" class="tab-button">
                <span class="tab-icon">⚙️</span>
                <span class="tab-text"><?php _e('Général', 'pdf-builder-pro'); ?></span>
            </label>
            <label for="tab-licence" class="tab-button">
                <span class="tab-icon">🔑</span>
                <span class="tab-text"><?php _e('Licence', 'pdf-builder-pro'); ?></span>
            </label>
            <label for="tab-systeme" class="tab-button">
                <span class="tab-icon">🖥️</span>
                <span class="tab-text"><?php _e('Système', 'pdf-builder-pro'); ?></span>
            </label>
            <label for="tab-securite" class="tab-button">
                <span class="tab-icon">🔒</span>
                <span class="tab-text"><?php _e('Sécurité', 'pdf-builder-pro'); ?></span>
            </label>
            <label for="tab-pdf" class="tab-button">
                <span class="tab-icon">📄</span>
                <span class="tab-text"><?php _e('Configuration PDF', 'pdf-builder-pro'); ?></span>
            </label>
            <label for="tab-contenu" class="tab-button">
                <span class="tab-icon">🎨</span>
                <span class="tab-text"><?php _e('Canvas & Design', 'pdf-builder-pro'); ?></span>
            </label>
            <label for="tab-templates" class="tab-button">
                <span class="tab-icon">📋</span>
                <span class="tab-text"><?php _e('Templates', 'pdf-builder-pro'); ?></span>
            </label>
            <label for="tab-developpeur" class="tab-button">
                <span class="tab-icon">👨‍💻</span>
                <span class="tab-text"><?php _e('Développeur', 'pdf-builder-pro'); ?></span>
            </label>
        </div>
    </nav>

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

<style>
    /* Masquer les boutons radio */
    input[type="radio"] {
        display: none;
    }

    /* Navigation moderne des onglets */
    .tabs-navigation {
        margin: 20px 0 30px 0;
        border-bottom: 2px solid #e1e1e1;
    }

    .tabs-container {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        background: #f8f9fa;
        padding: 8px;
        border-radius: 8px;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
    }

    .tab-button {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 16px;
        background: transparent;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 14px;
        font-weight: 500;
        color: #6c757d;
        text-decoration: none;
        position: relative;
        min-height: 44px;
        flex: 1;
        justify-content: center;
        white-space: nowrap;
    }

    .tab-button:hover {
        background: rgba(0,123,255,0.1);
        color: #0056b3;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,123,255,0.15);
    }

    .tab-icon {
        font-size: 16px;
        line-height: 1;
    }

    .tab-text {
        font-weight: 500;
    }

    /* Style de l'onglet actif */
    #tab-general:checked ~ .tabs-navigation .tab-button[for="tab-general"],
    #tab-licence:checked ~ .tabs-navigation .tab-button[for="tab-licence"],
    #tab-systeme:checked ~ .tabs-navigation .tab-button[for="tab-systeme"],
    #tab-securite:checked ~ .tabs-navigation .tab-button[for="tab-securite"],
    #tab-pdf:checked ~ .tabs-navigation .tab-button[for="tab-pdf"],
    #tab-contenu:checked ~ .tabs-navigation .tab-button[for="tab-contenu"],
    #tab-templates:checked ~ .tabs-navigation .tab-button[for="tab-templates"],
    #tab-developpeur:checked ~ .tabs-navigation .tab-button[for="tab-developpeur"] {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(0,123,255,0.3);
        transform: translateY(-2px);
    }

    #tab-general:checked ~ .tabs-navigation .tab-button[for="tab-general"] .tab-icon,
    #tab-licence:checked ~ .tabs-navigation .tab-button[for="tab-licence"] .tab-icon,
    #tab-systeme:checked ~ .tabs-navigation .tab-button[for="tab-systeme"] .tab-icon,
    #tab-securite:checked ~ .tabs-navigation .tab-button[for="tab-securite"] .tab-icon,
    #tab-pdf:checked ~ .tabs-navigation .tab-button[for="tab-pdf"] .tab-icon,
    #tab-contenu:checked ~ .tabs-navigation .tab-button[for="tab-contenu"] .tab-icon,
    #tab-templates:checked ~ .tabs-navigation .tab-button[for="tab-templates"] .tab-icon,
    #tab-developpeur:checked ~ .tabs-navigation .tab-button[for="tab-developpeur"] .tab-icon {
        filter: brightness(1.2);
    }

    /* Masquer toutes les sections par défaut */
    .tab-content {
        display: none;
        background: #fff;
        border: 1px solid #e1e1e1;
        border-radius: 8px;
        padding: 24px;
        margin-top: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
        display: block !important;
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .tab-content h3 {
        margin-top: 0;
        color: #2c3e50;
        font-size: 20px;
        font-weight: 600;
        border-bottom: 2px solid #007bff;
        padding-bottom: 8px;
        margin-bottom: 20px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .tabs-container {
            flex-direction: column;
            gap: 2px;
        }

        .tab-button {
            justify-content: flex-start;
            padding: 14px 16px;
        }

        .tab-content {
            padding: 16px;
        }
    }
</style>

</div>