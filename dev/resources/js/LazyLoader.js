/**
 * PDF Builder Pro - LazyLoader
 * Phase 3.4.1 - Chargement différé des images avec Intersection Observer API
 *
 * Gère le chargement paresseux des images pour optimiser les performances :
 * - Intersection Observer pour détecter la visibilité
 * - Placeholders pendant le chargement
 * - Gestion des erreurs de chargement
 */

class LazyLoader {
    /**
     * Configuration par défaut
     */
    static defaultConfig = {
        rootMargin: '50px 0px',
        threshold: 0.1,
        placeholder: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkxvYWRpbmcuLi48L3RleHQ+PC9zdmc+'
    };

    /**
     * Instance de l'observer
     */
    static observer = null;

    /**
     * Images en cours de chargement
     */
    static loadingImages = new Set();

    /**
     * Initialise le lazy loading pour toutes les images avec data-src
     *
     * @param {Object} config Configuration optionnelle
     */
    static init(config = {}) {
        // Fusionner la configuration
        const finalConfig = { ...this.defaultConfig, ...config };

        // Créer l'observer s'il n'existe pas
        if (!this.observer) {
            this.observer = new IntersectionObserver(
                (entries) => this.handleIntersection(entries),
                {
                    rootMargin: finalConfig.rootMargin,
                    threshold: finalConfig.threshold
                }
            );
        }

        // Observer toutes les images avec data-src
        this.observeImages();

        console.log('LazyLoader initialisé avec configuration:', finalConfig);
    }

    /**
     * Observe toutes les images lazy dans le conteneur spécifié
     *
     * @param {Element} container Conteneur à scanner (défaut: document)
     */
    static observeImages(container = document) {
        const lazyImages = container.querySelectorAll('img[data-src]');

        lazyImages.forEach(img => {
            // Éviter les doublons
            if (!img.hasAttribute('data-lazy-observed')) {
                img.setAttribute('data-lazy-observed', 'true');

                // Définir le placeholder si pas déjà défini
                if (!img.src || img.src === '') {
                    img.src = img.dataset.placeholder || this.defaultConfig.placeholder;
                }

                this.observer.observe(img);
            }
        });
    }

    /**
     * Gère les intersections détectées par l'observer
     *
     * @param {IntersectionObserverEntry[]} entries Entrées d'intersection
     */
    static handleIntersection(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                this.loadImage(img);
            }
        });
    }

    /**
     * Charge une image lazy
     *
     * @param {HTMLImageElement} img Image à charger
     */
    static async loadImage(img) {
        // Éviter les chargements multiples
        if (this.loadingImages.has(img) || img.src === img.dataset.src) {
            return;
        }

        this.loadingImages.add(img);

        try {
            // Créer une nouvelle image pour précharger
            const tempImg = new Image();

            tempImg.onload = () => {
                // Transférer l'image chargée
                img.src = img.dataset.src;
                img.classList.add('lazy-loaded');
                img.classList.remove('lazy-loading');

                // Nettoyer
                this.loadingImages.delete(img);
                this.observer.unobserve(img);

                // Déclencher un événement
                img.dispatchEvent(new CustomEvent('lazyloaded', {
                    detail: { success: true, src: img.dataset.src }
                }));
            };

            tempImg.onerror = () => {
                // Gestion d'erreur
                img.classList.add('lazy-error');
                img.classList.remove('lazy-loading');

                // Nettoyer
                this.loadingImages.delete(img);
                this.observer.unobserve(img);

                // Déclencher un événement d'erreur
                img.dispatchEvent(new CustomEvent('lazyerror', {
                    detail: { success: false, src: img.dataset.src }
                }));

                console.warn('Erreur de chargement lazy pour:', img.dataset.src);
            };

            // Ajouter la classe de chargement
            img.classList.add('lazy-loading');

            // Démarrer le chargement
            tempImg.src = img.dataset.src;

        } catch (error) {
            console.error('Erreur lors du lazy loading:', error);
            this.loadingImages.delete(img);
            this.observer.unobserve(img);
        }
    }

    /**
     * Force le chargement de toutes les images lazy
     */
    static loadAll() {
        const lazyImages = document.querySelectorAll('img[data-src]');
        lazyImages.forEach(img => this.loadImage(img));
    }

    /**
     * Détruit l'observer et nettoie les ressources
     */
    static destroy() {
        if (this.observer) {
            this.observer.disconnect();
            this.observer = null;
        }

        this.loadingImages.clear();

        // Retirer les attributs d'observation
        const lazyImages = document.querySelectorAll('img[data-lazy-observed]');
        lazyImages.forEach(img => {
            img.removeAttribute('data-lazy-observed');
        });

        console.log('LazyLoader détruit');
    }

    /**
     * Vérifie si une image est visible dans le viewport
     *
     * @param {HTMLImageElement} img Image à vérifier
     * @param {number} margin Marge en pixels
     * @returns {boolean} True si visible
     */
    static isInViewport(img, margin = 50) {
        const rect = img.getBoundingClientRect();
        return (
            rect.top >= -margin &&
            rect.left >= -margin &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) + margin &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth) + margin
        );
    }
}

// Auto-initialisation quand le DOM est prêt
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => LazyLoader.init());
} else {
    LazyLoader.init();
}

// Export pour les modules ES6
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LazyLoader;
}

// Export global
window.LazyLoader = LazyLoader;