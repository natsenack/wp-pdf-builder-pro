<?php // Modal components - Updated: 2025-11-18 20:20:00 ?>

<!-- Canvas Configuration Modals -->
<div id="canvas-dimensions-modal" class="canvas-modal" style="display: none;">
    <div class="canvas-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; display: flex; align-items: center; justify-content: center;">
        <div class="canvas-modal-content" style="background: white; border-radius: 12px; padding: 30px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
            <div class="canvas-modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid #dee2e6; padding-bottom: 15px;">
                <h3 style="margin: 0; color: #495057;">📐 Dimensions & Format</h3>
                <button type="button" class="canvas-modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">&times;</button>
            </div>
            <div class="canvas-modal-body">
                <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #007cba;">
                    <p style="margin: 0; font-size: 14px; color: #495057; line-height: 1.5;">
                        <strong>💡 Comment ça marche :</strong> Ces paramètres définissent la taille, l'orientation et la qualité du document PDF généré. 
                        Le format A4 est actuellement supporté, d'autres formats arrivent bientôt.
                    </p>
                </div>
                <form id="canvas-dimensions-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_format">Format du document</label></th>
                            <td>
                                <select id="canvas_format" name="canvas_format">
                                    <option value="A4" <?php selected(get_option('pdf_builder_canvas_format', 'A4'), 'A4'); ?>>A4 (210×297mm)</option>
                                    <option value="A3" disabled <?php selected(get_option('pdf_builder_canvas_format', 'A4'), 'A3'); ?>>A3 (297×420mm) - soon</option>
                                    <option value="A5" disabled <?php selected(get_option('pdf_builder_canvas_format', 'A4'), 'A5'); ?>>A5 (148×210mm) - soon</option>
                                    <option value="Letter" disabled <?php selected(get_option('pdf_builder_canvas_format', 'A4'), 'Letter'); ?>>Letter (8.5×11") - soon</option>
                                    <option value="Legal" disabled <?php selected(get_option('pdf_builder_canvas_format', 'A4'), 'Legal'); ?>>Legal (8.5×14") - soon</option>
                                    <option value="Tabloid" disabled <?php selected(get_option('pdf_builder_canvas_format', 'A4'), 'Tabloid'); ?>>Tabloid (11×17") - soon</option>
                                </select>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Taille standard du document PDF (A4 disponible)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_orientation">Orientation</label></th>
                            <td>
                                <select id="canvas_orientation" name="canvas_orientation">
                                    <option value="portrait" <?php selected(get_option('pdf_builder_canvas_orientation', 'portrait'), 'portrait'); ?>>Portrait</option>
                                    <option value="landscape" disabled <?php selected(get_option('pdf_builder_canvas_orientation', 'portrait'), 'landscape'); ?>>Paysage - soon</option>
                                </select>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Orientation verticale du document</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_dpi">Résolution DPI</label></th>
                            <td>
                                <select id="canvas_dpi" name="canvas_dpi">
                                    <option value="72" <?php selected(get_option('pdf_builder_canvas_dpi', 150), '72'); ?>>72 DPI (Web)</option>
                                    <option value="150" <?php selected(get_option('pdf_builder_canvas_dpi', 150), '150'); ?>>150 DPI (Impression)</option>
                                    <option value="300" <?php selected(get_option('pdf_builder_canvas_dpi', 150), '300'); ?>>300 DPI (Haute qualité)</option>
                                </select>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Qualité d'impression (plus élevé = meilleure qualité)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label>Dimensions calculées</label></th>
                            <td>
                                <div id="canvas-dimensions-display" style="padding: 10px; background: #f8f9fa; border-radius: 4px; font-family: monospace;">
                                    <span id="canvas-width-display"><?php echo intval(get_option('pdf_builder_canvas_width', 800)); ?></span> ×
                                    <span id="canvas-height-display"><?php echo intval(get_option('pdf_builder_canvas_height', 600)); ?></span> px
                                    <br>
                                    <small id="canvas-mm-display" style="color: #666;">
                                        <?php
                                        $format = get_option('pdf_builder_canvas_format', 'A4');
                                        $orientation = get_option('pdf_builder_canvas_orientation', 'portrait');
                                        
                                        // Dimensions standard en mm pour chaque format
                                        $formatDimensionsMM = [
                                            'A4' => ['width' => 210, 'height' => 297],
                                            'A3' => ['width' => 297, 'height' => 420],
                                            'A5' => ['width' => 148, 'height' => 210],
                                            'Letter' => ['width' => 215.9, 'height' => 279.4],
                                            'Legal' => ['width' => 215.9, 'height' => 355.6],
                                            'Tabloid' => ['width' => 279.4, 'height' => 431.8]
                                        ];
                                        
                                        $dimensions = isset($formatDimensionsMM[$format]) ? $formatDimensionsMM[$format] : $formatDimensionsMM['A4'];
                                        
                                        // Appliquer l'orientation
                                        if ($orientation === 'landscape') {
                                            $temp = $dimensions['width'];
                                            $dimensions['width'] = $dimensions['height'];
                                            $dimensions['height'] = $temp;
                                        }
                                        
                                        echo round($dimensions['width'], 1) . '×' . round($dimensions['height'], 1) . 'mm';
                                        ?>
                                    </small>
                                </div>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="canvas-modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #dee2e6; padding-top: 15px; margin-top: 25px;">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="dimensions">Sauvegarder</button>
            </div>
        </div>
    </div>
</div>

<div id="canvas-apparence-modal" class="canvas-modal" style="display: none;">
    <div class="canvas-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; display: flex; align-items: center; justify-content: center;">
        <div class="canvas-modal-content" style="background: white; border-radius: 12px; padding: 30px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
            <div class="canvas-modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid #dee2e6; padding-bottom: 15px;">
                <h3 style="margin: 0; color: #495057;">🎨 Apparence</h3>
                <button type="button" class="canvas-modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">&times;</button>
            </div>
            <div class="canvas-modal-body">
                <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #007cba;">
                    <p style="margin: 0; font-size: 14px; color: #495057; line-height: 1.5;">
                        <strong>💡 Comment ça marche :</strong> Ces paramètres contrôlent l'apparence visuelle du canvas de conception et de l'interface d'édition. 
                        Personnalisez les couleurs et les effets pour un meilleur confort de travail.
                    </p>
                </div>
<?php
/**
 * Paramètres canvas pour les modales
 * Définit les valeurs par défaut depuis les options séparées (synchronisées)
 */

// Les modales lisent depuis les options séparées pour cohérence
?>

                <form id="canvas-apparence-form">
                    <h4 style="margin-top: 0; color: #495057; border-bottom: 1px solid #dee2e6; padding-bottom: 8px;">🎨 Canvas</h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_bg_color">Couleur de fond du canvas</label></th>
                            <td>
                                <input type="color" id="canvas_bg_color" name="canvas_bg_color" value="<?php echo esc_attr(get_option('pdf_builder_canvas_bg_color', '#ffffff')); ?>" />
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Couleur d'arrière-plan de la zone de conception</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_border_color">Couleur des bordures</label></th>
                            <td>
                                <input type="color" id="canvas_border_color" name="canvas_border_color" value="<?php echo esc_attr(get_option('pdf_builder_canvas_border_color', '#cccccc')); ?>" />
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Couleur des bordures autour du canvas</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_border_width">Épaisseur des bordures (px)</label></th>
                            <td>
                                <input type="number" id="canvas_border_width" name="canvas_border_width" value="<?php echo intval(get_option('pdf_builder_canvas_border_width', 1)); ?>" min="0" max="10" />
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Épaisseur des bordures en pixels (0 = aucune)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_shadow_enabled">Ombre activée</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_shadow_enabled" name="canvas_shadow_enabled" value="1" <?php checked(get_option('pdf_builder_canvas_shadow_enabled', '0'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Ajoute une ombre portée au canvas</p>
                            </td>
                        </tr>
                    </table>
                    
                    <h4 style="margin-top: 25px; color: #495057; border-bottom: 1px solid #dee2e6; padding-bottom: 8px;">📦 Éditeur</h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_container_bg_color">Arrière-plan de l'éditeur</label></th>
                            <td>
                                <input type="color" id="canvas_container_bg_color" name="canvas_container_bg_color" value="<?php echo esc_attr(get_option('pdf_builder_canvas_container_bg_color', '#f8f9fa')); ?>" />
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Couleur de fond de l'interface d'édition</p>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="canvas-modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #dee2e6; padding-top: 15px; margin-top: 25px;">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="apparence">Sauvegarder</button>
            </div>
        </div>
    </div>
</div>

<div id="canvas-grille-modal" class="canvas-modal" style="display: none;">
    <div class="canvas-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; display: flex; align-items: center; justify-content: center;">
        <div class="canvas-modal-content" style="background: white; border-radius: 12px; padding: 30px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
            <div class="canvas-modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid #dee2e6; padding-bottom: 15px;">
                <h3 style="margin: 0; color: #495057;">📏 Grille & Guides</h3>
                <button type="button" class="canvas-modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">&times;</button>
            </div>
            <div class="canvas-modal-body">
                <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #007cba;">
                    <p style="margin: 0; font-size: 14px; color: #495057; line-height: 1.5;">
                        <strong>💡 Comment ça marche :</strong> Activez la grille pour afficher un quadrillage sur le canvas. 
                        Les éléments s'aligneront automatiquement sur les lignes de la grille si l'accrochage est activé.
                    </p>
                </div>
                <form id="canvas-grille-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_guides_enabled">Guides activés</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_guides_enabled" name="canvas_guides_enabled" value="1" <?php checked(get_option('pdf_builder_canvas_guides_enabled', '1'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Affiche des guides d'alignement temporaires</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_grid_enabled">Grille activée</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_grid_enabled" name="canvas_grid_enabled" value="1" <?php checked(get_option('pdf_builder_canvas_grid_enabled', '1'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Affiche/masque le quadrillage sur le canvas</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_grid_size">Taille de la grille (px)</label></th>
                            <td>
                                <input type="number" id="canvas_grid_size" name="canvas_grid_size" value="<?php echo intval(get_option('pdf_builder_canvas_grid_size', 20)); ?>" min="5" max="100" <?php echo get_option('pdf_builder_canvas_grid_enabled', '1') !== '1' ? 'disabled' : ''; ?> />
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Distance entre les lignes de la grille (5-100px)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_snap_to_grid">Accrochage à la grille</label></th>
                            <td>
                                <label class="toggle-switch <?php echo get_option('pdf_builder_canvas_grid_enabled', '1') !== '1' ? 'disabled' : ''; ?>">
                                    <input type="checkbox" id="canvas_snap_to_grid" name="canvas_snap_to_grid" value="1" <?php checked(get_option('pdf_builder_canvas_snap_to_grid', '1'), '1'); ?> <?php echo get_option('pdf_builder_canvas_grid_enabled', '1') !== '1' ? 'disabled' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Les éléments s'alignent automatiquement sur la grille</p>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="canvas-modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #dee2e6; padding-top: 15px; margin-top: 25px;">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="grille">Sauvegarder</button>
            </div>
        </div>
    </div>
</div>

<div id="canvas-zoom-modal" class="canvas-modal" style="display: none;">
    <div class="canvas-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; display: flex; align-items: center; justify-content: center;">
        <div class="canvas-modal-content" style="background: white; border-radius: 12px; padding: 30px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
            <div class="canvas-modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid #dee2e6; padding-bottom: 15px;">
                <h3 style="margin: 0; color: #495057;">🔍 Zoom & Navigation</h3>
                <button type="button" class="canvas-modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">&times;</button>
            </div>
            <div class="canvas-modal-body">
                <form id="canvas-zoom-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_pan_enabled">Navigation activée</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_pan_enabled" name="canvas_pan_enabled" value="1" <?php checked(get_option('pdf_builder_canvas_pan_enabled', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_zoom_min">Zoom minimum (%)</label></th>
                            <td>
                                <input type="number" id="canvas_zoom_min" name="canvas_zoom_min" value="<?php echo intval(get_option('pdf_builder_canvas_zoom_min', 10)); ?>" min="5" max="50" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_zoom_max">Zoom maximum (%)</label></th>
                            <td>
                                <input type="number" id="canvas_zoom_max" name="canvas_zoom_max" value="<?php echo intval(get_option('pdf_builder_canvas_zoom_max', 500)); ?>" min="100" max="1000" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_zoom_default">Zoom par défaut (%)</label></th>
                            <td>
                                <input type="number" id="canvas_zoom_default" name="canvas_zoom_default" value="<?php echo intval(get_option('pdf_builder_canvas_zoom_default', 100)); ?>" min="10" max="500" />
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="canvas-modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #dee2e6; padding-top: 15px; margin-top: 25px;">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="zoom">Sauvegarder</button>
            </div>
        </div>
    </div>
</div>

<div id="canvas-interaction-modal" class="canvas-modal" style="display: none;">
    <div class="canvas-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; display: flex; align-items: center; justify-content: center;">
        <div class="canvas-modal-content" style="background: white; border-radius: 12px; padding: 30px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
            <div class="canvas-modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid #dee2e6; padding-bottom: 15px;">
                <h3 style="margin: 0; color: #495057;">👆 Éléments Interactifs</h3>
                <button type="button" class="canvas-modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">&times;</button>
            </div>
            <div class="canvas-modal-body">
                <form id="canvas-interaction-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_drag_enabled">Glisser-déposer activé</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_drag_enabled" name="canvas_drag_enabled" value="1" <?php checked(get_option('pdf_builder_canvas_drag_enabled', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_resize_enabled">Redimensionnement activé</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_resize_enabled" name="canvas_resize_enabled" value="1" <?php checked(get_option('pdf_builder_canvas_resize_enabled', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_rotate_enabled">Rotation activée</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_rotate_enabled" name="canvas_rotate_enabled" value="1" <?php checked(get_option('pdf_builder_canvas_rotate_enabled', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_multi_select">Sélection multiple</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_multi_select" name="canvas_multi_select" value="1" <?php checked(get_option('pdf_builder_canvas_multi_select', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="canvas-modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #dee2e6; padding-top: 15px; margin-top: 25px;">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="interaction">Sauvegarder</button>
            </div>
        </div>
    </div>
</div>

<div id="canvas-comportement-modal" class="canvas-modal" style="display: none;">
    <div class="canvas-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; display: flex; align-items: center; justify-content: center;">
        <div class="canvas-modal-content" style="background: white; border-radius: 12px; padding: 30px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
            <div class="canvas-modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid #dee2e6; padding-bottom: 15px;">
                <h3 style="margin: 0; color: #495057;">⚙️ Comportement</h3>
                <button type="button" class="canvas-modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">&times;</button>
            </div>
            <div class="canvas-modal-body">
                <form id="canvas-comportement-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_selection_mode">Mode de sélection</label></th>
                            <td>
                                <select id="canvas_selection_mode" name="canvas_selection_mode">
                                    <option value="click" <?php selected(get_option('pdf_builder_canvas_selection_mode', 'click'), 'click'); ?>>Clic simple</option>
                                    <option value="lasso" <?php selected(get_option('pdf_builder_canvas_selection_mode', 'click'), 'lasso'); ?>>Lasso</option>
                                    <option value="rectangle" <?php selected(get_option('pdf_builder_canvas_selection_mode', 'click'), 'rectangle'); ?>>Rectangle</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_keyboard_shortcuts">Raccourcis clavier</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_keyboard_shortcuts" name="canvas_keyboard_shortcuts" value="1" <?php checked(get_option('pdf_builder_canvas_keyboard_shortcuts', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_auto_save">Sauvegarde automatique</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_auto_save" name="canvas_auto_save" value="1" <?php checked(get_option('pdf_builder_canvas_auto_save', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="canvas-modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #dee2e6; padding-top: 15px; margin-top: 25px;">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="comportement">Sauvegarder</button>
            </div>
        </div>
    </div>
</div>

<div id="canvas-export-modal" class="canvas-modal" style="display: none;">
    <div class="canvas-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; display: flex; align-items: center; justify-content: center;">
        <div class="canvas-modal-content" style="background: white; border-radius: 12px; padding: 30px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
            <div class="canvas-modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid #dee2e6; padding-bottom: 15px;">
                <h3 style="margin: 0; color: #495057;">📤 Export & Qualité</h3>
                <button type="button" class="canvas-modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">&times;</button>
            </div>
            <div class="canvas-modal-body">
                <form id="canvas-export-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_export_format">Format d'export par défaut</label></th>
                            <td>
                                <select id="canvas_export_format" name="canvas_export_format">
                                    <option value="png" <?php selected(get_option('pdf_builder_canvas_export_format', 'png'), 'png'); ?>>PNG</option>
                                    <option value="jpg" <?php selected(get_option('pdf_builder_canvas_export_format', 'png'), 'jpg'); ?>>JPG</option>
                                    <option value="svg" <?php selected(get_option('pdf_builder_canvas_export_format', 'png'), 'svg'); ?>>SVG</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_export_quality">Qualité d'export (%)</label></th>
                            <td>
                                <input type="number" id="canvas_export_quality" name="canvas_export_quality" value="<?php echo intval(get_option('pdf_builder_canvas_export_quality', 90)); ?>" min="1" max="100" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_export_transparent">Fond transparent</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_export_transparent" name="canvas_export_transparent" value="1" <?php checked(get_option('pdf_builder_canvas_export_transparent', false)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="canvas-modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #dee2e6; padding-top: 15px; margin-top: 25px;">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="export">Sauvegarder</button>
            </div>
        </div>
    </div>
</div>

<div id="canvas-performance-modal" class="canvas-modal" style="display: none;">
    <div class="canvas-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; display: flex; align-items: center; justify-content: center;">
        <div class="canvas-modal-content" style="background: white; border-radius: 12px; padding: 30px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
            <div class="canvas-modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid #dee2e6; padding-bottom: 15px;">
                <h3 style="margin: 0; color: #495057;">⚡ Performance</h3>
                <button type="button" class="canvas-modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">&times;</button>
            </div>
            <div class="canvas-modal-body">
                <form id="canvas-performance-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_fps_target">FPS cible</label></th>
                            <td>
                                <input type="number" id="canvas_fps_target" name="canvas_fps_target" value="<?php echo intval(get_option('pdf_builder_canvas_fps_target', 60)); ?>" min="10" max="120" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_memory_limit">Limite mémoire (MB)</label></th>
                            <td>
                                <input type="number" id="canvas_memory_limit" name="canvas_memory_limit" value="<?php echo intval(get_option('pdf_builder_canvas_memory_limit', 256)); ?>" min="64" max="1024" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_lazy_loading">Chargement paresseux</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_lazy_loading" name="canvas_lazy_loading" value="1" <?php checked(get_option('pdf_builder_canvas_lazy_loading', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="canvas-modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #dee2e6; padding-top: 15px; margin-top: 25px;">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="performance">Sauvegarder</button>
            </div>
        </div>
    </div>
</div>

<div id="canvas-autosave-modal" class="canvas-modal" style="display: none;">
    <div class="canvas-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; display: flex; align-items: center; justify-content: center;">
        <div class="canvas-modal-content" style="background: white; border-radius: 12px; padding: 30px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
            <div class="canvas-modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid #dee2e6; padding-bottom: 15px;">
                <h3 style="margin: 0; color: #495057;">💾 Sauvegarde Auto</h3>
                <button type="button" class="canvas-modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">&times;</button>
            </div>
            <div class="canvas-modal-body">
                <form id="canvas-autosave-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_autosave_enabled">Sauvegarde automatique activée</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_autosave_enabled" name="canvas_autosave_enabled" value="1" <?php checked(get_option('pdf_builder_canvas_autosave_enabled', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_autosave_interval">Intervalle (minutes)</label></th>
                            <td>
                                <input type="number" id="canvas_autosave_interval" name="canvas_autosave_interval" value="<?php echo intval(get_option('pdf_builder_canvas_autosave_interval', 5)); ?>" min="1" max="60" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_history_enabled">Historique activé</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_history_enabled" name="canvas_history_enabled" value="1" <?php checked(get_option('pdf_builder_canvas_history_enabled', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_history_max">Historique max (versions)</label></th>
                            <td>
                                <input type="number" id="canvas_history_max" name="canvas_history_max" value="<?php echo intval(get_option('pdf_builder_canvas_history_max', 50)); ?>" min="5" max="200" />
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="canvas-modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #dee2e6; padding-top: 15px; margin-top: 25px;">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="autosave">Sauvegarder</button>
            </div>
        </div>
    </div>
</div>

<div id="canvas-debug-modal" class="canvas-modal" style="display: none;">
    <div class="canvas-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; display: flex; align-items: center; justify-content: center;">
        <div class="canvas-modal-content" style="background: white; border-radius: 12px; padding: 30px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
            <div class="canvas-modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid #dee2e6; padding-bottom: 15px;">
                <h3 style="margin: 0; color: #495057;">🐛 Debug</h3>
                <button type="button" class="canvas-modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">&times;</button>
            </div>
            <div class="canvas-modal-body">
                <form id="canvas-debug-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_debug_enabled">Debug activé</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_debug_enabled" name="canvas_debug_enabled" value="1" <?php checked(get_option('pdf_builder_canvas_debug_enabled', false)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_performance_monitoring">Monitoring performance</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_performance_monitoring" name="canvas_performance_monitoring" value="1" <?php checked(get_option('pdf_builder_canvas_performance_monitoring', false)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_error_reporting">Rapport d'erreurs</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_error_reporting" name="canvas_error_reporting" value="1" <?php checked(get_option('pdf_builder_canvas_error_reporting', false)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="canvas-modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #dee2e6; padding-top: 15px; margin-top: 25px;">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="debug">Sauvegarder</button>
            </div>
        </div>
    </div>
</div>