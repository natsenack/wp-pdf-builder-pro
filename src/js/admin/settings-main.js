/**
 * PDF Builder Settings Main JavaScript
 */
(function($) {
    'use strict';

    // LOG DE TEST - SETTINGS-MAIN CHARGÉ
    console.log('🔧🔧🔧 SETTINGS-MAIN.JS CHARGÉ ET EXÉCUTÉ 🔧🔧🔧');
    console.log('⏰ Timestamp settings-main:', new Date().toISOString());

// Vérifier si canvas-settings peut être chargé
console.log('🔗 SETTINGS-MAIN: Vérification des dépendances pour canvas-settings...');
console.log('🔗 jQuery disponible:', typeof jQuery !== 'undefined');
console.log('🔗 settings-main disponible:', typeof window !== 'undefined');
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

})(jQuery);

