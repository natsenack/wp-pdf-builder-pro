<?php // Content tab content - Updated: 2025-11-18 20:20:00 ?>

            <h2>🎨 Contenu & Design</h2>

            <!-- Section Canvas -->
            <p style="color: #666; margin-bottom: 20px;">Configurez l'apparence et le comportement de votre canvas de conception PDF.</p>

            <form method="post" id="canvas-form">
                <?php wp_nonce_field('pdf_builder_canvas_nonce', 'pdf_builder_canvas_nonce'); ?>
                <input type="hidden" name="submit_canvas" value="1">

                <!-- Grille de cartes Canvas -->
                <div class="canvas-settings-grid">
                    <!-- Carte Dimensions & Format -->
                    <div class="canvas-card" data-category="dimensions">
                        <div class="canvas-card-header">
                            <span class="canvas-card-icon">📐</span>
                            <div>
                                <h4>Dimensions & Format</h4>
                                <span class="canvas-card-status ACTIF">ACTIF</span>
                            </div>
                        </div>
                        <div class="canvas-card-content">
                            <p>Configurez la taille, le format et la résolution de votre canvas.</p>
                        </div>
                        <div class="canvas-card-preview">
                            <div class="preview-format">800×600px</div>
                            <div class="preview-size">150 DPI</div>
                        </div>
                        <div class="canvas-card-actions">
                            <button type="button" class="canvas-configure-btn">Configurer</button>
                        </div>
                    </div>

                    <!-- Carte Apparence -->
                    <div class="canvas-card" data-category="apparence">
                        <div class="canvas-card-header">
                            <span class="canvas-card-icon">🎨</span>
                            <div>
                                <h4>Apparence</h4>
                                <span class="canvas-card-status ACTIF">ACTIF</span>
                            </div>
                        </div>
                        <div class="canvas-card-content">
                            <p>Personnalisez les couleurs, bordures et effets visuels.</p>
                        </div>
                        <div class="canvas-card-preview">
                            <div class="color-preview bg" style="background-color: #ffffff;"></div>
                            <div class="color-preview border" style="background-color: #cccccc;"></div>
                        </div>
                        <div class="canvas-card-actions">
                            <button type="button" class="canvas-configure-btn">Configurer</button>
                        </div>
                    </div>

                    <!-- Carte Grille & Guides -->
                    <div class="canvas-card" data-category="grille">
                        <div class="canvas-card-header">
                            <span class="canvas-card-icon">📏</span>
                            <div>
                                <h4>Grille & Guides</h4>
                                <span class="canvas-card-status ACTIF">ACTIF</span>
                            </div>
                        </div>
                        <div class="canvas-card-content">
                            <p>Gérez l'affichage et l'alignement sur la grille.</p>
                        </div>
                        <div class="canvas-card-preview">
                            <div class="grid-preview">
                                <div class="grid-line"></div>
                                <div class="grid-dot"></div>
                                <div class="grid-line"></div>
                            </div>
                        </div>
                        <div class="canvas-card-actions">
                            <button type="button" class="canvas-configure-btn">Configurer</button>
                        </div>
                    </div>

                    <!-- Carte Zoom & Navigation -->
                    <div class="canvas-card" data-category="zoom">
                        <div class="canvas-card-header">
                            <span class="canvas-card-icon">🔍</span>
                            <div>
                                <h4>Zoom & Navigation</h4>
                                <span class="canvas-card-status ACTIF">ACTIF</span>
                            </div>
                        </div>
                        <div class="canvas-card-content">
                            <p>Contrôlez les niveaux de zoom et la navigation.</p>
                        </div>
                        <div class="canvas-card-preview">
                            <div class="zoom-preview">
                                <span class="zoom-minus">-</span>
                                <span class="zoom-value">10-500%</span>
                                <span class="zoom-plus">+</span>
                            </div>
                        </div>
                        <div class="canvas-card-actions">
                            <button type="button" class="canvas-configure-btn">Configurer</button>
                        </div>
                    </div>

                    <!-- Carte Éléments Interactifs -->
                    <div class="canvas-card" data-category="interaction">
                        <div class="canvas-card-header">
                            <span class="canvas-card-icon">👆</span>
                            <div>
                                <h4>Éléments Interactifs</h4>
                                <span class="canvas-card-status ACTIF">ACTIF</span>
                            </div>
                        </div>
                        <div class="canvas-card-content">
                            <p>Activez le glisser-déposer, redimensionnement et rotation.</p>
                        </div>
                        <div class="canvas-card-preview">
                            <div class="interaction-preview">
                                <span class="element-handle">↔</span>
                                <span class="element-handle">↕</span>
                                <span class="element-handle">↻</span>
                            </div>
                        </div>
                        <div class="canvas-card-actions">
                            <button type="button" class="canvas-configure-btn">Configurer</button>
                        </div>
                    </div>

                    <!-- Carte Comportement -->
                    <div class="canvas-card" data-category="comportement">
                        <div class="canvas-card-header">
                            <span class="canvas-card-icon">⚙️</span>
                            <div>
                                <h4>Comportement</h4>
                                <span class="canvas-card-status ACTIF">ACTIF</span>
                            </div>
                        </div>
                        <div class="canvas-card-content">
                            <p>Définissez la sélection et les raccourcis clavier.</p>
                        </div>
                        <div class="canvas-card-preview">
                            <div class="behavior-preview">
                                <span class="behavior-icon">👆</span>
                                <span class="behavior-icon">⌨️</span>
                            </div>
                        </div>
                        <div class="canvas-card-actions">
                            <button type="button" class="canvas-configure-btn">Configurer</button>
                        </div>
                    </div>

                    <!-- Carte Export & Qualité -->
                    <div class="canvas-card" data-category="export">
                        <div class="canvas-card-header">
                            <span class="canvas-card-icon">📤</span>
                            <div>
                                <h4>Export & Qualité</h4>
                                <span class="canvas-card-status ACTIF">ACTIF</span>
                            </div>
                        </div>
                        <div class="canvas-card-content">
                            <p>Configurez les formats et la qualité d'export.</p>
                        </div>
                        <div class="canvas-card-preview">
                            <div class="export-preview">
                                <span class="export-format">PNG</span>
                                <span class="export-quality">90%</span>
                            </div>
                        </div>
                        <div class="canvas-card-actions">
                            <button type="button" class="canvas-configure-btn">Configurer</button>
                        </div>
                    </div>

                    <!-- Carte Performance -->
                    <div class="canvas-card" data-category="performance">
                        <div class="canvas-card-header">
                            <span class="canvas-card-icon">⚡</span>
                            <div>
                                <h4>Performance</h4>
                                <span class="canvas-card-status ACTIF">ACTIF</span>
                            </div>
                        </div>
                        <div class="canvas-card-content">
                            <p>Optimisez les FPS et la gestion mémoire.</p>
                        </div>
                        <div class="canvas-card-preview">
                            <div class="performance-bar">
                                <div class="performance-fill" style="width: 80%;"></div>
                            </div>
                            <div class="performance-fps">60 FPS</div>
                        </div>
                        <div class="canvas-card-actions">
                            <button type="button" class="canvas-configure-btn">Configurer</button>
                        </div>
                    </div>

                    <!-- Carte Sauvegarde Auto -->
                    <div class="canvas-card" data-category="autosave">
                        <div class="canvas-card-header">
                            <span class="canvas-card-icon">💾</span>
                            <div>
                                <h4>Sauvegarde Auto</h4>
                                <span class="canvas-card-status ACTIF">ACTIF</span>
                            </div>
                        </div>
                        <div class="canvas-card-content">
                            <p>Gérez la sauvegarde automatique et l'historique.</p>
                        </div>
                        <div class="canvas-card-preview">
                            <div class="autosave-preview">
                                <span class="autosave-icon">⏰</span>
                                <span class="autosave-timer">5min</span>
                            </div>
                        </div>
                        <div class="canvas-card-actions">
                            <button type="button" class="canvas-configure-btn">Configurer</button>
                        </div>
                    </div>

                    <!-- Carte Debug -->
                    <div class="canvas-card" data-category="debug">
                        <div class="canvas-card-header">
                            <span class="canvas-card-icon">🐛</span>
                            <div>
                                <h4>Debug</h4>
                                <span class="canvas-card-status INACTIF">INACTIF</span>
                            </div>
                        </div>
                        <div class="canvas-card-content">
                            <p>Outils de débogage et monitoring performance.</p>
                        </div>
                        <div class="canvas-card-preview">
                            <div class="debug-preview">
                                <span class="debug-icon">📊</span>
                                <span class="debug-fps">60 FPS</span>
                            </div>
                        </div>
                        <div class="canvas-card-actions">
                            <button type="button" class="canvas-configure-btn">Configurer</button>
                        </div>
                    </div>
                </div>

                <!-- Bouton de sauvegarde -->
                <div style="margin-top: 30px; text-align: center;">
                    <button type="submit" class="button button-primary button-hero" style="padding: 12px 24px; font-size: 16px;">
                        💾 Sauvegarder les paramètres Canvas
                    </button>
                </div>
            </form>

            <!-- Section Templates -->
            <div style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border: 2px solid #e9ecef; border-radius: 12px; padding: 30px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <h3 style="color: #495057; margin-top: 0; border-bottom: 2px solid #e9ecef; padding-bottom: 10px;">
                    <span style="display: inline-flex; align-items: center; gap: 10px;">
                        📋 Templates
                        <span style="font-size: 12px; background: #28a745; color: white; padding: 2px 8px; border-radius: 10px; font-weight: normal;">ACTIF</span>
                    </span>
                </h3>

                <form method="post" action="">
                    <?php wp_nonce_field('pdf_builder_templates', 'pdf_builder_templates_nonce'); ?>
                    <input type="hidden" name="current_tab" value="templates">

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
                </form>
            </div>

