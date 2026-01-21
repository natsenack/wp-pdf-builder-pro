/**
 * PDF Builder Pro V2 - React Initialization Script
 *
 * Ce script initialise l'éditeur React une fois que tous les bundles sont chargés
 */

(function() {
    'use strict';

    // 🚨 DEBUG: Log wrapper execution
    console.error('🔥 [REACT-WRAPPER] pdf-builder-react-wrapper.js script started executing');

    // Définir une variable globale pour indiquer que le wrapper est chargé
    window.pdfBuilderReactWrapper = {
        loaded: true,
        version: '2.0.0',
        timestamp: Date.now()
    };

    // Attendre que les bundles React soient chargés
    function waitForReactBundle(maxRetries = 50) {
        let retries = 0;

        function checkAndInit() {
            retries++;

            console.error('🔄 [REACT-WRAPPER] Check attempt', retries, 'of', maxRetries);

            if (retries > maxRetries) {
                console.error('❌ [REACT-WRAPPER] Max retries reached, giving up');
                return;
            }

            const container = document.getElementById('pdf-builder-react-root');

            if (!container) {
                console.error('❌ [REACT-WRAPPER] Container pdf-builder-react-root not found');
                setTimeout(checkAndInit, 100);
                return;
            }

            console.error('✅ [REACT-WRAPPER] Container found');

            if (typeof window.pdfBuilderReact === 'undefined' || typeof window.pdfBuilderReact.initPDFBuilderReact !== 'function') {
                console.error('❌ [REACT-WRAPPER] pdfBuilderReact not available:', {
                    pdfBuilderReact: typeof window.pdfBuilderReact,
                    initFunction: typeof window.pdfBuilderReact?.initPDFBuilderReact
                });
                setTimeout(checkAndInit, 100);
                return;
            }

            console.error('✅ [REACT-WRAPPER] pdfBuilderReact available, calling initPDFBuilderReact');

            try {
                // Initialiser l'éditeur React
                const success = window.pdfBuilderReact.initPDFBuilderReact('pdf-builder-react-root');

                if (success) {
                    console.error('✅ [REACT-WRAPPER] React initialization SUCCESS');
                } else {
                    console.error('❌ [REACT-WRAPPER] React initialization FAILED');
                }
            } catch (error) {
                console.error('💥 [REACT-WRAPPER] React initialization ERROR:', error);
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

