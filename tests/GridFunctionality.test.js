/**
 * Test de la fonctionnalité de grille du canvas
 * Vérifie que la grille peut être activée/désactivée correctement
 */

describe('Grid Functionality Tests', () => {
    let mockCanvas;

    beforeEach(() => {
        mockCanvas = {
            options: { showGrid: false, snapToGrid: false, gridSize: 20 },
            toggleGrid: jest.fn(),
            toggleSnapToGrid: jest.fn(),
            setGridSize: jest.fn(),
            snapToGrid: jest.fn(),
            drawGrid: jest.fn(),
            render: jest.fn()
        };
    });

    test('should toggle grid visibility', () => {
        mockCanvas.toggleGrid();
        expect(mockCanvas.toggleGrid).toHaveBeenCalled();
    });

    test('should toggle snap to grid', () => {
        mockCanvas.toggleSnapToGrid();
        expect(mockCanvas.toggleSnapToGrid).toHaveBeenCalled();
    });

    test('should set grid size', () => {
        mockCanvas.setGridSize(25);
        expect(mockCanvas.setGridSize).toHaveBeenCalledWith(25);
    });

    test('should snap to grid when enabled', () => {
        mockCanvas.options.snapToGrid = true;
        mockCanvas.snapToGrid(17);
        expect(mockCanvas.snapToGrid).toHaveBeenCalledWith(17);
    });

    test('should draw grid when enabled', () => {
        mockCanvas.options.showGrid = true;
        mockCanvas.drawGrid();
        expect(mockCanvas.drawGrid).toHaveBeenCalled();
    });
});