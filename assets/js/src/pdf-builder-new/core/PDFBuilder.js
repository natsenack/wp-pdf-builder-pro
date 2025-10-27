/**
 * PDF Builder - Noyau principal
 * Architecture modulaire et stable pour l'édition PDF
 */

export class PDFBuilder {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.options = {
            width: options.width || 800,
            height: options.height || 600,
            backgroundColor: options.backgroundColor || '#ffffff',
            gridSize: options.gridSize || 20,
            showGrid: options.showGrid !== false,
            zoom: options.zoom || 1,
            snapToGrid: options.snapToGrid !== false,
            ...options
        };

        // État du builder
        this.isInitialized = false;
        this.canvas = null;
        this.ctx = null;

        // Managers
        this.canvasEngine = null;
        this.elementManager = null;
        this.templateManager = null;
        this.uiManager = null;

        // État d'interaction
        this.mode = 'select';
        this.selectedElements = new Set();
        this.dragState = null;

        // Événements
        this.eventListeners = new Map();
    }

    /**
     * Initialisation du builder
     */
    async init() {
        try {
            console.log('🚀 [PDFBuilder] Initialisation...');

            // 1. Créer le conteneur canvas
            this._createCanvas();

            // 2. Initialiser les managers
            await this._initManagers();

            // 3. Configurer les événements
            this._setupEventListeners();

            this.isInitialized = true;
            console.log('✅ [PDFBuilder] Initialisation terminée');

            // Premier rendu
            this.render();

        } catch (error) {
            console.error('❌ [PDFBuilder] Erreur d\'initialisation:', error);
            throw error;
        }
    }

    /**
     * Création du canvas
     * @private
     */
    _createCanvas() {
        const container = document.getElementById(this.containerId);
        if (!container) {
            throw new Error(`Container ${this.containerId} not found`);
        }

        this.canvas = document.createElement('canvas');
        this.canvas.id = 'pdf-builder-canvas';
        this.canvas.width = this.options.width;
        this.canvas.height = this.options.height;
        this.canvas.style.border = '1px solid #ccc';
        this.canvas.style.backgroundColor = this.options.backgroundColor;

        container.appendChild(this.canvas);
        this.ctx = this.canvas.getContext('2d');
    }

    /**
     * Initialisation des managers
     * @private
     */
    async _initManagers() {
        // Import dynamique pour éviter les dépendances circulaires
        const { CanvasEngine } = await import('./core/CanvasEngine.js');
        const { ElementManager } = await import('./core/ElementManager.js');
        const { TemplateManager } = await import('./core/TemplateManager.js');

        this.canvasEngine = new CanvasEngine(this.canvas, this.ctx);
        this.elementManager = new ElementManager(this);
        this.templateManager = new TemplateManager(this);

        // UI si nécessaire
        if (this.options.enableUI) {
            const { UIManager } = await import('./ui/UIManager.js');
            this.uiManager = new UIManager(this);
        }
    }

    /**
     * Configuration des événements
     * @private
     */
    _setupEventListeners() {
        this.canvas.addEventListener('mousedown', this._handleMouseDown.bind(this));
        this.canvas.addEventListener('mousemove', this._handleMouseMove.bind(this));
        this.canvas.addEventListener('mouseup', this._handleMouseUp.bind(this));
        this.canvas.addEventListener('wheel', this._handleWheel.bind(this));

        // Événements clavier
        document.addEventListener('keydown', this._handleKeyDown.bind(this));
    }

    /**
     * Gestionnaire d'événements souris
     */
    _handleMouseDown(event) {
        const point = this._getMousePosition(event);

        if (this.mode === 'select') {
            this._handleSelect(point);
        } else if (this.mode === 'draw') {
            this._handleDrawStart(point);
        }

        this.emit('mousedown', { point, event });
    }

    _handleMouseMove(event) {
        const point = this._getMousePosition(event);

        if (this.dragState) {
            this._handleDrag(point);
        }

        this.emit('mousemove', { point, event });
    }

    _handleMouseUp(event) {
        if (this.dragState) {
            this._handleDragEnd();
        }

        this.emit('mouseup', { event });
    }

    _handleWheel(event) {
        event.preventDefault();
        const delta = event.deltaY > 0 ? 0.9 : 1.1;
        this.setZoom(this.options.zoom * delta);
        this.emit('zoom', { zoom: this.options.zoom });
    }

    _handleKeyDown(event) {
        if (event.key === 'Delete') {
            this.deleteSelectedElements();
        }
        this.emit('keydown', { event });
    }

    /**
     * Gestion de la sélection
     */
    _handleSelect(point) {
        const element = this.elementManager.getElementAtPoint(point);
        if (element) {
            this.selectElement(element.id, !event.ctrlKey);
            this.dragState = {
                type: 'move',
                startPoint: point,
                selectedElements: Array.from(this.selectedElements)
            };
        } else {
            this.clearSelection();
        }
    }

    /**
     * Gestion du drag
     */
    _handleDrag(point) {
        if (this.dragState.type === 'move') {
            const deltaX = point.x - this.dragState.startPoint.x;
            const deltaY = point.y - this.dragState.startPoint.y;

            this.dragState.selectedElements.forEach(elementId => {
                const element = this.elementManager.getElement(elementId);
                if (element) {
                    element.x += deltaX;
                    element.y += deltaY;
                }
            });

            this.dragState.startPoint = point;
            this.render();
        }
    }

    _handleDragEnd() {
        this.dragState = null;
        this.emit('dragend');
    }

    /**
     * API publique
     */
    addElement(type, properties = {}) {
        return this.elementManager.addElement(type, properties);
    }

    selectElement(elementId, clearSelection = true) {
        if (clearSelection) {
            this.selectedElements.clear();
        }
        this.selectedElements.add(elementId);
        this.render();
        this.emit('selectionchange', { selectedElements: Array.from(this.selectedElements) });
    }

    clearSelection() {
        this.selectedElements.clear();
        this.render();
        this.emit('selectionchange', { selectedElements: [] });
    }

    deleteSelectedElements() {
        this.selectedElements.forEach(elementId => {
            this.elementManager.removeElement(elementId);
        });
        this.clearSelection();
        this.emit('elementsdeleted');
    }

    setZoom(zoom) {
        this.options.zoom = Math.max(0.1, Math.min(5, zoom));
        this.canvas.style.transform = `scale(${this.options.zoom})`;
        this.render();
    }

    setMode(mode) {
        this.mode = mode;
        this.emit('modechange', { mode });
    }

    /**
     * Rendu
     */
    render() {
        if (!this.canvasEngine) return;

        this.canvasEngine.clear();

        // Rendu des éléments
        this.elementManager.getAllElements().forEach(element => {
            this.canvasEngine.renderElement(element, {
                selected: this.selectedElements.has(element.id)
            });
        });

        // Rendu de la grille si activée
        if (this.options.showGrid) {
            this.canvasEngine.renderGrid(this.options.gridSize);
        }

        this.emit('render');
    }

    /**
     * Utilitaires
     */
    _getMousePosition(event) {
        const rect = this.canvas.getBoundingClientRect();
        return {
            x: (event.clientX - rect.left) / this.options.zoom,
            y: (event.clientY - rect.top) / this.options.zoom
        };
    }

    /**
     * Système d'événements
     */
    on(eventType, callback) {
        if (!this.eventListeners.has(eventType)) {
            this.eventListeners.set(eventType, []);
        }
        this.eventListeners.get(eventType).push(callback);
    }

    off(eventType, callback) {
        if (this.eventListeners.has(eventType)) {
            const listeners = this.eventListeners.get(eventType);
            const index = listeners.indexOf(callback);
            if (index > -1) {
                listeners.splice(index, 1);
            }
        }
    }

    emit(eventType, data) {
        if (this.eventListeners.has(eventType)) {
            this.eventListeners.get(eventType).forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error('Event listener error:', error);
                }
            });
        }
    }

    /**
     * Destruction
     */
    destroy() {
        if (this.canvas) {
            this.canvas.remove();
        }
        this.eventListeners.clear();
        this.emit('destroy');
    }
}