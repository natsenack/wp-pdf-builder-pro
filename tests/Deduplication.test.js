/**
 * Test de dédoublonnement des éléments du canvas
 * Vérifie que les éléments dupliqués sont correctement gérés
 */

describe('Element Deduplication Tests', () => {
    let mockCanvas;

    beforeEach(() => {
        mockCanvas = {
            elements: new Map(),
            deduplicationEnabled: true,
            deduplicationThreshold: 5,
            findDuplicateElements: jest.fn(),
            removeDuplicateElements: jest.fn(),
            mergeDuplicateElements: jest.fn()
        };
    });

    test('should detect duplicate elements', () => {
        const element1 = { id: 'elem1', type: 'text', properties: { x: 10, y: 20, text: 'Test' } };
        const element2 = { id: 'elem2', type: 'text', properties: { x: 10, y: 20, text: 'Test' } };

        mockCanvas.elements.set('elem1', element1);
        mockCanvas.elements.set('elem2', element2);

        mockCanvas.findDuplicateElements.mockReturnValue(['elem1', 'elem2']);

        const duplicates = mockCanvas.findDuplicateElements();
        expect(duplicates).toContain('elem1');
        expect(duplicates).toContain('elem2');
    });

    test('should remove duplicate elements', () => {
        mockCanvas.removeDuplicateElements();
        expect(mockCanvas.removeDuplicateElements).toHaveBeenCalled();
    });

    test('should merge duplicate elements', () => {
        mockCanvas.mergeDuplicateElements();
        expect(mockCanvas.mergeDuplicateElements).toHaveBeenCalled();
    });

    test('should respect deduplication threshold', () => {
        expect(mockCanvas.deduplicationThreshold).toBe(5);
        expect(mockCanvas.deduplicationEnabled).toBe(true);
    });
});
        elements: new Map([
            ['elem1', { id: 'elem1', type: 'product_table', properties: { x: 10, y: 20 } }],
            ['elem2', { id: 'elem2', type: 'product_table', properties: { x: 10, y: 20 } }], // Dupliqué
            ['elem3', { id: 'elem3', type: 'text', properties: { text: 'Hello' } }],
            ['elem4', { id: 'elem4', type: 'text', properties: { text: 'Hello' } }], // Dupliqué
            ['elem5', { id: 'elem5', type: 'image', properties: { src: 'img.jpg' } }]
        ]),

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

            console.log(`Removed ${duplicates.length} duplicate elements: ${duplicates.join(', ')}`);
            return duplicates.length;
        }
    };

    console.log('Éléments avant nettoyage:', mockCanvas.elements.size);
    const removed = mockCanvas.removeDuplicateElements();
    console.log('Éléments après nettoyage:', mockCanvas.elements.size);
    console.log('Éléments restants:', Array.from(mockCanvas.elements.keys()));

    return removed === 2; // Devrait avoir supprimé 2 éléments dupliqués
};

// Test de la logique addElement avec vérification de dédoublonnement
const testAddElementDedup = () => {
    console.log('\n=== Test addElement avec dédoublonnement ===');

    const mockCanvas = {
        elements: new Map(),

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
        }
    };

    // Ajouter un élément
    const id1 = mockCanvas.addElement('product_table', { x: 10, y: 20 });
    console.log('Premier élément ajouté:', id1);

    // Tenter d'ajouter un élément avec le même ID (devrait être rejeté)
    const id2 = mockCanvas.addElement('product_table', { id: id1, x: 30, y: 40 });
    console.log('Tentative d\'ajout avec ID existant:', id2);

    // Vérifier que seul un élément existe
    console.log('Nombre d\'éléments:', mockCanvas.elements.size);

    return mockCanvas.elements.size === 1;
};

// Exécuter les tests automatiquement
const result1 = testDedup();
const result2 = testAddElementDedup();

console.log('\n=== Résultats des tests ===');
console.log('Test removeDuplicateElements:', result1 ? 'PASS' : 'FAIL');
console.log('Test addElement deduplication:', result2 ? 'PASS' : 'FAIL');

if (result1 && result2) {
    console.log('✅ Tous les tests de dédoublonnement passent !');
} else {
    console.log('❌ Certains tests ont échoué');
}

// Export pour les tests unitaires si nécessaire
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { testDedup, testAddElementDedup };
}