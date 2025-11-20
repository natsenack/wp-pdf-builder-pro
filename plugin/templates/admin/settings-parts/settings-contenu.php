<?php // Content tab content - Updated: 2025-11-18 20:20:00 ?>

<style>
/* Dynamic status indicators for canvas cards */
.canvas-card-status.active {
    background: #28a745;
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}

.canvas-card-status.inactive {
    background: #dc3545;
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}

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
                                <div class="grid-preview">
                                    <div class="grid-line"></div>
                                    <div class="grid-dot"></div>
                                    <div class="grid-line"></div>
                                </div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>📏</span> Configurer
                                </button>
                            </div>
                        </div>

                        <!-- Carte Zoom & Navigation -->
                        <div class="canvas-card" id="zoom-navigation-card" data-category="zoom">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">🔍</span>
                                    <span class="canvas-card-status <?php echo get_option('pdf_builder_canvas_pan_enabled', true) ? 'active' : 'inactive'; ?>" id="zoom-navigation-status"><?php echo get_option('pdf_builder_canvas_pan_enabled', true) ? 'ACTIF' : 'INACTIF'; ?></span>
                                </div>
                                <h4>Zoom & Navigation</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Contrôlez les niveaux de zoom et les options de navigation.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="zoom-preview">
                                    <span class="zoom-minus">-</span>
                                    <span class="zoom-value">10-500%</span>
                                    <span class="zoom-plus">+</span>
                                </div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>🔍</span> Configurer
                                </button>
                            </div>
                        </div>

                        <!-- Carte Éléments Interactifs -->
                        <div class="canvas-card" data-category="interaction">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">👆</span>
                                </div>
                                <h4>Éléments Interactifs</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Activez les interactions comme glisser-déposer et redimensionnement.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="interaction-preview">
                                    <span class="element-handle" title="Redimensionner">↔</span>
                                    <span class="element-handle" title="Déplacer">↕</span>
                                    <span class="element-handle" title="Pivoter">↻</span>
                                </div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>👆</span> Configurer
                                </button>
                            </div>
                        </div>

                        <!-- Carte Comportement -->
                        <div class="canvas-card" data-category="comportement">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">⚙️</span>
                                </div>
                                <h4>Comportement</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Définissez la sélection multiple et les raccourcis clavier.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="behavior-preview">
                                    <span class="behavior-icon" title="Sélection">👆</span>
                                    <span class="behavior-icon" title="Raccourcis">⌨️</span>
                                </div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>⚙️</span> Configurer
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
                                <div class="export-preview">
                                    <span class="export-format">PNG</span>
                                    <span class="export-quality">90%</span>
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
                                <p>Optimisez les FPS et la gestion mémoire du canvas.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="performance-bar">
                                    <div class="performance-fill" style="width: 80%;"></div>
                                </div>
                                <div class="performance-fps">60 FPS</div>
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
                                    <span class="canvas-card-status <?php echo get_option('pdf_builder_canvas_autosave_enabled', true) ? 'ACTIF' : 'INACTIF'; ?>" id="autosave-status"><?php echo get_option('pdf_builder_canvas_autosave_enabled', true) ? 'ACTIF' : 'INACTIF'; ?></span>
                                </div>
                                <h4>Sauvegarde Auto</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Gérez la sauvegarde automatique et l'historique des versions.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="autosave-preview">
                                    <span class="autosave-icon">⏰</span>
                                    <span class="autosave-timer">5min</span>
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
                                    <span class="canvas-card-status <?php echo get_option('pdf_builder_canvas_debug_enabled', false) ? 'ACTIF' : 'INACTIF'; ?>" id="debug-status"><?php echo get_option('pdf_builder_canvas_debug_enabled', false) ? 'ACTIF' : 'INACTIF'; ?></span>
                                </div>
                                <h4>Debug</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Outils de débogage et monitoring des performances.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="debug-preview">
                                    <span class="debug-icon">📊</span>
                                    <span class="debug-fps">60 FPS</span>
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

        // Handle modal overlay clicks
        const modalOverlays = document.querySelectorAll('.canvas-modal-overlay');
        modalOverlays.forEach(function(overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.closest('.canvas-modal').style.display = 'none';
                }
            });
        });

        // Update status indicators after modal save
        document.addEventListener('modalSaved', function(e) {
            const category = e.detail.category;
            const settings = e.detail.settings;

            console.log('Modal saved event received:', category, settings);
            updateStatusIndicator(category, settings);
        });
    });

    // Function to update status indicators based on category and settings
    function updateStatusIndicator(category, settings) {
        console.log('Updating status indicator for category:', category, 'with settings:', settings);

        if (category === 'canvas') {
            // Update zoom toggle indicator
            if (settings.hasOwnProperty('pan_enabled')) {
                console.log('Found pan_enabled in settings:', settings.pan_enabled);
                const zoomStatus = document.getElementById('zoom-navigation-status');
                if (zoomStatus) {
                    const isActive = settings.pan_enabled == '1' || settings.pan_enabled === true || settings.pan_enabled === 1;
                    zoomStatus.textContent = isActive ? 'ACTIF' : 'INACTIF';
                    zoomStatus.className = 'canvas-card-status ' + (isActive ? 'active' : 'inactive');
                    console.log('Zoom status updated to:', zoomStatus.textContent);
                }
            }

            // Update autosave toggle indicator
            if (settings.hasOwnProperty('auto_save')) {
                const autosaveStatus = document.getElementById('autosave-status');
                if (autosaveStatus) {
                    const isActive = settings.auto_save == '1' || settings.auto_save === true || settings.auto_save === 1;
                    autosaveStatus.textContent = isActive ? 'ACTIF' : 'INACTIF';
                    autosaveStatus.className = 'canvas-card-status ' + (isActive ? 'active' : 'inactive');
                }
            }

            // Update debug toggle indicator
            if (settings.hasOwnProperty('debug_enabled')) {
                const debugStatus = document.getElementById('debug-status');
                if (debugStatus) {
                    const isActive = settings.debug_enabled == '1' || settings.debug_enabled === true || settings.debug_enabled === 1;
                    debugStatus.textContent = isActive ? 'ACTIF' : 'INACTIF';
                    debugStatus.className = 'canvas-card-status ' + (isActive ? 'active' : 'inactive');
                }
            }
        }
    }
</script>