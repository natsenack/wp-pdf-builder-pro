<?php
/**
 * PDF Builder Pro - Content Settings Tab
 * Canvas and design configuration settings
 * Updated: 2025-12-03
 */

// require_once __DIR__ . '/settings-helpers.php'; // REMOVED - settings-helpers.php deleted

$settings = get_option('pdf_builder_settings', array());

error_log("[PDF Builder Debug] Page load: pdf_builder_settings contains shadow_enabled: " . ($settings['pdf_builder_canvas_canvas_shadow_enabled'] ?? 'NOT_SET'));
error_log("[PDF Builder Debug] Page load: grid_enabled: " . get_option('pdf_builder_canvas_canvas_grid_enabled', 'DEFAULT') . " (type: " . gettype(get_option('pdf_builder_canvas_canvas_grid_enabled', 'DEFAULT')) . ")");
error_log("[PDF Builder Debug] Page load: guides_enabled: " . get_option('pdf_builder_canvas_canvas_guides_enabled', 'DEFAULT') . " (type: " . gettype(get_option('pdf_builder_canvas_canvas_guides_enabled', 'DEFAULT')) . ")");
error_log("[PDF Builder Debug] Page load: snap_to_grid: " . get_option('pdf_builder_canvas_canvas_snap_to_grid', 'DEFAULT') . " (type: " . gettype(get_option('pdf_builder_canvas_canvas_snap_to_grid', 'DEFAULT')) . ")");
error_log("[PDF Builder Debug] Page load: grid_size: " . get_option('pdf_builder_canvas_canvas_grid_size', 'DEFAULT') . " (type: " . gettype(get_option('pdf_builder_canvas_canvas_grid_size', 'DEFAULT')) . ")");

