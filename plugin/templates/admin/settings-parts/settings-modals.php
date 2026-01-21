<?php
/**
 * PDF Builder Pro - Canvas Settings Modals
 * Modal dialogs for canvas configuration
 * Created: 2025-12-12
 */

// Valeurs par défaut pour les champs Canvas
$canvas_defaults = [
    'width' => '794',
    'height' => '1123',
    'dpi' => '96',
    'format' => 'A4',
    'orientation' => 'portrait',
    'allow_portrait' => '1',
    'allow_landscape' => '1',
    'bg_color' => '#ffffff',
    'border_color' => '#cccccc',
    'border_width' => '1',
    'container_bg_color' => '#f8f9fa',
    'shadow_enabled' => '0',
    'grid_enabled' => '1',
    'grid_size' => '20',
    'guides_enabled' => '1',
    'snap_to_grid' => '1',
    'zoom_min' => '25',
    'zoom_max' => '500',
    'zoom_default' => '100',
    'zoom_step' => '25',
    'drag_enabled' => '1',
    'resize_enabled' => '1',
    'rotate_enabled' => '1',
    'multi_select' => '1',
    'selection_mode' => 'single',
    'keyboard_shortcuts' => '1',
    'export_quality' => '90',
    'export_format' => 'pdf',
    'export_transparent' => '0',
    'fps_target' => '60',
    'memory_limit_js' => '128',
    'response_timeout' => '5000',
    'debug_enabled' => '0',
    'performance_monitoring' => '1',
    'error_reporting' => '1'
];

// Ajuster les valeurs par défaut pour les utilisateurs gratuits (features premium)
$can_use_grid_navigation = \PDF_Builder\Managers\PdfBuilderFeatureManager::canUseFeature('grid_navigation');
if (!$can_use_grid_navigation) {
    $canvas_defaults['grid_enabled'] = '0';
    $canvas_defaults['guides_enabled'] = '0';
    $canvas_defaults['snap_to_grid'] = '0';
}

// Fonction helper pour récupérer une valeur canvas
function get_canvas_modal_value($key, $default = '') {
    // Récupérer depuis l'array unifié de settings
    $settings = pdf_builder_get_option('pdf_builder_settings', array());
    $option_key = 'pdf_builder_' . $key;
    $value = isset($settings[$option_key]) ? $settings[$option_key] : null;

    if ($value === null) {
        $value = $default;
    }

    // Validation spéciale pour les champs array corrompus
    $array_fields = ['canvas_dpi', 'canvas_formats', 'canvas_orientations'];
    if (in_array($key, $array_fields)) {
        // Si la valeur contient '0' ou est vide/invalide, utiliser la valeur par défaut
        if (empty($value) || $value === '0' || strpos($value, '0,') === 0 || $value === '0,0' || $value === '0,0,0,0,0') {
            $value = $default;
        }
    }

    // Validation premium: forcer à '0' si l'utilisateur n'a pas accès aux fonctionnalités de grille
    $premium_grid_keys = ['canvas_grid_enabled', 'canvas_guides_enabled', 'canvas_snap_to_grid'];
    if (in_array($key, $premium_grid_keys)) {
        if (!\PDF_Builder\Managers\PdfBuilderFeatureManager::canUseFeature('grid_navigation')) {
            $value = '0';
        }
    }

    return $value;
}
?>

