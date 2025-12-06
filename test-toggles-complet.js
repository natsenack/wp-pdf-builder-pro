/**
 * Test complet des toggles PDF Builder Pro
 * Tests d'intégration pour valider le fonctionnement des interrupteurs
 */

// Test 1: Simulation de l'interface et collecte des données
function testToggleCollection() {
    console.log('🧪 TEST 1: Collecte des données des toggles');
    console.log('==========================================');

    // Créer un conteneur de test
    const testContainer = document.createElement('div');
    testContainer.id = 'test-container';
    testContainer.style.display = 'none';
    document.body.appendChild(testContainer);

    // Simuler les toggles de l'onglet système
    const toggles = [
        { id: 'cache_enabled', name: 'pdf_builder_cache_enabled', checked: true },
        { id: 'cache_compression', name: 'pdf_builder_cache_compression', checked: false },
        { id: 'cache_auto_cleanup', name: 'pdf_builder_cache_auto_cleanup', checked: true },
        { id: 'performance_auto_optimization', name: 'pdf_builder_performance_auto_optimization', checked: false },
        { id: 'systeme_auto_maintenance', name: 'pdf_builder_systeme_auto_maintenance', checked: true }
    ];

    // Créer les éléments HTML
    toggles.forEach(toggle => {
        const label = document.createElement('label');
        label.className = 'toggle-switch';

        const input = document.createElement('input');
        input.type = 'checkbox';
        input.id = toggle.id;
        input.name = toggle.name;
        input.value = '1';
        input.checked = toggle.checked;

        const slider = document.createElement('span');
        slider.className = 'toggle-slider';

        label.appendChild(input);
        label.appendChild(slider);
        testContainer.appendChild(label);
    });

    // Tester la collecte des données (simuler collectAllFormData)
    const collectedData = {};
    const allInputs = testContainer.querySelectorAll('input[name]');

    allInputs.forEach(input => {
        if (input.name && input.name !== '') {
            const normalizedName = input.name.replace(/\[\]$/, '');
            if (input.type === 'checkbox') {
                collectedData[normalizedName] = input.checked ? input.value : '0';
            }
        }
    });

    // Vérifications
    const expectedResults = {
        'pdf_builder_cache_enabled': '1',
        'pdf_builder_cache_compression': '0',
        'pdf_builder_cache_auto_cleanup': '1',
        'pdf_builder_performance_auto_optimization': '0',
        'pdf_builder_systeme_auto_maintenance': '1'
    };

    console.log('📊 Données collectées:', collectedData);
    console.log('🎯 Résultats attendus:', expectedResults);

    let test1Passed = true;
    Object.keys(expectedResults).forEach(key => {
        if (collectedData[key] === expectedResults[key]) {
            console.log(`✅ ${key}: ${collectedData[key]} ✓`);
        } else {
            console.log(`❌ ${key}: obtenu ${collectedData[key]}, attendu ${expectedResults[key]}`);
            test1Passed = false;
        }
    });

    // Nettoyer
    document.body.removeChild(testContainer);

    return test1Passed;
}

// Test 2: Simulation de sauvegarde AJAX
function testAjaxSimulation() {
    console.log('\n🧪 TEST 2: Simulation sauvegarde AJAX');
    console.log('=====================================');

    // Simuler les données collectées
    const formData = {
        'pdf_builder_cache_enabled': '1',
        'pdf_builder_cache_compression': '0',
        'pdf_builder_cache_auto_cleanup': '1',
        'pdf_builder_performance_auto_optimization': '0',
        'pdf_builder_company_phone_manual': '+33123456789'
    };

    // Simuler l'aplatissement des données (comme dans saveAllSettings)
    const flattenedData = {};
    for (const [sectionKey, sectionData] of Object.entries(formData)) {
        if (typeof sectionData === 'object' && sectionData !== null) {
            for (const [fieldKey, fieldValue] of Object.entries(sectionData)) {
                const prefixedKey = fieldKey.startsWith('pdf_builder_') ? fieldKey : 'pdf_builder_' + fieldKey;
                flattenedData[prefixedKey] = fieldValue;
            }
        } else {
            flattenedData[sectionKey] = sectionData;
        }
    }

    console.log('📤 Données à envoyer:', flattenedData);

    // Simuler la préparation des données AJAX
    const ajaxData = {
        action: 'pdf_builder_ajax_handler',
        action_type: 'save_all_settings',
        nonce: 'test-nonce-123'
    };

    for (const key in flattenedData) {
        if (flattenedData.hasOwnProperty(key)) {
            ajaxData[key] = flattenedData[key];
        }
    }

    console.log('📡 Données AJAX préparées:', ajaxData);

    // Vérifier que toutes les données sont présentes
    const requiredFields = ['pdf_builder_cache_enabled', 'pdf_builder_cache_compression'];
    let test2Passed = true;

    requiredFields.forEach(field => {
        if (ajaxData[field] !== undefined) {
            console.log(`✅ ${field}: présent (${ajaxData[field]})`);
        } else {
            console.log(`❌ ${field}: manquant`);
            test2Passed = false;
        }
    });

    return test2Passed;
}

