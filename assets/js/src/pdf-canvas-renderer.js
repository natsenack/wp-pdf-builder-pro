/**
 * PDF Canvas Renderer - Intégration avancée Canvas HTML5 API
 * Gestion des transformations, effets visuels et optimisation du rendu
 */

export class PDFCanvasRenderer {
    constructor(mainInstance) {
        this.mainInstance = mainInstance;
        this.canvas = null;
        this.ctx = null;
        this.devicePixelRatio = window.devicePixelRatio || 1;

        // Cache pour les images et formes
        this.imageCache = new Map();
        this.shapeCache = new Map();

        // Paramètres de rendu
        this.renderSettings = {
            antiAlias: true,
            imageSmoothing: true,
            shadowQuality: 'high',
            textRendering: 'optimizeLegibility'
        };

        // Statistiques de performance
        this.performanceStats = {
            renderTime: 0,
            elementCount: 0,
            lastRenderTime: Date.now()
        };

        // Will be initialized when canvas is created
    }

    /**
     * Initialise le renderer avec le canvas et contexte
     */
    initialize(canvas, context) {
        this.canvas = canvas;
        this.ctx = context;
        this.init();
    }

    /**
     * Initialise le renderer
     */
    init() {
        if (!this.canvas || !this.ctx) return;
        this.optimizeForDevice();
        this.setupContext();
    }

    /**
     * Optimise le canvas pour le périphérique
     */
    optimizeForDevice() {
        // Ajuster la taille du canvas pour la densité de pixels
        const rect = this.canvas.getBoundingClientRect();
        this.canvas.width = rect.width * this.devicePixelRatio;
        this.canvas.height = rect.height * this.devicePixelRatio;

        // Ajuster l'échelle du contexte
        this.ctx.scale(this.devicePixelRatio, this.devicePixelRatio);

        // Restaurer les dimensions CSS
        this.canvas.style.width = rect.width + 'px';
        this.canvas.style.height = rect.height + 'px';
    }

    /**
     * Configure le contexte de rendu
     */
    setupContext() {
        // Configuration de l'anti-aliasing
        this.ctx.imageSmoothingEnabled = this.renderSettings.imageSmoothing;
        this.ctx.imageSmoothingQuality = 'high';

        // Configuration du texte
        this.ctx.textBaseline = 'top';
        this.ctx.font = '14px Arial, sans-serif';

        // Configuration globale
        this.ctx.globalCompositeOperation = 'source-over';
    }

    /**
     * Rend un élément avec des effets avancés
     */
    renderElement(element, computedProperties) {
        const startTime = performance.now();

        this.ctx.save();

        try {
            // Appliquer les transformations
            this.applyTransforms(element.properties, computedProperties);

            // Appliquer les effets visuels
            this.applyVisualEffects(element.properties);

            // Rendre selon le type
            this.renderByType(element);

            // Appliquer les filtres post-rendu
            this.applyPostFilters(element.properties);

        } finally {
            this.ctx.restore();
        }

        // Mettre à jour les statistiques
        this.performanceStats.renderTime += performance.now() - startTime;
        this.performanceStats.elementCount++;
    }

    /**
     * Applique les transformations géométriques
     */
    applyTransforms(properties, computedProperties) {
        // Translation
        this.ctx.translate(properties.x, properties.y);

        // Rotation
        if (properties.rotation && properties.rotation !== 0) {
            const centerX = properties.width / 2;
            const centerY = properties.height / 2;
            this.ctx.translate(centerX, centerY);
            this.ctx.rotate((properties.rotation * Math.PI) / 180);
            this.ctx.translate(-centerX, -centerY);
        }

        // Échelle
        if (properties.scale && properties.scale !== 100) {
            const scale = properties.scale / 100;
            const centerX = properties.width / 2;
            const centerY = properties.height / 2;
            this.ctx.translate(centerX, centerY);
            this.ctx.scale(scale, scale);
            this.ctx.translate(-centerX, -centerY);
        }

        // Opacité
        if (properties.opacity !== undefined && properties.opacity < 100) {
            this.ctx.globalAlpha = properties.opacity / 100;
        }
    }

