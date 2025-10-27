/**
 * PDF Canvas Layers Manager - Gestionnaire de calques
 * Organisation des éléments en calques avec gestion de l'ordre et de la visibilité
 */

export class PDFCanvasLayersManager {
    constructor(canvasInstance) {
        this.canvasInstance = canvasInstance;
        this.layers = new Map(); // Calques par ID
        this.layerOrder = []; // Ordre des calques (IDs)
        this.activeLayerId = null;
        this.layerGroups = new Map(); // Groupes de calques

        // Configuration
        this.config = {
            maxLayers: 100,
            defaultLayerName: 'Layer',
            autoCreateLayers: true,
            showLayerBounds: false,
            enableLayerLocking: true,
            enableLayerVisibility: true,
            snapToLayerBounds: false
        };

        // Créer le calque par défaut
        this.createLayer('default', {
            name: 'Default Layer',
            visible: true,
            locked: false,
            opacity: 100,
            blendMode: 'normal'
        });
    }

    /**
     * Crée un nouveau calque
     */
    createLayer(layerId, options = {}) {
        if (this.layers.size >= this.config.maxLayers) {
            throw new Error(`Maximum number of layers (${this.config.maxLayers}) reached`);
        }

        if (this.layers.has(layerId)) {
            throw new Error(`Layer with id "${layerId}" already exists`);
        }

        const layer = {
            id: layerId,
            name: options.name || `${this.config.defaultLayerName} ${this.layers.size + 1}`,
            visible: options.visible !== false,
            locked: options.locked || false,
            opacity: options.opacity || 100,
            blendMode: options.blendMode || 'normal',
            elements: new Set(), // IDs des éléments dans ce calque
            bounds: null, // Limites du calque
            createdAt: Date.now(),
            updatedAt: Date.now(),
            metadata: options.metadata || {}
        };

        this.layers.set(layerId, layer);
        this.layerOrder.push(layerId);

        // Définir comme calque actif si c'est le premier
        if (!this.activeLayerId) {
            this.activeLayerId = layerId;
        }

        this.emitLayerEvent('layer-created', layer);
        return layer;
    }

    /**
     * Supprime un calque
     */
    deleteLayer(layerId) {
        const layer = this.layers.get(layerId);
        if (!layer) return false;

        // Ne pas supprimer le dernier calque
        if (this.layers.size === 1) {
            throw new Error('Cannot delete the last remaining layer');
        }

        // Déplacer les éléments vers le calque par défaut ou actif
        const targetLayerId = this.activeLayerId !== layerId ? this.activeLayerId : 'default';
        const targetLayer = this.layers.get(targetLayerId);

        if (targetLayer) {
            layer.elements.forEach(elementId => {
                this.moveElementToLayer(elementId, targetLayerId);
            });
        }

        // Supprimer le calque
        this.layers.delete(layerId);
        this.layerOrder = this.layerOrder.filter(id => id !== layerId);

        // Changer de calque actif si nécessaire
        if (this.activeLayerId === layerId) {
            this.activeLayerId = this.layerOrder[0] || null;
        }

        this.emitLayerEvent('layer-deleted', { id: layerId });
        this.canvasInstance.render();

        return true;
    }

    /**
     * Obtient un calque par son ID
     */
    getLayer(layerId) {
        return this.layers.get(layerId) || null;
    }

    /**
     * Obtient tous les calques
     */
    getAllLayers() {
        return Array.from(this.layers.values());
    }

    /**
     * Obtient les calques dans l'ordre d'affichage
     */
    getLayersInOrder() {
        return this.layerOrder.map(id => this.layers.get(id)).filter(layer => layer);
    }

    /**
     * Définit le calque actif
     */
    setActiveLayer(layerId) {
        if (!this.layers.has(layerId)) {
            throw new Error(`Layer "${layerId}" does not exist`);
        }

        const previousActive = this.activeLayerId;
        this.activeLayerId = layerId;

        this.emitLayerEvent('active-layer-changed', {
            previous: previousActive,
            current: layerId
        });

        return true;
    }

    /**
     * Obtient le calque actif
     */
    getActiveLayer() {
        return this.layers.get(this.activeLayerId) || null;
    }

