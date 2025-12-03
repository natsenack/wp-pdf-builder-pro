<?php
if (!defined('ABSPATH')) exit('Direct access forbidden');
if (!is_user_logged_in() || !current_user_can('pdf_builder_access')) wp_die('Access denied');
$settings = get_option('pdf_builder_settings', array());
?>
<!-- Settings page loaded -->
<main class="wrap" id="pdf-builder-settings-wrapper">
    <header class="pdf-builder-header">
        <h1>Paramètres PDF Builder Pro</h1>
    </header>



    <nav class="nav-tab-wrapper wp-clearfix" id="pdf-builder-tabs" role="tablist" aria-label="Onglets des paramètres PDF Builder">
        <a id="tab-general" href="#general" class="nav-tab nav-tab-active" data-tab="general" role="tab" aria-selected="true" aria-controls="general">Général</a>
        <a id="tab-licence" href="#licence" class="nav-tab" data-tab="licence" role="tab" aria-selected="false" aria-controls="licence">Licence</a>
        <a id="tab-systeme" href="#systeme" class="nav-tab" data-tab="systeme" role="tab" aria-selected="false" aria-controls="systeme">Système</a>
        <a id="tab-acces" href="#acces" class="nav-tab" data-tab="acces" role="tab" aria-selected="false" aria-controls="acces">Accès</a>
        <a id="tab-securite" href="#securite" class="nav-tab" data-tab="securite" role="tab" aria-selected="false" aria-controls="securite">Sécurité</a>
        <a id="tab-pdf" href="#pdf" class="nav-tab" data-tab="pdf" role="tab" aria-selected="false" aria-controls="pdf">PDF</a>
        <a id="tab-contenu" href="#contenu" class="nav-tab" data-tab="contenu" role="tab" aria-selected="false" aria-controls="contenu">Contenu</a>
        <a id="tab-templates" href="#templates" class="nav-tab" data-tab="templates" role="tab" aria-selected="false" aria-controls="templates">Modèles</a>
        <a id="tab-developpeur" href="#developpeur" class="nav-tab" data-tab="developpeur" role="tab" aria-selected="false" aria-controls="developpeur">Développeur</a>
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

    <!-- Modales de configuration - Chargées après tous les onglets pour éviter les conflits de structure -->
    <?php require_once 'settings-modals.php'; ?>

    <!-- Navigation JavaScript - Gérée par assets/js/settings-tabs.js -->
    <!-- Le fichier settings-tabs.js fournit PDFBuilderTabsAPI avec switchToTab(), getActiveTab() -->

    <!-- Styles inline de secours (au cas où le CSS ne chargerait pas) -->
    <style>
    /* Styles pour la navigation par onglets */
    #pdf-builder-tab-content .tab-content {
        display: none;
        padding: 20px 0;
    }
    #pdf-builder-tab-content .tab-content.active {
        display: block;
    }
    #pdf-builder-tabs .nav-tab {
        cursor: pointer;
    }
    </style>
</main>

<!-- Script de secours inline (APRÈS le main pour que les éléments existent) -->
<script>
(function() {
    // Vérifier si settings-tabs.js est chargé
    setTimeout(function() {
        if (!window.PDFBuilderTabsAPI) {
            initMinimalTabs();
        }
    }, 1000);

    function initMinimalTabs() {
        const tabsContainer = document.getElementById('pdf-builder-tabs');
        const contentContainer = document.getElementById('pdf-builder-tab-content');

        if (!tabsContainer || !contentContainer) {
            return;
        }

        // Gestionnaire de clic pour les onglets
        tabsContainer.addEventListener('click', function(e) {
            const tab = e.target.closest('.nav-tab');
            if (!tab) return;

            e.preventDefault();
            const tabId = tab.getAttribute('data-tab');
            if (!tabId) return;

            // Désactiver tous les onglets
            tabsContainer.querySelectorAll('.nav-tab').forEach(t => {
                t.classList.remove('nav-tab-active');
                t.setAttribute('aria-selected', 'false');
            });

            // Désactiver tous les contenus
            contentContainer.querySelectorAll('.tab-content').forEach(c => {
                c.classList.remove('active');
            });

            // Activer l'onglet cliqué
            tab.classList.add('nav-tab-active');
            tab.setAttribute('aria-selected', 'true');

            // Activer le contenu correspondant
            const content = document.getElementById(tabId);
            if (content) {
                content.classList.add('active');
            }

            // Sauvegarder dans localStorage
            try {
                localStorage.setItem('pdf_builder_active_tab', tabId);
            } catch (e) {
                // Ignore
            }
        });

        // Restaurer l'onglet sauvegardé
        try {
            const savedTab = localStorage.getItem('pdf_builder_active_tab');
            if (savedTab) {
                const savedTabElement = tabsContainer.querySelector('[data-tab="' + savedTab + '"]');
                if (savedTabElement) {
                    savedTabElement.click();
                    return;
                }
            }
        } catch (e) {
            // Ignore
        }

        // Activer le premier onglet par défaut
        const firstTab = tabsContainer.querySelector('.nav-tab');
        if (firstTab) {
            firstTab.click();
        }
    }
})();
</script>
