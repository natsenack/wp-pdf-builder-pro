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
})(self, () => {
return (self["webpackChunkpdfBuilderReact"] = self["webpackChunkpdfBuilderReact"] || []).push([[763],{

/***/ 326:
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);


var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");
var _typeof2 = _interopRequireDefault(require("@babel/runtime/helpers/typeof"));
require("../fallbacks/browser-compatibility.js");
var _react = _interopRequireDefault(require("react"));
var _client = _interopRequireDefault(require("react-dom/client"));
var _canvas = require("./constants/canvas.ts");
var _debug = require("./utils/debug");
var _globalApi = require("./api/global-api");
// ============================================================================
// PDF Builder React Bundle - Entry Point
// ============================================================================

console.log('🚀 [PDF Builder] React bundle loading...');

// Note: Performance patch is loaded separately as 'pdf-builder-react-performance-patch' entry point

// Import du diagnostic de compatibilité

// Import des composants React

// État de l'application
// let currentTemplate = null;
// let isModified = false;

// Flag pour afficher les logs d'initialisation détaillés
var DEBUG_VERBOSE = true;
if (DEBUG_VERBOSE) (0, _debug.debugLog)('🚀 PDF Builder React bundle starting execution...');
function initPDFBuilderReact() {
  console.log('🔧 [PDF Builder] initPDFBuilderReact function called');
  if (DEBUG_VERBOSE) (0, _debug.debugLog)('✅ initPDFBuilderReact function called');
  try {
    // Vérifier si le container existe
    var container = document.getElementById('pdf-builder-react-root');
    console.log('🔍 [PDF Builder] Container element:', container);
    if (DEBUG_VERBOSE) (0, _debug.debugLog)('🔍 Container element:', container);
    if (!container) {
      console.error('❌ [PDF Builder] Container #pdf-builder-react-root not found');
      (0, _debug.debugError)('❌ Container #pdf-builder-react-root not found');
      return false;
    }
    console.log('✅ [PDF Builder] Container found, checking dependencies...');
    if (DEBUG_VERBOSE) (0, _debug.debugLog)('✅ Container found, checking dependencies...');

    // Vérifier les dépendances
    console.log('🔧 [PDF Builder] Checking React availability:', (0, _typeof2["default"])(_react["default"]));
    if (typeof _react["default"] === 'undefined') {
      console.error('❌ [PDF Builder] React is not available');
      (0, _debug.debugError)('❌ React is not available');
      return false;
    }
    console.log('🔧 [PDF Builder] Checking ReactDOM availability:', (0, _typeof2["default"])(_client["default"]));
    if (typeof _client["default"] === 'undefined') {
      console.error('❌ [PDF Builder] ReactDOM is not available');
      (0, _debug.debugError)('❌ ReactDOM is not available');
      return false;
    }
    console.log('✅ [PDF Builder] React dependencies available');
    if (DEBUG_VERBOSE) (0, _debug.debugLog)('✅ React dependencies available');
    console.log('🎯 [PDF Builder] All dependencies loaded, initializing React...');
    if (DEBUG_VERBOSE) (0, _debug.debugLog)('🎯 All dependencies loaded, initializing React...');

    // Masquer le loading et afficher l'éditeur
    var loadingEl = document.getElementById('pdf-builder-react-loading');
    var editorEl = document.getElementById('pdf-builder-react-editor');
    console.log('🎨 [PDF Builder] Hiding loading, showing editor:', {
      loadingEl: loadingEl,
      editorEl: editorEl
    });
    if (loadingEl) loadingEl.style.display = 'none';
    if (editorEl) editorEl.style.display = 'block';
    console.log('🎨 [PDF Builder] Creating React root...');
    if (DEBUG_VERBOSE) (0, _debug.debugLog)('🎨 Creating React root...');

    // Créer et rendre l'application React
    var root = _client["default"].createRoot(container);
    console.log('🎨 [PDF Builder] React root created, rendering component...');
    if (DEBUG_VERBOSE) (0, _debug.debugLog)('🎨 React root created, rendering component...');
    root.render(_react["default"].createElement('div', {
      style: {
        padding: '20px',
        border: '1px solid green',
        backgroundColor: 'lightgreen'
      }
    }, '✅ React is working! PDF Builder will load here.'));
    console.log('✅ [PDF Builder] React component rendered successfully');
    if (DEBUG_VERBOSE) (0, _debug.debugLog)('✅ React component rendered successfully');
    return true;
  } catch (error) {
    console.error('❌ [PDF Builder] Error in initPDFBuilderReact:', error);
    console.error('❌ [PDF Builder] Error stack:', error.stack);
    (0, _debug.debugError)('❌ Error in initPDFBuilderReact:', error);
    (0, _debug.debugError)('❌ Error stack:', error.stack);
    var _container = document.getElementById('pdf-builder-react-root');
    if (_container) {
      _container.innerHTML = '<p>❌ Erreur lors du rendu React: ' + error.message + '</p><pre>' + error.stack + '</pre>';
    }
    return false;
  }
}
if (DEBUG_VERBOSE) (0, _debug.debugLog)('📦 Creating exports object...');

// Export default pour webpack
var _exports = {
  initPDFBuilderReact: initPDFBuilderReact,
  loadTemplate: _globalApi.loadTemplate,
  getEditorState: _globalApi.getEditorState,
  setEditorState: _globalApi.setEditorState,
  getCurrentTemplate: _globalApi.getCurrentTemplate,
  exportTemplate: _globalApi.exportTemplate,
  saveTemplate: _globalApi.saveTemplate,
  registerEditorInstance: _globalApi.registerEditorInstance,
  resetAPI: _globalApi.resetAPI
};
if (DEBUG_VERBOSE) (0, _debug.debugLog)('🌐 Assigning to window...');

// Wrapper IIFE for immediate execution
(function () {
  console.log('🔄 [PDF Builder] IIFE starting...');
  if (typeof window === 'undefined') {
    console.warn('⚠️ [PDF Builder] Window not available, skipping global assignment');
    return;
  }

  // CRITICAL: Assign the exports object directly and immediately
  window.pdfBuilderReact = _exports;
  console.log('🌐 [PDF Builder] Assigned to window.pdfBuilderReact:', window.pdfBuilderReact);

  // Verify immediately
  if (window.pdfBuilderReact && typeof window.pdfBuilderReact.initPDFBuilderReact === 'function') {
    console.log('✅ [PDF Builder] initPDFBuilderReact function is available globally');
  } else {
    console.error('❌ [PDF Builder] initPDFBuilderReact function NOT available globally');
  }
}).call(window);
if (DEBUG_VERBOSE) (0, _debug.debugLog)('🎉 PDF Builder React bundle execution completed');

// NO MORE EXPORTS - webpack will handle this differently
// Removed: export default exports;
// Removed: if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') { module.exports = exports; }

/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ var __webpack_exports__ = (__webpack_exec__(326));
/******/ return __webpack_exports__;
/******/ }
]);
});
//# sourceMappingURL=pdf-builder-react.bundle.js.map