<!-- MODAL AFFICHAGE -->
<div id="canvas-affichage-modal-overlay" class="canvas-modal-overlay" style="display: none;">
    <div class="canvas-modal-container" style="display: block; z-index: 10001;">
        <div class="canvas-modal-header">
            <div style="flex: 1; display: flex; align-items: center; gap: 15px;">
                <h3 style="margin: 0;"><span style="font-size: 24px;">📐</span> Paramètres d'Affichage</h3>
                <?php if (!\PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance()->is_premium()): ?>
                <div class="premium-header-notice" style="padding: 6px 12px; background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border: 1px solid #f39c12; border-radius: 6px; font-size: 12px; color: #856404; flex: 1; max-width: 52%">
                    <strong>🔒 Fonction Premium</strong> - Débloquez la personnalisation avancée du canvas (couleurs, bordures, formats étendus)
                    <a href="#" onclick="showUpgradeModal('canvas_settings')" style="color: #856404; text-decoration: underline; font-weight: 500; margin-left: 8px;">Passer en Premium →</a>
                </div>
                <?php endif; ?>
            </div>
            <button type="button" class="canvas-modal-close">&times;</button>
        </div>
        <div class="canvas-modal-body">
            <div class="modal-settings-grid">
                <div class="setting-group" style="grid-column: span 2;">
                    <label><span style="font-size: 16px;">📏</span> Dimensions du Canvas</label>
                    <div class="dimensions-display-compact">
                        <div class="dimensions-value">
                            <?php echo esc_html(get_canvas_modal_value('width', $canvas_defaults['width'])); ?> × <?php echo esc_html(get_canvas_modal_value('height', $canvas_defaults['height'])); ?> px
                        </div>
                        <div class="dimensions-format">
                            Format: <?php echo esc_html(get_canvas_modal_value('format', $canvas_defaults['format'])); ?> • <?php echo esc_html(get_canvas_modal_value('orientation', $canvas_defaults['orientation'])); ?>
                        </div>
                    </div>
                </div>
                <div class="setting-group">
                    <label><span style="font-size: 16px;">🔍</span> Résolutions DPI</label>
                    <div style="display: flex; flex-direction: column; margin-top: 8px;">
                        <?php
                        $current_dpi_string = get_canvas_modal_value('canvas_dpi', $canvas_defaults['dpi']);
                        // Convertir la valeur actuelle en tableau (peut être une chaîne ou un tableau sérialisé)
                        if (is_string($current_dpi_string) && strpos($current_dpi_string, ',') !== false) {
                            $current_dpis = explode(',', $current_dpi_string);
                        } elseif (is_array($current_dpi_string)) {
                            $current_dpis = $current_dpi_string;
                        } else {
                            // Valeur unique, la convertir en tableau
                            $current_dpis = [$current_dpi_string];
                        }
                        $current_dpis = array_map('strval', $current_dpis); // S'assurer que ce sont des chaînes

                        $is_premium = \PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance()->is_premium();
                        $can_use_high_dpi = \PDF_Builder\Managers\PdfBuilderFeatureManager::canUseFeature('high_dpi');

                        $dpi_options = [
                            ['value' => '72', 'label' => '72 DPI - Écran', 'desc' => 'Faible qualité', 'premium' => false],
                            ['value' => '96', 'label' => '96 DPI - Web', 'desc' => 'Qualité standard', 'premium' => false],
                            ['value' => '150', 'label' => '150 DPI - Impression', 'desc' => 'Moyenne qualité', 'premium' => false],
                            ['value' => '300', 'label' => '300 DPI - Haute qualité', 'desc' => 'Professionnel', 'premium' => true],
                            ['value' => '600', 'label' => '600 DPI - Ultra HD', 'desc' => 'Maximum', 'premium' => true]
                        ];

                        foreach ($dpi_options as $option) {
                            $disabled = ($option['premium'] && !$can_use_high_dpi) ? 'disabled' : '';
                            $checked = in_array($option['value'], $current_dpis) ? 'checked' : '';
                            $premium_class = $option['premium'] ? 'premium-option' : '';

                            echo '<label style="display: flex; align-items: center; gap: 12px; margin: 0; padding: 8px; border-radius: 8px; transition: background 0.2s ease; ' . ($option['premium'] && !$can_use_high_dpi ? 'opacity: 0.6;' : '') . '" class="' . $premium_class . '" onmouseover="this.style.background=\'#f8f9fa\'" onmouseout="this.style.background=\'transparent\'">';
                            echo '<input type="checkbox" name="pdf_builder_canvas_dpi[]" value="' . $option['value'] . '" ' . $checked . ' ' . $disabled . '>';
                            echo '<div style="flex: 1;">';
                            echo '<div style="font-weight: 500; color: #2c3e50;">' . $option['label'] . '</div>';
                            echo '<div style="font-size: 12px; color: #6c757d;">' . $option['desc'] . '</div>';
                            echo '</div>';
                            if ($option['premium']) {
                                echo '<span class="premium-badge">⭐ PREMIUM</span>';
                            }
                            echo '</label>';
                        }
                        ?>

                        <div class="info-box">
                            <strong>ℹ️ Information:</strong> Les résolutions sélectionnées seront disponibles dans les paramètres des templates.
                        </div>
                    </div>
                </div>
                <div class="setting-group">
                    <label><span style="font-size: 16px;">📄</span> Formats de Document Disponibles</label>
                    <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 12px;">
                        <?php
                        // Récupérer les formats actuellement sélectionnés
                        $current_formats_string = get_canvas_modal_value('canvas_formats', 'A4');
                        $current_formats = [];

                        // Convertir la valeur actuelle en tableau
                        if (is_string($current_formats_string) && strpos($current_formats_string, ',') !== false) {
                            $current_formats = explode(',', $current_formats_string);
                        } elseif (is_array($current_formats_string)) {
                            $current_formats = $current_formats_string;
                        } else {
                            // Valeur unique, la convertir en tableau
                            $current_formats = [$current_formats_string];
                        }
                        $current_formats = array_map('strval', $current_formats); // S'assurer que ce sont des chaînes

                        $is_premium = \PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance()->is_premium();
                        $can_use_extended_formats = \PDF_Builder\Managers\PdfBuilderFeatureManager::canUseFeature('extended_formats');

                        $format_options = [
                            ['value' => 'A4', 'label' => 'A4 (210×297mm)', 'desc' => 'Format standard européen', 'icon' => '📄', 'premium' => false],
                            ['value' => 'A3', 'label' => 'A3 (297×420mm)', 'desc' => 'Format double A4', 'icon' => '📃', 'premium' => true],
                            ['value' => 'Letter', 'label' => 'Letter (8.5×11")', 'desc' => 'Format américain standard', 'icon' => '🇺🇸', 'premium' => true],
                            ['value' => 'Legal', 'label' => 'Legal (8.5×14")', 'desc' => 'Format américain légal', 'icon' => '⚖️', 'premium' => true],
                            ['value' => 'Label', 'label' => 'Étiquette Colis (100×150mm)', 'desc' => 'Format pour étiquettes de colis', 'icon' => '📦', 'premium' => true]
                        ];

                        foreach ($format_options as $option) {
                            $disabled = ($option['premium'] && !$can_use_extended_formats) ? 'disabled' : '';
                            $checked = in_array($option['value'], $current_formats) ? 'checked' : '';
                            $premium_class = $option['premium'] ? 'premium-option' : '';

                            echo '<label style="display: flex; align-items: center; gap: 12px; margin: 0; padding: 8px; border-radius: 8px; transition: background 0.2s ease; ' . ($option['premium'] && !$can_use_extended_formats ? 'opacity: 0.6;' : '') . '" class="' . $premium_class . '" onmouseover="this.style.background=\'#f8f9fa\'" onmouseout="this.style.background=\'transparent\'">';
                            echo '<input type="checkbox" name="pdf_builder_canvas_formats[]" value="' . $option['value'] . '" ' . $checked . ' ' . $disabled . '>';
                            echo '<div style="flex: 1;">';
                            echo '<div style="font-weight: 500; color: #2c3e50;">' . $option['icon'] . ' ' . $option['label'] . '</div>';
                            echo '<div style="font-size: 12px; color: #6c757d;">' . $option['desc'] . '</div>';
                            echo '</div>';
                            if ($option['premium']) {
                                echo '<span class="premium-badge">⭐ PREMIUM</span>';
                            }
                            echo '</label>';
                        }
                        ?>

                        <div class="info-box">
                            <strong>ℹ️ Information:</strong> Les formats sélectionnés seront disponibles dans les paramètres des templates.
                        </div>
                    </div>
                </div>
                <div class="setting-group">
                    <label><span style="font-size: 16px;">🔄</span> Orientations Disponibles</label>
                    <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 12px;">
                        <?php
                        // Récupérer les orientations actuellement sélectionnées
                        $current_orientations_string = get_canvas_modal_value('canvas_orientations', 'portrait,landscape');
                        $current_orientations = [];

                        // Convertir la valeur actuelle en tableau
                        if (is_string($current_orientations_string) && strpos($current_orientations_string, ',') !== false) {
                            $current_orientations = explode(',', $current_orientations_string);
                        } elseif (is_array($current_orientations_string)) {
                            $current_orientations = $current_orientations_string;
                        } else {
                            // Valeur unique, la convertir en tableau
                            $current_orientations = [$current_orientations_string];
                        }
                        $current_orientations = array_map('strval', $current_orientations); // S'assurer que ce sont des chaînes

                        $orientation_options = [
                            ['value' => 'portrait', 'label' => 'Portrait', 'desc' => '794×1123 px • Vertical', 'icon' => '📱'],
                            ['value' => 'landscape', 'label' => 'Paysage', 'desc' => '1123×794 px • Horizontal', 'icon' => '🖥️']
                        ];

                        foreach ($orientation_options as $option) {
                            $checked = in_array($option['value'], $current_orientations) ? 'checked' : '';

                            echo '<label style="display: flex; align-items: center; gap: 12px; margin: 0; padding: 8px; border-radius: 8px; transition: background 0.2s ease; ' . ($option['value'] === 'portrait' ? 'opacity: 1;' : '') . '" onmouseover="this.style.background=\'#f8f9fa\'" onmouseout="this.style.background=\'transparent\'">';
                            echo '<input type="checkbox" name="pdf_builder_canvas_orientations[]" value="' . $option['value'] . '" ' . $checked . '>';
                            echo '<div style="flex: 1;">';
                            echo '<div style="font-weight: 500; color: #2c3e50;">' . $option['icon'] . ' ' . $option['label'] . '</div>';
                            echo '<div style="font-size: 12px; color: #6c757d;">' . $option['desc'] . '</div>';
                            echo '</div>';
                            echo '</label>';
                        }
                        ?>

                        <div class="info-box">
                            <strong>ℹ️ Information:</strong> Les orientations sélectionnées seront disponibles dans les paramètres des templates.
                        </div>
                    </div>
                </div>
                <div class="setting-group">
                    <label style="display: flex; align-items: center; justify-content: space-between;"><span style="font-size: 16px;">🔳</span> Bordure du canvas <span class="premium-badge">⭐ PREMIUM</span></label>
                    <?php $can_use_custom_colors = \PDF_Builder\Managers\PdfBuilderFeatureManager::canUseFeature('custom_colors'); ?>
                    <?php if ($can_use_custom_colors): ?>
                    <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 12px;">
                        <div style="flex: 1;">
                            <label style="font-size: 12px; color: #6c757d; display: block; margin-bottom: 4px;">Couleur</label>
                            <input type="color" id="modal_canvas_border_color" name="pdf_builder_canvas_border_color"
                                   value="<?php echo esc_attr(get_canvas_modal_value('canvas_border_color', $canvas_defaults['border_color'])); ?>"
                                   style="width: 60px; height: 36px; border: none; border-radius: 6px; cursor: pointer; padding: 5px;">
                        </div>
                        <div style="flex: 1;">
                            <label style="font-size: 12px; color: #6c757d; display: block; margin-bottom: 4px;">Épaisseur</label>
                            <input type="number" id="modal_canvas_border_width" name="pdf_builder_canvas_border_width"
                                   value="<?php echo esc_attr(get_canvas_modal_value('canvas_border_width', $canvas_defaults['border_width'])); ?>"
                                   min="0" max="20" style="width: 100%;">
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <label for="modal_canvas_shadow_enabled" style="font-weight: 500; cursor: pointer; flex: 1;">Ombre activée</label>
                        <div class="toggle-switch">
                            <input type="checkbox" id="modal_canvas_shadow_enabled" name="pdf_builder_canvas_shadow_enabled"
                                   value="1" <?php checked(get_canvas_modal_value('canvas_shadow_enabled', $canvas_defaults['shadow_enabled']), '1'); ?>>
                            <label for="modal_canvas_shadow_enabled"></label>
                        </div>
                    </div>
                    <?php else: ?>
                    <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 12px; opacity: 0.6; pointer-events: none;">
                        <div style="flex: 1;">
                            <label style="font-size: 12px; color: #6c757d; display: block; margin-bottom: 4px;">Couleur</label>
                            <input type="color" id="modal_canvas_border_color" name="pdf_builder_canvas_border_color"
                                   value="#cccccc" disabled
                                   style="width: 60px; height: 36px; border: none; border-radius: 6px; cursor: not-allowed; padding: 5px;">
                        </div>
                        <div style="flex: 1;">
                            <label style="font-size: 12px; color: #6c757d; display: block; margin-bottom: 4px;">Épaisseur</label>
                            <input type="number" id="modal_canvas_border_width" name="pdf_builder_canvas_border_width"
                                   value="1" disabled
                                   min="0" max="20" style="width: 100%;">
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px; opacity: 0.6; pointer-events: none;">
                        <label for="modal_canvas_shadow_enabled" style="font-weight: 500; cursor: pointer; flex: 1;">Ombre activée</label>
                        <div class="toggle-switch">
                            <input type="checkbox" id="modal_canvas_shadow_enabled" name="pdf_builder_canvas_shadow_enabled"
                                   value="0" disabled>
                            <label for="modal_canvas_shadow_enabled"></label>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="setting-group">
                    <label><span style="font-size: 16px;">🎨</span> Couleur de Fond du Conteneur <span class="premium-badge">⭐ PREMIUM</span></label>
                    <?php if ($can_use_custom_colors): ?>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <input type="color" id="modal_canvas_container_bg_color" name="pdf_builder_canvas_container_bg_color"
                               value="<?php echo esc_attr(get_canvas_modal_value('canvas_container_bg_color', $canvas_defaults['container_bg_color'])); ?>"
                               style="width: 60px; height: 40px; border: none; border-radius: 8px; cursor: pointer; padding: 5px;">
                        <input type="text" readonly value="<?php echo esc_attr(get_canvas_modal_value('canvas_container_bg_color', $canvas_defaults['container_bg_color'])); ?>"
                               style="flex: 1; font-family: monospace; background: #f8f9fa; border: 1px solid #e1e5e9;">
                    </div>
                    <?php else: ?>
                    <div style="display: flex; gap: 12px; align-items: center; opacity: 0.6; pointer-events: none;">
                        <input type="color" id="modal_canvas_container_bg_color" name="pdf_builder_canvas_container_bg_color"
                               value="#f8f9fa" disabled
                               style="width: 60px; height: 40px; border: none; border-radius: 8px; cursor: not-allowed; padding: 5px;">
                        <input type="text" readonly value="#f8f9fa"
                               style="flex: 1; font-family: monospace; background: #f8f9fa; border: 1px solid #e1e5e9;">
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="canvas-modal-footer">
            <button type="button" class="button canvas-modal-cancel">❌ Annuler</button>
            <button type="button" class="button button-primary canvas-modal-apply" data-category="affichage">✅ Appliquer</button>
        </div>
    </div>
