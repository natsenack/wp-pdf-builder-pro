
// Données centralisées chargées depuis la base de données
window.pdfBuilderSavedSettings = {};
window.pdfBuilderCanvasSettings = {};

// Paramètres de debug pour le JavaScript
window.pdfBuilderDebugSettings = {
    javascript: false,
    javascript_verbose: false,
    ajax: false,
    performance: false,
    settings_page: false,
    pdf_editor: false,
    database: false
};

// Variables AJAX globales pour les requêtes AJAX
window.pdfBuilderAjax = {
    nonce: '',
    ajaxurl: ''
};

// Nonce spécifique pour les paramètres
window.pdfBuilderSettingsNonce = '';

// Paramètres de notifications pour le JavaScript
window.pdfBuilderNotifications = {
    settings: {
        enabled: true,
        position: 'top-right',
        duration: 5000,
        max_notifications: 5,
        animation: 'slide',
        sound_enabled: false,
        types: {
            success: {icon: '✅', color: '#28a745', bg: '#d4edda'},
            error: {icon: '❌', color: '#dc3545', bg: '#f8d7da'},
            warning: {icon: '⚠️', color: '#ffc107', bg: '#fff3cd'},
            info: {icon: 'ℹ️', color: '#17a2b8', bg: '#d1ecf1'}
        }
    },
    ajax_url: '',
    nonce: '',
    strings: {
        close: 'Fermer',
        dismiss_all: 'Tout fermer'
    }
};

// Système centralisé d'initialisation des previews avec données BDD
window.PDF_Builder_Preview_Manager = {
    /**
     * Initialise toutes les previews avec les données sauvegardées
     */
    initializeAllPreviews: function() {
        pdfBuilderDebug('Initializing all previews with saved data');

        // Initialiser les previews individuelles
        this.initializeCompanyPreview();
        this.initializePDFPreview();
        this.initializeCachePreview();
        this.initializeTemplatesPreview();
        this.initializeDeveloperPreview();
        this.initializeCanvasPreviews();

        pdfBuilderDebug('All previews initialized with saved data');
    },

    /**
     * Preview des informations entreprise
     */
    initializeCompanyPreview: function() {
        if (!window.pdfBuilderSavedSettings) return;

        const data = window.pdfBuilderSavedSettings;

        // Mettre à jour les champs de preview entreprise
        const phoneField = document.querySelector('.company-phone-preview');
        if (phoneField && data.company_phone_manual) {
            phoneField.textContent = data.company_phone_manual;
        }

        const siretField = document.querySelector('.company-siret-preview');
        if (siretField && data.company_siret) {
            siretField.textContent = data.company_siret;
        }

        const vatField = document.querySelector('.company-vat-preview');
        if (vatField && data.company_vat) {
            vatField.textContent = data.company_vat;
        }

        const rcsField = document.querySelector('.company-rcs-preview');
        if (rcsField && data.company_rcs) {
            rcsField.textContent = data.company_rcs;
        }

        const capitalField = document.querySelector('.company-capital-preview');
        if (capitalField && data.company_capital) {
            capitalField.textContent = data.company_capital + ' €';
        }

        pdfBuilderDebug('Company preview initialized');
    },

    /**
     * Preview des paramètres PDF
     */
    initializePDFPreview: function() {
        if (!window.pdfBuilderSavedSettings) return;

        const data = window.pdfBuilderSavedSettings;

        // Mettre à jour les champs de preview PDF
        const qualityField = document.querySelector('.pdf-quality-preview');
        if (qualityField && data.pdf_quality) {
            qualityField.textContent = data.pdf_quality;
        }

        const formatField = document.querySelector('.pdf-format-preview');
        if (formatField && data.default_format) {
            formatField.textContent = data.default_format;
        }

        const orientationField = document.querySelector('.pdf-orientation-preview');
        if (orientationField && data.default_orientation) {
            orientationField.textContent = data.default_orientation;
        }

        pdfBuilderDebug('PDF preview initialized');
    },

    /**
     * Preview des paramètres cache
     */
    initializeCachePreview: function() {
        if (!window.pdfBuilderSavedSettings) return;

        const data = window.pdfBuilderSavedSettings;

        // Mettre à jour les indicateurs de cache
        const cacheEnabledIndicator = document.querySelector('.cache-enabled-indicator');
        if (cacheEnabledIndicator) {
            // Changer la couleur selon l'état du cache
            cacheEnabledIndicator.style.color = data.cache_enabled ? '#28a745' : '#dc3545';
            cacheEnabledIndicator.textContent = data.cache_enabled ? 'Activé' : 'Désactivé';
        }

        const cacheTtlField = document.querySelector('.cache-ttl-preview');
        if (cacheTtlField && data.cache_ttl) {
            cacheTtlField.textContent = data.cache_ttl + ' secondes';
        }

        const cacheCompressionField = document.querySelector('.cache-compression-preview');
        if (cacheCompressionField) {
            cacheCompressionField.textContent = data.cache_compression ? 'Activée' : 'Désactivée';
        }

        pdfBuilderDebug('Cache preview initialized');
    },

    /**
     * Preview des paramètres templates
     */
    initializeTemplatesPreview: function() {
        if (!window.pdfBuilderSavedSettings) return;

        const data = window.pdfBuilderSavedSettings;

        // Mettre à jour l'indicateur de la bibliothèque de templates
        const templateLibraryIndicator = document.querySelector('.template-library-indicator');
        if (templateLibraryIndicator) {
            // Convertir explicitement en boolean (0, "0", false deviennent false)
            const isEnabled = Boolean(data.template_library_enabled && data.template_library_enabled !== "0" && data.template_library_enabled !== 0);

            templateLibraryIndicator.style.background = isEnabled ? '#28a745' : '#dc3545';
            templateLibraryIndicator.textContent = isEnabled ? 'ACTIF' : 'INACTIF';
        }

        pdfBuilderDebug('Templates preview initialized');
    },

    /**
     * Preview des paramètres développeur
     */
    initializeDeveloperPreview: function() {
        if (!window.pdfBuilderSavedSettings) return;

        const data = window.pdfBuilderSavedSettings;

        // Mettre à jour les indicateurs développeur
        const debugModeIndicator = document.querySelector('.debug-mode-indicator');
        if (debugModeIndicator) {
            // Changer la couleur selon l'état du mode debug
            debugModeIndicator.style.color = data.debug_mode ? '#28a745' : '#dc3545';
            debugModeIndicator.textContent = data.debug_mode ? 'Activé' : 'Désactivé';
        }

        const developerEnabledIndicator = document.querySelector('.developer-enabled-indicator');
        if (developerEnabledIndicator) {
            // Changer la couleur selon l'état du mode développeur
            developerEnabledIndicator.style.color = data.developer_enabled ? '#28a745' : '#dc3545';
            developerEnabledIndicator.textContent = data.developer_enabled ? 'Activé' : 'Désactivé';
        }

        // Mettre à jour l'indicateur de statut du mode développeur
        if (window.updateDeveloperStatusIndicator) {
            window.updateDeveloperStatusIndicator();
        }

        pdfBuilderDebug('Developer preview initialized');
    },

    /**
     * Initialise les previews canvas avec les données sauvegardées
     */
    initializeCanvasPreviews: function() {
        if (!window.pdfBuilderCanvasSettings) return;

        pdfBuilderDebug('Initializing canvas previews with saved settings');

        // Délai pour s'assurer que le DOM est prêt
        setTimeout(() => {
            try {
                // Initialiser les previews individuelles des cartes canvas
                if (typeof updateDimensionsCardPreview === 'function') {
                    updateDimensionsCardPreview();
                }
                if (typeof updateApparenceCardPreview === 'function') {
                    updateApparenceCardPreview();
                }
                if (typeof updateInteractionsCardPreview === 'function') {
                    updateInteractionsCardPreview();
                }
                if (typeof updatePerformanceCardPreview === 'function') {
                    updatePerformanceCardPreview();
                }
                if (typeof updateZoomCardPreview === 'function') {
                    updateZoomCardPreview();
                }
                if (typeof updateGridCardPreview === 'function') {
                    updateGridCardPreview();
                }
                if (typeof updateAutosaveCardPreview === 'function') {
                    updateAutosaveCardPreview();
                }

                pdfBuilderDebug('Canvas previews initialized successfully');
            } catch (error) {
                pdfBuilderError('Error initializing canvas previews:', error);
            }
        }, 100);
    }
};


