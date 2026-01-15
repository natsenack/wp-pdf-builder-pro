/**
 * PDF Builder Pro - Notifications System
 * Gère les notifications utilisateur
 */

(function() {
    'use strict';

    console.log('[PDF Builder] Notifications module loaded');

    /**
     * Notification Manager
     */
    window.PDFBuilderNotifications = {
        /**
         * Afficher une notification
         */
        show: function(message, type, duration) {
            type = type || 'info';
            duration = duration || 5000;

            var className = 'pdf-builder-notice notice notice-' + type + ' is-dismissible';
            var html = '<div class="' + className + '">' +
                       '<p>' + message + '</p>' +
                       '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Rejeter ce message.</span></button>' +
                       '</div>';

            // Ajouter à la page
            var adminNotices = document.querySelector('.wrap') || document.body;
            var noticeDiv = document.createElement('div');
            noticeDiv.innerHTML = html;
            adminNotices.insertBefore(noticeDiv.firstChild, adminNotices.firstChild);

            // Fermer automatiquement
            if (duration > 0) {
                setTimeout(function() {
                    var notice = document.querySelector('.' + className);
                    if (notice) {
                        notice.remove();
                    }
                }, duration);
            }
        },

        /**
         * Afficher une notification de succès
         */
        success: function(message, duration) {
            this.show(message, 'success', duration);
        },

        /**
         * Afficher une notification d'erreur
         */
        error: function(message, duration) {
            this.show(message, 'error', duration || 0);
        },

        /**
         * Afficher une notification d'avertissement
         */
        warning: function(message, duration) {
            this.show(message, 'warning', duration || 0);
        },

        /**
         * Afficher une notification d'information
         */
        info: function(message, duration) {
            this.show(message, 'info', duration);
        }
    };

    // Exposer globalement
    if (typeof window.pdfBuilderNotifications === 'undefined') {
        window.pdfBuilderNotifications = window.PDFBuilderNotifications;
    }
})();