</div>

<!-- MODAL NAVIGATION -->
<div id="canvas-navigation-modal-overlay" class="canvas-modal-overlay" style="display: none;">
    <div class="canvas-modal-container" style="display: block; z-index: 10001;">
        <div class="canvas-modal-header">
            <h3><span style="font-size: 24px;">🧭</span> Paramètres de Navigation</h3>
            <button type="button" class="canvas-modal-close">&times;</button>
        </div>
        <div class="canvas-modal-body">
            <div class="modal-settings-grid">
                <?php $can_use_grid_navigation = \PDF_Builder\Managers\PdfBuilderFeatureManager::canUseFeature('grid_navigation'); ?>
                <div class="setting-group">
                    <label><span style="font-size: 16px;">📐</span> Grille activée</label>
                    <div class="toggle-switch<?php echo !$can_use_grid_navigation ? ' disabled' : ''; ?>"<?php echo !$can_use_grid_navigation ? ' style="opacity: 0.6; pointer-events: none;"' : ''; ?>>
                        <input type="checkbox" id="modal_canvas_grid_enabled" name="pdf_builder_canvas_grid_enabled"
                               value="1"<?php echo !$can_use_grid_navigation ? ' disabled' : ''; ?> <?php checked(get_canvas_modal_value('canvas_grid_enabled', $canvas_defaults['grid_enabled']), '1'); ?>>
                        <label for="modal_canvas_grid_enabled"></label>
                    </div>
                    <?php if (!$can_use_grid_navigation): ?>
                    <span class="premium-badge">⭐ PREMIUM</span>
                    <?php endif; ?>
                </div>
                <div class="setting-group">
                    <label><span style="font-size: 16px;">📏</span> Taille grille (px)</label>
                    <?php if ($can_use_grid_navigation): ?>
                    <input type="number" id="modal_canvas_grid_size" name="pdf_builder_canvas_grid_size"
                           value="<?php echo esc_attr(get_canvas_modal_value('canvas_grid_size', $canvas_defaults['grid_size'])); ?>">
                    <?php else: ?>
                    <input type="number" id="modal_canvas_grid_size" name="pdf_builder_canvas_grid_size"
                           value="<?php echo esc_attr(get_canvas_modal_value('canvas_grid_size', $canvas_defaults['grid_size'])); ?>" disabled
                           style="opacity: 0.6; cursor: not-allowed;">
                    <span class="premium-badge">⭐ PREMIUM</span>
                    <?php endif; ?>
                </div>
                <div class="setting-group">
                    <label><span style="font-size: 16px;">📍</span> Guides activés</label>
                    <div class="toggle-switch<?php echo !$can_use_grid_navigation ? ' disabled' : ''; ?>"<?php echo !$can_use_grid_navigation ? ' style="opacity: 0.6; pointer-events: none;"' : ''; ?>>
                        <input type="checkbox" id="modal_canvas_guides_enabled" name="pdf_builder_canvas_guides_enabled"
                               value="1"<?php echo !$can_use_grid_navigation ? ' disabled' : ''; ?> <?php checked(get_canvas_modal_value('canvas_guides_enabled', $canvas_defaults['guides_enabled']), '1'); ?>>
                        <label for="modal_canvas_guides_enabled"></label>
                    </div>
                    <?php if (!$can_use_grid_navigation): ?>
                    <span class="premium-badge">⭐ PREMIUM</span>
                    <?php endif; ?>
                </div>
                <div class="setting-group">
                    <label><span style="font-size: 16px;">🧲</span> Accrochage à la grille</label>
                    <div class="toggle-switch<?php echo !$can_use_grid_navigation ? ' disabled' : ''; ?>"<?php echo !$can_use_grid_navigation ? ' style="opacity: 0.6; pointer-events: none;"' : ''; ?>>
                        <input type="checkbox" id="modal_canvas_snap_to_grid" name="pdf_builder_canvas_snap_to_grid"
                               value="1"<?php echo !$can_use_grid_navigation ? ' disabled' : ''; ?> <?php checked(get_canvas_modal_value('canvas_snap_to_grid', $canvas_defaults['snap_to_grid']), '1'); ?>>
                        <label for="modal_canvas_snap_to_grid"></label>
                    </div>
                    <?php if (!$can_use_grid_navigation): ?>
                    <span class="premium-badge">⭐ PREMIUM</span>
                    <?php endif; ?>
                </div>
                <div class="setting-group">
                    <label><span style="font-size: 16px;">🔍</span> Zoom minimum (%)</label>
                    <input type="number" id="modal_canvas_zoom_min" name="pdf_builder_canvas_zoom_min"
                           value="<?php echo esc_attr(get_canvas_modal_value('canvas_zoom_min', $canvas_defaults['zoom_min'])); ?>">
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_zoom_max">Zoom maximum (%)</label>
                    <input type="number" id="modal_canvas_zoom_max" name="pdf_builder_canvas_zoom_max"
                           value="<?php echo esc_attr(get_canvas_modal_value('canvas_zoom_max', $canvas_defaults['zoom_max'])); ?>">
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_zoom_default">Zoom par défaut (%)</label>
                    <input type="number" id="modal_canvas_zoom_default" name="pdf_builder_canvas_zoom_default"
                           value="<?php echo esc_attr(get_canvas_modal_value('canvas_zoom_default', $canvas_defaults['zoom_default'])); ?>">
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_zoom_step">Pas de zoom (%)</label>
                    <input type="number" id="modal_canvas_zoom_step" name="pdf_builder_canvas_zoom_step"
                           value="<?php echo esc_attr(get_canvas_modal_value('canvas_zoom_step', $canvas_defaults['zoom_step'])); ?>">
                </div>
            </div>
        </div>
        <div class="canvas-modal-footer">
            <button type="button" class="button canvas-modal-cancel">❌ Annuler</button>
            <button type="button" class="button button-primary canvas-modal-apply" data-category="navigation">✅ Appliquer</button>
        </div>
    </div>
