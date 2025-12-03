/**
 * Paramètres PDF Builder Pro - Navigation des onglets (Version Debug)
 */

(function() {
    'use strict';

    const DEBUG = !!(typeof PDF_BUILDER_CONFIG !== 'undefined' && PDF_BUILDER_CONFIG.debug);
    if (DEBUG) {
        console.log('PDF Builder: settings-tabs.js DEBUG MODE ACTIVÉ');
    } else {
        console.log('PDF Builder: settings-tabs.js chargé (debug OFF)');
    }

    // Configuration globale
    const PDF_BUILDER_CONFIG = typeof window.PDF_BUILDER_CONFIG !== 'undefined' ? window.PDF_BUILDER_CONFIG : {};

    // Fonction de debug pour vérifier les éléments DOM
    function debugElements() {
        const container = document.getElementById('pdf-builder-settings-wrapper');
        const tabsContainer = document.getElementById('pdf-builder-tabs');
        const contentContainer = document.getElementById('pdf-builder-tab-content');
        
        console.log('PDF Builder - DIAGNOSTIC DES ÉLÉMENTS DOM:');
        console.log('  - Container principal:', container ? '✅ Trouvé' : '❌ Non trouvé');
        console.log('  - Container onglets:', tabsContainer ? '✅ Trouvé' : '❌ Non trouvé');
        console.log('  - Container contenu:', contentContainer ? '✅ Trouvé' : '❌ Non trouvé');
        
        if (tabsContainer) {
            const tabButtons = tabsContainer.querySelectorAll('.nav-tab');
            if (DEBUG) console.log('  - Boutons onglets trouvés:', tabButtons.length);
            
            tabButtons.forEach(function(btn, index) {
                console.log('    ' + (index + 1) + '. ' + btn.textContent.trim() + ' (data-tab: ' + btn.getAttribute('data-tab') + ')');
            });
        }
        
        if (contentContainer) {
            const tabContents = contentContainer.querySelectorAll('.tab-content');
            if (DEBUG) console.log('  - Contenus onglets trouvés:', tabContents.length);
            
            tabContents.forEach(function(content, index) {
                console.log('    ' + (index + 1) + '. #' + content.id + ' - ' + (content.classList.contains('active') ? 'ACTIF' : 'inactif'));
            });
        }
    }

    // Fonction principale de switch d'onglet avec animations CSS améliorées
    function switchTab(tabId) {
        if (DEBUG) console.log('PDF Builder - SWITCH TAB: Début du changement vers "' + tabId + '"');
        
        const tabButtons = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
        const tabContents = document.querySelectorAll('#pdf-builder-tab-content .tab-content');

        // Log current active states
        const currentActiveBtn = document.querySelector('#pdf-builder-tabs .nav-tab.nav-tab-active');
        const currentActiveContent = document.querySelector('#pdf-builder-tab-content .tab-content.active');
        if (DEBUG) console.log('PDF Builder - Current Active:', {
            btn: currentActiveBtn ? currentActiveBtn.getAttribute('data-tab') : null,
            content: currentActiveContent ? currentActiveContent.id : null
        });
        
        console.log('PDF Builder - Éléments trouvés: ' + tabButtons.length + ' boutons, ' + tabContents.length + ' contenus');
        
        // Ajouter classe de transition
        document.body.classList.add('tabs-transitioning');
        
        // Désactiver tous les onglets avec animation
        console.log('PDF Builder - Désactivation de tous les onglets...');
        tabButtons.forEach(function(btn, index) {
            btn.classList.remove('nav-tab-active');
            if (DEBUG) console.log('  ' + (index + 1) + '. "' + btn.textContent.trim() + '" désactivé');
        });
        
        tabContents.forEach(function(content, index) {
            content.classList.remove('active');
            content.style.opacity = '0';
            content.style.transform = 'translateX(20px)';
            if (DEBUG) console.log('  ' + (index + 1) + '. "#' + content.id + '" désactivé');
        });
        
        // Petite pause pour l'animation de sortie
        setTimeout(function() {
            // Activer l'onglet cible
            console.log('PDF Builder - Activation de l\'onglet "' + tabId + '"...');
            const targetBtn = document.querySelector('[data-tab="' + tabId + '"]');
            // Support IDs that are either 'general' OR prefixed 'tab-general'
            let targetContent = document.getElementById(tabId);
            if (!targetContent) {
                targetContent = document.getElementById('tab-' + tabId);
            }
            
            if (targetBtn) {
                targetBtn.classList.add('nav-tab-active');
                // Ajouter effet de focus
                targetBtn.focus({ preventScroll: true });
                if (DEBUG) console.log('  ✅ Bouton trouvé et activé: "' + targetBtn.textContent.trim() + '"');
            } else {
                console.error('  ❌ ERREUR: Bouton avec data-tab="' + tabId + '" non trouvé!');
            }
            
            if (targetContent) {
                // Activer avec animation d'entrée
                setTimeout(function() {
                    targetContent.classList.add('active');
                    targetContent.style.opacity = '1';
                    targetContent.style.transform = 'translateX(0)';
                    if (DEBUG) console.log('  ✅ Contenu trouvé et activé: "#' + targetContent.id + '"');
                }, 50);
            } else {
                console.error('  ❌ ERREUR: Contenu avec id="' + tabId + '" non trouvé!');
            }
            
            // Retirer la classe de transition après l'animation
            setTimeout(function() {
                document.body.classList.remove('tabs-transitioning');
            }, 350);
            
            // Sauvegarder en localStorage
            try {
                localStorage.setItem('pdf_builder_active_tab', tabId);
                if (DEBUG) console.log('PDF Builder - Onglet "' + tabId + '" sauvegardé en localStorage');
            } catch(e) {
                console.warn('PDF Builder - Impossible de sauvegarder en localStorage:', e.message);
            }
            
            // Déclencher événement personnalisé pour les intégrations
            document.dispatchEvent(new CustomEvent('pdfBuilderTabChanged', {
                detail: { tabId: tabId, timestamp: Date.now() }
            }));
            
            if (DEBUG) console.log('PDF Builder - SWITCH TAB: Terminé pour "' + tabId + '"');
        }, 150);
    }

    // Gestionnaire d'événements avec logs
    function handleTabClick(event) {
        // Use currentTarget to always reference the element the listener was attached to
        const el = event.currentTarget || event.target;
        console.log('PDF Builder - CLIQUE DÉTECTÉ (element):', el);
        
        const tabId = el.getAttribute('data-tab');
        if (!tabId) {
            console.error('PDF Builder - ERREUR: Aucun attribut data-tab trouvé sur l\'élément cliqué!');
            return;
        }
        
        // Prevent the default navigation and propagation after we know we have a data-tab
        event.preventDefault();
        event.stopPropagation();
        if (DEBUG) {
            console.log('PDF Builder - Event details:', {
                defaultPrevented: event.defaultPrevented,
                isTrusted: event.isTrusted,
                pointerType: event.pointerType || null,
                clientX: event.clientX || null,
                clientY: event.clientY || null
            });
        }
        console.log('PDF Builder - LANCEMENT du switch vers "' + tabId + '"');
        switchTab(tabId);
    }

    // Initialisation principale
    function initializeTabs() {
        // Avoid double initialization when fallback AND main script run
        if (window.PDF_BUILDER_TABS_INITIALIZED) {
            if (DEBUG) console.log('PDF Builder - initializeTabs() déjà initialisé, sortie rapide');
            return true;
        }
        window.PDF_BUILDER_TABS_INITIALIZED = true;

        console.log('PDF Builder - INITIALISATION DES ONGLETS');
        
        // Vérifier que les éléments DOM existent
        const tabsContainer = document.getElementById('pdf-builder-tabs');
        const contentContainer = document.getElementById('pdf-builder-tab-content');
        
        if (!tabsContainer) {
            console.error('PDF Builder - ERREUR CRITIQUE: Container #pdf-builder-tabs non trouvé!');
            return false;
        }
        
        if (!contentContainer) {
            console.error('PDF Builder - ERREUR CRITIQUE: Container #pdf-builder-tab-content non trouvé!');
            return false;
        }
        
        // Vérifier les onglets
        const tabButtons = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
        const tabContents = document.querySelectorAll('#pdf-builder-tab-content .tab-content');
        
        if (tabButtons.length === 0) {
            console.error('PDF Builder - ERREUR CRITIQUE: Aucun bouton d\'onglet trouvé!');
            return false;
        }
        
        if (tabContents.length === 0) {
            console.error('PDF Builder - ERREUR CRITIQUE: Aucun contenu d\'onglet trouvé!');
            return false;
        }
        
        console.log('PDF Builder - ' + tabButtons.length + ' onglets et ' + tabContents.length + ' contenus trouvés');
        
        // Attacher les événements de clic (listener par délégation comme fallback)
        console.log('PDF Builder - Attribution des événements de clic (délégation)');
        tabsContainer.addEventListener('click', function(e) {
            const anchor = e.target.closest('.nav-tab');
            if (anchor && tabsContainer.contains(anchor)) {
                if (DEBUG) {
                    const cs = window.getComputedStyle(anchor);
                    console.log('PDF Builder - Délégation: clic détecté sur', anchor.getAttribute('data-tab'), {
                        pointerEvents: cs.pointerEvents,
                        display: cs.display,
                        visibility: cs.visibility,
                        bounds: anchor.getBoundingClientRect()
                    });
                }
                handleTabClick.call(anchor, e);
            }
        });
        // Et aussi attacher sur chaque bouton individuellement pour robustesse
        tabButtons.forEach(function(btn, index) {
            btn.removeEventListener('click', handleTabClick);
            btn.addEventListener('click', handleTabClick);
            if (DEBUG) {
                const cs = window.getComputedStyle(btn);
                console.log('PDF Builder - Btn info: ', index + 1, btn.getAttribute('data-tab'), {
                    pointerEvents: cs.pointerEvents,
                    display: cs.display,
                    visibility: cs.visibility,
                    bounds: btn.getBoundingClientRect()
                });
            }
        });
        
        // Restaurer l'onglet sauvegardé
        try {
            const savedTab = localStorage.getItem('pdf_builder_active_tab');
            if (savedTab && document.getElementById(savedTab)) {
                console.log('PDF Builder - Restauration de l\'onglet sauvegardé: "' + savedTab + '"');
                switchTab(savedTab);
            } else {
                console.log('PDF Builder - Aucun onglet sauvegardé, activation du premier onglet');
                const firstTab = tabButtons[0].getAttribute('data-tab');
                switchTab(firstTab);
            }
        } catch(e) {
            console.warn('PDF Builder - Erreur lors de la restauration de l\'onglet:', e.message);
            // Fallback: activer le premier onglet
            const firstTab = tabButtons[0].getAttribute('data-tab');
            switchTab(firstTab);
        }
        
        console.log('PDF Builder - ONGLETS INITIALISÉS AVEC SUCCÈS');
        return true;
    }

    // Démarrage quand le DOM est prêt
    document.addEventListener('DOMContentLoaded', function() {
        console.log('PDF Builder - DOM CONTENT LOADED - Démarrage de l\'initialisation');
        
        // Attendre un peu pour s'assurer que tous les scripts sont chargés
        setTimeout(function() {
            console.log('PDF Builder - TIMEOUT - Lancement de l\'initialisation différée');
            
            // Logs de diagnostic
            debugElements();
            
            // Initialisation
            const success = initializeTabs();
            
            if (!success) {
                console.error('PDF Builder - ÉCHEC DE L\'INITIALISATION - Vérifiez les éléments DOM');
            }
        }, 100);
    });

    // Pour diagnostiquer les clics capturés plus haut dans la pile (utile si un overlay empêche les clicks)
    if (DEBUG) {
        document.addEventListener('click', function(e) {
            console.log('PDF Builder - Capture-level click event:', e.target, {
                clientX: e.clientX,
                clientY: e.clientY,
                defaultPrevented: e.defaultPrevented
            });
        }, true);
        document.addEventListener('pointerdown', function(e) {
            console.log('PDF Builder - pointerdown event at capture:', e.target);
        }, true);
    }

    // Logs supplémentaires pour le debugging
    console.log('PDF Builder - Script settings-tabs.js chargé jusqu\'à la fin');
})();
