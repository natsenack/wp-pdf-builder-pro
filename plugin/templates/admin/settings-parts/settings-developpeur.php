<?php // Developer Settings Tab - Cleaned and Simplified
$license_test_mode = (isset($settings) && isset($settings['pdf_builder_license_test_mode_enabled'])) ? $settings['pdf_builder_license_test_mode_enabled'] : false;
$license_test_key = (isset($settings) && isset($settings['pdf_builder_license_test_key'])) ? $settings['pdf_builder_license_test_key'] : '';
?>

<h2>Paramètres Développeur</h2>
<p style="color: #666;">⚠️ Cette section est réservée aux développeurs. Les modifications ici peuvent affecter le fonctionnement du plugin.</p>

<form method="post" id="settings-developpeur-form">
    <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_settings_nonce'); ?>
    <input type="hidden" name="submit_developpeur" value="1">

    <!-- Section Contrôle d'Accès -->
    <h3 class="section-title">🔐 Contrôle d'Accès</h3>
    <table class="form-table">
        <tr>
            <th scope="row"><label for="developer_enabled">Mode Développeur</label></th>
            <td>
                <div class="toggle-container">
                    <label class="toggle-switch">
                        <input type="checkbox" id="developer_enabled" name="pdf_builder_developer_enabled" value="1" <?php echo isset($settings['pdf_builder_developer_enabled']) && $settings['pdf_builder_developer_enabled'] && $settings['pdf_builder_developer_enabled'] !== '0' ? 'checked' : ''; ?> />
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Activer le mode développeur</span>
                    <span class="developer-status-indicator" style="margin-left: 10px; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; <?php echo isset($settings['pdf_builder_developer_enabled']) && $settings['pdf_builder_developer_enabled'] && $settings['pdf_builder_developer_enabled'] !== '0' ? 'background: #28a745; color: white;' : 'background: #dc3545; color: white;'; ?>">
                        <?php echo isset($settings['pdf_builder_developer_enabled']) && $settings['pdf_builder_developer_enabled'] && $settings['pdf_builder_developer_enabled'] !== '0' ? 'ACTIF' : 'INACTIF'; ?>
                    </span>
                </div>
                <div class="toggle-description">Active le mode développeur avec logs détaillés</div>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="developer_password">Mot de Passe Dev</label></th>
            <td>
                <input type="text" autocomplete="username" style="display: none;" />
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="password" id="developer_password" name="pdf_builder_developer_password"
                           placeholder="Laisser vide pour aucun mot de passe" autocomplete="current-password"
                           style="width: 250px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                           value="<?php echo esc_attr($settings['pdf_builder_developer_password'] ?? ''); ?>" />
                    <button type="button" id="toggle_password" class="button button-secondary" style="padding: 8px 12px; height: auto;">
                        👁️ Afficher
                    </button>
                </div>
                <p class="description">Protège les outils développeur avec un mot de passe (optionnel)</p>
            </td>
        </tr>
    </table>

    <!-- Section Debug -->
    <div id="dev-debug-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
    <h3 class="section-title">🔍 Paramètres de Debug</h3>
    <table class="form-table">
        <tr>
            <th scope="row"><label>Options de Debug</label></th>
            <td>
                <div style="background: #f5f5f5; padding: 15px; border-radius: 4px; border-left: 4px solid #2196F3;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px;">
                        
                        <!-- Debug PHP -->
                        <div style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #e0e0e0;">
                            <label class="toggle-switch" style="margin: 0;">
                                <input type="checkbox" id="debug_php_errors" name="pdf_builder_debug_php_errors" value="1" <?php echo isset($settings['pdf_builder_debug_php_errors']) && $settings['pdf_builder_debug_php_errors'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span style="margin-left: 10px;">⚠️ Debug PHP</span>
                            <div style="font-size: 11px; color: #999; margin-top: 5px;">Erreurs et warnings</div>
                        </div>

                        <!-- Debug JavaScript -->
                        <div style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #e0e0e0;">
                            <label class="toggle-switch" style="margin: 0;">
                                <input type="checkbox" id="debug_javascript" name="pdf_builder_debug_javascript" value="1" <?php echo isset($settings['pdf_builder_debug_javascript']) && $settings['pdf_builder_debug_javascript'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span style="margin-left: 10px;">🔧 Debug JS</span>
                            <div style="font-size: 11px; color: #999; margin-top: 5px;">Tous les logs JavaScript</div>
                        </div>

                        <!-- Debug AJAX -->
                        <div style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #e0e0e0;">
                            <label class="toggle-switch" style="margin: 0;">
                                <input type="checkbox" id="debug_ajax" name="pdf_builder_debug_ajax" value="1" <?php echo isset($settings['pdf_builder_debug_ajax']) && $settings['pdf_builder_debug_ajax'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span style="margin-left: 10px;">🔄 Debug AJAX</span>
                            <div style="font-size: 11px; color: #999; margin-top: 5px;">Requêtes AJAX</div>
                        </div>

                        <!-- Debug Performance -->
                        <div style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #e0e0e0;">
                            <label class="toggle-switch" style="margin: 0;">
                                <input type="checkbox" id="debug_performance" name="pdf_builder_debug_performance" value="1" <?php echo isset($settings['pdf_builder_debug_performance']) && $settings['pdf_builder_debug_performance'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span style="margin-left: 10px;">⚡ Performance</span>
                            <div style="font-size: 11px; color: #999; margin-top: 5px;">Temps &amp; mémoire</div>
                        </div>

                        <!-- Debug Database -->
                        <div style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #e0e0e0;">
                            <label class="toggle-switch" style="margin: 0;">
                                <input type="checkbox" id="debug_database" name="pdf_builder_debug_database" value="1" <?php echo isset($settings['pdf_builder_debug_database']) && $settings['pdf_builder_debug_database'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span style="margin-left: 10px;">🗄️ Base de données</span>
                            <div style="font-size: 11px; color: #999; margin-top: 5px;">Requêtes SQL</div>
                        </div>

                        <!-- Force HTTPS -->
                        <div style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #e0e0e0;">
                            <label class="toggle-switch" style="margin: 0;">
                                <input type="checkbox" id="force_https" name="pdf_builder_force_https" value="1" <?php echo isset($settings['pdf_builder_force_https']) && $settings['pdf_builder_force_https'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span style="margin-left: 10px;">🔒 HTTPS forcé</span>
                            <div style="font-size: 11px; color: #999; margin-top: 5px;">API sécurisée</div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>
    </div>

    <!-- Section Logs -->
    <div id="dev-logs-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
    <h3 class="section-title">📝 Configuration des Logs</h3>
    <table class="form-table">
        <tr>
            <th scope="row"><label for="log_level">Niveau de Log</label></th>
            <td>
                <select id="log_level" name="pdf_builder_log_level" style="width: 200px;">
                    <option value="0" <?php echo (isset($settings['pdf_builder_log_level']) && $settings['pdf_builder_log_level'] == 0) ? 'selected' : ''; ?>>Aucun log</option>
                    <option value="1" <?php echo (isset($settings['pdf_builder_log_level']) && $settings['pdf_builder_log_level'] == 1) ? 'selected' : ''; ?>>Erreurs uniquement</option>
                    <option value="2" <?php echo (isset($settings['pdf_builder_log_level']) && $settings['pdf_builder_log_level'] == 2) ? 'selected' : ''; ?>>Erreurs + Avertissements</option>
                    <option value="3" <?php echo (isset($settings['pdf_builder_log_level']) && $settings['pdf_builder_log_level'] == 3) ? 'selected' : ''; ?>>Info complète</option>
                    <option value="4" <?php echo (isset($settings['pdf_builder_log_level']) && $settings['pdf_builder_log_level'] == 4) ? 'selected' : ''; ?>>Détails (Développement)</option>
                </select>
                <p class="description">0=Aucun, 1=Erreurs, 2=Warn, 3=Info, 4=Détails</p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="log_file_size">Taille Max Log</label></th>
            <td>
                <input type="number" id="log_file_size" name="pdf_builder_log_file_size" value="<?php echo isset($settings['pdf_builder_log_file_size']) ? intval($settings['pdf_builder_log_file_size']) : '10'; ?>" min="1" max="100" /> MB
                <p class="description">Rotation automatique au dépassement</p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="log_retention">Retention Logs</label></th>
            <td>
                <input type="number" id="log_retention" name="pdf_builder_log_retention" value="<?php echo isset($settings['pdf_builder_log_retention']) ? intval($settings['pdf_builder_log_retention']) : '30'; ?>" min="1" max="365" /> jours
                <p class="description">Supprime automatiquement les vieux logs</p>
            </td>
        </tr>
    </table>
    </div>

    <!-- Section Outils -->
    <div id="dev-tools-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
    <h3 class="section-title">🛠️ Outils de Développement</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px;">
        <button type="button" id="view_logs_js_btn" class="button button-secondary">📄 Logs JS</button>
        <button type="button" id="clear_cache_btn" class="button button-secondary">🔄 Vider Cache</button>
        <button type="button" id="clear_temp_btn" class="button button-secondary">🗑️ Vider Temp</button>
        <button type="button" id="clear_logs_btn" class="button button-secondary">📋 Vider Logs</button>
    </div>
    </div>

    <!-- Avertissement Production -->
    <div style="background: #ffebee; border-left: 4px solid #d32f2f; border-radius: 4px; padding: 15px; margin-top: 30px;">
        <h3 style="margin-top: 0; color: #c62828;">🚨 Avertissement Production</h3>
        <ul style="margin: 0; padding-left: 20px; color: #c62828; font-size: 14px;">
            <li>❌ Ne jamais laisser le mode développeur ACTIVÉ en production</li>
            <li>❌ Ne jamais afficher les logs détaillés aux utilisateurs</li>
            <li>✓ Utilisez des mots de passe pour protéger les outils dev</li>
        </ul>
    </div>

</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Synchroniser les toggles au chargement
    if (window.pdfBuilderSavedSettings) {
        const settingMap = {
            'developer_enabled': 'pdf_builder_developer_enabled',
            'debug_php_errors': 'pdf_builder_debug_php_errors',
            'debug_javascript': 'pdf_builder_debug_javascript',
            'debug_javascript_verbose': 'pdf_builder_debug_javascript_verbose',
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

    // Gérer la visibilité des sections selon developer_enabled
    const developerToggle = document.getElementById('developer_enabled');
    const devSections = ['dev-debug-section', 'dev-logs-section', 'dev-tools-section'];

    if (developerToggle) {
        developerToggle.addEventListener('change', function() {
            devSections.forEach(sectionId => {
                const section = document.getElementById(sectionId);
                if (section) {
                    section.style.display = this.checked ? 'block' : 'none';
                }
            });
        });
    }

    // Toggle mot de passe
    const togglePasswordBtn = document.getElementById('toggle_password');
    const passwordField = document.getElementById('developer_password');

    if (togglePasswordBtn && passwordField) {
        togglePasswordBtn.addEventListener('click', function() {
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                togglePasswordBtn.textContent = '🙈 Masquer';
            } else {
                passwordField.type = 'password';
                togglePasswordBtn.textContent = '👁️ Afficher';
            }
        });
    }

    // Boutons outils
    const viewLogsJsBtn = document.getElementById('view_logs_js_btn');
    const clearCacheBtn = document.getElementById('clear_cache_btn');
    const clearTempBtn = document.getElementById('clear_temp_btn');
    const clearLogsBtn = document.getElementById('clear_logs_btn');

    if (viewLogsJsBtn) {
        viewLogsJsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.open(window.pdfBuilderLogsUrl || '/wp-content/plugins/pdf-builder-pro/logs/', '_blank');
        });
    }

    if (clearCacheBtn) {
        clearCacheBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Vider le cache du plugin ?')) {
                makeAjaxCall('pdf_builder_clear_cache', clearCacheBtn);
            }
        });
    }

    if (clearTempBtn) {
        clearTempBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Vider les fichiers temporaires ?')) {
                makeAjaxCall('pdf_builder_clear_temp', clearTempBtn);
            }
        });
    }

    if (clearLogsBtn) {
        clearLogsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Vider tous les logs ?')) {
                makeAjaxCall('pdf_builder_clear_logs', clearLogsBtn);
            }
        });
    }

    // AJAX Helper
    function makeAjaxCall(action, button) {
        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = '⏳ Traitement...';

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
            button.disabled = false;
            button.textContent = originalText;
            
            if (data.success) {
                alert('✅ ' + (data.data?.message || 'Opération réussie'));
            } else {
                alert('❌ ' + (data.data?.message || 'Erreur'));
            }
        })
        .catch(err => {
            button.disabled = false;
            button.textContent = originalText;
            alert('❌ Erreur de connexion');
            console.error(err);
        });
    }
});
</script>
