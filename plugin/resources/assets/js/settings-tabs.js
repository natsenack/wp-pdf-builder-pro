/**
 * PDF Builder Pro - Navigation des onglets
 * Version: 2.1.0 - Sécurisé et optimisé
 * Date: 2025-12-06
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

    // Métriques de performance
    const PerformanceMetrics = {
        startTime: 0,
        requestCount: 0,
        errorCount: 0,

        start(operation) {
            this.startTime = performance.now();
            if (PDF_BUILDER_CONFIG.debug) {
                console.log(`[PDF Builder] Début ${operation}`);
            }
        },

        end(operation) {
            const duration = performance.now() - this.startTime;
            this.requestCount++;

            if (PDF_BUILDER_CONFIG.debug) {
                console.log(`[PDF Builder] ${operation} terminé en ${duration.toFixed(2)}ms`);
            }

            // Stocker les métriques dans localStorage
            this.storeMetrics(operation, duration);
        },

        error(operation, error) {
            this.errorCount++;
            if (PDF_BUILDER_CONFIG.debug) {
                console.error(`[PDF Builder] Erreur ${operation}:`, error);
            }
        },

        storeMetrics(operation, duration) {
            try {
                const metrics = JSON.parse(localStorage.getItem('pdf_builder_metrics') || '{}');
                if (!metrics[operation]) {
                    metrics[operation] = { count: 0, totalTime: 0, avgTime: 0, maxTime: 0 };
                }

                metrics[operation].count++;
                metrics[operation].totalTime += duration;
                metrics[operation].avgTime = metrics[operation].totalTime / metrics[operation].count;
                metrics[operation].maxTime = Math.max(metrics[operation].maxTime, duration);

                localStorage.setItem('pdf_builder_metrics', JSON.stringify(metrics));
            } catch (e) {
                // Ignore les erreurs localStorage
            }
        },

        getMetrics() {
            try {
                return JSON.parse(localStorage.getItem('pdf_builder_metrics') || '{}');
            } catch (e) {
                return {};
            }
        }
    };

    // Compatibilité navigateurs - Fallback pour fetch
    const AjaxCompat = {
        fetch(url, options) {
            // Utiliser fetch si disponible
            if (window.fetch) {
                return window.fetch(url, options);
            }

            // Fallback vers XMLHttpRequest
            return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();

                xhr.open(options.method || 'GET', url);

                // Headers
                if (options.headers) {
                    Object.keys(options.headers).forEach(key => {
                        xhr.setRequestHeader(key, options.headers[key]);
                    });
                }

                xhr.onload = () => {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        resolve({
                            ok: true,
                            status: xhr.status,
                            json: () => Promise.resolve(JSON.parse(xhr.responseText))
                        });
                    } else {
                        reject(new Error(`HTTP ${xhr.status}`));
                    }
                };

                xhr.onerror = () => reject(new Error('Network error'));
                xhr.send(options.body);
            });
        }
    };

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
            const content = contentContainer.querySelector('#' + tabId);

            if (content) {
                content.classList.add('active');
                
            } else {
                
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
        
        if (firstTab) {
            
            firstTab.click();
        } else {
            
        }
    }

    // Initialiser au chargement du DOM
    document.addEventListener('DOMContentLoaded', initTabs);

    // Bouton de sauvegarde flottant
    let saveButtonInitialized = false;

    function initSaveButton() {
        // Vérifier si on est sur la page de paramètres
        if (typeof window !== 'undefined' && window.location && window.location.href.indexOf('page=pdf-builder-settings') === -1) {
            
            return;
        }

        if (saveButtonInitialized) {
            
            return;
        }

        const saveBtn = document.getElementById('pdf-builder-save-floating-btn');
        const floatingContainer = document.getElementById('pdf-builder-save-floating');

        if (saveBtn && floatingContainer) {
            saveBtn.addEventListener('click', function(e) {
                e.preventDefault();

                // Collecter toutes les données de tous les formulaires de tous les onglets
                const allFormData = collectAllFormData();

                if (Object.keys(allFormData).length > 0) {
                    
                    // Sauvegarder via AJAX
                    saveAllSettings(allFormData);
                } else {
                    
                }
            });

            saveButtonInitialized = true;
            
        } else {
            
            setTimeout(initSaveButton, 1000);
        }
    }

    // Initialiser le bouton flottant aussi
    document.addEventListener('DOMContentLoaded', initSaveButton);

    // Initialiser les notifications pour les toggles et contrôles interactifs
    function initToggleNotifications() {

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

            }
        });

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

    // Système de sauvegarde centralisé - Empêcher les soumissions individuelles des formulaires
    let centralizedSaveInitialized = false;

    function initCentralizedSaveSystem() {
        if (centralizedSaveInitialized) {
            return;
        }

        // Liste des IDs de formulaires à centraliser
        const formIds = [
            'general-form',
            'licence-form',
            'systeme-form',
            'securite-form',
            'pdf-form',
            'contenu-form',
            'templates-status-form',
            'developpeur-form'
        ];

        // Ajouter des écouteurs d'événements à tous les formulaires pour centraliser la sauvegarde
        formIds.forEach(formId => {
            const form = document.getElementById(formId);
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault(); // Empêcher la soumission par défaut

                    // Collecter toutes les données de tous les formulaires
                    const allFormData = collectAllFormData();

                    if (Object.keys(allFormData).length > 0) {
                        // Sauvegarder via le système centralisé AJAX
                        saveAllSettings(allFormData);
                    } else {
                        showSaveMessage('Aucune donnée à sauvegarder.', 'info');
                    }

                    return false; // Sécurité supplémentaire
                });
            }
        });

        centralizedSaveInitialized = true;
    }

    // Initialiser le système de sauvegarde centralisé
    document.addEventListener('DOMContentLoaded', initCentralizedSaveSystem);

    // Système de sauvegarde automatique pour tous les contrôles interactifs
    let autoSaveTimeout = null;
    const AUTO_SAVE_DELAY = 2000; // 2 secondes de délai

    function initAutoSaveSystem() {
        // Écouter tous les changements sur les éléments de formulaire PDF Builder
        document.addEventListener('change', function(event) {
            const target = event.target;

            // Vérifier si c'est un élément dans un formulaire PDF Builder
            if (target.closest('form') && target.name && target.name.includes('pdf_builder')) {

                // Types d'éléments à surveiller pour la sauvegarde automatique
                const autoSaveTypes = ['checkbox', 'radio', 'select-one', 'select-multiple', 'text', 'email', 'url', 'tel', 'number', 'textarea'];

                if (autoSaveTypes.includes(target.type) || target.tagName === 'TEXTAREA' || target.tagName === 'SELECT') {

                    // Annuler le délai précédent
                    if (autoSaveTimeout) {
                        clearTimeout(autoSaveTimeout);
                    }

                    // Programmer une sauvegarde automatique avec délai
                    autoSaveTimeout = setTimeout(() => {
                        const modifiedData = collectModifiedData();
                        if (Object.keys(modifiedData).length > 0) {
                            saveAllSettings(modifiedData, true); // true = sauvegarde automatique silencieuse
                        }
                    }, AUTO_SAVE_DELAY);
                }
            }
        });

        // Écouter aussi les événements input pour les champs textuels (sauvegarde en temps réel avec délai plus long)
        document.addEventListener('input', function(event) {
            const target = event.target;

            if (target.closest('form') && target.name && target.name.includes('pdf_builder')) {
                const textTypes = ['text', 'email', 'url', 'tel', 'number', 'textarea'];

                if (textTypes.includes(target.type) || target.tagName === 'TEXTAREA') {
                    // Délai plus long pour les champs textuels (5 secondes)
                    if (autoSaveTimeout) {
                        clearTimeout(autoSaveTimeout);
                    }

                    autoSaveTimeout = setTimeout(() => {
                        const modifiedData = collectModifiedData();
                        if (Object.keys(modifiedData).length > 0) {
                            saveAllSettings(modifiedData, true);
                        }
                    }, 5000);
                }
            }
        });
    }

    // Initialiser le système de sauvegarde automatique
    document.addEventListener('DOMContentLoaded', initAutoSaveSystem);

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
            'general-form'
        ];

        // Collecter les données de chaque formulaire
        formIds.forEach(formId => {
            const form = document.getElementById(formId);
            if (form) {
                
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

        return allData;
    }

    // Indicateur visuel de statut de sauvegarde
    let saveStatusIndicator = null;

    /**
     * Met à jour l'indicateur visuel de statut
     */
    function updateSaveStatus(status, message = '') {
        if (!saveStatusIndicator) {
            // Créer l'indicateur s'il n'existe pas
            saveStatusIndicator = document.createElement('div');
            saveStatusIndicator.id = 'pdf-builder-save-status';
            saveStatusIndicator.style.cssText = `
                position: fixed;
                top: 50px;
                right: 20px;
                padding: 8px 16px;
                border-radius: 4px;
                font-size: 14px;
                font-weight: 500;
                z-index: 10000;
                display: none;
                transition: all 0.3s ease;
            `;
            document.body.appendChild(saveStatusIndicator);
        }

        saveStatusIndicator.style.display = 'block';

        switch (status) {
            case 'saving':
                saveStatusIndicator.textContent = '⏳ Sauvegarde en cours...';
                saveStatusIndicator.style.backgroundColor = '#fff3cd';
                saveStatusIndicator.style.color = '#856404';
                saveStatusIndicator.style.border = '1px solid #ffeaa7';
                break;
            case 'success':
                saveStatusIndicator.textContent = message || '✅ Sauvegardé';
                saveStatusIndicator.style.backgroundColor = '#d4edda';
                saveStatusIndicator.style.color = '#155724';
                saveStatusIndicator.style.border = '1px solid #c3e6cb';
                setTimeout(() => {
                    if (saveStatusIndicator) saveStatusIndicator.style.display = 'none';
                }, 3000);
                break;
            case 'error':
                saveStatusIndicator.textContent = message || '❌ Erreur de sauvegarde';
                saveStatusIndicator.style.backgroundColor = '#f8d7da';
                saveStatusIndicator.style.color = '#721c24';
                saveStatusIndicator.style.border = '1px solid #f5c6cb';
                setTimeout(() => {
                    if (saveStatusIndicator) saveStatusIndicator.style.display = 'none';
                }, 5000);
                break;
            case 'modified':
                saveStatusIndicator.textContent = '📝 Modifications non sauvegardées';
                saveStatusIndicator.style.backgroundColor = '#fff3cd';
                saveStatusIndicator.style.color = '#856404';
                saveStatusIndicator.style.border = '1px solid #ffeaa7';
                break;
        }
    }

    /**
     * Cache local pour récupération en cas d'erreur
     * Utilise sessionStorage pour éviter les conflits entre onglets
     */
    const LocalCache = {
        save: function(data) {
            try {
                // Calculer un hash simple pour vérifier l'intégrité
                const dataStr = JSON.stringify(data);
                const hash = this.simpleHash(dataStr);

                const cacheData = {
                    data: data,
                    timestamp: Date.now(),
                    version: '1.1',
                    hash: hash,
                    sessionId: this.getSessionId()
                };

                sessionStorage.setItem('pdf_builder_settings_backup', JSON.stringify(cacheData));

                if (PDF_BUILDER_CONFIG.debug) {
                    console.log('[PDF Builder] Cache sauvegardé, hash:', hash);
                }
            } catch (e) {
                console.warn('Impossible de sauvegarder dans le cache local:', e);
            }
        },

        load: function() {
            try {
                const cacheStr = sessionStorage.getItem('pdf_builder_settings_backup');
                if (!cacheStr) return null;

                const cache = JSON.parse(cacheStr);

                // Vérifier la version
                if (cache.version !== '1.1') {
                    this.clear();
                    return null;
                }

                // Vérifier si le cache n'est pas trop vieux (2h pour sessionStorage)
                if (Date.now() - cache.timestamp > 2 * 60 * 60 * 1000) {
                    this.clear();
                    return null;
                }

                // Vérifier la session
                if (cache.sessionId !== this.getSessionId()) {
                    this.clear();
                    return null;
                }

                // Vérifier l'intégrité des données
                const dataStr = JSON.stringify(cache.data);
                const currentHash = this.simpleHash(dataStr);
                if (currentHash !== cache.hash) {
                    console.warn('Cache corrompu détecté, suppression');
                    this.clear();
                    return null;
                }

                if (PDF_BUILDER_CONFIG.debug) {
                    console.log('[PDF Builder] Cache chargé depuis sessionStorage');
                }

                return cache.data;
            } catch (e) {
                console.warn('Impossible de charger depuis le cache local:', e);
                return null;
            }
        },

        clear: function() {
            try {
                sessionStorage.removeItem('pdf_builder_settings_backup');
                if (PDF_BUILDER_CONFIG.debug) {
                    console.log('[PDF Builder] Cache vidé');
                }
            } catch (e) {
                console.warn('Impossible de vider le cache local:', e);
            }
        },

        getSessionId: function() {
            // Générer un ID de session basé sur l'onglet actuel
            let sessionId = sessionStorage.getItem('pdf_builder_session_id');
            if (!sessionId) {
                sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                sessionStorage.setItem('pdf_builder_session_id', sessionId);
            }
            return sessionId;
        },

        simpleHash: function(str) {
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                const char = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash; // Convertir en 32 bits
            }
            return hash.toString();
        }
    };
    function validateFormData(formData) {
        const errors = [];

        // Validation des champs optionnels mais avec format spécifique
        // Note: Ces champs ne sont PAS requis - ils peuvent être vides

        // Validation des types numériques (seulement si une valeur est fournie)
        const numericFields = ['pdf_builder_cache_max_size', 'pdf_builder_cache_ttl'];
        for (const field of numericFields) {
            if (formData[field] && formData[field] !== '' && isNaN(parseInt(formData[field]))) {
                errors.push(`Le champ ${field.replace('pdf_builder_', '').replace('_', ' ')} doit être un nombre`);
            }
        }

        // Validation des URLs (seulement si une valeur est fournie)
        const urlFields = ['pdf_builder_api_endpoint'];
        for (const field of urlFields) {
            if (formData[field] && formData[field] !== '') {
                try {
                    new URL(formData[field]);
                } catch {
                    errors.push(`Le champ ${field.replace('pdf_builder_', '').replace('_', ' ')} doit être une URL valide`);
                }
            }
        }

        // Validation de la clé de licence (format basique si fournie)
        if (formData['pdf_builder_license_key'] && formData['pdf_builder_license_key'] !== '') {
            const licenseKey = formData['pdf_builder_license_key'];
            // Vérifier que ce n'est pas juste des espaces
            if (licenseKey.trim().length === 0) {
                errors.push('La clé de licence ne peut pas être vide');
            }
            // Vérifier la longueur minimale (clé typique de 20+ caractères)
            else if (licenseKey.length < 10) {
                errors.push('La clé de licence semble trop courte');
            }
        }

        return errors;
    }

    /**
     * Sauvegarde toutes les données via AJAX
     */
    function saveAllSettings(formData, isAutoSave = false) {
        PerformanceMetrics.start('saveAllSettings');

        try {
            // Validation des données avant sauvegarde
            const validationErrors = validateFormData(formData);
            if (validationErrors.length > 0 && !isAutoSave) {
                showSaveMessage('Erreurs de validation: ' + validationErrors.join(', '), 'error');
                PerformanceMetrics.error('saveAllSettings', 'Validation failed');
                return;
            }

            // Préparer les références pour l'interface utilisateur
            const saveBtn = document.getElementById('pdf-builder-save-floating-btn');
            const originalText = saveBtn ? saveBtn.textContent : '';

            // Pour les sauvegardes automatiques, ne pas modifier l'interface utilisateur
            if (!isAutoSave && saveBtn) {
                saveBtn.textContent = 'Sauvegarde...';
                saveBtn.disabled = true;
                updateSaveStatus('saving');
            }

            // Sauvegarder dans le cache local avant envoi
            LocalCache.save(flattenedData);

            // Préparer les données pour AJAX - convertir les arrays en JSON
            const ajaxData = {
                action: 'pdf_builder_ajax_handler',
                action_type: 'save_all_settings',
                nonce: pdfBuilderAjax ? pdfBuilderAjax.nonce : '',
            };

            // Traiter chaque champ en convertissant les arrays en JSON
            for (const key in flattenedData) {
                if (flattenedData.hasOwnProperty(key)) {
                    if (Array.isArray(flattenedData[key])) {
                        ajaxData[key] = JSON.stringify(flattenedData[key]);
                    } else {
                        ajaxData[key] = flattenedData[key];
                    }
                }
            }

            // Envoyer via AJAX avec compatibilité navigateurs
            const ajaxFormData = new FormData();
            for (const key in ajaxData) {
                if (ajaxData.hasOwnProperty(key)) {
                    ajaxFormData.append(key, ajaxData[key]);
                }
            }

            AjaxCompat.fetch(pdfBuilderAjax ? pdfBuilderAjax.ajaxurl : '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: ajaxFormData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                PerformanceMetrics.end('saveAllSettings');

                if (data.success) {
                    // Afficher un message de succès (plus discret pour les sauvegardes automatiques)
                    if (isAutoSave) {
                        updateSaveStatus('success', 'Sauvegardé automatiquement');
                    } else {
                        updateSaveStatus('success', 'Toutes les données ont été sauvegardées avec succès!');
                        showSaveMessage('Toutes les données ont été sauvegardées avec succès!', 'success');
                    }

                    // Mettre à jour les champs du formulaire avec les valeurs sauvegardées
                    if (data.data && data.data.saved_settings) {
                        updateFormFieldsWithSavedData(data.data.saved_settings);
                    }

                    // Déclencher un événement personnalisé
                    document.dispatchEvent(new CustomEvent('pdfBuilderSettingsSaved', {
                        detail: { formData: formData, response: data, isAutoSave: isAutoSave }
                    }));

                    // Réinitialiser le suivi des modifications après sauvegarde réussie
                    if (!isAutoSave) {
                        resetChangeTracking();
                    }

                    // Recharger les paramètres de debug silencieusement
                    reloadDebugSettings().catch(error => {
                        // Ne pas afficher d'erreur car la sauvegarde a réussi
                    });

                } else {
                    // Gestion d'erreur améliorée
                    const errorMessage = data.data && data.data.message ? data.data.message : 'Erreur inconnue';
                    updateSaveStatus('error', `Erreur: ${errorMessage}`);
                    showSaveMessage(`Erreur lors de la sauvegarde: ${errorMessage}`, 'error');
                    PerformanceMetrics.error('saveAllSettings', errorMessage);
                }
            })
            .catch(error => {
                PerformanceMetrics.error('saveAllSettings', error.message);

                // Restaurer depuis le cache local en cas d'erreur réseau
                const cachedData = LocalCache.load();
                if (cachedData) {
                    showSaveMessage('Erreur réseau - Données restaurées depuis le cache local', 'error');
                    updateFormFieldsWithSavedData(cachedData);
                } else {
                    updateSaveStatus('error', 'Erreur réseau - Impossible de sauvegarder');
                    showSaveMessage('Erreur réseau: ' + error.message, 'error');
                }

                console.error('Erreur de sauvegarde:', error);
            })
            .finally(() => {
                // Restaurer l'interface utilisateur
                if (!isAutoSave && saveBtn) {
                    saveBtn.textContent = originalText;
                    saveBtn.disabled = false;
                }
            });

        } catch (error) {
            PerformanceMetrics.error('saveAllSettings', error.message);
            console.error('Erreur inattendue dans saveAllSettings:', error);
            showSaveMessage('Erreur inattendue lors de la sauvegarde', 'error');
        }
    }

            // Tenter une récupération depuis le cache local
            const cachedData = LocalCache.load();
            if (cachedData && !isAutoSave) {
                updateSaveStatus('error', 'Erreur de connexion - Données récupérées du cache');
                showSaveMessage('Erreur de connexion. Données récupérées depuis le cache local.', 'error');

                // Restaurer les valeurs depuis le cache
                updateFormFieldsWithSavedData(cachedData);
            } else {
                updateSaveStatus('error', 'Erreur de communication');
                showSaveMessage('Erreur de communication avec le serveur. Réessayez plus tard.', 'error');
            }
        })
        .finally(() => {
            // Restaurer le bouton seulement pour les sauvegardes manuelles
            if (!isAutoSave && saveBtn) {
                saveBtn.textContent = originalText;
                saveBtn.disabled = false;
            }
        });
    }

    /**
     * Met à jour les champs du formulaire avec les données sauvegardées pour un comportement dynamique
     */
    function updateFormFieldsWithSavedData(savedSettings) {
        // Mettre à jour tous les champs du formulaire avec les valeurs sauvegardées
        for (const [fieldName, fieldValue] of Object.entries(savedSettings)) {
            // Essayer d'abord le nom du champ tel quel, puis avec le préfixe pdf_builder_
            let input = document.getElementById(fieldName) || document.querySelector(`[name="${fieldName}"]`) || document.querySelector(`[name="${fieldName}[]"]`);

            if (!input) {
                // Essayer avec le préfixe pdf_builder_
                const prefixedName = 'pdf_builder_' + fieldName;
                input = document.getElementById(prefixedName) || document.querySelector(`[name="${prefixedName}"]`) || document.querySelector(`[name="${prefixedName}[]"]`);
            }

            if (input) {
                if (input.type === 'checkbox') {
                    input.checked = fieldValue === '1' || fieldValue === 1 || fieldValue === true;
                } else if (input.type === 'radio') {
                    // Pour les radios, trouver celui avec la bonne valeur
                    const radios = document.querySelectorAll(`[name="${input.name}"]`);
                    radios.forEach(radio => {
                        radio.checked = radio.value == fieldValue;
                    });
                } else if (input.tagName === 'SELECT') {
                    input.value = fieldValue;
                } else {
                    input.value = fieldValue;
                }
            }
        }
    }

    /**
      * Recharge les paramètres de debug depuis la base de données
      */
    function reloadDebugSettings() {

        const ajaxUrl = pdfBuilderAjax ? pdfBuilderAjax.ajaxurl : '/wp-admin/admin-ajax.php';
        const nonce = pdfBuilderAjax ? pdfBuilderAjax.nonce : '';

        const requestData = new FormData();
        requestData.append('action', 'pdf_builder_get_debug_settings');
        requestData.append('nonce', nonce);

        return fetch(ajaxUrl, {
            method: 'POST',
            body: requestData
        })
        .then(response => {

            if (!response.ok) {
                // console.error('PDF Builder - [RELOAD DEBUG] HTTP ERROR - Status:', response.status, 'StatusText:', response.statusText);
                throw new Error('HTTP Error: ' + response.status + ' ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {

            if (data.success && data.data) {

                // Mettre à jour les checkboxes de debug
                const debugFields = [
                    'debug_javascript',
                    'debug_javascript_verbose',
                    'debug_ajax',
                    'debug_performance',
                    'debug_database',
                    'debug_php_errors'
                ];

                debugFields.forEach(fieldName => {
                    const checkbox = document.getElementById(fieldName);
                    if (checkbox) {
                        const newValue = data.data[fieldName] || false;
                        
                        checkbox.checked = newValue;
                    } else {
                        // console.warn(`PDF Builder - [RELOAD DEBUG] Checkbox non trouvée: ${fieldName}`);
                    }
                });

                return data.data;
            } else {
                // console.error('PDF Builder - [RELOAD DEBUG] Erreur - données invalides:', data);
                throw new Error(data.data?.message || 'Erreur lors du rechargement des paramètres de debug');
            }
        })
        .catch(error => {
            // console.error('PDF Builder - [RELOAD DEBUG] Erreur dans la promesse:', error);
            // console.error('PDF Builder - [RELOAD DEBUG] Message d\'erreur:', error.message);
            // console.error('PDF Builder - [RELOAD DEBUG] Stack trace:', error.stack);
            throw error;
        });
    }

    // Système de suivi des modifications pour sauvegarde sélective
    let originalFormData = {};
    let modifiedFields = new Set();

    /**
     * Initialise le suivi des modifications
     */
    function initChangeTracking() {
        // Charger les données originales au chargement de la page
        setTimeout(() => {
            originalFormData = collectAllFormData();
        }, 1000);

        // Suivre les modifications sur tous les champs
        document.addEventListener('change', function(event) {
            const target = event.target;
            if (target.name && target.name.includes('pdf_builder')) {
                modifiedFields.add(target.name);
                updateSaveStatus('modified');
            }
        });

        document.addEventListener('input', function(event) {
            const target = event.target;
            if (target.name && target.name.includes('pdf_builder')) {
                modifiedFields.add(target.name);
                updateSaveStatus('modified');
            }
        });
    }

    /**
     * Collecte seulement les données modifiées
     */
    function collectModifiedData() {
        const currentData = collectAllFormData();
        const modifiedData = {};

        for (const field of modifiedFields) {
            // Chercher la valeur actuelle dans currentData
            for (const formId in currentData) {
                if (currentData[formId][field] !== undefined) {
                    if (!modifiedData[formId]) modifiedData[formId] = {};
                    modifiedData[formId][field] = currentData[formId][field];
                    break;
                }
            }
        }

        return modifiedData;
    }

    /**
     * Réinitialise le suivi des modifications après sauvegarde réussie
     */
    function resetChangeTracking() {
        originalFormData = collectAllFormData();
        modifiedFields.clear();
    }

    // Initialiser les gestionnaires d'événements au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        initTabs();
        initSaveButton();
        initChangeTracking();
    });

    // Exposer les fonctions de monitoring globalement pour le debug
    window.PDF_BUILDER_DEBUG = {
        getMetrics: () => PerformanceMetrics.getMetrics(),
        clearMetrics: () => {
            localStorage.removeItem('pdf_builder_metrics');
            console.log('Métriques vidées');
        },
        getCache: () => LocalCache.load(),
        clearCache: () => LocalCache.clear(),
        getModifiedFields: () => Array.from(modifiedFields),
        forceSave: () => saveAllSettings(collectAllFormData(), false),
        getValidationErrors: () => validateFormData(collectAllFormData()),
        testAjaxConnection: () => {
            return AjaxCompat.fetch(pdfBuilderAjax ? pdfBuilderAjax.ajaxurl : '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: new FormData([['action', 'pdf_builder_ajax_handler'], ['action_type', 'get_settings']])
            }).then(r => r.json());
        }
    };

})();

// FORCE CACHE BUST - Modified: 2025-12-06 - Added monitoring and security improvements
// Cache bust timestamp: 1733440875


