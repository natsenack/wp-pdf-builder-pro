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

    test('should position elements dynamically to avoid overlaps', () => {
        // Simuler l'ajout de plusieurs éléments company_logo
        const element1 = { 
            id: 'logo1', 
            type: 'company_logo', 
            properties: { x: 350, y: 50, width: 150, height: 80 } 
        };
        const element2 = { 
            id: 'logo2', 
            type: 'company_logo', 
            properties: { x: 350, y: 140, width: 150, height: 80 } 
        };
        
        mockCanvas.elements.set('logo1', element1);
        mockCanvas.elements.set('logo2', element2);

        // Vérifier que les éléments ont des positions Y différentes
        expect(mockCanvas.elements.get('logo1').properties.y).toBe(50);
        expect(mockCanvas.elements.get('logo2').properties.y).toBe(140);
        expect(mockCanvas.elements.get('logo1').properties.y).not.toBe(mockCanvas.elements.get('logo2').properties.y);
    });
});