<?php // PDF tab content - Updated: 2025-11-19 01:40:00

$settings = pdf_builder_get_option('pdf_builder_settings', array());
error_log('[PDF Builder] settings-pdf.php loaded - settings count: ' . count($settings));

// Recuperer les parametres du Canvas
$canvas_format = $settings['pdf_builder_canvas_format'] ?? 'A4';
$canvas_orientation = $settings['pdf_builder_canvas_default_orientation'] ?? 'portrait';

// Verifier si l'utilisateur a une licence premium
$license_manager = \PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance();
$is_premium = $license_manager->isPremium();
?>

<!-- Section Principale : Configuration PDF -->
<section id="pdf-config" class="pdf-section contenu-canvas-section">
    <h3 style="display: flex; justify-content: flex-start; align-items: center;">
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
                    <th scope="row"><label>Format de page</label></th>
                    <td>
                        <?php 
                            $format_labels = ['A4' => 'A4', 'A3' => 'A3', 'Letter' => 'Lettre US'];
                        ?>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-weight: 600; font-size: 16px; color: #667eea;">📋 <?php echo esc_html($format_labels[$canvas_format] ?? $canvas_format); ?></span>
                            <a href="#" class="button button-small" onclick="if(window.PDFBuilderTabsAPI && PDFBuilderTabsAPI.switchToTab) { PDFBuilderTabsAPI.switchToTab('canvas'); return false; } else if(window.switchTab) { switchTab('canvas'); return false; } else { window.location.hash = '#canvas'; return false; }">Modifier dans Canvas →</a>
                        </div>
                        <p class="description" style="margin-top: 12px; color: #666; font-size: 12px;">Le format PDF est synchronisé avec le format du Canvas. Pour le modifier, accédez à l'onglet <strong>Canvas</strong>.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label>Orientation</label></th>
                    <td>
                        <?php 
                            $orientation_labels = ['portrait' => 'Portrait', 'landscape' => 'Paysage'];
                            $orientation_emoji = ($canvas_orientation === 'landscape') ? '📄' : '📋';
                        ?>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-weight: 600; font-size: 16px; color: #667eea;"><?php echo $orientation_emoji; ?> <?php echo esc_html($orientation_labels[$canvas_orientation] ?? $canvas_orientation); ?></span>
                            <a href="#" class="button button-small" onclick="if(window.PDFBuilderTabsAPI && PDFBuilderTabsAPI.switchToTab) { PDFBuilderTabsAPI.switchToTab('canvas'); return false; } else if(window.switchTab) { switchTab('canvas'); return false; } else { window.location.hash = '#canvas'; return false; }">Modifier dans Canvas →</a>
                        </div>
                        <p class="description" style="margin-top: 12px; color: #666; font-size: 12px;">L'orientation PDF est synchronisée avec l'orientation du Canvas. Pour la modifier, accédez à l'onglet <strong>Canvas</strong>.</p>
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
                        <input type="number" id="export_quality" name="pdf_builder_settings[pdf_builder_canvas_export_quality]" value="<?php echo esc_attr($settings['pdf_builder_canvas_export_quality'] ?? '90'); ?>" min="1" max="100">
                        <p class="description">Qualité de l'image exportée (1-100%)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="export_format">Format export</label></th>
                    <td>
                        <?php 
                            $can_use_multi_format_export = \PDF_Builder\Managers\PDF_Builder_Feature_Manager::canUseFeature('multi_format_export');
                            $current_export_format = $settings['pdf_builder_canvas_export_format'] ?? 'pdf';
                            $png_disabled = !$can_use_multi_format_export ? 'disabled' : '';
                            $png_label = !$can_use_multi_format_export ? 'PNG ⭐ PREMIUM' : 'PNG';
                            $jpg_disabled = !$can_use_multi_format_export ? 'disabled' : '';
                            $jpg_label = !$can_use_multi_format_export ? 'JPG ⭐ PREMIUM' : 'JPG';
                            $svg_disabled = !$can_use_multi_format_export ? 'disabled' : '';
                            $svg_label = !$can_use_multi_format_export ? 'SVG ⭐ PREMIUM' : 'SVG';
                        ?>
                        <select id="export_format" name="pdf_builder_settings[pdf_builder_canvas_export_format]">
                            <option value="pdf" <?php selected($current_export_format, 'pdf'); ?>>PDF</option>
                            <option value="png" <?php selected($current_export_format, 'png'); ?> <?php echo esc_attr($png_disabled); ?>><?php echo esc_html($png_label); ?></option>
                            <option value="jpg" <?php selected($current_export_format, 'jpg'); ?> <?php echo esc_attr($jpg_disabled); ?>><?php echo esc_html($jpg_label); ?></option>
                            <option value="svg" <?php selected($current_export_format, 'svg'); ?> <?php echo esc_attr($svg_disabled); ?>><?php echo esc_html($svg_label); ?></option>
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
                <?php if ($is_premium): ?>
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
                <?php endif; ?>
            </table>

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
        </div><!-- pdfb-pdf-settings-left -->

        <!-- Panel d'aperçu PDF -->
        <div class="pdfb-pdf-preview-panel" data-canvas-format="<?php echo esc_attr($canvas_format ?? 'A4'); ?>" data-canvas-orientation="<?php echo esc_attr($canvas_orientation ?? 'portrait'); ?>">
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
</section><!-- pdf-config -->

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
    
    // Fonction pour mettre a jour l'apercu
    function updatePdfPreview() {
        const quality = $('#pdf_quality').val() || 'high';
        const canvasFormat = $('.pdfb-pdf-preview-panel').attr('data-canvas-format') || 'A4';
        const canvasOrientation = $('.pdfb-pdf-preview-panel').attr('data-canvas-orientation') || 'portrait';
        const compression = $('#pdf_compression').val() || 'medium';
        const exportQuality = parseInt($('#export_quality').val()) || 90;
        
        console.log('📄 [PDF Preview Update] Quality:', quality, 'Format:', canvasFormat, 'Orientation:', canvasOrientation, 'Compression:', compression, 'Export Quality:', exportQuality);
        
        // Mises a jour visuelles
        const qualityConfig = qualityConfigs[quality] || qualityConfigs['high'];
        const formatConfig = pdfFormats[canvasFormat] || pdfFormats['A4'];
        
        // Ratio d'aspect
        let ratio = formatConfig.ratio;
        if (canvasOrientation === 'landscape') {
            ratio = formatConfig.ratio.split('/').reverse().join('/');
        }
        
        $('#pdf-preview-frame').css('--preview-ratio', ratio);
        $('#pdf-preview-frame').attr('data-format', canvasFormat + ' ' + (canvasOrientation === 'portrait' ? '📋' : '📄'));
        
        // Infos
        $('#preview-format').text(canvasFormat);
        $('#preview-orientation').text(canvasOrientation === 'portrait' ? 'Portrait' : 'Paysage');
        $('#preview-quality').text(qualityConfig.label);
        
        // Gestion de la compression
        const compressionLabel = (qualityConfigs[compression] && qualityConfigs[compression].compression) ? qualityConfigs[compression].compression : 'Moyenne';
        $('#preview-compression').text(compressionLabel);
        
        // Barre de qualite
        const qualityPercent = Math.round((exportQuality / 100) * 100);
        $('#preview-quality-bar').css('width', qualityPercent + '%');
        
        // Estimation de taille
        const baseSizeKb = 500;
        const qualityMultiplier = qualityConfig.factor;
        const compressionFactor = compression === 'high' ? 0.6 : (compression === 'medium' ? 0.85 : 1.0);
        const estimatedSize = Math.round(baseSizeKb * qualityMultiplier * (exportQuality / 100) * compressionFactor);
        
        $('#preview-file-size').text('~' + (estimatedSize < 1024 ? estimatedSize + ' KB' : (estimatedSize / 1024).toFixed(1) + ' MB'));
        
        console.log('📄 [PDF Preview] Apercu mis a jour - Taille estimee:', estimatedSize + 'KB');
    }
    
    // Ecouter les changements avec support input et change
    $(document).ready(function() {
        console.log('📄 [PDF Settings] Initialisation du systeme d\'apercu PDF');
        
        // Selects et dropdowns (evenement change) - Format et orientation viennent du Canvas
        $('#pdf_quality, #pdf_compression').on('change', function() {
            console.log('📄 [PDF Change Event] Selecteur change:', $(this).attr('id'), 'Valeur:', $(this).val());
            updatePdfPreview();
        });
        
        // Input number (evenement input pour temps reel + change pour le fallback)
        $('#export_quality').on('input change', function() {
            console.log('📄 [PDF Input Event] Qualite export changee:', $(this).val());
            updatePdfPreview();
        });
        
        // Initialisation
        console.log('📄 [PDF Settings] Initialisation de l\'apercu');
        updatePdfPreview();
        console.log('📄 [PDF Settings] Systeme d\'apercu PDF pret');
    });
})(jQuery);
</script>

