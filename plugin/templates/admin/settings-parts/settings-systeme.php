<?php // Systeme tab content - Updated: 2025-11-18 20:20:00 ?>
            <h2>⚙️ Système - Performance, Maintenance & Sauvegarde</h2>

            <!-- Formulaire unique pour tout l'onglet système -->
            <form id="systeme-settings-form" method="post" action="">
                <?php wp_nonce_field('pdf_builder_save_settings', 'pdf_builder_systeme_nonce'); ?>
                <input type="hidden" name="current_tab" value="systeme">
                <input type="hidden" name="current_tab" value="systeme">

                <!-- Section Cache et Performance -->
                <div style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border: 2px solid #e9ecef; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                    <h3 style="color: #495057; margin-top: 0; border-bottom: 2px solid #e9ecef; padding-bottom: 8px; font-size: 18px;">
                        <span style="display: inline-flex; align-items: center; gap: 10px;">
                            📋 Cache & Performance - ⚠️ En attente d'implémentation
                            <span class="cache-performance-status" style="font-size: 12px; background: <?php echo get_option('pdf_builder_cache_enabled', false) ? '#28a745' : '#dc3545'; ?>; color: white; padding: 2px 8px; border-radius: 10px; font-weight: normal;"><?php echo get_option('pdf_builder_cache_enabled', false) ? 'ACTIF' : 'INACTIF'; ?></span>
                        </span>
                    </h3>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="general_cache_enabled">Cache activé</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="general_cache_enabled" name="cache_enabled" value="1" <?php checked(get_option('pdf_builder_cache_enabled', false)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Améliore les performances en mettant en cache les données</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="cache_compression">Compression du cache</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="cache_compression" name="cache_compression" value="1" <?php checked(get_option('pdf_builder_cache_compression', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Compresser les données en cache pour économiser l'espace disque</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="cache_auto_cleanup">Nettoyage automatique</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="cache_auto_cleanup" name="cache_auto_cleanup" value="1" <?php checked(get_option('pdf_builder_cache_auto_cleanup', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Nettoyer automatiquement les anciens fichiers cache</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="cache_max_size">Taille max du cache (MB)</label></th>
                            <td>
                                <input type="number" id="cache_max_size" name="cache_max_size" value="<?php echo intval(get_option('pdf_builder_cache_max_size', 100)); ?>" min="10" max="1000" step="10" />
                                <p class="description">Taille maximale du dossier cache en mégaoctets</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="cache_ttl">TTL du cache (secondes)</label></th>
                            <td>
                                <input type="number" id="cache_ttl" name="cache_ttl" value="<?php echo intval(get_option('pdf_builder_cache_ttl', 3600)); ?>" min="0" max="86400" />
                                <p class="description">Durée de vie du cache en secondes (défaut: 3600)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="performance_auto_optimization">Optimisation automatique des performances</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="performance_auto_optimization" name="performance_auto_optimization" value="1" <?php checked(get_option('pdf_builder_performance_auto_optimization', '0'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Optimisation hebdomadaire automatique de la base de données et des ressources système</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Test du système</th>
                            <td>
                                <button type="button" id="test-cache-btn" class="button button-secondary" style="background-color: #6c757d; border-color: #6c757d; color: white; font-weight: bold; padding: 10px 15px;">
                                    🧪 Tester l'intégration du cache
                                </button>
                                <span id="cache-test-results" style="margin-left: 10px;"></span>
                                <div id="cache-test-output" style="display: none; margin-top: 10px; padding: 15px; background: #e7f5e9; border-left: 4px solid #28a745; -webkit-border-radius: 4px; -moz-border-radius: 4px; -ms-border-radius: 4px; -o-border-radius: 4px; border-radius: 4px; color: #155724;"></div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Vider le cache</th>
                            <td>
                                <button type="button" id="clear-cache-general-btn" class="button button-secondary" style="background-color: #dc3232; border-color: #dc3232; color: white; font-weight: bold; padding: 10px 15px;">
                                    🗑️ Vider tout le cache
                                </button>
                                <span id="clear-cache-general-results" style="margin-left: 10px;"></span>
                                <p class="description">Vide tous les transients, caches et données en cache du plugin</p>
                            </td>
                        </tr>
                    </table>

                    <!-- Informations sur l'état du cache -->
                    <div style="margin-top: 20px; padding: 15px; background: rgba(255,255,255,0.8); border-radius: 8px; border: 1px solid #28a745;">
                        <h4 style="margin-top: 0; color: #155724; font-size: 16px;">📊 État du système de cache</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-top: 12px;">
                            <div style="text-align: center;">
                                <div style="font-size: 24px; font-weight: bold; color: #28a745;">
                                    <?php
                                    $cache_size = 0;
                                    $cache_dirs = [
                                        WP_CONTENT_DIR . '/cache/wp-pdf-builder-previews/',
                                        wp_upload_dir()['basedir'] . '/pdf-builder-cache'
                                    ];

                                    // Afficher la taille avec l'unité appropriée et décimales
                                    echo '<span id="cache-size-display">';
                                    if ($cache_size < 1048576) { // < 1 Mo
                                        echo number_format($cache_size / 1024, 1) . ' Ko';
                                    } else {
                                        echo number_format($cache_size / 1048576, 1) . ' Mo';
                                    }
                                    echo '</span>';
                                    ?>
                                </div>
                                <div style="color: #666; font-size: 12px;">Taille du cache</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-size: 24px; font-weight: bold; color: #28a745;">
                                    <?php
                                    $transient_count = 0;
                                    global $wpdb;
                                    $transient_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'");
                                    echo intval($transient_count);
                                    ?>
                                </div>
                                <div style="color: #666; font-size: 12px;">Transients actifs</div>
                            </div>
                            <div style="text-align: center;" class="systeme-cache-status">
                                <div class="cache-enabled-indicator" style="font-size: 24px; font-weight: bold; color: <?php echo get_option('pdf_builder_cache_enabled', false) ? '#28a745' : '#dc3545'; ?>;">
                                <?php echo get_option('pdf_builder_cache_enabled', false) ? 'Cache activé' : 'Cache désactivé'; ?>
                            </div>
                                <div style="color: #666; font-size: 12px;">État du cache</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-size: 24px; font-weight: bold; color: #28a745;">
                                    <?php
                                    $last_cleanup = get_option('pdf_builder_cache_last_cleanup', 'Jamais');
                                    if ($last_cleanup !== 'Jamais') {
                                        $last_cleanup = human_time_diff(strtotime($last_cleanup)) . ' ago';
                                    }
                                    echo $last_cleanup;
                                    ?>
                                </div>
                                <div style="color: #666; font-size: 12px;">Dernier nettoyage</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Maintenance -->
                <div style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border: 2px solid #e9ecef; border-radius: 12px; padding: 30px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                    <h3 style="color: #495057; margin-top: 0; border-bottom: 2px solid #e9ecef; padding-bottom: 10px;">
                        <span style="display: inline-flex; align-items: center; gap: 10px;">
                            🔧 Maintenance
                            <span class="maintenance-status" style="font-size: 12px; background: <?php echo get_option('pdf_builder_auto_maintenance', '0') === '1' ? '#28a745' : '#dc3545'; ?>; color: white; padding: 2px 8px; border-radius: 10px; font-weight: normal;"><?php echo get_option('pdf_builder_auto_maintenance', '0') === '1' ? 'ACTIF' : 'INACTIF'; ?></span>
                        </span>
                    </h3>

                    <table class="form-table">
                        <tr>
                            <th scope="row">Actions de maintenance</th>
                            <td>
                                <button type="button" id="optimize-db-btn" class="button button-secondary" style="margin-right: 10px;">🗃️ Optimiser la base</button>
                                <button type="button" id="repair-templates-btn" class="button button-secondary" style="margin-right: 10px;">🔧 Réparer les templates</button>
                                <button type="button" id="remove-temp-btn" class="button button-secondary">🗂️ Supprimer fichiers temp</button>
                                <div id="maintenance-results" style="margin-top: 10px;"></div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="systeme_auto_maintenance">Maintenance automatique</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="systeme_auto_maintenance" name="systeme_auto_maintenance" value="1" <?php checked(get_option('pdf_builder_auto_maintenance', '0'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Active la maintenance automatique hebdomadaire</p>
                                <div style="margin-top: 8px; padding: 10px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; font-size: 12px; color: #6c757d;">
                                    <strong>ℹ️ Ce que fait la maintenance automatique :</strong><br>
                                    • Optimisation de la base de données (réparation des tables)<br>
                                    • Vérification et réparation des templates<br>
                                    • Suppression des fichiers temporaires (+24h)<br>
                                    • Nettoyage du cache ancien (+7 jours)<br>
                                    <em>Exécution tous les dimanches à 02:00. Les logs sont enregistrés automatiquement.</em>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Section Sauvegarde -->
                <div style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border: 2px solid #e9ecef; border-radius: 12px; padding: 30px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                    <h3 style="color: #495057; margin-top: 0; border-bottom: 2px solid #e9ecef; padding-bottom: 10px;">
                        <span style="display: inline-flex; align-items: center; gap: 10px;">
                            💾 Gestion des Sauvegardes
                            <span class="backup-status" style="font-size: 12px; background: <?php echo get_option('pdf_builder_auto_backup', '0') === '1' ? '#28a745' : '#dc3545'; ?>; color: white; padding: 2px 8px; border-radius: 10px; font-weight: normal;"><?php echo get_option('pdf_builder_auto_backup', '0') === '1' ? 'ACTIF' : 'INACTIF'; ?></span>
                        </span>
                    </h3>

                    <!-- Informations sur les sauvegardes -->
                    <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                        <h4 style="margin: 0 0 10px 0; color: #495057; font-size: 14px;">ℹ️ Informations</h4>
                        <ul style="margin: 0; padding-left: 20px; color: #6c757d; font-size: 13px;">
                            <li>Les sauvegardes contiennent tous vos paramètres PDF Builder</li>
                            <li>Les sauvegardes automatiques sont créées quotidiennement</li>
                            <li>Les anciennes sauvegardes sont supprimées automatiquement selon la rétention configurée</li>
                        </ul>
                    </div>

                    <table class="form-table">
                        <tr>
                            <th scope="row" style="width: 200px;">Actions de sauvegarde</th>
                            <td>
                                <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                                    <button type="button" id="create-backup-btn" class="button button-primary" style="display: inline-flex; align-items: center; gap: 5px;">
                                        <span>📦</span> Créer une sauvegarde
                                    </button>
                                    <button type="button" id="list-backups-btn" class="button button-secondary" style="display: inline-flex; align-items: center; gap: 5px;">
                                        <span>📋</span> Lister les sauvegardes
                                    </button>
                                </div>
                                <div id="backup-results" style="margin-top: 15px; min-height: 30px;"></div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="systeme_auto_backup" style="display: flex; align-items: center; gap: 8px;">
                                    <span>🔄</span> Sauvegarde automatique
                                </label>
                            </th>
                            <td>
                                <label class="toggle-switch" style="margin-right: 15px;">
                                    <input type="checkbox" id="systeme_auto_backup" name="systeme_auto_backup" value="1" <?php checked(get_option('pdf_builder_auto_backup', '0'), '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span style="color: #6c757d; font-size: 13px;">Active la création automatique de sauvegardes</span>
                            </td>
                        </tr>
                        <tr id="auto_backup_frequency_row">
                            <th scope="row">
                                <label for="systeme_auto_backup_frequency" style="display: flex; align-items: center; gap: 8px;">
                                    <span>⏰</span> Fréquence des sauvegardes
                                </label>
                            </th>
                            <td>
                                <?php
                                // S'assurer que l'option existe avec une valeur par défaut
                                $stored_value = get_option('pdf_builder_auto_backup_frequency');
                                if (empty($stored_value)) {
                                    update_option('pdf_builder_auto_backup_frequency', 'daily');
                                    $stored_value = 'daily';
                                }
                                $current_frequency = $stored_value;
                                ?>
                                <select id="systeme_auto_backup_frequency" name="systeme_auto_backup_frequency" style="min-width: 200px;" <?php echo (get_option('pdf_builder_auto_backup', '0') === '0') ? 'disabled' : ''; ?>>
                                    <option value="daily" <?php selected($current_frequency, 'daily'); ?>>📅 Quotidienne (tous les jours)</option>
                                    <option value="weekly" <?php selected($current_frequency, 'weekly'); ?>>📆 Hebdomadaire (tous les dimanches)</option>
                                    <option value="monthly" <?php selected($current_frequency, 'monthly'); ?>>📊 Mensuelle (1er du mois)</option>
                                </select>
                                <!-- Champ hidden pour garantir que la valeur est toujours soumise, même si le select est disabled -->
                                <input type="hidden" name="systeme_auto_backup_frequency_hidden" value="<?php echo esc_attr($current_frequency); ?>" id="systeme_auto_backup_frequency_hidden">
                                <p class="description" style="margin-top: 5px;">Détermine la fréquence de création automatique des sauvegardes</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="systeme_backup_retention" style="display: flex; align-items: center; gap: 8px;">
                                    <span>🗂️</span> Rétention des sauvegardes
                                </label>
                            </th>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <input type="number" id="systeme_backup_retention" name="systeme_backup_retention" value="<?php echo esc_attr(get_option('pdf_builder_backup_retention', 30)); ?>" min="1" max="365" style="width: 80px;">
                                    <span>jours</span>
                                </div>
                                <p class="description" style="margin-top: 5px;">Nombre de jours avant suppression automatique des anciennes sauvegardes (1-365 jours)</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Message d'aide pour la sauvegarde -->
                <div style="margin-top: 30px; padding: 20px; background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border: 2px solid #f39c12; border-radius: 12px;">
                    <h4 style="margin: 0 0 10px 0; color: #8b4513;">💡 Comment sauvegarder les paramètres ?</h4>
                    <p style="margin: 0; color: #5d4e37; font-size: 14px;">
                        Utilisez le bouton <strong style="color: #007cba;">"💾 Enregistrer"</strong> flottant en bas à droite de l'écran pour sauvegarder tous les paramètres système.
                        Les modifications ne sont appliquées que lorsque vous cliquez sur ce bouton.
                    </p>
                </div>
            </form>

            <!-- Scripts JavaScript pour la section système -->
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                // === GESTIONNAIRES POUR LES BOUTONS DE SAUVEGARDE ===

                // Bouton "Créer une sauvegarde"
                $('#create-backup-btn').on('click', function() {
                    const $btn = $(this);
                    const $results = $('#backup-results');

                    $btn.prop('disabled', true).html('<span>⏳</span> Création...');
                    $results.html('<span style="color: #007cba;">⏳ Création de la sauvegarde en cours...</span>');

                    $.ajax({
                        url: pdf_builder_ajax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'pdf_builder_create_backup',
                            nonce: pdf_builder_ajax.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                $results.html('<span style="color: #28a745;">✅ Sauvegarde créée avec succès</span>');
                                // Ajouter une notification toast
                                if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                                    PDF_Builder_Notification_Manager.show_toast('Sauvegarde créée avec succès !', 'success');
                                }
                            } else {
                                $results.html('<span style="color: #dc3545;">❌ Erreur: ' + (response.data || 'Erreur inconnue') + '</span>');
                                $btn.prop('disabled', false).html('<span>📦</span> Créer une sauvegarde');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('[PDF Builder JS] Erreur AJAX création sauvegarde:', xhr, status, error);
                            $results.html('<span style="color: #dc3545;">❌ Erreur AJAX lors de la création de la sauvegarde</span>');
                        },
                        complete: function() {
                            $btn.prop('disabled', false).html('<span>📦</span> Créer une sauvegarde');
                        }
                    });
                });

                // Bouton "Lister les sauvegardes"
                $('#list-backups-btn').on('click', function() {
                    const $btn = $(this);
                    const $results = $('#backup-results');

                    $btn.prop('disabled', true).html('<span>⏳</span> Chargement...');
                    $results.html('<span style="color: #007cba;">⏳ Chargement de la liste des sauvegardes...</span>');

                    $.ajax({
                        url: pdf_builder_ajax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'pdf_builder_list_backups',
                            nonce: pdf_builder_ajax.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                let html = '<div style="margin-top: 15px;">';
                                html += '<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; padding: 10px; background: #e9ecef; border-radius: 6px;">';
                                html += '<h4 style="margin: 0; color: #495057; display: flex; align-items: center; gap: 8px;">';
                                html += '<span>📋</span> Sauvegardes disponibles (' + response.data.backups.length + ')';
                                html += '</h4>';
                                html += '<small style="color: #6c757d;">Triées par date (plus récent en premier)</small>';
                                html += '</div>';

                                if (response.data.backups.length > 0) {
                                    response.data.backups.forEach(function(backup, index) {
                                        const isAuto = backup.type === 'automatic';
                                        const badgeColor = isAuto ? '#17a2b8' : '#28a745';
                                        const badgeText = isAuto ? 'AUTO' : 'MANUEL';

                                        html += '<div class="backup-item" style="display: flex; align-items: center; justify-content: space-between; padding: 15px; margin: 8px 0; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; transition: all 0.2s ease;">';
                                        html += '<div style="flex: 1; display: flex; align-items: center; gap: 15px;">';
                                        html += '<div style="font-size: 24px;">' + (isAuto ? '🔄' : '📦') + '</div>';
                                        html += '<div>';
                                        html += '<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">';
                                        html += '<strong style="color: #495057; font-size: 14px;">' + backup.filename_raw + '</strong>';
                                        html += '<span style="background: ' + badgeColor + '; color: white; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: bold;">' + badgeText + '</span>';
                                        html += '</div>';
                                        html += '<div style="color: #6c757d; font-size: 12px;">';
                                        html += '<span>📏 ' + backup.size_human + '</span> • ';
                                        html += '<span>📅 ' + backup.modified_human + '</span>';
                                        html += '</div>';
                                        html += '</div>';
                                        html += '</div>';
                                        html += '<div style="display: flex; gap: 8px;">';
                                        html += '<button type="button" class="button button-small restore-backup-btn" data-filename="' + backup.filename + '" style="background: #28a745; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; font-size: 12px;" title="Restaurer cette sauvegarde">';
                                        html += '<span>🔄</span> Restaurer</button>';
                                        html += '<a href="' + window.location.href.split('?')[0] + '?action=pdf_builder_download_backup&filename=' + encodeURIComponent(backup.filename) + '&nonce=' + pdf_builder_ajax.nonce + '" target="_blank" class="button button-small" style="background: #007cba; color: white; text-decoration: none; padding: 6px 12px; border-radius: 4px; display: inline-flex; align-items: center; gap: 5px; font-size: 12px;" title="Télécharger cette sauvegarde">';
                                        html += '<span>📥</span> Télécharger</a>';
                                        html += '<button type="button" class="button button-small delete-backup-btn" data-filename="' + backup.filename + '" style="background: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; font-size: 12px;" title="Supprimer cette sauvegarde">';
                                        html += '<span>🗑️</span> Supprimer</button>';
                                        html += '</div>';
                                        html += '</div>';
                                    });
                                } else {
                                    html += '<div style="text-align: center; padding: 40px; color: #6c757d;">';
                                    html += '<div style="font-size: 48px; margin-bottom: 15px;">📂</div>';
                                    html += '<p>Aucune sauvegarde trouvée.</p>';
                                    html += '<p style="font-size: 14px;">Créez votre première sauvegarde pour sécuriser vos paramètres.</p>';
                                    html += '</div>';
                                }
                                html += '</div>';

                                $results.html('<span style="color: #28a745;">✅ Liste chargée</span>' + html);

                            } else {
                                $results.html('<span style="color: #dc3545;">❌ Erreur: ' + (response.data || 'Erreur inconnue') + '</span>');
                                $btn.prop('disabled', false).html('<span>📋</span> Lister les sauvegardes');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('[PDF Builder JS] Erreur AJAX listage sauvegardes:', xhr, status, error);
                            $results.html('<span style="color: #dc3545;">❌ Erreur AJAX lors du chargement de la liste</span>');
                        },
                        complete: function() {
                            $btn.prop('disabled', false).html('<span>📋</span> Lister les sauvegardes');
                        }
                    });
                });

                // Gestionnaire pour restaurer une sauvegarde
                $(document).on('click', '.restore-backup-btn', function() {
                    const filename = $(this).data('filename');
                    const $btn = $(this);

                    if (!confirm('⚠️ ATTENTION: Cette action va remplacer tous vos paramètres actuels par ceux de la sauvegarde.\n\nÊtes-vous sûr de vouloir continuer ?')) {
                        return;
                    }

                    $btn.prop('disabled', true).html('<span>⏳</span> Restauration...');

                    $.ajax({
                        url: pdf_builder_ajax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'pdf_builder_restore_backup',
                            filename: filename,
                            nonce: pdf_builder_ajax.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                                    PDF_Builder_Notification_Manager.show_toast('Sauvegarde restaurée avec succès ! Rechargez la page.', 'success');
                                }
                                setTimeout(function() {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                alert('Erreur lors de la restauration: ' + (response.data || 'Erreur inconnue'));
                                $btn.prop('disabled', false).html('<span>🔄</span> Restaurer');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('[PDF Builder JS] Erreur AJAX restauration sauvegarde:', xhr, status, error);
                            alert('Erreur AJAX lors de la restauration');
                            $btn.prop('disabled', false).html('<span>🔄</span> Restaurer');
                        }
                    });
                });

                // Gestionnaire pour supprimer une sauvegarde
                $(document).on('click', '.delete-backup-btn', function() {
                    const filename = $(this).data('filename');
                    const $btn = $(this);

                    if (!confirm('Êtes-vous sûr de vouloir supprimer cette sauvegarde ?\n\n' + filename)) {
                        return;
                    }

                    $btn.prop('disabled', true).html('<span>⏳</span> Suppression...');

                    $.ajax({
                        url: pdf_builder_ajax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'pdf_builder_delete_backup',
                            filename: filename,
                            nonce: pdf_builder_ajax.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                                    PDF_Builder_Notification_Manager.show_toast('Sauvegarde supprimée avec succès !', 'success');
                                }
                                // Recharger la liste des sauvegardes
                                $('#list-backups-btn').click();
                            } else {
                                alert('Erreur lors de la suppression: ' + (response.data || 'Erreur inconnue'));
                                $btn.prop('disabled', false).html('<span>🗑️</span> Supprimer');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('[PDF Builder JS] Erreur AJAX suppression sauvegarde:', xhr, status, error);
                            alert('Erreur AJAX lors de la suppression');
                            $btn.prop('disabled', false).html('<span>🗑️</span> Supprimer');
                        }
                    });
                });

                // Gestionnaire pour la sauvegarde automatique - activer/désactiver la fréquence
                $('#systeme_auto_backup').on('change', function() {
                    const isChecked = $(this).is(':checked');
                    const $frequencyRow = $('#auto_backup_frequency_row');
                    const $frequencySelect = $('#systeme_auto_backup_frequency');

                    if (isChecked) {
                        $frequencyRow.show();
                        $frequencySelect.prop('disabled', false);
                    } else {
                        $frequencyRow.hide();
                        $frequencySelect.prop('disabled', true);
                    }
                });

                // Gestionnaire pour la fréquence de sauvegarde automatique
                $('#systeme_auto_backup_frequency').on('change', function() {
                    const $hiddenInput = $('#systeme_auto_backup_frequency_hidden');
                    $hiddenInput.val($(this).val());
                });

                // Initialiser l'état au chargement de la page
                $('#systeme_auto_backup').trigger('change');
            });
            </script>