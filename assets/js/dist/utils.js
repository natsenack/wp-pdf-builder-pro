"use strict";
(self["webpackChunkwp_pdf_builder_pro"] = self["webpackChunkwp_pdf_builder_pro"] || []).push([[738],{

/***/ 149:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   useCanvasState: () => (/* reexport safe */ _useCanvasState__WEBPACK_IMPORTED_MODULE_0__.v),
/* harmony export */   useClipboard: () => (/* reexport safe */ _useClipboard__WEBPACK_IMPORTED_MODULE_3__.i),
/* harmony export */   useContextMenu: () => (/* reexport safe */ _useContextMenu__WEBPACK_IMPORTED_MODULE_5__.E),
/* harmony export */   useDragAndDrop: () => (/* reexport safe */ _useDragAndDrop__WEBPACK_IMPORTED_MODULE_7__.g),
/* harmony export */   useHistory: () => (/* reexport safe */ _useHistory_js__WEBPACK_IMPORTED_MODULE_1__.W),
/* harmony export */   useKeyboardShortcuts: () => (/* reexport safe */ _useKeyboardShortcuts__WEBPACK_IMPORTED_MODULE_6__.K),
/* harmony export */   useResize: () => (/* reexport safe */ _useResize__WEBPACK_IMPORTED_MODULE_8__.s),
/* harmony export */   useRotation: () => (/* reexport safe */ _useRotation_js__WEBPACK_IMPORTED_MODULE_9__.o),
/* harmony export */   useSelection: () => (/* reexport safe */ _useSelection__WEBPACK_IMPORTED_MODULE_2__.C),
/* harmony export */   useZoom: () => (/* reexport safe */ _useZoom__WEBPACK_IMPORTED_MODULE_4__.f)
/* harmony export */ });
/* harmony import */ var _useCanvasState__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(169);
/* harmony import */ var _useHistory_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(232);
/* harmony import */ var _useSelection__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(698);
/* harmony import */ var _useClipboard__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(430);
/* harmony import */ var _useZoom__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(553);
/* harmony import */ var _useContextMenu__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(364);
/* harmony import */ var _useKeyboardShortcuts__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(960);
/* harmony import */ var _useDragAndDrop__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(174);
/* harmony import */ var _useResize__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(630);
/* harmony import */ var _useRotation_js__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(920);











/***/ }),

/***/ 169:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   v: () => (/* binding */ useCanvasState)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(540);
/* harmony import */ var _useHistory__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(232);
/* harmony import */ var _useSelection__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(698);
/* harmony import */ var _useClipboard__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(430);
/* harmony import */ var _useZoom__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(553);
/* harmony import */ var _useContextMenu__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(364);
/* harmony import */ var _useDragAndDrop__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(174);
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }









