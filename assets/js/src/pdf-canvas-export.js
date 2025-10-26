/**
 * PDF Canvas Export Manager - Gestionnaire d'export PDF
 * Conversion du contenu canvas vers PDF avec options avancées
 */

export class PDFCanvasExportManager {
    constructor(canvasInstance) {
        this.canvasInstance = canvasInstance;
        this.jspdf = null; // Sera chargé dynamiquement

        // Configuration d'export
        this.config = {
            format: 'a4', // a4, letter, legal, custom
            orientation: 'portrait', // portrait, landscape
            unit: 'mm', // mm, cm, in, px
            quality: 2.0, // Facteur de qualité (1.0 = normal, 2.0 = haute qualité)
            compression: 'FAST', // NONE, FAST, MEDIUM, SLOW
            includeMetadata: true,
            includeLayers: true,
            backgroundColor: '#ffffff',
            margin: {
                top: 10,
                right: 10,
                bottom: 10,
                left: 10
            },
            dpi: 300, // Résolution pour l'export
            filename: 'pdf-builder-export.pdf'
        };

        // État d'export
        this.isExporting = false;
        this.exportProgress = 0;
        this.exportCallbacks = {
            onProgress: null,
            onComplete: null,
            onError: null
        };
    }

    /**
     * Initialise jsPDF (chargement dynamique)
     */
    async initializeJSPDF() {
        if (this.jspdf) return this.jspdf;

        try {
            // Charger jsPDF dynamiquement
            if (typeof window !== 'undefined' && !window.jspdf) {
                await this.loadJSPDF();
            }

            const { jsPDF } = window.jspdf;
            this.jspdf = jsPDF;
            return this.jspdf;
        } catch (error) {
            console.error('Failed to load jsPDF:', error);
            throw new Error('jsPDF library is required for PDF export');
        }
    }

