<?php
if (!defined('ABSPATH')) exit('No direct access');
?>

<div class="pdf-builder-developer-settings">
    <h2>🛠️ Outils Développeur</h2>
    <p class="description">Outils avancés pour les développeurs. Utilisez avec précaution.</p>

    <!-- CONTROLE DU MODE DEVELOPPEUR -->
    <div class="dev-section" id="dev-mode-section">
        <div class="dev-section-header" tabindex="0" role="button" aria-expanded="true" aria-controls="dev-mode-content">
            <span class="dev-section-toggle">🔽</span>
            <h3>Mode Développeur</h3>
        </div>
        <div class="dev-section-content" id="dev-mode-content" aria-hidden="false">
            <table class="form-table">
                <tr>
                    <th scope="row">Activer le mode développeur</th>
                    <td>
                        <label>
                            <input type="checkbox" id="developer_enabled" name="pdf_builder_settings[developer_enabled]" value="1" <?php checked(get_option('pdf_builder_settings')['developer_enabled'] ?? '', '1'); ?>>
                            Activer les outils développeur avancés
                        </label>
                        <p class="description">Affiche les sections de développement et active les fonctionnalités de debug.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Mot de passe développeur</th>
                    <td>
                        <input type="password" id="developer_password" name="pdf_builder_settings[developer_password]" value="<?php echo esc_attr(get_option('pdf_builder_settings')['developer_password'] ?? ''); ?>" class="regular-text">
                        <button type="button" id="toggle_password" class="button">👁️ Afficher</button>
                        <p class="description">Mot de passe requis pour accéder aux fonctionnalités sensibles.</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- TESTS DE LICENCE -->
    <div class="dev-section" id="dev-license-section">
        <div class="dev-section-header" tabindex="0" role="button" aria-expanded="false" aria-controls="dev-license-content">
            <span class="dev-section-toggle">▶️</span>
            <h3>Tests de Licence</h3>
        </div>
        <div class="dev-section-content" id="dev-license-content" aria-hidden="true">
            <table class="form-table">
                <tr>
                    <th scope="row">Mode test licence</th>
                    <td>
                        <label>
                            <input type="checkbox" id="license_test_mode" name="pdf_builder_settings[license_test_mode]" value="1" <?php checked(get_option('pdf_builder_settings')['license_test_mode'] ?? '', '1'); ?>>
                            Activer le mode test de licence
                        </label>
                        <span id="license_test_mode_status" style="margin-left: 10px; font-weight: bold;">
                            <?php echo (get_option('pdf_builder_settings')['license_test_mode'] ?? '') === '1' ? '✅ MODE TEST ACTIF' : '❌ Mode test inactif'; ?>
                        </span>
                        <p class="description">Permet de tester les fonctionnalités sans licence valide.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Clé de test</th>
                    <td>
                        <input type="text" id="license_test_key" name="pdf_builder_settings[license_test_key]" value="<?php echo esc_attr(get_option('pdf_builder_settings')['license_test_key'] ?? ''); ?>" class="regular-text" readonly>
                        <button type="button" id="generate_license_key_btn" class="button">🔑 Générer</button>
                        <button type="button" id="validate_license_key_btn" class="button">✅ Valider</button>
                        <button type="button" id="show_license_key_btn" class="button">👁️ Afficher</button>
                        <button type="button" id="copy_license_key_btn" class="button">📋 Copier</button>
                        <button type="button" id="delete_license_key_btn" class="button button-link-delete" style="color: #dc3545;">🗑️ Supprimer</button>
                        <br>
                        <span id="license_test_key_display" style="font-family: monospace; margin-top: 5px; display: inline-block;">
                            <?php
                            $key = get_option('pdf_builder_settings')['license_test_key'] ?? '';
                            if ($key) {
                                $masked = substr($key, 0, 6) . '••••••••••••••••' . substr($key, -6);
                                echo esc_html($masked);
                            }
                            ?>
                        </span>
                        <br>
                        <span id="license_key_expires" style="color: #666; font-size: 12px;">
                            <?php
                            $expires = get_option('pdf_builder_settings')['license_test_key_expires'] ?? '';
                            if ($expires) {
                                echo 'Expire le: ' . esc_html($expires);
                            }
                            ?>
                        </span>
                        <br>
                        <span id="license_key_status" style="margin-top: 5px; display: inline-block;"></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Nettoyage complet</th>
                    <td>
                        <button type="button" id="cleanup_license_btn" class="button button-link-delete" style="color: #dc3545; border-color: #dc3545;">
                            🧹 Nettoyer toutes les données de licence
                        </button>
                        <br>
                        <span id="cleanup_status" style="margin-top: 5px; display: inline-block;"></span>
                        <p class="description" style="color: #dc3545; font-weight: bold;">
                            ⚠️ ATTENTION: Cette action supprime TOUTES les données de licence et réinitialise le plugin !
                        </p>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- TESTS DE NOTIFICATIONS -->
    <div class="dev-section" id="dev-notifications-test-section">
        <div class="dev-section-header" tabindex="0" role="button" aria-expanded="false" aria-controls="dev-notifications-content">
            <span class="dev-section-toggle">▶️</span>
            <h3>🔔 Tests de Notifications</h3>
        </div>
        <div class="dev-section-content" id="dev-notifications-content" aria-hidden="true">
            <p class="description">Testez le système de notifications du plugin.</p>

            <div style="margin-bottom: 20px;">
                <button type="button" id="test_notification_success" class="button button-success" style="margin-right: 10px; background: #28a745; color: white; border-color: #28a745;">
                    ✅ Test Succès
                </button>
                <button type="button" id="test_notification_error" class="button button-error" style="margin-right: 10px; background: #dc3545; color: white; border-color: #dc3545;">
                    ❌ Test Erreur
                </button>
                <button type="button" id="test_notification_warning" class="button button-warning" style="margin-right: 10px; background: #ffc107; color: #212529; border-color: #ffc107;">
                    ⚠️ Test Avertissement
                </button>
                <button type="button" id="test_notification_info" class="button button-info" style="margin-right: 10px; background: #17a2b8; color: white; border-color: #17a2b8;">
                    ℹ️ Test Info
                </button>
                <button type="button" id="test_notification_all" class="button" style="margin-right: 10px; background: #6c757d; color: white; border-color: #6c757d;">
                    🎯 Tout tester
                </button>
                <button type="button" id="test_notification_clear" class="button button-secondary" style="margin-right: 10px;">
                    🗑️ Tout effacer
                </button>
                <button type="button" id="test_notification_stats" class="button button-secondary">
                    📊 Statistiques
                </button>
            </div>

            <div style="border: 1px solid #ddd; border-radius: 4px; padding: 10px; background: #f9f9f9;">
                <h4 style="margin-top: 0;">📋 Logs des tests</h4>
                <div id="notification_test_logs" style="max-height: 200px; overflow-y: auto; font-family: monospace; font-size: 12px; background: white; border: 1px solid #eee; padding: 5px;">
                    <!-- Les logs des tests apparaîtront ici -->
                </div>
                <button type="button" id="clear_notification_logs" class="button button-small" style="margin-top: 5px;">
                    🗑️ Vider les logs
                </button>
            </div>
        </div>
    </div>

    <!-- OUTILS DE DEVELOPPEMENT -->
    <div class="dev-section" id="dev-tools-section">
        <div class="dev-section-header" tabindex="0" role="button" aria-expanded="false" aria-controls="dev-tools-content">
            <span class="dev-section-toggle">▶️</span>
            <h3>🔧 Outils de Développement</h3>
        </div>
        <div class="dev-section-content" id="dev-tools-content" aria-hidden="true">
            <table class="form-table">
                <tr>
                    <th scope="row">Cache</th>
                    <td>
                        <button type="button" id="reload_cache_btn" class="button">🔄 Recharger le cache</button>
                        <p class="description">Force le rechargement du cache système.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Fichiers temporaires</th>
                    <td>
                        <button type="button" id="clear_temp_btn" class="button">🗑️ Vider les fichiers temp</button>
                        <p class="description">Supprime tous les fichiers temporaires.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Routes</th>
                    <td>
                        <button type="button" id="test_routes_btn" class="button">🧪 Tester les routes</button>
                        <p class="description">Vérifie que toutes les routes du plugin fonctionnent.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Diagnostic</th>
                    <td>
                        <button type="button" id="export_diagnostic_btn" class="button">📊 Exporter diagnostic</button>
                        <p class="description">Génère un rapport complet du système.</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- GESTION DES LOGS -->
    <div class="dev-section" id="dev-logs-section">
        <div class="dev-section-header" tabindex="0" role="button" aria-expanded="false" aria-controls="dev-logs-content">
            <span class="dev-section-toggle">▶️</span>
            <h3>📋 Gestion des Logs</h3>
        </div>
        <div class="dev-section-content" id="dev-logs-content" aria-hidden="true">
            <table class="form-table">
                <tr>
                    <th scope="row">Filtre</th>
                    <td>
                        <select id="log_filter">
                            <option value="">Tous les logs</option>
                            <option value="error">Erreurs uniquement</option>
                            <option value="warning">Avertissements</option>
                            <option value="info">Informations</option>
                        </select>
                        <button type="button" id="refresh_logs_btn" class="button">🔄 Actualiser</button>
                        <button type="button" id="clear_logs_btn" class="button button-link-delete" style="color: #dc3545;">🗑️ Vider</button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Contenu des logs</th>
                    <td>
                        <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f9f9f9; font-family: monospace; font-size: 12px;">
                            <pre id="logs_content" style="margin: 0;">Cliquez sur "Actualiser" pour charger les logs...</pre>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- CONSOLE DE CODE -->
    <div class="dev-section" id="dev-console-section">
        <div class="dev-section-header" tabindex="0" role="button" aria-expanded="false" aria-controls="dev-console-content">
            <span class="dev-section-toggle">▶️</span>
            <h3>💻 Console de Code</h3>
        </div>
        <div class="dev-section-content" id="dev-console-content" aria-hidden="true">
            <table class="form-table">
                <tr>
                    <th scope="row">Code à exécuter</th>
                    <td>
                        <textarea id="test_code" rows="8" cols="80" placeholder="Entrez du code JavaScript à exécuter..."></textarea>
                        <br>
                        <button type="button" id="execute_code_btn" class="button button-primary">▶️ Exécuter</button>
                        <button type="button" id="clear_console_btn" class="button">🗑️ Vider</button>
                        <br>
                        <span id="code_result" style="margin-top: 10px; display: inline-block; font-family: monospace;"></span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- MONITORING DES PERFORMANCES -->
    <div class="dev-section" id="dev-performance-section">
        <div class="dev-section-header" tabindex="0" role="button" aria-expanded="false" aria-controls="dev-performance-content">
            <span class="dev-section-toggle">▶️</span>
            <h3>⚡ Monitoring Performances</h3>
        </div>
        <div class="dev-section-content" id="dev-performance-content" aria-hidden="true">
            <table class="form-table">
                <tr>
                    <th scope="row">Test FPS</th>
                    <td>
                        <button type="button" id="test_fps_btn" class="button">🎮 Tester FPS</button>
                        <span id="fps_test_result" style="margin-left: 10px; font-weight: bold;"></span>
                        <div id="fps_test_details" style="margin-top: 10px; display: none;">
                            <p>Test de performance en cours...</p>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Informations système</th>
                    <td>
                        <button type="button" id="system_info_btn" class="button">ℹ️ Infos Système</button>
                        <div id="system_info_result" style="margin-top: 10px; display: none; border: 1px solid #ddd; padding: 10px; background: #f9f9f9; font-family: monospace; font-size: 12px;">
                            <p>Chargement des informations système...</p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- RACCOURCIS -->
    <div class="dev-section" id="dev-shortcuts-section">
        <div class="dev-section-header" tabindex="0" role="button" aria-expanded="false" aria-controls="dev-shortcuts-content">
            <span class="dev-section-toggle">▶️</span>
            <h3>🚀 Raccourcis</h3>
        </div>
        <div class="dev-section-content" id="dev-shortcuts-content" aria-hidden="true">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                <button type="button" id="system_info_shortcut_btn" class="button" style="height: 40px;">ℹ️ Infos Système</button>
                <button type="button" id="view_logs_btn" class="button" style="height: 40px;">📋 Voir Logs</button>
                <button type="button" id="export_diagnostic_btn" class="button" style="height: 40px;">📊 Diagnostic</button>
            </div>
        </div>
    </div>

    <!-- TODO LIST -->
    <div class="dev-section" id="dev-todo-section">
        <div class="dev-section-header" tabindex="0" role="button" aria-expanded="false" aria-controls="dev-todo-content">
            <span class="dev-section-toggle">▶️</span>
            <h3>📝 Todo List</h3>
        </div>
        <div class="dev-section-content" id="dev-todo-content" aria-hidden="true">
            <div id="dev-todo-content">
                <p>Liste des tâches en cours...</p>
                <ul>
                    <li>✅ Corriger les erreurs JavaScript</li>
                    <li>✅ Implémenter le système de notifications</li>
                    <li>✅ Ajouter les boutons de test</li>
                    <li>🔄 Tester toutes les fonctionnalités</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.pdf-builder-developer-settings {
    margin-top: 20px;
}

