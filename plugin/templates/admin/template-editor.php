<?php
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}

// LOG PHP - Vérifier que la page PHP est chargée
error_log('[PHP] Template editor page loaded - PHP execution test');
error_log('[PHP] Current URL: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'unknown'));
error_log('[PHP] Template ID: ' . (isset($_GET['template_id']) ? intval($_GET['template_id']) : 'none'));
error_log('[PHP] User: ' . get_current_user_id());
?>
<script>
console.log('🚀 TEMPLATE EDITOR PAGE LOADED - JavaScript execution test');
console.log('Current URL:', window.location.href);
console.log('User Agent:', navigator.userAgent);
console.log('DOMContentLoaded status:', document.readyState);

// Test if jQuery is loaded
if (typeof jQuery !== 'undefined') {
    console.log('✅ jQuery is loaded, version:', jQuery.fn.jquery);
} else {
    console.log('❌ jQuery is NOT loaded');
}

// Test if our main script variables exist
console.log('pdfBuilderAjax exists:', typeof pdfBuilderAjax !== 'undefined');
console.log('pdfBuilderInstance exists:', typeof pdfBuilderInstance !== 'undefined');
console.log('pdfBuilderPro exists:', typeof pdfBuilderPro !== 'undefined');

// Check if the canvas container exists
var canvasContainer = document.getElementById('pdf-canvas-container');
console.log('Canvas container exists:', !!canvasContainer);

document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 DOMContentLoaded fired - page fully loaded');
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
            <div class="pdf-builder-toolbar pdf-builder-toolbar">
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
                        <canvas id="pdf-builder-canvas" width="595" height="842"></canvas>
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
    margin: 0 0 4px 0;
    font-size: 24px;
    font-weight: 600;
    color: #1d2327;
}

.template-info {
    font-size: 14px;
    color: #646970;
}

.template-name {
    background-color: #f0f0f0;
    padding: 2px 8px;
    border-radius: 3px;
    font-weight: 500;
}

.header-right {
    display: flex;
    gap: 8px;
    align-items: center;
}

.header-right .button {
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Loading State */
.pdf-builder-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    flex: 1;
    gap: 20px;
}

.pdf-builder-loading p {
    font-size: 16px;
    color: #666;
}

/* Toolbar */
.pdf-builder-toolbar {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 12px 15px;
    background-color: white;
    border-bottom: 1px solid #e5e5e5;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.toolbar-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.toolbar-group h3 {
    margin: 0;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    color: #999;
    margin-right: 8px;
}

.toolbar-group-right {
    margin-left: auto;
}

/* Toolbar Buttons */
.toolbar-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background-color: #f0f0f0;
    border: 1px solid #ddd;
    border-radius: 3px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    color: #333;
    transition: all 0.2s ease;
}

.tool-btn {
    padding: 8px;
    min-width: 36px;
    justify-content: center;
}

.tool-btn.active {
    background-color: #2271b1;
    color: white;
    border-color: #1e5aa8;
}

.tool-icon {
    font-size: 16px;
    line-height: 1;
}

.toolbar-btn:hover:not(:disabled):not(.active) {
    background-color: #e8e8e8;
    border-color: #ccc;
}

.toolbar-btn-primary {
    background-color: #2271b1;
    color: white;
    border-color: #1e5aa8;
}

.toolbar-btn-primary:hover:not(:disabled) {
    background-color: #1e5aa8;
    border-color: #1a4d92;
}

.toolbar-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.toolbar-btn .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

/* Zoom Control */
.zoom-control {
    display: flex;
    align-items: center;
    gap: 8px;
    background-color: #f0f0f0;
    padding: 4px 8px;
    border-radius: 3px;
    border: 1px solid #ddd;
}

.zoom-level {
    min-width: 50px;
    text-align: center;
    font-size: 12px;
    font-weight: 600;
}

/* Editor Content */
.pdf-builder-editor {
    display: flex;
    flex-direction: column;
    flex: 1;
    overflow: hidden;
}