    /**
     * Applique les effets visuels
     */
    applyVisualEffects(properties) {
        // Ombres
        if (properties.shadow) {
            this.ctx.shadowColor = properties.shadowColor || '#000000';
            this.ctx.shadowBlur = 4;
            this.ctx.shadowOffsetX = properties.shadowOffsetX || 2;
            this.ctx.shadowOffsetY = properties.shadowOffsetY || 2;
        } else {
            this.ctx.shadowColor = 'transparent';
            this.ctx.shadowBlur = 0;
            this.ctx.shadowOffsetX = 0;
            this.ctx.shadowOffsetY = 0;
        }

        // Filtres CSS (si supportés)
        if (this.ctx.filter !== undefined) {
            const filters = [];

            if (properties.brightness && properties.brightness !== 100) {
                filters.push(`brightness(${properties.brightness}%)`);
            }

            if (properties.contrast && properties.contrast !== 100) {
                filters.push(`contrast(${properties.contrast}%)`);
            }

            if (properties.saturate && properties.saturate !== 100) {
                filters.push(`saturate(${properties.saturate}%)`);
            }

            this.ctx.filter = filters.join(' ') || 'none';
        }
    }

    /**
     * Rend selon le type d'élément
     */
    renderByType(element) {
        console.log('Renderer: rendering type', element.type);
        switch (element.type) {
            case 'text':
                console.log('Renderer: calling renderText');
                this.renderText(element);
                break;
            case 'rectangle':
            case 'shape-rectangle':
                console.log('Renderer: calling renderRectangle');
                this.renderRectangle(element);
                break;
            case 'circle':
            case 'shape-circle':
                console.log('Renderer: calling renderCircle');
                this.renderCircle(element);
                break;
            case 'line':
            case 'shape-line':
                console.log('Renderer: calling renderLine');
                this.renderLine(element);
                break;
            case 'image':
                console.log('Renderer: calling renderImage');
                this.renderImage(element);
                break;
            case 'barcode':
                console.log('Renderer: calling renderBarcode');
                this.renderBarcode(element);
                break;
            case 'qrcode':
                console.log('Renderer: calling renderQRCode');
                this.renderQRCode(element);
                break;
            default:
                console.log('Renderer: calling renderGeneric for type', element.type);
                this.renderGeneric(element);
                break;
        }
    }

    /**
     * Rend du texte avec effets avancés
     */
    renderText(element) {
        console.log('Renderer: renderText called with element:', element);
        console.log('Renderer: renderText properties:', element.properties);
        const props = element.properties;

        // Configuration de la police
        const fontStyle = this.buildFontString(props);
        this.ctx.font = fontStyle;
        this.ctx.fillStyle = props.color || '#000000';
        this.ctx.textAlign = props.textAlign || 'left';

        // Position de départ
        let x = 0;
        let y = 0;

        // Ajuster selon l'alignement
        const text = props.text || '';
        const metrics = this.ctx.measureText(text);

        if (props.textAlign === 'center') {
            x = props.width / 2;
        } else if (props.textAlign === 'right') {
            x = props.width;
        }

        // Rendu du texte avec effets
        if (props.textDecoration) {
            this.renderTextDecoration(text, x, y, props, metrics);
        }

        // Rendu principal du texte
        this.ctx.fillText(text, x, y);

        // Contour du texte si spécifié
        if (props.strokeWidth && props.strokeWidth > 0) {
            this.ctx.strokeStyle = props.strokeColor || '#000000';
            this.ctx.lineWidth = props.strokeWidth;
            this.ctx.strokeText(text, x, y);
        }
    }

    /**
     * Construit la chaîne de police CSS
     */
    buildFontString(props) {
        const weight = props.fontWeight || 'normal';
        const size = props.fontSize || 14;
        const family = props.fontFamily || 'Arial, sans-serif';
        const style = props.fontStyle || 'normal';

        return `${style} ${weight} ${size}px ${family}`;
    }

    /**
     * Rend les décorations de texte (souligné, barré, etc.)
     */
    renderTextDecoration(text, x, y, props, metrics) {
        const lineY = y + (props.fontSize || 14) * 0.8; // Position de la ligne de base

        this.ctx.strokeStyle = props.color || '#000000';
        this.ctx.lineWidth = 1;

        switch (props.textDecoration) {
            case 'underline':
                this.ctx.beginPath();
                this.ctx.moveTo(x, lineY);
                this.ctx.lineTo(x + metrics.width, lineY);
                this.ctx.stroke();
                break;
            case 'overline':
                this.ctx.beginPath();
                this.ctx.moveTo(x, y);
                this.ctx.lineTo(x + metrics.width, y);
                this.ctx.stroke();
                break;
            case 'line-through':
                const strikeY = y + (props.fontSize || 14) * 0.5;
                this.ctx.beginPath();
                this.ctx.moveTo(x, strikeY);
                this.ctx.lineTo(x + metrics.width, strikeY);
                this.ctx.stroke();
                break;
        }
    }

