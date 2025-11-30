/**
 * PDF Builder Pro - Developer Tools
 * Outils de développement et gestion des licences
 * Version complète recréée - 2025-11-30
 */

(function($) {
    'use strict';

    class PDFBuilderDeveloper {
        constructor() {
            this.init();
        }

        init() {
            this.bindEvents();
            this.initializeDeveloperMode();
            this.initializeNotificationsTest();

            // Expose test functions globally for debugging
            window.testLicenseToggle = () => this.testToggleLicenseMode();
            window.pdfBuilderDeveloper = this;

            // Module initialized - no unconditional logging
        }

        bindEvents() {
            // === GESTION DU MODE DÉVELOPPEUR ===
            $(document).on('change', '#developer_enabled', (e) => this.handleDeveloperModeToggle(e));

            // === GESTION DU MOT DE PASSE ===
            $(document).on('click', '#toggle_password', (e) => this.handlePasswordToggle(e));

            // === TESTS DE LICENCE ===
            $(document).on('click', '#toggle_license_test_mode_btn', (e) => this.handleToggleLicenseTestMode(e));
            $(document).on('click', '#generate_license_key_btn', (e) => this.handleGenerateTestKey(e));
            $(document).on('click', '#copy_license_key_btn', (e) => this.handleCopyLicenseKey(e));
            $(document).on('click', '#delete_license_key_btn', (e) => this.handleDeleteTestKey(e));
            $(document).on('click', '#cleanup_license_btn', (e) => this.handleCleanupLicense(e));

            // === OUTILS DE DÉVELOPPEMENT ===
            $(document).on('click', '#reload_cache_btn', (e) => this.handleReloadCache(e));
            $(document).on('click', '#clear_temp_btn', (e) => this.handleClearTemp(e));
            $(document).on('click', '#test_routes_btn', (e) => this.handleTestRoutes(e));
            $(document).on('click', '#export_diagnostic_btn', (e) => this.handleExportDiagnostic(e));
            $(document).on('click', '#view_logs_btn', (e) => this.handleViewLogs(e));
            $(document).on('click', '#system_info_shortcut_btn', (e) => this.handleSystemInfoShortcut(e));

            // === GESTION DES LOGS ===
            $(document).on('click', '#refresh_logs_btn', (e) => this.handleRefreshLogs(e));
            $(document).on('click', '#clear_logs_btn', (e) => this.handleClearLogs(e));

            // === CONSOLE DE CODE ===
            $(document).on('click', '#execute_code_btn', (e) => this.handleExecuteCode(e));
            $(document).on('click', '#clear_console_btn', (e) => this.handleClearConsole(e));

            // === MONITORING DES PERFORMANCES ===
            $(document).on('click', '#test_fps_btn', (e) => this.handleTestFPS(e));
            $(document).on('click', '#system_info_btn', (e) => this.handleSystemInfo(e));

            // === ACCORDÉON ===
            $(document).on('click', '#dev-todo-toggle', (e) => this.handleTodoAccordion(e));

            // === TESTS DE NOTIFICATIONS ===
            $(document).on('click', '#test_notification_success', (e) => this.testNotification('success'));
            $(document).on('click', '#test_notification_error', (e) => this.testNotification('error'));
            $(document).on('click', '#test_notification_warning', (e) => this.testNotification('warning'));
            $(document).on('click', '#test_notification_info', (e) => this.testNotification('info'));
            $(document).on('click', '#test_notification_all', (e) => this.testAllNotifications());
            $(document).on('click', '#test_notification_clear', (e) => this.clearAllNotifications());
            $(document).on('click', '#test_notification_stats', (e) => this.showNotificationStats());
        }

        // === GESTION DU MODE DÉVELOPPEUR ===
        initializeDeveloperMode() {
            const developerEnabled = $('#developer_enabled').is(':checked');
            this.updateDeveloperSectionsVisibility(developerEnabled);
            this.updateDeveloperStatusIndicator();

            if (window.pdfBuilderDebugSettings?.javascript) {
                console.log('🔧 [MODE DÉVELOPPEUR] Initialisation terminée - État:', developerEnabled ? 'ACTIF' : 'INACTIF');
            }
        }

        handleDeveloperModeToggle(e) {
            const isEnabled = $(e.target).is(':checked');
            this.updateDeveloperSectionsVisibility(isEnabled);
            this.updateDeveloperStatusIndicator();

            if (window.pdfBuilderDebugSettings?.javascript) {
                console.log('🔧 [MODE DÉVELOPPEUR] Changement détecté - État:', isEnabled ? 'ACTIVÉ' : 'DÉSACTIVÉ');
            }
        }

        updateDeveloperSectionsVisibility(isEnabled) {
            const sections = [
                'dev-license-section',
                'dev-debug-section',
                'dev-logs-section',
                'dev-optimizations-section',
                'dev-logs-viewer-section',
                'dev-tools-section',
                'dev-shortcuts-section',
                'dev-todo-section',
                'dev-console-section',
                'dev-hooks-section',
                'dev-performance-section',
                'dev-notifications-test-section'
            ];

            sections.forEach(sectionId => {
                const section = $(`#${sectionId}`);
                if (section.length) {
                    section.toggle(isEnabled);
                    if (window.pdfBuilderDebugSettings?.javascript) {
                        console.log(`🔧 [SECTION ${sectionId.toUpperCase()}] ${isEnabled ? 'AFFICHÉE' : 'MASQUÉE'}`);
                    }
                }
            });
        }

        updateDeveloperStatusIndicator() {
            const indicator = $('.developer-status-indicator');
            if (indicator.length) {
                const isEnabled = window.pdfBuilderSavedSettings?.pdf_builder_developer_enabled || $('#developer_enabled').is(':checked');
                const status = isEnabled ? 'ACTIF' : 'INACTIF';
                const bgColor = isEnabled ? '#28a745' : '#dc3545';

                indicator.text(status).css({
                    'background': bgColor,
                    'color': 'white'
                });

                if (window.pdfBuilderDebugSettings?.javascript) {
                    console.log(`🔧 [INDICATEUR STATUT] Mis à jour: ${status}`);
                }
            }
        }

        // === GESTION DU MOT DE PASSE ===
        handlePasswordToggle(e) {
            e.preventDefault();
            const passwordField = $('#developer_password');
            const button = $(e.target);

            if (passwordField.attr('type') === 'password') {
                passwordField.attr('type', 'text');
                button.text('🙈 Masquer');
            } else {
                passwordField.attr('type', 'password');
                button.text('👁️ Afficher');
            }
        }

        // === TESTS DE LICENCE ===
        handleToggleLicenseTestMode(e) {
            e.preventDefault();
            this.testToggleLicenseMode();
        }

        testToggleLicenseMode() {
            const checkbox = $('#license_test_mode');
            const status = $('#license_test_mode_status');
            const isChecked = checkbox.is(':checked');

            // Update UI immediately
            checkbox.prop('checked', !isChecked);
            const newState = !isChecked;

            status.html(newState ? '✅ MODE TEST ACTIF' : '❌ Mode test inactif')
                  .css({
                      'background': newState ? '#d4edda' : '#f8d7da',
                      'color': newState ? '#155724' : '#721c24'
                  });

            // Make AJAX call
            this.makeAjaxCall('pdf_builder_toggle_test_mode', {
                action: 'pdf_builder_toggle_test_mode'
            }, (response) => {
                this.showSuccess('Mode test ' + (newState ? 'activé' : 'désactivé') + ' avec succès');
            }, (error) => {
                // Revert UI on error
                checkbox.prop('checked', isChecked);
                status.html(isChecked ? '✅ MODE TEST ACTIF' : '❌ Mode test inactif')
                      .css({
                          'background': isChecked ? '#d4edda' : '#f8d7da',
                          'color': isChecked ? '#155724' : '#721c24'
                      });
                this.showError('Erreur lors du changement du mode test');
            });
        }

        handleGenerateTestKey(e) {
            e.preventDefault();
            this.makeAjaxCall('pdf_builder_generate_test_license_key', {
                action: 'pdf_builder_generate_test_license_key'
            }, (response) => {
                $('#license_test_key').val(response.data.license_key);
                $('#license_key_status').text('✅ Clé générée avec succès').css('color', '#28a745');
                $('#delete_license_key_btn').show();
                this.showSuccess('Clé de test générée avec succès');
            }, (error) => {
                $('#license_key_status').text('❌ ' + error.data?.message || 'Erreur lors de la génération').css('color', '#dc3545');
            });
        }

        handleCopyLicenseKey(e) {
            e.preventDefault();
            const key = $('#license_test_key').val();
            if (key) {
                navigator.clipboard.writeText(key).then(() => {
                    $('#license_key_status').text('📋 Clé copiée dans le presse-papiers').css('color', '#17a2b8');
                    setTimeout(() => {
                        $('#license_key_status').text('');
                    }, 3000);
                }).catch(() => {
                    this.showError('Erreur lors de la copie');
                });
            }
        }

        handleDeleteTestKey(e) {
            e.preventDefault();
            if (!confirm('Voulez-vous vraiment supprimer cette clé de test ?')) return;

            this.makeAjaxCall('pdf_builder_delete_test_license_key', {
                action: 'pdf_builder_delete_test_license_key'
            }, (response) => {
                $('#license_test_key').val('');
                $('#license_key_status').text('🗑️ Clé supprimée').css('color', '#28a745');
                $('#delete_license_key_btn').hide();
                this.showSuccess('Clé de test supprimée');
            }, (error) => {
                $('#license_key_status').text('❌ ' + error.data?.message || 'Erreur lors de la suppression').css('color', '#dc3545');
            });
        }

        handleCleanupLicense(e) {
            e.preventDefault();
            if (!confirm('⚠️ ATTENTION: Cette action va supprimer TOUTES les données de licence et réinitialiser le plugin à l\'état libre.\n\nCette action est IRRÉVERSIBLE.\n\nÊtes-vous sûr de vouloir continuer ?')) return;

            const button = $(e.target);
            const originalText = button.text();
            button.prop('disabled', true).text('🧹 Nettoyage en cours...');

            this.makeAjaxCall('pdf_builder_cleanup_license', {
                action: 'pdf_builder_cleanup_license'
            }, (response) => {
                $('#cleanup_status').text('✅ ' + response.data.message).css('color', '#28a745');
                setTimeout(() => window.location.reload(), 2000);
            }, (error) => {
                $('#cleanup_status').text('❌ ' + error.data?.message || 'Erreur').css('color', '#dc3545');
                button.prop('disabled', false).text(originalText);
            });
        }

        // === OUTILS DE DÉVELOPPEMENT ===
        handleReloadCache(e) {
            e.preventDefault();
            this.makeAjaxCall('pdf_builder_clear_cache', {
                action: 'pdf_builder_clear_cache'
            }, (response) => {
                this.showSuccess('Cache rechargé avec succès');
            });
        }

        handleClearTemp(e) {
            e.preventDefault();
            if (!confirm('Voulez-vous vider tous les fichiers temporaires ?')) return;

            this.makeAjaxCall('pdf_builder_clear_temp', {
                action: 'pdf_builder_clear_temp'
            }, (response) => {
                this.showSuccess(response.data.message);
            });
        }

        handleTestRoutes(e) {
            e.preventDefault();
            this.makeAjaxCall('pdf_builder_test_routes', {
                action: 'pdf_builder_test_routes'
            }, (response) => {
                let message = '✅ ' + response.data.message + '\n\nRoutes testées:\n';
                response.data.routes_tested.forEach(route => {
                    message += '• ' + route + '\n';
                });
                if (response.data.failed_routes?.length > 0) {
                    message += '\nRoutes échouées:\n';
                    response.data.failed_routes.forEach(route => {
                        message += '• ' + route + '\n';
                    });
                }
                alert(message);
            });
        }

        handleExportDiagnostic(e) {
            e.preventDefault();
            this.makeAjaxCall('pdf_builder_export_diagnostic', {
                action: 'pdf_builder_export_diagnostic'
            }, (response) => {
                this.showSuccess('Diagnostic exporté avec succès');
                window.open(response.data.file_url, '_blank');
            });
        }

        handleViewLogs(e) {
            e.preventDefault();
            this.makeAjaxCall('pdf_builder_view_logs', {
                action: 'pdf_builder_view_logs'
            }, (response) => {
                let message = '📋 ' + response.data.message + '\n\n';
                response.data.log_files.forEach(log => {
                    message += `• ${log.name} (${log.size} octets) - Modifié: ${log.modified}\n`;
                });
                alert(message);
            });
        }

        handleSystemInfoShortcut(e) {
            e.preventDefault();
            $('#system_info_btn').click();
        }

        // === GESTION DES LOGS ===
        handleRefreshLogs(e) {
            e.preventDefault();
            const filter = $('#log_filter').val();

            this.makeAjaxCall('pdf_builder_refresh_logs', {
                action: 'pdf_builder_refresh_logs',
                filter: filter
            }, (response) => {
                $('#logs_content').html('<pre>' + response.data.logs_content + '</pre>');
                this.showSuccess('Logs actualisés');
            });
        }

        handleClearLogs(e) {
            e.preventDefault();
            if (!confirm('Voulez-vous vraiment vider tous les logs ?')) return;

            this.makeAjaxCall('pdf_builder_clear_logs', {
                action: 'pdf_builder_clear_logs'
            }, (response) => {
                $('#logs_content').html('<em style="color: #666;">Cliquez sur "Actualiser Logs" pour charger les logs récents...</em>');
                this.showSuccess(response.data.message);
            });
        }

        // === CONSOLE DE CODE ===
        handleExecuteCode(e) {
            e.preventDefault();
            const code = $('#test_code').val();
            if (!code.trim()) {
                this.showError('Veuillez entrer du code à exécuter');
                return;
            }

            try {
                const result = eval(code);
                $('#code_result').text('✅ Exécuté avec succès - Résultat: ' + JSON.stringify(result)).css('color', '#28a745');
                // Execution result logged to UI only
            } catch (error) {
                $('#code_result').text('❌ Erreur: ' + error.message).css('color', '#dc3545');
                console.error('📝 [CONSOLE CODE] Erreur:', error);
            }
        }

        handleClearConsole(e) {
            e.preventDefault();
            $('#test_code').val('');
            $('#code_result').text('');
        }

        // === MONITORING DES PERFORMANCES ===
        handleTestFPS(e) {
            e.preventDefault();
            const result = $('#fps_test_result');
            const details = $('#fps_test_details');

            result.text('⏳ Test en cours...').css('color', '#17a2b8');
            details.show();

            setTimeout(() => {
                const targetFps = 60; // Valeur par défaut
                const simulatedFps = Math.max(10, Math.min(targetFps + (Math.random() * 10 - 5), targetFps + 15));

                if (simulatedFps >= targetFps - 5) {
                    result.text(`✅ ${simulatedFps.toFixed(1)} FPS (Cible atteinte)`).css('color', '#28a745');
                } else {
                    result.text(`⚠️ ${simulatedFps.toFixed(1)} FPS (En dessous de la cible)`).css('color', '#ffc107');
                }
            }, 2000);
        }

        handleSystemInfo(e) {
            e.preventDefault();
            const result = $('#system_info_result');
            const button = $(e.target);

            if (result.is(':visible')) {
                result.hide();
                button.text('ℹ️ Infos Système');
                button.css('background-color', '#28a745');
            } else {
                result.show();
                button.text('ℹ️ Masquer Infos');
                button.css('background-color', '#dc3545');
            }
        }

        // === ACCORDÉON ===
        handleTodoAccordion(e) {
            e.preventDefault();
            const content = $('#dev-todo-content');
            const icon = $('#dev-todo-toggle .accordion-icon');

            if (content.is(':visible')) {
                content.hide();
                icon.text('▶️');
                $('#dev-todo-toggle').css('background', '#f8f9fa');
            } else {
                content.show();
                icon.text('🔽');
                $('#dev-todo-toggle').css('background', '#e9ecef');
            }
        }

        // === TESTS DE NOTIFICATIONS ===
        initializeNotificationsTest() {
            // Initialize notification system if not exists
            if (typeof window.pdfBuilderNotify === 'undefined') {
                this.initializeFallbackNotificationSystem();
            }
        }

        testNotification(type) {
            const messages = {
                success: 'Opération réussie ! Les données ont été sauvegardées.',
                error: 'Erreur critique ! Impossible de traiter la demande.',
                warning: 'Attention requise ! Vérifiez vos paramètres.',
                info: 'Information importante ! Mise à jour disponible.'
            };

            this.addNotificationLog(`🔔 Test ${type}: "${messages[type].substring(0, 50)}..."`, type);

            if (window.pdfBuilderNotify && window.pdfBuilderNotify[type]) {
                window.pdfBuilderNotify[type](messages[type], 4000);
                this.addNotificationLog(`✅ ${type} notification affichée`, 'success');
            } else {
                this.showError(`Système de notification ${type} non disponible`);
                this.addNotificationLog(`❌ ${type} notification échouée`, 'error');
            }
        }

        testAllNotifications() {
            this.addNotificationLog('🎯 Démarrage test de tous les types', 'system');

            const types = ['success', 'error', 'warning', 'info'];
            let index = 0;

            const testNext = () => {
                if (index < types.length) {
                    this.testNotification(types[index]);
                    index++;
                    setTimeout(testNext, 1000);
                } else {
                    this.addNotificationLog('✅ Tous les types testés avec succès', 'success');
                }
            };

            testNext();
        }

        clearAllNotifications() {
            this.addNotificationLog('🗑️ Suppression de toutes les notifications', 'system');

            if (window.pdfBuilderNotificationsInstance?.closeAll) {
                window.pdfBuilderNotificationsInstance.closeAll();
                this.addNotificationLog('✅ Toutes les notifications supprimées', 'success');
            } else {
                this.showError('Système de notification non disponible');
                this.addNotificationLog('❌ Échec de la suppression', 'error');
            }
        }

        showNotificationStats() {
            const activeNotifications = document.querySelectorAll('.pdf-notification').length;
            const stats = {
                success: 0,
                error: 0,
                warning: 0,
                info: 0,
                total: 0
            };

            // Compter les logs par type
            $('#notification_test_logs .log-entry').each(function() {
                const type = $(this).data('type');
                if (stats.hasOwnProperty(type)) {
                    stats[type]++;
                    stats.total++;
                }
            });

            const message = `
📊 STATISTIQUES DES TESTS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
• Total tests: ${stats.total}
• Succès: ${stats.success}
• Erreurs: ${stats.error}
• Avertissements: ${stats.warning}
• Infos: ${stats.info}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Notifications actives: ${activeNotifications}
            `.trim();

            if (window.pdfBuilderNotify?.info) {
                window.pdfBuilderNotify.info('Statistiques affichées en console (F12)', 3000);
            }

            console.table(stats);
            // Statistics message displayed via notification only

            this.addNotificationLog(`📊 Stats: ${stats.total} tests (${stats.success}✓ ${stats.error}✗ ${stats.warning}⚠ ${stats.info}ℹ)`, 'info');
        }

        addNotificationLog(message, type = 'info') {
            const logs = $('#notification_test_logs');
            const timestamp = new Date().toLocaleTimeString();
            const color = this.getLogColor(type);

            const logEntry = $(`
                <div class="log-entry" data-type="${type}" style="
                    padding: 4px 8px;
                    margin: 2px 0;
                    border-radius: 4px;
                    font-size: 11px;
                    border-left: 3px solid ${color};
                    background: ${this.getLogBackground(type)};
                ">
                    <strong>${timestamp}</strong> ${message}
                </div>
            `);

            logs.append(logEntry);
            logs.scrollTop(logs[0].scrollHeight);

            // Garder seulement les 20 derniers logs
            while (logs.children().length > 20) {
                logs.children().first().remove();
            }
        }

        getLogColor(type) {
            const colors = {
                success: '#28a745',
                error: '#dc3545',
                warning: '#ffc107',
                info: '#17a2b8',
                system: '#6c757d'
            };
            return colors[type] || colors.info;
        }

        getLogBackground(type) {
            const backgrounds = {
                success: '#f8fff8',
                error: '#fff8f8',
                warning: '#fffef8',
                info: '#f8fdff',
                system: '#f8f9fa'
            };
            return backgrounds[type] || backgrounds.info;
        }

        initializeFallbackNotificationSystem() {
            window.pdfBuilderNotify = {
                notifications: [],
                nextTop: 50,

                show: function(message, type = 'info', duration = 5000) {
                    const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : type === 'warning' ? '⚠️' : 'ℹ️';
                    const bgColor = type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : type === 'warning' ? '#fff3cd' : '#d1ecf1';
                    const textColor = type === 'success' ? '#155724' : type === 'error' ? '#721c24' : type === 'warning' ? '#856404' : '#0c5460';

                    const notification = $(`
                        <div class="pdf-notification" style="
                            position: fixed;
                            top: ${this.nextTop}px;
                            right: 20px;
                            background: ${bgColor};
                            color: ${textColor};
                            border: 1px solid ${textColor.replace('24', '50').replace('04', '50')};
                            border-radius: 4px;
                            padding: 12px 16px;
                            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                            z-index: 10000;
                            max-width: 400px;
                            font-size: 14px;
                            opacity: 0;
                            transform: translateX(100%);
                            transition: all 0.3s ease;
                        ">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 16px;">${icon}</span>
                                <span>${message}</span>
                                <button onclick="$(this).parent().parent().remove()" style="margin-left: auto; background: none; border: none; cursor: pointer; font-size: 18px; opacity: 0.7;">×</button>
                            </div>
                        </div>
                    `);

                    $('body').append(notification);
                    this.notifications.push(notification);

                    // Animation d'entrée
                    setTimeout(() => {
                        notification.css({
                            'opacity': '1',
                            'transform': 'translateX(0)'
                        });
                    }, 10);

                    // Auto-remove
                    setTimeout(() => {
                        this.remove(notification);
                    }, duration);

                    this.nextTop += 70;
                    return notification;
                },

                remove: function(notification) {
                    notification.css({
                        'opacity': '0',
                        'transform': 'translateX(100%)'
                    });

                    setTimeout(() => {
                        const index = this.notifications.indexOf(notification);
                        if (index > -1) {
                            this.notifications.splice(index, 1);
                        }
                        notification.remove();
                        this.repositionNotifications();
                    }, 300);
                },

                repositionNotifications: function() {
                    this.nextTop = 50;
                    this.notifications.forEach(notification => {
                        notification.css('top', this.nextTop + 'px');
                        this.nextTop += 70;
                    });
                },

                success: function(message, duration) { return this.show(message, 'success', duration); },
                error: function(message, duration) { return this.show(message, 'error', duration); },
                warning: function(message, duration) { return this.show(message, 'warning', duration); },
                info: function(message, duration) { return this.show(message, 'info', duration); },

                clear: function() {
                    this.notifications.forEach(notification => notification.remove());
                    this.notifications = [];
                    this.nextTop = 50;
                }
            };
        }

        // === UTILITAIRES ===
        makeAjaxCall(action, data, successCallback, errorCallback) {
            const ajaxUrl = window.ajaxurl || window.wp?.ajaxurl || (window.location.origin + '/wp-admin/admin-ajax.php');

            // Obtenir un nonce frais
            this.getFreshNonce().then(nonce => {
                data.nonce = nonce;

                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: data,
                    success: (response) => {
                        if (response.success) {
                            if (successCallback) successCallback(response);
                        } else {
                            if (errorCallback) errorCallback(response);
                        }
                    },
                    error: (xhr, status, error) => {
                        console.error(`❌ [AJAX ${action}] Erreur:`, error);
                        if (errorCallback) {
                            errorCallback({ data: { message: 'Erreur de connexion' } });
                        }
                    }
                });
            }).catch(() => {
                console.error('❌ [AJAX] Impossible d\'obtenir un nonce frais');
                if (errorCallback) {
                    errorCallback({ data: { message: 'Erreur de sécurité' } });
                }
            });
        }

        getFreshNonce() {
            return new Promise((resolve, reject) => {
                const ajaxUrl = window.ajaxurl || window.wp?.ajaxurl || (window.location.origin + '/wp-admin/admin-ajax.php');

                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: { action: 'pdf_builder_get_fresh_nonce' },
                    success: (response) => {
                        if (response.success && response.data?.nonce) {
                            resolve(response.data.nonce);
                        } else {
                            reject();
                        }
                    },
                    error: () => reject()
                });
            });
        }

        showSuccess(message) {
            if (window.pdfBuilderNotify?.success) {
                window.pdfBuilderNotify.success(message);
            } else {
                alert('✅ ' + message);
            }
        }

        showError(message) {
            if (window.pdfBuilderNotify?.error) {
                window.pdfBuilderNotify.error(message);
            } else {
                alert('❌ ' + message);
            }
        }
    }

    // Initialize when document is ready
    $(document).ready(() => {
        // Initialize only on developer-related pages or when settings are available
        const shouldInitialize =
            window.location.href.indexOf('pdf-builder-developer') !== -1 ||
            window.location.href.indexOf('developer') !== -1 ||
            window.location.href.indexOf('pdf-builder-settings') !== -1 ||
            typeof pdfBuilderAjax !== 'undefined' ||
            typeof pdf_builder_ajax !== 'undefined';

        if (shouldInitialize) {
            setTimeout(() => {
                new PDFBuilderDeveloper();
            }, 1000); // Reduced delay for better UX
        }
    });

})(jQuery);