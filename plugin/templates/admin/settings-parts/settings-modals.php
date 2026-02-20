<?php
/**
 * PDF Builder Pro - Canvas Settings Modals
 * Modal dialogs for canvas configuration
 * Created: 2025-12-12
 */

// Valeurs par défaut pour les champs Canvas - SYNCHRONISÉES avec Canvas_Manager.php
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
    'grid_enabled' => '0',  // false -> '0'
    'grid_size' => '10',    // CORRIGÉ: 20 -> 10 (cohérent avec Canvas_Manager)
    'guides_enabled' => '0', // false -> '0'
    'snap_to_grid' => '0',   // false -> '0'
    'zoom_min' => '10',     // CORRIGÉ: 25 -> 10 (cohérent avec Canvas_Manager)
    'zoom_max' => '500',
    'zoom_default' => '100',
    'zoom_step' => '25',
    'drag_enabled' => '0',  // false -> '0'
    'resize_enabled' => '0', // false -> '0'
    'rotate_enabled' => '0', // false -> '0'
    'multi_select' => '0',   // false -> '0'
    'selection_mode' => 'single',
    'keyboard_shortcuts' => '0', // false -> '0'
    'export_quality' => 'print', // CORRIGÉ: '90' -> 'print'
    'export_format' => 'pdf',
    'export_transparent' => '0',
    'fps_target' => '60',
    'memory_limit_js' => '50',
    'response_timeout' => '5000',
    'debug_enabled' => '0', // false -> '0'
    'performance_monitoring' => '1',
    'error_reporting' => '1'
];

// Ajuster les valeurs par défaut pour les utilisateurs gratuits (features premium)
$can_use_grid_navigation = \PDF_Builder\Managers\PDF_Builder_Feature_Manager::canUseFeature('grid_navigation');
if (!$can_use_grid_navigation) {
    $canvas_defaults['grid_enabled'] = '0';      // DÉJÀ '0', inchangé
    $canvas_defaults['guides_enabled'] = '0';    // DÉJÀ '0', inchangé
    $canvas_defaults['snap_to_grid'] = '0';      // DÉJÀ '0', inchangé
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
        if (!\PDF_Builder\Managers\PDF_Builder_Feature_Manager::canUseFeature('grid_navigation')) {
            $value = '0';
        }
    }

    return $value;
}
?>

