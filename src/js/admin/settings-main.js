/**
 * PDF Builder Settings Main JavaScript
 */
(function($) {
    'use strict';

    

    // Define global functions immediately for modal control
    window.closeDeactivateModal = function() {
        $('#deactivate_modal').hide();
    };

    window.showDeactivateModal = function() {
        $('#deactivate_modal').show();
    };

    window.deactivateLicense = function() {
        // Create and submit a form to deactivate the license
        var form = $('<form>', {
            'method': 'POST',
            'action': window.location.href
        });

        // Add required fields
        form.append($('<input>', {
            'type': 'hidden',
            'name': 'deactivate_license',
            'value': '1'
        }));

        // Add nonce for deactivation
        form.append($('<input>', {
            'type': 'hidden',
            'name': 'pdf_builder_deactivate_nonce',
            'value': (window.pdfBuilderLicense && window.pdfBuilderLicense.deactivateNonce) ? window.pdfBuilderLicense.deactivateNonce : ''
        }));

        // Submit the form
        $('body').append(form);
        form.submit();
    };

    // Debug floating save button
    $(document).on('click', '#pdf-builder-save-floating-btn', function(e) {
        // Add a hidden field to indicate floating save was used
        var form = $(this).closest('form');
        if (form.length && !form.find('input[name="pdf_builder_floating_save"]').length) {
            form.append('<input type="hidden" name="pdf_builder_floating_save" value="1">');
            console.log('[PDF Builder] Floating save button clicked - hidden field added');
        } else {
            console.log('[PDF Builder] Floating save button clicked - hidden field already exists or form not found');
        }
        // Don't prevent default, let the form submit
    });

    // Debug form submission
    $(document).on('submit', 'form[action="options.php"]', function(e) {
        console.log('[PDF Builder] Form submitted');
        var floatingField = $(this).find('input[name="pdf_builder_floating_save"]');
        if (floatingField.length) {
            console.log('[PDF Builder] Floating save field found with value: ' + floatingField.val());
        } else {
            console.log('[PDF Builder] No floating save field found');
        }
    });

    // Only initialize modal functionality if we're on the license tab
    var currentUrl = window.location.href;
    if (currentUrl.indexOf('tab=licence') !== -1) {
        initializeModals();
    }

    /**
     * Initialize modal functionality
     */
    function initializeModals() {
        // Show deactivate modal when button is clicked
        $(document).on('click', '#deactivate-license-btn', function(e) {
            e.preventDefault();
            showDeactivateModal();
        });

        // Close modal when clicking outside
        $(document).on('click', '.modal-overlay', function(e) {
            if (e.target === this) {
                $(this).hide();
            }
        });

        // Close modal with ESC key
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27) { // ESC key
                $('.modal-overlay').hide();
            }
        });
    }

    // Debug when DOM is ready
    $(document).ready(function() {
        console.log('[PDF Builder] settings-main.js loaded and DOM ready');
        // Check if floating button exists
        if ($('#pdf-builder-save-floating-btn').length) {
            console.log('[PDF Builder] Floating save button found in DOM');
        } else {
            console.log('[PDF Builder] Floating save button NOT found in DOM');
        }
    });

    

})(jQuery);

