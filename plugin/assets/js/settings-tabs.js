/**
 * Paramètres PDF Builder Pro - Navigation des onglets
 * Version: 2.1.0 - Logs conditionnés par paramètres développeur
 * Date: 2025-12-04
 */

// Fonction utilitaire pour les logs conditionnés
function pdfBuilderLog() {
    if (typeof window.PDF_BUILDER_CONFIG !== 'undefined' && window.PDF_BUILDER_CONFIG.debug) {
        console.log.apply(console, ['🎯 PDF BUILDER TABS:'].concat(Array.prototype.slice.call(arguments)));
    }
}

function pdfBuilderWarn() {
    if (typeof window.PDF_BUILDER_CONFIG !== 'undefined' && window.PDF_BUILDER_CONFIG.debug) {
        console.warn.apply(console, ['🚨 PDF BUILDER TABS:'].concat(Array.prototype.slice.call(arguments)));
    }
}

function pdfBuilderError() {
    if (typeof window.PDF_BUILDER_CONFIG !== 'undefined' && window.PDF_BUILDER_CONFIG.debug) {
        console.error.apply(console, ['💥 PDF BUILDER TABS:'].concat(Array.prototype.slice.call(arguments)));
    }
}

// LOG IMMÉDIAT AU CHARGEMENT DU SCRIPT (seulement en mode debug)
pdfBuilderLog('Script chargé et exécuté !');
pdfBuilderLog('URL actuelle:', window.location.href);
pdfBuilderLog('User Agent:', navigator.userAgent);

// Test de visibilité des logs (seulement en mode debug)
pdfBuilderWarn('LOG WARNING POUR TEST VISIBILITÉ');
pdfBuilderError('LOG ERROR POUR TEST VISIBILITÉ');

// Test de l'API console (seulement en mode debug)
if (typeof console === 'undefined') {
    alert('Console non disponible !');
} else {
    pdfBuilderLog('Console disponible');
}

// LOG QUI S'AFFICHE QUAND MÊME SI LE SCRIPT PLANTE (seulement en mode debug)
try {
    pdfBuilderLog('Début de l\'exécution du script');
} catch (e) {
    pdfBuilderError('Erreur immédiate:', e);
}