<!-- MODAL AFFICHAGE -->
<div id="canvas-affichage-modal-overlay" class="pdfb-canvas-modal-overlay" style="display: none;">
    <div class="pdfb-canvas-modal-container">
        <div class="pdfb-canvas-modal-header">
            <div style="flex: 1; display: flex; align-items: center; gap: 15px;">
                <h3 style="margin: 0;"><span style="font-size: 24px;">📐</span> Paramètres d'Affichage</h3>
                <?php if (!\PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance()->is_premium()): ?>
                <div class="pdfb-premium-header-notice" style="padding: 6px 12px; background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border: 1px solid #f39c12; border-radius: 6px; font-size: 12px; color: #856404; flex: 1; max-width: 52%">
                    <strong>🔒 Fonction Premium</strong> - Débloquez la personnalisation avancée du canvas (couleurs, bordures, formats étendus)
                    <a href="#" onclick="showUpgradeModal('canvas_settings')" style="color: #856404; text-decoration: underline; font-weight: 500; margin-left: 8px;">Passer en Premium →</a>
                </div>
                <?php endif; ?>
            </div>
            <button type="button" class="pdfb-canvas-modal-close">&times;</button>
        </div>
        <div class="pdfb-canvas-modal-body">
            <div class="pdfb-pdfb-modal-settings-grid">
                <div class="pdfb-setting-group" style="grid-column: span 2;">
                    <label><span style="font-size: 16px;">📏</span> Dimensions du Canvas <span class="pdfb-info-tooltip" title="Définit la taille par défaut du canvas en pixels">ℹ️</span></label>
                    <div class="pdfb-dimensions-display-compact">
                        <div class="pdfb-dimensions-value">
                            <?php echo esc_html(get_canvas_modal_value('width', $canvas_defaults['width'])); ?> × <?php echo esc_html(get_canvas_modal_value('height', $canvas_defaults['height'])); ?> px
                        </div>
                        <div class="pdfb-dimensions-format">
                            Format: <?php echo esc_html(get_canvas_modal_value('format', $canvas_defaults['format'])); ?> • <?php echo esc_html(get_canvas_modal_value('orientation', $canvas_defaults['orientation'])); ?>
                        </div>
                    </div>
                </div>
                <div class="pdfb-setting-group">
                    <label><span style="font-size: 16px;">🔍</span> Résolutions DPI <span class="pdfb-info-tooltip" title="Résolutions disponibles pour l'export des PDF">ℹ️</span></label>
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
                        $can_use_high_dpi = \PDF_Builder\Managers\PDF_Builder_Feature_Manager::canUseFeature('high_dpi');

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
                            $premium_class = $option['premium'] ? ' pdfb-premium-option ' : '';

                            echo '<label style="display: flex; align-items: center; gap: 12px; margin: 0; padding: 8px; border-radius: 8px; transition: background 0.2s ease; ' . ($option['premium'] && !$can_use_high_dpi ? 'opacity: 0.6;' : '') . '" class="' . $premium_class . '" onmouseover="this.style.background=\'#f8f9fa\'" onmouseout="this.style.background=\'transparent\'">';
                            echo '<input type="checkbox" name="pdf_builder_canvas_dpi[]" value="' . $option['value'] . '" ' . $checked . ' ' . $disabled . '>';
                            echo '<div style="flex: 1;">';
                            echo '<div style="font-weight: 500; color: #2c3e50;">' . $option['label'] . '</div>';
                            echo '<div style="font-size: 12px; color: #6c757d;">' . $option['desc'] . '</div>';
                            echo '</div>';
                            if ($option['premium']) {
                                echo '<span class="pdfb-pdfb-premium-badge">⭐ PREMIUM</span>';
                            }
                            echo '</label>';
                        }
                        ?>

                        <div class="pdfb-info-box">
                            <strong>ℹ️ Information:</strong> Les résolutions sélectionnées seront disponibles dans les paramètres des templates.
                        </div>
                    </div>
                </div>
                <div class="pdfb-setting-group">
                    <label><span style="font-size: 16px;">📄</span> Formats de Document Disponibles <span class="pdfb-info-tooltip" title="Formats de papier supportés pour les templates">ℹ️</span></label>
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
                        $can_use_extended_formats = \PDF_Builder\Managers\PDF_Builder_Feature_Manager::canUseFeature('extended_formats');

                        $format_options = [
                            ['value' => 'A4',     'label' => 'A4 (210×297mm)',          'desc' => 'Format standard européen',       'icon' => '📄', 'premium' => false, 'coming_soon' => false],
                            ['value' => 'A3',     'label' => 'A3 (297×420mm)',          'desc' => 'Format double A4',               'icon' => '📃', 'premium' => true,  'coming_soon' => false],
                            ['value' => 'Letter', 'label' => 'Letter (8.5×11")',        'desc' => 'Format américain standard',      'icon' => '🇺🇸', 'premium' => true,  'coming_soon' => true],
                            ['value' => 'Legal',  'label' => 'Legal (8.5×14")',         'desc' => 'Format américain légal',         'icon' => '⚖️', 'premium' => true,  'coming_soon' => true],
                            ['value' => 'Label',  'label' => 'Étiquette Colis (100×150mm)', 'desc' => 'Format pour étiquettes de colis', 'icon' => '📦', 'premium' => true,  'coming_soon' => true]
                        ];

                        foreach ($format_options as $option) {
                            $is_coming_soon = !empty($option['coming_soon']);
                            $disabled = ($is_coming_soon || ($option['premium'] && !$can_use_extended_formats)) ? 'disabled' : '';
                            $checked = (!$is_coming_soon && in_array($option['value'], $current_formats)) ? 'checked' : '';
                            $premium_class = $option['premium'] ? ' pdfb-premium-option ' : '';
                            $opacity_style = ($is_coming_soon || ($option['premium'] && !$can_use_extended_formats)) ? 'opacity: 0.5;' : '';
                            $pointer_style = $is_coming_soon ? 'pointer-events: none; cursor: not-allowed;' : '';

                            echo '<label style="display: flex; align-items: center; gap: 12px; margin: 0; padding: 8px; border-radius: 8px; transition: background 0.2s ease; ' . $opacity_style . ' ' . $pointer_style . '" class="' . $premium_class . '" ' . (!$is_coming_soon ? 'onmouseover="this.style.background=\'#f8f9fa\'" onmouseout="this.style.background=\'transparent\'"' : '') . '>';
                            echo '<input type="checkbox" name="pdf_builder_canvas_formats[]" value="' . $option['value'] . '" ' . $checked . ' ' . $disabled . '>';
                            echo '<div style="flex: 1;">';
                            echo '<div style="font-weight: 500; color: #2c3e50;">' . $option['icon'] . ' ' . $option['label'] . '</div>';
                            echo '<div style="font-size: 12px; color: #6c757d;">' . $option['desc'] . '</div>';
                            echo '</div>';
                            if ($is_coming_soon) {
                                echo '<span style="font-size: 11px; padding: 3px 8px; background: #e9ecef; color: #6c757d; border-radius: 4px; font-weight: 600; white-space: nowrap;">🔒 Prochainement</span>';
                            } elseif ($option['premium']) {
                                echo '<span class="pdfb-pdfb-premium-badge">⭐ PREMIUM</span>';
                            }
                            echo '</label>';
                        }
                        ?>

                        <div class="pdfb-info-box">
                            <strong>ℹ️ Information:</strong> Les formats sélectionnés seront disponibles dans les paramètres des templates.
                        </div>
                    </div>
                </div>
                <div class="pdfb-setting-group">
                    <label><span style="font-size: 16px;">🔄</span> Orientations Disponibles <span class="pdfb-info-tooltip" title="Orientations portrait/paysage autorisées">ℹ️</span></label>
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
                            ['value' => 'portrait',  'label' => 'Portrait', 'desc' => '794×1123 px • Vertical',   'icon' => '📱', 'coming_soon' => false],
                            ['value' => 'landscape', 'label' => 'Paysage',  'desc' => '1123×794 px • Horizontal', 'icon' => '🖥️', 'coming_soon' => true]
                        ];

                        foreach ($orientation_options as $option) {
                            $is_coming_soon = !empty($option['coming_soon']);
                            $disabled = $is_coming_soon ? 'disabled' : '';
                            $checked = (!$is_coming_soon && in_array($option['value'], $current_orientations)) ? 'checked' : '';
                            $opacity_style = $is_coming_soon ? 'opacity: 0.5;' : 'opacity: 1;';
                            $pointer_style = $is_coming_soon ? 'pointer-events: none; cursor: not-allowed;' : '';

                            echo '<label style="display: flex; align-items: center; gap: 12px; margin: 0; padding: 8px; border-radius: 8px; transition: background 0.2s ease; ' . $opacity_style . ' ' . $pointer_style . '" ' . (!$is_coming_soon ? 'onmouseover="this.style.background=\'#f8f9fa\'" onmouseout="this.style.background=\'transparent\'"' : '') . '>';
                            echo '<input type="checkbox" name="pdf_builder_canvas_orientations[]" value="' . $option['value'] . '" ' . $checked . ' ' . $disabled . '>';
                            echo '<div style="flex: 1;">';
                            echo '<div style="font-weight: 500; color: #2c3e50;">' . $option['icon'] . ' ' . $option['label'] . '</div>';
                            echo '<div style="font-size: 12px; color: #6c757d;">' . $option['desc'] . '</div>';
                            echo '</div>';
                            if ($is_coming_soon) {
                                echo '<span style="font-size: 11px; padding: 3px 8px; background: #e9ecef; color: #6c757d; border-radius: 4px; font-weight: 600; white-space: nowrap;">🔒 Prochainement</span>';
                            }
                            echo '</label>';
                        }
                        ?>

                        <div class="pdfb-info-box">
                            <strong>ℹ️ Information:</strong> Les orientations sélectionnées seront disponibles dans les paramètres des templates.
                        </div>
                    </div>
                </div>
                <div class="pdfb-setting-group">
                    <label style="display: flex; align-items: center; justify-content: space-between;"><span style="font-size: 16px;">🔳</span> Bordure du canvas <span class="pdfb-pdfb-premium-badge">⭐ PREMIUM</span></label>
                    <?php $can_use_custom_colors = \PDF_Builder\Managers\PDF_Builder_Feature_Manager::canUseFeature('custom_colors'); ?>
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
                        <div class="pdfb-toggle-switch">
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
                        <div class="pdfb-toggle-switch">
                            <input type="checkbox" id="modal_canvas_shadow_enabled" name="pdf_builder_canvas_shadow_enabled"
                                   value="0" disabled>
                            <label for="modal_canvas_shadow_enabled"></label>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="pdfb-setting-group">
                    <label><span style="font-size: 16px;">🎨</span> Couleur de Fond du Conteneur <span class="pdfb-pdfb-premium-badge">⭐ PREMIUM</span> <span class="pdfb-info-tooltip" title="Couleur d'arrière-plan du conteneur canvas">ℹ️</span></label>
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
        <div class="pdfb-canvas-modal-footer">
            <button type="button" class="button pdfb-canvas-modal-cancel">❌ Annuler</button>
            <button type="button" class="button button-primary pdfb-canvas-modal-apply" data-category="affichage">✅ Appliquer</button>
        </div>
    </div>
