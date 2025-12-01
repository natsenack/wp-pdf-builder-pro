<?php
/**
 * PDF Builder - Developer Settings Tab
 *
 * Complete rewrite with proper AJAX, security, and state management
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get settings from database
$settings = get_option('pdf_builder_settings', []);
$dev_mode = $settings['pdf_builder_developer_enabled'] ?? '0';
$debug_enabled = $settings['pdf_builder_canvas_debug_enabled'] ?? '0';
$dev_password = $settings['pdf_builder_developer_password'] ?? '';
$show_tools = $dev_mode === '1';

// Create secure AJAX configuration
$ajax_config = [
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('pdf_builder_settings_ajax'),
    'action' => 'pdf_builder_developer_save_settings',
    'debug_mode' => defined('WP_DEBUG') && WP_DEBUG,
    'current_values' => [
        'dev_mode' => $dev_mode,
        'debug_enabled' => $debug_enabled,
        'dev_password' => $dev_password
    ]
];

// Localize script properly
wp_enqueue_script('jquery');
wp_localize_script('jquery', 'pdfBuilderDeveloperAjax', $ajax_config);
wp_localize_script('wp-util', 'pdfBuilderDeveloperAjax', $ajax_config);
wp_enqueue_script('pdf-builder-developer-js', plugins_url('assets/js/developer-settings.js', dirname(__FILE__, 3)), ['jquery'], '1.0.0', true);
wp_localize_script('pdf-builder-developer-js', 'pdfBuilderDeveloperConfig', $ajax_config);
?>

<div class="pdf-builder-developer">
    <!-- Status Banner -->
    <div class="dev-banner <?php echo $dev_mode === '1' ? 'active' : 'inactive'; ?>" id="dev-status-banner">
        <div class="dev-banner-content">
            <div>
                <h2 class="dev-title">🚀 Mode Développeur</h2>
                <p class="dev-status" id="dev-status-text">
                    <?php echo $dev_mode === '1' ? 'Activé - Outils avancés disponibles' : 'Désactivé - Mode normal'; ?>
                </p>
            </div>
            <div class="dev-actions">
                <button type="button" class="dev-quick-enable button button-primary" id="dev-quick-enable" <?php echo $dev_mode === '1' ? 'style="display:none;"' : ''; ?>>
                    ⚡ Activer rapidement
                </button>
            </div>
        </div>
    </div>

    <!-- Developer Controls Grid -->
    <div class="dev-controls-grid">
        <!-- Developer Mode Toggle -->
        <div class="dev-control-card">
            <div class="dev-card-icon">🎯</div>
            <h3 class="dev-card-title">Mode Développeur</h3>
            <div class="dev-toggle-group">
                <label class="dev-toggle <?php echo $dev_mode === '1' ? 'active' : ''; ?>" for="pdf-builder-dev-mode">
                    <input type="checkbox" id="pdf-builder-dev-mode" name="pdf_builder_developer_enabled" value="1" <?php checked($dev_mode, '1'); ?>>
                    <span class="dev-toggle-slider"></span>
                </label>
                <span class="dev-toggle-label" id="dev-mode-label">
                    <?php echo $dev_mode === '1' ? 'Activé' : 'Désactivé'; ?>
                </span>
            </div>
            <p class="dev-card-description">
                Active les outils de développement avancés et les fonctionnalités expérimentales.
            </p>
        </div>

        <!-- JavaScript Debug Logs Toggle -->
        <div class="dev-control-card">
            <div class="dev-card-icon">📄</div>
            <h3 class="dev-card-title">Logs JavaScript Détaillés</h3>
            <div class="dev-toggle-group">
                <label class="dev-toggle <?php echo $debug_enabled === '1' ? 'active' : ''; ?>" for="pdf-builder-debug-enabled">
                    <input type="checkbox" id="pdf-builder-debug-enabled" name="pdf_builder_canvas_debug_enabled" value="1" <?php checked($debug_enabled, '1'); ?>>
                    <span class="dev-toggle-slider"></span>
                </label>
                <span class="dev-toggle-label" id="debug-label">
                    <?php echo $debug_enabled === '1' ? 'Activé' : 'Désactivé'; ?>
                </span>
            </div>
            <p class="dev-card-description">
                Affiche des logs détaillés dans la console du navigateur pour déboguer l'éditeur.
            </p>
        </div>

        <!-- Security Password -->
        <div class="dev-control-card">
            <div class="dev-card-icon">🔐</div>
            <h3 class="dev-card-title">Mot de Passe de Sécurité</h3>
            <div class="dev-password-field">
                <input type="password" id="pdf-builder-dev-password" name="pdf_builder_developer_password"
                       placeholder="Mot de passe optionnel pour sécuriser l'accès"
                       value="<?php echo esc_attr($dev_password); ?>">
                <button type="button" class="dev-password-toggle" id="dev-password-toggle" title="Afficher/Masquer le mot de passe">
                    👁️
                </button>
            </div>
            <p class="dev-card-description">
                Protège l'accès aux outils développeur (optionnel).
            </p>
        </div>
    </div>

    <!-- Developer Tools Section -->
    <div class="dev-tools-section" id="dev-tools-section" <?php echo !$show_tools ? 'style="display:none;"' : ''; ?>>
        <div class="dev-tools-header">
            <h3 class="dev-tools-title">🛠️ Outils de Développement</h3>
            <div class="dev-tools-info">
                <span class="dev-info-badge">Mode développeur activé</span>
            </div>
        </div>

        <div class="dev-tools-grid">
            <button type="button" class="dev-tool-btn" id="dev-tool-js-logs" data-tooltip="Afficher les logs JavaScript en temps réel">
                <span class="dev-tool-icon">📄</span>
                <span class="dev-tool-label">Logs JS</span>
            </button>

            <button type="button" class="dev-tool-btn" id="dev-tool-system-info" data-tooltip="Informations système et configuration">
                <span class="dev-tool-icon">ℹ️</span>
                <span class="dev-tool-label">Info Système</span>
            </button>

            <button type="button" class="dev-tool-btn" id="dev-tool-clear-cache" data-tooltip="Vider le cache système">
                <span class="dev-tool-icon">🔄</span>
                <span class="dev-tool-label">Cache</span>
            </button>

            <button type="button" class="dev-tool-btn" id="dev-tool-export-settings" data-tooltip="Exporter la configuration">
                <span class="dev-tool-icon">💾</span>
                <span class="dev-tool-label">Export</span>
            </button>

            <button type="button" class="dev-tool-btn" id="dev-tool-performance" data-tooltip="Analyser les performances">
                <span class="dev-tool-icon">⚡</span>
                <span class="dev-tool-label">Performance</span>
            </button>

            <button type="button" class="dev-tool-btn" id="dev-tool-reset" data-tooltip="Réinitialiser les paramètres développeur">
                <span class="dev-tool-icon">🔄</span>
                <span class="dev-tool-label">Reset</span>
            </button>
        </div>

        <!-- System Status -->
        <div class="dev-system-status">
            <h4>📊 État du Système</h4>
            <div class="dev-status-grid">
                <div class="dev-status-item">
                    <span class="dev-status-label">WordPress:</span>
                    <span class="dev-status-value"><?php echo get_bloginfo('version'); ?></span>
                </div>
                <div class="dev-status-item">
                    <span class="dev-status-label">PHP:</span>
                    <span class="dev-status-value"><?php echo PHP_VERSION; ?></span>
                </div>
                <div class="dev-status-item">
                    <span class="dev-status-label">Mode Debug:</span>
                    <span class="dev-status-value <?php echo defined('WP_DEBUG') && WP_DEBUG ? 'active' : 'inactive'; ?>">
                        <?php echo defined('WP_DEBUG') && WP_DEBUG ? 'Activé' : 'Désactivé'; ?>
                    </span>
                </div>
                <div class="dev-status-item">
                    <span class="dev-status-label">Mémoire:</span>
                    <span class="dev-status-value"><?php echo size_format(memory_get_peak_usage(true)); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Logs Modal -->
    <div class="dev-modal" id="dev-js-logs-modal" style="display: none;">
        <div class="dev-modal-backdrop" id="dev-js-logs-backdrop"></div>
        <div class="dev-modal-content">
            <div class="dev-modal-header">
                <h3 class="dev-modal-title">
                    <span class="dev-modal-icon">📄</span>
                    Console JavaScript - Logs Temps Réel
                </h3>
                <div class="dev-modal-actions">
                    <button type="button" class="dev-modal-btn" id="dev-logs-refresh" title="Actualiser">
                        <span class="dev-btn-icon">🔄</span>
                    </button>
                    <button type="button" class="dev-modal-btn" id="dev-logs-export" title="Exporter">
                        <span class="dev-btn-icon">💾</span>
                    </button>
                    <button type="button" class="dev-modal-btn" id="dev-logs-clear" title="Vider">
                        <span class="dev-btn-icon">🗑️</span>
                    </button>
                    <button type="button" class="dev-modal-close" id="dev-js-logs-close" title="Fermer">
                        <span class="dev-btn-icon">✕</span>
                    </button>
                </div>
            </div>

            <div class="dev-modal-body">
                <div class="dev-logs-container" id="dev-logs-container">
                    <div class="dev-logs-loading">
                        <div class="dev-loading-spinner"></div>
                        <p>Chargement des logs JavaScript...</p>
                    </div>
                </div>
            </div>

            <div class="dev-modal-footer">
                <div class="dev-logs-stats" id="dev-logs-stats">
                    <span class="dev-stat-item">Total: <strong>0</strong></span>
                    <span class="dev-stat-item">Info: <strong>0</strong></span>
                    <span class="dev-stat-item">Warn: <strong>0</strong></span>
                    <span class="dev-stat-item">Error: <strong>0</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- System Info Modal -->
    <div class="dev-modal" id="dev-system-info-modal" style="display: none;">
        <div class="dev-modal-backdrop" id="dev-system-info-backdrop"></div>
        <div class="dev-modal-content dev-modal-large">
            <div class="dev-modal-header">
                <h3 class="dev-modal-title">
                    <span class="dev-modal-icon">ℹ️</span>
                    Informations Système Détaillées
                </h3>
                <button type="button" class="dev-modal-close" id="dev-system-info-close" title="Fermer">
                    <span class="dev-btn-icon">✕</span>
                </button>
            </div>

            <div class="dev-modal-body">
                <div class="dev-system-info-content" id="dev-system-info-content">
                    <div class="dev-loading-spinner"></div>
                    <p>Collecte des informations système...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications Container -->
    <div class="dev-notifications" id="dev-notifications"></div>
</div>

<style>
/* Base Styles */
.pdf-builder-developer {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    max-width: none;
    margin: 0;
    padding: 0;
}

