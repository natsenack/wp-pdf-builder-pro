/**
 * PDF Canvas Vanilla - Version complètement reconstruite et simplifiée
 * Engine de rendu minimaliste et robuste basé sur PDFCanvasCore
 */

import { WooCommerceElementsManager, wooCommerceElementsManager } from './pdf-canvas-woocommerce.js';
import { ElementCustomizationService, elementCustomizationService } from './pdf-canvas-customization.js';
import { PDFCanvasCore } from './pdf-canvas-core.js';
import { PDFCanvasEventManager } from './pdf-canvas-events.js';
import { PDFCanvasSelectionManager } from './pdf-canvas-selection.js';
import { PDFCanvasTransformationsManager } from './pdf-canvas-transformations.js';
import { PDFCanvasHistoryManager } from './pdf-canvas-history.js';
import { PDFCanvasDragDropManager } from './pdf-canvas-dragdrop.js';

/**
 * Classe principale du Canvas Vanilla
 * Gère l'initialisation, les événements et le rendu
 */
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
            canvasElementId: options.canvasElementId || 'pdf-builder-canvas',
            templateId: options.templateId || null,
            ...options
        };

        // État du canvas
        this.canvas = null;
        this.ctx = null;
        this.renderEngine = null;
        this.isInitialized = false;
        this.isRendering = false;

        // Gestion des éléments
        this.elements = new Map();
        this.eventListeners = new Map();

        // Services
        this.wooCommerceManager = wooCommerceElementsManager;
        this.customizationService = elementCustomizationService;

        // Managers
        this.eventManager = new PDFCanvasEventManager(this);
        this.selectionManager = new PDFCanvasSelectionManager(this);
        this.transformationsManager = new PDFCanvasTransformationsManager(this);
        this.historyManager = new PDFCanvasHistoryManager(this);
        this.dragDropManager = new PDFCanvasDragDropManager(this);

        // État d'interaction
        this.mode = 'select';
        this.tool = null;
        this.dragState = null;
        this.selectedElement = null;
    }

    /**
     * Événements personnalisés
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
     * Initialisation du canvas
     */
    async init() {
        console.log('🚀🚀🚀 [init] DÉBUT initialisation');
        try {
            console.log('🚀 [init] DÉBUT initialisation PDFCanvasVanilla');
        } catch (e) {
            console.error('Erreur avant premier log:', e);
        }
        console.log('🚀 [init] containerId:', this.containerId);
        console.log('🚀 [init] options:', this.options);
        
        try {
            // 1. Créer le canvas
            console.log('🚀 [init] Appel createCanvas()');
            this.createCanvas();
            console.log('✅ [init] Canvas créé avec succès');

            // 2. Configurer le contexte
            console.log('🚀 [init] Appel setupContext()');
            this.setupContext();
            console.log('✅ [init] Contexte configuré');

            // 3. Initialiser le render engine
            console.log('🚀 [init] Création de PDFCanvasCore');
            this.renderEngine = new PDFCanvasCore(this.canvas, this.ctx);
            console.log('✅ [init] PDFCanvasCore créé:', this.renderEngine);

            // 4. Initialiser l'event manager
            console.log('🚀 [init] Initialisation event manager');
            this.eventManager.initialize(this.canvas);
            console.log('✅ [init] Event manager initialisé');

            // 5. Attacher les événements
            console.log('🚀 [init] Attachement des événements');
            this.attachEventListeners();
            console.log('✅ [init] Événements attachés');

            // 6. Charger les données initiales
            console.log('🚀 [init] Appel loadInitialData()');
            await this.loadInitialData();
            console.log('✅ [init] Données initiales chargées');

            // 7. Premier rendu
            console.log('🚀 [init] Appel render() - premier rendu');
            this.render();
            console.log('✅ [init] Premier rendu effectué');

            // 8. Sauvegarder l'état initial
            console.log('🚀 [init] Sauvegarde de l\'état initial');
            this.historyManager.saveState();
            console.log('✅ [init] État initial sauvegardé');

            this.isInitialized = true;
            console.log('🟢 [init] Canvas initialisé avec SUCCÈS');
        } catch (error) {
            console.error('❌ [init] ERREUR initialisation:', error);
            console.error('❌ Stack:', error.stack);
            throw error;
        }
    }

    /**
     * Crée le canvas
     */
    createCanvas() {
        console.log('🔍 [createCanvas] Recherche container:', this.containerId);
        const container = document.getElementById(this.containerId);
        if (!container) {
            console.error('❌ [createCanvas] Container NOT FOUND:', this.containerId);
            throw new Error(`Container "${this.containerId}" not found`);
        }
        console.log('✅ [createCanvas] Container trouvé:', container);

        console.log('🔍 [createCanvas] Recherche canvas:', this.options.canvasElementId);
        this.canvas = document.getElementById(this.options.canvasElementId || 'pdf-builder-canvas');
        if (!this.canvas) {
            console.error('❌ [createCanvas] Canvas NOT FOUND:', this.options.canvasElementId);
            throw new Error(`Canvas element "${this.options.canvasElementId}" not found`);
        }
        console.log('✅ [createCanvas] Canvas trouvé:', this.canvas);

        console.log('🔍 [createCanvas] Récupération contexte 2D');
        this.ctx = this.canvas.getContext('2d');
        if (!this.ctx) {
            console.error('❌ [createCanvas] Contexte 2D FAILED');
            throw new Error('Failed to get 2D context');
        }
        console.log('✅ [createCanvas] Contexte 2D obtenu');
        console.log('📐 [createCanvas] Dimensions canvas:', this.canvas.width, 'x', this.canvas.height);
    }

    /**
     * Configure le contexte
     */
    setupContext() {
        this.ctx.imageSmoothingEnabled = true;
        this.ctx.imageSmoothingQuality = 'high';
        this.ctx.textBaseline = 'top';
        this.ctx.font = '14px Arial';
    }

    /**
     * Attache les écouteurs d'événements
     */
    attachEventListeners() {
        document.addEventListener('dragover', this.handleDragOver.bind(this));
        document.addEventListener('drop', this.handleDrop.bind(this));
        window.addEventListener('resize', this.handleResize.bind(this));
    }

    /**
     * Charge les données initiales
     */
    async loadInitialData() {
        console.log('📥 [loadInitialData] Début chargement données');
        console.log('📥 [loadInitialData] templateId:', this.options.templateId);
        console.log('📥 [loadInitialData] templateData:', this.options.templateData);
        
        if (this.options.templateId && this.options.templateId > 0) {
            console.log('📥 [loadInitialData] Chargement template du serveur ID:', this.options.templateId);
            await this.loadTemplateFromServer(this.options.templateId);
        } else if (this.options.templateData) {
            console.log('📥 [loadInitialData] Chargement templateData local');
            this.loadTemplateData(this.options.templateData);
        } else {
            console.log('📥 [loadInitialData] Pas de template trouvé');
        }

        // Activer le mode test pour WooCommerce
        console.log('📥 [loadInitialData] Activation mode test WooCommerce');
        this.wooCommerceManager.setTestMode(true);
        console.log('📥 [loadInitialData] Chargement données WooCommerce');
        await this.wooCommerceManager.loadWooCommerceData();
        console.log('✅ [loadInitialData] Données WooCommerce chargées');
    }

    /**
     * Charge un template depuis le serveur
     */
    async loadTemplateFromServer(templateId) {
        try {
            const response = await fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'pdf_builder_load_template',
                    template_id: templateId,
                    nonce: window.pdfBuilderNonce || ''
                })
            });

            const data = await response.json();
            if (data.success && data.data.template) {
                this.loadTemplateData(data.data.template);
            }
        } catch (error) {
            console.error('Failed to load template:', error);
        }
    }

    /**
     * Charge les données du template
     */
    loadTemplateData(templateData) {
        if (!templateData.elements) return;

        this.elements.clear();
        templateData.elements.forEach(elementData => {
            this.addElement(elementData.type, elementData.properties);
        });
    }

    /**
     * RENDU PRINCIPAL - Simple et efficace
     */
    render() {
        console.log(`🎨 [render] RENDU APPELÉ - isRendering: ${this.isRendering}, renderEngine: ${!!this.renderEngine}`);
        
        if (this.isRendering || !this.renderEngine) {
            console.log(`⚠️  [render] Rendu IGNORÉ - isRendering: ${this.isRendering}, renderEngine: ${!!this.renderEngine}`);
            return;
        }

        this.isRendering = true;

        try {
            console.log(`🎨 [render] Éléments: ${this.elements.size}`);
            const selectedIds = this.selectionManager.getSelectedElementIds();
            console.log(`🎨 [render] Appel renderEngine.renderAll() avec ${this.elements.size} éléments`);
            this.renderEngine.renderAll(this.elements, selectedIds, this.options);
            console.log(`✅ [render] Rendu COMPLÉTÉ`);
        } catch (error) {
            console.error('❌ [render] Render error:', error);
            console.error('❌ Stack:', error.stack);
        } finally {
            this.isRendering = false;
        }
    }

    /**
     * Ajoute un élément
     */
    addElement(type, properties = {}) {
        const elementId = properties.id || `element_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;

        if (this.elements.has(elementId)) {
            return elementId;
        }

        const defaultProps = this.customizationService.getDefaultProperties(type);
        const elementProperties = { ...defaultProps, ...properties };

        const element = {
            id: elementId,
            type: type,
            properties: elementProperties,
            createdAt: Date.now(),
            updatedAt: Date.now()
        };

        this.elements.set(elementId, element);
        console.log(`➕ Element ajouté: ${type} (ID: ${elementId.substring(0, 20)}...) - Position: (${elementProperties.x}, ${elementProperties.y}), Size: ${elementProperties.width}x${elementProperties.height}`);
        
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

        element.properties[property] = value;
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
     * Obtient l'élément à une position
     */
    getElementAtPoint(point) {
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
        const p = element.properties;
        const x = p.x || 0;
        const y = p.y || 0;
        const width = p.width || 100;
        const height = p.height || 50;

        return point.x >= x && point.x <= x + width && point.y >= y && point.y <= y + height;
    }

    /**
     * Obtient la position de la souris
     */
    getMousePosition(event) {
        if (!this.canvas) return { x: 0, y: 0 };

        const rect = this.canvas.getBoundingClientRect();
        const scaleX = this.canvas.width / rect.width;
        const scaleY = this.canvas.height / rect.height;

        let clientX, clientY;

        if (event.position) {
            return event.position;
        } else if (event.originalEvent) {
            clientX = event.originalEvent.clientX;
            clientY = event.originalEvent.clientY;
        } else {
            clientX = event.clientX;
            clientY = event.clientY;
        }

        return {
            x: (clientX - rect.left) * scaleX,
            y: (clientY - rect.top) * scaleY
        };
    }

    /**
     * Gestionnaires d'événements
     */
    handleMouseDown(event) {
        const point = this.getMousePosition(event);
        const ctrlKey = event.modifiers?.ctrl || event.ctrlKey || event.originalEvent?.ctrlKey;

        const elementSelected = this.selectionManager.selectAtPoint(point, ctrlKey);
        if (elementSelected) {
            const selectedIds = this.selectionManager.getSelectedElementIds();
            if (selectedIds.length === 1) {
                this.selectedElement = this.elements.get(selectedIds[0]);
                this.startDrag(point);
            }
        }

        this.render();
    }

    handleMouseMove(event) {
        const point = this.getMousePosition(event);

        if (this.transformationsManager.isTransforming) {
            this.transformationsManager.updateTransform(point);
            this.render();
            return;
        }

        if (this.dragState) {
            this.updateDrag(point);
            this.render();
            return;
        }

        if (this.selectionManager.isSelecting) {
            this.selectionManager.updateSelection(point);
            this.render();
        }
    }

    handleMouseUp(event) {
        if (this.transformationsManager.isTransforming) {
            this.transformationsManager.endTransform();
        }

        if (this.dragState) {
            this.endDrag();
        }

        if (this.selectionManager.isSelecting) {
            this.selectionManager.endSelection(event.ctrlKey || event.metaKey);
        }

        this.render();
    }

    handleDragOver(event) {
        if (this.dragDropManager) {
            this.dragDropManager.handleDragOver(event);
        }
    }

    handleDrop(event) {
        if (this.dragDropManager) {
            this.dragDropManager.handleDrop(event);
        }
    }

    handleResize() {
        this.render();
    }

    /**
     * Gestion du drag local
     */
    startDrag(point) {
        const selectedElements = this.selectionManager.getSelectedElements();
        if (selectedElements.length === 0) return;

        this.dragState = {
            startPoint: point,
            elementStartPositions: selectedElements.map(el => ({
                id: el.id,
                x: el.properties.x || 0,
                y: el.properties.y || 0
            }))
        };
    }

    updateDrag(point) {
        if (!this.dragState) return;

        const deltaX = point.x - this.dragState.startPoint.x;
        const deltaY = point.y - this.dragState.startPoint.y;

        this.dragState.elementStartPositions.forEach(startPos => {
            const element = this.elements.get(startPos.id);
            if (element) {
                element.properties.x = startPos.x + deltaX;
                element.properties.y = startPos.y + deltaY;
            }
        });
    }

    endDrag() {
        if (this.dragState) {
            this.historyManager.saveState();
            this.dragState = null;
        }
    }

    /**
     * Sélection
     */
    selectElement(elementId) {
        this.selectionManager.clearSelection();
        this.selectionManager.selectAtPoint(
            { x: 0, y: 0 },
            false
        );
        this.selectedElement = this.elements.get(elementId);
        this.render();
    }

    deselectElement() {
        this.selectionManager.clearSelection();
        this.selectedElement = null;
        this.render();
    }

    selectAll() {
        this.selectionManager.clearSelection();
        for (const element of this.elements.values()) {
            const p = element.properties;
            this.selectionManager.selectAtPoint(
                { x: (p.x || 0) + (p.width || 100) / 2, y: (p.y || 0) + (p.height || 50) / 2 },
                true
            );
        }
        this.render();
    }

    /**
     * Undo/Redo
     */
    undo() {
        if (this.historyManager.undo()) {
            this.render();
            return true;
        }
        return false;
    }

    redo() {
        if (this.historyManager.redo()) {
            this.render();
            return true;
        }
        return false;
    }

    /**
     * Grille
     */
    toggleGrid() {
        this.options.showGrid = !this.options.showGrid;
        this.render();
        return this.options.showGrid;
    }

    setGrid(enabled) {
        this.options.showGrid = enabled;
        this.render();
    }

    /**
     * Zoom
     */
    setZoom(zoom) {
        this.options.zoom = Math.max(0.1, Math.min(5, zoom));
        this.canvas.style.transform = `scale(${this.options.zoom})`;
        this.canvas.style.transformOrigin = 'top left';
    }

    /**
     * Mode
     */
    setMode(mode) {
        this.mode = mode;
    }

    setTool(toolId) {
        this.tool = toolId;
    }
}

export default PDFCanvasVanilla;
