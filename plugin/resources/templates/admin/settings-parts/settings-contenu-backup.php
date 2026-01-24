<?php
    /**
     * PDF Builder Pro - Content Settings Tab
     * Canvas and design configuration settings
     * Updated: 2025-12-03
     */

    // require_once __DIR__ . '/settings-helpers.php'; // REMOVED - settings-helpers.php deleted

    $settings = get_option('pdf_builder_settings', array());

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

                <!-- Champs cachés pour la sauvegarde centralisée des paramètres -->
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_width]" value="<?php echo esc_attr($settings['pdf_builder_canvas_width'] ?? '794'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_height]" value="<?php echo esc_attr($settings['pdf_builder_canvas_height'] ?? '1123'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_dpi]" value="<?php echo esc_attr($settings['pdf_builder_canvas_dpi'] ?? '96'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_format]" value="<?php echo esc_attr($settings['pdf_builder_canvas_format'] ?? 'A4'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_bg_color]" value="<?php echo esc_attr($settings['pdf_builder_canvas_bg_color'] ?? '#ffffff'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_border_color]" value="<?php echo esc_attr($settings['pdf_builder_canvas_border_color'] ?? '#cccccc'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_border_width]" value="<?php echo esc_attr($settings['pdf_builder_canvas_border_width'] ?? '1'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_shadow_enabled]" value="<?php echo esc_attr($settings['pdf_builder_canvas_shadow_enabled'] ?? '0'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_container_bg_color]" value="<?php echo esc_attr($settings['pdf_builder_canvas_container_bg_color'] ?? '#f8f9fa'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_grid_enabled]" value="<?php echo esc_attr($settings['pdf_builder_canvas_grid_enabled'] ?? '1'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_grid_size]" value="<?php echo esc_attr($settings['pdf_builder_canvas_grid_size'] ?? '20'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_guides_enabled]" value="<?php echo esc_attr($settings['pdf_builder_canvas_guides_enabled'] ?? '1'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_snap_to_grid]" value="<?php echo esc_attr($settings['pdf_builder_canvas_snap_to_grid'] ?? '1'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_zoom_min]" value="<?php echo esc_attr($settings['pdf_builder_canvas_zoom_min'] ?? '25'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_zoom_max]" value="<?php echo esc_attr($settings['pdf_builder_canvas_zoom_max'] ?? '500'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_zoom_default]" value="<?php echo esc_attr($settings['pdf_builder_canvas_zoom_default'] ?? '100'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_zoom_step]" value="<?php echo esc_attr($settings['pdf_builder_canvas_zoom_step'] ?? '25'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_export_quality]" value="<?php echo esc_attr($settings['pdf_builder_canvas_export_quality'] ?? '90'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_export_format]" value="<?php echo esc_attr($settings['pdf_builder_canvas_export_format'] ?? 'png'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_export_transparent]" value="<?php echo esc_attr($settings['pdf_builder_canvas_export_transparent'] ?? '0'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_drag_enabled]" value="<?php echo esc_attr($settings['pdf_builder_canvas_drag_enabled'] ?? '1'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_resize_enabled]" value="<?php echo esc_attr($settings['pdf_builder_canvas_resize_enabled'] ?? '1'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_rotate_enabled]" value="<?php echo esc_attr($settings['pdf_builder_canvas_rotate_enabled'] ?? '1'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_multi_select]" value="<?php echo esc_attr($settings['pdf_builder_canvas_multi_select'] ?? '1'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_selection_mode]" value="<?php echo esc_attr($settings['pdf_builder_canvas_selection_mode'] ?? 'single'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_canvas_keyboard_shortcuts]" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_keyboard_shortcuts', '1')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_canvas_fps_target]" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_fps_target', '60')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_canvas_memory_limit_js]" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_memory_limit_js', '50')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_canvas_response_timeout]" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_response_timeout', '5000')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_canvas_lazy_loading_editor]" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_lazy_loading_editor', '1')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_canvas_preload_critical]" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_preload_critical', '1')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_canvas_lazy_loading_plugin]" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_lazy_loading_plugin', '1')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_canvas_debug_enabled]" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_debug_enabled', '0')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_canvas_performance_monitoring]" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_performance_monitoring', '0')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_error_reporting]" value="<?php echo esc_attr($settings['pdf_builder_canvas_error_reporting'] ?? '0'); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_memory_limit_php]" value="<?php echo esc_attr($settings['pdf_builder_canvas_memory_limit_php'] ?? '128'); ?>">

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
                                        <span id="card-canvas-width"><?php echo esc_html(get_option('pdf_builder_canvas_canvas_width', '794')); ?></span>×
                                        <span id="card-canvas-height"><?php echo esc_html(get_option('pdf_builder_canvas_canvas_height', '1123')); ?></span>px
                                    </div>
                                    <span class="preview-size" id="card-canvas-dpi">
                                        <?php
                                        $width = intval(get_option('pdf_builder_canvas_canvas_width', '794'));
                                        $height = intval(get_option('pdf_builder_canvas_canvas_height', '1123'));
                                        $dpi = intval(get_option('pdf_builder_canvas_canvas_dpi', '96'));
                                        $format = get_option('pdf_builder_canvas_canvas_format', 'A4');

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
                                        <div class="apparence-background" style="background-color: <?php echo esc_attr($settings['pdf_builder_canvas_canvas_bg_color'] ?? '#ffffff'); ?>;"></div>
                                        <!-- Bordure -->
                                        <div class="apparence-border" style="border: <?php echo esc_attr($settings['pdf_builder_canvas_canvas_border_width'] ?? '1'); ?>px solid <?php echo esc_attr($settings['pdf_builder_canvas_canvas_border_color'] ?? '#cccccc'); ?>;"></div>
                                        <!-- Ombre -->
                                        <?php if (($settings['pdf_builder_canvas_canvas_shadow_enabled'] ?? '0') === '1'): ?>
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
                                        <span class="legend-item"><?php echo (($settings['pdf_builder_canvas_canvas_shadow_enabled'] ?? '0') === '1') ? '🌑' : '☀️'; ?> Ombre</span>
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
                                            <span id="card-perf-preview" class="metric-value">60</span>
                                        </div>
                                        <div class="metric-item">
                                            <span class="metric-label">RAM JS</span>
                                            <span class="metric-value">50MB</span>
                                        </div>
                                        <div class="metric-item">
                                            <span class="metric-label">RAM PHP</span>
                                            <span class="metric-value">128MB</span>
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



            <!-- CSS pour les modales Canvas - REMOVED - file doesn't exist -->
            <?php
                // $plugin_dir = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
                // $css_url = plugins_url('resources/assets/css/canvas-modals.css', $plugin_dir . '/pdf-builder-pro.php');
                // wp_enqueue_style('pdf-builder-canvas-modals', $css_url, array(), '1.0.0');
            ?>

            <script>
                (function() {
                    'use strict';

                    // Fonction d'échappement pour les attributs HTML
                    function escapeHtmlAttr(str) {
                        return String(str).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                    }

                    // Fonction d'échappement pour le contenu HTML
                    function escapeHtml(str) {
                        const div = document.createElement('div');
                        div.textContent = str;
                        return div.innerHTML;
                    }

                    // Nonce pour les appels AJAX de sauvegarde
                    const pdfBuilderSaveNonce = '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>';

                    // État de la modal
                    var currentModalCategory = null;

                    /*
                    // SYSTÈME DE MONITORING UNIFIÉ
                    /*
                    const modalMonitoring = {
                        // Métriques de performance
                        metrics: {
                            modalOpens: 0,
                            modalCloses: 0,
                            savesSuccess: 0,
                            savesFailed: 0,
                            validationErrors: 0,
                            dependencyUpdates: 0,
                            previewUpdates: 0,
                            avgSaveTime: 0,
                            lastActivity: null
                        },

                        // Historique des actions
                        history: [],

                        // État actuel
                        currentState: {
                            activeModal: null,
                            lastSaveTime: null,
                            errors: [],
                            warnings: []
                        },

                        // Initialiser le monitoring
                        init: function() {
                            this.loadFromStorage();
                            this.log('system', 'Monitoring activé', { timestamp: Date.now() });
                        },

                        // Charger depuis localStorage
                        loadFromStorage: function() {
                            try {
                                const stored = localStorage.getItem('pdfBuilderMonitoring');
                                if (stored) {
                                    const data = JSON.parse(stored);
                                    this.metrics = Object.assign({}, this.metrics, data.metrics);
                                    this.history = data.history || [];
                                    this.currentState = Object.assign({}, this.currentState, data.currentState);
                                }
                            } catch (e) {
                                console.warn('⚠️ Erreur chargement monitoring localStorage:', e);
                            }
                        },

                        // Sauvegarder vers localStorage
                        saveToStorage: function() {
                            try {
                                const data = {
                                    metrics: this.metrics,
                                    history: this.history.slice(-50), // Garder seulement les 50 dernières
                                    currentState: this.currentState,
                                    lastSave: Date.now()
                                };
                                localStorage.setItem('pdfBuilderMonitoring', JSON.stringify(data));
                            } catch (e) {
                                console.warn('⚠️ Erreur sauvegarde monitoring localStorage:', e);
                            }
                        },

                        // Logger une action
                        log: function(type, message, data = {}) {
                            const entry = {
                                timestamp: Date.now(),
                                type: type,
                                message: message,
                                data: data,
                                modalCategory: typeof currentModalCategory !== 'undefined' ? currentModalCategory : null
                            };

                            this.history.push(entry);

                            // Garder seulement les 100 dernières entrées
                            if (this.history.length > 100) {
                                this.history.shift();
                            }

                            // Log console avec emoji selon le type
                            const emoji = {
                                'modal_open': '🚪',
                                'modal_close': '🚪❌',
                                'save_success': '💾✅',
                                'save_error': '💾❌',
                                'validation_error': '⚠️',
                                'dependency': '🔗',
                                'preview': '👁️',
                                'system': '🔧',
                                'performance': '⚡'
                            };

                            // Sauvegarder après chaque log
                            this.saveToStorage();
                        },

                        // Monitorer l'ouverture d'une modal
                        trackModalOpen: function(category) {
                            this.metrics.modalOpens++;
                            this.currentState.activeModal = category;
                            this.currentState.lastActivity = Date.now();
                            this.log('modal_open', `Modal ouverte: ${category}`, {
                                category: category,
                                totalOpens: this.metrics.modalOpens
                            });
                            this.updateVisualIndicator();
                        },

                        // Monitorer la fermeture d'une modal
                        trackModalClose: function(category) {
                            this.metrics.modalCloses++;
                            this.currentState.activeModal = null;
                            this.log('modal_close', `Modal fermée: ${category}`, {
                                category: category,
                                totalCloses: this.metrics.modalCloses
                            });
                            this.updateVisualIndicator();
                        },

                        // Monitorer une sauvegarde réussie
                        trackSaveSuccess: function(category, saveTime, fieldCount) {
                            this.metrics.savesSuccess++;
                            this.currentState.lastSaveTime = Date.now();

                            // Calculer la moyenne des temps de sauvegarde
                            const totalTime = this.metrics.avgSaveTime * (this.metrics.savesSuccess - 1) + saveTime;
                            this.metrics.avgSaveTime = totalTime / this.metrics.savesSuccess;

                            this.log('save_success', `Sauvegarde réussie: ${category}`, {
                                category: category,
                                saveTime: saveTime,
                                fieldCount: fieldCount,
                                avgSaveTime: this.metrics.avgSaveTime
                            });
                        },

                        // Monitorer une erreur de sauvegarde
                        trackSaveError: function(category, error, saveTime) {
                            this.metrics.savesFailed++;
                            this.currentState.errors.push({
                                type: 'save_error',
                                category: category,
                                error: error,
                                timestamp: Date.now()
                            });

                            this.log('save_error', `Erreur de sauvegarde: ${category}`, {
                                category: category,
                                error: error,
                                saveTime: saveTime,
                                totalErrors: this.metrics.savesFailed
                            });
                        },

                        // Monitorer les erreurs de validation
                        trackValidationError: function(category, errors) {
                            this.metrics.validationErrors += errors.length;
                            errors.forEach(error => {
                                this.currentState.errors.push({
                                    type: 'validation_error',
                                    category: category,
                                    field: error.field,
                                    message: error.message,
                                    timestamp: Date.now()
                                });
                            });

                            this.log('validation_error', `Erreurs de validation: ${category}`, {
                                category: category,
                                errors: errors,
                                totalValidationErrors: this.metrics.validationErrors
                            });
                        },

                        // Monitorer les mises à jour de dépendances
                        trackDependencyUpdate: function(masterField, dependentFields, isEnabled) {
                            this.metrics.dependencyUpdates++;
                            this.log('dependency', `Dépendance mise à jour: ${masterField}`, {
                                masterField: masterField,
                                dependentFields: dependentFields,
                                isEnabled: isEnabled,
                                totalUpdates: this.metrics.dependencyUpdates
                            });
                        },

                        // Monitorer les mises à jour de preview
                        trackPreviewUpdate: function(changedFields) {
                            this.metrics.previewUpdates++;
                            this.log('preview', `Preview mise à jour`, {
                                changedFields: changedFields,
                                totalUpdates: this.metrics.previewUpdates
                            });
                        },

                        // Obtenir les métriques actuelles
                        getMetrics: function() {
                            return Object.assign({}, this.metrics, {
                                historyLength: this.history.length,
                                currentState: this.currentState,
                                uptime: Date.now() - (this.history[0]?.timestamp || Date.now())
                            };
                        },

                        // Générer un rapport de monitoring
                        generateReport: function() {
                            const metrics = this.getMetrics();
                            const recentHistory = this.history.slice(-10);

                            return {
                                summary: {
                                    totalOpens: metrics.modalOpens,
                                    totalCloses: metrics.modalCloses,
                                    successRate: metrics.savesSuccess / Math.max(metrics.savesSuccess + metrics.savesFailed, 1) * 100,
                                    avgSaveTime: metrics.avgSaveTime,
                                    totalErrors: metrics.validationErrors + metrics.savesFailed,
                                    uptime: metrics.uptime
                                },
                                currentState: metrics.currentState,
                                recentActivity: recentHistory,
                                alerts: this.generateAlerts()
                            };
                        },

                        // Générer des alertes basées sur les métriques
                        generateAlerts: function() {
                            const alerts = [];
                            const metrics = this.metrics;

                            // Alerte si taux de succès des sauvegardes < 80%
                            const successRate = metrics.savesSuccess / Math.max(metrics.savesSuccess + metrics.savesFailed, 1);
                            if (successRate < 0.8 && (metrics.savesSuccess + metrics.savesFailed) > 5) {
                                alerts.push({
                                    level: 'warning',
                                    message: `Taux de succès des sauvegardes faible: ${(successRate * 100).toFixed(1)}%`,
                                    suggestion: 'Vérifier la connectivité réseau et les permissions AJAX'
                                });
                            }

                            // Alerte si temps moyen de sauvegarde > 2 secondes
                            if (metrics.avgSaveTime > 2000 && metrics.savesSuccess > 3) {
                                alerts.push({
                                    level: 'warning',
                                    message: `Temps de sauvegarde élevé: ${metrics.avgSaveTime.toFixed(0)}ms en moyenne`,
                                    suggestion: 'Optimiser les requêtes AJAX ou vérifier la charge serveur'
                                });
                            }

                            // Alerte si beaucoup d'erreurs de validation
                            if (metrics.validationErrors > 10) {
                                alerts.push({
                                    level: 'info',
                                    message: `${metrics.validationErrors} erreurs de validation détectées`,
                                    suggestion: 'Vérifier la configuration des champs de formulaire'
                                });
                            }

                            return alerts;
                        },

                        // Afficher le tableau de bord de monitoring (pour debug)
                        showDashboard: function() {
                            const report = this.generateReport();
                            // Mettre à jour l'indicateur visuel
                            this.updateVisualIndicator();
                        },

                        // Mettre à jour l'indicateur visuel de monitoring
                        updateVisualIndicator: function() {
                            const indicator = document.getElementById('monitoring-status');
                            if (!indicator) return;

                            const metrics = this.getMetrics();
                            const alerts = this.generateAlerts();

                            let status = '🔍 Monitoring actif';
                            let color = '#666';

                            if (alerts.some(a => a.level === 'warning')) {
                                status = '⚠️ Alertes détectées';
                                color = '#ffc107';
                            } else if (metrics.modalOpens > 0) {
                                status = `✅ ${metrics.modalOpens} modales ouvertes`;
                                color = '#28a745';
                            }

                            indicator.textContent = status;
                            indicator.style.color = color;
                            indicator.style.opacity = '1';

                            // Faire clignoter brièvement pour attirer l'attention
                            setTimeout(() => {
                                indicator.style.opacity = '0.7';
                            }, 2000);
                        },

                        // Démarrer l'auto-monitoring
                        startAutoMonitoring: function() {
                            // Vérifier l'état toutes les 30 secondes
                            setInterval(() => {
                                const alerts = this.generateAlerts();
                                if (alerts.length > 0) {
                                    console.group('🔍 Auto-Monitoring - Alertes détectées');
                                    alerts.forEach(alert => {
                                        console.warn(`[${alert.level.toUpperCase()}] ${alert.message}`);
                                    });
                                    console.groupEnd();
                                    this.updateVisualIndicator();
                                }
                            }, 30000);

                            // Mettre à jour l'indicateur visuel toutes les 5 secondes
                            setInterval(() => {
                                this.updateVisualIndicator();
                            }, 5000);
                        }
                    };
                    */

                    // Initialiser le monitoring
                    // modalMonitoring.init();



                    // ===========================================
                    // SYSTÈME UNIFIÉ DE FORMULAIRES DE MODALES
                    // ===========================================

                    /**
                     * Générateur unifié de formulaires pour les modales
                     * Centralise la création, validation et gestion des formulaires
                     */
                    /*
                    class ModalFormGenerator {

                        constructor() {
                            this.fieldDefinitions = {};
                            this.formValidators = {};
                            this.fieldDependencies = {};
                        }

                        /**
                         * Définit un champ de formulaire
                         */
                        defineField(name, config) {
                            this.fieldDefinitions[name] = {
                                type: config.type || 'text',
                                label: config.label || name,
                                placeholder: config.placeholder || '',
                                required: config.required || false,
                                min: config.min,
                                max: config.max,
                                step: config.step || 1,
                                options: config.options || [],
                                defaultValue: config.defaultValue || '',
                                description: config.description || '',
                                validation: config.validation || null,
                                dependencies: config.dependencies || [],
                                group: config.group || 'default'
                            };
                            return Object.assign(this.fieldDefinitions[name], config);
                        }

                        /**
                         * Définit une dépendance entre champs
                         */
                        addDependency(masterField, dependentFields) {
                            this.fieldDependencies[masterField] = dependentFields;
                            return this;
                        }

                        /**
                         * Ajoute un validateur personnalisé
                         */
                        addValidator(fieldName, validator) {
                            this.formValidators[fieldName] = validator;
                            return this;
                        }

                        /**
                         * Génère le HTML pour un champ
                         */
                        generateFieldHTML(fieldName, currentValue) {
                            const field = this.fieldDefinitions[fieldName];
                            if (!field) return '';

                            const fieldId = `pdf_builder_canvas_${fieldName}`;
                            const fieldNameAttr = `pdf_builder_canvas_${fieldName}`;
                            const value = currentValue !== undefined ? currentValue : field.defaultValue;
                            const escapedValue = typeof value === 'string' ? escapeHtmlAttr(value) : value;

                            let html = `<div class="form-group" data-field-group="${field.group}">`;

                            // Label
                            html += `<label for="${fieldId}">${escapeHtml(field.label)}`;
                            if (field.required) html += ' <span class="required">*</span>';
                            html += '</label>';

                            // Champ selon le type
                            switch (field.type) {
                                case 'color':
                                    html += `<input type="color" id="${fieldId}" name="${fieldNameAttr}" value="${escapedValue}">`;
                                    break;

                                case 'number':
                                    html += `<input type="number" id="${fieldId}" name="${fieldNameAttr}" value="${escapedValue}"`;
                                    if (field.min !== undefined) html += ` min="${field.min}"`;
                                    if (field.max !== undefined) html += ` max="${field.max}"`;
                                    if (field.step !== undefined) html += ` step="${field.step}"`;
                                    if (field.readonly) html += ` readonly`;
                                    html += '>';
                                    break;

                                case 'select':
                                    html += `<select id="${fieldId}" name="${fieldNameAttr}">`;
                                    field.options.forEach(option => {
                                        const selected = (option.value == value) ? ' selected' : '';
                                        const disabled = option.disabled ? ' disabled' : '';
                                        html += `<option value="${escapeHtmlAttr(option.value)}"${selected}${disabled}>${escapeHtml(option.label)}</option>`;
                                    });
                                    html += '</select>';
                                    break;

                                case 'checkbox':
                                    const checkedAttr = (value == '1' || value === true || value === 'true') ? ' checked' : '';
                                    html += '<label class="toggle-switch">';
                                    html += '<input type="checkbox" id="' + fieldId + '" name="' + fieldNameAttr + '" value="1"' + checkedAttr + '>';
                                    html += '<span class="toggle-slider"></span>';
                                    html += '</label>';
                                    break;

                                case 'range':
                                    html += `<input type="range" id="${fieldId}" name="${fieldNameAttr}" value="${escapedValue}"`;
                                    if (field.min !== undefined) html += ` min="${field.min}"`;
                                    if (field.max !== undefined) html += ` max="${field.max}"`;
                                    if (field.step !== undefined) html += ` step="${field.step}"`;
                                    html += `>`;
                                    break;

                                default: // text
                                    html += `<input type="text" id="${fieldId}" name="${fieldNameAttr}" value="${escapedValue}"`;
                                    if (field.placeholder) html += ` placeholder="${escapeHtmlAttr(field.placeholder)}"`;
                                    html += `>`;
                            }

                            // Description
                            if (field.description) {
                                html += `<small class="field-description">${escapeHtml(field.description)}</small>`;
                            }

                            html += '</div>';
                            return html;
                        }

                        /**
                         * Génère le HTML complet pour une modale
                         */
                        generateModalHTML(category) {
                            // Configuration des champs par catégorie
                            const categoryFields = {
                                dimensions: ['width', 'height', 'dpi', 'format'],
                                apparence: ['bg_color', 'border_color', 'border_width', 'shadow_enabled'],
                                grille: ['grid_enabled', 'grid_size', 'guides_enabled', 'snap_to_grid'],
                                zoom: ['zoom_min', 'zoom_max', 'zoom_default', 'zoom_step'],
                                interactions: ['drag_enabled', 'resize_enabled', 'rotate_enabled', 'multi_select', 'selection_mode', 'keyboard_shortcuts'],
                                export: ['export_format', 'export_quality', 'export_transparent'],
                                performance: ['fps_target', 'memory_limit_js', 'response_timeout', 'lazy_loading_editor', 'preload_critical', 'lazy_loading_plugin'],
                                debug: ['debug_enabled', 'performance_monitoring', 'error_reporting', 'memory_limit_php']
                            };

                            const fields = categoryFields[category];
                            if (!fields) {
                                console.error('Catégorie de modal inconnue:', category);
                                return '<p>Erreur: Catégorie de modal inconnue</p>';
                            }

                            let html = '<div class="modal-form-grid">';

                            fields.forEach(fieldName => {
                                const hiddenField = document.querySelector(`input[name="pdf_builder_settings[pdf_builder_canvas_${fieldName}]"]`);
                                const currentValue = hiddenField ? hiddenField.value : '';
                                html += this.generateFieldHTML(fieldName, currentValue);
                            });

                            html += '</div>';
                            return html;
                        }

                        /**
                         * Valide un formulaire
                         */
                        validateForm(category) {
                            const errors = [];
                            const modal = document.querySelector(`#canvas-${category}-modal`);
                            if (!modal) return errors;

                            const fields = modal.querySelectorAll('input, select');

                            fields.forEach(field => {
                                const fieldName = field.name.replace('pdf_builder_canvas_canvas_', '');
                                const fieldConfig = this.fieldDefinitions[fieldName];

                                if (!fieldConfig) return;

                                // Validation required
                                if (fieldConfig.required && !field.value.trim()) {
                                    errors.push(`${fieldConfig.label} est requis`);
                                }

                                // Validation personnalisée
                                if (fieldConfig.validation && typeof fieldConfig.validation === 'function') {
                                    const customError = fieldConfig.validation(field.value);
                                    if (customError) errors.push(customError);
                                }

                                // Validation numérique
                                if (fieldConfig.type === 'number') {
                                    const numValue = parseFloat(field.value);
                                    if (isNaN(numValue)) {
                                        errors.push(`${fieldConfig.label} doit être un nombre`);
                                    } else {
                                        if (fieldConfig.min !== undefined && numValue < fieldConfig.min) {
                                            errors.push(`${fieldConfig.label} doit être au moins ${fieldConfig.min}`);
                                        }
                                        if (fieldConfig.max !== undefined && numValue > fieldConfig.max) {
                                            errors.push(`${fieldConfig.label} doit être au plus ${fieldConfig.max}`);
                                        }
                                    }
                                }
                            });

                            return errors;
                        }

                        /**
                         * Met à jour les dépendances des champs
                         */
                        updateFieldDependencies(masterField, isEnabled) {
                            const dependentFields = this.fieldDependencies[masterField];
                            if (!dependentFields) return;

                            // Monitorer la mise à jour de dépendance
                            modalMonitoring.trackDependencyUpdate(masterField, dependentFields, isEnabled);

                            dependentFields.forEach(dependentField => {
                                const fieldElement = document.querySelector(`[name="pdf_builder_settings[pdf_builder_canvas_canvas_${dependentField}"]`);
                                if (fieldElement) {
                                    const formGroup = fieldElement.closest('.form-group');
                                    if (formGroup) {
                                        if (isEnabled) {
                                            formGroup.style.opacity = '1';
                                            fieldElement.disabled = false;
                                        } else {
                                            formGroup.style.opacity = '0.5';
                                            fieldElement.disabled = true;
                                        }
                                    }
                                }
                            });
                        }
                    }
                    */
                    */

                    // ===========================================
                    // CONFIGURATION CENTRALISÉE DES MODALES
                    // ===========================================

                    /*
                    // Initialiser le générateur de formulaires
                    const formGenerator = new ModalFormGenerator();

                    // Définir tous les champs disponibles
                    formGenerator
                        // Dimensions
                        .defineField('width', {
                            type: 'number',
                            label: 'Largeur (px)',
                            min: 100,
                            max: 5000,
                            defaultValue: '794',
                            readonly: true,
                            group: 'dimensions'
                        })
                        .defineField('height', {
                            type: 'number',
                            label: 'Hauteur (px)',
                            min: 100,
                            max: 5000,
                            defaultValue: '1123',
                            readonly: true,
                            group: 'dimensions'
                        })
                        .defineField('dpi', {
                            type: 'select',
                            label: 'DPI',
                            options: [
                                { value: '72', label: '72 (Web)' },
                                { value: '96', label: '96 (Écran)' },
                                { value: '150', label: '150 (Impression)' },
                                { value: '300', label: '300 (Haute qualité)' }
                            ],
                            defaultValue: '96',
                            group: 'dimensions'
                        })
                        .defineField('format', {
                            type: 'select',
                            label: 'Format prédéfini',
                            options: [
                                { value: 'A4', label: 'A4 (210×297mm)' },
                                { value: 'A3', label: 'A3 (297×420mm) - Bientôt', disabled: true },
                                { value: 'Letter', label: 'Letter (8.5×11") - Bientôt', disabled: true },
                                { value: 'Legal', label: 'Legal (8.5×14") - Bientôt', disabled: true }
                            ],
                            defaultValue: 'A4',
                            group: 'dimensions'
                        })

                        // Apparence
                        .defineField('bg_color', {
                            type: 'color',
                            label: 'Couleur de fond',
                            defaultValue: '#ffffff',
                            group: 'apparence'
                        })
                        .defineField('border_color', {
                            type: 'color',
                            label: 'Couleur bordure',
                            defaultValue: '#cccccc',
                            group: 'apparence'
                        })
                        .defineField('border_width', {
                            type: 'number',
                            label: 'Épaisseur bordure (px)',
                            min: 0,
                            max: 10,
                            defaultValue: '1',
                            group: 'apparence'
                        })
                        .defineField('shadow_enabled', {
                            type: 'checkbox',
                            label: 'Ombre activée',
                            defaultValue: '0',
                            group: 'apparence'
                        })

                        // Grille & Guides
                        .defineField('grid_enabled', {
                            type: 'checkbox',
                            label: 'Grille activée',
                            defaultValue: '1',
                            group: 'grille'
                        })
                        .defineField('grid_size', {
                            type: 'number',
                            label: 'Taille grille (px)',
                            min: 5,
                            max: 100,
                            defaultValue: '20',
                            group: 'grille'
                        })
                        .defineField('guides_enabled', {
                            type: 'checkbox',
                            label: 'Guides activés',
                            defaultValue: '1',
                            group: 'grille'
                        })
                        .defineField('snap_to_grid', {
                            type: 'checkbox',
                            label: 'Accrochage à la grille',
                            defaultValue: '1',
                            group: 'grille'
                        })

                        // Zoom & Navigation
                        .defineField('zoom_min', {
                            type: 'number',
                            label: 'Zoom minimum (%)',
                            min: 10,
                            max: 100,
                            defaultValue: '25',
                            group: 'zoom'
                        })
                        .defineField('zoom_max', {
                            type: 'number',
                            label: 'Zoom maximum (%)',
                            min: 100,
                            max: 1000,
                            defaultValue: '500',
                            group: 'zoom'
                        })
                        .defineField('zoom_default', {
                            type: 'number',
                            label: 'Zoom par défaut (%)',
                            min: 25,
                            max: 500,
                            defaultValue: '100',
                            group: 'zoom'
                        })
                        .defineField('zoom_step', {
                            type: 'number',
                            label: 'Pas de zoom (%)',
                            min: 5,
                            max: 50,
                            defaultValue: '25',
                            group: 'zoom'
                        })

                        // Interaction
                        .defineField('drag_enabled', {
                            type: 'checkbox',
                            label: 'Glisser activé',
                            defaultValue: '1',
                            group: 'interactions'
                        })
                        .defineField('resize_enabled', {
                            type: 'checkbox',
                            label: 'Redimensionnement activé',
                            defaultValue: '1',
                            group: 'interactions'
                        })
                        .defineField('rotate_enabled', {
                            type: 'checkbox',
                            label: 'Rotation activée',
                            defaultValue: '1',
                            group: 'interactions'
                        })
                        .defineField('multi_select', {
                            type: 'checkbox',
                            label: 'Sélection multiple',
                            defaultValue: '1',
                            group: 'interactions'
                        })
                        .defineField('selection_mode', {
                            type: 'select',
                            label: 'Mode de sélection',
                            options: [
                                { value: 'single', label: 'Simple' },
                                { value: 'multiple', label: 'Multiple' },
                                { value: 'group', label: 'Grouper' }
                            ],
                            defaultValue: 'single',
                            group: 'interactions'
                        })
                        .defineField('keyboard_shortcuts', {
                            type: 'checkbox',
                            label: 'Raccourcis clavier',
                            defaultValue: '1',
                            group: 'interactions'
                        })

                        // Export
                        .defineField('export_format', {
                            type: 'select',
                            label: 'Format d\'export',
                            options: [
                                { value: 'png', label: 'PNG' },
                                { value: 'jpg', label: 'JPEG' },
                                { value: 'svg', label: 'SVG' },
                                { value: 'pdf', label: 'PDF' }
                            ],
                            defaultValue: 'png',
                            group: 'export'
                        })
                        .defineField('export_quality', {
                            type: 'number',
                            label: 'Qualité (%)',
                            min: 10,
                            max: 100,
                            defaultValue: '90',
                            group: 'export'
                        })
                        .defineField('export_transparent', {
                            type: 'checkbox',
                            label: 'Fond transparent',
                            defaultValue: '0',
                            group: 'export'
                        })

                        // Performance
                        .defineField('fps_target', {
                            type: 'number',
                            label: 'FPS cible',
                            min: 10,
                            max: 120,
                            defaultValue: '60',
                            group: 'performance'
                        })
                        .defineField('memory_limit_js', {
                            type: 'number',
                            label: 'Limite mémoire JS (MB)',
                            min: 10,
                            max: 500,
                            defaultValue: '50',
                            group: 'performance'
                        })
                        .defineField('response_timeout', {
                            type: 'number',
                            label: 'Timeout réponse (ms)',
                            min: 1000,
                            max: 30000,
                            defaultValue: '5000',
                            group: 'performance'
                        })
                        .defineField('lazy_loading_editor', {
                            type: 'checkbox',
                            label: 'Chargement différé éditeur',
                            defaultValue: '1',
                            group: 'performance'
                        })
                        .defineField('preload_critical', {
                            type: 'checkbox',
                            label: 'Préchargement critique',
                            defaultValue: '1',
                            group: 'performance'
                        })
                        .defineField('lazy_loading_plugin', {
                            type: 'checkbox',
                            label: 'Chargement différé plugin',
                            defaultValue: '1',
                            group: 'performance'
                        })

                        // Debug & Maintenance
                        .defineField('debug_enabled', {
                            type: 'checkbox',
                            label: 'Debug activé',
                            defaultValue: '0',
                            group: 'debug'
                        })
                        .defineField('performance_monitoring', {
                            type: 'checkbox',
                            label: 'Monitoring performance',
                            defaultValue: '0',
                            group: 'debug'
                        })
                        .defineField('error_reporting', {
                            type: 'checkbox',
                            label: 'Rapport d\'erreurs',
                            defaultValue: '0',
                            group: 'debug'
                        })
                        .defineField('memory_limit_php', {
                            type: 'number',
                            label: 'Limite mémoire PHP (MB)',
                            min: 32,
                            max: 1024,
                            defaultValue: '128',
                            group: 'debug'
                        });

                    // Définir les dépendances entre champs
                    formGenerator
                        .addDependency('grid_enabled', ['snap_to_grid', 'grid_size'])
                        .addDependency('guides_enabled', []);
                    */

                    // Ouvrir une modal avec le nouveau système de génération
                    function openModal(category) {
                        // Fermer toute modal existante
                        if (currentModalCategory) {
                            closeModal();
                        }

                        currentModalCategory = category;

                        // Monitorer l'ouverture
                        modalMonitoring.trackModalOpen(category);

                        // Générer le contenu de la modal
                        const modalContent = formGenerator.generateModalHTML(category);

                        // Insérer le contenu dans la modal (chercher d'abord dans l'overlay, puis dans la modal)
                        const modalId = `canvas-${category}-modal`;
                        const overlay = document.getElementById(`canvas-${category}-modal-overlay`);
                        let modalBody = null;

                        // Chercher d'abord dans l'overlay (pour les modales restructurées)
                        if (overlay) {
                            modalBody = overlay.querySelector('.canvas-modal-body');
                        }

                        // Si pas trouvé dans l'overlay, chercher dans la modal elle-même (compatibilité)
                        if (!modalBody) {
                            const modal = document.getElementById(modalId);
                            if (modal) {
                                modalBody = modal.querySelector('.canvas-modal-body');
                            }
                        }

                        if (modalBody) {
                            modalBody.innerHTML = modalContent;
                        } else {
                            console.error('❌ [OPEN MODAL] Modal body NON trouvé pour:', modalId);
                        }

                        // Afficher la modal en ajoutant la classe 'active' à l'overlay

                        if (overlay) {
                            const wasActive = overlay.classList.contains('active');

                            if (!wasActive) {
                                // Add active class to overlay only (canvas-modal elements removed)
                                overlay.classList.add('active');
                                document.body.classList.add('canvas-modal-open');
                            }

                            // Synchroniser les valeurs de la modal avec les champs cachés
                            modalSettingsManager.syncModalValues();

                            // Configurer les event listeners pour cette modal après l'ouverture
                            setTimeout(() => {
                            }, 100);
                        } else {
                            console.error('❌ [OPEN MODAL] Overlay NON trouvé pour:', category);
                        }
                    }

                    // Gestionnaire centralisé des paramètres des modales
                    const modalSettingsManager = {
                        // Synchroniser les valeurs des champs cachés vers la modal actuelle
                        syncModalValues: function() {
                            if (!currentModalCategory) return;

                            // Trouver la modal actuelle
                            let currentModal = document.querySelector(`#canvas-${currentModalCategory}-modal-fullscreen`);
                            if (!currentModal) {
                                currentModal = document.querySelector(`#canvas-${currentModalCategory}-modal`);
                            }
                            if (!currentModal) return;

                            // Pour chaque champ caché canvas, mettre à jour le champ correspondant dans la modal
                            const hiddenFields = document.querySelectorAll('input[type="hidden"][name^="pdf_builder_settings[pdf_builder_canvas_"]');

                            hiddenFields.forEach(hiddenField => {
                                const fieldName = hiddenField.name.replace('pdf_builder_settings[', '').replace(']', '');
                                const modalField = currentModal.querySelector(`[name="${fieldName}"]`);

                                if (modalField) {
                                    const value = hiddenField.value;

                                    if (modalField.type === 'checkbox' || modalField.classList.contains('toggle-checkbox')) {
                                        modalField.checked = value === '1';
                                    } else {
                                        modalField.value = value;
                                    }
                                }
                            });
                        },

                        // Sauvegarder les paramètres de la modal actuelle
                        saveModalSettings: function() {
                            if (!currentModalCategory) return;

                            const startTime = Date.now();

                            // Validation du formulaire
                            const errors = formGenerator.validateForm(currentModalCategory);
                            if (errors.length > 0) {
                                modalMonitoring.trackValidationError(currentModalCategory, errors);
                                alert('Erreurs de validation:\n' + errors.join('\n'));
                                return;
                            }

                            // Trouver le modal actuel (fullscreen d'abord, puis original)
                            let currentModal = document.querySelector(`#canvas-${currentModalCategory}-modal-fullscreen`);
                            if (!currentModal) {
                                currentModal = document.querySelector(`#canvas-${currentModalCategory}-modal`);
                            }
                            if (!currentModal) {
                                console.error('Modal non trouvé pour sauvegarde:', currentModalCategory);
                                return;
                            }

                            // Collecter toutes les valeurs des champs de la modal
                            const modalInputs = currentModal.querySelectorAll('input, select');
                            const updatedValues = {};

                            modalInputs.forEach(input => {
                                if (!input.name) return;

                                let value;
                                if (input.type === 'checkbox') {
                                    value = input.checked ? '1' : '0';
                                } else if (input.type === 'number') {
                                    value = parseFloat(input.value) || 0;
                                } else {
                                    value = input.value;
                                }

                                updatedValues[input.name] = value;

                                // Mettre à jour le champ caché correspondant
                                const hiddenField = document.querySelector(`input[name="${input.name}"]`);
                                if (hiddenField) {
                                    hiddenField.value = value;
                                }
                            });

                            // Sauvegarder côté serveur via AJAX
                            this.saveToServer(updatedValues);

                            // La modale sera fermée dans le callback AJAX après mise à jour des previews
                            // closeModal(); // Déplacé dans le callback AJAX
                        },

                        // Mettre à jour l'affichage des valeurs dans les cartes après sauvegarde
                        // Mise à jour dynamique des previews désactivée
                        /*
                        updateDisplayValues: function() {

                            // Récupérer les valeurs depuis les champs cachés
                            const widthField = document.querySelector('input[name="pdf_builder_settings[pdf_builder_canvas_canvas_width]"]');
                            const heightField = document.querySelector('input[name="pdf_builder_settings[pdf_builder_canvas_canvas_height]"]');
                            const dpiField = document.querySelector('input[name="pdf_builder_settings[pdf_builder_canvas_canvas_dpi]"]');
                            const formatField = document.querySelector('input[name="pdf_builder_settings[pdf_builder_canvas_canvas_format]"]');

                            const width = widthField ? widthField.value : '794';
                            const height = heightField ? heightField.value : '1123';
                            const dpi = dpiField ? dpiField.value : '96';
                            const format = formatField ? formatField.value : 'A4';

                            // Calculer les dimensions en mm
                            const calculateMM = function(pixels, dpi) {
                                return ((pixels / dpi) * 25.4).toFixed(1);
                            };

                            const widthMM = calculateMM(width, dpi);
                            const heightMM = calculateMM(height, dpi);

                            // Mettre à jour les éléments d'affichage
                            const widthEl = document.getElementById('card-canvas-width');
                            const heightEl = document.getElementById('card-canvas-height');
                            const dpiEl = document.getElementById('card-canvas-dpi');

                            if (widthEl) {
                                widthEl.textContent = width;
                            }
                            if (heightEl) {
                                heightEl.textContent = height;
                            }
                            if (dpiEl) {
                                dpiEl.textContent = `${dpi} DPI - ${format} (${widthMM}×${heightMM}mm)`;
                            }
                        },
                        */

                        // Sauvegarder côté serveur
                        saveToServer: function(values) {
                            const saveStartTime = Date.now();

                            // Créer FormData avec les valeurs
                            const formData = new FormData();
                            formData.append('action', 'pdf_builder_save_all_settings');
                            formData.append('current_tab', 'contenu');
                            formData.append('nonce', pdfBuilderSaveNonce);

                            // Ajouter toutes les valeurs
                            Object.entries(values).forEach(([key, value]) => {
                                formData.append(key, value);
                            });

                            // Faire l'appel AJAX
                            fetch(pdfBuilderAjax?.ajaxurl || '/wp-admin/admin-ajax.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => {
                                return response.json();
                            })
                            .then(data => {
                                const saveTime = Date.now() - saveStartTime;
                                if (data.success) {

                                    modalMonitoring.trackSaveSuccess(currentModalCategory, saveTime, Object.keys(values).length);

                                    // Mise à jour dynamique des previews désactivée - previews restent statiques
                                    // this.updateDisplayValues();

                                    // Fermer la modale après sauvegarde
                                    closeModal();

                                    // Afficher une notification de succès sans rechargement de page
                                    if (window.pdfBuilderDeveloper && typeof window.pdfBuilderDeveloper.showSuccess === 'function') {
                                        window.pdfBuilderDeveloper.showSuccess('Paramètres sauvegardés avec succès');
                                    }

                                    // Mettre à jour les champs cachés du formulaire principal avec les nouvelles valeurs
                                    Object.entries(values).forEach(([key, value]) => {
                                        if (key.startsWith('pdf_builder_canvas_')) {
                                            const hiddenField = document.querySelector(`input[type="hidden"][name="${key}"]`);
                                            if (hiddenField) {
                                                hiddenField.value = value;
                                            }
                                        }
                                    });
                                } else {
                                    modalMonitoring.trackSaveError(currentModalCategory, data.data?.message || data.message || 'Erreur inconnue', saveTime);
                                    console.error('Erreur lors de la sauvegarde:', data.data?.message || data.message || 'Erreur inconnue');

                                    // Afficher une notification d'erreur
                                    if (window.pdfBuilderDeveloper && typeof window.pdfBuilderDeveloper.showError === 'function') {
                                        window.pdfBuilderDeveloper.showError(data.data?.message || data.message || 'Erreur inconnue');
                                    } else {
                                        console.error('❌ Erreur de sauvegarde:', data.data?.message || data.message || 'Erreur inconnue');
                                    }

                                    // Fermer la modale même en cas d'erreur
                                    closeModal();
                                }
                            })
                            .catch(error => {
                                const saveTime = Date.now() - saveStartTime;
                                modalMonitoring.trackSaveError(currentModalCategory, error.message || 'Erreur réseau', saveTime);
                                console.error('Erreur AJAX lors de la sauvegarde:', error);
                                // Fermer la modale même en cas d'erreur réseau
                                closeModal();
                            });
                        }
                    };

                    // Fermer la modal (version corrigée)
                    function closeModal() {
                        if (!currentModalCategory) {
                            return;
                        }

                        // Monitorer la fermeture
                        modalMonitoring.trackModalClose(currentModalCategory);

                        const modalId = `canvas-${currentModalCategory}-modal`;

                        // Fermer la modal en retirant la classe 'active' de l'overlay seulement
                        const overlay = document.getElementById(`canvas-${currentModalCategory}-modal-overlay`);

                        if (overlay) {
                            const wasActive = overlay.classList.contains('active');

                            if (wasActive) {
                                overlay.classList.remove('active');
                                document.body.classList.remove('canvas-modal-open');
                            }
                        } else {
                            console.error('❌ [CLOSE MODAL] Overlay NON trouvé pour:', currentModalCategory);
                        }

                        currentModalCategory = null;
                    }

                    // Sauvegarder les paramètres
                    function saveModalSettings() {
                        modalSettingsManager.saveModalSettings();
                    }

                    // Initialisation : fermer toutes les modales au départ
                    document.addEventListener('DOMContentLoaded', function() {
                        const allOverlays = document.querySelectorAll('.canvas-modal-overlay');
                        allOverlays.forEach(overlay => {
                            overlay.classList.remove('active');
                        });
                        document.body.classList.remove('canvas-modal-open');
                    });

                    // Écouter l'événement de sauvegarde globale pour mettre à jour les indicateurs
                    document.addEventListener('pdfBuilderSettingsSaved', function(event) {

                        // Mettre à jour l'indicateur de la bibliothèque de templates
                        const templateLibraryCheckbox = document.getElementById('template_library_enabled');
                        const templateLibraryIndicator = document.getElementById('template-library-indicator');

                        if (templateLibraryCheckbox && templateLibraryIndicator) {
                            const isEnabled = templateLibraryCheckbox.checked;
                            templateLibraryIndicator.textContent = isEnabled ? 'ACTIF' : 'INACTIF';
                            templateLibraryIndicator.style.background = isEnabled ? '#28a745' : '#dc3545';
                        }
                    });
                    document.addEventListener('click', function(e) {
                        // Bouton de configuration d'une carte
                        if (e.target.closest('.canvas-configure-btn')) {
                            e.preventDefault();
                            const card = e.target.closest('.canvas-card');
                            if (card && card.dataset.category) {
                                openModal(card.dataset.category);
                            }
                            return;
                        }

                        // Bouton de fermeture
                        if (e.target.closest('.canvas-modal-close') || e.target.closest('.canvas-modal-cancel')) {
                            closeModal();
                            return;
                        }

                        // Clic sur l'overlay (backdrop)
                        if (e.target.classList.contains('canvas-modal-overlay') || e.target.classList.contains('modal-backdrop')) {
                            closeModal();
                            return;
                        }

                        // Bouton de sauvegarde
                        if (e.target.closest('.canvas-modal-save')) {
                            e.preventDefault();
                            saveModalSettings();
                            return;
                        }
                    });

                    // Fermeture avec Échap
                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape' && currentModalCategory) {
                            const modalId = `canvas-${currentModalCategory}-modal`;
                            const modal = document.getElementById(modalId);
                            const fullscreenModal = document.getElementById(modalId + '-fullscreen');

                            // Vérifier si une modale est ouverte (originale ou fullscreen)
                            if ((modal && modal.style.display !== 'none') || fullscreenModal) {
                                closeModal();
                            }
                        }

                        // Raccourci pour le monitoring (Ctrl+Shift+M)
                        if (e.ctrlKey && e.shiftKey && e.key === 'M') {
                            e.preventDefault();
                            modalMonitoring.showDashboard();
                        }
                    });

                    // ===========================================
                    // ===========================================
                    // FONCTIONS GLOBALES DE MONITORING

                    // Fonction globale pour accéder au monitoring depuis la console
                    window.pdfBuilderMonitoring = {
                        showDashboard: () => modalMonitoring.showDashboard(),
                        getMetrics: () => modalMonitoring.getMetrics(),
                        getReport: () => modalMonitoring.generateReport(),
                        clearHistory: () => {
                            modalMonitoring.history = [];
                            localStorage.removeItem('pdfBuilderMonitoring');
                        },
                        exportData: () => {
                            const data = {
                                metrics: modalMonitoring.getMetrics(),
                                report: modalMonitoring.generateReport(),
                                timestamp: new Date().toISOString()
                            };
                            return data;
                        }
                    };

                    // ===========================================
                    // SYNCHRONISATION DES TOGGLES AVEC CHAMPS CACHÉS
                    // ===========================================

                    // Fonction pour synchroniser les champs avec les champs cachés
                    function syncFieldsWithHiddenFields() {
                        // Écouter tous les changements sur les champs canvas
                        document.addEventListener('change', function(e) {
                            const target = e.target;
                            const fieldName = target.name || target.getAttribute('data-field');

                            if (fieldName && fieldName.startsWith('pdf_builder_canvas_canvas_')) {
                                // Trouver le champ caché correspondant
                                const hiddenField = document.querySelector(`input[type="hidden"][name="${fieldName}"]`);

                                if (hiddenField) {
                                    let newValue;

                                    // Gérer différents types de champs
                                    if (target.type === 'checkbox' || target.classList.contains('toggle-checkbox')) {
                                        newValue = target.checked ? '1' : '0';
                                    } else if (target.type === 'number') {
                                        newValue = parseFloat(target.value) || 0;
                                        newValue = newValue.toString();
                                    } else {
                                        newValue = target.value;
                                    }

                                    hiddenField.value = newValue;
                                }
                            }
                        });

                        // Synchronisation bidirectionnelle: mettre à jour les champs depuis les cachés lors du focus
                        document.addEventListener('focus', function(e) {
                            const target = e.target;
                            const fieldName = target.name || target.getAttribute('data-field');

                            if (fieldName && fieldName.startsWith('pdf_builder_canvas_canvas_')) {
                                const hiddenField = document.querySelector(`input[type="hidden"][name="${fieldName}"]`);
                                if (hiddenField && hiddenField.value !== target.value) {
                                    if (target.type === 'checkbox' || target.classList.contains('toggle-checkbox')) {
                                        target.checked = hiddenField.value === '1';
                                    } else {
                                        target.value = hiddenField.value;
                                    }
                                }
                            }
                        });
                    }

                    // Initialiser la synchronisation
                    syncFieldsWithHiddenFields();

                    // === DIAGNOSTIC COMPLET DE L'ONGLET CANVAS ===
                    function runCanvasDiagnostic() {
                        const results = {
                            cards: 0,
                            buttons: 0,
                            modals: 0,
                            hiddenFields: 0,
                            previewElements: 0,
                            issues: []
                        };

                        // 1. Vérifier les cartes
                        const cards = document.querySelectorAll('.canvas-card');
                        results.cards = cards.length;

                        cards.forEach((card, index) => {
                            const category = card.dataset.category;
                            const button = card.querySelector('.canvas-configure-btn');
                            if (!category) results.issues.push(`Carte ${index}: pas de data-category`);
                            if (!button) results.issues.push(`Carte ${index} (${category}): pas de bouton configurer`);
                            else results.buttons++;
                        });

                        // 2. Vérifier les overlays (modals canvas)
                        const overlayIds = ['canvas-dimensions-modal-overlay', 'canvas-apparence-modal-overlay', 'canvas-grille-modal-overlay',
                                          'canvas-zoom-modal-overlay', 'canvas-interactions-modal-overlay', 'canvas-export-modal-overlay',
                                          'canvas-performance-modal-overlay', 'canvas-debug-modal-overlay'];

                        overlayIds.forEach(overlayId => {
                            const overlay = document.getElementById(overlayId);
                            if (overlay) results.modals++;
                            else results.issues.push(`Overlay manquant: ${overlayId}`);
                        });

                        // 3. Vérifier les champs cachés
                        const hiddenFields = document.querySelectorAll('input[type="hidden"][name^="pdf_builder_canvas_canvas_"]');
                        results.hiddenFields = hiddenFields.length;

                        // 4. Vérifier les éléments de preview
                        const previewElements = ['card-canvas-width', 'card-canvas-height', 'card-canvas-dpi',
                                               'card-bg-preview', 'card-border-preview', 'card-grid-preview',
                                               'card-zoom-preview', 'card-perf-preview'];

                        previewElements.forEach(id => {
                            const el = document.getElementById(id);
                            if (el) results.previewElements++;
                            else results.issues.push(`Élément preview manquant: ${id}`);
                        });

                        // 5. Vérifier formGenerator
                        if (typeof formGenerator === 'undefined') {
                            results.issues.push('formGenerator non défini');
                        } else {
                            if (!formGenerator.generateModalHTML) results.issues.push('formGenerator.generateModalHTML manquant');
                        }

                        // 6. Vérifier modalSettingsManager
                        if (typeof modalSettingsManager === 'undefined') {
                            results.issues.push('modalSettingsManager non défini');
                        }

                        return results;
                    }

                    // Lancer le diagnostic automatiquement
                    setTimeout(runCanvasDiagnostic, 1000);

                })();

                // ==========================================
                // CHARGEMENT DU SYSTÈME DE MONITORING
                // ==========================================

                <?php
                // Vérifier si le fichier JS existe et logger en PHP
                $plugin_root = dirname(__DIR__, 4);
                $file_path = $plugin_root . '/resources/assets/js/canvas-card-monitor.js';
                if (!file_exists($file_path)) {
                    error_log('PHP LOG: canvas-card-monitor.js not found at ' . $file_path);
                } else {
                    error_log('PHP LOG: canvas-card-monitor.js exists at ' . $file_path . ', size: ' . filesize($file_path));
                }
                ?>

                // Fonction pour charger le script de monitoring
                function loadCanvasCardMonitor() {
                    return new Promise((resolve, reject) => {
                        const scriptUrl = '<?php echo plugins_url('wp-pdf-builder-pro/resources/assets/js/canvas-card-monitor.js') . "?v=" . time(); ?>';
                        const script = document.createElement('script');
                        script.src = scriptUrl;
                        script.onload = () => {
                            resolve();
                        };
                        script.onerror = (error) => {
                            console.error('❌ [MONITORING] Erreur de chargement du système de monitoring:', error);
                            reject(error);
                        };
                        document.head.appendChild(script);
                    });
                }

                // Initialisation dynamique des previews désactivée - previews restent statiques
                // initializeCanvasPreviews();
                */

            })();

            </script>


    </div> <!-- Fermeture de settings-content -->

            <!-- Inclusion des modales Canvas -->
            <?php require_once __DIR__ . '/settings-modals.php'; ?>

</section> <!-- Fermeture de settings-section contenu-settings -->

