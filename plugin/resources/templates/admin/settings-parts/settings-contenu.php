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

                <form method="post" id="canvas-form">
                    <?php wp_nonce_field('pdf_builder_canvas_nonce', 'pdf_builder_canvas_nonce'); ?>
                    <input type="hidden" name="submit_canvas" value="1">

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
                                <div class="color-preview bg" title="Fond"></div>
                                <div class="color-preview border" title="Bordure"></div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>🎨</span> Configurer
                                </button>
                            </div>
                        </article>

                        <!-- Carte Grille & Guides -->
                        <article class="canvas-card" data-category="grille">
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
                                <div class="grid-preview-container">
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
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>📏</span> Configurer
                                </button>
                            </div>
                        </article>

                        <!-- Carte Zoom -->
                        <article class="canvas-card" id="zoom-navigation-card" data-category="zoom">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">🔍</span>
                                </div>
                                <h4>Zoom</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Contrôlez les niveaux de zoom et les options de navigation.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="zoom-preview-container">
                                    <div class="zoom-indicator">
                                        <button class="zoom-btn zoom-minus" disabled>−</button>
                                        <span class="zoom-level">100%</span>
                                        <button class="zoom-btn zoom-plus" disabled>+</button>
                                    </div>
                                    <div class="zoom-info">
                                        <span>10% - 500%</span>
                                        <span>Pas: 25%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>🔍</span> Configurer
                                </button>
                            </div>
                        </article>

                        <!-- Carte Interactions & Comportement -->
                        <article class="canvas-card" data-category="interactions">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">🎯</span>
                                </div>
                                <h4>Interactions & Comportement</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Contrôlez les interactions canvas, la sélection et les raccourcis clavier.</p>
                            </div>
                            <div class="canvas-card-preview">
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
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>🎯</span> Configurer
                                </button>
                            </div>
                        </article>

                        <!-- Carte Export & Qualité -->
                        <article class="canvas-card" data-category="export">
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
                                <div class="export-preview" title="Export PNG/JPG/PDF activé"></div>
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>📤</span> Configurer
                                </button>
                            </div>
                        </article>

                        <!-- Carte Performance -->
                        <article class="canvas-card" data-category="performance">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">⚡</span>
                                </div>
                                <h4>Performance</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Optimisez les FPS, mémoire et temps de réponse.</p>
                            </div>
                            <div class="canvas-card-preview">
                                <div class="performance-preview-container">
                                    <div class="performance-metrics">
                                        <div class="metric-item">
                                            <span class="metric-label">FPS</span>
                                            <span class="metric-value">60</span>
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
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>⚡</span> Configurer
                                </button>
                            </div>
                        </article>

                        <!-- Carte Debug -->
                        <article class="canvas-card" data-category="debug">
                            <div class="canvas-card-header">
                                <div class="canvas-card-header-left">
                                    <span class="canvas-card-icon">🐛</span>
                                </div>
                                <h4>Debug</h4>
                            </div>
                            <div class="canvas-card-content">
                                <p>Outils de débogage et monitoring des performances.</p>
                            </div>
                            <div class="canvas-card-preview">
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
                            </div>
                            <div class="canvas-card-actions">
                                <button type="button" class="canvas-configure-btn">
                                    <span>🐛</span> Configurer
                                </button>
                            </div>
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

            <!-- NOUVEAU SYSTÈME D'OVERLAY MODAL COMPLET -->
            <div id="pdf-builder-modal-overlay" class="pdf-builder-modal-overlay" style="display: none;">
                <div class="pdf-builder-modal-backdrop"></div>
                <div class="pdf-builder-modal-container">
                    <div class="pdf-builder-modal-header">
                        <h2 id="pdf-builder-modal-title">Configuration</h2>
                        <button type="button" class="pdf-builder-modal-close" aria-label="Fermer">&times;</button>
                    </div>
                    <div class="pdf-builder-modal-body">
                        <!-- Contenu dynamique des modales -->
                    </div>
                    <div class="pdf-builder-modal-footer">
                        <button type="button" class="button button-secondary pdf-builder-modal-cancel">Annuler</button>
                        <button type="button" class="button button-primary pdf-builder-modal-save">Enregistrer</button>
                    </div>
                </div>
            </div>

            <script>
                (function() {
                    'use strict';

                    // Configuration des modales
                    const modalConfigs = {
                        dimensions: {
                            title: '📐 Dimensions & Format',
                            content: `
                                <div class="modal-form-grid">
                                    <div class="form-group">
                                        <label for="modal_canvas_width">Largeur (px)</label>
                                        <input type="number" id="modal_canvas_width" name="modal_canvas_width" value="${<?php echo json_encode($settings['pdf_builder_canvas_width'] ?? '794'); ?>}" min="100" max="5000">
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_height">Hauteur (px)</label>
                                        <input type="number" id="modal_canvas_height" name="modal_canvas_height" value="${<?php echo json_encode($settings['pdf_builder_canvas_height'] ?? '1123'); ?>}" min="100" max="5000">
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_dpi">DPI</label>
                                        <select id="modal_canvas_dpi" name="modal_canvas_dpi">
                                            <option value="72" ${'<?php echo ($settings['pdf_builder_canvas_dpi'] ?? '96') === '72' ? 'selected' : ''; ?>'}>72 (Web)</option>
                                            <option value="96" ${'<?php echo ($settings['pdf_builder_canvas_dpi'] ?? '96') === '96' ? 'selected' : ''; ?>'}>96 (Écran)</option>
                                            <option value="150" ${'<?php echo ($settings['pdf_builder_canvas_dpi'] ?? '96') === '150' ? 'selected' : ''; ?>'}>150 (Impression)</option>
                                            <option value="300" ${'<?php echo ($settings['pdf_builder_canvas_dpi'] ?? '96') === '300' ? 'selected' : ''; ?>'}>300 (Haute qualité)</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_format">Format prédéfini</label>
                                        <select id="modal_canvas_format" name="modal_canvas_format">
                                            <option value="custom">Personnalisé</option>
                                            <option value="A4" ${'<?php echo ($settings['pdf_builder_canvas_format'] ?? 'A4') === 'A4' ? 'selected' : ''; ?>'}>A4 (210×297mm)</option>
                                            <option value="A3" ${'<?php echo ($settings['pdf_builder_canvas_format'] ?? 'A4') === 'A3' ? 'selected' : ''; ?>'}>A3 (297×420mm)</option>
                                            <option value="Letter" ${'<?php echo ($settings['pdf_builder_canvas_format'] ?? 'A4') === 'Letter' ? 'selected' : ''; ?>'}>Letter (8.5×11")</option>
                                            <option value="Legal" ${'<?php echo ($settings['pdf_builder_canvas_format'] ?? 'A4') === 'Legal' ? 'selected' : ''; ?>'}>Legal (8.5×14")</option>
                                        </select>
                                    </div>
                                </div>
                            `
                        },
                        apparence: {
                            title: '🎨 Apparence',
                            content: `
                                <div class="modal-form-grid">
                                    <div class="form-group">
                                        <label for="modal_canvas_bg_color">Couleur de fond</label>
                                        <input type="color" id="modal_canvas_bg_color" name="modal_canvas_bg_color" value="${<?php echo json_encode($settings['pdf_builder_canvas_bg_color'] ?? '#ffffff'); ?>}">
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_border_color">Couleur bordure</label>
                                        <input type="color" id="modal_canvas_border_color" name="modal_canvas_border_color" value="${<?php echo json_encode($settings['pdf_builder_canvas_border_color'] ?? '#cccccc'); ?>}">
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_border_width">Épaisseur bordure (px)</label>
                                        <input type="number" id="modal_canvas_border_width" name="modal_canvas_border_width" value="${<?php echo json_encode($settings['pdf_builder_canvas_border_width'] ?? '1'); ?>}" min="0" max="10">
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_shadow_enabled">Ombre activée</label>
                                        <input type="checkbox" id="modal_canvas_shadow_enabled" name="modal_canvas_shadow_enabled" value="1" ${'<?php echo ($settings['pdf_builder_canvas_shadow_enabled'] ?? '0') === '1' ? 'checked' : ''; ?>'}>
                                    </div>
                                </div>
                            `
                        },
                        grille: {
                            title: '📏 Grille & Guides',
                            content: `
                                <div class="modal-form-grid">
                                    <div class="form-group">
                                        <label for="modal_canvas_grid_enabled">Grille activée</label>
                                        <input type="checkbox" id="modal_canvas_grid_enabled" name="modal_canvas_grid_enabled" value="1" ${'<?php echo ($settings['pdf_builder_canvas_grid_enabled'] ?? '1') === '1' ? 'checked' : ''; ?>'}>
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_grid_size">Taille grille (px)</label>
                                        <input type="number" id="modal_canvas_grid_size" name="modal_canvas_grid_size" value="${<?php echo json_encode($settings['pdf_builder_canvas_grid_size'] ?? '20'); ?>}" min="5" max="100">
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_guides_enabled">Guides activés</label>
                                        <input type="checkbox" id="modal_canvas_guides_enabled" name="modal_canvas_guides_enabled" value="1" ${'<?php echo ($settings['pdf_builder_canvas_guides_enabled'] ?? '1') === '1' ? 'checked' : ''; ?>'}>
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_snap_to_grid">Accrochage à la grille</label>
                                        <input type="checkbox" id="modal_canvas_snap_to_grid" name="modal_canvas_snap_to_grid" value="1" ${'<?php echo ($settings['pdf_builder_canvas_snap_to_grid'] ?? '1') === '1' ? 'checked' : ''; ?>'}>
                                    </div>
                                </div>
                            `
                        },
                        zoom: {
                            title: '🔍 Zoom & Navigation',
                            content: `
                                <div class="modal-form-grid">
                                    <div class="form-group">
                                        <label for="modal_canvas_zoom_min">Zoom minimum (%)</label>
                                        <input type="number" id="modal_canvas_zoom_min" name="modal_canvas_zoom_min" value="${<?php echo json_encode($settings['pdf_builder_canvas_zoom_min'] ?? '25'); ?>}" min="10" max="100">
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_zoom_max">Zoom maximum (%)</label>
                                        <input type="number" id="modal_canvas_zoom_max" name="modal_canvas_zoom_max" value="${<?php echo json_encode($settings['pdf_builder_canvas_zoom_max'] ?? '500'); ?>}" min="100" max="1000">
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_zoom_default">Zoom par défaut (%)</label>
                                        <input type="number" id="modal_canvas_zoom_default" name="modal_canvas_zoom_default" value="${<?php echo json_encode($settings['pdf_builder_canvas_zoom_default'] ?? '100'); ?>}" min="25" max="500">
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_zoom_step">Pas de zoom (%)</label>
                                        <input type="number" id="modal_canvas_zoom_step" name="modal_canvas_zoom_step" value="${<?php echo json_encode($settings['pdf_builder_canvas_zoom_step'] ?? '25'); ?>}" min="5" max="50">
                                    </div>
                                </div>
                            `
                        },
                        interactions: {
                            title: '🖱️ Interaction',
                            content: `
                                <div class="modal-form-grid">
                                    <div class="form-group">
                                        <label for="modal_canvas_drag_enabled">Glisser activé</label>
                                        <input type="checkbox" id="modal_canvas_drag_enabled" name="modal_canvas_drag_enabled" value="1" ${'<?php echo ($settings['pdf_builder_canvas_drag_enabled'] ?? '1') === '1' ? 'checked' : ''; ?>'}>
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_resize_enabled">Redimensionnement activé</label>
                                        <input type="checkbox" id="modal_canvas_resize_enabled" name="modal_canvas_resize_enabled" value="1" ${'<?php echo ($settings['pdf_builder_canvas_resize_enabled'] ?? '1') === '1' ? 'checked' : ''; ?>'}>
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_rotate_enabled">Rotation activée</label>
                                        <input type="checkbox" id="modal_canvas_rotate_enabled" name="modal_canvas_rotate_enabled" value="1" ${'<?php echo ($settings['pdf_builder_canvas_rotate_enabled'] ?? '1') === '1' ? 'checked' : ''; ?>'}>
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_multi_select">Sélection multiple</label>
                                        <input type="checkbox" id="modal_canvas_multi_select" name="modal_canvas_multi_select" value="1" ${'<?php echo ($settings['pdf_builder_canvas_multi_select'] ?? '1') === '1' ? 'checked' : ''; ?>'}>
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_selection_mode">Mode de sélection</label>
                                        <select id="modal_canvas_selection_mode" name="modal_canvas_selection_mode">
                                            <option value="single" ${'<?php echo ($settings['pdf_builder_canvas_selection_mode'] ?? 'single') === 'single' ? 'selected' : ''; ?>'}>Simple</option>
                                            <option value="multiple" ${'<?php echo ($settings['pdf_builder_canvas_selection_mode'] ?? 'single') === 'multiple' ? 'selected' : ''; ?>'}>Multiple</option>
                                            <option value="group" ${'<?php echo ($settings['pdf_builder_canvas_selection_mode'] ?? 'single') === 'group' ? 'selected' : ''; ?>'}>Grouper</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_keyboard_shortcuts">Raccourcis clavier</label>
                                        <input type="checkbox" id="modal_canvas_keyboard_shortcuts" name="modal_canvas_keyboard_shortcuts" value="1" ${'<?php echo ($settings['pdf_builder_canvas_keyboard_shortcuts'] ?? '1') === '1' ? 'checked' : ''; ?>'}>
                                    </div>
                                </div>
                            `
                        },
                        export: {
                            title: '💾 Export',
                            content: `
                                <div class="modal-form-grid">
                                    <div class="form-group">
                                        <label for="modal_canvas_export_format">Format d'export</label>
                                        <select id="modal_canvas_export_format" name="modal_canvas_export_format">
                                            <option value="png" ${'<?php echo ($settings['pdf_builder_canvas_export_format'] ?? 'png') === 'png' ? 'selected' : ''; ?>'}>PNG</option>
                                            <option value="jpg" ${'<?php echo ($settings['pdf_builder_canvas_export_format'] ?? 'png') === 'jpg' ? 'selected' : ''; ?>'}>JPEG</option>
                                            <option value="svg" ${'<?php echo ($settings['pdf_builder_canvas_export_format'] ?? 'png') === 'svg' ? 'selected' : ''; ?>'}>SVG</option>
                                            <option value="pdf" ${'<?php echo ($settings['pdf_builder_canvas_export_format'] ?? 'png') === 'pdf' ? 'selected' : ''; ?>'}>PDF</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_export_quality">Qualité (%)</label>
                                        <input type="number" id="modal_canvas_export_quality" name="modal_canvas_export_quality" value="${<?php echo json_encode($settings['pdf_builder_canvas_export_quality'] ?? '90'); ?>}" min="10" max="100">
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_export_transparent">Fond transparent</label>
                                        <input type="checkbox" id="modal_canvas_export_transparent" name="modal_canvas_export_transparent" value="1" ${'<?php echo ($settings['pdf_builder_canvas_export_transparent'] ?? '0') === '1' ? 'checked' : ''; ?>'}>
                                    </div>
                                </div>
                            `
                        },
                        performance: {
                            title: '⚡ Performance',
                            content: `
                                <div class="modal-form-grid">
                                    <div class="form-group">
                                        <label for="modal_canvas_fps_target">FPS cible</label>
                                        <input type="number" id="modal_canvas_fps_target" name="modal_canvas_fps_target" value="${<?php echo json_encode($settings['pdf_builder_canvas_fps_target'] ?? '60'); ?>}" min="10" max="120">
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_memory_limit_js">Limite mémoire JS (MB)</label>
                                        <input type="number" id="modal_canvas_memory_limit_js" name="modal_canvas_memory_limit_js" value="${<?php echo json_encode($settings['pdf_builder_canvas_memory_limit_js'] ?? '50'); ?>}" min="10" max="500">
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_response_timeout">Timeout réponse (ms)</label>
                                        <input type="number" id="modal_canvas_response_timeout" name="modal_canvas_response_timeout" value="${<?php echo json_encode($settings['pdf_builder_canvas_response_timeout'] ?? '5000'); ?>}" min="1000" max="30000">
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_lazy_loading_editor">Chargement différé éditeur</label>
                                        <input type="checkbox" id="modal_canvas_lazy_loading_editor" name="modal_canvas_lazy_loading_editor" value="1" ${'<?php echo ($settings['pdf_builder_canvas_lazy_loading_editor'] ?? '1') === '1' ? 'checked' : ''; ?>'}>
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_preload_critical">Préchargement critique</label>
                                        <input type="checkbox" id="modal_canvas_preload_critical" name="modal_canvas_preload_critical" value="1" ${'<?php echo ($settings['pdf_builder_canvas_preload_critical'] ?? '1') === '1' ? 'checked' : ''; ?>'}>
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_lazy_loading_plugin">Chargement différé plugin</label>
                                        <input type="checkbox" id="modal_canvas_lazy_loading_plugin" name="modal_canvas_lazy_loading_plugin" value="1" ${'<?php echo ($settings['pdf_builder_canvas_lazy_loading_plugin'] ?? '1') === '1' ? 'checked' : ''; ?>'}>
                                    </div>
                                </div>
                            `
                        },
                        debug: {
                            title: '🐛 Debug & Maintenance',
                            content: `
                                <div class="modal-form-grid">
                                    <div class="form-group">
                                        <label for="modal_canvas_debug_enabled">Debug activé</label>
                                        <input type="checkbox" id="modal_canvas_debug_enabled" name="modal_canvas_debug_enabled" value="1" ${'<?php echo ($settings['pdf_builder_canvas_debug_enabled'] ?? '0') === '1' ? 'checked' : ''; ?>'}>
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_performance_monitoring">Monitoring performance</label>
                                        <input type="checkbox" id="modal_canvas_performance_monitoring" name="modal_canvas_performance_monitoring" value="1" ${'<?php echo ($settings['pdf_builder_canvas_performance_monitoring'] ?? '0') === '1' ? 'checked' : ''; ?>'}>
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_error_reporting">Rapport d'erreurs</label>
                                        <input type="checkbox" id="modal_canvas_error_reporting" name="modal_canvas_error_reporting" value="1" ${'<?php echo ($settings['pdf_builder_canvas_error_reporting'] ?? '0') === '1' ? 'checked' : ''; ?>'}>
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_canvas_memory_limit_php">Limite mémoire PHP (MB)</label>
                                        <input type="number" id="modal_canvas_memory_limit_php" name="modal_canvas_memory_limit_php" value="${<?php echo json_encode($settings['pdf_builder_canvas_memory_limit_php'] ?? '128'); ?>}" min="32" max="1024">
                                    </div>
                                </div>
                            `
                        }
                    };

                    // État de la modal
                    let currentModalCategory = null;

                    // Éléments DOM
                    const overlay = document.getElementById('pdf-builder-modal-overlay');
                    const modalTitle = document.getElementById('pdf-builder-modal-title');
                    const modalBody = document.querySelector('.pdf-builder-modal-body');

                    // Ouvrir une modal
                    function openModal(category) {
                        if (!modalConfigs[category]) {
                            console.error('Configuration de modal introuvable pour:', category);
                            return;
                        }

                        currentModalCategory = category;
                        const config = modalConfigs[category];

                        // Mettre à jour le titre
                        modalTitle.textContent = config.title;

                        // Mettre à jour le contenu
                        modalBody.innerHTML = config.content;

                        // Afficher l'overlay
                        overlay.style.display = 'block';
                        document.body.style.overflow = 'hidden';

                        console.log('Modal ouverte:', category);
                    }

                    // Fermer la modal
                    function closeModal() {
                        overlay.style.display = 'none';
                        document.body.style.overflow = '';
                        currentModalCategory = null;
                        console.log('Modal fermée');
                    }

                    // Sauvegarder les paramètres (TODO: implémenter)
                    function saveModalSettings() {
                        console.log('Sauvegarde des paramètres pour:', currentModalCategory);
                        // TODO: Collecter et sauvegarder les données
                        closeModal();
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
                        if (e.target.closest('.pdf-builder-modal-close') || e.target.closest('.pdf-builder-modal-cancel')) {
                            closeModal();
                            return;
                        }

                        // Clic sur l'overlay (backdrop)
                        if (e.target.classList.contains('pdf-builder-modal-overlay')) {
                            closeModal();
                            return;
                        }

                        // Bouton de sauvegarde
                        if (e.target.closest('.pdf-builder-modal-save')) {
                            saveModalSettings();
                            return;
                        }
                    });

                    // Fermeture avec Échap
                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape' && overlay.style.display === 'block') {
                            closeModal();
                        }
                    });

                    console.log('Système de modal PDF Builder initialisé');
                })();
            </script>
