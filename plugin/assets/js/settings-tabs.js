/**
 * Paramètres PDF Builder Pro - Navigation des onglets (Version simplifiée)
 */

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
        console.log('🔧 PDF Builder: Initialisation du système d\'onglets');

        const tabsContainer = document.getElementById('pdf-builder-tabs');
        const contentContainer = document.getElementById('pdf-builder-tab-content');

        if (!tabsContainer || !contentContainer) {
            console.error('❌ PDF Builder: Conteneurs non trouvés', {
                tabsContainer: !!tabsContainer,
                contentContainer: !!contentContainer
            });
            return;
        }

        console.log('✅ PDF Builder: Conteneurs trouvés, configuration des gestionnaires d\'événements');

        // Gestionnaire de clic pour les onglets
        tabsContainer.addEventListener('click', function(e) {
            console.log('🖱️ PDF Builder: Clic détecté sur les onglets');

            const tab = e.target.closest('.nav-tab');
            if (!tab) {
                console.log('⚠️ PDF Builder: Clic en dehors d\'un onglet');
                return;
            }

            e.preventDefault();

            const tabId = tab.getAttribute('data-tab');
            console.log('📋 PDF Builder: Onglet cliqué', { tabId, tabElement: tab });

            if (!tabId) {
                console.error('❌ PDF Builder: Aucun data-tab trouvé sur l\'onglet');
                return;
            }

            console.log('🔄 PDF Builder: Changement d\'onglet vers', tabId);

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
            console.log('✅ PDF Builder: Onglet activé visuellement', tabId);

            // Activer le contenu correspondant
            const content = document.getElementById(tabId);
            if (content) {
                content.classList.add('active');
                console.log('✅ PDF Builder: Contenu activé', tabId);
            } else {
                console.error('❌ PDF Builder: Contenu non trouvé pour', tabId);
            }

            // Sauvegarder dans localStorage
            try {
                localStorage.setItem('pdf_builder_active_tab', tabId);
                console.log('💾 PDF Builder: Onglet sauvegardé dans localStorage', tabId);
            } catch (e) {
                console.error('❌ PDF Builder: Erreur localStorage', e);
            }
        });

        // Restaurer l'onglet sauvegardé
        try {
            const savedTab = localStorage.getItem('pdf_builder_active_tab');
            console.log('🔍 PDF Builder: Vérification localStorage', { savedTab });

            if (savedTab) {
                const savedTabElement = tabsContainer.querySelector('[data-tab="' + savedTab + '"]');
                const savedContent = document.getElementById(savedTab);
                console.log('📂 PDF Builder: Éléments trouvés pour restauration', {
                    savedTabElement: !!savedTabElement,
                    savedContent: !!savedContent,
                    tabId: savedTab
                });

                if (savedTabElement && savedContent) {
                    console.log('🔄 PDF Builder: Restauration de l\'onglet sauvegardé', savedTab);
                    savedTabElement.click();
                    return;
                } else {
                    console.warn('⚠️ PDF Builder: Impossible de restaurer l\'onglet sauvegardé', savedTab);
                }
            } else {
                console.log('ℹ️ PDF Builder: Aucun onglet sauvegardé trouvé');
            }
        } catch (e) {
            console.error('❌ PDF Builder: Erreur lors de la restauration localStorage', e);
        }

        // Activer le premier onglet par défaut
        const firstTab = tabsContainer.querySelector('.nav-tab');
        if (firstTab) {
            console.log('🏠 PDF Builder: Activation du premier onglet par défaut');
            firstTab.click();
        } else {
            console.error('❌ PDF Builder: Aucun onglet trouvé pour l\'activation par défaut');
        }
    }

    // Initialiser au chargement du DOM
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 PDF Builder: DOM chargé, initialisation des onglets');
        initTabs();
    });

    // Log de confirmation du chargement du script
    console.log('📜 PDF Builder: Script settings-tabs.js chargé');

    // Exposer une API simple
    window.PDFBuilderTabsAPI = {
        switchToTab: function(tabId) {
            console.log('🔧 PDF Builder: API switchToTab appelée', tabId);
            const tab = document.querySelector('[data-tab="' + tabId + '"]');
            if (tab) {
                console.log('✅ PDF Builder: Onglet trouvé via API, déclenchement clic');
                tab.click();
            } else {
                console.error('❌ PDF Builder: Onglet non trouvé via API', tabId);
            }
        },
        getActiveTab: function() {
            try {
                const activeTab = localStorage.getItem('pdf_builder_active_tab');
                console.log('📖 PDF Builder: API getActiveTab', activeTab);
                return activeTab;
            } catch (e) {
                console.error('❌ PDF Builder: Erreur API getActiveTab', e);
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
