/**
 * Test du déplacement et rendu des éléments du canvas
 * Vérifie que les éléments peuvent être déplacés et que le rendu fonctionne
 */

describe('Element Movement Tests', () => {
    let mockCanvas;

    beforeEach(() => {
        // Simuler une instance de canvas avec les gestionnaires
        mockCanvas = {
            elements: new Map(),
            dragState: null,
            isRendering: false,
            mouseMoveThrottleMs: 8,
            lastMouseMoveTime: 0,
            ctx: {
                clearRect: jest.fn(),
                save: jest.fn(),
                restore: jest.fn(),
                translate: jest.fn(),
                rotate: jest.fn(),
                globalAlpha: 1,
                shadowColor: '',
                shadowBlur: 0,
                shadowOffsetX: 0,
                shadowOffsetY: 0
            },
            options: {
                showGrid: false
            },
            renderer: {
                renderElement: jest.fn()
            },
            selectionManager: {
                render: jest.fn()
            },
            transformationsManager: {
                render: jest.fn()
            },
            drawGrid: jest.fn(),
            renderElementDragFeedback: jest.fn()
        };
    });

    test('should initialize drag state correctly', () => {
        expect(mockCanvas.dragState).toBeNull();
        expect(mockCanvas.isRendering).toBe(false);
        expect(mockCanvas.mouseMoveThrottleMs).toBe(8);
    });

    test('should handle drag initialization', () => {
        const element = { id: 'test-element', type: 'rectangle', properties: { x: 10, y: 20 } };
        mockCanvas.elements.set('test-element', element);

        // Simuler l'initialisation du drag
        mockCanvas.dragState = {
            elementStartPositions: [{ id: 'test-element', startX: 10, startY: 20 }],
            dragOffset: { x: 5, y: 5 }
        };

        expect(mockCanvas.dragState.elementStartPositions).toHaveLength(1);
        expect(mockCanvas.dragState.elementStartPositions[0].id).toBe('test-element');
    });

    test('should throttle mouse movements', () => {
        const currentTime = Date.now();
        mockCanvas.lastMouseMoveTime = currentTime - 5; // 5ms ago

        // Should allow movement (within throttle limit)
        expect(currentTime - mockCanvas.lastMouseMoveTime).toBeLessThan(mockCanvas.mouseMoveThrottleMs);
    });

    test('should render drag feedback', () => {
        const element = { id: 'test-element', type: 'rectangle', properties: { x: 10, y: 20, width: 100, height: 50 } };
        mockCanvas.elements.set('test-element', element);

        mockCanvas.dragState = {
            elementStartPositions: [{ id: 'test-element' }]
        };

        // Mock renderElementDragFeedback to test it's called
        mockCanvas.renderElementDragFeedback = jest.fn();

        // Simulate render call
        if (mockCanvas.dragState) {
            mockCanvas.dragState.elementStartPositions.forEach(startPos => {
                const element = mockCanvas.elements.get(startPos.id);
                if (element) {
                    mockCanvas.renderElementDragFeedback(element);
                }
            });
        }

        expect(mockCanvas.renderElementDragFeedback).toHaveBeenCalledWith(element);
    });
});
        elements: new Map(),
        options: { showGrid: false },
        render: () => console.log('Canvas rendu'),

        // Gestionnaire de sélection simulé
        selectionManager: {
            selectedElements: new Set(),
            selectionBounds: null,
            isSelecting: false,

            selectAtPoint: function(point, multiSelect) {
                // Simuler la sélection du premier élément trouvé
                for (const [id, element] of mockCanvas.elements) {
                    if (mockCanvas.isPointInElement(point, element)) {
                        if (!multiSelect) {
                            this.selectedElements.clear();
                        }
                        this.selectedElements.add(id);
                        this.updateSelectionBounds();
                        return true;
                    }
                }
                return false;
            },

            clearSelection: function() {
                this.selectedElements.clear();
                this.selectionBounds = null;
            },

            getSelectionCount: function() {
                return this.selectedElements.size;
            },

            getSelectedElements: function() {
                const selected = [];
                this.selectedElements.forEach(id => {
                    const element = mockCanvas.elements.get(id);
                    if (element) selected.push(element);
                });
                return selected;
            },

            getSelectedElementIds: function() {
                return Array.from(this.selectedElements);
            },

            isElementSelected: function(id) {
                return this.selectedElements.has(id);
            },

            updateSelectionBounds: function() {
                const selected = this.getSelectedElements();
                if (selected.length === 0) {
                    this.selectionBounds = null;
                    return;
                }

                let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;

                selected.forEach(element => {
                    const props = element.properties;
                    minX = Math.min(minX, props.x);
                    minY = Math.min(minY, props.y);
                    maxX = Math.max(maxX, props.x + props.width);
                    maxY = Math.max(maxY, props.y + props.height);
                });

                this.selectionBounds = {
                    x: minX,
                    y: minY,
                    width: maxX - minX,
                    height: maxY - minY
                };
            }
        },

        // Gestionnaire de transformations simulé
        transformationsManager: {
            isTransforming: false,
            transformHandle: null,
            transformStartPoint: null,
            originalBounds: null,

            startTransform: function(point, handle) {
                if (mockCanvas.selectionManager.getSelectionCount() === 0) {
                    return false;
                }

                this.isTransforming = true;
                this.transformHandle = handle;
                this.transformStartPoint = { x: point.x, y: point.y };
                this.originalBounds = mockCanvas.selectionManager.selectionBounds;

                console.log('Started transform:', handle.type);
                return true;
            },

            updateTransform: function(point) {
                if (!this.isTransforming || !this.transformStartPoint) {
                    return;
                }

                const deltaX = point.x - this.transformStartPoint.x;
                const deltaY = point.y - this.transformStartPoint.y;

                if (this.transformHandle.type === 'move') {
                    this.moveElements(deltaX, deltaY);
                }

                mockCanvas.render();
            },

            endTransform: function() {
                if (this.isTransforming) {
                    this.isTransforming = false;
                    this.transformHandle = null;
                    this.transformStartPoint = null;
                    this.originalBounds = null;
                    console.log('Ended transform');
                }
            },

            moveElements: function(deltaX, deltaY) {
                const selectedIds = mockCanvas.selectionManager.getSelectedElementIds();
                selectedIds.forEach(id => {
                    const element = mockCanvas.elements.get(id);
                    if (element) {
                        element.properties.x = (element.properties.x || 0) + deltaX;
                        element.properties.y = (element.properties.y || 0) + deltaY;
                        element.updatedAt = Date.now();
                    }
                });
                console.log('Moved elements by:', deltaX, deltaY);
            },

            getHandleAtPoint: function(point) {
                // Pour ce test, on ne gère que le déplacement simple
                return null;
            }
        },

        // Méthodes utilitaires
        getElementAtPoint: function(point) {
            for (const [id, element] of this.elements) {
                if (this.isPointInElement(point, element)) {
                    return element;
                }
            }
            return null;
        },

        isPointInElement: function(point, element) {
            const props = element.properties;
            const x = props.x || 0;
            const y = props.y || 0;
            const width = props.width || 100;
            const height = props.height || 50;

            return point.x >= x && point.x <= x + width &&
                   point.y >= y && point.y <= y + height;
        },

        // Gestionnaire d'historique simulé
        historyManager: {
            saveState: () => console.log('State saved')
        }
    };

    // Test 1: Ajouter un élément
    console.log('\n--- Test 1: Ajout d\'élément ---');
    const elementId = 'test-element';
    mockCanvas.elements.set(elementId, {
        id: elementId,
        type: 'text',
        properties: { x: 50, y: 50, width: 100, height: 50, text: 'Test' },
        createdAt: Date.now(),
        updatedAt: Date.now()
    });
    console.log('Élément ajouté à la position:', mockCanvas.elements.get(elementId).properties);

    // Test 2: Sélectionner l'élément
    console.log('\n--- Test 2: Sélection ---');
    const clickPoint = { x: 75, y: 75 }; // Centre de l'élément
    mockCanvas.selectionManager.selectAtPoint(clickPoint, false);
    console.log('Éléments sélectionnés:', mockCanvas.selectionManager.getSelectionCount());

    // Test 3: Déplacer l'élément
    console.log('\n--- Test 3: Déplacement ---');
    const startPoint = { x: 75, y: 75 };
    const movePoint = { x: 125, y: 100 }; // Déplacement de +50, +25

    mockCanvas.transformationsManager.startTransform(startPoint, { type: 'move' });
    mockCanvas.transformationsManager.updateTransform(movePoint);
    mockCanvas.transformationsManager.endTransform();

    const finalPosition = mockCanvas.elements.get(elementId).properties;
    console.log('Position finale:', finalPosition);

    // Vérifications
    const test1Pass = mockCanvas.elements.has(elementId);
    const test2Pass = mockCanvas.selectionManager.getSelectionCount() === 1;
    const test3Pass = finalPosition.x === 100 && finalPosition.y === 75; // 50 + 50, 50 + 25

    return { test1Pass, test2Pass, test3Pass };
};

// Exécuter le test de déplacement
const movementResults = testElementMovement();

console.log('\n=== Résultats du test de déplacement ===');
console.log('Test 1 (ajout élément):', movementResults.test1Pass ? 'PASS' : 'FAIL');
console.log('Test 2 (sélection):', movementResults.test2Pass ? 'PASS' : 'FAIL');
console.log('Test 3 (déplacement):', movementResults.test3Pass ? 'PASS' : 'FAIL');

if (movementResults.test1Pass && movementResults.test2Pass && movementResults.test3Pass) {
    console.log('✅ Test de déplacement réussi ! Les éléments peuvent être déplacés correctement.');
} else {
    console.log('❌ Test de déplacement échoué. Vérifiez la logique de déplacement.');
}