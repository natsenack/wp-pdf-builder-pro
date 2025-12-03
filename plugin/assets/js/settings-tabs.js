/**
 * Paramètres PDF Builder Pro - Navigation des onglets (Version simplifiée)
 */

// LOG IMMÉDIAT AU CHARGEMENT DU SCRIPT
console.log('🎯 PDF BUILDER TABS: Script chargé et exécuté !');
console.log('📍 PDF BUILDER TABS: URL actuelle:', window.location.href);
console.log('🔍 PDF BUILDER TABS: User Agent:', navigator.userAgent);

// Test de visibilité des logs
console.warn('🚨 PDF BUILDER TABS: LOG WARNING POUR TEST VISIBILITÉ');
console.error('💥 PDF BUILDER TABS: LOG ERROR POUR TEST VISIBILITÉ');

// Test de l'API console
if (typeof console === 'undefined') {
    alert('Console non disponible !');
} else {
    console.log('✅ Console disponible');
}

// LOG QUI S'AFFICHE QUAND MÊME SI LE SCRIPT PLANTE
try {
    console.log('🔄 PDF BUILDER TABS: Début de l\'exécution du script');
} catch (e) {
    console.error('❌ PDF BUILDER TABS: Erreur immédiate:', e);
}

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

    console.log('⚙️ PDF BUILDER TABS: Configuration définie', window.PDF_BUILDER_CONFIG);

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
            console.log('🔍 PDF Builder: Recherche élément avec ID:', tabId);
            console.log('📋 PDF Builder: Élément trouvé:', content);
            if (content) {
                content.classList.add('active');
                console.log('✅ PDF Builder: Contenu activé', tabId);
            } else {
                console.error('❌ PDF Builder: Contenu non trouvé pour', tabId);
                // Debug: lister tous les éléments avec classe tab-content
                const allTabs = document.querySelectorAll('.tab-content');
                console.log('📊 PDF Builder: Tous les onglets trouvés:', Array.from(allTabs).map(el => ({id: el.id, classes: el.className})));
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
        },
        saveAllSettings: function() {
            console.log('💾 PDF Builder: Sauvegarde globale déclenchée');

            const saveBtn = document.getElementById('pdf-builder-save-all');
            const statusIndicator = document.getElementById('save-status-indicator');
            const statusText = document.getElementById('save-status-text');

            if (!saveBtn || !statusIndicator || !statusText) {
                console.error('❌ PDF Builder: Éléments du bouton de sauvegarde non trouvés');
                return;
            }

            // Désactiver le bouton et afficher l'état de sauvegarde
            saveBtn.classList.add('saving');
            saveBtn.disabled = true;
            statusText.textContent = 'Sauvegarde en cours...';
            statusIndicator.classList.add('visible');

            // Collecter toutes les données des formulaires
            const formData = new FormData();
            formData.append('action', 'pdf_builder_save_all_settings');
            formData.append('nonce', window.pdfBuilderSettings?.nonce || '');

            // Collecter les données de tous les onglets
            const tabs = ['general', 'licence', 'systeme', 'acces', 'securite', 'pdf', 'contenu', 'templates', 'developpeur'];

            tabs.forEach(tabId => {
                // Chercher tous les inputs, selects, textareas dans l'onglet
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
                console.log('📨 PDF Builder: Réponse sauvegarde', data);

                if (data.success) {
                    statusText.textContent = 'Sauvegardé avec succès !';
                    statusIndicator.classList.add('success');
                    statusIndicator.classList.remove('error');

                    // Afficher un message de succès
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
                console.error('❌ PDF Builder: Erreur sauvegarde', error);
                statusText.textContent = 'Erreur lors de la sauvegarde';
                statusIndicator.classList.add('error');
                statusIndicator.classList.remove('success');

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
                    statusIndicator.classList.remove('visible', 'success', 'error');
                    statusText.textContent = 'Prêt à enregistrer';
                }, 3000);
            });
        }
    };

    // Initialiser le bouton de sauvegarde flottant
    function initSaveButton() {
        const saveBtn = document.getElementById('pdf-builder-save-all');
        if (saveBtn) {
            console.log('💾 PDF Builder: Bouton de sauvegarde flottant trouvé, configuration');
            saveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                PDFBuilderTabsAPI.saveAllSettings();
            });
        } else {
            console.warn('⚠️ PDF Builder: Bouton de sauvegarde flottant non trouvé');
        }
    }

    // Initialiser au chargement du DOM
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 PDF Builder: DOM chargé, initialisation des onglets');
        initTabs();
        initSaveButton();
    });

})();
