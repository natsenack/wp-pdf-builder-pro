/**
 * PDF Builder - Preview API Client
 * UI only - no generation
 * Buttons, metabox and modals remain intact
 * All generation logic removed
 */

(function($) {
    'use strict';

    // Stub API - no actual functionality
    window.pdfPreviewApiClient = {
        generatePreview: function(data, callback) {
            // Do nothing
        }
    };

    window.pdfPreviewAPI = {
        generateEditorPreview: function(templateData, options) {
            // Return rejected promise
            return Promise.reject(new Error('Preview generation disabled'));
        },

        generateOrderPreview: function(templateData, orderId, options) {
            // Return rejected promise
            return Promise.reject(new Error('Preview generation disabled'));
        }
    };

})(jQuery);

