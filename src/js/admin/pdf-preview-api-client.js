/**
 * PDF Builder - Preview API Client (Ultra-Minimal)
 * Simplified preview system - no actual generation
 */

(function($) {
    'use strict';

    window.pdfPreviewApiClient = {
        generatePreview: function(data, callback) {
            if (callback) callback(null, { success: true, data: { image_url: '#' } });
        }
    };

    // API interface for React components
    window.pdfPreviewAPI = {
        generateEditorPreview: function(templateData, options) {
            return Promise.resolve({ success: true, data: { image_url: '#' } });
        },

        generateOrderPreview: function(templateData, orderId, options) {
            return Promise.resolve({ success: true, data: { image_url: '#' } });
        }
    };

})(jQuery);