    /**
     * Met à jour les propriétés d'un calque
     */
    updateLayer(layerId, properties) {
        const layer = this.layers.get(layerId);
        if (!layer) return false;

        const updatedProperties = {};
        for (const [key, value] of Object.entries(properties)) {
            if (layer.hasOwnProperty(key)) {
                updatedProperties[key] = layer[key];
                layer[key] = value;
            }
        }

        layer.updatedAt = Date.now();

        this.emitLayerEvent('layer-updated', {
            id: layerId,
            changes: updatedProperties,
            layer: { ...layer }
        });

        this.canvasInstance.render();
        return true;
    }

    /**
     * Ajoute un élément à un calque
     */
    addElementToLayer(elementId, layerId) {
        const layer = this.layers.get(layerId);
        if (!layer) return false;

        // Retirer de l'ancien calque
        this.removeElementFromLayer(elementId);

        // Ajouter au nouveau calque
        layer.elements.add(elementId);
        this.updateLayerBounds(layerId);

        this.emitLayerEvent('element-added-to-layer', {
            elementId,
            layerId
        });

        return true;
    }

    /**
     * Retire un élément d'un calque
     */
    removeElementFromLayer(elementId) {
        for (const layer of this.layers.values()) {
            if (layer.elements.has(elementId)) {
                layer.elements.delete(elementId);
                this.updateLayerBounds(layer.id);

                this.emitLayerEvent('element-removed-from-layer', {
                    elementId,
                    layerId: layer.id
                });

                return true;
            }
        }
        return false;
    }

    /**
     * Déplace un élément vers un autre calque
     */
    moveElementToLayer(elementId, targetLayerId) {
        return this.addElementToLayer(elementId, targetLayerId);
    }

    /**
     * Obtient le calque d'un élément
     */
    getElementLayer(elementId) {
        for (const [layerId, layer] of this.layers) {
            if (layer.elements.has(elementId)) {
                return layer;
            }
        }
        return null;
    }

    /**
     * Obtient tous les éléments d'un calque
     */
    getLayerElements(layerId) {
        const layer = this.layers.get(layerId);
        if (!layer) return [];

        const elements = [];
        for (const elementId of layer.elements) {
            const element = this.canvasInstance.elements.get(elementId);
            if (element) {
                elements.push(element);
            }
        }
        return elements;
    }

    /**
     * Change l'ordre des calques
     */
    reorderLayers(newOrder) {
        // Valider le nouvel ordre
        const currentIds = new Set(this.layerOrder);
        const newIds = new Set(newOrder);

        if (currentIds.size !== newIds.size || !newOrder.every(id => currentIds.has(id))) {
            throw new Error('Invalid layer order: must contain all existing layer IDs');
        }

        this.layerOrder = [...newOrder];

        this.emitLayerEvent('layers-reordered', {
            newOrder: [...this.layerOrder]
        });

        this.canvasInstance.render();
    }

    /**
     * Déplace un calque vers le haut
     */
    moveLayerUp(layerId) {
        const index = this.layerOrder.indexOf(layerId);
        if (index > 0) {
            [this.layerOrder[index], this.layerOrder[index - 1]] =
            [this.layerOrder[index - 1], this.layerOrder[index]];
            this.reorderLayers(this.layerOrder);
        }
    }

    /**
     * Déplace un calque vers le bas
     */
    moveLayerDown(layerId) {
        const index = this.layerOrder.indexOf(layerId);
        if (index < this.layerOrder.length - 1) {
            [this.layerOrder[index], this.layerOrder[index + 1]] =
            [this.layerOrder[index + 1], this.layerOrder[index]];
            this.reorderLayers(this.layerOrder);
        }
    }

    /**
     * Met un calque au premier plan
     */
    bringLayerToFront(layerId) {
        const index = this.layerOrder.indexOf(layerId);
        if (index !== -1 && index < this.layerOrder.length - 1) {
            this.layerOrder.splice(index, 1);
            this.layerOrder.push(layerId);
            this.reorderLayers(this.layerOrder);
        }
    }

    /**
     * Met un calque à l'arrière-plan
     */
    sendLayerToBack(layerId) {
        const index = this.layerOrder.indexOf(layerId);
        if (index > 0) {
            this.layerOrder.splice(index, 1);
            this.layerOrder.unshift(layerId);
            this.reorderLayers(this.layerOrder);
        }
    }

