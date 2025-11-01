<?php
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}

// Debug logs removed for production
?>
<script>
// Créer le nonce global pour les appels AJAX
window.pdfBuilderNonce = '<?php echo wp_create_nonce('pdf_builder_nonce'); ?>';

// Check if the canvas container exists and DOM ready
var canvasContainer = document.getElementById('pdf-canvas-container');
document.addEventListener('DOMContentLoaded', function() {
    // Page loaded
});
</script>
<div id="wpbody-content">
    <div class="pdf-builder-workspace">
        <!-- Header -->
        <div class="pdf-builder-header">
            <div class="header-content">
                <div class="header-left">
                    <h1><?php esc_html_e('PDF Builder Pro - Template Editor', 'pdf-builder-pro'); ?></h1>
                    <div class="template-info">
                        <?php
                        $template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;
                        if ($template_id > 0) {
                            // Récupérer les informations du template
                            global $pdf_builder_pro;
                            if ($pdf_builder_pro && method_exists($pdf_builder_pro, 'get_template_manager')) {
                                $template_manager = $pdf_builder_pro->get_template_manager();
                                if (method_exists($template_manager, 'load_template_robust')) {
                                    $template_data = $template_manager->load_template_robust($template_id);
                                    if ($template_data && isset($template_data['name'])) {
                                        echo '<span class="template-name editing-indicator">' . sprintf(__('✏️ Editing: %s', 'pdf-builder-pro'), esc_html($template_data['name'])) . '</span>';
                                    } else {
                                        echo '<span class="template-name editing-indicator">' . sprintf(__('✏️ Editing Template #%d', 'pdf-builder-pro'), $template_id) . '</span>';
                                    }
                                } else {
                                    echo '<span class="template-name editing-indicator">' . sprintf(__('✏️ Editing Template #%d', 'pdf-builder-pro'), $template_id) . '</span>';
                                }
                            } else {
                                echo '<span class="template-name editing-indicator">' . sprintf(__('✏️ Editing Template #%d', 'pdf-builder-pro'), $template_id) . '</span>';
                            }
                        } else {
                            echo '<span class="template-name">' . __('📄 New Template', 'pdf-builder-pro') . '</span>';
                        }
                        ?>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?php echo admin_url('admin.php?page=pdf-builder-templates'); ?>" class="button button-secondary">
                        <span class="dashicons dashicons-arrow-left-alt"></span>
                        <?php esc_html_e('Back to Templates', 'pdf-builder-pro'); ?>
                    </a>
                    <button id="btn-preview" class="button button-secondary">
                        <span class="dashicons dashicons-visibility"></span>
                        <?php esc_html_e('Preview', 'pdf-builder-pro'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="pdf-builder-loading" class="pdf-builder-loading">
            <div class="spinner is-active"></div>
            <p><?php esc_html_e('Initializing PDF Editor...', 'pdf-builder-pro'); ?></p>
        </div>

        <!-- Main Editor -->
        <div id="pdf-builder-editor" class="pdf-builder-editor" style="display: none;">
            <!-- Toolbar -->
            <div class="pdf-builder-toolbar">
                <!-- Text Tools -->
                <div class="toolbar-group">
                    <h3><?php esc_html_e('Text', 'pdf-builder-pro'); ?></h3>
                    <button id="tool-select" class="toolbar-btn tool-btn" data-tool="select" title="Sélection (V)">
                        <span class="tool-icon">👆</span>
                    </button>
                    <button id="tool-add-text" class="toolbar-btn tool-btn" data-tool="add-text" title="Texte Simple (T)">
                        <span class="tool-icon">📝</span>
                    </button>
                    <button id="tool-add-text-title" class="toolbar-btn tool-btn" data-tool="add-text-title" title="Titre (H)">
                        <span class="tool-icon">📄</span>
                    </button>
                    <button id="tool-add-text-subtitle" class="toolbar-btn tool-btn" data-tool="add-text-subtitle" title="Sous-titre (S)">
                        <span class="tool-icon">📋</span>
                    </button>
                </div>

                <!-- Shape Tools -->
                <div class="toolbar-group">
                    <h3><?php esc_html_e('Shapes', 'pdf-builder-pro'); ?></h3>
                    <button id="tool-add-rectangle" class="toolbar-btn tool-btn" data-tool="add-rectangle" title="Rectangle (R)">
                        <span class="tool-icon">▭</span>
                    </button>
                    <button id="tool-add-circle" class="toolbar-btn tool-btn" data-tool="add-circle" title="Cercle (C)">
                        <span class="tool-icon">○</span>
                    </button>
                    <button id="tool-add-line" class="toolbar-btn tool-btn" data-tool="add-line" title="Ligne (L)">
                        <span class="tool-icon">━</span>
                    </button>
                    <button id="tool-add-arrow" class="toolbar-btn tool-btn" data-tool="add-arrow" title="Flèche (A)">
                        <span class="tool-icon">➤</span>
                    </button>
                    <button id="tool-add-triangle" class="toolbar-btn tool-btn" data-tool="add-triangle" title="Triangle (3)">
                        <span class="tool-icon">△</span>
                    </button>
                    <button id="tool-add-star" class="toolbar-btn tool-btn" data-tool="add-star" title="Étoile (5)">
                        <span class="tool-icon">⭐</span>
                    </button>
                </div>

                <!-- Insert Tools -->
                <div class="toolbar-group">
                    <h3><?php esc_html_e('Insert', 'pdf-builder-pro'); ?></h3>
                    <button id="tool-add-divider" class="toolbar-btn tool-btn" data-tool="add-divider" title="Séparateur (D)">
                        <span class="tool-icon">⎯</span>
                    </button>
                    <button id="tool-add-image" class="toolbar-btn tool-btn" data-tool="add-image" title="Image (I)">
                        <span class="tool-icon">🖼️</span>
                    </button>
                </div>

                <!-- Actions -->
                <div class="toolbar-group">
                    <h3><?php esc_html_e('Actions', 'pdf-builder-pro'); ?></h3>
                    <button id="btn-save" class="toolbar-btn toolbar-btn-primary" title="Save">
                        <span class="dashicons dashicons-yes"></span>
                    </button>
                    <button id="btn-export-pdf" class="toolbar-btn toolbar-btn-primary" title="Export PDF">
                        <span class="dashicons dashicons-download"></span>
                    </button>
                    <button id="btn-undo" class="toolbar-btn" title="Undo" disabled>
                        <span class="dashicons dashicons-undo"></span>
                    </button>
                    <button id="btn-redo" class="toolbar-btn" title="Redo" disabled>
                        <span class="dashicons dashicons-redo"></span>
                    </button>
                </div>

                <!-- View Controls -->
                <div class="toolbar-group">
                    <h3><?php esc_html_e('View', 'pdf-builder-pro'); ?></h3>
                    <button id="btn-toggle-grid" class="toolbar-btn" title="Toggle Grid (G)">
                        <span class="tool-icon">⊞</span>
                    </button>
                    <button id="btn-toggle-snap" class="toolbar-btn" title="Toggle Snap to Grid (S)">
                        <span class="tool-icon">📌</span>
                    </button>
                </div>

                <div class="toolbar-group toolbar-group-right">
                    <div class="zoom-control">
                        <button id="btn-zoom-out" class="toolbar-btn" title="Zoom Out">−</button>
                        <span id="zoom-level" class="zoom-level">100%</span>
                        <button id="btn-zoom-in" class="toolbar-btn" title="Zoom In">+</button>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="pdf-builder-content">
                <!-- Elements Sidebar -->
                <div class="pdf-builder-elements-sidebar">
                    <h3><?php esc_html_e('Elements', 'pdf-builder-pro'); ?></h3>
                    <div class="elements-search">
                        <input type="text" id="elements-search" placeholder="<?php esc_html_e('Search elements...', 'pdf-builder-pro'); ?>">
                    </div>
                    <div id="elements-container" class="elements-container">
                        <!-- Elements will be populated by JavaScript -->
                    </div>
                </div>

                <!-- Canvas Area -->
                <div class="pdf-builder-canvas-area">
                    <div id="pdf-canvas-container" class="pdf-canvas-container">
                        <!-- A4 Portrait: 594px × 1123px -->
                        <canvas id="pdf-builder-canvas" width="594" height="1123"></canvas>
                    </div>
                </div>

                <!-- Properties Panel -->
                <div class="pdf-builder-properties">
                    <h3><?php esc_html_e('Properties', 'pdf-builder-pro'); ?></h3>
                    <div id="properties-content" class="properties-content">
                        <p class="no-selection"><?php esc_html_e('Select an element to edit properties', 'pdf-builder-pro'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error State -->
        <div id="pdf-builder-error" class="pdf-builder-error" style="display: none;">
            <h3><?php esc_html_e('Error', 'pdf-builder-pro'); ?></h3>
            <p id="error-message"></p>
            <button id="btn-retry" class="button button-primary"><?php esc_html_e('Retry', 'pdf-builder-pro'); ?></button>
        </div>
    </div>
</div>

<style>
/* Workspace Layout */
.pdf-builder-workspace {
    display: flex;
    flex-direction: column;
    height: 100vh;
    background-color: #f5f5f5;
}

/* Header */
.pdf-builder-header {
    background-color: white;
    border-bottom: 1px solid #e5e5e5;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    z-index: 100;
}

.header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    max-width: 100%;
}

.header-left {
    flex: 1;
}

.header-left h1 {
    margin: 0 0 5px 0;
    font-size: 22px;
    color: #1a1a1a;
}

.template-info {
    font-size: 12px;
    color: #666;
}

.template-name {
    font-weight: 600;
    color: #0073aa;
}

.editing-indicator {
    color: #d97706;
}

.header-right {
    display: flex;
    gap: 10px;
    align-items: center;
}

.pdf-builder-editor {
    display: flex;
    flex-direction: column;
    flex: 1;
    overflow: hidden;
}

.pdf-builder-toolbar {
    background-color: white;
    border-bottom: 1px solid #e5e5e5;
    padding: 12px 20px;
    display: flex;
    gap: 20px;
    overflow-x: auto;
    flex-wrap: wrap;
    align-items: center;
}

.toolbar-group {
    display: flex;
    gap: 8px;
    align-items: center;
    padding-right: 20px;
    border-right: 1px solid #e5e5e5;
}

.toolbar-group:last-child {
    border-right: none;
}

.toolbar-group h3 {
    margin: 0;
    font-size: 11px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    white-space: nowrap;
}

.toolbar-btn {
    background-color: #f5f5f5;
    border: 1px solid #ddd;
    padding: 6px 8px;
    cursor: pointer;
    border-radius: 3px;
    transition: all 0.2s ease;
    font-size: 14px;
}

.toolbar-btn:hover {
    background-color: #e5e5e5;
    border-color: #999;
}

.toolbar-btn.active {
    background-color: #0073aa;
    color: white;
    border-color: #0073aa;
}

.toolbar-btn-primary {
    background-color: #0073aa;
    color: white;
    border-color: #0073aa;
}

.toolbar-btn-primary:hover {
    background-color: #005a87;
    border-color: #005a87;
}

.toolbar-group-right {
    border-right: none;
    margin-left: auto;
}

.zoom-control {
    display: flex;
    align-items: center;
    gap: 8px;
}

.zoom-level {
    min-width: 50px;
    text-align: center;
    font-size: 12px;
    font-weight: 600;
}

.pdf-builder-content {
    display: flex;
    flex: 1;
    overflow: hidden;
    gap: 0;
}

.pdf-builder-elements-sidebar {
    width: 250px;
    background-color: white;
    border-right: 1px solid #e5e5e5;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.pdf-builder-elements-sidebar h3 {
    margin: 15px 15px 10px 15px;
    font-size: 12px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
}

.elements-search {
    padding: 0 15px 15px 15px;
}

.elements-search input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-size: 12px;
}

