/**
 * PDF Builder Pro - Notifications System
 * Handles displaying notifications to users
 */

(function(window, $) {
    'use strict';

    // Notification Manager Class
    function NotificationManager(settings) {
        this.settings = $.extend({
            enabled: true,
            position: 'top-right',
            duration: 5000,
            max_notifications: 5,
            animation: 'slide',
            theme: 'modern'
        }, settings || {});

        this.notifications = [];
        this.container = null;
        this.init();
    }

    NotificationManager.prototype.init = function() {
        if (!this.settings.enabled) return;

        // Create container if it doesn't exist
        if (!$('#pdf-builder-notifications-container').length) {
            this.container = $('<div id="pdf-builder-notifications-container" class="pdf-builder-notifications-container ' + this.settings.position + ' ' + this.settings.theme + '"></div>');
            $('body').append(this.container);
        } else {
            this.container = $('#pdf-builder-notifications-container');
        }

        // Add CSS if not already added
        this.addStyles();
    };

    NotificationManager.prototype.addStyles = function() {
        if ($('#pdf-builder-notifications-styles').length) return;

        var css = `
            .pdf-builder-notifications-container {
                position: fixed;
                z-index: 999999;
                pointer-events: none;
            }
            .pdf-builder-notifications-container.top-right {
                top: 20px;
                right: 20px;
            }
            .pdf-builder-notifications-container.top-left {
                top: 20px;
                left: 20px;
            }
            .pdf-builder-notifications-container.bottom-right {
                bottom: 20px;
                right: 20px;
            }
            .pdf-builder-notifications-container.bottom-left {
                bottom: 20px;
                left: 20px;
            }
            .pdf-builder-notification {
                pointer-events: auto;
                margin-bottom: 10px;
                min-width: 300px;
                max-width: 500px;
                padding: 15px 20px;
                border-radius: 4px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                display: flex;
                align-items: center;
                justify-content: space-between;
                opacity: 0;
                transform: translateX(100%);
                transition: all 0.3s ease;
            }
            .pdf-builder-notification.show {
                opacity: 1;
                transform: translateX(0);
            }
            .pdf-builder-notification.success {
                background-color: #d4edda;
                border-left: 4px solid #28a745;
                color: #155724;
            }
            .pdf-builder-notification.error {
                background-color: #f8d7da;
                border-left: 4px solid #dc3545;
                color: #721c24;
            }
            .pdf-builder-notification.warning {
                background-color: #fff3cd;
                border-left: 4px solid #ffc107;
                color: #856404;
            }
            .pdf-builder-notification.info {
                background-color: #d1ecf1;
                border-left: 4px solid #17a2b8;
                color: #0c5460;
            }
            .pdf-builder-notification-content {
                flex: 1;
                font-size: 14px;
                line-height: 1.4;
            }
            .pdf-builder-notification-close {
                cursor: pointer;
                margin-left: 15px;
                font-size: 18px;
                opacity: 0.7;
                transition: opacity 0.2s;
            }
            .pdf-builder-notification-close:hover {
                opacity: 1;
            }
        `;

        $('<style id="pdf-builder-notifications-styles">' + css + '</style>').appendTo('head');
    };

    NotificationManager.prototype.show = function(type, message, options) {
        if (!this.settings.enabled) return;

        options = $.extend({
            duration: this.settings.duration,
            closable: true
        }, options || {});

        // Create notification element
        var notification = $('<div class="pdf-builder-notification ' + type + '">' +
            '<div class="pdf-builder-notification-content">' + message + '</div>' +
            (options.closable ? '<span class="pdf-builder-notification-close">&times;</span>' : '') +
            '</div>');

        // Add to container
        this.container.append(notification);
        this.notifications.push(notification);

        // Limit number of notifications
        if (this.notifications.length > this.settings.max_notifications) {
            this.notifications.shift().remove();
        }

        // Show notification
        setTimeout(function() {
            notification.addClass('show');
        }, 10);

        // Auto hide
        if (options.duration > 0) {
            setTimeout(function() {
                this.hide(notification);
            }.bind(this), options.duration);
        }

        // Close button handler
        notification.find('.pdf-builder-notification-close').on('click', function() {
            this.hide(notification);
        }.bind(this));

        return notification;
    };

    NotificationManager.prototype.hide = function(notification) {
        notification.removeClass('show');
        setTimeout(function() {
            notification.remove();
            var index = this.notifications.indexOf(notification);
            if (index > -1) {
                this.notifications.splice(index, 1);
            }
        }.bind(this), 300);
    };

    NotificationManager.prototype.success = function(message, options) {
        return this.show('success', message, options);
    };

    NotificationManager.prototype.error = function(message, options) {
        return this.show('error', message, options);
    };

    NotificationManager.prototype.warning = function(message, options) {
        return this.show('warning', message, options);
    };

    NotificationManager.prototype.info = function(message, options) {
        return this.show('info', message, options);
    };

    NotificationManager.prototype.clear = function() {
        this.notifications.forEach(function(notification) {
            notification.remove();
        });
        this.notifications = [];
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        // Create global instance if settings are available
        if (window.pdfBuilderNotifications) {
            window.pdfBuilderNotificationManager = new NotificationManager(window.pdfBuilderNotifications.settings);
        }
    });

    // Expose to global scope
    window.NotificationManager = NotificationManager;

})(window, jQuery);