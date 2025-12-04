/**
 * AJAX Throttle & Connection Pool Manager
 * Prevents "Too many connections" errors by limiting concurrent requests
 */

// Debug functions
function isDebugEnabled() {
    if (typeof window !== 'undefined' &&
        window.pdfBuilderDebugSettings &&
        typeof window.pdfBuilderDebugSettings === 'object') {
        return !!window.pdfBuilderDebugSettings.javascript;
    }
    return false;
}

function debugLog(...args) {
    if (isDebugEnabled()) {
        console.log(...args);
    }
}

(function() {
    'use strict';

    // Track pending requests
    let pendingRequests = 0;
    const MAX_CONCURRENT_REQUESTS = 3; // Limit concurrent connections
    const requestQueue = [];

    // Override native fetch to throttle requests
    const originalFetch = window.fetch;
    
    window.fetch = function(...args) {
        return new Promise((resolve, reject) => {
            const executeRequest = () => {
                pendingRequests++;
                
                originalFetch.apply(window, args)
                    .then(response => {
                        resolve(response);
                    })
                    .catch(error => {
                        reject(error);
                    })
                    .finally(() => {
                        pendingRequests--;
                        
                        // Process next queued request
                        if (requestQueue.length > 0) {
                            const nextRequest = requestQueue.shift();
                            nextRequest();
                        }
                    });
            };

            if (pendingRequests < MAX_CONCURRENT_REQUESTS) {
                executeRequest();
            } else {
                // Queue the request
                requestQueue.push(executeRequest);
            }
        });
    };

    // Add connection pool monitoring
    window.getAjaxStats = function() {
        return {
            pendingRequests,
            queuedRequests: requestQueue.length,
            totalCapacity: MAX_CONCURRENT_REQUESTS
        };
    };

    debugLog('✅ [AJAX Throttle] Connection pool manager initialized (max ' + MAX_CONCURRENT_REQUESTS + ' concurrent)');
})();
