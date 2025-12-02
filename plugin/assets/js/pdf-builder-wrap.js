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
            initPDFBuilderReact: function() { return false; },
            loadTemplate: function() { return false; },
            getEditorState: function() { return null; },
            setEditorState: function() { return false; },
            getCurrentTemplate: function() { return null; },
            exportTemplate: function() { return false; },
            saveTemplate: function() { return false; },
            registerEditorInstance: function() { return false; },
            resetAPI: function() { return false; },
            updateCanvasDimensions: function() { return false; }
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
            
            // Check if this is a real implementation (not a stub that just returns false)
            if (fnStr.indexOf('return false') === -1 && fnStr.indexOf('warn') === -1) {
                // Verify it's different from our stub
                var isReal = fnStr.length > 50; // Real function will be longer
                
                if (isReal) {
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
        }
    }, 50);

    // Force assign stub and dispatch event after timeout
    setTimeout(function() {
        if (!isInitialized) {
            clearInterval(checkRealModule);
            // Only assign stub if no real implementation exists
            if (!window.pdfBuilderReact || typeof window.pdfBuilderReact.initPDFBuilderReact !== 'function' || 
                window.pdfBuilderReact.initPDFBuilderReact.toString().indexOf('return false') !== -1) {
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
