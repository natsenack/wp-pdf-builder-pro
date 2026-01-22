/**
 * PDF Builder - Preview Integration
 * Integration utilities for PDF preview functionality
 */
(function($) {
    'use strict';

    

    window.pdfPreviewIntegration = {
        // Preview integration utilities
        init: function() {
            console.log('[PDF PREVIEW INTEGRATION] Initializing preview integration');
            console.log('[PDF PREVIEW INTEGRATION] window.pdfPreviewAPI available:', typeof window.pdfPreviewAPI);
            console.log('[PDF PREVIEW INTEGRATION] window.pdfPreviewApiClient available:', typeof window.pdfPreviewApiClient);
            console.log('[PDF PREVIEW INTEGRATION] window.pdfBuilderAjax available:', typeof window.pdfBuilderAjax);

            // Bind preview events
            this.bindEvents();
        },

        bindEvents: function() {
            console.log('[PDF PREVIEW INTEGRATION] Binding events');
            // Example event bindings
            $(document).on('pdfBuilderPreviewRequested', function(e, data) {
                console.log('[PDF PREVIEW INTEGRATION] Preview requested event:', data);
                // Handle preview request
            });

            $(document).on('pdfBuilderPreviewReady', function(e, previewData) {
                console.log('[PDF PREVIEW INTEGRATION] Preview ready event:', previewData);
                // Handle preview display
            });
        },

        showPreview: function(previewUrl) {
            console.log('[PDF PREVIEW INTEGRATION] Showing preview:', previewUrl);
            // Basic preview display
            const previewWindow = window.open(previewUrl, 'pdf-preview', 'width=800,height=600');
            if (previewWindow) {
                previewWindow.focus();
            }
        },

        updatePreview: function(elementId, content) {
            console.log('[PDF PREVIEW INTEGRATION] Updating preview element:', elementId, 'with content length:', content.length);
            const element = document.getElementById(elementId);
            if (element) {
                element.innerHTML = content;
                
            }
        }
    };

    // Auto-initialize on document ready
    $(document).ready(function() {
        console.log('[PDF PREVIEW INTEGRATION] Document ready, initializing...');
        window.pdfPreviewIntegration.init();
    });

})(jQuery);

