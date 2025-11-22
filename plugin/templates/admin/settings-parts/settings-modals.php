<?php // Modal components - Updated: 2025-11-18 20:20:00 ?>

<!-- Canvas Configuration Modals Dimensions & Format -->
<div id="canvas-dimensions-modal" class="canvas-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 999999; display: flex; align-items: center; justify-content: center;">
    <div class="canvas-modal-content" style="pointer-events: auto; background: white; border-radius: 8px; max-width: 600px; width: 90%; max-height: 90%; overflow-y: auto;">
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
            <div class="canvas-modal-footer">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="canvas-modal-save" data-category="dimensions" style="pointer-events: auto; cursor: pointer; display: block !important;">Sauvegarder</button>Sauvegarder</button>
            </div>
        </div>
</div>
<!-- Canvas Configuration Modals Zoom & Navigation -->
<div id="canvas-zoom-modal" class="canvas-modal" style="display: none;">
    <div class="canvas-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; display: flex; align-items: center; justify-content: center;">
        <div class="canvas-modal-content">
            <div class="canvas-modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid #dee2e6; padding-bottom: 15px;">
                <h3 style="margin: 0; color: #495057;">🔍 Zoom</h3>
                <button type="button" class="canvas-modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">&times;</button>
            </div>
            <div class="canvas-modal-body">
                <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #007cba;">
                    <p style="margin: 0; font-size: 14px; color: #495057; line-height: 1.5;">
                        <strong>💡 Comment ça marche :</strong> Contrôlez les niveaux de zoom et les options de navigation du canvas.
                        Le zoom avec la molette de souris peut être activé/désactivé, et la navigation au clavier permet de se déplacer dans le canvas.
                    </p>
                </div>
                <form id="zoom-form">
                    <h4 style="margin-top: 0; color: #495057; border-bottom: 1px solid #dee2e6; padding-bottom: 8px;">🔍 Zoom</h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="zoom_min">Zoom minimum (%)</label></th>
                            <td>
                                <input type="number" id="zoom_min" name="canvas_zoom_min" value="<?php echo intval(get_option('pdf_builder_canvas_zoom_min', 10)); ?>" min="1" max="100" />
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Niveau de zoom minimum autorisé</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="zoom_max">Zoom maximum (%)</label></th>
                            <td>
                                <input type="number" id="zoom_max" name="canvas_zoom_max" value="<?php echo intval(get_option('pdf_builder_canvas_zoom_max', 500)); ?>" min="100" max="1000" />
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Niveau de zoom maximum autorisé</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="zoom_default">Zoom par défaut (%)</label></th>
                            <td>
                                <input type="number" id="zoom_default" name="canvas_zoom_default" value="<?php echo intval(get_option('pdf_builder_canvas_zoom_default', 100)); ?>" min="10" max="500" />
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Niveau de zoom au chargement du canvas</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="zoom_step">Pas de zoom (%)</label></th>
                            <td>
                                <input type="number" id="zoom_step" name="canvas_zoom_step" value="<?php echo intval(get_option('pdf_builder_canvas_zoom_step', 25)); ?>" min="5" max="50" />
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Incrément de zoom par étape</p>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="canvas-modal-footer">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="zoom">Sauvegarder</button>
            </div>
        </div>
    </div>
</div>
<!-- Canvas Configuration Modals Apparence -->
<div id="canvas-apparence-modal" class="canvas-modal" style="display: none;">
    <div class="canvas-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; display: flex; align-items: center; justify-content: center;">
        <div class="canvas-modal-content">
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
            <div class="canvas-modal-footer">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="apparence">Sauvegarder</button>
            </div>
        </div>
    </div>
</div>
<!-- Canvas Configuration Modals Grille & Guides -->
<div id="canvas-grille-modal" class="canvas-modal" style="display: none;">
    <div class="canvas-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; display: flex; align-items: center; justify-content: center;">
        <div class="canvas-modal-content">
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
            <div class="canvas-modal-footer">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="grille">Sauvegarder</button>
            </div>
        </div>
    </div>
