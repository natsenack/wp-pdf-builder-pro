/**
 * PDF Builder Pro - Canvas - Version Archive Réparée
 * Réparé suite aux modifications problématiques de Claude Sonnet 4
 */

if (typeof window.PDF_BUILDER_CANVAS !== 'undefined') {
    console.warn('⚠️ PDF_BUILDER_CANVAS déjà déclaré, réutilisation');
} else {

const PDF_BUILDER_CANVAS = {
    // État simplifié
    state: {
        initialized: false,
        zoom: 100,
        selectedElements: new Set(),
        isDirty: false,
        snapToGrid: true,
        showGrid: true,
        showMargins: true,
        gridSize: 10,
        canvasSize: { width: 794, height: 1123 },
        previewMode: false
    },

    // Cache optimisé
    cache: {
        elements: new Map(),
        undoStack: [],
        redoStack: []
    },

    /**
     * Initialise le canvas
     */
    init: function(options = {}) {
        if (this.state.initialized) return;

        if (options && typeof PDF_BUILDER_UNIFIED_CONFIG !== 'undefined') {
            PDF_BUILDER_UNIFIED_CONFIG.initDimensions(options);
            this.state.canvasSize = {
                width: PDF_BUILDER_UNIFIED_CONFIG.CANVAS.WIDTH,
                height: PDF_BUILDER_UNIFIED_CONFIG.CANVAS.HEIGHT
            };
        }

        this.setupCanvas();
        this.setupGrid();
        this.setupZoom();
        this.bindEvents();

        // Initialisation conditionnelle des systèmes additionnels
        this.layers && this.layers.create && this.layers.create('default', { name: 'Calque par défaut' });
        this.drawing && this.drawing.init && this.drawing.init();
        this.performance && this.performance.startMonitoring && this.performance.startMonitoring();

        if (this.state.ai && this.state.ai.enabled) {
            this.ai.analyzeLayout && this.ai.analyzeLayout();
        }

        this.state.initialized = true;
        console.log('🚀 Canvas initialisé (version Archive Réparée)');
    },

    /**
     * Configure le canvas de base
     */
    setupCanvas: function() {
        const $canvas = jQuery('#pdf-canvas');
        if (!$canvas.length) {
            console.error('❌ Canvas introuvable');
            return;
        }

        $canvas.empty().append(`
            <div id="canvas-grid" class="canvas-layer canvas-grid"></div>
            <div id="canvas-margins" class="canvas-layer canvas-margins"></div>
            <div id="pdf-elements-container" class="canvas-layer elements-container"></div>
            <div id="canvas-selection" class="canvas-layer selection-layer"></div>
        `);

        this.updateCanvasSize();
    },

    /**
     * Configure la grille
     */
    setupGrid: function() {
        const $grid = jQuery('#canvas-grid');
        if (!$grid.length) return;

        const { width, height } = this.state.canvasSize;
        let gridHTML = '';
        for (let x = 0; x <= width; x += this.state.gridSize) {
            gridHTML += `<div class="grid-line grid-vertical" style="left: ${x}px;"></div>`;
        }
        for (let y = 0; y <= height; y += this.state.gridSize) {
            gridHTML += `<div class="grid-line grid-horizontal" style="top: ${y}px;"></div>`;
        }

        $grid.html(gridHTML).toggle(this.state.showGrid);
    },

    /**
     * Configure le zoom
     */
    setupZoom: function() {
        this.updateZoom();
    },

    /**
     * Met à jour le zoom
     */
    updateZoom: function() {
        const $canvas = jQuery('#pdf-canvas');
        $canvas.css('transform', `scale(${this.state.zoom / 100})`);
        jQuery('.zoom-level').text(`${this.state.zoom}%`);
    },

    /**
     * Met à jour la taille du canvas
     */
    updateCanvasSize: function() {
        jQuery('#pdf-canvas').css({
            width: this.state.canvasSize.width + 'px',
            height: this.state.canvasSize.height + 'px'
        });
    },

    /**
     * Lie tous les événements
     */
    bindEvents: function() {
        const self = this;

        jQuery(document).on('keydown', function(e) {
            if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;
            switch(e.key) {
                case 'Delete':
                case 'Backspace':
                    e.preventDefault();
                    self.deleteSelected && self.deleteSelected();
                    break;
                case 'Escape':
                    self.deselectAll && self.deselectAll();
                    break;
            }

            if (e.ctrlKey || e.metaKey) {
                if (e.key === 'z' && !e.shiftKey) {
                    e.preventDefault();
                    self.undo && self.undo();
                } else if (e.key === 'y' || (e.key === 'z' && e.shiftKey)) {
                    e.preventDefault();
                    self.redo && self.redo();
                }
            }
        });

        jQuery(document).on('click', '#pdf-canvas', function(e) {
            if (e.target.id === 'pdf-canvas') {
                self.deselectAll && self.deselectAll();
            }
        });

        jQuery(document).on('dragover', '#pdf-canvas', function(e) {
            e.preventDefault();
            jQuery(this).addClass('drag-over');
        });

        jQuery(document).on('dragleave', '#pdf-canvas', function(e) {
            e.preventDefault();
            jQuery(this).removeClass('drag-over');
        });

        jQuery(document).on('drop', '#pdf-canvas', function(e) {
            e.preventDefault();
            e.stopPropagation();
            jQuery(this).removeClass('drag-over');

            try {
                // Vérifier si des données existent
                const dataTransfer = e.originalEvent.dataTransfer;
                if (!dataTransfer) {
                    console.warn('⚠️ Pas de données de transfert');
                    return;
                }

                const dataText = dataTransfer.getData('text/plain');
                if (!dataText || dataText.trim() === '') {
                    console.warn('⚠️ Données de drag vides');
                    return;
                }

                const data = JSON.parse(dataText);

                // Validation des données
                if (!data || !data.type) {
                    console.error('❌ Données invalides:', data);
                    return;
                }

                const rect = e.target.getBoundingClientRect();
                const x = (e.originalEvent.clientX - rect.left) / (self.state.zoom / 100);
                const y = (e.originalEvent.clientY - rect.top) / (self.state.zoom / 100);

                // Vérifier que les coordonnées sont valides
                if (isNaN(x) || isNaN(y) || x < 0 || y < 0) {
                    console.error('❌ Coordonnées invalides:', { x, y });
                    return;
                }

                self.addElementAtPosition(data.type, data.category || 'base', x, y);
                console.log('✅ Élément déposé:', data.type, 'à', Math.round(x), Math.round(y));

            } catch (error) {
                console.error('❌ Erreur lors du drop:', error);
                // Afficher un message utilisateur si possible
                if (typeof toastr !== 'undefined') {
                    toastr.error('Erreur lors du dépôt de l\'élément');
                }
            }
        });

        // Événements de dessin avancés (avec namespaces pour éviter les conflits)
        jQuery('#pdf-canvas').on('mousedown.drawing', (e) => {
            if (e.which !== 1) return; // Only left click

            // Vérifier si on clique sur un élément ou sa poignée de drag
            const $target = jQuery(e.target);
            if ($target.hasClass('pdf-element') ||
                $target.closest('.pdf-element').length ||
                $target.hasClass('element-handle') ||
                $target.closest('.element-handle').length) {
                return; // Laisser le drag gérer cela
            }

            this.drawing.startDrawing(e);
        });

        jQuery(document).on('mousemove.drawing', (e) => {
            // Ne pas interférer si on est en train de dragger un élément
            if (jQuery('.pdf-element.dragging').length > 0) return;
            this.drawing.continueDrawing(e);
        });

        jQuery(document).on('mouseup.drawing', (e) => {
            // Ne pas interférer si on est en train de dragger un élément
            if (jQuery('.pdf-element.dragging').length > 0) return;
            this.drawing.finishDrawing(e);
        });
    },

    /**
     * Ajoute un élément à une position
     */
    addElementAtPosition: function(type, category, x, y) {
        const elementId = this.generateId();
        const template = this.getElementTemplate(type, category);

        if (!template) {
            console.error('❌ Template introuvable:', type);
            return;
        }

        // Snap to grid
        if (this.state.snapToGrid) {
            x = Math.round(x / this.state.gridSize) * this.state.gridSize;
            y = Math.round(y / this.state.gridSize) * this.state.gridSize;
        }

        const elementData = {
            ...template,
            id: elementId,
            x: Math.max(0, x),
            y: Math.max(0, y)
        };

        const $element = this.createElement(elementData);
        jQuery('#pdf-elements-container').append($element);

        this.makeElementInteractive($element);
        this.selectElement($element);
        this.saveState();

        return $element;
    },

    /**
     * Crée un élément DOM (fonction unifiée)
     */
    createElement: function(data) {
        const styles = this.generateElementStyles(data);
        const content = this.renderElementContent(data);

        const $element = jQuery(`
            <div class="pdf-element pdf-element-${data.type}"
                 data-element-id="${data.id}"
                 data-type="${data.type}"
                 style="
                     position: absolute;
                     left: ${data.x}px;
                     top: ${data.y}px;
                     width: ${data.width || 200}px;
                     height: ${data.height || 'auto'};
                     z-index: ${data.zIndex || 1};
                     ${styles}
                 ">
                <div class="element-handle" title="Déplacer">⋮⋮</div>
                <div class="element-toolbar">
                    <button class="element-btn btn-duplicate" title="Dupliquer">📋</button>
                    <button class="element-btn btn-delete" title="Supprimer">🗑️</button>
                </div>
                <div class="element-content">${content}</div>
                <div class="resize-handles">
                    <div class="resize-handle resize-se"></div>
                </div>
            </div>
        `);

        // Cache des données
        this.cache.elements.set(data.id, data);

        return $element;
    },

    /**
     * Génère les styles CSS
     */
    generateElementStyles: function(data) {
        if (!data.style) return '';

        let css = '';
        const style = data.style;

        if (style.fontSize) css += `font-size: ${style.fontSize}px; `;
        if (style.fontWeight) css += `font-weight: ${style.fontWeight}; `;
        if (style.color) css += `color: ${style.color}; `;
        if (style.backgroundColor) css += `background-color: ${style.backgroundColor}; `;
        if (style.border) css += `border: ${style.border.width}px solid ${style.border.color}; `;

        return css;
    },

    /**
     * Rend le contenu selon le type
     */
    renderElementContent: function(data) {
        switch (data.type) {
            case 'text':
                return `<div contenteditable="true">${data.content || 'Texte'}</div>`;

            case 'image':
                return data.src
                    ? `<img src="${data.src}" alt="${data.alt || ''}" style="max-width: 100%; height: auto;">`
                    : `<div style="padding: 20px; text-align: center; border: 2px dashed #ccc;">📷<br>Cliquez pour ajouter une image</div>`;

            case 'table':
                const rows = data.rows || 2;
                const cols = data.cols || 3;
                let tableHTML = '<table style="width: 100%; border-collapse: collapse;">';
                for (let r = 0; r < rows; r++) {
                    tableHTML += '<tr>';
                    for (let c = 0; c < cols; c++) {
                        tableHTML += `<td style="border: 1px solid #ddd; padding: 4px;" contenteditable="true">Cellule</td>`;
                    }
                    tableHTML += '</tr>';
                }
                tableHTML += '</table>';
                return tableHTML;

            default:
                return `<div contenteditable="true">${data.content || 'Contenu'}</div>`;
        }
    },

    /**
     * Rend un élément interactif
     */
    makeElementInteractive: function($element) {
        this.bindElementEvents($element);
        this.makeDraggable($element);
        this.makeResizable($element);
    },

    /**
     * Lie les événements d'un élément
     */
    bindElementEvents: function($element) {
        const self = this;

        // Sélection
        $element.on('click', function(e) {
            e.stopPropagation();
            if (!e.ctrlKey) self.deselectAll();
            self.selectElement($element);
        });

        // Boutons
        $element.on('click', '.btn-delete', function(e) {
            e.stopPropagation();
            self.deleteElement($element);
        });

        $element.on('click', '.btn-duplicate', function(e) {
            e.stopPropagation();
            self.duplicateElement($element);
        });

        // Édition du contenu
        $element.on('input', '[contenteditable]', function() {
            self.updateElementData($element);
            self.markAsDirty();
        });
    },

    /**
     * Rend un élément déplaçable
     */
    makeDraggable: function($element) {
        let isDragging = false;
        let startX, startY, startLeft, startTop;
        let dragHandler = null;
        let mouseMoveHandler = null;
        let mouseUpHandler = null;

        // Nettoyer les anciens événements si existants
        this.cleanupDraggable($element);

        dragHandler = function(e) {
            e.preventDefault();
            e.stopPropagation();
            isDragging = true;

            startX = e.pageX;
            startY = e.pageY;
            startLeft = parseInt($element.css('left')) || 0;
            startTop = parseInt($element.css('top')) || 0;

            $element.addClass('dragging');
            $element.css('z-index', 1000);
        };

        mouseMoveHandler = function(e) {
            if (!isDragging) return;

            e.preventDefault();
            const deltaX = e.pageX - startX;
            const deltaY = e.pageY - startY;

            let newLeft = startLeft + deltaX;
            let newTop = startTop + deltaY;

            // Contraintes avec marges de sécurité
            const canvasSize = PDF_BUILDER_CANVAS.state.canvasSize;
            const elementWidth = $element.outerWidth() || 50;
            const elementHeight = $element.outerHeight() || 50;

            newLeft = Math.max(0, Math.min(canvasSize.width - elementWidth, newLeft));
            newTop = Math.max(0, Math.min(canvasSize.height - elementHeight, newTop));

            $element.css({
                left: newLeft + 'px',
                top: newTop + 'px'
            });
        };

        mouseUpHandler = function(e) {
            if (!isDragging) return;

            e.preventDefault();
            isDragging = false;
            $element.removeClass('dragging');
            $element.css('z-index', '');

            PDF_BUILDER_CANVAS.updateElementData($element);
            PDF_BUILDER_CANVAS.saveState();
            PDF_BUILDER_CANVAS.markAsDirty();
        };

        // Attacher les événements
        $element.find('.element-handle').on('mousedown', dragHandler);
        jQuery(document).on('mousemove.drag', mouseMoveHandler);
        jQuery(document).on('mouseup.drag', mouseUpHandler);

        // Stocker les références pour le nettoyage
        $element.data('drag-handlers', {
            dragHandler: dragHandler,
            mouseMoveHandler: mouseMoveHandler,
            mouseUpHandler: mouseUpHandler
        });
    },

    /**
     * Nettoie les événements de drag d'un élément
     */
    cleanupDraggable: function($element) {
        const handlers = $element.data('drag-handlers');
        if (handlers) {
            $element.find('.element-handle').off('mousedown', handlers.dragHandler);
            jQuery(document).off('mousemove.drag', handlers.mouseMoveHandler);
            jQuery(document).off('mouseup.drag', handlers.mouseUpHandler);
            $element.removeData('drag-handlers');
        }
    },

    /**
     * Rend un élément redimensionnable
     */
    makeResizable: function($element) {
        let isResizing = false;
        let startX, startY, startWidth, startHeight;

        $element.find('.resize-handle').on('mousedown', function(e) {
            e.preventDefault();
            e.stopPropagation();

            isResizing = true;
            startX = e.pageX;
            startY = e.pageY;
            startWidth = $element.outerWidth();
            startHeight = $element.outerHeight();

            $element.addClass('resizing');
        });

        jQuery(document).on('mousemove', function(e) {
            if (!isResizing) return;

            const deltaX = e.pageX - startX;
            const deltaY = e.pageY - startY;

            const newWidth = Math.max(50, startWidth + deltaX);
            const newHeight = Math.max(20, startHeight + deltaY);

            $element.css({ width: newWidth, height: newHeight });
        });

        jQuery(document).on('mouseup', function() {
            if (!isResizing) return;

            isResizing = false;
            $element.removeClass('resizing');
            PDF_BUILDER_CANVAS.updateElementData($element);
            PDF_BUILDER_CANVAS.saveState();
        });
    },

    /**
     * Sélectionne un élément
     */
    selectElement: function($element) {
        jQuery('.pdf-element').removeClass('selected');
        $element.addClass('selected');
        this.state.selectedElements.clear();
        this.state.selectedElements.add($element.data('element-id'));
    },

    /**
     * Désélectionne tout
     */
    deselectAll: function() {
        jQuery('.pdf-element').removeClass('selected');
        this.state.selectedElements.clear();
    },

    /**
     * Supprime les éléments sélectionnés
     */
    deleteSelected: function() {
        if (this.state.selectedElements.size === 0) return;

        this.state.selectedElements.forEach(id => {
            const $element = jQuery(`[data-element-id="${id}"]`);
            $element.remove();
            this.cache.elements.delete(id);
        });

        this.state.selectedElements.clear();
        this.saveState();
    },

    /**
     * Duplique un élément
     */
    duplicateElement: function($element) {
        const originalId = $element.data('element-id');
        const originalData = this.cache.elements.get(originalId);

        if (!originalData) return;

        const newId = this.generateId();
        const newData = {
            ...originalData,
            id: newId,
            x: originalData.x + 20,
            y: originalData.y + 20
        };

        const $newElement = this.createElement(newData);
        jQuery('#pdf-elements-container').append($newElement);

        this.makeElementInteractive($newElement);
        this.deselectAll();
        this.selectElement($newElement);
        this.saveState();
    },

    /**
     * Supprime un élément
     */
    deleteElement: function($element) {
        const id = $element.data('element-id');
        $element.fadeOut(200, () => {
            $element.remove();
            this.cache.elements.delete(id);
            this.state.selectedElements.delete(id);
            this.saveState();
        });
    },

    /**
     * Met à jour les données d'un élément
     */
    updateElementData: function($element) {
        const id = $element.data('element-id');
        const data = this.cache.elements.get(id);

        if (data) {
            data.x = parseInt($element.css('left'));
            data.y = parseInt($element.css('top'));
            data.width = $element.outerWidth();
            data.height = $element.outerHeight();
            data.content = $element.find('.element-content').html();
        }
    },

    /**
     * Sauvegarde l'état
     */
    saveState: function() {
        const state = {
            elements: Array.from(this.cache.elements.entries()),
            timestamp: Date.now()
        };

        this.cache.undoStack.push(JSON.stringify(state));

        // Limiter l'historique
        if (this.cache.undoStack.length > 20) {
            this.cache.undoStack.shift();
        }

        this.cache.redoStack = [];
        this.markAsDirty();
    },

    /**
     * Annule la dernière action
     */
    undo: function() {
        if (this.cache.undoStack.length === 0) return;

        const currentState = JSON.stringify({
            elements: Array.from(this.cache.elements.entries()),
            timestamp: Date.now()
        });
        this.cache.redoStack.push(currentState);

        const previousState = JSON.parse(this.cache.undoStack.pop());
        this.restoreState(previousState);
    },

    /**
     * Refait la dernière action
     */
    redo: function() {
        if (this.cache.redoStack.length === 0) return;

        const currentState = JSON.stringify({
            elements: Array.from(this.cache.elements.entries()),
            timestamp: Date.now()
        });
        this.cache.undoStack.push(currentState);

        const nextState = JSON.parse(this.cache.redoStack.pop());
        this.restoreState(nextState);
    },

    /**
     * Restaure un état
     */
    restoreState: function(state) {
        jQuery('#pdf-elements-container').empty();
        this.cache.elements.clear();
        this.state.selectedElements.clear();

        state.elements.forEach(([id, data]) => {
            const $element = this.createElement(data);
            jQuery('#pdf-elements-container').append($element);
            this.makeElementInteractive($element);
        });
    },

    /**
     * Marque comme modifié
     */
    markAsDirty: function() {
        this.state.isDirty = true;
    },

    /**
     * Génère un ID unique
     */
    generateId: function() {
        return 'element_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    },

    /**
     * Récupère le template d'un élément
     */
    getElementTemplate: function(type, category) {
        const templates = {
            text: {
                type: 'text',
                category: 'base',
                width: 200,
                height: 'auto',
                content: 'Nouveau texte',
                zIndex: 1
            },
            heading: {
                type: 'heading',
                category: 'base',
                width: 300,
                height: 'auto',
                content: 'Titre',
                zIndex: 1
            },
            image: {
                type: 'image',
                category: 'media',
                width: 200,
                height: 150,
                zIndex: 1
            },
            table: {
                type: 'table',
                category: 'layout',
                width: 400,
                height: 150,
                rows: 2,
                cols: 3,
                zIndex: 1
            }
        };

        return templates[type] || templates.text;
    },

    /**
     * Zoom functions
     */
    zoomIn: function() {
        this.state.zoom = Math.min(200, this.state.zoom + 25);
        this.updateZoom();
    },

    zoomOut: function() {
        this.state.zoom = Math.max(25, this.state.zoom - 25);
        this.updateZoom();
    },

    resetZoom: function() {
        this.state.zoom = 100;
        this.updateZoom();
    },

    /**
     * Toggle functions
     */
    toggleGrid: function() {
        this.state.showGrid = !this.state.showGrid;
        jQuery('#canvas-grid').toggle(this.state.showGrid);
    },

    toggleMargins: function() {
        this.state.showMargins = !this.state.showMargins;
        jQuery('#canvas-margins').toggle(this.state.showMargins);
    },

    /**
     * Clear canvas
     */
    clearCanvas: function() {
        if (!confirm('Vider le canvas ?')) return;

        jQuery('#pdf-elements-container').empty();
        this.cache.elements.clear();
        this.state.selectedElements.clear();
        this.saveState();
    },

    /**
     * Export canvas data
     */
    exportData: function() {
        const elements = [];
        this.cache.elements.forEach((data, id) => {
            elements.push(data);
        });

        return {
            elements: elements,
            canvasSize: this.state.canvasSize,
            zoom: this.state.zoom,
            settings: PDF_BUILDER_UNIFIED_CONFIG ?
                {
                    pageSize: PDF_BUILDER_UNIFIED_CONFIG.CANVAS.PAGE_SIZE,
                    orientation: PDF_BUILDER_UNIFIED_CONFIG.CANVAS.ORIENTATION
                } : null
        };
    },

    /**
     * Import canvas data
     */
    importData: function(data) {
        if (!data || !data.elements) return;

        // Apply settings if available
        if (data.settings && typeof PDF_BUILDER_UNIFIED_CONFIG !== 'undefined') {
            PDF_BUILDER_UNIFIED_CONFIG.initDimensions(data.settings);
            this.state.canvasSize = {
                width: PDF_BUILDER_UNIFIED_CONFIG.CANVAS.WIDTH,
                height: PDF_BUILDER_UNIFIED_CONFIG.CANVAS.HEIGHT
            };
            this.updateCanvasSize();
        }

        // Clear current canvas
        this.clearCanvas();

        // Add elements
        data.elements.forEach(elementData => {
            const $element = this.createElement(elementData);
            jQuery('#pdf-elements-container').append($element);
            this.makeElementInteractive($element);
        });

        this.saveState();
    },

    // ========================================
    // SYSTÈMES AVANCÉS - VERSION 7.0.0
    // ========================================

    // Système de calques avancés
    layers: {
        list: new Map(),
        activeLayer: 'default',
        zIndexCounter: 1000,

        create: function(name, options = {}) {
            const layer = {
                id: 'layer_' + Date.now(),
                name: name,
                visible: true,
                locked: false,
                opacity: 1,
                blendMode: 'normal',
                elements: new Set(),
                zIndex: this.zIndexCounter++,
                ...options
            };

            this.list.set(layer.id, layer);
            this.renderLayerPanel();
            return layer.id;
        },

        setActive: function(layerId) {
            this.activeLayer = layerId;
            jQuery('.layer-item').removeClass('active');
            jQuery(`[data-layer-id="${layerId}"]`).addClass('active');
        },

        addElement: function(elementId, layerId = this.activeLayer) {
            const layer = this.list.get(layerId);
            if (!layer) return false;

            layer.elements.add(elementId);
            const $element = jQuery(`[data-element-id="${elementId}"]`);
            $element.css('z-index', layer.zIndex);
            return true;
        },

        renderLayerPanel: function() {
            const $panel = jQuery('#layers-panel');
            if (!$panel.length) return;

            $panel.empty();
            this.list.forEach(layer => {
                const $layerItem = jQuery(`
                    <div class="layer-item ${layer.id === this.activeLayer ? 'active' : ''}"
                         data-layer-id="${layer.id}">
                        <div class="layer-controls">
                            <input type="checkbox" ${layer.visible ? 'checked' : ''}
                                   onchange="PDF_BUILDER_CANVAS.layers.toggleVisibility('${layer.id}')">
                            <span class="layer-name">${layer.name}</span>
                            <span class="layer-count">(${layer.elements.size})</span>
                        </div>
                    </div>
                `);
                $panel.append($layerItem);
            });
        },

        toggleVisibility: function(layerId) {
            const layer = this.list.get(layerId);
            if (!layer) return;

            layer.visible = !layer.visible;
            layer.elements.forEach(elementId => {
                const $element = jQuery(`[data-element-id="${elementId}"]`);
                $element.toggle(layer.visible);
            });
        }
    },

    // Système de groupes avancés
    groups: {
        list: new Map(),
        selectedGroups: new Set(),

        create: function(elementIds, name = 'Groupe') {
            const group = {
                id: 'group_' + Date.now(),
                name: name,
                elements: new Set(elementIds),
                bounds: this.calculateBounds(elementIds),
                transform: { x: 0, y: 0, rotation: 0, scale: 1 }
            };

            this.list.set(group.id, group);
            this.renderGroup(group);
            return group.id;
        },

        calculateBounds: function(elementIds) {
            const elements = elementIds.map(id => jQuery(`[data-element-id="${id}"]`));
            if (elements.length === 0) return { left: 0, top: 0, width: 0, height: 0 };

            let minLeft = Infinity, minTop = Infinity, maxRight = 0, maxBottom = 0;

            elements.forEach($el => {
                const offset = $el.offset();
                const width = $el.outerWidth();
                const height = $el.outerHeight();

                minLeft = Math.min(minLeft, offset.left);
                minTop = Math.min(minTop, offset.top);
                maxRight = Math.max(maxRight, offset.left + width);
                maxBottom = Math.max(maxBottom, offset.top + height);
            });

            return {
                left: minLeft,
                top: minTop,
                width: maxRight - minLeft,
                height: maxBottom - minTop
            };
        },

        renderGroup: function(group) {
            const $groupElement = jQuery(`
                <div class="pdf-group" data-group-id="${group.id}"
                     style="position: absolute; left: ${group.bounds.left}px; top: ${group.bounds.top}px;
                            width: ${group.bounds.width}px; height: ${group.bounds.height}px;
                            border: 2px dashed #007cba; background: rgba(0, 124, 186, 0.1);">
                    <div class="group-label">${group.name}</div>
                </div>
            `);

            jQuery('#pdf-canvas').append($groupElement);
            this.makeGroupInteractive($groupElement, group);
        },

        makeGroupInteractive: function($group, group) {
            let isDragging = false, startX, startY, startLeft, startTop;

            $group.on('mousedown', (e) => {
                if (e.which !== 1) return;
                e.stopPropagation();

                isDragging = true;
                startX = e.pageX;
                startY = e.pageY;
                startLeft = parseInt($group.css('left'));
                startTop = parseInt($group.css('top'));

                $group.addClass('dragging');
            });

            jQuery(document).on('mousemove', (e) => {
                if (!isDragging) return;

                const deltaX = e.pageX - startX;
                const deltaY = e.pageY - startY;

                $group.css({
                    left: startLeft + deltaX + 'px',
                    top: startTop + deltaY + 'px'
                });

                // Déplacer tous les éléments du groupe
                group.elements.forEach(elementId => {
                    const $element = jQuery(`[data-element-id="${elementId}"]`);
                    const elementStartLeft = parseInt($element.css('left'));
                    const elementStartTop = parseInt($element.css('top'));

                    $element.css({
                        left: elementStartLeft + deltaX + 'px',
                        top: elementStartTop + deltaY + 'px'
                    });
                });
            });

            jQuery(document).on('mouseup', () => {
                if (isDragging) {
                    isDragging = false;
                    $group.removeClass('dragging');
                    PDF_BUILDER_CANVAS.saveState();
                }
            });
        }
    },

    // Outils de dessin avancés
    drawing: {
        currentTool: 'select',
        tools: new Map(),
        isDrawing: false,
        currentPath: [],

        init: function() {
            this.registerTool('select', {
                name: 'Sélection',
                icon: 'cursor',
                cursor: 'default'
            });

            this.registerTool('rectangle', {
                name: 'Rectangle',
                icon: 'square',
                cursor: 'crosshair',
                draw: this.drawRectangle.bind(this)
            });

            this.registerTool('circle', {
                name: 'Cercle',
                icon: 'circle',
                cursor: 'crosshair',
                draw: this.drawCircle.bind(this)
            });

            this.renderToolbar();
        },

        registerTool: function(id, tool) {
            this.tools.set(id, tool);
        },

        setTool: function(toolId) {
            this.currentTool = toolId;
            const tool = this.tools.get(toolId);

            jQuery('#pdf-canvas').css('cursor', tool.cursor);
            jQuery('.drawing-tool').removeClass('active');
            jQuery(`[data-tool="${toolId}"]`).addClass('active');

            console.log(`🎨 Outil changé: ${tool.name}`);
        },

        renderToolbar: function() {
            const $toolbar = jQuery('#drawing-toolbar');
            if (!$toolbar.length) return;

            $toolbar.empty();
            this.tools.forEach((tool, id) => {
                const $tool = jQuery(`
                    <button class="drawing-tool ${id === this.currentTool ? 'active' : ''}"
                            data-tool="${id}" title="${tool.name}">
                        <i class="dashicons dashicons-${tool.icon}"></i>
                    </button>
                `);

                $tool.on('click', () => this.setTool(id));
                $toolbar.append($tool);
            });
        },

        startDrawing: function(e) {
            const tool = this.tools.get(this.currentTool);
            if (!tool || !tool.draw) return;

            this.isDrawing = true;
            this.currentPath = [{ x: e.pageX, y: e.pageY }];
        },

        continueDrawing: function(e) {
            if (!this.isDrawing) return;

            this.currentPath.push({ x: e.pageX, y: e.pageY });
        },

        finishDrawing: function(e) {
            if (!this.isDrawing) return;

            const tool = this.tools.get(this.currentTool);
            if (tool && tool.draw) {
                tool.draw(this.currentPath);
            }

            this.isDrawing = false;
            this.currentPath = [];
            PDF_BUILDER_CANVAS.saveState();
        },

        drawRectangle: function(path) {
            if (path.length < 2) return;

            const start = path[0];
            const end = path[path.length - 1];
            const width = Math.abs(end.x - start.x);
            const height = Math.abs(end.y - start.y);
            const left = Math.min(start.x, end.x);
            const top = Math.min(start.y, end.y);

            PDF_BUILDER_CANVAS.createElement('shape', {
                type: 'rectangle',
                x: left,
                y: top,
                width: width,
                height: height,
                style: {
                    backgroundColor: '#3b82f6',
                    border: '2px solid #60a5fa'
                }
            });
        },

        drawCircle: function(path) {
            if (path.length < 2) return;

            const start = path[0];
            const end = path[path.length - 1];
            const radius = Math.sqrt(
                Math.pow(end.x - start.x, 2) + Math.pow(end.y - start.y, 2)
            ) / 2;
            const centerX = (start.x + end.x) / 2;
            const centerY = (start.y + end.y) / 2;

            PDF_BUILDER_CANVAS.createElement('shape', {
                type: 'circle',
                x: centerX - radius,
                y: centerY - radius,
                width: radius * 2,
                height: radius * 2,
                style: {
                    borderRadius: '50%',
                    backgroundColor: '#3b82f6',
                    border: '2px solid #60a5fa'
                }
            });
        }
    },

    // IA et assistance intelligente
    ai: {
        suggestions: [],
        patterns: new Map(),

        analyzeLayout: function() {
            const elements = Array.from(PDF_BUILDER_CANVAS.cache.elements.values());
            const suggestions = [];

            if (elements.length > 1) {
                suggestions.push({
                    type: 'alignment',
                    message: 'Aligner les éléments pour un meilleur design',
                    action: () => this.autoAlign()
                });
            }

            this.suggestions = suggestions;
            return suggestions;
        },

        autoAlign: function() {
            const elements = jQuery('.pdf-element');
            if (elements.length < 2) return;

            const positions = elements.map(el => ({
                $el: jQuery(el),
                left: parseInt(jQuery(el).css('left')),
                top: parseInt(jQuery(el).css('top'))
            }));

            const avgLeft = positions.reduce((sum, p) => sum + p.left, 0) / positions.length;

            positions.forEach(({ $el, left }) => {
                if (Math.abs(left - avgLeft) < 50) {
                    $el.css('left', avgLeft + 'px');
                    PDF_BUILDER_CANVAS.updateElementData($el);
                }
            });

            PDF_BUILDER_CANVAS.saveState();
        }
    },

    // Système de collaboration
    collaboration: {
        enabled: false,
        users: new Map(),
        cursors: new Map(),

        enable: function() {
            this.enabled = true;
            console.log('🤝 Collaboration activée');
        },

        broadcast: function(action, data) {
            if (!this.enabled) return;
            console.log('📡 Collaboration:', action, data);
        }
    },

    // Monitoring de performance
    performance: {
        fps: 60,
        frameCount: 0,
        lastTime: 0,

        monitor: function() {
            const now = performance.now();
            this.frameCount++;

            if (now - this.lastTime >= 1000) {
                this.fps = Math.round((this.frameCount * 1000) / (now - this.lastTime));
                this.frameCount = 0;
                this.lastTime = now;

                jQuery('.fps-counter').text(`${this.fps} FPS`);
                if (performance.memory) {
                    const mem = Math.round(performance.memory.usedJSHeapSize / 1024 / 1024);
                    jQuery('.memory-counter').text(`${mem} MB`);
                }
            }
        },

        startMonitoring: function() {
            setInterval(() => this.monitor(), 100);
        }
    },

    /**
     * Nettoie tous les événements et ressources
     */
    destroy: function() {
        console.log('🧹 Nettoyage du canvas PDF Builder...');

        // Nettoyer les événements du canvas
        jQuery('#pdf-canvas').off('mousedown.drawing');
        jQuery(document).off('mousemove.drawing mouseup.drawing');
        jQuery(document).off('dragover drop dragleave', '#pdf-canvas');

        // Nettoyer les événements de drag des éléments
        jQuery('.pdf-element').each((index, element) => {
            this.cleanupDraggable(jQuery(element));
        });

        // Nettoyer les autres événements
        jQuery(document).off('click', '#pdf-canvas');
        jQuery(window).off('resize', this.resizeHandler);

        // Nettoyer le cache
        this.cache.elements.clear();
        this.cache.undoStack.length = 0;
        this.cache.redoStack.length = 0;

        // Réinitialiser l'état
        this.state.initialized = false;
        this.state.selectedElements.clear();

        console.log('✅ Canvas nettoyé');
    }
};

// Exposition globale
window.PDF_BUILDER_CANVAS = PDF_BUILDER_CANVAS;

}