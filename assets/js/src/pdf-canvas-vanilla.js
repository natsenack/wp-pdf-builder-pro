/**
 * PDF Canvas Vanilla - Classe principale pour le système Vanilla JS
 * Remplace les composants React avec une implémentation Canvas HTML5
 * Intègre les utilitaires migrés pour la gestion des éléments
 */

import { ELEMENT_PROPERTY_RESTRICTIONS, ELEMENT_TYPE_MAPPING, isPropertyAllowed, getPropertyDefault, validateProperty, fixInvalidProperty } from './pdf-canvas-elements.js';
import { WooCommerceElementsManager, wooCommerceElementsManager } from './pdf-canvas-woocommerce.js';
import { ElementCustomizationService, elementCustomizationService } from './pdf-canvas-customization.js';
import { PDFCanvasRenderer } from './pdf-canvas-renderer.js';
import { PDFCanvasEventManager } from './pdf-canvas-events.js';
import { PDFCanvasRenderUtils } from './pdf-canvas-render-utils.js';
import { PDFCanvasSelectionManager } from './pdf-canvas-selection.js';
import { PDFCanvasPropertiesManager } from './pdf-canvas-properties.js';
import { PDFCanvasLayersManager } from './pdf-canvas-layers.js';
import { PDFCanvasExportManager } from './pdf-canvas-export.js';
import { PDFCanvasPerformanceOptimizer } from './pdf-canvas-optimizer.js';
import { PDFCanvasTransformationsManager } from './pdf-canvas-transformations.js';
import { PDFCanvasHistoryManager } from './pdf-canvas-history.js';
import { PDFCanvasDragDropManager } from './pdf-canvas-dragdrop.js';