</div>

<!-- MODAL COMPORTEMENT -->
<div id="canvas-comportement-modal-overlay" class="canvas-modal-overlay" style="display: none;">
    <div class="canvas-modal-container" style="display: block; z-index: 10001;">
        <div class="canvas-modal-header">
            <h3><span style="font-size: 24px;">🎯</span> Paramètres de Comportement</h3>
            <button type="button" class="canvas-modal-close">&times;</button>
        </div>
        <div class="canvas-modal-body">
            <div class="modal-settings-grid">
                <div class="setting-group">
                    <label><span style="font-size: 16px;">✋</span> Glisser activé</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="modal_canvas_drag_enabled" name="pdf_builder_canvas_drag_enabled"
                               value="1" <?php checked(get_canvas_modal_value('canvas_drag_enabled', $canvas_defaults['drag_enabled']), '1'); ?>>
                        <label for="modal_canvas_drag_enabled"></label>
                    </div>
                </div>
                <div class="setting-group">
                    <label><span style="font-size: 16px;">📐</span> Redimensionnement activé</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="modal_canvas_resize_enabled" name="pdf_builder_canvas_resize_enabled"
                               value="1" <?php checked(get_canvas_modal_value('canvas_resize_enabled', $canvas_defaults['resize_enabled']), '1'); ?>>
                        <label for="modal_canvas_resize_enabled"></label>
                    </div>
                </div>
                <div class="setting-group">
                    <label><span style="font-size: 16px;">🔄</span> Rotation activée</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="modal_canvas_rotate_enabled" name="pdf_builder_canvas_rotate_enabled"
                               value="1" <?php checked(get_canvas_modal_value('canvas_rotate_enabled', $canvas_defaults['rotate_enabled']), '1'); ?>>
                        <label for="modal_canvas_rotate_enabled"></label>
                    </div>
                </div>
                <div class="setting-group">
                    <label><span style="font-size: 16px;">☑️</span> Sélection multiple</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="modal_canvas_multi_select" name="pdf_builder_canvas_multi_select"
                               value="1" <?php checked(get_canvas_modal_value('canvas_multi_select', $canvas_defaults['multi_select']), '1'); ?>>
                        <label for="modal_canvas_multi_select"></label>
                    </div>
                </div>
                <div class="setting-group">
                    <label><span style="font-size: 16px;">🎯</span> Mode de sélection</label>
                    <?php $can_use_advanced_selection = \PDF_Builder\Managers\PdfBuilderFeatureManager::canUseFeature('advanced_selection'); ?>
                    <select id="modal_canvas_selection_mode" name="pdf_builder_canvas_selection_mode">
                        <option value="single" <?php selected(get_canvas_modal_value('canvas_selection_mode', $canvas_defaults['selection_mode']), 'single'); ?>>Simple</option>
                        <option value="multiple" <?php selected(get_canvas_modal_value('canvas_selection_mode', $canvas_defaults['selection_mode']), 'multiple'); ?> <?php echo !$can_use_advanced_selection ? 'disabled' : ''; ?>><?php echo !$can_use_advanced_selection ? 'Multiple ⭐ PREMIUM' : 'Multiple'; ?></option>
                        <option value="group" <?php selected(get_canvas_modal_value('canvas_selection_mode', $canvas_defaults['selection_mode']), 'group'); ?> <?php echo !$can_use_advanced_selection ? 'disabled' : ''; ?>><?php echo !$can_use_advanced_selection ? 'Groupe ⭐ PREMIUM' : 'Groupe'; ?></option>
                    </select>
                    <?php if (!$can_use_advanced_selection): ?>
                        <div class="info-box" style="margin-top: 8px; padding: 8px; background: #fff3cd; border: 1px solid #f39c12; border-radius: 4px; font-size: 12px;">
                            <strong>🔒 Fonction Premium</strong> - Débloquez les modes de sélection avancés (Multiple et Groupe)
                            <a href="#" onclick="showUpgradeModal('canvas_settings')" style="color: #856404; text-decoration: underline; font-weight: 500;">Passer en Premium →</a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_keyboard_shortcuts">Raccourcis clavier</label>
                    <?php $can_use_keyboard_shortcuts = \PDF_Builder\Managers\PdfBuilderFeatureManager::canUseFeature('keyboard_shortcuts'); ?>
                    <?php if ($can_use_keyboard_shortcuts): ?>
                    <div class="toggle-switch">
                        <input type="checkbox" id="modal_canvas_keyboard_shortcuts" name="pdf_builder_canvas_keyboard_shortcuts"
                               value="1" <?php checked(get_canvas_modal_value('canvas_keyboard_shortcuts', $canvas_defaults['keyboard_shortcuts']), '1'); ?>>
                        <label for="modal_canvas_keyboard_shortcuts"></label>
                    </div>
                    <?php else: ?>
                    <div class="toggle-switch" style="opacity: 0.6; pointer-events: none;">
                        <input type="checkbox" id="modal_canvas_keyboard_shortcuts" name="pdf_builder_canvas_keyboard_shortcuts"
                               value="1" disabled>
                        <label for="modal_canvas_keyboard_shortcuts"></label>
                    </div>
                    <span class="premium-badge">⭐ PREMIUM</span>
                    <?php endif; ?>
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_export_quality">Qualité export (%)</label>
                    <input type="number" id="modal_canvas_export_quality" name="pdf_builder_canvas_export_quality"
                           value="<?php echo esc_attr(get_canvas_modal_value('canvas_export_quality', $canvas_defaults['export_quality'])); ?>">
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_export_format">Format export</label>
                    <?php $can_use_multi_format_export = \PDF_Builder\Managers\PdfBuilderFeatureManager::canUseFeature('multi_format_export'); ?>
                    <select id="modal_canvas_export_format" name="pdf_builder_canvas_export_format">
                        <option value="pdf" <?php selected(get_canvas_modal_value('canvas_export_format', $canvas_defaults['export_format']), 'pdf'); ?>>PDF</option>
                        <option value="png" <?php selected(get_canvas_modal_value('canvas_export_format', $canvas_defaults['export_format']), 'png'); ?> <?php echo !$can_use_multi_format_export ? 'disabled' : ''; ?>><?php echo !$can_use_multi_format_export ? 'PNG ⭐ PREMIUM' : 'PNG'; ?></option>
                        <option value="jpg" <?php selected(get_canvas_modal_value('canvas_export_format', $canvas_defaults['export_format']), 'jpg'); ?> <?php echo !$can_use_multi_format_export ? 'disabled' : ''; ?>><?php echo !$can_use_multi_format_export ? 'JPG ⭐ PREMIUM' : 'JPG'; ?></option>
                        <option value="svg" <?php selected(get_canvas_modal_value('canvas_export_format', $canvas_defaults['export_format']), 'svg'); ?> <?php echo !$can_use_multi_format_export ? 'disabled' : ''; ?>><?php echo !$can_use_multi_format_export ? 'SVG ⭐ PREMIUM' : 'SVG'; ?></option>
                    </select>
                    <?php if (!$can_use_multi_format_export): ?>
                        <div class="info-box" style="margin-top: 8px; padding: 8px; background: #fff3cd; border: 1px solid #f39c12; border-radius: 4px; font-size: 12px;">
                            <strong>🔒 Fonction Premium</strong> - Débloquez l'export PNG, JPG et SVG
                            <a href="#" onclick="showUpgradeModal('canvas_settings')" style="color: #856404; text-decoration: underline; font-weight: 500;">Passer en Premium →</a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_export_transparent">Fond transparent</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="modal_canvas_export_transparent" name="pdf_builder_canvas_export_transparent"
                               value="1" <?php checked(get_canvas_modal_value('canvas_export_transparent', $canvas_defaults['export_transparent']), '1'); ?>>
                        <label for="modal_canvas_export_transparent"></label>
                    </div>
                </div>
            </div>
        </div>
        <div class="canvas-modal-footer">
            <button type="button" class="button canvas-modal-cancel">❌ Annuler</button>
            <button type="button" class="button button-primary canvas-modal-apply" data-category="comportement">✅ Appliquer</button>
        </div>
    </div>
