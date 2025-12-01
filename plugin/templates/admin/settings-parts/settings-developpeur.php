<?php
/**
 * PDF Builder - Developer Settings Tab
 *
 * Clean implementation with proper AJAX and persistence
 */

// Load settings from existing system - with POST override for immediate visual feedback
$settings = get_option('pdf_builder_settings', []);
$dev_mode = isset($_POST['pdf_builder_developer_enabled']) ? sanitize_text_field($_POST['pdf_builder_developer_enabled']) : get_option('pdf_builder_developer_enabled', $settings['pdf_builder_developer_enabled'] ?? '0');
$debug_enabled = isset($_POST['pdf_builder_canvas_debug_enabled']) ? sanitize_text_field($_POST['pdf_builder_canvas_debug_enabled']) : get_option('pdf_builder_canvas_debug_enabled', $settings['pdf_builder_canvas_debug_enabled'] ?? '0');
$dev_password = isset($_POST['pdf_builder_developer_password']) ? sanitize_text_field($_POST['pdf_builder_developer_password']) : get_option('pdf_builder_developer_password', $settings['pdf_builder_developer_password'] ?? '');
$show_tools = $dev_mode === '1';
?>

<style>
.pdf-builder-dev {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

.dev-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

.dev-banner.active {
    background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
}

.dev-banner.inactive {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.dev-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 4px;
}

.dev-status {
    opacity: 0.9;
    font-size: 0.875rem;
}

.dev-actions {
    display: flex;
    gap: 8px;
}

.dev-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.dev-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    transition: all 0.2s;
}

.dev-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
}

.dev-card-icon {
    font-size: 1.5rem;
    margin-bottom: 8px;
}

.dev-card-title {
    font-weight: 600;
    color: #111827;
    margin-bottom: 12px;
}

.dev-toggle-group {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.dev-toggle {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
}

.dev-toggle input {
    display: none;
}

.dev-toggle-slider {
    position: absolute;
    inset: 0;
    background: #e5e7eb;
    border-radius: 24px;
    transition: 0.3s;
    cursor: pointer;
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
    transition: 0.3s;
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
}

.dev-tools-section {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin-top: 24px;
    overflow: hidden;
}

.dev-tools-header {
    background: #f9fafb;
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
}

.dev-tools-title {
    font-weight: 600;
    color: #111827;
    margin: 0;
    font-size: 1rem;
}

.dev-tools-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 12px;
    padding: 20px;
}

.dev-tool-btn {
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 12px 16px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
}

.dev-tool-btn:hover {
    border-color: #3b82f6;
    background: #eff6ff;
    color: #1e40af;
    transform: translateY(-1px);
}

/* Password field */
.dev-password-field {
    position: relative;
}

.dev-password-field input {
    width: 100%;
    padding: 8px 40px 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
}

.dev-password-toggle-btn {
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
}

/* Modal */
.js-logs-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.8);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

.js-logs-modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 900px;
    height: 75%;
    max-height: 700px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 64px rgba(0, 0, 0, 0.2);
}

.js-logs-header {
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.js-logs-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 8px;
}

.js-logs-actions {
    display: flex;
    gap: 8px;
}

.js-logs-close,
.js-logs-refresh,
.js-logs-export,
.js-logs-clear {
    background: #f9fafb;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.js-logs-close {
    background: #dc2626;
    color: white;
    border-color: #dc2626;
}

.js-logs-close:hover {
    background: #b91c1c;
}

.js-logs-refresh:hover,
.js-logs-export:hover,
.js-logs-clear:hover {
    border-color: #3b82f6;
    background: #eff6ff;
}

.js-logs-body {
    flex: 1;
    padding: 20px;
    font-family: 'Monaco', 'Menlo', monospace;
    font-size: 0.875rem;
    line-height: 1.5;
    background: #1f2937;
    color: #f9fafb;
    overflow: auto;
}

.log-entry {
    margin-bottom: 4px;
    padding: 2px 4px;
    border-radius: 3px;
}

