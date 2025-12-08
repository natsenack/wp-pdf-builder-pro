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
    <nav class="tabs-navigation">
        <div class="tabs-container">
            <button type="button" data-tab="general" class="tab-button active">
                <span class="tab-icon">⚙️</span>
                <span class="tab-text"><?php _e('Général', 'pdf-builder-pro'); ?></span>
            </button>

            <button type="button" data-tab="licence" class="tab-button">
                <span class="tab-icon">🔑</span>
                <span class="tab-text"><?php _e('Licence', 'pdf-builder-pro'); ?></span>
            </button>

            <button type="button" data-tab="systeme" class="tab-button">
                <span class="tab-icon">🖥️</span>
                <span class="tab-text"><?php _e('Système', 'pdf-builder-pro'); ?></span>
            </button>

            <button type="button" data-tab="securite" class="tab-button">
                <span class="tab-icon">🔒</span>
                <span class="tab-text"><?php _e('Sécurité', 'pdf-builder-pro'); ?></span>
            </button>

            <button type="button" data-tab="pdf" class="tab-button">
                <span class="tab-icon">📄</span>
                <span class="tab-text"><?php _e('Configuration PDF', 'pdf-builder-pro'); ?></span>
            </button>

            <button type="button" data-tab="contenu" class="tab-button">
                <span class="tab-icon">🎨</span>
                <span class="tab-text"><?php _e('Canvas & Design', 'pdf-builder-pro'); ?></span>
            </button>

            <button type="button" data-tab="templates" class="tab-button">
                <span class="tab-icon">📋</span>
                <span class="tab-text"><?php _e('Templates', 'pdf-builder-pro'); ?></span>
            </button>

            <button type="button" data-tab="developpeur" class="tab-button">
                <span class="tab-icon">👨‍💻</span>
                <span class="tab-text"><?php _e('Développeur', 'pdf-builder-pro'); ?></span>
            </button>
        </div>
    </nav>

    <!-- contenu des onglets moderne -->

    <!-- contenu des onglets moderne - HORS du form pour éviter les problèmes de structure -->
    <div class="tabs-content-wrapper">

        <!-- Section Général -->
        <div id="content-general" class="tab-content">
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
        <div id="content-licence" class="tab-content">
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
        <div id="content-systeme" class="tab-content">
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
        <div id="content-securite" class="tab-content">
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
        <div id="content-pdf" class="tab-content">
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
        <div id="content-contenu" class="tab-content">
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
        <div id="content-templates" class="tab-content">
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
        <div id="content-developpeur" class="tab-content">
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

    </div>

    <!-- Bouton flottant de sauvegarde -->
    <button id="pdf-builder-save-floating-btn" class="floating-save-btn" style="display: none; position: fixed; bottom: 20px; right: 20px; z-index: 1000; background: #007cba; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);">
        💾 Enregistrer
    </button>

    <!-- Inclusion des modales -->
    <?php require_once __DIR__ . '/settings-modals.php'; ?>

    <!-- Containers fictifs pour éviter les erreurs JS -->
    <div id="pdf-builder-tabs" style="display: none;"></div>
    <div id="pdf-builder-tab-content" style="display: none;"></div>

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
            justify-content: center;
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
        .tab-button.active {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(0,123,255,0.3);
            transform: translateY(-2px);
        }

        .tab-button.active .tab-icon {
            filter: brightness(1.2);
        }

        /* Wrapper pour les contenus d'onglets */
        .tabs-content-wrapper {
            margin-top: 20px;
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

        .tab-content.active {
            display: block;
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('PDF Builder Pro - Initialisation des onglets');

            // Gestion des onglets avec boutons JavaScript
            const tabButtons = document.querySelectorAll('.tab-button[data-tab]');
            const contents = document.querySelectorAll('.tab-content');
            let activeTab = 'general'; // Onglet actif par défaut

            console.log('Boutons trouvés:', tabButtons.length);
            console.log('Contenus trouvés:', contents.length);

            // Vérifier chaque container de contenu
            contents.forEach(content => {
                console.log('Container trouvé:', content.id, content.classList.contains('active') ? '(actif)' : '(inactif)');
            });

            // Vérifier chaque bouton
            tabButtons.forEach(button => {
                console.log('Bouton trouvé:', button.getAttribute('data-tab'), button.classList.contains('active') ? '(actif)' : '(inactif)');
            });

            function updateTabs() {
                console.log('Mise à jour des onglets - actif:', activeTab);

                // Masquer tous les contenus
                contents.forEach(content => {
                    content.classList.remove('active');
                    console.log('Contenu masqué:', content.id);
                });

                // Désactiver tous les boutons
                tabButtons.forEach(button => {
                    button.classList.remove('active');
                    console.log('Bouton désactivé:', button.getAttribute('data-tab'));
                });

                // Afficher le contenu actif
                const targetContent = document.getElementById('content-' + activeTab);
                if (targetContent) {
                    targetContent.classList.add('active');
                    console.log('Contenu affiché:', targetContent.id);
                } else {
                    console.error('Contenu non trouvé pour:', activeTab);
                }

                // Activer le bouton actif
                const activeButton = document.querySelector(`.tab-button[data-tab="${activeTab}"]`);
                if (activeButton) {
                    activeButton.classList.add('active');
                    console.log('Bouton activé:', activeTab);
                } else {
                    console.error('Bouton non trouvé pour:', activeTab);
                }
            }

            // Écouter les clics sur les boutons
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const newTab = this.getAttribute('data-tab');
                    console.log('Clic sur bouton:', newTab);
                    activeTab = newTab;
                    updateTabs();
                });
            });

            // Initialisation
            console.log('Initialisation terminée');
            updateTabs();
        });
    </script>
</div> <!-- Fin du .wrap -->