</div>

<!-- MODAL SYSTEME -->
<div id="canvas-systeme-modal-overlay" class="canvas-modal-overlay" style="display: none;">
    <div class="canvas-modal-container" style="display: block; z-index: 10001;">
        <div class="canvas-modal-header">
            <h3><span style="font-size: 24px;">⚙️</span> Paramètres Système</h3>
            <button type="button" class="canvas-modal-close">&times;</button>
        </div>
        <div class="canvas-modal-body">
            <div class="modal-settings-grid">
                <div class="setting-group">
                    <label><span style="font-size: 16px;">🎮</span> FPS cible</label>
                    <input type="number" id="modal_canvas_fps_target" name="pdf_builder_canvas_fps_target"
                           value="<?php echo esc_attr(get_canvas_modal_value('canvas_fps_target', $canvas_defaults['fps_target'])); ?>">
                </div>
                <div class="setting-group">
                    <label><span style="font-size: 16px;">🧠</span> Limite mémoire JS (MB)</label>
                    <input type="number" id="modal_canvas_memory_limit_js" name="pdf_builder_canvas_memory_limit_js"
                           value="<?php echo esc_attr(get_canvas_modal_value('canvas_memory_limit_js', $canvas_defaults['memory_limit_js'])); ?>">
                </div>
                <div class="setting-group">
                    <label><span style="font-size: 16px;">⏱️</span> Timeout réponse (ms)</label>
                    <input type="number" id="modal_canvas_response_timeout" name="pdf_builder_canvas_response_timeout"
                           value="<?php echo esc_attr(get_canvas_modal_value('canvas_response_timeout', $canvas_defaults['response_timeout'])); ?>">
                </div>
                <div class="setting-group">
                    <label><span style="font-size: 16px;">🐛</span> Debug activé</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="modal_canvas_debug_enabled" name="pdf_builder_canvas_debug_enabled"
                               value="1" <?php checked(get_canvas_modal_value('canvas_debug_enabled', $canvas_defaults['debug_enabled']), '1'); ?>>
                        <label for="modal_canvas_debug_enabled"></label>
                    </div>
                </div>
                <div class="setting-group">
                    <label><span style="font-size: 16px;">📊</span> Monitoring performance</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="modal_canvas_performance_monitoring" name="pdf_builder_canvas_performance_monitoring"
                               value="1" <?php checked(get_canvas_modal_value('canvas_performance_monitoring', $canvas_defaults['performance_monitoring']), '1'); ?>>
                        <label for="modal_canvas_performance_monitoring"></label>
                    </div>
                </div>
                <div class="setting-group">
                    <label><span style="font-size: 16px;">🚨</span> Rapport d'erreurs</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="modal_canvas_error_reporting" name="pdf_builder_canvas_error_reporting"
                               value="1" <?php checked(get_canvas_modal_value('canvas_error_reporting', $canvas_defaults['error_reporting']), '1'); ?>>
                        <label for="modal_canvas_error_reporting"></label>
                    </div>
                </div>
            </div>
        </div>
        <div class="canvas-modal-footer">
            <button type="button" class="button canvas-modal-cancel">❌ Annuler</button>
            <button type="button" class="button button-primary canvas-modal-apply" data-category="systeme">✅ Appliquer</button>
        </div>
    </div>
