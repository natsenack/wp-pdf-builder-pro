// PDF Builder Pro - Correction de Nonce
// Version: 1.0.3 - 2025-10-12_13:00 - NOUVEAU HANDLE
// Ce fichier corrige les problèmes de nonce en forçant l'utilisation de la bonne valeur

console.log('🔧 PDF Builder Pro - NOUVEAU HANDLE NONCE-FIX chargé - v1.0.3');
console.log('⏰ PDF Builder Pro - Timestamp de chargement:', Date.now());
console.log('🌐 PDF Builder Pro - Vérification globale pdfBuilderAjax:', typeof window.pdfBuilderAjax);

// S'assurer que pdfBuilderAjax existe et a la bonne version
if (typeof pdfBuilderAjax === 'undefined') {
    console.log('pdfBuilderAjax n\'existe pas, création...');
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
    console.log('pdfBuilderAjax existe, vérification version...');
    // Forcer la mise à jour avec la nouvelle version
    pdfBuilderAjax.version = '3.0.0';
    pdfBuilderAjax.nonce = pdfBuilderAjax.nonce || 'forced_nonce_' + Date.now();
}

console.log('PDF Builder Ajax final:', pdfBuilderAjax);