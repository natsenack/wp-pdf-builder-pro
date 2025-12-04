/**
 * PDF Builder React - Initialization Helper
 * This file ensures that window.pdfBuilderReact is properly initialized
 * and triggers the initialization event after the bundle loads.
 * 
 * This file MUST load AFTER pdf-builder-react.js
 */

(function() {
    'use strict';
    
    const MAX_CHECKS = 100;
    let checkCount = 0;
    
    function checkAndInitialize() {
        checkCount++;
        
        // Check if pdfBuilderReact exists
        if (typeof window.pdfBuilderReact !== 'undefined') {
            console.log('✅ [pdf-builder-init] window.pdfBuilderReact is now available!');
            console.log('✅ [pdf-builder-init] Dispatching pdfBuilderReactLoaded event');
            
            // Dispatch the ready event for the initialization script
            document.dispatchEvent(new Event('pdfBuilderReactLoaded'));
            
            // Auto-initialize React if the container exists
            const container = document.getElementById('pdf-builder-react-root');
            if (container && typeof window.pdfBuilderReact.initPDFBuilderReact === 'function') {
                console.log('🚀 [pdf-builder-init] Auto-initializing React...');
                try {
                    const result = window.pdfBuilderReact.initPDFBuilderReact();
                    console.log('✅ [pdf-builder-init] React auto-initialization result:', result);
                } catch (error) {
                    console.error('❌ [pdf-builder-init] React auto-initialization failed:', error);
                }
            } else {
                console.log('⚠️ [pdf-builder-init] Container not found or initPDFBuilderReact not available, skipping auto-init');
            }
            
            return true;
        }
        
        // Log periodically
        if (checkCount === 1 || checkCount % 25 === 0) {
            console.log('⏳ [pdf-builder-init] Waiting for pdfBuilderReact... (' + checkCount + '/' + MAX_CHECKS + ')');
        }
        
        // Keep checking
        if (checkCount < MAX_CHECKS) {
            setTimeout(checkAndInitialize, 100);
        } else {
            console.error('❌ [pdf-builder-init] TIMEOUT: pdfBuilderReact not found after ' + MAX_CHECKS + ' attempts');
            // Try one more time with diagnostic info
            console.log('🔍 [pdf-builder-init] Diagnostic info:');
            console.log('  - window type:', typeof window);
            console.log('  - window.pdfBuilderReact type:', typeof window.pdfBuilderReact);
            console.log('  - Available on window:', Object.keys(window).filter(k => k.includes('pdf') || k.includes('Builder')).slice(0, 10));
        }
    }
    
    // Start checking
    console.log('🚀 [pdf-builder-init] Initializer script loaded, checking for pdfBuilderReact...');
    checkAndInitialize();
})();
