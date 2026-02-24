<?php // Developer tab content - Updated: 2025-11-18 20:20:00
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed, PluginCheck.Security.DirectDB, Squiz.PHP.DiscouragedFunctions, Generic.PHP.DisallowAlternativePHPTags
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Double-sécurité : bloquer l'accès direct sans le token développeur valide
if (!function_exists('pdf_builder_is_dev_access') || !pdf_builder_is_dev_access()) {
    wp_die('Accès refusé.', 403);
}

    // Récupération des paramètres depuis le tableau unifié
    $settings = pdf_builder_get_option('pdf_builder_settings', array());
    error_log('[PDF Builder] settings-developpeur.php loaded - license_test_mode: ' . ($settings['pdf_builder_license_test_mode_enabled'] ?? 'not set') . ', settings count: ' . count($settings)); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log,WordPress.PHP.DevelopmentFunctions.error_log_print_r

    // Variables nécessaires pour l'onglet développeur
    $license_test_mode = $settings['pdf_builder_license_test_mode_enabled'] ?? '0';
    $license_test_key = $settings['pdf_builder_license_test_key'] ?? '';
?>
            <h3 style="display: flex; justify-content: flex-start; align-items: center;">
                <span>👨‍💻 Paramètres Développeur</span>
            </h3>
            <p class="pdfb-developer-warning">⚠️ Cette section est réservée aux développeurs. Les modifications ici peuvent affecter le fonctionnement du plugin.</p>

                <h3 class="pdfb-pdfb-section-title">🔐 Contrôle d'Accès</h3>

             <table class="pdfb-form-table">
                <tr>
                    <th scope="row"><label for="developer_enabled">Mode Développeur</label></th>
                    <td>
                        <div class="pdfb-toggle-container">
                            <label class="pdfb-toggle-switch">
                                <input type="hidden" name="pdf_builder_settings[pdf_builder_developer_enabled]" value="0">
                                <input type="checkbox" id="developer_enabled" name="pdf_builder_settings[pdf_builder_developer_enabled]" value="1" <?php echo isset($settings['pdf_builder_developer_enabled']) && $settings['pdf_builder_developer_enabled'] && $settings['pdf_builder_developer_enabled'] !== '0' ? 'checked' : ''; ?> />
                                <span class="pdfb-toggle-slider"></span>
                            </label>
                            <span class="pdfb-toggle-label">Activer le mode développeur</span>
                            <span class="pdfb-developer-status-indicator <?php echo isset($settings['pdf_builder_developer_enabled']) && $settings['pdf_builder_developer_enabled'] && $settings['pdf_builder_developer_enabled'] !== '0' ? ' pdfb-developer-status-active ' : ' pdfb-developer-status-inactive '; ?>">
                                <?php echo isset($settings['pdf_builder_developer_enabled']) && $settings['pdf_builder_developer_enabled'] && $settings['pdf_builder_developer_enabled'] !== '0' ? 'ACTIF' : 'INACTIF'; ?>
                            </span>
                        </div>
                        <div class="pdfb-toggle-description">Active le mode développeur avec logs détaillés</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="developer_password">Mot de Passe Dev</label></th>
                    <td>
                        <!-- Champ username caché pour l'accessibilité -->
                        <input type="text" autocomplete="username" class="pdfb-hidden-element" />
                        <div class="pdfb-developer-field-group">
                            <input type="password" id="developer_password" name="pdf_builder_settings[pdf_builder_developer_password]"
                                   placeholder="Laisser vide pour aucun mot de passe" autocomplete="current-password"
                                   class="pdfb-developer-input"
                                   value="<?php echo esc_attr($settings['pdf_builder_developer_password'] ?? ''); ?>" />
                            <button type="button" id="toggle_password" class="button button-secondary pdfb-pdfb-developer-button">
                                👁️ Afficher
                            </button>
                        </div>
                        <p class="description">Protège les outils développeur avec un mot de passe (optionnel)</p>
                        <?php if (!empty($settings['developer_password'])) :
                            ?>
                        <p class="description pdfb-developer-password-set">✓ Mot de passe configuré et sauvegardé</p>
                            <?php
                        endif; ?>
                    </td>
                </tr>
             </table>

            <section id="dev-license-section" class="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? ' pdfb-developer-section-hidden ' : ''; ?>">
                <?php if (pdf_builder_is_dev_access()) : // Section rendue uniquement si le token est valide dans wp-config —— rien dans le DOM sinon ?>
                <h3 class="pdfb-pdfb-section-title">🔐 Test de Licence</h3>

                <table class="pdfb-form-table">
                    <tr>
                        <th scope="row"><label for="license_test_mode">Mode Test Licence</label></th>
                        <td>
                            <div class="pdfb-developer-field-group">
                                <button type="button" id="toggle_license_test_mode_btn" class="button button-secondary pdfb-pdfb-developer-button">
                                    🎚️ <?php echo $license_test_mode ? 'Désactiver' : 'Activer'; ?> Mode Test
                                </button>
                                <span id="license_test_mode_status" class="pdfb-license-test-mode-status <?php echo $license_test_mode ? 'pdfb-license-test-mode-active' : 'pdfb-license-test-mode-inactive'; ?>">
                                    <?php echo $license_test_mode ? '✅ MODE TEST ACTIF' : '❌ Mode test inactif'; ?>
                                </span>
                            </div>
                            <p class="description">Basculer le mode test pour développer et tester sans serveur de licence en production</p>
                            <input type="hidden" name="pdf_builder_settings[pdf_builder_license_test_mode_enabled]" value="0">
                            <input type="checkbox" id="license_test_mode" name="pdf_builder_settings[pdf_builder_license_test_mode_enabled]" value="1" <?php checked($license_test_mode, '1'); ?> class="pdfb-hidden-element" />
                            <input type="hidden" id="toggle_license_test_mode_nonce" value="<?php echo esc_attr(wp_create_nonce('pdf_builder_ajax')); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>Clé de Test</label></th>
                        <td>
                            <div class="pdfb-developer-field-group">
                                <input type="text" id="license_test_key" readonly class="license-test-key-input" placeholder="Générer une clé..." value="<?php echo esc_attr($license_test_key); ?>" />
                                <button type="button" id="generate_license_key_btn" class="button button-secondary pdfb-pdfb-developer-button">
                                    🔑 Générer
                                </button>
                                <button type="button" id="copy_license_key_btn" class="button button-secondary pdfb-pdfb-developer-button">
                                    📋 Copier
                                </button>
                                <?php if ($license_test_key) :
                                    ?>
                                <button type="button" id="delete_license_key_btn" class="button pdfb-button-link-delete pdfb-pdfb-developer-button">
                                    🗑️ Supprimer
                                </button>
                                    <?php
                                endif; ?>
                            </div>
                            <p class="description">Génère une clé de test aléatoire pour valider le système de licence</p>
                            <span id="license_key_status" style="margin-left: 0; margin-top: 10px; display: inline-block;"></span>
                            <input type="hidden" id="generate_license_key_nonce" value="<?php echo esc_attr(wp_create_nonce('pdf_builder_ajax')); ?>" />
                            <input type="hidden" id="delete_license_key_nonce" value="<?php echo esc_attr(wp_create_nonce('pdf_builder_ajax')); ?>" />
                            <input type="hidden" id="validate_license_key_nonce" value="<?php echo esc_attr(wp_create_nonce('pdf_builder_ajax')); ?>" />
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
                            <input type="hidden" id="cleanup_license_nonce" value="<?php echo esc_attr(wp_create_nonce('pdf_builder_ajax')); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>Vérification Expiration</label></th>
                        <td>
                            <button type="button" id="check_expiration_btn" class="button button-secondary" style="padding: 10px 15px; height: auto;">
                                🔍 Vérifier expiration manuellement
                            </button>
                            <p class="description">Déclenche manuellement la vérification d'expiration des licences (normalement exécutée quotidiennement par cron)</p>
                            <span id="check_expiration_status" style="margin-left: 0; margin-top: 10px; display: inline-block;"></span>
                            <input type="hidden" id="check_expiration_nonce" value="<?php echo esc_attr(wp_create_nonce('pdf_builder_ajax')); ?>" />
                        </td>
                    </tr>
                </table>
                <?php else : // Token absent — aucun contenu dans le DOM ?>
                <div style="padding:40px;text-align:center;color:#94a3b8;">
                    <span style="font-size:40px;">🔐</span>
                    <p style="margin:12px 0 0;font-size:14px;">Section non disponible.</p>
                </div>
                <?php endif; ?>
            </section>

            <section id="dev-optimizations-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="pdfb-pdfb-section-title">Optimisations Avancées</h3>

                <table class="pdfb-form-table">
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

            <section id="dev-tools-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="pdfb-pdfb-section-title">Outils de Développement</h3>

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
                <h3 class="pdfb-pdfb-section-title">🔔 Test du Système de Notifications</h3>
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
                <h3 class="pdfb-pdfb-section-title">Raccourcis Clavier Développeur</h3>

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
                        <h4 style="color: #2e7d32; margin-top: 0;">✅ Système d'aperçu PDF - IMPLÉMENTÉ</h4>
                        <p style="margin-bottom: 15px;"><strong>Statut :</strong> <span style="color: #4caf50; font-weight: bold;">COMPLÉTÉ</span></p>

                        <div style="background: #f1f8e9; border-left: 4px solid #4caf50; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #2e7d32;">✨ Implémentations complétées :</h5>
                            <ul style="margin-bottom: 0;">
                                <li>✅ <strong>Aperçu visuel</strong> : Panel sticky avec prévisualisation du format PDF</li>
                                <li>✅ <strong>Ratio d'aspect dynamique</strong> : Ajuste selon format et orientation</li>
                                <li>✅ <strong>Contrôle qualité</strong> : Barre visuelle pour la qualité export (1-100%)</li>
                                <li>✅ <strong>Compression</strong> : Affichage du type de compression sélectionné</li>
                                <li>✅ <strong>Taille estimée</strong> : Calcul intelligent basé qualité/compression/format</li>
                                <li>✅ <strong>Mise à jour en temps réel</strong> : Réactivité instantanée à chaque changement</li>
                            </ul>
                        </div>

                        <div style="background: #f8f9fa; border-left: 4px solid #2196f3; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #1976d2;">📍 Localisation :</h5>
                            <p style="margin-bottom: 0;"><strong>Fichier :</strong> <code>templates/admin/settings-parts/settings-pdf.php</code><br/>
                            <strong>Classes CSS :</strong> pdfb-pdf-* (styling Canvas purple #667eea)</p>
                        </div>

                        <div style="background: #e8f5e8; border-left: 4px solid #4caf50; padding: 15px; margin: 15px 0;">
                            <h5 style="margin-top: 0; color: #2e7d32;">🎯 Prochaines étapes :</h5>
                            <ol style="margin-bottom: 0;">
                                <li>Créer la carte <strong>"Export & Qualité"</strong> dans l'onglet Canvas Settings</li>
                                <li>Intégrer l'aperçu PDF avec les paramètres canvas</li>
                                <li>Ajouter un bouton "Exporter maintenant" dans le panel d'aperçu</li>
                                <li>Implémenter la génération réelle de miniatures PNG/JPG</li>
                            </ol>
                        </div>

                        <p style="margin-top: 15px;"><strong>Priorité :</strong> <span style="color: #4caf50; font-weight: bold;">✅ COMPLÉTÉE</span></p>
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

            <section id="dev-hooks-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <!-- Tableau de références des hooks disponibles -->
                <div class="pdfb-accordion-item">
                    <button type="button" class="pdfb-accordion-toggle" data-target="#hooks-accordion-content">
                        <span class="pdfb-accordion-icon">▶</span>
                        <h3 class="pdfb-pdfb-section-title" style="display: inline; margin: 0 0 0 10px;">Hooks Disponibles</h3>
                    </button>
                    <div id="hooks-accordion-content" class="pdfb-accordion-content" style="display: block;">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th style="width: 22%;">Hook</th>
                                    <th style="width: 43%;">Description</th>
                                    <th style="width: 15%;">Typage</th>
                                    <th style="width: 20%; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr data-hook="pdf_builder_template_data" data-type="filter">
                                    <td><code>pdf_builder_template_data</code></td>
                                    <td>Filtre les données de template</td>
                                    <td><span style="background: #e8f5e9; padding: 2px 6px; border-radius: 3px;">filter</span></td>
                                    <td style="text-align: center;"><button type="button" class="pdfb-hook-test-btn button button-small" data-hook="pdf_builder_template_data">🧪 Tester</button></td>
                                </tr>
                                <tr data-hook="pdf_builder_element_render" data-type="filter">
                                    <td><code>pdf_builder_element_render</code></td>
                                    <td>Rendu d'un élément du canvas</td>
                                    <td><span style="background: #e8f5e9; padding: 2px 6px; border-radius: 3px;">filter</span></td>
                                    <td style="text-align: center;"><button type="button" class="pdfb-hook-test-btn button button-small" data-hook="pdf_builder_element_render">🧪 Tester</button></td>
                                </tr>
                                <tr data-hook="pdf_builder_security_check" data-type="filter">
                                    <td><code>pdf_builder_security_check</code></td>
                                    <td>Vérifications de sécurité personnalisées</td>
                                    <td><span style="background: #e8f5e9; padding: 2px 6px; border-radius: 3px;">filter</span></td>
                                    <td style="text-align: center;"><button type="button" class="pdfb-hook-test-btn button button-small" data-hook="pdf_builder_security_check">🧪 Tester</button></td>
                                </tr>
                                <tr data-hook="pdf_builder_before_save" data-type="action">
                                    <td><code>pdf_builder_before_save</code></td>
                                    <td>Avant sauvegarde des paramètres</td>
                                    <td><span style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">action</span></td>
                                    <td style="text-align: center;"><button type="button" class="pdfb-hook-test-btn button button-small" data-hook="pdf_builder_before_save">🧪 Tester</button></td>
                                </tr>
                                <tr data-hook="pdf_builder_after_save" data-type="action">
                                    <td><code>pdf_builder_after_save</code></td>
                                    <td>Après sauvegarde des paramètres</td>
                                    <td><span style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">action</span></td>
                                    <td style="text-align: center;"><button type="button" class="pdfb-hook-test-btn button button-small" data-hook="pdf_builder_after_save">🧪 Tester</button></td>
                                </tr>
                                <tr data-hook="pdf_builder_initialize_canvas" data-type="action">
                                    <td><code>pdf_builder_initialize_canvas</code></td>
                                    <td>Initialisation du canvas editor</td>
                                    <td><span style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">action</span></td>
                                    <td style="text-align: center;"><button type="button" class="pdfb-hook-test-btn button button-small" data-hook="pdf_builder_initialize_canvas">🧪 Tester</button></td>
                                </tr>
                                <tr data-hook="pdf_builder_render_complete" data-type="action">
                                    <td><code>pdf_builder_render_complete</code></td>
                                    <td>Rendu PNG/PDF terminé</td>
                                    <td><span style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">action</span></td>
                                    <td style="text-align: center;"><button type="button" class="pdfb-hook-test-btn button button-small" data-hook="pdf_builder_render_complete">🧪 Tester</button></td>
                                </tr>
                                <tr data-hook="pdf_builder_pdf_generated" data-type="action">
                                    <td><code>pdf_builder_pdf_generated</code></td>
                                    <td>PDF généré avec succès</td>
                                    <td><span style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">action</span></td>
                                    <td style="text-align: center;"><button type="button" class="pdfb-hook-test-btn button button-small" data-hook="pdf_builder_pdf_generated">🧪 Tester</button></td>
                                </tr>
                                <tr data-hook="pdf_builder_admin_page_loaded" data-type="action">
                                    <td><code>pdf_builder_admin_page_loaded</code></td>
                                    <td>Page d'administration chargée</td>
                                    <td><span style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">action</span></td>
                                    <td style="text-align: center;"><button type="button" class="pdfb-hook-test-btn button button-small" data-hook="pdf_builder_admin_page_loaded">🧪 Tester</button></td>
                                </tr>
                                <tr data-hook="pdf_builder_cache_cleared" data-type="action">
                                    <td><code>pdf_builder_cache_cleared</code></td>
                                    <td>Cache vidé avec succès</td>
                                    <td><span style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">action</span></td>
                                    <td style="text-align: center;"><button type="button" class="pdfb-hook-test-btn button button-small" data-hook="pdf_builder_cache_cleared">🧪 Tester</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Section Monitoring des Performances -->
            <h3 class="pdfb-pdfb-section-title">📊 Monitoring des Performances</h3>
            <p style="color: #666; margin-bottom: 15px;">Outils pour mesurer et analyser les performances du système.</p>

            <table class="pdfb-form-table">
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
                            3. Observez le FPS affiché (devrait être proche de la cible configurée : <?php echo intval(pdf_builder_get_option('pdf_builder_canvas_fps_target', 60)); ?> FPS)<br>
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
                            • Mémoire PHP : <?php echo esc_html(ini_get('memory_limit')); ?><br>
                            • Timeout max : <?php echo esc_html(ini_get('max_execution_time')); ?>s<br>
                            • Upload max : <?php echo esc_html(ini_get('upload_max_filesize')); ?><br>
                            • Post max : <?php echo esc_html(ini_get('post_max_size')); ?><br>
                            <strong>Paramètres Performance :</strong><br>
                            • FPS cible : <?php echo intval(pdf_builder_get_option('pdf_builder_canvas_fps_target', 60)); ?> FPS<br>
                            • Mémoire JS : <?php echo intval(pdf_builder_get_option('pdf_builder_canvas_memory_limit_js', 256)); ?> MB<br>
                            • Mémoire PHP : <?php echo intval(pdf_builder_get_option('pdf_builder_canvas_memory_limit_php', 256)); ?> MB<br>
                            • Lazy Loading Éditeur : <?php echo pdf_builder_get_option('pdf_builder_canvas_lazy_loading_editor', '1') == '1' ? 'Activé' : 'Désactivé'; ?><br>
                            • Lazy Loading Plugin : <?php echo pdf_builder_get_option('pdf_builder_canvas_lazy_loading_plugin', '1') == '1' ? 'Activé' : 'Désactivé'; ?>
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

            <!-- Section Gestion Base de Données -->
            <section id="dev-database-section" style="<?php echo !isset($settings['pdf_builder_developer_enabled']) || !$settings['pdf_builder_developer_enabled'] || $settings['pdf_builder_developer_enabled'] === '0' ? 'display: none;' : ''; ?>">
                <h3 class="pdfb-pdfb-section-title">🗄️ Gestion de la Base de Données</h3>
                
                <div style="background: #e3f2fd; border: 1px solid #2196f3; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                    <h4 style="color: #0d47a1; margin-top: 0;">📊 Gestion du Stockage Personnalisé</h4>
                    <p style="margin-bottom: 15px; color: #333;">
                        Gère la table personnalisée <code style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">wp_pdf_builder_settings</code> 
                        pour le stockage des paramètres du plugin.
                    </p>

                    <div style="background: #f8f9fa; border-left: 4px solid #2196f3; padding: 15px; margin: 15px 0;">
                        <h5 style="margin-top: 0; color: #0d47a1;">🎯 Actions Disponibles :</h5>
                        <ul style="margin-bottom: 0;">
                            <li><strong>Créer la Table :</strong> Crée la table de stockage personnalisée</li>
                            <li><strong>Migrer les Données :</strong> Migre les paramètres de wp_options vers la table personnalisée</li>
                            <li><strong>Vérifier l'État :</strong> Affiche l'état actuel de la table et de la migration</li>
                        </ul>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;">
                        <button type="button" id="create_db_table_btn" class="button button-secondary" style="padding: 10px 15px; height: auto; font-weight: bold;">
                            📊 Créer la Table
                        </button>
                        <button type="button" id="migrate_db_data_btn" class="button button-secondary" style="padding: 10px 15px; height: auto; font-weight: bold;">
                            🔄 Migrer les Données
                        </button>
                        <button type="button" id="check_db_status_btn" class="button button-secondary" style="padding: 10px 15px; height: auto; font-weight: bold;">
                            ✅ Vérifier l'État
                        </button>
                    </div>

                    <div id="database_status_container" style="margin-top: 20px;">
                        <span id="database_status" style="display: inline-block;"></span>
                    </div>

                    <input type="hidden" id="create_db_table_nonce" value="<?php echo esc_attr(wp_create_nonce('pdf_builder_ajax')); ?>" />
                    <input type="hidden" id="migrate_db_data_nonce" value="<?php echo esc_attr(wp_create_nonce('pdf_builder_ajax')); ?>" />
                    <input type="hidden" id="check_db_status_nonce" value="<?php echo esc_attr(wp_create_nonce('pdf_builder_ajax')); ?>" />
                </div>
            </section>

<!-- JavaScript déplacé vers settings-main.php pour éviter les conflits -->

<script type="text/javascript">
(function($) {
    'use strict';

    $(document).ready(function() {
        console.log('🔧 [Mode Développeur] Document ready, initialisation du gestionnaire toggle');

        // === Fin déverrouillage ===
        
        // Initialiser l'état du toggle au chargement
        const $developerToggle = $('#developer_enabled');
        const $toggleSwitch = $developerToggle.closest('.pdfb-toggle-switch');
        if ($developerToggle.is(':checked')) {
            $toggleSwitch.addClass('pdfb-checked');
            console.log('🔧 [Mode Développeur] Toggle initialisé à coché');
        } else {
            $toggleSwitch.removeClass('pdfb-checked');
            console.log('🔧 [Mode Développeur] Toggle initialisé à décoché');
        }
        
        // Gestionnaire pour afficher/masquer les sections développeur
        $('#developer_enabled').on('change', function() {
            const isEnabled = $(this).is(':checked');
            const $status = $('#developer_status_indicator');
            const $toggleSwitch = $(this).closest('.pdfb-toggle-switch');
            
            console.log('🔧 [Mode Développeur] Toggle changé:', isEnabled);
            
            // Mettre à jour la classe du toggle pour la couleur de fond
            if (isEnabled) {
                $toggleSwitch.addClass('pdfb-checked');
                $status.removeClass(' pdfb-developer-status-inactive ').addClass(' pdfb-developer-status-active ').text('ACTIF');
                console.log('🔧 [Mode Développeur] Toggle mis à pdfb-checked, indicateur mis à ACTIF');
            } else {
                $toggleSwitch.removeClass('pdfb-checked');
                $status.removeClass(' pdfb-developer-status-active ').addClass(' pdfb-developer-status-inactive ').text('INACTIF');
                console.log('🔧 [Mode Développeur] Toggle retiré pdfb-checked, indicateur mis à INACTIF');
            }
            
            // Afficher/masquer les sections développeur
            const sections = [
                '#dev-license-section',
                '#dev-optimizations-section',
                '#dev-tools-section',
                '#dev-notifications-test-section',
                '#dev-shortcuts-section',
                '#dev-todo-section',
                '#dev-hooks-section',
                '#dev-database-section'
            ];
            
            sections.forEach(function(section) {
                if (isEnabled) {
                    $(section).slideDown(300).removeClass(' pdfb-developer-section-hidden ');
                    console.log('🔧 [Mode Développeur] Section affichée:', section);
                } else {
                    $(section).slideUp(300).addClass(' pdfb-developer-section-hidden ');
                    console.log('🔧 [Mode Développeur] Section masquée:', section);
                }
            });
            
            console.log('🔧 [Mode Développeur] Toutes les sections mises à jour');
            
            // Notification pour informer l'utilisateur de sauvegarder
            if (typeof showInfoNotification !== 'undefined') {
                showInfoNotification('Cliquez sur "Enregistrer" pour sauvegarder les changements du mode développeur');
            }
        });

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
                        // Notification unifiée
                        if (typeof showSuccessNotification !== 'undefined') {
                            showSuccessNotification('Clé de licence de test générée avec succès');
                        }
                    } else {
                        $status.html('<span style="color: #dc3545;">❌ Erreur: ' + (response.data.message || 'Erreur inconnue') + '</span>');
                        // Notification unifiée
                        if (typeof showErrorNotification !== 'undefined') {
                            showErrorNotification(response.data.message || 'Erreur lors de la génération de la clé');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    $status.html('<span style="color: #dc3545;">❌ Erreur AJAX: ' + error + '</span>');
                    // Notification unifiée
                    if (typeof showErrorNotification !== 'undefined') {
                        showErrorNotification('Erreur de communication lors de la génération de la clé');
                    }
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
            console.log('🔐 [Test de Licence] Bouton "Copier Clé" cliqué');
            
            const $input = $('#license_test_key');
            const key = $input.val();

            if (key) {
                console.log('🔐 [Test de Licence] Tentative de copie de la clé dans le presse-papiers');
                navigator.clipboard.writeText(key).then(function() {
                    $('#license_key_status').html('<span style="color: #28a745;">✅ Clé copiée dans le presse-papiers !</span>');
                    console.log('🔐 [Test de Licence] Clé copiée avec succès dans le presse-papiers');
                    setTimeout(function() {
                        $('#license_key_status').html('');
                    }, 3000);
                }).catch(function(err) {
                    $('#license_key_status').html('<span style="color: #dc3545;">❌ Erreur lors de la copie</span>');
                    console.error('🔐 [Test de Licence] Erreur lors de la copie dans le presse-papiers:', err);
                });
            } else {
                console.warn('🔐 [Test de Licence] Aucune clé à copier');
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
                        // Notification unifiée
                        if (typeof showSuccessNotification !== 'undefined') {
                            showSuccessNotification('Clé de licence de test supprimée avec succès');
                        }
                    } else {
                        $status.html('<span style="color: #dc3545;">❌ Erreur: ' + (response.data.message || 'Erreur inconnue') + '</span>');
                        // Notification unifiée
                        if (typeof showErrorNotification !== 'undefined') {
                            showErrorNotification(response.data.message || 'Erreur lors de la suppression de la clé');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    $status.html('<span style="color: #dc3545;">❌ Erreur AJAX: ' + error + '</span>');
                    // Notification unifiée
                    if (typeof showErrorNotification !== 'undefined') {
                        showErrorNotification('Erreur de communication lors de la suppression de la clé');
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).text('🗑️ Supprimer');
                }
            });
        });

        // Gestionnaire pour le bouton de nettoyage complet de la licence
        $('#cleanup_license_btn').on('click', function(e) {
            e.preventDefault();
            console.log('🔐 [Test de Licence] Bouton "Nettoyer Complètement" cliqué');
            
            if (!confirm('⚠️ ATTENTION: Cette action va supprimer TOUS les paramètres de licence et réinitialiser complètement le plugin à l\'état libre.\n\nCette action est IRRÉVERSIBLE.\n\nÊtes-vous absolument sûr de vouloir continuer ?')) {
                console.log('🔐 [Test de Licence] Nettoyage annulé par l\'utilisateur');
                return;
            }
            
            const $btn = $(this);
            const $status = $('#cleanup_status');
            const nonce = $('#cleanup_license_nonce').val();

            // Désactiver le bouton pendant le nettoyage
            $btn.prop('disabled', true).text('🧹 Nettoyage en cours...');
            $status.html('<span style="color: #007cba;">Nettoyage complet en cours...</span>');
            console.log('🔐 [Test de Licence] Début du nettoyage complet de la licence');

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
                    console.log('🔐 [Test de Licence] Réponse AJAX nettoyage reçue:', response);
                    if (response.success) {
                        // Supprimer immédiatement la clé de test du champ
                        $('#license_test_key').val('');
                        
                        // Désactiver le mode test
                        $('#license_test_mode').prop('checked', false);
                        $('#license_test_mode_status')
                            .removeClass('license-test-mode-active')
                            .addClass('license-test-mode-inactive')
                            .text('❌ Mode test inactif');
                        $('#toggle_license_test_mode_btn').text('🎚️ Activer Mode Test');
                        
                        $status.html('<span style="color: #28a745;">✅ Nettoyage complet réussi ! Le plugin a été réinitialisé à l\'état libre.</span>');
                        $btn.hide();
                        console.log('🔐 [Test de Licence] Nettoyage complet réussi, rechargement de la page dans 2 secondes');
                        // Recharger la page après 2 secondes pour voir les changements
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        console.error('🔐 [Test de Licence] Erreur lors du nettoyage:', response.data.message);
                        $status.html('<span style="color: #dc3545;">❌ Erreur: ' + (response.data.message || 'Erreur inconnue') + '</span>');
                        // Notification unifiée
                        if (typeof showErrorNotification !== 'undefined') {
                            showErrorNotification(response.data.message || 'Erreur lors du nettoyage de la licence');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('🔐 [Test de Licence] Erreur AJAX lors du nettoyage:', error);
                    $status.html('<span style="color: #dc3545;">❌ Erreur AJAX: ' + error + '</span>');
                    // Notification unifiée
                    if (typeof showErrorNotification !== 'undefined') {
                        showErrorNotification('Erreur de communication lors du nettoyage de la licence');
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).text('🧹 Nettoyer complètement la licence');
                    console.log('🔐 [Test de Licence] Nettoyage terminé');
                }
            });
        });

        // Gestionnaire pour le bouton de vérification d'expiration
        $('#check_expiration_btn').on('click', function(e) {
            e.preventDefault();
            console.log('🔍 [Vérification Expiration] Bouton cliqué');
            
            const $btn = $(this);
            const $status = $('#check_expiration_status');
            const nonce = $('#check_expiration_nonce').val();

            // Désactiver le bouton pendant la vérification
            $btn.prop('disabled', true).text('🔍 Vérification en cours...');
            $status.html('<span style="color: #007cba;">Vérification d\'expiration en cours...</span>');
            console.log('🔍 [Vérification Expiration] Début de la vérification');

            // Requête AJAX
            $.ajax({
                url: pdf_builder_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_ajax_handler',
                    action_type: 'check_license_expiration',
                    nonce: nonce
                },
                success: function(response) {
                    console.log('🔍 [Vérification Expiration] Réponse AJAX reçue:', response);
                    if (response.success) {
                        const data = response.data;
                        let message = '✅ Vérification terminée. ';
                        message += 'Statut: ' + data.license_status + ', ';
                        message += 'Clé licence: ' + (data.has_license_key ? 'présente' : 'absente') + ', ';
                        message += 'Clé test: ' + (data.has_test_key ? 'présente' : 'absente');
                        
                        $status.html('<span style="color: #28a745;">' + message + '</span>');
                        console.log('🔍 [Vérification Expiration] Vérification réussie');
                        // Notification unifiée
                        if (typeof showSuccessNotification !== 'undefined') {
                            showSuccessNotification('Vérification d\'expiration terminée avec succès');
                        }
                    } else {
                        console.error('🔍 [Vérification Expiration] Erreur:', response.data.message);
                        $status.html('<span style="color: #dc3545;">❌ Erreur: ' + (response.data.message || 'Erreur inconnue') + '</span>');
                        // Notification unifiée
                        if (typeof showErrorNotification !== 'undefined') {
                            showErrorNotification(response.data.message || 'Erreur lors de la vérification d\'expiration');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('🔍 [Vérification Expiration] Erreur AJAX:', error);
                    $status.html('<span style="color: #dc3545;">❌ Erreur AJAX: ' + error + '</span>');
                    // Notification unifiée
                    if (typeof showErrorNotification !== 'undefined') {
                        showErrorNotification('Erreur de communication lors de la vérification d\'expiration');
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).text('🔍 Vérifier expiration manuellement');
                    console.log('🔍 [Vérification Expiration] Vérification terminée');
                }
            });
        });

        // Gestionnaire pour le bouton toggle du mode test
        $('#toggle_license_test_mode_btn').on('click', function(e) {
            e.preventDefault();
            console.log('🔐 [Test de Licence] Bouton "Toggle Mode Test" cliqué');
            
            const $btn = $(this);
            const $status = $('#license_test_mode_status');
            const nonce = $('#toggle_license_test_mode_nonce').val();

            // Désactiver le bouton pendant l'opération
            $btn.prop('disabled', true).text('🔄 Basculement...');
            $status.html('<span style="color: #007cba;">Basculement en cours...</span>');
            console.log('🔐 [Test de Licence] Début du basculement du mode test');

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
                    console.log('🔐 [Test de Licence] Réponse AJAX reçue:', response);
                    if (response.success) {
                        const newMode = response.data.new_mode;
                        const isActive = newMode === '1';
                        $status.html('<span class="' + (isActive ? 'license-test-mode-active' : 'license-test-mode-inactive') + '">' + (isActive ? '✅ MODE TEST ACTIF' : '❌ Mode test inactif') + '</span>');
                        $btn.text(isActive ? '🎚️ Désactiver Mode Test' : '🎚️ Activer Mode Test');
                        
                        // Mettre à jour le checkbox caché
                        $('#license_test_mode').prop('checked', isActive);
                        console.log('🔐 [Test de Licence] Mode test ' + (isActive ? 'activé' : 'désactivé') + ' avec succès');
                        
                        // Recharger la page après 1 seconde pour voir les changements dans l'onglet licence
                        // Ajouter un paramètre de cache busting pour forcer le rechargement des options
                        setTimeout(function() {
                            const cacheBust = Date.now();
                            const currentUrl = new URL(window.location);
                            currentUrl.searchParams.set('cache_bust', cacheBust);
                            window.location.href = currentUrl.toString();
                        }, 1000);
                        // Notification unifiée
                        if (typeof showSuccessNotification !== 'undefined') {
                            showSuccessNotification('Mode test ' + (isActive ? 'activé' : 'désactivé') + ' avec succès');
                        }
                    } else {
                        console.error('🔐 [Test de Licence] Erreur lors du basculement:', response.data.message);
                        $status.html('<span style="color: #dc3545;">❌ Erreur: ' + (response.data.message || 'Erreur inconnue') + '</span>');
                        // Notification unifiée
                        if (typeof showErrorNotification !== 'undefined') {
                            showErrorNotification(response.data.message || 'Erreur lors du changement de mode test');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('🔐 [Test de Licence] Erreur AJAX lors du basculement:', error);
                    $status.html('<span style="color: #dc3545;">❌ Erreur AJAX: ' + error + '</span>');
                    // Notification unifiée
                    if (typeof showErrorNotification !== 'undefined') {
                        showErrorNotification('Erreur de communication lors du changement de mode test');
                    }
                },
                complete: function() {
                    const currentMode = $('#license_test_mode').is(':checked') ? '1' : '0';
                    const isActive = currentMode === '1';
                    $btn.prop('disabled', false).text(isActive ? '🎚️ Désactiver Mode Test' : '🎚️ Activer Mode Test');
                    console.log('🔐 [Test de Licence] Basculement terminé');
                }
            });
        });

        // Gestionnaire pour le bouton de génération de clé de test
        $('#generate_license_key_btn').on('click', function(e) {
            e.preventDefault();
            console.log('🔐 [Test de Licence] Bouton "Générer Clé" cliqué');
            
            const $btn = $(this);
            const $status = $('#license_key_status');
            const nonce = $('#generate_license_key_nonce').val();

            // Désactiver le bouton pendant la génération
            $btn.prop('disabled', true).text('🔄 Génération...');
            $status.html('<span style="color: #007cba;">Génération de la clé en cours...</span>');
            console.log('🔐 [Test de Licence] Début de la génération de clé de test');

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
                    console.log('🔐 [Test de Licence] Réponse AJAX génération reçue:', response);
                    if (response.success) {
                        $('#license_test_key').val(response.data.test_key);
                        $status.html('<span style="color: #28a745;">✅ Clé générée: ' + response.data.test_key + '</span>');
                        $('#delete_license_key_btn').show();
                        console.log('🔐 [Test de Licence] Clé générée avec succès:', response.data.test_key);
                        // Notification unifiée
                        if (typeof showSuccessNotification !== 'undefined') {
                            showSuccessNotification('Nouvelle clé de licence de test générée');
                        }
                    } else {
                        console.error('🔐 [Test de Licence] Erreur lors de la génération:', response.data.message);
                        $status.html('<span style="color: #dc3545;">❌ Erreur: ' + (response.data.message || 'Erreur inconnue') + '</span>');
                        // Notification unifiée
                        if (typeof showErrorNotification !== 'undefined') {
                            showErrorNotification(response.data.message || 'Erreur lors de la génération de la clé de test');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('🔐 [Test de Licence] Erreur AJAX lors de la génération:', error);
                    $status.html('<span style="color: #dc3545;">❌ Erreur AJAX: ' + error + '</span>');
                    // Notification unifiée
                    if (typeof showErrorNotification !== 'undefined') {
                        showErrorNotification('Erreur de communication lors de la génération de la clé de test');
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).text('🔑 Générer');
                    console.log('🔐 [Test de Licence] Génération de clé terminée');
                }
            });
        });

        // Gestionnaire pour le bouton de suppression de clé de test
        $('#delete_license_key_btn').on('click', function(e) {
            e.preventDefault();
            console.log('🔐 [Test de Licence] Bouton "Supprimer Clé" cliqué');
            
            if (!confirm('Êtes-vous sûr de vouloir supprimer la clé de test ?')) {
                console.log('🔐 [Test de Licence] Suppression annulée par l\'utilisateur');
                return;
            }
            
            const $btn = $(this);
            const $status = $('#license_key_status');
            const nonce = $('#delete_license_key_nonce').val();

            // Désactiver le bouton pendant la suppression
            $btn.prop('disabled', true).text('🗑️ Suppression...');
            $status.html('<span style="color: #007cba;">Suppression en cours...</span>');
            console.log('🔐 [Test de Licence] Début de la suppression de la clé de test');

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
                    console.log('🔐 [Test de Licence] Réponse AJAX suppression reçue:', response);
                    if (response.success) {
                        $('#license_test_key').val('');
                        $status.html('<span style="color: #28a745;">✅ Clé supprimée avec succès</span>');
                        $btn.hide();
                        console.log('🔐 [Test de Licence] Clé supprimée avec succès');
                        // Notification unifiée
                        if (typeof showSuccessNotification !== 'undefined') {
                            showSuccessNotification('Clé de licence de test supprimée avec succès');
                        }
                    } else {
                        console.error('🔐 [Test de Licence] Erreur lors de la suppression:', response.data.message);
                        $status.html('<span style="color: #dc3545;">❌ Erreur: ' + (response.data.message || 'Erreur inconnue') + '</span>');
                        // Notification unifiée
                        if (typeof showErrorNotification !== 'undefined') {
                            showErrorNotification(response.data.message || 'Erreur lors de la suppression de la clé de test');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('🔐 [Test de Licence] Erreur AJAX lors de la suppression:', error);
                    $status.html('<span style="color: #dc3545;">❌ Erreur AJAX: ' + error + '</span>');
                    // Notification unifiée
                    if (typeof showErrorNotification !== 'undefined') {
                        showErrorNotification('Erreur de communication lors de la suppression de la clé de test');
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).text('🗑️ Supprimer');
                    console.log('🔐 [Test de Licence] Suppression de clé terminée');
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
                        // Notification unifiée
                        if (typeof showSuccessNotification !== 'undefined') {
                            showSuccessNotification('Clé de licence validée avec succès');
                        }
                    } else {
                        $status.html('<span style="color: #dc3545;">❌ Clé invalide</span>');
                        // Notification unifiée
                        if (typeof showErrorNotification !== 'undefined') {
                            showErrorNotification('Clé de licence invalide');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    $status.html('<span style="color: #dc3545;">❌ Erreur AJAX: ' + error + '</span>');
                    // Notification unifiée
                    if (typeof showErrorNotification !== 'undefined') {
                        showErrorNotification('Erreur de communication lors de la validation de la clé');
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).text('✅ Valider');
                }
            });
        });

        // ============================================
        // Gestionnaires pour la gestion de la BD
        // ============================================

        // Créer la table
        $('#create_db_table_btn').on('click', function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const $status = $('#database_status');
            const nonce = $('#create_db_table_nonce').val();

            $btn.prop('disabled', true).text('⏳ Création...');
            $status.html('<span style="color: #007cba;">Création de la table en cours...</span>');

            $.ajax({
                url: pdf_builder_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_ajax_handler',
                    action_type: 'manage_database_table',
                    sub_action: 'create_table',
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        $status.html('<span style="color: #28a745;">✅ ' + response.data.message + '</span>');
                    } else {
                        $status.html('<span style="color: #dc3545;">❌ Erreur: ' + (response.data.message || 'Erreur inconnue') + '</span>');
                    }
                },
                error: function(xhr, status, error) {
                    $status.html('<span style="color: #dc3545;">❌ Erreur AJAX: ' + error + '</span>');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('📊 Créer la Table');
                }
            });
        });

        // Migrer les données
        $('#migrate_db_data_btn').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm('Êtes-vous sûr de vouloir migrer les données ? Cette opération copiera tous les paramètres vers la table personnalisée.')) {
                return;
            }
            
            const $btn = $(this);
            const $status = $('#database_status');
            const nonce = $('#migrate_db_data_nonce').val();

            $btn.prop('disabled', true).text('⏳ Migration...');
            $status.html('<span style="color: #007cba;">Migration des données en cours...</span>');

            $.ajax({
                url: pdf_builder_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_ajax_handler',
                    action_type: 'manage_database_table',
                    sub_action: 'migrate_data',
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        $status.html('<span style="color: #28a745;">✅ ' + response.data.message + '</span>');
                    } else {
                        $status.html('<span style="color: #dc3545;">❌ Erreur: ' + (response.data.message || 'Erreur inconnue') + '</span>');
                    }
                },
                error: function(xhr, status, error) {
                    $status.html('<span style="color: #dc3545;">❌ Erreur AJAX: ' + error + '</span>');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('🔄 Migrer les Données');
                }
            });
        });

        // Vérifier l'état
        $('#check_db_status_btn').on('click', function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const $status = $('#database_status');
            const nonce = $('#check_db_status_nonce').val();

            $btn.prop('disabled', true).text('⏳ Vérification...');
            $status.html('<span style="color: #007cba;">Vérification de l\'état en cours...</span>');

            $.ajax({
                url: pdf_builder_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_ajax_handler',
                    action_type: 'manage_database_table',
                    sub_action: 'check_status',
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        let status_html = '<div style="background: #f1f8e9; border: 1px solid #4caf50; border-radius: 4px; padding: 15px; margin-top: 10px;">';
                        
                        status_html += '<h5 style="margin-top: 0; color: #2e7d32;">📊 État de la Base de Données :</h5>';
                        status_html += '<ul style="margin: 0; padding-left: 20px;">';
                        status_html += '<li><strong>Table existe :</strong> ' + (data.table_exists ? '✅ Oui' : '❌ Non') + '</li>';
                        status_html += '<li><strong>Colonnes :</strong> ' + (data.columns_count || 0) + ' colonnes</li>';
                        status_html += '<li><strong>Enregistrements :</strong> ' + (data.records_count || 0) + ' enregistrements</li>';
                        status_html += '<li><strong>Migration effectuée :</strong> ' + (data.is_migrated ? '✅ Oui' : '❌ Non') + '</li>';
                        status_html += '</ul>';
                        status_html += '</div>';
                        
                        $status.html(status_html);
                    } else {
                        $status.html('<span style="color: #dc3545;">❌ Erreur: ' + (response.data.message || 'Erreur inconnue') + '</span>');
                    }
                },
                error: function(xhr, status, error) {
                    $status.html('<span style="color: #dc3545;">❌ Erreur AJAX: ' + error + '</span>');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('✅ Vérifier l\'État');
                }
            });
        });

        // Gestionnaire pour le toggle accordéon des Hooks Disponibles
        $('.pdfb-accordion-toggle').on('click', function(e) {
            e.preventDefault();
            const $toggle = $(this);
            const targetId = $toggle.data('target');
            const $content = $(targetId);

            console.log('🪝 [Accordéon Hooks] Toggle cliqué, target:', targetId);

            $toggle.toggleClass('open');
            
            if ($toggle.hasClass('open')) {
                $content.slideDown(300).removeClass('collapsed');
                console.log('🪝 [Accordéon Hooks] Accordéon ouvert');
            } else {
                $content.slideUp(300).addClass('collapsed');
                console.log('🪝 [Accordéon Hooks] Accordéon fermé');
            }
        });

        // Initialiser l'accordéon au chargement
        if ($('#hooks-accordion-content').length) {
            $('.pdfb-accordion-toggle').addClass('open');
            console.log('🪝 [Accordéon Hooks] Initialisé avec état ouvert');
        }

        // Gestionnaire pour tester les hooks disponibles
        $(document).on('click', '.pdfb-hook-test-btn', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const hookName = $btn.data('hook');
            const $row = $btn.closest('tr');
            const hookType = $row.data('type');
            
            const originalText = $btn.html();
            $btn.prop('disabled', true).html('⏳ Test en cours...');
            
            console.log('🪝 [Test Hook] Lancement test pour:', hookName, '(type:', hookType + ')');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_test_hook',
                    hookName: hookName,
                    hookType: hookType,
                    nonce: $('#check_db_status_nonce').val()
                },
                success: function(response) {
                    console.log('🪝 [Test Hook] Réponse AJAX:', response);

                    if (response.success) {
                        const hookInfo = response.data;
                        
                        // Créer une notification de succès
                        if (typeof showSuccessNotification !== 'undefined') {
                            showSuccessNotification('Hook ' + hookName + ' testé avec succès! Voir console pour détails.');
                        }

                        // Afficher les informations dans la console
                        let consoleMsg = '🪝 ════════════════════════════════════════════════════\n';
                        consoleMsg += '🪝 RÉSULTAT TEST HOOK: ' + hookName + '\n';
                        consoleMsg += '🪝 ════════════════════════════════════════════════════\n';
                        consoleMsg += 'Type: ' + hookInfo.type + '\n';
                        consoleMsg += 'Enregistré: ' + (hookInfo.is_registered ? 'OUI ✅' : 'NON ❌') + '\n';
                        consoleMsg += 'Nombre de callbacks: ' + hookInfo.callback_count + '\n';
                        
                        if (hookInfo.callbacks.length > 0) {
                            consoleMsg += '\nCallbacks attachés:\n';
                            hookInfo.callbacks.forEach(function(cb, idx) {
                                consoleMsg += '  ' + (idx + 1) + '. ' + cb.function + ' (priorité: ' + cb.priority + ')\n';
                            });
                        } else {
                            consoleMsg += '\nAucun callback enregistré\n';
                        }
                        
                        consoleMsg += '🪝 ════════════════════════════════════════════════════';
                        console.log(consoleMsg);

                        // Log detaillé
                        console.table({
                            'Hook': hookName,
                            'Type': hookType,
                            'Enregistré': hookInfo.is_registered ? 'Oui' : 'Non',
                            'Callbacks': hookInfo.callback_count,
                            'Détails': JSON.stringify(hookInfo.callbacks)
                        });
                    } else {
                        if (typeof showErrorNotification !== 'undefined') {
                            showErrorNotification('Erreur lors du test: ' + (response.data || 'Erreur inconnue'));
                        }
                        console.error('🪝 [Test Hook] Erreur:', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    if (typeof showErrorNotification !== 'undefined') {
                        showErrorNotification('Erreur AJAX: ' + error);
                    }
                    console.error('🪝 [Test Hook] Erreur AJAX:', error, xhr);
                },
                complete: function() {
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        });
    });
})(jQuery);
</script>

<?php
// Inclure les modales et le bouton flottant à la fin pour éviter les conflits de structure
require_once __DIR__ . '/settings-modals.php';
?>

