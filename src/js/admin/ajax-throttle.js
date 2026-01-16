/**
 * PDF Builder - AJAX Throttle
 * Limits AJAX calls to prevent server overload
 */
(function($) {
    'use strict';

    console.log('[PDF Builder] AJAX Throttle loaded');

    // Basic throttle implementation
    window.pdfBuilderAjaxThrottle = {
        calls: new Map(),
        throttle: function(key, fn, delay = 1000) {
            const now = Date.now();
            const lastCall = this.calls.get(key);

            if (!lastCall || (now - lastCall) > delay) {
                this.calls.set(key, now);
                return fn();
            }

            console.log('[PDF Builder] AJAX call throttled for key:', key);
            return Promise.resolve(null);
        }
    };

})(jQuery);