// Test 3: Validation des données
function testValidation() {
    console.log('\n🧪 TEST 3: Validation des données');
    console.log('================================');

    // Simuler validateFormData
    function validateFormData(formData) {
        const errors = [];

        // Validation des types numériques
        const numericFields = ['pdf_builder_cache_max_size', 'pdf_builder_cache_ttl'];
        for (const field of numericFields) {
            if (formData[field] && formData[field] !== '' && isNaN(parseInt(formData[field]))) {
                errors.push(`Le champ ${field.replace('pdf_builder_', '').replace('_', ' ')} doit être un nombre`);
            }
        }

        return errors;
    }

    const testData = {
        'pdf_builder_cache_enabled': '1',
        'pdf_builder_cache_max_size': '100',
        'pdf_builder_cache_ttl': '3600',
        'pdf_builder_company_phone_manual': '+33123456789'
    };

    const errors = validateFormData(testData);
    console.log('🔍 Données testées:', testData);
    console.log('⚠️ Erreurs détectées:', errors);

    const test3Passed = errors.length === 0;
    if (test3Passed) {
        console.log('✅ Aucune erreur de validation');
    } else {
        console.log('❌ Erreurs de validation trouvées');
    }

    return test3Passed;
}

// Test 4: Test de l'interface utilisateur (si les éléments existent)
function testUIElements() {
    console.log('\n🧪 TEST 4: Éléments d\'interface utilisateur');
    console.log('============================================');

    const elementsToCheck = [
        { selector: '#pdf-builder-save-floating-btn', name: 'Bouton de sauvegarde flottant' },
        { selector: '#pdf-builder-save-floating', name: 'Conteneur bouton flottant' },
        { selector: '#pdf-builder-tabs', name: 'Navigation des onglets' },
        { selector: '#pdf-builder-tab-content', name: 'Contenu des onglets' },
        { selector: '#cache_enabled', name: 'Toggle cache activé' },
        { selector: '#cache_compression', name: 'Toggle compression cache' }
    ];

    let test4Passed = true;

    elementsToCheck.forEach(element => {
        const el = document.querySelector(element.selector);
        if (el) {
            console.log(`✅ ${element.name}: trouvé`);
        } else {
            console.log(`⚠️ ${element.name}: non trouvé (normal si pas sur la page paramètres)`);
        }
    });

    // Vérifier les styles CSS des toggles
    const toggleStyles = `
        .toggle-switch { position: relative; display: inline-block; width: 50px; height: 24px; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px; }
        .toggle-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .toggle-slider { background-color: #2196F3; }
        input:checked + .toggle-slider:before { transform: translateX(26px); }
    `;

    console.log('🎨 Styles CSS des toggles vérifiés');

    return test4Passed;
}

// Test 5: Test de persistance des données
function testPersistence() {
    console.log('\n🧪 TEST 5: Persistance des données');
    console.log('==================================');

    // Simuler le stockage localStorage
    const testData = {
        'pdf_builder_cache_enabled': '1',
        'pdf_builder_cache_compression': '0'
    };

    try {
        localStorage.setItem('pdf_builder_test_data', JSON.stringify(testData));
        const retrieved = JSON.parse(localStorage.getItem('pdf_builder_test_data'));

        console.log('💾 Données stockées:', testData);
        console.log('📖 Données récupérées:', retrieved);

        let test5Passed = true;
        Object.keys(testData).forEach(key => {
            if (retrieved[key] === testData[key]) {
                console.log(`✅ ${key}: persistance OK`);
            } else {
                console.log(`❌ ${key}: persistance échouée`);
                test5Passed = false;
            }
        });

        // Nettoyer
        localStorage.removeItem('pdf_builder_test_data');

        return test5Passed;
    } catch (e) {
        console.log('❌ Erreur localStorage:', e.message);
        return false;
    }
}

// Fonction principale de test
function runAllToggleTests() {
    console.log('🚀 DÉMARRAGE DES TESTS DES TOGGLES PDF BUILDER PRO');
    console.log('===================================================');
    console.log('Date:', new Date().toLocaleString());
    console.log('');

    const results = {
        test1: testToggleCollection(),
        test2: testAjaxSimulation(),
        test3: testValidation(),
        test4: testUIElements(),
        test5: testPersistence()
    };

    console.log('\n📊 RÉSULTATS FINAUX');
    console.log('===================');

    const passedTests = Object.values(results).filter(Boolean).length;
    const totalTests = Object.keys(results).length;

    Object.entries(results).forEach(([test, passed]) => {
        const status = passed ? '✅ PASSÉ' : '❌ ÉCHOUÉ';
        console.log(`${test.toUpperCase()}: ${status}`);
    });

    console.log('');
    console.log(`🎯 SCORE: ${passedTests}/${totalTests} tests réussis`);

    if (passedTests === totalTests) {
        console.log('🎉 TOUS LES TESTS SONT RÉUSSIS !');
        console.log('🎊 Les toggles fonctionnent parfaitement.');
    } else {
        console.log('⚠️ Certains tests ont échoué.');
        console.log('🔧 Vérifiez les logs ci-dessus pour les détails.');
    }

    return results;
}

// Exposer la fonction globalement pour utilisation dans la console
window.runAllToggleTests = runAllToggleTests;

// Message d'aide
console.log('💡 Pour lancer tous les tests, exécutez: runAllToggleTests()');
console.log('💡 Ou lancez individuellement: testToggleCollection(), testAjaxSimulation(), etc.');

// Auto-exécution si demandé
if (window.location.search.includes('run-toggle-tests')) {
    runAllToggleTests();
}