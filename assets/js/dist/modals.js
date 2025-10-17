"use strict";
(self["webpackChunkwp_pdf_builder_pro"] = self["webpackChunkwp_pdf_builder_pro"] || []).push([[341],{

/***/ 510:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   A: () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(540);
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

var NewTemplateModal = function NewTemplateModal(_ref) {
  var isOpen = _ref.isOpen,
    onClose = _ref.onClose,
    onCreateTemplate = _ref.onCreateTemplate;
  var _useState = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)({
      name: '',
      defaultModel: 'Facture',
      description: '',
      isPublic: false,
      paperFormat: 'A4 (210 × 297 mm)',
      orientation: 'Portrait',
      category: 'Facture'
    }),
    _useState2 = _slicedToArray(_useState, 2),
    formData = _useState2[0],
    setFormData = _useState2[1];
  var _useState3 = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(false),
    _useState4 = _slicedToArray(_useState3, 2),
    showAdvanced = _useState4[0],
    setShowAdvanced = _useState4[1];
  var _useState5 = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)({}),
    _useState6 = _slicedToArray(_useState5, 2),
    errors = _useState6[0],
    setErrors = _useState6[1];
  var handleInputChange = function handleInputChange(field, value) {
    setFormData(function (prev) {
      return _objectSpread(_objectSpread({}, prev), {}, _defineProperty({}, field, value));
    });
    // Clear error when user starts typing
    if (errors[field]) {
      setErrors(function (prev) {
        return _objectSpread(_objectSpread({}, prev), {}, _defineProperty({}, field, ''));
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
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("div", {
    className: "modal-overlay",
    onClick: onClose
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("div", {
    className: "modal-content new-template-modal",
    onClick: function onClick(e) {
      return e.stopPropagation();
    }
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("div", {
    className: "modal-header"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("h3", null, "Nouveau template"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("button", {
    className: "modal-close",
    onClick: onClose
  }, "\xD7")), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("form", {
    onSubmit: handleSubmit,
    className: "modal-body"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("div", {
    className: "form-group"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("label", {
    htmlFor: "template-name"
  }, "Nom du template *"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("input", {
    id: "template-name",
    type: "text",
    value: formData.name,
    onChange: function onChange(e) {
      return handleInputChange('name', e.target.value);
    },
    className: errors.name ? 'error' : '',
    placeholder: "Ex: Facture Standard"
  }), errors.name && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("span", {
    className: "error-message"
  }, errors.name)), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("div", {
    className: "form-group"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("label", {
    htmlFor: "default-model"
  }, "Mod\xE8le par d\xE9faut"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("select", {
    id: "default-model",
    value: formData.defaultModel,
    onChange: function onChange(e) {
      return handleInputChange('defaultModel', e.target.value);
    }
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("option", {
    value: "Facture"
  }, "Facture"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("option", {
    value: "Devis"
  }, "Devis"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("option", {
    value: "Bon de commande"
  }, "Bon de commande"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("option", {
    value: "Bon de livraison"
  }, "Bon de livraison"))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("div", {
    className: "form-group"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("label", {
    htmlFor: "description"
  }, "Description"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("textarea", {
    id: "description",
    value: formData.description,
    onChange: function onChange(e) {
      return handleInputChange('description', e.target.value);
    },
    placeholder: "Description du template...",
    rows: 3
  })), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("div", {
    className: "form-group"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("button", {
    type: "button",
    className: "advanced-toggle",
    onClick: function onClick() {
      return setShowAdvanced(!showAdvanced);
    }
  }, "Param\xE8tres avanc\xE9s ", showAdvanced ? '▼' : '▶')), showAdvanced && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("div", {
    className: "advanced-settings"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("div", {
    className: "form-group checkbox-group"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("label", {
    className: "checkbox-label"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("input", {
    type: "checkbox",
    checked: formData.isPublic,
    onChange: function onChange(e) {
      return handleInputChange('isPublic', e.target.checked);
    }
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("span", null, "Template public (visible par tous les utilisateurs)"))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("div", {
    className: "form-group"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("label", {
    htmlFor: "paper-format"
  }, "Format de papier"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("select", {
    id: "paper-format",
    value: formData.paperFormat,
    onChange: function onChange(e) {
      return handleInputChange('paperFormat', e.target.value);
    }
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("option", {
    value: "A4 (210 \xD7 297 mm)"
  }, "A4 (210 \xD7 297 mm)"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("option", {
    value: "A5 (148 \xD7 210 mm)"
  }, "A5 (148 \xD7 210 mm)"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("option", {
    value: "Lettre (8.5 \xD7 11 pouces)"
  }, "Lettre (8.5 \xD7 11 pouces)"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("option", {
    value: "Legal (8.5 \xD7 14 pouces)"
  }, "Legal (8.5 \xD7 14 pouces)"))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("div", {
    className: "form-group"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("label", {
    htmlFor: "orientation"
  }, "Orientation"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("select", {
    id: "orientation",
    value: formData.orientation,
    onChange: function onChange(e) {
      return handleInputChange('orientation', e.target.value);
    }
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("option", {
    value: "Portrait"
  }, "Portrait"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("option", {
    value: "Paysage"
  }, "Paysage"))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("div", {
    className: "form-group"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("label", {
    htmlFor: "category"
  }, "Cat\xE9gorie"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("select", {
    id: "category",
    value: formData.category,
    onChange: function onChange(e) {
      return handleInputChange('category', e.target.value);
    }
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("option", {
    value: "Facture"
  }, "Facture"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("option", {
    value: "Devis"
  }, "Devis"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("option", {
    value: "Bon de commande"
  }, "Bon de commande"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("option", {
    value: "Bon de livraison"
  }, "Bon de livraison"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("option", {
    value: "Re\xE7u"
  }, "Re\xE7u"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("option", {
    value: "Autre"
  }, "Autre")))), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("div", {
    className: "modal-footer"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("button", {
    type: "button",
    className: "btn-secondary",
    onClick: onClose
  }, "Annuler"), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement("button", {
    type: "submit",
    className: "btn-primary"
  }, "Ouvrir le template")))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (NewTemplateModal);

/***/ }),

/***/ 690:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   A: () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(540);
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }


// Nouveau système d'aperçu côté serveur avec TCPDF

var PreviewModal = function PreviewModal(_ref) {
  var isOpen = _ref.isOpen,
    onClose = _ref.onClose,
    _ref$elements = _ref.elements,
    elements = _ref$elements === void 0 ? [] : _ref$elements,
    _ref$canvasWidth = _ref.canvasWidth,
    canvasWidth = _ref$canvasWidth === void 0 ? 595 : _ref$canvasWidth,
    _ref$canvasHeight = _ref.canvasHeight,
    canvasHeight = _ref$canvasHeight === void 0 ? 842 : _ref$canvasHeight,
    _ref$zoom = _ref.zoom,
    zoom = _ref$zoom === void 0 ? 1 : _ref$zoom,
    ajaxurl = _ref.ajaxurl,
    pdfBuilderNonce = _ref.pdfBuilderNonce,
    _ref$onOpenPDFModal = _ref.onOpenPDFModal,
    onOpenPDFModal = _ref$onOpenPDFModal === void 0 ? null : _ref$onOpenPDFModal,
    _ref$useServerPreview = _ref.useServerPreview,
    useServerPreview = _ref$useServerPreview === void 0 ? false : _ref$useServerPreview;
  var _useState = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(null),
    _useState2 = _slicedToArray(_useState, 2),
    previewData = _useState2[0],
    setPreviewData = _useState2[1];
  var _useState3 = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(false),
    _useState4 = _slicedToArray(_useState3, 2),
    loading = _useState4[0],
    setLoading = _useState4[1];
  var _useState5 = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(null),
    _useState6 = _slicedToArray(_useState5, 2),
    error = _useState6[0],
    setError = _useState6[1];

  // Fonction pour nettoyer les éléments avant sérialisation JSON
  var cleanElementsForJSON = function cleanElementsForJSON(elements) {
    if (!Array.isArray(elements)) {
      throw new Error('Les éléments doivent être un tableau');
    }
    return elements.map(function (element) {
      if (!element || _typeof(element) !== 'object') {
        throw new Error('Chaque élément doit être un objet valide');
      }

      // Créer une copie profonde de l'élément
      var cleaned = JSON.parse(JSON.stringify(element));

      // S'assurer que les propriétés numériques sont des nombres
      var numericProps = ['x', 'y', 'width', 'height', 'fontSize', 'padding', 'zIndex', 'borderWidth'];
      numericProps.forEach(function (prop) {
        if (cleaned[prop] !== undefined) {
          var numValue = parseFloat(cleaned[prop]);
          if (isNaN(numValue)) {
            throw new Error("Propri\xE9t\xE9 ".concat(prop, " doit \xEAtre un nombre valide"));
          }
          cleaned[prop] = numValue;
        }
      });

      // Valider les propriétés requises
      if (typeof cleaned.type !== 'string') {
        throw new Error('Chaque élément doit avoir un type string');
      }

      // Nettoyer les propriétés potentiellement problématiques
      delete cleaned.tempId; // Supprimer les IDs temporaires si présents
      delete cleaned.isDragging; // Supprimer les états d'interaction
      delete cleaned.isResizing; // Supprimer les états d'interaction

      return cleaned;
    });
  };

  // Fonction de validation des éléments avant envoi
  var validateElementsBeforeSend = function validateElementsBeforeSend(elements) {
    try {
      var cleanedElements = cleanElementsForJSON(elements);
      var jsonString = JSON.stringify(cleanedElements);

      // Vérifier que le JSON est valide
      JSON.parse(jsonString);

      // Vérifier la longueur raisonnable
      if (jsonString.length > 10000000) {
        // 10MB max
        throw new Error('JSON trop volumineux');
      }
      return {
        success: true,
        jsonString: jsonString,
        cleanedElements: cleanedElements
      };
    } catch (error) {
      console.error('Client-side validation failed:', error);
      return {
        success: false,
        error: error.message
      };
    }
  };

  // Fonction pour rendre le contenu du canvas en HTML
  var renderCanvasContent = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (elements) {
    // Réduire les logs pour éviter la boucle infinie - n'afficher que les erreurs importantes
    if (!elements || elements.length === 0) {
      return /*#__PURE__*/React.createElement("div", {
        style: {
          padding: '20px',
          textAlign: 'center',
          color: '#666'
        }
      }, "Aucun \xE9l\xE9ment \xE0 afficher");
    }

    // Vérifier que zoom est valide
    var validZoom = typeof zoom === 'number' && !isNaN(zoom) && zoom > 0 ? zoom : 1;
    return /*#__PURE__*/React.createElement("div", {
      style: {
        position: 'relative',
        width: canvasWidth * validZoom,
        height: canvasHeight * validZoom,
        backgroundColor: 'white',
        border: '1px solid #e2e8f0',
        borderRadius: '4px',
        overflow: 'hidden',
        margin: '0 auto'
      }
    }, elements.map(function (element, index) {
      // Vérifier que les propriétés essentielles existent
      if (typeof element.x !== 'number' || typeof element.y !== 'number' || typeof element.width !== 'number' || typeof element.height !== 'number') {
        console.error('❌ Element missing required properties:', element);
        return null;
      }
      var elementPadding = element.padding || 0;
      var baseStyle = {
        position: 'absolute',
        left: (element.x + elementPadding) * validZoom,
        top: (element.y + elementPadding) * validZoom,
        width: Math.max(1, element.width - elementPadding * 2) * validZoom,
        height: Math.max(1, element.height - elementPadding * 2) * validZoom,
        zIndex: element.zIndex || index + 1
      };
      return /*#__PURE__*/React.createElement("div", {
        key: index,
        style: baseStyle
      }, renderSpecialElement(element, validZoom));
    }));
  }, [zoom, canvasWidth, canvasHeight]);

  // Fonction pour rendre un élément spécial (basée sur CanvasElement.jsx)
  var renderSpecialElement = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(function (element, zoom) {
    // Réduire les logs - n'afficher que les erreurs importantes
    switch (element.type) {
      case 'text':
        return /*#__PURE__*/React.createElement("div", {
          style: {
            width: '100%',
            height: '100%',
            fontSize: (element.fontSize || 16) * zoom,
            color: element.color || '#000000',
            fontWeight: element.fontWeight === 'bold' ? 'bold' : 'normal',
            fontStyle: element.fontStyle === 'italic' ? 'italic' : 'normal',
            textDecoration: element.textDecoration || 'none',
            textAlign: element.textAlign || 'left',
            lineHeight: element.lineHeight || '1.2',
            whiteSpace: 'pre-wrap',
            overflow: 'hidden',
            padding: "".concat(4 * zoom, "px"),
            boxSizing: 'border-box'
          }
        }, element.content || element.text || 'Texte');
      case 'rectangle':
        return /*#__PURE__*/React.createElement("div", {
          style: {
            width: '100%',
            height: '100%',
            backgroundColor: element.fillColor || 'transparent',
            border: element.borderWidth ? "".concat(element.borderWidth * zoom, "px ").concat(element.borderStyle || 'solid', " ").concat(element.borderColor || '#000000') : 'none',
            borderRadius: (element.borderRadius || 0) * zoom
          }
        });
      case 'image':
        return /*#__PURE__*/React.createElement("img", {
          src: element.src || element.imageUrl || '',
          alt: element.alt || 'Image',
          style: {
            width: '100%',
            height: '100%',
            objectFit: element.objectFit || 'cover',
            borderRadius: (element.borderRadius || 0) * zoom
          },
          onError: function onError(e) {
            e.target.style.display = 'none';
          }
        });
      case 'line':
        return /*#__PURE__*/React.createElement("div", {
          style: _defineProperty({
            width: '100%',
            height: (element.height || element.strokeWidth || 1) * zoom,
            borderTop: "".concat((element.strokeWidth || 1) * zoom, "px solid ").concat(element.strokeColor || '#000000')
          }, "height", 0)
        });
      case 'divider':
        return /*#__PURE__*/React.createElement("div", {
          style: _defineProperty(_defineProperty(_defineProperty({
            width: '100%',
            height: '100%',
            backgroundColor: element.color || element.fillColor || '#cccccc'
          }, "height", "".concat((element.thickness || element.height || 2) * zoom, "px")), "margin", "".concat((element.margin || 10) * zoom, "px 0")), "borderRadius", (element.borderRadius || 0) * zoom)
        });
      case 'product_table':
        // Rendu dynamique du tableau de produits utilisant les propriétés de l'élément
        var getTableStyles = function getTableStyles() {
          var tableStyle = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'default';
          var baseStyles = {
            "default": {
              headerBg: '#f8fafc',
              headerBorder: '#e2e8f0',
              rowBorder: '#f1f5f9',
              altRowBg: '#fafbfc',
              borderWidth: 1,
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
              gradient: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
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
              gradient: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
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
              gradient: 'linear-gradient(135deg, #064e3b 0%, #10b981 100%)',
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
              gradient: 'linear-gradient(135deg, #9a3412 0%, #f97316 100%)',
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
              gradient: 'linear-gradient(135deg, #581c87 0%, #a855f7 100%)',
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
              gradient: 'linear-gradient(135deg, #be185d 0%, #f472b6 100%)',
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
              gradient: 'linear-gradient(135deg, #0f766e 0%, #14b8a6 100%)',
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
              gradient: 'linear-gradient(135deg, #991b1b 0%, #ef4444 100%)',
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
              gradient: 'linear-gradient(135deg, #92400e 0%, #f59e0b 100%)',
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
              gradient: 'linear-gradient(135deg, #312e81 0%, #6366f1 100%)',
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
              gradient: 'linear-gradient(135deg, #374151 0%, #6b7280 100%)',
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
              gradient: 'linear-gradient(135deg, #c2410c 0%, #fb7185 100%)',
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
              gradient: 'linear-gradient(135deg, #065f46 0%, #34d399 100%)',
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
              gradient: 'linear-gradient(135deg, #6d28d9 0%, #c084fc 100%)',
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
              gradient: 'linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%)',
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
              gradient: 'linear-gradient(135deg, #14532d 0%, #22c55e 100%)',
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
              gradient: 'linear-gradient(135deg, #b91c1c 0%, #f87171 100%)',
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
              gradient: 'linear-gradient(135deg, #a16207 0%, #eab308 100%)',
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
              gradient: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
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
              gradient: 'linear-gradient(135deg, #7f1d1d 0%, #dc2626 100%)',
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
              gradient: 'linear-gradient(135deg, #7c2d12 0%, #a855f7 100%)',
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
              gradient: 'linear-gradient(135deg, #134e4a 0%, #14b8a6 100%)',
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
              gradient: 'linear-gradient(135deg, #be185d 0%, #fb7185 100%)',
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
              gradient: 'linear-gradient(135deg, #9a3412 0%, #fb923c 100%)',
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
        var tableStyles = getTableStyles(element.tableStyle);
        var showHeaders = element.showHeaders !== false;
        var showBorders = element.showBorders !== false;
        var columns = element.columns || {
          image: false,
          name: true,
          sku: false,
          quantity: true,
          price: true,
          total: true
        };
        var headers = element.headers || ['Produit', 'Qté', 'Prix'];

        // Fonction pour obtenir l'en-tête d'une colonne
        var getColumnHeader = function getColumnHeader(columnType) {
          var defaultHeaders = {
            image: 'Img',
            name: headers[0] || 'Produit',
            sku: 'SKU',
            quantity: headers[1] || 'Qté',
            price: headers[2] || 'Prix',
            total: 'Total'
          };
          return defaultHeaders[columnType] || columnType;
        };

        // Données d'exemple pour l'aperçu
        var products = [{
          name: 'Produit A - Description',
          sku: 'SKU001',
          quantity: 2,
          price: 19.99,
          total: 39.98
        }, {
          name: 'Produit B - Article',
          sku: 'SKU002',
          quantity: 1,
          price: 29.99,
          total: 29.99
        }];

        // Calcul des totaux
        var subtotal = products.reduce(function (sum, product) {
          return sum + product.total;
        }, 0);
        var shipping = element.showShipping ? 5.00 : 0;
        var tax = element.showTaxes ? 2.25 : 0;
        var discount = element.showDiscount ? -5.00 : 0;
        var total = subtotal + shipping + tax + discount;

        // Déterminer la dernière colonne visible pour les totaux
        var getLastVisibleColumn = function getLastVisibleColumn() {
          var columnKeys = ['image', 'name', 'sku', 'quantity', 'price', 'total'];
          for (var i = columnKeys.length - 1; i >= 0; i--) {
            if (columns[columnKeys[i]] !== false) {
              return columnKeys[i];
            }
          }
          return 'total';
        };
        var lastVisibleColumn = getLastVisibleColumn();
        return /*#__PURE__*/React.createElement("div", {
          style: {
            width: '100%',
            height: '100%',
            display: 'flex',
            flexDirection: 'column',
            fontSize: "".concat((element.fontSize || 10) * zoom, "px"),
            fontFamily: element.fontFamily || 'Arial, sans-serif',
            border: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : element.borderWidth && element.borderWidth > 0 ? "".concat(Math.max(1, element.borderWidth * 0.5) * zoom, "px solid ").concat(element.borderColor || '#e5e7eb') : 'none',
            borderRadius: "".concat((element.borderRadius || 2) * zoom, "px"),
            overflow: 'hidden',
            backgroundColor: element.backgroundColor || 'transparent',
            boxSizing: 'border-box',
            boxShadow: tableStyles.shadow && element.tableStyle === 'modern' ? "0 4px 8px ".concat(tableStyles.shadow) : 'none'
          }
        }, showHeaders && /*#__PURE__*/React.createElement("div", {
          style: {
            display: 'flex',
            background: tableStyles.gradient || tableStyles.headerBg,
            borderBottom: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none',
            fontWeight: 'bold',
            color: tableStyles.headerTextColor || (element.tableStyle === 'modern' ? '#ffffff' : '#000000'),
            boxShadow: tableStyles.shadow ? "0 2px 4px ".concat(tableStyles.shadow) : 'none'
          }
        }, columns.image && /*#__PURE__*/React.createElement("div", {
          style: {
            flex: "0 0 ".concat(40 * zoom, "px"),
            padding: "".concat(4 * zoom, "px"),
            textAlign: 'center',
            borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none'
          }
        }, getColumnHeader('image')), columns.name && /*#__PURE__*/React.createElement("div", {
          style: {
            flex: 1,
            padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
            textAlign: 'left',
            borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none'
          }
        }, getColumnHeader('name')), columns.sku && /*#__PURE__*/React.createElement("div", {
          style: {
            flex: "0 0 ".concat(80 * zoom, "px"),
            padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
            textAlign: 'left',
            borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none'
          }
        }, getColumnHeader('sku')), columns.quantity && /*#__PURE__*/React.createElement("div", {
          style: {
            flex: "0 0 ".concat(60 * zoom, "px"),
            padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
            textAlign: 'center',
            borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none'
          }
        }, getColumnHeader('quantity')), columns.price && /*#__PURE__*/React.createElement("div", {
          style: {
            flex: "0 0 ".concat(80 * zoom, "px"),
            padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
            textAlign: 'right',
            borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none'
          }
        }, getColumnHeader('price')), columns.total && /*#__PURE__*/React.createElement("div", {
          style: {
            flex: '0 0 80px',
            padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
            textAlign: 'right'
          }
        }, getColumnHeader('total'))), /*#__PURE__*/React.createElement("div", {
          style: {
            flex: 1,
            display: 'flex',
            flexDirection: 'column'
          }
        }, products.map(function (product, index) {
          return /*#__PURE__*/React.createElement("div", {
            key: index,
            style: {
              display: 'flex',
              borderBottom: showBorders ? "".concat(tableStyles.borderWidth, "px solid ").concat(tableStyles.rowBorder) : 'none',
              backgroundColor: element.tableStyle === 'striped' && index % 2 === 1 ? tableStyles.altRowBg : 'transparent',
              color: tableStyles.rowTextColor || '#000000',
              boxShadow: tableStyles.shadow ? "0 1px 2px ".concat(tableStyles.shadow) : 'none'
            }
          }, columns.image && /*#__PURE__*/React.createElement("div", {
            style: {
              flex: "0 0 ".concat(40 * zoom, "px"),
              padding: "".concat(4 * zoom, "px"),
              textAlign: 'center',
              borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none'
            }
          }, "\uD83D\uDCF7"), columns.name && /*#__PURE__*/React.createElement("div", {
            style: {
              flex: 1,
              padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
              borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none'
            }
          }, product.name), columns.sku && /*#__PURE__*/React.createElement("div", {
            style: {
              flex: "0 0 ".concat(80 * zoom, "px"),
              padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
              borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none'
            }
          }, product.sku), columns.quantity && /*#__PURE__*/React.createElement("div", {
            style: {
              flex: "0 0 ".concat(60 * zoom, "px"),
              padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
              textAlign: 'center',
              borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none'
            }
          }, product.quantity), columns.price && /*#__PURE__*/React.createElement("div", {
            style: {
              flex: "0 0 ".concat(80 * zoom, "px"),
              padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
              textAlign: 'right',
              borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none'
            }
          }, product.price.toFixed(2), "\u20AC"), columns.total && /*#__PURE__*/React.createElement("div", {
            style: {
              flex: "0 0 ".concat(80 * zoom, "px"),
              padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
              textAlign: 'right'
            }
          }, product.total.toFixed(2), "\u20AC"));
        })), /*#__PURE__*/React.createElement("div", {
          style: {
            borderTop: showBorders ? "".concat(tableStyles.borderWidth, "px solid ").concat(tableStyles.headerBorder) : 'none'
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
            width: "".concat(80 * zoom, "px"),
            textAlign: 'right'
          }
        }, "Sous-total: ", subtotal.toFixed(2), "\u20AC")), element.showShipping && shipping > 0 && /*#__PURE__*/React.createElement("div", {
          style: {
            display: 'flex',
            justifyContent: 'flex-end',
            padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px")
          }
        }, /*#__PURE__*/React.createElement("div", {
          style: {
            width: "".concat(80 * zoom, "px"),
            textAlign: 'right'
          }
        }, "Port: ", shipping.toFixed(2), "\u20AC")), element.showTaxes && tax > 0 && /*#__PURE__*/React.createElement("div", {
          style: {
            display: 'flex',
            justifyContent: 'flex-end',
            padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px")
          }
        }, /*#__PURE__*/React.createElement("div", {
          style: {
            width: "".concat(80 * zoom, "px"),
            textAlign: 'right'
          }
        }, "TVA: ", tax.toFixed(2), "\u20AC")), element.showDiscount && discount < 0 && /*#__PURE__*/React.createElement("div", {
          style: {
            display: 'flex',
            justifyContent: 'flex-end',
            padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px")
          }
        }, /*#__PURE__*/React.createElement("div", {
          style: {
            width: "".concat(80 * zoom, "px"),
            textAlign: 'right'
          }
        }, "Remise: ", Math.abs(discount).toFixed(2), "\u20AC")), element.showTotal && /*#__PURE__*/React.createElement("div", {
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
            width: "".concat(80 * zoom, "px"),
            textAlign: 'right'
          }
        }, "TOTAL: ", total.toFixed(2), "\u20AC"))));
      case 'customer_info':
        // Rendu dynamique des informations client utilisant les propriétés de l'élément
        var customerFields = element.fields || ['name', 'email', 'phone', 'address', 'company', 'vat', 'siret'];
        var showLabels = element.showLabels !== false;
        var layout = element.layout || 'vertical';
        var alignment = element.alignment || 'left';
        var spacing = element.spacing || 3;

        // Données fictives pour l'aperçu (seront remplacées par les vraies données lors de la génération)
        var customerData = {
          name: 'Jean Dupont',
          company: 'ABC Company SARL',
          address: '123 Rue de la Paix\n75001 Paris\nFrance',
          email: 'jean.dupont@email.com',
          phone: '+33 6 12 34 56 78',
          tva: 'FR 12 345 678 901',
          siret: '123 456 789 00012',
          website: 'www.abc-company.com'
        };
        var containerStyle = {
          padding: "".concat(8 * zoom, "px"),
          fontSize: (element.fontSize || 12) * zoom,
          lineHeight: element.lineHeight || '1.4',
          color: element.color || '#1e293b',
          fontFamily: element.fontFamily || 'Inter, sans-serif',
          textAlign: alignment,
          display: layout === 'horizontal' ? 'flex' : 'block',
          flexWrap: layout === 'horizontal' ? 'wrap' : 'nowrap',
          gap: layout === 'horizontal' ? "".concat(spacing * zoom, "px") : '0'
        };
        return /*#__PURE__*/React.createElement("div", {
          style: containerStyle
        }, customerFields.map(function (field, index) {
          var fieldData = customerData[field];
          if (!fieldData) return null;
          var fieldStyle = layout === 'horizontal' ? {
            flex: '1',
            minWidth: "".concat(120 * zoom, "px")
          } : {
            marginBottom: index < customerFields.length - 1 ? "".concat(spacing * zoom, "px") : '0',
            display: 'flex',
            alignItems: 'flex-start'
          };
          return /*#__PURE__*/React.createElement("div", {
            key: field,
            style: fieldStyle
          }, showLabels && /*#__PURE__*/React.createElement("div", {
            style: {
              fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
              marginBottom: layout === 'horizontal' ? "".concat(2 * zoom, "px") : '0',
              marginRight: layout === 'horizontal' ? '0' : "".concat(8 * zoom, "px"),
              fontSize: "".concat(11 * zoom, "px"),
              opacity: 0.8,
              minWidth: layout === 'horizontal' ? 'auto' : "".concat(80 * zoom, "px"),
              flexShrink: 0
            }
          }, field === 'name' && 'Client', field === 'company' && 'Entreprise', field === 'address' && 'Adresse', field === 'email' && 'Email', field === 'phone' && 'Téléphone', field === 'tva' && 'N° TVA', field === 'siret' && 'SIRET', field === 'website' && 'Site web', ":"), /*#__PURE__*/React.createElement("div", {
            style: {
              whiteSpace: 'pre-line',
              fontSize: (element.fontSize || 12) * zoom,
              flex: layout === 'horizontal' ? '1' : 'auto'
            }
          }, fieldData));
        }));
      case 'company_info':
        // Rendu dynamique des informations entreprise utilisant les propriétés de l'élément
        var companyFields = element.fields || ['name', 'address', 'phone', 'email', 'website', 'vat', 'rcs', 'siret'];
        var showCompanyLabels = element.showLabels !== false;
        var companyLayout = element.layout || 'vertical';
        var companyAlignment = element.alignment || 'left';
        var companySpacing = element.spacing || 3;

        // Données fictives pour l'aperçu (seront remplacées par les vraies données lors de la génération)
        var companyData = {
          name: 'ABC Company SARL',
          address: '456 Avenue des Champs\n75008 Paris\nFrance',
          phone: '01 23 45 67 89',
          email: 'contact@abc-company.com',
          tva: 'FR 98 765 432 109',
          siret: '987 654 321 00098',
          rcs: 'Paris B 123 456 789',
          website: 'www.abc-company.com'
        };
        var companyContainerStyle = {
          padding: "".concat(8 * zoom, "px"),
          fontSize: (element.fontSize || 12) * zoom,
          lineHeight: element.lineHeight || '1.4',
          color: element.color || '#1e293b',
          fontFamily: element.fontFamily || 'Inter, sans-serif',
          textAlign: companyAlignment,
          display: companyLayout === 'horizontal' ? 'flex' : 'block',
          flexWrap: companyLayout === 'horizontal' ? 'wrap' : 'nowrap',
          gap: companyLayout === 'horizontal' ? "".concat(companySpacing * zoom, "px") : '0'
        };
        return /*#__PURE__*/React.createElement("div", {
          style: companyContainerStyle
        }, companyFields.map(function (field, index) {
          var fieldData = companyData[field];
          if (!fieldData) return null;
          var companyFieldStyle = companyLayout === 'horizontal' ? {
            flex: '1',
            minWidth: "".concat(120 * zoom, "px")
          } : {
            marginBottom: index < companyFields.length - 1 ? "".concat(companySpacing * zoom, "px") : '0',
            display: 'flex',
            alignItems: 'flex-start'
          };
          return /*#__PURE__*/React.createElement("div", {
            key: field,
            style: companyFieldStyle
          }, showCompanyLabels && /*#__PURE__*/React.createElement("div", {
            style: {
              fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
              marginBottom: companyLayout === 'horizontal' ? "".concat(2 * zoom, "px") : '0',
              marginRight: companyLayout === 'horizontal' ? '0' : "".concat(8 * zoom, "px"),
              fontSize: "".concat(11 * zoom, "px"),
              opacity: 0.8,
              minWidth: companyLayout === 'horizontal' ? 'auto' : "".concat(80 * zoom, "px"),
              flexShrink: 0
            }
          }, field === 'name' && 'Entreprise', field === 'address' && 'Adresse', field === 'phone' && 'Téléphone', field === 'email' && 'Email', field === 'tva' && 'N° TVA', field === 'siret' && 'SIRET', field === 'rcs' && 'RCS', field === 'website' && 'Site web', ":"), /*#__PURE__*/React.createElement("div", {
            style: {
              whiteSpace: 'pre-line',
              fontSize: (element.fontSize || 12) * zoom,
              flex: companyLayout === 'horizontal' ? '1' : 'auto'
            }
          }, fieldData));
        }));
      case 'company_logo':
        return /*#__PURE__*/React.createElement("div", {
          style: {
            width: '100%',
            height: '100%',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            padding: "".concat((element.padding || 8) * zoom, "px"),
            backgroundColor: element.backgroundColor || 'transparent',
            borderRadius: (element.borderRadius || 0) * zoom,
            border: element.borderWidth ? "".concat(element.borderWidth * zoom, "px solid ").concat(element.borderColor || '#e5e7eb') : 'none'
          }
        }, element.imageUrl || element.src ? /*#__PURE__*/React.createElement("img", {
          src: element.imageUrl || element.src,
          alt: element.alt || "Logo entreprise",
          style: {
            maxWidth: '100%',
            maxHeight: '100%',
            objectFit: element.objectFit || 'contain'
          }
        }) : /*#__PURE__*/React.createElement("div", {
          style: {
            width: '100%',
            height: '100%',
            backgroundColor: '#f0f0f0',
            border: "".concat(2 * zoom, "px dashed #ccc"),
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            color: '#666',
            fontSize: (element.fontSize || 12) * zoom
          }
        }, "\uD83C\uDFE2 Logo"));
      case 'order_number':
        return /*#__PURE__*/React.createElement("div", {
          style: {
            padding: "".concat((element.padding || 8) * zoom, "px"),
            fontSize: (element.fontSize || 14) * zoom,
            fontWeight: element.fontWeight || 'bold',
            color: element.color || '#333',
            fontFamily: element.fontFamily || 'Inter, sans-serif',
            textAlign: element.textAlign || 'left',
            backgroundColor: element.backgroundColor || 'transparent',
            borderRadius: (element.borderRadius || 0) * zoom,
            border: element.borderWidth ? "".concat(element.borderWidth * zoom, "px solid ").concat(element.borderColor || '#e5e7eb') : 'none'
          }
        }, element.showLabel !== false && /*#__PURE__*/React.createElement("div", {
          style: {
            fontSize: (element.fontSize || 14) * 0.8 * zoom,
            color: element.labelColor || '#666',
            marginBottom: "".concat(2 * zoom, "px"),
            fontWeight: 'normal'
          }
        }, element.label || 'N° de commande', ":"), /*#__PURE__*/React.createElement("div", {
          style: {
            fontSize: (element.fontSize || 14) * zoom,
            fontWeight: element.fontWeight || 'bold'
          }
        }, element.prefix || 'CMD-', element.orderNumber || '2025-00123'));
      case 'document_type':
        return /*#__PURE__*/React.createElement("div", {
          style: {
            padding: "".concat((element.padding || 8) * zoom, "px"),
            fontSize: "".concat((element.fontSize || 18) * zoom, "px"),
            fontWeight: element.fontWeight || 'bold',
            color: element.color || '#1e293b',
            fontFamily: element.fontFamily || 'Inter, sans-serif',
            textAlign: element.textAlign || 'center',
            backgroundColor: element.backgroundColor || 'transparent',
            borderRadius: (element.borderRadius || 0) * zoom
          }
        }, element.documentType === 'invoice' ? 'FACTURE' : element.documentType === 'quote' ? 'DEVIS' : element.documentType === 'receipt' ? 'REÇU' : element.documentType === 'order' ? 'COMMANDE' : element.documentType === 'credit_note' ? 'AVOIR' : 'DOCUMENT');
      case 'progress-bar':
        return /*#__PURE__*/React.createElement("div", {
          style: {
            width: '100%',
            height: (element.height || 20) * zoom,
            backgroundColor: element.backgroundColor || '#e5e7eb',
            borderRadius: (element.borderRadius || 10) * zoom,
            overflow: 'hidden',
            border: element.borderWidth ? "".concat(element.borderWidth * zoom, "px solid ").concat(element.borderColor || '#d1d5db') : 'none'
          }
        }, /*#__PURE__*/React.createElement("div", {
          style: {
            width: "".concat(Math.min(100, Math.max(0, element.progressValue || 75)), "%"),
            height: '100%',
            backgroundColor: element.progressColor || '#3b82f6',
            borderRadius: (element.borderRadius || 10) * zoom,
            transition: element.animate !== false ? 'width 0.3s ease' : 'none'
          }
        }));
      default:
        return /*#__PURE__*/React.createElement("div", {
          style: {
            width: '100%',
            height: '100%',
            backgroundColor: '#f0f0f0',
            border: "".concat(1 * zoom, "px dashed #ccc"),
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: "".concat(12 * zoom, "px"),
            color: '#666',
            padding: "".concat(4 * zoom, "px"),
            boxSizing: 'border-box'
          }
        }, element.type || 'Élément inconnu');
    }
  }, []);

  // Générer l'aperçu quand la modale s'ouvre
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(function () {
    if (isOpen && elements.length > 0) {
      if (useServerPreview) {
        // Utiliser l'aperçu unifié côté serveur
        generateServerPreview();
      } else {
        // Afficher immédiatement le contenu du canvas
        setPreviewData({
          success: true,
          elements_count: elements.length,
          width: 400,
          height: 566,
          fallback: false
        });
        // Puis générer l'aperçu côté serveur en arrière-plan
        generatePreview();
      }
    } else if (isOpen && elements.length === 0) {
      setPreviewData({
        success: true,
        elements_count: 0,
        width: 400,
        height: 566,
        fallback: false
      });
    }
  }, [isOpen, elements.length, useServerPreview]);
  var generatePreview = /*#__PURE__*/function () {
    var _ref4 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
      var _window$pdfBuilderAja, ajaxUrl, nonceFormData, nonceResponse, nonceData, freshNonce, _cleanElementsForJSON, validationResult, jsonString, cleanedElements, formData, response, data, responseText, errorMessage, _t, _t2;
      return _regenerator().w(function (_context) {
        while (1) switch (_context.p = _context.n) {
          case 0:
            // Ne pas définir loading=true car l'aperçu s'affiche déjà
            setError(null);
            _context.p = 1;
            // Vérifier que les variables AJAX sont disponibles
            ajaxUrl = ((_window$pdfBuilderAja = window.pdfBuilderAjax) === null || _window$pdfBuilderAja === void 0 ? void 0 : _window$pdfBuilderAja.ajaxurl) || ajaxurl;
            if (ajaxUrl) {
              _context.n = 2;
              break;
            }
            console.warn('Variables AJAX non disponibles pour validation côté serveur');
            return _context.a(2);
          case 2:
            // Obtenir un nonce frais
            nonceFormData = new FormData();
            nonceFormData.append('action', 'pdf_builder_get_fresh_nonce');
            _context.n = 3;
            return fetch(ajaxUrl, {
              method: 'POST',
              body: nonceFormData
            });
          case 3:
            nonceResponse = _context.v;
            if (nonceResponse.ok) {
              _context.n = 4;
              break;
            }
            console.warn('Erreur obtention nonce pour validation:', nonceResponse.status);
            return _context.a(2);
          case 4:
            _context.n = 5;
            return nonceResponse.json();
          case 5:
            nonceData = _context.v;
            if (nonceData.success) {
              _context.n = 6;
              break;
            }
            console.warn('Impossible d\'obtenir un nonce frais pour validation');
            return _context.a(2);
          case 6:
            freshNonce = nonceData.data.nonce; // Fonction pour nettoyer les éléments avant sérialisation JSON
            _cleanElementsForJSON = function _cleanElementsForJSON(elements) {
              return elements.map(function (element) {
                var cleaned = _objectSpread({}, element);

                // Supprimer les propriétés non sérialisables
                var propertiesToRemove = ['reactKey', 'tempId', 'style', '_internalId', 'ref', 'key'];
                propertiesToRemove.forEach(function (prop) {
                  delete cleaned[prop];
                });

                // Nettoyer récursivement tous les objets imbriqués
                var _cleanObject = function cleanObject(obj) {
                  if (obj === null || _typeof(obj) !== 'object') {
                    return obj;
                  }
                  if (Array.isArray(obj)) {
                    return obj.map(_cleanObject);
                  }
                  var cleanedObj = {};
                  for (var key in obj) {
                    if (obj.hasOwnProperty(key)) {
                      var value = obj[key];

                      // Ignorer les fonctions, symboles, et objets complexes
                      if (typeof value === 'function' || _typeof(value) === 'symbol' || _typeof(value) === 'object' && value !== null && !Array.isArray(value) && !(value instanceof Date) && !(value instanceof RegExp)) {
                        continue; // Skip this property
                      }

                      // Nettoyer récursivement
                      cleanedObj[key] = _cleanObject(value);
                    }
                  }
                  return cleanedObj;
                };

                // Appliquer le nettoyage récursif
                var fullyCleaned = _cleanObject(cleaned);

                // S'assurer que les propriétés numériques sont des nombres
                ['x', 'y', 'width', 'height', 'fontSize', 'borderWidth', 'borderRadius'].forEach(function (prop) {
                  if (fullyCleaned[prop] !== undefined && fullyCleaned[prop] !== null) {
                    var num = parseFloat(fullyCleaned[prop]);
                    if (!isNaN(num)) {
                      fullyCleaned[prop] = num;
                    } else {
                      delete fullyCleaned[prop]; // Supprimer si pas un nombre valide
                    }
                  }
                });

                // S'assurer que les propriétés boolean sont des booléens
                ['showLabels', 'showHeaders', 'showBorders', 'showSubtotal', 'showShipping', 'showTaxes', 'showDiscount', 'showTotal'].forEach(function (prop) {
                  if (fullyCleaned[prop] !== undefined) {
                    fullyCleaned[prop] = Boolean(fullyCleaned[prop]);
                  }
                });

                // S'assurer que les chaînes sont des chaînes
                ['id', 'type', 'content', 'text', 'color', 'backgroundColor', 'borderColor', 'fontFamily', 'fontWeight', 'fontStyle', 'textDecoration', 'textAlign', 'borderStyle'].forEach(function (prop) {
                  if (fullyCleaned[prop] !== undefined && fullyCleaned[prop] !== null) {
                    fullyCleaned[prop] = String(fullyCleaned[prop]);
                  }
                });
                return fullyCleaned;
              });
            }; // Validation côté client avant envoi
            validationResult = validateElementsBeforeSend(elements);
            if (validationResult.success) {
              _context.n = 7;
              break;
            }
            console.error('❌ Validation côté client échouée:', validationResult.error);
            setPreviewData(function (prev) {
              return _objectSpread(_objectSpread({}, prev), {}, {
                error: "Erreur de validation c\xF4t\xE9 client: ".concat(validationResult.error),
                isLoading: false
              });
            });
            return _context.a(2);
          case 7:
            jsonString = validationResult.jsonString, cleanedElements = validationResult.cleanedElements; // Préparer les données pour l'AJAX
            formData = new FormData();
            formData.append('action', 'pdf_builder_validate_preview');
            formData.append('nonce', freshNonce);
            formData.append('elements', jsonString);

            // Faire l'appel AJAX en arrière-plan
            _context.n = 8;
            return fetch(ajaxUrl, {
              method: 'POST',
              body: formData
            });
          case 8:
            response = _context.v;
            if (response.ok) {
              _context.n = 9;
              break;
            }
            console.warn('Erreur HTTP validation aperçu:', response.status);
            return _context.a(2);
          case 9:
            _context.p = 9;
            _context.n = 10;
            return response.json();
          case 10:
            data = _context.v;
            _context.n = 13;
            break;
          case 11:
            _context.p = 11;
            _t = _context.v;
            console.error('❌ Erreur parsing JSON réponse serveur:', _t);
            _context.n = 12;
            return response.text();
          case 12:
            responseText = _context.v;
            console.error('Contenu brut de la réponse:', responseText.substring(0, 500));
            // Garder l'aperçu local mais marquer l'erreur
            setPreviewData(function (prev) {
              return _objectSpread(_objectSpread({}, prev), {}, {
                server_error: 'Réponse serveur invalide (pas du JSON)'
              });
            });
            return _context.a(2);
          case 13:
            if (data.success) {
              // Mettre à jour previewData avec les données du serveur si nécessaire
              setPreviewData(function (prev) {
                return _objectSpread(_objectSpread(_objectSpread({}, prev), data.data), {}, {
                  server_validated: true
                });
              });
            } else {
              console.warn('⚠️ Validation aperçu côté serveur échouée:', data.data);
              // Garder l'aperçu local mais marquer qu'il y a un problème serveur
              // S'assurer que server_error est toujours une chaîne
              errorMessage = 'Erreur validation serveur';
              if (typeof data.data === 'string') {
                errorMessage = data.data;
              } else if (data.data && _typeof(data.data) === 'object' && data.data.message) {
                errorMessage = data.data.message;
              } else if (data.data && _typeof(data.data) === 'object') {
                errorMessage = JSON.stringify(data.data);
              }
              setPreviewData(function (prev) {
                return _objectSpread(_objectSpread({}, prev), {}, {
                  server_error: errorMessage
                });
              });
            }
            _context.n = 15;
            break;
          case 14:
            _context.p = 14;
            _t2 = _context.v;
            console.warn('Erreur validation aperçu côté serveur:', _t2);
            // Ne pas afficher d'erreur car l'aperçu local fonctionne
            setPreviewData(function (prev) {
              return _objectSpread(_objectSpread({}, prev), {}, {
                server_error: _t2.message || 'Erreur inconnue côté serveur'
              });
            });
          case 15:
            return _context.a(2);
        }
      }, _callee, null, [[9, 11], [1, 14]]);
    }));
    return function generatePreview() {
      return _ref4.apply(this, arguments);
    };
  }();
  var generateServerPreview = /*#__PURE__*/function () {
    var _ref5 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2() {
      var fallbackTimeout, _window$pdfBuilderAja2, _window$pdfBuilderAja3, validationResult, jsonString, ajaxUrl, nonceFormData, nonceResponse, nonceData, freshNonce, formData, response, data, _t3;
      return _regenerator().w(function (_context2) {
        while (1) switch (_context2.p = _context2.n) {
          case 0:
            setLoading(true);
            setError(null);
            setPreviewData(null);

            // Timeout de fallback - si l'aperçu côté serveur prend trop de temps, afficher l'aperçu côté client
            fallbackTimeout = setTimeout(function () {
              setPreviewData({
                success: true,
                elements_count: elements.length,
                width: canvasWidth,
                height: canvasHeight,
                fallback: true,
                server_timeout: true
              });
              setLoading(false);
            }, 10000); // 10 secondes timeout
            _context2.p = 1;
            // Validation côté client avant envoi
            validationResult = validateElementsBeforeSend(elements);
            if (validationResult.success) {
              _context2.n = 2;
              break;
            }
            console.error('❌ Validation côté client échouée:', validationResult.error);
            setPreviewData(function (prev) {
              return _objectSpread(_objectSpread({}, prev), {}, {
                error: "Erreur de validation c\xF4t\xE9 client: ".concat(validationResult.error),
                isLoading: false
              });
            });
            return _context2.a(2);
          case 2:
            jsonString = validationResult.jsonString; // Vérifier que les variables AJAX sont disponibles
            ajaxUrl = ((_window$pdfBuilderAja2 = window.pdfBuilderAjax) === null || _window$pdfBuilderAja2 === void 0 ? void 0 : _window$pdfBuilderAja2.ajaxurl) || ajaxurl;
            if (ajaxUrl) {
              _context2.n = 3;
              break;
            }
            alert('Erreur: Variables AJAX non disponibles. Rechargez la page.');
            return _context2.a(2);
          case 3:
            // Obtenir un nonce frais pour l'aperçu
            nonceFormData = new FormData();
            nonceFormData.append('action', 'pdf_builder_get_fresh_nonce');
            _context2.n = 4;
            return fetch(ajaxUrl, {
              method: 'POST',
              body: nonceFormData
            });
          case 4:
            nonceResponse = _context2.v;
            if (nonceResponse.ok) {
              _context2.n = 5;
              break;
            }
            throw new Error("Erreur HTTP nonce: ".concat(nonceResponse.status));
          case 5:
            _context2.n = 6;
            return nonceResponse.json();
          case 6:
            nonceData = _context2.v;
            if (nonceData.success) {
              _context2.n = 7;
              break;
            }
            throw new Error('Impossible d\'obtenir un nonce frais');
          case 7:
            freshNonce = nonceData.data.nonce; // Préparer les données pour l'AJAX unifié
            formData = new FormData();
            formData.append('action', 'pdf_builder_unified_preview');
            formData.append('nonce', freshNonce);
            formData.append('elements', jsonString);
            _context2.n = 8;
            return fetch(ajaxurl || ((_window$pdfBuilderAja3 = window.pdfBuilderAjax) === null || _window$pdfBuilderAja3 === void 0 ? void 0 : _window$pdfBuilderAja3.ajaxurl) || '/wp-admin/admin-ajax.php', {
              method: 'POST',
              body: formData
            });
          case 8:
            response = _context2.v;
            if (response.ok) {
              _context2.n = 9;
              break;
            }
            throw new Error("Erreur HTTP: ".concat(response.status));
          case 9:
            _context2.n = 10;
            return response.json();
          case 10:
            data = _context2.v;
            if (!(data.success && data.data && data.data.url)) {
              _context2.n = 11;
              break;
            }
            // Nettoyer le timeout de fallback
            clearTimeout(fallbackTimeout);

            // Mettre à jour l'état pour afficher le PDF dans la modale
            setPreviewData({
              url: data.data.url,
              server_validated: true,
              elements_count: elements.length,
              width: canvasWidth,
              height: canvasHeight,
              zoom: zoom
            });
            setLoading(false);
            setError(null);

            // Ne pas ouvrir de nouvel onglet - le PDF s'affichera dans la modale
            return _context2.a(2);
          case 11:
            throw new Error(data.data || 'Erreur génération aperçu côté serveur');
          case 12:
            _context2.n = 14;
            break;
          case 13:
            _context2.p = 13;
            _t3 = _context2.v;
            console.error('❌ Erreur génération aperçu côté serveur:', _t3);
            // Nettoyer le timeout de fallback
            clearTimeout(fallbackTimeout);
            setError("Erreur aper\xE7u c\xF4t\xE9 serveur: ".concat(_t3.message));
            setLoading(false);
          case 14:
            return _context2.a(2);
        }
      }, _callee2, null, [[1, 13]]);
    }));
    return function generateServerPreview() {
      return _ref5.apply(this, arguments);
    };
  }();
  var handlePrint = /*#__PURE__*/function () {
    var _ref6 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee3() {
      var printButton, _window$pdfBuilderAja4, ajaxUrl, nonceFormData, nonceResponse, nonceData, freshNonce, formData, originalText, response, data, errorMessage, pdfBase64, pdfBlob, pdfUrl, previewWindow, link, _t4;
      return _regenerator().w(function (_context3) {
        while (1) switch (_context3.p = _context3.n) {
          case 0:
            printButton = null;
            _context3.p = 1;
            // Vérifier que les variables AJAX sont disponibles
            ajaxUrl = ((_window$pdfBuilderAja4 = window.pdfBuilderAjax) === null || _window$pdfBuilderAja4 === void 0 ? void 0 : _window$pdfBuilderAja4.ajaxurl) || ajaxurl;
            if (ajaxUrl) {
              _context3.n = 2;
              break;
            }
            alert('Erreur: Variables AJAX non disponibles. Rechargez la page.');
            return _context3.a(2);
          case 2:
            // Obtenir un nonce frais
            nonceFormData = new FormData();
            nonceFormData.append('action', 'pdf_builder_get_fresh_nonce');
            _context3.n = 3;
            return fetch(ajaxUrl, {
              method: 'POST',
              body: nonceFormData
            });
          case 3:
            nonceResponse = _context3.v;
            if (nonceResponse.ok) {
              _context3.n = 4;
              break;
            }
            throw new Error("Erreur HTTP nonce: ".concat(nonceResponse.status));
          case 4:
            _context3.n = 5;
            return nonceResponse.json();
          case 5:
            nonceData = _context3.v;
            if (nonceData.success) {
              _context3.n = 6;
              break;
            }
            throw new Error('Impossible d\'obtenir un nonce frais');
          case 6:
            freshNonce = nonceData.data.nonce; // Préparer les données pour l'AJAX
            formData = new FormData();
            formData.append('action', 'pdf_builder_generate_pdf');
            formData.append('nonce', freshNonce);
            formData.append('elements', JSON.stringify(elements));

            // Afficher un indicateur de chargement
            printButton = document.querySelector('.btn-primary');
            if (printButton) {
              originalText = printButton.textContent;
              printButton.textContent = '⏳ Génération PDF...';
              printButton.disabled = true;
            }

            // Envoyer la requête AJAX
            _context3.n = 7;
            return fetch(ajaxUrl, {
              method: 'POST',
              body: formData
            });
          case 7:
            response = _context3.v;
            if (response.ok) {
              _context3.n = 8;
              break;
            }
            throw new Error('Erreur réseau: ' + response.status);
          case 8:
            _context3.n = 9;
            return response.json()["catch"](function (jsonError) {
              console.error('Erreur parsing JSON:', jsonError);
              throw new Error('Réponse invalide du serveur (pas du JSON)');
            });
          case 9:
            data = _context3.v;
            if (data.success) {
              _context3.n = 10;
              break;
            }
            errorMessage = 'Erreur inconnue lors de la génération du PDF';
            if (typeof data.data === 'string') {
              errorMessage = data.data;
            } else if (_typeof(data.data) === 'object' && data.data !== null) {
              errorMessage = data.data.message || JSON.stringify(data.data);
            }
            throw new Error(errorMessage);
          case 10:
            if (!(!data.data || !data.data.pdf)) {
              _context3.n = 11;
              break;
            }
            throw new Error('Données PDF manquantes dans la réponse');
          case 11:
            // Convertir le PDF base64 en blob
            pdfBase64 = data.data.pdf;
            pdfBlob = new Blob([Uint8Array.from(atob(pdfBase64), function (c) {
              return c.charCodeAt(0);
            })], {
              type: 'application/pdf'
            });
            if (!(pdfBlob.size === 0)) {
              _context3.n = 12;
              break;
            }
            throw new Error('Le PDF généré est vide');
          case 12:
            // Créer un URL pour le blob PDF
            pdfUrl = URL.createObjectURL(pdfBlob); // Ouvrir le PDF dans une modale si la prop est fournie, sinon dans une nouvelle fenêtre
            if (onOpenPDFModal) {
              onOpenPDFModal(pdfUrl);
            } else {
              // Fallback vers l'ancienne méthode
              previewWindow = window.open(pdfUrl, '_blank');
              if (!previewWindow) {
                // Fallback si le popup est bloqué
                link = document.createElement('a');
                link.href = pdfUrl;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                document.body.appendChild(link);
                link.click();
                // Vérifier que l'élément existe encore avant de le supprimer
                if (link.parentNode === document.body) {
                  document.body.removeChild(link);
                }
              }
            }

            // Libérer l'URL du blob après un délai (seulement si pas en modale)
            if (!onOpenPDFModal) {
              setTimeout(function () {
                URL.revokeObjectURL(pdfUrl);
              }, 1000);
            }
            _context3.n = 14;
            break;
          case 13:
            _context3.p = 13;
            _t4 = _context3.v;
            console.error('Erreur génération PDF:', _t4);
            alert('Erreur lors de la génération du PDF: ' + _t4.message);
          case 14:
            _context3.p = 14;
            // Restaurer le bouton
            if (printButton) {
              printButton.textContent = '👁️ Imprimer PDF';
              printButton.disabled = false;
            }
            return _context3.f(14);
          case 15:
            return _context3.a(2);
        }
      }, _callee3, null, [[1, 13, 14, 15]]);
    }));
    return function handlePrint() {
      return _ref6.apply(this, arguments);
    };
  }();
  if (!isOpen) return null;
  return /*#__PURE__*/React.createElement("div", {
    className: "preview-modal-overlay",
    onClick: onClose
  }, /*#__PURE__*/React.createElement("div", {
    className: "preview-modal-content",
    onClick: function onClick(e) {
      return e.stopPropagation();
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "preview-modal-header"
  }, /*#__PURE__*/React.createElement("h3", null, "\uD83C\uDFA8 Aper\xE7u Canvas - PDF Builder Pro v2.0"), /*#__PURE__*/React.createElement("button", {
    className: "preview-modal-close",
    onClick: onClose
  }, "\xD7")), /*#__PURE__*/React.createElement("div", {
    className: "preview-modal-body"
  }, loading && /*#__PURE__*/React.createElement("div", {
    className: "preview-loading"
  }, /*#__PURE__*/React.createElement("div", {
    className: "preview-spinner"
  }), /*#__PURE__*/React.createElement("p", null, "G\xE9n\xE9ration de l'aper\xE7u...")), error && /*#__PURE__*/React.createElement("div", {
    className: "preview-error"
  }, /*#__PURE__*/React.createElement("h4", null, "\u274C Erreur d'aper\xE7u"), /*#__PURE__*/React.createElement("p", null, error), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("small", null, "Le PDF pourra quand m\xEAme \xEAtre g\xE9n\xE9r\xE9 normalement."))), previewData && /*#__PURE__*/React.createElement("div", {
    className: "preview-content"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      textAlign: 'center',
      marginBottom: '20px',
      padding: '10px',
      background: previewData.server_validated ? '#e8f5e8' : '#fff3cd',
      borderRadius: '4px',
      border: "1px solid ".concat(previewData.server_validated ? '#c3e6c3' : '#ffeaa7')
    }
  }, /*#__PURE__*/React.createElement("strong", null, previewData.server_validated ? '✅' : '⚡', " Aper\xE7u g\xE9n\xE9r\xE9"), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("small", null, previewData.elements_count, " \xE9l\xE9ment", previewData.elements_count !== 1 ? 's' : '', " \u2022 ", previewData.width, "\xD7", previewData.height, "px", previewData.server_validated && ' • Serveur validé', previewData.server_error && ' • ⚠️ Problème serveur')), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      justifyContent: 'center',
      alignItems: 'flex-start',
      minHeight: '400px',
      backgroundColor: '#f8f9fa',
      borderRadius: '8px',
      padding: '20px'
    }
  }, previewData.url ?
  /*#__PURE__*/
  // Aperçu côté serveur - afficher le PDF dans un iframe
  React.createElement("iframe", {
    src: previewData.url,
    style: {
      width: '100%',
      height: '600px',
      border: '1px solid #dee2e6',
      borderRadius: '4px',
      backgroundColor: 'white'
    },
    title: "Aper\xE7u PDF c\xF4t\xE9 serveur"
  }) :
  // Aperçu côté client - rendre le HTML
  renderCanvasContent(elements)), previewData.server_error && /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: '20px',
      padding: '15px',
      backgroundColor: '#ffeaa7',
      borderRadius: '6px',
      border: '1px solid #d4a574'
    }
  }, /*#__PURE__*/React.createElement("h5", {
    style: {
      margin: '0 0 10px 0',
      color: '#856404'
    }
  }, "\u26A0\uFE0F Note"), /*#__PURE__*/React.createElement("p", {
    style: {
      margin: '0',
      fontSize: '14px',
      color: '#333'
    }
  }, "L'aper\xE7u s'affiche correctement, mais il y a un probl\xE8me de validation c\xF4t\xE9 serveur: ", previewData.server_error)), /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: '20px',
      padding: '15px',
      backgroundColor: '#e8f4fd',
      borderRadius: '6px',
      border: '1px solid #b3d9ff'
    }
  }, /*#__PURE__*/React.createElement("h5", {
    style: {
      margin: '0 0 10px 0',
      color: '#0066cc'
    }
  }, "\u2139\uFE0F Informations du Canvas"), /*#__PURE__*/React.createElement("p", {
    style: {
      margin: '0',
      fontSize: '14px',
      color: '#333'
    }
  }, /*#__PURE__*/React.createElement("strong", null, "Dimensions:"), " ", canvasWidth, " \xD7 ", canvasHeight, " pixels", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("strong", null, "\xC9l\xE9ments:"), " ", elements.length, /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("strong", null, "Zoom:"), " ", Math.round(zoom * 100), "%", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("strong", null, "Status:"), " ", previewData.server_validated ? 'Validé côté serveur' : 'Aperçu local'))), !loading && !error && !previewData && /*#__PURE__*/React.createElement("div", {
    className: "preview-loading"
  }, /*#__PURE__*/React.createElement("p", null, "Pr\xE9paration de l'aper\xE7u..."))), /*#__PURE__*/React.createElement("div", {
    className: "preview-modal-footer"
  }, /*#__PURE__*/React.createElement("button", {
    className: "btn btn-secondary",
    onClick: onClose
  }, "\u274C Fermer"), /*#__PURE__*/React.createElement("button", {
    className: "btn btn-primary",
    onClick: handlePrint
  }, "\uD83D\uDC41\uFE0F Imprimer PDF"))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (PreviewModal);

/***/ })

}]);