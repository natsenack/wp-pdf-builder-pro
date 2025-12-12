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
                                            DEBUG: width=<?php echo get_canvas_option_contenu('canvas_width', '794'); ?>, height=<?php echo get_canvas_option_contenu('canvas_height', '1123'); ?>
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

                        <!-- Carte Navigation (fusion Grille + Zoom) -->
                        <article class="canvas-card" data-category="navigation">
                            <header class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">🧭</span>
                                </div>
                                <h4>Navigation & Zoom</h4>
                            </header>
                            <main class="canvas-card-content">
                                <p>Configurez la grille, les guides, le zoom et les options de navigation du canvas.</p>
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

                        <!-- Carte Comportement (fusion Interactions + Export) -->
                        <article class="canvas-card" data-category="comportement">
                            <header class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">�</span>
                                </div>
                                <h4>Comportement & Export</h4>
                            </header>
                            <main class="canvas-card-content">
                                <p>Configurez les interactions, la sélection, les raccourcis et les options d'export du canvas.</p>
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

                        <!-- Carte Système (fusion Performance + Debug) -->
                        <article class="canvas-card" data-category="systeme">
                            <header class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">⚙️</span>
                                </div>
                                <h4>Performance & Système</h4>
                            </header>
                            <main class="canvas-card-content">
                                <p>Optimisez les performances, la mémoire et configurez les options de debug et monitoring.</p>
                            </main>
                            <aside class="canvas-card-preview">
                                <div class="performance-preview-container">
                                    <div class="performance-metrics">
                                        <div class="metric-item">
                                            <span class="metric-label">FPS</span>
                                            <span id="card-perf-preview" class="metric-value"><?php echo esc_html(get_canvas_option_contenu('canvas_fps_target', '60')); ?></span>
                                        </div>
                                        <div class="metric-item">
                                            <span class="metric-label">RAM JS</span>
                                            <span class="metric-value"><?php echo esc_html(get_canvas_option_contenu('canvas_memory_limit_js', '50')); ?>MB</span>
                                        </div>
                                        <div class="metric-item">
                                            <span class="metric-label">RAM PHP</span>
                                            <span class="metric-value"><?php echo esc_html(get_canvas_option_contenu('canvas_memory_limit_php', '128')); ?>MB</span>
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
                (function() {
                    'use strict';

                    console.log('[PDF Builder] Canvas Modals System - Simple Version v3.0');

                    // Configuration des modals
                    var modalConfig = {
                        'affichage': 'canvas-affichage-modal-overlay',
                        'navigation': 'canvas-navigation-modal-overlay',
                        'comportement': 'canvas-comportement-modal-overlay',
                        'systeme': 'canvas-systeme-modal-overlay'
                    };

                    // Fonction simple pour ouvrir une modal
                    function openModal(category) {
                        var modalId = modalConfig[category];
                        if (!modalId) {
                            console.error('[PDF Builder] Unknown category:', category);
                            return;
                        }

                        var modal = document.getElementById(modalId);
                        if (modal) {
                            modal.style.display = 'flex';
                            document.body.style.overflow = 'hidden';
                            console.log('[PDF Builder] Opened modal:', modalId);
                        } else {
                            console.error('[PDF Builder] Modal not found:', modalId);
                        }
                    }

                    // Fonction simple pour fermer une modal
                    function closeModal(modalElement) {
                        if (modalElement) {
                            modalElement.style.display = 'none';
                            document.body.style.overflow = '';
                            console.log('[PDF Builder] Closed modal');
                        }
                    }

                    // Appliquer les paramètres d'une modal
                    function applyModalSettings(category) {
                        var modalId = modalConfig[category];
                        if (!modalId) return;

                        var modal = document.getElementById(modalId);
                        if (!modal) return;

                        // Collecter et synchroniser les valeurs
                        var inputs = modal.querySelectorAll('input, select, textarea');
                        var updatedCount = 0;

                        inputs.forEach(function(input) {
                            if (input.name && input.name.indexOf('pdf_builder_canvas_') === 0) {
                                var hiddenField = document.querySelector('input[name="pdf_builder_settings[' + input.name + ']"]');
                                if (hiddenField) {
                                    var newValue = input.type === 'checkbox' ? (input.checked ? '1' : '0') : input.value;
                                    hiddenField.value = newValue;
                                    updatedCount++;
                                }
                            }
                        });

                        closeModal(modal);
                        console.log('[PDF Builder] Applied', updatedCount, 'settings for', category);
                    }

                    // Initialisation des événements
                    function initEvents() {
                        console.log('[PDF Builder] Initializing events...');

                        // Boutons de configuration
                        document.addEventListener('click', function(e) {
                            // Ouvrir modal
                            var configBtn = e.target.closest('.canvas-configure-btn');
                            if (configBtn) {
                                e.preventDefault();
                                var card = configBtn.closest('.canvas-card');
                                if (card) {
                                    var category = card.getAttribute('data-category');
                                    if (category && modalConfig[category]) {
                                        openModal(category);
                                    }
                                }
                                return;
                            }

                            // Fermer modal
                            var closeBtn = e.target.closest('.canvas-modal-close, .canvas-modal-cancel');
                            if (closeBtn) {
                                e.preventDefault();
                                var modal = closeBtn.closest('.canvas-modal-overlay');
                                if (modal) {
                                    closeModal(modal);
                                }
                                return;
                            }

                            // Appliquer paramètres
                            var applyBtn = e.target.closest('.canvas-modal-apply');
                            if (applyBtn) {
                                e.preventDefault();
                                var category = applyBtn.getAttribute('data-category');
                                if (category) {
                                    applyModalSettings(category);
                                }
                                return;
                            }

                            // Clic sur overlay
                            if (e.target.classList.contains('canvas-modal-overlay')) {
                                closeModal(e.target);
                                return;
                            }
                        });

                        // Touche Échap
                        document.addEventListener('keydown', function(e) {
                            if (e.key === 'Escape') {
                                var openModals = document.querySelectorAll('.canvas-modal-overlay[style*="display: flex"]');
                                openModals.forEach(function(modal) {
                                    closeModal(modal);
                                });
                            }
                        });

                        console.log('[PDF Builder] Events initialized');
                    }

                    // Initialisation au chargement du DOM
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', initEvents);
                    } else {
                        initEvents();
                    }

                    console.log('[PDF Builder] Canvas modals system ready');
                })();
            </script>

            </script>

            <!-- Inclusion des modales Canvas -->
            <?php require_once __DIR__ . '/settings-modals.php'; ?>

</section> <!-- Fermeture de settings-section contenu-settings -->

