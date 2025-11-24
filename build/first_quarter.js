
// Update zoom card preview
window.updateZoomCardPreview = function() {
    // Try to get values from modal inputs first (real-time), then from settings
    const minZoomInput = document.getElementById("zoom_min");
    const maxZoomInput = document.getElementById("zoom_max");
    const defaultZoomInput = document.getElementById("zoom_default");
    const stepZoomInput = document.getElementById("zoom_step");

    const minZoom = minZoomInput ? parseInt(minZoomInput.value) : (window.pdfBuilderCanvasSettings?.min_zoom || window.pdfBuilderCanvasSettings?.default_zoom_min || 10);
    const maxZoom = maxZoomInput ? parseInt(maxZoomInput.value) : (window.pdfBuilderCanvasSettings?.max_zoom || window.pdfBuilderCanvasSettings?.default_zoom_max || 500);
    const defaultZoom = defaultZoomInput ? parseInt(defaultZoomInput.value) : (window.pdfBuilderCanvasSettings?.default_zoom || 100);
    const stepZoom = stepZoomInput ? parseInt(stepZoomInput.value) : (window.pdfBuilderCanvasSettings?.zoom_step || 25);

    // Update zoom level display
    const zoomLevel = document.querySelector('.zoom-level');
    if (zoomLevel) {
        zoomLevel.textContent = `${defaultZoom}%`;
    }

    // Update zoom info
    const zoomInfo = document.querySelector('.zoom-info');
    if (zoomInfo) {
        zoomInfo.innerHTML = `
            <span>${minZoom}% - ${maxZoom}%</span>
            <span>Pas: ${stepZoom}%</span>
        `;
    }
};

