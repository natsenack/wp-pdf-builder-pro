(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["PDFBuilder"] = factory();
	else
		root["PDFBuilder"] = factory();
})(self, () => {
return /******/ (() => { // webpackBootstrap
/******/ 	"use strict";
var __webpack_exports__ = {};


var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");
Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = exports.PDFCanvasVanilla = void 0;
var _regenerator = _interopRequireDefault(require("@babel/runtime/regenerator"));
var _slicedToArray2 = _interopRequireDefault(require("@babel/runtime/helpers/slicedToArray"));
var _asyncToGenerator2 = _interopRequireDefault(require("@babel/runtime/helpers/asyncToGenerator"));
var _defineProperty2 = _interopRequireDefault(require("@babel/runtime/helpers/defineProperty"));
var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));
var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));
var _pdfCanvasElements = require("./pdf-canvas-elements.js");
var _pdfCanvasWoocommerce = require("./pdf-canvas-woocommerce.js");
var _pdfCanvasCustomization = require("./pdf-canvas-customization.js");
var _pdfCanvasRenderer = require("./pdf-canvas-renderer.js");
var _pdfCanvasEvents = require("./pdf-canvas-events.js");
var _pdfCanvasRenderUtils = require("./pdf-canvas-render-utils.js");
var _pdfCanvasSelection = require("./pdf-canvas-selection.js");
var _pdfCanvasProperties = require("./pdf-canvas-properties.js");
var _pdfCanvasLayers = require("./pdf-canvas-layers.js");
var _pdfCanvasExport = require("./pdf-canvas-export.js");
var _pdfCanvasOptimizer = require("./pdf-canvas-optimizer.js");
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { (0, _defineProperty2["default"])(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; } /**
 * PDF Canvas Vanilla - Classe principale pour le système Vanilla JS
 * Remplace les composants React avec une implémentation Canvas HTML5
 * Intègre les utilitaires migrés pour la gestion des éléments
 */
var PDFCanvasVanilla = exports.PDFCanvasVanilla = /*#__PURE__*/function () {
  function PDFCanvasVanilla(containerId) {
    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
    (0, _classCallCheck2["default"])(this, PDFCanvasVanilla);
    this.containerId = containerId;
    this.options = _objectSpread({
      width: options.width || 800,
      height: options.height || 600,
      backgroundColor: options.backgroundColor || '#ffffff',
      gridSize: options.gridSize || 20,
      showGrid: options.showGrid !== false,
      zoom: options.zoom || 1
    }, options);

    // État du canvas
    this.canvas = null;
    this.ctx = null;
    this.elements = new Map();
    this.selectedElement = null;
    this.dragState = null;
    this.isInitialized = false;

    // Gestionnaires d'événements
    this.eventListeners = new Map();

    // Services intégrés
    this.wooCommerceManager = _pdfCanvasWoocommerce.wooCommerceElementsManager;
    this.customizationService = _pdfCanvasCustomization.elementCustomizationService;

    // Gestionnaires spécialisés
    this.renderer = new _pdfCanvasRenderer.PDFCanvasRenderer(this);
    this.eventManager = new _pdfCanvasEvents.PDFCanvasEventManager(this);
    this.selectionManager = new _pdfCanvasSelection.PDFCanvasSelectionManager(this);
    this.propertiesManager = new _pdfCanvasProperties.PDFCanvasPropertiesManager(this);
    this.layersManager = new _pdfCanvasLayers.PDFCanvasLayersManager(this);
    this.exportManager = new _pdfCanvasExport.PDFCanvasExportManager(this);
    this.performanceOptimizer = new _pdfCanvasOptimizer.PDFCanvasPerformanceOptimizer(this);

    // État d'interaction
    this.mode = 'select'; // select, draw, text, etc.
    this.tool = null;

    // Historique pour undo/redo
    this.history = [];
    this.historyIndex = -1;
  }

  /**
   * Initialise le canvas et les gestionnaires d'événements
   */
  return (0, _createClass2["default"])(PDFCanvasVanilla, [{
    key: "init",
    value: (function () {
      var _init = (0, _asyncToGenerator2["default"])(/*#__PURE__*/_regenerator["default"].mark(function _callee() {
        var _t;
        return _regenerator["default"].wrap(function (_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              _context.prev = 0;
              // Créer le canvas
              this.createCanvas();

              // Configurer le contexte
              this.setupContext();

              // Attacher les gestionnaires d'événements
              this.attachEventListeners();

              // Charger les données WooCommerce si nécessaire
              _context.next = 1;
              return this.loadInitialData();
            case 1:
              // Premier rendu
              this.render();
              this.isInitialized = true;
              console.log('PDFCanvasVanilla initialized successfully');
              _context.next = 3;
              break;
            case 2:
              _context.prev = 2;
              _t = _context["catch"](0);
              console.error('Failed to initialize PDFCanvasVanilla:', _t);
              throw _t;
            case 3:
            case "end":
              return _context.stop();
          }
        }, _callee, this, [[0, 2]]);
      }));
      function init() {
        return _init.apply(this, arguments);
      }
      return init;
    }()
    /**
     * Crée l'élément canvas dans le conteneur
     */
    )
  }, {
    key: "createCanvas",
    value: function createCanvas() {
      var container = document.getElementById(this.containerId);
      if (!container) {
        throw new Error("Container with id \"".concat(this.containerId, "\" not found"));
      }

      // Vider le conteneur
      container.innerHTML = '';

      // Créer le canvas
      this.canvas = document.createElement('canvas');
      this.canvas.width = this.options.width;
      this.canvas.height = this.options.height;
      this.canvas.style.border = '1px solid #ddd';
      this.canvas.style.cursor = 'default';
      this.canvas.style.backgroundColor = this.options.backgroundColor;

      // Ajouter au conteneur
      container.appendChild(this.canvas);

      // Obtenir le contexte
      this.ctx = this.canvas.getContext('2d');
      if (!this.ctx) {
        throw new Error('Failed to get 2D context from canvas');
      }
    }

    /**
     * Configure le contexte de rendu
     */
  }, {
    key: "setupContext",
    value: function setupContext() {
      // Configuration de base
      this.ctx.imageSmoothingEnabled = true;
      this.ctx.imageSmoothingQuality = 'high';

      // Configuration du texte
      this.ctx.textBaseline = 'top';
      this.ctx.font = '14px Arial, sans-serif';
    }

    /**
     * Attache les gestionnaires d'événements DOM
     */
  }, {
    key: "attachEventListeners",
    value: function attachEventListeners() {
      // Gestionnaires de souris
      this.canvas.addEventListener('mousedown', this.handleMouseDown.bind(this));
      this.canvas.addEventListener('mousemove', this.handleMouseMove.bind(this));
      this.canvas.addEventListener('mouseup', this.handleMouseUp.bind(this));
      this.canvas.addEventListener('wheel', this.handleWheel.bind(this));

      // Gestionnaires de clavier
      document.addEventListener('keydown', this.handleKeyDown.bind(this));
      document.addEventListener('keyup', this.handleKeyUp.bind(this));

      // Gestionnaire de redimensionnement
      window.addEventListener('resize', this.handleResize.bind(this));
    }

    /**
     * Charge les données initiales
     */
  }, {
    key: "loadInitialData",
    value: (function () {
      var _loadInitialData = (0, _asyncToGenerator2["default"])(/*#__PURE__*/_regenerator["default"].mark(function _callee2() {
        return _regenerator["default"].wrap(function (_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              // Charger les données WooCommerce en mode test
              this.wooCommerceManager.setTestMode(true);
              _context2.next = 1;
              return this.wooCommerceManager.loadWooCommerceData();
            case 1:
            case "end":
              return _context2.stop();
          }
        }, _callee2, this);
      }));
      function loadInitialData() {
        return _loadInitialData.apply(this, arguments);
      }
      return loadInitialData;
    }()
    /**
     * Gestionnaire d'événement mouse down
     */
    )
  }, {
    key: "handleMouseDown",
    value: function handleMouseDown(event) {
      var point = this.getMousePosition(event);
      switch (this.mode) {
        case 'select':
          this.handleSelectMode(point);
          break;
        case 'draw':
          this.handleDrawMode(point);
          break;
        case 'text':
          this.handleTextMode(point);
          break;
      }
    }

    /**
     * Gestionnaire d'événement mouse move
     */
  }, {
    key: "handleMouseMove",
    value: function handleMouseMove(event) {
      var point = this.getMousePosition(event);
      if (this.dragState) {
        this.handleDrag(point);
      } else {
        this.handleHover(point);
      }
    }

    /**
     * Gestionnaire d'événement mouse up
     */
  }, {
    key: "handleMouseUp",
    value: function handleMouseUp(event) {
      if (this.dragState) {
        this.endDrag();
      }
    }

    /**
     * Gestionnaire de roulette de souris (zoom)
     */
  }, {
    key: "handleWheel",
    value: function handleWheel(event) {
      event.preventDefault();
      var delta = event.deltaY > 0 ? 0.9 : 1.1;
      this.setZoom(this.options.zoom * delta);
    }

    /**
     * Gestionnaire de touches clavier
     */
  }, {
    key: "handleKeyDown",
    value: function handleKeyDown(event) {
      switch (event.key) {
        case 'Delete':
        case 'Backspace':
          if (this.selectedElement) {
            this.deleteElement(this.selectedElement.id);
          }
          break;
        case 'Escape':
          this.deselectElement();
          break;
        case 'z':
          if (event.ctrlKey || event.metaKey) {
            event.preventDefault();
            this.undo();
          }
          break;
        case 'y':
          if (event.ctrlKey || event.metaKey) {
            event.preventDefault();
            this.redo();
          }
          break;
      }
    }

    /**
     * Gestionnaire de relâchement de touches
     */
  }, {
    key: "handleKeyUp",
    value: function handleKeyUp(event) {
      // Gérer les relâchements si nécessaire
    }

    /**
     * Gestionnaire de redimensionnement de fenêtre
     */
  }, {
    key: "handleResize",
    value: function handleResize() {
      // Ajuster la taille du canvas si nécessaire
      this.render();
    }

    /**
     * Obtient la position de la souris relative au canvas
     */
  }, {
    key: "getMousePosition",
    value: function getMousePosition(event) {
      var rect = this.canvas.getBoundingClientRect();
      return {
        x: (event.clientX - rect.left) / this.options.zoom,
        y: (event.clientY - rect.top) / this.options.zoom
      };
    }

    /**
     * Gère le mode sélection
     */
  }, {
    key: "handleSelectMode",
    value: function handleSelectMode(point) {
      var element = this.getElementAtPoint(point);
      if (element) {
        this.selectElement(element.id);
        this.startDrag(point);
      } else {
        this.deselectElement();
      }
    }

    /**
     * Gère le mode dessin
     */
  }, {
    key: "handleDrawMode",
    value: function handleDrawMode(point) {
      // Implémentation du mode dessin
      console.log('Draw mode at:', point);
    }

    /**
     * Gère le mode texte
     */
  }, {
    key: "handleTextMode",
    value: function handleTextMode(point) {
      // Implémentation du mode texte
      console.log('Text mode at:', point);
    }

    /**
     * Démarre un glisser-déposer
     */
  }, {
    key: "startDrag",
    value: function startDrag(point) {
      if (!this.selectedElement) return;
      this.dragState = {
        startPoint: point,
        elementStartPos: {
          x: this.selectedElement.properties.x,
          y: this.selectedElement.properties.y
        }
      };
    }

    /**
     * Gère le glisser-déposer
     */
  }, {
    key: "handleDrag",
    value: function handleDrag(point) {
      if (!this.dragState || !this.selectedElement) return;
      var deltaX = point.x - this.dragState.startPoint.x;
      var deltaY = point.y - this.dragState.startPoint.y;
      this.updateElementProperty(this.selectedElement.id, 'x', this.dragState.elementStartPos.x + deltaX);
      this.updateElementProperty(this.selectedElement.id, 'y', this.dragState.elementStartPos.y + deltaY);
      this.render();
    }

    /**
     * Termine le glisser-déposer
     */
  }, {
    key: "endDrag",
    value: function endDrag() {
      this.dragState = null;
      this.saveToHistory();
    }

    /**
     * Gère le survol des éléments
     */
  }, {
    key: "handleHover",
    value: function handleHover(point) {
      var element = this.getElementAtPoint(point);
      this.canvas.style.cursor = element ? 'move' : 'default';
    }

    /**
     * Sélectionne un élément
     */
  }, {
    key: "selectElement",
    value: function selectElement(elementId) {
      this.selectedElement = this.elements.get(elementId);
      this.render();
    }

    /**
     * Désélectionne l'élément actuel
     */
  }, {
    key: "deselectElement",
    value: function deselectElement() {
      this.selectedElement = null;
      this.render();
    }

    /**
     * Obtient l'élément à une position donnée
     */
  }, {
    key: "getElementAtPoint",
    value: function getElementAtPoint(point) {
      // Parcourir les éléments dans l'ordre inverse (dernier ajouté = premier cliqué)
      var elementsArray = Array.from(this.elements.values()).reverse();
      var _iterator = _createForOfIteratorHelper(elementsArray),
        _step;
      try {
        for (_iterator.s(); !(_step = _iterator.n()).done;) {
          var element = _step.value;
          if (this.isPointInElement(point, element)) {
            return element;
          }
        }
      } catch (err) {
        _iterator.e(err);
      } finally {
        _iterator.f();
      }
      return null;
    }

    /**
     * Vérifie si un point est dans un élément
     */
  }, {
    key: "isPointInElement",
    value: function isPointInElement(point, element) {
      var props = element.properties;
      return point.x >= props.x && point.x <= props.x + props.width && point.y >= props.y && point.y <= props.y + props.height;
    }

    /**
     * Ajoute un élément au canvas
     */
  }, {
    key: "addElement",
    value: function addElement(type) {
      var properties = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var elementId = "element_".concat(Date.now(), "_").concat(Math.random().toString(36).substr(2, 9));

      // Obtenir les propriétés par défaut
      var defaultProps = this.customizationService.getDefaultProperties(type);

      // Fusionner avec les propriétés fournies
      var elementProperties = _objectSpread(_objectSpread({}, defaultProps), properties);

      // Valider les propriétés
      var validatedProps = {};
      for (var _i = 0, _Object$entries = Object.entries(elementProperties); _i < _Object$entries.length; _i++) {
        var _Object$entries$_i = (0, _slicedToArray2["default"])(_Object$entries[_i], 2),
          key = _Object$entries$_i[0],
          value = _Object$entries$_i[1];
        validatedProps[key] = this.customizationService.validateProperty(key, value);
      }
      var element = {
        id: elementId,
        type: type,
        properties: validatedProps,
        createdAt: Date.now(),
        updatedAt: Date.now()
      };
      this.elements.set(elementId, element);
      this.saveToHistory();
      this.render();
      return elementId;
    }

    /**
     * Met à jour une propriété d'élément
     */
  }, {
    key: "updateElementProperty",
    value: function updateElementProperty(elementId, property, value) {
      var element = this.elements.get(elementId);
      if (!element) return false;

      // Valider la propriété
      var validatedValue = this.customizationService.validateProperty(property, value);

      // Vérifier les restrictions
      if (!(0, _pdfCanvasElements.isPropertyAllowed)(element.type, property)) {
        console.warn("Property \"".concat(property, "\" not allowed for element type \"").concat(element.type, "\""));
        return false;
      }
      element.properties[property] = validatedValue;
      element.updatedAt = Date.now();
      this.render();
      return true;
    }

    /**
     * Supprime un élément
     */
  }, {
    key: "deleteElement",
    value: function deleteElement(elementId) {
      if (this.elements["delete"](elementId)) {
        if (this.selectedElement && this.selectedElement.id === elementId) {
          this.selectedElement = null;
        }
        this.saveToHistory();
        this.render();
        return true;
      }
      return false;
    }

    /**
     * Définit le niveau de zoom
     */
  }, {
    key: "setZoom",
    value: function setZoom(zoom) {
      this.options.zoom = Math.max(0.1, Math.min(5, zoom));
      this.canvas.style.transform = "scale(".concat(this.options.zoom, ")");
      this.canvas.style.transformOrigin = 'top left';
    }

    /**
     * Définit le mode d'interaction
     */
  }, {
    key: "setMode",
    value: function setMode(mode) {
      this.mode = mode;
      this.canvas.style.cursor = this.getCursorForMode(mode);
    }

    /**
     * Obtient le curseur approprié pour un mode
     */
  }, {
    key: "getCursorForMode",
    value: function getCursorForMode(mode) {
      var cursors = {
        select: 'default',
        draw: 'crosshair',
        text: 'text',
        move: 'move'
      };
      return cursors[mode] || 'default';
    }

    /**
     * Rend tous les éléments sur le canvas
     */
  }, {
    key: "render",
    value: function render() {
      // Utiliser l'optimiseur de performance si disponible
      if (this.performanceOptimizer) {
        this.performanceOptimizer.optimizeRendering();
        return;
      }

      // Effacer le canvas
      this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

      // Dessiner la grille si activée
      if (this.options.showGrid) {
        this.drawGrid();
      }

      // Dessiner tous les éléments
      var _iterator2 = _createForOfIteratorHelper(this.elements.values()),
        _step2;
      try {
        for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
          var element = _step2.value;
          this.renderElement(element);
        }

        // Dessiner les poignées de sélection
      } catch (err) {
        _iterator2.e(err);
      } finally {
        _iterator2.f();
      }
      if (this.selectedElement) {
        this.drawSelectionHandles(this.selectedElement);
      }
    }

    /**
     * Dessine la grille d'arrière-plan
     */
  }, {
    key: "drawGrid",
    value: function drawGrid() {
      var gridSize = this.options.gridSize;
      this.ctx.strokeStyle = '#f0f0f0';
      this.ctx.lineWidth = 1;

      // Lignes verticales
      for (var x = 0; x <= this.canvas.width; x += gridSize) {
        this.ctx.beginPath();
        this.ctx.moveTo(x, 0);
        this.ctx.lineTo(x, this.canvas.height);
        this.ctx.stroke();
      }

      // Lignes horizontales
      for (var y = 0; y <= this.canvas.height; y += gridSize) {
        this.ctx.beginPath();
        this.ctx.moveTo(0, y);
        this.ctx.lineTo(this.canvas.width, y);
        this.ctx.stroke();
      }
    }

    /**
     * Rend un élément spécifique
     */
  }, {
    key: "renderElement",
    value: function renderElement(element) {
      var props = element.properties;

      // Sauvegarder le contexte
      this.ctx.save();

      // Appliquer les transformations
      this.ctx.translate(props.x + props.width / 2, props.y + props.height / 2);
      if (props.rotation) {
        this.ctx.rotate(props.rotation * Math.PI / 180);
      }
      this.ctx.translate(-props.width / 2, -props.height / 2);

      // Appliquer l'opacité
      if (props.opacity !== undefined && props.opacity < 100) {
        this.ctx.globalAlpha = props.opacity / 100;
      }

      // Rendu selon le type d'élément
      switch (element.type) {
        case 'text':
          this.renderTextElement(element);
          break;
        case 'rectangle':
          this.renderRectangleElement(element);
          break;
        case 'image':
          this.renderImageElement(element);
          break;
        default:
          this.renderGenericElement(element);
          break;
      }

      // Restaurer le contexte
      this.ctx.restore();
    }

    /**
     * Rend un élément texte
     */
  }, {
    key: "renderTextElement",
    value: function renderTextElement(element) {
      var props = element.properties;

      // Configuration du texte
      this.ctx.font = "".concat(props.fontWeight || 'normal', " ").concat(props.fontSize || 14, "px ").concat(props.fontFamily || 'Arial, sans-serif');
      this.ctx.fillStyle = props.color || '#000000';
      this.ctx.textAlign = props.textAlign || 'left';

      // Position de départ
      var x = 0;
      var y = 0;

      // Ajuster selon l'alignement
      if (props.textAlign === 'center') {
        x = props.width / 2;
      } else if (props.textAlign === 'right') {
        x = props.width;
      }

      // Rendu du texte
      var text = props.text || 'Texte';
      var lines = text.split('\n');
      for (var i = 0; i < lines.length; i++) {
        this.ctx.fillText(lines[i], x, y + i * (props.fontSize || 14) * 1.2);
      }
    }

    /**
     * Rend un élément rectangle
     */
  }, {
    key: "renderRectangleElement",
    value: function renderRectangleElement(element) {
      var props = element.properties;

      // Fond
      if (props.backgroundColor && props.backgroundColor !== 'transparent') {
        this.ctx.fillStyle = props.backgroundColor;
        this.roundRect(0, 0, props.width, props.height, props.borderRadius || 0);
        this.ctx.fill();
      }

      // Bordure
      if (props.borderWidth && props.borderWidth > 0) {
        this.ctx.strokeStyle = props.borderColor || '#000000';
        this.ctx.lineWidth = props.borderWidth;
        this.roundRect(0, 0, props.width, props.height, props.borderRadius || 0);
        this.ctx.stroke();
      }
    }

    /**
     * Rend un élément image
     */
  }, {
    key: "renderImageElement",
    value: function renderImageElement(element) {
      var _this = this;
      var props = element.properties;
      if (props.src) {
        var img = new Image();
        img.onload = function () {
          // Calculer les dimensions pour le fit
          var drawWidth = props.width;
          var drawHeight = props.height;
          var drawX = 0;
          var drawY = 0;
          if (props.objectFit === 'cover') {
            var scale = Math.max(props.width / img.width, props.height / img.height);
            drawWidth = img.width * scale;
            drawHeight = img.height * scale;
            drawX = (props.width - drawWidth) / 2;
            drawY = (props.height - drawHeight) / 2;
          }
          _this.ctx.drawImage(img, drawX, drawY, drawWidth, drawHeight);
          _this.render(); // Re-rendre après le chargement de l'image
        };
        img.src = props.src;
      }
    }

    /**
     * Rend un élément générique
     */
  }, {
    key: "renderGenericElement",
    value: function renderGenericElement(element) {
      // Rendu par défaut pour les éléments non reconnus
      this.renderRectangleElement(element);
    }

    /**
     * Dessine un rectangle avec des coins arrondis
     */
  }, {
    key: "roundRect",
    value: function roundRect(x, y, width, height, radius) {
      if (radius === 0) {
        this.ctx.rect(x, y, width, height);
        return;
      }
      this.ctx.beginPath();
      this.ctx.moveTo(x + radius, y);
      this.ctx.lineTo(x + width - radius, y);
      this.ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
      this.ctx.lineTo(x + width, y + height - radius);
      this.ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
      this.ctx.lineTo(x + radius, y + height);
      this.ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
      this.ctx.lineTo(x, y + radius);
      this.ctx.quadraticCurveTo(x, y, x + radius, y);
      this.ctx.closePath();
    }

    /**
     * Dessine les poignées de sélection
     */
  }, {
    key: "drawSelectionHandles",
    value: function drawSelectionHandles(element) {
      var _this2 = this;
      var props = element.properties;
      var handleSize = 8;
      this.ctx.strokeStyle = '#007bff';
      this.ctx.lineWidth = 2;
      this.ctx.fillStyle = '#ffffff';

      // Poignées de redimensionnement
      var handles = [{
        x: props.x,
        y: props.y
      },
      // Haut-gauche
      {
        x: props.x + props.width,
        y: props.y
      },
      // Haut-droite
      {
        x: props.x + props.width,
        y: props.y + props.height
      },
      // Bas-droite
      {
        x: props.x,
        y: props.y + props.height
      } // Bas-gauche
      ];
      handles.forEach(function (handle) {
        _this2.ctx.fillRect(handle.x - handleSize / 2, handle.y - handleSize / 2, handleSize, handleSize);
        _this2.ctx.strokeRect(handle.x - handleSize / 2, handle.y - handleSize / 2, handleSize, handleSize);
      });

      // Rectangle de sélection
      this.ctx.strokeStyle = '#007bff';
      this.ctx.setLineDash([5, 5]);
      this.ctx.strokeRect(props.x, props.y, props.width, props.height);
      this.ctx.setLineDash([]);
    }

    /**
     * Sauvegarde l'état actuel dans l'historique
     */
  }, {
    key: "saveToHistory",
    value: function saveToHistory() {
      var state = {
        elements: new Map(this.elements),
        selectedElement: this.selectedElement ? this.selectedElement.id : null
      };

      // Supprimer les états futurs si on est au milieu de l'historique
      this.history = this.history.slice(0, this.historyIndex + 1);

      // Ajouter le nouvel état
      this.history.push(state);
      this.historyIndex++;

      // Limiter la taille de l'historique
      if (this.history.length > 50) {
        this.history.shift();
        this.historyIndex--;
      }
    }

    /**
     * Annule la dernière action
     */
  }, {
    key: "undo",
    value: function undo() {
      if (this.historyIndex > 0) {
        this.historyIndex--;
        this.restoreFromHistory();
      }
    }

    /**
     * Rétablit la dernière action annulée
     */
  }, {
    key: "redo",
    value: function redo() {
      if (this.historyIndex < this.history.length - 1) {
        this.historyIndex++;
        this.restoreFromHistory();
      }
    }

    /**
     * Restaure l'état depuis l'historique
     */
  }, {
    key: "restoreFromHistory",
    value: function restoreFromHistory() {
      var state = this.history[this.historyIndex];
      this.elements = new Map(state.elements);
      this.selectedElement = state.selectedElement ? this.elements.get(state.selectedElement) : null;
      this.render();
    }

    /**
     * Exporte le canvas en image
     */
  }, {
    key: "exportToImage",
    value: function exportToImage() {
      var format = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'png';
      var quality = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 1;
      return this.canvas.toDataURL("image/".concat(format), quality);
    }

    /**
     * Obtient les données JSON du canvas
     */
  }, {
    key: "exportToJSON",
    value: function exportToJSON() {
      return {
        version: '1.0',
        canvas: {
          width: this.options.width,
          height: this.options.height,
          backgroundColor: this.options.backgroundColor
        },
        elements: Array.from(this.elements.values()).map(function (element) {
          return {
            id: element.id,
            type: element.type,
            properties: element.properties,
            createdAt: element.createdAt,
            updatedAt: element.updatedAt
          };
        }),
        metadata: {
          exportedAt: Date.now(),
          elementCount: this.elements.size
        }
      };
    }

    /**
     * Importe des données JSON dans le canvas
     */
  }, {
    key: "importFromJSON",
    value: function importFromJSON(data) {
      var _this3 = this;
      try {
        this.elements.clear();
        data.elements.forEach(function (elementData) {
          var element = _objectSpread(_objectSpread({}, elementData), {}, {
            properties: _objectSpread({}, elementData.properties)
          });
          _this3.elements.set(element.id, element);
        });
        this.render();
        this.saveToHistory();
        return true;
      } catch (error) {
        console.error('Failed to import JSON:', error);
        return false;
      }
    }

    /**
     * Nettoie les ressources
     */
  }, {
    key: "dispose",
    value: function dispose() {
      // Supprimer les gestionnaires d'événements
      if (this.canvas) {
        this.canvas.removeEventListener('mousedown', this.handleMouseDown);
        this.canvas.removeEventListener('mousemove', this.handleMouseMove);
        this.canvas.removeEventListener('mouseup', this.handleMouseUp);
        this.canvas.removeEventListener('wheel', this.handleWheel);
      }
      document.removeEventListener('keydown', this.handleKeyDown);
      document.removeEventListener('keyup', this.handleKeyUp);
      window.removeEventListener('resize', this.handleResize);

      // Nettoyer les références
      this.elements.clear();
      this.selectedElement = null;
      this.dragState = null;
      this.history = [];
      this.historyIndex = -1;
      console.log('PDFCanvasVanilla disposed');
    }

    /**
     * Obtient les statistiques du canvas
     */
  }, {
    key: "getStats",
    value: function getStats() {
      var baseStats = {
        totalElements: this.elements.size,
        selectedElement: this.selectedElement ? this.selectedElement.id : null,
        canvasSize: {
          width: this.canvas.width,
          height: this.canvas.height
        },
        zoom: this.options.zoom,
        mode: this.mode
      };

      // Ajouter les statistiques de performance si disponibles
      if (this.performanceOptimizer) {
        return _objectSpread(_objectSpread({}, baseStats), {}, {
          performance: this.performanceOptimizer.getPerformanceStats()
        });
      }
      return baseStats;
    }
  }]);
}(); // Export de la classe
var _default = exports["default"] = PDFCanvasVanilla; // Fonction d'initialisation globale pour WordPress
window.pdfBuilderInitVanilla = function (containerId) {
  var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  console.log('🚀 Initialisation Vanilla JS PDF Builder...');
  try {
    // Créer l'instance principale
    var pdfCanvas = new PDFCanvasVanilla(containerId, options);

    // Attendre que le DOM soit prêt
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function () {
        pdfCanvas.init();
      });
    } else {
      pdfCanvas.init();
    }

    // Exposer l'instance globalement pour le débogage
    window.pdfBuilderInstance = pdfCanvas;
    console.log('✅ PDF Builder Vanilla initialisé avec succès');
    return pdfCanvas;
  } catch (error) {
    console.error('❌ Erreur lors de l\'initialisation Vanilla:', error);
    throw error;
  }
};

// Alias pour la compatibilité
window.pdfBuilderPro = {
  init: window.pdfBuilderInitVanilla
};
__webpack_exports__ = __webpack_exports__["default"];
/******/ 	return __webpack_exports__;
/******/ })()
;
});
//# sourceMappingURL=pdf-canvas-vanilla.bundle.js.map