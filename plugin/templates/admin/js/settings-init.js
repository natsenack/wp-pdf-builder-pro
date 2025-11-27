/**
 * Initialisation différée des paramètres PDF Builder
 * Ce fichier est chargé après tous les autres scripts pour éviter les erreurs de syntaxe
 */

(function($) {
    // 'use strict';  // Removed to avoid strict mode issues

    // Attendre que le DOM soit complètement chargé
    $(document).ready(function() {

        // Vérifier que toutes les dépendances sont disponibles
        function checkDependencies() {
            if (typeof window.pdfBuilderSavedSettings === 'undefined') {
                console.warn('pdfBuilderSavedSettings not available, retrying in 500ms...');
                setTimeout(checkDependencies, 500);
                return;
            }

            if (typeof window.pdfBuilderCanvasSettings === 'undefined') {
                console.warn('pdfBuilderCanvasSettings not available, retrying in 500ms...');
                setTimeout(checkDependencies, 500);
                return;
            }

            console.log('PDF Builder Settings: All dependencies loaded, initializing...');

            // Constantes de debug
            const PDF_BUILDER_DEBUG_ENABLED = window.pdfBuilderCanvasSettings?.debug?.javascript || false;
            const PDF_BUILDER_DEBUG_VERBOSE = window.pdfBuilderCanvasSettings?.debug?.javascript_verbose || false;

            // Fonction de debug sécurisée
            window.pdfBuilderDebug = function(...args) {
                if (PDF_BUILDER_DEBUG_ENABLED && typeof console !== 'undefined' && console.log) {
                    if (PDF_BUILDER_DEBUG_VERBOSE) {
                        console.log('[PDF Builder Debug]', ...args);
                    } else {
                        console.log(...args);
                    }
                }
            };

            window.pdfBuilderError = function(...args) {
                if (typeof console !== 'undefined' && console.error) {
                    console.error('[PDF Builder Error]', ...args);
                }
            };

            pdfBuilderDebug('Deferred settings initialization started');
            pdfBuilderDebug('PDF_BUILDER_DEBUG_ENABLED:', PDF_BUILDER_DEBUG_ENABLED);
            pdfBuilderDebug('PDF_BUILDER_DEBUG_VERBOSE:', PDF_BUILDER_DEBUG_VERBOSE);

            // Ici nous pourrons ajouter toute la logique d'initialisation
            // qui était auparavant dans le template PHP

            pdfBuilderDebug('Deferred settings initialization completed');
        }

        // Vérifier que toutes les dépendances sont disponibles
        checkDependencies();

    });

})(jQuery);