</div>
<!-- Canvas Configuration Modals Interactions & Comportement-->
<div id="canvas-interactions-modal" class="canvas-modal" style="display: none;">
    <div class="canvas-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; display: flex; align-items: center; justify-content: center;">
        <div class="canvas-modal-content">
            <div class="canvas-modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid #dee2e6; padding-bottom: 15px;">
                <h3 style="margin: 0; color: #495057;">🎯 Interactions & Comportement</h3>
                <button type="button" class="canvas-modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">&times;</button>
            </div>
            <div class="canvas-modal-body">
                <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #007cba;">
                    <p style="margin: 0; font-size: 14px; color: #495057; line-height: 1.5;">
                        <strong>💡 Comment ça marche :</strong> Ces paramètres contrôlent les interactions disponibles sur le canvas pour manipuler les éléments,
                        ainsi que le comportement général de sélection et les raccourcis clavier.
                    </p>
                </div>
                <form id="canvas-interactions-form">
                    <h4 style="margin-top: 0; color: #495057; border-bottom: 1px solid #dee2e6; padding-bottom: 8px;">🎯 Interactions</h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_drag_enabled">Glisser-déposer activé</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_drag_enabled" name="canvas_drag_enabled" value="1" <?php checked(get_option('pdf_builder_canvas_drag_enabled', '1'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Permet de déplacer les éléments sur le canvas</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_resize_enabled">Redimensionnement activé</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_resize_enabled" name="canvas_resize_enabled" value="1" <?php checked(get_option('pdf_builder_canvas_resize_enabled', '1'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Affiche les poignées pour redimensionner les éléments</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_rotate_enabled">Rotation activée</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_rotate_enabled" name="canvas_rotate_enabled" value="1" <?php checked(get_option('pdf_builder_canvas_rotate_enabled', '1'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Permet de faire pivoter les éléments avec la souris</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_multi_select">Sélection multiple</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_multi_select" name="canvas_multi_select" value="1" <?php checked(get_option('pdf_builder_canvas_multi_select', '1'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Ctrl+Clic pour sélectionner plusieurs éléments</p>
                            </td>
                        </tr>
                    </table>

                    <h4 style="margin-top: 25px; color: #495057; border-bottom: 1px solid #dee2e6; padding-bottom: 8px;">⚙️ Comportement</h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_selection_mode">Mode de sélection</label></th>
                            <td>
                                <select id="canvas_selection_mode" name="canvas_selection_mode">
                                    <option value="click" <?php selected(get_option('pdf_builder_canvas_selection_mode', 'click'), 'click'); ?>>Clic simple</option>
                                    <option value="lasso" <?php selected(get_option('pdf_builder_canvas_selection_mode', 'click'), 'lasso'); ?>>Lasso</option>
                                    <option value="rectangle" <?php selected(get_option('pdf_builder_canvas_selection_mode', 'click'), 'rectangle'); ?>>Rectangle</option>
                                </select>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Méthode de sélection des éléments sur le canvas</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_keyboard_shortcuts">Raccourcis clavier</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_keyboard_shortcuts" name="canvas_keyboard_shortcuts" value="1" <?php checked(get_option('pdf_builder_canvas_keyboard_shortcuts', '1'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Active les raccourcis clavier (Ctrl+Z, Ctrl+Y, etc.)</p>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="canvas-modal-footer">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="interactions">Sauvegarder</button>
            </div>
        </div>
    </div>
</div>
<!-- Canvas Configuration Modals Export & Qualité -->
<div id="canvas-export-modal" class="canvas-modal" style="display: none;">
    <div class="canvas-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; display: flex; align-items: center; justify-content: center;">
        <div class="canvas-modal-content">
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
                                    <input type="checkbox" id="canvas_export_transparent" name="canvas_export_transparent" value="1" <?php checked(get_option('pdf_builder_canvas_export_transparent', '0'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="canvas-modal-footer">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="export">Sauvegarder</button>
            </div>
        </div>
    </div>
</div>
<!-- Canvas Configuration Modals Performance -->
<div id="canvas-performance-modal" class="canvas-modal" style="display: none;">
    <div class="canvas-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; display: flex; align-items: center; justify-content: center;">
        <div class="canvas-modal-content">
            <div class="canvas-modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid #dee2e6; padding-bottom: 15px;">
                <h3 style="margin: 0; color: #495057;">⚡ Performance</h3>
                <button type="button" class="canvas-modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">&times;</button>
            </div>
            <div class="canvas-modal-body">
                <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #007cba;">
                    <p style="margin: 0; font-size: 14px; color: #495057; line-height: 1.5;">
                        <strong>💡 Optimisation :</strong> Ces paramètres améliorent les performances de l'éditeur et du plugin pour une expérience plus fluide.
                    </p>
                </div>
                <form id="canvas-performance-form">
                    <!-- Section Éditeur PDF -->
                    <h4 style="margin: 25px 0 15px 0; padding-bottom: 8px; border-bottom: 1px solid #dee2e6; color: #495057;">
                        <span style="display: inline-flex; align-items: center; gap: 8px;">
                            🎨 Éditeur PDF
                        </span>
                    </h4>
                    <p style="color: #666; margin-bottom: 15px; font-size: 13px;">Paramètres de performance pour l'interface de conception</p>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_fps_target">Cible FPS</label></th>
                            <td>
                                <select id="canvas_fps_target" name="canvas_fps_target">
                                    <option value="30" <?php selected(get_option('pdf_builder_canvas_fps_target', 60), 30); ?>>30 FPS (Économie)</option>
                                    <option value="60" <?php selected(get_option('pdf_builder_canvas_fps_target', 60), 60); ?>>60 FPS (Standard)</option>
                                    <option value="120" <?php selected(get_option('pdf_builder_canvas_fps_target', 60), 120); ?>>120 FPS (Haute performance)</option>
                                </select>
                                <div id="fps_preview" style="margin-top: 5px; padding: 5px; background: #f8f9fa; border-radius: 3px; font-size: 12px; color: #666;">
                                    FPS actuel : <span id="current_fps_value"><?php echo intval(get_option('pdf_builder_canvas_fps_target', 60)); ?></span>
                                </div>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Fluidité du rendu canvas (plus élevé = plus de ressources)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_memory_limit_js">Limite mémoire JavaScript</label></th>
                            <td>
                                <select id="canvas_memory_limit_js" name="canvas_memory_limit_js">
                                    <option value="128" <?php selected(get_option('pdf_builder_canvas_memory_limit_js', '256'), '128'); ?>>128 MB</option>
                                    <option value="256" <?php selected(get_option('pdf_builder_canvas_memory_limit_js', '256'), '256'); ?>>256 MB</option>
                                    <option value="512" <?php selected(get_option('pdf_builder_canvas_memory_limit_js', '256'), '512'); ?>>512 MB</option>
                                    <option value="1024" <?php selected(get_option('pdf_builder_canvas_memory_limit_js', '256'), '1024'); ?>>1 GB</option>
                                </select>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Mémoire allouée au canvas et aux éléments</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_lazy_loading_editor">Chargement paresseux (Éditeur)</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_lazy_loading_editor" name="canvas_lazy_loading_editor" value="1" <?php checked(get_option('pdf_builder_canvas_lazy_loading_editor', '1'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Charge les éléments seulement quand visibles</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_preload_critical">Préchargement ressources critiques</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_preload_critical" name="canvas_preload_critical" value="1" <?php checked(get_option('pdf_builder_canvas_preload_critical', '1'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Précharge les polices et outils essentiels</p>
                            </td>
                        </tr>
                    </table>

                    <!-- Section Plugin WordPress -->
                    <h4 style="margin: 35px 0 15px 0; padding-bottom: 8px; border-bottom: 1px solid #dee2e6; color: #495057;">
                        <span style="display: inline-flex; align-items: center; gap: 8px;">
                            🔧 Plugin WordPress
                        </span>
                    </h4>
                    <p style="color: #666; margin-bottom: 15px; font-size: 13px;">Paramètres de performance pour le backend et génération PDF</p>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_memory_limit_php">Limite mémoire PHP</label></th>
                            <td>
                                <select id="canvas_memory_limit_php" name="canvas_memory_limit_php">
                                    <option value="128" <?php selected(get_option('pdf_builder_canvas_memory_limit_php', '256'), '128'); ?>>128 MB</option>
                                    <option value="256" <?php selected(get_option('pdf_builder_canvas_memory_limit_php', '256'), '256'); ?>>256 MB</option>
                                    <option value="512" <?php selected(get_option('pdf_builder_canvas_memory_limit_php', '256'), '512'); ?>>512 MB</option>
                                    <option value="1024" <?php selected(get_option('pdf_builder_canvas_memory_limit_php', '256'), '1024'); ?>>1 GB</option>
                                </select>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Mémoire pour génération PDF et traitement</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_response_timeout">Timeout réponses AJAX</label></th>
                            <td>
                                <select id="canvas_response_timeout" name="canvas_response_timeout">
                                    <option value="10" <?php selected(get_option('pdf_builder_canvas_response_timeout', '30'), '10'); ?>>10 secondes</option>
                                    <option value="30" <?php selected(get_option('pdf_builder_canvas_response_timeout', '30'), '30'); ?>>30 secondes</option>
                                    <option value="60" <?php selected(get_option('pdf_builder_canvas_response_timeout', '30'), '60'); ?>>60 secondes</option>
                                    <option value="120" <?php selected(get_option('pdf_builder_canvas_response_timeout', '30'), '120'); ?>>120 secondes</option>
                                </select>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Délai maximum pour les requêtes serveur</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_lazy_loading_plugin">Chargement paresseux (Plugin)</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_lazy_loading_plugin" name="canvas_lazy_loading_plugin" value="1" <?php checked(get_option('pdf_builder_canvas_lazy_loading_plugin', '1'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">Charge les données seulement quand nécessaire</p>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="canvas-modal-footer">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="performance">Sauvegarder</button>
            </div>
        </div>
    </div>
</div>
<!-- Canvas Configuration Modals Sauvegarde Auto -->
<div id="canvas-autosave-modal" class="canvas-modal" style="display: none;">
    <div class="canvas-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; display: flex; align-items: center; justify-content: center;">
        <div class="canvas-modal-content">
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
                                    <input type="checkbox" id="canvas_autosave_enabled" name="canvas_autosave_enabled" value="1" <?php checked(get_option('pdf_builder_canvas_autosave_enabled', '1'), '1'); ?>>
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
                                    <input type="checkbox" id="canvas_history_enabled" name="canvas_history_enabled" value="1" <?php checked(get_option('pdf_builder_canvas_history_enabled', '1'), '1'); ?>>
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
            <div class="canvas-modal-footer">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="autosave">Sauvegarder</button>
            </div>
        </div>
    </div>
</div>
<!-- Canvas Configuration Modals Debug -->
<div id="canvas-debug-modal" class="canvas-modal" style="display: none;">
    <div class="canvas-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; display: flex; align-items: center; justify-content: center;">
        <div class="canvas-modal-content">
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
                                    <input type="checkbox" id="canvas_debug_enabled" name="canvas_debug_enabled" value="1" <?php checked(get_option('pdf_builder_canvas_debug_enabled', '0'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_performance_monitoring">Monitoring performance</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_performance_monitoring" name="canvas_performance_monitoring" value="1" <?php checked(get_option('pdf_builder_canvas_performance_monitoring', '0'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_error_reporting">Rapport d'erreurs</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_error_reporting" name="canvas_error_reporting" value="1" <?php checked(get_option('pdf_builder_canvas_error_reporting', '0'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="canvas-modal-footer">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="performance">Sauvegarder</button>
            </div>
        </div>
    </div>
</div>

<script>
// Preview FPS en temps réel
document.addEventListener('DOMContentLoaded', function() {
    const fpsSelect = document.getElementById('canvas_fps_target');
    const fpsValue = document.getElementById('current_fps_value');

    if (fpsSelect && fpsValue) {
        fpsSelect.addEventListener('change', function() {
            fpsValue.textContent = this.value;
            fpsValue.style.color = this.value >= 60 ? '#28a745' : this.value >= 30 ? '#ffc107' : '#dc3545';
        });
    }
});
</script>