<script>
// Canvas configuration modals functionality
document.addEventListener('DOMContentLoaded', function() {
    // Handle canvas configure buttons
    const configureButtons = document.querySelectorAll('.canvas-configure-btn');
    configureButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const card = this.closest('.canvas-card');
            const category = card.getAttribute('data-category');
            const modalId = 'canvas-' + category + '-modal';
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'block';
            }
        });
    });

    // Handle modal close buttons
    const closeButtons = document.querySelectorAll('.canvas-modal-close, .canvas-modal-cancel');
    closeButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const modal = this.closest('.canvas-modal');
            modal.style.display = 'none';
        });
    });

    // Handle modal overlay clicks
    const modalOverlays = document.querySelectorAll('.canvas-modal-overlay');
    modalOverlays.forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.closest('.canvas-modal').style.display = 'none';
            }
        });
    });

    // Handle modal save buttons
    const saveButtons = document.querySelectorAll('.canvas-modal-save');
    saveButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            const modal = this.closest('.canvas-modal');
            const form = modal.querySelector('form');
            
            if (form) {
                const formData = new FormData(form);
                formData.append('action', 'pdf_builder_save_canvas_settings');
                formData.append('category', category);
                formData.append('nonce', '<?php echo wp_create_nonce('pdf_builder_canvas_nonce'); ?>');

                // Show loading state
                this.textContent = 'Sauvegarde...';
                this.disabled = true;

                fetch(ajaxurl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close modal
                        modal.style.display = 'none';
                        // Show success message
                        showCanvasNotification('Paramètres sauvegardés avec succès!', 'success');
                    } else {
                        showCanvasNotification('Erreur lors de la sauvegarde: ' + (data.data || 'Erreur inconnue'), 'error');
                    }
                })
                .catch(error => {
                    showCanvasNotification('Erreur réseau: ' + error.message, 'error');
                })
                .finally(() => {
                    // Reset button state
                    this.textContent = 'Sauvegarder';
                    this.disabled = false;
                });
            }
        });
    });

    // Handle canvas form submission
    const canvasForm = document.getElementById('canvas-form');
    if (canvasForm) {
        canvasForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'pdf_builder_save_settings');
            formData.append('current_tab', 'contenu');
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Sauvegarde en cours...';
            submitBtn.disabled = true;
            
            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showCanvasNotification('Paramètres canvas sauvegardés avec succès!', 'success');
                } else {
                    showCanvasNotification('Erreur lors de la sauvegarde: ' + (data.data?.message || 'Erreur inconnue'), 'error');
                }
            })
            .catch(error => {
                showCanvasNotification('Erreur réseau: ' + error.message, 'error');
            })
            .finally(() => {
                // Reset button state
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }
    function showCanvasNotification(message, type) {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.canvas-notification');
        existingNotifications.forEach(function(notification) {
            notification.remove();
        });

        // Create new notification
        const notification = document.createElement('div');
        notification.className = 'canvas-notification ' + (type === 'success' ? 'success' : 'error');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#28a745' : '#dc3545'};
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 10001;
            font-weight: bold;
            max-width: 400px;
        `;
        notification.textContent = message;

        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(function() {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
});
</script>