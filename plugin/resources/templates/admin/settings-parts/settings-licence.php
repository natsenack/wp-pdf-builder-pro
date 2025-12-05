<?php // Licence tab content - Updated: AJAX centralized 2025-12-02

require_once __DIR__ . '/../settings-helpers.php';
?>
            <!-- Licence Settings Section (No Form - AJAX Centralized) -->
            <section id="licence-container" aria-label="Gestion de la Licence">
                <h2 class="settings-page-title">🔐 Gestion de la Licence</h2>

                

                <?php
                    // Récupération des paramètres depuis le tableau unifié
                    $settings = get_option('pdf_builder_settings', []);

                    $license_status = $settings['pdf_builder_license_status'] ?? 'free';
                    $license_key = $settings['pdf_builder_license_key'] ?? '';
                    $license_expires = $settings['pdf_builder_license_expires'] ?? '';
                    $license_activated_at = $settings['pdf_builder_license_activated_at'] ?? '';
                    $test_mode_enabled = $settings['pdf_builder_license_test_mode_enabled'] ?? false;
                    $test_key = $settings['pdf_builder_license_test_key'] ?? '';
                    $test_key_expires = $settings['pdf_builder_license_test_key_expires'] ?? '';
                    $license_email_reminders = $settings['pdf_builder_license_email_reminders'] ?? '0';
                    $license_reminder_email = $settings['pdf_builder_license_reminder_email'] ?? pdf_builder_safe_get_option('admin_email', '');
                    // Email notifications removed — no UI or settings for license expiration notifications
                    // is_premium si vraie licence OU si clé de test existe
                    $is_premium = ($license_status !== 'free' && $license_status !== 'expired') || (!empty($test_key));
                    // is_test_mode si clé de test existe
                    $is_test_mode = !empty($test_key);
                    // DEBUG: Afficher les valeurs pour verifier
                    if (current_user_can('manage_options')) {
                        echo '<!-- DEBUG: status=' . esc_html($license_status) . ' key=' . (!empty($license_key) ? 'YES' : 'NO') . ' test_key=' . (!empty($test_key) ? 'YES:' . substr($test_key, 0, 5) : 'NO') . ' is_premium=' . ($is_premium ? 'TRUE' : 'FALSE') . ' -->';
                    }

                    // Traitement activation licence
                    if (isset($_POST['activate_license']) && isset($_POST['pdf_builder_license_nonce'])) {
                     // Mode DÉMO : Activation de clés réelles désactivée
                        // Les clés premium réelles seront validées une fois le système de licence en production
                        wp_die('<div class="alert-demo">
                                <h2>⚠️ Mode DÉMO</h2>
                                <p><strong>La validation des clés premium n\'est pas encore active.</strong></p>
                                <p>Pour tester les fonctionnalités premium, veuillez :</p>
                                <ol>
                                    <li>Allez à l\'onglet <strong>Développeur</strong></li>
                                    <li>Cliquez sur <strong>Générer une clé de test</strong></li>
                                    <li>La clé TEST s\'activera automatiquement</li>
                                </ol>
                                <p><a href="' . admin_url('admin.php?page=pdf-builder-pro-settings&tab=developer') . '">↻ Aller au mode Développeur</a></p>
                            </div>', 'Activation désactivée', ['response' => 403]);
                    }

                    // Traitement désactivation licence (legacy - devrait être remplacé par AJAX unifié)
                    if (isset($_POST['deactivate_license']) && isset($_POST['pdf_builder_deactivate_nonce'])) {

                        if (wp_verify_nonce($_POST['pdf_builder_deactivate_nonce'], 'pdf_builder_deactivate')) {
                            // Mise à jour du tableau unifié au lieu d'options séparées
                            $settings = get_option('pdf_builder_settings', []);
                            $settings['pdf_builder_license_key'] = '';
                            $settings['pdf_builder_license_expires'] = '';
                            $settings['pdf_builder_license_activated_at'] = '';
                            $settings['pdf_builder_license_test_key'] = '';
                            $settings['pdf_builder_license_test_mode_enabled'] = false;
                            $settings['pdf_builder_license_status'] = 'free';
                            update_option('pdf_builder_settings', $settings);

                            $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Licence désactivée complètement.</p></div>';
                            $is_premium = false;
                            $license_key = '';
                            $license_status = 'free';
                            $license_activated_at = '';
                            $test_key = '';
                            $test_mode_enabled = false;
                        }
                    }
                ?>

                    <!-- Statut de la licence -->
                <section class="licence-section">
                        <h3 class="settings-section-title">📊 Statut de la Licence</h3>

                        <section class="status-cards-grid">
                            <!-- Carte Statut Principal -->
                            <article class="status-card<?php echo $is_premium ? ' premium' : ''; ?>">
                                <aside class="status-card-label">Statut</aside>
                                <p class="status-card-value<?php echo $is_premium ? ' premium' : ''; ?>">
                                    <?php echo $is_premium ? '✅ Premium Actif' : '○ Gratuit'; ?>
                                </p>
                                <aside class="status-card-description<?php echo $is_premium ? ' premium' : ''; ?>">
                                    <?php echo $is_premium ? 'Licence premium activée' : 'Aucune licence premium'; ?>
                                </aside>
                            </article>

                            <!-- Carte Mode Test (si applicable) -->
                            <?php if (!empty($test_key)) :
                                ?>
                            <article class="status-card test">
                                <aside class="status-card-label">Mode</aside>
                                <p class="status-card-value test">
                                    🧪 TEST (Dev)
                                </p>
                                <aside class="status-card-description test">
                                    Mode développement actif
                                </aside>
                            </article>
                                <?php
                            endif; ?>

                            <!-- Carte Date d'expiration -->
                            <?php if ($is_premium && $license_expires) :
                                ?>
                            <article class="status-card expiry">
                                <aside class="status-card-label">Expire le</aside>
                                <p class="status-card-value expiry">
                                    <?php echo date('d/m/Y', strtotime($license_expires)); ?>
                                </p>
                                <aside class="status-card-description expiry">
                                    <?php
                                    $now = new DateTime();
                                    $expires = new DateTime($license_expires);
                                    $diff = $now->diff($expires);
                                    if ($diff->invert) {
                                        echo '❌ Expiré il y a ' . $diff->days . ' jours';
                                    } else {
                                        echo '✓ Valide pendant ' . $diff->days . ' jours';
                                    }
                                    ?>
                                </aside>
                            </article>
                                <?php
                            endif; ?>
                        </section>

                        <?php
                        // Bannière d'alerte si expiration dans moins de 30 jours
                        if ($is_premium && !empty($license_expires)) {
                            $now = new DateTime();
                            $expires = new DateTime($license_expires);
                            $diff = $now->diff($expires);

                            if (!$diff->invert && $diff->days <= 30 && $diff->days > 0) {
                                ?>
                                <aside class="license-alert">
                                    <section class="license-alert-content">
                                        <span class="alert-icon">⏰</span>
                                        <span>
                                            <strong class="alert-title">Votre licence expire bientôt</strong>
                                            <p class="alert-text">
                                                Votre licence Premium expire dans <strong><?php echo $diff->days; ?> jour<?php echo $diff->days > 1 ? 's' : ''; ?></strong> (le <?php echo date('d/m/Y', strtotime($license_expires)); ?>).
                                                Renouvelez dès maintenant pour continuer à bénéficier de toutes les fonctionnalités premium.
                                            </p>
                                        </span>
                                    </section>
                                </aside>
                                <?php
                            }
                        }
                        ?>                        <!-- Détails de la clé -->
                        <?php if ($is_premium || !empty($test_key)) :
                            ?>
                        <article class="license-details-card">
                            <header class="license-details-header">
                                <h4>🔐 Détails de la Clé</h4>
                                <?php if ($is_premium) :
                                    ?>
                                <button type="button" class="button button-secondary deactivate-btn"
                                        onclick="showDeactivateModal()">
                                    Désactiver
                                </button>
                                    <?php
                                endif; ?>
                            </header>
                            <table class="license-details-table">
                                <tr>
                                    <td>Site actuel :</td>
                                    <td>
                                        <code class="code-inline">
                                            <?php echo esc_html(home_url()); ?>
                                        </code>
                                    </td>
                                </tr>

                                <?php if ($is_premium && $license_key) :
                                    ?>
                                <tr>
                                    <td>Clé Premium :</td>
                                    <td>
                                        <code class="license-key-display">
                                            <?php
                                            $key = $license_key;
                                            $visible_start = substr($key, 0, 6);
                                            $visible_end = substr($key, -6);
                                            echo $visible_start . '••••••••••••••••' . $visible_end;
                                            ?>
                                        </code>
                                        <span class="copy-link" onclick="navigator.clipboard.writeText('<?php echo esc_js($license_key); ?>');">📋 Copier</span>
                                    </td>
                                </tr>
                                    <?php
                                endif; ?>

                                <?php if (!empty($test_key)) :
                                    ?>
                                <tr>
                                    <td>Clé de Test :</td>
                                    <td>
                                        <code class="test-key-display">
                                            <?php
                                            $test = $test_key;
                                            echo substr($test, 0, 6) . '••••••••••••••••' . substr($test, -6);
                                            ?>
                                        </code>
                                        <span class="test-mode-indicator"> (Mode Développement)</span>
                                    </td>
                                </tr>
                                    <?php if (!empty($test_key_expires)) :
                                        ?>
                                <tr>
                                    <td>Expire le :</td>
                                    <td>
                                        <p class="test-expiry-date">
                                            <strong><?php echo date('d/m/Y', strtotime($test_key_expires)); ?></strong>
                                        </p>
                                        <p class="test-expiry-status">
                                            <?php
                                            $now = new DateTime();
                                            $expires = new DateTime($test_key_expires);
                                            $diff = $now->diff($expires);
                                            if ($diff->invert) {
                                                echo '❌ Expiré il y a ' . $diff->days . ' jour' . ($diff->days > 1 ? 's' : '');
                                            } else {
                                                echo '✓ Valide pendant ' . $diff->days . ' jour' . ($diff->days > 1 ? 's' : '');
                                            }
                                            ?>
                                        </p>
                                    </td>
                                </tr>
                                        <?php
                                    endif; ?>
                                    <?php
                                endif; ?>

                                <?php if ($is_premium && $license_activated_at) :
                                    ?>
                                <tr>
                                    <td>Activée le :</td>
                                    <td>
                                        <?php echo date('d/m/Y à H:i', strtotime($license_activated_at)); ?>
                                    </td>
                                </tr>
                                    <?php
                                endif; ?>

                                <tr>
                                    <td>Statut :</td>
                                    <td>
                                        <?php
                                        if (!empty($test_key)) {
                                            echo '<span class="status-badge status-test">🧪 MODE TEST</span>';
                                        } elseif ($is_premium) {
                                            echo '<span class="status-badge status-active">✅ ACTIVE</span>';
                                        } else {
                                            echo '<span class="status-badge status-free">○ GRATUIT</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>

                                <?php if ($is_premium && !empty($license_expires)) :
                                    ?>
                                <tr>
                                    <td>Expire le :</td>
                                    <td>
                                        <p class="license-expiry-date">
                                            <strong><?php echo date('d/m/Y', strtotime($license_expires)); ?></strong>
                                        </p>
                                        <p class="license-expiry-status">
                                            <?php
                                            $now = new DateTime();
                                            $expires = new DateTime($license_expires);
                                            $diff = $now->diff($expires);
                                            if ($diff->invert) {
                                                echo '❌ Expiré il y a ' . $diff->days . ' jour' . ($diff->days > 1 ? 's' : '');
                                            } else {
                                                echo '✓ Valide pendant ' . $diff->days . ' jour' . ($diff->days > 1 ? 's' : '');
                                            }
                                            ?>
                                        </p>
                                    </td>
                                </tr>
                                    <?php
                                endif; ?>
                            </table>
                            <?php
                         endif; ?>
                        </article>
                </section>

                    <!-- Activation/Désactivation - Mode DEMO ou Gestion TEST -->
                    <?php if (!$is_premium) :
                        ?>
                    <!-- Mode DÉMO : Pas de licence -->
                    <section class="licence-section demo-mode">
                        <header class="demo-header">
                            <span class="demo-icon">🧪</span>
                            <div>
                                <h3 class="demo-title">Mode DÉMO - Clés de Test Uniquement</h3>
                                <p class="demo-description">La validation des clés premium n'est pas encore active. Utilisez le mode TEST pour explorer les fonctionnalités.</p>
                            </div>
                        </header>

                        <article class="demo-info">
                            <strong>✓ Comment tester :</strong>
                            <ol>
                                <li>Allez à l'onglet <strong>Développeur</strong></li>
                                <li>Cliquez sur <strong>🔑 Générer une clé de test</strong></li>
                                <li>La clé TEST s'activera automatiquement</li>
                                <li>Toutes les fonctionnalités premium seront disponibles</li>
                            </ol>
                        </article>

                        <aside class="demo-warning">
                            <strong>⚠️ Note importante :</strong> Les clés premium réelles seront validées une fois le système de licence en production.
                        </aside>
                    </section>
                        <?php
                    elseif ($is_test_mode) :
                        ?>
                    <!-- Mode TEST : Gestion de la clé de test -->
                    <section class="licence-section test-mode">
                        <header class="test-header">
                            <span class="test-icon">🧪</span>
                            <div>
                                <h3 class="test-title">Gestion de la Clé de Test</h3>
                                <p class="test-description">Vous testez actuellement avec une clé TEST. Toutes les fonctionnalités premium sont disponibles.</p>
                            </div>
                        </header>

                        <aside class="test-info">
                            <strong>ℹ️ Mode Test Actif :</strong> Vous pouvez désactiver cette clé à tout moment depuis la section "Détails de la Clé" ci-dessus, ou générer une nouvelle clé de test depuis l'onglet Développeur.
                        </aside>
                    </section>
                        <?php
                    else :
                        ?>
                    <!-- Mode PREMIUM : Gestion de la licence premium -->
                    <section class="licence-section premium-mode">
                        <header class="premium-header">
                            <span class="premium-icon">🔐</span>
                            <div>
                                <h3 class="premium-title">Gestion de la Licence Premium</h3>
                                <p class="premium-description">Votre licence premium est active et valide. Vous pouvez gérer votre licence ci-dessous.</p>
                            </div>
                        </header>

                        <!-- Avertissements et informations -->
                        <aside class="premium-warning">
                            <strong>Savoir :</strong>
                            <ul>
                                <li>Votre licence reste <strong>active pendant un an</strong> à partir de son activation</li>
                                <li>Même après désactivation, la licence reste valide jusqu'à son expiration</li>
                                <li><strong>Désactivez</strong> pour utiliser la même clé sur un autre site WordPress</li>
                                <li>Une clé ne peut être active que sur <strong>un seul site à la fois</strong></li>
                            </ul>
                        </aside>

                        <article>
                            <button type="button" id="deactivate-license-btn" class="button button-secondary premium-deactivate-btn">
                                Désactiver la Licence
                            </button>
                        </article>

                        <aside class="premium-tip">
                            <strong>Conseil :</strong>
                            <p>La désactivation permet de réutiliser votre clé sur un autre site, mais ne supprime pas votre accès ici jusqu'à l'expiration de la licence.</p>
                        </aside>
                    </section>

                        <?php
                    endif; ?>

                    <?php if ($is_premium) : ?>
                    <!-- Modal de confirmation pour désactivation -->
                    <div id="deactivate_modal" class="modal-overlay">
                        <section class="modal-content">
                            <span class="modal-icon">⚠️</span>
                            <h2 class="modal-title">Désactiver la Licence</h2>
                            <p class="modal-description">Êtes-vous sûr de vouloir désactiver cette licence ?</p>
                            <ul class="modal-list">
                                <li>✓ Vous pouvez la réactiver plus tard</li>
                                <li>✓ Vous pourrez l'utiliser sur un autre site</li>
                                <li>✓ La licence restera valide jusqu'à son expiration</li>
                            </ul>
                            <aside class="modal-actions">
                                <button type="button" class="modal-btn cancel" onclick="closeDeactivateModal()">
                                    Annuler
                                </button>
                                <button type="button" class="modal-btn danger" onclick="deactivateLicense()">
                                    Désactiver
                                </button>
                            </aside>
                        </section>
                    </div>
                    <?php endif; ?>

                    <!-- JavaScript déplacé vers settings-main.php pour éviter les conflits -->

                    <!-- Informations utiles -->
                    <aside class="info-section">
                        <h4 class="info-title">Informations Utiles</h4>
                        <section class="info-cards">
                            <!-- Site actuel -->
                            <article class="info-card">
                                <span class="info-card-title">Site actuel</span>
                                <code class="info-card-code"><?php echo esc_html(home_url()); ?></code>
                            </article>

                            <!-- Plan actif -->
                            <article class="info-card">
                                <span class="info-card-title">Plan actif</span>
                                <span class="info-card-badge"><?php echo !empty($test_key) ? '🧪 Mode Test' : ($is_premium ? '⭐ Premium' : '○ Gratuit'); ?></span>
                            </article>

                            <!-- Version du plugin -->
                            <article class="info-card">
                                <span class="info-card-title">Version du plugin</span>
                                <span class="info-card-version"><?php echo defined('PDF_BUILDER_VERSION') ? PDF_BUILDER_VERSION : 'N/A'; ?></span>
                            </article>

                            <?php if ($is_premium) :
                                ?>
                            <!-- Support Premium -->
                            <article class="info-card">
                                <span class="info-card-title">Support</span>
                                <a href="https://pdfbuilderpro.com/support" target="_blank" class="info-card-link">Contact Support Premium →</a>
                            </article>

                            <!-- Documentation -->
                            <article class="info-card">
                                <span class="info-card-title">Documentation</span>
                                <a href="https://pdfbuilderpro.com/docs" target="_blank" class="info-card-link">Lire la Documentation →</a>
                            </article>
                                <?php
                            endif; ?>
                        </section>
                    </aside>

                    <!-- Comparaison des fonctionnalités -->
                    <section class="licence-section">
                        <h3>Comparaison des Fonctionnalités</h3>
                        <table class="features-table">
                            <thead class="features-header">
                                <tr>
                                    <th class="feature-name">Fonctionnalité</th>
                                    <th class="feature-free">Gratuit</th>
                                    <th class="feature-premium">Premium</th>
                                    <th class="feature-details">Détails</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Nombre de templates</strong></td>
                                    <td class="feature-limited">1 seul</td>
                                    <td class="feature-enabled">✓ Illimité</td>
                                    <td>Templates prédéfinis et personnalisés</td>
                                </tr>
                                <tr>
                                    <td><strong>Qualité d'impression</strong></td>
                                    <td class="feature-limited">72 DPI</td>
                                    <td class="feature-enabled">300 DPI</td>
                                    <td>Résolution haute qualité pour impression</td>
                                </tr>
                                <tr>
                                    <td><strong>Filigrane</strong></td>
                                    <td class="feature-disabled">✓ Présent</td>
                                    <td class="feature-enabled">✗ Supprimé</td>
                                    <td>Marque d'eau "PDF Builder Pro" sur tous les PDFs</td>
                                </tr>
                                <tr>
                                    <td><strong>Éléments de base</strong></td>
                                    <td class="feature-enabled">✓</td>
                                    <td class="feature-enabled">✓</td>
                                    <td>Texte, images, formes géométriques, lignes</td>
                                </tr>
                                <tr>
                                    <td><strong>Éléments avancés</strong></td>
                                    <td class="feature-disabled">✗</td>
                                    <td class="feature-enabled">✓</td>
                                    <td>Codes-barres, QR codes, graphiques, tableaux dynamiques</td>
                                </tr>
                                <tr class="feature-hidden">
                                    <td><strong>Variables WooCommerce</strong></td>
                                    <td class="feature-enabled">✓ Basique</td>
                                    <td class="feature-enabled">✓ Complet</td>
                                    <td>Commandes, clients, produits, métadonnées</td>
                                </tr>
                                <tr class="feature-hidden">
                                    <td><strong>Génération PDF</strong></td>
                                    <td class="feature-limited">50/mois</td>
                                    <td class="feature-enabled">Illimitée</td>
                                    <td>Limite mensuelle de génération de documents</td>
                                </tr>
                                <tr class="feature-hidden">
                                    <td><strong>Génération en masse</strong></td>
                                    <td class="feature-disabled">✗</td>
                                    <td class="feature-enabled">✓</td>
                                    <td>Création automatique de multiples PDFs</td>
                                </tr>
                                <tr class="feature-hidden">
                                    <td><strong>API développeur</strong></td>
                                    <td class="feature-disabled">✗</td>
                                    <td class="feature-enabled">✓</td>
                                    <td>Accès complet à l'API REST pour intégrations</td>
                                </tr>
                                <tr class="feature-hidden">
                                    <td><strong>White-label</strong></td>
                                    <td class="feature-disabled">✗</td>
                                    <td class="feature-enabled">✓</td>
                                    <td>Rebranding complet, suppression des mentions</td>
                                </tr>
                                <tr class="feature-hidden">
                                    <td><strong>Mises à jour automatiques</strong></td>
                                    <td class="feature-disabled">✗</td>
                                    <td class="feature-enabled">✓</td>
                                    <td>Mises à jour transparentes et corrections de sécurité</td>
                                </tr>
                                <tr class="feature-hidden">
                                    <td><strong>Formats d'export</strong></td>
                                    <td class="feature-limited">PDF uniquement</td>
                                    <td class="feature-enabled">PDF, PNG, JPG</td>
                                    <td>Export multi-formats pour différents usages</td>
                                </tr>
                                <tr class="feature-hidden">
                                    <td><strong>Fiabilité de génération</strong></td>
                                    <td class="feature-limited">Générateur unique</td>
                                    <td class="feature-enabled">3 générateurs redondants</td>
                                    <td>Fallback automatique en cas d'erreur</td>
                                </tr>
                                <tr class="feature-hidden">
                                    <td><strong>API REST</strong></td>
                                    <td class="feature-disabled">✗</td>
                                    <td class="feature-enabled">✓</td>
                                    <td>API complète pour intégrations et automatisations</td>
                                </tr>
                                <tr class="feature-hidden">
                                    <td><strong>Templates prédéfinis</strong></td>
                                    <td class="feature-limited">1 template de base</td>
                                    <td class="feature-enabled">4 templates professionnels</td>
                                    <td>Factures, devis, bons de commande prêts à l'emploi</td>
                                </tr>
                                <tr class="feature-hidden">
                                    <td><strong>CSS personnalisé</strong></td>
                                    <td class="feature-disabled">✗</td>
                                    <td class="feature-enabled">✓</td>
                                    <td>Injection de styles CSS avancés pour personnalisation complète</td>
                                </tr>
                                <tr class="feature-hidden">
                                    <td><strong>Intégrations tierces</strong></td>
                                    <td class="feature-disabled">✗</td>
                                    <td class="feature-enabled">✓</td>
                                    <td>Zapier, webhooks, API externes pour automatisation</td>
                                </tr>
                                <tr class="feature-hidden">
                                    <td><strong>Historique des versions</strong></td>
                                    <td class="feature-disabled">✗</td>
                                    <td class="feature-enabled">✓</td>
                                    <td>Suivi des modifications et possibilité de rollback</td>
                                </tr>
                                <tr class="feature-hidden">
                                    <td><strong>Analytics & rapports</strong></td>
                                    <td class="feature-disabled">✗</td>
                                    <td class="feature-enabled">✓</td>
                                    <td>Statistiques d'usage, performances et métriques détaillées</td>
                                </tr>
                                <tr class="feature-hidden">
                                    <td><strong>Support technique</strong></td>
                                    <td class="feature-limited">Communauté</td>
                                    <td class="feature-enabled">Prioritaire</td>
                                    <td>Support rapide par email avec réponse garantie sous 24h</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Bouton toggle pour voir plus/moins de fonctionnalités -->
                        <aside class="toggle-container">
                            <button type="button" id="toggle-features-btn" class="toggle-features-btn">
                                <span class="show-text">🔽 Voir plus de fonctionnalités (10 restantes)</span>
                                <span class="hide-text">🔼 Voir moins</span>
                            </button>
                        </aside>

                        <aside class="promo-section">
                            <!-- Élément décoratif animé -->
                            <span class="promo-decoration"></span>

                            <h4 class="promo-header">
                                <span class="promo-badge">💎 PREMIUM</span>
                                <strong>5 bonnes raisons de passer en Premium</strong>
                            </h4>

                            <section class="promo-grid">
                                <article class="promo-item">
                                    <span class="promo-icon">🏢</span>
                                    <section class="promo-content">
                                        <strong class="promo-title">Usage professionnel</strong>
                                        <p class="promo-description">Qualité 300 DPI sans filigrane</p>
                                    </section>
                                </article>

                                <article class="promo-item">
                                    <span class="promo-icon">⚡</span>
                                    <section class="promo-content">
                                        <strong class="promo-title">Productivité</strong>
                                        <p class="promo-description">Templates illimités et génération en masse</p>
                                    </section>
                                </article>

                                <article class="promo-item">
                                    <span class="promo-icon">🔧</span>
                                    <section class="promo-content">
                                        <strong class="promo-title">Évolutivité</strong>
                                        <p class="promo-description">API développeur complète</p>
                                    </section>
                                </article>

                                <article class="promo-item">
                                    <span class="promo-icon">🎯</span>
                                    <section class="promo-content">
                                        <strong class="promo-title">Support dédié</strong>
                                        <p class="promo-description">Réponse sous 24h garantie</p>
                                    </section>
                                </article>

                                <article class="promo-item full-width">
                                    <span class="promo-icon">💰</span>
                                    <section class="promo-content">
                                        <strong class="promo-title">Économique</strong>
                                        <p class="promo-description">79€ à vie vs coûts récurrents</p>
                                    </section>
                                </article>
                            </section>
                        </aside>
                    </section>

                    <!-- Section Rappel par Email -->
                    <section class="reminder-section">
                        <h3 class="reminder-title">📧 Rappels par Email</h3>

                        <p class="reminder-description">
                            Recevez des rappels automatiques par email concernant l'expiration de votre licence premium.
                        </p>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="license_email_reminders">Activer les rappels</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="license_email_reminders" name="license_email_reminders"
                                            value="1" <?php pdf_builder_safe_checked($license_email_reminders, '1'); ?> />
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Recevoir des rappels par email 30 jours, 7 jours et 1 jour avant l'expiration</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="license_reminder_email">Adresse email</label></th>
                                <td>
                                    <input type="email" id="license_reminder_email" name="license_reminder_email"
                                        value="<?php echo pdf_builder_safe_esc_attr($license_reminder_email); ?>"
                                        placeholder="votre@email.com" class="form-input" />
                                    <p class="description">Adresse email où envoyer les rappels d'expiration de licence</p>
                                </td>
                            </tr>
                        </table>

                        <aside class="reminder-info">
                            <h5>ℹ️ Informations sur les rappels</h5>
                            <ul>
                                <li>Les rappels sont envoyés automatiquement selon le calendrier ci-dessus</li>
                                <li>Vous recevrez au maximum 3 emails par période de licence</li>
                                <li>Les emails sont envoyés depuis votre propre serveur WordPress</li>
                                <li>Vous pouvez désactiver cette fonctionnalité à tout moment</li>
                            </ul>
                        </aside>
                    </section>


                    

            </section>

                    <!-- JavaScript AJAX déplacé vers settings-main.php pour éviter les conflits -->

