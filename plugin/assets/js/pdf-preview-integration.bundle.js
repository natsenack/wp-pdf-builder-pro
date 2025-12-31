"use strict";
(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["pdfBuilderReact"] = factory();
	else
		root["pdfBuilderReact"] = factory();
})(self, () => {
return (self["webpackChunkpdfBuilderReact"] = self["webpackChunkpdfBuilderReact"] || []).push([[865],{

/***/ 536:
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);


var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");
var _regenerator = _interopRequireDefault(require("@babel/runtime/regenerator"));
var _asyncToGenerator2 = _interopRequireDefault(require("@babel/runtime/helpers/asyncToGenerator"));
var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));
var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));
/* eslint-disable no-undef */
/**
 * Exemple d'intégration de l'API Preview 1.4 dans l'interface
 * À intégrer dans votre éditeur ou metabox WooCommerce
 */

// Fonctions de debug conditionnel - ACTIVÉES pour le système d'aperçu
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

// ==========================================
// INTÉGRATION DANS L'ÉDITEUR (Canvas)
// ==========================================
var PDFEditorPreviewIntegration = /*#__PURE__*/function () {
  function PDFEditorPreviewIntegration(canvasEditor) {
    (0, _classCallCheck2["default"])(this, PDFEditorPreviewIntegration);
    this.canvasEditor = canvasEditor;
    this.previewBtn = null;
    this.init();
  }
  return (0, _createClass2["default"])(PDFEditorPreviewIntegration, [{
    key: "init",
    value: function init() {
      debugLog('🎨 Initialisation intégration éditeur...');
      this.createPreviewButton();
      this.bindEvents();
      debugLog('✅ Intégration éditeur initialisée');
    }
  }, {
    key: "createPreviewButton",
    value: function createPreviewButton() {
      debugLog('🔘 Création bouton aperçu éditeur...');

      // Créer le bouton d'aperçu dans la barre d'outils
      this.previewBtn = document.createElement('button');
      this.previewBtn.id = 'pdf-editor-preview-btn';
      this.previewBtn.innerHTML = '👁️ Aperçu';
      this.previewBtn.title = 'Générer un aperçu PDF';
      this.previewBtn.style.cssText = "\n            background: #007cba;\n            color: white;\n            border: none;\n            padding: 8px 16px;\n            border-radius: 4px;\n            cursor: pointer;\n            font-size: 14px;\n            margin-left: 10px;\n        ";

      // L'ajouter à la barre d'outils existante
      var toolbar = document.querySelector('.pdf-editor-toolbar') || document.querySelector('#pdf-editor-toolbar') || document.querySelector('.toolbar');
      if (toolbar) {
        toolbar.appendChild(this.previewBtn);
        debugLog('✅ Bouton aperçu ajouté à la toolbar');
      } else {
        // Fallback: l'ajouter au body avec position fixe
        this.previewBtn.style.position = 'fixed';
        this.previewBtn.style.top = '10px';
        this.previewBtn.style.right = '10px';
        this.previewBtn.style.zIndex = '1000';
        document.body.appendChild(this.previewBtn);
        debugLog('⚠️ Toolbar non trouvée, bouton ajouté en position fixe');
      }
    }
  }, {
    key: "bindEvents",
    value: function bindEvents() {
      var _this = this;
      if (this.previewBtn) {
        this.previewBtn.addEventListener('click', function () {
          _this.generatePreview();
        });
      }

      // Raccourci clavier Ctrl+P (ou Cmd+P sur Mac)
      document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
          e.preventDefault();
          _this.generatePreview();
        }
      });
    }
  }, {
    key: "generatePreview",
    value: function () {
      var _generatePreview = (0, _asyncToGenerator2["default"])(/*#__PURE__*/_regenerator["default"].mark(function _callee() {
        var templateData, result, _t;
        return _regenerator["default"].wrap(function (_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              debugLog('🚀 Démarrage génération aperçu éditeur...');
              _context.prev = 1;
              // Récupérer les données du template depuis l'éditeur
              templateData = this.getTemplateData();
              if (templateData) {
                _context.next = 2;
                break;
              }
              debugWarn('⚠️ Aucune donnée de template trouvée');
              alert('Aucune donnée de template trouvée. Veuillez créer un template d\'abord.');
              return _context.abrupt("return");
            case 2:
              debugLog('📄 Données template récupérées:', templateData);

              // Générer l'aperçu
              _context.next = 3;
              return window.generateEditorPreview(templateData, {
                quality: 150,
                format: 'png'
              });
            case 3:
              result = _context.sent;
              if (result) {
                debugLog('✅ Aperçu éditeur généré avec succès');
              }
              _context.next = 5;
              break;
            case 4:
              _context.prev = 4;
              _t = _context["catch"](1);
              debugError('❌ Erreur génération aperçu éditeur:', _t);
              alert('Erreur lors de la génération de l\'aperçu. Vérifiez la console pour plus de détails.');
            case 5:
            case "end":
              return _context.stop();
          }
        }, _callee, this, [[1, 4]]);
      }));
      function generatePreview() {
        return _generatePreview.apply(this, arguments);
      }
      return generatePreview;
    }()
  }, {
    key: "getTemplateData",
    value: function getTemplateData() {
      debugLog('🔍 Recherche données template...');

      // Adapter selon votre structure de données d'éditeur
      if (this.canvasEditor && typeof this.canvasEditor.getTemplateData === 'function') {
        var data = this.canvasEditor.getTemplateData();
        debugLog('✅ Données récupérées depuis canvasEditor');
        return data;
      }

      // Fallback: chercher dans les variables globales uniquement (pas de cache)
      if (window.pdfEditorTemplate) {
        debugLog('✅ Données récupérées depuis window.pdfEditorTemplate');
        return window.pdfEditorTemplate;
      }

      // Template par défaut pour les tests
      return {
        template: {
          elements: [{
            type: 'text',
            content: 'APERÇU PDF BUILDER PRO',
            x: 50,
            y: 50,
            width: 300,
            height: 40,
            fontSize: 18,
            fontFamily: 'Arial',
            color: '#000000',
            textAlign: 'center'
          }, {
            type: 'text',
            content: 'Template de démonstration',
            x: 50,
            y: 100,
            width: 300,
            height: 30,
            fontSize: 14,
            color: '#666666'
          }]
        }
      };
    }
  }]);
}(); // ==========================================
// INTÉGRATION DANS LA METABOX WOOCOMMERCE
// ==========================================
var PDFMetaboxPreviewIntegration = /*#__PURE__*/function () {
  function PDFMetaboxPreviewIntegration(metaboxContainer) {
    (0, _classCallCheck2["default"])(this, PDFMetaboxPreviewIntegration);
    this.metaboxContainer = metaboxContainer;
    this.orderId = this.getOrderId();
    this.previewBtn = null;
    this.init();
  }
  return (0, _createClass2["default"])(PDFMetaboxPreviewIntegration, [{
    key: "init",
    value: function init() {
      debugLog('🛒 Initialisation intégration metabox...');
      this.createPreviewButtons();
      this.bindEvents();
      debugLog('✅ Intégration metabox initialisée');
    }
  }, {
    key: "createPreviewButtons",
    value: function createPreviewButtons() {
      debugLog('🔘 Création boutons aperçu metabox...');

      // Créer un conteneur pour les boutons d'aperçu
      var buttonContainer = document.createElement('div');
      buttonContainer.id = 'pdf-metabox-preview-buttons';
      buttonContainer.style.cssText = "\n            margin: 15px 0;\n            padding: 15px;\n            background: #f8f9fa;\n            border: 1px solid #dee2e6;\n            border-radius: 4px;\n        ";
      buttonContainer.innerHTML = "\n            <h4 style=\"margin: 0 0 10px 0; color: #495057;\">\uD83D\uDCC4 Aper\xE7u PDF</h4>\n            <p style=\"margin: 0 0 15px 0; color: #6c757d; font-size: 13px;\">\n                G\xE9n\xE9rez un aper\xE7u du PDF avec les donn\xE9es r\xE9elles de cette commande.\n            </p>\n            <div style=\"display: flex; gap: 10px; flex-wrap: wrap;\">\n                <button id=\"pdf-metabox-preview-btn\" class=\"button button-secondary\">\n                    \uD83D\uDC41\uFE0F Aper\xE7u Image\n                </button>\n                <button id=\"pdf-metabox-generate-btn\" class=\"button button-primary\">\n                    \uD83D\uDCC4 G\xE9n\xE9rer PDF\n                </button>\n            </div>\n        ";

      // L'insérer dans la metabox
      if (this.metaboxContainer) {
        this.metaboxContainer.appendChild(buttonContainer);
      }
      this.previewBtn = document.getElementById('pdf-metabox-preview-btn');
    }
  }, {
    key: "bindEvents",
    value: function bindEvents() {
      var _this2 = this;
      if (this.previewBtn) {
        this.previewBtn.addEventListener('click', function () {
          _this2.generatePreview();
        });
      }

      // Bouton de régénération globale
      window.regenerateOrderPreview = function () {
        _this2.generatePreview();
      };
    }
  }, {
    key: "generatePreview",
    value: function () {
      var _generatePreview2 = (0, _asyncToGenerator2["default"])(/*#__PURE__*/_regenerator["default"].mark(function _callee2() {
        var templateData, result, _t2;
        return _regenerator["default"].wrap(function (_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              debugLog('🚀 Démarrage génération aperçu commande...');
              _context2.prev = 1;
              if (this.orderId) {
                _context2.next = 2;
                break;
              }
              debugError('❌ ID de commande non trouvé');
              alert('ID de commande non trouvé.');
              return _context2.abrupt("return");
            case 2:
              debugLog('📦 ID commande:', this.orderId);

              // Récupérer les données du template depuis la metabox
              templateData = this.getTemplateData();
              if (templateData) {
                _context2.next = 3;
                break;
              }
              debugWarn('⚠️ Aucune donnée de template trouvée');
              alert('Aucune donnée de template trouvée. Veuillez sélectionner un template.');
              return _context2.abrupt("return");
            case 3:
              debugLog('📄 Données template récupérées:', templateData);

              // Générer l'aperçu
              _context2.next = 4;
              return window.generateOrderPreview(templateData, this.orderId, {
                quality: 150,
                format: 'png'
              });
            case 4:
              result = _context2.sent;
              if (result) {
                debugLog('✅ Aperçu commande généré avec succès');
              }
              _context2.next = 6;
              break;
            case 5:
              _context2.prev = 5;
              _t2 = _context2["catch"](1);
              debugError('❌ Erreur génération aperçu commande:', _t2);
              alert('Erreur lors de la génération de l\'aperçu. Vérifiez la console pour plus de détails.');
            case 6:
            case "end":
              return _context2.stop();
          }
        }, _callee2, this, [[1, 5]]);
      }));
      function generatePreview() {
        return _generatePreview2.apply(this, arguments);
      }
      return generatePreview;
    }()
  }, {
    key: "getOrderId",
    value: function getOrderId() {
      // Essayer différentes méthodes pour récupérer l'ID de commande

      // Depuis l'URL
      var urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('post')) {
        return parseInt(urlParams.get('post'));
      }

      // Depuis les variables globales WordPress
      if (window.wpApiSettings && window.wpApiSettings.postId) {
        return window.wpApiSettings.postId;
      }

      // Depuis un élément caché dans la page
      var orderIdElement = document.getElementById('pdf-order-id') || document.querySelector('[data-order-id]');
      if (orderIdElement) {
        return parseInt(orderIdElement.dataset.orderId || orderIdElement.value);
      }

      // Depuis le titre de la page (parsing du DOM)
      var titleElement = document.querySelector('.wp-heading-inline') || document.querySelector('h1');
      if (titleElement) {
        var titleMatch = titleElement.textContent.match(/#(\d+)/);
        if (titleMatch) {
          return parseInt(titleMatch[1]);
        }
      }
      debugWarn('⚠️ ID de commande non trouvé automatiquement');
      return null;
    }
  }, {
    key: "getTemplateData",
    value: function getTemplateData() {
      // Récupérer les données du template sélectionné

      // Depuis un champ caché
      var templateDataElement = document.getElementById('pdf-template-data') || document.querySelector('[data-template-data]');
      if (templateDataElement) {
        try {
          return JSON.parse(templateDataElement.value || templateDataElement.dataset.templateData);
        } catch (e) {
          debugWarn('Données template mal formatées:', e);
        }
      }

      // Depuis les variables globales
      if (window.pdfCurrentTemplate) {
        return window.pdfCurrentTemplate;
      }

      // Template par défaut avec variables WooCommerce
      return {
        template: {
          elements: [{
            type: 'text',
            content: 'FACTURE',
            x: 50,
            y: 30,
            width: 200,
            height: 40,
            fontSize: 24,
            fontWeight: 'bold',
            color: '#000000'
          }, {
            type: 'text',
            content: 'Commande #{{order_number}}',
            x: 50,
            y: 80,
            width: 200,
            height: 30,
            fontSize: 16,
            color: '#333333'
          }, {
            type: 'text',
            content: 'Client: {{customer_name}}',
            x: 50,
            y: 120,
            width: 200,
            height: 25,
            fontSize: 14,
            color: '#666666'
          }, {
            type: 'text',
            content: 'Total: {{order_total}} €',
            x: 50,
            y: 150,
            width: 200,
            height: 25,
            fontSize: 14,
            fontWeight: 'bold',
            color: '#000000'
          }]
        }
      };
    }
  }]);
}(); // ==========================================
// INITIALISATION AUTOMATIQUE
// ==========================================
document.addEventListener('DOMContentLoaded', function () {
  // Initialiser l'intégration éditeur si on est dans l'éditeur
  if (document.querySelector('#pdf-editor-canvas') || document.querySelector('.pdf-canvas-editor') || window.location.href.includes('pdf-builder-editor')) {
    debugLog('🎨 Initialisation intégration éditeur...');
    window.pdfEditorPreview = new PDFEditorPreviewIntegration(window.pdfCanvasEditor);
  }

  // Initialiser l'intégration metabox si on est dans une commande WooCommerce
  if (document.querySelector('.woocommerce-order-data') || document.querySelector('#woocommerce-order-data') || window.location.href.includes('post.php?post=') && window.location.href.includes('action=edit')) {
    // Attendre que la metabox soit chargée
    setTimeout(function () {
      var metabox = document.querySelector('#pdf-builder-metabox') || document.querySelector('.pdf-builder-metabox') || document.querySelector('.postbox');
      if (metabox) {
        debugLog('🛒 Initialisation intégration metabox...');
        window.pdfMetaboxPreview = new PDFMetaboxPreviewIntegration(metabox);
      }
    }, 1000);
  }
});

