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
return (self["webpackChunkpdfBuilderReact"] = self["webpackChunkpdfBuilderReact"] || []).push([["pdf-canvas-vanilla"],{

/***/ "./assets/js/pdf-canvas-optimizer.js":
/*!*******************************************!*\
  !*** ./assets/js/pdf-canvas-optimizer.js ***!
  \*******************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   PDFCanvasPerformanceOptimizer: () => (/* binding */ PDFCanvasPerformanceOptimizer)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * PDF Canvas Performance Optimizer
 * Optimise les performances du canvas pour une meilleure expérience utilisateur
 */

var PDFCanvasPerformanceOptimizer = /*#__PURE__*/function () {
  function PDFCanvasPerformanceOptimizer(canvas) {
    _classCallCheck(this, PDFCanvasPerformanceOptimizer);
    this.canvas = canvas;
    this.metrics = {
      fps: 0,
      frameTime: 0,
      renderTime: 0,
      memoryUsage: 0,
      elementCount: 0
    };
    this.lastFrameTime = performance.now();
    this.frameCount = 0;
    this.fpsUpdateInterval = 1000; // Mise à jour FPS chaque seconde
    this.lastFpsUpdate = performance.now();

    // Paramètres d'optimisation
    this.settings = {
      targetFps: 60,
      maxElements: 1000,
      lazyLoadThreshold: 50,
      memoryLimit: 128 * 1024 * 1024,
      // 128MB
      enableProfiling: false
    };

    // Cache pour les calculs coûteux
    this.cache = new Map();

    // Gestionnaire de lazy loading
    this.lazyLoader = new LazyLoader(this);

    // Moniteur de performance
    this.performanceMonitor = new PerformanceMonitor(this);
    this.init();
  }
  return _createClass(PDFCanvasPerformanceOptimizer, [{
    key: "init",
    value: function init() {
      // Démarrer le monitoring
      this.performanceMonitor.start();

      // Configurer les optimisations
      this.setupOptimizations();
      console.log('[PDFCanvas] Performance Optimizer initialized');
    }
  }, {
    key: "setupOptimizations",
    value: function setupOptimizations() {
      // Optimisation du rendu
      this.optimizeRendering();

      // Optimisation mémoire
      this.optimizeMemory();

      // Lazy loading des éléments
      this.setupLazyLoading();
    }
  }, {
    key: "optimizeRendering",
    value: function optimizeRendering() {
      // Utiliser requestAnimationFrame pour un rendu fluide
      this.useRequestAnimationFrame();

      // Optimiser le rendu des éléments hors écran
      this.optimizeOffscreenRendering();

      // Utiliser des layers pour améliorer les performances
      this.setupLayerOptimization();
    }
  }, {
    key: "optimizeMemory",
    value: function optimizeMemory() {
      // Nettoyer le cache régulièrement
      this.setupCacheCleanup();

      // Optimiser la gestion des événements
      this.optimizeEventHandling();

      // Monitorer l'utilisation mémoire
      this.setupMemoryMonitoring();
    }
  }, {
    key: "setupLazyLoading",
    value: function setupLazyLoading() {
      // Charger les éléments visibles en priorité
      this.lazyLoader.enable();

      // Précharger les éléments proches
      this.setupPreloading();
    }

    // === MONITORING DES PERFORMANCES ===
  }, {
    key: "updateMetrics",
    value: function updateMetrics() {
      var now = performance.now();
      this.frameCount++;

      // Calculer le FPS
      if (now - this.lastFpsUpdate >= this.fpsUpdateInterval) {
        this.metrics.fps = Math.round(this.frameCount * 1000 / (now - this.lastFpsUpdate));
        this.frameCount = 0;
        this.lastFpsUpdate = now;

        // Avertir si FPS trop bas
        if (this.metrics.fps < 30) {
          console.warn("[PDFCanvas] Low FPS detected: ".concat(this.metrics.fps));
          this.handleLowPerformance();
        }
      }

      // Mesurer le temps de rendu
      this.metrics.renderTime = now - this.lastFrameTime;
      this.lastFrameTime = now;

      // Compter les éléments
      this.metrics.elementCount = this.canvas.elements.size;

      // Mesurer utilisation mémoire (estimation)
      this.metrics.memoryUsage = this.estimateMemoryUsage();
    }
  }, {
    key: "estimateMemoryUsage",
    value: function estimateMemoryUsage() {
      // Estimation simple basée sur le nombre d'éléments
      var baseMemory = 1024 * 1024; // 1MB base
      var elementMemory = this.metrics.elementCount * 2048; // ~2KB par élément
      return baseMemory + elementMemory;
    }
  }, {
    key: "handleLowPerformance",
    value: function handleLowPerformance() {
      // Réduire la qualité du rendu
      this.reduceRenderQuality();

      // Désactiver les animations coûteuses
      this.disableExpensiveAnimations();

      // Activer le lazy loading plus agressif
      this.lazyLoader.aggressiveMode = true;
    }

    // === OPTIMISATIONS DE RENDU ===
  }, {
    key: "useRequestAnimationFrame",
    value: function useRequestAnimationFrame() {
      var _this = this;
      // Remplacer les setInterval par requestAnimationFrame
      if (this.canvas.renderLoop) {
        clearInterval(this.canvas.renderLoop);
      }
      var _render = function render() {
        _this.canvas.render();
        _this.updateMetrics();
        requestAnimationFrame(_render);
      };
      requestAnimationFrame(_render);
    }
  }, {
    key: "optimizeOffscreenRendering",
    value: function optimizeOffscreenRendering() {
      var _this2 = this;
      // Ne rendre que les éléments visibles
      this.canvas.shouldRenderElement = function (element) {
        return _this2.isElementVisible(element);
      };
    }
  }, {
    key: "isElementVisible",
    value: function isElementVisible(element) {
      var canvasRect = this.canvas.canvas.getBoundingClientRect();
      var elementBounds = element.getBounds();

      // Vérifier si l'élément intersecte la zone visible
      return !(elementBounds.right < 0 || elementBounds.left > canvasRect.width || elementBounds.bottom < 0 || elementBounds.top > canvasRect.height);
    }
  }, {
    key: "setupLayerOptimization",
    value: function setupLayerOptimization() {
      // Créer des layers pour différents types d'éléments
      this.layers = {
        background: new OffscreenCanvas(1, 1),
        elements: new OffscreenCanvas(1, 1),
        overlay: new OffscreenCanvas(1, 1)
      };
      this.resizeLayers();
    }
  }, {
    key: "resizeLayers",
    value: function resizeLayers() {
      var _this$canvas$canvas = this.canvas.canvas,
        width = _this$canvas$canvas.width,
        height = _this$canvas$canvas.height;
      Object.values(this.layers).forEach(function (layer) {
        if (layer.width !== width || layer.height !== height) {
          layer.width = width;
          layer.height = height;
        }
      });
    }

    // === OPTIMISATIONS MÉMOIRE ===
  }, {
    key: "setupCacheCleanup",
    value: function setupCacheCleanup() {
      var _this3 = this;
      // Nettoyer le cache toutes les 30 secondes
      setInterval(function () {
        _this3.cleanupCache();
      }, 30000);
    }
  }, {
    key: "cleanupCache",
    value: function cleanupCache() {
      var maxAge = 5 * 60 * 1000; // 5 minutes
      var now = Date.now();
      var _iterator = _createForOfIteratorHelper(this.cache.entries()),
        _step;
      try {
        for (_iterator.s(); !(_step = _iterator.n()).done;) {
          var _ref = _step.value;
          var _ref2 = _slicedToArray(_ref, 2);
          var key = _ref2[0];
          var entry = _ref2[1];
          if (now - entry.timestamp > maxAge) {
            this.cache["delete"](key);
          }
        }

        // Forcer le garbage collector si disponible
      } catch (err) {
        _iterator.e(err);
      } finally {
        _iterator.f();
      }
      if (window.gc) {
        window.gc();
      }
    }
  }, {
    key: "optimizeEventHandling",
    value: function optimizeEventHandling() {
      // Utiliser l'event delegation
      this.setupEventDelegation();

      // Debouncer pour les événements fréquents
      this.setupEventDebouncing();
    }
  }, {
    key: "setupEventDelegation",
    value: function setupEventDelegation() {
      var _this4 = this;
      // Attacher les événements au container plutôt qu'aux éléments individuels
      var container = this.canvas.canvas.parentElement;
      if (container) {
        container.addEventListener('click', function (e) {
          _this4.handleDelegatedEvent('click', e);
        });
        container.addEventListener('mousemove', function (e) {
          _this4.handleDelegatedEvent('mousemove', e);
        });
      }
    }
  }, {
    key: "handleDelegatedEvent",
    value: function handleDelegatedEvent(type, event) {
      // Trouver l'élément cible et déclencher l'événement approprié
      var element = this.canvas.findElementAt(event.offsetX, event.offsetY);
      if (element) {
        this.canvas.eventManager.triggerElementEvent(type, element, event);
      }
    }
  }, {
    key: "setupEventDebouncing",
    value: function setupEventDebouncing() {
      var _this5 = this;
      this.debouncedEvents = new Map();

      // Debouncer pour les événements de zoom et scroll
      this.debounce('zoom', function () {
        return _this5.handleZoomEvent();
      }, 16); // ~60fps
      this.debounce('scroll', function () {
        return _this5.handleScrollEvent();
      }, 16);
    }
  }, {
    key: "debounce",
    value: function debounce(eventType, callback, delay) {
      var timeoutId;
      this.debouncedEvents.set(eventType, function () {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(callback, delay);
      });
    }
  }, {
    key: "setupMemoryMonitoring",
    value: function setupMemoryMonitoring() {
      var _this6 = this;
      // Monitorer l'utilisation mémoire
      if ('memory' in performance) {
        setInterval(function () {
          var memInfo = performance.memory;
          console.log("[PDFCanvas] Memory: ".concat(Math.round(memInfo.usedJSHeapSize / 1024 / 1024), "MB used"));
          if (memInfo.usedJSHeapSize > _this6.settings.memoryLimit) {
            console.warn('[PDFCanvas] High memory usage detected');
            _this6.handleHighMemoryUsage();
          }
        }, 10000); // Toutes les 10 secondes
      }
    }
  }, {
    key: "handleHighMemoryUsage",
    value: function handleHighMemoryUsage() {
      // Forcer le nettoyage
      this.cleanupCache();

      // Réduire le nombre d'éléments en cache
      this.reduceCacheSize();

      // Demander à l'utilisateur de sauvegarder
      this.canvas.emit('memory-warning');
    }

    // === LAZY LOADING ===
  }, {
    key: "setupPreloading",
    value: function setupPreloading() {
      var _this7 = this;
      // Précharger les éléments proches de la zone visible
      this.preloadDistance = 200; // pixels

      this.canvas.on('viewport-change', function () {
        _this7.preloadNearbyElements();
      });
    }
  }, {
    key: "preloadNearbyElements",
    value: function preloadNearbyElements() {
      var _this8 = this;
      var viewport = this.canvas.getViewportBounds();
      this.canvas.elements.forEach(function (element) {
        if (_this8.isElementNearViewport(element, viewport)) {
          _this8.lazyLoader.loadElement(element);
        }
      });
    }
  }, {
    key: "isElementNearViewport",
    value: function isElementNearViewport(element, viewport) {
      var bounds = element.getBounds();
      var distance = Math.max(Math.abs(bounds.left - viewport.left), Math.abs(bounds.right - viewport.right), Math.abs(bounds.top - viewport.top), Math.abs(bounds.bottom - viewport.bottom));
      return distance <= this.preloadDistance;
    }

    // === UTILITAIRES ===
  }, {
    key: "reduceRenderQuality",
    value: function reduceRenderQuality() {
      var _this9 = this;
      // Réduire la résolution du canvas temporairement
      var originalWidth = this.canvas.canvas.width;
      var originalHeight = this.canvas.canvas.height;
      this.canvas.canvas.width = originalWidth * 0.5;
      this.canvas.canvas.height = originalHeight * 0.5;

      // Restaurer après 5 secondes de bonnes performances
      setTimeout(function () {
        _this9.canvas.canvas.width = originalWidth;
        _this9.canvas.canvas.height = originalHeight;
      }, 5000);
    }
  }, {
    key: "disableExpensiveAnimations",
    value: function disableExpensiveAnimations() {
      // Désactiver les animations coûteuses
      this.canvas.settings.animations = false;
      this.canvas.settings.transitions = false;
    }
  }, {
    key: "reduceCacheSize",
    value: function reduceCacheSize() {
      var _this0 = this;
      // Garder seulement les éléments les plus récents
      var maxCacheSize = 50;
      if (this.cache.size > maxCacheSize) {
        var entries = Array.from(this.cache.entries());
        entries.sort(function (a, b) {
          return b[1].timestamp - a[1].timestamp;
        });

        // Supprimer les entrées les plus anciennes
        var toDelete = entries.slice(maxCacheSize);
        toDelete.forEach(function (_ref3) {
          var _ref4 = _slicedToArray(_ref3, 1),
            key = _ref4[0];
          return _this0.cache["delete"](key);
        });
      }
    }

    // === API PUBLIQUE ===
  }, {
    key: "getMetrics",
    value: function getMetrics() {
      return _objectSpread({}, this.metrics);
    }
  }, {
    key: "enableProfiling",
    value: function enableProfiling() {
      this.settings.enableProfiling = true;
      console.log('[PDFCanvas] Performance profiling enabled');
    }
  }, {
    key: "disableProfiling",
    value: function disableProfiling() {
      this.settings.enableProfiling = false;
      console.log('[PDFCanvas] Performance profiling disabled');
    }
  }, {
    key: "destroy",
    value: function destroy() {
      this.performanceMonitor.stop();
      this.lazyLoader.disable();
      this.cache.clear();
    }
  }]);
}();

// === CLASSES UTILITAIRES ===
var LazyLoader = /*#__PURE__*/function () {
  function LazyLoader(optimizer) {
    _classCallCheck(this, LazyLoader);
    this.optimizer = optimizer;
    this.enabled = false;
    this.aggressiveMode = false;
    this.loadedElements = new Set();
  }
  return _createClass(LazyLoader, [{
    key: "enable",
    value: function enable() {
      this.enabled = true;
      console.log('[LazyLoader] Enabled');
    }
  }, {
    key: "disable",
    value: function disable() {
      this.enabled = false;
      console.log('[LazyLoader] Disabled');
    }
  }, {
    key: "loadElement",
    value: function loadElement(element) {
      var _this1 = this;
      if (!this.enabled || this.loadedElements.has(element.id)) {
        return;
      }

      // Simuler le chargement lazy
      if (this.aggressiveMode) {
        // Mode agressif : charger immédiatement
        this.doLoadElement(element);
      } else {
        // Mode normal : charger avec un délai
        setTimeout(function () {
          _this1.doLoadElement(element);
        }, Math.random() * 100); // Délai aléatoire pour éviter les pics
      }
    }
  }, {
    key: "doLoadElement",
    value: function doLoadElement(element) {
      // Marquer comme chargé
      this.loadedElements.add(element.id);

      // Notifer le canvas que l'élément est prêt
      this.optimizer.canvas.emit('element-loaded', element);
    }
  }]);
}();
var PerformanceMonitor = /*#__PURE__*/function () {
  function PerformanceMonitor(optimizer) {
    _classCallCheck(this, PerformanceMonitor);
    this.optimizer = optimizer;
    this.intervalId = null;
    this.samples = [];
    this.maxSamples = 100;
  }
  return _createClass(PerformanceMonitor, [{
    key: "start",
    value: function start() {
      var _this10 = this;
      this.intervalId = setInterval(function () {
        _this10.collectSample();
      }, 1000); // Échantillon chaque seconde
    }
  }, {
    key: "stop",
    value: function stop() {
      if (this.intervalId) {
        clearInterval(this.intervalId);
        this.intervalId = null;
      }
    }
  }, {
    key: "collectSample",
    value: function collectSample() {
      var sample = {
        timestamp: Date.now(),
        fps: this.optimizer.metrics.fps,
        renderTime: this.optimizer.metrics.renderTime,
        memoryUsage: this.optimizer.metrics.memoryUsage,
        elementCount: this.optimizer.metrics.elementCount
      };
      this.samples.push(sample);

      // Garder seulement les échantillons récents
      if (this.samples.length > this.maxSamples) {
        this.samples.shift();
      }

      // Analyser les tendances
      this.analyzeTrends();
    }
  }, {
    key: "analyzeTrends",
    value: function analyzeTrends() {
      if (this.samples.length < 10) return;
      var recent = this.samples.slice(-10);
      var avgFps = recent.reduce(function (sum, s) {
        return sum + s.fps;
      }, 0) / recent.length;
      var avgRenderTime = recent.reduce(function (sum, s) {
        return sum + s.renderTime;
      }, 0) / recent.length;

      // Détecter les problèmes de performance
      if (avgFps < 30) {
        console.warn("[PerformanceMonitor] Average FPS too low: ".concat(avgFps.toFixed(1)));
      }
      if (avgRenderTime > 33) {
        // > 30fps
        console.warn("[PerformanceMonitor] Average render time too high: ".concat(avgRenderTime.toFixed(1), "ms"));
      }
    }
  }, {
    key: "getStats",
    value: function getStats() {
      if (this.samples.length === 0) return null;
      var recent = this.samples.slice(-10);
      return {
        avgFps: recent.reduce(function (sum, s) {
          return sum + s.fps;
        }, 0) / recent.length,
        avgRenderTime: recent.reduce(function (sum, s) {
          return sum + s.renderTime;
        }, 0) / recent.length,
        minFps: Math.min.apply(Math, _toConsumableArray(recent.map(function (s) {
          return s.fps;
        }))),
        maxFps: Math.max.apply(Math, _toConsumableArray(recent.map(function (s) {
          return s.fps;
        }))),
        sampleCount: this.samples.length
      };
    }
  }]);
}();

/***/ }),

