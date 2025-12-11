(function() {
    'use strict';

    // Root monitor for debugging tab navigation conflicts
    // Enables: logging of add/remove event listeners, stopPropagation calls, and click events

    if (typeof window === 'undefined') return;

    // Config flag - enable via window.PDF_BUILDER_CONFIG.debug = true
    const DEBUG = !!(window.PDF_BUILDER_CONFIG && window.PDF_BUILDER_CONFIG.debug);

    window.pdfBuilderRootLog = window.pdfBuilderRootLog || [];
    function logRoot(message, data) {
        try {
            const ts = new Date().toISOString();
            const entry = { ts: ts, msg: message, data: data };
            window.pdfBuilderRootLog.push(entry);
        } catch (e) {}
    }

    // Helper to stringify element for logs
    function elementSummary(el) {
        if (!el) return null;
        try {
            return {
                tag: el.tagName,
                id: el.id || null,
                classes: el.className || null,
                dataTab: el.dataset ? el.dataset.tab : null,
                outer: el.outerHTML ? el.outerHTML.slice(0, 200) + (el.outerHTML.length > 200 ? '...' : '') : null
            };
        } catch (e) {
            return { tag: el.tagName };
        }
    }

    // Patch addEventListener/removeEventListener
    (function() {
        if (!EventTarget.prototype.__pb_addEventListenerPatched) {
            const origAdd = EventTarget.prototype.addEventListener;
            EventTarget.prototype.addEventListener = function(type, listener, options) {
                try {
                    const el = this;
                    if (type === 'click' || type === 'keydown' || type === 'keyup' || type === 'hashchange' || type === 'change') {
                        // Collect a stack trace
                        const stack = (new Error()).stack.split('\n').slice(1,6).map(s => s.trim());
                        logRoot('addEventListener: ' + type, {
                            target: elementSummary(el),
                            options: options,
                            stack: stack
                        });
                    }
                } catch(e){}
                return origAdd.apply(this, arguments);
            };
            const origRemove = EventTarget.prototype.removeEventListener;
            EventTarget.prototype.removeEventListener = function(type, listener, options) {
                try {
                    const el = this;
                    if (type === 'click' || type === 'keydown' || type === 'keyup') {
                        logRoot('removeEventListener: ' + type, {
                            target: elementSummary(el),
                            options: options
                        });
                    }
                } catch(e){}
                return origRemove.apply(this, arguments);
            };
            EventTarget.prototype.__pb_addEventListenerPatched = true;
        }
    })();

    // Patch stopImmediatePropagation/stopPropagation to log when used
    (function() {
        if (!Event.prototype.__pb_stopPatched) {
            const origStopImmediate = Event.prototype.stopImmediatePropagation;
            Event.prototype.stopImmediatePropagation = function() {
                try {
                    logRoot('stopImmediatePropagation called', {
                        type: this.type,
                        target: elementSummary(this.target),
                        currentTarget: elementSummary(this.currentTarget),
                        stack: (new Error()).stack.split('\n').slice(1,6).map(s=>s.trim())
                    });
                } catch(e){}
                return origStopImmediate.apply(this, arguments);
            };

            const origStop = Event.prototype.stopPropagation;
            Event.prototype.stopPropagation = function() {
                try {
                    logRoot('stopPropagation called', {
                        type: this.type,
                        target: elementSummary(this.target),
                        currentTarget: elementSummary(this.currentTarget),
                        stack: (new Error()).stack.split('\n').slice(1,6).map(s=>s.trim())
                    });
                } catch(e){}
                return origStop.apply(this, arguments);
            };

            Event.prototype.__pb_stopPatched = true;
        }
    })();

    // Global click/keyboard capturing observer to track events and phases
    (function() {
        function globalClickHandler(e) {
            try {
                logRoot('CLICK event captured', {
                    type: e.type,
                    target: elementSummary(e.target),
                    currentTarget: elementSummary(e.currentTarget),
                    eventPhase: e.eventPhase
                });
            } catch(e){}
        }
        function globalKeyHandler(e) {
            try {
                logRoot('KEY event captured', { type: e.type, key: e.key, target: elementSummary(e.target) });
            } catch(e){}
        }

        document.addEventListener('click', globalClickHandler, true);
        document.addEventListener('keydown', globalKeyHandler, true);

        // Also a capture on body to detect when nav tabs are clicked/jumped
        document.addEventListener('click', function(e) {
            try {
                const anchor = e.target.closest && e.target.closest('.nav-tab');
                if (anchor) {
                    logRoot('Nav tab clicked (capture): ' + (anchor.getAttribute('data-tab') || anchor.id || 'unknown'), elementSummary(anchor));
                }
            } catch(e){}
        }, true);
    })();

    // Expose a helper function to dump collected log (and optionally print to server in future)
    window.pdfBuilderRootLogDump = function() {
        try {
            console.group('PDF Builder Root Log Dump - ' + window.pdfBuilderRootLog.length + ' entries');
            window.pdfBuilderRootLog.forEach(function(entry) {
                console.info(entry.ts, entry.msg, entry.data || '');
            });
            console.groupEnd();
        } catch(e) {
            // Error logging disabled
        }
        return window.pdfBuilderRootLog;
    };

    logRoot('tabs-root-monitor initialized (DEBUG=' + DEBUG + ')');
})();
