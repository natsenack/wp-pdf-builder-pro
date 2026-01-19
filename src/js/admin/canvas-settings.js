/**
 * PDF Builder Canvas Settings JavaScript
 */
(function($) {
    'use strict';

    console.log('🎨 PDF Builder Canvas Settings JS - Loading...');

    // Initialize canvas settings functionality
    $(document).ready(function() {
        console.log('🎨 PDF Builder Canvas Settings JS - Document Ready');
        console.log('⏰ Timestamp:', new Date().toISOString());

        // Add any canvas-specific initialization here
        if (typeof window.pdf_builder_canvas_settings !== 'undefined') {
            console.log('✅ Canvas settings initialized successfully!');
            console.log('🔧 Localized variables:', window.pdf_builder_canvas_settings);
        } else {
            console.log('❌ Canvas settings variables not found!');
            console.log('🔍 Available window properties:', Object.keys(window).filter(key => key.includes('pdf_builder')));
        }

        // Handle modal apply buttons
        $('.canvas-modal-apply').on('click', function(e) {
            e.preventDefault();

            console.log('=== CANVAS MODAL APPLY STARTED ===');
            console.log('Timestamp:', new Date().toISOString());

            var $button = $(this);
            var category = $button.data('category');
            var $modal = $button.closest('.canvas-modal-overlay');
            var modalId = $modal.attr('id') || 'unknown-modal';

            console.log('🎯 Modal Category:', category);
            console.log('🎯 Modal ID:', modalId);
            console.log('🎯 Modal Element:', $modal);

            var $form = $modal.find('form');

            console.log('📋 Form found:', $form.length > 0);
            console.log('🔧 Available variables:', {
                ajaxurl: typeof ajaxurl !== 'undefined' ? ajaxurl : 'undefined',
                pdf_builder_ajax: typeof pdf_builder_ajax !== 'undefined' ? pdf_builder_ajax : 'undefined',
                pdf_builder_canvas_settings: typeof pdf_builder_canvas_settings !== 'undefined' ? pdf_builder_canvas_settings : 'undefined'
            });

            // If no form, create one from modal inputs
            if ($form.length === 0) {
                console.log('📝 No form found, collecting inputs manually...');

                var formData = new FormData();
                var inputCount = 0;
                var inputs = $modal.find('input, select, textarea');

                console.log('📊 Found', inputs.length, 'input elements to process');

                // Collect all inputs from the modal
                inputs.each(function() {
                    var $input = $(this);
                    var name = $input.attr('name');
                    var type = $input.attr('type');
                    var value = $input.val();
                    var checked = $input.prop('checked');
                    var disabled = $input.prop('disabled');

                    console.log('🔍 Processing input:', {
                        name: name,
                        type: type,
                        value: value,
                        checked: checked,
                        disabled: disabled,
                        element: $input
                    });

                    if (name && !disabled) {
                        inputCount++;
                        if (type === 'checkbox') {
                            if (checked) {
                                if (name.endsWith('[]')) {
                                    // Handle array inputs
                                    var arrayName = name.slice(0, -2);
                                    console.log('📦 Array input detected:', arrayName);
                                    if (!formData.has(arrayName)) {
                                        formData.set(arrayName, []);
                                    }
                                    var currentValues = formData.get(arrayName);
                                    if (Array.isArray(currentValues)) {
                                        currentValues.push(value);
                                        formData.set(arrayName, currentValues);
                                        console.log('➕ Added to array', arrayName + ':', currentValues);
                                    }
                                } else {
                                    formData.append(name, value);
                                    console.log('✅ Checkbox added:', name + '=', value);
                                }
                            } else {
                                console.log('❌ Checkbox not checked, skipped:', name);
                            }
                        } else if (type === 'radio') {
                            if (checked) {
                                formData.append(name, value);
                                console.log('📻 Radio selected:', name + '=', value);
                            } else {
                                console.log('📻 Radio not selected, skipped:', name);
                            }
                        } else {
                            formData.append(name, value);
                            console.log('📝 Input added:', name + '=', value);
                        }
                    } else {
                        console.log('⚠️ Input skipped:', { name: name, disabled: disabled });
                    }
                });

                console.log('📈 Total inputs processed:', inputCount);
                console.log('📋 Final collected form data entries (' + formData.getAll.length + ' entries):');
                var entryIndex = 0;
                for (let [key, value] of formData.entries()) {
                    entryIndex++;
                    console.log('  ' + entryIndex + '. ' + key + ':', value);
                }

                // Add nonce and action
                console.log('🔐 Adding security data...');
                formData.append('action', 'pdf_builder_save_canvas_settings');
                formData.append('nonce', pdf_builder_canvas_settings.nonce || '');
                console.log('🔐 Action:', 'pdf_builder_save_canvas_settings');
                console.log('🔐 Nonce present:', !!(pdf_builder_canvas_settings.nonce));

                // Show loading state
                console.log('⏳ Setting loading state...');
                $button.prop('disabled', true).text('⏳ Sauvegarde...');

                // Send AJAX request
                console.log('🚀 Preparing AJAX request...');
                console.log('🌐 AJAX URL:', pdf_builder_canvas_settings.ajax_url || ajaxurl);

                $.ajax({
                    url: pdf_builder_canvas_settings.ajax_url || ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log('✅ AJAX SUCCESS - Response received:');
                        console.log('📄 Raw response:', response);

                        if (response.success) {
                            console.log('🎉 Settings saved successfully!');
                            console.log('📝 Response data:', response.data);

                            // Show success message
                            showNotification('Paramètres canvas sauvegardés avec succès !', 'success');

                            // Close modal
                            console.log('🔒 Closing modal...');
                            $modal.hide();

                            // Reload page to reflect changes
                            console.log('🔄 Reloading page in 1 second...');
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        } else {
                            console.log('❌ Server returned error:', response.data || 'Unknown error');
                            showNotification('Erreur lors de la sauvegarde : ' + (response.data || 'Erreur inconnue'), 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('💥 AJAX ERROR:');
                        console.log('📊 Status:', status);
                        console.log('❌ Error:', error);
                        console.log('📄 XHR:', xhr);
                        showNotification('Erreur AJAX : ' + error, 'error');
                    },
                    complete: function() {
                        console.log('🏁 AJAX Complete - Resetting button state');
                        // Reset button state
                        $button.prop('disabled', false).text('✅ Appliquer');
                    }
                });
            } else {
                console.log('📋 Form found, submitting normally...');
                $form.submit();
            }

            console.log('=== CANVAS MODAL APPLY PROCESSING ===');
        });

        // Handle modal cancel buttons
        $('.canvas-modal-cancel').on('click', function(e) {
            e.preventDefault();
            console.log('❌ Modal cancel button clicked');
            var $modal = $(this).closest('.canvas-modal-overlay');
            console.log('🔒 Closing modal (cancel):', $modal.attr('id'));
            $modal.hide();
        });

        // Handle modal close buttons
        $('.canvas-modal-close').on('click', function(e) {
            e.preventDefault();
            console.log('❌ Modal close button clicked');
            var $modal = $(this).closest('.canvas-modal-overlay');
            console.log('🔒 Closing modal (close):', $modal.attr('id'));
            $modal.hide();
        });

        // Close modal when clicking outside
        $('.canvas-modal-overlay').on('click', function(e) {
            if (e.target === this) {
                console.log('❌ Modal overlay clicked (outside)');
                var $modal = $(this);
                console.log('🔒 Closing modal (overlay):', $modal.attr('id'));
                $(this).hide();
            }
        });

        // Add logs for modal opening (if triggered by external buttons)
        $(document).on('click', '[data-modal], .open-modal, .modal-trigger', function(e) {
            console.log('🚪 Modal trigger clicked:', {
                element: this,
                className: $(this).attr('class'),
                dataModal: $(this).data('modal'),
                targetModal: $(this).attr('data-target') || $(this).data('modal')
            });
        });

        // Monitor modal visibility changes
        $('.canvas-modal-overlay').each(function() {
            var $modal = $(this);
            var modalId = $modal.attr('id');
            
            // Create a mutation observer to detect when modal becomes visible
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                        var display = $modal.css('display');
                        if (display === 'flex' || display === 'block') {
                            console.log('📂 Modal opened:', modalId);
                        } else if (display === 'none') {
                            console.log('📁 Modal closed:', modalId);
                        }
                    }
                });
            });
            
            observer.observe($modal[0], {
                attributes: true,
                attributeFilter: ['style']
            });
        });
    });

    // Helper function to show notifications
    function showNotification(message, type) {
        // Try to use existing notification system
        if (typeof showPdfBuilderNotification !== 'undefined') {
            showPdfBuilderNotification(message, type);
        } else {
            // Fallback to alert
            alert(message);
        }
    }

})(jQuery);