// ==========================================
// FONCTIONS GLOBALES D'AIDE
// ==========================================

/**
 * Génère un aperçu rapide (détection automatique du contexte)
 */
window.generateQuickPreview = /*#__PURE__*/(0, _asyncToGenerator2["default"])(/*#__PURE__*/_regenerator["default"].mark(function _callee3() {
  var templateData,
    orderId,
    isEditor,
    isMetabox,
    _window$pdfEditorPrev,
    data,
    _window$pdfMetaboxPre,
    _window$pdfMetaboxPre2,
    _data,
    id,
    _args3 = arguments,
    _t3;
  return _regenerator["default"].wrap(function (_context3) {
    while (1) switch (_context3.prev = _context3.next) {
      case 0:
        templateData = _args3.length > 0 && _args3[0] !== undefined ? _args3[0] : null;
        orderId = _args3.length > 1 && _args3[1] !== undefined ? _args3[1] : null;
        debugLog('⚡ Génération aperçu rapide démarrée...');
        _context3.prev = 1;
        // Détection automatique du contexte
        isEditor = document.querySelector('#pdf-editor-canvas') || document.querySelector('.pdf-canvas-editor') || window.location.href.includes('pdf-builder-editor');
        isMetabox = document.querySelector('.woocommerce-order-data') || document.querySelector('#woocommerce-order-data') || window.location.href.includes('post.php') && window.location.href.includes('action=edit');
        debugLog('🔍 Contexte détecté:', {
          isEditor: isEditor,
          isMetabox: isMetabox
        });
        if (!isEditor) {
          _context3.next = 3;
          break;
        }
        debugLog('🎨 Mode éditeur détecté');
        data = templateData || ((_window$pdfEditorPrev = window.pdfEditorPreview) === null || _window$pdfEditorPrev === void 0 ? void 0 : _window$pdfEditorPrev.getTemplateData());
        _context3.next = 2;
        return window.generateEditorPreview(data);
      case 2:
        return _context3.abrupt("return", _context3.sent);
      case 3:
        if (!isMetabox) {
          _context3.next = 5;
          break;
        }
        debugLog('🛒 Mode metabox détecté');
        _data = templateData || ((_window$pdfMetaboxPre = window.pdfMetaboxPreview) === null || _window$pdfMetaboxPre === void 0 ? void 0 : _window$pdfMetaboxPre.getTemplateData());
        id = orderId || ((_window$pdfMetaboxPre2 = window.pdfMetaboxPreview) === null || _window$pdfMetaboxPre2 === void 0 ? void 0 : _window$pdfMetaboxPre2.getOrderId());
        _context3.next = 4;
        return window.generateOrderPreview(_data, id);
      case 4:
        return _context3.abrupt("return", _context3.sent);
      case 5:
        debugWarn('⚠️ Contexte non reconnu pour l\'aperçu');
        return _context3.abrupt("return", null);
      case 6:
        _context3.prev = 6;
        _t3 = _context3["catch"](1);
        debugError('❌ Erreur génération aperçu rapide:', _t3);
        return _context3.abrupt("return", null);
      case 7:
      case "end":
        return _context3.stop();
    }
  }, _callee3, null, [[1, 6]]);
}));
debugLog('🚀 Intégrations API Preview 1.4 chargées !');
debugLog('💡 Raccourcis:');
debugLog('   - Ctrl+P (Cmd+P) : Aperçu rapide');
debugLog('   - generateQuickPreview() : Détection automatique du contexte');

