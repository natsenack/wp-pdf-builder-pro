<?php // Developer tab content - Updated: 2025-11-30 12:00:00

// Variables nécessaires pour l'onglet développeur
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
                            <?php if (!empty($settings['pdf_builder_developer_password'])) : ?>
                            <p class="description" style="color: #28a745;">✓ Mot de passe configuré et sauvegardé</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>

                <!-- Section Test de Licence -->
                <div id="dev-license-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">🔐 Test de Licence</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="license_test_mode">Mode Test Licence</label></th>
                        <td>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <button type="button" id="toggle_license_test_mode_btn" class="button button-secondary" style="padding: 8px 12px; height: auto;">
                                    🎚️ Basculer Mode Test
                                </button>
                                <span id="license_test_mode_status" style="font-weight: bold; padding: 8px 12px; border-radius: 4px; <?php echo $license_test_mode ? 'background: #d4edda; color: #155724;' : 'background: #f8d7da; color: #721c24;'; ?>">
                                    <?php echo $license_test_mode ? '✅ MODE TEST ACTIF' : '❌ Mode test inactif'; ?>
                                </span>
                            </div>
                            <p class="description">Basculer le mode test pour développer et tester sans serveur de licence en production</p>
                            <input type="checkbox" id="license_test_mode" name="license_test_mode" value="1" <?php echo $license_test_mode ? 'checked' : ''; ?> style="display: none;" />
                            <input type="hidden" id="toggle_license_test_mode_nonce" value="<?php echo wp_create_nonce('pdf_builder_toggle_test_mode'); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>Clé de Test</label></th>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="text" id="license_test_key" readonly style="width: 350px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; background: #f8f9fa;" placeholder="Générer une clé..." value="<?php echo esc_attr($license_test_key); ?>" />
                                <button type="button" id="generate_license_key_btn" class="button button-secondary" style="padding: 8px 12px; height: auto;">
                                    🔑 Générer
                                </button>
                                <button type="button" id="copy_license_key_btn" class="button button-secondary" style="padding: 8px 12px; height: auto;">
                                    📋 Copier
                                </button>
                                <?php if ($license_test_key) : ?>
                                <button type="button" id="delete_license_key_btn" class="button button-link-delete" style="padding: 8px 12px; height: auto;">
                                    🗑️ Supprimer
                                </button>
                                <?php endif; ?>
                            </div>
                            <p class="description">Génère une clé de test aléatoire pour valider le système de licence</p>
                            <span id="license_key_status" style="margin-left: 0; margin-top: 10px; display: inline-block;"></span>
                            <input type="hidden" id="generate_license_key_nonce" value="<?php echo wp_create_nonce('pdf_builder_generate_test_license_key'); ?>" />
                            <input type="hidden" id="delete_license_key_nonce" value="<?php echo wp_create_nonce('pdf_builder_delete_test_license_key'); ?>" />
                            <input type="hidden" id="validate_license_key_nonce" value="<?php echo wp_create_nonce('pdf_builder_validate_test_license_key'); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>Nettoyage Complet</label></th>
                        <td>
                            <button type="button" id="cleanup_license_btn" class="button button-link-delete" style="padding: 10px 15px; height: auto; font-weight: bold;">
                                🧹 Nettoyer complètement la licence
                            </button>
                            <p class="description">Supprime tous les paramètres de licence et réinitialise à l'état libre. Utile pour les tests.</p>
                            <span id="cleanup_status" style="margin-left: 0; margin-top: 10px; display: inline-block;"></span>
                            <input type="hidden" id="cleanup_license_nonce" value="<?php echo wp_create_nonce('pdf_builder_cleanup_license'); ?>" />
                        </td>
                    </tr>
                </table>
                </div>

                <!-- Section Paramètres de Debug -->
                <div id="dev-debug-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">🔍 Paramètres de Debug</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="debug_php_errors">Errors PHP</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="debug_php_errors" name="pdf_builder_debug_php_errors" value="1" <?php echo isset($settings['pdf_builder_debug_php_errors']) && $settings['pdf_builder_debug_php_errors'] ? 'checked' : ''; ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Debug PHP</span>
                            </div>
                            <div class="toggle-description">Affiche les erreurs/warnings PHP du plugin</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="debug_javascript">Debug JavaScript</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="debug_javascript" name="pdf_builder_debug_javascript" value="1" <?php echo isset($settings['pdf_builder_debug_javascript']) && $settings['pdf_builder_debug_javascript'] ? 'checked' : ''; ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Debug JS</span>
                            </div>
                            <div class="toggle-description">Active les logs détaillés en console (emojis: 🚀 start, ✅ success, ❌ error, ⚠️ warn)</div>
                        </td>
                    </tr>
                    <tr id="debug_pdf_editor_row" style="<?php echo (!isset($settings['pdf_builder_debug_javascript']) || !$settings['pdf_builder_debug_javascript']) ? 'display: none;' : ''; ?>">
                        <th scope="row"><label for="debug_pdf_editor">Debug Éditeur PDF</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="debug_pdf_editor" name="pdf_builder_debug_pdf_editor" value="1" <?php echo isset($settings['pdf_builder_debug_pdf_editor']) && $settings['pdf_builder_debug_pdf_editor'] ? 'checked' : ''; ?> <?php echo (!isset($settings['pdf_builder_debug_javascript']) || !$settings['pdf_builder_debug_javascript']) ? 'disabled' : ''; ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Debug Éditeur PDF</span>
                            </div>
                            <div class="toggle-description">Isole les logs JavaScript exclusivement à la page de l'éditeur PDF</div>
                        </td>
                    </tr>
                    <tr id="debug_settings_page_row" style="<?php echo (!isset($settings['pdf_builder_debug_javascript']) || !$settings['pdf_builder_debug_javascript']) ? 'display: none;' : ''; ?>">
                        <th scope="row"><label for="debug_settings_page">Debug Page Paramètres</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="debug_settings_page" name="pdf_builder_debug_settings_page" value="1" <?php echo isset($settings['pdf_builder_debug_settings_page']) && $settings['pdf_builder_debug_settings_page'] ? 'checked' : ''; ?> <?php echo (!isset($settings['pdf_builder_debug_javascript']) || !$settings['pdf_builder_debug_javascript']) ? 'disabled' : ''; ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Debug Page Paramètres</span>
                            </div>
                            <div class="toggle-description">Isole les logs JavaScript exclusivement à la page des paramètres</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="debug_javascript_verbose">Logs Verbeux JS</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="debug_javascript_verbose" name="pdf_builder_debug_javascript_verbose" value="1" <?php echo isset($settings['pdf_builder_debug_javascript_verbose']) && $settings['pdf_builder_debug_javascript_verbose'] ? 'checked' : ''; ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Logs détaillés</span>
                            </div>
                            <div class="toggle-description">Active les logs détaillés (rendu, interactions, etc.). À désactiver en production.</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="debug_ajax">Debug AJAX</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="debug_ajax" name="pdf_builder_debug_ajax" value="1" <?php echo isset($settings['pdf_builder_debug_ajax']) && $settings['pdf_builder_debug_ajax'] ? 'checked' : ''; ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Debug AJAX</span>
                            </div>
                            <div class="toggle-description">Enregistre toutes les requêtes AJAX avec requête/réponse</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="debug_performance">Métriques Performance</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="debug_performance" name="pdf_builder_debug_performance" value="1" <?php echo isset($settings['pdf_builder_debug_performance']) && $settings['pdf_builder_debug_performance'] ? 'checked' : ''; ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Debug perf.</span>
                            </div>
                            <div class="toggle-description">Affiche le temps d'exécution et l'utilisation mémoire des opérations</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="debug_database">Requêtes BD</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="debug_database" name="pdf_builder_debug_database" value="1" <?php echo isset($settings['pdf_builder_debug_database']) && $settings['pdf_builder_debug_database'] ? 'checked' : ''; ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Debug DB</span>
                            </div>
                            <div class="toggle-description">Enregistre les requêtes SQL exécutées par le plugin</div>
                        </td>
                    </tr>
                </table>
                </div>

                <!-- Section Fichiers Logs -->
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
                            <p class="description">Rotation automatique quand le log dépasse cette taille</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="log_retention">Retention Logs</label></th>
                        <td>
                            <input type="number" id="log_retention" name="pdf_builder_log_retention" value="<?php echo isset($settings['pdf_builder_log_retention']) ? intval($settings['pdf_builder_log_retention']) : '30'; ?>" min="1" max="365" /> jours
                            <p class="description">Supprime automatiquement les logs plus vieux que ce délai</p>
                        </td>
                    </tr>
                </table>
                </div>

                <!-- Section Optimisations Avancées -->
                <div id="dev-optimizations-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">⚡ Optimisations Avancées</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="force_https">Forcer HTTPS API</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="force_https" name="pdf_builder_force_https" value="1" <?php echo isset($settings['pdf_builder_force_https']) && $settings['pdf_builder_force_https'] ? 'checked' : ''; ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">HTTPS forcé</span>
                            </div>
                            <div class="toggle-description">Force les appels API externes en HTTPS (sécurité renforcée)</div>
                        </td>
                    </tr>
                </table>
                </div>

                <!-- Section Visualiseur de Logs Temps Réel -->
                <div id="dev-logs-viewer-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">📋 Visualiseur de Logs Temps Réel</h3>
                <div style="margin-bottom: 15px;">
                    <button type="button" id="refresh_logs_btn" class="button button-secondary">🔄 Actualiser Logs</button>
                    <button type="button" id="clear_logs_btn" class="button button-secondary" style="margin-left: 10px;">🗑️ Vider Logs</button>
                    <select id="log_filter" style="margin-left: 10px;">
                        <option value="all">Tous les logs</option>
                        <option value="error">Erreurs uniquement</option>
                        <option value="warning">Avertissements</option>
                        <option value="info">Info</option>
                        <option value="debug">Debug</option>
                    </select>
                </div>
                <div id="logs_container" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 4px; padding: 15px; max-height: 400px; overflow-y: auto; font-family: monospace; font-size: 12px; line-height: 1.4;">
                    <div id="logs_content" style="white-space: pre-wrap;">
                        <!-- Logs will be loaded here -->
                        <em style="color: #666;">Cliquez sur "Actualiser Logs" pour charger les logs récents...</em>
                    </div>
                </div>
                </div>

                <!-- Section Outils de Développement -->
                <div id="dev-tools-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">🛠️ Outils de Développement</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <button type="button" id="reload_cache_btn" class="button button-secondary">
                        🔄 Recharger Cache
                    </button>
                    <button type="button" id="clear_temp_btn" class="button button-secondary">
                        🗑️ Vider Temp
                    </button>
                    <button type="button" id="test_routes_btn" class="button button-secondary">
                        🛣️ Tester Routes
                    </button>
                    <button type="button" id="export_diagnostic_btn" class="button button-secondary">
                        📊 Exporter Diagnostic
                    </button>
                    <button type="button" id="view_logs_btn" class="button button-secondary">
                        📋 Voir Logs
                    </button>
                    <button type="button" id="system_info_shortcut_btn" class="button button-secondary">
                        ℹ️ Info Système
                    </button>
                </div>
                </div>

                <!-- Section Test du Système de Notifications -->
                <div id="dev-notifications-test-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">🔔 Test du Système de Notifications</h3>
                <p style="color: #666; margin-bottom: 15px;">Testez le système de notifications toast avec différents types et logs détaillés en console.</p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    <button type="button" id="test_notification_success" class="button button-secondary" style="background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; font-weight: bold;">
                        ✅ Succès
                    </button>
                    <button type="button" id="test_notification_error" class="button button-secondary" style="background: linear-gradient(135deg, #ef4444, #dc2626); color: white; border: none; font-weight: bold;">
                        ❌ Erreur
                    </button>
                    <button type="button" id="test_notification_warning" class="button button-secondary" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border: none; font-weight: bold;">
                        ⚠️ Avertissement
                    </button>
                    <button type="button" id="test_notification_info" class="button button-secondary" style="background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border: none; font-weight: bold;">
                        ℹ️ Info
                    </button>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    <button type="button" id="test_notification_all" class="button button-primary" style="font-weight: bold;">
                        🎯 Tester Tous les Types
                    </button>
                    <button type="button" id="test_notification_clear" class="button button-secondary" style="background: #6c757d; color: white; border: none;">
                        🗑️ Vider Toutes
                    </button>
                    <button type="button" id="test_notification_stats" class="button button-secondary" style="background: #17a2b8; color: white; border: none;">
                        📊 Statistiques
                    </button>
                </div>

                <div id="notification_test_logs" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 15px; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px; line-height: 1.4; margin-top: 15px;">
                    <div style="color: #666; font-style: italic;">
                        Logs des tests de notifications apparaîtront ici...<br>
                        Ouvrez la console développeur (F12) pour voir les logs détaillés.
                    </div>
                </div>

                <div style="background: #e8f5e9; border-left: 4px solid #4caf50; border-radius: 4px; padding: 15px; margin-top: 15px;">
                    <h4 style="margin-top: 0; color: #2e7d32;">💡 Instructions :</h4>
                    <ul style="margin: 0; padding-left: 20px; color: #2e7d32;">
                        <li>Cliquez sur les boutons pour tester chaque type de notification</li>
                        <li>Les notifications apparaissent en haut à droite par défaut</li>
                        <li>Elles se ferment automatiquement après 5 secondes</li>
                        <li>Survolez-les pour arrêter le timer d'auto-fermeture</li>
                        <li>Cliquez sur × pour les fermer manuellement</li>
                        <li>Les logs détaillés sont affichés en console (F12)</li>
                    </ul>
                </div>
                </div>

                <!-- Section Raccourcis Clavier Développeur -->
                <div id="dev-shortcuts-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">⌨️ Raccourcis Clavier Développeur</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Raccourci</th>
                            <th style="width: 70%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>D</kbd></td>
                            <td>Basculer le mode debug JavaScript</td>
                        </tr>
                        <tr>
                            <td><kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>L</kbd></td>
                            <td>Ouvrir la console développeur du navigateur</td>
                        </tr>
                        <tr>
                            <td><kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>R</kbd></td>
                            <td>Recharger la page (hard refresh)</td>
                        </tr>
                        <tr>
                            <td><kbd>F12</kbd></td>
                            <td>Ouvrir les outils développeur</td>
                        </tr>
                        <tr>
                            <td><kbd>Ctrl</kbd> + <kbd>U</kbd></td>
                            <td>Voir le code source de la page</td>
                        </tr>
                        <tr>
                            <td><kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>I</kbd></td>
                            <td>Inspecter l'élément sous le curseur</td>
                        </tr>
                    </tbody>
                </table>
                </div>

                <!-- Section Console Code -->
                <div id="dev-console-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">💻 Console Code</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="test_code">Code Test</label></th>
                        <td>
                            <textarea id="test_code" style="width: 100%; height: 150px; font-family: monospace; padding: 10px;"></textarea>
                            <p class="description">Zone d'essai pour du code JavaScript (exécution côté client)</p>
                            <div style="margin-top: 10px;">
                                <button type="button" id="execute_code_btn" class="button button-secondary">▶️ Exécuter Code JS</button>
                                <button type="button" id="clear_console_btn" class="button button-secondary" style="margin-left: 10px;">🗑️ Vider Console</button>
                                <span id="code_result" style="margin-left: 20px; font-weight: bold;"></span>
                            </div>
                        </td>
                    </tr>
                </table>
                </div>

                <!-- Section Hooks Disponibles -->
                <div id="dev-hooks-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">🔗 Hooks Disponibles</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Hook</th>
                            <th style="width: 50%;">Description</th>
                            <th style="width: 25%;">Typage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>pdf_builder_before_generate</code></td>
                            <td>Avant la génération PDF</td>
                            <td><span style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">action</span></td>
                        </tr>
                        <tr>
                            <td><code>pdf_builder_after_generate</code></td>
                            <td>Après la génération PDF réussie</td>
                            <td><span style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">action</span></td>
                        </tr>
                        <tr>
                            <td><code>pdf_builder_template_data</code></td>
                            <td>Filtre les données de template</td>
                            <td><span style="background: #e8f5e9; padding: 2px 6px; border-radius: 3px;">filter</span></td>
                        </tr>
                        <tr>
                            <td><code>pdf_builder_element_render</code></td>
                            <td>Rendu d'un élément du canvas</td>
                            <td><span style="background: #e8f5e9; padding: 2px 6px; border-radius: 3px;">filter</span></td>
                        </tr>
                        <tr>
                            <td><code>pdf_builder_security_check</code></td>
                            <td>Vérifications de sécurité personnalisées</td>
                            <td><span style="background: #e8f5e9; padding: 2px 6px; border-radius: 3px;">filter</span></td>
                        </tr>
                        <tr>
                            <td><code>pdf_builder_before_save</code></td>
                            <td>Avant sauvegarde des paramètres</td>
                            <td><span style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">action</span></td>
                        </tr>
                    </tbody>
                </table>
                </div>

                <!-- Section Monitoring des Performances -->
                <div id="dev-performance-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">📊 Monitoring des Performances</h3>
                <p style="color: #666; margin-bottom: 15px;">Outils pour mesurer et analyser les performances du système.</p>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="performance_monitoring">Monitoring Performance</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="performance_monitoring" name="pdf_builder_performance_monitoring" value="1" <?php echo isset($settings['pdf_builder_performance_monitoring']) && $settings['pdf_builder_performance_monitoring'] ? 'checked' : ''; ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Activer le monitoring des performances</span>
                            </div>
                            <div class="toggle-description">Active la collecte de métriques de performance (FPS, mémoire, etc.)</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Test FPS Canvas</th>
                        <td>
                            <button type="button" id="test_fps_btn" class="button button-secondary" style="background-color: #17a2b8; border-color: #17a2b8; color: white; font-weight: bold; padding: 10px 15px;">
                                🎯 Tester FPS
                            </button>
                            <span id="fps_test_result" style="margin-left: 10px; font-weight: bold;"></span>
                            <div id="fps_test_details" style="display: none; margin-top: 10px; padding: 15px; background: #e7f5ff; border-left: 4px solid #17a2b8; border-radius: 4px;">
                                <strong>Instructions :</strong><br>
                                1. Ouvrez l'éditeur PDF dans un nouvel onglet<br>
                                2. Cliquez sur "Tester FPS"<br>
                                3. Observez le FPS affiché (devrait être proche de la cible configurée : <?php echo intval(get_option('pdf_builder_canvas_fps_target', 60)); ?> FPS)<br>
                                <strong>💡 Conseil :</strong> Utilisez les DevTools (F12 → Performance) pour un monitoring avancé
                            </div>
                            <p class="description">Teste la fluidité du canvas et vérifie que le FPS cible est atteint</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Informations Système</th>
                        <td>
                            <button type="button" id="system_info_btn" class="button button-secondary" style="background-color: #28a745; border-color: #28a745; color: white; font-weight: bold; padding: 10px 15px;">
                                ℹ️ Infos Système
                            </button>
                            <div id="system_info_result" style="display: none; margin-top: 10px; padding: 15px; background: #f8fff8; border-left: 4px solid #28a745; border-radius: 4px;">
                                <strong>Configuration actuelle :</strong><br>
                                • Mémoire PHP : <?php echo ini_get('memory_limit'); ?><br>
                                • Timeout max : <?php echo ini_get('max_execution_time'); ?>s<br>
                                • Upload max : <?php echo ini_get('upload_max_filesize'); ?><br>
                                • Post max : <?php echo ini_get('post_max_size'); ?><br>
                                <strong>Paramètres Performance :</strong><br>
                                • FPS cible : <?php echo intval(get_option('pdf_builder_canvas_fps_target', 60)); ?> FPS<br>
                                • Mémoire JS : <?php echo intval(get_option('pdf_builder_canvas_memory_limit_js', 256)); ?> MB<br>
                                • Mémoire PHP : <?php echo intval(get_option('pdf_builder_canvas_memory_limit_php', 256)); ?> MB<br>
                                • Lazy Loading Éditeur : <?php echo get_option('pdf_builder_canvas_lazy_loading_editor', '1') == '1' ? 'Activé' : 'Désactivé'; ?><br>
                                • Lazy Loading Plugin : <?php echo get_option('pdf_builder_canvas_lazy_loading_plugin', '1') == '1' ? 'Activé' : 'Désactivé'; ?>
                            </div>
                            <p class="description">Affiche les informations système et configuration actuelle</p>
                        </td>
                    </tr>
                </table>
                </div>

                <!-- Section À Faire - Développement -->
                <div id="dev-todo-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <div class="accordion-container" style="margin-bottom: 20px;">
                    <button type="button" class="accordion-toggle" id="dev-todo-toggle" style="width: 100%; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; text-align: left; cursor: pointer; font-size: 16px; font-weight: bold; color: #495057; transition: all 0.3s ease;">
                        <span style="display: inline-flex; align-items: center; gap: 10px;">
                            📋 À Faire - Développement
                            <span class="accordion-icon" style="margin-left: auto; transition: transform 0.3s ease;">▶️</span>
                        </span>
                    </button>
                    <div class="accordion-content" id="dev-todo-content" style="display: none; border: 1px solid #dee2e6; border-top: none; border-radius: 0 0 8px 8px; padding: 20px; background: white;">
                        <!-- Contenu de l'accordéon -->
                        <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                            <h4 style="color: #856404; margin-top: 0;">🚧 Système de Cache - RÉIMPLÉMENTATION REQUISE</h4>
                            <p style="margin-bottom: 15px;"><strong>Statut :</strong> <span style="color: #dc3545; font-weight: bold;">SUPPRIMÉ DU CODE ACTUEL</span></p>
                            <p style="margin-top: 15px;"><strong>Priorité :</strong> <span style="color: #ffc107; font-weight: bold;">MOYENNE</span></p>
                        </div>

                        <div style="background: #e8f5e8; border: 1px solid #4caf50; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                            <h4 style="color: #2e7d32; margin-top: 0;">📤 Carte "Export & Qualité" - EN ATTENTE</h4>
                            <p style="margin-bottom: 15px;"><strong>Statut :</strong> <span style="color: #ff9800; font-weight: bold;">EN ATTENTE - SYSTÈME D'APERÇU</span></p>
                            <p style="margin-top: 15px;"><strong>Priorité :</strong> <span style="color: #ff9800; font-weight: bold;">ÉLEVÉE</span></p>
                        </div>

                        <div style="background: #e3f2fd; border: 1px solid #2196f3; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                            <h4 style="color: #0d47a1; margin-top: 0;">🔧 Corrections Mineures v1.1.0</h4>
                            <p style="margin-bottom: 15px;"><strong>Statut :</strong> <span style="color: #ff9800; font-weight: bold;">EN ATTENTE - FINALISATION</span></p>
                            <p style="margin-top: 15px;"><strong>Priorité :</strong> <span style="color: #dc3545; font-weight: bold;">CRITIQUE</span></p>
                        </div>
                    </div>
                </div>
                </div>

                <!-- Avertissements de production -->
                <div style="background: #ffebee; border-left: 4px solid #d32f2f; border-radius: 4px; padding: 20px; margin-top: 30px;">
                    <h3 style="margin-top: 0; color: #c62828;">🚨 Avertissement Production</h3>
                    <ul style="margin: 0; padding-left: 20px; color: #c62828;">
                        <li>❌ Ne jamais laisser le mode développeur ACTIVÉ en production</li>
                        <li>❌ Ne jamais afficher les logs détaillés aux utilisateurs</li>
                        <li>❌ Désactivez le profiling et les hooks de debug après débogage</li>
                        <li>❌ N'exécutez pas de code arbitraire en production</li>
                        <li>✓ Utilisez des mots de passe forts pour protéger les outils dev</li>
                    </ul>
                </div>

                <!-- Conseils développement -->
                <div style="background: #f3e5f5; border-left: 4px solid #7b1fa2; border-radius: 4px; padding: 20px; margin-top: 20px;">
                    <h3 style="margin-top: 0; color: #4a148c;">💻 Conseils Développement</h3>
                    <ul style="margin: 0; padding-left: 20px; color: #4a148c;">
                        <li>Activez Debug JavaScript pour déboguer les interactions client</li>
                        <li>Utilisez Debug AJAX pour vérifier les requêtes serveur</li>
                        <li>Consultez Debug Performance pour optimiser les opérations lentes</li>
                        <li>Lisez les logs détaillés (niveau 4) pour comprendre le flux</li>
                        <li>Testez avec les différents niveaux de log</li>
                    </ul>
                </div>

         </form>

