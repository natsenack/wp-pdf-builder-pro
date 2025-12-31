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
// PDF Builder React Bundle - STANDALONE IIFE APPROACH (NO WEBPACK UMD WRAPPING)
// ============================================================================

// Immediately invoke function to escape webpack UMD wrapping
(function () {
  'use strict';

  if (typeof window === 'undefined') return;
  console.log('🔥 [PDF BUNDLE] IIFE STARTED - window context available');

  // Define the initialization function IMMEDIATELY (not in module scope)
  function initPDFBuilderReact() {
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

      // Import PDFBuilder dynamically or inline
      // For now, we'll check if it's available in the module cache
      var PDFBuilder = null;

      // Try to get PDFBuilder from webpack modules if available
      if (true) {
        for (var key in __webpack_require__.m) {
          var mod = __webpack_require__.m[key];
          if (mod && mod.exports && mod.exports["default"]) {
            var exp = mod.exports["default"];
            // Check if this looks like PDFBuilder (has render method or is a React component)
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
  }

  // Assign to window IMMEDIATELY within IIFE scope
  window.pdfBuilderReact = {
    initPDFBuilderReact: initPDFBuilderReact
  };
  console.log('🔥 [PDF BUNDLE] IIFE: Assigned to window.pdfBuilderReact');
  console.log('🔥 [PDF BUNDLE] IIFE: window.pdfBuilderReact type:', _typeof(window.pdfBuilderReact));
  console.log('🔥 [PDF BUNDLE] IIFE: initPDFBuilderReact type:', _typeof(window.pdfBuilderReact.initPDFBuilderReact));
})();

// For webpack: this is needed but will be ignored in favor of the IIFE
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  initPDFBuilderReact: function initPDFBuilderReact() {
    return window.pdfBuilderReact ? window.pdfBuilderReact.initPDFBuilderReact() : false;
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
(function() {
  if (typeof window === 'undefined') return;
  
  // The UMD wrapper creates a factory that returns something
  // We need to capture it and ensure it's an object with initPDFBuilderReact
  console.log('🔥 [WEBPACK UMD] Fixing UMD export assignment...');
  console.log('🔥 [WEBPACK UMD] Current pdfBuilderReact type:', typeof window.pdfBuilderReact);
  console.log('🔥 [WEBPACK UMD] Current pdfBuilderReact value:', window.pdfBuilderReact);
  
  // The webpack module system stores exports in a different way
  // Try to access the actual module exports
  if (window.pdfBuilderReact && typeof window.pdfBuilderReact === 'object' && window.pdfBuilderReact.default) {
    console.log('🔥 [WEBPACK UMD] Found .default property, using it');
    window.pdfBuilderReact = window.pdfBuilderReact.default;
  }
  
  // If it's still not an object with initPDFBuilderReact, log the full object
  if (!window.pdfBuilderReact || typeof window.pdfBuilderReact !== 'object' || !window.pdfBuilderReact.initPDFBuilderReact) {
    console.error('🔥 [WEBPACK UMD] ERROR: pdfBuilderReact is not an object with initPDFBuilderReact!');
    console.error('🔥 [WEBPACK UMD] Full object:', window.pdfBuilderReact);
    console.error('🔥 [WEBPACK UMD] Type:', typeof window.pdfBuilderReact);
    
    // Try to find it in the module cache if available
    if (window.__webpack_modules__) {
      console.log('🔥 [WEBPACK UMD] Webpack modules available, investigating...');
      Object.keys(window.__webpack_modules__).forEach(function(key) {
        var mod = window.__webpack_modules__[key];
        if (mod && mod.exports && mod.exports.initPDFBuilderReact) {
          console.log('🔥 [WEBPACK UMD] Found initPDFBuilderReact in module', key);
          window.pdfBuilderReact = mod.exports;
        }
      });
    }
    return;
  }
  
  console.log('🔥 [WEBPACK UMD] Fixed pdfBuilderReact type:', typeof window.pdfBuilderReact);
  console.log('🔥 [WEBPACK UMD] initPDFBuilderReact available:', typeof window.pdfBuilderReact.initPDFBuilderReact);
  
  // Now call it
  if (typeof window.pdfBuilderReact.initPDFBuilderReact === 'function') {
    console.log('🔥 [WEBPACK UMD] Calling initPDFBuilderReact...');
    try {
      var result = window.pdfBuilderReact.initPDFBuilderReact();
      console.log('🔥 [WEBPACK UMD] initPDFBuilderReact result:', result);
    } catch (err) {
      console.error('🔥 [WEBPACK UMD] initPDFBuilderReact error:', err.message);
      console.error('🔥 [WEBPACK UMD] Stack:', err.stack);
    }
  }
})();