    /**
     * Charge jsPDF depuis CDN
     */
    async loadJSPDF() {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    /**
     * Exporte le canvas vers PDF
     */
    async exportToPDF(options = {}) {
        if (this.isExporting) {
            throw new Error('Export already in progress');
        }

        this.isExporting = true;
        this.exportProgress = 0;

        try {
            // Fusionner les options
            const exportConfig = { ...this.config, ...options };

            // Initialiser jsPDF
            await this.initializeJSPDF();

            // Créer le document PDF
            const pdf = new this.jspdf({
                orientation: exportConfig.orientation,
                unit: exportConfig.unit,
                format: exportConfig.format,
                compress: exportConfig.compression !== 'NONE'
            });

            // Obtenir les dimensions de la page
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            const contentWidth = pageWidth - exportConfig.margin.left - exportConfig.margin.right;
            const contentHeight = pageHeight - exportConfig.margin.top - exportConfig.margin.bottom;

            // Calculer l'échelle pour adapter le canvas à la page
            const canvasBounds = this.getCanvasBounds();
            const scaleX = contentWidth / canvasBounds.width;
            const scaleY = contentHeight / canvasBounds.height;
            const scale = Math.min(scaleX, scaleY) * exportConfig.quality;

            // Positionner le contenu
            const offsetX = exportConfig.margin.left;
            const offsetY = exportConfig.margin.top;

            // Ajouter les métadonnées
            if (exportConfig.includeMetadata) {
                this.addPDFMetadata(pdf, exportConfig);
            }

            // Convertir le canvas en images par calque
            const layerImages = await this.convertLayersToImages(scale, exportConfig);

            this.emitProgress(20);

            // Ajouter chaque calque au PDF
            for (let i = 0; i < layerImages.length; i++) {
                const layerImage = layerImages[i];

                if (i > 0) {
                    pdf.addPage();
                }

                // Ajouter l'image au PDF
                pdf.addImage(
                    layerImage.data,
                    layerImage.format,
                    offsetX,
                    offsetY,
                    layerImage.width / exportConfig.quality,
                    layerImage.height / exportConfig.quality
                );

                this.emitProgress(20 + (i / layerImages.length) * 60);
            }

            this.emitProgress(90);

            // Sauvegarder le PDF
            const filename = exportConfig.filename || this.config.filename;
            pdf.save(filename);

            this.emitProgress(100);
            this.emitComplete({ filename, pageCount: layerImages.length });

            return { success: true, filename, pageCount: layerImages.length };

        } catch (error) {
            this.emitError(error);
            throw error;
        } finally {
            this.isExporting = false;
            this.exportProgress = 0;
        }
    }

    /**
     * Convertit les calques en images
     */
    async convertLayersToImages(scale, config) {
        const layers = this.canvasInstance.layersManager.getLayersInOrder();
        const images = [];

        for (const layer of layers) {
            if (!layer.visible && config.includeLayers) continue;

            const imageData = await this.convertLayerToImage(layer, scale, config);
            if (imageData) {
                images.push(imageData);
            }
        }

        return images;
    }

    /**
     * Convertit un calque en image
     */
    async convertLayerToImage(layer, scale, config) {
        // Créer un canvas temporaire pour le calque
        const tempCanvas = document.createElement('canvas');
        const tempCtx = tempCanvas.getContext('2d');

        // Définir la taille du canvas temporaire
        const canvasBounds = this.getCanvasBounds();
        tempCanvas.width = canvasBounds.width * scale;
        tempCanvas.height = canvasBounds.height * scale;

        // Appliquer la transformation d'échelle
        tempCtx.scale(scale, scale);

        // Définir le fond
        tempCtx.fillStyle = config.backgroundColor;
        tempCtx.fillRect(0, 0, canvasBounds.width, canvasBounds.height);

        // Appliquer l'opacité du calque
        if (layer.opacity < 100) {
            tempCtx.globalAlpha = layer.opacity / 100;
        }

        // Appliquer le mode de fusion
        if (layer.blendMode !== 'normal') {
            tempCtx.globalCompositeOperation = layer.blendMode;
        }

        // Rendre les éléments du calque
        const elements = this.canvasInstance.layersManager.getLayerElements(layer.id);
        for (const element of elements) {
            await this.renderElementToCanvas(tempCtx, element);
        }

        // Convertir en données d'image
        const imageData = tempCanvas.toDataURL('image/png', 0.95);

        return {
            data: imageData,
            format: 'PNG',
            width: tempCanvas.width,
            height: tempCanvas.height,
            layerId: layer.id,
            layerName: layer.name
        };
    }

    /**
     * Rend un élément sur un canvas temporaire
     */
    async renderElementToCanvas(ctx, element) {
        const props = element.properties;

        ctx.save();

        // Appliquer les transformations
        if (props.rotation) {
            ctx.translate(props.x + props.width / 2, props.y + props.height / 2);
            ctx.rotate((props.rotation * Math.PI) / 180);
            ctx.translate(-props.width / 2, -props.height / 2);
        } else {
            ctx.translate(props.x, props.y);
        }

        // Appliquer l'opacité
        if (props.opacity !== undefined) {
            ctx.globalAlpha = props.opacity / 100;
        }

        // Rendre selon le type d'élément
        switch (element.type) {
            case 'text':
                this.renderTextElement(ctx, props);
                break;
            case 'rectangle':
                this.renderRectangleElement(ctx, props);
                break;
            case 'circle':
                this.renderCircleElement(ctx, props);
                break;
            case 'line':
                this.renderLineElement(ctx, props);
                break;
            case 'image':
                await this.renderImageElement(ctx, props);
                break;
            default:
                // Élément personnalisé - utiliser le renderer principal
                this.canvasInstance.renderElement(element, ctx);
        }

        ctx.restore();
    }

    /**
     * Rend un élément texte
     */
    renderTextElement(ctx, props) {
        ctx.fillStyle = props.color || '#000000';
        ctx.font = `${props.fontSize || 12}px ${props.fontFamily || 'Arial'}`;

        if (props.bold) ctx.font = `bold ${ctx.font}`;
        if (props.italic) ctx.font = `italic ${ctx.font}`;

        ctx.textAlign = props.textAlign || 'left';
        ctx.textBaseline = 'top';

        const lines = (props.text || '').split('\n');
        const lineHeight = props.lineHeight || 1.2;
        let y = 0;

        lines.forEach(line => {
            ctx.fillText(line, 0, y);
            y += (props.fontSize || 12) * lineHeight;
        });
    }

    /**
     * Rend un élément rectangle
     */
    renderRectangleElement(ctx, props) {
        ctx.fillStyle = props.fillColor || 'transparent';
        ctx.strokeStyle = props.strokeColor || '#000000';
        ctx.lineWidth = props.strokeWidth || 1;

        if (props.fillColor) {
            ctx.fillRect(0, 0, props.width, props.height);
        }

        if (props.strokeColor && props.strokeWidth > 0) {
            ctx.strokeRect(0, 0, props.width, props.height);
        }
    }

    /**
     * Rend un élément cercle
     */
    renderCircleElement(ctx, props) {
        const centerX = props.width / 2;
        const centerY = props.height / 2;
        const radius = Math.min(props.width, props.height) / 2;

        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);

        ctx.fillStyle = props.fillColor || 'transparent';
        ctx.strokeStyle = props.strokeColor || '#000000';
        ctx.lineWidth = props.strokeWidth || 1;

        if (props.fillColor) {
            ctx.fill();
        }

        if (props.strokeColor && props.strokeWidth > 0) {
            ctx.stroke();
        }
    }