    /**
     * Rend un rectangle avec coins arrondis
     */
    renderRectangle(element) {
        console.log('Renderer: renderRectangle called with element:', element);
        console.log('Renderer: renderRectangle properties:', element.properties);
        const props = element.properties;
        const radius = props.borderRadius || 0;

        this.ctx.beginPath();

        if (radius === 0) {
            // Rectangle simple
            this.ctx.rect(0, 0, props.width, props.height);
        } else {
            // Rectangle avec coins arrondis
            this.roundedRect(0, 0, props.width, props.height, radius);
        }

        // Fond
        if (props.backgroundColor && props.backgroundColor !== 'transparent') {
            this.ctx.fillStyle = props.backgroundColor;
            this.ctx.fill();
        }

        // Bordure
        if (props.borderWidth && props.borderWidth > 0) {
            this.ctx.strokeStyle = props.borderColor || '#000000';
            this.ctx.lineWidth = props.borderWidth;
            this.ctx.stroke();
        }
    }

    /**
     * Rend un cercle
     */
    renderCircle(element) {
        const props = element.properties;
        const centerX = props.width / 2;
        const centerY = props.height / 2;
        const radius = Math.min(centerX, centerY);

        this.ctx.beginPath();
        this.ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);

        // Fond
        if (props.backgroundColor && props.backgroundColor !== 'transparent') {
            this.ctx.fillStyle = props.backgroundColor;
            this.ctx.fill();
        }