</div>

<style>
/* ===== STYLES SOBRES ET PROFESSIONNELS ===== */

/* Styles de base des modals */
.canvas-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    animation: modalFadeIn 0.2s ease-out;
}

.canvas-modal-container {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    max-width: 90vw;
    max-height: 90vh;
    width: 800px;
    overflow: hidden;
    animation: modalSlideIn 0.3s ease-out;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

/* Animations d'entrée */
@keyframes modalFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Header du modal */
.canvas-modal-header {
    background: #f8f9fa;
    color: #2c3e50;
    padding: 24px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #e1e5e9;
    margin-bottom: 0px;
}

.canvas-modal-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 12px;
}

.canvas-modal-close {
    background: #e9ecef;
    border: none;
    color: #6c757d;
    font-size: 24px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.canvas-modal-close:hover {
    background: #dee2e6;
    color: #495057;
}

/* Corps du modal */
.canvas-modal-body {
    padding: 15px;
    max-height: 65vh;
    overflow-y: auto;
    overflow-x: hidden;
    background: #ffffff;
}

/* Grille de paramètres harmonieuse */
.modal-settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 10px;
    margin-bottom: 0;
}

/* Groupes de paramètres */
.setting-group {
    background: #f8f9fa;
    border: 1px solid #e1e5e9;
    border-radius: 12px;
    padding: 5px;
    transition: all 0.2s ease;
    position: relative;
}

