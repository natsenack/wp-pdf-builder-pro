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
return (self["webpackChunkpdfBuilderReact"] = self["webpackChunkpdfBuilderReact"] || []).push([["pdf-builder-react"],{

/***/ "./assets/js/pdf-builder-react/index.js":
/*!**********************************************!*\
  !*** ./assets/js/pdf-builder-react/index.js ***!
  \**********************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _ts_components_PDFBuilder__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/ts/components/PDFBuilder */ "./assets/ts/components/PDFBuilder.tsx");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
// ============================================================================
// PDF Builder React Bundle - Entry Point
// ============================================================================

console.log('🔥 [PDF Builder] BUNDLE EXECUTING - START - TIMESTAMP:', new Date().toISOString());
console.log('🔥 [PDF Builder] Document ready state:', document.readyState);
console.log('🔥 [PDF Builder] Window location:', window.location.href);

// Import the main PDF Builder component

console.log('🔥 [PDF Builder] PDFBuilder import completed');

// Use WordPress globals instead of imports
var React = window.React;
var ReactDOM = window.ReactDOM;
console.log('🔥 [PDF Builder] React globals:', {
  React: _typeof(React),
  ReactDOM: _typeof(ReactDOM)
});
console.log('🔥 [PDF Builder] window.React version:', window.React && window.React.version);
console.log('🔥 [PDF Builder] window.ReactDOM version:', window.ReactDOM && window.ReactDOM.version);

// Flag pour afficher les logs d'initialisation détaillés
var DEBUG_VERBOSE = true;
if (DEBUG_VERBOSE) console.log('🚀 PDF Builder React bundle starting execution...');
function initPDFBuilderReact() {
  console.log('🔧 [PDF Builder] initPDFBuilderReact function called');
  console.log('📊 [PDF Builder] PDFBuilder type:', _typeof(_ts_components_PDFBuilder__WEBPACK_IMPORTED_MODULE_0__["default"]));
  console.log('📊 [PDF Builder] PDFBuilder:', _ts_components_PDFBuilder__WEBPACK_IMPORTED_MODULE_0__["default"]);
  try {
    console.log('🔍 [PDF Builder] Looking for container...');
    // Vérifier si le container existe
    var container = document.getElementById('pdf-builder-react-root');
    console.log('🔍 [PDF Builder] Container element:', container);
    if (!container) {
      console.error('❌ [PDF Builder] Container #pdf-builder-react-root not found');
      return false;
    }
    console.log('✅ [PDF Builder] Container found, checking dependencies...');

    // Vérifier les dépendances
    console.log('🔧 [PDF Builder] Checking React availability:', _typeof(React), React);
    if (typeof React === 'undefined') {
      console.error('❌ [PDF Builder] React is not available');
      return false;
    }
    console.log('🔧 [PDF Builder] Checking ReactDOM availability:', _typeof(ReactDOM), ReactDOM);
    if (typeof ReactDOM === 'undefined') {
      console.error('❌ [PDF Builder] ReactDOM is not available');
      return false;
    }
    console.log('🔧 [PDF Builder] Checking ReactDOM.createRoot:', _typeof(ReactDOM.createRoot));
    if (typeof ReactDOM.createRoot === 'undefined') {
      console.error('❌ [PDF Builder] ReactDOM.createRoot is not available');
      return false;
    }
    console.log('✅ [PDF Builder] React dependencies available');
    console.log('🎯 [PDF Builder] All dependencies loaded, initializing React...');

    // Masquer le loading et afficher l'éditeur
    var loadingEl = document.getElementById('pdf-builder-react-loading');
    var editorEl = document.getElementById('pdf-builder-react-editor');
    console.log('🎨 [PDF Builder] Hiding loading, showing editor:', {
      loadingEl: loadingEl,
      editorEl: editorEl
    });
    if (loadingEl) loadingEl.style.display = 'none';
    if (editorEl) editorEl.style.display = 'block';
    console.log('🎨 [PDF Builder] Creating React root...');

    // Créer et rendre l'application React
    var root = ReactDOM.createRoot(container);
    console.log('🎨 [PDF Builder] React root created, rendering PDF Builder component...');
    console.log('📦 [PDF Builder] About to create element from PDFBuilder...');
    var pdfBuilderElement = React.createElement(_ts_components_PDFBuilder__WEBPACK_IMPORTED_MODULE_0__["default"]);
    console.log('🎨 [PDF Builder] Created PDF Builder element:', pdfBuilderElement);
    console.log('🎨 [PDF Builder] Element type:', pdfBuilderElement.type);
    console.log('🎨 [PDF Builder] Element props:', pdfBuilderElement.props);
    console.log('🔴 [PDF Builder] ABOUT TO RENDER TO CONTAINER');
    root.render(pdfBuilderElement);
    console.log('✅ [PDF Builder] PDF Builder component rendered successfully');
    return true;
  } catch (error) {
    console.error('❌ [PDF Builder] Error in initPDFBuilderReact:', error);
    console.error('❌ [PDF Builder] Error stack:', error.stack);
    console.error('❌ [PDF Builder] Error message:', error.message);
    var container = document.getElementById('pdf-builder-react-root');
    if (container) {
      container.innerHTML = '<p>❌ Erreur lors du rendu React: ' + error.message + '</p><pre>' + error.stack + '</pre>';
    }
    return false;
  }
}
if (DEBUG_VERBOSE) console.log('📦 Creating exports object...');

// Define exports object
var exports = {
  initPDFBuilderReact: initPDFBuilderReact
};
if (DEBUG_VERBOSE) console.log('🌐 Setting up global window.pdfBuilderReact...');

// Assign to window immediately (synchronously before module finishes)
window.pdfBuilderReact = exports;
if (DEBUG_VERBOSE) {
  console.log('🌐 [PDF Builder] Assigned to window.pdfBuilderReact:', window.pdfBuilderReact);
  console.log('🌐 [PDF Builder] window.pdfBuilderReact keys:', Object.keys(window.pdfBuilderReact));
  if (window.pdfBuilderReact && typeof window.pdfBuilderReact.initPDFBuilderReact === 'function') {
    console.log('✅ [PDF Builder] initPDFBuilderReact is available globally');
  } else {
    console.error('❌ [PDF Builder] initPDFBuilderReact NOT available globally');
  }
}

// Export as default for webpack
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (exports);

/***/ }),

