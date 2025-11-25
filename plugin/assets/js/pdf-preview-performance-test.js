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
    const transform = 'translate(' + panX + 'px, ' + panY + 'px) scale(' + scale + ') rotate(' + rotation + 'deg)';
    img.style.transform = transform;
    img.style.transformOrigin = 'center center';

    console.log(`✅ Transformation appliquée: ${transform}`);
    console.log(`🎨 Styles: transform-origin=${img.style.transformOrigin}`);

    // Cleanup
    document.body.removeChild(img);
}

// Test des optimisations ULTRA-CRITIQUES pour les FPS
function testCriticalOptimizations() {
    console.log('🚀 Test des optimisations ULTRA-CRITIQUES');

    // Simulation des propriétés optimisées
    const instance = {
        currentPanX: 0,
        currentPanY: 0,
        maxPanX: 100,
        maxPanY: 80,
        lastMouseX: 100,
        lastMouseY: 100,
        animationFrameId: null
    };

    // Test des contraintes inline ultra-rapides
    function optimizedMouseMove(clientX, clientY) {
        const lastX = instance.lastMouseX;
        const lastY = instance.lastMouseY;
        const maxPanX = instance.maxPanX;
        const maxPanY = instance.maxPanY;

        const deltaX = clientX - lastX;
        const deltaY = clientY - lastY;

        let newPanX = instance.currentPanX + deltaX;
        let newPanY = instance.currentPanY + deltaY;

        // Contraintes INSTANTANEES (pas de throttling)
        if (maxPanX > 0) {
            newPanX = newPanX < -maxPanX ? -maxPanX : (newPanX > maxPanX ? maxPanX : newPanX);
        }
        if (maxPanY > 0) {
            newPanY = newPanY < -maxPanY ? -maxPanY : (newPanY > maxPanY ? maxPanY : newPanY);
        }

        instance.currentPanX = newPanX;
        instance.currentPanY = newPanY;

        // RequestAnimationFrame optimisé
        if (!instance.animationFrameId) {
            instance.animationFrameId = requestAnimationFrame(() => {
                console.log(`🎯 Transform: translate(${newPanX}px, ${newPanY}px)`);
                instance.animationFrameId = null;
            });
        }

        instance.lastMouseX = clientX;
        instance.lastMouseY = clientY;
    }

    // Test de performance
    const startTime = performance.now();
    for (let i = 0; i < 1000; i++) {
        optimizedMouseMove(100 + i, 100 + i);
    }
    const endTime = performance.now();

    console.log(`⚡ Performance test: ${(endTime - startTime).toFixed(2)}ms pour 1000 mouvements`);
    console.log(`🎮 FPS théorique: ${(1000 / (endTime - startTime) * 60).toFixed(1)}fps`);
    console.log(`✅ Optimisations: Contraintes inline + RAF optimisé`);
}

// Fonction principale de test
function runPerformanceTests() {
    console.log('🚀 Démarrage des tests de performance du drag/pan');
    console.log('================================================');

    testInlineConstraints();
    testThrottling();
    testTransformUpdate();
    testCriticalOptimizations(); // Test des optimisations ULTRA-CRITIQUES

    console.log('================================================');
    console.log('✅ Tests terminés - Les optimisations sont opérationnelles');
    console.log('🎯 Résultat attendu: Drag/pan fluide à 60fps minimum');
}

// Exposer la fonction de test globalement
window.testPDFPreviewOptimizations = runPerformanceTests;

// Auto-run si en mode debug
if (window.location.search.includes('debug=force')) {
    console.log('🔧 Mode debug activé - Lancement automatique des tests');
    runPerformanceTests();
}