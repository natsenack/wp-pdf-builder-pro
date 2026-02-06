/**
 * PDF Builder React Module Executor
 * Ensures the React bundle has properly initialized window.pdfBuilderReact
 */

(function() {
    'use strict';

    console.log('[MODULE EXECUTOR] Starting PDF Builder React module execution check...');

    // Check if the React bundle has already set window.pdfBuilderReact
    function checkReactAPI() {
        if (window.pdfBuilderReact && typeof window.pdfBuilderReact.initPDFBuilderReact === 'function') {
            console.log('[MODULE EXECUTOR] ✓ window.pdfBuilderReact is available and has initPDFBuilderReact function');
            console.log('[MODULE EXECUTOR] API object:', window.pdfBuilderReact);
            return true;
        } else {
            console.log('[MODULE EXECUTOR] ✗ window.pdfBuilderReact not available or missing initPDFBuilderReact function');
            console.log('[MODULE EXECUTOR] Current window.pdfBuilderReact:', window.pdfBuilderReact);
            return false;
        }
    }

    // Check immediately
    if (!checkReactAPI()) {
        // If not available immediately, wait a bit and check again
        setTimeout(function() {
            if (!checkReactAPI()) {
                console.warn('[MODULE EXECUTOR] React API still not available after delay, creating emergency fallback');

                // Emergency fallback - create a basic API that shows an error
                window.pdfBuilderReact = {
                    initPDFBuilderReact: function(containerId) {
                        console.error('[MODULE EXECUTOR] Emergency fallback: React bundle failed to initialize properly');
                        if (containerId) {
                            const container = document.getElementById(containerId);
                            if (container) {
                                container.innerHTML = `
                                    <div style="color: red; padding: 20px; border: 2px solid red; border-radius: 5px; background: #ffe6e6;">
                                        <h3>PDF Builder React Error</h3>
                                        <p>The React application failed to load. Please check the browser console for errors and try refreshing the page.</p>
                                        <p>If the problem persists, contact support.</p>
                                        <button onclick="window.location.reload()" style="padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer;">Refresh Page</button>
                                    </div>
                                `;
                            }
                        }
                        return false;
                    },
                    _isEmergencyFallback: true,
                    _error: 'React bundle initialization failed'
                };

                console.log('[MODULE EXECUTOR] Emergency fallback API created');
            }
        }, 500);
    }

    console.log('[MODULE EXECUTOR] Module execution check completed');
})();