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
        console.log('PDF Builder - SWITCH TAB: Début du changement vers "' + tabId + '"');
        
        const tabButtons = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
        const tabContents = document.querySelectorAll('#pdf-builder-tab-content .tab-content');

        const currentActiveBtn = document.querySelector('#pdf-builder-tabs .nav-tab.nav-tab-active');
        const currentActiveContent = document.querySelector('#pdf-builder-tab-content .tab-content.active');
        console.log('PDF Builder - État actuel - Bouton actif:', currentActiveBtn ? currentActiveBtn.getAttribute('data-tab') : 'aucun');
        console.log('PDF Builder - État actuel - Contenu actif:', currentActiveContent ? currentActiveContent.id : 'aucun');
        
        console.log('PDF Builder - Désactivation de tous les onglets...');
        tabButtons.forEach(function(btn, index) {
            const wasActive = btn.classList.contains('nav-tab-active');
            btn.classList.remove('nav-tab-active');
            console.log('  Bouton ' + (index + 1) + ' ("' + btn.getAttribute('data-tab') + '"): ' + (wasActive ? 'était actif' : 'inactif') + ' -> désactivé');
        });
        
        tabContents.forEach(function(content, index) {
            const wasActive = content.classList.contains('active');
            content.classList.remove('active');
            console.log('  Contenu ' + (index + 1) + ' ("' + content.id + '"): ' + (wasActive ? 'était actif' : 'inactif') + ' -> désactivé');
        });
        
        console.log('PDF Builder - Activation de l\'onglet cible "' + tabId + '"...');
        const targetBtn = document.querySelector('[data-tab="' + tabId + '"]');
        let targetContent = document.getElementById(tabId) || document.getElementById('tab-' + tabId);
        
        if (targetBtn) {
            targetBtn.classList.add('nav-tab-active');
            console.log('  ✅ Bouton trouvé et activé: "' + targetBtn.textContent.trim() + '"');
        } else {
            console.error('  ❌ ERREUR: Bouton avec data-tab="' + tabId + '" non trouvé!');
            console.log('  Boutons disponibles:', Array.from(tabButtons).map(btn => btn.getAttribute('data-tab')));
        }
        
        if (targetContent) {
            targetContent.classList.add('active');
            console.log('  ✅ Contenu trouvé et activé: "#' + targetContent.id + '"');
        } else {
            console.error('  ❌ ERREUR: Contenu avec id="' + tabId + '" ou "tab-' + tabId + '" non trouvé!');
            console.log('  Contenus disponibles:', Array.from(tabContents).map(content => content.id));
        }
        
        try {
            localStorage.setItem('pdf_builder_active_tab', tabId);
            console.log('PDF Builder - Onglet "' + tabId + '" sauvegardé en localStorage');
        } catch(e) {
            console.warn('PDF Builder - Impossible de sauvegarder en localStorage:', e.message);
        }
        
        console.log('PDF Builder - SWITCH TAB: Terminé pour "' + tabId + '"');
        
        // Vérification finale
        const newActiveBtn = document.querySelector('#pdf-builder-tabs .nav-tab.nav-tab-active');
        const newActiveContent = document.querySelector('#pdf-builder-tab-content .tab-content.active');
        console.log('PDF Builder - État final - Bouton actif:', newActiveBtn ? newActiveBtn.getAttribute('data-tab') : 'aucun');
        console.log('PDF Builder - État final - Contenu actif:', newActiveContent ? newActiveContent.id : 'aucun');
    }

    function handleTabClick(event) {
        console.log('PDF Builder - CLIC DÉTECTÉ SUR ONGLET:', event.target);
        console.log('PDF Builder - Event type:', event.type, 'isTrusted:', event.isTrusted);
        
        // Use currentTarget to always reference the element the listener was attached to
        const el = event.currentTarget || event.target;
        console.log('PDF Builder - Élément cliqué (currentTarget):', el);
        console.log('PDF Builder - data-tab attribute:', el.getAttribute('data-tab'));
        console.log('PDF Builder - Texte du bouton:', el.textContent ? el.textContent.trim() : 'N/A');
        
        // Vérifier les styles CSS qui pourraient bloquer les clics
        const computedStyle = window.getComputedStyle(el);
        console.log('PDF Builder - Styles CSS de l\'élément cliqué:', {
            pointerEvents: computedStyle.pointerEvents,
            cursor: computedStyle.cursor,
            display: computedStyle.display,
            visibility: computedStyle.visibility,
            zIndex: computedStyle.zIndex,
            position: computedStyle.position
        });
        
        // Vérifier si l'élément est dans le viewport et cliquable
        const rect = el.getBoundingClientRect();
        console.log('PDF Builder - Position et taille de l\'élément:', {
            top: rect.top,
            left: rect.left,
            width: rect.width,
            height: rect.height,
            visible: rect.width > 0 && rect.height > 0 && rect.top >= 0 && rect.left >= 0
        });
        
        const tabId = el.getAttribute('data-tab');
        if (!tabId) {
            console.error('PDF Builder - ERREUR: Aucun attribut data-tab trouvé sur l\'élément cliqué!');
            console.log('PDF Builder - Élément cliqué:', el);
            console.log('PDF Builder - Attributs disponibles:', Array.from(el.attributes).map(attr => attr.name + '=' + attr.value));
            return;
        }
        
        console.log('PDF Builder - ONGLET CLIQUE: "' + tabId + '"');
        
        // Prevent the default navigation and propagation after we know we have a data-tab
        event.preventDefault();
        event.stopPropagation();
        console.log('PDF Builder - Event preventDefault/stopPropagation appliqués');
        
        console.log('PDF Builder - LANCEMENT du switch vers "' + tabId + '"');
        switchTab(tabId);
        
        console.log('PDF Builder - CLIC TRAITÉ POUR ONGLET "' + tabId + '"');
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