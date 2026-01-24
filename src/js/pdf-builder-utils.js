/**
 * PDF Builder Pro - Utilities
 * Core utility functions and classes for the PDF Builder
 */

(function(window) {
    'use strict';

    // Performance Metrics Class
    function PerformanceMetrics() {
        this.startTime = null;
        this.endTime = null;
        this.metrics = {};
    }

    PerformanceMetrics.prototype.start = function(operation) {
        this.startTime = performance.now();
        this.metrics[operation] = { start: this.startTime };
        console.log('[Performance] Started: ' + operation);
    };

    PerformanceMetrics.prototype.end = function(operation) {
        if (this.metrics[operation]) {
            this.endTime = performance.now();
            this.metrics[operation].end = this.endTime;
            this.metrics[operation].duration = this.endTime - this.metrics[operation].start;
            console.log('[Performance] Completed: ' + operation + ' (' + this.metrics[operation].duration.toFixed(2) + 'ms)');
            return this.metrics[operation].duration;
        }
        return 0;
    };

    PerformanceMetrics.prototype.getMetrics = function() {
        return this.metrics;
    };

    // Local Cache Class
    function LocalCache(prefix) {
        this.prefix = prefix || 'pdf_builder_';
        this.storage = window.localStorage;
    }

    LocalCache.prototype.set = function(key, value, ttl) {
        try {
            var item = {
                value: value,
                timestamp: Date.now(),
                ttl: ttl || 3600000 // 1 hour default
            };
            this.storage.setItem(this.prefix + key, JSON.stringify(item));
            return true;
        } catch (e) {
            console.warn('[LocalCache] Failed to set item:', e);
            return false;
        }
    };

    LocalCache.prototype.get = function(key) {
        try {
            var item = JSON.parse(this.storage.getItem(this.prefix + key));
            if (item && item.timestamp && (Date.now() - item.timestamp) < item.ttl) {
                return item.value;
            } else if (item) {
                // Expired, remove it
                this.remove(key);
            }
        } catch (e) {
            console.warn('[LocalCache] Failed to get item:', e);
        }
        return null;
    };

    LocalCache.prototype.remove = function(key) {
        try {
            this.storage.removeItem(this.prefix + key);
            return true;
        } catch (e) {
            console.warn('[LocalCache] Failed to remove item:', e);
            return false;
        }
    };

    LocalCache.prototype.clear = function() {
        try {
            var keys = Object.keys(this.storage);
            keys.forEach(function(key) {
                if (key.indexOf(this.prefix) === 0) {
                    this.storage.removeItem(key);
                }
            }.bind(this));
            return true;
        } catch (e) {
            console.warn('[LocalCache] Failed to clear cache:', e);
            return false;
        }
    };

    // Utility functions
    var utils = {
        // Debounce function
        debounce: function(func, wait, immediate) {
            var timeout;
            return function executedFunction() {
                var context = this;
                var args = arguments;
                var later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        },

        // Throttle function
        throttle: function(func, limit) {
            var inThrottle;
            return function() {
                var args = arguments;
                var context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(function() { inThrottle = false; }, limit);
                }
            };
        },

        // Deep clone object
        deepClone: function(obj) {
            if (obj === null || typeof obj !== 'object') return obj;
            if (obj instanceof Date) return new Date(obj.getTime());
            if (obj instanceof Array) return obj.map(item => this.deepClone(item));
            if (typeof obj === 'object') {
                var clonedObj = {};
                for (var key in obj) {
                    if (obj.hasOwnProperty(key)) {
                        clonedObj[key] = this.deepClone(obj[key]);
                    }
                }
                return clonedObj;
            }
        },

        // Generate unique ID
        generateId: function() {
            return Date.now().toString(36) + Math.random().toString(36).substr(2);
        },

        // Check if element is in viewport
        isInViewport: function(element) {
            if (!element) return false;
            var rect = element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }
    };

    // Expose to global scope
    window.PerformanceMetrics = PerformanceMetrics;
    window.LocalCache = LocalCache;
    window.pdfBuilderUtils = utils;

    // Initialize global instances
    window.pdfBuilderPerformance = new PerformanceMetrics();
    window.pdfBuilderCache = new LocalCache();

})(window);