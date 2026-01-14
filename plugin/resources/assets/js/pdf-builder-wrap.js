/**
 * PDF Builder React - Bootstrap Script
 * Creates stub functions on window that get replaced by webpack bundle
 */

(function() {
    'use strict';

    // Create initialized flag
    var isInitialized = false;
    var initialized = {};

    // Create object with all required methods
    var createStub = function() {
        return {
            initPDFBuilderReact: function() { /* STUB */ return false; },
            loadTemplate: function() { /* STUB */ return false; },
            getEditorState: function() { /* STUB */ return null; },
            setEditorState: function() { /* STUB */ return false; },
            getCurrentTemplate: function() { /* STUB */ return null; },
            exportTemplate: function() { /* STUB */ return false; },
            saveTemplate: function() { /* STUB */ return false; },
            registerEditorInstance: function() { /* STUB */ return false; },
            resetAPI: function() { /* STUB */ return false; },
            updateCanvasDimensions: function() { /* STUB */ return false; }
        };
    };

    // Create initial stub but DON'T assign to window yet
    var stub = createStub();

    // Check if webpack bundle has replaced the stub
    // Look for the webpack bundle flag
    var checkRealModule = setInterval(function() {

        if (window.pdfBuilderReact) {

        }
        if (window.pdfBuilderReact && typeof window.pdfBuilderReact.initPDFBuilderReact === 'function' && window.pdfBuilderReact._isWebpackBundle) {
            isInitialized = true;
            clearInterval(checkRealModule);
            
            // Dispatch ready event
            try {
                var event = new Event('pdfBuilderReactReady');
                document.dispatchEvent(event);
                
            } catch (e) {
                // console.error('[pdf-builder-wrap] Error dispatching event:', e);
            }
            return;
        }
    }, 50);

    // Force assign stub and dispatch event after timeout
    setTimeout(function() {
        if (!isInitialized) {
            clearInterval(checkRealModule);
            // Only assign stub if webpack bundle hasn't loaded
            if (!window.pdfBuilderReact || !window.pdfBuilderReact._isWebpackBundle) {
                window.pdfBuilderReact = stub;
                Object.assign(initialized, window.pdfBuilderReact);
            } else {
                
            }
            // Still dispatch event so initialization can proceed
            try {
                var event = new Event('pdfBuilderReactReady');
                document.dispatchEvent(event);
            } catch (e) {}
        }
    }, 5000);
})();

