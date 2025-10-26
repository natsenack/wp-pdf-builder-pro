/**
 * PDF Canvas Render Utils - Utilitaires de rendu pour Vanilla JS
 * Fonctions utilitaires pour faciliter le rendu d'éléments complexes
 */

export class PDFCanvasRenderUtils {
    constructor(ctx) {
        this.ctx = ctx;
        this.cache = new Map();
    }

    /**
     * Dessine un rectangle avec coins arrondis avancés
     */
    drawRoundedRect(x, y, width, height, radius, options = {}) {
        const {
            fill = null,
            stroke = null,
            lineWidth = 1,
            shadow = null
        } = options;

        this.ctx.save();

        // Appliquer l'ombre
        if (shadow) {
            this.ctx.shadowColor = shadow.color || '#000000';
            this.ctx.shadowBlur = shadow.blur || 4;
            this.ctx.shadowOffsetX = shadow.offsetX || 2;
            this.ctx.shadowOffsetY = shadow.offsetY || 2;
        }

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

        // Remplir
        if (fill) {
            this.ctx.fillStyle = fill;
            this.ctx.fill();
        }

        // Contour
        if (stroke) {
            this.ctx.strokeStyle = stroke;
            this.ctx.lineWidth = lineWidth;
            this.ctx.stroke();
        }

        this.ctx.restore();
    }

    /**
     * Dessine du texte multiligne avec formatage avancé
     */
    drawMultilineText(text, x, y, maxWidth, options = {}) {
        const {
            fontSize = 14,
            fontFamily = 'Arial, sans-serif',
            fontWeight = 'normal',
            color = '#000000',
            lineHeight = 1.2,
            textAlign = 'left',
            verticalAlign = 'top',
            maxLines = null,
            ellipsis = '...'
        } = options;

        if (!text) return { width: 0, height: 0, lines: 0 };

        this.ctx.save();

        // Configuration de la police
        this.ctx.font = `${fontWeight} ${fontSize}px ${fontFamily}`;
        this.ctx.fillStyle = color;
        this.ctx.textAlign = textAlign;

        // Diviser le texte en lignes
        const words = text.split(' ');
        const lines = [];
        let currentLine = '';
        let totalHeight = 0;

        for (const word of words) {
            const testLine = currentLine + (currentLine ? ' ' : '') + word;
            const metrics = this.ctx.measureText(testLine);

            if (metrics.width > maxWidth && currentLine) {
                lines.push(currentLine);
                currentLine = word;
            } else {
                currentLine = testLine;
            }
        }

        if (currentLine) {
            lines.push(currentLine);
        }

        // Limiter le nombre de lignes si spécifié
        if (maxLines && lines.length > maxLines) {
            lines.splice(maxLines - 1);
            const lastLine = lines[lines.length - 1];
            const ellipsisWidth = this.ctx.measureText(ellipsis).width;

            while (lines.length > 0 &&
                   this.ctx.measureText(lastLine + ellipsis).width > maxWidth) {
                const words = lastLine.split(' ');
                words.pop();
                lines[lines.length - 1] = words.join(' ');
            }

            lines[lines.length - 1] += ellipsis;
        }

        // Calculer la hauteur totale
        totalHeight = lines.length * (fontSize * lineHeight);

        // Ajuster la position verticale
        let startY = y;
        if (verticalAlign === 'middle') {
            startY = y - totalHeight / 2 + fontSize / 2;
        } else if (verticalAlign === 'bottom') {
            startY = y - totalHeight + fontSize;
        }

        // Rendre chaque ligne
        for (let i = 0; i < lines.length; i++) {
            const lineY = startY + i * (fontSize * lineHeight);
            this.ctx.fillText(lines[i], x, lineY);
        }

        this.ctx.restore();

        return {
            width: maxWidth,
            height: totalHeight,
            lines: lines.length
        };
    }

