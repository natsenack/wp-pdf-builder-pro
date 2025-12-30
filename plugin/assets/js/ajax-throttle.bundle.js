(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["PDFBuilder"] = factory();
	else
		root["PDFBuilder"] = factory();
})(self, () => {
return /******/ (() => { // webpackBootstrap
/******/ 	"use strict";
var __webpack_exports__ = {};


/**
 * AJAX Throttle & Connection Pool Manager
 * Prevents "Too many connections" errors by limiting concurrent requests
 */

(function () {
  'use strict';

  // Track pending requests
  var pendingRequests = 0;
  var MAX_CONCURRENT_REQUESTS = 3; // Limit concurrent connections
  var requestQueue = [];

  // Override native fetch to throttle requests
  var originalFetch = window.fetch;
  window.fetch = function () {
    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }
    return new Promise(function (resolve, reject) {
      var executeRequest = function executeRequest() {
        pendingRequests++;
        originalFetch.apply(window, args).then(function (response) {
          resolve(response);
        })["catch"](function (error) {
          reject(error);
        })["finally"](function () {
          pendingRequests--;

          // Process next queued request
          if (requestQueue.length > 0) {
            var nextRequest = requestQueue.shift();
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
  window.getAjaxStats = function () {
    return {
      pendingRequests: pendingRequests,
      queuedRequests: requestQueue.length,
      totalCapacity: MAX_CONCURRENT_REQUESTS
    };
  };
})();
__webpack_exports__ = __webpack_exports__["default"];
/******/ 	return __webpack_exports__;
/******/ })()
;
});
//# sourceMappingURL=ajax-throttle.bundle.js.map