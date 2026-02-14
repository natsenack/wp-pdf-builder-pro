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

                .btn-primary-large, .btn-secondary-large {
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

                .btn-primary-large {
                    background: #fff;
                    color: #667eea;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                }

                .btn-primary-large:hover {
                    transform: translateY(-1px);
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
                }

                .btn-secondary-large {
                    background: rgba(255, 255, 255, 0.2);
                    color: white;
                    border: 1px solid rgba(255, 255, 255, 0.3);
                }

                .btn-secondary-large:hover {
                    background: rgba(255, 255, 255, 0.3);
                }

                .btn-icon {
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

                .status-card-header {
                    display: flex;
                    align-items: center;
                    margin-bottom: 1.5rem;
                }

                .status-icon {
                    font-size: 3rem;
                    margin-right: 1rem;
                }

                .status-info h3 {
                    margin: 0 0 0.25rem 0;
                    font-size: 1.5rem;
                    font-weight: 600;
                    color: #333;
                }

                .status-subtitle {
                    margin: 0;
                    color: #666;
                    font-size: 1rem;
                }

                .status-details {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }

                .status-metrics {
                    display: flex;
                    gap: 2rem;
                }

                .metric-item {
                    text-align: center;
                }

                .metric-label {
                    display: block;
                    font-size: 0.85rem;
                    color: #666;
                    margin-bottom: 0.25rem;
                }

                .metric-value {
                    display: block;
                    font-weight: 600;
                    color: #333;
                }

                .test-mode-banner {
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

                .alert-icon {
                    font-size: 1.5rem;
                }

                .alert-content h4 {
                    margin: 0 0 0.25rem 0;
                    font-weight: 600;
                }

                .alert-date {
                    margin: 0.25rem 0 0 0;
                    font-size: 0.9rem;
                    opacity: 0.8;
                }

                .alert-actions {
                    margin-left: auto;
                }

                .btn-small {
                    padding: 0.5rem 1rem;
                    border-radius: 6px;
                    text-decoration: none;
                    font-weight: 500;
                    font-size: 0.9rem;
                    border: none;
                    cursor: pointer;
                    transition: all 0.2s ease;
                }

                .btn-small.primary {
                    background: #007bff;
                    color: white;
                }

                .btn-small:hover {
                    opacity: 0.9;
                }

                /* Section d'actions */
                .license-actions-section {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 2rem;
                    margin-bottom: 2rem;
                }

                .action-card {
                    background: white;
                    border-radius: 12px;
                    padding: 1.5rem;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                    border: 1px solid #e9ecef;
                }

                .action-card.secondary {
                    background: #f8f9fa;
                    border-color: #dee2e6;
                }

                .action-card-header h3 {
                    margin: 0 0 0.5rem 0;
                    font-size: 1.25rem;
                    display: flex;
                    align-items: center;
                }

                .action-card-header p {
                    margin: 0;
                    color: #666;
                }

                .action-icon {
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

                .input-with-button {
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

                .input-help {
                    margin: 0.5rem 0 0 0;
                    font-size: 0.9rem;
                    color: #666;
                }

                .link-primary {
                    color: #667eea;
                    text-decoration: none;
                    font-weight: 500;
                }

                .link-primary:hover {
                    text-decoration: underline;
                }

                .dev-mode-status {
                    margin-top: 1rem;
                }

                .status-active, .status-inactive {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    margin-bottom: 1rem;
                }

                .status-dot {
                    width: 8px;
                    height: 8px;
                    border-radius: 50%;
                }

                .status-dot.active {
                    background: #28a745;
                }

                .status-dot.inactive {
                    background: #6c757d;
                }

                .expiry-info {
                    color: #666;
                    font-size: 0.9rem;
                }

                /* Section détails */
                .license-details-section {
                    margin-bottom: 2rem;
                }

                .section-title {
                    font-size: 1.5rem;
                    margin-bottom: 1.5rem;
                    display: flex;
                    align-items: center;
                    color: #333;
                }

                .section-icon {
                    margin-right: 0.5rem;
                }

                .details-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    gap: 1rem;
                }

                .detail-card {
                    background: white;
                    padding: 1.5rem;
                    border-radius: 8px;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                    border: 1px solid #e9ecef;
                }

                .detail-card h4 {
                    margin: 0 0 0.5rem 0;
                    font-size: 1rem;
                    color: #666;
                    text-transform: uppercase;
                    font-weight: 600;
                    letter-spacing: 0.5px;
                }

                .detail-value {
                    margin: 0;
                    font-size: 1.1rem;
                    color: #333;
                    word-break: break-all;
                }

                .license-key, .test-key {
                    font-family: 'Courier New', monospace;
                    background: #f8f9fa;
                    padding: 0.5rem;
                    border-radius: 4px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }

                .copy-btn {
                    background: none;
                    border: none;
                    cursor: pointer;
                    padding: 0.25rem;
                    border-radius: 3px;
                    transition: background 0.2s ease;
                }

                .copy-btn:hover {
                    background: #e9ecef;
                }

                .test-badge {
                    background: #fff3cd;
                    color: #856404;
                    padding: 0.25rem 0.5rem;
                    border-radius: 4px;
                    font-size: 0.8rem;
                    font-weight: 500;
                }

                .status-badge {
                    padding: 0.25rem 0.75rem;
                    border-radius: 20px;
                    font-size: 0.85rem;
                    font-weight: 500;
                }

                .status-badge.active {
                    background: #d4edda;
                    color: #155724;
                }

                .status-badge.free {
                    background: #e2e3e5;
                    color: #383d41;
                }

                .status-badge.test {
                    background: #fff3cd;
                    color: #856404;
                }

                /* Section fonctionnalités premium */
                .premium-features-section {
                    background: #f8f9fa;
                    padding: 2rem;
                    border-radius: 12px;
                    margin-bottom: 2rem;
                }

                .features-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    gap: 1rem;
                    margin-bottom: 2rem;
                }

                .feature-item {
                    display: flex;
                    align-items: flex-start;
                    gap: 1rem;
                    padding: 1rem;
                    background: white;
                    border-radius: 8px;
                    border: 1px solid #e9ecef;
                    transition: all 0.2s ease;
                }

                .feature-item.unlocked {
                    border-color: #28a745;
                    background: #f8fff8;
                }

                .feature-item.locked {
                    opacity: 0.7;
                }

                .feature-icon {
                    font-size: 1.5rem;
                    flex-shrink: 0;
                }

                .feature-content h4 {
                    margin: 0 0 0.25rem 0;
                    font-size: 1rem;
                    color: #333;
                }

                .feature-content p {
                    margin: 0;
                    font-size: 0.9rem;
                    color: #666;
                }

                .upgrade-prompt {
                    text-align: center;
                    padding: 2rem;
                    background: white;
                    border-radius: 8px;
                    border: 2px solid #667eea;
                }

                .upgrade-prompt h4 {
                    margin: 0 0 0.5rem 0;
                    color: #333;
                    font-size: 1.25rem;
                }

                .upgrade-prompt p {
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

                    .status-details {
                        flex-direction: column;
                        align-items: flex-start;
                        gap: 1rem;
                    }

                    .status-metrics {
                        flex-direction: column;
                        gap: 1rem;
                        align-items: flex-start;
                    }

                    .features-grid, .details-grid {
                        grid-template-columns: 1fr;
                    }

                    .license-alert-card {
                        flex-direction: column;
                        text-align: center;
                        gap: 0.5rem;
                    }

                    .alert-actions {
                        margin-left: 0;
                    }
                }
                </style>

                <?php
                    // Récupération des paramètres depuis le tableau unifié
                    $settings = pdf_builder_get_option('pdf_builder_settings', array());
                    error_log('[PDF Builder] settings-licence.php loaded - license_status: ' . ($settings['pdf_builder_license_status'] ?? 'not set') . ', settings count: ' . count($settings));

                    $license_status = $settings['pdf_builder_license_status'] ?? 'free';
                    $license_key = $settings['pdf_builder_license_key'] ?? '';
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
                    $is_test_mode = $test_mode_enabled === '1';

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

                        if (pdf_builder_verify_nonce($_POST['pdf_builder_deactivate_nonce'], 'pdf_builder_deactivate')) {
                            // Mise à jour du tableau unifié au lieu d'options séparées
                            $settings = pdf_builder_get_option('pdf_builder_settings', array());
                            $settings['pdf_builder_license_key'] = '';
                            $settings['pdf_builder_license_expires'] = '';
                            $settings['pdf_builder_license_activated_at'] = '';
                            $settings['pdf_builder_license_test_key'] = '';
                            $settings['pdf_builder_license_test_mode_enabled'] = '0';
                            $settings['pdf_builder_license_status'] = 'free';
                            pdf_builder_update_option('pdf_builder_settings', $settings);

                            $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Licence désactivée complètement.</p></div>';
                            $is_premium = false;
                            $license_key = '';
                            $license_status = 'free';
                            $license_activated_at = '';
                            $test_key = '';
                            $test_mode_enabled = '0';
                        }
                    }
                ?>

                <!-- Header avec titre et actions principales -->
                <div class="license-header">
                    <div class="license-header-content">
                        <h2 class="license-main-title">
                            <span class="license-icon">🔐</span>
                            Gestion de la Licence
                        </h2>
                        <p class="license-subtitle">Gérez votre licence PDF Builder Pro et accédez aux fonctionnalités premium</p>
                    </div>

                    <!-- Actions rapides -->
                    <div class="license-quick-actions">
                        <?php if (!$is_premium): ?>
                            <a href="#activate-section" class="btn-primary-large">
                                <span class="btn-icon">🚀</span>
                                Activer Premium
                            </a>
                        <?php else: ?>
                            <button type="button" class="btn-secondary-large" onclick="showDeactivateModal()">
                                <span class="btn-icon">🔓</span>
                                Désactiver
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Dashboard de statut principal -->
                <div class="license-dashboard">

                    <!-- Carte de statut principal -->
                    <div class="license-status-card <?php echo $is_premium ? 'premium-active' : 'free-mode'; ?>">
                        <div class="status-card-header">
                            <div class="status-icon">
                                <?php if ($is_premium): ?>
                                    <span class="status-icon-premium">⭐</span>
                                <?php else: ?>
                                    <span class="status-icon-free">○</span>
                                <?php endif; ?>
                            </div>
                            <div class="status-info">
                                <h3 class="status-title">
                                    <?php echo $is_premium ? 'Licence Premium Active' : 'Version Gratuite'; ?>
                                </h3>
                                <p class="status-subtitle">
                                    <?php echo $is_premium ? 'Toutes les fonctionnalités débloquées' : 'Fonctionnalités limitées'; ?>
                                </p>
                            </div>
                        </div>

                        <div class="status-details">
                            <?php if ($is_premium): ?>
                                <div class="status-metrics">
                                    <?php if (!empty($license_expires)): ?>
                                        <div class="metric-item">
                                            <span class="metric-label">Expire le</span>
                                            <span class="metric-value"><?php echo date('d/m/Y', strtotime($license_expires)); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($license_activated_at)): ?>
                                        <div class="metric-item">
                                            <span class="metric-label">Activée le</span>
                                            <span class="metric-value"><?php echo date('d/m/Y', strtotime($license_activated_at)); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Indicateur de mode test -->
                            <?php if ($is_test_mode): ?>
                                <div class="test-mode-banner">
                                    <span class="test-icon">🧪</span>
                                    <span class="test-text">Mode Développement Actif</span>
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
                            <div class="alert-icon">⏰</div>
                            <div class="alert-content">
                                <h4>Expiration imminente</h4>
                                <p>Votre licence expire dans <strong><?php echo $days_left; ?> jour<?php echo $days_left > 1 ? 's' : ''; ?></strong></p>
                                <p class="alert-date">Le <?php echo date('d/m/Y', strtotime($license_expires)); ?></p>
                            </div>
                            <div class="alert-actions">
                                <a href="#renewal" class="btn-small">Renouveler</a>
                            </div>
                        </div>
                        <?php elseif ($diff->invert): ?>
                        <div class="license-alert-card error">
                            <div class="alert-icon">❌</div>
                            <div class="alert-content">
                                <h4>Licence expirée</h4>
                                <p>Votre licence a expiré il y a <?php echo abs($days_left); ?> jour<?php echo abs($days_left) > 1 ? 's' : ''; ?></p>
                                <p class="alert-date">Le <?php echo date('d/m/Y', strtotime($license_expires)); ?></p>
                            </div>
                            <div class="alert-actions">
                                <a href="#renewal" class="btn-small primary">Renouveler maintenant</a>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>

                </div>

                <!-- Section d'actions principales -->
                <div class="license-actions-section">

                    <!-- Activation de licence -->
                    <div id="activate-section" class="action-card">
                        <div class="action-card-header">
                            <h3>
                                <span class="action-icon">🔑</span>
                                <?php echo $is_premium ? 'Changer de Licence' : 'Activer une Licence'; ?>
                            </h3>
                            <p><?php echo $is_premium ? 'Remplacer votre licence actuelle' : 'Débloquer toutes les fonctionnalités premium'; ?></p>
                        </div>

                        <div class="action-card-content">
                            <div class="license-input-group">
                                <label for="license_key_input">Clé de licence</label>
                                <div class="input-with-button">
                                    <input type="text"
                                           id="license_key_input"
                                           placeholder="Entrez votre clé de licence premium"
                                           class="license-input">
                                    <button type="button" class="btn-primary" id="activate-license-btn">
                                        <span class="btn-text"><?php echo $is_premium ? 'Changer' : 'Activer'; ?></span>
                                        <span class="btn-icon">✓</span>
                                    </button>
                                </div>
                                <p class="input-help">
                                    <?php if (!$is_premium): ?>
                                        Vous n'avez pas de clé ? <a href="#get-license" class="link-primary">Obtenir une licence premium</a>
                                    <?php else: ?>
                                        Une nouvelle clé remplacera l'actuelle
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Support & Avantages Premium -->
                    <?php if ($is_test_mode): ?>
                    <div class="action-card premium-support" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 12px; box-shadow: 0 8px 25px rgba(79, 172, 254, 0.3);">
                        <div class="action-card-header" style="padding: 1rem; text-align: center;">
                            <div style="font-size: 2.5rem; margin-bottom: 0.5rem; animation: supportPulse 2s ease-in-out infinite;">💎</div>
                            <h3 style="margin: 0 0 0.5rem 0; color: white; font-size: 1.2rem; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">Support Premium & Avantages</h3>
                            <p style="margin: 0 0 1rem 0; color: rgba(255,255,255,0.9); font-size: 0.9rem; line-height: 1.4;">
                                Découvrez tous les avantages de votre licence premium
                            </p>
                        </div>

                        <div class="action-card-content" style="padding: 0 1rem 1rem 1rem;">
                            <div class="premium-features-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem; margin-bottom: 1rem;">
                                <div class="feature-item" style="background: rgba(255,255,255,0.1); padding: 0.8rem; border-radius: 8px; text-align: center; backdrop-filter: blur(10px);">
                                    <div style="font-size: 1.5rem; margin-bottom: 0.3rem;">📊</div>
                                    <div style="font-size: 0.8rem; color: white; font-weight: 500;">Exports Multi-Formats</div>
                                </div>
                                <div class="feature-item" style="background: rgba(255,255,255,0.1); padding: 0.8rem; border-radius: 8px; text-align: center; backdrop-filter: blur(10px);">
                                    <div style="font-size: 1.5rem; margin-bottom: 0.3rem;">🎯</div>
                                    <div style="font-size: 0.8rem; color: white; font-weight: 500;">Navigation Grille</div>
                                </div>
                                <div class="feature-item" style="background: rgba(255,255,255,0.1); padding: 0.8rem; border-radius: 8px; text-align: center; backdrop-filter: blur(10px);">
                                    <div style="font-size: 1.5rem; margin-bottom: 0.3rem;">⚡</div>
                                    <div style="font-size: 0.8rem; color: white; font-weight: 500;">Résolutions Élevées</div>
                                </div>
                                <div class="feature-item" style="background: rgba(255,255,255,0.1); padding: 0.8rem; border-radius: 8px; text-align: center; backdrop-filter: blur(10px);">
                                    <div style="font-size: 1.5rem; margin-bottom: 0.3rem;">🔧</div>
                                    <div style="font-size: 0.8rem; color: white; font-weight: 500;">Outils Avancés</div>
                                </div>
                            </div>

                            <div class="support-actions" style="display: flex; gap: 0.5rem; justify-content: center;">
                                <a href="https://wp-pdf-builder.com/support" target="_blank" class="btn-secondary" style="flex: 1; text-align: center; padding: 0.6rem; background: rgba(255,255,255,0.2); color: white; text-decoration: none; border-radius: 20px; font-size: 0.8rem; backdrop-filter: blur(10px);">
                                    <span style="display: block; font-weight: 500;">📞 Support</span>
                                </a>
                                <a href="https://wp-pdf-builder.com/docs" target="_blank" class="btn-secondary" style="flex: 1; text-align: center; padding: 0.6rem; background: rgba(255,255,255,0.2); color: white; text-decoration: none; border-radius: 20px; font-size: 0.8rem; backdrop-filter: blur(10px);">
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

                    .premium-support:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 12px 35px rgba(79, 172, 254, 0.4) !important;
                        transition: all 0.3s ease;
                    }

                    .feature-item:hover {
                        transform: translateY(-2px);
                        transition: all 0.3s ease;
                    }
                    </style>
                    <?php else: ?>
                    <!-- Section publicité premium -->
                    <div class="action-card premium-promo" style="max-height: 200px; overflow: hidden; position: relative; background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%); background-size: 200% 200%; animation: gradientShift 3s ease infinite; border-radius: 12px; box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);">
                        <div class="premium-promo-content" style="padding: 1rem; text-align: center; position: relative; z-index: 2;">
                            <div style="font-size: 2.5rem; margin-bottom: 0.5rem; animation: rocketFloat 2s ease-in-out infinite; display: inline-block;">🚀</div>
                            <h3 style="margin: 0 0 0.5rem 0; color: white; font-size: 1.2rem; text-shadow: 0 2px 4px rgba(0,0,0,0.3); animation: textGlow 2s ease-in-out infinite alternate;">Découvrez la Version Premium</h3>
                            <p style="margin: 0 0 1rem 0; color: rgba(255,255,255,0.9); font-size: 0.9rem; line-height: 1.4; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                                Débloquez toutes les fonctionnalités avancées : exports multi-formats, résolutions élevées, navigation grille, et bien plus !
                            </p>
                            <a href="#" onclick="showUpgradeModal('license_tab')" class="btn-primary premium-cta" style="display: inline-block; padding: 0.6rem 1.2rem; background: linear-gradient(45deg, #ff6b6b, #ffa500); color: white; text-decoration: none; border-radius: 25px; font-weight: 600; font-size: 0.9rem; box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4); transition: all 0.3s ease; position: relative; overflow: hidden;">
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

                    .premium-cta:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 6px 20px rgba(255, 107, 107, 0.6) !important;
                    }

                    .premium-cta:hover div {
                        left: 100%;
                    }

                    .premium-promo:hover {
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
                    <h3 class="section-title">
                        <span class="section-icon">ℹ️</span>
                        Informations détaillées
                    </h3>

                    <div class="details-grid">
                        <div class="detail-card">
                            <h4>Site actuel</h4>
                            <p class="detail-value"><?php echo esc_html(home_url()); ?></p>
                        </div>

                        <?php if (!empty($license_key)): ?>
                        <div class="detail-card">
                            <h4>Clé Premium</h4>
                            <p class="detail-value license-key">
                                <code><?php echo substr($license_key, 0, 8) . '••••••••••••' . substr($license_key, -8); ?></code>
                                <button type="button" class="copy-btn" onclick="navigator.clipboard.writeText('<?php echo esc_js($license_key); ?>')" title="Copier">
                                    📋
                                </button>
                            </p>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($test_key)): ?>
                        <div class="detail-card">
                            <h4>Clé de Test</h4>
                            <p class="detail-value test-key">
                                <code><?php echo substr($test_key, 0, 8) . '••••••••••••' . substr($test_key, -8); ?></code>
                                <span class="test-badge">TEST</span>
                            </p>
                        </div>
                        <?php endif; ?>

                        <div class="detail-card">
                            <h4>Statut</h4>
                            <p class="detail-value">
                                <?php if (!empty($test_key)): ?>
                                    <span class="status-badge test">🧪 Mode Test</span>
                                <?php elseif ($is_premium): ?>
                                    <span class="status-badge active">✅ Premium</span>
                                <?php else: ?>
                                    <span class="status-badge free">○ Gratuit</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Section fonctionnalités premium -->
                <div class="premium-features-section">
                    <h3 class="section-title">
                        <span class="section-icon">⭐</span>
                        Fonctionnalités Premium
                    </h3>

                    <div class="features-grid">
                        <div class="feature-item <?php echo $is_premium ? 'unlocked' : 'locked'; ?>">
                            <div class="feature-icon"><?php echo $is_premium ? '✅' : '🔒'; ?></div>
                            <div class="feature-content">
                                <h4>Templates Avancés</h4>
                                <p>Accès à tous les templates professionnels</p>
                            </div>
                        </div>

                        <div class="feature-item <?php echo $is_premium ? 'unlocked' : 'locked'; ?>">
                            <div class="feature-icon"><?php echo $is_premium ? '✅' : '🔒'; ?></div>
                            <div class="feature-content">
                                <h4>Export Haute Résolution</h4>
                                <p>PDF en qualité supérieure (300 DPI)</p>
                            </div>
                        </div>

                        <div class="feature-item <?php echo $is_premium ? 'unlocked' : 'locked'; ?>">
                            <div class="feature-icon"><?php echo $is_premium ? '✅' : '🔒'; ?></div>
                            <div class="feature-content">
                                <h4>Support Prioritaire</h4>
                                <p>Assistance technique dédiée</p>
                            </div>
                        </div>

                        <div class="feature-item <?php echo $is_premium ? 'unlocked' : 'locked'; ?>">
                            <div class="feature-icon"><?php echo $is_premium ? '✅' : '🔒'; ?></div>
                            <div class="feature-content">
                                <h4>Mises à Jour</h4>
                                <p>Accès aux dernières fonctionnalités</p>
                            </div>
                        </div>
                    </div>

                    <?php if (!$is_premium): ?>
                    <div class="upgrade-prompt">
                        <h4>Prêt à passer au premium ?</h4>
                        <p>Débloquez toutes ces fonctionnalités et bien plus encore</p>
                        <a href="#activate-section" class="btn-primary-large">
                            <span class="btn-icon">🚀</span>
                            Activer maintenant
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                            <article class="status-card<?php echo $is_premium ? ' premium' : ''; ?>">
                                <aside class="status-card-label">Statut</aside>

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
                        // Nonce for license deactivation
                        window.pdfBuilderLicense = window.pdfBuilderLicense || {};
                        window.pdfBuilderLicense.deactivateNonce = '<?php echo wp_create_nonce("pdf_builder_deactivate"); ?>';

                        // Fonctions JavaScript inline pour les modals de licence
                        function showDeactivateModal() {
                            if (!document.getElementById('deactivate-modal-overlay')) {
                                var modalHTML = `
                                    <div id="deactivate-modal-overlay" class="canvas-modal-overlay" style="display: flex; z-index: 10002;">
                                        <div class="canvas-modal-container" style="max-width: 450px;">
                                            <div class="canvas-modal-header">
                                                <h3>⚠️ Confirmer la désactivation</h3>
                                                <button type="button" class="canvas-modal-close" onclick="closeDeactivateModal()">&times;</button>
                                            </div>
                                            <div class="canvas-modal-body" style="text-align: center; padding: 30px;">
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
                            // Créer et soumettre un formulaire de désactivation
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
                            actionField.name = 'deactivate_license';
                            actionField.value = '1';
                            form.appendChild(actionField);

                            document.body.appendChild(form);
                            form.submit();
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




