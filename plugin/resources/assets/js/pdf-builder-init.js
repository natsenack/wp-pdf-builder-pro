/**
 * PDF Builder React - Initialization Helper
 * This file ensures that window.pdfBuilderReact is properly initialized
 * and triggers the initialization event after the bundle loads.
 *
 * This file MUST load AFTER pdf-builder-react.js
 */

// Gestionnaire d'erreurs global pour les erreurs d'extensions de navigateur
// Intercepte les erreurs courantes des extensions Chrome comme les contextes invalidés ou les canaux de messagerie fermés
window.addEventListener('unhandledrejection', function(event) {
    const error = event.reason;
    if (error && typeof error.message === 'string' &&
        (error.message.includes('A listener indicated an asynchronous response by returning true, but the message channel closed before a response was received') ||
         error.message.includes('Extension context invalidated.') ||
         error.message.includes('Unexpected token \'export\''))) {
        console.warn('⚠️ Erreur d\'extension interceptée et ignorée:', error.message);
        event.preventDefault(); // Empêche l'erreur de remonter
        return false; // Indique que l'erreur a été gérée
    }
});

// Gestionnaire d'erreurs global pour les erreurs synchrones d'extensions
window.addEventListener('error', function(event) {
    const error = event.error || event.message;
    if (error && typeof error === 'string' &&
        (error.includes('A listener indicated an asynchronous response by returning true, but the message channel closed before a response was received') ||
         error.includes('Extension context invalidated.') ||
         error.includes('Unexpected token \'export\''))) {
        console.warn('⚠️ Erreur d\'extension synchronisée interceptée et ignorée:', error);
        event.preventDefault(); // Empêche l'erreur de remonter
        return false; // Indique que l'erreur a été gérée
    }
});

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

