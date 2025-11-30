// Test file to isolate JavaScript syntax error
// Combining JavaScript from settings-main.php and settings-developpeur.php

// From settings-main.php - first script block
window.pdfBuilderSavedSettings = {}; // Mock data
window.pdfBuilderCanvasSettings = {}; // Mock data
window.pdfBuilderDebugSettings = {
    javascript: false,
    javascript_verbose: false,
    ajax: false,
    performance: false,
    settings_page: false,
    pdf_editor: false,
    database: false
};
window.pdfBuilderAjax = {
    nonce: 'test_nonce',
    ajaxurl: '/wp-admin/admin-ajax.php'
};
window.pdfBuilderSettingsNonce = 'test_nonce';
window.pdfBuilderNotifications = {
    settings: {
        enabled: true,
        position: 'top-right',
        duration: 5000,
        max_notifications: 5,
        animation: 'slide',
        sound_enabled: false,
        types: {
            success: ['icon' => '✅', 'color' => '#28a745', 'bg' => '#d4edda'],
            error: ['icon' => '❌', 'color' => '#dc3545', 'bg' => '#f8d7da'],
            warning: ['icon' => '⚠️', 'color' => '#ffc107', 'bg' => '#fff3cd'],
            info: ['icon' => 'ℹ️', 'color' => '#17a2b8', 'bg' => '#d1ecf1']
        }
    },
    ajax_url: '/wp-admin/admin-ajax.php',
    nonce: 'test_nonce',
    strings: {
        close: 'Fermer',
        dismiss_all: 'Tout fermer'
    }
};

// PDF_Builder_Preview_Manager from settings-main.php
window.PDF_Builder_Preview_Manager = {
    initializeAllPreviews: function() {
        console.log('Initializing all previews with saved data');
        this.initializeCompanyPreview();
        this.initializePDFPreview();
        this.initializeCachePreview();
        this.initializeTemplatesPreview();
        this.initializeDeveloperPreview();
        this.initializeCanvasPreviews();
        console.log('All previews initialized with saved data');
    },

    initializeCompanyPreview: function() {
        if (!window.pdfBuilderSavedSettings) return;
        const data = window.pdfBuilderSavedSettings;
        console.log('Company preview initialized');
    },

    initializePDFPreview: function() {
        if (!window.pdfBuilderSavedSettings) return;
        const data = window.pdfBuilderSavedSettings;
        console.log('PDF preview initialized');
    },

    initializeCachePreview: function() {
        if (!window.pdfBuilderSavedSettings) return;
        const data = window.pdfBuilderSavedSettings;
        console.log('Cache preview initialized');
    },

    initializeTemplatesPreview: function() {
        if (!window.pdfBuilderSavedSettings) return;
        const data = window.pdfBuilderSavedSettings;
        console.log('Templates preview initialized');
    },

    initializeDeveloperPreview: function() {
        if (!window.pdfBuilderSavedSettings) return;
        const data = window.pdfBuilderSavedSettings;
        console.log('Developer preview initialized');
    },

    initializeCanvasPreviews: function() {
        if (!window.pdfBuilderCanvasSettings) return;
        console.log('Initializing canvas previews with saved settings');
        setTimeout(() => {
            try {
                console.log('Canvas previews initialized successfully');
            } catch (error) {
                console.error('Error initializing canvas previews:', error);
            }
        }, 100);
    }
};

// From settings-main.php - second script block
window.updateZoomCardPreview = function() {
    console.log('updateZoomCardPreview called');
    try {
        const minZoom = 10;
        const maxZoom = 500;
        const defaultZoom = 100;
        const stepZoom = 25;

        console.log('zoom values - min:', minZoom, 'max:', maxZoom, 'default:', defaultZoom, 'step:', stepZoom);

        console.log('updateZoomCardPreview completed successfully');
    } catch (error) {
        console.error('Error in updateZoomCardPreview:', error);
    }
};

