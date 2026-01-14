/**
 * PDF Builder React - Initialization Helper
 * This file ensures that window.pdfBuilderReact is properly initialized
 * and triggers the initialization event after the bundle loads.
 *
 * This file MUST load AFTER pdf-builder-react.js
 */

// Suppress React passive event listener warnings in development
const originalWarn = console.warn;
console.warn = function(...args) {
    if (args[0] && typeof args[0] === 'string' &&
        (args[0].includes('Added non-passive event listener') ||
         args[0].includes('passive event listener'))) {
        return; // Suppress these warnings
    }
    originalWarn.apply(console, args);
};

(function() {
    'use strict';
    
    const MAX_CHECKS = 100;
    let checkCount = 0;
    
    function checkAndInitialize() {
        checkCount++;
        
        // Check if pdfBuilderReact exists
        if (typeof window.pdfBuilderReact !== 'undefined') {

            // Dispatch the ready event for the initialization script
            document.dispatchEvent(new Event('pdfBuilderReactLoaded'));
            
            return true;
        }
        
        // Log periodically
        if (checkCount === 1 || checkCount % 25 === 0) {
            // console.log('[PDF Builder Init] Checking for React module... (' + checkCount + '/' + MAX_CHECKS + ')');
        }
        
        // Keep checking
        if (checkCount < MAX_CHECKS) {
            setTimeout(checkAndInitialize, 100);
        } else {
            // console.error('❌ [pdf-builder-init] TIMEOUT: pdfBuilderReact not found after ' + MAX_CHECKS + ' attempts');
            // Try one more time with diagnostic info
            // console.log('[PDF Builder Init] Available window properties:', Object.keys(window).filter(k => k.includes('pdf') || k.includes('Builder')).slice(0, 10));
        }
    }
    
    // Start checking
    
    checkAndInitialize();
})();

