/**
 * PDF Canvas Selection Manager - Gestionnaire de sélection d'éléments
 * Système avancé de sélection simple et multiple avec gestion des groupes
 */

export class PDFCanvasSelectionManager {
    constructor(canvasInstance) {
        this.canvasInstance = canvasInstance;
        this.selectedElements = new Set();
        this.selectionBounds = null;
        this.selectionMode = 'single'; // single, multiple, group
        this.dragSelection = false;
        this.selectionBox = null;

        // Configuration de la sélection
        this.config = {
            showSelectionBox: true,
            showHandles: true,
            selectionBoxColor: '#007bff',
            selectionBoxWidth: 2,
            handleSize: 8,
            multiSelectKey: 'ctrl', // ctrl, shift, alt
            allowGroupSelection: true,
            snapToGrid: false,
            gridSize: 20
        };

        // État interne
        this.isSelecting = false;
        this.selectionStart = null;
        this.selectionEnd = null;
    }

    /**
     * Sélectionne un élément
     */
    selectElement(elementId, options = {}) {
        const {
            addToSelection = false,
            toggle = false
        } = options;

        const element = this.canvasInstance.elements.get(elementId);
        if (!element) return false;

        // Gestion de la sélection multiple
        if (this.selectionMode === 'multiple' || addToSelection) {
            if (toggle && this.selectedElements.has(elementId)) {
                this.deselectElement(elementId);
                return true;
            } else if (!this.selectedElements.has(elementId)) {
                this.selectedElements.add(elementId);
            }
        } else {
            // Sélection simple : désélectionner tout d'abord
            this.clearSelection();
            this.selectedElements.add(elementId);
        }

        this.updateSelectionBounds();
        this.canvasInstance.render();

        // Émettre un événement de sélection
        this.emitSelectionChange();

        return true;
    }

    /**
     * Désélectionne un élément spécifique
     */
    deselectElement(elementId) {
        if (this.selectedElements.delete(elementId)) {
            this.updateSelectionBounds();
            this.canvasInstance.render();
            this.emitSelectionChange();
            return true;
        }
        return false;
    }

    /**
     * Efface toute la sélection
     */
    clearSelection() {
        if (this.selectedElements.size > 0) {
            this.selectedElements.clear();
            this.selectionBounds = null;
            this.canvasInstance.render();
            this.emitSelectionChange();
        }
    }

    /**
     * Sélectionne tous les éléments
     */
    selectAll() {
        this.selectedElements.clear();
        for (const [elementId] of this.canvasInstance.elements) {
            this.selectedElements.add(elementId);
        }
        this.updateSelectionBounds();
        this.canvasInstance.render();
        this.emitSelectionChange();
    }

    /**
     * Inverse la sélection
     */
    invertSelection() {
        const allElementIds = new Set(this.canvasInstance.elements.keys());
        const newSelection = new Set();

        for (const elementId of allElementIds) {
            if (!this.selectedElements.has(elementId)) {
                newSelection.add(elementId);
            }
        }

        this.selectedElements = newSelection;
        this.updateSelectionBounds();
        this.canvasInstance.render();
        this.emitSelectionChange();
    }

    /**
     * Sélectionne les éléments dans une zone rectangulaire
     */
    selectInRect(rect, mode = 'replace') {
        const elementsInRect = this.getElementsInRect(rect);

        switch (mode) {
            case 'replace':
                this.selectedElements.clear();
                elementsInRect.forEach(id => this.selectedElements.add(id));
                break;
            case 'add':
                elementsInRect.forEach(id => this.selectedElements.add(id));
                break;
            case 'subtract':
                elementsInRect.forEach(id => this.selectedElements.delete(id));
                break;
            case 'intersect':
                const newSelection = new Set();
                elementsInRect.forEach(id => {
                    if (this.selectedElements.has(id)) {
                        newSelection.add(id);
                    }
                });
                this.selectedElements = newSelection;
                break;
        }

        this.updateSelectionBounds();
        this.canvasInstance.render();
        this.emitSelectionChange();
    }

    /**
     * Obtient les éléments dans un rectangle
     */
    getElementsInRect(rect) {
        const elementsInRect = [];

        for (const [elementId, element] of this.canvasInstance.elements) {
            const props = element.properties;
            const elementRect = {
                x: props.x,
                y: props.y,
                width: props.width,
                height: props.height
            };

            if (this.rectsIntersect(rect, elementRect)) {
                elementsInRect.push(elementId);
            }
        }

        return elementsInRect;
    }

