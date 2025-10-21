"use strict";
(self["webpackChunkPDFBuilderPro"] = self["webpackChunkPDFBuilderPro"] || []).push([[657],{

/***/ 657:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, {
  CanvasMode: () => (/* binding */ CanvasMode)
});

;// ./resources/js/components/preview-system/data/SampleDataProvider.jsx
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Fournisseur de données d'exemple pour le mode Canvas
 * Génère des données fictives réalistes pour l'aperçu
 */
var SampleDataProvider = /*#__PURE__*/function () {
  function SampleDataProvider() {
    _classCallCheck(this, SampleDataProvider);
    this.sampleData = {
      // Données de produits d'exemple étendues
      products: [{
        name: 'Ordinateur Portable Pro 15"',
        sku: 'LAPTOP-PRO-15',
        quantity: 1,
        price: '999,99 €',
        regular_price: '1 199,99 €',
        sale_price: '999,99 €',
        total: '999,99 €',
        subtotal: '999,99 €',
        tax: '199,99 €',
        discount: '200,00 €',
        image: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjNGY0NmU1Ii8+Cjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEwIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkxBUFQ8L3RleHQ+Cjwvc3ZnPg==',
        description: 'Ordinateur portable professionnel avec écran 15" 4K, processeur Intel i7, 16GB RAM, SSD 512GB.',
        short_description: 'Ordinateur portable professionnel 15" 4K',
        categories: ['Informatique', 'Ordinateurs Portables'],
        weight: '2.1 kg',
        dimensions: '35.5 x 24.5 x 1.8 cm',
        attributes: {
          'Processeur': 'Intel Core i7-11800H',
          'RAM': '16GB DDR4',
          'Stockage': 'SSD 512GB NVMe',
          'Écran': '15.6" 4K UHD'
        },
        stock_quantity: 15,
        stock_status: 'en_stock',
        product_type: 'simple',
        is_on_sale: true,
        is_virtual: false,
        is_downloadable: false,
        meta_data: {
          '_custom_field': 'Valeur personnalisée'
        }
      }, {
        name: 'Souris Gaming RGB Optique',
        sku: 'MOUSE-RGB-001',
        quantity: 2,
        price: '49,99 €',
        regular_price: '59,99 €',
        sale_price: '49,99 €',
        total: '99,98 €',
        subtotal: '99,98 €',
        tax: '19,99 €',
        discount: '20,00 €',
        image: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjMDU5NjY5Ii8+Cjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEwIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk1PVVNFPPC90ZXh0Pgo8L3N2Zz4=',
        description: 'Souris gaming optique RGB avec capteur PixArt 3335, 16 000 DPI, switches Omron et éclairage RGB personnalisable.',
        short_description: 'Souris gaming RGB 16K DPI',
        categories: ['Informatique', 'Périphériques', 'Gaming'],
        weight: '0.085 kg',
        dimensions: '12.5 x 6.8 x 3.8 cm',
        attributes: {
          'DPI': '16 000',
          'Switches': 'Omron',
          'Éclairage': 'RGB',
          'Capteur': 'PixArt 3335'
        },
        stock_quantity: 45,
        stock_status: 'en_stock',
        product_type: 'simple',
        is_on_sale: true,
        is_virtual: false,
        is_downloadable: false,
        meta_data: {}
      }, {
        name: 'Clavier Mécanique Gaming',
        sku: 'KEYBOARD-MECH-001',
        quantity: 1,
        price: '129,99 €',
        regular_price: '149,99 €',
        sale_price: '129,99 €',
        total: '129,99 €',
        subtotal: '129,99 €',
        tax: '25,99 €',
        discount: '20,00 €',
        image: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjZGMyNjI2Ii8+Cjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEwIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPktFWUI8L3RleHQ+Cjwvc3ZnPg==',
        description: 'Clavier mécanique gaming avec switches Cherry MX Red, éclairage RGB par touche, repose-poignets ergonomique.',
        short_description: 'Clavier mécanique RGB Cherry MX',
        categories: ['Informatique', 'Périphériques', 'Gaming'],
        weight: '0.95 kg',
        dimensions: '43.5 x 13.5 x 3.8 cm',
        attributes: {
          'Switches': 'Cherry MX Red',
          'Éclairage': 'RGB par touche',
          'Disposition': 'AZERTY',
          'Connexion': 'USB-C'
        },
        stock_quantity: 8,
        stock_status: 'en_stock',
        product_type: 'simple',
        is_on_sale: true,
        is_virtual: false,
        is_downloadable: false,
        meta_data: {
          '_warranty': '2 ans'
        }
      }],
      // Données client d'exemple
      customer: {
        name: 'Marie Dubois',
        email: 'marie.dubois@email.com',
        phone: '+33 6 12 34 56 78',
        address: '15 Avenue des Champs-Élysées\n75008 Paris\nFrance',
        company: 'TechCorp SARL',
        vat: 'FR12345678901',
        siret: '12345678901234'
      },
      // Données entreprise d'exemple
      company: {
        name: 'Ma Société SARL',
        address: '123 Rue de la Paix\n75001 Paris\nFrance',
        phone: '+33 1 42 86 75 30',
        email: 'contact@masociete.com',
        website: 'www.masociete.com',
        vat: 'FR98765432109',
        rcs: 'Paris B 123 456 789',
        siret: '98765432109876'
      },
      // Données commande d'exemple
      order: {
        number: 'CMD-2025-001',
        date: '19/10/2025',
        total: '1 229,96 €',
        subtotal: '1 129,97 €',
        tax: '99,99 €',
        shipping: '15,00 €',
        discount: '15,00 €',
        status: 'Traitement en cours',
        paymentMethod: 'Carte bancaire',
        shippingMethod: 'Colissimo 48h',
        // Styles de tableau disponibles
        tableStyles: {
          "default": {
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
          },
          classic: {
            header_bg: [30, 41, 59],
            // #1e293b
            header_border: [51, 65, 85],
            // #334155
            row_border: [51, 65, 85],
            // #334155
            alt_row_bg: [255, 255, 255],
            // #ffffff
            headerTextColor: '#ffffff',
            rowTextColor: '#1e293b',
            border_width: 1.5,
            headerFontWeight: '700',
            headerFontSize: '11px',
            rowFontSize: '10px'
          },
          blue: {
            header_bg: [59, 130, 246],
            // #3b82f6
            header_border: [37, 99, 235],
            // #2563eb
            row_border: [226, 232, 240],
            // #e2e8f0
            alt_row_bg: [248, 249, 250],
            // #f8fafc
            headerTextColor: '#ffffff',
            rowTextColor: '#1e293b',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '11px',
            rowFontSize: '10px'
          },
          minimal: {
            header_bg: [255, 255, 255],
            // #ffffff
            header_border: [229, 231, 235],
            // #e5e7eb
            row_border: [229, 231, 235],
            // #e5e7eb
            alt_row_bg: [255, 255, 255],
            // #ffffff
            headerTextColor: '#374151',
            rowTextColor: '#374151',
            border_width: 1,
            headerFontWeight: '600',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          light: {
            header_bg: [249, 250, 251],
            // #f9fafb
            header_border: [209, 213, 219],
            // #d1d5db
            row_border: [229, 231, 235],
            // #e5e7eb
            alt_row_bg: [255, 255, 255],
            // #ffffff
            headerTextColor: '#111827',
            rowTextColor: '#374151',
            border_width: 1,
            headerFontWeight: '500',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          emerald_forest: {
            header_bg: [16, 185, 129],
            // #10b981
            header_border: [5, 150, 105],
            // #059669
            row_border: [209, 213, 219],
            // #d1d5db
            alt_row_bg: [236, 253, 245],
            // #ecfdf5
            headerTextColor: '#ffffff',
            rowTextColor: '#065f46',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '11px',
            rowFontSize: '10px'
          },
          striped: {
            header_bg: [75, 85, 99],
            // #4b5563
            header_border: [107, 114, 128],
            // #6b7280
            row_border: [229, 231, 235],
            // #e5e7eb
            alt_row_bg: [249, 250, 251],
            // #f9fafb
            headerTextColor: '#ffffff',
            rowTextColor: '#374151',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          bordered: {
            header_bg: [31, 41, 55],
            // #1f2937
            header_border: [55, 65, 81],
            // #374151
            row_border: [55, 65, 81],
            // #374151
            alt_row_bg: [255, 255, 255],
            // #ffffff
            headerTextColor: '#ffffff',
            rowTextColor: '#1f2937',
            border_width: 2,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          modern: {
            header_bg: [17, 24, 39],
            // #111827
            header_border: [75, 85, 99],
            // #4b5563
            row_border: [209, 213, 219],
            // #d1d5db
            alt_row_bg: [243, 244, 246],
            // #f3f4f6
            headerTextColor: '#ffffff',
            rowTextColor: '#374151',
            border_width: 1,
            headerFontWeight: '600',
            headerFontSize: '13px',
            rowFontSize: '12px'
          },
          blue_ocean: {
            header_bg: [12, 74, 110],
            // #0c4a6e
            header_border: [2, 132, 199],
            // #0284c7
            row_border: [186, 230, 253],
            // #bae6fd
            alt_row_bg: [240, 249, 255],
            // #f0f9ff
            headerTextColor: '#ffffff',
            rowTextColor: '#0c4a6e',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          sunset_orange: {
            header_bg: [154, 52, 18],
            // #9a3412
            header_border: [234, 88, 12],
            // #ea580c
            row_border: [253, 186, 116],
            // #fdba74
            alt_row_bg: [255, 247, 237],
            // #fff7ed
            headerTextColor: '#ffffff',
            rowTextColor: '#9a3412',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          royal_purple: {
            header_bg: [88, 28, 135],
            // #581c87
            header_border: [147, 51, 234],
            // #9333ea
            row_border: [221, 214, 254],
            // #ddd6fe
            alt_row_bg: [250, 245, 255],
            // #faf5ff
            headerTextColor: '#ffffff',
            rowTextColor: '#581c87',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          rose_pink: {
            header_bg: [190, 18, 60],
            // #be123c
            header_border: [236, 72, 153],
            // #ec4899
            row_border: [253, 164, 175],
            // #fda4af
            alt_row_bg: [255, 241, 242],
            // #fff1f2
            headerTextColor: '#ffffff',
            rowTextColor: '#be123c',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          teal_aqua: {
            header_bg: [5, 150, 105],
            // #059669
            header_border: [20, 184, 166],
            // #14b8a6
            row_border: [153, 246, 228],
            // #99f6e4
            alt_row_bg: [236, 253, 245],
            // #ecfdf5
            headerTextColor: '#ffffff',
            rowTextColor: '#065f46',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          crimson_red: {
            header_bg: [153, 27, 27],
            // #991b1b
            header_border: [239, 68, 68],
            // #ef4444
            row_border: [252, 165, 165],
            // #fca5a5
            alt_row_bg: [254, 242, 242],
            // #fef2f2
            headerTextColor: '#ffffff',
            rowTextColor: '#991b1b',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          amber_gold: {
            header_bg: [161, 98, 7],
            // #a16207
            header_border: [245, 158, 11],
            // #f59e0b
            row_border: [253, 230, 138],
            // #fde68a
            alt_row_bg: [254, 252, 232],
            // #fefce8
            headerTextColor: '#ffffff',
            rowTextColor: '#92400e',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          indigo_night: {
            header_bg: [49, 46, 129],
            // #312e81
            header_border: [99, 102, 241],
            // #6366f1
            row_border: [196, 181, 253],
            // #c4b5fd
            alt_row_bg: [245, 243, 255],
            // #f5f3ff
            headerTextColor: '#ffffff',
            rowTextColor: '#312e81',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          slate_gray: {
            header_bg: [51, 65, 85],
            // #334155
            header_border: [100, 116, 139],
            // #64748b
            row_border: [203, 213, 225],
            // #cbd5e1
            alt_row_bg: [248, 250, 252],
            // #f8fafc
            headerTextColor: '#ffffff',
            rowTextColor: '#334155',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          coral_sunset: {
            header_bg: [194, 65, 12],
            // #c2410c
            header_border: [251, 146, 60],
            // #fb923c
            row_border: [253, 186, 116],
            // #fdba74
            alt_row_bg: [255, 247, 237],
            // #fff7ed
            headerTextColor: '#ffffff',
            rowTextColor: '#9a3412',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          mint_green: {
            header_bg: [34, 197, 94],
            // #22c55e
            header_border: [74, 222, 128],
            // #4ade80
            row_border: [187, 247, 208],
            // #bbf7d0
            alt_row_bg: [240, 253, 244],
            // #f0fdf4
            headerTextColor: '#ffffff',
            rowTextColor: '#166534',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          violet_dream: {
            header_bg: [109, 40, 217],
            // #6d28d9
            header_border: [168, 85, 247],
            // #a855f7
            row_border: [233, 213, 255],
            // #e9d5ff
            alt_row_bg: [251, 245, 255],
            // #fbf5ff
            headerTextColor: '#ffffff',
            rowTextColor: '#6b21a8',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          sky_blue: {
            header_bg: [3, 105, 161],
            // #0369a1
            header_border: [14, 165, 233],
            // #0ea5e9
            row_border: [125, 211, 252],
            // #7dd3fc
            alt_row_bg: [240, 249, 255],
            // #f0f9ff
            headerTextColor: '#ffffff',
            rowTextColor: '#0c4a6e',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          forest_green: {
            header_bg: [21, 128, 61],
            // #15803d
            header_border: [34, 197, 94],
            // #22c55e
            row_border: [134, 239, 172],
            // #86efac
            alt_row_bg: [236, 253, 245],
            // #ecfdf5
            headerTextColor: '#ffffff',
            rowTextColor: '#14532d',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          ruby_red: {
            header_bg: [185, 28, 28],
            // #b91c1c
            header_border: [239, 68, 68],
            // #ef4444
            row_border: [252, 165, 165],
            // #fca5a5
            alt_row_bg: [254, 226, 226],
            // #fee2e2
            headerTextColor: '#ffffff',
            rowTextColor: '#991b1b',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          }
        }
      }
    };
  }

  /**
   * Génère des données d'exemple pour un type d'élément spécifique
   * @param {string} elementType - Type de l'élément
   * @param {Object} properties - Propriétés de l'élément
   * @returns {Promise<any>} Données d'exemple
   */
  return _createClass(SampleDataProvider, [{
    key: "getElementData",
    value: (function () {
      var _getElementData = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(elementType, properties) {
        var _t;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.n) {
            case 0:
              _t = elementType;
              _context.n = _t === 'product_table' ? 1 : _t === 'customer_info' ? 2 : _t === 'company_logo' ? 3 : _t === 'company_info' ? 4 : _t === 'order_number' ? 5 : _t === 'dynamic-text' ? 6 : _t === 'conditional-text' ? 6 : _t === 'mentions' ? 7 : _t === 'rectangle' ? 8 : _t === 'line' ? 8 : _t === 'shape-rectangle' ? 8 : _t === 'shape-circle' ? 8 : _t === 'shape-line' ? 8 : _t === 'shape-arrow' ? 8 : _t === 'shape-triangle' ? 8 : _t === 'shape-star' ? 8 : _t === 'divider' ? 8 : _t === 'barcode' ? 9 : _t === 'qrcode' ? 9 : _t === 'progress-bar' ? 10 : _t === 'watermark' ? 11 : 12;
              break;
            case 1:
              return _context.a(2, this.generateProductTableData(properties));
            case 2:
              return _context.a(2, this.generateCustomerInfoData(properties));
            case 3:
              return _context.a(2, this.generateCompanyLogoData(properties));
            case 4:
              return _context.a(2, this.generateCompanyInfoData(properties));
            case 5:
              return _context.a(2, this.generateOrderNumberData(properties));
            case 6:
              return _context.a(2, this.generateDynamicTextData(properties));
            case 7:
              return _context.a(2, this.generateMentionsData(properties));
            case 8:
              return _context.a(2, this.generateRectangleData(properties));
            case 9:
              return _context.a(2, this.generateBarcodeData(properties));
            case 10:
              return _context.a(2, this.generateProgressBarData(properties));
            case 11:
              return _context.a(2, this.generateWatermarkData(properties));
            case 12:
              return _context.a(2, this.generateDefaultData(elementType, properties));
            case 13:
              return _context.a(2);
          }
        }, _callee, this);
      }));
      function getElementData(_x, _x2) {
        return _getElementData.apply(this, arguments);
      }
      return getElementData;
    }()
    /**
     * Génère des données pour un tableau de produits
     */
    )
  }, {
    key: "generateProductTableData",
    value: function generateProductTableData(properties) {
      var _properties$columns = properties.columns,
        columns = _properties$columns === void 0 ? {} : _properties$columns,
        _properties$showSubto = properties.showSubtotal,
        showSubtotal = _properties$showSubto === void 0 ? false : _properties$showSubto,
        _properties$showShipp = properties.showShipping,
        showShipping = _properties$showShipp === void 0 ? true : _properties$showShipp,
        _properties$showTaxes = properties.showTaxes,
        showTaxes = _properties$showTaxes === void 0 ? true : _properties$showTaxes,
        _properties$showDisco = properties.showDiscount,
        showDiscount = _properties$showDisco === void 0 ? true : _properties$showDisco,
        _properties$showTotal = properties.showTotal,
        showTotal = _properties$showTotal === void 0 ? true : _properties$showTotal,
        _properties$tableStyl = properties.tableStyle,
        tableStyle = _properties$tableStyl === void 0 ? 'default' : _properties$tableStyl;
      var tableData = {
        headers: [],
        rows: [],
        totals: {},
        style: tableStyle
      };

      // Configuration par défaut des colonnes si non spécifiée
      var defaultColumns = {
        image: false,
        name: true,
        sku: false,
        description: false,
        short_description: false,
        categories: false,
        quantity: true,
        price: true,
        regular_price: false,
        sale_price: false,
        discount: false,
        tax: false,
        weight: false,
        dimensions: false,
        attributes: false,
        stock_quantity: false,
        stock_status: false,
        total: true
      };

      // Fusionner avec les colonnes spécifiées
      var activeColumns = _objectSpread(_objectSpread({}, defaultColumns), columns);

      // Déterminer les colonnes à afficher
      if (activeColumns.image !== false) tableData.headers.push('Image');
      if (activeColumns.name !== false) tableData.headers.push('Produit');
      if (activeColumns.sku !== false) tableData.headers.push('SKU');
      if (activeColumns.description !== false) tableData.headers.push('Description');
      if (activeColumns.short_description !== false) tableData.headers.push('Description courte');
      if (activeColumns.categories !== false) tableData.headers.push('Catégories');
      if (activeColumns.quantity !== false) tableData.headers.push('Qté');
      if (activeColumns.price !== false) tableData.headers.push('Prix');
      if (activeColumns.regular_price !== false) tableData.headers.push('Prix régulier');
      if (activeColumns.sale_price !== false) tableData.headers.push('Prix soldé');
      if (activeColumns.discount !== false) tableData.headers.push('Remise');
      if (activeColumns.tax !== false) tableData.headers.push('TVA');
      if (activeColumns.weight !== false) tableData.headers.push('Poids');
      if (activeColumns.dimensions !== false) tableData.headers.push('Dimensions');
      if (activeColumns.attributes !== false) tableData.headers.push('Attributs');
      if (activeColumns.stock_quantity !== false) tableData.headers.push('Stock');
      if (activeColumns.stock_status !== false) tableData.headers.push('Statut stock');
      if (activeColumns.total !== false) tableData.headers.push('Total');

      // Générer les lignes de produits
      tableData.rows = this.sampleData.products.map(function (product) {
        var row = [];
        if (activeColumns.image !== false) row.push(product.image);
        if (activeColumns.name !== false) row.push(product.name);
        if (activeColumns.sku !== false) row.push(product.sku || '-');
        if (activeColumns.description !== false) row.push(product.description || '-');
        if (activeColumns.short_description !== false) row.push(product.short_description || '-');
        if (activeColumns.categories !== false) row.push(product.categories ? product.categories.join(', ') : '-');
        if (activeColumns.quantity !== false) row.push(product.quantity);
        if (activeColumns.price !== false) row.push(product.price);
        if (activeColumns.regular_price !== false) row.push(product.regular_price || '-');
        if (activeColumns.sale_price !== false) row.push(product.sale_price || '-');
        if (activeColumns.discount !== false) row.push(product.discount || '0,00 €');
        if (activeColumns.tax !== false) row.push(product.tax || '0,00 €');
        if (activeColumns.weight !== false) row.push(product.weight || '-');
        if (activeColumns.dimensions !== false) row.push(product.dimensions || '-');
        if (activeColumns.attributes !== false) {
          var attrs = product.attributes ? Object.entries(product.attributes).map(function (_ref) {
            var _ref2 = _slicedToArray(_ref, 2),
              k = _ref2[0],
              v = _ref2[1];
            return "".concat(k, ": ").concat(v);
          }).join('; ') : '-';
          row.push(attrs);
        }
        if (activeColumns.stock_quantity !== false) row.push(product.stock_quantity || '-');
        if (activeColumns.stock_status !== false) row.push(product.stock_status === 'en_stock' ? 'En stock' : 'Rupture');
        if (activeColumns.total !== false) row.push(product.total);
        return row;
      });

      // Ajouter les totaux si demandés
      if (showSubtotal) tableData.totals.subtotal = this.sampleData.order.subtotal;
      if (showShipping) tableData.totals.shipping = this.sampleData.order.shipping;
      if (showDiscount) tableData.totals.discount = this.sampleData.order.discount;
      if (showTaxes) tableData.totals.tax = this.sampleData.order.tax;
      if (showTotal) tableData.totals.total = this.sampleData.order.total;

      // Ajouter les informations de style du tableau
      tableData.tableStyleData = this.sampleData.order.tableStyles[tableStyle] || this.sampleData.order.tableStyles['default'];
      return tableData;
    }

    /**
     * Génère des données pour les informations client
     */
  }, {
    key: "generateCustomerInfoData",
    value: function generateCustomerInfoData(properties) {
      var _this = this;
      var _properties$fields = properties.fields,
        fields = _properties$fields === void 0 ? [] : _properties$fields;
      var data = {};
      fields.forEach(function (field) {
        switch (field) {
          case 'name':
            data.name = _this.sampleData.customer.name;
            break;
          case 'email':
            data.email = _this.sampleData.customer.email;
            break;
          case 'phone':
            data.phone = _this.sampleData.customer.phone;
            break;
          case 'address':
            data.address = _this.sampleData.customer.address;
            break;
          case 'company':
            data.company = _this.sampleData.customer.company;
            break;
          case 'vat':
            data.vat = _this.sampleData.customer.vat;
            break;
          case 'siret':
            data.siret = _this.sampleData.customer.siret;
            break;
        }
      });
      return data;
    }

    /**
     * Génère des données pour le logo entreprise
     */
  }, {
    key: "generateCompanyLogoData",
    value: function generateCompanyLogoData(properties) {
      return {
        imageUrl: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjgwIiB2aWV3Qm94PSIwIDAgMjAwIDgwIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjgwIiBmaWxsPSIjMjU2M2ViIi8+Cjx0ZXh0IHg9IjEwMCIgeT0iNDAiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0id2hpdGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5MT0dPPC90ZXh0Pgo8L3N2Zz4=',
        alt: 'Logo de l\'entreprise'
      };
    }

    /**
     * Génère des données pour les informations entreprise
     */
  }, {
    key: "generateCompanyInfoData",
    value: function generateCompanyInfoData(properties) {
      var _this2 = this;
      var _properties$fields2 = properties.fields,
        fields = _properties$fields2 === void 0 ? [] : _properties$fields2;
      var data = {};
      fields.forEach(function (field) {
        switch (field) {
          case 'name':
            data.name = _this2.sampleData.company.name;
            break;
          case 'address':
            data.address = _this2.sampleData.company.address;
            break;
          case 'phone':
            data.phone = _this2.sampleData.company.phone;
            break;
          case 'email':
            data.email = _this2.sampleData.company.email;
            break;
          case 'website':
            data.website = _this2.sampleData.company.website;
            break;
          case 'vat':
            data.vat = _this2.sampleData.company.vat;
            break;
          case 'rcs':
            data.rcs = _this2.sampleData.company.rcs;
            break;
          case 'siret':
            data.siret = _this2.sampleData.company.siret;
            break;
        }
      });
      return data;
    }

    /**
     * Génère des données pour le numéro de commande
     */
  }, {
    key: "generateOrderNumberData",
    value: function generateOrderNumberData(properties) {
      var _properties$format = properties.format,
        format = _properties$format === void 0 ? 'Commande #{order_number} - {order_date}' : _properties$format;
      return {
        formatted: format.replace('{order_number}', this.sampleData.order.number).replace('{order_date}', this.sampleData.order.date)
      };
    }

    /**
     * Génère des données pour le texte dynamique
     */
  }, {
    key: "generateDynamicTextData",
    value: function generateDynamicTextData(properties) {
      var _properties$template = properties.template,
        template = _properties$template === void 0 ? 'total_only' : _properties$template,
        _properties$customCon = properties.customContent,
        customContent = _properties$customCon === void 0 ? '' : _properties$customCon;
      var content = customContent;
      if (template === 'total_only') {
        content = "Total: ".concat(this.sampleData.order.total);
      }

      // Remplacer les variables
      content = content.replace(/\{\{order_total\}\}/g, this.sampleData.order.total).replace(/\{\{customer_name\}\}/g, this.sampleData.customer.name).replace(/\{\{order_number\}\}/g, this.sampleData.order.number);
      return {
        content: content
      };
    }

    /**
     * Génère des données pour les mentions légales
     */
  }, {
    key: "generateMentionsData",
    value: function generateMentionsData(properties) {
      var _properties$showEmail = properties.showEmail,
        showEmail = _properties$showEmail === void 0 ? true : _properties$showEmail,
        _properties$showPhone = properties.showPhone,
        showPhone = _properties$showPhone === void 0 ? true : _properties$showPhone,
        _properties$showSiret = properties.showSiret,
        showSiret = _properties$showSiret === void 0 ? true : _properties$showSiret,
        _properties$showVat = properties.showVat,
        showVat = _properties$showVat === void 0 ? false : _properties$showVat,
        _properties$showAddre = properties.showAddress,
        showAddress = _properties$showAddre === void 0 ? false : _properties$showAddre,
        _properties$showWebsi = properties.showWebsite,
        showWebsite = _properties$showWebsi === void 0 ? false : _properties$showWebsi,
        _properties$showCusto = properties.showCustomText,
        showCustomText = _properties$showCusto === void 0 ? false : _properties$showCusto,
        _properties$customTex = properties.customText,
        customText = _properties$customTex === void 0 ? '' : _properties$customTex;
      var mentions = [];
      if (showEmail) mentions.push(this.sampleData.company.email);
      if (showPhone) mentions.push(this.sampleData.company.phone);
      if (showSiret) mentions.push("SIRET: ".concat(this.sampleData.company.siret));
      if (showVat) mentions.push("TVA: ".concat(this.sampleData.company.vat));
      if (showAddress) mentions.push(this.sampleData.company.address.replace('\n', ' • '));
      if (showWebsite) mentions.push(this.sampleData.company.website);
      if (showCustomText && customText) mentions.push(customText);
      return {
        mentions: mentions
      };
    }

    /**
     * Génère des données pour les rectangles et formes géométriques
     */
  }, {
    key: "generateRectangleData",
    value: function generateRectangleData(properties) {
      return {
        // Les rectangles utilisent principalement les propriétés CSS de base
        rendered: true
      };
    }

    /**
     * Génère des données pour les codes-barres et QR codes
     */
  }, {
    key: "generateBarcodeData",
    value: function generateBarcodeData(properties) {
      var _properties$content = properties.content,
        content = _properties$content === void 0 ? '123456789' : _properties$content;
      return {
        code: content,
        format: properties.type === 'qrcode' ? 'QR_CODE' : 'CODE128'
      };
    }

    /**
     * Génère des données pour les barres de progression
     */
  }, {
    key: "generateProgressBarData",
    value: function generateProgressBarData(properties) {
      var _properties$progressV = properties.progressValue,
        progressValue = _properties$progressV === void 0 ? 75 : _properties$progressV;
      return {
        progress: Math.min(100, Math.max(0, progressValue)),
        label: "".concat(progressValue, "%")
      };
    }

    /**
     * Génère des données pour les filigranes
     */
  }, {
    key: "generateWatermarkData",
    value: function generateWatermarkData(properties) {
      var _properties$content2 = properties.content,
        content = _properties$content2 === void 0 ? 'CONFIDENTIEL' : _properties$content2;
      return {
        text: content,
        angle: -45
      };
    }

    /**
     * Génère des données par défaut pour les éléments non supportés
     */
  }, {
    key: "generateDefaultData",
    value: function generateDefaultData(elementType, properties) {
      return {
        type: elementType,
        placeholder: "Donn\xE9es d'exemple pour ".concat(elementType),
        properties: properties
      };
    }
  }]);
}();
;// ./resources/js/components/preview-system/modes/CanvasMode.jsx
function CanvasMode_typeof(o) { "@babel/helpers - typeof"; return CanvasMode_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, CanvasMode_typeof(o); }
function _regeneratorValues(e) { if (null != e) { var t = e["function" == typeof Symbol && Symbol.iterator || "@@iterator"], r = 0; if (t) return t.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) return { next: function next() { return e && r >= e.length && (e = void 0), { value: e && e[r++], done: !e }; } }; } throw new TypeError(CanvasMode_typeof(e) + " is not iterable"); }
function CanvasMode_regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return CanvasMode_regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (CanvasMode_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, CanvasMode_regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, CanvasMode_regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), CanvasMode_regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", CanvasMode_regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), CanvasMode_regeneratorDefine2(u), CanvasMode_regeneratorDefine2(u, o, "Generator"), CanvasMode_regeneratorDefine2(u, n, function () { return this; }), CanvasMode_regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (CanvasMode_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function CanvasMode_regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } CanvasMode_regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { CanvasMode_regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, CanvasMode_regeneratorDefine2(e, r, n, t); }
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = CanvasMode_unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || CanvasMode_unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function CanvasMode_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return CanvasMode_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? CanvasMode_arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return CanvasMode_arrayLikeToArray(r); }
function CanvasMode_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function CanvasMode_asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function CanvasMode_asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { CanvasMode_asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { CanvasMode_asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function CanvasMode_classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function CanvasMode_defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, CanvasMode_toPropertyKey(o.key), o); } }
function CanvasMode_createClass(e, r, t) { return r && CanvasMode_defineProperties(e.prototype, r), t && CanvasMode_defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function CanvasMode_toPropertyKey(t) { var i = CanvasMode_toPrimitive(t, "string"); return "symbol" == CanvasMode_typeof(i) ? i : i + ""; }
function CanvasMode_toPrimitive(t, r) { if ("object" != CanvasMode_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != CanvasMode_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }


/**
 * Mode Canvas : Aperçu avec données d'exemple
 * Utilisé dans l'éditeur pour prévisualiser les éléments avec des données fictives
 */
var CanvasMode = /*#__PURE__*/function () {
  function CanvasMode() {
    CanvasMode_classCallCheck(this, CanvasMode);
  }
  return CanvasMode_createClass(CanvasMode, null, [{
    key: "loadData",
    value: (
    /**
     * Charge les données d'aperçu pour le mode Canvas
     * @param {Array} elements - Liste des éléments du canvas
     * @param {number|null} orderId - ID de commande (ignoré en mode Canvas)
     * @param {Object} templateData - Données du template
     * @returns {Promise<Object>} Données d'aperçu
     */
    function () {
      var _loadData = CanvasMode_asyncToGenerator(/*#__PURE__*/CanvasMode_regenerator().m(function _callee(elements) {
        var orderId,
          templateData,
          dataProvider,
          elementTypes,
          previewData,
          _iterator,
          _step,
          _loop,
          _args2 = arguments,
          _t2;
        return CanvasMode_regenerator().w(function (_context2) {
          while (1) switch (_context2.p = _context2.n) {
            case 0:
              orderId = _args2.length > 1 && _args2[1] !== undefined ? _args2[1] : null;
              templateData = _args2.length > 2 && _args2[2] !== undefined ? _args2[2] : {};
              dataProvider = new SampleDataProvider(); // Collecter tous les types d'éléments présents
              elementTypes = _toConsumableArray(new Set(elements.map(function (el) {
                return el.type;
              }))); // Générer des données d'exemple pour chaque type d'élément
              previewData = {};
              _iterator = _createForOfIteratorHelper(elementTypes);
              _context2.p = 1;
              _loop = /*#__PURE__*/CanvasMode_regenerator().m(function _loop() {
                var elementType, elementsOfType, _iterator2, _step2, element, elementKey, _t;
                return CanvasMode_regenerator().w(function (_context) {
                  while (1) switch (_context.p = _context.n) {
                    case 0:
                      elementType = _step.value;
                      elementsOfType = elements.filter(function (el) {
                        return el.type === elementType;
                      }); // Pour chaque élément du type, générer des données spécifiques
                      _iterator2 = _createForOfIteratorHelper(elementsOfType);
                      _context.p = 1;
                      _iterator2.s();
                    case 2:
                      if ((_step2 = _iterator2.n()).done) {
                        _context.n = 5;
                        break;
                      }
                      element = _step2.value;
                      elementKey = "".concat(element.type, "_").concat(element.id);
                      _context.n = 3;
                      return dataProvider.getElementData(element.type, element.properties || element);
                    case 3:
                      previewData[elementKey] = _context.v;
                    case 4:
                      _context.n = 2;
                      break;
                    case 5:
                      _context.n = 7;
                      break;
                    case 6:
                      _context.p = 6;
                      _t = _context.v;
                      _iterator2.e(_t);
                    case 7:
                      _context.p = 7;
                      _iterator2.f();
                      return _context.f(7);
                    case 8:
                      return _context.a(2);
                  }
                }, _loop, null, [[1, 6, 7, 8]]);
              });
              _iterator.s();
            case 2:
              if ((_step = _iterator.n()).done) {
                _context2.n = 4;
                break;
              }
              return _context2.d(_regeneratorValues(_loop()), 3);
            case 3:
              _context2.n = 2;
              break;
            case 4:
              _context2.n = 6;
              break;
            case 5:
              _context2.p = 5;
              _t2 = _context2.v;
              _iterator.e(_t2);
            case 6:
              _context2.p = 6;
              _iterator.f();
              return _context2.f(6);
            case 7:
              // Ajouter des variables globales d'exemple
              previewData.global = {
                order_number: 'CMD-2025-001',
                order_date: '19/10/2025',
                order_total: '149,99 €',
                customer_name: 'Jean Dupont',
                customer_email: 'jean.dupont@email.com',
                customer_phone: '+33 6 12 34 56 78',
                company_name: 'Ma Société SARL',
                company_address: '123 Rue de la Paix\n75001 Paris\nFrance',
                company_phone: '+33 1 42 86 75 30',
                company_email: 'contact@masociete.com'
              };
              return _context2.a(2, previewData);
          }
        }, _callee, null, [[1, 5, 6, 7]]);
      }));
      function loadData(_x) {
        return _loadData.apply(this, arguments);
      }
      return loadData;
    }()
    /**
     * Valide si le mode Canvas peut être utilisé
     * @param {Array} elements - Liste des éléments
     * @returns {boolean} True si valide
     */
    )
  }, {
    key: "validate",
    value: function validate(elements) {
      // Le mode Canvas accepte tous les éléments
      return elements && Array.isArray(elements);
    }

    /**
     * Retourne les capacités du mode Canvas
     * @returns {Object} Capacités disponibles
     */
  }, {
    key: "getCapabilities",
    value: function getCapabilities() {
      return {
        supportsRealData: false,
        supportsDynamicVariables: true,
        supportsAllElements: true,
        maxElements: 50,
        features: ['données_exemple', 'variables_globales', 'rendu_temps_reel', 'guides_marge']
      };
    }
  }]);
}();

/***/ })

}]);