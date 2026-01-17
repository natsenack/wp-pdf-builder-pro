<?php // Developer tab content - Updated: 2025-11-18 20:20:00

    // require_once __DIR__ . '/../settings-helpers.php'; // REMOVED - settings-helpers.php deleted

    // Récupération des paramètres depuis le tableau unifié
    $settings = get_option('pdf_builder_settings', []);
    error_log('[PDF Builder] settings-developpeur.php loaded - license_test_mode: ' . ($settings['pdf_builder_license_test_mode'] ?? 'not set') . ', settings count: ' . count($settings));

    // Variables nécessaires pour l'onglet développeur
    $license_test_mode = $settings['pdf_builder_license_test_mode'] ?? '0';
    $license_test_key = $settings['pdf_builder_license_test_key'] ?? '';
?>
            <h3 style="display: flex; justify-content: flex-start; align-items: center;">
                <span>👨‍💻 Paramètres Développeur</span>
            </h3>
            <p class="developer-warning">⚠️ Cette section est réservée aux développeurs. Les modifications ici peuvent affecter le fonctionnement du plugin.</p>

                <h3 class="section-title">🔐 Contrôle d'Accès</h3>

             <table class="form-table">
                <tr>
                    <th scope="row"><label for="developer_enabled">Mode Développeur</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="hidden" name="pdf_builder_settings[pdf_builder_developer_enabled]" value="0">
                                <input type="checkbox" id="developer_enabled" name="pdf_builder_settings[pdf_builder_developer_enabled]" value="1" <?php echo isset($settings['pdf_builder_developer_enabled']) && $settings['pdf_builder_developer_enabled'] && $settings['pdf_builder_developer_enabled'] !== '0' ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Activer le mode développeur</span>
                            <span class="developer-status-indicator <?php echo isset($settings['pdf_builder_developer_enabled']) && $settings['pdf_builder_developer_enabled'] && $settings['pdf_builder_developer_enabled'] !== '0' ? 'developer-status-active' : 'developer-status-inactive'; ?>">
                                <?php echo isset($settings['pdf_builder_developer_enabled']) && $settings['pdf_builder_developer_enabled'] && $settings['pdf_builder_developer_enabled'] !== '0' ? 'ACTIF' : 'INACTIF'; ?>
                            </span>
                        </div>
                        <div class="toggle-description">Active le mode développeur avec logs détaillés</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="developer_password">Mot de Passe Dev</label></th>
                    <td>
                        <!-- Champ username caché pour l'accessibilité -->
                        <input type="text" autocomplete="username" class="hidden-element" />
                        <div class="developer-field-group">
                            <input type="password" id="developer_password" name="pdf_builder_settings[pdf_builder_developer_password]"
                                   placeholder="Laisser vide pour aucun mot de passe" autocomplete="current-password"
                                   class="developer-input"
                                   value="<?php echo esc_attr($settings['pdf_builder_developer_password'] ?? ''); ?>" />
                            <button type="button" id="toggle_password" class="button button-secondary developer-button">
                                👁️ Afficher
                            </button>
                        </div>
                        <p class="description">Protège les outils développeur avec un mot de passe (optionnel)</p>
                        <?php if (!empty($settings['developer_password'])) :
                            ?>
                        <p class="description developer-password-set">✓ Mot de passe configuré et sauvegardé</p>
                            <?php
                        endif; ?>
                    </td>
                </tr>
             </table>

            <section id="dev-license-section" class="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'developer-section-hidden' : ''; ?>">
                <h3 class="section-title">🔐 Test de Licence</h3>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="license_test_mode">Mode Test Licence</label></th>
                        <td>
                            <div class="developer-field-group">
                                <button type="button" id="toggle_license_test_mode_btn" class="button button-secondary developer-button">
                                    🎚️ <?php echo $license_test_mode ? 'Désactiver' : 'Activer'; ?> Mode Test
                                </button>
                                <span id="license_test_mode_status" class="license-test-mode-status <?php echo $license_test_mode ? 'license-test-mode-active' : 'license-test-mode-inactive'; ?>">
                                    <?php echo $license_test_mode ? '✅ MODE TEST ACTIF' : '❌ Mode test inactif'; ?>
                                </span>
                            </div>
                            <p class="description">Basculer le mode test pour développer et tester sans serveur de licence en production</p>
                            <input type="hidden" name="pdf_builder_settings[pdf_builder_license_test_mode]" value="0">
                            <input type="checkbox" id="license_test_mode" name="pdf_builder_settings[pdf_builder_license_test_mode]" value="1" <?php checked($license_test_mode, '1'); ?> class="hidden-element" />
                            <input type="hidden" id="toggle_license_test_mode_nonce" value="<?php echo wp_create_nonce('pdf_builder_ajax'); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>Clé de Test</label></th>
                        <td>
                            <div class="developer-field-group">
                                <input type="text" id="license_test_key" readonly class="license-test-key-input" placeholder="Générer une clé..." value="<?php echo esc_attr($license_test_key); ?>" />
                                <button type="button" id="generate_license_key_btn" class="button button-secondary developer-button">
                                    🔑 Générer
                                </button>
                                <button type="button" id="copy_license_key_btn" class="button button-secondary developer-button">
                                    📋 Copier
                                </button>
                                <?php if ($license_test_key) :
                                    ?>
                                <button type="button" id="delete_license_key_btn" class="button button-link-delete developer-button">
                                    🗑️ Supprimer
                                </button>
                                    <?php
                                endif; ?>
                            </div>
                            <p class="description">Génère une clé de test aléatoire pour valider le système de licence</p>
                            <span id="license_key_status" style="margin-left: 0; margin-top: 10px; display: inline-block;"></span>
                            <input type="hidden" id="generate_license_key_nonce" value="<?php echo wp_create_nonce('pdf_builder_ajax'); ?>" />
                            <input type="hidden" id="delete_license_key_nonce" value="<?php echo wp_create_nonce('pdf_builder_ajax'); ?>" />
                            <input type="hidden" id="validate_license_key_nonce" value="<?php echo wp_create_nonce('pdf_builder_ajax'); ?>" />
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
                            <input type="hidden" id="cleanup_license_nonce" value="<?php echo wp_create_nonce('pdf_builder_ajax'); ?>" />
                        </td>
                    </tr>
                </table>
            </section>

            <section id="dev-debug-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">🔍 Paramètres de Debug</h3>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="debug_php_errors">Errors PHP</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_settings[pdf_builder_debug_php_errors]" value="0">
                                    <input type="checkbox" id="debug_php_errors" name="pdf_builder_settings[pdf_builder_debug_php_errors]" value="1" <?php echo isset($settings['pdf_builder_debug_php_errors']) && $settings['pdf_builder_debug_php_errors'] ? 'checked' : ''; ?> />
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
                                    <input type="hidden" name="pdf_builder_settings[pdf_builder_debug_javascript]" value="0">
                                    <input type="checkbox" id="debug_javascript" name="pdf_builder_settings[pdf_builder_debug_javascript]" value="1" <?php echo isset($settings['pdf_builder_debug_javascript']) && $settings['pdf_builder_debug_javascript'] ? 'checked' : ''; ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Debug JS</span>
                            </div>
                            <div class="toggle-description">Active les logs détaillés en console (emojis: 🚀 start, ✅ success, ❌ error, ⚠️ warn)</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="debug_javascript_verbose">Logs Verbeux JS</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_settings[pdf_builder_debug_javascript_verbose]" value="0">
                                    <input type="checkbox" id="debug_javascript_verbose" name="pdf_builder_settings[pdf_builder_debug_javascript_verbose]" value="1" <?php echo isset($settings['pdf_builder_debug_javascript_verbose']) && $settings['pdf_builder_debug_javascript_verbose'] ? 'checked' : ''; ?> />
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
                                    <input type="hidden" name="pdf_builder_settings[pdf_builder_debug_ajax]" value="0">
                                    <input type="checkbox" id="debug_ajax" name="pdf_builder_settings[pdf_builder_debug_ajax]" value="1" <?php echo isset($settings['pdf_builder_debug_ajax']) && $settings['pdf_builder_debug_ajax'] ? 'checked' : ''; ?> />
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
                                    <input type="hidden" name="pdf_builder_settings[pdf_builder_debug_performance]" value="0">
                                    <input type="checkbox" id="debug_performance" name="pdf_builder_settings[pdf_builder_debug_performance]" value="1" <?php echo isset($settings['pdf_builder_debug_performance']) && $settings['pdf_builder_debug_performance'] ? 'checked' : ''; ?> />
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
                                    <input type="hidden" name="pdf_builder_settings[pdf_builder_debug_database]" value="0">
                                    <input type="checkbox" id="debug_database" name="pdf_builder_settings[pdf_builder_debug_database]" value="1" <?php echo isset($settings['pdf_builder_debug_database']) && $settings['pdf_builder_debug_database'] ? 'checked' : ''; ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Debug DB</span>
                            </div>
                            <div class="toggle-description">Enregistre les requêtes SQL exécutées par le plugin</div>
                        </td>
                    </tr>
                </table>
            </section>

            <section id="dev-logs-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">Fichiers Logs</h3>

                <table class="form-table">
                    <tr>
                     <th scope="row"><label for="log_level">Niveau de Log</label></th>
                        <td>
                            <select id="log_level" name="pdf_builder_settings[pdf_builder_log_level]" style="width: 200px;">
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
                            <input type="number" id="log_file_size" name="pdf_builder_settings[pdf_builder_log_file_size]" value="<?php echo isset($settings['pdf_builder_log_file_size']) ? intval($settings['pdf_builder_log_file_size']) : '10'; ?>" min="1" max="100" /> MB
                            <p class="description">Rotation automatique quand le log dépasse cette taille</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="log_retention">Retention Logs</label></th>
                        <td>
                            <input type="number" id="log_retention" name="pdf_builder_settings[pdf_builder_log_retention]" value="<?php echo isset($settings['pdf_builder_log_retention']) ? intval($settings['pdf_builder_log_retention']) : '30'; ?>" min="1" max="365" /> jours
                            <p class="description">Supprime automatiquement les logs plus vieux que ce délai</p>
                        </td>
                    </tr>
                </table>
            </section>

            <section id="dev-optimizations-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">Optimisations Avancées</h3>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="force_https">Forcer HTTPS API</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_settings[pdf_builder_force_https]" value="0">
                                    <input type="checkbox" id="force_https" name="pdf_builder_settings[pdf_builder_force_https]" value="1" <?php echo isset($settings['pdf_builder_force_https']) && $settings['pdf_builder_force_https'] ? 'checked' : ''; ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">HTTPS forcé</span>
                            </div>
                            <div class="toggle-description">Force les appels API externes en HTTPS (sécurité renforcée)</div>
                        </td>
                    </tr>
                </table>
            </section>

            <section id="dev-logs-viewer-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">Visualiseur de Logs Temps Réel</h3>

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
            </section>

            <section id="dev-tools-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">Outils de Développement</h3>

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
            </section>

            <section id="dev-notifications-test-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
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
            </section>

            <section id="dev-shortcuts-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">Raccourcis Clavier Développeur</h3>

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
            </section>

            <section id="dev-todo-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
             <!-- Accordéon pour la section À Faire - Développement -->
                <div class="accordion-container" style="margin-bottom: 20px;">
                    <button type="button" class="accordion-toggle" id="dev-todo-toggle" style="width: 100%; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; text-align: left; cursor: pointer; font-size: 16px; font-weight: bold; color: #495057; transition: all 0.3s ease;">
                        <span style="display: inline-flex; align-items: center; gap: 10px;">
                            📋 À Faire - Développement
                            <span class="accordion-icon" style="margin-left: auto; transition: transform 0.3s ease;">▶️</span>
                        </span>
                    </button>
                    <div class="accordion-content" id="dev-todo-content" style="display: none; border: 1px solid #dee2e6; border-top: none; border-radius: 0 0 8px 8px; padding: 20px; background: white;">
                    </div>
                    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                        <h4 style="color: #856404; margin-top: 0;">🚧 Système de Cache - RÉIMPLÉMENTATION REQUISE</h4>
                        <p style="margin-bottom: 15px;"><strong>Statut :</strong> <span style="color: #dc3545; font-weight: bold;">SUPPRIMÉ DU CODE ACTUEL</span></p>

                        <div style="background: #f8f9fa; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #856404;">📂 Fichiers concernés :</h5>
                            <ul style="margin-bottom: 0;">
                                <li><code>src/Cache/</code> - Répertoire complet du système de cache</li>
                                <li><code>templates/admin/settings-page.php</code> - Section système (lignes ~2133, ~276, ~349)</li>
                                <li><code>pdf-builder-pro.php</code> - Référence ligne 671</li>
                            </ul>
                        </div>

                        <div style="background: #f8f9fa; border-left: 4px solid #17a2b8; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #17a2b8;">🎯 Actions requises :</h5>
                            <ol style="margin-bottom: 0;">
                                <li><strong>Analyser les besoins :</strong> Déterminer si un système de cache est nécessaire pour les performances</li>
                                <li><strong>Concevoir l'architecture :</strong> Cache fichier/DB/transient selon les besoins</li>
                                <li><strong>Réimplémenter le Cache Manager :</strong> Classe principale de gestion du cache</li>
                                <li><strong>Réimplémenter l'Extended Cache Manager :</strong> Gestion avancée avec DB et nettoyage</li>
                                <li><strong>Mettre à jour l'interface :</strong> Section système avec contrôles fonctionnels</li>
                                <li><strong>Tester l'intégration :</strong> Vérifier que le cache améliore les performances sans bugs</li>
                            </ol>
                        </div>

                        <div style="background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #0c5460;">⚠️ Impact actuel :</h5>
                            <ul style="margin-bottom: 0;">
                                <li>Les toggles de cache dans l'onglet Système ne font rien</li>
                                <li>Pas de cache des aperçus PDF (impact performance)</li>
                                <li>Options de cache sauvegardées mais non utilisées</li>
                                <li>Code de cache présent mais non chargé</li>
                            </ul>
                        </div>

                        <p style="margin-top: 15px;"><strong>Priorité :</strong> <span style="color: #ffc107; font-weight: bold;">MOYENNE</span> - Fonctionnalité non critique pour le moment</p>
                    </div>

                    <div style="background: #e8f5e8; border: 1px solid #4caf50; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                        <h4 style="color: #2e7d32; margin-top: 0;">📤 Carte "Export & Qualité" - EN ATTENTE</h4>
                        <p style="margin-bottom: 15px;"><strong>Statut :</strong> <span style="color: #ff9800; font-weight: bold;">EN ATTENTE - SYSTÈME D'APERÇU</span></p>

                        <div style="background: #f1f8e9; border-left: 4px solid #4caf50; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #2e7d32;">📋 Contexte :</h5>
                            <p style="margin-bottom: 0;">Cette carte devra être créée dans les paramètres canvas une fois que le système d'aperçu PDF sera complètement implémenté et fonctionnel.</p>
                        </div>

                        <div style="background: #f8f9fa; border-left: 4px solid #2196f3; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #1976d2;">🎯 Fonctionnalités à implémenter :</h5>
                            <ul style="margin-bottom: 0;">
                                <li><strong>Formats d'export :</strong> PDF, PNG, JPG avec aperçu des formats disponibles</li>
                                <li><strong>Contrôle qualité :</strong> Slider/barre de qualité d'image (1-100%)</li>
                                <li><strong>Options de compression :</strong> Toggle pour compression intelligente</li>
                                <li><strong>Métadonnées :</strong> Option pour inclure/exclure les métadonnées</li>
                                <li><strong>Prévisualisation :</strong> Aperçu miniature du résultat d'export</li>
                                <li><strong>Taille estimée :</strong> Calcul automatique de la taille du fichier</li>
                            </ul>
                        </div>

                        <div style="background: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #e65100;">⏳ Condition préalable :</h5>
                            <p style="margin-bottom: 0;"><strong>Système d'aperçu PDF opérationnel requis avant de pouvoir créer cette carte.</strong> La carte aura besoin de pouvoir générer des aperçus des exports pour montrer à l'utilisateur le résultat avant l'export réel.</p>
                        </div>

                        <p style="margin-top: 15px;"><strong>Priorité :</strong> <span style="color: #ff9800; font-weight: bold;">ÉLEVÉE</span> - Fonctionnalité importante pour l'expérience utilisateur</p>
                        <p style="margin-top: 5px;"><strong>Dépend de :</strong> <span style="color: #2196f3; font-weight: bold;">Système d'aperçu PDF</span></p>
                    </div>

                    <div style="background: #e3f2fd; border: 1px solid #2196f3; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                        <h4 style="color: #0d47a1; margin-top: 0;">🔧 Corrections Mineures v1.1.0</h4>
                        <p style="margin-bottom: 15px;"><strong>Statut :</strong> <span style="color: #ff9800; font-weight: bold;">EN ATTENTE - FINALISATION</span></p>

                        <div style="background: #f8f9fa; border-left: 4px solid #2196f3; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #0d47a1;">📋 Corrections identifiées :</h5>
                            <ul style="margin-bottom: 0;">
                                <li><strong>Système d'Aperçu PDF/PNG/JPG :</strong> Implémentation complète du système d'aperçu multi-format</li>
                                <li><strong>Activation Onboarding Production :</strong> Vérifier que l'onboarding s'active en production (WP_DEBUG=false)</li>
                                <li><strong>Nettoyage Styles Temporaires :</strong> Supprimer/déplacer les styles inline temporaires vers debug.css</li>
                                <li><strong>Tests d'Intégration Complets :</strong> Validation Canvas/Metabox avec données réelles</li>
                                <li><strong>Tests Performance & Charge :</strong> Validation < 2s génération, cache hit ratio > 80%</li>
                                <li><strong>Tests Sécurité & Robustesse :</strong> Audit complet et gestion d'erreurs</li>
                                <li><strong>Tests Utilisateur & UX :</strong> Validation expérience utilisateur finale</li>
                                <li><strong>Tests Compatibilité Navigateurs :</strong> Chrome, Firefox, Safari, Edge</li>
                            </ul>
                        </div>

                        <div style="background: #f1f8e9; border-left: 4px solid #4caf50; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #2e7d32;">🎯 Actions requises :</h5>
                            <ol style="margin-bottom: 0;">
                                <li><strong>Implémenter système d'aperçu :</strong> PDF/PNG/JPG avec conversion côté serveur</li>
                                <li><strong>Vérifier l'onboarding :</strong> Tester activation en mode production</li>
                                <li><strong>Audit CSS :</strong> Identifier et nettoyer les styles temporaires</li>
                                <li><strong>Tests d'intégration :</strong> Validation transitions Canvas ↔ Metabox</li>
                                <li><strong>Tests performance :</strong> Mesure temps génération et cache efficiency</li>
                                <li><strong>Tests sécurité :</strong> Audit permissions, sanitisation, rate limiting</li>
                                <li><strong>Tests UX :</strong> Workflows intuitifs, gestion erreurs user-friendly</li>
                                <li><strong>Tests compatibilité :</strong> Validation cross-browser et responsive</li>
                            </ol>
                        </div>

                        <div style="background: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #e65100;">⚠️ Impact sur la release :</h5>
                            <p style="margin-bottom: 0;">Ces corrections sont critiques pour atteindre la version 1.1.0 stable. Le système d'aperçu PDF/PNG/JPG est essentiel pour l'expérience utilisateur, permettant aux clients de prévisualiser leurs documents avant génération finale.</p>
                        </div>

                        <p style="margin-top: 15px;"><strong>Priorité :</strong> <span style="color: #dc3545; font-weight: bold;">CRITIQUE</span> - Bloque la release v1.1.0</p>
                        <p style="margin-top: 5px;"><strong>Échéance :</strong> <span style="color: #dc3545; font-weight: bold;">Janvier 2026</span></p>
                    </div>

                    <div style="background: #e3f2fd; border: 1px solid #2196f3; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                        <h4 style="color: #0d47a1; margin-top: 0;">🖼️ Menu "Galerie" - À CACHER EN PRODUCTION</h4>
                        <p style="margin-bottom: 15px;"><strong>Statut :</strong> <span style="color: #2196f3; font-weight: bold;">NOTE POUR RELEASE FINALE</span></p>

                        <div style="background: #f8f9fa; border-left: 4px solid #2196f3; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #0d47a1;">📍 Localisation :</h5>
                            <ul style="margin-bottom: 0;">
                                <li><strong>Fichier :</strong> <code>templates/admin/predefined-templates-manager.php</code></li>
                                <li><strong>Ligne :</strong> 46 - Fonction <code>add_submenu_page()</code></li>
                                <li><strong>Slug :</strong> <code>pdf-builder-predefined-templates</code></li>
                                <li><strong>Label :</strong> <code>🖼️ Galerie</code></li>
                            </ul>
                        </div>

                        <div style="background: #f8f9fa; border-left: 4px solid #4caf50; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #2e7d32;">🎯 Action requise :</h5>
                            <p style="margin-bottom: 0;"><strong>Cacher le menu "Galerie" du menu admin WordPress</strong> car il est exclusivement destiné au développeur pour gérer les modèles prédéfinis du système.</p>
                        </div>

                        <div style="background: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #e65100;">💡 Solution proposée :</h5>
                            <ul style="margin-bottom: 0;">
                                <li>Ajouter une condition <code>if (WP_DEBUG)</code> autour de l'appel <code>add_submenu_page()</code></li>
                                <li>Ou utiliser un filtre/capability personnalisé pour les développeurs uniquement</li>
                                <li>Ou commenter/supprimer complètement la ligne</li>
                            </ul>
                        </div>

                        <p style="margin-top: 15px;"><strong>Priorité :</strong> <span style="color: #ff9800; font-weight: bold;">FAIBLE</span> - Amélioration UX pour utilisateurs finaux</p>
                        <p style="margin-top: 5px;"><strong>Action :</strong> <span style="color: #2196f3; font-weight: bold;">À FAIRE AVANT RELEASE FINALE</span></p>
                    </div>

                </div>
            </section>

            <section id="dev-console-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="section-title">Console Code</h3>

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
            </section>

            <section id="dev-hooks-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <!-- Tableau de références des hooks disponibles -->
                <h3 class="section-title">Hooks Disponibles</h3>

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
            </section>

            <!-- Section Monitoring des Performances -->
            <h3 class="section-title">📊 Monitoring des Performances</h3>
            <p style="color: #666; margin-bottom: 15px;">Outils pour mesurer et analyser les performances du système.</p>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="performance_monitoring">Monitoring Performance</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="hidden" name="pdf_builder_settings[pdf_builder_performance_monitoring]" value="0">
                                <input type="checkbox" id="performance_monitoring" name="pdf_builder_settings[pdf_builder_performance_monitoring]" value="1" <?php echo isset($settings['pdf_builder_performance_monitoring']) && $settings['pdf_builder_performance_monitoring'] ? 'checked' : ''; ?> />
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

            <!-- Avertissement production -->
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

            <!-- Bouton de sauvegarde spécifique à l'onglet développeur -->
            <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px;">
                <h3 style="margin-top: 0; color: #495057;">💾 Sauvegarde des Paramètres Développeur</h3>
                <p style="margin-bottom: 15px; color: #666;">Sauvegardez uniquement les paramètres de cette section développeur.</p>
                <button type="submit" class="button button-primary" style="background: #007cba; border-color: #007cba; color: white; padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 6px; cursor: pointer;">
                    💾 Sauvegarder les Paramètres Développeur
                </button>
            </div>