        // Bordure
        if (props.borderWidth && props.borderWidth > 0) {
            this.ctx.strokeStyle = props.borderColor || '#000000';
            this.ctx.lineWidth = props.borderWidth;
            this.ctx.stroke();
        }
    }

    /**
     * Rend une ligne
     */
    renderLine(element) {
        const props = element.properties;

        this.ctx.beginPath();
        this.ctx.moveTo(0, props.height / 2);
        this.ctx.lineTo(props.width, props.height / 2);

        this.ctx.strokeStyle = props.color || '#000000';
        this.ctx.lineWidth = props.lineWidth || 2;
        this.ctx.lineCap = 'round';
        this.ctx.stroke();
    }

    /**
     * Rend une image avec optimisation du cache
     */
    renderImage(element) {
        const props = element.properties;

        if (!props.src) return;

        const cacheKey = props.src;

        // Vérifier le cache
        if (this.imageCache.has(cacheKey)) {
            const cachedImage = this.imageCache.get(cacheKey);
            this.drawCachedImage(cachedImage, props);
        } else {
            // Charger et mettre en cache
            this.loadAndCacheImage(props.src, cacheKey, props);
        }
    }

    /**
     * Charge et met en cache une image
     */
    loadAndCacheImage(src, cacheKey, props) {
        const img = new Image();
        img.crossOrigin = 'anonymous';

        img.onload = () => {
            const cachedImage = {
                image: img,
                loaded: true,
                width: img.naturalWidth,
                height: img.naturalHeight
            };

            this.imageCache.set(cacheKey, cachedImage);
            this.drawCachedImage(cachedImage, props);
        };

        img.onerror = () => {
            console.warn(`Failed to load image: ${src}`);
            // Dessiner un placeholder
            this.drawImagePlaceholder(props);
        };

        img.src = src;
    }

    /**
     * Dessine une image mise en cache
     */
    drawCachedImage(cachedImage, props) {
        if (!cachedImage.loaded) return;

        const img = cachedImage.image;
        let drawWidth = props.width;
        let drawHeight = props.height;
        let drawX = 0;
        let drawY = 0;

        // Appliquer object-fit
        if (props.objectFit === 'cover') {
            const scale = Math.max(props.width / img.width, props.height / img.height);
            drawWidth = img.width * scale;
            drawHeight = img.height * scale;
            drawX = (props.width - drawWidth) / 2;
            drawY = (props.height - drawHeight) / 2;
        } else if (props.objectFit === 'contain') {
            const scale = Math.min(props.width / img.width, props.height / img.height);
            drawWidth = img.width * scale;
            drawHeight = img.height * scale;
            drawX = (props.width - drawWidth) / 2;
            drawY = (props.height - drawHeight) / 2;
        }

        this.ctx.drawImage(img, drawX, drawY, drawWidth, drawHeight);
    }

    /**
     * Dessine un placeholder pour les images non chargées
     */
    drawImagePlaceholder(props) {
        // Rectangle gris avec icône d'image
        this.ctx.fillStyle = '#f0f0f0';
        this.ctx.fillRect(0, 0, props.width, props.height);

        this.ctx.strokeStyle = '#ccc';
        this.ctx.lineWidth = 2;
        this.ctx.strokeRect(2, 2, props.width - 4, props.height - 4);

        // Icône simple (carré avec X)
        this.ctx.strokeStyle = '#999';
        this.ctx.lineWidth = 3;
        this.ctx.beginPath();
        this.ctx.moveTo(10, 10);
        this.ctx.lineTo(props.width - 10, props.height - 10);
        this.ctx.moveTo(props.width - 10, 10);
        this.ctx.lineTo(10, props.height - 10);
        this.ctx.stroke();
    }

    /**
     * Rend un code-barres
     */
    renderBarcode(element) {
        const props = element.properties;
        const value = props.value || '123456789012';

        // Utiliser une implémentation simple de code-barres
        // En production, utiliser une bibliothèque comme JsBarcode
        this.renderSimpleBarcode(value, props);
    }

    /**
     * Rend un code QR
     */
    renderQRCode(element) {
        const props = element.properties;
        const value = props.value || 'https://example.com';

        // Placeholder pour le QR code
        // En production, utiliser une bibliothèque comme qrcode.js
        this.renderQRPlaceholder(value, props);
    }

    /**
     * Rend un code-barres simple
     */
    renderSimpleBarcode(value, props) {
        const barWidth = 2;
        const barHeight = props.height * 0.8;
        const startX = (props.width - (value.length * barWidth)) / 2;
        const startY = (props.height - barHeight) / 2;

        this.ctx.fillStyle = props.color || '#000000';

        // Rendu simplifié : barres alternées
        for (let i = 0; i < value.length; i++) {
            if (parseInt(value[i]) % 2 === 0) {
                this.ctx.fillRect(
                    startX + i * barWidth,
                    startY,
                    barWidth,
                    barHeight
                );
            }
        }
    }

    /**
     * Rend un placeholder QR code
     */
    renderQRPlaceholder(value, props) {
        // Rectangle avec motif QR simplifié
        this.ctx.fillStyle = '#000000';
        this.ctx.fillRect(0, 0, props.width, props.height);

        // Motif central
        this.ctx.fillStyle = '#ffffff';
        const centerSize = Math.min(props.width, props.height) * 0.4;
        const centerX = (props.width - centerSize) / 2;
        const centerY = (props.height - centerSize) / 2;
        this.ctx.fillRect(centerX, centerY, centerSize, centerSize);

        // Texte de valeur
        this.ctx.fillStyle = '#000000';
        this.ctx.font = '10px monospace';
        this.ctx.textAlign = 'center';
        this.ctx.fillText('QR', props.width / 2, props.height / 2 + 3);
    }

    /**
     * Rend un élément générique
     */
    renderGeneric(element) {
        // Rendu par défaut
        this.renderRectangle(element);
    }

    /**
     * Applique les filtres post-rendu
     */
    applyPostFilters(properties) {
        // Filtres supplémentaires peuvent être appliqués ici
        // Par exemple : blur, sepia, etc.
    }

    /**
     * Dessine un rectangle avec coins arrondis
     */
    roundedRect(x, y, width, height, radius) {
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
     * Efface la zone de rendu
     */
    clear() {
        this.ctx.clearRect(0, 0, this.canvas.width / this.devicePixelRatio, this.canvas.height / this.devicePixelRatio);
    }

    /**
     * Redimensionne le canvas
     */
    resize(width, height) {
        this.canvas.width = width * this.devicePixelRatio;
        this.canvas.height = height * this.devicePixelRatio;
        this.canvas.style.width = width + 'px';
        this.canvas.style.height = height + 'px';
        this.ctx.scale(this.devicePixelRatio, this.devicePixelRatio);
    }

    /**
     * Obtient les statistiques de performance
     */
    getPerformanceStats() {
        return {
            ...this.performanceStats,
            averageRenderTime: this.performanceStats.elementCount > 0
                ? this.performanceStats.renderTime / this.performanceStats.elementCount
                : 0,
            fps: this.performanceStats.lastRenderTime > 0
                ? 1000 / (Date.now() - this.performanceStats.lastRenderTime)
                : 0
        };
    }

    /**
     * Réinitialise les statistiques
     */
    resetStats() {
        this.performanceStats = {
            renderTime: 0,
            elementCount: 0,
            lastRenderTime: Date.now()
        };
    }

    /**
     * Nettoie le cache
     */
    clearCache() {
        this.imageCache.clear();
        this.shapeCache.clear();
    }

    /**
     * Détruit le renderer
     */
    dispose() {
        this.clearCache();
        this.resetStats();
    }
}

export default PDFCanvasRenderer;