.pdf-builder-content {
    display: flex;
    flex: 1;
    overflow: hidden;
    gap: 0;
}

/* Canvas Area */
.pdf-builder-canvas-area {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 20px;
    background-color: #e8e8e8;
    overflow: auto;
}

.pdf-canvas-container {
    position: relative;
    background-color: white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    cursor: crosshair;
}

#pdf-builder-canvas {
    display: block;
    background-color: white;
}

/* Properties Panel */
.pdf-builder-properties {
    width: 280px;
    padding: 15px;
    background-color: white;
    border-left: 1px solid #e5e5e5;
    overflow-y: auto;
    box-shadow: -2px 0 4px rgba(0, 0, 0, 0.05);
}

.pdf-builder-properties h3 {
    margin: 0 0 15px 0;
    font-size: 14px;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #2271b1;
    padding-bottom: 10px;
}

.properties-content {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.no-selection {
    text-align: center;
    color: #999;
    font-size: 13px;
    margin: 20px 0;
}

/* Elements Sidebar */
.pdf-builder-elements-sidebar {
    width: 280px;
    padding: 15px;
    background-color: white;
    border-right: 1px solid #e5e5e5;
    overflow-y: auto;
    box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
}

.pdf-builder-elements-sidebar h3 {
    margin: 0 0 15px 0;
    font-size: 14px;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #2271b1;
    padding-bottom: 10px;
}

.elements-search {
    margin-bottom: 15px;
}

.elements-search input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
    box-sizing: border-box;
}

.elements-search input:focus {
    outline: none;
    border-color: #2271b1;
    box-shadow: 0 0 0 2px rgba(34, 113, 177, 0.1);
}

.elements-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.element-category {
    margin-bottom: 20px;
}

.element-category-title {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    color: #666;
    margin-bottom: 10px;
    padding-bottom: 5px;
    border-bottom: 1px solid #eee;
}

.element-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    background-color: #fafafa;
}

.element-item:hover {
    background-color: #f0f8ff;
    border-color: #2271b1;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(34, 113, 177, 0.15);
}

.element-item.dragging {
    opacity: 0.5;
    transform: rotate(5deg);
}

.element-icon {
    font-size: 20px;
    width: 24px;
    text-align: center;
}

.element-info {
    flex: 1;
    min-width: 0;
}

