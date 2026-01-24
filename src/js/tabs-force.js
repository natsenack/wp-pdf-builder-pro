/**
 * Navigation des onglets PDF Builder - Version Force Chargement
 */

(function() {
    'use strict';

    // Configuration de force
    const CONFIG = {
        debug: true,
        forceLoad: true
    };

    // Fonction de logging
    function log(message, data = null) {
        // Logging disabled for production
    function switchTab(tabId) {
        log('SWITCH vers:', tabId);
        
        const tabButtons = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
        const tabContents = document.querySelectorAll('#pdf-builder-tab-content .tab-content');

        log('Éléments trouvés:', { buttons: tabButtons.length, contents: tabContents.length });
        
        // Désactiver tous
        tabButtons.forEach(btn => btn.classList.remove('nav-tab-active'));
        tabContents.forEach(content => content.classList.remove('active'));
        
        // Activer l'onglet cible
        const targetBtn = document.querySelector(`[data-tab="${tabId}"]`);
        const targetContent = document.getElementById(tabId) || document.getElementById(`tab-${tabId}`) || document.getElementById(`tab-content-${tabId}`);
        
        if (targetBtn) {
            targetBtn.classList.add('nav-tab-active');
            log('✅ Bouton activé:', targetBtn.textContent.trim());
        } else {
            log('❌ Bouton non trouvé pour:', tabId);
        }
        
        if (targetContent) {
            targetContent.classList.add('active');
            log('✅ Contenu activé:', targetContent.id);
        } else {
            log('❌ Contenu non trouvé pour:', tabId);
        }
        
        // Si un manager global existe, déléguer l'action
        if (window.PDF_BUILDER_TABS && typeof window.PDF_BUILDER_TABS.switchToTab === 'function') {
            try {
                window.PDF_BUILDER_TABS.switchToTab(tabId);
                log('Délégué switchTab au manager global');
                return;
            } catch (e) {
                log('Erreur lors de l\'appel du manager global:', e.message || e);
            }
        }

        // Déclencher événement si aucun manager global
        document.dispatchEvent(new CustomEvent('pdfBuilderTabChanged', {
            detail: { tabId: tabId, source: 'force' }
        }));
        
        log('SWITCH terminé pour:', tabId);
    }

    // Gestionnaire de clic
    function handleTabClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const tabId = e.currentTarget.getAttribute('data-tab');
        if (!tabId) {
            log('❌ Aucun data-tab trouvé');
            return;
        }
        
        log('CLIC détecté sur:', tabId);
        // Si un manager global existe, utilisez son API pour s'assurer d'un comportement centralisé
        if (window.PDF_BUILDER_TABS && typeof window.PDF_BUILDER_TABS.switchToTab === 'function') {
            window.PDF_BUILDER_TABS.switchToTab(tabId);
            return;
        }
        switchTab(tabId);
    }

    // Initialisation
    function initialize() {
        log('INITIALISATION FORCE');
        
        const tabsContainer = document.getElementById('pdf-builder-tabs');
        const contentContainer = document.getElementById('pdf-builder-tab-content');
        
        if (!tabsContainer) {
            log('❌ Container onglets non trouvé');
            return false;
        }
        
        if (!contentContainer) {
            log('❌ Container contenu non trouvé');
            return false;
        }
        
        const tabButtons = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
        const tabContents = document.querySelectorAll('#pdf-builder-tab-content .tab-content');
        
        log('Onglets trouvés:', tabButtons.length);
        log('Contenus trouvés:', tabContents.length);
        
        // Attacher les événements
        tabButtons.forEach((btn, index) => {
            btn.removeEventListener('click', handleTabClick);
            btn.addEventListener('click', handleTabClick);
            log(`Event listener ajouté à l'onglet ${index + 1}:`, btn.getAttribute('data-tab'));
        });
        
        // Activer le premier onglet
        if (tabButtons[0]) {
            const firstTab = tabButtons[0].getAttribute('data-tab');
            log('Activation du premier onglet:', firstTab);
            setTimeout(() => switchTab(firstTab), 100);
        }
        
        window.PDF_BUILDER_TABS_FORCE_INITIALIZED = true;
        log('✅ INITIALISATION FORCE TERMINÉE');
        return true;
    }

    // Démarrage
    function start() {
        log('DÉMARRAGE FORCE');
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initialize);
        } else {
            initialize();
        }
        
        // Essayer aussi après un délai
        setTimeout(initialize, 500);
        
        // Surveillance continue
        setInterval(() => {
            if (!window.PDF_BUILDER_TABS_FORCE_INITIALIZED) {
                log('🔄 Nouvelle tentative d\'initialisation...');
                initialize();
            }
        }, 2000);
    }

    // Lancement immédiat
    start();

    // Export global pour diagnostic
    window.PDF_BUILDER_FORCE = {
        switchTab: switchTab,
        initialize: initialize,
        config: CONFIG
    };

})();