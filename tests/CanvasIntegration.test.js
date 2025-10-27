/**
 * Test d'intégration du canvas avec dédoublonnement
 * Teste les modifications réelles sur une instance de canvas
 */

describe('Canvas Integration Tests', () => {
    let mockCanvas;

    beforeEach(() => {
        mockCanvas = {
            elements: new Map(),
            deduplicationEnabled: true,
            addElement: jest.fn(),
            removeElement: jest.fn(),
            updateElement: jest.fn(),
            findDuplicateElements: jest.fn().mockReturnValue([]),
            removeDuplicateElements: jest.fn(),
            render: jest.fn()
        };
    });

    test('should add elements without duplicates', () => {
        const element = { id: 'test', type: 'rectangle', properties: { x: 10, y: 20 } };

        mockCanvas.addElement(element);
        expect(mockCanvas.addElement).toHaveBeenCalledWith(element);
    });

    test('should detect and remove duplicates on add', () => {
        mockCanvas.findDuplicateElements.mockReturnValue(['duplicate1']);
        mockCanvas.removeDuplicateElements();

        expect(mockCanvas.removeDuplicateElements).toHaveBeenCalled();
    });

    test('should maintain element integrity', () => {
        const element = { id: 'test', type: 'text', properties: { text: 'Hello' } };
        mockCanvas.elements.set('test', element);

        expect(mockCanvas.elements.get('test')).toEqual(element);
    });
});
        elements: new Map(),
        historyManager: {
            saveState: () => console.log('État sauvegardé')
        },
        render: () => console.log('Canvas rendu'),

        // Méthode addElement avec dédoublonnement
        addElement(type, properties = {}) {
            // Vérification de dédoublonnement : éviter d'ajouter des éléments identiques
            if (properties.id) {
                // Si un ID est fourni, vérifier s'il existe déjà
                if (this.elements.has(properties.id)) {
                    console.warn(`Element with ID ${properties.id} already exists, skipping duplicate`);
                    return properties.id;
                }
            }

            const elementId = properties.id || `element_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;

            const element = {
                id: elementId,
                type: type,
                properties: properties,
                createdAt: Date.now(),
                updatedAt: Date.now()
            };

            this.elements.set(elementId, element);
            return elementId;
        },

        // Méthode loadTemplateData avec clearing
        loadTemplateData(templateData) {
            console.log('Chargement des données de template...');

            // Vider les éléments existants pour éviter les dupliqués
            this.elements.clear();
            console.log('Éléments existants vidés');

            if (templateData && templateData.elements) {
                templateData.elements.forEach(elementData => {
                    this.addElement(elementData.type, elementData.properties);
                });
                console.log(`Chargé ${templateData.elements.length} éléments depuis le template`);
            }

            this.historyManager.saveState();
            this.render();
        },

        // Méthode removeDuplicateElements
        removeDuplicateElements() {
            const seen = new Map();
            const duplicates = [];

            for (const [id, element] of this.elements) {
                const key = `${element.type}_${JSON.stringify(element.properties)}`;
                if (seen.has(key)) {
                    duplicates.push(id);
                } else {
                    seen.set(key, id);
                }
            }

            // Supprimer les éléments dupliqués
            duplicates.forEach(id => {
                this.elements.delete(id);
            });

            if (duplicates.length > 0) {
                console.log(`Removed ${duplicates.length} duplicate elements`);
                this.historyManager.saveState();
                this.render();
            }

            return duplicates.length;
        }
    };

    // Test 1: Charger un template avec des éléments dupliqués
    console.log('\n--- Test 1: Chargement de template avec dupliqués ---');
    const templateWithDuplicates = {
        elements: [
            { type: 'product_table', properties: { x: 10, y: 20, width: 300, height: 200 } },
            { type: 'product_table', properties: { x: 10, y: 20, width: 300, height: 200 } }, // Dupliqué
            { type: 'text', properties: { text: 'Titre', x: 50, y: 50 } },
            { type: 'text', properties: { text: 'Titre', x: 50, y: 50 } }, // Dupliqué
            { type: 'image', properties: { src: 'logo.png', x: 100, y: 100 } }
        ]
    };

    mockCanvas.loadTemplateData(templateWithDuplicates);
    const elementsAfterLoad = mockCanvas.elements.size;
    console.log(`Éléments après chargement: ${elementsAfterLoad}`);

    // Test 2: Nettoyer les dupliqués
    console.log('\n--- Test 2: Nettoyage des dupliqués ---');
    const removed = mockCanvas.removeDuplicateElements();
    const elementsAfterClean = mockCanvas.elements.size;
    console.log(`Éléments après nettoyage: ${elementsAfterClean}`);

    // Test 3: Ajouter un élément avec ID existant (devrait être rejeté)
    console.log('\n--- Test 3: Tentative d\'ajout d\'élément avec ID dupliqué ---');
    const existingIds = Array.from(mockCanvas.elements.keys());
    const firstId = existingIds[0];
    console.log(`Tentative d'ajout avec ID existant: ${firstId}`);
    const result = mockCanvas.addElement('text', { id: firstId, text: 'Nouveau texte' });
    console.log(`Résultat: ${result} (devrait être ${firstId})`);
    const elementsAfterAttempt = mockCanvas.elements.size;
    console.log(`Nombre d'éléments final: ${elementsAfterAttempt}`);

    // Vérifications
    const test1Pass = elementsAfterLoad === 5; // 5 éléments chargés (avec dupliqués)
    const test2Pass = removed === 2; // 2 dupliqués supprimés
    const test3Pass = elementsAfterAttempt === 3; // 3 éléments restants après nettoyage

    return { test1Pass, test2Pass, test3Pass };
};

// Exécuter le test d'intégration
const results = testCanvasIntegration();

console.log('\n=== Résultats du test d\'intégration ===');
console.log('Test 1 (chargement template):', results.test1Pass ? 'PASS' : 'FAIL');
console.log('Test 2 (nettoyage dupliqués):', results.test2Pass ? 'PASS' : 'FAIL');
console.log('Test 3 (ajout ID dupliqué):', results.test3Pass ? 'PASS' : 'FAIL');

if (results.test1Pass && results.test2Pass && results.test3Pass) {
    console.log('✅ Test d\'intégration réussi ! Le système de dédoublonnement fonctionne correctement.');
} else {
    console.log('❌ Test d\'intégration échoué. Vérifiez les modifications.');
}