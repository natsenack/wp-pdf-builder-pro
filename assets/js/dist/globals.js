// Script séparé pour exposer les globals après le chargement du bundle
(function() {
    'use strict';

    // Attendre que le bundle principal soit chargé
    function exposeGlobals() {
        // Vérifier si React et ReactDOM sont disponibles via les modules webpack
        if (typeof window !== 'undefined') {
            // Essayer d'accéder aux modules webpack
            try {
                // Ces variables peuvent être disponibles via webpack
                if (typeof React !== 'undefined') {
                    window.React = React;
                    console.log('React exposed via global React variable');
                }
                if (typeof ReactDOM !== 'undefined') {
                    window.ReactDOM = ReactDOM;
                    console.log('ReactDOM exposed via global ReactDOM variable');
                }

                // Test de fonctionnement
                if (window.React && window.ReactDOM) {
                    const testElement = window.React.createElement('div', null, 'test');
                    console.log('React.createElement test successful in globals script');
                }
            } catch (e) {
                console.error('Error exposing globals:', e);
            }
        }
    }

    // Exposer immédiatement
    exposeGlobals();

    // Et aussi après un court délai au cas où le bundle n'est pas encore chargé
    setTimeout(exposeGlobals, 100);
    setTimeout(exposeGlobals, 500);
    setTimeout(exposeGlobals, 1000);

})();