export class PDFCanvasVanilla {
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
            constrainToCanvas: options.constrainToCanvas !== false,
            dragFeedback: options.dragFeedback !== false,
            ...options
        };

        // Gestion d'état pour optimisations
        this.dragState = null;
        this.isRendering = false;
        this.lastMouseMoveTime = 0;
        this.mouseMoveThrottleMs = 8; // ~120fps pour plus de fluidité
        this.lastRenderTime = 0;
        this.targetFPS = 60;
        this.frameInterval = 1000 / this.targetFPS;

        // État du canvas - DOIT être initialisé AVANT les gestionnaires
        this.canvas = null;
        this.ctx = null;
        this.elements = new Map();
        this.selectedElement = null;

        // Services intégrés
        this.eventListeners = new Map();

        // Services intégrés
        this.wooCommerceManager = wooCommerceElementsManager;
        this.customizationService = elementCustomizationService;

        // Gestionnaires spécialisés
        this.renderer = new PDFCanvasRenderer(this);
        this.eventManager = new PDFCanvasEventManager(this);
        this.selectionManager = new PDFCanvasSelectionManager(this);
        this.propertiesManager = new PDFCanvasPropertiesManager(this);
        this.layersManager = new PDFCanvasLayersManager(this);
        this.exportManager = new PDFCanvasExportManager(this);
        this.performanceOptimizer = new PDFCanvasPerformanceOptimizer(this);
        this.transformationsManager = new PDFCanvasTransformationsManager(this);
        this.historyManager = new PDFCanvasHistoryManager(this);
        this.dragDropManager = new PDFCanvasDragDropManager(this);

        // État d'interaction
        this.mode = 'select'; // select, draw, text, etc.
        this.tool = null;

        // Historique pour undo/redo
        this.history = [];
        this.historyIndex = -1;
    }

    /**
     * Écoute un événement personnalisé
     */
    on(eventType, callback) {
        if (!this.eventListeners.has(eventType)) {
            this.eventListeners.set(eventType, []);
        }
        this.eventListeners.get(eventType).push(callback);
    }

    /**
     * Supprime un écouteur d'événement
     */
    off(eventType, callback) {
        if (this.eventListeners.has(eventType)) {
            const listeners = this.eventListeners.get(eventType);
            const index = listeners.indexOf(callback);
            if (index > -1) {
                listeners.splice(index, 1);
            }
        }
    }

    /**
     * Émet un événement personnalisé
     */
    emit(eventType, data) {
        if (this.eventListeners.has(eventType)) {
            this.eventListeners.get(eventType).forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    // Error in event listener
                }
            });
        }
    }

    /**
     * Initialise le canvas et les gestionnaires d'événements
     */
    async init() {
        try {
            // Créer le canvas
            this.createCanvas();

            // Configurer le contexte
            this.setupContext();

            // Initialiser le renderer avec le canvas et contexte
            this.renderer.initialize(this.canvas, this.ctx);

            // Initialiser l'eventManager pour normaliser les événements
            this.eventManager.initialize(this.canvas);

            // Attacher les gestionnaires d'événements
            this.attachEventListeners();

            // Charger les données WooCommerce si nécessaire
            await this.loadInitialData();

            // Premier rendu
            this.render();

            // Sauvegarder l'état initial dans l'historique
            this.historyManager.saveState();

            this.isInitialized = true;

        } catch (error) {
            throw error;
        }
    }

    /**
     * Crée l'élément canvas dans le conteneur
     */
    createCanvas() {
        const container = document.getElementById(this.containerId);
        if (!container) {
            throw new Error(`Container with id "${this.containerId}" not found`);
        }

        // TOUJOURS utiliser le canvas existant défini en HTML
        // Ne JAMAIS vider le conteneur ni créer un nouveau canvas
        this.canvas = document.getElementById(this.options.canvasElementId || 'pdf-builder-canvas');
        
        if (!this.canvas || this.canvas.tagName !== 'CANVAS') {
            throw new Error(`Canvas element with id "${this.options.canvasElementId || 'pdf-builder-canvas'}" not found or is not a canvas element`);
        }

        // Obtenir le contexte 2D
        this.ctx = this.canvas.getContext('2d');
        if (!this.ctx) {
            throw new Error('Failed to get 2D context from canvas');
        }

        // Enregistrer les dimensions réelles du canvas
        this.canvasWidth = this.canvas.width;
        this.canvasHeight = this.canvas.height;

        // Canvas context initialized successfully
    }

    /**
     * Configure le contexte de rendu
     */
    setupContext() {
        // Configuration de base
        this.ctx.imageSmoothingEnabled = true;
        this.ctx.imageSmoothingQuality = 'high';

        // Configuration du texte
        this.ctx.textBaseline = 'top';
        this.ctx.font = '14px Arial, sans-serif';
    }

    /**
     * Attache les gestionnaires d'événements DOM
     */
    attachEventListeners() {
        // Les événements souris et clavier sont gérés par l'eventManager
        // Seuls les événements drag & drop sont gérés directement car non normalisés

        // Gestionnaires de drag & drop - DOIVENT ÊTRE AU NIVEAU DU DOCUMENT
        document.addEventListener('dragover', this.handleDragOver.bind(this));
        document.addEventListener('drop', this.handleDrop.bind(this));

        // Gestionnaire de redimensionnement
        window.addEventListener('resize', this.handleResize.bind(this));
    }

    /**
     * Charge les données initiales
     */
    async loadInitialData() {
        // Charger les données du template si un templateId est fourni
        if (this.options.templateId && this.options.templateId > 0) {
            await this.loadTemplateFromServer(this.options.templateId);
        } else if (this.options.templateData && this.options.templateData.elements) {
            this.loadTemplateData(this.options.templateData);
        }

        // Charger les données WooCommerce en mode test
        this.wooCommerceManager.setTestMode(true);
        await this.wooCommerceManager.loadWooCommerceData();
    }

    /**
     * Charge les données du template depuis le serveur
     */
    async loadTemplateFromServer(templateId) {
        try {
            const response = await fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'pdf_builder_load_template',
                    template_id: templateId,
                    nonce: window.pdfBuilderNonce || ''
                })
            });

            const data = await response.json();

            if (data.success && data.data.template) {
                this.loadTemplateData(data.data.template);
                // Template loaded successfully
            } else {
                // Failed to load template
            }
        } catch (error) {
            // Error loading template
        }
    }

    /**
     * Charge les données du template
     */
    loadTemplateData(templateData) {
        if (!templateData.elements) return;

        // Vider les éléments existants avant de charger
        this.elements.clear();

        templateData.elements.forEach(elementData => {
            this.addElement(elementData.type, elementData);
        });
    }

    /**
     * Gestionnaire d'événement mouse down
     */
    handleMouseDown(event) {
        // Vérifier que l'événement est valide
        if (!event || typeof event.preventDefault !== 'function') {
            return;
        }

        // Utiliser la position normalisée depuis l'EventManager
        const point = event.position || this.getMousePosition(event.originalEvent);

        if (!point || typeof point.x === 'undefined' || typeof point.y === 'undefined') {
            console.error('handleMouseDown: point is invalid', point);
            return;
        }

        switch (this.mode) {
            case 'select':
                this.handleSelectMode(point, event);
                break;
            case 'draw':
                this.handleDrawMode(point);
                break;
            case 'text':
                this.handleTextMode(point);
                break;
        }
    }

    /**
     * Gestionnaire d'événement mouse move
     */
    handleMouseMove(event) {
        // Vérifier que l'événement est valide
        if (!event || typeof event.preventDefault !== 'function') {
            return;
        }

        const now = Date.now();
        const point = event.position || this.getMousePosition(event.originalEvent);

        if (!point || typeof point.x === 'undefined' || typeof point.y === 'undefined') {
            console.error('handleMouseMove: point is invalid', point);
            return;
        }

        // Gérer les transformations en cours
        if (this.transformationsManager.isTransforming) {
            this.transformationsManager.updateTransform(point);
            return;
        }

        // Gérer la sélection par rectangle
        if (this.selectionManager.isSelecting) {
            this.selectionManager.updateSelection(point);
            this.render();
            return;
        }

        if (this.dragState) {
            // Pendant le drag, utiliser requestAnimationFrame pour la fluidité maximale
            this.handleDragImmediate(point);
        } else {
            this.handleHover(point);
        }
    }

    /**
     * Gestionnaire d'événement mouse up
     */
    handleMouseUp(event) {
        // Vérifier que l'événement est valide
        if (!event || typeof event.preventDefault !== 'function') {
            return;
        }
        
        const point = event.position || this.getMousePosition(event.originalEvent);

        if (!point || typeof point.x === 'undefined' || typeof point.y === 'undefined') {
            console.error('handleMouseUp: point is invalid', point);
            return;
        }

        // Terminer les transformations
        if (this.transformationsManager.isTransforming) {
            this.transformationsManager.endTransform();
            return;
        }

        // Terminer la sélection par rectangle
        if (this.selectionManager.isSelecting) {
            const multiSelect = event.ctrlKey || event.metaKey;
            this.selectionManager.endSelection(multiSelect);
            this.render();
            return;
        }

        if (this.dragState) {
            this.endDrag();
        }
    }

    /**
     * Gestionnaire de roulette de souris (zoom) - DÉSACTIVÉ
     */
    /*
    handleWheel(event) {
        if (!event || typeof event.preventDefault !== 'function') {
            // Invalid event in handleWheel
            return;
        }

        event.preventDefault();

        const delta = event.deltaY > 0 ? 0.9 : 1.1;
        this.setZoom(this.options.zoom * delta);
    }
    */

    /**
     * Gestionnaire d'événement drag over
     */
    handleDragOver(event) {
        try {
            // Vérifier que event est valide
            if (!event || typeof event.preventDefault !== 'function') {
                return;
            }
            // Déléguer au gestionnaire de drag & drop
            if (this.dragDropManager) {
                this.dragDropManager.handleDragOver(event);
            }
        } catch (error) {
            // Ignore errors
        }
    }

    /**
     * Gestionnaire d'événement drop
     */
    handleDrop(event) {
        try {
            // Vérifier que event est valide
            if (!event || typeof event.preventDefault !== 'function') {
                return;
            }
            // Déléguer au gestionnaire de drag & drop
            if (this.dragDropManager) {
                this.dragDropManager.handleDrop(event);
            }
        } catch (error) {
            // Ignore errors
        }
    }

    /**
     * Gestionnaire de touches clavier
     */
    handleKeyDown(event) {
        // Ignorer si un élément de formulaire est focus
        if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') {
            return;
        }

        switch (event.key) {
            case 'Delete':
            case 'Backspace':
                if (this.selectedElement) {
                    this.deleteElement(this.selectedElement.id);
                }
                break;
            case 'Escape':
                this.deselectElement();
                break;
            case 'a':
                if (event.ctrlKey || event.metaKey) {
                    event.preventDefault();
                    this.selectAll();
                }
                break;
            case 'd':
                if (event.ctrlKey || event.metaKey) {
                    event.preventDefault();
                    this.selectNone();
                }
                break;
            case 'i':
                if (event.ctrlKey || event.metaKey) {
                    event.preventDefault();
                    this.invertSelection();
                }
                break;
            case 'z':
                if (event.ctrlKey || event.metaKey) {
                    event.preventDefault();
                    this.undo();
                }
                break;
            case 'y':
                if (event.ctrlKey || event.metaKey) {
                    event.preventDefault();
                    this.redo();
                }
                break;
            case 'ArrowUp':
            case 'ArrowDown':
            case 'ArrowLeft':
            case 'ArrowRight':
                this.handleArrowKey(event);
                break;
        }
    }

    /**
     * Gestionnaire des touches fléchées pour déplacement précis
     */
    handleArrowKey(event) {
        const selectedElements = this.selectionManager.getSelectedElements();
        if (selectedElements.length === 0) return;

        event.preventDefault();

        // Calculer le déplacement
        const step = event.shiftKey ? 10 : 1; // Shift pour déplacement plus grand
        let deltaX = 0;
        let deltaY = 0;

        switch (event.key) {
            case 'ArrowUp':
                deltaY = -step;
                break;
            case 'ArrowDown':
                deltaY = step;
                break;
            case 'ArrowLeft':
                deltaX = -step;
                break;
            case 'ArrowRight':
                deltaX = step;
                break;
        }

        // Appliquer le snapping si activé
        if (this.options.snapToGrid) {
            const snapped = this.snapToGrid(deltaX, deltaY);
            deltaX = snapped.x;
            deltaY = snapped.y;
        }

        // Déplacer tous les éléments sélectionnés
        selectedElements.forEach(element => {
            const newX = element.properties.x + deltaX;
            const newY = element.properties.y + deltaY;

            this.updateElementProperty(element.id, 'x', newX);
            this.updateElementProperty(element.id, 'y', newY);
        });

        // Sauvegarder dans l'historique
        this.historyManager.saveState();
        this.render();
    }

    /**
     * Gestionnaire de relâchement de touches
     */
    handleKeyUp(event) {
        // Gérer les relâchements si nécessaire
    }

    /**
     * Gestionnaire de redimensionnement de fenêtre
     */
    handleResize() {
        // Ajuster la taille du canvas si nécessaire
        this.render();
    }

    /**
     * Obtient la position de la souris relative au canvas
     */
    getMousePosition(event) {
        // Pour les événements DOM directs
        if (event && typeof event.clientX === 'number' && typeof event.clientY === 'number') {
            const rect = this.canvas.getBoundingClientRect();
            const scaleX = this.canvas.width / rect.width;
            const scaleY = this.canvas.height / rect.height;
            return {
                x: (event.clientX - rect.left) * scaleX,
                y: (event.clientY - rect.top) * scaleY
            };
        }

        // Pour les événements avec originalEvent
        if (event && event.originalEvent && typeof event.originalEvent.clientX === 'number') {
            const rect = this.canvas.getBoundingClientRect();
            const scaleX = this.canvas.width / rect.width;
            const scaleY = this.canvas.height / rect.height;
            return {
                x: (event.originalEvent.clientX - rect.left) * scaleX,
                y: (event.originalEvent.clientY - rect.top) * scaleY
            };
        }

        return { x: 0, y: 0 };
    }

    /**
     * Gère le mode sélection
     */
    handleSelectMode(point, event) {
        // Utiliser les modificateurs depuis l'événement normalisé
        const ctrlKey = event.modifiers?.ctrl || event.ctrlKey || event.originalEvent?.ctrlKey;
        const multiSelect = ctrlKey;

        // Vérifier d'abord si on clique sur un handle de transformation pour les éléments sélectionnés
        const selectedElements = this.selectionManager.getSelectedElements();
        for (const element of selectedElements) {
            const transformHandle = this.transformationsManager.getHandleAtPoint(point, element);
            if (transformHandle) {
                // Démarrer une transformation par handle - pas de drag en même temps
                this.transformationsManager.startTransform(point, transformHandle);
                this.render();
                return;
            }
        }

        // Sinon, gérer la sélection normale
        const elementSelected = this.selectionManager.selectAtPoint(point, multiSelect);

        if (elementSelected) {
            // Mettre à jour selectedElement pour compatibilité
            const selectedIds = this.selectionManager.getSelectedElementIds();
            if (selectedIds.length === 1) {
                this.selectedElement = this.elements.get(selectedIds[0]);
            } else {
                this.selectedElement = null; // Multiple sélection
            }

            // Démarrer le déplacement si un élément est sélectionné (pas de transformation en cours)
            if (!this.transformationsManager.isTransforming) {
                this.startDrag(point);
            }
        } else {
            // Démarrer une sélection par rectangle
            this.selectionManager.startSelection(point);
        }
        
        // ✅ IMPORTANT: Toujours rendre après une sélection/clic
        this.render();
    }

    /**
     * Gère le mode dessin
     */
    handleDrawMode(point) {
        // Implémentation du mode dessin
        // TODO: Implement draw mode functionality
    }

    /**
     * Gère le mode texte
     */
    handleTextMode(point) {
        // Implémentation du mode texte
        // TODO: Implement text mode functionality
    }

    /**
     * Démarre un glisser-déposer
     */
    startDrag(point) {
        const selectedElements = this.selectionManager.getSelectedElements();
        if (selectedElements.length === 0) return;

        // Pour la compatibilité, utiliser le premier élément sélectionné
        this.selectedElement = selectedElements[0];

        // Calculer les bounds des éléments sélectionnés pour les contraintes
        const bounds = this.calculateSelectionBounds(selectedElements);

        this.dragState = {
            startPoint: { ...point },
            currentPoint: { ...point },
            elementStartPositions: selectedElements.map(element => ({
                id: element.id,
                x: (element.properties && element.properties.x) || 0,
                y: (element.properties && element.properties.y) || 0,
                originalX: (element.properties && element.properties.x) || 0,
                originalY: (element.properties && element.properties.y) || 0
            })),
            bounds: bounds,
            snapped: false,
            lastSnappedPosition: null
        };

        // Activer le feedback visuel si configuré
        if (this.options.dragFeedback) {
            this.canvas.style.cursor = 'grabbing';
        }
    }

    /**
     * Gère le glisser-déposer avec fluidité maximale (requestAnimationFrame)
     */
    handleDragImmediate(point) {
        if (!this.dragState || !this.dragState.elementStartPositions) return;

        // Mettre à jour la position actuelle
        this.dragState.currentPoint = { ...point };

        // Calculer les deltas
        const deltaX = point.x - this.dragState.startPoint.x;
        const deltaY = point.y - this.dragState.startPoint.y;

        // Stocker les nouvelles positions pour le rendu
        this.dragState.pendingDeltaX = deltaX;
        this.dragState.pendingDeltaY = deltaY;

        // Programmer le rendu avec requestAnimationFrame si pas déjà programmé
        if (!this.dragState.animationFrameRequested) {
            this.dragState.animationFrameRequested = true;
            requestAnimationFrame(() => {
                this.processDragUpdate();
            });
        }
    }

    /**
     * Traite la mise à jour du drag dans requestAnimationFrame
     */
    processDragUpdate() {
        if (!this.dragState) return;

        this.dragState.animationFrameRequested = false;

        const deltaX = this.dragState.pendingDeltaX;
        const deltaY = this.dragState.pendingDeltaY;

        // Appliquer le snapping à la grille si activé (optimisé)
        let finalDeltaX = deltaX;
        let finalDeltaY = deltaY;

        if (this.options.snapToGrid) {
            const snapped = this.snapToGrid(deltaX, deltaY);
            finalDeltaX = snapped.x;
            finalDeltaY = snapped.y;
            this.dragState.snapped = snapped.snapped;
        }

        // Appliquer les contraintes de canvas si activé (optimisé)
        if (this.options.constrainToCanvas) {
            const constrained = this.constrainToCanvas(finalDeltaX, finalDeltaY, this.dragState.bounds);
            finalDeltaX = constrained.x;
            finalDeltaY = constrained.y;
        }

        // Mettre à jour les positions des éléments (sans déclencher de re-rendu)
        this.dragState.elementStartPositions.forEach(startPos => {
            const element = this.elements.get(startPos.id);
            if (element) {
                element.properties.x = startPos.originalX + finalDeltaX;
                element.properties.y = startPos.originalY + finalDeltaY;
                element.updatedAt = Date.now();
            }
        });

        // Rendu complet pendant le drag pour éviter les problèmes de synchronisation
        this.render();
    }

    /**
     * Termine le glisser-déposer
     */
    endDrag() {
        if (!this.dragState) return;

        // Restaurer le curseur
        if (this.options.dragFeedback) {
            this.canvas.style.cursor = this.getCursorForMode(this.mode);
        }

        // Sauvegarder dans l'historique seulement si les éléments ont effectivement bougé
        const hasMoved = this.dragState.elementStartPositions.some(startPos => {
            const element = this.elements.get(startPos.id);
            return element &&
                   (element.properties.x !== startPos.originalX ||
                    element.properties.y !== startPos.originalY);
        });

        if (hasMoved) {
            this.historyManager.saveState();
        }

        // Nettoyer l'état de drag
        this.dragState = null;

        // Rendu final complet
        this.render();
    }

    /**
     * Calcule les bounds des éléments sélectionnés
     */
    calculateSelectionBounds(elements) {
        if (elements.length === 0) return null;

        let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;

        elements.forEach(element => {
            const props = element.properties;
            const x = props.x || 0;
            const y = props.y || 0;
            const width = props.width || 100;
            const height = props.height || 50;

            minX = Math.min(minX, x);
            minY = Math.min(minY, y);
            maxX = Math.max(maxX, x + width);
            maxY = Math.max(maxY, y + height);
        });

        return {
            x: minX,
            y: minY,
            width: maxX - minX,
            height: maxY - minY
        };
    }

    /**
     * Applique le snapping à la grille
     */
    snapToGrid(deltaX, deltaY) {
        const gridSize = this.options.gridSize;
        const snapThreshold = gridSize * 0.3; // 30% de la taille de la grille

        // Calculer la position snapped
        const snappedX = Math.round(deltaX / gridSize) * gridSize;
        const snappedY = Math.round(deltaY / gridSize) * gridSize;

        // Vérifier si on est proche assez de la grille pour snapper
        const distanceX = Math.abs(deltaX - snappedX);
        const distanceY = Math.abs(deltaY - snappedY);

        const shouldSnapX = distanceX <= snapThreshold;
        const shouldSnapY = distanceY <= snapThreshold;

        return {
            x: shouldSnapX ? snappedX : deltaX,
            y: shouldSnapY ? snappedY : deltaY,
            snapped: shouldSnapX || shouldSnapY
        };
    }

    /**
     * Contraint le déplacement aux limites du canvas
     */
    constrainToCanvas(deltaX, deltaY, bounds) {
        if (!bounds) return { x: deltaX, y: deltaY };

        const canvasWidth = this.canvas.width;
        const canvasHeight = this.canvas.height;

        // Calculer les nouvelles positions
        const newX = bounds.x + deltaX;
        const newY = bounds.y + deltaY;

        // Appliquer les contraintes
        let constrainedX = deltaX;
        let constrainedY = deltaY;

        if (newX < 0) {
            constrainedX = -bounds.x;
        } else if (newX + bounds.width > canvasWidth) {
            constrainedX = canvasWidth - bounds.width - bounds.x;
        }

        if (newY < 0) {
            constrainedY = -bounds.y;
        } else if (newY + bounds.height > canvasHeight) {
            constrainedY = canvasHeight - bounds.height - bounds.y;
        }

        return { x: constrainedX, y: constrainedY };
    }

    /**
     * Rendu ultra-optimisé pendant le drag (seulement les éléments en mouvement)
     */
    renderDragOptimized() {
        if (!this.dragState) return;

        // Éviter les appels récursifs
        if (this.isRendering) return;
        this.isRendering = true;

        const startTime = performance.now();

        try {
            // Effacer seulement la zone nécessaire (optimisation)
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

            // Dessiner la grille si activée (léger)
            if (this.options.showGrid) {
                this.drawGrid();
            }

            // Créer un Set des éléments en cours de déplacement pour un lookup O(1)
            const draggedElementIds = new Set(
                this.dragState.elementStartPositions.map(pos => pos.id)
            );

            // Rendre tous les éléments SAUF ceux en cours de déplacement
            // Utiliser for...of au lieu de forEach pour de meilleures performances
            for (const [elementId, element] of this.elements) {
                if (!draggedElementIds.has(elementId)) {
                    try {
                        this.renderer.renderElement(element, element.properties);
                    } catch (error) {
                        // Ignorer les erreurs de rendu pendant le drag pour la fluidité
                    }
                }
            }

            // Rendre les éléments en cours de déplacement avec feedback visuel optimisé
            for (const startPos of this.dragState.elementStartPositions) {
                const element = this.elements.get(startPos.id);
                if (element) {
                    this.renderElementDragFeedback(element);
                }
            }

            // Dessiner la sélection et les transformations (léger)
            this.selectionManager.render(this.ctx);
            this.transformationsManager.render(this.ctx);

        } finally {
            this.isRendering = false;

            // Mesurer le temps de rendu pour optimisations futures
            const renderTime = performance.now() - startTime;
            if (renderTime > 16.67) { // Plus de 60fps
                // Log pour debugging des performances (peut être supprimé en prod)
                console.debug(`Drag render took ${renderTime.toFixed(2)}ms`);
            }
        }
    }

    /**
     * Rendu ultra-optimisé d'un élément pendant le drag
     */
    renderElementDragFeedback(element) {
        const props = element.properties;

        // Sauvegarder le contexte (optimisé)
        this.ctx.save();

        // Appliquer les transformations de base
        this.ctx.translate(props.x + props.width / 2, props.y + props.height / 2);
        if (props.rotation) {
            this.ctx.rotate((props.rotation * Math.PI) / 180);
        }
        this.ctx.translate(-props.width / 2, -props.height / 2);

        // Appliquer l'opacité réduite pour le feedback (optimisé)
        if (props.opacity !== undefined) {
            const opacity = props.opacity;
            this.ctx.globalAlpha = (opacity <= 1 ? opacity : opacity / 100) * 0.85;
        } else {
            this.ctx.globalAlpha = 0.85;
        }

        // Ombre légère pour le feedback (optimisée)
        this.ctx.shadowColor = 'rgba(0, 123, 255, 0.3)';
        this.ctx.shadowBlur = 4;
        this.ctx.shadowOffsetX = 1;
        this.ctx.shadowOffsetY = 1;

        // Rendu selon le type d'élément (optimisé - pas de try/catch pour la vitesse)
        switch (element.type) {
            case 'text':
                this.renderTextElement(element);
                break;
            case 'rectangle':
                this.renderRectangleElement(element);
                break;
            case 'image':
                this.renderImageElement(element);
                break;
            default:
                this.renderGenericElement(element);
                break;
        }

        // Restaurer le contexte
        this.ctx.restore();
    }

    /**
     * Gère le survol des éléments
     */
    handleHover(point) {
        // Vérifier d'abord les handles de transformation pour les éléments sélectionnés
        const selectedElements = this.selectionManager.getSelectedElements();
        for (const element of selectedElements) {
            const transformHandle = this.transformationsManager.getHandleAtPoint(point, element);
            if (transformHandle) {
                // Changer le curseur selon le type de handle
                const cursorMap = {
                    'nw': 'nw-resize', 'n': 'n-resize', 'ne': 'ne-resize',
                    'e': 'e-resize', 'se': 'se-resize', 's': 's-resize',
                    'sw': 'sw-resize', 'w': 'w-resize'
                };
                this.canvas.style.cursor = cursorMap[transformHandle.position] || 'crosshair';
                return;
            }
        }

        // Pendant le drag, afficher un curseur spécial
        if (this.dragState) {
            if (this.dragState.snapped) {
                this.canvas.style.cursor = 'grabbing';
            } else {
                this.canvas.style.cursor = 'grabbing';
            }
            return;
        }

        // Sinon, vérifier les éléments pour le déplacement
        const element = this.getElementAtPoint(point);
        if (element) {
            this.canvas.style.cursor = 'grab';
        } else {
            this.canvas.style.cursor = 'default';
        }
    }

    /**
     * Sélectionne un élément
     */
    selectElement(elementId) {
        this.selectedElement = this.elements.get(elementId);
        this.selectionManager.clearSelection();
        if (this.selectedElement) {
                        const props = this.selectedElement.properties || {};
                        this.selectionManager.selectAtPoint(
                                { x: (props.x || 0) + (props.width || 100) / 2,
                                    y: (props.y || 0) + (props.height || 50) / 2 },
                                false
                        );
        }
        this.render();
    }

    /**
     * Désélectionne l'élément actuel
     */
    deselectElement() {
        this.selectedElement = null;
        this.selectionManager.clearSelection();
        this.render();
    }

    /**
     * Obtient l'élément à une position donnée
     */
    getElementAtPoint(point) {
        // Parcourir les éléments dans l'ordre inverse (dernier ajouté = premier cliqué)
        const elementsArray = Array.from(this.elements.values()).reverse();

        for (const element of elementsArray) {
            if (this.isPointInElement(point, element)) {
                return element;
            }
        }

        return null;
    }

    /**
     * Vérifie si un point est dans un élément
     */
    isPointInElement(point, element) {
        const props = element.properties;
        return point.x >= props.x &&
               point.x <= props.x + props.width &&
               point.y >= props.y &&
               point.y <= props.y + props.height;
    }

    /**
     * Ajoute un élément au canvas
     */
    addElement(type, properties = {}) {
        // Vérification de dédoublonnement : éviter d'ajouter des éléments identiques
        if (properties.id) {
            // Si un ID est fourni, vérifier s'il existe déjà
            if (this.elements.has(properties.id)) {
                console.warn(`Element with ID ${properties.id} already exists, skipping duplicate`);
                return properties.id;
            }
        }

        const elementId = properties.id || `element_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;

        // Obtenir les propriétés par défaut
        const defaultProps = this.customizationService.getDefaultProperties(type);

        // Fusionner avec les propriétés fournies
        const elementProperties = { ...defaultProps, ...properties };

        // Valider les propriétés
        const validatedProps = {};
        for (const [key, value] of Object.entries(elementProperties)) {
            validatedProps[key] = this.customizationService.validateProperty(key, value);
        }

        const element = {
            id: elementId,
            type: type,
            properties: validatedProps,
            createdAt: Date.now(),
            updatedAt: Date.now()
        };

        this.elements.set(elementId, element);
        this.historyManager.saveState();
        this.render();

        return elementId;
    }

    /**
     * Met à jour une propriété d'élément
     */
    updateElementProperty(elementId, property, value) {
        const element = this.elements.get(elementId);
        if (!element) return false;

        // Valider la propriété
        const validatedValue = this.customizationService.validateProperty(property, value);

        // Vérifier les restrictions
        if (!isPropertyAllowed(element.type, property)) {
            // Property not allowed for element type
            return false;
        }

        element.properties[property] = validatedValue;
        element.updatedAt = Date.now();

        this.historyManager.saveState();
        this.render();
        return true;
    }

    /**
     * Supprime un élément
     */
    deleteElement(elementId) {
        if (this.elements.delete(elementId)) {
            if (this.selectedElement && this.selectedElement.id === elementId) {
                this.selectedElement = null;
            }
            this.historyManager.saveState();
            this.render();
            return true;
        }
        return false;
    }

    /**
     * Annule la dernière action
     */
    undo() {
        if (this.historyManager.undo()) {
            this.emit('undo-executed');
            return true;
        }
        return false;
    }

    /**
     * Rétablit la dernière action annulée
     */
    redo() {
        if (this.historyManager.redo()) {
            this.emit('redo-executed');
            return true;
        }
        return false;
    }

    /**
     * Définit le niveau de zoom
     */
    setZoom(zoom) {
        this.options.zoom = Math.max(0.1, Math.min(5, zoom));
        this.canvas.style.transform = `scale(${this.options.zoom})`;
        this.canvas.style.transformOrigin = 'top left';
    }

    /**
     * Définit le mode d'interaction
     */
    setMode(mode) {
        this.mode = mode;
        this.canvas.style.cursor = this.getCursorForMode(mode);
    }

    /**
     * Définit l'outil actif
     */
    setTool(toolId) {
        this.tool = toolId;

        // Mapper les outils aux modes
        const toolToModeMap = {
            'select': 'select',
            'add-text': 'text',
            'add-text-title': 'text',
            'add-text-subtitle': 'text',
            'add-rectangle': 'draw',
            'add-circle': 'draw',
            'add-line': 'draw',
            'add-arrow': 'draw',
            'add-triangle': 'draw',
            'add-star': 'draw',
            'add-divider': 'draw',
            'add-image': 'draw'
        };

        const mode = toolToModeMap[toolId] || 'select';
        this.setMode(mode);
    }

    /**
     * Bascule l'affichage de la grille
     */
    toggleGrid() {
        this.options.showGrid = !this.options.showGrid;
        this.render();
        return this.options.showGrid;
    }

    /**
     * Active/désactive la grille
     */
    setGrid(enabled) {
        this.options.showGrid = enabled;
        this.render();
    }

    /**
     * Active/désactive le snapping à la grille
     */
    setSnapToGrid(enabled) {
        this.options.snapToGrid = enabled;
    }

    /**
     * Active/désactive les contraintes de canvas
     */
    setConstrainToCanvas(enabled) {
        this.options.constrainToCanvas = enabled;
    }

    /**
     * Active/désactive le feedback visuel pendant le drag
     */
    setDragFeedback(enabled) {
        this.options.dragFeedback = enabled;
    }

    /**
     * Définit la taille de la grille
     */
    setGridSize(size) {
        this.options.gridSize = Math.max(5, Math.min(100, size));
        if (this.options.showGrid) {
            this.render();
        }
    }

    /**
     * Active/désactive le snapping à la grille
     */
    setSnapToGrid(enabled) {
        this.options.snapToGrid = enabled;
    }

    /**
     * Active/désactive les contraintes de canvas
     */
    setConstrainToCanvas(enabled) {
        this.options.constrainToCanvas = enabled;
    }

    /**
     * Active/désactive le feedback visuel pendant le drag
     */
    setDragFeedback(enabled) {
        this.options.dragFeedback = enabled;
    }

    /**
     * Définit la taille de la grille
     */
    setGridSize(size) {
        this.options.gridSize = Math.max(5, Math.min(100, size));
        if (this.options.showGrid) {
            this.render();
        }
    }

    /**
     * Sélectionne tous les éléments
     */
    selectAll() {
        this.selectionManager.clearSelection();
        for (const element of this.elements.values()) {
            this.selectionManager.selectAtPoint(
                {
                    x: element.properties.x + element.properties.width / 2,
                    y: element.properties.y + element.properties.height / 2
                },
                true // multi-select
            );
        }
        this.render();
    }

    /**
     * Désélectionne tous les éléments
     */
    selectNone() {
        this.selectionManager.clearSelection();
        this.selectedElement = null;
        this.render();
    }

    /**
     * Inverse la sélection actuelle
     */
    invertSelection() {
        const currentlySelected = new Set(this.selectionManager.getSelectedElementIds());
        const allElementIds = Array.from(this.elements.keys());

        this.selectionManager.clearSelection();

        for (const elementId of allElementIds) {
            if (!currentlySelected.has(elementId)) {
                const element = this.elements.get(elementId);
                if (element) {
                    this.selectionManager.selectAtPoint(
                        {
                            x: element.properties.x + element.properties.width / 2,
                            y: element.properties.y + element.properties.height / 2
                        },
                        true
                    );
                }
            }
        }
        this.render();
    }

    /**
     * Obtient le curseur approprié pour un mode
     */
    getCursorForMode(mode) {
        const cursors = {
            select: 'default',
            draw: 'crosshair',
            text: 'text',
            move: 'move'
        };
        return cursors[mode] || 'default';
    }

    /**
     * Rend tous les éléments sur le canvas
     */
    render() {
        // Éviter les appels récursifs
        if (this.isRendering) {
            return;
        }

        this.isRendering = true;

        try {
            // Vérifier si l'optimisation de performance est activée
            if (this.performanceOptimizer && this.performanceOptimizer.shouldRender()) {
                this.performanceOptimizer.optimizeRendering();
                return;
            }

            // Rendu normal
            this.renderNormal();
        } finally {
            this.isRendering = false;
        }
    }

    /**
     * Rendu normal sans optimisation
     */
    renderNormal() {
        // Effacer le canvas
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        // Dessiner la grille si activée
        if (this.options.showGrid) {
            this.drawGrid();
        }

        // Dessiner tous les éléments en utilisant le renderer spécialisé
        for (const element of this.elements.values()) {
            try {
                this.renderer.renderElement(element, element.properties);
            } catch (error) {
                // Error rendering element
            }
        }

        // Dessiner la sélection et les transformations
        this.selectionManager.render(this.ctx);
        this.transformationsManager.render(this.ctx);

        // Dessiner le preview de drag & drop
        this.dragDropManager.render(this.ctx);
    }

    /**
     * Dessine la grille d'arrière-plan
     */
    drawGrid() {
        const gridSize = this.options.gridSize;
        const rect = this.canvas.getBoundingClientRect();
        const canvasWidth = rect.width;
        const canvasHeight = rect.height;

        this.ctx.strokeStyle = '#e0e0e0';
        this.ctx.lineWidth = 1;

        // Lignes verticales
        for (let x = 0; x <= canvasWidth; x += gridSize) {
            this.ctx.beginPath();
            this.ctx.moveTo(x, 0);
            this.ctx.lineTo(x, canvasHeight);
            this.ctx.stroke();
        }

        // Lignes horizontales
        for (let y = 0; y <= canvasHeight; y += gridSize) {
            this.ctx.beginPath();
            this.ctx.moveTo(0, y);
            this.ctx.lineTo(canvasWidth, y);
            this.ctx.stroke();
        }
    }

    /**
     * Rend un élément spécifique
     */
    renderElement(element) {
        const props = element.properties;

        // Sauvegarder le contexte
        this.ctx.save();

        // Appliquer les transformations
        this.ctx.translate(props.x + props.width / 2, props.y + props.height / 2);
        if (props.rotation) {
            this.ctx.rotate((props.rotation * Math.PI) / 180);
        }
        this.ctx.translate(-props.width / 2, -props.height / 2);

        // Appliquer l'opacité — accepte 0..1 ou 0..100
        if (props.opacity !== undefined) {
            const opacity = props.opacity;
            if (opacity <= 1) {
                this.ctx.globalAlpha = opacity;
            } else if (opacity <= 100) {
                this.ctx.globalAlpha = opacity / 100;
            }
        }

        // Rendu selon le type d'élément
        switch (element.type) {
            case 'text':
                this.renderTextElement(element);
                break;
            case 'rectangle':
                this.renderRectangleElement(element);
                break;
            case 'image':
                this.renderImageElement(element);
                break;
            default:
                this.renderGenericElement(element);
                break;
        }

        // Restaurer le contexte
        this.ctx.restore();
    }

    /**
     * Rend un élément texte
     */
    renderTextElement(element) {
        const props = element.properties;

        // Configuration du texte
        this.ctx.font = `${props.fontWeight || 'normal'} ${props.fontSize || 14}px ${props.fontFamily || 'Arial, sans-serif'}`;
        this.ctx.fillStyle = props.color || '#000000';
        this.ctx.textAlign = props.textAlign || 'left';

        // Position de départ
        let x = 0;
        let y = 0;

        // Ajuster selon l'alignement
        if (props.textAlign === 'center') {
            x = props.width / 2;
        } else if (props.textAlign === 'right') {
            x = props.width;
        }

        // Rendu du texte
        const text = props.text || 'Texte';
        const lines = text.split('\n');

        for (let i = 0; i < lines.length; i++) {
            this.ctx.fillText(lines[i], x, y + i * (props.fontSize || 14) * 1.2);
        }
    }

    /**
     * Rend un élément rectangle
     */
    renderRectangleElement(element) {
        const props = element.properties;

        // Fond
        if (props.backgroundColor && props.backgroundColor !== 'transparent') {
            this.ctx.fillStyle = props.backgroundColor;
            this.roundRect(0, 0, props.width, props.height, props.borderRadius || 0);
            this.ctx.fill();
        }

        // Bordure
        if (props.borderWidth && props.borderWidth > 0) {
            this.ctx.strokeStyle = props.borderColor || '#000000';
            this.ctx.lineWidth = props.borderWidth;
            this.roundRect(0, 0, props.width, props.height, props.borderRadius || 0);
            this.ctx.stroke();
        }
    }

    /**
     * Rend un élément image
     */
    renderImageElement(element) {
        const props = element.properties;

        if (props.src) {
            const img = new Image();
            img.onload = () => {
                // Calculer les dimensions pour le fit
                let drawWidth = props.width;
                let drawHeight = props.height;
                let drawX = 0;
                let drawY = 0;

                if (props.objectFit === 'cover') {
                    const scale = Math.max(props.width / img.width, props.height / img.height);
                    drawWidth = img.width * scale;
                    drawHeight = img.height * scale;
                    drawX = (props.width - drawWidth) / 2;
                    drawY = (props.height - drawHeight) / 2;
                }

                this.ctx.drawImage(img, drawX, drawY, drawWidth, drawHeight);
                this.render(); // Re-rendre après le chargement de l'image
            };
            img.src = props.src;
        }
    }

    /**
     * Rend un élément générique
     */
    renderGenericElement(element) {
        const props = element.properties;

        // Gestion spéciale pour les éléments WooCommerce
        if (this.isWooCommerceElement(element.type)) {
            this.renderWooCommerceElement(element);
            return;
        }

        // Rendu par défaut pour les éléments non reconnus
        this.renderRectangleElement(element);
    }

    /**
     * Vérifie si un élément est de type WooCommerce
     */
    isWooCommerceElement(type) {
        const wooCommerceTypes = [
            'product_table', 'customer_info', 'company_info', 'company_logo',
            'order_number', 'dynamic-text', 'mentions'
        ];
        return wooCommerceTypes.includes(type);
    }

    /**
     * Rend un élément WooCommerce
     */
    renderWooCommerceElement(element) {
        const props = element.properties;

        // Fond avec couleur spécifique pour les éléments WooCommerce
        this.ctx.fillStyle = props.backgroundColor || '#f8f9fa';
        this.ctx.strokeStyle = props.borderColor || '#6c757d';
        this.ctx.lineWidth = props.borderWidth || 1;

        // Dessiner le rectangle de fond
        this.roundRect(0, 0, props.width, props.height, 4);
        this.ctx.fill();
        if (props.showBorders !== false) {
            this.ctx.stroke();
        }

        // Icône et texte pour identifier le type d'élément
        this.ctx.fillStyle = '#495057';
        this.ctx.font = '12px Arial';
        this.ctx.textAlign = 'center';
        this.ctx.textBaseline = 'middle';

        const centerX = props.width / 2;
        const centerY = props.height / 2;

        // Afficher le type d'élément
        const displayName = this.getElementDisplayName(element.type);
        this.ctx.fillText(displayName, centerX, centerY);

        // Bordure distinctive pour les éléments WooCommerce
        this.ctx.strokeStyle = '#007bff';
        this.ctx.lineWidth = 2;
        this.ctx.setLineDash([5, 5]);
        this.roundRect(2, 2, props.width - 4, props.height - 4, 2);
        this.ctx.stroke();
        this.ctx.setLineDash([]);
    }

    /**
     * Obtient le nom d'affichage pour un type d'élément
     */
    getElementDisplayName(type) {
        const names = {
            'product_table': '📋 Tableau Produits',
            'customer_info': '👤 Infos Client',
            'company_info': '🏢 Infos Entreprise',
            'company_logo': '🖼️ Logo',
            'order_number': '🔢 N° Commande',
            'dynamic-text': '📝 Texte Dynamique',
            'mentions': '📄 Mentions'
        };
        return names[type] || type;
    }

    /**
     * Dessine un rectangle avec des coins arrondis
     */
    roundRect(x, y, width, height, radius) {
        if (radius === 0) {
            this.ctx.rect(x, y, width, height);
            return;
        }

        this.ctx.beginPath();
        this.ctx.moveTo(x + radius, y);
        this.ctx.lineTo(x + width - radius, y);
        this.ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
        this.ctx.lineTo(x + width, y + height - radius);
        this.ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
        this.ctx.lineTo(x + radius, y + height);
        this.ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
        this.ctx.lineTo(x, y + radius);
        this.ctx.quadraticCurveTo(x, y, x + radius, y);
        this.ctx.closePath();
    }

    /**
     * Dessine les poignées de sélection
     */
    drawSelectionHandles(element) {
        const props = element.properties;
        const handleSize = 8;

        this.ctx.strokeStyle = '#007bff';
        this.ctx.lineWidth = 2;
        this.ctx.fillStyle = '#ffffff';

        // Poignées de redimensionnement
        const handles = [
            { x: props.x, y: props.y }, // Haut-gauche
            { x: props.x + props.width, y: props.y }, // Haut-droite
            { x: props.x + props.width, y: props.y + props.height }, // Bas-droite
            { x: props.x, y: props.y + props.height } // Bas-gauche
        ];

        handles.forEach(handle => {
            this.ctx.fillRect(handle.x - handleSize/2, handle.y - handleSize/2, handleSize, handleSize);
            this.ctx.strokeRect(handle.x - handleSize/2, handle.y - handleSize/2, handleSize, handleSize);
        });

        // Rectangle de sélection
        this.ctx.strokeStyle = '#007bff';
        this.ctx.setLineDash([5, 5]);
        this.ctx.strokeRect(props.x, props.y, props.width, props.height);
        this.ctx.setLineDash([]);
    }

    /**
     * Sauvegarde l'état actuel dans l'historique
     */
    saveToHistory() {
        const state = {
            elements: new Map(this.elements),
            selectedElement: this.selectedElement ? this.selectedElement.id : null
        };

        // Supprimer les états futurs si on est au milieu de l'historique
        this.history = this.history.slice(0, this.historyIndex + 1);

        // Ajouter le nouvel état
        this.history.push(state);
        this.historyIndex++;

        // Limiter la taille de l'historique
        if (this.history.length > 50) {
            this.history.shift();
            this.historyIndex--;
        }
    }

    /**
     * Annule la dernière action
     */
    undo() {
        if (this.historyIndex > 0) {
            this.historyIndex--;
            this.restoreFromHistory();
        }
    }

    /**
     * Rétablit la dernière action annulée
     */
    redo() {
        if (this.historyIndex < this.history.length - 1) {
            this.historyIndex++;
            this.restoreFromHistory();
        }
    }

    /**
     * Restaure l'état depuis l'historique
     */
    restoreFromHistory() {
        const state = this.history[this.historyIndex];
        this.elements = new Map(state.elements);
        this.selectedElement = state.selectedElement ? this.elements.get(state.selectedElement) : null;
        this.render();
    }

    /**
     * Exporte le canvas en image
     */
    exportToImage(format = 'png', quality = 1) {
        return this.canvas.toDataURL(`image/${format}`, quality);
    }

    /**
     * Obtient les données JSON du canvas
     */
    exportToJSON() {
        return {
            version: '1.0',
            canvas: {
                width: this.options.width,
                height: this.options.height,
                backgroundColor: this.options.backgroundColor
            },
            elements: Array.from(this.elements.values()).map(element => ({
                id: element.id,
                type: element.type,
                properties: element.properties,
                createdAt: element.createdAt,
                updatedAt: element.updatedAt
            })),
            metadata: {
                exportedAt: Date.now(),
                elementCount: this.elements.size
            }
        };
    }

    /**
     * Importe des données JSON dans le canvas
     */
    importFromJSON(data) {
        try {
            this.elements.clear();

            data.elements.forEach(elementData => {
                const element = {
                    ...elementData,
                    properties: { ...elementData.properties }
                };
                this.elements.set(element.id, element);
            });

            this.render();
            this.saveToHistory();
            return true;
        } catch (error) {
            // Failed to import JSON
            return false;
        }
    }

    /**
     * Nettoie les ressources
     */
    dispose() {
        // Nettoyer l'eventManager (supprime tous les gestionnaires d'événements normalisés)
        if (this.eventManager) {
            this.eventManager.dispose();
        }

        // Supprimer les gestionnaires d'événements directs restants (drag & drop)
        document.removeEventListener('dragover', this.handleDragOver);
        document.removeEventListener('drop', this.handleDrop);
        window.removeEventListener('resize', this.handleResize);

        // Nettoyer les références
        this.elements.clear();
        this.selectedElement = null;
        this.dragState = null;
        this.history = [];
        this.historyIndex = -1;

        // PDFCanvasVanilla disposed
    }

    /**
     * Obtient la position de la souris relative au canvas
     */
    getMousePosition(event) {
        if (!this.canvas || !event) return { x: 0, y: 0 };

        const rect = this.canvas.getBoundingClientRect();
        const scaleX = this.canvas.width / rect.width;
        const scaleY = this.canvas.height / rect.height;

        return {
            x: (event.clientX - rect.left) * scaleX,
            y: (event.clientY - rect.top) * scaleY
        };
    }

    /**
     * Obtient les statistiques du canvas
     */
    getStats() {
        const baseStats = {
            totalElements: this.elements.size,
            selectedElement: this.selectedElement ? this.selectedElement.id : null,
            canvasSize: {
                width: this.canvas.width,
                height: this.canvas.height
            },
            zoom: this.options.zoom,
            mode: this.mode
        };

        // Ajouter les statistiques de performance si disponibles
        if (this.performanceOptimizer) {
            return {
                ...baseStats,
                performance: this.performanceOptimizer.getPerformanceStats()
            };
        }

        return baseStats;
    }

    /**
     * Nettoie les éléments dupliqués du canvas
     */
    removeDuplicateElements() {
        const seen = new Map();
        const duplicates = [];

        for (const [id, element] of this.elements) {
            const key = `${element.type}_${JSON.stringify(element.properties)}`;
            if (seen.has(key)) {
                duplicates.push(id);
            } else {
                seen.set(key, id);
            }
        }

        // Supprimer les éléments dupliqués
        duplicates.forEach(id => {
            this.elements.delete(id);
        });

        if (duplicates.length > 0) {
            console.log(`Removed ${duplicates.length} duplicate elements`);
            this.historyManager.saveState();
            this.render();
        }

        return duplicates.length;
    }

    /**
     * Bascule l'affichage de la grille
     */
    toggleGrid() {
        console.log('toggleGrid called - current showGrid:', this.options.showGrid);
        this.options.showGrid = !this.options.showGrid;
        console.log('toggleGrid - new showGrid:', this.options.showGrid);
        this.render();
        return this.options.showGrid;
    }

    /**
     * Active ou désactive la grille
     */
    setGridVisibility(visible) {
        console.log('setGridVisibility called - visible:', visible);
        this.options.showGrid = visible === true;
        console.log('setGridVisibility - new showGrid:', this.options.showGrid);
        this.render();
        return this.options.showGrid;
    }

    /**
     * Vérifie si la grille est visible
     */
    isGridVisible() {
        console.log('isGridVisible called - returning:', this.options.showGrid);
        return this.options.showGrid;
    }

    /**
     * Gère l'événement mousedown
     */
    handleMouseDown(event) {
        console.log('handleMouseDown:', event.position);

        const point = event.position;
        const multiSelect = event.modifiers.ctrl || event.modifiers.shift;

        // Vérifier si on clique sur un handle de transformation
        const transformHandle = this.transformationsManager.getHandleAtPoint(point);
        if (transformHandle) {
            console.log('Starting transformation:', transformHandle);
            this.transformationsManager.startTransform(point, transformHandle);
            this.canvas.style.cursor = this.getCursorForHandle(transformHandle);
            return;
        }

        // Vérifier si on clique sur un élément (pour déplacement)
        const element = this.getElementAtPoint(point);
        if (element) {
            console.log('Element clicked for move:', element.id);

            // Si l'élément n'est pas déjà sélectionné, le sélectionner
            if (!this.selectionManager.isElementSelected(element.id)) {
                this.selectionManager.selectAtPoint(point, multiSelect);
            }

            // Démarrer le déplacement si on a des éléments sélectionnés
            if (this.selectionManager.getSelectionCount() > 0) {
                this.transformationsManager.startTransform(point, { type: 'move' });
                this.canvas.style.cursor = 'move';
            }

            this.render();
            return;
        }

        // Démarrer une sélection par rectangle
        console.log('Starting selection rectangle');
        this.selectionManager.startSelection(point);
        this.render();
    }

    /**
     * Gère l'événement mousemove
     */
    handleMouseMove(event) {
        const point = event.position;

        // Si on est en train de transformer
        if (this.transformationsManager.isTransforming) {
            this.transformationsManager.updateTransform(point);
            return;
        }

        // Si on est en train de sélectionner
        if (this.selectionManager.isSelecting) {
            this.selectionManager.updateSelection(point);
            this.render();
            return;
        }

        // Mettre à jour le curseur selon ce qui est sous la souris
        const transformHandle = this.transformationsManager.getHandleAtPoint(point);
        if (transformHandle) {
            this.canvas.style.cursor = this.getCursorForHandle(transformHandle);
        } else {
            const element = this.getElementAtPoint(point);
            this.canvas.style.cursor = element ? 'move' : 'default';
        }
    }

    /**
     * Gère l'événement mouseup
     */
    handleMouseUp(event) {
        console.log('handleMouseUp');

        // Terminer la transformation si en cours
        if (this.transformationsManager.isTransforming) {
            this.transformationsManager.endTransform();
            this.canvas.style.cursor = 'default';
            return;
        }

        // Terminer la sélection si en cours
        if (this.selectionManager.isSelecting) {
            const multiSelect = event.modifiers.ctrl || event.modifiers.shift;
            this.selectionManager.endSelection(multiSelect);
            this.render();
        }
    }

    /**
     * Gère l'événement click
     */
    handleClick(event) {
        console.log('handleClick');
        // La logique de sélection est déjà gérée dans handleMouseDown
    }

    /**
     * Gère l'événement double click
     */
    handleDoubleClick(event) {
        console.log('handleDoubleClick');
        const point = event.position;
        const element = this.getElementAtPoint(point);
        if (element) {
            // Ouvrir les propriétés de l'élément ou éditer le texte
            console.log('Double-clicked element:', element.id);
            this.emit('element-double-click', { element, point });
        }
    }

    /**
     * Obtient l'élément à une position donnée
     */
    getElementAtPoint(point) {
        // Parcourir les éléments dans l'ordre inverse (dernier rendu = premier cliqué)
        const elements = Array.from(this.elements.values()).reverse();

        for (const element of elements) {
            if (this.isPointInElement(point, element)) {
                return element;
            }
        }

        return null;
    }

    /**
     * Vérifie si un point est dans un élément
     */
    isPointInElement(point, element) {
        const props = element.properties;
        const x = props.x || 0;
        const y = props.y || 0;
        const width = props.width || 100;
        const height = props.height || 50;

        return point.x >= x && point.x <= x + width &&
               point.y >= y && point.y <= y + height;
    }

    /**
     * Obtient le curseur approprié pour un handle
     */
    getCursorForHandle(handle) {
        switch (handle.type) {
            case 'move':
                return 'move';
            case 'resize':
                switch (handle.position) {
                    case 'nw': case 'se': return 'nw-resize';
                    case 'ne': case 'sw': return 'ne-resize';
                    case 'n': case 's': return 'ns-resize';
                    case 'e': case 'w': return 'ew-resize';
                    default: return 'pointer';
                }
            case 'rotate':
                return 'alias';
            default:
                return 'pointer';
        }
    }

    /**
     * Gère les événements clavier
     */
    handleKeyDown(event) {
        console.log('handleKeyDown:', event.key);

        switch (event.key) {
            case 'Delete':
            case 'Backspace':
                this.deleteSelectedElements();
                break;
            case 'Escape':
                this.selectionManager.clearSelection();
                this.render();
                break;
            case 'a':
                if (event.modifiers.ctrl) {
                    event.originalEvent.preventDefault();
                    this.selectAllElements();
                }
                break;
            case 'z':
                if (event.modifiers.ctrl) {
                    event.originalEvent.preventDefault();
                    if (event.modifiers.shift) {
                        this.redo();
                    } else {
                        this.undo();
                    }
                }
                break;
            case 'y':
                if (event.modifiers.ctrl) {
                    event.originalEvent.preventDefault();
                    this.redo();
                }
                break;
        }
    }

    /**
     * Supprime les éléments sélectionnés
     */
    deleteSelectedElements() {
        const selectedIds = Array.from(this.selectionManager.selectedElements);
        if (selectedIds.length === 0) return;

        selectedIds.forEach(id => {
            this.elements.delete(id);
        });

        this.selectionManager.clearSelection();
        this.historyManager.saveState();
        this.render();

        console.log('Deleted elements:', selectedIds);
    }

    /**
     * Sélectionne tous les éléments
     */
    selectAllElements() {
        this.selectionManager.clearSelection();
        for (const [id] of this.elements) {
            this.selectionManager.selectedElements.add(id);
        }
        this.selectionManager.updateSelectionBounds();
        this.render();
        console.log('Selected all elements');
    }

    /**
     * Undo
     */
    undo() {
        if (this.historyManager) {
            this.historyManager.undo();
        }
    }

    /**
     * Redo
     */
    redo() {
        if (this.historyManager) {
            this.historyManager.redo();
        }
    }
}

// Export de la classe
export default PDFCanvasVanilla;

// Fonction d'initialisation globale pour WordPress
window.pdfBuilderInitVanilla = function(containerIdOrOptions, options = {}) {
    // Initialisation Vanilla JS PDF Builder

    let containerId, canvasOptions;

    // Gérer les deux signatures possibles
    if (typeof containerIdOrOptions === 'string') {
        // Signature: init(containerId, options)
        containerId = containerIdOrOptions;
        canvasOptions = options;
    } else if (typeof containerIdOrOptions === 'object') {
        // Signature: init(options) - utiliser canvasElementId comme container
        canvasOptions = containerIdOrOptions;
        containerId = canvasOptions.canvasElementId || canvasOptions.containerId || 'pdf-canvas-container';
    } else {
        throw new Error('Invalid parameters for pdfBuilderInitVanilla');
    }

    try {
        // Créer l'instance principale
        const pdfCanvas = new PDFCanvasVanilla(containerId, canvasOptions);

        // Attendre que le DOM soit prêt
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                pdfCanvas.init();
            });
        } else {
            pdfCanvas.init();
        }

        // Exposer l'instance globalement pour le débogage
        window.pdfBuilderInstance = pdfCanvas;

        return pdfCanvas;

    } catch (error) {
        throw error;
    }
};

// Alias pour la compatibilité
window.pdfBuilderPro = {
    init: window.pdfBuilderInitVanilla
};