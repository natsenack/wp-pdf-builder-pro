/**
 * PDF Builder Settings Tabs Navigation
 * Handles tab switching and content display
 * Updated: 2025-12-03 01:35:00
 */

/**
 * This file was a duplicate of `assets/js/settings-tabs.js`.
 * To avoid duplication, it will now only act as a shim: if the canonical
 * `PDFBuilderTabsAPI` is not present, it logs a warning, otherwise it defers to it.
 */

(function() {
    'use strict';

    // Shim: do nothing if canonical manager present
    if (window && window.PDFBuilderTabsAPI && typeof window.PDFBuilderTabsAPI.switchToTab === 'function') {
        // Nothing to do, main script will handle tabs
        console.log('PDF Builder: settings-tabs.js (template) shim loaded — canonical manager detected. No action.');
        return;
    }

    // Otherwise, we will attempt to wait for the canonical manager before binding fallback
    console.warn('PDF Builder: settings-tabs.js (template) loaded but PDFBuilderTabsAPI is NOT present yet. Waiting before minimal fallback binding.');

    (function waitForCanonicalThenBindFallback() {
        var attempts = 0;
        var maxAttempts = 20; // 5s at 250ms intervals
        var buttons = null;
        var contents = null;

        function tryBind() {
            attempts++;
            if (window.PDFBuilderTabsAPI && typeof window.PDFBuilderTabsAPI.switchToTab === 'function') {
                console.log('PDF Builder: Canonical manager present; shim will not bind fallback');
                return;
            }

            // If we reached max attempts, attach the fallback once
            if (attempts >= maxAttempts) {
                if (!window.PDFBuilderShimFallbackBound) {
                    console.log('PDF Builder: Binding minimal fallback (shim) after timeout');
                    buttons = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
                    contents = document.querySelectorAll('#pdf-builder-tab-content .tab-content');
                    function switchTabFallback(tabId) {
                        Array.prototype.forEach.call(buttons, function(b) { b.classList.remove('nav-tab-active'); });
                        Array.prototype.forEach.call(contents, function(c) { c.classList.remove('active'); });
                        var btn = document.querySelector('#pdf-builder-tabs [data-tab="' + tabId + '"]');
                        var content = document.getElementById(tabId);
                        if (btn) { btn.classList.add('nav-tab-active'); }
                        if (content) { content.classList.add('active'); }
                    }
                    Array.prototype.forEach.call(buttons, function(btn) {
                        // Cleanup any prior shim handler on the button
                        try { if (btn._pdfBuilderShimFallbackHandler && typeof btn._pdfBuilderShimFallbackHandler === 'function') { btn.removeEventListener('click', btn._pdfBuilderShimFallbackHandler); } } catch (e) {}
                        // Attach and keep a reference to handler for canonical cleanup
                        btn._pdfBuilderShimFallbackHandler = function(e) {
                            e.preventDefault();
                            var id = this.getAttribute('data-tab');
                            switchTabFallback(id);
                        };
                        btn.addEventListener('click', btn._pdfBuilderShimFallbackHandler);
                    });
                    window.PDFBuilderShimFallbackBound = true;
                }
                return;
            }

            // retry
            setTimeout(tryBind, 250);
        }

        // Start checks
        tryBind();
        document.addEventListener('DOMContentLoaded', function(){ setTimeout(tryBind, 100); });
        window.addEventListener('load', function(){ setTimeout(tryBind, 100); });
    })();
})();