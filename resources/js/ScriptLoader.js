/**
 * PDF Builder Pro - ScriptLoader
 * Phase 3.4.3 - Optimisation du chargement JavaScript
 *
 * Système de chargement différé et optimisé pour améliorer les performances :
 * - Chargement différé des scripts non critiques
 * - Préchargement des ressources critiques
 * - Gestion intelligente des dépendances
 */

class ScriptLoader {
    /**
     * Scripts en cours de chargement
     */
    static loadingScripts = new Map();

    /**
     * Scripts déjà chargés
     */
    static loadedScripts = new Set();

    /**
     * Files d'attente pour les dépendances
     */
    static dependencyQueues = new Map();

    /**
     * Configuration par défaut
     */
    static defaultConfig = {
        criticalScripts: ['jquery', 'jquery-core'],
        deferThreshold: 2000, // Différer après 2 secondes
        preloadCritical: true,
        enableIntersectionObserver: true
    };

    /**
     * Initialise le système d'optimisation du chargement
     *
     * @param {Object} config Configuration optionnelle
     */
    static init(config = {}) {
        const finalConfig = { ...this.defaultConfig, ...config };

        // Démarrer l'optimisation après le chargement critique
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => this.optimizeLoading(finalConfig), finalConfig.deferThreshold);
            });
        } else {
            setTimeout(() => this.optimizeLoading(finalConfig), finalConfig.deferThreshold);
        }

        // Précharger les ressources critiques
        if (finalConfig.preloadCritical) {
            this.preloadCriticalResources();
        }

        console.log('ScriptLoader initialisé avec configuration:', finalConfig);
    }

    /**
     * Optimise le chargement des scripts non critiques
     *
     * @param {Object} config Configuration
     */
    static optimizeLoading(config) {
        const scripts = document.querySelectorAll('script[src]');
        const nonCriticalScripts = [];

        scripts.forEach(script => {
            const src = script.getAttribute('src');
            if (src && !this.isCriticalScript(src, config.criticalScripts)) {
                nonCriticalScripts.push(script);
            }
        });

        console.log(`Optimisation: ${nonCriticalScripts.length} scripts non critiques identifiés`);

        // Différer le chargement des scripts non critiques
        nonCriticalScripts.forEach(script => {
            this.deferScript(script);
        });
    }

    /**
     * Vérifie si un script est critique
     *
     * @param {string} src URL du script
     * @param {Array} criticalScripts Liste des scripts critiques
     * @returns {boolean} True si critique
     */
    static isCriticalScript(src, criticalScripts) {
        return criticalScripts.some(critical => src.includes(critical));
    }

    /**
     * Diffère le chargement d'un script
     *
     * @param {HTMLScriptElement} script Element script
     */
    static deferScript(script) {
        const src = script.getAttribute('src');
        const originalParent = script.parentNode;

        // Créer un marqueur de position
        const placeholder = document.createComment(`Deferred script: ${src}`);
        originalParent.replaceChild(placeholder, script);

        // Charger le script de manière asynchrone
        this.loadScriptAsync(src, script)
            .then(() => {
                // Restaurer le script à sa position originale
                originalParent.replaceChild(script, placeholder);
                console.log(`Script différé chargé: ${src}`);
            })
            .catch(error => {
                console.warn(`Échec du chargement différé pour: ${src}`, error);
                // Restaurer le script original en cas d'erreur
                originalParent.replaceChild(script, placeholder);
            });
    }

    /**
     * Charge un script de manière asynchrone
     *
     * @param {string} src URL du script
     * @param {HTMLScriptElement} originalScript Script original
     * @returns {Promise} Promise de chargement
     */
    static loadScriptAsync(src, originalScript) {
        return new Promise((resolve, reject) => {
            // Éviter les chargements multiples
            if (this.loadingScripts.has(src)) {
                this.loadingScripts.get(src).push({ resolve, reject });
                return;
            }

            if (this.loadedScripts.has(src)) {
                resolve();
                return;
            }

            // Initialiser la file d'attente
            this.loadingScripts.set(src, [{ resolve, reject }]);

            // Créer le script
            const script = document.createElement('script');
            script.src = src;
            script.async = true;

            // Copier les attributs importants
            if (originalScript.hasAttribute('integrity')) {
                script.integrity = originalScript.getAttribute('integrity');
            }
            if (originalScript.hasAttribute('crossorigin')) {
                script.crossOrigin = originalScript.getAttribute('crossorigin');
            }

            script.onload = () => {
                this.loadedScripts.add(src);
                const queue = this.loadingScripts.get(src) || [];
                queue.forEach(({ resolve: res }) => res());
                this.loadingScripts.delete(src);
            };

            script.onerror = (error) => {
                const queue = this.loadingScripts.get(src) || [];
                queue.forEach(({ reject: rej }) => rej(error));
                this.loadingScripts.delete(src);
            };

            document.head.appendChild(script);
        });
    }

    /**
     * Précharge les ressources critiques
     */
    static preloadCriticalResources() {
        // Précharger les polices critiques si elles existent
        const criticalFonts = [
            // Ajouter les URLs des polices critiques ici
        ];

        criticalFonts.forEach(fontUrl => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.href = fontUrl;
            link.as = 'font';
            link.crossOrigin = 'anonymous';
            document.head.appendChild(link);
        });

        // Précharger les CSS critiques
        const criticalCSS = document.querySelectorAll('link[rel="stylesheet"]');
        criticalCSS.forEach(link => {
            if (link.href && link.href.includes('pdf-builder-admin')) {
                link.rel = 'preload';
                link.as = 'style';
                link.onload = function() {
                    this.rel = 'stylesheet';
                };
                document.head.appendChild(link.cloneNode());
            }
        });

        console.log('Ressources critiques préchargées');
    }

    /**
     * Charge un script avec gestion des dépendances
     *
     * @param {string} src URL du script
     * @param {Array} dependencies Liste des dépendances
     * @param {Object} options Options de chargement
     * @returns {Promise} Promise de chargement
     */
    static loadScriptWithDeps(src, dependencies = [], options = {}) {
        return new Promise((resolve, reject) => {
            // Vérifier les dépendances
            const missingDeps = dependencies.filter(dep => !this.loadedScripts.has(dep));

            if (missingDeps.length > 0) {
                // Mettre en file d'attente
                if (!this.dependencyQueues.has(src)) {
                    this.dependencyQueues.set(src, []);
                }
                this.dependencyQueues.get(src).push({ resolve, reject, dependencies: missingDeps });

                // Charger les dépendances manquantes
                missingDeps.forEach(dep => {
                    this.loadScriptAsync(dep, document.createElement('script'))
                        .then(() => this.checkDependencyQueue(src))
                        .catch(reject);
                });
                return;
            }

            // Toutes les dépendances sont chargées, charger le script
            this.loadScriptAsync(src, document.createElement('script'))
                .then(resolve)
                .catch(reject);
        });
    }

    /**
     * Vérifie la file d'attente des dépendances
     *
     * @param {string} src URL du script à vérifier
     */
    static checkDependencyQueue(src) {
        const queue = this.dependencyQueues.get(src);
        if (!queue) return;

        // Filtrer les éléments dont toutes les dépendances sont chargées
        const readyItems = queue.filter(item =>
            item.dependencies.every(dep => this.loadedScripts.has(dep))
        );

        // Traiter les éléments prêts
        readyItems.forEach(item => {
            this.loadScriptAsync(src, document.createElement('script'))
                .then(item.resolve)
                .catch(item.reject);
        });

        // Supprimer les éléments traités
        queue.splice(0, readyItems.length);

        // Supprimer la file si vide
        if (queue.length === 0) {
            this.dependencyQueues.delete(src);
        }
    }

    /**
     * Mesure les métriques de performance
     *
     * @returns {Object} Métriques de chargement
     */
    static getPerformanceMetrics() {
        const metrics = {
            scriptsLoaded: this.loadedScripts.size,
            scriptsLoading: this.loadingScripts.size,
            dependencyQueues: this.dependencyQueues.size,
            totalScripts: document.querySelectorAll('script[src]').length
        };

        // Mesurer les Core Web Vitals si disponibles
        if (window.performance && window.performance.getEntriesByType) {
            const navigation = window.performance.getEntriesByType('navigation')[0];
            if (navigation) {
                metrics.domContentLoaded = navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart;
                metrics.loadComplete = navigation.loadEventEnd - navigation.loadEventStart;
            }
        }

        return metrics;
    }

    /**
     * Nettoie les ressources
     */
    static cleanup() {
        this.loadingScripts.clear();
        this.loadedScripts.clear();
        this.dependencyQueues.clear();
        console.log('ScriptLoader nettoyé');
    }
}

// Auto-initialisation intelligente
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => ScriptLoader.init());
} else {
    ScriptLoader.init();
}

// Export pour les modules ES6
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ScriptLoader;
}

// Export global
window.ScriptLoader = ScriptLoader;