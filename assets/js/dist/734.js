"use strict";
(self["webpackChunkPDFBuilderPro"] = self["webpackChunkPDFBuilderPro"] || []).push([[734],{

/***/ 325:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   usePerformanceMonitor: () => (/* binding */ usePerformanceMonitor)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(540);


/**
 * Hook personnalisé pour monitorer les performances du système d'aperçu
 * Mesure les temps de chargement, mémoire, et autres métriques
 */
function usePerformanceMonitor() {
  var componentName = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'Unknown';
  var startTimeRef = (0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  var renderCountRef = (0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(0);

  // Démarrer le monitoring
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    startTimeRef.current = performance.now();
    renderCountRef.current = 0;
    console.log("[Performance] ".concat(componentName, " - Mount started"));
    return function () {
      var duration = performance.now() - startTimeRef.current;
      console.log("[Performance] ".concat(componentName, " - Unmount after ").concat(duration.toFixed(2), "ms, ").concat(renderCountRef.current, " renders"));
    };
  }, [componentName]);

  // Tracker les renders
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    renderCountRef.current += 1;
  });

  // Mesurer une opération spécifique
  var measureOperation = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (operationName, operation) {
    var start = performance.now();
    try {
      var result = operation();
      var duration = performance.now() - start;
      console.log("[Performance] ".concat(componentName, " - ").concat(operationName, ": ").concat(duration.toFixed(2), "ms"));

      // Mesurer la mémoire si disponible
      if (performance.memory) {
        console.log("[Performance] ".concat(componentName, " - Memory: ").concat((performance.memory.usedJSHeapSize / 1024 / 1024).toFixed(2), "MB used"));
      }
      return result;
    } catch (error) {
      var _duration = performance.now() - start;
      console.error("[Performance] ".concat(componentName, " - ").concat(operationName, " failed after ").concat(_duration.toFixed(2), "ms:"), error);
      throw error;
    }
  }, [componentName]);

  // Mesurer le temps de chargement
  var measureLoadTime = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (resourceName) {
    var start = performance.now();
    return {
      end: function end() {
        var duration = performance.now() - start;
        console.log("[Performance] ".concat(componentName, " - ").concat(resourceName, " loaded in ").concat(duration.toFixed(2), "ms"));
        return duration;
      }
    };
  }, [componentName]);
  return {
    measureOperation: measureOperation,
    measureLoadTime: measureLoadTime
  };
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (usePerformanceMonitor);

/***/ }),

/***/ 438:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   calculateOptimalZoom: () => (/* binding */ calculateOptimalZoom),
/* harmony export */   checkBrowserSupport: () => (/* binding */ checkBrowserSupport),
/* harmony export */   debounce: () => (/* binding */ debounce),
/* harmony export */   formatPageNumber: () => (/* binding */ formatPageNumber),
/* harmony export */   generateCacheKey: () => (/* binding */ generateCacheKey),
/* harmony export */   getPageDimensions: () => (/* binding */ getPageDimensions),
/* harmony export */   getSystemInfo: () => (/* binding */ getSystemInfo),
/* harmony export */   measurePerformance: () => (/* binding */ measurePerformance),
/* harmony export */   throttle: () => (/* binding */ throttle)
/* harmony export */ });
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
/**
 * Utilitaires communs pour le système d'aperçu PDF
 * Fonctions helper pour le rendu, calculs et formatage
 */

/**
 * Calcule les dimensions d'une page PDF en pixels
 * @param {string} format - Format de page (A4, A3, Letter, etc.)
 * @param {number} dpi - Résolution en DPI (par défaut 96)
 * @returns {Object} Dimensions {width, height} en pixels
 */
function getPageDimensions() {
  var format = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'A4';
  var dpi = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 96;
  var dimensions = {
    A4: {
      width: 595,
      height: 842
    },
    // Points PDF à 72 DPI
    A3: {
      width: 842,
      height: 1191
    },
    Letter: {
      width: 612,
      height: 792
    },
    Legal: {
      width: 612,
      height: 1008
    }
  };
  var baseDims = dimensions[format] || dimensions.A4;

  // Conversion en pixels selon le DPI
  var scale = dpi / 72; // PDF base is 72 DPI

  return {
    width: Math.round(baseDims.width * scale),
    height: Math.round(baseDims.height * scale)
  };
}

