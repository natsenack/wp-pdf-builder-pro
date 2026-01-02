// pdf-builder-wrap.js - Wrapper functions for PDF Builder
(function() {
    'use strict';

    window.PDFBuilderWrap = {
        wrapContent: function(content) {
            return '<div class="pdf-builder-wrapper">' + content + '</div>';
        },

        unwrapContent: function(content) {
            return content.replace(/<div class="pdf-builder-wrapper">|<\/div>/g, '');
        },

        init: function() {
            console.log('PDF Builder wrap utilities loaded');
        }
    };

    // Auto-initialize
    window.PDFBuilderWrap.init();
})();