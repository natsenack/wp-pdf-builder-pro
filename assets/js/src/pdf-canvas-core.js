/**
 * PDF Canvas Core - Engine de rendu minimaliste et fiable
 * Complètement indépendant, sans dépendances externes
 * Rendu direct sur Canvas HTML5
 */

export class PDFCanvasCore {
    constructor(canvas, ctx) {
        this.canvas = canvas;
        this.ctx = ctx;
        this.canvasWidth = canvas.width;
        this.canvasHeight = canvas.height;
    }

    /**
     * Rendu complet - C'est LA seule méthode publique
     * @param {Map} elements - Map des éléments à rendre
     * @param {Array} selectedIds - IDs des éléments sélectionnés
     * @param {Object} options - Options du canvas
     */
    renderAll(elements, selectedIds = [], options = {}) {
        // console.log('🎨 PDFCanvasCore.renderAll() CALLED - Éléments:', elements.size, 'Canvas:', this.canvasWidth + 'x' + this.canvasHeight);
        
        // Étape 1: Nettoyer complètement
        this.ctx.fillStyle = options.backgroundColor || '#ffffff';
        this.ctx.fillRect(0, 0, this.canvasWidth, this.canvasHeight);

        // Étape 2: Grille (optionnel)
        if (options.showGrid) {
            this._drawGrid(options.gridSize || 20);
        }

        // Étape 3: Tous les éléments
        const elementArray = Array.from(elements.values());
        // console.log('📦 Rendu de', elementArray.length, 'éléments:', elementArray.map(e => e.type).join(', '));
        
        elementArray.forEach((element, index) => {
            // console.log(`  ✏️ Élément ${index + 1}:`, element.type, 'props:', element.properties);
            this._renderElement(element, options);
        });

        // Étape 4: Sélection et handles
        if (selectedIds.length > 0) {
            selectedIds.forEach(id => {
                const element = elements.get(id);
                if (element) {
                    this._drawSelection(element);
                    if (selectedIds.length === 1) {
                        this._drawHandles(element);
                    }
                }
            });
        }
    }

    /**
     * Rendu d'un élément individuel (pour rendu sélectif)
     * @param {Object} element - Élément à rendre
     * @param {Object} options - Options du canvas
     */
    renderElement(element, options = {}) {
        this._renderElement(element, options);
    }

    /**
     * Rendu de la sélection d'un élément (pour rendu sélectif)
     * @param {Object} element - Élément sélectionné
     * @param {Object} options - Options du canvas
     */
    renderSelection(element, options = {}) {
        this._drawSelection(element);
        this._drawHandles(element);
    }

    /**
     * Dessine la grille
     * @private
     */
    _drawGrid(gridSize) {
        this.ctx.strokeStyle = 'rgba(200, 200, 200, 0.2)';
        this.ctx.lineWidth = 0.5;

        // Lignes verticales
        for (let x = 0; x <= this.canvasWidth; x += gridSize) {
            this.ctx.beginPath();
            this.ctx.moveTo(x, 0);
            this.ctx.lineTo(x, this.canvasHeight);
            this.ctx.stroke();
        }

        // Lignes horizontales
        for (let y = 0; y <= this.canvasHeight; y += gridSize) {
            this.ctx.beginPath();
            this.ctx.moveTo(0, y);
            this.ctx.lineTo(this.canvasWidth, y);
            this.ctx.stroke();
        }
    }

