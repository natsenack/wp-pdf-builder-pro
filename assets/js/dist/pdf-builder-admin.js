/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ 626:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, {
  "default": () => (/* binding */ js)
});

// NAMESPACE OBJECT: ./resources/js/hooks/index.js
var hooks_namespaceObject = {};
__webpack_require__.r(hooks_namespaceObject);
__webpack_require__.d(hooks_namespaceObject, {
  useCanvasState: () => (useCanvasState),
  useClipboard: () => (useClipboard),
  useContextMenu: () => (useContextMenu),
  useDragAndDrop: () => (useDragAndDrop),
  useHistory: () => (useHistory),
  useKeyboardShortcuts: () => (useKeyboardShortcuts),
  useResize: () => (useResize),
  useRotation: () => (useRotation),
  useSelection: () => (useSelection),
  useZoom: () => (useZoom)
});

// EXTERNAL MODULE: ./node_modules/react/index.js
var react = __webpack_require__(540);
// EXTERNAL MODULE: ./node_modules/react-dom/index.js
var react_dom = __webpack_require__(961);
;// ./resources/js/hooks/useResize.js
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
  var _useState = (0,react.useState)(false),
    _useState2 = _slicedToArray(_useState, 2),
    isResizing = _useState2[0],
    setIsResizing = _useState2[1];
  var _useState3 = (0,react.useState)(null),
    _useState4 = _slicedToArray(_useState3, 2),
    resizeHandle = _useState4[0],
    setResizeHandle = _useState4[1];
  var resizeStartPos = (0,react.useRef)({
    x: 0,
    y: 0
  });
  var originalRect = (0,react.useRef)({
    x: 0,
    y: 0,
    width: 0,
    height: 0
  });
  var snapToGridValue = (0,react.useCallback)(function (value) {
    if (!snapToGrid) return value;
    return Math.round(value / gridSize) * gridSize;
  }, [snapToGrid, gridSize]);
  var snapToGuidesValue = (0,react.useCallback)(function (value) {
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
  var snapValue = (0,react.useCallback)(function (value) {
    var isHorizontal = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
    var snapped = value;

    // Appliquer l'aimantation à la grille d'abord
    snapped = snapToGridValue(snapped);

    // Puis appliquer l'aimantation aux guides
    snapped = snapToGuidesValue(snapped, isHorizontal);
    return snapped;
  }, [snapToGridValue, snapToGuidesValue]);
  var handleResizeStart = (0,react.useCallback)(function (e, handle, elementRect) {
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
;// ./resources/js/hooks/useRotation.js
function useRotation_slicedToArray(r, e) { return useRotation_arrayWithHoles(r) || useRotation_iterableToArrayLimit(r, e) || useRotation_unsupportedIterableToArray(r, e) || useRotation_nonIterableRest(); }
function useRotation_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function useRotation_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return useRotation_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? useRotation_arrayLikeToArray(r, a) : void 0; } }
function useRotation_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function useRotation_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function useRotation_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useRotation = function useRotation(onElementRotate) {
  var rotationStep = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 15;
  var rotationSnap = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;
  var _useState = (0,react.useState)(false),
    _useState2 = useRotation_slicedToArray(_useState, 2),
    isRotating = _useState2[0],
    setIsRotating = _useState2[1];
  var _useState3 = (0,react.useState)({
      angle: 0,
      centerX: 0,
      centerY: 0
    }),
    _useState4 = useRotation_slicedToArray(_useState3, 2),
    rotationStart = _useState4[0],
    setRotationStart = _useState4[1];
  var handleRotationStart = (0,react.useCallback)(function (e, element) {
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
;// ./resources/js/components/CanvasElement.jsx
function CanvasElement_typeof(o) { "@babel/helpers - typeof"; return CanvasElement_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, CanvasElement_typeof(o); }
function CanvasElement_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function CanvasElement_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? CanvasElement_ownKeys(Object(t), !0).forEach(function (r) { CanvasElement_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : CanvasElement_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function CanvasElement_defineProperty(e, r, t) { return (r = CanvasElement_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function CanvasElement_toPropertyKey(t) { var i = CanvasElement_toPrimitive(t, "string"); return "symbol" == CanvasElement_typeof(i) ? i : i + ""; }
function CanvasElement_toPrimitive(t, r) { if ("object" != CanvasElement_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != CanvasElement_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }



var CanvasElement = function CanvasElement(_ref) {
  var _element$fields, _element$fields2, _element$fields3, _element$fields4, _element$fields5, _element$fields6, _element$fields7, _element$fields8, _element$fields9, _element$fields0, _element$fields1, _element$fields10, _element$fields11, _element$fields12, _element$fields13;
  var element = _ref.element,
    isSelected = _ref.isSelected,
    zoom = _ref.zoom,
    snapToGrid = _ref.snapToGrid,
    gridSize = _ref.gridSize,
    canvasWidth = _ref.canvasWidth,
    canvasHeight = _ref.canvasHeight,
    onSelect = _ref.onSelect,
    onUpdate = _ref.onUpdate,
    onRemove = _ref.onRemove,
    onContextMenu = _ref.onContextMenu,
    dragAndDrop = _ref.dragAndDrop,
    _ref$enableRotation = _ref.enableRotation,
    enableRotation = _ref$enableRotation === void 0 ? true : _ref$enableRotation,
    _ref$rotationStep = _ref.rotationStep,
    rotationStep = _ref$rotationStep === void 0 ? 15 : _ref$rotationStep,
    _ref$rotationSnap = _ref.rotationSnap,
    rotationSnap = _ref$rotationSnap === void 0 ? true : _ref$rotationSnap,
    _ref$guides = _ref.guides,
    guides = _ref$guides === void 0 ? {
      horizontal: [],
      vertical: []
    } : _ref$guides,
    _ref$snapToGuides = _ref.snapToGuides,
    snapToGuides = _ref$snapToGuides === void 0 ? true : _ref$snapToGuides;
  var elementRef = (0,react.useRef)(null);
  var canvasRectRef = (0,react.useRef)(null);

  // DEBUG: Logger les propriétés des tableaux produits pour comparaison avec PHP
  (0,react.useEffect)(function () {
    if (element && element.type === 'product_table' && element.id) {
      // Logging removed for production
    }
  }, [element === null || element === void 0 ? void 0 : element.id, element === null || element === void 0 ? void 0 : element.type]); // Utiliser seulement id et type pour éviter les re-renders

  var resize = useResize({
    onElementResize: function onElementResize(newRect) {
      onUpdate({
        x: newRect.x,
        y: newRect.y,
        width: newRect.width,
        height: newRect.height
      });
    },
    snapToGrid: snapToGrid,
    gridSize: gridSize,
    canvasWidth: canvasWidth,
    canvasHeight: canvasHeight,
    guides: guides,
    snapToGuides: snapToGuides,
    elementType: element.type
  });
  var rotation = useRotation(function (newRotation) {
    onUpdate({
      rotation: newRotation
    });
  }, rotationStep, rotationSnap);

  // Fonction helper pour déterminer si un élément est spécial
  var isSpecialElement = function isSpecialElement(type) {
    return ['product_table', 'customer_info', 'company_logo', 'company_info', 'order_number', 'document_type', 'progress-bar'].includes(type);
  };

  // Fonction helper pour gérer les styles de bordure des éléments spéciaux
  var getSpecialElementBorderStyle = function getSpecialElementBorderStyle(element) {
    // Pour les éléments spéciaux, forcer toujours un fond transparent
    // indépendamment des propriétés de l'élément
    return CanvasElement_objectSpread({
      backgroundColor: 'transparent',
      // Utiliser box-sizing pour que les bordures soient incluses dans les dimensions
      boxSizing: 'border-box'
    }, element.borderWidth && element.borderWidth > 0 ? {
      border: "".concat(element.borderWidth * zoom, "px ").concat(element.borderStyle || 'solid', " ").concat(element.borderColor || '#e5e7eb')
    } : {});
  };

  // Fonction helper pour obtenir les styles de tableau selon le style choisi
  var getTableStyles = function getTableStyles() {
    var tableStyle = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'default';
    var baseStyles = {
      "default": {
        headerBg: '#f8fafc',
        headerBorder: '#e2e8f0',
        rowBorder: '#000000',
        rowBg: 'transparent',
        altRowBg: '#fafbfc',
        borderWidth: 2,
        headerTextColor: '#334155',
        rowTextColor: '#334155',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 1px 3px rgba(0, 0, 0, 0.1)',
        borderRadius: '4px'
      },
      classic: {
        headerBg: '#1e293b',
        headerBorder: '#334155',
        rowBorder: '#334155',
        rowBg: 'transparent',
        altRowBg: '#ffffff',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#1e293b',
        headerFontWeight: '700',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 2px 8px rgba(0, 0, 0, 0.15)',
        borderRadius: '0px'
      },
      striped: {
        headerBg: '#3b82f6',
        headerBorder: '#2563eb',
        rowBorder: '#e2e8f0',
        rowBg: 'transparent',
        altRowBg: '#f8fafc',
        borderWidth: 1,
        headerTextColor: '#ffffff',
        rowTextColor: '#334155',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 1px 4px rgba(59, 130, 246, 0.2)',
        borderRadius: '6px'
      },
      bordered: {
        headerBg: '#ffffff',
        headerBorder: '#374151',
        rowBorder: '#d1d5db',
        rowBg: 'transparent',
        altRowBg: '#ffffff',
        borderWidth: 2,
        headerTextColor: '#111827',
        rowTextColor: '#111827',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 12px rgba(0, 0, 0, 0.1), inset 0 0 0 1px #e5e7eb',
        borderRadius: '8px'
      },
      minimal: {
        headerBg: '#ffffff',
        headerBorder: '#d1d5db',
        rowBorder: '#f3f4f6',
        rowBg: 'transparent',
        altRowBg: '#ffffff',
        borderWidth: 0.5,
        headerTextColor: '#6b7280',
        rowTextColor: '#6b7280',
        headerFontWeight: '500',
        headerFontSize: '10px',
        rowFontSize: '9px',
        shadow: 'none',
        borderRadius: '0px'
      },
      modern: {
        headerBg: '#6366f1',
        headerBorder: '#5b21b6',
        rowBorder: '#e9d5ff',
        rowBg: 'transparent',
        altRowBg: '#faf5ff',
        borderWidth: 1,
        headerTextColor: '#ffffff',
        rowTextColor: '#6b21a8',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 20px rgba(102, 126, 234, 0.25)',
        borderRadius: '8px'
      },
      // Nouveaux styles colorés
      slate_gray: {
        headerBg: '#374151',
        headerBorder: '#4b5563',
        rowBorder: '#f3f4f6',
        rowBg: 'transparent',
        altRowBg: '#f9fafb',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#374151'
      },
      coral: {
        headerBg: '#f87171',
        headerBorder: '#fca5a5',
        rowBorder: '#fef2f2',
        rowBg: 'transparent',
        altRowBg: '#fef2f2',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#dc2626',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(248, 113, 113, 0.3)',
        borderRadius: '6px'
      },
      teal: {
        headerBg: '#14b8a6',
        headerBorder: '#0d9488',
        rowBorder: '#ccfbf1',
        rowBg: 'transparent',
        altRowBg: '#f0fdfa',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#0f766e'
      },
      indigo: {
        headerBg: '#6366f1',
        headerBorder: '#818cf8',
        rowBorder: '#e0e7ff',
        rowBg: 'transparent',
        altRowBg: '#eef2ff',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#3730a3'
      },
      amber: {
        headerBg: '#f59e0b',
        headerBorder: '#fbbf24',
        rowBorder: '#fef3c7',
        rowBg: 'transparent',
        altRowBg: '#fffbeb',
        borderWidth: 1.5,
        headerTextColor: '#92400e',
        rowTextColor: '#92400e'
      }
    };
    return baseStyles[tableStyle] || baseStyles["default"];
  };

  // Gestionnaire de clic sur l'élément
  var handleMouseDown = (0,react.useCallback)(function (e) {
    e.stopPropagation();
    if (!isSelected) {
      onSelect();
      return;
    }

    // Calculer les coordonnées relatives au canvas (en tenant compte du zoom)
    var canvas = elementRef.current.closest('.canvas-zoom-wrapper');
    if (!canvas) return;
    var canvasRect = canvas.getBoundingClientRect();
    var elementRect = elementRef.current.getBoundingClientRect();

    // Ajuster pour le zoom - les coordonnées doivent être relatives au canvas non-zoomé
    var relativeRect = {
      left: (elementRect.left - canvasRect.left) / zoom,
      top: (elementRect.top - canvasRect.top) / zoom,
      width: elementRect.width / zoom,
      height: elementRect.height / zoom
    };

    // Vérifier si on clique sur une poignée de redimensionnement
    var clickX = (e.clientX - canvasRect.left) / zoom;
    var clickY = (e.clientY - canvasRect.top) / zoom;
    var handleSize = 8 / zoom; // Ajuster la taille des poignées pour le zoom
    var elementLeft = element.x;
    var elementTop = element.y;
    var elementRight = element.x + element.width;
    var elementBottom = element.y + element.height;

    // Poignées de redimensionnement (coordonnées relatives au canvas)
    var handles = [{
      name: 'nw',
      x: elementLeft,
      y: elementTop
    }, {
      name: 'ne',
      x: elementRight,
      y: elementTop
    }, {
      name: 'sw',
      x: elementLeft,
      y: elementBottom
    }, {
      name: 'se',
      x: elementRight,
      y: elementBottom
    }, {
      name: 'n',
      x: elementLeft + element.width / 2,
      y: elementTop
    }, {
      name: 's',
      x: elementLeft + element.width / 2,
      y: elementBottom
    }, {
      name: 'w',
      x: elementLeft,
      y: elementTop + element.height / 2
    }, {
      name: 'e',
      x: elementRight,
      y: elementTop + element.height / 2
    }];
    var clickedHandle = handles.find(function (handle) {
      return clickX >= handle.x - handleSize / 2 && clickX <= handle.x + handleSize / 2 && clickY >= handle.y - handleSize / 2 && clickY <= handle.y + handleSize / 2;
    });
    if (clickedHandle) {
      var _canvas = elementRef.current.closest('.canvas-zoom-wrapper');
      var _canvasRect = _canvas.getBoundingClientRect();
      resize.handleResizeStart(e, clickedHandle.name, {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      }, _canvasRect, zoom);
    } else {
      // Démarrer le drag avec les coordonnées relatives au canvas
      var _canvas2 = elementRef.current.closest('.canvas-zoom-wrapper');
      var _canvasRect2 = _canvas2.getBoundingClientRect();

      // Mettre à jour la référence du canvas
      canvasRectRef.current = _canvasRect2;
      dragAndDrop.handleMouseDown(e, element.id, {
        left: element.x,
        top: element.y,
        width: element.width,
        height: element.height
      }, _canvasRect2, zoom, element.type);
    }
  }, [isSelected, onSelect, element, zoom, resize, dragAndDrop]);

  // Gestionnaire de double-clic pour édition
  var handleDoubleClick = (0,react.useCallback)(function (e) {
    e.stopPropagation();
    if (element.type === 'text') {
      var currentText = element.content || element.text || '';
      var newText = prompt('Modifier le texte:', currentText);

      // Annuler si l'utilisateur clique sur "Annuler" ou laisse vide
      if (newText === null) {
        return;
      }

      // Utiliser la même propriété que celle actuellement utilisée par l'élément
      var textProperty = element.content !== undefined ? 'content' : 'text';
      var updates = CanvasElement_defineProperty({}, textProperty, newText);
      onUpdate(updates);
    }
  }, [element, onUpdate]);

  // Gestionnaire de clic droit
  var handleContextMenuEvent = (0,react.useCallback)(function (e) {
    e.preventDefault();
    e.stopPropagation();
    if (onContextMenu) {
      onContextMenu(e, element.id);
    }
  }, [onContextMenu, element.id]);

  // Fonction helper pour obtenir les styles spécifiques au type d'élément
  var getElementTypeStyles = function getElementTypeStyles(element, zoom, canvasWidth) {
    switch (element.type) {
      case 'text':
        return {
          fontSize: (element.fontSize || 14) * zoom,
          fontFamily: element.fontFamily || 'Arial',
          color: element.color || '#1e293b',
          fontWeight: element.fontWeight || 'normal',
          fontStyle: element.fontStyle || 'normal',
          textAlign: element.textAlign || 'left',
          textDecoration: element.textDecoration || 'none',
          lineHeight: element.lineHeight || 'normal',
          display: 'flex',
          alignItems: 'center',
          justifyContent: element.textAlign === 'center' ? 'center' : element.textAlign === 'right' ? 'flex-end' : 'flex-start',
          wordBreak: 'break-word',
          overflow: 'hidden'
        };
      case 'rectangle':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: element.borderRadius ? "".concat(element.borderRadius, "px") : '0'
        };
      case 'image':
        if (element.src || element.imageUrl) {
          return {
            backgroundImage: "url(".concat(element.src || element.imageUrl, ")"),
            backgroundSize: element.objectFit || element.fit || 'cover',
            backgroundPosition: 'center',
            backgroundRepeat: 'no-repeat'
          };
        }
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          color: '#9ca3af',
          fontSize: 12 * zoom
        };
      case 'line':
        return {
          borderTop: "".concat(element.lineWidth || element.strokeWidth || 1, "px solid ").concat(element.lineColor || element.strokeColor || '#6b7280'),
          height: "".concat(Math.max(element.lineWidth || element.strokeWidth || 1, 12), "px"),
          // Hauteur augmentée à 12px minimum pour faciliter le clic
          width: '100%',
          cursor: 'pointer',
          backgroundColor: 'transparent' // S'assurer qu'il n'y a pas de fond qui cache
        };
      case 'layout-header':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '4px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: 14 * zoom,
          fontWeight: 'bold',
          color: element.color || '#64748b'
        };
      case 'layout-footer':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '4px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: 12 * zoom,
          color: element.color || '#64748b'
        };
      case 'layout-sidebar':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '4px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: 12 * zoom,
          color: element.color || '#64748b'
        };
      case 'layout-section':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '4px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: 12 * zoom,
          color: element.color || '#64748b'
        };
      case 'layout-container':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '4px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: 12 * zoom,
          color: element.color || '#94a3b8'
        };
      case 'shape-rectangle':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '0'
        };
      case 'shape-circle':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: '50%'
        };
      case 'shape-line':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          height: '100%'
        };
      case 'shape-arrow':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          clipPath: 'polygon(0% 50%, 70% 0%, 70% 40%, 100% 40%, 100% 60%, 70% 60%, 70% 100%)'
        };
      case 'shape-triangle':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          clipPath: 'polygon(50% 0%, 0% 100%, 100% 100%)'
        };
      case 'shape-star':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          clipPath: 'polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%)'
        };
      case 'divider':
        return {
          backgroundColor: element.backgroundColor || '#e5e7eb',
          height: '16px',
          // Hauteur fixe pour les séparateurs
          cursor: 'pointer',
          borderRadius: '2px'
        };
      case 'line':
        return {
          borderTop: "".concat(element.lineWidth || element.strokeWidth || 1, "px solid ").concat(element.lineColor || element.strokeColor || '#6b7280'),
          height: "".concat(Math.max(element.lineWidth || element.strokeWidth || 1, 12), "px"),
          // Hauteur augmentée à 12px minimum pour faciliter le clic
          left: 0,
          // Les lignes s'étendent toujours sur toute la largeur du canvas
          width: "".concat(canvasWidth, "px"),
          cursor: 'pointer',
          backgroundColor: 'transparent' // S'assurer qu'il n'y a pas de fond qui cache
        };

      // Styles par défaut pour les autres types
      default:
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: 12 * zoom,
          color: element.color || '#333333'
        };
    }
  };

  // Calcul du padding pour cohérence avec le PDF
  var elementPadding = element.padding || 0;

  // Styles élément optimisés avec useMemo pour éviter les recalculs inutiles
  var elementStyles = (0,react.useMemo)(function () {
    return CanvasElement_objectSpread(CanvasElement_objectSpread({
      position: 'absolute',
      left: (element.x + elementPadding) * zoom,
      top: (element.y + elementPadding) * zoom,
      width: Math.max(1, element.width - elementPadding * 2) * zoom,
      height: Math.max(1, element.height - elementPadding * 2) * zoom,
      cursor: dragAndDrop.isDragging ? 'grabbing' : 'grab',
      userSelect: 'none',
      '--selection-border-width': '2px',
      '--selection-border-color': '#3b82f6',
      '--selection-border-spacing': '2px',
      '--selection-shadow-opacity': '0.1',
      '--show-resize-handles': isSelected ? 'block' : 'none',
      '--resize-handle-size': "".concat(10 * zoom, "px"),
      '--resize-handle-color': '#3b82f6',
      '--resize-handle-border-color': 'white',
      '--resize-handle-offset': "".concat(-5 * zoom, "px"),
      '--element-border-width': '2px',
      '--resize-zone-size': '16px',
      '--show-resize-zones': isSelected ? 'auto' : 'none'
    }, isSpecialElement(element.type) ? getSpecialElementBorderStyle(element) : {
      // Styles de base communs à tous les éléments non-spéciaux
      backgroundColor: element.backgroundOpacity && element.backgroundColor && element.backgroundColor !== 'transparent' ? element.backgroundColor + Math.round(element.backgroundOpacity * 255).toString(16).padStart(2, '0') : element.backgroundColor || 'transparent',
      border: element.borderWidth ? "".concat(element.borderWidth * zoom, "px ").concat(element.borderStyle || 'solid', " ").concat(element.borderColor || 'transparent') : 'none'
    }), {}, {
      borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '0px',
      opacity: (element.opacity || 100) / 100,
      transform: "".concat(dragAndDrop.draggedElementId === element.id ? "translate(".concat(dragAndDrop.dragOffset.x * zoom, "px, ").concat(dragAndDrop.dragOffset.y * zoom, "px) ") : '', "rotate(").concat(element.rotation || 0, "deg) scale(").concat(element.scale || 100, "%)"),
      filter: "brightness(".concat(element.brightness || 100, "%) contrast(").concat(element.contrast || 100, "%) saturate(").concat(element.saturate || 100, "%)"),
      boxShadow: element.boxShadowColor ? "0px ".concat(element.boxShadowSpread || 0, "px ").concat(element.boxShadowBlur || 0, "px ").concat(element.boxShadowColor) : element.shadow ? "".concat(element.shadowOffsetX || 2, "px ").concat(element.shadowOffsetY || 2, "px 4px ").concat(element.shadowColor || '#000000', "40") : 'none'
    }, getElementTypeStyles(element, zoom, canvasWidth));
  }, [element.x, element.y, element.width, element.height, element.rotation, element.scale, element.backgroundColor, element.backgroundOpacity, element.borderWidth, element.borderStyle, element.borderColor, element.borderRadius, element.opacity, element.brightness, element.contrast, element.saturate, element.boxShadowColor, element.boxShadowSpread, element.boxShadowBlur, element.shadow, element.shadowOffsetX, element.shadowOffsetY, element.shadowColor, element.color, element.fontSize, element.fontFamily, element.fontWeight, element.fontStyle, element.textAlign, element.textDecoration, element.lineHeight, element.type, elementPadding, zoom, isSelected, dragAndDrop.isDragging, dragAndDrop.draggedElementId, dragAndDrop.dragOffset]);
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    ref: elementRef,
    "data-element-id": element.id,
    className: "canvas-element ".concat(isSelected ? 'selected' : ''),
    style: elementStyles,
    onMouseDown: handleMouseDown,
    onDoubleClick: handleDoubleClick,
    onContextMenu: handleContextMenuEvent,
    draggable: false
  }, element.type === 'text' ? element.content || element.text || 'Texte' : element.type === 'product_table' ? null :
  // Le contenu sera rendu plus bas pour les tableaux
  element.type === 'image' && !element.src ? '📷 Image' : element.type === 'line' ? null : element.type === 'layout-header' ? '[H] En-tête' : element.type === 'layout-footer' ? '📄 Pied de Page' : element.type === 'layout-sidebar' ? '📄 Barre Latérale' : element.type === 'layout-section' ? '📄 Section' : element.type === 'layout-container' ? '📦 Conteneur' : element.type === 'shape-rectangle' ? '▭' : element.type === 'shape-circle' ? '○' : element.type === 'shape-line' ? null : element.type === 'shape-arrow' ? '→' : element.type === 'shape-triangle' ? '△' : element.type === 'shape-star' ? '⭐' : element.type === 'divider' ? null : element.type === 'image-upload' ? '📤 Télécharger' : element.type === 'logo' ? '🏷️ Logo' : element.type === 'barcode' ? '📊 123456' : element.type === 'qrcode' || element.type === 'qrcode-dynamic' ? '📱 QR' : element.type === 'icon' ? element.content || '🎯' : element.type === 'dynamic-text' ? function () {
    // Fonction pour obtenir le contenu selon le template
    var getTemplateContent = function getTemplateContent(template, customContent) {
      var templates = {
        'total_only': '{{order_total}} €',
        'order_info': 'Commande {{order_number}} - {{order_date}}',
        'customer_info': '{{customer_name}} - {{customer_email}}',
        'customer_address': '{{customer_name}}\n{{billing_address}}',
        'full_header': 'Facture N° {{order_number}}\nClient: {{customer_name}}\nTotal: {{order_total}} €',
        'invoice_header': 'FACTURE N° {{order_number}}\nDate: {{date}}\nClient: {{customer_name}}\n{{billing_address}}',
        'order_summary': 'Sous-total: {{order_subtotal}} €\nFrais de port: {{order_shipping}} €\nTVA: {{order_tax}} €\nTotal: {{order_total}} €',
        'payment_info': 'Échéance: {{due_date}}\nMontant: {{order_total}} €',
        'payment_terms': 'Conditions de paiement: 30 jours\nÉchéance: {{due_date}}\nMontant dû: {{order_total}} €',
        'shipping_info': 'Adresse de livraison:\n{{shipping_address}}',
        'thank_you': 'Merci pour votre commande !\nNous vous remercions de votre confiance.',
        'legal_notice': 'TVA non applicable - art. 293 B du CGI\nPaiement à 30 jours fin de mois',
        'bank_details': 'Coordonnées bancaires:\nIBAN: FR76 1234 5678 9012 3456 7890 123\nBIC: BNPAFRPP',
        'contact_info': 'Contact: contact@monentreprise.com\nTél: 01 23 45 67 89',
        'order_confirmation': 'CONFIRMATION DE COMMANDE\nCommande {{order_number}} du {{order_date}}\nStatut: Confirmée',
        'delivery_note': 'BON DE LIVRAISON\nCommande {{order_number}}\nDestinataire: {{customer_name}}\n{{shipping_address}}',
        'warranty_info': 'Garantie: 2 ans pièces et main d\'œuvre\nService après-vente: sav@monentreprise.com',
        'return_policy': 'Droit de rétractation: 14 jours\nRetour sous 30 jours pour défauts',
        'signature_line': 'Signature du client:\n\n_______________________________\nDate: {{date}}',
        'invoice_footer': 'Facture générée automatiquement le {{date}}\nConservez cette facture pour vos archives',
        'terms_conditions': 'Conditions générales de vente disponibles sur notre site\nwww.monentreprise.com/conditions',
        'quality_guarantee': 'Tous nos produits sont garantis contre les défauts\nService qualité: qualite@monentreprise.com',
        'eco_friendly': 'Entreprise engagée pour l\'environnement\nEmballages recyclables et biodégradables',
        'follow_up': 'Suivi de commande: {{order_number}}\nContact: suivi@monentreprise.com',
        'custom': customContent || '{{order_total}} €'
      };
      return templates[template] || templates['total_only'];
    };
    var content = getTemplateContent(element.template, element.customContent);
    // Remplacement basique pour le rendu canvas
    return content.replace(/\{\{order_total\}\}/g, '125.99 €').replace(/\{\{order_number\}\}/g, 'CMD-2025-001').replace(/\{\{customer_name\}\}/g, 'Jean Dupont').replace(/\{\{customer_email\}\}/g, 'jean@example.com').replace(/\{\{date\}\}/g, '17/10/2025').replace(/\{\{order_date\}\}/g, '15/10/2025').replace(/\{\{due_date\}\}/g, '15/11/2025').replace(/\{\{order_subtotal\}\}/g, '100.00 €').replace(/\{\{order_tax\}\}/g, '25.99 €').replace(/\{\{order_shipping\}\}/g, '15.00 €').replace(/\{\{billing_address\}\}/g, '123 Rue de la Paix\n75001 Paris\nFrance').replace(/\{\{shipping_address\}\}/g, '456 Avenue des Champs\n75008 Paris\nFrance');
  }() : element.type === 'formula' ? element.content || '{{prix * quantite}}' : element.type === 'conditional-text' ? element.content || '{{condition ? "Oui" : "Non"}}' : element.type === 'counter' ? element.content || '1' : element.type === 'date-dynamic' ? element.content || '{{date|format:Y-m-d}}' : element.type === 'currency' ? element.content || '{{montant|currency:EUR}}' : element.type === 'table-dynamic' ? '📊 Tableau' : element.type === 'gradient-box' ? '🌈 Dégradé' : element.type === 'shadow-box' ? '📦 Ombre' : element.type === 'rounded-box' ? '🔄 Arrondi' : element.type === 'border-box' ? '🔲 Bordure' : element.type === 'background-pattern' ? '🎨 Motif' : element.type === 'watermark' ? element.content || 'CONFIDENTIEL' : element.type === 'progress-bar' ? null : element.type === 'product_table' ? null :
  // Le contenu sera rendu plus bas dans le même conteneur
  element.type === 'customer_info' ? null :
  // Le contenu sera rendu plus bas dans le même conteneur
  element.type === 'mentions' ? null :
  // Le contenu sera rendu plus bas dans le même conteneur
  element.type !== 'image' && element.type !== 'rectangle' && element.type !== 'company_logo' && element.type !== 'order_number' && element.type !== 'company_info' && element.type !== 'document_type' ? element.type : null, element.type === 'product_table' && function (_element$columns2, _element$columns3, _element$columns4, _element$columns5, _element$columns6, _element$columns7) {
    // Données des produits (utiliser sampleProducts si disponible, sinon données par défaut)
    var products = element.sampleProducts || [{
      name: 'Produit A - Description du produit',
      sku: 'SKU001',
      quantity: 2,
      price: 19.99,
      total: 39.98
    }, {
      name: 'Produit B - Un autre article',
      sku: 'SKU002',
      quantity: 1,
      price: 29.99,
      total: 29.99
    }];

    // Calcul des totaux dynamiques
    var subtotal = products.reduce(function (sum, product) {
      return sum + product.total;
    }, 0);
    var shipping = element.showShipping ? 5.00 : 0;
    var tax = element.showTaxes ? 2.25 : 0;
    var discount = element.showDiscount ? -5.00 : 0;
    var total = subtotal + shipping + tax + discount;

    // Déterminer la dernière colonne visible pour afficher les totaux
    var getLastVisibleColumn = function getLastVisibleColumn() {
      var columns = ['image', 'name', 'sku', 'quantity', 'price', 'total'];
      for (var i = columns.length - 1; i >= 0; i--) {
        var _element$columns;
        if (((_element$columns = element.columns) === null || _element$columns === void 0 ? void 0 : _element$columns[columns[i]]) !== false) {
          return columns[i];
        }
      }
      return 'total'; // fallback
    };
    var lastVisibleColumn = getLastVisibleColumn();
    var tableStyles = getTableStyles(element.tableStyle);
    // Forcer les bordures pour les tableaux de produits (correction du bug d'affichage)
    var showBorders = element.showBorders !== false; // Utiliser la propriété showBorders de l'élément

    // Log de debug pour voir les propriétés utilisées - COMMENTÉ POUR ÉVITER LA BOUCLE INFINIE
    // console.log('CanvasElement product_table debug:', {
    //   element: element,
    //   products: products,
    //   showBorders: showBorders,
    //   tableStyles: tableStyles,
    //   subtotal: subtotal,
    //   shipping: shipping,
    //   tax: tax,
    //   discount: discount,
    //   total: total,
    //   lastVisibleColumn: lastVisibleColumn
    // });
    return /*#__PURE__*/React.createElement("div", {
      style: {
        width: '100%',
        height: '100%',
        display: 'flex',
        flexDirection: 'column',
        fontSize: 10 * zoom,
        fontFamily: '"Inter", "Segoe UI", Roboto, -apple-system, BlinkMacSystemFont, sans-serif',
        // Utiliser les bordures du style de tableau si showBorders est activé
        border: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : element.borderWidth && element.borderWidth > 0 ? "".concat(Math.max(1, element.borderWidth * zoom * 0.5), "px solid ").concat(element.borderColor || '#e5e7eb') : 'none',
        borderRadius: "".concat(tableStyles.borderRadius * zoom, "px"),
        overflow: 'hidden',
        // Assurer que le background ne cache pas les bordures
        backgroundColor: element.backgroundColor || 'transparent',
        boxSizing: 'border-box',
        boxShadow: tableStyles.shadow,
        // Améliorer la lisibilité globale
        lineHeight: '1.4',
        color: tableStyles.rowTextColor
      }
    }, element.showHeaders !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        background: tableStyles.headerBg,
        borderBottom: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none',
        fontWeight: tableStyles.headerFontWeight,
        color: tableStyles.headerTextColor,
        fontSize: "".concat(tableStyles.headerFontSize * zoom, "px"),
        textTransform: 'uppercase',
        letterSpacing: '0.025em'
      }
    }, ((_element$columns2 = element.columns) === null || _element$columns2 === void 0 ? void 0 : _element$columns2.image) !== false && /*#__PURE__*/React.createElement("div", {
      key: "header-image",
      style: {
        flex: '0 0 40px',
        padding: "".concat(6 * zoom, "px ").concat(4 * zoom, "px"),
        textAlign: 'center',
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none',
        fontSize: "".concat(tableStyles.headerFontSize * zoom * 0.9, "px"),
        opacity: 0.9
      }
    }, "Img"), ((_element$columns3 = element.columns) === null || _element$columns3 === void 0 ? void 0 : _element$columns3.name) !== false && /*#__PURE__*/React.createElement("div", {
      key: "header-name",
      style: {
        flex: 1,
        padding: "".concat(6 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'left',
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none',
        fontSize: "".concat(tableStyles.headerFontSize * zoom, "px")
      }
    }, "Produit"), ((_element$columns4 = element.columns) === null || _element$columns4 === void 0 ? void 0 : _element$columns4.sku) !== false && /*#__PURE__*/React.createElement("div", {
      key: "header-sku",
      style: {
        flex: '0 0 80px',
        padding: "".concat(6 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'left',
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none',
        fontSize: "".concat(tableStyles.headerFontSize * zoom, "px")
      }
    }, "SKU"), ((_element$columns5 = element.columns) === null || _element$columns5 === void 0 ? void 0 : _element$columns5.quantity) !== false && /*#__PURE__*/React.createElement("div", {
      key: "header-quantity",
      style: {
        flex: '0 0 60px',
        padding: "".concat(6 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'center',
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none',
        fontSize: "".concat(tableStyles.headerFontSize * zoom, "px")
      }
    }, "Qt\xE9"), ((_element$columns6 = element.columns) === null || _element$columns6 === void 0 ? void 0 : _element$columns6.price) !== false && /*#__PURE__*/React.createElement("div", {
      key: "header-price",
      style: {
        flex: '0 0 80px',
        padding: "".concat(6 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none',
        fontSize: "".concat(tableStyles.headerFontSize * zoom, "px")
      }
    }, "Prix"), ((_element$columns7 = element.columns) === null || _element$columns7 === void 0 ? void 0 : _element$columns7.total) !== false && /*#__PURE__*/React.createElement("div", {
      key: "header-total",
      style: {
        flex: '0 0 80px',
        padding: "".concat(6 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        fontSize: "".concat(tableStyles.headerFontSize * zoom, "px")
      }
    }, "Total")), /*#__PURE__*/React.createElement("div", {
      style: {
        flex: 1,
        display: 'flex',
        flexDirection: 'column'
      }
    }, products.map(function (product, index) {
      var _element$columns8, _element$columns9, _element$columns0, _element$columns1, _element$columns10, _element$columns11;
      return /*#__PURE__*/React.createElement("div", {
        key: "row-".concat(index),
        style: {
          display: 'flex',
          borderBottom: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
          backgroundColor: index % 2 === 0 ? element.evenRowBg || tableStyles.rowBg : element.oddRowBg || tableStyles.altRowBg,
          color: index % 2 === 0 ? element.evenRowTextColor || tableStyles.rowTextColor : element.oddRowTextColor || tableStyles.rowTextColor,
          fontSize: "".concat(tableStyles.rowFontSize * zoom, "px"),
          transition: 'background-color 0.15s ease'
        }
      }, ((_element$columns8 = element.columns) === null || _element$columns8 === void 0 ? void 0 : _element$columns8.image) !== false && /*#__PURE__*/React.createElement("div", {
        style: {
          flex: '0 0 40px',
          padding: "".concat(5 * zoom, "px ").concat(4 * zoom, "px"),
          textAlign: 'center',
          borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
          color: tableStyles.rowTextColor,
          opacity: 0.7,
          fontSize: "".concat(tableStyles.rowFontSize * zoom * 0.9, "px")
        }
      }, "\uD83D\uDCF7"), ((_element$columns9 = element.columns) === null || _element$columns9 === void 0 ? void 0 : _element$columns9.name) !== false && /*#__PURE__*/React.createElement("div", {
        style: {
          flex: 1,
          padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
          borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
          color: tableStyles.rowTextColor,
          fontWeight: '500',
          lineHeight: '1.3'
        }
      }, product.name), ((_element$columns0 = element.columns) === null || _element$columns0 === void 0 ? void 0 : _element$columns0.sku) !== false && /*#__PURE__*/React.createElement("div", {
        style: {
          flex: '0 0 80px',
          padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
          borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
          color: tableStyles.rowTextColor,
          opacity: 0.8,
          fontFamily: 'monospace',
          fontSize: "".concat(tableStyles.rowFontSize * zoom * 0.9, "px")
        }
      }, product.sku), ((_element$columns1 = element.columns) === null || _element$columns1 === void 0 ? void 0 : _element$columns1.quantity) !== false && /*#__PURE__*/React.createElement("div", {
        style: {
          flex: '0 0 60px',
          padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
          textAlign: 'center',
          borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
          color: tableStyles.rowTextColor,
          fontWeight: '600'
        }
      }, product.quantity), ((_element$columns10 = element.columns) === null || _element$columns10 === void 0 ? void 0 : _element$columns10.price) !== false && /*#__PURE__*/React.createElement("div", {
        style: {
          flex: '0 0 80px',
          padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
          textAlign: 'right',
          borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
          color: tableStyles.rowTextColor,
          fontWeight: '500',
          fontFamily: '"Inter", system-ui, sans-serif'
        }
      }, product.price.toFixed(2), "\u20AC"), ((_element$columns11 = element.columns) === null || _element$columns11 === void 0 ? void 0 : _element$columns11.total) !== false && /*#__PURE__*/React.createElement("div", {
        style: {
          flex: '0 0 80px',
          padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
          textAlign: 'right',
          color: tableStyles.rowTextColor,
          fontWeight: '600',
          fontFamily: '"Inter", system-ui, sans-serif'
        }
      }, product.total.toFixed(2), "\u20AC"));
    })), (element.showSubtotal || element.showShipping || element.showTaxes || element.showDiscount || element.showTotal) && /*#__PURE__*/React.createElement("div", {
      style: {
        borderTop: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none'
      }
    }, element.showSubtotal && /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        justifyContent: 'flex-end',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        fontWeight: 'bold'
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        width: 'auto',
        textAlign: 'right',
        display: 'flex',
        justifyContent: 'space-between'
      }
    }, /*#__PURE__*/React.createElement("span", null, "Sous-total:"), /*#__PURE__*/React.createElement("span", null, subtotal.toFixed(2), "\u20AC"))), element.showShipping && /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        justifyContent: 'flex-end',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px")
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        width: 'auto',
        textAlign: 'right',
        display: 'flex',
        justifyContent: 'space-between'
      }
    }, /*#__PURE__*/React.createElement("span", null, "Port:"), /*#__PURE__*/React.createElement("span", null, shipping.toFixed(2), "\u20AC"))), element.showTaxes && /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        justifyContent: 'flex-end',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px")
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        width: 'auto',
        textAlign: 'right',
        display: 'flex',
        justifyContent: 'space-between'
      }
    }, /*#__PURE__*/React.createElement("span", null, "TVA:"), /*#__PURE__*/React.createElement("span", null, tax.toFixed(2), "\u20AC"))), element.showDiscount && /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        justifyContent: 'flex-end',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px")
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        width: 'auto',
        textAlign: 'right',
        display: 'flex',
        justifyContent: 'space-between'
      }
    }, /*#__PURE__*/React.createElement("span", null, "Remise:"), /*#__PURE__*/React.createElement("span", null, Math.abs(discount).toFixed(2), "\u20AC"))), element.showTotal && /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        justifyContent: 'flex-end',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        fontWeight: 'bold',
        background: tableStyles.gradient || tableStyles.headerBg,
        color: tableStyles.headerTextColor || (element.tableStyle === 'modern' ? '#ffffff' : '#000000'),
        boxShadow: tableStyles.shadow ? "0 2px 4px ".concat(tableStyles.shadow) : 'none'
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        width: 'auto',
        textAlign: 'right',
        display: 'flex',
        justifyContent: 'space-between'
      }
    }, /*#__PURE__*/React.createElement("span", null, "TOTAL:"), /*#__PURE__*/React.createElement("span", null, total.toFixed(2), "\u20AC")))));
  }(), element.type === 'customer_info' && /*#__PURE__*/React.createElement("div", {
    style: {
      width: '100%',
      height: '100%',
      padding: "".concat(8 * zoom, "px"),
      fontSize: "".concat((element.fontSize || 12) * zoom, "px"),
      fontFamily: element.fontFamily || 'Arial, sans-serif',
      fontWeight: element.fontWeight || 'normal',
      fontStyle: element.fontStyle || 'normal',
      textDecoration: element.textDecoration || 'none',
      color: element.color || '#333',
      backgroundColor: element.backgroundColor || 'transparent',
      // Bordures subtiles pour les éléments spéciaux
      border: element.borderWidth && element.borderWidth > 0 ? "".concat(Math.max(1, element.borderWidth * zoom * 0.5), "px solid ").concat(element.borderColor || '#e5e7eb') : 'none',
      borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '2px',
      boxSizing: 'border-box'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'row' : 'column',
      gap: "".concat(element.spacing * zoom || 8 * zoom, "px"),
      height: '100%'
    }
  }, ((_element$fields = element.fields) === null || _element$fields === void 0 ? void 0 : _element$fields.includes('name')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "Nom :"), /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: 'bold',
      color: element.color || '#333'
    }
  }, "Jean Dupont")), ((_element$fields2 = element.fields) === null || _element$fields2 === void 0 ? void 0 : _element$fields2.includes('email')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "Email :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: '#1976d2'
    }
  }, "jean.dupont@email.com")), ((_element$fields3 = element.fields) === null || _element$fields3 === void 0 ? void 0 : _element$fields3.includes('phone')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "T\xE9l\xE9phone :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: element.color || '#333'
    }
  }, "+33 6 12 34 56 78")), ((_element$fields4 = element.fields) === null || _element$fields4 === void 0 ? void 0 : _element$fields4.includes('address')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "Adresse :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: element.color || '#333',
      lineHeight: '1.4'
    }
  }, "123 Rue de la Paix", /*#__PURE__*/React.createElement("br", null), "75001 Paris, France")), ((_element$fields5 = element.fields) === null || _element$fields5 === void 0 ? void 0 : _element$fields5.includes('company')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "Soci\xE9t\xE9 :"), /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: 'bold',
      color: element.color || '#333'
    }
  }, "ABC Company SARL")), ((_element$fields6 = element.fields) === null || _element$fields6 === void 0 ? void 0 : _element$fields6.includes('vat')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "N\xB0 TVA :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: element.color || '#333'
    }
  }, "FR 12 345 678 901")), ((_element$fields7 = element.fields) === null || _element$fields7 === void 0 ? void 0 : _element$fields7.includes('siret')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "SIRET :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: element.color || '#333'
    }
  }, "123 456 789 00012")))), element.type === 'mentions' && function () {
    var mentions = [];
    if (element.showEmail) mentions.push('contact@monsite.com');
    if (element.showPhone) mentions.push('01 23 45 67 89');
    if (element.showSiret) mentions.push('SIRET: 123 456 789 00012');
    if (element.showVat) mentions.push('TVA: FR 12 345 678 901');
    if (element.showAddress) mentions.push('123 Rue de la Paix, 75001 Paris');
    if (element.showWebsite) mentions.push('www.monsite.com');
    if (element.showCustomText && element.customText) mentions.push(element.customText);
    var content = mentions.join(element.separator || ' • ');
    return /*#__PURE__*/React.createElement("div", {
      style: {
        width: '100%',
        height: '100%',
        display: 'flex',
        alignItems: 'center',
        justifyContent: element.textAlign === 'center' ? 'center' : element.textAlign === 'right' ? 'flex-end' : 'flex-start',
        padding: "".concat(4 * zoom, "px"),
        fontSize: "".concat((element.fontSize || 8) * zoom, "px"),
        fontFamily: element.fontFamily || 'Arial, sans-serif',
        fontWeight: element.fontWeight || 'normal',
        color: element.color || '#666666',
        lineHeight: element.lineHeight || 1.2,
        backgroundColor: element.backgroundColor || 'transparent',
        border: element.borderWidth && element.borderWidth > 0 ? "".concat(Math.max(1, element.borderWidth * zoom * 0.5), "px solid ").concat(element.borderColor || '#e5e7eb') : 'none',
        borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '2px',
        boxSizing: 'border-box',
        wordBreak: 'break-word',
        overflow: 'hidden'
      }
    }, element.layout === 'vertical' ? /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        flexDirection: 'column',
        gap: "".concat(2 * zoom, "px"),
        width: '100%',
        textAlign: element.textAlign || 'center'
      }
    }, mentions.map(function (mention, index) {
      return /*#__PURE__*/React.createElement("div", {
        key: index,
        style: {
          lineHeight: element.lineHeight || 1.2
        }
      }, mention);
    })) : /*#__PURE__*/React.createElement("div", {
      style: {
        textAlign: element.textAlign || 'center',
        lineHeight: element.lineHeight || 1.2,
        width: '100%'
      }
    }, content));
  }(), element.type === 'company_logo' && /*#__PURE__*/React.createElement("div", {
    style: {
      width: '100%',
      height: '100%',
      display: 'flex',
      alignItems: 'center',
      justifyContent: element.alignment === 'center' ? 'center' : element.alignment === 'right' ? 'flex-end' : 'flex-start',
      padding: '8px',
      backgroundColor: element.backgroundColor || 'transparent',
      // Bordures subtiles pour les éléments spéciaux
      border: element.borderWidth && element.borderWidth > 0 ? "".concat(Math.max(1, element.borderWidth * zoom * 0.5), "px solid ").concat(element.borderColor || '#e5e7eb') : 'none',
      borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '2px',
      boxSizing: 'border-box'
    }
  }, element.imageUrl ? /*#__PURE__*/React.createElement("img", {
    src: element.imageUrl,
    alt: "Logo entreprise",
    style: {
      width: "".concat(element.width || 150, "px"),
      height: "".concat(element.height || 80, "px"),
      objectFit: element.fit || 'contain',
      borderRadius: element.borderRadius || 0,
      border: element.borderWidth ? "".concat(element.borderWidth, "px ").concat(element.borderStyle || 'solid', " ").concat(element.borderColor || 'transparent') : element.showBorder ? '1px solid transparent' : 'none'
    }
  }) : /*#__PURE__*/React.createElement("div", {
    style: {
      width: "".concat(element.width || 150, "px"),
      height: "".concat(element.height || 80, "px"),
      backgroundColor: '#f5f5f5',
      border: element.borderWidth ? "".concat(element.borderWidth, "px ").concat(element.borderStyle || 'solid', " ").concat(element.borderColor || 'transparent') : element.showBorder ? '1px solid transparent' : 'none',
      borderRadius: element.borderRadius || '4px',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      color: '#999',
      fontSize: "".concat(12 * zoom, "px")
    }
  }, "\uD83C\uDFE2 Logo")), element.type === 'company_info' && /*#__PURE__*/React.createElement("div", {
    style: {
      width: '100%',
      height: '100%',
      padding: "".concat(8 * zoom, "px"),
      fontSize: "".concat((element.fontSize || 12) * zoom, "px"),
      fontFamily: element.fontFamily || 'Arial, sans-serif',
      fontWeight: element.fontWeight || 'normal',
      fontStyle: element.fontStyle || 'normal',
      textDecoration: element.textDecoration || 'none',
      color: element.color || '#333',
      backgroundColor: element.backgroundColor || 'transparent',
      // Bordures subtiles pour les éléments spéciaux
      border: element.borderWidth && element.borderWidth > 0 ? "".concat(Math.max(1, element.borderWidth * zoom * 0.5), "px solid ").concat(element.borderColor || '#e5e7eb') : 'none',
      borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '2px',
      boxSizing: 'border-box'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'row' : 'column',
      gap: "".concat(element.spacing * zoom || 8 * zoom, "px"),
      height: '100%'
    }
  }, ((_element$fields8 = element.fields) === null || _element$fields8 === void 0 ? void 0 : _element$fields8.includes('name')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "Nom :"), /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: 'bold',
      color: element.color || '#333'
    }
  }, "Ma Soci\xE9t\xE9 SARL")), ((_element$fields9 = element.fields) === null || _element$fields9 === void 0 ? void 0 : _element$fields9.includes('address')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "Adresse :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: element.color || '#333',
      lineHeight: '1.4'
    }
  }, "123 Rue de l'Entreprise", /*#__PURE__*/React.createElement("br", null), "75001 Paris, France")), ((_element$fields0 = element.fields) === null || _element$fields0 === void 0 ? void 0 : _element$fields0.includes('phone')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "T\xE9l\xE9phone :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: element.color || '#333'
    }
  }, "+33 1 23 45 67 89")), ((_element$fields1 = element.fields) === null || _element$fields1 === void 0 ? void 0 : _element$fields1.includes('email')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "Email :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: '#1976d2'
    }
  }, "contact@masociete.com")), ((_element$fields10 = element.fields) === null || _element$fields10 === void 0 ? void 0 : _element$fields10.includes('website')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "Site web :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: '#1976d2'
    }
  }, "www.masociete.com")), ((_element$fields11 = element.fields) === null || _element$fields11 === void 0 ? void 0 : _element$fields11.includes('vat')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "N\xB0 TVA :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: element.color || '#333'
    }
  }, "FR 12 345 678 901")), ((_element$fields12 = element.fields) === null || _element$fields12 === void 0 ? void 0 : _element$fields12.includes('rcs')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'bold',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "RCS :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: element.color || '#333'
    }
  }, "Paris B 123 456 789")), ((_element$fields13 = element.fields) === null || _element$fields13 === void 0 ? void 0 : _element$fields13.includes('siret')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "SIRET :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: element.color || '#333'
    }
  }, "123 456 789 00012")))), element.type === 'order_number' && /*#__PURE__*/React.createElement("div", {
    style: {
      width: '100%',
      height: '100%',
      display: 'flex',
      flexDirection: 'column',
      justifyContent: 'center',
      alignItems: element.textAlign === 'center' ? 'center' : element.textAlign === 'right' ? 'flex-end' : 'flex-start',
      padding: "".concat(8 * zoom, "px"),
      fontSize: "".concat((element.fontSize || 14) * zoom, "px"),
      fontFamily: element.fontFamily || 'Arial',
      fontWeight: element.fontWeight || 'bold',
      color: element.color || '#333333',
      textAlign: element.textAlign || 'right',
      backgroundColor: element.backgroundColor || 'transparent',
      // Bordures subtiles pour les éléments spéciaux
      border: element.borderWidth && element.borderWidth > 0 ? "".concat(Math.max(1, element.borderWidth * zoom * 0.5), "px solid ").concat(element.borderColor || '#e5e7eb') : 'none',
      borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '2px',
      boxSizing: 'border-box'
    }
  }, element.showLabel && /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: "".concat(12 * zoom, "px"),
      fontWeight: 'normal',
      color: element.color || '#666',
      marginBottom: "".concat(4 * zoom, "px")
    }
  }, element.labelText || 'N° de commande:'), /*#__PURE__*/React.createElement("div", null, function () {
    // Utiliser le format défini ou une valeur par défaut
    var format = element.format || 'Commande #{order_number} - {order_date}';

    // Données de test pour le rendu (seront remplacées par les vraies données lors de la génération)
    var testData = {
      order_number: '12345',
      order_date: '15/10/2025'
    };

    // Remplacer les variables dans le format
    return format.replace(/{order_number}/g, testData.order_number).replace(/{order_date}/g, testData.order_date);
  }())), element.type === 'document_type' && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'inline-block',
      padding: "".concat(8 * zoom, "px"),
      fontSize: "".concat((element.fontSize || 18) * zoom, "px"),
      fontFamily: element.fontFamily || 'Arial',
      fontWeight: element.fontWeight || 'bold',
      color: element.color || '#1e293b',
      textAlign: element.textAlign || 'center',
      backgroundColor: element.backgroundColor || 'transparent',
      // Bordures subtiles pour les éléments spéciaux
      border: element.borderWidth && element.borderWidth > 0 ? "".concat(Math.max(1, element.borderWidth * zoom * 0.5), "px solid ").concat(element.borderColor || '#e5e7eb') : 'none',
      borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '4px',
      whiteSpace: 'nowrap',
      boxSizing: 'border-box'
    }
  }, element.documentType === 'invoice' ? 'FACTURE' : element.documentType === 'quote' ? 'DEVIS' : element.documentType === 'receipt' ? 'REÇU' : element.documentType === 'order' ? 'COMMANDE' : element.documentType === 'credit_note' ? 'AVOIR' : 'DOCUMENT'), isSelected && /*#__PURE__*/React.createElement(React.Fragment, null, element.type !== 'line' && element.type !== 'divider' && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    key: "resize-handle-nw-".concat(element.id),
    className: "resize-handle nw",
    onMouseDown: function onMouseDown(e) {
      var _elementRef$current;
      e.stopPropagation();
      var canvas = (_elementRef$current = elementRef.current) === null || _elementRef$current === void 0 ? void 0 : _elementRef$current.closest('.canvas-zoom-wrapper');
      var canvasRect = canvas ? canvas.getBoundingClientRect() : null;
      resize.handleResizeStart(e, 'nw', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      }, canvasRect, zoom);
    },
    onContextMenu: handleContextMenuEvent
  }), /*#__PURE__*/React.createElement("div", {
    key: "resize-handle-ne-".concat(element.id),
    className: "resize-handle ne",
    onMouseDown: function onMouseDown(e) {
      var _elementRef$current2;
      e.stopPropagation();
      var canvas = (_elementRef$current2 = elementRef.current) === null || _elementRef$current2 === void 0 ? void 0 : _elementRef$current2.closest('.canvas-zoom-wrapper');
      var canvasRect = canvas ? canvas.getBoundingClientRect() : null;
      resize.handleResizeStart(e, 'ne', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      }, canvasRect, zoom);
    },
    onContextMenu: handleContextMenuEvent
  }), /*#__PURE__*/React.createElement("div", {
    key: "resize-handle-sw-".concat(element.id),
    className: "resize-handle sw",
    onMouseDown: function onMouseDown(e) {
      var _elementRef$current3;
      e.stopPropagation();
      var canvas = (_elementRef$current3 = elementRef.current) === null || _elementRef$current3 === void 0 ? void 0 : _elementRef$current3.closest('.canvas-zoom-wrapper');
      var canvasRect = canvas ? canvas.getBoundingClientRect() : null;
      resize.handleResizeStart(e, 'sw', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      }, canvasRect, zoom);
    },
    onContextMenu: handleContextMenuEvent
  })), /*#__PURE__*/React.createElement("div", {
    key: "resize-handle-se-".concat(element.id),
    className: "resize-handle se",
    onMouseDown: function onMouseDown(e) {
      var _elementRef$current4;
      e.stopPropagation();
      var canvas = (_elementRef$current4 = elementRef.current) === null || _elementRef$current4 === void 0 ? void 0 : _elementRef$current4.closest('.canvas-zoom-wrapper');
      var canvasRect = canvas ? canvas.getBoundingClientRect() : null;
      resize.handleResizeStart(e, 'se', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      }, canvasRect, zoom);
    },
    onContextMenu: handleContextMenuEvent
  }), element.type !== 'line' && element.type !== 'divider' && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    key: "resize-handle-n-".concat(element.id),
    className: "resize-handle n",
    onMouseDown: function onMouseDown(e) {
      var _elementRef$current5;
      e.stopPropagation();
      var canvas = (_elementRef$current5 = elementRef.current) === null || _elementRef$current5 === void 0 ? void 0 : _elementRef$current5.closest('.canvas-zoom-wrapper');
      var canvasRect = canvas ? canvas.getBoundingClientRect() : null;
      resize.handleResizeStart(e, 'n', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      }, canvasRect, zoom);
    },
    onContextMenu: handleContextMenuEvent
  }), /*#__PURE__*/React.createElement("div", {
    key: "resize-handle-s-".concat(element.id),
    className: "resize-handle s",
    onMouseDown: function onMouseDown(e) {
      var _elementRef$current6;
      e.stopPropagation();
      var canvas = (_elementRef$current6 = elementRef.current) === null || _elementRef$current6 === void 0 ? void 0 : _elementRef$current6.closest('.canvas-zoom-wrapper');
      var canvasRect = canvas ? canvas.getBoundingClientRect() : null;
      resize.handleResizeStart(e, 's', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      }, canvasRect, zoom);
    },
    onContextMenu: handleContextMenuEvent
  })), /*#__PURE__*/React.createElement("div", {
    key: "resize-handle-w-".concat(element.id),
    className: "resize-handle w",
    onMouseDown: function onMouseDown(e) {
      var _elementRef$current7;
      e.stopPropagation();
      var canvas = (_elementRef$current7 = elementRef.current) === null || _elementRef$current7 === void 0 ? void 0 : _elementRef$current7.closest('.canvas-zoom-wrapper');
      var canvasRect = canvas ? canvas.getBoundingClientRect() : null;
      resize.handleResizeStart(e, 'w', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      }, canvasRect, zoom);
    },
    onContextMenu: handleContextMenuEvent
  }), /*#__PURE__*/React.createElement("div", {
    key: "resize-handle-e-".concat(element.id),
    className: "resize-handle e",
    onMouseDown: function onMouseDown(e) {
      var _elementRef$current8;
      e.stopPropagation();
      var canvas = (_elementRef$current8 = elementRef.current) === null || _elementRef$current8 === void 0 ? void 0 : _elementRef$current8.closest('.canvas-zoom-wrapper');
      var canvasRect = canvas ? canvas.getBoundingClientRect() : null;
      resize.handleResizeStart(e, 'e', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      }, canvasRect, zoom);
    },
    onContextMenu: handleContextMenuEvent
  }), element.type !== 'line' && element.type !== 'divider' && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    key: "resize-zone-n-".concat(element.id),
    className: "resize-zone resize-zone-n",
    onMouseDown: function onMouseDown(e) {
      e.stopPropagation();
      resize.handleResizeStart(e, 'n', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  }), /*#__PURE__*/React.createElement("div", {
    key: "resize-zone-s-".concat(element.id),
    className: "resize-zone resize-zone-s",
    onMouseDown: function onMouseDown(e) {
      e.stopPropagation();
      resize.handleResizeStart(e, 's', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  })), /*#__PURE__*/React.createElement("div", {
    key: "resize-zone-w-".concat(element.id),
    className: "resize-zone resize-zone-w",
    onMouseDown: function onMouseDown(e) {
      e.stopPropagation();
      resize.handleResizeStart(e, 'w', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  }), /*#__PURE__*/React.createElement("div", {
    key: "resize-zone-e-".concat(element.id),
    className: "resize-zone resize-zone-e",
    onMouseDown: function onMouseDown(e) {
      e.stopPropagation();
      resize.handleResizeStart(e, 'e', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  })), isSelected && enableRotation && /*#__PURE__*/React.createElement("div", {
    key: "rotation-handle-".concat(element.id),
    className: "rotation-handle",
    style: {
      position: 'absolute',
      top: "".concat(-20 * zoom, "px"),
      left: '50%',
      transform: 'translateX(-50%)',
      width: "".concat(12 * zoom, "px"),
      height: "".concat(12 * zoom, "px"),
      backgroundColor: '#3b82f6',
      border: "".concat(2 * zoom, "px solid white"),
      borderRadius: '50%',
      cursor: 'alias',
      zIndex: 1000,
      boxShadow: '0 2px 4px rgba(0,0,0,0.2)'
    },
    onMouseDown: function onMouseDown(e) {
      e.stopPropagation();
      rotation.handleRotationStart(e, element);
    },
    title: "Faire pivoter l'\xE9l\xE9ment"
  })), element.type === 'progress-bar' && /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      top: 0,
      left: 0,
      height: '100%',
      width: "".concat(element.progressValue || 75, "%"),
      backgroundColor: element.progressColor || '#3b82f6',
      borderRadius: '10px',
      transition: 'width 0.3s ease',
      // Bordures subtiles pour les éléments spéciaux
      border: element.borderWidth && element.borderWidth > 0 ? "".concat(Math.max(1, element.borderWidth * zoom * 0.5), "px solid ").concat(element.borderColor || '#e5e7eb') : 'none',
      boxSizing: 'border-box'
    }
  }));
};
;// ./resources/js/hooks/useDragAndDrop.js
function useDragAndDrop_createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = useDragAndDrop_unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function useDragAndDrop_slicedToArray(r, e) { return useDragAndDrop_arrayWithHoles(r) || useDragAndDrop_iterableToArrayLimit(r, e) || useDragAndDrop_unsupportedIterableToArray(r, e) || useDragAndDrop_nonIterableRest(); }
function useDragAndDrop_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function useDragAndDrop_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return useDragAndDrop_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? useDragAndDrop_arrayLikeToArray(r, a) : void 0; } }
function useDragAndDrop_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function useDragAndDrop_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function useDragAndDrop_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

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
  var _useState = (0,react.useState)(false),
    _useState2 = useDragAndDrop_slicedToArray(_useState, 2),
    isDragging = _useState2[0],
    setIsDragging = _useState2[1];
  var _useState3 = (0,react.useState)({
      x: 0,
      y: 0
    }),
    _useState4 = useDragAndDrop_slicedToArray(_useState3, 2),
    dragOffset = _useState4[0],
    setDragOffset = _useState4[1];
  var _useState5 = (0,react.useState)(null),
    _useState6 = useDragAndDrop_slicedToArray(_useState5, 2),
    draggedElementId = _useState6[0],
    setDraggedElementId = _useState6[1];
  var dragStartPos = (0,react.useRef)({
    x: 0,
    y: 0
  });
  var elementStartPos = (0,react.useRef)({
    x: 0,
    y: 0
  });
  var currentDragData = (0,react.useRef)(null);
  var currentDragOffset = (0,react.useRef)({
    x: 0,
    y: 0
  });
  var snapToGridValue = (0,react.useCallback)(function (value) {
    if (!snapToGrid) return value;
    return Math.round(value / gridSize) * gridSize;
  }, [snapToGrid, gridSize]);
  var snapToGuidesValue = (0,react.useCallback)(function (value) {
    var isHorizontal = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
    if (!snapToGuides) return value;
    var guideArray = isHorizontal ? guides.horizontal : guides.vertical;
    var snapTolerance = 5; // pixels
    var _iterator = useDragAndDrop_createForOfIteratorHelper(guideArray),
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
  var snapValue = (0,react.useCallback)(function (value) {
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
  (0,react.useEffect)(function () {
    return function () {
      if (currentDragData.current) {
        document.removeEventListener('mousemove', currentDragData.current.handleMouseMove);
        document.removeEventListener('mouseup', currentDragData.current.handleMouseUp);
        currentDragData.current = null;
      }
    };
  }, []);
  var handleMouseDown = (0,react.useCallback)(function (e, elementId, elementRect) {
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
    if (isSeparator) {}
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
      if (isSeparator) {}
      if (isLine) {}
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
        if (_isSeparator) {}
        if (isLine) {}
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
  var handleDragStart = (0,react.useCallback)(function (e, elementId, elementRect) {
    e.dataTransfer.setData('text/plain', elementId);
    e.dataTransfer.effectAllowed = 'move';
    dragStartPos.current = {
      x: e.clientX - elementRect.left,
      y: e.clientY - elementRect.top
    };
  }, []);
  var handleDragOver = (0,react.useCallback)(function (e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
  }, []);
  var handleDrop = (0,react.useCallback)(function (e, canvasRect, elementRect) {
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
;// ./resources/js/components/Toolbar.jsx
function Toolbar_slicedToArray(r, e) { return Toolbar_arrayWithHoles(r) || Toolbar_iterableToArrayLimit(r, e) || Toolbar_unsupportedIterableToArray(r, e) || Toolbar_nonIterableRest(); }
function Toolbar_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function Toolbar_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return Toolbar_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? Toolbar_arrayLikeToArray(r, a) : void 0; } }
function Toolbar_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function Toolbar_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function Toolbar_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var Toolbar = function Toolbar(_ref) {
  var selectedTool = _ref.selectedTool,
    onToolSelect = _ref.onToolSelect,
    zoom = _ref.zoom,
    onZoomChange = _ref.onZoomChange,
    showGrid = _ref.showGrid,
    onShowGridChange = _ref.onShowGridChange,
    snapToGrid = _ref.snapToGrid,
    onSnapToGridChange = _ref.onSnapToGridChange,
    onUndo = _ref.onUndo,
    onRedo = _ref.onRedo,
    canUndo = _ref.canUndo,
    canRedo = _ref.canRedo;
  var _React$useState = react.useState('home'),
    _React$useState2 = Toolbar_slicedToArray(_React$useState, 2),
    activeTab = _React$useState2[0],
    setActiveTab = _React$useState2[1];
  var textTools = [{
    id: 'select',
    label: 'Sélection (V)',
    icon: '👆',
    shortcut: 'V'
  }, {
    id: 'add-text',
    label: 'Texte Simple (T)',
    icon: '📝',
    shortcut: 'T'
  }, {
    id: 'add-text-title',
    label: 'Titre (H)',
    icon: '📄',
    shortcut: 'H'
  }, {
    id: 'add-text-subtitle',
    label: 'Sous-titre (S)',
    icon: '📋',
    shortcut: 'S'
  }];
  var shapeTools = [{
    id: 'add-rectangle',
    label: 'Rectangle (R)',
    icon: '▭',
    shortcut: 'R'
  }, {
    id: 'add-circle',
    label: 'Cercle (C)',
    icon: '○',
    shortcut: 'C'
  }, {
    id: 'add-line',
    label: 'Ligne (L)',
    icon: '━',
    shortcut: 'L'
  }, {
    id: 'add-arrow',
    label: 'Flèche (A)',
    icon: '➤',
    shortcut: 'A'
  }, {
    id: 'add-triangle',
    label: 'Triangle (3)',
    icon: '△',
    shortcut: '3'
  }, {
    id: 'add-star',
    label: 'Étoile (5)',
    icon: '⭐',
    shortcut: '5'
  }];
  var insertTools = [{
    id: 'add-divider',
    label: 'Séparateur (D)',
    icon: '⎯',
    shortcut: 'D'
  }, {
    id: 'add-image',
    label: 'Image (I)',
    icon: '🖼️',
    shortcut: 'I'
  }];
  var tabs = [{
    id: 'home',
    label: 'Accueil',
    icon: '🏠'
  }, {
    id: 'insert',
    label: 'Insertion',
    icon: '➕'
  }, {
    id: 'view',
    label: 'Affichage',
    icon: '👁️'
  }];
  return /*#__PURE__*/react.createElement("div", {
    className: "toolbar ribbon-toolbar"
  }, /*#__PURE__*/react.createElement("div", {
    className: "toolbar-tabs"
  }, tabs.map(function (tab) {
    return /*#__PURE__*/react.createElement("button", {
      key: tab.id,
      className: "tab-button ".concat(activeTab === tab.id ? 'active' : ''),
      onClick: function onClick() {
        return setActiveTab(tab.id);
      }
    }, /*#__PURE__*/react.createElement("span", {
      className: "tab-icon"
    }, tab.icon), /*#__PURE__*/react.createElement("span", {
      className: "tab-label"
    }, tab.label));
  })), /*#__PURE__*/react.createElement("div", {
    className: "toolbar-content"
  }, activeTab === 'home' && /*#__PURE__*/react.createElement("div", {
    className: "tab-content"
  }, /*#__PURE__*/react.createElement("div", {
    className: "toolbar-group"
  }, /*#__PURE__*/react.createElement("h5", null, "Presse-papiers"), /*#__PURE__*/react.createElement("div", {
    className: "group-buttons"
  }, /*#__PURE__*/react.createElement("button", {
    className: "edit-button",
    onClick: onUndo,
    disabled: !canUndo,
    title: "Annuler (Ctrl+Z)"
  }, /*#__PURE__*/react.createElement("div", {
    className: "button-content"
  }, /*#__PURE__*/react.createElement("span", {
    className: "button-icon"
  }, "\u21B6"), /*#__PURE__*/react.createElement("span", {
    className: "button-text"
  }, "Annuler"))), /*#__PURE__*/react.createElement("button", {
    className: "edit-button",
    onClick: onRedo,
    disabled: !canRedo,
    title: "R\xE9tablir (Ctrl+Y)"
  }, /*#__PURE__*/react.createElement("div", {
    className: "button-content"
  }, /*#__PURE__*/react.createElement("span", {
    className: "button-icon"
  }, "\u21B7"), /*#__PURE__*/react.createElement("span", {
    className: "button-text"
  }, "R\xE9tablir"))))), /*#__PURE__*/react.createElement("div", {
    className: "toolbar-group"
  }, /*#__PURE__*/react.createElement("h5", null, "Outils"), /*#__PURE__*/react.createElement("div", {
    className: "group-buttons"
  }, textTools.map(function (tool) {
    return /*#__PURE__*/react.createElement("button", {
      key: tool.id,
      className: "tool-button ".concat(selectedTool === tool.id ? 'active' : ''),
      onClick: function onClick() {
        return onToolSelect(tool.id);
      },
      title: tool.label
    }, /*#__PURE__*/react.createElement("div", {
      className: "tool-content"
    }, /*#__PURE__*/react.createElement("span", {
      className: "tool-icon"
    }, tool.icon), /*#__PURE__*/react.createElement("span", {
      className: "tool-label"
    }, tool.shortcut)));
  }))), /*#__PURE__*/react.createElement("div", {
    className: "toolbar-group"
  }, /*#__PURE__*/react.createElement("h5", null, "Formes"), /*#__PURE__*/react.createElement("div", {
    className: "group-buttons shapes-grid"
  }, shapeTools.map(function (tool) {
    return /*#__PURE__*/react.createElement("button", {
      key: tool.id,
      className: "tool-button ".concat(selectedTool === tool.id ? 'active' : ''),
      onClick: function onClick() {
        return onToolSelect(tool.id);
      },
      title: tool.label
    }, /*#__PURE__*/react.createElement("div", {
      className: "tool-content"
    }, /*#__PURE__*/react.createElement("span", {
      className: "tool-icon"
    }, tool.icon), /*#__PURE__*/react.createElement("span", {
      className: "tool-label"
    }, tool.shortcut)));
  })))), activeTab === 'insert' && /*#__PURE__*/react.createElement("div", {
    className: "tab-content"
  }, /*#__PURE__*/react.createElement("div", {
    className: "toolbar-group"
  }, /*#__PURE__*/react.createElement("h5", null, "\xC9l\xE9ments"), /*#__PURE__*/react.createElement("div", {
    className: "group-buttons"
  }, insertTools.map(function (tool) {
    return /*#__PURE__*/react.createElement("button", {
      key: tool.id,
      className: "tool-button ".concat(selectedTool === tool.id ? 'active' : ''),
      onClick: function onClick() {
        return onToolSelect(tool.id);
      },
      title: tool.label
    }, /*#__PURE__*/react.createElement("div", {
      className: "tool-content"
    }, /*#__PURE__*/react.createElement("span", {
      className: "tool-icon"
    }, tool.icon), /*#__PURE__*/react.createElement("span", {
      className: "tool-label"
    }, tool.shortcut)));
  })))), activeTab === 'view' && /*#__PURE__*/react.createElement("div", {
    className: "tab-content"
  }, /*#__PURE__*/react.createElement("div", {
    className: "toolbar-group"
  }, /*#__PURE__*/react.createElement("h5", null, "Zoom"), /*#__PURE__*/react.createElement("div", {
    className: "group-buttons"
  }, /*#__PURE__*/react.createElement("div", {
    className: "zoom-controls"
  }, /*#__PURE__*/react.createElement("button", {
    className: "zoom-button",
    onClick: function onClick() {
      return onZoomChange(Math.max(0.1, zoom - 0.1));
    },
    title: "Zoom arri\xE8re (Ctrl+-)"
  }, /*#__PURE__*/react.createElement("span", {
    className: "button-icon"
  }, "\uD83D\uDD0D"), /*#__PURE__*/react.createElement("span", {
    className: "button-text"
  }, "-")), /*#__PURE__*/react.createElement("span", {
    className: "zoom-value"
  }, Math.round(zoom * 100), "%"), /*#__PURE__*/react.createElement("button", {
    className: "zoom-button",
    onClick: function onClick() {
      return onZoomChange(Math.min(3, zoom + 0.1));
    },
    title: "Zoom avant (Ctrl+=)"
  }, /*#__PURE__*/react.createElement("span", {
    className: "button-icon"
  }, "\uD83D\uDD0D"), /*#__PURE__*/react.createElement("span", {
    className: "button-text"
  }, "+"))))), /*#__PURE__*/react.createElement("div", {
    className: "toolbar-group"
  }, /*#__PURE__*/react.createElement("h5", null, "Affichage"), /*#__PURE__*/react.createElement("div", {
    className: "group-buttons"
  }, /*#__PURE__*/react.createElement("div", {
    className: "display-options"
  }, /*#__PURE__*/react.createElement("label", {
    className: "toggle-label"
  }, /*#__PURE__*/react.createElement("input", {
    type: "checkbox",
    checked: showGrid,
    onChange: function onChange(e) {
      return onShowGridChange(e.target.checked);
    }
  }), /*#__PURE__*/react.createElement("span", {
    className: "toggle-text"
  }, "Grille"), /*#__PURE__*/react.createElement("span", {
    className: "toggle-shortcut"
  }, "(G)")), /*#__PURE__*/react.createElement("label", {
    className: "toggle-label"
  }, /*#__PURE__*/react.createElement("input", {
    type: "checkbox",
    checked: snapToGrid,
    onChange: function onChange(e) {
      return onSnapToGridChange(e.target.checked);
    }
  }), /*#__PURE__*/react.createElement("span", {
    className: "toggle-text"
  }, "Aimantation"), /*#__PURE__*/react.createElement("span", {
    className: "toggle-shortcut"
  }, "(M)"))))))));
};
;// ./resources/js/hooks/useHistory.js
function useHistory_slicedToArray(r, e) { return useHistory_arrayWithHoles(r) || useHistory_iterableToArrayLimit(r, e) || useHistory_unsupportedIterableToArray(r, e) || useHistory_nonIterableRest(); }
function useHistory_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function useHistory_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return useHistory_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? useHistory_arrayLikeToArray(r, a) : void 0; } }
function useHistory_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function useHistory_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function useHistory_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useHistory = function useHistory() {
  var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  var _options$maxHistorySi = options.maxHistorySize,
    maxHistorySize = _options$maxHistorySi === void 0 ? 50 : _options$maxHistorySi;
  var _useState = (0,react.useState)([]),
    _useState2 = useHistory_slicedToArray(_useState, 2),
    history = _useState2[0],
    setHistory = _useState2[1];
  var _useState3 = (0,react.useState)(-1),
    _useState4 = useHistory_slicedToArray(_useState3, 2),
    currentIndex = _useState4[0],
    setCurrentIndex = _useState4[1];
  var isUndoRedoRef = (0,react.useRef)(false);
  var addToHistory = (0,react.useCallback)(function (state) {
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
  var undo = (0,react.useCallback)(function () {
    if (currentIndex > 0) {
      isUndoRedoRef.current = true;
      setCurrentIndex(function (prev) {
        return prev - 1;
      });
      return history[currentIndex - 1];
    }
    return null;
  }, [currentIndex, history]);
  var redo = (0,react.useCallback)(function () {
    if (currentIndex < history.length - 1) {
      isUndoRedoRef.current = true;
      setCurrentIndex(function (prev) {
        return prev + 1;
      });
      return history[currentIndex + 1];
    }
    return null;
  }, [currentIndex, history]);
  var canUndo = (0,react.useCallback)(function () {
    return currentIndex > 0;
  }, [currentIndex]);
  var canRedo = (0,react.useCallback)(function () {
    return currentIndex < history.length - 1;
  }, [currentIndex]);
  var clearHistory = (0,react.useCallback)(function () {
    setHistory([]);
    setCurrentIndex(-1);
  }, []);
  var getCurrentState = (0,react.useCallback)(function () {
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
;// ./resources/js/hooks/useSelection.js
function useSelection_typeof(o) { "@babel/helpers - typeof"; return useSelection_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, useSelection_typeof(o); }
function useSelection_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function useSelection_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? useSelection_ownKeys(Object(t), !0).forEach(function (r) { useSelection_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : useSelection_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function useSelection_defineProperty(e, r, t) { return (r = useSelection_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function useSelection_toPropertyKey(t) { var i = useSelection_toPrimitive(t, "string"); return "symbol" == useSelection_typeof(i) ? i : i + ""; }
function useSelection_toPrimitive(t, r) { if ("object" != useSelection_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != useSelection_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || useSelection_unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return useSelection_arrayLikeToArray(r); }
function useSelection_slicedToArray(r, e) { return useSelection_arrayWithHoles(r) || useSelection_iterableToArrayLimit(r, e) || useSelection_unsupportedIterableToArray(r, e) || useSelection_nonIterableRest(); }
function useSelection_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function useSelection_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return useSelection_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? useSelection_arrayLikeToArray(r, a) : void 0; } }
function useSelection_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function useSelection_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function useSelection_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useSelection = function useSelection(_ref) {
  var onSelectionChange = _ref.onSelectionChange,
    _ref$multiSelect = _ref.multiSelect,
    multiSelect = _ref$multiSelect === void 0 ? true : _ref$multiSelect;
  var _useState = (0,react.useState)([]),
    _useState2 = useSelection_slicedToArray(_useState, 2),
    selectedElements = _useState2[0],
    setSelectedElements = _useState2[1];
  var _useState3 = (0,react.useState)(null),
    _useState4 = useSelection_slicedToArray(_useState3, 2),
    selectionBox = _useState4[0],
    setSelectionBox = _useState4[1];
  var selectElement = (0,react.useCallback)(function (elementId) {
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
  var selectAll = (0,react.useCallback)(function (elementIds) {
    setSelectedElements(elementIds);
    onSelectionChange === null || onSelectionChange === void 0 || onSelectionChange(elementIds);
  }, [onSelectionChange]);
  var clearSelection = (0,react.useCallback)(function () {
    setSelectedElements([]);
    onSelectionChange === null || onSelectionChange === void 0 || onSelectionChange([]);
  }, [onSelectionChange]);
  var isSelected = (0,react.useCallback)(function (elementId) {
    return selectedElements.includes(elementId);
  }, [selectedElements]);
  var startSelectionBox = (0,react.useCallback)(function (startX, startY) {
    setSelectionBox({
      startX: startX,
      startY: startY,
      endX: startX,
      endY: startY
    });
  }, []);
  var updateSelectionBox = (0,react.useCallback)(function (endX, endY) {
    setSelectionBox(function (prev) {
      return prev ? useSelection_objectSpread(useSelection_objectSpread({}, prev), {}, {
        endX: endX,
        endY: endY
      }) : null;
    });
  }, []);
  var endSelectionBox = (0,react.useCallback)(function (elements) {
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
  var deleteSelected = (0,react.useCallback)(function () {
    // Cette fonction retourne les IDs à supprimer, la logique de suppression
    // sera gérée par le composant parent
    return _toConsumableArray(selectedElements);
  }, [selectedElements]);
  var duplicateSelected = (0,react.useCallback)(function () {
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
;// ./resources/js/hooks/useClipboard.js
function useClipboard_slicedToArray(r, e) { return useClipboard_arrayWithHoles(r) || useClipboard_iterableToArrayLimit(r, e) || useClipboard_unsupportedIterableToArray(r, e) || useClipboard_nonIterableRest(); }
function useClipboard_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function useClipboard_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return useClipboard_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? useClipboard_arrayLikeToArray(r, a) : void 0; } }
function useClipboard_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function useClipboard_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function useClipboard_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useClipboard = function useClipboard(_ref) {
  var onPaste = _ref.onPaste;
  var _useState = (0,react.useState)(null),
    _useState2 = useClipboard_slicedToArray(_useState, 2),
    clipboardData = _useState2[0],
    setClipboardData = _useState2[1];
  var copy = (0,react.useCallback)(function (data) {
    setClipboardData(data);
    // Ici on pourrait aussi utiliser l'API Clipboard du navigateur
    // mais pour la compatibilité, on utilise un état interne
  }, []);
  var paste = (0,react.useCallback)(function () {
    if (clipboardData && onPaste) {
      onPaste(clipboardData);
    }
    return clipboardData;
  }, [clipboardData, onPaste]);
  var cut = (0,react.useCallback)(function (data) {
    copy(data);
    // La suppression sera gérée par le composant parent
    return data;
  }, [copy]);
  var hasData = (0,react.useCallback)(function () {
    return clipboardData !== null;
  }, [clipboardData]);
  var clear = (0,react.useCallback)(function () {
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
;// ./resources/js/hooks/useZoom.js
function useZoom_slicedToArray(r, e) { return useZoom_arrayWithHoles(r) || useZoom_iterableToArrayLimit(r, e) || useZoom_unsupportedIterableToArray(r, e) || useZoom_nonIterableRest(); }
function useZoom_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function useZoom_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return useZoom_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? useZoom_arrayLikeToArray(r, a) : void 0; } }
function useZoom_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function useZoom_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function useZoom_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useZoom = function useZoom(_ref) {
  var _ref$initialZoom = _ref.initialZoom,
    initialZoom = _ref$initialZoom === void 0 ? 1 : _ref$initialZoom,
    _ref$minZoom = _ref.minZoom,
    minZoom = _ref$minZoom === void 0 ? 0.1 : _ref$minZoom,
    _ref$maxZoom = _ref.maxZoom,
    maxZoom = _ref$maxZoom === void 0 ? 3 : _ref$maxZoom,
    _ref$zoomStep = _ref.zoomStep,
    zoomStep = _ref$zoomStep === void 0 ? 0.1 : _ref$zoomStep;
  var _useState = (0,react.useState)(initialZoom),
    _useState2 = useZoom_slicedToArray(_useState, 2),
    zoom = _useState2[0],
    setZoom = _useState2[1];
  var _useState3 = (0,react.useState)({
      x: 0,
      y: 0
    }),
    _useState4 = useZoom_slicedToArray(_useState3, 2),
    zoomOrigin = _useState4[0],
    setZoomOrigin = _useState4[1];
  var zoomIn = (0,react.useCallback)(function () {
    setZoom(function (prevZoom) {
      return Math.min(prevZoom + zoomStep, maxZoom);
    });
  }, [maxZoom, zoomStep]);
  var zoomOut = (0,react.useCallback)(function () {
    setZoom(function (prevZoom) {
      return Math.max(prevZoom - zoomStep, minZoom);
    });
  }, [minZoom, zoomStep]);
  var setZoomLevel = (0,react.useCallback)(function (newZoom) {
    var clampedZoom = Math.max(minZoom, Math.min(maxZoom, newZoom));
    setZoom(clampedZoom);
  }, [minZoom, maxZoom]);
  var resetZoom = (0,react.useCallback)(function () {
    setZoom(initialZoom);
  }, [initialZoom]);
  var zoomToFit = (0,react.useCallback)(function (canvasWidth, canvasHeight, containerWidth, containerHeight) {
    var scaleX = containerWidth / canvasWidth;
    var scaleY = containerHeight / canvasHeight;
    var newZoom = Math.min(scaleX, scaleY, maxZoom);
    setZoom(Math.max(newZoom, minZoom));
  }, [minZoom, maxZoom]);
  var zoomToPoint = (0,react.useCallback)(function (pointX, pointY, zoomFactor) {
    var newZoom = Math.max(minZoom, Math.min(maxZoom, zoom * zoomFactor));
    setZoomOrigin({
      x: pointX,
      y: pointY
    });
    setZoom(newZoom);
  }, [zoom, minZoom, maxZoom]);
  var zoomToSelection = (0,react.useCallback)(function (selectedElements, canvasWidth, canvasHeight, containerWidth, containerHeight) {
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
  var getTransformStyle = (0,react.useCallback)(function () {
    return {
      transform: "scale(".concat(zoom, ")"),
      transformOrigin: "".concat(zoomOrigin.x, "px ").concat(zoomOrigin.y, "px")
    };
  }, [zoom, zoomOrigin]);
  var screenToCanvas = (0,react.useCallback)(function (screenX, screenY) {
    return {
      x: (screenX - zoomOrigin.x) / zoom,
      y: (screenY - zoomOrigin.y) / zoom
    };
  }, [zoom, zoomOrigin]);
  var canvasToScreen = (0,react.useCallback)(function (canvasX, canvasY) {
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
;// ./resources/js/hooks/useContextMenu.js
function useContextMenu_slicedToArray(r, e) { return useContextMenu_arrayWithHoles(r) || useContextMenu_iterableToArrayLimit(r, e) || useContextMenu_unsupportedIterableToArray(r, e) || useContextMenu_nonIterableRest(); }
function useContextMenu_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function useContextMenu_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return useContextMenu_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? useContextMenu_arrayLikeToArray(r, a) : void 0; } }
function useContextMenu_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function useContextMenu_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function useContextMenu_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useContextMenu = function useContextMenu() {
  var _useState = (0,react.useState)(null),
    _useState2 = useContextMenu_slicedToArray(_useState, 2),
    contextMenu = _useState2[0],
    setContextMenu = _useState2[1];
  var _useState3 = (0,react.useState)(false),
    _useState4 = useContextMenu_slicedToArray(_useState3, 2),
    isAnimating = _useState4[0],
    setIsAnimating = _useState4[1];
  var showContextMenu = (0,react.useCallback)(function (x, y, items) {
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
  var hideContextMenu = (0,react.useCallback)(function () {
    if (contextMenu) {
      setIsAnimating(true);
      // Attendre la fin de l'animation avant de masquer complètement
      setTimeout(function () {
        setContextMenu(null);
        setIsAnimating(false);
      }, 150); // Durée de l'animation
    }
  }, [contextMenu]);
  var handleContextMenuAction = (0,react.useCallback)(function (action) {
    hideContextMenu();
    return action;
  }, [hideContextMenu]);
  (0,react.useEffect)(function () {
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
;// ./resources/js/utils/elementPropertyRestrictions.js
// Système de gestion des propriétés d'éléments
// Définit les restrictions et validations pour chaque type d'élément

var ELEMENT_PROPERTY_RESTRICTIONS = {
  // Éléments spéciaux - contrôle du fond autorisé mais valeur par défaut transparente
  special: {
    backgroundColor: {
      disabled: false,
      // Maintenant autorisé
      "default": 'transparent' // Valeur par défaut transparente
    },
    borderColor: {
      disabled: false
    },
    borderWidth: {
      disabled: false
    }
  },
  // Éléments de mise en page - contrôle complet
  layout: {
    backgroundColor: {
      disabled: false,
      "default": '#f8fafc'
    },
    borderColor: {
      disabled: false
    },
    borderWidth: {
      disabled: false
    }
  },
  // Éléments de texte - contrôle complet
  text: {
    backgroundColor: {
      disabled: false,
      "default": 'transparent'
    },
    borderColor: {
      disabled: false
    },
    borderWidth: {
      disabled: false
    }
  },
  // Éléments graphiques - contrôle complet
  shape: {
    backgroundColor: {
      disabled: false,
      "default": '#e5e7eb'
    },
    borderColor: {
      disabled: false
    },
    borderWidth: {
      disabled: false
    }
  },
  // Éléments médias - contrôle limité
  media: {
    backgroundColor: {
      disabled: false,
      "default": '#f3f4f6'
    },
    borderColor: {
      disabled: false
    },
    borderWidth: {
      disabled: false
    }
  },
  // Éléments dynamiques - contrôle complet
  dynamic: {
    backgroundColor: {
      disabled: false,
      "default": 'transparent'
    },
    borderColor: {
      disabled: false
    },
    borderWidth: {
      disabled: false
    }
  }
};

// Mapping des types d'éléments vers leurs catégories
var ELEMENT_TYPE_MAPPING = {
  // Spéciaux
  'product_table': 'special',
  'customer_info': 'special',
  'company_logo': 'special',
  'company_info': 'special',
  'order_number': 'special',
  'document_type': 'special',
  'progress-bar': 'special',
  // Mise en page
  'layout-header': 'layout',
  'layout-footer': 'layout',
  'layout-sidebar': 'layout',
  'layout-section': 'layout',
  'layout-container': 'layout',
  'layout-section-divider': 'layout',
  'layout-spacer': 'layout',
  'layout-two-column': 'layout',
  'layout-three-column': 'layout',
  // Texte
  'text': 'text',
  'dynamic-text': 'text',
  'conditional-text': 'text',
  'counter': 'text',
  'date-dynamic': 'text',
  'currency': 'text',
  'formula': 'text',
  // Formes
  'rectangle': 'shape',
  'line': 'shape',
  'shape-rectangle': 'shape',
  'shape-circle': 'shape',
  'shape-line': 'shape',
  'shape-arrow': 'shape',
  'shape-triangle': 'shape',
  'shape-star': 'shape',
  'divider': 'shape',
  // Médias
  'image': 'media',
  'image-upload': 'media',
  'logo': 'media',
  'barcode': 'media',
  'qrcode': 'media',
  'qrcode-dynamic': 'media',
  'icon': 'media',
  // Dynamiques
  'table-dynamic': 'dynamic',
  'gradient-box': 'dynamic',
  'shadow-box': 'dynamic',
  'rounded-box': 'dynamic',
  'border-box': 'dynamic',
  'background-pattern': 'dynamic',
  'watermark': 'dynamic',
  // Factures (mélange de catégories)
  'invoice-header': 'layout',
  'invoice-address-block': 'layout',
  'invoice-info-block': 'layout',
  'invoice-products-table': 'special',
  'invoice-totals-block': 'layout',
  'invoice-payment-terms': 'layout',
  'invoice-legal-footer': 'layout',
  'invoice-signature-block': 'layout'
};

// Fonction pour vérifier si une propriété est autorisée pour un type d'élément
var isPropertyAllowed = function isPropertyAllowed(elementType, propertyName) {
  var category = ELEMENT_TYPE_MAPPING[elementType] || 'text'; // défaut texte
  var restrictions = ELEMENT_PROPERTY_RESTRICTIONS[category];
  if (!restrictions || !restrictions[propertyName]) {
    return true; // propriété autorisée par défaut
  }
  return !restrictions[propertyName].disabled;
};

// Fonction pour obtenir la valeur par défaut d'une propriété
var getPropertyDefault = function getPropertyDefault(elementType, propertyName) {
  var category = ELEMENT_TYPE_MAPPING[elementType] || 'text';
  var restrictions = ELEMENT_PROPERTY_RESTRICTIONS[category];
  if (restrictions && restrictions[propertyName] && restrictions[propertyName]["default"] !== undefined) {
    return restrictions[propertyName]["default"];
  }
  return null; // pas de valeur par défaut spécifique
};

// Fonction pour valider une propriété
var validateProperty = function validateProperty(elementType, propertyName, value) {
  if (!isPropertyAllowed(elementType, propertyName)) {
    var _ELEMENT_PROPERTY_RES;
    return {
      valid: false,
      reason: ((_ELEMENT_PROPERTY_RES = ELEMENT_PROPERTY_RESTRICTIONS[ELEMENT_TYPE_MAPPING[elementType] || 'text'][propertyName]) === null || _ELEMENT_PROPERTY_RES === void 0 ? void 0 : _ELEMENT_PROPERTY_RES.reason) || 'Propriété non autorisée'
    };
  }

  // Validations spécifiques selon le type de propriété
  switch (propertyName) {
    case 'backgroundColor':
      if (typeof value !== 'string') {
        return {
          valid: false,
          reason: 'La couleur doit être une chaîne'
        };
      }
      // Plus de restriction pour les éléments spéciaux - ils peuvent maintenant avoir un fond
      break;
    case 'borderWidth':
      if (typeof value !== 'number' || value < 0) {
        return {
          valid: false,
          reason: 'La largeur de bordure doit être un nombre positif'
        };
      }
      break;
    case 'fontSize':
      if (typeof value !== 'number' || value <= 0) {
        return {
          valid: false,
          reason: 'La taille de police doit être un nombre positif'
        };
      }
      break;
    case 'width':
    case 'height':
      if (typeof value !== 'number' || value <= 0) {
        return {
          valid: false,
          reason: 'Les dimensions doivent être positives'
        };
      }
      break;
    default:
      break;
  }
  return {
    valid: true
  };
};

// Fonction pour corriger automatiquement une propriété invalide
var fixInvalidProperty = function fixInvalidProperty(elementType, propertyName, invalidValue) {
  // Pour les éléments spéciaux, backgroundColor peut maintenant être contrôlé
  // (pas de forçage automatique à 'transparent')

  // Valeurs par défaut pour les propriétés numériques
  var numericDefaults = {
    borderWidth: 0,
    fontSize: 14,
    width: 100,
    height: 50,
    padding: 8
  };
  if (numericDefaults[propertyName] !== undefined) {
    return numericDefaults[propertyName];
  }

  // Valeurs par défaut pour les chaînes
  var stringDefaults = {
    backgroundColor: 'transparent',
    borderColor: 'transparent',
    color: '#000000',
    fontFamily: 'Arial, sans-serif'
  };
  return stringDefaults[propertyName] || invalidValue;
};
;// ./resources/js/hooks/useCanvasState.js
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function useCanvasState_createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = useCanvasState_unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function useCanvasState_typeof(o) { "@babel/helpers - typeof"; return useCanvasState_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, useCanvasState_typeof(o); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function useCanvasState_toConsumableArray(r) { return useCanvasState_arrayWithoutHoles(r) || useCanvasState_iterableToArray(r) || useCanvasState_unsupportedIterableToArray(r) || useCanvasState_nonIterableSpread(); }
function useCanvasState_nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function useCanvasState_iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function useCanvasState_arrayWithoutHoles(r) { if (Array.isArray(r)) return useCanvasState_arrayLikeToArray(r); }
function useCanvasState_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function useCanvasState_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? useCanvasState_ownKeys(Object(t), !0).forEach(function (r) { useCanvasState_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : useCanvasState_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function useCanvasState_defineProperty(e, r, t) { return (r = useCanvasState_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function useCanvasState_toPropertyKey(t) { var i = useCanvasState_toPrimitive(t, "string"); return "symbol" == useCanvasState_typeof(i) ? i : i + ""; }
function useCanvasState_toPrimitive(t, r) { if ("object" != useCanvasState_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != useCanvasState_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function useCanvasState_slicedToArray(r, e) { return useCanvasState_arrayWithHoles(r) || useCanvasState_iterableToArrayLimit(r, e) || useCanvasState_unsupportedIterableToArray(r, e) || useCanvasState_nonIterableRest(); }
function useCanvasState_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function useCanvasState_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return useCanvasState_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? useCanvasState_arrayLikeToArray(r, a) : void 0; } }
function useCanvasState_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function useCanvasState_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function useCanvasState_arrayWithHoles(r) { if (Array.isArray(r)) return r; }









// Hook utilitaire pour synchroniser les refs
var useLatest = function useLatest(value) {
  var ref = (0,react.useRef)(value);
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
  var _useState = (0,react.useState)(initialElements),
    _useState2 = useCanvasState_slicedToArray(_useState, 2),
    elements = _useState2[0],
    setElements = _useState2[1];
  var _useState3 = (0,react.useState)(1),
    _useState4 = useCanvasState_slicedToArray(_useState3, 2),
    nextId = _useState4[0],
    setNextId = _useState4[1];
  var _useState5 = (0,react.useState)(false),
    _useState6 = useCanvasState_slicedToArray(_useState5, 2),
    isSaving = _useState6[0],
    setIsSaving = _useState6[1];

  // États de chargement granulaires pour meilleure UX
  var _useState7 = (0,react.useState)({
      saving: false,
      loading: false,
      duplicating: false,
      deleting: false
    }),
    _useState8 = useCanvasState_slicedToArray(_useState7, 2),
    loadingStates = _useState8[0],
    setLoadingStates = _useState8[1];
  var history = useHistory();
  var selection = useSelection({
    onSelectionChange: (0,react.useCallback)(function (selectedIds) {
      // Callback pour les changements de sélection
    }, []),
    multiSelect: (_globalSettings$multi = globalSettings === null || globalSettings === void 0 ? void 0 : globalSettings.multiSelect) !== null && _globalSettings$multi !== void 0 ? _globalSettings$multi : true
  });

  // Synchronisation parfaite des refs avec useLatest
  var historyRef = useLatest(history);
  var selectionRef = useLatest(selection);
  var clipboard = useClipboard({
    onPaste: (0,react.useCallback)(function (data) {
      if (data.type === 'elements') {
        var pastedElements = data.elements.map(function (element) {
          return useCanvasState_objectSpread(useCanvasState_objectSpread({}, element), {}, {
            id: "element_".concat(nextId + data.elements.indexOf(element)),
            x: element.x + 20,
            // Offset pour éviter la superposition
            y: element.y + 20
          });
        });
        setElements(function (prev) {
          return [].concat(useCanvasState_toConsumableArray(prev), useCanvasState_toConsumableArray(pastedElements));
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
  var zoom = useZoom({
    initialZoom: globalSettings ? parseFloat(globalSettings.defaultZoom) / 100 : 1,
    minZoom: globalSettings ? globalSettings.minZoom / 100 : 0.25,
    maxZoom: globalSettings ? globalSettings.maxZoom / 100 : 3,
    zoomStep: globalSettings ? globalSettings.zoomStep / 100 : 0.1
  });
  var contextMenu = useContextMenu();

  // Fonction updateElement définie après history
  var updateElement = (0,react.useCallback)(function (elementId, updates) {
    setElements(function (prev) {
      var newElements = prev.map(function (element) {
        if (element.id === elementId) {
          var updatedElement = useCanvasState_objectSpread(useCanvasState_objectSpread({}, element), updates);
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
  (0,react.useEffect)(function () {
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
  var validateInitialElements = (0,react.useCallback)(function (elements) {
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
  (0,react.useEffect)(function () {
    var validatedElements = validateInitialElements(initialElements);
    setElements(validatedElements);
    if (validatedElements && validatedElements.length > 0) {
      var maxId = Math.max.apply(Math, useCanvasState_toConsumableArray(validatedElements.map(function (el) {
        var _el$id;
        var idParts = ((_el$id = el.id) === null || _el$id === void 0 ? void 0 : _el$id.split('_')) || [];
        return parseInt(idParts[1] || 0);
      })));
      setNextId(maxId + 1);
    } else {
      setNextId(1);
    }
  }, [initialElements, validateInitialElements]);
  var dragAndDrop = useDragAndDrop({
    onElementMove: (0,react.useCallback)(function (elementId, position) {
      updateElement(elementId, position);
    }, [updateElement]),
    onElementDrop: (0,react.useCallback)(function (elementId, position) {
      updateElement(elementId, position);
      history.addToHistory({
        elements: elements.map(function (el) {
          return el.id === elementId ? useCanvasState_objectSpread(useCanvasState_objectSpread({}, el), position) : el;
        }),
        nextId: nextId
      });
    }, [updateElement, history, elements, nextId])
  });

  // Fonction utilitaire pour nettoyer les éléments avant sauvegarde (éviter les références DOM)
  var cleanElementsForHistory = (0,react.useCallback)(function (elementsToClean) {
    return elementsToClean.map(function (element) {
      var cleaned = useCanvasState_objectSpread({}, element);
      // Supprimer les propriétés non sérialisables qui pourraient contenir des références DOM
      var nonSerializableProps = ['domElement', 'ref', 'eventListeners', 'component', 'render'];
      nonSerializableProps.forEach(function (prop) {
        delete cleaned[prop];
      });
      return cleaned;
    });
  }, []);

  // Sauvegarder l'état dans l'historique à chaque changement
  (0,react.useEffect)(function () {
    if (elements.length > 0 || history.historySize === 0) {
      var cleanedElements = cleanElementsForHistory(elements);
      history.addToHistory({
        elements: cleanedElements,
        nextId: nextId
      });
    }
  }, [elements, nextId, history, cleanElementsForHistory]);

  // Correction automatique des éléments spéciaux existants
  (0,react.useEffect)(function () {
    var specialElements = ['product_table', 'customer_info', 'company_logo', 'company_info', 'order_number', 'document_type', 'progress-bar'];
    var needsCorrection = elements.some(function (element) {
      return specialElements.includes(element.type) && element.backgroundColor !== 'transparent';
    });
    if (needsCorrection) {
      setElements(function (prevElements) {
        return prevElements.map(function (element) {
          if (specialElements.includes(element.type) && element.backgroundColor !== 'transparent') {
            return useCanvasState_objectSpread(useCanvasState_objectSpread({}, element), {}, {
              backgroundColor: 'transparent'
            });
          }
          return element;
        });
      });
    }
  }, []); // Uniquement au montage du composant

  var addElement = (0,react.useCallback)(function (elementType) {
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
    var newElement = useCanvasState_objectSpread(useCanvasState_objectSpread({
      id: "element_".concat(nextId),
      type: elementType
    }, defaultProps), properties);

    // Ajustements spécifiques selon le type d'élément
    if (elementType === 'line') {
      newElement.height = 12; // Hauteur fixe pour les lignes
      // Supprimé: newElement.width = 200; // Les lignes utilisent maintenant la largeur complète
    } else if (elementType === 'divider') {
      newElement.height = 16; // Hauteur fixe pour les séparateurs
      newElement.width = 200; // Largeur par défaut pour les séparateurs
    }
    setElements(function (prev) {
      var newElements = [].concat(useCanvasState_toConsumableArray(prev), [newElement]);
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

  var deleteElement = (0,react.useCallback)(function (elementId) {
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
  var deleteSelectedElements = (0,react.useCallback)(function () {
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
  var duplicateElement = (0,react.useCallback)(function (elementId) {
    var element = elements.find(function (el) {
      return el.id === elementId;
    });
    if (element) {
      var duplicatedElement = useCanvasState_objectSpread(useCanvasState_objectSpread({}, element), {}, {
        id: "element_".concat(nextId),
        x: element.x + 20,
        y: element.y + 20
      });
      setElements(function (prev) {
        var newElements = [].concat(useCanvasState_toConsumableArray(prev), [duplicatedElement]);
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
  var duplicateSelectedElements = (0,react.useCallback)(function () {
    var elementsToDuplicate = selection.duplicateSelected();
    var duplicatedElements = [];
    elementsToDuplicate.forEach(function (elementId) {
      var element = elements.find(function (el) {
        return el.id === elementId;
      });
      if (element) {
        var duplicatedElement = useCanvasState_objectSpread(useCanvasState_objectSpread({}, element), {}, {
          id: "element_".concat(nextId + duplicatedElements.length),
          x: element.x + 20,
          y: element.y + 20
        });
        duplicatedElements.push(duplicatedElement);
      }
    });
    if (duplicatedElements.length > 0) {
      setElements(function (prev) {
        var newElements = [].concat(useCanvasState_toConsumableArray(prev), duplicatedElements);
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
  var copySelectedElements = (0,react.useCallback)(function () {
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
  var pasteElements = (0,react.useCallback)(function () {
    clipboard.paste();
  }, [clipboard]);
  var undo = (0,react.useCallback)(function () {
    var previousState = history.undo();
    if (previousState) {
      setElements(previousState.elements);
      setNextId(previousState.nextId);
      selection.clearSelection();
    }
  }, [history, selection]);
  var redo = (0,react.useCallback)(function () {
    var nextState = history.redo();
    if (nextState) {
      setElements(nextState.elements);
      setNextId(nextState.nextId);
      selection.clearSelection();
    }
  }, [history, selection]);
  var saveTemplate = (0,react.useCallback)(/*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
    var isExistingTemplate, checkToastrAvailability, toastrAvailable, _window$pdfBuilderDat, _window$pdfBuilderDat2, _window$pdfBuilderDat3, _window$pdfBuilderAja5, cleanElementForSerialization, cleanedElements, templateData, jsonString, testParse, _iterator, _step, element, formData, _window$pdfBuilderAja, nonceResponse, nonceData, _window$pdfBuilderAja2, _window$pdfBuilderDat4, _window$pdfBuilderAja3, _window$pdfBuilderDat5, _window$pdfBuilderAja4, _window$pdfBuilderDat6, response, result, _result$data, errorMessage, _t, _t2, _t3, _t4;
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
            return useCanvasState_objectSpread(useCanvasState_objectSpread({}, prev), {}, {
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
              var _Object$entries$_i = useCanvasState_slicedToArray(_Object$entries[_i], 2),
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
                if (useCanvasState_typeof(value) === 'object' && value !== null) {
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
                    if (useCanvasState_typeof(item) === 'object' && item !== null) {
                      // Pour les objets dans les tableaux, seulement garder les propriétés primitives
                      var cleanedItem = {};
                      for (var _i2 = 0, _Object$entries2 = Object.entries(item); _i2 < _Object$entries2.length; _i2++) {
                        var _Object$entries2$_i = useCanvasState_slicedToArray(_Object$entries2[_i2], 2),
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
              } else if (useCanvasState_typeof(validatedValue) === 'object') {
                // Pour les objets, nettoyer récursivement mais de manière très stricte
                try {
                  var cleanedObj = {};
                  for (var _i3 = 0, _Object$entries3 = Object.entries(validatedValue); _i3 < _Object$entries3.length; _i3++) {
                    var _Object$entries3$_i = useCanvasState_slicedToArray(_Object$entries3[_i3], 2),
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
                console.warn("Type non support\xE9 ignor\xE9 pour ".concat(key, ": ").concat(useCanvasState_typeof(validatedValue)));
              }
            }
            return cleaned;
          }; // Nettoyer tous les éléments avec protection contre les erreurs
          cleanedElements = [];
          try {
            // console.log('🧹 PDF Builder SAVE - Nettoyage des éléments commencé, éléments bruts:', elements.length);

            // Log détaillé de chaque élément avant filtrage
            elements.forEach(function (element, index) {
              var hasElement = element && useCanvasState_typeof(element) === 'object';
              var hasId = element && element.id;
              var hasType = element && element.type;
              // console.log(`📋 Élément ${index} - valide: ${hasElement}, id: ${hasId ? element.id : 'MISSING'}, type: ${hasType ? element.type : 'MISSING'}`);
              if (!hasElement || !hasId || !hasType) {
                // console.log(`❌ Élément ${index} sera filtré:`, element);
              }
            });
            cleanedElements = elements.filter(function (element) {
              return element && useCanvasState_typeof(element) === 'object' && element.id && element.type;
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
          _iterator = useCanvasState_createForOfIteratorHelper(testParse.elements);
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
          // Obtenir un nonce frais avant la sauvegarde
          _context.p = 15;
          _context.n = 16;
          return fetch(((_window$pdfBuilderAja = window.pdfBuilderAjax) === null || _window$pdfBuilderAja === void 0 ? void 0 : _window$pdfBuilderAja.ajaxurl) || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
              action: 'pdf_builder_get_fresh_nonce'
            })
          });
        case 16:
          nonceResponse = _context.v;
          if (!nonceResponse.ok) {
            _context.n = 18;
            break;
          }
          _context.n = 17;
          return nonceResponse.json();
        case 17:
          nonceData = _context.v;
          if (nonceData.success) {
            formData.append('nonce', nonceData.data.nonce);
          } else {
            formData.append('nonce', ((_window$pdfBuilderAja2 = window.pdfBuilderAjax) === null || _window$pdfBuilderAja2 === void 0 ? void 0 : _window$pdfBuilderAja2.nonce) || ((_window$pdfBuilderDat4 = window.pdfBuilderData) === null || _window$pdfBuilderDat4 === void 0 ? void 0 : _window$pdfBuilderDat4.nonce) || '');
          }
          _context.n = 19;
          break;
        case 18:
          formData.append('nonce', ((_window$pdfBuilderAja3 = window.pdfBuilderAjax) === null || _window$pdfBuilderAja3 === void 0 ? void 0 : _window$pdfBuilderAja3.nonce) || ((_window$pdfBuilderDat5 = window.pdfBuilderData) === null || _window$pdfBuilderDat5 === void 0 ? void 0 : _window$pdfBuilderDat5.nonce) || '');
        case 19:
          _context.n = 21;
          break;
        case 20:
          _context.p = 20;
          _t3 = _context.v;
          console.warn('Erreur lors de l\'obtention du nonce frais:', _t3);
          formData.append('nonce', ((_window$pdfBuilderAja4 = window.pdfBuilderAjax) === null || _window$pdfBuilderAja4 === void 0 ? void 0 : _window$pdfBuilderAja4.nonce) || ((_window$pdfBuilderDat6 = window.pdfBuilderData) === null || _window$pdfBuilderDat6 === void 0 ? void 0 : _window$pdfBuilderDat6.nonce) || '');
        case 21:
          _context.n = 22;
          return fetch(((_window$pdfBuilderAja5 = window.pdfBuilderAjax) === null || _window$pdfBuilderAja5 === void 0 ? void 0 : _window$pdfBuilderAja5.ajaxurl) || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData
          });
        case 22:
          response = _context.v;
          _context.n = 23;
          return response.json();
        case 23:
          result = _context.v;
          if (result.success) {
            _context.n = 24;
            break;
          }
          console.error('❌ PDF Builder SAVE - Échec côté serveur:', {
            success: result.success,
            data: result.data,
            fullResult: result
          });
          throw new Error(((_result$data = result.data) === null || _result$data === void 0 ? void 0 : _result$data.message) || 'Erreur lors de la sauvegarde');
        case 24:
          // Notification de succès pour les templates existants
          if (isExistingTemplate) {
            if (toastrAvailable) {
              toastr.success('Modifications du canvas sauvegardées avec succès !');
            } else {
              alert('Modifications du canvas sauvegardées avec succès !');
            }
          }
          return _context.a(2, templateData);
        case 25:
          _context.p = 25;
          _t4 = _context.v;
          console.error('❌ PDF Builder SAVE - Erreur lors de la sauvegarde:', _t4);
          console.error('❌ PDF Builder SAVE - Détails de l\'erreur:', {
            message: _t4.message,
            stack: _t4.stack,
            name: _t4.name
          });

          // Notification d'erreur
          errorMessage = _t4.message || 'Erreur inconnue lors de la sauvegarde';
          if (toastrAvailable) {
            toastr.error("Erreur lors de la sauvegarde: ".concat(errorMessage));
          } else {
            alert("Erreur lors de la sauvegarde: ".concat(errorMessage));
          }
          throw _t4;
        case 26:
          _context.p = 26;
          // console.log('🏁 PDF Builder SAVE - Fin du processus de sauvegarde');
          setLoadingStates(function (prev) {
            return useCanvasState_objectSpread(useCanvasState_objectSpread({}, prev), {}, {
              saving: false
            });
          });
          return _context.f(26);
        case 27:
          return _context.a(2);
      }
    }, _callee, null, [[15, 20], [6, 10, 11, 12], [4, 13], [3, 25, 26, 27]]);
  })), [elements, canvasWidth, canvasHeight, isSaving, templateId]);
  var loadTemplate = (0,react.useCallback)(function (templateData) {
    if (templateData.elements) {
      setElements(templateData.elements);
      setNextId(templateData.nextId || Math.max.apply(Math, useCanvasState_toConsumableArray(templateData.elements.map(function (el) {
        return parseInt(el.id.split('_')[1]);
      }) || [0])) + 1);
      selection.clearSelection();
      history.clearHistory();
    }
  }, [selection, history]);
  var showContextMenu = (0,react.useCallback)(function (x, y) {
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
  var zoomToSelection = (0,react.useCallback)(function () {
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
  return (0,react.useMemo)(function () {
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
      getAllElements: (0,react.useCallback)(function () {
        return elements;
      }, [elements]),
      getElementById: (0,react.useCallback)(function (id) {
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
;// ./resources/js/hooks/useKeyboardShortcuts.js

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
    onToggleGrid = _ref.onToggleGrid;
  (0,react.useEffect)(function () {
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
  }, [onDelete, onCopy, onPaste, onUndo, onRedo, onSave, onZoomIn, onZoomOut, onSelectAll, onDeselectAll, onToolSelect, onToggleGrid]);
};
;// ./resources/js/hooks/useGlobalSettings.js
function useGlobalSettings_typeof(o) { "@babel/helpers - typeof"; return useGlobalSettings_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, useGlobalSettings_typeof(o); }
function useGlobalSettings_regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return useGlobalSettings_regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (useGlobalSettings_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, useGlobalSettings_regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, useGlobalSettings_regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), useGlobalSettings_regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", useGlobalSettings_regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), useGlobalSettings_regeneratorDefine2(u), useGlobalSettings_regeneratorDefine2(u, o, "Generator"), useGlobalSettings_regeneratorDefine2(u, n, function () { return this; }), useGlobalSettings_regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (useGlobalSettings_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function useGlobalSettings_regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } useGlobalSettings_regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { useGlobalSettings_regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, useGlobalSettings_regeneratorDefine2(e, r, n, t); }
function useGlobalSettings_asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function useGlobalSettings_asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { useGlobalSettings_asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { useGlobalSettings_asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function useGlobalSettings_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function useGlobalSettings_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? useGlobalSettings_ownKeys(Object(t), !0).forEach(function (r) { useGlobalSettings_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : useGlobalSettings_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function useGlobalSettings_defineProperty(e, r, t) { return (r = useGlobalSettings_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function useGlobalSettings_toPropertyKey(t) { var i = useGlobalSettings_toPrimitive(t, "string"); return "symbol" == useGlobalSettings_typeof(i) ? i : i + ""; }
function useGlobalSettings_toPrimitive(t, r) { if ("object" != useGlobalSettings_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != useGlobalSettings_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function useGlobalSettings_slicedToArray(r, e) { return useGlobalSettings_arrayWithHoles(r) || useGlobalSettings_iterableToArrayLimit(r, e) || useGlobalSettings_unsupportedIterableToArray(r, e) || useGlobalSettings_nonIterableRest(); }
function useGlobalSettings_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function useGlobalSettings_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return useGlobalSettings_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? useGlobalSettings_arrayLikeToArray(r, a) : void 0; } }
function useGlobalSettings_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function useGlobalSettings_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function useGlobalSettings_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useGlobalSettings = function useGlobalSettings() {
  var _useState = (0,react.useState)({
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
    _useState2 = useGlobalSettings_slicedToArray(_useState, 2),
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
  (0,react.useEffect)(function () {
    var wpSettings = getWordPressSettings();
    var ajaxSettings = getAjaxSettings();
    if (wpSettings) {
      // Utiliser les paramètres WordPress
      setSettings(function (prev) {
        return useGlobalSettings_objectSpread(useGlobalSettings_objectSpread(useGlobalSettings_objectSpread({}, prev), ajaxSettings), {}, {
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
            return useGlobalSettings_objectSpread(useGlobalSettings_objectSpread(useGlobalSettings_objectSpread({}, prev), _ajaxSettings), parsedSettings);
          });
        } catch (error) {
          setSettings(function (prev) {
            return useGlobalSettings_objectSpread(useGlobalSettings_objectSpread({}, prev), _ajaxSettings);
          });
        }
      } else {
        setSettings(function (prev) {
          return useGlobalSettings_objectSpread(useGlobalSettings_objectSpread({}, prev), _ajaxSettings);
        });
      }
    }
  }, []);

  // Écouter les mises à jour de paramètres en temps réel
  (0,react.useEffect)(function () {
    var handleSettingsUpdate = function handleSettingsUpdate(event) {
      refreshSettings();
    };
    window.addEventListener('pdfBuilderSettingsUpdated', handleSettingsUpdate);
    return function () {
      window.removeEventListener('pdfBuilderSettingsUpdated', handleSettingsUpdate);
    };
  }, []);

  // Appliquer les paramètres aux variables CSS
  (0,react.useEffect)(function () {
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
    var _ref = useGlobalSettings_asyncToGenerator(/*#__PURE__*/useGlobalSettings_regenerator().m(function _callee(newSettings) {
      return useGlobalSettings_regenerator().w(function (_context) {
        while (1) switch (_context.n) {
          case 0:
            setSettings(function (prev) {
              var updated = useGlobalSettings_objectSpread(useGlobalSettings_objectSpread({}, prev), newSettings);
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
                  body: new URLSearchParams(useGlobalSettings_objectSpread(useGlobalSettings_objectSpread({}, wpSettings), {}, {
                    nonce: ajaxSettings.nonce
                  }))
                }).then(function (response) {
                  if (!response.ok) {
                    console.warn('Erreur lors de la sauvegarde des paramètres dans WordPress:', response.status);
                  } else {}
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
        return useGlobalSettings_objectSpread(useGlobalSettings_objectSpread(useGlobalSettings_objectSpread({}, prev), ajaxSettings), {}, {
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
;// ./resources/js/components/FPSCounter.jsx
function FPSCounter_slicedToArray(r, e) { return FPSCounter_arrayWithHoles(r) || FPSCounter_iterableToArrayLimit(r, e) || FPSCounter_unsupportedIterableToArray(r, e) || FPSCounter_nonIterableRest(); }
function FPSCounter_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function FPSCounter_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return FPSCounter_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? FPSCounter_arrayLikeToArray(r, a) : void 0; } }
function FPSCounter_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function FPSCounter_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function FPSCounter_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var FPSCounter = function FPSCounter(_ref) {
  var showFps = _ref.showFps;
  var _useState = (0,react.useState)(0),
    _useState2 = FPSCounter_slicedToArray(_useState, 2),
    fps = _useState2[0],
    setFps = _useState2[1];
  var frameCountRef = (0,react.useRef)(0);
  var lastTimeRef = (0,react.useRef)(performance.now());
  var animationFrameRef = (0,react.useRef)(null);
  (0,react.useEffect)(function () {
    if (!showFps) return;
    var _updateFPS = function updateFPS() {
      var now = performance.now();
      frameCountRef.current++;

      // Mettre à jour les FPS toutes les secondes
      if (now - lastTimeRef.current >= 1000) {
        setFps(Math.round(frameCountRef.current * 1000 / (now - lastTimeRef.current)));
        frameCountRef.current = 0;
        lastTimeRef.current = now;
      }
      animationFrameRef.current = requestAnimationFrame(_updateFPS);
    };
    animationFrameRef.current = requestAnimationFrame(_updateFPS);
    return function () {
      if (animationFrameRef.current) {
        cancelAnimationFrame(animationFrameRef.current);
      }
    };
  }, [showFps]);
  if (!showFps) return null;
  return /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'fixed',
      top: '10px',
      right: '10px',
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      color: '#00ff00',
      padding: '5px 10px',
      borderRadius: '4px',
      fontFamily: 'monospace',
      fontSize: '12px',
      fontWeight: 'bold',
      zIndex: 9999,
      pointerEvents: 'none',
      userSelect: 'none'
    }
  }, "FPS: ", fps);
};
;// ./resources/js/components/ContextMenu.jsx

var ContextMenu = function ContextMenu(_ref) {
  var menu = _ref.menu,
    onAction = _ref.onAction,
    _ref$isAnimating = _ref.isAnimating,
    isAnimating = _ref$isAnimating === void 0 ? false : _ref$isAnimating,
    onClose = _ref.onClose;
  var menuRef = (0,react.useRef)(null);

  // Fermer le menu quand on clique ailleurs
  (0,react.useEffect)(function () {
    var handleClickOutside = function handleClickOutside(e) {
      if (menuRef.current && !menuRef.current.contains(e.target)) {
        if (onClose) {
          onClose();
        }
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return function () {
      return document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [onClose]);

  // Gestionnaire d'action
  var handleAction = function handleAction(item) {
    if (item.action && typeof item.action === 'function') {
      item.action();
    } else if (onAction) {
      onAction(item.action);
    }
  };
  if (!menu) return null;
  return /*#__PURE__*/React.createElement("div", {
    ref: menuRef,
    className: "context-menu",
    style: {
      position: 'fixed',
      left: menu.x,
      top: menu.y,
      backgroundColor: 'white',
      border: '1px solid #ccc',
      borderRadius: '6px',
      boxShadow: '0 8px 24px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.08)',
      zIndex: 1000,
      minWidth: '200px',
      padding: '4px 0',
      animation: isAnimating ? 'contextMenuFadeOut 0.15s cubic-bezier(0.4, 0, 1, 1)' : 'contextMenuFadeIn 0.15s cubic-bezier(0.16, 1, 0.3, 1)',
      transformOrigin: 'top left'
    }
  }, menu.items.map(function (item, index) {
    return /*#__PURE__*/React.createElement("div", {
      key: index
    }, item.type === 'separator' ? /*#__PURE__*/React.createElement("div", {
      style: {
        height: '1px',
        backgroundColor: '#f8f9fa',
        margin: '4px 0'
      }
    }) : /*#__PURE__*/React.createElement("button", {
      className: "context-menu-item",
      onClick: function onClick() {
        return handleAction(item);
      },
      disabled: item.disabled,
      style: {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        width: '100%',
        padding: '10px 16px',
        border: 'none',
        backgroundColor: 'transparent',
        cursor: item.disabled ? 'not-allowed' : 'pointer',
        fontSize: '14px',
        textAlign: 'left',
        borderRadius: '0',
        opacity: item.disabled ? 0.5 : 1,
        transition: 'background-color 0.15s ease, color 0.15s ease'
      },
      onMouseEnter: function onMouseEnter(e) {
        if (!item.disabled) {
          e.target.style.backgroundColor = '#f8f9fa';
          e.target.style.color = '#1e293b';
        }
      },
      onMouseLeave: function onMouseLeave(e) {
        if (!item.disabled) {
          e.target.style.backgroundColor = 'transparent';
          e.target.style.color = 'inherit';
        }
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        alignItems: 'center'
      }
    }, /*#__PURE__*/React.createElement("span", {
      style: {
        marginRight: '8px'
      }
    }, item.icon || getDefaultIcon(item.label)), item.label), getKeyboardShortcut(item.label) && /*#__PURE__*/React.createElement("span", {
      style: {
        fontSize: '12px',
        color: '#666',
        marginLeft: '16px'
      }
    }, getKeyboardShortcut(item.label))));
  }));
};

// Fonction utilitaire pour obtenir une icône par défaut selon le label
var getDefaultIcon = function getDefaultIcon(label) {
  var iconMap = {
    'Dupliquer': '📋',
    'Supprimer': '🗑️',
    'Copier': '[C]',
    'Coller': '[V]',
    'Annuler': '↶',
    'Rétablir': '↷',
    'Tout sélectionner': '☑️',
    'Désélectionner': '☐'
  };
  return iconMap[label] || '•';
};

// Fonction utilitaire pour obtenir un raccourci clavier selon le label
var getKeyboardShortcut = function getKeyboardShortcut(label) {
  var shortcutMap = {
    'Copier': 'Ctrl+C',
    'Coller': 'Ctrl+V',
    'Dupliquer': 'Ctrl+D',
    'Supprimer': 'Del',
    'Annuler': 'Ctrl+Z',
    'Rétablir': 'Ctrl+Y',
    'Tout sélectionner': 'Ctrl+A',
    'Désélectionner': 'Esc'
  };
  return shortcutMap[label];
};
/* harmony default export */ const components_ContextMenu = (ContextMenu);
;// ./resources/js/components/WooCommerceElements.jsx



/**
 * Fonction utilitaire pour obtenir le label d'un élément WooCommerce
 */
var getElementLabel = function getElementLabel(type) {
  var labels = {
    'woocommerce-invoice-number': 'Numéro Facture',
    'woocommerce-invoice-date': 'Date Facture',
    'woocommerce-order-number': 'N° Commande',
    'woocommerce-order-date': 'Date Commande',
    'woocommerce-billing-address': 'Adresse Facturation',
    'woocommerce-shipping-address': 'Adresse Livraison',
    'woocommerce-customer-name': 'Nom Client',
    'woocommerce-customer-email': 'Email Client',
    'woocommerce-payment-method': 'Paiement',
    'woocommerce-order-status': 'Statut',
    'woocommerce-products-table': 'Tableau Produits',
    'woocommerce-products-simple': 'Liste Produits',
    'woocommerce-subtotal': 'Sous-total',
    'woocommerce-discount': 'Remise',
    'woocommerce-shipping': 'Livraison',
    'woocommerce-taxes': 'Taxes',
    'woocommerce-total': 'Total',
    'woocommerce-refund': 'Remboursement',
    'woocommerce-fees': 'Frais',
    'woocommerce-quote-number': 'N° Devis',
    'woocommerce-quote-date': 'Date Devis',
    'woocommerce-quote-validity': 'Validité',
    'woocommerce-quote-notes': 'Notes Devis'
  };
  return labels[type] || 'Élément WC';
};

/**
 * Hook personnalisé pour gérer les éléments WooCommerce
 */
var useWooCommerceElements = function useWooCommerceElements() {
  var getElementDefaults = function getElementDefaults(type) {
    var defaults = {
      width: 200,
      height: 60,
      fontSize: 14,
      fontFamily: 'Arial, sans-serif',
      color: '#333333',
      backgroundColor: '#ffffff',
      borderColor: '#dddddd',
      borderWidth: 1,
      borderStyle: 'solid',
      borderRadius: 4,
      padding: 8
    };

    // Ajustements spécifiques selon le type
    switch (type) {
      case 'woocommerce-billing-address':
      case 'woocommerce-shipping-address':
        defaults.height = 100;
        break;
      case 'woocommerce-products-table':
        defaults.width = 400;
        defaults.height = 150;
        defaults.columns = {
          image: true,
          name: true,
          sku: true,
          quantity: true,
          price: true,
          total: true
        };
        break;
      case 'woocommerce-products-simple':
        defaults.width = 350;
        defaults.height = 120;
        break;
      case 'woocommerce-invoice-number':
      case 'woocommerce-order-number':
      case 'woocommerce-quote-number':
        defaults.width = 150;
        defaults.height = 40;
        break;
      default:
        break;
    }
    return defaults;
  };
  var validateElement = function validateElement(element) {
    // Validation basique des propriétés requises
    return element && element.type && element.id;
  };
  return {
    getElementDefaults: getElementDefaults,
    validateElement: validateElement,
    getElementLabel: getElementLabel
  };
};

/**
 * Composant pour gérer les éléments WooCommerce dans le canvas
 * Ce composant gère l'affichage et le rendu des éléments WooCommerce
 */
var WooCommerceElement = function WooCommerceElement(_ref) {
  var element = _ref.element,
    isSelected = _ref.isSelected,
    onSelect = _ref.onSelect,
    onUpdate = _ref.onUpdate,
    dragAndDrop = _ref.dragAndDrop,
    _ref$zoom = _ref.zoom,
    zoom = _ref$zoom === void 0 ? 1 : _ref$zoom,
    canvasWidth = _ref.canvasWidth,
    canvasHeight = _ref.canvasHeight,
    _ref$orderData = _ref.orderData,
    orderData = _ref$orderData === void 0 ? {} : _ref$orderData,
    onContextMenu = _ref.onContextMenu,
    _ref$snapToGrid = _ref.snapToGrid,
    snapToGrid = _ref$snapToGrid === void 0 ? true : _ref$snapToGrid,
    _ref$gridSize = _ref.gridSize,
    gridSize = _ref$gridSize === void 0 ? 10 : _ref$gridSize;
  var elementRef = (0,react.useRef)(null);
  var resize = useResize({
    onElementResize: function onElementResize(newRect) {
      onUpdate({
        x: newRect.x,
        y: newRect.y,
        width: newRect.width,
        height: newRect.height
      });
    },
    snapToGrid: snapToGrid,
    gridSize: gridSize,
    canvasWidth: canvasWidth,
    canvasHeight: canvasHeight
  });
  var handleMouseDown = function handleMouseDown(e) {
    e.stopPropagation();

    // Calculer les coordonnées relatives au canvas (en tenant compte du zoom)
    var canvas = elementRef.current.closest('.canvas-zoom-wrapper');
    if (!canvas) return;
    var canvasRect = canvas.getBoundingClientRect();
    var elementRect = elementRef.current.getBoundingClientRect();

    // Ajuster pour le zoom - les coordonnées doivent être relatives au canvas non-zoomé
    var clickX = (e.clientX - canvasRect.left) / zoom;
    var clickY = (e.clientY - canvasRect.top) / zoom;
    var handleSize = 8 / zoom; // Ajuster la taille des poignées pour le zoom

    // Poignées de redimensionnement (coordonnées relatives au canvas)
    var handles = [{
      name: 'nw',
      x: element.x,
      y: element.y
    }, {
      name: 'ne',
      x: element.x + element.width,
      y: element.y
    }, {
      name: 'sw',
      x: element.x,
      y: element.y + element.height
    }, {
      name: 'se',
      x: element.x + element.width,
      y: element.y + element.height
    }, {
      name: 'n',
      x: element.x + element.width / 2,
      y: element.y
    }, {
      name: 's',
      x: element.x + element.width / 2,
      y: element.y + element.height
    }, {
      name: 'w',
      x: element.x,
      y: element.y + element.height / 2
    }, {
      name: 'e',
      x: element.x + element.width,
      y: element.y + element.height / 2
    }];
    for (var _i = 0, _handles = handles; _i < _handles.length; _i++) {
      var handle = _handles[_i];
      if (clickX >= handle.x - handleSize / 2 && clickX <= handle.x + handleSize / 2 && clickY >= handle.y - handleSize / 2 && clickY <= handle.y + handleSize / 2) {
        resize.handleResizeStart(e, handle.name, {
          x: element.x,
          y: element.y,
          width: element.width,
          height: element.height
        }, canvasRect, zoom);
        return;
      }
    }

    // Si on clique ailleurs sur l'élément, commencer le drag
    if (dragAndDrop && dragAndDrop.handleMouseDown) {
      var _canvas = elementRef.current.closest('.canvas-zoom-wrapper');
      var _canvasRect = _canvas.getBoundingClientRect();
      dragAndDrop.handleMouseDown(e, element.id, {
        left: element.x,
        top: element.y,
        width: element.width,
        height: element.height
      }, _canvasRect, zoom);
    }
  };
  var handleClick = function handleClick(e) {
    e.stopPropagation();
    if (!isSelected) {
      onSelect(element.id);
    }
  };
  var baseStyle = {
    position: 'absolute',
    left: element.x * zoom,
    top: element.y * zoom,
    width: element.width * zoom,
    height: element.height * zoom,
    cursor: isSelected ? 'move' : 'pointer',
    userSelect: 'none',
    border: isSelected ? "2px solid #007cba" : element.borderWidth > 0 ? "".concat(element.borderWidth || 1, "px ").concat(element.borderStyle || 'solid', " ").concat(element.borderColor || 'transparent') : 'none',
    backgroundColor: element.backgroundColor || 'transparent',
    color: element.color || '#333333',
    fontSize: (element.fontSize || 14) * zoom,
    fontFamily: element.fontFamily || 'Arial, sans-serif',
    padding: (element.padding || 8) * zoom,
    borderRadius: (element.borderRadius || 4) * zoom,
    boxSizing: 'border-box',
    overflow: 'hidden',
    '--element-border-width': isSelected ? '2px' : element.borderWidth > 0 ? "".concat(element.borderWidth || 1, "px") : '0px'
  };

  // Fonction pour obtenir le contenu dynamique selon le type d'élément
  var getElementContent = function getElementContent(type) {
    switch (type) {
      case 'woocommerce-invoice-number':
        return orderData.invoice_number || 'INV-001';
      case 'woocommerce-invoice-date':
        return orderData.invoice_date || '15/10/2025';
      case 'woocommerce-order-number':
        return orderData.order_number || '#12345';
      case 'woocommerce-order-date':
        return orderData.order_date || '15/10/2025';
      case 'woocommerce-customer-name':
        return orderData.customer_name || 'John Doe';
      case 'woocommerce-customer-email':
        return orderData.customer_email || 'john.doe@example.com';
      case 'woocommerce-billing-address':
        return orderData.billing_address || '123 Rue de Test\n75001 Paris\nFrance';
      case 'woocommerce-shipping-address':
        return orderData.shipping_address || '456 Rue de Livraison\n75002 Paris\nFrance';
      case 'woocommerce-payment-method':
        return orderData.payment_method || 'Carte bancaire';
      case 'woocommerce-order-status':
        return orderData.order_status || 'Traitée';
      case 'woocommerce-subtotal':
        return orderData.subtotal || '45,00 €';
      case 'woocommerce-discount':
        return orderData.discount || '-5,00 €';
      case 'woocommerce-shipping':
        return orderData.shipping || '5,00 €';
      case 'woocommerce-taxes':
        return orderData.tax || '9,00 €';
      case 'woocommerce-total':
        return orderData.total || '54,00 €';
      case 'woocommerce-refund':
        return orderData.refund || '0,00 €';
      case 'woocommerce-fees':
        return orderData.fees || '1,50 €';
      case 'woocommerce-quote-number':
        return orderData.quote_number || 'QUO-001';
      case 'woocommerce-quote-date':
        return orderData.quote_date || '15/10/2025';
      case 'woocommerce-quote-validity':
        return orderData.quote_validity || '30 jours';
      case 'woocommerce-quote-notes':
        return orderData.quote_notes || 'Conditions spéciales : paiement à 30 jours.';
      case 'woocommerce-products-table':
        if (orderData.products && orderData.products.length > 0) {
          return orderData.products.map(function (product) {
            return "".concat(product.name, " x").concat(product.quantity, " - ").concat(product.total);
          }).join('\n');
        }
        return 'Produit Test 1 x1 - 25,00 €\nProduit Test 2 x2 - 20,00 €';
      case 'woocommerce-products-simple':
        if (orderData.products && orderData.products.length > 0) {
          return orderData.products.map(function (product) {
            return "".concat(product.quantity, "x ").concat(product.name);
          }).join('\n');
        }
        return '1x Produit Test 1\n2x Produit Test 2';
      default:
        return '[Contenu dynamique WooCommerce]';
    }
  };
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    ref: elementRef,
    style: baseStyle,
    onClick: handleClick,
    onMouseDown: handleMouseDown,
    onContextMenu: onContextMenu
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: 'bold',
      textTransform: 'uppercase',
      letterSpacing: '0.5px'
    }
  }, getElementLabel(element.type)), /*#__PURE__*/React.createElement("div", {
    style: {
      whiteSpace: 'pre-line',
      lineHeight: '1.4',
      color: '#666'
    }
  }, getElementContent(element.type))), isSelected && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "resize-handle nw",
    style: {
      position: 'absolute',
      width: 8,
      height: 8,
      backgroundColor: '#007cba',
      border: '1px solid white',
      pointerEvents: 'auto'
    },
    onMouseDown: function onMouseDown(e) {
      return resize.handleResizeStart(e, 'nw', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "resize-handle ne",
    style: {
      position: 'absolute',
      width: 8,
      height: 8,
      backgroundColor: '#007cba',
      border: '1px solid white',
      pointerEvents: 'auto'
    },
    onMouseDown: function onMouseDown(e) {
      return resize.handleResizeStart(e, 'ne', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "resize-handle sw",
    style: {
      position: 'absolute',
      width: 8,
      height: 8,
      backgroundColor: '#007cba',
      border: '1px solid white',
      pointerEvents: 'auto'
    },
    onMouseDown: function onMouseDown(e) {
      return resize.handleResizeStart(e, 'sw', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "resize-handle se",
    style: {
      position: 'absolute',
      width: 8,
      height: 8,
      backgroundColor: '#007cba',
      border: '1px solid white',
      pointerEvents: 'auto'
    },
    onMouseDown: function onMouseDown(e) {
      return resize.handleResizeStart(e, 'se', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "resize-handle n",
    style: {
      position: 'absolute',
      width: 8,
      height: 8,
      backgroundColor: '#007cba',
      border: '1px solid white',
      pointerEvents: 'auto'
    },
    onMouseDown: function onMouseDown(e) {
      return resize.handleResizeStart(e, 'n', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "resize-handle s",
    style: {
      position: 'absolute',
      width: 8,
      height: 8,
      backgroundColor: '#007cba',
      border: '1px solid white',
      pointerEvents: 'auto'
    },
    onMouseDown: function onMouseDown(e) {
      return resize.handleResizeStart(e, 's', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "resize-handle w",
    style: {
      position: 'absolute',
      width: 8,
      height: 8,
      backgroundColor: '#007cba',
      border: '1px solid white',
      pointerEvents: 'auto'
    },
    onMouseDown: function onMouseDown(e) {
      return resize.handleResizeStart(e, 'w', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "resize-handle e",
    style: {
      position: 'absolute',
      width: 8,
      height: 8,
      backgroundColor: '#007cba',
      border: '1px solid white',
      pointerEvents: 'auto'
    },
    onMouseDown: function onMouseDown(e) {
      return resize.handleResizeStart(e, 'e', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  })));
};
/* harmony default export */ const WooCommerceElements = (WooCommerceElement);
;// ./resources/js/components/ElementLibrary.jsx
function ElementLibrary_typeof(o) { "@babel/helpers - typeof"; return ElementLibrary_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, ElementLibrary_typeof(o); }
function ElementLibrary_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function ElementLibrary_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ElementLibrary_ownKeys(Object(t), !0).forEach(function (r) { ElementLibrary_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ElementLibrary_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function ElementLibrary_defineProperty(e, r, t) { return (r = ElementLibrary_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function ElementLibrary_toPropertyKey(t) { var i = ElementLibrary_toPrimitive(t, "string"); return "symbol" == ElementLibrary_typeof(i) ? i : i + ""; }
function ElementLibrary_toPrimitive(t, r) { if ("object" != ElementLibrary_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != ElementLibrary_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function ElementLibrary_slicedToArray(r, e) { return ElementLibrary_arrayWithHoles(r) || ElementLibrary_iterableToArrayLimit(r, e) || ElementLibrary_unsupportedIterableToArray(r, e) || ElementLibrary_nonIterableRest(); }
function ElementLibrary_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function ElementLibrary_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return ElementLibrary_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? ElementLibrary_arrayLikeToArray(r, a) : void 0; } }
function ElementLibrary_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function ElementLibrary_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function ElementLibrary_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var ElementLibrary = function ElementLibrary(_ref) {
  var onAddElement = _ref.onAddElement,
    selectedTool = _ref.selectedTool,
    onToolSelect = _ref.onToolSelect;
  var _useState = (0,react.useState)(false),
    _useState2 = ElementLibrary_slicedToArray(_useState, 2),
    showHeaderTemplatesModal = _useState2[0],
    setShowHeaderTemplatesModal = _useState2[1];

  // Bibliothèque d'éléments - boutons simples
  var elements = [{
    type: 'product_table',
    fieldID: 'products_table',
    label: 'Tableau Produits',
    icon: '📋',
    description: 'Tableau des produits commandés',
    defaultProperties: {
      showHeaders: true,
      showBorders: false,
      headers: ['Produit', 'Qté', 'Prix'],
      dataSource: 'order_items',
      tableStyle: 'default',
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
      showTotal: false
    }
  }, {
    type: 'customer_info',
    fieldID: 'customer_info',
    label: 'Fiche Client',
    icon: '👤',
    description: 'Informations détaillées du client',
    defaultProperties: {
      showHeaders: true,
      showBorders: false,
      fields: ['name', 'email', 'phone', 'address', 'company', 'vat', 'siret'],
      layout: 'vertical',
      // 'vertical' ou 'horizontal'
      showLabels: true,
      labelStyle: 'bold',
      // 'normal', 'bold', 'uppercase'
      spacing: 8 // espacement en pixels
    }
  }, {
    type: 'company_logo',
    fieldID: 'company_logo',
    label: 'Logo Entreprise',
    icon: '🏢',
    description: 'Logo et identité visuelle de l\'entreprise',
    defaultProperties: {
      imageUrl: '',
      width: 150,
      height: 80,
      alignment: 'left',
      // 'left', 'center', 'right'
      fit: 'contain',
      // 'contain', 'cover', 'fill'
      showBorder: false,
      borderRadius: 0
    }
  }, {
    type: 'company_info',
    fieldID: 'company_info',
    label: 'Informations Entreprise',
    icon: '[D]',
    description: 'Nom, adresse, contact et TVA de l\'entreprise',
    defaultProperties: {
      showHeaders: false,
      showBorders: false,
      fields: ['name', 'address', 'phone', 'email', 'website', 'vat', 'rcs', 'siret'],
      layout: 'vertical',
      showLabels: false,
      labelStyle: 'normal',
      spacing: 4,
      fontSize: 12,
      fontFamily: 'Arial',
      fontWeight: 'normal',
      textAlign: 'left' // 'left', 'center', 'right'
    }
  }, {
    type: 'order_number',
    fieldID: 'order_number',
    label: 'Numéro de Commande',
    icon: '🔢',
    description: 'Référence de commande avec date',
    defaultProperties: {
      showHeaders: false,
      showBorders: false,
      format: 'Commande #{order_number} - {order_date}',
      fontSize: 14,
      fontFamily: 'Arial',
      fontWeight: 'bold',
      textAlign: 'right',
      color: '#333333',
      showLabel: true,
      labelText: 'N° de commande:'
    }
  }, {
    type: 'dynamic-text',
    fieldID: 'dynamic_text',
    label: 'Texte Dynamique',
    icon: '�',
    description: 'Texte avec variables dynamiques',
    defaultProperties: {
      template: 'total_only',
      customContent: '{{order_total}} €',
      fontSize: 14,
      fontFamily: 'Arial',
      fontWeight: 'normal',
      textAlign: 'left',
      color: '#333333'
    }
  }, {
    type: 'mentions',
    fieldID: 'mentions',
    label: 'Mentions légales',
    icon: '📄',
    description: 'Informations légales (email, SIRET, téléphone, etc.)',
    defaultProperties: {
      showEmail: true,
      showPhone: true,
      showSiret: true,
      showVat: false,
      showAddress: false,
      showWebsite: false,
      showCustomText: false,
      customText: '',
      fontSize: 8,
      fontFamily: 'Arial',
      fontWeight: 'normal',
      textAlign: 'center',
      color: '#666666',
      lineHeight: 1.2,
      separator: ' • ',
      layout: 'horizontal' // 'horizontal' ou 'vertical'
    }
  }];
  var handleHeaderTemplateSelect = function handleHeaderTemplateSelect(template) {
    // Ici on peut ajouter la logique pour appliquer le modèle sélectionné
    setShowHeaderTemplatesModal(false);
    // Appliquer le contenu du modèle sélectionné
    onAddElement('text', {
      x: 50,
      y: 50,
      width: 300,
      height: 60,
      text: template.sample.replace('\\n', '\n'),
      fontSize: template.fontSize || 16,
      fontWeight: template.fontWeight || 'normal'
    });
  };
  var headerTemplates = [{
    id: 'classic',
    name: 'Classique',
    sample: '🏢 ENTREPRISE\n123 Rue de la Paix\n75000 Paris',
    fontSize: 14,
    fontWeight: 'bold'
  }, {
    id: 'modern',
    name: 'Moderne',
    sample: '✨ ENTREPRISE MODERNE\nInnovation & Qualité\ncontact@entreprise.com',
    fontSize: 16,
    fontWeight: 'bold'
  }, {
    id: 'minimal',
    name: 'Minimal',
    sample: 'ENTREPRISE\nAdresse • Téléphone • Email',
    fontSize: 12,
    fontWeight: 'normal'
  }, {
    id: 'elegant',
    name: 'Élégant',
    sample: '🎩 Maison Élégante\nParis, France\nwww.entreprise.com',
    fontSize: 15,
    fontWeight: 'bold'
  }];

  // Gestionnaire pour le drag start - REMOVED: plus d'éléments à dragger

  return /*#__PURE__*/React.createElement(React.Fragment, null, showHeaderTemplatesModal && /*#__PURE__*/React.createElement("div", {
    className: "modal-overlay",
    onClick: function onClick() {
      return setShowHeaderTemplatesModal(false);
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "modal-content",
    onClick: function onClick(e) {
      return e.stopPropagation();
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "modal-header"
  }, /*#__PURE__*/React.createElement("h3", null, "\uD83C\uDFA8 Choisir un mod\xE8le d'en-t\xEAte"), /*#__PURE__*/React.createElement("button", {
    className: "modal-close",
    onClick: function onClick() {
      return setShowHeaderTemplatesModal(false);
    }
  }, "\xD7")), /*#__PURE__*/React.createElement("div", {
    className: "modal-body"
  }, /*#__PURE__*/React.createElement("div", {
    className: "templates-grid"
  }, headerTemplates.map(function (template) {
    return /*#__PURE__*/React.createElement("div", {
      key: template.id,
      className: "template-item",
      onClick: function onClick() {
        return handleHeaderTemplateSelect(template);
      }
    }, /*#__PURE__*/React.createElement("div", {
      className: "template-sample"
    }, template.sample.split('\n').map(function (line, index) {
      return /*#__PURE__*/React.createElement("div", {
        key: index,
        style: {
          fontSize: template.fontSize,
          fontWeight: template.fontWeight,
          marginBottom: '4px',
          whiteSpace: 'pre-wrap'
        }
      }, line);
    })), /*#__PURE__*/React.createElement("div", {
      className: "template-name"
    }, template.name));
  }))))), /*#__PURE__*/React.createElement("div", {
    className: "element-library"
  }, /*#__PURE__*/React.createElement("div", {
    className: "library-header"
  }, /*#__PURE__*/React.createElement("h3", null, "\uD83D\uDCDA Biblioth\xE8que d'\xC9l\xE9ments"), /*#__PURE__*/React.createElement("p", {
    className: "library-subtitle"
  }, "Cliquez sur les blocs pour les ajouter au canvas")), /*#__PURE__*/React.createElement("div", {
    className: "library-content"
  }, /*#__PURE__*/React.createElement("div", {
    className: "elements-list"
  }, elements.map(function (element, index) {
    return /*#__PURE__*/React.createElement("button", {
      key: index,
      className: "element-button",
      onClick: function onClick() {
        onAddElement(element.type, ElementLibrary_objectSpread({
          x: 50 + index * 20,
          y: 100 + index * 20,
          width: 300,
          height: 150
        }, element.defaultProperties));
      },
      title: element.description
    }, /*#__PURE__*/React.createElement("span", {
      className: "element-icon"
    }, element.icon), /*#__PURE__*/React.createElement("div", {
      className: "element-info"
    }, /*#__PURE__*/React.createElement("div", {
      className: "element-label"
    }, element.label), /*#__PURE__*/React.createElement("div", {
      className: "element-description"
    }, element.description)));
  })))));
};
/* harmony default export */ const components_ElementLibrary = (ElementLibrary);
;// ./resources/js/services/ElementCustomizationService.js
function ElementCustomizationService_typeof(o) { "@babel/helpers - typeof"; return ElementCustomizationService_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, ElementCustomizationService_typeof(o); }
function ElementCustomizationService_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function ElementCustomizationService_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ElementCustomizationService_ownKeys(Object(t), !0).forEach(function (r) { ElementCustomizationService_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ElementCustomizationService_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function ElementCustomizationService_defineProperty(e, r, t) { return (r = ElementCustomizationService_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, ElementCustomizationService_toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function ElementCustomizationService_toPropertyKey(t) { var i = ElementCustomizationService_toPrimitive(t, "string"); return "symbol" == ElementCustomizationService_typeof(i) ? i : i + ""; }
function ElementCustomizationService_toPrimitive(t, r) { if ("object" != ElementCustomizationService_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != ElementCustomizationService_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
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
          height: 2
        }
      };
      return ElementCustomizationService_objectSpread(ElementCustomizationService_objectSpread({}, defaults), typeAdjustments[elementType] || {});
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
;// ./resources/js/hooks/useElementCustomization.js
function useElementCustomization_typeof(o) { "@babel/helpers - typeof"; return useElementCustomization_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, useElementCustomization_typeof(o); }
function useElementCustomization_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function useElementCustomization_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? useElementCustomization_ownKeys(Object(t), !0).forEach(function (r) { useElementCustomization_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : useElementCustomization_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function useElementCustomization_defineProperty(e, r, t) { return (r = useElementCustomization_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function useElementCustomization_toPropertyKey(t) { var i = useElementCustomization_toPrimitive(t, "string"); return "symbol" == useElementCustomization_typeof(i) ? i : i + ""; }
function useElementCustomization_toPrimitive(t, r) { if ("object" != useElementCustomization_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != useElementCustomization_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function useElementCustomization_slicedToArray(r, e) { return useElementCustomization_arrayWithHoles(r) || useElementCustomization_iterableToArrayLimit(r, e) || useElementCustomization_unsupportedIterableToArray(r, e) || useElementCustomization_nonIterableRest(); }
function useElementCustomization_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function useElementCustomization_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return useElementCustomization_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? useElementCustomization_arrayLikeToArray(r, a) : void 0; } }
function useElementCustomization_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function useElementCustomization_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function useElementCustomization_arrayWithHoles(r) { if (Array.isArray(r)) return r; }




/**
 * Hook pour gérer la personnalisation des éléments
 * Gère l'état local des propriétés et les changements en temps réel
 */
var useElementCustomization = function useElementCustomization(selectedElements, elements, onPropertyChange) {
  var _useState = (0,react.useState)({}),
    _useState2 = useElementCustomization_slicedToArray(_useState, 2),
    localProperties = _useState2[0],
    setLocalProperties = _useState2[1];
  var _useState3 = (0,react.useState)('appearance'),
    _useState4 = useElementCustomization_slicedToArray(_useState3, 2),
    activeTab = _useState4[0],
    setActiveTab = _useState4[1];

  // Obtenir l'élément sélectionné (mémorisé pour éviter les re-renders)
  var selectedElement = (0,react.useMemo)(function () {
    return selectedElements.length > 0 ? elements.find(function (el) {
      return el.id === selectedElements[0];
    }) : null;
  }, [selectedElements, elements]);

  // Synchroniser les propriétés locales avec l'élément sélectionné
  (0,react.useEffect)(function () {
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
      var restrictedDefaults = useElementCustomization_objectSpread({}, defaultProperties);

      // Pour les propriétés restreintes, utiliser les valeurs par défaut spécifiques
      Object.keys(defaultProperties).forEach(function (property) {
        var specificDefault = getPropertyDefault(elementType, property);
        if (specificDefault !== null) {
          restrictedDefaults[property] = specificDefault;
        }

        // Pour les éléments spéciaux, forcer backgroundColor à transparent
        if (ELEMENT_TYPE_MAPPING[elementType] === 'special' && property === 'backgroundColor') {
          restrictedDefaults[property] = 'transparent';
        }
      });
      var newProperties = useElementCustomization_objectSpread(useElementCustomization_objectSpread({}, restrictedDefaults), selectedElement);

      // Pour les propriétés restreintes, forcer les valeurs corrigées
      Object.keys(restrictedDefaults).forEach(function (property) {
        var isRestricted = !isPropertyAllowed(elementType, property);
        if (isRestricted) {
          var correctedValue = fixInvalidProperty(elementType, property, newProperties[property]);
          if (correctedValue !== newProperties[property]) {
            newProperties[property] = correctedValue;
          }
        }
        // Pour les éléments spéciaux, garder backgroundColor à transparent par défaut seulement si pas défini
        else if (ELEMENT_TYPE_MAPPING[elementType] === 'special' && property === 'backgroundColor' && !newProperties[property]) {
          newProperties[property] = 'transparent';
        }
      });

      // Validation finale des propriétés
      Object.keys(newProperties).forEach(function (property) {
        var validation = validateProperty(elementType, property, newProperties[property]);
        if (!validation.valid) {
          newProperties[property] = fixInvalidProperty(elementType, property, newProperties[property]);
        }
      });
      setLocalProperties(newProperties);
    } else {
      setLocalProperties({});
    }
  }, [selectedElement === null || selectedElement === void 0 ? void 0 : selectedElement.id]); // Ne dépendre que de l'ID pour éviter les re-renders inutiles

  // Gestionnaire de changement de propriété avec validation
  var handlePropertyChange = (0,react.useCallback)(function (elementId, property, value) {
    var element = elements.find(function (el) {
      return el.id === elementId;
    });
    if (!element) return;
    var validatedValue = value;

    // Validation selon le système de restrictions
    var validation = validateProperty(element.type, property, value);
    if (!validation.valid) {
      // Ne pas appliquer le changement si la propriété n'est pas autorisée
      return;
    }

    // Validation supplémentaire selon le type de propriété (service existant)
    if (typeof value !== 'boolean' && !property.startsWith('columns.')) {
      try {
        var serviceValidated = elementCustomizationService.validateProperty(property, value);
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
            if (!current[key] || useElementCustomization_typeof(current[key]) !== 'object') {
              current[key] = {};
            } else {
              current[key] = useElementCustomization_objectSpread({}, current[key]); // Créer une copie pour éviter de modifier l'original
            }
            return current[key];
          }, obj);
          target[lastKey] = value;
          return obj;
        };
        newProperties = useElementCustomization_objectSpread({}, prev);
        updateNestedProperty(newProperties, property, validatedValue);
      } else {
        newProperties = useElementCustomization_objectSpread(useElementCustomization_objectSpread({}, prev), {}, useElementCustomization_defineProperty({}, property, validatedValue));
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
  var applyPropertyPreset = (0,react.useCallback)(function (elementId, preset) {
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
        var _ref2 = useElementCustomization_slicedToArray(_ref, 2),
          property = _ref2[0],
          value = _ref2[1];
        handlePropertyChange(elementId, property, value);
      });
    }
  }, [handlePropertyChange]);

  // Réinitialiser les propriétés aux valeurs par défaut
  var resetProperties = (0,react.useCallback)(function (elementId) {
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
      var _ref4 = useElementCustomization_slicedToArray(_ref3, 2),
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
;// ./resources/js/hooks/useElementSynchronization.js


/**
 * Hook pour gérer la synchronisation des personnalisations d'éléments
 * Gère la persistance, la validation et la synchronisation avec le backend
 */
var useElementSynchronization = function useElementSynchronization(elements, onPropertyChange, onBatchUpdate) {
  var autoSave = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : true;
  var autoSaveDelay = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 3000;
  var pendingChangesRef = (0,react.useRef)(new Map());
  var autoSaveTimeoutRef = (0,react.useRef)(null);
  var lastSavedRef = (0,react.useRef)(new Map());

  // Synchronisation différée pour éviter les appels trop fréquents
  var debouncedSync = (0,react.useCallback)(function (elementId, property, value) {
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
  var immediateSync = (0,react.useCallback)(function (elementId, property, value) {
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
  var syncPendingChanges = (0,react.useCallback)(function () {
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
  var forceSync = (0,react.useCallback)(function () {
    syncPendingChanges();
  }, [syncPendingChanges]);

  // Vérifier si des changements sont en attente
  var hasPendingChanges = (0,react.useCallback)(function () {
    return pendingChangesRef.current.size > 0;
  }, []);

  // Obtenir les changements en attente pour un élément
  var getPendingChanges = (0,react.useCallback)(function (elementId) {
    var changes = [];
    pendingChangesRef.current.forEach(function (change, key) {
      if (change.elementId === elementId) {
        changes.push(change);
      }
    });
    return changes;
  }, []);

  // Annuler les changements en attente pour un élément
  var cancelPendingChanges = (0,react.useCallback)(function (elementId) {
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
  var restoreLastSaved = (0,react.useCallback)(function (elementId, property) {
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
  var validateAndSync = (0,react.useCallback)(function (elementId, property, value, validator) {
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
  (0,react.useEffect)(function () {
    return function () {
      if (autoSaveTimeoutRef.current) {
        clearTimeout(autoSaveTimeoutRef.current);
      }
    };
  }, []);

  // Synchronisation automatique lors des changements d'éléments
  (0,react.useEffect)(function () {
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
;// ./resources/js/components/PropertiesPanel.jsx
function PropertiesPanel_regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return PropertiesPanel_regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (PropertiesPanel_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, PropertiesPanel_regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, PropertiesPanel_regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), PropertiesPanel_regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", PropertiesPanel_regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), PropertiesPanel_regeneratorDefine2(u), PropertiesPanel_regeneratorDefine2(u, o, "Generator"), PropertiesPanel_regeneratorDefine2(u, n, function () { return this; }), PropertiesPanel_regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (PropertiesPanel_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function PropertiesPanel_regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } PropertiesPanel_regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { PropertiesPanel_regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, PropertiesPanel_regeneratorDefine2(e, r, n, t); }
function PropertiesPanel_asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function PropertiesPanel_asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { PropertiesPanel_asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { PropertiesPanel_asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function PropertiesPanel_slicedToArray(r, e) { return PropertiesPanel_arrayWithHoles(r) || PropertiesPanel_iterableToArrayLimit(r, e) || PropertiesPanel_unsupportedIterableToArray(r, e) || PropertiesPanel_nonIterableRest(); }
function PropertiesPanel_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function PropertiesPanel_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function PropertiesPanel_arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function PropertiesPanel_toConsumableArray(r) { return PropertiesPanel_arrayWithoutHoles(r) || PropertiesPanel_iterableToArray(r) || PropertiesPanel_unsupportedIterableToArray(r) || PropertiesPanel_nonIterableSpread(); }
function PropertiesPanel_nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function PropertiesPanel_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return PropertiesPanel_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? PropertiesPanel_arrayLikeToArray(r, a) : void 0; } }
function PropertiesPanel_iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function PropertiesPanel_arrayWithoutHoles(r) { if (Array.isArray(r)) return PropertiesPanel_arrayLikeToArray(r); }
function PropertiesPanel_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }





// Configuration des presets par template pour le texte dynamique
var TEMPLATE_PRESETS = {
  'total_only': {
    fontSize: 16,
    fontWeight: 'bold',
    textAlign: 'right',
    color: '#2563eb'
  },
  'order_info': {
    fontSize: 12,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#374151'
  },
  'customer_info': {
    fontSize: 12,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#374151'
  },
  'customer_address': {
    fontSize: 11,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#374151',
    lineHeight: 1.3
  },
  'full_header': {
    fontSize: 14,
    fontWeight: 'bold',
    textAlign: 'center',
    color: '#1f2937'
  },
  'invoice_header': {
    fontSize: 18,
    fontWeight: 'bold',
    textAlign: 'center',
    color: '#1f2937',
    fontFamily: 'Arial'
  },
  'order_summary': {
    fontSize: 11,
    fontWeight: 'normal',
    textAlign: 'right',
    color: '#374151',
    lineHeight: 1.4
  },
  'payment_info': {
    fontSize: 12,
    fontWeight: 'bold',
    textAlign: 'left',
    color: '#059669'
  },
  'payment_terms': {
    fontSize: 10,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#6b7280',
    lineHeight: 1.3
  },
  'shipping_info': {
    fontSize: 11,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#374151',
    lineHeight: 1.3
  },
  'thank_you': {
    fontSize: 14,
    fontWeight: 'normal',
    textAlign: 'center',
    color: '#059669',
    fontStyle: 'italic'
  },
  'legal_notice': {
    fontSize: 9,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#6b7280',
    lineHeight: 1.2
  },
  'bank_details': {
    fontSize: 10,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#374151',
    fontFamily: 'Courier New'
  },
  'contact_info': {
    fontSize: 11,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#374151'
  },
  'order_confirmation': {
    fontSize: 14,
    fontWeight: 'bold',
    textAlign: 'center',
    color: '#059669'
  },
  'delivery_note': {
    fontSize: 12,
    fontWeight: 'bold',
    textAlign: 'left',
    color: '#1f2937'
  },
  'warranty_info': {
    fontSize: 10,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#059669',
    lineHeight: 1.3
  },
  'return_policy': {
    fontSize: 10,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#dc2626',
    lineHeight: 1.3
  },
  'signature_line': {
    fontSize: 11,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#374151'
  },
  'invoice_footer': {
    fontSize: 9,
    fontWeight: 'normal',
    textAlign: 'center',
    color: '#6b7280'
  },
  'terms_conditions': {
    fontSize: 9,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#6b7280',
    lineHeight: 1.2
  },
  'quality_guarantee': {
    fontSize: 11,
    fontWeight: 'normal',
    textAlign: 'center',
    color: '#059669'
  },
  'eco_friendly': {
    fontSize: 11,
    fontWeight: 'normal',
    textAlign: 'center',
    color: '#059669'
  },
  'follow_up': {
    fontSize: 10,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#374151'
  },
  'custom': {
    fontSize: 14,
    fontWeight: 'normal',
    textAlign: 'left',
    color: '#374151'
  }
};
var ELEMENT_PROPERTY_PROFILES = {
  // Éléments texte
  text: {
    appearance: {
      sections: ['colors', 'typography', 'borders', 'effects'],
      properties: {
        colors: ['color', 'backgroundColor'],
        typography: ['fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'textDecoration', 'textAlign', 'textTransform', 'lineHeight', 'letterSpacing'],
        borders: ['borderWidth', 'borderColor', 'borderRadius'],
        effects: ['opacity', 'shadow']
      }
    },
    layout: {
      sections: ['position', 'dimensions', 'transform', 'layers'],
      properties: {
        position: ['x', 'y'],
        dimensions: ['width', 'height'],
        transform: ['rotation'],
        layers: ['zIndex']
      }
    },
    content: {
      sections: ['text', 'variables'],
      properties: {
        text: ['text'],
        variables: ['variables']
      }
    },
    effects: {
      sections: ['opacity', 'shadows', 'filters'],
      properties: {
        opacity: ['opacity'],
        shadows: ['shadow', 'shadowColor', 'shadowOffsetX', 'shadowOffsetY'],
        filters: ['brightness', 'contrast', 'saturate']
      }
    }
  },
  // Éléments image/logo (pas de propriétés texte)
  logo: {
    appearance: {
      sections: ['colors', 'borders', 'effects'],
      properties: {
        colors: ['backgroundColor'],
        // seulement le fond, pas de couleur texte
        borders: ['borderWidth', 'borderColor', 'borderRadius'],
        effects: ['opacity', 'shadow']
      }
    },
    layout: {
      sections: ['position', 'dimensions', 'transform', 'layers'],
      properties: {
        position: ['x', 'y'],
        dimensions: ['width', 'height'],
        transform: ['rotation'],
        layers: ['zIndex']
      }
    },
    content: {
      sections: ['image'],
      properties: {
        image: ['imageUrl', 'alt', 'objectFit']
      }
    },
    effects: {
      sections: ['opacity', 'shadows'],
      properties: {
        opacity: ['opacity'],
        shadows: ['shadow', 'shadowColor', 'shadowOffsetX', 'shadowOffsetY']
        // pas de filters pour les images
      }
    }
  },
  // Logo entreprise (même propriétés que logo)
  company_logo: {
    appearance: {
      sections: ['colors', 'borders', 'effects'],
      properties: {
        colors: ['backgroundColor'],
        borders: ['borderWidth', 'borderColor', 'borderRadius'],
        effects: ['opacity', 'shadow']
      }
    },
    layout: {
      sections: ['position', 'dimensions', 'transform', 'layers'],
      properties: {
        position: ['x', 'y'],
        dimensions: ['width', 'height'],
        transform: ['rotation'],
        layers: ['zIndex']
      }
    },
    content: {
      sections: ['image'],
      properties: {
        image: ['imageUrl', 'alt', 'objectFit']
      }
    },
    effects: {
      sections: ['opacity', 'shadows'],
      properties: {
        opacity: ['opacity'],
        shadows: ['shadow', 'shadowColor', 'shadowOffsetX', 'shadowOffsetY']
      }
    }
  },
  // Tableaux produits (propriétés simplifiées - focus sur la structure)
  product_table: {
    appearance: {
      sections: ['colors', 'borders', 'effects'],
      properties: {
        colors: ['backgroundColor'],
        // seulement le fond du tableau, pas de couleur texte individuelle
        borders: ['borderWidth', 'borderColor', 'borderRadius'],
        effects: ['opacity', 'shadow']
      }
    },
    layout: {
      sections: ['position', 'dimensions', 'transform', 'layers'],
      properties: {
        position: ['x', 'y'],
        dimensions: ['width', 'height'],
        transform: ['rotation'],
        layers: ['zIndex']
      }
    },
    content: {
      sections: ['table'],
      properties: {
        table: ['columns', 'showHeaders', 'showBorders', 'tableStyle']
      }
    },
    effects: {
      sections: ['opacity', 'shadows'],
      properties: {
        opacity: ['opacity'],
        shadows: ['shadow', 'shadowColor', 'shadowOffsetX', 'shadowOffsetY']
      }
    }
  },
  // Éléments d'informations client (accès aux couleurs et apparence)
  customer_info: {
    appearance: {
      sections: ['colors', 'typography', 'borders', 'effects'],
      properties: {
        colors: ['color', 'backgroundColor'],
        typography: ['fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'textDecoration', 'textAlign', 'textTransform', 'lineHeight'],
        borders: ['borderWidth', 'borderColor', 'borderRadius'],
        effects: ['opacity', 'shadow']
      }
    },
    layout: {
      sections: ['position', 'dimensions', 'transform', 'layers'],
      properties: {
        position: ['x', 'y'],
        dimensions: ['width', 'height'],
        transform: ['rotation'],
        layers: ['zIndex']
      }
    },
    content: {
      sections: ['customer_fields'],
      properties: {
        customer_fields: ['customerName', 'customerAddress', 'customerPhone', 'customerEmail']
      }
    },
    effects: {
      sections: ['opacity', 'shadows'],
      properties: {
        opacity: ['opacity'],
        shadows: ['shadow', 'shadowColor', 'shadowOffsetX', 'shadowOffsetY']
      }
    }
  },
  // Éléments d'informations entreprise (accès aux couleurs et apparence)
  company_info: {
    appearance: {
      sections: ['colors', 'typography', 'borders', 'effects'],
      properties: {
        colors: ['color', 'backgroundColor'],
        typography: ['fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'textDecoration', 'textAlign', 'textTransform', 'lineHeight'],
        borders: ['borderWidth', 'borderColor', 'borderRadius'],
        effects: ['opacity', 'shadow']
      }
    },
    layout: {
      sections: ['position', 'dimensions', 'transform', 'layers'],
      properties: {
        position: ['x', 'y'],
        dimensions: ['width', 'height'],
        transform: ['rotation'],
        layers: ['zIndex']
      }
    },
    content: {
      sections: ['company_fields'],
      properties: {
        company_fields: ['companyName', 'companyAddress', 'companyPhone', 'companyEmail', 'companyLogo']
      }
    },
    effects: {
      sections: ['opacity', 'shadows'],
      properties: {
        opacity: ['opacity'],
        shadows: ['shadow', 'shadowColor', 'shadowOffsetX', 'shadowOffsetY']
      }
    }
  },
  // Éléments texte dynamiques (même propriétés que text)
  'dynamic-text': {
    appearance: {
      sections: ['colors', 'typography', 'borders', 'effects'],
      properties: {
        colors: ['color', 'backgroundColor'],
        typography: ['fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'textDecoration', 'textAlign', 'textTransform', 'lineHeight', 'letterSpacing'],
        borders: ['borderWidth', 'borderColor', 'borderRadius'],
        effects: ['opacity', 'shadow']
      }
    },
    layout: {
      sections: ['position', 'dimensions', 'transform', 'layers'],
      properties: {
        position: ['x', 'y'],
        dimensions: ['width', 'height'],
        transform: ['rotation'],
        layers: ['zIndex']
      }
    },
    content: {
      sections: ['dynamic_text', 'variables'],
      properties: {
        dynamic_text: ['dynamicText'],
        variables: ['variables']
      }
    },
    effects: {
      sections: ['opacity', 'shadows', 'filters'],
      properties: {
        opacity: ['opacity'],
        shadows: ['shadow', 'shadowColor', 'shadowOffsetX', 'shadowOffsetY'],
        filters: ['brightness', 'contrast', 'saturate']
      }
    }
  },
  // Éléments par défaut (forme géométrique)
  "default": {
    appearance: {
      sections: ['colors', 'borders', 'effects'],
      properties: {
        colors: ['backgroundColor'],
        borders: ['borderWidth', 'borderColor', 'borderRadius'],
        effects: ['opacity', 'shadow']
      }
    },
    layout: {
      sections: ['position', 'dimensions', 'transform', 'layers'],
      properties: {
        position: ['x', 'y'],
        dimensions: ['width', 'height'],
        transform: ['rotation'],
        layers: ['zIndex']
      }
    },
    content: {
      sections: [],
      properties: {}
    },
    effects: {
      sections: ['opacity', 'shadows'],
      properties: {
        opacity: ['opacity'],
        shadows: ['shadow', 'shadowColor', 'shadowOffsetX', 'shadowOffsetY']
      }
    }
  }
};

// Système simplifié : toutes les propriétés sont disponibles pour tous les éléments
// On cache seulement quelques sections pour certains types d'éléments
var shouldShowSection = function shouldShowSection(sectionName, elementType) {
  // Sections à cacher selon le type d'élément
  var hiddenSections = {
    // Pour les logos : pas de typographie
    logo: ['typography'],
    company_logo: ['typography'],
    // Pour les tableaux : pas de typographie (trop complexe)
    product_table: ['typography']
  };
  var elementHiddenSections = hiddenSections[elementType] || [];
  return !elementHiddenSections.includes(sectionName);
};
var safeParseFloat = function safeParseFloat(value) {
  var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
  if (value === null || value === undefined || value === '') return defaultValue;
  var parsed = parseFloat(value);
  return isNaN(parsed) ? defaultValue : parsed;
};
var safeParseInt = function safeParseInt(value) {
  var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
  if (value === null || value === undefined || value === '') return defaultValue;
  var parsed = parseInt(value, 10);
  return isNaN(parsed) ? defaultValue : parsed;
};

// Fonction pour obtenir l'ordre intelligent des propriétés selon le type d'élément
var getSmartPropertyOrder = function getSmartPropertyOrder(elementType, tab) {
  var _orders$tab, _orders$tab2;
  var orders = {
    // Ordre pour l'onglet Apparence
    appearance: {
      // Éléments texte : couleur et police en premier
      text: ['colors', 'typography', 'borders', 'effects'],
      'dynamic-text': ['colors', 'typography', 'borders', 'effects'],
      'layout-header': ['colors', 'typography', 'borders', 'effects'],
      'layout-footer': ['colors', 'typography', 'borders', 'effects'],
      'layout-section': ['colors', 'typography', 'borders', 'effects'],
      // Éléments image : couleur de fond et bordures en premier
      logo: ['colors', 'borders', 'effects'],
      company_logo: ['colors', 'borders', 'effects'],
      // Tableaux : couleurs, police, bordures
      product_table: ['colors', 'typography', 'borders', 'effects'],
      // Éléments de données : couleurs et police
      customer_info: ['colors', 'typography', 'borders', 'effects'],
      company_info: ['colors', 'typography', 'borders', 'effects'],
      document_type: ['colors', 'typography', 'borders', 'effects'],
      order_number: ['colors', 'typography', 'borders', 'effects'],
      mentions: ['colors', 'typography', 'borders', 'effects'],
      // Par défaut
      "default": ['colors', 'borders', 'effects']
    },
    // Ordre pour l'onglet Mise en page
    layout: {
      // Tous les éléments : position et dimensions d'abord
      "default": ['position', 'dimensions', 'transform', 'layers']
    },
    // Ordre pour l'onglet Contenu
    content: {
      // Éléments texte : contenu textuel en premier
      text: ['text', 'variables'],
      'dynamic-text': ['dynamic_text', 'variables'],
      'layout-header': ['text', 'variables'],
      'layout-footer': ['text', 'variables'],
      'layout-section': ['text', 'variables'],
      // Éléments image : propriétés d'image
      logo: ['image'],
      company_logo: ['image'],
      // Éléments de données : champs spécifiques
      customer_info: ['customer_fields'],
      company_info: ['company_fields'],
      product_table: ['table'],
      document_type: ['document_type'],
      order_number: ['order_number'],
      mentions: ['mentions'],
      // Par défaut
      "default": []
    },
    // Ordre pour l'onglet Effets
    effects: {
      // Tous les éléments : opacité en premier, puis effets visuels
      "default": ['opacity', 'shadows', 'filters']
    }
  };
  return ((_orders$tab = orders[tab]) === null || _orders$tab === void 0 ? void 0 : _orders$tab[elementType]) || ((_orders$tab2 = orders[tab]) === null || _orders$tab2 === void 0 ? void 0 : _orders$tab2["default"]) || [];
};

// Composant amélioré pour les contrôles de couleur avec presets
var ColorPicker = function ColorPicker(_ref) {
  var label = _ref.label,
    value = _ref.value,
    _onChange = _ref.onChange,
    _ref$presets = _ref.presets,
    presets = _ref$presets === void 0 ? [] : _ref$presets,
    _ref$defaultColor = _ref.defaultColor,
    defaultColor = _ref$defaultColor === void 0 ? '#ffffff' : _ref$defaultColor;
  // Fonction pour valider et normaliser une couleur hex
  var normalizeColor = function normalizeColor(color) {
    if (!color || color === 'transparent') return defaultColor;
    if (color.startsWith('#') && (color.length === 4 || color.length === 7)) return color;
    return defaultColor; // fallback
  };

  // Valeur normalisée pour l'input color
  var inputValue = normalizeColor(value);

  // Fonction pour vérifier si une couleur est valide pour les presets
  var isValidColor = function isValidColor(color) {
    return color && color !== 'transparent' && color.startsWith('#');
  };
  return /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, label, ":"), /*#__PURE__*/React.createElement("div", {
    className: "color-picker-container"
  }, /*#__PURE__*/React.createElement("input", {
    type: "color",
    value: inputValue,
    onChange: function onChange(e) {
      var newColor = e.target.value;
      _onChange(newColor);
    },
    className: "color-input",
    title: "Couleur actuelle: ".concat(value || 'transparent')
  }), /*#__PURE__*/React.createElement("div", {
    className: "color-presets"
  }, presets.filter(isValidColor).map(function (preset, index) {
    return /*#__PURE__*/React.createElement("button", {
      key: index,
      className: "color-preset ".concat(value === preset ? 'active' : ''),
      style: {
        backgroundColor: preset,
        border: value === preset ? '2px solid #2563eb' : '1px solid #e2e8f0'
      },
      onClick: function onClick() {
        return _onChange(preset);
      },
      title: "".concat(label, ": ").concat(preset),
      "aria-label": "S\xE9lectionner la couleur ".concat(preset)
    });
  }), presets.includes('transparent') && /*#__PURE__*/React.createElement("button", {
    className: "color-preset transparent ".concat(value === 'transparent' ? 'active' : ''),
    style: {
      background: value === 'transparent' ? 'repeating-conic-gradient(#f0f0f0 0% 25%, #ffffff 0% 50%) 50% / 10px 10px' : 'repeating-conic-gradient(#e2e8f0 0% 25%, #ffffff 0% 50%) 50% / 10px 10px',
      border: value === 'transparent' ? '2px solid #2563eb' : '1px solid #e2e8f0'
    },
    onClick: function onClick() {
      return _onChange('transparent');
    },
    title: "".concat(label, ": Transparent"),
    "aria-label": "Rendre transparent"
  }))));
};

// Composant pour les contrôles de police
var FontControls = function FontControls(_ref2) {
  var _properties$fontSize, _properties$fontSize2, _properties$lineHeigh, _properties$lineHeigh2, _properties$letterSpa, _properties$letterSpa2, _properties$opacity, _properties$opacity2, _properties$textShado, _properties$textShado2;
  var elementId = _ref2.elementId,
    properties = _ref2.properties,
    onPropertyChange = _ref2.onPropertyChange;
  return /*#__PURE__*/React.createElement("div", {
    className: "properties-group"
  }, /*#__PURE__*/React.createElement("h4", null, "\uD83C\uDFA8 Police & Style"), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Famille:"), /*#__PURE__*/React.createElement("select", {
    value: properties.fontFamily || 'Inter',
    onChange: function onChange(e) {
      return onPropertyChange(elementId, 'fontFamily', e.target.value);
    }
  }, /*#__PURE__*/React.createElement("option", {
    value: "Inter"
  }, "Inter"), /*#__PURE__*/React.createElement("option", {
    value: "Arial"
  }, "Arial"), /*#__PURE__*/React.createElement("option", {
    value: "Helvetica"
  }, "Helvetica"), /*#__PURE__*/React.createElement("option", {
    value: "Times New Roman"
  }, "Times New Roman"), /*#__PURE__*/React.createElement("option", {
    value: "Courier New"
  }, "Courier New"), /*#__PURE__*/React.createElement("option", {
    value: "Georgia"
  }, "Georgia"), /*#__PURE__*/React.createElement("option", {
    value: "Verdana"
  }, "Verdana"), /*#__PURE__*/React.createElement("option", {
    value: "Roboto"
  }, "Roboto"), /*#__PURE__*/React.createElement("option", {
    value: "Open Sans"
  }, "Open Sans"))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Taille:"), /*#__PURE__*/React.createElement("div", {
    className: "slider-container"
  }, /*#__PURE__*/React.createElement("input", {
    type: "range",
    min: "8",
    max: "72",
    value: (_properties$fontSize = properties.fontSize) !== null && _properties$fontSize !== void 0 ? _properties$fontSize : 14,
    onChange: function onChange(e) {
      return onPropertyChange(elementId, 'fontSize', safeParseInt(e.target.value, 14));
    },
    className: "slider"
  }), /*#__PURE__*/React.createElement("span", {
    className: "slider-value"
  }, (_properties$fontSize2 = properties.fontSize) !== null && _properties$fontSize2 !== void 0 ? _properties$fontSize2 : 14, "px"))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Interligne:"), /*#__PURE__*/React.createElement("div", {
    className: "slider-container"
  }, /*#__PURE__*/React.createElement("input", {
    type: "range",
    min: "0.8",
    max: "3",
    step: "0.1",
    value: (_properties$lineHeigh = properties.lineHeight) !== null && _properties$lineHeigh !== void 0 ? _properties$lineHeigh : 1.2,
    onChange: function onChange(e) {
      return onPropertyChange(elementId, 'lineHeight', safeParseFloat(e.target.value, 1.2));
    },
    className: "slider"
  }), /*#__PURE__*/React.createElement("span", {
    className: "slider-value"
  }, (_properties$lineHeigh2 = properties.lineHeight) !== null && _properties$lineHeigh2 !== void 0 ? _properties$lineHeigh2 : 1.2))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Espacement lettres:"), /*#__PURE__*/React.createElement("div", {
    className: "slider-container"
  }, /*#__PURE__*/React.createElement("input", {
    type: "range",
    min: "-2",
    max: "10",
    step: "0.1",
    value: (_properties$letterSpa = properties.letterSpacing) !== null && _properties$letterSpa !== void 0 ? _properties$letterSpa : 0,
    onChange: function onChange(e) {
      return onPropertyChange(elementId, 'letterSpacing', safeParseFloat(e.target.value, 0));
    },
    className: "slider"
  }), /*#__PURE__*/React.createElement("span", {
    className: "slider-value"
  }, (_properties$letterSpa2 = properties.letterSpacing) !== null && _properties$letterSpa2 !== void 0 ? _properties$letterSpa2 : 0, "px"))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Opacit\xE9 texte:"), /*#__PURE__*/React.createElement("div", {
    className: "slider-container"
  }, /*#__PURE__*/React.createElement("input", {
    type: "range",
    min: "0",
    max: "1",
    step: "0.1",
    value: (_properties$opacity = properties.opacity) !== null && _properties$opacity !== void 0 ? _properties$opacity : 1,
    onChange: function onChange(e) {
      return onPropertyChange(elementId, 'opacity', safeParseFloat(e.target.value, 1));
    },
    className: "slider"
  }), /*#__PURE__*/React.createElement("span", {
    className: "slider-value"
  }, Math.round(((_properties$opacity2 = properties.opacity) !== null && _properties$opacity2 !== void 0 ? _properties$opacity2 : 1) * 100), "%"))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Ombre texte:"), /*#__PURE__*/React.createElement("div", {
    className: "slider-container"
  }, /*#__PURE__*/React.createElement("input", {
    type: "range",
    min: "0",
    max: "5",
    step: "0.1",
    value: (_properties$textShado = properties.textShadowBlur) !== null && _properties$textShado !== void 0 ? _properties$textShado : 0,
    onChange: function onChange(e) {
      return onPropertyChange(elementId, 'textShadowBlur', safeParseFloat(e.target.value, 0));
    },
    className: "slider"
  }), /*#__PURE__*/React.createElement("span", {
    className: "slider-value"
  }, (_properties$textShado2 = properties.textShadowBlur) !== null && _properties$textShado2 !== void 0 ? _properties$textShado2 : 0, "px"))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Style du texte:"), /*#__PURE__*/React.createElement("div", {
    className: "style-buttons-grid"
  }, /*#__PURE__*/React.createElement("button", {
    className: "style-btn ".concat(properties.fontWeight === 'bold' ? 'active' : ''),
    onClick: function onClick() {
      return onPropertyChange(elementId, 'fontWeight', properties.fontWeight === 'bold' ? 'normal' : 'bold');
    },
    title: "Gras"
  }, /*#__PURE__*/React.createElement("strong", null, "B")), /*#__PURE__*/React.createElement("button", {
    className: "style-btn ".concat(properties.fontStyle === 'italic' ? 'active' : ''),
    onClick: function onClick() {
      return onPropertyChange(elementId, 'fontStyle', properties.fontStyle === 'italic' ? 'normal' : 'italic');
    },
    title: "Italique"
  }, /*#__PURE__*/React.createElement("em", null, "I")), /*#__PURE__*/React.createElement("button", {
    className: "style-btn ".concat((properties.textDecoration || '').includes('underline') ? 'active' : ''),
    onClick: function onClick() {
      var currentDecorations = properties.textDecoration ? properties.textDecoration.split(' ') : [];
      var hasUnderline = currentDecorations.includes('underline');
      var newDecorations = hasUnderline ? currentDecorations.filter(function (d) {
        return d !== 'underline';
      }) : [].concat(PropertiesPanel_toConsumableArray(currentDecorations), ['underline']);
      onPropertyChange(elementId, 'textDecoration', newDecorations.join(' ') || 'none');
    },
    title: "Soulign\xE9"
  }, /*#__PURE__*/React.createElement("u", null, "U")), /*#__PURE__*/React.createElement("button", {
    className: "style-btn ".concat((properties.textDecoration || '').includes('line-through') ? 'active' : ''),
    onClick: function onClick() {
      var currentDecorations = properties.textDecoration ? properties.textDecoration.split(' ') : [];
      var hasLineThrough = currentDecorations.includes('line-through');
      var newDecorations = hasLineThrough ? currentDecorations.filter(function (d) {
        return d !== 'line-through';
      }) : [].concat(PropertiesPanel_toConsumableArray(currentDecorations), ['line-through']);
      onPropertyChange(elementId, 'textDecoration', newDecorations.join(' ') || 'none');
    },
    title: "Barr\xE9"
  }, /*#__PURE__*/React.createElement("s", null, "S")))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Alignement:"), /*#__PURE__*/React.createElement("div", {
    className: "alignment-buttons"
  }, [{
    value: 'left',
    icon: '⬅️',
    label: 'Gauche'
  }, {
    value: 'center',
    icon: '⬌',
    label: 'Centre'
  }, {
    value: 'right',
    icon: '➡️',
    label: 'Droite'
  }, {
    value: 'justify',
    icon: '⬌⬅️',
    label: 'Justifié'
  }].map(function (_ref3) {
    var value = _ref3.value,
      icon = _ref3.icon,
      label = _ref3.label;
    return /*#__PURE__*/React.createElement("button", {
      key: value,
      className: "align-btn ".concat(properties.textAlign === value ? 'active' : ''),
      onClick: function onClick() {
        return onPropertyChange(elementId, 'textAlign', value);
      },
      title: label
    }, icon);
  }))));
};

// Fonctions helper pour rendre chaque section de propriétés dans l'ordre intelligent
var renderColorsSection = function renderColorsSection(selectedElement, localProperties, handlePropertyChange, isBackgroundEnabled, activeTab) {
  var _localProperties$back, _localProperties$back2;
  // Vérifier si la section colors doit être affichée pour ce type d'élément
  if (!shouldShowSection('colors', selectedElement.type)) return null;
  return /*#__PURE__*/React.createElement("div", {
    key: "colors",
    className: "properties-group"
  }, /*#__PURE__*/React.createElement("h4", null, "\uD83C\uDFA8 Couleurs & Apparence"), selectedElement.type !== 'logo' && selectedElement.type !== 'company_logo' && /*#__PURE__*/React.createElement(ColorPicker, {
    label: "Texte",
    value: localProperties.color,
    onChange: function onChange(value) {
      handlePropertyChange(selectedElement.id, 'color', value);
    },
    presets: ['#1e293b', '#334155', '#475569', '#64748b', '#94a3b8', '#cbd5e1', '#000000'],
    defaultColor: "#333333"
  }), /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("span", null, "Fond activ\xE9:"), /*#__PURE__*/React.createElement("label", {
    className: "toggle"
  }, /*#__PURE__*/React.createElement("input", {
    type: "checkbox",
    checked: isBackgroundEnabled,
    disabled: false,
    onChange: function onChange(e) {
      if (e.target.checked) {
        handlePropertyChange(selectedElement.id, 'backgroundColor', '#ffffff');
      } else {
        handlePropertyChange(selectedElement.id, 'backgroundColor', 'transparent');
      }
    }
  }), /*#__PURE__*/React.createElement("span", {
    className: "toggle-slider"
  }))), /*#__PURE__*/React.createElement("div", {
    style: {
      display: isBackgroundEnabled ? 'block' : 'none',
      transition: 'opacity 0.3s ease'
    }
  }, /*#__PURE__*/React.createElement(ColorPicker, {
    label: "Fond",
    value: localProperties.backgroundColor === 'transparent' ? '#ffffff' : localProperties.backgroundColor,
    onChange: function onChange(value) {
      handlePropertyChange(selectedElement.id, 'backgroundColor', value);
    },
    presets: ['transparent', '#ffffff', '#f8fafc', '#f1f5f9', '#e2e8f0', '#cbd5e1', '#94a3b8']
  }), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Opacit\xE9 fond:"), /*#__PURE__*/React.createElement("div", {
    className: "slider-container"
  }, /*#__PURE__*/React.createElement("input", {
    type: "range",
    min: "0",
    max: "1",
    step: "0.1",
    value: (_localProperties$back = localProperties.backgroundOpacity) !== null && _localProperties$back !== void 0 ? _localProperties$back : 1,
    onChange: function onChange(e) {
      return handlePropertyChange(selectedElement.id, 'backgroundOpacity', safeParseFloat(e.target.value, 1));
    },
    className: "slider"
  }), /*#__PURE__*/React.createElement("span", {
    className: "slider-value"
  }, Math.round(((_localProperties$back2 = localProperties.backgroundOpacity) !== null && _localProperties$back2 !== void 0 ? _localProperties$back2 : 1) * 100), "%"))))));
};
var renderFontSection = function renderFontSection(selectedElement, localProperties, handlePropertyChange) {
  return /*#__PURE__*/React.createElement(FontControls, {
    key: "font",
    elementId: selectedElement.id,
    properties: localProperties,
    onPropertyChange: handlePropertyChange
  });
};

// Section Typographie - seulement si autorisée
var renderTypographySection = function renderTypographySection(selectedElement, localProperties, handlePropertyChange, activeTab) {
  // Vérifier si la section typography doit être affichée pour ce type d'élément
  if (!shouldShowSection('typography', selectedElement.type)) return null;
  return /*#__PURE__*/React.createElement("div", {
    key: "typography",
    className: "properties-group"
  }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDCDD Typographie"), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Police:"), /*#__PURE__*/React.createElement("select", {
    value: localProperties.fontFamily || 'Arial',
    onChange: function onChange(e) {
      return handlePropertyChange(selectedElement.id, 'fontFamily', e.target.value);
    },
    className: "property-select"
  }, /*#__PURE__*/React.createElement("option", {
    value: "Arial"
  }, "Arial"), /*#__PURE__*/React.createElement("option", {
    value: "Helvetica"
  }, "Helvetica"), /*#__PURE__*/React.createElement("option", {
    value: "Times New Roman"
  }, "Times New Roman"), /*#__PURE__*/React.createElement("option", {
    value: "Courier New"
  }, "Courier New"), /*#__PURE__*/React.createElement("option", {
    value: "Georgia"
  }, "Georgia"), /*#__PURE__*/React.createElement("option", {
    value: "Verdana"
  }, "Verdana"), /*#__PURE__*/React.createElement("option", {
    value: "Trebuchet MS"
  }, "Trebuchet MS"), /*#__PURE__*/React.createElement("option", {
    value: "Comic Sans MS"
  }, "Comic Sans MS"), /*#__PURE__*/React.createElement("option", {
    value: "Impact"
  }, "Impact"), /*#__PURE__*/React.createElement("option", {
    value: "Lucida Console"
  }, "Lucida Console"))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Taille:"), /*#__PURE__*/React.createElement("div", {
    className: "slider-container"
  }, /*#__PURE__*/React.createElement("input", {
    type: "range",
    min: "8",
    max: "72",
    step: "1",
    value: localProperties.fontSize || 12,
    onChange: function onChange(e) {
      return handlePropertyChange(selectedElement.id, 'fontSize', safeParseInt(e.target.value, 12));
    },
    className: "slider"
  }), /*#__PURE__*/React.createElement("span", {
    className: "slider-value"
  }, localProperties.fontSize || 12, "px"))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "\xC9paisseur:"), /*#__PURE__*/React.createElement("select", {
    value: localProperties.fontWeight || 'normal',
    onChange: function onChange(e) {
      return handlePropertyChange(selectedElement.id, 'fontWeight', e.target.value);
    },
    className: "property-select"
  }, /*#__PURE__*/React.createElement("option", {
    value: "normal"
  }, "Normal"), /*#__PURE__*/React.createElement("option", {
    value: "bold"
  }, "Gras"), /*#__PURE__*/React.createElement("option", {
    value: "lighter"
  }, "Fin"), /*#__PURE__*/React.createElement("option", {
    value: "100"
  }, "100"), /*#__PURE__*/React.createElement("option", {
    value: "200"
  }, "200"), /*#__PURE__*/React.createElement("option", {
    value: "300"
  }, "300"), /*#__PURE__*/React.createElement("option", {
    value: "400"
  }, "400"), /*#__PURE__*/React.createElement("option", {
    value: "500"
  }, "500"), /*#__PURE__*/React.createElement("option", {
    value: "600"
  }, "600"), /*#__PURE__*/React.createElement("option", {
    value: "700"
  }, "700"), /*#__PURE__*/React.createElement("option", {
    value: "800"
  }, "800"), /*#__PURE__*/React.createElement("option", {
    value: "900"
  }, "900"))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Style:"), /*#__PURE__*/React.createElement("select", {
    value: localProperties.fontStyle || 'normal',
    onChange: function onChange(e) {
      return handlePropertyChange(selectedElement.id, 'fontStyle', e.target.value);
    },
    className: "property-select"
  }, /*#__PURE__*/React.createElement("option", {
    value: "normal"
  }, "Normal"), /*#__PURE__*/React.createElement("option", {
    value: "italic"
  }, "Italique"), /*#__PURE__*/React.createElement("option", {
    value: "oblique"
  }, "Oblique"))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "D\xE9coration:"), /*#__PURE__*/React.createElement("select", {
    value: localProperties.textDecoration || 'none',
    onChange: function onChange(e) {
      return handlePropertyChange(selectedElement.id, 'textDecoration', e.target.value);
    },
    className: "property-select"
  }, /*#__PURE__*/React.createElement("option", {
    value: "none"
  }, "Aucune"), /*#__PURE__*/React.createElement("option", {
    value: "underline"
  }, "Soulign\xE9"), /*#__PURE__*/React.createElement("option", {
    value: "overline"
  }, "Surlign\xE9"), /*#__PURE__*/React.createElement("option", {
    value: "line-through"
  }, "Barr\xE9"))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Alignement:"), /*#__PURE__*/React.createElement("div", {
    className: "alignment-buttons"
  }, /*#__PURE__*/React.createElement("button", {
    className: "alignment-btn ".concat(localProperties.textAlign === 'left' ? 'active' : ''),
    onClick: function onClick() {
      return handlePropertyChange(selectedElement.id, 'textAlign', 'left');
    },
    title: "Aligner \xE0 gauche"
  }, "\u2B05\uFE0F"), /*#__PURE__*/React.createElement("button", {
    className: "alignment-btn ".concat(localProperties.textAlign === 'center' ? 'active' : ''),
    onClick: function onClick() {
      return handlePropertyChange(selectedElement.id, 'textAlign', 'center');
    },
    title: "Centrer"
  }, "\u2B0C"), /*#__PURE__*/React.createElement("button", {
    className: "alignment-btn ".concat(localProperties.textAlign === 'right' ? 'active' : ''),
    onClick: function onClick() {
      return handlePropertyChange(selectedElement.id, 'textAlign', 'right');
    },
    title: "Aligner \xE0 droite"
  }, "\u27A1\uFE0F"), /*#__PURE__*/React.createElement("button", {
    className: "alignment-btn ".concat(localProperties.textAlign === 'justify' ? 'active' : ''),
    onClick: function onClick() {
      return handlePropertyChange(selectedElement.id, 'textAlign', 'justify');
    },
    title: "Justifier"
  }, "\u2B0C\u2B05\uFE0F"))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Casse:"), /*#__PURE__*/React.createElement("select", {
    value: localProperties.textTransform || 'none',
    onChange: function onChange(e) {
      return handlePropertyChange(selectedElement.id, 'textTransform', e.target.value);
    },
    className: "property-select"
  }, /*#__PURE__*/React.createElement("option", {
    value: "none"
  }, "Aucune"), /*#__PURE__*/React.createElement("option", {
    value: "uppercase"
  }, "Majuscules"), /*#__PURE__*/React.createElement("option", {
    value: "lowercase"
  }, "Minuscules"), /*#__PURE__*/React.createElement("option", {
    value: "capitalize"
  }, "Premi\xE8re lettre"))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Interligne:"), /*#__PURE__*/React.createElement("div", {
    className: "slider-container"
  }, /*#__PURE__*/React.createElement("input", {
    type: "range",
    min: "0.8",
    max: "3",
    step: "0.1",
    value: localProperties.lineHeight || 1.2,
    onChange: function onChange(e) {
      return handlePropertyChange(selectedElement.id, 'lineHeight', safeParseFloat(e.target.value, 1.2));
    },
    className: "slider"
  }), /*#__PURE__*/React.createElement("span", {
    className: "slider-value"
  }, localProperties.lineHeight || 1.2))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Espacement:"), /*#__PURE__*/React.createElement("div", {
    className: "slider-container"
  }, /*#__PURE__*/React.createElement("input", {
    type: "range",
    min: "-2",
    max: "10",
    step: "0.5",
    value: localProperties.letterSpacing || 0,
    onChange: function onChange(e) {
      return handlePropertyChange(selectedElement.id, 'letterSpacing', safeParseFloat(e.target.value, 0));
    },
    className: "slider"
  }), /*#__PURE__*/React.createElement("span", {
    className: "slider-value"
  }, localProperties.letterSpacing || 0, "px"))));
};
var renderBordersSection = function renderBordersSection(selectedElement, localProperties, handlePropertyChange, isBorderEnabled, setIsBorderEnabled, setPreviousBorderWidth, setPreviousBorderColor, previousBorderWidth, previousBorderColor, activeTab) {
  var _localProperties$bord, _localProperties$bord2, _localProperties$bord3, _localProperties$bord4;
  // Les bordures sont disponibles pour tous les éléments
  if (!isBorderEnabled && localProperties.borderWidth <= 0) return null;
  return /*#__PURE__*/React.createElement("div", {
    key: "borders",
    className: "properties-group"
  }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDD32 Bordures & Coins Arrondis"), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("span", null, "Bordures activ\xE9es:"), /*#__PURE__*/React.createElement("label", {
    className: "toggle"
  }, /*#__PURE__*/React.createElement("input", {
    type: "checkbox",
    checked: isBorderEnabled,
    onChange: function onChange(e) {
      if (e.target.checked) {
        var widthToSet = previousBorderWidth || 1;
        var colorToSet = previousBorderColor || '#000000';
        handlePropertyChange(selectedElement.id, 'border', true);
        handlePropertyChange(selectedElement.id, 'borderWidth', widthToSet);
        handlePropertyChange(selectedElement.id, 'borderColor', colorToSet);
        setIsBorderEnabled(true);
      } else {
        setPreviousBorderWidth(localProperties.borderWidth || 1);
        setPreviousBorderColor(localProperties.borderColor || '#000000');
        handlePropertyChange(selectedElement.id, 'border', false);
        handlePropertyChange(selectedElement.id, 'borderWidth', 0);
        setIsBorderEnabled(false);
      }
    }
  }), /*#__PURE__*/React.createElement("span", {
    className: "toggle-slider"
  }))), /*#__PURE__*/React.createElement("div", {
    style: {
      display: localProperties.borderWidth > 0 ? 'block' : 'none',
      transition: 'opacity 0.3s ease'
    }
  }, /*#__PURE__*/React.createElement(ColorPicker, {
    label: "Couleur bordure",
    value: localProperties.borderColor || '#000000',
    onChange: function onChange(value) {
      return handlePropertyChange(selectedElement.id, 'borderColor', value);
    },
    presets: ['#e2e8f0', '#cbd5e1', '#94a3b8', '#64748b', '#475569', '#334155', '#000000']
  }), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Style bordure:"), /*#__PURE__*/React.createElement("select", {
    value: localProperties.borderStyle || 'solid',
    onChange: function onChange(e) {
      return handlePropertyChange(selectedElement.id, 'borderStyle', e.target.value);
    },
    className: "styled-select"
  }, /*#__PURE__*/React.createElement("option", {
    value: "solid"
  }, "Continue"), /*#__PURE__*/React.createElement("option", {
    value: "dashed"
  }, "Tirets"), /*#__PURE__*/React.createElement("option", {
    value: "dotted"
  }, "Pointill\xE9s"), /*#__PURE__*/React.createElement("option", {
    value: "double"
  }, "Double"))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "\xC9paisseur bordure:"), /*#__PURE__*/React.createElement("div", {
    className: "slider-container"
  }, /*#__PURE__*/React.createElement("input", {
    type: "range",
    min: "0",
    max: "10",
    value: (_localProperties$bord = localProperties.borderWidth) !== null && _localProperties$bord !== void 0 ? _localProperties$bord : 1,
    onChange: function onChange(e) {
      return handlePropertyChange(selectedElement.id, 'borderWidth', safeParseInt(e.target.value, 1));
    },
    className: "slider"
  }), /*#__PURE__*/React.createElement("span", {
    className: "slider-value"
  }, (_localProperties$bord2 = localProperties.borderWidth) !== null && _localProperties$bord2 !== void 0 ? _localProperties$bord2 : 1, "px"))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Coins arrondis:"), /*#__PURE__*/React.createElement("div", {
    className: "slider-container"
  }, /*#__PURE__*/React.createElement("input", {
    type: "range",
    min: "0",
    max: "50",
    value: (_localProperties$bord3 = localProperties.borderRadius) !== null && _localProperties$bord3 !== void 0 ? _localProperties$bord3 : 4,
    onChange: function onChange(e) {
      return handlePropertyChange(selectedElement.id, 'borderRadius', safeParseInt(e.target.value, 0));
    },
    className: "slider"
  }), /*#__PURE__*/React.createElement("span", {
    className: "slider-value"
  }, (_localProperties$bord4 = localProperties.borderRadius) !== null && _localProperties$bord4 !== void 0 ? _localProperties$bord4 : 4, "px")))));
};
var renderEffectsSection = function renderEffectsSection(selectedElement, localProperties, handlePropertyChange, activeTab) {
  var _localProperties$boxS, _localProperties$boxS2, _localProperties$boxS3, _localProperties$boxS4;
  // Les effets sont disponibles pour tous les éléments
  return /*#__PURE__*/React.createElement("div", {
    key: "effects",
    className: "properties-group"
  }, /*#__PURE__*/React.createElement("h4", null, "\u2728 Effets"), /*#__PURE__*/React.createElement(ColorPicker, {
    label: "Ombre",
    value: localProperties.boxShadowColor || '#000000',
    onChange: function onChange(value) {
      return handlePropertyChange(selectedElement.id, 'boxShadowColor', value);
    },
    presets: ['#000000', '#ffffff', '#64748b', '#ef4444', '#3b82f6']
  }), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Flou ombre:"), /*#__PURE__*/React.createElement("div", {
    className: "slider-container"
  }, /*#__PURE__*/React.createElement("input", {
    type: "range",
    min: "0",
    max: "20",
    value: (_localProperties$boxS = localProperties.boxShadowBlur) !== null && _localProperties$boxS !== void 0 ? _localProperties$boxS : 0,
    onChange: function onChange(e) {
      return handlePropertyChange(selectedElement.id, 'boxShadowBlur', safeParseInt(e.target.value, 0));
    },
    className: "slider"
  }), /*#__PURE__*/React.createElement("span", {
    className: "slider-value"
  }, (_localProperties$boxS2 = localProperties.boxShadowBlur) !== null && _localProperties$boxS2 !== void 0 ? _localProperties$boxS2 : 0, "px"))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "D\xE9calage ombre:"), /*#__PURE__*/React.createElement("div", {
    className: "slider-container"
  }, /*#__PURE__*/React.createElement("input", {
    type: "range",
    min: "0",
    max: "10",
    value: (_localProperties$boxS3 = localProperties.boxShadowSpread) !== null && _localProperties$boxS3 !== void 0 ? _localProperties$boxS3 : 0,
    onChange: function onChange(e) {
      return handlePropertyChange(selectedElement.id, 'boxShadowSpread', safeParseInt(e.target.value, 0));
    },
    className: "slider"
  }), /*#__PURE__*/React.createElement("span", {
    className: "slider-value"
  }, (_localProperties$boxS4 = localProperties.boxShadowSpread) !== null && _localProperties$boxS4 !== void 0 ? _localProperties$boxS4 : 0, "px"))));
};
var PropertiesPanel = /*#__PURE__*/(0,react.memo)(function (_ref4) {
  var selectedElements = _ref4.selectedElements,
    elements = _ref4.elements,
    onPropertyChange = _ref4.onPropertyChange,
    onBatchUpdate = _ref4.onBatchUpdate;
  // États pour mémoriser les valeurs précédentes
  var _useState = (0,react.useState)('#ffffff'),
    _useState2 = PropertiesPanel_slicedToArray(_useState, 2),
    previousBackgroundColor = _useState2[0],
    setPreviousBackgroundColor = _useState2[1];
  var _useState3 = (0,react.useState)(0),
    _useState4 = PropertiesPanel_slicedToArray(_useState3, 2),
    previousBorderWidth = _useState4[0],
    setPreviousBorderWidth = _useState4[1];
  var _useState5 = (0,react.useState)('#000000'),
    _useState6 = PropertiesPanel_slicedToArray(_useState5, 2),
    previousBorderColor = _useState6[0],
    setPreviousBorderColor = _useState6[1];
  var _useState7 = (0,react.useState)(false),
    _useState8 = PropertiesPanel_slicedToArray(_useState7, 2),
    isBackgroundEnabled = _useState8[0],
    setIsBackgroundEnabled = _useState8[1];
  var _useState9 = (0,react.useState)(false),
    _useState0 = PropertiesPanel_slicedToArray(_useState9, 2),
    isBorderEnabled = _useState0[0],
    setIsBorderEnabled = _useState0[1];

  // Utiliser les hooks de personnalisation et synchronisation
  var _useElementCustomizat = useElementCustomization(selectedElements, elements, onPropertyChange),
    localProperties = _useElementCustomizat.localProperties,
    activeTab = _useElementCustomizat.activeTab,
    setActiveTab = _useElementCustomizat.setActiveTab,
    customizationChange = _useElementCustomizat.handlePropertyChange;
  var _useElementSynchroniz = useElementSynchronization(elements, onPropertyChange, onBatchUpdate, true,
    // autoSave
    3000 // autoSaveDelay - increased to reduce AJAX calls
    ),
    syncImmediate = _useElementSynchroniz.syncImmediate,
    syncBatch = _useElementSynchroniz.syncBatch;

  // Obtenir l'élément sélectionné (mémorisé pour éviter les re-renders)
  var selectedElement = (0,react.useMemo)(function () {
    return selectedElements.length > 0 ? elements.find(function (el) {
      return el.id === selectedElements[0];
    }) : null;
  }, [selectedElements, elements]);

  // Mettre à jour les valeurs précédentes quand l'élément change
  (0,react.useEffect)(function () {
    if (selectedElement) {
      // Initialiser les valeurs précédentes avec les valeurs actuelles de l'élément
      setPreviousBackgroundColor(selectedElement.backgroundColor || '#ffffff');
      // Pour borderWidth, s'assurer qu'on a au moins 1 pour la restauration
      var initialBorderWidth = selectedElement.borderWidth && selectedElement.borderWidth > 0 ? selectedElement.borderWidth : 1;
      setPreviousBorderWidth(initialBorderWidth);
      setPreviousBorderColor(selectedElement.borderColor || '#000000');
    }
  }, [selectedElement]); // Ne dépendre que de selectedElement pour éviter les boucles

  // Synchroniser l'état du toggle fond
  (0,react.useEffect)(function () {
    var shouldBeEnabled = !!localProperties.backgroundColor && localProperties.backgroundColor !== 'transparent';
    setIsBackgroundEnabled(shouldBeEnabled);
  }, [localProperties.backgroundColor]);

  // Synchroniser l'état du toggle bordures
  (0,react.useEffect)(function () {
    setIsBorderEnabled(!!localProperties.border && (localProperties.borderWidth || 0) > 0);
  }, [localProperties.border, localProperties.borderWidth]);

  // Gestionnaire unifié de changement de propriété
  var handlePropertyChange = (0,react.useCallback)(function (elementId, property, value) {
    // Empêcher la couleur du texte d'être transparente
    if (property === 'color' && value === 'transparent') {
      value = '#333333';
    }

    // Validation via le service (sauf pour les propriétés boolean qui sont toujours valides)
    var isBooleanProperty = typeof value === 'boolean' || property.startsWith('columns.');
    var validatedValue = value; // Valeur par défaut

    if (!isBooleanProperty) {
      try {
        validatedValue = elementCustomizationService.validateProperty(property, value);
        if (validatedValue === undefined || validatedValue === null) {
          console.warn("Propri\xE9t\xE9 invalide: ".concat(property, " = ").concat(value));
          return;
        }
      } catch (error) {
        console.warn("Erreur de validation pour ".concat(property, ":"), error);
        return;
      }
    }

    // Utiliser le hook de personnalisation pour la gestion locale
    customizationChange(elementId, property, validatedValue);

    // Synchronisation immédiate pour les changements critiques et de style
    if (['x', 'y', 'width', 'height',
    // Position et dimensions
    'color', 'fontSize', 'fontFamily', 'fontWeight', 'fontStyle',
    // Texte et typographie
    'textAlign', 'lineHeight', 'letterSpacing', 'textDecoration',
    // Mise en forme texte
    'backgroundColor', 'backgroundOpacity',
    // Fond
    'borderColor', 'borderWidth', 'borderStyle', 'borderRadius',
    // Bordures
    'boxShadowColor', 'boxShadowBlur', 'boxShadowSpread',
    // Ombres
    'opacity', 'textShadowBlur' // Transparence et effets
    ].includes(property)) {
      syncImmediate(elementId, property, validatedValue);
    }
  }, [customizationChange, syncImmediate]);

  // Gestionnaire pour le toggle "Aucun fond"
  var handleNoBackgroundToggle = (0,react.useCallback)(function (elementId, checked) {
    // Vérifier si la propriété backgroundColor est autorisée pour ce type d'élément
    var isBackgroundAllowed = selectedElement !== null && selectedElement !== void 0 && selectedElement.type ? isPropertyAllowedForElement(selectedElement.type, activeTab, 'backgroundColor') : true;
    if (!isBackgroundAllowed) {
      console.warn('Fond non contrôlable pour ce type d\'élément');
      return;
    }
    if (checked) {
      // Sauvegarder la couleur actuelle avant de la désactiver
      if (selectedElement !== null && selectedElement !== void 0 && selectedElement.backgroundColor && selectedElement.backgroundColor !== 'transparent') {
        setPreviousBackgroundColor(selectedElement.backgroundColor);
      } else if (!previousBackgroundColor) {
        // Si pas de couleur précédente sauvegardée, utiliser la valeur par défaut
        setPreviousBackgroundColor('#ffffff');
      }
      handlePropertyChange(elementId, 'backgroundColor', 'transparent');
    } else {
      // Restaurer la couleur précédente (avec fallback)
      var colorToRestore = previousBackgroundColor || '#ffffff';
      handlePropertyChange(elementId, 'backgroundColor', colorToRestore);
    }
  }, [selectedElement === null || selectedElement === void 0 ? void 0 : selectedElement.backgroundColor, previousBackgroundColor, handlePropertyChange, selectedElement === null || selectedElement === void 0 ? void 0 : selectedElement.type]);

  // Gestionnaire pour le toggle "Aucune bordure"
  var handleNoBorderToggle = (0,react.useCallback)(function (elementId, checked) {
    if (checked) {
      // Sauvegarder l'épaisseur actuelle avant de la désactiver
      if (selectedElement !== null && selectedElement !== void 0 && selectedElement.borderWidth && selectedElement.borderWidth > 0) {
        setPreviousBorderWidth(selectedElement.borderWidth);
      } else {
        // Si pas de bordure ou bordure = 0, sauvegarder 2 comme valeur par défaut (plus visible)
        setPreviousBorderWidth(2);
      }
      handlePropertyChange(elementId, 'borderWidth', 0);
    } else {
      // Restaurer l'épaisseur précédente, au minimum 2
      var widthToRestore = Math.max(previousBorderWidth || 2, 2);
      handlePropertyChange(elementId, 'borderWidth', widthToRestore);
    }
  }, [selectedElement === null || selectedElement === void 0 ? void 0 : selectedElement.borderWidth, previousBorderWidth, handlePropertyChange]);

  // Rendu des onglets
  var renderTabs = (0,react.useCallback)(function () {
    return /*#__PURE__*/React.createElement("div", {
      className: "properties-tabs"
    }, /*#__PURE__*/React.createElement("button", {
      className: "tab-btn ".concat(activeTab === 'appearance' ? 'active' : ''),
      onClick: function onClick() {
        return setActiveTab('appearance');
      }
    }, "\uD83C\uDFA8 Apparence"), /*#__PURE__*/React.createElement("button", {
      className: "tab-btn ".concat(activeTab === 'layout' ? 'active' : ''),
      onClick: function onClick() {
        return setActiveTab('layout');
      }
    }, "\uD83D\uDCD0 Mise en page"), /*#__PURE__*/React.createElement("button", {
      className: "tab-btn ".concat(activeTab === 'content' ? 'active' : ''),
      onClick: function onClick() {
        return setActiveTab('content');
      }
    }, "\uD83D\uDCDD Contenu"), /*#__PURE__*/React.createElement("button", {
      className: "tab-btn ".concat(activeTab === 'effects' ? 'active' : ''),
      onClick: function onClick() {
        return setActiveTab('effects');
      }
    }, "\u2728 Effets"));
  }, [activeTab]);

  // Rendu du contenu selon l'onglet actif
  var renderTabContent = (0,react.useCallback)(function () {
    var _localProperties$show, _localProperties$show2, _localProperties$show3, _localProperties$show4, _localProperties$show5, _localProperties$show6, _localProperties$show7;
    if (!selectedElement) {
      return /*#__PURE__*/React.createElement("div", {
        className: "no-selection"
      }, /*#__PURE__*/React.createElement("div", {
        className: "no-selection-icon"
      }, "\uD83D\uDC46"), /*#__PURE__*/React.createElement("p", null, "S\xE9lectionnez un \xE9l\xE9ment pour modifier ses propri\xE9t\xE9s"), selectedElements.length > 1 && /*#__PURE__*/React.createElement("p", {
        className: "selection-info"
      }, selectedElements.length, " \xE9l\xE9ments s\xE9lectionn\xE9s"));
    }

    // Obtenir l'ordre intelligent des propriétés pour ce type d'élément
    var smartOrder = getSmartPropertyOrder(selectedElement.type, activeTab);

    // Obtenir le profil de propriétés pour ce type d'élément
    var elementProfile = ELEMENT_PROPERTY_PROFILES[selectedElement.type] || ELEMENT_PROPERTY_PROFILES['default'];
    var tabProfile = elementProfile[activeTab] || {
      sections: [],
      properties: {}
    };
    var allowedControls = tabProfile.sections || [];
    switch (activeTab) {
      case 'appearance':
        return /*#__PURE__*/React.createElement("div", {
          className: "tab-content"
        }, smartOrder.map(function (section) {
          switch (section) {
            case 'colors':
              return renderColorsSection(selectedElement, localProperties, handlePropertyChange, isBackgroundEnabled, activeTab);
            case 'typography':
              return renderTypographySection(selectedElement, localProperties, handlePropertyChange, activeTab);
            case 'borders':
              return allowedControls.includes('borders') ? renderBordersSection(selectedElement, localProperties, handlePropertyChange, isBorderEnabled, setIsBorderEnabled, setPreviousBorderWidth, setPreviousBorderColor, previousBorderWidth, previousBorderColor, activeTab) : null;
            case 'effects':
              return allowedControls.includes('effects') ? renderEffectsSection(selectedElement, localProperties, handlePropertyChange, activeTab) : null;
            default:
              return null;
          }
        }));
      case 'layout':
        return /*#__PURE__*/React.createElement("div", {
          className: "tab-content"
        }, allowedControls.includes('position') && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDCCD Position Pr\xE9cise"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "X:"), /*#__PURE__*/React.createElement("div", {
          className: "input-with-unit"
        }, /*#__PURE__*/React.createElement("input", {
          type: "number",
          value: Math.round(localProperties.x || 0),
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'x', safeParseInt(e.target.value, 0));
          },
          step: "1"
        }), /*#__PURE__*/React.createElement("span", {
          className: "unit"
        }, "mm"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Y:"), /*#__PURE__*/React.createElement("div", {
          className: "input-with-unit"
        }, /*#__PURE__*/React.createElement("input", {
          type: "number",
          value: Math.round(localProperties.y || 0),
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'y', safeParseInt(e.target.value, 0));
          },
          step: "1"
        }), /*#__PURE__*/React.createElement("span", {
          className: "unit"
        }, "mm")))), allowedControls.includes('dimensions') && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDCCF Dimensions"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Largeur:"), /*#__PURE__*/React.createElement("div", {
          className: "input-with-unit"
        }, /*#__PURE__*/React.createElement("input", {
          type: "number",
          value: Math.round(localProperties.width || 100),
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'width', safeParseInt(e.target.value, 100));
          },
          min: "1",
          step: "1"
        }), /*#__PURE__*/React.createElement("span", {
          className: "unit"
        }, "mm"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Hauteur:"), /*#__PURE__*/React.createElement("div", {
          className: "input-with-unit"
        }, /*#__PURE__*/React.createElement("input", {
          type: "number",
          value: Math.round(localProperties.height || 50),
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'height', safeParseInt(e.target.value, 50));
          },
          min: "1",
          step: "1"
        }), /*#__PURE__*/React.createElement("span", {
          className: "unit"
        }, "mm"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Ratio:"), /*#__PURE__*/React.createElement("div", {
          className: "ratio-buttons"
        }, /*#__PURE__*/React.createElement("button", {
          className: "ratio-btn",
          onClick: function onClick() {
            var newHeight = (localProperties.width || 100) * 0.75;
            handlePropertyChange(selectedElement.id, 'height', Math.round(newHeight));
          },
          title: "Format 4:3"
        }, "4:3"), /*#__PURE__*/React.createElement("button", {
          className: "ratio-btn",
          onClick: function onClick() {
            var newHeight = (localProperties.width || 100) * (297 / 210);
            handlePropertyChange(selectedElement.id, 'height', Math.round(newHeight));
          },
          title: "Format A4"
        }, "A4"), /*#__PURE__*/React.createElement("button", {
          className: "ratio-btn",
          onClick: function onClick() {
            var newHeight = localProperties.width || 100;
            handlePropertyChange(selectedElement.id, 'height', Math.round(newHeight));
          },
          title: "Carr\xE9"
        }, "1:1")))), allowedControls.includes('transform') && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDD04 Transformation"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Rotation:"), /*#__PURE__*/React.createElement("div", {
          className: "slider-container"
        }, /*#__PURE__*/React.createElement("input", {
          type: "range",
          min: "-180",
          max: "180",
          value: localProperties.rotation || 0,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'rotation', safeParseInt(e.target.value, 0));
          },
          onDoubleClick: function onDoubleClick() {
            return handlePropertyChange(selectedElement.id, 'rotation', 0);
          },
          className: "slider"
        }), /*#__PURE__*/React.createElement("span", {
          className: "slider-value"
        }, localProperties.rotation || 0, "\xB0"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Rotation rapide:"), /*#__PURE__*/React.createElement("div", {
          className: "rotation-buttons"
        }, /*#__PURE__*/React.createElement("button", {
          className: "rotation-btn",
          onClick: function onClick() {
            return handlePropertyChange(selectedElement.id, 'rotation', 0);
          },
          title: "Rotation 0\xB0"
        }, "\u21BB 0\xB0"), /*#__PURE__*/React.createElement("button", {
          className: "rotation-btn",
          onClick: function onClick() {
            return handlePropertyChange(selectedElement.id, 'rotation', 90);
          },
          title: "Rotation 90\xB0"
        }, "\u21BB 90\xB0"), /*#__PURE__*/React.createElement("button", {
          className: "rotation-btn",
          onClick: function onClick() {
            return handlePropertyChange(selectedElement.id, 'rotation', 180);
          },
          title: "Rotation 180\xB0"
        }, "\u21BB 180\xB0"), /*#__PURE__*/React.createElement("button", {
          className: "rotation-btn",
          onClick: function onClick() {
            return handlePropertyChange(selectedElement.id, 'rotation', -90);
          },
          title: "Rotation -90\xB0"
        }, "\u21BA -90\xB0")))), allowedControls.includes('layers') && selectedElement.type !== 'product_table' && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDCDA Calques"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Profondeur (Z-index):"), /*#__PURE__*/React.createElement("input", {
          type: "number",
          value: localProperties.zIndex || 0,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'zIndex', safeParseInt(e.target.value, 0));
          },
          min: "0",
          max: "100",
          step: "1"
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Actions:"), /*#__PURE__*/React.createElement("div", {
          className: "layer-actions"
        }, /*#__PURE__*/React.createElement("button", {
          className: "layer-btn",
          onClick: function onClick() {
            return handlePropertyChange(selectedElement.id, 'zIndex', (localProperties.zIndex || 0) + 1);
          },
          title: "Mettre devant"
        }, "\u2B06\uFE0F Devant"), /*#__PURE__*/React.createElement("button", {
          className: "layer-btn",
          onClick: function onClick() {
            return handlePropertyChange(selectedElement.id, 'zIndex', Math.max(0, (localProperties.zIndex || 0) - 1));
          },
          title: "Mettre derri\xE8re"
        }, "\u2B07\uFE0F Derri\xE8re")))));
      case 'content':
        return /*#__PURE__*/React.createElement("div", {
          className: "tab-content"
        }, allowedControls.includes('text') && selectedElement.type === 'text' && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDCDD Contenu texte"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Texte:"), /*#__PURE__*/React.createElement("textarea", {
          value: localProperties.text || '',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'text', e.target.value);
          },
          rows: 4,
          placeholder: "Saisissez votre texte ici..."
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Variables dynamiques:"), /*#__PURE__*/React.createElement("div", {
          className: "variables-list"
        }, /*#__PURE__*/React.createElement("button", {
          className: "variable-btn",
          onClick: function onClick() {
            var currentText = localProperties.text || '';
            handlePropertyChange(selectedElement.id, 'text', currentText + '{{date}}');
          }
        }, "\uD83D\uDCC5 Date"), /*#__PURE__*/React.createElement("button", {
          className: "variable-btn",
          onClick: function onClick() {
            var currentText = localProperties.text || '';
            handlePropertyChange(selectedElement.id, 'text', currentText + '{{order_number}}');
          }
        }, "[Ord] N\xB0 commande"), /*#__PURE__*/React.createElement("button", {
          className: "variable-btn",
          onClick: function onClick() {
            var currentText = localProperties.text || '';
            handlePropertyChange(selectedElement.id, 'text', currentText + '{{customer_name}}');
          }
        }, "\uD83D\uDC64 Client"), /*#__PURE__*/React.createElement("button", {
          className: "variable-btn",
          onClick: function onClick() {
            var currentText = localProperties.text || '';
            handlePropertyChange(selectedElement.id, 'text', currentText + '{{total}}');
          }
        }, "\uD83D\uDCB0 Total")))), allowedControls.includes('variables') && (selectedElement.type === 'layout-header' || selectedElement.type === 'layout-footer' || selectedElement.type === 'layout-section') && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDD04 Variables dynamiques"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Variables disponibles:"), /*#__PURE__*/React.createElement("div", {
          className: "variables-list"
        }, /*#__PURE__*/React.createElement("button", {
          className: "variable-btn",
          onClick: function onClick() {
            var currentText = localProperties.text || '';
            handlePropertyChange(selectedElement.id, 'text', currentText + '{{date}}');
          }
        }, "\uD83D\uDCC5 Date"), /*#__PURE__*/React.createElement("button", {
          className: "variable-btn",
          onClick: function onClick() {
            var currentText = localProperties.text || '';
            handlePropertyChange(selectedElement.id, 'text', currentText + '{{order_number}}');
          }
        }, "[Ord] N\xB0 commande"), /*#__PURE__*/React.createElement("button", {
          className: "variable-btn",
          onClick: function onClick() {
            var currentText = localProperties.text || '';
            handlePropertyChange(selectedElement.id, 'text', currentText + '{{customer_name}}');
          }
        }, "\uD83D\uDC64 Client"), /*#__PURE__*/React.createElement("button", {
          className: "variable-btn",
          onClick: function onClick() {
            var currentText = localProperties.text || '';
            handlePropertyChange(selectedElement.id, 'text', currentText + '{{total}}');
          }
        }, "\uD83D\uDCB0 Total")))), allowedControls.includes('table') && selectedElement.type === 'product_table' && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDCCA Tableau produits"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Colonnes \xE0 afficher:"), /*#__PURE__*/React.createElement("div", {
          className: "checkbox-group"
        }, [{
          key: 'image',
          label: 'Image'
        }, {
          key: 'name',
          label: 'Nom'
        }, {
          key: 'sku',
          label: 'SKU'
        }, {
          key: 'quantity',
          label: 'Quantité'
        }, {
          key: 'price',
          label: 'Prix'
        }, {
          key: 'total',
          label: 'Total'
        }].map(function (_ref5) {
          var _localProperties$colu, _localProperties$colu2;
          var key = _ref5.key,
            label = _ref5.label;
          return /*#__PURE__*/React.createElement("label", {
            key: key,
            className: "checkbox-item"
          }, /*#__PURE__*/React.createElement("input", {
            type: "checkbox",
            checked: (_localProperties$colu = (_localProperties$colu2 = localProperties.columns) === null || _localProperties$colu2 === void 0 ? void 0 : _localProperties$colu2[key]) !== null && _localProperties$colu !== void 0 ? _localProperties$colu : true,
            onChange: function onChange(e) {
              handlePropertyChange(selectedElement.id, "columns.".concat(key), e.target.checked);
            }
          }), label);
        }))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Style du tableau:"), /*#__PURE__*/React.createElement("div", {
          className: "table-style-selector"
        }, [{
          value: 'default',
          label: 'Défaut',
          headerBg: '#f8fafc',
          headerBorder: '#e2e8f0',
          rowBorder: '#f1f5f9',
          altRowBg: '#fafbfc',
          borderWidth: 1,
          textColor: '#334155'
        }, {
          value: 'classic',
          label: 'Classique',
          headerBg: '#1e293b',
          headerBorder: '#334155',
          rowBorder: '#334155',
          altRowBg: '#ffffff',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'striped',
          label: 'Alterné',
          headerBg: '#3b82f6',
          headerBorder: '#2563eb',
          rowBorder: '#e2e8f0',
          altRowBg: '#f8fafc',
          borderWidth: 1,
          textColor: '#ffffff'
        }, {
          value: 'bordered',
          label: 'Encadré',
          headerBg: '#ffffff',
          headerBorder: '#374151',
          rowBorder: '#d1d5db',
          altRowBg: '#ffffff',
          borderWidth: 2,
          textColor: '#111827'
        }, {
          value: 'minimal',
          label: 'Minimal',
          headerBg: '#ffffff',
          headerBorder: '#f3f4f6',
          rowBorder: '#f9fafb',
          altRowBg: '#ffffff',
          borderWidth: 0.5,
          textColor: '#6b7280'
        }, {
          value: 'modern',
          label: 'Moderne',
          gradient: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
          headerBorder: '#5b21b6',
          rowBorder: '#e9d5ff',
          altRowBg: '#faf5ff',
          borderWidth: 1,
          textColor: '#ffffff'
        }, {
          value: 'blue_ocean',
          label: 'Océan Bleu',
          gradient: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
          headerBorder: '#1e40af',
          rowBorder: '#dbeafe',
          altRowBg: '#eff6ff',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'emerald_forest',
          label: 'Forêt Émeraude',
          gradient: 'linear-gradient(135deg, #064e3b 0%, #10b981 100%)',
          headerBorder: '#065f46',
          rowBorder: '#d1fae5',
          altRowBg: '#a7f3d0',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'sunset_orange',
          label: 'Coucher Orange',
          gradient: 'linear-gradient(135deg, #9a3412 0%, #f97316 100%)',
          headerBorder: '#c2410c',
          rowBorder: '#fed7aa',
          altRowBg: '#fff7ed',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'royal_purple',
          label: 'Royal Violet',
          gradient: 'linear-gradient(135deg, #581c87 0%, #a855f7 100%)',
          headerBorder: '#7c3aed',
          rowBorder: '#e9d5ff',
          altRowBg: '#faf5ff',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'rose_pink',
          label: 'Rose Bonbon',
          gradient: 'linear-gradient(135deg, #be185d 0%, #f472b6 100%)',
          headerBorder: '#db2777',
          rowBorder: '#fce7f3',
          altRowBg: '#fdf2f8',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'teal_aqua',
          label: 'Aigue-marine',
          gradient: 'linear-gradient(135deg, #0f766e 0%, #14b8a6 100%)',
          headerBorder: '#0d9488',
          rowBorder: '#ccfbf1',
          altRowBg: '#f0fdfa',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'crimson_red',
          label: 'Rouge Cramoisi',
          gradient: 'linear-gradient(135deg, #991b1b 0%, #ef4444 100%)',
          headerBorder: '#dc2626',
          rowBorder: '#fecaca',
          altRowBg: '#fef2f2',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'amber_gold',
          label: 'Or Ambré',
          gradient: 'linear-gradient(135deg, #92400e 0%, #f59e0b 100%)',
          headerBorder: '#d97706',
          rowBorder: '#fef3c7',
          altRowBg: '#fffbeb',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'indigo_night',
          label: 'Nuit Indigo',
          gradient: 'linear-gradient(135deg, #312e81 0%, #6366f1 100%)',
          headerBorder: '#4338ca',
          rowBorder: '#e0e7ff',
          altRowBg: '#eef2ff',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'slate_gray',
          label: 'Ardoise',
          headerBg: '#374151',
          headerBorder: '#4b5563',
          rowBorder: '#f3f4f6',
          altRowBg: '#f9fafb',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'coral',
          label: 'Corail',
          headerBg: '#f87171',
          headerBorder: '#fca5a5',
          rowBorder: '#fef2f2',
          altRowBg: '#fef2f2',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'teal',
          label: 'Sarcelle',
          headerBg: '#14b8a6',
          headerBorder: '#0d9488',
          rowBorder: '#ccfbf1',
          altRowBg: '#f0fdfa',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'indigo',
          label: 'Indigo',
          headerBg: '#6366f1',
          headerBorder: '#818cf8',
          rowBorder: '#e0e7ff',
          altRowBg: '#eef2ff',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'amber',
          label: 'Ambre',
          headerBg: '#f59e0b',
          headerBorder: '#fbbf24',
          rowBorder: '#fef3c7',
          altRowBg: '#fffbeb',
          borderWidth: 1.5,
          textColor: '#92400e'
        }].map(function (style) {
          return /*#__PURE__*/React.createElement("button", {
            key: style.value,
            type: "button",
            className: "table-style-option ".concat(localProperties.tableStyle === style.value ? 'active' : ''),
            onClick: function onClick() {
              return handlePropertyChange(selectedElement.id, 'tableStyle', style.value);
            },
            title: "".concat(style.label, " - Style ").concat(style.label.toLowerCase(), " avec d\xE9grad\xE9 moderne")
          }, /*#__PURE__*/React.createElement("div", {
            className: "table-sample",
            style: {
              maxHeight: '60px',
              overflow: 'hidden'
            }
          }, /*#__PURE__*/React.createElement("div", {
            className: "table-header",
            style: {
              background: style.gradient || style.headerBg,
              border: "".concat(style.borderWidth, "px solid ").concat(style.headerBorder),
              borderBottom: 'none',
              color: style.textColor
            }
          }, /*#__PURE__*/React.createElement("div", {
            className: "table-cell",
            style: {
              borderRight: "".concat(style.borderWidth, "px solid ").concat(style.headerBorder)
            }
          }, "Produit"), /*#__PURE__*/React.createElement("div", {
            className: "table-cell",
            style: {
              borderRight: "".concat(style.borderWidth, "px solid ").concat(style.headerBorder)
            }
          }, "Qt\xE9"), /*#__PURE__*/React.createElement("div", {
            className: "table-cell"
          }, "Prix")), /*#__PURE__*/React.createElement("div", {
            className: "table-row",
            style: {
              backgroundColor: style.altRowBg,
              border: "".concat(style.borderWidth, "px solid ").concat(style.rowBorder),
              borderTop: 'none',
              color: style.textColor
            }
          }, /*#__PURE__*/React.createElement("div", {
            className: "table-cell",
            style: {
              borderRight: "".concat(style.borderWidth, "px solid ").concat(style.rowBorder)
            }
          }, "Article 1"), /*#__PURE__*/React.createElement("div", {
            className: "table-cell",
            style: {
              borderRight: "".concat(style.borderWidth, "px solid ").concat(style.rowBorder)
            }
          }, "2"), /*#__PURE__*/React.createElement("div", {
            className: "table-cell"
          }, "15.99\u20AC")), /*#__PURE__*/React.createElement("div", {
            className: "table-row",
            style: {
              backgroundColor: 'white',
              border: "".concat(style.borderWidth, "px solid ").concat(style.rowBorder),
              borderTop: 'none',
              color: style.textColor
            }
          }, /*#__PURE__*/React.createElement("div", {
            className: "table-cell",
            style: {
              borderRight: "".concat(style.borderWidth, "px solid ").concat(style.rowBorder)
            }
          }, "Article 2"), /*#__PURE__*/React.createElement("div", {
            className: "table-cell",
            style: {
              borderRight: "".concat(style.borderWidth, "px solid ").concat(style.rowBorder)
            }
          }, "1"), /*#__PURE__*/React.createElement("div", {
            className: "table-cell"
          }, "8.50\u20AC"))), /*#__PURE__*/React.createElement("span", {
            className: "style-label"
          }, style.label));
        }))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Lignes de totaux:"), /*#__PURE__*/React.createElement("div", {
          className: "checkbox-group"
        }, [{
          key: 'showSubtotal',
          label: 'Sous-total'
        }, {
          key: 'showShipping',
          label: 'Frais de port'
        }, {
          key: 'showTaxes',
          label: 'Taxes'
        }, {
          key: 'showDiscount',
          label: 'Remise'
        }, {
          key: 'showTotal',
          label: 'Total général'
        }].map(function (_ref6) {
          var key = _ref6.key,
            label = _ref6.label;
          return /*#__PURE__*/React.createElement("label", {
            key: key,
            className: "checkbox-item"
          }, /*#__PURE__*/React.createElement("input", {
            type: "checkbox",
            checked: localProperties[key] || false,
            onChange: function onChange(e) {
              return handlePropertyChange(selectedElement.id, key, e.target.checked);
            }
          }), label);
        }))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Afficher les bordures des cellules:"), /*#__PURE__*/React.createElement("label", {
          className: "toggle"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: (_localProperties$show = localProperties.showBorders) !== null && _localProperties$show !== void 0 ? _localProperties$show : true,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'showBorders', e.target.checked);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "toggle-slider"
        }))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", {
          style: {
            fontWeight: '600',
            color: '#2563eb',
            marginBottom: '8px',
            display: 'block'
          }
        }, "\uD83C\uDFA8 Couleurs individuelles des produits"), /*#__PURE__*/React.createElement("div", {
          className: "product-colors-editor"
        }, /*#__PURE__*/React.createElement("div", {
          className: "color-scheme-info"
        }, /*#__PURE__*/React.createElement("p", {
          style: {
            fontSize: '0.875rem',
            color: '#6b7280',
            margin: '0 0 12px 0'
          }
        }, "D\xE9finissez les couleurs pour les produits pairs et impairs :")), /*#__PURE__*/React.createElement("div", {
          className: "product-color-item"
        }, /*#__PURE__*/React.createElement("div", {
          className: "product-header"
        }, /*#__PURE__*/React.createElement("span", {
          className: "product-name"
        }, "\uD83D\uDCCF Produits pairs (1er, 3\xE8me, 5\xE8me...)"), /*#__PURE__*/React.createElement("span", {
          className: "product-index"
        }, "Even")), /*#__PURE__*/React.createElement("div", {
          className: "color-controls"
        }, /*#__PURE__*/React.createElement("div", {
          className: "color-control"
        }, /*#__PURE__*/React.createElement("label", null, "\uD83C\uDFAF Fond:"), /*#__PURE__*/React.createElement("div", {
          className: "color-input-wrapper"
        }, /*#__PURE__*/React.createElement("input", {
          type: "color",
          value: localProperties.evenRowBg || '#ffffff',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'evenRowBg', e.target.value);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "color-value"
        }, localProperties.evenRowBg || '#ffffff'))), /*#__PURE__*/React.createElement("div", {
          className: "color-control"
        }, /*#__PURE__*/React.createElement("label", null, "\uD83D\uDCDD Texte:"), /*#__PURE__*/React.createElement("div", {
          className: "color-input-wrapper"
        }, /*#__PURE__*/React.createElement("input", {
          type: "color",
          value: localProperties.evenRowTextColor || '#000000',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'evenRowTextColor', e.target.value);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "color-value"
        }, localProperties.evenRowTextColor || '#000000'))))), /*#__PURE__*/React.createElement("div", {
          className: "product-color-item"
        }, /*#__PURE__*/React.createElement("div", {
          className: "product-header"
        }, /*#__PURE__*/React.createElement("span", {
          className: "product-name"
        }, "\uD83D\uDCD0 Produits impairs (2\xE8me, 4\xE8me, 6\xE8me...)"), /*#__PURE__*/React.createElement("span", {
          className: "product-index"
        }, "Odd")), /*#__PURE__*/React.createElement("div", {
          className: "color-controls"
        }, /*#__PURE__*/React.createElement("div", {
          className: "color-control"
        }, /*#__PURE__*/React.createElement("label", null, "\uD83C\uDFAF Fond:"), /*#__PURE__*/React.createElement("div", {
          className: "color-input-wrapper"
        }, /*#__PURE__*/React.createElement("input", {
          type: "color",
          value: localProperties.oddRowBg || '#f9fafb',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'oddRowBg', e.target.value);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "color-value"
        }, localProperties.oddRowBg || '#f9fafb'))), /*#__PURE__*/React.createElement("div", {
          className: "color-control"
        }, /*#__PURE__*/React.createElement("label", null, "\uD83D\uDCDD Texte:"), /*#__PURE__*/React.createElement("div", {
          className: "color-input-wrapper"
        }, /*#__PURE__*/React.createElement("input", {
          type: "color",
          value: localProperties.oddRowTextColor || '#000000',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'oddRowTextColor', e.target.value);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "color-value"
        }, localProperties.oddRowTextColor || '#000000')))))))), allowedControls.includes('customer_fields') && selectedElement.type === 'customer_info' && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDC64 Informations client"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Champs \xE0 afficher:"), /*#__PURE__*/React.createElement("div", {
          className: "checkbox-group"
        }, [{
          key: 'name',
          label: 'Nom'
        }, {
          key: 'email',
          label: 'Email'
        }, {
          key: 'phone',
          label: 'Téléphone'
        }, {
          key: 'address',
          label: 'Adresse'
        }, {
          key: 'company',
          label: 'Société'
        }, {
          key: 'vat',
          label: 'N° TVA'
        }, {
          key: 'siret',
          label: 'SIRET'
        }].map(function (_ref7) {
          var _localProperties$fiel, _localProperties$fiel2;
          var key = _ref7.key,
            label = _ref7.label;
          return /*#__PURE__*/React.createElement("label", {
            key: key,
            className: "checkbox-item"
          }, /*#__PURE__*/React.createElement("input", {
            type: "checkbox",
            checked: (_localProperties$fiel = (_localProperties$fiel2 = localProperties.fields) === null || _localProperties$fiel2 === void 0 ? void 0 : _localProperties$fiel2.includes(key)) !== null && _localProperties$fiel !== void 0 ? _localProperties$fiel : true,
            onChange: function onChange(e) {
              var currentFields = localProperties.fields || ['name', 'email', 'phone', 'address', 'company', 'vat', 'siret'];
              var newFields = e.target.checked ? [].concat(PropertiesPanel_toConsumableArray(currentFields), [key]) : currentFields.filter(function (f) {
                return f !== key;
              });
              handlePropertyChange(selectedElement.id, 'fields', newFields);
            }
          }), label);
        }))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Disposition:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.layout || 'vertical',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'layout', e.target.value);
          }
        }, /*#__PURE__*/React.createElement("option", {
          value: "vertical"
        }, "Verticale"), /*#__PURE__*/React.createElement("option", {
          value: "horizontal"
        }, "Horizontale"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Afficher les \xE9tiquettes:"), /*#__PURE__*/React.createElement("label", {
          className: "toggle"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: (_localProperties$show2 = localProperties.showLabels) !== null && _localProperties$show2 !== void 0 ? _localProperties$show2 : true,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'showLabels', e.target.checked);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "toggle-slider"
        }))), localProperties.showLabels && /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Style des \xE9tiquettes:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.labelStyle || 'normal',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'labelStyle', e.target.value);
          }
        }, /*#__PURE__*/React.createElement("option", {
          value: "normal"
        }, "Normal"), /*#__PURE__*/React.createElement("option", {
          value: "bold"
        }, "Gras"), /*#__PURE__*/React.createElement("option", {
          value: "uppercase"
        }, "Majuscules"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Espacement:"), /*#__PURE__*/React.createElement("div", {
          className: "slider-container"
        }, /*#__PURE__*/React.createElement("input", {
          type: "range",
          min: "0",
          max: "20",
          value: localProperties.spacing || 8,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'spacing', safeParseInt(e.target.value, 10));
          },
          className: "slider"
        }), /*#__PURE__*/React.createElement("span", {
          className: "slider-value"
        }, localProperties.spacing || 8, "px")))), allowedControls.includes('mentions') && selectedElement.type === 'mentions' && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDCC4 Mentions l\xE9gales"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Informations \xE0 afficher:"), /*#__PURE__*/React.createElement("div", {
          className: "checkbox-group"
        }, [{
          key: 'showEmail',
          label: 'Email'
        }, {
          key: 'showPhone',
          label: 'Téléphone'
        }, {
          key: 'showSiret',
          label: 'SIRET'
        }, {
          key: 'showVat',
          label: 'N° TVA'
        }, {
          key: 'showAddress',
          label: 'Adresse'
        }, {
          key: 'showWebsite',
          label: 'Site web'
        }, {
          key: 'showCustomText',
          label: 'Texte personnalisé'
        }].map(function (_ref8) {
          var key = _ref8.key,
            label = _ref8.label;
          return /*#__PURE__*/React.createElement("label", {
            key: key,
            className: "checkbox-item"
          }, /*#__PURE__*/React.createElement("input", {
            type: "checkbox",
            checked: localProperties[key] || false,
            onChange: function onChange(e) {
              return handlePropertyChange(selectedElement.id, key, e.target.checked);
            }
          }), label);
        }))), localProperties.showCustomText && /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Texte personnalis\xE9:"), /*#__PURE__*/React.createElement("input", {
          type: "text",
          value: localProperties.customText || '',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'customText', e.target.value);
          },
          placeholder: "Ex: Mentions l\xE9gales personnalis\xE9es..."
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Disposition:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.layout || 'horizontal',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'layout', e.target.value);
          }
        }, /*#__PURE__*/React.createElement("option", {
          value: "horizontal"
        }, "Horizontale"), /*#__PURE__*/React.createElement("option", {
          value: "vertical"
        }, "Verticale"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "S\xE9parateur:"), /*#__PURE__*/React.createElement("input", {
          type: "text",
          value: localProperties.separator || ' • ',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'separator', e.target.value);
          },
          placeholder: " \u2022 ",
          style: {
            width: '60px'
          }
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Interligne:"), /*#__PURE__*/React.createElement("div", {
          className: "slider-container"
        }, /*#__PURE__*/React.createElement("input", {
          type: "range",
          min: "0.8",
          max: "2.0",
          step: "0.1",
          value: localProperties.lineHeight || 1.2,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'lineHeight', parseFloat(e.target.value));
          },
          className: "slider"
        }), /*#__PURE__*/React.createElement("span", {
          className: "slider-value"
        }, localProperties.lineHeight || 1.2)))), allowedControls.includes('dynamic_text') && selectedElement.type === 'dynamic-text' && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDCDD Texte Dynamique"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Mod\xE8le:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.template || 'total_only',
          onChange: function onChange(e) {
            var newTemplate = e.target.value;
            var oldTemplate = localProperties.template;
            handlePropertyChange(selectedElement.id, 'template', newTemplate);

            // Appliquer les presets seulement si c'est un changement de template
            // et seulement pour les propriétés qui ne sont pas déjà définies
            if (newTemplate !== oldTemplate) {
              var preset = TEMPLATE_PRESETS[newTemplate];
              if (preset) {
                Object.entries(preset).forEach(function (_ref9) {
                  var _ref0 = PropertiesPanel_slicedToArray(_ref9, 2),
                    property = _ref0[0],
                    defaultValue = _ref0[1];
                  // Appliquer seulement si la propriété n'est pas déjà personnalisée
                  // ou si elle a la valeur par défaut du template précédent
                  var currentValue = localProperties[property];
                  var oldPreset = oldTemplate ? TEMPLATE_PRESETS[oldTemplate] : null;
                  var oldDefaultValue = oldPreset ? oldPreset[property] : null;

                  // Appliquer le preset si :
                  // 1. La propriété n'est pas définie, ou
                  // 2. Elle a la valeur par défaut du template précédent
                  if (currentValue === undefined || currentValue === oldDefaultValue) {
                    handlePropertyChange(selectedElement.id, property, defaultValue);
                  }
                });
              }
            }
          }
        }, /*#__PURE__*/React.createElement("option", {
          value: "total_only"
        }, "\uD83D\uDCB0 Total uniquement"), /*#__PURE__*/React.createElement("option", {
          value: "order_info"
        }, "\uD83D\uDCCB Informations commande"), /*#__PURE__*/React.createElement("option", {
          value: "customer_info"
        }, "\uD83D\uDC64 Informations client"), /*#__PURE__*/React.createElement("option", {
          value: "customer_address"
        }, "\uD83C\uDFE0 Adresse client compl\xE8te"), /*#__PURE__*/React.createElement("option", {
          value: "full_header"
        }, "\uD83D\uDCC4 En-t\xEAte complet"), /*#__PURE__*/React.createElement("option", {
          value: "invoice_header"
        }, "\uD83D\uDCCB En-t\xEAte facture d\xE9taill\xE9"), /*#__PURE__*/React.createElement("option", {
          value: "order_summary"
        }, "\uD83E\uDDFE R\xE9capitulatif commande"), /*#__PURE__*/React.createElement("option", {
          value: "payment_info"
        }, "\uD83D\uDCB3 Informations paiement"), /*#__PURE__*/React.createElement("option", {
          value: "payment_terms"
        }, "\uD83D\uDCC5 Conditions de paiement"), /*#__PURE__*/React.createElement("option", {
          value: "shipping_info"
        }, "\uD83D\uDE9A Adresse de livraison"), /*#__PURE__*/React.createElement("option", {
          value: "thank_you"
        }, "\uD83D\uDE4F Message de remerciement"), /*#__PURE__*/React.createElement("option", {
          value: "legal_notice"
        }, "\u2696\uFE0F Mentions l\xE9gales"), /*#__PURE__*/React.createElement("option", {
          value: "bank_details"
        }, "\uD83C\uDFE6 Coordonn\xE9es bancaires"), /*#__PURE__*/React.createElement("option", {
          value: "contact_info"
        }, "\uD83D\uDCDE Informations de contact"), /*#__PURE__*/React.createElement("option", {
          value: "order_confirmation"
        }, "\u2705 Confirmation de commande"), /*#__PURE__*/React.createElement("option", {
          value: "delivery_note"
        }, "\uD83D\uDCE6 Bon de livraison"), /*#__PURE__*/React.createElement("option", {
          value: "warranty_info"
        }, "\uD83D\uDEE1\uFE0F Garantie produit"), /*#__PURE__*/React.createElement("option", {
          value: "return_policy"
        }, "\u21A9\uFE0F Politique de retour"), /*#__PURE__*/React.createElement("option", {
          value: "signature_line"
        }, "\u270D\uFE0F Ligne de signature"), /*#__PURE__*/React.createElement("option", {
          value: "invoice_footer"
        }, "\uD83D\uDCC4 Pied de facture"), /*#__PURE__*/React.createElement("option", {
          value: "terms_conditions"
        }, "\uD83D\uDCCB CGV"), /*#__PURE__*/React.createElement("option", {
          value: "quality_guarantee"
        }, "\u2B50 Garantie qualit\xE9"), /*#__PURE__*/React.createElement("option", {
          value: "eco_friendly"
        }, "\uD83C\uDF31 Engagement \xE9cologique"), /*#__PURE__*/React.createElement("option", {
          value: "follow_up"
        }, "\uD83D\uDCCA Suivi commande"), /*#__PURE__*/React.createElement("option", {
          value: "custom"
        }, "\uD83C\uDFA8 Personnalis\xE9"))), localProperties.template && localProperties.template !== 'custom' && /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null), /*#__PURE__*/React.createElement("button", {
          className: "reset-template-btn",
          onClick: function onClick() {
            var preset = TEMPLATE_PRESETS[localProperties.template];
            if (preset) {
              Object.entries(preset).forEach(function (_ref1) {
                var _ref10 = PropertiesPanel_slicedToArray(_ref1, 2),
                  property = _ref10[0],
                  value = _ref10[1];
                handlePropertyChange(selectedElement.id, property, value);
              });
            }
          },
          title: "R\xE9initialiser aux valeurs par d\xE9faut du template",
          style: {
            padding: '6px 12px',
            backgroundColor: '#f3f4f6',
            border: '1px solid #d1d5db',
            borderRadius: '4px',
            color: '#374151',
            fontSize: '12px',
            cursor: 'pointer',
            marginTop: '4px'
          }
        }, "\uD83D\uDD04 Valeurs par d\xE9faut")), localProperties.template === 'custom' && /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Contenu personnalis\xE9:"), /*#__PURE__*/React.createElement("textarea", {
          value: localProperties.customContent || '',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'customContent', e.target.value);
          },
          placeholder: "Utilisez des variables comme {{order_total}}, {{customer_name}}, etc.",
          rows: 4,
          style: {
            width: '100%',
            resize: 'vertical',
            minHeight: '80px'
          }
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row",
          style: {
            marginTop: '12px',
            padding: '8px',
            backgroundColor: '#f8fafc',
            borderRadius: '4px'
          }
        }, /*#__PURE__*/React.createElement("label", {
          style: {
            fontWeight: 'bold',
            marginBottom: '8px',
            display: 'block'
          }
        }, "Variables disponibles:"), /*#__PURE__*/React.createElement("div", {
          className: "variables-badges"
        }, /*#__PURE__*/React.createElement("div", {
          className: "variable-group"
        }, /*#__PURE__*/React.createElement("span", {
          className: "group-icon"
        }, "\uD83D\uDCB0"), /*#__PURE__*/React.createElement("span", {
          className: "group-label"
        }, "Commande:"), /*#__PURE__*/React.createElement("span", {
          className: "variable-badges"
        }, /*#__PURE__*/React.createElement("span", {
          className: "variable-badge",
          title: "Montant total de la commande"
        }, '{{order_total}}'), /*#__PURE__*/React.createElement("span", {
          className: "variable-badge",
          title: "Num\xE9ro de commande"
        }, '{{order_number}}'), /*#__PURE__*/React.createElement("span", {
          className: "variable-badge",
          title: "Date de la commande"
        }, '{{order_date}}'), /*#__PURE__*/React.createElement("span", {
          className: "variable-badge",
          title: "Sous-total HT"
        }, '{{order_subtotal}}'), /*#__PURE__*/React.createElement("span", {
          className: "variable-badge",
          title: "Montant TVA"
        }, '{{order_tax}}'), /*#__PURE__*/React.createElement("span", {
          className: "variable-badge",
          title: "Frais de port"
        }, '{{order_shipping}}'))), /*#__PURE__*/React.createElement("div", {
          className: "variable-group"
        }, /*#__PURE__*/React.createElement("span", {
          className: "group-icon"
        }, "\uD83D\uDC64"), /*#__PURE__*/React.createElement("span", {
          className: "group-label"
        }, "Client:"), /*#__PURE__*/React.createElement("span", {
          className: "variable-badges"
        }, /*#__PURE__*/React.createElement("span", {
          className: "variable-badge",
          title: "Nom du client"
        }, '{{customer_name}}'), /*#__PURE__*/React.createElement("span", {
          className: "variable-badge",
          title: "Email du client"
        }, '{{customer_email}}'), /*#__PURE__*/React.createElement("span", {
          className: "variable-badge",
          title: "Adresse de facturation"
        }, '{{billing_address}}'), /*#__PURE__*/React.createElement("span", {
          className: "variable-badge",
          title: "Adresse de livraison"
        }, '{{shipping_address}}'))), /*#__PURE__*/React.createElement("div", {
          className: "variable-group"
        }, /*#__PURE__*/React.createElement("span", {
          className: "group-icon"
        }, "\uD83D\uDCC5"), /*#__PURE__*/React.createElement("span", {
          className: "group-label"
        }, "Dates:"), /*#__PURE__*/React.createElement("span", {
          className: "variable-badges"
        }, /*#__PURE__*/React.createElement("span", {
          className: "variable-badge",
          title: "Date actuelle"
        }, '{{date}}'), /*#__PURE__*/React.createElement("span", {
          className: "variable-badge",
          title: "Date d'\xE9ch\xE9ance"
        }, '{{due_date}}')))))), allowedControls.includes('company_fields') && selectedElement.type === 'company_info' && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83C\uDFE2 Informations Entreprise"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Champs \xE0 afficher:"), /*#__PURE__*/React.createElement("div", {
          className: "checkbox-group"
        }, [{
          key: 'name',
          label: 'Nom'
        }, {
          key: 'address',
          label: 'Adresse'
        }, {
          key: 'phone',
          label: 'Téléphone'
        }, {
          key: 'email',
          label: 'Email'
        }, {
          key: 'website',
          label: 'Site web'
        }, {
          key: 'vat',
          label: 'N° TVA'
        }, {
          key: 'rcs',
          label: 'RCS'
        }, {
          key: 'siret',
          label: 'SIRET'
        }].map(function (_ref11) {
          var _localProperties$fiel3, _localProperties$fiel4;
          var key = _ref11.key,
            label = _ref11.label;
          return /*#__PURE__*/React.createElement("label", {
            key: key,
            className: "checkbox-item"
          }, /*#__PURE__*/React.createElement("input", {
            type: "checkbox",
            checked: (_localProperties$fiel3 = (_localProperties$fiel4 = localProperties.fields) === null || _localProperties$fiel4 === void 0 ? void 0 : _localProperties$fiel4.includes(key)) !== null && _localProperties$fiel3 !== void 0 ? _localProperties$fiel3 : true,
            onChange: function onChange(e) {
              var currentFields = localProperties.fields || ['name', 'address', 'phone', 'email', 'website', 'vat', 'rcs', 'siret'];
              var newFields = e.target.checked ? [].concat(PropertiesPanel_toConsumableArray(currentFields), [key]) : currentFields.filter(function (f) {
                return f !== key;
              });
              handlePropertyChange(selectedElement.id, 'fields', newFields);
            }
          }), label);
        }))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Disposition:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.layout || 'vertical',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'layout', e.target.value);
          }
        }, /*#__PURE__*/React.createElement("option", {
          value: "vertical"
        }, "Verticale"), /*#__PURE__*/React.createElement("option", {
          value: "horizontal"
        }, "Horizontale"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Afficher les \xE9tiquettes:"), /*#__PURE__*/React.createElement("label", {
          className: "toggle"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: (_localProperties$show3 = localProperties.showLabels) !== null && _localProperties$show3 !== void 0 ? _localProperties$show3 : false,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'showLabels', e.target.checked);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "toggle-slider"
        }))), localProperties.showLabels && /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Style des \xE9tiquettes:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.labelStyle || 'normal',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'labelStyle', e.target.value);
          }
        }, /*#__PURE__*/React.createElement("option", {
          value: "normal"
        }, "Normal"), /*#__PURE__*/React.createElement("option", {
          value: "bold"
        }, "Gras"), /*#__PURE__*/React.createElement("option", {
          value: "uppercase"
        }, "Majuscules"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Espacement:"), /*#__PURE__*/React.createElement("div", {
          className: "slider-container"
        }, /*#__PURE__*/React.createElement("input", {
          type: "range",
          min: "0",
          max: "20",
          value: localProperties.spacing || 8,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'spacing', safeParseInt(e.target.value, 10));
          },
          className: "slider"
        }), /*#__PURE__*/React.createElement("span", {
          className: "slider-value"
        }, localProperties.spacing || 8, "px")))), allowedControls.includes('document_type') && selectedElement.type === 'document_type' && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDCCB Type de Document"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Type de document:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.documentType || 'invoice',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'documentType', e.target.value);
          }
        }, /*#__PURE__*/React.createElement("option", {
          value: "invoice"
        }, "Facture"), /*#__PURE__*/React.createElement("option", {
          value: "quote"
        }, "Devis"), /*#__PURE__*/React.createElement("option", {
          value: "receipt"
        }, "Re\xE7u"), /*#__PURE__*/React.createElement("option", {
          value: "order"
        }, "Commande"), /*#__PURE__*/React.createElement("option", {
          value: "credit_note"
        }, "Avoir")))), allowedControls.includes('order_number') && selectedElement.type === 'order_number' && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDD22 Num\xE9ro de Commande"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Format d'affichage:"), /*#__PURE__*/React.createElement("input", {
          type: "text",
          value: localProperties.format || 'Commande #{order_number} - {order_date}',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'format', e.target.value);
          },
          placeholder: "Commande #{order_number} - {order_date}"
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Afficher l'\xE9tiquette:"), /*#__PURE__*/React.createElement("label", {
          className: "toggle"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: (_localProperties$show4 = localProperties.showLabel) !== null && _localProperties$show4 !== void 0 ? _localProperties$show4 : true,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'showLabel', e.target.checked);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "toggle-slider"
        }))), localProperties.showLabel && /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Texte de l'\xE9tiquette:"), /*#__PURE__*/React.createElement("input", {
          type: "text",
          value: localProperties.labelText || 'N° de commande:',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'labelText', e.target.value);
          },
          placeholder: "N\xB0 de commande:"
        }))), allowedControls.includes('font') && /*#__PURE__*/React.createElement(FontControls, {
          elementId: selectedElement.id,
          properties: localProperties,
          onPropertyChange: handlePropertyChange
        }), allowedControls.includes('image') && (selectedElement.type === 'logo' || selectedElement.type === 'company_logo') && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "[Img] Image"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "URL de l'image:"), /*#__PURE__*/React.createElement("div", {
          className: "input-with-button"
        }, /*#__PURE__*/React.createElement("input", {
          type: "text",
          value: localProperties.imageUrl || localProperties.src || '',
          onChange: function onChange(e) {
            handlePropertyChange(selectedElement.id, 'imageUrl', e.target.value);
            handlePropertyChange(selectedElement.id, 'src', e.target.value);
          },
          placeholder: "https://exemple.com/image.png"
        }), /*#__PURE__*/React.createElement("button", {
          type: "button",
          className: "media-button",
          onClick: /*#__PURE__*/PropertiesPanel_asyncToGenerator(/*#__PURE__*/PropertiesPanel_regenerator().m(function _callee() {
            var response, media, modal, modalContent, title, closeBtn, grid, _t;
            return PropertiesPanel_regenerator().w(function (_context) {
              while (1) switch (_context.p = _context.n) {
                case 0:
                  _context.p = 0;
                  _context.n = 1;
                  return fetch('/wp-json/wp/v2/media?media_type=image&per_page=50&_embed');
                case 1:
                  response = _context.v;
                  _context.n = 2;
                  return response.json();
                case 2:
                  media = _context.v;
                  // Créer une modale simple pour sélectionner l'image
                  modal = document.createElement('div');
                  modal.style.cssText = "\n                          position: fixed;\n                          top: 0;\n                          left: 0;\n                          width: 100%;\n                          height: 100%;\n                          background: rgba(0,0,0,0.8);\n                          z-index: 100;\n                          display: flex;\n                          align-items: center;\n                          justify-content: center;\n                        ";
                  modalContent = document.createElement('div');
                  modalContent.style.cssText = "\n                          background: white;\n                          padding: 20px;\n                          border-radius: 8px;\n                          max-width: 600px;\n                          max-height: 80vh;\n                          overflow-y: auto;\n                          width: 90%;\n                        ";
                  title = document.createElement('h3');
                  title.textContent = 'Sélectionner une image depuis la médiathèque';
                  title.style.marginBottom = '15px';
                  closeBtn = document.createElement('button');
                  closeBtn.textContent = '✕';
                  closeBtn.style.cssText = "\n                          position: absolute;\n                          top: 10px;\n                          right: 10px;\n                          background: none;\n                          border: none;\n                          font-size: 20px;\n                          cursor: pointer;\n                        ";
                  closeBtn.onclick = function () {
                    // Vérifier que la modale existe encore avant de la supprimer
                    if (modal && modal.parentNode === document.body) {
                      document.body.removeChild(modal);
                    }
                  };
                  grid = document.createElement('div');
                  grid.style.cssText = "\n                          display: grid;\n                          grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));\n                          gap: 10px;\n                          margin-top: 15px;\n                        ";
                  media.forEach(function (item) {
                    var imgContainer = document.createElement('div');
                    imgContainer.style.cssText = "\n                            border: 2px solid #ddd;\n                            border-radius: 4px;\n                            padding: 5px;\n                            cursor: pointer;\n                            transition: border-color 0.2s;\n                          ";
                    imgContainer.onmouseover = function () {
                      return imgContainer.style.borderColor = '#007cba';
                    };
                    imgContainer.onmouseout = function () {
                      return imgContainer.style.borderColor = '#ddd';
                    };
                    var img = document.createElement('img');
                    img.src = item.source_url;
                    img.style.cssText = "\n                            width: 100%;\n                            height: 80px;\n                            object-fit: cover;\n                            border-radius: 2px;\n                          ";
                    var name = document.createElement('div');
                    name.textContent = item.title.rendered.length > 15 ? item.title.rendered.substring(0, 15) + '...' : item.title.rendered;
                    name.style.cssText = "\n                            font-size: 11px;\n                            text-align: center;\n                            margin-top: 5px;\n                            color: #666;\n                          ";
                    imgContainer.onclick = function () {
                      handlePropertyChange(selectedElement.id, 'imageUrl', item.source_url);
                      handlePropertyChange(selectedElement.id, 'src', item.source_url);
                      // Vérifier que la modale existe encore avant de la supprimer
                      if (modal && modal.parentNode === document.body) {
                        document.body.removeChild(modal);
                      }
                    };
                    imgContainer.appendChild(img);
                    imgContainer.appendChild(name);
                    grid.appendChild(imgContainer);
                  });
                  modalContent.appendChild(title);
                  modalContent.appendChild(closeBtn);
                  modalContent.appendChild(grid);
                  modal.appendChild(modalContent);
                  document.body.appendChild(modal);
                  _context.n = 4;
                  break;
                case 3:
                  _context.p = 3;
                  _t = _context.v;
                  console.error('Erreur lors de la récupération des médias:', _t);
                  alert('Erreur lors de l\'accès à la médiathèque WordPress');
                case 4:
                  return _context.a(2);
              }
            }, _callee, null, [[0, 3]]);
          }))
        }, "\uD83D\uDCC1 M\xE9diath\xE8que"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Texte alternatif:"), /*#__PURE__*/React.createElement("input", {
          type: "text",
          value: localProperties.alt || '',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'alt', e.target.value);
          },
          placeholder: "Description de l'image"
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Ajustement:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.objectFit || localProperties.fit || 'cover',
          onChange: function onChange(e) {
            handlePropertyChange(selectedElement.id, 'objectFit', e.target.value);
            handlePropertyChange(selectedElement.id, 'fit', e.target.value);
          }
        }, /*#__PURE__*/React.createElement("option", {
          value: "cover"
        }, "Couvrir"), /*#__PURE__*/React.createElement("option", {
          value: "contain"
        }, "Contenir"), /*#__PURE__*/React.createElement("option", {
          value: "fill"
        }, "Remplir"), /*#__PURE__*/React.createElement("option", {
          value: "none"
        }, "Aucun"), /*#__PURE__*/React.createElement("option", {
          value: "scale-down"
        }, "R\xE9duire")))), selectedElement.type === 'document_type' && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDCCB Type de Document"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Type de document:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.documentType || 'invoice',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'documentType', e.target.value);
          }
        }, /*#__PURE__*/React.createElement("option", {
          value: "invoice"
        }, "Facture"), /*#__PURE__*/React.createElement("option", {
          value: "quote"
        }, "Devis"), /*#__PURE__*/React.createElement("option", {
          value: "receipt"
        }, "Re\xE7u"), /*#__PURE__*/React.createElement("option", {
          value: "order"
        }, "Commande"), /*#__PURE__*/React.createElement("option", {
          value: "credit_note"
        }, "Avoir"))), /*#__PURE__*/React.createElement(FontControls, {
          elementId: selectedElement.id,
          properties: localProperties,
          onPropertyChange: handlePropertyChange
        }), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Alignement du texte:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.textAlign || 'center',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'textAlign', e.target.value);
          }
        }, /*#__PURE__*/React.createElement("option", {
          value: "left"
        }, "Gauche"), /*#__PURE__*/React.createElement("option", {
          value: "center"
        }, "Centre"), /*#__PURE__*/React.createElement("option", {
          value: "right"
        }, "Droite"))), /*#__PURE__*/React.createElement(ColorPicker, {
          label: "Couleur du texte",
          value: localProperties.color,
          onChange: function onChange(value) {
            return handlePropertyChange(selectedElement.id, 'color', value);
          },
          presets: ['#1e293b', '#334155', '#475569', '#64748b', '#000000', '#dc2626', '#059669', '#7c3aed'],
          defaultColor: "#333333"
        }), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Afficher la bordure:"), /*#__PURE__*/React.createElement("label", {
          className: "toggle"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: localProperties.showBorder || false,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'showBorder', e.target.checked);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "toggle-slider"
        }))), /*#__PURE__*/React.createElement(ColorPicker, {
          label: "Couleur de fond",
          value: localProperties.backgroundColor,
          onChange: function onChange(value) {
            return handlePropertyChange(selectedElement.id, 'backgroundColor', value);
          },
          presets: ['transparent', '#ffffff', '#f8fafc', '#fef3c7', '#ecfdf5', '#f0f9ff']
        })), selectedElement.type !== 'product_table' && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uFFFD Contenu"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Texte/Contenu:"), /*#__PURE__*/React.createElement("input", {
          type: "text",
          value: localProperties.content || '',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'content', e.target.value);
          },
          placeholder: "Texte \xE0 afficher"
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Format:"), /*#__PURE__*/React.createElement("input", {
          type: "text",
          value: localProperties.format || '',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'format', e.target.value);
          },
          placeholder: "Format d'affichage (optionnel)"
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Type de document:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.documentType || 'invoice',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'documentType', e.target.value);
          }
        }, /*#__PURE__*/React.createElement("option", {
          value: "invoice"
        }, "Facture"), /*#__PURE__*/React.createElement("option", {
          value: "quote"
        }, "Devis"), /*#__PURE__*/React.createElement("option", {
          value: "receipt"
        }, "Re\xE7u"), /*#__PURE__*/React.createElement("option", {
          value: "order"
        }, "Commande"), /*#__PURE__*/React.createElement("option", {
          value: "credit_note"
        }, "Avoir")))), selectedElement.type !== 'product_table' && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDCCB Champs & Options"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Champs \xE0 afficher:"), /*#__PURE__*/React.createElement("div", {
          className: "checkbox-group"
        }, [{
          key: 'name',
          label: 'Nom'
        }, {
          key: 'address',
          label: 'Adresse'
        }, {
          key: 'phone',
          label: 'Téléphone'
        }, {
          key: 'email',
          label: 'Email'
        }, {
          key: 'website',
          label: 'Site web'
        }, {
          key: 'vat',
          label: 'N° TVA'
        }, {
          key: 'image',
          label: 'Image'
        }, {
          key: 'sku',
          label: 'SKU'
        }, {
          key: 'quantity',
          label: 'Quantité'
        }, {
          key: 'price',
          label: 'Prix'
        }, {
          key: 'total',
          label: 'Total'
        }].map(function (_ref13) {
          var _localProperties$fiel5, _localProperties$fiel6;
          var key = _ref13.key,
            label = _ref13.label;
          return /*#__PURE__*/React.createElement("label", {
            key: key,
            className: "checkbox-item"
          }, /*#__PURE__*/React.createElement("input", {
            type: "checkbox",
            checked: (_localProperties$fiel5 = (_localProperties$fiel6 = localProperties.fields) === null || _localProperties$fiel6 === void 0 ? void 0 : _localProperties$fiel6.includes(key)) !== null && _localProperties$fiel5 !== void 0 ? _localProperties$fiel5 : false,
            onChange: function onChange(e) {
              var currentFields = localProperties.fields || [];
              var newFields = e.target.checked ? [].concat(PropertiesPanel_toConsumableArray(currentFields), [key]) : currentFields.filter(function (f) {
                return f !== key;
              });
              handlePropertyChange(selectedElement.id, 'fields', newFields);
            }
          }), label);
        }))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Afficher l'\xE9tiquette:"), /*#__PURE__*/React.createElement("label", {
          className: "toggle"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: (_localProperties$show5 = localProperties.showLabel) !== null && _localProperties$show5 !== void 0 ? _localProperties$show5 : false,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'showLabel', e.target.checked);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "toggle-slider"
        }))), localProperties.showLabel && /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Texte de l'\xE9tiquette:"), /*#__PURE__*/React.createElement("input", {
          type: "text",
          value: localProperties.labelText || '',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'labelText', e.target.value);
          },
          placeholder: "Texte de l'\xE9tiquette"
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Afficher les bordures:"), /*#__PURE__*/React.createElement("label", {
          className: "toggle"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: (_localProperties$show6 = localProperties.showBorders) !== null && _localProperties$show6 !== void 0 ? _localProperties$show6 : true,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'showBorders', e.target.checked);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "toggle-slider"
        }))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Afficher les en-t\xEAtes:"), /*#__PURE__*/React.createElement("label", {
          className: "toggle"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: (_localProperties$show7 = localProperties.showHeaders) !== null && _localProperties$show7 !== void 0 ? _localProperties$show7 : false,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'showHeaders', e.target.checked);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "toggle-slider"
        })))));
      case 'effects':
        return /*#__PURE__*/React.createElement("div", {
          className: "tab-content"
        }, allowedControls.includes('opacity') && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83C\uDF1F Transparence & Visibilit\xE9"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Opacit\xE9:"), /*#__PURE__*/React.createElement("div", {
          className: "slider-container"
        }, /*#__PURE__*/React.createElement("input", {
          type: "range",
          min: "0",
          max: "100",
          value: localProperties.opacity || 100,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'opacity', safeParseInt(e.target.value, 100));
          },
          className: "slider"
        }), /*#__PURE__*/React.createElement("span", {
          className: "slider-value"
        }, localProperties.opacity || 100, "%"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Visibilit\xE9:"), /*#__PURE__*/React.createElement("label", {
          className: "toggle"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: localProperties.visible !== false,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'visible', e.target.checked);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "toggle-slider"
        })))), allowedControls.includes('shadows') && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\u2728 Ombres & Effets"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Ombre:"), /*#__PURE__*/React.createElement("label", {
          className: "toggle"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: localProperties.shadow || false,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'shadow', e.target.checked);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "toggle-slider"
        }))), localProperties.shadow && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(ColorPicker, {
          label: "Couleur ombre",
          value: localProperties.shadowColor,
          onChange: function onChange(value) {
            return handlePropertyChange(selectedElement.id, 'shadowColor', value);
          },
          presets: ['#000000', '#374151', '#6b7280', '#9ca3af']
        }), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "D\xE9calage X:"), /*#__PURE__*/React.createElement("input", {
          type: "number",
          value: localProperties.shadowOffsetX || 2,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'shadowOffsetX', safeParseInt(e.target.value, 0));
          },
          min: "-20",
          max: "20"
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "D\xE9calage Y:"), /*#__PURE__*/React.createElement("input", {
          type: "number",
          value: localProperties.shadowOffsetY || 2,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'shadowOffsetY', safeParseInt(e.target.value, 0));
          },
          min: "-20",
          max: "20"
        })))), allowedControls.includes('filters') && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83C\uDFAD Filtres visuels"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Luminosit\xE9:"), /*#__PURE__*/React.createElement("div", {
          className: "slider-container"
        }, /*#__PURE__*/React.createElement("input", {
          type: "range",
          min: "0",
          max: "200",
          value: localProperties.brightness || 100,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'brightness', safeParseInt(e.target.value, 100));
          },
          className: "slider"
        }), /*#__PURE__*/React.createElement("span", {
          className: "slider-value"
        }, localProperties.brightness || 100, "%"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Contraste:"), /*#__PURE__*/React.createElement("div", {
          className: "slider-container"
        }, /*#__PURE__*/React.createElement("input", {
          type: "range",
          min: "0",
          max: "200",
          value: localProperties.contrast || 100,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'contrast', parseInt(e.target.value));
          },
          className: "slider"
        }), /*#__PURE__*/React.createElement("span", {
          className: "slider-value"
        }, localProperties.contrast || 100, "%"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Saturation:"), /*#__PURE__*/React.createElement("div", {
          className: "slider-container"
        }, /*#__PURE__*/React.createElement("input", {
          type: "range",
          min: "0",
          max: "200",
          value: localProperties.saturate || 100,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'saturate', parseInt(e.target.value));
          },
          className: "slider"
        }), /*#__PURE__*/React.createElement("span", {
          className: "slider-value"
        }, localProperties.saturate || 100, "%")))));
      default:
        return null;
    }
  }, [activeTab, selectedElement, localProperties, handlePropertyChange, selectedElements.length]);
  return /*#__PURE__*/React.createElement("div", {
    className: "properties-panel"
  }, /*#__PURE__*/React.createElement("div", {
    className: "properties-header"
  }, /*#__PURE__*/React.createElement("h3", null, "Propri\xE9t\xE9s"), selectedElement && /*#__PURE__*/React.createElement("div", {
    className: "element-info"
  }, /*#__PURE__*/React.createElement("span", {
    className: "element-type"
  }, selectedElement.type), /*#__PURE__*/React.createElement("span", {
    className: "element-id"
  }, "#", selectedElement.id))), renderTabs(), /*#__PURE__*/React.createElement("div", {
    className: "properties-content"
  }, renderTabContent()));
});
/* harmony default export */ const components_PropertiesPanel = (PropertiesPanel);
;// ./resources/js/components/NewTemplateModal.jsx
function NewTemplateModal_typeof(o) { "@babel/helpers - typeof"; return NewTemplateModal_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, NewTemplateModal_typeof(o); }
function NewTemplateModal_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function NewTemplateModal_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? NewTemplateModal_ownKeys(Object(t), !0).forEach(function (r) { NewTemplateModal_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : NewTemplateModal_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function NewTemplateModal_defineProperty(e, r, t) { return (r = NewTemplateModal_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function NewTemplateModal_toPropertyKey(t) { var i = NewTemplateModal_toPrimitive(t, "string"); return "symbol" == NewTemplateModal_typeof(i) ? i : i + ""; }
function NewTemplateModal_toPrimitive(t, r) { if ("object" != NewTemplateModal_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != NewTemplateModal_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function NewTemplateModal_slicedToArray(r, e) { return NewTemplateModal_arrayWithHoles(r) || NewTemplateModal_iterableToArrayLimit(r, e) || NewTemplateModal_unsupportedIterableToArray(r, e) || NewTemplateModal_nonIterableRest(); }
function NewTemplateModal_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function NewTemplateModal_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return NewTemplateModal_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? NewTemplateModal_arrayLikeToArray(r, a) : void 0; } }
function NewTemplateModal_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function NewTemplateModal_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function NewTemplateModal_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var NewTemplateModal = function NewTemplateModal(_ref) {
  var isOpen = _ref.isOpen,
    onClose = _ref.onClose,
    onCreateTemplate = _ref.onCreateTemplate;
  var _useState = (0,react.useState)({
      name: '',
      defaultModel: 'Facture',
      description: '',
      isPublic: false,
      paperFormat: 'A4 (210 × 297 mm)',
      orientation: 'Portrait',
      category: 'Facture'
    }),
    _useState2 = NewTemplateModal_slicedToArray(_useState, 2),
    formData = _useState2[0],
    setFormData = _useState2[1];
  var _useState3 = (0,react.useState)(false),
    _useState4 = NewTemplateModal_slicedToArray(_useState3, 2),
    showAdvanced = _useState4[0],
    setShowAdvanced = _useState4[1];
  var _useState5 = (0,react.useState)({}),
    _useState6 = NewTemplateModal_slicedToArray(_useState5, 2),
    errors = _useState6[0],
    setErrors = _useState6[1];
  var handleInputChange = function handleInputChange(field, value) {
    setFormData(function (prev) {
      return NewTemplateModal_objectSpread(NewTemplateModal_objectSpread({}, prev), {}, NewTemplateModal_defineProperty({}, field, value));
    });
    // Clear error when user starts typing
    if (errors[field]) {
      setErrors(function (prev) {
        return NewTemplateModal_objectSpread(NewTemplateModal_objectSpread({}, prev), {}, NewTemplateModal_defineProperty({}, field, ''));
      });
    }
  };
  var validateForm = function validateForm() {
    var newErrors = {};
    if (!formData.name.trim()) {
      newErrors.name = 'Le nom du template est obligatoire';
    }
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };
  var handleSubmit = function handleSubmit(e) {
    e.preventDefault();
    if (validateForm()) {
      onCreateTemplate(formData);
      onClose();
      // Reset form
      setFormData({
        name: '',
        defaultModel: 'Facture',
        description: '',
        isPublic: false,
        paperFormat: 'A4 (210 × 297 mm)',
        orientation: 'Portrait',
        category: 'Facture'
      });
      setShowAdvanced(false);
    }
  };
  if (!isOpen) return null;
  return /*#__PURE__*/react.createElement("div", {
    className: "modal-overlay",
    onClick: onClose
  }, /*#__PURE__*/react.createElement("div", {
    className: "modal-content new-template-modal",
    onClick: function onClick(e) {
      return e.stopPropagation();
    }
  }, /*#__PURE__*/react.createElement("div", {
    className: "modal-header"
  }, /*#__PURE__*/react.createElement("h3", null, "Nouveau template"), /*#__PURE__*/react.createElement("button", {
    className: "modal-close",
    onClick: onClose
  }, "\xD7")), /*#__PURE__*/react.createElement("form", {
    onSubmit: handleSubmit,
    className: "modal-body"
  }, /*#__PURE__*/react.createElement("div", {
    className: "form-group"
  }, /*#__PURE__*/react.createElement("label", {
    htmlFor: "template-name"
  }, "Nom du template *"), /*#__PURE__*/react.createElement("input", {
    id: "template-name",
    type: "text",
    value: formData.name,
    onChange: function onChange(e) {
      return handleInputChange('name', e.target.value);
    },
    className: errors.name ? 'error' : '',
    placeholder: "Ex: Facture Standard"
  }), errors.name && /*#__PURE__*/react.createElement("span", {
    className: "error-message"
  }, errors.name)), /*#__PURE__*/react.createElement("div", {
    className: "form-group"
  }, /*#__PURE__*/react.createElement("label", {
    htmlFor: "default-model"
  }, "Mod\xE8le par d\xE9faut"), /*#__PURE__*/react.createElement("select", {
    id: "default-model",
    value: formData.defaultModel,
    onChange: function onChange(e) {
      return handleInputChange('defaultModel', e.target.value);
    }
  }, /*#__PURE__*/react.createElement("option", {
    value: "Facture"
  }, "Facture"), /*#__PURE__*/react.createElement("option", {
    value: "Devis"
  }, "Devis"), /*#__PURE__*/react.createElement("option", {
    value: "Bon de commande"
  }, "Bon de commande"), /*#__PURE__*/react.createElement("option", {
    value: "Bon de livraison"
  }, "Bon de livraison"))), /*#__PURE__*/react.createElement("div", {
    className: "form-group"
  }, /*#__PURE__*/react.createElement("label", {
    htmlFor: "description"
  }, "Description"), /*#__PURE__*/react.createElement("textarea", {
    id: "description",
    value: formData.description,
    onChange: function onChange(e) {
      return handleInputChange('description', e.target.value);
    },
    placeholder: "Description du template...",
    rows: 3
  })), /*#__PURE__*/react.createElement("div", {
    className: "form-group"
  }, /*#__PURE__*/react.createElement("button", {
    type: "button",
    className: "advanced-toggle",
    onClick: function onClick() {
      return setShowAdvanced(!showAdvanced);
    }
  }, "Param\xE8tres avanc\xE9s ", showAdvanced ? '▼' : '▶')), showAdvanced && /*#__PURE__*/react.createElement("div", {
    className: "advanced-settings"
  }, /*#__PURE__*/react.createElement("div", {
    className: "form-group checkbox-group"
  }, /*#__PURE__*/react.createElement("label", {
    className: "checkbox-label"
  }, /*#__PURE__*/react.createElement("input", {
    type: "checkbox",
    checked: formData.isPublic,
    onChange: function onChange(e) {
      return handleInputChange('isPublic', e.target.checked);
    }
  }), /*#__PURE__*/react.createElement("span", null, "Template public (visible par tous les utilisateurs)"))), /*#__PURE__*/react.createElement("div", {
    className: "form-group"
  }, /*#__PURE__*/react.createElement("label", {
    htmlFor: "paper-format"
  }, "Format de papier"), /*#__PURE__*/react.createElement("select", {
    id: "paper-format",
    value: formData.paperFormat,
    onChange: function onChange(e) {
      return handleInputChange('paperFormat', e.target.value);
    }
  }, /*#__PURE__*/react.createElement("option", {
    value: "A4 (210 \xD7 297 mm)"
  }, "A4 (210 \xD7 297 mm)"), /*#__PURE__*/react.createElement("option", {
    value: "A5 (148 \xD7 210 mm)"
  }, "A5 (148 \xD7 210 mm)"), /*#__PURE__*/react.createElement("option", {
    value: "Lettre (8.5 \xD7 11 pouces)"
  }, "Lettre (8.5 \xD7 11 pouces)"), /*#__PURE__*/react.createElement("option", {
    value: "Legal (8.5 \xD7 14 pouces)"
  }, "Legal (8.5 \xD7 14 pouces)"))), /*#__PURE__*/react.createElement("div", {
    className: "form-group"
  }, /*#__PURE__*/react.createElement("label", {
    htmlFor: "orientation"
  }, "Orientation"), /*#__PURE__*/react.createElement("select", {
    id: "orientation",
    value: formData.orientation,
    onChange: function onChange(e) {
      return handleInputChange('orientation', e.target.value);
    }
  }, /*#__PURE__*/react.createElement("option", {
    value: "Portrait"
  }, "Portrait"), /*#__PURE__*/react.createElement("option", {
    value: "Paysage"
  }, "Paysage"))), /*#__PURE__*/react.createElement("div", {
    className: "form-group"
  }, /*#__PURE__*/react.createElement("label", {
    htmlFor: "category"
  }, "Cat\xE9gorie"), /*#__PURE__*/react.createElement("select", {
    id: "category",
    value: formData.category,
    onChange: function onChange(e) {
      return handleInputChange('category', e.target.value);
    }
  }, /*#__PURE__*/react.createElement("option", {
    value: "Facture"
  }, "Facture"), /*#__PURE__*/react.createElement("option", {
    value: "Devis"
  }, "Devis"), /*#__PURE__*/react.createElement("option", {
    value: "Bon de commande"
  }, "Bon de commande"), /*#__PURE__*/react.createElement("option", {
    value: "Bon de livraison"
  }, "Bon de livraison"), /*#__PURE__*/react.createElement("option", {
    value: "Re\xE7u"
  }, "Re\xE7u"), /*#__PURE__*/react.createElement("option", {
    value: "Autre"
  }, "Autre")))), /*#__PURE__*/react.createElement("div", {
    className: "modal-footer"
  }, /*#__PURE__*/react.createElement("button", {
    type: "button",
    className: "btn-secondary",
    onClick: onClose
  }, "Annuler"), /*#__PURE__*/react.createElement("button", {
    type: "submit",
    className: "btn-primary"
  }, "Ouvrir le template")))));
};
/* harmony default export */ const components_NewTemplateModal = (NewTemplateModal);
;// ./resources/js/components/PDFCanvasEditor.jsx
function PDFCanvasEditor_typeof(o) { "@babel/helpers - typeof"; return PDFCanvasEditor_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, PDFCanvasEditor_typeof(o); }
function PDFCanvasEditor_regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return PDFCanvasEditor_regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (PDFCanvasEditor_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, PDFCanvasEditor_regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, PDFCanvasEditor_regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), PDFCanvasEditor_regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", PDFCanvasEditor_regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), PDFCanvasEditor_regeneratorDefine2(u), PDFCanvasEditor_regeneratorDefine2(u, o, "Generator"), PDFCanvasEditor_regeneratorDefine2(u, n, function () { return this; }), PDFCanvasEditor_regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (PDFCanvasEditor_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function PDFCanvasEditor_regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } PDFCanvasEditor_regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { PDFCanvasEditor_regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, PDFCanvasEditor_regeneratorDefine2(e, r, n, t); }
function PDFCanvasEditor_asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function PDFCanvasEditor_asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { PDFCanvasEditor_asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { PDFCanvasEditor_asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function PDFCanvasEditor_toConsumableArray(r) { return PDFCanvasEditor_arrayWithoutHoles(r) || PDFCanvasEditor_iterableToArray(r) || PDFCanvasEditor_unsupportedIterableToArray(r) || PDFCanvasEditor_nonIterableSpread(); }
function PDFCanvasEditor_nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function PDFCanvasEditor_iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function PDFCanvasEditor_arrayWithoutHoles(r) { if (Array.isArray(r)) return PDFCanvasEditor_arrayLikeToArray(r); }
function PDFCanvasEditor_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function PDFCanvasEditor_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? PDFCanvasEditor_ownKeys(Object(t), !0).forEach(function (r) { PDFCanvasEditor_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : PDFCanvasEditor_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function PDFCanvasEditor_defineProperty(e, r, t) { return (r = PDFCanvasEditor_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function PDFCanvasEditor_toPropertyKey(t) { var i = PDFCanvasEditor_toPrimitive(t, "string"); return "symbol" == PDFCanvasEditor_typeof(i) ? i : i + ""; }
function PDFCanvasEditor_toPrimitive(t, r) { if ("object" != PDFCanvasEditor_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != PDFCanvasEditor_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function PDFCanvasEditor_slicedToArray(r, e) { return PDFCanvasEditor_arrayWithHoles(r) || PDFCanvasEditor_iterableToArrayLimit(r, e) || PDFCanvasEditor_unsupportedIterableToArray(r, e) || PDFCanvasEditor_nonIterableRest(); }
function PDFCanvasEditor_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function PDFCanvasEditor_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return PDFCanvasEditor_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? PDFCanvasEditor_arrayLikeToArray(r, a) : void 0; } }
function PDFCanvasEditor_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function PDFCanvasEditor_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function PDFCanvasEditor_arrayWithHoles(r) { if (Array.isArray(r)) return r; }









// Import direct des composants (plus de lazy loading)





var PDFCanvasEditor = /*#__PURE__*/(0,react.forwardRef)(function (_ref, ref) {
  var options = _ref.options;
  var _useState = (0,react.useState)('select'),
    _useState2 = PDFCanvasEditor_slicedToArray(_useState, 2),
    tool = _useState2[0],
    setTool = _useState2[1];
  var _useState3 = (0,react.useState)(false),
    _useState4 = PDFCanvasEditor_slicedToArray(_useState3, 2),
    showNewTemplateModal = _useState4[0],
    setShowNewTemplateModal = _useState4[1];
  var _useState5 = (0,react.useState)(false),
    _useState6 = PDFCanvasEditor_slicedToArray(_useState5, 2),
    isPropertiesCollapsed = _useState6[0],
    setIsPropertiesCollapsed = _useState6[1];

  // États pour le pan et la navigation
  var _useState7 = (0,react.useState)({
      x: 0,
      y: 0
    }),
    _useState8 = PDFCanvasEditor_slicedToArray(_useState7, 2),
    panOffset = _useState8[0],
    setPanOffset = _useState8[1];
  var _useState9 = (0,react.useState)(false),
    _useState0 = PDFCanvasEditor_slicedToArray(_useState9, 2),
    isPanning = _useState0[0],
    setIsPanning = _useState0[1];
  var _useState1 = (0,react.useState)({
      x: 0,
      y: 0
    }),
    _useState10 = PDFCanvasEditor_slicedToArray(_useState1, 2),
    lastPanPoint = _useState10[0],
    setLastPanPoint = _useState10[1];

  // États pour les guides
  var _useState11 = (0,react.useState)({
      horizontal: [],
      vertical: []
    }),
    _useState12 = PDFCanvasEditor_slicedToArray(_useState11, 2),
    guides = _useState12[0],
    setGuides = _useState12[1];
  var _useState13 = (0,react.useState)(false),
    _useState14 = PDFCanvasEditor_slicedToArray(_useState13, 2),
    isCreatingGuide = _useState14[0],
    setIsCreatingGuide = _useState14[1];
  var _useState15 = (0,react.useState)(null),
    _useState16 = PDFCanvasEditor_slicedToArray(_useState15, 2),
    guideCreationType = _useState16[0],
    setGuideCreationType = _useState16[1]; // 'horizontal' or 'vertical'

  // Hook pour les paramètres globaux
  var globalSettings = useGlobalSettings();

  // Fonctions pour gérer les guides
  var addHorizontalGuide = (0,react.useCallback)(function (y) {
    if (!globalSettings.settings.lockGuides) {
      setGuides(function (prev) {
        return PDFCanvasEditor_objectSpread(PDFCanvasEditor_objectSpread({}, prev), {}, {
          horizontal: [].concat(PDFCanvasEditor_toConsumableArray(prev.horizontal), [y]).sort(function (a, b) {
            return a - b;
          })
        });
      });
    }
  }, [globalSettings.settings.lockGuides]);
  var addVerticalGuide = (0,react.useCallback)(function (x) {
    if (!globalSettings.settings.lockGuides) {
      setGuides(function (prev) {
        return PDFCanvasEditor_objectSpread(PDFCanvasEditor_objectSpread({}, prev), {}, {
          vertical: [].concat(PDFCanvasEditor_toConsumableArray(prev.vertical), [x]).sort(function (a, b) {
            return a - b;
          })
        });
      });
    }
  }, [globalSettings.settings.lockGuides]);
  var removeGuide = (0,react.useCallback)(function (type, position) {
    if (!globalSettings.settings.lockGuides) {
      setGuides(function (prev) {
        return PDFCanvasEditor_objectSpread(PDFCanvasEditor_objectSpread({}, prev), {}, PDFCanvasEditor_defineProperty({}, type, prev[type].filter(function (pos) {
          return pos !== position;
        })));
      });
    }
  }, [globalSettings.settings.lockGuides]);

  // Données de commande WooCommerce (passées via options ou données de test)
  var orderData = options.orderData || {
    invoice_number: 'INV-001',
    invoice_date: '15/10/2025',
    order_number: '#12345',
    order_date: '15/10/2025',
    customer_name: 'John Doe',
    customer_email: 'john.doe@example.com',
    billing_address: '123 Rue de Test\n75001 Paris\nFrance',
    shipping_address: '456 Rue de Livraison\n75002 Paris\nFrance',
    payment_method: 'Carte bancaire',
    order_status: 'Traitée',
    subtotal: '45,00 €',
    discount: '-5,00 €',
    shipping: '5,00 €',
    tax: '9,00 €',
    total: '54,00 €',
    refund: '0,00 €',
    fees: '1,50 €',
    quote_number: 'QUO-001',
    quote_date: '15/10/2025',
    quote_validity: '30 jours',
    quote_notes: 'Conditions spéciales : paiement à 30 jours.',
    products: [{
      name: 'Produit Test 1',
      quantity: 1,
      price: '25,00 €',
      total: '25,00 €'
    }, {
      name: 'Produit Test 2',
      quantity: 2,
      price: '10,00 €',
      total: '20,00 €'
    }]
  };
  var canvasState = useCanvasState({
    initialElements: options.initialElements || [],
    templateId: options.templateId || null,
    canvasWidth: options.width || 595,
    canvasHeight: options.height || 842,
    globalSettings: globalSettings.settings
  });

  // Hook pour l'historique Undo/Redo - REMOVED: utilise maintenant canvasState.history
  // const history = useHistory({ maxHistorySize: globalSettings.settings.undoLevels || 50 });

  // Fonction wrapper pour les mises à jour avec historique
  var updateElementWithHistory = (0,react.useCallback)(function (elementId, updates) {
    var description = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'Modifier élément';
    // Sauvegarder l'état actuel avant modification
    var currentElements = canvasState.getAllElements();
    canvasState.history.addToHistory(currentElements, description);

    // Appliquer la mise à jour
    canvasState.updateElement(elementId, updates);
  }, [canvasState]);

  // Fonctions Undo/Redo - utilisent directement celles du canvasState
  var handleUndo = (0,react.useCallback)(function () {
    canvasState.undo();
  }, [canvasState]);
  var handleRedo = (0,react.useCallback)(function () {
    canvasState.redo();
  }, [canvasState]);

  // Handler pour créer un nouveau template
  var handleCreateTemplate = (0,react.useCallback)(function (templateData) {
    // TODO: Implémenter la logique de création du template
    // Pour l'instant, on ferme juste la modale et on affiche les données
    // Logging removed for production
    alert("Template \"".concat(templateData.name, "\" cr\xE9\xE9 avec succ\xE8s!\n\nMod\xE8le: ").concat(templateData.defaultModel, "\nFormat: ").concat(templateData.paperFormat, "\nOrientation: ").concat(templateData.orientation, "\nCat\xE9gorie: ").concat(templateData.category));
  }, []);

  // Handlers pour les paramètres de grille
  var handleShowGridChange = (0,react.useCallback)(function (showGrid) {
    globalSettings.updateSettings({
      showGrid: showGrid
    });
  }, [globalSettings]);
  var handleSnapToGridChange = (0,react.useCallback)(function (snapToGrid) {
    globalSettings.updateSettings({
      snapToGrid: snapToGrid
    });
  }, [globalSettings]);
  var editorRef = (0,react.useRef)(null);
  var canvasRef = (0,react.useRef)(null);
  var canvasContainerRef = (0,react.useRef)(null);

  // Exposer les méthodes du composant via ref
  (0,react.useImperativeHandle)(ref, function () {
    return {
      getElements: function getElements() {
        return canvasState.getAllElements();
      },
      getRenderedHtml: function getRenderedHtml() {
        // Cette méthode pourrait être implémentée pour retourner le HTML rendu
        // Logging removed for production
        return '<div>HTML rendering not yet implemented</div>';
      }
    };
  }, [canvasState]);

  // Hook pour le drag and drop
  var dragAndDrop = useDragAndDrop({
    onElementMove: function onElementMove(elementId, position) {
      updateElementWithHistory(elementId, position, 'Déplacer élément');
    },
    onElementDrop: function onElementDrop(elementId, position) {
      updateElementWithHistory(elementId, position, 'Déposer élément');
    },
    snapToGrid: globalSettings.settings.snapToGrid,
    gridSize: globalSettings.settings.gridSize,
    zoom: canvasState.zoom.zoom,
    canvasWidth: canvasState.canvasWidth,
    canvasHeight: canvasState.canvasHeight,
    guides: guides,
    snapToGuides: globalSettings.settings.snapToElements
  });

  // Gestion des raccourcis clavier
  useKeyboardShortcuts({
    onDelete: canvasState.deleteSelectedElements,
    onCopy: canvasState.copySelectedElements,
    onPaste: canvasState.pasteElements,
    onUndo: handleUndo,
    onRedo: handleRedo,
    onSave: canvasState.saveTemplate,
    onZoomIn: canvasState.zoom.zoomIn,
    onZoomOut: canvasState.zoom.zoomOut
  });

  // Gestionnaire pour ajouter un élément depuis la bibliothèque
  var handleAddElement = (0,react.useCallback)(function (elementType) {
    var properties = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
    canvasState.addElement(elementType, properties);
    setTool('select');
  }, [canvasState]);

  // Gestionnaire pour la sélection d'élément
  var handleElementSelect = (0,react.useCallback)(function (elementId, event) {
    var addToSelection = (event === null || event === void 0 ? void 0 : event.ctrlKey) || (event === null || event === void 0 ? void 0 : event.metaKey); // Ctrl ou Cmd pour multi-sélection
    canvasState.selection.selectElement(elementId, addToSelection);
  }, [canvasState.selection]);

  // Gestionnaire pour l'impression
  var handlePrint = (0,react.useCallback)(/*#__PURE__*/PDFCanvasEditor_asyncToGenerator(/*#__PURE__*/PDFCanvasEditor_regenerator().m(function _callee() {
    var _window$pdfBuilderAja, _window$pdfBuilderAja2, elements, jsonString, formData, response, data, _data$data, _t, _t2;
    return PDFCanvasEditor_regenerator().w(function (_context) {
      while (1) switch (_context.p = _context.n) {
        case 0:
          _context.p = 0;
          // Récupérer tous les éléments du canvas
          elements = canvasState.getAllElements();
          if (!(elements.length === 0)) {
            _context.n = 1;
            break;
          }
          alert('Aucun élément à imprimer. Ajoutez des éléments au canvas d\'abord.');
          return _context.a(2);
        case 1:
          _context.p = 1;
          jsonString = JSON.stringify(elements);
          _context.n = 3;
          break;
        case 2:
          _context.p = 2;
          _t = _context.v;
          console.error('❌ Erreur lors de JSON.stringify:', _t);
          console.error('Éléments problématiques:', elements);
          alert('Erreur de sérialisation des éléments. Vérifiez la console pour plus de détails.');
          return _context.a(2);
        case 3:
          // Préparer les données pour l'AJAX
          formData = new FormData();
          formData.append('action', 'pdf_builder_generate_pdf');
          formData.append('nonce', (_window$pdfBuilderAja = window.pdfBuilderAjax) === null || _window$pdfBuilderAja === void 0 ? void 0 : _window$pdfBuilderAja.nonce);
          formData.append('elements', jsonString);

          // Faire l'appel AJAX
          _context.n = 4;
          return fetch((_window$pdfBuilderAja2 = window.pdfBuilderAjax) === null || _window$pdfBuilderAja2 === void 0 ? void 0 : _window$pdfBuilderAja2.ajaxurl, {
            method: 'POST',
            body: formData
          });
        case 4:
          response = _context.v;
          if (response.ok) {
            _context.n = 5;
            break;
          }
          throw new Error("Erreur HTTP: ".concat(response.status));
        case 5:
          _context.n = 6;
          return response.json();
        case 6:
          data = _context.v;
          if (!data.success) {
            _context.n = 7;
            break;
          }
          // PDF généré avec succès
          console.log('PDF généré avec succès');
          _context.n = 8;
          break;
        case 7:
          console.error('Erreur serveur:', data.data);
          throw new Error(((_data$data = data.data) === null || _data$data === void 0 ? void 0 : _data$data.message) || 'Erreur lors de la génération du PDF');
        case 8:
          _context.n = 10;
          break;
        case 9:
          _context.p = 9;
          _t2 = _context.v;
          console.error('Erreur lors de l\'impression:', _t2);
          alert('Erreur lors de la génération du PDF: ' + _t2.message);
        case 10:
          return _context.a(2);
      }
    }, _callee, null, [[1, 2], [0, 9]]);
  })), [canvasState]);

  // Gestionnaire pour la désélection et création d'éléments
  var handleCanvasClick = (0,react.useCallback)(function (e) {
    // Vérifier si c'est un Ctrl+clic pour créer un guide
    if (e.ctrlKey && globalSettings.settings.showGuides && !globalSettings.settings.lockGuides) {
      var canvasRect = e.currentTarget.getBoundingClientRect();
      var clickX = e.clientX - canvasRect.left;
      var clickY = e.clientY - canvasRect.top;

      // Ajuster pour le zoom et le pan
      var adjustedX = (clickX - panOffset.x) / canvasState.zoom.zoom;
      var adjustedY = (clickY - panOffset.y) / canvasState.zoom.zoom;

      // Créer un guide horizontal ou vertical selon la position relative au centre
      var centerX = canvasState.canvasWidth / 2;
      var centerY = canvasState.canvasHeight / 2;
      if (Math.abs(adjustedX - centerX) < Math.abs(adjustedY - centerY)) {
        // Plus proche verticalement, créer guide horizontal
        addHorizontalGuide(Math.round(adjustedY));
      } else {
        // Plus proche horizontalement, créer guide vertical
        addVerticalGuide(Math.round(adjustedX));
      }
      return;
    }

    // Vérifier si le clic vient de la zone vide du canvas (pas d'un élément)
    var clickedElement = e.target.closest('[data-element-id]');
    if (clickedElement) {
      // Si on clique sur un élément, ne rien faire ici (laissé à CanvasElement)
      return;
    }

    // Si un outil d'ajout est sélectionné, créer l'élément
    if (tool.startsWith('add-')) {
      var _canvasRect = e.currentTarget.getBoundingClientRect();
      var _clickX = e.clientX - _canvasRect.left;
      var _clickY = e.clientY - _canvasRect.top;

      // Ajuster pour le zoom
      var _adjustedX = _clickX / canvasState.zoom.zoom;
      var _adjustedY = _clickY / canvasState.zoom.zoom;
      var elementType = 'text';
      var defaultProps = {};

      // Déterminer le type d'élément selon l'outil
      switch (tool) {
        case 'add-text':
          elementType = 'text';
          break;
        case 'add-text-title':
          elementType = 'text';
          defaultProps = {
            fontSize: 24,
            fontWeight: 'bold'
          };
          break;
        case 'add-text-subtitle':
          elementType = 'text';
          defaultProps = {
            fontSize: 18,
            fontWeight: 'bold'
          };
          break;
        case 'add-rectangle':
          elementType = 'rectangle';
          break;
        case 'add-circle':
          elementType = 'shape-circle';
          break;
        case 'add-line':
          elementType = 'line';
          break;
        case 'add-arrow':
          elementType = 'shape-arrow';
          break;
        case 'add-triangle':
          elementType = 'shape-triangle';
          break;
        case 'add-star':
          elementType = 'shape-star';
          break;
        case 'add-divider':
          elementType = 'divider';
          break;
        case 'add-image':
          elementType = 'image';
          break;
        default:
          // Pour les autres outils de la bibliothèque
          if (tool.startsWith('add-')) {
            elementType = tool.replace('add-', '');
          }
          break;
      }
      canvasState.addElement(elementType, PDFCanvasEditor_objectSpread({
        x: Math.max(0, _adjustedX - 50),
        y: Math.max(0, _adjustedY - 25)
      }, defaultProps));

      // Remettre l'outil de sélection après ajout
      setTool('select');
      return;
    }

    // Sinon, désélectionner
    canvasState.selection.clearSelection();
  }, [canvasState, tool]);

  // Gestionnaire pour les changements de propriétés
  var handlePropertyChange = (0,react.useCallback)(function (elementId, property, value) {
    // Récupérer l'élément actuel pour connaître les valeurs existantes
    var currentElement = canvasState.getElementById(elementId);
    if (!currentElement) return;

    // Gérer les propriétés imbriquées (ex: "columns.image" -> { columns: { image: value } })
    var updates = {};
    if (property.includes('.')) {
      // Fonction récursive pour mettre à jour les propriétés imbriquées
      // en préservant toutes les valeurs existantes
      var updateNestedProperty = function updateNestedProperty(existingObj, path, val) {
        var keys = path.split('.');
        var lastKey = keys.pop();

        // Commencer avec une copie complète de l'objet existant
        var result = PDFCanvasEditor_objectSpread({}, existingObj);
        var current = result;

        // Naviguer jusqu'à l'avant-dernier niveau en préservant les objets existants
        for (var i = 0; i < keys.length - 1; i++) {
          var key = keys[i];
          if (!current[key] || PDFCanvasEditor_typeof(current[key]) !== 'object') {
            current[key] = {};
          } else {
            current[key] = PDFCanvasEditor_objectSpread({}, current[key]);
          }
          current = current[key];
        }

        // Pour le dernier niveau (avant la propriété finale)
        var parentKey = keys[keys.length - 1];
        if (parentKey) {
          if (!current[parentKey] || PDFCanvasEditor_typeof(current[parentKey]) !== 'object') {
            current[parentKey] = {};
          } else {
            current[parentKey] = PDFCanvasEditor_objectSpread({}, current[parentKey]);
          }
          current[parentKey][lastKey] = val;
        } else {
          // Propriété directement sur l'objet racine
          current[lastKey] = val;
        }
        return result;
      };

      // Créer l'update en préservant toutes les propriétés existantes
      var fullUpdate = updateNestedProperty(currentElement, property, value);
      Object.assign(updates, fullUpdate);
    } else {
      updates[property] = value;
    }
    updateElementWithHistory(elementId, updates, "Modifier ".concat(property));
  }, [canvasState, updateElementWithHistory]);

  // Gestionnaire pour les mises à jour par lot
  var handleBatchUpdate = (0,react.useCallback)(function (updates) {
    updates.forEach(function (_ref3) {
      var elementId = _ref3.elementId,
        property = _ref3.property,
        value = _ref3.value;
      canvasState.updateElement(elementId, PDFCanvasEditor_defineProperty({}, property, value));
    });
  }, [canvasState]);

  // Gestionnaire du menu contextuel
  var handleContextMenu = (0,react.useCallback)(function (e) {
    var elementId = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
    e.preventDefault();
    var menuItems = [];
    if (elementId) {
      // Menu contextuel pour un élément spécifique
      var element = canvasState.getElementById(elementId);
      if (element) {
        menuItems.push({
          label: 'Copier',
          action: function action() {
            return canvasState.copySelectedElements();
          }
        }, {
          label: 'Dupliquer',
          action: function action() {
            return canvasState.duplicateElement(elementId);
          }
        }, {
          type: 'separator'
        }, {
          label: 'Supprimer',
          action: function action() {
            return canvasState.deleteElement(elementId);
          }
        });
      }
    } else {
      // Menu contextuel pour le canvas vide
      var hasSelection = canvasState.selection.selectedElements.length > 0;
      if (hasSelection) {
        menuItems.push({
          label: 'Copier',
          action: function action() {
            return canvasState.copySelectedElements();
          }
        }, {
          label: 'Dupliquer',
          action: function action() {
            return canvasState.duplicateSelectedElements();
          }
        }, {
          type: 'separator'
        }, {
          label: 'Supprimer',
          action: function action() {
            return canvasState.deleteSelectedElements();
          }
        });
      }
      menuItems.push({
        type: 'separator'
      }, {
        label: 'Coller',
        action: function action() {
          return canvasState.pasteElements();
        }
      }, {
        type: 'separator'
      }, {
        label: 'Tout sélectionner',
        action: function action() {
          return canvasState.selectAll();
        }
      }, {
        label: 'Désélectionner',
        action: function action() {
          return canvasState.selection.clearSelection();
        }
      });
    }
    canvasState.showContextMenu(e.clientX, e.clientY, menuItems);
  }, [canvasState]);

  // Gestionnaire pour les actions du menu contextuel
  var handleContextMenuAction = (0,react.useCallback)(function (action) {
    if (typeof action === 'function') {
      action();
    }
  }, []);

  // Fonction pour déterminer le curseur selon l'outil sélectionné
  var getCursorStyle = (0,react.useCallback)(function () {
    if (isPanning) return 'grabbing';
    switch (tool) {
      case 'select':
        return 'default';
      case 'add-text':
      case 'add-text-title':
      case 'add-text-subtitle':
        return 'text';
      case 'add-rectangle':
      case 'add-circle':
      case 'add-line':
      case 'add-arrow':
      case 'add-triangle':
      case 'add-star':
      case 'add-divider':
      case 'add-image':
        return 'crosshair';
      default:
        return 'default';
    }
  }, [tool, isPanning]);

  // Gestionnaire pour le drag over
  var handleDragOver = (0,react.useCallback)(function (e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
  }, []);

  // Gestionnaire pour le drop
  var handleDrop = (0,react.useCallback)(function (e) {
    e.preventDefault();
    try {
      var jsonData = e.dataTransfer.getData('application/json');

      // Vérifier si les données existent et ne sont pas vides
      if (!jsonData || jsonData.trim() === '') {
        // C'est probablement un drop normal (image, fichier, etc.) - ignorer silencieusement
        return;
      }
      var data = JSON.parse(jsonData);
      if (data.type === 'new-element') {
        var canvasRect = e.currentTarget.getBoundingClientRect();
        var dropX = e.clientX - canvasRect.left;
        var dropY = e.clientY - canvasRect.top;

        // Ajuster pour le zoom
        var adjustedX = dropX / canvasState.zoom.zoom;
        var adjustedY = dropY / canvasState.zoom.zoom;
        canvasState.addElement(data.elementType, PDFCanvasEditor_objectSpread({
          x: Math.max(0, adjustedX - 50),
          // Centrer l'élément sur le point de drop
          y: Math.max(0, adjustedY - 25)
        }, data.defaultProps));
      }
    } catch (error) {
      // Ne logger que les vraies erreurs (pas les drops normaux)
      if (error instanceof SyntaxError && e.dataTransfer.getData('application/json')) {
        console.error('Erreur lors du parsing des données de drop:', error);
      }
      // Pour les autres types de drop (fichiers, images, etc.), ignorer silencieusement
    }
  }, [canvasState]);

  // Gestionnaire pour le zoom avec la molette
  var handleWheel = (0,react.useCallback)(function (e) {
    if (!globalSettings.settings.zoomWithWheel) return;
    e.preventDefault();

    // Calculer le facteur de zoom basé sur les paramètres globaux
    var zoomFactor = 1 + globalSettings.settings.zoomStep / 100;

    // Déterminer si on zoome ou dézoome
    var delta = e.deltaY > 0 ? -1 : 1;

    // Calculer les coordonnées de la souris relatives au conteneur
    var container = canvasContainerRef.current;
    if (!container) return;
    var rect = container.getBoundingClientRect();
    var mouseX = e.clientX - rect.left;
    var mouseY = e.clientY - rect.top;

    // Appliquer le zoom vers le point de la souris
    var finalZoomFactor = delta > 0 ? zoomFactor : 1 / zoomFactor;
    canvasState.zoom.zoomToPoint(mouseX, mouseY, finalZoomFactor);
  }, [globalSettings.settings.zoomWithWheel, globalSettings.settings.zoomStep, canvasState.zoom]);

  // Attacher le gestionnaire de roue de manière non-passive pour permettre preventDefault
  (0,react.useEffect)(function () {
    var container = canvasContainerRef.current;
    if (!container || !globalSettings.settings.zoomWithWheel) return;
    var handleWheelEvent = function handleWheelEvent(e) {
      handleWheel(e);
    };
    container.addEventListener('wheel', handleWheelEvent, {
      passive: false
    });
    return function () {
      container.removeEventListener('wheel', handleWheelEvent);
    };
  }, [handleWheel, globalSettings.settings.zoomWithWheel]);

  // Gestionnaire pour le pan avec la souris (clic milieu ou espace + drag)
  var handleMouseDown = (0,react.useCallback)(function (e) {
    if (!globalSettings.settings.panWithMouse) return;

    // Pan avec le bouton du milieu ou espace + clic gauche
    if (e.button === 1 || e.button === 0 && e.altKey) {
      e.preventDefault();
      setIsPanning(true);
      setLastPanPoint({
        x: e.clientX,
        y: e.clientY
      });
    }
  }, [globalSettings.settings.panWithMouse]);
  var handleMouseMove = (0,react.useCallback)(function (e) {
    if (!isPanning) return;
    var deltaX = e.clientX - lastPanPoint.x;
    var deltaY = e.clientY - lastPanPoint.y;
    setPanOffset(function (prev) {
      return {
        x: prev.x + deltaX,
        y: prev.y + deltaY
      };
    });
    setLastPanPoint({
      x: e.clientX,
      y: e.clientY
    });
  }, [isPanning, lastPanPoint]);
  var handleMouseUp = (0,react.useCallback)(function () {
    setIsPanning(false);
  }, []);

  // Gestionnaire pour double-clic
  var handleDoubleClick = (0,react.useCallback)(function (e) {
    if (!globalSettings.settings.zoomToSelection) return;

    // Vérifier qu'il n'y a pas d'élément cliqué (double-clic sur le fond)
    var clickedElement = e.target.closest('[data-element-id]');
    if (clickedElement) return;

    // Si des éléments sont sélectionnés, zoomer dessus
    if (canvasState.selection.selectedElements.length > 0) {
      canvasState.zoomToSelection();
    }
  }, [globalSettings.settings.zoomToSelection, canvasState]);
  return /*#__PURE__*/react.createElement("div", {
    className: "pdf-canvas-editor",
    ref: editorRef
  }, /*#__PURE__*/react.createElement("header", {
    className: "editor-header"
  }, /*#__PURE__*/react.createElement("h2", null, "\xC9diteur PDF - ", options.isNew ? 'Nouveau Template' : options.templateName), /*#__PURE__*/react.createElement("nav", {
    className: "editor-actions"
  }, /*#__PURE__*/react.createElement("button", {
    className: "btn btn-outline",
    onClick: function onClick() {
      return setShowNewTemplateModal(true);
    },
    title: "Cr\xE9er un nouveau template"
  }, "\u2795 Nouveau template"), /*#__PURE__*/react.createElement("button", {
    className: "btn btn-primary",
    onClick: function onClick() {
      return canvasState.saveTemplate();
    },
    disabled: canvasState.isSaving
  }, canvasState.isSaving ? '⏳ Sauvegarde...' : options.isNew ? '💾 Sauvegarder' : '✏️ Modifier'))), /*#__PURE__*/react.createElement(Toolbar, {
    selectedTool: tool,
    onToolSelect: setTool,
    zoom: canvasState.zoom.zoom,
    onZoomChange: canvasState.zoom.setZoomLevel,
    showGrid: globalSettings.settings.showGrid,
    onShowGridChange: handleShowGridChange,
    snapToGrid: globalSettings.settings.snapToGrid,
    onSnapToGridChange: handleSnapToGridChange,
    onUndo: handleUndo,
    onRedo: handleRedo,
    canUndo: canvasState.history.canUndo(),
    canRedo: canvasState.history.canRedo()
  }), /*#__PURE__*/react.createElement("main", {
    className: "editor-workspace"
  }, /*#__PURE__*/react.createElement("aside", {
    className: "editor-sidebar left-sidebar"
  }, /*#__PURE__*/react.createElement(components_ElementLibrary, {
    onAddElement: handleAddElement,
    selectedTool: tool,
    onToolSelect: setTool
  })), /*#__PURE__*/react.createElement("section", {
    className: "canvas-section",
    ref: canvasContainerRef,
    onContextMenu: handleContextMenu,
    onDragOver: handleDragOver,
    onDrop: handleDrop,
    onMouseDown: handleMouseDown,
    onMouseMove: handleMouseMove,
    onMouseUp: handleMouseUp,
    onMouseLeave: handleMouseUp,
    onDoubleClick: handleDoubleClick,
    style: {
      cursor: getCursorStyle(),
      backgroundColor: globalSettings.settings.containerShowTransparency ? 'transparent' : globalSettings.settings.containerBackgroundColor || '#f8f9fa',
      backgroundImage: globalSettings.settings.containerShowTransparency ? "linear-gradient(45deg, #f0f0f0 25%, transparent 25%), linear-gradient(-45deg, #f0f0f0 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f0f0f0 75%), linear-gradient(-45deg, transparent 75%, #f0f0f0 75%)" : 'none',
      backgroundSize: globalSettings.settings.containerShowTransparency ? '20px 20px' : 'auto',
      backgroundPosition: globalSettings.settings.containerShowTransparency ? '0 0, 0 10px, 10px -10px, -10px 0px' : '0 0'
    }
  }, /*#__PURE__*/react.createElement("div", {
    className: "canvas-zoom-wrapper",
    style: {
      transform: "translate(".concat(panOffset.x, "px, ").concat(panOffset.y, "px) scale(").concat(canvasState.zoom.zoom, ")"),
      transformOrigin: 'center',
      cursor: isPanning ? 'grabbing' : 'default',
      transition: globalSettings.settings.smoothZoom ? 'transform 0.2s ease-out' : 'none',
      willChange: globalSettings.settings.enableHardwareAcceleration ? 'transform' : 'auto'
    }
  }, /*#__PURE__*/react.createElement("div", {
    className: "canvas",
    ref: canvasRef,
    onClick: handleCanvasClick,
    style: {
      width: canvasState.canvasWidth,
      height: 'auto',
      // Laisser le CSS contrôler la hauteur pour s'adapter au conteneur 130vh
      minHeight: canvasState.canvasHeight,
      // Hauteur minimale pour éviter la compression excessive
      position: 'relative',
      backgroundColor: globalSettings.settings.canvasShowTransparency ? 'transparent' : globalSettings.settings.canvasBackgroundColor || '#ffffff',
      backgroundImage: globalSettings.settings.canvasShowTransparency ? "linear-gradient(45deg, #f0f0f0 25%, transparent 25%), linear-gradient(-45deg, #f0f0f0 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f0f0f0 75%), linear-gradient(-45deg, transparent 75%, #f0f0f0 75%)" : 'none',
      backgroundSize: globalSettings.settings.canvasShowTransparency ? '20px 20px' : 'auto',
      backgroundPosition: globalSettings.settings.canvasShowTransparency ? '0 0, 0 10px, 10px -10px, -10px 0px' : '0 0'
    }
  }, globalSettings.settings.showGrid && /*#__PURE__*/react.createElement("div", {
    className: "canvas-grid",
    style: {
      position: 'absolute',
      top: 0,
      left: 0,
      width: '100%',
      height: '100%',
      backgroundImage: "\n                      linear-gradient(to right, ".concat(globalSettings.settings.gridColor).concat(Math.round(globalSettings.settings.gridOpacity * 2.55).toString(16).padStart(2, '0'), " 1px, transparent 1px),\n                      linear-gradient(to bottom, ").concat(globalSettings.settings.gridColor).concat(Math.round(globalSettings.settings.gridOpacity * 2.55).toString(16).padStart(2, '0'), " 1px, transparent 1px)\n                    "),
      backgroundSize: "".concat(globalSettings.settings.gridSize, "px ").concat(globalSettings.settings.gridSize, "px"),
      pointerEvents: 'none',
      zIndex: 1
    }
  }), globalSettings.settings.showGuides && /*#__PURE__*/react.createElement("div", {
    className: "canvas-guides"
  }, guides.horizontal.map(function (y, index) {
    return /*#__PURE__*/react.createElement("div", {
      key: "h-guide-".concat(index),
      className: "canvas-guide horizontal-guide",
      onClick: function onClick(e) {
        e.stopPropagation();
        if (!globalSettings.settings.lockGuides) {
          removeGuide('horizontal', y);
        }
      },
      style: {
        position: 'absolute',
        top: "".concat(y, "px"),
        left: 0,
        width: '100%',
        height: '2px',
        backgroundColor: '#007cba',
        cursor: globalSettings.settings.lockGuides ? 'default' : 'pointer',
        zIndex: 2,
        opacity: 0.7
      },
      title: "Guide horizontal \xE0 ".concat(y, "px - ").concat(globalSettings.settings.lockGuides ? 'Verrouillé' : 'Cliquer pour supprimer')
    });
  }), guides.vertical.map(function (x, index) {
    return /*#__PURE__*/react.createElement("div", {
      key: "v-guide-".concat(index),
      className: "canvas-guide vertical-guide",
      onClick: function onClick(e) {
        e.stopPropagation();
        if (!globalSettings.settings.lockGuides) {
          removeGuide('vertical', x);
        }
      },
      style: {
        position: 'absolute',
        top: 0,
        left: "".concat(x, "px"),
        height: '100%',
        width: '2px',
        backgroundColor: '#007cba',
        cursor: globalSettings.settings.lockGuides ? 'default' : 'pointer',
        zIndex: 2,
        opacity: 0.7
      },
      title: "Guide vertical \xE0 ".concat(x, "px - ").concat(globalSettings.settings.lockGuides ? 'Verrouillé' : 'Cliquer pour supprimer')
    });
  })), canvasState.elements.filter(function (el) {
    return !el.type.startsWith('woocommerce-');
  }).map(function (element) {
    return /*#__PURE__*/react.createElement(CanvasElement, {
      key: element.id,
      element: element,
      isSelected: canvasState.selection.selectedElements.includes(element.id),
      zoom: 1,
      snapToGrid: globalSettings.settings.snapToGrid,
      gridSize: globalSettings.settings.gridSize,
      canvasWidth: canvasState.canvasWidth,
      canvasHeight: canvasState.canvasHeight,
      onSelect: function onSelect() {
        return handleElementSelect(element.id);
      },
      onUpdate: function onUpdate(updates) {
        return canvasState.updateElement(element.id, updates);
      },
      onRemove: function onRemove() {
        return canvasState.deleteElement(element.id);
      },
      onContextMenu: function onContextMenu(e) {
        return handleContextMenu(e, element.id);
      },
      dragAndDrop: dragAndDrop,
      enableRotation: globalSettings.settings.enableRotation,
      rotationStep: globalSettings.settings.rotationStep,
      rotationSnap: globalSettings.settings.rotationSnap,
      guides: guides,
      snapToGuides: globalSettings.settings.snapToElements
    });
  }), canvasState.elements.filter(function (el) {
    return el.type.startsWith('woocommerce-');
  }).map(function (element) {
    return /*#__PURE__*/react.createElement(WooCommerceElements, {
      key: element.id,
      element: element,
      isSelected: canvasState.selection.selectedElements.includes(element.id),
      onSelect: handleElementSelect,
      onUpdate: canvasState.updateElement,
      dragAndDrop: dragAndDrop,
      zoom: 1,
      canvasWidth: canvasState.canvasWidth,
      canvasHeight: canvasState.canvasHeight,
      orderData: orderData,
      onContextMenu: function onContextMenu(e) {
        return handleContextMenu(e, element.id);
      },
      snapToGrid: globalSettings.settings.snapToGrid,
      gridSize: globalSettings.settings.gridSize,
      guides: guides,
      snapToGuides: globalSettings.settings.snapToElements
    });
  })))), /*#__PURE__*/react.createElement("aside", {
    className: "editor-sidebar right-sidebar ".concat(isPropertiesCollapsed ? 'collapsed' : '')
  }, !isPropertiesCollapsed && /*#__PURE__*/react.createElement(components_PropertiesPanel, {
    selectedElements: canvasState.selection.selectedElements,
    elements: canvasState.elements,
    onPropertyChange: handlePropertyChange,
    onBatchUpdate: handleBatchUpdate
  }))), /*#__PURE__*/react.createElement("button", {
    className: "sidebar-toggle-fixed",
    onClick: function onClick() {
      return setIsPropertiesCollapsed(!isPropertiesCollapsed);
    },
    title: isPropertiesCollapsed ? 'Agrandir le panneau' : 'Réduire le panneau',
    style: {
      position: 'fixed',
      top: '50%',
      right: isPropertiesCollapsed ? '0px' : '350px',
      transform: 'translateY(-50%)',
      zIndex: 999999
    }
  }, isPropertiesCollapsed ? '◀' : '▶'), canvasState.contextMenu.contextMenu && /*#__PURE__*/react.createElement(components_ContextMenu, {
    menu: canvasState.contextMenu.contextMenu,
    onAction: handleContextMenuAction,
    isAnimating: canvasState.contextMenu.isAnimating || false,
    onClose: canvasState.contextMenu.hideContextMenu
  }), /*#__PURE__*/react.createElement("footer", {
    className: "editor-status"
  }, /*#__PURE__*/react.createElement("span", null, "\xC9l\xE9ments: ", canvasState.elements.length), /*#__PURE__*/react.createElement("span", null, "|"), globalSettings.settings.showZoomIndicator && /*#__PURE__*/react.createElement(react.Fragment, null, /*#__PURE__*/react.createElement("span", null, "Zoom: ", Math.round(canvasState.zoom.zoom * 100), "%"), /*#__PURE__*/react.createElement("span", null, "|")), /*#__PURE__*/react.createElement("span", null, "Outil: ", tool), canvasState.selection.selectedElements.length > 0 && /*#__PURE__*/react.createElement(react.Fragment, null, /*#__PURE__*/react.createElement("span", null, "|"), /*#__PURE__*/react.createElement("span", null, "\xC9l\xE9ments s\xE9lectionn\xE9s: ", canvasState.selection.selectedElements.length))), /*#__PURE__*/react.createElement(components_NewTemplateModal, {
    isOpen: showNewTemplateModal,
    onClose: function onClose() {
      return setShowNewTemplateModal(false);
    },
    onCreateTemplate: handleCreateTemplate
  }), /*#__PURE__*/react.createElement(FPSCounter, {
    showFps: globalSettings.settings.showFps
  }));
});

// Optimisation : éviter les re-renders inutiles
/* harmony default export */ const components_PDFCanvasEditor = (/*#__PURE__*/react.memo(/*#__PURE__*/(0,react.forwardRef)(PDFCanvasEditor)));
;// ./resources/js/hooks/index.js










;// ./resources/js/index.js
function js_typeof(o) { "@babel/helpers - typeof"; return js_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, js_typeof(o); }
function js_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function js_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? js_ownKeys(Object(t), !0).forEach(function (r) { js_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : js_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function js_defineProperty(e, r, t) { return (r = js_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function js_classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function js_defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, js_toPropertyKey(o.key), o); } }
function js_createClass(e, r, t) { return r && js_defineProperties(e.prototype, r), t && js_defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function js_toPropertyKey(t) { var i = js_toPrimitive(t, "string"); return "symbol" == js_typeof(i) ? i : i + ""; }
function js_toPrimitive(t, r) { if ("object" != js_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != js_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
// Tous les imports doivent être au niveau supérieur du module



// import './react-global'; // REMOVED - on expose directement ici


// FORCER L'EXPOSITION GLOBALE DE REACT ICI
if (typeof window !== 'undefined') {
  window.React = react;
  window.ReactDOM = react_dom;
}

// Forcer l'inclusion de tous les hooks personnalisés


// Système de protection et monitoring - SIMPLIFIÉ
var PDFBuilderSecurity = {
  errors: [],
  initialized: false,
  // Log sécurisé des erreurs
  logError: function logError(error) {
    var _navigator, _window;
    var context = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
    var errorInfo = {
      message: error.message,
      stack: error.stack,
      context: context,
      timestamp: new Date().toISOString(),
      userAgent: (_navigator = navigator) === null || _navigator === void 0 ? void 0 : _navigator.userAgent,
      url: (_window = window) === null || _window === void 0 || (_window = _window.location) === null || _window === void 0 ? void 0 : _window.href
    };
    this.errors.push(errorInfo);
  },
  // Protection contre les appels multiples - améliorée
  preventMultipleInit: function preventMultipleInit() {
    var now = Date.now();
    var lastInit = window._pdfBuilderLastInit || 0;
    var timeSinceLastInit = now - lastInit;

    // Si plus de 5 secondes se sont écoulées depuis la dernière initialisation,
    // permettre une réinitialisation (utile pour les rechargements de page)
    if (window._pdfBuilderInitialized && timeSinceLastInit < 5000) {
      // Log silencieux au lieu d'un avertissement intrusif
      return false;
    }
    window._pdfBuilderInitialized = true;
    window._pdfBuilderLastInit = now;
    return true;
  }
};

// Test des imports de base avec protection
try {
  // Exposer React globalement pour compatibilité - FORCER L'INCLUSION
  if (typeof window !== 'undefined') {
    window.React = react;
    window.ReactDOM = react_dom;
    // Forcer l'utilisation pour éviter l'optimisation webpack
    window._forceReactInclusion = {
      React: react,
      ReactDOM: react_dom,
      createElement: react.createElement
    };
  }
} catch (error) {
  PDFBuilderSecurity.logError(error, 'React initialization');
}

// Classe principale pour l'éditeur PDF
var PDFBuilderPro = /*#__PURE__*/function () {
  function PDFBuilderPro() {
    js_classCallCheck(this, PDFBuilderPro);
    this.version = '2.0.0';
    this.editors = new Map();

    // Forcer l'inclusion des hooks (ne pas supprimer cette ligne)
    this._hooks = hooks_namespaceObject;

    // Références explicites pour forcer l'inclusion
    this._forceInclude = {
      useHistory: useHistory,
      useRotation: useRotation,
      useResize: useResize
    };

    // Forcer l'appel des hooks pour éviter le tree shaking
    try {
      var dummyHistory = useHistory();
      var dummyRotation = useRotation(function () {});
      var dummyResize = useResize();
      this._dummyInstances = {
        dummyHistory: dummyHistory,
        dummyRotation: dummyRotation,
        dummyResize: dummyResize
      };
    } catch (e) {
      // Ignorer les erreurs en mode SSR
    }

    // Assigner explicitement les méthodes comme propriétés propres de l'instance
  }

  // Initialiser l'éditeur dans un conteneur
  return js_createClass(PDFBuilderPro, [{
    key: "init",
    value: function init(containerId) {
      var _this = this;
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      try {
        // Vérification stricte du containerId
        if (!containerId || typeof containerId !== 'string') {
          throw new Error('ContainerId must be a non-empty string');
        }
        var container = document.getElementById(containerId);
        if (!container) {
          throw new Error("Container with ID \"".concat(containerId, "\" does not exist in the DOM"));
        }

        // Vérifier la disponibilité de React et ReactDOM
        if (!react || !react_dom) {
          throw new Error('React or ReactDOM is not available. Make sure the scripts are loaded properly.');
        }

        // Vérifier que PDFCanvasEditor est disponible
        if (!PDFCanvasEditor) {
          throw new Error('PDFCanvasEditor component is not available. Check for compilation errors.');
        }

        // Options par défaut avec validation
        var defaultOptions = js_objectSpread({
          templateId: null,
          templateName: null,
          isNew: true,
          initialElements: [],
          width: 595,
          // A4 width in points
          height: 842,
          // A4 height in points
          zoom: 1,
          gridSize: 10,
          snapToGrid: true
        }, options);

        // Validation des options critiques
        if (typeof defaultOptions.width !== 'number' || defaultOptions.width <= 0) {
          defaultOptions.width = 595;
        }
        if (typeof defaultOptions.height !== 'number' || defaultOptions.height <= 0) {
          defaultOptions.height = 842;
        }

        // Créer l'éditeur React avec protection
        var editorElement = /*#__PURE__*/(0,react.createElement)(PDFCanvasEditor, {
          options: defaultOptions,
          ref: function ref(_ref) {
            // Stocker la référence du composant
            _this.canvas = _ref;
          }
        });

        // Vérifier que l'élément a été créé correctement
        if (!editorElement) {
          throw new Error('Failed to create React element for PDFCanvasEditor');
        }
        react_dom.render(editorElement, container);
        this.editors.set(containerId, {
          container: container,
          options: defaultOptions
        });
      } catch (error) {
        // Fallback visuel pour l'utilisateur
        var _container = document.getElementById(containerId);
        if (_container) {
          _container.innerHTML = "\n                    <div style=\"\n                        color: #721c24;\n                        background-color: #f8d7da;\n                        border: 1px solid #f5c6cb;\n                        border-radius: 4px;\n                        padding: 15px;\n                        margin: 10px 0;\n                        font-family: Arial, sans-serif;\n                        font-size: 14px;\n                    \">\n                        <strong>Erreur PDF Builder Pro</strong><br>\n                        Impossible d'initialiser l'\xE9diteur. V\xE9rifiez la console pour plus de d\xE9tails.<br>\n                        <small>Erreur: ".concat(error.message, "</small>\n                    </div>\n                ");
        }

        // Re-throw pour permettre la gestion en amont si nécessaire
        throw error;
      }
    }

    // Détruire un éditeur
  }, {
    key: "destroy",
    value: function destroy(containerId) {
      try {
        var editor = this.editors.get(containerId);
        if (editor) {
          // Vérifier que ReactDOM est disponible avant de démonter
          if (react_dom && react_dom.unmountComponentAtNode) {
            react_dom.unmountComponentAtNode(editor.container);
          }
          this.editors["delete"](containerId);
        }
      } catch (error) {
        // Forcer la suppression même en cas d'erreur
        this.editors["delete"](containerId);
      }
    }

    // Obtenir les données d'un éditeur
  }, {
    key: "getData",
    value: function getData(containerId) {
      // Cette méthode pourrait être étendue pour récupérer l'état actuel
      return null;
    }

    // Obtenir les éléments du canvas actif
  }, {
    key: "getElements",
    value: function getElements() {
      try {
        if (this.canvas && typeof this.canvas.getElements === 'function') {
          return this.canvas.getElements();
        }
        return [];
      } catch (error) {
        return [];
      }
    }
  }]);
}(); // Instance globale
var pdfBuilderPro = new PDFBuilderPro();

// Export par défaut pour webpack
/* harmony default export */ const js = (pdfBuilderPro);

// Attacher à window pour WordPress - FORCER L'EXPOSITION DIRECTE
try {
  if (typeof window !== 'undefined') {
    // Forcer l'assignation directe de l'instance, pas du module webpack
    window.PDFBuilderPro = pdfBuilderPro;
    window.pdfBuilderPro = pdfBuilderPro;
  } else {}
} catch (error) {}
// Marquer comme initialisé pour éviter les conflits
PDFBuilderSecurity.preventMultipleInit();

// Attacher à window pour WordPress - simplifié
window.PDFBuilderPro = pdfBuilderPro;
// Alias pour compatibilité
window.pdfBuilderPro = pdfBuilderPro;

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
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
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
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			967: 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
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
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunkPDFBuilderPro"] = self["webpackChunkPDFBuilderPro"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, [96], () => (__webpack_require__(626)))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	window.PDFBuilderPro = __webpack_exports__["default"];
/******/ 	
/******/ })()
;