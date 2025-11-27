/**
 * Initialisation différée des paramètres PDF Builder
 * Ce fichier est chargé après tous les autres scripts pour éviter les erreurs de syntaxe
 */

(function($) {
    'use strict';

    // Attendre que le DOM soit complètement chargé
    $(document).ready(function() {

        // Vérifier que toutes les dépendances sont disponibles
        if (typeof window.pdfBuilderSavedSettings === 'undefined') {
            console.warn('pdfBuilderSavedSettings not available, retrying in 500ms...');
            setTimeout(arguments.callee, 500);
            return;
        }

        if (typeof window.pdfBuilderCanvasSettings === 'undefined') {
            console.warn('pdfBuilderCanvasSettings not available, retrying in 500ms...');
            setTimeout(arguments.callee, 500);
            return;
        }

        console.log('PDF Builder Settings: All dependencies loaded, initializing...');

        // Constantes de debug
        const PDF_BUILDER_DEBUG_ENABLED = window.pdfBuilderCanvasSettings?.debug?.javascript || false;
        const PDF_BUILDER_DEBUG_VERBOSE = window.pdfBuilderCanvasSettings?.debug?.javascript_verbose || false;

        // Fonction de debug sécurisée
        window.pdfBuilderDebug = function() {
            if (PDF_BUILDER_DEBUG_ENABLED && typeof console !== 'undefined' && console.log) {
                if (PDF_BUILDER_DEBUG_VERBOSE) {
                    console.log.apply(console, ['[PDF Builder Debug]'].concat(Array.prototype.slice.call(arguments)));
                } else {
                    console.log.apply(console, arguments);
                }
            }
        };

        window.pdfBuilderError = function() {
            if (typeof console !== 'undefined' && console.error) {
                console.error.apply(console, ['[PDF Builder Error]'].concat(Array.prototype.slice.call(arguments)));
            }
        };

        pdfBuilderDebug('Deferred settings initialization started');
        pdfBuilderDebug('PDF_BUILDER_DEBUG_ENABLED:', PDF_BUILDER_DEBUG_ENABLED);
        pdfBuilderDebug('PDF_BUILDER_DEBUG_VERBOSE:', PDF_BUILDER_DEBUG_VERBOSE);

        // Ici nous pourrons ajouter toute la logique d'initialisation
        // qui était auparavant dans le template PHP

        pdfBuilderDebug('Deferred settings initialization completed');

    });

})(jQuery);