    /**
     * Rend un seul élément
     * @private
     */
    _renderElement(element, options = {}) {
        const p = element.properties || {};
        const x = p.x !== undefined ? p.x : 0;
        const y = p.y !== undefined ? p.y : 0;
        const width = p.width !== undefined ? p.width : 100;
        const height = p.height !== undefined ? p.height : 50;

        // console.log(`    Position: x=${x}, y=${y}, width=${width}, height=${height}`);

        // Sauvegarder le contexte
        this.ctx.save();

        // Appliquer transformations
        this.ctx.translate(x, y);

        if (p.rotation) {
            this.ctx.translate(width / 2, height / 2);
            this.ctx.rotate((p.rotation * Math.PI) / 180);
            this.ctx.translate(-width / 2, -height / 2);
        }

        if (p.scale && p.scale !== 100) {
            const scale = p.scale / 100;
            this.ctx.translate(width / 2, height / 2);
            this.ctx.scale(scale, scale);
            this.ctx.translate(-width / 2, -height / 2);
        }

        if (p.opacity !== undefined) {
            const opacity = p.opacity > 1 ? p.opacity / 100 : p.opacity;
            this.ctx.globalAlpha = opacity;
        }

        // Mode lowQuality : rendu simplifié
        this._renderElementNormal(element);

        this.ctx.restore();
    }

    /**
     * Rendu normal complet
     * @private
     */
    _renderElementNormal(element) {
        // Rendre selon le type
        switch (element.type) {
            case 'text':
                this._drawText(element);
                break;
            case 'rectangle':
            case 'shape-rectangle':
                this._drawRectangle(element);
                break;
            case 'circle':
            case 'shape-circle':
                this._drawCircle(element);
                break;
            case 'line':
            case 'shape-line':
                this._drawLine(element);
                break;
            case 'image':
                this._drawImage(element);
                break;
            // Tous les éléments WooCommerce
            case 'product_table':
            case 'customer_info':
            case 'company_info':
            case 'company_logo':
            case 'order_number':
            case 'order_date':
            case 'customer_name':
            case 'customer_email':
            case 'billing_address':
            case 'shipping_address':
            case 'payment_method':
            case 'order_status':
            case 'subtotal':
            case 'discount':
            case 'shipping':
            case 'taxes':
            case 'total':
            case 'refund':
            case 'dynamic-text':
            case 'mentions':
            case 'woocommerce-invoice-number':
            case 'woocommerce-invoice-date':
                this._drawWooCommerceElement(element);
                break;
            default:
                this._drawPlaceholder(element);
        }
    }

    /**
     * Dessine du texte
     * @private
     */
    _drawText(element) {
        const p = element.properties;
        const text = p.text || '';
        const fontSize = p.fontSize || 14;
        const fontFamily = p.fontFamily || 'Arial';
        const color = p.color || '#000000';
        const width = p.width || 100;
        const height = p.height || 50;

        this.ctx.font = `${p.fontWeight || 'normal'} ${fontSize}px ${fontFamily}`;
        this.ctx.fillStyle = color;
        this.ctx.textAlign = p.textAlign || 'left';
        this.ctx.textBaseline = 'top';

        // Pour les mentions en mode medley, ne pas appliquer de clipping
        // pour permettre au texte de dépasser si nécessaire
        const isMedleyMentions = element.type === 'mentions' && p.mentionType === 'medley';

        if (!isMedleyMentions) {
            // Clip pour garder le texte dans les limites (pour les autres éléments)
            this.ctx.beginPath();
            this.ctx.rect(0, 0, width, height);
            this.ctx.clip();
        }

        const lines = text.split('\n');
        const lineHeight = fontSize * 1.3;

        lines.forEach((line, i) => {
            this.ctx.fillText(line, 5, 5 + i * lineHeight);
        });
    }

    /**
     * Dessine un rectangle
     * @private
     */
    _drawRectangle(element) {
        const p = element.properties;
        const width = p.width || 100;
        const height = p.height || 50;

        if (p.fillColor) {
            this.ctx.fillStyle = p.fillColor;
            this.ctx.fillRect(0, 0, width, height);
        }

        if (p.strokeWidth && p.strokeWidth > 0) {
            this.ctx.strokeStyle = p.strokeColor || '#000000';
            this.ctx.lineWidth = p.strokeWidth;
            this.ctx.strokeRect(0, 0, width, height);
        } else if (!p.fillColor) {
            // Rectangle vide par défaut
            this.ctx.strokeStyle = '#cccccc';
            this.ctx.lineWidth = 1;
            this.ctx.strokeRect(0, 0, width, height);
        }
    }

