/**
 * PDF Canvas Performance Optimizations
 * Optimisations de performance pour le système Vanilla JS
 */

export class PDFCanvasPerformanceOptimizer {
    constructor(canvasInstance) {
        this.canvasInstance = canvasInstance;
        this.metrics = {
            renderTime: 0,
            frameCount: 0,
            memoryUsage: 0,
            elementCount: 0,
            lastFrameTime: 0
        };

        this.optimizationConfig = {
            enableFrameSkipping: true,
            maxFrameRate: 60,
            enableObjectPooling: true,
            enableDirtyRectRendering: true,
            enableLOD: true, // Level of Detail
            maxCanvasSize: 4096,
            enableWebWorkers: false, // Pour les calculs lourds
            cacheStaticElements: true
        };

        this.objectPool = new Map();
        this.dirtyRegions = [];
        this.staticElementCache = new Map();
        this.frameSkipCounter = 0;

        this.initPerformanceMonitoring();
    }

    /**
     * Initialise le monitoring de performance
     */
    initPerformanceMonitoring() {
        if (typeof performance !== 'undefined' && performance.memory) {
            this.originalMemory = performance.memory.usedJSHeapSize;
        }

        // Surveiller les métriques de performance
        this.monitorInterval = setInterval(() => {
            this.updateMetrics();
        }, 1000);
    }

    /**
     * Met à jour les métriques de performance
     */
    updateMetrics() {
        this.metrics.elementCount = this.canvasInstance.elements.size;
        this.metrics.frameCount++;

        if (typeof performance !== 'undefined' && performance.memory) {
            this.metrics.memoryUsage = performance.memory.usedJSHeapSize - this.originalMemory;
        }

        // Calculer le FPS
        const now = performance.now();
        if (this.metrics.lastFrameTime) {
            const delta = now - this.metrics.lastFrameTime;
            this.metrics.fps = 1000 / delta;
        }
        this.metrics.lastFrameTime = now;
    }

    /**
     * Vérifie si le rendu doit être effectué (pour frame skipping)
     */
    shouldRender() {
        return this.optimizationConfig.enableFrameSkipping;
    }

    /**
     * Optimise le rendu avec skipping de frames
     */
    optimizeRendering() {
        if (!this.optimizationConfig.enableFrameSkipping) {
            this.renderOptimized();
            return;
        }

        this.frameSkipCounter++;
        const targetFrameInterval = 1000 / this.optimizationConfig.maxFrameRate;

        if (this.frameSkipCounter >= targetFrameInterval / 16.67) { // 60 FPS baseline
            this.frameSkipCounter = 0;
            this.renderOptimized();
        }
    }

    /**
     * Rendu optimisé avec dirty rectangles
     */
    renderOptimized() {
        const startTime = performance.now();

        if (this.optimizationConfig.enableDirtyRectRendering && this.dirtyRegions.length > 0) {
            this.renderDirtyRegions();
        } else {
            this.renderNormal();
        }

        this.metrics.renderTime = performance.now() - startTime;
    }

    /**
     * Rendu normal (délégué depuis l'instance principale)
     */
    renderNormal() {
        // Déléguer au rendu normal de l'instance canvas
        this.canvasInstance.renderNormal();
    }

    /**
     * Rend seulement les régions modifiées
     */
    renderDirtyRegions() {
        // Pour l'instant, rerendre tout le canvas
        // TODO: Implémenter le rendu partiel optimisé
        this.renderNormal();
        this.dirtyRegions = [];
    }

    /**
     * Vérifie si un élément est dans une région
     */
    isElementInRegion(element, region) {
        // Safeguard: ensure properties exist
        if (!element || !element.properties) {
            console.warn('isElementInRegion: invalid element or properties', element);
            return false;
        }
        const props = element.properties;
        const x = props.x || 0;
        const y = props.y || 0;
        const w = props.width || 0;
        const h = props.height || 0;
        return !(x + w < region.x ||
                x > region.x + region.width ||
                y + h < region.y ||
                y > region.y + region.height);
    }

