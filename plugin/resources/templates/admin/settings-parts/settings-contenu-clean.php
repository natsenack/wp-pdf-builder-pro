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
</script>


    </div> <!-- Fermeture de settings-content -->

            <!-- Inclusion des modales Canvas -->
            <?php require_once __DIR__ . '/settings-modals.php'; ?>

</section> <!-- Fermeture de settings-section contenu-settings -->

