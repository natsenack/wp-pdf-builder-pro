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
     * Rend l'ensemble du canvas
     */
    render() {
        // Effacer le canvas
        this.clear();

        // Rendre le fond
        this.renderBackground();

        // Rendre la grille si activée
        console.log('Canvas render - showGrid:', this.mainInstance.options.showGrid);
        if (this.mainInstance.options.showGrid) {
            console.log('Calling renderGrid');
            this.renderGrid();
        } else {
            console.log('Grid not rendered - showGrid is false');
        }

        // Rendre tous les éléments
        this.renderAllElements();

        // Rendre les poignées de sélection et de transformation
        this.renderSelectionHandles();
    }

    /**
     * Rend le fond du canvas
     */
    renderBackground() {
        this.ctx.fillStyle = this.mainInstance.options.backgroundColor || '#ffffff';
        this.ctx.fillRect(0, 0, this.canvas.width / this.devicePixelRatio, this.canvas.height / this.devicePixelRatio);
    }

    /**
     * Rend la grille d'alignement
     */
    renderGrid() {
        const gridSize = this.mainInstance.options.gridSize || 20;
        const width = this.canvas.width / this.devicePixelRatio;
        const height = this.canvas.height / this.devicePixelRatio;

        console.log('Rendering grid:', { showGrid: this.mainInstance.options.showGrid, gridSize, width, height });

        this.ctx.save();

        // Couleur plus visible pour la grille
        this.ctx.strokeStyle = 'rgba(0, 0, 0, 0.1)'; // Plus visible que #e0e0e0
        this.ctx.lineWidth = 0.5; // Ligne plus fine
        this.ctx.setLineDash([2, 2]); // Pointillés plus visibles

        // Lignes verticales
        for (let x = 0; x <= width; x += gridSize) {
            this.ctx.beginPath();
            this.ctx.moveTo(x, 0);
            this.ctx.lineTo(x, height);
            this.ctx.stroke();
        }

        // Lignes horizontales
        for (let y = 0; y <= height; y += gridSize) {
            this.ctx.beginPath();
            this.ctx.moveTo(0, y);
            this.ctx.lineTo(width, y);
            this.ctx.stroke();
        }

        this.ctx.restore();
    }

    /**
     * Rend tous les éléments
     */
    renderAllElements() {
        // Trier les éléments par ordre z-index (couche)
        const sortedElements = Array.from(this.mainInstance.elements.values())
            .sort((a, b) => (a.properties.zIndex || 0) - (b.properties.zIndex || 0));

        sortedElements.forEach(element => {
            this.renderElement(element, {});
        });
    }

    /**
     * Rend les poignées de sélection et de transformation
     */
    renderSelectionHandles() {
        const selectedElements = this.mainInstance.selectionManager.getSelectedElements();

        selectedElements.forEach(element => {
            // Rendre les handles de transformation
            this.mainInstance.transformationsManager.renderElementHandles(this.ctx, element);
        });

        // Rendre les bounds de sélection si multiple
        if (selectedElements.length > 1) {
            this.renderSelectionBounds();
        }
    }

    /**
     * Rend les bounds de sélection pour les sélections multiples
     */
    renderSelectionBounds() {
        const bounds = this.mainInstance.selectionManager.selectionBounds;
        if (!bounds) return;

        this.ctx.save();
        this.ctx.strokeStyle = '#007bff';
        this.ctx.lineWidth = 2;
        this.ctx.setLineDash([5, 5]);
        this.ctx.strokeRect(bounds.x, bounds.y, bounds.width, bounds.height);
        this.ctx.restore();
    }

    /**
     * Rend un élément avec des effets avancés
     */
    renderElement(element, computedProperties) {
        const startTime = performance.now();

        this.ctx.save();

        try {
            // Safeguard against invalid element or properties
            if (!element || !element.properties) {
                console.warn('renderElement: invalid element or properties', element);
                return;
            }

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
        // Safeguard against undefined properties
        if (!properties) {
            console.warn('applyTransforms: properties is undefined');
            return;
        }

        // Ensure required properties have defaults
        const safeProps = {
            x: properties.x || 0,
            y: properties.y || 0,
            width: properties.width || 100,
            height: properties.height || 50,
            rotation: properties.rotation || 0,
            scale: properties.scale || 100,
            opacity: properties.opacity !== undefined ? properties.opacity : 100,
            ...properties
        };

        // Translation
        this.ctx.translate(safeProps.x, safeProps.y);

        // Rotation
        if (safeProps.rotation && safeProps.rotation !== 0) {
            const centerX = safeProps.width / 2;
            const centerY = safeProps.height / 2;
            this.ctx.translate(centerX, centerY);
            this.ctx.rotate((safeProps.rotation * Math.PI) / 180);
            this.ctx.translate(-centerX, -centerY);
        }

        // Échelle
        if (safeProps.scale && safeProps.scale !== 100) {
            const scale = safeProps.scale / 100;
            const centerX = safeProps.width / 2;
            const centerY = safeProps.height / 2;
            this.ctx.translate(centerX, centerY);
            this.ctx.scale(scale, scale);
            this.ctx.translate(-centerX, -centerY);
        }

        // Opacité — accepte les deux échelles (0-1 ou 0-100)
        if (safeProps.opacity !== undefined) {
            const opacity = safeProps.opacity;
            if (opacity <= 1) {
                // Valeur déjà en 0..1
                this.ctx.globalAlpha = opacity;
            } else if (opacity <= 100) {
                // Valeur en pourcentage 0..100
                this.ctx.globalAlpha = opacity / 100;
            }
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
        
        // Éléments WooCommerce - traiter comme du texte dynamique
        if (this.isWooCommerceElement(element.type)) {
            this.renderWooCommerceElement(element);
            return;
        }
        
        switch (element.type) {
            case 'text':
                this.renderText(element);
                break;
            case 'rectangle':
            case 'shape-rectangle':
                this.renderRectangle(element);
                break;
            case 'circle':
            case 'shape-circle':
                this.renderCircle(element);
                break;
            case 'line':
            case 'shape-line':
                this.renderLine(element);
                break;
            case 'image':
                this.renderImage(element);
                break;
            case 'barcode':
                this.renderBarcode(element);
                break;
            case 'qrcode':
                this.renderQRCode(element);
                break;
            default:
                this.renderGeneric(element);
                break;
        }
    }

    /**
     * Vérifie si un type d'élément est un élément WooCommerce
     */
    isWooCommerceElement(type) {
        const wooCommerceTypes = [
            'order_number', 'order_date', 'customer_name', 'customer_email',
            'billing_address', 'shipping_address', 'payment_method', 'order_status',
            'subtotal', 'discount', 'shipping', 'taxes', 'total', 'refund',
            'product_table', 'customer_info', 'company_info', 'company_logo',
            'dynamic-text', 'woocommerce-invoice-number', 'woocommerce-invoice-date',
            'woocommerce-order-number', 'woocommerce-order-date', 'woocommerce-billing-address',
            'woocommerce-shipping-address', 'woocommerce-customer-name', 'woocommerce-customer-email',
            'woocommerce-payment-method', 'woocommerce-order-status', 'woocommerce-products-table',
            'woocommerce-subtotal', 'woocommerce-discount', 'woocommerce-shipping',
            'woocommerce-taxes', 'woocommerce-total', 'woocommerce-refund'
        ];
        return wooCommerceTypes.includes(type);
    }

    /**
     * Rend un élément WooCommerce comme du texte
     */
    renderWooCommerceElement(element) {
        
        // Obtenir le texte depuis le gestionnaire WooCommerce
        let displayText = '';
        if (this.mainInstance && this.mainInstance.wooCommerceManager) {
            displayText = this.mainInstance.wooCommerceManager.getElementDisplayText(element.type);
        }
        
        // Créer une copie des propriétés avec le texte
        const textElement = {
            ...element,
            properties: {
                ...element.properties,
                text: displayText || `[${element.type}]`
            }
        };
        
        // Rendre comme du texte
        this.renderText(textElement);
    }

    /**
     * Rend du texte avec effets avancés
     */
    renderText(element) {
        const props = element.properties;
        
        // Safeguard
        if (!props) {
            console.warn('renderText: properties is undefined for element', element);
            return;
        }

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
            x = (props.width || 100) / 2;
        } else if (props.textAlign === 'right') {
            x = props.width || 100;
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

        // Ajouter un texte d'exemple pour la visibilité
        if (props.width > 60 && props.height > 20) {
            this.ctx.fillStyle = props.color || '#64748b';
            this.ctx.font = '12px Arial, sans-serif';
            this.ctx.textAlign = 'center';
            this.ctx.fillText('Zone de contenu', props.width / 2, props.height / 2 + 4);
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

        // Ajouter un indicateur visuel
        if (radius > 15) {
            this.ctx.fillStyle = props.color || '#ffffff';
            this.ctx.font = '16px Arial, sans-serif';
            this.ctx.textAlign = 'center';
            this.ctx.fillText('●', centerX, centerY + 6);
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
        // Rectangle avec fond dégradé
        const gradient = this.ctx.createLinearGradient(0, 0, props.width, props.height);
        gradient.addColorStop(0, '#f8fafc');
        gradient.addColorStop(1, '#e2e8f0');

        this.ctx.fillStyle = gradient;
        this.ctx.fillRect(0, 0, props.width, props.height);

        this.ctx.strokeStyle = '#cbd5e1';
        this.ctx.lineWidth = 2;
        this.ctx.strokeRect(2, 2, props.width - 4, props.height - 4);

        // Icône d'image stylisée
        this.ctx.fillStyle = '#94a3b8';
        this.ctx.font = Math.min(props.width / 4, 24) + 'px Arial, sans-serif';
        this.ctx.textAlign = 'center';
        this.ctx.fillText('🖼️', props.width / 2, props.height / 2 + 8);

        // Texte descriptif
        if (props.width > 80 && props.height > 40) {
            this.ctx.fillStyle = '#64748b';
            this.ctx.font = '11px Arial, sans-serif';
            this.ctx.fillText('Image', props.width / 2, props.height - 15);
        }
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