.elements-container {
    flex: 1;
    overflow-y: auto;
    padding: 0 10px;
}

.element-category {
    margin-bottom: 15px;
}

.element-category-title {
    font-size: 11px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    padding: 8px 5px;
    margin-bottom: 8px;
    border-bottom: 1px solid #e5e5e5;
}

.element-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px;
    margin-bottom: 5px;
    background-color: #f9f9f9;
    border: 1px solid #e5e5e5;
    border-radius: 3px;
    cursor: grab;
    transition: all 0.2s ease;
}

.element-item:hover {
    background-color: #f0f0f0;
    border-color: #999;
}

.element-item.dragging {
    opacity: 0.5;
}

.element-icon {
    font-size: 16px;
    min-width: 20px;
}

.element-info {
    flex: 1;
    min-width: 0;
}

.element-name {
    font-size: 12px;
    font-weight: 600;
    color: #333;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.element-description {
    font-size: 10px;
    color: #999;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pdf-builder-canvas-area {
    flex: 1;
    background-color: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: auto;
    position: relative;
}

.pdf-canvas-container {
    position: relative;
    background-color: white;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.pdf-canvas-container canvas {
    display: block;
    background-color: white;
    border: 1px solid #ddd;
}

.pdf-canvas-container.drag-over canvas {
    border: 2px dashed #0073aa;
    background-color: #f0f7ff;
}

.pdf-builder-properties {
    width: 280px;
    background-color: white;
    border-left: 1px solid #e5e5e5;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.pdf-builder-properties h3 {
    margin: 15px 15px 10px 15px;
    font-size: 12px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
}

.properties-content {
    flex: 1;
    overflow-y: auto;
    padding: 0 15px 15px 15px;
}

.no-selection {
    color: #999;
    font-size: 12px;
    text-align: center;
    padding: 20px 0;
}

.pdf-builder-loading {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background-color: white;
}

.pdf-builder-loading .spinner {
    margin-bottom: 20px;
}

.pdf-builder-loading p {
    font-size: 14px;
    color: #666;
}

.pdf-builder-error {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background-color: #fef2f2;
    padding: 20px;
}

.pdf-builder-error h3 {
    color: #dc2626;
    font-size: 18px;
    margin-bottom: 10px;
}

.pdf-builder-error p {
    color: #666;
    margin-bottom: 20px;
    text-align: center;
    max-width: 400px;
}

@media (max-width: 768px) {
    .pdf-builder-elements-sidebar,
    .pdf-builder-properties {
        max-height: 150px;
    }

    .pdf-builder-canvas-area {
        min-height: 400px;
    }
}
</style>

<!-- ===== INITIALISATION DU CANVAS EDITOR ===== -->
<script>
// console.log removed

// Attendre que PDFBuilderPro soit disponible
function waitForPDFBuilder(maxRetries = 20) {
    
    if (typeof window.PDFBuilderPro !== 'undefined') {
        initializeCanvas();
        return;
    }
    
    if (maxRetries > 0) {
        setTimeout(() => waitForPDFBuilder(maxRetries - 1), 250);
    } else {
        showError('Impossible de charger le système PDF Builder');
    }
}

// Initialiser le canvas une fois PDFBuilderPro disponible
async function initializeCanvas() {
    // console.log removed
    
    // Masquer le loading, afficher l'éditeur
    var loading = document.getElementById('pdf-builder-loading');
    var editor = document.getElementById('pdf-builder-editor');
    
    if (loading) loading.style.display = 'none';
    if (editor) editor.style.display = 'flex';
    
    // Récupérer l'ID et le nom du template depuis l'URL et la page
    var templateId = new URLSearchParams(window.location.search).get('template_id');
    var templateNameEl = document.querySelector('.template-name');
    var templateName = templateNameEl ? templateNameEl.textContent.replace('✏️ Editing: ', '').replace('✏️ Editing Template #' + templateId, 'Template ' + templateId) : 'Template ' + (templateId || 'Nouveau');
    // console.log removed
    
    // Options de configuration du canvas
    var canvasOptions = {
        containerId: 'pdf-canvas-container',
        templateId: templateId || null,
        templateName: templateName || 'Template',
        width: 594,  // A4 width in pixels
        height: 1123, // A4 height in pixels
        zoom: 1,
        gridEnabled: true,
        snapToGrid: true,
        gridSize: 10,
        canvasElementId: 'pdf-builder-canvas',
        elementsContainerId: 'elements-container',
        propertiesPanelId: 'properties-content',
        version: '1.0.0'
    };
    
    // console.log removed
    
    console.log('🔍 Vérification window.PDFBuilderPro:', typeof window.PDFBuilderPro);
    console.log('🔍 Vérification PDFCanvasVanilla:', typeof window.PDFBuilderPro?.PDFCanvasVanilla);
    console.log('🔍 Paramètres:', canvasOptions);
    
    try {
        // Initialiser le canvas avec PDFCanvasVanilla
        // console.log removed
        
        // ✅ Créer une instance de PDFCanvasVanilla (c'est une classe)
        console.log('🚀 Création instance PDFCanvasVanilla...');
        var canvas = new window.PDFBuilderPro.PDFCanvasVanilla('pdf-canvas-container', canvasOptions);
        console.log('✅ Instance créée:', canvas);
        window.pdfCanvasInstance = canvas;
        
        // ✅ Initialiser le canvas
        console.log('🚀 Appel canvas.init()...');
        await canvas.init();
        console.log('✅ canvas.init() complété');
        
        // console.log removed
        
        // Charger les données initiales du canvas (template + WooCommerce)
        if (typeof canvas.loadInitialData === 'function') {
            // console.log removed
            canvas.loadInitialData();
        }
        
        // Populer la bibliothèque d'éléments
        populateElementsLibrary();
        
        // Configurer les contrôles de la toolbar
        setupToolbarControls();
        
        // Configurer le drag & drop
        setupDragAndDrop();
        
    } catch (error) {
        console.error('[INIT] ❌ Erreur lors de l\'initialisation:', error);
        showError('Erreur lors de l\'initialisation du canvas: ' + error.message);
    }
}

// Populer la bibliothèque d'éléments
function populateElementsLibrary() {
    // console.log removed
    
    var elementsContainer = document.getElementById('elements-container');
    if (!elementsContainer) {
        console.warn('[INIT] Elements container non trouvé');
        return;
    }
    
    // Vérifier si PDFBuilderPro a une méthode pour obtenir les éléments
    if (typeof window.PDFBuilderPro !== 'undefined') {
        // Essayer différentes méthodes pour obtenir les éléments
        var elements = null;
        
        if (typeof window.PDFBuilderPro.getAllElements === 'function') {
            elements = window.PDFBuilderPro.getAllElements();
        } else if (typeof window.PDFBuilderPro.ELEMENT_LIBRARY !== 'undefined') {
            // console.log removed
        }
        
        if (!elements || Object.keys(elements).length === 0) {
            console.warn('[INIT] Aucun élément trouvé, utilisation des éléments par défaut');
            // Éléments par défaut si rien n'est trouvé
            elements = {
                'text': [
                    { type: 'text', label: 'Texte', description: 'Texte simple', icon: '📝' },
                    { type: 'text-title', label: 'Titre', description: 'Titre principal', icon: '📄' },
                    { type: 'text-subtitle', label: 'Sous-titre', description: 'Sous-titre', icon: '📋' }
                ],
                'shapes': [
                    { type: 'rectangle', label: 'Rectangle', description: 'Forme rectangulaire', icon: '▭' },
                    { type: 'circle', label: 'Cercle', description: 'Forme circulaire', icon: '○' },
                    { type: 'line', label: 'Ligne', description: 'Ligne simple', icon: '━' },
                    { type: 'arrow', label: 'Flèche', description: 'Flèche directionnelle', icon: '➤' }
                ],
                'special': [
                    { type: 'image', label: 'Image', description: 'Insérer une image', icon: '🖼️' },
                    { type: 'divider', label: 'Séparateur', description: 'Ligne de séparation', icon: '⎯' }
                ]
            };
        }
        
        // Vider le container
        elementsContainer.innerHTML = '';
        
        // Ajouter les éléments
        for (var category in elements) {
            var categoryElements = elements[category];
            if (!categoryElements || categoryElements.length === 0) continue;
            
            // Créer une section pour la catégorie
            var categoryDiv = document.createElement('div');
            categoryDiv.className = 'element-category';
            
            var categoryTitle = document.createElement('div');
            categoryTitle.className = 'element-category-title';
            categoryTitle.textContent = category.charAt(0).toUpperCase() + category.slice(1);
            categoryDiv.appendChild(categoryTitle);
            
            // Ajouter chaque élément
            categoryElements.forEach(function(element) {
                var elementDiv = document.createElement('div');
                elementDiv.className = 'element-item';
                elementDiv.draggable = true;
                elementDiv.setAttribute('data-element-type', element.type);
                elementDiv.setAttribute('data-element', JSON.stringify(element));
                
                elementDiv.innerHTML = (element.icon || '') + ' ' + (element.label || element.type);
                elementDiv.title = element.description || '';
                
                categoryDiv.appendChild(elementDiv);
                // console.log removed
            });
            
            elementsContainer.appendChild(categoryDiv);
        }
        
        // console.log removed
    } else {
        console.error('[INIT] PDFBuilderPro non disponible');
    }
}

// Configurer les contrôles de la toolbar
function setupToolbarControls() {
    console.log('[TOOLBAR] Configuration des contrôles de toolbar');

    // Bouton toggle grid
    var toggleGridBtn = document.getElementById('btn-toggle-grid');
    if (toggleGridBtn) {
        toggleGridBtn.addEventListener('click', function() {
            console.log('[TOOLBAR] Toggle grid button clicked');
            if (window.pdfCanvasInstance && typeof window.pdfCanvasInstance.toggleGrid === 'function') {
                var isVisible = window.pdfCanvasInstance.toggleGrid();
                this.innerHTML = '<span class="tool-icon">' + (isVisible ? '⊞' : '⊟') + '</span>';
                this.title = (isVisible ? 'Masquer la grille (G)' : 'Afficher la grille (G)');
                console.log('[TOOLBAR] Grid toggled:', isVisible);
            } else {
                console.error('[TOOLBAR] pdfCanvasInstance.toggleGrid not available');
            }
        });
        console.log('[TOOLBAR] Toggle grid button configured');
    } else {
        console.warn('[TOOLBAR] Toggle grid button not found');
    }

    // Bouton toggle snap
    var toggleSnapBtn = document.getElementById('btn-toggle-snap');
    if (toggleSnapBtn) {
        toggleSnapBtn.addEventListener('click', function() {
            console.log('[TOOLBAR] Toggle snap button clicked');
            // TODO: Implémenter la logique snap to grid
            this.classList.toggle('active');
        });
    }

    // Boutons zoom
    var zoomInBtn = document.getElementById('btn-zoom-in');
    var zoomOutBtn = document.getElementById('btn-zoom-out');
    var zoomLevel = document.getElementById('zoom-level');

    if (zoomInBtn && zoomOutBtn && zoomLevel) {
        zoomInBtn.addEventListener('click', function() {
            // TODO: Implémenter le zoom in
            console.log('[TOOLBAR] Zoom in clicked');
        });

        zoomOutBtn.addEventListener('click', function() {
            // TODO: Implémenter le zoom out
            console.log('[TOOLBAR] Zoom out clicked');
        });
    }

    // Boutons undo/redo
    var undoBtn = document.getElementById('btn-undo');
    var redoBtn = document.getElementById('btn-redo');

    if (undoBtn && redoBtn) {
        undoBtn.addEventListener('click', function() {
            if (window.pdfCanvasInstance && typeof window.pdfCanvasInstance.undo === 'function') {
                window.pdfCanvasInstance.undo();
            }
        });

        redoBtn.addEventListener('click', function() {
            if (window.pdfCanvasInstance && typeof window.pdfCanvasInstance.redo === 'function') {
                window.pdfCanvasInstance.redo();
            }
        });
    }

    // Bouton save
    var saveBtn = document.getElementById('btn-save');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            if (window.pdfCanvasInstance && typeof window.pdfCanvasInstance.saveTemplate === 'function') {
                window.pdfCanvasInstance.saveTemplate();
            }
        });
    }

    // Bouton export PDF
    var exportBtn = document.getElementById('btn-export-pdf');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            if (window.pdfCanvasInstance && typeof window.pdfCanvasInstance.exportPDF === 'function') {
                window.pdfCanvasInstance.exportPDF();
            }
        });
    }

    console.log('[TOOLBAR] Toolbar controls configured');
}

