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
    console.log('✅ [pdf-builder-wrap] Stub pdfBuilderReact created (not assigned to window yet)');

    // Check if webpack bundle has replaced the stub
    // A real implementation will have non-empty function bodies
    var checkRealModule = setInterval(function() {
        if (window.pdfBuilderReact && typeof window.pdfBuilderReact.initPDFBuilderReact === 'function') {
            var fnStr = window.pdfBuilderReact.initPDFBuilderReact.toString();
            
            // Check if this is a real implementation (not a stub that contains "STUB")
            if (fnStr.indexOf('/* STUB */') === -1) {
                // This is a real implementation
                console.log('✅ [pdf-builder-wrap] Real pdfBuilderReact loaded from webpack');
                isInitialized = true;
                clearInterval(checkRealModule);
                
                // Dispatch ready event
                try {
                    var event = new Event('pdfBuilderReactReady');
                    document.dispatchEvent(event);
                    console.log('✅ [pdf-builder-wrap] pdfBuilderReactReady event dispatched');
                } catch (e) {
                    console.error('[pdf-builder-wrap] Error dispatching event:', e);
                }
                return;
            }
        }
    }, 50);

    // Force assign stub and dispatch event after timeout
    setTimeout(function() {
        if (!isInitialized) {
            clearInterval(checkRealModule);
            // Only assign stub if no real implementation exists
            if (!window.pdfBuilderReact || typeof window.pdfBuilderReact.initPDFBuilderReact !== 'function' || 
                window.pdfBuilderReact.initPDFBuilderReact.toString().indexOf('/* STUB */') !== -1) {
                window.pdfBuilderReact = stub;
                Object.assign(initialized, window.pdfBuilderReact);
                console.log('⚠️ [pdf-builder-wrap] Using stub pdfBuilderReact (webpack module not loaded)');
            } else {
                console.log('✅ [pdf-builder-wrap] Keeping real pdfBuilderReact from webpack bundle');
            }
            // Still dispatch event so initialization can proceed
            try {
                var event = new Event('pdfBuilderReactReady');
                document.dispatchEvent(event);
            } catch (e) {}
        }
    }, 5000);
})();
