"use strict";
(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["pdfBuilderReact"] = factory();
	else
		root["pdfBuilderReact"] = factory();
})(typeof self !== "undefined" ? self : this, () => {
return (Object(typeof self !== "undefined" ? self : this)["webpackChunkpdfBuilderReact"] = Object(typeof self !== "undefined" ? self : this)["webpackChunkpdfBuilderReact"] || []).push([["pdf-builder-react"],{

/***/ "./assets/js/pdf-builder-react/index.js":
/*!**********************************************!*\
  !*** ./assets/js/pdf-builder-react/index.js ***!
  \**********************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
// ============================================================================
// PDF Builder React - BOOTSTRAP AUTO-EXECUTION
// ============================================================================
// This module runs at the module level (in browser), BEFORE the UMD wrapper
// executes. This ensures window.pdfBuilderReact is assigned before anything
// tries to use it.

console.log('🔥 [PDF BUNDLE] Module level - START LOADING');
if (typeof window !== 'undefined') {
  // Define the initialization function at MODULE SCOPE (will be seen by UMD)
  var initPDFBuilderReact = function initPDFBuilderReact() {
    console.log('🔧 [PDF BUNDLE] initPDFBuilderReact CALLED');
    try {
      // Get globals
      var React = window.React;
      var ReactDOM = window.ReactDOM;
      console.log('🔧 [PDF BUNDLE] React type:', _typeof(React));
      console.log('🔧 [PDF BUNDLE] ReactDOM type:', _typeof(ReactDOM));

      // Check for container
      var container = document.getElementById('pdf-builder-react-root');
      console.log('🔧 [PDF BUNDLE] Container element:', container ? 'FOUND' : 'NOT FOUND');
      if (!container) {
        console.error('❌ [PDF BUNDLE] ERROR: Container not found');
        return false;
      }

      // Validate React
      if (typeof React === 'undefined' || !React) {
        console.error('❌ [PDF BUNDLE] ERROR: React undefined or null');
        return false;
      }
      if (typeof ReactDOM === 'undefined' || !ReactDOM) {
        console.error('❌ [PDF BUNDLE] ERROR: ReactDOM undefined or null');
        return false;
      }
      if (typeof ReactDOM.createRoot !== 'function') {
        console.error('❌ [PDF BUNDLE] ERROR: ReactDOM.createRoot not a function');
        return false;
      }
      console.log('✅ [PDF BUNDLE] React dependencies validated');

      // Hide loading, show editor
      var loadingEl = document.getElementById('pdf-builder-react-loading');
      var editorEl = document.getElementById('pdf-builder-react-editor');
      if (loadingEl) loadingEl.style.display = 'none';
      if (editorEl) editorEl.style.display = 'block';
      console.log('🎨 [PDF BUNDLE] Creating React root...');
      var root = ReactDOM.createRoot(container);

      // Try to get PDFBuilder from webpack modules if available
      var PDFBuilder = null;
      if (true) {
        for (var key in __webpack_require__.m) {
          var mod = __webpack_require__.m[key];
          if (mod && mod.exports && mod.exports["default"]) {
            var exp = mod.exports["default"];
            if (typeof exp === 'function' && (exp.$$typeof || exp.prototype)) {
              PDFBuilder = exp;
              console.log('🎨 [PDF BUNDLE] Found PDFBuilder in module cache');
              break;
            }
          }
        }
      }
      if (!PDFBuilder) {
        console.error('❌ [PDF BUNDLE] ERROR: PDFBuilder component not found');
        return false;
      }
      console.log('🎨 [PDF BUNDLE] Creating element from PDFBuilder component...');
      var element = React.createElement(PDFBuilder);
      console.log('🎨 [PDF BUNDLE] Rendering to root...');
      root.render(element);
      console.log('✅ [PDF BUNDLE] Rendered successfully!');
      return true;
    } catch (error) {
      console.error('❌ [PDF BUNDLE] EXCEPTION:', error.message);
      console.error('❌ [PDF BUNDLE] Stack:', error.stack);
      return false;
    }
  }; // Assign to window at MODULE SCOPE (browser environment)
  // This happens BEFORE UMD wrapper returns
  // We are in browser - execute immediately at module scope

  console.log('🔥 [PDF BUNDLE] Browser environment detected');
  window.pdfBuilderReact = {
    initPDFBuilderReact: initPDFBuilderReact
  };
  console.log('🔥 [PDF BUNDLE] Assigned window.pdfBuilderReact at module scope');
  console.log('🔥 [PDF BUNDLE] Type:', _typeof(window.pdfBuilderReact));
  console.log('🔥 [PDF BUNDLE] initPDFBuilderReact type:', _typeof(window.pdfBuilderReact.initPDFBuilderReact));
}

// Export for UMD wrapper and CommonJS
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  initPDFBuilderReact: function initPDFBuilderReact() {
    if (typeof window !== 'undefined' && window.pdfBuilderReact && typeof window.pdfBuilderReact.initPDFBuilderReact === 'function') {
      return window.pdfBuilderReact.initPDFBuilderReact();
    }
    return false;
  }
});

/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ var __webpack_exports__ = (__webpack_exec__("./assets/js/pdf-builder-react/index.js"));
/******/ __webpack_exports__ = __webpack_exports__["default"];
/******/ return __webpack_exports__;
/******/ }
]);
});
//# sourceMappingURL=pdf-builder-react.bundle.js.map