// Tab switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.nav-tab');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function(e) {
            e.preventDefault();

            // Remove active class from all tabs
            tabs.forEach(function(t) {
                t.classList.remove('nav-tab-active');
            });
            // Add active class to clicked tab
            this.classList.add('nav-tab-active');

            // Hide all tab contents
            contents.forEach(function(c) {
                c.classList.remove('active');
            });
            // Show corresponding tab content
            const target = this.getAttribute('href').substring(1);
            document.getElementById(target).classList.add('active');

            // Update canvas previews when switching to contenu tab
            if (target === 'contenu' && typeof updateCanvasPreviews === 'function') {
                setTimeout(function() {
                    updateCanvasPreviews('all');
                }, 50);
            }

            // Update URL hash without scrolling
            history.replaceState(null, null, '#' + target);
        });
    });

    // Check hash on load
    const hash = window.location.hash.substring(1);
    if (hash) {
        const tab = document.querySelector('.nav-tab[href="#' + hash + '"]');
        if (tab) {
            tab.click();
        }
    } else {
        const defaultTab = document.querySelector('.nav-tab[href="#general"]');
        if (defaultTab) {
            defaultTab.click();
        }
    }

    // Fonction pour mettre à jour les indicateurs ACTIF/INACTIF dans l'onglet Sécurité
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
                const selectedOption = select.querySelector(`option[value="${selectValue}"]`);
                const templateName = selectedOption ? selectedOption.textContent : 'Template inconnu';
                
                previewDiv.innerHTML = `
                    <p class="current-template">
                        <strong>Assigné :</strong> ${templateName}
                        <span class="assigned-badge">✓</span>
                    </p>
                `;
            } else {
                // Aucun template assigné
                previewDiv.innerHTML = `
                    <p class="no-template">Aucun template assigné</p>
                `;
            }
        });
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

    // Initialiser les indicateurs au chargement de la page
    updateSecurityStatusIndicators();
    toggleRGPDControls();

    // Ajouter un event listener pour le toggle RGPD principal
    const gdprEnabledCheckbox = document.getElementById('gdpr_enabled');
    if (gdprEnabledCheckbox) {
        gdprEnabledCheckbox.addEventListener('change', function() {
            toggleRGPDControls();
            // Removed: updateSecurityStatusIndicators(); // Ne plus mettre à jour l'indicateur lors du toggle
        });
    }

    // Gestion du bouton flottant de sauvegarde
    const floatingSaveBtn = document.getElementById('floating-save-btn');
    if (floatingSaveBtn) {
        floatingSaveBtn.addEventListener('click', function() {
            // Vérifier que pdf_builder_ajax est défini
            if (typeof pdf_builder_ajax === 'undefined') {
                alert('Erreur: Configuration AJAX manquante. Actualisez la page.');
                return;
            }

            // Changer l'apparence du bouton pendant la sauvegarde
            const originalText = '<span class="save-icon">💾</span><span class="save-text">Enregistrer</span>'; // Texte fixe original
            floatingSaveBtn.innerHTML = '<span class="save-icon">⏳</span><span class="save-text">Sauvegarde...</span>';
            floatingSaveBtn.classList.add('saving');

            // Timeout de sécurité : remettre le bouton à l'état normal après 5 secondes maximum
            const safetyTimeout = setTimeout(() => {
                floatingSaveBtn.innerHTML = '<span class="save-icon">💾</span><span class="save-text">Enregistrer</span>';
                floatingSaveBtn.classList.remove('saving', 'saved', 'error');
            }, 5000);

            // Collecter les données de tous les formulaires
            const formData = new FormData();

            // Ajouter l'action AJAX
            formData.append('action', 'pdf_builder_save_settings');
            formData.append('nonce', pdf_builder_ajax?.nonce || '');
            formData.append('current_tab', 'all'); // Sauvegarder tous les onglets

            // Collecter les données de TOUS les formulaires (pas seulement visibles)
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                const formInputs = form.querySelectorAll('input, select, textarea');

                // Collecter d'abord les checkboxes multiples (arrays)
                const checkboxArrays = {};
                formInputs.forEach(input => {
                    if (input.type === 'checkbox' && input.name && input.name.endsWith('[]')) {
                        const baseName = input.name.slice(0, -2); // Retirer []
                        if (!checkboxArrays[baseName]) {
                            checkboxArrays[baseName] = [];
                        }
                        // Inclure même les checkboxes disabled si elles sont checked
                        if (input.checked) {
                            checkboxArrays[baseName].push(input.value);
                        }
                    }
                });

                // Ajouter les arrays de checkboxes
                Object.keys(checkboxArrays).forEach(name => {
                    if (checkboxArrays[name].length > 0) {
                        checkboxArrays[name].forEach(value => {
                            formData.append(name + '[]', value);
                        });
                    } else {
                        // Si aucun checkbox coché, envoyer un array vide
                        formData.append(name + '[]', '');
                    }
                });

                // Collecter les autres inputs
                formInputs.forEach(input => {
                    if (input.name && input.type !== 'submit' && input.type !== 'button' &&
                        input.name !== 'action' && input.name !== 'nonce' && input.name !== 'current_tab' &&
                        !input.name.endsWith('[]')) { // Ne pas traiter les arrays ici
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
            });

            // Envoyer la requête AJAX
            fetch(pdf_builder_ajax.ajax_url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                clearTimeout(safetyTimeout); // Annuler le timeout de sécurité

                if (data.success) {
                    // Succès
                    floatingSaveBtn.innerHTML = '<span class="save-icon">✅</span><span class="save-text">Sauvegardé !</span>';
                    floatingSaveBtn.classList.remove('saving');
                    floatingSaveBtn.classList.add('saved');

                    // Mettre à jour les indicateurs ACTIF/INACTIF dans l'onglet Sécurité & Conformité
                    updateSecurityStatusIndicators();

                    // Mettre à jour l'état des contrôles RGPD
                    toggleRGPDControls();

                    // Mettre à jour les indicateurs des templates assignés
                    updateTemplateStatusIndicators();

                    // Remettre le texte original après 2 secondes
                    setTimeout(() => {
                        floatingSaveBtn.innerHTML = '<span class="save-icon">💾</span><span class="save-text">Enregistrer</span>';
                        floatingSaveBtn.classList.remove('saved');
                    }, 2000);

                    // Notification de succès déjà gérée par le changement d'apparence du bouton
                    // La notification popup a été supprimée pour éviter les doublons
                } else {
                    // Erreur
                    
                    const errorMessage = data.data && data.data.message ? data.data.message : 'Erreur inconnue';
                    
                    alert('Erreur de sauvegarde: ' + errorMessage);
                    floatingSaveBtn.innerHTML = '<span class="save-icon">❌</span><span class="save-text">Erreur</span>';
                    floatingSaveBtn.classList.remove('saving');
                    floatingSaveBtn.classList.add('error');

                    // Remettre le texte original après 3 secondes
                    setTimeout(() => {
                        floatingSaveBtn.innerHTML = '<span class="save-icon">💾</span><span class="save-text">Enregistrer</span>';
                        floatingSaveBtn.classList.remove('error');
                    }, 3000);

                    // Afficher l'erreur
                    if (data.data && data.data.message) {
                        alert('Erreur de sauvegarde: ' + data.data.message);
                    }
                }
            })
            .catch(error => {
                
                clearTimeout(safetyTimeout); // Annuler le timeout de sécurité
                floatingSaveBtn.innerHTML = '<span class="save-icon">❌</span><span class="save-text">Erreur</span>';
                floatingSaveBtn.classList.remove('saving');
                floatingSaveBtn.classList.add('error');

                setTimeout(() => {
                    floatingSaveBtn.innerHTML = '<span class="save-icon">💾</span><span class="save-text">Enregistrer</span>';
                    floatingSaveBtn.classList.remove('error');
                }, 3000);

                alert('Erreur de connexion lors de la sauvegarde');
            });
        });
    } else {
        
    }

    // Initialize zoom card preview with real values
    updateZoomCardPreview();

    // Initialize all canvas card previews with real values
    // Use setTimeout to ensure window.pdfBuilderCanvasSettings is loaded
    setTimeout(function() {
        if (window.updateCanvasPreviews) {
            window.updateCanvasPreviews('all');
        }
    }, 100);
});

