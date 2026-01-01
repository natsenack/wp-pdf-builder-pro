/**
 * PDF Builder Pro - Plugin Deactivation Modal
 * Handles the deactivation feedback modal
 */

jQuery(document).ready(function($) {
    'use strict';

    // Debug: vérifier que le script se charge
    console.log('PDF Builder Deactivation: Script loaded');
    console.log('PDF Builder Deactivation: pdf_builder_deactivation object:', pdf_builder_deactivation);
    console.log('PDF Builder Deactivation: jQuery version:', $.fn.jquery);

    var modal = $('#pdf-builder-deactivation-modal');
    var deactivateLink = null;
    var selectedReason = null;

    // Debug: vérifier que le modal existe
    console.log('PDF Builder Deactivation: Modal element found:', modal.length);

    // Intercept deactivate link clicks - utiliser un sélecteur plus général et plus robuste
    $(document).on('click', 'a[href*="action=deactivate"]', function(e) {
        var href = $(this).attr('href');
        console.log('PDF Builder Deactivation: Link clicked, href:', href);

        // Vérifier si c'est notre plugin
        if (href && href.indexOf('wp-pdf-builder-pro') !== -1) {
            console.log('PDF Builder Deactivation: Our plugin deactivate link clicked');
            e.preventDefault();
            deactivateLink = $(this);
            showDeactivationModal();
        }
    });

    // Close modal events
    $(document).on('click', '.pdf-builder-modal-close, .pdf-builder-modal-cancel, .pdf-builder-modal-overlay', function() {
        hideDeactivationModal();
    });

    // Reason selection
    $(document).on('change', 'input[name="deactivation_reason"]', function() {
        selectedReason = $(this).val();
        $('.pdf-builder-modal-confirm').prop('disabled', false);

        // Show details textarea for "other" reason
        if (selectedReason === 'other') {
            $('.reason-details').slideDown();
        } else {
            $('.reason-details').slideUp();
        }
    });

    // Skip and deactivate (without feedback)
    $(document).on('click', '.pdf-builder-modal-skip', function() {
        // Proceed with deactivation without feedback
        if (deactivateLink) {
            // Send minimal feedback (just that user skipped)
            var feedbackData = {
                reason: 'skipped',
                details: '',
                is_premium: pdf_builder_deactivation.is_premium,
                plugin_slug: pdf_builder_deactivation.plugin_slug
            };

            // Try to send feedback but don't wait for response
            $.ajax({
                url: pdf_builder_deactivation.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_deactivation_feedback',
                    nonce: pdf_builder_deactivation.nonce,
                    feedback: feedbackData
                },
                timeout: 2000, // 2 second timeout
                success: function() {
                    // Feedback sent successfully, proceed with deactivation
                    window.location.href = deactivateLink.attr('href');
                },
                error: function() {
                    // Feedback failed or timed out, still proceed with deactivation
                    window.location.href = deactivateLink.attr('href');
                }
            });
        }
    });

    // Confirm deactivation
    $(document).on('click', '.pdf-builder-modal-confirm', function() {
        if (!selectedReason) {
            alert(pdf_builder_deactivation.strings.reason_required);
            return;
        }

        // Collect feedback data
        var feedbackData = {
            reason: selectedReason,
            details: $('#deactivation_details').val(),
            is_premium: pdf_builder_deactivation.is_premium,
            plugin_slug: pdf_builder_deactivation.plugin_slug
        };

        // Send feedback via AJAX
        submitDeactivationFeedback(feedbackData);
    });

    // ESC key to close modal
    $(document).on('keydown', function(e) {
        if (e.keyCode === 27 && modal.is(':visible')) {
            hideDeactivationModal();
        }
    });

    function showDeactivationModal() {
        modal.fadeIn(300);
        $('body').addClass('pdf-builder-modal-open');

        // Focus on first reason option
        modal.find('input[name="deactivation_reason"]:first').focus();
    }

    function hideDeactivationModal() {
        modal.fadeOut(300);
        $('body').removeClass('pdf-builder-modal-open');
        selectedReason = null;
        $('.pdf-builder-modal-confirm').prop('disabled', true);
        $('.reason-details').slideUp();
        $('#deactivation_details').val('');
        $('input[name="deactivation_reason"]').prop('checked', false);
    }

    function submitDeactivationFeedback(feedbackData) {
        // Show loading state
        $('.pdf-builder-modal-confirm')
            .prop('disabled', true)
            .text(pdf_builder_deactivation.strings.deactivating);

        $.ajax({
            url: pdf_builder_deactivation.ajax_url,
            type: 'POST',
            data: {
                action: 'pdf_builder_deactivation_feedback',
                nonce: pdf_builder_deactivation.nonce,
                feedback: feedbackData
            },
            success: function(response) {
                if (response.success) {
                    // Proceed with deactivation
                    if (deactivateLink) {
                        window.location.href = deactivateLink.attr('href');
                    }
                } else {
                    alert('Erreur lors de l\'envoi du feedback. Le plugin sera tout de même désactivé.');
                    if (deactivateLink) {
                        window.location.href = deactivateLink.attr('href');
                    }
                }
            },
            error: function() {
                alert('Erreur lors de l\'envoi du feedback. Le plugin sera tout de même désactivé.');
                if (deactivateLink) {
                    window.location.href = deactivateLink.attr('href');
                }
            }
        });
    }
});