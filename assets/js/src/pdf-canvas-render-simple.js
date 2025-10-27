/**
 * PDF Canvas Simple Renderer - Système de rendu simplifié et robuste
 * Remplace le système complexe par une implémentation claire et fiable
 */

export class PDFCanvasSimpleRenderer {
    constructor(canvasInstance) {
        this.canvasInstance = canvasInstance;
        this.canvas = null;
        this.ctx = null;
    }

    initialize(canvas, ctx) {
        this.canvas = canvas;
        this.ctx = ctx;
    }

    /**
     * Rendu complet du canvas
     * C'est LA méthode principale de rendu - elle fait tout
     */
    renderComplete() {
        if (!this.canvas || !this.ctx) {
            console.error('Canvas or context not initialized');
            return;
        }

        // 1. NETTOYER LE CANVAS
        this.clearCanvas();

        // 2. DESSINER LE FOND
        this.drawBackground();

        // 3. DESSINER LA GRILLE si activée
        if (this.canvasInstance.options.showGrid) {
            this.drawGrid();
        }

        // 4. DESSINER TOUS LES ÉLÉMENTS
        for (const element of this.canvasInstance.elements.values()) {
            try {
                this.renderElement(element);
            } catch (error) {
                console.error('Error rendering element:', element.id, error);
            }
        }

        // 5. DESSINER LA SÉLECTION ET LES HANDLES si des éléments sont sélectionnés
        const selectedIds = this.canvasInstance.selectionManager.getSelectedElementIds();
        if (selectedIds.length > 0) {
            this.drawSelection(selectedIds);
            this.drawTransformationHandles(selectedIds);
        }
    }

    /**
     * Nettoie complètement le canvas
     */
    clearCanvas() {
        this.ctx.fillStyle = '#ffffff';
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
    }

    /**
     * Dessine le fond du canvas
     */
    drawBackground() {
        const bgColor = this.canvasInstance.options.backgroundColor || '#ffffff';
        this.ctx.fillStyle = bgColor;
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
    }

    /**
     * Dessine la grille d'alignement
     */
    drawGrid() {
        const gridSize = this.canvasInstance.options.gridSize || 20;
        const width = this.canvas.width;
        const height = this.canvas.height;

        this.ctx.strokeStyle = 'rgba(200, 200, 200, 0.3)';
        this.ctx.lineWidth = 0.5;

        // Lignes verticales
        for (let x = 0; x < width; x += gridSize) {
            this.ctx.beginPath();
            this.ctx.moveTo(x, 0);
            this.ctx.lineTo(x, height);
            this.ctx.stroke();
        }

        // Lignes horizontales
        for (let y = 0; y < height; y += gridSize) {
            this.ctx.beginPath();
            this.ctx.moveTo(0, y);
            this.ctx.lineTo(width, y);
            this.ctx.stroke();
        }
    }

    /**
     * Rend un élément individuel
     */
    renderElement(element) {
        const props = element.properties;

        // Sauvegarder le contexte
        this.ctx.save();

        // Appliquer la position
        this.ctx.translate(props.x || 0, props.y || 0);

        // Appliquer la rotation si présente
        if (props.rotation && props.rotation !== 0) {
            const centerX = (props.width || 100) / 2;
            const centerY = (props.height || 50) / 2;
            this.ctx.translate(centerX, centerY);
            this.ctx.rotate((props.rotation * Math.PI) / 180);
            this.ctx.translate(-centerX, -centerY);
        }

        // Appliquer l'échelle si présente
        if (props.scale && props.scale !== 100) {
            const scale = props.scale / 100;
            const centerX = (props.width || 100) / 2;
            const centerY = (props.height || 50) / 2;
            this.ctx.translate(centerX, centerY);
            this.ctx.scale(scale, scale);
            this.ctx.translate(-centerX, -centerY);
        }

        // Appliquer l'opacité
        if (props.opacity !== undefined) {
            let opacity = props.opacity;
            if (opacity > 1) opacity = opacity / 100;
            this.ctx.globalAlpha = opacity;
        }

        // Appeler le renderer approprié selon le type
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
            default:
                // Éléments WooCommerce ou autres types personnalisés
                if (this.isWooCommerceElement(element.type)) {
                    this.renderWooCommerceElement(element);
                } else {
                    this.renderGeneric(element);
                }
        }

