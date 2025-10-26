/**
 * PDF Canvas History Manager - Gestionnaire d'historique pour undo/redo
 * Système complet de gestion des états pour annuler/rétablir les actions
 */

function PDFCanvasHistoryManager(canvasInstance) {
    this.canvasInstance = canvasInstance;
    this.history = [];
    this.historyIndex = -1;
    this.maxHistorySize = 50;

    // État initial
    this.saveState();
}

/**
 * Sauvegarde l'état actuel dans l'historique
 */
PDFCanvasHistoryManager.prototype.saveState = function() {
    const state = {
        elements: new Map(),
        selectedElement: this.canvasInstance.selectedElement ? this.canvasInstance.selectedElement.id : null,
        timestamp: Date.now()
    };

    // Copier tous les éléments
    this.canvasInstance.elements.forEach((element, elementId) => {
        state.elements.set(elementId, JSON.parse(JSON.stringify(element)));
    });

    // Supprimer les états plus récents si on n'est pas à la fin de l'historique
    if (this.historyIndex < this.history.length - 1) {
        this.history = this.history.slice(0, this.historyIndex + 1);
    }

    // Ajouter le nouvel état
    this.history.push(state);
    this.historyIndex++;

    // Limiter la taille de l'historique
    if (this.history.length > this.maxHistorySize) {
        this.history.shift();
        this.historyIndex--;
    }
};

/**
 * Annule la dernière action
 */
PDFCanvasHistoryManager.prototype.undo = function() {
    if (this.historyIndex > 0) {
        this.historyIndex--;
        this.restoreState(this.history[this.historyIndex]);
        return true;
    }
    return false;
};

/**
 * Rétablit la dernière action annulée
 */
PDFCanvasHistoryManager.prototype.redo = function() {
    if (this.historyIndex < this.history.length - 1) {
        this.historyIndex++;
        this.restoreState(this.history[this.historyIndex]);
        return true;
    }
    return false;
};

/**
 * Restaure un état depuis l'historique
 */
PDFCanvasHistoryManager.prototype.restoreState = function(state) {
    // Restaurer les éléments
    this.canvasInstance.elements.clear();
    state.elements.forEach((element, elementId) => {
        this.canvasInstance.elements.set(elementId, JSON.parse(JSON.stringify(element)));
    });

    // Restaurer la sélection
    if (state.selectedElement) {
        this.canvasInstance.selectedElement = this.canvasInstance.elements.get(state.selectedElement);
        // Mettre à jour le gestionnaire de sélection
        if (this.canvasInstance.selectionManager) {
            this.canvasInstance.selectionManager.clearSelection();
            this.canvasInstance.selectionManager.selectAtPoint(
                {
                    x: this.canvasInstance.selectedElement.properties.x + this.canvasInstance.selectedElement.properties.width / 2,
                    y: this.canvasInstance.selectedElement.properties.y + this.canvasInstance.selectedElement.properties.height / 2
                },
                false
            );
        }
    } else {
        this.canvasInstance.selectedElement = null;
        if (this.canvasInstance.selectionManager) {
            this.canvasInstance.selectionManager.clearSelection();
        }
    }

    // Redessiner le canvas
    this.canvasInstance.render();

    // Émettre un événement
    this.canvasInstance.emit('history-restored', {
        action: 'restore',
        timestamp: state.timestamp
    });
};

/**
 * Vérifie si undo est disponible
 */
PDFCanvasHistoryManager.prototype.canUndo = function() {
    return this.historyIndex > 0;
};

/**
 * Vérifie si redo est disponible
 */
PDFCanvasHistoryManager.prototype.canRedo = function() {
    return this.historyIndex < this.history.length - 1;
};

/**
 * Obtient les informations sur l'historique
 */
PDFCanvasHistoryManager.prototype.getHistoryInfo = function() {
    return {
        size: this.history.length,
        currentIndex: this.historyIndex,
        canUndo: this.canUndo(),
        canRedo: this.canRedo(),
        maxSize: this.maxHistorySize
    };
};

/**
 * Efface l'historique
 */
PDFCanvasHistoryManager.prototype.clearHistory = function() {
    this.history = [];
    this.historyIndex = -1;
    this.saveState();
};

/**
 * Configure le gestionnaire
 */
PDFCanvasHistoryManager.prototype.configure = function(options) {
    if (options.maxHistorySize !== undefined) {
        this.maxHistorySize = Math.max(10, Math.min(200, options.maxHistorySize));
        // Réduire l'historique si nécessaire
        if (this.history.length > this.maxHistorySize) {
            const keepFrom = Math.max(0, this.history.length - this.maxHistorySize);
            this.history = this.history.slice(keepFrom);
            this.historyIndex = Math.max(0, this.historyIndex - keepFrom);
        }
    }
};

// Export pour compatibilité
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PDFCanvasHistoryManager;
}