// Hook utilitaire pour synchroniser les refs
var useLatest = function useLatest(value) {
  var ref = (0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(value);
  ref.current = value;
  return ref;
};

// Fallback notification system in case Toastr is not available
if (typeof window !== 'undefined' && typeof window.toastr === 'undefined') {
  // Simple notification system
  var createNotification = function createNotification(type, title, message) {
    var notification = document.createElement('div');
    notification.style.cssText = "\n      position: fixed;\n      top: 20px;\n      right: 20px;\n      z-index: 100;\n      padding: 15px 20px;\n      margin-bottom: 10px;\n      border-radius: 5px;\n      color: white;\n      font-family: Arial, sans-serif;\n      font-size: 14px;\n      box-shadow: 0 4px 12px rgba(0,0,0,0.3);\n      max-width: 300px;\n      opacity: 0;\n      transform: translateX(100%);\n      transition: all 0.3s ease;\n    ";

    // Set colors based on type
    switch (type) {
      case 'success':
        notification.style.backgroundColor = '#51A351';
        break;
      case 'error':
        notification.style.backgroundColor = '#BD362F';
        break;
      case 'warning':
        notification.style.backgroundColor = '#F89406';
        break;
      case 'info':
      default:
        notification.style.backgroundColor = '#2F96B4';
        break;
    }

    // Create content
    var titleElement = title ? "<strong>".concat(title, "</strong><br>") : '';
    notification.innerHTML = "".concat(titleElement).concat(message);

    // Add close button
    var closeButton = document.createElement('button');
    closeButton.innerHTML = '×';
    closeButton.style.cssText = "\n      position: absolute;\n      top: 5px;\n      right: 10px;\n      background: none;\n      border: none;\n      color: white;\n      font-size: 20px;\n      cursor: pointer;\n      opacity: 0.8;\n    ";
    closeButton.onclick = function () {
      return removeNotification(notification);
    };
    notification.appendChild(closeButton);

    // Add to page
    document.body.appendChild(notification);

    // Animate in
    setTimeout(function () {
      notification.style.opacity = '1';
      notification.style.transform = 'translateX(0)';
    }, 10);

    // Auto remove after 5 seconds
    setTimeout(function () {
      return removeNotification(notification);
    }, 5000);
    function removeNotification(el) {
      el.style.opacity = '0';
      el.style.transform = 'translateX(100%)';
      setTimeout(function () {
        if (el.parentNode) {
          el.parentNode.removeChild(el);
        }
      }, 300);
    }
  };

  // Create fallback toastr object
  window.toastr = {
    success: function success(message, title) {
      createNotification('success', title, message);
    },
    error: function error(message, title) {
      createNotification('error', title, message);
    },
    warning: function warning(message, title) {
      createNotification('warning', title, message);
    },
    info: function info(message, title) {
      createNotification('info', title, message);
    },
    options: {} // Placeholder for options
  };
}
var useCanvasState = function useCanvasState(_ref) {
  var _globalSettings$multi;
  var _ref$initialElements = _ref.initialElements,
    initialElements = _ref$initialElements === void 0 ? [] : _ref$initialElements,
    _ref$templateId = _ref.templateId,
    templateId = _ref$templateId === void 0 ? null : _ref$templateId,
    _ref$canvasWidth = _ref.canvasWidth,
    canvasWidth = _ref$canvasWidth === void 0 ? 595 : _ref$canvasWidth,
    _ref$canvasHeight = _ref.canvasHeight,
    canvasHeight = _ref$canvasHeight === void 0 ? 842 : _ref$canvasHeight,
    _ref$globalSettings = _ref.globalSettings,
    globalSettings = _ref$globalSettings === void 0 ? null : _ref$globalSettings;
  // Logs conditionnels selon l'environnement
  var isDevelopment = "production" === 'development';
  var _useState = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(initialElements),
    _useState2 = _slicedToArray(_useState, 2),
    elements = _useState2[0],
    setElements = _useState2[1];
  var _useState3 = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(1),
    _useState4 = _slicedToArray(_useState3, 2),
    nextId = _useState4[0],
    setNextId = _useState4[1];
  var _useState5 = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(false),
    _useState6 = _slicedToArray(_useState5, 2),
    isSaving = _useState6[0],
    setIsSaving = _useState6[1];

  // États de chargement granulaires pour meilleure UX
  var _useState7 = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)({
      saving: false,
      loading: false,
      duplicating: false,
      deleting: false
    }),
    _useState8 = _slicedToArray(_useState7, 2),
    loadingStates = _useState8[0],
    setLoadingStates = _useState8[1];
  var history = (0,_useHistory__WEBPACK_IMPORTED_MODULE_1__/* .useHistory */ .W)();
  var selection = (0,_useSelection__WEBPACK_IMPORTED_MODULE_2__/* .useSelection */ .C)({
    onSelectionChange: (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (selectedIds) {
      // Callback pour les changements de sélection
    }, []),
    multiSelect: (_globalSettings$multi = globalSettings === null || globalSettings === void 0 ? void 0 : globalSettings.multiSelect) !== null && _globalSettings$multi !== void 0 ? _globalSettings$multi : true
  });

  // Synchronisation parfaite des refs avec useLatest
  var historyRef = useLatest(history);
  var selectionRef = useLatest(selection);
  var clipboard = (0,_useClipboard__WEBPACK_IMPORTED_MODULE_3__/* .useClipboard */ .i)({
    onPaste: (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (data) {
      if (data.type === 'elements') {
        var pastedElements = data.elements.map(function (element) {
          return _objectSpread(_objectSpread({}, element), {}, {
            id: "element_".concat(nextId + data.elements.indexOf(element)),
            x: element.x + 20,
            // Offset pour éviter la superposition
            y: element.y + 20
          });
        });
        setElements(function (prev) {
          return [].concat(_toConsumableArray(prev), _toConsumableArray(pastedElements));
        });
        setNextId(function (prev) {
          return prev + pastedElements.length;
        });
        selection.selectAll(pastedElements.map(function (el) {
          return el.id;
        }));
      }
    }, [nextId, selection])
  });
  var zoom = (0,_useZoom__WEBPACK_IMPORTED_MODULE_4__/* .useZoom */ .f)({
    initialZoom: globalSettings ? parseFloat(globalSettings.defaultZoom) / 100 : 1,
    minZoom: globalSettings ? globalSettings.minZoom / 100 : 0.25,
    maxZoom: globalSettings ? globalSettings.maxZoom / 100 : 3,
    zoomStep: globalSettings ? globalSettings.zoomStep / 100 : 0.1
  });
  var contextMenu = (0,_useContextMenu__WEBPACK_IMPORTED_MODULE_5__/* .useContextMenu */ .E)();

  // Fonction updateElement définie après history
  var updateElement = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (elementId, updates) {
    setElements(function (prev) {
      var newElements = prev.map(function (element) {
        if (element.id === elementId) {
          var updatedElement = _objectSpread(_objectSpread({}, element), updates);
          // Pour les dividers, forcer la hauteur à 16px
          if (element.type === 'divider') {
            updatedElement.height = 16;
          }
          // Pour les lignes, forcer la hauteur à 12px
          if (element.type === 'line') {
            updatedElement.height = 12;
          }
          return updatedElement;
        }
        return element;
      });
      return newElements;
    });
  }, []); // Retirer les dépendances pour éviter les re-renders inutiles

  // Effet séparé pour l'historique - optimisation des performances
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    if (elements.length > 0) {
      try {
        if (historyRef.current && typeof historyRef.current.addToHistory === 'function') {
          historyRef.current.addToHistory({
            elements: elements,
            nextId: nextId
          });
        }
      } catch (error) {
        // Continuer l'exécution malgré l'erreur d'historique
      }
    }
  }, [elements, nextId]);

  // Validation des données d'entrée (initialElements)
  var validateInitialElements = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (elements) {
    if (!Array.isArray(elements)) {
      return [];
    }
    return elements.map(function (element) {
      if (!element.id || !element.type) {
        return null;
      }
      return element;
    }).filter(Boolean);
  }, []);

  // Calculer le prochain ID basé sur les éléments initiaux validés
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    var validatedElements = validateInitialElements(initialElements);
    setElements(validatedElements);
    if (validatedElements && validatedElements.length > 0) {
      var maxId = Math.max.apply(Math, _toConsumableArray(validatedElements.map(function (el) {
        var _el$id;
        var idParts = ((_el$id = el.id) === null || _el$id === void 0 ? void 0 : _el$id.split('_')) || [];
        return parseInt(idParts[1] || 0);
      })));
      setNextId(maxId + 1);
    } else {
      setNextId(1);
    }
  }, [initialElements, validateInitialElements]);
  var dragAndDrop = (0,_useDragAndDrop__WEBPACK_IMPORTED_MODULE_6__/* .useDragAndDrop */ .g)({
    onElementMove: (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (elementId, position) {
      updateElement(elementId, position);
    }, [updateElement]),
    onElementDrop: (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (elementId, position) {
      updateElement(elementId, position);
      history.addToHistory({
        elements: elements.map(function (el) {
          return el.id === elementId ? _objectSpread(_objectSpread({}, el), position) : el;
        }),
        nextId: nextId
      });
    }, [updateElement, history, elements, nextId])
  });

  // Fonction utilitaire pour nettoyer les éléments avant sauvegarde (éviter les références DOM)
  var cleanElementsForHistory = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (elementsToClean) {
    return elementsToClean.map(function (element) {
      var cleaned = _objectSpread({}, element);
      // Supprimer les propriétés non sérialisables qui pourraient contenir des références DOM
      var nonSerializableProps = ['domElement', 'ref', 'eventListeners', 'component', 'render'];
      nonSerializableProps.forEach(function (prop) {
        delete cleaned[prop];
      });
      return cleaned;
    });
  }, []);

  // Sauvegarder l'état dans l'historique à chaque changement
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    if (elements.length > 0 || history.historySize === 0) {
      var cleanedElements = cleanElementsForHistory(elements);
      history.addToHistory({
        elements: cleanedElements,
        nextId: nextId
      });
    }
  }, [elements, nextId, history, cleanElementsForHistory]);

  // Correction automatique des éléments spéciaux existants
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    var specialElements = ['product_table', 'customer_info', 'company_logo', 'company_info', 'order_number', 'document_type', 'progress-bar'];
    var needsCorrection = elements.some(function (element) {
      return specialElements.includes(element.type) && element.backgroundColor !== 'transparent';
    });
    if (needsCorrection) {
      setElements(function (prevElements) {
        return prevElements.map(function (element) {
          if (specialElements.includes(element.type) && element.backgroundColor !== 'transparent') {
            return _objectSpread(_objectSpread({}, element), {}, {
              backgroundColor: 'transparent'
            });
          }
          return element;
        });
      });
    }
  }, []); // Uniquement au montage du composant

  var addElement = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (elementType) {
    var properties = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
    // Utiliser les refs pour accéder aux valeurs actuelles
    var currentHistory = historyRef.current;
    var currentSelection = selectionRef.current;

    // Vérifications de sécurité
    if (!currentSelection || !currentHistory) {
      return;
    }
    if (typeof currentSelection.selectElement !== 'function') {
      return;
    }
    if (typeof currentHistory.addToHistory !== 'function') {
      return;
    }

    // Propriétés par défaut complètes et synchronisées
    var defaultProps = {
      // Position et dimensions
      x: 50,
      y: 50,
      width: 100,
      height: 50,
      // Apparence de base
      backgroundColor: 'transparent',
      borderColor: 'transparent',
      borderWidth: 0,
      borderStyle: 'solid',
      borderRadius: 0,
      // Typographie
      color: '#1e293b',
      fontFamily: 'Inter, sans-serif',
      fontSize: 14,
      fontWeight: 'normal',
      fontStyle: 'normal',
      textAlign: 'left',
      textDecoration: 'none',
      // Contenu
      text: 'Texte',
      // Propriétés avancées
      opacity: 100,
      rotation: 0,
      scale: 100,
      visible: true,
      // Images et médias
      src: '',
      alt: '',
      objectFit: 'cover',
      brightness: 100,
      contrast: 100,
      saturate: 100,
      // Effets
      shadow: false,
      shadowColor: '#000000',
      shadowOffsetX: 2,
      shadowOffsetY: 2,
      // Propriétés spécifiques aux tableaux
      showHeaders: true,
      showBorders: true,
      dataSource: 'order_items',
      showSubtotal: false,
      showShipping: true,
      showTaxes: true,
      showDiscount: false,
      showTotal: false,
      // Propriétés de barre de progression
      progressColor: '#3b82f6',
      progressValue: 75,
      // Propriétés de code et lignes
      lineColor: '#64748b',
      lineWidth: 2,
      // Propriétés de document
      documentType: 'invoice',
      imageUrl: '',
      // Propriétés de mise en page
      spacing: 8,
      layout: 'vertical',
      alignment: 'left',
      fit: 'contain'
    };
    var newElement = _objectSpread(_objectSpread({
      id: "element_".concat(nextId),
      type: elementType
    }, defaultProps), properties);

    // Ajustements spécifiques selon le type d'élément
    if (elementType === 'line') {
      newElement.height = 12; // Hauteur fixe pour les lignes
      newElement.width = 200; // Largeur par défaut pour les lignes
    } else if (elementType === 'divider') {
      newElement.height = 16; // Hauteur fixe pour les séparateurs
      newElement.width = 200; // Largeur par défaut pour les séparateurs
    }
    setElements(function (prev) {
      var newElements = [].concat(_toConsumableArray(prev), [newElement]);
      // Sauvegarder dans l'historique
      try {
        if (currentHistory && typeof currentHistory.addToHistory === 'function') {
          currentHistory.addToHistory({
            elements: newElements,
            nextId: nextId + 1
          });
        }
      } catch (error) {}
      return newElements;
    });
    setNextId(function (prev) {
      return prev + 1;
    });
    try {
      if (currentSelection && typeof currentSelection.selectElement === 'function') {
        currentSelection.selectElement(newElement.id);
      }
    } catch (error) {}
  }, [nextId]); // Retirer selection et history des dépendances

  var deleteElement = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (elementId) {
    setElements(function (prev) {
      var newElements = prev.filter(function (element) {
        return element.id !== elementId;
      });
      // Sauvegarder dans l'historique
      history.addToHistory({
        elements: newElements,
        nextId: nextId
      });
      return newElements;
    });
    selection.clearSelection();
  }, [selection, history, nextId]);
  var deleteSelectedElements = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    var elementsToDelete = selection.deleteSelected();
    setElements(function (prev) {
      var newElements = prev.filter(function (element) {
        return !elementsToDelete.includes(element.id);
      });
      // Sauvegarder dans l'historique
      history.addToHistory({
        elements: newElements,
        nextId: nextId
      });
      return newElements;
    });
    selection.clearSelection();
  }, [selection, history, nextId]);
  var duplicateElement = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (elementId) {
    var element = elements.find(function (el) {
      return el.id === elementId;
    });
    if (element) {
      var duplicatedElement = _objectSpread(_objectSpread({}, element), {}, {
        id: "element_".concat(nextId),
        x: element.x + 20,
        y: element.y + 20
      });
      setElements(function (prev) {
        var newElements = [].concat(_toConsumableArray(prev), [duplicatedElement]);
        // Sauvegarder dans l'historique
        history.addToHistory({
          elements: newElements,
          nextId: nextId + 1
        });
        return newElements;
      });
      setNextId(function (prev) {
        return prev + 1;
      });
      selection.selectElement(duplicatedElement.id);
    }
  }, [elements, nextId, selection, history]);
  var duplicateSelectedElements = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    var elementsToDuplicate = selection.duplicateSelected();
    var duplicatedElements = [];
    elementsToDuplicate.forEach(function (elementId) {
      var element = elements.find(function (el) {
        return el.id === elementId;
      });
      if (element) {
        var duplicatedElement = _objectSpread(_objectSpread({}, element), {}, {
          id: "element_".concat(nextId + duplicatedElements.length),
          x: element.x + 20,
          y: element.y + 20
        });
        duplicatedElements.push(duplicatedElement);
      }
    });
    if (duplicatedElements.length > 0) {
      setElements(function (prev) {
        var newElements = [].concat(_toConsumableArray(prev), duplicatedElements);
        // Sauvegarder dans l'historique
        history.addToHistory({
          elements: newElements,
          nextId: nextId + duplicatedElements.length
        });
        return newElements;
      });
      setNextId(function (prev) {
        return prev + duplicatedElements.length;
      });
      selection.selectAll(duplicatedElements.map(function (el) {
        return el.id;
      }));
    }
  }, [elements, nextId, selection, history]);
  var copySelectedElements = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    var selectedIds = selection.selectedElements;
    var selectedElementsData = elements.filter(function (el) {
      return selectedIds.includes(el.id);
    });
    if (selectedElementsData.length > 0) {
      clipboard.copy({
        type: 'elements',
        elements: selectedElementsData
      });
    }
  }, [elements, selection, clipboard]);
  var pasteElements = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    clipboard.paste();
  }, [clipboard]);
  var undo = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    var previousState = history.undo();
    if (previousState) {
      setElements(previousState.elements);
      setNextId(previousState.nextId);
      selection.clearSelection();
    }
  }, [history, selection]);
  var redo = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    var nextState = history.redo();
    if (nextState) {
      setElements(nextState.elements);
      setNextId(nextState.nextId);
      selection.clearSelection();
    }
  }, [history, selection]);
  var saveTemplate = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(/*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
    var isExistingTemplate, checkToastrAvailability, toastrAvailable, _window$pdfBuilderDat, _window$pdfBuilderDat2, _window$pdfBuilderDat3, _window$pdfBuilderAja, _window$pdfBuilderDat4, _window$pdfBuilderAja2, cleanElementForSerialization, cleanedElements, templateData, jsonString, testParse, _iterator, _step, element, formData, response, result, _result$data, errorMessage, _t, _t2, _t3;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.p = _context.n) {
        case 0:
          if (!loadingStates.saving) {
            _context.n = 1;
            break;
          }
          return _context.a(2);
        case 1:
          // console.log('🚀 PDF Builder SAVE - Démarrage de la sauvegarde');
          setLoadingStates(function (prev) {
            return _objectSpread(_objectSpread({}, prev), {}, {
              saving: true
            });
          });

          // Déterminer si c'est un template existant
          isExistingTemplate = templateId && templateId !== '0' && templateId !== 0; // Fonction pour vérifier la disponibilité de Toastr avec retry
          checkToastrAvailability = function checkToastrAvailability() {
            return Promise.resolve(true); // Toastr is now always available (real or fallback)
          };
          _context.n = 2;
          return checkToastrAvailability();
        case 2:
          toastrAvailable = _context.v;
          _context.p = 3;
          // Fonction pour nettoyer et valider les données avant sérialisation
          cleanElementForSerialization = function cleanElementForSerialization(element) {
            // Liste des propriétés à exclure car elles ne sont pas sérialisables
            var excludedProps = ['domElement', 'eventListeners', 'ref', 'onClick', 'onMouseDown', 'onMouseUp', 'onMouseMove', 'onContextMenu', 'onDoubleClick', 'onDragStart', 'onDragEnd', 'onResize', 'component', 'render', 'props', 'state', 'context', 'refs', '_reactInternalInstance', '_reactInternals', '$$typeof', 'constructor', 'prototype',
            // Propriétés React spécifiques
            '_owner', '_store', 'key', 'ref', '_self', '_source'];
            var cleaned = {};
            for (var _i = 0, _Object$entries = Object.entries(element); _i < _Object$entries.length; _i++) {
              var _Object$entries$_i = _slicedToArray(_Object$entries[_i], 2),
                key = _Object$entries$_i[0],
                value = _Object$entries$_i[1];
              // Exclure les propriétés problématiques
              if (excludedProps.includes(key)) {
                continue;
              }

              // Exclure les propriétés qui commencent par underscore (privées React)
              if (key.startsWith('_')) {
                continue;
              }

              // Validation et correction selon le type de propriété
              var validatedValue = value;

              // Propriétés numériques
              var numericProps = ['x', 'y', 'width', 'height', 'fontSize', 'opacity', 'lineHeight', 'letterSpacing', 'zIndex', 'borderWidth', 'borderRadius', 'rotation', 'padding', 'scale', 'shadowOffsetX', 'shadowOffsetY', 'brightness', 'contrast', 'saturate', 'progressValue', 'lineWidth', 'spacing'];
              if (numericProps.includes(key)) {
                if (typeof value === 'string' && value !== '' && !isNaN(value)) {
                  validatedValue = parseFloat(value);
                } else if (typeof value !== 'number') {
                  // Valeurs par défaut
                  var defaults = {
                    x: 0,
                    y: 0,
                    width: 100,
                    height: 50,
                    fontSize: 14,
                    opacity: 1,
                    lineHeight: 1.2,
                    letterSpacing: 0,
                    zIndex: 0,
                    borderWidth: 0,
                    borderRadius: 0,
                    rotation: 0,
                    padding: 0
                  };
                  validatedValue = defaults[key] || 0;
                }
              }

              // Propriétés de couleur
              var colorProps = ['color', 'backgroundColor', 'borderColor', 'shadowColor', 'progressColor', 'lineColor'];
              if (colorProps.includes(key)) {
                if (value && value !== 'transparent') {
                  // Normaliser les couleurs
                  if (!/^#[0-9A-Fa-f]{3,6}$/i.test(value)) {
                    // Couleurs nommées communes
                    var namedColors = {
                      'black': '#000000',
                      'white': '#ffffff',
                      'red': '#ff0000',
                      'green': '#008000',
                      'blue': '#0000ff',
                      'gray': '#808080',
                      'grey': '#808080',
                      'transparent': 'transparent'
                    };
                    validatedValue = namedColors[value.toLowerCase()] || '#000000';
                  }
                }
              }

              // Propriétés de style de texte
              if (key === 'fontWeight') {
                var validWeights = ['normal', 'bold', '100', '200', '300', '400', '500', '600', '700', '800', '900'];
                if (!validWeights.includes(value)) {
                  validatedValue = 'normal';
                }
              }
              if (key === 'textAlign') {
                var validAligns = ['left', 'center', 'right', 'justify'];
                if (!validAligns.includes(value)) {
                  validatedValue = 'left';
                }
              }
              if (key === 'textDecoration') {
                var validDecorations = ['none', 'underline', 'overline', 'line-through'];
                if (!validDecorations.includes(value)) {
                  validatedValue = 'none';
                }
              }
              if (key === 'textTransform') {
                var validTransforms = ['none', 'capitalize', 'uppercase', 'lowercase'];
                if (!validTransforms.includes(value)) {
                  validatedValue = 'none';
                }
              }
              if (key === 'borderStyle') {
                var validStyles = ['solid', 'dashed', 'dotted', 'double', 'none'];
                if (!validStyles.includes(value)) {
                  validatedValue = 'solid';
                }
              }

              // Propriétés de texte et contenu
              if (key === 'text' || key === 'content') {
                if (typeof value !== 'string') {
                  validatedValue = '';
                }
              }

              // Propriétés de police
              if (key === 'fontFamily') {
                if (typeof value !== 'string' || value.trim() === '') {
                  validatedValue = 'Inter, sans-serif';
                }
              }
              if (key === 'fontStyle') {
                var _validStyles = ['normal', 'italic', 'oblique'];
                if (!_validStyles.includes(value)) {
                  validatedValue = 'normal';
                }
              }

              // Propriétés de visibilité et transformation
              if (key === 'visible') {
                if (typeof value !== 'boolean') {
                  validatedValue = true;
                }
              }
              if (key === 'scale') {
                if (typeof value === 'string' && value !== '' && !isNaN(value)) {
                  validatedValue = parseFloat(value);
                } else if (typeof value !== 'number') {
                  validatedValue = 100;
                }
                // Limiter la scale entre 10 et 500
                validatedValue = Math.max(10, Math.min(500, validatedValue));
              }

              // Propriétés d'ombre
              if (key === 'shadow') {
                if (typeof value !== 'boolean') {
                  validatedValue = false;
                }
              }
              if (key === 'shadowColor') {
                if (value && value !== 'transparent') {
                  if (!/^#[0-9A-Fa-f]{3,6}$/i.test(value)) {
                    var _namedColors = {
                      'black': '#000000',
                      'white': '#ffffff',
                      'red': '#ff0000',
                      'green': '#008000',
                      'blue': '#0000ff',
                      'gray': '#808080',
                      'grey': '#808080',
                      'transparent': 'transparent'
                    };
                    validatedValue = _namedColors[value.toLowerCase()] || '#000000';
                  }
                }
              }
              var shadowOffsetProps = ['shadowOffsetX', 'shadowOffsetY'];
              if (shadowOffsetProps.includes(key)) {
                if (typeof value === 'string' && value !== '' && !isNaN(value)) {
                  validatedValue = parseFloat(value);
                } else if (typeof value !== 'number') {
                  validatedValue = 2;
                }
              }

              // Propriétés d'image et médias
              if (key === 'brightness' || key === 'contrast' || key === 'saturate') {
                if (typeof value === 'string' && value !== '' && !isNaN(value)) {
                  validatedValue = parseFloat(value);
                } else if (typeof value !== 'number') {
                  validatedValue = 100;
                }
                // Limiter entre 0 et 200
                validatedValue = Math.max(0, Math.min(200, validatedValue));
              }
              if (key === 'objectFit') {
                var validFits = ['fill', 'contain', 'cover', 'none', 'scale-down'];
                if (!validFits.includes(value)) {
                  validatedValue = 'cover';
                }
              }

              // Propriétés de tableau
              var booleanTableProps = ['showHeaders', 'showBorders', 'showSubtotal', 'showShipping', 'showTaxes', 'showDiscount', 'showTotal'];
              if (booleanTableProps.includes(key)) {
                if (typeof value !== 'boolean') {
                  validatedValue = false;
                }
              }
              if (key === 'dataSource') {
                var validSources = ['order_items', 'cart_items', 'custom'];
                if (!validSources.includes(value)) {
                  validatedValue = 'order_items';
                }
              }

              // Propriétés de barre de progression
              if (key === 'progressValue') {
                if (typeof value === 'string' && value !== '' && !isNaN(value)) {
                  validatedValue = parseFloat(value);
                } else if (typeof value !== 'number') {
                  validatedValue = 0;
                }
                validatedValue = Math.max(0, Math.min(100, validatedValue));
              }

              // Propriétés de ligne/code
              if (key === 'lineWidth') {
                if (typeof value === 'string' && value !== '' && !isNaN(value)) {
                  validatedValue = parseFloat(value);
                } else if (typeof value !== 'number') {
                  validatedValue = 2;
                }
                validatedValue = Math.max(1, Math.min(10, validatedValue));
              }

              // Propriétés de mise en page
              if (key === 'spacing') {
                if (typeof value === 'string' && value !== '' && !isNaN(value)) {
                  validatedValue = parseFloat(value);
                } else if (typeof value !== 'number') {
                  validatedValue = 8;
                }
              }
              if (key === 'layout') {
                var validLayouts = ['vertical', 'horizontal', 'grid'];
                if (!validLayouts.includes(value)) {
                  validatedValue = 'vertical';
                }
              }
              if (key === 'alignment') {
                var validAlignments = ['left', 'center', 'right', 'justify'];
                if (!validAlignments.includes(value)) {
                  validatedValue = 'left';
                }
              }
              if (key === 'fit') {
                var _validFits = ['contain', 'cover', 'fill', 'none'];
                if (!_validFits.includes(value)) {
                  validatedValue = 'contain';
                }
              }

              // Propriétés de document
              if (key === 'documentType') {
                var validTypes = ['invoice', 'quote', 'receipt', 'order'];
                if (!validTypes.includes(value)) {
                  validatedValue = 'invoice';
                }
              }

              // Propriétés d'objet complexes
              if (key === 'columns') {
                if (_typeof(value) === 'object' && value !== null) {
                  var _value$image, _value$name, _value$sku, _value$quantity, _value$price, _value$total;
                  validatedValue = {
                    image: (_value$image = value.image) !== null && _value$image !== void 0 ? _value$image : true,
                    name: (_value$name = value.name) !== null && _value$name !== void 0 ? _value$name : true,
                    sku: (_value$sku = value.sku) !== null && _value$sku !== void 0 ? _value$sku : false,
                    quantity: (_value$quantity = value.quantity) !== null && _value$quantity !== void 0 ? _value$quantity : true,
                    price: (_value$price = value.price) !== null && _value$price !== void 0 ? _value$price : true,
                    total: (_value$total = value.total) !== null && _value$total !== void 0 ? _value$total : true
                  };
                } else {
                  validatedValue = {
                    image: true,
                    name: true,
                    sku: false,
                    quantity: true,
                    price: true,
                    total: true
                  };
                }
              }

              // Vérifier le type de valeur
              if (validatedValue === null || validatedValue === undefined) {
                // Ne pas inclure les propriétés null/undefined
                continue; // Skip this property but continue cleaning the rest
              } else if (typeof validatedValue === 'string' || typeof validatedValue === 'number' || typeof validatedValue === 'boolean') {
                cleaned[key] = validatedValue;
              } else if (Array.isArray(validatedValue)) {
                // Pour les tableaux, nettoyer chaque élément de manière très stricte
                try {
                  var cleanedArray = validatedValue.filter(function (item) {
                    return item !== null && item !== undefined;
                  }) // Filtrer les valeurs null/undefined
                  .map(function (item) {
                    if (_typeof(item) === 'object' && item !== null) {
                      // Pour les objets dans les tableaux, seulement garder les propriétés primitives
                      var cleanedItem = {};
                      for (var _i2 = 0, _Object$entries2 = Object.entries(item); _i2 < _Object$entries2.length; _i2++) {
                        var _Object$entries2$_i = _slicedToArray(_Object$entries2[_i2], 2),
                          itemKey = _Object$entries2$_i[0],
                          itemValue = _Object$entries2$_i[1];
                        if (typeof itemValue === 'string' || typeof itemValue === 'number' || typeof itemValue === 'boolean') {
                          cleanedItem[itemKey] = itemValue;
                        }
                      }
                      return cleanedItem;
                    }
                    return typeof item === 'string' || typeof item === 'number' || typeof item === 'boolean' ? item : null;
                  }).filter(function (item) {
                    return item !== null;
                  }); // Retirer les éléments null

                  // Test final de sérialisation du tableau complet
                  JSON.stringify(cleanedArray);
                  cleaned[key] = cleanedArray;
                } catch (e) {
                  console.warn("Impossible de s\xE9rialiser le tableau pour ".concat(key, ", utilisation tableau vide:"), e);
                  cleaned[key] = [];
                }
              } else if (_typeof(validatedValue) === 'object') {
                // Pour les objets, nettoyer récursivement mais de manière très stricte
                try {
                  var cleanedObj = {};
                  for (var _i3 = 0, _Object$entries3 = Object.entries(validatedValue); _i3 < _Object$entries3.length; _i3++) {
                    var _Object$entries3$_i = _slicedToArray(_Object$entries3[_i3], 2),
                      objKey = _Object$entries3$_i[0],
                      objValue = _Object$entries3$_i[1];
                    // Exclure les propriétés problématiques des objets imbriqués
                    if (objKey.startsWith('_') || excludedProps.includes(objKey)) {
                      continue;
                    }
                    if (typeof objValue === 'string' || typeof objValue === 'number' || typeof objValue === 'boolean') {
                      cleanedObj[objKey] = objValue;
                    }
                  }
                  // Test de sérialisation de l'objet nettoyé
                  JSON.stringify(cleanedObj);
                  cleaned[key] = cleanedObj;
                } catch (e) {
                  console.warn("Impossible de s\xE9rialiser l'objet pour ".concat(key, ", utilisation objet vide:"), e);
                  cleaned[key] = {};
                }
              } else {
                // Pour les autres types (functions, symbols, etc.), ignorer silencieusement
                console.warn("Type non support\xE9 ignor\xE9 pour ".concat(key, ": ").concat(_typeof(validatedValue)));
              }
            }
            return cleaned;
          }; // Nettoyer tous les éléments avec protection contre les erreurs
          cleanedElements = [];
          try {
            // console.log('🧹 PDF Builder SAVE - Nettoyage des éléments commencé, éléments bruts:', elements.length);

            // Log détaillé de chaque élément avant filtrage
            elements.forEach(function (element, index) {
              var hasElement = element && _typeof(element) === 'object';
              var hasId = element && element.id;
              var hasType = element && element.type;
              // console.log(`📋 Élément ${index} - valide: ${hasElement}, id: ${hasId ? element.id : 'MISSING'}, type: ${hasType ? element.type : 'MISSING'}`);
              if (!hasElement || !hasId || !hasType) {
                // console.log(`❌ Élément ${index} sera filtré:`, element);
              }
            });
            cleanedElements = elements.filter(function (element) {
              return element && _typeof(element) === 'object' && element.id && element.type;
            }) // Filtrer les éléments invalides
            .map(cleanElementForSerialization).filter(function (element) {
              return element && element.id && element.type;
            }); // Filtrer après nettoyage

            // console.log('✅ PDF Builder SAVE - Nettoyage terminé, éléments nettoyés:', cleanedElements.length);

            // Test de sérialisation de tous les éléments
            JSON.stringify(cleanedElements);
            // console.log('✅ PDF Builder SAVE - Test de sérialisation réussi');
          } catch (e) {
            console.error('❌ PDF Builder SAVE - Erreur lors du nettoyage des éléments:', e);
            console.error('❌ PDF Builder SAVE - Éléments originaux qui ont causé l\'erreur:', elements);
            // En cas d'erreur, utiliser un tableau vide pour éviter les crashes
            cleanedElements = [];
          }

          // Log détaillé des propriétés de chaque élément (mode développement uniquement)
          if (isDevelopment) {
            elements.forEach(function (element, index) {
              // console.log(`Élément ${index} (${element.type}) propriétés avant nettoyage:`, Object.keys(element));
              if (element.type === 'product_table') {
                // console.log(`Tableau ${index} - paramètres:`, {
                //   showHeaders: element.showHeaders,
                //   showBorders: element.showBorders,
                //   columns: element.columns,
                //   tableStyle: element.tableStyle,
                //   showSubtotal: element.showSubtotal,
                //   showShipping: element.showShipping,
                //   showTaxes: element.showTaxes,
                //   showDiscount: element.showDiscount,
                //   showTotal: element.showTotal
                // });
              }
            });

            // console.log('Éléments nettoyés pour sauvegarde:', cleanedElements);
          }
          templateData = {
            elements: cleanedElements,
            canvasWidth: canvasWidth,
            canvasHeight: canvasHeight,
            version: '1.0'
          }; // Log des données en mode développement uniquement
          if (isDevelopment) {
            // console.log('Données template à sauvegarder:', templateData);
          }

          // Valider le JSON avant envoi avec protection renforcée
          _context.p = 4;
          jsonString = JSON.stringify(templateData);

          // Tester le parsing pour valider
          testParse = JSON.parse(jsonString); // Vérifier que les données essentielles sont présentes
          if (!(!testParse.elements || !Array.isArray(testParse.elements))) {
            _context.n = 5;
            break;
          }
          throw new Error('Structure de données invalide: éléments manquants ou incorrects');
        case 5:
          // Vérifier que chaque élément a au moins un ID et un type
          _iterator = _createForOfIteratorHelper(testParse.elements);
          _context.p = 6;
          _iterator.s();
        case 7:
          if ((_step = _iterator.n()).done) {
            _context.n = 9;
            break;
          }
          element = _step.value;
          if (!(!element.id || !element.type)) {
            _context.n = 8;
            break;
          }
          throw new Error("\xC9l\xE9ment invalide d\xE9tect\xE9: ID ou type manquant pour ".concat(JSON.stringify(element)));
        case 8:
          _context.n = 7;
          break;
        case 9:
          _context.n = 11;
          break;
        case 10:
          _context.p = 10;
          _t = _context.v;
          _iterator.e(_t);
        case 11:
          _context.p = 11;
          _iterator.f();
          return _context.f(11);
        case 12:
          _context.n = 14;
          break;
        case 13:
          _context.p = 13;
          _t2 = _context.v;
          console.error('Erreur de validation JSON côté client:', _t2);
          console.error('Données templateData qui ont causé l\'erreur:', templateData);
          throw new Error('Données JSON invalides côté client: ' + _t2.message);
        case 14:
          // Sauvegarde directe via AJAX avec FormData pour les données volumineuses
          // console.log('📤 PDF Builder SAVE - Préparation des données pour envoi au serveur');
          formData = new FormData();
          formData.append('action', 'pdf_builder_pro_save_template');
          formData.append('template_data', jsonString);
          formData.append('template_name', ((_window$pdfBuilderDat = window.pdfBuilderData) === null || _window$pdfBuilderDat === void 0 ? void 0 : _window$pdfBuilderDat.templateName) || "Template ".concat(((_window$pdfBuilderDat2 = window.pdfBuilderData) === null || _window$pdfBuilderDat2 === void 0 ? void 0 : _window$pdfBuilderDat2.templateId) || 'New'));
          formData.append('template_id', ((_window$pdfBuilderDat3 = window.pdfBuilderData) === null || _window$pdfBuilderDat3 === void 0 ? void 0 : _window$pdfBuilderDat3.templateId) || '0');
          formData.append('nonce', ((_window$pdfBuilderAja = window.pdfBuilderAjax) === null || _window$pdfBuilderAja === void 0 ? void 0 : _window$pdfBuilderAja.nonce) || ((_window$pdfBuilderDat4 = window.pdfBuilderData) === null || _window$pdfBuilderDat4 === void 0 ? void 0 : _window$pdfBuilderDat4.nonce) || '');

          // console.log('📤 PDF Builder SAVE - Données FormData préparées:', {
          //   action: 'pdf_builder_pro_save_template',
          //   templateName: window.pdfBuilderData?.templateName || `Template ${window.pdfBuilderData?.templateId || 'New'}`,
          //   templateId: window.pdfBuilderData?.templateId || '0',
          //   nonce: window.pdfBuilderAjax?.nonce || window.pdfBuilderData?.nonce || '',
          //   jsonLength: jsonString.length
          // });

          // console.log('🌐 PDF Builder SAVE - Envoi de la requête AJAX...');
          _context.n = 15;
          return fetch(((_window$pdfBuilderAja2 = window.pdfBuilderAjax) === null || _window$pdfBuilderAja2 === void 0 ? void 0 : _window$pdfBuilderAja2.ajaxurl) || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData
          });
        case 15:
          response = _context.v;
          _context.n = 16;
          return response.json();
        case 16:
          result = _context.v;
          if (result.success) {
            _context.n = 17;
            break;
          }
          throw new Error(((_result$data = result.data) === null || _result$data === void 0 ? void 0 : _result$data.message) || 'Erreur lors de la sauvegarde');
        case 17:
          // Notification de succès pour les templates existants
          if (isExistingTemplate) {
            if (toastrAvailable) {
              toastr.success('Modifications du canvas sauvegardées avec succès !');
            } else {
              alert('Modifications du canvas sauvegardées avec succès !');
            }
          }
          return _context.a(2, templateData);
        case 18:
          _context.p = 18;
          _t3 = _context.v;
          console.error('❌ PDF Builder SAVE - Erreur lors de la sauvegarde:', _t3);
          console.error('❌ PDF Builder SAVE - Détails de l\'erreur:', {
            message: _t3.message,
            stack: _t3.stack,
            name: _t3.name
          });

          // Notification d'erreur
          errorMessage = _t3.message || 'Erreur inconnue lors de la sauvegarde';
          if (toastrAvailable) {
            toastr.error("Erreur lors de la sauvegarde: ".concat(errorMessage));
          } else {
            alert("Erreur lors de la sauvegarde: ".concat(errorMessage));
          }
          throw _t3;
        case 19:
          _context.p = 19;
          // console.log('🏁 PDF Builder SAVE - Fin du processus de sauvegarde');
          setLoadingStates(function (prev) {
            return _objectSpread(_objectSpread({}, prev), {}, {
              saving: false
            });
          });
          return _context.f(19);
        case 20:
          return _context.a(2);
      }
    }, _callee, null, [[6, 10, 11, 12], [4, 13], [3, 18, 19, 20]]);
  })), [elements, canvasWidth, canvasHeight, isSaving, templateId]);
  var loadTemplate = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (templateData) {
    if (templateData.elements) {
      setElements(templateData.elements);
      setNextId(templateData.nextId || Math.max.apply(Math, _toConsumableArray(templateData.elements.map(function (el) {
        return parseInt(el.id.split('_')[1]);
      }) || [0])) + 1);
      selection.clearSelection();
      history.clearHistory();
    }
  }, [selection, history]);
  var showContextMenu = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (x, y) {
    var targetElementId = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
    var menuItems = [];
    if (targetElementId) {
      menuItems.push({
        label: 'Dupliquer',
        action: function action() {
          return duplicateElement(targetElementId);
        }
      }, {
        label: 'Supprimer',
        action: function action() {
          return deleteElement(targetElementId);
        }
      }, {
        type: 'separator'
      }, {
        label: 'Copier',
        action: copySelectedElements
      }, {
        label: 'Coller',
        action: pasteElements,
        disabled: !clipboard.hasData()
      });
    } else if (selection.selectedElements.length > 0) {
      menuItems.push({
        label: 'Dupliquer',
        action: duplicateSelectedElements
      }, {
        label: 'Supprimer',
        action: deleteSelectedElements
      }, {
        type: 'separator'
      }, {
        label: 'Copier',
        action: copySelectedElements
      }, {
        label: 'Coller',
        action: pasteElements,
        disabled: !clipboard.hasData()
      });
    } else {
      menuItems.push({
        label: 'Coller',
        action: pasteElements,
        disabled: !clipboard.hasData()
      });
    }
    contextMenu.showContextMenu(x, y, menuItems);
  }, [selection, contextMenu, duplicateElement, deleteElement, copySelectedElements, pasteElements, clipboard, duplicateSelectedElements, deleteSelectedElements]);
  var zoomToSelection = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    var selectedElementIds = selection.selectedElements;
    if (selectedElementIds.length === 0) return;
    var selectedElements = elements.filter(function (el) {
      return selectedElementIds.includes(el.id);
    });
    if (selectedElements.length === 0) return;

    // Obtenir les dimensions du conteneur (viewport du canvas)
    var containerElement = document.querySelector('.canvas-container');
    if (!containerElement) return;
    var containerRect = containerElement.getBoundingClientRect();
    var containerWidth = containerRect.width;
    var containerHeight = containerRect.height;
    zoom.zoomToSelection(selectedElements, canvasWidth, canvasHeight, containerWidth, containerHeight);
  }, [selection.selectedElements, elements, zoom, canvasWidth, canvasHeight]);
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(function () {
    return {
      // État
      elements: elements,
      canvasWidth: canvasWidth,
      canvasHeight: canvasHeight,
      // Hooks intégrés
      selection: selection,
      zoom: zoom,
      contextMenu: contextMenu,
      dragAndDrop: dragAndDrop,
      // Actions sur les éléments
      addElement: addElement,
      updateElement: updateElement,
      deleteElement: deleteElement,
      deleteSelectedElements: deleteSelectedElements,
      duplicateElement: duplicateElement,
      duplicateSelectedElements: duplicateSelectedElements,
      // Presse-papiers
      copySelectedElements: copySelectedElements,
      pasteElements: pasteElements,
      // Historique
      undo: undo,
      redo: redo,
      canUndo: history.canUndo(),
      canRedo: history.canRedo(),
      history: history,
      // Zoom
      zoomToSelection: zoomToSelection,
      // Sauvegarde
      saveTemplate: saveTemplate,
      // États de chargement pour feedback visuel
      loadingStates: loadingStates,
      isSaving: loadingStates.saving,
      // Alias pour compatibilité

      // Menu contextuel
      showContextMenu: showContextMenu,
      // Utilitaires
      getAllElements: (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
        return elements;
      }, [elements]),
      getElementById: (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (id) {
        return elements.find(function (el) {
          return el.id === id;
        });
      }, [elements])
    };
  }, [elements, canvasWidth, canvasHeight, selection, zoom, contextMenu, dragAndDrop, addElement, updateElement, deleteElement, deleteSelectedElements, duplicateElement, duplicateSelectedElements, copySelectedElements, pasteElements, undo, redo, history, showContextMenu, zoomToSelection, saveTemplate]);

  // Nettoyage mémoire au démontage
  // removed by dead control flow


  // Synchronisation temps réel entre onglets via localStorage
  // removed by dead control flow

  // removed by dead control flow

};

/***/ }),

