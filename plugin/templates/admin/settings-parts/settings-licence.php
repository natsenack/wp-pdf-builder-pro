<?php // Licence tab content - Updated: AJAX centralized 2025-12-02

?>
            <!-- Licence Settings Section (No Form - AJAX Centralized) -->
            <section id="licence" aria-label="Gestion de la Licence">

                <!-- Styles CSS pour l'interface ergonomique -->
                

                <?php
                    // Récupération des paramètres depuis le tableau unifié
                    $settings = pdf_builder_get_option('pdf_builder_settings', array());
                    error_log('[PDF Builder] settings-licence.php loaded - license_status: ' . ($settings['pdf_builder_license_status'] ?? 'not set') . ', settings count: ' . count($settings));

                    $license_status = $settings['pdf_builder_license_status'] ?? 'free';
                    // La clé est chiffrée en DB — on ne la lit plus directement
                    $license_key        = ''; // masquée ; utiliser getLicenseKeyForLinks() pour les URLs
                    $license_expires = $settings['pdf_builder_license_expires'] ?? '';
                    $license_activated_at = $settings['pdf_builder_license_activated_at'] ?? '';
                    $test_mode_enabled = $settings['pdf_builder_license_test_mode_enabled'] ?? '0';
                    $test_key = $settings['pdf_builder_license_test_key'] ?? '';
                    $test_key_expires = $settings['pdf_builder_license_test_key_expires'] ?? '';
                    $license_email_reminders = $settings['pdf_builder_license_email_reminders'] ?? '0';
                    $license_reminder_email = $settings['pdf_builder_license_reminder_email'] ?? get_option('admin_email', '');

                    // Utiliser la méthode centralisée du License Manager pour déterminer si premium
                    $license_manager = \PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance();
                    $is_premium = $license_manager->isPremium();
                    // Clé en clair (admin uniquement) et ID EDD pour les liens de gestion
                    $edd_license_key = $is_premium ? $license_manager->getLicenseKeyForLinks() : '';
                    $edd_license_id  = $is_premium ? $license_manager->getLicenseId()          : '';
                    // Clé masquée pour l'affichage dans le champ (5 premiers caractères + points)
                    $license_key_masked = (!empty($edd_license_key))
                        ? substr($edd_license_key, 0, 5) . str_repeat('•', 18)
                        : '';
                    $is_test_mode = $test_mode_enabled === '1';

                    // Données détaillées EDD (customer, expiration, activations…)
                    $lic_data        = $is_premium ? pdf_builder_get_option('pdf_builder_license_data', []) : [];
                    $lic_expires_raw = $lic_data['expires_raw']
                                        ?? pdf_builder_get_option('pdf_builder_license_expires', $license_expires);
                    $lic_expires_ts  = !empty($lic_data['expires']) ? (int)$lic_data['expires']
                                        : (!empty($lic_expires_raw) && $lic_expires_raw !== 'lifetime' ? strtotime($lic_expires_raw) : 0);
                    $lic_is_lifetime = ($lic_expires_raw === 'lifetime');
                    $lic_days_left   = (!$lic_is_lifetime && $lic_expires_ts > 0)
                                        ? max(0, (int)(($lic_expires_ts - time()) / 86400)) : null;
                    $lic_customer    = $lic_data['customer'] ?? '';
                    $lic_email       = $lic_data['email']    ?? '';
                    $lic_activations = isset($lic_data['activations']) ? (int)$lic_data['activations'] : null;

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

                    // ✅ DÉSACTIVATION VIA AJAX (voir bootstrap.php: wp_ajax_pdf_builder_deactivate_license)
                    // L'ancien code POST est supprimé - utilisez confirmDeactivateLicense() pour l'AJAX
                ?>

                <!-- Header avec titre et actions principales -->
                <div class="pdfb-license-header">
                    <div class="pdfb-pdfb-license-header-content">
                        <div class="pdfb-pdfb-license-header-top">
                            <h2 class="license-main-title">
                                <span class="pdfb-license-icon">🔐</span>
                                Gestion de la Licence
                            </h2>
                            <!-- Badge Statut Licence -->
                            <span class="pdfb-license-status-badge <?php echo $is_premium ? 'badge-premium' : 'badge-free'; ?>">
                                <?php echo $is_premium ? '⭐ Premium' : '○ Version Gratuite'; ?>
                            </span>
                        </div>
                        <p class="pdfb-license-subtitle">Gérez votre licence PDF Builder Pro et accédez aux fonctionnalités premium</p>
                    </div>

                    <!-- Actions rapides -->
                    <div class="pdfb-license-quick-actions">
                        <?php if (!$is_premium): ?>
                            <a href="https://hub.threeaxe.fr/index.php/downloads/pdf-builder-pro/" class="pdfb-pdfb-license-btn-primary-large">
                                <span class="pdfb-license-btn-icon">🚀</span>
                                Activer Premium
                            </a>
                        <?php else: ?>
                            <button type="button" class="pdfb-pdfb-license-btn-secondary-large" onclick="showDeactivateModal()">
                                <span class="pdfb-license-btn-icon">🔓</span>
                                Désactiver
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Dashboard de statut principal -->
                <div class="pdfb-license-dashboard">

                    <!-- Alertes importantes -->
                    <?php if ($is_premium && !empty($license_expires)): ?>
                        <?php
                        $now = new DateTime();
                        $expires = new DateTime($license_expires);
                        $diff = $now->diff($expires);
                        $days_left = $diff->invert ? -$diff->days : $diff->days;

                        if ($days_left <= 30 && $days_left > 0):
                        ?>
                        <div class="pdfb-license-alert-card warning">
                            <div class="pdfb-license-alert-icon">⏰</div>
                            <div class="pdfb-license-alert-content">
                                <h4>Expiration imminente</h4>
                                <p>Votre licence expire dans <strong><?php echo $days_left; ?> jour<?php echo $days_left > 1 ? 's' : ''; ?></strong></p>
                                <p class="pdfb-license-alert-date">Le <?php echo date('d/m/Y', strtotime($license_expires)); ?></p>
                            </div>
                            <div class="pdfb-license-alert-actions">
                                <a href="#renewal" class="pdfb-license-btn-small">Renouveler</a>
                            </div>
                        </div>
                        <?php elseif ($diff->invert): ?>
                        <div class="pdfb-license-alert-card error">
                            <div class="pdfb-license-alert-icon">❌</div>
                            <div class="pdfb-license-alert-content">
                                <h4>Licence expirée</h4>
                                <p>Votre licence a expiré il y a <?php echo abs($days_left); ?> jour<?php echo abs($days_left) > 1 ? 's' : ''; ?></p>
                                <p class="pdfb-license-alert-date">Le <?php echo date('d/m/Y', strtotime($license_expires)); ?></p>
                            </div>
                            <div class="pdfb-license-alert-actions">
                                <a href="#renewal" class="pdfb-license-btn-small primary">Renouveler maintenant</a>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>

                </div>

                <!-- Section d'actions principales -->
                <div class="pdfb-license-actions-section">

                    <!-- Activation de licence -->
                    <div id="activate-section" class="pdfb-license-action-card">
                        <div class="pdfb-pdfb-license-action-card-header">
                            <h3>
                                <span class="pdfb-license-action-icon">🔑</span>
                                <?php echo $is_premium ? 'Changer de Licence' : 'Activer une Licence'; ?>
                            </h3>
                            <p><?php echo $is_premium ? 'Remplacer votre licence actuelle' : 'Débloquer toutes les fonctionnalités premium'; ?></p>
                        </div>

                        <div class="pdfb-license-action-card-content">
                            <div class="pdfb-pdfb-license-input-group">
                                <label for="license_key_input">Clé de licence</label>
                                <div class="pdfb-pdfb-license-input-with-button">
                                    <input type="text"
                                           id="license_key_input"
                                           placeholder="<?php echo $is_premium && !empty($license_key_masked) ? esc_attr($license_key_masked) : 'Entrez votre clé de licence premium'; ?>"
                                           class="pdfb-license-input">
                                    <button type="button" class="pdfb-license-btn-primary" id="activate-license-btn">
                                        <span class="pdfb-license-btn-text"><?php echo $is_premium ? 'Changer' : 'Activer'; ?></span>
                                        <span class="pdfb-license-btn-icon">✓</span>
                                    </button>
                                </div>
                                <p class="pdfb-pdfb-license-input-help">
                                    <?php if (!$is_premium): ?>
                                        Vous n'avez pas de clé ? <a href="https://hub.threeaxe.fr/index.php/downloads/pdf-builder-pro/" class="pdfb-license-link-primary">Obtenir une licence premium</a>
                                    <?php else: ?>
                                        Une nouvelle clé remplacera l'actuelle
                                    <?php endif; ?>
                                </p>
                                <?php if ($is_premium && !empty($edd_license_key)): ?>
                                <div class="license-management-links" style="display:flex;gap:.75rem;margin-top:1.25rem;flex-wrap:wrap;">
                                    <a href="<?php echo esc_url('https://hub.threeaxe.fr/index.php/checkout/?edd_license_key=' . urlencode($edd_license_key) . '&download_id=19'); ?>"
                                       target="_blank" rel="noopener noreferrer"
                                       style="display:inline-flex;align-items:center;gap:.45rem;padding:.6rem 1.1rem;border-radius:8px;background:#f0f7ff;color:#2271b1;border:1px solid #c3d9f5;font-weight:600;font-size:.9rem;text-decoration:none;">
                                        🔄 Renouveler la licence
                                    </a>
                                    <a href="<?php echo esc_url('https://hub.threeaxe.fr?edd_action=license_unsubscribe' . (!empty($edd_license_id) ? '&license_id=' . urlencode($edd_license_id) : '') . '&license_key=' . urlencode($edd_license_key)); ?>"
                                       target="_blank" rel="noopener noreferrer"
                                       onclick="return confirm('Êtes-vous sûr de vouloir vous désabonner ?')"
                                       style="display:inline-flex;align-items:center;gap:.45rem;padding:.6rem 1.1rem;border-radius:8px;background:#fff5f5;color:#cc1818;border:1px solid #f5c3c3;font-weight:600;font-size:.9rem;text-decoration:none;">
                                        ❌ Se désabonner
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Support & Avantages Premium -->
                    <?php if ($is_premium || $is_test_mode): ?>
                    <div class="pdfb-license-action-card pdfb-pdfb-license-premium-support" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 12px; box-shadow: 0 8px 25px rgba(79, 172, 254, 0.3);">
                        <div class="pdfb-pdfb-license-action-card-header" style="padding: 1rem; text-align: center;">
                            <div style="font-size: 2.5rem; margin-bottom: 0.5rem; animation: supportPulse 2s ease-in-out infinite;">💎</div>
                            <h3 style="margin: 0 0 0.5rem 0; color: white; font-size: 1.2rem; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">Support Premium & Avantages</h3>
                            <p style="margin: 0 0 1rem 0; color: rgba(255,255,255,0.9); font-size: 0.9rem; line-height: 1.4;">
                                Découvrez tous les avantages de votre licence premium
                            </p>
                        </div>

                        <div class="pdfb-license-action-card-content" style="padding: 0 1rem 1rem 1rem;">
                            <div class="license-premium-pdfb-features-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem; margin-bottom: 1rem;">
                                <div class="pdfb-pdfb-license-feature-item" style="background: rgba(255,255,255,0.1); padding: 0.8rem; border-radius: 8px; text-align: center; backdrop-filter: blur(10px);">
                                    <div style="font-size: 1.5rem; margin-bottom: 0.3rem;">📊</div>
                                    <div style="font-size: 0.8rem; color: white; font-weight: 500;">Exports Multi-Formats</div>
                                </div>
                                <div class="pdfb-pdfb-license-feature-item" style="background: rgba(255,255,255,0.1); padding: 0.8rem; border-radius: 8px; text-align: center; backdrop-filter: blur(10px);">
                                    <div style="font-size: 1.5rem; margin-bottom: 0.3rem;">🎯</div>
                                    <div style="font-size: 0.8rem; color: white; font-weight: 500;">Navigation Grille</div>
                                </div>
                                <div class="pdfb-pdfb-license-feature-item" style="background: rgba(255,255,255,0.1); padding: 0.8rem; border-radius: 8px; text-align: center; backdrop-filter: blur(10px);">
                                    <div style="font-size: 1.5rem; margin-bottom: 0.3rem;">⚡</div>
                                    <div style="font-size: 0.8rem; color: white; font-weight: 500;">Résolutions Élevées</div>
                                </div>
                                <div class="pdfb-pdfb-license-feature-item" style="background: rgba(255,255,255,0.1); padding: 0.8rem; border-radius: 8px; text-align: center; backdrop-filter: blur(10px);">
                                    <div style="font-size: 1.5rem; margin-bottom: 0.3rem;">🔧</div>
                                    <div style="font-size: 0.8rem; color: white; font-weight: 500;">Outils Avancés</div>
                                </div>
                            </div>

                            <div class="pdfb-license-support-actions" style="display: flex; gap: 0.5rem; justify-content: center;">
                                <a href="https://wp-pdf-builder.com/support" target="_blank" class="pdfb-license-btn-secondary" style="flex: 1; text-align: center; padding: 0.6rem; background: rgba(255,255,255,0.2); color: white; text-decoration: none; border-radius: 20px; font-size: 0.8rem; backdrop-filter: blur(10px);">
                                    <span style="display: block; font-weight: 500;">📞 Support</span>
                                </a>
                                <a href="https://wp-pdf-builder.com/docs" target="_blank" class="pdfb-license-btn-secondary" style="flex: 1; text-align: center; padding: 0.6rem; background: rgba(255,255,255,0.2); color: white; text-decoration: none; border-radius: 20px; font-size: 0.8rem; backdrop-filter: blur(10px);">
                                    <span style="display: block; font-weight: 500;">📚 Docs</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    
                    <?php else: ?>
                    <!-- Section publicité premium -->
                    <div class="pdfb-license-action-card pdfb-pdfb-license-premium-promo" style="max-height: 200px; overflow: hidden; position: relative; background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%); background-size: 200% 200%; animation: gradientShift 3s ease infinite; border-radius: 12px; box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);">
                        <div class="pdfb-pdfb-pdfb-license-premium-promo-content" style="padding: 1rem; text-align: center; position: relative; z-index: 2;">
                            <div style="font-size: 2.5rem; margin-bottom: 0.5rem; animation: rocketFloat 2s ease-in-out infinite; display: inline-block;">🚀</div>
                            <h3 style="margin: 0 0 0.5rem 0; color: white; font-size: 1.2rem; text-shadow: 0 2px 4px rgba(0,0,0,0.3); animation: textGlow 2s ease-in-out infinite alternate;">Découvrez la Version Premium</h3>
                            <p style="margin: 0 0 1rem 0; color: rgba(255,255,255,0.9); font-size: 0.9rem; line-height: 1.4; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                                Débloquez toutes les fonctionnalités avancées : exports multi-formats, résolutions élevées, navigation grille, et bien plus !
                            </p>
                            <a href="#" onclick="showUpgradeModal('license_tab')" class="pdfb-license-btn-primary pdfb-pdfb-license-premium-cta" style="display: inline-block; padding: 0.6rem 1.2rem; background: linear-gradient(45deg, #ff6b6b, #ffa500); color: white; text-decoration: none; border-radius: 25px; font-weight: 600; font-size: 0.9rem; box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4); transition: all 0.3s ease; position: relative; overflow: hidden;">
                                <span style="position: relative; z-index: 2;">✨ Passer en Premium</span>
                                <div style="position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent); transition: left 0.5s ease; z-index: 1;"></div>
                            </a>
                        </div>
                        <!-- Particules animées en arrière-plan -->
                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; overflow: hidden; z-index: 1;">
                            <div style="position: absolute; width: 4px; height: 4px; background: rgba(255,255,255,0.6); border-radius: 50%; top: 20%; left: 10%; animation: particleFloat 4s ease-in-out infinite;"></div>
                            <div style="position: absolute; width: 6px; height: 6px; background: rgba(255,255,255,0.4); border-radius: 50%; top: 60%; left: 80%; animation: particleFloat 5s ease-in-out infinite reverse;"></div>
                            <div style="position: absolute; width: 3px; height: 3px; background: rgba(255,255,255,0.5); border-radius: 50%; top: 40%; left: 60%; animation: particleFloat 3s ease-in-out infinite;"></div>
                        </div>
                    </div>

                    
                    <?php endif; ?>

                </div>

                <!-- Section informations détaillées -->
                <?php if ($is_premium || !empty($test_key)): ?>
                <div class="pdfb-license-details-section">
                    <button type="button"
                            class="pdfb-expand-toggle"
                            aria-expanded="false"
                            aria-controls="pdfb-details-body"
                            onclick="pdfbToggleExpand(this,'pdfb-details-body')"
                            style="width:100%;display:flex;justify-content:space-between;align-items:center;background:#f0f4ff;border:1px solid #d0d9f5;border-radius:8px;padding:.55rem .9rem;cursor:pointer;font-size:.88rem;font-weight:600;color:#2c3e80;margin-bottom:0;">
                        <span>ℹ️ Informations détaillées</span>
                        <span class="pdfb-chevron" style="transition:transform .25s;">&#9660;</span>
                    </button>

                    <div id="pdfb-details-body" style="display:none;margin-top:.5rem;">
                    <div class="pdfb-license-details-grid">

                        <!-- Statut -->
                        <div class="pdfb-license-detail-card">
                            <h4>Statut</h4>
                            <p class="pdfb-license-detail-value">
                                <?php if (!empty($test_key)): ?>
                                    <span class="pdfb-license-status-badge test">🧪 Mode Test</span>
                                <?php elseif ($is_premium): ?>
                                    <span class="pdfb-license-status-badge active">✅ Premium</span>
                                <?php else: ?>
                                    <span class="pdfb-license-status-badge free">○ Gratuit</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <!-- Site -->
                        <div class="pdfb-license-detail-card">
                            <h4>Site actuel</h4>
                            <p class="pdfb-license-detail-value"><?php echo esc_html(home_url()); ?></p>
                        </div>

                        <!-- Clé Premium (masquée) -->
                        <?php if (!empty($edd_license_key)): ?>
                        <div class="pdfb-license-detail-card">
                            <h4>Clé de licence</h4>
                            <p class="pdfb-license-detail-value pdfb-license-key" style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                                <code><?php echo esc_html(substr($edd_license_key, 0, 5) . str_repeat('•', 18)); ?></code>
                                <button type="button" class="pdfb-license-copy-btn"
                                        onclick="navigator.clipboard.writeText('<?php echo esc_js($edd_license_key); ?>').then(function(){ this.textContent='✅'; }.bind(this))"
                                        title="Copier la clé">
                                    📋
                                </button>
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Expiration -->
                        <?php if ($is_premium): ?>
                        <div class="pdfb-license-detail-card">
                            <h4>Expiration</h4>
                            <p class="pdfb-license-detail-value">
                                <?php if ($lic_is_lifetime): ?>
                                    <span style="color:#00a32a;font-weight:600;">♾️ À vie</span>
                                <?php elseif ($lic_expires_ts > 0): ?>
                                    <?php
                                        $exp_date = wp_date(get_option('date_format', 'd/m/Y'), $lic_expires_ts);
                                        echo esc_html($exp_date);
                                    ?>
                                <?php else: ?>
                                    <span style="color:#999;">—</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <!-- Jours restants -->
                        <div class="pdfb-license-detail-card">
                            <h4>Jours restants</h4>
                            <p class="pdfb-license-detail-value">
                                <?php if ($lic_is_lifetime): ?>
                                    <span style="color:#00a32a;font-weight:600;">∞</span>
                                <?php elseif ($lic_days_left !== null): ?>
                                    <?php
                                        $color = $lic_days_left > 60 ? '#00a32a' : ($lic_days_left > 14 ? '#d97c00' : '#cc1818');
                                        echo '<span style="color:' . $color . ';font-weight:600;font-size:1.1rem;">' . (int)$lic_days_left . ' j</span>';
                                    ?>
                                <?php else: ?>
                                    <span style="color:#999;">—</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Client -->
                        <?php if (!empty($lic_customer)): ?>
                        <div class="pdfb-license-detail-card">
                            <h4>Titulaire</h4>
                            <p class="pdfb-license-detail-value"><?php echo esc_html($lic_customer); ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Email -->
                        <?php if (!empty($lic_email)): ?>
                        <div class="pdfb-license-detail-card">
                            <h4>Email</h4>
                            <p class="pdfb-license-detail-value"><?php echo esc_html($lic_email); ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Activations restantes -->
                        <?php if ($is_premium && $lic_activations !== null): ?>
                        <div class="pdfb-license-detail-card">
                            <h4>Activations restantes</h4>
                            <p class="pdfb-license-detail-value">
                                <span style="font-weight:600;"><?php echo (int)$lic_activations; ?></span>
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Clé de Test -->
                        <?php if (!empty($test_key)): ?>
                        <div class="pdfb-license-detail-card">
                            <h4>Clé de Test</h4>
                            <p class="pdfb-license-detail-value pdfb-license-pdfb-test-key">
                                <code><?php echo esc_html(substr($test_key, 0, 8) . '••••••••••••' . substr($test_key, -8)); ?></code>
                                <span class="test-badge">TEST</span>
                            </p>
                        </div>
                        <?php endif; ?>

                    </div>
                    </div><!-- /#pdfb-details-body -->
                </div>
                <?php endif; ?>

                <!-- Comparatif Gratuit vs Premium -->
                <div class="premium-features-section">
                    <h3 class="pdfb-section-title" style="font-size:1rem;">
                        <span class="pdfb-section-icon">⭐</span>
                        Gratuit vs Premium
                    </h3>

                    <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
                        <thead>
                            <tr style="background:#f0f4ff;">
                                <th style="text-align:left;padding:.5rem .75rem;border:1px solid #dde3f5;font-weight:600;color:#333;">Fonctionnalité</th>
                                <th style="text-align:center;padding:.5rem .75rem;border:1px solid #dde3f5;color:#6c757d;font-weight:600;">Gratuit</th>
                                <th style="text-align:center;padding:.5rem .75rem;border:1px solid #dde3f5;color:#2271b1;font-weight:600;">Premium</th>
                            </tr>
                        </thead>
                        <tbody id="pdfb-compare-visible">
                            <tr>
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">Templates prédéfinis</td>
                                <td style="text-align:center;border:1px solid #eee;">4</td>
                                <td style="text-align:center;border:1px solid #eee;">∞</td>
                            </tr>
                            <tr style="background:#fafafa;">
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">Éléments de base (texte, image, formes)</td>
                                <td style="text-align:center;border:1px solid #eee;">✅</td>
                                <td style="text-align:center;border:1px solid #eee;">✅</td>
                            </tr>
                            <tr>
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">Génération PDF mensuelle</td>
                                <td style="text-align:center;border:1px solid #eee;">50</td>
                                <td style="text-align:center;border:1px solid #eee;">∞</td>
                            </tr>
                            <tr style="background:#fafafa;">
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">Qualité d'export (DPI)</td>
                                <td style="text-align:center;border:1px solid #eee;">72 DPI</td>
                                <td style="text-align:center;border:1px solid #eee;">300/600 DPI</td>
                            </tr>
                            <tr>
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">Watermark sur PDFs</td>
                                <td style="text-align:center;border:1px solid #eee;"><span style="color:#cc1818;">✓</span></td>
                                <td style="text-align:center;border:1px solid #eee;"><span style="color:#bbb;">—</span></td>
                            </tr>
                            <tr style="background:#fafafa;">
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">Support</td>
                                <td style="text-align:center;border:1px solid #eee;">Communauté</td>
                                <td style="text-align:center;border:1px solid #eee;">Prioritaire 24/7</td>
                            </tr>
                        </tbody>
                        <tbody id="pdfb-compare-hidden" style="display:none;">
                            <tr>
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">Éléments premium (codes-barres, QR, graphiques)</td>
                                <td style="text-align:center;border:1px solid #eee;"><span style="color:#bbb;">—</span></td>
                                <td style="text-align:center;border:1px solid #eee;">✅</td>
                            </tr>
                            <tr style="background:#fafafa;">
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">Signatures et champs</td>
                                <td style="text-align:center;border:1px solid #eee;"><span style="color:#bbb;">—</span></td>
                                <td style="text-align:center;border:1px solid #eee;">✅</td>
                            </tr>
                            <tr>
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">Exports multi-format (PNG, JPG, SVG)</td>
                                <td style="text-align:center;border:1px solid #eee;"><span style="color:#bbb;">—</span></td>
                                <td style="text-align:center;border:1px solid #eee;">✅</td>
                            </tr>
                            <tr style="background:#fafafa;">
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">Génération en masse (batch)</td>
                                <td style="text-align:center;border:1px solid #eee;"><span style="color:#bbb;">—</span></td>
                                <td style="text-align:center;border:1px solid #eee;">✅</td>
                            </tr>
                            <tr>
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">Navigation grille et guides magnétiques</td>
                                <td style="text-align:center;border:1px solid #eee;"><span style="color:#bbb;">—</span></td>
                                <td style="text-align:center;border:1px solid #eee;">✅</td>
                            </tr>
                            <tr style="background:#fafafa;">
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">Modes de sélection avancés (multiple, groupe)</td>
                                <td style="text-align:center;border:1px solid #eee;"><span style="color:#bbb;">—</span></td>
                                <td style="text-align:center;border:1px solid #eee;">✅</td>
                            </tr>
                            <tr>
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">Raccourcis clavier</td>
                                <td style="text-align:center;border:1px solid #eee;"><span style="color:#bbb;">—</span></td>
                                <td style="text-align:center;border:1px solid #eee;">✅</td>
                            </tr>
                            <tr style="background:#fafafa;">
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">Formats étendus (A3, Letter, Legal, Étiquettes)</td>
                                <td style="text-align:center;border:1px solid #eee;"><span style="color:#bbb;">—</span></td>
                                <td style="text-align:center;border:1px solid #eee;">✅</td>
                            </tr>
                            <tr>
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">Couleurs personnalisées avancées</td>
                                <td style="text-align:center;border:1px solid #eee;"><span style="color:#bbb;">—</span></td>
                                <td style="text-align:center;border:1px solid #eee;">✅</td>
                            </tr>
                            <tr style="background:#fafafa;">
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">Variables conditionnelles</td>
                                <td style="text-align:center;border:1px solid #eee;"><span style="color:#bbb;">—</span></td>
                                <td style="text-align:center;border:1px solid #eee;">✅</td>
                            </tr>
                            <tr>
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">Intégration WooCommerce complète</td>
                                <td style="text-align:center;border:1px solid #eee;">Basique</td>
                                <td style="text-align:center;border:1px solid #eee;">Complète</td>
                            </tr>
                            <tr style="background:#fafafa;">
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">API développeur REST</td>
                                <td style="text-align:center;border:1px solid #eee;"><span style="color:#bbb;">—</span></td>
                                <td style="text-align:center;border:1px solid #eee;">✅</td>
                            </tr>
                            <tr>
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">Analytics et rapports détaillés</td>
                                <td style="text-align:center;border:1px solid #eee;"><span style="color:#bbb;">—</span></td>
                                <td style="text-align:center;border:1px solid #eee;">✅</td>
                            </tr>
                            <tr style="background:#fafafa;">
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">White-label / Rebranding</td>
                                <td style="text-align:center;border:1px solid #eee;"><span style="color:#bbb;">—</span></td>
                                <td style="text-align:center;border:1px solid #eee;">✅</td>
                            </tr>
                            <tr>
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">Métadonnées PDF (auteur, sujet...)</td>
                                <td style="text-align:center;border:1px solid #eee;"><span style="color:#bbb;">—</span></td>
                                <td style="text-align:center;border:1px solid #eee;">✅</td>
                            </tr>
                            <tr style="background:#fafafa;">
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">Optimisation impression</td>
                                <td style="text-align:center;border:1px solid #eee;"><span style="color:#bbb;">—</span></td>
                                <td style="text-align:center;border:1px solid #eee;">✅</td>
                            </tr>
                            <tr>
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">Sauvegardes et versioning</td>
                                <td style="text-align:center;border:1px solid #eee;"><span style="color:#bbb;">—</span></td>
                                <td style="text-align:center;border:1px solid #eee;">✅</td>
                            </tr>
                            <tr style="background:#fafafa;">
                                <td style="padding:.45rem .75rem;border:1px solid #eee;">Mises à jour gratuites à vie</td>
                                <td style="text-align:center;border:1px solid #eee;"><span style="color:#bbb;">—</span></td>
                                <td style="text-align:center;border:1px solid #eee;">✅</td>
                            </tr>
                        </tbody>
                    </table>

                    <button type="button"
                            id="pdfb-compare-btn"
                            onclick="pdfbToggleExpand(this,'pdfb-compare-hidden')"
                            aria-expanded="false"
                            style="margin-top:.6rem;background:none;border:1px solid #d0d9f5;border-radius:6px;padding:.35rem .9rem;font-size:.8rem;color:#2271b1;cursor:pointer;display:flex;width:100%;justify-content:center;align-items:center;gap:.4rem;">
                        <span>Voir plus de fonctionnalités</span>
                        <span class="pdfb-chevron" style="transition:transform .25s;">&#9660;</span>
                    </button>

                    <?php if (!$is_premium): ?>
                    <div class="pdfb-license-upgrade-prompt" style="margin-top:1rem;">
                        <h4>Prêt à passer au premium ?</h4>
                        <p>Débloquez toutes ces fonctionnalités et bien plus encore</p>
                        <a href="https://hub.threeaxe.fr/index.php/downloads/pdf-builder-pro/" class="pdfb-pdfb-license-btn-primary-large">
                            <span class="pdfb-license-btn-icon">🚀</span>
                            Activer maintenant
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

            </section>

            <!-- Section Rappels par Email pour Expiration -->
            <section id="email-reminders" aria-label="Rappels par Email" style="margin-top: 2rem; padding: 2rem; background: #f8f9fa; border-radius: 12px; border: 1px solid #e9ecef;">
                <h3 style="margin-top: 0; margin-bottom: 1.5rem; color: #495057; display: flex; align-items: center;">
                    <span style="margin-right: 0.5rem;">📧</span>
                    Rappels par Email pour l'Expiration
                </h3>

                <p style="margin-bottom: 1.5rem; color: #6c757d; line-height: 1.6;">
                    Recevez des notifications par email avant l'expiration de votre licence pour éviter toute interruption de service.
                </p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start;">
                    <!-- Activation des rappels -->
                    <div>
                        <label for="license_email_reminders" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #495057;">
                            <input type="checkbox"
                                   id="license_email_reminders"
                                   name="pdf_builder_settings[pdf_builder_license_email_reminders]"
                                   value="1"
                                   <?php checked($license_email_reminders, '1'); ?>
                                   style="margin-right: 0.5rem;">
                            Activer les rappels par email
                        </label>
                        <p style="margin: 0; font-size: 0.9rem; color: #6c757d;">
                            Recevoir des notifications 30 et 7 jours avant l'expiration.
                        </p>
                    </div>

                    <!-- Adresse email -->
                    <div>
                        <label for="license_reminder_email" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #495057;">
                            Adresse email pour les rappels
                        </label>
                        <input type="email"
                               id="license_reminder_email"
                               name="pdf_builder_settings[pdf_builder_license_reminder_email]"
                               value="<?php echo esc_attr($license_reminder_email); ?>"
                               placeholder="<?php echo esc_attr(get_option('admin_email', '')); ?>"
                               style="width: 100%; padding: 0.75rem; border: 1px solid #ced4da; border-radius: 6px; font-size: 1rem;">
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: #6c757d;">
                            Laissez vide pour utiliser l'email administrateur du site.
                        </p>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.8rem; color: #8e8e8e; font-style: italic;">
                            🔒 RGPD : Cette adresse ne sera utilisée que pour les rappels de licence. Vous pouvez la supprimer à tout moment en décochant la case ci-dessus.
                        </p>
                    </div>
                </div>

                <!-- Bouton de sauvegarde -->
                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e9ecef; text-align: center;">
                    <p style="margin: 0; font-size: 0.9rem; color: #6c757d;">
                        💡 Utilisez le bouton "Enregistrer" flottant en bas de la page pour sauvegarder vos paramètres.
                    </p>
                </div>

                <!-- JavaScript pour la gestion AJAX déplacé vers settings-main.php pour éviter les conflits -->
            </section>

                    <!-- JavaScript AJAX déplacé vers settings-main.php pour éviter les conflits -->
                    <script type="text/javascript">
                        // Nonces de licence
                        window.pdfBuilderLicense = window.pdfBuilderLicense || {};
                        window.pdfBuilderLicense.deactivateNonce = '<?php echo wp_create_nonce("pdf_builder_deactivate"); ?>';
                        window.pdfBuilderLicense.ajaxNonce      = '<?php echo wp_create_nonce("pdf_builder_ajax"); ?>';
                        window.pdfBuilderLicense.ajaxUrl         = '<?php echo admin_url("admin-ajax.php"); ?>';

                        // ── Expand / Collapse générique ─────────────────────────────────────
                        function pdfbToggleExpand(btn, targetId) {
                            var target   = document.getElementById(targetId);
                            var chevron  = btn.querySelector('.pdfb-chevron');
                            var expanded = btn.getAttribute('aria-expanded') === 'true';
                            if (!target) return;

                            if (expanded) {
                                target.style.display = 'none';
                                btn.setAttribute('aria-expanded', 'false');
                                if (chevron) chevron.style.transform = '';
                                // Mise à jour du libellé pour le comparatif
                                var label = btn.querySelector('span:first-child');
                                if (label && label.textContent.indexOf('Moins') !== -1) {
                                    label.textContent = 'Voir plus de fonctionnalités';
                                }
                            } else {
                                target.style.display = '';
                                btn.setAttribute('aria-expanded', 'true');
                                if (chevron) chevron.style.transform = 'rotate(180deg)';
                                var label = btn.querySelector('span:first-child');
                                if (label && label.textContent.indexOf('Voir plus') !== -1) {
                                    label.textContent = 'Voir moins';
                                }
                            }
                        }

                        // ── Validation et activation de licence (EDD) ──────────────────────────
                        (function() {
                            var btn   = document.getElementById('activate-license-btn');
                            var input = document.getElementById('license_key_input');
                            if (!btn || !input) return;

                            // Format attendu par EDD Software Licensing : 32 hex lowercase
                            var EDD_REGEX = /^[a-f0-9]{32}$/i;

                            // Zone de notification sous l'input
                            var notice = document.createElement('p');
                            notice.id  = 'license-key-notice';
                            notice.style.cssText = 'margin:6px 0 0; font-size:13px; display:none;';
                            input.parentNode.parentNode.insertBefore(notice, input.parentNode.nextSibling);

                            function showNotice(msg, type) {
                                notice.textContent = msg;
                                notice.style.display  = 'block';
                                notice.style.color    = type === 'error'   ? '#cc1818' :
                                                        type === 'success' ? '#1a7e2e' : '#888';
                                notice.style.fontWeight = type === 'loading' ? 'normal' : '600';
                            }

                            function hideNotice() {
                                notice.style.display = 'none';
                            }

                            // Validation en temps réel pendant la frappe
                            input.addEventListener('input', function() {
                                var val = this.value.trim();
                                if (!val) { hideNotice(); return; }
                                if (!EDD_REGEX.test(val)) {
                                    showNotice('⚠ Format invalide — une clé EDD comporte 32 caractères hexadécimaux (0-9, a-f).', 'error');
                                    btn.disabled = true;
                                } else {
                                    showNotice('✓ Format valide.', 'success');
                                    btn.disabled = false;
                                }
                            });

                            // Soumission AJAX
                            btn.addEventListener('click', function() {
                                var key = input.value.trim();

                                if (!key) {
                                    showNotice('⚠ Veuillez saisir votre clé de licence.', 'error');
                                    input.focus();
                                    return;
                                }

                                if (!EDD_REGEX.test(key)) {
                                    showNotice('⚠ Format invalide — une clé EDD comporte 32 caractères hexadécimaux (0-9, a-f).', 'error');
                                    input.focus();
                                    return;
                                }

                                // État chargement
                                btn.disabled = true;
                                btn.querySelector('.btn-text').textContent = 'Activation…';
                                showNotice('⏳ Vérification auprès du serveur de licences…', 'loading');

                                var formData = new FormData();
                                formData.append('action',      'pdf_builder_activate_license');
                                formData.append('nonce',       window.pdfBuilderLicense.ajaxNonce);
                                formData.append('license_key', key);

                                fetch(window.pdfBuilderLicense.ajaxUrl, {
                                    method: 'POST',
                                    body:   formData,
                                })
                                .then(function(r) { return r.json(); })
                                .then(function(data) {
                                    if (data.success) {
                                        showNotice('✓ ' + data.data.message, 'success');
                                        // Recharger la page après un court délai
                                        setTimeout(function() { window.location.reload(); }, 1500);
                                    } else {
                                        showNotice('✗ ' + (data.data && data.data.message ? data.data.message : 'Erreur inconnue'), 'error');
                                        btn.disabled = false;
                                        btn.querySelector('.btn-text').textContent = '<?php echo $is_premium ? 'Changer' : 'Activer'; ?>';
                                    }
                                })
                                .catch(function(err) {
                                    showNotice('✗ Erreur réseau : ' + err.message, 'error');
                                    btn.disabled = false;
                                    btn.querySelector('.btn-text').textContent = '<?php echo $is_premium ? 'Changer' : 'Activer'; ?>';
                                });
                            });
                        })();

                        // Fonctions JavaScript inline pour les modals de licence
                        function showDeactivateModal() {
                            if (!document.getElementById('deactivate-modal-overlay')) {
                                var modalHTML = `
                                    <div id="deactivate-modal-overlay" class="pdfb-canvas-modal-overlay" style="display: flex; z-index: 10002;">
                                        <div class="pdfb-canvas-modal-container" style="max-width: 450px;">
                                            <div class="pdfb-canvas-modal-header">
                                                <h3>⚠️ Confirmer la désactivation</h3>
                                                <button type="button" class="pdfb-canvas-modal-close" onclick="closeDeactivateModal()">&times;</button>
                                            </div>
                                            <div class="pdfb-canvas-modal-body" style="text-align: center; padding: 30px;">
                                                <div style="font-size: 48px; margin-bottom: 20px;">⚠️</div>
                                                <h4 style="margin-bottom: 15px; color: #23282d;">Êtes-vous sûr de vouloir désactiver la licence ?</h4>
                                                <p style="margin-bottom: 20px; color: #666; line-height: 1.5;">
                                                    Cette action va :
                                                </p>
                                                <ul style="text-align: left; color: #666; margin: 0 0 25px 0; padding-left: 20px;">
                                                    <li>Supprimer votre clé de licence</li>
                                                    <li>Repasser en mode gratuit</li>
                                                    <li>Perdre l'accès aux fonctionnalités premium</li>
                                                </ul>
                                                <div style="display: flex; gap: 10px; justify-content: center;">
                                                    <button type="button" class="button button-secondary" onclick="closeDeactivateModal()" style="padding: 10px 20px;">Annuler</button>
                                                    <button type="button" class="button button-danger" onclick="confirmDeactivateLicense()" style="padding: 10px 20px; background: #dc3545; border-color: #dc3545;">Désactiver</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                document.body.insertAdjacentHTML('beforeend', modalHTML);
                            } else {
                                document.getElementById('deactivate-modal-overlay').style.display = 'flex';
                            }
                        }

                        function closeDeactivateModal() {
                            var modal = document.getElementById('deactivate-modal-overlay');
                            if (modal) {
                                modal.style.display = 'none';
                            }
                        }

                        function confirmDeactivateLicense() {
                            // Appel AJAX pour désactiver la licence
                            var xhr = new XMLHttpRequest();
                            xhr.open('POST', window.pdfBuilderLicense.ajaxUrl, true);
                            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                            
                            var data = 'action=pdf_builder_deactivate_license' +
                                       '&nonce=' + encodeURIComponent(window.pdfBuilderLicense.deactivateNonce);
                            
                            xhr.onload = function() {
                                closeDeactivateModal();
                                if (xhr.status === 200) {
                                    var response = {};
                                    try {
                                        response = JSON.parse(xhr.responseText);
                                    } catch(e) {
                                        console.error('Réponse AJAX invalide:', xhr.responseText);
                                    }
                                    
                                    if (response.success) {
                                        // Afficher un message de succès
                                        var successMsg = document.createElement('div');
                                        successMsg.className = 'notice notice-success';
                                        successMsg.style.cssText = 'margin: 20px 0; padding: 12px; border-left: 4px solid #28a745;';
                                        successMsg.innerHTML = '<p><strong>✓</strong> Licence désactivée avec succès. Rafraîchissement...</p>';
                                        document.body.insertBefore(successMsg, document.body.firstChild);
                                        
                                        // Rafraîchir la page après 1 seconde
                                        setTimeout(function() {
                                            window.location.reload();
                                        }, 1500);
                                    } else {
                                        // Erreur
                                        var errorMsg = document.createElement('div');
                                        errorMsg.className = 'notice notice-error';
                                        errorMsg.style.cssText = 'margin: 20px 0; padding: 12px; border-left: 4px solid #dc3545;';
                                        errorMsg.innerHTML = '<p><strong>✗</strong> ' + (response.message || 'Erreur lors de la désactivation') + '</p>';
                                        document.body.insertBefore(errorMsg, document.body.firstChild);
                                    }
                                } else {
                                    var errorMsg = document.createElement('div');
                                    errorMsg.className = 'notice notice-error';
                                    errorMsg.style.cssText = 'margin: 20px 0; padding: 12px; border-left: 4px solid #dc3545;';
                                    errorMsg.innerHTML = '<p><strong>✗</strong> Erreur serveur (statut: ' + xhr.status + ')</p>';
                                    document.body.insertBefore(errorMsg, document.body.firstChild);
                                }
                            };
                            
                            xhr.onerror = function() {
                                closeDeactivateModal();
                                var errorMsg = document.createElement('div');
                                errorMsg.className = 'notice notice-error';
                                errorMsg.style.cssText = 'margin: 20px 0; padding: 12px; border-left: 4px solid #dc3545;';
                                errorMsg.innerHTML = '<p><strong>✗</strong> Erreur de connexion</p>';
                                document.body.insertBefore(errorMsg, document.body.firstChild);
                            };
                            
                            xhr.send(data);
                        }

                        function deactivateTestMode() {
                            if (confirm('Êtes-vous sûr de vouloir désactiver le mode test ? Toutes les fonctionnalités premium seront désactivées.')) {
                                // Créer et soumettre un formulaire de désactivation du mode test
                                var form = document.createElement('form');
                                form.method = 'POST';
                                form.action = '';

                                var nonceField = document.createElement('input');
                                nonceField.type = 'hidden';
                                nonceField.name = 'pdf_builder_deactivate_nonce';
                                nonceField.value = window.pdfBuilderLicense.deactivateNonce;
                                form.appendChild(nonceField);

                                var actionField = document.createElement('input');
                                actionField.type = 'hidden';
                                actionField.name = 'deactivate_test_mode';
                                actionField.value = '1';
                                form.appendChild(actionField);

                                document.body.appendChild(form);
                                form.submit();
                            }
                        }
                    </script>

