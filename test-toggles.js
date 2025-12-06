/**
 * Test rapide des toggles dans l'onglet système
 * À exécuter dans la console du navigateur sur la page des paramètres
 */

function testToggleCollection() {
    console.log('🧪 Test de collecte des toggles système...');

    // Simuler quelques toggles comme dans l'onglet système
    const testToggles = [
        { name: 'pdf_builder_cache_enabled', checked: true, value: '1' },
        { name: 'pdf_builder_cache_compression', checked: false, value: '1' },
        { name: 'pdf_builder_cache_auto_cleanup', checked: true, value: '1' },
        { name: 'pdf_builder_performance_auto_optimization', checked: false, value: '1' }
    ];

    // Créer des éléments de test
    const testContainer = document.createElement('div');
    testContainer.style.display = 'none';
    document.body.appendChild(testContainer);

    testToggles.forEach(toggle => {
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = toggle.name;
        checkbox.value = toggle.value;
        checkbox.checked = toggle.checked;
        testContainer.appendChild(checkbox);
    });

    // Tester la collecte
    const allInputs = testContainer.querySelectorAll('input[name]');
    const collectedData = {};

    allInputs.forEach(input => {
        if (input.name && input.name !== '') {
            const normalizedName = input.name.replace(/\[\]$/, '');
            if (input.type === 'checkbox') {
                collectedData[normalizedName] = input.checked ? input.value : '0';
            }
        }
    });

    // Nettoyer
    document.body.removeChild(testContainer);

    console.log('📊 Données collectées:', collectedData);

    // Vérifications
    const expected = {
        'pdf_builder_cache_enabled': '1',
        'pdf_builder_cache_compression': '0',
        'pdf_builder_cache_auto_cleanup': '1',
        'pdf_builder_performance_auto_optimization': '0'
    };

    let allCorrect = true;
    Object.keys(expected).forEach(key => {
        if (collectedData[key] !== expected[key]) {
            console.error(`❌ ${key}: attendu ${expected[key]}, obtenu ${collectedData[key]}`);
            allCorrect = false;
        } else {
            console.log(`✅ ${key}: ${collectedData[key]}`);
        }
    });

    if (allCorrect) {
        console.log('🎉 Tous les toggles sont correctement collectés!');
    } else {
        console.log('⚠️ Certains toggles ne sont pas correctement collectés');
    }

    return collectedData;
}

// Exécuter le test
testToggleCollection();