/**
 * Calcule le niveau de zoom optimal pour contenir la page
 * @param {Object} pageDims - Dimensions de la page {width, height}
 * @param {Object} containerDims - Dimensions du conteneur {width, height}
 * @param {number} padding - Marge en pixels
 * @returns {number} Niveau de zoom (0.1 à 2.0)
 */
function calculateOptimalZoom(pageDims, containerDims) {
  var padding = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 20;
  var availableWidth = containerDims.width - padding * 2;
  var availableHeight = containerDims.height - padding * 2;
  var scaleX = availableWidth / pageDims.width;
  var scaleY = availableHeight / pageDims.height;
  var optimalScale = Math.min(scaleX, scaleY, 1); // Max 100% pour éviter l'agrandissement excessif

  return Math.max(0.1, Math.min(2.0, optimalScale));
}

/**
 * Formate un numéro de page
 * @param {number} current - Page actuelle
 * @param {number} total - Nombre total de pages
 * @returns {string} Format "X / Y"
 */
function formatPageNumber(current, total) {
  return "".concat(current, " / ").concat(total);
}

/**
 * Génère une clé unique pour le cache
 * @param {string} prefix - Préfixe pour la clé
 * @param {Object} params - Paramètres à inclure
 * @returns {string} Clé de cache unique
 */
function generateCacheKey(prefix) {
  var params = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  var sortedParams = Object.keys(params).sort().map(function (key) {
    return "".concat(key, ":").concat(params[key]);
  }).join('|');
  return "".concat(prefix, "_").concat(btoa(sortedParams).replace(/[^a-zA-Z0-9]/g, ''));
}

/**
 * Débounce une fonction
 * @param {Function} func - Fonction à debouncer
 * @param {number} wait - Délai en ms
 * @returns {Function} Fonction debouncée
 */
function debounce(func, wait) {
  var timeout;
  return function executedFunction() {
    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }
    var later = function later() {
      clearTimeout(timeout);
      func.apply(void 0, args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

/**
 * Throttle une fonction
 * @param {Function} func - Fonction à throttler
 * @param {number} limit - Limite en ms
 * @returns {Function} Fonction throttlée
 */
function throttle(func, limit) {
  var inThrottle;
  return function executedFunction() {
    if (!inThrottle) {
      for (var _len2 = arguments.length, args = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
        args[_key2] = arguments[_key2];
      }
      func.apply(this, args);
      inThrottle = true;
      setTimeout(function () {
        return inThrottle = false;
      }, limit);
    }
  };
}

/**
 * Mesure les performances d'une fonction
 * @param {Function} fn - Fonction à mesurer
 * @param {string} label - Label pour les logs
 * @returns {*} Résultat de la fonction
 */
function measurePerformance(fn) {
  var label = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'Operation';
  var start = performance.now();
  try {
    var result = fn();
    var duration = performance.now() - start;
    console.log("[Performance] ".concat(label, ": ").concat(duration.toFixed(2), "ms"));
    return result;
  } catch (error) {
    var _duration = performance.now() - start;
    console.error("[Performance] ".concat(label, " failed after ").concat(_duration.toFixed(2), "ms:"), error);
    throw error;
  }
}

/**
 * Vérifie si le navigateur supporte les fonctionnalités requises
 * @returns {Object} Support des fonctionnalités
 */
function checkBrowserSupport() {
  return {
    intersectionObserver: 'IntersectionObserver' in window,
    resizeObserver: 'ResizeObserver' in window,
    webWorkers: 'Worker' in window,
    canvas: 'HTMLCanvasElement' in window,
    webgl: function () {
      try {
        var canvas = document.createElement('canvas');
        return !!(window.WebGLRenderingContext && canvas.getContext('webgl'));
      } catch (e) {
        return false;
      }
    }(),
    fetch: 'fetch' in window,
    promises: 'Promise' in window,
    asyncAwait: _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
      return _regenerator().w(function (_context) {
        while (1) switch (_context.n) {
          case 0:
            return _context.a(2);
        }
      }, _callee);
    }))() instanceof Promise
  };
}

/**
 * Obtient les informations de débogage système
 * @returns {Object} Informations système
 */
