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
    <div class="canvas-modal">
        <div class="canvas-modal-header">
            <h3>Paramètres d'Affichage</h3>
            <button type="button" class="canvas-modal-close">&times;</button>
        </div>
        <div class="canvas-modal-body">
            <div class="modal-settings-grid">
                <div class="setting-group">
                    <label for="modal_canvas_width">Largeur (px)</label>
                    <input type="number" id="modal_canvas_width" name="pdf_builder_canvas_width"
                           value="<?php echo esc_attr(get_canvas_modal_value('width', $canvas_defaults['width'])); ?>">
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_height">Hauteur (px)</label>
                    <input type="number" id="modal_canvas_height" name="pdf_builder_canvas_height"
                           value="<?php echo esc_attr(get_canvas_modal_value('height', $canvas_defaults['height'])); ?>">
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_dpi">DPI</label>
                    <input type="number" id="modal_canvas_dpi" name="pdf_builder_canvas_dpi"
                           value="<?php echo esc_attr(get_canvas_modal_value('dpi', $canvas_defaults['dpi'])); ?>">
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_format">Format</label>
                    <select id="modal_canvas_format" name="pdf_builder_canvas_format">
                        <option value="A4" <?php selected(get_canvas_modal_value('format', $canvas_defaults['format']), 'A4'); ?>>A4</option>
                        <option value="A3" <?php selected(get_canvas_modal_value('format', $canvas_defaults['format']), 'A3'); ?>>A3</option>
                        <option value="Letter" <?php selected(get_canvas_modal_value('format', $canvas_defaults['format']), 'Letter'); ?>>Letter</option>
                        <option value="Legal" <?php selected(get_canvas_modal_value('format', $canvas_defaults['format']), 'Legal'); ?>>Legal</option>
                    </select>
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_bg_color">Couleur de fond</label>
                    <input type="color" id="modal_canvas_bg_color" name="pdf_builder_canvas_bg_color"
                           value="<?php echo esc_attr(get_canvas_modal_value('bg_color', $canvas_defaults['bg_color'])); ?>">
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_border_color">Couleur bordure</label>
                    <input type="color" id="modal_canvas_border_color" name="pdf_builder_canvas_border_color"
                           value="<?php echo esc_attr(get_canvas_modal_value('border_color', $canvas_defaults['border_color'])); ?>">
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_border_width">Épaisseur bordure (px)</label>
                    <input type="number" id="modal_canvas_border_width" name="pdf_builder_canvas_border_width"
                           value="<?php echo esc_attr(get_canvas_modal_value('border_width', $canvas_defaults['border_width'])); ?>">
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_shadow_enabled">Ombre activée</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="modal_canvas_shadow_enabled" name="pdf_builder_canvas_shadow_enabled"
                               value="1" <?php checked(get_canvas_modal_value('shadow_enabled', $canvas_defaults['shadow_enabled']), '1'); ?>>
                        <label for="modal_canvas_shadow_enabled"></label>
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
    <div class="canvas-modal">
        <div class="canvas-modal-header">
            <h3>Paramètres de Navigation</h3>
            <button type="button" class="canvas-modal-close">&times;</button>
        </div>
        <div class="canvas-modal-body">
            <div class="modal-settings-grid">
                <div class="setting-group">
                    <label for="modal_canvas_grid_enabled">Grille activée</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="modal_canvas_grid_enabled" name="pdf_builder_canvas_grid_enabled"
                               value="1" <?php checked(get_canvas_modal_value('grid_enabled', $canvas_defaults['grid_enabled']), '1'); ?>>
                        <label for="modal_canvas_grid_enabled"></label>
                    </div>
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_grid_size">Taille grille (px)</label>
                    <input type="number" id="modal_canvas_grid_size" name="pdf_builder_canvas_grid_size"
                           value="<?php echo esc_attr(get_canvas_modal_value('grid_size', $canvas_defaults['grid_size'])); ?>">
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_guides_enabled">Guides activés</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="modal_canvas_guides_enabled" name="pdf_builder_canvas_guides_enabled"
                               value="1" <?php checked(get_canvas_modal_value('guides_enabled', $canvas_defaults['guides_enabled']), '1'); ?>>
                        <label for="modal_canvas_guides_enabled"></label>
                    </div>
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_snap_to_grid">Accrochage à la grille</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="modal_canvas_snap_to_grid" name="pdf_builder_canvas_snap_to_grid"
                               value="1" <?php checked(get_canvas_modal_value('snap_to_grid', $canvas_defaults['snap_to_grid']), '1'); ?>>
                        <label for="modal_canvas_snap_to_grid"></label>
                    </div>
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_zoom_min">Zoom minimum (%)</label>
                    <input type="number" id="modal_canvas_zoom_min" name="pdf_builder_canvas_zoom_min"
                           value="<?php echo esc_attr(get_canvas_modal_value('zoom_min', $canvas_defaults['zoom_min'])); ?>">
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_zoom_max">Zoom maximum (%)</label>
                    <input type="number" id="modal_canvas_zoom_max" name="pdf_builder_canvas_zoom_max"
                           value="<?php echo esc_attr(get_canvas_modal_value('zoom_max', $canvas_defaults['zoom_max'])); ?>">
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_zoom_default">Zoom par défaut (%)</label>
                    <input type="number" id="modal_canvas_zoom_default" name="pdf_builder_canvas_zoom_default"
                           value="<?php echo esc_attr(get_canvas_modal_value('zoom_default', $canvas_defaults['zoom_default'])); ?>">
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_zoom_step">Pas de zoom (%)</label>
                    <input type="number" id="modal_canvas_zoom_step" name="pdf_builder_canvas_zoom_step"
                           value="<?php echo esc_attr(get_canvas_modal_value('zoom_step', $canvas_defaults['zoom_step'])); ?>">
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
    <div class="canvas-modal">
        <div class="canvas-modal-header">
            <h3>Paramètres de Comportement</h3>
            <button type="button" class="canvas-modal-close">&times;</button>
        </div>
        <div class="canvas-modal-body">
            <div class="modal-settings-grid">
                <div class="setting-group">
                    <label for="modal_canvas_drag_enabled">Glisser activé</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="modal_canvas_drag_enabled" name="pdf_builder_canvas_drag_enabled"
                               value="1" <?php checked(get_canvas_modal_value('drag_enabled', $canvas_defaults['drag_enabled']), '1'); ?>>
                        <label for="modal_canvas_drag_enabled"></label>
                    </div>
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_resize_enabled">Redimensionnement activé</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="modal_canvas_resize_enabled" name="pdf_builder_canvas_resize_enabled"
                               value="1" <?php checked(get_canvas_modal_value('resize_enabled', $canvas_defaults['resize_enabled']), '1'); ?>>
                        <label for="modal_canvas_resize_enabled"></label>
                    </div>
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_rotate_enabled">Rotation activée</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="modal_canvas_rotate_enabled" name="pdf_builder_canvas_rotate_enabled"
                               value="1" <?php checked(get_canvas_modal_value('rotate_enabled', $canvas_defaults['rotate_enabled']), '1'); ?>>
                        <label for="modal_canvas_rotate_enabled"></label>
                    </div>
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_multi_select">Sélection multiple</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="modal_canvas_multi_select" name="pdf_builder_canvas_multi_select"
                               value="1" <?php checked(get_canvas_modal_value('multi_select', $canvas_defaults['multi_select']), '1'); ?>>
                        <label for="modal_canvas_multi_select"></label>
                    </div>
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_selection_mode">Mode de sélection</label>
                    <select id="modal_canvas_selection_mode" name="pdf_builder_canvas_selection_mode">
                        <option value="single" <?php selected(get_canvas_modal_value('selection_mode', $canvas_defaults['selection_mode']), 'single'); ?>>Simple</option>
                        <option value="multiple" <?php selected(get_canvas_modal_value('selection_mode', $canvas_defaults['selection_mode']), 'multiple'); ?>>Multiple</option>
                        <option value="group" <?php selected(get_canvas_modal_value('selection_mode', $canvas_defaults['selection_mode']), 'group'); ?>>Groupe</option>
                    </select>
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_keyboard_shortcuts">Raccourcis clavier</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="modal_canvas_keyboard_shortcuts" name="pdf_builder_canvas_keyboard_shortcuts"
                               value="1" <?php checked(get_canvas_modal_value('keyboard_shortcuts', $canvas_defaults['keyboard_shortcuts']), '1'); ?>>
                        <label for="modal_canvas_keyboard_shortcuts"></label>
                    </div>
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_export_quality">Qualité export (%)</label>
                    <input type="number" id="modal_canvas_export_quality" name="pdf_builder_canvas_export_quality"
                           value="<?php echo esc_attr(get_canvas_modal_value('export_quality', $canvas_defaults['export_quality'])); ?>">
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_export_format">Format export</label>
                    <select id="modal_canvas_export_format" name="pdf_builder_canvas_export_format">
                        <option value="pdf" <?php selected(get_canvas_modal_value('export_format', $canvas_defaults['export_format']), 'pdf'); ?>>PDF</option>
                        <option value="png" <?php selected(get_canvas_modal_value('export_format', $canvas_defaults['export_format']), 'png'); ?>>PNG</option>
                        <option value="jpg" <?php selected(get_canvas_modal_value('export_format', $canvas_defaults['export_format']), 'jpg'); ?>>JPG</option>
                        <option value="svg" <?php selected(get_canvas_modal_value('export_format', $canvas_defaults['export_format']), 'svg'); ?>>SVG</option>
                    </select>
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
    <div class="canvas-modal">
        <div class="canvas-modal-header">
            <h3>Paramètres Système</h3>
            <button type="button" class="canvas-modal-close">&times;</button>
        </div>
        <div class="canvas-modal-body">
            <div class="modal-settings-grid">
                <div class="setting-group">
                    <label for="modal_canvas_fps_target">FPS cible</label>
                    <input type="number" id="modal_canvas_fps_target" name="pdf_builder_canvas_fps_target"
                           value="<?php echo esc_attr(get_canvas_modal_value('fps_target', $canvas_defaults['fps_target'])); ?>">
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_memory_limit_js">Limite mémoire JS (MB)</label>
                    <input type="number" id="modal_canvas_memory_limit_js" name="pdf_builder_canvas_memory_limit_js"
                           value="<?php echo esc_attr(get_canvas_modal_value('memory_limit_js', $canvas_defaults['memory_limit_js'])); ?>">
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_response_timeout">Timeout réponse (ms)</label>
                    <input type="number" id="modal_canvas_response_timeout" name="pdf_builder_canvas_response_timeout"
                           value="<?php echo esc_attr(get_canvas_modal_value('response_timeout', $canvas_defaults['response_timeout'])); ?>">
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_debug_enabled">Debug activé</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="modal_canvas_debug_enabled" name="pdf_builder_canvas_debug_enabled"
                               value="1" <?php checked(get_canvas_modal_value('debug_enabled', $canvas_defaults['debug_enabled']), '1'); ?>>
                        <label for="modal_canvas_debug_enabled"></label>
                    </div>
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_performance_monitoring">Monitoring performance</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="modal_canvas_performance_monitoring" name="pdf_builder_canvas_performance_monitoring"
                               value="1" <?php checked(get_canvas_modal_value('performance_monitoring', $canvas_defaults['performance_monitoring']), '1'); ?>>
                        <label for="modal_canvas_performance_monitoring"></label>
                    </div>
                </div>
                <div class="setting-group">
                    <label for="modal_canvas_error_reporting">Rapport d'erreurs</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="modal_canvas_error_reporting" name="pdf_builder_canvas_error_reporting"
                               value="1" <?php checked(get_canvas_modal_value('error_reporting', $canvas_defaults['error_reporting']), '1'); ?>>
                        <label for="modal_canvas_error_reporting"></label>
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
/* Styles pour les modals */
.canvas-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10000;
}

