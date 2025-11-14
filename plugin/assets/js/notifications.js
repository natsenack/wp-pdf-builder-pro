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
            console.log('DEBUG: PDFBuilderNotifications.init() called');
            this.createToastContainer();
            this.bindEvents();
            this.showQueuedToasts();
            console.log('DEBUG: PDFBuilderNotifications.init() completed');
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
            console.log('DEBUG: showQueuedToasts called');
            if (typeof window.pdfBuilderToasts !== 'undefined') {
                console.log('DEBUG: Found queued toasts:', window.pdfBuilderToasts);
                window.pdfBuilderToasts.forEach(toast => {
                    console.log('DEBUG: Showing queued toast:', toast);
                    this.showToast(toast.message, toast.type, toast.duration);
                });
                // Nettoyer après affichage
                delete window.pdfBuilderToasts;
                console.log('DEBUG: Cleared queued toasts');
            } else {
                console.log('DEBUG: No queued toasts found');
            }
        }

        showToast(message, type = 'success', duration = 6000) {
            console.log('DEBUG: showToast called with:', { message, type, duration });

            const toastId = 'toast_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            console.log('DEBUG: Generated toastId:', toastId);

            const toast = $(`
                <div class="pdf-builder-notification pdf-builder-notification-${type}" id="${toastId}" style="pointer-events: auto;">
                    <span class="pdf-builder-notification-icon">${this.getIcon(type)}</span>
                    <span class="pdf-builder-notification-message">${this.escapeHtml(message)}</span>
                    <span class="pdf-builder-notification-close" style="cursor: pointer; font-size: 16px; font-weight: bold; opacity: 0.7; transition: opacity 0.2s; flex-shrink: 0; margin-left: 4px;">×</span>
                </div>
            `);

            if (!this.toastContainer || this.toastContainer.length === 0) {
                console.log('DEBUG: Creating new toast container');
                this.createToastContainer();
            } else {
                console.log('DEBUG: Using existing toast container');
            }

            console.log('DEBUG: Toast container exists:', this.toastContainer.length > 0);
            this.toastContainer.append(toast);
            console.log('DEBUG: Toast appended to container');
            console.log('DEBUG: Toast HTML:', toast.prop('outerHTML'));
            console.log('DEBUG: Container HTML after append:', this.toastContainer.html());

            // Animation d'entrée
            console.log('DEBUG: Starting entrance animation');
            toast.css({
                'opacity': '0',
                'transform': 'translateX(100%)'
            }).animate({
                'opacity': '1',
                'transform': 'translateX(0)'
            }, 300, function() {
                console.log('DEBUG: Entrance animation completed for toast:', toastId);
                // Vérifier la visibilité après l'animation
                console.log('DEBUG: Toast visibility after animation - display:', toast.css('display'), 'opacity:', toast.css('opacity'), 'visibility:', toast.css('visibility'));
                console.log('DEBUG: Toast position - top:', toast.css('top'), 'right:', toast.css('right'), 'z-index:', toast.css('z-index'));
                console.log('DEBUG: Container position - top:', toast.parent().css('top'), 'right:', toast.parent().css('right'), 'z-index:', toast.parent().css('z-index'));
            });
            console.log('DEBUG: Entrance animation started');

            let dismissTimeout;

            // Fonction pour démarrer le timer d'auto-dismiss
            const startDismissTimer = () => {
                if (dismissTimeout) clearTimeout(dismissTimeout);
                console.log('DEBUG: Starting dismiss timer for toast:', toastId);
                dismissTimeout = setTimeout(() => {
                    console.log('DEBUG: Timer expired, dismissing toast:', toastId);
                    this.dismissNotification(toast);
                }, duration);
            };

            // Fonction pour arrêter le timer
            const stopDismissTimer = () => {
                console.log('DEBUG: Stopping dismiss timer for toast:', toastId);
                if (dismissTimeout) {
                    clearTimeout(dismissTimeout);
                    dismissTimeout = null;
                }
            };

            // Démarrer le timer initial
            if (duration > 0) {
                console.log('DEBUG: Starting dismiss timer with duration:', duration);
                startDismissTimer();
            } else {
                console.log('DEBUG: No auto-dismiss timer (duration = 0)');
            }

            // Gérer les événements souris pour pause/reprise
            console.log('DEBUG: Setting up mouse events');
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
            console.log('DEBUG: dismissNotification called for notification:', $notification.attr('id'));
            // Nettoyer les event listeners et timers
            $notification.off('mouseenter mouseleave');
            console.log('DEBUG: Starting exit animation');
            $notification.animate({
                'opacity': '0',
                'transform': 'translateX(100%)'
            }, 300, function() {
                console.log('DEBUG: Animation complete, removing notification:', $notification.attr('id'));
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