/***/ 174:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   g: () => (/* binding */ useDragAndDrop)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(540);
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useDragAndDrop = function useDragAndDrop(_ref) {
  var onElementMove = _ref.onElementMove,
    onElementDrop = _ref.onElementDrop,
    _ref$snapToGrid = _ref.snapToGrid,
    snapToGrid = _ref$snapToGrid === void 0 ? true : _ref$snapToGrid,
    _ref$gridSize = _ref.gridSize,
    gridSize = _ref$gridSize === void 0 ? 10 : _ref$gridSize,
    _ref$zoom = _ref.zoom,
    zoom = _ref$zoom === void 0 ? 1 : _ref$zoom,
    _ref$canvasWidth = _ref.canvasWidth,
    canvasWidth = _ref$canvasWidth === void 0 ? 595 : _ref$canvasWidth,
    _ref$canvasHeight = _ref.canvasHeight,
    canvasHeight = _ref$canvasHeight === void 0 ? 842 : _ref$canvasHeight,
    _ref$guides = _ref.guides,
    guides = _ref$guides === void 0 ? {
      horizontal: [],
      vertical: []
    } : _ref$guides,
    _ref$snapToGuides = _ref.snapToGuides,
    snapToGuides = _ref$snapToGuides === void 0 ? true : _ref$snapToGuides;
  var _useState = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(false),
    _useState2 = _slicedToArray(_useState, 2),
    isDragging = _useState2[0],
    setIsDragging = _useState2[1];
  var _useState3 = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)({
      x: 0,
      y: 0
    }),
    _useState4 = _slicedToArray(_useState3, 2),
    dragOffset = _useState4[0],
    setDragOffset = _useState4[1];
  var _useState5 = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(null),
    _useState6 = _slicedToArray(_useState5, 2),
    draggedElementId = _useState6[0],
    setDraggedElementId = _useState6[1];
  var dragStartPos = (0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)({
    x: 0,
    y: 0
  });
  var elementStartPos = (0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)({
    x: 0,
    y: 0
  });
  var currentDragData = (0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  var currentDragOffset = (0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)({
    x: 0,
    y: 0
  });
  var snapToGridValue = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (value) {
    if (!snapToGrid) return value;
    return Math.round(value / gridSize) * gridSize;
  }, [snapToGrid, gridSize]);
  var snapToGuidesValue = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (value) {
    var isHorizontal = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
    if (!snapToGuides) return value;
    var guideArray = isHorizontal ? guides.horizontal : guides.vertical;
    var snapTolerance = 5; // pixels
    var _iterator = _createForOfIteratorHelper(guideArray),
      _step;
    try {
      for (_iterator.s(); !(_step = _iterator.n()).done;) {
        var guide = _step.value;
        if (Math.abs(value - guide) <= snapTolerance) {
          return guide;
        }
      }
    } catch (err) {
      _iterator.e(err);
    } finally {
      _iterator.f();
    }
    return value;
  }, [snapToGuides, guides]);
  var snapValue = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (value) {
    var isHorizontal = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
    var elementType = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
    // Pour les lignes, désactiver le snap horizontal pour permettre le déplacement libre
    if (elementType === 'line' && !isHorizontal) {
      return value;
    }
    var snapped = value;

    // Appliquer l'aimantation à la grille d'abord
    snapped = snapToGridValue(snapped);

    // Puis appliquer l'aimantation aux guides
    snapped = snapToGuidesValue(snapped, isHorizontal);
    return snapped;
  }, [snapToGridValue, snapToGuidesValue]);

  // Nettoyer les event listeners quand le composant se démonte
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    return function () {
      if (currentDragData.current) {
        document.removeEventListener('mousemove', currentDragData.current.handleMouseMove);
        document.removeEventListener('mouseup', currentDragData.current.handleMouseUp);
        currentDragData.current = null;
      }
    };
  }, []);
  var handleMouseDown = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (e, elementId, elementRect) {
    var canvasRect = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
    var zoomLevel = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 1;
    var elementType = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : null;
    if (e.button !== 0) return; // Only left mouse button

    // Vérifier que l'élément source existe encore dans le DOM
    if (!e.target || !e.target.isConnected) {
      console.warn('Drag target no longer exists in DOM');
      return;
    }

    // Vérifier que l'élément parent existe encore
    if (!e.target.parentNode || !e.target.parentNode.isConnected) {
      console.warn('Drag target parent no longer exists in DOM');
      return;
    }

    // Log spécifique pour l'outil séparateur
    var isSeparator = elementType === 'divider';
    if (isSeparator) {
      // console.log(`[SEPARATOR LOG] Début du drag pour élément: ${elementId} (type: ${elementType})`);
      // console.log(`[SEPARATOR LOG] Position initiale élément: x=${elementRect.left}, y=${elementRect.top}`);
      // console.log(`[SEPARATOR LOG] Dimensions élément: width=${elementRect.width}, height=${elementRect.height}`);
    }
    e.preventDefault();
    setIsDragging(true);
    setDraggedElementId(elementId);

    // Obtenir le canvasRect dynamiquement si non fourni
    var currentCanvasRect = canvasRect || {
      left: 0,
      top: 0,
      width: canvasWidth,
      height: canvasHeight
    };
    var currentZoom = zoomLevel || zoom || 1;
    var startX = (e.clientX - currentCanvasRect.left) / currentZoom;
    var startY = (e.clientY - currentCanvasRect.top) / currentZoom;
    var lastMouseX = startX;
    var lastMouseY = startY;
    dragStartPos.current = {
      x: startX - elementRect.left,
      y: startY - elementRect.top
    };

    // Stocker les coordonnées initiales de l'élément pour le calcul de la position finale
    elementStartPos.current = {
      x: elementRect.left,
      y: elementRect.top
    };
    var handleMouseMove = function handleMouseMove(moveEvent) {
      // Vérifier que les données de drag existent toujours
      if (!currentDragData.current) {
        console.warn('Drag data no longer exists during move');
        return;
      }
      var mouseX = (moveEvent.clientX - currentCanvasRect.left) / currentZoom;
      var mouseY = (moveEvent.clientY - currentCanvasRect.top) / currentZoom;
      lastMouseX = mouseX;
      lastMouseY = mouseY;
      var deltaX = mouseX - startX;
      var deltaY = mouseY - startY;
      var effectiveCanvasWidth = canvasRect ? canvasRect.width / zoomLevel : canvasWidth;
      var effectiveCanvasHeight = canvasRect ? canvasRect.height / zoomLevel : canvasHeight;
      var newX = Math.max(0, Math.min(effectiveCanvasWidth - elementRect.width, snapValue(elementRect.left + deltaX, false, elementType)));
      var newY = Math.max(0, Math.min(effectiveCanvasHeight - elementRect.height, snapValue(elementRect.top + deltaY, true, elementType)));

      // Log pour le séparateur pendant le mouvement
      var elementType = currentDragData.current.elementType;
      var isSeparator = elementType === 'divider';
      var isLine = elementType === 'line';
      if (isSeparator) {
        // console.log(`[SEPARATOR LOG] Mouvement - Position calculée: x=${newX}, y=${newY}, delta: x=${deltaX}, y=${deltaY}`);
      }
      if (isLine) {
        // console.log(`[LINE LOG] Mouvement - Position calculée: x=${newX}, y=${newY}, delta: x=${deltaX}, y=${deltaY}, startX: ${startX}, mouseX: ${mouseX}`);
      }
      var newOffset = {
        x: newX - elementRect.left,
        y: newY - elementRect.top
      };
      setDragOffset(newOffset);
      currentDragOffset.current = newOffset;

      // Removed onElementMove call for performance - visual feedback via transform
    };
    var _handleMouseUp = function handleMouseUp() {
      // Vérifier que les données de drag existent toujours
      if (!currentDragData.current) {
        console.warn('Drag data no longer exists during drop');
        setIsDragging(false);
        setDragOffset({
          x: 0,
          y: 0
        });
        setDraggedElementId(null);
        return;
      }
      var _currentDragData$curr = currentDragData.current,
        elementId = _currentDragData$curr.elementId,
        elementType = _currentDragData$curr.elementType;
      setIsDragging(false);
      if (onElementDrop && elementId) {
        // Calculer la position finale en utilisant les coordonnées initiales de l'élément + le déplacement
        var finalX = elementStartPos.current.x + currentDragOffset.current.x;
        var finalY = elementStartPos.current.y + currentDragOffset.current.y;

        // Log pour le séparateur lors du drop
        var _isSeparator = elementType === 'divider';
        var isLine = elementType === 'line';
        if (_isSeparator) {
          // console.log(`[SEPARATOR LOG] Drop - Position initiale stockée: x=${elementStartPos.current.x}, y=${elementStartPos.current.y}`);
          // console.log(`[SEPARATOR LOG] Drop - Offset appliqué: x=${currentDragOffset.current.x}, y=${currentDragOffset.current.y}`);
          // console.log(`[SEPARATOR LOG] Drop - Position finale calculée: x=${finalX}, y=${finalY}`);
          // console.log(`[SEPARATOR LOG] Drop - Appel onElementDrop avec élément: ${elementId}`);
        }
        if (isLine) {
          // console.log(`[LINE LOG] Drop - Position initiale stockée: x=${elementStartPos.current.x}, y=${elementStartPos.current.y}`);
          // console.log(`[LINE LOG] Drop - Offset appliqué: x=${currentDragOffset.current.x}, y=${currentDragOffset.current.y}`);
          // console.log(`[LINE LOG] Drop - Position finale calculée: x=${finalX}, y=${finalY}`);
          // console.log(`[LINE LOG] Drop - Appel onElementDrop avec élément: ${elementId}`);
        }
        onElementDrop(elementId, {
          x: finalX,
          y: finalY
        });
      } else {
        console.warn('[DEBUG] onElementDrop not called - elementId:', elementId, 'onElementDrop:', !!onElementDrop);
      }
      setDragOffset({
        x: 0,
        y: 0
      });
      setDraggedElementId(null);
      elementStartPos.current = {
        x: 0,
        y: 0
      };
      currentDragOffset.current = {
        x: 0,
        y: 0
      };

      // Nettoyer les event listeners
      document.removeEventListener('mousemove', handleMouseMove);
      document.removeEventListener('mouseup', _handleMouseUp);
      currentDragData.current = null;
    };

    // Stocker les références pour le nettoyage
    currentDragData.current = {
      handleMouseMove: handleMouseMove,
      handleMouseUp: _handleMouseUp,
      elementId: elementId,
      elementType: elementType
    };
    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', _handleMouseUp);
  }, [snapToGridValue, onElementMove, onElementDrop, zoom, canvasWidth, canvasHeight]);
  var handleDragStart = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (e, elementId, elementRect) {
    e.dataTransfer.setData('text/plain', elementId);
    e.dataTransfer.effectAllowed = 'move';
    dragStartPos.current = {
      x: e.clientX - elementRect.left,
      y: e.clientY - elementRect.top
    };
  }, []);
  var handleDragOver = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
  }, []);
  var handleDrop = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (e, canvasRect, elementRect) {
    e.preventDefault();
    var elementId = e.dataTransfer.getData('text/plain');
    if (!elementId) return;
    var dropX = (e.clientX - canvasRect.left - dragStartPos.current.x) / zoom;
    var dropY = (e.clientY - canvasRect.top - dragStartPos.current.y) / zoom;
    var effectiveCanvasWidth = canvasRect ? canvasRect.width / zoom : canvasWidth;
    var effectiveCanvasHeight = canvasRect ? canvasRect.height / zoom : canvasHeight;
    var snappedX = Math.max(0, Math.min(effectiveCanvasWidth - elementRect.width, snapValue(dropX, false)));
    var snappedY = Math.max(0, Math.min(effectiveCanvasHeight - elementRect.height, snapValue(dropY, true)));
    if (onElementDrop) {
      onElementDrop(elementId, {
        x: snappedX,
        y: snappedY
      });
    }
  }, [snapToGridValue, onElementDrop, zoom, canvasWidth, canvasHeight]);
  return {
    isDragging: isDragging,
    dragOffset: dragOffset,
    draggedElementId: draggedElementId,
    handleMouseDown: handleMouseDown,
    handleDragStart: handleDragStart,
    handleDragOver: handleDragOver,
    handleDrop: handleDrop
  };
};

