/**
 * PDF Canvas Drag & Drop Manager - Gestionnaire de glisser-déposer
 * Permet de glisser des éléments depuis la bibliothèque vers le canvas
 */

function PDFCanvasDragDropManager(canvasInstance) {
    this.canvasInstance = canvasInstance;
    this.isDragging = false;
    this.dragElement = null;
    this.dragOffset = null;
    this.dropZones = new Map();

    this.init();
}

/**
 * Initialise le gestionnaire
 */
PDFCanvasDragDropManager.prototype.init = function() {
    this.setupGlobalListeners();
};

/**
 * Configure les écouteurs d'événements globaux
 */
PDFCanvasDragDropManager.prototype.setupGlobalListeners = function() {
    document.addEventListener('dragstart', this.handleDragStart.bind(this));
    document.addEventListener('dragend', this.handleDragEnd.bind(this));
    document.addEventListener('dragover', this.handleDragOver.bind(this));
    document.addEventListener('drop', this.handleDrop.bind(this));
};

/**
 * Gère le début du glisser
 */
PDFCanvasDragDropManager.prototype.handleDragStart = function(event) {
    const target = event.target;

    // Vérifier si c'est un élément de la bibliothèque
    const elementType = target.getAttribute('data-element-type');
    if (!elementType) return;

    this.isDragging = true;
    this.dragElement = {
        type: elementType,
        properties: this.getDefaultPropertiesForType(elementType)
    };

    // Créer un feedback visuel
    event.dataTransfer.effectAllowed = 'copy';
    event.dataTransfer.setData('application/json', JSON.stringify(this.dragElement));

    // Ajouter une classe CSS pour le feedback
    document.body.classList.add('pdf-builder-dragging');
};

/**
 * Gère la fin du glisser
 */
PDFCanvasDragDropManager.prototype.handleDragEnd = function(event) {
    this.isDragging = false;
    this.dragElement = null;
    this.dragOffset = null;

    // Retirer la classe CSS
    document.body.classList.remove('pdf-builder-dragging');
};

/**
 * Gère le survol pendant le glisser
 */
PDFCanvasDragDropManager.prototype.handleDragOver = function(event) {
    if (!this.isDragging) return;

    // Vérifier si on survole le canvas
    const canvasRect = this.canvasInstance.canvas.getBoundingClientRect();
    const isOverCanvas = event.clientX >= canvasRect.left &&
                        event.clientX <= canvasRect.right &&
                        event.clientY >= canvasRect.top &&
                        event.clientY <= canvasRect.bottom;

    if (isOverCanvas) {
        event.preventDefault();
        event.dataTransfer.dropEffect = 'copy';

        // Calculer la position sur le canvas
        const point = this.canvasInstance.getMousePosition(event);
        this.dragOffset = point;

        // Mettre à jour le rendu pour montrer le preview
        this.canvasInstance.render();
    }
};

/**
 * Gère le dépôt
 */
PDFCanvasDragDropManager.prototype.handleDrop = function(event) {
    if (!this.isDragging || !this.dragElement) return;

    // Vérifier si on dépose sur le canvas
    const canvasRect = this.canvasInstance.canvas.getBoundingClientRect();
    const isOverCanvas = event.clientX >= canvasRect.left &&
                        event.clientX <= canvasRect.right &&
                        event.clientY >= canvasRect.top &&
                        event.clientY <= canvasRect.bottom;

    if (isOverCanvas) {
        event.preventDefault();

        // Calculer la position finale
        const point = this.canvasInstance.getMousePosition(event);

        // Ajuster la position pour centrer l'élément
        const finalProperties = Object.assign({}, this.dragElement.properties);
        finalProperties.x = point.x - (finalProperties.width || 100) / 2;
        finalProperties.y = point.y - (finalProperties.height || 50) / 2;

        // Ajouter l'élément au canvas
        const elementId = this.canvasInstance.addElement(this.dragElement.type, finalProperties);

        // Sélectionner le nouvel élément
        this.canvasInstance.selectElement(elementId);

        // Émettre un événement
        this.canvasInstance.emit('element-dropped', {
            elementId: elementId,
            elementType: this.dragElement.type,
            position: point
        });
    }
};

/**
 * Obtient les propriétés par défaut pour un type d'élément
 */
PDFCanvasDragDropManager.prototype.getDefaultPropertiesForType = function(type) {
    const defaults = {
        'text': {
            content: 'Nouveau texte',
            fontSize: 14,
            fontFamily: 'Arial',
            color: '#000000',
            width: 200,
            height: 50
        },
        'rectangle': {
            fillColor: '#ffffff',
            strokeColor: '#000000',
            strokeWidth: 1,
            width: 100,
            height: 100
        },
        'circle': {
            fillColor: '#ffffff',
            strokeColor: '#000000',
            strokeWidth: 1,
            radius: 50
        },
        'line': {
            strokeColor: '#000000',
            strokeWidth: 2,
            x2: 100,
            y2: 0
        },
        'image': {
            src: '',
            width: 100,
            height: 100
        }
    };

    return defaults[type] || {};
};

/**
 * Rend le preview pendant le glisser
 */
PDFCanvasDragDropManager.prototype.render = function(ctx) {
    if (!this.isDragging || !this.dragOffset || !this.dragElement) return;

    ctx.save();

    // Style pour le preview
    ctx.globalAlpha = 0.7;
    ctx.strokeStyle = '#007bff';
    ctx.lineWidth = 2;
    ctx.setLineDash([5, 5]);

    const props = this.dragElement.properties;
    const x = this.dragOffset.x - (props.width || 100) / 2;
    const y = this.dragOffset.y - (props.height || 50) / 2;
    const w = props.width || 100;
    const h = props.height || 50;

    // Dessiner un rectangle de preview
    ctx.strokeRect(x, y, w, h);

    // Ajouter une icône ou texte
    ctx.globalAlpha = 1;
    ctx.fillStyle = '#007bff';
    ctx.font = '12px Arial';
    ctx.textAlign = 'center';
    ctx.fillText(this.dragElement.type, x + w / 2, y + h / 2);

    ctx.restore();
};

/**
 * Enregistre une zone de dépôt
 */
PDFCanvasDragDropManager.prototype.registerDropZone = function(id, element) {
    this.dropZones.set(id, element);
};

/**
 * Supprime une zone de dépôt
 */
PDFCanvasDragDropManager.prototype.unregisterDropZone = function(id) {
    this.dropZones.delete(id);
};

/**
 * Nettoie les ressources
 */
PDFCanvasDragDropManager.prototype.dispose = function() {
    document.removeEventListener('dragstart', this.handleDragStart.bind(this));
    document.removeEventListener('dragend', this.handleDragEnd.bind(this));
    document.removeEventListener('dragover', this.handleDragOver.bind(this));
    document.removeEventListener('drop', this.handleDrop.bind(this));

    this.dropZones.clear();
};

// Export pour compatibilité
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PDFCanvasDragDropManager;
}