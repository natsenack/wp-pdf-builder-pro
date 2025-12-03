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

    // Otherwise, fallback minimal manager
    console.warn('PDF Builder: settings-tabs.js (template) loaded but PDFBuilderTabsAPI is NOT present. Minimal fallback engaged.');
    const buttons = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
    const contents = document.querySelectorAll('#pdf-builder-tab-content .tab-content');
    function switchTabFallback(tabId) {
        buttons.forEach(b => b.classList.remove('nav-tab-active'));
        contents.forEach(c => c.classList.remove('active'));
        const btn = document.querySelector('#pdf-builder-tabs [data-tab="' + tabId + '"]');
        const content = document.getElementById(tabId);
        if (btn) { btn.classList.add('nav-tab-active'); }
        if (content) { content.classList.add('active'); }
    }
    buttons.forEach(btn => btn.addEventListener('click', (e) => {
        e.preventDefault();
        const id = btn.getAttribute('data-tab');
        switchTabFallback(id);
    }));
})();