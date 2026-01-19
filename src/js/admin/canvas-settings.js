/**
 * PDF Builder Canvas Settings JavaScript
 */
(function($) {
    'use strict';

    // Initialize canvas settings functionality
    $(document).ready(function() {
        console.log('Canvas settings JavaScript loaded');

        // Add any canvas-specific initialization here
        if (typeof window.pdfBuilderCanvasSettings !== 'undefined') {
            console.log('Canvas settings initialized:', window.pdfBuilderCanvasSettings);
        }
    });

})(jQuery);

