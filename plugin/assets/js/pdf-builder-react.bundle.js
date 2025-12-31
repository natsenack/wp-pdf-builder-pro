/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ 206:
/***/ ((module) => {

module.exports = ReactDOM;

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
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/create fake namespace object */
/******/ 	(() => {
/******/ 		var getProto = Object.getPrototypeOf ? (obj) => (Object.getPrototypeOf(obj)) : (obj) => (obj.__proto__);
/******/ 		var leafPrototypes;
/******/ 		// create a fake namespace object
/******/ 		// mode & 1: value is a module id, require it
/******/ 		// mode & 2: merge all properties of value into the ns
/******/ 		// mode & 4: return value when already ns object
/******/ 		// mode & 16: return value when it's Promise-like
/******/ 		// mode & 8|1: behave like require
/******/ 		__webpack_require__.t = function(value, mode) {
/******/ 			if(mode & 1) value = this(value);
/******/ 			if(mode & 8) return value;
/******/ 			if(typeof value === 'object' && value) {
/******/ 				if((mode & 4) && value.__esModule) return value;
/******/ 				if((mode & 16) && typeof value.then === 'function') return value;
/******/ 			}
/******/ 			var ns = Object.create(null);
/******/ 			__webpack_require__.r(ns);
/******/ 			var def = {};
/******/ 			leafPrototypes = leafPrototypes || [null, getProto({}), getProto([]), getProto(getProto)];
/******/ 			for(var current = mode & 2 && value; (typeof current == 'object' || typeof current == 'function') && !~leafPrototypes.indexOf(current); current = getProto(current)) {
/******/ 				Object.getOwnPropertyNames(current).forEach((key) => (def[key] = () => (value[key])));
/******/ 			}
/******/ 			def['default'] = () => (value);
/******/ 			__webpack_require__.d(ns, def);
/******/ 			return ns;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/


var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");
var _regenerator = _interopRequireDefault(require("@babel/runtime/regenerator"));
var _asyncToGenerator2 = _interopRequireDefault(require("@babel/runtime/helpers/asyncToGenerator"));
var _typeof2 = _interopRequireDefault(require("@babel/runtime/helpers/typeof"));
var _PDFBuilder = _interopRequireDefault(require("./PDFBuilder.tsx"));
var _canvas = require("./constants/canvas");
var _debug = require("./utils/debug");
var _react = require("react");
var _client = require("react-dom/client");
// ============================================================================
// PDF Builder React Bundle - Entry Point OPTIMISÉ avec Code Splitting
// ============================================================================

console.log('🎯 [BUNDLE START] pdf-builder-react/index.js file loaded and executing');

// Import the existing PDFBuilder component

// Import React for compatibility

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
          element = /*#__PURE__*/(0, _react.createElement)(_PDFBuilder["default"], {
            width: canvasWidth,
            height: canvasHeight
          });
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
          return Promise.resolve(/* import() */).then(__webpack_require__.t.bind(__webpack_require__, 206, 19));
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
  PDFBuilder: _PDFBuilder["default"],
  DEFAULT_CANVAS_WIDTH: _canvas.DEFAULT_CANVAS_WIDTH,
  DEFAULT_CANVAS_HEIGHT: _canvas.DEFAULT_CANVAS_HEIGHT,
  getCanvasDimensions: _canvas.getCanvasDimensions,
  _isWebpackBundle: true
};
if (DEBUG_VERBOSE) (0, _debug.debugLog)('🌐 Assigning to window...');

// ✅ CRITICAL: Assign to window SYNCHRONOUSLY
if (typeof window !== 'undefined') {
  window.pdfBuilderReact = _exports;
  console.log('✅ [WEBPACK BUNDLE] window.pdfBuilderReact assigned manually in index.js');
}

// Remove export to avoid webpack module issues
// export default exports;
/******/ })()
;
//# sourceMappingURL=pdf-builder-react.bundle.js.map