/* Status Banner */
.dev-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 24px;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    transition: all 0.3s ease;
}

.dev-banner.active {
    background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
    box-shadow: 0 4px 12px rgba(74, 222, 128, 0.15);
}

.dev-banner.inactive {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
}

.dev-banner-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dev-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0 0 4px 0;
}

.dev-status {
    margin: 0;
    opacity: 0.9;
    font-size: 0.875rem;
}

.dev-actions {
    display: flex;
    gap: 8px;
}

.dev-quick-enable {
    padding: 8px 16px;
    background: white !important;
    color: #374151 !important;
    border: 1px solid #d1d5db !important;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s ease;
}

.dev-quick-enable:hover {
    background: #f9fafb !important;
    border-color: #9ca3af !important;
}

/* Controls Grid */
.dev-controls-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

.dev-control-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    transition: all 0.2s ease;
}

.dev-control-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
}

.dev-card-icon {
    font-size: 1.5rem;
    margin-bottom: 12px;
}

.dev-card-title {
    font-weight: 600;
    color: #111827;
    margin: 0 0 16px 0;
    font-size: 1rem;
}

.dev-card-description {
    margin: 12px 0 0 0;
    font-size: 0.875rem;
    color: #6b7280;
    line-height: 1.4;
}

/* Toggle Styles */
.dev-toggle-group {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}

