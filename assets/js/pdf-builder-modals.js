// PDF Builder Pro - Modal Settings JavaScript
// Handles modal interactions and AJAX calls for the React editor

(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        initializeCanvasModals();
    });

    function initializeCanvasModals() {
        console.log('🎛️ Initializing canvas modals for React editor...');

        // Safe querySelector utility
        function safeQuerySelector(selector) {
            try {
                return document.querySelector(selector);
            } catch (e) {
                console.warn('Invalid selector:', selector);
                return null;
            }
        }

        function safeQuerySelectorAll(selector) {
            try {
                return document.querySelectorAll(selector);
            } catch (e) {
                console.warn('Invalid selector:', selector);
                return [];
            }
        }

        // Modal management functions
        function showModal(modal) {
            if (modal) {
                modal.style.display = 'flex';
                modal.classList.add('canvas-modal-visible');
                document.body.classList.add('canvas-modal-open');
            }
        }

        function hideModal(modal) {
            if (modal) {
                modal.style.display = 'none';
                modal.classList.remove('canvas-modal-visible');
                document.body.classList.remove('canvas-modal-open');
            }
        }

        // Handle modal triggers
        const modalTriggers = safeQuerySelectorAll('.canvas-modal-trigger');
        modalTriggers.forEach(function(trigger) {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                const modalId = this.getAttribute('data-modal');
                const modal = document.getElementById(modalId);
                if (modal) {
                    showModal(modal);
                }
            });
        });

        // Handle modal close buttons
        const closeButtons = safeQuerySelectorAll('.canvas-modal-close');
        closeButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const modal = this.closest('.canvas-modal');
                hideModal(modal);
            });
        });

        // Handle modal background click to close
        document.addEventListener('click', function(event) {
            const target = event.target;
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

                console.log('💾 Save button clicked for category:', this.getAttribute('data-category'));

                const modal = this.closest('.canvas-modal');
                const category = this.getAttribute('data-category');
                const form = modal ? modal.querySelector('form') : null;

                console.log('📋 Modal found:', !!modal, 'Form found:', !!form, 'Category:', category);

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
                    console.error('Available globals:', { pdf_builder_ajax: typeof pdf_builder_ajax, pdfBuilderAjax: typeof pdfBuilderAjax, ajaxurl: typeof ajaxurl });
                    return;
                }

                // Collect form data safely
                let formData;
                try {
                    formData = new FormData(form);
                    formData.append('action', 'pdf_builder_save_canvas_settings');
                    formData.append('category', category || '');
                    formData.append('nonce', ajaxConfig.nonce || '');

                    console.log('📤 Sending AJAX save request for category:', category);
                    console.log('📦 Form data keys:', Array.from(formData.keys()));

                } catch (e) {
                    console.error('❌ Error creating form data:', e);
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
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('💾 Save AJAX response received:', data);
                    if (data.success) {
                        console.log('✅ Save successful for category:', category);
                        hideModal(modal);
                        this.textContent = originalText;
                        this.disabled = false;

                        // Update previews if function exists
                        if (typeof updateCanvasPreviews === 'function') {
                            console.log('🔄 Calling updateCanvasPreviews after save');
                            updateCanvasPreviews(category);
                        }

                        // Dispatch custom event for real-time canvas updates
                        if (category === 'apparence') {
                            console.log('🎨 Dispatching appearance settings update event');

                            // Update window.pdfBuilderCanvasSettings with new values
                            if (typeof window.pdfBuilderCanvasSettings !== 'undefined') {
                                // Get the updated values from the form
                                const newSettings = {};

                                for (let [key, value] of formData.entries()) {
                                    if (key === 'canvas_shadow_enabled') {
                                        newSettings.shadow_enabled = value === '1';
                                    } else if (key === 'canvas_border_width') {
                                        newSettings.border_width = parseInt(value) || 1;
                                    } else if (key === 'canvas_bg_color') {
                                        newSettings.canvas_background_color = value;
                                    } else if (key === 'canvas_container_bg_color') {
                                        newSettings.container_background_color = value;
                                    } else if (key === 'canvas_border_color') {
                                        newSettings.border_color = value;
                                    }
                                }

                                // Update window object
                                Object.assign(window.pdfBuilderCanvasSettings, newSettings);
                                console.log('🔄 Updated window.pdfBuilderCanvasSettings:', newSettings);
                            }

                            const updateEvent = new CustomEvent('pdfBuilderCanvasSettingsUpdated', {
                                detail: { category: 'apparence' }
                            });
                            window.dispatchEvent(updateEvent);
                        }

                        // Alert supprimée selon les préférences utilisateur
                    } else {
                        console.error('❌ Save failed:', data.data?.message || 'Unknown error');
                        throw new Error(data.data?.message || 'Erreur inconnue');
                    }
                })
                .catch(error => {
                    clearTimeout(timeoutId);
                    console.error('Save error:', error);
                    this.textContent = originalText;
                    this.disabled = false;

                    if (error.name === 'AbortError') {
                        alert('Erreur: Timeout de la requête (30 secondes)');
                    } else {
                        alert('Erreur lors de la sauvegarde: ' + error.message);
                    }
                });
            });
        });

        // Function to update canvas previews after save
        window.updateCanvasPreviews = function(category) {
            // Get AJAX config
            let ajaxConfig = null;
            console.log('Available globals in updateCanvasPreviews:', { pdf_builder_ajax: typeof pdf_builder_ajax, pdfBuilderAjax: typeof pdfBuilderAjax, ajaxurl: typeof ajaxurl });
            if (typeof pdf_builder_ajax !== 'undefined') {
                ajaxConfig = pdf_builder_ajax;
                console.log('Using pdf_builder_ajax in updateCanvasPreviews:', ajaxConfig);
            } else if (typeof pdfBuilderAjax !== 'undefined') {
                ajaxConfig = pdfBuilderAjax;
                console.log('Using pdfBuilderAjax in updateCanvasPreviews:', ajaxConfig);
            } else if (typeof ajaxurl !== 'undefined') {
                ajaxConfig = { ajax_url: ajaxurl, nonce: '' };
                console.log('Using fallback ajaxurl in updateCanvasPreviews:', ajaxConfig);
            }

            if (!ajaxConfig || !ajaxConfig.ajax_url) {
                return;
            }

            // Make AJAX call to get updated values
            fetch(ajaxConfig.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'action': 'pdf_builder_get_canvas_settings',
                    'category': category,
                    'nonce': ajaxConfig.nonce || ''
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    updateModalValues(category, data.data);
                }
            })
            .catch(error => {
                console.error('Error updating previews:', error);
            });
        };

        // Function to update modal values in DOM
        function updateModalValues(category, values) {
            const modalId = `canvas-${category}-modal`;
            const modal = document.getElementById(modalId);
            if (!modal) {
                return;
            }

            // Update values based on category
            switch (category) {
                case 'grille':
                    updateGrilleModal(modal, values);
                    break;
                case 'dimensions':
                    updateDimensionsModal(modal, values);
                    break;
                case 'zoom':
                    updateZoomModal(modal, values);
                    break;
                case 'apparence':
                    updateApparenceModal(modal, values);
                    break;
                case 'interactions':
                    updateInteractionsModal(modal, values);
                    break;
                case 'export':
                    updateExportModal(modal, values);
                    break;
                case 'performance':
                    updatePerformanceModal(modal, values);
                    break;
                case 'autosave':
                    updateAutosaveModal(modal, values);
                    break;
                case 'debug':
                    updateDebugModal(modal, values);
                    break;
                default:
                    console.warn('⚠️ Unknown category:', category);
            }
        }

        // Update apparence modal values
        function updateApparenceModal(modal, values) {
            console.log('🎨 Updating apparence modal with values:', values);

            // Update canvas background color
            const canvasBgColorInput = modal.querySelector('#canvas_bg_color');
            if (canvasBgColorInput && values.canvas_bg_color) {
                canvasBgColorInput.value = values.canvas_bg_color;
                console.log('✅ Updated canvas_bg_color:', values.canvas_bg_color);
            }

            // Update container background color
            const containerBgColorInput = modal.querySelector('#canvas_container_bg_color');
            if (containerBgColorInput && values.canvas_container_bg_color) {
                containerBgColorInput.value = values.canvas_container_bg_color;
                console.log('✅ Updated canvas_container_bg_color:', values.canvas_container_bg_color);
            }

            // Update border color
            const borderColorInput = modal.querySelector('#canvas_border_color');
            if (borderColorInput && values.canvas_border_color) {
                borderColorInput.value = values.canvas_border_color;
                console.log('✅ Updated canvas_border_color:', values.canvas_border_color);
            }

            // Update border width
            const borderWidthInput = modal.querySelector('#canvas_border_width');
            if (borderWidthInput && values.canvas_border_width !== undefined) {
                borderWidthInput.value = values.canvas_border_width;
                console.log('✅ Updated canvas_border_width:', values.canvas_border_width);
            }

            // Update shadow enabled checkbox
            const shadowCheckbox = modal.querySelector('#canvas_shadow_enabled');
            if (shadowCheckbox) {
                const isEnabled = values.canvas_shadow_enabled === '1' || values.canvas_shadow_enabled === true;
                shadowCheckbox.checked = isEnabled;
                console.log('✅ Updated canvas_shadow_enabled:', isEnabled);
            }
        }

        // Placeholder functions for other modals (to be implemented if needed)
        function updateDimensionsModal(modal, values) { /* TODO */ }
        function updateZoomModal(modal, values) { /* TODO */ }
        function updateGrilleModal(modal, values) { /* TODO */ }
        function updateInteractionsModal(modal, values) { /* TODO */ }
        function updateExportModal(modal, values) { /* TODO */ }
        function updatePerformanceModal(modal, values) { /* TODO */ }
        function updateAutosaveModal(modal, values) { /* TODO */ }
        function updateDebugModal(modal, values) { /* TODO */ }

        console.log('✅ Canvas modals initialized successfully for React editor');
    }

})(jQuery);