/**
 * Paramètres PDF Builder Pro - Navigation des onglets
 * Version: 2.0.0 - Nettoyée (sans logs de debug)
 * Date: 2025-12-03
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

        debugLog('PDF Builder - Initialisation du bouton flottant...');

        const saveBtn = document.getElementById('pdf-builder-save-floating-btn');
        const floatingContainer = document.getElementById('pdf-builder-save-floating');

        debugLog('   - Bouton #pdf-builder-save-floating-btn:', saveBtn ? 'trouvé' : 'manquant');
        debugLog('   - Conteneur #pdf-builder-save-floating:', floatingContainer ? 'trouvé' : 'manquant');

        if (saveBtn && floatingContainer) {
            // Afficher le bouton flottant
            saveBtn.style.display = 'block';
            console.log('PDF Builder - Bouton flottant affiché');

            saveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('PDF Builder - Clic sur le bouton flottant détecté');

                // Utiliser AJAX pour sauvegarder tous les paramètres
                const mainForm = document.getElementById('pdf-builder-settings-form') || document.querySelector('form');
                console.log('PDF Builder - Formulaire trouvé:', mainForm ? 'OUI' : 'NON');

                if (mainForm) {
                    // Collecter toutes les données du formulaire
                    const formData = new FormData(mainForm);
                    formData.append('action', 'pdf_builder_save_all_settings');
                    formData.append('current_tab', 'all');

                    // Ajouter le nonce
                    const nonceField = mainForm.querySelector('input[name="pdf_builder_settings_nonce"]');
                    console.log('PDF Builder - Nonce trouvé:', nonceField ? 'OUI' : 'NON');
                    if (nonceField) {
                        formData.append('nonce', nonceField.value);
                        console.log('PDF Builder - Valeur nonce:', nonceField.value);
                    }

                    console.log('PDF Builder - Envoi AJAX vers:', pdfBuilderAjax?.ajaxurl || '/wp-admin/admin-ajax.php');

                    // Désactiver le bouton pendant la sauvegarde
                    saveBtn.disabled = true;
                    saveBtn.textContent = '💾 Sauvegarde...';

                    // Faire l'appel AJAX
                    fetch(pdfBuilderAjax?.ajaxurl || '/wp-admin/admin-ajax.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        console.log('PDF Builder - Réponse HTTP status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('PDF Builder - Réponse AJAX complète:', data);

                        if (data.success) {
                            // Afficher un message de succès
                            alert('✅ ' + (data.message || 'Paramètres sauvegardés avec succès'));

                            // Déclencher un événement personnalisé pour que les onglets puissent réagir
                            document.dispatchEvent(new CustomEvent('pdfBuilderSettingsSaved', {
                                detail: { savedCount: data.saved_count, savedSettings: data.saved_settings }
                            }));
                        } else {
                            alert('❌ Erreur: ' + (data.data?.message || 'Erreur inconnue'));
                        }
                    })
                    .catch(error => {
                        console.error('PDF Builder - Erreur AJAX:', error);
                        alert('❌ Erreur de réseau lors de la sauvegarde: ' + error.message);
                    })
                    .finally(() => {
                        // Réactiver le bouton
                        saveBtn.disabled = false;
                        saveBtn.textContent = '💾 Enregistrer';
                    });
                } else {
                    console.error('PDF Builder - Formulaire principal non trouvé');
                    alert('❌ Erreur: Formulaire non trouvé');
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

    // Section Test de Licence - Onglet Développeur
    function initLicenseTestSection() {
        // Vérifier si on est sur la page de paramètres
        if (typeof window !== 'undefined' && window.location && window.location.href.indexOf('page=pdf-builder-settings') === -1) {
            debugLog('PDF Builder - Pas sur la page de paramètres, skip section licence');
            return;
        }

        debugLog('PDF Builder - Initialisation de la section Test de Licence...');
        debugLog('PDF Builder - pdfBuilderAjax disponible:', typeof pdfBuilderAjax !== 'undefined');
        if (typeof pdfBuilderAjax !== 'undefined') {
            debugLog('PDF Builder - ajaxurl:', pdfBuilderAjax.ajaxurl);
        }

        // Attendre que la section soit visible (peut être cachée initialement)
        const checkAndInit = function() {
            const section = document.getElementById('dev-license-section');
            if (!section) {
                debugLog('PDF Builder - Section licence pas encore trouvée, retry dans 500ms');
                setTimeout(checkAndInit, 500);
                return;
            }

            const isVisible = section.style.display !== 'none';
            if (!isVisible) {
                debugLog('PDF Builder - Section licence cachée, on attend qu\'elle soit visible');
                // Attendre que la section devienne visible
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                            const currentDisplay = section.style.display;
                            if (currentDisplay !== 'none') {
                                debugLog('PDF Builder - Section licence maintenant visible, initialisation...');
                                observer.disconnect();
                                initButtons();
                            }
                        }
                    });
                });
                observer.observe(section, { attributes: true, attributeFilter: ['style'] });
                return;
            }

            initButtons();
        };

        const initButtons = function() {
            debugLog('PDF Builder - Initialisation des boutons licence...');

            // Bouton basculer mode test
            const toggleBtn = document.getElementById('toggle_license_test_mode_btn');
            debugLog('PDF Builder - Bouton toggle trouvé:', !!toggleBtn);
        const toggleBtn = document.getElementById('toggle_license_test_mode_btn');
        if (toggleBtn) {
            debugLog('PDF Builder - Bouton toggle mode test trouvé, ajout event listener');
            toggleBtn.addEventListener('click', function() {
                debugLog('PDF Builder - Clic sur bouton toggle mode test');
                const nonce = document.getElementById('toggle_license_test_mode_nonce')?.value;
                if (!nonce) {
                    debugError('Nonce manquant pour toggle test mode');
                    return;
                }

                debugLog('PDF Builder - Nonce trouvé:', nonce.substring(0, 10) + '...');
                toggleBtn.disabled = true;
                toggleBtn.textContent = '⏳ Basculement...';

                const xhr = new XMLHttpRequest();
                xhr.open('POST', pdfBuilderAjax?.ajaxurl || window.ajaxurl || '/wp-admin/admin-ajax.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        debugLog('PDF Builder - Réponse AJAX reçue, status:', xhr.status);
                        debugLog('PDF Builder - Réponse:', xhr.responseText);
                        toggleBtn.disabled = false;
                        toggleBtn.textContent = '🎚️ Basculer Mode Test';

                        if (xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                debugLog('PDF Builder - Réponse parsée:', response);
                                if (response.success) {
                                    const statusSpan = document.getElementById('license_test_mode_status');
                                    if (statusSpan) {
                                        statusSpan.textContent = response.data.enabled ? '✅ MODE TEST ACTIF' : '❌ Mode test inactif';
                                        statusSpan.style.background = response.data.enabled ? '#d4edda' : '#f8d7da';
                                        statusSpan.style.color = response.data.enabled ? '#155724' : '#721c24';
                                    }
                                    
                                    // Mettre à jour le champ clé de test
                                    const keyInput = document.getElementById('license_test_key');
                                    const deleteBtn = document.getElementById('delete_license_key_btn');
                                    if (keyInput) {
                                        keyInput.value = response.data.test_key || '';
                                    }
                                    if (deleteBtn) {
                                        deleteBtn.style.display = response.data.test_key ? 'inline-block' : 'none';
                                    }
                                    
                                    debugLog('Mode test basculé:', response.data.enabled, 'clé:', response.data.test_key ? 'présente' : 'absente');
                                } else {
                                    debugError('Erreur toggle mode test:', response.data?.message || 'Erreur inconnue');
                                }
                            } catch (e) {
                                debugError('Erreur parsing réponse toggle:', e);
                            }
                        } else {
                            debugError('Erreur HTTP toggle mode test:', xhr.status);
                        }
                    }
                };

                xhr.send('action=pdf_builder_toggle_test_mode&nonce=' + encodeURIComponent(nonce));
            });
        } else {
            debugError('PDF Builder - Bouton toggle mode test NON trouvé');
        }

        // Bouton générer clé
        const generateBtn = document.getElementById('generate_license_key_btn');
        if (generateBtn) {
            generateBtn.addEventListener('click', function() {
                const nonce = document.getElementById('generate_license_key_nonce')?.value;
                if (!nonce) {
                    debugError('Nonce manquant pour générer clé');
                    return;
                }

                generateBtn.disabled = true;
                generateBtn.textContent = '⏳ Génération...';

                const xhr = new XMLHttpRequest();
                xhr.open('POST', pdfBuilderAjax?.ajaxurl || window.ajaxurl || '/wp-admin/admin-ajax.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        generateBtn.disabled = false;
                        generateBtn.textContent = '🔑 Générer';

                        if (xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    const keyInput = document.getElementById('license_test_key');
                                    const statusSpan = document.getElementById('license_key_status');
                                    const deleteBtn = document.getElementById('delete_license_key_btn');

                                    if (keyInput) keyInput.value = response.data.key || '';
                                    if (statusSpan) {
                                        statusSpan.textContent = '✅ Clé générée avec succès';
                                        statusSpan.style.color = '#28a745';
                                    }
                                    if (deleteBtn) deleteBtn.style.display = response.data.key ? 'inline-block' : 'none';

                                    debugLog('Clé générée:', response.data.key);
                                } else {
                                    const statusSpan = document.getElementById('license_key_status');
                                    if (statusSpan) {
                                        statusSpan.textContent = '❌ Erreur: ' + (response.data?.message || 'Erreur inconnue');
                                        statusSpan.style.color = '#dc3545';
                                    }
                                    debugError('Erreur génération clé:', response.data?.message || 'Erreur inconnue');
                                }
                            } catch (e) {
                                debugError('Erreur parsing réponse génération:', e);
                            }
                        } else {
                            debugError('Erreur HTTP génération clé:', xhr.status);
                        }
                    }
                };

                xhr.send('action=pdf_builder_generate_test_license_key&nonce=' + encodeURIComponent(nonce));
            });
        }

        // Bouton copier clé
        const copyBtn = document.getElementById('copy_license_key_btn');
        if (copyBtn) {
            copyBtn.addEventListener('click', function() {
                const keyInput = document.getElementById('license_test_key');
                const statusSpan = document.getElementById('license_key_status');

                if (keyInput && keyInput.value) {
                    navigator.clipboard.writeText(keyInput.value).then(function() {
                        if (statusSpan) {
                            statusSpan.textContent = '📋 Clé copiée dans le presse-papiers';
                            statusSpan.style.color = '#28a745';
                        }
                        debugLog('Clé copiée dans le presse-papiers');
                    }).catch(function(err) {
                        if (statusSpan) {
                            statusSpan.textContent = '❌ Erreur lors de la copie';
                            statusSpan.style.color = '#dc3545';
                        }
                        debugError('Erreur copie presse-papiers:', err);
                    });
                } else {
                    if (statusSpan) {
                        statusSpan.textContent = '❌ Aucune clé à copier';
                        statusSpan.style.color = '#dc3545';
                    }
                }
            });
        }

        // Bouton supprimer clé
        const deleteBtn = document.getElementById('delete_license_key_btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                if (!confirm('Êtes-vous sûr de vouloir supprimer la clé de test ?')) {
                    return;
                }

                const nonce = document.getElementById('delete_license_key_nonce')?.value;
                if (!nonce) {
                    debugError('Nonce manquant pour supprimer clé');
                    return;
                }

                deleteBtn.disabled = true;
                deleteBtn.textContent = '⏳ Suppression...';

                const xhr = new XMLHttpRequest();
                xhr.open('POST', pdfBuilderAjax?.ajaxurl || window.ajaxurl || '/wp-admin/admin-ajax.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        deleteBtn.disabled = false;
                        deleteBtn.textContent = '🗑️ Supprimer';

                        if (xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    const keyInput = document.getElementById('license_test_key');
                                    const statusSpan = document.getElementById('license_key_status');

                                    if (keyInput) keyInput.value = '';
                                    if (statusSpan) {
                                        statusSpan.textContent = '✅ Clé supprimée avec succès';
                                        statusSpan.style.color = '#28a745';
                                    }
                                    deleteBtn.style.display = 'none';

                                    debugLog('Clé supprimée');
                                } else {
                                    const statusSpan = document.getElementById('license_key_status');
                                    if (statusSpan) {
                                        statusSpan.textContent = '❌ Erreur: ' + (response.data?.message || 'Erreur inconnue');
                                        statusSpan.style.color = '#dc3545';
                                    }
                                    debugError('Erreur suppression clé:', response.data?.message || 'Erreur inconnue');
                                }
                            } catch (e) {
                                debugError('Erreur parsing réponse suppression:', e);
                            }
                        } else {
                            debugError('Erreur HTTP suppression clé:', xhr.status);
                        }
                    }
                };

                xhr.send('action=pdf_builder_delete_test_license_key&nonce=' + encodeURIComponent(nonce));
            });
        }

        // Bouton nettoyage complet
        const cleanupBtn = document.getElementById('cleanup_license_btn');
        if (cleanupBtn) {
            cleanupBtn.addEventListener('click', function() {
                if (!confirm('⚠️ ATTENTION: Cette action va supprimer TOUS les paramètres de licence et réinitialiser à l\'état libre. Cette action ne peut pas être annulée. Continuer ?')) {
                    return;
                }

                const nonce = document.getElementById('cleanup_license_nonce')?.value;
                if (!nonce) {
                    debugError('Nonce manquant pour nettoyage');
                    return;
                }

                cleanupBtn.disabled = true;
                cleanupBtn.textContent = '⏳ Nettoyage...';

                const xhr = new XMLHttpRequest();
                xhr.open('POST', pdfBuilderAjax?.ajaxurl || window.ajaxurl || '/wp-admin/admin-ajax.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        cleanupBtn.disabled = false;
                        cleanupBtn.textContent = '🧹 Nettoyer complètement la licence';

                        if (xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    const statusSpan = document.getElementById('cleanup_status');
                                    if (statusSpan) {
                                        statusSpan.textContent = '✅ Nettoyage complet effectué avec succès';
                                        statusSpan.style.color = '#28a745';
                                    }
                                    // Recharger la page pour refléter les changements
                                    setTimeout(function() {
                                        window.location.reload();
                                    }, 2000);
                                    debugLog('Nettoyage licence effectué');
                                } else {
                                    const statusSpan = document.getElementById('cleanup_status');
                                    if (statusSpan) {
                                        statusSpan.textContent = '❌ Erreur: ' + (response.data?.message || 'Erreur inconnue');
                                        statusSpan.style.color = '#dc3545';
                                    }
                                    debugError('Erreur nettoyage:', response.data?.message || 'Erreur inconnue');
                                }
                            } catch (e) {
                                debugError('Erreur parsing réponse nettoyage:', e);
                            }
                        } else {
                            debugError('Erreur HTTP nettoyage:', xhr.status);
                        }
                    }
                };

                xhr.send('action=pdf_builder_cleanup_license&nonce=' + encodeURIComponent(nonce));
            });
        }

        debugLog('PDF Builder - Section Test de Licence initialisée');
        };

        checkAndInit();
    }

    // Initialiser la section licence aussi
    document.addEventListener('DOMContentLoaded', initLicenseTestSection);

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
