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
// Pre-init script ensures window.pdfBuilderReact and debug array exist first
// ============================================================================

// IIFE to execute module-level code immediately
(function () {
  if (typeof window !== 'undefined') {
    window.pdfBuilderReactDebug = window.pdfBuilderReactDebug || [];
    window.pdfBuilderReactDebug.push('MODULE_LEVEL_EXECUTION');
  }
})();

// Define the initialization function
function initPDFBuilderReact() {
  window.pdfBuilderReactDebug.push('FUNCTION_CALLED_STARTED');
  window.pdfBuilderReactDebug.push('IMMEDIATE_RETURN_TEST');
  return false;
  // removed by dead control flow
 var element, exp, mod, key, PDFBuilder, root, editorEl, loadingEl, moduleCount, container, ReactDOM, React; 
}

// Force immediate assignment at module level
// This runs when webpack loads the module, before anything else
if (typeof window !== 'undefined') {
  window.pdfBuilderReactDebug = window.pdfBuilderReactDebug || [];
  window.pdfBuilderReactDebug.push('MODULE_LEVEL_EXECUTION');
}
window.pdfBuilderReact = window.pdfBuilderReact || {};
window.pdfBuilderReact.initPDFBuilderReact = initPDFBuilderReact;
if (typeof window !== 'undefined') {
  window.pdfBuilderReactDebug.push('FUNCTION_ASSIGNED');
}

// Export as default so webpack exports it to window.pdfBuilderReact
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  initPDFBuilderReact: initPDFBuilderReact,
  __esModule: true
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