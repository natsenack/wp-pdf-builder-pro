/**
 * JavaScript pour le système de notifications unifié de PDF Builder Pro
 */

(function($) {
    'use strict';

    class PDFBuilderNotifications {
        constructor() {
            this.toastContainer = null;
            this.config = window.pdfBuilderNotifications?.config || {};
            this.selectors = window.pdfBuilderNotifications?.selectors || {};
            this.classes = window.pdfBuilderNotifications?.classes || {};
            this.strings = window.pdfBuilderNotifications?.strings || {};
            // Init will be called on DOM ready
        }

        init() {
            this.createToastContainer();
            this.bindEvents();
            this.showQueuedToasts();
        }

        createToastContainer() {
            const containerSelector = this.selectors.container || '#pdf-builder-toast-container';
            if (!$(containerSelector).length) {
                this.toastContainer = $('<div id="pdf-builder-toast-container"></div>');
                $('body').append(this.toastContainer);
            } else {
                this.toastContainer = $(containerSelector);
            }
        }

        bindEvents() {
            const closeSelector = this.selectors.close || '.pdf-builder-notification-close';
            // Délégation d'événements pour les boutons de fermeture
            $(document).on('click', closeSelector, (e) => {
                e.preventDefault();
                const notificationSelector = this.selectors.notification || '.pdf-builder-notification';
                const $notification = $(e.target).closest(notificationSelector);
                this.dismissNotification($notification);
            });
        }

        showQueuedToasts() {
            // Afficher les toasts en attente depuis PHP
            if (typeof window.pdfBuilderToasts !== 'undefined') {
                window.pdfBuilderToasts.forEach(toast => {
                    this.showToast(toast.message, toast.type, toast.duration);
                });
                // Nettoyer après affichage
                delete window.pdfBuilderToasts;
            }
        }

        showToast(message, type = 'success', duration = null) {
            const defaultDuration = this.config.default_toast_duration || 6000;
            const animationDuration = this.config.animation_duration || 300;
            duration = duration !== null ? duration : defaultDuration;

            const toastId = 'toast_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            const closeText = this.strings.close || '×';

            // Utiliser le template au lieu de générer HTML
            const templateSelector = this.selectors.template || '#pdf-builder-toast-template';
            const template = document.getElementById(templateSelector.replace('#', ''));
            if (!template) {
                console.error('Template de toast non trouvé');
                return;
            }

            const toast = template.content.cloneNode(true).querySelector(this.classes.notification || '.pdf-builder-notification');
            toast.id = toastId;
            const typeClass = this.classes['type_' + type] || `pdf-builder-notification-${type}`;
            toast.classList.add(typeClass);

            // Remplir le contenu
            const messageSelector = this.classes.message || '.pdf-builder-notification-message';
            const closeSelector = this.classes.close || '.pdf-builder-notification-close';
            const messageEl = toast.querySelector(messageSelector);
            const closeEl = toast.querySelector(closeSelector);
            if (messageEl) messageEl.textContent = this.escapeHtml(message);
            if (closeEl) closeEl.textContent = closeText;

            const $toast = $(toast);

            if (!this.toastContainer || this.toastContainer.length === 0) {
                this.createToastContainer();
            }

            this.toastContainer.append(toast);

            // Animation d'entrée avec CSS transitions
            // L'état initial est déjà défini en CSS (opacity: 0, transform: translateX(100%))

            // Forcer un reflow pour que les styles soient appliqués
            toast[0].offsetHeight;

            // Déclencher l'animation en ajoutant la classe
            const visibleClass = this.classes.visible || 'pdf-builder-notification-visible';
            toast.addClass(visibleClass);

            // Attendre la fin de l'animation
            setTimeout(() => {
                // Animation terminée
            }, animationDuration);

            let dismissTimeout;

            // Fonction pour démarrer le timer d'auto-dismiss
            const startDismissTimer = () => {
                if (dismissTimeout) clearTimeout(dismissTimeout);
                dismissTimeout = setTimeout(() => {
                    this.dismissNotification(toast);
                }, duration);
            };

            // Fonction pour arrêter le timer
            const stopDismissTimer = () => {
                if (dismissTimeout) {
                    clearTimeout(dismissTimeout);
                    dismissTimeout = null;
                }
            };

            // Démarrer le timer initial
            if (duration > 0) {
                startDismissTimer();
            }

            // Gérer les événements souris pour pause/reprise
            toast.on('mouseenter', stopDismissTimer);
            toast.on('mouseleave', startDismissTimer);

            return toastId;
        }

        showInline(message, type = 'success', target = null) {
            const closeText = window.pdfBuilderNotifications?.strings?.close || '×';
            const notification = $(`
                <div class="pdf-builder-notification pdf-builder-notification-${type} pdf-builder-notification-inline">
                    <span class="pdf-builder-notification-icon"></span>
                    <span class="pdf-builder-notification-message">${this.escapeHtml(message)}</span>
                    <span class="pdf-builder-notification-close">${closeText}</span>
                </div>
            `);

            if (target) {
                $(target).prepend(notification);
            } else {
                $('.wrap h1').after(notification);
            }

            return notification;
        }

        dismissNotification($notification) {
            const animationDuration = this.config.animation_duration || 300;
            const dismissingClass = this.classes.dismissing || 'pdf-builder-notification-dismissing';
            // Animation de sortie avec CSS class
            $notification.addClass(dismissingClass);

            setTimeout(() => {
                $notification.remove();
            }, animationDuration);
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }

    // Initialiser immédiatement (pas seulement dans document.ready)
    window.pdfBuilderNotifications = new PDFBuilderNotifications();

    // Initialiser aussi quand le DOM est prêt (au cas où)
    $(document).ready(function() {
        if (window.pdfBuilderNotifications) {
            window.pdfBuilderNotifications.init();
        }
    });

    // API globale compatible avec le code PHP
    window.PDF_Builder_Notification_Manager = {
        show_toast: function(message, type, duration) {
            if (window.pdfBuilderNotifications && window.pdfBuilderNotifications.showToast) {
                return window.pdfBuilderNotifications.showToast(message, type, duration);
            }
        },
        show_inline: function(message, type, target) {
            if (window.pdfBuilderNotifications && window.pdfBuilderNotifications.showInline) {
                return window.pdfBuilderNotifications.showInline(message, type, target);
            }
        },
        add_toast: function(message, type, duration) {
            return this.show_toast(message, type, duration);
        },
        add_inline: function(message, type, target) {
            return this.show_inline(message, type, target);
        }
    };

})(jQuery);
