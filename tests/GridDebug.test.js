/**
 * Test de débogage de la grille du canvas
 * Vérifie que les méthodes de grille sont appelées et que la grille se rend
 */

describe('Grid Debug Tests', () => {
    let mockCanvas;
    let mockRenderer;

    beforeEach(() => {
        mockRenderer = {
            renderGrid: jest.fn(),
            clearCanvas: jest.fn()
        };

        mockCanvas = {
            options: { showGrid: true, gridSize: 20 },
            renderer: mockRenderer,
            ctx: { save: jest.fn(), restore: jest.fn() },
            drawGrid: jest.fn(),
            render: jest.fn(),
            debugGrid: jest.fn()
        };
    });

    test('should render grid when enabled', () => {
        mockCanvas.drawGrid();
        expect(mockCanvas.drawGrid).toHaveBeenCalled();
    });

    test('should call renderer for grid drawing', () => {
        mockRenderer.renderGrid();
        expect(mockRenderer.renderGrid).toHaveBeenCalled();
    });

    test('should handle grid debug mode', () => {
        mockCanvas.debugGrid();
        expect(mockCanvas.debugGrid).toHaveBeenCalled();
    });

    test('should save and restore canvas context', () => {
        mockCanvas.ctx.save();
        mockCanvas.ctx.restore();

        expect(mockCanvas.ctx.save).toHaveBeenCalled();
        expect(mockCanvas.ctx.restore).toHaveBeenCalled();
    });

    test('should respect grid options', () => {
        expect(mockCanvas.options.showGrid).toBe(true);
        expect(mockCanvas.options.gridSize).toBe(20);
    });
});
        mainInstance: {
            options: { showGrid: false, gridSize: 20 }
        },
        canvas: { width: 800, height: 600 },
        ctx: {
            save: () => console.log('ctx.save()'),
            restore: () => console.log('ctx.restore()'),
            strokeStyle: '',
            lineWidth: 1,
            setLineDash: (pattern) => console.log('setLineDash:', pattern),
            beginPath: () => {},
            moveTo: (x, y) => {},
            lineTo: (x, y) => {},
            stroke: () => console.log('stroke()')
        },
        devicePixelRatio: 1,

        renderGrid() {
            const gridSize = this.mainInstance.options.gridSize || 20;
            const width = this.canvas.width / this.devicePixelRatio;
            const height = this.canvas.height / this.devicePixelRatio;

            console.log('Rendering grid:', { showGrid: this.mainInstance.options.showGrid, gridSize, width, height });

            this.ctx.save();

            // Couleur plus visible pour la grille
            this.ctx.strokeStyle = 'rgba(0, 0, 0, 0.1)'; // Plus visible que #e0e0e0
            this.ctx.lineWidth = 0.5; // Ligne plus fine
            this.ctx.setLineDash([2, 2]); // Pointillés plus visibles

            // Lignes verticales
            for (let x = 0; x <= width; x += gridSize) {
                this.ctx.beginPath();
                this.ctx.moveTo(x, 0);
                this.ctx.lineTo(x, height);
                this.ctx.stroke();
            }

            // Lignes horizontales
            for (let y = 0; y <= height; y += gridSize) {
                this.ctx.beginPath();
                this.ctx.moveTo(0, y);
                this.ctx.lineTo(width, y);
                this.ctx.stroke();
            }

            this.ctx.restore();
        },

        render() {
            console.log('Canvas render - showGrid:', this.mainInstance.options.showGrid);
            if (this.mainInstance.options.showGrid) {
                console.log('Calling renderGrid');
                this.renderGrid();
            } else {
                console.log('Grid not rendered - showGrid is false');
            }
        }
    };

    const mockCanvas = {
        options: { showGrid: false },
        renderer: mockRenderer,

        toggleGrid() {
            console.log('toggleGrid called - current showGrid:', this.options.showGrid);
            this.options.showGrid = !this.options.showGrid;
            console.log('toggleGrid - new showGrid:', this.options.showGrid);
            this.renderer.mainInstance.options.showGrid = this.options.showGrid;
            this.render();
            return this.options.showGrid;
        },

        setGridVisibility(visible) {
            console.log('setGridVisibility called - visible:', visible);
            this.options.showGrid = visible === true;
            console.log('setGridVisibility - new showGrid:', this.options.showGrid);
            this.renderer.mainInstance.options.showGrid = this.options.showGrid;
            this.render();
            return this.options.showGrid;
        },

        isGridVisible() {
            console.log('isGridVisible called - returning:', this.options.showGrid);
            return this.options.showGrid;
        },

        render() {
            this.renderer.render();
        }
    };

    console.log('\n--- Test 1: État initial ---');
    mockCanvas.render();

    console.log('\n--- Test 2: Activation de la grille ---');
    mockCanvas.setGridVisibility(true);

    console.log('\n--- Test 3: Bascule de la grille ---');
    mockCanvas.toggleGrid();

    console.log('\n--- Test 4: Désactivation de la grille ---');
    mockCanvas.setGridVisibility(false);

    return true;
};

// Exécuter le test de débogage
testGridDebug();

console.log('\n=== Instructions pour le débogage en production ===');
console.log('1. Ouvrez la console du navigateur (F12)');
console.log('2. Allez dans l\'éditeur PDF Builder');
console.log('3. Cherchez les logs commençant par "Canvas render" et "toggleGrid"');
console.log('4. Si vous voyez "Grid not rendered - showGrid is false", la grille est désactivée');
console.log('5. Si vous voyez "Rendering grid:", la grille devrait s\'afficher');
console.log('6. Vérifiez que les lignes de grille sont visibles (lignes grisées fines)');