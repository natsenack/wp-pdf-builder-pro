/**
 * PDF Builder Canvas Settings JavaScript
 */
(function($) {
    'use strict';

    // Initialize canvas settings functionality
    $(document).ready(function() {

        // Handle modal apply buttons
        $('.canvas-modal-apply').on('click', function(e) {
            e.preventDefault();

            var $button = $(this);
            var category = $button.data('category');
            var $modal = $button.closest('.canvas-modal-overlay');
            var $form = $modal.find('form');

            // If no form, create one from modal inputs
            if ($form.length === 0) {
                var formData = new FormData();

                // Collect all inputs from the modal
                $modal.find('input, select, textarea').each(function() {
                    var $input = $(this);
                    var name = $input.attr('name');
                    var type = $input.attr('type');
                    var value = $input.val();

                    if (name && !$input.prop('disabled')) {
                        if (type === 'checkbox') {
                            if ($input.prop('checked')) {
                                if (name.endsWith('[]')) {
                                    // Handle array inputs - append multiple values with same key
                                    var arrayName = name.slice(0, -2);
                                    formData.append(arrayName, value);
                                } else {
                                    formData.append(name, value);
                                }
                            }
                        } else if (type === 'radio') {
                            if ($input.prop('checked')) {
                                formData.append(name, value);
                            }
                        } else {
                            formData.append(name, value);
                        }
                    }
                });

                // Add nonce and action
                formData.append('action', 'pdf_builder_save_canvas_settings');
                formData.append('nonce', pdf_builder_canvas_settings.nonce || '');

                // Show loading state
                $button.prop('disabled', true).text('⏳ Sauvegarde...');

                // Send AJAX request
                $.ajax({
                    url: pdf_builder_canvas_settings.ajax_url || ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            showNotification('Paramètres canvas sauvegardés avec succès !', 'success');

                            // Close modal
                            $modal.hide();

                            // Reload page to reflect changes
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        } else {
                            showNotification('Erreur lors de la sauvegarde : ' + (response.data || 'Erreur inconnue'), 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        showNotification('Erreur AJAX : ' + error, 'error');
                    },
                    complete: function() {
                        // Reset button state
                        $button.prop('disabled', false).text('✅ Appliquer');
                    }
                });
            }
        });

        // Handle modal cancel buttons
        $('.canvas-modal-cancel').on('click', function(e) {
            e.preventDefault();
            var $modal = $(this).closest('.canvas-modal-overlay');
            $modal.hide();
        });

        // Handle modal close buttons
        $('.canvas-modal-close').on('click', function(e) {
            e.preventDefault();
            var $modal = $(this).closest('.canvas-modal-overlay');
            $modal.hide();
        });

        // Close modal when clicking outside
        $('.canvas-modal-overlay').on('click', function(e) {
            if (e.target === this) {
                $(this).hide();
            }
        });
    });

    // Helper function to show notifications
    function showNotification(message, type) {
        // Try to use existing notification system
        if (typeof showSystemNotification !== 'undefined') {
            showSystemNotification(message, type);
        } else {
            // Fallback to alert
            alert(message);
        }
    }

})(jQuery);