/***/ }),

/***/ 232:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   W: () => (/* binding */ useHistory)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(540);
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useHistory = function useHistory() {
  var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  var _options$maxHistorySi = options.maxHistorySize,
    maxHistorySize = _options$maxHistorySi === void 0 ? 50 : _options$maxHistorySi;
  var _useState = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)([]),
    _useState2 = _slicedToArray(_useState, 2),
    history = _useState2[0],
    setHistory = _useState2[1];
  var _useState3 = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(-1),
    _useState4 = _slicedToArray(_useState3, 2),
    currentIndex = _useState4[0],
    setCurrentIndex = _useState4[1];
  var isUndoRedoRef = (0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(false);
  var addToHistory = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (state) {
    if (isUndoRedoRef.current) {
      isUndoRedoRef.current = false;
      return;
    }
    setHistory(function (prevHistory) {
      // Supprimer tout ce qui vient après l'index actuel
      var newHistory = prevHistory.slice(0, currentIndex + 1);

      // Ajouter le nouvel état
      newHistory.push(state);

      // Limiter la taille de l'historique
      if (newHistory.length > maxHistorySize) {
        newHistory.shift();
        setCurrentIndex(newHistory.length - 1);
        return newHistory;
      }
      setCurrentIndex(newHistory.length - 1);
      return newHistory;
    });
  }, [currentIndex, maxHistorySize]);
  var undo = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    if (currentIndex > 0) {
      isUndoRedoRef.current = true;
      setCurrentIndex(function (prev) {
        return prev - 1;
      });
      return history[currentIndex - 1];
    }
    return null;
  }, [currentIndex, history]);
  var redo = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    if (currentIndex < history.length - 1) {
      isUndoRedoRef.current = true;
      setCurrentIndex(function (prev) {
        return prev + 1;
      });
      return history[currentIndex + 1];
    }
    return null;
  }, [currentIndex, history]);
  var canUndo = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    return currentIndex > 0;
  }, [currentIndex]);
  var canRedo = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    return currentIndex < history.length - 1;
  }, [currentIndex]);
  var clearHistory = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    setHistory([]);
    setCurrentIndex(-1);
  }, []);
  var getCurrentState = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    return currentIndex >= 0 ? history[currentIndex] : null;
  }, [currentIndex, history]);
  return {
    addToHistory: addToHistory,
    undo: undo,
    redo: redo,
    canUndo: canUndo,
    canRedo: canRedo,
    clearHistory: clearHistory,
    getCurrentState: getCurrentState,
    historySize: history.length,
    currentIndex: currentIndex
  };
};

/***/ }),

/***/ 364:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   E: () => (/* binding */ useContextMenu)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(540);
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useContextMenu = function useContextMenu() {
  var _useState = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(null),
    _useState2 = _slicedToArray(_useState, 2),
    contextMenu = _useState2[0],
    setContextMenu = _useState2[1];
  var _useState3 = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(false),
    _useState4 = _slicedToArray(_useState3, 2),
    isAnimating = _useState4[0],
    setIsAnimating = _useState4[1];
  var showContextMenu = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (x, y, items) {
    // Ajuster la position pour éviter que le menu sorte de l'écran
    var menuWidth = 180; // Largeur approximative du menu
    var menuHeight = items.length * 36; // Hauteur approximative

    var adjustedX = x;
    var adjustedY = y;

    // Ajuster horizontalement
    if (x + menuWidth > window.innerWidth) {
      adjustedX = x - menuWidth;
    }

    // Ajuster verticalement
    if (y + menuHeight > window.innerHeight) {
      adjustedY = y - menuHeight;
    }

    // S'assurer que le menu reste dans les limites
    adjustedX = Math.max(0, Math.min(adjustedX, window.innerWidth - menuWidth));
    adjustedY = Math.max(0, Math.min(adjustedY, window.innerHeight - menuHeight));
    setContextMenu({
      x: adjustedX,
      y: adjustedY,
      items: items
    });
  }, []);
  var hideContextMenu = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    if (contextMenu) {
      setIsAnimating(true);
      // Attendre la fin de l'animation avant de masquer complètement
      setTimeout(function () {
        setContextMenu(null);
        setIsAnimating(false);
      }, 150); // Durée de l'animation
    }
  }, [contextMenu]);
  var handleContextMenuAction = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (action) {
    hideContextMenu();
    return action;
  }, [hideContextMenu]);
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    var handleClickOutside = function handleClickOutside(e) {
      if (contextMenu && !e.target.closest('.context-menu')) {
        hideContextMenu();
      }
    };
    var handleEscape = function handleEscape(e) {
      if (e.key === 'Escape' && contextMenu) {
        hideContextMenu();
      }
    };
    if (contextMenu) {
      document.addEventListener('mousedown', handleClickOutside);
      document.addEventListener('keydown', handleEscape);
    }
    return function () {
      document.removeEventListener('mousedown', handleClickOutside);
      document.removeEventListener('keydown', handleEscape);
    };
  }, [contextMenu, hideContextMenu]);
  return {
    contextMenu: contextMenu,
    showContextMenu: showContextMenu,
    hideContextMenu: hideContextMenu,
    handleContextMenuAction: handleContextMenuAction,
    isAnimating: isAnimating
  };
};

/***/ }),

/***/ 430:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   i: () => (/* binding */ useClipboard)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(540);
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useClipboard = function useClipboard(_ref) {
  var onPaste = _ref.onPaste;
  var _useState = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(null),
    _useState2 = _slicedToArray(_useState, 2),
    clipboardData = _useState2[0],
    setClipboardData = _useState2[1];
  var copy = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (data) {
    setClipboardData(data);
    // Ici on pourrait aussi utiliser l'API Clipboard du navigateur
    // mais pour la compatibilité, on utilise un état interne
  }, []);
  var paste = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    if (clipboardData && onPaste) {
      onPaste(clipboardData);
    }
    return clipboardData;
  }, [clipboardData, onPaste]);
  var cut = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (data) {
    copy(data);
    // La suppression sera gérée par le composant parent
    return data;
  }, [copy]);
  var hasData = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    return clipboardData !== null;
  }, [clipboardData]);
  var clear = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    setClipboardData(null);
  }, []);
  return {
    copy: copy,
    paste: paste,
    cut: cut,
    hasData: hasData,
    clear: clear,
    clipboardData: clipboardData
  };
};

/***/ }),

/***/ 455:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   l: () => (/* binding */ elementCustomizationService)
/* harmony export */ });
/* unused harmony export ElementCustomizationService */
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Service de personnalisation des éléments
 * Fournit des utilitaires pour la gestion des propriétés d'éléments
 */
var ElementCustomizationService = /*#__PURE__*/function () {
  function ElementCustomizationService() {
    _classCallCheck(this, ElementCustomizationService);
    this.propertyValidators = new Map();
    this.propertyPresets = new Map();
    this.propertyGroups = new Map();
    this.initDefaults();
  }

  /**
   * Initialise les validateurs, presets et groupes par défaut
   */
  return _createClass(ElementCustomizationService, [{
    key: "initDefaults",
    value: function initDefaults() {
      var _this = this;
      // Validateurs de propriétés
      this.propertyValidators.set('numeric', function (value) {
        return parseFloat(value) || 0;
      });
      this.propertyValidators.set('positiveNumeric', function (value) {
        return Math.max(0, parseFloat(value) || 0);
      });
      this.propertyValidators.set('percentage', function (value) {
        return Math.max(0, Math.min(100, parseFloat(value) || 100));
      });
      this.propertyValidators.set('angle', function (value) {
        return ((parseFloat(value) || 0) % 360 + 360) % 360;
      });
      this.propertyValidators.set('color', function (value) {
        return _this.validateColor(value);
      });
      this.propertyValidators.set('fontSize', function (value) {
        return Math.max(8, Math.min(72, parseInt(value) || 14));
      });
      this.propertyValidators.set('borderWidth', function (value) {
        return Math.max(0, Math.min(20, parseInt(value) || 0));
      });
      this.propertyValidators.set('borderStyle', function (value) {
        var validStyles = ['solid', 'dashed', 'dotted', 'double'];
        return validStyles.includes(value) ? value : 'solid';
      });
      this.propertyValidators.set('borderRadius', function (value) {
        return Math.max(0, Math.min(100, parseInt(value) || 0));
      });

      // Presets de couleurs
      this.propertyPresets.set('colors', {
        slate: ['#f8fafc', '#f1f5f9', '#e2e8f0', '#cbd5e1', '#94a3b8', '#64748b', '#475569', '#334155', '#1e293b'],
        blue: ['#eff6ff', '#dbeafe', '#bfdbfe', '#93c5fd', '#60a5fa', '#3b82f6', '#2563eb', '#1d4ed8', '#1e40af'],
        green: ['#f0fdf4', '#dcfce7', '#bbf7d0', '#86efac', '#4ade80', '#22c55e', '#16a34a', '#15803d', '#166534'],
        red: ['#fef2f2', '#fee2e2', '#fecaca', '#fca5a5', '#f87171', '#ef4444', '#dc2626', '#b91c1c', '#991b1b']
      });

      // Groupes de propriétés
      this.propertyGroups.set('position', ['x', 'y']);
      this.propertyGroups.set('dimensions', ['width', 'height']);
      this.propertyGroups.set('typography', ['fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'color', 'textAlign']);
      this.propertyGroups.set('appearance', ['backgroundColor', 'borderColor', 'borderWidth', 'borderRadius']);
      this.propertyGroups.set('effects', ['opacity', 'shadow', 'brightness', 'contrast', 'saturate']);
      this.propertyGroups.set('transform', ['rotation', 'scale']);
    }

    /**
     * Valide une valeur de couleur
     */
  }, {
    key: "validateColor",
    value: function validateColor(value) {
      if (!value) return '#000000';

      // Vérifier si c'est un code hex valide
      if (/^#[0-9A-Fa-f]{6}$/.test(value) || /^#[0-9A-Fa-f]{3}$/.test(value)) {
        return value;
      }

      // Vérifier si c'est un nom de couleur CSS valide
      var tempElement = document.createElement('div');
      tempElement.style.color = value;
      return tempElement.style.color || '#000000';
    }

    /**
     * Valide une propriété selon son type et applique des corrections automatiques
     */
  }, {
    key: "validateProperty",
    value: function validateProperty(property, value) {
      // Pour les propriétés boolean, retourner la valeur telle quelle
      if (typeof value === 'boolean') {
        return value;
      }

      // Pour les propriétés de colonnes (tableaux), retourner la valeur telle quelle
      if (property.startsWith('columns.')) {
        return value;
      }

      // Chercher un validateur pour cette propriété
      var validator = this.propertyValidators.get(property);
      if (validator) {
        try {
          return validator(value);
        } catch (error) {
          return this.getDefaultValue(property);
        }
      }

      // Validation spécifique selon le type de propriété
      if (this.isNumericProperty(property)) {
        return this.validateNumericProperty(property, value);
      }
      if (this.isColorProperty(property)) {
        return this.validateColorProperty(value);
      }
      if (this.isTextStyleProperty(property)) {
        return this.validateTextStyleProperty(property, value);
      }

      // Si pas de validateur spécifique, retourner la valeur telle quelle
      return value;
    }

    /**
     * Vérifie si une propriété est numérique
     */
  }, {
    key: "isNumericProperty",
    value: function isNumericProperty(property) {
      var numericProps = ['x', 'y', 'width', 'height', 'fontSize', 'opacity', 'lineHeight', 'letterSpacing', 'zIndex', 'borderWidth', 'borderRadius', 'rotation', 'padding'];
      return numericProps.includes(property);
    }

    /**
     * Vérifie si une propriété est une couleur
     */
  }, {
    key: "isColorProperty",
    value: function isColorProperty(property) {
      var colorProps = ['color', 'backgroundColor', 'borderColor'];
      return colorProps.includes(property);
    }

    /**
     * Vérifie si une propriété est un style de texte
     */
  }, {
    key: "isTextStyleProperty",
    value: function isTextStyleProperty(property) {
      var textProps = ['fontWeight', 'textAlign', 'textDecoration', 'textTransform', 'borderStyle'];
      return textProps.includes(property);
    }

    /**
     * Valide une propriété numérique
     */
  }, {
    key: "validateNumericProperty",
    value: function validateNumericProperty(property, value) {
      if (value === null || value === undefined || value === '') {
        return this.getDefaultValue(property);
      }
      var numericValue;
      if (typeof value === 'string') {
        numericValue = parseFloat(value);
        if (isNaN(numericValue)) {
          return this.getDefaultValue(property);
        }
      } else if (typeof value === 'number') {
        numericValue = value;
      } else {
        return this.getDefaultValue(property);
      }

      // Appliquer les contraintes selon la propriété
      var constraints = {
        fontSize: {
          min: 8,
          max: 72
        },
        opacity: {
          min: 0,
          max: 1
        },
        lineHeight: {
          min: 0.5,
          max: 3
        },
        letterSpacing: {
          min: -5,
          max: 10
        },
        zIndex: {
          min: -100,
          max: 1000
        },
        borderWidth: {
          min: 0,
          max: 20
        },
        borderRadius: {
          min: 0,
          max: 100
        },
        rotation: {
          min: -180,
          max: 180
        },
        padding: {
          min: 0,
          max: 100
        }
      };
      if (constraints[property]) {
        var _constraints$property = constraints[property],
          min = _constraints$property.min,
          max = _constraints$property.max;
        numericValue = Math.max(min, Math.min(max, numericValue));
      }
      return numericValue;
    }

    /**
     * Valide une propriété de couleur
     */
  }, {
    key: "validateColorProperty",
    value: function validateColorProperty(value) {
      if (!value) return '#000000';
      if (value === 'transparent') return value;

      // Vérifier si c'est un code hex valide
      if (/^#[0-9A-Fa-f]{6}$/.test(value) || /^#[0-9A-Fa-f]{3}$/.test(value)) {
        return value;
      }

      // Vérifier si c'est un nom de couleur CSS valide
      var tempElement = document.createElement('div');
      tempElement.style.color = value;
      var computedColor = tempElement.style.color;

      // Si le navigateur reconnaît la couleur, la retourner
      if (computedColor && computedColor !== '') {
        return value;
      }
      return '#000000';
    }

    /**
     * Valide une propriété de style de texte
     */
  }, {
    key: "validateTextStyleProperty",
    value: function validateTextStyleProperty(property, value) {
      var validations = {
        fontWeight: ['normal', 'bold', '100', '200', '300', '400', '500', '600', '700', '800', '900'],
        textAlign: ['left', 'center', 'right', 'justify'],
        textDecoration: ['none', 'underline', 'overline', 'line-through'],
        textTransform: ['none', 'capitalize', 'uppercase', 'lowercase'],
        borderStyle: ['solid', 'dashed', 'dotted', 'double', 'none']
      };
      if (validations[property] && validations[property].includes(value)) {
        return value;
      }

      // Valeurs par défaut
      var defaults = {
        fontWeight: 'normal',
        textAlign: 'left',
        textDecoration: 'none',
        textTransform: 'none',
        borderStyle: 'solid'
      };
      return defaults[property] || value;
    }

    /**
     * Obtient la valeur par défaut pour une propriété
     */
  }, {
    key: "getDefaultValue",
    value: function getDefaultValue(property) {
      var defaults = {
        x: 0,
        y: 0,
        width: 100,
        height: 50,
        fontSize: 14,
        opacity: 1,
        lineHeight: 1.2,
        letterSpacing: 0,
        zIndex: 0,
        borderWidth: 0,
        borderRadius: 0,
        rotation: 0,
        padding: 0,
        color: '#333333',
        backgroundColor: 'transparent',
        borderColor: '#dddddd',
        fontWeight: 'normal',
        textAlign: 'left',
        textDecoration: 'none',
        textTransform: 'none',
        borderStyle: 'solid'
      };
      return defaults[property] || null;
    }

    /**
     * Obtient les presets pour une catégorie
     */
  }, {
    key: "getPresets",
    value: function getPresets(category) {
      return this.propertyPresets.get(category) || {};
    }

    /**
     * Obtient les propriétés d'un groupe
     */
  }, {
    key: "getPropertiesInGroup",
    value: function getPropertiesInGroup(groupName) {
      return this.propertyGroups.get(groupName) || [];
    }

    /**
     * Applique un preset à un ensemble de propriétés
     */
  }, {
    key: "applyPreset",
    value: function applyPreset(presetName, currentProperties) {
      var presets = {
        // Presets de style de texte
        'text-title': {
          fontSize: 24,
          fontWeight: 'bold',
          textAlign: 'center',
          color: '#1e293b'
        },
        'text-subtitle': {
          fontSize: 18,
          fontWeight: 'bold',
          textAlign: 'left',
          color: '#334155'
        },
        'text-body': {
          fontSize: 14,
          fontWeight: 'normal',
          textAlign: 'left',
          color: '#475569'
        },
        'text-caption': {
          fontSize: 12,
          fontWeight: 'normal',
          textAlign: 'left',
          color: '#64748b'
        },
        // Presets de formes
        'shape-rounded': {
          borderRadius: 8,
          borderWidth: 1,
          borderColor: 'transparent'
        },
        'shape-circle': {
          borderRadius: 50,
          borderWidth: 1,
          borderColor: 'transparent'
        },
        'shape-square': {
          borderRadius: 0,
          borderWidth: 1,
          borderColor: 'transparent'
        },
        // Presets d'effets
        'effect-shadow-soft': {
          shadow: true,
          shadowColor: '#000000',
          shadowOffsetX: 1,
          shadowOffsetY: 1,
          opacity: 90
        },
        'effect-shadow-strong': {
          shadow: true,
          shadowColor: '#000000',
          shadowOffsetX: 3,
          shadowOffsetY: 3,
          opacity: 85
        },
        'effect-glow': {
          shadow: true,
          shadowColor: '#2563eb',
          shadowOffsetX: 0,
          shadowOffsetY: 0,
          opacity: 95
        },
        // Presets de couleurs
        'color-primary': {
          backgroundColor: '#2563eb',
          color: '#ffffff'
        },
        'color-secondary': {
          backgroundColor: '#64748b',
          color: '#ffffff'
        },
        'color-success': {
          backgroundColor: '#16a34a',
          color: '#ffffff'
        },
        'color-warning': {
          backgroundColor: '#ca8a04',
          color: '#ffffff'
        },
        'color-error': {
          backgroundColor: '#dc2626',
          color: '#ffffff'
        }
      };
      return presets[presetName] || {};
    }

    /**
     * Réinitialise les propriétés aux valeurs par défaut
     */
  }, {
    key: "getDefaultProperties",
    value: function getDefaultProperties() {
      var elementType = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'text';
      // Propriétés communes à tous les éléments
      var defaults = {
        // Propriétés communes
        x: 50,
        y: 50,
        width: 100,
        height: 50,
        opacity: 100,
        rotation: 0,
        scale: 100,
        visible: true,
        // Apparence
        backgroundColor: 'transparent',
        borderColor: 'transparent',
        borderWidth: 0,
        borderStyle: 'solid',
        borderRadius: 0,
        // Typographie (disponible pour tous les éléments)
        color: '#1e293b',
        fontFamily: 'Inter, sans-serif',
        fontSize: 14,
        fontWeight: 'normal',
        fontStyle: 'normal',
        textAlign: 'left',
        textDecoration: 'none',
        // Contenu (pour éléments texte)
        text: 'Texte',
        // Images
        src: '',
        alt: '',
        objectFit: 'cover',
        // Effets
        shadow: false,
        shadowColor: '#000000',
        shadowOffsetX: 2,
        shadowOffsetY: 2,
        brightness: 100,
        contrast: 100,
        saturate: 100,
        // Propriétés spécifiques aux tableaux
        showHeaders: true,
        showBorders: true,
        headers: ['Produit', 'Qté', 'Prix'],
        dataSource: 'order_items',
        columns: {
          image: true,
          name: true,
          sku: false,
          quantity: true,
          price: true,
          total: true
        },
        showSubtotal: false,
        showShipping: true,
        showTaxes: true,
        showDiscount: false,
        showTotal: false,
        // Propriétés pour les barres de progression
        progressColor: '#3b82f6',
        progressValue: 75,
        // Propriétés pour les codes
        lineColor: '#64748b',
        lineWidth: 2,
        // Propriétés pour les types de document
        documentType: 'invoice',
        // Propriétés pour les logos et images
        imageUrl: '',
        // Propriétés d'espacement et mise en page
        spacing: 8,
        layout: 'vertical',
        alignment: 'left',
        fit: 'contain'
      };

      // Ajustements mineurs selon le type pour une meilleure UX
      var typeAdjustments = {
        'text': {
          width: 150,
          height: 30
        },
        'image': {
          width: 150,
          height: 100
        },
        'rectangle': {
          backgroundColor: '#f1f5f9',
          borderWidth: 1,
          width: 150,
          height: 80
        },
        'product_table': {
          width: 300,
          height: 150
        },
        'customer_info': {
          width: 200,
          height: 100
        },
        'company_logo': {
          width: 100,
          height: 60
        },
        'order_number': {
          width: 150,
          height: 30
        },
        'company_info': {
          width: 200,
          height: 80
        },
        'document_type': {
          width: 120,
          height: 40
        },
        'watermark': {
          width: 300,
          height: 200,
          opacity: 10,
          text: 'CONFIDENTIEL'
        },
        'progress-bar': {
          width: 200,
          height: 20
        },
        'barcode': {
          width: 150,
          height: 60
        },
        'qrcode': {
          width: 80,
          height: 80
        },
        'icon': {
          width: 50,
          height: 50
        },
        'line': {
          width: 200,
          height: 2
        }
      };
      return _objectSpread(_objectSpread({}, defaults), typeAdjustments[elementType] || {});
    }

    /**
     * Calcule les propriétés calculées (readonly)
     */
  }, {
    key: "getComputedProperties",
    value: function getComputedProperties(properties) {
      return {
        // Position absolue avec rotation
        absoluteX: properties.x + properties.width / 2,
        absoluteY: properties.y + properties.height / 2,
        // Dimensions avec échelle
        scaledWidth: properties.width * (properties.scale / 100),
        scaledHeight: properties.height * (properties.scale / 100),
        // Styles CSS calculés
        cssTransform: "rotate(".concat(properties.rotation, "deg) scale(").concat(properties.scale / 100, ")"),
        cssFilter: "brightness(".concat(properties.brightness, "%) contrast(").concat(properties.contrast, "%) saturate(").concat(properties.saturate, "%)"),
        cssBoxShadow: properties.shadow ? "".concat(properties.shadowOffsetX || 0, "px ").concat(properties.shadowOffsetY || 0, "px 4px ").concat(properties.shadowColor || '#000000') : 'none'
      };
    }

    /**
     * Vérifie si une propriété peut être animée
     */
  }, {
    key: "isAnimatable",
    value: function isAnimatable(property) {
      var animatableProperties = ['x', 'y', 'width', 'height', 'rotation', 'scale', 'opacity', 'brightness', 'contrast', 'saturate'];
      return animatableProperties.includes(property);
    }

    /**
     * Obtient les contraintes d'une propriété
     */
  }, {
    key: "getPropertyConstraints",
    value: function getPropertyConstraints(property) {
      var constraints = {
        x: {
          min: -1000,
          max: 2000,
          step: 1
        },
        y: {
          min: -1000,
          max: 2000,
          step: 1
        },
        width: {
          min: 1,
          max: 2000,
          step: 1
        },
        height: {
          min: 1,
          max: 2000,
          step: 1
        },
        fontSize: {
          min: 8,
          max: 72,
          step: 1
        },
        borderWidth: {
          min: 0,
          max: 20,
          step: 1
        },
        borderRadius: {
          min: 0,
          max: 100,
          step: 1
        },
        rotation: {
          min: -180,
          max: 180,
          step: 1
        },
        scale: {
          min: 10,
          max: 200,
          step: 5
        },
        opacity: {
          min: 0,
          max: 100,
          step: 1
        },
        brightness: {
          min: 0,
          max: 200,
          step: 5
        },
        contrast: {
          min: 0,
          max: 200,
          step: 5
        },
        saturate: {
          min: 0,
          max: 200,
          step: 5
        },
        shadowOffsetX: {
          min: -50,
          max: 50,
          step: 1
        },
        shadowOffsetY: {
          min: -50,
          max: 50,
          step: 1
        }
      };
      return constraints[property] || {};
    }
  }]);
}();

// Instance singleton du service
var elementCustomizationService = new ElementCustomizationService();

/***/ }),

