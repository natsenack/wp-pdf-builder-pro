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
/***/ ((__unused_webpack___webpack_module__, __unused_webpack___webpack_exports__, __webpack_require__) => {



var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");
Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;
var _regenerator = _interopRequireDefault(require("@babel/runtime/regenerator"));
var _asyncToGenerator2 = _interopRequireDefault(require("@babel/runtime/helpers/asyncToGenerator"));
var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));
var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));
var _possibleConstructorReturn2 = _interopRequireDefault(require("@babel/runtime/helpers/possibleConstructorReturn"));
var _getPrototypeOf2 = _interopRequireDefault(require("@babel/runtime/helpers/getPrototypeOf"));
var _inherits2 = _interopRequireDefault(require("@babel/runtime/helpers/inherits"));
var _typeof2 = _interopRequireDefault(require("@babel/runtime/helpers/typeof"));
require("../fallbacks/browser-compatibility.js");
var _canvas = require("./constants/canvas.ts");
var _debug = require("./utils/debug.ts");
var _react = require("react");
var _client = require("react-dom/client");
var _globalApi = require("./api/global-api");
function _callSuper(t, o, e) { return o = (0, _getPrototypeOf2["default"])(o), (0, _possibleConstructorReturn2["default"])(t, _isNativeReflectConstruct() ? Reflect.construct(o, e || [], (0, _getPrototypeOf2["default"])(t).constructor) : o.apply(t, e)); }
function _isNativeReflectConstruct() { try { var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); } catch (t) {} return (_isNativeReflectConstruct = function _isNativeReflectConstruct() { return !!t; })(); }
// ============================================================================
// PDF Builder React Bundle - Entry Point OPTIMISÉ avec Code Splitting
// ============================================================================

console.log('🎯 [BUNDLE START] pdf-builder-react/index.js file loaded and executing');

// Import du diagnostic de compatibilité

// Imports synchrones légers

// Import React pour les composants

console.log('🔧 [WEBPACK BUNDLE] pdf-builder-react/index.js starting execution...');
console.log('🔧 [WEBPACK BUNDLE] React available:', (0, _typeof2["default"])(_react.createElement));
console.log('🔧 [WEBPACK BUNDLE] React.useRef available:', (0, _typeof2["default"])(_react.useRef));
console.log('🔧 [WEBPACK BUNDLE] React.useState available:', (0, _typeof2["default"])(_react.useState));
console.log('🔧 [WEBPACK BUNDLE] createRoot available:', (0, _typeof2["default"])(_client.createRoot));

// ✅ Exports React from window for fallback access
if (typeof window !== 'undefined' && !window.React) {
  window.React = {
    createElement: _react.createElement,
    Component: _react.Component,
    useRef: _react.useRef,
    useState: _react.useState
  };
}
if (typeof window !== 'undefined' && !window.ReactDOM) {
  window.ReactDOM = {
    createRoot: _client.createRoot
  };
}

