<?php
if (!defined('ABSPATH')) exit('Direct access forbidden');
if (!is_user_logged_in() || !current_user_can('pdf_builder_access')) wp_die('Access denied');
$settings = get_option('pdf_builder_settings', array());
?>
<main class="wrap" id="pdf-builder-settings-wrapper">
    <header class="pdf-builder-header">
        <h1>Paramètres PDF Builder Pro</h1>
    </header>

    <!-- Monitor root: track JS event listener additions and stopPropagation usage -->
    <script src="<?php echo plugin_dir_url( __FILE__ ) . '../../../assets/js/tabs-root-monitor.js'; ?>" defer></script>

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
    (function() {
        'use strict';

        console.log('PDF Builder: Script de navigation chargé');

        // Fonction principale d'initialisation
        function initTabNavigation() {
            // Si le manager principal est présent, ne pas attacher nos propres handlers
            if (window.PDF_BUILDER_TABS_INITIALIZED || (window.PDFBuilderTabsAPI && typeof window.PDFBuilderTabsAPI.switchToTab === 'function')) {
                console.log('PDF Builder: Manager global détecté, fallback inline désactivé');
                try {
                    const saved = (window.PDFBuilderTabsAPI && window.PDFBuilderTabsAPI.getActiveTab) ? window.PDFBuilderTabsAPI.getActiveTab() : null;
                    if (saved && window.PDFBuilderTabsAPI && typeof window.PDFBuilderTabsAPI.switchToTab === 'function') {
                        window.PDFBuilderTabsAPI.switchToTab(saved);
                    }
                } catch (e) {
                    console.log('PDF Builder: Erreur lors de la synchronisation avec le manager global', e && e.message ? e.message : e);
                }
                return true;
            }
            console.log('PDF Builder: Initialisation forcée de la navigation');

            const tabsContainer = document.getElementById('pdf-builder-tabs');
            const contentContainer = document.getElementById('pdf-builder-tab-content');

            if (!tabsContainer || !contentContainer) {
                console.error('PDF Builder: Conteneurs non trouvés, retry dans 1s...');
                setTimeout(initTabNavigation, 1000);
                return;
            }

            const tabs = tabsContainer.querySelectorAll('.nav-tab');
            const contents = contentContainer.querySelectorAll('.tab-content');

            console.log('PDF Builder: Trouvé', tabs.length, 'onglets et', contents.length, 'contenus');

            if (tabs.length === 0 || contents.length === 0) {
                console.error('PDF Builder: Éléments manquants, retry dans 1s...');
                setTimeout(initTabNavigation, 1000);
                return;
            }

            // Fonction de changement d'onglet
            function switchTab(tabId) {
                console.log('PDF Builder: Changement vers', tabId);

                // Désactiver tous les onglets
                tabs.forEach(function(tab) {
                    tab.classList.remove('nav-tab-active');
                    tab.setAttribute('aria-selected', 'false');
                });

                // Désactiver tous les contenus
                contents.forEach(function(content) {
                    content.classList.remove('active');
                });

                // Activer l'onglet cible
                const targetTab = tabsContainer.querySelector('[data-tab="' + tabId + '"]');
                const targetContent = document.getElementById(tabId);

                if (targetTab) {
                    targetTab.classList.add('nav-tab-active');
                    targetTab.setAttribute('aria-selected', 'true');
                    console.log('PDF Builder: Onglet', tabId, 'activé');
                }

                if (targetContent) {
                    targetContent.classList.add('active');
                    console.log('PDF Builder: Contenu', tabId, 'affiché');
                }
            }

            // Gestionnaire de clic
            function handleTabClick(event) {
                event.preventDefault();
                event.stopPropagation();

                const tabId = this.getAttribute('data-tab');
                console.log('PDF Builder: Clic détecté sur onglet', tabId);

                if (tabId) {
                    switchTab(tabId);
                }
            }

            // Attacher les événements - FORCER l'attachement
            console.log('PDF Builder: Attachement des événements...');
            tabs.forEach(function(tab, index) {
                // Supprimer les anciens événements pour éviter les doublons
                tab.removeEventListener('click', handleTabClick);
                // Attacher le nouvel événement
                tab.addEventListener('click', handleTabClick);
                console.log('PDF Builder: Événement attaché à onglet', index + 1, ':', tab.getAttribute('data-tab'));
            });

            // Bouton de sauvegarde
            const saveBtn = document.getElementById('pdf-builder-save-all');
            if (saveBtn) {
                saveBtn.addEventListener('click', function() {
                    alert('Sauvegarde globale à implémenter');
                });
                console.log('PDF Builder: Bouton de sauvegarde configuré');
            }

            // Activer l'onglet par défaut
            const activeTab = tabsContainer.querySelector('.nav-tab-active');
            if (activeTab) {
                const activeTabId = activeTab.getAttribute('data-tab');
                if (activeTabId) {
                    switchTab(activeTabId);
                }
            }

            console.log('PDF Builder: Navigation initialisée avec succès!');
            return true;
        }

        // Exécution immédiate
        console.log('PDF Builder: Tentative d\'initialisation immédiate...');
        initTabNavigation();

        // Fallbacks multiples
        setTimeout(initTabNavigation, 500);
        setTimeout(initTabNavigation, 1000);
        setTimeout(initTabNavigation, 2000);
        setTimeout(initTabNavigation, 5000);

        // Aussi au chargement du DOM si pas déjà fait
        document.addEventListener('DOMContentLoaded', function() {
            console.log('PDF Builder: DOM chargé, vérification...');
            setTimeout(initTabNavigation, 100);
        });

        // Et aussi au chargement complet de la page
        window.addEventListener('load', function() {
            console.log('PDF Builder: Page chargée, vérification finale...');
            setTimeout(initTabNavigation, 100);
        });

    })();
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
