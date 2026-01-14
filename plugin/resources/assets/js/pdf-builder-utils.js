/**
 * Utilitaires PDF Builder Pro
 * Contient les fonctions utilitaires pour les métriques, cache, validation et AJAX
 * Version: 1.0.0
 * Date: 2025-12-10
 */

(function(window, document) {
    'use strict';

    // ==========================================
    // SYSTÈME DE MÉTRIQUES DE PERFORMANCE
    // ==========================================

    /**
     * Système de tracking des performances et erreurs
     */
    window.PerformanceMetrics = {
        metrics: {},

        /**
         * Démarre le tracking d'une opération
         * @param {string} operation - Nom de l'opération
         */
        start: function(operation) {
            if (!this.metrics[operation]) {
                this.metrics[operation] = {
                    count: 0,
                    totalTime: 0,
                    avgTime: 0,
                    errorCount: 0,
                    minTime: Infinity,
                    maxTime: 0
                };
            }
            this.metrics[operation].startTime = Date.now();
        },

        /**
         * Termine le tracking d'une opération
         * @param {string} operation - Nom de l'opération
         */
        end: function(operation) {
            if (this.metrics[operation] && this.metrics[operation].startTime) {
                const duration = Date.now() - this.metrics[operation].startTime;
                const metric = this.metrics[operation];

                metric.count++;
                metric.totalTime += duration;
                metric.avgTime = metric.totalTime / metric.count;
                metric.minTime = Math.min(metric.minTime, duration);
                metric.maxTime = Math.max(metric.maxTime, duration);

                delete metric.startTime;
            }
        },

        /**
         * Enregistre une erreur pour une opération
         * @param {string} operation - Nom de l'opération
         * @param {string|Error} error - Erreur survenue
         */
        error: function(operation, error) {
            if (!this.metrics[operation]) {
                this.metrics[operation] = {
                    count: 0,
                    totalTime: 0,
                    avgTime: 0,
                    errorCount: 0,
                    minTime: Infinity,
                    maxTime: 0
                };
            }
            this.metrics[operation].errorCount++;

            // Log l'erreur si le debug est activé
            if (window.PDF_BUILDER_CONFIG && window.PDF_BUILDER_CONFIG.debug) {
                console.error('PerformanceMetrics error for ' + operation + ':', error);
            }
        },

        /**
         * Récupère toutes les métriques
         * @returns {Object} Métriques collectées
         */
        getMetrics: function() {
            return JSON.parse(JSON.stringify(this.metrics)); // Deep copy
        },

        /**
         * Réinitialise toutes les métriques
         */
        reset: function() {
            this.metrics = {};
        }
    };

    // ==========================================
    // SYSTÈME DE CACHE LOCAL
    // ==========================================

    /**
     * Système de cache local avec expiration et validation
     */
    window.LocalCache = {
        VERSION: '1.1',
        EXPIRY_HOURS: 3,

        /**
         * Efface tout le cache
         */
        clear: function() {
            try {
                sessionStorage.removeItem('pdf_builder_settings_backup');
                sessionStorage.removeItem('pdf_builder_cache_metadata');
            } catch (e) {
                console.warn('LocalCache: Unable to clear cache', e);
            }
        },

        /**
         * Sauvegarde des données dans le cache
         * @param {Object} data - Données à sauvegarder
         */
        save: function(data) {
            try {
                const cache = {
                    data: data,
                    timestamp: Date.now(),
                    version: this.VERSION,
                    hash: this.simpleHash(JSON.stringify(data)),
                    sessionId: this.getSessionId()
                };

                sessionStorage.setItem('pdf_builder_settings_backup', JSON.stringify(cache));

                // Sauvegarder les métadonnées séparément pour la validation
                const metadata = {
                    timestamp: cache.timestamp,
                    version: cache.version,
                    hash: cache.hash
                };
                sessionStorage.setItem('pdf_builder_cache_metadata', JSON.stringify(metadata));

            } catch (e) {
                console.warn('LocalCache: Unable to save data', e);
            }
        },

        /**
         * Charge les données du cache
         * @returns {Object|null} Données chargées ou null si expiré/corrompu
         */
        load: function() {
            try {
                const cacheStr = sessionStorage.getItem('pdf_builder_settings_backup');
                if (!cacheStr) return null;

                const cache = JSON.parse(cacheStr);

                // Vérifier l'expiration (3h ou plus)
                if (Date.now() - cache.timestamp >= this.EXPIRY_HOURS * 60 * 60 * 1000) {
                    this.clear(); // Nettoyer le cache expiré
                    return null;
                }

                // Vérifier la version
                if (cache.version !== this.VERSION) {
                    this.clear();
                    return null;
                }

                // Vérifier l'intégrité des données
                if (cache.hash !== this.simpleHash(JSON.stringify(cache.data))) {
                    this.clear();
                    return null;
                }

                // Vérifier la session
                if (cache.sessionId !== this.getSessionId()) {
                    this.clear();
                    return null;
                }

                return cache.data;

            } catch (e) {
                // Données corrompues
                console.warn('LocalCache: Corrupted data, clearing cache', e);
                this.clear();
                return null;
            }
        },

        /**
         * Génère un hash simple pour la validation d'intégrité
         * @param {string} str - Chaîne à hasher
         * @returns {string} Hash généré
         */
        simpleHash: function(str) {
            let hash = 0;
            if (str.length === 0) return hash.toString();

            for (let i = 0; i < str.length; i++) {
                const char = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash; // Convertir en 32 bits
            }
            return hash.toString();
        },

        /**
         * Génère un ID de session unique
         * @returns {string} ID de session
         */
        getSessionId: function() {
            let sessionId = sessionStorage.getItem('pdf_builder_session_id');
            if (!sessionId) {
                sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                sessionStorage.setItem('pdf_builder_session_id', sessionId);
            }
            return sessionId;
        }
    };

    // ==========================================
    // VALIDATION DES DONNÉES DE FORMULAIRE
    // ==========================================

    /**
     * Validation générique des données de formulaire
     * @param {Object} data - Données à valider
     * @param {Object} rules - Règles de validation (optionnel)
     * @returns {Array|Object} Tableau d'erreurs ou objet {isValid, errors}
     */
    window.validateFormData = function(data, rules) {
        // Si pas de règles, utiliser la validation par défaut pour PDF Builder
        if (!rules || typeof rules !== 'object') {
            return window.validatePDFFormData(data);
        }

        const result = {
            isValid: true,
            errors: {}
        };

        if (!data || typeof data !== 'object') {
            result.isValid = false;
            result.errors.general = ['Données invalides'];
            return result;
        }

        for (const [field, rule] of Object.entries(rules)) {
            const value = data[field];
            const fieldErrors = [];

            // Validation required
            if (rule.required && (value === undefined || value === null || value === '')) {
                fieldErrors.push('requis');
            }

            // Si la valeur est vide et non requise, passer au champ suivant
            if ((value === undefined || value === null || value === '') && !rule.required) {
                continue;
            }

            // Validation de type
            if (rule.type) {
                switch (rule.type) {
                    case 'string':
                        if (typeof value !== 'string') {
                            fieldErrors.push('doit être une chaîne de caractères');
                        }
                        break;
                    case 'number':
                        if (typeof value !== 'number' && isNaN(Number(value))) {
                            fieldErrors.push('doit être un nombre');
                        }
                        break;
                    case 'boolean':
                        if (typeof value !== 'boolean' && value !== 'true' && value !== 'false') {
                            fieldErrors.push('doit être un booléen');
                        }
                        break;
                    case 'array':
                        if (!Array.isArray(value)) {
                            fieldErrors.push('doit être un tableau');
                        }
                        break;
                    case 'email': {
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (typeof value !== 'string' || !emailRegex.test(value)) {
                            fieldErrors.push('doit être une adresse email valide');
                        }
                        break;
                    }
                }
            }

            // Validation de longueur pour les chaînes
            if (rule.type === 'string' || typeof value === 'string') {
                if (rule.minLength && value.length < rule.minLength) {
                    fieldErrors.push(`minimum ${rule.minLength} caractères`);
                }
                if (rule.maxLength && value.length > rule.maxLength) {
                    fieldErrors.push(`maximum ${rule.maxLength} caractères`);
                }
                if (rule.length && value.length !== rule.length) {
                    fieldErrors.push(`doit faire exactement ${rule.length} caractères`);
                }
            }

            // Validation numérique
            if (rule.type === 'number' || typeof value === 'number' || !isNaN(Number(value))) {
                const numValue = Number(value);
                if (rule.min !== undefined && numValue < rule.min) {
                    fieldErrors.push(`minimum ${rule.min}`);
                }
                if (rule.max !== undefined && numValue > rule.max) {
                    fieldErrors.push(`maximum ${rule.max}`);
                }
            }

            if (fieldErrors.length > 0) {
                result.errors[field] = fieldErrors;
                result.isValid = false;
            }
        }

        return result;
    };

    // Garder l'ancienne fonction pour compatibilité
    window.validatePDFFormData = function(formData) {
        const errors = [];

        if (!formData || typeof formData !== 'object') {
            errors.push('Données de formulaire invalides');
            return errors;
        }

        // Validation des champs numériques
        const numericFields = [
            'pdf_builder_cache_max_size',
            'pdf_builder_cache_ttl',
            'pdf_builder_max_execution_time',
            'pdf_builder_memory_limit'
        ];

        for (const field of numericFields) {
            if (formData[field] !== undefined && formData[field] !== '') {
                const value = parseInt(formData[field]);
                if (isNaN(value) || value < 0) {
                    errors.push(`${field.replace('pdf_builder_', '').replace(/_/g, ' ')} doit être un nombre positif`);
                }
            }
        }

        // Validation des champs email
        const emailFields = ['pdf_builder_company_email'];
        for (const field of emailFields) {
            if (formData[field] && formData[field].trim()) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(formData[field])) {
                    errors.push(`${field.replace('pdf_builder_', '').replace(/_/g, ' ')} doit être une adresse email valide`);
                }
            }
        }

        // Validation des champs URL
        const urlFields = ['pdf_builder_logo_url', 'pdf_builder_custom_css_url'];
        for (const field of urlFields) {
            if (formData[field] && formData[field].trim()) {
                try {
                    new URL(formData[field]);
                } catch (_e) {
                    errors.push(`${field.replace('pdf_builder_', '').replace(/_/g, ' ')} doit être une URL valide`);
                }
            }
        }

        // Validation des champs texte requis
        const requiredFields = ['pdf_builder_company_name'];
        for (const field of requiredFields) {
            if (!formData[field] || !formData[field].trim()) {
                errors.push(`${field.replace('pdf_builder_', '').replace(/_/g, ' ')} est obligatoire`);
            }
        }

        return errors;
    };

    // ==========================================
    // COMPATIBILITÉ AJAX
    // ==========================================

    /**
     * Compatibilité AJAX avec gestion d'erreurs
     */
    window.AjaxCompat = {
        cache: new Map(),
        lastRequestTime: 0,
        throttleDelay: 100, // 100ms entre requêtes

        /**
         * Réinitialise l'état d'AjaxCompat
         */
        reset: function() {
            this.cache.clear();
            this.lastRequestTime = 0;
        },

        /**
         * Requête AJAX générique avec retry et cache
         * @param {string} action - Action WordPress
         * @param {Object} data - Données à envoyer
         * @param {Object} options - Options (retries, cache, etc.)
         * @returns {Promise} Promesse de réponse
         */
        request: async function(action, data = {}, options = {}) {
            const cacheKey = options.cache !== false ? JSON.stringify({action, data}) : null;

            // Vérifier le cache
            if (cacheKey && this.cache.has(cacheKey)) {
                return this.cache.get(cacheKey);
            }

            // Rate limiting
            const now = Date.now();
            const timeSinceLastRequest = now - this.lastRequestTime;
            if (timeSinceLastRequest < this.throttleDelay) {
                await new Promise(resolve => setTimeout(resolve, this.throttleDelay - timeSinceLastRequest));
            }
            this.lastRequestTime = Date.now();

            const maxRetries = options.retries || 0;
            let lastError;

            for (let attempt = 0; attempt <= maxRetries; attempt++) {
                try {
                    const result = await this._executeRequest(action, data);

                    // Mettre en cache si demandé
                    if (cacheKey) {
                        this.cache.set(cacheKey, result);
                    }

                    return result;
                } catch (error) {
                    lastError = error;
                    if (attempt < maxRetries) {
                        // Attendre avant retry (exponential backoff)
                        await new Promise(resolve => setTimeout(resolve, Math.pow(2, attempt) * 1000));
                    }
                }
            }

            throw lastError;
        },

        /**
         * Exécute une requête HTTP
         * @private
         */
        _executeRequest: async function(action, data) {
            const operationId = 'ajax_' + action + '_' + Date.now();
            window.PerformanceMetrics.start(operationId);

            try {
                const formData = new FormData();
                formData.append('action', action);

                // Ajouter le nonce
                if (window.PDF_BUILDER_CONFIG && window.PDF_BUILDER_CONFIG.nonce) {
                    formData.append('nonce', window.PDF_BUILDER_CONFIG.nonce);
                }

                // Ajouter les données
                for (const [key, value] of Object.entries(data)) {
                    formData.append(key, value);
                }

                const response = await fetch(window.PDF_BUILDER_CONFIG.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const result = await response.json();

                window.PerformanceMetrics.end(operationId);
                return result;

            } catch (error) {
                window.PerformanceMetrics.error(operationId, error);
                throw error;
            }
        },

        /**
         * Fetch avec gestion d'erreurs améliorée
         * @param {string} url - URL à appeler
         * @param {Object} options - Options de fetch
         * @returns {Promise} Promesse de réponse
         */
        fetch: function(url, options = {}) {
            // Démarrer le tracking de performance
            const operationId = 'ajax_' + Date.now();
            window.PerformanceMetrics.start(operationId);

            return fetch(url, options)
                .then(response => {
                    window.PerformanceMetrics.end(operationId);

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }

                    return response;
                })
                .catch(error => {
                    window.PerformanceMetrics.error(operationId, error);
                    throw error;
                });
        },

        /**
         * Requête POST AJAX
         * @param {string} url - URL de destination
         * @param {Object} data - Données à envoyer
         * @returns {Promise} Promesse de réponse JSON
         */
        post: function(url, data) {
            const formData = new FormData();

            // Ajouter le nonce si disponible
            if (window.PDF_BUILDER_CONFIG && window.PDF_BUILDER_CONFIG.nonce) {
                formData.append('nonce', window.PDF_BUILDER_CONFIG.nonce);
            }

            // Ajouter les données
            for (const key in data) {
                formData.append(key, data[key]);
            }

            return this.fetch(url, {
                method: 'POST',
                body: formData
            }).then(response => response.json());
        },

        /**
         * Requête GET AJAX
         * @param {string} url - URL avec paramètres
         * @returns {Promise} Promesse de réponse JSON
         */
        get: function(url) {
            return this.fetch(url)
                .then(response => response.json());
        }
    };

    // ==========================================
    // INITIALISATION
    // ==========================================

    // Initialiser les utilitaires quand le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initializeUtilities();
        });
    } else {
        initializeUtilities();
    }

    function initializeUtilities() {
        // Log d'initialisation si debug activé
        if (window.PDF_BUILDER_CONFIG && window.PDF_BUILDER_CONFIG.debug) {
            console.log('PDF Builder Utilities initialized');
        }

        // Nettoyer le cache expiré au démarrage
        window.LocalCache.load(); // Cela nettoiera automatiquement le cache expiré
    }

})(window, document);