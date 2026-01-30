/**
 * PDF Builder Pro V2 - React Initialization Script
 *
 * Ce script initialise l'éditeur React une fois que tous les bundles sont chargés
 */

(function() {
    'use strict';

    

    // Attendre que les bundles React soient chargés
    function waitForReactBundle(maxRetries = 50) {
        
        let retries = 0;

        function checkAndInit() {
            retries++;
            

            if (retries > maxRetries) {
                
                
                return;
            }

            const container = document.getElementById('pdf-builder-react-root');
            

            if (!container) {
                
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