</div>

<!-- MODAL NAVIGATION -->
<div id="canvas-navigation-modal-overlay" class="pdfb-canvas-modal-overlay" style="display: none;">
    <div class="pdfb-canvas-modal-container">
        <div class="pdfb-canvas-modal-header">
            <h3><span style="font-size: 24px;">🧭</span> Paramètres de Navigation</h3>
            <button type="button" class="pdfb-canvas-modal-close">&times;</button>
        </div>
        <div class="pdfb-canvas-modal-body">
            <div class="pdfb-pdfb-modal-settings-grid">
                <?php $can_use_grid_navigation = \PDF_Builder\Managers\PDF_Builder_Feature_Manager::canUseFeature('grid_navigation'); ?>
                <div class="pdfb-setting-group">
                    <label><span style="font-size: 16px;">📐</span> Grille activée<?php if (!$can_use_grid_navigation): ?> <span class="pdfb-pdfb-premium-badge">⭐ PREMIUM</span><?php endif; ?> <span class="pdfb-info-tooltip" title="Affiche une grille d'aide à l'alignement">ℹ️</span></label>
                    <div class="pdfb-toggle-switch<?php echo !$can_use_grid_navigation ? ' disabled' : ''; ?>"<?php echo !$can_use_grid_navigation ? ' style="opacity: 0.6; pointer-events: none;"' : ''; ?>>
                        <input type="checkbox" id="modal_canvas_grid_enabled" name="pdf_builder_canvas_grid_enabled"
                               value="1"<?php echo !$can_use_grid_navigation ? ' disabled' : ''; ?> <?php checked(get_canvas_modal_value('canvas_grid_enabled', $canvas_defaults['grid_enabled']), '1'); ?>>
                        <label for="modal_canvas_grid_enabled"></label>
                    </div>
                </div>
                <div class="pdfb-setting-group">
                    <label><span style="font-size: 16px;">📏</span> Taille grille (px)<?php if (!$can_use_grid_navigation): ?> <span class="pdfb-pdfb-premium-badge">⭐ PREMIUM</span><?php endif; ?> <span class="pdfb-info-tooltip" title="Espacement entre les lignes de la grille">ℹ️</span></label>
                    <?php if ($can_use_grid_navigation): ?>
                    <input type="number" id="modal_canvas_grid_size" name="pdf_builder_canvas_grid_size"
                           value="<?php echo esc_attr(get_canvas_modal_value('canvas_grid_size', $canvas_defaults['grid_size'])); ?>">
                    <?php else: ?>
                    <input type="number" id="modal_canvas_grid_size" name="pdf_builder_canvas_grid_size"
                           value="<?php echo esc_attr(get_canvas_modal_value('canvas_grid_size', $canvas_defaults['grid_size'])); ?>" disabled
                           style="opacity: 0.6; cursor: not-allowed;">
                    <?php endif; ?>
                </div>
                <div class="pdfb-setting-group">
                    <label><span style="font-size: 16px;">📍</span> Guides activés<?php if (!$can_use_grid_navigation): ?> <span class="pdfb-pdfb-premium-badge">⭐ PREMIUM</span><?php endif; ?> <span class="pdfb-info-tooltip" title="Affiche des guides d'alignement magnétiques">ℹ️</span></label>
                    <div class="pdfb-toggle-switch<?php echo !$can_use_grid_navigation ? ' disabled' : ''; ?>"<?php echo !$can_use_grid_navigation ? ' style="opacity: 0.6; pointer-events: none;"' : ''; ?>>
                        <input type="checkbox" id="modal_canvas_guides_enabled" name="pdf_builder_canvas_guides_enabled"
                               value="1"<?php echo !$can_use_grid_navigation ? ' disabled' : ''; ?> <?php checked(get_canvas_modal_value('canvas_guides_enabled', $canvas_defaults['guides_enabled']), '1'); ?>>
                        <label for="modal_canvas_guides_enabled"></label>
                    </div>
                </div>
                <div class="pdfb-setting-group">
                    <label><span style="font-size: 16px;">🧲</span> Accrochage à la grille<?php if (!$can_use_grid_navigation): ?> <span class="pdfb-pdfb-premium-badge">⭐ PREMIUM</span><?php endif; ?> <span class="pdfb-info-tooltip" title="Les éléments s'alignent automatiquement sur la grille">ℹ️</span></label>
                    <div class="pdfb-toggle-switch<?php echo !$can_use_grid_navigation ? ' disabled' : ''; ?>"<?php echo !$can_use_grid_navigation ? ' style="opacity: 0.6; pointer-events: none;"' : ''; ?>>
                        <input type="checkbox" id="modal_canvas_snap_to_grid" name="pdf_builder_canvas_snap_to_grid"
                               value="1"<?php echo !$can_use_grid_navigation ? ' disabled' : ''; ?> <?php checked(get_canvas_modal_value('canvas_snap_to_grid', $canvas_defaults['snap_to_grid']), '1'); ?>>
                        <label for="modal_canvas_snap_to_grid"></label>
                    </div>
                </div>
                <div class="pdfb-setting-group">
                    <label><span style="font-size: 16px;">🔍</span> Zoom minimum (%) <span class="pdfb-info-tooltip" title="Niveau de zoom minimum autorisé">ℹ️</span></label>
                    <input type="number" id="modal_canvas_zoom_min" name="pdf_builder_canvas_zoom_min"
                           value="<?php echo esc_attr(get_canvas_modal_value('canvas_zoom_min', $canvas_defaults['zoom_min'])); ?>">
                </div>
                <div class="pdfb-setting-group">
                    <label for="modal_canvas_zoom_max">Zoom maximum (%) <span class="pdfb-info-tooltip" title="Niveau de zoom maximum autorisé">ℹ️</span></label>
                    <input type="number" id="modal_canvas_zoom_max" name="pdf_builder_canvas_zoom_max"
                           value="<?php echo esc_attr(get_canvas_modal_value('canvas_zoom_max', $canvas_defaults['zoom_max'])); ?>">
                </div>
                <div class="pdfb-setting-group">
                    <label for="modal_canvas_zoom_default">Zoom par défaut (%) <span class="pdfb-info-tooltip" title="Niveau de zoom au chargement du canvas">ℹ️</span></label>
                    <input type="number" id="modal_canvas_zoom_default" name="pdf_builder_canvas_zoom_default"
                           value="<?php echo esc_attr(get_canvas_modal_value('canvas_zoom_default', $canvas_defaults['zoom_default'])); ?>">
                </div>
                <div class="pdfb-setting-group">
                    <label for="modal_canvas_zoom_step">Pas de zoom (%) <span class="pdfb-info-tooltip" title="Incrément de zoom lors des contrôles">ℹ️</span></label>
                    <input type="number" id="modal_canvas_zoom_step" name="pdf_builder_canvas_zoom_step"
                           value="<?php echo esc_attr(get_canvas_modal_value('canvas_zoom_step', $canvas_defaults['zoom_step'])); ?>">
                </div>
            </div>
        </div>
        <div class="pdfb-canvas-modal-footer">
            <button type="button" class="button pdfb-canvas-modal-cancel">❌ Annuler</button>
            <button type="button" class="button button-primary pdfb-canvas-modal-apply" data-category="navigation">✅ Appliquer</button>
        </div>
    </div>
