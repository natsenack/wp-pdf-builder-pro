/**
 * PDF Builder - Notifications System
 * Handles user notifications and messages
 */
(function($) {
    'use strict';

    

    window.pdfBuilderNotifications = {
        show: function(message, type = 'info', duration = 5000) {
            

            // Basic notification - could be enhanced with UI
            const notification = $('<div class="pdf-builder-notification pdf-builder-notification-' + type + '">' + message + '</div>');
            $('body').append(notification);

            setTimeout(function() {
                notification.fadeOut(function() {
                    notification.remove();
                });
            }, duration);
        },

        success: function(message) { this.show(message, 'success'); },
        error: function(message) { this.show(message, 'error'); },
        warning: function(message) { this.show(message, 'warning'); },
        info: function(message) { this.show(message, 'info'); }
    };

})(jQuery);

