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

    <!-- Inclusion des modales Canvas -->
    <?php require_once __DIR__ . '/settings-modals.php'; ?>

    <div class="settings-content">
<?php
    $settings = get_option('pdf_builder_settings', array());

?>
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
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_allow_portrait]" value="<?php echo esc_attr(get_option('pdf_builder_canvas_allow_portrait', '1')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_allow_landscape]" value="<?php echo esc_attr(get_option('pdf_builder_canvas_allow_landscape', '1')); ?>">

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
                                    <span class="canvas-card-icon">🎯</span>
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



            <!-- CSS pour les modales Canvas - DÉJÀ INCLUS dans pdf-builder-unified.css -->
            <style>
            </style>

            <script>
                (function() {
                    'use strict';

                    console.log('[PDF Builder] Canvas Modals System - Simple Version v3.0');

                    // Initialize window.pdfBuilderCanvasSettings with current DB values
                    window.pdfBuilderCanvasSettings = {
                        width: <?php echo json_encode(get_canvas_option_contenu('canvas_width', '794')); ?>,
                        height: <?php echo json_encode(get_canvas_option_contenu('canvas_height', '1123')); ?>,
                        dpi: <?php echo json_encode(get_canvas_option_contenu('canvas_dpi', '96')); ?>,
                        format: <?php echo json_encode(get_canvas_option_contenu('canvas_format', 'A4')); ?>,
                        bgColor: <?php echo json_encode(get_canvas_option_contenu('canvas_bg_color', '#ffffff')); ?>,
                        borderColor: <?php echo json_encode(get_canvas_option_contenu('canvas_border_color', '#cccccc')); ?>,
                        borderWidth: <?php echo json_encode(get_canvas_option_contenu('canvas_border_width', '1')); ?>,
                        containerBgColor: <?php echo json_encode(get_canvas_option_contenu('canvas_container_bg_color', '#f8f9fa')); ?>,
                        shadowEnabled: <?php echo json_encode(get_canvas_option_contenu('canvas_shadow_enabled', '0') === '1'); ?>,
                        gridEnabled: <?php echo json_encode(get_canvas_option_contenu('canvas_grid_enabled', '1') === '1'); ?>,
                        gridSize: <?php echo json_encode(get_canvas_option_contenu('canvas_grid_size', '20')); ?>,
                        guidesEnabled: <?php echo json_encode(get_canvas_option_contenu('canvas_guides_enabled', '1') === '1'); ?>,
                        snapToGrid: <?php echo json_encode(get_canvas_option_contenu('canvas_snap_to_grid', '1') === '1'); ?>,
                        zoomMin: <?php echo json_encode(get_canvas_option_contenu('canvas_zoom_min', '25')); ?>,
                        zoomMax: <?php echo json_encode(get_canvas_option_contenu('canvas_zoom_max', '500')); ?>,
                        zoomDefault: <?php echo json_encode(get_canvas_option_contenu('canvas_zoom_default', '100')); ?>,
                        zoomStep: <?php echo json_encode(get_canvas_option_contenu('canvas_zoom_step', '25')); ?>,
                        exportQuality: <?php echo json_encode(get_canvas_option_contenu('canvas_export_quality', '90')); ?>,
                        exportFormat: <?php echo json_encode(get_canvas_option_contenu('canvas_export_format', 'png')); ?>,
                        exportTransparent: <?php echo json_encode(get_canvas_option_contenu('canvas_export_transparent', '0') === '1'); ?>,
                        dragEnabled: <?php echo json_encode(get_canvas_option_contenu('canvas_drag_enabled', '1') === '1'); ?>,
                        resizeEnabled: <?php echo json_encode(get_canvas_option_contenu('canvas_resize_enabled', '1') === '1'); ?>,
                        rotateEnabled: <?php echo json_encode(get_canvas_option_contenu('canvas_rotate_enabled', '1') === '1'); ?>,
                        multiSelect: <?php echo json_encode(get_canvas_option_contenu('canvas_multi_select', '1') === '1'); ?>,
                        selectionMode: <?php echo json_encode(get_canvas_option_contenu('canvas_selection_mode', 'single')); ?>,
                        keyboardShortcuts: <?php echo json_encode(get_canvas_option_contenu('canvas_keyboard_shortcuts', '1') === '1'); ?>,
                        fpsTarget: <?php echo json_encode(get_canvas_option_contenu('canvas_fps_target', '60')); ?>,
                        memoryLimitJs: <?php echo json_encode(get_canvas_option_contenu('canvas_memory_limit_js', '50')); ?>,
                        responseTimeout: <?php echo json_encode(get_canvas_option_contenu('canvas_response_timeout', '5000')); ?>,
                        lazyLoadingEditor: <?php echo json_encode(get_canvas_option_contenu('canvas_lazy_loading_editor', '1') === '1'); ?>,
                        preloadCritical: <?php echo json_encode(get_canvas_option_contenu('canvas_preload_critical', '1') === '1'); ?>,
                        lazyLoadingPlugin: <?php echo json_encode(get_canvas_option_contenu('canvas_lazy_loading_plugin', '1') === '1'); ?>,
                        debugEnabled: <?php echo json_encode(get_canvas_option_contenu('canvas_debug_enabled', '0') === '1'); ?>,
                        performanceMonitoring: <?php echo json_encode(get_canvas_option_contenu('canvas_performance_monitoring', '0') === '1'); ?>,
                        errorReporting: <?php echo json_encode(get_canvas_option_contenu('canvas_error_reporting', '0') === '1'); ?>,
                        memoryLimitPhp: <?php echo json_encode(get_canvas_option_contenu('canvas_memory_limit_php', '128')); ?>
                    };
                    console.log('[PDF Builder] Initialized window.pdfBuilderCanvasSettings:', window.pdfBuilderCanvasSettings);

                    // Configuration des modals
                    var modalConfig = {
                        'affichage': 'canvas-affichage-modal-overlay',
                        'navigation': 'canvas-navigation-modal-overlay',
                        'comportement': 'canvas-comportement-modal-overlay',
                        'systeme': 'canvas-systeme-modal-overlay'
                    };

                    // Fonction simple pour ouvrir une modal
                    function openModal(category) {
                        console.log('[PDF Builder] openModal called with category:', category);
                        var modalId = modalConfig[category];
                        console.log('[PDF Builder] Looking for modal ID:', modalId);
                        if (!modalId) {
                            console.error('[PDF Builder] Unknown category:', category);
                            return;
                        }

                        var modal = document.getElementById(modalId);
                        console.log('[PDF Builder] Modal element found:', modal);
                        if (modal) {
                            // Synchroniser les inputs avec les valeurs actuelles avant d'ouvrir
                            syncModalInputsWithSettings(modal, category);
                            console.log('[PDF Builder] Setting modal display to flex');
                            modal.style.display = 'flex';
                            document.body.style.overflow = 'hidden';
                            console.log('[PDF Builder] Opened modal:', modalId);
                        } else {
                            console.error('[PDF Builder] Modal not found:', modalId);
                            console.log('[PDF Builder] Available modal IDs:');
                            Object.values(modalConfig).forEach(function(id) {
                                var element = document.getElementById(id);
                                console.log('  -', id, ':', element ? 'EXISTS' : 'NOT FOUND');
                            });
                        }
                    }

                    // Fonction pour synchroniser les inputs du modal avec window.pdfBuilderCanvasSettings
                    function syncModalInputsWithSettings(modal, category) {
                        if (!modal || !window.pdfBuilderCanvasSettings) {
                            console.log('[PDF Builder] Cannot sync modal inputs: modal or settings not available');
                            return;
                        }

                        console.log('[PDF Builder] Syncing modal inputs for category:', category);

                        // Mapping des clés settings vers les noms d'input
                        var settingToInputMap = {
                            'dragEnabled': 'pdf_builder_canvas_drag_enabled',
                            'resizeEnabled': 'pdf_builder_canvas_resize_enabled',
                            'rotateEnabled': 'pdf_builder_canvas_rotate_enabled',
                            'multiSelect': 'pdf_builder_canvas_multi_select',
                            'selectionMode': 'pdf_builder_canvas_selection_mode',
                            'keyboardShortcuts': 'pdf_builder_canvas_keyboard_shortcuts',
                            'exportQuality': 'pdf_builder_canvas_export_quality',
                            'exportFormat': 'pdf_builder_canvas_export_format',
                            'exportTransparent': 'pdf_builder_canvas_export_transparent',
                            'gridEnabled': 'pdf_builder_canvas_grid_enabled',
                            'gridSize': 'pdf_builder_canvas_grid_size',
                            'guidesEnabled': 'pdf_builder_canvas_guides_enabled',
                            'snapToGrid': 'pdf_builder_canvas_snap_to_grid'
                        };

                        // Pour chaque setting, trouver l'input correspondant et le mettre à jour
                        Object.keys(settingToInputMap).forEach(function(settingKey) {
                            var inputName = settingToInputMap[settingKey];
                            var input = modal.querySelector('[name="' + inputName + '"]');
                            var settingValue = window.pdfBuilderCanvasSettings[settingKey];

                            if (input) {
                                if (input.type === 'checkbox') {
                                    input.checked = settingValue === true || settingValue === '1';
                                    console.log('[PDF Builder] Synced checkbox', inputName, 'to', input.checked);
                                } else {
                                    input.value = settingValue;
                                    console.log('[PDF Builder] Synced input', inputName, 'to', settingValue);
                                }
                            } else {
                                console.log('[PDF Builder] Input not found:', inputName);
                            }
                        });
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
                    // Fonction utilitaire pour afficher des notifications via le système unifié
                    function showNotification(message, type) {
                        // Utiliser le système de notification unifié du plugin
                        jQuery.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'pdf_builder_show_notification',
                                message: message,
                                type: type,
                                nonce: '<?php echo wp_create_nonce("pdf_builder_notifications"); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    console.log('[PDF Builder] Notification affichée:', response.data);
                                } else {
                                    console.error('[PDF Builder] Erreur notification:', response.data);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('[PDF Builder] Erreur AJAX notification:', error);
                            }
                        });
                    }

                    function applyModalSettings(category) {
                        var modalId = modalConfig[category];
                        if (!modalId) return;

                        var modal = document.getElementById(modalId);
                        if (!modal) return;

                        // Collecter les valeurs des inputs canvas
                        var canvasData = {};
                        var inputs = modal.querySelectorAll('input, select, textarea');

                        // Gérer spécifiquement les checkboxes DPI (tableau)
                        var dpiCheckboxes = modal.querySelectorAll('input[name="pdf_builder_canvas_dpi[]"]:checked');
                        if (dpiCheckboxes.length > 0) {
                            var dpiValues = [];
                            dpiCheckboxes.forEach(function(checkbox) {
                                dpiValues.push(checkbox.value);
                            });
                            canvasData['pdf_builder_canvas_dpi'] = dpiValues.join(',');
                        }

                        // Gérer spécifiquement les checkboxes Formats (tableau)
                        var formatCheckboxes = modal.querySelectorAll('input[name="pdf_builder_canvas_formats[]"]:checked');
                        if (formatCheckboxes.length > 0) {
                            var formatValues = [];
                            formatCheckboxes.forEach(function(checkbox) {
                                formatValues.push(checkbox.value);
                            });
                            canvasData['pdf_builder_canvas_formats'] = formatValues.join(',');
                        }

                        // Gérer spécifiquement les checkboxes Orientations (tableau)
                        var orientationCheckboxes = modal.querySelectorAll('input[name="pdf_builder_canvas_orientations[]"]:checked');
                        if (orientationCheckboxes.length > 0) {
                            var orientationValues = [];
                            orientationCheckboxes.forEach(function(checkbox) {
                                orientationValues.push(checkbox.value);
                            });
                            canvasData['pdf_builder_canvas_orientations'] = orientationValues.join(',');
                        }

                        // Gérer les autres inputs normalement
                        inputs.forEach(function(input) {
                            // Ignorer les checkboxes DPI, Formats et Orientations déjà traitées
                            if (input.name === 'pdf_builder_canvas_dpi[]' || input.name === 'pdf_builder_canvas_formats[]' || input.name === 'pdf_builder_canvas_orientations[]') {
                                return;
                            }

                            if (input.name && input.name.indexOf('pdf_builder_canvas_') === 0) {
                                var value = input.type === 'checkbox' ? (input.checked ? '1' : '0') : input.value;
                                canvasData[input.name] = value;
                                console.log('[PDF Builder] Collected input:', input.name, '=', value, 'type:', input.type);
                            }
                        });

                        console.log('[PDF Builder] Canvas data to save:', canvasData);
                        console.log('[PDF Builder] Navigation params in canvasData:');
                        console.log('  grid_enabled:', canvasData['pdf_builder_canvas_grid_enabled']);
                        console.log('  guides_enabled:', canvasData['pdf_builder_canvas_guides_enabled']);
                        console.log('  snap_to_grid:', canvasData['pdf_builder_canvas_snap_to_grid']);
                        console.log('[PDF Builder] Total inputs found:', inputs.length);

                        if (Object.keys(canvasData).length === 0) {
                            console.log('[PDF Builder] Aucune donnée canvas à sauvegarder');
                            closeModal(modal);
                            return;
                        }

                        // Afficher un indicateur de chargement
                        var applyBtn = modal.querySelector('.canvas-modal-apply[data-category="' + category + '"]');
                        if (applyBtn) {
                            var originalText = applyBtn.innerHTML;
                            applyBtn.innerHTML = '⏳ Sauvegarde...';
                            applyBtn.disabled = true;
                        }

                        // Envoyer la requête AJAX
                        jQuery.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'pdf_builder_ajax_handler',
                                action_type: 'save_canvas_modal_settings',
                                ...canvasData,
                                nonce: '<?php echo wp_create_nonce("pdf_builder_ajax"); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    console.log('[PDF Builder] Paramètres canvas sauvegardés:', response.data);
                                    
                                    // Mettre à jour les paramètres dans window.pdfBuilderCanvasSettings
                                    if (typeof window.pdfBuilderCanvasSettings !== 'undefined') {
                                        // Créer une copie des données pour la conversion (sans modifier canvasData utilisé pour AJAX)
                                        var settingsUpdate = {};
                                        Object.keys(canvasData).forEach(function(key) {
                                            var settingKey = key.replace('pdf_builder_canvas_', '');
                                            var value = canvasData[key];
                                            
                                            // Convertir les valeurs '1'/'0' en boolean pour les paramètres booléens
                                            if (['drag_enabled', 'resize_enabled', 'rotate_enabled', 'multi_select', 'keyboard_shortcuts', 'grid_enabled', 'guides_enabled', 'snap_to_grid', 'shadow_enabled', 'debug_enabled', 'performance_monitoring', 'error_reporting'].includes(settingKey)) {
                                                value = value === '1';
                                            }
                                            
                                            settingsUpdate[settingKey] = value;
                                        });
                                        
                                        // Mettre à jour window.pdfBuilderCanvasSettings avec les nouvelles valeurs
                                        Object.assign(window.pdfBuilderCanvasSettings, settingsUpdate);
                                        
                                        // Dispatcher l'événement pour notifier React
                                        var event = new CustomEvent('pdfBuilderCanvasSettingsUpdated');
                                        window.dispatchEvent(event);
                                        
                                        // Mettre à jour les inputs du modal avec les valeurs sauvegardées
                                        Object.keys(canvasData).forEach(function(key) {
                                            var input = modal.querySelector('[name="' + key + '"]');
                                            if (input) {
                                                if (input.type === 'checkbox') {
                                                    input.checked = canvasData[key] === '1';
                                                } else {
                                                    input.value = canvasData[key];
                                                }
                                            }
                                        });
                                    }
                                    
                                    // Afficher une notification de succès
                                    showNotification('Paramètres sauvegardés avec succès !', 'success');
                                } else {
                                    console.error('[PDF Builder] Erreur sauvegarde:', response.data);
                                    showNotification('Erreur lors de la sauvegarde: ' + (response.data.message || 'Erreur inconnue'), 'error');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('[PDF Builder] Erreur AJAX:', error);
                                showNotification('Erreur de communication avec le serveur', 'error');
                            },
                            complete: function() {
                                // Restaurer le bouton
                                if (applyBtn) {
                                    applyBtn.innerHTML = originalText;
                                    applyBtn.disabled = false;
                                }
                                // Fermer la modal
                                closeModal(modal);
                            }
                        });
                    }

                    // Initialisation des événements
                    function initEvents() {
                        console.log('[PDF Builder] Initializing events...');

                        // Boutons de configuration
                        document.addEventListener('click', function(e) {
                            console.log('[PDF Builder] Click detected on:', e.target);

                            // Ouvrir modal
                            var configBtn = e.target.closest('.canvas-configure-btn');
                            if (configBtn) {
                                console.log('[PDF Builder] Configure button clicked');
                                e.preventDefault();
                                var card = configBtn.closest('.canvas-card');
                                if (card) {
                                    var category = card.getAttribute('data-category');
                                    console.log('[PDF Builder] Card category:', category);
                                    if (category && modalConfig[category]) {
                                        console.log('[PDF Builder] Opening modal for category:', category);
                                        openModal(category);
                                    } else {
                                        console.log('[PDF Builder] Invalid category or no modal config for:', category);
                                    }
                                } else {
                                    console.log('[PDF Builder] No parent card found');
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
                        document.addEventListener('DOMContentLoaded', function() {
                            // S'assurer que tous les modals sont cachés au chargement
                            var allModals = document.querySelectorAll('.canvas-modal-overlay');
                            allModals.forEach(function(modal) {
                                modal.style.display = 'none';
                            });
                            initEvents();
                        });
                    } else {
                        // S'assurer que tous les modals sont cachés au chargement
                        var allModals = document.querySelectorAll('.canvas-modal-overlay');
                        allModals.forEach(function(modal) {
                            modal.style.display = 'none';
                        });
                        initEvents();
                    }

                    console.log('[PDF Builder] Canvas modals system ready');
                })();
            </script>

</section> <!-- Fermeture de settings-section contenu-settings -->

