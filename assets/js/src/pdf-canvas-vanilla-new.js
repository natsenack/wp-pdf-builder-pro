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
// NOTE: PDFCanvasDragDropManager is disabled - drag & drop is handled by template-editor.php setupDragAndDrop()

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
        // NOTE: dragDropManager is disabled - template-editor.php handles drag & drop via setupDragAndDrop()
        // this.dragDropManager = new PDFCanvasDragDropManager(this);

        // État d'interaction
        this.mode = 'select';
        this.tool = null;
        this.dragState = null;
        this.selectedElement = null;

        // Optimisation du rendu avec RAF
        this.pendingFrame = null;
        this.isDirty = false;
        
        // Rendu sélectif (dirty rectangles)
        this.dirtyRegions = [];
        this.renderScheduled = false;
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

        console.log('[📥 LOAD] loadTemplateData - elements count:', templateData.elements.length);
        this.elements.clear();
        templateData.elements.forEach((elementData, index) => {
            // Conversion des unités vers pixels si nécessaire
            // ✅ FUSION: Merger les données top-level avec properties
            const properties = {
                ...elementData.properties,
                // Récupérer x, y, width, height du top-level si présents
                x: elementData.x !== undefined ? elementData.x : elementData.properties?.x,
                y: elementData.y !== undefined ? elementData.y : elementData.properties?.y,
                width: elementData.width !== undefined ? elementData.width : elementData.properties?.width,
                height: elementData.height !== undefined ? elementData.height : elementData.properties?.height,
            };
            
            // Récupérer l'unité depuis les settings
            const unit = window.pdfBuilderCanvasSettings?.default_canvas_unit || 'mm';
            
            // Facteurs de conversion vers pixels (A4: 210mm = 595px)
            const conversions = {
                'mm': 595 / 210,  // ≈ 2.833
                'cm': 595 / 21,   // ≈ 28.333
                'in': 595 / 8.27, // ≈ 72.0 (1 inch = 25.4mm, 210mm/25.4 ≈ 8.27 inches)
                'px': 1            // Pas de conversion
            };
            
            const factor = conversions[unit] || conversions['mm'];
            
            console.log(`[📥 LOAD] Element ${index}: type=${elementData.type}, unit=${unit}, factor=${factor.toFixed(3)}`);
            console.log(`[📥 LOAD]   AVANT: x=${properties.x}, y=${properties.y}, w=${properties.width}, h=${properties.height}`);
            
            // Convertir les dimensions vers pixels
            if (properties.x !== undefined) {
                properties.x = Math.round(properties.x * factor * 100) / 100; // 2 décimales
            }
            if (properties.y !== undefined) {
                properties.y = Math.round(properties.y * factor * 100) / 100;
            }
            if (properties.width !== undefined) {
                properties.width = Math.round(properties.width * factor * 100) / 100;
            }
            if (properties.height !== undefined) {
                properties.height = Math.round(properties.height * factor * 100) / 100;
            }
            
            console.log(`[📥 LOAD]   APRÈS: x=${properties.x}, y=${properties.y}, w=${properties.width}, h=${properties.height}`);
            
            this.addElement(elementData.type, properties);
        });
    }

    /**
     * RENDU PRINCIPAL - Simple et efficace
     */
    render() {
        // console.log(`🎨 [render] RENDU APPELÉ - isRendering: ${this.isRendering}, renderEngine: ${!!this.renderEngine}`);
        
        if (this.isRendering || !this.renderEngine) {
            // console.log(`⚠️  [render] Rendu IGNORÉ - isRendering: ${this.isRendering}, renderEngine: ${!!this.renderEngine}`);
            return;
        }

        this.isRendering = true;

        try {
            // Rendu sélectif si des régions sont marquées comme sales
            if (this.dirtyRegions.length > 0) {
                this.renderSelective();
            } else {
                // Rendu complet normal
                const selectedIds = this.selectionManager.getSelectedElementIds();
                this.renderEngine.renderAll(this.elements, selectedIds, this.options);
            }
            // console.log(`✅ [render] Rendu COMPLÉTÉ`);
        } catch (error) {
            console.error('❌ [render] Render error:', error);
            console.error('❌ Stack:', error.stack);
        } finally {
            this.isRendering = false;
            this.dirtyRegions = []; // Reset après rendu
        }
    }

    /**
     * Optimisation RAF - Planifie un rendu
     */
    scheduleRender() {
        if (this.renderScheduled) return;
        this.renderScheduled = true;
        
        this.pendingFrame = requestAnimationFrame(() => {
            this.pendingFrame = null;
            this.renderScheduled = false;
            if (this.isDirty) {
                this.isDirty = false;
                this.render();
            }
        });
    }

    /**
     * Rendu sélectif - Ne redessine que les régions modifiées
     */
    renderSelective() {
        const selectedIds = this.selectionManager.getSelectedElementIds();
        
        // Effacer seulement les régions sales
        this.dirtyRegions.forEach(region => {
            this.ctx.clearRect(region.x, region.y, region.width, region.height);
        });
        
        // Redessiner seulement les éléments affectés
        this.elements.forEach(element => {
            if (this.isElementInDirtyRegion(element)) {
                this.renderEngine.renderElement(element, this.options);
            }
        });
        
        // Redessiner la sélection si nécessaire
        if (selectedIds.length > 0) {
            selectedIds.forEach(id => {
                const element = this.elements.get(id);
                if (element && this.isElementInDirtyRegion(element)) {
                    this.renderEngine.renderSelection(element, this.options);
                }
            });
        }
    }

    /**
     * Marque une région comme sale (à redessiner)
     */
    markDirty(element) {
        if (!element || !element.properties) return;
        
        const region = {
            x: element.properties.x - 10,
            y: element.properties.y - 10,
            width: element.properties.width + 20,
            height: element.properties.height + 20
        };
        this.dirtyRegions.push(region);
    }

    /**
     * Vérifie si un élément est dans une région sale
     */
    isElementInDirtyRegion(element) {
        if (!element || !element.properties) return false;
        
        return this.dirtyRegions.some(region => 
            element.properties.x < region.x + region.width &&
            element.properties.x + element.properties.width > region.x &&
            element.properties.y < region.y + region.height &&
            element.properties.y + element.properties.height > region.y
        );
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

        // Tenir compte du zoom CSS
        const zoom = this.options.zoom || 1;

        return {
            x: ((clientX - rect.left) * scaleX) / zoom,
            y: ((clientY - rect.top) * scaleY) / zoom
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
            this.scheduleRender();
            return;
        }

        if (this.dragState) {
            this.dragState.lastPoint = point;
            this.updateDrag(point);
            this.scheduleRender();
            return;
        }

        if (this.selectionManager.isSelecting) {
            this.selectionManager.updateSelection(point);
            this.scheduleRender();
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
        // Drag & drop is handled by template-editor.php setupDragAndDrop()
        // This method is kept for compatibility but does nothing
    }

    handleDrop(event) {
        // Drag & drop is handled by template-editor.php setupDragAndDrop()
        // This method is kept for compatibility but does nothing
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
            lastPoint: point, // Pour éviter les mises à jour inutiles
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

        // Mettre à jour les éléments et marquer les régions comme sales
        this.dragState.elementStartPositions.forEach(startPos => {
            const element = this.elements.get(startPos.id);
            if (element) {
                const newX = startPos.x + deltaX;
                const newY = startPos.y + deltaY;
                
                // Marquer l'ancienne position comme sale
                this.markDirty(element);
                
                element.properties.x = newX;
                element.properties.y = newY;
                
                // Marquer la nouvelle position comme sale
                this.markDirty(element);
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

    /**
     * Sérialise les éléments du canvas avec leurs positions actuelles
     * Prépare les données pour la sauvegarde en base de données
     */
    serializeElements() {
        const serialized = [];
        
        // Récupérer l'unité depuis les settings
        const unit = window.pdfBuilderCanvasSettings?.default_canvas_unit || 'mm';
        
        // Facteurs de conversion depuis pixels
        const conversions = {
            'mm': 210 / 595,  // ≈ 0.353
            'cm': 21 / 595,   // ≈ 0.0353
            'in': 8.27 / 595, // ≈ 0.0139
            'px': 1            // Pas de conversion
        };
        
        const factor = conversions[unit] || conversions['mm'];
        
        console.log(`[💾 SAVE] serializeElements - unit=${unit}, factor=${factor.toFixed(4)}, elements=${this.elements.size}`);
        
        for (const [id, element] of this.elements) {
            // Convertir les positions de pixels vers l'unité configurée
            const xPixels = element.properties.x || 0;
            const yPixels = element.properties.y || 0;
            const wPixels = element.properties.width || 100;
            const hPixels = element.properties.height || 50;
            
            const elementData = {
                id: element.id,
                type: element.type,
                x: Math.round(xPixels * factor * 100) / 100,
                y: Math.round(yPixels * factor * 100) / 100,
                width: Math.round(wPixels * factor * 100) / 100,
                height: Math.round(hPixels * factor * 100) / 100,
                ...element.properties  // Inclure toutes les autres propriétés
            };
            
            console.log(`[💾 SAVE]   ${element.type}: px(${xPixels},${yPixels},${wPixels},${hPixels}) → unit(${elementData.x},${elementData.y},${elementData.width},${elementData.height})`);
            
            serialized.push(elementData);
        }
        
        return {
            elements: serialized,
            canvasWidth: this.options.width || 595,
            canvasHeight: this.options.height || 842,
            version: this.options.version || '1.0.0',
            settings: {
                width: this.options.width,
                height: this.options.height,
                backgroundColor: this.options.backgroundColor,
                gridSize: this.options.gridSize
            }
        };
    }

    /**
     * Sauvegarde le template en base de données
     * Envoie les éléments avec leurs positions actuelles au serveur
     */
    async saveTemplate() {
        try {
            const templateData = this.serializeElements();
            const templateName = this.options.templateName || `Template ${Date.now()}`;
            const templateId = this.options.templateId || 0;

            const response = await fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'pdf_builder_save_template',
                    template_id: templateId,
                    template_name: templateName,
                    template_data: JSON.stringify(templateData),
                    nonce: window.pdfBuilderNonce || ''
                })
            });

            const data = await response.json();

            if (data.success) {
                console.log('✅ [saveTemplate] Template sauvegardé avec succès');
                console.log('📥 [saveTemplate] Template ID:', data.data.template_id);
                this.historyManager.saveState();
                return true;
            } else {
                console.error('❌ [saveTemplate] Erreur:', data.data);
                return false;
            }
        } catch (error) {
            console.error('❌ [saveTemplate] Exception:', error);
            return false;
        }
    }

    /**
     * Exporte le PDF
     */
    async exportPDF() {
        try {
            const templateData = this.serializeElements();

            const response = await fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'pdf_builder_generate_pdf',
                    template_data: JSON.stringify(templateData),
                    nonce: window.pdfBuilderNonce || ''
                })
            });

            const data = await response.json();

            if (data.success) {
                console.log('✅ [exportPDF] PDF généré');
                // Rediriger vers le PDF ou l'ouvrir
                if (data.data.url) {
                    window.open(data.data.url, '_blank');
                }
                return true;
            } else {
                console.error('❌ [exportPDF] Erreur:', data.data);
                return false;
            }
        } catch (error) {
            console.error('❌ [exportPDF] Exception:', error);
            return false;
        }
    }
}

export default PDFCanvasVanilla;
// Force rebuild
