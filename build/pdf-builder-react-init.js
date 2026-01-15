/**
 * PDF Builder Pro V2 - React Initialization Script
 * 
 * Ce script initialise l'éditeur React une fois que tous les bundles sont chargés
 */

(function() {
    'use strict';

    console.log('[PDF Builder] React init script loaded');

    // Attendre que les bundles React soient chargés
    function waitForReactBundle(maxRetries = 50) {
        let retries = 0;

        function checkAndInit() {
            retries++;

            if (retries > maxRetries) {
                console.error('[PDF Builder] ❌ React bundles not loaded after ' + maxRetries + ' attempts');
                return;
            }

            const container = document.getElementById('pdf-builder-react-root');

            if (!container) {
                console.error('[PDF Builder] ❌ Container #pdf-builder-react-root not found');
                setTimeout(checkAndInit, 100);
                return;
            }

            // Vérifier que pdfBuilderReact est disponible
            if (typeof window.pdfBuilderReact === 'undefined' || typeof window.pdfBuilderReact.initPDFBuilderReact !== 'function') {
                console.log('[PDF Builder] Waiting for React bundle... attempt ' + retries);
                setTimeout(checkAndInit, 100);
                return;
            }

            console.log('[PDF Builder] ✅ React bundle detected, initializing PDFBuilderReact');

            try {
                // Initialiser l'éditeur React
                const success = window.pdfBuilderReact.initPDFBuilderReact('pdf-builder-react-root');
                
                if (success) {
                    console.log('[PDF Builder] ✅ PDFBuilderReact initialized successfully');
                } else {
                    console.error('[PDF Builder] ❌ PDFBuilderReact initialization failed');
                }
            } catch (error) {
                console.error('[PDF Builder] ❌ Error initializing PDFBuilderReact:', error);
            }
        }

        // Commencer à vérifier
        checkAndInit();
    }

    // Attendre que le document soit prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            console.log('[PDF Builder] DOMContentLoaded fired');
            waitForReactBundle();
        });
    } else {
        console.log('[PDF Builder] Document already loaded, starting React init');
        waitForReactBundle();
    }

})();
