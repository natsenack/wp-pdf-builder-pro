// PDF Builder Developer Tools
// Version: 1.0.0
// Date: 2026-01-02

(function($) {
    'use strict';

    if (typeof window.pdfBuilderDeveloperTools === 'undefined') {
        window.pdfBuilderDeveloperTools = {
            // Configuration
            config: {
                enabled: false,
                debug: false,
                verbose: false
            },

            // État
            isInitialized: false,

            // Initialiser les outils développeur
            init: function() {
                if (this.isInitialized) return;

                // Récupérer la configuration depuis les paramètres WordPress
                if (typeof window.pdfBuilderDebugSettings !== 'undefined') {
                    this.config = $.extend({}, this.config, window.pdfBuilderDebugSettings);
                }

                if (!this.config.enabled) {
                    console.log('[PDF Builder DevTools] Developer tools disabled');
                    return;
                }

                console.log('[PDF Builder DevTools] Initializing developer tools...', this.config);

                this.createDebugPanel();
                this.bindEvents();
                this.addDebugHelpers();

                this.isInitialized = true;
                console.log('[PDF Builder DevTools] Developer tools initialized');
            },

            // Créer le panneau de debug
            createDebugPanel: function() {
                if (!$('#pdf-builder-debug-panel').length) {
                    const panel = $(`
                        <div id="pdf-builder-debug-panel" style="
                            position: fixed;
                            bottom: 10px;
                            left: 10px;
                            width: 300px;
                            max-height: 400px;
                            background: rgba(0, 0, 0, 0.9);
                            color: #00ff00;
                            font-family: monospace;
                            font-size: 11px;
                            padding: 10px;
                            border-radius: 5px;
                            z-index: 999999;
                            overflow-y: auto;
                            display: none;
                        ">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <strong>PDF Builder Debug</strong>
                                <button id="debug-close-btn" style="background: none; border: none; color: #00ff00; cursor: pointer; font-size: 14px;">×</button>
                            </div>
                            <div id="debug-content" style="max-height: 350px; overflow-y: auto;"></div>
                        </div>
                    `);

                    $('body').append(panel);

                    // Toggle avec Ctrl+Shift+D
                    $(document).on('keydown', function(e) {
                        if (e.ctrlKey && e.shiftKey && e.keyCode === 68) { // D
                            e.preventDefault();
                            panel.toggle();
                        }
                    });

                    $('#debug-close-btn').on('click', function() {
                        panel.hide();
                    });
                }
            },

            // Lier les événements
            bindEvents: function() {
                // Écouter les erreurs JavaScript
                window.addEventListener('error', (e) => {
                    this.log('error', 'JavaScript Error: ' + e.message + ' at ' + e.filename + ':' + e.lineno);
                });

                // Écouter les erreurs AJAX
                $(document).ajaxError((event, xhr, settings, thrownError) => {
                    this.log('error', 'AJAX Error: ' + thrownError + ' for ' + settings.url);
                });
            },

            // Ajouter des helpers de debug
            addDebugHelpers: function() {
                // Fonction globale pour logger
                window.pdfDebug = (message, type = 'info') => {
                    this.log(type, message);
                };

                // Fonction pour inspecter l'état de l'éditeur
                window.inspectEditorState = () => {
                    if (window.pdfBuilderReact && window.pdfBuilderReact.getEditorState) {
                        const state = window.pdfBuilderReact.getEditorState();
                        console.log('Editor State:', state);
                        this.log('info', 'Editor state inspected (check console)');
                        return state;
                    } else {
                        this.log('warning', 'Editor not available for inspection');
                        return null;
                    }
                };

                // Fonction pour tester les notifications
                window.testNotifications = () => {
                    if (window.pdfBuilderNotifications) {
                        window.pdfBuilderNotifications.success('Test success notification');
                        window.pdfBuilderNotifications.error('Test error notification');
                        window.pdfBuilderNotifications.warning('Test warning notification');
                        window.pdfBuilderNotifications.info('Test info notification');
                        this.log('info', 'Test notifications sent');
                    } else {
                        this.log('warning', 'Notifications system not available');
                    }
                };
            },

            // Logger un message
            log: function(type, message) {
                if (!this.config.enabled) return;

                const timestamp = new Date().toLocaleTimeString();
                const logEntry = `[${timestamp}] ${type.toUpperCase()}: ${message}`;

                console.log(logEntry);

                if (this.config.verbose || type === 'error') {
                    this.addToPanel(logEntry, type);
                }
            },

            // Ajouter au panneau de debug
            addToPanel: function(message, type) {
                const panel = $('#debug-content');
                if (!panel.length) return;

                const color = this.getLogColor(type);
                const entry = $(`<div style="margin-bottom: 3px; color: ${color};">${message}</div>`);

                panel.append(entry);
                panel.scrollTop(panel[0].scrollHeight);
            },

            // Couleur selon le type de log
            getLogColor: function(type) {
                const colors = {
                    error: '#ff4444',
                    warning: '#ffaa00',
                    info: '#00aaff',
                    success: '#00aa00',
                    debug: '#aaaaaa'
                };
                return colors[type] || colors.info;
            },

            // Méthodes publiques
            enable: function() {
                this.config.enabled = true;
                this.init();
            },

            disable: function() {
                this.config.enabled = false;
                $('#pdf-builder-debug-panel').hide();
            },

            clear: function() {
                $('#debug-content').empty();
                console.clear();
            }
        };
    }

    // Initialiser automatiquement
    $(document).ready(function() {
        if (window.pdfBuilderDeveloperTools) {
            window.pdfBuilderDeveloperTools.init();
        }
    });

})(jQuery);