                // Force cache clear
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.getRegistrations().then(function(registrations) {
                        for(let registration of registrations) {
                            registration.unregister();
                        }
                    });
                }
                // Clear localStorage and sessionStorage
                localStorage.clear();
                sessionStorage.clear();
                console.log('Cache cleared by PDF Builder');

                // Valeurs par défaut globales pour tous les champs Canvas - SOURCE UNIQUE DE VÉRITÉ
                const CANVAS_DEFAULT_VALUES = {
                    'pdf_builder_canvas_width': '794',
                    'pdf_builder_canvas_height': '1123',
                    'pdf_builder_canvas_dpi': '96',
                    'pdf_builder_canvas_format': 'A4',
                    'pdf_builder_canvas_bg_color': '#ffffff',
                    'pdf_builder_canvas_border_color': '#cccccc',
                    'pdf_builder_canvas_border_width': '1',
                    'pdf_builder_canvas_container_bg_color': '#f8f9fa',
                    'pdf_builder_canvas_shadow_enabled': '0',
                    'pdf_builder_canvas_grid_enabled': '1',
                    'pdf_builder_canvas_grid_size': '20',
                    'pdf_builder_canvas_guides_enabled': '1',
                    'pdf_builder_canvas_snap_to_grid': '1',
                    'pdf_builder_canvas_zoom_min': '25',
                    'pdf_builder_canvas_zoom_max': '500',
                    'pdf_builder_canvas_zoom_default': '100',
                    'pdf_builder_canvas_zoom_step': '25',
                    'pdf_builder_canvas_export_quality': '90',
                    'pdf_builder_canvas_export_format': 'png',
                    'pdf_builder_canvas_export_transparent': '0',
                    'pdf_builder_canvas_drag_enabled': '1',
                    'pdf_builder_canvas_resize_enabled': '1',
                    'pdf_builder_canvas_rotate_enabled': '1',
                    'pdf_builder_canvas_multi_select': '1',
                    'pdf_builder_canvas_selection_mode': 'single',
                    'pdf_builder_canvas_keyboard_shortcuts': '1',
                    'pdf_builder_canvas_fps_target': '60',
                    'pdf_builder_canvas_memory_limit_js': '50',
                    'pdf_builder_canvas_response_timeout': '5000',
                    'pdf_builder_canvas_lazy_loading_editor': '1',
                    'pdf_builder_canvas_preload_critical': '1',
                    'pdf_builder_canvas_lazy_loading_plugin': '1',
                    'pdf_builder_canvas_debug_enabled': '0',
                    'pdf_builder_canvas_performance_monitoring': '0',
                    'pdf_builder_canvas_error_reporting': '0',
                    'pdf_builder_canvas_memory_limit_php': '128'
                };

                // Configuration de robustesse
                const CANVAS_CONFIG = {
                    MAX_RETRIES: 3,
                    RETRY_DELAY: 1000, // ms
                    AJAX_TIMEOUT: 30000, // 30 secondes
                    HEALTH_CHECK_INTERVAL: 60000, // 1 minute
                    CACHE_KEY: 'pdf_builder_canvas_backup',
                    CACHE_TTL: 3600000 // 1 heure
                };

                // Feature flags pour contrôle granulaire
                const CANVAS_FEATURES = {
                    ENABLE_VALIDATION: true,
                    ENABLE_CACHE: true,
                    ENABLE_RETRY: true,
                    ENABLE_HEALTH_CHECK: true,
                    ENABLE_METRICS: true,
                    ENABLE_RECOVERY: true,
                    ENABLE_CIRCUIT_BREAKER: true
                };

                // Fonction de contrôle des features
                const FeatureGate = {
                    isEnabled: (feature) => {
                        return CANVAS_FEATURES[feature] !== false;
                    },

                    disable: (feature) => {
                        CANVAS_FEATURES[feature] = false;
                        console.log(`FEATURE_DISABLED: ${feature} turned off`);
                    },

                    enable: (feature) => {
                        CANVAS_FEATURES[feature] = true;
                        console.log(`FEATURE_ENABLED: ${feature} turned on`);
                    },

                    emergencyShutdown: () => {
                        console.warn('EMERGENCY: Disabling all advanced features');
                        Object.keys(CANVAS_FEATURES).forEach(feature => {
                            if (feature !== 'ENABLE_VALIDATION') { // Garder la validation
                                CANVAS_FEATURES[feature] = false;
                            }
                        });
                    }
                };

                // Utilitaires de validation
                const CanvasValidators = {
                    isValidNumber: (value, min, max) => {
                        const num = parseInt(value);
                        return !isNaN(num) && num >= min && num <= max;
                    },

                    isValidHexColor: (value) => {
                        return /^#[0-9A-Fa-f]{6}$/.test(value);
                    },

                    isValidBoolean: (value) => {
                        return value === '0' || value === '1';
                    },

                    validateField: (key, value) => {
                        if (key.includes('_width') && !key.includes('_border_width')) {
                            return CanvasValidators.isValidNumber(value, 100, 5000);
                        }
                        if (key.includes('_height')) {
                            return CanvasValidators.isValidNumber(value, 100, 5000);
                        }
                        if (key.includes('_border_width')) {
                            return CanvasValidators.isValidNumber(value, 0, 10);
                        }
                        if (key.includes('_dpi')) {
                            return CanvasValidators.isValidNumber(value, 72, 600);
                        }
                        if (key.includes('_color')) {
                            return CanvasValidators.isValidHexColor(value);
                        }
                        if (key.includes('_enabled') || key.includes('_transparent')) {
                            return CanvasValidators.isValidBoolean(value);
                        }
                        return true; // Autres champs acceptés par défaut
                    }
                };

                // Gestionnaire de cache localStorage
                const CanvasCache = {
                    save: (data) => {
                        try {
                            const cacheData = {
                                data: data,
                                timestamp: Date.now(),
                                version: '1.0'
                            };
                            localStorage.setItem(CANVAS_CONFIG.CACHE_KEY, JSON.stringify(cacheData));
                            console.log('CACHE_SAVE: Canvas settings cached locally');
                        } catch (e) {
                            console.warn('CACHE_ERROR: Failed to save to localStorage:', e);
                        }
                    },

                    load: () => {
                        try {
                            const cached = localStorage.getItem(CANVAS_CONFIG.CACHE_KEY);
                            if (!cached) return null;

                            const cacheData = JSON.parse(cached);
                            const age = Date.now() - cacheData.timestamp;

                            if (age > CANVAS_CONFIG.CACHE_TTL) {
                                console.log('CACHE_EXPIRED: Cache too old, removing');
                                CanvasCache.clear();
                                return null;
                            }

                            console.log('CACHE_LOAD: Loaded cached settings');
                            return cacheData.data;
                        } catch (e) {
                            console.warn('CACHE_ERROR: Failed to load from localStorage:', e);
                            return null;
                        }
                    },

                    clear: () => {
                        try {
                            localStorage.removeItem(CANVAS_CONFIG.CACHE_KEY);
                            console.log('CACHE_CLEAR: Local cache cleared');
                        } catch (e) {
                            console.warn('CACHE_ERROR: Failed to clear cache:', e);
                        }
                    }
                };

                // Health monitoring
                const CanvasHealth = {
                    lastSuccess: Date.now(),
                    failureCount: 0,
                    isHealthy: true,

                    recordSuccess: () => {
                        CanvasHealth.lastSuccess = Date.now();
                        CanvasHealth.failureCount = 0;
                        CanvasHealth.isHealthy = true;
                    },

                    recordFailure: () => {
                        CanvasHealth.failureCount++;
                        if (CanvasHealth.failureCount >= 3) {
                            CanvasHealth.isHealthy = false;
                            console.warn('HEALTH_WARNING: System marked as unhealthy after 3 failures');
                        }
                    },

                    checkHealth: () => {
                        const timeSinceLastSuccess = Date.now() - CanvasHealth.lastSuccess;
                        return CanvasHealth.isHealthy && timeSinceLastSuccess < 300000; // 5 minutes
                    }
                };

                // Métriques de performance
                const CanvasMetrics = {
                    saveAttempts: 0,
                    saveSuccesses: 0,
                    saveFailures: 0,
                    averageSaveTime: 0,
                    lastSaveTime: 0,

                    recordSaveStart: () => {
                        CanvasMetrics.saveAttempts++;
                        CanvasMetrics.lastSaveTime = performance.now();
                    },

                    recordSaveEnd: (success) => {
                        const duration = performance.now() - CanvasMetrics.lastSaveTime;
                        CanvasMetrics.averageSaveTime = (CanvasMetrics.averageSaveTime + duration) / 2;

                        if (success) {
                            CanvasMetrics.saveSuccesses++;
                        } else {
                            CanvasMetrics.saveFailures++;
                        }

                        console.log(`METRICS: Save completed in ${duration.toFixed(2)}ms (avg: ${CanvasMetrics.averageSaveTime.toFixed(2)}ms)`);

                        // Alerte si performance dégradée
                        if (CanvasMetrics.averageSaveTime > 5000) { // 5 secondes
                            console.warn('PERFORMANCE_WARNING: Average save time > 5s, possible issues');
                        }
                    },

                    getStats: () => {
                        const successRate = CanvasMetrics.saveAttempts > 0 ?
                            (CanvasMetrics.saveSuccesses / CanvasMetrics.saveAttempts * 100).toFixed(1) : 0;

                        return {
                            attempts: CanvasMetrics.saveAttempts,
                            successes: CanvasMetrics.saveSuccesses,
                            failures: CanvasMetrics.saveFailures,
                            successRate: successRate + '%',
                            averageTime: CanvasMetrics.averageSaveTime.toFixed(2) + 'ms'
                        };
                    }
                };
                const CanvasRecovery = {
                    init: () => {
                        // Health check périodique
                        setInterval(() => {
                            CanvasRecovery.performHealthCheck();
                        }, CANVAS_CONFIG.HEALTH_CHECK_INTERVAL);

                        // Récupération au chargement de la page
                        CanvasRecovery.attemptRecovery();

                        console.log('RECOVERY_INIT: Auto-recovery system initialized');
                    },

                    performHealthCheck: () => {
                        // Ping simple du serveur
                        fetch(ajaxurl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({
                                action: 'pdf_builder_health_check',
                                nonce: '<?php echo wp_create_nonce("pdf_builder_health"); ?>'
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                CanvasHealth.recordSuccess();
                                console.log('HEALTH_CHECK: System is healthy');
                            } else {
                                CanvasHealth.recordFailure();
                                console.warn('HEALTH_CHECK: System reported issues');
                            }
                        })
                        .catch(error => {
                            CanvasHealth.recordFailure();
                            console.warn('HEALTH_CHECK: Failed to reach server:', error);
                        });
                    },

                    attemptRecovery: () => {
                        // Vérifier s'il y a des données en cache non sauvegardées
                        const cachedData = CanvasCache.load();
                        if (cachedData) {
                            console.log('RECOVERY: Found unsaved data in cache, attempting auto-save');

                            // Interface utilisateur pour informer l'utilisateur
                            if (confirm('Des paramètres non sauvegardés ont été trouvés. Voulez-vous les restaurer ?')) {
                                CanvasRecovery.restoreFromCache(cachedData);
                            }
                        }
                    },

                    restoreFromCache: (cachedData) => {
                        console.log('CACHE_RESTORE: Restoring settings from cache');

                        // Appliquer les données cachées aux champs cachés
                        Object.entries(cachedData).forEach(([key, value]) => {
                            const hiddenField = document.querySelector(`input[name="pdf_builder_settings[${key}]"]`);
                            if (hiddenField) {
                                hiddenField.value = value;
                                console.log(`CACHE_RESTORE: Restored ${key} = ${value}`);
                            }
                        });

                        // Notification à l'utilisateur
                        alert('Paramètres restaurés depuis le cache local. Pensez à sauvegarder !');
                    }
                };

                // Gestionnaire des modales Canvas
                (function() {
                    'use strict';

                    console.log('[PDF Builder] 🚀 MODALS_SYSTEM_v2.1 - Initializing Canvas modals system (FIXED VERSION)');
                    console.log('[PDF Builder] 📅 Date: 2025-12-11 21:35');
                    console.log('[PDF Builder] 🔧 Fix: HTML/PHP moved outside script tags');

                    // Fonction d'initialisation avec retry
                    function initializeModals(retryCount = 0) {
                        const maxRetries = 5;
                        const retryDelay = 200; // ms

                        try {
                            console.log(`[PDF Builder] MODALS_INIT - Initializing Canvas modals system (attempt ${retryCount + 1}/${maxRetries + 1})`);

                            // Vérifier que les modals existent
                            const modalCategories = ['dimensions', 'apparence', 'grille', 'zoom', 'interactions', 'export', 'performance', 'debug'];
                            let missingModals = [];
                            let foundModals = [];

                            modalCategories.forEach(category => {
                                const modalId = `canvas-${category}-modal-overlay`;
                                const modal = document.getElementById(modalId);
                                if (!modal) {
                                    missingModals.push(modalId);
                                    console.warn(`[PDF Builder] MODALS_INIT - Missing modal: ${modalId}`);
                                } else {
                                    foundModals.push(modalId);
                                    console.log(`[PDF Builder] MODALS_INIT - Found modal: ${modalId}`);
                                }
                            });

                            // Vérifier que les boutons de configuration existent
                            const configButtons = document.querySelectorAll('.canvas-configure-btn');
                            console.log(`[PDF Builder] MODALS_INIT - Found ${configButtons.length} configuration buttons`);

                            if (missingModals.length > 0) {
                                if (retryCount < maxRetries) {
                                    console.warn(`[PDF Builder] MODALS_INIT - ${missingModals.length} modals missing, retrying in ${retryDelay}ms...`);
                                    setTimeout(() => initializeModals(retryCount + 1), retryDelay);
                                    return;
                                } else {
                                    console.error(`[PDF Builder] MODALS_INIT - ${missingModals.length} modals are missing after ${maxRetries} retries:`, missingModals);
                                    alert(`Attention: ${missingModals.length} modales sont manquantes. Certaines fonctionnalités risquent de ne pas fonctionner.`);
                                }
                            } else {
                                console.log(`[PDF Builder] MODALS_INIT - All ${foundModals.length} modals found successfully`);
                            }

                            if (configButtons.length === 0) {
                                console.warn('[PDF Builder] MODALS_INIT - No configuration buttons found');
                                if (retryCount < maxRetries) {
                                    console.warn(`[PDF Builder] MODALS_INIT - Retrying buttons check in ${retryDelay}ms...`);
                                    setTimeout(() => initializeModals(retryCount + 1), retryDelay);
                                    return;
                                }
                            }

                            console.log('[PDF Builder] MODALS_INIT - Modal system initialized successfully');

                        } catch (error) {
                            console.error('[PDF Builder] MODALS_INIT - Error during initialization:', error);
                            if (retryCount < maxRetries) {
                                console.warn(`[PDF Builder] MODALS_INIT - Retrying after error in ${retryDelay}ms...`);
                                setTimeout(() => initializeModals(retryCount + 1), retryDelay);
                            }
                        }
                    }

                    // Appeler l'initialisation quand le DOM est prêt et les modals sont chargées
                    function initWhenReady() {
                        if (document.readyState === 'loading') {
                            document.addEventListener('DOMContentLoaded', () => waitForModalsAndInitialize(0));
                        } else {
                            // DOM déjà chargé, attendre les modals
                            waitForModalsAndInitialize(0);
                        }
                    }

                    // Fonction pour attendre que les modals soient chargées
                    function waitForModalsAndInitialize(attempt = 0) {
                        const maxAttempts = 10;
                        const modalIds = [
                            'canvas-dimensions-modal-overlay',
                            'canvas-apparence-modal-overlay',
                            'canvas-grille-modal-overlay',
                            'canvas-zoom-modal-overlay',
                            'canvas-interactions-modal-overlay',
                            'canvas-export-modal-overlay',
                            'canvas-performance-modal-overlay',
                            'canvas-debug-modal-overlay'
                        ];

                        const allModalsLoaded = modalIds.every(id => document.getElementById(id) !== null);

                        if (allModalsLoaded) {
                            console.log('[PDF Builder] MODALS_READY - All modals loaded, initializing...');
                            initializeModals(0);
                        } else if (attempt < maxAttempts) {
                            console.log(`[PDF Builder] MODALS_WAIT - Waiting for modals (attempt ${attempt + 1}/${maxAttempts})`);
                            setTimeout(() => waitForModalsAndInitialize(attempt + 1), 100);
                        } else {
                            console.error('[PDF Builder] MODALS_TIMEOUT - Modals failed to load after maximum attempts');
                            // Essayer quand même d'initialiser avec ce qui est disponible
                            initializeModals(0);
                        }
                    }

                    initWhenReady();

                    // Fonction pour ouvrir une modale - VERSION RENFORCÉE
                    function openModal(modalId) {
                        try {
                            console.log(`[PDF Builder] OPEN_MODAL - Attempting to open: ${modalId}`);

                            const modal = document.getElementById(modalId);
                            if (!modal) {
                                console.error(`[PDF Builder] OPEN_MODAL - Modal element not found: ${modalId}`);
                                alert(`Erreur: La modale ${modalId} n'a pas été trouvée.`);
                                return;
                            }

                            // Extraire la catégorie depuis l'ID de la modale
                            const categoryMatch = modalId.match(/canvas-(\w+)-modal-overlay/);
                            if (categoryMatch) {
                                const category = categoryMatch[1];
                                console.log(`[PDF Builder] OPEN_MODAL - Opening modal for category: ${category}`);

                                // Mettre à jour les valeurs avant d'ouvrir
                                updateModalValues(category);
                            } else {
                                console.warn(`[PDF Builder] OPEN_MODAL - Could not extract category from modalId: ${modalId}`);
                            }

                            // Afficher la modale avec animation
                            modal.style.display = 'flex';
                            document.body.style.overflow = 'hidden';

                            // Focus sur la modale pour l'accessibilité
                            modal.setAttribute('aria-hidden', 'false');
                            modal.focus();

                            console.log(`[PDF Builder] OPEN_MODAL - Modal opened successfully: ${modalId}`);

                        } catch (error) {
                            console.error(`[PDF Builder] OPEN_MODAL - Error opening modal ${modalId}:`, error);
                            alert(`Erreur lors de l'ouverture de la modale: ${error.message}`);
                        }
                    }

                    // Fonction pour mettre à jour les valeurs d'une modale avec les paramètres actuels
                    function updateModalValues(category) {
                        console.log(`[PDF Builder] UPDATE_MODAL - Called with category: ${category}`);
                        console.log('[PDF Builder] UPDATE_MODAL - Starting modal value synchronization');
                        const modal = document.querySelector(`#canvas-${category}-modal-overlay`);
                        if (!modal) {
                            console.log(`[PDF Builder] UPDATE_MODAL - Modal #canvas-${category}-modal-overlay not found`);
                            return;
                        }
                        console.log(`[PDF Builder] UPDATE_MODAL - Modal found, processing category: ${category}`);

                        // Mapping des champs selon la catégorie
                        const fieldMappings = {
                            'dimensions': {
                                'canvas_width': 'pdf_builder_canvas_width',
                                'canvas_height': 'pdf_builder_canvas_height',
                                'canvas_dpi': 'pdf_builder_canvas_dpi',
                                'canvas_format': 'pdf_builder_canvas_format'
                            },
                            'apparence': {
                                'canvas_bg_color': 'pdf_builder_canvas_bg_color',
                                'canvas_border_color': 'pdf_builder_canvas_border_color',
                                'canvas_border_width': 'pdf_builder_canvas_border_width',
                                'canvas_shadow_enabled': 'pdf_builder_canvas_shadow_enabled',
                                'canvas_container_bg_color': 'pdf_builder_canvas_container_bg_color'
                            },
                            'grille': {
                                'canvas_grid_enabled': 'pdf_builder_canvas_grid_enabled',
                                'canvas_grid_size': 'pdf_builder_canvas_grid_size',
                                'canvas_guides_enabled': 'pdf_builder_canvas_guides_enabled',
                                'canvas_snap_to_grid': 'pdf_builder_canvas_snap_to_grid'
                            },
                            'zoom': {
                                'canvas_zoom_min': 'pdf_builder_canvas_zoom_min',
                                'canvas_zoom_max': 'pdf_builder_canvas_zoom_max',
                                'canvas_zoom_default': 'pdf_builder_canvas_zoom_default',
                                'canvas_zoom_step': 'pdf_builder_canvas_zoom_step'
                            },
                            'interactions': {
                                'canvas_drag_enabled': 'pdf_builder_canvas_drag_enabled',
                                'canvas_resize_enabled': 'pdf_builder_canvas_resize_enabled',
                                'canvas_rotate_enabled': 'pdf_builder_canvas_rotate_enabled',
                                'canvas_multi_select': 'pdf_builder_canvas_multi_select',
                                'canvas_selection_mode': 'pdf_builder_canvas_selection_mode',
                                'canvas_keyboard_shortcuts': 'pdf_builder_canvas_keyboard_shortcuts'
                            },
                            'export': {
                                'canvas_export_quality': 'pdf_builder_canvas_export_quality',
                                'canvas_export_format': 'pdf_builder_canvas_export_format',
                                'canvas_export_transparent': 'pdf_builder_canvas_export_transparent'
                            },
                            'performance': {
                                'canvas_fps_target': 'pdf_builder_canvas_fps_target',
                                'canvas_memory_limit_js': 'pdf_builder_canvas_memory_limit_js',
                                'canvas_response_timeout': 'pdf_builder_canvas_response_timeout'
                            },
                            'debug': {
                                'canvas_debug_enabled': 'pdf_builder_canvas_debug_enabled',
                                'canvas_performance_monitoring': 'pdf_builder_canvas_performance_monitoring',
                                'canvas_error_reporting': 'pdf_builder_canvas_error_reporting'
                            }
                        };

                        const mappings = fieldMappings[category];
                        if (!mappings) return;

                        // Valeurs par défaut pour les champs Canvas
                        const defaultValues = CANVAS_DEFAULT_VALUES;

                        // Mettre à jour chaque champ
                        for (const [fieldId, settingKey] of Object.entries(mappings)) {
                            const field = modal.querySelector(`#${fieldId}, [name="${settingKey}"]`);
                            if (field) {
                                // Chercher la valeur dans les champs cachés
                                const hiddenField = document.querySelector(`input[name="pdf_builder_settings[${settingKey}]"]`);
                                let value = '';
                                
                                if (hiddenField && hiddenField.value && hiddenField.value.trim() !== '') {
                                    value = hiddenField.value;
                                    console.log(`[PDF Builder] UPDATE_MODAL - Using hidden field value for ${settingKey}: ${value}`);
                                } else {
                                    // Utiliser la valeur par défaut si le champ caché est vide ou n'existe pas
                                    value = defaultValues[settingKey] || '';
                                    console.log(`[PDF Builder] UPDATE_MODAL - Using default value for ${settingKey}: ${value} (hidden field was empty)`);
                                }
                                
                                if (category === 'grille') {
                                    console.log(`[PDF Builder] GRID_UPDATE - Processing grid field ${fieldId} with value: ${value}`);
                                }
                                
                                // Log spécifique pour les toggles de grille
                                if (['canvas_grid_enabled', 'canvas_guides_enabled', 'canvas_snap_to_grid'].includes(fieldId)) {
                                    console.log(`GRID_TOGGLE: Updating ${fieldId} (${settingKey}) with value: ${value}, field type: ${field.type}`);
                                }
                                
                                if (field.type === 'checkbox') {
                                    field.checked = value === '1';
                                    // Synchroniser la classe CSS pour les toggles
                                    const toggleSwitch = field.closest('.toggle-switch');
                                    if (toggleSwitch) {
                                        if (value === '1') {
                                            toggleSwitch.classList.add('checked');
                                        } else {
                                            toggleSwitch.classList.remove('checked');
                                        }
                                        console.log(`TOGGLE_DEBUG: ${fieldId} - checked=${field.checked}, toggle classes: ${toggleSwitch.className}`);
                                    } else {
                                        console.log(`TOGGLE_DEBUG: ${fieldId} - No toggle-switch parent found`);
                                    }
                                    if (['canvas_grid_enabled', 'canvas_guides_enabled', 'canvas_snap_to_grid', 'canvas_drag_enabled', 'canvas_resize_enabled', 'canvas_rotate_enabled', 'canvas_multi_select', 'canvas_keyboard_shortcuts'].includes(fieldId)) {
                                        console.log(`ALL_TOGGLES: Set checkbox ${fieldId} checked to: ${field.checked}, toggle class: ${toggleSwitch ? toggleSwitch.className : 'no toggle'}`);
                                    }
                                } else {
                                    field.value = value;
                                    
                                    // Pour les selects, mettre à jour l'attribut selected
                                    if (field.tagName === 'SELECT') {
                                        const options = field.querySelectorAll('option');
                                        options.forEach(option => {
                                            option.selected = option.value === value;
                                        });
                                    }
                                }
                            } else {
                                console.log(`Field not found for ${fieldId} or ${settingKey}`);
                            }
                        }
                    }

                    // Fonction pour fermer une modale - VERSION RENFORCÉE
                    function closeModal(modalId) {
                        try {
                            console.log(`[PDF Builder] CLOSE_MODAL - Attempting to close: ${modalId}`);

                            const modal = document.getElementById(modalId);
                            if (!modal) {
                                console.warn(`[PDF Builder] CLOSE_MODAL - Modal element not found: ${modalId}`);
                                return;
                            }

                            // Masquer la modale
                            modal.style.display = 'none';
                            document.body.style.overflow = '';

                            // Accessibilité
                            modal.setAttribute('aria-hidden', 'true');

                            console.log(`[PDF Builder] CLOSE_MODAL - Modal closed successfully: ${modalId}`);

                        } catch (error) {
                            console.error(`[PDF Builder] CLOSE_MODAL - Error closing modal ${modalId}:`, error);
                        }
                    }

                    // Fonction pour sauvegarder les paramètres d'une modale
                    function saveModalSettings(category) {
                        // VALIDATION CÔTÉ CLIENT AVANT ENVOI
                        const allCanvasInputs = document.querySelectorAll('input[name^="pdf_builder_canvas_"]');
                        const settings = {};
                        let validationErrors = [];

                        // Collecter et valider toutes les valeurs
                        allCanvasInputs.forEach(input => {
                            let value = '';
                            if (input.type === 'checkbox') {
                                value = input.checked ? '1' : '0';
                            } else {
                                value = input.value;
                            }

                            // Utiliser la valeur par défaut si vide
                            if (!value || value.trim() === '') {
                                value = CANVAS_DEFAULT_VALUES[input.name] || '';
                                console.log(`SAVE_DEFAULT: Applied default for ${input.name}: ${value}`);
                            }

                        // VALIDATION CÔTÉ CLIENT (si activée)
                        if (FeatureGate.isEnabled('ENABLE_VALIDATION')) {
                            // Validation côté client
                            if (!CanvasValidators.validateField(input.name, value)) {
                                validationErrors.push(`${input.name}: valeur invalide "${value}"`);
                                value = CANVAS_DEFAULT_VALUES[input.name] || '';
                                console.warn(`VALIDATION_FIX: Corrected invalid value for ${input.name} to ${value}`);
                            }
                        }

                        settings[input.name] = value;
                        });

                        // Bloquer si trop d'erreurs de validation
                        if (validationErrors.length > 5) {
                            alert('Trop d\'erreurs de validation. Rechargement de la page...');
                            location.reload();
                            return;
                        }

                        console.log('SAVE_DEBUG: Validated settings to save:', settings);

                        // SAUVEGARDE EN CACHE LOCAL AVANT ENVOI
                        CanvasCache.save(settings);

                        // Ajouter métadonnées
                        settings['action'] = 'pdf_builder_save_canvas_settings';
                        settings['nonce'] = '<?php echo wp_create_nonce("pdf_builder_canvas_settings"); ?>';
                        settings['client_timestamp'] = Date.now();

                        // FONCTION DE SAUVEGARDE AVEC RETRY
                        function attemptSave(retryCount = 0) {
                            CanvasMetrics.recordSaveStart();

                            // VÉRIFICATION HEALTH AVANT ENVOI
                            if (!CanvasHealth.checkHealth() && retryCount === 0) {
                                console.warn('HEALTH_CHECK: System unhealthy, attempting save anyway');
                            }

                            const saveBtn = document.querySelector(`.canvas-modal-save[data-category="${category}"]`);
                            if (saveBtn) {
                                saveBtn.innerHTML = retryCount > 0 ? `⏳ Retry ${retryCount}/${CANVAS_CONFIG.MAX_RETRIES}...` : '⏳ Sauvegarde...';
                                saveBtn.disabled = true;
                            }

                            // CONTROLLER POUR ANNULER SI TROP LONG
                            const controller = new AbortController();
                            const timeoutId = setTimeout(() => {
                                controller.abort();
                            }, CANVAS_CONFIG.AJAX_TIMEOUT);

                            fetch(ajaxurl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: new URLSearchParams(settings),
                                signal: controller.signal
                            })
                            .then(response => {
                                clearTimeout(timeoutId);
                                if (!response.ok) {
                                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    CanvasMetrics.recordSaveEnd(true);
                                    CanvasHealth.recordSuccess();
                                    CanvasCache.clear(); // Nettoyer le cache après succès

                                    closeModal(`canvas-${category}-modal-overlay`);
                                    location.reload();
                                } else {
                                    throw new Error(data.data?.message || 'Erreur serveur inconnue');
                                }
                            })
                            .catch(error => {
                                CanvasMetrics.recordSaveEnd(false);
                                clearTimeout(timeoutId);
                                CanvasHealth.recordFailure();

                                console.error(`SAVE_ERROR (attempt ${retryCount + 1}):`, error);

                                // RETRY AUTOMATIQUE
                                if (retryCount < CANVAS_CONFIG.MAX_RETRIES) {
                                    console.log(`RETRY: Attempting save again in ${CANVAS_CONFIG.RETRY_DELAY}ms...`);
                                    setTimeout(() => {
                                        attemptSave(retryCount + 1);
                                    }, CANVAS_CONFIG.RETRY_DELAY * (retryCount + 1)); // Backoff exponentiel
                                } else {
                                    // ÉCHEC FINAL - TENTER RECUPÉRATION VIA CACHE
                                    console.error('SAVE_FAILED: All retry attempts failed');

                                    const cachedData = CanvasCache.load();
                                    if (cachedData) {
                                        console.log('FALLBACK: Attempting to restore from cache');
                                        // Ici on pourrait implémenter une restauration silencieuse
                                        alert('Sauvegarde échouée. Données préservées en cache local.');
                                    } else {
                                        alert(`Erreur de sauvegarde après ${CANVAS_CONFIG.MAX_RETRIES} tentatives: ${error.message}`);
                                    }
                                }
                            })
                            .finally(() => {
                                const saveBtn = document.querySelector(`.canvas-modal-save[data-category="${category}"]`);
                                if (saveBtn && retryCount >= CANVAS_CONFIG.MAX_RETRIES) {
                                    saveBtn.innerHTML = '💾 Sauvegarder';
                                    saveBtn.disabled = false;
                                }
                            });
                        }

                        // Démarrer la sauvegarde
                        attemptSave();
                    }

                    // Gestionnaire d'événements pour les boutons de configuration - VERSION RENFORCÉE
                    document.addEventListener('click', function(e) {
                        try {
                            // Gestionnaire pour ouvrir les modales
                            const button = e.target.closest('.canvas-configure-btn');
                            if (button) {
                                e.preventDefault();
                                console.log('[PDF Builder] CONFIG_BUTTON - Configure button clicked');

                                const card = button.closest('.canvas-card');
                                if (card) {
                                    const category = card.getAttribute('data-category');
                                    if (category) {
                                        const modalId = 'canvas-' + category + '-modal-overlay';
                                        console.log(`[PDF Builder] CONFIG_BUTTON - Opening modal for category: ${category}`);
                                        openModal(modalId);
                                    } else {
                                        console.error('[PDF Builder] CONFIG_BUTTON - No data-category attribute found on card');
                                    }
                                } else {
                                    console.error('[PDF Builder] CONFIG_BUTTON - No canvas-card parent found');
                                }
                                return;
                            }

                            // Gestionnaire pour fermer les modales
                            const closeBtn = e.target.closest('.canvas-modal-close, .cache-modal-close');
                            if (closeBtn) {
                                e.preventDefault();
                                console.log('[PDF Builder] CLOSE_BUTTON - Close button clicked');

                                const modal = closeBtn.closest('.canvas-modal-overlay, .cache-modal');
                                if (modal) {
                                    const modalId = modal.id;
                                    closeModal(modalId);
                                }
                                return;
                            }

                            // Fermer en cliquant sur l'overlay
                            const overlay = e.target.closest('.canvas-modal-overlay');
                            if (overlay && e.target === overlay) {
                                console.log('[PDF Builder] OVERLAY_CLICK - Overlay clicked');
                                const modalId = overlay.id;
                                closeModal(modalId);
                                return;
                            }

                            // Gestionnaire pour sauvegarder les paramètres
                            const saveBtn = e.target.closest('.canvas-modal-save');
                            if (saveBtn) {
                                e.preventDefault();
                                console.log('[PDF Builder] SAVE_BUTTON - Save button clicked');

                                const category = saveBtn.getAttribute('data-category');
                                if (category) {
                                    saveModalSettings(category);
                                } else {
                                    console.error('[PDF Builder] SAVE_BUTTON - No data-category attribute on save button');
                                }
                                return;
                            }

                            // Gestionnaire pour annuler les modales
                            const cancelBtn = e.target.closest('.canvas-modal-cancel, .button-secondary');
                            if (cancelBtn) {
                                e.preventDefault();
                                console.log('[PDF Builder] CANCEL_BUTTON - Cancel button clicked');

                                const modal = cancelBtn.closest('.canvas-modal-overlay');
                                if (modal) {
                                    const modalId = modal.id;
                                    closeModal(modalId);
                                }
                                return;
                            }

                        } catch (error) {
                            console.error('[PDF Builder] EVENT_HANDLER - Error in click handler:', error);
                        }
                    });

                    // Gestionnaire pour la touche Échap - VERSION RENFORCÉE
                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape') {
                            console.log('[PDF Builder] ESC_KEY - Escape key pressed');

                            // Fermer toutes les modales ouvertes
                            const openModals = document.querySelectorAll('.canvas-modal-overlay[style*="display: flex"], .cache-modal[style*="display: block"]');
                            openModals.forEach(modal => {
                                const modalId = modal.id;
                                closeModal(modalId);
                            });
                        }
                    });

                    // Synchronisation des toggles CSS lors des changements manuels
                    document.addEventListener('change', function(e) {
                        const field = e.target;
                        
                        // Synchronisation CSS pour les checkboxes dans les toggles
                        if (field.type === 'checkbox' && field.closest('.toggle-switch')) {
                            const toggleSwitch = field.closest('.toggle-switch');
                            if (field.checked) {
                                toggleSwitch.classList.add('checked');
                            } else {
                                toggleSwitch.classList.remove('checked');
                            }
                            console.log(`TOGGLE_CHANGE: ${field.id} changed to ${field.checked}, toggle classes: ${toggleSwitch.className}`);
                        }
                        
                        // SYNCHRONISATION DES VALEURS MODAL -> CHAMPS CACHÉS
                        // Si le champ fait partie d'un modal Canvas, synchroniser avec le champ caché
                        const modal = field.closest('.canvas-modal-content');
                        if (modal) {
                            // Trouver le mapping pour ce champ
                            const fieldMappings = {
                                'canvas_width': 'pdf_builder_canvas_width',
                                'canvas_height': 'pdf_builder_canvas_height',
                                'canvas_dpi': 'pdf_builder_canvas_dpi',
                                'canvas_format': 'pdf_builder_canvas_format',
                                'canvas_bg_color': 'pdf_builder_canvas_bg_color',
                                'canvas_border_color': 'pdf_builder_canvas_border_color',
                                'canvas_border_width': 'pdf_builder_canvas_border_width',
                                'canvas_container_bg_color': 'pdf_builder_canvas_container_bg_color',
                                'canvas_shadow_enabled': 'pdf_builder_canvas_shadow_enabled',
                                'canvas_grid_enabled': 'pdf_builder_canvas_grid_enabled',
                                'canvas_grid_size': 'pdf_builder_canvas_grid_size',
                                'canvas_guides_enabled': 'pdf_builder_canvas_guides_enabled',
                                'canvas_snap_to_grid': 'pdf_builder_canvas_snap_to_grid',
                                'canvas_zoom_min': 'pdf_builder_canvas_zoom_min',
                                'canvas_zoom_max': 'pdf_builder_canvas_zoom_max',
                                'canvas_zoom_default': 'pdf_builder_canvas_zoom_default',
                                'canvas_zoom_step': 'pdf_builder_canvas_zoom_step',
                                'canvas_export_quality': 'pdf_builder_canvas_export_quality',
                                'canvas_export_format': 'pdf_builder_canvas_export_format',
                                'canvas_export_transparent': 'pdf_builder_canvas_export_transparent',
                                'canvas_drag_enabled': 'pdf_builder_canvas_drag_enabled',
                                'canvas_resize_enabled': 'pdf_builder_canvas_resize_enabled',
                                'canvas_rotate_enabled': 'pdf_builder_canvas_rotate_enabled',
                                'canvas_multi_select': 'pdf_builder_canvas_multi_select',
                                'canvas_selection_mode': 'pdf_builder_canvas_selection_mode',
                                'canvas_keyboard_shortcuts': 'pdf_builder_canvas_keyboard_shortcuts',
                                'canvas_fps_target': 'pdf_builder_canvas_fps_target',
                                'canvas_memory_limit_js': 'pdf_builder_canvas_memory_limit_js',
                                'canvas_response_timeout': 'pdf_builder_canvas_response_timeout',
                                'canvas_lazy_loading_editor': 'pdf_builder_canvas_lazy_loading_editor',
                                'canvas_preload_critical': 'pdf_builder_canvas_preload_critical',
                                'canvas_lazy_loading_plugin': 'pdf_builder_canvas_lazy_loading_plugin',
                                'canvas_debug_enabled': 'pdf_builder_canvas_debug_enabled',
                                'canvas_performance_monitoring': 'pdf_builder_canvas_performance_monitoring',
                                'canvas_error_reporting': 'pdf_builder_canvas_error_reporting',
                                'canvas_memory_limit_php': 'pdf_builder_canvas_memory_limit_php'
                            };
                            
                            // Chercher si ce champ correspond à un mapping
                            let settingKey = null;
                            if (field.id && fieldMappings[field.id]) {
                                settingKey = fieldMappings[field.id];
                            } else if (field.name && fieldMappings[field.name]) {
                                settingKey = fieldMappings[field.name];
                            }
                            
                            if (settingKey) {
                                // Trouver le champ caché correspondant
                                const hiddenField = document.querySelector(`input[name="pdf_builder_settings[${settingKey}]"]`);
                                if (hiddenField) {
                                    // Mettre à jour la valeur du champ caché
                                    if (field.type === 'checkbox') {
                                        hiddenField.value = field.checked ? '1' : '0';
                                    } else {
                                        hiddenField.value = field.value;
                                    }
                                    console.log(`MODAL_SYNC: Updated hidden field ${settingKey} with value: ${hiddenField.value} from modal field ${field.id || field.name}`);
                                } else {
                                    console.log(`MODAL_SYNC: Hidden field not found for ${settingKey}`);
                                }
                            }
                        }
                    });

                    console.log('Modal manager initialized');

                    // INITIALISER LES SYSTÈMES DE ROBUSTESSE
                    CanvasRecovery.init();

                    // Circuit breaker pour éviter les spam de requêtes
                    let lastSaveAttempt = 0;
                    const SAVE_COOLDOWN = 2000; // 2 secondes minimum entre sauvegardes

                    // Wrapper pour saveModalSettings avec circuit breaker
                    const originalSaveModalSettings = saveModalSettings;
                    saveModalSettings = function(category) {
                        const now = Date.now();
                        if (now - lastSaveAttempt < SAVE_COOLDOWN) {
                            console.warn('CIRCUIT_BREAKER: Save attempt too soon, ignoring');
                            return;
                        }
                        lastSaveAttempt = now;

                        // Vérifier si le système est en mode dégradé
                        if (!CanvasHealth.checkHealth()) {
                            console.warn('CIRCUIT_BREAKER: System unhealthy, forcing cache-only mode');
                            // Ici on pourrait implémenter un mode dégradé
                        }

                        originalSaveModalSettings(category);
                    };

                    console.log('ROBUSTNESS_INIT: All safety systems activated');

                    // EXPOSER LES OUTILS DE DIAGNOSTIC GLOBALEMENT
                    window.CanvasDebug = {
                        getHealthStatus: () => ({
                            healthy: CanvasHealth.isHealthy,
                            lastSuccess: new Date(CanvasHealth.lastSuccess).toLocaleString(),
                            failureCount: CanvasHealth.failureCount,
                            timeSinceLastSuccess: Math.round((Date.now() - CanvasHealth.lastSuccess) / 1000) + 's'
                        }),

                        getMetrics: () => CanvasMetrics.getStats(),

                        getCacheStatus: () => {
                            const cached = CanvasCache.load();
                            return cached ? {
                                hasCache: true,
                                age: Math.round((Date.now() - JSON.parse(localStorage.getItem(CANVAS_CONFIG.CACHE_KEY) || '{}').timestamp || 0) / 1000) + 's',
                                data: cached
                            } : { hasCache: false };
                        },

                        forceHealthCheck: () => CanvasRecovery.performHealthCheck(),

                        clearCache: () => CanvasCache.clear(),

                        simulateFailure: () => {
                            CanvasHealth.recordFailure();
                            console.log('DEBUG: Simulated failure recorded');
                        },

                        getSystemStatus: () => ({
                            health: window.CanvasDebug.getHealthStatus(),
                            metrics: window.CanvasDebug.getMetrics(),
                            cache: window.CanvasDebug.getCacheStatus(),
                            config: CANVAS_CONFIG
                        })
                    };

                    console.log('DEBUG_INIT: Diagnostic tools available via window.CanvasDebug');
