<?php // Licence tab content - Updated: AJAX centralized 2025-12-02

// require_once __DIR__ . '/../settings-helpers.php'; // REMOVED - settings-helpers.php deleted
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

                

                <?php
                    // Récupération des paramètres depuis le tableau unifié
                    $settings = get_option('pdf_builder_settings', []);
                    error_log('[PDF Builder] settings-licence.php loaded - license_status: ' . ($settings['pdf_builder_license_status'] ?? 'not set') . ', settings count: ' . count($settings));

                    $license_status = $settings['pdf_builder_license_status'] ?? 'free';
                    $license_key = $settings['pdf_builder_license_key'] ?? '';
                    $license_expires = $settings['pdf_builder_license_expires'] ?? '';
                    $license_activated_at = $settings['pdf_builder_license_activated_at'] ?? '';
                    $test_mode_enabled = get_option('pdf_builder_license_test_mode_enabled', false);
                    $test_key = get_option('pdf_builder_license_test_key', '');
                    $test_key_expires = get_option('pdf_builder_license_test_key_expires', '');
                    $license_email_reminders = $settings['pdf_builder_license_email_reminders'] ?? '0';
                    $license_reminder_email = $settings['pdf_builder_license_reminder_email'] ?? get_option('admin_email', '');
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
                            <?php if (!empty($test_key)): ?>
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

                    <!-- Mode développement -->
                    <div class="action-card secondary">
                        <div class="action-card-header">
                            <h3>
                                <span class="action-icon">🧪</span>
                                Mode Développement
                            </h3>
                            <p>Activer une licence de test pour le développement</p>
                        </div>

                        <div class="action-card-content">
                            <div class="dev-mode-status">
                                <?php if (!empty($test_key)): ?>
                                    <div class="status-active">
                                        <span class="status-dot active"></span>
                                        <span>Mode test actif</span>
                                        <?php if (!empty($test_key_expires)): ?>
                                            <span class="expiry-info">
                                                (expire le <?php echo date('d/m/Y', strtotime($test_key_expires)); ?>)
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <button type="button" class="btn-secondary" onclick="deactivateTestMode()">
                                        Désactiver le mode test
                                    </button>
                                <?php else: ?>
                                    <div class="status-inactive">
                                        <span class="status-dot inactive"></span>
                                        <span>Mode test inactif</span>
                                    </div>
                                    <a href="<?php echo admin_url('admin.php?page=pdf-builder-settings&tab=developpeur'); ?>" class="btn-secondary">
                                        <span class="btn-icon">⚙️</span>
                                        Aller aux outils développeur
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

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

                    <!-- JavaScript AJAX déplacé vers settings-main.php pour éviter les conflits -->
                    <script type="text/javascript">
                        // Nonce for license deactivation
                        window.pdfBuilderLicense = window.pdfBuilderLicense || {};
                        window.pdfBuilderLicense.deactivateNonce = '<?php echo wp_create_nonce("pdf_builder_deactivate"); ?>';
                    </script>