/***/ 553:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   f: () => (/* binding */ useZoom)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(540);
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useZoom = function useZoom(_ref) {
  var _ref$initialZoom = _ref.initialZoom,
    initialZoom = _ref$initialZoom === void 0 ? 1 : _ref$initialZoom,
    _ref$minZoom = _ref.minZoom,
    minZoom = _ref$minZoom === void 0 ? 0.1 : _ref$minZoom,
    _ref$maxZoom = _ref.maxZoom,
    maxZoom = _ref$maxZoom === void 0 ? 3 : _ref$maxZoom,
    _ref$zoomStep = _ref.zoomStep,
    zoomStep = _ref$zoomStep === void 0 ? 0.1 : _ref$zoomStep;
  var _useState = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(initialZoom),
    _useState2 = _slicedToArray(_useState, 2),
    zoom = _useState2[0],
    setZoom = _useState2[1];
  var _useState3 = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)({
      x: 0,
      y: 0
    }),
    _useState4 = _slicedToArray(_useState3, 2),
    zoomOrigin = _useState4[0],
    setZoomOrigin = _useState4[1];
  var zoomIn = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    setZoom(function (prevZoom) {
      return Math.min(prevZoom + zoomStep, maxZoom);
    });
  }, [maxZoom, zoomStep]);
  var zoomOut = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    setZoom(function (prevZoom) {
      return Math.max(prevZoom - zoomStep, minZoom);
    });
  }, [minZoom, zoomStep]);
  var setZoomLevel = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (newZoom) {
    var clampedZoom = Math.max(minZoom, Math.min(maxZoom, newZoom));
    setZoom(clampedZoom);
  }, [minZoom, maxZoom]);
  var resetZoom = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    setZoom(initialZoom);
  }, [initialZoom]);
  var zoomToFit = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (canvasWidth, canvasHeight, containerWidth, containerHeight) {
    var scaleX = containerWidth / canvasWidth;
    var scaleY = containerHeight / canvasHeight;
    var newZoom = Math.min(scaleX, scaleY, maxZoom);
    setZoom(Math.max(newZoom, minZoom));
  }, [minZoom, maxZoom]);
  var zoomToPoint = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (pointX, pointY, zoomFactor) {
    var newZoom = Math.max(minZoom, Math.min(maxZoom, zoom * zoomFactor));
    setZoomOrigin({
      x: pointX,
      y: pointY
    });
    setZoom(newZoom);
  }, [zoom, minZoom, maxZoom]);
  var zoomToSelection = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (selectedElements, canvasWidth, canvasHeight, containerWidth, containerHeight) {
    if (!selectedElements || selectedElements.length === 0) return;

    // Calculer le rectangle englobant de tous les éléments sélectionnés
    var minX = Infinity,
      minY = Infinity,
      maxX = -Infinity,
      maxY = -Infinity;
    selectedElements.forEach(function (element) {
      minX = Math.min(minX, element.x);
      minY = Math.min(minY, element.y);
      maxX = Math.max(maxX, element.x + element.width);
      maxY = Math.max(maxY, element.y + element.height);
    });
    var selectionWidth = maxX - minX;
    var selectionHeight = maxY - minY;

    // Ajouter une marge de 20% autour de la sélection
    var margin = 0.2;
    var paddedWidth = selectionWidth * (1 + margin);
    var paddedHeight = selectionHeight * (1 + margin);

    // Calculer le zoom nécessaire pour faire tenir la sélection dans le conteneur
    var scaleX = containerWidth / paddedWidth;
    var scaleY = containerHeight / paddedHeight;
    var newZoom = Math.min(scaleX, scaleY, maxZoom);

    // S'assurer que le zoom est dans les limites
    var clampedZoom = Math.max(minZoom, Math.min(maxZoom, newZoom));

    // Calculer le centre de la sélection
    var selectionCenterX = minX + selectionWidth / 2;
    var selectionCenterY = minY + selectionHeight / 2;

    // Calculer le décalage pour centrer la sélection dans le conteneur
    var containerCenterX = containerWidth / 2;
    var containerCenterY = containerHeight / 2;

    // Le décalage nécessaire pour centrer la sélection
    var offsetX = containerCenterX - selectionCenterX * clampedZoom;
    var offsetY = containerCenterY - selectionCenterY * clampedZoom;
    setZoom(clampedZoom);
    setZoomOrigin({
      x: offsetX,
      y: offsetY
    });
  }, [minZoom, maxZoom]);
  var getTransformStyle = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    return {
      transform: "scale(".concat(zoom, ")"),
      transformOrigin: "".concat(zoomOrigin.x, "px ").concat(zoomOrigin.y, "px")
    };
  }, [zoom, zoomOrigin]);
  var screenToCanvas = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (screenX, screenY) {
    return {
      x: (screenX - zoomOrigin.x) / zoom,
      y: (screenY - zoomOrigin.y) / zoom
    };
  }, [zoom, zoomOrigin]);
  var canvasToScreen = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (canvasX, canvasY) {
    return {
      x: canvasX * zoom + zoomOrigin.x,
      y: canvasY * zoom + zoomOrigin.y
    };
  }, [zoom, zoomOrigin]);
  return {
    zoom: zoom,
    zoomOrigin: zoomOrigin,
    zoomIn: zoomIn,
    zoomOut: zoomOut,
    setZoomLevel: setZoomLevel,
    resetZoom: resetZoom,
    zoomToFit: zoomToFit,
    zoomToPoint: zoomToPoint,
    zoomToSelection: zoomToSelection,
    getTransformStyle: getTransformStyle,
    screenToCanvas: screenToCanvas,
    canvasToScreen: canvasToScreen
  };
};

/***/ }),

/***/ 630:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   s: () => (/* binding */ useResize)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(540);
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useResize = function useResize(_ref) {
  var onElementResize = _ref.onElementResize,
    _ref$snapToGrid = _ref.snapToGrid,
    snapToGrid = _ref$snapToGrid === void 0 ? true : _ref$snapToGrid,
    _ref$gridSize = _ref.gridSize,
    gridSize = _ref$gridSize === void 0 ? 10 : _ref$gridSize,
    _ref$minWidth = _ref.minWidth,
    minWidth = _ref$minWidth === void 0 ? 20 : _ref$minWidth,
    _ref$minHeight = _ref.minHeight,
    minHeight = _ref$minHeight === void 0 ? 20 : _ref$minHeight,
    _ref$zoom = _ref.zoom,
    zoom = _ref$zoom === void 0 ? 1 : _ref$zoom,
    _ref$canvasRect = _ref.canvasRect,
    canvasRect = _ref$canvasRect === void 0 ? null : _ref$canvasRect,
    _ref$canvasWidth = _ref.canvasWidth,
    canvasWidth = _ref$canvasWidth === void 0 ? 595 : _ref$canvasWidth,
    _ref$canvasHeight = _ref.canvasHeight,
    canvasHeight = _ref$canvasHeight === void 0 ? 842 : _ref$canvasHeight,
    _ref$guides = _ref.guides,
    guides = _ref$guides === void 0 ? {
      horizontal: [],
      vertical: []
    } : _ref$guides,
    _ref$snapToGuides = _ref.snapToGuides,
    snapToGuides = _ref$snapToGuides === void 0 ? true : _ref$snapToGuides,
    _ref$elementType = _ref.elementType,
    elementType = _ref$elementType === void 0 ? null : _ref$elementType;
  var _useState = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(false),
    _useState2 = _slicedToArray(_useState, 2),
    isResizing = _useState2[0],
    setIsResizing = _useState2[1];
  var _useState3 = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(null),
    _useState4 = _slicedToArray(_useState3, 2),
    resizeHandle = _useState4[0],
    setResizeHandle = _useState4[1];
  var resizeStartPos = (0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)({
    x: 0,
    y: 0
  });
  var originalRect = (0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)({
    x: 0,
    y: 0,
    width: 0,
    height: 0
  });
  var snapToGridValue = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (value) {
    if (!snapToGrid) return value;
    return Math.round(value / gridSize) * gridSize;
  }, [snapToGrid, gridSize]);
  var snapToGuidesValue = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (value) {
    var isHorizontal = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
    if (!snapToGuides) return value;
    var guideArray = isHorizontal ? guides.horizontal : guides.vertical;
    var snapTolerance = 5; // pixels
    var _iterator = _createForOfIteratorHelper(guideArray),
      _step;
    try {
      for (_iterator.s(); !(_step = _iterator.n()).done;) {
        var guide = _step.value;
        if (Math.abs(value - guide) <= snapTolerance) {
          return guide;
        }
      }
    } catch (err) {
      _iterator.e(err);
    } finally {
      _iterator.f();
    }
    return value;
  }, [snapToGuides, guides]);
  var snapValue = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (value) {
    var isHorizontal = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
    var snapped = value;

    // Appliquer l'aimantation à la grille d'abord
    snapped = snapToGridValue(snapped);

    // Puis appliquer l'aimantation aux guides
    snapped = snapToGuidesValue(snapped, isHorizontal);
    return snapped;
  }, [snapToGridValue, snapToGuidesValue]);
  var handleResizeStart = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (e, handle, elementRect) {
    var canvasRectParam = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
    var zoomLevel = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 1;
    e.preventDefault();
    e.stopPropagation();

    // Vérifier que l'élément source existe encore dans le DOM
    if (!e.target || !e.target.isConnected) {
      console.warn('Resize handle target no longer exists in DOM');
      return;
    }

    // Vérifier que l'élément parent existe encore
    if (!e.target.parentNode || !e.target.parentNode.isConnected) {
      console.warn('Resize handle parent no longer exists in DOM');
      return;
    }
    setIsResizing(true);
    setResizeHandle(handle);

    // Ajuster les coordonnées pour le zoom
    var currentCanvasRect = canvasRectParam || canvasRect || {
      left: 0,
      top: 0
    };
    var currentZoom = zoomLevel || zoom || 1;
    resizeStartPos.current = {
      x: (e.clientX - currentCanvasRect.left) / currentZoom,
      y: (e.clientY - currentCanvasRect.top) / currentZoom
    };
    originalRect.current = _objectSpread({}, elementRect);
    var handleMouseMove = function handleMouseMove(moveEvent) {
      var mouseX = (moveEvent.clientX - currentCanvasRect.left) / currentZoom;
      var mouseY = (moveEvent.clientY - currentCanvasRect.top) / currentZoom;
      var deltaX = mouseX - resizeStartPos.current.x;
      var deltaY = mouseY - resizeStartPos.current.y;
      var newRect = _objectSpread({}, originalRect.current);

      // Pour les dividers et lignes, empêcher la modification de la hauteur seulement
      var isFixedHeight = elementType === 'divider' || elementType === 'line';
      switch (handle) {
        case 'nw':
          if (!isFixedHeight) {
            newRect.x = snapValue(originalRect.current.x + deltaX, false);
            newRect.y = snapValue(originalRect.current.y + deltaY, true);
            newRect.width = snapValue(originalRect.current.width - deltaX, false);
            newRect.height = snapValue(originalRect.current.height - deltaY, true);
          } else {
            newRect.x = snapValue(originalRect.current.x + deltaX, false);
            newRect.width = snapValue(originalRect.current.width - deltaX, false);
          }
          break;
        case 'ne':
          if (!isFixedHeight) {
            newRect.y = snapValue(originalRect.current.y + deltaY, true);
            newRect.width = snapValue(originalRect.current.width + deltaX, false);
            newRect.height = snapValue(originalRect.current.height - deltaY, true);
          } else {
            newRect.width = snapValue(originalRect.current.width + deltaX, false);
          }
          break;
        case 'sw':
          if (!isFixedHeight) {
            newRect.x = snapValue(originalRect.current.x + deltaX, false);
            newRect.width = snapValue(originalRect.current.width - deltaX, false);
            newRect.height = snapValue(originalRect.current.height + deltaY, true);
          } else {
            newRect.x = snapValue(originalRect.current.x + deltaX, false);
            newRect.width = snapValue(originalRect.current.width - deltaX, false);
          }
          break;
        case 'se':
          if (!isFixedHeight) {
            newRect.width = snapValue(originalRect.current.width + deltaX, false);
            newRect.height = snapValue(originalRect.current.height + deltaY, true);
          } else {
            newRect.width = snapValue(originalRect.current.width + deltaX, false);
          }
          break;
        case 'n':
          if (!isFixedHeight) {
            newRect.y = snapValue(originalRect.current.y + deltaY, true);
            newRect.height = snapValue(originalRect.current.height - deltaY, true);
          }
          break;
        case 's':
          if (!isFixedHeight) {
            newRect.height = snapValue(originalRect.current.height + deltaY, true);
          }
          break;
        case 'w':
          newRect.x = snapValue(originalRect.current.x + deltaX, false);
          newRect.width = snapValue(originalRect.current.width - deltaX, false);
          break;
        case 'e':
          newRect.width = snapValue(originalRect.current.width + deltaX, false);
          break;
        default:
          break;
      }

      // Appliquer les contraintes de taille minimale
      if (newRect.width < minWidth) {
        if (handle.includes('w')) {
          newRect.x = originalRect.current.x + originalRect.current.width - minWidth;
        }
        newRect.width = minWidth;
      }
      if (newRect.height < minHeight) {
        if (handle.includes('n')) {
          newRect.y = originalRect.current.y + originalRect.current.height - minHeight;
        }
        newRect.height = minHeight;
      }

      // Appliquer les contraintes du canvas
      var effectiveCanvasWidth = canvasRectParam ? canvasRectParam.width / currentZoom : canvasWidth;
      var effectiveCanvasHeight = canvasRectParam ? canvasRectParam.height / currentZoom : canvasHeight;
      newRect.x = Math.max(0, Math.min(effectiveCanvasWidth - newRect.width, newRect.x));
      newRect.y = Math.max(0, Math.min(effectiveCanvasHeight - newRect.height, newRect.y));
      if (onElementResize) {
        onElementResize(newRect);
      }
    };
    var _handleMouseUp = function handleMouseUp() {
      setIsResizing(false);
      setResizeHandle(null);
      document.removeEventListener('mousemove', handleMouseMove);
      document.removeEventListener('mouseup', _handleMouseUp);
    };
    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', _handleMouseUp);
  }, [snapToGridValue, minWidth, minHeight, onElementResize, zoom, canvasRect, canvasWidth, canvasHeight]);
  return {
    isResizing: isResizing,
    resizeHandle: resizeHandle,
    handleResizeStart: handleResizeStart
  };
};

