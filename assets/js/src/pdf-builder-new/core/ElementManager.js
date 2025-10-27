/**
 * Element Manager - Gestion des éléments du canvas
 * CRUD complet pour les éléments avec validation
 */

export class ElementManager {
    constructor(pdfBuilder) {
        this.pdfBuilder = pdfBuilder;
        this.elements = new Map();
        this.elementIdCounter = 1;
        this.elementTypes = new Set([
            'rectangle', 'circle', 'text', 'line', 'image',
            'product_table', 'product_image', 'product_title', 'product_price'
        ]);
    }

    /**
     * Ajout d'un élément
     */
    addElement(type, properties = {}) {
        if (!this.elementTypes.has(type)) {
            throw new Error(`Type d'élément non supporté: ${type}`);
        }

        const elementId = properties.id || `element_${this.elementIdCounter++}`;

        if (this.elements.has(elementId)) {
            throw new Error(`Élément avec l'ID ${elementId} existe déjà`);
        }

        const element = {
            id: elementId,
            type,
            ...this._getDefaultProperties(type),
            ...properties,
            createdAt: Date.now(),
            updatedAt: Date.now()
        };

        // Validation des propriétés
        this._validateElement(element);

        this.elements.set(elementId, element);
        this.pdfBuilder.emit('elementadded', { element });

        return elementId;
    }

    /**
     * Suppression d'un élément
     */
    removeElement(elementId) {
        if (!this.elements.has(elementId)) {
            return false;
        }

        const element = this.elements.get(elementId);
        this.elements.delete(elementId);
        this.pdfBuilder.emit('elementremoved', { element });

        return true;
    }

    /**
     * Mise à jour d'un élément
     */
    updateElement(elementId, updates) {
        if (!this.elements.has(elementId)) {
            throw new Error(`Élément ${elementId} introuvable`);
        }

        const element = this.elements.get(elementId);
        const updatedElement = {
            ...element,
            ...updates,
            updatedAt: Date.now()
        };

        // Validation
        this._validateElement(updatedElement);

        this.elements.set(elementId, updatedElement);
        this.pdfBuilder.emit('elementupdated', { element: updatedElement, previous: element });

        return updatedElement;
    }

    /**
     * Récupération d'un élément
     */
    getElement(elementId) {
        return this.elements.get(elementId) || null;
    }

    /**
     * Récupération de tous les éléments
     */
    getAllElements() {
        return Array.from(this.elements.values());
    }

    /**
     * Recherche d'élément à une position
     */
    getElementAtPoint(point) {
        // Recherche en ordre inverse (dernier ajouté = premier trouvé)
        const elements = Array.from(this.elements.values()).reverse();

        for (const element of elements) {
            if (this._isPointInElement(point, element)) {
                return element;
            }
        }

        return null;
    }

    /**
     * Vérification si un point est dans un élément
     * @private
     */
    _isPointInElement(point, element) {
        const { x = 0, y = 0, width = 100, height = 50 } = element;

        return point.x >= x && point.x <= x + width &&
               point.y >= y && point.y <= y + height;
    }

    /**
     * Propriétés par défaut selon le type
     * @private
     */
    _getDefaultProperties(type) {
        const defaults = {
            rectangle: {
                x: 50,
                y: 50,
                width: 100,
                height: 50,
                fillColor: '#cccccc',
                strokeColor: '#000000',
                strokeWidth: 1
            },
            circle: {
                x: 50,
                y: 50,
                width: 100,
                height: 100,
                fillColor: '#cccccc',
                strokeColor: '#000000',
                strokeWidth: 1
            },
            text: {
                x: 50,
                y: 50,
                width: 200,
                height: 30,
                text: 'Nouveau texte',
                fontSize: 16,
                fontFamily: 'Arial',
                color: '#000000',
                align: 'left'
            },
            line: {
                x: 50,
                y: 50,
                x2: 150,
                y2: 50,
                strokeColor: '#000000',
                strokeWidth: 2
            },
            image: {
                x: 50,
                y: 50,
                width: 100,
                height: 100,
                image: null
            },
            product_table: {
                x: 50,
                y: 50,
                width: 400,
                height: 200,
                columns: ['Image', 'Nom', 'Prix', 'Quantité'],
                rows: []
            },
            product_image: {
                x: 50,
                y: 50,
                width: 100,
                height: 100,
                productId: null
            },
            product_title: {
                x: 50,
                y: 130,
                width: 200,
                height: 30,
                productId: null,
                fontSize: 16,
                fontFamily: 'Arial',
                color: '#000000'
            },
            product_price: {
                x: 50,
                y: 160,
                width: 100,
                height: 25,
                productId: null,
                fontSize: 14,
                fontFamily: 'Arial',
                color: '#008000'
            }
        };

        return defaults[type] || {};
    }

