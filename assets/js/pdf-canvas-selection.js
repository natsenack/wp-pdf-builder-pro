/**
 * PDF Canvas Selection Manager - Gestionnaire de sélection d'éléments
 * Système de sélection multiple avec handles de transformation
 */

function PDFCanvasSelectionManager(canvasInstance) {
    this.canvasInstance = canvasInstance;
    this.selectedElements = new Set();
    this.selectionBounds = null;
    this.isSelecting = false;
    this.selectionStartPoint = null;
    this.selectionRect = null;

    // Configuration
    this.selectionColor = 'rgba(0, 123, 255, 0.2)';
    this.selectionBorderColor = '#007bff';
    this.selectionBorderWidth = 2;
    this.minSelectionSize = 5;
}

/**
 * Sélectionne un élément à une position donnée
 */
PDFCanvasSelectionManager.prototype.selectAtPoint = function(point, multiSelect) {
    var element = this.canvasInstance.getElementAtPoint(point);

    if (!multiSelect) {
        this.clearSelection();
    }

    if (element) {
        if (multiSelect && this.selectedElements.has(element.id)) {
            this.selectedElements.delete(element.id);
        } else {
            this.selectedElements.add(element.id);
        }
        this.updateSelectionBounds();
        return true;
    }

    return false;
};

/**
 * Démarre une sélection par rectangle
 */
PDFCanvasSelectionManager.prototype.startSelection = function(point) {
    this.isSelecting = true;
    this.selectionStartPoint = { x: point.x, y: point.y };
    this.selectionRect = {
        x: point.x,
        y: point.y,
        width: 0,
        height: 0
    };
};

/**
 * Met à jour la sélection par rectangle
 */
PDFCanvasSelectionManager.prototype.updateSelection = function(point) {
    if (!this.isSelecting || !this.selectionStartPoint) return;

    this.selectionRect.x = Math.min(this.selectionStartPoint.x, point.x);
    this.selectionRect.y = Math.min(this.selectionStartPoint.y, point.y);
    this.selectionRect.width = Math.abs(point.x - this.selectionStartPoint.x);
    this.selectionRect.height = Math.abs(point.y - this.selectionStartPoint.y);
};

/**
 * Termine la sélection par rectangle
 */
PDFCanvasSelectionManager.prototype.endSelection = function(multiSelect) {
    if (!this.isSelecting || !this.selectionRect) return;

    if (!multiSelect) {
        this.clearSelection();
    }

    // Vérifier si la sélection est assez grande
    if (this.selectionRect.width >= this.minSelectionSize &&
        this.selectionRect.height >= this.minSelectionSize) {

        var elementsInRect = this.getElementsInRect(this.selectionRect);
        elementsInRect.forEach(function(elementId) {
            this.selectedElements.add(elementId);
        }.bind(this));
    }

    this.updateSelectionBounds();
    this.isSelecting = false;
    this.selectionStartPoint = null;
    this.selectionRect = null;
};

/**
 * Obtient les éléments dans un rectangle
 */
PDFCanvasSelectionManager.prototype.getElementsInRect = function(rect) {
    var elementsInRect = [];

    this.canvasInstance.elements.forEach(function(element, elementId) {
        if (this.elementIntersectsRect(element, rect)) {
            elementsInRect.push(elementId);
        }
    }.bind(this));

    return elementsInRect;
};

/**
 * Vérifie si un élément intersecte un rectangle
 */
PDFCanvasSelectionManager.prototype.elementIntersectsRect = function(element, rect) {
    var props = element.properties;
    return !(props.x + props.width < rect.x ||
             rect.x + rect.width < props.x ||
             props.y + props.height < rect.y ||
             rect.y + rect.height < props.y);
};

/**
 * Met à jour les bounds de la sélection
 */
PDFCanvasSelectionManager.prototype.updateSelectionBounds = function() {
    if (this.selectedElements.size === 0) {
        this.selectionBounds = null;
        return;
    }

    var minX = Infinity, minY = Infinity;
    var maxX = -Infinity, maxY = -Infinity;

    this.selectedElements.forEach(function(elementId) {
        var element = this.canvasInstance.elements.get(elementId);
        if (element) {
            var props = element.properties;
            minX = Math.min(minX, props.x);
            minY = Math.min(minY, props.y);
            maxX = Math.max(maxX, props.x + props.width);
            maxY = Math.max(maxY, props.y + props.height);
        }
    }.bind(this));

    this.selectionBounds = {
        x: minX,
        y: minY,
        width: maxX - minX,
        height: maxY - minY
    };
};

/**
 * Efface la sélection
 */
PDFCanvasSelectionManager.prototype.clearSelection = function() {
    this.selectedElements.clear();
    this.selectionBounds = null;
};

/**
 * Obtient le nombre d'éléments sélectionnés
 */
PDFCanvasSelectionManager.prototype.getSelectionCount = function() {
    return this.selectedElements.size;
};

/**
 * Obtient les IDs des éléments sélectionnés
 */
PDFCanvasSelectionManager.prototype.getSelectedElementIds = function() {
    return Array.from(this.selectedElements);
};

/**
 * Obtient les éléments sélectionnés
 */
PDFCanvasSelectionManager.prototype.getSelectedElements = function() {
    var selected = [];
    this.selectedElements.forEach(function(elementId) {
        var element = this.canvasInstance.elements.get(elementId);
        if (element) {
            selected.push(element);
        }
    }.bind(this));
    return selected;
};

/**
 * Vérifie si un élément est sélectionné
 */
PDFCanvasSelectionManager.prototype.isElementSelected = function(elementId) {
    return this.selectedElements.has(elementId);
};

/**
 * Sélectionne tous les éléments
 */
PDFCanvasSelectionManager.prototype.selectAll = function() {
    this.canvasInstance.elements.forEach(function(element, elementId) {
        this.selectedElements.add(elementId);
    }.bind(this));
    this.updateSelectionBounds();
};

/**
 * Rend la sélection
 */
PDFCanvasSelectionManager.prototype.render = function(ctx) {
    // Rendre le rectangle de sélection en cours
    if (this.isSelecting && this.selectionRect) {
        ctx.save();
        ctx.fillStyle = this.selectionColor;
        ctx.strokeStyle = this.selectionBorderColor;
        ctx.lineWidth = this.selectionBorderWidth;

        ctx.fillRect(
            this.selectionRect.x,
            this.selectionRect.y,
            this.selectionRect.width,
            this.selectionRect.height
        );
        ctx.strokeRect(
            this.selectionRect.x,
            this.selectionRect.y,
            this.selectionRect.width,
            this.selectionRect.height
        );
        ctx.restore();
    }

    // Rendre les bounds de sélection
    if (this.selectionBounds && this.selectedElements.size > 0) {
        ctx.save();
        ctx.strokeStyle = this.selectionBorderColor;
        ctx.lineWidth = this.selectionBorderWidth;
        ctx.setLineDash([5, 5]);

        ctx.strokeRect(
            this.selectionBounds.x,
            this.selectionBounds.y,
            this.selectionBounds.width,
            this.selectionBounds.height
        );
        ctx.restore();
    }
};

/**
 * Nettoie les ressources
 */
PDFCanvasSelectionManager.prototype.dispose = function() {
    this.clearSelection();
    this.isSelecting = false;
    this.selectionStartPoint = null;
    this.selectionRect = null;
};

// Export pour compatibilité
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PDFCanvasSelectionManager;
}