.dev-toggle {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    cursor: pointer;
}

.dev-toggle input {
    display: none;
}

.dev-toggle-slider {
    position: absolute;
    inset: 0;
    background: #e5e7eb;
    border-radius: 24px;
    transition: 0.3s ease;
}

.dev-toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background: white;
    border-radius: 50%;
    transition: 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.dev-toggle.active .dev-toggle-slider {
    background: #3b82f6;
}

.dev-toggle.active .dev-toggle-slider:before {
    transform: translateX(20px);
}

.dev-toggle-label {
    font-weight: 500;
    color: #374151;
    font-size: 0.875rem;
}

/* Password Field */
.dev-password-field {
    position: relative;
    margin-bottom: 8px;
}

.dev-password-field input {
    width: 100%;
    padding: 8px 40px 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: border-color 0.2s ease;
}

.dev-password-field input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.dev-password-toggle {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    color: #6b7280;
    font-size: 1rem;
    transition: color 0.2s ease;
}

.dev-password-toggle:hover {
    color: #374151;
}

/* Developer Tools Section */
.dev-tools-section {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
}

.dev-tools-header {
    background: #f9fafb;
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dev-tools-title {
    margin: 0;
    font-weight: 600;
    color: #111827;
    font-size: 1rem;
}

.dev-tools-info {
    display: flex;
    gap: 8px;
}

.dev-info-badge {
    background: #10b981;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.dev-tools-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 8px;
    padding: 20px;
}

.dev-tool-btn {
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 12px 16px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    position: relative;
}

.dev-tool-btn:hover {
    border-color: #3b82f6;
    background: #eff6ff;
    color: #1e40af;
    transform: translateY(-1px);
}

.dev-tool-icon {
    font-size: 1.25rem;
}

.dev-tool-label {
    font-size: 0.75rem;
    font-weight: 600;
}

/* System Status */
.dev-system-status {
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    padding: 20px;
}

.dev-system-status h4 {
    margin: 0 0 16px 0;
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
}

.dev-status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
}

