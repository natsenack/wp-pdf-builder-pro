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
// Fonction de logging conditionnel (désactivée)
function debugLog() {
    // Logging supprimé
}
exports.debugLog = debugLog;
// Fonction de debug pour les sauvegardes (désactivée)
function debugSave() {
    // Logging supprimé
}
exports.debugSave = debugSave;
function debugError() {
    // Logging supprimé
}
exports.debugError = debugError;
function debugWarn() {
    // Logging supprimé
}
exports.debugWarn = debugWarn;
function debugTable(data) {
    // Logging supprimé
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
            }
            catch (err) {
                // ignore
            }
        });
    }
}

