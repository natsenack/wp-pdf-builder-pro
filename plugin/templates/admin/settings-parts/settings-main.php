<?php
if (!defined('ABSPATH')) exit('Direct access forbidden');
if (!is_user_logged_in() || !current_user_can('pdf_builder_access')) wp_die('Access denied');
$settings = get_option('pdf_builder_settings', array());
?>
<main class="wrap" id="pdf-builder-settings-wrapper">
    <header class="pdf-builder-header">
        <h1>Paramètres PDF Builder Pro</h1>
    </header>

    <nav class="nav-tab-wrapper wp-clearfix" id="pdf-builder-tabs" role="tablist" aria-label="Onglets des paramètres PDF Builder">
        <a href="#general" class="nav-tab nav-tab-active" data-tab="general" role="tab" aria-selected="true" aria-controls="general">Général</a>
        <a href="#licence" class="nav-tab" data-tab="licence" role="tab" aria-selected="false" aria-controls="licence">Licence</a>
        <a href="#systeme" class="nav-tab" data-tab="systeme" role="tab" aria-selected="false" aria-controls="systeme">Système</a>
        <a href="#acces" class="nav-tab" data-tab="acces" role="tab" aria-selected="false" aria-controls="acces">Accès</a>
        <a href="#securite" class="nav-tab" data-tab="securite" role="tab" aria-selected="false" aria-controls="securite">Sécurité</a>
        <a href="#pdf" class="nav-tab" data-tab="pdf" role="tab" aria-selected="false" aria-controls="pdf">PDF</a>
        <a href="#contenu" class="nav-tab" data-tab="contenu" role="tab" aria-selected="false" aria-controls="contenu">Contenu</a>
        <a href="#templates" class="nav-tab" data-tab="templates" role="tab" aria-selected="false" aria-controls="templates">Modèles</a>
        <a href="#developpeur" class="nav-tab" data-tab="developpeur" role="tab" aria-selected="false" aria-controls="developpeur">Développeur</a>
    </nav>

    <section id="pdf-builder-tab-content" class="tab-content-wrapper" role="tabpanel" aria-live="polite">
        <div id="general" class="tab-content active" role="tabpanel" aria-labelledby="tab-general">
            <?php require_once 'settings-general.php'; ?>
        </div>

        <div id="licence" class="tab-content" role="tabpanel" aria-labelledby="tab-licence">
            <?php require_once 'settings-licence.php'; ?>
        </div>

        <div id="systeme" class="tab-content" role="tabpanel" aria-labelledby="tab-systeme">
            <?php require_once 'settings-systeme.php'; ?>
        </div>

        <div id="acces" class="tab-content" role="tabpanel" aria-labelledby="tab-acces">
            <?php require_once 'settings-acces.php'; ?>
        </div>

        <div id="securite" class="tab-content" role="tabpanel" aria-labelledby="tab-securite">
            <?php require_once 'settings-securite.php'; ?>
        </div>

        <div id="pdf" class="tab-content" role="tabpanel" aria-labelledby="tab-pdf">
            <?php require_once 'settings-pdf.php'; ?>
        </div>

        <div id="contenu" class="tab-content" role="tabpanel" aria-labelledby="tab-contenu">
            <?php require_once 'settings-contenu.php'; ?>
        </div>

        <div id="templates" class="tab-content" role="tabpanel" aria-labelledby="tab-templates">
            <?php require_once 'settings-templates.php'; ?>
        </div>

        <div id="developpeur" class="tab-content" role="tabpanel" aria-labelledby="tab-developpeur">
            <?php require_once 'settings-developpeur.php'; ?>
        </div>
    </section>

    <!-- Navigation JavaScript simplifiée -->
    <script>
    jQuery(document).ready(function($) {
        'use strict';

        console.log('PDF Builder: Initialisation de la navigation par onglets');

        // Vérifier que les éléments existent
        if ($('#pdf-builder-tabs .nav-tab').length === 0) {
            console.error('PDF Builder: Aucun onglet trouvé!');
            return;
        }

        if ($('#pdf-builder-tab-content .tab-content').length === 0) {
            console.error('PDF Builder: Aucun contenu d\'onglet trouvé!');
            return;
        }

        function switchTab(tabId) {
            console.log('PDF Builder: Changement vers onglet:', tabId);

            // Désactiver tous les onglets
            $('#pdf-builder-tabs .nav-tab').removeClass('nav-tab-active').attr('aria-selected', 'false');
            $('#pdf-builder-tab-content .tab-content').removeClass('active');

            // Activer l'onglet cible
            const targetBtn = $('[data-tab="' + tabId + '"]');
            const targetContent = $('#' + tabId);

            if (targetBtn.length) {
                targetBtn.addClass('nav-tab-active').attr('aria-selected', 'true');
                console.log('PDF Builder: Onglet activé:', tabId);
            } else {
                console.error('PDF Builder: Onglet non trouvé:', tabId);
            }

            if (targetContent.length) {
                targetContent.addClass('active');
                console.log('PDF Builder: Contenu activé:', tabId);
            } else {
                console.error('PDF Builder: Contenu non trouvé:', tabId);
            }
        }

        function handleTabClick(event) {
            event.preventDefault();
            const tabId = $(this).data('tab');
            console.log('PDF Builder: Clic sur onglet:', tabId);
            if (tabId) {
                switchTab(tabId);
            }
        }

        // Attacher les événements aux onglets
        $('#pdf-builder-tabs .nav-tab').on('click', handleTabClick);
        console.log('PDF Builder: Événements attachés à', $('#pdf-builder-tabs .nav-tab').length, 'onglets');

        // Gestionnaire pour le bouton flottant de sauvegarde
        $('#pdf-builder-save-all').on('click', function() {
            if (confirm('Voulez-vous sauvegarder tous les paramètres ?')) {
                // Simuler la sauvegarde - à implémenter selon le système AJAX existant
                alert('Fonction de sauvegarde globale à implémenter');
            }
        });

        // Test initial
        console.log('PDF Builder: Navigation initialisée');
        console.log('PDF Builder: Onglets trouvés:', $('#pdf-builder-tabs .nav-tab').length);
        console.log('PDF Builder: Contenus trouvés:', $('#pdf-builder-tab-content .tab-content').length);

        // Forcer l'affichage de l'onglet actif initial
        const activeTab = $('#pdf-builder-tabs .nav-tab-active').data('tab');
        if (activeTab) {
            switchTab(activeTab);
        }
    });
    </script>

    <!-- Bouton flottant de sauvegarde -->
    <button type="button" id="pdf-builder-save-all" class="pdf-builder-floating-save-btn" title="Sauvegarder tous les paramètres">
        💾 Enregistrer
    </button>

    <style>
    /* Styles pour la navigation par onglets */
    .tab-content {
        display: none;
        padding: 20px 0;
    }
    .tab-content.active {
        display: block;
    }
    .nav-tab {
        cursor: pointer;
        text-decoration: none;
    }
    .nav-tab-active {
        background: #fff;
        border-bottom: 1px solid #fff;
        color: #23282d;
    }

    .pdf-builder-floating-save-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #007cba;
        color: white;
        border: none;
        border-radius: 50px;
        padding: 12px 20px;
        font-size: 14px;
        font-weight: bold;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 9999;
        transition: all 0.3s ease;
    }
    .pdf-builder-floating-save-btn:hover {
        background: #005a87;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.4);
    }
    .pdf-builder-floating-save-btn:active {
        transform: translateY(0);
    }
    </style>
</main>
