<?php // PDF tab content - Updated: 2025-11-19 01:40:00

$settings = pdf_builder_get_option('pdf_builder_settings', array());
if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] settings-pdf.php loaded - settings count: ' . count($settings)); }

// Vérifier si l'utilisateur a une licence premium
$license_manager = \PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance();
$is_premium = $license_manager->isPremium();
?>



            <!-- Section Principale -->
            <section id="pdf" class="pdf-section contenu-canvas-section">
            <h3 class="" style="display: flex; justify-content: flex-start; align-items: center;">
                <span>📄 Configuration PDF</span>
            </h3>
                <h4 style="color: #495057; margin-top: 0; border-bottom: 2px solid #007cba; padding-bottom: 10px;">
                    ⚙️ Paramètres principaux
                </h4>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="pdf_quality">Qualité</label></th>
                        <td>
                            <select id="pdf_quality" name="pdf_builder_settings[pdf_builder_pdf_quality]">
                                    <option value="low" <?php selected($settings['pdf_builder_pdf_quality'] ?? 'high', 'low'); ?>>Rapide (fichiers légers)</option>
                                    <option value="medium" <?php selected($settings['pdf_builder_pdf_quality'] ?? 'high', 'medium'); ?>>Équilibré</option>
                                    <option value="high" <?php selected($settings['pdf_builder_pdf_quality'] ?? 'high', 'high'); ?>>Haute qualité</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="default_format">Format de page</label></th>
                            <td>
                                <select id="default_format" name="pdf_builder_settings[pdf_builder_default_format]">
                                    <option value="A4" <?php selected($settings['pdf_builder_default_format'] ?? 'A4', 'A4'); ?>>A4</option>
                                    <option value="A3" <?php selected($settings['pdf_builder_default_format'] ?? 'A4', 'A3'); ?> disabled title="Bientôt disponible">A3 (soon)</option>
                                    <option value="Letter" <?php selected($settings['pdf_builder_default_format'] ?? 'A4', 'Letter'); ?> disabled title="Bientôt disponible">Letter (soon)</option>
                                </select>
                                <p class="description" style="margin-top:6px; color:#6c757d; font-size:12px;">Les formats A3 et Letter sont prévus; sélection désactivée pour l'instant.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="default_orientation">Orientation</label></th>
                            <td>
                                <select id="default_orientation" name="pdf_builder_settings[pdf_builder_default_orientation]">
                                    <option value="portrait" <?php selected($settings['pdf_builder_default_orientation'] ?? 'portrait', 'portrait'); ?>>Portrait</option>
                                    <option value="landscape" <?php selected($settings['pdf_builder_default_orientation'] ?? 'portrait', 'landscape'); ?>>Paysage</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="pdf_builder_pdf_cache_enabled">Cache activé</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_settings[pdf_builder_pdf_cache_enabled]" value="0">
                                    <input type="checkbox" id="pdf_builder_pdf_cache_enabled" name="pdf_builder_settings[pdf_builder_pdf_cache_enabled]" value="1" <?php checked($settings['pdf_builder_pdf_cache_enabled'] ?? '0', '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Améliorer les performances en mettant en cache les PDF</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="export_quality">Qualité export (%)</label></th>
                            <td>
                                <input type="number" id="export_quality" name="pdf_builder_settings[pdf_builder_canvas_export_quality]"
                                       value="<?php echo esc_attr($settings['pdf_builder_canvas_export_quality'] ?? '90'); ?>" min="1" max="100">
                                <p class="description">Qualité de l'image exportée (1-100%)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="export_format">Format export</label></th>
                            <td>
                                <?php $can_use_multi_format_export = \PDF_Builder\Managers\PDF_Builder_Feature_Manager::canUseFeature('multi_format_export'); ?>
                                <select id="export_format" name="pdf_builder_settings[pdf_builder_canvas_export_format]">
                                    <option value="pdf" <?php selected($settings['pdf_builder_canvas_export_format'] ?? 'pdf', 'pdf'); ?>>PDF</option>
                                    <option value="png" <?php selected($settings['pdf_builder_canvas_export_format'] ?? 'pdf', 'png'); ?> <?php echo !$can_use_multi_format_export ? 'disabled' : ''; ?>><?php echo !$can_use_multi_format_export ? 'PNG ⭐ PREMIUM' : 'PNG'; ?></option>
                                    <option value="jpg" <?php selected($settings['pdf_builder_canvas_export_format'] ?? 'pdf', 'jpg'); ?> <?php echo !$can_use_multi_format_export ? 'disabled' : ''; ?>><?php echo !$can_use_multi_format_export ? 'JPG ⭐ PREMIUM' : 'JPG'; ?></option>
                                    <option value="svg" <?php selected($settings['pdf_builder_canvas_export_format'] ?? 'pdf', 'svg'); ?> <?php echo !$can_use_multi_format_export ? 'disabled' : ''; ?>><?php echo !$can_use_multi_format_export ? 'SVG ⭐ PREMIUM' : 'SVG'; ?></option>
                                </select>
                                <?php if (!$can_use_multi_format_export): ?>
                                <div class="info-box" style="margin-top: 8px; padding: 8px; background: #fff3cd; border: 1px solid #f39c12; border-radius: 4px; font-size: 12px;">
                                    <strong>🔒 Fonction Premium</strong> - Débloquez l'export PNG, JPG et SVG
                                    <a href="#" onclick="showUpgradeModal('canvas_settings')" style="color: #856404; text-decoration: underline; font-weight: 500;">Passer en Premium →</a>
                                </div>
                                <?php endif; ?>
                                <p class="description">Format de fichier pour l'export</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="export_transparent">Fond transparent</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_settings[pdf_builder_canvas_export_transparent]" value="0">
                                    <input type="checkbox" id="export_transparent" name="pdf_builder_settings[pdf_builder_canvas_export_transparent]" value="1" <?php checked($settings['pdf_builder_canvas_export_transparent'] ?? '0', '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Exporter avec un fond transparent (PNG/SVG uniquement)</p>
                            </td>
                        </tr>
                        <script>
                            // PDF Cache toggle functionality
                            (function() {
                                const pdfCacheInput = document.getElementById('pdf_builder_pdf_cache_enabled');
                                const pdfCacheLabel = pdfCacheInput ? pdfCacheInput.closest('label') : null;
                                const pdfCacheSlider = pdfCacheLabel ? pdfCacheLabel.querySelector('.toggle-slider') : null;
                                
                                if (pdfCacheInput && pdfCacheLabel && pdfCacheSlider) {
                                    // Make slider clickable
                                    pdfCacheSlider.style.pointerEvents = 'auto';
                                    pdfCacheSlider.style.cursor = 'pointer';
                                    
                                    // Handle slider clicks
                                    pdfCacheSlider.addEventListener('click', function(e) {
                                        e.stopPropagation();
                                        pdfCacheInput.dataset.sliderClicked = 'true';
                                        pdfCacheInput.checked = !pdfCacheInput.checked;
                                        pdfCacheInput.dispatchEvent(new Event('change', { bubbles: true }));
                                    });
                                    
                                    // Handle label clicks (prevent double toggle)
                                    pdfCacheLabel.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        if (pdfCacheInput.dataset.sliderClicked) {
                                            delete pdfCacheInput.dataset.sliderClicked;
                                            return;
                                        }
                                        pdfCacheInput.checked = !pdfCacheInput.checked;
                                        pdfCacheInput.dispatchEvent(new Event('change', { bubbles: true }));
                                    });
                                }
                            })();
                        </script>
                        <script>
                            // Export Transparent toggle functionality
                            (function() {
                                const exportTransparentInput = document.getElementById('export_transparent');
                                const exportTransparentLabel = exportTransparentInput ? exportTransparentInput.closest('label') : null;
                                const exportTransparentSlider = exportTransparentLabel ? exportTransparentLabel.querySelector('.toggle-slider') : null;
                                
                                if (exportTransparentInput && exportTransparentLabel && exportTransparentSlider) {
                                    // Make slider clickable
                                    exportTransparentSlider.style.pointerEvents = 'auto';
                                    exportTransparentSlider.style.cursor = 'pointer';
                                    
                                    // Handle slider clicks
                                    exportTransparentSlider.addEventListener('click', function(e) {
                                        e.stopPropagation();
                                        exportTransparentInput.dataset.sliderClicked = 'true';
                                        exportTransparentInput.checked = !exportTransparentInput.checked;
                                        exportTransparentInput.dispatchEvent(new Event('change', { bubbles: true }));
                                    });
                                    
                                    // Handle label clicks (prevent double toggle)
                                    exportTransparentLabel.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        if (exportTransparentInput.dataset.sliderClicked) {
                                            delete exportTransparentInput.dataset.sliderClicked;
                                            return;
                                        }
                                        exportTransparentInput.checked = !exportTransparentInput.checked;
                                        exportTransparentInput.dispatchEvent(new Event('change', { bubbles: true }));
                                    });
                                }
                            })();
                        </script>
                    </table>
                </section>

                <?php if (!$is_premium): ?>
                <!-- Message pour version Premium -->
                <div class="notice notice-warning inline" style="margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px;">
                    <h4 style="margin: 0 0 10px 0; color: #856404;">🔒 Options avancées - Version Premium</h4>
                    <p style="margin: 0 0 15px 0; color: #856404;">
                        Les options avancées de compression, métadonnées et optimisation d'impression sont disponibles dans la version Premium.
                    </p>
                    <p style="margin: 0;">
                        <a href="#" onclick="if(window.PDFBuilderTabsAPI && PDFBuilderTabsAPI.switchToTab) { PDFBuilderTabsAPI.switchToTab('licence'); return false; } else if(window.switchTab) { switchTab('licence'); return false; } else { window.location.href='<?php echo admin_url('admin.php?page=pdf-builder-settings&tab=licence'); ?>'; return false; }" class="button button-primary" style="background: #007cba; border-color: #007cba; color: white; text-decoration: none; padding: 8px 16px; border-radius: 4px;">
                            Passer à la version Premium
                        </a>
                    </p>
                </div>
                <?php endif; ?>

                <?php if ($is_premium): ?>
                <!-- Section Avancée (Premium) -->
                <section id="pdf" class="pdf-section">
                    <h3 style="color: #495057; margin-top: 30px; border-bottom: 2px solid #6c757d; padding-bottom: 10px;">
                        🔧 Options avancées
                    </h3>



                    <section id="advanced-section">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="pdf_compression">Compression</label></th>
                                <td>
                                    <select id="pdf_compression" name="pdf_builder_settings[pdf_builder_pdf_compression]">
                                        <option value="none" <?php selected($settings['pdf_builder_pdf_compression'] ?? 'medium', 'none'); ?>>Aucune</option>
                                        <option value="medium" <?php selected($settings['pdf_builder_pdf_compression'] ?? 'medium', 'medium'); ?>>Moyenne</option>
                                        <option value="high" <?php selected($settings['pdf_builder_pdf_compression'] ?? 'medium', 'high'); ?>>Élevée</option>
                                    </select>
                                    <p class="description">Réduit la taille des fichiers PDF</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="pdf_metadata_enabled">Métadonnées</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="hidden" name="pdf_builder_settings[pdf_builder_pdf_metadata_enabled]" value="0">
                                        <input type="checkbox" id="pdf_metadata_enabled" name="pdf_builder_settings[pdf_builder_pdf_metadata_enabled]" value="1" <?php checked($settings['pdf_builder_pdf_metadata_enabled'] ?? '1', '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Inclure titre, auteur et sujet dans les propriétés PDF</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="pdf_print_optimized">Optimisé impression</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="hidden" name="pdf_builder_settings[pdf_builder_pdf_print_optimized]" value="0">
                                        <input type="checkbox" id="pdf_print_optimized" name="pdf_builder_settings[pdf_builder_pdf_print_optimized]" value="1" <?php checked($settings['pdf_builder_pdf_print_optimized'] ?? '1', '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Ajuster les couleurs et la résolution pour l'impression</p>
                                </td>
                            </tr>
                        </table>
                    </section>
                <?php endif; ?>
                </section>

            <!-- JavaScript déplacé vers settings-main.php pour éviter les conflits -->




