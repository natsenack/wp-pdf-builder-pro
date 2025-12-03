/**
 * Paramètres PDF Builder Pro - Navigation des onglets (Version simplifiée)
 */

// DEBUG: Force console output to verify script is loaded
console.log('🔥 PDF BUILDER DEBUG: settings-tabs.js LOADED - ' + new Date().toLocaleTimeString() + ' - CACHE BUSTER: ' + Date.now());

(function() {
    'use strict';

    // Définition de PDF_BUILDER_CONFIG si elle n'existe pas
    if (typeof window.PDF_BUILDER_CONFIG === 'undefined') {
        window.PDF_BUILDER_CONFIG = {
            debug: false,
            ajaxurl: '',
            nonce: ''
        };
    }

    // Système de navigation des onglets simplifié
    function initTabs() {
        console.log('🔧 PDF BUILDER DEBUG: initTabs() called');

        const tabsContainer = document.getElementById('pdf-builder-tabs');
        const contentContainer = document.getElementById('pdf-builder-tab-content');

        console.log('📍 tabsContainer:', tabsContainer);
        console.log('📍 contentContainer:', contentContainer);

        if (!tabsContainer || !contentContainer) {
            console.log('❌ PDF Builder: Conteneurs non trouvés');
            return;
        }

        console.log('✅ PDF Builder: Conteneurs trouvés, ajout des gestionnaires');

        // Gestionnaire de clic pour les onglets
        tabsContainer.addEventListener('click', function(e) {
            console.log('🖱️ PDF BUILDER DEBUG: Click detected on tabs container');

            const tab = e.target.closest('.nav-tab');
            console.log('🎯 tab element:', tab);

            if (!tab) {
                console.log('❌ No .nav-tab element found');
                return;
            }

            e.preventDefault();

            const tabId = tab.getAttribute('data-tab');
            console.log('📋 tabId:', tabId);

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
            const content = document.getElementById('tab-content-' + tabId);
            if (content) {
                content.classList.add('active');
            }

            // Sauvegarder dans localStorage
            try {
                localStorage.setItem('pdf_builder_active_tab', tabId);
            } catch (e) {
                // Ignore les erreurs localStorage
            }
        });

        // Restaurer l'onglet sauvegardé
        try {
            const savedTab = localStorage.getItem('pdf_builder_active_tab');
            if (savedTab) {
                const savedTabElement = tabsContainer.querySelector('[data-tab="' + savedTab + '"]');
                const savedContent = document.getElementById('tab-content-' + savedTab);
                if (savedTabElement && savedContent) {
                    savedTabElement.click();
                    return;
                }
            }
        } catch (e) {
            // Ignore les erreurs localStorage
        }

        // Activer le premier onglet par défaut
        const firstTab = tabsContainer.querySelector('.nav-tab');
        if (firstTab) {
            firstTab.click();
        }
    }

    // Initialiser au chargement du DOM
    document.addEventListener('DOMContentLoaded', initTabs);

    // Exposer une API simple
    window.PDFBuilderTabsAPI = {
        switchToTab: function(tabId) {
            const tab = document.querySelector('[data-tab="' + tabId + '"]');
            if (tab) {
                tab.click();
            }
        },
        getActiveTab: function() {
            try {
                return localStorage.getItem('pdf_builder_active_tab');
            } catch (e) {
                return null;
            }
        },
        toggleAdvancedSection: function() {
            const section = document.getElementById('advanced-section');
            const toggle = document.getElementById('advanced-toggle');
            if (section && toggle) {
                const isVisible = section.style.display !== 'none';
                section.style.display = isVisible ? 'none' : 'block';
                toggle.textContent = isVisible ? '▼' : '▲';
            }
        },
        resetTemplatesStatus: function() {
            if (confirm('Êtes-vous sûr de vouloir réinitialiser tous les mappings de templates ? Cette action ne peut pas être annulée.')) {
                // Réinitialiser tous les selects
                const selects = document.querySelectorAll('#templates-status-form select[name^="order_status_templates"]');
                selects.forEach(select => {
                    select.value = '';
                });
                alert('Les mappings de templates ont été réinitialisés. N\'oubliez pas de sauvegarder vos modifications.');
            }
        }
    };

})();