.setting-group:hover {
    border-color: #6c757d;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.setting-group label {
    display: block;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 12px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Styles pour les inputs */
.setting-group input[type="text"],
.setting-group input[type="number"],
.setting-group input[type="color"],
.setting-group select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s ease;
    background: white;
}

.setting-group input:focus,
.setting-group select:focus {
    outline: none;
    border-color: #6c757d;
    box-shadow: 0 0 0 3px rgba(108, 117, 125, 0.1);
}

/* Toggle switches améliorés */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-switch label {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: #dee2e6;
    border-radius: 12px; /* Moitié de la hauteur pour un cercle parfait */
    transition: 0.3s;
    border: 1px solid #adb5bd; /* Bordure subtile */
    height: 22px;
    
}

.toggle-switch label:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 2px; /* Ajusté pour centrer */
    top: 2px; /* Ajusté pour centrer */
    background: white;
    border-radius: 50%;
    transition: 0.3s;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2); /* Ombre subtile */
}

.toggle-switch input:checked + label {
    background: #6c757d;
    border-color: #5a6268;
    height: 22px;
}

.toggle-switch input:checked + label:before {
    transform: translateX(26px); /* Translation ajustée pour la nouvelle largeur */
    vertical-align: middle;
}

/* Checkboxes améliorés */
.setting-group input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #6c757d;
    border-radius: 4px;
    cursor: pointer;
}

