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
// ============================================================================
// PDF Builder React - MAIN BUNDLE
// Exports initPDFBuilderReact function to window.pdfBuilderReact
// Pre-init script ensures window.pdfBuilderReact exists before this runs
// ============================================================================

// FORCE EXECUTION WITH IIFE TO ESCAPE WEBPACK WRAPPER
(function () {
  window.pdfBuilderReactDebug = window.pdfBuilderReactDebug || [];
  window.pdfBuilderReactDebug.push('BUNDLE_LOADED_START');
})();

// Define the initialization function
function initPDFBuilderReact() {
  // FORCE WINDOW ACCESS WITH IIFE
  (function () {
    window.pdfBuilderReactDebug = window.pdfBuilderReactDebug || [];
    window.pdfBuilderReactDebug.push('FUNCTION_CALLED_STARTED');
  })();
  try {
    (function () {
      window.pdfBuilderReactDebug.push('FUNCTION_IN_TRY_BLOCK');
      // Get globals
      var React = window.React;
      var ReactDOM = window.ReactDOM;
      window.pdfBuilderReactDebug.push('FUNCTION_AFTER_GLOBALS');
      // Just return true to test if function works at all
      window.pdfBuilderReactDebug.push('FUNCTION_RETURNING_TRUE');
    })();
    return true;
    // removed by dead control flow

    // removed by dead control flow


    // Check for container
    // removed by dead control flow
 var container; 
    // removed by dead control flow

    // removed by dead control flow


    // Validate React
    // removed by dead control flow

    // removed by dead control flow

    // removed by dead control flow

    // removed by dead control flow


    // Check webpack modules count
    // removed by dead control flow
 var moduleCount; 
    // removed by dead control flow


    // Get UI elements safely
    // removed by dead control flow
 var loadingEl; 
    // removed by dead control flow
 var editorEl; 
    // removed by dead control flow

    // removed by dead control flow

    // removed by dead control flow

    // removed by dead control flow
 var root; 

    // Try to get PDFBuilder from webpack modules if available
    // removed by dead control flow
 var PDFBuilder; 
    // removed by dead control flow
 var exp, mod, key; 
    // removed by dead control flow

    // removed by dead control flow

    // removed by dead control flow
 var element; 
    // removed by dead control flow

    // removed by dead control flow

    // removed by dead control flow

    // removed by dead control flow

  } catch (error) {
    (function () {
      window.pdfBuilderReactDebug.push('ERROR: ' + error.message);
    })();
    console.error('❌ [PDF BUNDLE] EXCEPTION:', error.message);
    console.error('❌ [PDF BUNDLE] Stack:', error.stack);
    return false;
  }
}

// Force immediate assignment at module level
// This runs when webpack loads the module, before anything else
window.pdfBuilderReactDebug = window.pdfBuilderReactDebug || [];
window.pdfBuilderReactDebug.push('MODULE_LEVEL_EXECUTION');
window.pdfBuilderReact = window.pdfBuilderReact || {};
window.pdfBuilderReact.initPDFBuilderReact = initPDFBuilderReact;
window.pdfBuilderReactDebug.push('FUNCTION_ASSIGNED');
console.log('✅ [PDF BUNDLE] Debug log:', window.pdfBuilderReactDebug);

// Export for module systems
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (initPDFBuilderReact);

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