    /**
     * Vérifie si deux rectangles s'intersectent
     */
    rectsIntersect(rect1, rect2) {
        return !(rect1.x + rect1.width < rect2.x ||
                 rect2.x + rect2.width < rect1.x ||
                 rect1.y + rect1.height < rect2.y ||
                 rect2.y + rect2.height < rect1.y);
    }

    /**
     * Met à jour les limites de la sélection
     */
    updateSelectionBounds() {
        if (this.selectedElements.size === 0) {
            this.selectionBounds = null;
            return;
        }

        let minX = Infinity, minY = Infinity;
        let maxX = -Infinity, maxY = -Infinity;

        for (const elementId of this.selectedElements) {
            const element = this.canvasInstance.elements.get(elementId);
            if (!element) continue;

            const props = element.properties;
            minX = Math.min(minX, props.x);
            minY = Math.min(minY, props.y);
            maxX = Math.max(maxX, props.x + props.width);
            maxY = Math.max(maxY, props.y + props.height);
        }

        this.selectionBounds = {
            x: minX,
            y: minY,
            width: maxX - minX,
            height: maxY - minY
        };
    }

    /**
     * Démarre une sélection par glisser
     */
    startDragSelection(point) {
        this.isSelecting = true;
        this.selectionStart = { ...point };
        this.selectionEnd = { ...point };
        this.selectionBox = {
            x: point.x,
            y: point.y,
            width: 0,
            height: 0
        };
    }

    /**
     * Met à jour la sélection par glisser
     */
    updateDragSelection(point) {
        if (!this.isSelecting) return;

        this.selectionEnd = { ...point };

        // Calculer le rectangle de sélection
        const x = Math.min(this.selectionStart.x, this.selectionEnd.x);
        const y = Math.min(this.selectionStart.y, this.selectionEnd.y);
        const width = Math.abs(this.selectionEnd.x - this.selectionStart.x);
        const height = Math.abs(this.selectionEnd.y - this.selectionStart.y);

        this.selectionBox = { x, y, width, height };
        this.canvasInstance.render();
    }

    /**
     * Termine la sélection par glisser
     */
    endDragSelection() {
        if (!this.isSelecting) return;

        this.isSelecting = false;

        // Appliquer la sélection si le rectangle est suffisamment grand
        if (this.selectionBox && this.selectionBox.width > 5 && this.selectionBox.height > 5) {
            const mode = this.getSelectionModeFromKeys();
            this.selectInRect(this.selectionBox, mode);
        }

        this.selectionBox = null;
        this.selectionStart = null;
        this.selectionEnd = null;
        this.canvasInstance.render();
    }

    /**
     * Obtient le mode de sélection basé sur les touches pressées
     */
    getSelectionModeFromKeys() {
        const modifiers = (this.canvasInstance.eventManager &&
                          this.canvasInstance.eventManager.keyboardState &&
                          this.canvasInstance.eventManager.keyboardState.modifiers) || {};

        switch (this.config.multiSelectKey) {
            case 'ctrl':
                return modifiers.ctrl ? 'add' : 'replace';
            case 'shift':
                return modifiers.shift ? 'add' : 'replace';
            case 'alt':
                return modifiers.alt ? 'subtract' : 'replace';
            default:
                return 'replace';
        }
    }

    /**
     * Rend la sélection
     */
    render(ctx) {
        // Rendre la boîte de sélection en cours
        if (this.isSelecting && this.selectionBox) {
            this.renderSelectionBox(ctx, this.selectionBox, true);
        }

        // Rendre les éléments sélectionnés
        if (this.selectedElements.size > 0) {
            this.renderSelectedElements(ctx);
        }

        // Rendre les limites de sélection groupée
        if (this.selectionBounds && this.selectedElements.size > 1) {
            this.renderSelectionBounds(ctx);
        }
    }

    /**
     * Rend la boîte de sélection
     */
    renderSelectionBox(ctx, box, isPreview = false) {
        ctx.save();

        ctx.strokeStyle = this.config.selectionBoxColor;
        ctx.lineWidth = this.config.selectionBoxWidth;
        ctx.setLineDash(isPreview ? [5, 5] : []);
        ctx.strokeRect(box.x, box.y, box.width, box.height);

        // Remplir avec transparence pour la prévisualisation
        if (isPreview) {
            ctx.fillStyle = 'rgba(0, 123, 255, 0.1)';
            ctx.fillRect(box.x, box.y, box.width, box.height);
        }

        ctx.restore();
    }

