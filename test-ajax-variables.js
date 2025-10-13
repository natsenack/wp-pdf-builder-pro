// Test rapide des variables AJAX PDF Builder
console.log('=== TEST VARIABLES AJAX PDF BUILDER ===');

// Vérifier si window.pdfBuilderAjax existe
if (typeof window.pdfBuilderAjax === 'undefined') {
    console.error('❌ window.pdfBuilderAjax n\'existe pas !');
} else {
    console.log('✅ window.pdfBuilderAjax trouvé:', {
        ajaxurl: window.pdfBuilderAjax.ajaxurl ? '✅ Défini' : '❌ Manquant',
        nonce: window.pdfBuilderAjax.nonce ? `✅ Défini (${window.pdfBuilderAjax.nonce.length} chars)` : '❌ Manquant',
        version: window.pdfBuilderAjax.version || 'N/A'
    });
}

// Test de génération d'aperçu simulé
console.log('=== TEST APERÇU SIMULÉ ===');
const testElements = [
    { id: 'test-1', type: 'text', content: 'Test element' }
];

if (window.pdfBuilderAjax?.ajaxurl && window.pdfBuilderAjax?.nonce) {
    console.log('🔄 Test de l\'appel AJAX...');

    const formData = new FormData();
    formData.append('action', 'pdf_builder_generate_preview');
    formData.append('nonce', window.pdfBuilderAjax.nonce);
    formData.append('elements', JSON.stringify(testElements));

    fetch(window.pdfBuilderAjax.ajaxurl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Aperçu généré avec succès');
        } else {
            console.error('❌ Erreur aperçu:', data.data);
        }
    })
    .catch(error => {
        console.error('❌ Erreur réseau:', error);
    });
} else {
    console.error('❌ Impossible de tester - variables manquantes');
}

console.log('=== FIN TEST ===');