.element-name {
    font-size: 13px;
    font-weight: 600;
    color: #333;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.element-description {
    font-size: 11px;
    color: #666;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Error State */
.pdf-builder-error {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex: 1;
    gap: 20px;
    text-align: center;
}

.pdf-builder-error h3 {
    color: #d32f2f;
    font-size: 18px;
    margin: 0;
}

.pdf-builder-error p {
    color: #666;
    max-width: 400px;
}

/* Responsive Design */
@media (max-width: 1400px) {
    .pdf-builder-elements-sidebar,
    .pdf-builder-properties {
        width: 250px;
    }
}

@media (max-width: 1200px) {
    .pdf-builder-elements-sidebar,
    .pdf-builder-properties {
        width: 220px;
    }
}

@media (max-width: 1000px) {
    .pdf-builder-content {
        flex-direction: column;
        height: calc(100vh - 120px);
    }

    .pdf-builder-elements-sidebar {
        width: 100%;
        border-right: none;
        border-bottom: 1px solid #e5e5e5;
        max-height: 200px;
    }

    .pdf-builder-canvas-area {
        flex: 1;
    }

    .pdf-builder-properties {
        width: 100%;
        border-left: none;
        border-top: 1px solid #e5e5e5;
        max-height: 250px;
    }
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

<script>
console.log('[TEMPLATE] Début du script template-editor.php');
document.addEventListener('DOMContentLoaded', function() {
    console.log('[TEMPLATE] DOMContentLoaded déclenché');
    
    // Initialize editor when bundle is ready
    if (typeof window.PDFBuilderPro !== 'undefined') {
        initializeEditor();
    } else {
        // Wait for bundle to load (with timeout)
        var timeout = setTimeout(function() {
            console.error('❌ PDF Builder bundle failed to load');
            showError('PDF Builder bundle failed to load. Please refresh the page.');
        }, 10000);

        // Listen for bundle load
        var checkInterval = setInterval(function() {
            if (typeof window.PDFBuilderPro !== 'undefined') {
                clearTimeout(timeout);
                clearInterval(checkInterval);
                initializeEditor();
            }
        }, 100);
    }

    function initializeEditor() {
        try {
            console.log('✅ [TEMPLATE] Initializing PDF Canvas Editor - PDFBuilderPro available:', typeof window.PDFBuilderPro);
            
            // Set up AJAX globals
            if (typeof ajaxurl === 'undefined') {
                ajaxurl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
            }
            if (typeof window.pdfBuilderNonce === 'undefined') {
                window.pdfBuilderNonce = '<?php echo wp_create_nonce('pdf_builder_templates'); ?>';
            }
            
            // Get container
            var container = document.getElementById('pdf-canvas-container');
            if (!container) {
                throw new Error('Canvas container not found');
            }

            // Initialize canvas editor
            console.log('🎯 [TEMPLATE] Creating PDFCanvasVanilla instance...');
            var editor = new window.PDFBuilderPro.PDFCanvasVanilla('pdf-builder-canvas', {
                width: 595,
                height: 842,
                templateId: <?php echo isset($_GET['template_id']) ? intval($_GET['template_id']) : '0'; ?>
            });
            console.log('✅ [TEMPLATE] PDFCanvasVanilla instance created:', editor);

            // Initialize the editor
            console.log('🚀 [TEMPLATE] Calling editor.init()...');
            editor.init().then(function() {
                console.log('✅ [TEMPLATE] Editor initialized successfully');
                // Initialize elements sidebar
                initializeElementsSidebar(editor);

                // Setup event listeners
                setupToolbarEvents(editor);
                setupCanvasEvents(editor);

                // Initialize view controls state
                initializeViewControls(editor);

                // Show editor, hide loading
                document.getElementById('pdf-builder-loading').style.display = 'none';
                document.getElementById('pdf-builder-editor').style.display = 'flex';

                console.log('✅ PDF Editor initialized successfully');
            }).catch(function(error) {
                console.error('❌ Error initializing editor:', error);
                showError('Failed to initialize editor: ' + error.message);
            });

        } catch (error) {
            console.error('❌ Error initializing editor:', error);
            showError('Failed to initialize editor: ' + error.message);
        }
    }

    function setupToolbarEvents(editor) {
        var currentTool = 'select';
        var toolButtons = document.querySelectorAll('.tool-btn');

        // Tool definitions matching the test specifications
        var toolDefinitions = {
            'select': { action: 'select', shortcut: 'V' },
            'add-text': { action: 'add-text', shortcut: 'T' },
            'add-text-title': { action: 'add-text-title', shortcut: 'H' },
            'add-text-subtitle': { action: 'add-text-subtitle', shortcut: 'S' },
            'add-rectangle': { action: 'add-rectangle', shortcut: 'R' },
            'add-circle': { action: 'add-circle', shortcut: 'C' },
            'add-line': { action: 'add-line', shortcut: 'L' },
            'add-arrow': { action: 'add-arrow', shortcut: 'A' },
            'add-triangle': { action: 'add-triangle', shortcut: '3' },
            'add-star': { action: 'add-star', shortcut: '5' },
            'add-divider': { action: 'add-divider', shortcut: 'D' },
            'add-image': { action: 'add-image', shortcut: 'I' }
        };

        // Function to set active tool
        function setActiveTool(toolId) {
            // Remove active class from all tool buttons
            toolButtons.forEach(function(btn) {
                btn.classList.remove('active');
            });

            // Add active class to selected tool
            var activeBtn = document.getElementById('tool-' + toolId);
            if (activeBtn) {
                activeBtn.classList.add('active');
            }

            currentTool = toolId;
            console.log('🔧 Tool changed to:', toolId);

            // Notify canvas about tool change
            if (editor && typeof editor.setTool === 'function') {
                editor.setTool(toolId);
            }
        }

        // Function to handle tool action
        function handleToolAction(toolId) {
            var toolDef = toolDefinitions[toolId];
            if (!toolDef) return;

            if (toolId === 'select') {
                setActiveTool('select');
            } else if (toolId.startsWith('add-')) {
                // Add element and keep tool active
                addElementByTool(toolId);
            }
        }

        // Function to add element based on tool
        function addElementByTool(toolId) {
            var elementType = toolId.replace('add-', '');
            var defaultProps = {};

            switch (elementType) {
                case 'text':
                    defaultProps = {
                        x: 50,
                        y: 50,
                        text: '<?php esc_html_e('New Text', 'pdf-builder-pro'); ?>',
                        fontSize: 14,
                        color: '#000000'
                    };
                    break;
                case 'text-title':
                    defaultProps = {
                        x: 50,
                        y: 50,
                        text: '<?php esc_html_e('Title', 'pdf-builder-pro'); ?>',
                        fontSize: 24,
                        color: '#000000',
                        fontWeight: 'bold'
                    };
                    break;
                case 'text-subtitle':
                    defaultProps = {
                        x: 50,
                        y: 100,
                        text: '<?php esc_html_e('Subtitle', 'pdf-builder-pro'); ?>',
                        fontSize: 18,
                        color: '#666666'
                    };
                    break;
                case 'rectangle':
                    defaultProps = {
                        x: 100,
                        y: 100,
                        width: 100,
                        height: 60,
                        fillColor: '#cccccc',
                        strokeColor: '#000000'
                    };
                    break;
                case 'circle':
                    defaultProps = {
                        x: 150,
                        y: 150,
                        width: 80,
                        height: 80,
                        fillColor: '#cccccc',
                        strokeColor: '#000000'
                    };
                    break;
                case 'line':
                    defaultProps = {
                        x: 200,
                        y: 200,
                        width: 100,
                        height: 2,
                        strokeColor: '#000000'
                    };
                    break;
                case 'arrow':
                    defaultProps = {
                        x: 200,
                        y: 200,
                        width: 100,
                        height: 20,
                        strokeColor: '#000000'
                    };
                    break;
                case 'triangle':
                    defaultProps = {
                        x: 150,
                        y: 150,
                        width: 80,
                        height: 80,
                        fillColor: '#cccccc',
                        strokeColor: '#000000'
                    };
                    break;
                case 'star':
                    defaultProps = {
                        x: 150,
                        y: 150,
                        width: 80,
                        height: 80,
                        fillColor: '#cccccc',
                        strokeColor: '#000000'
                    };
                    break;
                case 'divider':
                    defaultProps = {
                        x: 50,
                        y: 200,
                        width: 500,
                        height: 2,
                        strokeColor: '#cccccc'
                    };
                    break;
                case 'image':
                    defaultProps = {
                        x: 100,
                        y: 100,
                        width: 150,
                        height: 100,
                        src: '' // Will need image picker
                    };
                    break;
            }

            editor.addElement(elementType, defaultProps);
        }

        // Add click handlers for tool buttons
        toolButtons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var toolId = this.dataset.tool;
                handleToolAction(toolId);
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Only handle shortcuts when not typing in inputs
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                return;
            }

            var key = e.key.toUpperCase();
            var toolId = null;

            // Find tool by shortcut
            for (var id in toolDefinitions) {
                if (toolDefinitions[id].shortcut === key) {
                    toolId = id;
                    break;
                }
            }

            if (toolId) {
                e.preventDefault();
                handleToolAction(toolId);
            }
        });

        // Action buttons
        document.getElementById('btn-save').addEventListener('click', function() {
            console.log('💾 Saving template...');
            alert('<?php esc_html_e('Save functionality coming soon', 'pdf-builder-pro'); ?>');
        });

        document.getElementById('btn-export-pdf').addEventListener('click', function() {
            console.log('📄 Exporting PDF...');
            alert('<?php esc_html_e('Export functionality coming soon', 'pdf-builder-pro'); ?>');
        });

        // View controls
        document.getElementById('btn-toggle-grid').addEventListener('click', function() {
            if (editor && editor.transformationsManager) {
                var showGrid = !editor.options.showGrid;
                editor.options.showGrid = showGrid;
                this.classList.toggle('active', showGrid);
                editor.render();
                console.log('🔲 Grid toggled:', showGrid);
            }
        });

        document.getElementById('btn-toggle-snap').addEventListener('click', function() {
            if (editor && editor.transformationsManager) {
                var snapToGrid = !editor.transformationsManager.snapToGrid;
                editor.transformationsManager.snapToGrid = snapToGrid;
                this.classList.toggle('active', snapToGrid);
                console.log('📌 Snap to grid toggled:', snapToGrid);
            }
        });

        // Zoom controls
        var zoomLevel = 1;
        document.getElementById('btn-zoom-in').addEventListener('click', function() {
            zoomLevel = Math.min(zoomLevel + 0.1, 3);
            updateZoom(zoomLevel);
        });

        document.getElementById('btn-zoom-out').addEventListener('click', function() {
            zoomLevel = Math.max(zoomLevel - 0.1, 0.5);
            updateZoom(zoomLevel);
        });

        function updateZoom(level) {
            var canvas = document.getElementById('pdf-builder-canvas');
            canvas.style.transform = 'scale(' + level + ')';
            canvas.style.transformOrigin = 'top center';
            document.getElementById('zoom-level').textContent = Math.round(level * 100) + '%';
        }

        // Set initial tool
        setActiveTool('select');
    }

    function initializeViewControls(editor) {
        // Set initial state for grid button
        var gridBtn = document.getElementById('btn-toggle-grid');
        if (gridBtn && editor.options.showGrid) {
            gridBtn.classList.add('active');
        }

        // Set initial state for snap button
        var snapBtn = document.getElementById('btn-toggle-snap');
        if (snapBtn && editor.transformationsManager && editor.transformationsManager.snapToGrid) {
            snapBtn.classList.add('active');
        }
    }

    // Header button handlers
    document.getElementById('btn-preview').addEventListener('click', function() {
        console.log('👁️ Opening preview...');
        // TODO: Implement preview functionality
        alert('<?php esc_html_e('Preview functionality coming soon', 'pdf-builder-pro'); ?>');
    });

    function setupCanvasEvents(editor) {
        // Listen for element selection
        editor.on('element-selected', function(elementId) {
            console.log('🎯 Element selected:', elementId);
            updatePropertiesPanel(elementId);
        });

        // Listen for element deselection
        editor.on('selection-cleared', function() {
            document.getElementById('properties-content').innerHTML = 
                '<p class="no-selection"><?php esc_html_e('Select an element to edit properties', 'pdf-builder-pro'); ?></p>';
        });
    }

    function updatePropertiesPanel(elementId) {
        var content = document.getElementById('properties-content');
        content.innerHTML = '<p><strong><?php esc_html_e('Element:', 'pdf-builder-pro'); ?></strong> ' + elementId + '</p>';
        content.innerHTML += '<p><em><?php esc_html_e('Property editing coming soon', 'pdf-builder-pro'); ?></em></p>';
    }

    function initializeElementsSidebar(editor) {
        var elementsContainer = document.getElementById('elements-container');
        var searchInput = document.getElementById('elements-search');

        // Get elements library
        var elementLibrary = window.PDFBuilderPro.getAllElements();

        // Function to render elements
        function renderElements(elements) {
            elementsContainer.innerHTML = '';

            for (var category in elements) {
                var categoryElements = elements[category];
                if (categoryElements.length === 0) continue;

                // Create category section
                var categoryDiv = document.createElement('div');
                categoryDiv.className = 'element-category';

                var categoryTitle = document.createElement('div');
                categoryTitle.className = 'element-category-title';
                categoryTitle.textContent = getCategoryLabel(category);
                categoryDiv.appendChild(categoryTitle);

                // Add elements
                categoryElements.forEach(function(element) {
                    var elementDiv = document.createElement('div');
                    elementDiv.className = 'element-item';
                    elementDiv.draggable = true;
                    elementDiv.setAttribute('data-element-type', element.type);

                    elementDiv.innerHTML = `
                        <div class="element-icon">${element.icon}</div>
                        <div class="element-info">
                            <div class="element-name">${element.label}</div>
                            <div class="element-description">${element.description}</div>
                        </div>
                    `;

                    // Add click handler
                    elementDiv.addEventListener('click', function() {
                        addElementToCanvas(editor, element);
                    });

                    // Note: Drag functionality is handled by PDFCanvasDragDropManager
                    // No need for additional drag event listeners here

                    categoryDiv.appendChild(elementDiv);
                });

                elementsContainer.appendChild(categoryDiv);
            }
        }

        // Function to get category label
        function getCategoryLabel(category) {
            var labels = {
                special: 'Éléments WooCommerce'
            };
            return labels[category] || category;
        }

        // Function to add element to canvas
        function addElementToCanvas(editor, element) {
            var defaultProps = element.defaultProps || {};
            editor.addElement(element.type, defaultProps);
        }

        // Initial render
        renderElements(elementLibrary);

        // Search functionality
        searchInput.addEventListener('input', function() {
            var query = this.value.trim();
            if (query.length === 0) {
                renderElements(elementLibrary);
            } else {
                var filteredElements = {};
                for (var category in elementLibrary) {
                    var categoryElements = elementLibrary[category];
                    var filtered = categoryElements.filter(function(element) {
                        return element.label.toLowerCase().includes(query.toLowerCase()) ||
                               element.description.toLowerCase().includes(query.toLowerCase()) ||
                               element.type.toLowerCase().includes(query.toLowerCase());
                    });
                    if (filtered.length > 0) {
                        filteredElements[category] = filtered;
                    }
                }
                renderElements(filteredElements);
            }
        });
    }

    function showError(message) {
        document.getElementById('pdf-builder-loading').style.display = 'none';
        document.getElementById('pdf-builder-error').style.display = 'flex';
        document.getElementById('error-message').textContent = message;

        document.getElementById('btn-retry').addEventListener('click', function() {
            location.reload();
        });
    }
});

