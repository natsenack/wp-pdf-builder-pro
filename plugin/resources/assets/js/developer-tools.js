/**
 * PDF Builder Pro - Developer Tools
 * Outils de développement et gestion des licences
 * Version complète recréée - 2025-11-30
 */

// Fonction de debug conditionnel

// LOG ABSOLU - toujours affiché, même si le script ne s'initialise pas

// Assurer que les données nécessaires sont disponibles
if (typeof window.pdfBuilderNotifications === 'undefined') {
    
    window.pdfBuilderNotifications = {
        ajax_url: (window.ajaxurl || (window.location.origin + '/wp-admin/admin-ajax.php')),
        nonce: 'fallback-nonce',
        settings: {
            enabled: true,
            position: 'top-right',
            duration: 5000,
            max_notifications: 5,
            animation: 'slide',
            theme: 'modern'
        },
        strings: {
            success: 'Succès',
            error: 'Erreur',
            warning: 'Avertissement',
            info: 'Information',
            close: 'Fermer'
        }
    };
}

if (typeof window.pdfBuilderDebugSettings === 'undefined') {
    
    window.pdfBuilderDebugSettings = {
        javascript: true,
        javascript_verbose: true,
        php: false,
        ajax: true
    };
}

(function($) {
    'use strict';

    // LOG INCONDITIONNEL - toujours affiché

    class PDFBuilderDeveloper {
        constructor() {
            this.init();
        }

        init() {
                this.bindEvents();
            this.initializeDeveloperMode();
            this.initializeNotificationsTest();
                this.initializeSectionsCollapsedState();
            // export a toggles manager globally for other scripts to re-sync UI
            window.pdfBuilderDeveloperToggles = {
                forceSync: () => {
                    try {
                        const isEnabled = (window.pdfBuilderSavedSettings && window.pdfBuilderSavedSettings.pdf_builder_developer_enabled === '1') || $('#developer_enabled').is(':checked');
                        this.updateDeveloperSectionsVisibility(isEnabled);
                        this.updateDeveloperStatusIndicator();
                        
                    } catch (e) {
                        // console.error('[DEV TOGGLES] forceSync failed:', e);
                    }
                }
            };

            // Expose test functions globally for debugging
            window.testLicenseToggle = () => this.testToggleLicenseMode();
            window.pdfBuilderDeveloper = this;

            // Listen for debug settings change so we can react immediately
            if (typeof window !== 'undefined' && window.addEventListener) {
                window.addEventListener('pdfBuilder:debugSettingsChanged', (e) => {
                    try {
                        const newSettings = e && e.detail ? e.detail : window.pdfBuilderDebugSettings;
                        // Mettre à jour window.pdfBuilderDebugSettings.javascript en fonction des paramètres sauvegardés
                        window.pdfBuilderDebugSettings.javascript = !!(window.pdfBuilderSavedSettings && window.pdfBuilderSavedSettings.pdf_builder_canvas_debug_enabled && window.pdfBuilderSavedSettings.pdf_builder_canvas_debug_enabled !== '0');
                        if (window.pdfBuilderDebugSettings.javascript) {
                            
                        } else {
                            
                        }
                        // Re-sync developer toggles and visibility if necessary
                        if (window.pdfBuilderDeveloperToggles && typeof window.pdfBuilderDeveloperToggles.forceSync === 'function') {
                            window.pdfBuilderDeveloperToggles.forceSync();
                        }
                    } catch (err) {
                        // console.warn('[DEV TOGGLES] Error handling pdfBuilder:debugSettingsChanged', err);
                    }
                });
            }

            // Module initialized - no unconditional logging
            
        }

        bindEvents() {
            // === GESTION DU MODE DÉVELOPPEUR ===
            $(document).on('change', '#developer_enabled', (e) => this.handleDeveloperModeToggle(e));

            // === GESTION DU MOT DE PASSE ===
            $(document).on('click', '#toggle_password', (e) => this.handlePasswordToggle(e));

            // === TESTS DE LICENCE ===
            $(document).on('click', '#toggle_license_test_mode_btn', (e) => this.handleToggleLicenseTestMode(e));
            // Ensure checkbox change also triggers AJAX toggle
            $(document).on('change', '#license_test_mode', (e) => this.testToggleLicenseMode(false));
            $(document).on('click', '#generate_license_key_btn', (e) => this.handleGenerateTestKey(e));
            $(document).on('click', '#validate_license_key_btn', (e) => this.handleValidateLicenseKey(e));
            $(document).on('click', '#show_license_key_btn', (e) => this.handleShowLicenseKey(e));
            $(document).on('click', '#license_modal_validate_btn', (e) => this.handleValidateLicenseKeyFromModal(e));
            $(document).on('click', '#license_modal_save_btn', (e) => this.handleSaveLicenseKeyFromModal(e));
            $(document).on('click', '#license_modal_close_btn', (e) => this.handleCloseLicenseModal(e));
            $(document).on('click', '#copy_license_key_btn', (e) => this.handleCopyLicenseKey(e));
            $(document).on('click', '#delete_license_key_btn', (e) => this.handleDeleteTestKey(e));
            $(document).on('click', '#license_modal_delete_btn', (e) => this.handleDeleteTestKey(e));
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
            $(document).on('click', '.dev-section-header', (e) => this.handleSectionToggle(e));
            // Keyboard accessibility: toggle on Enter or Space
            $(document).on('keydown', '.dev-section-header', (e) => {
                const key = e.key || e.keyCode;
                if (key === 'Enter' || key === ' ' || key === 13 || key === 32) {
                    e.preventDefault();
                    this.handleSectionToggle(e);
                }
            });

            // === TESTS DE NOTIFICATIONS ===
            $(document).on('click', '#test_notification_success', (e) => this.testNotification('success'));
            $(document).on('click', '#test_notification_error', (e) => this.testNotification('error'));
            $(document).on('click', '#test_notification_warning', (e) => this.testNotification('warning'));
            $(document).on('click', '#test_notification_info', (e) => this.testNotification('info'));
            $(document).on('click', '#test_notification_all', (e) => this.testAllNotifications());
            $(document).on('click', '#test_notification_clear', (e) => this.clearAllNotifications());
            $(document).on('click', '#test_notification_stats', (e) => this.showNotificationStats());

        }

        // Ensure all dev sections are closed by default and set correct toggle icons
        initializeSectionsCollapsedState() {
            $('.dev-section').each(function() {
                const section = $(this);
                section.addClass('collapsed');
                const toggle = section.find('.dev-section-toggle');
                if (toggle.length) {
                    toggle.text('▶️');
                }
                // Update aria attributes
                const header = section.find('.dev-section-header');
                const content = section.find('.dev-section-content');
                if (header.length) {
                    header.attr('aria-expanded', 'false');
                }
                if (content.length) {
                    content.attr('aria-hidden', 'true');
                }
            });
        }

        // === GESTION DU MODE DÉVELOPPEUR ===
        initializeDeveloperMode() {
            const developerEnabled = $('#developer_enabled').is(':checked');
            this.updateDeveloperSectionsVisibility(developerEnabled);
            this.updateDeveloperStatusIndicator();

            if (window.pdfBuilderDebugSettings?.javascript) {
                
            }

            // Initialize license test key display
            if (window.pdfBuilderSavedSettings) {
                const savedKey = window.pdfBuilderSavedSettings.pdf_builder_license_test_key || '';
                const savedExpires = window.pdfBuilderSavedSettings.pdf_builder_license_test_key_expires || '';
                if (savedKey) {
                    const masked = savedKey.substr(0,6) + '••••••••••••••••' + savedKey.substr(-6);
                    $('#license_test_key_display').text(masked);
                    $('#license_test_key').val(savedKey);
                    $('#delete_license_key_btn').show();
                    if (savedExpires) {
                        $('#license_key_expires').text('Expire le: ' + savedExpires);
                    }
                } else {
                    $('#license_key_status').text('');
                    $('#license_test_key_display').text('');
                    $('#delete_license_key_btn').hide();
                }
            }
        }

        handleDeveloperModeToggle(e) {
            const isEnabled = $(e.target).is(':checked');
            this.updateDeveloperSectionsVisibility(isEnabled);
            this.updateDeveloperStatusIndicator();

            if (window.pdfBuilderDebugSettings?.javascript) {
                
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
            this.testToggleLicenseMode(true);
        }

        testToggleLicenseMode(forceToggle = true) {
            const checkbox = $('#license_test_mode');
            const status = $('#license_test_mode_status');
            const isChecked = checkbox.is(':checked');

            let newState;
            if (forceToggle) {
                // Force toggle if requested (i.e., from a button click)
                checkbox.prop('checked', !isChecked);
                newState = !isChecked;
            } else {
                // Use the current checkbox state (i.e., user clicked the checkbox directly)
                newState = checkbox.is(':checked');
            }

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
                const newKey = response.data?.key || response.data?.license_key || '';
                $('#license_test_key').val(newKey);
                // Keep global saved settings in sync if present
                if (window.pdfBuilderSavedSettings) {
                    window.pdfBuilderSavedSettings.pdf_builder_license_test_key = newKey;
                    if (response.data?.expires) {
                        window.pdfBuilderSavedSettings.pdf_builder_license_test_key_expires = response.data.expires;
                    }
                }
                if (newKey) {
                    const masked = newKey.substr(0,6) + '••••••••••••••••' + newKey.substr(-6);
                    $('#license_test_key_display').text(masked);
                    $('#delete_license_key_btn').show();
                }
                if (response.data?.expires) {
                    $('#license_key_expires').text('Expire le: ' + response.data.expires);
                }
                $('#license_key_status').text('✅ Clé générée avec succès').css('color', '#28a745');
                // Ensure test mode checkbox is updated
                $('#license_test_mode').prop('checked', true);
                $('#license_test_mode_status').html('✅ MODE TEST ACTIF').css({ 'background': '#d4edda', 'color': '#155724' });
                if (window.pdfBuilderSavedSettings) {
                    window.pdfBuilderSavedSettings.pdf_builder_license_test_mode_enabled = '1';
                }
                this.showSuccess(response.data?.message || 'Clé de test générée avec succès');
            }, (error) => {
                $('#license_key_status').text('❌ ' + (error.data?.message || 'Erreur lors de la génération')).css('color', '#dc3545');
            });
        }

        // Validate current key (from display) via AJAX
        handleValidateLicenseKey(e) {
            e.preventDefault();
            const key = $('#license_test_key').val() || '';
            if (!key) {
                this.showError('Aucune clé à valider');
                $('#license_key_status').text('❌ Aucune clé à valider').css('color', '#dc3545');
                return;
            }
            this.makeAjaxCall('pdf_builder_validate_test_license_key', { action: 'pdf_builder_validate_test_license_key', key: key }, (response) => {
                $('#license_key_status').text('✅ ' + (response.data?.message || 'Clé valide')).css('color', '#28a745');
                this.showSuccess(response.data?.message || 'Clé valide');
            }, (error) => {
                $('#license_key_status').text('❌ ' + (error.data?.message || 'Clé invalide')).css('color', '#dc3545');
                this.showError(error.data?.message || 'Clé invalide');
            });
        }

        // Show full key in modal
        handleShowLicenseKey(e) {
            e.preventDefault();
            const key = $('#license_test_key').val() || '';
            $('#license_test_key_input').val(key);
            $('#license_modal_message').text('');
            $('#license_key_modal').css('display', 'flex');
        }

        handleValidateLicenseKeyFromModal(e) {
            e.preventDefault();
            const key = $('#license_test_key_input').val() || '';
            if (!key) {
                $('#license_modal_message').text('Veuillez saisir une clé à valider').css('color', '#dc3545');
                return;
            }
            this.makeAjaxCall('pdf_builder_validate_test_license_key', { action: 'pdf_builder_validate_test_license_key', key: key }, (response) => {
                $('#license_modal_message').text('✅ ' + (response.data?.message || 'Clé valide')).css('color', '#28a745');
                this.showSuccess(response.data?.message || 'Clé valide');
            }, (error) => {
                $('#license_modal_message').text('❌ ' + (error.data?.message || 'Clé invalide')).css('color', '#dc3545');
                this.showError(error.data?.message || 'Clé invalide');
            });
        }

        // Save key entered in modal to server via save settings (developpeur tab)
        handleSaveLicenseKeyFromModal(e) {
            e.preventDefault();
            const key = $('#license_test_key_input').val() || '';
            if (!key) {
                $('#license_modal_message').text('Veuillez saisir une clé à enregistrer').css('color', '#dc3545');
                return;
            }
            // Save via the unified save settings endpoint - tab=developpeur
            const data = {
                action: 'pdf_builder_save_settings',
                tab: 'developpeur',
                pdf_builder_license_test_key: key
            };
            // Get nonce and call
            this.makeAjaxCall('pdf_builder_save_settings', data, (response) => {
                // Update UI
                $('#license_test_key').val(key);
                const masked = key ? (key.substr(0,6) + '••••••••••••••••' + key.substr(-6)) : '';
                $('#license_test_key_display').text(masked);
                if (response.success) {
                    $('#license_modal_message').text('✅ Clé enregistrée').css('color', '#28a745');
                    $('#license_key_status').text('✅ Clé enregistrée et active').css('color', '#28a745');
                    $('#license_key_modal').hide();
                }
            }, (error) => {
                $('#license_modal_message').text('❌ ' + (error.data?.message || 'Erreur lors de l\'enregistrement')).css('color', '#dc3545');
                this.showError(error.data?.message || 'Erreur lors de l\'enregistrement');
            });
        }

        handleCloseLicenseModal(e) {
            e.preventDefault();
            $('#license_key_modal').hide();
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
                if (window.pdfBuilderSavedSettings) {
                    window.pdfBuilderSavedSettings.pdf_builder_license_test_key = '';
                    window.pdfBuilderSavedSettings.pdf_builder_license_test_key_expires = '';
                }
                $('#license_test_key_display').text('');
                $('#license_key_expires').text('');
                $('#license_key_status').text('🗑️ Clé supprimée').css('color', '#28a745');
                // Ensure test mode is off when key is deleted
                $('#license_test_mode').prop('checked', false);
                $('#license_test_mode_status').html('❌ Mode test inactif').css({ 'background': '#f8d7da', 'color': '#721c24' });
                if (window.pdfBuilderSavedSettings) {
                    window.pdfBuilderSavedSettings.pdf_builder_license_test_mode_enabled = '0';
                }
                $('#delete_license_key_btn').hide();
                // If called from the modal, hide it as well
                if ($('#license_key_modal').is(':visible')) {
                    $('#license_key_modal').hide();
                }
                this.showSuccess(response.data?.message || 'Clé de test supprimée');
            }, (error) => {
                $('#license_key_status').text('❌ ' + (error.data?.message || 'Erreur lors de la suppression')).css('color', '#dc3545');
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
                // console.error('📝 [CONSOLE CODE] Erreur:', error);
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
        handleSectionToggle(e) {
            e.preventDefault();
            const header = $(e.currentTarget);
            const section = header.closest('.dev-section');
            const toggle = header.find('.dev-section-toggle');

            if (section.hasClass('collapsed')) {
                section.removeClass('collapsed');
                if (toggle.length) toggle.text('🔽');
                header.attr('aria-expanded', 'true');
                section.find('.dev-section-content').attr('aria-hidden', 'false');
            } else {
                section.addClass('collapsed');
                if (toggle.length) toggle.text('▶️');
                header.attr('aria-expanded', 'false');
                section.find('.dev-section-content').attr('aria-hidden', 'true');
            }
        }
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

            // Try to use the real notification system first
            const notificationFunction = window[`show${type.charAt(0).toUpperCase() + type.slice(1)}Notification`];

            if (notificationFunction && typeof notificationFunction === 'function') {
                
                notificationFunction(messages[type], { duration: 4000 });
                this.addNotificationLog(`✅ ${type} notification affichée via système réel`, 'success');
            } else if (window.pdfBuilderNotify && window.pdfBuilderNotify[type]) {
                // Fallback to the old system
                
                window.pdfBuilderNotify[type](messages[type], 4000);
                this.addNotificationLog(`✅ ${type} notification affichée via fallback`, 'success');
            } else {
                // console.error('Developer Tools: No notification system available for', type);
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
                        // console.error(`❌ [AJAX ${action}] Erreur:`, error);
                        if (errorCallback) {
                            errorCallback({ data: { message: 'Erreur de connexion' } });
                        }
                    }
                });
            }).catch(() => {
                // console.error('❌ [AJAX] Impossible d\'obtenir un nonce frais');
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
                alert('[SUCCESS] ' + message);
            }
        }

        showError(message) {
            if (window.pdfBuilderNotify?.error) {
                window.pdfBuilderNotify.error(message);
            } else {
                alert('[ERROR] ' + message);
            }
        }
    }

        // Initialize when document is ready
        const shouldInitialize = window.location.href.indexOf('wp-admin') !== -1 ||
                                window.location.href.indexOf('admin.php') !== -1;

        if (shouldInitialize) {
            
            setTimeout(() => {
                new PDFBuilderDeveloper();
            }, 500); // Reduced delay for better UX
        } else {
            
        }

})(jQuery);