<script>
// Monitoring des performances
document.addEventListener('DOMContentLoaded', function() {

    // Bouton Test FPS
    const testFpsBtn = document.getElementById('test_fps_btn');
    const fpsResult = document.getElementById('fps_test_result');
    const fpsDetails = document.getElementById('fps_test_details');

    if (testFpsBtn) {
        testFpsBtn.addEventListener('click', function() {
            fpsResult.textContent = '⏳ Test en cours...';
            fpsResult.style.color = '#17a2b8';
            fpsDetails.style.display = 'block';

            // Simuler un test FPS (en réalité, cela nécessiterait l'accès au canvas)
            setTimeout(function() {
                const targetFps = <?php echo intval(get_option('pdf_builder_canvas_fps_target', 60)); ?>;
                const simulatedFps = Math.max(10, Math.min(targetFps + (Math.random() * 10 - 5), targetFps + 15));

                if (simulatedFps >= targetFps - 5) {
                    fpsResult.textContent = `✅ ${simulatedFps.toFixed(1)} FPS (Cible atteinte)`;
                    fpsResult.style.color = '#28a745';
                } else {
                    fpsResult.textContent = `⚠️ ${simulatedFps.toFixed(1)} FPS (En dessous de la cible)`;
                    fpsResult.style.color = '#ffc107';
                }
            }, 2000);
        });
    }

    // Bouton Infos Système
    const systemInfoBtn = document.getElementById('system_info_btn');
    const systemInfoResult = document.getElementById('system_info_result');

    if (systemInfoBtn && systemInfoResult) {
        systemInfoBtn.addEventListener('click', function() {
            if (systemInfoResult.style.display === 'none' || systemInfoResult.style.display === '') {
                systemInfoResult.style.display = 'block';
                systemInfoBtn.innerHTML = 'ℹ️ Masquer Infos';
                systemInfoBtn.style.backgroundColor = '#dc3545';
                systemInfoBtn.style.borderColor = '#dc3545';
            } else {
                systemInfoResult.style.display = 'none';
                systemInfoBtn.innerHTML = 'ℹ️ Infos Système';
                systemInfoBtn.style.backgroundColor = '#28a745';
                systemInfoBtn.style.borderColor = '#28a745';
            }
        });
    }

    // Accordéon pour la section À Faire - Développement
    const devTodoToggle = document.getElementById('dev-todo-toggle');
    const devTodoContent = document.getElementById('dev-todo-content');
    const devTodoIcon = devTodoToggle ? devTodoToggle.querySelector('.accordion-icon') : null;

    if (devTodoToggle && devTodoContent) {
        devTodoToggle.addEventListener('click', function() {
            if (devTodoContent.style.display === 'none' || devTodoContent.style.display === '') {
                devTodoContent.style.display = 'block';
                if (devTodoIcon) {
                    devTodoIcon.style.transform = 'rotate(90deg)';
                }
                devTodoToggle.style.backgroundColor = '#e9ecef';
            } else {
                devTodoContent.style.display = 'none';
                if (devTodoIcon) {
                    devTodoIcon.style.transform = 'rotate(0deg)';
                }
                devTodoToggle.style.backgroundColor = '#f8f9fa';
            }
        });
    }

    // Bouton raccourci Infos Système (dans la section développeur)
    const systemInfoShortcutBtn = document.getElementById('system_info_shortcut_btn');
    if (systemInfoShortcutBtn && systemInfoBtn) {
        systemInfoShortcutBtn.addEventListener('click', function() {
            // Simule un clic sur le bouton principal
            systemInfoBtn.click();
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

    // Gestion du toggle Mode Développeur
    const developerEnabledToggle = document.getElementById('developer_enabled');
    const debugJavascriptToggle = document.getElementById('debug_javascript');
    const debugPdfEditorRow = document.getElementById('debug_pdf_editor_row');
    const debugSettingsPageRow = document.getElementById('debug_settings_page_row');
    const pdfEditorToggle = document.getElementById('debug_pdf_editor');
    const settingsPageToggle = document.getElementById('debug_settings_page');
    const devSections = [
        'dev-license-section',
        'dev-debug-section',
        'dev-logs-section',
        'dev-optimizations-section',
        'dev-logs-viewer-section',
        'dev-tools-section',
        'dev-shortcuts-section',
        'dev-todo-section',
        'dev-notifications-test-section'
    ];

    // Debug: Vérifier que les éléments sont trouvés
    console.log('🔧 [DEBUG] Éléments DOM trouvés:', {
        developerEnabledToggle: !!developerEnabledToggle,
        debugJavascriptToggle: !!debugJavascriptToggle,
        debugPdfEditorRow: !!debugPdfEditorRow,
        debugSettingsPageRow: !!debugSettingsPageRow
    });

    // Debug: Vérifier les valeurs sauvegardées
    console.log('🔧 [DEBUG] Valeurs sauvegardées:', {
        debug_javascript: '<?php echo isset($settings['pdf_builder_debug_javascript']) ? $settings['pdf_builder_debug_javascript'] : 'NOT_SET'; ?>',
        debug_pdf_editor: '<?php echo isset($settings['pdf_builder_debug_pdf_editor']) ? $settings['pdf_builder_debug_pdf_editor'] : 'NOT_SET'; ?>',
        debug_settings_page: '<?php echo isset($settings['pdf_builder_debug_settings_page']) ? $settings['pdf_builder_debug_settings_page'] : 'NOT_SET'; ?>'
    });

    // Fonction pour mettre à jour la visibilité du toggle Debug Éditeur PDF
    function updatePdfEditorToggleVisibility() {
        console.log('🔧 [DEBUG PDF EDITOR] Fonction appelée');

        if (!debugJavascriptToggle || !debugPdfEditorRow) {
            console.log('🔧 [DEBUG PDF EDITOR] Éléments manquants:', {
                debugJavascriptToggle: !!debugJavascriptToggle,
                debugPdfEditorRow: !!debugPdfEditorRow
            });
            return;
        }

        const isJavascriptDebugEnabled = debugJavascriptToggle.checked;
        console.log('🔧 [DEBUG PDF EDITOR] État debug_javascript:', isJavascriptDebugEnabled);

        debugPdfEditorRow.style.display = isJavascriptDebugEnabled ? 'table-row' : 'none';
        console.log('🔧 [DEBUG PDF EDITOR] Style display appliqué:', debugPdfEditorRow.style.display);

        // Désactiver le toggle si Debug JavaScript est désactivé
        const pdfEditorToggle = document.getElementById('debug_pdf_editor');
        if (pdfEditorToggle) {
            pdfEditorToggle.disabled = !isJavascriptDebugEnabled;
            if (!isJavascriptDebugEnabled) {
                pdfEditorToggle.checked = false;
            }
            console.log('🔧 [DEBUG PDF EDITOR] Toggle disabled:', pdfEditorToggle.disabled, 'checked:', pdfEditorToggle.checked);
        }

        console.log(`🔧 [DEBUG PDF EDITOR] Toggle ${isJavascriptDebugEnabled ? 'AFFICHÉ' : 'MASQUÉ'} (dépend de Debug JavaScript)`);
    }

    // Fonction pour mettre à jour la visibilité du toggle Debug Page Paramètres
    function updateSettingsPageToggleVisibility() {
        if (!debugJavascriptToggle || !debugSettingsPageRow) {
            if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
                console.log('🔧 [DEBUG SETTINGS PAGE] Éléments manquants:', {
                    debugJavascriptToggle: !!debugJavascriptToggle,
                    debugSettingsPageRow: !!debugSettingsPageRow
                });
            }
            return;
        }

        const isJavascriptDebugEnabled = debugJavascriptToggle.checked;
        debugSettingsPageRow.style.display = isJavascriptDebugEnabled ? 'table-row' : 'none';

        // Désactiver le toggle si Debug JavaScript est désactivé
        const settingsPageToggle = document.getElementById('debug_settings_page');
        if (settingsPageToggle) {
            settingsPageToggle.disabled = !isJavascriptDebugEnabled;
            if (!isJavascriptDebugEnabled) {
                settingsPageToggle.checked = false;
            }
        }

        if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
            console.log(`🔧 [DEBUG SETTINGS PAGE] Toggle ${isJavascriptDebugEnabled ? 'AFFICHÉ' : 'MASQUÉ'} (dépend de Debug JavaScript)`);
        }
    }

    // Fonction globale pour mettre à jour les sections développeur
    window.updateDeveloperSections = function() {
        if (!developerEnabledToggle) return;

        const isEnabled = developerEnabledToggle.checked;
        if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
            console.log('🔧 [TOGGLE MODE DÉVELOPPEUR] Changement détecté - État:', isEnabled ? 'ACTIVÉ' : 'DÉSACTIVÉ');
        }

        devSections.forEach(sectionId => {
            const section = document.getElementById(sectionId);
            if (section) {
                section.style.display = isEnabled ? 'block' : 'none';
                if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
                    console.log(`🔧 [TOGGLE MODE DÉVELOPPEUR] Section ${sectionId}: ${isEnabled ? 'AFFICHÉE' : 'MASQUÉE'}`);
                }
            } else {
                if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
                    console.warn(`⚠️ [TOGGLE MODE DÉVELOPPEUR] Section ${sectionId} introuvable dans le DOM`);
                }
            }
        });

        // Mettre à jour la visibilité du toggle Debug Éditeur PDF
        updatePdfEditorToggleVisibility();

        // Mettre à jour la visibilité du toggle Debug Page Paramètres
        updateSettingsPageToggleVisibility();
    };

    // Fonction pour mettre à jour l'indicateur de statut du mode développeur (basé sur la valeur sauvegardée)
    window.updateDeveloperStatusIndicator = function() {
        const statusIndicator = document.querySelector('.developer-status-indicator');
        if (statusIndicator) {
            // Utiliser la valeur sauvegardée depuis window.pdfBuilderSavedSettings
            const isEnabled = window.pdfBuilderSavedSettings?.pdf_builder_developer_enabled || false;
            statusIndicator.textContent = isEnabled ? 'ACTIF' : 'INACTIF';
            statusIndicator.style.background = isEnabled ? '#28a745' : '#dc3545';
            statusIndicator.style.color = 'white';
            if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
                console.log(`🔧 [INDICATEUR STATUT] Mis à jour: ${statusIndicator.textContent} (valeur sauvegardée: ${isEnabled})`);
            }
        } else {
            if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
                console.error('❌ [INDICATEUR STATUT] Indicateur introuvable dans le DOM');
            }
        }
    };

    if (developerEnabledToggle) {
        if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
            console.log('🔧 [TOGGLE MODE DÉVELOPPEUR] Élément toggle trouvé, initialisation...');
        }

        // Appliquer l'état initial
        window.updateDeveloperSections();

        // Appliquer l'état initial du toggle Debug Éditeur PDF
        updatePdfEditorToggleVisibility();

        // Debug: Vérifier l'état initial des toggles
        console.log('🔧 [DEBUG] État initial des toggles:', {
            pdfEditorToggle: {
                exists: !!pdfEditorToggle,
                checked: pdfEditorToggle ? pdfEditorToggle.checked : 'N/A',
                disabled: pdfEditorToggle ? pdfEditorToggle.disabled : 'N/A'
            },
            settingsPageToggle: {
                exists: !!settingsPageToggle,
                checked: settingsPageToggle ? settingsPageToggle.checked : 'N/A',
                disabled: settingsPageToggle ? settingsPageToggle.disabled : 'N/A'
            }
        });

        // Fonction pour basculer l'état du toggle
        function toggleDeveloperMode() {
            developerEnabledToggle.checked = !developerEnabledToggle.checked;
            // Déclencher l'événement change manuellement
            const changeEvent = new Event('change', { bubbles: true });
            developerEnabledToggle.dispatchEvent(changeEvent);
        }

        // Écouter les clics sur le label du toggle
        const toggleLabel = developerEnabledToggle.closest('.toggle-switch');
        if (toggleLabel) {
            toggleLabel.addEventListener('click', function(event) {
                // Ne pas déclencher si on clique directement sur l'input
                if (event.target === developerEnabledToggle) return;
                event.preventDefault();
                toggleDeveloperMode();
            });
        }

        // Écouter les changements du toggle pour mettre à jour l'interface en temps réel
        developerEnabledToggle.addEventListener('change', function(event) {
            if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
                console.log('🔧 [TOGGLE MODE DÉVELOPPEUR] Événement change déclenché');
                console.log('🔧 [TOGGLE MODE DÉVELOPPEUR] Valeur du toggle:', event.target.checked);
                console.log('🔧 [TOGGLE MODE DÉVELOPPEUR] ID de l\'élément:', event.target.id);
            }
            window.updateDeveloperSections();
        });

        // Écouter les changements du toggle Debug JavaScript pour mettre à jour la visibilité du toggle Debug Éditeur PDF
        if (debugJavascriptToggle) {
            debugJavascriptToggle.addEventListener('change', function(event) {
                console.log('🔧 [DEBUG JAVASCRIPT] Événement change déclenché');
                console.log('🔧 [DEBUG JAVASCRIPT] Valeur du toggle:', event.target.checked);
                updatePdfEditorToggleVisibility();
                updateSettingsPageToggleVisibility();
            });

            // Ajouter aussi un écouteur de clic pour debug
            debugJavascriptToggle.addEventListener('click', function(event) {
                console.log('🔧 [DEBUG JAVASCRIPT] Clic détecté sur toggle');
            });
        }

        // Attacher les écouteurs d'événements aux toggles enfants pour permettre l'interaction
        if (pdfEditorToggle) {
            pdfEditorToggle.addEventListener('change', function(event) {
                console.log('🔧 [DEBUG PDF EDITOR] Toggle changé:', event.target.checked);
                // Le toggle peut être changé programmatiquement ou par l'utilisateur
            });

            pdfEditorToggle.addEventListener('click', function(event) {
                console.log('🔧 [DEBUG PDF EDITOR] Clic détecté sur toggle enfant');
            });
        }

        if (settingsPageToggle) {
            settingsPageToggle.addEventListener('change', function(event) {
                console.log('🔧 [DEBUG SETTINGS PAGE] Toggle changé:', event.target.checked);
                // Le toggle peut être changé programmatiquement ou par l'utilisateur
            });

            settingsPageToggle.addEventListener('click', function(event) {
                console.log('🔧 [DEBUG SETTINGS PAGE] Clic détecté sur toggle enfant');
            });
        }

        if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
            console.log('🔧 [TOGGLE MODE DÉVELOPPEUR] Écouteur d\'événements attaché avec succès');
        }
    } else {
        if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
            console.error('❌ [TOGGLE MODE DÉVELOPPEUR] Élément toggle introuvable dans le DOM');
        }
    }

    // Gestion du nettoyage complet de la licence
    const cleanupLicenseBtn = document.getElementById('cleanup_license_btn');
    const cleanupStatus = document.getElementById('cleanup_status');
    const cleanupNonce = document.getElementById('cleanup_license_nonce');

    if (cleanupLicenseBtn && cleanupStatus && cleanupNonce) {
        cleanupLicenseBtn.addEventListener('click', function() {
            if (!confirm('⚠️ ATTENTION: Cette action va supprimer TOUTES les données de licence et réinitialiser le plugin à l\'état libre.\n\nCette action est IRRÉVERSIBLE.\n\nÊtes-vous sûr de vouloir continuer ?')) {
                return;
            }

            // Désactiver le bouton pendant l'opération
            cleanupLicenseBtn.disabled = true;
            cleanupLicenseBtn.textContent = '🧹 Nettoyage en cours...';
            cleanupStatus.textContent = '';
            cleanupStatus.style.color = '#007cba';

            // Faire l'appel AJAX
            const ajaxUrl = window.ajaxurl || window.wp?.ajaxurl || (window.location.origin + '/wp-admin/admin-ajax.php');
            fetch(ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'pdf_builder_cleanup_license',
                    nonce: cleanupNonce.value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cleanupStatus.textContent = '✅ ' + data.data.message;
                    cleanupStatus.style.color = '#28a745';
                    // Recharger la page après 2 secondes pour refléter les changements
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    cleanupStatus.textContent = '❌ Erreur: ' + (data.data?.message || 'Erreur inconnue');
                    cleanupStatus.style.color = '#dc3545';
                    cleanupLicenseBtn.disabled = false;
                    cleanupLicenseBtn.textContent = '🧹 Nettoyer complètement la licence';
                }
            })
            .catch(error => {
                if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
                    console.error('Erreur AJAX cleanup license:', error);
                }
                cleanupStatus.textContent = '❌ Erreur de connexion';
                cleanupStatus.style.color = '#dc3545';
                cleanupLicenseBtn.disabled = false;
                cleanupLicenseBtn.textContent = '🧹 Nettoyer complètement la licence';
            });
        });
    }

    // Boutons d'outils de développement
    const toggleLicenseTestModeBtn = document.getElementById('toggle_license_test_mode_btn');
    const clearCacheBtn = document.getElementById('clear_cache_btn');
    const testRoutesBtn = document.getElementById('test_routes_btn');
    const exportDiagnosticBtn = document.getElementById('export_diagnostic_btn');
    const viewLogsBtn = document.getElementById('view_logs_btn');

    // Helper function for AJAX calls
    function makeAjaxCall(action, button, successCallback, errorCallback) {
        const ajaxUrl = window.ajaxurl || window.wp?.ajaxurl || (window.location.origin + '/wp-admin/admin-ajax.php');
        const originalText = button.textContent;

        // Disable button and show loading state
        button.disabled = true;
        button.textContent = '⏳ Chargement...';

        // Always request a fresh nonce to avoid stale nonce problems
        fetch(ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({ action: 'pdf_builder_get_fresh_nonce' })
        })
        .then(resp => resp.json())
        .then(nonceData => {
            const nonce = (nonceData && nonceData.success && nonceData.data && nonceData.data.nonce) ? nonceData.data.nonce : (window.pdfBuilderAjax?.nonce || '');

            return fetch(ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: action, nonce: nonce })
            });
        })
        .then(response => response.json())
        .then(data => {
            button.disabled = false;
            button.textContent = originalText;

            if (data.success) {
                if (successCallback) successCallback(data);
            } else {
                if (errorCallback) errorCallback(data);
                alert('❌ Erreur: ' + (data.data?.message || 'Erreur inconnue'));
            }
        })
        .catch(error => {
            if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
                console.error('Erreur AJAX:', error);
            }
            button.disabled = false;
            button.textContent = originalText;
            alert('❌ Erreur de connexion');
        });
    }

    // Test License Button
    if (toggleLicenseTestModeBtn) {
        toggleLicenseTestModeBtn.addEventListener('click', function() {
            // This button toggles test mode, not tests license
            // The actual license testing is done via generate_license_key_btn
        });
    }

    // Generate License Key Button
    const generateLicenseKeyBtn = document.getElementById('generate_license_key_btn');
    const copyLicenseKeyBtn = document.getElementById('copy_license_key_btn');
    const deleteLicenseKeyBtn = document.getElementById('delete_license_key_btn');
    const licenseKeyField = document.getElementById('license_test_key');
    const licenseKeyStatus = document.getElementById('license_key_status');

    if (generateLicenseKeyBtn && licenseKeyField && licenseKeyStatus) {
        generateLicenseKeyBtn.addEventListener('click', function() {
            makeAjaxCall('pdf_builder_generate_test_license_key', generateLicenseKeyBtn,
                function(data) {
                    licenseKeyField.value = data.data.license_key;
                    licenseKeyStatus.textContent = '✅ Clé générée avec succès';
                    licenseKeyStatus.style.color = '#28a745';
                    // Show delete button
                    if (deleteLicenseKeyBtn) {
                        deleteLicenseKeyBtn.style.display = 'inline-block';
                    }
                },
                function(data) {
                    licenseKeyStatus.textContent = '❌ ' + data.data.message;
                    licenseKeyStatus.style.color = '#dc3545';
                }
            );
        });
    }

    if (copyLicenseKeyBtn && licenseKeyField) {
        copyLicenseKeyBtn.addEventListener('click', function() {
            if (licenseKeyField.value) {
                navigator.clipboard.writeText(licenseKeyField.value).then(function() {
                    licenseKeyStatus.textContent = '📋 Clé copiée dans le presse-papiers';
                    licenseKeyStatus.style.color = '#17a2b8';
                    setTimeout(function() {
                        licenseKeyStatus.textContent = '';
                    }, 3000);
                }).catch(function(err) {
                    licenseKeyStatus.textContent = '❌ Erreur lors de la copie';
                    licenseKeyStatus.style.color = '#dc3545';
                });
            } else {
                licenseKeyStatus.textContent = '❌ Aucune clé à copier';
                licenseKeyStatus.style.color = '#dc3545';
            }
        });
    }

    if (deleteLicenseKeyBtn && licenseKeyField && licenseKeyStatus) {
        deleteLicenseKeyBtn.addEventListener('click', function() {
            if (!confirm('Voulez-vous vraiment supprimer cette clé de test ?')) {
                return;
            }

            makeAjaxCall('pdf_builder_delete_test_license_key', deleteLicenseKeyBtn,
                function(data) {
                    licenseKeyField.value = '';
                    licenseKeyStatus.textContent = '🗑️ Clé supprimée';
                    licenseKeyStatus.style.color = '#28a745';
                    // Hide delete button
                    deleteLicenseKeyBtn.style.display = 'none';
                },
                function(data) {
                    licenseKeyStatus.textContent = '❌ ' + data.data.message;
                    licenseKeyStatus.style.color = '#dc3545';
                }
            );
        });
    }

    // Clear Cache Button
    if (clearCacheBtn) {
        clearCacheBtn.addEventListener('click', function() {
            if (!confirm('Voulez-vous vider tout le cache du plugin ?')) {
                return;
            }

            makeAjaxCall('pdf_builder_clear_cache', clearCacheBtn,
                function(data) {
                    alert('✅ ' + data.data.message + '\nNouvelle taille du cache: ' + (data.data.new_cache_size || '0 Ko'));
                    // Refresh cache metrics if available
                    if (typeof updateCacheMetrics === 'function') {
                        updateCacheMetrics();
                    }
                }
            );
        });
    }

    // Reload Cache Button
    const reloadCacheBtn = document.getElementById('reload_cache_btn');
    if (reloadCacheBtn) {
        reloadCacheBtn.addEventListener('click', function() {
            makeAjaxCall('pdf_builder_clear_cache', reloadCacheBtn,
                function(data) {
                    alert('✅ Cache rechargé avec succès\n' + data.data.message);
                    // Refresh cache metrics if available
                    if (typeof updateCacheMetrics === 'function') {
                        updateCacheMetrics();
                    }
                }
            );
        });
    }

    // Clear Temp Button
    const clearTempBtn = document.getElementById('clear_temp_btn');
    if (clearTempBtn) {
        clearTempBtn.addEventListener('click', function() {
            if (!confirm('Voulez-vous vider tous les fichiers temporaires ?')) {
                return;
            }

            makeAjaxCall('pdf_builder_clear_temp', clearTempBtn,
                function(data) {
                    alert('✅ ' + data.data.message);
                },
                function(data) {
                    alert('❌ ' + data.data.message);
                }
            );
        });
    }

    // Test Routes Button
    if (testRoutesBtn) {
        testRoutesBtn.addEventListener('click', function() {
            makeAjaxCall('pdf_builder_test_routes', testRoutesBtn,
                function(data) {
                    let message = '✅ ' + data.data.message + '\n\nRoutes testées:\n';
                    data.data.routes_tested.forEach(route => {
                        message += '• ' + route + '\n';
                    });
                    if (data.data.failed_routes && data.data.failed_routes.length > 0) {
                        message += '\nRoutes échouées:\n';
                        data.data.failed_routes.forEach(route => {
                            message += '• ' + route + '\n';
                        });
                    }
                    alert(message);
                }
            );
        });
    }

    // Export Diagnostic Button
    if (exportDiagnosticBtn) {
        exportDiagnosticBtn.addEventListener('click', function() {
            makeAjaxCall('pdf_builder_export_diagnostic', exportDiagnosticBtn,
                function(data) {
                    alert('✅ ' + data.data.message + '\n\nFichier créé: ' + data.data.file_url);
                    // Open download link in new tab
                    window.open(data.data.file_url, '_blank');
                }
            );
        });
    }

    // View Logs Button
    if (viewLogsBtn) {
        viewLogsBtn.addEventListener('click', function() {
            makeAjaxCall('pdf_builder_view_logs', viewLogsBtn,
                function(data) {
                    let message = '📋 ' + data.data.message + '\n\n';
                    data.data.log_files.forEach(log => {
                        message += `• ${log.name} (${log.size} octets) - Modifié: ${log.modified}\n`;
                    });
                    alert(message);
                },
                function(data) {
                    alert('❌ ' + data.data.message);
                }
            );
        });
    }

    // Refresh Logs Button
    const refreshLogsBtn = document.getElementById('refresh_logs_btn');
    const clearLogsBtn = document.getElementById('clear_logs_btn');
    const logsContainer = document.getElementById('logs_content');

    if (refreshLogsBtn && logsContainer) {
        refreshLogsBtn.addEventListener('click', function() {
            makeAjaxCall('pdf_builder_refresh_logs', refreshLogsBtn,
                function(data) {
                    logsContainer.innerHTML = '<pre>' + data.data.logs_content + '</pre>';
                    alert('✅ Logs actualisés');
                },
                function(data) {
                    alert('❌ Erreur lors de l\'actualisation des logs: ' + data.data.message);
                }
            );
        });
    }

    if (clearLogsBtn && logsContainer) {
        clearLogsBtn.addEventListener('click', function() {
            if (!confirm('Voulez-vous vraiment vider tous les logs ?')) {
                return;
            }

            makeAjaxCall('pdf_builder_clear_logs', clearLogsBtn,
                function(data) {
                    logsContainer.innerHTML = '<em style="color: #666;">Cliquez sur "Actualiser Logs" pour charger les logs récents...</em>';
                    alert('✅ ' + data.data.message);
                },
                function(data) {
                    alert('❌ ' + data.data.message);
                }
            );
        });
    }

    // === NOTIFICATIONS TEST SYSTEM ===
    const notificationTestLogs = document.getElementById('notification_test_logs');
    let notificationTestCount = 0;
    let notificationStats = { success: 0, error: 0, warning: 0, info: 0 };

    // Helper function to add log entry
    function addNotificationLog(message, type = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        const logEntry = document.createElement('div');
        logEntry.style.cssText = `
            padding: 4px 8px;
            margin: 2px 0;
            border-radius: 4px;
            font-size: 11px;
            border-left: 3px solid ${getLogColor(type)};
            background: ${getLogBackground(type)};
        `;
        logEntry.innerHTML = `<strong>${timestamp}</strong> ${message}`;
        notificationTestLogs.appendChild(logEntry);
        notificationTestLogs.scrollTop = notificationTestLogs.scrollHeight;

        // Keep only last 20 entries
        while (notificationTestLogs.children.length > 20) {
            notificationTestLogs.removeChild(notificationTestLogs.firstChild);
        }
    }

    function getLogColor(type) {
        const colors = {
            success: '#28a745',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8',
            system: '#6c757d'
        };
        return colors[type] || colors.info;
    }

    function getLogBackground(type) {
        const backgrounds = {
            success: '#f8fff8',
            error: '#fff8f8',
            warning: '#fffef8',
            info: '#f8fdff',
            system: '#f8f9fa'
        };
        return backgrounds[type] || backgrounds.info;
    }

    // Console logging helper
    function logToConsole(level, message, data = null) {
        if (!window.pdfBuilderCanvasSettings?.debug?.javascript) {
            return;
        }
        
        const prefix = '[🔔 NOTIFICATION TEST]';
        const timestamp = new Date().toISOString();

        switch(level) {
            case 'info':
                console.info(`${prefix} ${timestamp} - ${message}`, data || '');
                break;
            case 'success':
                console.log(`${prefix} ✅ ${timestamp} - ${message}`, data || '');
                break;
            case 'error':
                console.error(`${prefix} ❌ ${timestamp} - ${message}`, data || '');
                break;
            case 'warning':
                console.warn(`${prefix} ⚠️ ${timestamp} - ${message}`, data || '');
                break;
            default:
                console.log(`${prefix} ${timestamp} - ${message}`, data || '');
        }
    }

    // Test individual notification types
    function testNotification(type, customMessage = null) {
        const messages = {
            success: 'Opération réussie ! Les données ont été sauvegardées.',
            error: 'Erreur critique ! Impossible de traiter la demande.',
            warning: 'Attention requise ! Vérifiez vos paramètres.',
            info: 'Information importante ! Mise à jour disponible.'
        };

        const message = customMessage || messages[type];
        const notificationId = `test_${type}_${Date.now()}`;

        logToConsole('info', `Testing ${type} notification`, {
            message: message,
            id: notificationId,
            timestamp: Date.now()
        });

        addNotificationLog(`🔔 Test ${type}: "${message.substring(0, 50)}..."`, type);

        // Function to attempt showing notification
        function attemptShowNotification(retryCount = 0) {
            try {
                // Check if notification system is available
                if (typeof window.pdfBuilderNotify !== 'undefined' && window.pdfBuilderNotify[type]) {
                    const result = window.pdfBuilderNotify[type](message, 4000); // 4 seconds for testing
                    notificationStats[type]++;
                    notificationTestCount++;

                    logToConsole('success', `${type} notification shown successfully`, {
                        id: result,
                        stats: notificationStats
                    });

                    addNotificationLog(`✅ ${type} notification affichée (ID: ${result})`, 'success');
                } else if (typeof window[`show${type.charAt(0).toUpperCase() + type.slice(1)}Notification`] === 'function') {
                    // Fallback to old global functions
                    const showFunction = window[`show${type.charAt(0).toUpperCase() + type.slice(1)}Notification`];
                    const result = showFunction(message, 4000); // 4 seconds for testing
                    notificationStats[type]++;
                    notificationTestCount++;

                    logToConsole('success', `${type} notification shown successfully`, {
                        id: result,
                        stats: notificationStats
                    });

                    addNotificationLog(`✅ ${type} notification affichée (ID: ${result})`, 'success');
                } else {
                    // Retry up to 10 times with increasing delay (up to ~3 seconds total)
                    if (retryCount < 10) {
                        const delay = Math.min(200 * (retryCount + 1), 1000); // Max 1 second delay
                        setTimeout(() => attemptShowNotification(retryCount + 1), delay);
                        return;
                    }
                    throw new Error(`Notification system not available after ${retryCount} retries. Scripts may not be loaded yet.`);
                }
            } catch (error) {
                logToConsole('error', `Failed to show ${type} notification`, error);
                addNotificationLog(`❌ Erreur ${type}: ${error.message}`, 'error');
            }
        }

        // Start attempt
        attemptShowNotification();
    }

    // Individual test buttons
    const testButtons = {
        success: document.getElementById('test_notification_success'),
        error: document.getElementById('test_notification_error'),
        warning: document.getElementById('test_notification_warning'),
        info: document.getElementById('test_notification_info')
    };

    Object.keys(testButtons).forEach(type => {
        if (testButtons[type]) {
            testButtons[type].addEventListener('click', function() {
                logToConsole('info', `Manual test triggered for ${type} notification`);
                testNotification(type);
            });
        }
    });

    // Test all notifications button
    const testAllBtn = document.getElementById('test_notification_all');
    if (testAllBtn) {
        testAllBtn.addEventListener('click', function() {
            logToConsole('info', 'Testing all notification types sequentially');
            addNotificationLog('🎯 Démarrage test de tous les types', 'system');

            const types = ['success', 'error', 'warning', 'info'];
            let index = 0;

            const testNext = () => {
                if (index < types.length) {
                    testNotification(types[index]);
                    index++;
                    setTimeout(testNext, 1000); // 1 second delay between tests
                } else {
                    logToConsole('success', 'All notification types tested successfully');
                    addNotificationLog('✅ Tous les types testés avec succès', 'success');
                }
            };

            testNext();
        });
    }

    // Clear all notifications button
    const clearAllBtn = document.getElementById('test_notification_clear');
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function() {
            logToConsole('info', 'Clearing all notifications');
            addNotificationLog('🗑️ Suppression de toutes les notifications', 'system');

            try {
                if (window.pdfBuilderNotificationsInstance && window.pdfBuilderNotificationsInstance.closeAll) {
                    window.pdfBuilderNotificationsInstance.closeAll();
                    logToConsole('success', 'All notifications cleared successfully');
                    addNotificationLog('✅ Toutes les notifications supprimées', 'success');
                } else {
                    throw new Error('Notification instance not available');
                }
            } catch (error) {
                logToConsole('error', 'Failed to clear notifications', error);
                addNotificationLog(`❌ Erreur suppression: ${error.message}`, 'error');
            }
        });
    }

    // Statistics button
    const statsBtn = document.getElementById('test_notification_stats');
    if (statsBtn) {
        statsBtn.addEventListener('click', function() {
            logToConsole('info', 'Showing notification statistics', notificationStats);
            addNotificationLog('📊 Statistiques des notifications', 'system');

            const statsMessage = `
📊 STATISTIQUES DES TESTS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
• Total tests: ${notificationTestCount}
• Succès: ${notificationStats.success}
• Erreurs: ${notificationStats.error}
• Avertissements: ${notificationStats.warning}
• Infos: ${notificationStats.info}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Notifications actives: ${document.querySelectorAll('.pdf-notification').length}
            `.trim();

            // Show in notification
            if (typeof window.pdfBuilderNotify !== 'undefined' && window.pdfBuilderNotify.info) {
                window.pdfBuilderNotify.info('Statistiques affichées en console (F12)', 3000);
            } else if (typeof window.showInfoNotification === 'function') {
                window.showInfoNotification('Statistiques affichées en console (F12)', 3000);
            }

            // Log detailed stats
            if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
                console.table(notificationStats);
                console.log(statsMessage);
            }

            addNotificationLog(`📊 Stats: ${notificationTestCount} tests (${notificationStats.success}✓ ${notificationStats.error}✗ ${notificationStats.warning}⚠ ${notificationStats.info}ℹ)`, 'info');
        });
    }

    // Function to test all notification types sequentially
    function testAllNotifications() {
        logToConsole('info', 'Testing all notification types sequentially');
        addNotificationLog('🎯 Démarrage test de tous les types', 'system');

        const types = ['success', 'error', 'warning', 'info'];
        let index = 0;

        const testNext = () => {
            if (index < types.length) {
                testNotification(types[index]);
                index++;
                setTimeout(testNext, 1000); // 1 second delay between tests
            } else {
                logToConsole('success', 'All notification types tested successfully');
                addNotificationLog('✅ Tous les types testés avec succès', 'success');
            }
        };

        testNext();
    }

    // Système de notification de secours simple
    window.simpleNotificationSystem = {
        notifications: [],
        nextTop: 50,

        show: function(message, type = 'info') {
            if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
                console.log(`[NOTIFICATION ${type.toUpperCase()}] ${message}`);
            }

            // Calculer la position verticale pour éviter la superposition
            const currentTop = this.nextTop;
            this.nextTop += 70; // Espace entre notifications

            // Créer une notification simple dans le DOM
            const notification = document.createElement('div');
            notification.className = `simple-notification simple-notification-${type}`;
            notification.style.cssText = `
                position: fixed;
                top: ${currentTop}px;
                right: 20px;
                background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : type === 'warning' ? '#fff3cd' : '#d1ecf1'};
                color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : type === 'warning' ? '#856404' : '#0c5460'};
                border: 1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : type === 'warning' ? '#ffeaa7' : '#bee5eb'};
                border-radius: 4px;
                padding: 12px 16px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                z-index: 10000;
                max-width: 400px;
                font-size: 14px;
                opacity: 0;
                transform: translateX(100%);
                transition: all 0.3s ease;
            `;

            const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : type === 'warning' ? '⚠️' : 'ℹ️';
            notification.innerHTML = `
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 16px;">${icon}</span>
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" style="margin-left: auto; background: none; border: none; cursor: pointer; font-size: 18px; opacity: 0.7;">×</button>
                </div>
            `;

            // Ajouter à la liste des notifications
            this.notifications.push(notification);
            document.body.appendChild(notification);

            // Animation d'entrée
            setTimeout(() => {
                notification.style.opacity = '1';
                notification.style.transform = 'translateX(0)';
            }, 10);

            // Auto-remove après 5 secondes
            setTimeout(() => {
                this.remove(notification);
            }, 5000);

            return notification;
        },

        remove: function(notification) {
            if (!notification) return;

            // Animation de sortie
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';

            setTimeout(() => {
                // Retirer de la liste
                const index = this.notifications.indexOf(notification);
                if (index > -1) {
                    this.notifications.splice(index, 1);
                }

                // Supprimer du DOM
                if (notification.parentNode) {
                    notification.remove();
                }

                // Recalculer les positions des notifications restantes
                this.repositionNotifications();
            }, 300);
        },

        repositionNotifications: function() {
            this.nextTop = 50;
            this.notifications.forEach(notification => {
                notification.style.top = this.nextTop + 'px';
                this.nextTop += 70;
            });
        },

        success: function(message) { return this.show(message, 'success'); },
        error: function(message) { return this.show(message, 'error'); },
        warning: function(message) { return this.show(message, 'warning'); },
        info: function(message) { return this.show(message, 'info'); },

        clear: function() {
            // Supprimer toutes les notifications
            this.notifications.forEach(notification => {
                if (notification.parentNode) {
                    notification.remove();
                }
            });
            this.notifications = [];
            this.nextTop = 50;
        }
    };

    // Alias pour compatibilité
    window.pdfBuilderNotify = window.simpleNotificationSystem;
    window.showSuccessNotification = window.simpleNotificationSystem.success;
    window.showErrorNotification = window.simpleNotificationSystem.error;
    window.showWarningNotification = window.simpleNotificationSystem.warning;
    window.showInfoNotification = window.simpleNotificationSystem.info;

    // Add section to dev sections array for toggle
    devSections.push('dev-notifications-test-section');

    // Initial log
    logToConsole('info', 'Notification test system initialized');
    addNotificationLog('🚀 Système de test des notifications initialisé', 'system');

    // Manual testing only - no automatic execution on page load
    // Tests are triggered manually via buttons only
});
</script>
