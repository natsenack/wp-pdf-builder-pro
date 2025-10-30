// Script pour afficher le JSON du template ID 1 dans la console
(async function() {
    try {
        console.log('🔄 Chargement du template ID 1...');

        const templateId = '1';
        const response = await fetch(`${window.pdfBuilderData.ajaxUrl}?action=pdf_builder_get_template&template_id=${templateId}&nonce=${window.pdfBuilderData.nonce}`);

        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }

        const result = await response.json();
        console.log('📡 Réponse API:', result);

        if (!result.success) {
            console.error('❌ Erreur:', result.data);
            return;
        }

        console.log('✅ Template chargé avec succès:');
        console.log('📊 Données complètes:', result.data);
        console.log('🎨 Éléments:', result.data.elements);
        console.log('🖼️ Canvas:', result.data.canvas);

        // Afficher le JSON formaté
        console.log('📄 JSON complet formaté:');
        console.log(JSON.stringify(result.data, null, 2));

    } catch (error) {
        console.error('❌ Erreur lors du chargement:', error.message);
    }
})();