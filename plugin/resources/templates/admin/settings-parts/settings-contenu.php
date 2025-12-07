<?php
/**
 * PDF Builder Pro - Content Settings Tab
 * Canvas and design configuration settings
 * Updated: 2025-12-03
 */

// Inclure les fonctions helper nécessaires pour tous les onglets
require_once __DIR__ . '/settings-helpers.php';

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

                <form method="post" id="canvas-form">
                    <?php wp_nonce_field('pdf_builder_canvas_nonce', 'pdf_builder_canvas_nonce'); ?>
                    <input type="hidden" name="submit_canvas" value="1">

                    <!-- Champs cachés pour la sauvegarde centralisée des paramètres -->
                    <input type="hidden" name="pdf_builder_canvas_width" value="<?php echo esc_attr(get_option('pdf_builder_canvas_width', '794')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_height" value="<?php echo esc_attr(get_option('pdf_builder_canvas_height', '1123')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_dpi" value="<?php echo esc_attr(get_option('pdf_builder_canvas_dpi', '96')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_format" value="<?php echo esc_attr(get_option('pdf_builder_canvas_format', 'A4')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_bg_color" value="<?php echo esc_attr(get_option('pdf_builder_canvas_bg_color', '#ffffff')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_border_color" value="<?php echo esc_attr(get_option('pdf_builder_canvas_border_color', '#cccccc')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_border_width" value="<?php echo esc_attr(get_option('pdf_builder_canvas_border_width', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_shadow_enabled" value="<?php echo esc_attr(get_option('pdf_builder_canvas_shadow_enabled', '0')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_container_bg_color" value="<?php echo esc_attr(get_option('pdf_builder_canvas_container_bg_color', '#f8f9fa')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_grid_enabled" value="<?php echo esc_attr(get_option('pdf_builder_canvas_grid_enabled', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_grid_size" value="<?php echo esc_attr(get_option('pdf_builder_canvas_grid_size', '20')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_guides_enabled" value="<?php echo esc_attr(get_option('pdf_builder_canvas_guides_enabled', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_snap_to_grid" value="<?php echo esc_attr(get_option('pdf_builder_canvas_snap_to_grid', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_zoom_min" value="<?php echo esc_attr(get_option('pdf_builder_canvas_zoom_min', '25')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_zoom_max" value="<?php echo esc_attr(get_option('pdf_builder_canvas_zoom_max', '500')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_zoom_default" value="<?php echo esc_attr(get_option('pdf_builder_canvas_zoom_default', '100')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_zoom_step" value="<?php echo esc_attr(get_option('pdf_builder_canvas_zoom_step', '25')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_export_quality" value="<?php echo esc_attr(get_option('pdf_builder_canvas_export_quality', '90')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_export_format" value="<?php echo esc_attr(get_option('pdf_builder_canvas_export_format', 'png')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_export_transparent" value="<?php echo esc_attr(get_option('pdf_builder_canvas_export_transparent', '0')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_drag_enabled" value="<?php echo esc_attr(get_option('pdf_builder_canvas_drag_enabled', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_resize_enabled" value="<?php echo esc_attr(get_option('pdf_builder_canvas_resize_enabled', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_rotate_enabled" value="<?php echo esc_attr(get_option('pdf_builder_canvas_rotate_enabled', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_multi_select" value="<?php echo esc_attr(get_option('pdf_builder_canvas_multi_select', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_selection_mode" value="<?php echo esc_attr(get_option('pdf_builder_canvas_selection_mode', 'single')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_keyboard_shortcuts" value="<?php echo esc_attr(get_option('pdf_builder_canvas_keyboard_shortcuts', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_fps_target" value="<?php echo esc_attr(get_option('pdf_builder_canvas_fps_target', '60')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_memory_limit_js" value="<?php echo esc_attr(get_option('pdf_builder_canvas_memory_limit_js', '50')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_response_timeout" value="<?php echo esc_attr(get_option('pdf_builder_canvas_response_timeout', '5000')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_lazy_loading_editor" value="<?php echo esc_attr(get_option('pdf_builder_canvas_lazy_loading_editor', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_preload_critical" value="<?php echo esc_attr(get_option('pdf_builder_canvas_preload_critical', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_lazy_loading_plugin" value="<?php echo esc_attr(get_option('pdf_builder_canvas_lazy_loading_plugin', '1')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_debug_enabled" value="<?php echo esc_attr(get_option('pdf_builder_canvas_debug_enabled', '0')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_performance_monitoring" value="<?php echo esc_attr(get_option('pdf_builder_canvas_performance_monitoring', '0')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_error_reporting" value="<?php echo esc_attr(get_option('pdf_builder_canvas_error_reporting', '0')); ?>">
                    <input type="hidden" name="pdf_builder_canvas_memory_limit_php" value="<?php echo esc_attr(get_option('pdf_builder_canvas_memory_limit_php', '128')); ?>">

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
                                <div id="card-bg-preview" class="color-preview bg" title="Fond"></div>
                                <div id="card-border-preview" class="color-preview border" title="Bordure"></div>
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
                                        <span>10% - 500%</span>
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
                                            <span class="metric-value">256MB</span>
                                        </div>
                                        <div class="metric-item">
                                            <span class="metric-label">RAM PHP</span>
                                            <span class="metric-value">256MB</span>
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



            <!-- CSS pour les modales - DESIGN SIMPLE ET ÉLÉGANT -->
            <style>
                /* Styles pour les modales - DESIGN SIMPLE ET ÉLÉGANT */
                .modal-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    width: 100vw;
                    height: 100vh;
                    z-index: 999999;
                    display: none;
                    overflow: visible;
                    margin: 0;
                    padding: 0;
                    border: none;
                    outline: none;
                    box-sizing: border-box;
                }

                body > .modal-fullscreen {
                    position: fixed !important;
                    top: 0 !important;
                    left: 0 !important;
                    right: 0 !important;
                    bottom: 0 !important;
                    width: 100vw !important;
                    height: 100vh !important;
                    z-index: 999999 !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    border: none !important;
                    outline: none !important;
                    overflow: visible !important;
                    box-sizing: border-box !important;
                }

                .modal-backdrop {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.6);
                    backdrop-filter: blur(4px);
                    z-index: 1;
                }
                    position: relative;
                    background: #ffffff;
                    border-radius: 16px;
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
                    max-width: 600px;
                    width: 90vw;
                    max-height: 80vh;
                    overflow: hidden;
                    z-index: 10001;
                    border: 1px solid rgba(0, 0, 0, 0.1);
                    animation: modalFadeIn 0.3s ease-out;
                }

                @keyframes modalFadeIn {
                    from {
                        opacity: 0;
                        transform: translateY(-20px) scale(0.95);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0) scale(1);
                    }
                }

                .modal-header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 24px 28px;
                    border-bottom: 1px solid #e5e7eb;
                    background: #f9fafb;
                }

                .modal-header h2 {
                    margin: 0;
                    font-size: 1.375rem;
                    font-weight: 600;
                    color: #111827;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }

                .modal-header h2::before {
                    content: '⚙️';
                    font-size: 1.1em;
                }

                .modal-close {
                    background: #f3f4f6;
                    border: none;
                    font-size: 18px;
                    color: #6b7280;
                    cursor: pointer;
                    padding: 8px;
                    border-radius: 8px;
                    transition: all 0.2s ease;
                }

                .modal-close:hover {
                    background: #ef4444;
                    color: white;
                }

                .modal-body {
                    padding: 28px;
                    max-height: 60vh;
                    overflow-y: auto;
                }

                .modal-footer {
                    display: flex;
                    justify-content: flex-end;
                    gap: 12px;
                    padding: 20px 28px 24px;
                    border-top: 1px solid #e5e7eb;
                    background: #f9fafb;
                }

                .modal-form-grid {
                    display: grid;
                    gap: 20px;
                }

                .form-group {
                    display: flex;
                    flex-direction: column;
                    gap: 8px;
                }

                .form-group label {
                    font-weight: 500;
                    color: #374151;
                    font-size: 14px;
                    display: flex;
                    align-items: center;
                    gap: 6px;
                }

                .form-group label::before {
                    content: '•';
                    color: #3b82f6;
                    font-weight: bold;
                }

                .form-group input,
                .form-group select {
                    padding: 10px 12px;
                    border: 2px solid #e5e7eb;
                    border-radius: 8px;
                    font-size: 14px;
                    transition: border-color 0.2s ease, box-shadow 0.2s ease;
                    background: white;
                }

                .form-group input:focus,
                .form-group select:focus {
                    outline: none;
                    border-color: #3b82f6;
                    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
                }

                .form-group input:disabled,
                .form-group select:disabled {
                    background: #f9fafb;
                    opacity: 0.6;
                    cursor: not-allowed;
                }

                .field-description {
                    font-size: 12px;
                    color: #6b7280;
                    margin: 0;
                    line-height: 1.4;
                }

                .toggle-switch {
                    position: relative;
                    display: inline-block;
                    width: 48px;
                    height: 24px;
                    margin-left: 8px;
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
                    background: #d1d5db;
                    transition: background-color 0.3s ease;
                    border-radius: 24px;
                }

                .toggle-slider:before {
                    position: absolute;
                    content: "";
                    height: 18px;
                    width: 18px;
                    left: 3px;
                    bottom: 3px;
                    background: white;
                    transition: transform 0.3s ease;
                    border-radius: 50%;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                }

                input:checked + .toggle-slider {
                    background: #10b981;
                }

                input:checked + .toggle-slider:before {
                    transform: translateX(24px);
                }

                .toggle-switch.disabled {
                    opacity: 0.5;
                    cursor: not-allowed;
                }

                .toggle-switch.disabled .toggle-slider {
                    cursor: not-allowed;
                }

                /* Boutons de la modale */
                .modal-footer button {
                    padding: 10px 20px;
                    border: none;
                    border-radius: 8px;
                    font-size: 14px;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }

                .modal-footer button:first-child {
                    background: #f3f4f6;
                    color: #374151;
                    border: 1px solid #d1d5db;
                }

                .modal-footer button:first-child:hover {
                    background: #e5e7eb;
                }

                .modal-footer button:last-child {
                    background: #3b82f6;
                    color: white;
                }

                .modal-footer button:last-child:hover {
                    background: #2563eb;
                }

                .form-group input[type="color"] {
                    width: 60px;
                    height: 40px;
                    padding: 2px;
                    border: 2px solid #e5e7eb;
                    border-radius: 8px;
                    cursor: pointer;
                    background: white;
                    transition: border-color 0.2s ease, box-shadow 0.2s ease;
                }

                .form-group input[type="color"]:focus {
                    outline: none;
                    border-color: #3b82f6;
                    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
                }

                .form-group input[type="color"]:hover {
                    border-color: #9ca3af;
                }

                /* Prévisualisation des couleurs dans les cartes */
                .color-preview {
                    display: inline-block;
                    width: 24px;
                    height: 24px;
                    border-radius: 4px;
                    border: 2px solid #e5e7eb;
                    margin: 2px;
                    cursor: pointer;
                    transition: transform 0.2s ease, box-shadow 0.2s ease;
                }

                .color-preview:hover {
                    transform: scale(1.1);
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
                }

                .color-preview.bg {
                    background-color: #ffffff;
                }

                .color-preview.border {
                    border-style: solid;
                    border-width: 3px;
                    background-color: transparent;
                }
            </style>

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
                            canvas_width: <?php echo json_encode($settings['pdf_builder_canvas_width'] ?? '794'); ?>,
                            canvas_height: <?php echo json_encode($settings['pdf_builder_canvas_height'] ?? '1123'); ?>,
                            canvas_dpi: <?php echo json_encode($settings['pdf_builder_canvas_dpi'] ?? '96'); ?>,
                            canvas_format: <?php echo json_encode($settings['pdf_builder_canvas_format'] ?? 'A4'); ?>,
                            canvas_bg_color: <?php echo json_encode($settings['pdf_builder_canvas_bg_color'] ?? '#ffffff'); ?>,
                            canvas_border_color: <?php echo json_encode($settings['pdf_builder_canvas_border_color'] ?? '#cccccc'); ?>,
                            canvas_border_width: <?php echo json_encode($settings['pdf_builder_canvas_border_width'] ?? '1'); ?>,
                            canvas_shadow_enabled: <?php echo json_encode(($settings['pdf_builder_canvas_shadow_enabled'] ?? '0') === '1'); ?>,
                            canvas_grid_enabled: <?php echo json_encode(($settings['pdf_builder_canvas_grid_enabled'] ?? '1') === '1'); ?>,
                            canvas_grid_size: <?php echo json_encode($settings['pdf_builder_canvas_grid_size'] ?? '20'); ?>,
                            canvas_guides_enabled: <?php echo json_encode(($settings['pdf_builder_canvas_guides_enabled'] ?? '1') === '1'); ?>,
                            canvas_snap_to_grid: <?php echo json_encode(($settings['pdf_builder_canvas_snap_to_grid'] ?? '1') === '1'); ?>,
                            canvas_zoom_min: <?php echo json_encode($settings['pdf_builder_canvas_zoom_min'] ?? '25'); ?>,
                            canvas_zoom_max: <?php echo json_encode($settings['pdf_builder_canvas_zoom_max'] ?? '500'); ?>,
                            canvas_zoom_default: <?php echo json_encode($settings['pdf_builder_canvas_zoom_default'] ?? '100'); ?>,
                            canvas_zoom_step: <?php echo json_encode($settings['pdf_builder_canvas_zoom_step'] ?? '25'); ?>,
                            canvas_export_quality: <?php echo json_encode($settings['pdf_builder_canvas_export_quality'] ?? '90'); ?>,
                            canvas_export_format: <?php echo json_encode($settings['pdf_builder_canvas_export_format'] ?? 'png'); ?>,
                            canvas_export_transparent: <?php echo json_encode(($settings['pdf_builder_canvas_export_transparent'] ?? '0') === '1'); ?>,
                            canvas_drag_enabled: <?php echo json_encode(($settings['pdf_builder_canvas_drag_enabled'] ?? '1') === '1'); ?>,
                            canvas_resize_enabled: <?php echo json_encode(($settings['pdf_builder_canvas_resize_enabled'] ?? '1') === '1'); ?>,
                            canvas_rotate_enabled: <?php echo json_encode(($settings['pdf_builder_canvas_rotate_enabled'] ?? '1') === '1'); ?>,
                            canvas_multi_select: <?php echo json_encode(($settings['pdf_builder_canvas_multi_select'] ?? '1') === '1'); ?>,
                            canvas_selection_mode: <?php echo json_encode($settings['pdf_builder_canvas_selection_mode'] ?? 'single'); ?>,
                            canvas_keyboard_shortcuts: <?php echo json_encode(($settings['pdf_builder_canvas_keyboard_shortcuts'] ?? '1') === '1'); ?>,
                            canvas_fps_target: <?php echo json_encode($settings['pdf_builder_canvas_fps_target'] ?? '60'); ?>,
                            canvas_memory_limit_js: <?php echo json_encode($settings['pdf_builder_canvas_memory_limit_js'] ?? '50'); ?>,
                            canvas_response_timeout: <?php echo json_encode($settings['pdf_builder_canvas_response_timeout'] ?? '5000'); ?>,
                            canvas_lazy_loading_editor: <?php echo json_encode(($settings['pdf_builder_canvas_lazy_loading_editor'] ?? '1') === '1'); ?>,
                            canvas_preload_critical: <?php echo json_encode(($settings['pdf_builder_canvas_preload_critical'] ?? '1') === '1'); ?>,
                            canvas_lazy_loading_plugin: <?php echo json_encode(($settings['pdf_builder_canvas_lazy_loading_plugin'] ?? '1') === '1'); ?>,
                            canvas_debug_enabled: <?php echo json_encode(($settings['pdf_builder_canvas_debug_enabled'] ?? '0') === '1'); ?>,
                            canvas_performance_monitoring: <?php echo json_encode(($settings['pdf_builder_canvas_performance_monitoring'] ?? '0') === '1'); ?>,
                            canvas_error_reporting: <?php echo json_encode(($settings['pdf_builder_canvas_error_reporting'] ?? '0') === '1'); ?>,
                            canvas_memory_limit_php: <?php echo json_encode($settings['pdf_builder_canvas_memory_limit_php'] ?? '128'); ?>
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
                            const changedFields = Object.keys(this.values);

                            // Monitorer la mise à jour de preview
                            modalMonitoring.trackPreviewUpdate(changedFields);

                            const v = this.values;

                            // Preview Dimensions
                            const widthEl = document.getElementById('card-canvas-width');
                            const heightEl = document.getElementById('card-canvas-height');
                            const dpiEl = document.getElementById('card-canvas-dpi');

                            if (widthEl) widthEl.textContent = v.canvas_width;
                            if (heightEl) heightEl.textContent = v.canvas_height;
                            if (dpiEl) {
                                const format = v.canvas_format || 'A4';
                                const widthMM = this.calculateMM(v.canvas_width, v.canvas_dpi);
                                const heightMM = this.calculateMM(v.canvas_height, v.canvas_dpi);
                                dpiEl.textContent = `${v.canvas_dpi} DPI - ${format} (${widthMM}×${heightMM}mm)`;
                            }

                            // Preview Apparence
                            const bgPreview = document.getElementById('card-bg-preview');
                            const borderPreview = document.getElementById('card-border-preview');

                            if (bgPreview) bgPreview.style.backgroundColor = v.canvas_bg_color;
                            if (borderPreview) {
                                borderPreview.style.borderColor = v.canvas_border_color;
                                borderPreview.style.borderWidth = v.canvas_border_width + 'px';
                                borderPreview.style.boxShadow = v.canvas_shadow_enabled ? '0 4px 8px rgba(0,0,0,0.2)' : 'none';
                            }

                            // Preview Grille
                            const gridPreview = document.getElementById('card-grid-preview');
                            if (gridPreview) {
                                gridPreview.style.backgroundImage = v.canvas_grid_enabled ?
                                    `linear-gradient(rgba(0,0,0,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(0,0,0,0.1) 1px, transparent 1px)` : 'none';
                                gridPreview.style.backgroundSize = v.canvas_grid_enabled ? `${v.canvas_grid_size}px ${v.canvas_grid_size}px` : 'auto';
                            }

                            // Preview Zoom
                            const zoomPreview = document.getElementById('card-zoom-preview');
                            if (zoomPreview) {
                                zoomPreview.textContent = `${v.canvas_zoom_default}%`;
                                zoomPreview.style.fontSize = Math.max(12, Math.min(24, v.canvas_zoom_default / 4)) + 'px';
                            }

                            // Preview Performance
                            const perfPreview = document.getElementById('card-perf-preview');
                            if (perfPreview) {
                                perfPreview.textContent = `${v.canvas_fps_target} FPS`;
                                perfPreview.style.color = v.canvas_fps_target >= 60 ? '#28a745' : v.canvas_fps_target >= 30 ? '#ffc107' : '#dc3545';
                            }

                            console.log('Preview System: All previews refreshed');
                        },

                        // Initialiser le système
                        init: function() {
                            this.refreshPreviews();
                            this.setupEventListeners();
                        },
                        // Configurer les event listeners pour les inputs des modales
                        setupEventListeners: function() {
                            // Écouter les changements dans toutes les modales canvas
                            const modalInputs = document.querySelectorAll('[id^="canvas-"][id$="-modal"] input, [id^="canvas-"][id$="-modal"] select');

                            modalInputs.forEach(input => {
                                input.addEventListener('input', (e) => {
                                    const key = e.target.name.replace('pdf_builder_canvas_', 'canvas_');
                                    let value = e.target.type === 'checkbox' ? e.target.checked : e.target.value;

                                    // Conversion des types
                                    if (e.target.type === 'number') value = parseFloat(value) || 0;
                                    if (['canvas_shadow_enabled', 'canvas_grid_enabled', 'canvas_guides_enabled', 'canvas_snap_to_grid', 'canvas_export_transparent', 'canvas_lazy_loading_editor', 'canvas_performance_monitoring', 'canvas_error_reporting'].includes(key)) {
                                        value = value === true || value === '1' || value === 1;
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
                                const currentValue = previewSystem.values[`canvas_${fieldName}`];
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
                                const fieldName = field.name.replace('pdf_builder_canvas_', '');
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
                                const fieldElement = document.querySelector(`[name="pdf_builder_canvas_${dependentField}"]`);
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
                        console.log('Ouverture de la modal pour:', category);

                        currentModalCategory = category;

                        // Monitorer l'ouverture
                        modalMonitoring.trackModalOpen(category);

                        // Générer le contenu de la modal avec le nouveau système
                        const modalContent = formGenerator.generateModalHTML(category);

                        // Insérer le contenu dans la modal spécifique à la catégorie
                        const modalId = `canvas-${category}-modal`;
                        const modalBody = document.querySelector(`#${modalId} .modal-body`);
                        if (modalBody) {
                            modalBody.innerHTML = modalContent;
                        }

                        // Afficher la modal
                        const modal = document.getElementById(modalId);
                        if (modal) {
                            console.log('🔍 DEBUG: Modal trouvée:', modalId);
                            console.log('🔍 DEBUG: Position actuelle:', modal.parentElement ? modal.parentElement.tagName : 'null');

                            // Créer une nouvelle modale directement dans le body
                            const fullscreenModal = document.createElement('div');
                            fullscreenModal.id = modalId + '-fullscreen';
                            fullscreenModal.className = 'modal-overlay modal-fullscreen';
                            fullscreenModal.innerHTML = modal.innerHTML;

                            // Appliquer tous les styles nécessaires
                            Object.assign(fullscreenModal.style, {
                                position: 'fixed',
                                top: '0',
                                left: '0',
                                width: '100vw',
                                height: '100vh',
                                zIndex: '999999',
                                margin: '0',
                                padding: '0',
                                border: 'none',
                                outline: 'none',
                                overflow: 'visible',
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                backgroundColor: 'rgba(0, 0, 0, 0.5)',
                                backdropFilter: 'blur(2px)'
                            });

                            // Ajouter au body
                            document.body.appendChild(fullscreenModal);
                            console.log('🔍 DEBUG: Modal ajoutée au body');

                            // Cacher l'originale
                            modal.style.display = 'none';

                            // Fonction de fermeture
                            const closeModal = () => {
                                console.log('🔍 DEBUG: Fermeture de la modal fullscreen');
                                fullscreenModal.remove();
                                modal.style.display = 'none';
                                modal.classList.remove('show');
                                currentModalCategory = null;
                            };

                            // Gérer la fermeture
                            const closeBtn = fullscreenModal.querySelector('.modal-close');
                            const backdrop = fullscreenModal.querySelector('.modal-backdrop');

                            if (closeBtn) closeBtn.onclick = closeModal;
                            if (backdrop) backdrop.onclick = closeModal;

                            // Gérer la sauvegarde dans la modale fullscreen
                            const saveBtn = fullscreenModal.querySelector('.modal-save');
                            if (saveBtn) {
                                saveBtn.onclick = (e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    console.log('🔍 DEBUG: Sauvegarde depuis modale fullscreen');
                                    saveModalSettings();
                                    setTimeout(() => {
                                        closeModal();
                                    }, 500); // Délai pour permettre la sauvegarde AJAX
                                };
                            }

                            // Écouter Échap
                            const escapeHandler = (e) => {
                                if (e.key === 'Escape') {
                                    closeModal();
                                    document.removeEventListener('keydown', escapeHandler);
                                }
                            };
                            document.addEventListener('keydown', escapeHandler);

                            console.log('🔍 DEBUG: Modal fullscreen affichée');
                        }

                        // Synchroniser les valeurs des champs
                        modalSettingsManager.syncModalValues();

                        // Configurer les gestionnaires d'événements pour les dépendances
                        modalSettingsManager.setupDependencyHandlers();

                        console.log('Modal ouverte avec succès pour:', category);
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
                                    const fieldName = input.name.replace('pdf_builder_canvas_', '');
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

                                // Mettre à jour la valeur dans le système de previews
                                const previewKey = input.name.replace('pdf_builder_canvas_', 'canvas_');
                                previewSystem.values[previewKey] = value;

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

                            console.log('Paramètres sauvegardés et previews mises à jour');
                            closeModal();
                        },

                        // Sauvegarder côté serveur
                        saveToServer: function(values) {
                            const saveStartTime = Date.now();
                            console.log('Sauvegarde côté serveur...');

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
                                if (data.success) {
                                    modalMonitoring.trackSaveSuccess(currentModalCategory, saveTime, Object.keys(values).length);
                                    console.log('Paramètres sauvegardés avec succès:', data.saved_count, 'paramètres');
                                } else {
                                    modalMonitoring.trackSaveError(currentModalCategory, data.data?.message || data.message || 'Erreur inconnue', saveTime);
                                    console.error('Erreur lors de la sauvegarde:', data.data?.message || data.message || 'Erreur inconnue');
                                }
                            })
                            .catch(error => {
                                const saveTime = Date.now() - saveStartTime;
                                modalMonitoring.trackSaveError(currentModalCategory, error.message || 'Erreur réseau', saveTime);
                                console.error('Erreur AJAX lors de la sauvegarde:', error);
                            });
                        },

                        // Configurer les gestionnaires d'événements pour les dépendances
                        setupDependencyHandlers: function() {
                            if (!currentModalCategory) return;

                            const currentModal = document.querySelector(`#canvas-${currentModalCategory}-modal`);
                            if (!currentModal) return;

                            // Écouter les changements sur les champs qui ont des dépendances
                            Object.keys(formGenerator.fieldDependencies).forEach(masterField => {
                                const masterInput = currentModal.querySelector(`input[name="pdf_builder_canvas_${masterField}"], select[name="pdf_builder_canvas_${masterField}"]`);
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
                        if (!currentModalCategory) return;

                        // Monitorer la fermeture
                        modalMonitoring.trackModalClose(currentModalCategory);

                        const modalId = `canvas-${currentModalCategory}-modal`;
                        const modal = document.getElementById(modalId);
                        if (modal) {
                            modal.style.display = 'none';
                            modal.classList.remove('show');
                        }
                        currentModalCategory = null;
                        console.log('Modal fermée');
                    }

                    // Sauvegarder les paramètres
                    function saveModalSettings() {
                        modalSettingsManager.saveModalSettings();
                    }

                    // Gestionnaire d'événements pour les boutons de configuration
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
                        if (e.target.closest('.modal-close') || e.target.closest('.modal-cancel')) {
                            closeModal();
                            return;
                        }

                        // Clic sur l'overlay (backdrop)
                        if (e.target.classList.contains('modal-overlay') || e.target.classList.contains('modal-backdrop')) {
                            closeModal();
                            return;
                        }

                        // Bouton de sauvegarde
                        if (e.target.closest('.modal-save')) {
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
                            if (modal && modal.style.display !== 'none') {
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
                    // FONCTIONS GLOBALES DE MONITORING
                    // ===========================================

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

                    // Initialiser le système de previews dynamiques
                    previewSystem.init();
                })();
            </script>

            <!-- Modales individuelles pour chaque catégorie -->
            <div id="canvas-dimensions-modal" class="modal-overlay">
                <div class="modal-backdrop"></div>
                <div class="modal-container">
                    <div class="modal-header">
                        <h2>📐 Dimensions & Format</h2>
                        <button type="button" class="modal-close" aria-label="Fermer">&times;</button>
                    </div>
                    <div class="modal-body">
                        <!-- Contenu généré dynamiquement -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="button button-secondary modal-cancel">Annuler</button>
                        <button type="button" class="button button-primary modal-save">Enregistrer</button>
                    </div>
                </div>
            </div>

            <div id="canvas-apparence-modal" class="modal-overlay">
                <div class="modal-backdrop"></div>
                <div class="modal-container">
                    <div class="modal-header">
                        <h2>🎨 Apparence</h2>
                        <button type="button" class="modal-close" aria-label="Fermer">&times;</button>
                    </div>
                    <div class="modal-body">
                        <!-- Contenu généré dynamiquement -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="button button-secondary modal-cancel">Annuler</button>
                        <button type="button" class="button button-primary modal-save">Enregistrer</button>
                    </div>
                </div>
            </div>

            <div id="canvas-grille-modal" class="modal-overlay">
                <div class="modal-backdrop"></div>
                <div class="modal-container">
                    <div class="modal-header">
                        <h2>📏 Grille & Guides</h2>
                        <button type="button" class="modal-close" aria-label="Fermer">&times;</button>
                    </div>
                    <div class="modal-body">
                        <!-- Contenu généré dynamiquement -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="button button-secondary modal-cancel">Annuler</button>
                        <button type="button" class="button button-primary modal-save">Enregistrer</button>
                    </div>
                </div>
            </div>

            <div id="canvas-zoom-modal" class="modal-overlay">
                <div class="modal-backdrop"></div>
                <div class="modal-container">
                    <div class="modal-header">
                        <h2>🔍 Zoom & Navigation</h2>
                        <button type="button" class="modal-close" aria-label="Fermer">&times;</button>
                    </div>
                    <div class="modal-body">
                        <!-- Contenu généré dynamiquement -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="button button-secondary modal-cancel">Annuler</button>
                        <button type="button" class="button button-primary modal-save">Enregistrer</button>
                    </div>
                </div>
            </div>

            <div id="canvas-interactions-modal" class="modal-overlay">
                <div class="modal-backdrop"></div>
                <div class="modal-container">
                    <div class="modal-header">
                        <h2>🖱️ Interaction</h2>
                        <button type="button" class="modal-close" aria-label="Fermer">&times;</button>
                    </div>
                    <div class="modal-body">
                        <!-- Contenu généré dynamiquement -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="button button-secondary modal-cancel">Annuler</button>
                        <button type="button" class="button button-primary modal-save">Enregistrer</button>
                    </div>
                </div>
            </div>

            <div id="canvas-export-modal" class="modal-overlay">
                <div class="modal-backdrop"></div>
                <div class="modal-container">
                    <div class="modal-header">
                        <h2>💾 Export</h2>
                        <button type="button" class="modal-close" aria-label="Fermer">&times;</button>
                    </div>
                    <div class="modal-body">
                        <!-- Contenu généré dynamiquement -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="button button-secondary modal-cancel">Annuler</button>
                        <button type="button" class="button button-primary modal-save">Enregistrer</button>
                    </div>
                </div>
            </div>

            <div id="canvas-performance-modal" class="modal-overlay">
                <div class="modal-backdrop"></div>
                <div class="modal-container">
                    <div class="modal-header">
                        <h2>⚡ Performance</h2>
                        <button type="button" class="modal-close" aria-label="Fermer">&times;</button>
                    </div>
                    <div class="modal-body">
                        <!-- Contenu généré dynamiquement -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="button button-secondary modal-cancel">Annuler</button>
                        <button type="button" class="button button-primary modal-save">Enregistrer</button>
                    </div>
                </div>
            </div>

            <div id="canvas-debug-modal" class="modal-overlay">
                <div class="modal-backdrop"></div>
                <div class="modal-container">
                    <div class="modal-header">
                        <h2>🐛 Debug & Maintenance</h2>
                        <button type="button" class="modal-close" aria-label="Fermer">&times;</button>
                    </div>
                    <div class="modal-body">
                        <!-- Contenu généré dynamiquement -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="button button-secondary modal-cancel">Annuler</button>
                        <button type="button" class="button button-primary modal-save">Enregistrer</button>
                    </div>
                </div>
            </div>
