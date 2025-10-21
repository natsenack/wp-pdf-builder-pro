"use strict";
(self["webpackChunkwp_pdf_builder_pro"] = self["webpackChunkwp_pdf_builder_pro"] || []).push([[295],{

/***/ 295:
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, {
  MetaboxMode: () => (/* binding */ MetaboxMode)
});

;// ./resources/js/components/preview-system/data/RealDataProvider.jsx
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
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
 * Fournisseur de données réelles pour le mode Metabox
 * Récupère les vraies données depuis WooCommerce
 */
var RealDataProvider = /*#__PURE__*/function () {
  function RealDataProvider() {
    var _window$pdfBuilderPro, _window$pdfBuilderAja;
    _classCallCheck(this, RealDataProvider);
    this.ajaxUrl = window.ajaxurl || '/wp-admin/admin-ajax.php';
    this.nonce = ((_window$pdfBuilderPro = window.pdfBuilderPro) === null || _window$pdfBuilderPro === void 0 ? void 0 : _window$pdfBuilderPro.nonce) || ((_window$pdfBuilderAja = window.pdfBuilderAjax) === null || _window$pdfBuilderAja === void 0 ? void 0 : _window$pdfBuilderAja.nonce) || '';
    this.variablesCache = new Map(); // Cache des variables par commande
  }

  /**
   * Charge les données complètes d'une commande WooCommerce
   * @param {number} orderId - ID de la commande
   * @returns {Promise<Object>} Données de la commande
   */
  return _createClass(RealDataProvider, [{
    key: "loadOrderData",
    value: (function () {
      var _loadOrderData = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(orderId) {
        var response, _response$data;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.n) {
            case 0:
              _context.n = 1;
              return this.makeAjaxRequest('pdf_builder_get_order_data', {
                order_id: orderId
              });
            case 1:
              response = _context.v;
              if (response.success) {
                _context.n = 2;
                break;
              }
              throw new Error(((_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.message) || 'Erreur lors du chargement des données de commande');
            case 2:
              return _context.a(2, response.data.order);
          }
        }, _callee, this);
      }));
      function loadOrderData(_x) {
        return _loadOrderData.apply(this, arguments);
      }
      return loadOrderData;
    }()
    /**
     * Charge les variables mappées pour une commande (utilise VariableMapper PHP)
     * @param {number} orderId - ID de la commande
     * @returns {Promise<Object>} Variables mappées
     */
    )
  }, {
    key: "loadVariables",
    value: (function () {
      var _loadVariables = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(orderId) {
        var response, _response$data2, variables;
        return _regenerator().w(function (_context2) {
          while (1) switch (_context2.n) {
            case 0:
              if (!this.variablesCache.has(orderId)) {
                _context2.n = 1;
                break;
              }
              return _context2.a(2, this.variablesCache.get(orderId));
            case 1:
              _context2.n = 2;
              return this.makeAjaxRequest('pdf_builder_get_order_preview_data', {
                order_id: orderId
              });
            case 2:
              response = _context2.v;
              if (response.success) {
                _context2.n = 3;
                break;
              }
              throw new Error(((_response$data2 = response.data) === null || _response$data2 === void 0 ? void 0 : _response$data2.message) || 'Erreur lors du chargement des variables d\'aperçu');
            case 3:
              variables = response.data.variables;
              this.variablesCache.set(orderId, variables);
              return _context2.a(2, variables);
          }
        }, _callee2, this);
      }));
      function loadVariables(_x2) {
        return _loadVariables.apply(this, arguments);
      }
      return loadVariables;
    }()
    /**
     * Valide l'accès à une commande
     * @param {number} orderId - ID de la commande
     * @returns {Promise<void>}
     */
    )
  }, {
    key: "validateOrderAccess",
    value: (function () {
      var _validateOrderAccess = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee3(orderId) {
        var response, _response$data3;
        return _regenerator().w(function (_context3) {
          while (1) switch (_context3.n) {
            case 0:
              _context3.n = 1;
              return this.makeAjaxRequest('pdf_builder_validate_order_access', {
                order_id: orderId
              });
            case 1:
              response = _context3.v;
              if (response.success) {
                _context3.n = 2;
                break;
              }
              throw new Error(((_response$data3 = response.data) === null || _response$data3 === void 0 ? void 0 : _response$data3.message) || 'Accès non autorisé à cette commande');
            case 2:
              return _context3.a(2);
          }
        }, _callee3, this);
      }));
      function validateOrderAccess(_x3) {
        return _validateOrderAccess.apply(this, arguments);
      }
      return validateOrderAccess;
    }()
    /**
     * Génère des données réelles pour un type d'élément spécifique
     * @param {string} elementType - Type de l'élément
     * @param {Object} properties - Propriétés de l'élément
     * @param {Object} orderData - Données de la commande
     * @returns {Promise<any>} Données réelles
     */
    )
  }, {
    key: "getElementData",
    value: (function () {
      var _getElementData = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee4(elementType, properties, orderData) {
        var _t;
        return _regenerator().w(function (_context4) {
          while (1) switch (_context4.n) {
            case 0:
              _t = elementType;
              _context4.n = _t === 'product_table' ? 1 : _t === 'customer_info' ? 2 : _t === 'company_logo' ? 3 : _t === 'company_info' ? 4 : _t === 'order_number' ? 5 : _t === 'dynamic-text' ? 6 : _t === 'mentions' ? 7 : 8;
              break;
            case 1:
              return _context4.a(2, this.generateProductTableData(properties, orderData));
            case 2:
              return _context4.a(2, this.generateCustomerInfoData(properties, orderData));
            case 3:
              return _context4.a(2, this.generateCompanyLogoData(properties, orderData));
            case 4:
              return _context4.a(2, this.generateCompanyInfoData(properties, orderData));
            case 5:
              return _context4.a(2, this.generateOrderNumberData(properties, orderData));
            case 6:
              return _context4.a(2, this.generateDynamicTextData(properties, orderData));
            case 7:
              return _context4.a(2, this.generateMentionsData(properties, orderData));
            case 8:
              return _context4.a(2, this.generateDefaultData(elementType, properties, orderData));
            case 9:
              return _context4.a(2);
          }
        }, _callee4, this);
      }));
      function getElementData(_x4, _x5, _x6) {
        return _getElementData.apply(this, arguments);
      }
      return getElementData;
    }()
    /**
     * Génère des données pour un tableau de produits (données réelles)
     */
    )
  }, {
    key: "generateProductTableData",
    value: function generateProductTableData(properties, orderData) {
      var _properties$columns = properties.columns,
        columns = _properties$columns === void 0 ? {} : _properties$columns,
        _properties$showSubto = properties.showSubtotal,
        showSubtotal = _properties$showSubto === void 0 ? false : _properties$showSubto,
        _properties$showShipp = properties.showShipping,
        showShipping = _properties$showShipp === void 0 ? true : _properties$showShipp,
        _properties$showTaxes = properties.showTaxes,
        showTaxes = _properties$showTaxes === void 0 ? true : _properties$showTaxes,
        _properties$tableStyl = properties.tableStyle,
        tableStyle = _properties$tableStyl === void 0 ? 'default' : _properties$tableStyl;
      var tableData = {
        headers: [],
        rows: [],
        totals: {}
      };

      // Déterminer les colonnes à afficher
      if (columns.image !== false) tableData.headers.push('Image');
      if (columns.name !== false) tableData.headers.push('Produit');
      if (columns.sku !== false) tableData.headers.push('SKU');
      if (columns.quantity !== false) tableData.headers.push('Qté');
      if (columns.price !== false) tableData.headers.push('Prix');
      if (columns.total !== false) tableData.headers.push('Total');

      // Générer les lignes de produits depuis les données réelles
      if (orderData.items && Array.isArray(orderData.items)) {
        tableData.rows = orderData.items.map(function (item) {
          var row = [];
          if (columns.image !== false) row.push(item.image || '');
          if (columns.name !== false) row.push(item.name || '');
          if (columns.sku !== false) row.push(item.sku || '');
          if (columns.quantity !== false) row.push(item.quantity || 0);
          if (columns.price !== false) row.push(item.price || '0 €');
          if (columns.total !== false) row.push(item.total || '0 €');
          return row;
        });
      }

      // Ajouter les totaux si demandés
      if (showSubtotal) tableData.totals.subtotal = orderData.subtotal || '0 €';
      if (showShipping) tableData.totals.shipping = orderData.shipping_total || '0 €';
      if (showTaxes) tableData.totals.tax = orderData.total_tax || '0 €';
      tableData.totals.total = orderData.total || '0 €';

      // Ajouter les données de style du tableau (même logique que SampleDataProvider)
      tableData.tableStyleData = this.getTableStyleData(tableStyle);
      return tableData;
    }

    /**
     * Génère des données pour les informations client (données réelles)
     */
  }, {
    key: "generateCustomerInfoData",
    value: function generateCustomerInfoData(properties, orderData) {
      var _this = this;
      var _properties$fields = properties.fields,
        fields = _properties$fields === void 0 ? [] : _properties$fields;
      var data = {};
      fields.forEach(function (field) {
        var _orderData$billing, _orderData$billing2, _orderData$billing3, _orderData$billing4, _orderData$billing5, _orderData$billing6, _orderData$billing7;
        switch (field) {
          case 'name':
            data.name = (_orderData$billing = orderData.billing) !== null && _orderData$billing !== void 0 && _orderData$billing.first_name && (_orderData$billing2 = orderData.billing) !== null && _orderData$billing2 !== void 0 && _orderData$billing2.last_name ? "".concat(orderData.billing.first_name, " ").concat(orderData.billing.last_name) : '';
            break;
          case 'email':
            data.email = ((_orderData$billing3 = orderData.billing) === null || _orderData$billing3 === void 0 ? void 0 : _orderData$billing3.email) || '';
            break;
          case 'phone':
            data.phone = ((_orderData$billing4 = orderData.billing) === null || _orderData$billing4 === void 0 ? void 0 : _orderData$billing4.phone) || '';
            break;
          case 'address':
            data.address = _this.formatAddress(orderData.billing);
            break;
          case 'company':
            data.company = ((_orderData$billing5 = orderData.billing) === null || _orderData$billing5 === void 0 ? void 0 : _orderData$billing5.company) || '';
            break;
          case 'vat':
            data.vat = ((_orderData$billing6 = orderData.billing) === null || _orderData$billing6 === void 0 ? void 0 : _orderData$billing6.vat) || '';
            break;
          case 'siret':
            data.siret = ((_orderData$billing7 = orderData.billing) === null || _orderData$billing7 === void 0 ? void 0 : _orderData$billing7.siret) || '';
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
    value: function generateCompanyLogoData(properties, orderData) {
      // Le logo entreprise est généralement stocké dans les options WordPress
      // Pour l'instant, retourner une valeur par défaut
      return {
        imageUrl: '',
        // À récupérer depuis wp_options
        alt: 'Logo de l\'entreprise'
      };
    }

    /**
     * Génère des données pour les informations entreprise
     */
  }, {
    key: "generateCompanyInfoData",
    value: function generateCompanyInfoData(properties, orderData) {
      // Les informations entreprise sont généralement dans wp_options
      // Simulation avec des données vides pour l'instant
      var _properties$fields2 = properties.fields,
        fields = _properties$fields2 === void 0 ? [] : _properties$fields2;
      var data = {};
      fields.forEach(function (field) {
        data[field] = ''; // À récupérer depuis wp_options
      });
      return data;
    }

    /**
     * Génère des données pour le numéro de commande
     */
  }, {
    key: "generateOrderNumberData",
    value: function generateOrderNumberData(properties, orderData) {
      var _properties$format = properties.format,
        format = _properties$format === void 0 ? 'Commande #{order_number} - {order_date}' : _properties$format;
      return {
        formatted: format.replace('{order_number}', orderData.number || orderData.id || '').replace('{order_date}', orderData.date_created || '')
      };
    }

    /**
     * Génère des données pour un élément de texte dynamique
     */
  }, {
    key: "generateDynamicTextData",
    value: (function () {
      var _generateDynamicTextData = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee5(properties, orderData) {
        var _properties$template, template, _properties$customCon, customContent, content, _orderData$order, variables, _t2;
        return _regenerator().w(function (_context5) {
          while (1) switch (_context5.p = _context5.n) {
            case 0:
              _properties$template = properties.template, template = _properties$template === void 0 ? 'total_only' : _properties$template, _properties$customCon = properties.customContent, customContent = _properties$customCon === void 0 ? '' : _properties$customCon;
              content = customContent;
              if (template === 'total_only') {
                content = "Total: ".concat(orderData.total || '0 €');
              }

              // Charger les variables mappées depuis le VariableMapper PHP
              _context5.p = 1;
              _context5.n = 2;
              return this.loadVariables(orderData.id || ((_orderData$order = orderData.order) === null || _orderData$order === void 0 ? void 0 : _orderData$order.id));
            case 2:
              variables = _context5.v;
              // Remplacer les variables avec les vraies données du VariableMapper
              content = this.replaceVariablesWithMapper(content, variables);
              _context5.n = 4;
              break;
            case 3:
              _context5.p = 3;
              _t2 = _context5.v;
              console.warn('Erreur lors du chargement des variables, utilisation du fallback:', _t2);
              // Fallback vers l'ancien système si le VariableMapper échoue
              content = this.replaceVariables(content, orderData);
            case 4:
              return _context5.a(2, {
                content: content
              });
          }
        }, _callee5, this, [[1, 3]]);
      }));
      function generateDynamicTextData(_x7, _x8) {
        return _generateDynamicTextData.apply(this, arguments);
      }
      return generateDynamicTextData;
    }()
    /**
     * Génère des données pour les mentions légales
     */
    )
  }, {
    key: "generateMentionsData",
    value: function generateMentionsData(properties, orderData) {
      // Les mentions légales sont généralement dans wp_options
      // Simulation pour l'instant
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
      if (showEmail) mentions.push(orderData.company_email || '');
      if (showPhone) mentions.push(orderData.company_phone || '');
      if (showSiret) mentions.push("SIRET: ".concat(orderData.company_siret || ''));
      if (showVat) mentions.push("TVA: ".concat(orderData.company_vat || ''));
      if (showAddress) mentions.push(orderData.company_address || '');
      if (showWebsite) mentions.push(orderData.company_website || '');
      if (showCustomText && customText) mentions.push(customText);
      return {
        mentions: mentions
      };
    }

    /**
     * Génère des données par défaut pour les éléments non supportés
     */
  }, {
    key: "generateDefaultData",
    value: function generateDefaultData(elementType, properties, orderData) {
      return {
        type: elementType,
        placeholder: "Donn\xE9es r\xE9elles pour ".concat(elementType),
        properties: properties,
        orderData: orderData
      };
    }

    /**
     * Formate une adresse depuis les données WooCommerce
     */
  }, {
    key: "formatAddress",
    value: function formatAddress(addressData) {
      if (!addressData) return '';
      var parts = [addressData.address_1, addressData.address_2, addressData.postcode, addressData.city, addressData.state, addressData.country].filter(Boolean);
      return parts.join('\n');
    }

    /**
     * Remplace les variables dynamiques dans un contenu
     */
  }, {
    key: "replaceVariables",
    value: function replaceVariables(content, orderData) {
      var _orderData$billing8, _orderData$billing9, _orderData$billing0, _orderData$billing1;
      if (!content || !orderData) return content;
      var replacements = {
        '{{order_number}}': orderData.number || orderData.id || '',
        '{{order_date}}': orderData.date_created || '',
        '{{order_total}}': orderData.total || '0 €',
        '{{order_status}}': orderData.status || '',
        '{{customer_name}}': (_orderData$billing8 = orderData.billing) !== null && _orderData$billing8 !== void 0 && _orderData$billing8.first_name && (_orderData$billing9 = orderData.billing) !== null && _orderData$billing9 !== void 0 && _orderData$billing9.last_name ? "".concat(orderData.billing.first_name, " ").concat(orderData.billing.last_name) : '',
        '{{customer_email}}': ((_orderData$billing0 = orderData.billing) === null || _orderData$billing0 === void 0 ? void 0 : _orderData$billing0.email) || '',
        '{{customer_phone}}': ((_orderData$billing1 = orderData.billing) === null || _orderData$billing1 === void 0 ? void 0 : _orderData$billing1.phone) || '',
        '{{billing_address}}': this.formatAddress(orderData.billing),
        '{{shipping_address}}': this.formatAddress(orderData.shipping),
        '{{payment_method}}': orderData.payment_method_title || '',
        '{{shipping_method}}': orderData.shipping_method || '',
        '{{subtotal}}': orderData.subtotal || '0 €',
        '{{tax_amount}}': orderData.total_tax || '0 €',
        '{{shipping_amount}}': orderData.shipping_total || '0 €',
        '{{discount_amount}}': orderData.discount_total || '0 €',
        '{{total_excl_tax}}': orderData.total_excl_tax || '0 €'
      };
      var result = content;
      Object.entries(replacements).forEach(function (_ref) {
        var _ref2 = _slicedToArray(_ref, 2),
          variable = _ref2[0],
          value = _ref2[1];
        result = result.replace(new RegExp(variable.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), value);
      });
      return result;
    }

    /**
     * Remplace les variables dans le contenu en utilisant les données du VariableMapper PHP
     * @param {string} content - Contenu avec variables
     * @param {Object} variables - Variables du VariableMapper
     * @returns {string} Contenu avec variables remplacées
     */
  }, {
    key: "replaceVariablesWithMapper",
    value: function replaceVariablesWithMapper(content, variables) {
      if (!content || !variables) return content;
      var result = content;

      // Remplacer chaque variable du mapper
      Object.entries(variables).forEach(function (_ref3) {
        var _ref4 = _slicedToArray(_ref3, 2),
          key = _ref4[0],
          value = _ref4[1];
        var variable = "{{".concat(key, "}}");
        var regex = new RegExp(variable.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g');
        result = result.replace(regex, value || '');
      });
      return result;
    }

    /**
     * Récupère les données de style pour un tableau
     */
  }, {
    key: "getTableStyleData",
    value: function getTableStyleData(tableStyle) {
      var tableStyles = {
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
      };
      return tableStyles[tableStyle] || tableStyles['default'];
    }

    /**
     * Effectue une requête AJAX vers WordPress
     */
  }, {
    key: "makeAjaxRequest",
    value: (function () {
      var _makeAjaxRequest = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee6(action) {
        var data,
          formData,
          response,
          _args6 = arguments;
        return _regenerator().w(function (_context6) {
          while (1) switch (_context6.n) {
            case 0:
              data = _args6.length > 1 && _args6[1] !== undefined ? _args6[1] : {};
              formData = new FormData();
              formData.append('action', action);
              formData.append('nonce', this.nonce);
              Object.entries(data).forEach(function (_ref5) {
                var _ref6 = _slicedToArray(_ref5, 2),
                  key = _ref6[0],
                  value = _ref6[1];
                formData.append(key, value);
              });
              _context6.n = 1;
              return fetch(this.ajaxUrl, {
                method: 'POST',
                body: formData
              });
            case 1:
              response = _context6.v;
              if (response.ok) {
                _context6.n = 2;
                break;
              }
              throw new Error("Erreur HTTP: ".concat(response.status));
            case 2:
              _context6.n = 3;
              return response.json();
            case 3:
              return _context6.a(2, _context6.v);
          }
        }, _callee6, this);
      }));
      function makeAjaxRequest(_x9) {
        return _makeAjaxRequest.apply(this, arguments);
      }
      return makeAjaxRequest;
    }())
  }]);
}();
;// ./resources/js/components/preview-system/modes/MetaboxMode.jsx
function MetaboxMode_typeof(o) { "@babel/helpers - typeof"; return MetaboxMode_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, MetaboxMode_typeof(o); }
function _regeneratorValues(e) { if (null != e) { var t = e["function" == typeof Symbol && Symbol.iterator || "@@iterator"], r = 0; if (t) return t.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) return { next: function next() { return e && r >= e.length && (e = void 0), { value: e && e[r++], done: !e }; } }; } throw new TypeError(MetaboxMode_typeof(e) + " is not iterable"); }
function MetaboxMode_regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return MetaboxMode_regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (MetaboxMode_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, MetaboxMode_regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, MetaboxMode_regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), MetaboxMode_regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", MetaboxMode_regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), MetaboxMode_regeneratorDefine2(u), MetaboxMode_regeneratorDefine2(u, o, "Generator"), MetaboxMode_regeneratorDefine2(u, n, function () { return this; }), MetaboxMode_regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (MetaboxMode_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function MetaboxMode_regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } MetaboxMode_regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { MetaboxMode_regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, MetaboxMode_regeneratorDefine2(e, r, n, t); }
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = MetaboxMode_unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || MetaboxMode_unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function MetaboxMode_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return MetaboxMode_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? MetaboxMode_arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return MetaboxMode_arrayLikeToArray(r); }
function MetaboxMode_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function MetaboxMode_asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function MetaboxMode_asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { MetaboxMode_asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { MetaboxMode_asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function MetaboxMode_classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function MetaboxMode_defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, MetaboxMode_toPropertyKey(o.key), o); } }
function MetaboxMode_createClass(e, r, t) { return r && MetaboxMode_defineProperties(e.prototype, r), t && MetaboxMode_defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function MetaboxMode_toPropertyKey(t) { var i = MetaboxMode_toPrimitive(t, "string"); return "symbol" == MetaboxMode_typeof(i) ? i : i + ""; }
function MetaboxMode_toPrimitive(t, r) { if ("object" != MetaboxMode_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != MetaboxMode_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }


/**
 * Mode Metabox : Aperçu avec données réelles de commande WooCommerce
 * Utilisé dans la metabox des commandes pour afficher l'aperçu avec les vraies données
 */
var MetaboxMode = /*#__PURE__*/function () {
  function MetaboxMode() {
    MetaboxMode_classCallCheck(this, MetaboxMode);
  }
  return MetaboxMode_createClass(MetaboxMode, null, [{
    key: "loadData",
    value: (
    /**
     * Charge les données d'aperçu pour le mode Metabox
     * @param {Array} elements - Liste des éléments du template
     * @param {number} orderId - ID de la commande WooCommerce
     * @param {Object} templateData - Données du template
     * @returns {Promise<Object>} Données d'aperçu
     */
    function () {
      var _loadData = MetaboxMode_asyncToGenerator(/*#__PURE__*/MetaboxMode_regenerator().m(function _callee(elements, orderId) {
        var templateData,
          dataProvider,
          orderData,
          elementTypes,
          previewData,
          _iterator,
          _step,
          _loop,
          _args2 = arguments,
          _t2,
          _t3;
        return MetaboxMode_regenerator().w(function (_context2) {
          while (1) switch (_context2.p = _context2.n) {
            case 0:
              templateData = _args2.length > 2 && _args2[2] !== undefined ? _args2[2] : {};
              if (orderId) {
                _context2.n = 1;
                break;
              }
              throw new Error('ID de commande requis pour le mode Metabox');
            case 1:
              dataProvider = new RealDataProvider();
              _context2.p = 2;
              _context2.n = 3;
              return dataProvider.loadOrderData(orderId);
            case 3:
              orderData = _context2.v;
              // Collecter tous les types d'éléments présents
              elementTypes = _toConsumableArray(new Set(elements.map(function (el) {
                return el.type;
              }))); // Générer des données réelles pour chaque type d'élément
              previewData = {};
              _iterator = _createForOfIteratorHelper(elementTypes);
              _context2.p = 4;
              _loop = /*#__PURE__*/MetaboxMode_regenerator().m(function _loop() {
                var elementType, elementsOfType, _iterator2, _step2, element, elementKey, _t;
                return MetaboxMode_regenerator().w(function (_context) {
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
                      return dataProvider.getElementData(element.type, element.properties || element, orderData);
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
            case 5:
              if ((_step = _iterator.n()).done) {
                _context2.n = 7;
                break;
              }
              return _context2.d(_regeneratorValues(_loop()), 6);
            case 6:
              _context2.n = 5;
              break;
            case 7:
              _context2.n = 9;
              break;
            case 8:
              _context2.p = 8;
              _t2 = _context2.v;
              _iterator.e(_t2);
            case 9:
              _context2.p = 9;
              _iterator.f();
              return _context2.f(9);
            case 10:
              // Variables globales de la commande
              previewData.global = orderData;
              return _context2.a(2, previewData);
            case 11:
              _context2.p = 11;
              _t3 = _context2.v;
              console.error('Erreur lors du chargement des données de commande:', _t3);
              throw new Error("Impossible de charger les donn\xE9es de la commande ".concat(orderId, ": ").concat(_t3.message));
            case 12:
              return _context2.a(2);
          }
        }, _callee, null, [[4, 8, 9, 10], [2, 11]]);
      }));
      function loadData(_x, _x2) {
        return _loadData.apply(this, arguments);
      }
      return loadData;
    }()
    /**
     * Valide si le mode Metabox peut être utilisé
     * @param {Array} elements - Liste des éléments
     * @param {number} orderId - ID de la commande
     * @returns {boolean} True si valide
     */
    )
  }, {
    key: "validate",
    value: function validate(elements, orderId) {
      return elements && Array.isArray(elements) && orderId && typeof orderId === 'number' && orderId > 0;
    }

    /**
     * Retourne les capacités du mode Metabox
     * @returns {Object} Capacités disponibles
     */
  }, {
    key: "getCapabilities",
    value: function getCapabilities() {
      return {
        supportsRealData: true,
        supportsDynamicVariables: true,
        supportsAllElements: true,
        requiresOrderId: true,
        maxElements: 100,
        features: ['données_réelles', 'variables_dynamiques', 'validation_commande', 'sécurité_wc']
      };
    }

    /**
     * Vérifie si la commande existe et est accessible
     * @param {number} orderId - ID de la commande
     * @returns {Promise<boolean>} True si accessible
     */
  }, {
    key: "checkOrderAccess",
    value: (function () {
      var _checkOrderAccess = MetaboxMode_asyncToGenerator(/*#__PURE__*/MetaboxMode_regenerator().m(function _callee2(orderId) {
        var dataProvider, _t4;
        return MetaboxMode_regenerator().w(function (_context3) {
          while (1) switch (_context3.p = _context3.n) {
            case 0:
              _context3.p = 0;
              dataProvider = new RealDataProvider();
              _context3.n = 1;
              return dataProvider.validateOrderAccess(orderId);
            case 1:
              return _context3.a(2, true);
            case 2:
              _context3.p = 2;
              _t4 = _context3.v;
              console.warn("Acc\xE8s refus\xE9 \xE0 la commande ".concat(orderId, ":"), _t4.message);
              return _context3.a(2, false);
          }
        }, _callee2, null, [[0, 2]]);
      }));
      function checkOrderAccess(_x3) {
        return _checkOrderAccess.apply(this, arguments);
      }
      return checkOrderAccess;
    }())
  }]);
}();

/***/ })

}]);