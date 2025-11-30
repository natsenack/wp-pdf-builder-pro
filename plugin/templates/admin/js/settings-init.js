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
                setTimeout(checkDependencies, 500);
                return;
            }

            if (typeof window.pdfBuilderCanvasSettings === 'undefined') {
                setTimeout(checkDependencies, 500);
                return;
            }

            // Constantes de debug
            const PDF_BUILDER_DEBUG_ENABLED = window.pdfBuilderDebugSettings?.javascript || false;
            const PDF_BUILDER_DEBUG_VERBOSE = window.pdfBuilderDebugSettings?.javascript || false;

            // Fonction de debug sécurisée
            window.pdfBuilderDebug = function(...args) {
                if (PDF_BUILDER_DEBUG_ENABLED) {
                    console.log('[PDF Builder Debug]', ...args);
                }
            };

            window.pdfBuilderError = function(...args) {
                if (PDF_BUILDER_DEBUG_ENABLED) {
                    console.error('[PDF Builder Error]', ...args);
                }
            };

            // Ici nous pourrons ajouter toute la logique d'initialisation
            // qui était auparavant dans le template PHP
        }

        // Vérifier que toutes les dépendances sont disponibles
        checkDependencies();

    });

})(jQuery);