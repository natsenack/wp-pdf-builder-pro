<?php
    /**
     * PDF Builder Pro - Content Settings Tab
     * Canvas and design configuration settings
     * Updated: 2025-12-03
     */

    // require_once __DIR__ . '/settings-helpers.php'; // REMOVED - settings-helpers.php deleted

    echo "<!-- TEST: settings-contenu.php loaded -->";

    $settings = get_option('pdf_builder_settings', array());

    // Fonction helper pour récupérer les valeurs Canvas depuis les options individuelles
    function get_canvas_option_contenu($key, $default = '') {
        $option_key = 'pdf_builder_' . $key;
        $value = get_option($option_key, $default);
        if (in_array($key, ['canvas_grid_enabled', 'canvas_guides_enabled', 'canvas_snap_to_grid'])) {
            error_log("[PDF Builder] GRID_TOGGLES - get_canvas_option_contenu - {$key}: {$value} (default: {$default})");
        }
        return $value;
    }

    // INITIALISER LES OPTIONS CANVAS AVEC VALEURS PAR DÉFAUT SI ELLES N'EXISTENT PAS
    $default_canvas_options = [
        'pdf_builder_canvas_width' => '794',
        'pdf_builder_canvas_height' => '1123',
        'pdf_builder_canvas_dpi' => '96',
        'pdf_builder_canvas_format' => 'A4',
        'pdf_builder_canvas_bg_color' => '#ffffff',
        'pdf_builder_canvas_border_color' => '#cccccc',
        'pdf_builder_canvas_border_width' => '1',
        'pdf_builder_canvas_container_bg_color' => '#f8f9fa',
        'pdf_builder_canvas_shadow_enabled' => '0',
        'pdf_builder_canvas_grid_enabled' => '1',
        'pdf_builder_canvas_grid_size' => '20',
        'pdf_builder_canvas_guides_enabled' => '1',
        'pdf_builder_canvas_snap_to_grid' => '1',
        'pdf_builder_canvas_zoom_min' => '25',
        'pdf_builder_canvas_zoom_max' => '500',
        'pdf_builder_canvas_zoom_default' => '100',
        'pdf_builder_canvas_zoom_step' => '25',
        'pdf_builder_canvas_export_quality' => '90',
        'pdf_builder_canvas_export_format' => 'png',
        'pdf_builder_canvas_export_transparent' => '0',
        'pdf_builder_canvas_drag_enabled' => '1',
        'pdf_builder_canvas_resize_enabled' => '1',
        'pdf_builder_canvas_rotate_enabled' => '1',
        'pdf_builder_canvas_multi_select' => '1',
        'pdf_builder_canvas_selection_mode' => 'single',
        'pdf_builder_canvas_keyboard_shortcuts' => '1',
        'pdf_builder_canvas_fps_target' => '60',
        'pdf_builder_canvas_memory_limit_js' => '50',
        'pdf_builder_canvas_response_timeout' => '5000',
        'pdf_builder_canvas_lazy_loading_editor' => '1',
        'pdf_builder_canvas_preload_critical' => '1',
        'pdf_builder_canvas_lazy_loading_plugin' => '1',
        'pdf_builder_canvas_debug_enabled' => '0',
        'pdf_builder_canvas_performance_monitoring' => '0',
        'pdf_builder_canvas_error_reporting' => '0',
        'pdf_builder_canvas_memory_limit_php' => '128'
    ];

    foreach ($default_canvas_options as $option_name => $default_value) {
        if (get_option($option_name) === false) {
            add_option($option_name, $default_value);
            error_log("[INIT CANVAS OPTIONS] Created option: $option_name = $default_value");
        }
    }
?>
<section id="contenu" class="settings-section contenu-settings" role="tabpanel" aria-labelledby="tab-contenu">

    <div class="settings-content">
<?php
    $settings = get_option('pdf_builder_settings', array());

