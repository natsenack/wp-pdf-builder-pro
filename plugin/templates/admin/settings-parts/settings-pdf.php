<?php // PDF tab content - Updated: 2025-11-19 01:40:00

$settings = pdf_builder_get_option('pdf_builder_settings', array());
error_log('[PDF Builder] settings-pdf.php loaded - settings count: ' . count($settings));

// Vérifier si l'utilisateur a une licence premium
$license_manager = \PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance();
$is_premium = $license_manager->isPremium();
?>

<style>
/* Layout flexbox pour les paramètres PDF avec aperçu */
.pdfb-pdf-settings-wrapper {
    display: flex;
    gap: 30px;
    align-items: flex-start;
    margin-bottom: 30px;
}

.pdfb-pdf-settings-left {
    flex: 1;
    min-width: 0;
}

.pdfb-pdf-preview-panel {
    flex: 0 0 350px;
    background: linear-gradient(135deg, #667eea 0%, #5568d3 100%);
    border-radius: 12px;
    padding: 25px;
    color: white;
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
    position: sticky;
    top: 20px;
}

.pdfb-pdf-preview-title {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.pdfb-pdf-preview-canvas {
    background: white;
    border-radius: 8px;
    margin-bottom: 20px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.pdfb-pdf-preview-frame {
    width: 100%;
    aspect-ratio: var(--preview-ratio, 210/297);
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: #999;
    padding: 10px;
    text-align: center;
    position: relative;
}

.pdfb-pdf-preview-frame::before {
    content: attr(data-format);
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.pdfb-pdf-preview-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    font-size: 13px;
}

.pdfb-pdf-info-item {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 8px;
    padding: 12px;
    backdrop-filter: blur(10px);
}

.pdfb-pdf-info-label {
    font-size: 11px;
    text-transform: uppercase;
    font-weight: 600;
    opacity: 0.9;
    display: block;
    margin-bottom: 4px;
}

.pdfb-pdf-info-value {
    font-size: 14px;
    font-weight: 700;
}

.pdfb-pdf-quality-bar {
    background: rgba(255, 255, 255, 0.2);
    height: 6px;
    border-radius: 3px;
    overflow: hidden;
    margin-top: 4px;
}

.pdfb-pdf-quality-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981 0%, #fbbf24 50%, #ef4444 100%);
    width: 90%;
    border-radius: 3px;
}

.pdfb-pdf-file-size {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    padding: 12px;
    margin-top: 15px;
    font-size: 13px;
    text-align: center;
}

.pdfb-pdf-file-size-value {
    font-size: 16px;
    font-weight: 700;
    margin: 4px 0;
}

@media (max-width: 1200px) {
    .pdfb-pdf-settings-wrapper {
        flex-direction: column;
    }
    
    .pdfb-pdf-preview-panel {
        flex: none;
        width: 100%;
        position: static;
    }
}
</style>

            <!-- Section Principale -->
            <section id="pdf" class="pdf-section contenu-canvas-section">
            <h3 class="" style="display: flex; justify-content: flex-start; align-items: center;">
                <span>📄 Configuration PDF</span>
            </h3>
            
            <div class="pdfb-pdf-settings-wrapper">
                <div class="pdfb-pdf-settings-left">
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
                </div><!-- pdfb-pdf-settings-left -->
                
                <!-- Panel d'aperçu PDF -->
                <div class="pdfb-pdf-preview-panel">
                    <div class="pdfb-pdf-preview-title">
                        👁️ Aperçu PDF
                    </div>
                    
                    <div class="pdfb-pdf-preview-canvas">
                        <div class="pdfb-pdf-preview-frame" data-format="A4" id="pdf-preview-frame" style="--preview-ratio: 210/297;">
                        </div>
                    </div>
                    
                    <div class="pdfb-pdf-preview-info">
                        <div class="pdfb-pdf-info-item">
                            <span class="pdfb-pdf-info-label">Format</span>
                            <span class="pdfb-pdf-info-value" id="preview-format">A4</span>
                        </div>
                        <div class="pdfb-pdf-info-item">
                            <span class="pdfb-pdf-info-label">Orientation</span>
                            <span class="pdfb-pdf-info-value" id="preview-orientation">Portrait</span>
                        </div>
                        <div class="pdfb-pdf-info-item">
                            <span class="pdfb-pdf-info-label">Qualité</span>
                            <span class="pdfb-pdf-info-value" id="preview-quality">Haute</span>
                            <div class="pdfb-pdf-quality-bar">
                                <div class="pdfb-pdf-quality-fill" id="preview-quality-bar" style="width: 100%;"></div>
                            </div>
                        </div>
                        <div class="pdfb-pdf-info-item">
                            <span class="pdfb-pdf-info-label">Compression</span>
                            <span class="pdfb-pdf-info-value" id="preview-compression">Moyenne</span>
                        </div>
                    </div>
                    
                    <div class="pdfb-pdf-file-size">
                        <span style="opacity: 0.9;">Taille estimée</span>
                        <div class="pdfb-pdf-file-size-value" id="preview-file-size">~850 KB</div>
                        <small style="opacity: 0.8;">*(à titre indicatif)</small>
                    </div>
                </div><!-- pdfb-pdf-preview-panel -->
                
            </div><!-- pdfb-pdf-settings-wrapper -->
                </section>

            <!-- JavaScript pour l'aperçu PDF en temps réel -->
            <script>
            (function($) {
                'use strict';
                
                // Configuration des formats PDF
                const pdfFormats = {
                    'A4': { width: 210, height: 297, ratio: '210/297' },
                    'A3': { width: 297, height: 420, ratio: '297/420' },
                    'Letter': { width: 215.9, height: 279.4, ratio: '215.9/279.4' }
                };
                
                // Configuration des qualités
                const qualityConfigs = {
                    'low': { label: 'Rapide', factor: 0.6, compression: 'Élevée' },
                    'medium': { label: 'Équilibré', factor: 0.8, compression: 'Moyenne' },
                    'high': { label: 'Haute', factor: 1.0, compression: 'Minimale' }
                };
                
                // Fonction pour mettre à jour l'aperçu
                function updatePdfPreview() {
                    const quality = $('#pdf_quality').val() || 'high';
                    const format = $('#default_format').val() || 'A4';
                    const orientation = $('#default_orientation').val() || 'portrait';
                    const compression = $('#pdf_compression').val() || 'medium';
                    const exportQuality = parseInt($('#export_quality').val()) || 90;
                    
                    console.log('📄 [PDF Preview Update] Quality:', quality, 'Format:', format, 'Orientation:', orientation, 'Compression:', compression, 'Export Quality:', exportQuality);
                    
                    // Mises à jour visuelles
                    const qualityConfig = qualityConfigs[quality] || qualityConfigs['high'];
                    const formatConfig = pdfFormats[format] || pdfFormats['A4'];
                    
                    // Ratio d'aspect
                    let ratio = formatConfig.ratio;
                    if (orientation === 'landscape') {
                        ratio = formatConfig.ratio.split('/').reverse().join('/');
                    }
                    
                    $('#pdf-preview-frame').css('--preview-ratio', ratio);
                    $('#pdf-preview-frame').attr('data-format', format + ' ' + (orientation === 'portrait' ? '📋' : '📄'));
                    
                    // Infos
                    $('#preview-format').text(format);
                    $('#preview-orientation').text(orientation === 'portrait' ? 'Portrait' : 'Paysage');
                    $('#preview-quality').text(qualityConfig.label);
                    
                    // Gestion de la compression
                    const compressionLabel = (qualityConfigs[compression] && qualityConfigs[compression].compression) ? qualityConfigs[compression].compression : 'Moyenne';
                    $('#preview-compression').text(compressionLabel);
                    
                    // Barre de qualité
                    const qualityPercent = Math.round((exportQuality / 100) * 100);
                    $('#preview-quality-bar').css('width', qualityPercent + '%');
                    
                    // Estimation de taille
                    const baseSizeKb = 500;
                    const qualityMultiplier = qualityConfig.factor;
                    const compressionFactor = compression === 'high' ? 0.6 : (compression === 'medium' ? 0.85 : 1.0);
                    const estimatedSize = Math.round(baseSizeKb * qualityMultiplier * (exportQuality / 100) * compressionFactor);
                    
                    $('#preview-file-size').text('~' + (estimatedSize < 1024 ? estimatedSize + ' KB' : (estimatedSize / 1024).toFixed(1) + ' MB'));
                    
                    console.log('📄 [PDF Preview] Aperçu mis à jour - Taille estimée:', estimatedSize + 'KB');
                }
                
                // Écouter les changements avec support input et change
                $(document).ready(function() {
                    console.log('📄 [PDF Settings] Initialisation du système d\'aperçu PDF');
                    
                    // Sélects et dropdowns (événement change)
                    $('#pdf_quality, #default_format, #default_orientation, #pdf_compression').on('change', function() {
                        console.log('📄 [PDF Change Event] Sélecteur changé:', $(this).attr('id'), 'Valeur:', $(this).val());
                        updatePdfPreview();
                    });
                    
                    // Input number (événement input pour temps réel + change pour le fallback)
                    $('#export_quality').on('input change', function() {
                        console.log('📄 [PDF Input Event] Qualité export changée:', $(this).val());
                        updatePdfPreview();
                    });
                    
                    // Initialisation
                    console.log('📄 [PDF Settings] Initialisation de l\'aperçu');
                    updatePdfPreview();
                    console.log('📄 [PDF Settings] Système d\'aperçu PDF prêt');
                });
            })(jQuery);
            </script>

            <!-- JavaScript déplacé vers settings-main.php pour éviter les conflits -->





