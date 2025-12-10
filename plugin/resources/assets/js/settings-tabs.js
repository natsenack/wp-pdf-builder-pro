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
        return (typeof window.location.search === 'string' && window.location.search.includes('debug=force')) ||
               (typeof window.pdfBuilderDebugSettings !== 'undefined' && window.pdfBuilderDebugSettings?.javascript);
    }

    function debugLog(...args) {
        if (isDebugEnabled()) {
            console.log(...args);
        }
    }

    function debugError(...args) {
        if (isDebugEnabled()) {
            console.error(...args);
        }
    }

    function debugWarn(...args) {
        if (isDebugEnabled()) {
            console.warn(...args);
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
        let tabRestored = false;
        try {
            const savedTab = localStorage.getItem('pdf_builder_active_tab');
            if (savedTab) {
                const savedTabElement = tabsContainer.querySelector('[data-tab="' + savedTab + '"]');
                const savedContent = document.getElementById('tab-content-' + savedTab);
                if (savedTabElement && savedContent) {
                    savedTabElement.click();
                    tabRestored = true;
                }
            }
        } catch (e) {
            // Ignore les erreurs localStorage
        }

        // Activer le premier onglet par défaut seulement si aucun onglet n'a été restauré
        if (!tabRestored) {
            const firstTab = tabsContainer.querySelector('.nav-tab');
            if (firstTab) {
                firstTab.click();
            }
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

                // Trouver le formulaire principal (plusieurs stratégies)
                let mainForm = document.querySelector('form[action="options.php"]');
                
                // Si pas trouvé, essayer de trouver un formulaire contenant les champs de template
                if (!mainForm) {
                    const forms = document.querySelectorAll('form');
                    for (const form of forms) {
                        if (form.querySelector('[name*="pdf_builder_settings"]')) {
                            mainForm = form;
                            debugLog('PDF Builder - Formulaire trouvé via champs pdf_builder_settings');
                            break;
                        }
                    }
                }
                
                // Dernière tentative: prendre le premier formulaire trouvé
                if (!mainForm) {
                    mainForm = document.querySelector('form');
                    debugLog('PDF Builder - Utilisation du premier formulaire trouvé');
                }
                if (mainForm) {
                    debugLog('PDF Builder - Formulaire trouvé, soumission en cours');
                    debugLog('PDF Builder - Formulaire action:', mainForm.action);
                    debugLog('PDF Builder - Formulaire method:', mainForm.method);
                    
                    // Log des données du formulaire avant soumission
                    const formData = new FormData(mainForm);
                    console.log('PDF Builder: Form data before submit:');
                    let templateFieldsFound = 0;
                    for (let [key, value] of formData.entries()) {
                        if (key.includes('template') || key.includes('pdf_builder_settings')) {
                            console.log('  ', key, '=', value);
                            templateFieldsFound++;
                        }
                    }
                    debugLog('PDF Builder - Champs template trouvés:', templateFieldsFound);
                    
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
                        const templateFields = form.querySelectorAll('[name*="template"], [name*="pdf_builder_settings"]');
                        console.log('    Template fields in form:', templateFields.length);
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
                const selects = document.querySelectorAll('#templates-status-form select[name^="pdf_builder_order_status_templates"]');
                selects.forEach(select => {
                    select.value = '';
                });
                alert('Les mappings de templates ont été réinitialisés. N\'oubliez pas de sauvegarder vos modifications.');
            }
        }
    };

})();