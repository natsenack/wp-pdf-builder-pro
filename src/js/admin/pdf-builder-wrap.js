/**
 * PDF Builder - Wrapper Utilities
 * General utilities and helpers
 */
(function($) {
    'use strict';

    

    window.pdfBuilderWrap = {
        // Utility functions
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        throttle: function(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            }
        },

        // DOM utilities
        isVisible: function(element) {
            return !!(element.offsetWidth || element.offsetHeight || element.getClientRects().length);
        }
    };

})(jQuery);

