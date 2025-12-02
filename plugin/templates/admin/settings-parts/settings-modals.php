<!-- Cache Metrics Modals -->
<!-- Cache Size Details Modal -->
<div id="cache-size-modal" class="cache-modal" data-category="size">
    <div class="cache-modal-overlay">
        <section class="cache-modal-content">
            <header class="cache-modal-header">
                <h3>[DETAILS CACHE] Détails de la taille du cache</h3>
                <button type="button" class="cache-modal-close">&times;</button>
            </header>
            <main class="cache-modal-body">
                <aside class="cache-modal-info">
                    <p>
                        <strong>ℹ️ Informations sur la taille du cache :</strong> Cette section affiche la taille totale des fichiers en cache du plugin PDF Builder.
                        Le cache inclut les aperçus PDF générés et les données temporaires.
                    </p>
                </aside>
                <article id="cache-size-details">
                    <section style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                        <article style="padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6;">
                            <h4 style="margin-top: 0; color: #495057;">[DOSSIER APERCUS] Dossier des aperçus</h4>
                            <div style="font-size: 18px; font-weight: bold; color: #28a745;" id="previews-cache-size">
                                Calcul en cours...
                            </div>
                            <div style="color: #666; font-size: 12px;">wp-content/cache/wp-pdf-builder-previews/</div>
                        </article>
                        <article style="padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6;">
                            <h4 style="margin-top: 0; color: #495057;">[DOSSIER PRINCIPAL] Dossier principal</h4>
                            <div style="font-size: 18px; font-weight: bold; color: #28a745;" id="main-cache-size">
                                Calcul en cours...
                            </div>
                            <div style="color: #666; font-size: 12px;">wp-content/uploads/pdf-builder-cache/</div>
                        </article>
                    </section>
                    <aside style="margin-top: 20px; padding: 15px; background: #e7f5e9; border: 1px solid #28a745; border-radius: 8px;">
                        <h4 style="margin-top: 0; color: #155724;">💡 Recommandations</h4>
                        <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #155724;">
                            <li>Une taille de cache normale est inférieure à 100 Mo</li>
                            <li>Si la taille dépasse 500 Mo, considérez un nettoyage manuel</li>
                            <li>Le cache est automatiquement nettoyé selon les paramètres configurés</li>
                        </ul>
                    </aside>
                </article>
            </main>
            <footer class="cache-modal-footer">
                <button type="button" class="button button-secondary cache-modal-cancel">Fermer</button>
                <button type="button" class="button button-primary" id="clear-cache-from-modal">🗑️ Vider le cache</button>
            </footer>
        </section>
    </div>
</div>

