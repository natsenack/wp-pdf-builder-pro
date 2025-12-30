/**
 * PDF Builder Pro - Asset Performance Manager
 * Gestionnaire de performance pour les assets compressés et optimisés
 *
 * @version 1.0.0
 */

(function(window, document) {
    'use strict';

    // ============================================================================
    // GESTIONNAIRE DE PERFORMANCE DES ASSETS
    // ============================================================================

    class AssetPerformanceManager {

        constructor() {
            this.assets = new Map();
            this.performanceData = new Map();
            this.observers = new Set();
            this.initialized = false;

            this.init();
        }

        /**
         * Initialise le gestionnaire
         */
        init() {
            if (this.initialized) return;

            this.setupPerformanceObserver();
            this.setupResourceObserver();
            this.setupAssetTracking();

            this.initialized = true;

            // Émettre un événement d'initialisation
            this.emit('initialized', {
                timestamp: Date.now(),
                assets: Array.from(this.assets.keys())
            });
        }

        /**
         * Configure l'observateur de performance
         */
        setupPerformanceObserver() {
            if (!window.PerformanceObserver) return;

            try {
                // Observer les métriques de navigation
                const navObserver = new PerformanceObserver((list) => {
                    const entries = list.getEntries();
                    entries.forEach(entry => {
                        if (entry.entryType === 'navigation') {
                            this.trackNavigationPerformance(entry);
                        }
                    });
                });

                navObserver.observe({ entryTypes: ['navigation'] });

                // Observer les métriques de paint
                const paintObserver = new PerformanceObserver((list) => {
                    const entries = list.getEntries();
                    entries.forEach(entry => {
                        this.trackPaintMetrics(entry);
                    });
                });

                paintObserver.observe({ entryTypes: ['paint'] });

            } catch (error) {
                console.warn('Performance Observer non supporté:', error);
            }
        }

        /**
         * Configure l'observateur de ressources
         */
        setupResourceObserver() {
            if (!window.PerformanceObserver) return;

            try {
                const resourceObserver = new PerformanceObserver((list) => {
                    const entries = list.getEntries();
                    entries.forEach(entry => {
                        if (this.isPluginAsset(entry.name)) {
                            this.trackAssetPerformance(entry);
                        }
                    });
                });

                resourceObserver.observe({ entryTypes: ['resource'] });

            } catch (error) {
                console.warn('Resource Observer non supporté:', error);
            }
        }

        /**
         * Configure le suivi des assets
         */
        setupAssetTracking() {
            // Intercepter les chargements d'assets
            this.interceptAssetLoading();

            // Surveiller les erreurs de chargement
            this.setupErrorTracking();
        }

        /**
         * Intercepte les chargements d'assets
         */
        interceptAssetLoading() {
            // Sauvegarder les méthodes originales
            const originalCreateElement = document.createElement;
            const originalSetAttribute = Element.prototype.setAttribute;

            // Intercepter la création d'éléments script et link
            document.createElement = function(tagName) {
                const element = originalCreateElement.call(this, tagName);

                if (tagName === 'script' || tagName === 'link') {
                    const originalOnload = element.onload;
                    const originalOnerror = element.onerror;

                    element.onload = function() {
                        if (element.src || element.href) {
                            window.assetPerformanceManager.trackAssetLoad(element);
                        }
                        if (originalOnload) originalOnload.call(this);
                    };

                    element.onerror = function() {
                        if (element.src || element.href) {
                            window.assetPerformanceManager.trackAssetError(element);
                        }
                        if (originalOnerror) originalOnerror.call(this);
                    };
                }

                return element;
            };

            // Intercepter les changements d'attributs src/href
            Element.prototype.setAttribute = function(name, value) {
                if ((name === 'src' || name === 'href') && (this.tagName === 'SCRIPT' || this.tagName === 'LINK')) {
                    window.assetPerformanceManager.registerAsset(this, value);
                }
                return originalSetAttribute.call(this, name, value);
            };
        }

        /**
         * Configure le suivi des erreurs
         */
        setupErrorTracking() {
            window.addEventListener('error', (event) => {
                if (event.target && (event.target.tagName === 'SCRIPT' || event.target.tagName === 'LINK')) {
                    this.trackAssetError(event.target);
                }
            });

            window.addEventListener('unhandledrejection', (event) => {
                console.warn('Erreur non gérée dans les assets:', event.reason);
            });
        }

        /**
         * Vérifie si l'asset appartient au plugin
         */
        isPluginAsset(url) {
            return url && url.includes('pdf-builder');
        }

        /**
         * Enregistre un asset
         */
        registerAsset(element, url) {
            if (!url || !this.isPluginAsset(url)) return;

            const assetId = this.generateAssetId(url);
            const assetInfo = {
                id: assetId,
                url: url,
                element: element,
                type: element.tagName.toLowerCase(),
                registeredAt: Date.now(),
                loaded: false,
                compressed: url.includes('.min.'),
                cached: false
            };

            this.assets.set(assetId, assetInfo);

            this.emit('assetRegistered', assetInfo);
        }

        /**
         * Suit le chargement d'un asset
         */
        trackAssetLoad(element) {
            const url = element.src || element.href;
            if (!url) return;

            const assetId = this.generateAssetId(url);
            const asset = this.assets.get(assetId);

            if (asset) {
                asset.loaded = true;
                asset.loadTime = Date.now() - asset.registeredAt;

                // Détecter si c'est un asset compressé
                asset.compressed = url.includes('.min.');
                asset.cached = this.isFromCache(url);

                this.performanceData.set(assetId, {
                    loadTime: asset.loadTime,
                    size: this.estimateAssetSize(asset),
                    compressed: asset.compressed,
                    cached: asset.cached
                });

                this.emit('assetLoaded', asset);
            }
        }

        /**
         * Suit les erreurs de chargement d'assets
         */
        trackAssetError(element) {
            const url = element.src || element.href;
            if (!url) return;

            const assetId = this.generateAssetId(url);
            const asset = this.assets.get(assetId);

            if (asset) {
                asset.error = true;
                asset.errorTime = Date.now();

                this.emit('assetError', asset);
            }
        }

        /**
         * Suit les métriques de navigation
         */
        trackNavigationPerformance(entry) {
            const metrics = {
                domContentLoaded: entry.domContentLoadedEventEnd - entry.domContentLoadedEventStart,
                loadComplete: entry.loadEventEnd - entry.loadEventStart,
                totalTime: entry.loadEventEnd - entry.fetchStart
            };

            this.emit('navigationMetrics', metrics);
        }

        /**
         * Suit les métriques de paint
         */
        trackPaintMetrics(entry) {
            const metrics = {
                type: entry.name,
                startTime: entry.startTime
            };

            this.emit('paintMetrics', metrics);
        }

        /**
         * Suit les performances des ressources
         */
        trackAssetPerformance(entry) {
            const assetId = this.generateAssetId(entry.name);
            const performance = {
                url: entry.name,
                loadTime: entry.responseEnd - entry.requestStart,
                size: entry.transferSize || 0,
                compressed: entry.decodedBodySize < entry.transferSize,
                cached: entry.transferSize === 0
            };

            this.performanceData.set(assetId, performance);
            this.emit('resourcePerformance', performance);
        }

        /**
         * Génère un ID unique pour un asset
         */
        generateAssetId(url) {
            return btoa(url).replace(/[^a-zA-Z0-9]/g, '').substr(0, 16);
        }

        /**
         * Estime la taille d'un asset
         */
        estimateAssetSize(asset) {
            // Estimation basée sur le type et la compression
            const baseSizes = {
                script: 50000,  // 50KB moyenne pour JS
                link: 30000     // 30KB moyenne pour CSS
            };

            let size = baseSizes[asset.type] || 20000;

            if (asset.compressed) {
                size *= 0.7; // Réduction de 30% par compression
            }

            return size;
        }

        /**
         * Vérifie si l'asset vient du cache
         */
        isFromCache(url) {
            // Vérifier les headers de cache ou l'URL
            return url.includes('cache') || url.includes('.min.');
        }

        /**
         * Ajoute un observateur d'événements
         */
        addObserver(callback) {
            this.observers.add(callback);
        }

        /**
         * Supprime un observateur
         */
        removeObserver(callback) {
            this.observers.delete(callback);
        }

        /**
         * Émet un événement
         */
        emit(eventType, data) {
            const event = {
                type: eventType,
                data: data,
                timestamp: Date.now()
            };

            this.observers.forEach(callback => {
                try {
                    callback(event);
                } catch (error) {
                    console.error('Erreur dans l\'observateur:', error);
                }
            });

            // Émettre aussi un événement DOM personnalisé
            const customEvent = new CustomEvent('pdfAssetPerformance', {
                detail: event
            });
            document.dispatchEvent(customEvent);
        }

        /**
         * Obtient les statistiques de performance
         */
        getPerformanceStats() {
            const assets = Array.from(this.assets.values());
            const performance = Array.from(this.performanceData.values());

            return {
                totalAssets: assets.length,
                loadedAssets: assets.filter(a => a.loaded).length,
                failedAssets: assets.filter(a => a.error).length,
                compressedAssets: assets.filter(a => a.compressed).length,
                cachedAssets: assets.filter(a => a.cached).length,
                averageLoadTime: this.calculateAverage(performance.map(p => p.loadTime)),
                totalSize: performance.reduce((sum, p) => sum + (p.size || 0), 0),
                compressionRatio: this.calculateCompressionRatio(performance)
            };
        }

        /**
         * Calcule la moyenne d'un tableau de valeurs
         */
        calculateAverage(values) {
            if (values.length === 0) return 0;
            const validValues = values.filter(v => v && !isNaN(v));
            return validValues.reduce((sum, v) => sum + v, 0) / validValues.length;
        }

        /**
         * Calcule le ratio de compression
         */
        calculateCompressionRatio(performance) {
            const compressed = performance.filter(p => p.compressed);
            if (compressed.length === 0) return 0;

            const originalSize = compressed.reduce((sum, p) => sum + (p.size || 0), 0);
            const compressedSize = compressed.reduce((sum, p) => sum + ((p.size || 0) * 0.7), 0);

            return originalSize > 0 ? ((originalSize - compressedSize) / originalSize) * 100 : 0;
        }

        /**
         * Optimise les assets chargés
         */
        optimizeAssets() {
            const stats = this.getPerformanceStats();

            // Identifier les assets lents
            const slowAssets = Array.from(this.assets.values())
                .filter(asset => asset.loadTime > 1000) // Plus d'1 seconde
                .sort((a, b) => b.loadTime - a.loadTime);

            // Identifier les assets non compressés
            const uncompressedAssets = Array.from(this.assets.values())
                .filter(asset => !asset.compressed && asset.loaded);

            this.emit('optimizationRecommendations', {
                slowAssets: slowAssets.slice(0, 5),
                uncompressedAssets: uncompressedAssets,
                stats: stats
            });

            return {
                slowAssets: slowAssets,
                uncompressedAssets: uncompressedAssets,
                stats: stats
            };
        }
    }

    // ============================================================================
    // INTÉGRATION AVEC LE CANVAS OPTIMIZER
    // ============================================================================

    class IntegratedPerformanceManager {

        constructor() {
            this.assetManager = new AssetPerformanceManager();
            this.canvasOptimizer = null;
            this.initialized = false;
        }

        /**
         * Initialise le gestionnaire intégré
         */
        init() {
            if (this.initialized) return;

            // Attendre que le canvas optimizer soit disponible
            this.waitForCanvasOptimizer().then(() => {
                this.setupIntegration();
                this.initialized = true;

                this.emit('integrated', {
                    timestamp: Date.now(),
                    components: ['assetManager', 'canvasOptimizer']
                });
            });
        }

        /**
         * Attend que le canvas optimizer soit disponible
         */
        async waitForCanvasOptimizer() {
            return new Promise((resolve) => {
                const checkOptimizer = () => {
                    if (window.PDFCanvasPerformanceOptimizer) {
                        this.canvasOptimizer = window.PDFCanvasPerformanceOptimizer;
                        resolve();
                    } else {
                        setTimeout(checkOptimizer, 100);
                    }
                };
                checkOptimizer();
            });
        }

        /**
         * Configure l'intégration
         */
        setupIntegration() {
            // Connecter les événements
            this.assetManager.addObserver((event) => {
                this.handleAssetEvent(event);
            });

            // Synchroniser les métriques
            this.syncPerformanceMetrics();

            // Optimisations croisées
            this.setupCrossOptimization();
        }

        /**
         * Gère les événements des assets
         */
        handleAssetEvent(event) {
            switch (event.type) {
                case 'assetLoaded':
                    this.onAssetLoaded(event.data);
                    break;
                case 'assetError':
                    this.onAssetError(event.data);
                    break;
                case 'navigationMetrics':
                    this.onNavigationMetrics(event.data);
                    break;
            }
        }

        /**
         * Gestionnaire de chargement d'asset
         */
        onAssetLoaded(asset) {
            // Notifier le canvas optimizer
            if (this.canvasOptimizer && this.canvasOptimizer.updateMetrics) {
                this.canvasOptimizer.updateMetrics('assetLoad', {
                    url: asset.url,
                    loadTime: asset.loadTime,
                    compressed: asset.compressed
                });
            }

            // Optimiser le canvas si nécessaire
            if (asset.type === 'script' && asset.url.includes('pdf-builder')) {
                this.optimizeCanvasForAssets();
            }
        }

        /**
         * Gestionnaire d'erreur d'asset
         */
        onAssetError(asset) {
            console.warn('Erreur de chargement d\'asset:', asset.url);

            // Notifier le canvas optimizer
            if (this.canvasOptimizer && this.canvasOptimizer.updateMetrics) {
                this.canvasOptimizer.updateMetrics('assetError', {
                    url: asset.url,
                    error: true
                });
            }
        }

        /**
         * Gestionnaire des métriques de navigation
         */
        onNavigationMetrics(metrics) {
            // Ajuster les optimisations basées sur les métriques globales
            if (metrics.totalTime > 3000) { // Plus de 3 secondes
                this.enableAggressiveOptimization();
            }
        }

        /**
         * Synchronise les métriques de performance
         */
        syncPerformanceMetrics() {
            setInterval(() => {
                const assetStats = this.assetManager.getPerformanceStats();

                if (this.canvasOptimizer && this.canvasOptimizer.updateMetrics) {
                    this.canvasOptimizer.updateMetrics('assetStats', assetStats);
                }
            }, 5000); // Toutes les 5 secondes
        }

        /**
         * Configure les optimisations croisées
         */
        setupCrossOptimization() {
            // Optimiser le canvas en fonction des assets chargés
            document.addEventListener('pdfAssetPerformance', (event) => {
                const perfEvent = event.detail;

                if (perfEvent.type === 'optimizationRecommendations') {
                    this.applyOptimizationRecommendations(perfEvent.data);
                }
            });
        }

        /**
         * Optimise le canvas pour les assets
         */
        optimizeCanvasForAssets() {
            if (!this.canvasOptimizer) return;

            // Réduire la fréquence de mise à jour si les assets sont lents
            const stats = this.assetManager.getPerformanceStats();
            if (stats.averageLoadTime > 500) {
                this.canvasOptimizer.setOptimizationLevel('aggressive');
            }
        }

        /**
         * Active l'optimisation agressive
         */
        enableAggressiveOptimization() {
            if (this.canvasOptimizer) {
                this.canvasOptimizer.setOptimizationLevel('aggressive');
            }

            // Désactiver les animations non essentielles
            this.disableNonEssentialFeatures();
        }

        /**
         * Désactive les fonctionnalités non essentielles
         */
        disableNonEssentialFeatures() {
            // Désactiver les animations de survol coûteuses
            const expensiveElements = document.querySelectorAll('.pdf-builder-hover-effect');
            expensiveElements.forEach(el => {
                el.style.transition = 'none';
            });
        }

        /**
         * Applique les recommandations d'optimisation
         */
        applyOptimizationRecommendations(recommendations) {
            // Précharger les assets lents
            recommendations.slowAssets.forEach(asset => {
                this.preloadAsset(asset.url);
            });

            // Compresser les assets non compressés (côté serveur)
            if (recommendations.uncompressedAssets.length > 0) {
                this.requestServerCompression(recommendations.uncompressedAssets);
            }
        }

        /**
         * Précharge un asset
         */
        preloadAsset(url) {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.href = url;
            link.as = url.includes('.js') ? 'script' : 'style';
            document.head.appendChild(link);
        }

        /**
         * Demande la compression côté serveur
         */
        requestServerCompression(assets) {
            // Envoyer une requête AJAX pour compresser les assets
            if (window.ajaxurl) {
                fetch(window.ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'pdf_builder_compress_assets',
                        assets: JSON.stringify(assets.map(a => a.url)),
                        nonce: window.pdfBuilderNonce || ''
                    })
                }).catch(error => {
                    console.warn('Erreur lors de la demande de compression:', error);
                });
            }
        }

        /**
         * Émet un événement
         */
        emit(eventType, data) {
            const event = new CustomEvent('pdfIntegratedPerformance', {
                detail: {
                    type: eventType,
                    data: data,
                    timestamp: Date.now()
                }
            });
            document.dispatchEvent(event);
        }

        /**
         * Obtient les statistiques intégrées
         */
        getIntegratedStats() {
            const assetStats = this.assetManager.getPerformanceStats();
            const canvasStats = this.canvasOptimizer ?
                this.canvasOptimizer.getStats() : {};

            return {
                assets: assetStats,
                canvas: canvasStats,
                integrated: {
                    optimizationLevel: this.canvasOptimizer ?
                        this.canvasOptimizer.getOptimizationLevel() : 'unknown',
                    crossOptimizationEnabled: true,
                    lastSync: Date.now()
                }
            };
        }
    }

    // ============================================================================
    // INITIALISATION GLOBALE
    // ============================================================================

    // Créer l'instance globale
    window.assetPerformanceManager = new AssetPerformanceManager();
    window.integratedPerformanceManager = new IntegratedPerformanceManager();

    // Initialiser quand le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            window.integratedPerformanceManager.init();
        });
    } else {
        window.integratedPerformanceManager.init();
    }

    // Exposition publique pour le débogage
    window.PDFAssetPerformanceManager = AssetPerformanceManager;
    window.PDFIntegratedPerformanceManager = IntegratedPerformanceManager;

})(window, document);