<?php // Systeme tab content - Updated: 2025-11-18 20:20:00

?>
<?php
/**
 * Safe wrapper for get_option that works even when WordPress is not fully loaded
 */
function pdf_builder_safe_get_option($option, $default = '') {
    if (function_exists('get_option')) {
        return get_option($option, $default);
    }
    return $default;
}

/**
 * Safe wrapper for checked function
 */
function pdf_builder_safe_checked($checked, $current = true, $echo = true) {
    if (function_exists('checked')) {
        return checked($checked, $current, $echo);
    }
    $result = checked($checked, $current, false);
    if ($echo) echo $result;
    return $result;
}

/**
 * Safe wrapper for selected function
 */
function pdf_builder_safe_selected($selected, $current = true, $echo = true) {
    if (function_exists('selected')) {
        return selected($selected, $current, $echo);
    }
    $result = selected($selected, $current, false);
    if ($echo) echo $result;
    return $result;
}
?>
            <h2>⚙️ Système - Performance, Maintenance & Sauvegarde</h2>

                <!-- Section Cache et Performance -->
                <section class="system-cache-section">
                    <header>
                        <h3>
                            <span>
                                📋 Cache & Performance - ⚠️ En attente d'implémentation
                                <span class="cache-performance-status"><?php echo pdf_builder_safe_get_option('pdf_builder_cache_enabled', false) ? 'ACTIF' : 'INACTIF'; ?></span>
                            </span>
                        </h3>
                    </header>

                    <div class="system-section-content">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="general_cache_enabled">Cache activé</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="general_cache_enabled" name="pdf_builder_cache_enabled" value="1" <?php pdf_builder_safe_checked(pdf_builder_safe_get_option('pdf_builder_cache_enabled', false)); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Améliore les performances en mettant en cache les données</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="cache_compression">Compression du cache</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="cache_compression" name="cache_compression" value="1" <?php pdf_builder_safe_checked(pdf_builder_safe_get_option('pdf_builder_cache_compression', true)); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Compresser les données en cache pour économiser l'espace disque</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="cache_auto_cleanup">Nettoyage automatique</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="cache_auto_cleanup" name="cache_auto_cleanup" value="1" <?php pdf_builder_safe_checked(pdf_builder_safe_get_option('pdf_builder_cache_auto_cleanup', true)); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Nettoyer automatiquement les anciens fichiers cache</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="cache_max_size">Taille max du cache (MB)</label></th>
                                <td>
                                    <input type="number" id="cache_max_size" name="cache_max_size" value="<?php echo intval(pdf_builder_safe_get_option('pdf_builder_cache_max_size', 100)); ?>" min="10" max="1000" step="10" />
                                    <p class="description">Taille maximale du dossier cache en mégaoctets</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="cache_ttl">TTL du cache (secondes)</label></th>
                                <td>
                                    <input type="number" id="cache_ttl" name="cache_ttl" value="<?php echo intval(pdf_builder_safe_get_option('pdf_builder_cache_ttl', 3600)); ?>" min="0" max="86400" />
                                    <p class="description">Durée de vie du cache en secondes (défaut: 3600)</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="performance_auto_optimization">Optimisation automatique des performances</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="performance_auto_optimization" name="performance_auto_optimization" value="1" <?php pdf_builder_safe_checked(pdf_builder_safe_get_option('pdf_builder_performance_auto_optimization', '0'), '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Optimisation hebdomadaire automatique de la base de données et des ressources système</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Test du système</th>
                                <td>
                                    <button type="button" id="test-cache-btn" class="button button-secondary system-btn">
                                        🧪 Tester l'intégration du cache
                                    </button>
                                    <span id="cache-test-results"></span>
                                    <div id="cache-test-output"></div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Vider le cache</th>
                                <td>
                                    <button type="button" id="clear-cache-general-btn" class="button button-secondary system-btn danger">
                                        🗑️ Vider tout le cache
                                    </button>
                                    <span id="clear-cache-general-results"></span>
                                    <p class="description">Vide tous les transients, caches et données en cache du plugin</p>
                                </td>
                            </tr>
                        </table>

                        <!-- Informations sur l'état du cache -->
                        <article class="cache-status-info">
                            <header>
                                <h4>📊 État du système de cache</h4>
                            </header>
                            <div class="metric-grid">
                                <div class="cache-metric-card" data-metric="size">
                                    <div class="metric-value">
                                        <?php
                                        $cache_size = 0;
                                        $cache_dirs = [
                                            (defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : '') . '/cache/wp-pdf-builder-previews/',
                                            (function_exists('wp_upload_dir') ? wp_upload_dir()['basedir'] : '') . '/pdf-builder-cache'
                                        ];

                                        // Version simplifiée : compter juste les fichiers
                                        $cache_file_count = 0;
                                        foreach ($cache_dirs as $dir) {
                                            if (is_dir($dir) && is_readable($dir)) {
                                                try {
                                                    $files = glob($dir . '/*');
                                                    if ($files) {
                                                        $cache_file_count += count($files);
                                                    }
                                                } catch (Exception $e) {
                                                    // Ignorer les erreurs
                                                }
                                            }
                                        }

                                        // Afficher le nombre de fichiers
                                        echo '<span id="cache-size-display">';
                                        echo $cache_file_count . ' fichiers';
                                        echo '</span>';
                                        ?>
                                    </div>
                                    <div class="metric-label">Taille du cache</div>
                                    <div class="metric-hint">Cliquez pour détails</div>
                                </div>
                                <div class="cache-metric-card" data-metric="transients">
                                    <div class="metric-value">
                                        <?php
                                        $transient_count = 0;
                                        if (isset($GLOBALS['wpdb']) && function_exists('get_option')) {
                                            global $wpdb;
                                            try {
                                                $transient_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'");
                                            } catch (Exception $e) {
                                                $transient_count = 0;
                                            }
                                        }
                                        echo intval($transient_count);
                                        ?>
                                    </div>
                                    <div class="metric-label">Transients actifs</div>
                                    <div class="metric-hint">Cliquez pour détails</div>
                                </div>
                                <div class="cache-metric-card systeme-cache-status" data-metric="status">
                                    <div class="cache-enabled-indicator metric-value">
                                    <?php echo pdf_builder_safe_get_option('pdf_builder_cache_enabled', false) ? 'Cache activé' : 'Cache désactivé'; ?>
                                    </div>
                                    <div class="metric-label">État du cache</div>
                                    <div class="metric-hint">Cliquez pour configurer</div>
                                </div>
                                <div class="cache-metric-card" data-metric="cleanup">
                                    <div class="metric-value">
                                        <?php
                                        $last_cleanup = pdf_builder_safe_get_option('pdf_builder_cache_last_cleanup', 'Jamais');
                                        if ($last_cleanup !== 'Jamais') {
                                            $last_cleanup = (function_exists('human_time_diff') ? human_time_diff(strtotime($last_cleanup)) : $last_cleanup) . ' ago';
                                        }
                                        echo $last_cleanup;
                                        ?>
                                    </div>
                                    <div class="metric-label">Dernier nettoyage</div>
                                    <div class="metric-hint">Cliquez pour nettoyer</div>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>
                <!-- Section Maintenance -->
                <section class="system-maintenance-section">
                    <h3>
                        <span style="display: inline-flex; align-items: center; gap: 10px;">
                            🔧 Maintenance
                            <span class="maintenance-status"><?php echo pdf_builder_safe_get_option('pdf_builder_auto_maintenance', '0') === '1' ? 'ACTIF' : 'INACTIF'; ?></span>
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
                    </table>

                    <!-- Section Maintenance Système -->
                    <div class="system-section-content">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="systeme_auto_maintenance">Maintenance automatique</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="systeme_auto_maintenance" name="systeme_auto_maintenance" value="1" <?php pdf_builder_safe_checked(pdf_builder_safe_get_option('pdf_builder_auto_maintenance', '0'), '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Active la maintenance automatique hebdomadaire</p>
                                    <div class="maintenance-info-box">
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

                        <!-- Métriques de maintenance -->
                        <article class="maintenance-status-info">
                            <header>
                                <h4>📊 État de la maintenance</h4>
                            </header>
                            <div class="metric-grid">
                                <button type="button" class="maintenance-metric-card maintenance-action-btn" data-metric="last-run" data-action="run-maintenance" title="Lancer la maintenance manuelle complète">
                                    <div class="metric-card-header">
                                        <div class="metric-icon">🔄</div>
                                        <div class="metric-status" data-status="ready">
                                            <span class="status-indicator"></span>
                                        </div>
                                    </div>
                                    <div class="metric-value">
                                        <?php
                                        $last_maintenance = pdf_builder_safe_get_option('pdf_builder_last_maintenance', 'Jamais');
                                        if ($last_maintenance !== 'Jamais') {
                                            $last_maintenance = (function_exists('human_time_diff') ? human_time_diff(strtotime($last_maintenance)) : $last_maintenance) . ' ago';
                                        }
                                        echo $last_maintenance;
                                        ?>
                                    </div>
                                    <div class="metric-label">Dernière exécution</div>
                                    <div class="metric-action">
                                        <span class="action-text">Lancer maintenant</span>
                                        <span class="action-icon">▶️</span>
                                    </div>
                                </button>

                                <button type="button" class="maintenance-metric-card maintenance-action-btn" data-metric="next-run" data-action="schedule-maintenance" title="Programmer la prochaine maintenance automatique">
                                    <div class="metric-card-header">
                                        <div class="metric-icon">📅</div>
                                        <div class="metric-status" data-status="scheduled">
                                            <span class="status-indicator"></span>
                                        </div>
                                    </div>
                                    <div class="metric-value">
                                        <?php
                                        $next_maintenance = pdf_builder_safe_get_option('pdf_builder_next_maintenance', 'Non planifiée');
                                        if ($next_maintenance !== 'Non planifiée') {
                                            $next_maintenance = (function_exists('date_i18n') ? date_i18n('d/m/Y H:i', strtotime($next_maintenance)) : date('d/m/Y H:i', strtotime($next_maintenance)));
                                        }
                                        echo $next_maintenance;
                                        ?>
                                    </div>
                                    <div class="metric-label">Prochaine exécution</div>
                                    <div class="metric-action">
                                        <span class="action-text">Programmer</span>
                                        <span class="action-icon">⚙️</span>
                                    </div>
                                </button>

                                <button type="button" class="maintenance-metric-card maintenance-action-btn" data-metric="status" data-action="toggle-maintenance" title="Activer/désactiver la maintenance automatique">
                                    <div class="metric-card-header">
                                        <div class="metric-icon">
                                            <?php echo pdf_builder_safe_get_option('pdf_builder_auto_maintenance', '0') === '1' ? '✅' : '❌'; ?>
                                        </div>
                                        <div class="metric-status" data-status="<?php echo pdf_builder_safe_get_option('pdf_builder_auto_maintenance', '0') === '1' ? 'active' : 'inactive'; ?>">
                                            <span class="status-indicator"></span>
                                        </div>
                                    </div>
                                    <div class="metric-value">
                                        <span class="status-badge <?php echo pdf_builder_safe_get_option('pdf_builder_auto_maintenance', '0') === '1' ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo pdf_builder_safe_get_option('pdf_builder_auto_maintenance', '0') === '1' ? 'Activée' : 'Désactivée'; ?>
                                        </span>
                                    </div>
                                    <div class="metric-label">Maintenance auto</div>
                                    <div class="metric-action">
                                        <span class="action-text">Basculer</span>
                                        <span class="action-icon">🔄</span>
                                    </div>
                                </button>

                                <button type="button" class="maintenance-metric-card maintenance-action-btn" data-metric="manual" data-action="run-manual-maintenance" title="Exécuter manuellement toutes les tâches de maintenance">
                                    <div class="metric-card-header">
                                        <div class="metric-icon">🚀</div>
                                        <div class="metric-status" data-status="manual">
                                            <span class="status-indicator"></span>
                                        </div>
                                    </div>
                                    <div class="metric-value">
                                        <span class="manual-badge">Manuel</span>
                                    </div>
                                    <div class="metric-label">Lancement manuel</div>
                                    <div class="metric-action">
                                        <span class="action-text">Exécuter</span>
                                        <span class="action-icon">⚡</span>
                                    </div>
                                </button>
                            </div>
                        </article>
                    </div>
                </section>
                <!-- Section Sauvegarde -->
                <section class="system-backup-section">
                    <header>
                        <h3>
                            <span>
                                💾 Gestion des Sauvegardes
                                <span class="backup-status"><?php echo pdf_builder_safe_get_option('pdf_builder_auto_backup', '0') === '1' ? 'ACTIF' : 'INACTIF'; ?></span>
                            </span>
                        </h3>
                    </header>

                    <div class="system-section-content">
                        <!-- Informations sur les sauvegardes -->
                        <article class="backup-info">
                            <header>
                                <h4>ℹ️ Informations</h4>
                            </header>
                            <ul>
                                <li>Les sauvegardes contiennent tous vos paramètres PDF Builder</li>
                                <li>Les sauvegardes automatiques sont créées quotidiennement</li>
                                <li>Les anciennes sauvegardes sont supprimées automatiquement selon la rétention configurée</li>
                            </ul>
                        </article>

                        <table class="form-table">
                            <tr>
                                <th scope="row">Actions de sauvegarde</th>
                                <td>
                                    <div class="backup-actions">
                                        <button type="button" id="create-backup-btn" class="button button-primary">
                                            <span>📦</span> Créer une sauvegarde
                                        </button>
                                        <button type="button" id="list-backups-btn" class="button button-secondary">
                                            <span>📋</span> Lister les sauvegardes
                                        </button>
                                    </div>
                                    <div id="backup-results"></div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="systeme_auto_backup">Sauvegarde automatique</label>
                                </th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="systeme_auto_backup" name="systeme_auto_backup" value="1" <?php pdf_builder_safe_checked(pdf_builder_safe_get_option('pdf_builder_auto_backup', '0'), '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <span>Active la création automatique de sauvegardes</span>
                                </td>
                            </tr>
                            <tr id="auto_backup_frequency_row">
                                <th scope="row">
                                    <label for="systeme_auto_backup_frequency">Fréquence des sauvegardes</label>
                                </th>
                                <td>
                                    <?php
                                    // S'assurer que l'option existe avec une valeur par défaut
                                    $stored_value = pdf_builder_safe_get_option('pdf_builder_auto_backup_frequency');
                                    if (empty($stored_value)) {
                                        update_option('pdf_builder_auto_backup_frequency', 'daily');
                                        $stored_value = 'daily';
                                    }
                                    $current_frequency = $stored_value;
                                    ?>
                                    <select id="systeme_auto_backup_frequency" name="systeme_auto_backup_frequency" <?php echo (pdf_builder_safe_get_option('pdf_builder_auto_backup', '0') === '0') ? 'disabled' : ''; ?>>
                                        <option value="daily" <?php pdf_builder_safe_selected($current_frequency, 'daily'); ?>>📅 Quotidienne (tous les jours)</option>
                                        <option value="weekly" <?php pdf_builder_safe_selected($current_frequency, 'weekly'); ?>>📆 Hebdomadaire (tous les dimanches)</option>
                                        <option value="monthly" <?php pdf_builder_safe_selected($current_frequency, 'monthly'); ?>>📊 Mensuelle (1er du mois)</option>
                                    </select>
                                    <!-- Champ hidden pour garantir que la valeur est toujours soumise, même si le select est disabled -->
                                    <input type="hidden" name="systeme_auto_backup_frequency_hidden" value="<?php echo esc_attr($current_frequency); ?>" id="systeme_auto_backup_frequency_hidden">
                                    <p class="description">Détermine la fréquence de création automatique des sauvegardes</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="systeme_backup_retention">Rétention des sauvegardes</label>
                                </th>
                                <td>
                                    <div class="backup-retention-input">
                                        <input type="number" id="systeme_backup_retention" name="systeme_backup_retention" value="<?php echo esc_attr(pdf_builder_safe_get_option('pdf_builder_backup_retention', 30)); ?>" min="1" max="365">
                                        <span>jours</span>
                                    </div>
                                    <p class="description">Nombre de jours avant suppression automatique des anciennes sauvegardes (1-365 jours)</p>
                                </td>
                            </tr>
                        </table>

                        <!-- Métriques de sauvegarde -->
                        <article class="backup-status-info">
                            <header>
                                <h4>📊 État des sauvegardes</h4>
                            </header>
                            <div class="metric-grid">
                                <div class="backup-metric-card" data-metric="last-backup">
                                    <div class="metric-value">
                                        <?php
                                        $last_backup = pdf_builder_safe_get_option('pdf_builder_last_backup', 'Jamais');
                                        if ($last_backup !== 'Jamais') {
                                            $last_backup = (function_exists('human_time_diff') ? human_time_diff(strtotime($last_backup)) : $last_backup) . ' ago';
                                        }
                                        echo $last_backup;
                                        ?>
                                    </div>
                                    <div class="metric-label">Dernière sauvegarde</div>
                                    <div class="metric-hint">Cliquez pour créer</div>
                                </div>
                                <div class="backup-metric-card" data-metric="total-backups">
                                    <div class="metric-value">
                                        <?php
                                        $backup_dir = (function_exists('wp_upload_dir') ? wp_upload_dir()['basedir'] : '') . '/pdf-builder-backups';
                                        $backup_count = 0;
                                        if (is_dir($backup_dir)) {
                                            $files = glob($backup_dir . '/*.json');
                                            $backup_count = count($files);
                                        }
                                        echo $backup_count;
                                        ?>
                                    </div>
                                    <div class="metric-label">Total sauvegardes</div>
                                    <div class="metric-hint">Cliquez pour lister</div>
                                </div>
                                <div class="backup-metric-card" data-metric="auto-status">
                                    <div class="metric-value">
                                        <?php echo pdf_builder_safe_get_option('pdf_builder_auto_backup', '0') === '1' ? 'Activée' : 'Désactivée'; ?>
                                    </div>
                                    <div class="metric-label">Sauvegarde auto</div>
                                    <div class="metric-hint">Cliquez pour configurer</div>
                                </div>
                                <div class="backup-metric-card" data-metric="retention">
                                    <div class="metric-value">
                                        <?php echo pdf_builder_safe_get_option('pdf_builder_backup_retention', 30); ?>j
                                    </div>
                                    <div class="metric-label">Rétention</div>
                                    <div class="metric-hint">Cliquez pour modifier</div>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>

                <!-- Message d'aide pour la sauvegarde -->
                <aside class="backup-help-notice">
                    <header>
                        <h4>💡 Comment sauvegarder les paramètres ?</h4>
                    </header>
                    <p>
                        Utilisez le bouton <strong>"💾 Enregistrer"</strong> flottant en bas à droite de l'écran pour sauvegarder tous les paramètres système.
                        Les modifications ne sont appliquées que lorsque vous cliquez sur ce bouton.
                    </p>
                </aside>

            <!-- Modales de cache et canvas - chargées SEULEMENT dans le tab Système -->
            <?php require_once __DIR__ . '/settings-modals.php'; ?>

