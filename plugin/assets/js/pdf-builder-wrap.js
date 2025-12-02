/**
 * PDF Builder React - Bootstrap Script
 * Initializes pdfBuilderReact on window with stub functions
 * The actual implementation is loaded from the webpack bundle
 */

(function() {
    'use strict';

    // Create stub object immediately
    if (!window.pdfBuilderReact) {
        window.pdfBuilderReact = {
            initPDFBuilderReact: function() { console.warn('[pdf-builder-wrap] initPDFBuilderReact not loaded yet'); },
            loadTemplate: function() { console.warn('[pdf-builder-wrap] loadTemplate not loaded yet'); },
            getEditorState: function() { console.warn('[pdf-builder-wrap] getEditorState not loaded yet'); },
            setEditorState: function() { console.warn('[pdf-builder-wrap] setEditorState not loaded yet'); },
            getCurrentTemplate: function() { console.warn('[pdf-builder-wrap] getCurrentTemplate not loaded yet'); },
            exportTemplate: function() { console.warn('[pdf-builder-wrap] exportTemplate not loaded yet'); },
            saveTemplate: function() { console.warn('[pdf-builder-wrap] saveTemplate not loaded yet'); },
            registerEditorInstance: function() { console.warn('[pdf-builder-wrap] registerEditorInstance not loaded yet'); },
            resetAPI: function() { console.warn('[pdf-builder-wrap] resetAPI not loaded yet'); },
            updateCanvasDimensions: function() { console.warn('[pdf-builder-wrap] updateCanvasDimensions not loaded yet'); }
        };
        console.log('✅ [pdf-builder-wrap] Stub pdfBuilderReact created on window');
    }

    // Wait for webpack module to execute and merge its exports
    var attempts = 0;
    var checkWebpackModule = setInterval(function() {
        attempts++;
        
        // Check if webpack has set a proper object
        if (window.pdfBuilderReact && typeof window.pdfBuilderReact.initPDFBuilderReact === 'function') {
            // Check if it's still a stub (no real implementation)
            var fnStr = window.pdfBuilderReact.initPDFBuilderReact.toString();
            if (fnStr.indexOf('not loaded yet') === -1) {
                console.log('✅ [pdf-builder-wrap] Real pdfBuilderReact loaded from webpack');
                clearInterval(checkWebpackModule);
                document.dispatchEvent(new Event('pdfBuilderReactReady'));
                return;
            }
        }
        
        if (attempts > 50) {
            clearInterval(checkWebpackModule);
            console.warn('⚠️ [pdf-builder-wrap] Timeout waiting for webpack module');
            document.dispatchEvent(new Event('pdfBuilderReactReady'));
        }
    }, 100);
})();