/* Footer du modal */
.canvas-modal-footer {
    background: #f8f9fa;
    padding: 24px 32px;
    border-top: 1px solid #e1e5e9;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.canvas-modal-footer .button {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
    min-width: 100px;
}

.canvas-modal-cancel {
    background: #6c757d;
    color: white;
}

.canvas-modal-cancel:hover {
    background: #5a6268;
}

.canvas-modal-apply {
    background: #6c757d;
    color: white;
}

.canvas-modal-apply:hover {
    background: #5a6268;
}

/* Styles pour les options premium */
.premium-option {
    position: relative;
}

.premium-badge {
    background: #fff3cd;
    color: #856404;
    font-size: 10px;
    font-weight: bold;
    padding: 3px 8px;
    border-radius: 12px;
    border: 1px solid #ffeaa7;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-left: 8px;
}

.premium-option input:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.premium-option input:disabled + span {
    color: #6c757d;
}

/* Info boxes */
.setting-group .info-box {
    margin-top: 16px;
    padding: 12px 16px;
    background: #e7f3ff;
    border: 1px solid #b3d9ff;
    border-radius: 8px;
    font-size: 13px;
    color: #1e5b8b;
    line-height: 1.4;
}

.setting-group .warning-box {
    margin-top: 16px;
    padding: 12px 16px;
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 8px;
    font-size: 13px;
    color: #856404;
    line-height: 1.4;
}

/* Responsive */
@media (max-width: 768px) {
    .canvas-modal-container {
        width: 95vw;
        margin: 20px;
    }

    .canvas-modal-header,
    .canvas-modal-body,
    .canvas-modal-footer {
        padding: 20px;
    }

    .modal-settings-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
}

/* Amélioration du style des radio buttons DPI */
.setting-group input[type="radio"] {
    transform: scale(1.2);
    margin-right: 8px;
    accent-color: #6c757d;
}

/* Styles pour les dimensions (affichage compact) */
.dimensions-display-compact {
    background: #f8f9fa;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 12px 16px;
    text-align: center;
    margin-bottom: 0;
}

.dimensions-value {
    font-size: 16px;
    font-weight: 600;
    font-family: 'Monaco', 'Menlo', monospace;
    color: #2c3e50;
    margin-bottom: 4px;
}

.dimensions-format {
    font-size: 12px;
    color: #6c757d;
    opacity: 0.8;
}
</style>

<!-- JavaScript déplacé vers settings-main.php pour éviter les conflits -->
<script>
/**
 * Fonction pour afficher le modal de mise à niveau
 */
function showUpgradeModal(feature) {
    // Créer le modal de mise à niveau s'il n'existe pas
    if (!document.getElementById('upgrade-modal-overlay')) {
        var modalHTML = `
            <div id="upgrade-modal-overlay" class="canvas-modal-overlay" style="display: flex; z-index: 10002;">
                <div class="canvas-modal-container" style="max-width: 500px;">
                    <div class="canvas-modal-header">
                        <h3>🔒 Fonctionnalité Premium</h3>
                        <button type="button" class="canvas-modal-close" onclick="closeUpgradeModal()">&times;</button>
                    </div>
                    <div class="canvas-modal-body" style="text-align: center; padding: 30px;">
                        <div style="font-size: 48px; margin-bottom: 20px;">⭐</div>
                        <h4 style="margin-bottom: 15px; color: #23282d;">Débloquez cette fonctionnalité Premium</h4>
                        <p style="margin-bottom: 20px; color: #666; line-height: 1.5;">
                            Cette fonctionnalité est réservée aux utilisateurs Premium de PDF Builder Pro.
                        </p>
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                            <h5 style="margin: 0 0 10px 0; color: #23282d;">Avantages Premium :</h5>
                            <ul style="text-align: left; color: #666; margin: 0; padding-left: 20px;">
                                <li>✅ Résolutions DPI élevées (300 & 600 DPI)</li>
                                <li>✅ Templates illimités</li>
                                <li>✅ Support prioritaire</li>
                                <li>✅ Mises à jour gratuites à vie</li>
                                <li>✅ Fonctionnalités avancées</li>
                            </ul>
                        </div>
                        <div style="background: #e8f5e8; border: 2px solid #28a745; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                            <strong style="color: #155724; font-size: 18px;">69€ à vie</strong>
                            <br><small style="color: #155724;">Paiement unique, pas d'abonnement</small>
                        </div>
                        <a href="https://pdf-builder-pro.com/premium" target="_blank" class="button button-primary" style="background: #28a745; border-color: #28a745; padding: 12px 24px; font-size: 16px;">
                            🚀 Passer en Premium - 69€
                        </a>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    } else {
        document.getElementById('upgrade-modal-overlay').style.display = 'flex';
    }
}

/**
 * Fonction pour fermer le modal de mise à niveau
 */
function closeUpgradeModal() {
    var modal = document.getElementById('upgrade-modal-overlay');
    if (modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php