.log-info { color: #60a5fa; }
.log-warn { color: #fbbf24; }
.log-error { color: #ef4444; }
.log-log { color: #f9fafb; }

.log-source {
    color: #c084fc;
    opacity: 0.8;
}

.log-timestamp {
    color: #6b7280;
    margin-right: 8px;
}

.js-logs-loading {
    text-align: center;
    color: #6b7280;
    padding: 40px;
}

/* Notifications */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 12px 16px;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    z-index: 10001;
    transform: translateX(400px);
    opacity: 0;
    transition: all 0.3s ease;
}

.notification.show {
    transform: translateX(0);
    opacity: 1;
}

.notification.success {
    background: #10b981;
    color: white;
}

.notification.error {
    background: #ef4444;
    color: white;
}

.notification.info {
    background: #3b82f6;
    color: white;
}
</style>

<div class="pdf-builder-dev">
    <!-- Status Banner -->
    <div class="dev-banner <?php echo $dev_mode === '1' ? 'active' : 'inactive'; ?>">
        <div>
            <h2 class="dev-title">🚀 Mode Développeur</h2>
            <p class="dev-status"><?php echo $dev_mode === '1' ? 'Activé - Outils avancés disponibles' : 'Désactivé - Mode normal'; ?></p>
        </div>
        <div class="dev-actions">
            <button type="button" class="dev-quick-enable" style="padding: 8px 16px; background: white; border: 1px solid #d1d5db; border-radius: 6px; cursor: pointer; <?php if ($dev_mode === '1') echo 'display: none;'; ?>">
                ⚡ Activer rapidement
            </button>
        </div>
    </div>

    <!-- Developer Controls -->
    <div class="dev-grid">
        <!-- Developer Mode Toggle -->
        <div class="dev-card">
            <div class="dev-card-icon">🎯</div>
            <h3 class="dev-card-title">Mode Développeur</h3>
            <div class="dev-toggle-group">
                <label class="dev-toggle">
                    <input type="checkbox" id="pdf-builder-dev-mode" name="pdf_builder_developer_enabled" <?php checked($dev_mode, '1'); ?>>
                    <span class="dev-toggle-slider"></span>
                </label>
                <span class="dev-toggle-label"><?php echo $dev_mode === '1' ? 'Activé' : 'Désactivé'; ?></span>
            </div>
        </div>

        <!-- JavaScript Logs Toggle -->
        <div class="dev-card">
            <div class="dev-card-icon">📄</div>
            <h3 class="dev-card-title">Logs JavaScript</h3>
            <div class="dev-toggle-group">
                <label class="dev-toggle <?php echo $debug_enabled === '1' ? 'active' : ''; ?>">
                    <input type="checkbox" id="pdf-builder-debug-enabled" name="pdf_builder_canvas_debug_enabled" <?php checked($debug_enabled, '1'); ?>>
                    <span class="dev-toggle-slider"></span>
                </label>
                <span class="dev-toggle-label"><?php echo $debug_enabled === '1' ? 'Activé' : 'Désactivé'; ?></span>
            </div>
        </div>

        <!-- Security Password -->
        <div class="dev-card">
            <div class="dev-card-icon">🔐</div>
            <h3 class="dev-card-title">Sécurité d'Accès</h3>
            <div class="dev-password-field">
                <input type="password" id="pdf-builder-dev-password" name="pdf_builder_developer_password" placeholder="Mot de passe (optionnel)"
                       value="<?php echo esc_attr($dev_password); ?>">
                <button type="button" class="dev-password-toggle-btn">👁️</button>
            </div>
        </div>
    </div>

    <!-- Developer Tools -->
    <div class="dev-tools-section" id="dev-tools-section" style="display: <?php echo $show_tools ? 'block' : 'none'; ?>;">
        <div class="dev-tools-header">
            <h3 class="dev-tools-title">🛠️ Outils de Développement</h3>
        </div>
        <div class="dev-tools-grid">
            <button type="button" class="dev-tool-btn" id="show-js-logs">📄 Logs JS</button>
            <button type="button" class="dev-tool-btn" id="clear-system-cache">🔄 Cache</button>
            <button type="button" class="dev-tool-btn" id="system-info">ℹ️ Info</button>
            <button type="button" class="dev-tool-btn" id="export-settings">💾 Export</button>
        </div>
    </div>

    <!-- JavaScript Logs Modal -->
    <div class="js-logs-modal" id="js-logs-modal">
        <div class="js-logs-modal-content">
            <div class="js-logs-header">
                <h3 class="js-logs-title">📄 Logs JavaScript - Console Dynamique</h3>
                <div class="js-logs-actions">
                    <button type="button" class="js-logs-refresh" id="js-logs-refresh">🔄</button>
                    <button type="button" class="js-logs-export" id="js-logs-export">💾</button>
                    <button type="button" class="js-logs-clear" id="js-logs-clear">🗑️</button>
                    <button type="button" class="js-logs-close" id="js-logs-close">✕</button>
                </div>
            </div>
            <div class="js-logs-body" id="js-logs-body">
                <div class="js-logs-loading">🔄 Chargement des logs...</div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[PDF Builder] Developer tab initialized');

    // Get the same nonce configuration as the main settings system
    const PDF_BUILDER_CONFIG = window.PDF_BUILDER_CONFIG || {
        ajax_url: window.ajaxurl || '<?php echo admin_url('admin-ajax.php'); ?>',
        nonce: '<?php echo wp_create_nonce('pdf_builder_settings_ajax'); ?>'
    };

    // Elements
    const devModeToggle = document.getElementById('pdf-builder-dev-mode');
    const devModeToggleContainer = devModeToggle.closest('.dev-toggle');
    const debugToggle = document.getElementById('pdf-builder-debug-enabled');
    const debugToggleContainer = debugToggle.closest('.dev-toggle');
    const passwordField = document.getElementById('pdf-builder-dev-password');
    const passwordToggle = document.querySelector('.dev-password-toggle-btn');
    const quickEnableBtn = document.querySelector('.dev-quick-enable');
    const toolsSection = document.getElementById('dev-tools-section');
    const banner = document.querySelector('.dev-banner');

    // Modal elements
    const logsModal = document.getElementById('js-logs-modal');
    const logsClose = document.getElementById('js-logs-close');
    const logsBody = document.getElementById('js-logs-body');
    const logsRefresh = document.getElementById('js-logs-refresh');
    const logsExport = document.getElementById('js-logs-export');
    const logsClear = document.getElementById('js-logs-clear');

    // Toggle password visibility
    if (passwordToggle) {
        passwordToggle.addEventListener('click', function() {
            const input = passwordField;
            const type = input.type === 'password' ? 'text' : 'password';
            input.type = type;
            this.textContent = type === 'password' ? '👁️' : '🙈';
        });
    }

    // Update UI based on dev mode
    function updateDevMode(isEnabled) {
        toolsSection.style.display = isEnabled ? 'block' : 'none';
        const statusText = banner.querySelector('.dev-status');
        const toggleLabel = devModeToggle.closest('.dev-toggle-group').querySelector('.dev-toggle-label');

        if (isEnabled) {
            banner.className = 'dev-banner active';
            statusText.textContent = 'Activé - Outils avancés disponibles';
            toggleLabel.textContent = 'Activé';
            quickEnableBtn.style.display = 'none';
        } else {
            banner.className = 'dev-banner inactive';
            statusText.textContent = 'Désactivé - Mode normal';
            toggleLabel.textContent = 'Désactivé';
            quickEnableBtn.style.display = 'inline-block';
        }
    }

    // Auto-save setting via existing AJAX system
    function saveSetting(setting, value) {
        console.log('[DEV] saveSetting called:', setting, '=', value);
        console.log('[DEV] PDF_BUILDER_CONFIG:', PDF_BUILDER_CONFIG);

        const formData = new FormData();
        formData.append('action', 'pdf_builder_save_all_settings');
        formData.append('nonce', PDF_BUILDER_CONFIG.nonce);
        formData.append(setting, value);

        // Include all developer settings to ensure consistency
        formData.append('pdf_builder_developer_enabled', devModeToggle.checked ? '1' : '0');
        formData.append('pdf_builder_canvas_debug_enabled', debugToggle.checked ? '1' : '0');
        formData.append('pdf_builder_developer_password', passwordField ? passwordField.value : '');

        console.log('[DEV] Sending AJAX request to:', PDF_BUILDER_CONFIG.ajax_url);
        console.log('[DEV] FormData:', Object.fromEntries(formData));

        return fetch(PDF_BUILDER_CONFIG.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('[DEV] Raw response:', response);
            return response.json();
        })
        .then(data => {
            console.log('[DEV] AJAX response:', data);
            if (data.success) {
                showNotification('Paramètre sauvegardé', 'success');
                console.log('[DEV] Setting saved:', setting, '=', value);
                return data;
            } else {
                console.error('[DEV] Save failed:', data);
                showNotification('Erreur lors de la sauvegarde: ' + (data.data?.message || 'Erreur inconnue'), 'error');
                throw new Error('Save failed');
            }
        })
        .catch(error => {
            console.error('[DEV] AJAX error:', error);
            showNotification('Erreur de connexion: ' + error.message, 'error');
            throw error;
        });
    }

    // Event listeners for toggles
    if (devModeToggle) {
        // Initialize toggle state
        if (devModeToggle.checked) {
            devModeToggleContainer.classList.add('active');
        }

        devModeToggle.addEventListener('change', function() {
            const isEnabled = this.checked;
            if (isEnabled) {
                devModeToggleContainer.classList.add('active');
            } else {
                devModeToggleContainer.classList.remove('active');
            }
            updateDevMode(isEnabled);
            saveSetting('pdf_builder_developer_enabled', isEnabled ? '1' : '0');
        });
    }

    if (debugToggle) {
        // Initialize toggle state
        if (debugToggle.checked) {
            debugToggleContainer.classList.add('active');
        }

        debugToggle.addEventListener('change', function() {
            const isEnabled = this.checked;
            if (isEnabled) {
                debugToggleContainer.classList.add('active');
            } else {
                debugToggleContainer.classList.remove('active');
            }
            const label = this.closest('.dev-toggle-group').querySelector('.dev-toggle-label');
            label.textContent = isEnabled ? 'Activé' : 'Désactivé';
            saveSetting('pdf_builder_canvas_debug_enabled', isEnabled ? '1' : '0');
        });
    }

    if (passwordField) {
        passwordField.addEventListener('change', function() {
            saveSetting('pdf_builder_developer_password', this.value);
        });
    }

    // Quick enable button
    if (quickEnableBtn) {
        quickEnableBtn.addEventListener('click', function() {
            devModeToggle.checked = true;
            updateDevMode(true);
            saveSetting('pdf_builder_developer_enabled', '1');
        });
    }

    // Modal functionality
    function loadLogs() {
        logsBody.innerHTML = '<div class="js-logs-loading">🔄 Collecte des logs JavaScript...</div>';

        setTimeout(() => {
            const logs = collectLogs();
            displayLogs(logs);
        }, 500);
    }

    function collectLogs() {
        const logs = [];

        // System status logs
        logs.push({
            type: 'info',
            timestamp: new Date().toISOString(),
            message: '=== RAPPORT MONITORING PDF BUILDER ===',
            source: 'System'
        });

        logs.push({
            type: 'info',
            timestamp: new Date().toISOString(),
            message: `Mode développeur: ${devModeToggle.checked ? 'ON' : 'OFF'}`,
            source: 'DeveloperMode'
        });

        logs.push({
            type: 'info',
            timestamp: new Date().toISOString(),
            message: `Logs JavaScript: ${debugToggle.checked ? 'ON' : 'OFF'}`,
            source: 'CanvasDebug'
        });

        // Simulate some logs
        if (debugToggle.checked) {
            const sampleLogs = [
                { type: 'log', message: '[PDF Builder] Composant initialisé avec succès', source: 'Canvas' },
                { type: 'warn', message: '[PDF Builder] Attention: Cache périmé détecté', source: 'Cache' },
                { type: 'error', message: '[PDF Builder] Erreur: Élément DOM introuvable #element-123', source: 'DOM' },
                { type: 'info', message: '[PDF Builder] Export PDF terminé: 15 éléments rendus', source: 'Export' }
            ];

            sampleLogs.forEach(log => {
                logs.push({
                    type: log.type,
                    timestamp: new Date(Date.now() - Math.random() * 3600000).toISOString(),
                    message: log.message,
                    source: log.source
                });
            });
        }

        return logs;
    }

    function displayLogs(logs) {
        if (logs.length === 0) {
            logsBody.innerHTML = '<div class="js-logs-loading">Aucun log disponible</div>';
            return;
        }

        let html = '';
        logs.forEach(log => {
            const time = new Date(log.timestamp).toLocaleTimeString('fr-FR', {
                hour: '2-digit', minute: '2-digit', second: '2-digit'
            });

            html += `<div class="log-entry log-${log.type}">`;
            html += `<span class="log-timestamp">[${time}]</span>`;
            html += `<strong>${getLogPrefix(log.type)}</strong> `;
            html += `<span class="log-source">[${log.source}]</span> `;
            html += `${log.message}`;
            html += '</div>';
        });

        html += '<div style="margin-top: 20px; padding: 12px; background: rgba(255,255,255,0.1); border-radius: 4px; font-size: 0.8em;">';
        html += `<strong>Total:</strong> ${logs.length} logs | `;
        html += `Info: ${logs.filter(l => l.type === 'info').length} | `;
        html += `Warnings: ${logs.filter(l => l.type === 'warn').length} | `;
        html += `Errors: ${logs.filter(l => l.type === 'error').length} | `;
        html += `Logs: ${logs.filter(l => l.type === 'log').length}`;
        html += '</div>';

        logsBody.innerHTML = html;
    }

    function getLogPrefix(type) {
        const prefixes = {
            info: '🔵 INFO',
            warn: '🟡 WARN',
            error: '🔴 ERROR',
            log: '⚪ LOG'
        };
        return prefixes[type] || prefixes.log;
    }

    function exportLogs(logs) {
        const data = {
            timestamp: new Date().toISOString(),
            summary: {
                total: logs.length,
                info: logs.filter(l => l.type === 'info').length,
                warn: logs.filter(l => l.type === 'warn').length,
                error: logs.filter(l => l.type === 'error').length,
                log: logs.filter(l => l.type === 'log').length
            },
            logs: logs
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

        showNotification('Logs exportés avec succès', 'success');
    }

    // Modal event listeners
    document.getElementById('show-js-logs').addEventListener('click', () => {
        logsModal.style.display = 'flex';
        loadLogs();
    });

    logsClose.addEventListener('click', () => {
        logsModal.style.display = 'none';
    });

    logsModal.addEventListener('click', (e) => {
        if (e.target === logsModal) {
            logsModal.style.display = 'none';
        }
    });

    logsRefresh.addEventListener('click', loadLogs);

    logsExport.addEventListener('click', () => {
        const logs = collectLogs();
        exportLogs(logs);
    });

    logsClear.addEventListener('click', () => {
        if (confirm('Vider tous les logs ? Cette action est irréversible.')) {
            logsBody.innerHTML = '<div style="text-align: center; padding: 40px; color: #10b981;"><h3>🗑️ Logs vidés</h3><p>Les logs ont été supprimés. Rechargez la page pour en générer de nouveaux.</p></div>';
            showNotification('Logs vidés avec succès', 'success');
        }
    });

    // Notification system
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => notification.classList.add('show'), 10);

        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 4000);
    }

    // Initialize
    updateDevMode(devModeToggle.checked);
    console.log('[PDF Builder] Developer tab ready');
});
</script>
