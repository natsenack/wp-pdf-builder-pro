(function() {
    'use strict';

    if (typeof window === 'undefined') return;

    const DEBUG = !!(window.PDF_BUILDER_CONFIG && window.PDF_BUILDER_CONFIG.debug);

    window.pdfBuilderRootLog = window.pdfBuilderRootLog || [];
    function logRoot(message, data) {
        try {
            const ts = new Date().toISOString();
            const entry = { ts: ts, msg: message, data: data };
            window.pdfBuilderRootLog.push(entry);
            if (DEBUG) {
                
            }
        } catch (e) {}
    }

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

    (function() {
        if (!EventTarget.prototype.__pb_addEventListenerPatched) {
            const origAdd = EventTarget.prototype.addEventListener;
            EventTarget.prototype.addEventListener = function(type, listener, options) {
                try {
                    const el = this;
                    if (type === 'click' || type === 'keydown' || type === 'keyup' || type === 'hashchange' || type === 'change') {
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
    
    // Auto reinitialize tab manager on mutations to the nav/container
    (function(){
        try {
            const navRoot = document.getElementById('pdf-builder-tabs');
            const contentRoot = document.getElementById('pdf-builder-tab-content');
            const root = navRoot || contentRoot || document.body;

            const reinit = () => {
                if (window.PDFBuilderTabsAPI && typeof window.PDFBuilderTabsAPI.initialize === 'function') {
                    logRoot('Reinit: calling window.PDFBuilderTabsAPI.initialize()');
                    try { window.PDFBuilderTabsAPI.initialize(); } catch(e) { logRoot('Reinit error', e); }
                } else if (window.PDF_BUILDER_TABS && typeof window.PDF_BUILDER_TABS.init === 'function') {
                    logRoot('Reinit: calling window.PDF_BUILDER_TABS.init()');
                    try { window.PDF_BUILDER_TABS.init(); } catch(e) { logRoot('Reinit error', e); }
                }
            };

            const mo = new MutationObserver(function(mutations) {
                for (const m of mutations) {
                    if (m.type === 'childList' || (m.type === 'attributes' && (m.attributeName === 'class' || m.attributeName === 'data-tab'))) {
                        logRoot('Mutation detected: re-initializing');
                        setTimeout(reinit, 50);
                        break;
                    }
                }
            });
            mo.observe(root, { childList: true, subtree: true, attributes: true });
            logRoot('tabs-root-monitor: MutationObserver registered on root');
        } catch(e) { logRoot('tabs-root-monitor: Failed to register reinit observer', e); }
    })();

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
                // If this is a click event inside PDF Builder tabs, block the external stopImmediatePropagation
                try {
                    if (this.type === 'click' && this.target && this.target.closest && this.target.closest('#pdf-builder-tabs')) {
                        logRoot('BLOCKED stopImmediatePropagation for click inside #pdf-builder-tabs', { target: elementSummary(this.target) });
                        return; // swallow it
                    }
                } catch(e) {}
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

    // Optional aggressive patch to stopPropagation as well for clicks inside nav
    (function(){
        if (!Event.prototype.__pb_stopPatched2) {
            const origStop = Event.prototype.stopPropagation;
            Event.prototype.stopPropagation = function() {
                try {
                    if (this.type === 'click' && this.target && this.target.closest && this.target.closest('#pdf-builder-tabs')) {
                        logRoot('BLOCKED stopPropagation for click inside #pdf-builder-tabs', { target: elementSummary(this.target) });
                        return; // swallow it
                    }
                } catch(e) {}
                return origStop.apply(this, arguments);
            };
            Event.prototype.__pb_stopPatched2 = true;
        }
    })();

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

        document.addEventListener('click', function(e) {
            try {
                const anchor = e.target.closest && e.target.closest('.nav-tab');
                if (anchor) {
                    logRoot('Nav tab clicked (capture): ' + (anchor.getAttribute('data-tab') || anchor.id || 'unknown'), elementSummary(anchor));
                }
            } catch(e){}
        }, true);
    })();

    window.pdfBuilderRootLogDump = function() {
        try {
            // console.group('PDF Builder Root Log Dump - ' + window.pdfBuilderRootLog.length + ' entries');
            window.pdfBuilderRootLog.forEach(function(entry) {
                // console.info(entry.ts, entry.msg, entry.data || '');
            });
            // console.groupEnd();
        } catch(e) {
            
        }
        return window.pdfBuilderRootLog;
    };

    logRoot('tabs-root-monitor initialized (DEBUG=' + DEBUG + ')');
})();

