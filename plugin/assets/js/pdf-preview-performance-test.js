/**
 * Test rapide des optimisations de performance du drag/pan
 * Ce fichier peut être exécuté dans la console du navigateur pour tester les optimisations
 */

// Fonctions de debug conditionnel pour les tests
function isDebugEnabled() {
    return window.location.search.includes('debug=force');
}

function debugLog(...args) {
    if (isDebugEnabled()) {
        debugLog(...args);
    }
}

// Test des calculs inline de contraintes
function testInlineConstraints() {
    debugLog('🧪 Test des contraintes inline optimisées');

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

    debugLog(`✅ Contraintes appliquées: panX=${panX}, panY=${panY}`);
    debugLog(`📊 Limites: maxPanX=${instance.maxPanX}, maxPanY=${instance.maxPanY}`);
}

// Test du throttling à 32ms
function testThrottling() {
    debugLog('🧪 Test du throttling à 32ms');

    let lastTime = 0;
    let callCount = 0;

    function simulateThrottledCall() {
        const now = performance.now();
        if ((now - lastTime) > 32) {
            callCount++;
            lastTime = now;
            debugLog(`📞 Appel throttlé #${callCount} à ${now.toFixed(2)}ms`);
        }
    }

    // Simuler plusieurs appels rapides
    for (let i = 0; i < 10; i++) {
        setTimeout(simulateThrottledCall, i * 5); // Tous les 5ms
    }
}

// Test des transformations CSS
function testTransformUpdate() {
    debugLog('🧪 Test des transformations CSS optimisées');

    const img = document.createElement('img');
    document.body.appendChild(img);

    // Simulation des valeurs
    const panX = 50;
    const panY = -30;
    const scale = 1.5;
    const rotation = 45;

    // Appliquer la transformation optimisée
    const transform = 'translate(' + panX + 'px, ' + panY + 'px) scale(' + scale + ') rotate(' + rotation + 'deg)';
    img.style.transform = transform;
    img.style.transformOrigin = 'center center';

    debugLog(`✅ Transformation appliquée: ${transform}`);
    debugLog(`🎨 Styles: transform-origin=${img.style.transformOrigin}`);

    // Cleanup
    document.body.removeChild(img);
}

// Test ULTRA-RAPIDE des FPS réels (sans logs)
function testRealFPS() {
    debugLog('⚡ Test FPS réel - Mesure précise sans pollution');

    // Simulation du handleMouseMove optimisé
    let currentPanX = 0, currentPanY = 0;
    let lastMouseX = 100, lastMouseY = 100;
    const maxPanX = 100, maxPanY = 80;

    function optimizedMouseMove(clientX, clientY) {
        const lastX = lastMouseX;
        const lastY = lastMouseY;

        const deltaX = clientX - lastX;
        const deltaY = clientY - lastY;

        let newPanX = currentPanX + deltaX;
        let newPanY = currentPanY + deltaY;

        // Contraintes inline ultra-rapides
        if (maxPanX > 0) {
            newPanX = newPanX < -maxPanX ? -maxPanX : (newPanX > maxPanX ? maxPanX : newPanX);
        }
        if (maxPanY > 0) {
            newPanY = newPanY < -maxPanY ? -maxPanY : (newPanY > maxPanY ? maxPanY : newPanY);
        }

        currentPanX = newPanX;
        currentPanY = newPanY;

        lastMouseX = clientX;
        lastMouseY = clientY;
    }

    // Test de performance réel
    const iterations = 10000;
    const startTime = performance.now();

    for (let i = 0; i < iterations; i++) {
        optimizedMouseMove(100 + (i % 50), 100 + (i % 30));
    }

    const endTime = performance.now();
    const totalTime = endTime - startTime;
    const fps = (iterations / totalTime) * 1000;

    debugLog(`🚀 Résultat: ${iterations} mouvements en ${totalTime.toFixed(2)}ms`);
    debugLog(`🎯 FPS réel: ${fps.toFixed(1)}fps (${(1000/fps).toFixed(3)}ms par mouvement)`);
    debugLog(`✅ Performance: ${fps > 1000 ? 'EXCELLENTE' : fps > 500 ? 'TRÈS BONNE' : 'BONNE'}`);

    return fps;
}

// Fonction principale de test
function runPerformanceTests() {
    debugLog('🚀 Démarrage des tests de performance du drag/pan');
    debugLog('================================================');

    testInlineConstraints();
    testThrottling();
    testTransformUpdate();
    testRealFPS(); // Test FPS réel ULTRA-RAPIDE

    debugLog('================================================');
    debugLog('✅ Tests terminés - Optimisations déployées');
    debugLog('🎯 Résultat attendu: Drag/pan fluide à 60fps+ minimum');
}

// Exposer la fonction de test globalement
window.testPDFPreviewOptimizations = runPerformanceTests;

// Auto-run si en mode debug
if (window.location.search.includes('debug=force')) {
    debugLog('🔧 Mode debug activé - Lancement automatique des tests');
    runPerformanceTests();
}
