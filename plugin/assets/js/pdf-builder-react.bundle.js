"use strict";
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
return (self["webpackChunkPDFBuilder"] = self["webpackChunkPDFBuilder"] || []).push([[763],{

/***/ 326:
/***/ (() => {



var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");
var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));
var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));
var _possibleConstructorReturn2 = _interopRequireDefault(require("@babel/runtime/helpers/possibleConstructorReturn"));
var _getPrototypeOf2 = _interopRequireDefault(require("@babel/runtime/helpers/getPrototypeOf"));
var _inherits2 = _interopRequireDefault(require("@babel/runtime/helpers/inherits"));
require("../fallbacks/browser-compatibility.js");
var _react = _interopRequireDefault(require("react"));
var _client = _interopRequireDefault(require("react-dom/client"));
var _PDFBuilder = require("./PDFBuilder.tsx");
var _canvas = require("./constants/canvas.ts");
var _debug = require("./utils/debug");
var _globalApi = require("./api/global-api");
function _callSuper(t, o, e) { return o = (0, _getPrototypeOf2["default"])(o), (0, _possibleConstructorReturn2["default"])(t, _isNativeReflectConstruct() ? Reflect.construct(o, e || [], (0, _getPrototypeOf2["default"])(t).constructor) : o.apply(t, e)); }
function _isNativeReflectConstruct() { try { var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); } catch (t) {} return (_isNativeReflectConstruct = function _isNativeReflectConstruct() { return !!t; })(); } // ============================================================================
// PDF Builder React Bundle - Entry Point
// ============================================================================
// Note: Performance patch is loaded separately as 'pdf-builder-react-performance-patch' entry point
// Import du diagnostic de compatibilité
// Import des composants React
// Composant ErrorBoundary pour capturer les erreurs de rendu
var ErrorBoundary = /*#__PURE__*/function (_React$Component) {
  function ErrorBoundary(props) {
    var _this;
    (0, _classCallCheck2["default"])(this, ErrorBoundary);
    _this = _callSuper(this, ErrorBoundary, [props]);
    _this.state = {
      hasError: false,
      error: null,
      errorInfo: null
    };
    return _this;
  }
  (0, _inherits2["default"])(ErrorBoundary, _React$Component);
  return (0, _createClass2["default"])(ErrorBoundary, [{
    key: "componentDidCatch",
    value: function componentDidCatch(error, errorInfo) {
      (0, _debug.debugError)('❌ React Error Boundary caught an error:', error);
      (0, _debug.debugError)('❌ Error Info:', errorInfo);
      this.setState({
        error: error,
        errorInfo: errorInfo
      });
    }
  }, {
    key: "render",
    value: function render() {
      if (this.state.hasError) {
        return _react["default"].createElement('div', {
          style: {
            padding: '20px',
            border: '1px solid #ff6b6b',
            borderRadius: '5px',
            backgroundColor: '#ffe6e6',
            color: '#d63031',
            fontFamily: 'Arial, sans-serif'
          }
        }, _react["default"].createElement('h2', null, 'Erreur dans l\'éditeur PDF'), _react["default"].createElement('p', null, 'Une erreur s\'est produite lors du rendu de l\'éditeur. Veuillez rafraîchir la page.'), _react["default"].createElement('details', {
          style: {
            whiteSpace: 'pre-wrap'
          }
        }, _react["default"].createElement('summary', null, 'Détails de l\'erreur'), this.state.error && this.state.error.toString(), _react["default"].createElement('br'), this.state.errorInfo && this.state.errorInfo.componentStack));
      }
      return this.props.children;
    }
  }], [{
    key: "getDerivedStateFromError",
    value: function getDerivedStateFromError(_error) {
      return {
        hasError: true
      };
    }
  }]);
}(_react["default"].Component); // État de l'application
// let currentTemplate = null;
// let isModified = false;
// Flag pour afficher les logs d'initialisation détaillés
var DEBUG_VERBOSE = false;
if (DEBUG_VERBOSE) (0, _debug.debugLog)('🚀 PDF Builder React bundle starting execution...');
function initPDFBuilderReact() {
  if (DEBUG_VERBOSE) (0, _debug.debugLog)('✅ initPDFBuilderReact function called');
  try {
    // Vérifier si le container existe
    var container = document.getElementById('pdf-builder-react-root');
    if (DEBUG_VERBOSE) (0, _debug.debugLog)('🔍 Container element:', container);
    if (!container) {
      (0, _debug.debugError)('❌ Container #pdf-builder-react-root not found');
      return false;
    }
    if (DEBUG_VERBOSE) (0, _debug.debugLog)('✅ Container found, checking dependencies...');

    // Vérifier les dépendances
    if (typeof _react["default"] === 'undefined') {
      (0, _debug.debugError)('❌ React is not available');
      return false;
    }
    if (typeof _client["default"] === 'undefined') {
      (0, _debug.debugError)('❌ ReactDOM is not available');
      return false;
    }
    if (DEBUG_VERBOSE) (0, _debug.debugLog)('✅ React dependencies available');
    if (DEBUG_VERBOSE) (0, _debug.debugLog)('🎯 All dependencies loaded, initializing React...');

    // Masquer le loading et afficher l'éditeur
    var loadingEl = document.getElementById('pdf-builder-react-loading');
    var editorEl = document.getElementById('pdf-builder-react-editor');
    if (loadingEl) loadingEl.style.display = 'none';
    if (editorEl) editorEl.style.display = 'block';
    if (DEBUG_VERBOSE) (0, _debug.debugLog)('🎨 Creating React root...');

    // Créer et rendre l'application React
    var root = _client["default"].createRoot(container);
    if (DEBUG_VERBOSE) (0, _debug.debugLog)('🎨 React root created, rendering component...');
    root.render(_react["default"].createElement(ErrorBoundary, null, _react["default"].createElement(_PDFBuilder.PDFBuilder, {
      width: _canvas.DEFAULT_CANVAS_WIDTH,
      height: _canvas.DEFAULT_CANVAS_HEIGHT
    })));
    if (DEBUG_VERBOSE) (0, _debug.debugLog)('✅ React component rendered successfully');
    return true;
  } catch (error) {
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
  if (typeof window === 'undefined') {
    return;
  }

  // CRITICAL: Assign the exports object directly and immediately
  window.pdfBuilderReact = _exports;

  // Verify immediately
  if (window.pdfBuilderReact && typeof window.pdfBuilderReact.initPDFBuilderReact === 'function') {
    // Silent success - editor is ready
  } else {}
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
/******/ __webpack_exports__ = __webpack_exports__["default"];
/******/ return __webpack_exports__;
/******/ }
]);
});
//# sourceMappingURL=pdf-builder-react.bundle.js.map