// Canvas configuration modals functionality - Version stable
(function() {
    'use strict';

    // Configuration
    const MODAL_Z_INDEX = 2147483647; // Maximum z-index possible (signed 32-bit integer)
    const MODAL_CONFIG = {
        display: 'flex',
        visibility: 'visible',
        opacity: '1',
        position: 'fixed',
        top: '0',
        left: '0',
        width: '100%',
        height: '100%',
        background: 'rgba(0,0,0,0.7)',
        'z-index': MODAL_Z_INDEX.toString()
    };

    let isInitialized = false;

    // Utility functions
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

    function applyModalStyles(modal) {
        if (!modal) return false;

        try {
            // Reset all styles first
            modal.style.cssText = '';

            // Apply configuration with !important
            Object.keys(MODAL_CONFIG).forEach(property => {
                modal.style.setProperty(property, MODAL_CONFIG[property], 'important');
            });

            // Additional safety styles
            modal.style.setProperty('pointer-events', 'auto', 'important');
            modal.style.setProperty('align-items', 'center', 'important');
            modal.style.setProperty('justify-content', 'center', 'important');

            return true;
        } catch (e) {
            
            return false;
        }
    }

    function hideModal(modal) {
        if (!modal) return;
        try {
            // Restore original settings if dimensions or apparence modal was cancelled
            if (modal.getAttribute('data-category') === 'dimensions' && modal._originalDimensionsSettings) {
                if (window.pdfBuilderCanvasSettings) {
                    window.pdfBuilderCanvasSettings.default_canvas_format = modal._originalDimensionsSettings.format;
                    window.pdfBuilderCanvasSettings.default_canvas_dpi = modal._originalDimensionsSettings.dpi;
                    
                    // Update preview with restored values
                    if (typeof updateDimensionsCardPreview === 'function') {
                        updateDimensionsCardPreview();
                    }
                }
                delete modal._originalDimensionsSettings;
            }
            if (modal.getAttribute('data-category') === 'apparence' && modal._originalApparenceSettings) {
                if (window.pdfBuilderCanvasSettings) {
                    window.pdfBuilderCanvasSettings.canvas_background_color = modal._originalApparenceSettings.bgColor;
                    window.pdfBuilderCanvasSettings.border_color = modal._originalApparenceSettings.borderColor;
                    window.pdfBuilderCanvasSettings.border_width = modal._originalApparenceSettings.borderWidth;
                    window.pdfBuilderCanvasSettings.shadow_enabled = modal._originalApparenceSettings.shadowEnabled;
                    window.pdfBuilderCanvasSettings.container_background_color = modal._originalApparenceSettings.containerBgColor;
                    
                    // Update preview with restored values
                    if (typeof updateApparenceCardPreview === 'function') {
                        updateApparenceCardPreview();
                    }
                }
                delete modal._originalApparenceSettings;
            }
            if (modal.getAttribute('data-category') === 'interactions' && modal._originalInteractionsSettings) {
                if (window.pdfBuilderCanvasSettings) {
                    window.pdfBuilderCanvasSettings.drag_enabled = modal._originalInteractionsSettings.dragEnabled;
                    window.pdfBuilderCanvasSettings.resize_enabled = modal._originalInteractionsSettings.resizeEnabled;
                    window.pdfBuilderCanvasSettings.rotate_enabled = modal._originalInteractionsSettings.rotateEnabled;
                    window.pdfBuilderCanvasSettings.multi_select = modal._originalInteractionsSettings.multiSelect;
                    window.pdfBuilderCanvasSettings.selection_mode = modal._originalInteractionsSettings.selectionMode;
                    window.pdfBuilderCanvasSettings.keyboard_shortcuts = modal._originalInteractionsSettings.keyboardShortcuts;
                    
                    // Update preview with restored values
                    if (typeof updateInteractionsCardPreview === 'function') {
                        updateInteractionsCardPreview();
                    }
                }
                delete modal._originalInteractionsSettings;
            }
            if (modal.getAttribute('data-category') === 'autosave' && modal._originalAutosaveSettings) {
                if (window.pdfBuilderCanvasSettings) {
                    window.pdfBuilderCanvasSettings.autosave_enabled = modal._originalAutosaveSettings.autosaveEnabled;
                    window.pdfBuilderCanvasSettings.autosave_interval = modal._originalAutosaveSettings.autosaveInterval;
                    window.pdfBuilderCanvasSettings.versions_limit = mo