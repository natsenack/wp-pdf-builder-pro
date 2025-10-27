/**
 * PDF Canvas Transformations Manager - Gestionnaire de transformations d'éléments
 * Système complet de déplacement, redimensionnement et rotation
 */

export class PDFCanvasTransformationsManager {
    constructor(canvasInstance) {
        this.canvasInstance = canvasInstance;
        this.isTransforming = false;
        this.transformHandle = null;
        this.transformStartPoint = null;
        this.originalBounds = null;
        this.snapToGrid = false;
        this.gridSize = 20;
        this.minSize = 10;

        // Configuration des handles
        this.handleSize = 12;
        this.handleColor = '#007bff';
        this.handleBorderColor = '#ffffff';
        this.rotationHandleColor = '#ff6b35';
    }

    /**
     * Démarre une transformation
     */
    startTransform(point, handle) {
        if (!this.canvasInstance.selectionManager ||
            this.canvasInstance.selectionManager.getSelectionCount() === 0) {
            return false;
        }

        this.isTransforming = true;
        this.transformHandle = handle;
        // Utiliser la position de la souris comme point de référence pour les transformations
        this.transformStartPoint = { x: point.x, y: point.y };

        // Sauvegarder les bounds originaux de l'élément spécifique (si handle.element existe)
        // ou des éléments sélectionnés
        if (handle.element) {
            const props = handle.element.properties;
            if (props) {
                this.originalBounds = {
                    x: props.x || 0,
                    y: props.y || 0,
                    width: props.width || 100,
                    height: props.height || 50
                };
            } else {
                console.warn('startTransform: handle.element.properties is undefined');
                this.originalBounds = { x: 0, y: 0, width: 100, height: 50 };
            }
        } else {
            this.originalBounds = this.canvasInstance.selectionManager.selectionBounds;
        }

        return true;
    }

    /**
     * Met à jour la transformation en cours
     */
    updateTransform(point) {
        if (!this.isTransforming || !this.transformStartPoint || !this.originalBounds) {
            return;
        }

        const deltaX = point.x - this.transformStartPoint.x;
        const deltaY = point.y - this.transformStartPoint.y;

        // Appliquer la transformation selon le type de handle
        switch (this.transformHandle.type) {
            case 'move':
                this.moveElements(deltaX, deltaY);
                break;
            case 'resize':
                this.resizeElements(this.transformHandle.position, deltaX, deltaY);
                break;
            case 'rotate':
                this.rotateElements(deltaX, deltaY);
                break;
        }

        this.canvasInstance.render();
    }

    /**
     * Termine la transformation
     */
    endTransform() {
        if (this.isTransforming) {
            this.isTransforming = false;
            this.transformHandle = null;
            this.transformStartPoint = null;
            this.originalBounds = null;

            // Sauvegarder dans l'historique
            if (this.canvasInstance.historyManager) {
                this.canvasInstance.historyManager.saveState();
            }
        }
    }

    /**
     * Déplace un élément spécifique
     */
    moveElement(element, deltaX, deltaY) {
        const original = this.originalBounds;

        // Safeguard
        if (!element || !element.properties) {
            console.warn('moveElement: invalid element or properties', element);
            return;
        }

        // Calculer la nouvelle position de manière absolue
        let newX = original.x + deltaX;
        let newY = original.y + deltaY;

        // Appliquer le snapping si activé
        if (this.snapToGrid) {
            const snapped = this.snapToGridPoint(newX, newY);
            newX = snapped.x;
            newY = snapped.y;
        }

        element.properties.x = newX;
        element.properties.y = newY;
        element.updatedAt = Date.now();
    }

    /**
     * Déplace les éléments sélectionnés
     */
    moveElements(deltaX, deltaY) {
        // Si on a un élément spécifique dans le handle, ne déplacer que cet élément
        if (this.transformHandle && this.transformHandle.element) {
            this.moveElement(this.transformHandle.element, deltaX, deltaY);
        } else {
            // Sinon, déplacer tous les éléments sélectionnés
            const selectedIds = this.canvasInstance.selectionManager.getSelectedElementIds();
            selectedIds.forEach(elementId => {
                const element = this.canvasInstance.elements.get(elementId);
                if (element) {
                    this.moveElement(element, deltaX, deltaY);
                }
            });
        }

        // Mettre à jour les bounds de sélection
        this.canvasInstance.selectionManager.updateSelectionBounds();
    }