    /**
     * Fusionne deux calques
     */
    mergeLayers(sourceLayerId, targetLayerId) {
        const sourceLayer = this.layers.get(sourceLayerId);
        const targetLayer = this.layers.get(targetLayerId);

        if (!sourceLayer || !targetLayer) return false;

        // Déplacer tous les éléments
        sourceLayer.elements.forEach(elementId => {
            this.moveElementToLayer(elementId, targetLayerId);
        });

        // Supprimer le calque source
        return this.deleteLayer(sourceLayerId);
    }

    /**
     * Duplique un calque
     */
    duplicateLayer(layerId, newName = null) {
        const sourceLayer = this.layers.get(layerId);
        if (!sourceLayer) return null;

        const newLayerId = `layer_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        const duplicatedLayer = this.createLayer(newLayerId, {
            name: newName || `${sourceLayer.name} Copy`,
            visible: sourceLayer.visible,
            locked: sourceLayer.locked,
            opacity: sourceLayer.opacity,
            blendMode: sourceLayer.blendMode,
            metadata: { ...sourceLayer.metadata }
        });

        // Dupliquer les éléments
        sourceLayer.elements.forEach(elementId => {
            const element = this.canvasInstance.elements.get(elementId);
            if (element) {
                const newElementId = this.canvasInstance.addElement(element.type, {
                    ...element.properties,
                    x: element.properties.x + 20, // Offset léger
                    y: element.properties.y + 20
                });
                this.addElementToLayer(newElementId, newLayerId);
            }
        });

        return duplicatedLayer;
    }

    /**
     * Crée un groupe de calques
     */
    createLayerGroup(groupId, layerIds, options = {}) {
        if (this.layerGroups.has(groupId)) {
            throw new Error(`Layer group "${groupId}" already exists`);
        }

        // Valider que tous les calques existent
        const invalidLayers = layerIds.filter(id => !this.layers.has(id));
        if (invalidLayers.length > 0) {
            throw new Error(`Invalid layer IDs: ${invalidLayers.join(', ')}`);
        }

        const group = {
            id: groupId,
            name: options.name || `Group ${groupId}`,
            layerIds: [...layerIds],
            visible: options.visible !== false,
            locked: options.locked || false,
            opacity: options.opacity || 100,
            createdAt: Date.now(),
            metadata: options.metadata || {}
        };

        this.layerGroups.set(groupId, group);

        this.emitLayerEvent('layer-group-created', group);
        return group;
    }

    /**
     * Supprime un groupe de calques
     */
    deleteLayerGroup(groupId) {
        if (!this.layerGroups.has(groupId)) return false;

        this.layerGroups.delete(groupId);

        this.emitLayerEvent('layer-group-deleted', { id: groupId });
        return true;
    }

    /**
     * Met à jour les limites d'un calque
     */
    updateLayerBounds(layerId) {
        const layer = this.layers.get(layerId);
        if (!layer) return;

        const elements = this.getLayerElements(layerId);
        if (elements.length === 0) {
            layer.bounds = null;
            return;
        }

        let minX = Infinity, minY = Infinity;
        let maxX = -Infinity, maxY = -Infinity;

        elements.forEach(element => {
            const props = element.properties;
            minX = Math.min(minX, props.x);
            minY = Math.min(minY, props.y);
            maxX = Math.max(maxX, props.x + props.width);
            maxY = Math.max(maxY, props.y + props.height);
        });

        layer.bounds = {
            x: minX,
            y: minY,
            width: maxX - minX,
            height: maxY - maxY
        };
    }

    /**
     * Met à jour les limites de tous les calques
     */
    updateAllLayerBounds() {
        for (const layerId of this.layers.keys()) {
            this.updateLayerBounds(layerId);
        }
    }

    /**
     * Rend les calques
     */
    render(ctx) {
        // Rendre dans l'ordre des calques (du bas vers le haut)
        for (const layer of this.getLayersInOrder()) {
            if (!layer.visible) continue;

            ctx.save();

            // Appliquer l'opacité du calque
            if (layer.opacity < 100) {
                ctx.globalAlpha = layer.opacity / 100;
            }

            // Appliquer le mode de fusion
            if (layer.blendMode !== 'normal') {
                ctx.globalCompositeOperation = layer.blendMode;
            }

            // Rendre les éléments du calque
            this.renderLayerElements(ctx, layer);

            // Rendre les limites du calque si activé
            if (this.config.showLayerBounds && layer.bounds) {
                this.renderLayerBounds(ctx, layer);
            }

            ctx.restore();
        }
    }

    /**
     * Rend les éléments d'un calque
     */
    renderLayerElements(ctx, layer) {
        for (const elementId of layer.elements) {
            const element = this.canvasInstance.elements.get(elementId);
            if (element) {
                this.canvasInstance.renderElement(element);
            }
        }
    }

    /**
     * Rend les limites d'un calque
     */
    renderLayerBounds(ctx, layer) {
        if (!layer.bounds) return;

        ctx.strokeStyle = '#007bff';
        ctx.lineWidth = 1;
        ctx.setLineDash([5, 5]);
        ctx.strokeRect(
            layer.bounds.x,
            layer.bounds.y,
            layer.bounds.width,
            layer.bounds.height
        );
        ctx.setLineDash([]);
    }

    /**
     * Vérifie si un calque est visible
     */
    isLayerVisible(layerId) {
        const layer = this.layers.get(layerId);
        return layer ? layer.visible : false;
    }

    /**
     * Vérifie si un calque est verrouillé
     */
    isLayerLocked(layerId) {
        const layer = this.layers.get(layerId);
        return layer ? layer.locked : false;
    }

    /**
     * Obtient les statistiques des calques
     */
    getLayerStats() {
        const stats = {
            totalLayers: this.layers.size,
            visibleLayers: 0,
            lockedLayers: 0,
            totalElements: 0,
            layerElementCounts: {},
            activeLayer: this.activeLayerId,
            layerGroups: this.layerGroups.size
        };

        for (const [layerId, layer] of this.layers) {
            if (layer.visible) stats.visibleLayers++;
            if (layer.locked) stats.lockedLayers++;

            const elementCount = layer.elements.size;
            stats.totalElements += elementCount;
            stats.layerElementCounts[layerId] = elementCount;
        }

        return stats;
    }

    /**
     * Exporte la configuration des calques
     */
    exportLayers() {
        return {
            layers: Array.from(this.layers.entries()),
            layerOrder: [...this.layerOrder],
            activeLayerId: this.activeLayerId,
            layerGroups: Array.from(this.layerGroups.entries()),
            config: { ...this.config }
        };
    }

    /**
     * Importe une configuration de calques
     */
    importLayers(data) {
        try {
            // Vider les calques existants
            this.layers.clear();
            this.layerOrder = [];
            this.layerGroups.clear();

            // Importer les calques
            data.layers.forEach(([layerId, layer]) => {
                this.layers.set(layerId, { ...layer, elements: new Set(layer.elements) });
            });

            this.layerOrder = [...data.layerOrder];
            this.activeLayerId = data.activeLayerId;

            // Importer les groupes
            data.layerGroups.forEach(([groupId, group]) => {
                this.layerGroups.set(groupId, { ...group });
            });

            // Importer la configuration
            if (data.config) {
                this.config = { ...this.config, ...data.config };
            }

            this.emitLayerEvent('layers-imported', data);
            return true;
        } catch (error) {
            
            return false;
        }
    }

    /**
     * Émet un événement de calque
     */
    emitLayerEvent(eventType, data) {
        // Ne pas émettre d'événements si le canvas n'est pas encore initialisé
        if (!this.canvasInstance.canvas) return;

        const event = new CustomEvent(`layer-${eventType}`, {
            detail: data,
            bubbles: true
        });

        this.canvasInstance.canvas.dispatchEvent(event);
    }

    /**
     * Configure le gestionnaire de calques
     */
    configure(options) {
        this.config = { ...this.config, ...options };
    }

    /**
     * Nettoie les ressources
     */
    dispose() {
        this.layers.clear();
        this.layerOrder = [];
        this.layerGroups.clear();
        this.activeLayerId = null;
    }
}

export default PDFCanvasLayersManager;