<!-- ===== INITIALISATION DU CANVAS EDITOR ===== -->
<script>
console.log('[INIT] 🚀 Initialisation du Canvas Editor - Début');

// Attendre que PDFBuilderPro soit disponible
function waitForPDFBuilder(maxRetries = 20) {
    console.log('[INIT] Attente du chargement de PDFBuilderPro...');
    
    if (typeof window.PDFBuilderPro !== 'undefined') {
        console.log('[INIT] ✅ PDFBuilderPro trouvé!', typeof window.PDFBuilderPro);
        initializeCanvas();
        return;
    }
    
    if (maxRetries > 0) {
        setTimeout(() => waitForPDFBuilder(maxRetries - 1), 250);
    } else {
        console.error('[INIT] ❌ PDFBuilderPro pas trouvé après attente');
        showError('Impossible de charger le système PDF Builder');
    }
}

// Initialiser le canvas une fois PDFBuilderPro disponible
function initializeCanvas() {
    console.log('[INIT] Initialisation du Canvas...');
    
    // Masquer le loading, afficher l'éditeur
    var loading = document.getElementById('pdf-builder-loading');
    var editor = document.getElementById('pdf-builder-editor');
    
    if (loading) loading.style.display = 'none';
    if (editor) editor.style.display = 'block';
    
    // Récupérer l'ID du template depuis l'URL
    var templateId = new URLSearchParams(window.location.search).get('template_id');
    console.log('[INIT] Template ID:', templateId);
    
    // Options de configuration du canvas
    var canvasOptions = {
        containerId: 'pdf-canvas-container',
        templateId: templateId || null,
        width: 595,  // A4 largeur
        height: 842, // A4 hauteur
        zoom: 1,
        gridEnabled: true,
        snapToGrid: true,
        gridSize: 10,
        canvasElementId: 'pdf-builder-canvas',
        elementsContainerId: 'elements-container',
        propertiesPanelId: 'properties-content'
    };
    
    console.log('[INIT] Options:', canvasOptions);
    
    try {
        // Initialiser le canvas
        if (window.PDFBuilderPro.init && typeof window.PDFBuilderPro.init === 'function') {
            console.log('[INIT] Appel de PDFBuilderPro.init()');
            window.PDFBuilderPro.init(canvasOptions);
            window.pdfCanvasInstance = window.PDFBuilderPro;
        } else if (window.PDFBuilderPro.PDFCanvasVanilla) {
            console.log('[INIT] Utilisation de PDFCanvasVanilla');
            var canvas = new window.PDFBuilderPro.PDFCanvasVanilla(canvasOptions);
            window.pdfCanvasInstance = canvas;
            canvas.init();
        }
        
        console.log('[INIT] ✅ Canvas initialisé avec succès');
        
        // Configurer le drag & drop de la bibliothèque
        setupDragAndDrop();
        
        // Charger le template si fourni
        if (templateId && window.pdfCanvasInstance && typeof window.pdfCanvasInstance.loadTemplate === 'function') {
            console.log('[INIT] Chargement du template:', templateId);
            window.pdfCanvasInstance.loadTemplate(templateId);
        }
        
    } catch (error) {
        console.error('[INIT] ❌ Erreur lors de l\'initialisation:', error);
        showError('Erreur lors de l\'initialisation du canvas: ' + error.message);
    }
}