<!-- Cache Transients Details Modal -->
<div id="cache-transients-modal" class="cache-modal" data-category="transients">
    <div class="cache-modal-overlay">
        <section class="cache-modal-content">
            <header class="cache-modal-header">
                <h3>📊 Détails des transients actifs</h3>
                <button type="button" class="cache-modal-close">&times;</button>
            </header>
            <main class="cache-modal-body">
                <aside class="cache-modal-info">
                    <p>
                        <strong>ℹ️ Informations sur les transients :</strong> Les transients sont des données temporaires stockées dans la base de données WordPress.
                        Ils expirent automatiquement et améliorent les performances en évitant les recalculs.
                    </p>
                </aside>
                <article id="cache-transients-details">
                    <section style="margin-top: 20px;">
                        <article style="padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6;">
                            <h4 style="margin-top: 0; color: #495057;">📊 Statistiques des transients</h4>
                            <section style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-top: 15px;">
                                <article style="text-align: center;">
                                    <div style="font-size: 24px; font-weight: bold; color: #28a745;" id="total-transients-count">0</div>
                                    <div style="color: #666; font-size: 12px;">Total actifs</div>
                                </article>
                                <article style="text-align: center;">
                                    <div style="font-size: 24px; font-weight: bold; color: #17a2b8;" id="expired-transients-count">0</div>
                                    <div style="color: #666; font-size: 12px;">Expirés</div>
                                </article>
                                <article style="text-align: center;">
                                    <div style="font-size: 24px; font-weight: bold; color: #ffc107;" id="pdf-builder-transients-count">0</div>
                                    <div style="color: #666; font-size: 12px;">PDF Builder</div>
                                </article>
                            </section>
                        </article>
                        <aside style="margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #f39c12; border-radius: 8px;">
                            <h4 style="margin-top: 0; color: #8b4513;">⚠️ Note importante</h4>
                            <p style="margin: 10px 0 0 0; color: #5d4e37;">
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
        <section class="cache-modal-content">
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
                                    <input type="checkbox" id="modal_cache_enabled" name="cache_enabled" value="1" <?php checked(get_option('pdf_builder_cache_enabled', false)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Active/désactive le système de cache du plugin</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="modal_cache_compression">Compression du cache</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="modal_cache_compression" name="cache_compression" value="1" <?php checked(get_option('pdf_builder_cache_compression', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Compresser les données en cache pour économiser l'espace disque</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="modal_cache_auto_cleanup">Nettoyage automatique</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="modal_cache_auto_cleanup" name="cache_auto_cleanup" value="1" <?php checked(get_option('pdf_builder_cache_auto_cleanup', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Nettoyer automatiquement les anciens fichiers cache</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="modal_cache_max_size">Taille max du cache (MB)</label></th>
                            <td>
                                <input type="number" id="modal_cache_max_size" name="cache_max_size" value="<?php echo intval(get_option('pdf_builder_cache_max_size', 100)); ?>" min="10" max="1000" step="10" />
                                <p class="description">Taille maximale du dossier cache en mégaoctets</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="modal_cache_ttl">TTL du cache (secondes)</label></th>
                            <td>
                                <input type="number" id="modal_cache_ttl" name="cache_ttl" value="<?php echo intval(get_option('pdf_builder_cache_ttl', 3600)); ?>" min="0" max="86400" />
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
        <section class="cache-modal-content">
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
                            <article style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" id="cleanup_files" checked>
                                <label for="cleanup_files">Supprimer les fichiers cache obsolètes</label>
                            </article>
                            <article style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" id="cleanup_transients" checked>
                                <label for="cleanup_transients">Vider les transients expirés</label>
                            </article>
                            <article style="display: flex; align-items: center; gap: 10px;">
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
<div id="canvas-dimensions-modal" class="canvas-modal" data-category="dimensions">
    <div class="canvas-modal-overlay">
        <section class="canvas-modal-content">
            <header class="canvas-modal-header">
                <h3>📏 Dimensions & Format</h3>
                <button type="button" class="canvas-modal-close">&times;</button>
            </header>
            <main class="canvas-modal-body">
                <aside class="canvas-modal-info">
                    <p>
                        <strong>ℹ️ Comment ça marche :</strong> Ces paramètres définissent la taille, l'orientation et la qualité du document PDF généré. 
                        Le format A4 est actuellement supporté, d'autres formats arrivent bientôt.
                    </p>
                </aside>
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
                                <p class="canvas-modal-description">Taille standard du document PDF (A4 disponible)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label>Orientation</label></th>
                            <td>
                                <aside style="background: #f0f8ff; border: 1px solid #b3d9ff; border-radius: 4px; padding: 10px; margin: 5px 0;">
                                    <strong>📄 Portrait uniquement (v1.0)</strong><br>
                                    <small style="color: #666;">
                                        L'orientation paysage sera disponible dans la version 2.0 avec recalcul automatique des dimensions.
                                        Actuellement, tous les documents sont générés en format portrait pour garantir la stabilité.
                                    </small>
                                </aside>
                                <p class="canvas-modal-description">Orientation fixée en portrait pour la v1.0</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_dpi">Résolution DPI</label></th>
                            <td>
                                <select id="canvas_dpi" name="canvas_dpi">
                                    <option value="72" <?php selected(get_option('pdf_builder_canvas_dpi', 96), '72'); ?>>72 DPI (Web)</option>
                                    <option value="96" <?php selected(get_option('pdf_builder_canvas_dpi', 96), '96'); ?>>96 DPI (Écran)</option>
                                    <option value="150" <?php selected(get_option('pdf_builder_canvas_dpi', 96), '150'); ?>>150 DPI (Impression)</option>
                                    <option value="300" <?php selected(get_option('pdf_builder_canvas_dpi', 96), '300'); ?>>300 DPI (Haute qualité)</option>
                                </select>
                                <p class="canvas-modal-description">Qualité d'impression (plus élevé = meilleure qualité)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label>Dimensions calculées</label></th>
                            <td>
                                <aside id="canvas-dimensions-display" class="canvas-modal-display">
                                    <span id="canvas-width-display"><?php echo intval(get_option('pdf_builder_canvas_width', 794)); ?></span> ×
                                    <span id="canvas-height-display"><?php echo intval(get_option('pdf_builder_canvas_height', 1123)); ?></span> px
                                    <br>
                                    <small id="canvas-mm-display">
                                        <?php
                                        $format = get_option('pdf_builder_canvas_format', 'A4');
                                        $orientation = 'portrait'; // FORCÉ EN PORTRAIT - v2.0
                                        
                                        // Utiliser les dimensions standard centralisées
                                        $formatDimensionsMM = \PDF_Builder\PAPER_FORMATS;
                                        
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
                <button type="button" class="button button-primary canvas-modal-save" data-category="dimensions">Sauvegarder</button>
            </footer>
        </section>
    </div>
</div>
<!-- Canvas Configuration Modals Zoom & Navigation -->
<div id="canvas-zoom-modal" class="canvas-modal" data-category="zoom">
    <div class="canvas-modal-overlay">
        <section class="canvas-modal-content">
            <header class="canvas-modal-header">
                <h3>🔍 Zoom</h3>
                <button type="button" class="canvas-modal-close">&times;</button>
            </header>
            <main class="canvas-modal-body">
                <aside class="canvas-modal-info">
                    <p>
                        <strong>ℹ️ Comment ça marche :</strong> Contrôlez les niveaux de zoom et les options de navigation du canvas.
                        Le zoom avec la molette de souris peut être activé/désactivé, et la navigation au clavier permet de se déplacer dans le canvas.
                    </p>
                </aside>
                <form id="zoom-form">
                    <section>
                        <header>
                            <h4 class="canvas-modal-section-title">🔍 Zoom</h4>
                        </header>
                        <table class="form-table">
                        <tr>
                            <th scope="row"><label for="zoom_min">Zoom minimum (%)</label></th>
                            <td>
                                <input type="number" id="zoom_min" name="canvas_zoom_min" value="<?php echo intval(get_option('pdf_builder_canvas_zoom_min', 10)); ?>" min="1" max="100" />
                                <p class="canvas-modal-description">Niveau de zoom minimum autorisé</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="zoom_max">Zoom maximum (%)</label></th>
                            <td>
                                <input type="number" id="zoom_max" name="canvas_zoom_max" value="<?php echo intval(get_option('pdf_builder_canvas_zoom_max', 500)); ?>" min="100" max="1000" />
                                <p class="canvas-modal-description">Niveau de zoom maximum autorisé</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="zoom_default">Zoom par défaut (%)</label></th>
                            <td>
                                <input type="number" id="zoom_default" name="canvas_zoom_default" value="<?php echo intval(get_option('pdf_builder_canvas_zoom_default', 100)); ?>" min="10" max="500" />
                                <p class="canvas-modal-description">Niveau de zoom au chargement du canvas</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="zoom_step">Pas de zoom (%)</label></th>
                            <td>
                                <input type="number" id="zoom_step" name="canvas_zoom_step" value="<?php echo intval(get_option('pdf_builder_canvas_zoom_step', 25)); ?>" min="5" max="50" />
                                <p class="canvas-modal-description">Incrément de zoom par étape</p>
                            </td>
                        </tr>
                    </table>
                    </section>
                </form>
            </main>
            <footer class="canvas-modal-footer">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="zoom">Sauvegarder</button>
            </footer>
        </section>
    </div>
</div>
<!-- Canvas Configuration Modals Apparence -->
<div id="canvas-apparence-modal" class="canvas-modal" data-category="apparence">
    <div class="canvas-modal-overlay">
        <section class="canvas-modal-content">
            <header class="canvas-modal-header">
                <h3 >🎨 Apparence</h3>
                <button type="button" class="canvas-modal-close">&times;</button>
            </header>
            <main class="canvas-modal-body">
                <aside class="canvas-modal-info">
                    <p >
                        <strong>ℹ️ Comment ça marche :</strong> Ces paramètres contrôlent l'apparence visuelle du canvas de conception et de l'interface d'édition. 
                        Personnalisez les couleurs et les effets pour un meilleur confort de travail.
                    </p>
                </aside>
                <?php
                /**
                 * Paramètres canvas pour les modales
                 * Définit les valeurs par défaut depuis les options séparées (synchronisées)
                 */

                // Les modales lisent depuis les options séparées pour cohérence
                ?>

                <form id="canvas-apparence-form">
                    <section>
                        <header>
                            <h4 class="canvas-modal-section-title">🎨 Canvas</h4>
                        </header>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="canvas_bg_color">Couleur de fond du canvas</label></th>
                                <td>
                                    <input type="color" id="canvas_bg_color" name="canvas_bg_color" value="<?php echo esc_attr(get_option('pdf_builder_canvas_bg_color', '#ffffff')); ?>" />
                                    <p class="canvas-modal-description">Couleur d'arrière-plan de la zone de conception</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="canvas_border_color">Couleur des bordures</label></th>
                                <td>
                                    <input type="color" id="canvas_border_color" name="canvas_border_color" value="<?php echo esc_attr(get_option('pdf_builder_canvas_border_color', '#cccccc')); ?>" />
                                    <p class="canvas-modal-description">Couleur des bordures autour du canvas</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="canvas_border_width">Épaisseur des bordures (px)</label></th>
                                <td>
                                    <input type="number" id="canvas_border_width" name="canvas_border_width" value="<?php echo intval(get_option('pdf_builder_canvas_border_width', 1)); ?>" min="0" max="10" />
                                    <p class="canvas-modal-description">Épaisseur des bordures en pixels (0 = aucune)</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="canvas_shadow_enabled">Ombre activée</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="canvas_shadow_enabled" name="canvas_shadow_enabled" value="1" <?php checked(get_option('pdf_builder_canvas_shadow_enabled', '0'), '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="canvas-modal-description">Ajoute une ombre portée au canvas</p>
                                </td>
                            </tr>
                        </table>
                    </section>
                    
                    <section>
                        <header>
                            <h4 class="canvas-modal-section-title spaced">✏️ Éditeur</h4>
                        </header>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="canvas_container_bg_color">Arrière-plan de l'éditeur</label></th>
                                <td>
                                    <input type="color" id="canvas_container_bg_color" name="canvas_container_bg_color" value="<?php echo esc_attr(get_option('pdf_builder_canvas_container_bg_color', '#f8f9fa')); ?>" />
                                    <p class="canvas-modal-description">Couleur de fond de l'interface d'édition</p>
                                </td>
                            </tr>
                        </table>
                    </section>
                </form>
            </main>
            <footer class="canvas-modal-footer">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="apparence">Sauvegarder</button>
            </footer>
        </section>
    </div>
</div>
<!-- Canvas Configuration Modals Grille & Guides -->
<div id="canvas-grille-modal" class="canvas-modal" data-category="grille">
    <div class="canvas-modal-overlay">
        <section class="canvas-modal-content">
            <header class="canvas-modal-header">
                <h3 >📐 Grille & Guides</h3>
                <button type="button" class="canvas-modal-close">&times;</button>
            </header>
            <main class="canvas-modal-body">
                <aside class="canvas-modal-info">
                    <p >
                        <strong>ℹ️ Comment ça marche :</strong> Activez la grille pour afficher un quadrillage sur le canvas. 
                        Les éléments s'aligneront automatiquement sur les lignes de la grille si l'accrochage est activé.
                    </p>
                </aside>
                <form id="canvas-grille-form">
                    <section>
                        <table class="form-table">
                        <tr>
                            <th scope="row"><label for="canvas_guides_enabled">Guides activés</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_guides_enabled" name="canvas_guides_enabled" value="1" <?php checked(get_option('pdf_builder_canvas_guides_enabled', '1'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="canvas-modal-description">Affiche des guides d'alignement temporaires</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_grid_enabled">Grille activée</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="canvas_grid_enabled" name="canvas_grid_enabled" value="1" <?php checked(get_option('pdf_builder_canvas_grid_enabled', '1'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="canvas-modal-description">Affiche/masque le quadrillage sur le canvas</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_grid_size">Taille de la grille (px)</label></th>
                            <td>
                                <input type="number" id="canvas_grid_size" name="canvas_grid_size" value="<?php echo intval(get_option('pdf_builder_canvas_grid_size', 20)); ?>" min="5" max="100" <?php echo get_option('pdf_builder_canvas_grid_enabled', '1') !== '1' ? 'disabled' : ''; ?> />
                                <p class="canvas-modal-description">Distance entre les lignes de la grille (5-100px)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="canvas_snap_to_grid">Accrochage à la grille</label></th>
                            <td>
                                <label class="toggle-switch <?php echo get_option('pdf_builder_canvas_grid_enabled', '1') !== '1' ? 'disabled' : ''; ?>">
                                    <input type="checkbox" id="canvas_snap_to_grid" name="canvas_snap_to_grid" value="1" <?php checked(get_option('pdf_builder_canvas_snap_to_grid', '1'), '1'); ?> <?php echo get_option('pdf_builder_canvas_grid_enabled', '1') !== '1' ? 'disabled' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="canvas-modal-description">Les éléments s'alignent automatiquement sur la grille</p>
                            </td>
                        </tr>
                    </table>
                    </section>
                </form>
            </main>
            <footer class="canvas-modal-footer">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="grille">Sauvegarder</button>
            </footer>
        </section>
    </div>
</div>
<!-- Canvas Configuration Modals Interactions & Comportement-->
<div id="canvas-interactions-modal" class="canvas-modal" data-category="interactions">
    <div class="canvas-modal-overlay">
        <section class="canvas-modal-content">
            <header class="canvas-modal-header">
                <h3 >🖱️ Interactions & Comportement</h3>
                <button type="button" class="canvas-modal-close">&times;</button>
            </header>
            <main class="canvas-modal-body">
                <aside class="canvas-modal-info">
                    <p >
                        <strong>ℹ️ Comment ça marche :</strong> Ces paramètres contrôlent les interactions disponibles sur le canvas pour manipuler les éléments,
                        ainsi que le comportement général de sélection et les raccourcis clavier.
                    </p>
                </aside>
                <form id="canvas-interactions-form">
                    <section>
                        <header>
                            <h4 class="canvas-modal-section-title">🖱️ Interactions</h4>
                        </header>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="canvas_drag_enabled">Glisser-déposer activé</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="canvas_drag_enabled" name="canvas_drag_enabled" value="1" <?php checked(get_option('pdf_builder_canvas_drag_enabled', '1'), '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="canvas-modal-description">Permet de déplacer les éléments sur le canvas</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="canvas_resize_enabled">Redimensionnement activé</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="canvas_resize_enabled" name="canvas_resize_enabled" value="1" <?php checked(get_option('pdf_builder_canvas_resize_enabled', '1'), '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="canvas-modal-description">Affiche les poignées pour redimensionner les éléments</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="canvas_rotate_enabled">Rotation activée</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="canvas_rotate_enabled" name="canvas_rotate_enabled" value="1" <?php checked(get_option('pdf_builder_canvas_rotate_enabled', '1'), '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="canvas-modal-description">Permet de faire pivoter les éléments avec la souris</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="canvas_multi_select">Sélection multiple</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="canvas_multi_select" name="canvas_multi_select" value="1" <?php checked(get_option('pdf_builder_canvas_multi_select', '1'), '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="canvas-modal-description">Ctrl+Clic pour sélectionner plusieurs éléments</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="canvas_selection_mode">Mode de sélection</label></th>
                                <td>
                                    <select id="canvas_selection_mode" name="canvas_selection_mode">
                                        <option value="click" <?php selected(get_option('pdf_builder_canvas_selection_mode', 'click'), 'click'); ?>>Clic simple</option>
                                        <option value="lasso" <?php selected(get_option('pdf_builder_canvas_selection_mode', 'click'), 'lasso'); ?>>Lasso</option>
                                        <option value="rectangle" <?php selected(get_option('pdf_builder_canvas_selection_mode', 'click'), 'rectangle'); ?>>Rectangle</option>
                                    </select>
                                    <p class="canvas-modal-description">Méthode de sélection des éléments sur le canvas</p>
                                </td>
                            </tr>
                        </table>
                    </section>

                    <section>
                        <header>
                            <h4 class="canvas-modal-section-title spaced">⚙️ Comportement</h4>
                        </header>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="canvas_keyboard_shortcuts">Raccourcis clavier</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="canvas_keyboard_shortcuts" name="canvas_keyboard_shortcuts" value="1" <?php checked(get_option('pdf_builder_canvas_keyboard_shortcuts', '1'), '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="canvas-modal-description">Active les raccourcis clavier (Ctrl+Z, Ctrl+Y, etc.)</p>
                                </td>
                            </tr>
                        </table>
                    </section>
                </form>
            </main>
            <footer class="canvas-modal-footer">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="interactions">Sauvegarder</button>
            </footer>
        </section>
    </div>
</div>
<!-- Canvas Configuration Modals Export & Qualité -->
<div id="canvas-export-modal" class="canvas-modal" data-category="export">
    <div class="canvas-modal-overlay">
        <section class="canvas-modal-content">
            <header class="canvas-modal-header">
                <h3 >📤 Export & Qualité</h3>
                <button type="button" class="canvas-modal-close">&times;</button>
            </header>
            <main class="canvas-modal-body">
                <aside class="canvas-modal-info">
                    <p>
                        <strong>📤 Export & Qualité :</strong> Ces paramètres contrôlent le format et la qualité des fichiers exportés depuis le canvas.
                        Choisissez le format approprié selon vos besoins (PNG pour la transparence, JPG pour la compatibilité, PDF pour les documents).
                    </p>
                </aside>
                <form id="canvas-export-form">
                    <section>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="canvas_export_format">Format d'export par défaut</label></th>
                                <td>
                                    <select id="canvas_export_format" name="canvas_export_format">
                                        <option value="png" <?php selected(get_option('pdf_builder_canvas_export_format', 'png'), 'png'); ?>>PNG</option>
                                        <option value="jpg" <?php selected(get_option('pdf_builder_canvas_export_format', 'png'), 'jpg'); ?>>JPG</option>
                                        <option value="pdf" <?php selected(get_option('pdf_builder_canvas_export_format', 'png'), 'pdf'); ?>>PDF</option>
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
                    </section>
                </form>
            </main>
            <footer class="canvas-modal-footer">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="export">Sauvegarder</button>
            </footer>
        </section>
    </div>
</div>
<!-- Canvas Configuration Modals Performance -->
<div id="canvas-performance-modal" class="canvas-modal" data-category="performance">
    <div class="canvas-modal-overlay">
        <section class="canvas-modal-content">
            <header class="canvas-modal-header">
                <h3 >⚡ Performance</h3>
                <button type="button" class="canvas-modal-close">&times;</button>
            </header>
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
                                    <select id="canvas_fps_target" name="canvas_fps_target">
                                        <option value="30" <?php selected(get_option('pdf_builder_canvas_fps_target', 60), 30); ?>>30 FPS (Économie)</option>
                                        <option value="60" <?php selected(get_option('pdf_builder_canvas_fps_target', 60), 60); ?>>60 FPS (Standard)</option>
                                        <option value="120" <?php selected(get_option('pdf_builder_canvas_fps_target', 60), 120); ?>>120 FPS (Haute performance)</option>
                                    </select>
                                    <aside id="fps_preview" class="canvas-modal-preview">
                                        FPS actuel : <span id="current_fps_value"><?php echo intval(get_option('pdf_builder_canvas_fps_target', 60)); ?></span>
                                    </aside>
                                    <p class="canvas-modal-description">Fluidité du rendu canvas (plus élevé = plus de ressources)</p>
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
                                    <p class="canvas-modal-description">Mémoire allouée au canvas et aux éléments</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="canvas_lazy_loading_editor">Chargement paresseux (Éditeur)</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="canvas_lazy_loading_editor" name="canvas_lazy_loading_editor" value="1" <?php checked(get_option('pdf_builder_canvas_lazy_loading_editor', '1'), '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="canvas-modal-description">Charge les éléments seulement quand visibles</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="canvas_preload_critical">Préchargement ressources critiques</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="canvas_preload_critical" name="canvas_preload_critical" value="1" <?php checked(get_option('pdf_builder_canvas_preload_critical', '1'), '1'); ?>>
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
                                    <select id="canvas_memory_limit_php" name="canvas_memory_limit_php">
                                        <option value="128" <?php selected(get_option('pdf_builder_canvas_memory_limit_php', '256'), '128'); ?>>128 MB</option>
                                        <option value="256" <?php selected(get_option('pdf_builder_canvas_memory_limit_php', '256'), '256'); ?>>256 MB</option>
                                        <option value="512" <?php selected(get_option('pdf_builder_canvas_memory_limit_php', '256'), '512'); ?>>512 MB</option>
                                        <option value="1024" <?php selected(get_option('pdf_builder_canvas_memory_limit_php', '256'), '1024'); ?>>1 GB</option>
                                    </select>
                                    <p class="canvas-modal-description">Mémoire pour génération PDF et traitement</p>
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
                                    <p class="canvas-modal-description">Délai maximum pour les requêtes serveur</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="canvas_lazy_loading_plugin">Chargement paresseux (Plugin)</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="canvas_lazy_loading_plugin" name="canvas_lazy_loading_plugin" value="1" <?php checked(get_option('pdf_builder_canvas_lazy_loading_plugin', '1'), '1'); ?>>
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
                <button type="button" class="button button-primary canvas-modal-save" data-category="performance">Sauvegarder</button>
            </footer>
        </section>
    </div>
</div>
<!-- Canvas Configuration Modals Debug -->
<div id="canvas-debug-modal" class="canvas-modal" data-category="debug">
    <div class="canvas-modal-overlay">
        <section class="canvas-modal-content">
            <header class="canvas-modal-header">
                <h3 >🐛 Debug</h3>
                <button type="button" class="canvas-modal-close">&times;</button>
            </header>
            <main class="canvas-modal-body">
                <aside class="canvas-modal-info">
                    <p>
                        <strong>🐛 Debug & Monitoring :</strong> Ces outils de débogage vous aident à diagnostiquer les problèmes et à surveiller les performances du plugin PDF Builder.
                        Utilisez-les uniquement en cas de problème ou pour l'optimisation avancée.
                    </p>
                </aside>
                <form id="canvas-debug-form">
                    <section>
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
                    </section>
                </form>
            </main>
            <footer class="canvas-modal-footer">
                <button type="button" class="button button-secondary canvas-modal-cancel">Annuler</button>
                <button type="button" class="button button-primary canvas-modal-save" data-category="debug">Sauvegarder</button>
            </footer>
        </section>
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

        // Gestion de la dépendance Sélection multiple -> Mode de sélection
        const multiSelectCheckbox = document.getElementById('canvas_multi_select');
        const selectionModeSelect = document.getElementById('canvas_selection_mode');

        if (multiSelectCheckbox && selectionModeSelect) {
            // Fonction pour gérer l'état du mode de sélection
            function updateSelectionModeState() {
                if (!multiSelectCheckbox.checked) {
                    // Désactiver le mode de sélection si sélection multiple est désactivée
                    selectionModeSelect.disabled = true;
                    selectionModeSelect.style.opacity = '0.5';
                    selectionModeSelect.style.cursor = 'not-allowed';
                    // Sauvegarder la valeur actuelle pour la restaurer si réactivé
                    selectionModeSelect.setAttribute('data-previous-value', selectionModeSelect.value);
                    selectionModeSelect.value = 'click'; // Forcer en mode clic simple
                } else {
                    // Réactiver le mode de sélection si sélection multiple est activée
                    selectionModeSelect.disabled = false;
                    selectionModeSelect.style.opacity = '1';
                    selectionModeSelect.style.cursor = 'default';
                    // Restaurer la valeur précédente si elle existe
                    const previousValue = selectionModeSelect.getAttribute('data-previous-value');
                    if (previousValue) {
                        selectionModeSelect.value = previousValue;
                        selectionModeSelect.removeAttribute('data-previous-value');
                    }
                }
            }

            // Appliquer l'état initial
            updateSelectionModeState();

            // Écouter les changements sur la case à cocher
            multiSelectCheckbox.addEventListener('change', updateSelectionModeState);
        }
    });
</script>
