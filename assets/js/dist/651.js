"use strict";
(self["webpackChunkPDFBuilderPro"] = self["webpackChunkPDFBuilderPro"] || []).push([[651],{

/***/ 651:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, {
  "default": () => (/* binding */ CanvasMode_new)
});

// EXTERNAL MODULE: ./node_modules/react/index.js
var react = __webpack_require__(540);
;// ./resources/js/components/preview-system/SimplePreviewSystem_v3_TEST.jsx


// TEST SIMPLE - Export direct
function useSimplePreview() {
  console.log('[TEST] useSimplePreview called');
  return {
    elements: [],
    templateData: {
      width: 595,
      height: 842
    },
    previewData: {},
    scale: 0.8,
    zoom: 1,
    isFullscreen: false,
    actualScale: 0.8,
    canvasWidth: 595,
    canvasHeight: 842,
    displayWidth: 476,
    displayHeight: 673.6,
    containerStyle: {
      width: '100%',
      height: '100%'
    },
    canvasStyle: {
      width: 595,
      height: 842,
      backgroundColor: '#fff'
    },
    canvasWrapperStyle: {
      width: 476,
      height: 673.6
    },
    renderElements: function renderElements() {
      return /*#__PURE__*/react.createElement('div', {}, 'Test elements');
    },
    setElements: function setElements() {},
    setTemplateData: function setTemplateData() {},
    setPreviewData: function setPreviewData() {},
    setScale: function setScale() {},
    setZoom: function setZoom() {},
    setFullscreen: function setFullscreen() {}
  };
}
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