function getSystemInfo() {
  return {
    userAgent: navigator.userAgent,
    language: navigator.language,
    platform: navigator.platform,
    cookieEnabled: navigator.cookieEnabled,
    onLine: navigator.onLine,
    screen: {
      width: screen.width,
      height: screen.height,
      colorDepth: screen.colorDepth
    },
    viewport: {
      width: window.innerWidth,
      height: window.innerHeight
    },
    memory: performance.memory ? {
      used: Math.round(performance.memory.usedJSHeapSize / 1024 / 1024),
      total: Math.round(performance.memory.totalJSHeapSize / 1024 / 1024),
      limit: Math.round(performance.memory.jsHeapSizeLimit / 1024 / 1024)
    } : null,
    timing: {
      loadTime: performance.timing.loadEventEnd - performance.timing.navigationStart,
      domReady: performance.timing.domContentLoadedEventEnd - performance.timing.navigationStart
    }
  };
}

/***/ }),

/***/ 544:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   ImageRenderer: () => (/* binding */ ImageRenderer)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(540);
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }


/**
 * Renderer pour les éléments image (logos, etc.)
 */
var ImageRenderer = function ImageRenderer(_ref) {
  var element = _ref.element,
    previewData = _ref.previewData,
    mode = _ref.mode;
  var _element$x = element.x,
    x = _element$x === void 0 ? 0 : _element$x,
    _element$y = element.y,
    y = _element$y === void 0 ? 0 : _element$y,
    _element$width = element.width,
    width = _element$width === void 0 ? 150 : _element$width,
    _element$height = element.height,
    height = _element$height === void 0 ? 80 : _element$height,
    _element$imageUrl = element.imageUrl,
    imageUrl = _element$imageUrl === void 0 ? '' : _element$imageUrl,
    _element$alt = element.alt,
    alt = _element$alt === void 0 ? 'Image' : _element$alt,
    _element$objectFit = element.objectFit,
    objectFit = _element$objectFit === void 0 ? 'contain' : _element$objectFit,
    _element$backgroundCo = element.backgroundColor,
    backgroundColor = _element$backgroundCo === void 0 ? 'transparent' : _element$backgroundCo,
    _element$borderWidth = element.borderWidth,
    borderWidth = _element$borderWidth === void 0 ? 0 : _element$borderWidth,
    _element$borderColor = element.borderColor,
    borderColor = _element$borderColor === void 0 ? '#000000' : _element$borderColor,
    _element$borderRadius = element.borderRadius,
    borderRadius = _element$borderRadius === void 0 ? 0 : _element$borderRadius,
    _element$opacity = element.opacity,
    opacity = _element$opacity === void 0 ? 1 : _element$opacity,
    _element$rotation = element.rotation,
    rotation = _element$rotation === void 0 ? 0 : _element$rotation,
    _element$scale = element.scale,
    scale = _element$scale === void 0 ? 1 : _element$scale,
    _element$visible = element.visible,
    visible = _element$visible === void 0 ? true : _element$visible,
    _element$shadow = element.shadow,
    shadow = _element$shadow === void 0 ? false : _element$shadow,
    _element$shadowColor = element.shadowColor,
    shadowColor = _element$shadowColor === void 0 ? '#000000' : _element$shadowColor,
    _element$shadowOffset = element.shadowOffsetX,
    shadowOffsetX = _element$shadowOffset === void 0 ? 2 : _element$shadowOffset,
    _element$shadowOffset2 = element.shadowOffsetY,
    shadowOffsetY = _element$shadowOffset2 === void 0 ? 2 : _element$shadowOffset2,
    _element$brightness = element.brightness,
    brightness = _element$brightness === void 0 ? 100 : _element$brightness,
    _element$contrast = element.contrast,
    contrast = _element$contrast === void 0 ? 100 : _element$contrast,
    _element$saturate = element.saturate,
    saturate = _element$saturate === void 0 ? 100 : _element$saturate;

  // Récupérer les données d'image depuis l'aperçu
  var elementKey = "".concat(element.type, "_").concat(element.id);
  var imageData = previewData[elementKey] || {};
  var finalImageUrl = imageData.imageUrl || imageUrl;
  var containerStyle = {
    position: 'absolute',
    left: x,
    top: y,
    width: width,
    height: height,
    backgroundColor: backgroundColor,
    border: borderWidth > 0 ? "".concat(borderWidth, "px solid ").concat(borderColor) : 'none',
    borderRadius: "".concat(borderRadius, "px"),
    opacity: opacity,
    display: visible ? 'flex' : 'none',
    alignItems: 'center',
    justifyContent: 'center',
    boxSizing: 'border-box',
    overflow: 'hidden',
    // Transformations
    transform: "rotate(".concat(rotation, "deg) scale(").concat(scale, ")"),
    transformOrigin: 'center center',
    // Ombres
    boxShadow: shadow ? "".concat(shadowOffsetX, "px ").concat(shadowOffsetY, "px 4px ").concat(shadowColor) : 'none'
  };
  var imageStyle = {
    width: '100%',
    height: '100%',
    objectFit: objectFit,
    borderRadius: borderWidth > 0 ? '0' : "".concat(borderRadius, "px"),
    // Filtres d'image
    filter: "brightness(".concat(brightness, "%) contrast(").concat(contrast, "%) saturate(").concat(saturate, "%)")
  };
  var placeholderStyle = {
    width: '100%',
    height: '100%',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#f8f9fa',
    border: '2px dashed #dee2e6',
    borderRadius: "".concat(borderRadius, "px"),
    color: '#6c757d',
    fontSize: '12px',
    textAlign: 'center',
    padding: '8px',
    boxSizing: 'border-box'
  };
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("div", {
    className: "preview-element preview-image-element",
    style: containerStyle,
    "data-element-id": element.id,
    "data-element-type": element.type
  }, finalImageUrl ? /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("img", {
    src: finalImageUrl,
    alt: alt,
    style: imageStyle,
    onError: function onError(e) {
      // Fallback vers le placeholder en cas d'erreur de chargement
      e.target.style.display = 'none';
      e.target.nextSibling.style.display = 'flex';
    }
  }) : null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("div", {
    style: _objectSpread(_objectSpread({}, placeholderStyle), {}, {
      display: finalImageUrl ? 'none' : 'flex'
    })
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("div", null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("div", {
    style: {
      fontSize: '16px',
      marginBottom: '4px'
    }
  }, "\uD83D\uDCF7"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("div", null, element.type === 'company_logo' ? 'Logo' : 'Image'))));
};

/***/ }),

