<?php // Systeme tab content - Updated: 2025-12-05 01:15:00

    // require_once __DIR__ . '/../settings-helpers.php'; // REMOVED - settings-helpers.php deleted

    // Récupération des paramètres depuis le tableau unifié
    $settings = get_option('pdf_builder_settings', []);
    error_log('[PDF Builder] settings-systeme.php - Full settings from DB: ' . print_r($settings, true));

    // Préparer toutes les variables nécessaires
    $cache_enabled = $settings['pdf_builder_cache_enabled'] ?? '0';
    $cache_compression = $settings['pdf_builder_cache_compression'] ?? '1';
    $cache_auto_cleanup = $settings['pdf_builder_cache_auto_cleanup'] ?? '1';
    $cache_max_size = intval($settings['pdf_builder_cache_max_size'] ?? 100);
    $cache_ttl = intval($settings['pdf_builder_cache_ttl'] ?? 3600);
    $performance_auto_optimization = $settings['pdf_builder_performance_auto_optimization'] ?? '0';
    $auto_maintenance = $settings['pdf_builder_systeme_auto_maintenance'] ?? '0';
    $last_maintenance = $settings['pdf_builder_last_maintenance'] ?? 'Jamais';
    $next_maintenance = $settings['pdf_builder_next_maintenance'] ?? 'Non planifiée';
    $last_backup = $settings['pdf_builder_last_backup'] ?? 'Jamais';
    $cache_last_cleanup = $settings['pdf_builder_cache_last_cleanup'] ?? 'Jamais';

    error_log('[PDF Builder] settings-systeme.php loaded - cache_enabled: ' . $cache_enabled . ' (type: ' . gettype($cache_enabled) . '), cache_ttl: ' . $cache_ttl);
    error_log('[PDF Builder] Toggle values - cache_enabled should be checked: ' . ($cache_enabled === '1' ? 'YES' : 'NO'));

    // Vérifier le statut premium de l'utilisateur
    $is_premium = \PDF_Builder\Admin\PdfBuilderAdmin::is_premium_user();

    // Calculer les métriques de cache
    $cache_file_count = 0;
    $cache_dirs = [
        (defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : '') . '/cache/wp-pdf-builder-previews/',
        (function_exists('wp_upload_dir') ? wp_upload_dir()['basedir'] : '') . '/pdf-builder-cache'
    ];

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

    // Calculer les transients
    $transient_count = 0;
    if (isset($GLOBALS['wpdb']) && function_exists('get_option')) {
        global $wpdb;
        try {
            $transient_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'");
        } catch (Exception $e) {
            $transient_count = 0;
        }
    }

    // Calculer le nombre de sauvegardes
    $backup_count = 0;
    $backup_dir = (function_exists('wp_upload_dir') ? wp_upload_dir()['basedir'] : '') . '/pdf-builder-backups';
    if (is_dir($backup_dir)) {
        $files = glob($backup_dir . '/*.json');
        $backup_count = count($files);
    }

    // Formater les dates
    if ($last_maintenance !== 'Jamais') {
        $last_maintenance = (function_exists('human_time_diff') ? human_time_diff(strtotime($last_maintenance)) : $last_maintenance) . ' ago';
    }
    if ($next_maintenance !== 'Non planifiée') {
        $next_maintenance = (function_exists('date_i18n') ? date_i18n('d/m/Y H:i', strtotime($next_maintenance)) : date('d/m/Y H:i', strtotime($next_maintenance)));
    }
    if ($last_backup !== 'Jamais') {
        $last_backup = (function_exists('human_time_diff') ? human_time_diff(strtotime($last_backup)) : $last_backup) . ' ago';
    }
    if ($cache_last_cleanup !== 'Jamais') {
        $cache_last_cleanup = (function_exists('human_time_diff') ? human_time_diff(strtotime($cache_last_cleanup)) : $cache_last_cleanup) . ' ago';
    }