    /**
     * Rend les éléments sélectionnés
     */
    renderSelectedElements(ctx) {
        for (const elementId of this.selectedElements) {
            const element = this.canvasInstance.elements.get(elementId);
            if (!element) continue;

            this.renderSelectionOverlay(ctx, element);
        }
    }

    /**
     * Rend l'overlay de sélection pour un élément
     */
    renderSelectionOverlay(ctx, element) {
        const props = element.properties;

        ctx.save();

        // Rectangle de sélection
        ctx.strokeStyle = this.config.selectionBoxColor;
        ctx.lineWidth = this.config.selectionBoxWidth;
        ctx.setLineDash([3, 3]);
        ctx.strokeRect(props.x, props.y, props.width, props.height);

        // Poignées de redimensionnement
        if (this.config.showHandles) {
            this.renderResizeHandles(ctx, props);
        }

        ctx.restore();
    }

    /**
     * Rend les poignées de redimensionnement
     */
    renderResizeHandles(ctx, props) {
        const handleSize = this.config.handleSize;
        const handles = [
            { x: props.x, y: props.y }, // Haut-gauche
            { x: props.x + props.width, y: props.y }, // Haut-droite
            { x: props.x + props.width, y: props.y + props.height }, // Bas-droite
            { x: props.x, y: props.y + props.height } // Bas-gauche
        ];

        ctx.fillStyle = '#ffffff';
        ctx.strokeStyle = this.config.selectionBoxColor;
        ctx.lineWidth = 1;

        handles.forEach(handle => {
            ctx.fillRect(
                handle.x - handleSize / 2,
                handle.y - handleSize / 2,
                handleSize,
                handleSize
            );
            ctx.strokeRect(
                handle.x - handleSize / 2,
                handle.y - handleSize / 2,
                handleSize,
                handleSize
            );
        });
    }

    /**
     * Rend les limites de sélection groupée
     */
    renderSelectionBounds(ctx) {
        if (!this.selectionBounds) return;

        ctx.save();

        ctx.strokeStyle = this.config.selectionBoxColor;
        ctx.lineWidth = this.config.selectionBoxWidth * 1.5;
        ctx.setLineDash([8, 4]);
        ctx.strokeRect(
            this.selectionBounds.x,
            this.selectionBounds.y,
            this.selectionBounds.width,
            this.selectionBounds.height
        );

        ctx.restore();
    }

    /**
     * Vérifie si un point est sur une poignée de redimensionnement
     */
    getHandleAtPoint(point, element) {
        if (!this.config.showHandles) return null;

        const props = element.properties;
        const handleSize = this.config.handleSize;
        const handles = [
            { name: 'nw', x: props.x, y: props.y },
            { name: 'ne', x: props.x + props.width, y: props.y },
            { name: 'se', x: props.x + props.width, y: props.y + props.height },
            { name: 'sw', x: props.x, y: props.y + props.height }
        ];

        for (const handle of handles) {
            if (point.x >= handle.x - handleSize / 2 &&
                point.x <= handle.x + handleSize / 2 &&
                point.y >= handle.y - handleSize / 2 &&
                point.y <= handle.y + handleSize / 2) {
                return handle.name;
            }
        }

        return null;
    }

    /**
     * Obtient l'élément sélectionné principal (le dernier sélectionné)
     */
    getPrimarySelectedElement() {
        if (this.selectedElements.size === 0) return null;

        // Retourner le dernier élément de l'ensemble
        const elements = Array.from(this.selectedElements);
        return this.canvasInstance.elements.get(elements[elements.length - 1]);
    }

    /**
     * Vérifie si un élément est sélectionné
     */
    isElementSelected(elementId) {
        return this.selectedElements.has(elementId);
    }

    /**
     * Obtient tous les éléments sélectionnés
     */
    getSelectedElements() {
        const elements = [];
        for (const elementId of this.selectedElements) {
            const element = this.canvasInstance.elements.get(elementId);
            if (element) {
                elements.push(element);
            }
        }
        return elements;
    }

    /**
     * Obtient les IDs des éléments sélectionnés
     */
    getSelectedElementIds() {
        return Array.from(this.selectedElements);
    }

    /**
     * Compte les éléments sélectionnés
     */
    getSelectionCount() {
        return this.selectedElements.size;
    }

