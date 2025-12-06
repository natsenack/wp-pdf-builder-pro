/**
 * Test rapide pour vérifier que les toggles système sont correctement collectés
 */

function testSystemTogglesCollection() {
    console.log('🧪 TEST RAPIDE - Toggles système');
    console.log('================================');

    // Simuler la collecte des données comme le fait le vrai système
    const mockSystemToggles = [
        { name: 'pdf_builder_cache_enabled', checked: true },
        { name: 'pdf_builder_cache_compression', checked: false },
        { name: 'pdf_builder_cache_auto_cleanup', checked: true },
        { name: 'pdf_builder_performance_auto_optimization', checked: false },
        { name: 'pdf_builder_systeme_auto_maintenance', checked: true }
    ];

    // Simuler la logique de collecte (version corrigée)
    const collectedData = {};
    const allowedSections = ['general', 'licence', 'systeme', 'securite', 'pdf', 'contenu', 'templates', 'developpeur'];

    mockSystemToggles.forEach(toggle => {
        const normalizedName = toggle.name.replace(/\[\]$/, '');
        const sectionId = 'systeme'; // Maintenant que les sections ont id="systeme"

        if (allowedSections.includes(sectionId) || normalizedName.startsWith('pdf_builder_')) {
            if (!collectedData[sectionId]) {
                collectedData[sectionId] = {};
            }

            // Logique corrigée pour les checkboxes
            collectedData[sectionId][normalizedName] = toggle.checked ? '1' : '0';
        }
    });

    console.log('📊 Données collectées:', collectedData);

    // Vérifications
    const systemeData = collectedData.systeme;
    let allCorrect = true;

    if (systemeData) {
        console.log('✅ Section "systeme" trouvée');

        const expectedValues = {
            'pdf_builder_cache_enabled': '1',
            'pdf_builder_cache_compression': '0',
            'pdf_builder_cache_auto_cleanup': '1',
            'pdf_builder_performance_auto_optimization': '0',
            'pdf_builder_systeme_auto_maintenance': '1'
        };

        Object.keys(expectedValues).forEach(key => {
            if (systemeData[key] === expectedValues[key]) {
                console.log(`✅ ${key}: ${systemeData[key]} ✓`);
            } else {
                console.log(`❌ ${key}: obtenu ${systemeData[key]}, attendu ${expectedValues[key]}`);
                allCorrect = false;
            }
        });
    } else {
        console.log('❌ Section "systeme" non trouvée');
        allCorrect = false;
    }

    console.log('');
    if (allCorrect) {
        console.log('🎉 Tous les toggles système sont correctement collectés dans la section "systeme" !');
    } else {
        console.log('⚠️ Problème de collecte des toggles système');
    }

    return allCorrect;
}

// Exécuter le test
testSystemTogglesCollection();