/**
 * PDF Builder React Module Executor
 * Ensures the React bundle has properly initialized window.pdfBuilderReact
 */

(function() {
    'use strict';

    // Check if the React bundle has already set window.pdfBuilderReact
    function checkReactAPI() {
        if (window.pdfBuilderReact && typeof window.pdfBuilderReact.initPDFBuilderReact === 'function') {
            return true;
        } else {
            return false;
        }
    }

    // Check immediately
    if (!checkReactAPI()) {
        // If not available immediately, wait a bit and check again
        setTimeout(function() {
            if (!checkReactAPI()) {

                // Emergency fallback - create a basic API that shows an error
                window.pdfBuilderReact = {
                    initPDFBuilderReact: function(containerId) {
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
            }
        }, 500);
    }
})();