// Debug: check all canvas options
global $wpdb;
$canvas_options = $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'pdf_builder_canvas_canvas_%' LIMIT 20");
error_log("[PDF Builder Debug] All canvas options in DB:");
foreach ($canvas_options as $option) {
    error_log("  {$option->option_name} = {$option->option_value} (type: " . gettype($option->option_value) . ")");
}

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

                <form method="post" id="canvas-form">
                    <?php wp_nonce_field('pdf_builder_canvas_nonce', 'pdf_builder_canvas_nonce'); ?>
                    <input type="hidden" name="submit_canvas" value="1">

                    <!-- Champs cachés pour la sauvegarde centralisée des paramètres -->
                    <input type="hidden" name="pdf_builder_canvas_canvas_width" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_width', '794')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_height" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_height', '1123')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_dpi" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_dpi', '96')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_format" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_format', 'A4')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_bg_color" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_bg_color', '#ffffff')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_border_color" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_border_color', '#cccccc')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_border_width" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_border_width', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_shadow_enabled" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_shadow_enabled', '0')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_container_bg_color" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_container_bg_color', '#f8f9fa')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_grid_enabled" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_grid_enabled', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_grid_size" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_grid_size', '20')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_guides_enabled" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_guides_enabled', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_snap_to_grid" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_snap_to_grid', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_zoom_min" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_zoom_min', '25')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_zoom_max" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_zoom_max', '500')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_zoom_default" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_zoom_default', '100')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_zoom_step" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_zoom_step', '25')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_export_quality" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_export_quality', '90')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_export_format" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_export_format', 'png')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_export_transparent" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_export_transparent', '0')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_drag_enabled" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_drag_enabled', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_resize_enabled" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_resize_enabled', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_rotate_enabled" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_rotate_enabled', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_multi_select" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_multi_select', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_selection_mode" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_selection_mode', 'single')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_keyboard_shortcuts" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_keyboard_shortcuts', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_fps_target" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_fps_target', '60')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_memory_limit_js" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_memory_limit_js', '50')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_response_timeout" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_response_timeout', '5000')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_lazy_loading_editor" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_lazy_loading_editor', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_preload_critical" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_preload_critical', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_lazy_loading_plugin" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_lazy_loading_plugin', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_debug_enabled" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_debug_enabled', '0')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_performance_monitoring" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_performance_monitoring', '0')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_error_reporting" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_error_reporting', '0')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_canvas_memory_limit_php" value="<?php echo esc_attr(get_option('pdf_builder_canvas_canvas_memory_limit_php', '128')); ?>">

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
                                        <span id="card-canvas-width">794</span>×
                                        <span id="card-canvas-height">1123</span>px
                                    </div>
                                    <span class="preview-size" id="card-canvas-dpi">
                                        96 DPI - A4 (210.0×297.0mm)
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
                                <div id="card-bg-preview" class="color-preview bg" title="Fond" style="background-color: <?php echo esc_attr(get_option('pdf_builder_canvas_canvas_bg_color', '#ffffff')); ?>;"></div>
                                <div id="card-border-preview" class="color-preview border" title="Bordure" style="border-color: <?php echo esc_attr(get_option('pdf_builder_canvas_canvas_border_color', '#cccccc')); ?>; border-width: <?php echo esc_attr(get_option('pdf_builder_canvas_canvas_border_width', '1')); ?>px;"></div>
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
                                <div class="export-preview" title="Export PNG/JPG/PDF activé"></div>
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

                </form>
            </section>

                <!-- Section Templates -->
            <section class="contenu-templates-section">
                <h3>
                    <span>
                        📋 Templates
                        <span id="template-library-indicator" class="template-library-indicator" style="background: <?php echo (($settings['pdf_builder_template_library_enabled'] ?? '1') === '1') ? '#28a745' : '#dc3545'; ?>;"><?php echo (($settings['pdf_builder_template_library_enabled'] ?? '1') === '1') ? 'ACTIF' : 'INACTIF'; ?></span>
                    </span>
                </h3>

                <form method="post" id="templates-form">
                    <?php wp_nonce_field('pdf_builder_templates_nonce', 'pdf_builder_templates_nonce'); ?>
                    <input type="hidden" name="submit_templates" value="1">

                    <table class="form-table">
                    <tr>
                        <th scope="row"><label for="default_template">Template par défaut</label></th>
                        <td>
                            <select id="default_template" name="pdf_builder_default_template">
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
                                <input type="checkbox" id="template_library_enabled" name="pdf_builder_template_library_enabled" value="1" <?php checked($settings['pdf_builder_template_library_enabled'] ?? '1', '1'); ?>>
                                <span class="toggle-slider"></span>
                            </label>
                            <p class="description">Active la bibliothèque de templates prédéfinis</p>
                        </td>
                    </tr>
                </table>
                </form>
            </section>



            <!-- CSS pour les modales Canvas -->
            <?php
            $plugin_dir = dirname(dirname(dirname(__FILE__)));
            $css_url = plugins_url('resources/assets/css/canvas-modals.css', $plugin_dir . '/pdf-builder-pro.php');
            wp_enqueue_style('pdf-builder-canvas-modals', $css_url, array(), '1.0.0');
            ?>

            <script>
                (function() {
                    'use strict';

                    // LOGS JAVASCRIPT DÉTAILLÉS POUR DÉBOGAGE MAXIMAL
                    console.log('🚀 [JS INIT] Début chargement script modales - ' + new Date().toISOString());
                    console.log('🔍 [JS INIT] URL actuelle:', window.location.href);
                    console.log('🔍 [JS INIT] UserAgent:', navigator.userAgent);
                    console.log('🔍 [JS INIT] Viewport:', window.innerWidth + 'x' + window.innerHeight);
                    console.log('🔍 [JS INIT] DOM ready:', document.readyState);
                    console.log('🔍 [JS INIT] jQuery loaded:', typeof jQuery !== 'undefined');
                    console.log('🔍 [JS INIT] WordPress ajaxurl:', typeof ajaxurl !== 'undefined' ? ajaxurl : 'undefined');

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

                    // SYSTÈME DE MONITORING UNIFIÉ
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
                            console.log('🔍 Modal Monitoring System: Initialisé');
                            this.log('system', 'Monitoring activé', { timestamp: Date.now() });
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

                            console.log(`${emoji[type] || '📝'} [${type.toUpperCase()}] ${message}`, data);
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
                            return {
                                ...this.metrics,
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
                            console.group('📊 Modal Monitoring Dashboard');
                            console.table(report.summary);
                            console.log('🔄 État actuel:', report.currentState);
                            console.log('📝 Activité récente:', report.recentActivity);
                            if (report.alerts.length > 0) {
                                console.group('🚨 Alertes');
                                report.alerts.forEach(alert => {
                                    console.log(`[${alert.level.toUpperCase()}] ${alert.message}`);
                                    console.log(`💡 Suggestion: ${alert.suggestion}`);
                                });
                                console.groupEnd();
                            }
                            console.groupEnd();

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

                            console.log('🔄 Auto-monitoring démarré (vérifications toutes les 30s)');
                        }
                    };

                    // Initialiser le monitoring
                    modalMonitoring.init();

                    // Démarrer l'auto-monitoring
                    modalMonitoring.startAutoMonitoring();
                    const previewSystem = {
                        // Valeurs actuelles des paramètres
                        values: {
                            canvas_canvas_width: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_width', '794')); ?>,
                            canvas_canvas_height: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_height', '1123')); ?>,
                            canvas_canvas_dpi: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_dpi', '96')); ?>,
                            canvas_canvas_format: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_format', 'A4')); ?>,
                            canvas_canvas_bg_color: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_bg_color', '#ffffff')); ?>,
                            canvas_canvas_border_color: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_border_color', '#cccccc')); ?>,
                            canvas_canvas_border_width: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_border_width', '1')); ?>,
                            canvas_canvas_shadow_enabled: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_shadow_enabled', '0') === '1'); ?>,
                            canvas_canvas_grid_enabled: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_grid_enabled', '1') === '1'); ?>,
                            canvas_canvas_grid_size: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_grid_size', '20')); ?>,
                            canvas_canvas_guides_enabled: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_guides_enabled', '1') === '1'); ?>,
                            canvas_canvas_snap_to_grid: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_snap_to_grid', '1') === '1'); ?>,
                            canvas_canvas_zoom_min: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_zoom_min', '25')); ?>,
                            canvas_canvas_zoom_max: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_zoom_max', '500')); ?>,
                            canvas_canvas_zoom_default: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_zoom_default', '100')); ?>,
                            canvas_canvas_zoom_step: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_zoom_step', '25')); ?>,
                            canvas_canvas_export_quality: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_export_quality', '90')); ?>,
                            canvas_canvas_export_format: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_export_format', 'png')); ?>,
                            canvas_canvas_export_transparent: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_export_transparent', '0') === '1'); ?>,
                            canvas_canvas_drag_enabled: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_drag_enabled', '1') === '1'); ?>,
                            canvas_canvas_resize_enabled: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_resize_enabled', '1') === '1'); ?>,
                            canvas_canvas_rotate_enabled: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_rotate_enabled', '1') === '1'); ?>,
                            canvas_canvas_multi_select: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_multi_select', '1') === '1'); ?>,
                            canvas_canvas_selection_mode: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_selection_mode', 'single')); ?>,
                            canvas_canvas_keyboard_shortcuts: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_keyboard_shortcuts', '1') === '1'); ?>,
                            canvas_canvas_fps_target: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_fps_target', '60')); ?>,
                            canvas_canvas_memory_limit_js: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_memory_limit_js', '50')); ?>,
                            canvas_canvas_response_timeout: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_response_timeout', '5000')); ?>,
                            canvas_canvas_lazy_loading_editor: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_lazy_loading_editor', '1') === '1'); ?>,
                            canvas_canvas_preload_critical: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_preload_critical', '1') === '1'); ?>,
                            canvas_canvas_lazy_loading_plugin: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_lazy_loading_plugin', '1') === '1'); ?>,
                            canvas_canvas_debug_enabled: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_debug_enabled', '0') === '1'); ?>,
                            canvas_canvas_performance_monitoring: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_performance_monitoring', '0') === '1'); ?>,
                            canvas_canvas_error_reporting: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_error_reporting', '0') === '1'); ?>,
                            canvas_canvas_memory_limit_php: <?php echo json_encode(get_option('pdf_builder_canvas_canvas_memory_limit_php', '128')); ?>
                        },

                        // Mettre à jour une valeur et rafraîchir les previews
                        updateValue: function(key, value) {
                            this.values[key] = value;
                            this.refreshPreviews();
                        },

                        // Calculer les dimensions en mm
                        calculateMM: function(pixels, dpi) {
                            return ((pixels / dpi) * 25.4).toFixed(1);
                        },

                        // Rafraîchir toutes les previews
                        refreshPreviews: function() {
                            console.log('🔄 REFRESH PREVIEWS CALLED');

                            const v = this.values;

                            // Attendre que le DOM soit prêt
                            if (document.readyState !== 'complete') {
                                console.log('DOM not ready, retrying in 100ms...');
                                setTimeout(() => this.refreshPreviews(), 100);
                                return;
                            }

                            // Preview Dimensions
                            const widthEl = document.getElementById('card-canvas-width');
                            const heightEl = document.getElementById('card-canvas-height');
                            const dpiEl = document.getElementById('card-canvas-dpi');

                            console.log('Elements found:', { widthEl: !!widthEl, heightEl: !!heightEl, dpiEl: !!dpiEl });

                            if (widthEl) {
                                widthEl.textContent = v.canvas_canvas_width;
                                console.log('✅ Width updated to:', v.canvas_canvas_width);
                            }
                            if (heightEl) {
                                heightEl.textContent = v.canvas_canvas_height;
                                console.log('✅ Height updated to:', v.canvas_canvas_height);
                            }
                            if (dpiEl) {
                                const format = v.canvas_canvas_format || 'A4';
                                const widthMM = this.calculateMM(v.canvas_canvas_width, v.canvas_canvas_dpi);
                                const heightMM = this.calculateMM(v.canvas_canvas_height, v.canvas_canvas_dpi);
                                dpiEl.textContent = `${v.canvas_canvas_dpi} DPI - ${format} (${widthMM}×${heightMM}mm)`;
                                console.log('✅ DPI updated to:', dpiEl.textContent);
                            }

                            // Preview Apparence
                            const bgPreview = document.getElementById('card-bg-preview');
                            const borderPreview = document.getElementById('card-border-preview');

                            console.log('Appearance elements:', { bgPreview: !!bgPreview, borderPreview: !!borderPreview });

                            if (bgPreview) {
                                bgPreview.style.backgroundColor = v.canvas_canvas_bg_color;
                                bgPreview.style.borderColor = v.canvas_canvas_bg_color;
                                console.log('✅ BG color updated to:', v.canvas_canvas_bg_color);
                            }
                            if (borderPreview) {
                                borderPreview.style.borderColor = v.canvas_canvas_border_color;
                                borderPreview.style.backgroundColor = v.canvas_canvas_bg_color;
                                borderPreview.style.borderWidth = v.canvas_canvas_border_width + 'px';
                                borderPreview.style.boxShadow = v.canvas_canvas_shadow_enabled ? '0 4px 8px rgba(0,0,0,0.2)' : 'none';
                                console.log('✅ Border updated:', v.canvas_canvas_border_color, v.canvas_canvas_border_width + 'px');
                            }

                            // Preview Grille
                            const gridPreview = document.getElementById('card-grid-preview');
                            if (gridPreview) {
                                gridPreview.style.backgroundImage = v.canvas_canvas_grid_enabled ?
                                    `linear-gradient(rgba(0,0,0,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(0,0,0,0.1) 1px, transparent 1px)` : 'none';
                                gridPreview.style.backgroundSize = v.canvas_canvas_grid_enabled ? `${v.canvas_canvas_grid_size}px ${v.canvas_canvas_grid_size}px` : 'auto';
                                console.log('✅ Grid updated');
                            }

                            // Preview Zoom
                            const zoomPreview = document.getElementById('card-zoom-preview');
                            if (zoomPreview) {
                                zoomPreview.textContent = `${v.canvas_canvas_zoom_default}%`;
                                zoomPreview.style.fontSize = Math.max(12, Math.min(24, v.canvas_canvas_zoom_default / 4)) + 'px';
                                console.log('✅ Zoom updated to:', v.canvas_canvas_zoom_default + '%');
                            }

                            // Preview Performance
                            const perfPreview = document.getElementById('card-perf-preview');
                            if (perfPreview) {
                                perfPreview.textContent = `${v.canvas_canvas_fps_target} FPS`;
                                perfPreview.style.color = v.canvas_canvas_fps_target >= 60 ? '#28a745' : v.canvas_canvas_fps_target >= 30 ? '#ffc107' : '#dc3545';
                                console.log('✅ Performance updated to:', v.canvas_canvas_fps_target + ' FPS');
                            }

                            console.log('✅ All previews refreshed successfully');
                        },

                        // Initialiser le système
                        init: function() {
                            console.log('🎬 Initializing preview system...');

                            // Attendre que le DOM soit complètement chargé
                            const initPreviews = () => {
                                if (document.readyState === 'complete') {
                                    this.refreshPreviews();
                                    this.setupEventListeners();
                                    console.log('✅ Preview system initialized');
                                } else {
                                    setTimeout(initPreviews, 50);
                                }
                            };

                            initPreviews();
                        },
                        // Configurer les event listeners pour les inputs des modales
                        setupEventListeners: function() {
                            // Écouter les changements dans toutes les modales canvas
                            const modalInputs = document.querySelectorAll('[id^="canvas-"][id$="-modal"] input, [id^="canvas-"][id$="-modal"] select');

                            modalInputs.forEach(input => {
                                input.addEventListener('input', (e) => {
                                    const key = e.target.name.replace('pdf_builder_canvas_', 'canvas_canvas_');
                                    let value = e.target.type === 'checkbox' ? e.target.checked : e.target.value;

                                    console.log('Changement détecté (input):', e.target.name, '->', key, '=', value, '(type:', e.target.type + ')');

                                    // Conversion des types
                                    if (e.target.type === 'number') value = parseFloat(value) || 0;
                                    if (['canvas_canvas_shadow_enabled', 'canvas_canvas_grid_enabled', 'canvas_canvas_guides_enabled', 'canvas_canvas_snap_to_grid', 'canvas_canvas_export_transparent', 'canvas_canvas_lazy_loading_editor', 'canvas_canvas_performance_monitoring', 'canvas_canvas_error_reporting'].includes(key)) {
                                        value = value === true || value === '1' || value === 1;
                                        console.log('Conversion boolean pour', key, ':', value);
                                    }

                                    this.updateValue(key, value);
                                });

                                // Pour les selects et autres contrôles qui utilisent 'change'
                                input.addEventListener('change', (e) => {
                                    const key = e.target.name.replace('pdf_builder_canvas_', 'canvas_canvas_');
                                    let value = e.target.type === 'checkbox' ? e.target.checked : e.target.value;

                                    console.log('Changement détecté (change):', e.target.name, '->', key, '=', value, '(type:', e.target.type + ')');

                                    // Conversion des types
                                    if (e.target.type === 'number') value = parseFloat(value) || 0;
                                    if (['canvas_canvas_shadow_enabled', 'canvas_canvas_grid_enabled', 'canvas_canvas_guides_enabled', 'canvas_canvas_snap_to_grid', 'canvas_canvas_export_transparent', 'canvas_canvas_lazy_loading_editor', 'canvas_canvas_performance_monitoring', 'canvas_canvas_error_reporting'].includes(key)) {
                                        value = value === true || value === '1' || value === 1;
                                        console.log('Conversion boolean pour', key, ':', value);
                                    }

                                    this.updateValue(key, value);
                                });
                            });

                            console.log('Preview System: Event listeners setup for', modalInputs.length, 'inputs');
                        }
                    };

                    // ===========================================
                    // SYSTÈME UNIFIÉ DE FORMULAIRES DE MODALES
                    // ===========================================

                    /**
                     * Générateur unifié de formulaires pour les modales
                     * Centralise la création, validation et gestion des formulaires
                     */
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
                                group: config.group || 'default',
                                ...config
                            };
                            return this;
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

                            const fieldId = `pdf_builder_canvas_canvas_${fieldName}`;
                            const fieldNameAttr = `pdf_builder_canvas_canvas_${fieldName}`;
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
                                    const checked = (value == '1' || value === true || value === 'true') ? ' checked' : '';
                                    html += `<label class="toggle-switch">`;
                                    html += `<input type="checkbox" id="${fieldId}" name="${fieldNameAttr}" value="1"${checked}>`;
                                    html += `<span class="toggle-slider"></span>`;
                                    html += `</label>`;
                                    break;

                                case 'range':
                                    html += `<input type="range" id="${fieldId}" name="${fieldNameAttr}" value="${escapedValue}"`;
                                    if (field.min !== undefined) html += ` min="${field.min}"`;
                                    if (field.max !== undefined) html += ` max="${field.max}"`;
                                    if (field.step !== undefined) html += ` step="${field.step}"`;
                                    html += '>';
                                    break;

                                default: // text
                                    html += `<input type="text" id="${fieldId}" name="${fieldNameAttr}" value="${escapedValue}"`;
                                    if (field.placeholder) html += ` placeholder="${escapeHtmlAttr(field.placeholder)}"`;
                                    html += '>';
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
                                const currentValue = previewSystem.values[`canvas_canvas_${fieldName}`];
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
                                const fieldElement = document.querySelector(`[name="pdf_builder_canvas_canvas_${dependentField}"]`);
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

                    // ===========================================
                    // CONFIGURATION CENTRALISÉE DES MODALES
                    // ===========================================

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

                    // Ouvrir une modal avec le nouveau système de génération
                    function openModal(category) {
                        console.log('🚪 [OPEN MODAL] Ouverture modal:', category);

                        // Fermer toute modal existante
                        if (currentModalCategory) {
                            console.log('🔄 [OPEN MODAL] Fermeture modal existante:', currentModalCategory);
                            closeModal();
                        }

                        currentModalCategory = category;
                        console.log('✅ [OPEN MODAL] currentModalCategory défini:', currentModalCategory);

                        // Monitorer l'ouverture
                        modalMonitoring.trackModalOpen(category);

                        // Générer le contenu de la modal
                        const modalContent = formGenerator.generateModalHTML(category);
                        console.log('📝 [OPEN MODAL] Contenu généré, longueur:', modalContent.length);

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

                        console.log('🔍 [OPEN MODAL] Modal body trouvé:', !!modalBody, 'pour ID:', modalId, '(overlay:', !!overlay, ')');

                        if (modalBody) {
                            modalBody.innerHTML = modalContent;
                            console.log('✅ [OPEN MODAL] Contenu inséré');
                        } else {
                            console.error('❌ [OPEN MODAL] Modal body NON trouvé pour:', modalId);
                        }

                        // Afficher la modal en ajoutant la classe 'active' à l'overlay
                        const modal = document.getElementById(modalId);
                        console.log('🔍 [OPEN MODAL] Modal element trouvé:', !!modal);

                        if (modal) {
                            const overlay = document.getElementById(`canvas-${category}-modal-overlay`);
                            console.log('🔍 [OPEN MODAL] Overlay séparé trouvé:', !!overlay, 'ID:', `canvas-${category}-modal-overlay`);

                            if (overlay) {
                                const wasActive = overlay.classList.contains('active');
                                console.log('🔍 [OPEN MODAL] Overlay était actif:', wasActive);

                                if (!wasActive) {
                                    overlay.classList.add('active');
                                    document.body.classList.add('canvas-modal-open');
                                    console.log('✅ [OPEN MODAL] Classe active ajoutée à overlay');
                                }

                                console.log('🎉 [OPEN MODAL] Modal ouverte:', modalId);
                            } else {
                                console.error('❌ [OPEN MODAL] Overlay séparé NON trouvé pour:', category);
                            }
                        } else {
                            console.error('❌ [OPEN MODAL] Modal NON trouvée:', modalId);
                        }
                    }

                    // Gestionnaire centralisé des paramètres des modales
                    const modalSettingsManager = {
                        // Synchroniser les valeurs des champs cachés vers la modal actuelle
                        syncModalValues: function() {
                            if (!currentModalCategory) return;

                            console.log('Synchronisation des valeurs de la modal pour:', currentModalCategory);

                            // Trouver le modal actuellement ouvert
                            const currentModal = document.querySelector(`#canvas-${currentModalCategory}-modal`);
                            if (!currentModal) {
                                console.error('Modal non trouvé pour la catégorie:', currentModalCategory);
                                return;
                            }

                            // Parcourir tous les champs de la modal actuelle
                            const modalInputs = currentModal.querySelectorAll('input, select');

                            modalInputs.forEach(input => {
                                if (!input.name) return;

                                // Trouver le champ caché correspondant
                                const hiddenField = document.querySelector(`input[name="${input.name}"]`);

                                if (hiddenField) {
                                    const currentValue = hiddenField.value;
                                    console.log(`Synchronisation ${input.name} = ${currentValue}`);

                                    if (input.type === 'checkbox') {
                                        input.checked = currentValue === '1' || currentValue === 'true';
                                    } else {
                                        input.value = currentValue;
                                    }

                                    // Gérer les dépendances avec le nouveau système
                                    const fieldName = input.name.replace('pdf_builder_canvas_canvas_', '');
                                    formGenerator.updateFieldDependencies(fieldName, input.checked || input.value);
                                }
                            });
                        },

                        // Sauvegarder les paramètres de la modal actuelle
                        saveModalSettings: function() {
                            if (!currentModalCategory) return;

                            const startTime = Date.now();
                            console.log('Sauvegarde des paramètres pour:', currentModalCategory);

                            // Validation du formulaire
                            const errors = formGenerator.validateForm(currentModalCategory);
                            if (errors.length > 0) {
                                console.error('Erreurs de validation:', errors);
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

                                // Debug log pour les paramètres de grille
                                if (input.name.includes('grid') || input.name.includes('guide') || input.name.includes('snap')) {
                                    console.log('🔍 DEBUG: Grid field collected:', input.name, '=', value, '(type:', input.type, 'checked:', input.checked, ')');
                                }

                                // Mettre à jour la valeur dans le système de previews
                                const previewKey = input.name.replace('pdf_builder_canvas_', 'canvas_canvas_');
                                previewSystem.values[previewKey] = value;

                                // Debug log pour l'ombre
                                if (input.name === 'pdf_builder_canvas_canvas_shadow_enabled') {
                                    console.log('🔍 DEBUG: Shadow enabled value collected:', value, '(type:', typeof value, ')');
                                }

                                // Mettre à jour le champ caché correspondant
                                const hiddenField = document.querySelector(`input[name="${input.name}"]`);
                                if (hiddenField) {
                                    hiddenField.value = value;
                                    console.log(`Champ caché mis à jour: ${input.name} = ${value}`);
                                }
                            });

                            // Rafraîchir toutes les previews avec les nouvelles valeurs
                            previewSystem.refreshPreviews();

                            // Sauvegarder côté serveur via AJAX
                            this.saveToServer(updatedValues);

                            // La modale sera fermée dans le callback AJAX après mise à jour des previews
                            // closeModal(); // Déplacé dans le callback AJAX
                        },

                        // Sauvegarder côté serveur
                        saveToServer: function(values) {
                            const saveStartTime = Date.now();
                            console.log('Sauvegarde côté serveur...');

                            // Debug: vérifier la valeur de l'ombre
                            if (values['pdf_builder_canvas_canvas_shadow_enabled'] !== undefined) {
                                console.log('🔍 DEBUG: Sending shadow_enabled to server:', values['pdf_builder_canvas_canvas_shadow_enabled'], '(type:', typeof values['pdf_builder_canvas_canvas_shadow_enabled'], ')');
                            }

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
                                console.log('Response status:', response.status);
                                return response.json();
                            })
                            .then(data => {
                                const saveTime = Date.now() - saveStartTime;
                                console.log('Response data:', data);
                                if (data.data && data.data.canvas_shadow_enabled !== undefined) {
                                    console.log('🔍 DEBUG: Server returned shadow_enabled:', data.data.canvas_shadow_enabled, '(type:', typeof data.data.canvas_shadow_enabled, ')');
                                }
                                if (data.success) {
                                    console.log('✅ AJAX SUCCESS - Raw response data:', data);
                                    console.log('✅ saved_settings received:', data.saved_settings);

                                    modalMonitoring.trackSaveSuccess(currentModalCategory, saveTime, Object.keys(values).length);
                                    console.log('Paramètres sauvegardés avec succès:', data.saved_count, 'paramètres');

                                    // Mettre à jour previewSystem.values avec les vraies valeurs sauvegardées
                                    if (data.saved_settings && typeof data.saved_settings === 'object') {
                                        console.log('🔄 Updating previewSystem with server values...');

                                        // Mapping des clés courtes vers les clés longues utilisées par previewSystem
                                        const keyMapping = {
                                            'canvas_width': 'canvas_canvas_width',
                                            'canvas_height': 'canvas_canvas_height',
                                            'canvas_dpi': 'canvas_canvas_dpi',
                                            'canvas_format': 'canvas_canvas_format',
                                            'canvas_bg_color': 'canvas_canvas_bg_color',
                                            'canvas_border_color': 'canvas_canvas_border_color',
                                            'canvas_border_width': 'canvas_canvas_border_width',
                                            'canvas_shadow_enabled': 'canvas_canvas_shadow_enabled',
                                            'canvas_container_bg_color': 'canvas_canvas_container_bg_color',
                                            'canvas_grid_enabled': 'canvas_canvas_grid_enabled',
                                            'canvas_grid_size': 'canvas_canvas_grid_size',
                                            'canvas_guides_enabled': 'canvas_canvas_guides_enabled',
                                            'canvas_snap_to_grid': 'canvas_canvas_snap_to_grid',
                                            'canvas_zoom_min': 'canvas_canvas_zoom_min',
                                            'canvas_zoom_max': 'canvas_canvas_zoom_max',
                                            'canvas_zoom_default': 'canvas_canvas_zoom_default',
                                            'canvas_zoom_step': 'canvas_canvas_zoom_step',
                                            'canvas_drag_enabled': 'canvas_canvas_drag_enabled',
                                            'canvas_resize_enabled': 'canvas_canvas_resize_enabled',
                                            'canvas_rotate_enabled': 'canvas_canvas_rotate_enabled',
                                            'canvas_multi_select': 'canvas_canvas_multi_select',
                                            'canvas_selection_mode': 'canvas_canvas_selection_mode',
                                            'canvas_keyboard_shortcuts': 'canvas_canvas_keyboard_shortcuts',
                                            'canvas_export_quality': 'canvas_canvas_export_quality',
                                            'canvas_export_format': 'canvas_canvas_export_format',
                                            'canvas_export_transparent': 'canvas_canvas_export_transparent',
                                            'canvas_fps_target': 'canvas_canvas_fps_target',
                                            'canvas_memory_limit_js': 'canvas_canvas_memory_limit_js',
                                            'canvas_memory_limit_php': 'canvas_canvas_memory_limit_php',
                                            'canvas_lazy_loading_editor': 'canvas_canvas_lazy_loading_editor',
                                            'canvas_performance_monitoring': 'canvas_canvas_performance_monitoring',
                                            'canvas_error_reporting': 'canvas_canvas_error_reporting'
                                        };

                                        // Mettre à jour previewSystem.values avec les valeurs du serveur
                                        let updatedCount = 0;
                                        Object.entries(keyMapping).forEach(([shortKey, longKey]) => {
                                            if (data.saved_settings.hasOwnProperty(shortKey) && data.saved_settings[shortKey] !== undefined && data.saved_settings[shortKey] !== null) {
                                                const oldValue = previewSystem.values[longKey];
                                                previewSystem.values[longKey] = data.saved_settings[shortKey];
                                                console.log(`🔄 Preview system updated: ${longKey} = ${data.saved_settings[shortKey]} (was: ${oldValue})`);
                                                updatedCount++;
                                            }
                                        });
                                        console.log(`🔄 Total values updated from server: ${updatedCount}`);
                                    } else {
                                        console.warn('⚠️ No saved_settings received from server, using local values');
                                    }

                                    // Rafraîchir les previews avec les vraies valeurs
                                    console.log('🔄 Calling refreshPreviews()...');
                                    previewSystem.refreshPreviews();

                                    // Fermer la modale après avoir mis à jour les previews
                                    console.log('🔒 Closing modal after preview update...');
                                    closeModal();

                                    // Afficher une notification de succès sans rechargement de page
                                    if (window.pdfBuilderDeveloper && typeof window.pdfBuilderDeveloper.showSuccess === 'function') {
                                        window.pdfBuilderDeveloper.showSuccess('Paramètres sauvegardés avec succès');
                                    } else {
                                        console.log('✅ Paramètres sauvegardés avec succès');
                                    }

                                    // Mettre à jour les champs cachés du formulaire principal avec les nouvelles valeurs
                                    Object.entries(values).forEach(([key, value]) => {
                                        if (key.startsWith('pdf_builder_canvas_canvas_')) {
                                            const hiddenField = document.querySelector(`input[type="hidden"][name="${key}"]`);
                                            if (hiddenField) {
                                                hiddenField.value = value;
                                                console.log(`🔄 Champ caché mis à jour après sauvegarde modale: ${key} = ${value}`);
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
                        },

                        // Configurer les gestionnaires d'événements pour les dépendances
                        setupDependencyHandlers: function() {
                            if (!currentModalCategory) return;

                            const currentModal = document.querySelector(`#canvas-${currentModalCategory}-modal`);
                            if (!currentModal) return;

                            // Écouter les changements sur les champs qui ont des dépendances
                            Object.keys(formGenerator.fieldDependencies).forEach(masterField => {
                                const masterInput = currentModal.querySelector(`input[name="pdf_builder_canvas_canvas_${masterField}"], select[name="pdf_builder_canvas_canvas_${masterField}"]`);
                                if (masterInput) {
                                    masterInput.addEventListener('change', (e) => {
                                        const isEnabled = e.target.type === 'checkbox' ? e.target.checked : e.target.value;
                                        formGenerator.updateFieldDependencies(masterField, isEnabled);
                                    });
                                }
                            });
                        }
                    };

                    // Fermer la modal
                    function closeModal() {
                        console.log('🚪 [CLOSE MODAL] Fermeture modal, currentModalCategory:', currentModalCategory);

                        if (!currentModalCategory) {
                            console.log('⚠️ [CLOSE MODAL] Aucune modal ouverte');
                            return;
                        }

                        // Monitorer la fermeture
                        modalMonitoring.trackModalClose(currentModalCategory);

                        const modalId = `canvas-${currentModalCategory}-modal`;
                        const modal = document.getElementById(modalId);
                        console.log('🔍 [CLOSE MODAL] Modal trouvée:', !!modal, 'ID:', modalId);

                        // Fermer la modal en retirant la classe 'active' de l'overlay
                        if (modal) {
                            const overlay = document.getElementById(`canvas-${currentModalCategory}-modal-overlay`);
                            console.log('🔍 [CLOSE MODAL] Overlay séparé trouvé:', !!overlay, 'ID:', `canvas-${currentModalCategory}-modal-overlay`);

                            if (overlay) {
                                const wasActive = overlay.classList.contains('active');
                                console.log('🔍 [CLOSE MODAL] Overlay était actif:', wasActive);

                                if (wasActive) {
                                    overlay.classList.remove('active');
                                    document.body.classList.remove('canvas-modal-open');
                                    console.log('✅ [CLOSE MODAL] Classe active retirée');
                                } else {
                                    console.log('⚠️ [CLOSE MODAL] Overlay déjà inactif');
                                }

                            } else {
                                console.error('❌ [CLOSE MODAL] Overlay séparé NON trouvé pour:', currentModalCategory);
                            }
                        } else {
                            console.error('❌ [CLOSE MODAL] Modal NON trouvée:', modalId);
                        }

                        console.log('🔄 [CLOSE MODAL] Reset currentModalCategory');
                        currentModalCategory = null;
                    }

                    // Sauvegarder les paramètres
                    function saveModalSettings() {
                        modalSettingsManager.saveModalSettings();
                    }

                    // Initialisation : fermer toutes les modales au départ
                    document.addEventListener('DOMContentLoaded', function() {
                        console.log('🔍 DEBUG: Fermeture de toutes les modales au démarrage');
                        const allOverlays = document.querySelectorAll('.canvas-modal-overlay');
                        allOverlays.forEach(overlay => {
                            overlay.classList.remove('active');
                        });
                        document.body.classList.remove('canvas-modal-open');
                        console.log('🔍 DEBUG: Toutes les modales fermées au démarrage');
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

                    console.log('Système de modal PDF Builder initialisé');

                    // ===========================================
                    // PRÉVENTION DU RECHARGEMENT DE PAGE
                    // ===========================================

                    // Empêcher la soumission du formulaire principal (canvas-form)
                    const canvasForm = document.getElementById('canvas-form');
                    if (canvasForm) {
                        canvasForm.addEventListener('submit', function(e) {
                            e.preventDefault();
                            console.log('🚫 Soumission du formulaire principal empêchée - toutes les sauvegardes passent par AJAX');
                            return false;
                        });
                        console.log('✅ Prévention du rechargement de page activée');
                    }

                    // ===========================================
                    // FONCTIONS GLOBALES DE MONITORING

                    // Fonction globale pour accéder au monitoring depuis la console
                    window.pdfBuilderMonitoring = {
                        showDashboard: () => modalMonitoring.showDashboard(),
                        getMetrics: () => modalMonitoring.getMetrics(),
                        getReport: () => modalMonitoring.generateReport(),
                        clearHistory: () => {
                            modalMonitoring.history = [];
                            console.log('📝 Historique de monitoring effacé');
                        },
                        exportData: () => {
                            const data = {
                                metrics: modalMonitoring.getMetrics(),
                                report: modalMonitoring.generateReport(),
                                timestamp: new Date().toISOString()
                            };
                            console.log('📊 Données de monitoring exportées:', data);
                            return data;
                        }
                    };

                    console.log('🔍 Monitoring accessible via: window.pdfBuilderMonitoring.showDashboard()');

                    // ===========================================
                    // SYNCHRONISATION DES TOGGLES AVEC CHAMPS CACHÉS
                    // ===========================================

                    // Fonction pour synchroniser les toggles avec les champs cachés
                    function syncTogglesWithHiddenFields() {
                        // Écouter tous les changements sur les checkboxes et toggles
                        document.addEventListener('change', function(e) {
                            const target = e.target;

                            // Vérifier si c'est une checkbox ou un toggle
                            if (target.type === 'checkbox' || target.classList.contains('toggle-checkbox')) {
                                const fieldName = target.name || target.getAttribute('data-field');

                                if (fieldName && fieldName.startsWith('pdf_builder_canvas_canvas_')) {
                                    // Trouver le champ caché correspondant
                                    const hiddenField = document.querySelector(`input[type="hidden"][name="${fieldName}"]`);

                                    if (hiddenField) {
                                        // Mettre à jour la valeur du champ caché
                                        const newValue = target.checked ? '1' : '0';
                                        hiddenField.value = newValue;

                                        console.log(`🔄 Toggle synchronisé: ${fieldName} = ${newValue}`);
                                    }
                                }
                            }
                        });

                        console.log('🔗 Synchronisation toggles ↔ champs cachés activée');
                    }

                    // Initialiser la synchronisation
                    syncTogglesWithHiddenFields();

                    // Initialiser le système de previews dynamiques
                    previewSystem.init();

                    // === DIAGNOSTIC COMPLET DE L'ONGLET CANVAS ===
                    function runCanvasDiagnostic() {
                        console.log('🔍 === DIAGNOSTIC COMPLET CANVAS ===');

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
                        console.log(`📋 Cartes trouvées: ${results.cards}`);

                        cards.forEach((card, index) => {
                            const category = card.dataset.category;
                            const button = card.querySelector('.canvas-configure-btn');
                            if (!category) results.issues.push(`Carte ${index}: pas de data-category`);
                            if (!button) results.issues.push(`Carte ${index} (${category}): pas de bouton configurer`);
                            else results.buttons++;
                        });

                        // 2. Vérifier les modales
                        const modalIds = ['canvas-dimensions-modal', 'canvas-apparence-modal', 'canvas-grille-modal',
                                        'canvas-zoom-modal', 'canvas-interactions-modal', 'canvas-export-modal',
                                        'canvas-performance-modal', 'canvas-debug-modal'];

                        modalIds.forEach(modalId => {
                            const modal = document.getElementById(modalId);
                            if (modal) results.modals++;
                            else results.issues.push(`Modale manquante: ${modalId}`);
                        });

                        // 3. Vérifier les champs cachés
                        const hiddenFields = document.querySelectorAll('input[type="hidden"][name^="pdf_builder_canvas_canvas_"]');
                        results.hiddenFields = hiddenFields.length;
                        console.log(`🔒 Champs cachés: ${results.hiddenFields}`);

                        // 4. Vérifier les éléments de preview
                        const previewElements = ['card-canvas-width', 'card-canvas-height', 'card-canvas-dpi',
                                               'card-bg-preview', 'card-border-preview', 'card-grid-preview',
                                               'card-zoom-preview', 'card-perf-preview'];

                        previewElements.forEach(id => {
                            const el = document.getElementById(id);
                            if (el) results.previewElements++;
                            else results.issues.push(`Élément preview manquant: ${id}`);
                        });

                        // 5. Vérifier previewSystem
                        if (typeof previewSystem === 'undefined') {
                            results.issues.push('previewSystem non défini');
                        } else {
                            console.log('✅ previewSystem défini');
                            if (!previewSystem.values) results.issues.push('previewSystem.values manquant');
                            if (!previewSystem.refreshPreviews) results.issues.push('previewSystem.refreshPreviews manquant');
                        }

                        // 6. Vérifier formGenerator
                        if (typeof formGenerator === 'undefined') {
                            results.issues.push('formGenerator non défini');
                        } else {
                            console.log('✅ formGenerator défini');
                            if (!formGenerator.generateModalHTML) results.issues.push('formGenerator.generateModalHTML manquant');
                        }

                        // 7. Vérifier modalSettingsManager
                        if (typeof modalSettingsManager === 'undefined') {
                            results.issues.push('modalSettingsManager non défini');
                        } else {
                            console.log('✅ modalSettingsManager défini');
                        }

                        // Résumé
                        console.log('📊 RÉSULTATS DIAGNOSTIC:');
                        console.log(`   Cartes: ${results.cards}/8`);
                        console.log(`   Boutons: ${results.buttons}/8`);
                        console.log(`   Modales: ${results.modals}/8`);
                        console.log(`   Champs cachés: ${results.hiddenFields}`);
                        console.log(`   Éléments preview: ${results.previewElements}/8`);

                        if (results.issues.length === 0) {
                            console.log('✅ AUCUN PROBLÈME DÉTECTÉ');
                        } else {
                            console.error('❌ PROBLÈMES DÉTECTÉS:');
                            results.issues.forEach(issue => console.error(`   - ${issue}`));
                        }

                        return results;
                    }

                    // Lancer le diagnostic automatiquement
                    setTimeout(runCanvasDiagnostic, 1000);

                    console.log('✅ [JS INIT] Script modales chargé avec succès - ' + new Date().toISOString());
                    console.log('🔍 [JS INIT] Fonctions disponibles: openModal, closeModal, saveModalSettings');
                    console.log('🔍 [JS INIT] Variables globales: currentModalCategory =', currentModalCategory);

                })();
            </script>
        </div>
    </div>
</div>