// Configurer le drag & drop
function setupDragAndDrop() {
    // console.log removed
    
    var elementsContainer = document.getElementById('elements-container');
    var canvas = document.getElementById('pdf-canvas-container') || document.getElementById('pdf-builder-canvas');
    
    if (!canvas) {
        console.warn('[DRAGDROP] Canvas non trouvé pour le drag & drop');
        return;
    }
    
    if (!elementsContainer) {
        console.warn('[DRAGDROP] Elements container non trouvé');
        return;
    }
    
    // console.log removed
    
    var isDragging = false;
    var currentDraggedElement = null;
    
    // Événements de drag sur les éléments (DEPUIS la toolbar)
    elementsContainer.addEventListener('dragstart', function(e) {
        // console.log removed
        
        // Chercher l'ancêtre .element-item (délégation d'événements robuste)
        var elementItem = e.target.closest('.element-item');
        if (elementItem) {
            var elementType = elementItem.getAttribute('data-element-type');
            var elementData = {
                type: 'new-element',
                elementType: elementType,
                elementData: JSON.parse(elementItem.dataset.element || '{}')
            };
            // console.log removed
            isDragging = true;
            currentDraggedElement = elementItem;
            e.dataTransfer.effectAllowed = 'copy';
            e.dataTransfer.setData('application/json', JSON.stringify(elementData));
            elementItem.classList.add('dragging');
        } else {
            console.warn('[DRAGDROP] ⚠️ Pas de .element-item trouvé pour:', e.target);
        }
    });
    
    elementsContainer.addEventListener('dragend', function(e) {
        // console.log removed
        isDragging = false;
        currentDraggedElement = null;
        var elementItem = e.target.closest('.element-item');
        if (elementItem) {
            elementItem.classList.remove('dragging');
        }
    });
    
    // Événements de drop sur le canvas (ACCEPTER le drop UNIQUEMENT si on vient de la toolbar)
    canvas.addEventListener('dragover', function(e) {
        // ⚠️ N'accepter le drag que s'il vient d'un élément de toolbar (isDragging = true depuis la toolbar)
        if (isDragging && currentDraggedElement) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
            canvas.classList.add('drag-over');
        } else {
            e.dataTransfer.dropEffect = 'none';
        }
    }, false);
    
    canvas.addEventListener('dragleave', function(e) {
        // console.log removed
        if (e.target === canvas) {
            canvas.classList.remove('drag-over');
        }
    }, false);
    
    canvas.addEventListener('drop', function(e) {
        console.log('[DRAGDROP] 🔵 Drop event fired');
        
        // Ne traiter le drop que s'il vient de la toolbar
        if (!isDragging || !currentDraggedElement) {
            console.warn('[DRAGDROP] ⚠️ Not a toolbar drag, ignoring');
            return;
        }
        
        e.preventDefault();
        e.stopPropagation();
        canvas.classList.remove('drag-over');
        
        try {
            var jsonData = e.dataTransfer.getData('application/json');
            console.log('[DRAGDROP] 📦 Received data:', jsonData);
            
            var data = JSON.parse(jsonData);
            console.log('[DRAGDROP] 📋 Parsed data:', data);
            
            if (data.type === 'new-element') {
                var rect = canvas.getBoundingClientRect();
                var zoom = (window.pdfCanvasInstance && window.pdfCanvasInstance.options && window.pdfCanvasInstance.options.zoom) || 1;
                var x = (e.clientX - rect.left) / zoom;
                var y = (e.clientY - rect.top) / zoom;
                
                console.log('[DRAGDROP] 📍 Position:', { x, y, zoom });
                console.log('[DRAGDROP] ✓ pdfCanvasInstance exists:', !!window.pdfCanvasInstance);
                console.log('[DRAGDROP] ✓ addElement function exists:', typeof window.pdfCanvasInstance?.addElement);
                
                if (window.pdfCanvasInstance && typeof window.pdfCanvasInstance.addElement === 'function') {
                    console.log('[DRAGDROP] ✅ Adding element:', data.elementType);
                    window.pdfCanvasInstance.addElement(data.elementType, { x, y, ...data.elementData });
                } else {
                    console.error('[DRAGDROP] ❌ pdfCanvasInstance or addElement not available');
                }
            }
        } catch (error) {
            console.error('[DRAGDROP] ❌ Erreur:', error);
            console.error('[DRAGDROP] ❌ Stack:', error.stack);
        }
    }, false);
    
    // Empêcher les conflits avec les clics sur les éléments du canvas
    canvas.addEventListener('mousedown', function(e) {
        // console.log removed
        
        // Si un drag est en cours depuis la toolbar, ignorer les interactions du canvas
        if (isDragging && currentDraggedElement) {
            // console.log removed
            e.stopPropagation();
            e.preventDefault();
            return false;
        }
        
        // Sinon, laisser passer les événements normaux du canvas (sélection, redimensionnement)
        // console.log removed
    }, false);
    
    // console.log removed
}

// Afficher message d'erreur
function showError(message) {
    console.error('[INIT] Erreur:', message);
    document.getElementById('pdf-builder-loading').style.display = 'none';
    document.getElementById('pdf-builder-error').style.display = 'flex';
    document.getElementById('error-message').textContent = message;

    document.getElementById('btn-retry').addEventListener('click', function() {
        location.reload();
    });
}

// Initialiser au chargement du DOM (système unique d'initialisation)
// console.log removed

// Vérifier si DOM est déjà chargé
if (document.readyState === 'loading') {
    // console.log removed
    document.addEventListener('DOMContentLoaded', function() {
        // console.log removed
        waitForPDFBuilder();
    });
} else {
    // console.log removed
    waitForPDFBuilder();
}
</script>