.pdf-builder-developer-settings .dev-section {
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 15px;
    background: #fff;
}

.pdf-builder-developer-settings .dev-section-header {
    background: #f8f9fa;
    padding: 12px 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    border-radius: 4px 4px 0 0;
    transition: background-color 0.2s;
}

.pdf-builder-developer-settings .dev-section-header:hover {
    background: #e9ecef;
}

.pdf-builder-developer-settings .dev-section-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    flex-grow: 1;
}

.pdf-builder-developer-settings .dev-section-toggle {
    margin-right: 10px;
    font-size: 14px;
}

.pdf-builder-developer-settings .dev-section-content {
    padding: 16px;
    border-top: 1px solid #eee;
}

.pdf-builder-developer-settings .dev-section.collapsed .dev-section-content {
    display: none;
}

.pdf-builder-developer-settings .dev-section.collapsed .dev-section-header {
    border-radius: 4px;
}

.developer-status-indicator {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}
</style>

<script>
// Ajouter le gestionnaire pour vider les logs de notification
document.addEventListener('DOMContentLoaded', function() {
    const clearLogsBtn = document.getElementById('clear_notification_logs');
    if (clearLogsBtn) {
        clearLogsBtn.addEventListener('click', function() {
            const logsContainer = document.getElementById('notification_test_logs');
            if (logsContainer) {
                logsContainer.innerHTML = '';
            }
        });
    }
});
</script>
