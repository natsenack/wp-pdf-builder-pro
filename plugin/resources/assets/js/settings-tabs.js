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

    /**
     * Affiche un message de sauvegarde
     */
    function showSaveMessage(message, type) {
        // Utiliser le système de notifications personnalisé si disponible
        if (typeof window.showSuccessNotification === 'function' && typeof window.showErrorNotification === 'function') {
            if (type === 'success') {
                window.showSuccessNotification(message, { duration: 4000 });
            } else {
                window.showErrorNotification(message, { duration: 6000 });
            }
        } else {
            // Fallback vers les messages WordPress classiques
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
    }

    // Système de navigation des onglets
    function initTabs() {
        console.log('PDF Builder - initTabs called');
        const tabsContainer = document.getElementById('pdf-builder-tabs');
        const contentContainer = document.getElementById('pdf-builder-tab-content');

        console.log('PDF Builder - tabsContainer:', tabsContainer);
        console.log('PDF Builder - contentContainer:', contentContainer);

        if (!tabsContainer || !contentContainer) {
            console.log('PDF Builder - Containers not found, exiting');
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
            const content = contentContainer.querySelector('#' + tabId);
            console.log('PDF Builder - Looking for content with selector:', '#' + tabId);
            console.log('PDF Builder - Found content element:', content);
            if (content) {
                content.classList.add('active');
                console.log('PDF Builder - Added active class to content');
            } else {
                console.log('PDF Builder - Content element not found!');
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
                const savedContent = contentContainer.querySelector('#' + savedTab);
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
        console.log('PDF Builder - First tab found:', firstTab);
        if (firstTab) {
            console.log('PDF Builder - Clicking first tab');
            firstTab.click();
        } else {
            console.log('PDF Builder - No first tab found');
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
            saveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                debugLog('PDF Builder - Clic sur le bouton flottant');

                // Collecter toutes les données de tous les formulaires de tous les onglets
                const allFormData = collectAllFormData();

                if (Object.keys(allFormData).length > 0) {
                    debugLog('PDF Builder - Données collectées pour sauvegarde:', allFormData);
                    // Sauvegarder via AJAX
                    saveAllSettings(allFormData);
                } else {
                    debugError('PDF Builder - Aucune donnée de formulaire trouvée à sauvegarder');
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

    // Initialiser les notifications pour les toggles et contrôles interactifs
    function initToggleNotifications() {
        debugLog('PDF Builder - Initialisation des notifications de toggle...');

        // Écouter les changements sur les checkboxes et radios
        document.addEventListener('change', function(event) {
            const target = event.target;

            // Ne traiter que les checkboxes et radios dans les formulaires PDF Builder
            if ((target.type === 'checkbox' || target.type === 'radio') &&
                target.closest('form') &&
                target.name) {

                // Obtenir un label descriptif pour le toggle
                const label = getToggleLabel(target);
                const isChecked = target.type === 'checkbox' ? target.checked : target.checked;
                const action = isChecked ? 'activé' : 'désactivé';

                // Messages personnalisés selon le type de toggle
                let message = '';
                let notificationType = isChecked ? 'success' : 'info';

                if (target.name.includes('debug')) {
                    message = `Mode debug ${action} pour ${label}`;
                } else if (target.name.includes('enable') || target.name.includes('enabled')) {
                    message = `${label} ${action}`;
                } else if (target.name.includes('auto')) {
                    message = `${label} ${action}`;
                } else if (target.name.includes('cache')) {
                    message = `Cache ${action}`;
                } else if (target.name.includes('performance')) {
                    message = `Optimisation performance ${action}`;
                } else if (target.name.includes('backup')) {
                    message = `Sauvegarde automatique ${action}`;
                } else if (target.name.includes('maintenance')) {
                    message = `Maintenance automatique ${action}`;
                } else if (target.name.includes('license')) {
                    message = `Gestion licence ${action}`;
                } else {
                    message = `${label} ${action}`;
                }

                // Afficher la notification
                if (typeof window.showSuccessNotification === 'function' &&
                    typeof window.showInfoNotification === 'function') {

                    if (notificationType === 'success') {
                        window.showSuccessNotification(message, { duration: 3000 });
                    } else {
                        window.showInfoNotification(message, { duration: 3000 });
                    }
                }

                debugLog(`PDF Builder - Toggle changé: ${target.name} = ${isChecked}`);
            }
        });

        debugLog('PDF Builder - Notifications de toggle initialisées');
    }

    // Fonction helper pour obtenir un label descriptif du toggle
    function getToggleLabel(input) {
        // Chercher d'abord un label associé
        const label = document.querySelector(`label[for="${input.id}"]`);
        if (label) {
            return label.textContent.trim().replace(/[:*]$/, '');
        }

        // Chercher dans le parent (cas des toggles avec structure complexe)
        const parent = input.closest('.form-group, .setting-group, .option-group');
        if (parent) {
            const parentLabel = parent.querySelector('label, h3, h4, .setting-title');
            if (parentLabel) {
                return parentLabel.textContent.trim().replace(/[:*]$/, '');
            }
        }

        // Fallback: utiliser le nom du champ de manière plus lisible
        return input.name
            .replace(/_/g, ' ')
            .replace(/\b\w/g, l => l.toUpperCase())
            .replace(/Pdf Builder/g, 'PDF Builder');
    }

    // Initialiser les notifications de toggle
    document.addEventListener('DOMContentLoaded', initToggleNotifications);

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

        // Fonction pour normaliser les noms de champs (retirer [] à la fin)
        function normalizeFieldName(name) {
            return name.replace(/\[\]$/, '');
        }

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
            'canvas-debug-form',
            'acces-form'
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
                    const normalizedKey = normalizeFieldName(key);
                    if (formObject[normalizedKey]) {
                        if (Array.isArray(formObject[normalizedKey])) {
                            formObject[normalizedKey].push(value);
                        } else {
                            formObject[normalizedKey] = [formObject[normalizedKey], value];
                        }
                    } else {
                        formObject[normalizedKey] = value;
                    }
                }

                // TRAITEMENT SPÉCIAL pour pdf_builder_allowed_roles - collecter même les non-cochées
                if (formId === 'acces-form') {
                    console.log('PDF Builder - Collecte spéciale des rôles...');
                    const allowedRolesCheckboxes = form.querySelectorAll('input[name="pdf_builder_allowed_roles[]"]');
                    console.log('PDF Builder - Cases à cocher trouvées:', allowedRolesCheckboxes.length);
                    const selectedRoles = [];
                    allowedRolesCheckboxes.forEach((checkbox, index) => {
                        console.log(`PDF Builder - Case ${index + 1}: ${checkbox.value} - checked: ${checkbox.checked} - disabled: ${checkbox.disabled}`);
                        if (checkbox.checked) {
                            selectedRoles.push(checkbox.value);
                        }
                    });
                    formObject.pdf_builder_allowed_roles = selectedRoles;
                    console.log(`PDF Builder - Rôles sélectionnés collectés: ${selectedRoles.length} rôles - ${selectedRoles.join(', ')} - ${Date.now()}`);
                }

                // Ajouter les données du formulaire à allData
                allData[formId] = formObject;
            }
        });

        // Collecter aussi tous les champs input, select, textarea qui ne sont pas dans des formulaires
        const allInputs = document.querySelectorAll('input[name], select[name], textarea[name]');
        allInputs.forEach(input => {
            if (input.name && input.name !== '') {
                const normalizedName = normalizeFieldName(input.name);
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
                        if (allData[sectionId][normalizedName]) {
                            if (Array.isArray(allData[sectionId][normalizedName])) {
                                if (input.checked) {
                                    allData[sectionId][normalizedName].push(input.value);
                                }
                            } else {
                                allData[sectionId][normalizedName] = input.checked ? [allData[sectionId][normalizedName], input.value] : [allData[sectionId][normalizedName]];
                            }
                        } else {
                            allData[sectionId][normalizedName] = input.checked ? [input.value] : [];
                        }
                    } else if (input.type === 'radio') {
                        if (input.checked) {
                            allData[sectionId][normalizedName] = input.value;
                        }
                    } else {
                        allData[sectionId][normalizedName] = input.value;
                    }
                }
            }
        });

        debugLog('PDF Builder - Données collectées:', allData);
        // Log spécifique pour les données d'accès
        if (allData.acces && allData.acces.pdf_builder_allowed_roles) {
            debugLog('PDF Builder - Données acces collectées:', allData.acces.pdf_builder_allowed_roles);
        }
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

        // Aplatir les données pour éviter les problèmes de taille JSON
        const flattenedData = {};
        for (const formId in formData) {
            if (formData.hasOwnProperty(formId) && typeof formData[formId] === 'object') {
                for (const key in formData[formId]) {
                    if (formData[formId].hasOwnProperty(key)) {
                        flattenedData[key] = formData[formId][key];
                    }
                }
            }
        }

        console.log('PDF Builder - Données aplaties:', flattenedData);
        // Log spécifique pour pdf_builder_allowed_roles
        if (flattenedData.pdf_builder_allowed_roles) {
            console.log('PDF Builder - pdf_builder_allowed_roles à envoyer:', flattenedData.pdf_builder_allowed_roles);
            console.log('PDF Builder - Type de pdf_builder_allowed_roles:', typeof flattenedData.pdf_builder_allowed_roles);
            console.log('PDF Builder - Longueur de pdf_builder_allowed_roles:', flattenedData.pdf_builder_allowed_roles.length);
        } else {
            console.log('PDF Builder - pdf_builder_allowed_roles est undefined ou vide');
        }

        // S'assurer que pdf_builder_allowed_roles est toujours envoyé, même vide
        if (!flattenedData.hasOwnProperty('pdf_builder_allowed_roles')) {
            flattenedData.pdf_builder_allowed_roles = [];
            console.log('PDF Builder - pdf_builder_allowed_roles forcé à array vide');
        }

        // DEBUG: Log debug fields being sent
        const debugFields = Object.keys(flattenedData).filter(key => key.includes('debug'));
        console.log('PDF Builder - Debug fields being sent:', debugFields);
        console.log('PDF Builder - pdf_builder_debug_javascript value:', flattenedData['pdf_builder_debug_javascript']);

        // Préparer les données pour AJAX - convertir les arrays en JSON
        const ajaxData = {
            action: 'pdf_builder_save_all_settings',
            nonce: pdfBuilderAjax ? pdfBuilderAjax.nonce : '',
        };

        // Traiter chaque champ en convertissant les arrays en JSON
        for (const key in flattenedData) {
            if (flattenedData.hasOwnProperty(key)) {
                if (Array.isArray(flattenedData[key])) {
                    ajaxData[key] = JSON.stringify(flattenedData[key]);
                    console.log(`PDF Builder - Array converti en JSON pour ${key}:`, ajaxData[key]);
                } else {
                    ajaxData[key] = flattenedData[key];
                }
            }
        }

        console.log('PDF Builder - Données AJAX préparées (aplaties):', ajaxData);

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

                // Tenter de recharger les données des rôles pour mettre à jour l'interface (optionnel)
                console.log('PDF Builder - Tentative de rechargement des rôles après sauvegarde...');
                reloadRolesData().then(updatedRoles => {
                    console.log('PDF Builder - Interface mise à jour avec les rôles sauvegardés:', updatedRoles);
                }).catch(error => {
                    console.warn('PDF Builder - Impossible de recharger automatiquement les rôles, mais la sauvegarde a réussi:', error);
                    // Ne pas afficher d'erreur à l'utilisateur car la sauvegarde a fonctionné
                });
            } else {
                debugError('PDF Builder - Erreur de sauvegarde:', data);
                debugError('PDF Builder - Détails de l\'erreur:', data.data);
                // Afficher un message d'erreur
                showSaveMessage('Erreur lors de la sauvegarde: ' + (data.data || data.message || 'Erreur inconnue'), 'error');
            }
        })
        .catch(error => {
            debugError('PDF Builder - Erreur AJAX:', error);
            showSaveMessage('Erreur de communication avec le serveur', 'error');
        })
        .finally(() => {
            // Restaurer le bouton
            saveBtn.textContent = originalText;
            saveBtn.disabled = false;
        });
    }

    /**
     * Recharge les données des rôles depuis la base de données
     */
    function reloadRolesData() {
        console.log('PDF Builder - [RELOAD ROLES] ===== DÉBUT RECHARGEMENT =====');
        console.log('PDF Builder - [RELOAD ROLES] pdfBuilderAjax défini:', typeof pdfBuilderAjax !== 'undefined');
        console.log('PDF Builder - [RELOAD ROLES] pdfBuilderAjax contenu:', pdfBuilderAjax);

        const ajaxUrl = pdfBuilderAjax ? pdfBuilderAjax.ajaxurl : '/wp-admin/admin-ajax.php';
        const nonce = pdfBuilderAjax ? pdfBuilderAjax.nonce : '';

        console.log('PDF Builder - [RELOAD ROLES] URL AJAX:', ajaxUrl);
        console.log('PDF Builder - [RELOAD ROLES] Nonce:', nonce ? 'présent (' + nonce.substring(0, 8) + '...)' : 'vide');
        console.log('PDF Builder - [RELOAD ROLES] Action à envoyer: pdf_builder_test_roles');

        const requestData = new URLSearchParams({
            action: 'pdf_builder_test_roles',
            nonce: nonce
        });
        console.log('PDF Builder - [RELOAD ROLES] Données de requête:', requestData.toString());

        return fetch(ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: requestData
        })
        .then(response => {
            console.log('PDF Builder - [RELOAD ROLES] Réponse HTTP reçue - Status:', response.status);
            console.log('PDF Builder - [RELOAD ROLES] Réponse OK:', response.ok);
            if (!response.ok) {
                console.error('PDF Builder - [RELOAD ROLES] HTTP ERROR - Status:', response.status, 'StatusText:', response.statusText);
                throw new Error('HTTP Error: ' + response.status + ' ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            console.log('PDF Builder - [RELOAD ROLES] Données JSON reçues:', data);
            console.log('PDF Builder - [RELOAD ROLES] Success:', data.success);
            console.log('PDF Builder - [RELOAD ROLES] Data object:', data.data);

            if (data.success && data.data && Array.isArray(data.data.allowed_roles)) {
                console.log('PDF Builder - [RELOAD ROLES] Rôles autorisés reçus:', data.data.allowed_roles);
                console.log('PDF Builder - [RELOAD ROLES] Nombre de rôles:', data.data.allowed_roles.length);
                console.log('PDF Builder - [RELOAD ROLES] Status du handler:', data.data.status);

                // Mettre à jour l'état des cases à cocher
                console.log('PDF Builder - [RELOAD ROLES] Mise à jour des cases à cocher...');
                updateRoleCheckboxes(data.data.allowed_roles);

                // Mettre à jour le compteur
                console.log('PDF Builder - [RELOAD ROLES] Mise à jour du compteur...');
                updateSelectedCount(data.data.allowed_roles.length);

                console.log('PDF Builder - [RELOAD ROLES] ===== FIN RECHARGEMENT RÉUSSI =====');
                return data.data.allowed_roles;
            } else {
                console.error('PDF Builder - [RELOAD ROLES] Erreur - données invalides:', data);
                console.error('PDF Builder - [RELOAD ROLES] Data.success:', data.success);
                console.error('PDF Builder - [RELOAD ROLES] Data.data:', data.data);
                console.error('PDF Builder - [RELOAD ROLES] Allowed_roles isArray:', Array.isArray(data.data?.allowed_roles));
                console.error('PDF Builder - [RELOAD ROLES] Full response structure:', JSON.stringify(data, null, 2));
                throw new Error(data.data || 'Erreur lors du rechargement des rôles');
            }
        })
        .catch(error => {
            console.error('PDF Builder - [RELOAD ROLES] Erreur dans la promesse:', error);
            console.error('PDF Builder - [RELOAD ROLES] Message d\'erreur:', error.message);
            console.error('PDF Builder - [RELOAD ROLES] Stack trace:', error.stack);
            throw error;
        });
    }

    /**
     * Met à jour l'état des cases à cocher des rôles
     */
    function updateRoleCheckboxes(allowedRoles) {
        console.log('PDF Builder - [UPDATE CHECKBOXES] ===== DÉBUT MISE À JOUR =====');
        console.log('PDF Builder - [UPDATE CHECKBOXES] Rôles autorisés reçus:', allowedRoles);
        console.log('PDF Builder - [UPDATE CHECKBOXES] Type des rôles:', typeof allowedRoles);
        console.log('PDF Builder - [UPDATE CHECKBOXES] Longueur du tableau:', allowedRoles.length);

        const roleCheckboxes = document.querySelectorAll('input[name="pdf_builder_allowed_roles[]"]');
        console.log('PDF Builder - [UPDATE CHECKBOXES] Nombre de cases trouvées:', roleCheckboxes.length);
        console.log('PDF Builder - [UPDATE CHECKBOXES] Cases trouvées:', roleCheckboxes);

        if (roleCheckboxes.length === 0) {
            console.error('PDF Builder - [UPDATE CHECKBOXES] AUCUNE CASE À COCHER TROUVÉE!');
            console.error('PDF Builder - [UPDATE CHECKBOXES] Vérification du DOM...');
            const allInputs = document.querySelectorAll('input');
            console.log('PDF Builder - [UPDATE CHECKBOXES] Tous les inputs sur la page:', allInputs.length);
            const roleInputs = Array.from(allInputs).filter(input => input.name && input.name.includes('roles'));
            console.log('PDF Builder - [UPDATE CHECKBOXES] Inputs avec "roles" dans le name:', roleInputs);
        }

        roleCheckboxes.forEach((checkbox, index) => {
            const roleKey = checkbox.value;
            const shouldBeChecked = allowedRoles.includes(roleKey);

            console.log(`PDF Builder - [UPDATE CHECKBOXES] Case ${index + 1}: role="${roleKey}", shouldBeChecked=${shouldBeChecked}, currentChecked=${checkbox.checked}, disabled=${checkbox.disabled}`);

            // Ne pas modifier les administrateurs (toujours cochés et désactivés)
            if (roleKey === 'administrator') {
                console.log('PDF Builder - [UPDATE CHECKBOXES] Administrator détecté - forçage checked=true, disabled=true');
                checkbox.checked = true;
                checkbox.disabled = true;
                console.log('PDF Builder - [UPDATE CHECKBOXES] Administrator mis à jour - checked:', checkbox.checked, 'disabled:', checkbox.disabled);
                return;
            }

            console.log(`PDF Builder - [UPDATE CHECKBOXES] Mise à jour case ${roleKey}: ${shouldBeChecked ? 'cochée' : 'décochée'}`);
            checkbox.checked = shouldBeChecked;
            console.log(`PDF Builder - [UPDATE CHECKBOXES] Case ${roleKey} après mise à jour: checked=${checkbox.checked}`);
        });

        console.log('PDF Builder - [UPDATE CHECKBOXES] ===== FIN MISE À JOUR =====');
        
        // Vérification finale
        console.log('PDF Builder - [UPDATE CHECKBOXES] VÉRIFICATION FINALE:');
        roleCheckboxes.forEach((checkbox, index) => {
            console.log(`PDF Builder - [UPDATE CHECKBOXES] Case finale ${index + 1} (${checkbox.value}): checked=${checkbox.checked}, disabled=${checkbox.disabled}`);
        });
    }

    /**
     * Met à jour le compteur de rôles sélectionnés
     */
    function updateSelectedCount(count) {
        const countElement = document.getElementById('selected-count');
        if (countElement) {
            countElement.textContent = count;
        }
    }



    /**
     * Gestionnaire pour les boutons de contrôle rapide des rôles
     */
    function initRoleControlButtons() {
        // Bouton "Sélectionner Tout"
        const selectAllBtn = document.getElementById('select-all-roles');
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function() {
                const roleCheckboxes = document.querySelectorAll('input[name="pdf_builder_allowed_roles[]"]:not([disabled])');
                roleCheckboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });
                updateSelectedCount(roleCheckboxes.length + 1); // +1 pour administrator qui est toujours sélectionné
                debugLog('PDF Builder - Tous les rôles sélectionnés');
            });
        }

        // Bouton "Rôles Courants"
        const selectCommonBtn = document.getElementById('select-common-roles');
        if (selectCommonBtn) {
            selectCommonBtn.addEventListener('click', function() {
                const commonRoles = ['administrator', 'editor', 'shop_manager'];
                const roleCheckboxes = document.querySelectorAll('input[name="pdf_builder_allowed_roles[]"]');
                roleCheckboxes.forEach(checkbox => {
                    const roleKey = checkbox.value;
                    if (roleKey === 'administrator') {
                        checkbox.checked = true; // Toujours coché
                    } else {
                        checkbox.checked = commonRoles.includes(roleKey);
                    }
                });
                updateSelectedCount(commonRoles.length);
                debugLog('PDF Builder - Rôles courants sélectionnés:', commonRoles);
            });
        }

        // Bouton "Désélectionner Tout"
        const selectNoneBtn = document.getElementById('select-none-roles');
        if (selectNoneBtn) {
            selectNoneBtn.addEventListener('click', function() {
                const roleCheckboxes = document.querySelectorAll('input[name="pdf_builder_allowed_roles[]"]:not([disabled])');
                roleCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                updateSelectedCount(1); // Administrator reste toujours sélectionné
                debugLog('PDF Builder - Tous les rôles désélectionnés (sauf administrator)');
            });
        }

        // Mettre à jour le compteur initial
        const initialChecked = document.querySelectorAll('input[name="pdf_builder_allowed_roles[]"]:checked').length;
        updateSelectedCount(initialChecked);
    }

    // Initialiser les gestionnaires d'événements au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        debugLog('PDF Builder - Initialisation des gestionnaires d\'événements...');
        initTabs();
        initSaveButton();
        initRoleControlButtons();
        debugLog('PDF Builder - Tous les gestionnaires initialisés');
    });

})();
