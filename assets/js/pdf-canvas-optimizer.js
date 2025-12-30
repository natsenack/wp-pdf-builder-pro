/**
 * PDF Canvas Performance Optimizer
 * Optimise les performances du canvas pour une meilleure expérience utilisateur
 */

export class PDFCanvasPerformanceOptimizer {
    constructor(canvas) {
        this.canvas = canvas;
        this.metrics = {
            fps: 0,
            frameTime: 0,
            renderTime: 0,
            memoryUsage: 0,
            elementCount: 0
        };

        this.lastFrameTime = performance.now();
        this.frameCount = 0;
        this.fpsUpdateInterval = 1000; // Mise à jour FPS chaque seconde
        this.lastFpsUpdate = performance.now();

        // Paramètres d'optimisation
        this.settings = {
            targetFps: 60,
            maxElements: 1000,
            lazyLoadThreshold: 50,
            memoryLimit: 128 * 1024 * 1024, // 128MB
            enableProfiling: false
        };

        // Cache pour les calculs coûteux
        this.cache = new Map();

        // Gestionnaire de lazy loading
        this.lazyLoader = new LazyLoader(this);

        // Moniteur de performance
        this.performanceMonitor = new PerformanceMonitor(this);

        this.init();
    }

    init() {
        // Démarrer le monitoring
        this.performanceMonitor.start();

        // Configurer les optimisations
        this.setupOptimizations();

        console.log('[PDFCanvas] Performance Optimizer initialized');
    }

    setupOptimizations() {
        // Optimisation du rendu
        this.optimizeRendering();

        // Optimisation mémoire
        this.optimizeMemory();

        // Lazy loading des éléments
        this.setupLazyLoading();
    }

    optimizeRendering() {
        // Utiliser requestAnimationFrame pour un rendu fluide
        this.useRequestAnimationFrame();

        // Optimiser le rendu des éléments hors écran
        this.optimizeOffscreenRendering();

        // Utiliser des layers pour améliorer les performances
        this.setupLayerOptimization();
    }

    optimizeMemory() {
        // Nettoyer le cache régulièrement
        this.setupCacheCleanup();

        // Optimiser la gestion des événements
        this.optimizeEventHandling();

        // Monitorer l'utilisation mémoire
        this.setupMemoryMonitoring();
    }

    setupLazyLoading() {
        // Charger les éléments visibles en priorité
        this.lazyLoader.enable();

        // Précharger les éléments proches
        this.setupPreloading();
    }

    // === MONITORING DES PERFORMANCES ===

    updateMetrics() {
        const now = performance.now();
        this.frameCount++;

        // Calculer le FPS
        if (now - this.lastFpsUpdate >= this.fpsUpdateInterval) {
            this.metrics.fps = Math.round((this.frameCount * 1000) / (now - this.lastFpsUpdate));
            this.frameCount = 0;
            this.lastFpsUpdate = now;

            // Avertir si FPS trop bas
            if (this.metrics.fps < 30) {
                console.warn(`[PDFCanvas] Low FPS detected: ${this.metrics.fps}`);
                this.handleLowPerformance();
            }
        }

        // Mesurer le temps de rendu
        this.metrics.renderTime = now - this.lastFrameTime;
        this.lastFrameTime = now;

        // Compter les éléments
        this.metrics.elementCount = this.canvas.elements.size;

        // Mesurer utilisation mémoire (estimation)
        this.metrics.memoryUsage = this.estimateMemoryUsage();
    }

    estimateMemoryUsage() {
        // Estimation simple basée sur le nombre d'éléments
        const baseMemory = 1024 * 1024; // 1MB base
        const elementMemory = this.metrics.elementCount * 2048; // ~2KB par élément
        return baseMemory + elementMemory;
    }

    handleLowPerformance() {
        // Réduire la qualité du rendu
        this.reduceRenderQuality();

        // Désactiver les animations coûteuses
        this.disableExpensiveAnimations();

        // Activer le lazy loading plus agressif
        this.lazyLoader.aggressiveMode = true;
    }

    // === OPTIMISATIONS DE RENDU ===

    useRequestAnimationFrame() {
        // Remplacer les setInterval par requestAnimationFrame
        if (this.canvas.renderLoop) {
            clearInterval(this.canvas.renderLoop);
        }

        const render = () => {
            this.canvas.render();
            this.updateMetrics();
            requestAnimationFrame(render);
        };

        requestAnimationFrame(render);
    }

    optimizeOffscreenRendering() {
        // Ne rendre que les éléments visibles
        this.canvas.shouldRenderElement = (element) => {
            return this.isElementVisible(element);
        };
    }

