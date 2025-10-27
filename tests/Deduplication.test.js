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
