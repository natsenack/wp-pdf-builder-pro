/**
 * PDF Builder React - Webpack Bundle Wrapper
 * Ensures the module is properly assigned to window when loaded
 * This file acts as the true webpack entry point that exports everything to window
 */

(function() {
    console.log('📦📦📦 WRAPPER_FILE_LOADED_V5: pdf-builder-react-wrapper.min.js STARTED at ' + new Date().toISOString());

    // Wait for the main React module to be loaded
    function waitForReactModule() {
        console.log('🔍 Waiting for pdfBuilderReact module...');
        
        if (window.pdfBuilderReact && window.pdfBuilderReact.initPDFBuilderReact) {
            console.log('✅ pdfBuilderReact module found, re-exporting...');
            
            // Re-export to window
            window.pdfBuilderReactWrapper = {
                initPDFBuilderReact: window.pdfBuilderReact.initPDFBuilderReact,
                loadTemplate: window.pdfBuilderReact.loadTemplate,
                getEditorState: window.pdfBuilderReact.getEditorState,
                setEditorState: window.pdfBuilderReact.setEditorState,
                getCurrentTemplate: window.pdfBuilderReact.getCurrentTemplate,
                exportTemplate: window.pdfBuilderReact.exportTemplate,
                saveTemplate: window.pdfBuilderReact.saveTemplate,
                registerEditorInstance: window.pdfBuilderReact.registerEditorInstance,
                resetAPI: window.pdfBuilderReact.resetAPI,
                updateCanvasDimensions: window.pdfBuilderReact.updateCanvasDimensions,
                _isWebpackBundle: true,
            };
            
            console.log('✅ pdfBuilderReactWrapper assigned');
            
            // Signal when loaded
            try {
                const event = new Event('pdfBuilderReactLoaded');
                document.dispatchEvent(event);
                console.log('✅ pdfBuilderReactLoaded event dispatched');
            } catch (e) {
                console.error('Error dispatching event:', e);
            }
            
            return true;
        }
        
        return false;
    }
    
    // Try immediately
    if (!waitForReactModule()) {
        // If not available yet, wait for it
        let attempts = 0;
        const maxAttempts = 100; // 5 seconds at 50ms intervals
        
        const checkInterval = setInterval(function() {
            attempts++;
            
            if (waitForReactModule()) {
                clearInterval(checkInterval);
            } else if (attempts >= maxAttempts) {
                clearInterval(checkInterval);
                console.error('❌ pdfBuilderReact module not found after 5 seconds');
            }
        }, 50);
    }
})();

