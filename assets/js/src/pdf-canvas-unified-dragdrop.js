/**
 * PDF Builder Pro - SYSTÈME DE DRAG & DROP UNIFIÉ
 * ================================================
 * 
 * Gère le drag & drop de manière cohérente pour tous les types d'éléments
 */



export class UnifiedDragDropManager {
    constructor(canvasElement, pdfBuilder) {
        this.canvas = canvasElement;
        this.pdfBuilder = pdfBuilder;
        this.draggedElement = null;
        this.dragOffset = { x: 0, y: 0 };
        this.isDragging = false;
        this.init();
    }

    init() {
        
        
        // Événements de drag sur les éléments du canvas
        document.addEventListener('dragstart', (e) => this.handleDragStart(e), true);
        document.addEventListener('dragover', (e) => this.handleDragOver(e), true);
        document.addEventListener('drop', (e) => this.handleDrop(e), true);
        document.addEventListener('dragend', (e) => this.handleDragEnd(e), true);
        
        // Mouse events pour le drag d'éléments existants
        document.addEventListener('mousedown', (e) => this.handleMouseDown(e), true);
        document.addEventListener('mousemove', (e) => this.handleMouseMove(e), true);
        document.addEventListener('mouseup', (e) => this.handleMouseUp(e), true);
    }

    handleDragStart(e) {
        const target = e.target.closest('[data-element-id]');
        if (!target) return;

        
        
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', target.dataset.elementId);
        
        this.draggedElement = target;
        target.classList.add('dragging');
    }

    handleDragOver(e) {
        if (!this.canvas.contains(e.target) && e.target !== this.canvas) return;
        
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        this.canvas.classList.add('drag-over');
    }

    handleDrop(e) {
        if (!this.canvas.contains(e.target) && e.target !== this.canvas) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        this.canvas.classList.remove('drag-over');
        
        // Récupérer les données du drop
        const elementId = e.dataTransfer.getData('text/plain');
        const jsonData = e.dataTransfer.getData('application/json');
        
        const rect = this.canvas.getBoundingClientRect();
        const x = (e.clientX - rect.left) / (this.pdfBuilder?.zoom || 1);
        const y = (e.clientY - rect.top) / (this.pdfBuilder?.zoom || 1);
        
        if (elementId) {
            // C'est un élément existant qu'on déplace
            
            if (this.pdfBuilder && typeof this.pdfBuilder.moveElement === 'function') {
                this.pdfBuilder.moveElement(elementId, x, y);
            }
        } else if (jsonData) {
            // C'est un nouvel élément de la bibliothèque
            const data = JSON.parse(jsonData);
            
            if (this.pdfBuilder && typeof this.pdfBuilder.addElement === 'function') {
                this.pdfBuilder.addElement(data.elementType, { x, y });
            }
        }
    }

    handleDragEnd(e) {
        if (this.draggedElement) {
            
            this.draggedElement.classList.remove('dragging');
            this.draggedElement = null;
        }
        this.canvas.classList.remove('drag-over');
    }

    handleMouseDown(e) {
        const element = e.target.closest('[data-element-id]');
        if (!element || element.closest('.pdf-builder-toolbar')) return;
        
        
        
        this.isDragging = true;
        this.draggedElement = element;
        
        const rect = element.getBoundingClientRect();
        this.dragOffset = {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
        };
        
        element.classList.add('dragging');
    }

    handleMouseMove(e) {
        if (!this.isDragging || !this.draggedElement) return;
        
        const canvasRect = this.canvas.getBoundingClientRect();
        const x = (e.clientX - canvasRect.left - this.dragOffset.x) / (this.pdfBuilder?.zoom || 1);
        const y = (e.clientY - canvasRect.top - this.dragOffset.y) / (this.pdfBuilder?.zoom || 1);
        
        // Appliquer le snap to grid si activé
        const snappedX = Math.round(x / 10) * 10;
        const snappedY = Math.round(y / 10) * 10;
        
        if (this.pdfBuilder && typeof this.pdfBuilder.updateElementPosition === 'function') {
            this.pdfBuilder.updateElementPosition(this.draggedElement.dataset.elementId, snappedX, snappedY);
        }
    }

    handleMouseUp(e) {
        if (this.draggedElement) {
            
            this.draggedElement.classList.remove('dragging');
            this.isDragging = false;
            this.draggedElement = null;
        }
    }

    destroy() {
        
        document.removeEventListener('dragstart', (e) => this.handleDragStart(e), true);
        document.removeEventListener('dragover', (e) => this.handleDragOver(e), true);
        document.removeEventListener('drop', (e) => this.handleDrop(e), true);
        document.removeEventListener('dragend', (e) => this.handleDragEnd(e), true);
    }
}

export default UnifiedDragDropManager;
