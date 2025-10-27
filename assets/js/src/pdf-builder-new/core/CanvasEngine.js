/**
 * Canvas Engine - Moteur de rendu optimisé
 * Gère le rendu Canvas avec optimisation des performances
 */

export class CanvasEngine {
    constructor(canvas, ctx) {
        this.canvas = canvas;
        this.ctx = ctx;
        this.canvasWidth = canvas.width;
        this.canvasHeight = canvas.height;

        // Optimisations de rendu
        this.dirtyRegions = [];
        this.renderScheduled = false;
        this.lastRenderTime = 0;
    }

    /**
     * Effacement optimisé
     */
    clear() {
        // Effacement sélectif si des régions sont marquées
        if (this.dirtyRegions.length > 0) {
            this.dirtyRegions.forEach(region => {
                this.ctx.clearRect(region.x, region.y, region.width, region.height);
            });
            this.dirtyRegions = [];
        } else {
            // Effacement complet
            this.ctx.clearRect(0, 0, this.canvasWidth, this.canvasHeight);
        }
    }

    /**
     * Rendu d'un élément
     */
    renderElement(element, options = {}) {
        this.ctx.save();

        // Positionnement
        this.ctx.translate(element.x || 0, element.y || 0);

        // Rotation si nécessaire
        if (element.rotation) {
            this.ctx.rotate((element.rotation * Math.PI) / 180);
        }

        // Opacité
        if (element.opacity !== undefined) {
            this.ctx.globalAlpha = element.opacity;
        }

        // Rendu selon le type
        this._renderElementByType(element, options);

        // Sélection
        if (options.selected) {
            this._renderSelection(element);
        }

        this.ctx.restore();
    }

    /**
     * Rendu selon le type d'élément
     * @private
     */
    _renderElementByType(element, options) {
        switch (element.type) {
            case 'rectangle':
                this._renderRectangle(element);
                break;
            case 'circle':
                this._renderCircle(element);
                break;
            case 'text':
                this._renderText(element);
                break;
            case 'line':
                this._renderLine(element);
                break;
            case 'image':
                this._renderImage(element);
                break;
            default:
                this._renderDefault(element);
        }
    }

    /**
     * Rendu rectangle
     * @private
     */
    _renderRectangle(element) {
        const { width = 100, height = 50, fillColor = '#cccccc', strokeColor, strokeWidth = 1 } = element;

        // Fond
        if (fillColor) {
            this.ctx.fillStyle = fillColor;
            this.ctx.fillRect(0, 0, width, height);
        }

        // Bordure
        if (strokeColor) {
            this.ctx.strokeStyle = strokeColor;
            this.ctx.lineWidth = strokeWidth;
            this.ctx.strokeRect(0, 0, width, height);
        }
    }

    /**
     * Rendu cercle
     * @private
     */
    _renderCircle(element) {
        const { width = 100, height = 50, fillColor = '#cccccc', strokeColor, strokeWidth = 1 } = element;
        const radius = Math.min(width, height) / 2;
        const centerX = width / 2;
        const centerY = height / 2;

        this.ctx.beginPath();
        this.ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);

        if (fillColor) {
            this.ctx.fillStyle = fillColor;
            this.ctx.fill();
        }

        if (strokeColor) {
            this.ctx.strokeStyle = strokeColor;
            this.ctx.lineWidth = strokeWidth;
            this.ctx.stroke();
        }
    }

    /**
     * Rendu texte
     * @private
     */
    _renderText(element) {
        const {
            text = 'Texte',
            fontSize = 16,
            fontFamily = 'Arial',
            color = '#000000',
            align = 'left',
            width = 200
        } = element;

        this.ctx.font = `${fontSize}px ${fontFamily}`;
        this.ctx.fillStyle = color;
        this.ctx.textAlign = align;

        // Gestion du texte multiligne si nécessaire
        const lines = text.split('\n');
        const lineHeight = fontSize * 1.2;
        let y = 0;

        lines.forEach(line => {
            this.ctx.fillText(line, 0, y + fontSize);
            y += lineHeight;
        });
    }

    /**
     * Rendu ligne
     * @private
     */
    _renderLine(element) {
        const { x2 = 100, y2 = 0, strokeColor = '#000000', strokeWidth = 2 } = element;

        this.ctx.strokeStyle = strokeColor;
        this.ctx.lineWidth = strokeWidth;
        this.ctx.beginPath();
        this.ctx.moveTo(0, 0);
        this.ctx.lineTo(x2, y2);
        this.ctx.stroke();
    }

    /**
     * Rendu image
     * @private
     */
    _renderImage(element) {
        const { image, width = 100, height = 100 } = element;

        if (image && image.complete) {
            this.ctx.drawImage(image, 0, 0, width, height);
        } else {
            // Placeholder si l'image n'est pas chargée
            this.ctx.fillStyle = '#f0f0f0';
            this.ctx.fillRect(0, 0, width, height);
            this.ctx.strokeStyle = '#ccc';
            this.ctx.strokeRect(0, 0, width, height);
            this.ctx.fillStyle = '#666';
            this.ctx.font = '12px Arial';
            this.ctx.textAlign = 'center';
            this.ctx.fillText('Image', width / 2, height / 2 + 4);
        }
    }

    /**
     * Rendu par défaut
     * @private
     */
    _renderDefault(element) {
        this.ctx.fillStyle = '#ffcccc';
        this.ctx.fillRect(0, 0, element.width || 50, element.height || 50);
        this.ctx.strokeStyle = '#ff0000';
        this.ctx.strokeRect(0, 0, element.width || 50, element.height || 50);
    }

    /**
     * Rendu de la sélection
     * @private
     */
    _renderSelection(element) {
        const { width = 100, height = 50 } = element;

        // Rectangle de sélection
        this.ctx.strokeStyle = '#007bff';
        this.ctx.lineWidth = 2;
        this.ctx.setLineDash([5, 5]);
        this.ctx.strokeRect(-2, -2, width + 4, height + 4);
        this.ctx.setLineDash([]);

        // Poignées de redimensionnement
        this.ctx.fillStyle = '#007bff';
        const handles = [
            [0, 0], [width/2, 0], [width, 0],
            [0, height/2], [width, height/2],
            [0, height], [width/2, height], [width, height]
        ];

        handles.forEach(([x, y]) => {
            this.ctx.fillRect(x - 4, y - 4, 8, 8);
        });
    }

    /**
     * Rendu de la grille
     */
    renderGrid(gridSize = 20) {
        this.ctx.save();
        this.ctx.strokeStyle = '#e0e0e0';
        this.ctx.lineWidth = 1;

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

        this.ctx.restore();
    }

    /**
     * Marquage d'une région comme sale
     */
    markDirty(x, y, width, height) {
        this.dirtyRegions.push({ x, y, width, height });
    }

    /**
     * Rendu avec optimisation RAF
     */
    scheduleRender(callback) {
        if (this.renderScheduled) return;

        this.renderScheduled = true;

        requestAnimationFrame(() => {
            this.renderScheduled = false;
            if (callback) callback();
        });
    }
}