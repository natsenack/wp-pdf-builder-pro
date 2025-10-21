"use strict";
(self["webpackChunkPDFBuilderPro"] = self["webpackChunkPDFBuilderPro"] || []).push([[851],{

/***/ 851:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, {
  "default": () => (/* binding */ preview_system_MetaboxMode)
});

// NAMESPACE OBJECT: ./resources/js/components/preview-system/renderers/ImageRenderer.jsx
var ImageRenderer_namespaceObject = {};
__webpack_require__.r(ImageRenderer_namespaceObject);
__webpack_require__.d(ImageRenderer_namespaceObject, {
  ImageRenderer: () => (ImageRenderer)
});

// EXTERNAL MODULE: ./node_modules/react/index.js
var react = __webpack_require__(540);
// EXTERNAL MODULE: ./resources/js/components/preview-system/context/PreviewContext.jsx
var PreviewContext = __webpack_require__(38);
;// ./resources/js/components/preview-system/hooks/usePerformanceMonitor.js


/**
 * Hook personnalisé pour monitorer les performances du système d'aperçu
 * Mesure les temps de chargement, mémoire, et autres métriques
 */
function usePerformanceMonitor() {
  var componentName = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'Unknown';
  var startTimeRef = (0,react.useRef)(null);
  var renderCountRef = (0,react.useRef)(0);

  // Démarrer le monitoring
  (0,react.useEffect)(function () {
    startTimeRef.current = performance.now();
    renderCountRef.current = 0;
    console.log("[Performance] ".concat(componentName, " - Mount started"));
    return function () {
      var duration = performance.now() - startTimeRef.current;
      console.log("[Performance] ".concat(componentName, " - Unmount after ").concat(duration.toFixed(2), "ms, ").concat(renderCountRef.current, " renders"));
    };
  }, [componentName]);

  // Tracker les renders
  (0,react.useEffect)(function () {
    renderCountRef.current += 1;
  });

  // Mesurer une opération spécifique
  var measureOperation = (0,react.useCallback)(function (operationName, operation) {
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
  var measureLoadTime = (0,react.useCallback)(function (resourceName) {
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
/* harmony default export */ const hooks_usePerformanceMonitor = (usePerformanceMonitor);
;// ./resources/js/components/preview-system/utils/previewUtils.js
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
;// ./resources/js/components/preview-system/renderers/PDFRenderer.jsx
var _excluded = ["type", "x", "y", "content"];
function _objectWithoutProperties(e, t) { if (null == e) return {}; var o, r, i = _objectWithoutPropertiesLoose(e, t); if (Object.getOwnPropertySymbols) { var n = Object.getOwnPropertySymbols(e); for (r = 0; r < n.length; r++) o = n[r], -1 === t.indexOf(o) && {}.propertyIsEnumerable.call(e, o) && (i[o] = e[o]); } return i; }
function _objectWithoutPropertiesLoose(r, e) { if (null == r) return {}; var t = {}; for (var n in r) if ({}.hasOwnProperty.call(r, n)) { if (-1 !== e.indexOf(n)) continue; t[n] = r[n]; } return t; }
function PDFRenderer_regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return PDFRenderer_regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (PDFRenderer_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, PDFRenderer_regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, PDFRenderer_regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), PDFRenderer_regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", PDFRenderer_regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), PDFRenderer_regeneratorDefine2(u), PDFRenderer_regeneratorDefine2(u, o, "Generator"), PDFRenderer_regeneratorDefine2(u, n, function () { return this; }), PDFRenderer_regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (PDFRenderer_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function PDFRenderer_regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } PDFRenderer_regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { PDFRenderer_regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, PDFRenderer_regeneratorDefine2(e, r, n, t); }
function PDFRenderer_asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function PDFRenderer_asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { PDFRenderer_asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { PDFRenderer_asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
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
  var canvasRef = (0,react.useRef)(null);
  var _usePerformanceMonito = usePerformanceMonitor('PDFRenderer'),
    measureOperation = _usePerformanceMonito.measureOperation;
  var _useState = (0,react.useState)('idle'),
    _useState2 = _slicedToArray(_useState, 2),
    renderStatus = _useState2[0],
    setRenderStatus = _useState2[1]; // idle, rendering, complete, error

  (0,react.useEffect)(function () {
    if (!pageData || !canvasRef.current) return;
    setRenderStatus('rendering');
    measureOperation('PDF Page Render', /*#__PURE__*/PDFRenderer_asyncToGenerator(/*#__PURE__*/PDFRenderer_regenerator().m(function _callee() {
      var canvas, ctx, pageDims, scaledWidth, scaledHeight;
      return PDFRenderer_regenerator().w(function (_context) {
        while (1) switch (_context.n) {
          case 0:
            try {
              canvas = canvasRef.current;
              ctx = canvas.getContext('2d'); // Dimensions de la page (A4 par défaut)
              pageDims = getPageDimensions('A4', 96);
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
              console.error('Erreur rendu PDF:', error);
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
  return /*#__PURE__*/react.createElement("div", {
    className: "pdf-renderer ".concat(className)
  }, /*#__PURE__*/react.createElement("canvas", {
    ref: canvasRef,
    className: "pdf-canvas ".concat(renderStatus),
    style: {
      maxWidth: '100%',
      height: 'auto',
      border: renderStatus === 'error' ? '2px solid #ff6b6b' : '1px solid #e0e0e0',
      borderRadius: '4px'
    }
  }), renderStatus === 'rendering' && /*#__PURE__*/react.createElement("div", {
    className: "pdf-renderer-loading"
  }, /*#__PURE__*/react.createElement("div", {
    className: "pdf-spinner"
  }), /*#__PURE__*/react.createElement("span", null, "Rendu en cours...")), renderStatus === 'error' && /*#__PURE__*/react.createElement("div", {
    className: "pdf-renderer-error"
  }, /*#__PURE__*/react.createElement("span", null, "Erreur de rendu")));
}
/* harmony default export */ const renderers_PDFRenderer = (/*#__PURE__*/react.memo(PDFRenderer));
;// ./resources/js/components/preview-system/renderers/CanvasRenderer.jsx
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }





/**
 * CanvasRenderer - Renderer spécialisé pour l'aperçu des éléments Canvas
 * Rend les éléments d'édition en temps réel avec interactions
 */
function CanvasRenderer(_ref) {
  var _ref$elements = _ref.elements,
    elements = _ref$elements === void 0 ? [] : _ref$elements,
    _ref$scale = _ref.scale,
    scale = _ref$scale === void 0 ? 1 : _ref$scale,
    _ref$interactive = _ref.interactive,
    interactive = _ref$interactive === void 0 ? false : _ref$interactive,
    _ref$className = _ref.className,
    className = _ref$className === void 0 ? '' : _ref$className;
  var canvasRef = (0,react.useRef)(null);
  var containerRef = (0,react.useRef)(null);
  var _usePerformanceMonito = usePerformanceMonitor('CanvasRenderer'),
    measureOperation = _usePerformanceMonito.measureOperation;

  // Dimensions de la page
  var pageDims = getPageDimensions('A4', 96);

  // Rendu des éléments Canvas
  var renderElements = (0,react.useCallback)(function (ctx, elements, scale) {
    // Fond blanc
    ctx.fillStyle = 'white';
    ctx.fillRect(0, 0, pageDims.width * scale, pageDims.height * scale);

    // Appliquer l'échelle
    ctx.save();
    ctx.scale(scale, scale);

    // Bordure de page
    ctx.strokeStyle = '#e0e0e0';
    ctx.lineWidth = 1 / scale; // Ajuster pour l'échelle
    ctx.strokeRect(10, 10, pageDims.width - 20, pageDims.height - 20);

    // Guides de marge (optionnel)
    ctx.strokeStyle = '#f0f0f0';
    ctx.setLineDash([5, 5]);
    ctx.strokeRect(50, 50, pageDims.width - 100, pageDims.height - 100);
    ctx.setLineDash([]);

    // Rendre chaque élément
    elements.forEach(function (element, index) {
      renderElement(ctx, element, index, scale);
    });
    ctx.restore();
  }, [pageDims]);

  // Rendu d'un élément individuel
  var renderElement = function renderElement(ctx, element, index, scale) {
    var type = element.type,
      _element$x = element.x,
      x = _element$x === void 0 ? 0 : _element$x,
      _element$y = element.y,
      y = _element$y === void 0 ? 0 : _element$y,
      _element$width = element.width,
      width = _element$width === void 0 ? 100 : _element$width,
      _element$height = element.height,
      height = _element$height === void 0 ? 50 : _element$height,
      _element$content = element.content,
      content = _element$content === void 0 ? '' : _element$content,
      _element$properties = element.properties,
      properties = _element$properties === void 0 ? {} : _element$properties;
    ctx.save();

    // Positionnement
    ctx.translate(x, y);
    switch (type) {
      case 'text':
        renderTextElement(ctx, _objectSpread({
          content: content
        }, properties));
        break;
      case 'dynamic-text':
        renderDynamicTextElement(ctx, _objectSpread({
          content: content
        }, properties));
        break;
      case 'rectangle':
        renderRectangleElement(ctx, _objectSpread({
          width: width,
          height: height
        }, properties));
        break;
      case 'image':
        renderImageElement(ctx, _objectSpread({
          width: width,
          height: height
        }, properties));
        break;
      case 'line':
        renderLineElement(ctx, _objectSpread({
          width: width,
          height: height
        }, properties));
        break;
      case 'product_table':
        renderTableElement(ctx, _objectSpread({
          width: width,
          height: height
        }, properties));
        break;
      default:
        renderUnknownElement(ctx, {
          type: type,
          width: width,
          height: height
        });
    }

    // Sélection visuelle si interactif
    if (interactive && element.selected) {
      ctx.strokeStyle = '#007cba';
      ctx.lineWidth = 2 / scale;
      ctx.strokeRect(-2, -2, width + 4, height + 4);
    }
    ctx.restore();
  };

  // Rendu texte
  var renderTextElement = function renderTextElement(ctx, _ref2) {
    var content = _ref2.content,
      _ref2$fontSize = _ref2.fontSize,
      fontSize = _ref2$fontSize === void 0 ? 12 : _ref2$fontSize,
      _ref2$fontFamily = _ref2.fontFamily,
      fontFamily = _ref2$fontFamily === void 0 ? 'Arial' : _ref2$fontFamily,
      _ref2$color = _ref2.color,
      color = _ref2$color === void 0 ? '#000000' : _ref2$color,
      _ref2$textAlign = _ref2.textAlign,
      textAlign = _ref2$textAlign === void 0 ? 'left' : _ref2$textAlign;
    ctx.fillStyle = color;
    ctx.font = "".concat(fontSize, "px ").concat(fontFamily);
    ctx.textAlign = textAlign;
    ctx.fillText(content, 0, fontSize);
  };

  // Rendu texte dynamique
  var renderDynamicTextElement = function renderDynamicTextElement(ctx, props) {
    // Style spécial pour le texte dynamique
    ctx.fillStyle = '#0066cc';
    renderTextElement(ctx, props);

    // Indicateur visuel
    ctx.strokeStyle = '#0066cc';
    ctx.lineWidth = 1;
    ctx.strokeRect(-2, -2, props.width + 4, props.height + 4);
  };

  // Rendu rectangle
  var renderRectangleElement = function renderRectangleElement(ctx, _ref3) {
    var width = _ref3.width,
      height = _ref3.height,
      _ref3$fillColor = _ref3.fillColor,
      fillColor = _ref3$fillColor === void 0 ? '#cccccc' : _ref3$fillColor,
      _ref3$strokeColor = _ref3.strokeColor,
      strokeColor = _ref3$strokeColor === void 0 ? '#000000' : _ref3$strokeColor,
      _ref3$strokeWidth = _ref3.strokeWidth,
      strokeWidth = _ref3$strokeWidth === void 0 ? 1 : _ref3$strokeWidth,
      _ref3$fill = _ref3.fill,
      fill = _ref3$fill === void 0 ? true : _ref3$fill,
      _ref3$stroke = _ref3.stroke,
      stroke = _ref3$stroke === void 0 ? true : _ref3$stroke;
    if (fill) {
      ctx.fillStyle = fillColor;
      ctx.fillRect(0, 0, width, height);
    }
    if (stroke) {
      ctx.strokeStyle = strokeColor;
      ctx.lineWidth = strokeWidth;
      ctx.strokeRect(0, 0, width, height);
    }
  };

  // Rendu image
  var renderImageElement = function renderImageElement(ctx, _ref4) {
    var width = _ref4.width,
      height = _ref4.height,
      src = _ref4.src,
      _ref4$alt = _ref4.alt,
      alt = _ref4$alt === void 0 ? 'Image' : _ref4$alt;
    if (src) {
      var img = new Image();
      img.onload = function () {
        ctx.drawImage(img, 0, 0, width, height);
      };
      img.src = src;
    } else {
      // Placeholder
      ctx.fillStyle = '#f0f0f0';
      ctx.fillRect(0, 0, width, height);
      ctx.fillStyle = '#666666';
      ctx.font = '12px Arial';
      ctx.textAlign = 'center';
      ctx.fillText(alt, width / 2, height / 2);
    }
  };

  // Rendu ligne
  var renderLineElement = function renderLineElement(ctx, _ref5) {
    var _ref5$x = _ref5.x2,
      x2 = _ref5$x === void 0 ? 100 : _ref5$x,
      _ref5$y = _ref5.y2,
      y2 = _ref5$y === void 0 ? 0 : _ref5$y,
      _ref5$color = _ref5.color,
      color = _ref5$color === void 0 ? '#000000' : _ref5$color,
      _ref5$lineWidth = _ref5.lineWidth,
      lineWidth = _ref5$lineWidth === void 0 ? 1 : _ref5$lineWidth;
    ctx.strokeStyle = color;
    ctx.lineWidth = lineWidth;
    ctx.beginPath();
    ctx.moveTo(0, 0);
    ctx.lineTo(x2, y2);
    ctx.stroke();
  };

  // Rendu tableau produits
  var renderTableElement = function renderTableElement(ctx, _ref6) {
    var width = _ref6.width,
      height = _ref6.height,
      _ref6$products = _ref6.products,
      products = _ref6$products === void 0 ? [] : _ref6$products;
    var rowHeight = 20;
    var colWidth = width / 4;

    // En-têtes
    ctx.fillStyle = '#f5f5f5';
    ctx.fillRect(0, 0, width, rowHeight);
    ctx.fillStyle = '#333333';
    ctx.font = '12px Arial';
    ctx.textAlign = 'left';
    var headers = ['Produit', 'Qté', 'Prix', 'Total'];
    headers.forEach(function (header, i) {
      ctx.fillText(header, i * colWidth + 5, 15);
    });

    // Lignes de produits
    products.slice(0, Math.floor((height - rowHeight) / rowHeight)).forEach(function (product, i) {
      var y = (i + 1) * rowHeight;
      ctx.fillStyle = i % 2 === 0 ? '#ffffff' : '#f9f9f9';
      ctx.fillRect(0, y, width, rowHeight);
      ctx.fillStyle = '#333333';
      ctx.fillText(product.name || 'Produit', 5, y + 15);
      ctx.textAlign = 'center';
      ctx.fillText(product.qty || '1', colWidth + colWidth / 2, y + 15);
      ctx.fillText(product.price || '0€', 2 * colWidth + colWidth / 2, y + 15);
      ctx.fillText(product.total || '0€', 3 * colWidth + colWidth / 2, y + 15);
      ctx.textAlign = 'left';
    });

    // Bordures
    ctx.strokeStyle = '#dddddd';
    ctx.lineWidth = 1;
    for (var i = 0; i <= products.length + 1; i++) {
      ctx.beginPath();
      ctx.moveTo(0, i * rowHeight);
      ctx.lineTo(width, i * rowHeight);
      ctx.stroke();
    }
    for (var _i = 0; _i <= 4; _i++) {
      ctx.beginPath();
      ctx.moveTo(_i * colWidth, 0);
      ctx.lineTo(_i * colWidth, height);
      ctx.stroke();
    }
  };

  // Rendu élément inconnu
  var renderUnknownElement = function renderUnknownElement(ctx, _ref7) {
    var type = _ref7.type,
      width = _ref7.width,
      height = _ref7.height;
    ctx.fillStyle = '#ffebee';
    ctx.fillRect(0, 0, width, height);
    ctx.fillStyle = '#c62828';
    ctx.font = '10px Arial';
    ctx.textAlign = 'center';
    ctx.fillText("[".concat(type, "]"), width / 2, height / 2);
  };

  // Effet de rendu
  (0,react.useEffect)(function () {
    if (!canvasRef.current) return;
    measureOperation('Canvas Render', function () {
      var canvas = canvasRef.current;
      var ctx = canvas.getContext('2d');

      // Ajuster la taille du canvas
      var scaledWidth = pageDims.width * scale;
      var scaledHeight = pageDims.height * scale;
      canvas.width = scaledWidth;
      canvas.height = scaledHeight;

      // Rendre les éléments
      renderElements(ctx, elements, scale);
    });
  }, [elements, scale, renderElements, measureOperation, pageDims]);

  // Gestion du redimensionnement
  (0,react.useEffect)(function () {
    var handleResize = function handleResize() {
      if (containerRef.current && canvasRef.current) {
        var container = containerRef.current;
        var canvas = canvasRef.current;
        var optimalZoom = calculateOptimalZoom({
          width: pageDims.width,
          height: pageDims.height
        }, {
          width: container.clientWidth,
          height: container.clientHeight
        });

        // Ici on pourrait ajuster le zoom automatiquement
        console.log('Optimal zoom:', optimalZoom);
      }
    };
    window.addEventListener('resize', handleResize);
    handleResize(); // Appel initial

    return function () {
      return window.removeEventListener('resize', handleResize);
    };
  }, [pageDims]);
  return /*#__PURE__*/react.createElement("div", {
    ref: containerRef,
    className: "canvas-renderer ".concat(className)
  }, /*#__PURE__*/react.createElement("canvas", {
    ref: canvasRef,
    className: "canvas-renderer-canvas",
    style: {
      maxWidth: '100%',
      height: 'auto',
      border: '1px solid #e0e0e0',
      borderRadius: '4px',
      cursor: interactive ? 'crosshair' : 'default'
    }
  }), interactive && /*#__PURE__*/react.createElement("div", {
    className: "canvas-renderer-overlay",
    style: {
      position: 'absolute',
      top: 0,
      left: 0,
      width: '100%',
      height: '100%',
      pointerEvents: 'none'
    }
  }));
}
/* harmony default export */ const renderers_CanvasRenderer = (/*#__PURE__*/react.memo(CanvasRenderer));
;// ./resources/js/components/preview-system/renderers/ImageRenderer.jsx
function ImageRenderer_typeof(o) { "@babel/helpers - typeof"; return ImageRenderer_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, ImageRenderer_typeof(o); }
function ImageRenderer_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function ImageRenderer_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ImageRenderer_ownKeys(Object(t), !0).forEach(function (r) { ImageRenderer_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ImageRenderer_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function ImageRenderer_defineProperty(e, r, t) { return (r = ImageRenderer_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function ImageRenderer_toPropertyKey(t) { var i = ImageRenderer_toPrimitive(t, "string"); return "symbol" == ImageRenderer_typeof(i) ? i : i + ""; }
function ImageRenderer_toPrimitive(t, r) { if ("object" != ImageRenderer_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != ImageRenderer_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }


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
  return /*#__PURE__*/react.createElement("div", {
    className: "preview-element preview-image-element",
    style: containerStyle,
    "data-element-id": element.id,
    "data-element-type": element.type
  }, finalImageUrl ? /*#__PURE__*/react.createElement("img", {
    src: finalImageUrl,
    alt: alt,
    style: imageStyle,
    onError: function onError(e) {
      // Fallback vers le placeholder en cas d'erreur de chargement
      e.target.style.display = 'none';
      e.target.nextSibling.style.display = 'flex';
    }
  }) : null, /*#__PURE__*/react.createElement("div", {
    style: ImageRenderer_objectSpread(ImageRenderer_objectSpread({}, placeholderStyle), {}, {
      display: finalImageUrl ? 'none' : 'flex'
    })
  }, /*#__PURE__*/react.createElement("div", null, /*#__PURE__*/react.createElement("div", {
    style: {
      fontSize: '16px',
      marginBottom: '4px'
    }
  }, "\uD83D\uDCF7"), /*#__PURE__*/react.createElement("div", null, element.type === 'company_logo' ? 'Logo' : 'Image'))));
};
;// ./resources/js/components/preview-system/NavigationControls.jsx
function NavigationControls_slicedToArray(r, e) { return NavigationControls_arrayWithHoles(r) || NavigationControls_iterableToArrayLimit(r, e) || NavigationControls_unsupportedIterableToArray(r, e) || NavigationControls_nonIterableRest(); }
function NavigationControls_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function NavigationControls_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return NavigationControls_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? NavigationControls_arrayLikeToArray(r, a) : void 0; } }
function NavigationControls_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function NavigationControls_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function NavigationControls_arrayWithHoles(r) { if (Array.isArray(r)) return r; }




/**
 * NavigationControls - Contrôles de navigation pour l'aperçu modal
 * Inclut la navigation par page, zoom, rotation, et export
 */
function NavigationControls(_ref) {
  var _ref$className = _ref.className,
    className = _ref$className === void 0 ? '' : _ref$className;
  var _usePreviewContext = (0,PreviewContext.usePreviewContext)(),
    _usePreviewContext$st = _usePreviewContext.state,
    currentPage = _usePreviewContext$st.currentPage,
    totalPages = _usePreviewContext$st.totalPages,
    zoom = _usePreviewContext$st.zoom,
    rotation = _usePreviewContext$st.rotation,
    isFullscreen = _usePreviewContext$st.isFullscreen,
    _usePreviewContext$ac = _usePreviewContext.actions,
    setCurrentPage = _usePreviewContext$ac.setPage,
    setZoom = _usePreviewContext$ac.setZoom,
    setRotation = _usePreviewContext$ac.setRotation,
    toggleFullscreen = _usePreviewContext$ac.toggleFullscreen;
  var _usePerformanceMonito = usePerformanceMonitor('NavigationControls'),
    measureOperation = _usePerformanceMonito.measureOperation;
  var _useState = (0,react.useState)(false),
    _useState2 = NavigationControls_slicedToArray(_useState, 2),
    showZoomMenu = _useState2[0],
    setShowZoomMenu = _useState2[1];

  // Navigation par page
  var goToPage = (0,react.useCallback)(function (page) {
    var timer = measureOperation('goToPage');
    setCurrentPage(Math.max(1, Math.min(totalPages, page)));
    timer.end();
  }, [setCurrentPage, totalPages, measureOperation]);
  var goToPreviousPage = (0,react.useCallback)(function () {
    goToPage(currentPage - 1);
  }, [goToPage, currentPage]);
  var goToNextPage = (0,react.useCallback)(function () {
    goToPage(currentPage + 1);
  }, [goToPage, currentPage]);

  // Contrôles de zoom
  var zoomLevels = [25, 50, 75, 100, 125, 150, 200, 300, 400];
  var handleZoomChange = (0,react.useCallback)(function (newZoom) {
    var timer = measureOperation('zoomChange');
    setZoom(Math.max(10, Math.min(500, newZoom)));
    setShowZoomMenu(false);
    timer.end();
  }, [setZoom, measureOperation]);
  var zoomIn = (0,react.useCallback)(function () {
    var currentIndex = zoomLevels.findIndex(function (level) {
      return level >= zoom;
    });
    var nextZoom = zoomLevels[Math.min(currentIndex + 1, zoomLevels.length - 1)];
    handleZoomChange(nextZoom);
  }, [zoom, handleZoomChange, zoomLevels]);
  var zoomOut = (0,react.useCallback)(function () {
    var currentIndex = zoomLevels.findIndex(function (level) {
      return level >= zoom;
    });
    var prevZoom = zoomLevels[Math.max(currentIndex - 1, 0)];
    handleZoomChange(prevZoom);
  }, [zoom, handleZoomChange, zoomLevels]);
  var fitToWidth = (0,react.useCallback)(function () {
    handleZoomChange(100); // Logique à implémenter selon la largeur du conteneur
  }, [handleZoomChange]);
  var fitToPage = (0,react.useCallback)(function () {
    handleZoomChange(100); // Logique à implémenter selon les dimensions de la page
  }, [handleZoomChange]);

  // Rotation
  var rotateClockwise = (0,react.useCallback)(function () {
    var timer = measureOperation('rotate');
    setRotation((rotation + 90) % 360);
    timer.end();
  }, [setRotation, rotation, measureOperation]);
  var rotateCounterClockwise = (0,react.useCallback)(function () {
    var timer = measureOperation('rotate');
    setRotation((rotation - 90 + 360) % 360);
    timer.end();
  }, [setRotation, rotation, measureOperation]);

  // Export (placeholder pour l'instant)
  var handleExport = (0,react.useCallback)(function () {
    var timer = measureOperation('export');
    // TODO: Implémenter l'export selon le type (PDF, PNG, etc.)
    console.log('Export functionality to be implemented');
    timer.end();
  }, [measureOperation]);
  return /*#__PURE__*/react.createElement("div", {
    className: "navigation-controls ".concat(className)
  }, /*#__PURE__*/react.createElement("div", {
    className: "nav-main-bar"
  }, /*#__PURE__*/react.createElement("div", {
    className: "nav-page-controls"
  }, /*#__PURE__*/react.createElement("button", {
    className: "nav-btn nav-btn-previous",
    onClick: goToPreviousPage,
    disabled: currentPage <= 1,
    title: "Page pr\xE9c\xE9dente"
  }, "\u2039"), /*#__PURE__*/react.createElement("div", {
    className: "nav-page-indicator"
  }, /*#__PURE__*/react.createElement("input", {
    type: "number",
    min: "1",
    max: totalPages,
    value: currentPage,
    onChange: function onChange(e) {
      return goToPage(parseInt(e.target.value) || 1);
    },
    className: "nav-page-input"
  }), /*#__PURE__*/react.createElement("span", {
    className: "nav-page-total"
  }, " / ", totalPages)), /*#__PURE__*/react.createElement("button", {
    className: "nav-btn nav-btn-next",
    onClick: goToNextPage,
    disabled: currentPage >= totalPages,
    title: "Page suivante"
  }, "\u203A")), /*#__PURE__*/react.createElement("div", {
    className: "nav-zoom-controls"
  }, /*#__PURE__*/react.createElement("button", {
    className: "nav-btn nav-btn-zoom-out",
    onClick: zoomOut,
    disabled: zoom <= zoomLevels[0],
    title: "Zoom arri\xE8re"
  }, "\u2212"), /*#__PURE__*/react.createElement("div", {
    className: "nav-zoom-dropdown"
  }, /*#__PURE__*/react.createElement("button", {
    className: "nav-btn nav-zoom-current",
    onClick: function onClick() {
      return setShowZoomMenu(!showZoomMenu);
    },
    title: "Changer le zoom"
  }, zoom, "%"), showZoomMenu && /*#__PURE__*/react.createElement("div", {
    className: "nav-zoom-menu"
  }, zoomLevels.map(function (level) {
    return /*#__PURE__*/react.createElement("button", {
      key: level,
      className: "nav-zoom-option ".concat(zoom === level ? 'active' : ''),
      onClick: function onClick() {
        return handleZoomChange(level);
      }
    }, level, "%");
  }), /*#__PURE__*/react.createElement("div", {
    className: "nav-zoom-separator"
  }), /*#__PURE__*/react.createElement("button", {
    className: "nav-zoom-option",
    onClick: fitToWidth
  }, "Ajuster \xE0 la largeur"), /*#__PURE__*/react.createElement("button", {
    className: "nav-zoom-option",
    onClick: fitToPage
  }, "Ajuster \xE0 la page"))), /*#__PURE__*/react.createElement("button", {
    className: "nav-btn nav-btn-zoom-in",
    onClick: zoomIn,
    disabled: zoom >= zoomLevels[zoomLevels.length - 1],
    title: "Zoom avant"
  }, "+")), /*#__PURE__*/react.createElement("div", {
    className: "nav-rotation-controls"
  }, /*#__PURE__*/react.createElement("button", {
    className: "nav-btn nav-btn-rotate-ccw",
    onClick: rotateCounterClockwise,
    title: "Rotation antihoraire"
  }, "\u27F2"), /*#__PURE__*/react.createElement("span", {
    className: "nav-rotation-display"
  }, rotation, "\xB0"), /*#__PURE__*/react.createElement("button", {
    className: "nav-btn nav-btn-rotate-cw",
    onClick: rotateClockwise,
    title: "Rotation horaire"
  }, "\u27F3")), /*#__PURE__*/react.createElement("div", {
    className: "nav-action-controls"
  }, /*#__PURE__*/react.createElement("button", {
    className: "nav-btn nav-btn-export",
    onClick: handleExport,
    title: "Exporter"
  }, "\u2B07"), /*#__PURE__*/react.createElement("button", {
    className: "nav-btn nav-btn-fullscreen",
    onClick: toggleFullscreen,
    title: isFullscreen ? 'Quitter le plein écran' : 'Plein écran'
  }, isFullscreen ? '⛶' : '⛶'))), /*#__PURE__*/react.createElement("div", {
    className: "nav-status-bar"
  }, /*#__PURE__*/react.createElement("span", {
    className: "nav-status-text"
  }, "Page ", currentPage, " sur ", totalPages, " \u2022 Zoom ", zoom, "% \u2022 Rotation ", rotation, "\xB0")));
}
/* harmony default export */ const preview_system_NavigationControls = (/*#__PURE__*/react.memo(NavigationControls));
;// ./resources/js/components/preview-system/MetaboxMode.jsx
function MetaboxMode_slicedToArray(r, e) { return MetaboxMode_arrayWithHoles(r) || MetaboxMode_iterableToArrayLimit(r, e) || MetaboxMode_unsupportedIterableToArray(r, e) || MetaboxMode_nonIterableRest(); }
function MetaboxMode_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function MetaboxMode_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return MetaboxMode_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? MetaboxMode_arrayLikeToArray(r, a) : void 0; } }
function MetaboxMode_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function MetaboxMode_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function MetaboxMode_arrayWithHoles(r) { if (Array.isArray(r)) return r; }








/**
 * MetaboxMode - Mode d'aperçu intégré dans les metaboxes WooCommerce
 * Optimisé pour l'intégration dans l'admin WordPress avec contraintes d'espace
 */
function MetaboxMode(_ref) {
  var _templateElements2, _templateElements3, _previewData$elements;
  var productId = _ref.productId,
    templateData = _ref.templateData,
    _ref$className = _ref.className,
    className = _ref$className === void 0 ? '' : _ref$className,
    _ref$compact = _ref.compact,
    compact = _ref$compact === void 0 ? true : _ref$compact,
    _ref$showControls = _ref.showControls,
    showControls = _ref$showControls === void 0 ? true : _ref$showControls;
  var _usePreviewContext = (0,PreviewContext.usePreviewContext)(),
    _usePreviewContext$st = _usePreviewContext.state,
    loading = _usePreviewContext$st.loading,
    error = _usePreviewContext$st.error,
    previewData = _usePreviewContext$st.data,
    _usePreviewContext$ac = _usePreviewContext.actions,
    loadPreview = _usePreviewContext$ac.loadPreview,
    clearPreview = _usePreviewContext$ac.clearPreview;
  var _usePerformanceMonito = usePerformanceMonitor('MetaboxMode'),
    measureOperation = _usePerformanceMonito.measureOperation;
  var _useState = (0,react.useState)('preview'),
    _useState2 = MetaboxMode_slicedToArray(_useState, 2),
    activeTab = _useState2[0],
    setActiveTab = _useState2[1]; // preview, settings, export

  // Chargement des données d'aperçu au montage
  (0,react.useEffect)(function () {
    if (productId && templateData) {
      var timer = measureLoadTime('loadMetaboxPreview');
      loadPreview({
        type: 'metabox',
        productId: productId,
        templateData: templateData,
        mode: 'compact'
      });
      timer.end();
    }
    return function () {
      clearPreview();
    };
  }, [productId, templateData, loadPreview, clearPreview, measureOperation]);

  // Gestion des onglets
  var handleTabChange = (0,react.useCallback)(function (tab) {
    setActiveTab(tab);
  }, []);

  // Styles inline pour les indicateurs Phase 8
  var phase8Styles = "\n    .phase8-badge {\n      position: fixed !important;\n      top: 50px !important;\n      right: 20px !important;\n      background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%) !important;\n      color: white !important;\n      padding: 8px 16px !important;\n      border-radius: 20px !important;\n      font-size: 14px !important;\n      font-weight: bold !important;\n      box-shadow: 0 4px 12px rgba(255,107,107,0.4) !important;\n      z-index: 999999 !important;\n      border: 3px solid white !important;\n      animation: phase8-pulse 2s infinite !important;\n    }\n    @keyframes phase8-pulse {\n      0% { transform: scale(1); }\n      50% { transform: scale(1.05); }\n      100% { transform: scale(1); }\n    }\n    .phase8-metrics {\n      font-size: 12px !important;\n      color: #ff6b6b !important;\n      background: #ffeaea !important;\n      padding: 4px 8px !important;\n      border-radius: 12px !important;\n      margin-left: 8px !important;\n      border: 1px solid #ff6b6b !important;\n      font-weight: bold !important;\n    }\n    .metabox-mode {\n      border: 3px solid #ff6b6b !important;\n      position: relative !important;\n    }\n  ";

  // Injection des styles Phase 8
  (0,react.useEffect)(function () {
    console.log('=== PHASE 8: Injecting styles ===');
    var styleSheet = document.createElement('style');
    styleSheet.textContent = phase8Styles;
    document.head.appendChild(styleSheet);
    console.log('=== PHASE 8: Styles injected successfully ===');
    return function () {
      document.head.removeChild(styleSheet);
    };
  }, []);

  // Debug: Log du rendu
  console.log('=== PHASE 8: MetaboxMode rendering ===', {
    loading: loading,
    error: error,
    previewData: previewData,
    templateElements: templateElements
  });

  // Alerte de confirmation Phase 8
  (0,react.useEffect)(function () {
    console.log('=== PHASE 8 ALERT: MetaboxMode component loaded! ===');
    // Petit délai pour éviter de spammer
    var timer = setTimeout(function () {
      var _templateElements;
      console.log('🚀 PHASE 8: Nouveau système d\'aperçu actif!');
      console.log('📊 État actuel:', {
        loading: loading,
        error: error,
        templateElements: ((_templateElements = templateElements) === null || _templateElements === void 0 ? void 0 : _templateElements.length) || 0
      });
    }, 1000);
    return function () {
      return clearTimeout(timer);
    };
  }, [loading, error, templateElements]);

  // Rendu conditionnel selon l'état
  if (loading) {
    return /*#__PURE__*/react.createElement("div", {
      className: "metabox-mode loading ".concat(className)
    }, /*#__PURE__*/react.createElement("div", {
      className: "metabox-loading"
    }, /*#__PURE__*/react.createElement("div", {
      className: "metabox-spinner"
    }), /*#__PURE__*/react.createElement("span", null, "Chargement de l'aper\xE7u...")));
  }
  if (error) {
    return /*#__PURE__*/react.createElement("div", {
      className: "metabox-mode error ".concat(className)
    }, /*#__PURE__*/react.createElement("div", {
      className: "metabox-error"
    }, /*#__PURE__*/react.createElement("span", {
      className: "error-icon"
    }, "\u26A0\uFE0F"), /*#__PURE__*/react.createElement("span", {
      className: "error-message"
    }, "Erreur lors du chargement de l'aper\xE7u: ", error.message), /*#__PURE__*/react.createElement("button", {
      className: "error-retry-btn",
      onClick: function onClick() {
        return loadPreview({
          type: 'metabox',
          productId: productId,
          templateData: templateData,
          mode: 'compact'
        });
      }
    }, "R\xE9essayer")));
  }
  return /*#__PURE__*/react.createElement("div", {
    className: "metabox-mode ".concat(compact ? 'compact' : '', " ").concat(className)
  }, /*#__PURE__*/react.createElement("div", {
    style: {
      position: 'fixed',
      top: '20px',
      left: '20px',
      background: 'red',
      color: 'white',
      padding: '10px',
      borderRadius: '10px',
      fontSize: '16px',
      fontWeight: 'bold',
      zIndex: 999999,
      border: '3px solid yellow'
    }
  }, "\uD83D\uDD25 PHASE 8 ACTIVE - ", new Date().toLocaleTimeString()), /*#__PURE__*/react.createElement("div", {
    className: "phase8-badge"
  }, "\uD83D\uDE80 Phase 8 Active - ", ((_templateElements2 = templateElements) === null || _templateElements2 === void 0 ? void 0 : _templateElements2.length) || 0, " \xE9l\xE9ments"), /*#__PURE__*/react.createElement("div", {
    className: "metabox-header"
  }, /*#__PURE__*/react.createElement("div", {
    className: "metabox-tabs"
  }, /*#__PURE__*/react.createElement("button", {
    className: "metabox-tab ".concat(activeTab === 'preview' ? 'active' : ''),
    onClick: function onClick() {
      return handleTabChange('preview');
    }
  }, "Aper\xE7u"), /*#__PURE__*/react.createElement("button", {
    className: "metabox-tab ".concat(activeTab === 'settings' ? 'active' : ''),
    onClick: function onClick() {
      return handleTabChange('settings');
    }
  }, "Param\xE8tres"), /*#__PURE__*/react.createElement("button", {
    className: "metabox-tab ".concat(activeTab === 'export' ? 'active' : ''),
    onClick: function onClick() {
      return handleTabChange('export');
    }
  }, "Export")), /*#__PURE__*/react.createElement("div", {
    className: "metabox-actions"
  }, /*#__PURE__*/react.createElement("button", {
    className: "metabox-action-btn",
    title: "Actualiser"
  }, "\uD83D\uDD04"), /*#__PURE__*/react.createElement("button", {
    className: "metabox-action-btn",
    title: "Plein \xE9cran"
  }, "\u26F6"), /*#__PURE__*/react.createElement("div", {
    className: "phase8-metrics"
  }, "\u26A1 ", ((_templateElements3 = templateElements) === null || _templateElements3 === void 0 ? void 0 : _templateElements3.length) || 0, " \xE9l\xE9ments"))), /*#__PURE__*/react.createElement("div", {
    className: "metabox-content"
  }, activeTab === 'preview' && /*#__PURE__*/react.createElement("div", {
    className: "metabox-preview"
  }, /*#__PURE__*/react.createElement("div", {
    className: "metabox-preview-canvas"
  }, previewData === null || previewData === void 0 || (_previewData$elements = previewData.elements) === null || _previewData$elements === void 0 ? void 0 : _previewData$elements.map(function (element) {
    switch (element.type) {
      case 'pdf':
        return /*#__PURE__*/react.createElement(renderers_PDFRenderer, {
          key: element.id,
          element: element,
          previewData: previewData,
          mode: "metabox"
        });
      case 'canvas':
        return /*#__PURE__*/react.createElement(renderers_CanvasRenderer, {
          key: element.id,
          element: element,
          previewData: previewData,
          mode: "metabox"
        });
      case 'image':
      case 'company_logo':
        return /*#__PURE__*/react.createElement(ImageRenderer_namespaceObject["default"], {
          key: element.id,
          element: element,
          previewData: previewData,
          mode: "metabox"
        });
      default:
        return null;
    }
  })), showControls && /*#__PURE__*/react.createElement("div", {
    className: "metabox-controls"
  }, /*#__PURE__*/react.createElement(preview_system_NavigationControls, {
    compact: true
  }))), activeTab === 'settings' && /*#__PURE__*/react.createElement("div", {
    className: "metabox-settings"
  }, /*#__PURE__*/react.createElement("div", {
    className: "settings-group"
  }, /*#__PURE__*/react.createElement("h4", null, "Param\xE8tres d'aper\xE7u"), /*#__PURE__*/react.createElement("div", {
    className: "setting-item"
  }, /*#__PURE__*/react.createElement("label", null, /*#__PURE__*/react.createElement("input", {
    type: "checkbox",
    defaultChecked: true
  }), "Afficher les marges")), /*#__PURE__*/react.createElement("div", {
    className: "setting-item"
  }, /*#__PURE__*/react.createElement("label", null, /*#__PURE__*/react.createElement("input", {
    type: "checkbox",
    defaultChecked: true
  }), "Mode haute qualit\xE9")), /*#__PURE__*/react.createElement("div", {
    className: "setting-item"
  }, /*#__PURE__*/react.createElement("label", null, /*#__PURE__*/react.createElement("input", {
    type: "checkbox"
  }), "Aper\xE7u en temps r\xE9el"))), /*#__PURE__*/react.createElement("div", {
    className: "settings-group"
  }, /*#__PURE__*/react.createElement("h4", null, "Param\xE8tres du template"), /*#__PURE__*/react.createElement("div", {
    className: "setting-item"
  }, /*#__PURE__*/react.createElement("label", null, "Taille de page:"), /*#__PURE__*/react.createElement("select", {
    defaultValue: "a4"
  }, /*#__PURE__*/react.createElement("option", {
    value: "a4"
  }, "A4"), /*#__PURE__*/react.createElement("option", {
    value: "letter"
  }, "Letter"), /*#__PURE__*/react.createElement("option", {
    value: "legal"
  }, "Legal"))), /*#__PURE__*/react.createElement("div", {
    className: "setting-item"
  }, /*#__PURE__*/react.createElement("label", null, "Orientation:"), /*#__PURE__*/react.createElement("select", {
    defaultValue: "portrait"
  }, /*#__PURE__*/react.createElement("option", {
    value: "portrait"
  }, "Portrait"), /*#__PURE__*/react.createElement("option", {
    value: "landscape"
  }, "Paysage"))))), activeTab === 'export' && /*#__PURE__*/react.createElement("div", {
    className: "metabox-export"
  }, /*#__PURE__*/react.createElement("div", {
    className: "export-options"
  }, /*#__PURE__*/react.createElement("button", {
    className: "export-btn export-pdf"
  }, "\uD83D\uDCC4 Exporter en PDF"), /*#__PURE__*/react.createElement("button", {
    className: "export-btn export-png"
  }, "\uD83D\uDDBC\uFE0F Exporter en PNG"), /*#__PURE__*/react.createElement("button", {
    className: "export-btn export-jpeg"
  }, "\uD83D\uDCF7 Exporter en JPEG")), /*#__PURE__*/react.createElement("div", {
    className: "export-settings"
  }, /*#__PURE__*/react.createElement("div", {
    className: "setting-item"
  }, /*#__PURE__*/react.createElement("label", null, /*#__PURE__*/react.createElement("input", {
    type: "checkbox",
    defaultChecked: true
  }), "Inclure les marges")), /*#__PURE__*/react.createElement("div", {
    className: "setting-item"
  }, /*#__PURE__*/react.createElement("label", null, "R\xE9solution:"), /*#__PURE__*/react.createElement("select", {
    defaultValue: "high"
  }, /*#__PURE__*/react.createElement("option", {
    value: "low"
  }, "Basse (72 DPI)"), /*#__PURE__*/react.createElement("option", {
    value: "medium"
  }, "Moyenne (150 DPI)"), /*#__PURE__*/react.createElement("option", {
    value: "high"
  }, "Haute (300 DPI)")))))));
}
/* harmony default export */ const preview_system_MetaboxMode = (/*#__PURE__*/react.memo(MetaboxMode));

/***/ })

}]);