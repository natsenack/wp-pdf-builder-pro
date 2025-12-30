/**
 * Système de monitoring et synchronisation des cartes canvas
 * Assure la cohérence entre toutes les cartes et les modals
 * Version: 1.0.1 - 10/12/2025 - Cache Fix Applied
 */

window.CanvasCardMonitor = {
    // État du système
    state: {
        initialized: false,
        cards: {},
        modals: {},
        settings: {},
        lastSync: null,
        errors: [],
        warnings: []
    },

    // Configuration du monitoring
    config: {
        autoSync: true,
        syncInterval: 5000, // 5 secondes
        logLevel: 'INFO', // DEBUG, INFO, WARN, ERROR
        enableValidation: true,
        enableAutoFix: false
    },

    // Initialisation du système de monitoring
    init: function() {
        if (this.state.initialized) {
            this.log('INFO', 'CanvasCardMonitor déjà initialisé');
            return;
        }

        this.log('INFO', 'Initialisation du système de monitoring des cartes canvas');

        try {
            // Charger les paramètres depuis les inputs hidden
            this.loadSettingsFromDOM();

            // Découvrir toutes les cartes
            this.discoverCards();

            // Découvrir tous les modals
            this.discoverModals();

            // Valider la cohérence initiale
            this.validateConsistency();

            // Vérifier la persistance des données au chargement
            this.validateDataPersistence();

            // Configurer les écouteurs d'événements
            this.setupEventListeners();

            // Démarrer la synchronisation automatique si activée
            if (this.config.autoSync) {
                this.startAutoSync();
            }

            this.state.initialized = true;
            this.state.lastSync = new Date();

            this.log('INFO', 'Système de monitoring initialisé avec succès');

        } catch (error) {
            this.log('ERROR', 'Erreur lors de l\'initialisation:', error);
            this.state.errors.push({
                type: 'INIT_ERROR',
                message: error.message,
                timestamp: new Date()
            });
        }
    },

    // Charger les paramètres depuis le DOM (inputs hidden)
    loadSettingsFromDOM: function() {
        this.log('DEBUG', 'Chargement des paramètres depuis le DOM');

        const settingsInputs = document.querySelectorAll('input[name^="pdf_builder_settings[pdf_builder_canvas_"]');
        this.state.settings = {};

        settingsInputs.forEach(input => {
            const name = input.name;
            const value = input.value;

            // Extraire la clé du paramètre
            const keyMatch = name.match(/pdf_builder_settings\[pdf_builder_canvas_(.+)\]/);
            if (keyMatch) {
                const key = 'pdf_builder_canvas_' + keyMatch[1];
                this.state.settings[key] = value;
                this.log('DEBUG', `Paramètre chargé: ${key} = ${value}`);
            }
        });

        this.log('INFO', `${Object.keys(this.state.settings).length} paramètres chargés`);
    },

    // Découvrir toutes les cartes canvas
    discoverCards: function() {
        this.log('DEBUG', 'Découverte des cartes canvas');

        const cards = document.querySelectorAll('.canvas-card');
        this.state.cards = {};

        cards.forEach(card => {
            const category = card.dataset.category;
            if (category) {
                this.state.cards[category] = {
                    element: card,
                    category: category,
                    lastUpdate: null,
                    values: {},
                    status: 'unknown'
                };
                this.log('DEBUG', `Carte découverte: ${category}`);
            }
        });

        this.log('INFO', `${Object.keys(this.state.cards).length} cartes découvertes`);
    },

    // Découvrir tous les modals
    discoverModals: function() {
        this.log('DEBUG', 'Découverte des modals');

        const modalSelectors = [
            '#dimensions-modal',
            '#apparence-modal',
            '#zoom-modal',
            '#grille-modal',
            '#interactions-modal',
            '#export-modal',
            '#performance-modal',
            '#debug-modal'
        ];

        this.state.modals = {};

        modalSelectors.forEach(selector => {
            const modal = document.querySelector(selector);
            if (modal) {
                const category = selector.replace('#', '').replace('-modal', '');
                this.state.modals[category] = {
                    element: modal,
                    category: category,
                    inputs: {},
                    lastUpdate: null,
                    status: 'unknown'
                };

                // Découvrir les inputs du modal
                const inputs = modal.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    if (input.name && input.name.startsWith('pdf_builder_canvas_')) {
                        const key = input.name.replace('pdf_builder_canvas_', '');
                        this.state.modals[category].inputs[key] = input;
                    }
                });

                this.log('DEBUG', `Modal découvert: ${category} (${Object.keys(this.state.modals[category].inputs).length} inputs)`);
            }
        });

        this.log('INFO', `${Object.keys(this.state.modals).length} modals découverts`);
    },

    // Valider la cohérence entre cartes, modals et paramètres
    validateConsistency: function() {
        this.log('INFO', 'Validation de la cohérence du système');

        let inconsistencies = 0;

        // Pour chaque carte, vérifier la cohérence
        Object.keys(this.state.cards).forEach(category => {
            const card = this.state.cards[category];
            const modal = this.state.modals[category];

            // Vérifier si le modal existe
            if (!modal) {
                this.log('WARN', `Carte ${category} n'a pas de modal correspondant`);
                this.state.warnings.push({
                    type: 'MISSING_MODAL',
                    category: category,
                    message: `Aucun modal trouvé pour la carte ${category}`,
                    timestamp: new Date()
                });
                return;
            }

            // Comparer les valeurs affichées dans la carte avec les paramètres
            const cardValues = this.getCardDisplayedValues(card);
            const expectedValues = this.getExpectedValuesForCard(category);

            // Vérifier les incohérences
            Object.keys(expectedValues).forEach(key => {
                const expected = expectedValues[key];
                const displayed = cardValues[key];

                if (expected !== undefined && displayed !== undefined && expected != displayed) {
                    inconsistencies++;
                    this.log('WARN', `Incohérence détectée pour ${category}.${key}: attendu=${expected}, affiché=${displayed}`);

                    this.state.warnings.push({
                        type: 'VALUE_INCONSISTENCY',
                        category: category,
                        key: key,
                        expected: expected,
                        displayed: displayed,
                        timestamp: new Date()
                    });
                }
            });

            card.status = inconsistencies > 0 ? 'inconsistent' : 'consistent';
        });

        this.log('INFO', `Validation terminée: ${inconsistencies} incohérences détectées`);
        return inconsistencies === 0;
    },

    // Vérifier la persistance des données au chargement de la page
    validateDataPersistence: function() {
        this.log('INFO', 'Vérification de la persistance des données au chargement');

        let persistenceIssues = 0;

        // 1. Vérifier que tous les inputs hidden ont des valeurs
        const requiredSettings = [
            'pdf_builder_canvas_width',
            'pdf_builder_canvas_height',
            'pdf_builder_canvas_dpi',
            'pdf_builder_canvas_bg_color',
            'pdf_builder_canvas_border_color',
            'pdf_builder_canvas_border_width',
            'pdf_builder_canvas_shadow_enabled',
            'pdf_builder_canvas_grid_enabled',
            'pdf_builder_canvas_grid_size',
            'pdf_builder_canvas_guides_enabled',
            'pdf_builder_canvas_snap_to_grid',
            'pdf_builder_canvas_zoom_min',
            'pdf_builder_canvas_zoom_max',
            'pdf_builder_canvas_zoom_default',
            'pdf_builder_canvas_zoom_step'
        ];

        requiredSettings.forEach(settingKey => {
            const value = this.state.settings[settingKey];
            if (value === undefined || value === null || value === '') {
                persistenceIssues++;
                this.log('ERROR', `Paramètre manquant ou vide: ${settingKey}`);
                this.state.errors.push({
                    type: 'MISSING_SETTING',
                    key: settingKey,
                    message: `Le paramètre ${settingKey} n'a pas de valeur au chargement`,
                    timestamp: new Date()
                });
            } else {
                this.log('DEBUG', `Paramètre OK: ${settingKey} = ${value}`);
            }
        });

        // 2. Vérifier que les aperçus correspondent aux valeurs des inputs hidden
        Object.keys(this.state.cards).forEach(category => {
            const card = this.state.cards[category];
            const displayedValues = this.getCardDisplayedValues(card);
            const expectedValues = this.getExpectedValuesForCard(category);

            Object.keys(expectedValues).forEach(key => {
                const expected = expectedValues[key];
                const displayed = displayedValues[key];

                if (expected !== undefined && displayed !== undefined) {
                    if (expected != displayed) {
                        persistenceIssues++;
                        this.log('ERROR', `Persistance défaillante pour ${category}.${key}: input=${expected}, affiché=${displayed}`);
                        this.state.errors.push({
                            type: 'PERSISTENCE_MISMATCH',
                            category: category,
                            key: key,
                            expected: expected,
                            displayed: displayed,
                            message: `L'aperçu ne correspond pas à la valeur sauvegardée pour ${category}.${key}`,
                            timestamp: new Date()
                        });
                    } else {
                        this.log('DEBUG', `Persistance OK pour ${category}.${key}: ${displayed}`);
                    }
                }
            });
        });

        // 3. Vérifier la cohérence avec la base de données via AJAX (optionnel)
        this.verifyDatabaseConsistency();

        this.log('INFO', `Vérification de persistance terminée: ${persistenceIssues} problèmes détectés`);
        return persistenceIssues === 0;
    },

    // Vérifier la cohérence avec la base de données
    verifyDatabaseConsistency: function() {
        if (!window.jQuery || !window.ajaxurl) {
            this.log('DEBUG', 'AJAX non disponible pour vérification base de données');
            return;
        }

        this.log('DEBUG', 'Vérification de cohérence avec la base de données');

        window.jQuery.ajax({
            url: window.ajaxurl,
            type: 'POST',
            data: {
                action: 'verify_canvas_settings_consistency',
                nonce: window.pdfBuilderSettings?.nonce || ''
            },
            success: (response) => {
                if (response.success) {
                    const dbValues = response.data;
                    let dbInconsistencies = 0;

                    // Comparer les valeurs DOM avec celles de la DB
                    Object.keys(this.state.settings).forEach(key => {
                        const domValue = this.state.settings[key];
                        const dbValue = dbValues[key];

                        if (dbValue !== undefined && domValue != dbValue) {
                            dbInconsistencies++;
                            this.log('WARN', `Incohérence DB-DOM: ${key} (DOM: ${domValue}, DB: ${dbValue})`);
                            this.state.warnings.push({
                                type: 'DB_DOM_INCONSISTENCY',
                                key: key,
                                domValue: domValue,
                                dbValue: dbValue,
                                message: `Valeur DOM différente de la base de données pour ${key}`,
                                timestamp: new Date()
                            });
                        }
                    });

                    this.log('INFO', `Vérification DB terminée: ${dbInconsistencies} incohérences détectées`);
                } else {
                    this.log('WARN', 'Échec de la vérification de cohérence DB:', response.data);
                }
            },
            error: (xhr, status, error) => {
                this.log('WARN', 'Erreur AJAX lors de la vérification DB:', error);
            }
        });
    },

    // Obtenir les valeurs affichées dans une carte
    getCardDisplayedValues: function(card) {
        const values = {};

        // Dimensions
        if (card.category === 'dimensions') {
            const widthEl = card.element.querySelector('#card-canvas-width');
            const heightEl = card.element.querySelector('#card-canvas-height');
            const dpiEl = card.element.querySelector('#card-canvas-dpi');

            if (widthEl) values.width = parseInt(widthEl.textContent) || 0;
            if (heightEl) values.height = parseInt(heightEl.textContent) || 0;
            if (dpiEl) {
                const dpiMatch = dpiEl.textContent.match(/(\d+)\s*DPI/);
                if (dpiMatch) values.dpi = parseInt(dpiMatch[1]);
            }
        }

        // Apparence
        if (card.category === 'apparence') {
            // Les valeurs d'apparence sont plus difficiles à extraire visuellement
            // On se base sur les paramètres sauvegardés
        }

        return values;
    },

    // Obtenir les valeurs attendues pour une carte
    getExpectedValuesForCard: function(category) {
        const values = {};

        if (category === 'dimensions') {
            values.width = parseInt(this.state.settings.pdf_builder_canvas_width) || 794;
            values.height = parseInt(this.state.settings.pdf_builder_canvas_height) || 1123;
            values.dpi = parseInt(this.state.settings.pdf_builder_canvas_dpi) || 96;
        }

        return values;
    },

    // Configurer les écouteurs d'événements
    setupEventListeners: function() {
        this.log('DEBUG', 'Configuration des écouteurs d\'événements');

        // Écouter les changements dans les modals
        Object.values(this.state.modals).forEach(modal => {
            Object.values(modal.inputs).forEach(input => {
                input.addEventListener('change', (e) => {
                    this.log('DEBUG', `Changement détecté dans ${modal.category}: ${input.name} = ${input.value}`);
                    modal.lastUpdate = new Date();
                    this.onModalValueChanged(modal.category, input.name, input.value);
                });
            });
        });

        // Écouter les sauvegardes AJAX
        if (window.jQuery) {
            window.jQuery(document).on('ajaxComplete', (event, xhr, settings) => {
                if (settings.url && settings.url.includes('admin-ajax.php') && settings.data && settings.data.includes('action=save_canvas_settings')) {
                    this.log('INFO', 'Sauvegarde AJAX détectée, revalidation dans 2 secondes');
                    setTimeout(() => {
                        this.loadSettingsFromDOM();
                        this.validateConsistency();
                        this.syncAllCards();
                    }, 2000);
                }
            });
        }

        // Surveiller les changements en temps réel dans les cartes
        this.setupRealtimeCardMonitoring();

        this.log('INFO', 'Écouteurs d\'événements configurés');
    },

    // Configurer la surveillance en temps réel des cartes
    setupRealtimeCardMonitoring: function() {
        this.log('DEBUG', 'Configuration de la surveillance temps réel des cartes');

        // Utiliser MutationObserver pour surveiller les changements dans les cartes
        Object.values(this.state.cards).forEach(card => {
            this.setupCardObserver(card);
        });

        // Surveiller également les changements dans les éléments spécifiques des cartes
        this.setupSpecificElementMonitoring();

        this.log('INFO', 'Surveillance temps réel des cartes configurée');
    },

    // Configurer un observateur pour une carte spécifique
    setupCardObserver: function(card) {
        const observer = new MutationObserver((mutations) => {
            let hasChanged = false;
            const changes = [];

            mutations.forEach(mutation => {
                if (mutation.type === 'childList' || mutation.type === 'characterData' || mutation.type === 'attributes') {
                    hasChanged = true;
                    changes.push({
                        type: mutation.type,
                        target: mutation.target,
                        attribute: mutation.attributeName,
                        oldValue: mutation.oldValue,
                        newValue: mutation.target.textContent || mutation.target.value
                    });
                }
            });

            if (hasChanged) {
                this.log('DEBUG', `Changement temps réel détecté dans carte ${card.category}`);
                card.lastUpdate = new Date();
                this.onCardRealtimeChange(card.category, changes);
            }
        });

        // Observer les changements dans la carte
        observer.observe(card.element, {
            childList: true,
            subtree: true,
            characterData: true,
            attributes: true,
            attributeOldValue: true,
            characterDataOldValue: true
        });

        // Stocker l'observateur pour pouvoir le nettoyer plus tard
        card.observer = observer;
    },

    // Surveiller des éléments spécifiques qui changent souvent
    setupSpecificElementMonitoring: function() {
        const elementsToMonitor = [
            '#card-canvas-width',
            '#card-canvas-height',
            '#card-canvas-dpi',
            '.metric-value',
            '.status-indicator',
            '.progress-fill',
            '.performance-fill'
        ];

        elementsToMonitor.forEach(selector => {
            const element = document.querySelector(selector);
            if (element) {
                // Créer un observateur spécifique pour cet élément
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach(mutation => {
                        if (mutation.type === 'characterData' || (mutation.type === 'attributes' && mutation.attributeName === 'style')) {
                            const category = this.getCategoryFromElement(element);
                            if (category) {
                                this.log('DEBUG', `Changement spécifique détecté: ${selector} dans ${category}`);
                                this.onSpecificElementChange(category, selector, element);
                            }
                        }
                    });
                });

                observer.observe(element, {
                    characterData: true,
                    attributes: true,
                    attributeFilter: ['style', 'class'],
                    characterDataOldValue: true
                });

                this.log('DEBUG', `Surveillance configurée pour ${selector}`);
            }
        });
    },

    // Déterminer la catégorie d'une carte à partir d'un élément
    getCategoryFromElement: function(element) {
        let currentElement = element;
        while (currentElement && currentElement !== document.body) {
            if (currentElement.classList && currentElement.classList.contains('canvas-card')) {
                return currentElement.dataset.category;
            }
            currentElement = currentElement.parentElement;
        }
        return null;
    },

    // Gestionnaire de changement en temps réel dans une carte
    onCardRealtimeChange: function(category, changes) {
        this.log('INFO', `Changement temps réel dans carte ${category}: ${changes.length} mutations`);

        // Mettre à jour l'état de la carte
        if (this.state.cards[category]) {
            this.state.cards[category].lastRealtimeChange = new Date();
            this.state.cards[category].realtimeChanges = changes;
        }

        // Vérifier la cohérence après un court délai
        if (this.realtimeValidationTimeout) {
            clearTimeout(this.realtimeValidationTimeout);
        }

        this.realtimeValidationTimeout = setTimeout(() => {
            this.validateCardConsistency(category);
        }, 500);
    },

    // Gestionnaire de changement dans un élément spécifique
    onSpecificElementChange: function(category, selector, element) {
        const value = element.textContent || element.value || element.style.width;
        this.log('DEBUG', `Élément ${selector} changé dans ${category}: ${value}`);

        // Mettre à jour les valeurs en cache de la carte
        if (this.state.cards[category]) {
            if (!this.state.cards[category].currentValues) {
                this.state.cards[category].currentValues = {};
            }
            this.state.cards[category].currentValues[selector] = value;
        }
    },

    // Valider la cohérence d'une carte spécifique
    validateCardConsistency: function(category) {
        const card = this.state.cards[category];
        if (!card) return;

        const displayedValues = this.getCardDisplayedValues(card);
        const expectedValues = this.getExpectedValuesForCard(category);

        let inconsistencies = 0;
        Object.keys(expectedValues).forEach(key => {
            const expected = expectedValues[key];
            const displayed = displayedValues[key];

            if (expected !== undefined && displayed !== undefined && expected != displayed) {
                inconsistencies++;
                this.log('WARN', `Incohérence temps réel dans ${category}.${key}: attendu=${expected}, affiché=${displayed}`);
            }
        });

        card.status = inconsistencies > 0 ? 'inconsistent' : 'consistent';

        if (inconsistencies > 0) {
            this.state.warnings.push({
                type: 'REALTIME_INCONSISTENCY',
                category: category,
                message: `${inconsistencies} incohérences détectées en temps réel`,
                timestamp: new Date()
            });
        }
    },

    // Gestionnaire de changement de valeur dans un modal
    onModalValueChanged: function(category, inputName, value) {
        this.log('DEBUG', `Valeur changée: ${category}.${inputName} = ${value}`);

        // Marquer le modal comme modifié
        if (this.state.modals[category]) {
            this.state.modals[category].lastUpdate = new Date();
        }

        // Si la synchronisation automatique est activée, mettre à jour la carte correspondante
        if (this.config.autoSync) {
            this.syncCard(category);
        }
    },

    // Synchroniser une carte spécifique
    syncCard: function(category) {
        const card = this.state.cards[category];
        if (!card) {
            this.log('WARN', `Carte ${category} introuvable pour synchronisation`);
            return;
        }

        this.log('DEBUG', `Synchronisation de la carte ${category}`);

        try {
            // Recharger les paramètres
            this.loadSettingsFromDOM();

            // Mettre à jour la carte selon sa catégorie
            switch (category) {
                case 'dimensions':
                    this.updateDimensionsCard();
                    break;
                case 'apparence':
                    this.updateApparenceCard();
                    break;
                default:
                    this.log('DEBUG', `Pas de fonction de mise à jour spécifique pour ${category}`);
            }

            card.lastUpdate = new Date();
            card.status = 'synced';

            this.log('INFO', `Carte ${category} synchronisée`);

        } catch (error) {
            this.log('ERROR', `Erreur lors de la synchronisation de ${category}:`, error);
            card.status = 'error';
        }
    },

    // Synchroniser toutes les cartes
    syncAllCards: function() {
        this.log('INFO', 'Synchronisation de toutes les cartes');

        Object.keys(this.state.cards).forEach(category => {
            this.syncCard(category);
        });

        this.state.lastSync = new Date();
        this.validateConsistency();
    },

    // Démarrer la synchronisation automatique
    startAutoSync: function() {
        this.log('INFO', `Démarrage de la synchronisation automatique (intervalle: ${this.config.syncInterval}ms)`);

        this.autoSyncInterval = setInterval(() => {
            this.syncAllCards();
        }, this.config.syncInterval);
    },

    // Arrêter la synchronisation automatique
    stopAutoSync: function() {
        if (this.autoSyncInterval) {
            clearInterval(this.autoSyncInterval);
            this.autoSyncInterval = null;
            this.log('INFO', 'Synchronisation automatique arrêtée');
        }
    },

    // Fonctions de mise à jour spécifiques aux cartes
    updateDimensionsCard: function() {
        const width = this.state.settings.pdf_builder_canvas_width || 794;
        const height = this.state.settings.pdf_builder_canvas_height || 1123;
        const dpi = this.state.settings.pdf_builder_canvas_dpi || 96;
        const format = this.state.settings.pdf_builder_canvas_format || 'A4';

        // Mettre à jour les éléments d'affichage
        const widthEl = document.getElementById('card-canvas-width');
        const heightEl = document.getElementById('card-canvas-height');
        const dpiEl = document.getElementById('card-canvas-dpi');

        if (widthEl) widthEl.textContent = width;
        if (heightEl) heightEl.textContent = height;
        if (dpiEl) dpiEl.textContent = `${dpi} DPI - ${format} (calcul automatique)`;

        this.log('DEBUG', `Carte dimensions mise à jour: ${width}x${height}px, ${dpi} DPI`);
    },

    updateApparenceCard: function() {
        // Mise à jour de la carte apparence si nécessaire
        this.log('DEBUG', 'Carte apparence mise à jour');
    },

    // Système de logging
    log: function(level, message, ...args) {
        const levels = ['DEBUG', 'INFO', 'WARN', 'ERROR'];
        const levelIndex = levels.indexOf(level.toUpperCase());

        if (levelIndex < 0 || levelIndex < levels.indexOf(this.config.logLevel)) {
            return;
        }

        const timestamp = new Date().toISOString();
        const formattedMessage = `[${timestamp}] [${level}] ${message}`;

        // Afficher dans la console
        console.log(formattedMessage, ...args);

        // Stocker les erreurs et avertissements
        if (level === 'ERROR') {
            this.state.errors.push({
                level: level,
                message: formattedMessage,
                args: args,
                timestamp: new Date()
            });
        } else if (level === 'WARN') {
            this.state.warnings.push({
                level: level,
                message: formattedMessage,
                args: args,
                timestamp: new Date()
            });
        }
    },

    // Obtenir le statut du système
    getStatus: function() {
        const realtimeStats = this.getRealtimeStats();

        return {
            initialized: this.state.initialized,
            cardsCount: Object.keys(this.state.cards).length,
            modalsCount: Object.keys(this.state.modals).length,
            settingsCount: Object.keys(this.state.settings).length,
            lastSync: this.state.lastSync,
            errorsCount: this.state.errors.length,
            warningsCount: this.state.warnings.length,
            autoSyncActive: !!this.autoSyncInterval,
            realtimeMonitoring: {
                active: true,
                totalChanges: realtimeStats.totalChanges,
                lastChange: realtimeStats.lastChange,
                mostActiveCard: realtimeStats.mostActiveCard
            }
        };
    },

    // Obtenir les statistiques temps réel
    getRealtimeStats: function() {
        let totalChanges = 0;
        let lastChange = null;
        let mostActiveCard = null;
        let maxChanges = 0;

        Object.values(this.state.cards).forEach(card => {
            if (card.realtimeChanges) {
                const changes = card.realtimeChanges.length;
                totalChanges += changes;

                if (changes > maxChanges) {
                    maxChanges = changes;
                    mostActiveCard = card.category;
                }
            }

            if (card.lastRealtimeChange && (!lastChange || card.lastRealtimeChange > lastChange)) {
                lastChange = card.lastRealtimeChange;
            }
        });

        return {
            totalChanges: totalChanges,
            lastChange: lastChange,
            mostActiveCard: mostActiveCard
        };
    },

    // Obtenir les erreurs récentes
    getRecentErrors: function(limit = 10) {
        return this.state.errors.slice(-limit);
    },

    // Obtenir les avertissements récents
    getRecentWarnings: function(limit = 10) {
        return this.state.warnings.slice(-limit);
    },

    // Forcer une resynchronisation complète
    forceResync: function() {
        this.log('INFO', 'Resynchronisation forcée demandée');
        this.loadSettingsFromDOM();
        this.syncAllCards();
    },

    // Obtenir les détails des changements temps réel
    getRealtimeChanges: function(limit = 10) {
        const changes = [];

        Object.values(this.state.cards).forEach(card => {
            if (card.realtimeChanges && card.lastRealtimeChange) {
                changes.push({
                    category: card.category,
                    timestamp: card.lastRealtimeChange,
                    changesCount: card.realtimeChanges.length,
                    lastChanges: card.realtimeChanges.slice(-3) // Derniers 3 changements
                });
            }
        });

        // Trier par timestamp décroissant
        changes.sort((a, b) => b.timestamp - a.timestamp);

        return changes.slice(0, limit);
    },

    // Nettoyer le système
    destroy: function() {
        this.stopAutoSync();

        // Nettoyer les observateurs
        Object.values(this.state.cards).forEach(card => {
            if (card.observer) {
                card.observer.disconnect();
            }
        });

        // Nettoyer les timeouts
        if (this.realtimeValidationTimeout) {
            clearTimeout(this.realtimeValidationTimeout);
        }

        this.state.initialized = false;
        this.log('INFO', 'Système de monitoring détruit');
    }
};