    /**
     * Validation d'un élément
     * @private
     */
    _validateElement(element) {
        if (!element.id) {
            throw new Error('L\'élément doit avoir un ID');
        }

        if (!element.type) {
            throw new Error('L\'élément doit avoir un type');
        }

        // Validation des dimensions
        if (element.width !== undefined && element.width <= 0) {
            throw new Error('La largeur doit être positive');
        }

        if (element.height !== undefined && element.height <= 0) {
            throw new Error('La hauteur doit être positive');
        }

        // Validation des couleurs
        if (element.fillColor && !this._isValidColor(element.fillColor)) {
            throw new Error('Couleur de remplissage invalide');
        }

        if (element.strokeColor && !this._isValidColor(element.strokeColor)) {
            throw new Error('Couleur de bordure invalide');
        }

        if (element.color && !this._isValidColor(element.color)) {
            throw new Error('Couleur de texte invalide');
        }
    }

    /**
     * Validation d'une couleur
     * @private
     */
    _isValidColor(color) {
        // Support des formats hex, rgb, rgba, hsl, hsla et noms de couleur
        const colorRegex = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$|^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$|^rgba\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3}),\s*(0|1|0?\.\d+)\)$|^hsl\((\d{1,3}),\s*(\d{1,3})%,\s*(\d{1,3})%\)$|^hsla\((\d{1,3}),\s*(\d{1,3})%,\s*(\d{1,3})%,\s*(0|1|0?\.\d+)\)$|^[a-zA-Z]+$/;
        return colorRegex.test(color);
    }

    /**
     * Duplication d'un élément
     */
    duplicateElement(elementId, offsetX = 20, offsetY = 20) {
        const original = this.getElement(elementId);
        if (!original) {
            throw new Error(`Élément ${elementId} introuvable`);
        }

        const duplicate = {
            ...original,
            id: undefined, // Générera un nouvel ID
            x: (original.x || 0) + offsetX,
            y: (original.y || 0) + offsetY,
            createdAt: Date.now(),
            updatedAt: Date.now()
        };

        return this.addElement(duplicate.type, duplicate);
    }

    /**
     * Déplacement d'un élément
     */
    moveElement(elementId, deltaX, deltaY) {
        const element = this.getElement(elementId);
        if (!element) return;

        return this.updateElement(elementId, {
            x: (element.x || 0) + deltaX,
            y: (element.y || 0) + deltaY
        });
    }

    /**
     * Redimensionnement d'un élément
     */
    resizeElement(elementId, newWidth, newHeight) {
        return this.updateElement(elementId, {
            width: Math.max(1, newWidth),
            height: Math.max(1, newHeight)
        });
    }

    /**
     * Rotation d'un élément
     */
    rotateElement(elementId, angle) {
        return this.updateElement(elementId, {
            rotation: angle % 360
        });
    }

    /**
     * Export des éléments
     */
    exportElements() {
        return {
            version: '1.0',
            elements: this.getAllElements(),
            exportedAt: Date.now()
        };
    }

    /**
     * Import d'éléments
     */
    importElements(data) {
        if (!data.elements || !Array.isArray(data.elements)) {
            throw new Error('Format d\'import invalide');
        }

        const importedIds = [];

        data.elements.forEach(elementData => {
            try {
                const elementId = this.addElement(elementData.type, elementData);
                importedIds.push(elementId);
            } catch (error) {
                console.warn('Erreur lors de l\'import d\'un élément:', error);
            }
        });

        this.pdfBuilder.emit('elementsimported', { importedIds });
        return importedIds;
    }

    /**
     * Nettoyage des éléments orphelins
     */
    cleanup() {
        // Supprimer les éléments avec des propriétés invalides
        const toRemove = [];

        this.elements.forEach((element, id) => {
            try {
                this._validateElement(element);
            } catch (error) {
                console.warn(`Élément ${id} invalide, suppression:`, error);
                toRemove.push(id);
            }
        });

        toRemove.forEach(id => this.removeElement(id));

        return toRemove.length;
    }
}