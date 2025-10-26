/**
 * PDF Canvas Drag & Drop Manager - Gestionnaire de glisser-déposer
 * Permet de glisser des éléments depuis la bibliothèque vers le canvas
 */

console.log('[DRAG] Script drag-drop chargé');

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
    console.log('[DRAG] Initialisation du PDFCanvasDragDropManager');
    this.setupGlobalListeners();
    console.log('[DRAG] Gestionnaire initialisé avec succès');
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

    console.log('[DRAG] Début du drag - Type:', elementType, 'Target:', target);

    this.isDragging = true;
    this.dragElement = {
        type: elementType,
        properties: this.getDefaultPropertiesForType(elementType)
    };

    console.log('[DRAG] Élément drag créé:', this.dragElement);

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
    console.log('[DRAG] Fin du drag - isDragging:', this.isDragging, 'dragElement:', this.dragElement);

    this.isDragging = false;
    this.dragElement = null;
    this.dragOffset = null;

    // Retirer la classe CSS
    document.body.classList.remove('pdf-builder-dragging');

    console.log('[DRAG] Drag terminé, nettoyage effectué');
};

/**
 * Gère le survol pendant le glisser
 */
PDFCanvasDragDropManager.prototype.handleDragOver = function(event) {
    if (!this.isDragging || !this.canvasInstance || !this.canvasInstance.canvas) {
        console.log('[DRAG] DragOver ignoré - isDragging:', this.isDragging, 'canvasInstance:', !!this.canvasInstance);
        return;
    }

    // Vérifier si on survole le canvas
    const canvasRect = this.canvasInstance.canvas.getBoundingClientRect();
    const isOverCanvas = event.clientX >= canvasRect.left &&
                        event.clientX <= canvasRect.right &&
                        event.clientY >= canvasRect.top &&
                        event.clientY <= canvasRect.bottom;

    console.log('🔍🔍🔍 DEBUGGING CANVAS DETECTION 🔍🔍🔍');
    console.log('[DRAG] Canvas rect:', {
        left: canvasRect.left,
        right: canvasRect.right,
        top: canvasRect.top,
        bottom: canvasRect.bottom,
        width: canvasRect.width,
        height: canvasRect.height
    });
    console.log('[DRAG] Mouse position:', {x: event.clientX, y: event.clientY});
    console.log('[DRAG] Checks:', {
        xInRange: event.clientX >= canvasRect.left && event.clientX <= canvasRect.right,
        yInRange: event.clientY >= canvasRect.top && event.clientY <= canvasRect.bottom,
        isOverCanvas: isOverCanvas
    });
    console.log('🔍🔍🔍 END DEBUGGING 🔍🔍🔍');

    console.log('[DRAG] DragOver - Position:', {x: event.clientX, y: event.clientY}, 'Sur canvas:', isOverCanvas);

    if (isOverCanvas) {
        event.preventDefault();
        event.dataTransfer.dropEffect = 'copy';

        // Calculer la position sur le canvas
        const point = this.canvasInstance.getMousePosition(event);
        this.dragOffset = point;

        console.log('[DRAG] Position canvas calculée:', point);

        // Mettre à jour le rendu pour montrer le preview
        this.canvasInstance.render();
    }
};

/**
 * Gère le dépôt
 */
PDFCanvasDragDropManager.prototype.handleDrop = function(event) {
    console.log('[DRAG] Drop détecté - isDragging:', this.isDragging, 'dragElement:', this.dragElement);

    if (!this.isDragging || !this.dragElement || !this.canvasInstance || !this.canvasInstance.canvas) {
        console.log('[DRAG] Drop ignoré - conditions non remplies');
        return;
    }

    // Vérifier si on dépose sur le canvas
    const canvasRect = this.canvasInstance.canvas.getBoundingClientRect();
    const isOverCanvas = event.clientX >= canvasRect.left &&
                        event.clientX <= canvasRect.right &&
                        event.clientY >= canvasRect.top &&
                        event.clientY <= canvasRect.bottom;

    console.log('[DRAG] Drop canvas rect:', {
        left: canvasRect.left,
        right: canvasRect.right,
        top: canvasRect.top,
        bottom: canvasRect.bottom,
        width: canvasRect.width,
        height: canvasRect.height
    });
    console.log('[DRAG] Drop mouse position:', {x: event.clientX, y: event.clientY});
    console.log('[DRAG] Drop checks:', {
        xInRange: event.clientX >= canvasRect.left && event.clientX <= canvasRect.right,
        yInRange: event.clientY >= canvasRect.top && event.clientY <= canvasRect.bottom,
        isOverCanvas: isOverCanvas
    });

    console.log('[DRAG] Drop position:', {x: event.clientX, y: event.clientY}, 'Sur canvas:', isOverCanvas);

    if (isOverCanvas) {
        event.preventDefault();

        // Calculer la position finale
        const point = this.canvasInstance.getMousePosition(event);
        console.log('[DRAG] Position finale calculée:', point);

        // Ajuster la position pour centrer l'élément
        const finalProperties = Object.assign({}, this.dragElement.properties);
        finalProperties.x = point.x - (finalProperties.width || 100) / 2;
        finalProperties.y = point.y - (finalProperties.height || 50) / 2;

        console.log('[DRAG] Propriétés finales:', finalProperties);

        // Ajouter l'élément au canvas
        const elementId = this.canvasInstance.addElement(this.dragElement.type, finalProperties);
        console.log('[DRAG] Élément ajouté avec ID:', elementId);

        // Sélectionner le nouvel élément
        this.canvasInstance.selectElement(elementId);

        // Émettre un événement
        this.canvasInstance.emit('element-dropped', {
            elementId: elementId,
            elementType: this.dragElement.type,
            position: point
        });

        console.log('[DRAG] Drop terminé avec succès - Élément créé:', elementId);
    } else {
        console.log('[DRAG] Drop hors du canvas - ignoré');
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
        },
        // Éléments WooCommerce spéciaux
        'product_table': {
            x: 50,
            y: 100,
            width: 500,
            height: 200,
            showHeaders: true,
            showBorders: true,
            fontSize: 12,
            backgroundColor: '#ffffff',
            borderColor: '#e5e7eb',
            borderWidth: 1
        },
        'customer_info': {
            x: 50,
            y: 50,
            width: 250,
            height: 120,
            showHeaders: true,
            showBorders: false,
            fontSize: 12,
            backgroundColor: 'transparent',
            layout: 'vertical'
        },
        'company_info': {
            x: 320,
            y: 50,
            width: 250,
            height: 120,
            showHeaders: true,
            showBorders: false,
            fontSize: 12,
            backgroundColor: 'transparent',
            layout: 'vertical'
        },
        'company_logo': {
            x: 50,
            y: 200,
            width: 150,
            height: 80,
            fit: 'contain',
            alignment: 'left'
        },
        'order_number': {
            x: 450,
            y: 20,
            width: 100,
            height: 30,
            fontSize: 14,
            fontFamily: 'Arial',
            textAlign: 'right',
            backgroundColor: 'transparent'
        },
        'dynamic-text': {
            x: 50,
            y: 320,
            width: 200,
            height: 40,
            template: 'Commande #{order_number}',
            fontSize: 14,
            fontFamily: 'Arial',
            backgroundColor: 'transparent'
        },
        'mentions': {
            x: 50,
            y: 380,
            width: 500,
            height: 60,
            fontSize: 10,
            fontFamily: 'Arial',
            textAlign: 'left',
            backgroundColor: 'transparent'
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

// Export ES6
export { PDFCanvasDragDropManager };
export default PDFCanvasDragDropManager;