"use strict";
(self["webpackChunkPDFBuilderPro"] = self["webpackChunkPDFBuilderPro"] || []).push([[555],{

/***/ 2544:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   ImageRenderer: () => (/* binding */ ImageRenderer)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(6540);
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
    mode = _ref.mode,
    _ref$canvasScale = _ref.canvasScale,
    canvasScale = _ref$canvasScale === void 0 ? 1 : _ref$canvasScale;
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
    left: "".concat(x * canvasScale, "px"),
    top: "".concat(y * canvasScale, "px"),
    width: "".concat(width * canvasScale, "px"),
    height: "".concat(height * canvasScale, "px"),
    backgroundColor: backgroundColor,
    border: borderWidth > 0 ? "".concat(borderWidth, "px solid ").concat(borderColor) : 'none',
    borderRadius: "".concat(borderRadius, "px"),
    opacity: opacity,
    display: visible ? 'flex' : 'none',
    alignItems: 'center',
    justifyContent: 'center',
    boxSizing: 'border-box',
    overflow: 'hidden',
    transform: "rotate(".concat(rotation, "deg) scale(").concat(scale, ")"),
    transformOrigin: 'top left',
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

/***/ 9555:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, {
  "default": () => (/* binding */ modes_CanvasMode)
});

// EXTERNAL MODULE: ./node_modules/react/index.js
var react = __webpack_require__(6540);
// EXTERNAL MODULE: ./resources/js/components/preview-system/context/PreviewContext.jsx
var PreviewContext = __webpack_require__(38);
;// ./resources/js/components/preview-system/renderers/TextRenderer.jsx


/**
 * Renderer pour les éléments de texte simple
 */
var TextRenderer = function TextRenderer(_ref) {
  var element = _ref.element,
    previewData = _ref.previewData,
    mode = _ref.mode,
    _ref$canvasScale = _ref.canvasScale,
    canvasScale = _ref$canvasScale === void 0 ? 1 : _ref$canvasScale;
  var _element$x = element.x,
    x = _element$x === void 0 ? 0 : _element$x,
    _element$y = element.y,
    y = _element$y === void 0 ? 0 : _element$y,
    _element$width = element.width,
    width = _element$width === void 0 ? 200 : _element$width,
    _element$height = element.height,
    height = _element$height === void 0 ? 50 : _element$height,
    _element$content = element.content,
    content = _element$content === void 0 ? '' : _element$content,
    _element$text = element.text,
    text = _element$text === void 0 ? content || 'Texte d\'exemple' : _element$text,
    _element$fontSize = element.fontSize,
    fontSize = _element$fontSize === void 0 ? 14 : _element$fontSize,
    _element$fontFamily = element.fontFamily,
    fontFamily = _element$fontFamily === void 0 ? 'Arial' : _element$fontFamily,
    _element$fontWeight = element.fontWeight,
    fontWeight = _element$fontWeight === void 0 ? 'normal' : _element$fontWeight,
    _element$fontStyle = element.fontStyle,
    fontStyle = _element$fontStyle === void 0 ? 'normal' : _element$fontStyle,
    _element$textAlign = element.textAlign,
    textAlign = _element$textAlign === void 0 ? 'left' : _element$textAlign,
    _element$color = element.color,
    color = _element$color === void 0 ? '#333333' : _element$color,
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
    _element$textDecorati = element.textDecoration,
    textDecoration = _element$textDecorati === void 0 ? 'none' : _element$textDecorati,
    _element$lineHeight = element.lineHeight,
    lineHeight = _element$lineHeight === void 0 ? 1.2 : _element$lineHeight,
    _element$padding = element.padding,
    padding = _element$padding === void 0 ? 4 : _element$padding;
  var style = {
    position: 'absolute',
    left: "".concat(x * canvasScale, "px"),
    top: "".concat(y * canvasScale, "px"),
    width: "".concat(width * canvasScale, "px"),
    height: "".concat(height * canvasScale, "px"),
    fontSize: "".concat(fontSize * canvasScale, "px"),
    fontFamily: fontFamily,
    fontWeight: fontWeight,
    fontStyle: fontStyle,
    textAlign: textAlign,
    color: color,
    backgroundColor: backgroundColor,
    border: borderWidth > 0 ? "".concat(borderWidth, "px solid ").concat(borderColor) : 'none',
    borderRadius: "".concat(borderRadius, "px"),
    opacity: opacity,
    padding: "".concat(padding, "px"),
    boxSizing: 'border-box',
    overflow: 'hidden',
    display: visible ? 'block' : 'none',
    whiteSpace: 'pre-wrap',
    wordWrap: 'break-word',
    textDecoration: textDecoration,
    lineHeight: "".concat(lineHeight),
    transform: "rotate(".concat(rotation, "deg) scale(").concat(scale, ")"),
    transformOrigin: 'top left',
    boxShadow: shadow ? "".concat(shadowOffsetX, "px ").concat(shadowOffsetY, "px 4px ").concat(shadowColor) : 'none'
  };
  return /*#__PURE__*/react.createElement("div", {
    className: "preview-element preview-text-element",
    style: style,
    "data-element-id": element.id,
    "data-element-type": "text"
  }, text);
};
;// ./resources/js/components/preview-system/renderers/RectangleRenderer.jsx


/**
 * Renderer pour les éléments géométriques (rectangles, lignes, formes)
 */
var RectangleRenderer = function RectangleRenderer(_ref) {
  var element = _ref.element,
    previewData = _ref.previewData,
    mode = _ref.mode,
    _ref$canvasScale = _ref.canvasScale,
    canvasScale = _ref$canvasScale === void 0 ? 1 : _ref$canvasScale;
  var _element$x = element.x,
    x = _element$x === void 0 ? 0 : _element$x,
    _element$y = element.y,
    y = _element$y === void 0 ? 0 : _element$y,
    _element$width = element.width,
    width = _element$width === void 0 ? 100 : _element$width,
    _element$height = element.height,
    height = _element$height === void 0 ? 50 : _element$height,
    _element$backgroundCo = element.backgroundColor,
    backgroundColor = _element$backgroundCo === void 0 ? 'transparent' : _element$backgroundCo,
    _element$borderColor = element.borderColor,
    borderColor = _element$borderColor === void 0 ? '#000000' : _element$borderColor,
    _element$borderWidth = element.borderWidth,
    borderWidth = _element$borderWidth === void 0 ? 1 : _element$borderWidth,
    _element$borderRadius = element.borderRadius,
    borderRadius = _element$borderRadius === void 0 ? 0 : _element$borderRadius,
    _element$opacity = element.opacity,
    opacity = _element$opacity === void 0 ? 100 : _element$opacity,
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
    shadowOffsetY = _element$shadowOffset2 === void 0 ? 2 : _element$shadowOffset2;
  var containerStyle = {
    position: 'absolute',
    left: "".concat(x * canvasScale, "px"),
    top: "".concat(y * canvasScale, "px"),
    width: "".concat(width * canvasScale, "px"),
    height: "".concat(height * canvasScale, "px"),
    backgroundColor: backgroundColor,
    border: borderWidth > 0 ? "".concat(borderWidth, "px solid ").concat(borderColor) : 'none',
    borderRadius: "".concat(borderRadius, "px"),
    opacity: opacity / 100,
    display: visible ? 'block' : 'none',
    transform: "rotate(".concat(rotation, "deg) scale(").concat(scale, ")"),
    transformOrigin: 'top left',
    boxShadow: shadow ? "".concat(shadowOffsetX, "px ").concat(shadowOffsetY, "px 4px ").concat(shadowColor) : 'none'
  };
  return /*#__PURE__*/react.createElement("div", {
    className: "preview-element preview-rectangle-element",
    style: containerStyle,
    "data-element-id": element.id,
    "data-element-type": element.type
  });
};
// EXTERNAL MODULE: ./resources/js/components/preview-system/renderers/ImageRenderer.jsx
var ImageRenderer = __webpack_require__(2544);
;// ./resources/js/components/preview-system/renderers/TableRenderer.jsx
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }


/**
 * Renderer pour les tableaux de produits
 */

// Fonction utilitaire pour convertir RGB en CSS
var rgbToCss = function rgbToCss(rgbArray) {
  if (!Array.isArray(rgbArray) || rgbArray.length !== 3) {
    return 'transparent';
  }
  return "rgb(".concat(rgbArray[0], ", ").concat(rgbArray[1], ", ").concat(rgbArray[2], ")");
};
var TableRenderer = function TableRenderer(_ref) {
  var element = _ref.element,
    previewData = _ref.previewData,
    mode = _ref.mode,
    _ref$canvasScale = _ref.canvasScale,
    canvasScale = _ref$canvasScale === void 0 ? 1 : _ref$canvasScale;
  var _element$x = element.x,
    x = _element$x === void 0 ? 0 : _element$x,
    _element$y = element.y,
    y = _element$y === void 0 ? 0 : _element$y,
    _element$width = element.width,
    width = _element$width === void 0 ? 500 : _element$width,
    _element$height = element.height,
    height = _element$height === void 0 ? 200 : _element$height,
    _element$showHeaders = element.showHeaders,
    showHeaders = _element$showHeaders === void 0 ? true : _element$showHeaders,
    _element$showBorders = element.showBorders,
    showBorders = _element$showBorders === void 0 ? false : _element$showBorders,
    _element$tableStyle = element.tableStyle,
    tableStyle = _element$tableStyle === void 0 ? 'default' : _element$tableStyle,
    _element$backgroundCo = element.backgroundColor,
    backgroundColor = _element$backgroundCo === void 0 ? 'transparent' : _element$backgroundCo,
    _element$borderWidth = element.borderWidth,
    borderWidth = _element$borderWidth === void 0 ? 1 : _element$borderWidth,
    _element$borderColor = element.borderColor,
    borderColor = _element$borderColor === void 0 ? '#dddddd' : _element$borderColor,
    _element$borderRadius = element.borderRadius,
    borderRadius = _element$borderRadius === void 0 ? 0 : _element$borderRadius,
    _element$opacity = element.opacity,
    opacity = _element$opacity === void 0 ? 1 : _element$opacity,
    _element$showLabels = element.showLabels,
    showLabels = _element$showLabels === void 0 ? true : _element$showLabels,
    _element$headers = element.headers,
    headers = _element$headers === void 0 ? ['Produit', 'Qté', 'Prix'] : _element$headers,
    _element$dataSource = element.dataSource,
    dataSource = _element$dataSource === void 0 ? 'order_items' : _element$dataSource,
    _element$columns = element.columns,
    columns = _element$columns === void 0 ? {
      image: true,
      name: true,
      sku: false,
      quantity: true,
      price: true,
      total: true
    } : _element$columns,
    _element$showSubtotal = element.showSubtotal,
    showSubtotal = _element$showSubtotal === void 0 ? false : _element$showSubtotal,
    _element$showShipping = element.showShipping,
    showShipping = _element$showShipping === void 0 ? true : _element$showShipping,
    _element$showTaxes = element.showTaxes,
    showTaxes = _element$showTaxes === void 0 ? true : _element$showTaxes,
    _element$showDiscount = element.showDiscount,
    showDiscount = _element$showDiscount === void 0 ? false : _element$showDiscount,
    _element$showTotal = element.showTotal,
    showTotal = _element$showTotal === void 0 ? false : _element$showTotal,
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
    shadowOffsetY = _element$shadowOffset2 === void 0 ? 2 : _element$shadowOffset2;

  // Récupérer les données du tableau
  var elementKey = "product_table_".concat(element.id);
  var tableData = previewData[elementKey] || {};

  // Utiliser les données de style du tableau si disponibles
  var tableStyleData = tableData.tableStyleData || {
    header_bg: [248, 249, 250],
    // #f8f9fa
    header_border: [226, 232, 240],
    // #e2e8f0
    row_border: [241, 245, 249],
    // #f1f5f9
    alt_row_bg: [250, 251, 252],
    // #fafbfc
    headerTextColor: '#000000',
    rowTextColor: '#000000',
    border_width: 1,
    headerFontWeight: 'bold',
    headerFontSize: '12px',
    rowFontSize: '11px'
  };

  // Utiliser les headers depuis l'élément ou les données
  var tableHeaders = headers && headers.length > 0 ? headers : tableData.headers || [];

  // Générer les headers dynamiquement selon les colonnes activées (fallback)
  var generateHeadersFromColumns = function generateHeadersFromColumns() {
    var dynamicHeaders = [];
    if (columns.image) dynamicHeaders.push('Image');
    if (columns.name) dynamicHeaders.push('Produit');
    if (columns.sku) dynamicHeaders.push('SKU');
    if (columns.quantity) dynamicHeaders.push('Qté');
    if (columns.price) dynamicHeaders.push('Prix');
    if (columns.total) dynamicHeaders.push('Total');
    return dynamicHeaders;
  };

  // Utiliser les headers dans cet ordre de priorité :
  // 1. Headers personnalisés depuis l'élément
  // 2. Headers depuis les données (générés par SampleDataProvider)
  // 3. Headers générés dynamiquement depuis les colonnes
  var finalHeaders = tableHeaders.length > 0 ? tableHeaders : tableData.headers && tableData.headers.length > 0 ? tableData.headers : generateHeadersFromColumns();
  var containerStyle = {
    position: 'absolute',
    left: "".concat(x * canvasScale, "px"),
    top: "".concat(y * canvasScale, "px"),
    width: "".concat(width * canvasScale, "px"),
    height: "".concat(height * canvasScale, "px"),
    backgroundColor: backgroundColor,
    border: borderWidth > 0 ? "".concat(borderWidth, "px solid ").concat(borderColor) : 'none',
    borderRadius: "".concat(borderRadius, "px"),
    opacity: opacity,
    padding: '8px',
    boxSizing: 'border-box',
    overflow: 'auto',
    display: visible ? 'block' : 'none',
    transform: "rotate(".concat(rotation, "deg) scale(").concat(scale, ")"),
    transformOrigin: 'top left',
    boxShadow: shadow ? "".concat(shadowOffsetX, "px ").concat(shadowOffsetY, "px 4px ").concat(shadowColor) : 'none'
  };
  var tableStyleConfig = {
    width: '100%',
    borderCollapse: showBorders ? 'collapse' : 'separate',
    borderSpacing: showBorders ? '0' : '2px',
    fontSize: tableStyleData.rowFontSize,
    fontFamily: 'Arial, sans-serif'
  };
  var headerStyle = {
    backgroundColor: rgbToCss(tableStyleData.header_bg),
    color: tableStyleData.headerTextColor,
    fontWeight: tableStyleData.headerFontWeight,
    fontSize: tableStyleData.headerFontSize,
    padding: '8px',
    textAlign: 'left',
    border: showBorders ? "".concat(tableStyleData.border_width, "px solid ").concat(rgbToCss(tableStyleData.header_border)) : 'none'
  };
  var imageStyle = {
    width: '40px',
    height: '40px',
    objectFit: 'cover',
    borderRadius: '4px'
  };
  var descriptionStyle = {
    maxWidth: '200px',
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap'
  };
  var attributesStyle = {
    maxWidth: '150px',
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap',
    fontSize: '10px'
  };
  var cellStyle = {
    padding: '6px 8px',
    border: showBorders ? "".concat(tableStyleData.border_width, "px solid ").concat(rgbToCss(tableStyleData.row_border)) : 'none',
    verticalAlign: 'top',
    fontSize: tableStyleData.rowFontSize,
    color: tableStyleData.rowTextColor
  };
  return /*#__PURE__*/react.createElement("div", {
    className: "preview-element preview-table-element",
    style: containerStyle,
    "data-element-id": element.id,
    "data-element-type": "product_table"
  }, /*#__PURE__*/react.createElement("table", {
    style: tableStyleConfig
  }, showHeaders && finalHeaders && finalHeaders.length > 0 && /*#__PURE__*/react.createElement("thead", null, /*#__PURE__*/react.createElement("tr", null, finalHeaders.map(function (header, index) {
    return /*#__PURE__*/react.createElement("th", {
      key: index,
      style: headerStyle
    }, header);
  }))), /*#__PURE__*/react.createElement("tbody", null, tableData.rows && tableData.rows.map(function (row, rowIndex) {
    // Appliquer les couleurs alternées des lignes
    var isEvenRow = rowIndex % 2 === 0;
    var rowBackgroundColor = isEvenRow ? rgbToCss(tableStyleData.alt_row_bg) : 'transparent';
    return /*#__PURE__*/react.createElement("tr", {
      key: rowIndex,
      style: {
        backgroundColor: rowBackgroundColor
      }
    }, row.map(function (cell, cellIndex) {
      var header = finalHeaders[cellIndex] || '';
      var isImageColumn = header.toLowerCase() === 'image';
      var isDescriptionColumn = header.toLowerCase().includes('description');
      var isAttributesColumn = header.toLowerCase() === 'attributs';
      var isQuantityColumn = header.toLowerCase() === 'qté';
      var isPriceColumn = header.toLowerCase().includes('prix') || header.toLowerCase() === 'total' || header.toLowerCase() === 'tva' || header.toLowerCase() === 'remise';
      var cellStyleWithAlignment = _objectSpread({}, cellStyle);

      // Alignement spécial pour certaines colonnes
      if (isQuantityColumn) {
        cellStyleWithAlignment.textAlign = 'center';
      } else if (isPriceColumn) {
        cellStyleWithAlignment.textAlign = 'right';
      }
      return /*#__PURE__*/react.createElement("td", {
        key: cellIndex,
        style: cellStyleWithAlignment
      }, isImageColumn && cell ? /*#__PURE__*/react.createElement("img", {
        src: cell,
        alt: "Produit",
        style: imageStyle,
        onError: function onError(e) {
          e.target.style.display = 'none';
        }
      }) : isDescriptionColumn && cell ? /*#__PURE__*/react.createElement("span", {
        style: descriptionStyle,
        title: cell
      }, cell) : isAttributesColumn && cell ? /*#__PURE__*/react.createElement("span", {
        style: attributesStyle,
        title: cell
      }, cell) : cell || '-');
    }));
  })), (showSubtotal || showShipping || showTaxes || showDiscount || showTotal) && tableData.totals && Object.keys(tableData.totals).length > 0 && /*#__PURE__*/react.createElement("tfoot", null, showSubtotal && tableData.totals.subtotal && /*#__PURE__*/react.createElement("tr", null, /*#__PURE__*/react.createElement("td", {
    colSpan: finalHeaders.length,
    style: _objectSpread(_objectSpread({}, cellStyle), {}, {
      textAlign: 'right',
      fontWeight: 'bold',
      borderTop: '1px solid #dee2e6'
    })
  }, "Sous-total: ", tableData.totals.subtotal)), showShipping && tableData.totals.shipping && /*#__PURE__*/react.createElement("tr", null, /*#__PURE__*/react.createElement("td", {
    colSpan: finalHeaders.length,
    style: _objectSpread(_objectSpread({}, cellStyle), {}, {
      textAlign: 'right',
      fontWeight: 'bold'
    })
  }, "Frais de port: ", tableData.totals.shipping)), showTaxes && tableData.totals.tax && /*#__PURE__*/react.createElement("tr", null, /*#__PURE__*/react.createElement("td", {
    colSpan: finalHeaders.length,
    style: _objectSpread(_objectSpread({}, cellStyle), {}, {
      textAlign: 'right',
      fontWeight: 'bold'
    })
  }, "TVA: ", tableData.totals.tax)), showDiscount && tableData.totals.discount && /*#__PURE__*/react.createElement("tr", null, /*#__PURE__*/react.createElement("td", {
    colSpan: finalHeaders.length,
    style: _objectSpread(_objectSpread({}, cellStyle), {}, {
      textAlign: 'right',
      fontWeight: 'bold'
    })
  }, "Remise: ", tableData.totals.discount)), showTotal && tableData.totals.total && /*#__PURE__*/react.createElement("tr", null, /*#__PURE__*/react.createElement("td", {
    colSpan: finalHeaders.length,
    style: _objectSpread(_objectSpread({}, cellStyle), {}, {
      textAlign: 'right',
      fontWeight: 'bold',
      fontSize: '14px',
      color: '#2563eb',
      borderTop: '2px solid #2563eb'
    })
  }, "Total: ", tableData.totals.total)))), (!tableData.rows || tableData.rows.length === 0) && /*#__PURE__*/react.createElement("div", {
    style: {
      textAlign: 'center',
      color: '#6c757d',
      fontStyle: 'italic',
      padding: '20px'
    }
  }, "Aucun produit \xE0 afficher"));
};
;// ./resources/js/components/preview-system/renderers/DynamicTextRenderer.jsx


/**
 * Renderer pour les éléments de texte dynamique avec variables
 */
var DynamicTextRenderer = function DynamicTextRenderer(_ref) {
  var element = _ref.element,
    previewData = _ref.previewData,
    mode = _ref.mode,
    _ref$canvasScale = _ref.canvasScale,
    canvasScale = _ref$canvasScale === void 0 ? 1 : _ref$canvasScale;
  var _element$x = element.x,
    x = _element$x === void 0 ? 0 : _element$x,
    _element$y = element.y,
    y = _element$y === void 0 ? 0 : _element$y,
    _element$width = element.width,
    width = _element$width === void 0 ? 300 : _element$width,
    _element$height = element.height,
    height = _element$height === void 0 ? 50 : _element$height,
    _element$template = element.template,
    template = _element$template === void 0 ? 'total_only' : _element$template,
    _element$customConten = element.customContent,
    customContent = _element$customConten === void 0 ? '{{order_total}} €' : _element$customConten,
    _element$fontSize = element.fontSize,
    fontSize = _element$fontSize === void 0 ? 14 : _element$fontSize,
    _element$fontFamily = element.fontFamily,
    fontFamily = _element$fontFamily === void 0 ? 'Arial' : _element$fontFamily,
    _element$fontWeight = element.fontWeight,
    fontWeight = _element$fontWeight === void 0 ? 'normal' : _element$fontWeight,
    _element$fontStyle = element.fontStyle,
    fontStyle = _element$fontStyle === void 0 ? 'normal' : _element$fontStyle,
    _element$textAlign = element.textAlign,
    textAlign = _element$textAlign === void 0 ? 'left' : _element$textAlign,
    _element$color = element.color,
    color = _element$color === void 0 ? '#333333' : _element$color,
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
    _element$textDecorati = element.textDecoration,
    textDecoration = _element$textDecorati === void 0 ? 'none' : _element$textDecorati,
    _element$lineHeight = element.lineHeight,
    lineHeight = _element$lineHeight === void 0 ? 1.2 : _element$lineHeight;

  // Récupérer le contenu depuis les données d'aperçu
  var elementKey = "dynamic-text_".concat(element.id);
  var elementData = previewData[elementKey] || {};
  var displayContent = elementData.content || customContent;
  var style = {
    position: 'absolute',
    left: "".concat(x * canvasScale, "px"),
    top: "".concat(y * canvasScale, "px"),
    width: "".concat(width * canvasScale, "px"),
    height: "".concat(height * canvasScale, "px"),
    fontSize: "".concat(fontSize * canvasScale, "px"),
    fontFamily: fontFamily,
    fontWeight: fontWeight,
    fontStyle: fontStyle,
    textAlign: textAlign,
    color: color,
    backgroundColor: backgroundColor,
    border: borderWidth > 0 ? "".concat(borderWidth, "px solid ").concat(borderColor) : 'none',
    borderRadius: "".concat(borderRadius, "px"),
    opacity: opacity,
    padding: '4px',
    boxSizing: 'border-box',
    overflow: 'hidden',
    display: visible ? 'block' : 'none',
    whiteSpace: 'pre-wrap',
    wordWrap: 'break-word',
    textDecoration: textDecoration,
    lineHeight: "".concat(lineHeight),
    transform: "rotate(".concat(rotation, "deg) scale(").concat(scale, ")"),
    transformOrigin: 'top left',
    boxShadow: shadow ? "".concat(shadowOffsetX, "px ").concat(shadowOffsetY, "px 4px ").concat(shadowColor) : 'none'
  };
  return /*#__PURE__*/react.createElement("div", {
    className: "preview-element preview-dynamic-text-element",
    style: style,
    "data-element-id": element.id,
    "data-element-type": "dynamic-text",
    title: "Template: ".concat(template)
  }, displayContent);
};
// EXTERNAL MODULE: ./node_modules/jsbarcode/bin/JsBarcode.js
var JsBarcode = __webpack_require__(6129);
var JsBarcode_default = /*#__PURE__*/__webpack_require__.n(JsBarcode);
// EXTERNAL MODULE: ./node_modules/qrcode/lib/browser.js
var browser = __webpack_require__(7583);
;// ./resources/js/components/preview-system/renderers/BarcodeRenderer.jsx




/**
 * Renderer pour les codes-barres et QR codes
 */
var BarcodeRenderer = function BarcodeRenderer(_ref) {
  var element = _ref.element,
    previewData = _ref.previewData,
    mode = _ref.mode,
    _ref$canvasScale = _ref.canvasScale,
    canvasScale = _ref$canvasScale === void 0 ? 1 : _ref$canvasScale;
  var _element$x = element.x,
    x = _element$x === void 0 ? 0 : _element$x,
    _element$y = element.y,
    y = _element$y === void 0 ? 0 : _element$y,
    _element$width = element.width,
    width = _element$width === void 0 ? 150 : _element$width,
    _element$height = element.height,
    height = _element$height === void 0 ? 60 : _element$height,
    _element$backgroundCo = element.backgroundColor,
    backgroundColor = _element$backgroundCo === void 0 ? 'transparent' : _element$backgroundCo,
    _element$borderColor = element.borderColor,
    borderColor = _element$borderColor === void 0 ? '#000000' : _element$borderColor,
    _element$borderWidth = element.borderWidth,
    borderWidth = _element$borderWidth === void 0 ? 1 : _element$borderWidth,
    _element$opacity = element.opacity,
    opacity = _element$opacity === void 0 ? 100 : _element$opacity,
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
    _element$content = element.content,
    content = _element$content === void 0 ? '' : _element$content,
    _element$code = element.code,
    code = _element$code === void 0 ? '' : _element$code,
    _element$format = element.format,
    format = _element$format === void 0 ? 'CODE128' : _element$format;
  var svgRef = (0,react.useRef)(null);
  var canvasRef = (0,react.useRef)(null);

  // Extraire le contenu du code à encoder
  var codeValue = content || code || 'BARCODE';

  // Générer le code-barres ou QR code
  (0,react.useEffect)(function () {
    if (!visible) return;
    if (element.type === 'qrcode') {
      // Générer QR code
      if (canvasRef.current) {
        browser.toCanvas(canvasRef.current, codeValue, {
          errorCorrectionLevel: 'H',
          type: 'image/png',
          quality: 1,
          margin: 0,
          width: Math.min(200, Math.max(50, width * canvasScale / 2))
        })["catch"](function (err) {
          return console.error('QR Code génération échouée:', err);
        });
      }
    } else {
      // Générer code-barres
      if (svgRef.current) {
        try {
          JsBarcode_default()(svgRef.current, codeValue, {
            format: format || 'CODE128',
            width: 2,
            height: Math.max(40, height * canvasScale - 20),
            displayValue: true,
            fontSize: 12,
            margin: 2
          });
        } catch (err) {
          console.error('Code-barres génération échouée:', err);
        }
      }
    }
  }, [codeValue, element.type, width, height, canvasScale, format, visible]);
  var containerStyle = {
    position: 'absolute',
    left: "".concat(x * canvasScale, "px"),
    top: "".concat(y * canvasScale, "px"),
    width: "".concat(width * canvasScale, "px"),
    height: "".concat(height * canvasScale, "px"),
    backgroundColor: backgroundColor,
    border: borderWidth > 0 ? "".concat(borderWidth, "px solid ").concat(borderColor) : 'none',
    opacity: opacity / 100,
    display: visible ? 'flex' : 'none',
    alignItems: 'center',
    justifyContent: 'center',
    transform: "rotate(".concat(rotation, "deg) scale(").concat(scale, ")"),
    transformOrigin: 'top left',
    boxShadow: shadow ? "".concat(shadowOffsetX, "px ").concat(shadowOffsetY, "px 4px ").concat(shadowColor) : 'none',
    overflow: 'hidden'
  };
  return /*#__PURE__*/react.createElement("div", {
    className: "preview-element preview-barcode-element",
    style: containerStyle,
    "data-element-id": element.id,
    "data-element-type": element.type
  }, element.type === 'qrcode' ? /*#__PURE__*/react.createElement("canvas", {
    ref: canvasRef,
    style: {
      width: '100%',
      height: '100%',
      objectFit: 'contain'
    }
  }) : /*#__PURE__*/react.createElement("svg", {
    ref: svgRef,
    style: {
      width: '100%',
      height: 'auto',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center'
    }
  }));
};
;// ./resources/js/components/preview-system/renderers/ProgressBarRenderer.jsx


/**
 * Renderer pour les barres de progression
 */
var ProgressBarRenderer = function ProgressBarRenderer(_ref) {
  var element = _ref.element,
    previewData = _ref.previewData,
    mode = _ref.mode,
    _ref$canvasScale = _ref.canvasScale,
    canvasScale = _ref$canvasScale === void 0 ? 1 : _ref$canvasScale;
  var _element$x = element.x,
    x = _element$x === void 0 ? 0 : _element$x,
    _element$y = element.y,
    y = _element$y === void 0 ? 0 : _element$y,
    _element$width = element.width,
    width = _element$width === void 0 ? 200 : _element$width,
    _element$height = element.height,
    height = _element$height === void 0 ? 20 : _element$height,
    _element$backgroundCo = element.backgroundColor,
    backgroundColor = _element$backgroundCo === void 0 ? '#e5e7eb' : _element$backgroundCo,
    _element$borderColor = element.borderColor,
    borderColor = _element$borderColor === void 0 ? '#d1d5db' : _element$borderColor,
    _element$borderWidth = element.borderWidth,
    borderWidth = _element$borderWidth === void 0 ? 1 : _element$borderWidth,
    _element$borderRadius = element.borderRadius,
    borderRadius = _element$borderRadius === void 0 ? 10 : _element$borderRadius,
    _element$opacity = element.opacity,
    opacity = _element$opacity === void 0 ? 100 : _element$opacity,
    _element$progressValu = element.progressValue,
    progressValue = _element$progressValu === void 0 ? 75 : _element$progressValu,
    _element$progressColo = element.progressColor,
    progressColor = _element$progressColo === void 0 ? '#3b82f6' : _element$progressColo,
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
    shadowOffsetY = _element$shadowOffset2 === void 0 ? 2 : _element$shadowOffset2;
  var containerStyle = {
    position: 'absolute',
    left: "".concat(x * canvasScale, "px"),
    top: "".concat(y * canvasScale, "px"),
    width: "".concat(width * canvasScale, "px"),
    height: "".concat(height * canvasScale, "px"),
    backgroundColor: backgroundColor,
    border: borderWidth > 0 ? "".concat(borderWidth, "px solid ").concat(borderColor) : 'none',
    borderRadius: "".concat(borderRadius, "px"),
    opacity: opacity / 100,
    overflow: 'hidden',
    display: visible ? 'block' : 'none',
    transform: "rotate(".concat(rotation, "deg) scale(").concat(scale, ")"),
    transformOrigin: 'top left',
    boxShadow: shadow ? "".concat(shadowOffsetX, "px ").concat(shadowOffsetY, "px 4px ").concat(shadowColor) : 'none'
  };
  var progressStyle = {
    width: "".concat(Math.min(100, Math.max(0, progressValue)), "%"),
    height: '100%',
    backgroundColor: progressColor,
    transition: 'width 0.3s ease'
  };
  return /*#__PURE__*/react.createElement("div", {
    className: "preview-element preview-progress-element",
    style: containerStyle,
    "data-element-id": element.id,
    "data-element-type": "progress-bar"
  }, /*#__PURE__*/react.createElement("div", {
    style: progressStyle
  }));
};
;// ./resources/js/components/preview-system/renderers/ElementRenderer.jsx









/**
 * ElementRenderer - Renderer principal pour tous les types d'éléments
 * Route vers le renderer approprié selon le type d'élément
 */
function ElementRenderer(_ref) {
  var _element$properties, _element$properties2, _templateData$custome, _templateData$custome2, _templateData$custome3, _templateData$custome4, _element$properties3, _element$properties4, _templateData$company, _templateData$company2, _templateData$company3, _templateData$company4, _element$properties5, _element$properties6, _element$properties7, _templateData$order;
  var element = _ref.element,
    _ref$scale = _ref.scale,
    scale = _ref$scale === void 0 ? 1 : _ref$scale,
    _ref$templateData = _ref.templateData,
    templateData = _ref$templateData === void 0 ? {} : _ref$templateData,
    _ref$interactive = _ref.interactive,
    interactive = _ref$interactive === void 0 ? false : _ref$interactive;
  // Rendu selon le type d'élément
  switch (element.type) {
    case 'text':
      return /*#__PURE__*/react.createElement(TextRenderer, {
        element: element,
        canvasScale: scale
      });
    case 'dynamic-text':
      return /*#__PURE__*/react.createElement(DynamicTextRenderer, {
        element: element,
        previewData: templateData,
        canvasScale: scale
      });
    case 'rectangle':
    case 'shape-rectangle':
      return /*#__PURE__*/react.createElement(RectangleRenderer, {
        element: element,
        canvasScale: scale
      });
    case 'image':
      return /*#__PURE__*/react.createElement(ImageRenderer.ImageRenderer, {
        element: element,
        canvasScale: scale
      });
    case 'product_table':
      return /*#__PURE__*/react.createElement(TableRenderer, {
        element: element,
        previewData: templateData,
        canvasScale: scale
      });
    case 'barcode':
    case 'qrcode':
      return /*#__PURE__*/react.createElement(BarcodeRenderer, {
        element: element,
        previewData: templateData,
        canvasScale: scale
      });
    case 'progress-bar':
      return /*#__PURE__*/react.createElement(ProgressBarRenderer, {
        element: element,
        previewData: templateData,
        canvasScale: scale
      });
    case 'customer_info':
      return /*#__PURE__*/react.createElement("div", {
        style: {
          position: 'absolute',
          left: "".concat(element.x * scale, "px"),
          top: "".concat(element.y * scale, "px"),
          width: "".concat(element.width * scale, "px"),
          minHeight: "".concat(element.height * scale, "px"),
          padding: '10px',
          backgroundColor: ((_element$properties = element.properties) === null || _element$properties === void 0 ? void 0 : _element$properties.backgroundColor) || '#f8f9fa',
          border: (_element$properties2 = element.properties) !== null && _element$properties2 !== void 0 && _element$properties2.borderWidth ? "".concat(element.properties.borderWidth, "px solid ").concat(element.properties.borderColor || '#dee2e6') : 'none',
          borderRadius: '4px',
          fontSize: '12px',
          lineHeight: '1.4'
        }
      }, /*#__PURE__*/react.createElement("div", null, /*#__PURE__*/react.createElement("strong", null, "Client:"), " ", ((_templateData$custome = templateData.customer) === null || _templateData$custome === void 0 ? void 0 : _templateData$custome.name) || 'N/A'), /*#__PURE__*/react.createElement("div", null, /*#__PURE__*/react.createElement("strong", null, "Email:"), " ", ((_templateData$custome2 = templateData.customer) === null || _templateData$custome2 === void 0 ? void 0 : _templateData$custome2.email) || 'N/A'), /*#__PURE__*/react.createElement("div", null, /*#__PURE__*/react.createElement("strong", null, "T\xE9l\xE9phone:"), " ", ((_templateData$custome3 = templateData.customer) === null || _templateData$custome3 === void 0 ? void 0 : _templateData$custome3.phone) || 'N/A'), ((_templateData$custome4 = templateData.customer) === null || _templateData$custome4 === void 0 ? void 0 : _templateData$custome4.address) && /*#__PURE__*/react.createElement("div", null, /*#__PURE__*/react.createElement("strong", null, "Adresse:"), " ", templateData.customer.address.replace('\n', ', ')));
    case 'company_info':
      return /*#__PURE__*/react.createElement("div", {
        style: {
          position: 'absolute',
          left: "".concat(element.x * scale, "px"),
          top: "".concat(element.y * scale, "px"),
          width: "".concat(element.width * scale, "px"),
          minHeight: "".concat(element.height * scale, "px"),
          padding: '10px',
          backgroundColor: ((_element$properties3 = element.properties) === null || _element$properties3 === void 0 ? void 0 : _element$properties3.backgroundColor) || '#f8f9fa',
          border: (_element$properties4 = element.properties) !== null && _element$properties4 !== void 0 && _element$properties4.borderWidth ? "".concat(element.properties.borderWidth, "px solid ").concat(element.properties.borderColor || '#dee2e6') : 'none',
          borderRadius: '4px',
          fontSize: '12px',
          lineHeight: '1.4'
        }
      }, /*#__PURE__*/react.createElement("div", null, /*#__PURE__*/react.createElement("strong", null, "Entreprise:"), " ", ((_templateData$company = templateData.company) === null || _templateData$company === void 0 ? void 0 : _templateData$company.name) || 'N/A'), /*#__PURE__*/react.createElement("div", null, /*#__PURE__*/react.createElement("strong", null, "Email:"), " ", ((_templateData$company2 = templateData.company) === null || _templateData$company2 === void 0 ? void 0 : _templateData$company2.email) || 'N/A'), /*#__PURE__*/react.createElement("div", null, /*#__PURE__*/react.createElement("strong", null, "T\xE9l\xE9phone:"), " ", ((_templateData$company3 = templateData.company) === null || _templateData$company3 === void 0 ? void 0 : _templateData$company3.phone) || 'N/A'), ((_templateData$company4 = templateData.company) === null || _templateData$company4 === void 0 ? void 0 : _templateData$company4.address) && /*#__PURE__*/react.createElement("div", null, /*#__PURE__*/react.createElement("strong", null, "Adresse:"), " ", templateData.company.address.replace('\n', ', ')));
    case 'order_number':
      return /*#__PURE__*/react.createElement("div", {
        style: {
          position: 'absolute',
          left: "".concat(element.x * scale, "px"),
          top: "".concat(element.y * scale, "px"),
          width: "".concat(element.width * scale, "px"),
          minHeight: "".concat(element.height * scale, "px"),
          padding: '8px',
          backgroundColor: ((_element$properties5 = element.properties) === null || _element$properties5 === void 0 ? void 0 : _element$properties5.backgroundColor) || '#e3f2fd',
          border: (_element$properties6 = element.properties) !== null && _element$properties6 !== void 0 && _element$properties6.borderWidth ? "".concat(element.properties.borderWidth, "px solid ").concat(element.properties.borderColor || '#2196f3') : 'none',
          borderRadius: '4px',
          fontSize: '14px',
          fontWeight: 'bold',
          color: ((_element$properties7 = element.properties) === null || _element$properties7 === void 0 ? void 0 : _element$properties7.color) || '#1976d2',
          textAlign: 'center',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center'
        }
      }, "Commande #", ((_templateData$order = templateData.order) === null || _templateData$order === void 0 ? void 0 : _templateData$order.number) || 'N/A');
    default:
      // Élément inconnu - afficher un placeholder
      return /*#__PURE__*/react.createElement("div", {
        style: {
          position: 'absolute',
          left: "".concat(element.x * scale, "px"),
          top: "".concat(element.y * scale, "px"),
          width: "".concat(element.width * scale, "px"),
          minHeight: "".concat(element.height * scale, "px"),
          padding: '10px',
          backgroundColor: '#ffebee',
          border: '1px solid #f44336',
          borderRadius: '4px',
          color: '#c62828',
          fontSize: '12px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center'
        }
      }, /*#__PURE__*/react.createElement("div", {
        style: {
          textAlign: 'center'
        }
      }, /*#__PURE__*/react.createElement("div", null, "\u2753"), /*#__PURE__*/react.createElement("div", null, "Type: ", element.type)));
  }
}
/* harmony default export */ const renderers_ElementRenderer = (ElementRenderer);
;// ./resources/js/components/preview-system/modes/CanvasMode.jsx
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }




/**
 * CanvasMode - Mode d'aperçu canvas utilisant le système principal
 * Rend les éléments avec leurs propriétés et outils
 */
function CanvasMode() {
  var _usePreviewContext = (0,PreviewContext.usePreviewContext)(),
    _usePreviewContext$st = _usePreviewContext.state,
    loading = _usePreviewContext$st.loading,
    error = _usePreviewContext$st.error,
    previewData = _usePreviewContext$st.data,
    config = _usePreviewContext$st.config,
    clearPreview = _usePreviewContext.actions.clearPreview;

  // Extraire les éléments et les données de template depuis la config
  var _config$elements = config.elements,
    elements = _config$elements === void 0 ? [] : _config$elements,
    _config$templateData = config.templateData,
    templateData = _config$templateData === void 0 ? {} : _config$templateData;

  // Calculer les dimensions et l'échelle optimales
  var canvasConfig = (0,react.useMemo)(function () {
    var pageWidth = 595; // A4 width in points
    var pageHeight = 842; // A4 height in points
    var containerWidth = 800; // Largeur max du conteneur
    var scale = Math.min(1, containerWidth / pageWidth);
    return {
      pageWidth: pageWidth,
      pageHeight: pageHeight,
      containerWidth: containerWidth,
      scale: scale,
      displayWidth: pageWidth * scale,
      displayHeight: pageHeight * scale
    };
  }, []);
  if (loading) {
    return /*#__PURE__*/react.createElement("div", {
      className: "canvas-mode-loading"
    }, /*#__PURE__*/react.createElement("div", {
      className: "spinner"
    }, "Chargement..."));
  }
  if (error) {
    return /*#__PURE__*/react.createElement("div", {
      className: "canvas-mode-error"
    }, /*#__PURE__*/react.createElement("p", null, "Erreur: ", error), /*#__PURE__*/react.createElement("button", {
      onClick: clearPreview
    }, "R\xE9essayer"));
  }
  return /*#__PURE__*/react.createElement("div", {
    className: "canvas-mode"
  }, /*#__PURE__*/react.createElement("div", {
    className: "canvas-container",
    style: {
      width: "".concat(canvasConfig.containerWidth, "px"),
      margin: '0 auto',
      border: '1px solid #e0e0e0',
      borderRadius: '8px',
      overflow: 'hidden',
      backgroundColor: '#f8f9fa'
    }
  }, /*#__PURE__*/react.createElement("div", {
    style: {
      padding: '12px 20px',
      backgroundColor: 'white',
      borderBottom: '1px solid #e0e0e0',
      fontSize: '14px',
      color: '#1d1d1f',
      display: 'flex',
      alignItems: 'center',
      gap: '20px'
    }
  }, /*#__PURE__*/react.createElement("span", null, "\uD83D\uDCC4 ", Math.round(canvasConfig.pageWidth), " \xD7 ", Math.round(canvasConfig.pageHeight), " points"), /*#__PURE__*/react.createElement("span", null, "|"), /*#__PURE__*/react.createElement("span", null, "\uD83D\uDD0D ", Math.round(canvasConfig.scale * 100), "%"), /*#__PURE__*/react.createElement("span", null, "|"), /*#__PURE__*/react.createElement("span", null, "\uD83D\uDCE6 ", elements && elements.length ? elements.length : 0, " \xE9l\xE9ments")), /*#__PURE__*/react.createElement("div", {
    style: {
      padding: '20px',
      backgroundColor: '#f8f9fa',
      minHeight: "".concat(canvasConfig.displayHeight + 40, "px")
    }
  }, /*#__PURE__*/react.createElement("div", {
    style: {
      width: "".concat(canvasConfig.displayWidth, "px"),
      height: "".concat(canvasConfig.displayHeight, "px"),
      backgroundColor: 'white',
      boxShadow: '0 4px 12px rgba(0,0,0,0.1)',
      margin: '0 auto',
      position: 'relative',
      overflow: 'hidden'
    }
  }, elements && elements.length > 0 ? /*#__PURE__*/react.createElement("div", {
    style: {
      transform: "scale(".concat(canvasConfig.scale, ")"),
      transformOrigin: 'top left',
      width: "".concat(canvasConfig.pageWidth, "px"),
      height: "".concat(canvasConfig.pageHeight, "px"),
      position: 'relative'
    }
  }, elements.map(function (element, index) {
    return /*#__PURE__*/react.createElement(renderers_ElementRenderer, {
      key: "".concat(element.type, "-").concat(index),
      element: element,
      scale: 1 // Échelle déjà appliquée au conteneur
      ,
      templateData: templateData,
      interactive: false
    });
  })) : /*#__PURE__*/react.createElement("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      height: '100%',
      color: '#6b7280',
      fontSize: '16px'
    }
  }, /*#__PURE__*/react.createElement("div", {
    style: {
      textAlign: 'center'
    }
  }, /*#__PURE__*/react.createElement("div", {
    style: {
      fontSize: '48px',
      marginBottom: '16px'
    }
  }, "\uD83D\uDCC4"), /*#__PURE__*/react.createElement("p", null, "Aucun \xE9l\xE9ment \xE0 afficher"), /*#__PURE__*/react.createElement("p", {
    style: {
      fontSize: '14px',
      marginTop: '8px'
    }
  }, "Ajoutez des \xE9l\xE9ments dans l'\xE9diteur pour les voir ici"))))),  false && /*#__PURE__*/0));
}

// Fonction utilitaire pour charger des données (utilisée par PreviewModal)
CanvasMode.loadData = /*#__PURE__*/function () {
  var _ref = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(elements, templateData, config) {
    return _regenerator().w(function (_context) {
      while (1) switch (_context.n) {
        case 0:
          return _context.a(2, {
            elements: elements || [],
            templateData: templateData || {},
            config: config || {}
          });
      }
    }, _callee);
  }));
  return function (_x, _x2, _x3) {
    return _ref.apply(this, arguments);
  };
}();
/* harmony default export */ const modes_CanvasMode = (CanvasMode);

/***/ })

}]);