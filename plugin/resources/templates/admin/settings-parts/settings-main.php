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

    <!-- Navigation par onglets moderne -->
    <h2 class="nav-tab-wrapper">
        <div class="tabs-container">
            <button type="button" data-tab="general" class="nav-tab nav-tab-active">
                <span class="tab-icon">⚙️</span>
                <span class="tab-text"><?php _e('Général', 'pdf-builder-pro'); ?></span>
            </button>

            <button type="button" data-tab="licence" class="nav-tab">
                <span class="tab-icon">🔑</span>
                <span class="tab-text"><?php _e('Licence', 'pdf-builder-pro'); ?></span>
            </button>

            <button type="button" data-tab="systeme" class="nav-tab">
                <span class="tab-icon">🖥️</span>
                <span class="tab-text"><?php _e('Système', 'pdf-builder-pro'); ?></span>
            </button>

            <button type="button" data-tab="securite" class="nav-tab">
                <span class="tab-icon">🔒</span>
                <span class="tab-text"><?php _e('Sécurité', 'pdf-builder-pro'); ?></span>
            </button>

            <button type="button" data-tab="pdf" class="nav-tab">
                <span class="tab-icon">📄</span>
                <span class="tab-text"><?php _e('Configuration PDF', 'pdf-builder-pro'); ?></span>
            </button>

            <button type="button" data-tab="contenu" class="nav-tab">
                <span class="tab-icon">🎨</span>
                <span class="tab-text"><?php _e('Canvas & Design', 'pdf-builder-pro'); ?></span>
            </button>

            <button type="button" data-tab="templates" class="nav-tab">
                <span class="tab-icon">📋</span>
                <span class="tab-text"><?php _e('Templates', 'pdf-builder-pro'); ?></span>
            </button>

            <button type="button" data-tab="developpeur" class="nav-tab">
                <span class="tab-icon">👨‍💻</span>
                <span class="tab-text"><?php _e('Développeur', 'pdf-builder-pro'); ?></span>
            </button>
        </div>
    </h2>

    <!-- contenu des onglets moderne -->

    <!-- contenu des onglets moderne -->
    <div class="tabs-content-wrapper">

        <!-- Section Général -->
        <div id="content-general" class="tab-content active">
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
        <div id="content-licence" class="tab-content">
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
        <div id="content-systeme" class="tab-content">
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
        <div id="content-securite" class="tab-content">
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
        <div id="content-pdf" class="tab-content">
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
        <div id="content-contenu" class="tab-content">
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
        <div id="content-templates" class="tab-content">
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
        <div id="content-developpeur" class="tab-content">
            <?php
            $developpeur_file = __DIR__ . '/settings-developpeur.php';
            if (file_exists($developpeur_file)) {
                require_once $developpeur_file;
            } else {
                echo '<p>' . __('Fichier de paramètres développeur manquant.', 'pdf-builder-pro') . '</p>';
            }
            ?>
        </div>

    </div>

    <!-- Bouton flottant de sauvegarde -->
    <button id="pdf-builder-save-floating-btn" class="floating-save-btn" style="display: none; position: fixed; bottom: 20px; right: 20px; z-index: 1000; background: #007cba; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);">
        💾 Enregistrer
    </button>

    <!-- Containers fictifs pour éviter les erreurs JS -->
    <div id="pdf-builder-tabs" style="display: none;"></div>
    <div id="pdf-builder-tab-content" style="display: none;"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('PDF Builder Pro - Initialisation des onglets');

            // Gestion des onglets avec boutons JavaScript
            const tabButtons = document.querySelectorAll('.nav-tab[data-tab]');
            const contents = document.querySelectorAll('.tab-content');
            let activeTab = 'general'; // Onglet actif par défaut

            function updateTabs() {
                // Masquer tous les contenus
                contents.forEach(content => {
                    content.classList.remove('active');
                });

                // Désactiver tous les boutons
                tabButtons.forEach(button => {
                    button.classList.remove('nav-tab-active');
                });

                // Afficher le contenu actif
                const targetContent = document.getElementById('content-' + activeTab);
                if (targetContent) {
                    targetContent.classList.add('active');
                }

                // Activer le bouton actif
                const activeButton = document.querySelector(`.nav-tab[data-tab="${activeTab}"]`);
                if (activeButton) {
                    activeButton.classList.add('nav-tab-active');
                }
            }

            // Écouter les clics sur les boutons
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    activeTab = this.getAttribute('data-tab');
                    updateTabs();
                });
            });

            // Initialisation
            updateTabs();
        });
    </script>

</div> <!-- Fin du .wrap -->

<!-- Inclusion des modales en dehors du .wrap -->
<?php require_once __DIR__ . '/settings-modals.php'; ?>