    /**
     * Déplace tous les éléments sélectionnés
     */
    moveSelectedElements(deltaX, deltaY, snapToGrid = false) {
        if (this.selectedElements.size === 0) return;

        for (const elementId of this.selectedElements) {
            const element = this.canvasInstance.elements.get(elementId);
            if (!element) continue;

            let newX = element.properties.x + deltaX;
            let newY = element.properties.y + deltaY;

            // Accrochage à la grille
            if (snapToGrid || this.config.snapToGrid) {
                newX = Math.round(newX / this.config.gridSize) * this.config.gridSize;
                newY = Math.round(newY / this.config.gridSize) * this.config.gridSize;
            }

            this.canvasInstance.updateElementProperty(elementId, 'x', newX);
            this.canvasInstance.updateElementProperty(elementId, 'y', newY);
        }

        this.updateSelectionBounds();
    }

    /**
     * Redimensionne les éléments sélectionnés
     */
    resizeSelectedElements(handle, deltaX, deltaY) {
        if (this.selectedElements.size !== 1) return; // Redimensionnement multiple pas supporté

        const elementId = this.getSelectedElementIds()[0];
        const element = this.canvasInstance.elements.get(elementId);
        if (!element) return;

        const props = element.properties;
        let newX = props.x;
        let newY = props.y;
        let newWidth = props.width;
        let newHeight = props.height;

        switch (handle) {
            case 'nw':
                newX = props.x + deltaX;
                newY = props.y + deltaY;
                newWidth = props.width - deltaX;
                newHeight = props.height - deltaY;
                break;
            case 'ne':
                newY = props.y + deltaY;
                newWidth = props.width + deltaX;
                newHeight = props.height - deltaY;
                break;
            case 'se':
                newWidth = props.width + deltaX;
                newHeight = props.height + deltaY;
                break;
            case 'sw':
                newX = props.x + deltaX;
                newWidth = props.width - deltaX;
                newHeight = props.height + deltaY;
                break;
        }

        // Validation des dimensions minimales
        if (newWidth >= 10 && newHeight >= 10) {
            this.canvasInstance.updateElementProperty(elementId, 'x', newX);
            this.canvasInstance.updateElementProperty(elementId, 'y', newY);
            this.canvasInstance.updateElementProperty(elementId, 'width', newWidth);
            this.canvasInstance.updateElementProperty(elementId, 'height', newHeight);
            this.updateSelectionBounds();
        }
    }

    /**
     * Supprime tous les éléments sélectionnés
     */
    deleteSelectedElements() {
        const elementIds = this.getSelectedElementIds();
        elementIds.forEach(elementId => {
            this.canvasInstance.deleteElement(elementId);
        });
        this.clearSelection();
    }

    /**
     * Duplique les éléments sélectionnés
     */
    duplicateSelectedElements(offsetX = 20, offsetY = 20) {
        const newSelection = new Set();

        for (const elementId of this.selectedElements) {
            const element = this.canvasInstance.elements.get(elementId);
            if (!element) continue;

            const newProperties = {
                ...element.properties,
                x: element.properties.x + offsetX,
                y: element.properties.y + offsetY
            };

            const newElementId = this.canvasInstance.addElement(element.type, newProperties);
            newSelection.add(newElementId);
        }

        // Sélectionner les nouveaux éléments
        this.selectedElements = newSelection;
        this.updateSelectionBounds();
        this.canvasInstance.render();
        this.emitSelectionChange();
    }

    /**
     * Configure le gestionnaire de sélection
     */
    configure(options) {
        this.config = { ...this.config, ...options };
    }

    /**
     * Émet un événement de changement de sélection
     */
    emitSelectionChange() {
        const event = new CustomEvent('selection-change', {
            detail: {
                selectedElements: this.getSelectedElementIds(),
                selectionBounds: this.selectionBounds,
                selectionCount: this.getSelectionCount()
            }
        });

        this.canvasInstance.canvas.dispatchEvent(event);
    }

    /**
     * Obtient l'état actuel de la sélection
     */
    getSelectionState() {
        return {
            selectedElements: this.getSelectedElementIds(),
            selectionBounds: this.selectionBounds,
            selectionCount: this.getSelectionCount(),
            isSelecting: this.isSelecting,
            selectionBox: this.selectionBox
        };
    }

    /**
     * Nettoie les ressources
     */
    dispose() {
        this.clearSelection();
        this.isSelecting = false;
        this.selectionStart = null;
        this.selectionEnd = null;
        this.selectionBox = null;
    }
}

export default PDFCanvasSelectionManager;