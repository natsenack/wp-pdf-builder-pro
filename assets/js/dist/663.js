"use strict";
(self["webpackChunkPDFBuilderPro"] = self["webpackChunkPDFBuilderPro"] || []).push([[663],{

/***/ 663:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, {
  "default": () => (/* binding */ CanvasMode_new)
});

// EXTERNAL MODULE: ./node_modules/react/index.js
var react = __webpack_require__(540);
;// ./resources/js/components/preview-system/SimplePreviewSystem_v3.jsx
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
 * SYSTÈME D'APERÇU COMPLETEMENT RECONSTRUIT - VERSION 3.0
 * Architecture ultra-simple et robuste pour éviter tous les problèmes
 */

// =============================================================================
// 1. SYSTÈME DE POSITIONNEMENT ULTRA-SIMPLE
// =============================================================================

/**
 * Hook principal pour le système d'aperçu v3.0
 * Fournit toutes les données et fonctions nécessaires à CanvasMode
 */
function useSimplePreview() {
  // État du système d'aperçu
  var _React$useState = react.useState({
      elements: [],
      templateData: {
        width: 595,
        height: 842,
        orientation: 'Portrait'
      },
      previewData: {},
      scale: 0.8,
      zoom: 1,
      isFullscreen: false
    }),
    _React$useState2 = _slicedToArray(_React$useState, 2),
    state = _React$useState2[0],
    setState = _React$useState2[1];

  // Calculs des dimensions
  var canvasWidth = state.templateData.width;
  var canvasHeight = state.templateData.height;
  var containerWidth = 800;
  var containerHeight = 600;
  var scaling = usePreviewScaling(canvasWidth, canvasHeight, containerWidth, containerHeight);
  var actualScale = scaling.scale;
  var displayWidth = scaling.displayWidth;
  var displayHeight = scaling.displayHeight;

  // Styles
  var containerStyle = {
    width: '100%',
    height: '100%',
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
    justifyContent: 'flex-start',
    padding: '20px',
    backgroundColor: '#f5f5f7',
    overflow: 'auto'
  };
  var canvasStyle = {
    width: canvasWidth,
    height: canvasHeight,
    backgroundColor: '#ffffff',
    position: 'relative',
    boxShadow: '0 8px 32px rgba(0, 0, 0, 0.12)',
    border: '1px solid #e5e5e7',
    borderRadius: '8px',
    overflow: 'hidden',
    transform: "scale(".concat(actualScale, ")"),
    transformOrigin: 'top center',
    margin: "".concat(20 / actualScale, "px auto")
  };
  var canvasWrapperStyle = {
    width: displayWidth,
    height: displayHeight,
    margin: '20px auto',
    position: 'relative'
  };

  // Fonction pour rendre les éléments
  var renderElements = react.useCallback(function () {
    console.log('[PREVIEW V3.0] Rendering elements:', state.elements.length);
    return state.elements.map(function (element) {
      console.log('[ELEMENT POSITION]', element.type, 'at', element.x, element.y);
      return /*#__PURE__*/react.createElement(SimpleElementRenderer, {
        key: element.id,
        element: element,
        previewData: state.previewData,
        scale: 1
      });
    });
  }, [state.elements, state.previewData]);
  return {
    // Données
    elements: state.elements,
    templateData: state.templateData,
    previewData: state.previewData,
    scale: state.scale,
    zoom: state.zoom,
    isFullscreen: state.isFullscreen,
    // Calculs
    actualScale: actualScale,
    canvasWidth: canvasWidth,
    canvasHeight: canvasHeight,
    displayWidth: displayWidth,
    displayHeight: displayHeight,
    // Styles
    containerStyle: containerStyle,
    canvasStyle: canvasStyle,
    canvasWrapperStyle: canvasWrapperStyle,
    // Fonctions
    renderElements: renderElements,
    // Setters pour mettre à jour l'état (utiles pour l'intégration)
    setElements: function setElements(elements) {
      return setState(function (prev) {
        return _objectSpread(_objectSpread({}, prev), {}, {
          elements: elements
        });
      });
    },
    setTemplateData: function setTemplateData(templateData) {
      return setState(function (prev) {
        return _objectSpread(_objectSpread({}, prev), {}, {
          templateData: templateData
        });
      });
    },
    setPreviewData: function setPreviewData(previewData) {
      return setState(function (prev) {
        return _objectSpread(_objectSpread({}, prev), {}, {
          previewData: previewData
        });
      });
    },
    setScale: function setScale(scale) {
      return setState(function (prev) {
        return _objectSpread(_objectSpread({}, prev), {}, {
          scale: scale
        });
      });
    },
    setZoom: function setZoom(zoom) {
      return setState(function (prev) {
        return _objectSpread(_objectSpread({}, prev), {}, {
          zoom: zoom
        });
      });
    },
    setFullscreen: function setFullscreen(isFullscreen) {
      return setState(function (prev) {
        return _objectSpread(_objectSpread({}, prev), {}, {
          isFullscreen: isFullscreen
        });
      });
    }
  };
}

/**
 * Hook pour calculer les dimensions et l'échelle de l'aperçu
 */
function usePreviewScaling(templateWidth, templateHeight) {
  var containerWidth = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 800;
  var containerHeight = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 600;
  return react.useMemo(function () {
    // Calcul de l'échelle pour que le template tienne dans le conteneur
    var scaleX = containerWidth / templateWidth;
    var scaleY = containerHeight / templateHeight;
    var scale = Math.min(scaleX, scaleY, 1); // Ne pas agrandir

    return {
      scale: scale,
      displayWidth: templateWidth * scale,
      displayHeight: templateHeight * scale,
      templateWidth: templateWidth,
      templateHeight: templateHeight
    };
  }, [templateWidth, templateHeight, containerWidth, containerHeight]);
}

/**
 * Composant de base pour positionner un élément
 */
function PositionedElement(_ref) {
  var element = _ref.element,
    scale = _ref.scale,
    children = _ref.children,
    _ref$className = _ref.className,
    className = _ref$className === void 0 ? '' : _ref$className;
  var style = {
    position: 'absolute',
    left: element.x * scale,
    top: element.y * scale,
    width: element.width * scale,
    height: element.height * scale,
    // Styles de base pour éviter les conflits
    boxSizing: 'border-box',
    overflow: 'hidden'
  };
  return /*#__PURE__*/react.createElement("div", {
    className: "preview-element ".concat(className),
    style: style,
    "data-element-id": element.id,
    "data-element-type": element.type
  }, children);
}

// =============================================================================
// 2. RENDERERS ULTRA-SIMPLES POUR CHAQUE TYPE D'ÉLÉMENT
// =============================================================================

/**
 * Renderer pour le texte - Version ultra-simple
 */
function SimpleTextRenderer(_ref2) {
  var element = _ref2.element,
    scale = _ref2.scale;
  var fontSize = (element.fontSize || 14) * scale;
  var lineHeight = element.lineHeight || 1.2;
  return /*#__PURE__*/react.createElement(PositionedElement, {
    element: element,
    scale: scale,
    className: "text-element"
  }, /*#__PURE__*/react.createElement("div", {
    style: {
      width: '100%',
      height: '100%',
      fontSize: "".concat(fontSize, "px"),
      fontFamily: element.fontFamily || 'Arial',
      fontWeight: element.fontWeight || 'normal',
      fontStyle: element.fontStyle || 'normal',
      color: element.color || '#000000',
      textAlign: element.textAlign || 'left',
      lineHeight: lineHeight,
      backgroundColor: element.backgroundColor || 'transparent',
      border: element.borderWidth ? "".concat(element.borderWidth * scale, "px solid ").concat(element.borderColor || '#000') : 'none',
      borderRadius: "".concat((element.borderRadius || 0) * scale, "px"),
      padding: "".concat((element.padding || 0) * scale, "px"),
      display: 'flex',
      alignItems: element.textAlign === 'center' ? 'center' : element.textAlign === 'right' ? 'flex-end' : 'flex-start',
      justifyContent: 'flex-start',
      wordWrap: 'break-word',
      overflowWrap: 'break-word',
      whiteSpace: 'pre-wrap'
    }
  }, element.text || element.content || 'Texte'));
}

/**
 * Renderer pour les rectangles/formes
 */
function SimpleRectangleRenderer(_ref3) {
  var _element$opacity;
  var element = _ref3.element,
    scale = _ref3.scale;
  return /*#__PURE__*/react.createElement(PositionedElement, {
    element: element,
    scale: scale,
    className: "rectangle-element"
  }, /*#__PURE__*/react.createElement("div", {
    style: {
      width: '100%',
      height: '100%',
      backgroundColor: element.backgroundColor || '#cccccc',
      border: element.borderWidth ? "".concat(element.borderWidth * scale, "px solid ").concat(element.borderColor || '#000') : 'none',
      borderRadius: "".concat((element.borderRadius || 0) * scale, "px"),
      opacity: (_element$opacity = element.opacity) !== null && _element$opacity !== void 0 ? _element$opacity : 1
    }
  }));
}

/**
 * Renderer pour les images
 */
function SimpleImageRenderer(_ref4) {
  var element = _ref4.element,
    scale = _ref4.scale;
  var _React$useState3 = react.useState(false),
    _React$useState4 = _slicedToArray(_React$useState3, 2),
    imageLoaded = _React$useState4[0],
    setImageLoaded = _React$useState4[1];
  var _React$useState5 = react.useState(false),
    _React$useState6 = _slicedToArray(_React$useState5, 2),
    imageError = _React$useState6[0],
    setImageError = _React$useState6[1];
  return /*#__PURE__*/react.createElement(PositionedElement, {
    element: element,
    scale: scale,
    className: "image-element"
  }, /*#__PURE__*/react.createElement("div", {
    style: {
      width: '100%',
      height: '100%',
      backgroundColor: '#f0f0f0',
      border: element.borderWidth ? "".concat(element.borderWidth * scale, "px solid ").concat(element.borderColor || '#000') : 'none',
      borderRadius: "".concat((element.borderRadius || 0) * scale, "px"),
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      overflow: 'hidden'
    }
  }, element.src || element.url ? /*#__PURE__*/react.createElement(react.Fragment, null, !imageError && /*#__PURE__*/react.createElement("img", {
    src: element.src || element.url,
    alt: element.alt || 'Image',
    style: {
      width: '100%',
      height: '100%',
      objectFit: element.objectFit || 'cover',
      objectPosition: element.objectPosition || 'center',
      opacity: imageLoaded ? 1 : 0,
      transition: 'opacity 0.2s ease'
    },
    onLoad: function onLoad() {
      return setImageLoaded(true);
    },
    onError: function onError() {
      return setImageError(true);
    }
  }), imageError && /*#__PURE__*/react.createElement("div", {
    style: {
      color: '#666',
      fontSize: "".concat(12 * scale, "px"),
      textAlign: 'center'
    }
  }, "\uD83D\uDDBC\uFE0F Image introuvable")) : /*#__PURE__*/react.createElement("div", {
    style: {
      color: '#999',
      fontSize: "".concat(12 * scale, "px"),
      textAlign: 'center'
    }
  }, "\uD83D\uDCF7 Aucune image")));
}

/**
 * Renderer pour les tableaux
 */
function SimpleTableRenderer(_ref5) {
  var element = _ref5.element,
    scale = _ref5.scale;
  var tableData = element.data || element.rows || [['Colonne 1', 'Colonne 2'], ['Donnée 1', 'Donnée 2']];
  var cellStyle = {
    border: "1px solid ".concat(element.borderColor || '#ddd'),
    padding: "".concat((element.cellPadding || 4) * scale, "px"),
    fontSize: "".concat((element.fontSize || 12) * scale, "px"),
    textAlign: element.textAlign || 'left'
  };
  return /*#__PURE__*/react.createElement(PositionedElement, {
    element: element,
    scale: scale,
    className: "table-element"
  }, /*#__PURE__*/react.createElement("div", {
    style: {
      width: '100%',
      height: '100%',
      backgroundColor: element.backgroundColor || 'transparent',
      border: element.borderWidth ? "".concat(element.borderWidth * scale, "px solid ").concat(element.borderColor || '#000') : 'none',
      borderRadius: "".concat((element.borderRadius || 0) * scale, "px"),
      padding: "".concat((element.padding || 0) * scale, "px"),
      overflow: 'hidden'
    }
  }, /*#__PURE__*/react.createElement("table", {
    style: {
      width: '100%',
      height: '100%',
      borderCollapse: 'collapse',
      fontSize: 'inherit',
      color: 'inherit'
    }
  }, /*#__PURE__*/react.createElement("tbody", null, tableData.map(function (row, rowIndex) {
    return /*#__PURE__*/react.createElement("tr", {
      key: rowIndex
    }, row.map(function (cell, cellIndex) {
      return /*#__PURE__*/react.createElement("td", {
        key: cellIndex,
        style: cellStyle
      }, cell);
    }));
  })))));
}

/**
 * Renderer générique pour éléments inconnus
 */
function SimpleUnknownRenderer(_ref6) {
  var element = _ref6.element,
    scale = _ref6.scale;
  return /*#__PURE__*/react.createElement(PositionedElement, {
    element: element,
    scale: scale,
    className: "unknown-element"
  }, /*#__PURE__*/react.createElement("div", {
    style: {
      width: '100%',
      height: '100%',
      backgroundColor: '#ffeaa7',
      border: '2px dashed #d63031',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      fontSize: "".concat(12 * scale, "px"),
      color: '#d63031',
      textAlign: 'center'
    }
  }, "\u2753 ", element.type || 'Inconnu'));
}

// =============================================================================
// 3. SYSTÈME DE RENDU PRINCIPAL ULTRA-SIMPLE
// =============================================================================

/**
 * Renderer universel qui route vers le bon renderer selon le type
 */
function SimpleElementRenderer(_ref7) {
  var element = _ref7.element,
    scale = _ref7.scale;
  // Logs de débogage pour voir exactement ce qui se passe
  console.log('🎨 Rendering element:', {
    id: element.id,
    type: element.type,
    x: element.x,
    y: element.y,
    width: element.width,
    height: element.height,
    scale: scale,
    displayX: element.x * scale,
    displayY: element.y * scale,
    displayWidth: element.width * scale,
    displayHeight: element.height * scale
  });
  switch (element.type) {
    case 'text':
      return /*#__PURE__*/react.createElement(SimpleTextRenderer, {
        element: element,
        scale: scale
      });
    case 'rectangle':
    case 'rect':
      return /*#__PURE__*/react.createElement(SimpleRectangleRenderer, {
        element: element,
        scale: scale
      });
    case 'image':
    case 'img':
      return /*#__PURE__*/react.createElement(SimpleImageRenderer, {
        element: element,
        scale: scale
      });
    case 'table':
      return /*#__PURE__*/react.createElement(SimpleTableRenderer, {
        element: element,
        scale: scale
      });
    default:
      return /*#__PURE__*/react.createElement(SimpleUnknownRenderer, {
        element: element,
        scale: scale
      });
  }
}

// =============================================================================
// 4. COMPOSANT D'APERÇU PRINCIPAL ULTRA-SIMPLE
// =============================================================================

/**
 * Composant d'aperçu principal - Version 3.0 ultra-simple
 */
function SimpleCanvasPreview(_ref8) {
  var _ref8$elements = _ref8.elements,
    elements = _ref8$elements === void 0 ? [] : _ref8$elements,
    _ref8$templateWidth = _ref8.templateWidth,
    templateWidth = _ref8$templateWidth === void 0 ? 595 : _ref8$templateWidth,
    _ref8$templateHeight = _ref8.templateHeight,
    templateHeight = _ref8$templateHeight === void 0 ? 842 : _ref8$templateHeight,
    _ref8$containerWidth = _ref8.containerWidth,
    containerWidth = _ref8$containerWidth === void 0 ? 800 : _ref8$containerWidth,
    _ref8$containerHeight = _ref8.containerHeight,
    containerHeight = _ref8$containerHeight === void 0 ? 600 : _ref8$containerHeight,
    _ref8$showDebug = _ref8.showDebug,
    showDebug = _ref8$showDebug === void 0 ? false : _ref8$showDebug;
  var _usePreviewScaling = usePreviewScaling(templateWidth, templateHeight, containerWidth, containerHeight),
    scale = _usePreviewScaling.scale,
    displayWidth = _usePreviewScaling.displayWidth,
    displayHeight = _usePreviewScaling.displayHeight;
  console.log('📐 Canvas Preview Config:', {
    templateWidth: templateWidth,
    templateHeight: templateHeight,
    containerWidth: containerWidth,
    containerHeight: containerHeight,
    scale: scale,
    displayWidth: displayWidth,
    displayHeight: displayHeight,
    elementsCount: elements.length
  });
  return /*#__PURE__*/react.createElement("div", {
    style: {
      width: '100%',
      height: '100%',
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'center',
      padding: '20px',
      backgroundColor: '#f8f9fa'
    }
  }, showDebug && /*#__PURE__*/react.createElement("div", {
    style: {
      marginBottom: '20px',
      padding: '12px 20px',
      backgroundColor: 'white',
      borderRadius: '8px',
      border: '1px solid #e9ecef',
      fontSize: '14px',
      color: '#495057',
      textAlign: 'center'
    }
  }, /*#__PURE__*/react.createElement("div", {
    style: {
      fontWeight: '600',
      marginBottom: '8px'
    }
  }, "\uD83D\uDD0D Debug Info"), /*#__PURE__*/react.createElement("div", null, "Template: ", templateWidth, "\xD7", templateHeight, " \u2022 Scale: ", (scale * 100).toFixed(1), "% \u2022 Elements: ", elements.length), /*#__PURE__*/react.createElement("div", null, "Display: ", displayWidth.toFixed(0), "\xD7", displayHeight.toFixed(0), "px")), /*#__PURE__*/react.createElement("div", {
    style: {
      position: 'relative',
      width: displayWidth,
      height: displayHeight,
      backgroundColor: 'white',
      border: '1px solid #dee2e6',
      borderRadius: '4px',
      boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
      overflow: 'hidden'
    }
  }, elements.map(function (element) {
    return /*#__PURE__*/react.createElement(SimpleElementRenderer, {
      key: element.id,
      element: element,
      scale: scale
    });
  }), elements.length === 0 && /*#__PURE__*/react.createElement("div", {
    style: {
      position: 'absolute',
      top: '50%',
      left: '50%',
      transform: 'translate(-50%, -50%)',
      textAlign: 'center',
      color: '#6c757d',
      fontSize: '16px'
    }
  }, /*#__PURE__*/react.createElement("div", {
    style: {
      fontSize: '48px',
      marginBottom: '16px'
    }
  }, "\uD83D\uDCC4"), /*#__PURE__*/react.createElement("div", null, "Aucun \xE9l\xE9ment dans l'aper\xE7u")), showDebug && /*#__PURE__*/react.createElement("svg", {
    style: {
      position: 'absolute',
      top: 0,
      left: 0,
      width: '100%',
      height: '100%',
      pointerEvents: 'none',
      opacity: 0.1
    }
  }, /*#__PURE__*/react.createElement("defs", null, /*#__PURE__*/react.createElement("pattern", {
    id: "debug-grid",
    width: "20",
    height: "20",
    patternUnits: "userSpaceOnUse"
  }, /*#__PURE__*/react.createElement("path", {
    d: "M 20 0 L 0 0 0 20",
    fill: "none",
    stroke: "#ff6b6b",
    strokeWidth: "1"
  }))), /*#__PURE__*/react.createElement("rect", {
    width: "100%",
    height: "100%",
    fill: "url(#debug-grid)"
  }))), /*#__PURE__*/react.createElement("div", {
    style: {
      marginTop: '20px',
      padding: '12px 20px',
      backgroundColor: 'white',
      borderRadius: '8px',
      border: '1px solid #e9ecef',
      fontSize: '13px',
      color: '#6c757d',
      textAlign: 'center'
    }
  }, /*#__PURE__*/react.createElement("div", null, "\uD83D\uDCCF Dimensions: ", templateWidth, " \xD7 ", templateHeight, " points"), /*#__PURE__*/react.createElement("div", null, "\uD83D\uDCD0 \xC9chelle: ", (scale * 100).toFixed(1), "% \u2022 Affichage: ", displayWidth.toFixed(0), " \xD7 ", displayHeight.toFixed(0), " px")));
}

// =============================================================================
// 5. MODAL D'APERÇU ULTRA-SIMPLE
// =============================================================================

/**
 * Modal d'aperçu ultra-simple
 */
function SimplePreviewModal(_ref9) {
  var isOpen = _ref9.isOpen,
    onClose = _ref9.onClose,
    _ref9$elements = _ref9.elements,
    elements = _ref9$elements === void 0 ? [] : _ref9$elements,
    _ref9$templateWidth = _ref9.templateWidth,
    templateWidth = _ref9$templateWidth === void 0 ? 595 : _ref9$templateWidth,
    _ref9$templateHeight = _ref9.templateHeight,
    templateHeight = _ref9$templateHeight === void 0 ? 842 : _ref9$templateHeight,
    _ref9$title = _ref9.title,
    title = _ref9$title === void 0 ? "Aperçu PDF" : _ref9$title;
  if (!isOpen) return null;
  return /*#__PURE__*/react.createElement("div", {
    style: {
      position: 'fixed',
      top: 0,
      left: 0,
      right: 0,
      bottom: 0,
      backgroundColor: 'rgba(0, 0, 0, 0.75)',
      backdropFilter: 'blur(4px)',
      zIndex: 10000,
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      padding: '20px'
    }
  }, /*#__PURE__*/react.createElement("div", {
    style: {
      width: '95%',
      height: '95%',
      maxWidth: '1200px',
      backgroundColor: 'white',
      borderRadius: '12px',
      boxShadow: '0 20px 40px rgba(0, 0, 0, 0.3)',
      display: 'flex',
      flexDirection: 'column',
      overflow: 'hidden'
    }
  }, /*#__PURE__*/react.createElement("div", {
    style: {
      padding: '20px 24px',
      borderBottom: '1px solid #e9ecef',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between',
      backgroundColor: '#f8f9fa'
    }
  }, /*#__PURE__*/react.createElement("h3", {
    style: {
      margin: 0,
      fontSize: '18px',
      fontWeight: '600',
      color: '#1f2937'
    }
  }, title), /*#__PURE__*/react.createElement("button", {
    onClick: onClose,
    style: {
      padding: '8px',
      border: 'none',
      borderRadius: '6px',
      backgroundColor: '#ef4444',
      color: 'white',
      cursor: 'pointer',
      fontSize: '16px',
      width: '36px',
      height: '36px',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center'
    }
  }, "\u2715")), /*#__PURE__*/react.createElement("div", {
    style: {
      flex: 1,
      overflow: 'auto'
    }
  }, /*#__PURE__*/react.createElement(SimpleCanvasPreview, {
    elements: elements,
    templateWidth: templateWidth,
    templateHeight: templateHeight,
    showDebug: true
  }))));
}

// =============================================================================
// 6. COMPOSANT DE TEST ULTRA-SIMPLE
// =============================================================================

/**
 * Composant de test avec des données d'exemple
 */
function SimplePreviewTest() {
  var _React$useState7 = react.useState(false),
    _React$useState8 = _slicedToArray(_React$useState7, 2),
    showPreview = _React$useState8[0],
    setShowPreview = _React$useState8[1];

  // Données de test ultra-simples
  var testElements = [{
    id: 1,
    type: 'text',
    x: 50,
    y: 50,
    width: 200,
    height: 40,
    text: 'Titre du document',
    fontSize: 18,
    fontWeight: 'bold',
    color: '#1f2937'
  }, {
    id: 2,
    type: 'rectangle',
    x: 50,
    y: 100,
    width: 300,
    height: 100,
    backgroundColor: '#3b82f6',
    borderRadius: 8
  }, {
    id: 3,
    type: 'text',
    x: 70,
    y: 120,
    width: 260,
    height: 60,
    text: 'Contenu dans le rectangle bleu',
    fontSize: 14,
    color: 'white',
    textAlign: 'center'
  }, {
    id: 4,
    type: 'image',
    x: 400,
    y: 50,
    width: 120,
    height: 120,
    src: 'https://via.placeholder.com/120x120/6366f1/white?text=LOGO',
    borderRadius: 8
  }, {
    id: 5,
    type: 'table',
    x: 50,
    y: 220,
    width: 400,
    height: 120,
    data: [['Produit', 'Quantité', 'Prix'], ['Article A', '2', '29,99 €'], ['Article B', '1', '15,50 €'], ['Total', '3', '45,49 €']],
    fontSize: 12
  }, {
    id: 6,
    type: 'rectangle',
    x: 100,
    y: 360,
    width: 150,
    height: 50,
    backgroundColor: '#10b981',
    borderRadius: 25
  }, {
    id: 7,
    type: 'text',
    x: 110,
    y: 370,
    width: 130,
    height: 30,
    text: 'Élément vert',
    fontSize: 14,
    color: 'white',
    textAlign: 'center'
  }];
  return /*#__PURE__*/react.createElement("div", {
    style: {
      padding: '20px'
    }
  }, /*#__PURE__*/react.createElement("h2", null, "\uD83E\uDDEA Test du syst\xE8me d'aper\xE7u ultra-simple v3.0"), /*#__PURE__*/react.createElement("p", null, "Cliquez pour voir l'aper\xE7u avec des \xE9l\xE9ments de test parfaitement positionn\xE9s."), /*#__PURE__*/react.createElement("button", {
    onClick: function onClick() {
      return setShowPreview(true);
    },
    style: {
      padding: '12px 24px',
      backgroundColor: '#3b82f6',
      color: 'white',
      border: 'none',
      borderRadius: '8px',
      fontSize: '16px',
      cursor: 'pointer',
      margin: '20px 0'
    }
  }, "\uD83D\uDD0D Ouvrir l'aper\xE7u de test"), /*#__PURE__*/react.createElement("div", {
    style: {
      marginTop: '20px',
      fontSize: '14px',
      color: '#666'
    }
  }, /*#__PURE__*/react.createElement("h3", null, "\xC9l\xE9ments de test inclus :"), /*#__PURE__*/react.createElement("ul", null, /*#__PURE__*/react.createElement("li", null, "\u2705 Texte avec diff\xE9rentes tailles et couleurs"), /*#__PURE__*/react.createElement("li", null, "\u2705 Rectangles avec coins arrondis"), /*#__PURE__*/react.createElement("li", null, "\u2705 Image avec placeholder"), /*#__PURE__*/react.createElement("li", null, "\u2705 Tableau avec donn\xE9es"), /*#__PURE__*/react.createElement("li", null, "\u2705 Positionnement pr\xE9cis \xE0 des coordonn\xE9es sp\xE9cifiques"), /*#__PURE__*/react.createElement("li", null, "\u2705 \xC9chelle automatique pour s'adapter \xE0 la fen\xEAtre"))), /*#__PURE__*/react.createElement(SimplePreviewModal, {
    isOpen: showPreview,
    onClose: function onClose() {
      return setShowPreview(false);
    },
    elements: testElements,
    templateWidth: 595,
    templateHeight: 842,
    title: "Aper\xE7u de test - Version 3.0"
  }));
}
/* harmony default export */ const SimplePreviewSystem_v3 = ({
  SimpleCanvasPreview: SimpleCanvasPreview,
  SimplePreviewModal: SimplePreviewModal,
  SimplePreviewTest: SimplePreviewTest,
  SimpleElementRenderer: SimpleElementRenderer,
  usePreviewScaling: usePreviewScaling,
  useSimplePreview: useSimplePreview
});
;// ./resources/js/components/preview-system/modes/CanvasMode_new.jsx



/**
 * Mode Canvas - Version 3.0 ultra-simple
 * Système d'aperçu avec calculs mathématiques parfaits
 */

function CanvasMode() {
  var _useSimplePreview = useSimplePreview(),
    elements = _useSimplePreview.elements,
    templateData = _useSimplePreview.templateData,
    previewData = _useSimplePreview.previewData,
    scale = _useSimplePreview.scale,
    zoom = _useSimplePreview.zoom,
    isFullscreen = _useSimplePreview.isFullscreen,
    actualScale = _useSimplePreview.actualScale,
    canvasWidth = _useSimplePreview.canvasWidth,
    canvasHeight = _useSimplePreview.canvasHeight,
    displayWidth = _useSimplePreview.displayWidth,
    displayHeight = _useSimplePreview.displayHeight,
    containerStyle = _useSimplePreview.containerStyle,
    canvasStyle = _useSimplePreview.canvasStyle,
    canvasWrapperStyle = _useSimplePreview.canvasWrapperStyle,
    renderElements = _useSimplePreview.renderElements;
  return /*#__PURE__*/react.createElement("div", {
    style: containerStyle,
    className: "canvas-mode-container"
  }, /*#__PURE__*/react.createElement("div", {
    style: {
      marginBottom: '20px',
      padding: '12px 20px',
      backgroundColor: 'white',
      borderRadius: '8px',
      border: '1px solid #e5e5e7',
      fontSize: '14px',
      color: '#1d1d1f',
      display: 'flex',
      alignItems: 'center',
      gap: '20px',
      minWidth: '300px',
      justifyContent: 'center'
    }
  }, /*#__PURE__*/react.createElement("span", null, "\uD83D\uDCC4 ", canvasWidth, " \xD7 ", canvasHeight, " points"), /*#__PURE__*/react.createElement("span", null, "|"), /*#__PURE__*/react.createElement("span", null, "\uD83D\uDD0D ", Math.round(actualScale * 100), "%"), /*#__PURE__*/react.createElement("span", null, "|"), /*#__PURE__*/react.createElement("span", null, "\uD83D\uDCE6 ", elements.length, " \xE9l\xE9ments"), previewData && Object.keys(previewData).length > 0 && /*#__PURE__*/react.createElement(react.Fragment, null, /*#__PURE__*/react.createElement("span", null, "|"), /*#__PURE__*/react.createElement("span", {
    style: {
      color: '#34c759'
    }
  }, "\u2713 Donn\xE9es inject\xE9es"))), /*#__PURE__*/react.createElement("div", {
    style: canvasWrapperStyle
  }, /*#__PURE__*/react.createElement("div", {
    style: canvasStyle,
    className: "preview-canvas"
  }, renderElements(), elements.length === 0 && /*#__PURE__*/react.createElement("div", {
    style: {
      position: 'absolute',
      top: '50%',
      left: '50%',
      transform: 'translate(-50%, -50%)',
      textAlign: 'center',
      color: '#8e8e93',
      fontSize: '16px',
      fontWeight: '500'
    }
  }, /*#__PURE__*/react.createElement("div", {
    style: {
      fontSize: '48px',
      marginBottom: '16px'
    }
  }, "\uD83D\uDCC4"), /*#__PURE__*/react.createElement("div", null, "Canvas vide"), /*#__PURE__*/react.createElement("div", {
    style: {
      fontSize: '14px',
      marginTop: '8px',
      fontWeight: '400'
    }
  }, "Ajoutez des \xE9l\xE9ments dans l'\xE9diteur pour les voir ici")),  false && /*#__PURE__*/0)), /*#__PURE__*/react.createElement("div", {
    style: {
      marginTop: '20px',
      padding: '16px 20px',
      backgroundColor: 'white',
      borderRadius: '8px',
      border: '1px solid #e5e5e7',
      fontSize: '13px',
      color: '#6e6e73',
      maxWidth: '600px',
      textAlign: 'center',
      lineHeight: '1.5'
    }
  }, /*#__PURE__*/react.createElement("div", {
    style: {
      marginBottom: '8px',
      fontWeight: '600',
      color: '#1d1d1f'
    }
  }, "\uD83D\uDCCA D\xE9tails de l'aper\xE7u"), /*#__PURE__*/react.createElement("div", {
    style: {
      display: 'flex',
      justifyContent: 'center',
      gap: '20px',
      flexWrap: 'wrap'
    }
  }, /*#__PURE__*/react.createElement("span", null, "Format: ", templateData.orientation || 'Portrait'), /*#__PURE__*/react.createElement("span", null, "\xC9chelle: ", Math.round(scale * 100), "%"), /*#__PURE__*/react.createElement("span", null, "Zoom: ", Math.round(zoom * 100), "%"), /*#__PURE__*/react.createElement("span", null, "R\xE9solution: ", Math.round(canvasWidth * 0.3528), " \xD7 ", Math.round(canvasHeight * 0.3528), " mm"))));
}
/* harmony default export */ const CanvasMode_new = (CanvasMode);

/***/ })

}]);