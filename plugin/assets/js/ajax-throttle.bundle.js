"use strict";
var pdfBuilderReact;
(self["webpackChunkpdfBuilderReact"] = self["webpackChunkpdfBuilderReact"] || []).push([["ajax-throttle"],{

/***/ "./assets/js/ajax-throttle.js":
/*!************************************!*\
  !*** ./assets/js/ajax-throttle.js ***!
  \************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
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

/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ var __webpack_exports__ = (__webpack_exec__("./assets/js/ajax-throttle.js"));
/******/ pdfBuilderReact = __webpack_exports__;
/******/ }
]);
//# sourceMappingURL=ajax-throttle.bundle.js.map