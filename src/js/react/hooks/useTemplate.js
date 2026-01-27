"use strict";
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
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (g && (g = 0, op[0] && (_ = 0)), _) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};
exports.__esModule = true;
exports.useTemplate = void 0;
var react_1 = require("react");
var BuilderContext_1 = require("../contexts/builder/BuilderContext");
var CanvasSettingsContext_1 = require("../contexts/CanvasSettingsContext");
var debug_1 = require("../utils/debug");
var elementNormalization_1 = require("../utils/elementNormalization");
function useTemplate() {
    var _this = this;
    var _a = (0, BuilderContext_1.useBuilder)(), state = _a.state, dispatch = _a.dispatch;
    var _b = (0, CanvasSettingsContext_1.useCanvasSettings)(), canvasWidth = _b.canvasWidth, canvasHeight = _b.canvasHeight;
    
    // Détecter si on est sur un template existant via l'URL ou les données localisées
    var getTemplateIdFromUrl = (0, react_1.useCallback)(function () {
        var _a;
        // Priorité 1: Utiliser le templateId des données PHP localisées
        if ((_a = window.pdfBuilderData) === null || _a === void 0 ? void 0 : _a.templateId) {
            return window.pdfBuilderData.templateId.toString();
        }
        // Priorité 2: Utiliser le paramètre URL (template ou template_id pour compatibilité)
        var urlParams = new URLSearchParams(window.location.search);
        var urlTemplateId = urlParams.get('template') || urlParams.get('template_id');
        if (urlTemplateId) {
            return urlTemplateId;
        }
        return null;
    }, []);
    var isEditingExistingTemplate = function () {
        return getTemplateIdFromUrl() !== null;
    };
    // Charger un template existant
    var loadExistingTemplate = (0, react_1.useCallback)(function (templateId) { return __awaiter(_this, void 0, void 0, function () {
        var templateData_1, templateName_1, elements_1, canvasData_1, normalizedElements_1, enrichedElements_1, lastSavedDate_1, isChrome, isFirefox, isSafari, fetchOptions, cacheBreaker, url, response, errorText, result, templateData, ajaxTemplateName, templateName, orderNumberElements, elements, canvasData, normalizedElements, enrichedElements, lastSavedDate, finalOrderNumberElements, error_1, isChrome, isFirefox, isSafari, fallbackOptions, fallbackUrl, fallbackResponse, fallbackError_1;
        var _a, _b, _c, _d, _e, _f, _g, _h, _j, _k, _l, _m, _o;
        return __generator(this, function (_p) {
            switch (_p.label) {
                case 0:
                    
                    
                    
                    
                     }));
                    
                    
                    _p.label = 1;
                case 1:
                    _p.trys.push([1, 6, , 12]);
                    // ✅ PRIORITÉ: Utiliser les données localisées si disponibles (plus rapide et fiable)
                    if (((_e = window.pdfBuilderData) === null || _e === void 0 ? void 0 : _e.existingTemplate) && ((_f = window.pdfBuilderData) === null || _f === void 0 ? void 0 : _f.hasExistingData)) {
                        
                        templateData_1 = window.pdfBuilderData.existingTemplate;
                        
                        
                        
                        
                        templateName_1 = ((templateData_1 === null || templateData_1 === void 0 ? void 0 : templateData_1.name) && templateData_1.name.trim() !== '') ?
                            templateData_1.name :
                            ((templateData_1 === null || templateData_1 === void 0 ? void 0 : templateData_1._db_name) && templateData_1._db_name.trim() !== '') ?
                                templateData_1._db_name :
                                "[NOM NON R\u00C9CUP\u00C9R\u00C9 - ID: ".concat(templateId, "]");
                        
                        elements_1 = [];
                        canvasData_1 = null;
                        try {
                            if (typeof templateData_1.elements === 'string') {
                                elements_1 = JSON.parse(templateData_1.elements);
                            }
                            else if (Array.isArray(templateData_1.elements)) {
                                elements_1 = templateData_1.elements;
                            }
                            else {
                                elements_1 = [];
                            }
                            if (templateData_1.canvasWidth && templateData_1.canvasHeight) {
                                canvasData_1 = {
                                    width: templateData_1.canvasWidth,
                                    height: templateData_1.canvasHeight
                                };
                            }
                            else if (typeof templateData_1.canvas === 'string') {
                                canvasData_1 = JSON.parse(templateData_1.canvas);
                            }
                            else if (templateData_1.canvas && typeof templateData_1.canvas === 'object') {
                                canvasData_1 = templateData_1.canvas;
                            }
                            else {
                                canvasData_1 = { width: 794, height: 1123 };
                            }
                        }
                        catch (parseError) {
                            (0, debug_1.debugError)('❌ [LOAD TEMPLATE] Erreur de parsing des données localisées:', parseError);
                            elements_1 = [];
                            canvasData_1 = { width: 794, height: 1123 };
                        }
                        normalizedElements_1 = (0, elementNormalization_1.normalizeElementsAfterLoad)(elements_1);
                        enrichedElements_1 = normalizedElements_1.map(function (el) {
                            var enrichedElement = __assign({}, el);
                            if (el.type === 'company_logo' && !el.src && !el.logoUrl) {
                                var logoUrl = el.defaultSrc || '';
                                if (logoUrl) {
                                    enrichedElement.src = logoUrl;
                                }
                            }
                            if (enrichedElement.createdAt) {
                                try {
                                    var createdAt = new Date(enrichedElement.createdAt);
                                    enrichedElement.createdAt = isNaN(createdAt.getTime()) ? new Date() : createdAt;
                                }
                                catch (_a) {
                                    enrichedElement.createdAt = new Date();
                                }
                            }
                            else {
                                enrichedElement.createdAt = new Date();
                            }
                            if (enrichedElement.updatedAt) {
                                try {
                                    var updatedAt = new Date(enrichedElement.updatedAt);
                                    enrichedElement.updatedAt = isNaN(updatedAt.getTime()) ? new Date() : updatedAt;
                                }
                                catch (_b) {
                                    enrichedElement.updatedAt = new Date();
                                }
                            }
                            else {
                                enrichedElement.updatedAt = new Date();
                            }
                            return enrichedElement;
                        });
                        try {
                            if (templateData_1.updated_at) {
                                lastSavedDate_1 = new Date(templateData_1.updated_at);
                                if (isNaN(lastSavedDate_1.getTime())) {
                                    lastSavedDate_1 = new Date();
                                }
                            }
                            else {
                                lastSavedDate_1 = new Date();
                            }
                        }
                        catch (_q) {
                            lastSavedDate_1 = new Date();
                        }
                        dispatch({
                            type: 'LOAD_TEMPLATE',
                            payload: {
                                id: templateId,
                                name: templateName_1,
                                elements: enrichedElements_1,
                                canvas: canvasData_1,
                                lastSaved: lastSavedDate_1
                            }
                        });
                        return [2 /*return*/, true];
                    }
                    // ✅ FALLBACK: Utiliser AJAX si les données localisées ne sont pas disponibles
                    
                    
                    
                    
                    isChrome = typeof navigator !== 'undefined' &&
                        /Chrome/.test(navigator.userAgent) &&
                        /Google Inc/.test(navigator.vendor);
                    isFirefox = typeof navigator !== 'undefined' &&
                        /Firefox/.test(navigator.userAgent);
                    isSafari = typeof navigator !== 'undefined' &&
                        /Safari/.test(navigator.userAgent) &&
                        !/Chrome/.test(navigator.userAgent) &&
                        !/Chromium/.test(navigator.userAgent);
                    
                    fetchOptions = {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            // En-têtes anti-cache spécifiques
                            'Cache-Control': 'no-cache, no-store, must-revalidate',
                            'Pragma': 'no-cache',
                            'Expires': '0'
                        },
                        // Mode spécifique selon le navigateur
                        mode: 'cors',
                        credentials: 'same-origin'
                    };
                    // Ajustements spécifiques par navigateur
                    if (isChrome) {
                        // Chrome peut avoir besoin d'un mode plus permissif
                        fetchOptions.mode = 'cors';
                        fetchOptions.cache = 'no-cache';
                        
                    }
                    else if (isFirefox) {
                        // Firefox gère bien le cache par défaut
                        fetchOptions.cache = 'no-cache';
                        
                    }
                    else if (isSafari) {
                        // Safari peut avoir des problèmes avec certains modes
                        fetchOptions.mode = 'cors';
                        
                    }
                    cacheBreaker = Date.now();
                    url = "".concat((_j = window.pdfBuilderData) === null || _j === void 0 ? void 0 : _j.ajaxUrl, "?action=pdf_builder_get_template&template_id=").concat(templateId, "&nonce=").concat((_k = window.pdfBuilderData) === null || _k === void 0 ? void 0 : _k.nonce, "&t=").concat(cacheBreaker);
                    
                    
                    return [4 /*yield*/, fetch(url, fetchOptions)];
                case 2:
                    response = _p.sent();
                    if (!!response.ok) return [3 /*break*/, 4];
                    return [4 /*yield*/, response.text()];
                case 3:
                    errorText = _p.sent();
                    (0, debug_1.debugError)('[useTemplate] Response error text:', errorText);
                    throw new Error("Erreur HTTP ".concat(response.status, ": ").concat(response.statusText));
                case 4: return [4 /*yield*/, response.json()];
                case 5:
                    result = _p.sent();
                    if (!result.success) {
                        throw new Error(result.data || 'Erreur lors du chargement du template');
                    }
                    
                    
                    
                    
                    templateData = result.data ? result.data.template : result.template;
                    ajaxTemplateName = result.data ? (result.data.template_name || result.data.name) : (result.name || result.template_name);
                    
                    
                    templateName = (ajaxTemplateName && ajaxTemplateName.trim() !== '') ?
                        ajaxTemplateName :
                        ((templateData === null || templateData === void 0 ? void 0 : templateData.name) && templateData.name.trim() !== '') ?
                            templateData.name :
                            "[NOM NON R\u00C9CUP\u00C9R\u00C9 - ID: ".concat(templateId, "]");
                    // 🔍 Tracer les éléments reçus du serveur
                    if (templateData.elements) {
                        orderNumberElements = templateData.elements.filter(function (el) { return el.type === 'order_number'; });
                    }
                    elements = [];
                    canvasData = null;
                    try {
                        // Check if elements is already an object or needs parsing
                        if (typeof templateData.elements === 'string') {
                            elements = JSON.parse(templateData.elements);
                        }
                        else if (Array.isArray(templateData.elements)) {
                            elements = templateData.elements;
                        }
                        else {
                            elements = [];
                        }
                        // ✅ CORRECTION: Support both old format (canvas: {width, height}) and new format (canvasWidth, canvasHeight)
                        if (templateData.canvasWidth && templateData.canvasHeight) {
                            canvasData = {
                                width: templateData.canvasWidth,
                                height: templateData.canvasHeight
                            };
                        }
                        else if (typeof templateData.canvas === 'string') {
                            canvasData = JSON.parse(templateData.canvas);
                        }
                        else if (templateData.canvas && typeof templateData.canvas === 'object') {
                            canvasData = templateData.canvas;
                        }
                        else {
                            canvasData = { width: 794, height: 1123 };
                        }
                    }
                    catch (parseError) {
                        (0, debug_1.debugError)('❌ [LOAD TEMPLATE] Erreur de parsing:', parseError);
                        elements = [];
                        canvasData = { width: 794, height: 1123 };
                    }
                    normalizedElements = (0, elementNormalization_1.normalizeElementsAfterLoad)(elements);
                    (0, elementNormalization_1.debugElementState)(normalizedElements, 'APRÈS CHARGEMENT');
                    enrichedElements = normalizedElements.map(function (el) {
                        var enrichedElement = __assign({}, el);
                        // ✅ CORRECTION: Enrichir les éléments company_logo SEULEMENT si src ET logoUrl sont vides
                        if (el.type === 'company_logo' && !el.src && !el.logoUrl) {
                            // Essayer d'obtenir le logo depuis les propriétés de l'élément
                            var logoUrl = el.defaultSrc || '';
                            if (logoUrl) {
                                enrichedElement.src = logoUrl;
                            }
                        }
                        // Convertir les propriétés de date en objets Date valides
                        if (enrichedElement.createdAt) {
                            try {
                                var createdAt = new Date(enrichedElement.createdAt);
                                enrichedElement.createdAt = isNaN(createdAt.getTime()) ? new Date() : createdAt;
                            }
                            catch (_a) {
                                enrichedElement.createdAt = new Date();
                            }
                        }
                        else {
                            enrichedElement.createdAt = new Date();
                        }
                        if (enrichedElement.updatedAt) {
                            try {
                                var updatedAt = new Date(enrichedElement.updatedAt);
                                enrichedElement.updatedAt = isNaN(updatedAt.getTime()) ? new Date() : updatedAt;
                            }
                            catch (_b) {
                                enrichedElement.updatedAt = new Date();
                            }
                        }
                        else {
                            enrichedElement.updatedAt = new Date();
                        }
                        return enrichedElement;
                    });
                    enrichedElements.slice(0, 3).forEach(function (_el, _idx) {
                    });
                    lastSavedDate = void 0;
                    try {
                        if (templateData.updated_at) {
                            lastSavedDate = new Date(templateData.updated_at);
                            // Vérifier si la date est valide
                            if (isNaN(lastSavedDate.getTime())) {
                                lastSavedDate = new Date();
                            }
                        }
                        else {
                            lastSavedDate = new Date();
                        }
                    }
                    catch (_r) {
                        lastSavedDate = new Date();
                    }
                    finalOrderNumberElements = enrichedElements.filter(function (el) { return el.type === 'order_number'; });
                    dispatch({
                        type: 'LOAD_TEMPLATE',
                        payload: {
                            id: templateId,
                            name: templateName,
                            elements: enrichedElements,
                            canvas: canvasData,
                            lastSaved: lastSavedDate
                        }
                    });
                    return [2 /*return*/, true];
                case 6:
                    error_1 = _p.sent();
                    (0, debug_1.debugError)('❌ [LOAD TEMPLATE] Erreur lors du chargement:', error_1);
                    isChrome = typeof navigator !== 'undefined' &&
                        /Chrome/.test(navigator.userAgent) &&
                        /Google Inc/.test(navigator.vendor);
                    isFirefox = typeof navigator !== 'undefined' &&
                        /Firefox/.test(navigator.userAgent);
                    isSafari = typeof navigator !== 'undefined' &&
                        /Safari/.test(navigator.userAgent) &&
                        !/Chrome/.test(navigator.userAgent) &&
                        !/Chromium/.test(navigator.userAgent);
                    (0, debug_1.debugError)("\u274C [LOAD TEMPLATE] \u00C9chec du chargement sur ".concat(isChrome ? 'Chrome' : isFirefox ? 'Firefox' : isSafari ? 'Safari' : 'navigateur inconnu'));
                    (0, debug_1.debugError)('❌ [LOAD TEMPLATE] Détails de l\'erreur:', {
                        message: error_1 instanceof Error ? error_1.message : 'Unknown error',
                        stack: error_1 instanceof Error ? error_1.stack : undefined,
                        name: error_1 instanceof Error ? error_1.name : 'Unknown',
                        templateId: templateId,
                        ajaxUrl: (_l = window.pdfBuilderData) === null || _l === void 0 ? void 0 : _l.ajaxUrl,
                        userAgent: navigator.userAgent
                    });
                    if (!(isChrome && (error_1 instanceof Error && error_1.message.includes('fetch')))) return [3 /*break*/, 11];
                    (0, debug_1.debugWarn)('🔄 [LOAD TEMPLATE] Tentative de fallback pour Chrome - Nouvelle tentative avec options différentes');
                    _p.label = 7;
                case 7:
                    _p.trys.push([7, 10, , 11]);
                    // Attendre un peu avant retry
                    return [4 /*yield*/, new Promise(function (resolve) { return setTimeout(resolve, 1000); })];
                case 8:
                    // Attendre un peu avant retry
                    _p.sent();
                    fallbackOptions = {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json, text/plain, */*',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        mode: 'no-cors',
                        cache: 'reload'
                    };
                    fallbackUrl = "".concat((_m = window.pdfBuilderData) === null || _m === void 0 ? void 0 : _m.ajaxUrl, "?action=pdf_builder_get_template&template_id=").concat(templateId, "&nonce=").concat((_o = window.pdfBuilderData) === null || _o === void 0 ? void 0 : _o.nonce, "&fallback=1&t=").concat(Date.now());
                    return [4 /*yield*/, fetch(fallbackUrl, fallbackOptions)];
                case 9:
                    fallbackResponse = _p.sent();
                    if (fallbackResponse.ok || fallbackResponse.status === 0) { // no-cors peut retourner status 0
                        // Traiter la réponse même si elle est opaque
                        return [2 /*return*/, true];
                    }
                    return [3 /*break*/, 11];
                case 10:
                    fallbackError_1 = _p.sent();
                    (0, debug_1.debugError)('❌ [LOAD TEMPLATE] Échec du fallback:', fallbackError_1);
                    return [3 /*break*/, 11];
                case 11: return [2 /*return*/, false];
                case 12: return [2 /*return*/];
            }
        });
    }); }, [dispatch]);
    // 🎯 DISABLED: Event-based template loading causes race conditions with useEffect
    // Both methods try to load the same template, causing flashing/alternating canvas
    // The useEffect approach (reading URL) is more reliable and runs once per page load
    (0, react_1.useEffect)(function () {
        // ✅ Event listener disabled to prevent race conditions
        // Only useEffect with URL reading will load templates now
        return function () {
            // cleanup
        };
    }, []);
    // Effet pour charger automatiquement un template existant au montage
    // ✅ Dépendance vide: charger une seule fois au montage du composant
    (0, react_1.useEffect)(function () {
        var templateId = getTemplateIdFromUrl();
        if (templateId) {
            // Timeout de sécurité : forcer isLoading à false après 10 secondes si le chargement échoue
            var loadingTimeout_1 = setTimeout(function () {
                (0, debug_1.debugError)('[useTemplate] Loading timeout reached, forcing isLoading to false');
                dispatch({ type: 'SET_TEMPLATE_LOADING', payload: false });
            }, 10000);
            // Charger le template avec gestion d'erreur améliorée
            loadExistingTemplate(templateId)
                .then(function () {
                clearTimeout(loadingTimeout_1);
            })["catch"](function (error) {
                clearTimeout(loadingTimeout_1);
                (0, debug_1.debugError)('[useTemplate] Template loading failed:', error);
                // Force isLoading to false on error
                dispatch({ type: 'SET_TEMPLATE_LOADING', payload: false });
            });
        }
        else {
            // Si pas de template ID, forcer isLoading à false pour nouveau template
            dispatch({ type: 'NEW_TEMPLATE' });
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);
    // Sauvegarder un template manuellement
    var saveTemplate = (0, react_1.useCallback)(function () { return __awaiter(_this, void 0, void 0, function () {
        var templateId, normalizedElements, templateData, formData, response, result, errorMessage, error_2;
        var _a, _b, _c, _d, _e;
        return __generator(this, function (_f) {
            switch (_f.label) {
                case 0:
                    // 
                    dispatch({ type: 'SET_TEMPLATE_SAVING', payload: true });
                    _f.label = 1;
                case 1:
                    _f.trys.push([1, 4, 5, 6]);
                    templateId = state.template.id || getTemplateIdFromUrl();
                    // 
                    if (!templateId) {
                        throw new Error('Aucun template chargé pour la sauvegarde');
                    }
                    // Vérifier que le template est complètement chargé
                    if (!state.template.name || state.template.name.trim() === '') {
                        // 
                        return [2 /*return*/]; // Ne pas lancer d'erreur, juste ignorer
                    }
                    // 
                    if (!((_a = window.pdfBuilderData) === null || _a === void 0 ? void 0 : _a.ajaxUrl)) {
                        throw new Error('URL AJAX non disponible');
                    }
                    if (!((_b = window.pdfBuilderData) === null || _b === void 0 ? void 0 : _b.nonce)) {
                        throw new Error('Nonce non disponible');
                    }
                    normalizedElements = (0, elementNormalization_1.normalizeElementsBeforeSave)(state.elements);
                    (0, elementNormalization_1.debugElementState)(normalizedElements, 'AVANT SAUVEGARDE');
                    // 🔍 DEBUG: Log complet des propriétés des éléments avant sauvegarde
                    // 
                    // 
                    // Vérifier les propriétés spéciales
                    normalizedElements.forEach(function (el, idx) {
                        // 
                        // Chercher des propriétés avec emoji ou "interactions"
                        Object.keys(el).forEach(function (key) {
                            if (key.includes('🎯') || key.includes('interactions') || key.includes('comportement') || key.includes('behavior')) {
                                // 
                            }
                        });
                    });
                    templateData = {
                        elements: normalizedElements,
                        canvasWidth: canvasWidth,
                        canvasHeight: canvasHeight,
                        version: '1.0',
                        // Inclure les paramètres du template
                        name: state.template.name,
                        description: state.template.description,
                        showGuides: state.template.showGuides,
                        snapToGrid: state.template.snapToGrid,
                        marginTop: state.template.marginTop,
                        marginBottom: state.template.marginBottom
                    };
                    formData = new FormData();
                    formData.append('action', 'pdf_builder_save_template');
                    formData.append('template_id', templateId);
                    formData.append('template_name', state.template.name || 'Nouveau template');
                    formData.append('template_description', state.template.description || '');
                    formData.append('template_data', JSON.stringify(templateData));
                    formData.append('nonce', ((_c = window.pdfBuilderData) === null || _c === void 0 ? void 0 : _c.nonce) || '');
                    
                    
                    // Ajouter les paramètres du template
                    formData.append('show_guides', state.template.showGuides ? '1' : '0');
                    formData.append('snap_to_grid', state.template.snapToGrid ? '1' : '0');
                    formData.append('margin_top', (state.template.marginTop || 0).toString());
                    formData.append('margin_bottom', (state.template.marginBottom || 0).toString());
                    formData.append('canvas_width', (state.template.canvasWidth || canvasWidth).toString());
                    formData.append('canvas_height', (state.template.canvasHeight || canvasHeight).toString());
                    return [4 /*yield*/, fetch(((_e = window.pdfBuilderData) === null || _e === void 0 ? void 0 : _e.ajaxUrl) || '', {
                            method: 'POST',
                            body: formData
                        })];
                case 2:
                    response = _f.sent();
                    // 
                    if (!response.ok) {
                        throw new Error("Erreur HTTP: ".concat(response.status));
                    }
                    return [4 /*yield*/, response.json()];
                case 3:
                    result = _f.sent();
                    // 
                    if (!result.success) {
                        (0, debug_1.debugError)('[PDF_BUILDER_FRONTEND] Server returned error:', result.data);
                        errorMessage = result.data || 'Unknown error during save';
                        throw new Error(errorMessage);
                    }
                    // 
                    dispatch({
                        type: 'SAVE_TEMPLATE',
                        payload: {
                            id: result.data.template_id || result.data.id,
                            name: result.data.name
                        }
                    });
                    return [3 /*break*/, 6];
                case 4:
                    error_2 = _f.sent();
                    (0, debug_1.debugError)('[PDF_BUILDER_FRONTEND] Save failed:', error_2);
                    (0, debug_1.debugError)('[useTemplate] SAVE - Error:', error_2);
                    throw error_2;
                case 5:
                    dispatch({ type: 'SET_TEMPLATE_SAVING', payload: false });
                    return [7 /*endfinally*/];
                case 6: return [2 /*return*/];
            }
        });
    }); }, [state.elements, state.template.name, dispatch, canvasWidth, canvasHeight, getTemplateIdFromUrl]);
    var previewTemplate = (0, react_1.useCallback)(function () {
        dispatch({ type: 'SET_SHOW_PREVIEW_MODAL', payload: true });
    }, [dispatch]);
    var newTemplate = (0, react_1.useCallback)(function () {
        dispatch({ type: 'NEW_TEMPLATE' });
    }, [dispatch]);
    var setTemplateModified = (0, react_1.useCallback)(function (modified) {
        dispatch({ type: 'SET_TEMPLATE_MODIFIED', payload: modified });
    }, [dispatch]);
    var updateTemplateSettings = (0, react_1.useCallback)(function (settings) {
        dispatch({ type: 'UPDATE_TEMPLATE_SETTINGS', payload: settings });
    }, [dispatch]);
    return {
        templateName: state.template.name,
        templateDescription: state.template.description,
        templateTags: state.template.tags,
        canvasWidth: state.template.canvasWidth,
        canvasHeight: state.template.canvasHeight,
        marginTop: state.template.marginTop,
        marginBottom: state.template.marginBottom,
        showGuides: state.template.showGuides,
        snapToGrid: state.template.snapToGrid,
        isNewTemplate: state.template.isNew,
        isModified: state.template.isModified,
        isSaving: state.template.isSaving,
        isLoading: state.template.isLoading,
        lastSaved: state.template.lastSaved,
        isEditingExistingTemplate: isEditingExistingTemplate(),
        saveTemplate: saveTemplate,
        previewTemplate: previewTemplate,
        newTemplate: newTemplate,
        setTemplateModified: setTemplateModified,
        updateTemplateSettings: updateTemplateSettings
    };
}
exports.useTemplate = useTemplate;

