<?php
    /**
     * PDF Builder Pro - Content Settings Tab
     * Canvas and design configuration settings
     * Updated: 2025-12-03
     */

    // require_once __DIR__ . '/settings-helpers.php'; // REMOVED - settings-helpers.php deleted

    echo "<!-- TEST: settings-contenu.php loaded - VERSION DIRECTE 2025-12-12 -->";

    $settings = get_option('pdf_builder_settings', array());

    // Fonction helper pour récupérer les valeurs Canvas depuis les options individuelles
    function get_canvas_option_contenu($key, $default = '') {
        $option_key = 'pdf_builder_' . $key;
        // Forcer la lecture directe depuis la base de données en contournant le cache
        global $wpdb;
        $value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", $option_key));

        // DEBUG: Vérifier si l'option existe vraiment
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name = %s", $option_key));

        if ($value === null) {
            $value = $default;
            error_log("[PDF Builder] PAGE_LOAD - {$key}: OPTION_NOT_FOUND - using default '{$default}' - KEY: {$option_key}");
        } else {
            error_log("[PDF Builder] PAGE_LOAD - {$key}: FOUND_DB_VALUE '{$value}' - KEY: {$option_key} - EXISTS: {$exists}");
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
                <h3 style="display: flex; justify-content: flex-start; align-items: center;">
                    <span>
                        🎨 Canvas
                    </span>
                    <button type="button" id="reset-canvas-settings" class="button button-secondary" style="font-size: 12px; padding: 4px 8px; margin-left: auto;" title="Réinitialiser tous les paramètres Canvas aux valeurs par défaut">
                        🔄 Réinitialiser
                    </button>
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
                        <!-- Carte Affichage (fusion Dimensions + Apparence) -->
                        <article class="canvas-card" data-category="affichage">
                            <header class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">🎨</span>
                                </div>
                                <h4>Affichage & Dimensions</h4>
                            </header>
                            <main class="canvas-card-content">
                                <p>Configurez les dimensions, le format, les couleurs et l'apparence générale du canvas.</p>
                            </main>
                            <aside class="canvas-card-preview">
                                <div class="preview-format">
                                    <div >
                                        <span id="card-canvas-width"><?php echo esc_html(get_canvas_option_contenu('canvas_width', '794')); ?></span>×
                                        <span id="card-canvas-height"><?php echo esc_html(get_canvas_option_contenu('canvas_height', '1123')); ?></span>px
                                        <div style="font-size: 10px; color: #666; margin-top: 5px;">
                                            <?php echo esc_html(get_canvas_option_contenu('canvas_bg_color', '#ffffff')); ?> • <?php echo esc_html(get_canvas_option_contenu('canvas_border_width', '1')); ?>px
                                        </div>
                                    </div>
                                    <span class="preview-size" id="card-canvas-dpi">
                                        <?php
                                        $width = intval(get_canvas_option_contenu('canvas_width', '794'));
                                        $height = intval(get_canvas_option_contenu('canvas_height', '1123'));
                                        $dpi = intval(get_canvas_option_contenu('canvas_dpi', '96'));
                                        $format = get_canvas_option_contenu('canvas_format', 'A4');

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
                                        <div class="apparence-background" style="background-color: <?php echo esc_attr(get_canvas_option_contenu('canvas_bg_color', '#ffffff')); ?>;"></div>
                                        <!-- Bordure -->
                                        <div class="apparence-border" style="border: <?php echo esc_attr(get_canvas_option_contenu('canvas_border_width', '1')); ?>px solid <?php echo esc_attr(get_canvas_option_contenu('canvas_border_color', '#cccccc')); ?>;"></div>
                                        <!-- Ombre -->
                                        <?php if (get_canvas_option_contenu('canvas_shadow_enabled', '0') === '1'): ?>
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
                                        <span class="legend-item"><?php echo (get_canvas_option_contenu('canvas_shadow_enabled', '0') === '1') ? '🌑' : '☀️'; ?> Ombre</span>
                                    </div>
                        </article>

                        <!-- Carte Navigation (fusion Grille + Zoom) -->
                        <article class="canvas-card" data-category="navigation">
                            <header class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">🧭</span>
                                </div>
                                <h4>Navigation & Grille</h4>
                            </header>
                            <main class="canvas-card-content">
                                <p>Configurez la grille, les guides, le zoom et les options de navigation.</p>
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
                                        <span class="legend-item">📐 Grille <?php echo intval(get_canvas_option_contenu('canvas_grid_size', '20')); ?>px</span>
                                        <span class="legend-item">🔍 Zoom <?php echo intval(get_canvas_option_contenu('canvas_zoom_default', '100')); ?>%</span>
                                        <span class="snap-indicator"><?php echo (get_canvas_option_contenu('canvas_snap_to_grid', '1') === '1') ? '🔗' : '🔓'; ?> Snap</span>
                                    </div>
                                </div>
                            </aside>
                            <footer class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>🧭</span> Configurer
                                </button>
                            </footer>
                        </article>
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

                        <!-- Carte Comportement (fusion Interactions + Export) -->
                        <article class="canvas-card" data-category="comportement">
                            <header class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">🎮</span>
                                </div>
                                <h4>Comportement & Export</h4>
                            </header>
                            <main class="canvas-card-content">
                                <p>Configurez les interactions, la sélection et les options d'export.</p>
                            </main>
                            <aside class="canvas-card-preview">
                                <div class="interactions-preview-container">
                                    <!-- Canvas miniature avec éléments -->
                                    <div class="mini-canvas">
                                        <!-- Éléments sur le canvas -->
                                        <div class="mini-element shape-element selected" style="top: 40px; left: 15px; width: 32px; height: 22px;" title="Élément sélectionné">
                                            <div class="mini-element-content">□</div>
                                        </div>
                                        <div class="mini-element image-element" style="top: 18px; left: 75px; width: 28px; height: 28px;" title="Élément image">
                                            <div class="mini-element-content">🖼</div>
                                        </div>
                                    </div>

                                    <!-- Contrôles -->
                                    <div class="interactions-controls">
                                        <div class="selection-mode-indicator">
                                            <span class="mode-icon active" title="Sélection rectangle">▭</span>
                                            <span class="mode-icon" title="Sélection lasso">🪢</span>
                                        </div>
                                        <div class="interaction-status">
                                            <span class="status-indicator selecting">Sélection active</span>
                                            <div class="keyboard-status" title="Raccourcis clavier">
                                                <span class="keyboard-icon">⌨️</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Export formats -->
                                    <div class="export-formats">
                                        <span class="format-badge active"><?php echo esc_html(strtoupper(get_canvas_option_contenu('canvas_export_format', 'pdf'))); ?></span>
                                        <span class="format-badge">Qualité <?php echo intval(get_canvas_option_contenu('canvas_export_quality', '90')); ?>%</span>
                                    </div>
                                </div>
                            </aside>
                            <footer class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>🎮</span> Configurer
                                </button>
                            </footer>
                        </article>

                        <!-- Carte Système (fusion Performance + Debug) -->
                        <article class="canvas-card" data-category="systeme">
                            <header class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">⚙️</span>
                                </div>
                                <h4>Système & Performance</h4>
                            </header>
                            <main class="canvas-card-content">
                                <p>Configurez les performances, le débogage et les paramètres système.</p>
                            </main>
                            <aside class="canvas-card-preview">
                                <div class="system-preview-container">
                                    <div class="system-metrics">
                                        <div class="metric-item">
                                            <span class="metric-label">FPS</span>
                                            <span class="metric-value"><?php echo esc_html(get_canvas_option_contenu('canvas_fps_target', '60')); ?></span>
                                        </div>
                                        <div class="metric-item">
                                            <span class="metric-label">RAM</span>
                                            <span class="metric-value"><?php echo esc_html(get_canvas_option_contenu('canvas_memory_limit_js', '50')); ?>MB</span>
                                        </div>
                                        <div class="metric-item">
                                            <span class="metric-label">Debug</span>
                                            <span class="metric-value"><?php echo (get_canvas_option_contenu('canvas_debug_enabled', '0') === '1') ? 'ON' : 'OFF'; ?></span>
                                        </div>
                                    </div>
                                    <div class="system-status">
                                        <div class="status-indicator">
                                            <span class="status-dot <?php echo (get_canvas_option_contenu('canvas_performance_monitoring', '0') === '1') ? 'active' : ''; ?>"></span>
                                            <span class="status-text">Monitoring</span>
                                        </div>
                                    </div>
                                </div>
                            </aside>
                            <footer class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>⚙️</span> Configurer
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
.canvas-modal-apply {
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

.canvas-modal-apply {
    background: #667eea;
    color: white;
}

.canvas-modal-apply:hover {
    background: #5a67d8;
}

.canvas-modal-apply:disabled {
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
.canvas-modal-apply.loading {
    position: relative;
    color: transparent;
}

.canvas-modal-apply.loading::after {
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

/* Indicateurs de valeur */
.value-indicator {
    font-size: 12px;
    font-style: italic;
    margin-left: 8px;
}

.value-default {
    border-left: 3px solid #666;
}

.value-custom {
    border-left: 3px solid #007cba;
}

.value-cached {
    border-left: 3px solid #f39c12;
}
            </style>
            <?php
                // $plugin_dir = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
                // $css_url = plugins_url('resources/assets/css/canvas-modals.css', $plugin_dir . '/pdf-builder-pro.php');
                // wp_enqueue_style('pdf-builder-canvas-modals', $css_url, array(), '1.0.0');
            ?>

<script>
                // Force cache clear
                // Temporarily commented out service worker code to test syntax error
                // if ('serviceWorker' in navigator) {
                //     navigator.serviceWorker.getRegistrations().then(function(registrations) {
                //         for(let registration of registrations) {
                //             registration.unregister();
                //         }
                //     });
                // }
                console.log('Cache cleared by PDF Builder');

                // Gestionnaire des modales Canvas
                (function() {
                    'use strict';

                    // Valeurs par défaut pour les paramètres Canvas (injectées depuis PHP)
                    // <?php
                    // $canvas_defaults_json = json_encode($default_canvas_options, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    // echo "const CANVAS_DEFAULT_VALUES = $canvas_defaults_json;";
                    // ?>

                    // Temporary: Use hardcoded values
                    // const CANVAS_DEFAULT_VALUES = {
                    //     'pdf_builder_canvas_width': '794',
                    //     'pdf_builder_canvas_height': '1123',
                    //     'pdf_builder_canvas_dpi': '96',
                    //     'pdf_builder_canvas_format': 'A4',
                    //     'pdf_builder_canvas_bg_color': '#ffffff',
                    //     'pdf_builder_canvas_border_color': '#cccccc',
                    //     'pdf_builder_canvas_border_width': '1',
                    //     'pdf_builder_canvas_container_bg_color': '#f8f9fa',
                    //     'pdf_builder_canvas_shadow_enabled': '0',
                    //     'pdf_builder_canvas_grid_enabled': '1',
                    //     'pdf_builder_canvas_grid_size': '20',
                    //     'pdf_builder_canvas_guides_enabled': '1',
                    //     'pdf_builder_canvas_snap_to_grid': '1',
                    //     'pdf_builder_canvas_zoom_min': '25',
                    //     'pdf_builder_canvas_zoom_max': '500',
                    //     'pdf_builder_canvas_zoom_default': '100',
                    //     'pdf_builder_canvas_zoom_step': '25',
                    //     'pdf_builder_canvas_export_quality': '90',
                    //     'pdf_builder_canvas_export_format': 'png',
                    //     'pdf_builder_canvas_export_transparent': '0',
                    //     'pdf_builder_canvas_drag_enabled': '1',
                    //     'pdf_builder_canvas_resize_enabled': '1',
                    //     'pdf_builder_canvas_rotate_enabled': '1',
                    //     'pdf_builder_canvas_multi_select': '1',
                    //     'pdf_builder_canvas_selection_mode': 'single',
                    //     'pdf_builder_canvas_keyboard_shortcuts': '1',
                    //     'pdf_builder_canvas_fps_target': '60',
                    //     'pdf_builder_canvas_memory_limit_js': '50',
                    //     'pdf_builder_canvas_response_timeout': '5000',
                    //     'pdf_builder_canvas_lazy_loading_editor': '1',
                    //     'pdf_builder_canvas_preload_critical': '1',
                    //     'pdf_builder_canvas_lazy_loading_plugin': '1',
                    //     'pdf_builder_canvas_debug_enabled': '0',
                    //     'pdf_builder_canvas_performance_monitoring': '0',
                    //     'pdf_builder_canvas_error_reporting': '0',
                    //     'pdf_builder_canvas_memory_limit_php': '128'
                    // };

                    console.log('[PDF Builder] 🚀 MODALS_SYSTEM_v2.1 - Initializing Canvas modals system (LIGHT VERSION)');

                    // Fonction d'initialisation simplifiée
                    function initializeModals(retryCount = 0) {
                        const maxRetries = 3;

                        try {
                            console.log(`[PDF Builder] MODALS_INIT - Attempt ${retryCount + 1}/${maxRetries + 1}`);

                            initializeToggleStates();

                            const modalCategories = ['affichage', 'navigation', 'comportement', 'systeme'];
                            let missingModals = [];

                            modalCategories.forEach(category => {
                                const modalId = `canvas-${category}-modal-overlay`;
                                const modal = document.getElementById(modalId);
                                if (!modal) missingModals.push(modalId);
                            });

                            if (missingModals.length > 0 && retryCount < maxRetries) {
                                setTimeout(() => initializeModals(retryCount + 1), 200);
                                return;
                            }

                            if (missingModals.length > 0) {
                                console.error('Missing modals:', missingModals);
                                alert(`${missingModals.length} modales manquantes.`);
                            }

                            attachEventListeners();
                            console.log('[PDF Builder] MODALS_INIT - Success');

                        } catch (error) {
                            console.error('[PDF Builder] MODALS_INIT - Error:', error);
                        }
                    }

                    // Fonction pour appliquer les paramètres de la modale (simplifiée)
                    function applyModalSettings(category) {
                        console.log('[JS APPLY] ===== STARTING applyModalSettings for category:', category);

                        const modal = document.querySelector(`#canvas-${category}-modal-overlay`);
                        if (!modal) {
                            console.error('[JS APPLY] ❌ Modal not found for category:', category);
                            return;
                        }

                        // Collecter et synchroniser les valeurs
                        const inputs = modal.querySelectorAll('input, select, textarea');
                        let updatedCount = 0;

                        inputs.forEach(input => {
                            if (input.name && input.name.startsWith('pdf_builder_canvas_')) {
                                const hiddenField = document.querySelector(`input[name="pdf_builder_settings[${input.name}]"]`);
                                if (hiddenField) {
                                    const newValue = input.type === 'checkbox' ? (input.checked ? '1' : '0') : input.value;
                                    hiddenField.value = newValue;
                                    updatedCount++;
                                }
                            }
                        });

                        console.log(`[JS APPLY] Updated ${updatedCount} fields`);

                        // Fermer la modale et notifier
                        closeModal(`canvas-${category}-modal-overlay`);
                        showNotification('success', `✅ ${updatedCount} paramètres appliqués`);
                    }

                    // Fonction pour attacher tous les event listeners
                    function attachEventListeners() {
                        console.log('[PDF Builder] ATTACH_LISTENERS - Attaching event listeners');

                            // Gestionnaire d'événements pour les boutons de configuration - VERSION SIMPLIFIÉE
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
                                            }
                                        }
                                        return;
                                    }

                                    // Gestionnaire pour fermer les modales
                                    const closeBtn = e.target.closest('.canvas-modal-close, .canvas-modal-cancel');
                                    if (closeBtn) {
                                        e.preventDefault();
                                        const modal = closeBtn.closest('.canvas-modal-overlay');
                                        if (modal) {
                                            closeModal(modal.id);
                                        }
                                        return;
                                    }

                                    // Gestionnaire pour appliquer les paramètres
                                    const applyBtn = e.target.closest('.canvas-modal-apply');
                                    if (applyBtn) {
                                        e.preventDefault();
                                        const category = applyBtn.getAttribute('data-category');
                                        if (category) {
                                            applyModalSettings(category);
                                        }
                                        return;
                                    }

                                    // Gestionnaire pour les clics sur l'overlay (fermer la modale)
                                    if (e.target.classList.contains('canvas-modal-overlay')) {
                                        e.preventDefault();
                                        closeModal(e.target.id);
                                        return;
                                    }

                                    // Gestionnaire pour réinitialiser les paramètres Canvas
                                    const resetBtn = e.target.closest('#reset-canvas-settings');
                                    if (resetBtn) {
                                        e.preventDefault();
                                        if (confirm('Êtes-vous sûr de vouloir réinitialiser tous les paramètres Canvas aux valeurs par défaut ?')) {
                                            resetCanvasSettings();
                                        }
                                        return;
                                    }

                            // Gestionnaire pour la touche Échap
                            document.addEventListener('keydown', function(e) {
                                if (e.key === 'Escape') {
                                    console.log('[PDF Builder] ESC_KEY - Escape key pressed');

                                    // Fermer toutes les modales ouvertes
                                    const openModals = document.querySelectorAll('.canvas-modal-overlay[style*="display: flex"]');
                                    openModals.forEach(modal => {
                                        closeModal(modal.id);
                                    });
                                }
                            });
                                openModals.forEach(modal => {
                                    closeModal(modal);
                                });
                            }
                        });

                        console.log('[PDF Builder] ATTACH_LISTENERS - Event listeners attached successfully');
                    }

                    // Appeler l'initialisation quand le DOM est prêt et les modals sont chargées
                    function initWhenReady() {
                        if (document.readyState === 'loading') {
                            document.addEventListener('DOMContentLoaded', () => waitForModalsAndInitialize(0));
                        } else {
                            // DOM déjà chargé, attendre les modals
                            waitForModalsAndInitialize(0);
                        }
                    }

                    // Fonction pour attendre que les modals soient chargées
                    function waitForModalsAndInitialize(attempt = 0) {
                        const maxAttempts = 10;
                        const modalIds = [
                            'canvas-affichage-modal-overlay',
                            'canvas-navigation-modal-overlay',
                            'canvas-comportement-modal-overlay',
                            'canvas-systeme-modal-overlay'
                        ];

                        const allModalsLoaded = modalIds.every(id => document.getElementById(id) !== null);

                        if (allModalsLoaded) {
                            console.log('[PDF Builder] MODALS_READY - All modals loaded, initializing...');
                            initializeModals(0);
                        } else if (attempt < maxAttempts) {
                            console.log(`[PDF Builder] MODALS_WAIT - Waiting for modals (attempt ${attempt + 1}/${maxAttempts})`);
                            setTimeout(() => waitForModalsAndInitialize(attempt + 1), 100);
                        } else {
                            console.error('[PDF Builder] MODALS_TIMEOUT - Modals failed to load after maximum attempts');
                            // Essayer quand même d'initialiser avec ce qui est disponible
                            initializeModals(0);
                        }
                    }

                    initWhenReady();

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

                            // Accessibilité - utiliser inert au lieu d'aria-hidden
                            modal.removeAttribute('inert');
                            modal.focus();

                            console.log(`[PDF Builder] OPEN_MODAL - Modal opened successfully: ${modalId}`);

                        } catch (error) {
                            console.error(`[PDF Builder] OPEN_MODAL - Error opening modal ${modalId}:`, error);
                            alert(`Erreur lors de l'ouverture de la modale: ${error.message}`);
                        }
                    }

                    // Fonction pour initialiser l'état des toggles
                    function initializeToggleStates() {
                        console.log('[PDF Builder] TOGGLE_INIT - Initializing toggle states');

                        // Parcourir tous les toggles existants
                        const allToggles = document.querySelectorAll('.toggle-switch input[type="checkbox"]');
                        allToggles.forEach(checkbox => {
                            const toggleSwitch = checkbox.closest('.toggle-switch');
                            if (toggleSwitch) {
                                if (checkbox.checked) {
                                    toggleSwitch.classList.add('checked');
                                } else {
                                    toggleSwitch.classList.remove('checked');
                                }
                                console.log(`[PDF Builder] TOGGLE_INIT - ${checkbox.id || checkbox.name}: checked=${checkbox.checked}`);
                            }
                        });

                        console.log(`[PDF Builder] TOGGLE_INIT - Initialized ${allToggles.length} toggles`);
                    }
                        console.log('[PDF Builder] UPDATE_CARDS - Updating canvas cards display');

                        try {
                            // Mettre à jour les indicateurs de statut sur les cartes
                            const cards = document.querySelectorAll('.canvas-card');
                            cards.forEach(card => {
                                const category = card.getAttribute('data-category');
                                if (category) {
                                    // Marquer comme valeurs par défaut
                                    const statusIndicator = card.querySelector('.canvas-status');
                                    if (statusIndicator) {
                                        statusIndicator.textContent = 'Défaut';
                                        statusIndicator.className = 'canvas-status status-default';
                                    }

                                    console.log(`[PDF Builder] UPDATE_CARDS - Updated card for category: ${category}`);
                                }
                            });

                            // Forcer la mise à jour des valeurs dans toutes les modales ouvertes
                            const openModals = document.querySelectorAll('.canvas-modal-overlay[style*="display: flex"]');
                            openModals.forEach(modal => {
                                const category = modal.id.replace('canvas-', '').replace('-modal-overlay', '');
                                if (category) {
                                    updateModalValues(category);
                                }
                            });

                        } catch (error) {
                            console.error('[PDF Builder] UPDATE_CARDS - Error updating cards display:', error);
                        }
                    }

                    // Fonction pour mettre à jour les valeurs d'une modale avec les paramètres actuels
                    // Fonction simplifiée pour mettre à jour les valeurs de la modale
                    function updateModalValues(category) {
                        const modal = document.querySelector(`#canvas-${category}-modal-overlay`);
                        if (!modal) return;

                        // Mettre à jour tous les champs de la modale depuis les champs cachés
                        const inputs = modal.querySelectorAll('input, select, textarea');
                        inputs.forEach(input => {
                            if (input.name && input.name.startsWith('pdf_builder_canvas_')) {
                                const hiddenField = document.querySelector(`input[name="pdf_builder_settings[${input.name}]"]`);
                                if (hiddenField && hiddenField.value) {
                                    if (input.type === 'checkbox') {
                                        input.checked = hiddenField.value === '1';
                                        const toggleSwitch = input.closest('.toggle-switch');
                                        if (toggleSwitch) {
                                            toggleSwitch.classList.toggle('checked', input.checked);
                                        }
                                    } else {
                                        input.value = hiddenField.value;
                                        if (input.tagName === 'SELECT') {
                                            input.querySelectorAll('option').forEach(opt => {
                                                opt.selected = opt.value === hiddenField.value;
                                            });
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // Fonction pour fermer une modale - VERSION RENFORCÉE
                    function closeModal(modalOrId) {
                        try {
                            let modal;
                            let modalId;

                            // Déterminer si c'est un ID ou un élément
                            if (typeof modalOrId === 'string') {
                                modalId = modalOrId;
                                modal = document.getElementById(modalId);
                            } else if (modalOrId && modalOrId.nodeType === 1) {
                                // C'est un élément DOM
                                modal = modalOrId;
                                modalId = modal.id || 'unknown-modal';
                            } else {
                                console.error('[PDF Builder] CLOSE_MODAL - Invalid parameter:', modalOrId);
                                return;
                            }

                            console.log(`[PDF Builder] CLOSE_MODAL - Attempting to close: ${modalId}`);

                            if (!modal) {
                                console.warn(`[PDF Builder] CLOSE_MODAL - Modal element not found: ${modalId}`);
                                return;
                            }

                            // Masquer la modale
                            modal.style.display = 'none';
                            document.body.style.overflow = '';

                            // Accessibilité - déplacer le focus et utiliser inert
                            document.body.focus();
                            modal.setAttribute('inert', '');

                            console.log(`[PDF Builder] CLOSE_MODAL - Modal closed successfully: ${modalId}`);

                        } catch (error) {
                            console.error(`[PDF Builder] CLOSE_MODAL - Error closing modal:`, error);
                        }
                    }

                    // Fonction helper pour les notifications avec fallback
                    function showNotification(type, message, options) {
                        console.log(`[PDF Builder] NOTIFICATION_HELPER - Attempting to show ${type} notification:`, message);

                        if (type === 'success' && typeof showSuccessNotification === 'function') {
                            console.log('[PDF Builder] NOTIFICATION_HELPER - Using showSuccessNotification');
                            showSuccessNotification(message, options);
                        } else if (type === 'error' && typeof showErrorNotification === 'function') {
                            console.log('[PDF Builder] NOTIFICATION_HELPER - Using showErrorNotification');
                            showErrorNotification(message, options);
                        } else {
                            console.log('[PDF Builder] NOTIFICATION_HELPER - Using alert fallback');
                            alert(message);
                        }
                    }

                    // Fonction pour réinitialiser tous les paramètres Canvas aux valeurs par défaut
                    function resetCanvasSettings() {
                        console.log('[PDF Builder] RESET_CANVAS - Function called, starting Canvas settings reset');

                        try {
                            // Valeurs par défaut pour tous les paramètres Canvas
                            const defaultValues = {
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
                                'pdf_builder_canvas_zoom_min': '0.1',
                                'pdf_builder_canvas_zoom_max': '5',
                                'pdf_builder_canvas_zoom_default': '1',
                                'pdf_builder_canvas_zoom_step': '0.1',
                                'pdf_builder_canvas_export_quality': '90',
                                'pdf_builder_canvas_export_format': 'pdf',
                                'pdf_builder_canvas_export_transparent': '0',
                                'pdf_builder_canvas_drag_enabled': '1',
                                'pdf_builder_canvas_resize_enabled': '1',
                                'pdf_builder_canvas_rotate_enabled': '1',
                                'pdf_builder_canvas_multi_select': '1',
                                'pdf_builder_canvas_selection_mode': 'single',
                                'pdf_builder_canvas_keyboard_shortcuts': '1',
                                'pdf_builder_canvas_fps_target': '60',
                                'pdf_builder_canvas_memory_limit_js': '128',
                                'pdf_builder_canvas_response_timeout': '5000',
                                'pdf_builder_canvas_lazy_loading_editor': '1',
                                'pdf_builder_canvas_preload_critical': '1',
                                'pdf_builder_canvas_lazy_loading_plugin': '1',
                                'pdf_builder_canvas_debug_enabled': '0',
                                'pdf_builder_canvas_performance_monitoring': '1',
                                'pdf_builder_canvas_error_reporting': '1',
                                'pdf_builder_canvas_memory_limit_php': '256'
                            };

                            // Réinitialiser les champs cachés
                            Object.keys(defaultValues).forEach(key => {
                                const hiddenField = document.querySelector(`input[name="pdf_builder_settings[${key}]"]`);
                                if (hiddenField) {
                                    hiddenField.value = defaultValues[key];
                                    console.log(`[PDF Builder] RESET_CANVAS - Reset ${key} to ${defaultValues[key]}`);
                                }
                            });

                            // AJAX supprimé - réinitialisation simplifiée côté client uniquement
                            console.log('[PDF Builder] RESET_CANVAS - Client-side reset completed');

                            // Fermer toutes les modales ouvertes
                            const openModals = document.querySelectorAll('.canvas-modal-overlay[style*="display: flex"]');
                            openModals.forEach(modal => {
                                const modalId = modal.id;
                                closeModal(modalId);
                            });

                            // Mettre à jour l'affichage des cartes avec les nouvelles valeurs
                            updateCanvasCardsDisplay();

                            // Notification de succès
                            showNotification('success', '✅ Tous les paramètres Canvas ont été réinitialisés aux valeurs par défaut (côté client).', {
                                duration: 6000,
                                dismissible: true
                            });

                        } catch (error) {
                            console.error('[PDF Builder] RESET_CANVAS - Error during reset:', error);
                            // Notification d'erreur générale
                            showNotification('error', '❌ Erreur lors de la réinitialisation des paramètres.', {
                                duration: 8000,
                                dismissible: true
                            });
                        }
                    }

                    console.log('Modal manager initialized');

                    console.log('SYSTEM_INIT: Canvas modals system initialized successfully');
                })();
            </script>

            <!-- Inclusion des modales Canvas -->
            <?php require_once __DIR__ . '/settings-modals.php'; ?>

</section> <!-- Fermeture de settings-section contenu-settings -->

