/**
 * PDF Builder Settings Main JavaScript
 */
(function($) {
    'use strict';

    console.log('[PDF Builder Settings] Main settings initialized');

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

    // Only initialize modal functionality if we're on the license tab
    var currentUrl = window.location.href;
    if (currentUrl.indexOf('tab=licence') !== -1) {
        console.log('[PDF Builder Settings] On license tab - initializing modals');
        initializeModals();
    } else {
        console.log('[PDF Builder Settings] Not on license tab - skipping modal initialization');
    }

    /**
     * Initialize modal functionality
     */
    function initializeModals() {
        console.log('[PDF Builder Settings] Initializing modal functionality');

        // Show deactivate modal when button is clicked
        $(document).on('click', '#deactivate-license-btn', function(e) {
            e.preventDefault();
            console.log('[PDF Builder Settings] Deactivate button clicked');
            showDeactivateModal();
        });

        // Close modal when clicking outside
        $(document).on('click', '.modal-overlay', function(e) {
            if (e.target === this) {
                console.log('[PDF Builder Settings] Modal overlay clicked - closing modal');
                $(this).hide();
            }
        });

        // Close modal with ESC key
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27) { // ESC key
                console.log('[PDF Builder Settings] ESC key pressed - closing modal');
                $('.modal-overlay').hide();
            }
        });
    }

})(jQuery);