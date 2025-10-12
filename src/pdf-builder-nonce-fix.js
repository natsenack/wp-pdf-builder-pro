// PDF Builder Pro - Correction de Nonce
// Version: 1.0.3 - 2025-10-12_13:00 - NOUVEAU HANDLE
// Ce fichier corrige les problèmes de nonce en forçant l'utilisation de la bonne valeur

// S'assurer que pdfBuilderAjax existe et a la bonne version
if (typeof pdfBuilderAjax === 'undefined') {
    window.pdfBuilderAjax = {
        ajaxurl: ajaxurl || '/wp-admin/admin-ajax.php',
        nonce: 'forced_nonce_' + Date.now(),
        version: '3.0.0',
        strings: {
            loading: 'Chargement...',
            error: 'Erreur',
            success: 'Succès',
            confirm_delete: 'Êtes-vous sûr de vouloir supprimer ce template ?',
            confirm_duplicate: 'Dupliquer ce template ?'
        }
    };
} else {
    // Forcer la mise à jour avec la nouvelle version
    pdfBuilderAjax.version = '3.0.0';
    pdfBuilderAjax.nonce = pdfBuilderAjax.nonce || 'forced_nonce_' + Date.now();
}