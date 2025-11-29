/**
 * JavaScript pour le système de notifications PDF Builder Pro
 *
 * @package PDF_Builder
 * @since 2.0.0
 */

(function(window, document) {
    'use strict';

    // Vérifier si jQuery est disponible
    if (typeof jQuery === 'undefined') {
        console.error('PDF Builder Notifications: jQuery is required');
        return;
    }

    var $ = jQuery;

    /**
     * Gestionnaire de notifications unifié
     */
    var PDF_Builder_Notifications = {

        // Configuration par défaut
        config: {
            animation_duration: 300,
            default_toast_duration: 6000,
            max_toasts: 5,
            container_id: 'pdf-builder-toast-container',
            template_id: 'pdf-builder-toast-template'
        },

        // File d'attente des toasts
        queue: [],

        // Toasts actifs
        activeToasts: [],

        /**
         * Initialise le système de notifications
         */
        init: function(options) {
            // Fusionner les options
            if (options && typeof options === 'object') {
                this.config = $.extend({}, this.config, options);
            }

            // Créer le conteneur si nécessaire
            this.ensureContainer();

            // Injecter les styles CSS si nécessaire
            this.injectStyles();

            // Écouter les événements personnalisés
            this.bindEvents();

            // Afficher les toasts en attente depuis PHP
            this.displayQueuedToasts();

            console.log('PDF Builder Notifications initialized');
        },

        /**
         * S'assure que le conteneur de toasts existe
         */
        ensureContainer: function() {
            var $container = $('#' + this.config.container_id);

            if ($container.length === 0) {
                // Créer le conteneur
                $container = $('<div>', {
                    id: this.config.container_id,
                    class: 'pdf-builder-toast-container'
                });

                // Créer le template
                var $template = $('<template>', {
                    id: this.config.template_id
                }).html(
                    '<div class="pdf-builder-notification pdf-builder-notification-toast" role="alert" aria-live="assertive">' +
                        '<div class="pdf-builder-notification-icon">' +
                            '<span class="pdf-builder-notification-icon-symbol" aria-hidden="true"></span>' +
                        '</div>' +
                        '<div class="pdf-builder-notification-content">' +
                            '<div class="pdf-builder-notification-message"></div>' +
                        '</div>' +
                        '<button class="pdf-builder-notification-close" type="button" aria-label="' + (this.config.strings ? this.config.strings.close : 'Fermer') + '">' +
                            '<span aria-hidden="true">×</span>' +
                        '</button>' +
                    '</div>'
                );

                $container.append($template);
                $('body').append($container);
            }

            // Afficher le conteneur
            $container.show();
        },

        /**
         * Injecte les styles CSS si nécessaire
         */
        injectStyles: function() {
            if ($('#pdf-builder-notifications-css').length === 0) {
                var cssUrl = this.config.css_url || (window.pdfBuilderAjax ? window.pdfBuilderAjax.plugin_url + 'assets/css/notifications.css' : '');
                if (cssUrl) {
                    $('<link>', {
                        id: 'pdf-builder-notifications-css',
                        rel: 'stylesheet',
                        href: cssUrl
                    }).appendTo('head');
                }
            }
        },

        /**
         * Lie les événements
         */
        bindEvents: function() {
            var self = this;

            // Délégation d'événements pour les boutons de fermeture
            $(document).on('click', '.pdf-builder-notification-close', function(e) {
                e.preventDefault();
                var $toast = $(this).closest('.pdf-builder-notification-toast');
                var toastId = $toast.data('toast-id');
                self.dismiss(toastId);
            });

            // Auto-dismiss après la durée spécifiée
            $(document).on('mouseenter', '.pdf-builder-notification-toast', function() {
                var $toast = $(this);
                $toast.data('pause-timer', true);
            });

            $(document).on('mouseleave', '.pdf-builder-notification-toast', function() {
                var $toast = $(this);
                $toast.data('pause-timer', false);
            });
        },

        /**
         * Affiche les toasts mis en file d'attente depuis PHP
         */
        displayQueuedToasts: function() {
            if (window.pdfBuilderToasts && Array.isArray(window.pdfBuilderToasts)) {
                var self = this;
                window.pdfBuilderToasts.forEach(function(toast) {
                    self.show(toast.message, toast.type, toast.duration);
                });
                // Vider la file d'attente globale
                window.pdfBuilderToasts = [];
            }
        },

        /**
         * Affiche une notification toast
         */
        show: function(message, type, duration) {
            type = type || 'info';
            duration = duration || this.config.default_toast_duration;

            var toast = {
                id: this.generateId(),
                message: message,
                type: type,
                duration: duration,
                timestamp: Date.now()
            };

            // Ajouter à la file d'attente
            this.queue.push(toast);

            // Traiter la file d'attente
            this.processQueue();

            return toast.id;
        },

        /**
         * Traite la file d'attente des toasts
         */
        processQueue: function() {
            var self = this;

            // Limiter le nombre de toasts actifs
            while (this.activeToasts.length >= this.config.max_toasts) {
                var oldestToast = this.activeToasts.shift();
                this.removeToast(oldestToast.id);
            }

            // Traiter les toasts en attente
            while (this.queue.length > 0 && this.activeToasts.length < this.config.max_toasts) {
                var toast = this.queue.shift();
                this.displayToast(toast);
            }
        },

        /**
         * Affiche un toast individuel
         */
        displayToast: function(toast) {
            var self = this;

            // Cloner le template
            var $template = $('#' + this.config.template_id);
            if ($template.length === 0) {
                console.error('PDF Builder Notifications: Template not found');
                return;
            }

            var $toast = $template.html();
            $toast = $($toast);

            // Configurer le toast
            $toast
                .attr('data-toast-id', toast.id)
                .addClass('pdf-builder-notification-' + toast.type)
                .find('.pdf-builder-notification-message')
                .html(toast.message);

            // Ajouter au conteneur
            $('#' + this.config.container_id).append($toast);

            // Ajouter aux toasts actifs
            this.activeToasts.push(toast);

            // Forcer le reflow pour l'animation
            $toast[0].offsetHeight;

            // Afficher avec animation
            setTimeout(function() {
                $toast.addClass('pdf-builder-notification-visible');
            }, 10);

            // Programmer l'auto-dismiss
            if (toast.duration > 0) {
                this.scheduleDismiss(toast.id, toast.duration);
            }

            // Émettre un événement
            $(document).trigger('pdf-builder-notification-shown', [toast]);
        },

        /**
         * Programme le masquage automatique d'un toast
         */
        scheduleDismiss: function(toastId, duration) {
            var self = this;
            var $toast = $('[data-toast-id="' + toastId + '"]');

            var dismissTimer = setTimeout(function() {
                if (!$toast.data('pause-timer')) {
                    self.dismiss(toastId);
                }
            }, duration);

            $toast.data('dismiss-timer', dismissTimer);
        },

        /**
         * Masque un toast
         */
        dismiss: function(toastId) {
            var self = this;
            var $toast = $('[data-toast-id="' + toastId + '"]');

            if ($toast.length === 0) {
                return;
            }

            // Annuler le timer d'auto-dismiss
            var timer = $toast.data('dismiss-timer');
            if (timer) {
                clearTimeout(timer);
            }

            // Animation de sortie
            $toast
                .removeClass('pdf-builder-notification-visible')
                .addClass('pdf-builder-notification-dismissing');

            // Supprimer après l'animation
            setTimeout(function() {
                self.removeToast(toastId);
            }, this.config.animation_duration);

            // Émettre un événement
            $(document).trigger('pdf-builder-notification-dismissed', [toastId]);
        },

        /**
         * Supprime complètement un toast
         */
        removeToast: function(toastId) {
            $('[data-toast-id="' + toastId + '"]').remove();

            // Retirer des toasts actifs
            this.activeToasts = this.activeToasts.filter(function(toast) {
                return toast.id !== toastId;
            });

            // Traiter la file d'attente restante
            this.processQueue();
        },

        /**
         * Génère un ID unique pour un toast
         */
        generateId: function() {
            return 'toast_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        },

        /**
         * Méthodes de commodité pour différents types de notifications
         */
        success: function(message, duration) {
            return this.show(message, 'success', duration);
        },

        error: function(message, duration) {
            return this.show(message, 'error', duration);
        },

        warning: function(message, duration) {
            return this.show(message, 'warning', duration);
        },

        info: function(message, duration) {
            return this.show(message, 'info', duration);
        },

        /**
         * API AJAX pour les notifications côté serveur
         */
        ajaxShow: function(message, type, duration) {
            var self = this;

            return $.ajax({
                url: (window.pdfBuilderAjax ? window.pdfBuilderAjax.ajaxurl : ajaxurl),
                type: 'POST',
                data: {
                    action: 'pdf_builder_show_toast',
                    message: message,
                    type: type || 'info',
                    duration: duration || this.config.default_toast_duration,
                    nonce: (window.pdfBuilderAjax ? window.pdfBuilderAjax.nonce : '')
                },
                success: function(response) {
                    if (response.success) {
                        // Le toast sera affiché côté serveur
                    } else {
                        console.error('PDF Builder Notifications: AJAX error', response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('PDF Builder Notifications: AJAX failed', error);
                }
            });
        }
    };

    // Exposer globalement
    window.pdfBuilderNotifications = PDF_Builder_Notifications;
    window.PDF_Builder_Notification_Manager = {
        show_toast: function(message, type, duration) {
            return PDF_Builder_Notifications.show(message, type, duration);
        }
    };

    // Initialisation automatique quand le DOM est prêt
    $(document).ready(function() {
        // Fusionner avec la configuration de localisation si disponible
        var config = {};
        if (window.pdfBuilderNotifications && window.pdfBuilderNotifications.config) {
            config = window.pdfBuilderNotifications.config;
        }

        PDF_Builder_Notifications.init(config);
    });

})(window, document);