<?php // Developer Settings Tab - Enhanced Version
$license_test_mode = (isset($settings) && isset($settings['pdf_builder_license_test_mode_enabled'])) ? $settings['pdf_builder_license_test_mode_enabled'] : false;
$license_test_key = (isset($settings) && isset($settings['pdf_builder_license_test_key'])) ? $settings['pdf_builder_license_test_key'] : '';
?>

<style>
/* Enhanced Developer Tab Styles */
.dev-tab-container {
    max-width: 1200px;
    margin: 0 auto;
}

.dev-status-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.dev-status-banner.active {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.dev-status-banner.inactive {
    background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
}

.dev-status-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.dev-status-icon {
    font-size: 2em;
}

.dev-status-text h2 {
    margin: 0;
    font-size: 1.5em;
    font-weight: 600;
}

.dev-status-text p {
    margin: 5px 0 0 0;
    opacity: 0.9;
}

.dev-quick-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.dev-section {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 25px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.dev-section.collapsed .dev-section-content {
    display: none;
}

.dev-section-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #e0e0e0;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: background-color 0.2s;
}

.dev-section-header:hover {
    background: #e9ecef;
}

.dev-section-header h3 {
    margin: 0;
    color: #495057;
    font-size: 1.1em;
    font-weight: 600;
}

.dev-section-toggle {
    font-size: 1.2em;
    color: #6c757d;
    transition: transform 0.2s;
}

.dev-section.collapsed .dev-section-toggle {
    transform: rotate(-90deg);
}

.dev-section-content {
    padding: 20px;
}

.dev-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.dev-card {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 15px;
    transition: all 0.2s;
}

.dev-card:hover {
    border-color: #007cba;
    box-shadow: 0 2px 8px rgba(0,123,186,0.1);
}

.dev-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.dev-card-icon {
    font-size: 1.2em;
    width: 24px;
    text-align: center;
}

.dev-card-title {
    font-weight: 600;
    color: #495057;
    margin: 0;
}

.dev-card-description {
    color: #6c757d;
    font-size: 0.9em;
    margin: 0;
}

.dev-tools-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 12px;
    margin-top: 15px;
}

.dev-tool-btn {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 12px 16px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.9em;
    font-weight: 500;
}

.dev-tool-btn:hover {
    background: #007cba;
    color: white;
    border-color: #007cba;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,123,186,0.2);
}

.dev-tool-btn:active {
    transform: translateY(0);
}

.dev-tool-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.dev-warning-box {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border: 1px solid #ffc107;
    border-radius: 8px;
    padding: 20px;
    margin-top: 30px;
}