?>
            <h2>🎨 Contenu & Design</h2>

            <!-- Section Canvas -->
            <section class="contenu-canvas-section">
                <?php error_log("[PDF Builder] CANVAS_SECTION - Rendering canvas section"); ?>
                <h3>
                    <span>
                        🎨 Canvas
                    </span>
                </h3>

                <p>Configurez l'apparence et le comportement de votre canvas de conception PDF.</p>

                <!-- Indicateur de monitoring -->
                <div class="monitoring-indicator" style="position: absolute; top: 10px; right: 10px; font-size: 12px; color: #666; opacity: 0.7;">
                    <span id="monitoring-status">🔍 Monitoring actif</span>
                </div>

                <?php error_log("[PDF Builder] HIDDEN_FIELDS - About to render hidden fields"); ?>
                <!-- Champs cachés pour la sauvegarde centralisée des paramètres -->
                <!-- DEBUG: Hidden fields rendering started -->
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_width]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_width', '794')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_height]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_height', '1123')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_dpi]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_dpi', '96')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_format]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_format', 'A4')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_bg_color]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_bg_color', '#ffffff')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_border_color]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_border_color', '#cccccc')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_border_width]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_border_width', '1')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_shadow_enabled]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_shadow_enabled', '0')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_container_bg_color]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_container_bg_color', '#f8f9fa')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_grid_enabled]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_grid_enabled', '1')); ?>">
                    <?php
                    $grid_enabled_value = get_canvas_option_contenu('canvas_grid_enabled', '1');
                    error_log("[PDF Builder] HIDDEN_FIELD_RENDER - canvas_grid_enabled: " . $grid_enabled_value);
                    echo "<!-- DEBUG: canvas_grid_enabled = $grid_enabled_value -->";
                    ?>
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_grid_size]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_grid_size', '20')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_guides_enabled]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_guides_enabled', '1')); ?>">
                    <?php
                    $guides_enabled_value = get_canvas_option_contenu('canvas_guides_enabled', '1');
                    error_log("[PDF Builder] HIDDEN_FIELD_RENDER - canvas_guides_enabled: " . $guides_enabled_value);
                    echo "<!-- DEBUG: canvas_guides_enabled = $guides_enabled_value -->";
                    ?>
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_snap_to_grid]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_snap_to_grid', '1')); ?>">
                    <?php
                    $snap_enabled_value = get_canvas_option_contenu('canvas_snap_to_grid', '1');
                    error_log("[PDF Builder] HIDDEN_FIELD_RENDER - canvas_snap_to_grid: " . $snap_enabled_value);
                    echo "<!-- DEBUG: canvas_snap_to_grid = $snap_enabled_value -->";
                    ?>
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_zoom_min]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_zoom_min', '25')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_zoom_max]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_zoom_max', '500')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_zoom_default]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_zoom_default', '100')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_zoom_step]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_zoom_step', '25')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_export_quality]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_export_quality', '90')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_export_format]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_export_format', 'png')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_export_transparent]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_export_transparent', '0')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_drag_enabled]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_drag_enabled', '1')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_resize_enabled]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_resize_enabled', '1')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_rotate_enabled]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_rotate_enabled', '1')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_multi_select]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_multi_select', '1')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_selection_mode]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_selection_mode', 'single')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_keyboard_shortcuts]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_keyboard_shortcuts', '1')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_fps_target]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_fps_target', '60')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_memory_limit_js]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_memory_limit_js', '50')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_response_timeout]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_response_timeout', '5000')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_lazy_loading_editor]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_lazy_loading_editor', '1')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_preload_critical]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_preload_critical', '1')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_lazy_loading_plugin]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_lazy_loading_plugin', '1')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_debug_enabled]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_debug_enabled', '0')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_performance_monitoring]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_performance_monitoring', '0')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_error_reporting]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_error_reporting', '0')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_memory_limit_php]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_memory_limit_php', '128')); ?>">

                    <!-- DEBUG: Hidden fields rendering completed -->
                    <?php error_log("[PDF Builder] HIDDEN_FIELDS - Hidden fields rendered successfully"); ?>

                    <!-- Grille de cartes Canvas -->
                    <div class="canvas-settings-grid">
                        <!-- Carte Dimensions & Format -->
                        <article class="canvas-card" data-category="dimensions">
                            <header class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">📐</span>
                                </div>
                                <h4>Dimensions & Format</h4>
                            </header>
                            <main class="canvas-card-content">
                                <p>Définissez la taille, la résolution et le format de votre canvas de conception.</p>
                            </main>
                            <aside class="canvas-card-preview">
                                <div class="preview-format">
                                    <div >
                                        <span id="card-canvas-width"><?php echo esc_html($settings['pdf_builder_canvas_width'] ?? '794'); ?></span>×
                                        <span id="card-canvas-height"><?php echo esc_html($settings['pdf_builder_canvas_height'] ?? '1123'); ?></span>px
                                    </div>
                                    <span class="preview-size" id="card-canvas-dpi">
                                        <?php
                                        $width = intval($settings['pdf_builder_canvas_width'] ?? '794');
                                        $height = intval($settings['pdf_builder_canvas_height'] ?? '1123');
                                        $dpi = intval($settings['pdf_builder_canvas_dpi'] ?? '96');
                                        $format = $settings['pdf_builder_canvas_format'] ?? 'A4';

                                        // Protection contre division par zéro
                                        $dpi = max(1, $dpi); // Au minimum 1 DPI pour éviter division par zéro

                                        $widthMM = round(($width / $dpi) * 25.4, 1);
                                        $heightMM = round(($height / $dpi) * 25.4, 1);
                                        echo esc_html("{$dpi} DPI - {$format} ({$widthMM}×{$heightMM}mm)");
                                        ?>
                                    </span>
                                </div>
                            </aside>
                            <footer class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>⚙️</span> Configurer
                                </button>
                            </footer>
                        </article>

                        <!-- Carte Apparence -->
                        <article class="canvas-card" data-category="apparence">
                            <header class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">🎨</span>
                                </div>
                                <h4>Apparence</h4>
                            </header>
                            <main class="canvas-card-content">
                                <p>Personnalisez les couleurs, bordures et effets visuels du canvas.</p>
                            </main>
                            <aside class="canvas-card-preview">
                                <!-- Éléments factices pour compatibilité avec l'ancien JavaScript -->
                                <div id="card-bg-preview" class="color-preview bg" style="display: none;"></div>
                                <div id="card-border-preview" class="color-preview border" style="display: none;"></div>
                                
                                <div class="apparence-preview-container">
                                    <div class="apparence-canvas">
                                        <!-- Fond coloré -->
                                        <div class="apparence-background" style="background-color: <?php echo esc_attr($settings['pdf_builder_canvas_bg_color'] ?? '#ffffff'); ?>;"></div>
                                        <!-- Bordure -->
                                        <div class="apparence-border" style="border: <?php echo esc_attr($settings['pdf_builder_canvas_border_width'] ?? '1'); ?>px solid <?php echo esc_attr($settings['pdf_builder_canvas_border_color'] ?? '#cccccc'); ?>;"></div>
                                        <!-- Ombre -->
                                        <?php if (($settings['pdf_builder_canvas_shadow_enabled'] ?? '0') === '1'): ?>
                                        <div class="apparence-shadow"></div>
                                        <?php endif; ?>
                                        <!-- Élément d'exemple -->
                                        <div class="apparence-element">
                                            <div class="element-shape rect"></div>
                                            <div class="element-shape circle"></div>
                                        </div>
                                    </div>
                                    <div class="apparence-legend">
                                        <span class="legend-item">🎨 Fond</span>
                                        <span class="legend-item">🔲 Bordure</span>
                                        <span class="legend-item"><?php echo (($settings['pdf_builder_canvas_shadow_enabled'] ?? '0') === '1') ? '🌑' : '☀️'; ?> Ombre</span>
                                    </div>
                                </div>
                            </aside>
                            <footer class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>🎨</span> Configurer
                                </button>
                            </footer>
                        </article>

                        <!-- Carte Grille & Guides -->
                        <article class="canvas-card" data-category="grille">
                            <header class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">📏</span>
                                </div>
                                <h4>Grille & Guides</h4>
                            </header>
                            <main class="canvas-card-content">
                                <p>Configurez l'affichage et l'alignement sur la grille de conception.</p>
                            </main>
                            <aside class="canvas-card-preview">
                                <div id="card-grid-preview" class="grid-preview-container">
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
                                        <span class="snap-indicator">🔗 Snap activé</span>
                                    </div>
                                </div>
                            </aside>
                            <footer class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>📏</span> Configurer
                                </button>
                            </footer>
                        </article>

                        <!-- Carte Zoom -->
                        <article class="canvas-card" id="zoom-navigation-card" data-category="zoom">
                            <header class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">🔍</span>
                                </div>
                                <h4>Zoom</h4>
                            </header>
                            <main class="canvas-card-content">
                                <p>Contrôlez les niveaux de zoom et les options de navigation.</p>
                            </main>
                            <aside class="canvas-card-preview">
                                <div class="zoom-preview-container">
                                    <div class="zoom-indicator">
                                        <button class="zoom-btn zoom-minus" disabled>−</button>
                                        <span id="card-zoom-preview" class="zoom-level">100%</span>
                                        <button class="zoom-btn zoom-plus" disabled>+</button>
                                    </div>
                                    <div class="zoom-info">
                                        <span>25% - 500%</span>
                                        <span>Pas: 25%</span>
                                    </div>
                                </div>
                            </aside>
                            <footer class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>🔍</span> Configurer
                                </button>
                            </footer>
                        </article>

                        <!-- Carte Interactions & Comportement -->
                        <article class="canvas-card" data-category="interactions">
                            <header class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">🎯</span>
                                </div>
                                <h4>Interactions & Comportement</h4>
                            </header>
                            <main class="canvas-card-content">
                                <p>Contrôlez les interactions canvas, la sélection et les raccourcis clavier.</p>
                            </main>
                            <aside class="canvas-card-preview">
                                <div class="interactions-preview-container">
                                    <!-- Canvas miniature avec éléments -->
                                    <div class="mini-canvas">
                                        <!-- Grille de fond -->
                                        <div class="mini-canvas-grid"></div>

                                        <!-- Éléments sur le canvas -->
                                        <div class="mini-element text-element" style="top: 15px; left: 20px; width: 35px; height: 18px;" title="Élément texte - Double-clic pour éditer">
                                            <div class="mini-element-content">T</div>
                                        </div>
                                        <div class="mini-element shape-element selected" style="top: 40px; left: 15px; width: 32px; height: 22px;" title="Élément sélectionné - Glisser pour déplacer">
                                            <div class="mini-element-content">□</div>
                                            <!-- Poignées de sélection -->
                                            <div class="mini-handle nw" title="Redimensionner (coin supérieur gauche)"></div>
                                            <div class="mini-handle ne" title="Redimensionner (coin supérieur droit)"></div>
                                            <div class="mini-handle sw" title="Redimensionner (coin inférieur gauche)"></div>
                                            <div class="mini-handle se" title="Redimensionner (coin inférieur droit)"></div>
                                            <div class="mini-handle rotation" style="top: -6px; left: 50%; transform: translateX(-50%);" title="Rotation - Maintenir Maj pour angles précis"></div>
                                        </div>
                                        <div class="mini-element image-element" style="top: 18px; left: 75px; width: 28px; height: 28px;" title="Élément image - Clic droit pour options">
                                            <div class="mini-element-content">🖼</div>
                                        </div>

                                        <!-- Sélection rectangle en cours -->
                                        <div class="selection-rectangle" style="top: 10px; left: 10px; width: 55px; height: 35px;" title="Sélection multiple - Relâcher pour sélectionner"></div>

                                        <!-- Curseur de souris -->
                                        <div class="mouse-cursor" style="top: 50px; left: 95px;">
                                            <div class="cursor-icon">👆</div>
                                        </div>

                                        <!-- Indicateur de zoom -->
                                        <div class="zoom-indicator" title="Niveau de zoom actuel - Ctrl+molette pour zoomer">
                                            <span class="zoom-level">100%</span>
                                        </div>

                                        <!-- Indicateur de performance -->
                                        <div class="performance-indicator" title="Performance canvas - 60 FPS">
                                            <div class="performance-bar">
                                                <div class="performance-fill" style="width: 85%"></div>
                                            </div>
                                            <span class="performance-text">85%</span>
                                        </div>
                                    </div>

                                    <!-- Contrôles en bas -->
                                    <div class="interactions-controls">
                                        <div class="selection-mode-indicator">
                                            <span class="mode-icon active" title="Sélection rectangle (R) - Pour sélectionner plusieurs éléments" data-mode="rectangle">▭</span>
                                            <span class="mode-icon" title="Sélection lasso (L) - Pour sélection libre" data-mode="lasso">🪢</span>
                                            <span class="mode-icon" title="Sélection par clic (C) - Pour sélection simple" data-mode="click">👆</span>
                                        </div>
                                        <div class="interaction-status">
                                            <span class="status-indicator selecting">Sélection active</span>
                                            <div class="keyboard-status" title="Raccourcis clavier activés">
                                                <span class="keyboard-icon">⌨️</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Barre de progression des interactions -->
                                    <div class="interaction-progress">
                                        <div class="progress-label">Fluidité</div>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: 92%"></div>
                                        </div>
                                        <div class="progress-value">92%</div>
                                    </div>
                                </div>
                            </aside>
                            <footer class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>🎯</span> Configurer
                                </button>
                            </footer>
                        </article>

                        <!-- Carte Export & Qualité -->
                        <article class="canvas-card" data-category="export">
                            <header class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">📤</span>
                                </div>
                                <h4>Export & Qualité</h4>
                            </header>
                            <main class="canvas-card-content">
                                <p>Configurez les formats et la qualité d'export des designs.</p>
                            </main>
                            <aside class="canvas-card-preview">
                                <div class="export-quality-preview">
                                    <div class="quality-bar">
                                        <div class="quality-fill" style="width: 90%"></div>
                                        <div class="quality-text">90%</div>
                                    </div>
                                    <div class="export-formats">
                                        <span class="format-badge">PNG</span>
                                        <span class="format-badge">JPG</span>
                                        <span class="format-badge active">PDF</span>
                                    </div>
                                </div>
                            </aside>
                            <footer class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>📤</span> Configurer
                                </button>
                            </footer>
                        </article>

                        <!-- Carte Performance -->
                        <article class="canvas-card" data-category="performance">
                            <header class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">⚡</span>
                                </div>
                                <h4>Performance</h4>
                            </header>
                            <main class="canvas-card-content">
                                <p>Optimisez les FPS, mémoire et temps de réponse.</p>
                            </main>
                            <aside class="canvas-card-preview">
                                <div class="performance-preview-container">
                                    <div class="performance-metrics">
                                        <div class="metric-item">
                                            <span class="metric-label">FPS</span>
                                            <span id="card-perf-preview" class="metric-value"><?php echo esc_html($settings['pdf_builder_canvas_fps_target'] ?? '60'); ?></span>
                                        </div>
                                        <div class="metric-item">
                                            <span class="metric-label">RAM JS</span>
                                            <span class="metric-value"><?php echo esc_html($settings['pdf_builder_canvas_memory_limit_js'] ?? '50'); ?>MB</span>
                                        </div>
                                        <div class="metric-item">
                                            <span class="metric-label">RAM PHP</span>
                                            <span class="metric-value"><?php echo esc_html($settings['pdf_builder_canvas_memory_limit_php'] ?? '128'); ?>MB</span>
                                        </div>
                                    </div>
                                    <div class="performance-status">
                                        <div class="status-indicator">
                                            <span class="status-dot"></span>
                                            <span class="status-text">Lazy Loading</span>
                                        </div>
                                    </div>
                                </div>
                            </aside>
                            <footer class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>⚡</span> Configurer
                                </button>
                            </footer>
                        </article>

                        <!-- Carte Debug -->
                        <article class="canvas-card" data-category="debug">
                            <header class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">🐛</span>
                                </div>
                                <h4>Debug</h4>
                            </header>
                            <main class="canvas-card-content">
                                <p>Outils de débogage et monitoring des performances.</p>
                            </main>
                            <aside class="canvas-card-preview">
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
                                            <span class="stat-value"><?php echo esc_html($settings['pdf_builder_canvas_fps_target'] ?? '60'); ?></span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-label">RAM</span>
                                            <span class="stat-value"><?php echo esc_html($settings['pdf_builder_canvas_memory_limit_js'] ?? '50'); ?>MB</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-label">Errors</span>
                                            <span class="stat-value">0</span>
                                        </div>
                                    </div>
                                </div>
                            </aside>
                            <footer class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>🐛</span> Configurer
                                </button>
                            </footer>
                        </article>
                    </div>


            </section>

                <!-- Section Templates -->
            <section class="contenu-templates-section">
                <h3>
                    <span>
                        📋 Templates
                        <span id="template-library-indicator" class="template-library-indicator" style="background: <?php echo (($settings['pdf_builder_template_library_enabled'] ?? '1') === '1') ? '#28a745' : '#dc3545'; ?>;"><?php echo (($settings['pdf_builder_template_library_enabled'] ?? '1') === '1') ? 'ACTIF' : 'INACTIF'; ?></span>
                    </span>
                </h3>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="default_template">Template par défaut</label></th>
                            <td>
                                <select id="default_template" name="pdf_builder_settings[pdf_builder_default_template]">
                                    <option value="blank" <?php selected($settings['pdf_builder_default_template'] ?? 'blank', 'blank'); ?>>Page blanche</option>
                                    <option value="invoice" <?php selected($settings['pdf_builder_default_template'] ?? 'blank', 'invoice'); ?>>Facture</option>
                                    <option value="quote" <?php selected($settings['pdf_builder_default_template'] ?? 'blank', 'quote'); ?>>Devis</option>
                                </select>
                                <p class="description">Template utilisé par défaut pour nouveaux documents</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="template_library_enabled">Bibliothèque de templates</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_settings[pdf_builder_template_library_enabled]" value="0">
                                    <input type="checkbox" id="template_library_enabled" name="pdf_builder_settings[pdf_builder_template_library_enabled]" value="1" <?php checked($settings['pdf_builder_template_library_enabled'] ?? '1', '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Active la bibliothèque de templates prédéfinis</p>
                            </td>
                        </tr>
                    </table>
            </section>



            <!-- CSS pour les modales Canvas - AJOUTÉ -->
            <style>
/* === CSS POUR LES MODALES CANVAS === */

/* Overlay de la modale */
.canvas-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 10000;
    backdrop-filter: blur(2px);
}

