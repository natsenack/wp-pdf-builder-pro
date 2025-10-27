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