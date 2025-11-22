<?php // Content tab content - Updated: 2025-11-18 20:20:00 ?>

<style>
/* Toggle switch styles */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
    cursor: pointer;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    border-radius: 24px;
    transition: 0.3s;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    border-radius: 50%;
    transition: 0.3s;
}

input:checked + .toggle-slider {
    background-color: #007cba;
}

input:checked + .toggle-slider:before {
    transform: translateX(26px);
}
</style>

            <h2>🎨 Contenu & Design</h2>

            <!-- Section Canvas -->
            <div style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border: 2px solid #e9ecef; border-radius: 12px; padding: 10px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <h3 style="color: #495057; margin-top: 0; border-bottom: 2px solid #e9ecef; padding-bottom: 10px;">
                    <span style="display: inline-flex; align-items: center; gap: 10px;">
                        🎨 Canvas
                    </span>
                </h3>

                <p style="color: #666; margin-bottom: 20px;">Configurez l'apparence et le comportement de votre canvas de conception PDF.</p>

                <form method="post" id="canvas-form">
                    <?php wp_nonce_field('pdf_builder_canvas_nonce', 'pdf_builder_canvas_nonce'); ?>
                    <input type="hidden" name="submit_canvas" value="1">

                    <!-- Grille de cartes Canvas -->
                    <div class="canvas-settings-grid">
                        <!-- Carte Dimensions & Format -->
                        <div class="canvas-card" data-category="dimensions">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">📐</span>
                                </div>
                                <h4>Dimensions & Format</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Définissez la taille, la résolution et le format de votre canvas de conception.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="preview-format">
                                    <div >
                                        <span id="card-canvas-width"><?php echo intval(get_option('pdf_builder_canvas_width', 794)); ?></span>×
                                        <span id="card-canvas-height"><?php echo intval(get_option('pdf_builder_canvas_height', 1123)); ?></span>px
                                    </div>
                                    <span class="preview-size" id="card-canvas-dpi">
                                        <?php
                                        $dpi = get_option('pdf_builder_canvas_dpi', 150);
                                        $format = get_option('pdf_builder_canvas_format', 'A4');
                                        echo "{$dpi} DPI - {$format}";
                                        ?>
                                    </span>
                                </div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>⚙️</span> Configurer
                                </button>
                            </div>
                        </div>

                        <!-- Carte Apparence -->
                        <div class="canvas-card" data-category="apparence">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">🎨</span>
                                </div>
                                <h4>Apparence</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Personnalisez les couleurs, bordures et effets visuels du canvas.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="color-preview bg" style="background-color: <?php echo esc_attr(get_option('pdf_builder_canvas_bg_color', '#ffffff')); ?>;" title="Fond"></div>
                                <div class="color-preview border" style="background-color: <?php echo esc_attr(get_option('pdf_builder_canvas_border_color', '#007cba')); ?>;" title="Bordure"></div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>🎨</span> Configurer
                                </button>
                            </div>
                        </div>

                        <!-- Carte Grille & Guides -->
                        <div class="canvas-card" data-category="grille">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">📏</span>
                                </div>
                                <h4>Grille & Guides</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Configurez l'affichage et l'alignement sur la grille de conception.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="grid-preview-container">
                                    <div class="grid-canvas">
                                        <!-- Quadrillage principal -->
                                        <div class="grid-lines">
                                            <div class="grid-line horizontal"></div>
                                            <div class="grid-line horizontal"></div>
                                            <div class="grid-line horizontal"></div>
                                            <div class="grid-line vertical"></div>
                                            <div class="grid-line vertical"></div>
                                            <div class="grid-line vertical"></div>
                                        </div>
                                        <!-- Points d'intersection -->
                                        <div class="grid-dots">
                                            <div class="grid-dot"></div>
                                            <div class="grid-dot"></div>
                                            <div class="grid-dot"></div>
                                            <div class="grid-dot"></div>
                                            <div class="grid-dot"></div>
                                            <div class="grid-dot"></div>
                                            <div class="grid-dot"></div>
                                            <div class="grid-dot"></div>
                                            <div class="grid-dot"></div>
                                        </div>
                                        <!-- Guides d'alignement -->
                                        <div class="guide-lines">
                                            <div class="guide-line horizontal active"></div>
                                            <div class="guide-line vertical active"></div>
                                        </div>
                                        <!-- Élément d'exemple -->
                                        <div class="preview-element">
                                            <div class="element-box"></div>
                                        </div>
                                    </div>
                                    <div class="grid-legend">
                                        <span class="legend-item">📐 Grille</span>
                                        <span class="legend-item">📏 Guides</span>
                                        <span class="legend-item">📦 Élément</span>
                                    </div>
                                </div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>📏</span> Configurer
                                </button>
                            </div>
                        </div>

                        <!-- Carte Zoom -->
                        <div class="canvas-card" id="zoom-navigation-card" data-category="zoom">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">🔍</span>
                                </div>
                                <h4>Zoom</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Contrôlez les niveaux de zoom et les options de navigation.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="zoom-preview">
                                    <span class="zoom-minus">-</span>
                                    <span class="zoom-value" id="zoom-preview-value">
                                        <?php echo intval(get_option('pdf_builder_canvas_zoom_min', 10)); ?>-
                                        <?php echo intval(get_option('pdf_builder_canvas_zoom_max', 500)); ?>%
                                    </span>
                                    <span class="zoom-plus">+</span>
                                </div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>🔍</span> Configurer
                                </button>
                            </div>
                        </div>

                        <!-- Carte Interactions & Comportement -->
                        <div class="canvas-card" data-category="interactions">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">🎯</span>
                                </div>
                                <h4>Interactions & Comportement</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Contrôlez les interactions canvas, la sélection et les raccourcis clavier.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="interactions-preview-container">
                                    <!-- Canvas miniature avec éléments -->
                                    <div class="mini-canvas">
                                        <!-- Grille de fond -->
                                        <div class="mini-canvas-grid"></div>

                                        <!-- Éléments sur le canvas -->
                                        <div class="mini-element text-element" style="top: 15px; left: 20px; width: 40px; height: 20px;">
                                            <div class="mini-element-content">T</div>
                                        </div>
                                        <div class="mini-element shape-element selected" style="top: 45px; left: 15px; width: 35px; height: 25px;">
                                            <div class="mini-element-content">□</div>
                                            <!-- Poignées de sélection -->
                                            <div class="mini-handle nw"></div>
                                            <div class="mini-handle ne"></div>
                                            <div class="mini-handle sw"></div>
                                            <div class="mini-handle se"></div>
                                            <div class="mini-handle rotation" style="top: -8px; left: 50%; transform: translateX(-50%);"></div>
                                        </div>
                                        <div class="mini-element image-element" style="top: 20px; left: 70px; width: 30px; height: 30px;">
                                            <div class="mini-element-content">🖼</div>
                                        </div>

                                        <!-- Sélection rectangle en cours -->
                                        <div class="selection-rectangle" style="top: 10px; left: 10px; width: 60px; height: 40px;"></div>

                                        <!-- Curseur de souris -->
                                        <div class="mouse-cursor" style="top: 55px; left: 85px;">
                                            <div class="cursor-icon">👆</div>
                                        </div>
                                    </div>

                                    <!-- Contrôles en bas -->
                                    <div class="interactions-controls">
                                        <div class="selection-mode-indicator">
                                            <span class="mode-icon active" title="Rectangle">▭</span>
                                            <span class="mode-icon" title="Lasso">🪢</span>
                                            <span class="mode-icon" title="Clic">👆</span>
                                        </div>
                                        <div class="interaction-status">
                                            <span class="status-indicator selecting">Sélection en cours</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>🎯</span> Configurer
                                </button>
                            </div>
                        </div>

                        <!-- Carte Export & Qualité -->
                        <div class="canvas-card" data-category="export">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">📤</span>
                                </div>
                                <h4>Export & Qualité</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Configurez les formats et la qualité d'export des designs.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="export-preview-container">
                                    <div class="export-formats">
                                        <span class="format-badge png">PNG</span>
                                        <span class="format-badge jpg">JPG</span>
                                        <span class="format-badge pdf">PDF</span>
                                    </div>
                                    <div class="export-quality-bar">
                                        <div class="quality-fill" style="width: 90%;"></div>
                                        <span class="quality-text">90%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>📤</span> Configurer
                                </button>
                            </div>
                        </div>

                        <!-- Carte Performance -->
                        <div class="canvas-card" data-category="performance">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">⚡</span>
                                </div>
                                <h4>Performance</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Optimisez les FPS, mémoire et temps de réponse.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="performance-preview-container">
                                    <div class="performance-metrics">
                                        <div class="metric-item">
                                            <span class="metric-label">FPS</span>
                                            <span class="metric-value"><?php echo intval(get_option('pdf_builder_canvas_fps_target', 60)); ?></span>
                                        </div>
                                        <div class="metric-item">
                                            <span class="metric-label">RAM JS</span>
                                            <span class="metric-value"><?php echo intval(get_option('pdf_builder_canvas_memory_limit_js', 256)); ?>MB</span>
                                        </div>
                                        <div class="metric-item">
                                            <span class="metric-label">RAM PHP</span>
                                            <span class="metric-value"><?php echo intval(get_option('pdf_builder_canvas_memory_limit_php', 256)); ?>MB</span>
                                        </div>
                                    </div>
                                    <div class="performance-status">
                                        <div class="status-indicator <?php echo (get_option('pdf_builder_canvas_lazy_loading_editor', '1') === '1' && get_option('pdf_builder_canvas_lazy_loading_plugin', '1') === '1') ? 'active' : 'inactive'; ?>">
                                            <span class="status-dot"></span>
                                            <span class="status-text">Lazy Loading</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>⚡</span> Configurer
                                </button>
                            </div>
                        </div>

                        <!-- Carte Sauvegarde Auto -->
                        <div class="canvas-card" data-category="autosave">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">💾</span>
                                </div>
                                <h4>Sauvegarde Auto</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Gérez la sauvegarde automatique et l'historique des versions.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="autosave-preview-container">
                                    <div class="autosave-indicator">
                                        <div class="autosave-icon">⏰</div>
                                        <div class="autosave-timer">5min</div>
                                        <div class="autosave-status active"></div>
                                    </div>
                                    <div class="autosave-versions">
                                        <div class="version-dot" title="Version 1"></div>
                                        <div class="version-dot" title="Version 2"></div>
                                        <div class="version-dot active" title="Version actuelle"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>💾</span> Configurer
                                </button>
                            </div>
                        </div>

                        <!-- Carte Debug -->
                        <div class="canvas-card" data-category="debug">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">🐛</span>
                                </div>
                                <h4>Debug</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Outils de débogage et monitoring des performances.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="debug-preview-container">
                                    <div class="debug-console">
                                        <div class="console-line">
                                            <span class="console-timestamp">[14:32:15]</span>
                                            <span class="console-level info">INFO</span>
                                            <span class="console-message">Canvas initialized</span>
                                        </div>
                                        <div class="console-line">
                                            <span class="console-timestamp">[14:32:16]</span>
                                            <span class="console-level warn">WARN</span>
                                            <span class="console-message">Memory usage: 85%</span>
                                        </div>
                                        <div class="console-line">
                                            <span class="console-timestamp">[14:32:17]</span>
                                            <span class="console-level error">ERROR</span>
                                            <span class="console-message">Failed to load image</span>
                                        </div>
                                        <div class="console-cursor">_</div>
                                    </div>
                                    <div class="debug-stats">
                                        <div class="stat-item">
                                            <span class="stat-label">FPS</span>
                                            <span class="stat-value">60</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-label">RAM</span>
                                            <span class="stat-value">85MB</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-label">Errors</span>
                                            <span class="stat-value">2</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>🐛</span> Configurer
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

                <!-- Section Templates -->
            <div style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border: 2px solid #e9ecef; border-radius: 12px; padding: 30px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <h3 style="color: #495057; margin-top: 0; border-bottom: 2px solid #e9ecef; padding-bottom: 10px;">
                    <span style="display: inline-flex; align-items: center; gap: 10px;">
                        📋 Templates
                        <span style="font-size: 12px; background: #28a745; color: white; padding: 2px 8px; border-radius: 10px; font-weight: normal;">ACTIF</span>
                    </span>
                </h3>

                <form method="post" action="">
                    <?php wp_nonce_field('pdf_builder_templates', 'pdf_builder_templates_nonce'); ?>
                    <input type="hidden" name="current_tab" value="templates">

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="default_template">Template par défaut</label></th>
                            <td>
                                <select id="default_template" name="default_template">
                                    <option value="blank" <?php selected(get_option('pdf_builder_default_template', 'blank'), 'blank'); ?>>Page blanche</option>
                                    <option value="invoice" <?php selected(get_option('pdf_builder_default_template', 'blank'), 'invoice'); ?>>Facture</option>
                                    <option value="quote" <?php selected(get_option('pdf_builder_default_template', 'blank'), 'quote'); ?>>Devis</option>
                                </select>
                                <p class="description">Template utilisé par défaut pour nouveaux documents</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="template_library_enabled">Bibliothèque de templates</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="template_library_enabled" name="template_library_enabled" value="1" <?php checked(get_option('pdf_builder_template_library_enabled', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Active la bibliothèque de templates prédéfinis</p>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>

<script>
    // Canvas configuration modals functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Handle canvas configure buttons
        const configureButtons = document.querySelectorAll('.canvas-configure-btn');
        configureButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const card = this.closest('.canvas-card');
                const category = card.getAttribute('data-category');
                const modalId = 'canvas-' + category + '-modal';
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'block';
                }
            });
        });

        // Handle modal close buttons
        const closeButtons = document.querySelectorAll('.canvas-modal-close, .canvas-modal-cancel');
        closeButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const modal = this.closest('.canvas-modal');
                modal.style.display = 'none';
            });
        });

        // Handle modal save buttons
        const saveButtons = document.querySelectorAll('.canvas-modal-save');
        saveButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const category = this.getAttribute('data-category');
                const modal = this.closest('.canvas-modal');
                const form = modal.querySelector('form');

                console.log('Save button clicked for category:', category);
                console.log('pdf_builder_ajax available:', typeof pdf_builder_ajax !== 'undefined');
                if (typeof pdf_builder_ajax !== 'undefined') {
                    console.log('pdf_builder_ajax:', pdf_builder_ajax);
                }

                if (!form) {
                    console.error('Form not found in modal for category:', category);
                    return;
                }

                // Collect form data
                const formData = new FormData(form);
                formData.append('action', 'pdf_builder_save_canvas_settings');
                formData.append('category', category);
                formData.append('nonce', pdf_builder_ajax?.nonce || '');

                // Show loading state
                const originalText = this.textContent;
                this.textContent = 'Sauvegarde...';
                this.disabled = true;

                // Send AJAX request
                fetch(pdf_builder_ajax.ajax_url, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => {
                    console.log('AJAX response received:', response);
                    return response.json();
                })
                .then(data => {
                    console.log('AJAX data received:', data);
                    if (data.success) {
                        // Success - close modal and show success message
                        modal.style.display = 'none';
                        this.textContent = originalText;
                        this.disabled = false;

                        // Update preview cards if needed
                        updateCanvasPreviews(category);

                        // Dispatch event for other components to update
                        window.dispatchEvent(new CustomEvent('canvasSettingsUpdated', {
                            detail: { category: category, data: data.data }
                        }));

                        // Show success notification (you can enhance this)
                        alert('Paramètres sauvegardés avec succès !');
                    } else {
                        // Error
                        console.error('Save error:', data);
                        this.textContent = originalText;
                        this.disabled = false;
                        alert('Erreur lors de la sauvegarde: ' + (data.data?.message || 'Erreur inconnue'));
                    }
                })
                .catch(error => {
                    console.error('AJAX error:', error);
                    this.textContent = originalText;
                    this.disabled = false;
                    alert('Erreur de connexion lors de la sauvegarde');
                });
            });
        });

        // Function to update canvas preview cards after save
        function updateCanvasPreviews(category) {
            switch(category) {
                case 'dimensions':
                    // Update dimensions preview
                    const widthSpan = document.getElementById('card-canvas-width');
                    const heightSpan = document.getElementById('card-canvas-height');
                    if (widthSpan && heightSpan) {
                        const format = document.getElementById('canvas_format')?.value || 'A4';
                        const orientation = document.getElementById('canvas_orientation')?.value || 'portrait';
                        const dpi = document.getElementById('canvas_dpi')?.value || 150;

                        // Calculate dimensions based on format
                        const formatDimensions = {
                            'A4': orientation === 'landscape' ? {width: 1123, height: 794} : {width: 794, height: 1123},
                            'A3': orientation === 'landscape' ? {width: 1587, height: 1123} : {width: 1123, height: 1587},
                            'A5': orientation === 'landscape' ? {width: 559, height: 397} : {width: 397, height: 559}
                        };

                        const dims = formatDimensions[format] || formatDimensions['A4'];
                        widthSpan.textContent = dims.width;
                        heightSpan.textContent = dims.height;
                    }
                    break;

                case 'zoom':
                    // Update zoom preview
                    updateZoomPreview();
                    break;

                // Add other categories as needed
                default:
                    console.log('Preview update not implemented for category:', category);
            }
        }

        // Update zoom preview
        function updateZoomPreview() {
            const zoomPreview = document.getElementById('zoom-preview-value');
            if (zoomPreview) {
                // Get current values from options (this would need AJAX in real implementation)
                // For now, we'll update it when settings are saved
                fetch(pdf_builder_ajax.ajax_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'pdf_builder_get_canvas_settings',
                        nonce: pdf_builder_ajax?.nonce || ''
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const minZoom = data.data.min_zoom || 10;
                        const maxZoom = data.data.max_zoom || 500;
                        zoomPreview.textContent = minZoom + '-' + maxZoom + '%';
                    }
                })
                .catch(error => {
                    console.error('Error updating zoom preview:', error);
                });
            }
        }

        // Update zoom preview on settings update
        window.addEventListener('canvasSettingsUpdated', updateZoomPreview);

        // Initial update
        updateZoomPreview();
    });
</script>