// Lazy loading du composant principal pour réduire la taille du bundle initial
var PDFBuilder = (0, _react.lazy)(function () {
  return Promise.all(/* import() */[__webpack_require__.e(96), __webpack_require__.e(271)]).then(__webpack_require__.bind(__webpack_require__, 271));
});
// Composant ErrorBoundary pour capturer les erreurs de rendu
var ErrorBoundary = /*#__PURE__*/function (_Component) {
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
  (0, _inherits2["default"])(ErrorBoundary, _Component);
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
        return (0, _react.createElement)('div', {
          style: {
            padding: '20px',
            border: '1px solid #ff6b6b',
            borderRadius: '5px',
            backgroundColor: '#ffe6e6',
            color: '#d63031',
            fontFamily: 'Arial, sans-serif'
          }
        }, (0, _react.createElement)('h2', null, 'Erreur dans l\'éditeur PDF'), (0, _react.createElement)('p', null, 'Une erreur s\'est produite lors du rendu de l\'éditeur. Veuillez rafraîchir la page.'), (0, _react.createElement)('details', {
          style: {
            whiteSpace: 'pre-wrap'
          }
        }, (0, _react.createElement)('summary', null, 'Détails de l\'erreur'), this.state.error && this.state.error.toString(), (0, _react.createElement)('br'), this.state.errorInfo && this.state.errorInfo.componentStack));
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
}(_react.Component); // État de l'application
// let currentTemplate = null;
// let isModified = false;
// Flag pour afficher les logs d'initialisation détaillés
var DEBUG_VERBOSE = false;
console.log('🎯 [BUNDLE INIT] About to define initPDFBuilderReact function');
if (DEBUG_VERBOSE) (0, _debug.debugLog)('🚀 PDF Builder React bundle starting execution...');
function initPDFBuilderReact() {
  return _initPDFBuilderReact.apply(this, arguments);
}
function _initPDFBuilderReact() {
  _initPDFBuilderReact = (0, _asyncToGenerator2["default"])(/*#__PURE__*/_regenerator["default"].mark(function _callee() {
    var container, loadingEl, editorEl, root, canvasDimensions, canvasWidth, canvasHeight, element, _yield$import, render, _container, _t;
    return _regenerator["default"].wrap(function (_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          console.log('🚀 [initPDFBuilderReact] Function called');
          if (DEBUG_VERBOSE) (0, _debug.debugLog)('✅ initPDFBuilderReact function called');
          _context.prev = 1;
          // Vérifier si le container existe
          container = document.getElementById('pdf-builder-react-root');
          console.log('🔍 [initPDFBuilderReact] Container found:', !!container);
          if (DEBUG_VERBOSE) (0, _debug.debugLog)('🔍 Container element:', container);
          if (container) {
            _context.next = 2;
            break;
          }
          console.error('❌ [initPDFBuilderReact] Container #pdf-builder-react-root not found');
          (0, _debug.debugError)('❌ Container #pdf-builder-react-root not found');
          return _context.abrupt("return", false);
        case 2:
          if (DEBUG_VERBOSE) (0, _debug.debugLog)('✅ Container found, checking dependencies...');

          // Vérifier les dépendances
          if (!(typeof _react.createElement === 'undefined')) {
            _context.next = 3;
            break;
          }
          (0, _debug.debugError)('❌ React is not available');
          return _context.abrupt("return", false);
        case 3:
          if (DEBUG_VERBOSE) (0, _debug.debugLog)('✅ React dependencies available');

          // Composants déjà chargés de manière synchrone
          if (DEBUG_VERBOSE) (0, _debug.debugLog)('✅ Components loaded synchronously, initializing React...');

          // Masquer le loading et afficher l'éditeur
          loadingEl = document.getElementById('pdf-builder-loader');
          editorEl = document.getElementById('pdf-builder-editor-container');
          if (loadingEl) loadingEl.style.display = 'none';
          if (editorEl) editorEl.style.display = 'block';
          if (DEBUG_VERBOSE) (0, _debug.debugLog)('🎨 Creating React root...');

          // Créer et rendre l'application React
          // Essayer createRoot d'abord (React 18), sinon utiliser render (compatibilité)

          console.log('🔧 [initPDFBuilderReact] Checking ReactDOM.createRoot:', (0, _typeof2["default"])(_client.createRoot));
          if (_client.createRoot) {
            root = (0, _client.createRoot)(container);
            console.log('✅ [initPDFBuilderReact] Using React 18 createRoot API');
            if (DEBUG_VERBOSE) (0, _debug.debugLog)('🎨 Using React 18 createRoot API');
          } else {
            console.log('⚠️ [initPDFBuilderReact] createRoot not available, using render fallback');
            // Fallback pour anciennes versions
            if (DEBUG_VERBOSE) (0, _debug.debugLog)('🎨 Using React render API (fallback)');
          }
          console.log('🎨 [initPDFBuilderReact] About to render React component...');

          // Récupérer les dimensions dynamiques depuis les paramètres
          canvasDimensions = (0, _canvas.getCanvasDimensions)();
          canvasWidth = canvasDimensions.width;
          canvasHeight = canvasDimensions.height;
          console.log('📐 [initPDFBuilderReact] Canvas dimensions:', {
            width: canvasWidth,
            height: canvasHeight
          });
          element = (0, _react.createElement)(ErrorBoundary, null, (0, _react.createElement)(_react.Suspense, {
            fallback: (0, _react.createElement)('div', {
              style: {
                padding: '20px',
                textAlign: 'center'
              }
            }, 'Chargement de l\'éditeur PDF...')
          }, (0, _react.createElement)(PDFBuilder, {
            width: canvasWidth,
            height: canvasHeight
          })));
          if (!root) {
            _context.next = 4;
            break;
          }
          // React 18 API
          console.log('🎯 [initPDFBuilderReact] Calling root.render()...');
          root.render(element);
          console.log('✅ [initPDFBuilderReact] root.render() completed');
          _context.next = 6;
          break;
        case 4:
          // Fallback API
          console.log('🎯 [initPDFBuilderReact] Calling ReactDOM.render()...');
          // For fallback, we need to import render from react-dom
          _context.next = 5;
          return __webpack_require__.e(/* import() */ 96).then(__webpack_require__.t.bind(__webpack_require__, 961, 19));
        case 5:
          _yield$import = _context.sent;
          render = _yield$import.render;
          render(element, container);
          console.log('✅ [initPDFBuilderReact] ReactDOM.render() completed');
        case 6:
          console.log('✅ [initPDFBuilderReact] React rendering completed successfully');
          if (DEBUG_VERBOSE) (0, _debug.debugLog)('✅ React component rendered successfully');
          return _context.abrupt("return", true);
        case 7:
          _context.prev = 7;
          _t = _context["catch"](1);
          (0, _debug.debugError)('❌ Error in initPDFBuilderReact:', _t);
          (0, _debug.debugError)('❌ Error stack:', _t.stack);
          _container = document.getElementById('pdf-builder-react-root');
          if (_container) {
            _container.innerHTML = '<p>❌ Erreur lors du rendu React: ' + _t.message + '</p><pre>' + _t.stack + '</pre>';
          }
          return _context.abrupt("return", false);
        case 8:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[1, 7]]);
  }));
  return _initPDFBuilderReact.apply(this, arguments);
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
  resetAPI: _globalApi.resetAPI,
  updateCanvasDimensions: _globalApi.updateCanvasDimensions,
  _isWebpackBundle: true
};
if (DEBUG_VERBOSE) (0, _debug.debugLog)('🌐 Assigning to window...');

// ✅ CRITICAL: Assign to window SYNCHRONOUSLY
if (typeof window !== 'undefined') {
  window.pdfBuilderReact = _exports;
  console.log('✅ [WEBPACK BUNDLE] window.pdfBuilderReact assigned manually in index.js');
}

// No complex exports - let webpack UMD handle it with the assignment above
var _default = exports["default"] = _exports;

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