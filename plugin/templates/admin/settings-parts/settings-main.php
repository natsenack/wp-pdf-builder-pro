<?php
if (!defined('ABSPATH')) exit('Direct access forbidden');
if (!is_user_logged_in() || !current_user_can('pdf_builder_access')) wp_die('Access denied');
$settings = get_option('pdf_builder_settings', array());
?>
<main class="wrap" id="pdf-builder-settings-wrapper">
    <header class="pdf-builder-header">
        <h1>Paramètres PDF Builder Pro</h1>
    </header>

    <!-- Monitor root: track JS event listener additions and stopPropagation usage (load only in debug) -->
    <?php if (get_option('pdf_builder_debug_javascript', '0') === '1'): ?>
        <script src="<?php echo esc_url( defined('PDF_BUILDER_PRO_ASSETS_URL') ? PDF_BUILDER_PRO_ASSETS_URL . 'js/tabs-root-monitor.js' : plugin_dir_url( __FILE__ ) . '../../../assets/js/tabs-root-monitor.js' ); ?>" defer></script>
    <?php endif; ?>

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
    // Système de navigation simplifié - juste pour éviter les conflits
    (function() {
        'use strict';

        // Attendre que le système principal soit chargé
        function checkForMainSystem() {
            if (window.PDFBuilderTabsAPI && typeof window.PDFBuilderTabsAPI.switchToTab === 'function') {
                // Système principal chargé, rien à faire
                return;
            }

            // Si pas chargé après 2 secondes, initialiser un système minimal
            setTimeout(function() {
                if (!window.PDFBuilderTabsAPI) {
                    initMinimalTabs();
                }
            }, 2000);
        }

        function initMinimalTabs() {
            const tabsContainer = document.getElementById('pdf-builder-tabs');
            const contentContainer = document.getElementById('pdf-builder-tab-content');

            if (!tabsContainer || !contentContainer) return;

            tabsContainer.addEventListener('click', function(e) {
                const tab = e.target.closest('.nav-tab');
                if (!tab) return;

                e.preventDefault();
                const tabId = tab.getAttribute('data-tab');
                if (!tabId) return;

                // Désactiver tous
                tabsContainer.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('nav-tab-active'));
                contentContainer.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

                // Activer le bon
                tab.classList.add('nav-tab-active');
                const content = document.getElementById(tabId);
                if (content) content.classList.add('active');
            });

            // Activer le premier onglet
            const firstTab = tabsContainer.querySelector('.nav-tab');
            if (firstTab) firstTab.click();
        }

        document.addEventListener('DOMContentLoaded', checkForMainSystem);
    })();
    </script>

        // LOG RACINE - État initial du DOM
        console.log('🔥 ROOT NAVIGATION: Vérification DOM initial');
        const rootTabsContainer = document.getElementById('pdf-builder-tabs');
        const rootContentContainer = document.getElementById('pdf-builder-tab-content');
        console.log('🔥 ROOT NAVIGATION: Container tabs trouvé:', !!rootTabsContainer);
        console.log('🔥 ROOT NAVIGATION: Container content trouvé:', !!rootContentContainer);

        if (rootTabsContainer) {
            const rootTabButtons = rootTabsContainer.querySelectorAll('.nav-tab');
            console.log('🔥 ROOT NAVIGATION: Nombre de boutons onglet:', rootTabButtons.length);
            rootTabButtons.forEach((btn, index) => {
                console.log('🔥 ROOT NAVIGATION: Bouton', index + 1, '- data-tab:', btn.getAttribute('data-tab'), '- text:', btn.textContent.trim());
            });
        }

        if (rootContentContainer) {
            const rootTabContents = rootContentContainer.querySelectorAll('.tab-content');
            console.log('🔥 ROOT NAVIGATION: Nombre de contenus onglet:', rootTabContents.length);
            rootTabContents.forEach((content, index) => {
                console.log('🔥 ROOT NAVIGATION: Contenu', index + 1, '- id:', content.id, '- active:', content.classList.contains('active'));
            });
        }

        // LOG RACINE - État des variables globales
        console.log('🔥 ROOT NAVIGATION: État des variables globales au chargement:');
        console.log('🔥 ROOT NAVIGATION: window.PDF_BUILDER_TABS_INITIALIZED:', window.PDF_BUILDER_TABS_INITIALIZED);
        console.log('🔥 ROOT NAVIGATION: window.PDFBuilderTabsAPI:', !!window.PDFBuilderTabsAPI);
        console.log('🔥 ROOT NAVIGATION: window.PDFBuilderInlineFallbackBound:', window.PDFBuilderInlineFallbackBound);

        // LOG RACINE - Événements de clic globaux
        document.addEventListener('click', function(e) {
            if (e.target.closest && e.target.closest('#pdf-builder-tabs')) {
                console.log('🔥 ROOT NAVIGATION: Clic détecté dans #pdf-builder-tabs:', {
                    target: e.target.tagName + (e.target.id ? '#' + e.target.id : '') + (e.target.className ? '.' + e.target.className : ''),
                    closestNavTab: !!e.target.closest('.nav-tab'),
                    dataTab: e.target.closest('.nav-tab') ? e.target.closest('.nav-tab').getAttribute('data-tab') : null,
                    timestamp: Date.now(),
                    eventPhase: e.eventPhase,
                    defaultPrevented: e.defaultPrevented,
                    propagationStopped: e.cancelBubble
                });
            }
        }, true); // Capture phase pour voir tous les clics

        // LOG RACINE - Changements DOM
        const rootObserver = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.target.id === 'pdf-builder-tabs' || mutation.target.id === 'pdf-builder-tab-content') {
                    console.log('🔥 ROOT NAVIGATION: Mutation DOM détectée:', {
                        type: mutation.type,
                        target: mutation.target.id,
                        addedNodes: mutation.addedNodes.length,
                        removedNodes: mutation.removedNodes.length,
                        attributeName: mutation.attributeName,
                        timestamp: Date.now()
                    });
                }
            });
        });

        if (rootTabsContainer) {
            rootObserver.observe(rootTabsContainer, { childList: true, subtree: true, attributes: true });
        }
        if (rootContentContainer) {
            rootObserver.observe(rootContentContainer, { childList: true, subtree: true, attributes: true });
        }

        // LOG RACINE - Chargement des scripts
        window.addEventListener('load', function() {
            console.log('🔥 ROOT NAVIGATION: Window load - État final:');
            console.log('🔥 ROOT NAVIGATION: PDF_BUILDER_TABS_INITIALIZED:', window.PDF_BUILDER_TABS_INITIALIZED);
            console.log('🔥 ROOT NAVIGATION: PDFBuilderTabsAPI:', !!window.PDFBuilderTabsAPI);
            console.log('🔥 ROOT NAVIGATION: PDFBuilderInlineFallbackBound:', window.PDFBuilderInlineFallbackBound);

            // Vérifier les event listeners
            const tabs = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
            console.log('🔥 ROOT NAVIGATION: Vérification des event listeners sur', tabs.length, 'onglets:');
            tabs.forEach((tab, index) => {
                const listeners = tab._pdfBuilderInlineFallbackHandler || 'AUCUN';
                console.log('🔥 ROOT NAVIGATION: Onglet', index + 1, '- data-tab:', tab.getAttribute('data-tab'), '- handler:', typeof listeners);
            });
        });

        // Fonction principale d'initialisation
        function initTabNavigation() {
            console.log('📋 INLINE NAVIGATION: initTabNavigation appelée - TIMESTAMP:', Date.now());
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
                console.log('📋 INLINE NAVIGATION: switchTab appelée avec tabId:', tabId, '- TIMESTAMP:', Date.now());

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

                const tabId = this.getAttribute('data-tab');
                console.log('📋 INLINE NAVIGATION: handleTabClick déclenché - tabId:', tabId, '- event.target:', event.target, '- TIMESTAMP:', Date.now());

                if (tabId) {
                    switchTab(tabId);
                }
            }

            // Attacher les événements - FORCER l'attachement
            console.log('PDF Builder: Attachement des événements...');
            tabs.forEach(function(tab, index) {
                // Supprimer les anciens événements pour éviter les doublons (si présent)
                try { if (tab._pdfBuilderInlineFallbackHandler && typeof tab._pdfBuilderInlineFallbackHandler === 'function') { tab.removeEventListener('click', tab._pdfBuilderInlineFallbackHandler); } } catch (e) {}
                // Attacher le nouvel événement et conserver une référence pour que le manager canonical puisse l'enlever si besoin
                tab._pdfBuilderInlineFallbackHandler = handleTabClick;
                tab.addEventListener('click', tab._pdfBuilderInlineFallbackHandler);
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

        // Exécution contrôlée: attendre le manager canonical avant d'attacher nos propres handlers
        console.log('PDF Builder: Tentative d\'initialisation contrôlée (attente du manager canonical)...');

        // Pour éviter le double-binding, attendre que window.PDFBuilderTabsAPI soit présent
        // Essayer pendant 5 secondes (250ms * 20). Si le canonical n'arrive pas, on bind le fallback.
        (function waitForCanonicalAndInit() {
            var attempts = 0;
            var maxAttempts = 20; // 20 * 250ms = 5s

            function check() {
                attempts++;
                console.log('📋 INLINE NAVIGATION: Tentative', attempts, '/', maxAttempts, '- État:', {
                    PDF_BUILDER_TABS_INITIALIZED: window.PDF_BUILDER_TABS_INITIALIZED,
                    PDFBuilderTabsAPI: !!window.PDFBuilderTabsAPI,
                    PDFBuilderInlineFallbackBound: window.PDFBuilderInlineFallbackBound,
                    timestamp: Date.now()
                });
                // Si le manager canonical est présent, on l'utilise et on synchronise l'état
                if (window.PDF_BUILDER_TABS_INITIALIZED || (window.PDFBuilderTabsAPI && typeof window.PDFBuilderTabsAPI.switchToTab === 'function')) {
                    console.log('PDF Builder: Manager global détecté pendant attente, fallback inline désactivé');
                    try {
                        const saved = (window.PDFBuilderTabsAPI && window.PDFBuilderTabsAPI.getActiveTab) ? window.PDFBuilderTabsAPI.getActiveTab() : null;
                        if (saved && window.PDFBuilderTabsAPI && typeof window.PDFBuilderTabsAPI.switchToTab === 'function') {
                            window.PDFBuilderTabsAPI.switchToTab(saved);
                        }
                    } catch (e) {
                        console.log('PDF Builder: Erreur lors de la synchronisation avec le manager global', e && e.message ? e.message : e);
                    }
                    return;
                }

                if (attempts >= maxAttempts) {
                    // Pas de manager canonical après attente raisonnable: binder notre fallback (une seule fois)
                    if (!window.PDFBuilderInlineFallbackBound) {
                        console.log('PDF Builder: Aucune détection du manager canonical — binding fallback (inline)');
                        initTabNavigation();
                        window.PDFBuilderInlineFallbackBound = true; // Guard global pour éviter multiples binds
                    } else {
                        console.log('PDF Builder: Fallback inline déjà attaché, skipping');
                    }
                    return;
                }

                // Réessayer plus tard
                setTimeout(check, 250);
            }

            // Initial check + event-based triggers
            check();
            document.addEventListener('DOMContentLoaded', function() { setTimeout(check, 100); });
            window.addEventListener('load', function() { setTimeout(check, 100); });
        })();

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