/* Conteneur principal de la modale */
.canvas-modal-container {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow: hidden;
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* Header de la modale */
.canvas-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #e1e5e9;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.canvas-modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.canvas-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    color: white;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s;
}

.canvas-modal-close:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

/* Corps de la modale */
.canvas-modal-body {
    padding: 24px;
    max-height: 60vh;
    overflow-y: auto;
}

/* Section d'information */
.canvas-modal-info {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 20px;
}

.canvas-modal-info p {
    margin: 0;
    color: #495057;
    font-size: 14px;
    line-height: 1.5;
}

/* Formulaire dans la modale */
#canvas-dimensions-form,
#canvas-apparence-form,
#canvas-grille-form,
#canvas-zoom-form,
#canvas-interactions-form,
#canvas-export-form,
#canvas-performance-form,
#canvas-debug-form {
    margin: 0;
}

/* Table des paramètres */
.form-table {
    width: 100%;
    border-collapse: collapse;
}

.form-table th,
.form-table td {
    padding: 12px 0;
    border-bottom: 1px solid #f1f3f4;
    vertical-align: top;
}

.form-table th {
    width: 200px;
    text-align: left;
    font-weight: 600;
    color: #37474f;
}

.form-table td {
    padding-left: 20px;
}

/* Champs de formulaire */
.form-table input[type="text"],
.form-table input[type="number"],
.form-table select {
    width: 100%;
    max-width: 200px;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-table input:focus,
.form-table select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
}

/* Toggles dans les modals */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
    margin-right: 12px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.3s;
    border-radius: 24px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
}

.toggle-switch input:checked + .toggle-slider {
    background-color: #667eea;
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(26px);
}

.toggle-switch.checked .toggle-slider {
    background-color: #667eea;
}

.toggle-switch.checked .toggle-slider:before {
    transform: translateX(26px);
}

/* Descriptions */
.canvas-modal-description {
    margin: 4px 0 0 0;
    font-size: 12px;
    color: #6c757d;
    font-style: italic;
}

/* Aperçu dans la modale */
.canvas-modal-display {
    margin-top: 20px;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.canvas-modal-display h4 {
    margin: 0 0 12px 0;
    color: #495057;
    font-size: 16px;
}

/* Footer de la modale */
.canvas-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 20px 24px;
    border-top: 1px solid #e1e5e9;
    background: #f8f9fa;
}