?>
            <h2>⚙️ Système - Performance, Maintenance & Sauvegarde</h2>

                <!-- Section Cache et Performance -->
                <section id="systeme" class="system-cache-section">
                    <header>
                        <h3>
                            <span>
                                📋 Cache & Performance - ⚠️ En attente d'implémentation
                                <span class="cache-performance-status" id="cache-performance-status"><?php echo $cache_enabled ? 'ACTIF' : 'INACTIF'; ?></span>
                            </span>
                        </h3>
                    </header>

                    <div class="system-section-content">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="general_cache_enabled">Cache activé</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="hidden" name="pdf_builder_settings[pdf_builder_cache_enabled]" value="0">
                                        <input type="checkbox" id="general_cache_enabled" name="pdf_builder_settings[pdf_builder_cache_enabled]" value="1" <?php checked($cache_enabled, '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Améliore les performances en mettant en cache les données</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="cache_compression">Compression du cache</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="hidden" name="pdf_builder_settings[pdf_builder_cache_compression]" value="0">
                                        <input type="checkbox" id="cache_compression" name="pdf_builder_settings[pdf_builder_cache_compression]" value="1" <?php checked($cache_compression, '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Compresser les données en cache pour économiser l'espace disque</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="cache_auto_cleanup">Nettoyage automatique</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="hidden" name="pdf_builder_settings[pdf_builder_cache_auto_cleanup]" value="0">
                                        <input type="checkbox" id="cache_auto_cleanup" name="pdf_builder_settings[pdf_builder_cache_auto_cleanup]" value="1" <?php checked($cache_auto_cleanup, '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Nettoyer automatiquement les anciens fichiers cache</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="cache_max_size">Taille max du cache (MB)</label></th>
                                <td>
                                    <input type="number" id="cache_max_size" name="pdf_builder_settings[pdf_builder_cache_max_size]" value="<?php echo $cache_max_size; ?>" min="10" max="1000" step="10" />
                                    <p class="description">Taille maximale du dossier cache en mégaoctets</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="cache_ttl">TTL du cache (secondes)</label></th>
                                <td>
                                    <input type="number" id="cache_ttl" name="pdf_builder_settings[pdf_builder_cache_ttl]" value="<?php echo $cache_ttl; ?>" min="0" max="86400" />
                                    <p class="description">Durée de vie du cache en secondes (défaut: 3600)</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="performance_auto_optimization">Optimisation automatique des performances</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="hidden" name="pdf_builder_settings[pdf_builder_performance_auto_optimization]" value="0">
                                        <input type="checkbox" id="performance_auto_optimization" name="pdf_builder_settings[pdf_builder_performance_auto_optimization]" value="1" <?php checked($performance_auto_optimization, '1'); ?>>
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
                                <div class="cache-metric-card" data-metric="size" style="pointer-events: none; cursor: default;">
                                    <div class="metric-value">
                                        <span id="cache-size-display"><?php echo $cache_file_count; ?> fichiers</span>
                                    </div>
                                    <div class="metric-label">Taille du cache</div>
                                </div>
                                <div class="cache-metric-card" data-metric="transients" style="pointer-events: none; cursor: default;">
                                    <div class="metric-value">
                                        <?php echo intval($transient_count); ?>
                                    </div>
                                    <div class="metric-label">Transients actifs</div>
                                </div>
                                <div class="cache-metric-card systeme-cache-status" data-metric="status" style="pointer-events: none; cursor: default;">
                                    <div class="cache-enabled-indicator metric-value">
                                    <?php echo $cache_enabled ? 'Cache activé' : 'Cache désactivé'; ?>
                                    </div>
                                    <div class="metric-label">État du cache</div>
                                </div>
                                <div class="cache-metric-card" data-metric="cleanup" style="pointer-events: none; cursor: default;">
                                    <div class="metric-value">
                                        <?php echo $cache_last_cleanup; ?>
                                    </div>
                                    <div class="metric-label">Dernier nettoyage</div>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>
                <!-- Section Maintenance -->
                <section id="systeme" class="system-maintenance-section">
                    <h3>
                        <span style="display: inline-flex; align-items: center; gap: 10px;">
                            🔧 Maintenance
                            <span class="maintenance-status"><?php echo $auto_maintenance === '1' ? 'ACTIF' : 'INACTIF'; ?></span>
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
                                        <input type="hidden" name="pdf_builder_settings[pdf_builder_systeme_auto_maintenance]" value="0">
                                        <input type="checkbox" id="systeme_auto_maintenance" name="pdf_builder_settings[pdf_builder_systeme_auto_maintenance]" value="1" <?php checked($auto_maintenance, '1'); ?>>
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
                                        <?php echo $last_maintenance; ?>
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
                                        <?php echo $next_maintenance; ?>
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
                                            <?php echo $auto_maintenance === '1' ? '✅' : '❌'; ?>
                                        </div>
                                        <div class="metric-status" data-status="<?php echo $auto_maintenance === '1' ? 'active' : 'inactive'; ?>">
                                            <span class="status-indicator"></span>
                                        </div>
                                    </div>
                                    <div class="metric-value">
                                        <span class="status-badge <?php echo $auto_maintenance === '1' ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $auto_maintenance === '1' ? 'Activée' : 'Désactivée'; ?>
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
                <section id="systeme" class="system-backup-section">
                    <header>
                        <h3>
                            <span>
                                💾 Gestion des Sauvegardes
                            </span>
                        </h3>
                    </header>

                    <div class="system-section-content">
                        <?php if (!$is_premium): ?>
                        <!-- Version gratuite - Sauvegardes non disponibles -->
                        <article class="backup-info premium-feature">
                            <header>
                                <h4>🔒 Sauvegardes - Fonctionnalité Premium</h4>
                            </header>
                            <div class="premium-feature-content" style="padding: 20px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 2px solid #007cba; border-radius: 8px; text-align: center;">
                                <div style="font-size: 48px; margin-bottom: 15px;">⭐</div>
                                <h3 style="color: #007cba; margin: 0 0 15px 0;">Sauvegardes Disponibles en Version Premium</h3>
                                <p style="margin: 0 0 20px 0; color: #495057; font-size: 16px;">
                                    La fonctionnalité de sauvegarde n'est disponible que dans la version premium de PDF Builder Pro.
                                </p>
                                <ul style="text-align: left; display: inline-block; margin: 0 0 20px 0; padding-left: 20px;">
                                    <li>Création de sauvegardes illimitée de vos paramètres</li>
                                    <li>Restauration facile en cas de problème</li>
                                    <li>Support technique prioritaire</li>
                                    <li>Fonctionnalités avancées supplémentaires</li>
                                </ul>
                                <a href="<?php echo admin_url('admin.php?page=pdf-builder-settings&tab=licence'); ?>" class="button button-primary button-large" style="background: #007cba; border-color: #007cba;">
                                    🚀 Passer à la Version Premium
                                </a>
                            </div>
                        </article>
                        <?php else: ?>
                        <!-- Version premium - Sauvegardes disponibles -->
                        <!-- Informations sur les sauvegardes -->
                        <article class="backup-info">
                            <header>
                                <h4>ℹ️ Informations</h4>
                            </header>
                            <ul>
                                <li>Les sauvegardes contiennent tous vos paramètres PDF Builder</li>
                                <li>Créez des sauvegardes manuellement selon vos besoins</li>
                                <li>Limite : 50 sauvegardes maximum</li>
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
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Sauvegardes disponibles</th>
                                <td>
                                    <div class="main-backup-accordion" style="border: 1px solid #dee2e6; border-radius: 4px; margin-top: 10px;">
                                        <div class="main-backup-accordion-header" style="padding: 12px 15px; background: #f8f9fa; cursor: pointer; display: flex; align-items: center; justify-content: space-between;" id="main-backup-accordion-header">
                                            <div class="main-backup-header-info" class="flex-center-gap">
                                                <span style="font-size: 18px;">📦</span>
                                                <div>
                                                    <strong style="color: #007cba;">Sauvegardes disponibles</strong>
                                                    <div style="font-size: 12px; color: #6c757d; margin-top: 2px;" id="backup-count-info">Chargement...</div>
                                                </div>
                                            </div>
                                            <div class="main-backup-accordion-toggle" style="transition: transform 0.2s;">▼</div>
                                        </div>
                                        <div id="main-backup-accordion-content" class="main-backup-accordion-content" style="display: none; padding: 15px; background: white; border-top: 1px solid #dee2e6;">
                                            <div id="backup-accordion-container">
                                                <div style="text-align: center; padding: 20px; color: #6c757d;">
                                                    <div style="font-size: 24px; margin-bottom: 10px;">⏳</div>
                                                    <div>Chargement des sauvegardes...</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
                                        <?php echo $last_backup; ?>
                                    </div>
                                    <div class="metric-label">Dernière sauvegarde</div>
                                    <div class="metric-hint">Cliquez pour créer</div>
                                </div>
                                <div class="backup-metric-card" data-metric="total-backups">
                                    <div class="metric-value">
                                        <?php echo $backup_count; ?>
                                    </div>
                                    <div class="metric-label">Total sauvegardes</div>
                                    <div class="metric-hint">Cliquez pour lister</div>
                                </div>
                            </div>
                        </article>
                        <?php endif; ?>
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

            <!-- Modales de cache et canvas - DÉPLACÉES vers la fin de settings-main.php pour éviter les conflits de structure -->
            <?php // require_once __DIR__ . '/settings-modals.php'; // Désactivé - les modales sont maintenant dans settings-main.php ?>

<script type="text/javascript">
(function($) {
    'use strict';

    // Gestionnaire pour le bouton de test du cache
    $('#test-cache-btn').on('click', function(e) {
        e.preventDefault();

        const $btn = $(this);
        const $results = $('#cache-test-results');
        const $output = $('#cache-test-output');

        // Désactiver le bouton pendant le test
        $btn.prop('disabled', true).text('🧪 Test en cours...');
        $results.html('<span style="color: #007cba;">Test en cours...</span>');
        $output.empty();

        // Générer un nonce pour la requête
        const nonce = '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>';

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_test_cache_integration',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    // Afficher les résultats détaillés
                    let output = '<div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-left: 4px solid #007cba;">';
                    output += '<h4>Résultats du test :</h4>';
                    output += '<ul style="margin: 0; padding-left: 20px;">';

                    $.each(response.data.results, function(test, result) {
                        output += '<li>' + test.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) + ': ' + result + '</li>';
                    });

                    output += '</ul></div>';

                    $output.html(output);
                    $results.html('<span style="color: #28a745;">✅ Test terminé</span>');

                    // Notification de succès
                    showSystemNotification(response.data.message, 'success');
                } else {
                    $results.html('<span style="color: #dc3545;">❌ Erreur</span>');
                    showSystemNotification('Erreur lors du test du cache', 'error');
                }
            },
            error: function(xhr, status, error) {
                $results.html('<span style="color: #dc3545;">❌ Erreur de connexion</span>');
                $output.html('<div style="color: #dc3545; margin-top: 10px;">Erreur AJAX: ' + error + '</div>');
                showSystemNotification('Erreur de connexion lors du test du cache', 'error');
            },
            complete: function() {
                // Réactiver le bouton
                $btn.prop('disabled', false).text('🧪 Tester l\'intégration du cache');
            }
        });
    });

    // Gestionnaire pour le bouton de vidage du cache
    $('#clear-cache-general-btn').on('click', function(e) {
        e.preventDefault();

        if (!confirm('Êtes-vous sûr de vouloir vider tout le cache ? Cette action est irréversible.')) {
            return;
        }

        const $btn = $(this);
        const $results = $('#clear-cache-general-results');

        // Désactiver le bouton pendant le vidage
        $btn.prop('disabled', true).text('🗑️ Vidage en cours...');
        $results.html('<span style="color: #007cba;">Vidage en cours...</span>');

        // Générer un nonce pour la requête
        const nonce = '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>';

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_clear_cache',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    $results.html('<span style="color: #28a745;">✅ Cache vidé avec succès</span>');

                    // Mettre à jour l'affichage des métriques de cache
                    $('#cache-size-display').text('0 fichiers');

                    // Notification de succès
                    showSystemNotification(response.data.message, 'success');
                } else {
                    $results.html('<span style="color: #dc3545;">❌ Erreur lors du vidage</span>');
                    showSystemNotification('Erreur lors du vidage du cache', 'error');
                }
            },
            error: function(xhr, status, error) {
                $results.html('<span style="color: #dc3545;">❌ Erreur de connexion</span>');
                showSystemNotification('Erreur de connexion lors du vidage du cache', 'error');
            },
            complete: function() {
                // Réactiver le bouton
                $btn.prop('disabled', false).text('🗑️ Vider tout le cache');
            }
        });
    });

    // Gestionnaire pour le bouton d'optimisation de la base de données
    $('#optimize-db-btn').on('click', function(e) {
        e.preventDefault();

        const $btn = $(this);
        const $results = $('#maintenance-results');

        // Désactiver le bouton pendant l'optimisation
        $btn.prop('disabled', true).text('🗃️ Optimisation en cours...');
        $results.html('<span style="color: #007cba;">Optimisation de la base de données en cours...</span>');

        // Générer un nonce pour la requête
        const nonce = '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>';

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_optimize_database',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    $results.html('<div style="color: #28a745; margin-top: 10px; white-space: pre-line;">' + response.data.message + '</div>');
                    showSystemNotification('Base de données optimisée avec succès', 'success');
                } else {
                    $results.html('<div style="color: #dc3545; margin-top: 10px;">❌ Erreur lors de l\'optimisation</div>');
                    showSystemNotification('Erreur lors de l\'optimisation de la base', 'error');
                }
            },
            error: function(xhr, status, error) {
                $results.html('<div style="color: #dc3545; margin-top: 10px;">❌ Erreur de connexion</div>');
                showSystemNotification('Erreur de connexion lors de l\'optimisation', 'error');
            },
            complete: function() {
                // Réactiver le bouton
                $btn.prop('disabled', false).text('🗃️ Optimiser la base');
            }
        });
    });

    // Gestionnaire pour le bouton de réparation des templates
    $('#repair-templates-btn').on('click', function(e) {
        e.preventDefault();

        const $btn = $(this);
        const $results = $('#maintenance-results');

        // Désactiver le bouton pendant la réparation
        $btn.prop('disabled', true).text('🔧 Réparation en cours...');
        $results.html('<span style="color: #007cba;">Vérification et réparation des templates en cours...</span>');

        // Générer un nonce pour la requête
        const nonce = '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>';

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_repair_templates',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    $results.html('<div style="color: #28a745; margin-top: 10px; white-space: pre-line;">' + response.data.message + '</div>');
                    showSystemNotification('Templates vérifiés et réparés', 'success');
                } else {
                    $results.html('<div style="color: #dc3545; margin-top: 10px;">❌ Erreur lors de la réparation</div>');
                    showSystemNotification('Erreur lors de la réparation des templates', 'error');
                }
            },
            error: function(xhr, status, error) {
                $results.html('<div style="color: #dc3545; margin-top: 10px;">❌ Erreur de connexion</div>');
                showSystemNotification('Erreur de connexion lors de la réparation', 'error');
            },
            complete: function() {
                // Réactiver le bouton
                $btn.prop('disabled', false).text('🔧 Réparer les templates');
            }
        });
    });

    // Gestionnaire pour le bouton de suppression des fichiers temporaires
    $('#remove-temp-btn').on('click', function(e) {
        e.preventDefault();

        if (!confirm('Êtes-vous sûr de vouloir supprimer tous les fichiers temporaires ? Cette action est irréversible.')) {
            return;
        }

        const $btn = $(this);
        const $results = $('#maintenance-results');

        // Désactiver le bouton pendant le nettoyage
        $btn.prop('disabled', true).text('🗂️ Suppression en cours...');
        $results.html('<span style="color: #007cba;">Suppression des fichiers temporaires en cours...</span>');

        // Générer un nonce pour la requête
        const nonce = '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>';

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_remove_temp_files',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    $results.html('<div style="color: #28a745; margin-top: 10px; white-space: pre-line;">' + response.data.message + '</div>');
                    showSystemNotification('Fichiers temporaires supprimés', 'success');
                } else {
                    $results.html('<div style="color: #dc3545; margin-top: 10px;">❌ Erreur lors de la suppression</div>');
                    showSystemNotification('Erreur lors de la suppression des fichiers temporaires', 'error');
                }
            },
            error: function(xhr, status, error) {
                $results.html('<div style="color: #dc3545; margin-top: 10px;">❌ Erreur de connexion</div>');
                showSystemNotification('Erreur de connexion lors de la suppression', 'error');
            },
            complete: function() {
                // Réactiver le bouton
                $btn.prop('disabled', false).text('🗂️ Supprimer fichiers temp');
            }
        });
    });

    // Fonction utilitaire pour afficher les notifications
    function showSystemNotification(message, type = 'info') {
        // Utiliser les fonctions de notification globales
        if (type === 'success' && window.showSuccessNotification) {
            window.showSuccessNotification(message);
        } else if (type === 'error' && window.showErrorNotification) {
            window.showErrorNotification(message);
        } else if (type === 'warning' && window.showWarningNotification) {
            window.showWarningNotification(message);
        } else if (type === 'info' && window.showInfoNotification) {
            window.showInfoNotification(message);
        } else {
            // Fallback: créer une notification temporaire
            const notification = $('<div class="system-notification ' + type + '">' + message + '</div>');
            notification.css({
                'position': 'fixed',
                'top': '40px',
                'right': '20px',
                'background': type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#007cba',
                'color': 'white',
                'padding': '12px 20px',
                'border-radius': '4px',
                'box-shadow': '0 2px 10px rgba(0,0,0,0.2)',
                'z-index': '9999',
                'max-width': '400px'
            });

            $('body').append(notification);

            // Animation d'entrée
            notification.animate({right: '20px', opacity: 1}, 300);

            // Auto-suppression après 5 secondes
            setTimeout(function() {
                notification.animate({right: '-400px', opacity: 0}, 300, function() {
                    notification.remove();
                });
            }, 5000);
        }
    }

    // Gestionnaire pour les boutons de maintenance
    $('.maintenance-action-btn').on('click', function(e) {
        e.preventDefault();

        const $btn = $(this);
        const action = $btn.data('action');
        const metric = $btn.data('metric');

        // Générer un nonce pour la requête
        const nonce = '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>';

        // Désactiver le bouton pendant l'action
        $btn.prop('disabled', true);
        const originalText = $btn.find('.action-text').text();
        $btn.find('.action-text').text('En cours...');

        let ajaxAction = '';
        let confirmMessage = '';

        switch(action) {
            case 'run-maintenance':
                ajaxAction = 'pdf_builder_run_full_maintenance';
                confirmMessage = 'Êtes-vous sûr de vouloir lancer la maintenance complète ?';
                break;
            case 'schedule-maintenance':
                ajaxAction = 'pdf_builder_schedule_maintenance';
                confirmMessage = 'Programmer la prochaine maintenance automatique ?';
                break;
            case 'toggle-maintenance':
                ajaxAction = 'pdf_builder_toggle_auto_maintenance';
                break;
            case 'run-manual-maintenance':
                ajaxAction = 'pdf_builder_run_manual_maintenance';
                confirmMessage = 'Lancer la maintenance manuelle complète ?';
                break;
            default:
                showSystemNotification('Action non reconnue', 'error');
                $btn.prop('disabled', false);
                $btn.find('.action-text').text(originalText);
                return;
        }

        // Demander confirmation si nécessaire
        if (confirmMessage && !confirm(confirmMessage)) {
            $btn.prop('disabled', false);
            $btn.find('.action-text').text(originalText);
            return;
        }

        // Pour la maintenance complète, exécuter les trois actions séquentiellement
        if (action === 'run-maintenance' || action === 'run-manual-maintenance') {
            runFullMaintenance($btn, originalText, nonce);
        } else {
            // Actions simples
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: ajaxAction,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        showSystemNotification(response.data.message || 'Action exécutée avec succès', 'success');
                        
                        // Mettre à jour l'affichage si nécessaire
                        if (action === 'toggle-maintenance') {
                            // Basculer l'état visuel
                            const $icon = $btn.find('.metric-icon');
                            const $status = $btn.find('.metric-status');
                            const $value = $btn.find('.metric-value .status-badge');
                            
                            if ($icon.text() === '✅') {
                                $icon.text('❌');
                                $status.attr('data-status', 'inactive');
                                $value.removeClass('status-active').addClass('status-inactive').text('Désactivée');
                            } else {
                                $icon.text('✅');
                                $status.attr('data-status', 'active');
                                $value.removeClass('status-inactive').addClass('status-active').text('Activée');
                            }
                        } else if (action === 'schedule-maintenance') {
                            // Mettre à jour la date de prochaine maintenance
                            if (response.data.next_maintenance) {
                                $btn.find('.metric-value').text(response.data.next_maintenance);
                                $btn.find('.metric-status').attr('data-status', 'scheduled');
                            }
                        }
                    } else {
                        showSystemNotification(response.data.message || 'Erreur lors de l\'action', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    showSystemNotification('Erreur de connexion: ' + error, 'error');
                },
                complete: function() {
                    // Réactiver le bouton
                    $btn.prop('disabled', false);
                    $btn.find('.action-text').text(originalText);
                }
            });
        }
    });

    // Fonction pour exécuter la maintenance complète (optimisation DB + réparation templates + suppression fichiers temp)
    function runFullMaintenance($btn, originalText, nonce) {
        const actions = [
            { action: 'pdf_builder_optimize_database', name: 'Optimisation DB' },
            { action: 'pdf_builder_repair_templates', name: 'Réparation templates' },
            { action: 'pdf_builder_remove_temp_files', name: 'Suppression fichiers temp' }
        ];
        
        let currentAction = 0;
        let results = [];
        
        function executeNextAction() {
            if (currentAction >= actions.length) {
                // Toutes les actions terminées
                let message = 'Maintenance complète terminée:\n';
                results.forEach(result => {
                    message += '• ' + result.name + ': ' + result.status + '\n';
                });
                showSystemNotification(message, 'success');
                
                // Mettre à jour l'affichage de la dernière exécution
                const now = new Date();
                const timeString = now.toLocaleString('fr-FR', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                $('.maintenance-metric-card[data-metric="last-run"] .metric-value').text('À l\'instant');
                
                $btn.prop('disabled', false);
                $btn.find('.action-text').text(originalText);
                return;
            }
            
            const current = actions[currentAction];
            $btn.find('.action-text').text(current.name + '...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: current.action,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        results.push({ name: current.name, status: '✅ Succès' });
                    } else {
                        results.push({ name: current.name, status: '❌ Échec: ' + (response.data.message || 'Erreur inconnue') });
                    }
                },
                error: function(xhr, status, error) {
                    results.push({ name: current.name, status: '❌ Erreur: ' + error });
                },
                complete: function() {
                    currentAction++;
                    executeNextAction();
                }
            });
        }
        
        executeNextAction();
    }

    // Gestionnaire pour le bouton de création de sauvegarde
    $('#create-backup-btn').on('click', function(e) {
        e.preventDefault();

        const $btn = $(this);

        // Désactiver le bouton pendant la création
        $btn.prop('disabled', true).html('<span>📦</span> Création en cours...');

        // Générer un nonce pour la requête
        const nonce = '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>';

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_create_backup',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    showSystemNotification(response.data.message, 'success');
                    // Recharger automatiquement l'accordéon des sauvegardes
                    loadBackupsOnPageLoad();
                } else {
                    showSystemNotification('Erreur lors de la création de la sauvegarde', 'error');
                }
            },
            error: function(xhr, status, error) {
                showSystemNotification('Erreur de connexion lors de la création de la sauvegarde', 'error');
            },
            complete: function() {
                // Réactiver le bouton
                $btn.prop('disabled', false).html('<span>📦</span> Créer une sauvegarde');
            }
        });
    });

    // Gestionnaire pour l'accordéon principal des sauvegardes
    $('#main-backup-accordion-header').on('click', function() {
        const content = $('#main-backup-accordion-content');
        const toggle = $('.main-backup-accordion-toggle');

        if (content.is(':visible')) {
            content.slideUp(200);
            toggle.css('transform', 'rotate(0deg)');
        } else {
            content.slideDown(200);
            toggle.css('transform', 'rotate(180deg)');
        }
    });

    // Fonction pour gérer l'accordéon principal des sauvegardes
    function toggleMainBackupAccordion() {
        const content = $('#main-backup-accordion-content');
        const toggle = $('.main-backup-accordion-toggle');

        if (content.is(':visible')) {
            content.slideUp(200);
            toggle.css('transform', 'rotate(0deg)');
        } else {
            content.slideDown(200);
            toggle.css('transform', 'rotate(180deg)');
        }
    }

    // Chargement automatique des sauvegardes au démarrage
    
    loadBackupsOnPageLoad();

    function loadBackupsOnPageLoad() {
        const $container = $('#backup-accordion-container');

        // Générer un nonce pour la requête
        const nonce = '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>';

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_list_backups',
                nonce: nonce
            },
            success: function(response) {
                
                if (response.success) {
                    
                    let output = '';

                    if (response.data.backups && response.data.backups.length > 0) {
                        

                        // Mettre à jour le compteur dans le header
                        const backupCount = response.data.backups.length;
                        const countText = backupCount + ' sauvegarde' + (backupCount > 1 ? 's' : '') + ' disponible' + (backupCount > 1 ? 's' : '');
                        $('#backup-count-info').text(countText);

                        // Vérifier la limite de 50 sauvegardes
                        if (backupCount >= 50) {
                            $('#create-backup-btn').prop('disabled', true).html('<span>📦</span> Limite atteinte (50 max)');
                            $('#create-backup-btn').attr('title', 'Vous avez atteint la limite de 50 sauvegardes. Supprimez des sauvegardes anciennes pour en créer de nouvelles.');
                        } else {
                            $('#create-backup-btn').prop('disabled', false).html('<span>📦</span> Créer une sauvegarde');
                            $('#create-backup-btn').removeAttr('title');
                        }

                        output += '<div class="backup-list" style="margin-top: 15px;">';
                        output += '<style>.backup-item-info { margin-bottom: 0 !important; }</style>';
                        output += '<style>@keyframes fadeInOut { 0% { opacity: 0; transform: scale(0.8); } 20% { opacity: 1; transform: scale(1.1); } 80% { opacity: 1; transform: scale(1); } 100% { opacity: 0; transform: scale(0.8); } } .backup-count-plus-one { animation: fadeInOut 3s ease-in-out; }</style>';

                        response.data.backups.forEach(function(backup, index) {
                            
                            output += '<div class="backup-item" style="display: flex; align-items: center; justify-content: space-between; padding: 12px; margin-bottom: 10px; background: white; border: 1px solid #dee2e6; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
                            output += '<div class="backup-item-info" style="flex: 1; display: flex; align-items: center; gap: 12px;">';
                            output += '<div class="backup-icon" style="font-size: 20px;">📄</div>';
                            output += '<div class="backup-details">';
                            output += '<div class="backup-filename" style="font-weight: 600; color: #007cba; margin-bottom: 2px;">' + backup.filename + '</div>';
                            output += '<div class="backup-meta" style="font-size: 12px; color: #6c757d;">' + backup.size_human + ' • ' + backup.modified_human + ' • ' + backup.type.toUpperCase() + '</div>';
                            output += '</div>';
                            output += '</div>';
                            output += '<div class="backup-actions" style="display: flex; gap: 8px;">';
                            output += '<button type="button" class="button button-small restore-backup-btn" data-filename="' + backup.filename + '" style="background: #28a745; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">🔄 Restaurer</button>';
                            output += '<button type="button" class="button button-small download-backup-btn" data-filename="' + backup.filename + '" style="background: #007cba; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">📥 Télécharger</button>';
                            output += '<button type="button" class="button button-small delete-backup-btn" data-filename="' + backup.filename + '" style="background: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">🗑️ Supprimer</button>';
                            output += '</div>';
                            
                            output += '</div>';
                        });

                        output += '</div>';
                    } else {
                        

                        // Mettre à jour le compteur dans le header
                        $('#backup-count-info').text('0 sauvegarde disponible');

                        // Activer le bouton de création si pas à la limite
                        $('#create-backup-btn').prop('disabled', false).html('<span>📦</span> Créer une sauvegarde');
                        $('#create-backup-btn').removeAttr('title');

                        output += '<div style="padding: 20px; text-align: center; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; color: #6c757d;">';
                        output += '<div style="font-size: 48px; margin-bottom: 10px;">📦</div>';
                        output += '<h4 style="margin: 0 0 10px 0; color: #495057;">Aucune sauvegarde trouvée</h4>';
                        output += '<p style="margin: 0; font-style: italic;">Créez votre première sauvegarde en utilisant le bouton "Créer une sauvegarde" ci-dessus.</p>';
                        output += '</div>';
                    }

                    
                    $container.html(output);
                    

                    // Ouvrir automatiquement l'accordéon principal après le chargement
                    setTimeout(function() {
                        $('#main-backup-accordion-content').slideDown(300);
                        $('.main-backup-accordion-toggle').css('transform', 'rotate(180deg)');
                    }, 500);
                } else {
                    
                    $('#backup-count-info').text('Erreur de chargement');
                    
                    // Désactiver le bouton si erreur (probablement pas premium)
                    $('#create-backup-btn').prop('disabled', true).html('<span>🔒</span> Premium requis');
                    $('#create-backup-btn').attr('title', 'La fonctionnalité de sauvegarde nécessite la version premium.');
                    
                    $container.html('<div style="color: #dc3545; padding: 20px; text-align: center;">❌ Erreur lors du chargement des sauvegardes</div>');
                }
            },
            error: function(xhr, status, error) {
                
                $('#backup-count-info').text('Erreur de connexion');
                $container.html('<div style="color: #dc3545; padding: 20px; text-align: center;">❌ Erreur de connexion lors du chargement des sauvegardes</div>');
            }
        });
    }

    // Gestionnaire pour les boutons de restauration de sauvegarde
    $(document).on('click', '.restore-backup-btn', function(e) {
        e.preventDefault();

        const filename = $(this).data('filename');
        const $btn = $(this);

        if (!confirm('Êtes-vous sûr de vouloir restaurer la sauvegarde "' + filename + '" ? Cette action écrasera les paramètres actuels.')) {
            return;
        }

        // Désactiver le bouton pendant la restauration
        $btn.prop('disabled', true).text('🔄 Restauration...');

        // Générer un nonce pour la requête
        const nonce = '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>';

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_restore_backup',
                filename: filename,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    showSystemNotification(response.data.message, 'success');
                    // Recharger la page pour appliquer les nouveaux paramètres
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showSystemNotification(response.data.message || 'Erreur lors de la restauration', 'error');
                    $btn.prop('disabled', false).text('🔄 Restaurer');
                }
            },
            error: function(xhr, status, error) {
                showSystemNotification('Erreur de connexion lors de la restauration', 'error');
                $btn.prop('disabled', false).text('🔄 Restaurer');
            }
        });
    });

    // Gestionnaire pour les boutons de téléchargement de sauvegarde
    $(document).on('click', '.download-backup-btn', function(e) {
        e.preventDefault();

        const filename = $(this).data('filename');
        const $btn = $(this);

        // Désactiver le bouton pendant le téléchargement
        $btn.prop('disabled', true).text('📥 Téléchargement...');

        // Générer un nonce pour la requête
        const nonce = '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>';

        // Créer un formulaire temporaire pour le téléchargement
        const form = $('<form>', {
            method: 'POST',
            action: ajaxurl,
            style: 'display: none;'
        });

        form.append($('<input>', {
            type: 'hidden',
            name: 'action',
            value: 'pdf_builder_download_backup'
        }));

        form.append($('<input>', {
            type: 'hidden',
            name: 'filename',
            value: filename
        }));

        form.append($('<input>', {
            type: 'hidden',
            name: 'nonce',
            value: nonce
        }));

        $('body').append(form);
        form.submit();
        form.remove();

        // Réactiver le bouton après un court délai
        setTimeout(function() {
            $btn.prop('disabled', false).text('📥 Télécharger');
        }, 1000);

        showSystemNotification('Téléchargement de la sauvegarde démarré', 'success');
    });

    // Gestionnaire pour les boutons de suppression de sauvegarde
    $(document).on('click', '.delete-backup-btn', function(e) {
        e.preventDefault();

        const filename = $(this).data('filename');
        const $btn = $(this);

        if (!confirm('Êtes-vous sûr de vouloir supprimer définitivement la sauvegarde "' + filename + '" ? Cette action est irréversible.')) {
            return;
        }

        // Désactiver le bouton pendant la suppression
        $btn.prop('disabled', true).text('🗑️ Suppression...');

        // Générer un nonce pour la requête
        const nonce = '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>';

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_delete_backup',
                filename: filename,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    // Supprimer l'élément de la liste
                    $btn.closest('.backup-item').fadeOut(300, function() {
                        $(this).remove();
                        // Mettre à jour le compteur de sauvegardes
                        const remainingItems = $('.backup-item').length;
                        if (remainingItems === 0) {
                            // Aucune sauvegarde restante, afficher le message approprié
                            $('#backup-accordion-container').html('<div style="padding: 20px; text-align: center; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; color: #6c757d;"><div style="font-size: 48px; margin-bottom: 10px;">📦</div><h4 style="margin: 0 0 10px 0; color: #495057;">Aucune sauvegarde trouvée</h4><p style="margin: 0; font-style: italic;">Créez votre première sauvegarde en utilisant le bouton "Créer une sauvegarde" ci-dessus.</p></div>');
                            $('#backup-count-info').text('0 sauvegarde disponible');
                        } else {
                            // Mettre à jour le compteur
                            const countText = remainingItems + ' sauvegarde' + (remainingItems > 1 ? 's' : '') + ' disponible' + (remainingItems > 1 ? 's' : '');
                            $('#backup-count-info').text(countText);
                        }
                    });
                    showSystemNotification(response.data.message, 'success');
                } else {
                    showSystemNotification(response.data.message || 'Erreur lors de la suppression', 'error');
                    $btn.prop('disabled', false).text('🗑️ Supprimer');
                }
            },
            error: function(xhr, status, error) {
                showSystemNotification('Erreur de connexion lors de la suppression', 'error');
                $btn.prop('disabled', false).text('🗑️ Supprimer');
            }
        });
    });

    // Mise à jour dynamique des indicateurs de statut
    function updateStatusIndicators() {
        // Mettre à jour le statut du cache
        const cacheEnabled = document.getElementById('general_cache_enabled').checked;
        const statusElement = document.getElementById('cache-performance-status');
        const cacheIndicator = document.querySelector('.cache-enabled-indicator');

        if (statusElement) {
            statusElement.textContent = cacheEnabled ? 'ACTIF' : 'INACTIF';
            statusElement.style.backgroundColor = cacheEnabled ? '#28a745' : '#dc3545';
        }

        if (cacheIndicator) {
            cacheIndicator.textContent = cacheEnabled ? 'Cache activé' : 'Cache désactivé';
        }
    }

    // Écouter les changements sur les toggles du cache
    document.getElementById('general_cache_enabled').addEventListener('change', updateStatusIndicators);
    document.getElementById('cache_compression').addEventListener('change', updateStatusIndicators);
    document.getElementById('cache_auto_cleanup').addEventListener('change', updateStatusIndicators);

    // Initialiser les indicateurs au chargement
    updateStatusIndicators();

})(jQuery);
</script>

