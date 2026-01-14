"use strict";
exports.__esModule = true;
exports.debugTable = exports.debugWarn = exports.debugError = exports.debugSave = exports.debugLog = void 0;
// Fonction pour vérifier si on est sur la page de l'éditeur PDF
function isPDFEditorPage() {
    // Vérifier si l'élément avec la classe 'pdf-builder' existe (composant PDFBuilderContent)
    return typeof document !== 'undefined' &&
        document.querySelector('.pdf-builder') !== null;
}
// Fonction pour vérifier si on est sur la page des paramètres
function isSettingsPage() {
    // Vérifier si on est sur la page des paramètres (admin.php?page=pdf-builder-settings)
    return typeof window !== 'undefined' &&
        typeof window.location !== 'undefined' &&
        window.location.href.indexOf('pdf-builder-settings') !== -1;
}
// Fonction pour vérifier si le debug est activé
function isDebugEnabled() {
    var _a, _b;
    // Debug activé si explicitement forcé
    if ((_b = (_a = window.location) === null || _a === void 0 ? void 0 : _a.search) === null || _b === void 0 ? void 0 : _b.includes('debug=force')) {
        return true;
    }
    // Vérifier les paramètres de debug centralisés
    if (typeof window.pdfBuilderDebugSettings !== 'undefined' &&
        window.pdfBuilderDebugSettings &&
        typeof window.pdfBuilderDebugSettings === 'object') {
        return !!window.pdfBuilderDebugSettings.javascript;
    }
    // Fallback vers pdfBuilderCanvasSettings pour la compatibilité
    if (typeof window.pdfBuilderCanvasSettings !== 'undefined' &&
        window.pdfBuilderCanvasSettings &&
        typeof window.pdfBuilderCanvasSettings === 'object') {
        var debugSettings = window.pdfBuilderCanvasSettings.debug;
        if (debugSettings && typeof debugSettings === 'object') {
            return !!debugSettings.javascript;
        }
    }
    return false;
}
// Fonction de logging conditionnel
function debugLog() {
    var args = [];
    for (var _i = 0; _i < arguments.length; _i++) {
        args[_i] = arguments[_i];
    }
    if (isDebugEnabled()) {
        console.log.apply(console, args);
    }
}
exports.debugLog = debugLog;
// Fonction de debug pour les sauvegardes (activable séparément)
function debugSave() {
    var args = [];
    for (var _i = 0; _i < arguments.length; _i++) {
        args[_i] = arguments[_i];
    }
    if (isDebugEnabled()) {
        console.log.apply(console, args);
    }
}
exports.debugSave = debugSave;
function debugError() {
    var args = [];
    for (var _i = 0; _i < arguments.length; _i++) {
        args[_i] = arguments[_i];
    }
    if (isDebugEnabled()) {
        console.error.apply(console, args);
    }
}
exports.debugError = debugError;
function debugWarn() {
    var args = [];
    for (var _i = 0; _i < arguments.length; _i++) {
        args[_i] = arguments[_i];
    }
    if (isDebugEnabled()) {
        console.warn.apply(console, args);
    }
}
exports.debugWarn = debugWarn;
function debugTable(data) {
    if (isDebugEnabled()) {
        console.table(data);
    }
}
exports.debugTable = debugTable;
// Keep an internal verbose flag in sync with window.pdfBuilderDebugSettings
if (typeof window !== 'undefined') {
    try {
        window.PDF_BUILDER_VERBOSE = !!(window.pdfBuilderDebugSettings && window.pdfBuilderDebugSettings.javascript);
    }
    catch (e) {
        // ignore
    }
    // Listener to update verbose flag at runtime
    if (window.addEventListener) {
        window.addEventListener('pdfBuilder:debugSettingsChanged', function (e) {
            try {
                var detail = e && e.detail ? e.detail : window.pdfBuilderDebugSettings;
                window.PDF_BUILDER_VERBOSE = !!(detail && detail.javascript);
                if (typeof window.console !== 'undefined' && window.PDF_BUILDER_VERBOSE) {
                    console.log('[PDF Builder Debug] pdfBuilder:debugSettingsChanged, verbose set to', window.PDF_BUILDER_VERBOSE);
                }
            }
            catch (err) {
                if (typeof window.console !== 'undefined') {
                    console.warn('[PDF Builder Debug] Error handling pdfBuilder:debugSettingsChanged', err);
                }
            }
        });
    }
}