/***/ }),

/***/ 698:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   C: () => (/* binding */ useSelection)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(540);
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useSelection = function useSelection(_ref) {
  var onSelectionChange = _ref.onSelectionChange,
    _ref$multiSelect = _ref.multiSelect,
    multiSelect = _ref$multiSelect === void 0 ? true : _ref$multiSelect;
  var _useState = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)([]),
    _useState2 = _slicedToArray(_useState, 2),
    selectedElements = _useState2[0],
    setSelectedElements = _useState2[1];
  var _useState3 = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(null),
    _useState4 = _slicedToArray(_useState3, 2),
    selectionBox = _useState4[0],
    setSelectionBox = _useState4[1];
  var selectElement = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (elementId) {
    var addToSelection = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
    if (multiSelect && addToSelection) {
      setSelectedElements(function (prev) {
        var isAlreadySelected = prev.includes(elementId);
        var newSelection = isAlreadySelected ? prev.filter(function (id) {
          return id !== elementId;
        }) : [].concat(_toConsumableArray(prev), [elementId]);
        onSelectionChange === null || onSelectionChange === void 0 || onSelectionChange(newSelection);
        return newSelection;
      });
    } else {
      setSelectedElements([elementId]);
      onSelectionChange === null || onSelectionChange === void 0 || onSelectionChange([elementId]);
    }
  }, [multiSelect, onSelectionChange]);
  var selectAll = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (elementIds) {
    setSelectedElements(elementIds);
    onSelectionChange === null || onSelectionChange === void 0 || onSelectionChange(elementIds);
  }, [onSelectionChange]);
  var clearSelection = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    setSelectedElements([]);
    onSelectionChange === null || onSelectionChange === void 0 || onSelectionChange([]);
  }, [onSelectionChange]);
  var isSelected = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (elementId) {
    return selectedElements.includes(elementId);
  }, [selectedElements]);
  var startSelectionBox = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (startX, startY) {
    setSelectionBox({
      startX: startX,
      startY: startY,
      endX: startX,
      endY: startY
    });
  }, []);
  var updateSelectionBox = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (endX, endY) {
    setSelectionBox(function (prev) {
      return prev ? _objectSpread(_objectSpread({}, prev), {}, {
        endX: endX,
        endY: endY
      }) : null;
    });
  }, []);
  var endSelectionBox = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (elements) {
    if (!selectionBox) return;
    var startX = selectionBox.startX,
      startY = selectionBox.startY,
      endX = selectionBox.endX,
      endY = selectionBox.endY;
    var minX = Math.min(startX, endX);
    var maxX = Math.max(startX, endX);
    var minY = Math.min(startY, endY);
    var maxY = Math.max(startY, endY);
    var selectedInBox = elements.filter(function (element) {
      var elementCenterX = element.x + element.width / 2;
      var elementCenterY = element.y + element.height / 2;
      return elementCenterX >= minX && elementCenterX <= maxX && elementCenterY >= minY && elementCenterY <= maxY;
    }).map(function (element) {
      return element.id;
    });
    if (selectedInBox.length > 0) {
      if (multiSelect) {
        setSelectedElements(function (prev) {
          var newSelection = _toConsumableArray(new Set([].concat(_toConsumableArray(prev), _toConsumableArray(selectedInBox))));
          onSelectionChange === null || onSelectionChange === void 0 || onSelectionChange(newSelection);
          return newSelection;
        });
      } else {
        setSelectedElements(selectedInBox);
        onSelectionChange === null || onSelectionChange === void 0 || onSelectionChange(selectedInBox);
      }
    }
    setSelectionBox(null);
  }, [selectionBox, multiSelect, onSelectionChange]);
  var deleteSelected = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    // Cette fonction retourne les IDs à supprimer, la logique de suppression
    // sera gérée par le composant parent
    return _toConsumableArray(selectedElements);
  }, [selectedElements]);
  var duplicateSelected = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    // Cette fonction retourne les IDs à dupliquer, la logique de duplication
    // sera gérée par le composant parent
    return _toConsumableArray(selectedElements);
  }, [selectedElements]);
  return {
    selectedElements: selectedElements,
    selectionBox: selectionBox,
    selectElement: selectElement,
    selectAll: selectAll,
    clearSelection: clearSelection,
    isSelected: isSelected,
    startSelectionBox: startSelectionBox,
    updateSelectionBox: updateSelectionBox,
    endSelectionBox: endSelectionBox,
    deleteSelected: deleteSelected,
    duplicateSelected: duplicateSelected
  };
};

/***/ }),

/***/ 700:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   k: () => (/* binding */ useGlobalSettings)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(540);
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useGlobalSettings = function useGlobalSettings() {
  var _useState = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)({
      // Paramètres généraux du canvas
      defaultCanvasWidth: 210,
      defaultCanvasHeight: 297,
      defaultCanvasUnit: 'mm',
      defaultOrientation: 'portrait',
      canvasBackgroundColor: '#ffffff',
      canvasShowTransparency: false,
      containerBackgroundColor: '#f8f9fa',
      containerShowTransparency: false,
      // Marges de sécurité
      marginTop: 10,
      marginRight: 10,
      marginBottom: 10,
      marginLeft: 10,
      showMargins: true,
      // Paramètres de grille
      showGrid: true,
      gridSize: 10,
      gridColor: '#e0e0e0',
      gridOpacity: 30,
      // Aimantation
      snapToGrid: true,
      snapToElements: true,
      snapToMargins: true,
      snapTolerance: 5,
      // Lignes guides
      showGuides: true,
      lockGuides: false,
      // Paramètres de zoom et navigation
      defaultZoom: '100',
      minZoom: 10,
      maxZoom: 500,
      zoomStep: 25,
      panWithMouse: true,
      smoothZoom: true,
      showZoomIndicator: true,
      zoomWithWheel: false,
      zoomToSelection: true,
      // Paramètres de sélection et manipulation
      showResizeHandles: true,
      handleSize: 8,
      handleColor: '#007cba',
      enableRotation: true,
      rotationStep: 15,
      rotationSnap: true,
      multiSelect: true,
      selectAllShortcut: true,
      showSelectionBounds: true,
      copyPasteEnabled: true,
      duplicateOnDrag: false,
      // Paramètres d'export et qualité
      exportQuality: 'print',
      exportFormat: 'pdf',
      compressImages: true,
      imageQuality: 85,
      maxImageSize: 2048,
      includeMetadata: true,
      pdfAuthor: '',
      pdfSubject: '',
      autoCrop: false,
      embedFonts: true,
      optimizeForWeb: true,
      // Paramètres avancés
      enableHardwareAcceleration: true,
      limitFps: true,
      maxFps: 60,
      autoSaveEnabled: true,
      autoSaveInterval: 30,
      autoSaveVersions: 10,
      undoLevels: 50,
      redoLevels: 50,
      enableKeyboardShortcuts: true,
      debugMode: false,
      showFps: false,
      // Anciens paramètres (pour compatibilité)
      resizeHandleSize: 12,
      resizeHandleColor: '#007cba',
      resizeHandleBorderColor: '#ffffff',
      resizeHandleOffset: -6,
      selectionBorderWidth: 2,
      selectionBorderColor: 'var(--primary-color)',
      selectionShadowOpacity: 0.1,
      selectionBorderSpacing: 2,
      resizeZoneSize: 16,
      resizeZoneOpacity: 0.1,
      showResizeHandlesLegacy: true,
      showResizeZones: true,
      selectionAnimation: true,
      defaultTextColor: '#000000',
      defaultBackgroundColor: '#ffffff',
      defaultFontSize: 14,
      // Paramètres AJAX
      ajaxurl: window.ajaxurl || '',
      nonce: window.pdfBuilderNonce || ''
    }),
    _useState2 = _slicedToArray(_useState, 2),
    settings = _useState2[0],
    setSettings = _useState2[1];

  // Fonction pour récupérer les paramètres WordPress
  var getWordPressSettings = function getWordPressSettings() {
    if (window.pdfBuilderCanvasSettings) {
      return window.pdfBuilderCanvasSettings;
    }
    return null;
  };

  // Fonction pour récupérer ajaxurl et nonce
  var getAjaxSettings = function getAjaxSettings() {
    // Essayer d'abord les variables globales définies par WordPress
    if (window.pdfBuilderAjax) {
      return {
        ajaxurl: window.pdfBuilderAjax.ajaxurl || '',
        nonce: window.pdfBuilderAjax.nonce || ''
      };
    }
    // Fallback vers les variables individuelles
    return {
      ajaxurl: window.ajaxurl || '',
      nonce: window.pdfBuilderNonce || window.pdf_builder_nonce || ''
    };
  };

  // Charger les paramètres depuis WordPress ou localStorage au montage
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    var wpSettings = getWordPressSettings();
    var ajaxSettings = getAjaxSettings();
    if (wpSettings) {
      // Utiliser les paramètres WordPress
      setSettings(function (prev) {
        return _objectSpread(_objectSpread(_objectSpread({}, prev), ajaxSettings), {}, {
          // Ajouter ajaxurl et nonce

          // Paramètres généraux du canvas
          defaultCanvasWidth: wpSettings.default_canvas_width || prev.defaultCanvasWidth,
          defaultCanvasHeight: wpSettings.default_canvas_height || prev.defaultCanvasHeight,
          defaultCanvasUnit: wpSettings.default_canvas_unit || prev.defaultCanvasUnit,
          defaultOrientation: wpSettings.default_orientation || prev.defaultOrientation,
          canvasBackgroundColor: wpSettings.canvas_background_color || prev.canvasBackgroundColor,
          canvasShowTransparency: wpSettings.canvas_show_transparency !== undefined ? wpSettings.canvas_show_transparency : prev.canvasShowTransparency,
          containerBackgroundColor: wpSettings.container_background_color || prev.containerBackgroundColor,
          containerShowTransparency: wpSettings.container_show_transparency !== undefined ? wpSettings.container_show_transparency : prev.containerShowTransparency,
          // Marges de sécurité
          marginTop: wpSettings.margin_top || prev.marginTop,
          marginRight: wpSettings.margin_right || prev.marginRight,
          marginBottom: wpSettings.margin_bottom || prev.marginBottom,
          marginLeft: wpSettings.margin_left || prev.marginLeft,
          showMargins: wpSettings.show_margins !== undefined ? wpSettings.show_margins : prev.showMargins,
          // Paramètres de grille
          showGrid: wpSettings.show_grid !== undefined ? wpSettings.show_grid : prev.showGrid,
          gridSize: wpSettings.grid_size || prev.gridSize,
          gridColor: wpSettings.grid_color || prev.gridColor,
          gridOpacity: wpSettings.grid_opacity || prev.gridOpacity,
          // Aimantation
          snapToGrid: wpSettings.snap_to_grid !== undefined ? wpSettings.snap_to_grid : prev.snapToGrid,
          snapToElements: wpSettings.snap_to_elements !== undefined ? wpSettings.snap_to_elements : prev.snapToElements,
          snapToMargins: wpSettings.snap_to_margins !== undefined ? wpSettings.snap_to_margins : prev.snapToMargins,
          snapTolerance: wpSettings.snap_tolerance || prev.snapTolerance,
          // Lignes guides
          showGuides: wpSettings.show_guides !== undefined ? wpSettings.show_guides : prev.showGuides,
          lockGuides: wpSettings.lock_guides !== undefined ? wpSettings.lock_guides : prev.lockGuides,
          // Paramètres de zoom et navigation
          defaultZoom: wpSettings.default_zoom || prev.defaultZoom,
          minZoom: wpSettings.min_zoom || prev.minZoom,
          maxZoom: wpSettings.max_zoom || prev.maxZoom,
          zoomStep: wpSettings.zoom_step || prev.zoomStep,
          panWithMouse: wpSettings.pan_with_mouse !== undefined ? wpSettings.pan_with_mouse : prev.panWithMouse,
          smoothZoom: wpSettings.smooth_zoom !== undefined ? wpSettings.smooth_zoom : prev.smoothZoom,
          showZoomIndicator: wpSettings.show_zoom_indicator !== undefined ? wpSettings.show_zoom_indicator : prev.showZoomIndicator,
          zoomWithWheel: wpSettings.zoom_with_wheel !== undefined ? wpSettings.zoom_with_wheel : prev.zoomWithWheel,
          zoomToSelection: wpSettings.zoom_to_selection !== undefined ? wpSettings.zoom_to_selection : prev.zoomToSelection,
          // Paramètres de sélection et manipulation
          showResizeHandles: true,
          // Forcer à true pour corriger le bug des poignées
          handleSize: wpSettings.handle_size || prev.handleSize,
          handleColor: wpSettings.handle_color || prev.handleColor,
          enableRotation: wpSettings.enable_rotation !== undefined ? wpSettings.enable_rotation : prev.enableRotation,
          rotationStep: wpSettings.rotation_step || prev.rotationStep,
          rotationSnap: wpSettings.rotation_snap !== undefined ? wpSettings.rotation_snap : prev.rotationSnap,
          multiSelect: wpSettings.multi_select !== undefined ? wpSettings.multi_select : prev.multiSelect,
          selectAllShortcut: wpSettings.select_all_shortcut !== undefined ? wpSettings.select_all_shortcut : prev.selectAllShortcut,
          showSelectionBounds: wpSettings.show_selection_bounds !== undefined ? wpSettings.show_selection_bounds : prev.showSelectionBounds,
          copyPasteEnabled: wpSettings.copy_paste_enabled !== undefined ? wpSettings.copy_paste_enabled : prev.copyPasteEnabled,
          duplicateOnDrag: wpSettings.duplicate_on_drag !== undefined ? wpSettings.duplicate_on_drag : prev.duplicateOnDrag,
          // Paramètres d'export et qualité
          exportQuality: wpSettings.export_quality || prev.exportQuality,
          exportFormat: wpSettings.export_format || prev.exportFormat,
          compressImages: wpSettings.compress_images !== undefined ? wpSettings.compress_images : prev.compressImages,
          imageQuality: wpSettings.image_quality || prev.imageQuality,
          maxImageSize: wpSettings.max_image_size || prev.maxImageSize,
          includeMetadata: wpSettings.include_metadata !== undefined ? wpSettings.include_metadata : prev.includeMetadata,
          pdfAuthor: wpSettings.pdf_author || prev.pdfAuthor,
          pdfSubject: wpSettings.pdf_subject || prev.pdfSubject,
          autoCrop: wpSettings.auto_crop !== undefined ? wpSettings.auto_crop : prev.autoCrop,
          embedFonts: wpSettings.embed_fonts !== undefined ? wpSettings.embed_fonts : prev.embedFonts,
          optimizeForWeb: wpSettings.optimize_for_web !== undefined ? wpSettings.optimize_for_web : prev.optimizeForWeb,
          // Paramètres avancés
          enableHardwareAcceleration: wpSettings.enable_hardware_acceleration !== undefined ? wpSettings.enable_hardware_acceleration : prev.enableHardwareAcceleration,
          limitFps: wpSettings.limit_fps !== undefined ? wpSettings.limit_fps : prev.limitFps,
          maxFps: wpSettings.max_fps || prev.maxFps,
          autoSaveEnabled: wpSettings.auto_save_enabled !== undefined ? wpSettings.auto_save_enabled : prev.autoSaveEnabled,
          autoSaveInterval: wpSettings.auto_save_interval || prev.autoSaveInterval,
          autoSaveVersions: wpSettings.auto_save_versions || prev.autoSaveVersions,
          undoLevels: wpSettings.undo_levels || prev.undoLevels,
          redoLevels: wpSettings.redo_levels || prev.redoLevels,
          enableKeyboardShortcuts: wpSettings.enable_keyboard_shortcuts !== undefined ? wpSettings.enable_keyboard_shortcuts : prev.enableKeyboardShortcuts,
          debugMode: wpSettings.debug_mode !== undefined ? wpSettings.debug_mode : prev.debugMode,
          showFps: wpSettings.show_fps !== undefined ? wpSettings.show_fps : prev.showFps,
          // Anciens paramètres (pour compatibilité)
          resizeHandleSize: wpSettings.canvas_handle_size || prev.resizeHandleSize,
          resizeHandleColor: wpSettings.canvas_handle_color || prev.resizeHandleColor,
          resizeHandleBorderColor: wpSettings.canvas_handle_hover_color || prev.resizeHandleBorderColor,
          selectionBorderWidth: wpSettings.canvas_border_width || prev.selectionBorderWidth,
          selectionBorderColor: wpSettings.canvas_border_color || prev.selectionBorderColor,
          selectionBorderSpacing: wpSettings.canvas_border_spacing || prev.selectionBorderSpacing,
          showResizeHandlesLegacy: wpSettings.canvas_resize_handles_enabled !== undefined ? wpSettings.canvas_resize_handles_enabled : prev.showResizeHandlesLegacy,
          showResizeZones: wpSettings.canvas_element_borders_enabled !== undefined ? wpSettings.canvas_element_borders_enabled : prev.showResizeZones,
          defaultTextColor: wpSettings.default_text_color || prev.defaultTextColor,
          defaultBackgroundColor: wpSettings.default_background_color || prev.defaultBackgroundColor,
          defaultFontSize: wpSettings.default_font_size || prev.defaultFontSize
        });
      });
    } else {
      // Fallback vers localStorage si les paramètres WordPress ne sont pas disponibles
      var _ajaxSettings = getAjaxSettings();
      var savedSettings = localStorage.getItem('pdf-builder-global-settings');
      if (savedSettings) {
        try {
          var parsedSettings = JSON.parse(savedSettings);
          // Forcer showResizeHandles à true pour corriger le bug des poignées
          parsedSettings.showResizeHandles = true;
          setSettings(function (prev) {
            return _objectSpread(_objectSpread(_objectSpread({}, prev), _ajaxSettings), parsedSettings);
          });
        } catch (error) {
          setSettings(function (prev) {
            return _objectSpread(_objectSpread({}, prev), _ajaxSettings);
          });
        }
      } else {
        setSettings(function (prev) {
          return _objectSpread(_objectSpread({}, prev), _ajaxSettings);
        });
      }
    }
  }, []);

  // Écouter les mises à jour de paramètres en temps réel
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    var handleSettingsUpdate = function handleSettingsUpdate(event) {
      refreshSettings();
    };
    window.addEventListener('pdfBuilderSettingsUpdated', handleSettingsUpdate);
    return function () {
      window.removeEventListener('pdfBuilderSettingsUpdated', handleSettingsUpdate);
    };
  }, []);

  // Appliquer les paramètres aux variables CSS
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    var root = document.documentElement;

    // Appliquer les paramètres des points de redimensionnement
    root.style.setProperty('--resize-handle-size', "".concat(settings.resizeHandleSize, "px"));
    root.style.setProperty('--resize-handle-color', settings.resizeHandleColor);
    root.style.setProperty('--resize-handle-border-color', settings.resizeHandleBorderColor);
    root.style.setProperty('--resize-handle-offset', "".concat(settings.resizeHandleOffset, "px"));

    // Appliquer les paramètres des bordures de sélection
    root.style.setProperty('--selection-border-width', "".concat(settings.selectionBorderWidth, "px"));
    root.style.setProperty('--selection-border-color', settings.selectionBorderColor);
    root.style.setProperty('--selection-border-spacing', "".concat(settings.selectionBorderSpacing, "px"));
    root.style.setProperty('--selection-shadow-opacity', settings.selectionShadowOpacity);

    // Appliquer les paramètres des zones de redimensionnement
    root.style.setProperty('--resize-zone-size', "".concat(settings.resizeZoneSize, "px"));
    root.style.setProperty('--resize-zone-opacity', settings.resizeZoneOpacity);

    // Appliquer les paramètres généraux
    root.style.setProperty('--show-resize-handles', settings.showResizeHandles ? 'block' : 'none');
    root.style.setProperty('--show-resize-zones', settings.showResizeZones ? 'auto' : 'none');
    root.style.setProperty('--selection-animation', settings.selectionAnimation ? 'all 0.2s ease' : 'none');
  }, [settings]);
  var updateSettings = /*#__PURE__*/function () {
    var _ref = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(newSettings) {
      return _regenerator().w(function (_context) {
        while (1) switch (_context.n) {
          case 0:
            setSettings(function (prev) {
              var updated = _objectSpread(_objectSpread({}, prev), newSettings);
              // Forcer showResizeHandles à true pour corriger le bug des poignées
              updated.showResizeHandles = true;
              // Sauvegarder dans localStorage pour la compatibilité
              localStorage.setItem('pdf-builder-global-settings', JSON.stringify(updated));

              // Sauvegarder dans WordPress via AJAX
              var ajaxSettings = getAjaxSettings();
              if (ajaxSettings.ajaxurl && ajaxSettings.nonce) {
                // Convertir les paramètres camelCase vers snake_case pour WordPress
                var wpSettings = {
                  show_grid: updated.showGrid,
                  grid_size: updated.gridSize,
                  grid_color: updated.gridColor,
                  grid_opacity: updated.gridOpacity,
                  snap_to_grid: updated.snapToGrid,
                  snap_to_elements: updated.snapToElements,
                  snap_to_margins: updated.snapToMargins,
                  snap_tolerance: updated.snapTolerance,
                  show_guides: updated.showGuides,
                  lock_guides: updated.lockGuides,
                  default_zoom: updated.defaultZoom,
                  min_zoom: updated.minZoom,
                  max_zoom: updated.maxZoom,
                  zoom_step: updated.zoomStep,
                  pan_with_mouse: updated.panWithMouse,
                  smooth_zoom: updated.smoothZoom,
                  show_zoom_indicator: updated.showZoomIndicator,
                  zoom_with_wheel: updated.zoomWithWheel,
                  zoom_to_selection: updated.zoomToSelection,
                  show_resize_handles: updated.showResizeHandles,
                  handle_size: updated.handleSize,
                  handle_color: updated.handleColor,
                  enable_rotation: updated.enableRotation,
                  rotation_step: updated.rotationStep,
                  rotation_snap: updated.rotationSnap,
                  multi_select: updated.multiSelect,
                  select_all_shortcut: updated.selectAllShortcut,
                  show_selection_bounds: updated.showSelectionBounds,
                  copy_paste_enabled: updated.copyPasteEnabled,
                  duplicate_on_drag: updated.duplicateOnDrag,
                  export_quality: updated.exportQuality,
                  export_format: updated.exportFormat,
                  compress_images: updated.compressImages,
                  image_quality: updated.imageQuality,
                  max_image_size: updated.maxImageSize,
                  include_metadata: updated.includeMetadata,
                  pdf_author: updated.pdfAuthor,
                  pdf_subject: updated.pdfSubject,
                  auto_crop: updated.autoCrop,
                  embed_fonts: updated.embedFonts,
                  optimize_for_web: updated.optimizeForWeb,
                  enable_hardware_acceleration: updated.enableHardwareAcceleration,
                  limit_fps: updated.limitFps,
                  max_fps: updated.maxFps,
                  auto_save_enabled: updated.autoSaveEnabled,
                  auto_save_interval: updated.autoSaveInterval,
                  auto_save_versions: updated.autoSaveVersions,
                  undo_levels: updated.undoLevels,
                  redo_levels: updated.redoLevels,
                  enable_keyboard_shortcuts: updated.enableKeyboardShortcuts,
                  debug_mode: updated.debugMode,
                  show_fps: updated.showFps,
                  // Paramètres canvas
                  default_canvas_width: updated.defaultCanvasWidth,
                  default_canvas_height: updated.defaultCanvasHeight,
                  default_canvas_unit: updated.defaultCanvasUnit,
                  canvas_background_color: updated.canvasBackgroundColor,
                  canvas_show_transparency: updated.canvasShowTransparency,
                  margin_top: updated.marginTop,
                  margin_right: updated.marginRight,
                  margin_bottom: updated.marginBottom,
                  margin_left: updated.marginLeft,
                  show_margins: updated.showMargins
                };

                // Faire l'appel AJAX en arrière-plan (sans bloquer)
                fetch(ajaxSettings.ajaxurl + '?action=pdf_builder_save_settings_page', {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                  },
                  body: new URLSearchParams(_objectSpread(_objectSpread({}, wpSettings), {}, {
                    nonce: ajaxSettings.nonce
                  }))
                }).then(function (response) {
                  if (!response.ok) {
                    console.warn('Erreur lors de la sauvegarde des paramètres dans WordPress:', response.status);
                  } else {
                    // console.log('Paramètres sauvegardés avec succès dans WordPress');
                  }
                })["catch"](function (error) {
                  console.warn('Erreur AJAX lors de la sauvegarde des paramètres:', error);
                });
              }
              return updated;
            });
          case 1:
            return _context.a(2);
        }
      }, _callee);
    }));
    return function updateSettings(_x) {
      return _ref.apply(this, arguments);
    };
  }();
  var resetToDefaults = function resetToDefaults() {
    var defaultSettings = {
      resizeHandleSize: 12,
      resizeHandleColor: '#007cba',
      resizeHandleBorderColor: '#ffffff',
      selectionBorderWidth: 2,
      selectionBorderColor: 'var(--primary-color)',
      selectionBorderSpacing: 2,
      selectionShadowOpacity: 0.1,
      resizeZoneSize: 16,
      resizeZoneOpacity: 0.1,
      showResizeHandles: true,
      showResizeZones: true,
      selectionAnimation: true
    };
    setSettings(defaultSettings);
  };

  // Fonction pour rafraîchir les paramètres depuis WordPress (pour mise à jour en temps réel)
  var refreshSettings = function refreshSettings() {
    var wpSettings = getWordPressSettings();
    var ajaxSettings = getAjaxSettings();
    if (wpSettings) {
      setSettings(function (prev) {
        return _objectSpread(_objectSpread(_objectSpread({}, prev), ajaxSettings), {}, {
          // Ajouter ajaxurl et nonce

          // Paramètres généraux du canvas
          defaultCanvasWidth: wpSettings.default_canvas_width || prev.defaultCanvasWidth,
          defaultCanvasHeight: wpSettings.default_canvas_height || prev.defaultCanvasHeight,
          defaultCanvasUnit: wpSettings.default_canvas_unit || prev.defaultCanvasUnit,
          defaultOrientation: wpSettings.default_orientation || prev.defaultOrientation,
          canvasBackgroundColor: wpSettings.canvas_background_color || prev.canvasBackgroundColor,
          canvasShowTransparency: wpSettings.canvas_show_transparency !== undefined ? wpSettings.canvas_show_transparency : prev.canvasShowTransparency,
          containerBackgroundColor: wpSettings.container_background_color || prev.containerBackgroundColor,
          containerShowTransparency: wpSettings.container_show_transparency !== undefined ? wpSettings.container_show_transparency : prev.containerShowTransparency,
          // Marges de sécurité
          marginTop: wpSettings.margin_top || prev.marginTop,
          marginRight: wpSettings.margin_right || prev.marginRight,
          marginBottom: wpSettings.margin_bottom || prev.marginBottom,
          marginLeft: wpSettings.margin_left || prev.marginLeft,
          showMargins: wpSettings.show_margins !== undefined ? wpSettings.show_margins : prev.showMargins,
          // Paramètres de grille
          showGrid: wpSettings.show_grid !== undefined ? wpSettings.show_grid : prev.showGrid,
          gridSize: wpSettings.grid_size || prev.gridSize,
          gridColor: wpSettings.grid_color || prev.gridColor,
          gridOpacity: wpSettings.grid_opacity || prev.gridOpacity,
          // Aimantation
          snapToGrid: wpSettings.snap_to_grid !== undefined ? wpSettings.snap_to_grid : prev.snapToGrid,
          snapToElements: wpSettings.snap_to_elements !== undefined ? wpSettings.snap_to_elements : prev.snapToElements,
          snapToMargins: wpSettings.snap_to_margins !== undefined ? wpSettings.snap_to_margins : prev.snapToMargins,
          snapTolerance: wpSettings.snap_tolerance || prev.snapTolerance,
          // Lignes guides
          showGuides: wpSettings.show_guides !== undefined ? wpSettings.show_guides : prev.showGuides,
          lockGuides: wpSettings.lock_guides !== undefined ? wpSettings.lock_guides : prev.lockGuides,
          // Paramètres de zoom et navigation
          defaultZoom: wpSettings.default_zoom || prev.defaultZoom,
          minZoom: wpSettings.min_zoom || prev.minZoom,
          maxZoom: wpSettings.max_zoom || prev.maxZoom,
          zoomStep: wpSettings.zoom_step || prev.zoomStep,
          panWithMouse: wpSettings.pan_with_mouse !== undefined ? wpSettings.pan_with_mouse : prev.panWithMouse,
          smoothZoom: wpSettings.smooth_zoom !== undefined ? wpSettings.smooth_zoom : prev.smoothZoom,
          showZoomIndicator: wpSettings.show_zoom_indicator !== undefined ? wpSettings.show_zoom_indicator : prev.showZoomIndicator,
          zoomWithWheel: wpSettings.zoom_with_wheel !== undefined ? wpSettings.zoom_with_wheel : prev.zoomWithWheel,
          zoomToSelection: wpSettings.zoom_to_selection !== undefined ? wpSettings.zoom_to_selection : prev.zoomToSelection,
          // Paramètres de sélection et manipulation
          showResizeHandles: true,
          // Forcer à true pour corriger le bug des poignées
          handleSize: wpSettings.handle_size || prev.handleSize,
          handleColor: wpSettings.handle_color || prev.handleColor,
          enableRotation: wpSettings.enable_rotation !== undefined ? wpSettings.enable_rotation : prev.enableRotation,
          rotationStep: wpSettings.rotation_step || prev.rotationStep,
          rotationSnap: wpSettings.rotation_snap !== undefined ? wpSettings.rotation_snap : prev.rotationSnap,
          multiSelect: wpSettings.multi_select !== undefined ? wpSettings.multi_select : prev.multiSelect,
          selectAllShortcut: wpSettings.select_all_shortcut !== undefined ? wpSettings.select_all_shortcut : prev.selectAllShortcut,
          showSelectionBounds: wpSettings.show_selection_bounds !== undefined ? wpSettings.show_selection_bounds : prev.showSelectionBounds,
          copyPasteEnabled: wpSettings.copy_paste_enabled !== undefined ? wpSettings.copy_paste_enabled : prev.copyPasteEnabled,
          duplicateOnDrag: wpSettings.duplicate_on_drag !== undefined ? wpSettings.duplicate_on_drag : prev.duplicateOnDrag,
          // Paramètres d'export et qualité
          exportQuality: wpSettings.export_quality || prev.exportQuality,
          exportFormat: wpSettings.export_format || prev.exportFormat,
          compressImages: wpSettings.compress_images !== undefined ? wpSettings.compress_images : prev.compressImages,
          imageQuality: wpSettings.image_quality || prev.imageQuality,
          maxImageSize: wpSettings.max_image_size || prev.maxImageSize,
          includeMetadata: wpSettings.include_metadata !== undefined ? wpSettings.include_metadata : prev.includeMetadata,
          pdfAuthor: wpSettings.pdf_author || prev.pdfAuthor,
          pdfSubject: wpSettings.pdf_subject || prev.pdfSubject,
          autoCrop: wpSettings.auto_crop !== undefined ? wpSettings.auto_crop : prev.autoCrop,
          embedFonts: wpSettings.embed_fonts !== undefined ? wpSettings.embed_fonts : prev.embedFonts,
          optimizeForWeb: wpSettings.optimize_for_web !== undefined ? wpSettings.optimize_for_web : prev.optimizeForWeb,
          // Paramètres avancés
          enableHardwareAcceleration: wpSettings.enable_hardware_acceleration !== undefined ? wpSettings.enable_hardware_acceleration : prev.enableHardwareAcceleration,
          limitFps: wpSettings.limit_fps !== undefined ? wpSettings.limit_fps : prev.limitFps,
          maxFps: wpSettings.max_fps || prev.maxFps,
          autoSaveEnabled: wpSettings.auto_save_enabled !== undefined ? wpSettings.auto_save_enabled : prev.autoSaveEnabled,
          autoSaveInterval: wpSettings.auto_save_interval || prev.autoSaveInterval,
          autoSaveVersions: wpSettings.auto_save_versions || prev.autoSaveVersions,
          undoLevels: wpSettings.undo_levels || prev.undoLevels,
          redoLevels: wpSettings.redo_levels || prev.redoLevels,
          enableKeyboardShortcuts: wpSettings.enable_keyboard_shortcuts !== undefined ? wpSettings.enable_keyboard_shortcuts : prev.enableKeyboardShortcuts,
          debugMode: wpSettings.debug_mode !== undefined ? wpSettings.debug_mode : prev.debugMode,
          showFps: wpSettings.show_fps !== undefined ? wpSettings.show_fps : prev.showFps,
          // Anciens paramètres (pour compatibilité)
          resizeHandleSize: wpSettings.canvas_handle_size || prev.resizeHandleSize,
          resizeHandleColor: wpSettings.canvas_handle_color || prev.resizeHandleColor,
          resizeHandleBorderColor: wpSettings.canvas_handle_hover_color || prev.resizeHandleBorderColor,
          selectionBorderWidth: wpSettings.canvas_border_width || prev.selectionBorderWidth,
          selectionBorderColor: wpSettings.canvas_border_color || prev.selectionBorderColor,
          selectionBorderSpacing: wpSettings.canvas_border_spacing || prev.selectionBorderSpacing,
          showResizeHandlesLegacy: wpSettings.canvas_resize_handles_enabled !== undefined ? wpSettings.canvas_resize_handles_enabled : prev.showResizeHandlesLegacy,
          showResizeZones: wpSettings.canvas_element_borders_enabled !== undefined ? wpSettings.canvas_element_borders_enabled : prev.showResizeZones,
          defaultTextColor: wpSettings.default_text_color || prev.defaultTextColor,
          defaultBackgroundColor: wpSettings.default_background_color || prev.defaultBackgroundColor,
          defaultFontSize: wpSettings.default_font_size || prev.defaultFontSize
        });
      });
    }
  };
  return {
    settings: settings,
    updateSettings: updateSettings,
    resetToDefaults: resetToDefaults,
    refreshSettings: refreshSettings
  };
};