</div>

<!-- MODAL COMPORTEMENT -->
<div id="canvas-comportement-modal-overlay" class="pdfb-canvas-modal-overlay" style="display: none;">
    <div class="pdfb-canvas-modal-container">
        <div class="pdfb-canvas-modal-header">
            <h3><span style="font-size: 24px;">🎯</span> Paramètres de Comportement</h3>
            <button type="button" class="pdfb-canvas-modal-close">&times;</button>
        </div>
        <div class="pdfb-canvas-modal-body">
            <div class="pdfb-pdfb-modal-settings-grid">
                <div class="pdfb-setting-group">
                    <label><span style="font-size: 16px;">✋</span> Glisser activé <span class="pdfb-info-tooltip" title="Permet de déplacer les éléments sur le canvas">ℹ️</span></label>
                    <div class="pdfb-toggle-switch">
                        <input type="checkbox" id="modal_canvas_drag_enabled" name="pdf_builder_canvas_drag_enabled"
                               value="1" <?php checked(get_canvas_modal_value('canvas_drag_enabled', $canvas_defaults['drag_enabled']), '1'); ?>>
                        <label for="modal_canvas_drag_enabled"></label>
                    </div>
                </div>
                <div class="pdfb-setting-group">
                    <label><span style="font-size: 16px;">📐</span> Redimensionnement activé <span class="pdfb-info-tooltip" title="Permet de redimensionner les éléments">ℹ️</span></label>
                    <div class="pdfb-toggle-switch">
                        <input type="checkbox" id="modal_canvas_resize_enabled" name="pdf_builder_canvas_resize_enabled"
                               value="1" <?php checked(get_canvas_modal_value('canvas_resize_enabled', $canvas_defaults['resize_enabled']), '1'); ?>>
                        <label for="modal_canvas_resize_enabled"></label>
                    </div>
                </div>
                <div class="pdfb-setting-group">
                    <label><span style="font-size: 16px;">🔄</span> Rotation activée <span class="pdfb-info-tooltip" title="Permet de faire pivoter les éléments">ℹ️</span></label>
                    <div class="pdfb-toggle-switch">
                        <input type="checkbox" id="modal_canvas_rotate_enabled" name="pdf_builder_canvas_rotate_enabled"
                               value="1" <?php checked(get_canvas_modal_value('canvas_rotate_enabled', $canvas_defaults['rotate_enabled']), '1'); ?>>
                        <label for="modal_canvas_rotate_enabled"></label>
                    </div>
                </div>
                <div class="pdfb-setting-group">
                    <label><span style="font-size: 16px;">☑️</span> Sélection multiple <span class="pdfb-info-tooltip" title="Permet de sélectionner plusieurs éléments simultanément">ℹ️</span></label>
                    <div class="pdfb-toggle-switch">
                        <input type="checkbox" id="modal_canvas_multi_select" name="pdf_builder_canvas_multi_select"
                               value="1" <?php checked(get_canvas_modal_value('canvas_multi_select', $canvas_defaults['multi_select']), '1'); ?>>
                        <label for="modal_canvas_multi_select"></label>
                    </div>
                </div>
                <div class="pdfb-setting-group">
                    <label><span style="font-size: 16px;">🎯</span> Mode de sélection <span class="pdfb-info-tooltip" title="Comportement de la sélection (simple ou rectangle)">ℹ️</span></label>
                    <?php $can_use_advanced_selection = \PDF_Builder\Managers\PDF_Builder_Feature_Manager::canUseFeature('advanced_selection'); ?>
                    <select id="modal_canvas_selection_mode" name="pdf_builder_canvas_selection_mode">
                        <option value="single" <?php selected(get_canvas_modal_value('canvas_selection_mode', $canvas_defaults['selection_mode']), 'single'); ?>>Simple</option>
                        <option value="multiple" <?php selected(get_canvas_modal_value('canvas_selection_mode', $canvas_defaults['selection_mode']), 'multiple'); ?> <?php echo !$can_use_advanced_selection ? 'disabled' : ''; ?>><?php echo !$can_use_advanced_selection ? 'Multiple ⭐ PREMIUM' : 'Multiple'; ?></option>
                        <option value="group" <?php selected(get_canvas_modal_value('canvas_selection_mode', $canvas_defaults['selection_mode']), 'group'); ?> <?php echo !$can_use_advanced_selection ? 'disabled' : ''; ?>><?php echo !$can_use_advanced_selection ? 'Groupe ⭐ PREMIUM' : 'Groupe'; ?></option>
                    </select>
                    <?php if (!$can_use_advanced_selection): ?>
                        <div class="pdfb-info-box" style="margin-top: 8px; padding: 8px; background: #fff3cd; border: 1px solid #f39c12; border-radius: 4px; font-size: 12px;">
                            <strong>🔒 Fonction Premium</strong> - Débloquez les modes de sélection avancés (Multiple et Groupe)
                            <a href="#" onclick="showUpgradeModal('canvas_settings')" style="color: #856404; text-decoration: underline; font-weight: 500;">Passer en Premium →</a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="pdfb-setting-group">
                    <?php $can_use_keyboard_shortcuts = \PDF_Builder\Managers\PDF_Builder_Feature_Manager::canUseFeature('keyboard_shortcuts'); ?>
                    <label for="modal_canvas_keyboard_shortcuts">Raccourcis clavier<?php if (!$can_use_keyboard_shortcuts): ?> <span class="pdfb-pdfb-premium-badge">⭐ PREMIUM</span><?php endif; ?></label>
                    <?php if ($can_use_keyboard_shortcuts): ?>
                    <div class="pdfb-toggle-switch">
                        <input type="checkbox" id="modal_canvas_keyboard_shortcuts" name="pdf_builder_canvas_keyboard_shortcuts"
                               value="1" <?php checked(get_canvas_modal_value('canvas_keyboard_shortcuts', $canvas_defaults['keyboard_shortcuts']), '1'); ?>>
                        <label for="modal_canvas_keyboard_shortcuts"></label>
                    </div>
                    <?php else: ?>
                    <div class="pdfb-toggle-switch" style="opacity: 0.6; pointer-events: none;">
                        <input type="checkbox" id="modal_canvas_keyboard_shortcuts" name="pdf_builder_canvas_keyboard_shortcuts"
                               value="1" disabled>
                        <label for="modal_canvas_keyboard_shortcuts"></label>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="pdfb-canvas-modal-footer">
            <button type="button" class="button pdfb-canvas-modal-cancel">❌ Annuler</button>
            <button type="button" class="button button-primary pdfb-canvas-modal-apply" data-category="comportement">✅ Appliquer</button>
        </div>
    </div>