    /**
     * Dessine une forme complexe (polygone, étoile, etc.)
     */
    drawShape(type, x, y, size, options = {}) {
        const cacheKey = `${type}_${size}_${JSON.stringify(options)}`;

        if (this.cache.has(cacheKey)) {
            const cached = this.cache.get(cacheKey);
            this.drawCachedShape(cached, x, y);
            return;
        }

        const shape = this.createShape(type, size, options);
        this.cache.set(cacheKey, shape);
        this.drawCachedShape(shape, x, y);
    }

    /**
     * Crée une forme géométrique
     */
    createShape(type, size, options) {
        const { sides = 6, spikes = 5, rotation = 0 } = options;
        const points = [];
        const centerX = size / 2;
        const centerY = size / 2;
        const radius = size / 2;

        switch (type) {
            case 'polygon':
                for (let i = 0; i < sides; i++) {
                    const angle = (i * 2 * Math.PI) / sides + rotation;
                    points.push({
                        x: centerX + radius * Math.cos(angle),
                        y: centerY + radius * Math.sin(angle)
                    });
                }
                break;

            case 'star':
                for (let i = 0; i < spikes * 2; i++) {
                    const angle = (i * Math.PI) / spikes + rotation;
                    const r = i % 2 === 0 ? radius : radius * 0.5;
                    points.push({
                        x: centerX + r * Math.cos(angle),
                        y: centerY + r * Math.sin(angle)
                    });
                }
                break;

            case 'arrow':
                points.push(
                    { x: 0, y: size * 0.3 },
                    { x: size * 0.7, y: size * 0.3 },
                    { x: size * 0.7, y: size * 0.2 },
                    { x: size, y: size * 0.5 },
                    { x: size * 0.7, y: size * 0.8 },
                    { x: size * 0.7, y: size * 0.7 },
                    { x: 0, y: size * 0.7 }
                );
                break;
        }

        return { points, type };
    }

    /**
     * Dessine une forme mise en cache
     */
    drawCachedShape(shape, x, y) {
        this.ctx.save();
        this.ctx.translate(x, y);

        this.ctx.beginPath();
        if (shape.points.length > 0) {
            this.ctx.moveTo(shape.points[0].x, shape.points[0].y);
            for (let i = 1; i < shape.points.length; i++) {
                this.ctx.lineTo(shape.points[i].x, shape.points[i].y);
            }
            this.ctx.closePath();
        }

        // Appliquer les styles
        this.ctx.fillStyle = '#e5e7eb';
        this.ctx.fill();
        this.ctx.strokeStyle = '#64748b';
        this.ctx.lineWidth = 1;
        this.ctx.stroke();

        this.ctx.restore();
    }

    /**
     * Dessine une grille d'arrière-plan
     */
    drawGrid(width, height, gridSize = 20, options = {}) {
        const {
            color = '#f0f0f0',
            lineWidth = 1,
            showLabels = false,
            labelSize = 10
        } = options;

        this.ctx.save();
        this.ctx.strokeStyle = color;
        this.ctx.lineWidth = lineWidth;
        this.ctx.font = `${labelSize}px monospace`;
        this.ctx.fillStyle = '#999';

        // Lignes verticales
        for (let x = 0; x <= width; x += gridSize) {
            this.ctx.beginPath();
            this.ctx.moveTo(x, 0);
            this.ctx.lineTo(x, height);
            this.ctx.stroke();

            if (showLabels && x > 0) {
                this.ctx.fillText(x.toString(), x + 2, labelSize + 2);
            }
        }

        // Lignes horizontales
        for (let y = 0; y <= height; y += gridSize) {
            this.ctx.beginPath();
            this.ctx.moveTo(0, y);
            this.ctx.lineTo(width, y);
            this.ctx.stroke();

            if (showLabels && y > 0) {
                this.ctx.fillText(y.toString(), 2, y - 2);
            }
        }

        this.ctx.restore();
    }