        // Restaurer le contexte
        this.ctx.restore();
    }

    /**
     * Rend un élément texte
     */
    renderText(element) {
        const props = element.properties;
        const text = props.text || '';
        const fontSize = props.fontSize || 14;
        const color = props.color || '#000000';
        const fontFamily = props.fontFamily || 'Arial';

        // Configuration du texte
        this.ctx.font = `${props.fontWeight || 'normal'} ${fontSize}px ${fontFamily}`;
        this.ctx.fillStyle = color;
        this.ctx.textAlign = props.textAlign || 'left';
        this.ctx.textBaseline = 'top';

        // Gérer le texte multi-ligne
        const lines = text.split('\n');
        const lineHeight = fontSize * 1.2;

        lines.forEach((line, index) => {
            const y = index * lineHeight;
            this.ctx.fillText(line, 0, y);
        });

        // Dessiner la boîte de sélection autour du texte pour debug
        if (this.canvasInstance.options.showElementBounds) {
            this.ctx.strokeStyle = 'rgba(200, 200, 200, 0.5)';
            this.ctx.lineWidth = 1;
            this.ctx.strokeRect(0, 0, props.width || 100, props.height || 50);
        }
    }

    /**
     * Rend un rectangle
     */
    renderRectangle(element) {
        const props = element.properties;
        const width = props.width || 100;
        const height = props.height || 50;

        // Couleur de remplissage
        if (props.fillColor) {
            this.ctx.fillStyle = props.fillColor;
            this.ctx.fillRect(0, 0, width, height);
        }

        // Bordure
        if (props.strokeWidth && props.strokeWidth > 0) {
            this.ctx.strokeStyle = props.strokeColor || '#000000';
            this.ctx.lineWidth = props.strokeWidth;
            this.ctx.strokeRect(0, 0, width, height);
        }
    }

    /**
     * Rend un cercle
     */
    renderCircle(element) {
        const props = element.properties;
        const radius = (props.radius || 25);
        const width = props.width || 100;
        const height = props.height || 100;

        // Utiliser le rayon ou calculer depuis width/height
        const actualRadius = radius || Math.min(width, height) / 2;

        this.ctx.beginPath();
        this.ctx.arc(width / 2, height / 2, actualRadius, 0, Math.PI * 2);

        // Remplissage
        if (props.fillColor) {
            this.ctx.fillStyle = props.fillColor;
            this.ctx.fill();
        }

        // Bordure
        if (props.strokeWidth && props.strokeWidth > 0) {
            this.ctx.strokeStyle = props.strokeColor || '#000000';
            this.ctx.lineWidth = props.strokeWidth;
            this.ctx.stroke();
        }
    }

    /**
     * Rend une ligne
     */
    renderLine(element) {
        const props = element.properties;
        const x2 = props.x2 || 100;
        const y2 = props.y2 || 0;

        this.ctx.strokeStyle = props.strokeColor || '#000000';
        this.ctx.lineWidth = props.strokeWidth || 2;
        this.ctx.beginPath();
        this.ctx.moveTo(0, 0);
        this.ctx.lineTo(x2, y2);
        this.ctx.stroke();
    }

    /**
     * Rend une image
     */
    renderImage(element) {
        const props = element.properties;
        if (!props.src) return;

        const img = new Image();
        img.onload = () => {
            this.ctx.drawImage(img, 0, 0, props.width || 100, props.height || 100);
        };
        img.src = props.src;
    }

    /**
     * Rend un élément WooCommerce
     */
    renderWooCommerceElement(element) {
        const props = element.properties;

        // Afficher un texte placeholder avec le type
        this.ctx.fillStyle = '#666666';
        this.ctx.font = '12px Arial';
        this.ctx.textAlign = 'left';
        this.ctx.fillText(`[${element.type}]`, 5, 5);

        // Dessiner une boîte
        this.ctx.strokeStyle = '#cccccc';
        this.ctx.lineWidth = 1;
        this.ctx.strokeRect(0, 0, props.width || 200, props.height || 50);
    }

    /**
     * Rend un élément générique
     */
    renderGeneric(element) {
        const props = element.properties;

        // Boîte par défaut
        this.ctx.strokeStyle = '#999999';
        this.ctx.lineWidth = 1;
        this.ctx.strokeRect(0, 0, props.width || 100, props.height || 50);

        // Texte du type
        this.ctx.fillStyle = '#999999';
        this.ctx.font = '10px Arial';
        this.ctx.fillText(element.type, 3, 3);
    }

    /**
     * Vérifie si c'est un élément WooCommerce
     */
    isWooCommerceElement(type) {
        const wooTypes = [
            'product_table', 'customer_info', 'company_info', 'company_logo',
            'order_number', 'order_date', 'customer_name', 'customer_email',
            'billing_address', 'shipping_address', 'payment_method', 'order_status',
            'subtotal', 'discount', 'shipping', 'taxes', 'total', 'refund',
            'dynamic-text', 'mentions', 'woocommerce-invoice-number', 'woocommerce-invoice-date'
        ];
        return wooTypes.includes(type);
    }

    /**
     * Dessine la sélection autour des éléments sélectionnés
     */
    drawSelection(selectedIds) {
        selectedIds.forEach(elementId => {
            const element = this.canvasInstance.elements.get(elementId);
            if (!element) return;

            const props = element.properties;
            const x = props.x || 0;
            const y = props.y || 0;
            const width = props.width || 100;
            const height = props.height || 50;

            // Dessiner le rectangle de sélection
            this.ctx.strokeStyle = '#007bff';
            this.ctx.lineWidth = 2;
            this.ctx.strokeRect(x, y, width, height);

            // Remplissage semi-transparent
            this.ctx.fillStyle = 'rgba(0, 123, 255, 0.1)';
            this.ctx.fillRect(x, y, width, height);
        });
    }

    /**
     * Dessine les handles de transformation autour des éléments sélectionnés
     */
    drawTransformationHandles(selectedIds) {
        if (selectedIds.length !== 1) return; // Handles seulement pour une sélection unique

        const elementId = selectedIds[0];
        const element = this.canvasInstance.elements.get(elementId);
        if (!element) return;

        const props = element.properties;
        const x = props.x || 0;
        const y = props.y || 0;
        const width = props.width || 100;
        const height = props.height || 50;

        const handleSize = 6;
        const handles = [
            { x: x - handleSize/2, y: y - handleSize/2, position: 'nw' },
            { x: x + width/2 - handleSize/2, y: y - handleSize/2, position: 'n' },
            { x: x + width - handleSize/2, y: y - handleSize/2, position: 'ne' },
            { x: x + width - handleSize/2, y: y + height/2 - handleSize/2, position: 'e' },
            { x: x + width - handleSize/2, y: y + height - handleSize/2, position: 'se' },
            { x: x + width/2 - handleSize/2, y: y + height - handleSize/2, position: 's' },
            { x: x - handleSize/2, y: y + height - handleSize/2, position: 'sw' },
            { x: x - handleSize/2, y: y + height/2 - handleSize/2, position: 'w' }
        ];

        handles.forEach(handle => {
            this.ctx.fillStyle = '#007bff';
            this.ctx.fillRect(handle.x, handle.y, handleSize, handleSize);
            this.ctx.strokeStyle = '#ffffff';
            this.ctx.lineWidth = 1;
            this.ctx.strokeRect(handle.x, handle.y, handleSize, handleSize);
        });
    }
}

export default PDFCanvasSimpleRenderer;