/***/ 813:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(540);
/* harmony import */ var _context_PreviewContext__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(38);
/* harmony import */ var _hooks_usePerformanceMonitor__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(325);
/* harmony import */ var _utils_previewUtils__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(438);
var _excluded = ["type", "x", "y", "content"];
function _objectWithoutProperties(e, t) { if (null == e) return {}; var o, r, i = _objectWithoutPropertiesLoose(e, t); if (Object.getOwnPropertySymbols) { var n = Object.getOwnPropertySymbols(e); for (r = 0; r < n.length; r++) o = n[r], -1 === t.indexOf(o) && {}.propertyIsEnumerable.call(e, o) && (i[o] = e[o]); } return i; }
function _objectWithoutPropertiesLoose(r, e) { if (null == r) return {}; var t = {}; for (var n in r) if ({}.hasOwnProperty.call(r, n)) { if (-1 !== e.indexOf(n)) continue; t[n] = r[n]; } return t; }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }





/**
 * PDFRenderer - Renderer spécialisé pour l'aperçu PDF
 * Utilise Canvas HTML5 pour le rendu haute qualité
 */
function PDFRenderer(_ref) {
  var pageData = _ref.pageData,
    _ref$scale = _ref.scale,
    scale = _ref$scale === void 0 ? 1 : _ref$scale,
    _ref$className = _ref.className,
    className = _ref$className === void 0 ? '' : _ref$className;
  var canvasRef = (0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  var _usePerformanceMonito = (0,_hooks_usePerformanceMonitor__WEBPACK_IMPORTED_MODULE_2__.usePerformanceMonitor)('PDFRenderer'),
    measureOperation = _usePerformanceMonito.measureOperation;
  var _useState = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)('idle'),
    _useState2 = _slicedToArray(_useState, 2),
    renderStatus = _useState2[0],
    setRenderStatus = _useState2[1]; // idle, rendering, complete, error

  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    if (!pageData || !canvasRef.current) return;
    setRenderStatus('rendering');
    measureOperation('PDF Page Render', /*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
      var canvas, ctx, pageDims, scaledWidth, scaledHeight;
      return _regenerator().w(function (_context) {
        while (1) switch (_context.n) {
          case 0:
            try {
              canvas = canvasRef.current;
              ctx = canvas.getContext('2d'); // Dimensions de la page (A4 par défaut)
              pageDims = (0,_utils_previewUtils__WEBPACK_IMPORTED_MODULE_3__.getPageDimensions)('A4', 96);
              scaledWidth = pageDims.width * scale;
              scaledHeight = pageDims.height * scale; // Ajuster la taille du canvas
              canvas.width = scaledWidth;
              canvas.height = scaledHeight;

              // Fond blanc
              ctx.fillStyle = 'white';
              ctx.fillRect(0, 0, scaledWidth, scaledHeight);

              // Appliquer l'échelle
              ctx.save();
              ctx.scale(scale, scale);

              // Rendu simulé du contenu PDF
              // Ici : logique réelle de rendu PDF (screenshot + TCPDF)
              renderPDFContent(ctx, pageData, pageDims.width, pageDims.height);
              ctx.restore();
              setRenderStatus('complete');
            } catch (error) {
              // Erreur rendu PDF
              setRenderStatus('error');
            }
          case 1:
            return _context.a(2);
        }
      }, _callee);
    })));
  }, [pageData, scale, measureOperation]);

  // Fonction de rendu simulé (remplacer par vraie logique)
  var renderPDFContent = function renderPDFContent(ctx, data, width, height) {
    // Fond de page
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, width, height);

    // Bordure de page
    ctx.strokeStyle = '#e0e0e0';
    ctx.lineWidth = 1;
    ctx.strokeRect(10, 10, width - 20, height - 20);

    // Contenu simulé
    if (data.elements) {
      data.elements.forEach(function (element, index) {
        renderElement(ctx, element, index);
      });
    }

    // Numéro de page
    ctx.fillStyle = '#666666';
    ctx.font = '12px Arial';
    ctx.textAlign = 'center';
    ctx.fillText("Page ".concat(data.number || 1), width / 2, height - 20);
  };

  // Rendu d'un élément individuel
  var renderElement = function renderElement(ctx, element, index) {
    var type = element.type,
      _element$x = element.x,
      x = _element$x === void 0 ? 0 : _element$x,
      _element$y = element.y,
      y = _element$y === void 0 ? 0 : _element$y,
      _element$content = element.content,
      content = _element$content === void 0 ? '' : _element$content,
      props = _objectWithoutProperties(element, _excluded);
    ctx.save();
    switch (type) {
      case 'text':
        ctx.fillStyle = props.color || '#000000';
        ctx.font = "".concat(props.fontSize || 12, "px ").concat(props.fontFamily || 'Arial');
        ctx.textAlign = props.textAlign || 'left';
        ctx.fillText(content, x, y);
        break;
      case 'rectangle':
        ctx.fillStyle = props.fillColor || '#cccccc';
        ctx.strokeStyle = props.strokeColor || '#000000';
        ctx.lineWidth = props.strokeWidth || 1;
        if (props.fill) ctx.fillRect(x, y, props.width || 100, props.height || 50);
        if (props.stroke) ctx.strokeRect(x, y, props.width || 100, props.height || 50);
        break;
      case 'line':
        ctx.strokeStyle = props.color || '#000000';
        ctx.lineWidth = props.width || 1;
        ctx.beginPath();
        ctx.moveTo(x, y);
        ctx.lineTo(props.x2 || x + 100, props.y2 || y);
        ctx.stroke();
        break;
      default:
        // Élément non supporté - rendu générique
        ctx.fillStyle = '#ffcccc';
        ctx.fillRect(x, y, 50, 20);
        ctx.fillStyle = '#000000';
        ctx.font = '10px Arial';
        ctx.fillText("".concat(type), x + 5, y + 15);
    }
    ctx.restore();
  };
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("div", {
    className: "pdf-renderer ".concat(className)
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("canvas", {
    ref: canvasRef,
    className: "pdf-canvas ".concat(renderStatus),
    style: {
      maxWidth: '100%',
      height: 'auto',
      border: renderStatus === 'error' ? '2px solid #ff6b6b' : '1px solid #e0e0e0',
      borderRadius: '4px'
    }
  }), renderStatus === 'rendering' && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("div", {
    className: "pdf-renderer-loading"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("div", {
    className: "pdf-spinner"
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("span", null, "Rendu en cours...")), renderStatus === 'error' && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("div", {
    className: "pdf-renderer-error"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("span", null, "Erreur de rendu")));
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (/*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.memo(PDFRenderer));

/***/ })

}]);