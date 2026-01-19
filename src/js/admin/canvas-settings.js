/**
 * PDF Builder Canvas Settings JavaScript
 * Simple modal system for canvas configuration
 * Version: 3.0 - Complete rewrite from scratch
 */

// Log immédiat au chargement du script
console.log('[CANVAS_MODAL] Script file loaded at:', new Date().toISOString());

(function($) {
    'use strict';

    console.log('[CANVAS_MODAL] Script loaded, jQuery available:', typeof $ !== 'undefined');

    // Test immédiat pour voir si les éléments existent
    $(document).ready(function() {
        console.log('[CANVAS_MODAL] DOM ready - immediate check');
        console.log('[CANVAS_MODAL] Configure buttons found:', $('.canvas-configure-btn').length);
        console.log('[CANVAS_MODAL] Modal overlays found:', $('.canvas-modal-overlay').length);
        console.log('[CANVAS_MODAL] Canvas cards found:', $('.canvas-card').length);

        // Vérifier la visibilité des éléments
        $('.canvas-configure-btn').each(function(index) {
            console.log('[CANVAS_MODAL] Button', index, '- visible:', $(this).is(':visible'), '- display:', $(this).css('display'));
        });

        $('.canvas-card').each(function(index) {
            var $card = $(this);
            var category = $card.data('category');
            console.log('[CANVAS_MODAL] Card', index, '- category:', category, '- visible:', $card.is(':visible'));
        });

        // Test click event binding
        $(document).on('click', '.canvas-configure-btn', function(e) {
            console.log('[CANVAS_MODAL] CLICK DETECTED on button!');
            console.log('[CANVAS_MODAL] Event target:', e.target);
            console.log('[CANVAS_MODAL] Button element:', this);
            e.preventDefault();
            return false;
        });
    });

    // Listen for tab changes to show/hide canvas cards
    $(document).on('pdfBuilderTabChanged', function(event, tabId) {
        console.log('[CANVAS_MODAL] Tab changed to:', tabId);
        var $canvasContainer = $('#canvas-cards-container');
        if (tabId === 'contenu') {
            $canvasContainer.show();
            console.log('[CANVAS_MODAL] Canvas cards shown');
        } else {
            $canvasContainer.hide();
            console.log('[CANVAS_MODAL] Canvas cards hidden');
        }
    });

    // Simple modal manager
    var CanvasModalManager = {
        init: function() {
            console.log('[CANVAS_MODAL] Initializing modal system');

            // Bind click events to configure buttons
            $(document).on('click', '.canvas-configure-btn', function(e) {
                e.preventDefault();
                console.log('[CANVAS_MODAL] Configure button clicked');

                var $button = $(this);
                var $card = $button.closest('.canvas-card');
                var category = $card.data('category');

                console.log('[CANVAS_MODAL] Category:', category);
                console.log('[CANVAS_MODAL] Card element:', $card.length > 0 ? 'found' : 'not found');

                if (category) {
                    CanvasModalManager.openModal(category);
                } else {
                    console.error('[CANVAS_MODAL] No category found on card');
                }
            });

            // Bind close events
            $(document).on('click', '.canvas-modal-close, .canvas-modal-cancel', function(e) {
                e.preventDefault();
                var $modal = $(this).closest('.canvas-modal-overlay');
                CanvasModalManager.closeModal($modal);
            });

            // Close on overlay click
            $(document).on('click', '.canvas-modal-overlay', function(e) {
                if (e.target === this) {
                    CanvasModalManager.closeModal($(this));
                }
            });

            // Bind apply/save events
            $(document).on('click', '.canvas-modal-apply', function(e) {
                e.preventDefault();
                var $button = $(this);
                var $modal = $button.closest('.canvas-modal-overlay');
                var category = $button.data('category') || $modal.data('category');

                console.log('[CANVAS_MODAL] Apply button clicked for category:', category);

                if (category) {
                    CanvasModalManager.saveModal($modal, category);
                }
            });

            console.log('[CANVAS_MODAL] Modal system initialized');
        },

        openModal: function(category) {
            console.log('[CANVAS_MODAL] Opening modal for category:', category);

            var modalId = 'canvas-' + category + '-modal-overlay';
            var $modal = $('#' + modalId);

            if ($modal.length === 0) {
                console.error('[CANVAS_MODAL] Modal not found:', modalId);
                return;
            }

            // Close any open modals first
            $('.canvas-modal-overlay').hide();

            // Show the modal
            $modal.show();
            $('body').css('overflow', 'hidden');

            console.log('[CANVAS_MODAL] Modal opened:', modalId);
        },

        closeModal: function($modal) {
            $modal.hide();
            $('body').css('overflow', '');
            console.log('[CANVAS_MODAL] Modal closed');
        },

        saveModal: function($modal, category) {
            console.log('[CANVAS_MODAL] Saving modal for category:', category);

            var $applyBtn = $modal.find('.canvas-modal-apply');
            var originalText = $applyBtn.text();

            // Disable button and show loading
            $applyBtn.prop('disabled', true).text('⏳ Sauvegarde...');

            // Collect form data
            var formData = new FormData();
            formData.append('action', 'pdf_builder_save_canvas_modal');
            formData.append('category', category);
            formData.append('nonce', window.pdfBuilderCanvasSettings ? window.pdfBuilderCanvasSettings.nonce : '');

            // Get all form inputs
            $modal.find('input, select, textarea').each(function() {
                var $input = $(this);
                var name = $input.attr('name');
                var type = $input.attr('type');

                if (!name || $input.prop('disabled')) return;

                var value;
                if (type === 'checkbox') {
                    value = $input.prop('checked') ? '1' : '0';
                } else if (type === 'radio') {
                    if ($input.prop('checked')) {
                        value = $input.val();
                    } else {
                        return; // Skip unchecked radios
                    }
                } else {
                    value = $input.val();
                }

                if (value !== null && value !== undefined) {
                    formData.append(name, value);
                }
            });

            // Send AJAX request
            $.ajax({
                url: window.pdfBuilderCanvasSettings ? window.pdfBuilderCanvasSettings.ajax_url : ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('[CANVAS_MODAL] Save response:', response);

                    if (response.success) {
                        // Close modal and reload page
                        CanvasModalManager.closeModal($modal);
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        alert('Erreur lors de la sauvegarde: ' + (response.data ? response.data.message : 'Erreur inconnue'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('[CANVAS_MODAL] AJAX error:', error);
                    alert('Erreur AJAX: ' + error);
                },
                complete: function() {
                    // Re-enable button
                    $applyBtn.prop('disabled', false).text(originalText);
                }
            });
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        console.log('[CANVAS_MODAL] DOM ready, checking if we should initialize');
        console.log('[CANVAS_MODAL] Current URL:', window.location.href);
        console.log('[CANVAS_MODAL] jQuery available:', typeof jQuery !== 'undefined');
        console.log('[CANVAS_MODAL] $ available:', typeof $ !== 'undefined');

        // Check if we're on the settings page (modals are now always available)
        if (window.location.href.indexOf('page=pdf-builder-settings') !== -1) {

            console.log('[CANVAS_MODAL] On settings page, initializing modals');

            // Check if elements exist
            console.log('[CANVAS_MODAL] Configure buttons found:', $('.canvas-configure-btn').length);
            console.log('[CANVAS_MODAL] Modal overlays found:', $('.canvas-modal-overlay').length);

            // Small delay to ensure everything is loaded
            setTimeout(function() {
                console.log('[CANVAS_MODAL] Executing init after timeout');
                CanvasModalManager.init();
                console.log('[CANVAS_MODAL] Init completed');
            }, 500);

        } else {
            console.log('[CANVAS_MODAL] Not on settings page, skipping initialization');
        }
    });

    // Make it globally available for debugging
    window.CanvasModalManager = CanvasModalManager;

})(jQuery);