</div>

<!-- MODAL SYSTEME -->
<div id="canvas-systeme-modal-overlay" class="pdfb-canvas-modal-overlay" style="display: none;">
    <div class="pdfb-canvas-modal-container">
        <div class="pdfb-canvas-modal-header">
            <h3><span style="font-size: 24px;">⚙️</span> Paramètres Système</h3>
            <button type="button" class="pdfb-canvas-modal-close">&times;</button>
        </div>
        <div class="pdfb-canvas-modal-body">
            <div class="pdfb-pdfb-modal-settings-grid">
                <div class="pdfb-setting-group">
                    <label><span style="font-size: 16px;">🎮</span> FPS cible <span class="pdfb-info-tooltip" title="Images par seconde visées pour les animations">ℹ️</span></label>
                    <input type="number" id="modal_canvas_fps_target" name="pdf_builder_canvas_fps_target"
                           value="<?php echo esc_attr(get_canvas_modal_value('canvas_fps_target', $canvas_defaults['fps_target'])); ?>">
                </div>
                <div class="pdfb-setting-group">
                    <label><span style="font-size: 16px;">🧠</span> Limite mémoire JS (MB) <span class="pdfb-info-tooltip" title="Limite de mémoire pour le JavaScript du canvas">ℹ️</span></label>
                    <input type="number" id="modal_canvas_memory_limit_js" name="pdf_builder_canvas_memory_limit_js"
                           value="<?php echo esc_attr(get_canvas_modal_value('canvas_memory_limit_js', $canvas_defaults['memory_limit_js'])); ?>">
                </div>
                <div class="pdfb-setting-group">
                    <label><span style="font-size: 16px;">⏱️</span> Timeout réponse (ms) <span class="pdfb-info-tooltip" title="Délai maximum pour les réponses des opérations">ℹ️</span></label>
                    <input type="number" id="modal_canvas_response_timeout" name="pdf_builder_canvas_response_timeout"
                           value="<?php echo esc_attr(get_canvas_modal_value('canvas_response_timeout', $canvas_defaults['response_timeout'])); ?>">
                </div>
                <div class="pdfb-setting-group">
                    <label><span style="font-size: 16px;">🐛</span> Debug activé <span class="pdfb-info-tooltip" title="Active les logs de débogage dans la console">ℹ️</span></label>
                    <div class="pdfb-toggle-switch">
                        <input type="checkbox" id="modal_canvas_debug_enabled" name="pdf_builder_canvas_debug_enabled"
                               value="1" <?php checked(get_canvas_modal_value('canvas_debug_enabled', $canvas_defaults['debug_enabled']), '1'); ?>>
                        <label for="modal_canvas_debug_enabled"></label>
                    </div>
                </div>
                <div class="pdfb-setting-group">
                    <label><span style="font-size: 16px;">📊</span> Monitoring performance <span class="pdfb-info-tooltip" title="Surveille les performances du canvas">ℹ️</span></label>
                    <div class="pdfb-toggle-switch">
                        <input type="checkbox" id="modal_canvas_performance_monitoring" name="pdf_builder_canvas_performance_monitoring"
                               value="1" <?php checked(get_canvas_modal_value('canvas_performance_monitoring', $canvas_defaults['performance_monitoring']), '1'); ?>>
                        <label for="modal_canvas_performance_monitoring"></label>
                    </div>
                </div>
                <div class="pdfb-setting-group">
                    <label><span style="font-size: 16px;">🚨</span> Rapport d'erreurs <span class="pdfb-info-tooltip" title="Rapporte les erreurs à l'équipe de développement">ℹ️</span></label>
                    <div class="pdfb-toggle-switch">
                        <input type="checkbox" id="modal_canvas_error_reporting" name="pdf_builder_canvas_error_reporting"
                               value="1" <?php checked(get_canvas_modal_value('canvas_error_reporting', $canvas_defaults['error_reporting']), '1'); ?>>
                        <label for="modal_canvas_error_reporting"></label>
                    </div>
                </div>
            </div>
        </div>
        <div class="pdfb-canvas-modal-footer">
            <button type="button" class="button pdfb-canvas-modal-cancel">❌ Annuler</button>
            <button type="button" class="button button-primary pdfb-canvas-modal-apply" data-category="systeme">✅ Appliquer</button>
        </div>
    </div>