    /**
     * Marque une région comme modifiée
     */
    markDirtyRegion(x, y, width, height) {
        if (!this.optimizationConfig.enableDirtyRectRendering) return;

        this.dirtyRegions.push({ x, y, width, height });

        // Fusionner les régions qui se chevauchent
        this.mergeDirtyRegions();
    }

    /**
     * Fusionne les régions modifiées qui se chevauchent
     */
    mergeDirtyRegions() {
        if (this.dirtyRegions.length < 2) return;

        const merged = [];
        const sorted = this.dirtyRegions.sort((a, b) => a.x - b.x);

        let current = sorted[0];

        for (let i = 1; i < sorted.length; i++) {
            const next = sorted[i];

            if (current.x + current.width >= next.x &&
                current.y + current.height >= next.y &&
                current.y <= next.y + next.height) {
                // Fusionner
                current.width = Math.max(current.x + current.width, next.x + next.width) - current.x;
                current.height = Math.max(current.y + current.height, next.y + next.height) - current.y;
            } else {
                merged.push(current);
                current = next;
            }
        }

        merged.push(current);
        this.dirtyRegions = merged;
    }

    /**
     * Cache les éléments statiques
     */
    cacheStaticElement(elementId) {
        if (!this.optimizationConfig.cacheStaticElements) return;

        const element = this.canvasInstance.elements.get(elementId);
        if (!element) return;

        // Créer un canvas temporaire pour l'élément
        const cacheCanvas = document.createElement('canvas');
        const cacheCtx = cacheCanvas.getContext('2d');

        const bounds = this.getElementBounds(element);
        cacheCanvas.width = bounds.width;
        cacheCanvas.height = bounds.height;

        // Sauvegarder le contexte principal
        const mainCtx = this.canvasInstance.ctx;
        this.canvasInstance.ctx = cacheCtx;

        // Déplacer temporairement l'élément à l'origine
    // Safeguard properties
    const originalX = (element.properties && element.properties.x) || 0;
    const originalY = (element.properties && element.properties.y) || 0;
    if (!element.properties) element.properties = {};
    element.properties.x = 0;
    element.properties.y = 0;

        // Rendre l'élément dans le cache
        this.canvasInstance.renderElement(element);

        // Restaurer
        element.properties.x = originalX;
        element.properties.y = originalY;
        this.canvasInstance.ctx = mainCtx;

        this.staticElementCache.set(elementId, {
            canvas: cacheCanvas,
            bounds: bounds,
            lastModified: Date.now()
        });
    }

    /**
     * Récupère les limites d'un élément
     */
    getElementBounds(element) {
        if (!element || !element.properties) {
            console.warn('getElementBounds: invalid element or properties', element);
            return { x: 0, y: 0, width: 100, height: 100 };
        }
        const props = element.properties;
        return {
            x: props.x || 0,
            y: props.y || 0,
            width: props.width || 100,
            height: props.height || 100
        };
    }

    /**
     * Utilise le cache pour rendre un élément statique
     */
    renderCachedElement(elementId, ctx) {
        const cache = this.staticElementCache.get(elementId);
        if (!cache) return false;

        const element = this.canvasInstance.elements.get(elementId);
        if (!element) return false;

        const x = (element.properties && element.properties.x) || 0;
        const y = (element.properties && element.properties.y) || 0;
        ctx.drawImage(cache.canvas, x, y);
        return true;
    }

    /**
     * Pool d'objets pour réutiliser les instances
     */
    getPooledObject(type, constructor) {
        if (!this.optimizationConfig.enableObjectPooling) {
            return new constructor();
        }

        if (!this.objectPool.has(type)) {
            this.objectPool.set(type, []);
        }

        const pool = this.objectPool.get(type);
        if (pool.length > 0) {
            return pool.pop();
        }

        return new constructor();
    }

    /**
     * Retourne un objet au pool
     */
    returnToPool(type, object) {
        if (!this.optimizationConfig.enableObjectPooling) return;

        if (!this.objectPool.has(type)) {
            this.objectPool.set(type, []);
        }

        const pool = this.objectPool.get(type);
        if (pool.length < 50) { // Limite de pool
            // Réinitialiser l'objet si nécessaire
            if (typeof object.reset === 'function') {
                object.reset();
            }
            pool.push(object);
        }
    }

