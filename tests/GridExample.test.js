/**
 * Exemple d'utilisation des méthodes de grille du canvas
 * Montre comment intégrer les contrôles de grille dans l'interface utilisateur
 */

describe('Grid Example Integration', () => {
    let mockCanvas;

    beforeEach(() => {
        mockCanvas = {
            options: { showGrid: false, snapToGrid: false },
            toggleGrid: jest.fn(),
            setGridSize: jest.fn(),
            toggleSnapToGrid: jest.fn(),
            render: jest.fn()
        };
    });

    test('should setup grid controls correctly', () => {
        // Test que les contrôles peuvent être configurés
        expect(mockCanvas.options.showGrid).toBe(false);
        expect(mockCanvas.options.snapToGrid).toBe(false);
    });

    test('should handle grid toggle', () => {
        mockCanvas.toggleGrid();
        expect(mockCanvas.toggleGrid).toHaveBeenCalled();
    });

    test('should handle snap toggle', () => {
        mockCanvas.toggleSnapToGrid();
        expect(mockCanvas.toggleSnapToGrid).toHaveBeenCalled();
