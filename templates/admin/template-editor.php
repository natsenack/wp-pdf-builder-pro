<?php
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}
?>
<div id="wpbody-content">
    <div class="pdf-builder-workspace">
        <!-- Loading State -->
        <div id="pdf-builder-loading" class="pdf-builder-loading">
            <div class="spinner is-active"></div>
            <p><?php esc_html_e('Initializing PDF Editor...', 'pdf-builder-pro'); ?></p>
        </div>

        <!-- Main Editor -->
        <div id="pdf-builder-editor" class="pdf-builder-editor" style="display: none;">
            <!-- Toolbar -->
            <div class="pdf-builder-toolbar">
                <div class="toolbar-group">
                    <h3><?php esc_html_e('Elements', 'pdf-builder-pro'); ?></h3>
                    <button id="btn-add-text" class="toolbar-btn" title="Add Text">
                        <span class="dashicons dashicons-edit"></span> <?php esc_html_e('Text', 'pdf-builder-pro'); ?>
                    </button>
                    <button id="btn-add-rectangle" class="toolbar-btn" title="Add Rectangle">
                        <span class="dashicons dashicons-admin-page"></span> <?php esc_html_e('Rectangle', 'pdf-builder-pro'); ?>
                    </button>
                    <button id="btn-add-circle" class="toolbar-btn" title="Add Circle">
                        <span class="dashicons dashicons-admin-comments"></span> <?php esc_html_e('Circle', 'pdf-builder-pro'); ?>
                    </button>
                    <button id="btn-add-line" class="toolbar-btn" title="Add Line">
                        <span class="dashicons dashicons-minus"></span> <?php esc_html_e('Line', 'pdf-builder-pro'); ?>
                    </button>
                </div>

                <div class="toolbar-group">
                    <h3><?php esc_html_e('Actions', 'pdf-builder-pro'); ?></h3>
                    <button id="btn-save" class="toolbar-btn toolbar-btn-primary" title="Save">
                        <span class="dashicons dashicons-yes"></span> <?php esc_html_e('Save', 'pdf-builder-pro'); ?>
                    </button>
                    <button id="btn-export-pdf" class="toolbar-btn toolbar-btn-primary" title="Export PDF">
                        <span class="dashicons dashicons-download"></span> <?php esc_html_e('Export PDF', 'pdf-builder-pro'); ?>
                    </button>
                    <button id="btn-undo" class="toolbar-btn" title="Undo" disabled>
                        <span class="dashicons dashicons-undo"></span>
                    </button>
                    <button id="btn-redo" class="toolbar-btn" title="Redo" disabled>
                        <span class="dashicons dashicons-redo"></span>
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

.toolbar-btn:hover:not(:disabled) {
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
@media (max-width: 1200px) {
    .pdf-builder-properties {
        width: 250px;
    }
}

@media (max-width: 900px) {
    .pdf-builder-content {
        flex-direction: column;
    }

    .pdf-builder-properties {
        width: 100%;
        border-left: none;
        border-top: 1px solid #e5e5e5;
        max-height: 200px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎨 PDF Builder Editor Template Loaded');
    
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
            console.log('✅ Initializing PDF Canvas Editor');
            
            // Get container
            var container = document.getElementById('pdf-canvas-container');
            if (!container) {
                throw new Error('Canvas container not found');
            }

            // Initialize canvas editor
            var editor = new window.PDFBuilderPro.PDFCanvasVanilla('pdf-builder-canvas', {
                width: 595,
                height: 842,
                templateId: <?php echo isset($_GET['id']) ? intval($_GET['id']) : '0'; ?>
            });

            // Setup event listeners
            setupToolbarEvents(editor);
            setupCanvasEvents(editor);

            // Show editor, hide loading
            document.getElementById('pdf-builder-loading').style.display = 'none';
            document.getElementById('pdf-builder-editor').style.display = 'flex';

            console.log('✅ PDF Editor initialized successfully');

        } catch (error) {
            console.error('❌ Error initializing editor:', error);
            showError('Failed to initialize editor: ' + error.message);
        }
    }

    function setupToolbarEvents(editor) {
        // Add element buttons
        document.getElementById('btn-add-text').addEventListener('click', function() {
            editor.addElement('text', {
                x: 50,
                y: 50,
                text: '<?php esc_html_e('New Text', 'pdf-builder-pro'); ?>',
                fontSize: 14,
                color: '#000000'
            });
        });

        document.getElementById('btn-add-rectangle').addEventListener('click', function() {
            editor.addElement('rectangle', {
                x: 100,
                y: 100,
                width: 100,
                height: 60,
                fillColor: '#cccccc',
                strokeColor: '#000000'
            });
        });

        document.getElementById('btn-add-circle').addEventListener('click', function() {
            editor.addElement('circle', {
                x: 150,
                y: 150,
                width: 80,
                height: 80,
                fillColor: '#cccccc',
                strokeColor: '#000000'
            });
        });

        document.getElementById('btn-add-line').addEventListener('click', function() {
            editor.addElement('line', {
                x: 200,
                y: 200,
                width: 100,
                height: 2,
                strokeColor: '#000000'
            });
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
    }

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

    function showError(message) {
        document.getElementById('pdf-builder-loading').style.display = 'none';
        document.getElementById('pdf-builder-error').style.display = 'flex';
        document.getElementById('error-message').textContent = message;

        document.getElementById('btn-retry').addEventListener('click', function() {
            location.reload();
        });
    }
});
</script>
