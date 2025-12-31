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
})(typeof self !== "undefined" ? self : this, () => {
return (Object(typeof self !== "undefined" ? self : this)["webpackChunkpdfBuilderReact"] = Object(typeof self !== "undefined" ? self : this)["webpackChunkpdfBuilderReact"] || []).push([["pdf-builder-react"],{

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
// PDF Builder React Bundle - Entry Point - IMMEDIATE EXECUTION
// ============================================================================

// FORCE IMMEDIATE EXECUTION - These run BEFORE module wrapping
if (typeof window !== 'undefined') {
  window._pdfBundleStarting = true;
  console.log('🔥 [PDF BUNDLE] WINDOW CONTEXT AVAILABLE - Starting bootstrap');
  console.log('🔥 [PDF BUNDLE] React available?', _typeof(window.React));
  console.log('🔥 [PDF BUNDLE] ReactDOM available?', _typeof(window.ReactDOM));
}

// Import the main PDF Builder component


// THIS CODE RUNS IMMEDIATELY - Not wrapped in a function
console.log('🔥 [PDF BUNDLE] BOOTSTRAP PHASE - After imports');
console.log('🔥 [PDF BUNDLE] PDFBuilder imported, type:', _typeof(_ts_components_PDFBuilder__WEBPACK_IMPORTED_MODULE_0__["default"]));

// Get WordPress globals
var React = window.React;
var ReactDOM = window.ReactDOM;
console.log('🔥 [PDF BUNDLE] React global assignment done');
console.log('🔥 [PDF BUNDLE] ReactDOM global assignment done');

// Define the initialization function
function initPDFBuilderReact() {
  console.log('🔧 [PDF BUNDLE] initPDFBuilderReact called');
  try {
    // Check for container
    var container = document.getElementById('pdf-builder-react-root');
    if (!container) {
      console.error('❌ [PDF BUNDLE] Container not found');
      return false;
    }
    console.log('✅ [PDF BUNDLE] Container found');

    // Check React
    if (typeof React === 'undefined' || typeof ReactDOM === 'undefined') {
      console.error('❌ [PDF BUNDLE] React or ReactDOM not available');
      return false;
    }
    console.log('✅ [PDF BUNDLE] React dependencies OK');

    // Hide loading, show editor
    var loadingEl = document.getElementById('pdf-builder-react-loading');
    var editorEl = document.getElementById('pdf-builder-react-editor');
    if (loadingEl) loadingEl.style.display = 'none';
    if (editorEl) editorEl.style.display = 'block';
    console.log('🎨 [PDF BUNDLE] Creating React root...');

    // Create root and render
    var root = ReactDOM.createRoot(container);
    var element = React.createElement(_ts_components_PDFBuilder__WEBPACK_IMPORTED_MODULE_0__["default"]);
    console.log('🎨 [PDF BUNDLE] Rendering component...');
    root.render(element);
    console.log('✅ [PDF BUNDLE] Rendered successfully');
    return true;
  } catch (error) {
    console.error('❌ [PDF BUNDLE] Error:', error.message);
    console.error('❌ [PDF BUNDLE] Stack:', error.stack);
    return false;
  }
}

// Export for external use
var exports = {
  initPDFBuilderReact: initPDFBuilderReact
};
console.log('🌐 [PDF BUNDLE] Assigning to window.pdfBuilderReact');

// Assign to window IMMEDIATELY
window.pdfBuilderReact = exports;
console.log('✅ [PDF BUNDLE] window.pdfBuilderReact assigned:', _typeof(window.pdfBuilderReact));
console.log('✅ [PDF BUNDLE] window.pdfBuilderReact.initPDFBuilderReact:', _typeof(window.pdfBuilderReact.initPDFBuilderReact));

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
// IMMEDIATE POST-LOAD EXECUTION
if (typeof window !== 'undefined' && window.pdfBuilderReact) {
  console.log('🔥 [PDF BUNDLE] POST-LOAD: pdfBuilderReact is available');
}
