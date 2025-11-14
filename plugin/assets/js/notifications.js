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
            console.log('NOTIFICATION: Creating toast container');
            if (!$('#pdf-builder-toast-container').length) {
                this.toastContainer = $('<div id="pdf-builder-toast-container" style="position: fixed; top: 40px; right: 20px; z-index: 10000; pointer-events: none;"></div>');
                $('body').append(this.toastContainer);
                console.log('NOTIFICATION: Container created and appended to body');
                console.log('NOTIFICATION: Container in DOM:', $('#pdf-builder-toast-container').length);
            } else {
                this.toastContainer = $('#pdf-builder-toast-container');
                console.log('NOTIFICATION: Using existing container');
            }
            console.log('NOTIFICATION: Container element:', this.toastContainer[0]);
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
            console.log('NOTIFICATION: ===== showToast called =====');
            console.log('NOTIFICATION: Message:', message);
            console.log('NOTIFICATION: Type:', type);
            console.log('NOTIFICATION: Duration:', duration);

            const toastId = 'toast_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            console.log('NOTIFICATION: Generated ID:', toastId);

            const toast = $(`
                <div class="pdf-builder-notification pdf-builder-notification-${type}" id="${toastId}" style="pointer-events: auto;">
                    <span class="pdf-builder-notification-icon">${this.getIcon(type)}</span>
                    <span class="pdf-builder-notification-message">${this.escapeHtml(message)}</span>
                    <span class="pdf-builder-notification-close" style="cursor: pointer; font-size: 16px; font-weight: bold; opacity: 0.7; transition: opacity 0.2s; flex-shrink: 0; margin-left: 4px;">×</span>
                </div>
            `);

            console.log('NOTIFICATION: Toast HTML created:', toast.prop('outerHTML'));
            console.log('NOTIFICATION: Toast has classes:', toast.hasClass('pdf-builder-notification'), toast.hasClass(`pdf-builder-notification-${type}`));

            if (!this.toastContainer || this.toastContainer.length === 0) {
                this.createToastContainer();
            }

            this.toastContainer.append(toast);

            // Vérifier immédiatement après ajout
            console.log('NOTIFICATION: Toast added to DOM');
            console.log('NOTIFICATION: Initial toast style before animation:', {
                display: toast.css('display'),
                opacity: toast.css('opacity'),
                transform: toast.css('transform'),
                visibility: toast.css('visibility')
            });

            // Animation d'entrée avec CSS transitions
            console.log('NOTIFICATION: Starting CSS transition animation');
            toast[0].style.opacity = '0';
            toast[0].style.transform = 'translateX(100%)';

            // Forcer un reflow pour que les styles soient appliqués
            toast[0].offsetHeight;

            toast[0].style.transition = 'all 0.3s ease';
            toast[0].style.opacity = '1';
            toast[0].style.transform = 'translateX(0)';

            // Attendre la fin de l'animation
            setTimeout(() => {
                console.log('NOTIFICATION: CSS animation completed');
            }, 300);

            let dismissTimeout;

            // Fonction pour démarrer le timer d'auto-dismiss
            const startDismissTimer = () => {
                console.log('NOTIFICATION: startDismissTimer called for:', toastId);
                if (dismissTimeout) {
                    console.log('NOTIFICATION: Clearing existing timeout');
                    clearTimeout(dismissTimeout);
                }
                console.log('NOTIFICATION: Setting timeout for', duration, 'ms');
                dismissTimeout = setTimeout(() => {
                    console.log('NOTIFICATION: Timeout expired, dismissing:', toastId);
                    this.dismissNotification(toast);
                }, duration);
                console.log('NOTIFICATION: Timeout set:', dismissTimeout);
            };

            // Fonction pour arrêter le timer
            const stopDismissTimer = () => {
                console.log('NOTIFICATION: stopDismissTimer called for:', toastId);
                if (dismissTimeout) {
                    console.log('NOTIFICATION: Clearing timeout:', dismissTimeout);
                    clearTimeout(dismissTimeout);
                    dismissTimeout = null;
                    console.log('NOTIFICATION: Timeout cleared');
                } else {
                    console.log('NOTIFICATION: No timeout to clear');
                }
            };

            // Démarrer le timer initial
            if (duration > 0) {
                console.log('NOTIFICATION: Starting timer with duration:', duration);
                startDismissTimer();
            } else {
                console.log('NOTIFICATION: No timer (duration = 0)');
            }

            // Gérer les événements souris pour pause/reprise
            console.log('NOTIFICATION: Setting up mouse events');
            toast.on('mouseenter', function() {
                console.log('NOTIFICATION: Mouse enter - stopping timer');
                stopDismissTimer();
            });
            toast.on('mouseleave', function() {
                console.log('NOTIFICATION: Mouse leave - starting timer');
                startDismissTimer();
            });

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
            console.log('NOTIFICATION: dismissNotification called for:', $notification.attr('id'));

            // Animation de sortie avec CSS transitions
            $notification[0].style.transition = 'all 0.3s ease';
            $notification[0].style.opacity = '0';
            $notification[0].style.transform = 'translateX(100%)';

            setTimeout(() => {
                console.log('NOTIFICATION: CSS dismiss animation complete, removing element');
                $notification.remove();
            }, 300);
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