/**
 * Paramètres PDF Builder Pro - Navigation des onglets
 * Version: 2.0.0 - Nettoyée (sans logs de debug)
 * Date: 2025-12-03
 */

console.log('PDF Builder - settings-tabs.js LOADED AND EXECUTING');

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

    // Fonctions de debug conditionnel
    function isDebugEnabled() {
        return window.location.search.includes('debug=force') ||
               (typeof window.pdfBuilderDebugSettings !== 'undefined' && window.pdfBuilderDebugSettings?.javascript);
    }

    function debugLog(...args) {
        if (isDebugEnabled()) {
            debugLog(...args);
        }
    }

    function debugError(...args) {
        if (isDebugEnabled()) {
            debugError(...args);
        }
    }

    function debugWarn(...args) {
        if (isDebugEnabled()) {
            debugWarn(...args);
        }
    }

    // Système de navigation des onglets
    function initTabs() {
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

    // Bouton de sauvegarde flottant
    let saveButtonInitialized = false;

    function initSaveButton() {
        // Vérifier si on est sur la page de paramètres
        if (typeof window !== 'undefined' && window.location && window.location.href.indexOf('page=pdf-builder-settings') === -1) {
            debugLog('PDF Builder - Bouton flottant: Pas sur la page de paramètres, skip');
            return;
        }

        if (saveButtonInitialized) {
            debugLog('PDF Builder - Bouton flottant: Déjà initialisé');
            return;
        }

        console.log('PDF Builder - Initialisation du bouton flottant...');

        const saveBtn = document.getElementById('pdf-builder-save-floating-btn');
        const floatingContainer = document.getElementById('pdf-builder-save-floating');

        debugLog('   - Bouton #pdf-builder-save-floating-btn:', saveBtn ? 'trouvé' : 'manquant');
        debugLog('   - Conteneur #pdf-builder-save-floating:', floatingContainer ? 'trouvé' : 'manquant');

        if (saveBtn && floatingContainer) {
            saveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                debugLog('PDF Builder - Clic sur le bouton flottant');

                // Trouver le formulaire principal avec action="options.php"
                const mainForm = document.querySelector('form[action="options.php"]');
                if (mainForm) {
                    debugLog('PDF Builder - Formulaire trouvé, soumission en cours');
                    
                    // Log des données du formulaire avant soumission
                    const formData = new FormData(mainForm);
                    console.log('PDF Builder: Form data before submit:');
                    for (let [key, value] of formData.entries()) {
                        if (key.includes('template')) {
                            console.log('  ', key, '=', value);
                        }
                    }
                    
                    // Changer le texte du bouton pendant la sauvegarde
                    const originalText = saveBtn.textContent;
                    saveBtn.textContent = 'Sauvegarde...';
                    saveBtn.disabled = true;
                    
                    // Soumettre le formulaire directement
                    mainForm.submit();
                    
                    // Remettre le texte original après un délai
                    setTimeout(function() {
                        saveBtn.textContent = originalText;
                        saveBtn.disabled = false;
                    }, 5000);
                } else {
                    debugError('PDF Builder - Formulaire principal non trouvé');
                    // Log all forms on the page
                    const allForms = document.querySelectorAll('form');
                    console.log('PDF Builder: All forms on page:', allForms.length);
                    allForms.forEach((form, index) => {
                        console.log('  Form', index, ': action=', form.action, 'method=', form.method);
                    });
                }
            });

            saveButtonInitialized = true;
            debugLog('PDF Builder - Bouton flottant initialisé avec succès');
        } else {
            debugLog('PDF Builder - Éléments du bouton flottant manquants, retry dans 1s...');
            setTimeout(initSaveButton, 1000);
        }
    }

    // Initialiser le bouton flottant aussi
    document.addEventListener('DOMContentLoaded', initSaveButton);

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
                const activeTab = document.querySelector('.nav-tab-active');
                return activeTab ? activeTab.getAttribute('data-tab') : null;
            } catch (e) {
                debugError('Erreur getActiveTab:', e);
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
            if (!confirm('⚠️ ATTENTION: Cette action va réinitialiser TOUS les mappings de templates.\n\nCette action est IRRÉVERSIBLE.\n\nÊtes-vous sûr de vouloir continuer ?')) {
                return;
            }

            // Réinitialiser tous les selects de templates
            const selects = document.querySelectorAll('select[name^="pdf_builder_settings[templates_mapping]"]');
            selects.forEach(function(select) {
                select.value = '';
            });
            alert('Les mappings de templates ont été réinitialisés. N\'oubliez pas de sauvegarder vos modifications.');
        }
    };

})();