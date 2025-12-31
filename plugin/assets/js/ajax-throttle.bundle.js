/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 283:
/***/ (function(module, exports) {

/**
 * AJAX Throttle & Connection Pool Manager
 * Prevents "Too many connections" errors by limiting concurrent requests
 */

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
})();


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module doesn't tell about it's top-level declarations so it can't be inlined
/******/ 	var __webpack_exports__ = __webpack_require__(283);
/******/ 	
/******/ })()
;
//# sourceMappingURL=ajax-throttle.bundle.js.map