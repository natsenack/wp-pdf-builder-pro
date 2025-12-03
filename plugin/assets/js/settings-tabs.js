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
        console.log('🔍 PDF Builder: Recherche du bouton de sauvegarde flottant...');

        // Log détaillé du DOM au moment de la recherche
        console.log('📊 PDF Builder: Analyse détaillée du DOM:');
        console.log('   - Body existe:', !!document.body);
        console.log('   - Body children:', document.body ? document.body.children.length : 'N/A');
        console.log('   - Total éléments avec ID:', document.querySelectorAll('[id]').length);

        const saveBtn = document.getElementById('pdf-builder-save-all');
        const floatingContainer = document.getElementById('pdf-builder-save-floating');

        console.log('🎯 PDF Builder: Recherche spécifique des éléments:');
        console.log('   - Recherche ID: pdf-builder-save-all');
        console.log('   - Résultat:', saveBtn);
        console.log('   - Recherche ID: pdf-builder-save-floating');
        console.log('   - Résultat:', floatingContainer);

        console.log('📋 PDF Builder: État du DOM:', {
            saveBtn: !!saveBtn,
            floatingContainer: !!floatingContainer,
            body: !!document.body,
            allButtons: document.querySelectorAll('button').length,
            allDivs: document.querySelectorAll('div').length
        });

        // Chercher tous les éléments qui contiennent "save" dans leur ID
        const allSaveElements = Array.from(document.querySelectorAll('[id*="save"]'));
        console.log('💾 PDF Builder: Éléments avec "save" dans l\'ID:', allSaveElements.map(el => ({id: el.id, tag: el.tagName, text: el.textContent?.substring(0, 50)})));

        // Chercher tous les éléments qui contiennent "pdf-builder" dans leur ID
        const allPdfElements = Array.from(document.querySelectorAll('[id*="pdf-builder"]'));
        console.log('🏗️ PDF Builder: Éléments avec "pdf-builder" dans l\'ID:', allPdfElements.map(el => ({id: el.id, tag: el.tagName})));

        // Chercher tous les éléments avec position fixed
        const fixedElements = Array.from(document.querySelectorAll('[style*="position: fixed"], [style*="position:fixed"]'));
        console.log('📌 PDF Builder: Éléments en position fixed:', fixedElements.map(el => ({id: el.id, tag: el.tagName, style: el.getAttribute('style')})));

        if (saveBtn) {
            console.log('💾 PDF Builder: Bouton de sauvegarde flottant trouvé, configuration');
            console.log('   - Bouton:', saveBtn);
            console.log('   - Texte du bouton:', saveBtn.textContent);
            console.log('   - Style du bouton:', saveBtn.getAttribute('style'));
            console.log('   - Parent:', saveBtn.parentElement);

            saveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('🖱️ PDF Builder: Clic sur le bouton de sauvegarde');
                PDFBuilderTabsAPI.saveAllSettings();
            });

            console.log('✅ PDF Builder: Bouton configuré');
        } else {
            console.warn('⚠️ PDF Builder: Bouton de sauvegarde flottant non trouvé - création du bouton réel');

            // Essayer de trouver tous les éléments avec des IDs similaires
            const allIds = Array.from(document.querySelectorAll('[id]')).map(el => el.id);
            console.log('📝 PDF Builder: IDs trouvés dans le document:', allIds.filter(id => id.includes('save') || id.includes('pdf')));

            // Créer le vrai bouton de sauvegarde flottant
            console.log('🔧 PDF Builder: Création du bouton de sauvegarde flottant...');
            const floatingContainer = document.createElement('div');
            floatingContainer.id = 'pdf-builder-save-floating';
            floatingContainer.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 9999;
                display: block;
            `;

            const saveBtn = document.createElement('button');
            saveBtn.id = 'pdf-builder-save-all';
            saveBtn.className = 'button button-primary';
            saveBtn.textContent = 'Enregistrer les paramètres';
            saveBtn.style.cssText = `
                padding: 12px 20px;
                font-size: 16px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                border-radius: 8px;
                transition: all 0.3s ease;
            `;

            saveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('🖱️ Bouton de sauvegarde flottant cliqué');
                PDFBuilderTabsAPI.saveAllSettings();
            });

            floatingContainer.appendChild(saveBtn);

            if (document.body) {
                document.body.appendChild(floatingContainer);
                console.log('✅ PDF Builder: Bouton de sauvegarde flottant créé et ajouté au DOM');
            } else {
                console.error('❌ PDF Builder: Impossible de créer le bouton - body inexistant');
            }

            // Réessayer dans 1 seconde
            setTimeout(function() {
                console.log('🔄 PDF Builder: Nouvelle tentative de recherche du bouton...');
                const retryBtn = document.getElementById('pdf-builder-save-all');
                if (retryBtn) {
                    console.log('✅ PDF Builder: Bouton trouvé à la deuxième tentative');
                    retryBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        PDFBuilderTabsAPI.saveAllSettings();
                    });
                } else {
                    console.error('❌ PDF Builder: Bouton toujours introuvable après retry');
                }
            }, 1000);
        }
    }

    // Initialiser au chargement du DOM
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 PDF Builder: DOM chargé, initialisation des onglets');
        initTabs();
        // Délai pour s'assurer que le bouton flottant est rendu
        setTimeout(initSaveButton, 100);
    });

    // Aussi essayer au chargement complet de la fenêtre
    window.addEventListener('load', function() {
        console.log('🏁 PDF Builder: Fenêtre chargée, vérification bouton sauvegarde');
        setTimeout(initSaveButton, 100);
    });

})();