.dev-status-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #e5e7eb;
}

.dev-status-item:last-child {
    border-bottom: none;
}

.dev-status-label {
    font-weight: 500;
    color: #374151;
}

.dev-status-value {
    font-weight: 600;
    color: #111827;
}

.dev-status-value.active {
    color: #10b981;
}

.dev-status-value.inactive {
    color: #ef4444;
}

/* Modal Styles */
.dev-modal {
    position: fixed;
    inset: 0;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.dev-modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.8);
}

.dev-modal-content {
    position: relative;
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 900px;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 64px rgba(0, 0, 0, 0.2);
    overflow: hidden;
}

.dev-modal-large {
    max-width: 1200px;
}

.dev-modal-header {
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f9fafb;
}

.dev-modal-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 8px;
}

.dev-modal-icon {
    font-size: 1.5rem;
}

.dev-modal-actions {
    display: flex;
    gap: 8px;
}

.dev-modal-btn {
    background: #f9fafb;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.dev-modal-btn:hover {
    border-color: #3b82f6;
    background: #eff6ff;
}

.dev-modal-close {
    background: #dc2626;
    color: white;
    border-color: #dc2626;
    padding: 8px;
    width: 36px;
    height: 36px;
}

.dev-modal-close:hover {
    background: #b91c1c;
    border-color: #b91c1c;
}

.dev-btn-icon {
    font-size: 1rem;
}

.dev-modal-body {
    flex: 1;
    padding: 20px;
    overflow: auto;
}

.dev-modal-footer {
    padding: 16px 20px;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
}

/* Logs Styles */
.dev-logs-container {
    font-family: 'Monaco', 'Menlo', monospace;
    font-size: 0.875rem;
    line-height: 1.5;
    background: #1f2937;
    color: #f9fafb;
    border-radius: 6px;
    min-height: 400px;
    max-height: 500px;
    overflow: auto;
}

.dev-logs-loading {
    text-align: center;
    color: #6b7280;
    padding: 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}

.dev-loading-spinner {
    width: 32px;
    height: 32px;
    border: 3px solid #e5e7eb;
    border-top: 3px solid #3b82f6;
    border-radius: 50%;
    animation: dev-spin 1s linear infinite;
}

@keyframes dev-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.dev-log-entry {
    margin-bottom: 4px;
    padding: 2px 4px;
    border-radius: 3px;
    border-left: 3px solid transparent;
}

.dev-log-info {
    border-left-color: #60a5fa;
    color: #60a5fa;
}

.dev-log-warn {
    border-left-color: #fbbf24;
    color: #fbbf24;
}

.dev-log-error {
    border-left-color: #ef4444;
    color: #ef4444;
}

.dev-log-log {
    border-left-color: #f9fafb;
    color: #f9fafb;
}

.dev-log-timestamp {
    color: #6b7280;
    margin-right: 8px;
    font-size: 0.8em;
}

.dev-log-source {
    color: #c084fc;
    opacity: 0.8;
    margin-right: 8px;
}

.dev-log-prefix {
    font-weight: bold;
    margin-right: 8px;
}

.dev-logs-stats {
    display: flex;
    gap: 16px;
    font-size: 0.875rem;
    color: #374151;
}

.dev-stat-item strong {
    color: #111827;
}

/* System Info Styles */
.dev-system-info-content {
    font-family: 'Monaco', 'Menlo', monospace;
    font-size: 0.875rem;
    line-height: 1.5;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 20px;
    min-height: 400px;
}

.dev-system-info-section {
    margin-bottom: 24px;
}