// Tab switching functionality
function initializeTabs() {
    console.log('initializeTabs called');
}

if (window.pdfBuilderDebugSettings?.javascript) {
    console.log('PDF Builder: About to add DOMContentLoaded listener');
    console.log('PDF Builder: Document readyState:', document.readyState);
    console.log('PDF Builder: Window loaded:', window.loaded);
}

window.initializeTabs = initializeTabs;

function updateSecurityStatusIndicators() {
    console.log('updateSecurityStatusIndicators called');
}

function updateTemplateStatusIndicators() {
    console.log('updateTemplateStatusIndicators called');
}

function updateTemplateLibraryIndicator() {
    console.log('updateTemplateLibraryIndicator called');
}

function updateSystemStatusIndicators() {
    console.log('updateSystemStatusIndicators called');
}

function toggleRGPDControls() {
    console.log('toggleRGPDControls called');
}

window.updateSecurityStatusIndicators = updateSecurityStatusIndicators;
window.updateTemplateStatusIndicators = updateTemplateStatusIndicators;
window.updateTemplateLibraryIndicator = updateTemplateLibraryIndicator;
window.updateSystemStatusIndicators = updateSystemStatusIndicators;
window.toggleRGPDControls = toggleRGPDControls;

function updateFloatingSaveButtonText(activeTabId) {
    console.log(`[FLOATING SAVE] Button text updated to: "Enregistrer ${activeTabId}" for tab: ${activeTabId}`);
}

window.updateFloatingSaveButtonText = updateFloatingSaveButtonText;

