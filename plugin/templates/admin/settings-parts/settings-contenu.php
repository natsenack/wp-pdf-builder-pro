<?php // Content tab content - Updated: 2025-11-18 20:20:00 ?>



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
                        <span id="template-library-indicator" class="template-library-indicator" style="background: <?php echo get_option('pdf_builder_template_library_enabled', true) ? '#28a745' : '#dc3545'; ?>;"><?php echo get_option('pdf_builder_template_library_enabled', true) ? 'ACTIF' : 'INACTIF'; ?></span>
                    </span>
                </h3>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="default_template">Template par défaut</label></th>
                        <td>
                            <select id="default_template" name="default_template" data-settings-field="true" data-settings-tab="contenu">
                                <option value="blank" <?php selected(get_option('pdf_builder_default_template', 'blank'), 'blank'); ?>>Page blanche</option>
                                <option value="invoice" <?php selected(get_option('pdf_builder_default_template', 'blank'), 'invoice'); ?>>Facture</option>
                                <option value="quote" <?php selected(get_option('pdf_builder_default_template', 'blank'), 'quote'); ?>>Devis</option>
                            </select>
                            <p class="description">Template utilisé par défaut pour nouveaux documents</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="template_library_enabled">Bibliothèque de templates</label></th>
                        <td>
                            <label class="toggle-switch">
                                <input type="checkbox" id="template_library_enabled" name="template_library_enabled" value="1" data-settings-field="true" data-settings-tab="contenu" <?php checked(get_option('pdf_builder_template_library_enabled', true)); ?>>
                                <span class="toggle-slider"></span>
                            </label>
                            <p class="description">Active la bibliothèque de templates prédéfinis</p>
                        </td>
                    </tr>
                </table>
            </section>


