/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ 320:
/***/ ((__unused_webpack_module, __unused_webpack___webpack_exports__, __webpack_require__) => {


// UNUSED EXPORTS: default

// EXTERNAL MODULE: ./node_modules/react/index.js
var react = __webpack_require__(540);
// EXTERNAL MODULE: ./node_modules/react-dom/index.js
var react_dom = __webpack_require__(961);
// EXTERNAL MODULE: ./src/hooks/useResize.js
var useResize = __webpack_require__(630);
// EXTERNAL MODULE: ./src/hooks/useRotation.js
var useRotation = __webpack_require__(920);
;// ./src/components/CanvasElement.jsx
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }



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

  // DEBUG: Logger les positions des éléments dans l'éditeur
  (0,react.useEffect)(function () {
    if (element && element.id) {
      var displayX = element.x * zoom;
      var displayY = element.y * zoom;
      var displayWidth = element.width * zoom;
      var displayHeight = element.height * zoom;

      // Log supprimé pour la production
    }
  }, [element.x, element.y, element.width, element.height, zoom, element.id, element.type]);
  var resize = (0,useResize/* useResize */.s)({
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
  var rotation = (0,useRotation/* useRotation */.o)(function (newRotation) {
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
    return _objectSpread({
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
        headerBg: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        headerBorder: '#5b21b6',
        rowBorder: '#e9d5ff',
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
      blue_ocean: {
        headerBg: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
        headerBorder: '#1e40af',
        rowBorder: '#dbeafe',
        altRowBg: '#eff6ff',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#1e3a8a',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(59, 130, 246, 0.3)',
        borderRadius: '6px'
      },
      emerald_forest: {
        headerBg: 'linear-gradient(135deg, #064e3b 0%, #10b981 100%)',
        headerBorder: '#065f46',
        rowBorder: '#d1fae5',
        altRowBg: '#ecfdf5',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#064e3b',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(16, 185, 129, 0.3)',
        borderRadius: '6px'
      },
      sunset_orange: {
        headerBg: 'linear-gradient(135deg, #9a3412 0%, #f97316 100%)',
        headerBorder: '#c2410c',
        rowBorder: '#fed7aa',
        altRowBg: '#fff7ed',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#9a3412',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(249, 115, 22, 0.3)',
        borderRadius: '6px'
      },
      royal_purple: {
        headerBg: 'linear-gradient(135deg, #581c87 0%, #a855f7 100%)',
        headerBorder: '#7c3aed',
        rowBorder: '#e9d5ff',
        altRowBg: '#faf5ff',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#581c87',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(168, 85, 247, 0.3)',
        borderRadius: '6px'
      },
      rose_pink: {
        headerBg: 'linear-gradient(135deg, #be185d 0%, #f472b6 100%)',
        headerBorder: '#db2777',
        rowBorder: '#fce7f3',
        altRowBg: '#fdf2f8',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#be185d',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(244, 114, 182, 0.3)',
        borderRadius: '6px'
      },
      teal_aqua: {
        headerBg: 'linear-gradient(135deg, #0f766e 0%, #14b8a6 100%)',
        headerBorder: '#0d9488',
        rowBorder: '#ccfbf1',
        altRowBg: '#f0fdfa',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#0f766e',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(20, 184, 166, 0.3)',
        borderRadius: '6px'
      },
      crimson_red: {
        headerBg: 'linear-gradient(135deg, #991b1b 0%, #ef4444 100%)',
        headerBorder: '#dc2626',
        rowBorder: '#fecaca',
        altRowBg: '#fef2f2',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#991b1b',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(239, 68, 68, 0.3)',
        borderRadius: '6px'
      },
      amber_gold: {
        headerBg: 'linear-gradient(135deg, #92400e 0%, #f59e0b 100%)',
        headerBorder: '#d97706',
        rowBorder: '#fef3c7',
        altRowBg: '#fffbeb',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#92400e',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(245, 158, 11, 0.3)',
        borderRadius: '6px'
      },
      indigo_night: {
        headerBg: 'linear-gradient(135deg, #312e81 0%, #6366f1 100%)',
        headerBorder: '#4338ca',
        rowBorder: '#e0e7ff',
        altRowBg: '#eef2ff',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#312e81',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(99, 102, 241, 0.3)',
        borderRadius: '6px'
      },
      slate_gray: {
        headerBg: 'linear-gradient(135deg, #374151 0%, #6b7280 100%)',
        headerBorder: '#4b5563',
        rowBorder: '#f3f4f6',
        altRowBg: '#f9fafb',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#374151',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(107, 114, 128, 0.3)',
        borderRadius: '6px'
      },
      coral_sunset: {
        headerBg: 'linear-gradient(135deg, #c2410c 0%, #fb7185 100%)',
        headerBorder: '#ea580c',
        rowBorder: '#fed7d7',
        altRowBg: '#fef7f7',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#c2410c',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(251, 113, 133, 0.3)',
        borderRadius: '6px'
      },
      mint_green: {
        headerBg: 'linear-gradient(135deg, #065f46 0%, #34d399 100%)',
        headerBorder: '#047857',
        rowBorder: '#d1fae5',
        altRowBg: '#ecfdf5',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#065f46',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(52, 211, 153, 0.3)',
        borderRadius: '6px'
      },
      violet_dream: {
        headerBg: 'linear-gradient(135deg, #6d28d9 0%, #c084fc 100%)',
        headerBorder: '#8b5cf6',
        rowBorder: '#ede9fe',
        altRowBg: '#f5f3ff',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#6d28d9',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(192, 132, 252, 0.3)',
        borderRadius: '6px'
      },
      sky_blue: {
        headerBg: 'linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%)',
        headerBorder: '#0284c7',
        rowBorder: '#bae6fd',
        altRowBg: '#f0f9ff',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#0369a1',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(14, 165, 233, 0.3)',
        borderRadius: '6px'
      },
      forest_green: {
        headerBg: 'linear-gradient(135deg, #14532d 0%, #22c55e 100%)',
        headerBorder: '#15803d',
        rowBorder: '#bbf7d0',
        altRowBg: '#f0fdf4',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#14532d',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(34, 197, 94, 0.3)',
        borderRadius: '6px'
      },
      ruby_red: {
        headerBg: 'linear-gradient(135deg, #b91c1b 0%, #f87171 100%)',
        headerBorder: '#dc2626',
        rowBorder: '#fecaca',
        altRowBg: '#fef2f2',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#b91c1b',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(248, 113, 113, 0.3)',
        borderRadius: '6px'
      },
      golden_yellow: {
        headerBg: 'linear-gradient(135deg, #a16207 0%, #eab308 100%)',
        headerBorder: '#ca8a04',
        rowBorder: '#fef08a',
        altRowBg: '#fefce8',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#a16207',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(234, 179, 8, 0.3)',
        borderRadius: '6px'
      },
      navy_blue: {
        headerBg: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
        headerBorder: '#1e40af',
        rowBorder: '#dbeafe',
        altRowBg: '#eff6ff',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#1e3a8a',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(59, 130, 246, 0.3)',
        borderRadius: '6px'
      },
      burgundy_wine: {
        headerBg: 'linear-gradient(135deg, #7f1d1d 0%, #dc2626 100%)',
        headerBorder: '#991b1b',
        rowBorder: '#fecaca',
        altRowBg: '#fef2f2',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#7f1d1d',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(220, 38, 38, 0.3)',
        borderRadius: '6px'
      },
      lavender_purple: {
        headerBg: 'linear-gradient(135deg, #7c2d12 0%, #a855f7 100%)',
        headerBorder: '#9333ea',
        rowBorder: '#e9d5ff',
        altRowBg: '#faf5ff',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#7c2d12',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(168, 85, 247, 0.3)',
        borderRadius: '6px'
      },
      ocean_teal: {
        headerBg: 'linear-gradient(135deg, #134e4a 0%, #14b8a6 100%)',
        headerBorder: '#0f766e',
        rowBorder: '#ccfbf1',
        altRowBg: '#f0fdfa',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#134e4a',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(20, 184, 166, 0.3)',
        borderRadius: '6px'
      },
      cherry_blossom: {
        headerBg: 'linear-gradient(135deg, #be185d 0%, #fb7185 100%)',
        headerBorder: '#db2777',
        rowBorder: '#fce7f3',
        altRowBg: '#fdf2f8',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#be185d',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(251, 113, 133, 0.3)',
        borderRadius: '6px'
      },
      autumn_orange: {
        headerBg: 'linear-gradient(135deg, #9a3412 0%, #fb923c 100%)',
        headerBorder: '#ea580c',
        rowBorder: '#fed7aa',
        altRowBg: '#fff7ed',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#9a3412',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(251, 146, 60, 0.3)',
        borderRadius: '6px'
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
      var updates = _defineProperty({}, textProperty, newText);
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
  var getElementTypeStyles = function getElementTypeStyles(element, zoom) {
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
          borderTop: "".concat(element.lineWidth || 1, "px solid ").concat(element.lineColor || '#6b7280'),
          height: "".concat(Math.max(element.lineWidth || 1, 12), "px"),
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
          backgroundColor: element.backgroundColor || '#64748b',
          height: '12px',
          // Hauteur fixe pour les lignes
          cursor: 'pointer'
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
    return _objectSpread(_objectSpread({
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
    }, getElementTypeStyles(element, zoom));
  }, [element.x, element.y, element.width, element.height, element.rotation, element.scale, element.backgroundColor, element.backgroundOpacity, element.borderWidth, element.borderStyle, element.borderColor, element.borderRadius, element.opacity, element.brightness, element.contrast, element.saturate, element.boxShadowColor, element.boxShadowSpread, element.boxShadowBlur, element.shadow, element.shadowOffsetX, element.shadowOffsetY, element.shadowColor, element.type, elementPadding, zoom, isSelected, dragAndDrop.isDragging, dragAndDrop.draggedElementId, dragAndDrop.dragOffset]);
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
  element.type === 'image' && !element.src ? '📷 Image' : element.type === 'line' ? null : element.type === 'layout-header' ? '[H] En-tête' : element.type === 'layout-footer' ? '📄 Pied de Page' : element.type === 'layout-sidebar' ? '📄 Barre Latérale' : element.type === 'layout-section' ? '📄 Section' : element.type === 'layout-container' ? '📦 Conteneur' : element.type === 'shape-rectangle' ? '▭' : element.type === 'shape-circle' ? '○' : element.type === 'shape-line' ? null : element.type === 'shape-arrow' ? '→' : element.type === 'shape-triangle' ? '△' : element.type === 'shape-star' ? '⭐' : element.type === 'divider' ? null : element.type === 'image-upload' ? '📤 Télécharger' : element.type === 'logo' ? '🏷️ Logo' : element.type === 'barcode' ? '📊 123456' : element.type === 'qrcode' || element.type === 'qrcode-dynamic' ? '📱 QR' : element.type === 'icon' ? element.content || '🎯' : element.type === 'dynamic-text' ? element.content || '{{variable}}' : element.type === 'formula' ? element.content || '{{prix * quantite}}' : element.type === 'conditional-text' ? element.content || '{{condition ? "Oui" : "Non"}}' : element.type === 'counter' ? element.content || '1' : element.type === 'date-dynamic' ? element.content || '{{date|format:Y-m-d}}' : element.type === 'currency' ? element.content || '{{montant|currency:EUR}}' : element.type === 'table-dynamic' ? '📊 Tableau' : element.type === 'gradient-box' ? '🌈 Dégradé' : element.type === 'shadow-box' ? '📦 Ombre' : element.type === 'rounded-box' ? '🔄 Arrondi' : element.type === 'border-box' ? '🔲 Bordure' : element.type === 'background-pattern' ? '🎨 Motif' : element.type === 'watermark' ? element.content || 'CONFIDENTIEL' : element.type === 'progress-bar' ? null : element.type === 'product_table' ? null :
  // Le contenu sera rendu plus bas dans le même conteneur
  element.type === 'customer_info' ? null :
  // Le contenu sera rendu plus bas dans le même conteneur
  element.type !== 'image' && element.type !== 'rectangle' && element.type !== 'company_logo' && element.type !== 'order_number' && element.type !== 'company_info' && element.type !== 'document_type' ? element.type : null, element.type === 'product_table' && function (_element$columns2, _element$columns3, _element$columns4, _element$columns5, _element$columns6, _element$columns7, _element$columns8, _element$columns9, _element$columns0, _element$columns1, _element$columns10, _element$columns11, _element$columns12, _element$columns13, _element$columns14, _element$columns15, _element$columns16, _element$columns17, _element$columns18, _element$columns19, _element$columns20, _element$columns21, _element$columns22, _element$columns23, _element$columns24, _element$columns25, _element$columns26, _element$columns27, _element$columns28, _element$columns29, _element$columns30) {
    // Données des produits (pourrait venir de props ou d'un état global)
    var products = [{
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
        borderRadius: tableStyles.borderRadius ? "".concat(tableStyles.borderRadius * zoom, "px") : '2px',
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
    }, /*#__PURE__*/React.createElement("div", {
      key: "row-1",
      style: {
        display: 'flex',
        borderBottom: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        backgroundColor: tableStyles.altRowBg,
        fontSize: "".concat(tableStyles.rowFontSize * zoom, "px"),
        transition: 'background-color 0.15s ease'
      }
    }, ((_element$columns8 = element.columns) === null || _element$columns8 === void 0 ? void 0 : _element$columns8.image) !== false && /*#__PURE__*/React.createElement("div", {
      key: "row-1-image",
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
      key: "row-1-name",
      style: {
        flex: 1,
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        color: tableStyles.rowTextColor,
        fontWeight: '500',
        lineHeight: '1.3'
      }
    }, "Produit A - Description du produit"), ((_element$columns0 = element.columns) === null || _element$columns0 === void 0 ? void 0 : _element$columns0.sku) !== false && /*#__PURE__*/React.createElement("div", {
      key: "row-1-sku",
      style: {
        flex: '0 0 80px',
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        color: tableStyles.rowTextColor,
        opacity: 0.8,
        fontFamily: 'monospace',
        fontSize: "".concat(tableStyles.rowFontSize * zoom * 0.9, "px")
      }
    }, "SKU001"), ((_element$columns1 = element.columns) === null || _element$columns1 === void 0 ? void 0 : _element$columns1.quantity) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 60px',
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'center',
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        color: tableStyles.rowTextColor,
        fontWeight: '600'
      }
    }, "2"), ((_element$columns10 = element.columns) === null || _element$columns10 === void 0 ? void 0 : _element$columns10.price) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        color: tableStyles.rowTextColor,
        fontWeight: '500',
        fontFamily: '"Inter", system-ui, sans-serif'
      }
    }, "\u20AC19.99"), ((_element$columns11 = element.columns) === null || _element$columns11 === void 0 ? void 0 : _element$columns11.total) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        color: tableStyles.rowTextColor,
        fontWeight: '600',
        fontFamily: '"Inter", system-ui, sans-serif'
      }
    }, "\u20AC39.98")), /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        borderBottom: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        backgroundColor: 'transparent',
        // Ligne normale sans alternance
        fontSize: "".concat(tableStyles.rowFontSize * zoom, "px"),
        transition: 'background-color 0.15s ease'
      }
    }, ((_element$columns12 = element.columns) === null || _element$columns12 === void 0 ? void 0 : _element$columns12.image) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 40px',
        padding: "".concat(5 * zoom, "px ").concat(4 * zoom, "px"),
        textAlign: 'center',
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        color: tableStyles.rowTextColor,
        opacity: 0.7,
        fontSize: "".concat(tableStyles.rowFontSize * zoom * 0.9, "px")
      }
    }, "\uD83D\uDCF7"), ((_element$columns13 = element.columns) === null || _element$columns13 === void 0 ? void 0 : _element$columns13.name) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: 1,
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        color: tableStyles.rowTextColor,
        fontWeight: '500',
        lineHeight: '1.3'
      }
    }, "Produit B - Un autre article"), ((_element$columns14 = element.columns) === null || _element$columns14 === void 0 ? void 0 : _element$columns14.sku) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        color: tableStyles.rowTextColor,
        opacity: 0.8,
        fontFamily: 'monospace',
        fontSize: "".concat(tableStyles.rowFontSize * zoom * 0.9, "px")
      }
    }, "SKU002"), ((_element$columns15 = element.columns) === null || _element$columns15 === void 0 ? void 0 : _element$columns15.quantity) !== false && /*#__PURE__*/React.createElement("div", {
      key: "row-1-quantity",
      style: {
        flex: '0 0 60px',
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'center',
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        color: tableStyles.rowTextColor,
        fontWeight: '600'
      }
    }, "1"), ((_element$columns16 = element.columns) === null || _element$columns16 === void 0 ? void 0 : _element$columns16.price) !== false && /*#__PURE__*/React.createElement("div", {
      key: "row-1-price",
      style: {
        flex: '0 0 80px',
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        color: tableStyles.rowTextColor,
        fontWeight: '500',
        fontFamily: '"Inter", system-ui, sans-serif'
      }
    }, "\u20AC29.99"), ((_element$columns17 = element.columns) === null || _element$columns17 === void 0 ? void 0 : _element$columns17.total) !== false && /*#__PURE__*/React.createElement("div", {
      key: "row-1-total",
      style: {
        flex: '0 0 80px',
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        color: tableStyles.rowTextColor,
        fontWeight: '600',
        fontFamily: '"Inter", system-ui, sans-serif'
      }
    }, "\u20AC29.99"))), (element.showSubtotal || element.showShipping || element.showTaxes || element.showDiscount || element.showTotal) && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: 1,
        display: 'flex',
        flexDirection: 'column'
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        borderTop: showBorders ? "".concat(tableStyles.borderWidth * 2 * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none',
        marginTop: "".concat(10 * zoom, "px"),
        paddingTop: "".concat(10 * zoom, "px"),
        fontSize: "".concat(tableStyles.rowFontSize * zoom, "px")
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        flex: 1,
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'right',
        fontWeight: 'bold',
        color: element.color || '#666'
      }
    }), ((_element$columns18 = element.columns) === null || _element$columns18 === void 0 ? void 0 : _element$columns18.quantity) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 60px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'center'
      }
    }), ((_element$columns19 = element.columns) === null || _element$columns19 === void 0 ? void 0 : _element$columns19.price) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'right',
        fontWeight: 'bold',
        color: element.color || '#666'
      }
    }), ((_element$columns20 = element.columns) === null || _element$columns20 === void 0 ? void 0 : _element$columns20.total) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'right',
        fontWeight: 'bold',
        color: element.color || '#666'
      }
    }, "Total")), element.showSubtotal && /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        borderBottom: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        backgroundColor: tableStyles.altRowBg,
        fontSize: "".concat(tableStyles.rowFontSize * zoom, "px")
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        flex: 1,
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        color: tableStyles.rowTextColor,
        fontWeight: '600'
      }
    }, "Sous-total"), ((_element$columns21 = element.columns) === null || _element$columns21 === void 0 ? void 0 : _element$columns21.quantity) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 60px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'center'
      }
    }), ((_element$columns22 = element.columns) === null || _element$columns22 === void 0 ? void 0 : _element$columns22.price) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'right'
      }
    }), lastVisibleColumn === 'total' && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'right',
        fontWeight: 'bold'
      }
    }, "\u20AC", subtotal.toFixed(2))), element.showShipping && /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        borderBottom: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        backgroundColor: 'transparent',
        fontSize: "".concat(tableStyles.rowFontSize * zoom, "px")
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        flex: 1,
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        color: tableStyles.rowTextColor,
        fontWeight: '500'
      }
    }, "Frais de port"), ((_element$columns23 = element.columns) === null || _element$columns23 === void 0 ? void 0 : _element$columns23.quantity) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 60px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'center'
      }
    }), ((_element$columns24 = element.columns) === null || _element$columns24 === void 0 ? void 0 : _element$columns24.price) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'right'
      }
    }), lastVisibleColumn === 'total' && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'right'
      }
    }, "\u20AC", shipping.toFixed(2))), element.showTaxes && /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        borderBottom: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        backgroundColor: 'transparent',
        fontSize: "".concat(tableStyles.rowFontSize * zoom, "px")
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        flex: 1,
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        color: tableStyles.rowTextColor,
        fontWeight: '500'
      }
    }, "Taxes (TVA 20%)"), ((_element$columns25 = element.columns) === null || _element$columns25 === void 0 ? void 0 : _element$columns25.quantity) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 60px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'center'
      }
    }), ((_element$columns26 = element.columns) === null || _element$columns26 === void 0 ? void 0 : _element$columns26.price) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'right'
      }
    }), lastVisibleColumn === 'total' && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'right'
      }
    }, "\u20AC", tax.toFixed(2))), element.showDiscount && /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        borderBottom: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        backgroundColor: 'transparent',
        fontSize: "".concat(tableStyles.rowFontSize * zoom, "px")
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        flex: 1,
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        color: '#dc3545',
        fontWeight: '500'
      }
    }, "Remise"), ((_element$columns27 = element.columns) === null || _element$columns27 === void 0 ? void 0 : _element$columns27.quantity) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 60px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'center'
      }
    }), ((_element$columns28 = element.columns) === null || _element$columns28 === void 0 ? void 0 : _element$columns28.price) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'right'
      }
    }), lastVisibleColumn === 'total' && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'right',
        color: '#d32f2f'
      }
    }, "\u20AC", discount.toFixed(2))), element.showTotal && /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        borderTop: showBorders ? "".concat(tableStyles.borderWidth * 2 * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none',
        backgroundColor: tableStyles.headerBg,
        marginTop: "".concat(6 * zoom, "px"),
        paddingTop: "".concat(6 * zoom, "px"),
        color: tableStyles.headerTextColor,
        fontWeight: tableStyles.headerFontWeight,
        fontSize: "".concat(tableStyles.headerFontSize * zoom, "px")
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        flex: 1,
        padding: "".concat(6 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        fontSize: "".concat(tableStyles.headerFontSize * zoom, "px"),
        textTransform: 'uppercase',
        letterSpacing: '0.05em'
      }
    }, "TOTAL TTC"), ((_element$columns29 = element.columns) === null || _element$columns29 === void 0 ? void 0 : _element$columns29.quantity) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 60px',
        padding: "".concat(6 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'center'
      }
    }), ((_element$columns30 = element.columns) === null || _element$columns30 === void 0 ? void 0 : _element$columns30.price) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(6 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right'
      }
    }), lastVisibleColumn === 'total' && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(6 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        fontSize: "".concat(tableStyles.headerFontSize * zoom * 1.2, "px"),
        fontWeight: '700',
        color: tableStyles.headerTextColor,
        fontFamily: '"Inter", system-ui, sans-serif'
      }
    }, "\u20AC", total.toFixed(2)))));
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
  }, "123 Rue de la Paix", /*#__PURE__*/React.createElement("br", null), "75001 Paris", /*#__PURE__*/React.createElement("br", null), "France")), ((_element$fields5 = element.fields) === null || _element$fields5 === void 0 ? void 0 : _element$fields5.includes('company')) && /*#__PURE__*/React.createElement("div", {
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
  }, "123 456 789 00012")))), element.type === 'company_logo' && /*#__PURE__*/React.createElement("div", {
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
  }, "123 Rue de l'Entreprise", /*#__PURE__*/React.createElement("br", null), "75001 Paris - France")), ((_element$fields0 = element.fields) === null || _element$fields0 === void 0 ? void 0 : _element$fields0.includes('phone')) && /*#__PURE__*/React.createElement("div", {
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

    // Données de test pour l'aperçu (seront remplacées par les vraies données lors de la génération)
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
      return resize.handleResizeStart(e, 'n', {
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
      return resize.handleResizeStart(e, 's', {
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
      return resize.handleResizeStart(e, 'w', {
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
      return resize.handleResizeStart(e, 'e', {
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
// EXTERNAL MODULE: ./src/hooks/useDragAndDrop.js
var useDragAndDrop = __webpack_require__(174);
;// ./src/components/Toolbar.jsx
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }

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
    canRedo = _ref.canRedo,
    onPreview = _ref.onPreview;
  var _React$useState = react.useState('home'),
    _React$useState2 = _slicedToArray(_React$useState, 2),
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
  }, /*#__PURE__*/react.createElement("h5", null, "Actions"), /*#__PURE__*/react.createElement("div", {
    className: "group-buttons"
  }, /*#__PURE__*/react.createElement("button", {
    className: "tool-button",
    onClick: onPreview,
    title: "Aper\xE7u du PDF"
  }, /*#__PURE__*/react.createElement("span", {
    className: "button-icon"
  }, "\uD83D\uDC41\uFE0F"), /*#__PURE__*/react.createElement("span", {
    className: "button-text"
  }, "Aper\xE7u")))), /*#__PURE__*/react.createElement("div", {
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
// EXTERNAL MODULE: ./src/hooks/useCanvasState.js
var useCanvasState = __webpack_require__(169);
// EXTERNAL MODULE: ./src/hooks/useKeyboardShortcuts.js
var useKeyboardShortcuts = __webpack_require__(960);
// EXTERNAL MODULE: ./src/hooks/useGlobalSettings.js
var useGlobalSettings = __webpack_require__(700);
;// ./src/components/FPSCounter.jsx
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
;// ./src/components/ContextMenu.jsx

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
// EXTERNAL MODULE: ./src/components/PreviewModal.jsx
var PreviewModal = __webpack_require__(690);
;// ./src/components/ModalPDFViewer.jsx
var ModalPDFViewer = function ModalPDFViewer(_ref) {
  var isOpen = _ref.isOpen,
    onClose = _ref.onClose,
    pdfUrl = _ref.pdfUrl,
    _ref$title = _ref.title,
    title = _ref$title === void 0 ? 'Aperçu PDF' : _ref$title;
  if (!isOpen) return null;
  return /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'fixed',
      top: 0,
      left: 0,
      right: 0,
      bottom: 0,
      backgroundColor: 'rgba(0, 0, 0, 0.7)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      zIndex: 9999
    },
    onClick: onClose
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      backgroundColor: 'white',
      borderRadius: '8px',
      width: '90%',
      height: '90%',
      maxWidth: '1200px',
      maxHeight: '800px',
      display: 'flex',
      flexDirection: 'column',
      boxShadow: '0 10px 30px rgba(0, 0, 0, 0.3)'
    },
    onClick: function onClick(e) {
      return e.stopPropagation();
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      padding: '16px 20px',
      borderBottom: '1px solid #e2e8f0',
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
      backgroundColor: '#f8f9fa',
      borderRadius: '8px 8px 0 0'
    }
  }, /*#__PURE__*/React.createElement("h3", {
    style: {
      margin: 0,
      color: '#1a202c',
      fontSize: '18px',
      fontWeight: '600'
    }
  }, title), /*#__PURE__*/React.createElement("button", {
    onClick: onClose,
    style: {
      background: 'none',
      border: 'none',
      fontSize: '24px',
      cursor: 'pointer',
      color: '#718096',
      padding: '4px 8px',
      borderRadius: '4px',
      transition: 'all 0.2s'
    },
    onMouseOver: function onMouseOver(e) {
      return e.target.style.backgroundColor = '#e2e8f0';
    },
    onMouseOut: function onMouseOut(e) {
      return e.target.style.backgroundColor = 'transparent';
    }
  }, "\xD7")), /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      padding: '0',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      backgroundColor: '#f5f5f5'
    }
  }, pdfUrl ? /*#__PURE__*/React.createElement("iframe", {
    src: pdfUrl,
    style: {
      width: '100%',
      height: '100%',
      border: 'none',
      borderRadius: '0 0 8px 8px'
    },
    title: title
  }) : /*#__PURE__*/React.createElement("div", {
    style: {
      textAlign: 'center',
      color: '#718096',
      fontSize: '16px'
    }
  }, "Chargement du PDF...")), /*#__PURE__*/React.createElement("div", {
    style: {
      padding: '16px 20px',
      borderTop: '1px solid #e2e8f0',
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
      backgroundColor: '#f8f9fa',
      borderRadius: '0 0 8px 8px'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: '14px',
      color: '#718096'
    }
  }, "Utilisez Ctrl+P (Cmd+P sur Mac) pour imprimer"), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("button", {
    onClick: function onClick() {
      if (pdfUrl) {
        var link = document.createElement('a');
        link.href = pdfUrl;
        link.download = 'document.pdf';
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        // Vérifier que l'élément existe encore avant de le supprimer
        if (link.parentNode === document.body) {
          document.body.removeChild(link);
        }
      }
    },
    style: {
      backgroundColor: '#4299e1',
      color: 'white',
      border: 'none',
      padding: '8px 16px',
      borderRadius: '4px',
      cursor: 'pointer',
      fontSize: '14px',
      marginRight: '8px',
      transition: 'background-color 0.2s'
    },
    onMouseOver: function onMouseOver(e) {
      return e.target.style.backgroundColor = '#3182ce';
    },
    onMouseOut: function onMouseOut(e) {
      return e.target.style.backgroundColor = '#4299e1';
    }
  }, "\uD83D\uDCE5 T\xE9l\xE9charger"), /*#__PURE__*/React.createElement("button", {
    onClick: onClose,
    style: {
      backgroundColor: '#e2e8f0',
      color: '#4a5568',
      border: 'none',
      padding: '8px 16px',
      borderRadius: '4px',
      cursor: 'pointer',
      fontSize: '14px'
    }
  }, "Fermer")))));
};
/* harmony default export */ const components_ModalPDFViewer = (ModalPDFViewer);
;// ./src/components/WooCommerceElements.jsx



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
  var resize = (0,useResize/* useResize */.s)({
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
;// ./src/components/ElementLibrary.jsx
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
      content: '{{order_total}} €',
      fontSize: 14,
      fontFamily: 'Arial',
      fontWeight: 'normal',
      textAlign: 'left',
      color: '#333333'
    }
  }, {
    type: 'order_totals',
    fieldID: 'order_totals',
    label: 'Totaux Commande',
    icon: '💰',
    description: 'Sous-total, TVA, frais de port, total',
    defaultProperties: {
      showSubtotal: true,
      showShipping: true,
      showTaxes: true,
      showDiscount: true,
      showTotal: true,
      layout: 'vertical',
      fontSize: 12,
      fontFamily: 'Arial',
      fontWeight: 'normal',
      labelAlign: 'left',
      valueAlign: 'right',
      spacing: 4
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
      text: template.preview.replace('\\n', '\n'),
      fontSize: template.fontSize || 16,
      fontWeight: template.fontWeight || 'normal'
    });
  };
  var headerTemplates = [{
    id: 'classic',
    name: 'Classique',
    preview: '🏢 ENTREPRISE\n123 Rue de la Paix\n75000 Paris',
    fontSize: 14,
    fontWeight: 'bold'
  }, {
    id: 'modern',
    name: 'Moderne',
    preview: '✨ ENTREPRISE MODERNE\nInnovation & Qualité\ncontact@entreprise.com',
    fontSize: 16,
    fontWeight: 'bold'
  }, {
    id: 'minimal',
    name: 'Minimal',
    preview: 'ENTREPRISE\nAdresse • Téléphone • Email',
    fontSize: 12,
    fontWeight: 'normal'
  }, {
    id: 'elegant',
    name: 'Élégant',
    preview: '🎩 Maison Élégante\nParis, France\nwww.entreprise.com',
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
      className: "template-preview"
    }, template.preview.split('\n').map(function (line, index) {
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
// EXTERNAL MODULE: ./src/hooks/useElementCustomization.js
var useElementCustomization = __webpack_require__(743);
// EXTERNAL MODULE: ./src/hooks/useElementSynchronization.js
var useElementSynchronization = __webpack_require__(874);
// EXTERNAL MODULE: ./src/services/ElementCustomizationService.js
var ElementCustomizationService = __webpack_require__(455);
// EXTERNAL MODULE: ./src/utilities/elementPropertyRestrictions.js
var elementPropertyRestrictions = __webpack_require__(779);
;// ./src/components/PropertiesPanel.jsx
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function PropertiesPanel_slicedToArray(r, e) { return PropertiesPanel_arrayWithHoles(r) || PropertiesPanel_iterableToArrayLimit(r, e) || PropertiesPanel_unsupportedIterableToArray(r, e) || PropertiesPanel_nonIterableRest(); }
function PropertiesPanel_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function PropertiesPanel_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function PropertiesPanel_arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || PropertiesPanel_unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function PropertiesPanel_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return PropertiesPanel_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? PropertiesPanel_arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return PropertiesPanel_arrayLikeToArray(r); }
function PropertiesPanel_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }






// Profils de propriétés contextuelles par type d'élément
var ELEMENT_PROPERTY_PROFILES = {
  // Éléments texte
  text: {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['text', 'variables'],
    effects: ['opacity', 'shadows', 'filters']
  },
  'layout-header': {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['text', 'variables'],
    effects: ['opacity', 'shadows', 'filters']
  },
  'layout-footer': {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['text', 'variables'],
    effects: ['opacity', 'shadows', 'filters']
  },
  'layout-section': {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['text', 'variables'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Éléments image/logo
  logo: {
    appearance: ['colors', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['image'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Logo entreprise (même propriétés que logo)
  company_logo: {
    appearance: ['colors', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['image'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Tableaux produits
  product_table: {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['table'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Informations client
  customer_info: {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['customer_fields'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Informations entreprise
  company_info: {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['company_fields'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Type de document
  document_type: {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['document_type'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Numéro de commande
  order_number: {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['order_number'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Éléments par défaut (forme géométrique)
  "default": {
    appearance: ['colors', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: [],
    effects: ['opacity', 'shadows', 'filters']
  }
};

// Fonction helper pour parser les valeurs numériques de manière sécurisée
var safeParseInt = function safeParseInt(value) {
  var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
  if (value === null || value === undefined || value === '') return defaultValue;
  var parsed = parseInt(value, 10);
  return isNaN(parsed) ? defaultValue : parsed;
};
var safeParseFloat = function safeParseFloat(value) {
  var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
  if (value === null || value === undefined || value === '') return defaultValue;
  var parsed = parseFloat(value);
  return isNaN(parsed) ? defaultValue : parsed;
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
      }) : [].concat(_toConsumableArray(currentDecorations), ['underline']);
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
      }) : [].concat(_toConsumableArray(currentDecorations), ['line-through']);
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
  var _useElementCustomizat = (0,useElementCustomization/* useElementCustomization */.R)(selectedElements, elements, onPropertyChange),
    localProperties = _useElementCustomizat.localProperties,
    activeTab = _useElementCustomizat.activeTab,
    setActiveTab = _useElementCustomizat.setActiveTab,
    customizationChange = _useElementCustomizat.handlePropertyChange;
  var _useElementSynchroniz = (0,useElementSynchronization/* useElementSynchronization */.I)(elements, onPropertyChange, onBatchUpdate, true,
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
        validatedValue = ElementCustomizationService/* elementCustomizationService */.l.validateProperty(property, value);
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

    // Synchronisation immédiate pour les changements critiques
    if (['x', 'y', 'width', 'height'].includes(property)) {
      syncImmediate(elementId, property, validatedValue);
    }
  }, [customizationChange, syncImmediate]);

  // Gestionnaire pour le toggle "Aucun fond"
  var handleNoBackgroundToggle = (0,react.useCallback)(function (elementId, checked) {
    // Vérifier si la propriété backgroundColor est autorisée pour ce type d'élément
    var isBackgroundAllowed = selectedElement !== null && selectedElement !== void 0 && selectedElement.type ? (0,elementPropertyRestrictions/* isPropertyAllowed */.Qn)(selectedElement.type, 'backgroundColor') : true;
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
    var _localProperties$back, _localProperties$back2, _localProperties$bord, _localProperties$bord2, _localProperties$bord3, _localProperties$bord4, _localProperties$boxS, _localProperties$boxS2, _localProperties$boxS3, _localProperties$boxS4, _localProperties$show, _localProperties$show2, _localProperties$show3, _localProperties$show4, _localProperties$show5, _localProperties$show6, _localProperties$show7;
    if (!selectedElement) {
      return /*#__PURE__*/React.createElement("div", {
        className: "no-selection"
      }, /*#__PURE__*/React.createElement("div", {
        className: "no-selection-icon"
      }, "\uD83D\uDC46"), /*#__PURE__*/React.createElement("p", null, "S\xE9lectionnez un \xE9l\xE9ment pour modifier ses propri\xE9t\xE9s"), selectedElements.length > 1 && /*#__PURE__*/React.createElement("p", {
        className: "selection-info"
      }, selectedElements.length, " \xE9l\xE9ments s\xE9lectionn\xE9s"));
    }

    // Obtenir le profil de propriétés pour ce type d'élément
    var elementProfile = ELEMENT_PROPERTY_PROFILES[selectedElement.type] || ELEMENT_PROPERTY_PROFILES["default"];
    var allowedControls = elementProfile[activeTab] || [];
    switch (activeTab) {
      case 'appearance':
        return /*#__PURE__*/React.createElement("div", {
          className: "tab-content"
        }, /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83C\uDFA8 Couleurs & Apparence"), /*#__PURE__*/React.createElement(ColorPicker, {
          label: "Texte",
          value: localProperties.color,
          onChange: function onChange(value) {
            handlePropertyChange(selectedElement.id, 'color', value);
          },
          presets: ['#1e293b', '#334155', '#475569', '#64748b', '#94a3b8', '#cbd5e1', '#000000'],
          defaultColor: "#333333"
        }), /*#__PURE__*/React.createElement("div", {
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
        }, Math.round(((_localProperties$back2 = localProperties.backgroundOpacity) !== null && _localProperties$back2 !== void 0 ? _localProperties$back2 : 1) * 100), "%"))))), allowedControls.includes('font') && selectedElement.type !== 'product_table' && /*#__PURE__*/React.createElement(FontControls, {
          elementId: selectedElement.id,
          properties: localProperties,
          onPropertyChange: handlePropertyChange
        }), allowedControls.includes('borders') && localProperties.borderWidth >= 0 && /*#__PURE__*/React.createElement("div", {
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
        }, (_localProperties$bord4 = localProperties.borderRadius) !== null && _localProperties$bord4 !== void 0 ? _localProperties$bord4 : 4, "px"))))), allowedControls.includes('effects') && /*#__PURE__*/React.createElement("div", {
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
        }, (_localProperties$boxS4 = localProperties.boxShadowSpread) !== null && _localProperties$boxS4 !== void 0 ? _localProperties$boxS4 : 0, "px")))));
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
        }, "\uD83D\uDCB0 Total")))), allowedControls.includes('image') && (selectedElement.type === 'logo' || selectedElement.type === 'company_logo') && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "[Img] Image"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "URL de l'image:"), /*#__PURE__*/React.createElement("input", {
          type: "url",
          value: localProperties.imageUrl || '',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'imageUrl', e.target.value);
          },
          placeholder: "https://exemple.com/logo.jpg"
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Alt text:"), /*#__PURE__*/React.createElement("input", {
          type: "text",
          value: localProperties.alt || '',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'alt', e.target.value);
          },
          placeholder: "Logo de l'entreprise"
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Adaptation:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.fit || 'contain',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'fit', e.target.value);
          }
        }, /*#__PURE__*/React.createElement("option", {
          value: "contain"
        }, "Contenir (int\xE9gral)"), /*#__PURE__*/React.createElement("option", {
          value: "cover"
        }, "Couvrir (zoom)"), /*#__PURE__*/React.createElement("option", {
          value: "fill"
        }, "Remplir"), /*#__PURE__*/React.createElement("option", {
          value: "none"
        }, "Aucune")))), allowedControls.includes('table') && selectedElement.type === 'product_table' && /*#__PURE__*/React.createElement("div", {
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
          altRowBg: '#ecfdf5',
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
          gradient: 'linear-gradient(135deg, #374151 0%, #6b7280 100%)',
          headerBorder: '#4b5563',
          rowBorder: '#f3f4f6',
          altRowBg: '#f9fafb',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'coral_sunset',
          label: 'Corail Couchant',
          gradient: 'linear-gradient(135deg, #c2410c 0%, #fb7185 100%)',
          headerBorder: '#ea580c',
          rowBorder: '#fed7d7',
          altRowBg: '#fef7f7',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'mint_green',
          label: 'Menthe Fraîche',
          gradient: 'linear-gradient(135deg, #065f46 0%, #34d399 100%)',
          headerBorder: '#047857',
          rowBorder: '#d1fae5',
          altRowBg: '#ecfdf5',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'violet_dream',
          label: 'Rêve Violet',
          gradient: 'linear-gradient(135deg, #6d28d9 0%, #c084fc 100%)',
          headerBorder: '#8b5cf6',
          rowBorder: '#ede9fe',
          altRowBg: '#f5f3ff',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'sky_blue',
          label: 'Ciel Bleu',
          gradient: 'linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%)',
          headerBorder: '#0284c7',
          rowBorder: '#bae6fd',
          altRowBg: '#f0f9ff',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'forest_green',
          label: 'Vert Forêt',
          gradient: 'linear-gradient(135deg, #14532d 0%, #22c55e 100%)',
          headerBorder: '#15803d',
          rowBorder: '#bbf7d0',
          altRowBg: '#f0fdf4',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'ruby_red',
          label: 'Rouge Rubis',
          gradient: 'linear-gradient(135deg, #b91c1c 0%, #f87171 100%)',
          headerBorder: '#dc2626',
          rowBorder: '#fecaca',
          altRowBg: '#fef2f2',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'golden_yellow',
          label: 'Jaune Doré',
          gradient: 'linear-gradient(135deg, #a16207 0%, #eab308 100%)',
          headerBorder: '#ca8a04',
          rowBorder: '#fef08a',
          altRowBg: '#fefce8',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'navy_blue',
          label: 'Bleu Marine',
          gradient: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
          headerBorder: '#1e40af',
          rowBorder: '#dbeafe',
          altRowBg: '#eff6ff',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'burgundy_wine',
          label: 'Vin Bordeaux',
          gradient: 'linear-gradient(135deg, #7f1d1d 0%, #dc2626 100%)',
          headerBorder: '#991b1b',
          rowBorder: '#fecaca',
          altRowBg: '#fef2f2',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'lavender_purple',
          label: 'Lavande',
          gradient: 'linear-gradient(135deg, #7c2d12 0%, #a855f7 100%)',
          headerBorder: '#9333ea',
          rowBorder: '#e9d5ff',
          altRowBg: '#faf5ff',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'ocean_teal',
          label: 'Océan Sarcelle',
          gradient: 'linear-gradient(135deg, #134e4a 0%, #14b8a6 100%)',
          headerBorder: '#0f766e',
          rowBorder: '#ccfbf1',
          altRowBg: '#f0fdfa',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'cherry_blossom',
          label: 'Cerisier',
          gradient: 'linear-gradient(135deg, #be185d 0%, #fb7185 100%)',
          headerBorder: '#db2777',
          rowBorder: '#fce7f3',
          altRowBg: '#fdf2f8',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'autumn_orange',
          label: 'Automne',
          gradient: 'linear-gradient(135deg, #9a3412 0%, #fb923c 100%)',
          headerBorder: '#ea580c',
          rowBorder: '#fed7aa',
          altRowBg: '#fff7ed',
          borderWidth: 1.5,
          textColor: '#ffffff'
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
            className: "table-preview",
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
        })))), allowedControls.includes('customer_fields') && selectedElement.type === 'customer_info' && /*#__PURE__*/React.createElement("div", {
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
              var newFields = e.target.checked ? [].concat(_toConsumableArray(currentFields), [key]) : currentFields.filter(function (f) {
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
        }, localProperties.spacing || 8, "px")))), allowedControls.includes('company_fields') && selectedElement.type === 'company_info' && /*#__PURE__*/React.createElement("div", {
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
        }].map(function (_ref8) {
          var _localProperties$fiel3, _localProperties$fiel4;
          var key = _ref8.key,
            label = _ref8.label;
          return /*#__PURE__*/React.createElement("label", {
            key: key,
            className: "checkbox-item"
          }, /*#__PURE__*/React.createElement("input", {
            type: "checkbox",
            checked: (_localProperties$fiel3 = (_localProperties$fiel4 = localProperties.fields) === null || _localProperties$fiel4 === void 0 ? void 0 : _localProperties$fiel4.includes(key)) !== null && _localProperties$fiel3 !== void 0 ? _localProperties$fiel3 : true,
            onChange: function onChange(e) {
              var currentFields = localProperties.fields || ['name', 'address', 'phone', 'email', 'website', 'vat', 'rcs', 'siret'];
              var newFields = e.target.checked ? [].concat(_toConsumableArray(currentFields), [key]) : currentFields.filter(function (f) {
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
          onClick: /*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
            var response, media, modal, modalContent, title, closeBtn, grid, _t;
            return _regenerator().w(function (_context) {
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
        }].map(function (_ref0) {
          var _localProperties$fiel5, _localProperties$fiel6;
          var key = _ref0.key,
            label = _ref0.label;
          return /*#__PURE__*/React.createElement("label", {
            key: key,
            className: "checkbox-item"
          }, /*#__PURE__*/React.createElement("input", {
            type: "checkbox",
            checked: (_localProperties$fiel5 = (_localProperties$fiel6 = localProperties.fields) === null || _localProperties$fiel6 === void 0 ? void 0 : _localProperties$fiel6.includes(key)) !== null && _localProperties$fiel5 !== void 0 ? _localProperties$fiel5 : false,
            onChange: function onChange(e) {
              var currentFields = localProperties.fields || [];
              var newFields = e.target.checked ? [].concat(_toConsumableArray(currentFields), [key]) : currentFields.filter(function (f) {
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
// EXTERNAL MODULE: ./src/components/NewTemplateModal.jsx
var NewTemplateModal = __webpack_require__(510);
;// ./src/components/PDFCanvasEditor.jsx
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







var PDFCanvasEditor = function PDFCanvasEditor(_ref) {
  var _window$pdfBuilderAja3, _window$pdfBuilderAja4;
  var options = _ref.options;
  var _useState = (0,react.useState)('select'),
    _useState2 = PDFCanvasEditor_slicedToArray(_useState, 2),
    tool = _useState2[0],
    setTool = _useState2[1];
  var _useState3 = (0,react.useState)(false),
    _useState4 = PDFCanvasEditor_slicedToArray(_useState3, 2),
    showPreviewModal = _useState4[0],
    setShowPreviewModal = _useState4[1];
  var _useState5 = (0,react.useState)(false),
    _useState6 = PDFCanvasEditor_slicedToArray(_useState5, 2),
    showNewTemplateModal = _useState6[0],
    setShowNewTemplateModal = _useState6[1];
  var _useState7 = (0,react.useState)(false),
    _useState8 = PDFCanvasEditor_slicedToArray(_useState7, 2),
    showPDFModal = _useState8[0],
    setShowPDFModal = _useState8[1];
  var _useState9 = (0,react.useState)(null),
    _useState0 = PDFCanvasEditor_slicedToArray(_useState9, 2),
    pdfModalUrl = _useState0[0],
    setPdfModalUrl = _useState0[1];
  var _useState1 = (0,react.useState)(false),
    _useState10 = PDFCanvasEditor_slicedToArray(_useState1, 2),
    isPropertiesCollapsed = _useState10[0],
    setIsPropertiesCollapsed = _useState10[1];

  // États pour le pan et la navigation
  var _useState11 = (0,react.useState)({
      x: 0,
      y: 0
    }),
    _useState12 = PDFCanvasEditor_slicedToArray(_useState11, 2),
    panOffset = _useState12[0],
    setPanOffset = _useState12[1];
  var _useState13 = (0,react.useState)(false),
    _useState14 = PDFCanvasEditor_slicedToArray(_useState13, 2),
    isPanning = _useState14[0],
    setIsPanning = _useState14[1];
  var _useState15 = (0,react.useState)({
      x: 0,
      y: 0
    }),
    _useState16 = PDFCanvasEditor_slicedToArray(_useState15, 2),
    lastPanPoint = _useState16[0],
    setLastPanPoint = _useState16[1];

  // États pour les guides
  var _useState17 = (0,react.useState)({
      horizontal: [],
      vertical: []
    }),
    _useState18 = PDFCanvasEditor_slicedToArray(_useState17, 2),
    guides = _useState18[0],
    setGuides = _useState18[1];
  var _useState19 = (0,react.useState)(false),
    _useState20 = PDFCanvasEditor_slicedToArray(_useState19, 2),
    isCreatingGuide = _useState20[0],
    setIsCreatingGuide = _useState20[1];
  var _useState21 = (0,react.useState)(null),
    _useState22 = PDFCanvasEditor_slicedToArray(_useState21, 2),
    guideCreationType = _useState22[0],
    setGuideCreationType = _useState22[1]; // 'horizontal' or 'vertical'

  // Hook pour les paramètres globaux
  var globalSettings = (0,useGlobalSettings/* useGlobalSettings */.k)();

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
  var canvasState = (0,useCanvasState/* useCanvasState */.v)({
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

  // Fonctions Undo/Redo
  var handleUndo = (0,react.useCallback)(function () {
    var previousState = canvasState.history.undo();
    if (previousState) {
      canvasState.setElements(previousState);
    }
  }, [canvasState]);
  var handleRedo = (0,react.useCallback)(function () {
    var nextState = canvasState.history.redo();
    if (nextState) {
      canvasState.setElements(nextState);
    }
  }, [canvasState]);

  // Handler pour créer un nouveau template
  var handleCreateTemplate = (0,react.useCallback)(function (templateData) {
    // TODO: Implémenter la logique de création du template
    // Pour l'instant, on ferme juste la modale et on affiche les données
    console.log('Création du template:', templateData);
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

  // Hook pour le drag and drop
  var dragAndDrop = (0,useDragAndDrop/* useDragAndDrop */.g)({
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
  (0,useKeyboardShortcuts/* useKeyboardShortcuts */.K)({
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
          // Ouvrir le PDF dans une modale
          // console.log('Ouverture du PDF dans une modale...');
          setPdfModalUrl(pdfDataUrl);
          setShowPDFModal(true);
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
    className: "btn btn-secondary",
    onClick: function onClick() {
      return setShowPreviewModal(true);
    }
  }, "\uD83D\uDC41\uFE0F Aper\xE7u"), /*#__PURE__*/react.createElement("button", {
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
    canRedo: canvasState.history.canRedo(),
    onPreview: function onPreview() {
      return setShowPreviewModal(true);
    }
  }), /*#__PURE__*/react.createElement("main", {
    className: "editor-workspace"
  }, !showPreviewModal && /*#__PURE__*/react.createElement("aside", {
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
  })))), !showPreviewModal && /*#__PURE__*/react.createElement("aside", {
    className: "editor-sidebar right-sidebar ".concat(isPropertiesCollapsed ? 'collapsed' : '')
  }, !isPropertiesCollapsed && /*#__PURE__*/react.createElement(components_PropertiesPanel, {
    selectedElements: canvasState.selection.selectedElements,
    elements: canvasState.elements,
    onPropertyChange: handlePropertyChange,
    onBatchUpdate: handleBatchUpdate
  }))), !showPreviewModal && /*#__PURE__*/react.createElement("button", {
    className: "sidebar-toggle-fixed",
    onClick: function onClick() {
      return setIsPropertiesCollapsed(!isPropertiesCollapsed);
    },
    title: isPropertiesCollapsed ? 'Agrandir le panneau' : 'Réduire le panneau',
    style: {
      position: 'fixed',
      top: '50%',
      right: isPropertiesCollapsed ? '80px' : '420px',
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
  }, /*#__PURE__*/react.createElement("span", null, "\xC9l\xE9ments: ", canvasState.elements.length), /*#__PURE__*/react.createElement("span", null, "|"), globalSettings.settings.showZoomIndicator && /*#__PURE__*/react.createElement(react.Fragment, null, /*#__PURE__*/react.createElement("span", null, "Zoom: ", Math.round(canvasState.zoom.zoom * 100), "%"), /*#__PURE__*/react.createElement("span", null, "|")), /*#__PURE__*/react.createElement("span", null, "Outil: ", tool), canvasState.selection.selectedElements.length > 0 && /*#__PURE__*/react.createElement(react.Fragment, null, /*#__PURE__*/react.createElement("span", null, "|"), /*#__PURE__*/react.createElement("span", null, "\xC9l\xE9ments s\xE9lectionn\xE9s: ", canvasState.selection.selectedElements.length))), /*#__PURE__*/react.createElement(PreviewModal/* default */.A, {
    isOpen: showPreviewModal,
    onClose: function onClose() {
      setShowPreviewModal(false);
    },
    elements: canvasState.elements,
    canvasWidth: canvasState.canvasWidth,
    canvasHeight: canvasState.canvasHeight,
    ajaxurl: (_window$pdfBuilderAja3 = window.pdfBuilderAjax) === null || _window$pdfBuilderAja3 === void 0 ? void 0 : _window$pdfBuilderAja3.ajaxurl,
    pdfBuilderNonce: (_window$pdfBuilderAja4 = window.pdfBuilderAjax) === null || _window$pdfBuilderAja4 === void 0 ? void 0 : _window$pdfBuilderAja4.nonce,
    useServerPreview: false,
    onOpenPDFModal: function onOpenPDFModal(pdfUrl) {
      setPdfModalUrl(pdfUrl);
      setShowPDFModal(true);
      setShowPreviewModal(false);
    }
  }), /*#__PURE__*/react.createElement(components_ModalPDFViewer, {
    isOpen: showPDFModal,
    onClose: function onClose() {
      setShowPDFModal(false);
      if (pdfModalUrl && pdfModalUrl.startsWith('blob:')) {
        setTimeout(function () {
          URL.revokeObjectURL(pdfModalUrl);
        }, 100);
      }
      setPdfModalUrl(null);
    },
    pdfUrl: pdfModalUrl,
    title: "PDF G\xE9n\xE9r\xE9"
  }), /*#__PURE__*/react.createElement(NewTemplateModal/* default */.A, {
    isOpen: showNewTemplateModal,
    onClose: function onClose() {
      return setShowNewTemplateModal(false);
    },
    onCreateTemplate: handleCreateTemplate
  }), /*#__PURE__*/react.createElement(FPSCounter, {
    showFps: globalSettings.settings.showFps
  }));
};

// Optimisation : éviter les re-renders inutiles
/* harmony default export */ const components_PDFCanvasEditor = (/*#__PURE__*/(/* unused pure expression or super */ null && (React.memo(PDFCanvasEditor))));
// EXTERNAL MODULE: ./src/hooks/index.js
var hooks = __webpack_require__(149);
;// ./src/index.js
function src_typeof(o) { "@babel/helpers - typeof"; return src_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, src_typeof(o); }
function src_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function src_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? src_ownKeys(Object(t), !0).forEach(function (r) { src_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : src_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function src_defineProperty(e, r, t) { return (r = src_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, src_toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function src_toPropertyKey(t) { var i = src_toPrimitive(t, "string"); return "symbol" == src_typeof(i) ? i : i + ""; }
function src_toPrimitive(t, r) { if ("object" != src_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != src_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }





// Exposer React globalement pour compatibilité
if (typeof window !== 'undefined') {
  window.React = react;
  window.ReactDOM = react_dom;
  console.log('PDF Builder Pro: Script loaded, React exposed globally');
}

// Forcer l'inclusion de tous les hooks personnalisés

try {
  // Classe principale pour l'éditeur PDF
  var PDFBuilderPro = /*#__PURE__*/function () {
    function PDFBuilderPro() {
      _classCallCheck(this, PDFBuilderPro);
      this.version = '2.0.0';
      this.editors = new Map();

      // Forcer l'inclusion des hooks (ne pas supprimer cette ligne)
      this._hooks = hooks;

      // Références explicites pour forcer l'inclusion
      this._forceInclude = {
        useHistory: hooks.useHistory,
        useRotation: hooks.useRotation,
        useResize: hooks.useResize
      };

      // Forcer l'appel des hooks pour éviter le tree shaking
      try {
        var dummyHistory = hooks.useHistory();
        var dummyRotation = hooks.useRotation(function () {});
        var dummyResize = hooks.useResize();
        this._dummyInstances = {
          dummyHistory: dummyHistory,
          dummyRotation: dummyRotation,
          dummyResize: dummyResize
        };
      } catch (e) {
        // Ignorer les erreurs en mode SSR
      }
    }

    // Initialiser l'éditeur dans un conteneur
    return _createClass(PDFBuilderPro, [{
      key: "init",
      value: function init(containerId) {
        var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        var container = document.getElementById(containerId);
        if (!container) {
          return;
        }

        // Options par défaut
        var defaultOptions = src_objectSpread({
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

        // Créer l'éditeur React
        var editorElement = /*#__PURE__*/(0,react.createElement)(PDFCanvasEditor, {
          options: defaultOptions
        });
        render(editorElement, container);
        this.editors.set(containerId, {
          container: container,
          options: defaultOptions
        });
      }

      // Détruire un éditeur
    }, {
      key: "destroy",
      value: function destroy(containerId) {
        var editor = this.editors.get(containerId);
        if (editor) {
          unmountComponentAtNode(editor.container);
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
    }]);
  }(); // Instance globale
  var pdfBuilderPro = new PDFBuilderPro();
  console.log('PDF Builder Pro: PDFBuilderPro instance created');

  // Attacher à window pour WordPress - avec vérification
  if (typeof window !== 'undefined') {
    window.PDFBuilderPro = pdfBuilderPro;
    // Alias pour compatibilité
    window.pdfBuilderPro = pdfBuilderPro;
    console.log('PDF Builder Pro: PDFBuilderPro attached to window');
  }
} catch (error) {
  // Tenter de définir quand même une version basique
  window.PDFBuilderPro = {
    init: function init() {
      return null;
    },
    version: 'error'
  };
  window.pdfBuilderPro = window.PDFBuilderPro;
}

// Export pour les modules ES6
/* harmony default export */ const src = ((/* unused pure expression or super */ null && (React)));

/***/ }),

/***/ 779:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Dw: () => (/* binding */ getPropertyDefault),
/* harmony export */   Qn: () => (/* binding */ isPropertyAllowed),
/* harmony export */   qb: () => (/* binding */ validateProperty),
/* harmony export */   uF: () => (/* binding */ fixInvalidProperty),
/* harmony export */   xG: () => (/* binding */ ELEMENT_TYPE_MAPPING)
/* harmony export */ });
/* unused harmony export ELEMENT_PROPERTY_RESTRICTIONS */
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
/******/ 		var chunkLoadingGlobal = self["webpackChunkwp_pdf_builder_pro"] = self["webpackChunkwp_pdf_builder_pro"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, [341,738,121], () => (__webpack_require__(320)))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;