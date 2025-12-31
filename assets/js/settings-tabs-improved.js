/**
 * PDF Builder Pro - Navigation des onglets améliorée
 * Version corrigée avec animations CSS fluides
 */

(function() {
    'use strict';

    // Si un manager canonical est présent, sortir pour éviter conflits
    if (typeof window !== 'undefined' && window.PDFBuilderTabsAPI && typeof window.PDFBuilderTabsAPI.switchToTab === 'function') {
        return;
    }

    // Configuration globale
    const CONFIG = {
        debug: !!(typeof window.PDF_BUILDER_CONFIG !== 'undefined' && window.PDF_BUILDER_CONFIG.debug),
        animationDuration: 300,
        storageKey: 'pdf_builder_active_tab'
    };

    // Variables globales
    let tabsContainer = null;
    let contentContainer = null;
    let tabButtons = [];
    let tabContents = [];
    let activeTab = null;
    let initialized = false;

    // Fonction de logging avec fallback
    function log() {
        // Logging disabled for production
    }

    function error() {
        // Error logging disabled for production
    }

    // Fonction de debug pour vérifier les éléments DOM
    function debugElements() {
        if (typeof document === 'undefined') return;

        const container = document.getElementById('pdf-builder-settings-wrapper');
        tabsContainer = document.getElementById('pdf-builder-tabs');
        contentContainer = document.getElementById('pdf-builder-tab-content');
        
        log('🔍 PDF BUILDER TABS: Diagnostic des éléments DOM');
        log('📍 PDF BUILDER TABS: URL actuelle:', typeof window !== 'undefined' && window.location ? window.location.href : 'N/A');
        log('🌐 PDF BUILDER TABS: User Agent:', typeof navigator !== 'undefined' ? navigator.userAgent : 'N/A');
        
        // Logs de test pour visibilité (temporaires)
        console.warn('🚨 PDF BUILDER TABS: LOG WARNING POUR TEST VISIBILITÉ');
        console.error('💥 PDF BUILDER TABS: LOG ERROR POUR TEST VISIBILITÉ');
        
        log('✅ PDF BUILDER TABS: Console disponible');
        log('🔄 PDF BUILDER TABS: Début de l\'exécution du script');
        log('  - Container principal:', container ? '✅ Trouvé' : '❌ Non trouvé');
        log('  - Container onglets:', tabsContainer ? '✅ Trouvé' : '❌ Non trouvé');
        log('  - Container contenu:', contentContainer ? '✅ Trouvé' : '❌ Non trouvé');
        
        if (tabsContainer) {
            tabButtons = tabsContainer.querySelectorAll('.nav-tab');
            log('  - Boutons onglets trouvés:', tabButtons.length);
            
            Array.prototype.forEach.call(tabButtons, function(btn, index) {
                log('    ' + (index + 1) + '. ' + btn.textContent.trim() + ' (data-tab: ' + btn.getAttribute('data-tab') + ')');
            });
        }
        
        if (contentContainer) {
            tabContents = contentContainer.querySelectorAll('.tab-content');
            log('  - Contenus onglets trouvés:', tabContents.length);
            
            Array.prototype.forEach.call(tabContents, function(content, index) {
                log('    ' + (index + 1) + '. #' + content.id + ' - ' + (content.classList.contains('active') ? 'ACTIF' : 'inactif'));
            });
        }
    }

    // Fonction de validation des éléments
    function validateElements() {
        if (!tabsContainer || !contentContainer) {
            error('PDF Builder - ERREUR CRITIQUE: Containers requis non trouvés');
            return false;
        }

        if (tabButtons.length === 0 || tabContents.length === 0) {
            error('PDF Builder - ERREUR CRITIQUE: Aucun bouton ou contenu d\'onglet trouvé');
            return false;
        }

        // Vérifier les attributs data-tab manquants
        Array.prototype.forEach.call(tabButtons, function(button, index) {
            if (!button.getAttribute('data-tab')) {
                const id = button.id || 'tab-' + index;
                button.setAttribute('data-tab', id);
                log('🔧 Ajout de data-tab manquant sur le bouton:', id);
            }
        });

        return true;
    }

    // Fonction principale de switch d'onglet avec animations CSS
    function switchTab(tabId) {
        if (!tabId) return;

        log('PDF Builder - SWITCH TAB: Début du changement vers "' + tabId + '"');

        // Vérifier que l'onglet cible existe
        const targetBtn = document.querySelector('[data-tab="' + tabId + '"]');
        const targetContent = document.getElementById(tabId) || document.getElementById('tab-' + tabId);

        if (!targetBtn || !targetContent) {
            error('PDF Builder - ERREUR: Onglet cible non trouvé:', tabId);
            return;
        }

        // Ajouter classe de transition
        if (typeof document !== 'undefined' && document.body) {
            document.body.classList.add('tabs-transitioning');
        }

        // Désactiver tous les onglets avec animation
        log('PDF Builder - Désactivation de tous les onglets...');
        
        Array.prototype.forEach.call(tabButtons, function(btn) {
            btn.classList.remove('nav-tab-active');
            btn.setAttribute('aria-selected', 'false');
        });
        
        Array.prototype.forEach.call(tabContents, function(content) {
            content.classList.remove('active');
            content.setAttribute('aria-hidden', 'true');
            if (content.style) {
                content.style.opacity = '0';
                content.style.transform = 'translateX(20px)';
            }
        });
        
        // Activer l'onglet cible avec délai pour l'animation
        setTimeout(function() {
            log('PDF Builder - Activation de l\'onglet "' + tabId + '"...');
            
            // Activer le bouton
            targetBtn.classList.add('nav-tab-active');
            targetBtn.setAttribute('aria-selected', 'true');
            
            // Activer le contenu avec animation
            targetContent.classList.add('active');
            targetContent.setAttribute('aria-hidden', 'false');
            
            if (targetContent.style) {
                setTimeout(function() {
                    targetContent.style.opacity = '1';
                    targetContent.style.transform = 'translateX(0)';
                }, 50);
            }
            
            // Mettre à jour l'onglet actif
            activeTab = tabId;
            
            // Retirer la classe de transition
            setTimeout(function() {
                if (typeof document !== 'undefined' && document.body) {
                    document.body.classList.remove('tabs-transitioning');
                }
            }, CONFIG.animationDuration + 50);
            
            // Sauvegarder en localStorage
            try {
                if (typeof localStorage !== 'undefined') {
                    localStorage.setItem(CONFIG.storageKey, tabId);
                }
                log('PDF Builder - Onglet "' + tabId + '" sauvegardé en localStorage');
            } catch(e) {
                log('PDF Builder - Impossible de sauvegarder en localStorage:', e.message);
            }
            
            // Déclencher événement personnalisé
            if (typeof document !== 'undefined' && document.dispatchEvent) {
                const event = new CustomEvent('pdfBuilderTabChanged', {
                    detail: { tabId: tabId, timestamp: Date.now() }
                });
                document.dispatchEvent(event);
            }
            
            log('PDF Builder - SWITCH TAB: Terminé pour "' + tabId + '"');
        }, 150);
    }

    // Gestionnaire d'événements pour les clics
    function handleTabClick(event) {
        event.preventDefault();
        event.stopPropagation();
        
        const el = event.currentTarget || event.target;
        const tabId = el.getAttribute('data-tab');
        
        if (!tabId) {
            error('PDF Builder - ERREUR: Aucun attribut data-tab trouvé!');
            return;
        }
        
        log('PDF Builder - CLIC DÉTECTÉ sur l\'onglet:', tabId);
        switchTab(tabId);
    }

    // Attacher les événements
    function bindEvents() {
        if (!tabsContainer) return;

        log('PDF Builder - Attribution des événements de clic...');

        // Délégation d'événements (fallback robuste)
        tabsContainer.addEventListener('click', function(e) {
            const anchor = e.target.closest('.nav-tab');
            if (anchor && tabsContainer.contains(anchor)) {
                handleTabClick.call(anchor, e);
            }
        });

        // Événements individuels pour chaque bouton
        Array.prototype.forEach.call(tabButtons, function(btn) {
            btn.removeEventListener('click', handleTabClick);
            btn.addEventListener('click', handleTabClick);
        });

        // Gestion des touches clavier
        tabsContainer.addEventListener('keydown', function(e) {
            handleKeyboardNavigation(e);
        });
    }

    // Navigation au clavier
    function handleKeyboardNavigation(e) {
        const activeButton = tabsContainer.querySelector('.nav-tab-active');
        if (!activeButton) return;

        const buttons = Array.prototype.slice.call(tabButtons);
        const currentIndex = buttons.indexOf(activeButton);

        let newIndex = currentIndex;

        switch (e.key) {
            case 'ArrowLeft':
                newIndex = currentIndex > 0 ? currentIndex - 1 : buttons.length - 1;
                break;
            case 'ArrowRight':
                newIndex = currentIndex < buttons.length - 1 ? currentIndex + 1 : 0;
                break;
            case 'Home':
                newIndex = 0;
                break;
            case 'End':
                newIndex = buttons.length - 1;
                break;
            default:
                return;
        }

        e.preventDefault();
        const newButton = buttons[newIndex];
        const newTabId = newButton.getAttribute('data-tab');
        if (newTabId) {
            switchTab(newTabId);
            newButton.focus();
        }
    }

    // Obtenir l'onglet sauvegardé
    function getStoredActiveTab() {
        try {
            if (typeof localStorage !== 'undefined') {
                return localStorage.getItem(CONFIG.storageKey);
            }
        } catch(e) {
            // localStorage non disponible
        }
        return null;
    }

    // Obtenir l'onglet par défaut
    function getDefaultActiveTab() {
        // Essayer de récupérer depuis l'URL hash
        if (typeof window !== 'undefined' && window.location) {
            const hash = window.location.hash.substring(1);
            if (hash) {
                const tabExists = document.getElementById(hash) || document.getElementById('tab-' + hash);
                if (tabExists) return hash;
            }
        }

        // Sinon, premier onglet disponible
        if (tabButtons.length > 0) {
            return tabButtons[0].getAttribute('data-tab');
        }
        return null;
    }

    // Initialisation principale
    function initializeTabs() {
        if (initialized) {
            log('PDF Builder - already initialized, skipping...');
            return true;
        }

        if (typeof window !== 'undefined') {
            window.PDF_BUILDER_TABS_INITIALIZED = true;
        }

        log('PDF Builder - INITIALISATION DES ONGLETS AMÉLIORÉE');
        
        // Diagnostic des éléments
        debugElements();
        
        // Validation
        if (!validateElements()) {
            return false;
        }
        
        log('PDF Builder - ' + tabButtons.length + ' onglets et ' + tabContents.length + ' contenus trouvés');
        
        // Ajouter classes CSS pour le contrôle de style
        if (typeof document !== 'undefined' && document.body) {
            document.body.classList.add('js-enabled');
        }
        
        // Lier les événements
        bindEvents();
        
        // Déterminer l'onglet initial
        const savedTab = getStoredActiveTab();
        activeTab = (savedTab && document.getElementById(savedTab)) ? savedTab : getDefaultActiveTab();
        
        if (activeTab) {
            log('PDF Builder - Activation de l\'onglet initial:', activeTab);
            switchTab(activeTab);
        } else {
            log('PDF Builder - ERREUR: Aucun onglet par défaut trouvé');
            return false;
        }
        
        log('PDF Builder - ONGLETS INITIALISÉS AVEC SUCCÈS');
        return true;
    }

    // Démarrage quand le DOM est prêt
    function onDOMReady() {
        log('🚀 PDF Builder: DOM chargé, initialisation des onglets');
        
        // Attendre que tous les scripts soient chargés
        setTimeout(function() {
            log('⏱️ PDF Builder: Timeout écoulé, lancement de l\'initialisation');
            initializeTabs();
        }, 100);
    }

    // Event listeners de démarrage
    if (typeof document !== 'undefined') {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', onDOMReady);
        } else {
            onDOMReady();
        }
    }

    // API globale pour la compatibilité
    if (typeof window !== 'undefined') {
        window.PDFBuilderTabsAPI = {
            switchToTab: switchTab,
            getActiveTab: function() { return activeTab; },
            initialize: initializeTabs
        };
    }

    log('PDF Builder - Script settings-tabs-improved.js chargé');

})();