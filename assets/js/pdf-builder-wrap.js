/**
 * PDF Builder React - Wrapper Script
 * Ensures pdfBuilderReact is properly exposed on window
 * This script runs AFTER pdf-builder-react.js is loaded
 */

(function() {
    'use strict';

    console.log('🔧 [pdf-builder-wrap] Wrapper script loaded');
    
    // Check if webpack already created a default export
    if (typeof window.pdfBuilderReact === 'object' && window.pdfBuilderReact !== null && typeof window.pdfBuilderReact.initPDFBuilderReact === 'function') {
        console.log('✅ [pdf-builder-wrap] pdfBuilderReact already available');
        return;
    }

    // Try to extract from webpack's internal structures
    // The UMD bundle exposes exports as a variable
    // We need to look for the bundle's exports object
    
    // Webpack stores the module in __webpack_require__ or in the global scope
    // For UMD, it should be in window.pdfBuilderReact already, but let's verify
    
    // Check if there's a pending module
    if (typeof window.webpackChunkpdfBuilderReact !== 'undefined') {
        console.log('🔍 [pdf-builder-wrap] Found webpack chunks, processing...');
        // Chunks are pending, wait for them to be processed
        setTimeout(function() {
            if (typeof window.pdfBuilderReact === 'object' && window.pdfBuilderReact !== null) {
                console.log('✅ [pdf-builder-wrap] pdfBuilderReact available after chunk processing');
                document.dispatchEvent(new Event('pdfBuilderReactReady'));
            }
        }, 100);
        return;
    }

    // Last resort - notify that we tried
    console.warn('⚠️ [pdf-builder-wrap] Could not find pdfBuilderReact export');
    console.log('🔍 [pdf-builder-wrap] Available on window:', Object.keys(window).filter(k => k.includes('pdf')));
})();
