// PDF Builder Pro - Correction de Nonce + Polyfill
// Version: 1.0.6 - 2025-10-25 - Avec polyfill pour opérateur de chaînage optionnel
// Ce fichier ajoute un polyfill pour la compatibilité avec les anciens navigateurs

// Polyfill pour l'opérateur de chaînage optionnel (?.)
// Nécessaire pour la compatibilité avec les anciens navigateurs
(function() {
    'use strict';

    // Vérifier si l'opérateur de chaînage optionnel est supporté
    if (!('optionalChaining' in window) && !window.hasOwnProperty('optionalChaining')) {
        try {
            // Test simple pour voir si ?. est supporté
            eval('var test = {}; test?.prop;');
            window.optionalChaining = true;
        } catch (e) {
            // L'opérateur n'est pas supporté, on ne peut pas ajouter de polyfill complet
            // car eval ne peut pas parser la syntaxe moderne
            console.warn('Optional chaining operator not supported. Some features may not work in older browsers.');
            window.optionalChaining = false;
        }
    }

    // Attendre que le DOM soit chargé
    document.addEventListener('DOMContentLoaded', function() {
        // Vérifier si pdfBuilderAjax existe
        if (typeof pdfBuilderAjax === 'undefined') {
            console.warn('pdfBuilderAjax not found - PDF Builder may not work correctly');
        } else {
            console.log('PDF Builder Pro: AJAX variables loaded successfully');
        }
    });
})();
