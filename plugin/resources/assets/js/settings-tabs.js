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
            const content = document.getElementById(tabId);
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
                const savedContent = document.getElementById(savedTab);
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
            console.log('PDF Builder - Bouton flottant: Pas sur la page de paramètres, skip');
            return;
        }

        if (saveButtonInitialized) {
            console.log('PDF Builder - Bouton flottant: Déjà initialisé');
            return;
        }

        console.log('PDF Builder - Initialisation du bouton flottant...');

        const saveBtn = document.getElementById('pdf-builder-save-floating-btn');
        const floatingContainer = document.getElementById('pdf-builder-save-floating');

        console.log('   - Bouton #pdf-builder-save-floating-btn:', saveBtn ? 'trouvé' : 'manquant');
        console.log('   - Conteneur #pdf-builder-save-floating:', floatingContainer ? 'trouvé' : 'manquant');

        if (saveBtn && floatingContainer) {
            saveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('PDF Builder - Clic sur le bouton flottant');

                // Collecter toutes les données de tous les formulaires de tous les onglets
                const allFormData = collectAllFormData();

                if (Object.keys(allFormData).length > 0) {
                    console.log('PDF Builder - Données collectées pour sauvegarde:', allFormData);
                    // Sauvegarder via AJAX
                    saveAllSettings(allFormData);
                } else {
                    console.error('PDF Builder - Aucune donnée de formulaire trouvée à sauvegarder');
                }
            });

            saveButtonInitialized = true;
            console.log('PDF Builder - Bouton flottant initialisé avec succès');
        } else {
            console.log('PDF Builder - Éléments du bouton flottant manquants, retry dans 1s...');
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
                const selects = document.querySelectorAll('#templates-status-form select[name^="order_status_templates"]');
                selects.forEach(select => {
                    select.value = '';
                });
                alert('Les mappings de templates ont été réinitialisés. N\'oubliez pas de sauvegarder vos modifications.');
            }
        }
    };

    /**
     * Collecte toutes les données de tous les formulaires de tous les onglets
     */
    function collectAllFormData() {
        console.log('PDF Builder - Collecte des données de tous les formulaires...');

        const allData = {};

        // Liste des IDs de formulaires à collecter
        const formIds = [
            'developpeur-form',
            'canvas-form',
            'securite-settings-form',
            'pdf-settings-form',
            'templates-status-form',
            'general-form',
            'cache-status-form',
            'canvas-dimensions-form',
            'zoom-form',
            'canvas-apparence-form',
            'canvas-grille-form',
            'canvas-interactions-form',
            'canvas-export-form',
            'canvas-performance-form',
            'canvas-debug-form'
        ];

        // Collecter les données de chaque formulaire
        formIds.forEach(formId => {
            const form = document.getElementById(formId);
            if (form) {
                console.log(`PDF Builder - Collecte du formulaire: ${formId}`);
                const formData = new FormData(form);
                const formObject = {};

                // Convertir FormData en objet
                for (let [key, value] of formData.entries()) {
                    // Gérer les cases à cocher multiples
                    if (formObject[key]) {
                        if (Array.isArray(formObject[key])) {
                            formObject[key].push(value);
                        } else {
                            formObject[key] = [formObject[key], value];
                        }
                    } else {
                        formObject[key] = value;
                    }
                }

                // Ajouter les données du formulaire à allData
                allData[formId] = formObject;
            }
        });

        // Collecter aussi tous les champs input, select, textarea qui ne sont pas dans des formulaires
        const allInputs = document.querySelectorAll('input[name], select[name], textarea[name]');
        allInputs.forEach(input => {
            if (input.name && input.name !== '') {
                const inputForm = input.closest('form');
                // Ne collecter que si ce n'est pas déjà dans un formulaire traité
                if (!inputForm || !formIds.includes(inputForm.id)) {
                    // Collecter aussi les champs dans des sections spécifiques (comme licence)
                    const section = input.closest('section');
                    const sectionId = section ? section.id : 'global';

                    if (!allData[sectionId]) {
                        allData[sectionId] = {};
                    }

                    if (input.type === 'checkbox') {
                        allData[sectionId][input.name] = input.checked ? input.value : '';
                    } else if (input.type === 'radio') {
                        if (input.checked) {
                            allData[sectionId][input.name] = input.value;
                        }
                    } else {
                        allData[sectionId][input.name] = input.value;
                    }
                }
            }
        });

        console.log('PDF Builder - Données collectées:', allData);
        return allData;
    }

    /**
     * Sauvegarde toutes les données via AJAX
     */
    function saveAllSettings(formData) {
        console.log('PDF Builder - Sauvegarde de toutes les données...');
        console.log('PDF Builder - Données à envoyer:', formData);

        // Afficher un indicateur de chargement
        const saveBtn = document.getElementById('pdf-builder-save-floating-btn');
        const originalText = saveBtn.textContent;
        saveBtn.textContent = 'Sauvegarde...';
        saveBtn.disabled = true;

        // Préparer les données pour AJAX
        const ajaxData = {
            action: 'pdf_builder_save_all_settings',
            nonce: pdfBuilderAjax ? pdfBuilderAjax.nonce : '',
            form_data: JSON.stringify(formData)
        };

        console.log('PDF Builder - Données AJAX préparées:', ajaxData);

        // Envoyer via AJAX
        fetch(pdfBuilderAjax ? pdfBuilderAjax.ajaxurl : '/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(ajaxData)
        })
        .then(response => response.json())
        .then(data => {
            console.log('PDF Builder - Réponse de sauvegarde:', data);

            if (data.success) {
                console.log('PDF Builder - Sauvegarde réussie, données sauvegardées:', data.saved_count);
                // Afficher un message de succès
                showSaveMessage('Toutes les données ont été sauvegardées avec succès!', 'success');

                // Déclencher un événement personnalisé pour que d'autres scripts puissent réagir
                document.dispatchEvent(new CustomEvent('pdfBuilderSettingsSaved', {
                    detail: { formData: formData, response: data }
                }));
            } else {
                console.error('PDF Builder - Erreur de sauvegarde:', data);
                // Afficher un message d'erreur
                showSaveMessage('Erreur lors de la sauvegarde: ' + (data.data || 'Erreur inconnue'), 'error');
            }
        })
        .catch(error => {
            console.error('PDF Builder - Erreur AJAX:', error);
            showSaveMessage('Erreur de communication avec le serveur', 'error');
        })
        .finally(() => {
            // Restaurer le bouton
            saveBtn.textContent = originalText;
            saveBtn.disabled = false;
        });
    }

    /**
     * Affiche un message de sauvegarde
     */
    function showSaveMessage(message, type) {
        // Supprimer les anciens messages
        const existingMessages = document.querySelectorAll('.pdf-builder-save-message');
        existingMessages.forEach(msg => msg.remove());

        // Créer le nouveau message
        const messageDiv = document.createElement('div');
        messageDiv.className = `pdf-builder-save-message notice notice-${type === 'success' ? 'success' : 'error'} is-dismissible`;
        messageDiv.innerHTML = `<p>${message}</p>`;

        // Ajouter au conteneur de messages ou au début de la page
        const container = document.querySelector('.wrap') || document.body;
        container.insertBefore(messageDiv, container.firstChild);

        // Auto-suppression après 5 secondes
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.remove();
            }
        }, 5000);
    }

})();
