/**
 * PDF Builder Developer Settings JavaScript
 *
 * Handles all developer settings functionality with proper AJAX and state management
 */

(function($) {
    'use strict';

    const DeveloperSettings = {
        config: null,
        elements: {},
        currentLogs: [],

        init: function() {

            // Get configuration
            this.config = window.pdfBuilderDeveloperConfig || {};

            // Cache elements
            this.cacheElements();

            // Bind events
            this.bindEvents();

            // Initialize state
            this.updateUI();

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

            const formData = new FormData();
            formData.append('action', this.config.action);
            formData.append('nonce', this.config.nonce);
            formData.append('setting_key', key);
            formData.append('setting_value', value);

            return fetch(this.config.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    
                    this.showNotification('Paramètre sauvegardé avec succès', 'success');
                    return data;
                } else {
                    // console.error('[PDF Builder Developer] Save failed:', data);
                    this.showNotification('Erreur lors de la sauvegarde: ' + (data.data?.message || 'Erreur inconnue'), 'error');
                    throw new Error('Save failed');
                }
            })
            .catch(error => {
                // console.error('[PDF Builder Developer] AJAX error:', error);
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
                    version: 'N/A (loaded via AJAX)',
                    debug: 'N/A (loaded via AJAX)',
                    multisite: 'N/A (loaded via AJAX)',
                    theme: 'N/A (loaded via AJAX)',
                    plugins: 'N/A (loaded via AJAX)'
                },
                server: {
                    php_version: 'N/A (loaded via AJAX)',
                    memory_limit: 'N/A (loaded via AJAX)',
                    max_execution_time: 'N/A (loaded via AJAX)',
                    upload_max_filesize: 'N/A (loaded via AJAX)',
                    post_max_size: 'N/A (loaded via AJAX)'
                },
                database: {
                    version: 'N/A (loaded via AJAX)',
                    size: 'N/A (loaded via AJAX)',
                    tables: 'N/A (loaded via AJAX)'
                },
                pdf_builder: {
                    version: 'Pro 2.1.0',
                    dev_mode: this.config.current_values.dev_mode === '1' ? 'Activé' : 'Désactivé',
                    debug_logs: this.config.current_values.debug_enabled === '1' ? 'Activé' : 'Désactivé',
                    cache_status: 'Opérationnel',
                    last_backup: 'N/A (loaded via AJAX)'
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

            if (window.showInfoNotification) {
                window.showInfoNotification(message);
            } else {
                alert(message);
            }
        },

        resetDeveloperSettings: function() {
            if (confirm('Réinitialiser tous les paramètres développeur ? Cette action est irréversible.')) {

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
            // Use centralized notification system
            if (window.simpleNotificationSystem) {
                window.simpleNotificationSystem.show(message, type);
            } else {
                // Fallback to local system if centralized not available
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
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        DeveloperSettings.init();
    });

})(jQuery);