// Update zoom card preview
window.updateZoomCardPreview = function() {
    pdfBuilderDebug('updateZoomCardPreview called');
    try {
        // Try to get values from modal inputs first (real-time), then from settings
        const minZoomInput = document.getElementById("zoom_min");
        const maxZoomInput = document.getElementById("zoom_max");
        const defaultZoomInput = document.getElementById("zoom_default");
        const stepZoomInput = document.getElementById("zoom_step");

        const minZoom = minZoomInput ? parseInt(minZoomInput.value) : (window.pdfBuilderCanvasSettings?.min_zoom || window.pdfBuilderCanvasSettings?.default_zoom_min || 10);
        const maxZoom = maxZoomInput ? parseInt(maxZoomInput.value) : (window.pdfBuilderCanvasSettings?.max_zoom || window.pdfBuilderCanvasSettings?.default_zoom_max || 500);
        const defaultZoom = defaultZoomInput ? parseInt(defaultZoomInput.value) : (window.pdfBuilderCanvasSettings?.default_zoom || 100);
        const stepZoom = stepZoomInput ? parseInt(stepZoomInput.value) : (window.pdfBuilderCanvasSettings?.zoom_step || 25);

        pdfBuilderDebug('zoom values - min:', minZoom, 'max:', maxZoom, 'default:', defaultZoom, 'step:', stepZoom);

        // Update zoom level display
        const zoomLevel = document.querySelector('.zoom-level');
        if (zoomLevel) {
            zoomLevel.textContent = defaultZoom + '%';
            pdfBuilderDebug('Updated zoom level to:', defaultZoom + '%');
        } else {
            pdfBuilderDebug('zoomLevel element not found');
        }

        // Update zoom info
        const zoomInfo = document.querySelector('.zoom-info');
        if (zoomInfo) {
            zoomInfo.innerHTML = '<span>' + minZoom + '% - ' + maxZoom + '%</span><span>Pas: ' + stepZoom + '%</span>';
            pdfBuilderDebug('Updated zoom info');
        } else {
            pdfBuilderDebug('zoomInfo element not found');
        }

        pdfBuilderDebug('updateZoomCardPreview completed successfully');
    } catch (error) {
        pdfBuilderError('Error in updateZoomCardPreview:', error);
    }
};

