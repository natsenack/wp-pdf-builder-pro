/**
 * Test de sauvegarde des paramètres canvas
 * Ce script teste si les paramètres canvas se sauvegardent correctement
 */

console.log('=== Test de sauvegarde des paramètres canvas ===');

// Fonction de test pour vérifier la sauvegarde
function testCanvasSettingsSave() {
    console.log('Test 1: Vérification des valeurs actuelles...');

    // Vérifier les valeurs actuelles des champs canvas
    const canvasMaxSize = document.getElementById('canvas_max_size')?.value;
    const canvasDpi = document.getElementById('canvas_dpi')?.value;
    const canvasFormat = document.getElementById('canvas_format')?.value;
    const canvasQuality = document.getElementById('canvas_quality')?.value;

    console.log('Valeurs actuelles:');
    console.log('- canvas_max_size:', canvasMaxSize);
    console.log('- canvas_dpi:', canvasDpi);
    console.log('- canvas_format:', canvasFormat);
    console.log('- canvas_quality:', canvasQuality);

    // Simuler une sauvegarde
    console.log('Test 2: Simulation de sauvegarde...');

    // Créer un FormData avec les valeurs de test
    const formData = new FormData();
    formData.append('action', 'pdf_builder_save_settings');
    formData.append('tab', 'contenu');
    formData.append('canvas_max_size', '15000');
    formData.append('canvas_dpi', '600');
    formData.append('canvas_format', 'jpg');
    formData.append('canvas_quality', '95');

    // Ajouter le nonce si disponible
    const nonce = document.querySelector('input[name="pdf_builder_settings_nonce"]')?.value;
    if (nonce) {
        formData.append('pdf_builder_settings_nonce', nonce);
    }

    // Faire la requête AJAX
    fetch(pdfBuilderAjax.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Réponse AJAX:', data);

        if (data.success) {
            console.log('✅ Sauvegarde réussie!');
            console.log('Paramètres sauvegardés:', data.data.saved_settings);

            // Vérifier que les paramètres canvas sont dans la réponse
            const savedSettings = data.data.saved_settings;
            if (savedSettings.canvas_max_size !== undefined &&
                savedSettings.canvas_dpi !== undefined &&
                savedSettings.canvas_format !== undefined &&
                savedSettings.canvas_quality !== undefined) {
                console.log('✅ Tous les paramètres canvas sont retournés dans la réponse');
            } else {
                console.log('❌ Certains paramètres canvas manquent dans la réponse');
            }
        } else {
            console.log('❌ Erreur de sauvegarde:', data.data?.message);
        }
    })
    .catch(error => {
        console.log('❌ Erreur AJAX:', error);
    });
}

// Exécuter le test après le chargement de la page
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', testCanvasSettingsSave);
} else {
    testCanvasSettingsSave();
}