    /**
     * Dessine un cercle
     * @private
     */
    _drawCircle(element) {
        const p = element.properties;
        const width = p.width || 100;
        const height = p.height || 100;
        const radius = p.radius || Math.min(width, height) / 2;

        this.ctx.beginPath();
        this.ctx.arc(width / 2, height / 2, radius, 0, Math.PI * 2);

        if (p.fillColor) {
            this.ctx.fillStyle = p.fillColor;
            this.ctx.fill();
        }

        if (p.strokeWidth && p.strokeWidth > 0) {
            this.ctx.strokeStyle = p.strokeColor || '#000000';
            this.ctx.lineWidth = p.strokeWidth;
            this.ctx.stroke();
        } else if (!p.fillColor) {
            this.ctx.strokeStyle = '#cccccc';
            this.ctx.lineWidth = 1;
            this.ctx.stroke();
        }
    }

    /**
     * Dessine une ligne
     * @private
     */
    _drawLine(element) {
        const p = element.properties;
        const x2 = p.x2 || 100;
        const y2 = p.y2 || 0;

        this.ctx.strokeStyle = p.strokeColor || '#000000';
        this.ctx.lineWidth = p.strokeWidth || 2;
        this.ctx.beginPath();
        this.ctx.moveTo(0, 0);
        this.ctx.lineTo(x2, y2);
        this.ctx.stroke();
    }

    /**
     * Dessine une image
     * @private
     */
    _drawImage(element) {
        const p = element.properties;
        if (!p.src) return;

        const img = new Image();
        img.onload = () => {
            this.ctx.drawImage(img, 0, 0, p.width || 100, p.height || 100);
        };
        img.src = p.src;
    }

    /**
     * Dessine un placeholder pour les types inconnus
     * @private
     */
    _drawPlaceholder(element) {
        const p = element.properties;
        const width = p.width || 100;
        const height = p.height || 50;

        // Boîte de base
        this.ctx.strokeStyle = '#cccccc';
        this.ctx.lineWidth = 1;
        this.ctx.strokeRect(0, 0, width, height);

        // Texte du type
        this.ctx.fillStyle = '#999999';
        this.ctx.font = '10px Arial';
        this.ctx.textAlign = 'left';
        this.ctx.textBaseline = 'top';
        this.ctx.fillText(element.type, 3, 3);
    }