    /**
     * Rend un élément ligne
     */
    renderLineElement(ctx, props) {
        ctx.strokeStyle = props.strokeColor || '#000000';
        ctx.lineWidth = props.strokeWidth || 1;
        ctx.lineCap = props.lineCap || 'butt';
        ctx.lineJoin = props.lineJoin || 'miter';

        ctx.beginPath();
        ctx.moveTo(0, 0);
        ctx.lineTo(props.width, props.height);
        ctx.stroke();
    }

    /**
     * Rend un élément image
     */
    async renderImageElement(ctx, props) {
        if (!props.imageSrc) return;

        try {
            const img = new Image();
            await new Promise((resolve, reject) => {
                img.onload = resolve;
                img.onerror = reject;
                img.src = props.imageSrc;
            });

            ctx.drawImage(img, 0, 0, props.width, props.height);
        } catch (error) {
            console.warn('Failed to load image for PDF export:', error);
            // Dessiner un rectangle de substitution
            ctx.strokeStyle = '#ff0000';
            ctx.lineWidth = 2;
            ctx.strokeRect(0, 0, props.width, props.height);
        }
    }

    /**
     * Obtient les limites du canvas
     */
    getCanvasBounds() {
        const elements = Array.from(this.canvasInstance.elements.values());
        if (elements.length === 0) {
            return { x: 0, y: 0, width: 800, height: 600 };
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

        return {
            x: minX,
            y: minY,
            width: maxX - minX,
            height: maxY - minY
        };
    }

    /**
     * Ajoute les métadonnées au PDF
     */
    addPDFMetadata(pdf, config) {
        const now = new Date();

        pdf.setProperties({
            title: 'PDF Builder Export',
            subject: 'Generated PDF Document',
            author: 'PDF Builder Pro',
            creator: 'PDF Builder Pro',
            creationDate: now,
            modDate: now
        });

        // Ajouter des informations supplémentaires
        if (config.includeMetadata) {
            const stats = this.canvasInstance.getStats();
            pdf.setKeywords([
                `elements: ${stats.totalElements}`,
                `layers: ${stats.totalLayers}`,
                `exported: ${now.toISOString()}`,
                'pdf-builder-pro'
            ]);
        }
    }

    /**
     * Exporte vers différents formats
     */
    async exportToFormat(format, options = {}) {
        switch (format.toLowerCase()) {
            case 'pdf':
                return await this.exportToPDF(options);
            case 'png':
                return await this.exportToPNG(options);
            case 'jpeg':
            case 'jpg':
                return await this.exportToJPEG(options);
            case 'svg':
                return await this.exportToSVG(options);
            default:
                throw new Error(`Unsupported export format: ${format}`);
        }
    }

    /**
     * Exporte vers PNG
     */
    async exportToPNG(options = {}) {
        const config = { ...this.config, ...options };
        const canvas = this.canvasInstance.canvas;

        // Créer un canvas temporaire avec le fond
        const exportCanvas = document.createElement('canvas');
        const exportCtx = exportCanvas.getContext('2d');

        exportCanvas.width = canvas.width * config.quality;
        exportCanvas.height = canvas.height * config.quality;

        exportCtx.scale(config.quality, config.quality);
        exportCtx.fillStyle = config.backgroundColor;
        exportCtx.fillRect(0, 0, canvas.width, canvas.height);
        exportCtx.drawImage(canvas, 0, 0);

        const dataURL = exportCanvas.toDataURL('image/png', 0.95);
        this.downloadDataURL(dataURL, config.filename.replace('.pdf', '.png'));

        return { success: true, format: 'png' };
    }

    /**
     * Exporte vers JPEG
     */
    async exportToJPEG(options = {}) {
        const config = { ...this.config, ...options };
        const canvas = this.canvasInstance.canvas;

        const exportCanvas = document.createElement('canvas');
        const exportCtx = exportCanvas.getContext('2d');

        exportCanvas.width = canvas.width * config.quality;
        exportCanvas.height = canvas.height * config.quality;

        exportCtx.scale(config.quality, config.quality);
        exportCtx.fillStyle = config.backgroundColor;
        exportCtx.fillRect(0, 0, canvas.width, canvas.height);
        exportCtx.drawImage(canvas, 0, 0);

        const dataURL = exportCanvas.toDataURL('image/jpeg', 0.95);
        this.downloadDataURL(dataURL, config.filename.replace('.pdf', '.jpg'));

        return { success: true, format: 'jpeg' };
    }

    /**
     * Exporte vers SVG (expérimental)
     */
    async exportToSVG(options = {}) {
        // Implémentation basique SVG - peut être étendue
        const config = { ...this.config, ...options };
        const bounds = this.getCanvasBounds();

        let svg = `<svg width="${bounds.width}" height="${bounds.height}" xmlns="http://www.w3.org/2000/svg">`;

        // Ajouter un fond
        svg += `<rect width="100%" height="100%" fill="${config.backgroundColor}"/>`;

        // Convertir les éléments en SVG (implémentation basique)
        for (const [id, element] of this.canvasInstance.elements) {
            svg += this.elementToSVG(element);
        }

        svg += '</svg>';

        const blob = new Blob([svg], { type: 'image/svg+xml' });
        const url = URL.createObjectURL(blob);
        this.downloadDataURL(url, config.filename.replace('.pdf', '.svg'));

        return { success: true, format: 'svg' };
    }

    /**
     * Convertit un élément en SVG (basique)
     */
    elementToSVG(element) {
        const props = element.properties;

        switch (element.type) {
            case 'rectangle':
                return `<rect x="${props.x}" y="${props.y}" width="${props.width}" height="${props.height}" fill="${props.fillColor || 'transparent'}" stroke="${props.strokeColor || '#000'}" stroke-width="${props.strokeWidth || 1}"/>`;
            case 'circle':
                const cx = props.x + props.width / 2;
                const cy = props.y + props.height / 2;
                const r = Math.min(props.width, props.height) / 2;
                return `<circle cx="${cx}" cy="${cy}" r="${r}" fill="${props.fillColor || 'transparent'}" stroke="${props.strokeColor || '#000'}" stroke-width="${props.strokeWidth || 1}"/>`;
            case 'text':
                return `<text x="${props.x}" y="${props.y + (props.fontSize || 12)}" font-family="${props.fontFamily || 'Arial'}" font-size="${props.fontSize || 12}" fill="${props.color || '#000'}">${props.text || ''}</text>`;
            default:
                return '';
        }
    }

    /**
     * Télécharge une URL de données
     */
    downloadDataURL(dataURL, filename) {
        const link = document.createElement('a');
        link.href = dataURL;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    /**
     * Configure l'export
     */
    configure(options) {
        this.config = { ...this.config, ...options };
    }

    /**
     * Définit les callbacks d'export
     */
    setCallbacks(callbacks) {
        this.exportCallbacks = { ...this.exportCallbacks, ...callbacks };
    }

    /**
     * Émet la progression
     */
    emitProgress(progress) {
        this.exportProgress = progress;
        if (this.exportCallbacks.onProgress) {
            this.exportCallbacks.onProgress(progress);
        }
    }

    /**
     * Émet la completion
     */
    emitComplete(data) {
        if (this.exportCallbacks.onComplete) {
            this.exportCallbacks.onComplete(data);
        }
    }

    /**
     * Émet une erreur
     */
    emitError(error) {
        if (this.exportCallbacks.onError) {
            this.exportCallbacks.onError(error);
        }
    }

    /**
     * Obtient les formats d'export supportés
     */
    getSupportedFormats() {
        return ['pdf', 'png', 'jpeg', 'jpg', 'svg'];
    }

    /**
     * Obtient les statistiques d'export
     */
    getExportStats() {
        return {
            isExporting: this.isExporting,
            progress: this.exportProgress,
            supportedFormats: this.getSupportedFormats(),
            config: { ...this.config }
        };
    }

    /**
     * Nettoie les ressources
     */
    dispose() {
        this.exportCallbacks = {
            onProgress: null,
            onComplete: null,
            onError: null
        };
        this.isExporting = false;
        this.exportProgress = 0;
    }
}

export default PDFCanvasExportManager;