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
            console.log('🔍 showToast called with:', message, type, duration);

            const toastId = 'toast_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

            const toast = $(`
                <div class="pdf-builder-notification pdf-builder-notification-${type}" id="${toastId}" style="pointer-events: auto;">
                    <span class="pdf-builder-notification-icon">${this.getIcon(type)}</span>
                    <span class="pdf-builder-notification-message">${this.escapeHtml(message)}</span>
                    <span class="pdf-builder-notification-close" style="cursor: pointer; font-size: 16px; font-weight: bold; opacity: 0.7; transition: opacity 0.2s; flex-shrink: 0; margin-left: 4px;">×</span>
                </div>
            `);

            console.log('🔍 Toast element created');
            console.log('🔍 Toast container exists:', !!this.toastContainer);
            console.log('🔍 Toast container length:', this.toastContainer ? this.toastContainer.length : 'N/A');

            if (!this.toastContainer || this.toastContainer.length === 0) {
                console.error('🔍 No toast container found, creating one...');
                this.createToastContainer();
            }

            this.toastContainer.append(toast);
            console.log('🔍 Toast appended to container');

            // Animation d'entrée
            toast.css({
                'opacity': '0',
                'transform': 'translateX(100%)'
            }).animate({
                'opacity': '1',
                'transform': 'translateX(0)'
            }, 300);

            console.log('🔍 Animation started');

            let dismissTimeout;

            // Fonction pour démarrer le timer d'auto-dismiss
            const startDismissTimer = () => {
                if (dismissTimeout) clearTimeout(dismissTimeout);
                dismissTimeout = setTimeout(() => {
                    console.log('🔍 Auto-dismiss triggered for toast:', toastId);
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
    console.log('🔍 Initializing PDFBuilderNotifications immediately');
    window.pdfBuilderNotifications = new PDFBuilderNotifications();
    console.log('🔍 PDFBuilderNotifications instance created:', !!window.pdfBuilderNotifications);

    // Initialiser aussi quand le DOM est prêt (au cas où)
    $(document).ready(function() {
        console.log('🔍 DOM ready, checking PDFBuilderNotifications');
        if (!window.pdfBuilderNotifications) {
            console.log('🔍 Creating PDFBuilderNotifications in DOM ready');
            window.pdfBuilderNotifications = new PDFBuilderNotifications();
        } else {
            console.log('🔍 PDFBuilderNotifications already exists');
        }
    });

    // API globale compatible avec le code PHP
    window.PDF_Builder_Notification_Manager = {
        show_toast: function(message, type, duration) {
            console.log('🔍 PDF_Builder_Notification_Manager.show_toast called:', message, type, duration);
            console.log('🔍 window.pdfBuilderNotifications exists:', !!window.pdfBuilderNotifications);
            if (window.pdfBuilderNotifications && window.pdfBuilderNotifications.showToast) {
                console.log('🔍 Calling showToast method');
                const result = window.pdfBuilderNotifications.showToast(message, type, duration);
                console.log('🔍 showToast result:', result);
                return result;
            } else {
                console.error('🔍 pdfBuilderNotifications not available');
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