    /**
     * Dessine des poignées de redimensionnement
     */
    drawResizeHandles(element, handleSize = 8) {
        const props = element.properties;
        const handles = [
            { x: props.x, y: props.y, cursor: 'nw-resize' },
            { x: props.x + props.width, y: props.y, cursor: 'ne-resize' },
            { x: props.x + props.width, y: props.y + props.height, cursor: 'se-resize' },
            { x: props.x, y: props.y + props.height, cursor: 'sw-resize' }
        ];

        this.ctx.save();
        this.ctx.fillStyle = '#007bff';
        this.ctx.strokeStyle = '#ffffff';
        this.ctx.lineWidth = 1;

        handles.forEach(handle => {
            // Cercle de fond
            this.ctx.beginPath();
            this.ctx.arc(handle.x, handle.y, handleSize / 2, 0, 2 * Math.PI);
            this.ctx.fill();
            this.ctx.stroke();

            // Indicateur de redimensionnement
            this.ctx.fillStyle = '#ffffff';
            this.ctx.font = `${handleSize - 2}px monospace`;
            this.ctx.textAlign = 'center';
            this.ctx.fillText('↔', handle.x, handle.y + 2);
        });

        this.ctx.restore();
    }

    /**
     * Applique des effets visuels avancés
     */
    applyAdvancedEffects(element, effects = {}) {
        const props = element.properties;

        // Effet de brillance
        if (effects.brightness && effects.brightness !== 100) {
            this.ctx.filter = `brightness(${effects.brightness}%)`;
        }

        // Effet de contraste
        if (effects.contrast && effects.contrast !== 100) {
            this.ctx.filter += ` contrast(${effects.contrast}%)`;
        }

        // Effet de saturation
        if (effects.saturate && effects.saturate !== 100) {
            this.ctx.filter += ` saturate(${effects.saturate}%)`;
        }

        // Effet de flou
        if (effects.blur && effects.blur > 0) {
            this.ctx.filter += ` blur(${effects.blur}px)`;
        }

        // Effet de teinte
        if (effects.hueRotate && effects.hueRotate !== 0) {
            this.ctx.filter += ` hue-rotate(${effects.hueRotate}deg)`;
        }
    }

    /**
     * Mesure les dimensions du texte
     */
    measureText(text, options = {}) {
        const {
            fontSize = 14,
            fontFamily = 'Arial, sans-serif',
            fontWeight = 'normal'
        } = options;

        this.ctx.save();
        this.ctx.font = `${fontWeight} ${fontSize}px ${fontFamily}`;

        const metrics = this.ctx.measureText(text);
        const height = fontSize * 1.2; // Estimation de la hauteur

        this.ctx.restore();

        return {
            width: metrics.width,
            height: height,
            actualBoundingBoxAscent: metrics.actualBoundingBoxAscent || 0,
            actualBoundingBoxDescent: metrics.actualBoundingBoxDescent || 0
        };
    }

    /**
     * Crée un gradient linéaire
     */
    createLinearGradient(x1, y1, x2, y2, colors) {
        const gradient = this.ctx.createLinearGradient(x1, y1, x2, y2);

        colors.forEach((color, index) => {
            const position = colors.length > 1 ? index / (colors.length - 1) : 0;
            gradient.addColorStop(position, color);
        });

        return gradient;
    }

    /**
     * Crée un gradient radial
     */
    createRadialGradient(x1, y1, r1, x2, y2, r2, colors) {
        const gradient = this.ctx.createRadialGradient(x1, y1, r1, x2, y2, r2);

        colors.forEach((color, index) => {
            const position = colors.length > 1 ? index / (colors.length - 1) : 0;
            gradient.addColorStop(position, color);
        });

        return gradient;
    }

