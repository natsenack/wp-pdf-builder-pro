/**
 * PDF Builder Pro - Settings Main JavaScript
 * Handles the main settings page functionality
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        console.log('[PDF Builder Settings] Initializing main settings...');

        // Initialize form handling
        initializeFormHandling();

        // Initialize notifications
        initializeNotifications();

        console.log('[PDF Builder Settings] Main settings initialized');
    });

    /**
     * Initialize form handling
     */
    function initializeFormHandling() {
        // Handle form submissions
        $('.pdf-builder-settings-form').on('submit', function(e) {
            e.preventDefault();

            const form = $(this);
            const submitBtn = form.find('input[type="submit"]');
            const originalText = submitBtn.val();

            // Disable button and show loading
            submitBtn.prop('disabled', true).val('Sauvegarde en cours...');

            // Submit form via AJAX
            $.ajax({
                url: pdf_builder_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_save_settings',
                    nonce: pdf_builder_ajax.nonce,
                    form_data: form.serialize()
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('Paramètres sauvegardés avec succès', 'success');
                    } else {
                        showNotification('Erreur lors de la sauvegarde: ' + (response.data || 'Erreur inconnue'), 'error');
                    }
                },
                error: function() {
                    showNotification('Erreur de communication avec le serveur', 'error');
                },
                complete: function() {
                    // Re-enable button
                    submitBtn.prop('disabled', false).val(originalText);
                }
            });
        });
    }

    /**
     * Initialize notifications system
     */
    function initializeNotifications() {
        // Create notification container if it doesn't exist
        if (!$('#pdf-builder-notifications').length) {
            $('body').append('<div id="pdf-builder-notifications" class="pdf-builder-notifications"></div>');
        }
    }

    /**
     * Show notification
     */
    function showNotification(message, type = 'info') {
        const notification = $(`
            <div class="pdf-builder-notification pdf-builder-notification-${type}">
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `);

        $('#pdf-builder-notifications').append(notification);

        // Auto-hide after 5 seconds
        setTimeout(function() {
            notification.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);

        // Close button
        notification.find('.notification-close').on('click', function() {
            notification.fadeOut(function() {
                $(this).remove();
            });
        });
    }

    // Make functions globally available
    window.pdfBuilderSettings = {
        showNotification: showNotification
    };

})(jQuery);