/***/ }),

/***/ 743:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   R: () => (/* binding */ useElementCustomization)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(540);
/* harmony import */ var _services_ElementCustomizationService__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(455);
/* harmony import */ var _utilities_elementPropertyRestrictions__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(779);
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }




/**
 * Hook pour gérer la personnalisation des éléments
 * Gère l'état local des propriétés et les changements en temps réel
 */
var useElementCustomization = function useElementCustomization(selectedElements, elements, onPropertyChange) {
  var _useState = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)({}),
    _useState2 = _slicedToArray(_useState, 2),
    localProperties = _useState2[0],
    setLocalProperties = _useState2[1];
  var _useState3 = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)('appearance'),
    _useState4 = _slicedToArray(_useState3, 2),
    activeTab = _useState4[0],
    setActiveTab = _useState4[1];

  // Obtenir l'élément sélectionné (mémorisé pour éviter les re-renders)
  var selectedElement = (0,react__WEBPACK_IMPORTED_MODULE_0__.useMemo)(function () {
    return selectedElements.length > 0 ? elements.find(function (el) {
      return el.id === selectedElements[0];
    }) : null;
  }, [selectedElements, elements]);

  // Synchroniser les propriétés locales avec l'élément sélectionné
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    if (selectedElement) {
      var defaultProperties = {
        // Valeurs par défaut de base
        color: '#333333',
        backgroundColor: 'transparent',
        borderColor: '#dddddd',
        borderWidth: 0,
        borderStyle: 'solid',
        borderRadius: 4,
        fontSize: 14,
        fontFamily: 'Inter',
        fontWeight: 'normal',
        fontStyle: 'normal',
        textDecoration: 'none',
        textAlign: 'left',
        lineHeight: 1.2,
        letterSpacing: 0
      };

      // Appliquer les restrictions selon le type d'élément
      var elementType = selectedElement.type;
      var restrictedDefaults = _objectSpread({}, defaultProperties);

      // Pour les propriétés restreintes, utiliser les valeurs par défaut spécifiques
      Object.keys(defaultProperties).forEach(function (property) {
        var specificDefault = (0,_utilities_elementPropertyRestrictions__WEBPACK_IMPORTED_MODULE_2__/* .getPropertyDefault */ .Dw)(elementType, property);
        if (specificDefault !== null) {
          restrictedDefaults[property] = specificDefault;
        }

        // Pour les éléments spéciaux, forcer backgroundColor à transparent
        if (_utilities_elementPropertyRestrictions__WEBPACK_IMPORTED_MODULE_2__/* .ELEMENT_TYPE_MAPPING */ .xG[elementType] === 'special' && property === 'backgroundColor') {
          restrictedDefaults[property] = 'transparent';
        }
      });
      var newProperties = _objectSpread(_objectSpread({}, restrictedDefaults), selectedElement);

      // Pour les propriétés restreintes, forcer les valeurs corrigées
      Object.keys(restrictedDefaults).forEach(function (property) {
        var isRestricted = !(0,_utilities_elementPropertyRestrictions__WEBPACK_IMPORTED_MODULE_2__/* .isPropertyAllowed */ .Qn)(elementType, property);
        if (isRestricted) {
          var correctedValue = (0,_utilities_elementPropertyRestrictions__WEBPACK_IMPORTED_MODULE_2__/* .fixInvalidProperty */ .uF)(elementType, property, newProperties[property]);
          if (correctedValue !== newProperties[property]) {
            newProperties[property] = correctedValue;
          }
        }
        // Pour les éléments spéciaux, garder backgroundColor à transparent par défaut seulement si pas défini
        else if (_utilities_elementPropertyRestrictions__WEBPACK_IMPORTED_MODULE_2__/* .ELEMENT_TYPE_MAPPING */ .xG[elementType] === 'special' && property === 'backgroundColor' && !newProperties[property]) {
          newProperties[property] = 'transparent';
        }
      });

      // Validation finale des propriétés
      Object.keys(newProperties).forEach(function (property) {
        var validation = (0,_utilities_elementPropertyRestrictions__WEBPACK_IMPORTED_MODULE_2__/* .validateProperty */ .qb)(elementType, property, newProperties[property]);
        if (!validation.valid) {
          newProperties[property] = (0,_utilities_elementPropertyRestrictions__WEBPACK_IMPORTED_MODULE_2__/* .fixInvalidProperty */ .uF)(elementType, property, newProperties[property]);
        }
      });
      setLocalProperties(newProperties);
    } else {
      setLocalProperties({});
    }
  }, [selectedElement === null || selectedElement === void 0 ? void 0 : selectedElement.id]); // Ne dépendre que de l'ID pour éviter les re-renders inutiles

  // Gestionnaire de changement de propriété avec validation
  var handlePropertyChange = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (elementId, property, value) {
    var element = elements.find(function (el) {
      return el.id === elementId;
    });
    if (!element) return;
    var validatedValue = value;

    // Validation selon le système de restrictions
    var validation = (0,_utilities_elementPropertyRestrictions__WEBPACK_IMPORTED_MODULE_2__/* .validateProperty */ .qb)(element.type, property, value);
    if (!validation.valid) {
      // Ne pas appliquer le changement si la propriété n'est pas autorisée
      return;
    }

    // Validation supplémentaire selon le type de propriété (service existant)
    if (typeof value !== 'boolean' && !property.startsWith('columns.')) {
      try {
        var serviceValidated = _services_ElementCustomizationService__WEBPACK_IMPORTED_MODULE_1__/* .elementCustomizationService */ .l.validateProperty(property, value);
        if (serviceValidated !== undefined) {
          validatedValue = serviceValidated;
        }
      } catch (error) {}
    }

    // Mettre à jour l'état local immédiatement pour l'UI
    setLocalProperties(function (prev) {
      var newProperties;
      if (property.includes('.')) {
        // Gérer les propriétés imbriquées (ex: "columns.image")
        var updateNestedProperty = function updateNestedProperty(obj, path, value) {
          var keys = path.split('.');
          var lastKey = keys.pop();
          var target = keys.reduce(function (current, key) {
            if (!current[key] || _typeof(current[key]) !== 'object') {
              current[key] = {};
            } else {
              current[key] = _objectSpread({}, current[key]); // Créer une copie pour éviter de modifier l'original
            }
            return current[key];
          }, obj);
          target[lastKey] = value;
          return obj;
        };
        newProperties = _objectSpread({}, prev);
        updateNestedProperty(newProperties, property, validatedValue);
      } else {
        newProperties = _objectSpread(_objectSpread({}, prev), {}, _defineProperty({}, property, validatedValue));
      }
      return newProperties;
    });

    // Notifier le parent pour la persistance
    onPropertyChange(elementId, property, validatedValue);
  }, [onPropertyChange, elements]);

  // Validation des valeurs de propriétés
  var validatePropertyValue = function validatePropertyValue(property, value) {
    switch (property) {
      case 'x':
      case 'y':
      case 'width':
      case 'height':
        return Math.max(0, parseInt(value) || 0);
      case 'fontSize':
        return Math.max(8, Math.min(72, parseInt(value) || 14));
      case 'lineHeight':
        return Math.max(0.5, Math.min(5, parseFloat(value) || 1.2));
      case 'letterSpacing':
        return Math.max(-5, Math.min(20, parseFloat(value) || 0));
      case 'borderWidth':
        return Math.max(0, Math.min(20, parseInt(value) || 0));
      case 'borderStyle':
        var validStyles = ['solid', 'dashed', 'dotted', 'double'];
        return validStyles.includes(value) ? value : 'solid';
      case 'borderRadius':
        return Math.max(0, Math.min(100, parseInt(value) || 0));
      case 'rotation':
        return ((parseInt(value) || 0) % 360 + 360) % 360;
      // Normaliser entre 0-359

      case 'scale':
        return Math.max(10, Math.min(200, parseInt(value) || 100));
      case 'opacity':
        return Math.max(0, Math.min(100, parseInt(value) || 100));
      case 'brightness':
      case 'contrast':
      case 'saturate':
        return Math.max(0, Math.min(200, parseInt(value) || 100));
      case 'shadowOffsetX':
      case 'shadowOffsetY':
        return Math.max(-50, Math.min(50, parseInt(value) || 0));
      default:
        return value;
    }
  };

  // Appliquer des presets de propriétés
  var applyPropertyPreset = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (elementId, preset) {
    var presets = {
      // Presets de couleurs
      'color-dark': {
        color: '#1e293b'
      },
      'color-light': {
        color: '#f8fafc'
      },
      'color-primary': {
        color: '#2563eb'
      },
      'color-success': {
        color: '#16a34a'
      },
      'color-warning': {
        color: '#ca8a04'
      },
      'color-error': {
        color: '#dc2626'
      },
      // Presets de style de texte
      'text-title': {
        fontSize: 24,
        fontWeight: 'bold',
        textAlign: 'center'
      },
      'text-subtitle': {
        fontSize: 18,
        fontWeight: 'bold',
        textAlign: 'left'
      },
      'text-body': {
        fontSize: 14,
        fontWeight: 'normal',
        textAlign: 'left'
      },
      'text-caption': {
        fontSize: 12,
        fontWeight: 'normal',
        textAlign: 'left'
      },
      // Presets de formes
      'shape-rounded': {
        borderRadius: 8
      },
      'shape-circle': {
        borderRadius: 50
      },
      'shape-square': {
        borderRadius: 0
      },
      // Presets d'effets
      'effect-shadow': {
        shadow: true,
        shadowColor: '#000000',
        shadowOffsetX: 2,
        shadowOffsetY: 2
      },
      'effect-glow': {
        shadow: true,
        shadowColor: '#2563eb',
        shadowOffsetX: 0,
        shadowOffsetY: 0
      },
      'effect-none': {
        shadow: false,
        brightness: 100,
        contrast: 100,
        saturate: 100
      }
    };
    if (presets[preset]) {
      Object.entries(presets[preset]).forEach(function (_ref) {
        var _ref2 = _slicedToArray(_ref, 2),
          property = _ref2[0],
          value = _ref2[1];
        handlePropertyChange(elementId, property, value);
      });
    }
  }, [handlePropertyChange]);

  // Réinitialiser les propriétés aux valeurs par défaut
  var resetProperties = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (elementId) {
    var defaultProperties = {
      x: 50,
      y: 50,
      width: 100,
      height: 50,
      backgroundColor: '#ffffff',
      borderColor: '#dddddd',
      borderWidth: 1,
      borderStyle: 'solid',
      borderRadius: 4,
      color: '#333333',
      fontSize: 14,
      fontFamily: 'Inter',
      fontWeight: 'normal',
      fontStyle: 'normal',
      textAlign: 'left',
      textDecoration: 'none',
      lineHeight: 1.2,
      letterSpacing: 0,
      opacity: 100,
      rotation: 0,
      scale: 100,
      shadow: false,
      brightness: 100,
      contrast: 100,
      saturate: 100
    };
    Object.entries(defaultProperties).forEach(function (_ref3) {
      var _ref4 = _slicedToArray(_ref3, 2),
        property = _ref4[0],
        value = _ref4[1];
      handlePropertyChange(elementId, property, value);
    });
  }, [handlePropertyChange]);
  return {
    localProperties: localProperties,
    activeTab: activeTab,
    setActiveTab: setActiveTab,
    selectedElement: selectedElement,
    handlePropertyChange: handlePropertyChange,
    applyPropertyPreset: applyPropertyPreset,
    resetProperties: resetProperties,
    validatePropertyValue: validatePropertyValue
  };
};