</div>

<!-- JavaScript déplacé vers settings-main.php pour éviter les conflits -->
<script>
/**
 * Gestion des dépendances entre paramètres canvas
 */
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la dépendance Sélection Multiple -> Mode de sélection
    const multiSelectCheckbox = document.getElementById('modal_canvas_multi_select');
    const selectionModeSelect = document.getElementById('modal_canvas_selection_mode');
    
    if (multiSelectCheckbox && selectionModeSelect) {
        // Fonction pour mettre à jour l'état du select Mode de sélection
        function updateSelectionModeState() {
            const isMultiSelectEnabled = multiSelectCheckbox.checked;
            
            if (isMultiSelectEnabled) {
                // Activer le select
                selectionModeSelect.disabled = false;
                selectionModeSelect.style.opacity = '1';
                selectionModeSelect.style.pointerEvents = 'auto';
                selectionModeSelect.parentElement.style.opacity = '1';
            } else {
                // Désactiver et griser le select
                selectionModeSelect.disabled = true;
                selectionModeSelect.style.opacity = '0.5';
                selectionModeSelect.style.pointerEvents = 'none';
                selectionModeSelect.parentElement.style.opacity = '0.7';
                
                // Forcer la valeur à 'single' quand désactivé
                const singleOption = selectionModeSelect.querySelector('option[value="single"]');
                if (singleOption) {
                    singleOption.selected = true;
                }
            }
        }
        
        // Écouter les changements sur la checkbox
        multiSelectCheckbox.addEventListener('change', updateSelectionModeState);
        
        // Initialiser l'état au chargement
        updateSelectionModeState();
    }
});