<!-- JavaScript déplacé vers settings-main.php pour éviter les conflits -->

<script type="text/javascript">
(function($) {
    'use strict';

    $(document).ready(function() {
        // Gestionnaire pour le bouton de génération de clé de test
        $('#generate_license_key_btn').on('click', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const $input = $('#license_test_key');
            const $status = $('#license_key_status');
            const nonce = $('#generate_license_key_nonce').val();

            // Désactiver le bouton pendant la génération
            $btn.prop('disabled', true).text('🔄 Génération...');
            $status.html('<span style="color: #007cba;">Génération en cours...</span>');

            // Requête AJAX
            $.ajax({
                url: pdf_builder_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_generate_test_license_key',
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        $input.val(response.data.license_key);
                        $status.html('<span style="color: #28a745;">✅ Clé générée avec succès ! Expire le ' + response.data.expires + '</span>');
                        // Activer le bouton copier
                        $('#copy_license_key_btn').prop('disabled', false);
                        $('#delete_license_key_btn').show();
                    } else {
                        $status.html('<span style="color: #dc3545;">❌ Erreur: ' + (response.data.message || 'Erreur inconnue') + '</span>');
                    }
                },
                error: function(xhr, status, error) {
                    $status.html('<span style="color: #dc3545;">❌ Erreur AJAX: ' + error + '</span>');
                },
                complete: function() {
                    // Réactiver le bouton
                    $btn.prop('disabled', false).text('🔑 Générer');
                }
            });
        });

        // Gestionnaire pour le bouton copier
        $('#copy_license_key_btn').on('click', function(e) {
            e.preventDefault();
            const $input = $('#license_test_key');
            const key = $input.val();

            if (key) {
                navigator.clipboard.writeText(key).then(function() {
                    $('#license_key_status').html('<span style="color: #28a745;">✅ Clé copiée dans le presse-papiers !</span>');
                    setTimeout(function() {
                        $('#license_key_status').html('');
                    }, 3000);
                }).catch(function(err) {
                    $('#license_key_status').html('<span style="color: #dc3545;">❌ Erreur lors de la copie</span>');
                });
            }
        });

        // Gestionnaire pour le bouton supprimer
        $('#delete_license_key_btn').on('click', function(e) {
            e.preventDefault();
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette clé de test ?')) {
                return;
            }

            const $btn = $(this);
            const $input = $('#license_test_key');
            const $status = $('#license_key_status');
            const nonce = $('#generate_license_key_nonce').val();

            $btn.prop('disabled', true).text('🗑️ Suppression...');

            $.ajax({
                url: pdf_builder_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_delete_test_license_key',
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        $input.val('');
                        $status.html('<span style="color: #28a745;">✅ Clé supprimée avec succès !</span>');
                        $('#copy_license_key_btn').prop('disabled', true);
                        $btn.hide();
                    } else {
                        $status.html('<span style="color: #dc3545;">❌ Erreur: ' + (response.data.message || 'Erreur inconnue') + '</span>');
                    }
                },
                error: function(xhr, status, error) {
                    $status.html('<span style="color: #dc3545;">❌ Erreur AJAX: ' + error + '</span>');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('🗑️ Supprimer');
                }
            });
        });

        // Gestionnaire pour le bouton de nettoyage complet de la licence
        $('#cleanup_license_btn').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm('⚠️ ATTENTION: Cette action va supprimer TOUS les paramètres de licence et réinitialiser complètement le plugin à l\'état libre.\n\nCette action est IRRÉVERSIBLE.\n\nÊtes-vous absolument sûr de vouloir continuer ?')) {
                return;
            }
            
            const $btn = $(this);
            const $status = $('#cleanup_status');
            const nonce = $('#cleanup_license_nonce').val();

            // Désactiver le bouton pendant le nettoyage
            $btn.prop('disabled', true).text('🧹 Nettoyage en cours...');
            $status.html('<span style="color: #007cba;">Nettoyage complet en cours...</span>');

            // Requête AJAX
            $.ajax({
                url: pdf_builder_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_ajax_handler',
                    action_type: 'cleanup_license',
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        $status.html('<span style="color: #28a745;">✅ Nettoyage complet réussi ! Le plugin a été réinitialisé à l\'état libre.</span>');
                        $btn.hide();
                        // Recharger la page après 2 secondes pour voir les changements
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        $status.html('<span style="color: #dc3545;">❌ Erreur: ' + (response.data.message || 'Erreur inconnue') + '</span>');
                    }
                },
                error: function(xhr, status, error) {
                    $status.html('<span style="color: #dc3545;">❌ Erreur AJAX: ' + error + '</span>');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('🧹 Nettoyer complètement la licence');
                }
            });
        });

        // Gestionnaire pour le bouton toggle du mode test
        $('#toggle_license_test_mode_btn').on('click', function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const $status = $('#license_test_mode_status');
            const nonce = $('#toggle_license_test_mode_nonce').val();

            // Désactiver le bouton pendant l'opération
            $btn.prop('disabled', true).text('🔄 Basculement...');
            $status.html('<span style="color: #007cba;">Basculement en cours...</span>');

            // Requête AJAX
            $.ajax({
                url: pdf_builder_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_ajax_handler',
                    action_type: 'toggle_license_test_mode',
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        const newMode = response.data.new_mode;
                        const isActive = newMode === '1';
                        $status.html('<span class="' + (isActive ? 'license-test-mode-active' : 'license-test-mode-inactive') + '">' + (isActive ? '✅ MODE TEST ACTIF' : '❌ Mode test inactif') + '</span>');
                        $btn.text(isActive ? '🎚️ Désactiver Mode Test' : '🎚️ Activer Mode Test');
                        
                        // Mettre à jour le checkbox caché
                        $('#license_test_mode').prop('checked', isActive);
                        
                        // Recharger la page après 1 seconde pour voir les changements dans l'onglet licence
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        $status.html('<span style="color: #dc3545;">❌ Erreur: ' + (response.data.message || 'Erreur inconnue') + '</span>');
                    }
                },
                error: function(xhr, status, error) {
                    $status.html('<span style="color: #dc3545;">❌ Erreur AJAX: ' + error + '</span>');
                },
                complete: function() {
                    const currentMode = $('#license_test_mode').is(':checked') ? '1' : '0';
                    const isActive = currentMode === '1';
                    $btn.prop('disabled', false).text(isActive ? '🎚️ Désactiver Mode Test' : '🎚️ Activer Mode Test');
                }
            });
        });

        // Gestionnaire pour le bouton de génération de clé de test
        $('#generate_license_key_btn').on('click', function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const $status = $('#license_key_status');
            const nonce = $('#generate_license_key_nonce').val();

            // Désactiver le bouton pendant la génération
            $btn.prop('disabled', true).text('🔄 Génération...');
            $status.html('<span style="color: #007cba;">Génération de la clé en cours...</span>');

            // Requête AJAX
            $.ajax({
                url: pdf_builder_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_ajax_handler',
                    action_type: 'generate_license_key',
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#license_test_key').val(response.data.test_key);
                        $status.html('<span style="color: #28a745;">✅ Clé générée: ' + response.data.test_key + '</span>');
                        $('#delete_license_key_btn').show();
                    } else {
                        $status.html('<span style="color: #dc3545;">❌ Erreur: ' + (response.data.message || 'Erreur inconnue') + '</span>');
                    }
                },
                error: function(xhr, status, error) {
                    $status.html('<span style="color: #dc3545;">❌ Erreur AJAX: ' + error + '</span>');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('🔑 Générer');
                }
            });
        });

        // Gestionnaire pour le bouton de suppression de clé de test
        $('#delete_license_key_btn').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm('Êtes-vous sûr de vouloir supprimer la clé de test ?')) {
                return;
            }
            
            const $btn = $(this);
            const $status = $('#license_key_status');
            const nonce = $('#delete_license_key_nonce').val();

            // Désactiver le bouton pendant la suppression
            $btn.prop('disabled', true).text('🗑️ Suppression...');
            $status.html('<span style="color: #007cba;">Suppression en cours...</span>');

            // Requête AJAX
            $.ajax({
                url: pdf_builder_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_ajax_handler',
                    action_type: 'delete_license_key',
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#license_test_key').val('');
                        $status.html('<span style="color: #28a745;">✅ Clé supprimée avec succès</span>');
                        $btn.hide();
                    } else {
                        $status.html('<span style="color: #dc3545;">❌ Erreur: ' + (response.data.message || 'Erreur inconnue') + '</span>');
                    }
                },
                error: function(xhr, status, error) {
                    $status.html('<span style="color: #dc3545;">❌ Erreur AJAX: ' + error + '</span>');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('🗑️ Supprimer');
                }
            });
        });

        // Gestionnaire pour le bouton de validation de clé de test
        $('#validate_license_key_btn').on('click', function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const $status = $('#license_key_status');
            const nonce = $('#validate_license_key_nonce').val();

            // Désactiver le bouton pendant la validation
            $btn.prop('disabled', true).text('✅ Validation...');
            $status.html('<span style="color: #007cba;">Validation en cours...</span>');

            // Requête AJAX
            $.ajax({
                url: pdf_builder_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_ajax_handler',
                    action_type: 'validate_license_key',
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success && response.data.valid) {
                        $status.html('<span style="color: #28a745;">✅ Clé validée avec succès</span>');
                    } else {
                        $status.html('<span style="color: #dc3545;">❌ Clé invalide</span>');
                    }
                },
                error: function(xhr, status, error) {
                    $status.html('<span style="color: #dc3545;">❌ Erreur AJAX: ' + error + '</span>');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('✅ Valider');
                }
            });
        });
    });
})(jQuery);
</script>


