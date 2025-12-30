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
/******/ 	var __webpack_modules__ = ({});
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
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
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
/******/ 	/* webpack/runtime/ensure chunk */
/******/ 	(() => {
/******/ 		__webpack_require__.f = {};
/******/ 		// This file contains only the entry chunk.
/******/ 		// The chunk loading function for additional chunks
/******/ 		__webpack_require__.e = (chunkId) => {
/******/ 			return Promise.all(Object.keys(__webpack_require__.f).reduce((promises, key) => {
/******/ 				__webpack_require__.f[key](chunkId, promises);
/******/ 				return promises;
/******/ 			}, []));
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/get javascript chunk filename */
/******/ 	(() => {
/******/ 		// This function allow to reference async chunks
/******/ 		__webpack_require__.u = (chunkId) => {
/******/ 			// return url for filenames based on template
/******/ 			return "js/" + chunkId + ".js";
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/get mini-css chunk filename */
/******/ 	(() => {
/******/ 		// This function allow to reference async chunks
/******/ 		__webpack_require__.miniCssF = (chunkId) => {
/******/ 			// return url for filenames based on template
/******/ 			return undefined;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/load script */
/******/ 	(() => {
/******/ 		var inProgress = {};
/******/ 		var dataWebpackPrefix = "PDFBuilder:";
/******/ 		// loadScript function to load a script via script tag
/******/ 		__webpack_require__.l = (url, done, key, chunkId) => {
/******/ 			if(inProgress[url]) { inProgress[url].push(done); return; }
/******/ 			var script, needAttach;
/******/ 			if(key !== undefined) {
/******/ 				var scripts = document.getElementsByTagName("script");
/******/ 				for(var i = 0; i < scripts.length; i++) {
/******/ 					var s = scripts[i];
/******/ 					if(s.getAttribute("src") == url || s.getAttribute("data-webpack") == dataWebpackPrefix + key) { script = s; break; }
/******/ 				}
/******/ 			}
/******/ 			if(!script) {
/******/ 				needAttach = true;
/******/ 				script = document.createElement('script');
/******/ 		
/******/ 				script.charset = 'utf-8';
/******/ 				if (__webpack_require__.nc) {
/******/ 					script.setAttribute("nonce", __webpack_require__.nc);
/******/ 				}
/******/ 				script.setAttribute("data-webpack", dataWebpackPrefix + key);
/******/ 		
/******/ 				script.src = url;
/******/ 			}
/******/ 			inProgress[url] = [done];
/******/ 			var onScriptComplete = (prev, event) => {
/******/ 				// avoid mem leaks in IE.
/******/ 				script.onerror = script.onload = null;
/******/ 				clearTimeout(timeout);
/******/ 				var doneFns = inProgress[url];
/******/ 				delete inProgress[url];
/******/ 				script.parentNode && script.parentNode.removeChild(script);
/******/ 				doneFns && doneFns.forEach((fn) => (fn(event)));
/******/ 				if(prev) return prev(event);
/******/ 			}
/******/ 			var timeout = setTimeout(onScriptComplete.bind(null, undefined, { type: 'timeout', target: script }), 120000);
/******/ 			script.onerror = onScriptComplete.bind(null, script.onerror);
/******/ 			script.onload = onScriptComplete.bind(null, script.onload);
/******/ 			needAttach && document.head.appendChild(script);
/******/ 		};
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
/******/ 	/* webpack/runtime/publicPath */
/******/ 	(() => {
/******/ 		__webpack_require__.p = "/wp-content/plugins/pdf-builder-pro/assets/";
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			763: 0
/******/ 		};
/******/ 		
/******/ 		__webpack_require__.f.j = (chunkId, promises) => {
/******/ 				// JSONP chunk loading for javascript
/******/ 				var installedChunkData = __webpack_require__.o(installedChunks, chunkId) ? installedChunks[chunkId] : undefined;
/******/ 				if(installedChunkData !== 0) { // 0 means "already installed".
/******/ 		
/******/ 					// a Promise means "currently loading".
/******/ 					if(installedChunkData) {
/******/ 						promises.push(installedChunkData[2]);
/******/ 					} else {
/******/ 						if(true) { // all chunks have JS
/******/ 							// setup Promise in chunk cache
/******/ 							var promise = new Promise((resolve, reject) => (installedChunkData = installedChunks[chunkId] = [resolve, reject]));
/******/ 							promises.push(installedChunkData[2] = promise);
/******/ 		
/******/ 							// start chunk loading
/******/ 							var url = __webpack_require__.p + __webpack_require__.u(chunkId);
/******/ 							// create error before stack unwound to get useful stacktrace later
/******/ 							var error = new Error();
/******/ 							var loadingEnded = (event) => {
/******/ 								if(__webpack_require__.o(installedChunks, chunkId)) {
/******/ 									installedChunkData = installedChunks[chunkId];
/******/ 									if(installedChunkData !== 0) installedChunks[chunkId] = undefined;
/******/ 									if(installedChunkData) {
/******/ 										var errorType = event && (event.type === 'load' ? 'missing' : event.type);
/******/ 										var realSrc = event && event.target && event.target.src;
/******/ 										error.message = 'Loading chunk ' + chunkId + ' failed.\n(' + errorType + ': ' + realSrc + ')';
/******/ 										error.name = 'ChunkLoadError';
/******/ 										error.type = errorType;
/******/ 										error.request = realSrc;
/******/ 										installedChunkData[1](error);
/******/ 									}
/******/ 								}
/******/ 							};
/******/ 							__webpack_require__.l(url, loadingEnded, "chunk-" + chunkId, chunkId);
/******/ 						}
/******/ 					}
/******/ 				}
/******/ 		};
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		// no on chunks loaded
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 		
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunkPDFBuilder"] = self["webpackChunkPDFBuilder"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};


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
var _PDFBuilder = _interopRequireDefault(require("./PDFBuilder.tsx"));
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

// Import direct du composant principal (sans lazy loading)
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
          element = (0, _react.createElement)(ErrorBoundary, null, (0, _react.createElement)(_PDFBuilder["default"], {
            width: canvasWidth,
            height: canvasHeight
          }));
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
          return __webpack_require__.e(/* import() */ 961).then(__webpack_require__.t.bind(__webpack_require__, 961, 19));
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
__webpack_exports__ = __webpack_exports__["default"];
/******/ 	return __webpack_exports__;
/******/ })()
;
});
//# sourceMappingURL=pdf-builder-react.bundle.js.map