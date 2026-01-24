/**
 * PDF Builder Pro - Developer Tools
 * Development utilities and debugging tools
 */

(function(window, $) {
    'use strict';

    // Developer Tools Class
    function DeveloperTools() {
        this.debugMode = false;
        this.verboseMode = false;
        this.tools = {};
        this.init();
    }

    DeveloperTools.prototype.init = function() {
        // Check for debug settings
        if (window.pdfBuilderDebugSettings) {
            this.debugMode = window.pdfBuilderDebugSettings.javascript || false;
            this.verboseMode = window.pdfBuilderDebugSettings.javascript_verbose || false;
        }

        if (this.debugMode) {
            console.log('[Developer Tools] Initialized');
            this.setupConsoleTools();
            this.setupNetworkMonitoring();
            this.setupErrorTracking();
        }
    };

    DeveloperTools.prototype.setupConsoleTools = function() {
        // Add global debug functions
        window.pdfBuilderDebug = {
            log: function(message, data) {
                if (this.debugMode) {
                    console.log('[PDF Builder Debug]', message, data || '');
                }
            }.bind(this),

            error: function(message, error) {
                console.error('[PDF Builder Error]', message, error || '');
                if (this.debugMode && window.pdfBuilderNotificationManager) {
                    window.pdfBuilderNotificationManager.error('Debug Error: ' + message);
                }
            }.bind(this),

            warn: function(message, data) {
                console.warn('[PDF Builder Warning]', message, data || '');
                if (this.debugMode && window.pdfBuilderNotificationManager) {
                    window.pdfBuilderNotificationManager.warning('Debug Warning: ' + message);
                }
            }.bind(this),

            info: function(message, data) {
                if (this.verboseMode) {
                    console.info('[PDF Builder Info]', message, data || '');
                }
            }.bind(this)
        };
    };

    DeveloperTools.prototype.setupNetworkMonitoring = function() {
        if (!this.debugMode) return;

        // Monitor AJAX requests
        $(document).ajaxStart(function() {
            window.pdfBuilderDebug.info('AJAX request started');
        });

        $(document).ajaxComplete(function(event, xhr, settings) {
            window.pdfBuilderDebug.info('AJAX request completed', {
                url: settings.url,
                status: xhr.status,
                responseTime: xhr.responseTime || 'unknown'
            });
        });

        $(document).ajaxError(function(event, xhr, settings, thrownError) {
            window.pdfBuilderDebug.error('AJAX request failed', {
                url: settings.url,
                status: xhr.status,
                error: thrownError
            });
        });
    };

    DeveloperTools.prototype.setupErrorTracking = function() {
        if (!this.debugMode) return;

        // Global error handler
        window.addEventListener('error', function(event) {
            window.pdfBuilderDebug.error('JavaScript Error', {
                message: event.message,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno,
                error: event.error
            });
        });

        // Unhandled promise rejection handler
        window.addEventListener('unhandledrejection', function(event) {
            window.pdfBuilderDebug.error('Unhandled Promise Rejection', {
                reason: event.reason,
                promise: event.promise
            });
        });
    };

    // Performance monitoring
    DeveloperTools.prototype.startPerformanceMonitor = function() {
        if (!this.debugMode || !window.performance) return;

        this.performanceData = {
            startTime: performance.now(),
            marks: [],
            measures: []
        };

        window.pdfBuilderDebug.info('Performance monitoring started');
    };

    DeveloperTools.prototype.markPerformance = function(name) {
        if (!this.debugMode || !window.performance || !this.performanceData) return;

        performance.mark(name);
        this.performanceData.marks.push({
            name: name,
            timestamp: performance.now()
        });

        window.pdfBuilderDebug.info('Performance mark: ' + name);
    };

    DeveloperTools.prototype.measurePerformance = function(name, startMark, endMark) {
        if (!this.debugMode || !window.performance || !this.performanceData) return;

        try {
            performance.measure(name, startMark, endMark);
            var measure = performance.getEntriesByName(name)[0];
            this.performanceData.measures.push({
                name: name,
                duration: measure.duration,
                startTime: measure.startTime
            });

            window.pdfBuilderDebug.info('Performance measure: ' + name, measure.duration + 'ms');
        } catch (e) {
            window.pdfBuilderDebug.error('Performance measure failed', e);
        }
    };

    DeveloperTools.prototype.getPerformanceReport = function() {
        if (!this.performanceData) return null;

        return {
            totalTime: performance.now() - this.performanceData.startTime,
            marks: this.performanceData.marks,
            measures: this.performanceData.measures
        };
    };

    // DOM inspection tools
    DeveloperTools.prototype.inspectElement = function(selector) {
        if (!this.debugMode) return;

        var element = $(selector);
        if (element.length) {
            window.pdfBuilderDebug.info('Element inspection: ' + selector, {
                exists: true,
                count: element.length,
                dimensions: {
                    width: element.outerWidth(),
                    height: element.outerHeight()
                },
                position: element.offset(),
                classes: element.attr('class'),
                attributes: element.get(0).attributes
            });
        } else {
            window.pdfBuilderDebug.warn('Element not found: ' + selector);
        }
    };

    // Memory usage monitoring
    DeveloperTools.prototype.getMemoryUsage = function() {
        if (!this.debugMode || !window.performance || !performance.memory) return null;

        return {
            used: performance.memory.usedJSHeapSize,
            total: performance.memory.totalJSHeapSize,
            limit: performance.memory.jsHeapSizeLimit,
            usedMB: Math.round(performance.memory.usedJSHeapSize / 1024 / 1024),
            totalMB: Math.round(performance.memory.totalJSHeapSize / 1024 / 1024),
            limitMB: Math.round(performance.memory.jsHeapSizeLimit / 1024 / 1024)
        };
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        window.pdfBuilderDeveloperTools = new DeveloperTools();
    });

    // Expose to global scope
    window.DeveloperTools = DeveloperTools;

})(window, jQuery);