    /**
     * Redimensionne les éléments sélectionnés
     */
    resizeElements(handlePosition, deltaX, deltaY) {
        // Si on a un élément spécifique dans le handle, ne redimensionner que cet élément
        if (this.transformHandle && this.transformHandle.element) {
            this.resizeElement(this.transformHandle.element, handlePosition, deltaX, deltaY);
        } else {
            // Sinon, redimensionner tous les éléments sélectionnés (pour compatibilité)
            const selectedIds = this.canvasInstance.selectionManager.getSelectedElementIds();
            selectedIds.forEach(elementId => {
                const element = this.canvasInstance.elements.get(elementId);
                if (element) {
                    this.resizeElement(element, handlePosition, deltaX, deltaY);
                }
            });
        }

        // Mettre à jour les bounds de sélection
        this.canvasInstance.selectionManager.updateSelectionBounds();
    }

    /**
     * Redimensionne un élément spécifique
     */
    resizeElement(element, handlePosition, deltaX, deltaY) {
        // Safeguard
        if (!element || !element.properties) {
            console.warn('resizeElement: invalid element or properties', element);
            return;
        }

        const props = element.properties;
        const original = this.originalBounds;

        // Calculer les nouvelles dimensions de manière absolue par rapport aux dimensions originales
        let newX = original.x;
        let newY = original.y;
        let newWidth = original.width;
        let newHeight = original.height;

        // Appliquer la transformation selon le handle
        switch (handlePosition) {
            case 'nw': // Nord-Ouest
                newX = original.x + deltaX;
                newY = original.y + deltaY;
                newWidth = Math.max(this.minSize, original.width - deltaX);
                newHeight = Math.max(this.minSize, original.height - deltaY);
                break;
            case 'ne': // Nord-Est
                newY = original.y + deltaY;
                newWidth = Math.max(this.minSize, original.width + deltaX);
                newHeight = Math.max(this.minSize, original.height - deltaY);
                break;
            case 'sw': // Sud-Ouest
                newX = original.x + deltaX;
                newWidth = Math.max(this.minSize, original.width - deltaX);
                newHeight = Math.max(this.minSize, original.height + deltaY);
                break;
            case 'se': // Sud-Est
                newWidth = Math.max(this.minSize, original.width + deltaX);
                newHeight = Math.max(this.minSize, original.height + deltaY);
                break;
            case 'n': // Nord
                newY = original.y + deltaY;
                newHeight = Math.max(this.minSize, original.height - deltaY);
                break;
            case 's': // Sud
                newHeight = Math.max(this.minSize, original.height + deltaY);
                break;
            case 'w': // Ouest
                newX = original.x + deltaX;
                newWidth = Math.max(this.minSize, original.width - deltaX);
                break;
            case 'e': // Est
                newWidth = Math.max(this.minSize, original.width + deltaX);
                break;
        }

        // Appliquer les nouvelles propriétés
        props.x = newX;
        props.y = newY;
        props.width = newWidth;
        props.height = newHeight;
        element.updatedAt = Date.now();
    }

    /**
     * Fait pivoter les éléments sélectionnés
     */
    rotateElements(deltaX, deltaY) {
        // Calculer l'angle de rotation basé sur le mouvement
        const centerX = this.originalBounds.x + this.originalBounds.width / 2;
        const centerY = this.originalBounds.y + this.originalBounds.height / 2;

        const angle1 = Math.atan2(
            this.transformStartPoint.y - centerY,
            this.transformStartPoint.x - centerX
        );
        const angle2 = Math.atan2(
            this.transformStartPoint.y + deltaY - centerY,
            this.transformStartPoint.x + deltaX - centerX
        );

        const rotationDelta = angle2 - angle1;

        const selectedIds = this.canvasInstance.selectionManager.getSelectedElementIds();
        selectedIds.forEach(elementId => {
            const element = this.canvasInstance.elements.get(elementId);
            if (element && element.properties) {
                element.properties.rotation = (element.properties.rotation || 0) + rotationDelta;
                element.updatedAt = Date.now();
            }
        });
    }

    /**
     * Calcule la position arrondie à la grille
     */
    snapToGridPoint(x, y) {
        return {
            x: Math.round(x / this.gridSize) * this.gridSize,
            y: Math.round(y / this.gridSize) * this.gridSize
        };
    }

