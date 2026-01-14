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

    // Create initial stub
    window.pdfBuilderReact = createStub();
    Object.assign(initialized, window.pdfBuilderReact);

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
                    isInitialized = true;
                    clearInterval(checkRealModule);

                    // Dispatch ready event
                    try {
                        var event = new Event('pdfBuilderReactReady');
                        document.dispatchEvent(event);
                    } catch (e) {
                        console.error('[pdf-builder-wrap] Error dispatching event:', e);
                    }
                    return;
                }
            }
        }
    }, 50);

    // Force dispatch event after timeout
    setTimeout(function() {
        if (!isInitialized) {
            clearInterval(checkRealModule);
            // Still dispatch event so initialization can proceed with stub
            try {
                var event = new Event('pdfBuilderReactReady');
                document.dispatchEvent(event);
            } catch (e) {}
        }
    }, 5000);
})();
