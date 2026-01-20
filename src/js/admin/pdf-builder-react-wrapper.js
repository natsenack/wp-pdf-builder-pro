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
    
    console.log('✅ [WRAPPER] Variable globale pdfBuilderReactWrapper définie:', window.pdfBuilderReactWrapper);
    
    // Log visible pour confirmer l'exécution
    console.warn('⚠️ [WRAPPER] Wrapper React chargé - vérification du container...');
    
    // Ajouter un indicateur visuel dans le DOM pour confirmer l'exécution
    var indicator = document.createElement('div');
    indicator.id = 'pdf-builder-wrapper-indicator';
    indicator.style.cssText = 'position: fixed; top: 10px; right: 10px; background: #007cba; color: white; padding: 5px 10px; border-radius: 3px; font-size: 12px; z-index: 9999;';
    indicator.textContent = 'Wrapper chargé - ' + new Date().toLocaleTimeString();
    document.body.appendChild(indicator);

    // Attendre que les bundles React soient chargés
    function waitForReactBundle(maxRetries = 50) {
        let retries = 0;

        function checkAndInit() {
            retries++;

            console.warn('🔄 [WRAPPER] Tentative', retries, '/', maxRetries, '- Recherche du container React');

            if (retries > maxRetries) {
                console.error('❌ [WRAPPER] Nombre maximum de tentatives atteint, abandon - Container #pdf-builder-react-root non trouvé');
                // Indicateur visuel d'échec
                var failIndicator = document.createElement('div');
                failIndicator.style.cssText = 'position: fixed; top: 40px; right: 10px; background: #dc3232; color: white; padding: 5px 10px; border-radius: 3px; font-size: 12px; z-index: 9999;';
                failIndicator.textContent = 'ÉCHEC: Container non trouvé';
                document.body.appendChild(failIndicator);
                return;
            }

            const container = document.getElementById('pdf-builder-react-root');

            if (!container) {
                console.warn('⏳ [WRAPPER] Container #pdf-builder-react-root pas trouvé, retry dans 100ms');
                setTimeout(checkAndInit, 100);
                return;
            }

            console.warn('✅ [WRAPPER] Container #pdf-builder-react-root trouvé:', container);
            // Indicateur visuel de succès
            var successIndicator = document.createElement('div');
            successIndicator.style.cssText = 'position: fixed; top: 70px; right: 10px; background: #46b450; color: white; padding: 5px 10px; border-radius: 3px; font-size: 12px; z-index: 9999;';
            successIndicator.textContent = 'Container trouvé';
            document.body.appendChild(successIndicator);
            if (typeof window.pdfBuilderReact === 'undefined' || typeof window.pdfBuilderReact.initPDFBuilderReact !== 'function') {
                console.warn('⏳ [WRAPPER] pdfBuilderReact pas prêt:', {
                    pdfBuilderReact: typeof window.pdfBuilderReact,
                    initFunction: typeof window.pdfBuilderReact?.initPDFBuilderReact
                });
                setTimeout(checkAndInit, 100);
                return;
            }

            console.warn('✅ [WRAPPER] pdfBuilderReact prêt, appel de initPDFBuilderReact');

            try {
                // Initialiser l'éditeur React
                const success = window.pdfBuilderReact.initPDFBuilderReact('pdf-builder-react-root');

                if (success) {
                    console.warn('✅ [WRAPPER] Initialisation React réussie - Éditeur chargé !');
                    // Indicateur visuel de succès d'initialisation
                    var initSuccessIndicator = document.createElement('div');
                    initSuccessIndicator.style.cssText = 'position: fixed; top: 100px; right: 10px; background: #00a32a; color: white; padding: 5px 10px; border-radius: 3px; font-size: 12px; z-index: 9999;';
                    initSuccessIndicator.textContent = 'React initialisé avec succès';
                    document.body.appendChild(initSuccessIndicator);
                } else {
                    console.error('❌ [WRAPPER] Initialisation React échouée - Fonction initPDFBuilderReact a retourné false');
                    // Indicateur visuel d'échec d'initialisation
                    var initFailIndicator = document.createElement('div');
                    initFailIndicator.style.cssText = 'position: fixed; top: 100px; right: 10px; background: #dc3232; color: white; padding: 5px 10px; border-radius: 3px; font-size: 12px; z-index: 9999;';
                    initFailIndicator.textContent = 'ÉCHEC: React non initialisé';
                    document.body.appendChild(initFailIndicator);
                }
            } catch (error) {
                console.error('❌ [WRAPPER] Erreur lors de l\'initialisation React:', error);
                // Indicateur visuel d'erreur
                var errorIndicator = document.createElement('div');
                errorIndicator.style.cssText = 'position: fixed; top: 100px; right: 10px; background: #dc3232; color: white; padding: 5px 10px; border-radius: 3px; font-size: 12px; z-index: 9999;';
                errorIndicator.textContent = 'ERREUR: ' + error.message;
                document.body.appendChild(errorIndicator);
            }
        }

        // Commencer à vérifier
        checkAndInit();
    }

    // Attendre que le document soit prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            console.warn('⚠️ [WRAPPER] DOM prêt, démarrage de l\'initialisation');
            waitForReactBundle();
        });
    } else {
        console.warn('⚠️ [WRAPPER] DOM déjà prêt, démarrage immédiat de l\'initialisation');
        waitForReactBundle();
    }

    // ESSAI IMMÉDIAT : Tenter l'initialisation immédiatement aussi
    console.warn('🚀 [WRAPPER] Tentative d\'initialisation immédiate');
    setTimeout(function() {
        const container = document.getElementById('pdf-builder-react-root');
        if (container && window.pdfBuilderReact && window.pdfBuilderReact.initPDFBuilderReact) {
            console.warn('✅ [WRAPPER] Conditions réunies pour initialisation immédiate');
            try {
                const success = window.pdfBuilderReact.initPDFBuilderReact('pdf-builder-react-root');
                if (success) {
                    console.warn('🎉 [WRAPPER] Initialisation immédiate RÉUSSIE !');
                    // Indicateur visuel de succès immédiat
                    var immediateSuccess = document.createElement('div');
                    immediateSuccess.style.cssText = 'position: fixed; top: 130px; right: 10px; background: #00a32a; color: white; padding: 5px 10px; border-radius: 3px; font-size: 12px; z-index: 9999;';
                    immediateSuccess.textContent = 'Initialisation immédiate réussie';
                    document.body.appendChild(immediateSuccess);
                } else {
                    console.warn('⚠️ [WRAPPER] Initialisation immédiate a retourné false');
                }
            } catch (error) {
                console.error('❌ [WRAPPER] Erreur lors de l\'initialisation immédiate:', error);
            }
        } else {
            console.warn('⏳ [WRAPPER] Conditions pas réunies pour initialisation immédiate:', {
                container: !!container,
                pdfBuilderReact: !!window.pdfBuilderReact,
                initFunction: !!(window.pdfBuilderReact && window.pdfBuilderReact.initPDFBuilderReact)
            });
        }
    }, 100);

})();

