/**
 * PDF Builder Pro V2 - React Initialization Script
 *
 * Ce script initialise l'éditeur React une fois que tous les bundles sont chargés
 */

console.log('[REACT INIT] ===== FILE LOADED =====');
console.log('[REACT INIT] React initialization script loaded at:', new Date().toISOString());
console.log('[REACT INIT] Window object available:', typeof window);
console.log('[REACT INIT] Document object available:', typeof document);

(function() {
    'use strict';

    console.log('[REACT INIT] IIFE executed, checking for React bundles');

    

    // Attendre que les bundles React soient chargés
    function waitForReactBundle(maxRetries = 50) {
        console.log('[REACT INIT] ===== waitForReactBundle STARTED =====');
        console.log('[REACT INIT] maxRetries:', maxRetries);
        let retries = 0;

        function checkAndInit() {
            retries++;
            console.log('[REACT INIT] checkAndInit attempt:', retries, '/', maxRetries);

            if (retries > maxRetries) {
                console.error('[REACT INIT] Max retries exceeded, giving up');
                return;
            }

            const container = document.getElementById('pdf-builder-react-root');
            console.log('[REACT INIT] Container element exists:', !!container);
            console.log('[REACT INIT] Container element:', container);

            if (!container) {
                console.log('[REACT INIT] Container not found, retrying in 100ms');
                setTimeout(checkAndInit, 100);
                return;
            }

            // Vérifier que pdfBuilderReact est disponible
            if (typeof window.pdfBuilderReact === 'undefined' || typeof window.pdfBuilderReact.initPDFBuilderReact !== 'function') {
                
                setTimeout(checkAndInit, 100);
                return;
            }

            

            try {
                // Initialiser l'éditeur React
                const success = window.pdfBuilderReact.initPDFBuilderReact('pdf-builder-react-root');

                if (success) {
                    
                } else {
                    
                }
            } catch (error) {
                
            }
        }

        // Commencer à vérifier
        checkAndInit();
    }

    // Attendre que le document soit prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            
            waitForReactBundle();
        });
    } else {
        
        waitForReactBundle();
    }

})();

