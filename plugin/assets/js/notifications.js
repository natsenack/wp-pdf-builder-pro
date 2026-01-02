// PDF Builder Notifications System
// Version: 1.0.0
// Date: 2026-01-02

(function($) {
    'use strict';

    if (typeof window.pdfBuilderNotifications === 'undefined') {
        window.pdfBuilderNotifications = {
            // Configuration par défaut
            config: {
                enabled: true,
                position: 'top-right',
                duration: 5000,
                max_notifications: 5,
                animation: 'slide',
                theme: 'modern'
            },

            // File d'attente des notifications
            queue: [],

            // Conteneur des notifications
            container: null,

            // Initialiser le système
            init: function() {
                if (!this.config.enabled) return;

                this.createContainer();
                this.bindEvents();
            },

            // Créer le conteneur
            createContainer: function() {
                if (this.container) return;

                const positions = {
                    'top-right': 'top: 20px; right: 20px;',
                    'top-left': 'top: 20px; left: 20px;',
                    'bottom-right': 'bottom: 20px; right: 20px;',
                    'bottom-left': 'bottom: 20px; left: 20px;',
                    'top-center': 'top: 20px; left: 50%; transform: translateX(-50%);'
                };

                const style = positions[this.config.position] || positions['top-right'];

                this.container = $('<div>', {
                    id: 'pdf-builder-notifications-container',
                    css: {
                        position: 'fixed',
                        zIndex: '999999',
                        pointerEvents: 'none',
                        ...this.parseStyle(style)
                    }
                }).appendTo('body');
            },

            // Analyser les styles CSS
            parseStyle: function(styleString) {
                const styles = {};
                const declarations = styleString.split(';');

                declarations.forEach(decl => {
                    const parts = decl.split(':');
                    if (parts.length === 2) {
                        const property = parts[0].trim();
                        const value = parts[1].trim();
                        styles[property] = value;
                    }
                });

                return styles;
            },

            // Lier les événements
            bindEvents: function() {
                // Rien à lier pour le moment
            },

            // Afficher une notification
            show: function(message, type = 'info', options = {}) {
                if (!this.config.enabled) return;

                const notification = {
                    id: Date.now(),
                    message: message,
                    type: type,
                    options: $.extend({}, this.config, options),
                    timestamp: new Date()
                };

                this.queue.push(notification);
                this.render(notification);

                // Limiter le nombre de notifications
                if (this.queue.length > this.config.max_notifications) {
                    const oldest = this.queue.shift();
                    this.remove(oldest.id);
                }

                return notification.id;
            },

            // Rendre une notification
            render: function(notification) {
                if (!this.container) this.init();

                const notificationEl = $('<div>', {
                    class: 'pdf-builder-notification pdf-builder-notification-' + notification.type,
                    'data-id': notification.id,
                    css: {
                        background: this.getBackgroundColor(notification.type),
                        color: this.getTextColor(notification.type),
                        padding: '12px 16px',
                        marginBottom: '10px',
                        borderRadius: '4px',
                        boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
                        pointerEvents: 'auto',
                        cursor: 'pointer',
                        minWidth: '300px',
                        maxWidth: '500px',
                        position: 'relative',
                        opacity: '0',
                        transform: 'translateY(-20px)',
                        transition: 'all 0.3s ease'
                    }
                });

                // Icône selon le type
                const icon = this.getIcon(notification.type);
                if (icon) {
                    notificationEl.append($('<span>', {
                        class: 'notification-icon',
                        html: icon,
                        css: {
                            marginRight: '8px',
                            fontSize: '16px'
                        }
                    }));
                }

                // Message
                notificationEl.append($('<span>', {
                    class: 'notification-message',
                    text: notification.message
                }));

                // Bouton de fermeture
                const closeBtn = $('<button>', {
                    class: 'notification-close',
                    html: '×',
                    css: {
                        position: 'absolute',
                        top: '5px',
                        right: '8px',
                        background: 'none',
                        border: 'none',
                        fontSize: '18px',
                        cursor: 'pointer',
                        color: 'inherit',
                        opacity: '0.7'
                    }
                });

                closeBtn.on('click', () => this.remove(notification.id));
                notificationEl.append(closeBtn);

                // Ajouter au conteneur
                this.container.append(notificationEl);

                // Animation d'entrée
                setTimeout(() => {
                    notificationEl.css({
                        opacity: '1',
                        transform: 'translateY(0)'
                    });
                }, 10);

                // Auto-suppression
                if (notification.options.duration > 0) {
                    setTimeout(() => {
                        this.remove(notification.id);
                    }, notification.options.duration);
                }

                // Supprimer au clic
                notificationEl.on('click', () => this.remove(notification.id));
            },

            // Supprimer une notification
            remove: function(id) {
                const notificationEl = this.container.find('[data-id="' + id + '"]');
                if (notificationEl.length) {
                    notificationEl.css({
                        opacity: '0',
                        transform: 'translateY(-20px)'
                    });

                    setTimeout(() => {
                        notificationEl.remove();
                    }, 300);
                }

                // Retirer de la file
                this.queue = this.queue.filter(n => n.id !== id);
            },

            // Couleurs selon le type
            getBackgroundColor: function(type) {
                const colors = {
                    success: '#d4edda',
                    error: '#f8d7da',
                    warning: '#fff3cd',
                    info: '#d1ecf1'
                };
                return colors[type] || colors.info;
            },

            getTextColor: function(type) {
                const colors = {
                    success: '#155724',
                    error: '#721c24',
                    warning: '#856404',
                    info: '#0c5460'
                };
                return colors[type] || colors.info;
            },

            getIcon: function(type) {
                const icons = {
                    success: '✓',
                    error: '✕',
                    warning: '⚠',
                    info: 'ℹ'
                };
                return icons[type] || '';
            },

            // Méthodes publiques
            success: function(message, options) { return this.show(message, 'success', options); },
            error: function(message, options) { return this.show(message, 'error', options); },
            warning: function(message, options) { return this.show(message, 'warning', options); },
            info: function(message, options) { return this.show(message, 'info', options); },

            // Nettoyer
            destroy: function() {
                if (this.container) {
                    this.container.remove();
                    this.container = null;
                }
                this.queue = [];
            }
        };
    }

    // Initialiser automatiquement
    $(document).ready(function() {
        if (window.pdfBuilderNotifications) {
            window.pdfBuilderNotifications.init();
        }
    });

})(jQuery);