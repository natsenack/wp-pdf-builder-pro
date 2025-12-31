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
/***/ (() => {

throw new Error("Module build failed (from ./node_modules/babel-loader/lib/index.js):\nSyntaxError: I:\\wp-pdf-builder-pro\\assets\\js\\pdf-builder-react\\index.js: Unexpected token (133:0)\n\n\u001b[0m \u001b[90m 131 |\u001b[39m }\u001b[33m;\u001b[39m\n \u001b[90m 132 |\u001b[39m   window\u001b[33m.\u001b[39mpdfBuilderReactDebug\u001b[33m.\u001b[39mpush(\u001b[32m'FUNCTION_ASSIGNED'\u001b[39m)\u001b[33m;\u001b[39m\n\u001b[31m\u001b[1m>\u001b[22m\u001b[39m\u001b[90m 133 |\u001b[39m }\n \u001b[90m     |\u001b[39m \u001b[31m\u001b[1m^\u001b[22m\u001b[39m\n \u001b[90m 134 |\u001b[39m\n \u001b[90m 135 |\u001b[39m \u001b[90m// Export for module systems\u001b[39m\n \u001b[90m 136 |\u001b[39m \u001b[36mexport\u001b[39m \u001b[36mdefault\u001b[39m initPDFBuilderReact\u001b[33m;\u001b[39m\u001b[0m\n    at constructor (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:367:19)\n    at Parser.raise (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:6624:19)\n    at Parser.unexpected (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:6644:16)\n    at Parser.parseExprAtom (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:11508:22)\n    at Parser.parseExprSubscripts (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:11145:23)\n    at Parser.parseUpdate (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:11130:21)\n    at Parser.parseMaybeUnary (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:11110:23)\n    at Parser.parseMaybeUnaryOrPrivate (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:10963:61)\n    at Parser.parseExprOps (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:10968:23)\n    at Parser.parseMaybeConditional (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:10945:23)\n    at Parser.parseMaybeAssign (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:10895:21)\n    at Parser.parseExpressionBase (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:10848:23)\n    at I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:10844:39\n    at Parser.allowInAnd (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:12495:16)\n    at Parser.parseExpression (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:10844:17)\n    at Parser.parseStatementContent (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:12971:23)\n    at Parser.parseStatementLike (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:12843:17)\n    at Parser.parseModuleItem (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:12820:17)\n    at Parser.parseBlockOrModuleBlockBody (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:13392:36)\n    at Parser.parseBlockBody (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:13385:10)\n    at Parser.parseProgram (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:12698:10)\n    at Parser.parseTopLevel (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:12688:25)\n    at Parser.parse (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:14568:25)\n    at parse (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\parser\\lib\\index.js:14602:38)\n    at parser (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\core\\lib\\parser\\index.js:41:34)\n    at parser.next (<anonymous>)\n    at normalizeFile (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\core\\lib\\transformation\\normalize-file.js:64:37)\n    at normalizeFile.next (<anonymous>)\n    at run (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\core\\lib\\transformation\\index.js:22:50)\n    at run.next (<anonymous>)\n    at transform (I:\\wp-pdf-builder-pro\\node_modules\\@babel\\core\\lib\\transform.js:22:33)\n    at transform.next (<anonymous>)\n    at step (I:\\wp-pdf-builder-pro\\node_modules\\gensync\\index.js:261:32)\n    at I:\\wp-pdf-builder-pro\\node_modules\\gensync\\index.js:273:13\n    at async.call.result.err.err (I:\\wp-pdf-builder-pro\\node_modules\\gensync\\index.js:223:11)");

/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ var __webpack_exports__ = (__webpack_exec__("./assets/js/pdf-builder-react/index.js"));
/******/ __webpack_exports__ = __webpack_exports__["default"];
/******/ return __webpack_exports__;
/******/ }
]);
});
//# sourceMappingURL=pdf-builder-react.bundle.js.map