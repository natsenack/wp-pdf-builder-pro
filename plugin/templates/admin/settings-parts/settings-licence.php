<?php // Licence tab content - Updated: AJAX centralized 2025-12-02

?>
            <!-- Licence Settings Section (No Form - AJAX Centralized) -->
            <section id="licence" aria-label="Gestion de la Licence">

                <!-- Styles CSS pour l'interface ergonomique -->
                <style>
                /* Styles pour l'onglet licence ergonomique */
                .license-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    margin-bottom: 2rem;
                    padding: 1.5rem;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border-radius: 12px;
                    color: white;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                }

                .license-header-content h2 {
                    margin: 0 0 0.5rem 0;
                    font-size: 1.8rem;
                    font-weight: 600;
                }

                .license-header-top {
                    display: flex;
                    align-items: center;
                    gap: 1rem;
                    flex-wrap: wrap;
                }

                .license-status-badge {
                    display: inline-flex;
                    align-items: center;
                    padding: 0.4rem 0.8rem;
                    border-radius: 20px;
                    font-size: 0.85rem;
                    font-weight: 600;
                    white-space: nowrap;
                    transition: all 0.2s ease;
                }

                .license-status-badge.badge-premium {
                    background: rgba(255, 255, 255, 0.25);
                    color: white;
                    border: 1px solid rgba(255, 255, 255, 0.4);
                }

                .license-status-badge.badge-free {
                    background: rgba(255, 255, 255, 0.15);
                    color: rgba(255, 255, 255, 0.9);
                    border: 1px solid rgba(255, 255, 255, 0.25);
                }

                .license-subtitle {
                    margin: 0;
                    opacity: 0.9;
                    font-size: 1rem;
                }

                .license-icon {
                    margin-right: 0.5rem;
                    font-size: 1.5rem;
                }

                .license-quick-actions {
                    flex-shrink: 0;
                }

                .license-btn-primary-large, .license-btn-secondary-large {
                    display: inline-flex;
                    align-items: center;
                    padding: 0.75rem 1.5rem;
                    border-radius: 8px;
                    font-weight: 600;
                    text-decoration: none;
                    transition: all 0.2s ease;
                    border: none;
                    cursor: pointer;
                    font-size: 0.95rem;
                }

                .license-btn-primary-large {
                    background: #fff;
                    color: #667eea;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                }

                .license-btn-primary-large:hover {
                    transform: translateY(-1px);
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
                }

                .license-btn-secondary-large {
                    background: rgba(255, 255, 255, 0.2);
                    color: white;
                    border: 1px solid rgba(255, 255, 255, 0.3);
                }

                .license-btn-secondary-large:hover {
                    background: rgba(255, 255, 255, 0.3);
                }

                .license-btn-icon {
                    margin-right: 0.5rem;
                }

                /* Dashboard de statut */
                .license-dashboard {
                    margin-bottom: 2rem;
                }

                .license-status-card {
                    background: white;
                    border-radius: 12px;
                    padding: 2rem;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                    border-left: 4px solid #e0e0e0;
                    transition: all 0.2s ease;
                }

                .license-status-card.premium-active {
                    border-left-color: #667eea;
                    background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
                }

                .license-status-card.free-mode {
                    border-left-color: #6c757d;
                }

                .license-status-card-header {
                    display: flex;
                    align-items: center;
                    margin-bottom: 1.5rem;
                }

                .license-status-icon {
                    font-size: 3rem;
                    margin-right: 1rem;
                }

                .license-status-info h3 {
                    margin: 0 0 0.25rem 0;
                    font-size: 1.5rem;
                    font-weight: 600;
                    color: #333;
                }

                .license-status-subtitle {
                    margin: 0;
                    color: #666;
                    font-size: 1rem;
                }

                .license-status-details {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }

                .license-status-metrics {
                    display: flex;
                    gap: 2rem;
                }

                .license-metric-item {
                    text-align: center;
                }

                .license-metric-label {
                    display: block;
                    font-size: 0.85rem;
                    color: #666;
                    margin-bottom: 0.25rem;
                }

                .license-metric-value {
                    display: block;
                    font-weight: 600;
                    color: #333;
                }

                .license-test-mode-banner {
                    background: #fff3cd;
                    color: #856404;
                    padding: 0.5rem 1rem;
                    border-radius: 6px;
                    display: inline-flex;
                    align-items: center;
                    gap: 0.5rem;
                    font-weight: 500;
                }

                /* Alertes */
                .license-alert-card {
                    margin-top: 1rem;
                    padding: 1rem;
                    border-radius: 8px;
                    display: flex;
                    align-items: center;
                    gap: 1rem;
                }

                .license-alert-card.warning {
                    background: #fff3cd;
                    border-left: 4px solid #ffc107;
                    color: #856404;
                }

                .license-alert-card.error {
                    background: #f8d7da;
                    border-left: 4px solid #dc3545;
                    color: #721c24;
                }

                .license-alert-icon {
                    font-size: 1.5rem;
                }

                .license-alert-content h4 {
                    margin: 0 0 0.25rem 0;
                    font-weight: 600;
                }

                .license-alert-date {
                    margin: 0.25rem 0 0 0;
                    font-size: 0.9rem;
                    opacity: 0.8;
                }

                .license-alert-actions {
                    margin-left: auto;
                }

                .license-btn-small {
                    padding: 0.5rem 1rem;
                    border-radius: 6px;
                    text-decoration: none;
                    font-weight: 500;
                    font-size: 0.9rem;
                    border: none;
                    cursor: pointer;
                    transition: all 0.2s ease;
                }

                .license-btn-small.primary {
                    background: #007bff;
                    color: white;
                }

                .license-btn-small:hover {
                    opacity: 0.9;
                }

                .license-btn-primary {
                    background: #667eea;
                    color: white;
                    padding: 0.6rem 1rem;
                    border-radius: 6px;
                    border: none;
                    cursor: pointer;
                    font-weight: 600;
                    font-size: 0.9rem;
                    transition: all 0.2s ease;
                    display: inline-flex;
                    align-items: center;
                    gap: 0.5rem;
                }

                .license-btn-primary:hover {
                    background: #5568d3;
                    transform: translateY(-1px);
                    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
                }

                .license-btn-text {
                    /* Placeholder for button text styling */
                }

                .license-btn-secondary {
                    background: rgba(255, 255, 255, 0.2);
                    color: white;
                    border: none;
                    cursor: pointer;
                    transition: all 0.2s ease;
                }

                .license-btn-secondary:hover {
                    background: rgba(255, 255, 255, 0.3);
                }

                .license-support-actions {
                    display: flex;
                    gap: 0.5rem;
                    justify-content: center;
                }

                .license-premium-features-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 0.8rem;
                    margin-bottom: 1rem;
                }

                .license-premium-support {
                    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
                    border-radius: 12px;
                    box-shadow: 0 8px 25px rgba(79, 172, 254, 0.3);
                }

                .license-premium-promo {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
                    border-radius: 12px;
                    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
                }

                .license-premium-promo-content {
                    padding: 1rem;
                    text-align: center;
                    position: relative;
                    z-index: 2;
                }

                .license-premium-cta {
                    background: linear-gradient(45deg, #ff6b6b, #ffa500);
                    color: white;
                    text-decoration: none;
                    border-radius: 25px;
                    font-weight: 600;
                    font-size: 0.9rem;
                    box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
                    transition: all 0.3s ease;
                    position: relative;
                    overflow: hidden;
                    padding: 0.6rem 1.2rem;
                    display: inline-block;
                }

                .license-premium-cta:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 20px rgba(255, 107, 107, 0.6) !important;
                }

                .license-test-key {
                    font-family: 'Courier New', monospace;
                    background: #f8f9fa;
                    padding: 0.2rem 0.4rem;
                    border-radius: 4px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }

                /* Section d'actions */
                .license-actions-section {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 2rem;
                    margin-bottom: 2rem;
                }

                .license-action-card {
                    background: white;
                    border-radius: 12px;
                    padding: 1.5rem;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                    border: 1px solid #e9ecef;
                }

                .license-action-card.secondary {
                    background: #f8f9fa;
                    border-color: #dee2e6;
                }

                .license-action-card-header h3 {
                    margin: 0 0 0.5rem 0;
                    font-size: 1.25rem;
                    display: flex;
                    align-items: center;
                }

                .license-action-card-header p {
                    margin: 0;
                    color: #666;
                }

                .license-action-icon {
                    margin-right: 0.5rem;
                }

                .license-input-group {
                    margin-top: 1rem;
                }

                .license-input-group label {
                    display: block;
                    margin-bottom: 0.5rem;
                    font-weight: 500;
                    color: #333;
                }

                .license-input-with-button {
                    display: flex;
                    gap: 0.5rem;
                }

                .license-input {
                    flex: 1;
                    padding: 0.75rem;
                    border: 1px solid #ced4da;
                    border-radius: 6px;
                    font-size: 1rem;
                }

                .license-input:focus {
                    outline: none;
                    border-color: #667eea;
                    box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.25);
                }

                .license-input-help {
                    margin: 0.5rem 0 0 0;
                    font-size: 0.9rem;
                    color: #666;
                }

                .license-link-primary {
                    color: #667eea;
                    text-decoration: none;
                    font-weight: 500;
                }

                .license-link-primary:hover {
                    text-decoration: underline;
                }

                .license-dev-mode-status {
                    margin-top: 1rem;
                }

                .license-status-active, .license-status-inactive {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    margin-bottom: 1rem;
                }

                .license-status-dot {
                    width: 8px;
                    height: 8px;
                    border-radius: 50%;
                }

                .license-status-dot.active {
                    background: #28a745;
                }

                .license-status-dot.inactive {
                    background: #6c757d;
                }

                .license-expiry-info {
                    color: #666;
                    font-size: 0.9rem;
                }

                /* Section détails */
                .license-details-section {
                    margin-bottom: 1.25rem;
                }

                .license-section-title {
                    font-size: 1rem;
                    margin-bottom: 0.75rem;
                    display: flex;
                    align-items: center;
                    color: #444;
                    font-weight: 600;
                }

                .license-section-icon {
                    margin-right: 0.4rem;
                }

                .license-details-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                    gap: 0.5rem;
                }

                .license-detail-card {
                    background: white;
                    padding: 0.6rem 0.85rem;
                    border-radius: 6px;
                    box-shadow: none;
                    border: 1px solid #e4e7ea;
                }

                .license-detail-card h4 {
                    margin: 0 0 0.2rem 0;
                    font-size: 0.7rem;
                    color: #888;
                    text-transform: uppercase;
                    font-weight: 600;
                    letter-spacing: 0.4px;
                }

                .license-detail-value {
                    margin: 0;
                    font-size: 0.85rem;
                    color: #333;
                    word-break: break-all;
                }

                .license-key, .test-key {
                    font-family: 'Courier New', monospace;
                    background: #f8f9fa;
                    padding: 0.2rem 0.4rem;
                    border-radius: 4px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }

                .license-copy-btn {
                    background: none;
                    border: none;
                    cursor: pointer;
                    padding: 0.25rem;
                    border-radius: 3px;
                    transition: background 0.2s ease;
                }

                .license-copy-btn:hover {
                    background: #e9ecef;
                }

                .license-test-badge {
                    background: #fff3cd;
                    color: #856404;
                    padding: 0.25rem 0.5rem;
                    border-radius: 4px;
                    font-size: 0.8rem;
                    font-weight: 500;
                }

                .license-status-badge {
                    padding: 0.25rem 0.75rem;
                    border-radius: 20px;
                    font-size: 0.85rem;
                    font-weight: 500;
                }

                .license-status-badge.active {
                    background: #d4edda;
                    color: #155724;
                }

                .license-status-badge.free {
                    background: #e2e3e5;
                    color: #383d41;
                }

                .license-status-badge.test {
                    background: #fff3cd;
                    color: #856404;
                }

                /* Section fonctionnalités premium */
                .license-premium-features-section {
                    background: #f8f9fa;
                    padding: 2rem;
                    border-radius: 12px;
                    margin-bottom: 2rem;
                }

                .license-features-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    gap: 1rem;
                    margin-bottom: 2rem;
                }

                .license-feature-item {
                    display: flex;
                    align-items: flex-start;
                    gap: 1rem;
                    padding: 1rem;
                    background: white;
                    border-radius: 8px;
                    border: 1px solid #e9ecef;
                    transition: all 0.2s ease;
                }

                .license-feature-item.unlocked {
                    border-color: #28a745;
                    background: #f8fff8;
                }

                .license-feature-item.locked {
                    opacity: 0.7;
                }

                .license-feature-icon {
                    font-size: 1.5rem;
                    flex-shrink: 0;
                }

                .license-feature-content h4 {
                    margin: 0 0 0.25rem 0;
                    font-size: 1rem;
                    color: #333;
                }

                .license-feature-content p {
                    margin: 0;
                    font-size: 0.9rem;
                    color: #666;
                }

                .license-upgrade-prompt {
                    text-align: center;
                    padding: 2rem;
                    background: white;
                    border-radius: 8px;
                    border: 2px solid #667eea;
                }

                .license-upgrade-prompt h4 {
                    margin: 0 0 0.5rem 0;
                    color: #333;
                    font-size: 1.25rem;
                }

                .license-upgrade-prompt p {
                    margin: 0 0 1.5rem 0;
                    color: #666;
                }

                /* Responsive */
                @media (max-width: 768px) {
                    .license-header {
                        flex-direction: column;
                        gap: 1rem;
                    }

                    .license-quick-actions {
                        align-self: stretch;
                    }

                    .license-actions-section {
                        grid-template-columns: 1fr;
                    }

                    .license-status-details {
                        flex-direction: column;
                        align-items: flex-start;
                        gap: 1rem;
                    }

                    .license-status-metrics {
                        flex-direction: column;
                        gap: 1rem;
                        align-items: flex-start;
                    }

                    .features-grid, .license-details-grid {
                        grid-template-columns: 1fr;
                    }

                    .license-alert-card {
                        flex-direction: column;
                        text-align: center;
                        gap: 0.5rem;
                    }

                    .license-alert-actions {
                        margin-left: 0;
                    }
                }
                </style>

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
                <div class="license-header">
                    <div class="license-header-content">
                        <div class="license-header-top">
                            <h2 class="license-main-title">
                                <span class="license-icon">🔐</span>
                                Gestion de la Licence
                            </h2>
                            <!-- Badge Statut Licence -->
                            <span class="license-status-badge <?php echo $is_premium ? 'badge-premium' : 'badge-free'; ?>">
                                <?php echo $is_premium ? '⭐ Premium' : '○ Version Gratuite'; ?>
                            </span>
                        </div>
                        <p class="license-subtitle">Gérez votre licence PDF Builder Pro et accédez aux fonctionnalités premium</p>
                    </div>

                    <!-- Actions rapides -->
                    <div class="license-quick-actions">
                        <?php if (!$is_premium): ?>
                            <a href="#activate-section" class="license-btn-primary-large">
                                <span class="license-btn-icon">🚀</span>
                                Activer Premium
                            </a>
                        <?php else: ?>
                            <button type="button" class="license-btn-secondary-large" onclick="showDeactivateModal()">
                                <span class="license-btn-icon">🔓</span>
                                Désactiver
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Dashboard de statut principal -->
                <div class="license-dashboard">

                    <!-- Carte de statut principal -->
                    <div class="license-status-card <?php echo $is_premium ? 'premium-active' : 'free-mode'; ?>">
                        <div class="license-status-card-header">
                            <div class="license-status-icon">
                                <?php if ($is_premium): ?>
                                    <span class="license-status-icon-premium">⭐</span>
                                <?php else: ?>
                                    <span class="license-status-icon-free">○</span>
                                <?php endif; ?>
                            </div>
                            <div class="license-status-info">
                                <h3 class="license-status-title">
                                    <?php echo $is_premium ? 'Licence Premium Active' : 'Statut de la Licence'; ?>
                                </h3>
                                <p class="license-status-subtitle">
                                    <?php echo $is_premium ? 'Toutes les fonctionnalités débloquées' : 'Fonctionnalités limitées disponibles'; ?>
                                </p>
                            </div>
                        </div>

                        <div class="license-status-details">
                            <?php if ($is_premium): ?>
                                <div class="license-status-metrics">
                                    <?php if (!empty($license_expires)): ?>
                                        <div class="license-metric-item">
                                            <span class="license-metric-label">Expire le</span>
                                            <span class="license-metric-value"><?php echo date('d/m/Y', strtotime($license_expires)); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($license_activated_at)): ?>
                                        <div class="license-metric-item">
                                            <span class="license-metric-label">Activée le</span>
                                            <span class="license-metric-value"><?php echo date('d/m/Y', strtotime($license_activated_at)); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Indicateur de mode test -->
                            <?php if ($is_test_mode): ?>
                                <div class="license-test-mode-banner">
                                    <span class="license-test-icon">🧪</span>
                                    <span class="license-test-text">Mode Développement Actif</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Alertes importantes -->
                    <?php if ($is_premium && !empty($license_expires)): ?>
                        <?php
                        $now = new DateTime();
                        $expires = new DateTime($license_expires);
                        $diff = $now->diff($expires);
                        $days_left = $diff->invert ? -$diff->days : $diff->days;

                        if ($days_left <= 30 && $days_left > 0):
                        ?>
                        <div class="license-alert-card warning">
                            <div class="license-alert-icon">⏰</div>
                            <div class="license-alert-content">
                                <h4>Expiration imminente</h4>
                                <p>Votre licence expire dans <strong><?php echo $days_left; ?> jour<?php echo $days_left > 1 ? 's' : ''; ?></strong></p>
                                <p class="license-alert-date">Le <?php echo date('d/m/Y', strtotime($license_expires)); ?></p>
                            </div>
                            <div class="license-alert-actions">
                                <a href="#renewal" class="license-btn-small">Renouveler</a>
                            </div>
                        </div>
                        <?php elseif ($diff->invert): ?>
                        <div class="license-alert-card error">
                            <div class="license-alert-icon">❌</div>
                            <div class="license-alert-content">
                                <h4>Licence expirée</h4>
                                <p>Votre licence a expiré il y a <?php echo abs($days_left); ?> jour<?php echo abs($days_left) > 1 ? 's' : ''; ?></p>
                                <p class="license-alert-date">Le <?php echo date('d/m/Y', strtotime($license_expires)); ?></p>
                            </div>
                            <div class="license-alert-actions">
                                <a href="#renewal" class="license-btn-small primary">Renouveler maintenant</a>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>

                </div>

                <!-- Section d'actions principales -->
                <div class="license-actions-section">

                    <!-- Activation de licence -->
                    <div id="activate-section" class="license-action-card">
                        <div class="license-action-card-header">
                            <h3>
                                <span class="license-action-icon">🔑</span>
                                <?php echo $is_premium ? 'Changer de Licence' : 'Activer une Licence'; ?>
                            </h3>
                            <p><?php echo $is_premium ? 'Remplacer votre licence actuelle' : 'Débloquer toutes les fonctionnalités premium'; ?></p>
                        </div>

                        <div class="license-action-card-content">
                            <div class="license-input-group">
                                <label for="license_key_input">Clé de licence</label>
                                <div class="license-input-with-button">
                                    <input type="text"
                                           id="license_key_input"
                                           placeholder="<?php echo $is_premium && !empty($license_key_masked) ? esc_attr($license_key_masked) : 'Entrez votre clé de licence premium'; ?>"
                                           class="license-input">
                                    <button type="button" class="license-btn-primary" id="activate-license-btn">
                                        <span class="license-btn-text"><?php echo $is_premium ? 'Changer' : 'Activer'; ?></span>
                                        <span class="license-btn-icon">✓</span>
                                    </button>
                                </div>
                                <p class="license-input-help">
                                    <?php if (!$is_premium): ?>
                                        Vous n'avez pas de clé ? <a href="#get-license" class="license-link-primary">Obtenir une licence premium</a>
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
                    <div class="license-action-card license-premium-support" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 12px; box-shadow: 0 8px 25px rgba(79, 172, 254, 0.3);">
                        <div class="license-action-card-header" style="padding: 1rem; text-align: center;">
                            <div style="font-size: 2.5rem; margin-bottom: 0.5rem; animation: supportPulse 2s ease-in-out infinite;">💎</div>
                            <h3 style="margin: 0 0 0.5rem 0; color: white; font-size: 1.2rem; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">Support Premium & Avantages</h3>
                            <p style="margin: 0 0 1rem 0; color: rgba(255,255,255,0.9); font-size: 0.9rem; line-height: 1.4;">
                                Découvrez tous les avantages de votre licence premium
                            </p>
                        </div>

                        <div class="license-action-card-content" style="padding: 0 1rem 1rem 1rem;">
                            <div class="license-premium-features-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem; margin-bottom: 1rem;">
                                <div class="license-feature-item" style="background: rgba(255,255,255,0.1); padding: 0.8rem; border-radius: 8px; text-align: center; backdrop-filter: blur(10px);">
                                    <div style="font-size: 1.5rem; margin-bottom: 0.3rem;">📊</div>
                                    <div style="font-size: 0.8rem; color: white; font-weight: 500;">Exports Multi-Formats</div>
                                </div>
                                <div class="license-feature-item" style="background: rgba(255,255,255,0.1); padding: 0.8rem; border-radius: 8px; text-align: center; backdrop-filter: blur(10px);">
                                    <div style="font-size: 1.5rem; margin-bottom: 0.3rem;">🎯</div>
                                    <div style="font-size: 0.8rem; color: white; font-weight: 500;">Navigation Grille</div>
                                </div>
                                <div class="license-feature-item" style="background: rgba(255,255,255,0.1); padding: 0.8rem; border-radius: 8px; text-align: center; backdrop-filter: blur(10px);">
                                    <div style="font-size: 1.5rem; margin-bottom: 0.3rem;">⚡</div>
                                    <div style="font-size: 0.8rem; color: white; font-weight: 500;">Résolutions Élevées</div>
                                </div>
                                <div class="license-feature-item" style="background: rgba(255,255,255,0.1); padding: 0.8rem; border-radius: 8px; text-align: center; backdrop-filter: blur(10px);">
                                    <div style="font-size: 1.5rem; margin-bottom: 0.3rem;">🔧</div>
                                    <div style="font-size: 0.8rem; color: white; font-weight: 500;">Outils Avancés</div>
                                </div>
                            </div>

                            <div class="license-support-actions" style="display: flex; gap: 0.5rem; justify-content: center;">
                                <a href="https://wp-pdf-builder.com/support" target="_blank" class="license-btn-secondary" style="flex: 1; text-align: center; padding: 0.6rem; background: rgba(255,255,255,0.2); color: white; text-decoration: none; border-radius: 20px; font-size: 0.8rem; backdrop-filter: blur(10px);">
                                    <span style="display: block; font-weight: 500;">📞 Support</span>
                                </a>
                                <a href="https://wp-pdf-builder.com/docs" target="_blank" class="license-btn-secondary" style="flex: 1; text-align: center; padding: 0.6rem; background: rgba(255,255,255,0.2); color: white; text-decoration: none; border-radius: 20px; font-size: 0.8rem; backdrop-filter: blur(10px);">
                                    <span style="display: block; font-weight: 500;">📚 Docs</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <style>
                    @keyframes supportPulse {
                        0%, 100% { transform: scale(1); }
                        50% { transform: scale(1.1); }
                    }

                    .license-premium-support:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 12px 35px rgba(79, 172, 254, 0.4) !important;
                        transition: all 0.3s ease;
                    }

                    .license-feature-item:hover {
                        transform: translateY(-2px);
                        transition: all 0.3s ease;
                    }
                    </style>
                    <?php else: ?>
                    <!-- Section publicité premium -->
                    <div class="license-action-card license-premium-promo" style="max-height: 200px; overflow: hidden; position: relative; background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%); background-size: 200% 200%; animation: gradientShift 3s ease infinite; border-radius: 12px; box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);">
                        <div class="license-premium-promo-content" style="padding: 1rem; text-align: center; position: relative; z-index: 2;">
                            <div style="font-size: 2.5rem; margin-bottom: 0.5rem; animation: rocketFloat 2s ease-in-out infinite; display: inline-block;">🚀</div>
                            <h3 style="margin: 0 0 0.5rem 0; color: white; font-size: 1.2rem; text-shadow: 0 2px 4px rgba(0,0,0,0.3); animation: textGlow 2s ease-in-out infinite alternate;">Découvrez la Version Premium</h3>
                            <p style="margin: 0 0 1rem 0; color: rgba(255,255,255,0.9); font-size: 0.9rem; line-height: 1.4; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                                Débloquez toutes les fonctionnalités avancées : exports multi-formats, résolutions élevées, navigation grille, et bien plus !
                            </p>
                            <a href="#" onclick="showUpgradeModal('license_tab')" class="license-btn-primary license-premium-cta" style="display: inline-block; padding: 0.6rem 1.2rem; background: linear-gradient(45deg, #ff6b6b, #ffa500); color: white; text-decoration: none; border-radius: 25px; font-weight: 600; font-size: 0.9rem; box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4); transition: all 0.3s ease; position: relative; overflow: hidden;">
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

                    <style>
                    @keyframes gradientShift {
                        0% { background-position: 0% 50%; }
                        50% { background-position: 100% 50%; }
                        100% { background-position: 0% 50%; }
                    }

                    @keyframes rocketFloat {
                        0%, 100% { transform: translateY(0px) rotate(-5deg); }
                        50% { transform: translateY(-10px) rotate(5deg); }
                    }

                    @keyframes textGlow {
                        0% { text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
                        100% { text-shadow: 0 2px 4px rgba(0,0,0,0.3), 0 0 20px rgba(255,255,255,0.5); }
                    }

                    @keyframes particleFloat {
                        0%, 100% { transform: translateY(0px) translateX(0px); opacity: 0.6; }
                        50% { transform: translateY(-20px) translateX(10px); opacity: 1; }
                    }

                    .license-premium-cta:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 6px 20px rgba(255, 107, 107, 0.6) !important;
                    }

                    .license-premium-cta:hover div {
                        left: 100%;
                    }

                    .license-premium-promo:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4) !important;
                        transition: all 0.3s ease;
                    }
                    </style>
                    <?php endif; ?>

                </div>

                <!-- Section informations détaillées -->
                <?php if ($is_premium || !empty($test_key)): ?>
                <div class="license-details-section">
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
                    <div class="license-details-grid">

                        <!-- Statut -->
                        <div class="license-detail-card">
                            <h4>Statut</h4>
                            <p class="license-detail-value">
                                <?php if (!empty($test_key)): ?>
                                    <span class="license-status-badge test">🧪 Mode Test</span>
                                <?php elseif ($is_premium): ?>
                                    <span class="license-status-badge active">✅ Premium</span>
                                <?php else: ?>
                                    <span class="license-status-badge free">○ Gratuit</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <!-- Site -->
                        <div class="license-detail-card">
                            <h4>Site actuel</h4>
                            <p class="license-detail-value"><?php echo esc_html(home_url()); ?></p>
                        </div>

                        <!-- Clé Premium (masquée) -->
                        <?php if (!empty($edd_license_key)): ?>
                        <div class="license-detail-card">
                            <h4>Clé de licence</h4>
                            <p class="license-detail-value license-key" style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                                <code><?php echo esc_html(substr($edd_license_key, 0, 5) . str_repeat('•', 18)); ?></code>
                                <button type="button" class="license-copy-btn"
                                        onclick="navigator.clipboard.writeText('<?php echo esc_js($edd_license_key); ?>').then(function(){ this.textContent='✅'; }.bind(this))"
                                        title="Copier la clé">
                                    📋
                                </button>
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Expiration -->
                        <?php if ($is_premium): ?>
                        <div class="license-detail-card">
                            <h4>Expiration</h4>
                            <p class="license-detail-value">
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
                        <div class="license-detail-card">
                            <h4>Jours restants</h4>
                            <p class="license-detail-value">
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
                        <div class="license-detail-card">
                            <h4>Titulaire</h4>
                            <p class="license-detail-value"><?php echo esc_html($lic_customer); ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Email -->
                        <?php if (!empty($lic_email)): ?>
                        <div class="license-detail-card">
                            <h4>Email</h4>
                            <p class="license-detail-value"><?php echo esc_html($lic_email); ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Activations restantes -->
                        <?php if ($is_premium && $lic_activations !== null): ?>
                        <div class="license-detail-card">
                            <h4>Activations restantes</h4>
                            <p class="license-detail-value">
                                <span style="font-weight:600;"><?php echo (int)$lic_activations; ?></span>
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Clé de Test -->
                        <?php if (!empty($test_key)): ?>
                        <div class="license-detail-card">
                            <h4>Clé de Test</h4>
                            <p class="license-detail-value license-test-key">
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
                    <h3 class="section-title" style="font-size:1rem;">
                        <span class="section-icon">⭐</span>
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
                    <div class="license-upgrade-prompt" style="margin-top:1rem;">
                        <h4>Prêt à passer au premium ?</h4>
                        <p>Débloquez toutes ces fonctionnalités et bien plus encore</p>
                        <a href="#activate-section" class="btn-primary-large">
                            <span class="btn-icon">🚀</span>
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






