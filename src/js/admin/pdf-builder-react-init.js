/**
 * PDF Builder Pro V2 - React Initialization Script
 *
 * Ce script initialise l'éditeur React une fois que tous les bundles sont chargés
 */

(function() {
    'use strict';

    // 🚨 DEBUG: Log script execution - BASIC
    console.error('🔥 [INIT BASIC] pdf-builder-react-init.js START OF FILE EXECUTING');

    // 🚨 DEBUG: Log script execution
    console.error('🔥 [REACT-INIT] pdf-builder-react-init.js script started executing');

    // Attendre que les bundles React soient chargés
    function waitForReactBundle(maxRetries = 50) {
        let retries = 0;

        function checkAndInit() {
            retries++;

            console.error('🔄 [REACT-INIT] Check attempt', retries, 'of', maxRetries);

            if (retries > maxRetries) {
                console.error('❌ [REACT-INIT] Max retries reached, giving up');
                return;
            }

            const container = document.getElementById('pdf-builder-react-root');

            if (!container) {
                console.error('❌ [REACT-INIT] Container pdf-builder-react-root not found');
                setTimeout(checkAndInit, 100);
                return;
            }

            console.error('✅ [REACT-INIT] Container found');

            // Vérifier que pdfBuilderReact est disponible
            if (typeof window.pdfBuilderReact === 'undefined' || typeof window.pdfBuilderReact.initPDFBuilderReact !== 'function') {
                console.error('❌ [REACT-INIT] pdfBuilderReact not available:', {
                    pdfBuilderReact: typeof window.pdfBuilderReact,
                    initFunction: typeof window.pdfBuilderReact?.initPDFBuilderReact
                });
                setTimeout(checkAndInit, 100);
                return;
            }

            console.error('✅ [REACT-INIT] pdfBuilderReact available, calling initPDFBuilderReact');

            try {
                // Initialiser l'éditeur React
                const success = window.pdfBuilderReact.initPDFBuilderReact('pdf-builder-react-root');

                if (success) {
                    console.error('✅ [REACT-INIT] React initialization SUCCESS');
                } else {
                    console.error('❌ [REACT-INIT] React initialization FAILED');
                }
            } catch (error) {
                console.error('💥 [REACT-INIT] React initialization ERROR:', error);
                
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

