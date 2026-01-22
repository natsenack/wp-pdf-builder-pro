/**
 * PDF Builder Pro V2 - React Initialization Script
 *
 * Ce script initialise l'éditeur React une fois que tous les bundles sont chargés
 */

// LOG ABSOLU AU DÉBUT DU FICHIER
console.log('=== PDF BUILDER REACT INIT LOADED ===');
console.log('Timestamp:', Date.now());
console.log('User Agent:', navigator.userAgent);
console.log('Location:', window.location.href);

console.log('[REACT INIT] ===== FILE LOADED =====');
console.log('[REACT INIT] React initialization script loaded at:', new Date().toISOString());
console.log('[REACT INIT] Window object available:', typeof window);
console.log('[REACT INIT] Document object available:', typeof document);

(function() {
    'use strict';

    console.log('[REACT INIT] ===== IIFE EXECUTED =====');
    console.log('[REACT INIT] Checking for React bundles');

    

    // Attendre que les bundles React soient chargés
    function waitForReactBundle(maxRetries = 50) {
        console.log('[REACT INIT] ===== waitForReactBundle STARTED =====');
        console.log('[REACT INIT] maxRetries:', maxRetries);
        console.log('[REACT INIT] Timestamp:', Date.now());
        let retries = 0;

        function checkAndInit() {
            retries++;
            console.log('[REACT INIT] ===== checkAndInit ATTEMPT =====');
            console.log('[REACT INIT] Attempt:', retries, '/', maxRetries);
            console.log('[REACT INIT] Timestamp:', Date.now());

            if (retries > maxRetries) {
                console.error('[REACT INIT] ===== MAX RETRIES EXCEEDED =====');
                console.error('[REACT INIT] Giving up after', maxRetries, 'attempts');
                console.error('[REACT INIT] Timestamp:', Date.now());
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

            console.log('[REACT INIT] Container found, checking for pdfBuilderReact');
            console.log('[REACT INIT] window.pdfBuilderReact type:', typeof window.pdfBuilderReact);
            console.log('[REACT INIT] window.pdfBuilderReact.initPDFBuilderReact type:', typeof (window.pdfBuilderReact && window.pdfBuilderReact.initPDFBuilderReact));

            // Vérifier que pdfBuilderReact est disponible
            if (typeof window.pdfBuilderReact === 'undefined' || typeof window.pdfBuilderReact.initPDFBuilderReact !== 'function') {
                console.log('[REACT INIT] pdfBuilderReact not ready, retrying in 100ms');
                console.log('[REACT INIT] window.pdfBuilderReact:', window.pdfBuilderReact);
                setTimeout(checkAndInit, 100);
                return;
            }

            console.log('[REACT INIT] ===== REACT BUNDLE READY =====');
            console.log('[REACT INIT] Attempting to initialize React app');

            try {
                // Initialiser l'éditeur React
                console.log('[REACT INIT] Calling window.pdfBuilderReact.initPDFBuilderReact');
                const success = window.pdfBuilderReact.initPDFBuilderReact('pdf-builder-react-root');
                console.log('[REACT INIT] initPDFBuilderReact returned:', success);
                console.log('[REACT INIT] Success type:', typeof success);

                if (success) {
                    console.log('[REACT INIT] ===== REACT APP INITIALIZED SUCCESSFULLY =====');
                    console.log('[REACT INIT] Timestamp:', Date.now());
                } else {
                    console.error('[REACT INIT] ===== REACT APP INITIALIZATION FAILED =====');
                    console.error('[REACT INIT] initPDFBuilderReact returned false');
                    console.error('[REACT INIT] Timestamp:', Date.now());
                }
            } catch (error) {
                console.error('[REACT INIT] ===== REACT APP INITIALIZATION ERROR =====');
                console.error('[REACT INIT] Error:', error);
                console.error('[REACT INIT] Error message:', error.message);
                console.error('[REACT INIT] Error stack:', error.stack);
                console.error('[REACT INIT] Timestamp:', Date.now());
            }
        }

        // Commencer à vérifier
        console.log('[REACT INIT] Starting checkAndInit loop');
        checkAndInit();
    }

    // Attendre que le document soit prêt
    console.log('[REACT INIT] Checking document readyState:', document.readyState);
    if (document.readyState === 'loading') {
        console.log('[REACT INIT] Document still loading, adding DOMContentLoaded listener');
        document.addEventListener('DOMContentLoaded', function() {
            console.log('[REACT INIT] ===== DOMContentLoaded EVENT =====');
            console.log('[REACT INIT] Timestamp:', Date.now());
            waitForReactBundle();
        });
    } else {
        console.log('[REACT INIT] Document already ready, calling waitForReactBundle immediately');
        waitForReactBundle();
    }

})();