.canvas-modal {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.canvas-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #ddd;
}

.canvas-modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.canvas-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.canvas-modal-close:hover {
    color: #333;
}

.canvas-modal-body {
    padding: 20px;
}

.modal-settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.setting-group {
    display: flex;
    flex-direction: column;
}

.setting-group label {
    font-weight: 500;
    margin-bottom: 5px;
    color: #333;
}

.setting-group input,
.setting-group select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.setting-group input[type="color"] {
    padding: 2px;
    height: 40px;
}

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
    background-color: #ccc;
    transition: 0.4s;
    border-radius: 24px;
}

.toggle-switch label:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.4s;
    border-radius: 50%;
}

.toggle-switch input:checked + label {
    background-color: #2196F3;
}

.toggle-switch input:checked + label:before {
    transform: translateX(26px);
}

.toggle-switch.checked label {
    background-color: #2196F3;
}

.toggle-switch.checked label:before {
    transform: translateX(26px);
}

.canvas-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 20px;
    border-top: 1px solid #ddd;
}

.canvas-modal-footer .button {
    padding: 8px 16px;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}

.canvas-modal-footer .button-primary {
    background-color: #007cba;
    color: white;
    border-color: #007cba;
}

.canvas-modal-footer .button-primary:hover {
    background-color: #005a87;
    border-color: #005a87;
}