    isElementVisible(element) {
        const canvasRect = this.canvas.canvas.getBoundingClientRect();
        const elementBounds = element.getBounds();

        // Vérifier si l'élément intersecte la zone visible
        return !(elementBounds.right < 0 ||
                elementBounds.left > canvasRect.width ||
                elementBounds.bottom < 0 ||
                elementBounds.top > canvasRect.height);
    }

    setupLayerOptimization() {
        // Créer des layers pour différents types d'éléments
        this.layers = {
            background: new OffscreenCanvas(1, 1),
            elements: new OffscreenCanvas(1, 1),
            overlay: new OffscreenCanvas(1, 1)
        };

        this.resizeLayers();
    }

    resizeLayers() {
        const { width, height } = this.canvas.canvas;
        Object.values(this.layers).forEach(layer => {
            if (layer.width !== width || layer.height !== height) {
                layer.width = width;
                layer.height = height;
            }
        });
    }

    // === OPTIMISATIONS MÉMOIRE ===

    setupCacheCleanup() {
        // Nettoyer le cache toutes les 30 secondes
        setInterval(() => {
            this.cleanupCache();
        }, 30000);
    }

    cleanupCache() {
        const maxAge = 5 * 60 * 1000; // 5 minutes
        const now = Date.now();

        for (const [key, entry] of this.cache.entries()) {
            if (now - entry.timestamp > maxAge) {
                this.cache.delete(key);
            }
        }

        // Forcer le garbage collector si disponible
        if (window.gc) {
            window.gc();
        }
    }

    optimizeEventHandling() {
        // Utiliser l'event delegation
        this.setupEventDelegation();

        // Debouncer pour les événements fréquents
        this.setupEventDebouncing();
    }

    setupEventDelegation() {
        // Attacher les événements au container plutôt qu'aux éléments individuels
        const container = this.canvas.canvas.parentElement;
        if (container) {
            container.addEventListener('click', (e) => {
                this.handleDelegatedEvent('click', e);
            });

            container.addEventListener('mousemove', (e) => {
                this.handleDelegatedEvent('mousemove', e);
            });
        }
    }

    handleDelegatedEvent(type, event) {
        // Trouver l'élément cible et déclencher l'événement approprié
        const element = this.canvas.findElementAt(event.offsetX, event.offsetY);
        if (element) {
            this.canvas.eventManager.triggerElementEvent(type, element, event);
        }
    }

    setupEventDebouncing() {
        this.debouncedEvents = new Map();

        // Debouncer pour les événements de zoom et scroll
        this.debounce('zoom', () => this.handleZoomEvent(), 16); // ~60fps
        this.debounce('scroll', () => this.handleScrollEvent(), 16);
    }

    debounce(eventType, callback, delay) {
        let timeoutId;
        this.debouncedEvents.set(eventType, () => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(callback, delay);
        });
    }

    setupMemoryMonitoring() {
        // Monitorer l'utilisation mémoire
        if ('memory' in performance) {
            setInterval(() => {
                const memInfo = performance.memory;
                console.log(`[PDFCanvas] Memory: ${Math.round(memInfo.usedJSHeapSize / 1024 / 1024)}MB used`);

                if (memInfo.usedJSHeapSize > this.settings.memoryLimit) {
                    console.warn('[PDFCanvas] High memory usage detected');
                    this.handleHighMemoryUsage();
                }
            }, 10000); // Toutes les 10 secondes
        }
    }

    handleHighMemoryUsage() {
        // Forcer le nettoyage
        this.cleanupCache();

        // Réduire le nombre d'éléments en cache
        this.reduceCacheSize();

        // Demander à l'utilisateur de sauvegarder
        this.canvas.emit('memory-warning');
    }

    // === LAZY LOADING ===

    setupPreloading() {
        // Précharger les éléments proches de la zone visible
        this.preloadDistance = 200; // pixels

        this.canvas.on('viewport-change', () => {
            this.preloadNearbyElements();
        });
    }

    preloadNearbyElements() {
        const viewport = this.canvas.getViewportBounds();

        this.canvas.elements.forEach(element => {
            if (this.isElementNearViewport(element, viewport)) {
                this.lazyLoader.loadElement(element);
            }
        });
    }

    isElementNearViewport(element, viewport) {
        const bounds = element.getBounds();
        const distance = Math.max(
            Math.abs(bounds.left - viewport.left),
            Math.abs(bounds.right - viewport.right),
            Math.abs(bounds.top - viewport.top),
            Math.abs(bounds.bottom - viewport.bottom)
        );

        return distance <= this.preloadDistance;
    }

    // === UTILITAIRES ===

    reduceRenderQuality() {
        // Réduire la résolution du canvas temporairement
        const originalWidth = this.canvas.canvas.width;
        const originalHeight = this.canvas.canvas.height;

        this.canvas.canvas.width = originalWidth * 0.5;
        this.canvas.canvas.height = originalHeight * 0.5;

        // Restaurer après 5 secondes de bonnes performances
        setTimeout(() => {
            this.canvas.canvas.width = originalWidth;
            this.canvas.canvas.height = originalHeight;
        }, 5000);
    }

    disableExpensiveAnimations() {
        // Désactiver les animations coûteuses
        this.canvas.settings.animations = false;
        this.canvas.settings.transitions = false;
    }

    reduceCacheSize() {
        // Garder seulement les éléments les plus récents
        const maxCacheSize = 50;
        if (this.cache.size > maxCacheSize) {
            const entries = Array.from(this.cache.entries());
            entries.sort((a, b) => b[1].timestamp - a[1].timestamp);

            // Supprimer les entrées les plus anciennes
            const toDelete = entries.slice(maxCacheSize);
            toDelete.forEach(([key]) => this.cache.delete(key));
        }
    }

    // === API PUBLIQUE ===

    getMetrics() {
        return { ...this.metrics };
    }

    enableProfiling() {
        this.settings.enableProfiling = true;
        console.log('[PDFCanvas] Performance profiling enabled');
    }

    disableProfiling() {
        this.settings.enableProfiling = false;
        console.log('[PDFCanvas] Performance profiling disabled');
    }

    destroy() {
        this.performanceMonitor.stop();
        this.lazyLoader.disable();
        this.cache.clear();
    }
}

