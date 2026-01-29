<?php
    /**
     * PDF Builder Pro - Content Settings Tab
     * Canvas and design configuration settings
     * Updated: 2025-12-03
     */

    // Sécurité WordPress
    if (!defined('ABSPATH')) {
        exit('Direct access not allowed');
    }

    // Définir la constante si elle n'existe pas
    if (!defined('PDF_BUILDER_PRO_PREMIUM')) {
        define('PDF_BUILDER_PRO_PREMIUM', false);
    }

    // Déclarations de fonctions WordPress si elles ne sont pas disponibles (pour linter)
    if (!function_exists('add_option')) {
        function add_option($option, $value = '', $deprecated = '', $autoload = 'yes') { return true; }
    }
    if (!function_exists('get_option')) {
        function get_option($option, $default = false) { return $default; }
    }
    if (!function_exists('esc_attr')) {
        function esc_attr($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
    }
    if (!function_exists('esc_html')) {
        function esc_html($text) { return htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8'); }
    }
    if (!function_exists('selected')) {
        function selected($selected, $current = true, $echo = true) {
            $result = $selected == $current ? ' selected="selected"' : '';
            if ($echo) echo $result;
            return $result;
        }
    }
    if (!function_exists('checked')) {
        function checked($checked, $current = true, $echo = true) {
            $result = $checked == $current ? ' checked="checked"' : '';
            if ($echo) echo $result;
            return $result;
        }
    }

    echo "<!-- TEST: settings-contenu.php loaded - VERSION DIRECTE 2025-12-12 -->";

    $settings = pdf_builder_get_option('pdf_builder_settings', array());

    // Fonction helper pour récupérer les valeurs Canvas depuis les options individuelles
    function get_canvas_option_contenu($key, $default = '') {
        $option_key = 'pdf_builder_' . $key;
        // Lire depuis l'array de settings
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $value = isset($settings[$option_key]) ? $settings[$option_key] : null;

        if ($value === null) {
            $value = $default;
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log("[PDF Builder] PAGE_LOAD - {$key}: OPTION_NOT_FOUND - using default '{$default}' - KEY: {$option_key}"); }
        } else {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log("[PDF Builder] PAGE_LOAD - {$key}: FOUND_DB_VALUE '{$value}' - KEY: {$option_key}"); }
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
        'pdf_builder_canvas_rotate_enabled' => '0',
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
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log("[INIT CANVAS OPTIONS] Created option: $option_name = $default_value"); }
        }
    }
?>
<section id="contenu" class="settings-section contenu-settings" role="tabpanel" aria-labelledby="tab-contenu">

    <!-- Inclusion des modales Canvas -->
    <?php require_once __DIR__ . '/settings-modals.php'; ?>

    <div class="settings-content">
<?php
    $settings = pdf_builder_get_option('pdf_builder_settings', array());