.canvas-modal-footer .canvas-modal-cancel:hover {
    background-color: #f1f1f1;
}
</style>

<!-- JavaScript déplacé vers settings-main.php pour éviter les conflits -->
<?php
                                <article class="cache-stat-item">
                                    <div class="cache-stat-number total" id="total-transients-count">0</div>
                                    <div class="cache-stat-label">Total actifs</div>
                                </article>
                                <article class="cache-stat-item">
                                    <div class="cache-stat-number expired" id="expired-transients-count">0</div>
                                    <div class="cache-stat-label">Expirés</div>
                                </article>
                                <article class="cache-stat-item">
                                    <div class="cache-stat-number pdf-builder" id="pdf-builder-transients-count">0</div>
                                    <div class="cache-stat-label">PDF Builder</div>
                                </article>
                            </section>
                        </article>
                        <aside class="cache-warning">
                            <h4 class="cache-warning-title">⚠️ Note importante</h4>
                            <p class="cache-warning-text">
                                Les transients expirent automatiquement. Un nombre élevé de transients n'est généralement pas préoccupant,
                                mais si vous remarquez des problèmes de performance, vous pouvez les vider manuellement.
                            </p>
                        </aside>
                    </section>
                </article>
            </main>
            <footer class="cache-modal-footer">
                <button type="button" class="button button-secondary cache-modal-cancel">Fermer</button>
                <button type="button" class="button button-warning" id="clear-transients-from-modal">🗑️ Vider les transients</button>
            </footer>
        </section>
    </div>
</div>

<!-- Cache Status Configuration Modal -->
<div id="cache-status-modal" class="cache-modal" data-category="status">
    <div class="cache-modal-overlay">
        <section class="cache-modal-container">
            <header class="cache-modal-header">
                <h3>⚙️ Configuration du cache</h3>
                <button type="button" class="cache-modal-close">&times;</button>
            </header>
            <main class="cache-modal-body">
                <aside class="cache-modal-info">
                    <p>
                        <strong>ℹ️ Configuration du système de cache :</strong> Gérez les paramètres de cache pour optimiser les performances du plugin PDF Builder.
                        Le cache améliore considérablement les temps de chargement en stockant les données temporaires.
                    </p>
                </aside>
                <form id="cache-status-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="modal_cache_enabled">Cache activé</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="modal_cache_enabled" name="pdf_builder_cache_enabled" value="1" <?php checked(get_option('pdf_builder_cache_enabled', false)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Active/désactive le système de cache du plugin</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="modal_cache_compression">Compression du cache</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="modal_cache_compression" name="pdf_builder_cache_compression" value="1" <?php checked(get_option('pdf_builder_cache_compression', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Compresser les données en cache pour économiser l'espace disque</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="modal_cache_auto_cleanup">Nettoyage automatique</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="modal_cache_auto_cleanup" name="pdf_builder_cache_auto_cleanup" value="1" <?php checked(get_option('pdf_builder_cache_auto_cleanup', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Nettoyer automatiquement les anciens fichiers cache</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="modal_cache_max_size">Taille max du cache (MB)</label></th>
                            <td>
                                <input type="number" id="modal_cache_max_size" name="pdf_builder_cache_max_size" value="<?php echo max(10, intval(get_option('pdf_builder_cache_max_size', 100))); ?>" min="10" max="1000" step="10" />
                                <p class="description">Taille maximale du dossier cache en mégaoctets</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="modal_cache_ttl">TTL du cache (secondes)</label></th>
                            <td>
                                <input type="number" id="modal_cache_ttl" name="pdf_builder_cache_ttl" value="<?php echo intval(get_option('pdf_builder_cache_ttl', 3600)); ?>" min="0" max="86400" />
                                <p class="description">Durée de vie du cache en secondes (défaut: 3600)</p>
                            </td>
                        </tr>
                    </table>
                </form>
            </main>
            <footer class="cache-modal-footer">
                <button type="button" class="button button-secondary cache-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary cache-modal-save" data-category="status">Sauvegarder</button>
            </footer>
        </section>
    </div>
</div>

<!-- Cache Cleanup Modal -->
<div id="cache-cleanup-modal" class="cache-modal" data-category="cleanup">
    <div class="cache-modal-overlay">
        <section class="cache-modal-container">
            <header class="cache-modal-header">
                <h3>🧹 Nettoyage du cache</h3>
                <button type="button" class="cache-modal-close">&times;</button>
            </header>
            <main class="cache-modal-body">
                <aside class="cache-modal-info">
                    <p>
                        <strong>ℹ️ Nettoyage du cache :</strong> Supprimez les fichiers cache obsolètes et les données temporaires pour libérer de l'espace disque
                        et améliorer les performances. Cette opération est sûre et peut être effectuée à tout moment.
                    </p>
                </aside>
                <article style="margin-top: 20px;">
                    <section>
                        <header>
                            <h4 style="margin-top: 0; color: #495057;">[DERNIERS NETTOYAGES] Derniers nettoyages</h4>
                        </header>
                        <section style="margin-top: 10px;">
                            <article style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #dee2e6;">
                                <span>Dernier nettoyage automatique:</span>
                                <span style="font-weight: bold; color: #28a745;">
                                    <?php
                                    $last_auto_cleanup = get_option('pdf_builder_cache_last_auto_cleanup', 'Jamais');
                                    echo $last_auto_cleanup !== 'Jamais' ? human_time_diff(strtotime($last_auto_cleanup)) . ' ago' : $last_auto_cleanup;
                                    ?>
                                </span>
                            </article>
                            <article style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #dee2e6;">
                                <span>Dernier nettoyage manuel:</span>
                                <span style="font-weight: bold; color: #28a745;">
                                    <?php
                                    $last_manual_cleanup = get_option('pdf_builder_cache_last_manual_cleanup', 'Jamais');
                                    echo $last_manual_cleanup !== 'Jamais' ? human_time_diff(strtotime($last_manual_cleanup)) . ' ago' : $last_manual_cleanup;
                                    ?>
                                </span>
                            </article>
                        </section>
                    </section>
                    <section>
                        <header>
                            <h4 style="margin-top: 0; color: #0c5460;">[ACTIONS NETTOYAGE] Actions de nettoyage disponibles</h4>
                        </header>
                        <section style="margin-top: 15px; display: grid; gap: 10px;">
                            <article class="flex-center-gap">
                                <input type="checkbox" id="cleanup_files" checked>
                                <label for="cleanup_files">Supprimer les fichiers cache obsolètes</label>
                            </article>
                            <article class="flex-center-gap">
                                <input type="checkbox" id="cleanup_transients" checked>
                                <label for="cleanup_transients">Vider les transients expirés</label>
                            </article>
                            <article class="flex-center-gap">
                                <input type="checkbox" id="cleanup_temp">
                                <label for="cleanup_temp">Supprimer les fichiers temporaires (+24h)</label>
                            </article>
                        </section>
                    </section>
                </article>
            </main>
            <footer class="cache-modal-footer">
                <button type="button" class="button button-secondary cache-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary" id="perform-cleanup-btn">🧹 Nettoyer maintenant</button>
            </footer>
        </section>
    </div>
