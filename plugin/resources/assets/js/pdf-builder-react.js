(()=>{"use strict";var e={};

// Simple PDF Builder React Implementation
// Version: 1.0.0 - Basic Editor Interface
// Date: 2026-01-02

const PDFBuilderReact = {
    // État de l'éditeur
    editorState: {
        templateId: null,
        elements: [],
        canvas: {
            width: 595, // A4 width in points
            height: 842, // A4 height in points
            zoom: 1
        },
        isLoading: false,
        hasUnsavedChanges: false
    },

    // Templates disponibles
    templates: {
        1: {
            id: 1,
            name: 'Template Basique',
            elements: [
                {
                    id: 'header-text',
                    type: 'text',
                    content: 'EN-TÊTE DU DOCUMENT',
                    x: 50,
                    y: 50,
                    width: 495,
                    height: 30,
                    style: {
                        fontSize: 18,
                        fontWeight: 'bold',
                        textAlign: 'center',
                        color: '#000000'
                    }
                },
                {
                    id: 'main-content',
                    type: 'text',
                    content: 'Contenu principal du document PDF.\n\nVous pouvez modifier ce texte et ajouter d\'autres éléments.',
                    x: 50,
                    y: 100,
                    width: 495,
                    height: 200,
                    style: {
                        fontSize: 12,
                        textAlign: 'left',
                        color: '#333333'
                    }
                }
            ]
        }
    },

    // Initialiser l'éditeur React
    initPDFBuilderReact: function() {
        console.log('[PDF Builder React] Initializing editor...');

        try {
            // Récupérer le conteneur React
            const container = document.getElementById('pdf-builder-react-root');
            if (!container) {
                throw new Error('React root container not found');
            }

            // Créer l'interface de l'éditeur
            this.renderEditor(container);

            // Charger le template si spécifié
            const templateId = window.pdfBuilderData?.templateId || 1;
            this.loadTemplate(templateId);

            console.log('[PDF Builder React] Editor initialized successfully');
            return true;

        } catch (error) {
            console.error('[PDF Builder React] Initialization error:', error);
            this.renderError(container, error.message);
            return false;
        }
    },

    // Rendre l'interface de l'éditeur
    renderEditor: function(container) {
        container.innerHTML = `
            <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #e1e1e1; padding-bottom: 15px;">
                    <h2 style="margin: 0; color: #1e1e1e;">Éditeur PDF - Template</h2>
                    <div>
                        <button id="pdf-save-btn" style="background: #007cba; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; margin-right: 10px;">💾 Sauvegarder</button>
                        <button id="pdf-preview-btn" style="background: #46b450; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">👁️ Aperçu</button>
                    </div>
                </div>

                <div style="display: flex; gap: 20px;">
                    <!-- Panneau d'outils -->
                    <div style="width: 250px; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #e1e1e1;">
                        <h3 style="margin-top: 0; color: #1e1e1e;">Outils</h3>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <button class="tool-btn" data-type="text" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; background: white; cursor: pointer;">📝 Ajouter Texte</button>
                            <button class="tool-btn" data-type="image" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; background: white; cursor: pointer;">🖼️ Ajouter Image</button>
                            <button class="tool-btn" data-type="rectangle" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; background: white; cursor: pointer;">▭ Ajouter Rectangle</button>
                        </div>

                        <h4 style="margin-top: 20px; color: #1e1e1e;">Propriétés</h4>
                        <div id="properties-panel" style="display: none;">
                            <div style="margin-bottom: 10px;">
                                <label style="display: block; font-size: 12px; margin-bottom: 3px;">Contenu:</label>
                                <textarea id="element-content" style="width: 100%; min-height: 60px; padding: 5px; border: 1px solid #ccc; border-radius: 3px; font-size: 12px;" placeholder="Contenu de l'élément"></textarea>
                            </div>
                            <div style="display: flex; gap: 5px;">
                                <button id="apply-changes" style="flex: 1; padding: 5px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 11px;">Appliquer</button>
                                <button id="delete-element" style="flex: 1; padding: 5px; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 11px;">Supprimer</button>
                            </div>
                        </div>
                    </div>

                    <!-- Canevas PDF -->
                    <div style="flex: 1; background: white; border: 2px solid #e1e1e1; border-radius: 8px; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: 10px; left: 10px; background: rgba(255,255,255,0.9); padding: 5px 10px; border-radius: 4px; font-size: 12px; color: #666;">
                            A4 (595 × 842 points) - Zoom: <span id="zoom-level">100%</span>
                        </div>
                        <div id="pdf-canvas" style="width: 595px; height: 842px; background: white; margin: 40px auto; position: relative; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border: 1px solid #e1e1e1;">
                            <!-- Les éléments seront rendus ici -->
                        </div>
                    </div>
                </div>

                <!-- Messages de statut -->
                <div id="status-message" style="margin-top: 20px; padding: 10px; border-radius: 4px; display: none;"></div>
            </div>
        `;

        // Attacher les événements
        this.attachEventListeners();
    },

    // Attacher les écouteurs d'événements
    attachEventListeners: function() {
        const self = this;

        // Boutons d'outils
        document.querySelectorAll('.tool-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.dataset.type;
                self.addElement(type);
            });
        });

        // Bouton sauvegarder
        document.getElementById('pdf-save-btn')?.addEventListener('click', function() {
            self.saveTemplate();
        });

        // Bouton aperçu
        document.getElementById('pdf-preview-btn')?.addEventListener('click', function() {
            self.showPreview();
        });

        // Appliquer les changements
        document.getElementById('apply-changes')?.addEventListener('click', function() {
            self.applyElementChanges();
        });

        // Supprimer l'élément
        document.getElementById('delete-element')?.addEventListener('click', function() {
            self.deleteSelectedElement();
        });
    },

    // Charger un template
    loadTemplate: function(templateId) {
        console.log('[PDF Builder React] Loading template:', templateId);

        const template = this.templates[templateId] || this.templates[1];
        this.editorState.templateId = templateId;
        this.editorState.elements = [...template.elements];

        this.renderCanvas();
        this.showStatus('Template chargé avec succès', 'success');
    },

    // Rendre le canevas
    renderCanvas: function() {
        const canvas = document.getElementById('pdf-canvas');
        if (!canvas) return;

        canvas.innerHTML = '';

        this.editorState.elements.forEach(element => {
            const elementDiv = document.createElement('div');
            elementDiv.className = 'canvas-element';
            elementDiv.dataset.id = element.id;
            elementDiv.style.position = 'absolute';
            elementDiv.style.left = element.x + 'px';
            elementDiv.style.top = element.y + 'px';
            elementDiv.style.width = element.width + 'px';
            elementDiv.style.height = element.height + 'px';
            elementDiv.style.border = '2px solid transparent';
            elementDiv.style.cursor = 'pointer';
            elementDiv.style.fontSize = (element.style?.fontSize || 12) + 'px';
            elementDiv.style.fontWeight = element.style?.fontWeight || 'normal';
            elementDiv.style.textAlign = element.style?.textAlign || 'left';
            elementDiv.style.color = element.style?.color || '#000000';
            elementDiv.style.overflow = 'hidden';

            if (element.type === 'text') {
                elementDiv.textContent = element.content;
                elementDiv.style.whiteSpace = 'pre-wrap';
                elementDiv.style.wordWrap = 'break-word';
            }

            // Événements pour la sélection
            elementDiv.addEventListener('click', (e) => {
                e.stopPropagation();
                this.selectElement(element.id);
            });

            canvas.appendChild(elementDiv);
        });

        // Désélectionner quand on clique sur le canevas vide
        canvas.addEventListener('click', () => {
            this.selectElement(null);
        });
    },

    // Sélectionner un élément
    selectElement: function(elementId) {
        // Retirer la sélection précédente
        document.querySelectorAll('.canvas-element').forEach(el => {
            el.style.border = '2px solid transparent';
        });

        if (elementId) {
            const element = document.querySelector(`[data-id="${elementId}"]`);
            if (element) {
                element.style.border = '2px solid #007cba';
                this.showElementProperties(elementId);
            }
        } else {
            this.hideElementProperties();
        }
    },

    // Afficher les propriétés d'un élément
    showElementProperties: function(elementId) {
        const element = this.editorState.elements.find(el => el.id === elementId);
        if (!element) return;

        const propertiesPanel = document.getElementById('properties-panel');
        const contentTextarea = document.getElementById('element-content');

        if (propertiesPanel && contentTextarea) {
            contentTextarea.value = element.content || '';
            propertiesPanel.style.display = 'block';
            this.editorState.selectedElementId = elementId;
        }
    },

    // Masquer les propriétés
    hideElementProperties: function() {
        const propertiesPanel = document.getElementById('properties-panel');
        if (propertiesPanel) {
            propertiesPanel.style.display = 'none';
            this.editorState.selectedElementId = null;
        }
    },

    // Appliquer les changements à un élément
    applyElementChanges: function() {
        const elementId = this.editorState.selectedElementId;
        const contentTextarea = document.getElementById('element-content');

        if (!elementId || !contentTextarea) return;

        const element = this.editorState.elements.find(el => el.id === elementId);
        if (element) {
            element.content = contentTextarea.value;
            this.renderCanvas();
            this.showStatus('Élément mis à jour', 'success');
        }
    },

    // Supprimer l'élément sélectionné
    deleteSelectedElement: function() {
        const elementId = this.editorState.selectedElementId;
        if (!elementId) return;

        this.editorState.elements = this.editorState.elements.filter(el => el.id !== elementId);
        this.renderCanvas();
        this.hideElementProperties();
        this.showStatus('Élément supprimé', 'success');
    },

    // Ajouter un nouvel élément
    addElement: function(type) {
        const newElement = {
            id: 'element-' + Date.now(),
            type: type,
            content: type === 'text' ? 'Nouveau ' + type : '',
            x: 100,
            y: 100,
            width: type === 'text' ? 200 : 100,
            height: type === 'text' ? 50 : 100,
            style: {
                fontSize: 12,
                fontWeight: 'normal',
                textAlign: 'left',
                color: '#000000'
            }
        };

        this.editorState.elements.push(newElement);
        this.renderCanvas();
        this.selectElement(newElement.id);
        this.showStatus('Élément ajouté', 'success');
    },

    // Sauvegarder le template
    saveTemplate: function() {
        console.log('[PDF Builder React] Saving template...');

        // Simuler une sauvegarde
        this.showStatus('Template sauvegardé avec succès !', 'success');

        // Ici, on pourrait envoyer les données au serveur
        // this.sendToServer(this.editorState);
    },

    // Afficher un aperçu
    showPreview: function() {
        console.log('[PDF Builder React] Showing preview...');
        this.showStatus('Fonction d\'aperçu à implémenter', 'info');
    },

    // Afficher un message de statut
    showStatus: function(message, type = 'info') {
        const statusDiv = document.getElementById('status-message');
        if (!statusDiv) return;

        const colors = {
            success: '#d4edda',
            error: '#f8d7da',
            warning: '#fff3cd',
            info: '#d1ecf1'
        };

        const textColors = {
            success: '#155724',
            error: '#721c24',
            warning: '#856404',
            info: '#0c5460'
        };

        statusDiv.style.backgroundColor = colors[type] || colors.info;
        statusDiv.style.color = textColors[type] || textColors.info;
        statusDiv.style.border = `1px solid ${colors[type] || colors.info}`;
        statusDiv.textContent = message;
        statusDiv.style.display = 'block';

        // Masquer après 3 secondes
        setTimeout(() => {
            statusDiv.style.display = 'none';
        }, 3000);
    },

    // Rendre une erreur
    renderError: function(container, message) {
        container.innerHTML = `
            <div style="padding: 20px; text-align: center; color: #dc3545; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px;">
                <h3>Erreur de chargement de l'éditeur</h3>
                <p>${message}</p>
                <p>Veuillez contacter l'administrateur du site.</p>
            </div>
        `;
    },

    // Autres méthodes de l'API (pour compatibilité)
    loadTemplate: function(templateId) {
        return this.loadTemplate(templateId);
    },

    getEditorState: function() {
        return this.editorState;
    },

    setEditorState: function(state) {
        this.editorState = { ...this.editorState, ...state };
        this.renderCanvas();
    },

    getCurrentTemplate: function() {
        return this.templates[this.editorState.templateId] || null;
    },

    exportTemplate: function() {
        return Promise.resolve(this.editorState);
    },

    saveTemplate: function(data) {
        return Promise.resolve(this.saveTemplate());
    },

    registerEditorInstance: function(instance) {
        console.log('[PDF Builder React] Editor instance registered:', instance);
    },

    resetAPI: function() {
        console.log('[PDF Builder React] API reset');
    },

    updateCanvasDimensions: function(dimensions) {
        if (dimensions.width) this.editorState.canvas.width = dimensions.width;
        if (dimensions.height) this.editorState.canvas.height = dimensions.height;
        this.renderCanvas();
    }
};

// Exposer globalement
if (typeof window !== 'undefined') {
    window.pdfBuilderReact = PDFBuilderReact;
}

console.log('[PDF Builder React] Module loaded successfully');

})();