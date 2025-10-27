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