// Initialisation automatique quand le DOM est prêt
document.addEventListener('DOMContentLoaded', function() {
    // Attendre un peu que tous les éléments soient chargés
    setTimeout(() => {
        window.CanvasCardMonitor.init();
    }, 1000);
});

// Exposition globale pour débogage
window.CanvasCardMonitorDebug = {
    getStatus: () => window.CanvasCardMonitor.getStatus(),
    getErrors: () => window.CanvasCardMonitor.getRecentErrors(),
    getWarnings: () => window.CanvasCardMonitor.getRecentWarnings(),
    getRealtimeChanges: () => window.CanvasCardMonitor.getRealtimeChanges(),
    getRealtimeStats: () => window.CanvasCardMonitor.getRealtimeStats(),
    forceResync: () => window.CanvasCardMonitor.forceResync(),
    setLogLevel: (level) => { window.CanvasCardMonitor.config.logLevel = level; },
    toggleAutoSync: () => {
        if (window.CanvasCardMonitor.autoSyncInterval) {
            window.CanvasCardMonitor.stopAutoSync();
        } else {
            window.CanvasCardMonitor.startAutoSync();
        }
    },
    validateNow: () => {
        window.CanvasCardMonitor.loadSettingsFromDOM();
        return window.CanvasCardMonitor.validateConsistency();
    },
    getCardDetails: (category) => {
        const card = window.CanvasCardMonitor.state.cards[category];
        return card ? {
            category: card.category,
            status: card.status,
            lastUpdate: card.lastUpdate,
            lastRealtimeChange: card.lastRealtimeChange,
            realtimeChangesCount: card.realtimeChanges ? card.realtimeChanges.length : 0,
            currentValues: card.currentValues
        } : null;
    },
    getStatus: () => {
        return {
            initialized: window.CanvasCardMonitor.state.initialized,
            cards: Object.keys(window.CanvasCardMonitor.state.cards).map(cat => ({
                category: cat,
                status: window.CanvasCardMonitor.state.cards[cat].status,
                lastUpdate: window.CanvasCardMonitor.state.cards[cat].lastUpdate,
                errors: window.CanvasCardMonitor.state.cards[cat].errors || []
            })),
            modals: Object.keys(window.CanvasCardMonitor.state.modals),
            lastSync: window.CanvasCardMonitor.state.lastSync,
            errors: window.CanvasCardMonitor.state.errors,
            warnings: window.CanvasCardMonitor.state.warnings
        };
    }
};

// === END OF CANVAS CARD MONITOR ===
// This file should end here. Any content after this line is corrupted cache data.
// Version: 1.0.1 - Cache Fix Applied - 11/12/2025
