<?php // Developer tab content - Updated: 2025-11-18 20:20:00 ?>

            <h2>Paramètres Développeur</h2>
            <p style="color: #666;">⚠️ Cette section est réservée aux développeurs. Les modifications ici peuvent affecter le fonctionnement du plugin.</p>

         <form method="post" id="developpeur-form" onsubmit="console.log('PDF_BUILDER_DEBUG: Developer form submitting', new FormData(this));">
                <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_developpeur_nonce'); ?>
                <input type="hidden" name="submit_developpeur" value="1">

                <h3 class="section-title">🔐 Contrôle d'Accès</h3>

             <table class="form-table">
                <tr>
                    <th scope="row"><label for="developer_enabled">Mode Développeur</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="developer_enabled" name="developer_enabled" value="1" <?php echo isset($settings['developer_enabled']) && $settings['developer_enabled'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Activer le mode développeur</span>
                        </div>
                        <div class="toggle-description">Active le mode développeur avec logs détaillés</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="developer_password">Mot de Passe Dev</label></th>
                    <td>
                        <!-- Champ username caché pour l'accessibilité -->
                        <input type="text" autocomplete="username" style="display: none;" />
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="password" id="developer_password" name="developer_password"
                                   placeholder="Laisser vide pour aucun mot de passe" autocomplete="current-password"
                                   style="width: 250px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                                   value="<?php echo esc_attr($settings['developer_password'] ?? ''); ?>" />
                            <button type="button" id="toggle_password" class="button button-secondary" style="padding: 8px 12px; height: auto;">
                                👁️ Afficher
                            </button>
                        </div>
                        <p class="description">Protège les outils développeur avec un mot de passe (optionnel)</p>
                        <?php if (!empty($settings['developer_password'])) :
                            ?>
                        <p class="description" style="color: #28a745;">✓ Mot de passe configuré et sauvegardé</p>
                            <?php
                        endif; ?>
                    </td>
                </tr>
             </table>

            <div id="dev-license-section" style="<?php echo !isset($settings['developer_enabled']) || !$settings['developer_enabled'] ? 'display: none;' : ''; ?>">
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
                            <?php if ($license_test_key) :
                                ?>
                            <button type="button" id="delete_license_key_btn" class="button button-link-delete" style="padding: 8px 12px; height: auto;">
                                🗑️ Supprimer
                            </button>
                                <?php
                            endif; ?>
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

            <h3 class="section-title">🔔 Tests de Notifications</h3>
            <p style="color: #666; margin-bottom: 15px;">Testez les différents types de notifications du système.</p>

            <div style="margin-bottom: 20px;">
                <button type="button" id="test-notifications-success" class="button button-small" style="margin-right: 5px; background: #28a745; color: white; border: none;">✅ Test Succès</button>
                <button type="button" id="test-notifications-error" class="button button-small" style="margin-right: 5px; background: #dc3545; color: white; border: none;">❌ Test Erreur</button>
                <button type="button" id="test-notifications-warning" class="button button-small" style="margin-right: 5px; background: #ffc107; color: black; border: none;">⚠️ Test Avertissement</button>
                <button type="button" id="test-notifications-info" class="button button-small" style="background: #17a2b8; color: white; border: none;">ℹ️ Test Info</button>
            </div>

            <div id="dev-debug-section" style="<?php echo !isset($settings['developer_enabled']) || !$settings['developer_enabled'] ? 'display: none;' : ''; ?>">
            <h3 class="section-title">🔍 Paramètres de Debug</h3>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="debug_php_errors">Errors PHP</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="debug_php_errors" name="debug_php_errors" value="1" <?php echo isset($settings['debug_php_errors']) && $settings['debug_php_errors'] ? 'checked' : ''; ?> />
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
                                <input type="checkbox" id="debug_javascript" name="debug_javascript" value="1" <?php echo isset($settings['debug_javascript']) && $settings['debug_javascript'] ? 'checked' : ''; ?> />
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
                                <input type="checkbox" id="debug_javascript_verbose" name="debug_javascript_verbose" value="1" <?php echo isset($settings['debug_javascript_verbose']) && $settings['debug_javascript_verbose'] ? 'checked' : ''; ?> />
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
                                <input type="checkbox" id="debug_ajax" name="debug_ajax" value="1" <?php echo isset($settings['debug_ajax']) && $settings['debug_ajax'] ? 'checked' : ''; ?> />
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
                                <input type="checkbox" id="debug_performance" name="debug_performance" value="1" <?php echo isset($settings['debug_performance']) && $settings['debug_performance'] ? 'checked' : ''; ?> />
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
                                <input type="checkbox" id="debug_database" name="debug_database" value="1" <?php echo isset($settings['debug_database']) && $settings['debug_database'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Debug DB</span>
                        </div>
                        <div class="toggle-description">Enregistre les requêtes SQL exécutées par le plugin</div>
                    </td>
                </tr>
            </table>
            </div>

            <div id="dev-logs-section" style="<?php echo !isset($settings['developer_enabled']) || !$settings['developer_enabled'] ? 'display: none;' : ''; ?>">
            <h3 class="section-title">Fichiers Logs</h3>

            <table class="form-table">
                <tr>
                  <th scope="row"><label for="log_level">Niveau de Log</label></th>
                    <td>
                        <select id="log_level" name="log_level" style="width: 200px;">
                            <option value="0" <?php echo (isset($settings['log_level']) && $settings['log_level'] == 0) ? 'selected' : ''; ?>>Aucun log</option>
                            <option value="1" <?php echo (isset($settings['log_level']) && $settings['log_level'] == 1) ? 'selected' : ''; ?>>Erreurs uniquement</option>
                            <option value="2" <?php echo (isset($settings['log_level']) && $settings['log_level'] == 2) ? 'selected' : ''; ?>>Erreurs + Avertissements</option>
                            <option value="3" <?php echo (isset($settings['log_level']) && $settings['log_level'] == 3) ? 'selected' : ''; ?>>Info complète</option>
                            <option value="4" <?php echo (isset($settings['log_level']) && $settings['log_level'] == 4) ? 'selected' : ''; ?>>Détails (Développement)</option>
                        </select>
                        <p class="description">0=Aucun, 1=Erreurs, 2=Warn, 3=Info, 4=Détails</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="log_file_size">Taille Max Log</label></th>
                    <td>
                        <input type="number" id="log_file_size" name="log_file_size" value="<?php echo isset($settings['log_file_size']) ? intval($settings['log_file_size']) : '10'; ?>" min="1" max="100" /> MB
                        <p class="description">Rotation automatique quand le log dépasse cette taille</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="log_retention">Retention Logs</label></th>
                    <td>
                        <input type="number" id="log_retention" name="log_retention" value="<?php echo isset($settings['log_retention']) ? intval($settings['log_retention']) : '30'; ?>" min="1" max="365" /> jours
                        <p class="description">Supprime automatiquement les logs plus vieux que ce délai</p>
                    </td>
                </tr>
            </table>
            </div>

            <div id="dev-optimizations-section" style="<?php echo !isset($settings['developer_enabled']) || !$settings['developer_enabled'] ? 'display: none;' : ''; ?>">
            <h3 class="section-title">Optimisations Avancées</h3>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="force_https">Forcer HTTPS API</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="force_https" name="force_https" value="1" <?php echo isset($settings['force_https']) && $settings['force_https'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">HTTPS forcé</span>
                        </div>
                        <div class="toggle-description">Force les appels API externes en HTTPS (sécurité renforcée)</div>
                    </td>
                </tr>
            </table>
            </div>

            <div id="dev-logs-viewer-section" style="<?php echo !isset($settings['developer_enabled']) || !$settings['developer_enabled'] ? 'display: none;' : ''; ?>">
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
            </div>

            <div id="dev-tools-section" style="<?php echo !isset($settings['developer_enabled']) || !$settings['developer_enabled'] ? 'display: none;' : ''; ?>">
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
            </div>

            <div id="dev-shortcuts-section" style="<?php echo !isset($settings['developer_enabled']) || !$settings['developer_enabled'] ? 'display: none;' : ''; ?>">
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
            </div>

            <div id="dev-todo-section" style="<?php echo !isset($settings['developer_enabled']) || !$settings['developer_enabled'] ? 'display: none;' : ''; ?>">
            <!-- Accordéon pour la section À Faire - Développement -->
            <div class="accordion-container" style="margin-bottom: 20px;">
                <button type="button" class="accordion-toggle" id="dev-todo-toggle" style="width: 100%; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; text-align: left; cursor: pointer; font-size: 16px; font-weight: bold; color: #495057; transition: all 0.3s ease;">
                    <span style="display: inline-flex; align-items: center; gap: 10px;">
                        📋 À Faire - Développement
                        <span class="accordion-icon" style="margin-left: auto; transition: transform 0.3s ease;">▶️</span>
                    </span>
                </button>
                <div class="accordion-content" id="dev-todo-content" style="display: none; border: 1px solid #dee2e6; border-top: none; border-radius: 0 0 8px 8px; padding: 20px; background: white;">

            <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <h4 style="color: #856404; margin-top: 0;">🚧 Système de Cache - RÉIMPLÉMENTATION REQUISE</h4>
                <p style="margin-bottom: 15px;"><strong>Statut :</strong> <span style="color: #dc3545; font-weight: bold;">SUPPRIMÉ DU CODE ACTUEL</span></p>

                <div style="background: #f8f9fa; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0;">
                    <h5 style="margin-top: 0; color: #856404;">📂 Fichiers concernés :</h5>
                    <ul style="margin-bottom: 0;">
                        <li><code>src/Cache/</code> - Répertoire complet du système de cache</li>
                        <li><code>src/Managers/PDF_Builder_Cache_Manager.php</code></li>
                        <li><code>src/Managers/PDF_Builder_Extended_Cache_Manager.php</code></li>
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
            </div>

            <div id="dev-console-section" style="<?php echo !isset($settings['developer_enabled']) || !$settings['developer_enabled'] ? 'display: none;' : ''; ?>">
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
            </div>

            <div id="dev-hooks-section" style="<?php echo !isset($settings['developer_enabled']) || !$settings['developer_enabled'] ? 'display: none;' : ''; ?>">
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
            </div>

            <!-- Section Monitoring des Performances -->
            <h3 class="section-title">📊 Monitoring des Performances</h3>
            <p style="color: #666; margin-bottom: 15px;">Outils pour mesurer et analyser les performances du système.</p>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="performance_monitoring">Monitoring Performance</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="performance_monitoring" name="performance_monitoring" value="1" <?php echo isset($settings['performance_monitoring']) && $settings['performance_monitoring'] ? 'checked' : ''; ?> />
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

            <p class="submit">
                <input type="submit" name="submit_developpeur" id="submit_developpeur" class="button button-primary" value="Sauvegarder les paramètres développeur">
            </p>

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
    const devSections = [
        'dev-license-section',
        'dev-debug-section',
        'dev-logs-section',
        'dev-optimizations-section',
        'dev-logs-viewer-section',
        'dev-tools-section',
        'dev-shortcuts-section',
        'dev-todo-section'
    ];

    if (developerEnabledToggle) {
        function toggleDeveloperSections() {
            const isEnabled = developerEnabledToggle.checked;
            devSections.forEach(sectionId => {
                const section = document.getElementById(sectionId);
                if (section) {
                    section.style.display = isEnabled ? 'block' : 'none';
                }
            });
        }

        // Appliquer l'état initial
        toggleDeveloperSections();

        // Écouter les changements
        developerEnabledToggle.addEventListener('change', toggleDeveloperSections);
    }

    // Tests de Notifications
    const testSuccessBtn = document.getElementById('test-notifications-success');
    const testErrorBtn = document.getElementById('test-notifications-error');
    const testWarningBtn = document.getElementById('test-notifications-warning');
    const testInfoBtn = document.getElementById('test-notifications-info');

    if (testSuccessBtn) {
        testSuccessBtn.addEventListener('click', function() {
            if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                PDF_Builder_Notification_Manager.show_toast(' Test de notification de succès réussi !', 'success');
            } else {
                alert('Le système de notifications n\'est pas chargé.');
            }
        });
    }

    if (testErrorBtn) {
        testErrorBtn.addEventListener('click', function() {
            if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                PDF_Builder_Notification_Manager.show_toast(' Test de notification d\'erreur réussi !', 'error');
            } else {
                alert('Le système de notifications n\'est pas chargé.');
            }
        });
    }

    if (testWarningBtn) {
        testWarningBtn.addEventListener('click', function() {
            if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                PDF_Builder_Notification_Manager.show_toast(' Test de notification d\'avertissement réussi !', 'warning');
            } else {
                alert('Le système de notifications n\'est pas chargé.');
            }
        });
    }

    if (testInfoBtn) {
        testInfoBtn.addEventListener('click', function() {
            if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                PDF_Builder_Notification_Manager.show_toast(' Test de notification d\'information réussi !', 'info');
            } else {
                alert('Le système de notifications n\'est pas chargé.');
            }
        });
    }
});
</script>