// PDF Builder Pro - Correction de Nonce
// Version: 1.0.5 - 2025-10-13_14:30 - SIMPLIFIÉ
// Ce fichier ne fait que vérifier que les variables existent

console.log('PDF Builder Nonce Fix: Vérification des variables AJAX');

// Attendre que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function() {
    console.log('PDF Builder Nonce Fix: DOM chargé');

    // Vérifier si pdfBuilderAjax existe
    if (typeof pdfBuilderAjax === 'undefined') {
        console.error('PDF Builder Nonce Fix: pdfBuilderAjax n\'existe toujours pas !');
    } else {
        console.log('PDF Builder Nonce Fix: pdfBuilderAjax trouvé:', {
            hasAjaxurl: !!pdfBuilderAjax.ajaxurl,
            hasNonce: !!pdfBuilderAjax.nonce,
            nonceLength: pdfBuilderAjax.nonce ? pdfBuilderAjax.nonce.length : 0,
            version: pdfBuilderAjax.version
        });
    }
});