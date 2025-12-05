<?php
if (!defined('ABSPATH')) exit('No direct access');
if (!is_user_logged_in() || !current_user_can('manage_options')) wp_die('Access denied');
?>

<div class="pdf-builder-settings-section">
    <h2>🔧 Système de diagnostic Cron (onglet temporaire)</h2>

    <div class="notice notice-info">
        <p><strong>ℹ️ Onglet temporaire :</strong> Cet onglet contient le système de diagnostic et réparation du cron système. Il sera intégré dans l'onglet "Système" une fois testé.</p>
    </div>

    <div class="pdf-builder-card">
        <h3>Configuration des sauvegardes</h3>

        <table class="form-table">
            <tr>
                <th scope="row">Fréquence des sauvegardes</th>
                <td>
                    <select id="backup_frequency" name="pdf_builder_backup_frequency">
                        <option value="every_minute" <?php selected(get_option('pdf_builder_backup_frequency', 'daily'), 'every_minute'); ?>>Toutes les minutes (test)</option>
                        <option value="hourly" <?php selected(get_option('pdf_builder_backup_frequency', 'daily'), 'hourly'); ?>>Toutes les heures</option>
                        <option value="twicedaily" <?php selected(get_option('pdf_builder_backup_frequency', 'daily'), 'twicedaily'); ?>>Deux fois par jour</option>
                        <option value="daily" <?php selected(get_option('pdf_builder_backup_frequency', 'daily'), 'daily'); ?>>Quotidienne</option>
                        <option value="weekly" <?php selected(get_option('pdf_builder_backup_frequency', 'daily'), 'weekly'); ?>>Hebdomadaire</option>
                    </select>
                    <p class="description">Détermine la fréquence des sauvegardes automatiques.</p>
                </td>
            </tr>
            <tr>
                <th scope="row">Sauvegardes automatiques activées</th>
                <td>
                    <label>
                        <input type="checkbox" id="auto_backup_enabled" name="pdf_builder_auto_backup_enabled" value="1" <?php checked(get_option('pdf_builder_auto_backup_enabled', '1'), '1'); ?>>
                        Activer les sauvegardes automatiques
                    </label>
                    <p class="description">Active ou désactive le système de sauvegarde automatique.</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="pdf-builder-card">
        <h3>Statut du système</h3>

        <div id="system-status">
            <p>Chargement des informations système...</p>
        </div>

        <div class="system-actions" style="margin-top: 20px;">
            <button type="button" id="diagnose-cron-btn" class="button">🔍 Diagnostiquer le système Cron</button>
            <button type="button" id="repair-cron-btn" class="button button-primary">🔧 Réparer le système Cron</button>
            <button type="button" id="refresh-stats-btn" class="button">📊 Actualiser les statistiques</button>
        </div>
    </div>

    <div class="pdf-builder-card">
        <h3>Actions manuelles</h3>

        <div class="manual-actions">
            <button type="button" id="create-backup-now-btn" class="button button-primary">📦 Créer une sauvegarde maintenant</button>
            <button type="button" id="test-fallback-btn" class="button">🧪 Tester le système de fallback</button>
        </div>

        <div id="backup-result" style="margin-top: 10px;"></div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Charger les statistiques au chargement de la page
    loadBackupStats();

    // Diagnostiquer le système cron
    $('#diagnose-cron-btn').on('click', function() {
        $(this).prop('disabled', true).text('🔍 Diagnostic en cours...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_diagnose_cron',
                nonce: '<?php echo wp_create_nonce('pdf_builder_admin_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    displayDiagnosisResults(response.data);
                } else {
                    alert('Erreur lors du diagnostic: ' + response.data);
                }
            },
            error: function() {
                alert('Erreur de communication avec le serveur');
            },
            complete: function() {
                $('#diagnose-cron-btn').prop('disabled', false).text('🔍 Diagnostiquer le système Cron');
            }
        });
    });

    // Réparer le système cron
    $('#repair-cron-btn').on('click', function() {
        if (!confirm('Cette action va tenter de réparer le système cron. Continuer ?')) return;

        $(this).prop('disabled', true).text('🔧 Réparation en cours...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_repair_cron',
                nonce: '<?php echo wp_create_nonce('pdf_builder_admin_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert('Système cron réparé avec succès!\n\n' + response.data.join('\n'));
                    loadBackupStats(); // Actualiser les stats
                } else {
                    alert('Erreur lors de la réparation: ' + response.data);
                }
            },
            error: function() {
                alert('Erreur de communication avec le serveur');
            },
            complete: function() {
                $('#repair-cron-btn').prop('disabled', false).text('🔧 Réparer le système Cron');
            }
        });
    });

    // Actualiser les statistiques
    $('#refresh-stats-btn').on('click', function() {
        loadBackupStats();
    });

    // Créer une sauvegarde manuelle
    $('#create-backup-now-btn').on('click', function() {
        $(this).prop('disabled', true).text('📦 Création en cours...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_create_backup',
                nonce: '<?php echo wp_create_nonce('pdf_builder_admin_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $('#backup-result').html('<div class="notice notice-success"><p>✅ Sauvegarde créée avec succès!</p></div>');
                    loadBackupStats(); // Actualiser les stats
                } else {
                    $('#backup-result').html('<div class="notice notice-error"><p>❌ Erreur lors de la création: ' + response.data + '</p></div>');
                }
            },
            error: function() {
                $('#backup-result').html('<div class="notice notice-error"><p>❌ Erreur de communication avec le serveur</p></div>');
            },
            complete: function() {
                $('#create-backup-now-btn').prop('disabled', false).text('📦 Créer une sauvegarde maintenant');
            }
        });
    });

    // Tester le système de fallback
    $('#test-fallback-btn').on('click', function() {
        $(this).prop('disabled', true).text('🧪 Test en cours...');

        // Simuler une visite admin pour déclencher le fallback
        $.ajax({
            url: window.location.href,
            type: 'GET',
            success: function() {
                $('#backup-result').html('<div class="notice notice-info"><p>🧪 Test du fallback terminé. Vérifiez les logs pour les détails.</p></div>');
            },
            error: function() {
                $('#backup-result').html('<div class="notice notice-error"><p>❌ Erreur lors du test du fallback</p></div>');
            },
            complete: function() {
                $('#test-fallback-btn').prop('disabled', false).text('🧪 Tester le système de fallback');
            }
        });
    });

    function loadBackupStats() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_get_backup_stats',
                nonce: '<?php echo wp_create_nonce('pdf_builder_admin_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    displayBackupStats(response.data);
                }
            }
        });
    }

    function displayBackupStats(stats) {
        let html = '<table class="widefat striped">';
        html += '<thead><tr><th>Statistique</th><th>Valeur</th></tr></thead>';
        html += '<tbody>';
        html += '<tr><td>Nombre total de sauvegardes</td><td>' + stats.total_backups + '</td></tr>';
        html += '<tr><td>Dernière sauvegarde</td><td>' + (stats.last_backup || 'Aucune') + '</td></tr>';
        html += '<tr><td>Prochaine sauvegarde</td><td>' + (stats.next_backup || 'Non planifiée') + '</td></tr>';
        html += '<tr><td>Fréquence</td><td>' + stats.backup_frequency + '</td></tr>';
        html += '<tr><td>Statut du cron</td><td>' + (stats.cron_status === 'active' ? '✅ Actif' : '❌ Inactif') + '</td></tr>';
        html += '<tr><td>Exécutions de fallback</td><td>' + stats.fallback_executions + '</td></tr>';

        if (stats.errors && stats.errors.length > 0) {
            html += '<tr><td>Erreurs récentes</td><td>';
            html += '<details><summary>' + stats.errors.length + ' erreur(s)</summary>';
            html += '<ul>';
            stats.errors.forEach(function(error) {
                html += '<li>' + error + '</li>';
            });
            html += '</ul></details>';
            html += '</td></tr>';
        }

        html += '</tbody></table>';

        $('#system-status').html(html);
    }

    function displayDiagnosisResults(diagnosis) {
        let html = '<div class="notice ' + (diagnosis.issues.length > 0 ? 'notice-warning' : 'notice-success') + '">';
        html += '<h4>Résultats du diagnostic:</h4>';

        if (diagnosis.issues.length > 0) {
            html += '<h5>⚠️ Problèmes détectés:</h5><ul>';
            diagnosis.issues.forEach(function(issue) {
                html += '<li>' + issue + '</li>';
            });
            html += '</ul>';
        } else {
            html += '<p>✅ Aucun problème détecté dans le système cron.</p>';
        }

        if (diagnosis.recommendations.length > 0) {
            html += '<h5>💡 Recommandations:</h5><ul>';
            diagnosis.recommendations.forEach(function(rec) {
                html += '<li>' + rec + '</li>';
            });
            html += '</ul>';
        }

        html += '<h5>📋 Tâches planifiées:</h5><ul>';
        if (diagnosis.scheduled_tasks.length > 0) {
            diagnosis.scheduled_tasks.forEach(function(task) {
                html += '<li>' + task + '</li>';
            });
        } else {
            html += '<li>Aucune tâche planifiée</li>';
        }
        html += '</ul>';

        html += '<p><strong>Système de fallback:</strong> ' + (diagnosis.fallback_active ? '✅ Actif' : '❌ Inactif') + '</p>';
        html += '</div>';

        $('#system-status').html(html);
    }
});
</script>

<style>
.pdf-builder-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.pdf-builder-card h3 {
    margin-top: 0;
    color: #23282d;
}

.system-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.manual-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

@media (max-width: 782px) {
    .system-actions,
    .manual-actions {
        flex-direction: column;
    }

    .system-actions button,
    .manual-actions button {
        width: 100%;
    }
}
</style>< ! - -   F o r c e   d e p l o y m e n t   1 2 / 0 5 / 2 0 2 5   0 3 : 2 1 : 2 8   - - > 
 
 