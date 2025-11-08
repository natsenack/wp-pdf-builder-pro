// Test JavaScript pour vérifier l'envoi des paramètres canvas
console.log('=== TEST JAVASCRIPT CANVAS ===');

// Simuler la collecte des données du formulaire canvas
function testCanvasDataCollection() {
    console.log('Test de collecte des données canvas...');

    // Simuler les éléments du DOM
    const mockElements = {
        'default_canvas_width': { value: '1200' },
        'default_canvas_height': { value: '1600' },
        'show_grid': { checked: true },
        'grid_size': { value: '15' },
        'snap_to_grid': { checked: false }
    };

    // Simuler la logique de collecte du JavaScript
    const formData = new FormData();
    formData.append('action', 'pdf_builder_save_settings');
    formData.append('current_tab', 'canvas');

    // Collecter les checkboxes
    Object.keys(mockElements).forEach(key => {
        const element = mockElements[key];
        if (element.checked !== undefined) {
            // C'est une checkbox
            formData.append(key, element.checked ? '1' : '0');
            console.log(`Checkbox ${key}: ${element.checked ? '1' : '0'}`);
        } else {
            // C'est un input
            formData.append(key, element.value);
            console.log(`Input ${key}: ${element.value}`);
        }
    });

    console.log('Données collectées:');
    for (let [key, value] of formData.entries()) {
        console.log(`  ${key}: ${value}`);
    }

    return formData;
}

// Tester
testCanvasDataCollection();