(function() {
    'use strict';

    // Définition de PDF_BUILDER_CONFIG si elle n'existe pas (fallback)
    if (typeof window.PDF_BUILDER_CONFIG === 'undefined') {
        window.PDF_BUILDER_CONFIG = {
            debug: false,
            ajaxurl: '',
            nonce: ''
        };
    }

    pdfBuilderLog('Configuration définie', window.PDF_BUILDER_CONFIG);

    // Système de navigation des onglets simplifié
    function initTabs() {
        const tabsContainer = document.getElementById('pdf-builder-tabs');
        const contentContainer = document.getElementById('pdf-builder-tab-content');

        if (!tabsContainer || !contentContainer) {
            pdfBuilderError('Conteneurs non trouvés');
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
            const content = document.getElementById(tabId);
            if (content) {
                content.classList.add('active');
            }

            // Sauvegarder dans localStorage
            try {
                localStorage.setItem('pdf_builder_active_tab', tabId);
            } catch (e) {
                pdfBuilderError('Erreur localStorage', e);
            }
        });

        // Restaurer l'onglet sauvegardé
        try {
            const savedTab = localStorage.getItem('pdf_builder_active_tab');
            if (savedTab) {
                const savedTabElement = tabsContainer.querySelector('[data-tab="' + savedTab + '"]');
                const savedContent = document.getElementById(savedTab);
                if (savedTabElement && savedContent) {
                    savedTabElement.click();
                    return;
                }
            }
        } catch (e) {
            pdfBuilderError('Erreur lors de la restauration localStorage', e);
        }

        // Activer le premier onglet par défaut
        const firstTab = tabsContainer.querySelector('.nav-tab');
        if (firstTab) {
            firstTab.click();
        }
    }

    // Initialiser au chargement du DOM
    document.addEventListener('DOMContentLoaded', function() {
        pdfBuilderLog('DOM chargé, initialisation des onglets');
        initTabs();
    });

    // Log de confirmation du chargement du script
    pdfBuilderLog('Script settings-tabs.js chargé');

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
                const selects = document.querySelectorAll('#templates-status-form select[name^="order_status_templates"]');
                selects.forEach(select => {
                    select.value = '';
                });
                alert('Les mappings de templates ont été réinitialisés. N\'oubliez pas de sauvegarder vos modifications.');
            }
        },
        saveAllSettings: function() {
            const saveBtn = document.getElementById('pdf-builder-save-all');
            const statusIndicator = document.getElementById('save-status-indicator');
            const statusText = document.getElementById('save-status-text');

            if (!saveBtn) return;

            // Désactiver le bouton et afficher l'état de sauvegarde
            saveBtn.classList.add('saving');
            saveBtn.disabled = true;

            if (statusText) statusText.textContent = 'Sauvegarde en cours...';
            if (statusIndicator) statusIndicator.classList.add('visible');

            // Collecter toutes les données des formulaires
            const formData = new FormData();
            formData.append('action', 'pdf_builder_save_all_settings');
            formData.append('nonce', window.pdfBuilderSettings?.nonce || '');

            // Collecter les données de tous les onglets
            const tabs = ['general', 'licence', 'systeme', 'acces', 'securite', 'pdf', 'contenu', 'templates', 'developpeur'];

            tabs.forEach(tabId => {
                const tabElement = document.getElementById(tabId);
                if (tabElement) {
                    const inputs = tabElement.querySelectorAll('input, select, textarea');
                    inputs.forEach(input => {
                        if (input.name && input.type !== 'submit' && input.type !== 'button') {
                            if (input.type === 'checkbox') {
                                formData.append(input.name, input.checked ? '1' : '0');
                            } else if (input.type === 'radio') {
                                if (input.checked) {
                                    formData.append(input.name, input.value);
                                }
                            } else {
                                formData.append(input.name, input.value);
                            }
                        }
                    });
                }
            });

            // Envoyer la requête AJAX
            fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sauvegardé !',
                            text: 'Tous les paramètres ont été sauvegardés avec succès.',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    } else {
                        alert('Tous les paramètres ont été sauvegardés avec succès !');
                    }
                } else {
                    throw new Error(data.data || 'Erreur inconnue');
                }
            })
            .catch(error => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: 'Une erreur s\'est produite lors de la sauvegarde : ' + error.message,
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert('Erreur lors de la sauvegarde : ' + error.message);
                }
            })
            .finally(() => {
                // Réactiver le bouton après un délai
                setTimeout(() => {
                    saveBtn.classList.remove('saving');
                    saveBtn.disabled = false;
                    if (statusIndicator) statusIndicator.classList.remove('visible', 'success', 'error');
                    if (statusText) statusText.textContent = 'Prêt à enregistrer';
                }, 3000);
            });
        }
    };

    // Indicateur pour éviter les initialisations multiples
    let saveButtonInitialized = false;

    // Initialiser le bouton de sauvegarde flottant (utilise seulement le bouton HTML existant)
    function initSaveButton() {
        // Éviter les initialisations multiples
        if (saveButtonInitialized) {
            pdfBuilderLog('Bouton déjà initialisé, ignoré');
            return;
        }

        pdfBuilderLog('Recherche du bouton de sauvegarde flottant HTML...');

        const saveBtn = document.getElementById('pdf-builder-save-all');
        const floatingContainer = document.getElementById('pdf-builder-save-floating');

        if (saveBtn && floatingContainer) {
            pdfBuilderLog('Bouton de sauvegarde flottant HTML trouvé, configuration');
            saveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                PDFBuilderTabsAPI.saveAllSettings();
            });
            pdfBuilderLog('Bouton HTML configuré avec succès');
        } else {
            pdfBuilderError('Bouton de sauvegarde flottant HTML non trouvé');
            pdfBuilderError('   - Conteneur #pdf-builder-save-floating:', floatingContainer ? 'trouvé' : 'manquant');
            pdfBuilderError('   - Bouton #pdf-builder-save-all:', saveBtn ? 'trouvé' : 'manquant');
        }

        // Marquer comme initialisé
        saveButtonInitialized = true;
        pdfBuilderLog('Initialisation du bouton HTML terminée');
    }

    // Initialiser au chargement du DOM
    document.addEventListener('DOMContentLoaded', function() {
        initTabs();
        // Délai plus long pour s'assurer que le HTML est complètement chargé
        setTimeout(initSaveButton, 500);
    });

})();