/**
 * Fonction pour afficher le modal de mise à niveau
 */
function showUpgradeModal(feature) {
    // Créer le modal de mise à niveau s'il n'existe pas
    if (!document.getElementById('upgrade-modal-overlay')) {
        var modalHTML = `
            <div id="upgrade-modal-overlay" class="pdfb-canvas-modal-overlay" style="display: flex; z-index: 10002;">
                <div class="pdfb-canvas-modal-container" style="max-width: 500px;">
                    <div class="pdfb-canvas-modal-header">
                        <h3>🔒 Fonctionnalité Premium</h3>
                        <button type="button" class="pdfb-canvas-modal-close" onclick="closeUpgradeModal()">&times;</button>
                    </div>
                    <div class="pdfb-canvas-modal-body" style="text-align: center; padding: 30px;">
                        <div style="font-size: 48px; margin-bottom: 20px;">⭐</div>
                        <h4 style="margin-bottom: 15px; color: #23282d;">Débloquez cette fonctionnalité Premium</h4>
                        <p style="margin-bottom: 20px; color: #666; line-height: 1.5;">
                            Cette fonctionnalité est réservée aux utilisateurs Premium de PDF Builder Pro.
                        </p>
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                            <h5 style="margin: 0 0 10px 0; color: #23282d;">Avantages Premium :</h5>
                            <ul style="text-align: left; color: #666; margin: 0; padding-left: 20px;">
                                <li>� Templates illimités — aucune restriction</li>
                                <li>🖼️ Génération PDF, PNG &amp; JPG prioritaire</li>
                                <li>🎯 Haute résolution 300/600 DPI</li>
                                <li>💬 Support prioritaire avec réponse garantie</li>
                            </ul>
                        </div>
                        <div style="background: #e8f5e8; border: 2px solid #28a745; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                            <strong style="color: #155724; font-size: 18px;">79.99€ à vie</strong>
                            <br><small style="color: #155724;">✨ Accès à vie ou abonnement flexible — sans engagement !</small>
                        </div>
                        <a href="https://hub.threeaxe.fr/index.php/downloads/pdf-builder-pro/" target="_blank" class="button button-primary" style="background: #28a745; border-color: #28a745; padding: 12px 24px; font-size: 16px;">
                            🚀 Passer en Premium - 79.99€
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

