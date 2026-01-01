/**
 * PDF Builder Pro - Plugin Deactivation Modal
 * Handles the deactivation feedback modal
 */

jQuery(document).ready(function($) {
    'use strict';

    // Debug: Check if script is loaded
    console.log('PDF Builder Deactivation: Script loaded');
    console.log('PDF Builder Deactivation: Plugin slug:', pdf_builder_deactivation ? pdf_builder_deactivation.plugin_slug : 'NOT SET');

    var modal = $('#pdf-builder-deactivation-modal');
    var deactivateLink = null;
    var selectedReason = null;

    // Intercept deactivate link clicks - Use specific class
    $(document).on('click', 'a.pdf-builder-deactivate-link', function(e) {
        console.log('PDF Builder Deactivation: Deactivate link clicked');
        e.preventDefault();
        deactivateLink = $(this);
        showDeactivationModal();
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