// ==========================================
// CANVAS PREVIEW MANAGER POUR SETTINGS
// ==========================================

/**
 * Gestionnaire centralisé des previews canvas pour la page des paramètres
 */
window.CanvasPreviewManager = {
  /**
   * Met à jour les previews pour une catégorie donnée
   */
  updatePreviews: function updatePreviews(category) {
    debugLog('🔄 Mise à jour previews pour catégorie:', category);
    try {
      switch (category) {
        case 'dimensions':
          if (typeof updateDimensionsCardPreview === 'function') {
            updateDimensionsCardPreview();
          }
          break;
        case 'apparence':
          if (typeof updateApparenceCardPreview === 'function') {
            updateApparenceCardPreview();
          }
          break;
        case 'performance':
          if (typeof updatePerformanceCardPreview === 'function') {
            updatePerformanceCardPreview();
          }
          break;
        case 'autosave':
          if (typeof updateAutosaveCardPreview === 'function') {
            updateAutosaveCardPreview();
          }
          break;
        case 'zoom':
          if (typeof updateZoomCardPreview === 'function') {
            updateZoomCardPreview();
          }
          break;
        case 'grille':
          if (typeof updateGrilleCardPreview === 'function') {
            updateGrilleCardPreview();
          }
          break;
        case 'interactions':
          if (typeof updateInteractionsCardPreview === 'function') {
            updateInteractionsCardPreview();
          }
          break;
        case 'export':
          if (typeof updateExportCardPreview === 'function') {
            updateExportCardPreview();
          }
          break;
        case 'all':
          // Mettre à jour toutes les previews
          this.updatePreviews('dimensions');
          this.updatePreviews('apparence');
          this.updatePreviews('performance');
          this.updatePreviews('autosave');
          this.updatePreviews('zoom');
          this.updatePreviews('grille');
          this.updatePreviews('interactions');
          this.updatePreviews('export');
          break;
        default:
          debugWarn('⚠️ Catégorie inconnue:', category);
      }
    } catch (error) {
      debugError('❌ Erreur mise à jour preview:', error);
    }
  },
  /**
   * Récupère les valeurs actuelles d'une carte
   */
  getCardValues: function getCardValues(category) {
    debugLog('📊 Récupération valeurs pour carte:', category);
    try {
      var settings = window.pdfBuilderCanvasSettings || {};
      switch (category) {
        case 'performance':
          return {
            fps_target: settings.fps_target || 60,
            memory_limit_js: settings.memory_limit_js || 128,
            memory_limit_php: settings.memory_limit_php || 256
          };
        case 'apparence':
          return {
            canvas_bg_color: settings.canvas_background_color || '#ffffff',
            canvas_border_color: settings.border_color || '#cccccc',
            canvas_border_width: settings.border_width || 1,
            canvas_shadow_enabled: settings.shadow_enabled || false,
            canvas_container_bg_color: settings.container_background_color || '#f8f9fa'
          };
        case 'grille':
          return {
            grid_enabled: settings.show_grid || false,
            grid_size: settings.grid_size || 20,
            snap_to_grid: settings.snap_to_grid || false
          };
        case 'interactions':
          return {
            drag_enabled: settings.drag_enabled !== false,
            resize_enabled: settings.resize_enabled !== false,
            rotate_enabled: settings.rotate_enabled !== false,
            multi_select: settings.multi_select !== false,
            selection_mode: settings.selection_mode || 'rectangle',
            keyboard_shortcuts: settings.keyboard_shortcuts !== false
          };
        case 'export':
          return {
            canvas_export_format: settings.export_format || 'pdf',
            canvas_export_quality: settings.export_quality || 90,
            canvas_export_transparent: settings.export_transparent || false
          };
        case 'zoom':
          return {
            canvas_zoom_min: settings.min_zoom || 10,
            canvas_zoom_max: settings.max_zoom || 500,
            canvas_zoom_default: settings.default_zoom || 100,
            canvas_zoom_step: settings.zoom_step || 25
          };
        case 'autosave':
          return {
            canvas_autosave_enabled: settings.autosave_enabled !== false,
            canvas_autosave_interval: settings.autosave_interval || 5,
            canvas_history_max: settings.versions_limit || 10
          };
        default:
          debugWarn('⚠️ Catégorie inconnue pour getCardValues:', category);
          return {};
      }
    } catch (error) {
      debugError('❌ Erreur récupération valeurs carte:', error);
      return {};
    }
  },
  /**
   * Récupère un élément DOM d'une carte
   */
  getCardElement: function getCardElement(category, selector) {
    debugLog('🔍 Recherche élément pour carte:', category, 'sélecteur:', selector);
    try {
      var card = document.querySelector(".canvas-card[data-category=\"".concat(category, "\"]"));
      if (!card) {
        debugWarn('⚠️ Carte non trouvée:', category);
        return null;
      }
      return card.querySelector(selector);
    } catch (error) {
      debugError('❌ Erreur recherche élément:', error);
      return null;
    }
  },
  /**
   * Met à jour une propriété d'un élément
   */
  updateElement: function updateElement(element, property, value) {
    if (!element) {
      debugWarn('⚠️ Élément null passé à updateElement');
      return;
    }
    try {
      debugLog('🔧 Mise à jour élément:', property, '=', value);

      // Gérer les propriétés imbriquées (ex: style.backgroundColor)
      if (property.includes('.')) {
        var parts = property.split('.');
        var obj = element;
        for (var i = 0; i < parts.length - 1; i++) {
          obj = obj[parts[i]];
          if (!obj) {
            debugWarn('⚠️ Propriété parent non trouvée:', parts.slice(0, i + 1).join('.'));
            return;
          }
        }
        obj[parts[parts.length - 1]] = value;
      } else {
        element[property] = value;
      }
    } catch (error) {
      debugError('❌ Erreur mise à jour élément:', error);
    }
  },
  /**
   * Met à jour la prévisualisation de la carte export
   */
  updateExportCardPreview: function updateExportCardPreview() {
    try {
      var values = this.getCardValues('export');
      var exportQuality = values.export_quality;
      var qualityFill = this.getCardElement('export', '.quality-fill');
      var qualityText = this.getCardElement('export', '.quality-text');
      this.updateElement(qualityFill, 'style.width', "".concat(exportQuality, "%"));
      this.updateElement(qualityText, 'textContent', "".concat(exportQuality, "%"));
      debugLog('✅ Export preview updated:', exportQuality);
    } catch (error) {
      debugError('❌ Error updating export preview:', error);
    }
  },
  /**
   * Met à jour la prévisualisation de la carte grille
   */
  updateGrilleCardPreview: function updateGrilleCardPreview() {
    try {
      var values = this.getCardValues('grille');
      var gridEnabled = values.show_grid,
        snapToGrid = values.snap_to_grid,
        showGuides = values.show_guides;
      var gridContainer = this.getCardElement('grille', '.grid-preview-container');
      if (!gridContainer) return;

      // Activer/désactiver la grille
      gridContainer.classList.toggle('grid-enabled', gridEnabled);
      gridContainer.classList.toggle('grid-disabled', !gridEnabled);

      // Afficher/cacher les guides
      var guideLines = gridContainer.querySelectorAll('.guide-line');
      guideLines.forEach(function (guide) {
        return guide.classList.toggle('active', showGuides);
      });

      // Mettre à jour l'indicateur de snap
      var snapIndicator = gridContainer.querySelector('.snap-indicator');
      if (snapIndicator) {
        var isActive = snapToGrid && gridEnabled;
        snapIndicator.textContent = isActive ? '🔗 Snap activé' : '🔗 Snap désactivé';
        snapIndicator.style.color = isActive ? '#28a745' : '#6c757d';
      }
      debugLog('✅ Grille preview updated:', {
        gridEnabled: gridEnabled,
        snapToGrid: snapToGrid,
        showGuides: showGuides
      });
    } catch (error) {
      debugError('❌ Error updating grille preview:', error);
    }
  },
  /**
   * Initialise les mises à jour en temps réel pour une catégorie
   */
  initializeRealTimeUpdates: function initializeRealTimeUpdates(modal) {
    if (!modal) return;
    debugLog('⚡ Initialisation mises à jour temps réel pour modal:', modal.getAttribute('data-category'));

    // Les mises à jour temps réel sont gérées dans settings-main.php
    // Cette méthode est appelée pour compatibilité
  }
};
debugLog('✅ CanvasPreviewManager initialisé');

/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ var __webpack_exports__ = (__webpack_exec__(536));
/******/ return __webpack_exports__;
/******/ }
]);
});
//# sourceMappingURL=pdf-preview-integration.bundle.js.map