    /**
     * Optimise la taille du canvas
     */
    optimizeCanvasSize() {
        const canvas = this.canvasInstance.canvas;
        const maxSize = this.optimizationConfig.maxCanvasSize;

        if (canvas.width > maxSize || canvas.height > maxSize) {
            // Canvas size exceeds maximum - consider reducing resolution
        }

        // Ajuster le device pixel ratio pour les écrans haute résolution
        const devicePixelRatio = window.devicePixelRatio || 1;
        if (devicePixelRatio > 1 && !canvas.style.width) {
            // Ajuster pour éviter le flou sur les écrans retina
            const ctx = canvas.getContext('2d');
            const actualWidth = canvas.width;
            const actualHeight = canvas.height;

            canvas.width = actualWidth * devicePixelRatio;
            canvas.height = actualHeight * devicePixelRatio;
            canvas.style.width = actualWidth + 'px';
            canvas.style.height = actualHeight + 'px';

            ctx.scale(devicePixelRatio, devicePixelRatio);
        }
    }

    /**
     * Optimise les événements avec throttling
     */
    throttleEvent(eventType, handler, delay = 16) {
        let timeoutId;
        let lastExecTime = 0;

        return (...args) => {
            const currentTime = Date.now();

            if (currentTime - lastExecTime > delay) {
                handler.apply(this, args);
                lastExecTime = currentTime;
            } else {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    handler.apply(this, args);
                    lastExecTime = Date.now();
                }, delay - (currentTime - lastExecTime));
            }
        };
    }

    /**
     * Level of Detail (LOD) pour les éléments distants
     */
    applyLOD(element, distance) {
        if (!this.optimizationConfig.enableLOD) return element;

        const lodLevels = {
            high: 1.0,    // Distance < 100px
            medium: 0.7,  // Distance 100-300px
            low: 0.4      // Distance > 300px
        };

        let lod = 'high';
        if (distance > 300) lod = 'low';
        else if (distance > 100) lod = 'medium';

        // Appliquer le LOD en ajustant la qualité de rendu
        const scale = lodLevels[lod];
        if (scale < 1.0) {
            element.properties.opacity = (element.properties.opacity || 100) * scale;
        }

        return element;
    }

    /**
     * Nettoie le cache des éléments supprimés
     */
    cleanupCache() {
        const currentElementIds = new Set(this.canvasInstance.elements.keys());

        // Supprimer les éléments du cache qui n'existent plus
        for (const [elementId] of this.staticElementCache) {
            if (!currentElementIds.has(elementId)) {
                this.staticElementCache.delete(elementId);
            }
        }

        // Nettoyer les pools d'objets
        for (const [type, pool] of this.objectPool) {
            // Garder seulement les 10 objets les plus récents
            if (pool.length > 10) {
                this.objectPool.set(type, pool.slice(-10));
            }
        }
    }

    /**
     * Obtient les statistiques de performance
     */
    getPerformanceStats() {
        return {
            ...this.metrics,
            cacheSize: this.staticElementCache.size,
            poolSizes: Object.fromEntries(
                Array.from(this.objectPool.entries()).map(([type, pool]) => [type, pool.length])
            ),
            dirtyRegionsCount: this.dirtyRegions.length,
            config: { ...this.optimizationConfig }
        };
    }

    /**
     * Configure les optimisations
     */
    configure(options) {
        this.optimizationConfig = { ...this.optimizationConfig, ...options };
    }

    /**
     * Force un garbage collection (si disponible)
     */
    forceGC() {
        if (typeof window !== 'undefined' && window.gc) {
            window.gc();
        }
    }

    /**
     * Nettoie les ressources
     */
    dispose() {
        if (this.monitorInterval) {
            clearInterval(this.monitorInterval);
        }

        this.objectPool.clear();
        this.staticElementCache.clear();
        this.dirtyRegions = [];

        this.forceGC();
    }
}

export default PDFCanvasPerformanceOptimizer;
