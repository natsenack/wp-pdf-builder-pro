/**
 * PDF Builder - General Initialization
 * General initialization utilities
 */
(function($) {
    'use strict';

    

    window.pdfBuilderInit = {
        // General initialization functions
        ready: function(callback) {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', callback);
            } else {
                callback();
            }
        },

        // Check if we're on a PDF Builder page
        isPDFBuilderPage: function() {
            return window.location.href.indexOf('pdf-builder') !== -1;
        },

        // Get page context
        getContext: function() {
            const urlParams = new URLSearchParams(window.location.search);
            return {
                page: urlParams.get('page'),
                templateId: urlParams.get('template_id'),
                tab: urlParams.get('tab')
            };
        }
    };

})(jQuery);