// From settings-main.php - IIFE block
(function() {
    'use strict';

    window.PDF_Builder_Ajax_Handler = {
        config: {
            nonceTTL: 20 * 60 * 1000,
            refreshThreshold: 5 * 60 * 1000,
            maxRetries: 2,
            retryDelay: 1000,
            preloadCount: 3,
            enableProactiveRefresh: true,
            enableRetry: true
        },

        nonceState: {
            current: null,
            created: null,
            expires: null,
            refreshTimer: null,
            preloadQueue: [],
            retryCount: 0,
            stats: {
                requests: 0,
                nonceErrors: 0,
                retries: 0,
                refreshes: 0,
                lastError: null
            }
        },

        initialize: function() {
            console.log('🔐 [PDF Builder] Initialisation du système de nonce avancé');
            this.nonceState.current = window.pdfBuilderAjax?.nonce;
            this.nonceState.created = Date.now();
            this.nonceState.expires = Date.now() + this.config.nonceTTL;

            if (this.config.enableProactiveRefresh) {
                this.startProactiveRefresh();
            }

            this.preloadNonces();

            console.log('🔐 [PDF Builder] Système de nonce initialisé:', {
                current: this.nonceState.current ? '***' : null,
                expires: new Date(this.nonceState.expires).toLocaleTimeString(),
                proactiveRefresh: this.config.enableProactiveRefresh
            });
        },

        startProactiveRefresh: function() {
            if (this.nonceState.refreshTimer) {
                clearTimeout(this.nonceState.refreshTimer);
            }

            const timeUntilRefresh = Math.max(0, this.nonceState.expires - Date.now() - this.config.refreshThreshold);

            this.nonceState.refreshTimer = setTimeout(() => {
                console.log('🔄 [PDF Builder] Rafraîchissement proactif du nonce');
                this.refreshNonce().then(() => {
                    this.startProactiveRefresh();
                }).catch(error => {
                    console.error('Erreur lors du rafraîchissement proactif:', error);
                    this.startProactiveRefresh();
                });
            }, timeUntilRefresh);

            console.log(`⏰ [PDF Builder] Prochain rafraîchissement dans ${Math.round(timeUntilRefresh / 1000 / 60)} minutes`);
        },

        preloadNonces: function() {
            if (this.nonceState.preloadQueue.length >= this.config.preloadCount) {
                return;
            }

            console.log(`📦 [PDF Builder] Préchargement de ${this.config.preloadCount - this.nonceState.preloadQueue.length} nonces`);
        },

        refreshNonce: function() {
            return new Promise((resolve, reject) => {
                if (this.nonceState.preloadQueue.length > 0) {
                    const freshNonce = this.nonceState.preloadQueue.shift();
                    this.setCurrentNonce(freshNonce.nonce, freshNonce.created);
                    this.nonceState.stats.refreshes++;
                    console.log('🔄 [PDF Builder] Nonce rafraîchi depuis le cache');
                    resolve();
                    return;
                }

                // Mock refresh
                this.setCurrentNonce('new_nonce_' + Date.now(), Date.now());
                this.nonceState.stats.refreshes++;
                console.log('🔄 [PDF Builder] Nonce rafraîchi depuis le serveur');
                resolve();
            });
        },

        setCurrentNonce: function(nonce, created = Date.now()) {
            this.nonceState.current = nonce;
            this.nonceState.created = created;
            this.nonceState.expires = created + this.config.nonceTTL;

            if (window.pdfBuilderAjax) {
                window.pdfBuilderAjax.nonce = nonce;
            }
        },

        isNonceExpiringSoon: function() {
            return (this.nonceState.expires - Date.now()) < this.config.refreshThreshold;
        },

        cleanup: function() {
            if (this.nonceState.refreshTimer) {
                clearTimeout(this.nonceState.refreshTimer);
                this.nonceState.refreshTimer = null;
            }
            this.nonceState.preloadQueue = [];
        },

        forceRefresh: function() {
            console.log('🔄 [PDF Builder] Rafraîchissement forcé du nonce');
            return this.refreshNonce();
        },

        configure: function(newConfig) {
            Object.assign(this.config, newConfig);
            console.log('⚙️ [PDF Builder] Configuration du système de nonce mise à jour:', this.config);
        },

        makeRequest: function(formData, options = {}) {
            const self = this;
            return new Promise((resolve, reject) => {
                const defaultOptions = {
                    button: null,
                    context: 'Unknown',
                    successCallback: null,
                    errorCallback: null,
                    retryCount: 0
                };

                const opts = Object.assign({}, defaultOptions, options);

                if (opts.button) {
                    this.setButtonState(opts.button, 'loading');
                }

                this.ensureValidNonce().then(() => {
                    if (formData instanceof FormData) {
                        formData.set('nonce', this.nonceState.current);
                    } else if (typeof formData === 'object') {
                        formData.nonce = this.nonceState.current;
                    }

                    console.log(`🔄 [PDF Builder AJAX] ${opts.context} - Making request with nonce:`, this.nonceState.current ? this.nonceState.current.substring(0, 10) + '...' : 'NULL');

                    // Mock fetch
                    setTimeout(() => {
                        if (opts.successCallback) {
                            opts.successCallback({success: true, data: {message: 'Success'}}, {success: true, data: {message: 'Success'}});
                        }
                        resolve({success: true, data: {message: 'Success'}});
                    }, 100);

                }).catch(error => {
                    console.error(`🔄 [PDF Builder AJAX] ${opts.context} - Nonce validation failed:`, error);
                    if (opts.button) {
                        this.setButtonState(opts.button, 'error');
                    }
                    reject(error);
                });
            });
        },

        ensureValidNonce: function() {
            return new Promise((resolve) => {
                if (this.nonceState.current && !this.isNonceExpiringSoon()) {
                    resolve();
                } else {
                    this.refreshNonce().then(resolve).catch(() => {
                        if (this.nonceState.current) {
                            resolve();
                        } else {
                            throw new Error('Unable to obtain valid nonce');
                        }
                    });
                }
            });
        },

        setButtonState: function(button, state) {
            if (!button) return;

            const originalText = button.getAttribute ? button.getAttribute('data-original-text') || button.textContent : 'Button';

            switch (state) {
                case 'loading':
                    if (button.setAttribute) button.setAttribute('data-original-text', originalText);
                    console.log('Button set to loading');
                    break;
                case 'success':
                    console.log('Button set to success');
                    break;
                case 'error':
                    console.log('Button set to error');
                    setTimeout(() => this.setButtonState(button, 'reset'), 2000);
                    break;
                case 'reset':
                default:
                    console.log('Button reset');
                    break;
            }
        },

        getStats: function() {
            return {
                nonce: {
                    current: this.nonceState.current ? '***' : null,
                    created: this.nonceState.created,
                    expires: this.nonceState.expires,
                    timeUntilExpiry: Math.max(0, this.nonceState.expires - Date.now()),
                    isExpiringSoon: this.isNonceExpiringSoon()
                },
                stats: this.nonceState.stats,
                config: this.config,
                preloadQueue: this.nonceState.preloadQueue.length
            };
        },

        show: function(message) {
            console.log('Show message:', message);
        }
    };

    // Initialize on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function() {
        PDF_Builder_Ajax_Handler.initialize();
    });

    // Cleanup on unload
    window.addEventListener('beforeunload', function() {
        PDF_Builder_Ajax_Handler.cleanup();
    });

    // Expose stats globally
    window.pdfBuilderNonceStats = function() {
        return PDF_Builder_Ajax_Handler.getStats();
    };

    window.show = function(message) {
        console.log('Global show:', message);
    };

    // Basic modal functionality
    function safeQuerySelector(selector) {
        try {
            return document.querySelector(selector);
        } catch (e) {
            return null;
        }
    }

    function safeQuerySelectorAll(selector) {
        try {
            return document.querySelectorAll(selector);
        } catch (e) {
            return [];
        }
    }

    function hideModal(modal) {
        if (!modal) return;
        try {
            modal.style.setProperty('display', 'none', 'important');
        } catch (e) {
        }
    }

    function showModal(modal) {
        if (!modal) return false;
        try {
            modal.style.setProperty('display', 'flex', 'important');
            modal.style.setProperty('position', 'fixed', 'important');
            modal.style.setProperty('top', '0', 'important');
            modal.style.setProperty('left', '0', 'important');
            modal.style.setProperty('width', '100%', 'important');
            modal.style.setProperty('height', '100%', 'important');
            modal.style.setProperty('background', 'rgba(0,0,0,0.7)', 'important');
            modal.style.setProperty('z-index', '2147483647', 'important');
            modal.style.setProperty('align-items', 'center', 'important');
            modal.style.setProperty('justify-content', 'center', 'important');
            return true;
        } catch (e) {
            return false;
        }
    }

    // Initialize modals
    function initializeModals() {
        console.log('Initializing modals');
    }

    window.updateCanvasPreviews = function(category) {
        console.log('updateCanvasPreviews called with category:', category);
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initializeModals();
            initializeFloatingSaveButton();
        });
    } else {
        initializeModals();
        initializeFloatingSaveButton();
    }

    function initializeFloatingSaveButton() {
        console.log('Initializing floating save button');
    }

    function updateFloatingSaveButtonText(activeTabId) {
        console.log(`[FLOATING SAVE] Button text updated to: "Enregistrer ${activeTabId}" for tab: ${activeTabId}`);
    }
})();

// From tab-diagnostic.php
if (window.pdfBuilderDebugSettings?.javascript) {
    console.log('=== PDF BUILDER TAB DIAGNOSTIC ===');
}

document.addEventListener('DOMContentLoaded', function() {
    if (window.pdfBuilderDebugSettings?.javascript) {
        console.log('DOM loaded, running diagnostic...');
    }

    if (window.pdfBuilderDebugSettings?.javascript) {
        console.log('initializeTabs function exists:', typeof window.initializeTabs === 'function');
    }

    if (window.pdfBuilderDebugSettings?.javascript) {
        console.log('=== DIAGNOSTIC COMPLETE ===');
    }
});

console.log('JavaScript test file loaded successfully');
