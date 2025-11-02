// Exemple d'utilisation de l'API Preview 1.4 depuis JavaScript
// À placer dans la console du navigateur ou dans votre code JavaScript

// Configuration de l'API
const previewAPI = {
    endpoint: '/wp-admin/admin-ajax.php',
    action: 'wp_pdf_preview_image',
    nonce: pdfBuilderAjax?.nonce || 'your-nonce-here'
};

// Fonction pour générer un aperçu depuis l'éditeur
async function generateEditorPreview(templateData) {
    const formData = new FormData();
    formData.append('action', previewAPI.action);
    formData.append('nonce', previewAPI.nonce);
    formData.append('context', 'editor');
    formData.append('template_data', JSON.stringify(templateData));
    formData.append('quality', '150');
    formData.append('format', 'png');

    try {
        const response = await fetch(previewAPI.endpoint, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            console.log('✅ Aperçu généré:', result.data);
            return result.data.image_url;
        } else {
            console.error('❌ Erreur génération:', result.data);
            return null;
        }
    } catch (error) {
        console.error('❌ Erreur réseau:', error);
        return null;
    }
}

// Fonction pour générer un aperçu depuis la metabox WooCommerce
async function generateOrderPreview(templateData, orderId) {
    const formData = new FormData();
    formData.append('action', previewAPI.action);
    formData.append('nonce', previewAPI.nonce);
    formData.append('context', 'metabox');
    formData.append('template_data', JSON.stringify(templateData));
    formData.append('order_id', orderId);
    formData.append('quality', '150');
    formData.append('format', 'png');

    try {
        const response = await fetch(previewAPI.endpoint, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            console.log('✅ Aperçu commande généré:', result.data);
            return result.data.image_url;
        } else {
            console.error('❌ Erreur génération commande:', result.data);
            return null;
        }
    } catch (error) {
        console.error('❌ Erreur réseau:', error);
        return null;
    }
}

// Exemple d'utilisation
console.log('🎯 API Preview 1.4 - Exemples d\'utilisation:');
console.log('1. Aperçu éditeur: generateEditorPreview(templateData)');
console.log('2. Aperçu commande: generateOrderPreview(templateData, orderId)');
console.log('3. Endpoint: POST', previewAPI.endpoint);
console.log('4. Action: wp_pdf_preview_image');
console.log('5. Sécurité: Nonce requis + permissions utilisateur');