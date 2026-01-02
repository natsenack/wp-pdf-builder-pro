// pdf-preview-integration.js - Integration utilities for PDF preview
(function() {
    'use strict';

    window.PDFPreviewIntegration = {
        integrateWithBuilder: function() {
            console.log('Integrating PDF preview with builder...');

            // Add event listeners for preview buttons
            document.addEventListener('click', function(e) {
                if (e.target.matches('.pdf-preview-btn')) {
                    e.preventDefault();
                    window.PDFPreviewAPI.generatePreview({}, function(response) {
                        console.log('Preview response:', response);
                    });
                }
            });
        },

        init: function() {
            console.log('PDF Preview integration initialized');
            this.integrateWithBuilder();
        }
    };

    // Auto-initialize
    window.PDFPreviewIntegration.init();
})();
