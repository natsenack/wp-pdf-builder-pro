/**
 * PDF Builder Pro V2 - React Initialization Script
 *
 * Ce script initialise l'éditeur React une fois que tous les bundles sont chargés
 */

(function() {
    'use strict';

    console.log('🚀 [WRAPPER] Script pdf-builder-react-wrapper.js chargé et exécuté');

    // Définir une variable globale pour indiquer que le wrapper est chargé
    window.pdfBuilderReactWrapper = {
        loaded: true,
        version: '2.0.0',
        timestamp: Date.now()
    };
    
    console.log('✅ [WRAPPER] Variable globale pdfBuilderReactWrapper définie');

    // Attendre que les bundles React soient chargés
    function waitForReactBundle(maxRetries = 50) {
        let retries = 0;

        function checkAndInit() {
            retries++;
            
            console.log('🔄 [WRAPPER] Tentative', retries, '/', maxRetries);

            if (retries > maxRetries) {
                console.log('❌ [WRAPPER] Nombre maximum de tentatives atteint, abandon');
                return;
            }

            const container = document.getElementById('pdf-builder-react-root');

            if (!container) {
                console.log('⏳ [WRAPPER] Container #pdf-builder-react-root pas trouvé, retry dans 100ms');
                setTimeout(checkAndInit, 100);
                return;
            }

            console.log('✅ [WRAPPER] Container trouvé:', container);
            if (typeof window.pdfBuilderReact === 'undefined' || typeof window.pdfBuilderReact.initPDFBuilderReact !== 'function') {
                console.log('⏳ [WRAPPER] pdfBuilderReact pas prêt:', {
                    pdfBuilderReact: typeof window.pdfBuilderReact,
                    initFunction: typeof window.pdfBuilderReact?.initPDFBuilderReact
                });
                setTimeout(checkAndInit, 100);
                return;
            }

            console.log('✅ [WRAPPER] pdfBuilderReact prêt, appel de initPDFBuilderReact');

            try {
                // Initialiser l'éditeur React
                const success = window.pdfBuilderReact.initPDFBuilderReact('pdf-builder-react-root');

                if (success) {
                    console.log('✅ [WRAPPER] Initialisation réussie');
                } else {
                    console.log('❌ [WRAPPER] Initialisation échouée');
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

