/**
 * PDF Canvas Render - Moteur de rendu ULTRA SIMPLE et DIRECT
 * Zéro complexité, zéro transformation - juste du dessin direct
 */

export class PDFCanvasRender {
    constructor(canvas, ctx) {
        this.canvas = canvas;
        this.ctx = ctx;
    }

    /**
     * Rendu SIMPLE et DIRECT - pas de contexte save/restore
     */
    render(elements, selectedIds = [], options = {}) {
        // 1. NETTOYER
        this.ctx.fillStyle = '#ffffff';
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);

        // 2. GRILLE
        if (options.showGrid) {
            this.drawGrid(options.gridSize || 20);
        }

        // 3. ÉLÉMENTS - DIRECT SANS TRANSFORMATION
        for (const element of elements.values()) {
            this.drawElement(element);
        }

        // 4. SÉLECTION
        for (const selectedId of selectedIds) {
            const element = elements.get(selectedId);
            if (element) {
                this.drawSelection(element);
            }
        }

        // 5. HANDLES
        if (selectedIds.length === 1) {
            const element = elements.get(selectedIds[0]);
            if (element) {
                this.drawHandles(element);
            }
        }
    }

    drawGrid(size) {
        this.ctx.strokeStyle = 'rgba(200, 200, 200, 0.2)';
        this.ctx.lineWidth = 0.5;

        for (let x = 0; x <= this.canvas.width; x += size) {
            this.ctx.beginPath();
            this.ctx.moveTo(x, 0);
            this.ctx.lineTo(x, this.canvas.height);
            this.ctx.stroke();
        }

        for (let y = 0; y <= this.canvas.height; y += size) {
            this.ctx.beginPath();
            this.ctx.moveTo(0, y);
            this.ctx.lineTo(this.canvas.width, y);
            this.ctx.stroke();
        }
    }

    /**
     * Dessine UN élément - SIMPLE ET DIRECT
     */
    drawElement(element) {
        const p = element.properties || {};
        const x = p.x || 0;
        const y = p.y || 0;
        const width = p.width || 100;
        const height = p.height || 50;
        const type = element.type;

        // Pas de transformations - juste une boîte avec le texte à la position X,Y
        switch (type) {
            case 'text':
                this.drawTextElement(x, y, width, height, p);
                break;
            case 'rectangle':
            case 'shape-rectangle':
                this.drawRectangleElement(x, y, width, height, p);
                break;
            case 'circle':
            case 'shape-circle':
                this.drawCircleElement(x, y, width, height, p);
                break;
            case 'line':
            case 'shape-line':
                this.drawLineElement(x, y, p);
                break;
            default:
                // WooCommerce ou autre
                this.drawWoocommerceElement(x, y, width, height, type);
        }
    }

    drawTextElement(x, y, width, height, p) {
        const text = p.text || '';
        const fontSize = p.fontSize || 14;
        const color = p.color || '#000000';

        this.ctx.fillStyle = '#f9f9f9';
        this.ctx.fillRect(x, y, width, height);

        this.ctx.strokeStyle = '#cccccc';
        this.ctx.lineWidth = 1;
        this.ctx.strokeRect(x, y, width, height);

        this.ctx.fillStyle = color;
        this.ctx.font = `${fontSize}px Arial`;
        this.ctx.textAlign = 'left';
        this.ctx.textBaseline = 'top';

        const lines = text.split('\n');
        lines.forEach((line, i) => {
            this.ctx.fillText(line, x + 5, y + 5 + i * fontSize * 1.2);
        });
    }

    drawRectangleElement(x, y, width, height, p) {
        const fillColor = p.fillColor || '#ffffff';
        const strokeColor = p.strokeColor || '#cccccc';

        this.ctx.fillStyle = fillColor;
        this.ctx.fillRect(x, y, width, height);

        this.ctx.strokeStyle = strokeColor;
        this.ctx.lineWidth = p.strokeWidth || 1;
        this.ctx.strokeRect(x, y, width, height);
    }

    drawCircleElement(x, y, width, height, p) {
        const radius = Math.min(width, height) / 2;
        const fillColor = p.fillColor || '#ffffff';
        const strokeColor = p.strokeColor || '#cccccc';

        this.ctx.fillStyle = fillColor;
        this.ctx.beginPath();
        this.ctx.arc(x + width / 2, y + height / 2, radius, 0, Math.PI * 2);
        this.ctx.fill();

        this.ctx.strokeStyle = strokeColor;
        this.ctx.lineWidth = p.strokeWidth || 1;
        this.ctx.stroke();
    }

    drawLineElement(x, y, p) {
        const x2 = p.x2 || 100;
        const y2 = p.y2 || 0;

        this.ctx.strokeStyle = p.strokeColor || '#000000';
        this.ctx.lineWidth = p.strokeWidth || 2;
        this.ctx.beginPath();
        this.ctx.moveTo(x, y);
        this.ctx.lineTo(x + x2, y + y2);
        this.ctx.stroke();
    }

    drawWoocommerceElement(x, y, width, height, type) {
        // Couleur distincte pour chaque type
        const colors = {
            'product_table': '#e8f4f8',
            'customer_info': '#fff9e6',
            'company_info': '#e8f5e9',
            'order_number': '#fce4ec',
            'customer_name': '#ffe0b2',
            'billing_address': '#f1f8e9',
            'payment_method': '#e0f2f1',
            'subtotal': '#fffde7',
            'total': '#ffe0b2',
            'discount': '#ffccbc'
        };

        const bgColor = colors[type] || '#f5f5f5';

        // Dessiner la boîte
        this.ctx.fillStyle = bgColor;
        this.ctx.fillRect(x, y, width, height);

        this.ctx.strokeStyle = '#999999';
        this.ctx.lineWidth = 1;
        this.ctx.strokeRect(x, y, width, height);

        // Texte du type avec icône
        const icons = {
            'product_table': '📦',
            'customer_info': '👤',
            'company_info': '🏢',
            'order_number': '#️⃣',
            'customer_name': '📝',
            'billing_address': '📍',
            'payment_method': '💳',
            'subtotal': '💰',
            'total': '💵',
            'discount': '🏷️'
        };

        const icon = icons[type] || '⚙️';

        this.ctx.fillStyle = '#333';
        this.ctx.font = 'bold 12px Arial';
        this.ctx.textAlign = 'left';
        this.ctx.textBaseline = 'top';
        this.ctx.fillText(icon + ' ' + type, x + 5, y + 5);

        // Texte fictif
        this.ctx.font = '10px Arial';
        this.ctx.fillStyle = '#666';

        const dummyTexts = {
            'order_number': 'Commande #12345',
            'customer_name': 'Marie Dupont',
            'billing_address': '123 Rue de Paris',
            'payment_method': 'Carte bancaire',
            'subtotal': 'Sous-total: 250€',
            'total': 'Total: 298.50€',
            'discount': 'Remise: -15€',
            'product_table': '3 produits',
            'customer_info': 'Informations client',
            'company_info': 'TechCorp SARL'
        };

        const text = dummyTexts[type] || '[' + type + ']';
        this.ctx.fillText(text, x + 5, y + 20);
    }

    drawSelection(element) {
        const x = element.properties?.x || 0;
        const y = element.properties?.y || 0;
        const width = element.properties?.width || 100;
        const height = element.properties?.height || 50;

        this.ctx.strokeStyle = '#007bff';
        this.ctx.lineWidth = 2;
        this.ctx.strokeRect(x, y, width, height);

        this.ctx.fillStyle = 'rgba(0, 123, 255, 0.1)';
        this.ctx.fillRect(x, y, width, height);
    }

    drawHandles(element) {
        const x = element.properties?.x || 0;
        const y = element.properties?.y || 0;
        const width = element.properties?.width || 100;
        const height = element.properties?.height || 50;
        const handleSize = 6;

        const corners = [
            [x - handleSize / 2, y - handleSize / 2],
            [x + width / 2 - handleSize / 2, y - handleSize / 2],
            [x + width - handleSize / 2, y - handleSize / 2],
            [x + width - handleSize / 2, y + height / 2 - handleSize / 2],
            [x + width - handleSize / 2, y + height - handleSize / 2],
            [x + width / 2 - handleSize / 2, y + height - handleSize / 2],
            [x - handleSize / 2, y + height - handleSize / 2],
            [x - handleSize / 2, y + height / 2 - handleSize / 2]
        ];

        corners.forEach(([hx, hy]) => {
            this.ctx.fillStyle = '#007bff';
            this.ctx.fillRect(hx, hy, handleSize, handleSize);
            this.ctx.strokeStyle = '#ffffff';
            this.ctx.lineWidth = 1;
            this.ctx.strokeRect(hx, hy, handleSize, handleSize);
        });
    }
}

export default PDFCanvasRender;