</div>
<!-- Canvas Affichage Modal Overlay (fusion Dimensions + Apparence) -->
<div id="canvas-affichage-modal-overlay" class="canvas-modal-overlay" data-modal="canvas-affichage-modal">
    <section id="canvas-affichage-modal" class="canvas-modal-container">
        <header class="canvas-modal-header">
            <h3>🎨 Affichage & Dimensions</h3>
            <button type="button" class="canvas-modal-close">&times;</button>
        </header>
        <main class="canvas-modal-body">
            <aside class="canvas-modal-info">
                <p>
                    <strong>ℹ️ Comment ça marche :</strong> Ces paramètres définissent la taille, l'orientation, la qualité et l'apparence générale du document PDF généré.
                    Configurez les dimensions, les couleurs et les effets visuels.
                </p>
            </aside>
            <form id="canvas-affichage-form">
                <section>
                    <header>
                        <h4 class="canvas-modal-section-title">📏 Dimensions & Format</h4>
                    </header>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_format">Format du document</label></th>
                            <td>
                                <select id="canvas_format" name="pdf_builder_canvas_format">
                                    <option value="A4" <?php selected(get_canvas_option('canvas_format', 'A4'), 'A4'); ?>>A4 (210×297mm)</option>
                                    <option value="A3" disabled <?php selected(get_canvas_option('canvas_format', 'A4'), 'A3'); ?>>A3 (297×420mm) - soon</option>
                                    <option value="A5" disabled <?php selected(get_canvas_option('canvas_format', 'A4'), 'A5'); ?>>A5 (148×210mm) - soon</option>
                                    <option value="Letter" disabled <?php selected(get_canvas_option('canvas_format', 'A4'), 'Letter'); ?>>Letter (8.5×11") - soon</option>
                                    <option value="Legal" disabled <?php selected(get_canvas_option('canvas_format', 'A4'), 'Legal'); ?>>Legal (8.5×14") - soon</option>
                                    <option value="Tabloid" disabled <?php selected(get_canvas_option('canvas_format', 'A4'), 'Tabloid'); ?>>Tabloid (11×17") - soon</option>
                                </select>
                                <p class="canvas-modal-description">Taille standard du document PDF (A4 disponible)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_dpi">Résolution DPI</label></th>
                            <td>
                                <select id="canvas_dpi" name="pdf_builder_canvas_dpi">
                                    <option value="72" <?php selected(get_canvas_option('canvas_dpi', 96), '72'); ?>>72 DPI (Web)</option>
                                    <option value="96" <?php selected(get_canvas_option('canvas_dpi', 96), '96'); ?>>96 DPI (Écran)</option>
                                    <option value="150" <?php selected(get_canvas_option('canvas_dpi', 96), '150'); ?>>150 DPI (Impression)</option>
                                    <option value="300" <?php selected(get_canvas_option('canvas_dpi', 96), '300'); ?>>300 DPI (Haute qualité)</option>
                                </select>
                                <p class="canvas-modal-description">Qualité d'impression (plus élevé = meilleure qualité)</p>
                            </td>
                        </tr>
                    </table>
                </section>

                <section>
                    <header>
                        <h4 class="canvas-modal-section-title">🎨 Apparence</h4>
                    </header>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_bg_color">Couleur de fond</label></th>
                            <td>
                                <input type="color" id="canvas_bg_color" name="pdf_builder_canvas_bg_color" value="<?php echo esc_attr(get_canvas_option('canvas_bg_color', '#ffffff')); ?>" />
                                <p class="canvas-modal-description">Couleur de fond du canvas</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_border_color">Couleur de bordure</label></th>
                            <td>
                                <input type="color" id="canvas_border_color" name="pdf_builder_canvas_border_color" value="<?php echo esc_attr(get_canvas_option('canvas_border_color', '#cccccc')); ?>" />
                                <p class="canvas-modal-description">Couleur de la bordure du canvas</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_border_width">Épaisseur de bordure (px)</label></th>
                            <td>
                                <input type="number" id="canvas_border_width" name="pdf_builder_canvas_border_width" value="<?php echo intval(get_canvas_option('canvas_border_width', 1)); ?>" min="0" max="10" />
                                <p class="canvas-modal-description">Épaisseur de la bordure en pixels</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_shadow_enabled">Ombre activée</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_canvas_shadow_enabled" value="0">
                                    <input type="checkbox" id="canvas_shadow_enabled" name="pdf_builder_canvas_shadow_enabled" value="1" <?php checked(get_canvas_option('canvas_shadow_enabled', '0'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="canvas-modal-description">Ajoute une ombre au canvas</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_container_bg_color">Fond du conteneur</label></th>
                            <td>
                                <input type="color" id="canvas_container_bg_color" name="pdf_builder_canvas_container_bg_color" value="<?php echo esc_attr(get_canvas_option('canvas_container_bg_color', '#f8f9fa')); ?>" />
                                <p class="canvas-modal-description">Couleur de fond de la zone autour du canvas</p>
                            </td>
                        </tr>
                    </table>
                </section>
            </form>
        </main>
        <footer class="canvas-modal-footer">
            <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
            <button type="button" class="button button-primary canvas-modal-apply" data-category="affichage">Appliquer</button>
        </footer>
    </section>
</div>
                        <?php
                            /**
                             * Canvas Configuration Modals
                             * Updated: 2025-12-03 00:30:00
                             */

                            // Définir les formats de papier si pas déjà défini
                            if (!defined('PDF_BUILDER_PAPER_FORMATS')) {
                                define('PDF_BUILDER_PAPER_FORMATS', [
                                    'A4' => ['width' => 210.0, 'height' => 297.0],
                                    'A3' => ['width' => 297.0, 'height' => 420.0],
                                    'A5' => ['width' => 148.0, 'height' => 210.0],
                                    'Letter' => ['width' => 215.9, 'height' => 279.4],
                                    'Legal' => ['width' => 215.9, 'height' => 355.6],
                                    'Tabloid' => ['width' => 279.4, 'height' => 431.8]
                                ]);
                            }
                        ?>
                    <tr>
                        <th scope="row"><label>Dimensions calculées</label></th>
                        <td>
                            <aside id="canvas-dimensions-display" class="canvas-modal-display">
                                <span id="canvas-width-display"><?php echo intval(get_canvas_option('canvas_width', 794)); ?></span> ×
                                <span id="canvas-height-display"><?php echo intval(get_canvas_option('canvas_height', 1123)); ?></span> px
                                <br>
                                <small id="canvas-mm-display">
                                    <?php
                                    $format = get_canvas_option('canvas_format', 'A4');
                                    $orientation = 'portrait'; // FORCÉ EN PORTRAIT - v2.0

                                    // Utiliser les dimensions standard centralisées
                                    $formatDimensionsMM = PDF_BUILDER_PAPER_FORMATS;

                                    $dimensions = isset($formatDimensionsMM[$format]) ? $formatDimensionsMM[$format] : $formatDimensionsMM['A4'];

                                    // Orientation temporairement désactivée - toujours portrait
                                    // if ($orientation === 'landscape') {
                                    //     $temp = $dimensions['width'];
                                    //     $dimensions['width'] = $dimensions['height'];
                                    //     $dimensions['height'] = $temp;
                                    // }

                                    echo round($dimensions['width'], 1) . '×' . round($dimensions['height'], 1) . 'mm';
                                    ?>
                                </small>
                            </aside>
                        </td>
                    </tr>
                </table>
            </form>
        </main>
        <footer class="canvas-modal-footer">
            <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
            <button type="button" class="button button-primary canvas-modal-apply" data-category="dimensions">Appliquer</button>
        </footer>
    </section>
</div>

<!-- Canvas Dimensions Modal (hidden container) -->
<!-- REMOVED: Empty container, content moved to overlay -->
<!-- Canvas Configuration Modals Zoom & Navigation -->

<!-- Canvas Configuration Modals Apparence -->

<!-- Canvas Configuration Modals Grille & Guides -->
<?php
    error_log("[PDF Builder] MODAL_RENDER - Rendering grille modal");
    $grille_guides_enabled = get_canvas_option('canvas_guides_enabled', '1');
    $grille_grid_enabled = get_canvas_option('canvas_grid_enabled', '1');
    $grille_grid_size = get_canvas_option('canvas_grid_size', '20');
    $grille_snap_to_grid = get_canvas_option('canvas_snap_to_grid', '1');
    error_log("[PDF Builder] MODAL_RENDER - Grille values: guides=$grille_guides_enabled, grid=$grille_grid_enabled, size=$grille_grid_size, snap=$grille_snap_to_grid");
?>
<!-- Canvas Navigation Modal Overlay (fusion Grille + Zoom) -->
<div id="canvas-navigation-modal-overlay" class="canvas-modal-overlay" data-modal="canvas-navigation-modal">
    <section id="canvas-navigation-modal" class="canvas-modal-container">
        <header class="canvas-modal-header">
            <h3>🧭 Navigation & Zoom</h3>
            <button type="button" class="canvas-modal-close">&times;</button>
        </header>
        <main class="canvas-modal-body">
            <aside class="canvas-modal-info">
                <p>
                    <strong>ℹ️ Comment ça marche :</strong> Ces paramètres contrôlent la navigation et le zoom sur le canvas.
                    Configurez la grille d'alignement, les niveaux de zoom et les options de déplacement.
                </p>
            </aside>
            <form id="canvas-navigation-form">
                <section>
                    <header>
                        <h4 class="canvas-modal-section-title">📐 Grille & Guides</h4>
                    </header>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_guides_enabled">Guides activés</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_canvas_guides_enabled" value="0">
                                    <input type="checkbox" id="canvas_guides_enabled" name="pdf_builder_canvas_guides_enabled" value="1" <?php checked(get_canvas_option('canvas_guides_enabled', '1'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="canvas-modal-description">Affiche des guides d'alignement temporaires</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_grid_enabled">Grille activée</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_canvas_grid_enabled" value="0">
                                    <input type="checkbox" id="canvas_grid_enabled" name="pdf_builder_canvas_grid_enabled" value="1" <?php checked(get_canvas_option('canvas_grid_enabled', '1'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="canvas-modal-description">Affiche/masque le quadrillage sur le canvas</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_grid_size">Taille de la grille (px)</label></th>
                            <td>
                                <input type="number" id="canvas_grid_size" name="pdf_builder_canvas_grid_size" value="<?php echo intval(get_canvas_option('canvas_grid_size', 20)); ?>" min="5" max="100" />
                                <p class="canvas-modal-description">Distance entre les lignes de la grille (5-100px)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_snap_to_grid">Accrochage à la grille</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_canvas_snap_to_grid" value="0">
                                    <input type="checkbox" id="canvas_snap_to_grid" name="pdf_builder_canvas_snap_to_grid" value="1" <?php checked(get_canvas_option('canvas_snap_to_grid', '1'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="canvas-modal-description">Les éléments s'alignent automatiquement sur la grille</p>
                            </td>
                        </tr>
                    </table>
                </section>

                <section>
                    <header>
                        <h4 class="canvas-modal-section-title spaced">🔍 Zoom</h4>
                    </header>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="zoom_min">Zoom minimum (%)</label></th>
                            <td>
                                <input type="number" id="zoom_min" name="pdf_builder_canvas_zoom_min" value="<?php echo intval(get_canvas_option('canvas_zoom_min', 10)); ?>" min="1" max="100" />
                                <p class="canvas-modal-description">Niveau de zoom minimum autorisé</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="zoom_max">Zoom maximum (%)</label></th>
                            <td>
                                <input type="number" id="zoom_max" name="pdf_builder_canvas_zoom_max" value="<?php echo intval(get_canvas_option('canvas_zoom_max', 500)); ?>" min="100" max="1000" />
                                <p class="canvas-modal-description">Niveau de zoom maximum autorisé</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="zoom_default">Zoom par défaut (%)</label></th>
                            <td>
                                <input type="number" id="zoom_default" name="pdf_builder_canvas_zoom_default" value="<?php echo intval(get_canvas_option('canvas_zoom_default', 100)); ?>" min="10" max="500" />
                                <p class="canvas-modal-description">Niveau de zoom au chargement du canvas</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="zoom_step">Pas de zoom (%)</label></th>
                            <td>
                                <input type="number" id="zoom_step" name="pdf_builder_canvas_zoom_step" value="<?php echo intval(get_canvas_option('canvas_zoom_step', 25)); ?>" min="5" max="50" />
                                <p class="canvas-modal-description">Incrément de zoom par étape</p>
                            </td>
                        </tr>
                    </table>
                </section>
            </form>
        </main>
        <footer class="canvas-modal-footer">
            <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
            <button type="button" class="button button-primary canvas-modal-apply" data-category="navigation">Appliquer</button>
        </footer>
    </section>
</div>
<!-- Canvas Configuration Modals Interactions & Comportement-->
<!-- Canvas Comportement Modal Overlay (fusion Interactions + Export) -->
<div id="canvas-comportement-modal-overlay" class="canvas-modal-overlay" data-modal="canvas-comportement-modal">
    <section id="canvas-comportement-modal" class="canvas-modal-container">
        <header class="canvas-modal-header">
            <h3>🎮 Comportement & Export</h3>
            <button type="button" class="canvas-modal-close">&times;</button>
        </header>
        <main class="canvas-modal-body">
            <aside class="canvas-modal-info">
                <p>
                    <strong>ℹ️ Comment ça marche :</strong> Ces paramètres contrôlent les interactions avec le canvas et les options d'export.
                    Configurez les manipulations d'éléments, les raccourcis clavier et les formats d'export.
                </p>
            </aside>
            <form id="canvas-comportement-form">
                <section>
                    <header>
                        <h4 class="canvas-modal-section-title">🖱️ Interactions</h4>
                    </header>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_drag_enabled">Glisser-déposer activé</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_canvas_drag_enabled" value="0">
                                    <input type="checkbox" id="canvas_drag_enabled" name="pdf_builder_canvas_drag_enabled" value="1" <?php checked(get_canvas_option('canvas_drag_enabled', '1'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="canvas-modal-description">Permet de déplacer les éléments sur le canvas</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_resize_enabled">Redimensionnement activé</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_canvas_resize_enabled" value="0">
                                    <input type="checkbox" id="canvas_resize_enabled" name="pdf_builder_canvas_resize_enabled" value="1" <?php checked(get_canvas_option('canvas_resize_enabled', '1'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="canvas-modal-description">Affiche les poignées pour redimensionner les éléments</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_rotate_enabled">Rotation activée</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_canvas_rotate_enabled" value="0">
                                    <input type="checkbox" id="canvas_rotate_enabled" name="pdf_builder_canvas_rotate_enabled" value="1" <?php checked(get_canvas_option('canvas_rotate_enabled', '1'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="canvas-modal-description">Permet de faire pivoter les éléments avec la souris</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_multi_select">Sélection multiple</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_canvas_multi_select" value="0">
                                    <input type="checkbox" id="canvas_multi_select" name="pdf_builder_canvas_multi_select" value="1" <?php checked(get_canvas_option('canvas_multi_select', '1'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="canvas-modal-description">Ctrl+Clic pour sélectionner plusieurs éléments</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_selection_mode">Mode de sélection</label></th>
                            <td>
                                <select id="canvas_selection_mode" name="pdf_builder_canvas_selection_mode">
                                    <option value="click" <?php selected(get_canvas_option('canvas_selection_mode', 'click'), 'click'); ?>>Clic simple</option>
                                    <option value="lasso" <?php selected(get_canvas_option('canvas_selection_mode', 'click'), 'lasso'); ?>>Lasso</option>
                                    <option value="rectangle" <?php selected(get_canvas_option('canvas_selection_mode', 'click'), 'rectangle'); ?>>Rectangle</option>
                                </select>
                                <p class="canvas-modal-description">Méthode de sélection des éléments sur le canvas</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_keyboard_shortcuts">Raccourcis clavier</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_canvas_keyboard_shortcuts" value="0">
                                    <input type="checkbox" id="canvas_keyboard_shortcuts" name="pdf_builder_canvas_keyboard_shortcuts" value="1" <?php checked(get_canvas_option('canvas_keyboard_shortcuts', '1'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="canvas-modal-description">Active les raccourcis clavier (Ctrl+Z, Ctrl+Y, etc.)</p>
                            </td>
                        </tr>
                    </table>
                </section>

                <section>
                    <header>
                        <h4 class="canvas-modal-section-title spaced">📤 Export</h4>
                    </header>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_export_format">Format d'export par défaut</label></th>
                            <td>
                                <select id="canvas_export_format" name="pdf_builder_canvas_export_format">
                                    <option value="png" <?php selected(get_canvas_option('canvas_export_format', 'png'), 'png'); ?>>PNG</option>
                                    <option value="jpg" <?php selected(get_canvas_option('canvas_export_format', 'png'), 'jpg'); ?>>JPG</option>
                                    <option value="pdf" <?php selected(get_canvas_option('canvas_export_format', 'png'), 'pdf'); ?>>PDF</option>
                                </select>
                                <p class="canvas-modal-description">Format par défaut pour l'export</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_export_quality">Qualité d'export (%)</label></th>
                            <td>
                                <input type="number" id="canvas_export_quality" name="pdf_builder_canvas_export_quality" value="<?php echo intval(get_canvas_option('canvas_export_quality', 90)); ?>" min="1" max="100" />
                                <p class="canvas-modal-description">Qualité de l'image exportée (1-100%)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_export_transparent">Fond transparent</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_canvas_export_transparent" value="0">
                                    <input type="checkbox" id="canvas_export_transparent" name="pdf_builder_canvas_export_transparent" value="1" <?php checked(get_canvas_option('canvas_export_transparent', '0'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="canvas-modal-description">Export avec fond transparent (PNG uniquement)</p>
                            </td>
                        </tr>
                    </table>
                </section>
            </form>
        </main>
        <footer class="canvas-modal-footer">
            <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
            <button type="button" class="button button-primary canvas-modal-apply" data-category="comportement">Appliquer</button>
        </footer>
    </section>
</div>
<!-- Canvas Configuration Modals Export & Qualité -->

<!-- Canvas Configuration Modals Performance -->
<!-- Canvas Système Modal Overlay (fusion Performance + Debug) -->
<div id="canvas-systeme-modal-overlay" class="canvas-modal-overlay" data-modal="canvas-systeme-modal">
    <section id="canvas-systeme-modal" class="canvas-modal-container">
        <header class="canvas-modal-header">
            <h3>⚙️ Système & Performance</h3>
            <button type="button" class="canvas-modal-close">&times;</button>
        </header>
        <main class="canvas-modal-body">
            <aside class="canvas-modal-info">
                <p>
                    <strong>ℹ️ Comment ça marche :</strong> Ces paramètres système contrôlent les performances, la mémoire et les outils de débogage.
                    Configurez l'optimisation et le monitoring pour une expérience optimale.
                </p>
            </aside>
            <form id="canvas-systeme-form">
                <section>
                    <header>
                        <h4 class="canvas-modal-section-title">⚡ Performance</h4>
                    </header>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_fps_target">Cible FPS</label></th>
                            <td>
                                <select id="canvas_fps_target" name="pdf_builder_canvas_fps_target">
                                    <option value="30" <?php selected(get_canvas_option('canvas_fps_target', 60), 30); ?>>30 FPS (Économie)</option>
                                    <option value="60" <?php selected(get_canvas_option('canvas_fps_target', 60), 60); ?>>60 FPS (Standard)</option>
                                    <option value="120" <?php selected(get_canvas_option('canvas_fps_target', 60), 120); ?>>120 FPS (Haute performance)</option>
                                </select>
                                <p class="canvas-modal-description">Fluidité du rendu canvas (plus élevé = plus de ressources)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_memory_limit_js">Limite mémoire JavaScript</label></th>
                            <td>
                                <select id="canvas_memory_limit_js" name="pdf_builder_canvas_memory_limit_js">
                                    <option value="128" <?php selected(get_canvas_option('canvas_memory_limit_js', '256'), '128'); ?>>128 MB</option>
                                    <option value="256" <?php selected(get_canvas_option('canvas_memory_limit_js', '256'), '256'); ?>>256 MB</option>
                                    <option value="512" <?php selected(get_canvas_option('canvas_memory_limit_js', '256'), '512'); ?>>512 MB</option>
                                    <option value="1024" <?php selected(get_canvas_option('canvas_memory_limit_js', '256'), '1024'); ?>>1 GB</option>
                                </select>
                                <p class="canvas-modal-description">Mémoire allouée au canvas et aux éléments</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_memory_limit_php">Limite mémoire PHP</label></th>
                            <td>
                                <select id="canvas_memory_limit_php" name="pdf_builder_canvas_memory_limit_php">
                                    <option value="128" <?php selected(get_canvas_option('canvas_memory_limit_php', '256'), '128'); ?>>128 MB</option>
                                    <option value="256" <?php selected(get_canvas_option('canvas_memory_limit_php', '256'), '256'); ?>>256 MB</option>
                                    <option value="512" <?php selected(get_canvas_option('canvas_memory_limit_php', '256'), '512'); ?>>512 MB</option>
                                    <option value="1024" <?php selected(get_canvas_option('canvas_memory_limit_php', '256'), '1024'); ?>>1 GB</option>
                                </select>
                                <p class="canvas-modal-description">Mémoire pour génération PDF et traitement</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_response_timeout">Timeout réponses AJAX</label></th>
                            <td>
                                <select id="canvas_response_timeout" name="pdf_builder_canvas_response_timeout">
                                    <option value="10" <?php selected(get_canvas_option('canvas_response_timeout', '30'), '10'); ?>>10 secondes</option>
                                    <option value="30" <?php selected(get_canvas_option('canvas_response_timeout', '30'), '30'); ?>>30 secondes</option>
                                    <option value="60" <?php selected(get_canvas_option('canvas_response_timeout', '30'), '60'); ?>>60 secondes</option>
                                    <option value="120" <?php selected(get_canvas_option('canvas_response_timeout', '30'), '120'); ?>>120 secondes</option>
                                </select>
                                <p class="canvas-modal-description">Délai maximum pour les requêtes serveur</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_lazy_loading_editor">Chargement paresseux (Éditeur)</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_canvas_lazy_loading_editor" value="0">
                                    <input type="checkbox" id="canvas_lazy_loading_editor" name="pdf_builder_canvas_lazy_loading_editor" value="1" <?php checked(get_canvas_option('canvas_lazy_loading_editor', '1'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="canvas-modal-description">Charge les éléments seulement quand visibles</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_preload_critical">Préchargement ressources critiques</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_canvas_preload_critical" value="0">
                                    <input type="checkbox" id="canvas_preload_critical" name="pdf_builder_canvas_preload_critical" value="1" <?php checked(get_canvas_option('canvas_preload_critical', '1'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="canvas-modal-description">Précharge les polices et outils essentiels</p>
                            </td>
                        </tr>
                    </table>
                </section>

                <section>
                    <header>
                        <h4 class="canvas-modal-section-title spaced">🐛 Debug & Monitoring</h4>
                    </header>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_debug_enabled">Debug activé</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_canvas_debug_enabled" value="0">
                                    <input type="checkbox" id="canvas_debug_enabled" name="pdf_builder_canvas_debug_enabled" value="1" <?php checked(get_canvas_option('canvas_debug_enabled', '0'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="canvas-modal-description">Active les logs de débogage détaillés</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_performance_monitoring">Monitoring performance</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_canvas_performance_monitoring" value="0">
                                    <input type="checkbox" id="canvas_performance_monitoring" name="pdf_builder_canvas_performance_monitoring" value="1" <?php checked(get_canvas_option('canvas_performance_monitoring', '0'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="canvas-modal-description">Surveille les métriques de performance</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_error_reporting">Rapport d'erreurs</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_canvas_error_reporting" value="0">
                                    <input type="checkbox" id="canvas_error_reporting" name="pdf_builder_canvas_error_reporting" value="1" <?php checked(get_canvas_option('canvas_error_reporting', '0'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="canvas-modal-description">Rapporte automatiquement les erreurs</p>
                            </td>
                        </tr>
                    </table>
                </section>
            </form>
        </main>
        <footer class="canvas-modal-footer">
            <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
            <button type="button" class="button button-primary canvas-modal-apply" data-category="systeme">Appliquer</button>
        </footer>
    </section>
</div>
            <main class="canvas-modal-body">
                <aside class="canvas-modal-info">
                    <p >
                        <strong>🚀 Optimisation :</strong> Ces paramètres améliorent les performances de l'éditeur et du plugin pour une expérience plus fluide.
                    </p>
                </aside>
                <form id="canvas-performance-form">
                    <!-- Section Éditeur PDF -->
                    <section>
                        <header>
                            <h4 class="canvas-modal-section-title margin-25">
                                <span class="canvas-modal-inline-flex">
                                    [EDITEUR PDF] Éditeur PDF
                                </span>
                            </h4>
                        </header>
                        <p class="canvas-modal-sub-description">Paramètres de performance pour l'interface de conception</p>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="canvas_fps_target">Cible FPS</label></th>
                                <td>
                                    <select id="canvas_fps_target" name="pdf_builder_canvas_fps_target">
                                        <option value="30" <?php selected(get_canvas_option('canvas_fps_target', 60), 30); ?>>30 FPS (Économie)</option>
                                        <option value="60" <?php selected(get_canvas_option('canvas_fps_target', 60), 60); ?>>60 FPS (Standard)</option>
                                        <option value="120" <?php selected(get_canvas_option('canvas_fps_target', 60), 120); ?>>120 FPS (Haute performance)</option>
                                    </select>
                                    <aside id="fps_preview" class="canvas-modal-preview">
                                        FPS actuel : <span id="current_fps_value"><?php echo intval(get_canvas_option('canvas_fps_target', 60)); ?></span>
                                    </aside>
                                    <p class="canvas-modal-description">Fluidité du rendu canvas (plus élevé = plus de ressources)</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="canvas_memory_limit_js">Limite mémoire JavaScript</label></th>
                                <td>
                                    <select id="canvas_memory_limit_js" name="pdf_builder_canvas_memory_limit_js">
                                        <option value="128" <?php selected(get_canvas_option('canvas_memory_limit_js', '256'), '128'); ?>>128 MB</option>
                                        <option value="256" <?php selected(get_canvas_option('canvas_memory_limit_js', '256'), '256'); ?>>256 MB</option>
                                        <option value="512" <?php selected(get_canvas_option('canvas_memory_limit_js', '256'), '512'); ?>>512 MB</option>
                                        <option value="1024" <?php selected(get_canvas_option('canvas_memory_limit_js', '256'), '1024'); ?>>1 GB</option>
                                    </select>
                                    <p class="canvas-modal-description">Mémoire allouée au canvas et aux éléments</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="canvas_lazy_loading_editor">Chargement paresseux (Éditeur)</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="canvas_lazy_loading_editor" name="pdf_builder_canvas_lazy_loading_editor" value="1" <?php checked(get_canvas_option('canvas_lazy_loading_editor', '1'), '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="canvas-modal-description">Charge les éléments seulement quand visibles</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="canvas_preload_critical">Préchargement ressources critiques</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="canvas_preload_critical" name="pdf_builder_canvas_preload_critical" value="1" <?php checked(get_canvas_option('canvas_preload_critical', '1'), '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="canvas-modal-description">Précharge les polices et outils essentiels</p>
                                </td>
                            </tr>
                        </table>
                    </section>

                    <!-- Section Plugin WordPress -->
                    <section>
                        <header>
                            <h4 class="canvas-modal-section-title margin-35">
                                <span class="canvas-modal-inline-flex">
                                    🔌 Plugin WordPress
                                </span>
                            </h4>
                        </header>
                        <p class="canvas-modal-sub-description">Paramètres de performance pour le backend et génération PDF</p>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="canvas_memory_limit_php">Limite mémoire PHP</label></th>
                                <td>
                                    <select id="canvas_memory_limit_php" name="pdf_builder_canvas_memory_limit_php">
                                        <option value="128" <?php selected(get_canvas_option('canvas_memory_limit_php', '256'), '128'); ?>>128 MB</option>
                                        <option value="256" <?php selected(get_canvas_option('canvas_memory_limit_php', '256'), '256'); ?>>256 MB</option>
                                        <option value="512" <?php selected(get_canvas_option('canvas_memory_limit_php', '256'), '512'); ?>>512 MB</option>
                                        <option value="1024" <?php selected(get_canvas_option('canvas_memory_limit_php', '256'), '1024'); ?>>1 GB</option>
                                    </select>
                                    <p class="canvas-modal-description">Mémoire pour génération PDF et traitement</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="canvas_response_timeout">Timeout réponses AJAX</label></th>
                                <td>
                                    <select id="canvas_response_timeout" name="pdf_builder_canvas_response_timeout">
                                        <option value="10" <?php selected(get_canvas_option('canvas_response_timeout', '30'), '10'); ?>>10 secondes</option>
                                        <option value="30" <?php selected(get_canvas_option('canvas_response_timeout', '30'), '30'); ?>>30 secondes</option>
                                        <option value="60" <?php selected(get_canvas_option('canvas_response_timeout', '30'), '60'); ?>>60 secondes</option>
                                        <option value="120" <?php selected(get_canvas_option('canvas_response_timeout', '30'), '120'); ?>>120 secondes</option>
                                    </select>
                                    <p class="canvas-modal-description">Délai maximum pour les requêtes serveur</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="canvas_lazy_loading_plugin">Chargement paresseux (Plugin)</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="canvas_lazy_loading_plugin" name="pdf_builder_canvas_lazy_loading_plugin" value="1" <?php checked(get_canvas_option('canvas_lazy_loading_plugin', '1'), '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="canvas-modal-description">Charge les données seulement quand nécessaire</p>
                                </td>
                            </tr>
                        </table>
                    </section>
                </form>
            </main>
            <footer class="canvas-modal-footer">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-apply" data-category="performance">Appliquer</button>
            </footer>
        </section>
</div>

<!-- Canvas performance Modal (hidden container) -->
<!-- REMOVED: Empty container, content moved to overlay -->
<!-- Canvas Configuration Modals Debug -->


<!-- JavaScript déplacé vers settings-main.php pour éviter les conflits -->
<?php



