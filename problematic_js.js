
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