/***/ "./assets/ts/components/PDFBuilder.tsx":
/*!*********************************************!*\
  !*** ./assets/ts/components/PDFBuilder.tsx ***!
  \*********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react/jsx-runtime */ "./node_modules/react/jsx-runtime.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _ts_components_TemplateSelector__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/ts/components/TemplateSelector */ "./assets/ts/components/TemplateSelector.tsx");



/**
 * Main PDF Builder component
 */
const PDFBuilder = () => {
    return ((0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)("div", { className: "pdf-builder-container", children: [(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)("div", { className: "pdf-builder-header", children: [(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("h1", { children: "PDF Builder Pro" }), (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("p", { children: "Cr\u00E9ez vos templates PDF personnalis\u00E9s" })] }), (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("div", { className: "pdf-builder-content", children: (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(_ts_components_TemplateSelector__WEBPACK_IMPORTED_MODULE_2__["default"], { onTemplateSelect: (template) => {
                        console.log('Template selected:', template);
                        // TODO: Implement template selection logic
                    } }) })] }));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (PDFBuilder);


/***/ }),

/***/ "./assets/ts/components/TemplateSelector.tsx":
/*!***************************************************!*\
  !*** ./assets/ts/components/TemplateSelector.tsx ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react/jsx-runtime */ "./node_modules/react/jsx-runtime.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react */ "./node_modules/react/index.js");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_1__);


/**
 * Composant pour sélectionner un template PDF
 * Exemple d'utilisation de la structure TypeScript
 */
const TemplateSelector = ({ selectedTemplate, onTemplateSelect, category, isLoading = false, className = '', ...props }) => {
    const [templates, setTemplates] = (0,react__WEBPACK_IMPORTED_MODULE_1__.useState)([]);
    const [error, setError] = (0,react__WEBPACK_IMPORTED_MODULE_1__.useState)(null);
    // Chargement des templates
    (0,react__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
        const loadTemplates = async () => {
            var _a;
            try {
                setError(null);
                // Exemple d'appel AJAX WordPress
                const response = await fetch(window.ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'pdf_builder_get_templates',
                        category: category || '',
                        nonce: window.pdfBuilderPro.nonce,
                    }),
                }).then(res => res.json());
                if (response.success) {
                    setTemplates(response.data);
                }
                else {
                    setError(((_a = response.data) === null || _a === void 0 ? void 0 : _a.message) || 'Erreur lors du chargement des templates');
                }
            }
            catch (err) {
                setError('Erreur de connexion');
                console.error('Erreur lors du chargement des templates:', err);
            }
        };
        loadTemplates();
    }, [category]);
    // Gestionnaire de sélection
    const handleTemplateSelect = (template) => {
        onTemplateSelect(template);
    };
    // Rendu en cas d'erreur
    if (error) {
        return ((0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("div", { className: `template-selector error ${className}`, ...props, children: (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)("div", { className: "error-message", children: [(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("p", { children: error }), (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("button", { type: "button", onClick: () => window.location.reload(), className: "button button-secondary", children: "R\u00E9essayer" })] }) }));
    }
    // Rendu en cours de chargement
    if (isLoading) {
        return ((0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("div", { className: `template-selector loading ${className}`, ...props, children: (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)("div", { className: "loading-spinner", children: [(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("span", { className: "dashicons dashicons-update spin" }), (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("p", { children: "Chargement des templates..." })] }) }));
    }
    // Rendu normal
    return ((0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)("div", { className: `template-selector ${className}`, ...props, children: [(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("h3", { children: "S\u00E9lectionner un template" }), (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("div", { className: "template-grid", children: templates.map((template) => ((0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)("div", { className: `template-card ${(selectedTemplate === null || selectedTemplate === void 0 ? void 0 : selectedTemplate.id) === template.id ? 'selected' : ''}`, onClick: () => handleTemplateSelect(template), role: "button", tabIndex: 0, onKeyDown: (e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            handleTemplateSelect(template);
                        }
                    }, children: [template.thumbnail && ((0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("img", { src: template.thumbnail, alt: template.name, className: "template-thumbnail" })), (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)("div", { className: "template-info", children: [(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("h4", { children: template.name }), (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("p", { children: template.description }), (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("span", { className: "template-category", children: template.category })] }), template.isDefault && ((0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("span", { className: "template-badge default", children: "Par d\u00E9faut" }))] }, template.id))) }), templates.length === 0 && ((0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("div", { className: "no-templates", children: (0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("p", { children: "Aucun template disponible" }) }))] }));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (TemplateSelector);


/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["vendors"], () => (__webpack_exec__("./assets/js/pdf-builder-react/index.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ return __webpack_exports__;
/******/ }
]);
});
//# sourceMappingURL=pdf-builder-react.bundle.js.map