// Tab switching functionality
function initializeTabs() {
    const tabs = document.querySelectorAll('.nav-tab');
    const contents = document.querySelectorAll('.tab-content');

    // First, hide ALL tab contents
    contents.forEach(function(content) {
        content.classList.remove('active');
        content.style.display = 'none';
    });

    // Add click listeners to tabs
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function(e) {
            if (window.pdfBuilderDebugSettings?.settings_page) {
                console.log('PDF Builder: ===== TAB CLICK START =====');
                console.log('PDF Builder: Tab clicked - event:', e);
                console.log('PDF Builder: Tab element:', this);
                console.log('PDF Builder: Tab href:', this.getAttribute('href'));
            }

            e.preventDefault();
            if (window.pdfBuilderDebugSettings?.settings_page) {
                console.log('PDF Builder: preventDefault() called');
                console.log('PDF Builder: Removing nav-tab-active from all tabs');
            }

            if (window.pdfBuilderDebugSettings?.settings_page) {
                console.log('PDF Builder: Adding nav-tab-active to clicked tab');
                console.log('PDF Builder: Before adding class, clicked tab classList:', this.classList.toString());
            }
            // Add active class to clicked tab
            this.classList.add('nav-tab-active');
            if (window.pdfBuilderDebugSettings?.settings_page) {
                console.log('PDF Builder: After adding class, clicked tab classList:', this.classList.toString());
                console.log('PDF Builder: Processing tab contents');
            }
            // Hide all tab contents
            contents.forEach(function(c) {
                c.classList.remove('active');
                c.style.display = 'none';
            });

            // Show corresponding tab content
            const target = this.getAttribute('href').substring(1);
            const targetContent = document.getElementById(target);

            if (targetContent) {
                targetContent.classList.add('active');
                targetContent.style.display = 'block';
            } else {
                if (window.pdfBuilderDebugSettings?.settings_page) {
                    console.error('PDF Builder: Target content not found for id:', target);
                    console.log('PDF Builder: Available content IDs:', Array.from(contents).map(c => c.id));
                }
            }

            if (window.pdfBuilderDebugSettings?.settings_page) {
                console.log('PDF Builder: ===== TAB CLICK END =====');
            }

            // Update canvas previews when switching to contenu tab
            if (target === 'contenu') {
                if (window.pdfBuilderDebugSettings?.settings_page) {
                    console.log('PDF Builder: Switching to contenu tab, updating canvas previews');
                }
                try {
                    if (window.CanvasPreviewManager && typeof window.CanvasPreviewManager.updatePreviews === 'function') {
                        setTimeout(function() {
                            window.CanvasPreviewManager.updatePreviews('all');
                        }, 200);
                    } else if (window.updateCanvasPreviews) {
                        setTimeout(function() {
                            window.updateCanvasPreviews('all');
                        }, 200);
                    }
                    // Also initialize templates preview when switching to contenu tab
                    if (window.PDF_Builder_Preview_Manager && typeof window.PDF_Builder_Preview_Manager.initializeTemplatesPreview === 'function') {
                        setTimeout(function() {
                            window.PDF_Builder_Preview_Manager.initializeTemplatesPreview();
                        }, 200);
                    }
                } catch (error) {
                    if (window.pdfBuilderDebugSettings?.settings_page) {
                        console.error('PDF Builder: Error updating canvas previews on tab switch:', error);
                    }
                }
            }

            // Update URL hash without scrolling
            if (window.pdfBuilderDebugSettings?.settings_page) {
                console.log('PDF Builder: Updating URL hash to:', '#' + target);
            }
            history.replaceState(null, null, '#' + target);

            // Update floating save button text
            updateFloatingSaveButtonText(target);
        });
    });

    // Check hash on load and initialize tabs properly
    const hash = window.location.hash.substring(1);
    let targetTab = 'general'; // Default tab

    if (hash) {
        const tabExists = document.querySelector('.nav-tab[href="#' + hash + '"]');
        if (tabExists) {
            targetTab = hash;
        }
    }

    // Set active tab and content without triggering click events
    const activeTab = document.querySelector('.nav-tab[href="#' + targetTab + '"]');
    const activeContent = document.getElementById(targetTab);

    if (activeTab && activeContent) {
        // Remove active classes from all tabs and contents
        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.classList.remove('nav-tab-active');
        });
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
            content.style.display = 'none';
        });

        // Add active classes to target tab and content
        activeTab.classList.add('nav-tab-active');
        activeContent.classList.add('active');
        activeContent.style.display = 'block';

        if (window.pdfBuilderDebugSettings?.settings_page) {
            console.log('PDF Builder: Tab initialization completed successfully');
        }

        // Update mobile menu text
        const currentTabText = document.querySelector('.current-tab-text');
        if (currentTabText) {
            const tabText = activeTab.querySelector('.tab-text');
            if (tabText) {
                currentTabText.textContent = tabText.textContent;
            }
        }

        // Update floating save button text
        updateFloatingSaveButtonText(targetTab);

        // Log final state after initialization
        if (window.pdfBuilderDebugSettings?.settings_page) {
            console.log('PDF Builder: ===== INITIALIZATION COMPLETE =====');
            console.log('PDF Builder: Active tab after init:', document.querySelector('.nav-tab-active'));
            console.log('PDF Builder: Active content after init:', document.querySelector('.tab-content.active'));
            console.log('PDF Builder: All tabs after init:', Array.from(document.querySelectorAll('.nav-tab')).map(tab => ({
                href: tab.getAttribute('href'),
                classes: tab.classList.toString(),
                isActive: tab.classList.contains('nav-tab-active')
            })));
            console.log('PDF Builder: All contents after init:', Array.from(document.querySelectorAll('.tab-content')).map(content => ({
                id: content.id,
                classes: content.classList.toString(),
                isActive: content.classList.contains('active'),
                display: window.getComputedStyle(content).display,
                visibility: window.getComputedStyle(content).visibility
            })));
            console.log('PDF Builder: ===== END INITIALIZATION =====');
        }
}

if (window.pdfBuilderDebugSettings?.javascript) {
    console.log('PDF Builder: About to add DOMContentLoaded listener');
    console.log('PDF Builder: Document readyState:', document.readyState);
    console.log('PDF Builder: Window loaded:', window.loaded);
}

// Make initializeTabs globally accessible
window.initializeTabs = initializeTabs;

function updateSecurityStatusIndicators() {
    // Mettre à jour l'indicateur de sécurité (enable_logging)
    const enableLoggingCheckbox = document.getElementById('enable_logging');
    const securityStatus = document.getElementById('security-status-indicator');
    if (enableLoggingCheckbox && securityStatus) {
        const isActive = enableLoggingCheckbox.checked;
        securityStatus.textContent = isActive ? 'ACTIF' : 'INACTIF';
        securityStatus.style.background = isActive ? '#28a745' : '#dc3545';
    }

    // Mettre à jour l'indicateur RGPD (gdpr_enabled)
    const gdprEnabledCheckbox = document.getElementById('gdpr_enabled');
    const rgpdStatus = document.getElementById('rgpd-status-indicator');
    if (gdprEnabledCheckbox && rgpdStatus) {
        const isActive = gdprEnabledCheckbox.checked;
        rgpdStatus.textContent = isActive ? 'ACTIF' : 'INACTIF';
        rgpdStatus.style.background = isActive ? '#28a745' : '#dc3545';
    }

    // Mettre à jour les indicateurs système
    updateSystemStatusIndicators();
}