?>
            <!-- Section Canvas -->
            <section class="contenu-canvas-section">
                <?php if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log("[PDF Builder] CANVAS_SECTION - Rendering canvas section"); } ?>
                <h3 style="display: flex; justify-content: flex-start; align-items: center;">
                    <span>
                        🎨 Canvas
                    </span>
                    <button type="button" id="reset-canvas-settings" class="button button-secondary" style="font-size: 12px; padding: 4px 8px; margin-left: auto;" title="Réinitialiser tous les paramètres Canvas aux valeurs par défaut">
                        🔄 Réinitialiser
                    </button>
                </h3>

                <p>Configurez l'apparence et le comportement de votre canvas de conception PDF.</p>

                <?php if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log("[PDF Builder] HIDDEN_FIELDS - About to render hidden fields"); } ?>
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
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_grid_size]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_grid_size', '20')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_guides_enabled]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_guides_enabled', '1')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_snap_to_grid]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_snap_to_grid', '1')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_zoom_min]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_zoom_min', '25')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_zoom_max]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_zoom_max', '500')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_zoom_default]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_zoom_default', '100')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_zoom_step]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_zoom_step', '25')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_export_quality]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_export_quality', '90')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_export_format]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_export_format', 'png')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_export_transparent]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_export_transparent', '0')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_drag_enabled]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_drag_enabled', '1')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_resize_enabled]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_resize_enabled', '1')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_rotate_enabled]" value="<?php echo esc_attr(get_canvas_option_contenu('canvas_rotate_enabled', '0')); ?>">
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
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_allow_portrait]" value="<?php echo esc_attr(pdf_builder_get_option('pdf_builder_canvas_allow_portrait', '1')); ?>">
                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_allow_landscape]" value="<?php echo esc_attr(pdf_builder_get_option('pdf_builder_canvas_allow_landscape', '1')); ?>">

                    <!-- DEBUG: Hidden fields rendering completed -->
                    <?php if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log("[PDF Builder] HIDDEN_FIELDS - Hidden fields rendered successfully"); } ?>

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
                var ajaxurl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
                (function() {
                    'use strict';

                    

                    // Initialize window.pdfBuilderCanvasSettings with current DB values
                    window.pdfBuilderCanvasSettings = {
                        default_canvas_width: <?php echo json_encode(get_canvas_option_contenu('canvas_width', '794')); ?>,
                        default_canvas_height: <?php echo json_encode(get_canvas_option_contenu('canvas_height', '1123')); ?>,
                        default_canvas_dpi: <?php echo json_encode(get_canvas_option_contenu('canvas_dpi', '96')); ?>,
                        default_canvas_format: <?php echo json_encode(get_canvas_option_contenu('canvas_format', 'A4')); ?>,
                        default_canvas_unit: <?php echo json_encode(get_canvas_option_contenu('canvas_unit', 'px')); ?>,
                        default_canvas_orientation: <?php echo json_encode(get_canvas_option_contenu('canvas_default_orientation', 'portrait')); ?>,
                        canvas_background_color: <?php echo json_encode(get_canvas_option_contenu('canvas_bg_color', '#ffffff')); ?>,
                        <?php
                        // Vérifier si l'utilisateur est premium pour les paramètres de style avancés
                        $is_premium = defined('PDF_BUILDER_PRO_PREMIUM') ? PDF_BUILDER_PRO_PREMIUM : false;
                        if ($is_premium) {
                            // Utilisateur premium : utiliser les paramètres configurés
                            echo 'border_color: ' . json_encode(get_canvas_option_contenu('canvas_border_color', '#cccccc')) . ',' . "\n";
                            echo 'border_width: ' . json_encode(get_canvas_option_contenu('canvas_border_width', '1')) . ',' . "\n";
                            echo 'container_background_color: ' . json_encode(get_canvas_option_contenu('canvas_container_bg_color', '#f8f9fa')) . ',' . "\n";
                        } else {
                            // Utilisateur non-premium : forcer des paramètres par défaut
                            echo 'border_color: "#cccccc",' . "\n";
                            echo 'border_width: "1",' . "\n";
                            echo 'container_background_color: "#f8f9fa",' . "\n";
                        }
                        ?>
                        shadow_enabled: <?php echo json_encode(get_canvas_option_contenu('canvas_shadow_enabled', '0') === '1'); ?>,
                        show_grid: <?php echo json_encode(get_canvas_option_contenu('canvas_grid_enabled', '1') === '1'); ?>,
                        grid_size: <?php echo json_encode(get_canvas_option_contenu('canvas_grid_size', '20')); ?>,
                        show_guides: <?php echo json_encode(get_canvas_option_contenu('canvas_guides_enabled', '1') === '1'); ?>,
                        snap_to_grid: <?php echo json_encode(get_canvas_option_contenu('canvas_snap_to_grid', '1') === '1'); ?>,
                        zoom_min: <?php echo json_encode(get_canvas_option_contenu('canvas_zoom_min', '25')); ?>,
                        zoom_max: <?php echo json_encode(get_canvas_option_contenu('canvas_zoom_max', '500')); ?>,
                        zoom_default: <?php echo json_encode(get_canvas_option_contenu('canvas_zoom_default', '100')); ?>,
                        zoom_step: <?php echo json_encode(get_canvas_option_contenu('canvas_zoom_step', '25')); ?>,
                        export_quality: <?php echo json_encode(get_canvas_option_contenu('canvas_export_quality', '90')); ?>,
                        export_format: <?php echo json_encode(get_canvas_option_contenu('canvas_export_format', 'png')); ?>,
                        export_transparent: <?php echo json_encode(get_canvas_option_contenu('canvas_export_transparent', '0') === '1'); ?>,
                        drag_enabled: <?php echo json_encode(get_canvas_option_contenu('canvas_drag_enabled', '1') === '1'); ?>,
                        resize_enabled: <?php echo json_encode(get_canvas_option_contenu('canvas_resize_enabled', '1') === '1'); ?>,
                        rotate_enabled: <?php echo json_encode(get_canvas_option_contenu('canvas_rotate_enabled', '1') === '1'); ?>,
                        multi_select: <?php echo json_encode(get_canvas_option_contenu('canvas_multi_select', '1') === '1'); ?>,
                        selection_mode: <?php echo json_encode(get_canvas_option_contenu('canvas_selection_mode', 'single')); ?>,
                        keyboard_shortcuts: <?php echo json_encode(get_canvas_option_contenu('canvas_keyboard_shortcuts', '1') === '1'); ?>,
                        fps_target: <?php echo json_encode(get_canvas_option_contenu('canvas_fps_target', '60')); ?>,
                        memory_limit_js: <?php echo json_encode(get_canvas_option_contenu('canvas_memory_limit_js', '50')); ?>,
                        response_timeout: <?php echo json_encode(get_canvas_option_contenu('canvas_response_timeout', '5000')); ?>,
                        lazy_loading_editor: <?php echo json_encode(get_canvas_option_contenu('canvas_lazy_loading_editor', '1') === '1'); ?>,
                        preload_critical: <?php echo json_encode(get_canvas_option_contenu('canvas_preload_critical', '1') === '1'); ?>,
                        lazy_loading_plugin: <?php echo json_encode(get_canvas_option_contenu('canvas_lazy_loading_plugin', '1') === '1'); ?>,
                        debug_enabled: <?php echo json_encode(get_canvas_option_contenu('canvas_debug_enabled', '0') === '1'); ?>,
                        performance_monitoring: <?php echo json_encode(get_canvas_option_contenu('canvas_performance_monitoring', '0') === '1'); ?>,
                        error_reporting: <?php echo json_encode(get_canvas_option_contenu('canvas_error_reporting', '0') === '1'); ?>,
                        memory_limit_php: <?php echo json_encode(get_canvas_option_contenu('canvas_memory_limit_php', '128')); ?>
                    };
                    

                    // Configuration des modals
                    var modalConfig = {
                        'affichage': 'canvas-affichage-modal-overlay',
                        'navigation': 'canvas-navigation-modal-overlay',
                        'comportement': 'canvas-comportement-modal-overlay',
                        'systeme': 'canvas-systeme-modal-overlay'
                    };

                    // ==================== NOUVELLE LOGIQUE DE SAUVEGARDE DES TOGGLES ====================

                    // Définition des toggles par modal
                    var modalToggles = {
                        'affichage': ['pdf_builder_canvas_shadow_enabled'],
                        'navigation': ['pdf_builder_canvas_grid_enabled', 'pdf_builder_canvas_guides_enabled', 'pdf_builder_canvas_snap_to_grid'],
                        'comportement': ['pdf_builder_canvas_drag_enabled', 'pdf_builder_canvas_resize_enabled', 'pdf_builder_canvas_rotate_enabled', 'pdf_builder_canvas_multi_select', 'pdf_builder_canvas_keyboard_shortcuts', 'pdf_builder_canvas_export_transparent'],
                        'systeme': ['pdf_builder_canvas_debug_enabled', 'pdf_builder_canvas_performance_monitoring', 'pdf_builder_canvas_error_reporting']
                    };

                    // Mapping des noms d'input vers les clés camelCase pour window.pdfBuilderCanvasSettings
                    var inputToSettingMap = {
                        'pdf_builder_canvas_shadow_enabled': 'shadowEnabled',
                        'pdf_builder_canvas_grid_enabled': 'gridEnabled',
                        'pdf_builder_canvas_guides_enabled': 'guidesEnabled',
                        'pdf_builder_canvas_snap_to_grid': 'snapToGrid',
                        'pdf_builder_canvas_drag_enabled': 'dragEnabled',
                        'pdf_builder_canvas_resize_enabled': 'resizeEnabled',
                        'pdf_builder_canvas_rotate_enabled': 'rotateEnabled',
                        'pdf_builder_canvas_multi_select': 'multiSelect',
                        'pdf_builder_canvas_keyboard_shortcuts': 'keyboardShortcuts',
                        'pdf_builder_canvas_export_transparent': 'exportTransparent',
                        'pdf_builder_canvas_debug_enabled': 'debugEnabled',
                        'pdf_builder_canvas_performance_monitoring': 'performanceMonitoring',
                        'pdf_builder_canvas_error_reporting': 'errorReporting'
                    };

                    // Fonction pour sauvegarder les toggles d'une modal (agressive : force la synchronisation)
                    function saveModalToggles(category) {
                        var modalId = modalConfig[category];
                        if (!modalId) return;

                        var modal = document.getElementById(modalId);
                        if (!modal) return;

                        var togglesForModal = modalToggles[category] || [];
                        if (togglesForModal.length === 0) return;

                        // Synchronisation agressive : mettre à jour tous les hidden fields pour cette modal
                        togglesForModal.forEach(function(toggleName) {
                            var modalInputs = modal.querySelectorAll('[name="' + toggleName + '"], [name="' + toggleName + '[]"]');
                            var hiddenField = document.querySelector('input[name="pdf_builder_settings[' + toggleName + ']"]');
                            
                            if (modalInputs.length > 0 && hiddenField) {
                                if (modalInputs[0].type === 'checkbox') {
                                    if (toggleName.endsWith('[]') || modalInputs.length > 1) {
                                        // Array of checkboxes - collect checked values
                                        var checkedValues = [];
                                        modalInputs.forEach(function(input) {
                                            if (input.checked) {
                                                checkedValues.push(input.value);
                                            }
                                        });
                                        hiddenField.value = checkedValues.join(',');
                                    } else {
                                        // Single checkbox
                                        hiddenField.value = modalInputs[0].checked ? '1' : '0';
                                    }
                                }
                            }
                        });

                        // Fermer la modal
                        closeModal(modal);
                    }



                    // Fonction simple pour ouvrir une modal
                    function openModal(category) {
                        var modalId = modalConfig[category];
                        if (!modalId) return;

                        var modal = document.getElementById(modalId);
                        if (modal) {
                            syncModalInputsWithHiddenFields(modal, category);
                            modal.style.display = 'flex';
                            document.body.style.overflow = 'hidden';
                        }
                    }

                    // Fonction pour synchroniser les inputs du modal avec les hidden fields
                    function syncModalInputsWithHiddenFields(modal, category) {
                        if (!modal) return;

                        var togglesForModal = modalToggles[category] || [];
                        togglesForModal.forEach(function(inputName) {
                            var hiddenField = document.querySelector('input[name="pdf_builder_settings[' + inputName + ']"]');
                            var modalInputs = modal.querySelectorAll('[name="' + inputName + '"], [name="' + inputName + '[]"]');

                            if (modalInputs.length > 0) {
                                if (modalInputs[0].type === 'checkbox') {
                                    if (inputName.endsWith('[]') || modalInputs.length > 1) {
                                        // Array of checkboxes
                                        if (hiddenField && hiddenField.value) {
                                            var values = hiddenField.value.split(',');
                                            modalInputs.forEach(function(input) {
                                                input.checked = values.includes(input.value);
                                            });
                                        }
                                    } else {
                                        // Single checkbox
                                        if (hiddenField) {
                                            modalInputs[0].checked = hiddenField.value === '1';
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // Fonction simple pour fermer une modal
                    function closeModal(modalElement) {
                        if (modalElement) {
                            modalElement.style.display = 'none';
                            document.body.style.overflow = '';
                            
                        }
                    }

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
                                nonce: '<?php echo wp_create_nonce('pdf_builder_notifications'); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    
                                } else {
                                    
                                }
                            },
                            error: function(xhr, status, error) {
                                
                            }
                        });
                    }

                    function applyModalSettings(category) {
                        console.log('applyModalSettings called with category:', category);
                        var modalId = modalConfig[category];
                        if (!modalId) {
                            console.error('No modalId found for category:', category);
                            return;
                        }

                        var modal = document.getElementById(modalId);
                        if (!modal) {
                            console.error('Modal not found:', modalId);
                            return;
                        }

                        // Collecter les données à sauvegarder
                        var formData = new FormData();
                        formData.append('action', 'pdf_builder_save_canvas_modal_settings');
                        formData.append('nonce', '<?php echo \PDF_Builder\Admin\Handlers\NonceManager::createNonce(); ?>');
                        formData.append('category', category);

                        // Collecter TOUS les champs de formulaire dans la modale
                        var allInputs = modal.querySelectorAll('input, select, textarea');
                        console.log('Found inputs in modal:', allInputs.length);

                        // Grouper les inputs par nom pour gérer les arrays
                        var inputsByName = {};
                        allInputs.forEach(function(input) {
                            var name = input.name;
                            if (name) {
                                if (!inputsByName[name]) {
                                    inputsByName[name] = [];
                                }
                                inputsByName[name].push(input);
                            }
                        });

                        // Traiter chaque groupe d'inputs
                        for (var name in inputsByName) {
                            var inputs = inputsByName[name];
                            var firstInput = inputs[0];

                            if (firstInput.type === 'checkbox') {
                                if (name.endsWith('[]')) {
                                    // Checkbox array - collect all checked values
                                    var checkedValues = [];
                                    inputs.forEach(function(input) {
                                        if (input.checked) {
                                            checkedValues.push(input.value);
                                        }
                                    });
                                    if (checkedValues.length > 0) {
                                        formData.append(name, checkedValues.join(','));
                                        console.log('Checkbox array:', name, '=', checkedValues.join(','));
                                    }
                                } else {
                                    // Single checkbox
                                    var checked = inputs.some(function(input) { return input.checked; });
                                    formData.append(name, checked ? '1' : '0');
                                    console.log('Checkbox:', name, '=', checked ? '1' : '0');
                                }
                            } else if (firstInput.type === 'radio') {
                                // Radio buttons - find the checked one
                                var checkedInput = inputs.find(function(input) { return input.checked; });
                                if (checkedInput) {
                                    formData.append(name, checkedInput.value);
                                    console.log('Radio:', name, '=', checkedInput.value);
                                }
                            } else if (firstInput.type === 'file') {
                                // Ne pas traiter les fichiers pour le moment
                            } else {
                                // Single value inputs (text, select, textarea, etc.)
                                formData.append(name, firstInput.value);
                                console.log('Input:', name, '=', firstInput.value);
                            }
                        }

                        console.log('Sending AJAX request...');
                        // Sauvegarder via AJAX
                        fetch(ajaxurl, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            console.log('AJAX response received:', response);
                            console.log('Response status:', response.status);
                            console.log('Response ok:', response.ok);
                            return response.json();
                        })
                        .then(data => {
                            console.log('AJAX data:', data);
                            if (data.success) {
                                console.log('Success: Parameters saved');
                                console.log('Updated count:', data.data ? data.data.updated_count : 'undefined');
                                console.log('Category:', data.data ? data.data.category : 'undefined');
                                showNotification('Paramètres sauvegardés avec succès', 'success');

                                // Forcer la synchronisation des hidden fields après sauvegarde
                                saveModalToggles(category);
                            } else {
                                console.error('Error saving:', data.message);
                                console.error('Full error data:', data);
                                showNotification('Erreur lors de la sauvegarde: ' + (data.message || 'Erreur inconnue'), 'error');
                            }
                        })
                        .catch(error => {
                            console.error('AJAX error:', error);
                            console.error('Error details:', error.message);
                            showNotification('Erreur de connexion: ' + error.message, 'error');
                        });

                        // Mettre à jour les hidden fields et fermer la modal (logique existante)
                        saveModalToggles(category);
                    }

                    // Initialisation des événements
                    function initEvents() {
                        

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
                                    } else {
                                        
                                    }
                                } else {
                                    
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

                        // Synchronisation agressive : mettre à jour les hidden fields en temps réel lors des changements dans les modales
                        document.addEventListener('change', function(e) {
                            var input = e.target;
                            if (input.type === 'checkbox' && input.closest('.canvas-modal-overlay')) {
                                var inputName = input.name;
                                if (inputName) {
                                    var hiddenField = document.querySelector('input[name="pdf_builder_settings[' + inputName + ']"]');
                                    if (hiddenField) {
                                        hiddenField.value = input.checked ? '1' : '0';
                                        
                                    }
                                }
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

                    
                })();
            </script>

</section> <!-- Fermeture de settings-section contenu-settings -->




