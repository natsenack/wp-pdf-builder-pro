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
        // Ajouter un champ caché pour indiquer une sauvegarde flottante
        $('#pdf-builder-settings-form').append($('<input>', {
            'type': 'hidden',
            'name': 'pdf_builder_floating_save',
            'value': '1'
        }));
        
        // Laisser le bouton soumettre normalement le formulaire
        // e.preventDefault(); // Ne pas empêcher le comportement par défaut
    });

    // Debug form submission
    $(document).on('submit', '#pdf-builder-settings-form', function(e) {
        var floatingField = $(this).find('input[name="pdf_builder_floating_save"]');
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
        // Check if floating button exists
        if ($('#pdf-builder-save-floating-btn').length) {
            // Floating save button found in DOM
        } else {
            // Floating save button NOT found in DOM
        }
    });

    

})(jQuery);

