// ajax-throttle.js - Throttling utilities for AJAX requests
(function() {
    'use strict';

    window.AjaxThrottle = {
        queue: [],
        activeRequests: 0,
        maxConcurrent: 3,
        throttleDelay: 100, // ms between requests

        throttle: function(callback, delay) {
            var delay = delay || this.throttleDelay;

            return function() {
                var context = this;
                var args = arguments;

                clearTimeout(callback._throttleTimeout);
                callback._throttleTimeout = setTimeout(function() {
                    callback.apply(context, args);
                }, delay);
            };
        },

        queueRequest: function(request) {
            this.queue.push(request);
            this.processQueue();
        },

        processQueue: function() {
            if (this.activeRequests >= this.maxConcurrent || this.queue.length === 0) {
                return;
            }

            var request = this.queue.shift();
            this.activeRequests++;

            request().always(function() {
                window.AjaxThrottle.activeRequests--;
                window.AjaxThrottle.processQueue();
            });
        },

        ajax: function(options) {
            var self = this;

            return this.throttle(function() {
                self.queueRequest(function() {
                    return jQuery.ajax(options);
                });
            })();
        },

        init: function() {
            console.log('AJAX throttle utilities initialized');
        }
    };

    // Auto-initialize
    window.AjaxThrottle.init();
})();