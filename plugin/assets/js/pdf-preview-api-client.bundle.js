"use strict";
(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define("pdfBuilderReact", [], factory);
	else if(typeof exports === 'object')
		exports["pdfBuilderReact"] = factory();
	else
		root["pdfBuilderReact"] = factory();
})(self, () => {
return (self["webpackChunkpdfBuilderReact"] = self["webpackChunkpdfBuilderReact"] || []).push([["pdf-preview-api-client"],{

/***/ "./assets/js/pdf-preview-api-client.js":
/*!*********************************************!*\
  !*** ./assets/js/pdf-preview-api-client.js ***!
  \*********************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
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
 * PDF Builder Pro - Preview API Client
 * Intégration complète de l'API Preview 1.4
 */

// Fonctions de debug conditionnel
function isDebugEnabled() {
  var _window$pdfBuilderDeb;
  // Debug activé si explicitement forcé ou si activé dans les paramètres
  return window.location.search.includes('debug=force') || typeof window.pdfBuilderDebugSettings !== 'undefined' && ((_window$pdfBuilderDeb = window.pdfBuilderDebugSettings) === null || _window$pdfBuilderDeb === void 0 ? void 0 : _window$pdfBuilderDeb.javascript);
}
function debugLog() {
  // Debug logging disabled for production
}
function debugError() {
  var _console;
  // TEMP: Always log for debugging
  (_console = console).error.apply(_console, arguments);
}
function debugWarn() {
  var _console2;
  // TEMP: Always log for debugging
  (_console2 = console).warn.apply(_console2, arguments);
}
var PDFPreviewAPI = /*#__PURE__*/function () {
  function PDFPreviewAPI() {
    var _pdfBuilderAjax;
    _classCallCheck(this, PDFPreviewAPI);
    this.endpoint = pdfBuilderAjax.ajaxurl;
    this.nonce = ((_pdfBuilderAjax = pdfBuilderAjax) === null || _pdfBuilderAjax === void 0 ? void 0 : _pdfBuilderAjax.nonce) || '';
    this.isGenerating = false;
    this.cache = new Map();
  }

  /**
   * Génère un aperçu depuis l'éditeur (données fictives)
   */
  return _createClass(PDFPreviewAPI, [{
    key: "generateEditorPreview",
    value: (function () {
      var _generateEditorPreview = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(templateData) {
        var options,
          formData,
          response,
          result,
          _args = arguments,
          _t;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.p = _context.n) {
            case 0:
              options = _args.length > 1 && _args[1] !== undefined ? _args[1] : {};
              if (!this.isGenerating) {
                _context.n = 1;
                break;
              }
              debugWarn('⚠️ Génération déjà en cours...');
              return _context.a(2, null);
            case 1:
              this.isGenerating = true;
              this.showLoadingIndicator();
              _context.p = 2;
              formData = new FormData();
              formData.append('action', 'wp_pdf_preview_image');
              formData.append('nonce', this.nonce);
              formData.append('context', 'editor');
              formData.append('template_data', JSON.stringify(templateData));
              formData.append('quality', options.quality || 150);
              formData.append('format', options.format || 'png');
              debugLog('📤 Envoi requête preview éditeur...');
              _context.n = 3;
              return fetch(this.endpoint, {
                method: 'POST',
                body: formData
              });
            case 3:
              response = _context.v;
              _context.n = 4;
              return response.json();
            case 4:
              result = _context.v;
              if (!result.success) {
                _context.n = 5;
                break;
              }
              debugLog('✅ Aperçu éditeur généré:', result.data);
              this.cachePreview(result.data);
              this.displayPreview(result.data.image_url, 'editor');
              return _context.a(2, result.data);
            case 5:
              debugError('❌ Erreur génération éditeur:', result.data);
              this.showError('Erreur lors de la génération de l\'aperçu');
              return _context.a(2, null);
            case 6:
              _context.n = 8;
              break;
            case 7:
              _context.p = 7;
              _t = _context.v;
              debugError('❌ Erreur réseau:', _t);
              this.showError('Erreur de connexion');
              return _context.a(2, null);
            case 8:
              _context.p = 8;
              this.isGenerating = false;
              this.hideLoadingIndicator();
              return _context.f(8);
            case 9:
              return _context.a(2);
          }
        }, _callee, this, [[2, 7, 8, 9]]);
      }));
      function generateEditorPreview(_x) {
        return _generateEditorPreview.apply(this, arguments);
      }
      return generateEditorPreview;
    }()
    /**
     * Génère un aperçu depuis la metabox WooCommerce (données réelles)
     */
    )
  }, {
    key: "generateOrderPreview",
    value: (function () {
      var _generateOrderPreview = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(templateData, orderId) {
        var options,
          formData,
          response,
          result,
          _args2 = arguments,
          _t2;
        return _regenerator().w(function (_context2) {
          while (1) switch (_context2.p = _context2.n) {
            case 0:
              options = _args2.length > 2 && _args2[2] !== undefined ? _args2[2] : {};
              if (!this.isGenerating) {
                _context2.n = 1;
                break;
              }
              debugWarn('⚠️ Génération déjà en cours...');
              return _context2.a(2, null);
            case 1:
              this.isGenerating = true;
              this.showLoadingIndicator();
              _context2.p = 2;
              formData = new FormData();
              formData.append('action', 'wp_pdf_preview_image');
              formData.append('nonce', this.nonce);
              formData.append('context', 'metabox');
              formData.append('template_data', JSON.stringify(templateData));
              formData.append('order_id', orderId);
              formData.append('quality', options.quality || 150);
              formData.append('format', options.format || 'png');
              debugLog('📤 Envoi requête preview commande...', orderId);
              _context2.n = 3;
              return fetch(this.endpoint, {
                method: 'POST',
                body: formData
              });
            case 3:
              response = _context2.v;
              _context2.n = 4;
              return response.json();
            case 4:
              result = _context2.v;
              if (!result.success) {
                _context2.n = 5;
                break;
              }
              debugLog('✅ Aperçu commande généré:', result.data);
              this.cachePreview(result.data);
              this.displayPreview(result.data.image_url, 'metabox', orderId);
              return _context2.a(2, result.data);
            case 5:
              debugError('❌ Erreur génération commande:', result.data);
              this.showError('Erreur lors de la génération de l\'aperçu de commande');
              return _context2.a(2, null);
            case 6:
              _context2.n = 8;
              break;
            case 7:
              _context2.p = 7;
              _t2 = _context2.v;
              debugError('❌ Erreur réseau:', _t2);
              this.showError('Erreur de connexion');
              return _context2.a(2, null);
            case 8:
              _context2.p = 8;
              this.isGenerating = false;
              this.hideLoadingIndicator();
              return _context2.f(8);
            case 9:
              return _context2.a(2);
          }
        }, _callee2, this, [[2, 7, 8, 9]]);
      }));
      function generateOrderPreview(_x2, _x3) {
        return _generateOrderPreview.apply(this, arguments);
      }
      return generateOrderPreview;
    }()
    /**
     * Met en cache les aperçus générés
     */
    )
  }, {
    key: "cachePreview",
    value: function cachePreview(data) {
      var key = data.cache_key || this.generateCacheKey(data);
      this.cache.set(key, {
        url: data.image_url,
        timestamp: Date.now(),
        context: data.context || 'unknown'
      });

      // Nettoyer le cache ancien (garder seulement 10 derniers)
      if (this.cache.size > 10) {
        var oldestKey = this.cache.keys().next().value;
        this.cache["delete"](oldestKey);
      }
    }

    /**
     * Génère une clé de cache
     */
  }, {
    key: "generateCacheKey",
    value: function generateCacheKey(data) {
      return btoa(JSON.stringify({
        context: data.context,
        order_id: data.order_id,
        template_hash: this.hashString(JSON.stringify(data.template_data))
      })).slice(0, 32);
    }

    /**
     * Hash simple pour les clés de cache
     */
  }, {
    key: "hashString",
    value: function hashString(str) {
      var hash = 0;
      for (var i = 0; i < str.length; i++) {
        var _char = str.charCodeAt(i);
        hash = (hash << 5) - hash + _char;
        hash = hash & hash; // Convertir en 32 bits
      }
      return Math.abs(hash).toString(36);
    }

    /**
     * Affiche l'aperçu généré
     */
  }, {
    key: "displayPreview",
    value: function displayPreview(imageUrl, context) {
      var orderId = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
      // Créer ou mettre à jour la modal d'aperçu
      var previewModal = document.getElementById('pdf-preview-modal');
      if (!previewModal) {
        previewModal = this.createPreviewModal();
        document.body.appendChild(previewModal);
      }
      var img = previewModal.querySelector('#pdf-preview-image');
      var title = previewModal.querySelector('#pdf-preview-title');
      img.src = imageUrl;
      img.style.maxWidth = '100%';
      img.style.height = 'auto';
      if (context === 'editor') {
        title.textContent = '👁️ Aperçu du Template';
      } else {
        title.textContent = "\uD83D\uDCC4 Aper\xE7u Commande #".concat(orderId);
      }

      // Ajouter des boutons d'action
      this.addPreviewActions(previewModal, imageUrl, context);

      // Afficher la modal en togglant la classe
      previewModal.classList.add('visible');
      debugLog('🖼️ Aperçu affiché:', imageUrl);
    }

    /**
     * Crée la modal d'aperçu - FIXED CENTERING v3.3
     */
  }, {
    key: "createPreviewModal",
    value: function createPreviewModal() {
      // Ajouter une vraie feuille CSS pour le modal si elle n'existe pas
      if (!document.getElementById('pdf-preview-modal-styles')) {
        var styleSheet = document.createElement('style');
        styleSheet.id = 'pdf-preview-modal-styles';
        styleSheet.textContent = "\n                #pdf-preview-modal {\n                    position: fixed !important;\n                    top: 0 !important;\n                    left: 0 !important;\n                    width: 100% !important;\n                    height: 100% !important;\n                    background-color: rgba(0,0,0,0.8) !important;\n                    display: none !important;\n                    z-index: 99999 !important;\n                    align-items: center !important;\n                    justify-content: center !important;\n                    flex-direction: column !important;\n                    visibility: visible !important;\n                    gap: 0 !important;\n                    padding: 0 !important;\n                    margin: 0 !important;\n                }\n                \n                #pdf-preview-modal.visible {\n                    display: flex !important;\n                }\n                \n                #pdf-preview-modal-wrapper {\n                    background: white !important;\n                    border-radius: 8px !important;\n                    padding: 20px !important;\n                    max-width: 90vw !important;\n                    max-height: 90vh !important;\n                    overflow-y: auto !important;\n                    box-shadow: 0 10px 40px rgba(0,0,0,0.3) !important;\n                    flex-shrink: 0 !important;\n                    min-width: 300px !important;\n                    position: relative !important;\n                    width: 500px !important;\n                    align-self: center !important;\n                }\n            ";
        document.head.appendChild(styleSheet);
      }
      var modal = document.createElement('div');
      modal.id = 'pdf-preview-modal';

      // Wrapper blanc centré
      var wrapper = document.createElement('div');
      wrapper.id = 'pdf-preview-modal-wrapper';

      // Header avec titre et bouton fermer
      var header = document.createElement('div');
      header.style.cssText = "\n            display: flex;\n            justify-content: space-between;\n            align-items: center;\n            margin-bottom: 15px;\n        ";
      var title = document.createElement('h3');
      title.id = 'pdf-preview-title';
      title.textContent = 'Aperçu PDF';
      title.style.cssText = 'margin: 0; color: #1d2327;';
      var closeBtn = document.createElement('button');
      closeBtn.id = 'pdf-preview-close';
      closeBtn.textContent = '×';
      closeBtn.style.cssText = "\n            background: none;\n            border: none;\n            font-size: 24px;\n            cursor: pointer;\n            color: #666;\n        ";
      header.appendChild(title);
      header.appendChild(closeBtn);

      // Actions container
      var actions = document.createElement('div');
      actions.id = 'pdf-preview-actions';
      actions.style.cssText = 'margin-bottom: 15px;';

      // Image container
      var img = document.createElement('img');
      img.id = 'pdf-preview-image';
      img.alt = 'Aperçu PDF';
      img.style.cssText = 'max-width: 100%; height: auto; border: 1px solid #ddd;';
      wrapper.appendChild(header);
      wrapper.appendChild(actions);
      wrapper.appendChild(img);
      modal.appendChild(wrapper);

      // Gestionnaire de fermeture
      closeBtn.addEventListener('click', function () {
        modal.classList.remove('visible');
      });

      // Fermeture en cliquant en dehors
      modal.addEventListener('click', function (e) {
        if (e.target === modal) {
          modal.classList.remove('visible');
        }
      });
      return modal;
    }

    /**
     * Ajoute les boutons d'action à l'aperçu
     */
  }, {
    key: "addPreviewActions",
    value: function addPreviewActions(modal, imageUrl, context) {
      var _this = this;
      var actionsContainer = modal.querySelector('#pdf-preview-actions');
      actionsContainer.innerHTML = '';

      // Bouton de téléchargement
      var downloadBtn = document.createElement('button');
      downloadBtn.textContent = '📥 Télécharger';
      downloadBtn.style.cssText = "\n            background: #007cba;\n            color: white;\n            border: none;\n            padding: 8px 16px;\n            border-radius: 4px;\n            cursor: pointer;\n            margin-right: 10px;\n        ";
      downloadBtn.addEventListener('click', function () {
        _this.downloadPreview(imageUrl);
      });

      // Bouton d'impression
      var printBtn = document.createElement('button');
      printBtn.textContent = '🖨️ Imprimer';
      printBtn.style.cssText = "\n            background: #46b450;\n            color: white;\n            border: none;\n            padding: 8px 16px;\n            border-radius: 4px;\n            cursor: pointer;\n            margin-right: 10px;\n        ";
      printBtn.addEventListener('click', function () {
        _this.printPreview(imageUrl);
      });

      // Bouton de régénération (pour metabox seulement)
      if (context === 'metabox') {
        var regenerateBtn = document.createElement('button');
        regenerateBtn.textContent = '🔄 Régénérer';
        regenerateBtn.style.cssText = "\n                background: #f56e28;\n                color: white;\n                border: none;\n                padding: 8px 16px;\n                border-radius: 4px;\n                cursor: pointer;\n            ";
        regenerateBtn.addEventListener('click', function () {
          // Cette fonction devra être appelée depuis le contexte parent
          if (typeof window.regenerateOrderPreview === 'function') {
            window.regenerateOrderPreview();
          }
        });
        actionsContainer.appendChild(regenerateBtn);
      }
      actionsContainer.appendChild(downloadBtn);
      actionsContainer.appendChild(printBtn);
    }

    /**
     * Télécharge l'aperçu
     */
  }, {
    key: "downloadPreview",
    value: function downloadPreview(imageUrl) {
      var link = document.createElement('a');
      link.href = imageUrl;
      link.download = "pdf-preview-".concat(Date.now(), ".png");
      link.style.display = 'none';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      debugLog('📥 Téléchargement démarré:', imageUrl);
    }

    /**
     * Imprime l'aperçu
     */
  }, {
    key: "printPreview",
    value: function printPreview(imageUrl) {
      var printWindow = window.open('', '_blank');
      printWindow.document.write("\n            <html>\n                <head>\n                    <title>Aper\xE7u PDF</title>\n                    <style>\n                        body { margin: 0; padding: 20px; text-align: center; }\n                        img { max-width: 100%; height: auto; }\n                        @media print {\n                            body { margin: 0; }\n                            img { max-width: 100%; height: auto; }\n                        }\n                    </style>\n                </head>\n                <body>\n                    <img src=\"".concat(imageUrl, "\" alt=\"Aper\xE7u PDF\" onload=\"window.print(); window.close();\" />\n                </body>\n            </html>\n        "));
      printWindow.document.close();
      debugLog('🖨️ Impression démarrée');
    }

    /**
     * Affiche l'indicateur de chargement
     */
  }, {
    key: "showLoadingIndicator",
    value: function showLoadingIndicator() {
      var loader = document.getElementById('pdf-preview-loader');
      if (!loader) {
        loader = document.createElement('div');
        loader.id = 'pdf-preview-loader';
        loader.style.cssText = "\n                position: fixed;\n                top: 50%;\n                left: 50%;\n                transform: translate(-50%, -50%);\n                background: rgba(255,255,255,0.9);\n                border: 1px solid #ccc;\n                border-radius: 8px;\n                padding: 20px;\n                z-index: 10000;\n                display: none;\n                text-align: center;\n            ";
        loader.innerHTML = "\n                <div style=\"border: 4px solid #f3f3f3; border-top: 4px solid #007cba; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 10px;\"></div>\n                <div>G\xE9n\xE9ration de l'aper\xE7u...</div>\n                <style>\n                    @keyframes spin {\n                        0% { transform: rotate(0deg); }\n                        100% { transform: rotate(360deg); }\n                    }\n                </style>\n            ";
        document.body.appendChild(loader);
      }
      loader.style.display = 'block';
    }

    /**
     * Cache l'indicateur de chargement
     */
  }, {
    key: "hideLoadingIndicator",
    value: function hideLoadingIndicator() {
      var loader = document.getElementById('pdf-preview-loader');
      if (loader) {
        loader.style.display = 'none';
      }
    }

    /**
     * Affiche un message d'erreur
     */
  }, {
    key: "showError",
    value: function showError(message) {
      // Notification system removed - log to console instead
      debugError('PDF Preview Error:', message);
    }
  }]);
}(); // Initialisation globale
window.pdfPreviewAPI = new PDFPreviewAPI();

// Fonctions d'aide pour une utilisation facile
window.generateEditorPreview = function (templateData, options) {
  return window.pdfPreviewAPI.generateEditorPreview(templateData, options);
};
window.generateOrderPreview = function (templateData, orderId, options) {
  return window.pdfPreviewAPI.generateOrderPreview(templateData, orderId, options);
};
debugLog('🎯 API Preview 1.4 initialisée et prête à l\'emploi !');
debugLog('📖 Utilisation:');
debugLog('   - generateEditorPreview(templateData)');
debugLog('   - generateOrderPreview(templateData, orderId)');

/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ var __webpack_exports__ = (__webpack_exec__("./assets/js/pdf-preview-api-client.js"));
/******/ return __webpack_exports__;
/******/ }
]);
});
//# sourceMappingURL=pdf-preview-api-client.bundle.js.map