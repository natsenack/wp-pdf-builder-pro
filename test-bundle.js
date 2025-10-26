// Test rapide du bundle Vanilla JS
// Ce script vérifie que le bundle se charge correctement sans erreurs ES6

console.log('🧪 Test du bundle PDF Builder Vanilla JS...');

try {
    // Vérifier que les modules sont disponibles globalement
    if (typeof window.PDFBuilderVanilla !== 'undefined') {
        console.log('✅ PDFBuilderVanilla disponible globalement');

        // Tester l'initialisation
        const result = window.PDFBuilderVanilla.init();
        if (result) {
            console.log('✅ Initialisation PDFBuilderVanilla réussie');
        } else {
            console.log('❌ Échec de l\'initialisation PDFBuilderVanilla');
        }
    } else {
        console.log('❌ PDFBuilderVanilla non disponible globalement');
    }

    // Vérifier les modules individuels
    const modules = [
        'VanillaCanvas',
        'CanvasRenderer',
        'CanvasEvents',
        'CanvasSelection',
        'CanvasProperties',
        'CanvasLayers',
        'CanvasExport',
        'WooCommerceElementsManager',
        'elementCustomizationService',
        'CanvasOptimizer',
        'CanvasTests'
    ];

    let availableCount = 0;
    modules.forEach(module => {
        if (typeof window[module] !== 'undefined') {
            console.log(`✅ ${module} disponible`);
            availableCount++;
        } else {
            console.log(`❌ ${module} non disponible`);
        }
    });

    console.log(`📊 ${availableCount}/${modules.length} modules disponibles`);

    if (availableCount === modules.length) {
        console.log('🎉 Test du bundle réussi ! Toutes les erreurs ES6 modules devraient être corrigées.');
    } else {
        console.log('⚠️ Certains modules sont manquants, mais le bundle se charge sans erreurs ES6.');
    }

} catch (error) {
    console.error('❌ Erreur lors du test du bundle:', error);
}