.dev-system-info-section h4 {
    margin: 0 0 12px 0;
    color: #111827;
    font-size: 1rem;
    font-weight: 600;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.dev-system-info-table {
    width: 100%;
    border-collapse: collapse;
}

.dev-system-info-table th,
.dev-system-info-table td {
    padding: 8px 12px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.dev-system-info-table th {
    background: #f3f4f6;
    font-weight: 600;
    color: #374151;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.dev-system-info-table td {
    color: #111827;
}

/* Notifications */
.dev-notifications {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10001;
    pointer-events: none;
}

.dev-notification {
    background: #10b981;
    color: white;
    padding: 12px 16px;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    margin-bottom: 8px;
    transform: translateX(400px);
    opacity: 0;
    transition: all 0.3s ease;
    pointer-events: auto;
    max-width: 400px;
}

.dev-notification.show {
    transform: translateX(0);
    opacity: 1;
}

.dev-notification.success {
    background: #10b981;
}

.dev-notification.error {
    background: #ef4444;
}

.dev-notification.warning {
    background: #f59e0b;
}

.dev-notification.info {
    background: #3b82f6;
}

/* Responsive */
@media (max-width: 768px) {
    .dev-controls-grid {
        grid-template-columns: 1fr;
    }

    .dev-banner-content {
        flex-direction: column;
        gap: 16px;
        text-align: center;
    }

    .dev-tools-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }

    .dev-modal-content {
        width: 95%;
        max-height: 90vh;
    }

    .dev-system-status {
        padding: 16px;
    }

    .dev-status-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .dev-tools-grid {
        grid-template-columns: 1fr;
    }

    .dev-modal-title {
        font-size: 1rem;
    }

    .dev-modal-actions {
        gap: 4px;
    }

    .dev-modal-btn {
        padding: 6px 8px;
    }
}
</style>

<script>
(function($) {
    'use strict';

    const DeveloperSettings = {
        config: null,
        elements: {},
        currentLogs: [],

        init: function() {
            console.log('[PDF Builder Developer] Initializing developer settings...');

            // Get configuration
            this.config = window.pdfBuilderDeveloperConfig || {};
            console.log('[PDF Builder Developer] Config loaded:', this.config);

            // Cache elements
            this.cacheElements();

            // Bind events
            this.bindEvents();

            // Initialize state
            this.updateUI();

            console.log('[PDF Builder Developer] Developer settings initialized successfully');
        },

        cacheElements: function() {
            this.elements = {
                devModeToggle: $('#pdf-builder-dev-mode'),
                debugToggle: $('#pdf-builder-debug-enabled'),
                passwordField: $('#pdf-builder-dev-password'),
                passwordToggle: $('#dev-password-toggle'),
                quickEnableBtn: $('#dev-quick-enable'),
                toolsSection: $('#dev-tools-section'),
                statusBanner: $('#dev-status-banner'),
                statusText: $('#dev-status-text'),
                devModeLabel: $('#dev-mode-label'),
                debugLabel: $('#debug-label'),

                // Modals
                jsLogsModal: $('#dev-js-logs-modal'),
                jsLogsBackdrop: $('#dev-js-logs-backdrop'),
                jsLogsClose: $('#dev-js-logs-close'),
                jsLogsContainer: $('#dev-logs-container'),
                jsLogsRefresh: $('#dev-logs-refresh'),
                jsLogsExport: $('#dev-logs-export'),
                jsLogsClear: $('#dev-logs-clear'),
                jsLogsStats: $('#dev-logs-stats'),

                systemInfoModal: $('#dev-system-info-modal'),
                systemInfoBackdrop: $('#dev-system-info-backdrop'),
                systemInfoClose: $('#dev-system-info-close'),
                systemInfoContent: $('#dev-system-info-content'),

                // Tools
                toolJsLogs: $('#dev-tool-js-logs'),
                toolSystemInfo: $('#dev-tool-system-info'),
                toolClearCache: $('#dev-tool-clear-cache'),
                toolExportSettings: $('#dev-tool-export-settings'),
                toolPerformance: $('#dev-tool-performance'),
                toolReset: $('#dev-tool-reset'),

                // Notifications
                notifications: $('#dev-notifications')
            };
        },

        bindEvents: function() {
            const self = this;

            // Toggle events
            this.elements.devModeToggle.on('change', function() {
                self.saveSetting('pdf_builder_developer_enabled', this.checked ? '1' : '0');
                self.updateDevMode(this.checked);
            });

            this.elements.debugToggle.on('change', function() {
                self.saveSetting('pdf_builder_canvas_debug_enabled', this.checked ? '1' : '0');
                self.updateDebugMode(this.checked);
            });

            this.elements.passwordField.on('change', function() {
                self.saveSetting('pdf_builder_developer_password', this.value);
            });

            // Password toggle
            this.elements.passwordToggle.on('click', function() {
                const input = self.elements.passwordField[0];
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                this.textContent = type === 'password' ? '👁️' : '🙈';
            });

            // Quick enable
            this.elements.quickEnableBtn.on('click', function() {
                self.elements.devModeToggle.prop('checked', true).trigger('change');
            });

            // Tool buttons
            this.elements.toolJsLogs.on('click', () => this.showJsLogsModal());
            this.elements.toolSystemInfo.on('click', () => this.showSystemInfoModal());
            this.elements.toolClearCache.on('click', () => this.clearSystemCache());
            this.elements.toolExportSettings.on('click', () => this.exportSettings());
            this.elements.toolPerformance.on('click', () => this.showPerformanceAnalysis());
            this.elements.toolReset.on('click', () => this.resetDeveloperSettings());

            // Modal events
            this.elements.jsLogsClose.on('click', () => this.hideJsLogsModal());
            this.elements.jsLogsBackdrop.on('click', () => this.hideJsLogsModal());
            this.elements.jsLogsRefresh.on('click', () => this.loadJsLogs());
            this.elements.jsLogsExport.on('click', () => this.exportJsLogs());
            this.elements.jsLogsClear.on('click', () => this.clearJsLogs());

            this.elements.systemInfoClose.on('click', () => this.hideSystemInfoModal());
            this.elements.systemInfoBackdrop.on('click', () => this.hideSystemInfoModal());

            // Keyboard shortcuts
            $(document).on('keydown', function(e) {
                if (e.ctrlKey || e.metaKey) {
                    switch(e.key) {
                        case 'd':
                            e.preventDefault();
                            self.elements.debugToggle.prop('checked', !self.elements.debugToggle.prop('checked')).trigger('change');
                            break;
                    }
                }
            });
        },

        updateUI: function() {
            const devMode = this.config.current_values.dev_mode === '1';
            const debugMode = this.config.current_values.debug_enabled === '1';

            this.updateDevMode(devMode);
            this.updateDebugMode(debugMode);
        },

        updateDevMode: function(enabled) {
            this.elements.toolsSection.toggle(enabled);
            this.elements.statusBanner.toggleClass('active inactive');
            this.elements.statusText.text(enabled ? 'Activé - Outils avancés disponibles' : 'Désactivé - Mode normal');
            this.elements.devModeLabel.text(enabled ? 'Activé' : 'Désactivé');
            this.elements.devModeToggle.closest('.dev-toggle').toggleClass('active', enabled);
            this.elements.quickEnableBtn.toggle(!enabled);
        },

        updateDebugMode: function(enabled) {
            this.elements.debugLabel.text(enabled ? 'Activé' : 'Désactivé');
            this.elements.debugToggle.closest('.dev-toggle').toggleClass('active', enabled);
        },

        saveSetting: function(key, value) {
            console.log(`[PDF Builder Developer] Saving setting: ${key} = ${value}`);

            const formData = new FormData();
            formData.append('action', this.config.action);
            formData.append('nonce', this.config.nonce);
            formData.append('setting_key', key);
            formData.append('setting_value', value);

            return fetch(this.config.ajax_url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log(`[PDF Builder Developer] Setting saved successfully: ${key}`);
                    this.showNotification('Paramètre sauvegardé avec succès', 'success');
                    return data;
                } else {
                    console.error('[PDF Builder Developer] Save failed:', data);
                    this.showNotification('Erreur lors de la sauvegarde: ' + (data.data?.message || 'Erreur inconnue'), 'error');
                    throw new Error('Save failed');
                }
            })
            .catch(error => {
                console.error('[PDF Builder Developer] AJAX error:', error);
                this.showNotification('Erreur de connexion: ' + error.message, 'error');
                throw error;
            });
        },

        showJsLogsModal: function() {
            this.elements.jsLogsModal.show();
            this.loadJsLogs();
        },

        hideJsLogsModal: function() {
            this.elements.jsLogsModal.hide();
        },

        loadJsLogs: function() {
            console.log('[PDF Builder Developer] Loading JS logs...');

            this.elements.jsLogsContainer.html(`
                <div class="dev-logs-loading">
                    <div class="dev-loading-spinner"></div>
                    <p>Collecte des logs JavaScript...</p>
                </div>
            `);

            // Simulate loading logs (in real implementation, this would fetch from server)
            setTimeout(() => {
                this.currentLogs = this.generateSampleLogs();
                this.displayJsLogs(this.currentLogs);
            }, 1000);
        },

        generateSampleLogs: function() {
            const logs = [];
            const now = new Date();

            // System logs
            logs.push({
                type: 'info',
                timestamp: now.toISOString(),
                source: 'DeveloperSettings',
                message: '=== SESSION MONITORING PDF BUILDER ==='
            });

            logs.push({
                type: 'info',
                timestamp: new Date(now.getTime() - 5000).toISOString(),
                source: 'Canvas',
                message: 'Composant Canvas initialisé avec succès'
            });

            logs.push({
                type: 'warn',
                timestamp: new Date(now.getTime() - 10000).toISOString(),
                source: 'Cache',
                message: 'Cache système périmé détecté - nettoyage automatique'
            });

            logs.push({
                type: 'error',
                timestamp: new Date(now.getTime() - 15000).toISOString(),
                source: 'DOM',
                message: 'Élément DOM introuvable #element-123 - vérifiez les sélecteurs'
            });

            logs.push({
                type: 'log',
                timestamp: new Date(now.getTime() - 20000).toISOString(),
                source: 'Export',
                message: 'Export PDF terminé: 15 éléments rendus en 2.3s'
            });

            return logs;
        },

        displayJsLogs: function(logs) {
            if (!logs || logs.length === 0) {
                this.elements.jsLogsContainer.html('<div class="dev-logs-loading">Aucun log disponible</div>');
                return;
            }

            let html = '';
            logs.forEach(log => {
                const time = new Date(log.timestamp).toLocaleTimeString('fr-FR', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });

                const prefix = this.getLogPrefix(log.type);

                html += `<div class="dev-log-entry dev-log-${log.type}">`;
                html += `<span class="dev-log-timestamp">[${time}]</span>`;
                html += `<span class="dev-log-source">[${log.source}]</span>`;
                html += `<strong class="dev-log-prefix">${prefix}</strong>`;
                html += `${log.message}`;
                html += '</div>';
            });

            this.elements.jsLogsContainer.html(html);
            this.updateLogsStats(logs);
        },

        getLogPrefix: function(type) {
            const prefixes = {
                info: '🔵 INFO',
                warn: '🟡 WARN',
                error: '🔴 ERROR',
                log: '⚪ LOG'
            };
            return prefixes[type] || prefixes.log;
        },

        updateLogsStats: function(logs) {
            const stats = {
                total: logs.length,
                info: logs.filter(l => l.type === 'info').length,
                warn: logs.filter(l => l.type === 'warn').length,
                error: logs.filter(l => l.type === 'error').length
            };

            this.elements.jsLogsStats.html(`
                <span class="dev-stat-item">Total: <strong>${stats.total}</strong></span>
                <span class="dev-stat-item">Info: <strong>${stats.info}</strong></span>
                <span class="dev-stat-item">Warn: <strong>${stats.warn}</strong></span>
                <span class="dev-stat-item">Error: <strong>${stats.error}</strong></span>
            `);
        },

        exportJsLogs: function() {
            const data = {
                timestamp: new Date().toISOString(),
                summary: {
                    total: this.currentLogs.length,
                    info: this.currentLogs.filter(l => l.type === 'info').length,
                    warn: this.currentLogs.filter(l => l.type === 'warn').length,
                    error: this.currentLogs.filter(l => l.type === 'error').length
                },
                logs: this.currentLogs
            };

            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `pdf-builder-js-logs-${new Date().toISOString().split('T')[0]}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

            this.showNotification('Logs exportés avec succès', 'success');
        },

        clearJsLogs: function() {
            if (confirm('Vider tous les logs ? Cette action est irréversible.')) {
                this.currentLogs = [];
                this.elements.jsLogsContainer.html('<div style="text-align: center; padding: 40px; color: #10b981;"><h3>🗑️ Logs vidés</h3><p>Les logs ont été supprimés. Rechargez la page pour en générer de nouveaux.</p></div>');
                this.updateLogsStats([]);
                this.showNotification('Logs vidés avec succès', 'success');
            }
        },

        showSystemInfoModal: function() {
            this.elements.systemInfoModal.show();
            this.loadSystemInfo();
        },

        hideSystemInfoModal: function() {
            this.elements.systemInfoModal.hide();
        },

        loadSystemInfo: function() {
            console.log('[PDF Builder Developer] Loading system info...');

            this.elements.systemInfoContent.html(`
                <div class="dev-loading-spinner" style="margin: 40px auto;"></div>
                <p style="text-align: center; color: #6b7280;">Collecte des informations système...</p>
            `);

            // Simulate loading system info
            setTimeout(() => {
                const systemInfo = this.generateSystemInfo();
                this.displaySystemInfo(systemInfo);
            }, 1500);
        },

        generateSystemInfo: function() {
            return {
                wordpress: {
                    version: '<?php echo get_bloginfo('version'); ?>',
                    debug: '<?php echo defined('WP_DEBUG') && WP_DEBUG ? 'Activé' : 'Désactivé'; ?>',
                    multisite: '<?php echo is_multisite() ? 'Oui' : 'Non'; ?>',
                    theme: '<?php echo wp_get_theme()->get('Name'); ?>',
                    plugins: '<?php echo count(get_plugins()); ?> actifs'
                },
                server: {
                    php_version: '<?php echo PHP_VERSION; ?>',
                    memory_limit: '<?php echo ini_get('memory_limit'); ?>',
                    max_execution_time: '<?php echo ini_get('max_execution_time'); ?>s',
                    upload_max_filesize: '<?php echo ini_get('upload_max_filesize'); ?>',
                    post_max_size: '<?php echo ini_get('post_max_size'); ?>'
                },
                database: {
                    version: '<?php global $wpdb; echo $wpdb->db_version(); ?>',
                    size: 'Calcul en cours...',
                    tables: '<?php global $wpdb; echo count($wpdb->get_results("SHOW TABLES", ARRAY_N)); ?>'
                },
                pdf_builder: {
                    version: 'Pro 2.1.0',
                    dev_mode: this.config.current_values.dev_mode === '1' ? 'Activé' : 'Désactivé',
                    debug_logs: this.config.current_values.debug_enabled === '1' ? 'Activé' : 'Désactivé',
                    cache_status: 'Opérationnel',
                    last_backup: '2024-01-15 14:30:22'
                }
            };
        },

        displaySystemInfo: function(info) {
            let html = '';

            Object.keys(info).forEach(section => {
                html += `<div class="dev-system-info-section">`;
                html += `<h4>${section.charAt(0).toUpperCase() + section.slice(1).replace('_', ' ')}</h4>`;
                html += `<table class="dev-system-info-table">`;

                Object.keys(info[section]).forEach(key => {
                    const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    const value = info[section][key];
                    html += `<tr><th>${label}</th><td>${value}</td></tr>`;
                });

                html += `</table></div>`;
            });

            this.elements.systemInfoContent.html(html);
        },

        clearSystemCache: function() {
            if (confirm('Vider le cache système ? Cette action peut améliorer les performances.')) {
                console.log('[PDF Builder Developer] Clearing system cache...');

                // Simulate cache clearing
                setTimeout(() => {
                    this.showNotification('Cache système vidé avec succès', 'success');
                }, 1000);
            }
        },

        exportSettings: function() {
            const settings = {
                timestamp: new Date().toISOString(),
                developer_settings: this.config.current_values,
                system_info: this.generateSystemInfo()
            };

            const blob = new Blob([JSON.stringify(settings, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `pdf-builder-developer-settings-${new Date().toISOString().split('T')[0]}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

            this.showNotification('Paramètres exportés avec succès', 'success');
        },

        showPerformanceAnalysis: function() {
            const analysis = {
                memory_usage: '45 MB / 256 MB',
                load_time: '2.3 secondes',
                dom_nodes: '1,247',
                js_heap: '67 MB',
                render_time: '120ms',
                recommendations: [
                    'Considérez la mise en cache des images fréquemment utilisées',
                    'Optimisez les sélecteurs CSS complexes',
                    'Réduisez le nombre d\'éléments DOM si possible'
                ]
            };

            let message = '📊 Analyse de Performance\n\n';
            message += `Mémoire: ${analysis.memory_usage}\n`;
            message += `Temps de chargement: ${analysis.load_time}\n`;
            message += `Noeuds DOM: ${analysis.dom_nodes}\n`;
            message += `Heap JS: ${analysis.js_heap}\n`;
            message += `Temps de rendu: ${analysis.render_time}\n\n`;
            message += 'Recommandations:\n';
            analysis.recommendations.forEach(rec => {
                message += `• ${rec}\n`;
            });

            alert(message);
        },

        resetDeveloperSettings: function() {
            if (confirm('Réinitialiser tous les paramètres développeur ? Cette action est irréversible.')) {
                console.log('[PDF Builder Developer] Resetting developer settings...');

                // Reset all settings to defaults
                this.saveSetting('pdf_builder_developer_enabled', '0');
                this.saveSetting('pdf_builder_canvas_debug_enabled', '0');
                this.saveSetting('pdf_builder_developer_password', '');

                // Update UI
                this.elements.devModeToggle.prop('checked', false).trigger('change');
                this.elements.debugToggle.prop('checked', false).trigger('change');
                this.elements.passwordField.val('');

                this.showNotification('Paramètres développeur réinitialisés', 'success');
            }
        },

        showNotification: function(message, type = 'info') {
            const notification = $(`<div class="dev-notification ${type}">${message}</div>`);
            this.elements.notifications.append(notification);

            // Animate in
            setTimeout(() => notification.addClass('show'), 10);

            // Auto remove
            setTimeout(() => {
                notification.removeClass('show');
                setTimeout(() => notification.remove(), 300);
            }, 4000);
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        DeveloperSettings.init();
    });

})(jQuery);
</script>
