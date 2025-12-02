<?php // Content tab content - Updated: 2025-11-18 20:20:00 ?>

<style>
    /* Toggle switch styles */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
        cursor: pointer;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        border-radius: 24px;
        transition: 0.3s;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        border-radius: 50%;
        transition: 0.3s;
    }

    input:checked + .toggle-slider {
        background-color: #007cba;
    }

    input:checked + .toggle-slider:before {
        transform: translateX(26px);
    }
</style>

            <h2>🎨 Contenu & Design</h2>

            <!-- Section Canvas -->
            <section class="canvas-section" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border: 2px solid #e9ecef; border-radius: 12px; padding: 10px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <h3 style="color: #495057; margin-top: 0; border-bottom: 2px solid #e9ecef; padding-bottom: 10px;">
                    <span style="display: inline-flex; align-items: center; gap: 10px;">
                        🎨 Canvas
                    </span>
                </h3>

                <p style="color: #666; margin-bottom: 20px;">Configurez l'apparence et le comportement de votre canvas de conception PDF.</p>

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
                                <div class="grid-preview" title="Grille & Guides activés"></div>
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
                                <div class="zoom-preview" title="Zoom 100% activé"></div>
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
                                <div class="interactions-preview" title="Sélection multiple activée"></div>
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
                                <div class="performance-preview" title="Performance optimisée"></div>
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
                                <div class="debug-preview" title="Debug activé"></div>
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
            <section class="templates-section" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border: 2px solid #e9ecef; border-radius: 12px; padding: 30px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <h3 style="color: #495057; margin-top: 0; border-bottom: 2px solid #e9ecef; padding-bottom: 10px;">
                    <span style="display: inline-flex; align-items: center; gap: 10px;">
                        📋 Templates
                        <span id="template-library-indicator" class="template-library-indicator" style="font-size: 12px; background: <?php echo get_option('pdf_builder_template_library_enabled', true) ? '#28a745' : '#dc3545'; ?>; color: white; padding: 2px 8px; border-radius: 10px; font-weight: normal;"><?php echo get_option('pdf_builder_template_library_enabled', true) ? 'ACTIF' : 'INACTIF'; ?></span>
                    </span>
                </h3>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="default_template">Template par défaut</label></th>
                        <td>
                            <select id="default_template" name="default_template">
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
                                <input type="checkbox" id="template_library_enabled" name="template_library_enabled" value="1" <?php checked(get_option('pdf_builder_template_library_enabled', true)); ?>>
                                <span class="toggle-slider"></span>
                            </label>
                            <p class="description">Active la bibliothèque de templates prédéfinis</p>
                        </td>
                    </tr>
                </table>
            </section>