// Fonction pour mettre à jour les indicateurs des templates assignés
function updateTemplateStatusIndicators() {
    // Parcourir tous les selects de templates
    const templateSelects = document.querySelectorAll('.template-select');
    
    templateSelects.forEach(select => {
        const selectValue = select.value;
        const selectId = select.id;
        
        // Trouver le conteneur parent (.template-status-card)
        const card = select.closest('.template-status-card');
        if (!card) return;
        
        // Trouver la section preview dans cette card
        const previewDiv = card.querySelector('.template-preview');
        if (!previewDiv) return;
        
        // Créer ou mettre à jour l'indicateur
        if (selectValue && selectValue !== '') {
            // Template assigné - récupérer le texte de l'option sélectionnée
            const selectedOption = select.querySelector('option[value="' + selectValue.replace(/"/g, '\\"') + '"]');
            const templateName = selectedOption ? selectedOption.textContent.trim() : 'Template inconnu';
            
            previewDiv.innerHTML = '<p class="current-template"><strong>Assigné :</strong> ' + templateName.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '<span class="assigned-badge">✅</span></p>';
        } else {
            // Aucun template assigné
            previewDiv.innerHTML = '<p class="no-template">Aucun template assigné</p>';
        }
    });
}

// Fonction pour mettre à jour l'indicateur de bibliothèque de templates
function updateTemplateLibraryIndicator() {
    const templateLibraryCheckbox = document.getElementById('template_library_enabled');
    const indicator = document.getElementById('template-library-indicator');
    
    if (templateLibraryCheckbox && indicator) {
        const isActive = templateLibraryCheckbox.checked;
        indicator.textContent = isActive ? 'ACTIF' : 'INACTIF';
        indicator.style.background = isActive ? '#28a745' : '#dc3545';
    }
}

// Fonction pour mettre à jour les indicateurs ACTIF/INACTIF dans l'onglet Système
function updateSystemStatusIndicators() {
    // Indicateur Cache & Performance
    const cacheEnabledCheckbox = document.getElementById('general_cache_enabled');
    const cacheStatus = document.querySelector('.cache-performance-status');
    if (cacheEnabledCheckbox && cacheStatus) {
        const isActive = cacheEnabledCheckbox.checked;
        cacheStatus.textContent = isActive ? 'ACTIF' : 'INACTIF';
        cacheStatus.style.background = isActive ? '#28a745' : '#dc3545';
    }

    // Indicateur Maintenance automatique
    const maintenanceCheckbox = document.getElementById('systeme_auto_maintenance');
    const maintenanceStatus = document.querySelector('.maintenance-status');
    if (maintenanceCheckbox && maintenanceStatus) {
        const isActive = maintenanceCheckbox.checked;
        maintenanceStatus.textContent = isActive ? 'ACTIF' : 'INACTIF';
        maintenanceStatus.style.background = isActive ? '#28a745' : '#dc3545';
    }

    // Indicateur Sauvegarde automatique
    const backupCheckbox = document.getElementById('systeme_auto_backup');
    const backupStatus = document.querySelector('.backup-status');
    if (backupCheckbox && backupStatus) {
        const isActive = backupCheckbox.checked;
        backupStatus.textContent = isActive ? 'ACTIF' : 'INACTIF';
        backupStatus.style.background = isActive ? '#28a745' : '#dc3545';
    }
}

// Fonction pour gérer l'activation/désactivation des contrôles RGPD
function toggleRGPDControls() {
    const gdprEnabledCheckbox = document.getElementById('gdpr_enabled');
    const isEnabled = gdprEnabledCheckbox ? gdprEnabledCheckbox.checked : false;

    // Liste des contrôles à désactiver/activer
    const controlsToToggle = [
        'gdpr_consent_required',
        'gdpr_data_retention',
        'gdpr_audit_enabled',
        'gdpr_encryption_enabled',
        'gdpr_consent_analytics',
        'gdpr_consent_templates',
        'gdpr_consent_marketing',
        'export-format',
        'export-my-data',
        'delete-my-data',
        'view-consent-status',
        'refresh-audit-log',
        'export-audit-log'
    ];

    // Désactiver/activer chaque contrôle
    controlsToToggle.forEach(controlId => {
        const control = document.getElementById(controlId);
        if (control) {
            control.disabled = !isEnabled;

            // Ajouter/enlever une classe CSS pour le style visuel
            if (isEnabled) {
                control.classList.remove('gdpr-disabled');
            } else {
                control.classList.add('gdpr-disabled');
            }

            // Pour les labels de toggle, désactiver aussi le parent label
            if (control.type === 'checkbox') {
                const label = control.closest('label');
                if (label) {
                    if (isEnabled) {
                        label.classList.remove('gdpr-disabled');
                    } else {
                        label.classList.add('gdpr-disabled');
                    }
                }
            }
        }
    });

    // Désactiver/activer les sections entières (actions utilisateur et logs)
    const gdprSections = document.querySelectorAll('.gdpr-section');
    gdprSections.forEach(section => {
        if (isEnabled) {
            section.classList.remove('gdpr-disabled-section');
        } else {
            section.classList.add('gdpr-disabled-section');
        }
    });
}

// Make functions globally accessible
window.updateSecurityStatusIndicators = updateSecurityStatusIndicators;
window.updateTemplateStatusIndicators = updateTemplateStatusIndicators;
window.updateTemplateLibraryIndicator = updateTemplateLibraryIndicator;
window.updateSystemStatusIndicators = updateSystemStatusIndicators;
window.toggleRGPDControls = toggleRGPDControls;

// Update floating save button text based on active tab
function updateFloatingSaveButtonText(activeTabId) {
    const floatingSaveBtn = document.getElementById('floating-save-btn');
    if (!floatingSaveBtn) return;

    const btnTextSpan = floatingSaveBtn.querySelector('.btn-text');
    if (!btnTextSpan) return;

    // Map tab IDs to button text
    const tabTextMap = {
        'general': 'Enregistrer Général',
        'licence': 'Enregistrer Licence',
        'systeme': 'Enregistrer Système',
        'acces': 'Enregistrer Accès',
        'securite': 'Enregistrer Sécurité',
        'pdf': 'Enregistrer PDF',
        'contenu': 'Enregistrer Canvas',
        'templates': 'Enregistrer Templates',
        'developpeur': 'Enregistrer Développeur'
    };

    // Update button text
    const newText = tabTextMap[activeTabId] || 'Enregistrer Tout';
    btnTextSpan.textContent = newText;

    if (window.pdfBuilderDebugSettings?.javascript) {
        console.log(`[FLOATING SAVE] Button text updated to: "${newText}" for tab: ${activeTabId}`);
    }
}

// Make updateFloatingSaveButtonText globally accessible
window.updateFloatingSaveButtonText = updateFloatingSaveButtonText;
}


(function() {
    'use strict';

    /**
     * Système centralisé de gestion des réponses AJAX avec gestion des nonces
     */
    window.PDF_Builder_Ajax_Handler = {
        // Configuration du système de nonce
        config: {
            nonceTTL: 20 * 60 * 1000, // 20 minutes (WordPress default est 24h, mais on est prudent)
            refreshThreshold: 5 * 60 * 1000, // Rafraîchir 5 minutes avant expiration
            maxRetries: 2, // Nombre maximum de tentatives
            retryDelay: 1000, // Délai entre tentatives (ms)
            preloadCount: 3, // Nombre de nonces à précharger
            enableProactiveRefresh: true, // Rafraîchissement proactif
            enableRetry: true // Retry automatique activé
        },

        // État du système de nonce
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

        /**
         * Initialise le système de nonce avancé
         */
        initialize: function() {
            if (window.pdfBuilderDebugSettings?.javascript) {
                console.log('[PDF Builder] Initialisation du système de nonce avancé');
            }

            // Initialiser avec le nonce actuel
            this.nonceState.current = window.pdfBuilderAjax?.nonce;
            this.nonceState.created = Date.now();
            this.nonceState.expires = Date.now() + this.config.nonceTTL;

            // Démarrer le rafraîchissement proactif
            if (this.config.enableProactiveRefresh) {
                this.startProactiveRefresh();
            }

            // Précharger des nonces
            this.preloadNonces();

            if (window.pdfBuilderDebugSettings?.javascript) {
                console.log('[PDF Builder] Système de nonce initialisé:', {
                    current: this.nonceState.current ? '***' : null,
                    expires: new Date(this.nonceState.expires).toLocaleTimeString(),
                    proactiveRefresh: this.config.enableProactiveRefresh
                });
            }
        },

        /**
         * Démarre le rafraîchissement proactif des nonces
         */
        startProactiveRefresh: function() {
            if (this.nonceState.refreshTimer) {
                clearTimeout(this.nonceState.refreshTimer);
            }

            const timeUntilRefresh = Math.max(0, this.nonceState.expires - Date.now() - this.config.refreshThreshold);

            this.nonceState.refreshTimer = setTimeout(() => {
                if (window.pdfBuilderDebugSettings?.javascript) {
                    console.log('[PDF Builder] Rafraîchissement proactif du nonce');
                }
                this.refreshNonce().then(() => {
                    // Redémarrer le timer pour le prochain rafraîchissement
                    this.startProactiveRefresh();
                }).catch(error => {
                    if (window.pdfBuilderDebugSettings?.javascript) {
                        console.error('Erreur lors du rafraîchissement proactif:', error);
                    }
                    // Redémarrer quand même
                    this.startProactiveRefresh();
                });
            }, timeUntilRefresh);

            if (window.pdfBuilderDebugSettings?.javascript) {
                console.log('[PDF Builder] Prochain rafraîchissement dans ' + Math.round(timeUntilRefresh / 1000 / 60) + ' minutes');
            }
        },

        /**
         * Précharge plusieurs nonces pour éviter les appels répétés
         */
        preloadNonces: function() {
            if (this.nonceState.preloadQueue.length >= this.config.preloadCount) {
                return; // Déjà assez de nonces
            }

            if (window.pdfBuilderDebugSettings?.javascript) {
                console.log('[PDF Builder] Préchargement de ' + this.config.preloadCount - this.nonceState.preloadQueue.length + ' nonces');
            }

            // Faire une requête simple pour obtenir un nouveau nonce
            const formData = new FormData();
            formData.append('action', 'pdf_builder_get_fresh_nonce');

            fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data && data.data.nonce) {
                    this.nonceState.preloadQueue.push({
                        nonce: data.data.nonce,
                        created: Date.now(),
                        expires: Date.now() + this.config.nonceTTL
                    });

                    // Nettoyer les nonces expirés
                    this.nonceState.preloadQueue = this.nonceState.preloadQueue.filter(n =>
                        n.expires > Date.now()
                    );

                    if (window.pdfBuilderDebugSettings?.javascript) {
                        console.log('[PDF Builder] Nonce préchargé (' + this.nonceState.preloadQueue.length + '/' + this.config.preloadCount + ')');
                    }

                    // Continuer le préchargement si nécessaire
                    if (this.nonceState.preloadQueue.length < this.config.preloadCount) {
                        setTimeout(() => this.preloadNonces(), 100);
                    }
                }
            })
            .catch(error => {
                if (window.pdfBuilderDebugSettings?.javascript) {
                    console.warn('Erreur lors du préchargement de nonce:', error);
                }
            });
        },

        /**
         * Rafraîchit le nonce actuel
         */
        refreshNonce: function() {
            return new Promise((resolve, reject) => {
                // Utiliser un nonce préchargé si disponible
                if (this.nonceState.preloadQueue.length > 0) {
                    const freshNonce = this.nonceState.preloadQueue.shift();
                    this.setCurrentNonce(freshNonce.nonce, freshNonce.created);
                    this.nonceState.stats.refreshes++;
                    if (window.pdfBuilderDebugSettings?.javascript) {
                        console.log('[PDF Builder] Nonce rafraîchi depuis le cache');
                    }
                    resolve();
                    return;
                }

                // Sinon, faire une requête pour obtenir un nouveau nonce
                const formData = new FormData();
                formData.append('action', 'pdf_builder_get_fresh_nonce');

                fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data && data.data.nonce) {
                        this.setCurrentNonce(data.data.nonce, Date.now());
                        this.nonceState.stats.refreshes++;
                        if (window.pdfBuilderDebugSettings?.javascript) {
                            console.log('[PDF Builder] Nonce rafraîchi depuis le serveur');
                        }
                        resolve();
                    } else {
                        reject(new Error('Impossible d\'obtenir un nouveau nonce'));
                    }
                })
                .catch(reject);
            });
        },

        /**
         * Définit le nonce actuel
         */
        setCurrentNonce: function(nonce, created = Date.now()) {
            this.nonceState.current = nonce;
            this.nonceState.created = created;
            this.nonceState.expires = created + this.config.nonceTTL;

            // Mettre à jour la variable globale
            if (window.pdfBuilderAjax) {
                window.pdfBuilderAjax.nonce = nonce;
            }
        },

        /**
         * Vérifie si le nonce actuel est proche de l'expiration
         */
        isNonceExpiringSoon: function() {
            return (this.nonceState.expires - Date.now()) < this.config.refreshThreshold;
        },

        /**
         * Nettoie les ressources du système de nonce
         */
        cleanup: function() {
            if (this.nonceState.refreshTimer) {
                clearTimeout(this.nonceState.refreshTimer);
                this.nonceState.refreshTimer = null;
            }
            this.nonceState.preloadQueue = [];
        },

        /**
         * Force un rafraîchissement immédiat du nonce
         */
        forceRefresh: function() {
            if (window.pdfBuilderDebugSettings?.javascript) {
                console.log('[PDF Builder] Rafraîchissement forcé du nonce');
            }
            return this.refreshNonce();
        },

        /**
         * Configure le système de nonce
         */
        configure: function(newConfig) {
            Object.assign(this.config, newConfig);
            if (window.pdfBuilderDebugSettings?.javascript) {
                console.log('[PDF Builder] Configuration du système de nonce mise à jour:', this.config);
            }
        },

        /**
         * Effectue une requête AJAX avec gestion automatique des nonces
         */
        makeRequest: function(formData, options = {}) {
            const self = this;
            return new Promise((resolve, reject) => {
                // Options par défaut
                const defaultOptions = {
                    button: null,
                    context: 'Unknown',
                    successCallback: null,
                    errorCallback: null,
                    retryCount: 0
                };

                const opts = Object.assign({}, defaultOptions, options);

                // Mettre à jour le bouton si fourni
                if (opts.button) {
                    this.setButtonState(opts.button, 'loading');
                }

                // S'assurer que nous avons un nonce valide
                this.ensureValidNonce().then(() => {
                    // Ajouter le nonce aux données
                    if (formData instanceof FormData) {
                        formData.set('nonce', this.nonceState.current);
                    } else if (typeof formData === 'object') {
                        formData.nonce = this.nonceState.current;
                    }

                    if (window.pdfBuilderDebugSettings?.javascript) {
                        console.log('[PDF Builder AJAX] ' + opts.context + ' - Making request with nonce: ' + (this.nonceState.current ? this.nonceState.current.substring(0, 10) + '...' : 'NULL'));
                    }

                    // Faire la requête
                    fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
                        method: 'POST',
                        body: formData,
                        headers: { 
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-WP-Nonce': this.nonceState.current
                        }
                    })
                    .then(response => {
                        if (window.pdfBuilderDebugSettings?.javascript) {
                            console.log('[PDF Builder AJAX] ' + opts.context + ' - Response status: ' + response.status);
                        }
                        return response.json().catch(() => {
                            // Si la réponse n'est pas du JSON valide, créer une erreur
                            throw new Error('Invalid JSON response from server');
                        });
                    })
                    .then(data => {
                        if (window.pdfBuilderDebugSettings?.javascript) {
                            console.log('[PDF Builder AJAX] ' + opts.context + ' - Response:', data);
                        }

                        if (data.success) {
                            // Succès
                            if (opts.button && opts.context !== 'PDF Builder') {
                                this.setButtonState(opts.button, 'success');
                            }
                            if (opts.successCallback) {
                                // Assurer que l'instance de notifications existe
                                if (!window.pdfBuilderNotificationsInstance && window.PDF_Builder_Notifications) {
                                    window.pdfBuilderNotificationsInstance = new window.PDF_Builder_Notifications();
                                }
                                opts.successCallback.call(window.pdfBuilderNotificationsInstance || window, data, data);
                            }
                            resolve(data);
                        } else {
                            // Erreur côté serveur
                            const errorMessage = typeof data.data === 'string' ? data.data : JSON.stringify(data.data) || 'Unknown error';

                            // Vérifier si c'est une erreur de nonce
                            if (errorMessage.includes('Nonce invalide') || errorMessage.includes('invalid nonce')) {
                                this.nonceState.stats.nonceErrors++;

                                // Essayer de rafraîchir le nonce et réessayer
                                if (opts.retryCount < this.config.maxRetries) {
                                    if (window.pdfBuilderDebugSettings?.javascript) {
                                        console.log('[PDF Builder AJAX] ' + opts.context + ' - Nonce error, retrying (' + (opts.retryCount + 1) + '/' + this.config.maxRetries + ')');
                                    }
                                    opts.retryCount++;
                                    this.forceRefresh().then(() => {
                                        // Réessayer avec le nouveau nonce
                                        setTimeout(() => {
                                            this.makeRequest(formData, opts).then(resolve).catch(reject);
                                        }, this.config.retryDelay);
                                    }).catch(() => {
                                        // Échec du rafraîchissement, échouer
                                        if (opts.button) {
                                            this.setButtonState(opts.button, 'error');
                                        }
                                        if (opts.errorCallback) {
                                            // Assurer que l'instance de notifications existe
                                            if (!window.pdfBuilderNotificationsInstance && window.PDF_Builder_Notifications) {
                                                window.pdfBuilderNotificationsInstance = new window.PDF_Builder_Notifications();
                                            }
                                            opts.errorCallback.call(window.pdfBuilderNotificationsInstance || window, data, data);
                                        }
                                        reject(new Error(errorMessage));
                                    });
                                    return;
                                }
                            }

                            // Erreur normale
                            if (opts.button) {
                                this.setButtonState(opts.button, 'error');
                            }
                            if (opts.errorCallback) {
                                // Assurer que l'instance de notifications existe
                                if (!window.pdfBuilderNotificationsInstance && window.PDF_Builder_Notifications) {
                                    window.pdfBuilderNotificationsInstance = new window.PDF_Builder_Notifications();
                                }
                                opts.errorCallback.call(window.pdfBuilderNotificationsInstance || window, data, data);
                            }
                            reject(new Error(errorMessage));
                        }
                    })
                    .catch(error => {
                        if (window.pdfBuilderDebugSettings?.javascript) {
                            console.error('[PDF Builder AJAX] ' + opts.context + ' - Network error:', error);
                        }
                        this.nonceState.stats.requests++;

                        // Erreur réseau
                        if (opts.button) {
                            this.setButtonState(opts.button, 'error');
                        }
                        if (opts.errorCallback) {
                            // Assurer que l'instance de notifications existe
                            if (!window.pdfBuilderNotificationsInstance && window.PDF_Builder_Notifications) {
                                window.pdfBuilderNotificationsInstance = new window.PDF_Builder_Notifications();
                            }
                            opts.errorCallback.call(window.pdfBuilderNotificationsInstance || window, {error: error.message}, {error: error.message});
                        }
                        reject(error);
                    });
                }).catch(error => {
                    if (window.pdfBuilderDebugSettings?.javascript) {
                        console.error('[PDF Builder AJAX] ' + opts.context + ' - Nonce validation failed:', error);
                    }
                    if (opts.button) {
                        this.setButtonState(opts.button, 'error');
                    }
                    reject(error);
                });
            });
        },

        /**
         * S'assure qu'un nonce valide est disponible
         */
        ensureValidNonce: function() {
            return new Promise((resolve) => {
                if (this.nonceState.current && !this.isNonceExpiringSoon()) {
                    resolve();
                } else {
                    this.refreshNonce().then(resolve).catch(() => {
                        // En cas d'échec du rafraîchissement, utiliser le nonce actuel s'il existe
                        if (this.nonceState.current) {
                            resolve();
                        } else {
                            throw new Error('Unable to obtain valid nonce');
                        }
                    });
                }
            });
        },

        /**
         * Définit l'état d'un bouton - VERSION SIMPLIFIÉE
         */
        setButtonState: function(button, state) {
            if (!button) return;

            const originalText = button.getAttribute('data-original-text') || button.textContent;

            switch (state) {
                case 'loading':
                    button.setAttribute('data-original-text', originalText);
                    button.disabled = true;
                    button.innerHTML = '<span class="dashicons dashicons-update spin"></span> Chargement...';
                    button.style.opacity = '0.7';
                    break;
                case 'success':
                    // Suppression de l'état "succès" - retour direct au texte original
                    button.disabled = false;
                    button.innerHTML = originalText;
                    button.style.opacity = '1';
                    button.removeAttribute('data-original-text');
                    break;
                case 'error':
                    button.disabled = false;
                    button.innerHTML = '<span class="dashicons dashicons-no"></span> Erreur';
                    button.style.opacity = '1';
                    // Reset après 2 secondes au lieu de 3
                    setTimeout(() => this.setButtonState(button, 'reset'), 2000);
                    break;
                case 'reset':
                default:
                    button.disabled = false;
                    button.innerHTML = originalText;
                    button.style.opacity = '1';
                    button.removeAttribute('data-original-text');
                    break;
            }
        },

        /**
         * Obtient les statistiques du système de nonce
         */
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
    
        /**
         * Méthode show pour la compatibilité avec les successCallback qui utilisent this.show
         */
        show: function(message) {
            if (window.showSuccessNotification) {
                window.showSuccessNotification(message);
            } else if (window.pdfBuilderNotificationsInstance && window.pdfBuilderNotificationsInstance.success) {
                window.pdfBuilderNotificationsInstance.success(message);
            }
        }
    };

    // Initialiser le système de nonce avancé au chargement
    document.addEventListener('DOMContentLoaded', function() {
        PDF_Builder_Ajax_Handler.initialize();
    });

    // Nettoyer à la fermeture de la page
    window.addEventListener('beforeunload', function() {
        PDF_Builder_Ajax_Handler.cleanup();
    });

    // Exposer les statistiques globalement pour le debug
    window.pdfBuilderNonceStats = function() {
        return PDF_Builder_Ajax_Handler.getStats();
    };

    // Méthode show globale pour la compatibilité
    window.show = function(message) {
        if (window.pdfBuilderNotificationsInstance && window.pdfBuilderNotificationsInstance.show) {
            window.pdfBuilderNotificationsInstance.show(message, 'info');
        }
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
        // Hide all modals by default
        const allModals = safeQuerySelectorAll('.canvas-modal');
        allModals.forEach(hideModal);

        // Basic event delegation for modals
        document.addEventListener('click', function(event) {
            const target = event.target;

            // Handle configure buttons
            if (target.closest('.canvas-configure-btn')) {
                event.preventDefault();
                const button = target.closest('.canvas-configure-btn');
                const card = button.closest('.canvas-card');
                if (!card) return;

                const category = card.getAttribute('data-category');
                if (!category) return;

                const modalId = 'canvas-' + category + '-modal';
                const modal = document.getElementById(modalId);
                if (!modal) return;

                showModal(modal);
            }

            // Handle close buttons
            if (target.closest('.canvas-modal-close, .canvas-modal-cancel')) {
                const modal = target.closest('.canvas-modal');
                if (modal) hideModal(modal);
            }

            // Handle modal background click
            if (target.classList.contains('canvas-modal') || target.classList.contains('canvas-modal-overlay')) {
                hideModal(target.closest('.canvas-modal'));
            }

            // Handle save buttons
            if (target.closest('.canvas-modal-save')) {
                event.preventDefault();
                const saveButton = target.closest('.canvas-modal-save');
                const modal = saveButton.closest('.canvas-modal');
                const category = saveButton.getAttribute('data-category');

                if (!modal || !category) return;

                // Disable button and show loading state
                saveButton.disabled = true;
                const originalText = saveButton.textContent;
                saveButton.textContent = 'Sauvegarde...';
                saveButton.style.opacity = '0.7';

                // Collect form data
                const form = modal.querySelector('form');
                if (!form) {
                    if (window.pdfBuilderDebugSettings?.javascript) {
                        console.error('No form found in modal for category:', category);
                    }
                    saveButton.disabled = false;
                    saveButton.textContent = originalText;
                    saveButton.style.opacity = '1';
                    return;
                }

                const formData = new FormData(form);
                formData.append('action', 'pdf_builder_save_canvas_settings');
                formData.append('nonce', window.pdfBuilderCanvasSettings?.nonce || '');
                formData.append('category', category);

                // Make AJAX request using centralized handler
                formData.append('action', 'pdf_builder_save_canvas_settings');
                formData.append('category', category);

                PDF_Builder_Ajax_Handler.makeRequest(formData, {
                    button: saveButton,
                    context: 'Canvas Modal',
                    successCallback: (result, originalData) => {
                        // Update canvas settings in window object
                        if (originalData.data && originalData.data.result_data) {
                            // Update window.pdfBuilderCanvasSettings with new values
                            Object.assign(window.pdfBuilderCanvasSettings, originalData.data.result_data);

                            // Update previews
                            if (typeof window.updateCanvasPreviews === 'function') {
                                window.updateCanvasPreviews(category);
                            }

                            // Update PDF_Builder_Preview_Manager if available
                            if (window.PDF_Builder_Preview_Manager && typeof window.PDF_Builder_Preview_Manager.initializeAllPreviews === 'function') {
                                window.PDF_Builder_Preview_Manager.initializeAllPreviews();
                            }
                        }

                        // Close modal after short delay
                        setTimeout(() => {
                            hideModal(modal);
                            PDF_Builder_Ajax_Handler.setButtonState(saveButton, 'reset');
                        }, 1500);
                    }
                }).catch(error => {
                    // Error already handled by the centralized handler
                    console.error('Canvas modal save error:', error);
                });
            }

            // Handle cache modal save buttons
            if (target.closest('.cache-modal-save')) {
                event.preventDefault();
                const saveButton = target.closest('.cache-modal-save');
                const modal = saveButton.closest('.canvas-modal');
                const category = saveButton.getAttribute('data-category') || 'cache';

                if (!modal) return;

                // Disable button and show loading state
                saveButton.disabled = true;
                const originalText = saveButton.textContent;
                saveButton.textContent = 'Sauvegarde...';
                saveButton.style.opacity = '0.7';

                // Collect form data
                const form = modal.querySelector('form');
                if (!form) {
                    if (window.pdfBuilderDebugSettings?.javascript) {
                        console.error('No form found in cache modal');
                    }
                    saveButton.disabled = false;
                    saveButton.textContent = originalText;
                    saveButton.style.opacity = '1';
                    return;
                }

                const formData = new FormData(form);
                formData.append('action', 'pdf_builder_save_cache_settings');
                formData.append('nonce', window.pdfBuilderAjax?.nonce || '');
                formData.append('category', category);

                // Make AJAX request using centralized handler
                formData.append('action', 'pdf_builder_save_cache_settings');
                formData.append('category', category);

                PDF_Builder_Ajax_Handler.makeRequest(formData, {
                    button: saveButton,
                    context: 'Cache Modal',
                    successCallback: (result, originalData) => {
                        // Close modal after short delay
                        setTimeout(() => {
                            hideModal(modal);
                            PDF_Builder_Ajax_Handler.setButtonState(saveButton, 'reset');
                        }, 1500);
                    }
                }).catch(error => {
                    // Error already handled by the centralized handler
                    console.error('Cache modal save error:', error);
                });
            }

            // Handle clear cache buttons
            if (target.closest('.clear-cache-from-modal, #clear-cache-from-modal')) {
                event.preventDefault();
                const clearButton = target.closest('.clear-cache-from-modal, #clear-cache-from-modal');
                const modal = clearButton.closest('.canvas-modal');

                // Disable button and show loading state
                clearButton.disabled = true;
                const originalText = clearButton.textContent;
                clearButton.textContent = 'Nettoyage...';
                clearButton.style.opacity = '0.7';

                // Make AJAX request using centralized handler
                const formData = new FormData();
                formData.append('action', 'pdf_builder_clear_cache');

                PDF_Builder_Ajax_Handler.makeRequest(formData, {
                    button: clearButton,
                    context: 'Clear Cache',
                    successCallback: (result, originalData) => {
                        // Reset button after delay
                        setTimeout(() => {
                            PDF_Builder_Ajax_Handler.setButtonState(clearButton, 'reset');
                        }, 3000);
                    }
                }).catch(error => {
                    // Error already handled by the centralized handler
                    console.error('Clear cache error:', error);
                });
            }

            // Handle perform cleanup buttons
            if (target.closest('.perform-cleanup-btn, #perform-cleanup-btn')) {
                event.preventDefault();
                const cleanupButton = target.closest('.perform-cleanup-btn, #perform-cleanup-btn');
                const modal = cleanupButton.closest('.canvas-modal');

                // Disable button and show loading state
                cleanupButton.disabled = true;
                const originalText = cleanupButton.textContent;
                cleanupButton.textContent = 'Nettoyage...';
                cleanupButton.style.opacity = '0.7';

                // Make AJAX request using centralized handler
                const formData = new FormData();
                formData.append('action', 'pdf_builder_remove_temp_files');

                PDF_Builder_Ajax_Handler.makeRequest(formData, {
                    button: cleanupButton,
                    context: 'Cleanup',
                    successCallback: (result, originalData) => {
                        // Reset button after delay
                        setTimeout(() => {
                            PDF_Builder_Ajax_Handler.setButtonState(cleanupButton, 'reset');
                        }, 3000);
                    }
                }).catch(error => {
                    // Error already handled by the centralized handler
                    console.error('Cleanup error:', error);
                });
            }
        });

        // Handle escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const visibleModals = safeQuerySelectorAll('.canvas-modal[style*="display: flex"]');
                visibleModals.forEach(hideModal);
            }
        });
    }

    // Basic preview update functions
    window.updateCanvasPreviews = function(category) {
        // Simplified preview updates
        if (typeof window.updateDimensionsCardPreview === 'function') {
            window.updateDimensionsCardPreview();
        }
        if (typeof window.updateApparenceCardPreview === 'function') {
            window.updateApparenceCardPreview();
        }
        if (typeof window.updateGrilleCardPreview === 'function') {
            window.updateGrilleCardPreview();
        }
        if (typeof window.updateInteractionsCardPreview === 'function') {
            window.updateInteractionsCardPreview();
        }
        if (typeof window.updateExportCardPreview === 'function') {
            window.updateExportCardPreview();
        }
        if (typeof window.updatePerformanceCardPreview === 'function') {
            window.updatePerformanceCardPreview();
        }
        if (typeof window.updateAutosaveCardPreview === 'function') {
            window.updateAutosaveCardPreview();
        }
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

    // Initialize floating save button
    function initializeFloatingSaveButton() {
        const floatingSaveBtn = document.getElementById('floating-save-btn');
        if (!floatingSaveBtn) return;

        floatingSaveBtn.addEventListener('click', function(e) {
            e.preventDefault();

            // Collect all form data from all tabs
            const formData = new FormData();

            // Add action and nonce
            formData.append('action', 'pdf_builder_save_all_settings');
            formData.append('nonce', window.pdfBuilderAjax?.nonce || '');

            // Get current active tab to determine context
            const activeTab = document.querySelector('.nav-tab-active');
            const currentTab = activeTab ? activeTab.getAttribute('href').substring(1) : 'general';
            formData.append('current_tab', currentTab);

            // Collect data from all visible forms and inputs across all tabs
            // This ensures we save data from all tabs, not just the active one
            const allInputs = document.querySelectorAll('input, select, textarea');
            let collectedCount = 0;
            let developerFields = 0;

            allInputs.forEach(input => {
                // Skip buttons, hidden fields we don't want, and disabled inputs
                if (input.type === 'button' || input.type === 'submit' || input.type === 'reset' ||
                    input.name === '' || input.disabled) {
                    return;
                }

                // Handle different input types
                if (input.type === 'checkbox') {
                    formData.append(input.name, input.checked ? '1' : '0');
                    collectedCount++;
                    if (input.name.includes('developer') || input.name.includes('debug') || input.name.includes('log')) {
                        developerFields++;
                        if (window.pdfBuilderDebugSettings?.javascript) {
                            console.log('[FLOATING SAVE] Developer checkbox: ' + input.name + ' = ' + (input.checked ? '1' : '0'));
                        }
                    }
                } else if (input.type === 'radio') {
                    if (input.checked) {
                        formData.append(input.name, input.value);
                        collectedCount++;
                    }
                } else {
                    formData.append(input.name, input.value || '');
                    collectedCount++;
                    if (input.name.includes('developer') || input.name.includes('debug') || input.name.includes('log')) {
                        developerFields++;
                        if (window.pdfBuilderDebugSettings?.javascript) {
                            console.log('[FLOATING SAVE] Developer field: ' + input.name + ' = ' + (input.value || ''));
                        }
                    }
                }
            });

            if (window.pdfBuilderDebugSettings?.javascript) {
                console.log('[FLOATING SAVE] Total inputs collected: ' + collectedCount);
                console.log('[FLOATING SAVE] Developer fields collected: ' + developerFields);
                console.log('[FLOATING SAVE] Current tab: ' + currentTab);
            }

            // Make AJAX request using centralized handler
            PDF_Builder_Ajax_Handler.makeRequest(formData, {
                button: floatingSaveBtn,
                context: 'Floating Save Button',
                successCallback: function(result, originalData) {
                    // Update previews after successful save
                    if (window.PDF_Builder_Preview_Manager && typeof window.PDF_Builder_Preview_Manager.initializeAllPreviews === 'function') {
                        window.PDF_Builder_Preview_Manager.initializeAllPreviews();
                    }

                    // Update canvas previews if on contenu tab
                    if (currentTab === 'contenu' && typeof window.updateCanvasPreviews === 'function') {
                        window.updateCanvasPreviews('all');
                    }

                    // Update status indicators
                    if (typeof window.updateSecurityStatusIndicators === 'function') {
                        window.updateSecurityStatusIndicators();
                    }
                    if (typeof window.updateTemplateStatusIndicators === 'function') {
                        window.updateTemplateStatusIndicators();
                    }
                    if (typeof window.updateSystemStatusIndicators === 'function') {
                        window.updateSystemStatusIndicators();
                    }
                    if (typeof window.updateTemplateLibraryIndicator === 'function') {
                        window.updateTemplateLibraryIndicator();
                    }
                }
            }).catch(error => {
                console.error('Floating save error:', error);
            });
        });
    }

    // Update floating save button text based on active tab
    function updateFloatingSaveButtonText(activeTabId) {
        const floatingSaveBtn = document.getElementById('floating-save-btn');
        if (!floatingSaveBtn) return;

        const btnTextSpan = floatingSaveBtn.querySelector('.btn-text');
        if (!btnTextSpan) return;

        // Map tab IDs to button text
        const tabTextMap = {
            'general': 'Enregistrer Général',
            'licence': 'Enregistrer Licence',
            'systeme': 'Enregistrer Système',
            'acces': 'Enregistrer Accès',
            'securite': 'Enregistrer Sécurité',
            'pdf': 'Enregistrer PDF',
            'contenu': 'Enregistrer Canvas',
            'templates': 'Enregistrer Templates',
            'developpeur': 'Enregistrer Développeur'
        };

        // Update button text
        const newText = tabTextMap[activeTabId] || 'Enregistrer Tout';
        btnTextSpan.textContent = newText;

        if (window.pdfBuilderDebugSettings?.javascript) {
            console.log('[FLOATING SAVE] Button text updated to: "' + newText + '" for tab: ' + activeTabId);
        }
    }
})();

