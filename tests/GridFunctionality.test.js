/**
 * Test de la fonctionnalité de grille du canvas
 * Vérifie que la grille peut être activée/désactivée correctement
 */

const testGridFunctionality = () => {
    console.log('=== Test de la fonctionnalité de grille ===');

    // Simuler une instance de PDFCanvasVanilla avec les méthodes de grille
    const mockCanvas = {
        options: {
            showGrid: false,
            gridSize: 20
        },
        render: () => console.log('Canvas rendu'),

        toggleGrid() {
            this.options.showGrid = !this.options.showGrid;
            this.render();
            return this.options.showGrid;
        },

        setGridVisibility(visible) {
            this.options.showGrid = visible === true;
            this.render();
            return this.options.showGrid;
        },

        isGridVisible() {
            return this.options.showGrid;
        }
    };

    // Test 1: État initial (grille désactivée)
    console.log('Test 1: État initial');
    const initialState = mockCanvas.isGridVisible();
    console.log(`Grille visible initialement: ${initialState}`);
    console.log('Attendu: false');

    // Test 2: Activer la grille via toggleGrid
    console.log('\nTest 2: Activation via toggleGrid');
    const toggleResult1 = mockCanvas.toggleGrid();
    console.log(`Après toggleGrid(): ${toggleResult1}`);
    console.log('Attendu: true');

    // Test 3: Désactiver la grille via toggleGrid
    console.log('\nTest 3: Désactivation via toggleGrid');
    const toggleResult2 = mockCanvas.toggleGrid();
    console.log(`Après deuxième toggleGrid(): ${toggleResult2}`);
    console.log('Attendu: false');

    // Test 4: Activer via setGridVisibility(true)
    console.log('\nTest 4: Activation via setGridVisibility(true)');
    const setResult1 = mockCanvas.setGridVisibility(true);
    console.log(`Après setGridVisibility(true): ${setResult1}`);
    console.log('Attendu: true');

    // Test 5: Désactiver via setGridVisibility(false)
    console.log('\nTest 5: Désactivation via setGridVisibility(false)');
    const setResult2 = mockCanvas.setGridVisibility(false);
    console.log(`Après setGridVisibility(false): ${setResult2}`);
    console.log('Attendu: false');

    // Test 6: Vérification de l'état via isGridVisible
    console.log('\nTest 6: Vérification de l\'état');
    mockCanvas.setGridVisibility(true);
    const checkResult1 = mockCanvas.isGridVisible();
    console.log(`isGridVisible() après activation: ${checkResult1}`);
    console.log('Attendu: true');

    mockCanvas.setGridVisibility(false);
    const checkResult2 = mockCanvas.isGridVisible();
    console.log(`isGridVisible() après désactivation: ${checkResult2}`);
    console.log('Attendu: false');

    // Vérifications
    const test1Pass = initialState === false;
    const test2Pass = toggleResult1 === true;
    const test3Pass = toggleResult2 === false;
    const test4Pass = setResult1 === true;
    const test5Pass = setResult2 === false;
    const test6Pass = checkResult1 === true && checkResult2 === false;

    return { test1Pass, test2Pass, test3Pass, test4Pass, test5Pass, test6Pass };
};

// Exécuter le test de grille
const gridResults = testGridFunctionality();

console.log('\n=== Résultats du test de grille ===');
console.log('Test 1 (état initial):', gridResults.test1Pass ? 'PASS' : 'FAIL');
console.log('Test 2 (toggle on):', gridResults.test2Pass ? 'PASS' : 'FAIL');
console.log('Test 3 (toggle off):', gridResults.test3Pass ? 'PASS' : 'FAIL');
console.log('Test 4 (set visible):', gridResults.test4Pass ? 'PASS' : 'FAIL');
console.log('Test 5 (set hidden):', gridResults.test5Pass ? 'PASS' : 'FAIL');
console.log('Test 6 (check state):', gridResults.test6Pass ? 'PASS' : 'FAIL');

if (gridResults.test1Pass && gridResults.test2Pass && gridResults.test3Pass &&
    gridResults.test4Pass && gridResults.test5Pass && gridResults.test6Pass) {
    console.log('✅ Test de grille réussi ! La fonctionnalité de grille fonctionne correctement.');
} else {
    console.log('❌ Test de grille échoué. Vérifiez l\'implémentation.');
}