/***/ "./assets/js/pdf-canvas-vanilla.js":
/*!*****************************************!*\
  !*** ./assets/js/pdf-canvas-vanilla.js ***!
  \*****************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   PDFCanvasVanilla: () => (/* binding */ PDFCanvasVanilla),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
Object(function webpackMissingModule() { var e = new Error("Cannot find module './pdf-canvas-elements.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
Object(function webpackMissingModule() { var e = new Error("Cannot find module './pdf-canvas-woocommerce.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
Object(function webpackMissingModule() { var e = new Error("Cannot find module './pdf-canvas-customization.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
Object(function webpackMissingModule() { var e = new Error("Cannot find module './pdf-canvas-renderer.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
Object(function webpackMissingModule() { var e = new Error("Cannot find module './pdf-canvas-events.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
Object(function webpackMissingModule() { var e = new Error("Cannot find module './pdf-canvas-render-utils.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
Object(function webpackMissingModule() { var e = new Error("Cannot find module './pdf-canvas-selection.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
Object(function webpackMissingModule() { var e = new Error("Cannot find module './pdf-canvas-properties.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
Object(function webpackMissingModule() { var e = new Error("Cannot find module './pdf-canvas-layers.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
Object(function webpackMissingModule() { var e = new Error("Cannot find module './pdf-canvas-export.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
/* harmony import */ var _pdf_canvas_optimizer_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./pdf-canvas-optimizer.js */ "./assets/js/pdf-canvas-optimizer.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * PDF Canvas Vanilla - Classe principale pour le système Vanilla JS
 * Remplace les composants React avec une implémentation Canvas HTML5
 * Intègre les utilitaires migrés pour la gestion des éléments
 */












var PDFCanvasVanilla = /*#__PURE__*/function () {
  function PDFCanvasVanilla(containerId) {
    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
    _classCallCheck(this, PDFCanvasVanilla);
    this.containerId = containerId;
    this.options = _objectSpread({
      width: options.width || 800,
      height: options.height || 600,
      backgroundColor: options.backgroundColor || '#ffffff',
      gridSize: options.gridSize || 20,
      showGrid: options.showGrid !== false,
      zoom: options.zoom || 1
    }, options);

    // État du canvas
    this.canvas = null;
    this.ctx = null;
    this.elements = new Map();
    this.selectedElement = null;
    this.dragState = null;
    this.isInitialized = false;

    // Gestionnaires d'événements
    this.eventListeners = new Map();

    // Services intégrés
    this.wooCommerceManager = Object(function webpackMissingModule() { var e = new Error("Cannot find module './pdf-canvas-woocommerce.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());
    this.customizationService = Object(function webpackMissingModule() { var e = new Error("Cannot find module './pdf-canvas-customization.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());

    // Gestionnaires spécialisés
    this.renderer = new Object(function webpackMissingModule() { var e = new Error("Cannot find module './pdf-canvas-renderer.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(this);
    this.eventManager = new Object(function webpackMissingModule() { var e = new Error("Cannot find module './pdf-canvas-events.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(this);
    this.selectionManager = new Object(function webpackMissingModule() { var e = new Error("Cannot find module './pdf-canvas-selection.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(this);
    this.propertiesManager = new Object(function webpackMissingModule() { var e = new Error("Cannot find module './pdf-canvas-properties.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(this);
    this.layersManager = new Object(function webpackMissingModule() { var e = new Error("Cannot find module './pdf-canvas-layers.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(this);
    this.exportManager = new Object(function webpackMissingModule() { var e = new Error("Cannot find module './pdf-canvas-export.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(this);
    this.performanceOptimizer = new _pdf_canvas_optimizer_js__WEBPACK_IMPORTED_MODULE_1__.PDFCanvasPerformanceOptimizer(this);

    // État d'interaction
    this.mode = 'select'; // select, draw, text, etc.
    this.tool = null;

    // Historique pour undo/redo
    this.history = [];
    this.historyIndex = -1;
  }

  /**
   * Initialise le canvas et les gestionnaires d'événements
   */
  return _createClass(PDFCanvasVanilla, [{
    key: "init",
    value: (function () {
      var _init = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
        var _t;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.p = _context.n) {
            case 0:
              _context.p = 0;
              // Créer le canvas
              this.createCanvas();

              // Configurer le contexte
              this.setupContext();

              // Attacher les gestionnaires d'événements
              this.attachEventListeners();

              // Charger les données WooCommerce si nécessaire
              _context.n = 1;
              return this.loadInitialData();
            case 1:
              // Premier rendu
              this.render();
              this.isInitialized = true;
              console.log('PDFCanvasVanilla initialized successfully');
              _context.n = 3;
              break;
            case 2:
              _context.p = 2;
              _t = _context.v;
              console.error('Failed to initialize PDFCanvasVanilla:', _t);
              throw _t;
            case 3:
              return _context.a(2);
          }
        }, _callee, this, [[0, 2]]);
      }));
      function init() {
        return _init.apply(this, arguments);
      }
      return init;
    }()
    /**
     * Crée l'élément canvas dans le conteneur
     */
    )
  }, {
    key: "createCanvas",
    value: function createCanvas() {
      var container = document.getElementById(this.containerId);
      if (!container) {
        throw new Error("Container with id \"".concat(this.containerId, "\" not found"));
      }

      // Vider le conteneur
      container.innerHTML = '';

      // Créer le canvas
      this.canvas = document.createElement('canvas');
      this.canvas.width = this.options.width;
      this.canvas.height = this.options.height;
      this.canvas.style.border = '1px solid #ddd';
      this.canvas.style.cursor = 'default';
      this.canvas.style.backgroundColor = this.options.backgroundColor;

      // Ajouter au conteneur
      container.appendChild(this.canvas);

      // Obtenir le contexte
      this.ctx = this.canvas.getContext('2d');
      if (!this.ctx) {
        throw new Error('Failed to get 2D context from canvas');
      }
    }

    /**
     * Configure le contexte de rendu
     */
  }, {
    key: "setupContext",
    value: function setupContext() {
      // Configuration de base
      this.ctx.imageSmoothingEnabled = true;
      this.ctx.imageSmoothingQuality = 'high';

      // Configuration du texte
      this.ctx.textBaseline = 'top';
      this.ctx.font = '14px Arial, sans-serif';
    }

    /**
     * Attache les gestionnaires d'événements DOM
     */
  }, {
    key: "attachEventListeners",
    value: function attachEventListeners() {
      // Gestionnaires de souris
      this.canvas.addEventListener('mousedown', this.handleMouseDown.bind(this));
      this.canvas.addEventListener('mousemove', this.handleMouseMove.bind(this));
      this.canvas.addEventListener('mouseup', this.handleMouseUp.bind(this));
      this.canvas.addEventListener('wheel', this.handleWheel.bind(this));

      // Gestionnaires de clavier
      document.addEventListener('keydown', this.handleKeyDown.bind(this));
      document.addEventListener('keyup', this.handleKeyUp.bind(this));

      // Gestionnaire de redimensionnement
      window.addEventListener('resize', this.handleResize.bind(this));
    }

    /**
     * Charge les données initiales
     */
  }, {
    key: "loadInitialData",
    value: (function () {
      var _loadInitialData = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2() {
        return _regenerator().w(function (_context2) {
          while (1) switch (_context2.n) {
            case 0:
              // Charger les données WooCommerce en mode test
              this.wooCommerceManager.setTestMode(true);
              _context2.n = 1;
              return this.wooCommerceManager.loadWooCommerceData();
            case 1:
              return _context2.a(2);
          }
        }, _callee2, this);
      }));
      function loadInitialData() {
        return _loadInitialData.apply(this, arguments);
      }
      return loadInitialData;
    }()
    /**
     * Gestionnaire d'événement mouse down
     */
    )
  }, {
    key: "handleMouseDown",
    value: function handleMouseDown(event) {
      var point = this.getMousePosition(event);
      switch (this.mode) {
        case 'select':
          this.handleSelectMode(point);
          break;
        case 'draw':
          this.handleDrawMode(point);
          break;
        case 'text':
          this.handleTextMode(point);
          break;
      }
    }

    /**
     * Gestionnaire d'événement mouse move
     */
  }, {
    key: "handleMouseMove",
    value: function handleMouseMove(event) {
      var point = this.getMousePosition(event);
      if (this.dragState) {
        this.handleDrag(point);
      } else {
        this.handleHover(point);
      }
    }

    /**
     * Gestionnaire d'événement mouse up
     */
  }, {
    key: "handleMouseUp",
    value: function handleMouseUp(event) {
      if (this.dragState) {
        this.endDrag();
      }
    }

    /**
     * Gestionnaire de roulette de souris (zoom)
     */
  }, {
    key: "handleWheel",
    value: function handleWheel(event) {
      event.preventDefault();
      var delta = event.deltaY > 0 ? 0.9 : 1.1;
      this.setZoom(this.options.zoom * delta);
    }

    /**
     * Gestionnaire de touches clavier
     */
  }, {
    key: "handleKeyDown",
    value: function handleKeyDown(event) {
      switch (event.key) {
        case 'Delete':
        case 'Backspace':
          if (this.selectedElement) {
            this.deleteElement(this.selectedElement.id);
          }
          break;
        case 'Escape':
          this.deselectElement();
          break;
        case 'z':
          if (event.ctrlKey || event.metaKey) {
            event.preventDefault();
            this.undo();
          }
          break;
        case 'y':
          if (event.ctrlKey || event.metaKey) {
            event.preventDefault();
            this.redo();
          }
          break;
      }
    }

    /**
     * Gestionnaire de relâchement de touches
     */
  }, {
    key: "handleKeyUp",
    value: function handleKeyUp(event) {
      // Gérer les relâchements si nécessaire
    }

    /**
     * Gestionnaire de redimensionnement de fenêtre
     */
  }, {
    key: "handleResize",
    value: function handleResize() {
      // Ajuster la taille du canvas si nécessaire
      this.render();
    }

    /**
     * Obtient la position de la souris relative au canvas
     */
  }, {
    key: "getMousePosition",
    value: function getMousePosition(event) {
      var rect = this.canvas.getBoundingClientRect();
      return {
        x: (event.clientX - rect.left) / this.options.zoom,
        y: (event.clientY - rect.top) / this.options.zoom
      };
    }

    /**
     * Gère le mode sélection
     */
  }, {
    key: "handleSelectMode",
    value: function handleSelectMode(point) {
      var element = this.getElementAtPoint(point);
      if (element) {
        this.selectElement(element.id);
        this.startDrag(point);
      } else {
        this.deselectElement();
      }
    }

    /**
     * Gère le mode dessin
     */
  }, {
    key: "handleDrawMode",
    value: function handleDrawMode(point) {
      // Implémentation du mode dessin
      console.log('Draw mode at:', point);
    }

    /**
     * Gère le mode texte
     */
  }, {
    key: "handleTextMode",
    value: function handleTextMode(point) {
      // Implémentation du mode texte
      console.log('Text mode at:', point);
    }

    /**
     * Démarre un glisser-déposer
     */
  }, {
    key: "startDrag",
    value: function startDrag(point) {
      if (!this.selectedElement) return;
      this.dragState = {
        startPoint: point,
        elementStartPos: {
          x: this.selectedElement.properties.x,
          y: this.selectedElement.properties.y
        }
      };
    }

    /**
     * Gère le glisser-déposer
     */
  }, {
    key: "handleDrag",
    value: function handleDrag(point) {
      if (!this.dragState || !this.selectedElement) return;
      var deltaX = point.x - this.dragState.startPoint.x;
      var deltaY = point.y - this.dragState.startPoint.y;
      this.updateElementProperty(this.selectedElement.id, 'x', this.dragState.elementStartPos.x + deltaX);
      this.updateElementProperty(this.selectedElement.id, 'y', this.dragState.elementStartPos.y + deltaY);
      this.render();
    }

    /**
     * Termine le glisser-déposer
     */
  }, {
    key: "endDrag",
    value: function endDrag() {
      this.dragState = null;
      this.saveToHistory();
    }

    /**
     * Gère le survol des éléments
     */
  }, {
    key: "handleHover",
    value: function handleHover(point) {
      var element = this.getElementAtPoint(point);
      this.canvas.style.cursor = element ? 'move' : 'default';
    }

    /**
     * Sélectionne un élément
     */
  }, {
    key: "selectElement",
    value: function selectElement(elementId) {
      this.selectedElement = this.elements.get(elementId);
      this.render();
    }

    /**
     * Désélectionne l'élément actuel
     */
  }, {
    key: "deselectElement",
    value: function deselectElement() {
      this.selectedElement = null;
      this.render();
    }

    /**
     * Obtient l'élément à une position donnée
     */
  }, {
    key: "getElementAtPoint",
    value: function getElementAtPoint(point) {
      // Parcourir les éléments dans l'ordre inverse (dernier ajouté = premier cliqué)
      var elementsArray = Array.from(this.elements.values()).reverse();
      var _iterator = _createForOfIteratorHelper(elementsArray),
        _step;
      try {
        for (_iterator.s(); !(_step = _iterator.n()).done;) {
          var element = _step.value;
          if (this.isPointInElement(point, element)) {
            return element;
          }
        }
      } catch (err) {
        _iterator.e(err);
      } finally {
        _iterator.f();
      }
      return null;
    }

    /**
     * Vérifie si un point est dans un élément
     */
  }, {
    key: "isPointInElement",
    value: function isPointInElement(point, element) {
      var props = element.properties;
      return point.x >= props.x && point.x <= props.x + props.width && point.y >= props.y && point.y <= props.y + props.height;
    }

    /**
     * Ajoute un élément au canvas
     */
  }, {
    key: "addElement",
    value: function addElement(type) {
      var properties = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var elementId = "element_".concat(Date.now(), "_").concat(Math.random().toString(36).substr(2, 9));

      // Obtenir les propriétés par défaut
      var defaultProps = this.customizationService.getDefaultProperties(type);

      // Fusionner avec les propriétés fournies
      var elementProperties = _objectSpread(_objectSpread({}, defaultProps), properties);

      // Valider les propriétés
      var validatedProps = {};
      for (var _i = 0, _Object$entries = Object.entries(elementProperties); _i < _Object$entries.length; _i++) {
        var _ref = _Object$entries[_i];
        var _ref2 = _slicedToArray(_ref, 2);
        var key = _ref2[0];
        var value = _ref2[1];
        validatedProps[key] = this.customizationService.validateProperty(key, value);
      }
      var element = {
        id: elementId,
        type: type,
        properties: validatedProps,
        createdAt: Date.now(),
        updatedAt: Date.now()
      };
      this.elements.set(elementId, element);
      this.saveToHistory();
      this.render();
      return elementId;
    }

    /**
     * Met à jour une propriété d'élément
     */
  }, {
    key: "updateElementProperty",
    value: function updateElementProperty(elementId, property, value) {
      var element = this.elements.get(elementId);
      if (!element) return false;

      // Valider la propriété
      var validatedValue = this.customizationService.validateProperty(property, value);

      // Vérifier les restrictions
      if (!Object(function webpackMissingModule() { var e = new Error("Cannot find module './pdf-canvas-elements.js'"); e.code = 'MODULE_NOT_FOUND'; throw e; }())(element.type, property)) {
        console.warn("Property \"".concat(property, "\" not allowed for element type \"").concat(element.type, "\""));
        return false;
      }
      element.properties[property] = validatedValue;
      element.updatedAt = Date.now();
      this.render();
      return true;
    }

    /**
     * Supprime un élément
     */
  }, {
    key: "deleteElement",
    value: function deleteElement(elementId) {
      if (this.elements["delete"](elementId)) {
        if (this.selectedElement && this.selectedElement.id === elementId) {
          this.selectedElement = null;
        }
        this.saveToHistory();
        this.render();
        return true;
      }
      return false;
    }

    /**
     * Définit le niveau de zoom
     */
  }, {
    key: "setZoom",
    value: function setZoom(zoom) {
      this.options.zoom = Math.max(0.1, Math.min(5, zoom));
      this.canvas.style.transform = "scale(".concat(this.options.zoom, ")");
      this.canvas.style.transformOrigin = 'top left';
    }

    /**
     * Définit le mode d'interaction
     */
  }, {
    key: "setMode",
    value: function setMode(mode) {
      this.mode = mode;
      this.canvas.style.cursor = this.getCursorForMode(mode);
    }

    /**
     * Obtient le curseur approprié pour un mode
     */
  }, {
    key: "getCursorForMode",
    value: function getCursorForMode(mode) {
      var cursors = {
        select: 'default',
        draw: 'crosshair',
        text: 'text',
        move: 'move'
      };
      return cursors[mode] || 'default';
    }

    /**
     * Rend tous les éléments sur le canvas
     */
  }, {
    key: "render",
    value: function render() {
      // Utiliser l'optimiseur de performance si disponible
      if (this.performanceOptimizer) {
        this.performanceOptimizer.optimizeRendering();
        return;
      }

      // Effacer le canvas
      this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

      // Dessiner la grille si activée
      if (this.options.showGrid) {
        this.drawGrid();
      }

      // Dessiner tous les éléments
      var _iterator2 = _createForOfIteratorHelper(this.elements.values()),
        _step2;
      try {
        for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
          var element = _step2.value;
          this.renderElement(element);
        }

        // Dessiner les poignées de sélection
      } catch (err) {
        _iterator2.e(err);
      } finally {
        _iterator2.f();
      }
      if (this.selectedElement) {
        this.drawSelectionHandles(this.selectedElement);
      }
    }

    /**
     * Dessine la grille d'arrière-plan
     */
  }, {
    key: "drawGrid",
    value: function drawGrid() {
      var gridSize = this.options.gridSize;
      this.ctx.strokeStyle = '#f0f0f0';
      this.ctx.lineWidth = 1;

      // Lignes verticales
      for (var x = 0; x <= this.canvas.width; x += gridSize) {
        this.ctx.beginPath();
        this.ctx.moveTo(x, 0);
        this.ctx.lineTo(x, this.canvas.height);
        this.ctx.stroke();
      }

      // Lignes horizontales
      for (var y = 0; y <= this.canvas.height; y += gridSize) {
        this.ctx.beginPath();
        this.ctx.moveTo(0, y);
        this.ctx.lineTo(this.canvas.width, y);
        this.ctx.stroke();
      }
    }

    /**
     * Rend un élément spécifique
     */
  }, {
    key: "renderElement",
    value: function renderElement(element) {
      var props = element.properties;

      // Sauvegarder le contexte
      this.ctx.save();

      // Appliquer les transformations
      this.ctx.translate(props.x + props.width / 2, props.y + props.height / 2);
      if (props.rotation) {
        this.ctx.rotate(props.rotation * Math.PI / 180);
      }
      this.ctx.translate(-props.width / 2, -props.height / 2);

      // Appliquer l'opacité
      if (props.opacity !== undefined && props.opacity < 100) {
        this.ctx.globalAlpha = props.opacity / 100;
      }

      // Rendu selon le type d'élément
      switch (element.type) {
        case 'text':
          this.renderTextElement(element);
          break;
        case 'rectangle':
          this.renderRectangleElement(element);
          break;
        case 'image':
          this.renderImageElement(element);
          break;
        default:
          this.renderGenericElement(element);
          break;
      }

      // Restaurer le contexte
      this.ctx.restore();
    }

    /**
     * Rend un élément texte
     */
  }, {
    key: "renderTextElement",
    value: function renderTextElement(element) {
      var props = element.properties;

      // Configuration du texte
      this.ctx.font = "".concat(props.fontWeight || 'normal', " ").concat(props.fontSize || 14, "px ").concat(props.fontFamily || 'Arial, sans-serif');
      this.ctx.fillStyle = props.color || '#000000';
      this.ctx.textAlign = props.textAlign || 'left';

      // Position de départ
      var x = 0;
      var y = 0;

      // Ajuster selon l'alignement
      if (props.textAlign === 'center') {
        x = props.width / 2;
      } else if (props.textAlign === 'right') {
        x = props.width;
      }

      // Rendu du texte
      var text = props.text || 'Texte';
      var lines = text.split('\n');
      for (var i = 0; i < lines.length; i++) {
        this.ctx.fillText(lines[i], x, y + i * (props.fontSize || 14) * 1.2);
      }
    }

    /**
     * Rend un élément rectangle
     */
  }, {
    key: "renderRectangleElement",
    value: function renderRectangleElement(element) {
      var props = element.properties;

      // Fond
      if (props.backgroundColor && props.backgroundColor !== 'transparent') {
        this.ctx.fillStyle = props.backgroundColor;
        this.roundRect(0, 0, props.width, props.height, props.borderRadius || 0);
        this.ctx.fill();
      }

      // Bordure
      if (props.borderWidth && props.borderWidth > 0) {
        this.ctx.strokeStyle = props.borderColor || '#000000';
        this.ctx.lineWidth = props.borderWidth;
        this.roundRect(0, 0, props.width, props.height, props.borderRadius || 0);
        this.ctx.stroke();
      }
    }

    /**
     * Rend un élément image
     */
  }, {
    key: "renderImageElement",
    value: function renderImageElement(element) {
      var _this = this;
      var props = element.properties;
      if (props.src) {
        var img = new Image();
        img.onload = function () {
          // Calculer les dimensions pour le fit
          var drawWidth = props.width;
          var drawHeight = props.height;
          var drawX = 0;
          var drawY = 0;
          if (props.objectFit === 'cover') {
            var scale = Math.max(props.width / img.width, props.height / img.height);
            drawWidth = img.width * scale;
            drawHeight = img.height * scale;
            drawX = (props.width - drawWidth) / 2;
            drawY = (props.height - drawHeight) / 2;
          }
          _this.ctx.drawImage(img, drawX, drawY, drawWidth, drawHeight);
          _this.render(); // Re-rendre après le chargement de l'image
        };
        img.src = props.src;
      }
    }

    /**
     * Rend un élément générique
     */
  }, {
    key: "renderGenericElement",
    value: function renderGenericElement(element) {
      // Rendu par défaut pour les éléments non reconnus
      this.renderRectangleElement(element);
    }

    /**
     * Dessine un rectangle avec des coins arrondis
     */
  }, {
    key: "roundRect",
    value: function roundRect(x, y, width, height, radius) {
      if (radius === 0) {
        this.ctx.rect(x, y, width, height);
        return;
      }
      this.ctx.beginPath();
      this.ctx.moveTo(x + radius, y);
      this.ctx.lineTo(x + width - radius, y);
      this.ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
      this.ctx.lineTo(x + width, y + height - radius);
      this.ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
      this.ctx.lineTo(x + radius, y + height);
      this.ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
      this.ctx.lineTo(x, y + radius);
      this.ctx.quadraticCurveTo(x, y, x + radius, y);
      this.ctx.closePath();
    }

    /**
     * Dessine les poignées de sélection
     */
  }, {
    key: "drawSelectionHandles",
    value: function drawSelectionHandles(element) {
      var _this2 = this;
      var props = element.properties;
      var handleSize = 8;
      this.ctx.strokeStyle = '#007bff';
      this.ctx.lineWidth = 2;
      this.ctx.fillStyle = '#ffffff';

      // Poignées de redimensionnement
      var handles = [{
        x: props.x,
        y: props.y
      },
      // Haut-gauche
      {
        x: props.x + props.width,
        y: props.y
      },
      // Haut-droite
      {
        x: props.x + props.width,
        y: props.y + props.height
      },
      // Bas-droite
      {
        x: props.x,
        y: props.y + props.height
      } // Bas-gauche
      ];
      handles.forEach(function (handle) {
        _this2.ctx.fillRect(handle.x - handleSize / 2, handle.y - handleSize / 2, handleSize, handleSize);
        _this2.ctx.strokeRect(handle.x - handleSize / 2, handle.y - handleSize / 2, handleSize, handleSize);
      });

      // Rectangle de sélection
      this.ctx.strokeStyle = '#007bff';
      this.ctx.setLineDash([5, 5]);
      this.ctx.strokeRect(props.x, props.y, props.width, props.height);
      this.ctx.setLineDash([]);
    }

    /**
     * Sauvegarde l'état actuel dans l'historique
     */
  }, {
    key: "saveToHistory",
    value: function saveToHistory() {
      var state = {
        elements: new Map(this.elements),
        selectedElement: this.selectedElement ? this.selectedElement.id : null
      };

      // Supprimer les états futurs si on est au milieu de l'historique
      this.history = this.history.slice(0, this.historyIndex + 1);

      // Ajouter le nouvel état
      this.history.push(state);
      this.historyIndex++;

      // Limiter la taille de l'historique
      if (this.history.length > 50) {
        this.history.shift();
        this.historyIndex--;
      }
    }

    /**
     * Annule la dernière action
     */
  }, {
    key: "undo",
    value: function undo() {
      if (this.historyIndex > 0) {
        this.historyIndex--;
        this.restoreFromHistory();
      }
    }

    /**
     * Rétablit la dernière action annulée
     */
  }, {
    key: "redo",
    value: function redo() {
      if (this.historyIndex < this.history.length - 1) {
        this.historyIndex++;
        this.restoreFromHistory();
      }
    }

    /**
     * Restaure l'état depuis l'historique
     */
  }, {
    key: "restoreFromHistory",
    value: function restoreFromHistory() {
      var state = this.history[this.historyIndex];
      this.elements = new Map(state.elements);
      this.selectedElement = state.selectedElement ? this.elements.get(state.selectedElement) : null;
      this.render();
    }

    /**
     * Exporte le canvas en image
     */
  }, {
    key: "exportToImage",
    value: function exportToImage() {
      var format = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'png';
      var quality = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 1;
      return this.canvas.toDataURL("image/".concat(format), quality);
    }

    /**
     * Obtient les données JSON du canvas
     */
  }, {
    key: "exportToJSON",
    value: function exportToJSON() {
      return {
        version: '1.0',
        canvas: {
          width: this.options.width,
          height: this.options.height,
          backgroundColor: this.options.backgroundColor
        },
        elements: Array.from(this.elements.values()).map(function (element) {
          return {
            id: element.id,
            type: element.type,
            properties: element.properties,
            createdAt: element.createdAt,
            updatedAt: element.updatedAt
          };
        }),
        metadata: {
          exportedAt: Date.now(),
          elementCount: this.elements.size
        }
      };
    }

    /**
     * Importe des données JSON dans le canvas
     */
  }, {
    key: "importFromJSON",
    value: function importFromJSON(data) {
      var _this3 = this;
      try {
        this.elements.clear();
        data.elements.forEach(function (elementData) {
          var element = _objectSpread(_objectSpread({}, elementData), {}, {
            properties: _objectSpread({}, elementData.properties)
          });
          _this3.elements.set(element.id, element);
        });
        this.render();
        this.saveToHistory();
        return true;
      } catch (error) {
        console.error('Failed to import JSON:', error);
        return false;
      }
    }

    /**
     * Nettoie les ressources
     */
  }, {
    key: "dispose",
    value: function dispose() {
      // Supprimer les gestionnaires d'événements
      if (this.canvas) {
        this.canvas.removeEventListener('mousedown', this.handleMouseDown);
        this.canvas.removeEventListener('mousemove', this.handleMouseMove);
        this.canvas.removeEventListener('mouseup', this.handleMouseUp);
        this.canvas.removeEventListener('wheel', this.handleWheel);
      }
      document.removeEventListener('keydown', this.handleKeyDown);
      document.removeEventListener('keyup', this.handleKeyUp);
      window.removeEventListener('resize', this.handleResize);

      // Nettoyer les références
      this.elements.clear();
      this.selectedElement = null;
      this.dragState = null;
      this.history = [];
      this.historyIndex = -1;
      console.log('PDFCanvasVanilla disposed');
    }

    /**
     * Obtient les statistiques du canvas
     */
  }, {
    key: "getStats",
    value: function getStats() {
      var baseStats = {
        totalElements: this.elements.size,
        selectedElement: this.selectedElement ? this.selectedElement.id : null,
        canvasSize: {
          width: this.canvas.width,
          height: this.canvas.height
        },
        zoom: this.options.zoom,
        mode: this.mode
      };

      // Ajouter les statistiques de performance si disponibles
      if (this.performanceOptimizer) {
        return _objectSpread(_objectSpread({}, baseStats), {}, {
          performance: this.performanceOptimizer.getPerformanceStats()
        });
      }
      return baseStats;
    }
  }]);
}();

// Export de la classe
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (PDFCanvasVanilla);

// Fonction d'initialisation globale pour WordPress
window.pdfBuilderInitVanilla = function (containerId) {
  var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  console.log('🚀 Initialisation Vanilla JS PDF Builder...');
  try {
    // Créer l'instance principale
    var pdfCanvas = new PDFCanvasVanilla(containerId, options);

    // Attendre que le DOM soit prêt
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function () {
        pdfCanvas.init();
      });
    } else {
      pdfCanvas.init();
    }

    // Exposer l'instance globalement pour le débogage
    window.pdfBuilderInstance = pdfCanvas;
    console.log('✅ PDF Builder Vanilla initialisé avec succès');
    return pdfCanvas;
  } catch (error) {
    console.error('❌ Erreur lors de l\'initialisation Vanilla:', error);
    throw error;
  }
};

// Alias pour la compatibilité
window.pdfBuilderPro = {
  init: window.pdfBuilderInitVanilla
};

/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ var __webpack_exports__ = (__webpack_exec__("./assets/js/pdf-canvas-vanilla.js"));
/******/ return __webpack_exports__;
/******/ }
]);
});
//# sourceMappingURL=pdf-canvas-vanilla.bundle.js.map