// Configurer le drag & drop
function setupDragAndDrop() {
    console.log('[INIT] Configuration du Drag & Drop...');
    
    var elementsContainer = document.getElementById('elements-container');
    var canvas = document.getElementById('pdf-canvas-container') || document.getElementById('pdf-builder-canvas');
    
    if (!canvas) {
        console.warn('[INIT] Canvas non trouvé pour le drag & drop');
        return;
    }
    
    // Événements de drag sur les éléments
    if (elementsContainer) {
        elementsContainer.addEventListener('dragstart', function(e) {
            if (e.target.classList.contains('element-item')) {
                var elementType = e.target.dataset.type;
                var elementData = {
                    type: 'new-element',
                    elementType: elementType,
                    elementData: JSON.parse(e.target.dataset.element || '{}')
                };
                console.log('[DRAGDROP] Début du drag:', elementType);
                e.dataTransfer.effectAllowed = 'copy';
                e.dataTransfer.setData('application/json', JSON.stringify(elementData));
            }
        });
    }
    
    // Événements de drop sur le canvas
    canvas.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
        canvas.classList.add('drag-over');
    });
    
    canvas.addEventListener('dragleave', function(e) {
        if (e.target === canvas) {
            canvas.classList.remove('drag-over');
        }
    });
    
    canvas.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        canvas.classList.remove('drag-over');
        
        try {
            var data = JSON.parse(e.dataTransfer.getData('application/json'));
            
            if (data.type === 'new-element') {
                var rect = canvas.getBoundingClientRect();
                var zoom = (window.pdfCanvasInstance && window.pdfCanvasInstance.zoom) || 1;
                var x = (e.clientX - rect.left) / zoom;
                var y = (e.clientY - rect.top) / zoom;
                
                console.log('[DRAGDROP] Drop:', data.elementType, 'à', { x, y });
                
                if (window.pdfCanvasInstance && typeof window.pdfCanvasInstance.addElement === 'function') {
                    window.pdfCanvasInstance.addElement(data.elementType, { x, y, ...data.elementData });
                }
            }
        } catch (error) {
            console.error('[DRAGDROP] ❌ Erreur:', error);
        }
    });
    
    console.log('[INIT] ✅ Drag & Drop configuré');
}

// Démarrer l'initialisation quand le DOM est prêt
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', waitForPDFBuilder);
} else {
    waitForPDFBuilder();
}

console.log('[INIT] Script d\'initialisation du Canvas chargé');
</script>

