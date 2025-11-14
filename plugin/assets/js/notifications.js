/**
 * JavaScript pour le système de notifications unifié de PDF Builder Pro
 */

(function($) {
    'use strict';

    class PDFBuilderNotifications {
        constructor() {
            this.toastContainer = null;
            this.init();
        }

        init() {
            this.createToastContainer();
            this.bindEvents();
            this.showQueuedToasts();
        }

        createToastContainer() {
            if (!$('#pdf-builder-toast-container').length) {
                this.toastContainer = $('<div id="pdf-builder-toast-container" style="position: fixed; top: 40px; right: 20px; z-index: 10000; pointer-events: none;"></div>');
                $('body').append(this.toastContainer);
            } else {
                this.toastContainer = $('#pdf-builder-toast-container');
            }
        }

        bindEvents() {
            // Délégation d'événements pour les boutons de fermeture
            $(document).on('click', '.pdf-builder-notification-close', (e) => {
                e.preventDefault();
                const $notification = $(e.target).closest('.pdf-builder-notification');
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

        showToast(message, type = 'success', duration = 6000) {

            const toastId = 'toast_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

            const toast = $(`
                <div class="pdf-builder-notification pdf-builder-notification-${type}" id="${toastId}" style="pointer-events: auto;">
                    <span class="pdf-builder-notification-icon">${this.getIcon(type)}</span>
                    <span class="pdf-builder-notification-message">${this.escapeHtml(message)}</span>
                    <span class="pdf-builder-notification-close" style="cursor: pointer; font-size: 16px; font-weight: bold; opacity: 0.7; transition: opacity 0.2s; flex-shrink: 0; margin-left: 4px;">×</span>
                </div>
            `);

            this.toastContainer.append(toast);

            // Animation d'entrée
            toast.css({
                'opacity': '0',
                'transform': 'translateX(100%)'
            }).animate({
                'opacity': '1',
                'transform': 'translateX(0)'
            }, 300);

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
            const notification = $(`
                <div class="pdf-builder-notification pdf-builder-notification-${type} pdf-builder-notification-inline">
                    <span class="pdf-builder-notification-icon">${this.getIcon(type)}</span>
                    <span class="pdf-builder-notification-message">${this.escapeHtml(message)}</span>
                    <span class="pdf-builder-notification-close">×</span>
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
            // Nettoyer les event listeners et timers
            $notification.off('mouseenter mouseleave');
            $notification.animate({
                'opacity': '0',
                'transform': 'translateX(100%)'
            }, 300, function() {
                $(this).remove();
            });
        }

        getIcon(type) {
            const icons = {
                'success': '✅',
                'error': '❌',
                'warning': '⚠️',
                'info': 'ℹ️'
            };
            return icons[type] || icons.info;
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
        if (!window.pdfBuilderNotifications) {
            window.pdfBuilderNotifications = new PDFBuilderNotifications();
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