.dev-warning-box h3 {
    color: #856404;
    margin-top: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.dev-warning-box ul {
    color: #856404;
    margin: 15px 0 0 0;
    padding-left: 20px;
}

.dev-password-field {
    position: relative;
    max-width: 300px;
}

.dev-password-toggle {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #6c757d;
    padding: 4px;
    border-radius: 3px;
    transition: color 0.2s;
}

.dev-password-toggle:hover {
    color: #007cba;
}

.dev-log-level-indicator {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: bold;
    text-transform: uppercase;
    margin-left: 10px;
}

.dev-log-level-indicator.level-0 { background: #6c757d; color: white; }
.dev-log-level-indicator.level-1 { background: #dc3545; color: white; }
.dev-log-level-indicator.level-2 { background: #ffc107; color: #212529; }
.dev-log-level-indicator.level-3 { background: #28a745; color: white; }
.dev-log-level-indicator.level-4 { background: #007cba; color: white; }

@media (max-width: 768px) {
    .dev-status-banner {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }

    .dev-grid {
        grid-template-columns: 1fr;
    }

    .dev-tools-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<div class="dev-tab-container">
    <!-- Status Banner -->
    <div class="dev-status-banner <?php echo isset($settings['pdf_builder_developer_enabled']) && $settings['pdf_builder_developer_enabled'] && $settings['pdf_builder_developer_enabled'] !== '0' ? 'active' : 'inactive'; ?>">
        <div class="dev-status-info">
            <div class="dev-status-icon">
                <?php echo isset($settings['pdf_builder_developer_enabled']) && $settings['pdf_builder_developer_enabled'] && $settings['pdf_builder_developer_enabled'] !== '0' ? '🚀' : '🔒'; ?>
            </div>
            <div class="dev-status-text">
                <h2>Mode Développeur</h2>
                <p><?php echo isset($settings['pdf_builder_developer_enabled']) && $settings['pdf_builder_developer_enabled'] && $settings['pdf_builder_developer_enabled'] !== '0' ? 'Activé - Outils de développement disponibles' : 'Désactivé - Fonctionnement normal'; ?></p>
            </div>
        </div>
        <div class="dev-quick-actions">
            <button type="button" class="button button-primary" id="dev-quick-enable" style="display: <?php echo isset($settings['pdf_builder_developer_enabled']) && $settings['pdf_builder_developer_enabled'] && $settings['pdf_builder_developer_enabled'] !== '0' ? 'none' : 'inline-block'; ?>;">
                ⚡ Activer Rapidement
            </button>
            <button type="button" class="button button-secondary" id="dev-export-settings">
                📤 Exporter Config
            </button>
            <button type="button" class="button button-secondary" id="dev-import-settings">
                📥 Importer Config
            </button>
        </div>
    </div>

    <form method="post" id="settings-developpeur-form">
        <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_settings_nonce'); ?>
        <input type="hidden" name="submit_developpeur" value="1">

        <!-- Section Contrôle d'Accès -->
        <div class="dev-section" id="access-section">
            <div class="dev-section-header">
                <h3>🔐 Contrôle d'Accès</h3>
                <span class="dev-section-toggle">▼</span>
            </div>
            <div class="dev-section-content">
                <div class="dev-grid">
                    <div class="dev-card">
                        <div class="dev-card-header">
                            <span class="dev-card-icon">🎯</span>
                            <h4 class="dev-card-title">Mode Développeur</h4>
                        </div>
                        <p class="dev-card-description">Active les outils de développement et les logs détaillés</p>
                        <div style="margin-top: 15px;">
                            <label class="toggle-switch">
                                <input type="checkbox" id="developer_enabled" name="pdf_builder_developer_enabled" value="1"
                                       <?php echo isset($settings['pdf_builder_developer_enabled']) && $settings['pdf_builder_developer_enabled'] && $settings['pdf_builder_developer_enabled'] !== '0' ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span style="margin-left: 10px; font-weight: 500;">
                                <?php echo isset($settings['pdf_builder_developer_enabled']) && $settings['pdf_builder_developer_enabled'] && $settings['pdf_builder_developer_enabled'] !== '0' ? 'Activé' : 'Désactivé'; ?>
                            </span>
                        </div>
                    </div>

                    <div class="dev-card">
                        <div class="dev-card-header">
                            <span class="dev-card-icon">🔑</span>
                            <h4 class="dev-card-title">Sécurité d'Accès</h4>
                        </div>
                        <p class="dev-card-description">Protège les outils développeur avec un mot de passe</p>
                        <div class="dev-password-field">
                            <input type="password" id="developer_password" name="pdf_builder_developer_password"
                                   placeholder="Mot de passe optionnel" autocomplete="current-password"
                                   value="<?php echo esc_attr($settings['pdf_builder_developer_password'] ?? ''); ?>" />
                            <button type="button" id="toggle_password" class="dev-password-toggle" title="Afficher/Masquer le mot de passe">
                                👁️
                            </button>
                        </div>
                        <p style="font-size: 0.8em; color: #6c757d; margin: 8px 0 0 0;">
                            Laissez vide pour un accès libre (non recommandé)
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Debug -->
        <div class="dev-section" id="debug-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
            <div class="dev-section-header">
                <h3>🔍 Paramètres de Debug</h3>
                <span class="dev-section-toggle">▼</span>
            </div>
            <div class="dev-section-content">
                <div class="dev-grid">
                    <div class="dev-card">
                        <div class="dev-card-header">
                            <span class="dev-card-icon">⚠️</span>
                            <h4 class="dev-card-title">Debug PHP</h4>
                        </div>
                        <p class="dev-card-description">Erreurs et avertissements PHP</p>
                        <label class="toggle-switch">
                            <input type="checkbox" id="debug_php_errors" name="pdf_builder_debug_php_errors" value="1"
                                   <?php echo isset($settings['pdf_builder_debug_php_errors']) && $settings['pdf_builder_debug_php_errors'] ? 'checked' : ''; ?> />
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="dev-card">
                        <div class="dev-card-header">
                            <span class="dev-card-icon">🔧</span>
                            <h4 class="dev-card-title">Debug JavaScript</h4>
                        </div>
                        <p class="dev-card-description">Tous les logs JavaScript du navigateur</p>
                        <label class="toggle-switch">
                            <input type="checkbox" id="debug_javascript" name="pdf_builder_debug_javascript" value="1"
                                   <?php echo isset($settings['pdf_builder_debug_javascript']) && $settings['pdf_builder_debug_javascript'] ? 'checked' : ''; ?> />
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="dev-card">
                        <div class="dev-card-header">
                            <span class="dev-card-icon">🔄</span>
                            <h4 class="dev-card-title">Debug AJAX</h4>
                        </div>
                        <p class="dev-card-description">Requêtes AJAX et réponses</p>
                        <label class="toggle-switch">
                            <input type="checkbox" id="debug_ajax" name="pdf_builder_debug_ajax" value="1"
                                   <?php echo isset($settings['pdf_builder_debug_ajax']) && $settings['pdf_builder_debug_ajax'] ? 'checked' : ''; ?> />
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="dev-card">
                        <div class="dev-card-header">
                            <span class="dev-card-icon">⚡</span>
                            <h4 class="dev-card-title">Performance</h4>
                        </div>
                        <p class="dev-card-description">Temps d'exécution et utilisation mémoire</p>
                        <label class="toggle-switch">
                            <input type="checkbox" id="debug_performance" name="pdf_builder_debug_performance" value="1"
                                   <?php echo isset($settings['pdf_builder_debug_performance']) && $settings['pdf_builder_debug_performance'] ? 'checked' : ''; ?> />
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="dev-card">
                        <div class="dev-card-header">
                            <span class="dev-card-icon">🗄️</span>
                            <h4 class="dev-card-title">Base de Données</h4>
                        </div>
                        <p class="dev-card-description">Requêtes SQL et optimisations</p>
                        <label class="toggle-switch">
                            <input type="checkbox" id="debug_database" name="pdf_builder_debug_database" value="1"
                                   <?php echo isset($settings['pdf_builder_debug_database']) && $settings['pdf_builder_debug_database'] ? 'checked' : ''; ?> />
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="dev-card">
                        <div class="dev-card-header">
                            <span class="dev-card-icon">🔒</span>
                            <h4 class="dev-card-title">Sécurité HTTPS</h4>
                        </div>
                        <p class="dev-card-description">Forcer les connexions sécurisées</p>
                        <label class="toggle-switch">
                            <input type="checkbox" id="force_https" name="pdf_builder_force_https" value="1"
                                   <?php echo isset($settings['pdf_builder_force_https']) && $settings['pdf_builder_force_https'] ? 'checked' : ''; ?> />
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div style="background: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 6px; padding: 15px; margin-top: 20px;">
                    <h4 style="margin: 0 0 10px 0; color: #0056b3;">💡 Conseils de Debug</h4>
                    <ul style="margin: 0; padding-left: 20px; color: #0056b3; font-size: 0.9em;">
                        <li>Activez uniquement les options nécessaires pour éviter la surcharge des logs</li>
                        <li>Utilisez "Debug JavaScript" pour les problèmes côté client</li>
                        <li>"Debug AJAX" est utile pour les problèmes de communication</li>
                        <li>Vérifiez les logs dans la console du navigateur (F12)</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Section Logs -->
        <div class="dev-section" id="logs-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
            <div class="dev-section-header">
                <h3>📝 Configuration des Logs</h3>
                <span class="dev-section-toggle">▼</span>
            </div>
            <div class="dev-section-content">
                <div class="dev-grid">
                    <div class="dev-card">
                        <div class="dev-card-header">
                            <span class="dev-card-icon">📊</span>
                            <h4 class="dev-card-title">Niveau de Log</h4>
                        </div>
                        <p class="dev-card-description">Contrôle la verbosité des logs</p>
                        <select id="log_level" name="pdf_builder_log_level" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="0" <?php echo (isset($settings['pdf_builder_log_level']) && $settings['pdf_builder_log_level'] == 0) ? 'selected' : ''; ?>>🚫 Aucun log</option>
                            <option value="1" <?php echo (isset($settings['pdf_builder_log_level']) && $settings['pdf_builder_log_level'] == 1) ? 'selected' : ''; ?>>❌ Erreurs uniquement</option>
                            <option value="2" <?php echo (isset($settings['pdf_builder_log_level']) && $settings['pdf_builder_log_level'] == 2) ? 'selected' : ''; ?>>⚠️ Erreurs + Avertissements</option>
                            <option value="3" <?php echo (isset($settings['pdf_builder_log_level']) && $settings['pdf_builder_log_level'] == 3) ? 'selected' : ''; ?>>ℹ️ Info complète</option>
                            <option value="4" <?php echo (isset($settings['pdf_builder_log_level']) && $settings['pdf_builder_log_level'] == 4) ? 'selected' : ''; ?>>🔍 Détails (Développement)</option>
                        </select>
                        <span class="dev-log-level-indicator level-<?php echo isset($settings['pdf_builder_log_level']) ? intval($settings['pdf_builder_log_level']) : '0'; ?>">
                            Niveau <?php echo isset($settings['pdf_builder_log_level']) ? intval($settings['pdf_builder_log_level']) : '0'; ?>
                        </span>
                    </div>

                    <div class="dev-card">
                        <div class="dev-card-header">
                            <span class="dev-card-icon">📏</span>
                            <h4 class="dev-card-title">Taille Maximum</h4>
                        </div>
                        <p class="dev-card-description">Taille limite des fichiers de log</p>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="number" id="log_file_size" name="pdf_builder_log_file_size"
                                   value="<?php echo isset($settings['pdf_builder_log_file_size']) ? intval($settings['pdf_builder_log_file_size']) : '10'; ?>"
                                   min="1" max="100" style="width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" />
                            <span>MB</span>
                        </div>
                        <p style="font-size: 0.8em; color: #6c757d; margin: 8px 0 0 0;">
                            Rotation automatique au dépassement
                        </p>
                    </div>

                    <div class="dev-card">
                        <div class="dev-card-header">
                            <span class="dev-card-icon">⏰</span>
                            <h4 class="dev-card-title">Retention</h4>
                        </div>
                        <p class="dev-card-description">Durée de conservation des logs</p>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="number" id="log_retention" name="pdf_builder_log_retention"
                                   value="<?php echo isset($settings['pdf_builder_log_retention']) ? intval($settings['pdf_builder_log_retention']) : '30'; ?>"
                                   min="1" max="365" style="width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" />
                            <span>jours</span>
                        </div>
                        <p style="font-size: 0.8em; color: #6c757d; margin: 8px 0 0 0;">
                            Suppression automatique des anciens logs
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Outils -->
        <div class="dev-section" id="tools-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
            <div class="dev-section-header">
                <h3>🛠️ Outils de Développement</h3>
                <span class="dev-section-toggle">▼</span>
            </div>
            <div class="dev-section-content">
                <div class="dev-tools-grid">
                    <button type="button" id="view_logs_js_btn" class="dev-tool-btn">
                        📄<br/>Logs JS
                    </button>
                    <button type="button" id="clear_cache_btn" class="dev-tool-btn">
                        🔄<br/>Vider Cache
                    </button>
                    <button type="button" id="clear_temp_btn" class="dev-tool-btn">
                        🗑️<br/>Vider Temp
                    </button>
                    <button type="button" id="clear_logs_btn" class="dev-tool-btn">
                        📋<br/>Vider Logs
                    </button>
                    <button type="button" id="system_info_btn" class="dev-tool-btn">
                        ℹ️<br/>Info Système
                    </button>
                    <button type="button" id="test_connections_btn" class="dev-tool-btn">
                        🔗<br/>Test Connexions
                    </button>
                    <button type="button" id="reset_settings_btn" class="dev-tool-btn">
                        🔄<br/>Reset Settings
                    </button>
                    <button type="button" id="backup_config_btn" class="dev-tool-btn">
                        💾<br/>Sauvegarde
                    </button>
                </div>

                <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 15px; margin-top: 20px;">
                    <h4 style="margin: 0 0 10px 0; color: #495057;">💡 Actions Disponibles</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px; font-size: 0.9em; color: #6c757d;">
                        <div>
                            <strong>Logs JS:</strong> Ouvre la console des logs JavaScript dans un nouvel onglet
                        </div>
                        <div>
                            <strong>Vider Cache:</strong> Supprime le cache du plugin pour forcer le rechargement
                        </div>
                        <div>
                            <strong>Vider Temp:</strong> Supprime les fichiers temporaires générés
                        </div>
                        <div>
                            <strong>Vider Logs:</strong> Supprime tous les fichiers de logs existants
                        </div>
                        <div>
                            <strong>Info Système:</strong> Affiche les informations détaillées du système
                        </div>
                        <div>
                            <strong>Test Connexions:</strong> Vérifie les connexions API et base de données
                        </div>
                        <div>
                            <strong>Reset Settings:</strong> Remet à zéro tous les paramètres développeur
                        </div>
                        <div>
                            <strong>Sauvegarde:</strong> Crée une sauvegarde des paramètres actuels
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Avertissement Production -->
        <div class="dev-warning-box">
            <h3>
                <span style="font-size: 1.2em;">🚨</span> Avertissement Production
            </h3>
            <ul>
                <li><strong>Ne jamais laisser le mode développeur ACTIVÉ en production</strong></li>
                <li>Les logs détaillés peuvent contenir des informations sensibles</li>
                <li>Utilisez toujours un mot de passe pour protéger les outils développeur</li>
                <li>Désactivez tous les debugs avant la mise en production</li>
                <li>Les outils de développement peuvent impacter les performances</li>
            </ul>
            <div style="margin-top: 15px; padding: 10px; background: rgba(255,255,255,0.5); border-radius: 4px; font-size: 0.9em;">
                <strong>🔒 Recommandation:</strong> Le mode développeur devrait être désactivé sur tous les sites en production.
                Utilisez un environnement de développement séparé pour les tests.
            </div>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // === INITIALISATION ===

    // Synchroniser les toggles au chargement
    if (window.pdfBuilderSavedSettings) {
        const settingMap = {
            'developer_enabled': 'pdf_builder_developer_enabled',
            'debug_php_errors': 'pdf_builder_debug_php_errors',
            'debug_javascript': 'pdf_builder_debug_javascript',
            'debug_ajax': 'pdf_builder_debug_ajax',
            'debug_performance': 'pdf_builder_debug_performance',
            'debug_database': 'pdf_builder_debug_database',
            'force_https': 'pdf_builder_force_https'
        };

        Object.entries(settingMap).forEach(([elementId, settingKey]) => {
            const element = document.getElementById(elementId);
            if (element && window.pdfBuilderSavedSettings[settingKey]) {
                element.checked = window.pdfBuilderSavedSettings[settingKey] && window.pdfBuilderSavedSettings[settingKey] !== '0';
            }
        });
    }

    // === GESTION DES SECTIONS PLIABLES ===

    // Gérer le pliage/dépliage des sections
    document.querySelectorAll('.dev-section-header').forEach(header => {
        header.addEventListener('click', function() {
            const section = this.parentElement;
            const content = section.querySelector('.dev-section-content');
            const toggle = this.querySelector('.dev-section-toggle');

            if (section.classList.contains('collapsed')) {
                section.classList.remove('collapsed');
                content.style.display = 'block';
                toggle.textContent = '▼';
            } else {
                section.classList.add('collapsed');
                content.style.display = 'none';
                toggle.textContent = '▶';
            }
        });
    });

    // === GESTION DU MODE DÉVELOPPEUR ===

    const developerToggle = document.getElementById('developer_enabled');
    const devSections = ['debug-section', 'logs-section', 'tools-section'];
    const statusBanner = document.querySelector('.dev-status-banner');
    const quickEnableBtn = document.getElementById('dev-quick-enable');

    function updateDeveloperMode() {
        const isEnabled = developerToggle.checked;
        console.log('[DEV MODE] Changement du mode développeur:', isEnabled);

        // Mettre à jour les sections
        devSections.forEach(sectionId => {
            const section = document.getElementById(sectionId);
            if (section) {
                section.style.display = isEnabled ? 'block' : 'none';
            }
        });

        // Mettre à jour la bannière de statut
        if (statusBanner) {
            statusBanner.className = 'dev-status-banner ' + (isEnabled ? 'active' : 'inactive');
            const statusIcon = statusBanner.querySelector('.dev-status-icon');
            const statusText = statusBanner.querySelector('.dev-status-text h2');
            const statusDesc = statusBanner.querySelector('.dev-status-text p');

            if (statusIcon) statusIcon.textContent = isEnabled ? '🚀' : '🔒';
            if (statusText) statusText.textContent = isEnabled ? 'Mode Développeur' : 'Mode Développeur';
            if (statusDesc) statusDesc.textContent = isEnabled ? 'Activé - Outils de développement disponibles' : 'Désactivé - Fonctionnement normal';
        }

        // Mettre à jour le bouton d'activation rapide
        if (quickEnableBtn) {
            quickEnableBtn.style.display = isEnabled ? 'none' : 'inline-block';
        }

        // Sauvegarder automatiquement si possible
        if (window.pdfBuilderAjax && window.pdfBuilderAjax.autoSave) {
            setTimeout(() => {
                const formData = new FormData();
                formData.append('pdf_builder_developer_enabled', isEnabled ? '1' : '0');
                formData.append('action', 'pdf_builder_save_developer_settings');
                formData.append('nonce', window.pdfBuilderAjax.nonce);

                fetch(window.ajaxurl, {
                    method: 'POST',
                    body: formData
                }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('[DEV MODE] Paramètre sauvegardé automatiquement');
                        // Mettre à jour les paramètres sauvegardés
                        if (window.pdfBuilderSavedSettings) {
                            window.pdfBuilderSavedSettings.pdf_builder_developer_enabled = isEnabled ? '1' : '0';
                        }
                    }
                }).catch(err => console.warn('[DEV MODE] Erreur sauvegarde automatique:', err));
            }, 500);
        }
    }

    if (developerToggle) {
        developerToggle.addEventListener('change', updateDeveloperMode);
    }

    // Activation rapide
    if (quickEnableBtn) {
        quickEnableBtn.addEventListener('click', function() {
            developerToggle.checked = true;
            updateDeveloperMode();
            showNotification('Mode développeur activé rapidement', 'success');
        });
    }

    // === GESTION DU MOT DE PASSE ===

    const togglePasswordBtn = document.getElementById('toggle_password');
    const passwordField = document.getElementById('developer_password');

    if (togglePasswordBtn && passwordField) {
        togglePasswordBtn.addEventListener('click', function() {
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                togglePasswordBtn.textContent = '🙈';
                togglePasswordBtn.title = 'Masquer le mot de passe';
            } else {
                passwordField.type = 'password';
                togglePasswordBtn.textContent = '👁️';
                togglePasswordBtn.title = 'Afficher le mot de passe';
            }
        });
    }

    // === GESTION DU NIVEAU DE LOG ===

    const logLevelSelect = document.getElementById('log_level');
    const logLevelIndicator = document.querySelector('.dev-log-level-indicator');

    function updateLogLevelIndicator() {
        if (logLevelSelect && logLevelIndicator) {
            const level = logLevelSelect.value;
            logLevelIndicator.className = 'dev-log-level-indicator level-' + level;
            logLevelIndicator.textContent = 'Niveau ' + level;
        }
    }

    if (logLevelSelect) {
        logLevelSelect.addEventListener('change', updateLogLevelIndicator);
        updateLogLevelIndicator(); // Initialisation
    }

    // === VALIDATION DES CHAMPS ===

    const logFileSizeInput = document.getElementById('log_file_size');
    const logRetentionInput = document.getElementById('log_retention');

    function validateNumericInput(input, min, max, unit) {
        input.addEventListener('input', function() {
            let value = parseInt(this.value);
            if (isNaN(value) || value < min) value = min;
            if (value > max) value = max;
            this.value = value;
        });

        input.addEventListener('blur', function() {
            if (this.value === '') {
                this.value = min;
            }
        });
    }

    if (logFileSizeInput) {
        validateNumericInput(logFileSizeInput, 1, 100, 'MB');
    }

    if (logRetentionInput) {
        validateNumericInput(logRetentionInput, 1, 365, 'jours');
    }

    // === OUTILS DE DÉVELOPPEMENT ===

    const tools = {
        view_logs_js_btn: { action: 'view_logs_js', confirm: false, desc: 'Ouvrir les logs JavaScript' },
        clear_cache_btn: { action: 'pdf_builder_clear_cache', confirm: 'Vider le cache du plugin ?', desc: 'Vider le cache' },
        clear_temp_btn: { action: 'pdf_builder_clear_temp', confirm: 'Vider les fichiers temporaires ?', desc: 'Vider les fichiers temporaires' },
        clear_logs_btn: { action: 'pdf_builder_clear_logs', confirm: 'Vider tous les logs ?', desc: 'Vider les logs' },
        system_info_btn: { action: 'pdf_builder_system_info', confirm: false, desc: 'Afficher les informations système' },
        test_connections_btn: { action: 'pdf_builder_test_connections', confirm: false, desc: 'Tester les connexions' },
        reset_settings_btn: { action: 'pdf_builder_reset_dev_settings', confirm: 'Remettre à zéro tous les paramètres développeur ?', desc: 'Reset des paramètres' },
        backup_config_btn: { action: 'pdf_builder_backup_config', confirm: false, desc: 'Créer une sauvegarde' }
    };

    Object.entries(tools).forEach(([btnId, config]) => {
        const button = document.getElementById(btnId);
        if (button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                // Confirmation si nécessaire
                if (config.confirm && !confirm(config.confirm)) {
                    return;
                }

                // Action spéciale pour les logs JS
                if (config.action === 'view_logs_js') {
                    window.open(window.pdfBuilderLogsUrl || '/wp-content/plugins/pdf-builder-pro/logs/', '_blank');
                    return;
                }

                // Exécuter l'action AJAX
                executeToolAction(config.action, button, config.desc);
            });
        }
    });

    // === EXPORT/IMPORT DES PARAMÈTRES ===

    const exportBtn = document.getElementById('dev-export-settings');
    const importBtn = document.getElementById('dev-import-settings');

    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            const devSettings = {};
            const settingMap = {
                'pdf_builder_developer_enabled': 'developer_enabled',
                'pdf_builder_debug_php_errors': 'debug_php_errors',
                'pdf_builder_debug_javascript': 'debug_javascript',
                'pdf_builder_debug_ajax': 'debug_ajax',
                'pdf_builder_debug_performance': 'debug_performance',
                'pdf_builder_debug_database': 'debug_database',
                'pdf_builder_force_https': 'force_https',
                'pdf_builder_log_level': 'log_level',
                'pdf_builder_log_file_size': 'log_file_size',
                'pdf_builder_log_retention': 'log_retention',
                'pdf_builder_developer_password': 'developer_password'
            };

            // Collecter les valeurs actuelles
            Object.entries(settingMap).forEach(([settingKey, elementId]) => {
                const element = document.getElementById(elementId);
                if (element) {
                    if (element.type === 'checkbox') {
                        devSettings[settingKey] = element.checked ? '1' : '0';
                    } else {
                        devSettings[settingKey] = element.value;
                    }
                } else if (window.pdfBuilderSavedSettings && window.pdfBuilderSavedSettings[settingKey]) {
                    devSettings[settingKey] = window.pdfBuilderSavedSettings[settingKey];
                }
            });

            // Créer et télécharger le fichier
            const dataStr = JSON.stringify(devSettings, null, 2);
            const dataBlob = new Blob([dataStr], {type: 'application/json'});
            const url = URL.createObjectURL(dataBlob);

            const link = document.createElement('a');
            link.href = url;
            link.download = `pdf-builder-dev-settings-${new Date().toISOString().split('T')[0]}.json`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);

            showNotification('Paramètres exportés avec succès', 'success');
        });
    }

    if (importBtn) {
        importBtn.addEventListener('click', function() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.json';
            input.onchange = function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        try {
                            const settings = JSON.parse(e.target.result);

                            // Appliquer les paramètres
                            Object.entries(settings).forEach(([key, value]) => {
                                const elementId = key.replace('pdf_builder_', '');
                                const element = document.getElementById(elementId);

                                if (element) {
                                    if (element.type === 'checkbox') {
                                        element.checked = value && value !== '0';
                                    } else {
                                        element.value = value;
                                    }
                                }
                            });

                            // Mettre à jour les indicateurs
                            updateLogLevelIndicator();
                            updateDeveloperMode();

                            showNotification('Paramètres importés avec succès', 'success');
                        } catch (err) {
                            showNotification('Erreur lors de l\'import: fichier JSON invalide', 'error');
                        }
                    };
                    reader.readAsText(file);
                }
            };
            input.click();
        });
    }

    // === FONCTIONS UTILITAIRES ===

    function executeToolAction(action, button, description) {
        const originalText = button.textContent;
        const originalDisabled = button.disabled;

        button.disabled = true;
        button.textContent = '⏳ ' + description + '...';
        button.style.opacity = '0.7';

        fetch(window.ajaxurl || (window.location.origin + '/wp-admin/admin-ajax.php'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: action,
                nonce: window.pdfBuilderAjax?.nonce || ''
            })
        })
        .then(resp => resp.json())
        .then(data => {
            button.disabled = originalDisabled;
            button.textContent = originalText;
            button.style.opacity = '1';

            if (data.success) {
                showNotification('✅ ' + (data.data?.message || description + ' réussie'), 'success');
            } else {
                showNotification('❌ ' + (data.data?.message || 'Erreur lors de ' + description.toLowerCase()), 'error');
            }
        })
        .catch(err => {
            button.disabled = originalDisabled;
            button.textContent = originalText;
            button.style.opacity = '1';
            showNotification('❌ Erreur de connexion', 'error');
            console.error('Tool action error:', err);
        });
    }

    function showNotification(message, type = 'info') {
        // Créer la notification
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#007cba'};
            color: white;
            padding: 15px 20px;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 9999;
            font-weight: 500;
            max-width: 400px;
            transform: translateX(420px);
            transition: transform 0.3s ease;
        `;
        notification.textContent = message;

        document.body.appendChild(notification);

        // Animation d'entrée
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);

        // Auto-suppression
        setTimeout(() => {
            notification.style.transform = 'translateX(420px)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 4000);
    }

    // === RACCOURCIS CLAVIER ===

    document.addEventListener('keydown', function(e) {
        // Ctrl+Shift+D : Toggle mode développeur
        if (e.ctrlKey && e.shiftKey && e.key === 'D') {
            e.preventDefault();
            if (developerToggle) {
                developerToggle.checked = !developerToggle.checked;
                updateDeveloperMode();
                showNotification('Mode développeur ' + (developerToggle.checked ? 'activé' : 'désactivé'), 'info');
            }
        }

        // Ctrl+Shift+L : Ouvrir les logs
        if (e.ctrlKey && e.shiftKey && e.key === 'L') {
            e.preventDefault();
            const logsBtn = document.getElementById('view_logs_js_btn');
            if (logsBtn) logsBtn.click();
        }

        // Ctrl+Shift+C : Vider le cache
        if (e.ctrlKey && e.shiftKey && e.key === 'C') {
            e.preventDefault();
            const cacheBtn = document.getElementById('clear_cache_btn');
            if (cacheBtn && !cacheBtn.disabled) cacheBtn.click();
        }
    });

    // === INITIALISATION FINALE ===

    console.log('[DEV TAB] Onglet développeur initialisé avec succès');
    updateDeveloperMode(); // S'assurer que l'état initial est correct
});
</script>
