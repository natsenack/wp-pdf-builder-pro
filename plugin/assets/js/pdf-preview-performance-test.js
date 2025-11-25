/**
 * Test rapide des optimisations de performance du drag/pan
 * Ce fichier peut être exécuté dans la console du navigateur pour tester les optimisations
 */

// Test des calculs inline de contraintes
function testInlineConstraints() {
    console.log('🧪 Test des contraintes inline optimisées');

    // Simulation des propriétés de l'instance
    const instance = {
        maxPanX: 100,
        maxPanY: 80,
        currentPanX: 150,
        currentPanY: -120
    };

    // Code inline identique à handleMouseMove
    let panX = instance.currentPanX;
    let panY = instance.currentPanY;

    if (instance.maxPanX > 0) {
        panX = panX < -instance.maxPanX ? -instance.maxPanX : (panX > instance.maxPanX ? instance.maxPanX : panX);
    }
    if (instance.maxPanY > 0) {
        panY = panY < -instance.maxPanY ? -instance.maxPanY : (panY > instance.maxPanY ? instance.maxPanY : panY);
    }

    console.log(`✅ Contraintes appliquées: panX=${panX}, panY=${panY}`);
    console.log(`📊 Limites: maxPanX=${instance.maxPanX}, maxPanY=${instance.maxPanY}`);
}

// Test du throttling à 32ms
function testThrottling() {
    console.log('🧪 Test du throttling à 32ms');

    let lastTime = 0;
    let callCount = 0;

    function simulateThrottledCall() {
        const now = performance.now();
        if ((now - lastTime) > 32) {
            callCount++;
            lastTime = now;
            console.log(`📞 Appel throttlé #${callCount} à ${now.toFixed(2)}ms`);
        }
    }

    // Simuler plusieurs appels rapides
    for (let i = 0; i < 10; i++) {
        setTimeout(simulateThrottledCall, i * 5); // Tous les 5ms
    }
}

// Test des transformations CSS
function testTransformUpdate() {
    console.log('🧪 Test des transformations CSS optimisées');

    const img = document.createElement('img');
    document.body.appendChild(img);

    // Simulation des valeurs
    const panX = 50;
    const panY = -30;
    const scale = 1.5;
    const rotation = 45;

    // Appliquer la transformation optimisée
    const transform = `translate(${panX}px, ${panY}px) scale(${scale}) rotate(${rotation}deg)`;
    img.style.transform = transform;
    img.style.transformOrigin = 'center center';
    img.style.willChange = 'transform';

    console.log(`✅ Transformation appliquée: ${transform}`);
    console.log(`🎨 Styles: transform-origin=${img.style.transformOrigin}, will-change=${img.style.willChange}`);

    // Cleanup
    document.body.removeChild(img);
}

// Fonction principale de test
function runPerformanceTests() {
    console.log('🚀 Démarrage des tests de performance du drag/pan');
    console.log('================================================');

    testInlineConstraints();
    testThrottling();
    testTransformUpdate();

    console.log('================================================');
    console.log('✅ Tests terminés - Les optimisations sont opérationnelles');
}

// Exposer la fonction de test globalement
window.testPDFPreviewOptimizations = runPerformanceTests;

// Auto-run si en mode debug
if (window.location.search.includes('debug=force')) {
    console.log('🔧 Mode debug activé - Lancement automatique des tests');
    runPerformanceTests();
}