    /**
     * Obtient le handle à une position donnée (pour tous les éléments sélectionnés)
     */
    getHandleAtPoint(point) {
        const selectedElements = this.canvasInstance.selectionManager.getSelectedElements();

        for (const element of selectedElements) {
            const handle = this.getHandleAtPointForElement(point, element);
            if (handle) {
                return handle;
            }
        }

        return null;
    }

    /**
     * Obtient le handle à une position donnée pour un élément spécifique
     */
    getHandleAtPointForElement(point, element) {
        // Safeguard
        if (!element || !element.properties) {
            return null;
        }

        const props = element.properties;
        const handles = this.getResizeHandles(props);

        for (const [position, handle] of Object.entries(handles)) {
            const distance = Math.sqrt(
                Math.pow(point.x - handle.x, 2) + Math.pow(point.y - handle.y, 2)
            );

            if (distance <= this.handleSize / 2) {
                return {
                    type: 'resize',
                    position: position,
                    element: element,
                    x: handle.x,
                    y: handle.y
                };
            }
        }

        return null;
    }

    /**
     * Obtient les positions des handles de redimensionnement
     */
    getResizeHandles(props) {
        // Safeguard against undefined properties
        if (!props) {
            console.warn('getResizeHandles: props is undefined');
            return {
                nw: { x: 0, y: 0 },
                n:  { x: 50, y: 0 },
                ne: { x: 100, y: 0 },
                w:  { x: 0, y: 25 },
                e:  { x: 100, y: 25 },
                sw: { x: 0, y: 50 },
                s:  { x: 50, y: 50 },
                se: { x: 100, y: 50 }
            };
        }

        const x = props.x || 0;
        const y = props.y || 0;
        const w = props.width || 100;
        const h = props.height || 50;

        return {
            nw: { x: x, y: y },           // Nord-Ouest
            n:  { x: x + w/2, y: y },     // Nord
            ne: { x: x + w, y: y },       // Nord-Est
            w:  { x: x, y: y + h/2 },     // Ouest
            e:  { x: x + w, y: y + h/2 }, // Est
            sw: { x: x, y: y + h },       // Sud-Ouest
            s:  { x: x + w/2, y: y + h }, // Sud
            se: { x: x + w, y: y + h }    // Sud-Est
        };
    }

    /**
     * Rend les handles de transformation
     */
    render(ctx) {
        if (!this.canvasInstance.selectionManager ||
            this.canvasInstance.selectionManager.getSelectionCount() === 0) {
            return;
        }

        const selectedElements = this.canvasInstance.selectionManager.getSelectedElements();

        selectedElements.forEach(element => {
            this.renderElementHandles(ctx, element);
        });
    }

    /**
     * Rend les handles pour un élément
     */
    renderElementHandles(ctx, element) {
        // Safeguard against invalid element or properties
        if (!element || !element.properties) {
            console.warn('renderElementHandles: invalid element or properties', element);
            return;
        }

        const handles = this.getResizeHandles(element.properties);

        ctx.save();

        // Dessiner les handles de redimensionnement
        Object.values(handles).forEach(handle => {
            ctx.fillStyle = this.handleColor;
            ctx.strokeStyle = this.handleBorderColor;
            ctx.lineWidth = 1;

            ctx.beginPath();
            ctx.rect(
                handle.x - this.handleSize / 2,
                handle.y - this.handleSize / 2,
                this.handleSize,
                this.handleSize
            );
            ctx.fill();
            ctx.stroke();
        });

        // Dessiner le handle de rotation (au-dessus du handle nord)
        const rotationHandle = handles.n;
        ctx.fillStyle = this.rotationHandleColor;
        ctx.strokeStyle = this.handleBorderColor;

        ctx.beginPath();
        ctx.arc(rotationHandle.x, rotationHandle.y - 20, this.handleSize / 2, 0, 2 * Math.PI);
        ctx.fill();
        ctx.stroke();

        ctx.restore();
    }

    /**
     * Configure le gestionnaire
     */
    configure(options) {
        if (options.snapToGrid !== undefined) this.snapToGrid = options.snapToGrid;
        if (options.gridSize !== undefined) this.gridSize = options.gridSize;
        if (options.minSize !== undefined) this.minSize = options.minSize;
        if (options.handleSize !== undefined) this.handleSize = options.handleSize;
    }

    /**
     * Nettoie les ressources
     */
    dispose() {
        this.endTransform();
    }
}

export default PDFCanvasTransformationsManager;