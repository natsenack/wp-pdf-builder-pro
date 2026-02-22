"use strict";
/**
 * Normalisation robuste des éléments pour assurer la préservation complète des propriétés
 * C'est LE système central qui garantit que contentAlign, labelPosition, etc. ne sont jamais perdus
 */
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
exports.__esModule = true;
exports.debugElementState = exports.validateElementIntegrity = exports.normalizeElementsBeforeSave = exports.normalizeElementsAfterLoad = void 0;
var debug_1 = require("./debug");
/**
 * FONCTION CRITIQUE: Normalise les éléments sans perdre AUCUNE propriété personnalisée
 * Utilisée au chargement APRÈS le parsing JSON
 *
 * Propriétés à préserver ABSOLUMENT:
 * - contentAlign, labelPosition (order_number)
 * - Toute propriété custom ajoutée via l'éditeur
 */
function normalizeElementsAfterLoad(elements) {
    if (!Array.isArray(elements)) {
        (0, debug_1.debugWarn)('❌ [NORMALIZE] Elements n\'est pas un array:', typeof elements);
        return [];
    }
    return elements.map(function (el, idx) {
        if (!el || typeof el !== 'object') {
            (0, debug_1.debugWarn)("\u274C [NORMALIZE] Element ".concat(idx, " invalide:"), el);
            return {};
        }
        var element = el;
        // Créer une copie COMPLÈTE (spread shallow)
        var normalized = __assign(__assign({}, element), { id: element.id || "element-".concat(idx), type: element.type || 'unknown', x: Number(element.x) || 0, y: Number(element.y) || 0, width: Number(element.width) || 100, height: Number(element.height) || 100 });
        return normalized;
    });
}
exports.normalizeElementsAfterLoad = normalizeElementsAfterLoad;
/**
 * FONCTION CRITIQUE: Prépare les éléments pour la sauvegarde
 * Assure que TOUT est sérialisable et complet
 */
function normalizeElementsBeforeSave(elements) {
    if (!Array.isArray(elements)) {
        (0, debug_1.debugWarn)('❌ [SAVE NORMALIZE] Elements n\'est pas un array');
        return [];
    }
    return elements.map(function (el, idx) {
        if (!el || typeof el !== 'object') {
            (0, debug_1.debugWarn)("\u274C [SAVE NORMALIZE] Element ".concat(idx, " invalide"));
            return {};
        }
        // Créer une copie COMPLÈTE
        var normalized = __assign({}, el);
        // Valider les champs critiques
        if (!normalized.id)
            normalized.id = "element-".concat(idx);
        if (!normalized.type)
            normalized.type = 'unknown';
        if (typeof normalized.x !== 'number')
            normalized.x = 0;
        if (typeof normalized.y !== 'number')
            normalized.y = 0;
        if (typeof normalized.width !== 'number')
            normalized.width = 100;
        if (typeof normalized.height !== 'number')
            normalized.height = 100;
        // CRITICAL: Log les propriétés order_number avant sauvegarde
        if (normalized.type === 'order_number') {
        }
        // Filtrer les propriétés non sérialisables (Date, Function, etc)
        var serializable = {};
        Object.keys(normalized).forEach(function (key) {
            var value = normalized[key];
            var type = typeof value;
            // DEBUG: Log des propriétés spéciales
            if (key.includes('🎯') || key.includes('interactions') || key.includes('comportement') || key.includes('behavior')) {
                // 
            }
            // Garder: string, number, boolean, null, undefined
            // Garder: objects simples et arrays
            // REJETER: functions, symbols, dates (sauf si sérialisées)
            if (value === null ||
                value === undefined ||
                type === 'string' ||
                type === 'number' ||
                type === 'boolean') {
                serializable[key] = value;
            }
            else if (type === 'object') {
                try {
                    // Vérifier si c'est sérialisable
                    JSON.stringify(value);
                    serializable[key] = value;
                }
                catch (_a) {
                    (0, debug_1.debugWarn)("\u26A0\uFE0F  [SAVE NORMALIZE] Propri\u00E9t\u00E9 non s\u00E9rialisable ".concat(key, " skipp\u00E9e"), value);
                }
            }
            else {
                // Propriétés rejetées (functions, etc.)
                (0, debug_1.debugWarn)("\u26A0\uFE0F  [SAVE NORMALIZE] Propri\u00E9t\u00E9 rejet\u00E9e: ".concat(key, " (type: ").concat(type, ")"));
            }
        });
        return serializable;
    });
}
exports.normalizeElementsBeforeSave = normalizeElementsBeforeSave;
/**
 * Valide que les propriétés critiques sont présentes
 */
function validateElementIntegrity(elements, elementType) {
    var elementsOfType = elements.filter(function (el) { return el.type === elementType; });
    if (elementsOfType.length === 0) {
        return true; // Pas d'éléments de ce type
    }
    var allValid = true;
    elementsOfType.forEach(function (el, idx) {
        var required = ['id', 'type', 'x', 'y', 'width', 'height'];
        var missing = required.filter(function (key) { return !(key in el); });
        if (missing.length > 0) {
            (0, debug_1.debugError)("\u274C [VALIDATE] Element ".concat(idx, " missing: ").concat(missing.join(', ')));
            allValid = false;
        }
        if (elementType === 'order_number') {
            var hasContentAlign = 'contentAlign' in el;
            var hasLabelPosition = 'labelPosition' in el;
            if (!hasContentAlign || !hasLabelPosition) {
                allValid = false;
            }
        }
    });
    return allValid;
}
exports.validateElementIntegrity = validateElementIntegrity;
/**
 * Debug helper: affiche un rapport complet
 */
function debugElementState(elements, label) {
    // Debug function - logs removed for production
}
exports.debugElementState = debugElementState;

