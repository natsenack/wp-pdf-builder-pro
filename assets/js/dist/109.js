"use strict";
(self["webpackChunkPDFBuilderPro"] = self["webpackChunkPDFBuilderPro"] || []).push([[109],{

/***/ 109:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, {
  "default": () => (/* binding */ modes_CanvasMode)
});

// EXTERNAL MODULE: ./node_modules/react/index.js
var react = __webpack_require__(540);
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
    _element$text = element.text,
    text = _element$text === void 0 ? 'Texte d\'exemple' : _element$text,
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
  var style = {
    position: 'absolute',
    left: x * canvasScale,
    top: y * canvasScale,
    width: width * canvasScale,
    height: height * canvasScale,
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
    display: visible ? 'flex' : 'none',
    alignItems: 'center',
    whiteSpace: 'pre-wrap',
    wordWrap: 'break-word',
    textDecoration: textDecoration,
    lineHeight: lineHeight,
    // Transformations
    transform: "rotate(".concat(rotation, "deg) scale(").concat(scale, ")"),
    transformOrigin: 'center center',
    // Ombres
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
    left: x * canvasScale,
    top: y * canvasScale,
    width: width * canvasScale,
    height: height * canvasScale,
    backgroundColor: backgroundColor,
    border: borderWidth > 0 ? "".concat(borderWidth, "px solid ").concat(borderColor) : 'none',
    borderRadius: "".concat(borderRadius, "px"),
    opacity: opacity / 100,
    display: visible ? 'block' : 'none',
    // Transformations
    transform: "rotate(".concat(rotation, "deg) scale(").concat(scale, ")"),
    transformOrigin: 'center center',
    // Ombres
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
var ImageRenderer = __webpack_require__(544);
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
    left: x * canvasScale,
    top: y * canvasScale,
    width: width * canvasScale,
    height: height * canvasScale,
    backgroundColor: backgroundColor,
    border: borderWidth > 0 ? "".concat(borderWidth, "px solid ").concat(borderColor) : 'none',
    borderRadius: "".concat(borderRadius, "px"),
    opacity: opacity,
    padding: '8px',
    boxSizing: 'border-box',
    overflow: 'auto',
    display: visible ? 'block' : 'none',
    // Transformations
    transform: "rotate(".concat(rotation, "deg) scale(").concat(scale, ")"),
    transformOrigin: 'center center',
    // Ombres
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
    shadowOffsetY = _element$shadowOffset2 === void 0 ? 2 : _element$shadowOffset2;
  var containerStyle = {
    position: 'absolute',
    left: x * canvasScale,
    top: y * canvasScale,
    width: width * canvasScale,
    height: height * canvasScale,
    backgroundColor: backgroundColor,
    border: borderWidth > 0 ? "".concat(borderWidth, "px solid ").concat(borderColor) : 'none',
    opacity: opacity / 100,
    display: visible ? 'flex' : 'none',
    alignItems: 'center',
    justifyContent: 'center',
    fontSize: '10px',
    color: '#666',
    fontFamily: 'monospace',
    // Transformations
    transform: "rotate(".concat(rotation, "deg) scale(").concat(scale, ")"),
    transformOrigin: 'center center',
    // Ombres
    boxShadow: shadow ? "".concat(shadowOffsetX, "px ").concat(shadowOffsetY, "px 4px ").concat(shadowColor) : 'none'
  };

  // Placeholder pour les codes-barres/QR codes
  var placeholderText = element.type === 'qrcode' ? 'QR CODE' : 'BARCODE';
  return /*#__PURE__*/react.createElement("div", {
    className: "preview-element preview-barcode-element",
    style: containerStyle,
    "data-element-id": element.id,
    "data-element-type": element.type
  }, /*#__PURE__*/react.createElement("div", {
    style: {
      border: '1px dashed #ccc',
      width: '80%',
      height: '80%',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      backgroundColor: '#f9f9f9'
    }
  }, placeholderText));
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
    left: x * canvasScale,
    top: y * canvasScale,
    width: width * canvasScale,
    height: height * canvasScale,
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
    display: visible ? 'flex' : 'none',
    alignItems: 'center',
    whiteSpace: 'pre-wrap',
    wordWrap: 'break-word',
    textDecoration: textDecoration,
    lineHeight: lineHeight,
    // Transformations
    transform: "rotate(".concat(rotation, "deg) scale(").concat(scale, ")"),
    transformOrigin: 'center center',
    // Ombres
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
;// ./resources/js/components/preview-system/renderers/CustomerInfoRenderer.jsx


/**
 * Renderer pour les informations client
 */
var CustomerInfoRenderer = function CustomerInfoRenderer(_ref) {
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
    height = _element$height === void 0 ? 150 : _element$height,
    _element$showHeaders = element.showHeaders,
    showHeaders = _element$showHeaders === void 0 ? true : _element$showHeaders,
    _element$showBorders = element.showBorders,
    showBorders = _element$showBorders === void 0 ? false : _element$showBorders,
    _element$fields = element.fields,
    fields = _element$fields === void 0 ? ['name', 'email', 'phone', 'address'] : _element$fields,
    _element$layout = element.layout,
    layout = _element$layout === void 0 ? 'vertical' : _element$layout,
    _element$showLabels = element.showLabels,
    showLabels = _element$showLabels === void 0 ? true : _element$showLabels,
    _element$labelStyle = element.labelStyle,
    labelStyle = _element$labelStyle === void 0 ? 'bold' : _element$labelStyle,
    _element$spacing = element.spacing,
    spacing = _element$spacing === void 0 ? 8 : _element$spacing,
    _element$fontSize = element.fontSize,
    fontSize = _element$fontSize === void 0 ? 12 : _element$fontSize,
    _element$fontFamily = element.fontFamily,
    fontFamily = _element$fontFamily === void 0 ? 'Arial' : _element$fontFamily,
    _element$fontWeight = element.fontWeight,
    fontWeight = _element$fontWeight === void 0 ? 'normal' : _element$fontWeight,
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

  // Récupérer les données client
  var elementKey = "customer_info_".concat(element.id);
  var customerData = previewData[elementKey] || {};
  var containerStyle = {
    position: 'absolute',
    left: x * canvasScale,
    top: y * canvasScale,
    width: width * canvasScale,
    height: height * canvasScale,
    backgroundColor: backgroundColor,
    border: borderWidth > 0 ? "".concat(borderWidth, "px solid ").concat(borderColor) : 'none',
    borderRadius: "".concat(borderRadius, "px"),
    opacity: opacity,
    padding: '8px',
    boxSizing: 'border-box',
    overflow: 'hidden',
    fontSize: "".concat(fontSize * canvasScale, "px"),
    fontFamily: fontFamily,
    color: color,
    display: visible ? 'block' : 'none',
    textDecoration: textDecoration,
    lineHeight: lineHeight,
    // Transformations
    transform: "rotate(".concat(rotation, "deg) scale(").concat(scale, ")"),
    transformOrigin: 'center center',
    // Ombres
    boxShadow: shadow ? "".concat(shadowOffsetX, "px ").concat(shadowOffsetY, "px 4px ").concat(shadowColor) : 'none'
  };
  var fieldStyle = {
    marginBottom: layout === 'vertical' ? "".concat(spacing, "px") : '0',
    display: layout === 'horizontal' ? 'inline-block' : 'block',
    marginRight: layout === 'horizontal' ? "".concat(spacing * 2, "px") : '0'
  };
  var labelStyleConfig = {
    fontWeight: labelStyle === 'bold' ? 'bold' : 'normal',
    textTransform: labelStyle === 'uppercase' ? 'uppercase' : 'none',
    marginRight: showLabels ? '8px' : '0'
  };
  var valueStyle = {
    fontWeight: fontWeight,
    textAlign: textAlign
  };

  // Mapping des champs vers leurs libellés
  var fieldLabels = {
    name: 'Nom :',
    email: 'Email :',
    phone: 'Téléphone :',
    address: 'Adresse :',
    company: 'Entreprise :',
    vat: 'TVA :',
    siret: 'SIRET :'
  };
  return /*#__PURE__*/react.createElement("div", {
    className: "preview-element preview-customer-info-element",
    style: containerStyle,
    "data-element-id": element.id,
    "data-element-type": "customer_info"
  }, fields.map(function (field, index) {
    var value = customerData[field];
    if (!value) return null;
    return /*#__PURE__*/react.createElement("div", {
      key: field,
      style: fieldStyle
    }, showLabels && /*#__PURE__*/react.createElement("span", {
      style: labelStyleConfig
    }, fieldLabels[field] || "".concat(field, " :")), /*#__PURE__*/react.createElement("span", {
      style: valueStyle
    }, field === 'address' ? /*#__PURE__*/react.createElement("span", {
      style: {
        whiteSpace: 'pre-line'
      }
    }, value) : value));
  }), fields.length === 0 || Object.keys(customerData).length === 0 && /*#__PURE__*/react.createElement("div", {
    style: {
      textAlign: 'center',
      color: '#6c757d',
      fontStyle: 'italic',
      padding: '20px'
    }
  }, "Aucune information client"));
};
;// ./resources/js/components/preview-system/renderers/CompanyInfoRenderer.jsx


/**
 * Renderer pour les informations entreprise
 */
var CompanyInfoRenderer = function CompanyInfoRenderer(_ref) {
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
    height = _element$height === void 0 ? 120 : _element$height,
    _element$showHeaders = element.showHeaders,
    showHeaders = _element$showHeaders === void 0 ? false : _element$showHeaders,
    _element$showBorders = element.showBorders,
    showBorders = _element$showBorders === void 0 ? false : _element$showBorders,
    _element$fields = element.fields,
    fields = _element$fields === void 0 ? ['name', 'address', 'phone', 'email'] : _element$fields,
    _element$layout = element.layout,
    layout = _element$layout === void 0 ? 'vertical' : _element$layout,
    _element$showLabels = element.showLabels,
    showLabels = _element$showLabels === void 0 ? false : _element$showLabels,
    _element$labelStyle = element.labelStyle,
    labelStyle = _element$labelStyle === void 0 ? 'normal' : _element$labelStyle,
    _element$spacing = element.spacing,
    spacing = _element$spacing === void 0 ? 4 : _element$spacing,
    _element$fontSize = element.fontSize,
    fontSize = _element$fontSize === void 0 ? 12 : _element$fontSize,
    _element$fontFamily = element.fontFamily,
    fontFamily = _element$fontFamily === void 0 ? 'Arial' : _element$fontFamily,
    _element$fontWeight = element.fontWeight,
    fontWeight = _element$fontWeight === void 0 ? 'normal' : _element$fontWeight,
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

  // Récupérer les données entreprise
  var elementKey = "company_info_".concat(element.id);
  var companyData = previewData[elementKey] || {};
  var containerStyle = {
    position: 'absolute',
    left: x * canvasScale,
    top: y * canvasScale,
    width: width * canvasScale,
    height: height * canvasScale,
    backgroundColor: backgroundColor,
    border: borderWidth > 0 ? "".concat(borderWidth, "px solid ").concat(borderColor) : 'none',
    borderRadius: "".concat(borderRadius, "px"),
    opacity: opacity,
    padding: '4px',
    boxSizing: 'border-box',
    overflow: 'hidden',
    fontSize: "".concat(fontSize * canvasScale, "px"),
    fontFamily: fontFamily,
    color: color,
    display: visible ? 'block' : 'none',
    textDecoration: textDecoration,
    lineHeight: lineHeight,
    // Transformations
    transform: "rotate(".concat(rotation, "deg) scale(").concat(scale, ")"),
    transformOrigin: 'center center',
    // Ombres
    boxShadow: shadow ? "".concat(shadowOffsetX, "px ").concat(shadowOffsetY, "px 4px ").concat(shadowColor) : 'none'
  };
  var fieldStyle = {
    marginBottom: layout === 'vertical' ? "".concat(spacing, "px") : '0',
    display: layout === 'horizontal' ? 'inline-block' : 'block',
    marginRight: layout === 'horizontal' ? "".concat(spacing * 2, "px") : '0'
  };
  var labelStyleConfig = {
    fontWeight: labelStyle === 'bold' ? 'bold' : 'normal',
    textTransform: labelStyle === 'uppercase' ? 'uppercase' : 'none',
    marginRight: showLabels ? '8px' : '0'
  };
  var valueStyle = {
    fontWeight: fontWeight,
    textAlign: textAlign
  };

  // Mapping des champs vers leurs libellés
  var fieldLabels = {
    name: 'Nom :',
    address: 'Adresse :',
    phone: 'Téléphone :',
    email: 'Email :',
    website: 'Site web :',
    vat: 'TVA :',
    rcs: 'RCS :',
    siret: 'SIRET :'
  };
  return /*#__PURE__*/react.createElement("div", {
    className: "preview-element preview-company-info-element",
    style: containerStyle,
    "data-element-id": element.id,
    "data-element-type": "company_info"
  }, fields.map(function (field, index) {
    var value = companyData[field];
    if (!value) return null;
    return /*#__PURE__*/react.createElement("div", {
      key: field,
      style: fieldStyle
    }, showLabels && /*#__PURE__*/react.createElement("span", {
      style: labelStyleConfig
    }, fieldLabels[field] || "".concat(field, " :")), /*#__PURE__*/react.createElement("span", {
      style: valueStyle
    }, field === 'address' ? /*#__PURE__*/react.createElement("span", {
      style: {
        whiteSpace: 'pre-line'
      }
    }, value) : value));
  }), fields.length === 0 || Object.keys(companyData).length === 0 && /*#__PURE__*/react.createElement("div", {
    style: {
      textAlign: 'center',
      color: '#6c757d',
      fontStyle: 'italic',
      padding: '20px'
    }
  }, "Aucune information entreprise"));
};
;// ./resources/js/components/preview-system/renderers/OrderNumberRenderer.jsx


/**
 * Renderer pour le numéro de commande
 */
var OrderNumberRenderer = function OrderNumberRenderer(_ref) {
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
    height = _element$height === void 0 ? 40 : _element$height,
    _element$showHeaders = element.showHeaders,
    showHeaders = _element$showHeaders === void 0 ? false : _element$showHeaders,
    _element$showBorders = element.showBorders,
    showBorders = _element$showBorders === void 0 ? false : _element$showBorders,
    _element$format = element.format,
    format = _element$format === void 0 ? 'Commande #{order_number} - {order_date}' : _element$format,
    _element$fontSize = element.fontSize,
    fontSize = _element$fontSize === void 0 ? 14 : _element$fontSize,
    _element$fontFamily = element.fontFamily,
    fontFamily = _element$fontFamily === void 0 ? 'Arial' : _element$fontFamily,
    _element$fontWeight = element.fontWeight,
    fontWeight = _element$fontWeight === void 0 ? 'bold' : _element$fontWeight,
    _element$textAlign = element.textAlign,
    textAlign = _element$textAlign === void 0 ? 'right' : _element$textAlign,
    _element$color = element.color,
    color = _element$color === void 0 ? '#333333' : _element$color,
    _element$showLabel = element.showLabel,
    showLabel = _element$showLabel === void 0 ? true : _element$showLabel,
    _element$labelText = element.labelText,
    labelText = _element$labelText === void 0 ? 'N° de commande:' : _element$labelText,
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

  // Récupérer les données du numéro de commande
  var elementKey = "order_number_".concat(element.id);
  var orderData = previewData[elementKey] || {};
  var formattedNumber = orderData.formatted || format;
  var containerStyle = {
    position: 'absolute',
    left: x * canvasScale,
    top: y * canvasScale,
    width: width * canvasScale,
    height: height * canvasScale,
    backgroundColor: backgroundColor,
    border: borderWidth > 0 ? "".concat(borderWidth, "px solid ").concat(borderColor) : 'none',
    borderRadius: "".concat(borderRadius, "px"),
    opacity: opacity,
    padding: '4px',
    boxSizing: 'border-box',
    overflow: 'hidden',
    fontSize: "".concat(fontSize * canvasScale, "px"),
    fontFamily: fontFamily,
    color: color,
    display: visible ? 'flex' : 'none',
    alignItems: 'center',
    justifyContent: textAlign === 'right' ? 'flex-end' : textAlign === 'center' ? 'center' : 'flex-start',
    textDecoration: textDecoration,
    lineHeight: lineHeight,
    // Transformations
    transform: "rotate(".concat(rotation, "deg) scale(").concat(scale, ")"),
    transformOrigin: 'center center',
    // Ombres
    boxShadow: shadow ? "".concat(shadowOffsetX, "px ").concat(shadowOffsetY, "px 4px ").concat(shadowColor) : 'none'
  };
  var labelStyle = {
    fontWeight: 'normal',
    marginRight: showLabel ? '8px' : '0',
    color: '#666666'
  };
  var valueStyle = {
    fontWeight: fontWeight,
    textAlign: textAlign
  };
  return /*#__PURE__*/react.createElement("div", {
    className: "preview-element preview-order-number-element",
    style: containerStyle,
    "data-element-id": element.id,
    "data-element-type": "order_number"
  }, showLabel && /*#__PURE__*/react.createElement("span", {
    style: labelStyle
  }, labelText), /*#__PURE__*/react.createElement("span", {
    style: valueStyle
  }, formattedNumber));
};
// EXTERNAL MODULE: ./resources/js/components/preview-system/renderers/PDFRenderer.jsx
var PDFRenderer = __webpack_require__(813);
;// ./resources/js/components/preview-system/renderers/WatermarkRenderer.jsx


/**
 * Renderer pour les filigranes
 */
var WatermarkRenderer = function WatermarkRenderer(_ref) {
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
    height = _element$height === void 0 ? 200 : _element$height,
    _element$content = element.content,
    content = _element$content === void 0 ? 'CONFIDENTIEL' : _element$content,
    _element$color = element.color,
    color = _element$color === void 0 ? '#999999' : _element$color,
    _element$fontSize = element.fontSize,
    fontSize = _element$fontSize === void 0 ? 48 : _element$fontSize,
    _element$fontFamily = element.fontFamily,
    fontFamily = _element$fontFamily === void 0 ? 'Arial, sans-serif' : _element$fontFamily,
    _element$fontWeight = element.fontWeight,
    fontWeight = _element$fontWeight === void 0 ? 'bold' : _element$fontWeight,
    _element$opacity = element.opacity,
    opacity = _element$opacity === void 0 ? 10 : _element$opacity,
    _element$rotation = element.rotation,
    rotation = _element$rotation === void 0 ? -45 : _element$rotation,
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
  var containerStyle = {
    position: 'absolute',
    left: x * canvasScale,
    top: y * canvasScale,
    width: width * canvasScale,
    height: height * canvasScale,
    display: visible ? 'flex' : 'none',
    alignItems: 'center',
    justifyContent: 'center',
    pointerEvents: 'none',
    zIndex: 1000
  };
  var textStyle = {
    color: color,
    fontSize: "".concat(fontSize * canvasScale, "px"),
    fontFamily: fontFamily,
    fontWeight: fontWeight,
    opacity: opacity / 100,
    transform: "rotate(".concat(rotation, "deg) scale(").concat(scale, ")"),
    transformOrigin: 'center center',
    textAlign: 'center',
    userSelect: 'none',
    whiteSpace: 'nowrap',
    textDecoration: textDecoration,
    lineHeight: lineHeight,
    // Ombres
    boxShadow: shadow ? "".concat(shadowOffsetX, "px ").concat(shadowOffsetY, "px 4px ").concat(shadowColor) : 'none'
  };
  return /*#__PURE__*/react.createElement("div", {
    className: "preview-element preview-watermark-element",
    style: containerStyle,
    "data-element-id": element.id,
    "data-element-type": "watermark"
  }, /*#__PURE__*/react.createElement("div", {
    style: textStyle
  }, content));
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
    left: x * canvasScale,
    top: y * canvasScale,
    width: width * canvasScale,
    height: height * canvasScale,
    backgroundColor: backgroundColor,
    border: borderWidth > 0 ? "".concat(borderWidth, "px solid ").concat(borderColor) : 'none',
    borderRadius: "".concat(borderRadius, "px"),
    opacity: opacity / 100,
    overflow: 'hidden',
    display: visible ? 'block' : 'none',
    // Transformations
    transform: "rotate(".concat(rotation, "deg) scale(").concat(scale, ")"),
    transformOrigin: 'center center',
    // Ombres
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
;// ./resources/js/components/preview-system/renderers/MentionsRenderer.jsx


/**
 * Renderer pour les mentions légales
 */
var MentionsRenderer = function MentionsRenderer(_ref) {
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
    width = _element$width === void 0 ? 400 : _element$width,
    _element$height = element.height,
    height = _element$height === void 0 ? 60 : _element$height,
    _element$showEmail = element.showEmail,
    showEmail = _element$showEmail === void 0 ? true : _element$showEmail,
    _element$showPhone = element.showPhone,
    showPhone = _element$showPhone === void 0 ? true : _element$showPhone,
    _element$showSiret = element.showSiret,
    showSiret = _element$showSiret === void 0 ? true : _element$showSiret,
    _element$showVat = element.showVat,
    showVat = _element$showVat === void 0 ? false : _element$showVat,
    _element$showAddress = element.showAddress,
    showAddress = _element$showAddress === void 0 ? false : _element$showAddress,
    _element$showWebsite = element.showWebsite,
    showWebsite = _element$showWebsite === void 0 ? false : _element$showWebsite,
    _element$showCustomTe = element.showCustomText,
    showCustomText = _element$showCustomTe === void 0 ? false : _element$showCustomTe,
    _element$customText = element.customText,
    customText = _element$customText === void 0 ? '' : _element$customText,
    _element$fontSize = element.fontSize,
    fontSize = _element$fontSize === void 0 ? 8 : _element$fontSize,
    _element$fontFamily = element.fontFamily,
    fontFamily = _element$fontFamily === void 0 ? 'Arial' : _element$fontFamily,
    _element$fontWeight = element.fontWeight,
    fontWeight = _element$fontWeight === void 0 ? 'normal' : _element$fontWeight,
    _element$textAlign = element.textAlign,
    textAlign = _element$textAlign === void 0 ? 'center' : _element$textAlign,
    _element$color = element.color,
    color = _element$color === void 0 ? '#666666' : _element$color,
    _element$lineHeight = element.lineHeight,
    lineHeight = _element$lineHeight === void 0 ? 1.2 : _element$lineHeight,
    _element$separator = element.separator,
    separator = _element$separator === void 0 ? ' • ' : _element$separator,
    _element$layout = element.layout,
    layout = _element$layout === void 0 ? 'horizontal' : _element$layout,
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
    textDecoration = _element$textDecorati === void 0 ? 'none' : _element$textDecorati;

  // Récupérer les données des mentions
  var elementKey = "mentions_".concat(element.id);
  var mentionsData = previewData[elementKey] || {};
  var mentions = mentionsData.mentions || [];
  var containerStyle = {
    position: 'absolute',
    left: x * canvasScale,
    top: y * canvasScale,
    width: width * canvasScale,
    height: height * canvasScale,
    backgroundColor: backgroundColor,
    border: borderWidth > 0 ? "".concat(borderWidth, "px solid ").concat(borderColor) : 'none',
    borderRadius: "".concat(borderRadius, "px"),
    opacity: opacity,
    padding: '4px',
    boxSizing: 'border-box',
    overflow: 'hidden',
    fontSize: "".concat(fontSize * canvasScale, "px"),
    fontFamily: fontFamily,
    fontWeight: fontWeight,
    textAlign: textAlign,
    color: color,
    lineHeight: lineHeight,
    display: visible ? 'flex' : 'none',
    alignItems: 'center',
    justifyContent: layout === 'horizontal' ? 'center' : 'flex-start',
    flexDirection: layout === 'vertical' ? 'column' : 'row',
    gap: layout === 'vertical' ? '4px' : '0',
    textDecoration: textDecoration,
    // Transformations
    transform: "rotate(".concat(rotation, "deg) scale(").concat(scale, ")"),
    transformOrigin: 'center center',
    // Ombres
    boxShadow: shadow ? "".concat(shadowOffsetX, "px ").concat(shadowOffsetY, "px 4px ").concat(shadowColor) : 'none'
  };
  var mentionStyle = {
    flexShrink: 0
  };
  return /*#__PURE__*/react.createElement("div", {
    className: "preview-element preview-mentions-element",
    style: containerStyle,
    "data-element-id": element.id,
    "data-element-type": "mentions"
  }, mentions.map(function (mention, index) {
    return /*#__PURE__*/react.createElement(react.Fragment, {
      key: index
    }, /*#__PURE__*/react.createElement("span", {
      style: mentionStyle
    }, mention), index < mentions.length - 1 && layout === 'horizontal' && /*#__PURE__*/react.createElement("span", {
      style: {
        margin: '0 8px',
        color: '#999'
      }
    }, separator));
  }), mentions.length === 0 && /*#__PURE__*/react.createElement("div", {
    style: {
      textAlign: 'center',
      color: '#6c757d',
      fontStyle: 'italic',
      width: '100%'
    }
  }, "Aucune mention l\xE9gale"));
};
;// ./resources/js/components/preview-system/modes/CanvasMode.jsx
function CanvasMode_typeof(o) { "@babel/helpers - typeof"; return CanvasMode_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, CanvasMode_typeof(o); }
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
function CanvasMode_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function CanvasMode_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? CanvasMode_ownKeys(Object(t), !0).forEach(function (r) { CanvasMode_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : CanvasMode_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function CanvasMode_defineProperty(e, r, t) { return (r = CanvasMode_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function CanvasMode_toPropertyKey(t) { var i = CanvasMode_toPrimitive(t, "string"); return "symbol" == CanvasMode_typeof(i) ? i : i + ""; }
function CanvasMode_toPrimitive(t, r) { if ("object" != CanvasMode_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != CanvasMode_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }



// Import des renderers














/**
 * CanvasMode - Aperçu spatial du canvas avec données d'exemple
 * Rend tous les éléments du canvas à leurs positions avec des données fictives
 */
function CanvasMode() {
  var _config$templateData, _config$templateData2;
  console.log('CanvasMode - Component rendering started');
  var _usePreviewContext = (0,PreviewContext.usePreviewContext)(),
    state = _usePreviewContext.state;
  console.log('CanvasMode - usePreviewContext returned:', {
    state: state
  });
  var data = state.data,
    config = state.config;

  // Récupérer les éléments depuis la config (passés via PreviewModal)
  var elements = (config === null || config === void 0 ? void 0 : config.elements) || [];
  var previewData = data || {};
  console.log('CanvasMode - State:', state);
  console.log('CanvasMode - Config:', config);
  console.log('CanvasMode - Elements:', elements);
  console.log('CanvasMode - PreviewData:', previewData);

  // Dimensions du canvas (A4 par défaut)
  var canvasWidth = (config === null || config === void 0 || (_config$templateData = config.templateData) === null || _config$templateData === void 0 ? void 0 : _config$templateData.width) || 595;
  var canvasHeight = (config === null || config === void 0 || (_config$templateData2 = config.templateData) === null || _config$templateData2 === void 0 ? void 0 : _config$templateData2.height) || 842;

  // Calculer l'échelle pour que le canvas tienne dans la modal
  // La modal fait environ 800px de large et 600px de haut
  // Laissons une marge de 100px de chaque côté
  var maxWidth = 600;
  var maxHeight = 400;
  var scaleX = maxWidth / canvasWidth;
  var scaleY = maxHeight / canvasHeight;
  var scale = Math.min(scaleX, scaleY, 1); // Ne pas agrandir si plus petit

  console.log('CanvasMode - Canvas dimensions:', {
    canvasWidth: canvasWidth,
    canvasHeight: canvasHeight
  });
  console.log('CanvasMode - Scale calculations:', {
    scaleX: scaleX,
    scaleY: scaleY,
    scale: scale
  });

  // Fonction pour obtenir le renderer approprié selon le type d'élément
  var getRenderer = function getRenderer(element) {
    console.log('CanvasMode - getRenderer called for element:', element.type, element.id);
    console.log('CanvasMode - Rendering element:', element);
    var elementKey = "".concat(element.type, "_").concat(element.id);
    var elementData = previewData[elementKey] || {};
    console.log('CanvasMode - Element key:', elementKey, 'Element data:', elementData);
    var commonProps = {
      element: CanvasMode_objectSpread(CanvasMode_objectSpread({}, element), elementData),
      previewData: previewData,
      mode: 'canvas',
      canvasScale: scale
    };
    switch (element.type) {
      case 'text':
        return /*#__PURE__*/react.createElement(TextRenderer, _extends({
          key: element.id
        }, commonProps));
      case 'rectangle':
        return /*#__PURE__*/react.createElement(RectangleRenderer, _extends({
          key: element.id
        }, commonProps));
      case 'image':
        return /*#__PURE__*/react.createElement(ImageRenderer.ImageRenderer, _extends({
          key: element.id
        }, commonProps));
      case 'table':
        return /*#__PURE__*/react.createElement(TableRenderer, _extends({
          key: element.id
        }, commonProps));
      case 'barcode':
        return /*#__PURE__*/react.createElement(BarcodeRenderer, _extends({
          key: element.id
        }, commonProps));
      case 'dynamic-text':
        return /*#__PURE__*/react.createElement(DynamicTextRenderer, _extends({
          key: element.id
        }, commonProps));
      case 'customer-info':
        return /*#__PURE__*/react.createElement(CustomerInfoRenderer, _extends({
          key: element.id
        }, commonProps));
      case 'company-info':
        return /*#__PURE__*/react.createElement(CompanyInfoRenderer, _extends({
          key: element.id
        }, commonProps));
      case 'order-number':
        return /*#__PURE__*/react.createElement(OrderNumberRenderer, _extends({
          key: element.id
        }, commonProps));
      case 'pdf':
        // Placeholder pour les éléments PDF dans l'aperçu canvas
        return /*#__PURE__*/react.createElement("div", {
          key: element.id,
          style: {
            position: 'absolute',
            left: element.x * scale,
            top: element.y * scale,
            width: element.width * scale,
            height: element.height * scale,
            backgroundColor: '#f8f9fa',
            border: '2px solid #dee2e6',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: "".concat(14 * scale, "px"),
            color: '#6c757d',
            borderRadius: '4px'
          }
        }, "\uD83D\uDCC4 PDF Embed");
      case 'watermark':
        return /*#__PURE__*/react.createElement(WatermarkRenderer, _extends({
          key: element.id
        }, commonProps));
      case 'progress-bar':
        return /*#__PURE__*/react.createElement(ProgressBarRenderer, _extends({
          key: element.id
        }, commonProps));
      case 'mentions':
        console.log('CanvasMode - Rendering mentions element:', element, 'with data:', elementData);
        return /*#__PURE__*/react.createElement(MentionsRenderer, _extends({
          key: element.id
        }, commonProps));
      default:
        return /*#__PURE__*/react.createElement("div", {
          key: element.id,
          style: {
            position: 'absolute',
            left: (element.x || 0) * scale,
            top: (element.y || 0) * scale,
            width: (element.width || 100) * scale,
            height: (element.height || 50) * scale,
            backgroundColor: '#f0f0f0',
            border: '2px dashed #ccc',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: "".concat(12 * scale, "px"),
            color: '#666'
          }
        }, element.type || 'unknown');
    }
  };
  return /*#__PURE__*/react.createElement("div", {
    className: "canvas-mode-preview"
  }, /*#__PURE__*/react.createElement("div", {
    className: "canvas-mode-canvas",
    style: {
      width: canvasWidth,
      height: canvasHeight,
      backgroundColor: '#ffffff',
      position: 'relative',
      margin: '0 auto',
      boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)',
      border: '1px solid #e1e1e1',
      transform: "scale(".concat(scale, ")"),
      transformOrigin: 'top center'
    }
  }, elements.map(function (element) {
    console.log('CanvasMode - Mapping element:', element);
    return getRenderer(element);
  }), elements.length === 0 && /*#__PURE__*/react.createElement("div", {
    style: {
      position: 'absolute',
      top: '50%',
      left: '50%',
      transform: 'translate(-50%, -50%)',
      textAlign: 'center',
      color: '#666',
      fontSize: '16px'
    }
  }, /*#__PURE__*/react.createElement("div", {
    style: {
      fontSize: '48px',
      marginBottom: '16px'
    }
  }, "\uD83D\uDCC4"), /*#__PURE__*/react.createElement("div", null, "Aucun \xE9l\xE9ment dans le canvas"), /*#__PURE__*/react.createElement("div", {
    style: {
      fontSize: '14px',
      marginTop: '8px'
    }
  }, "Ajoutez des \xE9l\xE9ments dans l'\xE9diteur pour les voir ici"))), /*#__PURE__*/react.createElement("div", {
    style: {
      marginTop: '20px',
      padding: '16px',
      backgroundColor: '#f8f9fa',
      borderRadius: '8px',
      textAlign: 'center',
      fontSize: '14px',
      color: '#666'
    }
  }, /*#__PURE__*/react.createElement("strong", null, "\uD83D\uDCCB Aper\xE7u du Canvas"), /*#__PURE__*/react.createElement("br", null), /*#__PURE__*/react.createElement("span", null, "Dimensions: ", canvasWidth, " \xD7 ", canvasHeight, " points (", Math.round(canvasWidth * 0.3528), " \xD7 ", Math.round(canvasHeight * 0.3528), " mm)"), /*#__PURE__*/react.createElement("br", null), /*#__PURE__*/react.createElement("span", null, "\xC9l\xE9ments: ", elements.length), Object.keys(previewData).length > 0 && /*#__PURE__*/react.createElement(react.Fragment, null, /*#__PURE__*/react.createElement("br", null), /*#__PURE__*/react.createElement("span", null, "\uD83D\uDD04 Donn\xE9es d'exemple charg\xE9es"))));
}
/* harmony default export */ const modes_CanvasMode = (CanvasMode);

/***/ })

}]);