    /**
     * Dessine un élément WooCommerce avec données fictives
     * @private
     */
    _drawWooCommerceElement(element) {
        const p = element.properties;
        const width = p.width || 200;
        const height = p.height || 50;
        const type = element.type;

        // Fond avec couleur selon le type
        const colorMap = {
            'product_table': '#f0f8ff',
            'customer_info': '#fffacd',
            'company_info': '#f0fff0',
            'order_number': '#fff0f5',
            'customer_name': '#ffe4e1',
            'billing_address': '#f5f5dc',
            'payment_method': '#e0ffff',
            'subtotal': '#fff8dc',
            'total': '#ffe4b5',
            'discount': '#ffa07a'
        };

        const bgColor = colorMap[type] || '#f5f5f5';
        this.ctx.fillStyle = bgColor;
        this.ctx.fillRect(0, 0, width, height);

        // Bordure
        this.ctx.strokeStyle = '#cccccc';
        this.ctx.lineWidth = 1;
        this.ctx.strokeRect(0, 0, width, height);

        // Texte du type avec icône
        const iconMap = {
            'product_table': '📦',
            'customer_info': '👤',
            'company_info': '🏢',
            'company_logo': '🖼️',
            'order_number': '#️⃣',
            'order_date': '📅',
            'customer_name': '📝',
            'customer_email': '✉️',
            'billing_address': '📍',
            'shipping_address': '🚚',
            'payment_method': '💳',
            'order_status': '⏳',
            'subtotal': '💰',
            'discount': '🏷️',
            'shipping': '📦',
            'taxes': '🧮',
            'total': '💵',
            'refund': '↩️',
            'dynamic-text': '📄',
            'mentions': '📋',
            'woocommerce-invoice-number': '📄',
            'woocommerce-invoice-date': '📅'
        };

        const icon = iconMap[type] || '⚙️';

        this.ctx.fillStyle = '#333333';
        this.ctx.font = 'bold 11px Arial';
        this.ctx.textAlign = 'left';
        this.ctx.textBaseline = 'top';

        // Afficher l'icône et le type
        this.ctx.fillText(icon + ' ' + type, 5, 5);

        // Afficher des données fictives selon le type
        this.ctx.font = '9px Arial';
        this.ctx.fillStyle = '#666666';

        let dummyText = '';
        switch (type) {
            case 'order_number':
                dummyText = 'Commande: #12345';
                break;
            case 'order_date':
                dummyText = '27 Oct 2025';
                break;
            case 'customer_name':
                dummyText = 'Marie Dupont';
                break;
            case 'customer_email':
                dummyText = 'marie@example.com';
                break;
            case 'subtotal':
                dummyText = 'Sous-total: 250.00 €';
                break;
            case 'total':
                dummyText = 'Total: 298.50 €';
                break;
            case 'discount':
                dummyText = 'Remise: -15.00 €';
                break;
            case 'shipping':
                dummyText = 'Livraison: 10.00 €';
                break;
            case 'taxes':
                dummyText = 'TVA: 53.50 €';
                break;
            case 'payment_method':
                dummyText = 'Carte bancaire';
                break;
            case 'order_status':
                dummyText = 'Statut: Traité';
                break;
            case 'product_table':
                dummyText = '3 produits';
                break;
            case 'customer_info':
                dummyText = 'Infos client';
                break;
            case 'company_info':
                dummyText = 'TechCorp SARL';
                break;
            case 'billing_address':
                dummyText = '123 Rue de Paris';
                break;
            case 'shipping_address':
                dummyText = '456 Ave Montpellier';
                break;
            default:
                dummyText = '[' + type + ']';
        }

        if (height > 30 && dummyText) {
            this.ctx.fillText(dummyText, 5, 18);
        }
    }

    /**
     * Dessine la sélection visuelle
     * @private
     */
    _drawSelection(element) {
        const p = element.properties;
        const x = p.x || 0;
        const y = p.y || 0;
        const width = p.width || 100;
        const height = p.height || 50;

        // Rectangle de sélection
        this.ctx.strokeStyle = '#007bff';
        this.ctx.lineWidth = 2;
        this.ctx.strokeRect(x, y, width, height);

        // Remplissage semi-transparent
        this.ctx.fillStyle = 'rgba(0, 123, 255, 0.1)';
        this.ctx.fillRect(x, y, width, height);
    }

    /**
     * Dessine les handles de redimensionnement
     * @private
     */
    _drawHandles(element) {
        const p = element.properties;
        const x = p.x || 0;
        const y = p.y || 0;
        const width = p.width || 100;
        const height = p.height || 50;

        const handleSize = 6;
        const positions = [
            { x: x - handleSize / 2, y: y - handleSize / 2 },
            { x: x + width / 2 - handleSize / 2, y: y - handleSize / 2 },
            { x: x + width - handleSize / 2, y: y - handleSize / 2 },
            { x: x + width - handleSize / 2, y: y + height / 2 - handleSize / 2 },
            { x: x + width - handleSize / 2, y: y + height - handleSize / 2 },
            { x: x + width / 2 - handleSize / 2, y: y + height - handleSize / 2 },
            { x: x - handleSize / 2, y: y + height - handleSize / 2 },
            { x: x - handleSize / 2, y: y + height / 2 - handleSize / 2 }
        ];

        positions.forEach(pos => {
            this.ctx.fillStyle = '#007bff';
            this.ctx.fillRect(pos.x, pos.y, handleSize, handleSize);
            this.ctx.strokeStyle = '#ffffff';
            this.ctx.lineWidth = 1;
            this.ctx.strokeRect(pos.x, pos.y, handleSize, handleSize);
        });
    }
}

export default PDFCanvasCore;