    /**
     * Dessine une ligne avec style avancé
     */
    drawStyledLine(x1, y1, x2, y2, options = {}) {
        const {
            color = '#000000',
            width = 1,
            style = 'solid',
            cap = 'butt',
            dash = []
        } = options;

        this.ctx.save();
        this.ctx.strokeStyle = color;
        this.ctx.lineWidth = width;
        this.ctx.lineCap = cap;

        // Style de ligne
        if (style === 'dashed') {
            this.ctx.setLineDash([5, 5]);
        } else if (style === 'dotted') {
            this.ctx.setLineDash([2, 2]);
        } else if (dash.length > 0) {
            this.ctx.setLineDash(dash);
        }

        this.ctx.beginPath();
        this.ctx.moveTo(x1, y1);
        this.ctx.lineTo(x2, y2);
        this.ctx.stroke();

        this.ctx.restore();
    }

    /**
     * Dessine une courbe de Bézier
     */
    drawBezierCurve(points, options = {}) {
        const { tension = 0.5, close = false } = options;

        if (points.length < 2) return;

        this.ctx.save();

        // Appliquer les styles
        if (options.color) this.ctx.strokeStyle = options.color;
        if (options.width) this.ctx.lineWidth = options.width;
        if (options.fill) this.ctx.fillStyle = options.fill;

        this.ctx.beginPath();
        this.ctx.moveTo(points[0].x, points[0].y);

        // Courbe simple si 2 points
        if (points.length === 2) {
            this.ctx.lineTo(points[1].x, points[1].y);
        } else {
            // Courbe de Bézier quadratique pour plusieurs points
            for (let i = 1; i < points.length; i++) {
                const prev = points[i - 1];
                const curr = points[i];
                const next = points[i + 1] || curr;

                const cp1x = prev.x + (curr.x - prev.x) * tension;
                const cp1y = prev.y + (curr.y - prev.y) * tension;
                const cp2x = curr.x - (next.x - prev.x) * tension;
                const cp2y = curr.y - (next.y - prev.y) * tension;

                this.ctx.bezierCurveTo(cp1x, cp1y, cp2x, cp2y, curr.x, curr.y);
            }
        }

        if (close) {
            this.ctx.closePath();
        }

        if (options.fill) {
            this.ctx.fill();
        }

        this.ctx.stroke();
        this.ctx.restore();
    }

    /**
     * Efface le cache
     */
    clearCache() {
        this.cache.clear();
    }

    /**
     * Obtient les statistiques du cache
     */
    getCacheStats() {
        return {
            size: this.cache.size,
            keys: Array.from(this.cache.keys())
        };
    }
}

// Fonctions utilitaires statiques
export const RenderUtils = {
    /**
     * Convertit des degrés en radians
     */
    degToRad(degrees) {
        return degrees * (Math.PI / 180);
    },

    /**
     * Convertit des radians en degrés
     */
    radToDeg(radians) {
        return radians * (180 / Math.PI);
    },

    /**
     * Calcule la distance entre deux points
     */
    distance(x1, y1, x2, y2) {
        return Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y2 - y1, 2));
    },

    /**
     * Calcule l'angle entre deux points
     */
    angle(x1, y1, x2, y2) {
        return Math.atan2(y2 - y1, x2 - x1);
    },

    /**
     * Vérifie si un point est dans un rectangle
     */
    pointInRect(px, py, rx, ry, rw, rh) {
        return px >= rx && px <= rx + rw && py >= ry && py <= ry + rh;
    },

    /**
     * Vérifie si un point est dans un cercle
     */
    pointInCircle(px, py, cx, cy, radius) {
        return this.distance(px, py, cx, cy) <= radius;
    },

    /**
     * Génère des coordonnées pour une forme régulière
     */
    generateRegularPolygon(sides, radius, centerX = 0, centerY = 0) {
        const points = [];
        for (let i = 0; i < sides; i++) {
            const angle = (i * 2 * Math.PI) / sides - Math.PI / 2;
            points.push({
                x: centerX + radius * Math.cos(angle),
                y: centerY + radius * Math.sin(angle)
            });
        }
        return points;
    },

    /**
     * Interpole entre deux valeurs
     */
    lerp(start, end, factor) {
        return start + (end - start) * factor;
    },

    /**
     * Limite une valeur entre min et max
     */
    clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }
};

export default PDFCanvasRenderUtils;