// === CLASSES UTILITAIRES ===

class LazyLoader {
    constructor(optimizer) {
        this.optimizer = optimizer;
        this.enabled = false;
        this.aggressiveMode = false;
        this.loadedElements = new Set();
    }

    enable() {
        this.enabled = true;
        console.log('[LazyLoader] Enabled');
    }

    disable() {
        this.enabled = false;
        console.log('[LazyLoader] Disabled');
    }

    loadElement(element) {
        if (!this.enabled || this.loadedElements.has(element.id)) {
            return;
        }

        // Simuler le chargement lazy
        if (this.aggressiveMode) {
            // Mode agressif : charger immédiatement
            this.doLoadElement(element);
        } else {
            // Mode normal : charger avec un délai
            setTimeout(() => {
                this.doLoadElement(element);
            }, Math.random() * 100); // Délai aléatoire pour éviter les pics
        }
    }

    doLoadElement(element) {
        // Marquer comme chargé
        this.loadedElements.add(element.id);

        // Notifer le canvas que l'élément est prêt
        this.optimizer.canvas.emit('element-loaded', element);
    }
}

class PerformanceMonitor {
    constructor(optimizer) {
        this.optimizer = optimizer;
        this.intervalId = null;
        this.samples = [];
        this.maxSamples = 100;
    }

    start() {
        this.intervalId = setInterval(() => {
            this.collectSample();
        }, 1000); // Échantillon chaque seconde
    }

    stop() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    }

    collectSample() {
        const sample = {
            timestamp: Date.now(),
            fps: this.optimizer.metrics.fps,
            renderTime: this.optimizer.metrics.renderTime,
            memoryUsage: this.optimizer.metrics.memoryUsage,
            elementCount: this.optimizer.metrics.elementCount
        };

        this.samples.push(sample);

        // Garder seulement les échantillons récents
        if (this.samples.length > this.maxSamples) {
            this.samples.shift();
        }

        // Analyser les tendances
        this.analyzeTrends();
    }

    analyzeTrends() {
        if (this.samples.length < 10) return;

        const recent = this.samples.slice(-10);
        const avgFps = recent.reduce((sum, s) => sum + s.fps, 0) / recent.length;
        const avgRenderTime = recent.reduce((sum, s) => sum + s.renderTime, 0) / recent.length;

        // Détecter les problèmes de performance
        if (avgFps < 30) {
            console.warn(`[PerformanceMonitor] Average FPS too low: ${avgFps.toFixed(1)}`);
        }

        if (avgRenderTime > 33) { // > 30fps
            console.warn(`[PerformanceMonitor] Average render time too high: ${avgRenderTime.toFixed(1)}ms`);
        }
    }

    getStats() {
        if (this.samples.length === 0) return null;

        const recent = this.samples.slice(-10);
        return {
            avgFps: recent.reduce((sum, s) => sum + s.fps, 0) / recent.length,
            avgRenderTime: recent.reduce((sum, s) => sum + s.renderTime, 0) / recent.length,
            minFps: Math.min(...recent.map(s => s.fps)),
            maxFps: Math.max(...recent.map(s => s.fps)),
            sampleCount: this.samples.length
        };
    }
}