// ============================================================================
// PDF Builder Performance Patch - Load FIRST
// This script patches EventTarget before any other scripts load
// ============================================================================

(function() {
    'use strict';

    // Only run in browser environment
    if (typeof window === 'undefined' || typeof EventTarget === 'undefined') {
        return;
    }

    console.log('[PDF Builder] 🚀 Performance patch loaded - applying passive event listeners');

    // Store original methods
    const originalAddEventListener = EventTarget.prototype.addEventListener;
    const originalRemoveEventListener = EventTarget.prototype.removeEventListener;

    // Enhanced addEventListener with passive defaults
    EventTarget.prototype.addEventListener = function(type, listener, options) {
        // Normalize options
        if (typeof options === 'boolean') {
            options = { capture: options };
        } else if (!options) {
            options = {};
        }

        // Force passive: true by default unless explicitly set to false
        if (!options.hasOwnProperty('passive')) {
            options.passive = true;
        }

        try {
            return originalAddEventListener.call(this, type, listener, options);
        } catch (error) {
            // Fallback if passive is not supported
            console.warn('[PDF Builder] Passive listeners not supported, falling back to active:', error);
            options.passive = false;
            return originalAddEventListener.call(this, type, listener, options);
        }
    };

    // Enhanced removeEventListener with matching logic
    EventTarget.prototype.removeEventListener = function(type, listener, options) {
        // Normalize options for consistency
        if (typeof options === 'boolean') {
            options = { capture: options };
        } else if (!options) {
            options = {};
        }

        // Apply same passive logic
        if (!options.hasOwnProperty('passive')) {
            options.passive = true;
        }

        try {
            return originalRemoveEventListener.call(this, type, listener, options);
        } catch (error) {
            // Fallback
            options.passive = false;
            return originalRemoveEventListener.call(this, type, listener, options);
        }
    };

    console.log('[PDF Builder] ✅ Passive event listeners patch applied successfully');
})();
