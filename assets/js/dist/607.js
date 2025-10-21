"use strict";
(self["webpackChunkPDFBuilderPro"] = self["webpackChunkPDFBuilderPro"] || []).push([[607],{

/***/ 607:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, {
  "default": () => (/* binding */ CanvasMode_new)
});

// NAMESPACE OBJECT: ./resources/js/components/preview-system/SimplePreviewSystem_v3.jsx
var SimplePreviewSystem_v3_namespaceObject = {};
__webpack_require__.r(SimplePreviewSystem_v3_namespaceObject);
__webpack_require__.d(SimplePreviewSystem_v3_namespaceObject, {
  "default": () => (SimplePreviewSystem_v3),
  useSimplePreview: () => (useSimplePreview)
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
 * SYSTÈME D'APERÇU V3.0 - VERSION NETTOYÉE
 * Simplifié au maximum - aucune complication
 */

// Hook d'aperçu principal - SEUL export qui compte
function useSimplePreview() {
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

  // Dimensions fixes
  var canvasWidth = state.templateData.width;
  var canvasHeight = state.templateData.height;
  var containerWidth = 800;
  var containerHeight = 600;

  // Calcul d'échelle simple
  var scaleX = containerWidth / canvasWidth;
  var scaleY = containerHeight / canvasHeight;
  var actualScale = Math.min(scaleX, scaleY, 1);
  var displayWidth = canvasWidth * actualScale;
  var displayHeight = canvasHeight * actualScale;

  // Styles pré-calculés
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

  // Fonction de rendu simplifié
  var renderElements = react.useCallback(function () {
    if (!state.elements || state.elements.length === 0) {
      return null;
    }
    return state.elements.map(function (element) {
      if (!element) return null;
      var elementStyle = {
        position: 'absolute',
        left: (element.x || 0) * 1,
        top: (element.y || 0) * 1,
        width: (element.width || 100) * 1,
        height: (element.height || 100) * 1,
        boxSizing: 'border-box',
        overflow: 'hidden',
        backgroundColor: element.backgroundColor || 'transparent',
        border: element.borderWidth ? "".concat(element.borderWidth, "px solid ").concat(element.borderColor || '#000') : 'none',
        fontSize: "".concat(element.fontSize || 14, "px"),
        color: element.color || '#000000',
        padding: element.padding || '5px',
        display: 'flex',
        alignItems: 'center',
        justifyContent: element.textAlign === 'center' ? 'center' : 'flex-start'
      };
      return /*#__PURE__*/react.createElement("div", {
        key: element.id || Math.random(),
        style: elementStyle,
        "data-element-id": element.id,
        "data-element-type": element.type
      }, element.content || element.text || element.value || '');
    });
  }, [state.elements]);
  return {
    elements: state.elements,
    templateData: state.templateData,
    previewData: state.previewData,
    scale: state.scale,
    zoom: state.zoom,
    isFullscreen: state.isFullscreen,
    actualScale: actualScale,
    canvasWidth: canvasWidth,
    canvasHeight: canvasHeight,
    displayWidth: displayWidth,
    displayHeight: displayHeight,
    containerStyle: containerStyle,
    canvasStyle: canvasStyle,
    canvasWrapperStyle: canvasWrapperStyle,
    renderElements: renderElements,
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
/* harmony default export */ const SimplePreviewSystem_v3 = ({
  useSimplePreview: useSimplePreview
});
;// ./resources/js/components/preview-system/index_v3.js
function index_v3_typeof(o) { "@babel/helpers - typeof"; return index_v3_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, index_v3_typeof(o); }
function index_v3_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function index_v3_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? index_v3_ownKeys(Object(t), !0).forEach(function (r) { index_v3_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : index_v3_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function index_v3_defineProperty(e, r, t) { return (r = index_v3_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function index_v3_toPropertyKey(t) { var i = index_v3_toPrimitive(t, "string"); return "symbol" == index_v3_typeof(i) ? i : i + ""; }
function index_v3_toPrimitive(t, r) { if ("object" != index_v3_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != index_v3_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function index_v3_slicedToArray(r, e) { return index_v3_arrayWithHoles(r) || index_v3_iterableToArrayLimit(r, e) || index_v3_unsupportedIterableToArray(r, e) || index_v3_nonIterableRest(); }
function index_v3_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function index_v3_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return index_v3_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? index_v3_arrayLikeToArray(r, a) : void 0; } }
function index_v3_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function index_v3_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function index_v3_arrayWithHoles(r) { if (Array.isArray(r)) return r; }
/**
 * SYSTÈME D'APERÇU ULTRA-SIMPLE - VERSION 3.0
 * Export principal pour le nouveau système d'aperçu
 */

// Import du système ultra-simple


// Hook pour intégrer facilement le système
function index_v3_useSimplePreview() {
  var _React$useState = React.useState({
      isOpen: false,
      elements: [],
      templateWidth: 595,
      templateHeight: 842,
      title: 'Aperçu PDF'
    }),
    _React$useState2 = index_v3_slicedToArray(_React$useState, 2),
    previewState = _React$useState2[0],
    setPreviewState = _React$useState2[1];
  var openPreview = React.useCallback(function (config) {
    setPreviewState({
      isOpen: true,
      elements: config.elements || [],
      templateWidth: config.templateWidth || 595,
      templateHeight: config.templateHeight || 842,
      title: config.title || 'Aperçu PDF'
    });
  }, []);
  var closePreview = React.useCallback(function () {
    setPreviewState(function (prev) {
      return index_v3_objectSpread(index_v3_objectSpread({}, prev), {}, {
        isOpen: false
      });
    });
  }, []);
  return {
    isOpen: previewState.isOpen,
    openPreview: openPreview,
    closePreview: closePreview,
    PreviewModal: function PreviewModal() {
      return /*#__PURE__*/React.createElement(SimplePreviewSystem_v3_namespaceObject.SimplePreviewModal, {
        isOpen: previewState.isOpen,
        onClose: closePreview,
        elements: previewState.elements,
        templateWidth: previewState.templateWidth,
        templateHeight: previewState.templateHeight,
        title: previewState.title
      });
    }
  };
}

// Composant de test autonome
function PreviewSystemTestV3() {
  return /*#__PURE__*/React.createElement(SimplePreviewSystem_v3_namespaceObject.SimplePreviewTest, null);
}

// Export de tous les composants


// Export par défaut
/* harmony default export */ const index_v3 = ({
  SimpleCanvasPreview: SimplePreviewSystem_v3_namespaceObject.SimpleCanvasPreview,
  SimplePreviewModal: SimplePreviewSystem_v3_namespaceObject.SimplePreviewModal,
  SimplePreviewTest: SimplePreviewSystem_v3_namespaceObject.SimplePreviewTest,
  useSimplePreview: index_v3_useSimplePreview,
  PreviewSystemTestV3: PreviewSystemTestV3
});
;// ./resources/js/components/preview-system/modes/CanvasMode_new.jsx



/**
 * Mode Canvas - Version 3.0 ultra-simple
 * Système d'aperçu avec calculs mathématiques parfaits
 */

function CanvasMode() {
  var _useSimplePreview = index_v3_useSimplePreview(),
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