// PDF Builder Pro - Correction de Nonce + Gestion d'Erreurs Globales
// Version: 1.7 - 2025-10-25 - Avec interception d'erreurs JavaScript
// Ce fichier ajoute une gestion d'erreurs globale pour les scripts externes

// Intercepter les erreurs JavaScript globales pour éviter les crashes
(function() {
    'use strict';

    // Sauvegarder l'ancien gestionnaire d'erreurs s'il existe
    var oldOnError = window.onerror;

    // Nouveau gestionnaire d'erreurs
    window.onerror = function(message, source, lineno, colno, error) {
        // Vérifier si c'est une erreur de syntaxe avec "Unexpected token '?'"
        if (message && message.indexOf("Unexpected token '?'") !== -1) {
            console.warn('PDF Builder Pro: Interception d\'erreur de syntaxe (probablement script externe):', message);
            console.warn('Source:', source, 'Ligne:', lineno);

            // Essayer de continuer l'exécution malgré l'erreur
            // Ne pas retourner true pour laisser l'erreur être traitée normalement
            // mais au moins la logger
            return false;
        }

        // Pour les autres erreurs, utiliser l'ancien gestionnaire si disponible
        if (oldOnError) {
            return oldOnError(message, source, lineno, colno, error);
        }

        // Logger l'erreur mais ne pas crash la page
        console.error('JavaScript Error:', message, source, lineno, colno, error);
        return false; // Laisser l'erreur être traitée normalement
    };

    // Attendre que le DOM soit chargé
    document.addEventListener('DOMContentLoaded', function() {
        // Vérifier si pdfBuilderAjax existe
        if (typeof pdfBuilderAjax === 'undefined') {
            console.warn('pdfBuilderAjax not found - PDF Builder may not work correctly');
        } else {
            console.log('PDF Builder Pro: AJAX variables loaded successfully');
        }

        // Ajouter un message dans la console pour confirmer que la gestion d'erreurs est active
        console.log('PDF Builder Pro: Global error handling activated');
    });
})();
