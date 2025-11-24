dal._originalAutosaveSettings.versionsLimit;
                    
                    // Update preview with restored values
                    if (typeof updateAutosaveCardPreview === 'function') {
                        updateAutosaveCardPreview();
                    }
                }
                delete modal._originalAutosaveSettings;
            }

            modal.style.setProperty('display', 'none', 'important');
        } catch (e) {
            
        }
    }

    function showModal(modal) {
        if (!modal) return false;

        try {
            const success = applyModalStyles(modal);

            if (success) {
                // Initialize event listeners for this modal
                initializeModalEventListeners(modal);

                // Synchronize modal values with current settings for apparence modal
                if (modal.getAttribute('data-category') === 'apparence') {
                    synchronizeApparenceModalValues(modal);
                }

                // Synchronize modal values with current settings for interactions modal
                if (modal.getAttribute('data-category') === 'interactions') {
                    synchronizeInteractionsModalValues(modal);
                }

                // Synchronize modal values with current settings for autosave modal
                if (modal.getAttribute('data-category') === 'autosave') {
                    synchronizeAutosaveModalValues(modal);
                }

                // Verify modal is visible after a short delay
                setTimeout(() => {
                    const rect = modal.getBoundingClientRect();
                    const isVisible = rect.width > 0 && rect.height > 0;

                    if (!isVisible) {
                        
                    }
                }, 100);
            }

            return success;
        } catch (e) {
            
            return false;
        }
    }

    function initializeModals() {
        if (isInitialized) return;

        try {
            // Hide all modals by default
            const allModals = safeQuerySelectorAll('.canvas-modal');
            allModals.forEach(hideModal);

            // Use event delegation for better stability
            document.addEventListener('click', function(event) {
                const target = event.target;

                // Handle configure buttons
                if (target.closest('.canvas-configure-btn')) {
                    event.preventDefault();
                    event.stopPropagation();

                    const button = target.closest('.canvas-configure-btn');
                    const card = button.closest('.canvas-card');

                    if (!card) {
                        
                        return;
                    }

                    const category = card.getAttribute('data-category');
                    if (!category) {
                        
                        return;
                    }

                    const modalId = 'canvas-' + category + '-modal';
                    const modal = document.getElementById(modalId);

                    if (!modal) {
                        
                        return;
                    }

                    
                    const success = showModal(modal);

                    if (success) {
                        // Modal opened successfully - update values from database
                        
                        // Always refresh modal values from database when opening
                        if (typeof updateCanvasPreviews === 'function') {
                            updateCanvasPreviews(category);
                        }
                    }
                }

                // Handle close buttons
                if (target.closest('.canvas-modal-close, .canvas-modal-cancel')) {
                    const modal = target.closest('.canvas-modal');
                    if (modal) {
                        hideModal(modal);
                    }
                }

                // Handle modal background click to close
                if (target.classList.contains('canvas-modal') || target.classList.contains('canvas-modal-overlay')) {
                    hideModal(target.closest('.canvas-modal'));
                }
            }, true); // Use capture phase for better event handling

            // Handle escape key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    const visibleModals = safeQuerySelectorAll('.canvas-modal[style*="display: flex"]');
                    visibleModals.forEach(hideModal);
                }
            });

            // Handle save buttons with proper error handling
            const saveButtons = safeQuerySelectorAll('.canvas-modal-save');
            saveButtons.forEach(function(button) {
                button.addEventListener('click', function(event) {
                    event.preventDefault();

                    const modal = this.closest('.canvas-modal');
                    const category = this.getAttribute('data-category');
                    const form = modal ? modal.querySelector('form') : null;

                    if (!form) {
                        alert('Erreur: Formulaire non trouvé');
                        return;
                    }

                    // Get AJAX config with fallbacks
                    let ajaxConfig = null;
                    if (typeof pdf_builder_ajax !== 'undefined') {
                        ajaxConfig = pdf_builder_ajax;
                    } else if (typeof pdfBuilderAjax !== 'undefined') {
                        ajaxConfig = pdfBuilderAjax;
                    } else if (typeof ajaxurl !== 'undefined') {
                        ajaxConfig = { ajax_url: ajaxurl, nonce: '' };
                    }

                    if (!ajaxConfig || !ajaxConfig.ajax_url) {
                        alert('Erreur de configuration AJAX: variables AJAX non trouvées');
                        
                        return;
                    }

                    // Collect form data safely
                    let formData;
                    try {
                        formData = new FormData(form);
                        formData.append('action', 'pdf_builder_save_canvas_settings');
                        formData.append('category', category || '');
                        formData.append('nonce', ajaxConfig.nonce || '');

                        // Debug: Log form data
                        console.log('PDF_BUILDER_DEBUG: Form data for category', category + ':');
                        for (let [key, value] of formData.entries()) {
                            console.log('PDF_BUILDER_DEBUG:', key, '=', value);
                        }

                        

                    } catch (e) {
                        
                        alert('Erreur lors de la préparation des données');
                        return;
                    }

                    // Show loading state
                    const originalText = this.textContent;
                    this.textContent = 'Sauvegarde...';
                    this.disabled = true;

                    // Send AJAX request with timeout
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 second timeout

                    fetch(ajaxConfig.ajax_url, {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin',
                        signal: controller.signal
                    })
                    .then(response => {
                        clearTimeout(timeoutId);
                        console.log('PDF_BUILDER_DEBUG: AJAX response status:', response.status);
                        if (!response.ok) {
                            throw new Error('HTTP ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('PDF_BUILDER_DEBUG: AJAX response data:', data);
                        
                        if (data.success) {
                            hideModal(modal);
                            this.textContent = originalText;
                            this.disabled = false;

                            // Clear original settings since save was successful
                            delete modal._originalDimensionsSettings;
                            delete modal._originalApparenceSettings;
                            delete modal._originalInteractionsSettings;
                            delete modal._originalAutosaveSettings;

                            // Update window.pdfBuilderCanvasSettings with saved values for dimensions
                            if (category === 'dimensions' && data.data && data.data.saved) {
                                if (data.data.saved.canvas_width) {
                                    window.pdfBuilderCanvasSettings.canvas_width = parseInt(data.data.saved.canvas_width);
                                }
                                if (data.data.saved.canvas_height) {
                                    window.pdfBuilderCanvasSettings.canvas_height = parseInt(data.data.saved.canvas_height);
                                }
                                if (data.data.saved.canvas_format) {
                                    window.pdfBuilderCanvasSettings.default_canvas_format = data.data.saved.canvas_format;
                                }
                                if (data.data.saved.canvas_dpi) {
                                    window.pdfBuilderCanvasSettings.default_canvas_dpi = parseInt(data.data.saved.canvas_dpi);
                                }
                                if (data.data.saved.canvas_orientation) {
                                    window.pdfBuilderCanvasSettings.default_canvas_orientation = data.data.saved.canvas_orientation;
                                }

                                // Déclencher l'événement pour mettre à jour l'éditeur React
                                const event = new CustomEvent('pdfBuilderUpdateCanvasDimensions', {
                                    detail: {
                                        width: parseInt(data.data.saved.canvas_width),
                                        height: parseInt(data.data.saved.canvas_height)
                                    }
                                });
                                document.dispatchEvent(event);
                            }

                            // Update window.pdfBuilderCanvasSettings with saved values for apparence
                            if (category === 'apparence' && data.data && data.data.saved) {
                                if (data.data.saved.canvas_bg_color !== undefined) {
                                    window.pdfBuilderCanvasSettings.canvas_background_color = data.data.saved.canvas_bg_color;
                                }
                                if (data.data.saved.canvas_border_color !== undefined) {
                                    window.pdfBuilderCanvasSettings.border_color = data.data.saved.canvas_border_color;
                                }
                                if (data.data.saved.canvas_border_width !== undefined) {
                                    window.pdfBuilderCanvasSettings.border_width = parseInt(data.data.saved.canvas_border_width);
                                }
                                if (data.data.saved.canvas_shadow_enabled !== undefined) {
                                    window.pdfBuilderCanvasSettings.shadow_enabled = data.data.saved.canvas_shadow_enabled === '1' || data.data.saved.canvas_shadow_enabled === true;
                                }
                                if (data.data.saved.canvas_container_bg_color !== undefined) {
                                    window.pdfBuilderCanvasSettings.container_background_color = data.data.saved.canvas_container_bg_color;
                                }
                            }

                            // Update window.pdfBuilderCanvasSettings with saved values for performance
                            if (category === 'performance' && data.data && data.data.saved) {
                                if (data.data.saved.canvas_fps_target !== undefined) {
                                    window.pdfBuilderCanvasSettings.fps_target = parseInt(data.data.saved.canvas_fps_target);
                                }
                                if (data.data.saved.canvas_memory_limit_js !== undefined) {
                                    window.pdfBuilderCanvasSettings.memory_limit_js = parseInt(data.data.saved.canvas_memory_limit_js);
                                }
                                if (data.data.saved.canvas_memory_limit_php !== undefined) {
                                    window.pdfBuilderCanvasSettings.memory_limit_php = parseInt(data.data.saved.canvas_memory_limit_php);
                                }
                                if (data.data.saved.canvas_lazy_loading_editor !== undefined) {
                                    window.pdfBuilderCanvasSettings.lazy_loading_editor = data.data.saved.canvas_lazy_loading_editor === '1' || data.data.saved.canvas_lazy_loading_editor === true;
                                }
                                if (data.data.saved.canvas_lazy_loading_plugin !== undefined) {
                                    window.pdfBuilderCanvasSettings.lazy_loading_plugin = data.data.saved.canvas_lazy_loading_plugin === '1' || data.data.saved.canvas_lazy_loading_plugin === true;
                                }
                            }

                            // Update window.pdfBuilderCanvasSettings with saved values for autosave
                            if (category === 'autosave' && data.data && data.data.saved) {
                                if (data.data.saved.canvas_autosave_enabled !== undefined) {
                                    window.pdfBuilderCanvasSettings.autosave_enabled = data.data.saved.canvas_autosave_enabled === '1' || data.data.saved.canvas_autosave_enabled === true;
                                }
                                if (data.data.saved.canvas_autosave_interval !== undefined) {
                                    window.pdfBuilderCanvasSettings.autosave_interval = parseInt(data.data.saved.canvas_autosave_interval);
                                }
                                if (data.data.saved.canvas_versions_limit !== undefined) {
                                    window.pdfBuilderCanvasSettings.versions_limit = parseInt(data.data.saved.canvas_versions_limit);
                                }
                                if (data.data.saved.canvas_history_max !== undefined) {
                                    window.pdfBuilderCanvasSettings.versions_limit = parseInt(data.data.saved.canvas_history_max);
                                }
                            }

                            // Update window.pdfBuilderCanvasSettings with saved values for export
                            if (category === 'export' && data.data && data.data.saved) {
                                if (data.data.saved.canvas_export_format !== undefined) {
                                    window.pdfBuilderCanvasSettings.export_format = data.data.saved.canvas_export_format;
                                }
                                if (data.data.saved.canvas_export_quality !== undefined) {
                                    window.pdfBuilderCanvasSettings.export_quality = parseInt(data.data.saved.canvas_export_quality);
                                }
                            }

                            // Update window.pdfBuilderCanvasSettings with saved values for zoom
                            if (category === 'zoom' && data.data && data.data.saved) {
                                if (data.data.saved.zoom_min !== undefined) {
                                    window.pdfBuilderCanvasSettings.min_zoom = parseInt(data.data.saved.zoom_min);
                                }
                                if (data.data.saved.zoom_max !== undefined) {
                                    window.pdfBuilderCanvasSettings.max_zoom = parseInt(data.data.saved.zoom_max);
                                }
                                if (data.data.saved.zoom_default !== undefined) {
                                    window.pdfBuilderCanvasSettings.default_zoom = parseInt(data.data.saved.zoom_default);
                                }
                                if (data.data.saved.zoom_step !== undefined) {
                                    window.pdfBuilderCanvasSettings.zoom_step = parseInt(data.data.saved.zoom_step);
                                }
                            }

                            // Update window.pdfBuilderCanvasSettings with saved values for grille
                            if (category === 'grille' && data.data && data.data.saved) {
                                if (data.data.saved.canvas_grid_enabled !== undefined) {
                                    window.pdfBuilderCanvasSettings.show_grid = data.data.saved.canvas_grid_enabled === '1' || data.data.saved.canvas_grid_enabled === true;
                                }
                                if (data.data.saved.canvas_grid_size !== undefined) {
                                    window.pdfBuilderCanvasSettings.grid_size = parseInt(data.data.saved.canvas_grid_size);
                                }
                                if (data.data.saved.canvas_snap_to_grid !== undefined) {
                                    window.pdfBuilderCanvasSettings.snap_to_grid = data.data.saved.canvas_snap_to_grid === '1' || data.data.saved.canvas_snap_to_grid === true;
                                }
                            }

                            // Update window.pdfBuilderCanvasSettings with saved values for interactions
                            if (category === 'interactions' && data.data && data.data.saved) {
                                console.log('PDF_BUILDER_DEBUG: Updating interactions settings with:', data.data.saved);
                                if (data.data.saved.canvas_drag_enabled !== undefined) {
                                    window.pdfBuilderCanvasSettings.drag_enabled = data.data.saved.canvas_drag_enabled === '1' || data.data.saved.canvas_drag_enabled === true;
                                }
                                if (data.data.saved.canvas_resize_enabled !== undefined) {
                                    window.pdfBuilderCanvasSettings.resize_enabled = data.data.saved.canvas_resize_enabled === '1' || data.data.saved.canvas_resize_enabled === true;
                                }
                                if (data.data.saved.canvas_rotate_enabled !== undefined) {
                                    window.pdfBuilderCanvasSettings.rotate_enabled = data.data.saved.canvas_rotate_enabled === '1' || data.data.saved.canvas_rotate_enabled === true;
                                }
                                if (data.data.saved.canvas_multi_select !== undefined) {
                                    window.pdfBuilderCanvasSettings.multi_select = data.data.saved.canvas_multi_select === '1' || data.data.saved.canvas_multi_select === true;
                                }
                                if (data.data.saved.canvas_selection_mode !== undefined) {
                                    window.pdfBuilderCanvasSettings.selection_mode = data.data.saved.canvas_selection_mode;
                                }
                                if (data.data.saved.canvas_keyboard_shortcuts !== undefined) {
                                    window.pdfBuilderCanvasSettings.keyboard_shortcuts = data.data.saved.canvas_keyboard_shortcuts === '1' || data.data.saved.canvas_keyboard_shortcuts === true;
                                }
                                console.log('PDF_BUILDER_DEBUG: Updated window.pdfBuilderCanvasSettings:', window.pdfBuilderCanvasSettings);
                            }

                            // Update canvas previews after successful save
                            if (category === 'dimensions' && typeof updateDimensionsCardPreview === 'function') {
                                setTimeout(function() {
                                    updateDimensionsCardPreview();
                                }, 100);
                            }
                            if (category === 'apparence' && typeof updateApparenceCardPreview === 'function') {
                                setTimeout(function() {
                                    updateApparenceCardPreview();
                                }, 100);
                            }
                            if (category === 'performance' && typeof updatePerformanceCardPreview === 'function') {
                                setTimeout(function() {
                                    updatePerformanceCardPreview();
                                }, 100);
                            }
                            if (category === 'autosave' && typeof updateAutosaveCardPreview === 'function') {
                                setTimeout(function() {
                                    updateAutosaveCardPreview();
                                }, 100);
                            }
                            if (category === 'export' && typeof updateExportCardPreview === 'function') {
                                setTimeout(function() {
                                    updateExportCardPreview();
                                }, 100);
                            }
                            if (category === 'zoom' && typeof updateZoomCardPreview === 'function') {
                                setTimeout(function() {
                                    updateZoomCardPreview();
                                }, 100);
                            }
                            if (category === 'grille' && typeof updateGrilleCardPreview === 'function') {
                                setTimeout(function() {
                                    updateGrilleCardPreview();
                                }, 100);
                            }
                            if (category === 'interactions' && typeof updateInteractionsCardPreview === 'function') {
                                setTimeout(function() {
                                    updateInteractionsCardPreview();
                                }, 100);
                            }
                        } else {
                            const errorMessage = (data.data && data.data.message) || 'Unknown error during save';
                            if (window.pdfBuilderNotifications) {
                                if (window.pdfBuilderNotifications.showToast) {
                                    window.pdfBuilderNotifications.showToast('Save error: ' + errorMessage, 'error', 6000);
                                }
                            }
                            if (window.PDF_Builder_Notification_Manager) {
                                if (window.PDF_Builder_Notification_Manager.show_toast) {
               