// pdf-builder-react-wrapper.js - Wrapper pour React components
(function() {
    'use strict';

    // Placeholder for React wrapper functionality
    window.PDFBuilderReact = {
        init: function() {
            console.log('PDF Builder React wrapper initialized');
        }
    };

    // Auto-initialize if DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.PDFBuilderReact.init);
    } else {
        window.PDFBuilderReact.init();
    }
})();