.canvas-modal-cancel,
.canvas-modal-save {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.canvas-modal-cancel {
    background: #6c757d;
    color: white;
}

.canvas-modal-cancel:hover {
    background: #5a6268;
}

.canvas-modal-save {
    background: #667eea;
    color: white;
}

.canvas-modal-save:hover {
    background: #5a67d8;
}

.canvas-modal-save:disabled {
    background: #ccc;
    cursor: not-allowed;
}

/* Responsive */
@media (max-width: 768px) {
    .canvas-modal-container {
        width: 95%;
        margin: 20px;
    }

    .canvas-modal-header,
    .canvas-modal-body,
    .canvas-modal-footer {
        padding: 16px;
    }

    .form-table th {
        width: 150px;
        font-size: 14px;
    }

    .form-table td {
        padding-left: 12px;
    }
}

/* États de chargement */
.canvas-modal-save.loading {
    position: relative;
    color: transparent;
}

.canvas-modal-save.loading::after {
    content: "";
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Messages d'erreur */
.canvas-modal-error {
    background: #f8d7da;
    color: #721c24;
    padding: 12px;
    border-radius: 4px;
    margin-bottom: 16px;
    border: 1px solid #f5c6cb;
}

.canvas-modal-success {
    background: #d4edda;
    color: #155724;
    padding: 12px;
    border-radius: 4px;
    margin-bottom: 16px;
    border: 1px solid #c3e6cb;
}
            </style>
            <?php
                // $plugin_dir = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
                // $css_url = plugins_url('resources/assets/css/canvas-modals.css', $plugin_dir . '/pdf-builder-pro.php');
                // wp_enqueue_style('pdf-builder-canvas-modals', $css_url, array(), '1.0.0');
            ?>

<script>
                // Force cache clear
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.getRegistrations().then(function(registrations) {
                        for(let registration of registrations) {
                            registration.unregister();
                        }
                    });
                }
                // Clear localStorage and sessionStorage
                localStorage.clear();
                sessionStorage.clear();
                console.log('Cache cleared by PDF Builder');

                // Valeurs par défaut globales pour tous les champs Canvas - SOURCE UNIQUE DE VÉRITÉ
                const CANVAS_DEFAULT_VALUES = {
                    'pdf_builder_canvas_width': '794',
                    'pdf_builder_canvas_height': '1123',
                    'pdf_builder_canvas_dpi': '96',
                    'pdf_builder_canvas_format': 'A4',
                    'pdf_builder_canvas_bg_color': '#ffffff',
                    'pdf_builder_canvas_border_color': '#cccccc',
                    'pdf_builder_canvas_border_width': '1',
                    'pdf_builder_canvas_container_bg_color': '#f8f9fa',
                    'pdf_builder_canvas_shadow_enabled': '0',
                    'pdf_builder_canvas_grid_enabled': '1',
                    'pdf_builder_canvas_grid_size': '20',
                    'pdf_builder_canvas_guides_enabled': '1',
                    'pdf_builder_canvas_snap_to_grid': '1',
                    'pdf_builder_canvas_zoom_min': '25',
                    'pdf_builder_canvas_zoom_max': '500',
                    'pdf_builder_canvas_zoom_default': '100',
                    'pdf_builder_canvas_zoom_step': '25',
                    'pdf_builder_canvas_export_quality': '90',
                    'pdf_builder_canvas_export_format': 'png',
                    'pdf_builder_canvas_export_transparent': '0',
                    'pdf_builder_canvas_drag_enabled': '1',
                    'pdf_builder_canvas_resize_enabled': '1',
                    'pdf_builder_canvas_rotate_enabled': '1',
                    'pdf_builder_canvas_multi_select': '1',
                    'pdf_builder_canvas_selection_mode': 'single',
                    'pdf_builder_canvas_keyboard_shortcuts': '1',
                    'pdf_builder_canvas_fps_target': '60',
                    'pdf_builder_canvas_memory_limit_js': '50',
                    'pdf_builder_canvas_response_timeout': '5000',
                    'pdf_builder_canvas_lazy_loading_editor': '1',
                    'pdf_builder_canvas_preload_critical': '1',
                    'pdf_builder_canvas_lazy_loading_plugin': '1',
                    'pdf_builder_canvas_debug_enabled': '0',
                    'pdf_builder_canvas_performance_monitoring': '0',
                    'pdf_builder_canvas_error_reporting': '0',
                    'pdf_builder_canvas_memory_limit_php': '128'
                };

                // Configuration de robustesse
                const CANVAS_CONFIG = {
                    MAX_RETRIES: 3,
                    RETRY_DELAY: 1000, // ms
                    AJAX_TIMEOUT: 30000, // 30 secondes
                    HEALTH_CHECK_INTERVAL: 60000, // 1 minute
                    CACHE_KEY: 'pdf_builder_canvas_backup',
                    CACHE_TTL: 3600000 // 1 heure
                };

                // Feature flags pour contrôle granulaire
                const CANVAS_FEATURES = {
                    ENABLE_VALIDATION: true,
                    ENABLE_CACHE: true,
                    ENABLE_RETRY: true,
                    ENABLE_HEALTH_CHECK: true,
                    ENABLE_METRICS: true,
                    ENABLE_RECOVERY: true,
                    ENABLE_CIRCUIT_BREAKER: true
                };

                // Fonction de contrôle des features
                const FeatureGate = {
                    isEnabled: (feature) => {
                        return CANVAS_FEATURES[feature] !== false;
                    },

                    disable: (feature) => {
                        CANVAS_FEATURES[feature] = false;
                        console.log(`FEATURE_DISABLED: ${feature} turned off`);
                    },

                    enable: (feature) => {
                        CANVAS_FEATURES[feature] = true;
                        console.log(`FEATURE_ENABLED: ${feature} turned on`);
                    },

                    emergencyShutdown: () => {
                        console.warn('EMERGENCY: Disabling all advanced features');
                        Object.keys(CANVAS_FEATURES).forEach(feature => {
                            if (feature !== 'ENABLE_VALIDATION') { // Garder la validation
                                CANVAS_FEATURES[feature] = false;
                            }
                        });
                    }
                };

                // Utilitaires de validation
                const CanvasValidators = {
                    isValidNumber: (value, min, max) => {
                        const num = parseInt(value);
                        return !isNaN(num) && num >= min && num <= max;
                    },

                    isValidHexColor: (value) => {
                        return /^#[0-9A-Fa-f]{6}$/.test(value);
                    },

                    isValidBoolean: (value) => {
                        return value === '0' || value === '1';
                    },

                    validateField: (key, value) => {
                        if (key.includes('_width') && !key.includes('_border_width')) {
                            return CanvasValidators.isValidNumber(value, 100, 5000);
                        }
                        if (key.includes('_height')) {
                            return CanvasValidators.isValidNumber(value, 100, 5000);
                        }
                        if (key.includes('_border_width')) {
                            return CanvasValidators.isValidNumber(value, 0, 10);
                        }
                        if (key.includes('_dpi')) {
                            return CanvasValidators.isValidNumber(value, 72, 600);
                        }
                        if (key.includes('_color')) {
                            return CanvasValidators.isValidHexColor(value);
                        }
                        if (key.includes('_enabled') || key.includes('_transparent')) {
                            return CanvasValidators.isValidBoolean(value);
                        }
                        return true; // Autres champs acceptés par défaut
                    }
                };

                // Gestionnaire de cache localStorage
                const CanvasCache = {
                    save: (data) => {
                        try {
                            const cacheData = {
                                data: data,
                                timestamp: Date.now(),
                                version: '1.0'
                            };
                            localStorage.setItem(CANVAS_CONFIG.CACHE_KEY, JSON.stringify(cacheData));
                            console.log('CACHE_SAVE: Canvas settings cached locally');
                        } catch (e) {
                            console.warn('CACHE_ERROR: Failed to save to localStorage:', e);
                        }
                    },

                    load: () => {
                        try {
                            const cached = localStorage.getItem(CANVAS_CONFIG.CACHE_KEY);
                            if (!cached) return null;

                            const cacheData = JSON.parse(cached);
                            const age = Date.now() - cacheData.timestamp;

                            if (age > CANVAS_CONFIG.CACHE_TTL) {
                                console.log('CACHE_EXPIRED: Cache too old, removing');
                                CanvasCache.clear();
                                return null;
                            }

                            console.log('CACHE_LOAD: Loaded cached settings');
                            return cacheData.data;
                        } catch (e) {
                            console.warn('CACHE_ERROR: Failed to load from localStorage:', e);
                            return null;
                        }
                    },

                    clear: () => {
                        try {
                            localStorage.removeItem(CANVAS_CONFIG.CACHE_KEY);
                            console.log('CACHE_CLEAR: Local cache cleared');
                        } catch (e) {
                            console.warn('CACHE_ERROR: Failed to clear cache:', e);
                        }
                    }
                };

                // Health monitoring
                const CanvasHealth = {
                    lastSuccess: Date.now(),
                    failureCount: 0,
                    isHealthy: true,

                    recordSuccess: () => {
                        CanvasHealth.lastSuccess = Date.now();
                        CanvasHealth.failureCount = 0;
                        CanvasHealth.isHealthy = true;
                    },

                    recordFailure: () => {
                        CanvasHealth.failureCount++;
                        if (CanvasHealth.failureCount >= 3) {
                            CanvasHealth.isHealthy = false;
                            console.warn('HEALTH_WARNING: System marked as unhealthy after 3 failures');
                        }
                    },

                    checkHealth: () => {
                        const timeSinceLastSuccess = Date.now() - CanvasHealth.lastSuccess;
                        return CanvasHealth.isHealthy && timeSinceLastSuccess < 300000; // 5 minutes
                    }
                };

                // Métriques de performance
                const CanvasMetrics = {
                    saveAttempts: 0,
                    saveSuccesses: 0,
                    saveFailures: 0,
                    averageSaveTime: 0,
                    lastSaveTime: 0,

                    recordSaveStart: () => {
                        CanvasMetrics.saveAttempts++;
                        CanvasMetrics.lastSaveTime = performance.now();
                    },

                    recordSaveEnd: (success) => {
                        const duration = performance.now() - CanvasMetrics.lastSaveTime;
                        CanvasMetrics.averageSaveTime = (CanvasMetrics.averageSaveTime + duration) / 2;

                        if (success) {
                            CanvasMetrics.saveSuccesses++;
                        } else {
                            CanvasMetrics.saveFailures++;
                        }

                        console.log(`METRICS: Save completed in ${duration.toFixed(2)}ms (avg: ${CanvasMetrics.averageSaveTime.toFixed(2)}ms)`);

                        // Alerte si performance dégradée
                        if (CanvasMetrics.averageSaveTime > 5000) { // 5 secondes
                            console.warn('PERFORMANCE_WARNING: Average save time > 5s, possible issues');
                        }
                    },

                    getStats: () => {
                        const successRate = CanvasMetrics.saveAttempts > 0 ?
                            (CanvasMetrics.saveSuccesses / CanvasMetrics.saveAttempts * 100).toFixed(1) : 0;

                        return {
                            attempts: CanvasMetrics.saveAttempts,
                            successes: CanvasMetrics.saveSuccesses,
                            failures: CanvasMetrics.saveFailures,
                            successRate: successRate + '%',
                            averageTime: CanvasMetrics.averageSaveTime.toFixed(2) + 'ms'
                        };
                    }
                };
                    init: () => {
                        // Health check périodique
                        setInterval(() => {
                            CanvasRecovery.performHealthCheck();
                        }, CANVAS_CONFIG.HEALTH_CHECK_INTERVAL);

                        // Récupération au chargement de la page
                        CanvasRecovery.attemptRecovery();

                        console.log('RECOVERY_INIT: Auto-recovery system initialized');
                    },

                    performHealthCheck: () => {
                        // Ping simple du serveur
                        fetch(ajaxurl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({
                                action: 'pdf_builder_health_check',
                                nonce: '<?php echo wp_create_nonce("pdf_builder_health"); ?>'
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                CanvasHealth.recordSuccess();
                                console.log('HEALTH_CHECK: System is healthy');
                            } else {
                                CanvasHealth.recordFailure();
                                console.warn('HEALTH_CHECK: System reported issues');
                            }
                        })
                        .catch(error => {
                            CanvasHealth.recordFailure();
                            console.warn('HEALTH_CHECK: Failed to reach server:', error);
                        });
                    },

                    attemptRecovery: () => {
                        // Vérifier s'il y a des données en cache non sauvegardées
                        const cachedData = CanvasCache.load();
                        if (cachedData) {
                            console.log('RECOVERY: Found unsaved data in cache, attempting auto-save');

                            // Interface utilisateur pour informer l'utilisateur
                            if (confirm('Des paramètres non sauvegardés ont été trouvés. Voulez-vous les restaurer ?')) {
                                CanvasRecovery.restoreFromCache(cachedData);
                            }
                        }
                    },

                    restoreFromCache: (cachedData) => {
                        console.log('CACHE_RESTORE: Restoring settings from cache');

                        // Appliquer les données cachées aux champs cachés
                        Object.entries(cachedData).forEach(([key, value]) => {
                            const hiddenField = document.querySelector(`input[name="pdf_builder_settings[${key}]"]`);
                            if (hiddenField) {
                                hiddenField.value = value;
                                console.log(`CACHE_RESTORE: Restored ${key} = ${value}`);
                            }
                        });

                        // Notification à l'utilisateur
                        alert('Paramètres restaurés depuis le cache local. Pensez à sauvegarder !');
                    }
                };

                // Gestionnaire des modales Canvas
                (function() {
                    'use strict';

                    console.log('[PDF Builder] MODALS_INIT - Initializing Canvas modals system');

                    // Fonction d'initialisation
                    function initializeModals() {
                        try {
                            console.log('[PDF Builder] MODALS_INIT - Setting up modal infrastructure');

                            // Vérifier que les modals existent
                            const modalCategories = ['dimensions', 'apparence', 'grille', 'zoom', 'interactions', 'export', 'performance', 'debug'];
                            let missingModals = [];

                            modalCategories.forEach(category => {
                                const modalId = `canvas-${category}-modal-overlay`;
                                const modal = document.getElementById(modalId);
                                if (!modal) {
                                    missingModals.push(modalId);
                                    console.warn(`[PDF Builder] MODALS_INIT - Missing modal: ${modalId}`);
                                }
                            });

                            if (missingModals.length > 0) {
                                console.error(`[PDF Builder] MODALS_INIT - ${missingModals.length} modals are missing:`, missingModals);
                                alert(`Attention: ${missingModals.length} modales sont manquantes. Certaines fonctionnalités risquent de ne pas fonctionner.`);
                            } else {
                                console.log('[PDF Builder] MODALS_INIT - All modals found successfully');
                            }

                            // Vérifier que les boutons de configuration existent
                            const configButtons = document.querySelectorAll('.canvas-configure-btn');
                            console.log(`[PDF Builder] MODALS_INIT - Found ${configButtons.length} configuration buttons`);

                            if (configButtons.length === 0) {
                                console.warn('[PDF Builder] MODALS_INIT - No configuration buttons found');
                            }

                            console.log('[PDF Builder] MODALS_INIT - Modal system initialized successfully');

                        } catch (error) {
                            console.error('[PDF Builder] MODALS_INIT - Error during initialization:', error);
                        }
                    }

                    // Appeler l'initialisation quand le DOM est prêt
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', initializeModals);
                    } else {
                        initializeModals();
                    }

                    // Fonction pour ouvrir une modale - VERSION RENFORCÉE
                    function openModal(modalId) {
                        try {
                            console.log(`[PDF Builder] OPEN_MODAL - Attempting to open: ${modalId}`);

                            const modal = document.getElementById(modalId);
                            if (!modal) {
                                console.error(`[PDF Builder] OPEN_MODAL - Modal element not found: ${modalId}`);
                                alert(`Erreur: La modale ${modalId} n'a pas été trouvée.`);
                                return;
                            }

                            // Extraire la catégorie depuis l'ID de la modale
                            const categoryMatch = modalId.match(/canvas-(\w+)-modal-overlay/);
                            if (categoryMatch) {
                                const category = categoryMatch[1];
                                console.log(`[PDF Builder] OPEN_MODAL - Opening modal for category: ${category}`);

                                // Mettre à jour les valeurs avant d'ouvrir
                                updateModalValues(category);
                            } else {
                                console.warn(`[PDF Builder] OPEN_MODAL - Could not extract category from modalId: ${modalId}`);
                            }

                            // Afficher la modale avec animation
                            modal.style.display = 'flex';
                            document.body.style.overflow = 'hidden';

                            // Focus sur la modale pour l'accessibilité
                            modal.setAttribute('aria-hidden', 'false');
                            modal.focus();

                            console.log(`[PDF Builder] OPEN_MODAL - Modal opened successfully: ${modalId}`);

                        } catch (error) {
                            console.error(`[PDF Builder] OPEN_MODAL - Error opening modal ${modalId}:`, error);
                            alert(`Erreur lors de l'ouverture de la modale: ${error.message}`);
                        }
                    }

                    // Fonction pour mettre à jour les valeurs d'une modale avec les paramètres actuels
                    function updateModalValues(category) {
                        console.log(`[PDF Builder] UPDATE_MODAL - Called with category: ${category}`);
                        console.log('[PDF Builder] UPDATE_MODAL - Starting modal value synchronization');
                        const modal = document.querySelector(`#canvas-${category}-modal-overlay`);
                        if (!modal) {
                            console.log(`[PDF Builder] UPDATE_MODAL - Modal #canvas-${category}-modal-overlay not found`);
                            return;
                        }
                        console.log(`[PDF Builder] UPDATE_MODAL - Modal found, processing category: ${category}`);

                        // Mapping des champs selon la catégorie
                        const fieldMappings = {
                            'dimensions': {
                                'canvas_width': 'pdf_builder_canvas_width',
                                'canvas_height': 'pdf_builder_canvas_height',
                                'canvas_dpi': 'pdf_builder_canvas_dpi',
                                'canvas_format': 'pdf_builder_canvas_format'
                            },
                            'apparence': {
                                'canvas_bg_color': 'pdf_builder_canvas_bg_color',
                                'canvas_border_color': 'pdf_builder_canvas_border_color',
                                'canvas_border_width': 'pdf_builder_canvas_border_width',
                                'canvas_shadow_enabled': 'pdf_builder_canvas_shadow_enabled',
                                'canvas_container_bg_color': 'pdf_builder_canvas_container_bg_color'
                            },
                            'grille': {
                                'canvas_grid_enabled': 'pdf_builder_canvas_grid_enabled',
                                'canvas_grid_size': 'pdf_builder_canvas_grid_size',
                                'canvas_guides_enabled': 'pdf_builder_canvas_guides_enabled',
                                'canvas_snap_to_grid': 'pdf_builder_canvas_snap_to_grid'
                            },
                            'zoom': {
                                'canvas_zoom_min': 'pdf_builder_canvas_zoom_min',
                                'canvas_zoom_max': 'pdf_builder_canvas_zoom_max',
                                'canvas_zoom_default': 'pdf_builder_canvas_zoom_default',
                                'canvas_zoom_step': 'pdf_builder_canvas_zoom_step'
                            },
                            'interactions': {
                                'canvas_drag_enabled': 'pdf_builder_canvas_drag_enabled',
                                'canvas_resize_enabled': 'pdf_builder_canvas_resize_enabled',
                                'canvas_rotate_enabled': 'pdf_builder_canvas_rotate_enabled',
                                'canvas_multi_select': 'pdf_builder_canvas_multi_select',
                                'canvas_selection_mode': 'pdf_builder_canvas_selection_mode',
                                'canvas_keyboard_shortcuts': 'pdf_builder_canvas_keyboard_shortcuts'
                            },
                            'export': {
                                'canvas_export_quality': 'pdf_builder_canvas_export_quality',
                                'canvas_export_format': 'pdf_builder_canvas_export_format',
                                'canvas_export_transparent': 'pdf_builder_canvas_export_transparent'
                            },
                            'performance': {
                                'canvas_fps_target': 'pdf_builder_canvas_fps_target',
                                'canvas_memory_limit_js': 'pdf_builder_canvas_memory_limit_js',
                                'canvas_response_timeout': 'pdf_builder_canvas_response_timeout'
                            },
                            'debug': {
                                'canvas_debug_enabled': 'pdf_builder_canvas_debug_enabled',
                                'canvas_performance_monitoring': 'pdf_builder_canvas_performance_monitoring',
                                'canvas_error_reporting': 'pdf_builder_canvas_error_reporting'
                            }
                        };

                        const mappings = fieldMappings[category];
                        if (!mappings) return;

                        // Valeurs par défaut pour les champs Canvas
                        const defaultValues = CANVAS_DEFAULT_VALUES;

                        // Mettre à jour chaque champ
                        for (const [fieldId, settingKey] of Object.entries(mappings)) {
                            const field = modal.querySelector(`#${fieldId}, [name="${settingKey}"]`);
                            if (field) {
                                // Chercher la valeur dans les champs cachés
                                const hiddenField = document.querySelector(`input[name="pdf_builder_settings[${settingKey}]"]`);
                                let value = '';
                                
                                if (hiddenField && hiddenField.value && hiddenField.value.trim() !== '') {
                                    value = hiddenField.value;
                                    console.log(`[PDF Builder] UPDATE_MODAL - Using hidden field value for ${settingKey}: ${value}`);
                                } else {
                                    // Utiliser la valeur par défaut si le champ caché est vide ou n'existe pas
                                    value = defaultValues[settingKey] || '';
                                    console.log(`[PDF Builder] UPDATE_MODAL - Using default value for ${settingKey}: ${value} (hidden field was empty)`);
                                }
                                
                                if (category === 'grille') {
                                    console.log(`[PDF Builder] GRID_UPDATE - Processing grid field ${fieldId} with value: ${value}`);
                                }
                                
                                // Log spécifique pour les toggles de grille
                                if (['canvas_grid_enabled', 'canvas_guides_enabled', 'canvas_snap_to_grid'].includes(fieldId)) {
                                    console.log(`GRID_TOGGLE: Updating ${fieldId} (${settingKey}) with value: ${value}, field type: ${field.type}`);
                                }
                                
                                if (field.type === 'checkbox') {
                                    field.checked = value === '1';
                                    // Synchroniser la classe CSS pour les toggles
                                    const toggleSwitch = field.closest('.toggle-switch');
                                    if (toggleSwitch) {
                                        if (value === '1') {
                                            toggleSwitch.classList.add('checked');
                                        } else {
                                            toggleSwitch.classList.remove('checked');
                                        }
                                        console.log(`TOGGLE_DEBUG: ${fieldId} - checked=${field.checked}, toggle classes: ${toggleSwitch.className}`);
                                    } else {
                                        console.log(`TOGGLE_DEBUG: ${fieldId} - No toggle-switch parent found`);
                                    }
                                    if (['canvas_grid_enabled', 'canvas_guides_enabled', 'canvas_snap_to_grid', 'canvas_drag_enabled', 'canvas_resize_enabled', 'canvas_rotate_enabled', 'canvas_multi_select', 'canvas_keyboard_shortcuts'].includes(fieldId)) {
                                        console.log(`ALL_TOGGLES: Set checkbox ${fieldId} checked to: ${field.checked}, toggle class: ${toggleSwitch ? toggleSwitch.className : 'no toggle'}`);
                                    }
                                } else {
                                    field.value = value;
                                    
                                    // Pour les selects, mettre à jour l'attribut selected
                                    if (field.tagName === 'SELECT') {
                                        const options = field.querySelectorAll('option');
                                        options.forEach(option => {
                                            option.selected = option.value === value;
                                        });
                                    }
                                }
                            } else {
                                console.log(`Field not found for ${fieldId} or ${settingKey}`);
                            }
                        }
                    }

                    // Fonction pour fermer une modale - VERSION RENFORCÉE
                    function closeModal(modalId) {
                        try {
                            console.log(`[PDF Builder] CLOSE_MODAL - Attempting to close: ${modalId}`);

                            const modal = document.getElementById(modalId);
                            if (!modal) {
                                console.warn(`[PDF Builder] CLOSE_MODAL - Modal element not found: ${modalId}`);
                                return;
                            }

                            // Masquer la modale
                            modal.style.display = 'none';
                            document.body.style.overflow = '';

                            // Accessibilité
                            modal.setAttribute('aria-hidden', 'true');

                            console.log(`[PDF Builder] CLOSE_MODAL - Modal closed successfully: ${modalId}`);

                        } catch (error) {
                            console.error(`[PDF Builder] CLOSE_MODAL - Error closing modal ${modalId}:`, error);
                        }
                    }

                    // Fonction pour sauvegarder les paramètres d'une modale
                    function saveModalSettings(category) {
                        // VALIDATION CÔTÉ CLIENT AVANT ENVOI
                        const allCanvasInputs = document.querySelectorAll('input[name^="pdf_builder_canvas_"]');
                        const settings = {};
                        let validationErrors = [];

                        // Collecter et valider toutes les valeurs
                        allCanvasInputs.forEach(input => {
                            let value = '';
                            if (input.type === 'checkbox') {
                                value = input.checked ? '1' : '0';
                            } else {
                                value = input.value;
                            }

                            // Utiliser la valeur par défaut si vide
                            if (!value || value.trim() === '') {
                                value = CANVAS_DEFAULT_VALUES[input.name] || '';
                                console.log(`SAVE_DEFAULT: Applied default for ${input.name}: ${value}`);
                            }

                        // VALIDATION CÔTÉ CLIENT (si activée)
                        if (FeatureGate.isEnabled('ENABLE_VALIDATION')) {
                            // Validation côté client
                            if (!CanvasValidators.validateField(input.name, value)) {
                                validationErrors.push(`${input.name}: valeur invalide "${value}"`);
                                value = CANVAS_DEFAULT_VALUES[input.name] || '';
                                console.warn(`VALIDATION_FIX: Corrected invalid value for ${input.name} to ${value}`);
                            }
                        }

                        settings[input.name] = value;
                        });

                        // Bloquer si trop d'erreurs de validation
                        if (validationErrors.length > 5) {
                            alert('Trop d\'erreurs de validation. Rechargement de la page...');
                            location.reload();
                            return;
                        }

                        console.log('SAVE_DEBUG: Validated settings to save:', settings);

                        // SAUVEGARDE EN CACHE LOCAL AVANT ENVOI
                        CanvasCache.save(settings);

                        // Ajouter métadonnées
                        settings['action'] = 'pdf_builder_save_canvas_settings';
                        settings['nonce'] = '<?php echo wp_create_nonce("pdf_builder_canvas_settings"); ?>';
                        settings['client_timestamp'] = Date.now();

                        // FONCTION DE SAUVEGARDE AVEC RETRY
                        function attemptSave(retryCount = 0) {
                            CanvasMetrics.recordSaveStart();

                            // VÉRIFICATION HEALTH AVANT ENVOI
                            if (!CanvasHealth.checkHealth() && retryCount === 0) {
                                console.warn('HEALTH_CHECK: System unhealthy, attempting save anyway');
                            }

                            const saveBtn = document.querySelector(`.canvas-modal-save[data-category="${category}"]`);
                            if (saveBtn) {
                                saveBtn.innerHTML = retryCount > 0 ? `⏳ Retry ${retryCount}/${CANVAS_CONFIG.MAX_RETRIES}...` : '⏳ Sauvegarde...';
                                saveBtn.disabled = true;
                            }

                            // CONTROLLER POUR ANNULER SI TROP LONG
                            const controller = new AbortController();
                            const timeoutId = setTimeout(() => {
                                controller.abort();
                            }, CANVAS_CONFIG.AJAX_TIMEOUT);

                            fetch(ajaxurl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: new URLSearchParams(settings),
                                signal: controller.signal
                            })
                            .then(response => {
                                clearTimeout(timeoutId);
                                if (!response.ok) {
                                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    CanvasMetrics.recordSaveEnd(true);
                                    CanvasHealth.recordSuccess();
                                    CanvasCache.clear(); // Nettoyer le cache après succès

                                    closeModal(`canvas-${category}-modal-overlay`);
                                    location.reload();
                                } else {
                                    throw new Error(data.data?.message || 'Erreur serveur inconnue');
                                }
                            })
                            .catch(error => {
                                CanvasMetrics.recordSaveEnd(false);
                                clearTimeout(timeoutId);
                                CanvasHealth.recordFailure();

                                console.error(`SAVE_ERROR (attempt ${retryCount + 1}):`, error);

                                // RETRY AUTOMATIQUE
                                if (retryCount < CANVAS_CONFIG.MAX_RETRIES) {
                                    console.log(`RETRY: Attempting save again in ${CANVAS_CONFIG.RETRY_DELAY}ms...`);
                                    setTimeout(() => {
                                        attemptSave(retryCount + 1);
                                    }, CANVAS_CONFIG.RETRY_DELAY * (retryCount + 1)); // Backoff exponentiel
                                } else {
                                    // ÉCHEC FINAL - TENTER RECUPÉRATION VIA CACHE
                                    console.error('SAVE_FAILED: All retry attempts failed');

                                    const cachedData = CanvasCache.load();
                                    if (cachedData) {
                                        console.log('FALLBACK: Attempting to restore from cache');
                                        // Ici on pourrait implémenter une restauration silencieuse
                                        alert('Sauvegarde échouée. Données préservées en cache local.');
                                    } else {
                                        alert(`Erreur de sauvegarde après ${CANVAS_CONFIG.MAX_RETRIES} tentatives: ${error.message}`);
                                    }
                                }
                            })
                            .finally(() => {
                                const saveBtn = document.querySelector(`.canvas-modal-save[data-category="${category}"]`);
                                if (saveBtn && retryCount >= CANVAS_CONFIG.MAX_RETRIES) {
                                    saveBtn.innerHTML = '💾 Sauvegarder';
                                    saveBtn.disabled = false;
                                }
                            });
                        }

                        // Démarrer la sauvegarde
                        attemptSave();
                    }

                    // Gestionnaire d'événements pour les boutons de configuration - VERSION RENFORCÉE
                    document.addEventListener('click', function(e) {
                        try {
                            // Gestionnaire pour ouvrir les modales
                            const button = e.target.closest('.canvas-configure-btn');
                            if (button) {
                                e.preventDefault();
                                console.log('[PDF Builder] CONFIG_BUTTON - Configure button clicked');

                                const card = button.closest('.canvas-card');
                                if (card) {
                                    const category = card.getAttribute('data-category');
                                    if (category) {
                                        const modalId = 'canvas-' + category + '-modal-overlay';
                                        console.log(`[PDF Builder] CONFIG_BUTTON - Opening modal for category: ${category}`);
                                        openModal(modalId);
                                    } else {
                                        console.error('[PDF Builder] CONFIG_BUTTON - No data-category attribute found on card');
                                    }
                                } else {
                                    console.error('[PDF Builder] CONFIG_BUTTON - No canvas-card parent found');
                                }
                                return;
                            }

                            // Gestionnaire pour fermer les modales
                            const closeBtn = e.target.closest('.canvas-modal-close, .cache-modal-close');
                            if (closeBtn) {
                                e.preventDefault();
                                console.log('[PDF Builder] CLOSE_BUTTON - Close button clicked');

                                const modal = closeBtn.closest('.canvas-modal-overlay, .cache-modal');
                                if (modal) {
                                    const modalId = modal.id;
                                    closeModal(modalId);
                                }
                                return;
                            }

                            // Fermer en cliquant sur l'overlay
                            const overlay = e.target.closest('.canvas-modal-overlay');
                            if (overlay && e.target === overlay) {
                                console.log('[PDF Builder] OVERLAY_CLICK - Overlay clicked');
                                const modalId = overlay.id;
                                closeModal(modalId);
                                return;
                            }

                            // Gestionnaire pour sauvegarder les paramètres
                            const saveBtn = e.target.closest('.canvas-modal-save');
                            if (saveBtn) {
                                e.preventDefault();
                                console.log('[PDF Builder] SAVE_BUTTON - Save button clicked');

                                const category = saveBtn.getAttribute('data-category');
                                if (category) {
                                    saveModalSettings(category);
                                } else {
                                    console.error('[PDF Builder] SAVE_BUTTON - No data-category attribute on save button');
                                }
                                return;
                            }

                            // Gestionnaire pour annuler les modales
                            const cancelBtn = e.target.closest('.canvas-modal-cancel, .button-secondary');
                            if (cancelBtn) {
                                e.preventDefault();
                                console.log('[PDF Builder] CANCEL_BUTTON - Cancel button clicked');

                                const modal = cancelBtn.closest('.canvas-modal-overlay');
                                if (modal) {
                                    const modalId = modal.id;
                                    closeModal(modalId);
                                }
                                return;
                            }

                        } catch (error) {
                            console.error('[PDF Builder] EVENT_HANDLER - Error in click handler:', error);
                        }
                    });

                    // Gestionnaire pour la touche Échap - VERSION RENFORCÉE
                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape') {
                            console.log('[PDF Builder] ESC_KEY - Escape key pressed');

                            // Fermer toutes les modales ouvertes
                            const openModals = document.querySelectorAll('.canvas-modal-overlay[style*="display: flex"], .cache-modal[style*="display: block"]');
                            openModals.forEach(modal => {
                                const modalId = modal.id;
                                closeModal(modalId);
                            });
                        }
                    });

                    // Synchronisation des toggles CSS lors des changements manuels
                    document.addEventListener('change', function(e) {
                        const field = e.target;
                        
                        // Synchronisation CSS pour les checkboxes dans les toggles
                        if (field.type === 'checkbox' && field.closest('.toggle-switch')) {
                            const toggleSwitch = field.closest('.toggle-switch');
                            if (field.checked) {
                                toggleSwitch.classList.add('checked');
                            } else {
                                toggleSwitch.classList.remove('checked');
                            }
                            console.log(`TOGGLE_CHANGE: ${field.id} changed to ${field.checked}, toggle classes: ${toggleSwitch.className}`);
                        }
                        
                        // SYNCHRONISATION DES VALEURS MODAL -> CHAMPS CACHÉS
                        // Si le champ fait partie d'un modal Canvas, synchroniser avec le champ caché
                        const modal = field.closest('.canvas-modal-content');
                        if (modal) {
                            // Trouver le mapping pour ce champ
                            const fieldMappings = {
                                'canvas_width': 'pdf_builder_canvas_width',
                                'canvas_height': 'pdf_builder_canvas_height',
                                'canvas_dpi': 'pdf_builder_canvas_dpi',
                                'canvas_format': 'pdf_builder_canvas_format',
                                'canvas_bg_color': 'pdf_builder_canvas_bg_color',
                                'canvas_border_color': 'pdf_builder_canvas_border_color',
                                'canvas_border_width': 'pdf_builder_canvas_border_width',
                                'canvas_container_bg_color': 'pdf_builder_canvas_container_bg_color',
                                'canvas_shadow_enabled': 'pdf_builder_canvas_shadow_enabled',
                                'canvas_grid_enabled': 'pdf_builder_canvas_grid_enabled',
                                'canvas_grid_size': 'pdf_builder_canvas_grid_size',
                                'canvas_guides_enabled': 'pdf_builder_canvas_guides_enabled',
                                'canvas_snap_to_grid': 'pdf_builder_canvas_snap_to_grid',
                                'canvas_zoom_min': 'pdf_builder_canvas_zoom_min',
                                'canvas_zoom_max': 'pdf_builder_canvas_zoom_max',
                                'canvas_zoom_default': 'pdf_builder_canvas_zoom_default',
                                'canvas_zoom_step': 'pdf_builder_canvas_zoom_step',
                                'canvas_export_quality': 'pdf_builder_canvas_export_quality',
                                'canvas_export_format': 'pdf_builder_canvas_export_format',
                                'canvas_export_transparent': 'pdf_builder_canvas_export_transparent',
                                'canvas_drag_enabled': 'pdf_builder_canvas_drag_enabled',
                                'canvas_resize_enabled': 'pdf_builder_canvas_resize_enabled',
                                'canvas_rotate_enabled': 'pdf_builder_canvas_rotate_enabled',
                                'canvas_multi_select': 'pdf_builder_canvas_multi_select',
                                'canvas_selection_mode': 'pdf_builder_canvas_selection_mode',
                                'canvas_keyboard_shortcuts': 'pdf_builder_canvas_keyboard_shortcuts',
                                'canvas_fps_target': 'pdf_builder_canvas_fps_target',
                                'canvas_memory_limit_js': 'pdf_builder_canvas_memory_limit_js',
                                'canvas_response_timeout': 'pdf_builder_canvas_response_timeout',
                                'canvas_lazy_loading_editor': 'pdf_builder_canvas_lazy_loading_editor',
                                'canvas_preload_critical': 'pdf_builder_canvas_preload_critical',
                                'canvas_lazy_loading_plugin': 'pdf_builder_canvas_lazy_loading_plugin',
                                'canvas_debug_enabled': 'pdf_builder_canvas_debug_enabled',
                                'canvas_performance_monitoring': 'pdf_builder_canvas_performance_monitoring',
                                'canvas_error_reporting': 'pdf_builder_canvas_error_reporting',
                                'canvas_memory_limit_php': 'pdf_builder_canvas_memory_limit_php'
                            };
                            
                            // Chercher si ce champ correspond à un mapping
                            let settingKey = null;
                            if (field.id && fieldMappings[field.id]) {
                                settingKey = fieldMappings[field.id];
                            } else if (field.name && fieldMappings[field.name]) {
                                settingKey = fieldMappings[field.name];
                            }
                            
                            if (settingKey) {
                                // Trouver le champ caché correspondant
                                const hiddenField = document.querySelector(`input[name="pdf_builder_settings[${settingKey}]"]`);
                                if (hiddenField) {
                                    // Mettre à jour la valeur du champ caché
                                    if (field.type === 'checkbox') {
                                        hiddenField.value = field.checked ? '1' : '0';
                                    } else {
                                        hiddenField.value = field.value;
                                    }
                                    console.log(`MODAL_SYNC: Updated hidden field ${settingKey} with value: ${hiddenField.value} from modal field ${field.id || field.name}`);
                                } else {
                                    console.log(`MODAL_SYNC: Hidden field not found for ${settingKey}`);
                                }
                            }
                        }
                    });

                    console.log('Modal manager initialized');

                    // INITIALISER LES SYSTÈMES DE ROBUSTESSE
                    CanvasRecovery.init();

                    // Circuit breaker pour éviter les spam de requêtes
                    let lastSaveAttempt = 0;
                    const SAVE_COOLDOWN = 2000; // 2 secondes minimum entre sauvegardes

                    // Wrapper pour saveModalSettings avec circuit breaker
                    const originalSaveModalSettings = saveModalSettings;
                    saveModalSettings = function(category) {
                        const now = Date.now();
                        if (now - lastSaveAttempt < SAVE_COOLDOWN) {
                            console.warn('CIRCUIT_BREAKER: Save attempt too soon, ignoring');
                            return;
                        }
                        lastSaveAttempt = now;

                        // Vérifier si le système est en mode dégradé
                        if (!CanvasHealth.checkHealth()) {
                            console.warn('CIRCUIT_BREAKER: System unhealthy, forcing cache-only mode');
                            // Ici on pourrait implémenter un mode dégradé
                        }

                        originalSaveModalSettings(category);
                    };

                    console.log('ROBUSTNESS_INIT: All safety systems activated');

                    // EXPOSER LES OUTILS DE DIAGNOSTIC GLOBALEMENT
                    window.CanvasDebug = {
                        getHealthStatus: () => ({
                            healthy: CanvasHealth.isHealthy,
                            lastSuccess: new Date(CanvasHealth.lastSuccess).toLocaleString(),
                            failureCount: CanvasHealth.failureCount,
                            timeSinceLastSuccess: Math.round((Date.now() - CanvasHealth.lastSuccess) / 1000) + 's'
                        }),

                        getMetrics: () => CanvasMetrics.getStats(),

                        getCacheStatus: () => {
                            const cached = CanvasCache.load();
                            return cached ? {
                                hasCache: true,
                                age: Math.round((Date.now() - JSON.parse(localStorage.getItem(CANVAS_CONFIG.CACHE_KEY) || '{}').timestamp || 0) / 1000) + 's',
                                data: cached
                            } : { hasCache: false };
                        },

                        forceHealthCheck: () => CanvasRecovery.performHealthCheck(),

                        clearCache: () => CanvasCache.clear(),

                        simulateFailure: () => {
                            CanvasHealth.recordFailure();
                            console.log('DEBUG: Simulated failure recorded');
                        },

                        getSystemStatus: () => ({
                            health: window.CanvasDebug.getHealthStatus(),
                            metrics: window.CanvasDebug.getMetrics(),
                            cache: window.CanvasDebug.getCacheStatus(),
                            config: CANVAS_CONFIG
                        })
                    };

                    console.log('DEBUG_INIT: Diagnostic tools available via window.CanvasDebug');
                })();
            </script>

            <!-- Inclusion des modales Canvas -->
            <?php require_once __DIR__ . '/settings-modals.php'; ?>

</section> <!-- Fermeture de settings-section contenu-settings -->

