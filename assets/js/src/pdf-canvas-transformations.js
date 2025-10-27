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
            this.originalBounds = {
                x: props.x,
                y: props.y,
                width: props.width,
                height: props.height
            };
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
        // Appliquer le snapping si activé
        let finalDeltaX = deltaX;
        let finalDeltaY = deltaY;

        if (this.snapToGrid) {
            const snapped = this.snapToGridPoint(
                element.properties.x + deltaX,
                element.properties.y + deltaY
            );
            finalDeltaX = snapped.x - element.properties.x;
            finalDeltaY = snapped.y - element.properties.y;
        }

        element.properties.x += finalDeltaX;
        element.properties.y += finalDeltaY;
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
        const props = element.properties;
        let newX = props.x;
        let newY = props.y;
        let newWidth = props.width;
        let newHeight = props.height;

        // Calculer les nouvelles dimensions selon le handle
        switch (handlePosition) {
            case 'nw': // Nord-Ouest
                newX = props.x + deltaX;
                newY = props.y + deltaY;
                newWidth = Math.max(this.minSize, props.width - deltaX);
                newHeight = Math.max(this.minSize, props.height - deltaY);
                break;
            case 'ne': // Nord-Est
                newY = props.y + deltaY;
                newWidth = Math.max(this.minSize, props.width + deltaX);
                newHeight = Math.max(this.minSize, props.height - deltaY);
                break;
            case 'sw': // Sud-Ouest
                newX = props.x + deltaX;
                newWidth = Math.max(this.minSize, props.width - deltaX);
                newHeight = Math.max(this.minSize, props.height + deltaY);
                break;
            case 'se': // Sud-Est
                newWidth = Math.max(this.minSize, props.width + deltaX);
                newHeight = Math.max(this.minSize, props.height + deltaY);
                break;
            case 'n': // Nord
                newY = props.y + deltaY;
                newHeight = Math.max(this.minSize, props.height - deltaY);
                break;
            case 's': // Sud
                newHeight = Math.max(this.minSize, props.height + deltaY);
                break;
            case 'w': // Ouest
                newX = props.x + deltaX;
                newWidth = Math.max(this.minSize, props.width - deltaX);
                break;
            case 'e': // Est
                newWidth = Math.max(this.minSize, props.width + deltaX);
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
            if (element) {
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
     * Obtient le handle à une position donnée
     */
    getHandleAtPoint(point, element) {
        if (!element) return null;

        const props = element.properties;
        const handles = this.getResizeHandles(props);

        for (const [position, handle] of Object.entries(handles)) {
            const distance = Math.sqrt(
                Math.pow(point.x - handle.x, 2) + Math.pow(point.y - handle.y, 2)
            );

            if (distance <= this.handleSize) {  // Augmenté de handleSize/2 à handleSize pour une meilleure détection
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
        const x = props.x;
        const y = props.y;
        const w = props.width;
        const h = props.height;

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