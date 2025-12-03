/**
 * Paramètres PDF Builder Pro - Navigation des onglets (plugin copy)
 */

(function() {
    'use strict';

    // LOG NON CONDITIONNEL POUR CONFIRMER LE CHARGEMENT
    console.log('PDF Builder: SCRIPT CHARGÉ - settings-tabs.js (plugin version)');

    const DEBUG = !!(typeof PDF_BUILDER_CONFIG !== 'undefined' && PDF_BUILDER_CONFIG.debug);
    if (DEBUG) {
        console.log('PDF Builder (plugin): settings-tabs.js DEBUG MODE ACTIVÉ');
    } else {
        console.log('PDF Builder (plugin): settings-tabs.js chargé (debug OFF)');
    }

    // Configuration globale
    const PDF_BUILDER_CONFIG = typeof window.PDF_BUILDER_CONFIG !== 'undefined' ? window.PDF_BUILDER_CONFIG : {};

    // Fonction de debug pour vérifier les éléments DOM
    function debugElements() {
        const container = document.getElementById('pdf-builder-settings-wrapper');
        const tabsContainer = document.getElementById('pdf-builder-tabs');
        const contentContainer = document.getElementById('pdf-builder-tab-content');
        
        console.log('PDF Builder - DIAGNOSTIC DES ÉLÉMENTS DOM (PLUGIN):');
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

    // --- copy the same main script to keep it independent of the root build ---
    function switchTab(tabId) {
        if (DEBUG) console.log('PDF Builder - SWITCH TAB: Début du changement vers "' + tabId + '"');
        
        const tabButtons = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
        const tabContents = document.querySelectorAll('#pdf-builder-tab-content .tab-content');

        const currentActiveBtn = document.querySelector('#pdf-builder-tabs .nav-tab.nav-tab-active');
        const currentActiveContent = document.querySelector('#pdf-builder-tab-content .tab-content.active');
        if (DEBUG) console.log('PDF Builder - Current Active:', {
            btn: currentActiveBtn ? currentActiveBtn.getAttribute('data-tab') : null,
            content: currentActiveContent ? currentActiveContent.id : null
        });
        
        // Désactiver
        tabButtons.forEach(function(btn) { btn.classList.remove('nav-tab-active'); });
        tabContents.forEach(function(content) { content.classList.remove('active'); });
        
        const targetBtn = document.querySelector('[data-tab="' + tabId + '"]');
        let targetContent = document.getElementById(tabId) || document.getElementById('tab-' + tabId);

        if (targetBtn) targetBtn.classList.add('nav-tab-active');
        if (targetContent) targetContent.classList.add('active');

        try { localStorage.setItem('pdf_builder_active_tab', tabId); } catch (e) { /* ignore */ }
    }

    function handleTabClick(event) {
        const el = event.currentTarget || event.target;
        const tabId = el.getAttribute('data-tab');
        if (!tabId) return;
        event.preventDefault();
        event.stopPropagation();
        switchTab(tabId);
    }

    function initializeTabs() {
        if (window.PDF_BUILDER_TABS_INITIALIZED) return true;
        window.PDF_BUILDER_TABS_INITIALIZED = true;

        const tabsContainer = document.getElementById('pdf-builder-tabs');
        const contentContainer = document.getElementById('pdf-builder-tab-content');
        if (!tabsContainer || !contentContainer) return false;

        const tabButtons = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
        const tabContents = document.querySelectorAll('#pdf-builder-tab-content .tab-content');
        if (tabButtons.length === 0 || tabContents.length === 0) return false;

        tabsContainer.addEventListener('click', function(e) {
            const anchor = e.target.closest('.nav-tab');
            if (anchor && tabsContainer.contains(anchor)) {
                handleTabClick.call(anchor, e);
            }
        });

        tabButtons.forEach(function(btn) {
            btn.removeEventListener('click', handleTabClick);
            btn.addEventListener('click', handleTabClick);
        });

        try {
            const savedTab = localStorage.getItem('pdf_builder_active_tab');
            if (savedTab && document.getElementById(savedTab)) {
                switchTab(savedTab);
            } else {
                const firstTab = tabButtons[0].getAttribute('data-tab');
                switchTab(firstTab);
            }
        } catch(e) {
            const firstTab = tabButtons[0].getAttribute('data-tab');
            switchTab(firstTab);
        }

        return true;
    }

    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            debugElements();
            initializeTabs();
        }, 100);
    });

})();