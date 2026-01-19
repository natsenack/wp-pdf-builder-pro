/**
 * PDF Builder Canvas Settings JavaScript
 * Nouveau système de sauvegarde par modal
 */
(function($) {
    'use strict';

    // Configuration des champs par modal
    var modalFieldsConfig = {
        'affichage': [
            'pdf_builder_canvas_width',
            'pdf_builder_canvas_height',
            'pdf_builder_canvas_dpi[]',
            'pdf_builder_canvas_formats[]',
            'pdf_builder_canvas_orientations[]',
            'pdf_builder_canvas_bg_color',
            'pdf_builder_canvas_border_color',
            'pdf_builder_canvas_border_width',
            'pdf_builder_canvas_container_bg_color',
            'pdf_builder_canvas_shadow_enabled'
        ],
        'navigation': [
            'pdf_builder_canvas_grid_enabled',
            'pdf_builder_canvas_grid_size',
            'pdf_builder_canvas_guides_enabled',
            'pdf_builder_canvas_snap_to_grid',
            'pdf_builder_canvas_zoom_min',
            'pdf_builder_canvas_zoom_max',
            'pdf_builder_canvas_zoom_default',
            'pdf_builder_canvas_zoom_step'
        ],
        'comportement': [
            'pdf_builder_canvas_drag_enabled',
            'pdf_builder_canvas_resize_enabled',
            'pdf_builder_canvas_rotate_enabled',
            'pdf_builder_canvas_multi_select',
            'pdf_builder_canvas_selection_mode',
            'pdf_builder_canvas_keyboard_shortcuts',
            'pdf_builder_canvas_export_quality',
            'pdf_builder_canvas_export_format',
            'pdf_builder_canvas_export_transparent'
        ],
        'systeme': [
            'pdf_builder_canvas_fps_target',
            'pdf_builder_canvas_memory_limit_js',
            'pdf_builder_canvas_response_timeout',
            'pdf_builder_canvas_lazy_loading_editor',
            'pdf_builder_canvas_preload_critical',
            'pdf_builder_canvas_lazy_loading_plugin',
            'pdf_builder_canvas_debug_enabled',
            'pdf_builder_canvas_performance_monitoring',
            'pdf_builder_canvas_error_reporting',
            'pdf_builder_canvas_memory_limit_php'
        ]
    };

    // Initialize canvas settings functionality
    $(document).ready(function() {
        console.log('Canvas settings JavaScript loaded - Nouveau système');

        // Handle modal apply buttons with new system
        $('.canvas-modal-apply').on('click', function(e) {
            e.preventDefault();

            console.log('Canvas modal apply button clicked - Nouveau système');

            var $button = $(this);
            var category = $button.data('category');
            var $modal = $button.closest('.canvas-modal-overlay');

            console.log('Processing modal category:', category);

            if (!category || !modalFieldsConfig[category]) {
                console.error('Unknown modal category:', category);
                showNotification('Erreur: Catégorie de modal inconnue', 'error');
                return;
            }

            // Collect form data for this specific modal
            var formData = new FormData();
            var collectedData = {};

            // Get all inputs in this modal
            $modal.find('input, select, textarea').each(function() {
                var $input = $(this);
                var name = $input.attr('name');
                var type = $input.attr('type');
                var value = $input.val();

                if (name && !$input.prop('disabled') && modalFieldsConfig[category].includes(name)) {
                    if (type === 'checkbox') {
                        if ($input.prop('checked')) {
                            if (name.endsWith('[]')) {
                                // Handle array inputs
                                var arrayName = name.slice(0, -2);
                                if (!collectedData[arrayName]) {
                                    collectedData[arrayName] = [];
                                }
                                collectedData[arrayName].push(value);
                                formData.append(arrayName, value);
                            } else {
                                collectedData[name] = '1';
                                formData.append(name, '1');
                            }
                        } else if (!name.endsWith('[]')) {
                            collectedData[name] = '0';
                            formData.append(name, '0');
                        }
                    } else if (type === 'radio') {
                        if ($input.prop('checked')) {
                            collectedData[name] = value;
                            formData.append(name, value);
                        }
                    } else {
                        collectedData[name] = value;
                        formData.append(name, value);
                    }
                }
            });

            console.log('Collected data for', category, ':', collectedData);

            // Add metadata
            formData.append('action', 'pdf_builder_save_canvas_modal');
            formData.append('modal_category', category);
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
                    console.log('Save response:', response);
                    if (response.success) {
                        showNotification('Paramètres ' + category + ' sauvegardés avec succès !', 'success');

                        // Update the main settings form hidden fields if they exist
                        updateMainFormFields(collectedData);

                        // Close modal
                        $modal.hide();

                        // Optional: reload to reflect changes
                        setTimeout(function() {
                            // window.location.reload();
                        }, 500);
                    } else {
                        showNotification('Erreur lors de la sauvegarde : ' + (response.data || 'Erreur inconnue'), 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', xhr, status, error);
                    showNotification('Erreur AJAX : ' + error, 'error');
                },
                complete: function() {
                    // Reset button state
                    $button.prop('disabled', false).text('✅ Appliquer');
                }
            });
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

    // Function to update main form hidden fields
    function updateMainFormFields(collectedData) {
        Object.keys(collectedData).forEach(function(key) {
            var hiddenField = document.querySelector('input[name="pdf_builder_settings[' + key + ']"]');
            if (hiddenField) {
                hiddenField.value = collectedData[key];
                console.log('Updated hidden field', key, 'to', collectedData[key]);
            }
        });
    }

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

