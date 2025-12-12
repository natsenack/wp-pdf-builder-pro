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

// Fonction helper pour récupérer une valeur canvas
function get_canvas_modal_value($key, $default = '') {
    $option_key = 'pdf_builder_canvas_' . $key;
    $value = get_option($option_key, $default);
    return $value;
}
?>

<!-- MODAL AFFICHAGE -->
<div id="canvas-affichage-modal-overlay" class="canvas-modal-overlay" style="display: none;">
    <div class="canvas-modal-container" style="display: block; z-index: 10001;">
        <div class="canvas-modal-header">
            <h3>Paramètres d'Affichage</h3>
            <button type="button" class="canvas-modal-close">&times;</button>
        </div>
        <div class="canvas-modal-body">
            <!-- Section Dimensions -->
            <div class="modal-section">
                <h4 class="modal-section-title">Dimensions du Canvas</h4>
                <div class="modal-settings-grid compact">
                    <div class="setting-group">
                        <label for="modal_canvas_width">Largeur (px)</label>
                        <input type="number" id="modal_canvas_width" name="pdf_builder_canvas_width"
                               value="<?php echo esc_attr(get_canvas_modal_value('width', $canvas_defaults['width'])); ?>">
                        <span class="value-indicator value-default">Défaut: <?php echo $canvas_defaults['width']; ?>px</span>
                    </div>
                    <div class="setting-group">
                        <label for="modal_canvas_height">Hauteur (px)</label>
                        <input type="number" id="modal_canvas_height" name="pdf_builder_canvas_height"
                               value="<?php echo esc_attr(get_canvas_modal_value('height', $canvas_defaults['height'])); ?>">
                        <span class="value-indicator value-default">Défaut: <?php echo $canvas_defaults['height']; ?>px</span>
                    </div>
                    <div class="setting-group">
                        <label for="modal_canvas_dpi">DPI</label>
                        <select id="modal_canvas_dpi" name="pdf_builder_canvas_dpi">
                            <option value="72" <?php selected(get_canvas_modal_value('dpi', $canvas_defaults['dpi']), '72'); ?>>72 DPI (Écran)</option>
                            <option value="96" <?php selected(get_canvas_modal_value('dpi', $canvas_defaults['dpi']), '96'); ?>>96 DPI (Web)</option>
                            <option value="150" <?php selected(get_canvas_modal_value('dpi', $canvas_defaults['dpi']), '150'); ?>>150 DPI (Impression)</option>
                            <option value="200" <?php selected(get_canvas_modal_value('dpi', $canvas_defaults['dpi']), '200'); ?>>200 DPI (Haute qualité)</option>
                            <option value="300" <?php selected(get_canvas_modal_value('dpi', $canvas_defaults['dpi']), '300'); ?>>300 DPI (Professionnel)</option>
                            <option value="400" <?php selected(get_canvas_modal_value('dpi', $canvas_defaults['dpi']), '400'); ?>>400 DPI (Très haute qualité)</option>
                            <option value="600" <?php selected(get_canvas_modal_value('dpi', $canvas_defaults['dpi']), '600'); ?>>600 DPI (Maximum)</option>
                        </select>
                        <span class="value-indicator value-default">Défaut: <?php echo $canvas_defaults['dpi']; ?> DPI</span>
                    </div>
                    <div class="setting-group">
                        <label for="modal_canvas_format">Format prédéfini</label>
                        <select id="modal_canvas_format" name="pdf_builder_canvas_format">
                            <option value="A4" <?php selected(get_canvas_modal_value('format', $canvas_defaults['format']), 'A4'); ?>>A4 (210×297mm)</option>
                            <option value="A3" <?php selected(get_canvas_modal_value('format', $canvas_defaults['format']), 'A3'); ?>>A3 (297×420mm)</option>
                            <option value="Letter" <?php selected(get_canvas_modal_value('format', $canvas_defaults['format']), 'Letter'); ?>>Letter (8.5×11")</option>
                            <option value="Legal" <?php selected(get_canvas_modal_value('format', $canvas_defaults['format']), 'Legal'); ?>>Legal (8.5×14")</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section Apparence -->
            <div class="modal-section">
                <h4 class="modal-section-title">Apparence</h4>
                <div class="modal-settings-grid compact">
                    <div class="setting-group">
                        <label for="modal_canvas_bg_color">Couleur de fond</label>
                        <input type="color" id="modal_canvas_bg_color" name="pdf_builder_canvas_bg_color"
                               value="<?php echo esc_attr(get_canvas_modal_value('bg_color', $canvas_defaults['bg_color'])); ?>">
                        <span class="value-indicator value-default">Défaut: <?php echo $canvas_defaults['bg_color']; ?></span>
                    </div>
                    <div class="setting-group">
                        <label for="modal_canvas_border_color">Couleur bordure</label>
                        <input type="color" id="modal_canvas_border_color" name="pdf_builder_canvas_border_color"
                               value="<?php echo esc_attr(get_canvas_modal_value('border_color', $canvas_defaults['border_color'])); ?>">
                        <span class="value-indicator value-default">Défaut: <?php echo $canvas_defaults['border_color']; ?></span>
                    </div>
                    <div class="setting-group">
                        <label for="modal_canvas_border_width">Épaisseur bordure (px)</label>
                        <input type="number" id="modal_canvas_border_width" name="pdf_builder_canvas_border_width"
                               value="<?php echo esc_attr(get_canvas_modal_value('border_width', $canvas_defaults['border_width'])); ?>">
                        <span class="value-indicator value-default">Défaut: <?php echo $canvas_defaults['border_width']; ?>px</span>
                    </div>
                    <div class="setting-group">
                        <label for="modal_canvas_shadow_enabled">Ombre activée</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="modal_canvas_shadow_enabled" name="pdf_builder_canvas_shadow_enabled"
                                   value="1" <?php checked(get_canvas_modal_value('shadow_enabled', $canvas_defaults['shadow_enabled']), '1'); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="value-indicator value-default">Défaut: Désactivé</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="canvas-modal-footer">
            <button type="button" class="button canvas-modal-cancel">Annuler</button>
            <button type="button" class="button button-primary canvas-modal-apply" data-category="affichage">Appliquer</button>
        </div>
    </div>
</div>

<!-- MODAL NAVIGATION -->
<div id="canvas-navigation-modal-overlay" class="canvas-modal-overlay" style="display: none;">
    <div class="canvas-modal-container" style="display: block; z-index: 10001;">
        <div class="canvas-modal-header">
            <h3>Paramètres de Navigation</h3>
            <button type="button" class="canvas-modal-close">&times;</button>
        </div>
        <div class="canvas-modal-body">
            <!-- Section Grille -->
            <div class="modal-section">
                <h4 class="modal-section-title">Système de Grille</h4>
                <div class="modal-settings-grid compact">
                    <div class="setting-group">
                        <label for="modal_canvas_grid_enabled">Grille activée</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="modal_canvas_grid_enabled" name="pdf_builder_canvas_grid_enabled"
                                   value="1" <?php checked(get_canvas_modal_value('grid_enabled', $canvas_defaults['grid_enabled']), '1'); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="value-indicator value-default">Défaut: Activé</span>
                    </div>
                    <div class="setting-group">
                        <label for="modal_canvas_grid_size">Taille grille (px)</label>
                        <input type="number" id="modal_canvas_grid_size" name="pdf_builder_canvas_grid_size"
                               value="<?php echo esc_attr(get_canvas_modal_value('grid_size', $canvas_defaults['grid_size'])); ?>">
                        <span class="value-indicator value-default">Défaut: <?php echo $canvas_defaults['grid_size']; ?>px</span>
                    </div>
                    <div class="setting-group">
                        <label for="modal_canvas_guides_enabled">Guides activés</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="modal_canvas_guides_enabled" name="pdf_builder_canvas_guides_enabled"
                                   value="1" <?php checked(get_canvas_modal_value('guides_enabled', $canvas_defaults['guides_enabled']), '1'); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="value-indicator value-default">Défaut: Activé</span>
                    </div>
                    <div class="setting-group">
                        <label for="modal_canvas_snap_to_grid">Accrochage à la grille</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="modal_canvas_snap_to_grid" name="pdf_builder_canvas_snap_to_grid"
                                   value="1" <?php checked(get_canvas_modal_value('snap_to_grid', $canvas_defaults['snap_to_grid']), '1'); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="value-indicator value-default">Défaut: Activé</span>
                    </div>
                </div>
            </div>

            <!-- Section Zoom -->
            <div class="modal-section">
                <h4 class="modal-section-title">Contrôles de Zoom</h4>
                <div class="modal-settings-grid compact">
                    <div class="setting-group">
                        <label for="modal_canvas_zoom_min">Zoom minimum (%)</label>
                        <input type="number" id="modal_canvas_zoom_min" name="pdf_builder_canvas_zoom_min"
                               value="<?php echo esc_attr(get_canvas_modal_value('zoom_min', $canvas_defaults['zoom_min'])); ?>">
                        <span class="value-indicator value-default">Défaut: <?php echo $canvas_defaults['zoom_min']; ?>%</span>
                    </div>
                    <div class="setting-group">
                        <label for="modal_canvas_zoom_max">Zoom maximum (%)</label>
                        <input type="number" id="modal_canvas_zoom_max" name="pdf_builder_canvas_zoom_max"
                               value="<?php echo esc_attr(get_canvas_modal_value('zoom_max', $canvas_defaults['zoom_max'])); ?>">
                        <span class="value-indicator value-default">Défaut: <?php echo $canvas_defaults['zoom_max']; ?>%</span>
                    </div>
                    <div class="setting-group">
                        <label for="modal_canvas_zoom_default">Zoom par défaut (%)</label>
                        <input type="number" id="modal_canvas_zoom_default" name="pdf_builder_canvas_zoom_default"
                               value="<?php echo esc_attr(get_canvas_modal_value('zoom_default', $canvas_defaults['zoom_default'])); ?>">
                        <span class="value-indicator value-default">Défaut: <?php echo $canvas_defaults['zoom_default']; ?>%</span>
                    </div>
                    <div class="setting-group">
                        <label for="modal_canvas_zoom_step">Pas de zoom (%)</label>
                        <input type="number" id="modal_canvas_zoom_step" name="pdf_builder_canvas_zoom_step"
                               value="<?php echo esc_attr(get_canvas_modal_value('zoom_step', $canvas_defaults['zoom_step'])); ?>">
                        <span class="value-indicator value-default">Défaut: <?php echo $canvas_defaults['zoom_step']; ?>%</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="canvas-modal-footer">
            <button type="button" class="button canvas-modal-cancel">Annuler</button>
            <button type="button" class="button button-primary canvas-modal-apply" data-category="navigation">Appliquer</button>
        </div>
    </div>
</div>

<!-- MODAL COMPORTEMENT -->
<div id="canvas-comportement-modal-overlay" class="canvas-modal-overlay" style="display: none;">
    <div class="canvas-modal-container" style="display: block; z-index: 10001;">
        <div class="canvas-modal-header">
            <h3>Paramètres de Comportement</h3>
            <button type="button" class="canvas-modal-close">&times;</button>
        </div>
        <div class="canvas-modal-body">
            <!-- Section Interactions -->
            <div class="modal-section">
                <h4 class="modal-section-title">Interactions avec les Éléments</h4>
                <div class="modal-settings-grid compact">
                    <div class="setting-group">
                        <label for="modal_canvas_drag_enabled">Glisser activé</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="modal_canvas_drag_enabled" name="pdf_builder_canvas_drag_enabled"
                                   value="1" <?php checked(get_canvas_modal_value('drag_enabled', $canvas_defaults['drag_enabled']), '1'); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="value-indicator value-default">Défaut: Activé</span>
                    </div>
                    <div class="setting-group">
                        <label for="modal_canvas_resize_enabled">Redimensionnement activé</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="modal_canvas_resize_enabled" name="pdf_builder_canvas_resize_enabled"
                                   value="1" <?php checked(get_canvas_modal_value('resize_enabled', $canvas_defaults['resize_enabled']), '1'); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="value-indicator value-default">Défaut: Activé</span>
                    </div>
                    <div class="setting-group">
                        <label for="modal_canvas_rotate_enabled">Rotation activée</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="modal_canvas_rotate_enabled" name="pdf_builder_canvas_rotate_enabled"
                                   value="1" <?php checked(get_canvas_modal_value('rotate_enabled', $canvas_defaults['rotate_enabled']), '1'); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="value-indicator value-default">Défaut: Activé</span>
                    </div>
                    <div class="setting-group">
                        <label for="modal_canvas_keyboard_shortcuts">Raccourcis clavier</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="modal_canvas_keyboard_shortcuts" name="pdf_builder_canvas_keyboard_shortcuts"
                                   value="1" <?php checked(get_canvas_modal_value('keyboard_shortcuts', $canvas_defaults['keyboard_shortcuts']), '1'); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="value-indicator value-default">Défaut: Activé</span>
                    </div>
                </div>
            </div>

            <!-- Section Sélection -->
            <div class="modal-section">
                <h4 class="modal-section-title">Système de Sélection</h4>
                <div class="modal-settings-grid compact">
                    <div class="setting-group">
                        <label for="modal_canvas_multi_select">Sélection multiple</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="modal_canvas_multi_select" name="pdf_builder_canvas_multi_select"
                                   value="1" <?php checked(get_canvas_modal_value('multi_select', $canvas_defaults['multi_select']), '1'); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="value-indicator value-default">Défaut: Activé</span>
                    </div>
                    <div class="setting-group">
                        <label for="modal_canvas_selection_mode">Mode de sélection</label>
                        <select id="modal_canvas_selection_mode" name="pdf_builder_canvas_selection_mode">
                            <option value="single" <?php selected(get_canvas_modal_value('selection_mode', $canvas_defaults['selection_mode']), 'single'); ?>>Simple</option>
                            <option value="multiple" <?php selected(get_canvas_modal_value('selection_mode', $canvas_defaults['selection_mode']), 'multiple'); ?>>Multiple</option>
                            <option value="group" <?php selected(get_canvas_modal_value('selection_mode', $canvas_defaults['selection_mode']), 'group'); ?>>Groupe</option>
                        </select>
                        <span class="value-indicator value-default">Défaut: Simple</span>
                    </div>
                </div>
            </div>

            <!-- Section Export -->
            <div class="modal-section">
                <h4 class="modal-section-title">Paramètres d'Export</h4>
                <div class="modal-settings-grid compact">
                    <div class="setting-group">
                        <label for="modal_canvas_export_quality">Qualité export (%)</label>
                        <input type="number" id="modal_canvas_export_quality" name="pdf_builder_canvas_export_quality"
                               value="<?php echo esc_attr(get_canvas_modal_value('export_quality', $canvas_defaults['export_quality'])); ?>">
                        <span class="value-indicator value-default">Défaut: <?php echo $canvas_defaults['export_quality']; ?>%</span>
                    </div>
                    <div class="setting-group">
                        <label for="modal_canvas_export_format">Format export</label>
                        <select id="modal_canvas_export_format" name="pdf_builder_canvas_export_format">
                            <option value="pdf" <?php selected(get_canvas_modal_value('export_format', $canvas_defaults['export_format']), 'pdf'); ?>>PDF</option>
                            <option value="png" <?php selected(get_canvas_modal_value('export_format', $canvas_defaults['export_format']), 'png'); ?>>PNG</option>
                            <option value="jpg" <?php selected(get_canvas_modal_value('export_format', $canvas_defaults['export_format']), 'jpg'); ?>>JPG</option>
                            <option value="svg" <?php selected(get_canvas_modal_value('export_format', $canvas_defaults['export_format']), 'svg'); ?>>SVG</option>
                        </select>
                        <span class="value-indicator value-default">Défaut: PDF</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="canvas-modal-footer">
            <button type="button" class="button canvas-modal-cancel">Annuler</button>
            <button type="button" class="button button-primary canvas-modal-apply" data-category="comportement">Appliquer</button>
        </div>
    </div>
</div>

<!-- MODAL SYSTEME -->
<div id="canvas-systeme-modal-overlay" class="canvas-modal-overlay" style="display: none;">
    <div class="canvas-modal-container" style="display: block; z-index: 10001;">
        <div class="canvas-modal-header">
            <h3>Paramètres Système</h3>
            <button type="button" class="canvas-modal-close">&times;</button>
        </div>
        <div class="canvas-modal-body">
            <!-- Section Performance -->
            <div class="modal-section">
                <h4 class="modal-section-title">Performance & Ressources</h4>
                <div class="modal-settings-grid compact">
                    <div class="setting-group">
                        <label for="modal_canvas_fps_target">FPS cible</label>
                        <input type="number" id="modal_canvas_fps_target" name="pdf_builder_canvas_fps_target"
                               value="<?php echo esc_attr(get_canvas_modal_value('fps_target', $canvas_defaults['fps_target'])); ?>">
                        <span class="value-indicator value-default">Défaut: <?php echo $canvas_defaults['fps_target']; ?> FPS</span>
                    </div>
                    <div class="setting-group">
                        <label for="modal_canvas_memory_limit_js">Limite mémoire JS (MB)</label>
                        <input type="number" id="modal_canvas_memory_limit_js" name="pdf_builder_canvas_memory_limit_js"
                               value="<?php echo esc_attr(get_canvas_modal_value('memory_limit_js', $canvas_defaults['memory_limit_js'])); ?>">
                        <span class="value-indicator value-default">Défaut: <?php echo $canvas_defaults['memory_limit_js']; ?> MB</span>
                    </div>
                    <div class="setting-group">
                        <label for="modal_canvas_response_timeout">Timeout réponse (ms)</label>
                        <input type="number" id="modal_canvas_response_timeout" name="pdf_builder_canvas_response_timeout"
                               value="<?php echo esc_attr(get_canvas_modal_value('response_timeout', $canvas_defaults['response_timeout'])); ?>">
                        <span class="value-indicator value-default">Défaut: <?php echo $canvas_defaults['response_timeout']; ?>ms</span>
                    </div>
                </div>
            </div>

            <!-- Section Debug & Monitoring -->
            <div class="modal-section">
                <h4 class="modal-section-title">Debug & Monitoring</h4>
                <div class="modal-settings-grid compact">
                    <div class="setting-group">
                        <label for="modal_canvas_debug_enabled">Debug activé</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="modal_canvas_debug_enabled" name="pdf_builder_canvas_debug_enabled"
                                   value="1" <?php checked(get_canvas_modal_value('debug_enabled', $canvas_defaults['debug_enabled']), '1'); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="value-indicator value-default">Défaut: Désactivé</span>
                    </div>
                    <div class="setting-group">
                        <label for="modal_canvas_performance_monitoring">Monitoring performance</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="modal_canvas_performance_monitoring" name="pdf_builder_canvas_performance_monitoring"
                                   value="1" <?php checked(get_canvas_modal_value('performance_monitoring', $canvas_defaults['performance_monitoring']), '1'); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="value-indicator value-default">Défaut: Activé</span>
                    </div>
                    <div class="setting-group">
                        <label for="modal_canvas_error_reporting">Rapport d'erreurs</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="modal_canvas_error_reporting" name="pdf_builder_canvas_error_reporting"
                                   value="1" <?php checked(get_canvas_modal_value('error_reporting', $canvas_defaults['error_reporting']), '1'); ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="value-indicator value-default">Défaut: Activé</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="canvas-modal-footer">
            <button type="button" class="button canvas-modal-cancel">Annuler</button>
            <button type="button" class="button button-primary canvas-modal-apply" data-category="systeme">Appliquer</button>
        </div>
    </div>
</div>

<style>
/* Styles pour les modals - utilisant les classes définies dans settings-contenu.php */
</style>

<!-- JavaScript déplacé vers settings-main.php pour éviter les conflits -->
<?php