/***/ }),

/***/ 874:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   I: () => (/* binding */ useElementSynchronization)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(540);


/**
 * Hook pour gérer la synchronisation des personnalisations d'éléments
 * Gère la persistance, la validation et la synchronisation avec le backend
 */
var useElementSynchronization = function useElementSynchronization(elements, onPropertyChange, onBatchUpdate) {
  var autoSave = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : true;
  var autoSaveDelay = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 3000;
  var pendingChangesRef = (0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(new Map());
  var autoSaveTimeoutRef = (0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  var lastSavedRef = (0,react__WEBPACK_IMPORTED_MODULE_0__.useRef)(new Map());

  // Synchronisation différée pour éviter les appels trop fréquents
  var debouncedSync = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (elementId, property, value) {
    // Annuler la sauvegarde automatique précédente
    if (autoSaveTimeoutRef.current) {
      clearTimeout(autoSaveTimeoutRef.current);
    }

    // Ajouter le changement aux modifications en attente
    var key = "".concat(elementId, ".").concat(property);
    pendingChangesRef.current.set(key, {
      elementId: elementId,
      property: property,
      value: value
    });

    // Programmer une sauvegarde automatique
    if (autoSave) {
      autoSaveTimeoutRef.current = setTimeout(function () {
        syncPendingChanges();
      }, autoSaveDelay);
    }
  }, [autoSave, autoSaveDelay]);

  // Synchroniser immédiatement
  var immediateSync = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (elementId, property, value) {
    // Annuler la sauvegarde automatique
    if (autoSaveTimeoutRef.current) {
      clearTimeout(autoSaveTimeoutRef.current);
    }

    // Synchroniser immédiatement
    onPropertyChange(elementId, property, value);

    // Mettre à jour la référence de dernière sauvegarde
    var key = "".concat(elementId, ".").concat(property);
    lastSavedRef.current.set(key, value);

    // Vider les changements en attente pour cette propriété
    pendingChangesRef.current["delete"](key);
  }, [onPropertyChange]);

  // Synchroniser tous les changements en attente
  var syncPendingChanges = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    if (pendingChangesRef.current.size === 0) return;
    var changes = Array.from(pendingChangesRef.current.values());

    // Grouper les changements par élément pour optimiser
    var changesByElement = changes.reduce(function (acc, change) {
      if (!acc[change.elementId]) {
        acc[change.elementId] = {};
      }
      acc[change.elementId][change.property] = change.value;
      return acc;
    }, {});

    // Si on a une fonction de mise à jour par lot, l'utiliser
    if (onBatchUpdate && Object.keys(changesByElement).length > 1) {
      onBatchUpdate(changesByElement);
    } else {
      // Sinon, mettre à jour élément par élément
      changes.forEach(function (_ref) {
        var elementId = _ref.elementId,
          property = _ref.property,
          value = _ref.value;
        onPropertyChange(elementId, property, value);
      });
    }

    // Mettre à jour les références de dernière sauvegarde
    changes.forEach(function (_ref2) {
      var elementId = _ref2.elementId,
        property = _ref2.property,
        value = _ref2.value;
      var key = "".concat(elementId, ".").concat(property);
      lastSavedRef.current.set(key, value);
    });

    // Vider les changements en attente
    pendingChangesRef.current.clear();
  }, [onPropertyChange, onBatchUpdate]);

  // Forcer la synchronisation immédiate
  var forceSync = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    syncPendingChanges();
  }, [syncPendingChanges]);

  // Vérifier si des changements sont en attente
  var hasPendingChanges = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function () {
    return pendingChangesRef.current.size > 0;
  }, []);

  // Obtenir les changements en attente pour un élément
  var getPendingChanges = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (elementId) {
    var changes = [];
    pendingChangesRef.current.forEach(function (change, key) {
      if (change.elementId === elementId) {
        changes.push(change);
      }
    });
    return changes;
  }, []);

  // Annuler les changements en attente pour un élément
  var cancelPendingChanges = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (elementId) {
    var keysToDelete = [];
    pendingChangesRef.current.forEach(function (change, key) {
      if (change.elementId === elementId) {
        keysToDelete.push(key);
      }
    });
    keysToDelete.forEach(function (key) {
      return pendingChangesRef.current["delete"](key);
    });
  }, []);

  // Restaurer les dernières valeurs sauvegardées
  var restoreLastSaved = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (elementId, property) {
    var key = "".concat(elementId, ".").concat(property);
    var lastSavedValue = lastSavedRef.current.get(key);
    if (lastSavedValue !== undefined) {
      // Annuler le changement en attente
      pendingChangesRef.current["delete"](key);

      // Restaurer la valeur
      return lastSavedValue;
    }
    return null;
  }, []);

  // Validation des propriétés avant synchronisation
  var validateAndSync = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (elementId, property, value, validator) {
    var validatedValue = value;

    // Appliquer la validation si fournie
    if (validator) {
      validatedValue = validator(value);
    }

    // Appliquer la validation par défaut selon le type de propriété
    validatedValue = validatePropertyValue(property, validatedValue);

    // Synchroniser
    debouncedSync(elementId, property, validatedValue);
    return validatedValue;
  }, [debouncedSync]);

  // Validation des valeurs de propriétés
  var validatePropertyValue = function validatePropertyValue(property, value) {
    switch (property) {
      case 'x':
      case 'y':
      case 'width':
      case 'height':
        return Math.max(0, parseInt(value) || 0);
      case 'fontSize':
        return Math.max(8, Math.min(72, parseInt(value) || 14));
      case 'borderWidth':
        return Math.max(0, Math.min(20, parseInt(value) || 0));
      case 'borderRadius':
        return Math.max(0, Math.min(100, parseInt(value) || 0));
      case 'rotation':
        return ((parseInt(value) || 0) % 360 + 360) % 360;
      case 'scale':
        return Math.max(10, Math.min(200, parseInt(value) || 100));
      case 'opacity':
        return Math.max(0, Math.min(100, parseInt(value) || 100));
      case 'brightness':
      case 'contrast':
      case 'saturate':
        return Math.max(0, Math.min(200, parseInt(value) || 100));
      case 'shadowOffsetX':
      case 'shadowOffsetY':
        return Math.max(-50, Math.min(50, parseInt(value) || 0));
      default:
        return value;
    }
  };

  // Nettoyer les timeouts au démontage
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    return function () {
      if (autoSaveTimeoutRef.current) {
        clearTimeout(autoSaveTimeoutRef.current);
      }
    };
  }, []);

  // Synchronisation automatique lors des changements d'éléments
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    // Sauvegarder automatiquement quand les éléments changent
    if (autoSave && pendingChangesRef.current.size > 0) {
      syncPendingChanges();
    }
  }, [elements, autoSave, syncPendingChanges]);
  return {
    // Méthodes de synchronisation
    sync: debouncedSync,
    syncImmediate: immediateSync,
    syncPending: syncPendingChanges,
    forceSync: forceSync,
    // Gestion des changements en attente
    hasPendingChanges: hasPendingChanges,
    getPendingChanges: getPendingChanges,
    cancelPendingChanges: cancelPendingChanges,
    // Validation et restauration
    validateAndSync: validateAndSync,
    restoreLastSaved: restoreLastSaved,
    // État
    pendingChangesCount: pendingChangesRef.current.size
  };
};

/***/ }),

/***/ 920:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   o: () => (/* binding */ useRotation)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(540);
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useRotation = function useRotation(onElementRotate) {
  var rotationStep = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 15;
  var rotationSnap = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;
  var _useState = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(false),
    _useState2 = _slicedToArray(_useState, 2),
    isRotating = _useState2[0],
    setIsRotating = _useState2[1];
  var _useState3 = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)({
      angle: 0,
      centerX: 0,
      centerY: 0
    }),
    _useState4 = _slicedToArray(_useState3, 2),
    rotationStart = _useState4[0],
    setRotationStart = _useState4[1];
  var handleRotationStart = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (e, element) {
    e.preventDefault();
    e.stopPropagation();
    setIsRotating(true);
    var rect = e.currentTarget.getBoundingClientRect();
    var centerX = rect.left + rect.width / 2;
    var centerY = rect.top + rect.height / 2;

    // Calculer l'angle initial entre le centre de l'élément et la position de la souris
    var deltaX = e.clientX - centerX;
    var deltaY = e.clientY - centerY;
    var initialAngle = Math.atan2(deltaY, deltaX) * (180 / Math.PI);
    setRotationStart({
      angle: initialAngle - (element.rotation || 0),
      centerX: centerX,
      centerY: centerY
    });

    // Ajouter les écouteurs d'événements globaux
    var handleRotationMove = function handleRotationMove(moveEvent) {
      if (!isRotating) return;
      var deltaX = moveEvent.clientX - centerX;
      var deltaY = moveEvent.clientY - centerY;
      var newAngle = Math.atan2(deltaY, deltaX) * (180 / Math.PI);

      // Calculer la rotation relative
      newAngle = newAngle - rotationStart.angle;

      // Appliquer l'aimantation si activée
      if (rotationSnap) {
        newAngle = Math.round(newAngle / rotationStep) * rotationStep;
      }

      // Normaliser l'angle entre 0 et 360
      newAngle = (newAngle % 360 + 360) % 360;
      onElementRotate(newAngle);
    };
    var _handleRotationEnd = function handleRotationEnd() {
      setIsRotating(false);
      document.removeEventListener('mousemove', handleRotationMove);
      document.removeEventListener('mouseup', _handleRotationEnd);
    };
    document.addEventListener('mousemove', handleRotationMove);
    document.addEventListener('mouseup', _handleRotationEnd);
  }, [isRotating, rotationStart, rotationStep, rotationSnap, onElementRotate]);
  return {
    isRotating: isRotating,
    handleRotationStart: handleRotationStart
  };
};

/***/ }),

/***/ 960:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   K: () => (/* binding */ useKeyboardShortcuts)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(540);

var useKeyboardShortcuts = function useKeyboardShortcuts(_ref) {
  var onDelete = _ref.onDelete,
    onCopy = _ref.onCopy,
    onPaste = _ref.onPaste,
    onUndo = _ref.onUndo,
    onRedo = _ref.onRedo,
    onSave = _ref.onSave,
    onZoomIn = _ref.onZoomIn,
    onZoomOut = _ref.onZoomOut,
    onSelectAll = _ref.onSelectAll,
    onDeselectAll = _ref.onDeselectAll,
    onToolSelect = _ref.onToolSelect,
    onToggleGrid = _ref.onToggleGrid,
    onTogglePreview = _ref.onTogglePreview;
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    var handleKeyDown = function handleKeyDown(e) {
      // Ignorer si on est dans un champ de saisie
      if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.contentEditable === 'true') {
        return;
      }
      var isCtrl = e.ctrlKey || e.metaKey;
      switch (e.key.toLowerCase()) {
        case 'delete':
        case 'backspace':
          if (onDelete) {
            e.preventDefault();
            onDelete();
          }
          break;
        case 'c':
          if (isCtrl && onCopy) {
            e.preventDefault();
            onCopy();
          }
          break;
        case 'v':
          if (isCtrl && onPaste) {
            e.preventDefault();
            onPaste();
          }
          break;
        case 'z':
          if (isCtrl) {
            e.preventDefault();
            if (e.shiftKey && onRedo) {
              onRedo();
            } else if (onUndo) {
              onUndo();
            }
          }
          break;
        case 'y':
          if (isCtrl && onRedo) {
            e.preventDefault();
            onRedo();
          }
          break;
        case 's':
          if (isCtrl && onSave) {
            e.preventDefault();
            onSave();
          }
          break;
        case '+':
        case '=':
          if (isCtrl && onZoomIn) {
            e.preventDefault();
            onZoomIn();
          }
          break;
        case '-':
          if (isCtrl && onZoomOut) {
            e.preventDefault();
            onZoomOut();
          }
          break;
        case 'a':
          if (isCtrl && onSelectAll) {
            e.preventDefault();
            onSelectAll();
          }
          break;
        case 'd':
          if (isCtrl && onDeselectAll) {
            e.preventDefault();
            onDeselectAll();
          }
          break;
        case 'g':
          if (isCtrl && onToggleGrid) {
            e.preventDefault();
            onToggleGrid();
          }
          break;
        case 'p':
          if (isCtrl && onTogglePreview) {
            e.preventDefault();
            onTogglePreview();
          }
          break;

        // Raccourcis pour les outils
        case 'v':
          if (!isCtrl && onToolSelect) {
            e.preventDefault();
            onToolSelect('select');
          }
          break;
        case 'r':
          if (!isCtrl && onToolSelect) {
            e.preventDefault();
            onToolSelect('rectangle');
          }
          break;
        case 't':
          if (!isCtrl && onToolSelect) {
            e.preventDefault();
            onToolSelect('text');
          }
          break;
        case 'i':
          if (!isCtrl && onToolSelect) {
            e.preventDefault();
            onToolSelect('image');
          }
          break;
        case 'l':
          if (!isCtrl && onToolSelect) {
            e.preventDefault();
            onToolSelect('line');
          }
          break;
        default:
          break;
      }
    };
    document.addEventListener('keydown', handleKeyDown);
    return function () {
      document.removeEventListener('keydown', handleKeyDown);
    };
  }, [onDelete, onCopy, onPaste, onUndo, onRedo, onSave, onZoomIn, onZoomOut, onSelectAll, onDeselectAll, onToolSelect, onToggleGrid, onTogglePreview]);
};

/***/ })

}]);